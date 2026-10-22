<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Concerns\ResolvesDoctor;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorPortalController extends Controller
{
    use ResolvesDoctor;

    public function dashboard(Request $request): View
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);

        $doctor = $this->doctor()->load('user');
        $date = $request->query('date', today()->toDateString());

        // The chosen day's queue (only this doctor's own appointments)
        $queue = $doctor->appointments()
            ->with(['student.user', 'sickSheet'])
            ->whereDate('date', $date)
            ->orderBy('time_slot')
            ->get();

        // Requests on ANY upcoming day that still need a decision, so none get missed
        $pending = $doctor->appointments()
            ->with('student.user')
            ->where('status', 'pending')
            ->whereDate('date', '>=', today())
            ->orderBy('date')->orderBy('time_slot')
            ->take(15)->get();

        return view('doctor.dashboard', [
            'doctor' => $doctor,
            'date' => $date,
            'queue' => $queue,
            'counts' => $queue->countBy('status'),
            'pending' => $pending,
            'blockFuture' => ConsultationController::BLOCK_FUTURE,
        ]);
    }

    public function respond(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeOwn($appointment);

        $data = $request->validate(['action' => ['required', Rule::in(['accept', 'cancel'])]]);

        if ($appointment->status !== 'pending') {
            return back()->withErrors(['appointment' => 'That request has already been handled.']);
        }

        $accepted = $data['action'] === 'accept';
        $appointment->update(['status' => $accepted ? 'approved' : 'cancelled']);   // cancelling frees the slot

        return back()->with('status', $accepted ? 'Appointment accepted.' : 'Appointment cancelled.');
    }
}
