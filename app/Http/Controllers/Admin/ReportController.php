<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const LIMIT = 500;

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $appointments = $this->query($filters)
            ->with(['student.user', 'doctor.user'])
            ->orderByDesc('date')->orderByDesc('time_slot')
            ->limit(self::LIMIT)->get();

        $byStatus = $this->query($filters)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.reports', [
            'filters'      => $filters,
            'appointments' => $appointments,
            'summary'      => $byStatus,
            'total'        => $byStatus->sum(),
            'doctors'      => Doctor::with('user')->get()->sortBy('name')->values(),
            'statuses'     => Appointment::STATUSES,
            'limit'        => self::LIMIT,
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);

        $rows = $this->query($filters)
            ->with(['student.user', 'doctor.user'])
            ->orderBy('date')->orderBy('time_slot')
            ->limit(5000)->get();

        $filename = 'mubs-appointments-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // lets Excel read UTF-8 names correctly
            fputcsv($out, ['Date', 'Time', 'Student', 'Reg No', 'Doctor', 'Category', 'Status', 'Reason']);

            foreach ($rows as $a) {
                fputcsv($out, [
                    $a->date->format('Y-m-d'),
                    substr($a->time_slot, 0, 5),
                    $this->safe($a->student->user->name),
                    $this->safe($a->student->reg_number),
                    $this->safe($a->doctor->name),
                    $this->safe($a->category),
                    $a->status,
                    $this->safe($a->reason),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Validated filters; defaults to a month back and a month ahead. */
    private function filters(Request $request): array
    {
        $data = $request->validate([
            'from'      => ['nullable', 'date_format:Y-m-d'],
            'to'        => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'status'    => ['nullable', Rule::in(Appointment::STATUSES)],
        ]);

        return [
            'from'      => $data['from'] ?? today()->subDays(30)->toDateString(),
            'to'        => $data['to'] ?? today()->addDays(30)->toDateString(),
            'doctor_id' => $data['doctor_id'] ?? null,
            'status'    => $data['status'] ?? null,
        ];
    }

    private function query(array $f)
    {
        return Appointment::query()
            ->whereBetween('date', [$f['from'], $f['to']])
            ->when($f['doctor_id'], fn ($q, $id) => $q->where('doctor_id', $id))
            ->when($f['status'], fn ($q, $status) => $q->where('status', $status));
    }

    /** Stops spreadsheet "formula injection": a cell starting with = + - @ would be run by Excel. */
    private function safe(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
