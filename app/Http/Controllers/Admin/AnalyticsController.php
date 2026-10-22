<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['range' => ['nullable', Rule::in([7, 30, 90])]]);
        $range = (int) ($data['range'] ?? 30);

        $to = today();
        $from = today()->subDays($range - 1);

        $appointments = Appointment::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get(['date', 'status']);
        $live = $appointments->where('status', '!=', 'cancelled');   // real demand: cancelled ones excluded

        // Appointments per day, with zero-filled gaps
        $perDay = $live->countBy(fn ($a) => $a->date->toDateString());
        $daily = ['labels' => [], 'data' => []];
        foreach (CarbonPeriod::create($from, $to) as $day) {
            $daily['labels'][] = $day->format('j M');
            $daily['data'][] = $perDay->get($day->toDateString(), 0);
        }

        // Status breakdown (all appointments in the period)
        $byStatus = $appointments->countBy('status');
        $status = [
            'labels' => ['Completed', 'Approved', 'Pending', 'Cancelled'],
            'data'   => [
                $byStatus->get('completed', 0), $byStatus->get('approved', 0),
                $byStatus->get('pending', 0), $byStatus->get('cancelled', 0),
            ],
        ];

        // Busiest weekdays (Sunday only appears if it has any)
        $byDay = $live->countBy(fn ($a) => $a->date->englishDayOfWeek);
        $weekday = ['labels' => [], 'data' => []];
        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $name) {
            if ($name === 'Sunday' && $byDay->get($name, 0) === 0) {
                continue;
            }
            $weekday['labels'][] = substr($name, 0, 3);
            $weekday['data'][] = $byDay->get($name, 0);
        }

        // Most common diagnoses in the period
        $top = MedicalRecord::whereDate('created_at', '>=', $from->toDateString())
            ->selectRaw('diagnosis, count(*) as total')
            ->groupBy('diagnosis')->orderByDesc('total')->limit(8)->get();
        $diagnoses = [
            'labels' => $top->pluck('diagnosis')->map(fn ($d) => Str::limit($d, 30))->all(),
            'data'   => $top->pluck('total')->all(),
        ];

        return view('admin.analytics', [
            'range' => $range, 'total' => $live->count(),
            'daily' => $daily, 'status' => $status, 'weekday' => $weekday, 'diagnoses' => $diagnoses,
        ]);
    }
}
