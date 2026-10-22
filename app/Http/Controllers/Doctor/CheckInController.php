<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    /** Front-desk lookup: scan or type a registration number to see who the patient is and today's visit. */
    public function index(Request $request): View
    {
        $request->validate(['reg_number' => ['nullable', 'string', 'max:30']]);

        $user = $request->user();
        $reg = trim((string) $request->query('reg_number', ''));

        $student = null;
        $appointments = collect();
        $error = null;

        if ($reg !== '') {
            $student = Student::with('user')->where('reg_number', $reg)->first();

            if ($student === null) {
                $error = 'No student found with registration number "' . $reg . '".';
            } else {
                $query = $student->appointments()
                    ->with('doctor.user')
                    ->whereDate('date', today())
                    ->orderBy('time_slot');

                // A doctor only sees visits booked with THEM; admin sees every doctor's
                if ($user->isDoctor()) {
                    abort_if($user->doctor === null, 403, 'Your account has no doctor profile.');
                    $query->where('doctor_id', $user->doctor->id);
                }

                $appointments = $query->get();
            }
        }

        return view('doctor.checkin', compact('student', 'appointments', 'error', 'reg'));
    }
}
