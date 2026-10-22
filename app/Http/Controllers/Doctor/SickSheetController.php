<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\ResolvesDoctor;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\SickSheet;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SickSheetController extends Controller
{
    use ResolvesDoctor;

    private const MAX_REST_DAYS = 30;

    /** Shows the issue form, or the issued letter if one already exists. */
    public function show(Appointment $appointment): View|RedirectResponse
    {
        $this->authorizeOwn($appointment);

        if ($appointment->status !== 'completed') {
            return redirect()
                ->route('doctor.dashboard', ['date' => $appointment->date->toDateString()])
                ->withErrors(['appointment' => 'A sick sheet can only be issued after the consultation is completed.']);
        }

        $appointment->load('student.user', 'doctor.user', 'sickSheet');
        $sheet = $appointment->sickSheet?->load('student.user', 'doctor.user');

        return view('doctor.sick-sheet', compact('appointment', 'sheet'));
    }

    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctor = $this->doctor();
        $this->authorizeOwn($appointment);

        if ($appointment->status !== 'completed') {
            return back()->withErrors(['appointment' => 'Complete the consultation first.']);
        }
        if ($appointment->sickSheet()->exists()) {
            return redirect()->route('doctor.sicksheet', $appointment)
                ->withErrors(['appointment' => 'A sick sheet was already issued for this visit.']);
        }

        $data = $request->validate([
            'rest_from' => ['required', 'date_format:Y-m-d'],
            'rest_to'   => ['required', 'date_format:Y-m-d', 'after_or_equal:rest_from'],
            'remarks'   => ['nullable', 'string', 'max:500'],
        ]);

        $days = (int) Carbon::parse($data['rest_from'])->diffInDays(Carbon::parse($data['rest_to'])) + 1;
        if ($days > self::MAX_REST_DAYS) {
            return back()->withInput()->withErrors(['rest_to' => 'Rest period cannot be longer than ' . self::MAX_REST_DAYS . ' days.']);
        }

        try {
            SickSheet::create([
                'appointment_id' => $appointment->id,
                'student_id'     => $appointment->student_id,
                'doctor_id'      => $doctor->id,
                'reference_no'   => SickSheet::nextReference(),
                'rest_from'      => $data['rest_from'],
                'rest_to'        => $data['rest_to'],
                'remarks'        => $data['remarks'] ?? null,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // double-click, or two sheets numbered at the exact same moment
            return redirect()->route('doctor.sicksheet', $appointment)
                ->withErrors(['appointment' => 'This sick sheet was already issued. Please check the details below.']);
        }

        return redirect()->route('doctor.sicksheet', $appointment)->with('status', 'Sick sheet issued.');
    }
}
