<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\ResolvesDoctor;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    use ResolvesDoctor;

    /**
     * false = a doctor may consult any APPROVED appointment (handy for demos).
     * true  = only appointments dated today or earlier (stricter, more realistic).
     */
    public const BLOCK_FUTURE = false;

    public function show(Appointment $appointment): View|RedirectResponse
    {
        $this->authorizeOwn($appointment);

        if ($problem = $this->cannotConsult($appointment)) {
            return $this->backToQueue($appointment)->withErrors(['appointment' => $problem]);
        }

        $appointment->load('student.user');

        // Earlier visits help the doctor see the patient's history
        $history = MedicalRecord::where('student_id', $appointment->student_id)
            ->with('doctor.user')->latest()->take(5)->get();

        return view('doctor.consultation', compact('appointment', 'history'));
    }

    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->doctor();
        $this->authorizeOwn($appointment);

        $data = $request->validate([
            'diagnosis'    => ['required', 'string', 'max:2000'],
            'prescription' => ['nullable', 'string', 'max:2000'],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ]);

        // Record + "completed" are saved together or not at all. Re-reading the row with a lock
        // stops a double-click from creating two records.
        $saved = DB::transaction(function () use ($appointment, $doctor, $data) {
            $fresh = Appointment::whereKey($appointment->id)->lockForUpdate()->first();

            if ($this->cannotConsult($fresh) !== null) {
                return false;
            }

            MedicalRecord::create([
                'appointment_id' => $fresh->id,
                'student_id'     => $fresh->student_id,
                'doctor_id'      => $doctor->id,
                'diagnosis'      => $data['diagnosis'],
                'prescription'   => $data['prescription'] ?? null,
                'notes'          => $data['notes'] ?? null,
            ]);
            $fresh->update(['status' => 'completed']);

            return true;
        });

        if (! $saved) {
            return $this->backToQueue($appointment)
                ->withErrors(['appointment' => 'This consultation was already completed or is no longer approved.']);
        }

        return $this->backToQueue($appointment)
            ->with('status', 'Consultation saved and the appointment marked completed. You can now issue a sick sheet if needed.');
    }

    private function cannotConsult(Appointment $appointment): ?string
    {
        if ($appointment->status === 'completed') {
            return 'This consultation has already been completed.';
        }
        if ($appointment->status !== 'approved') {
            return 'Only approved appointments can be consulted. Accept the request first.';
        }
        if (self::BLOCK_FUTURE && $appointment->date->gt(today())) {
            return 'This appointment is in the future.';
        }

        return null;
    }

    private function backToQueue(Appointment $appointment): RedirectResponse
    {
        return redirect()->route('doctor.dashboard', ['date' => $appointment->date->toDateString()]);
    }
}
