<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Appointment;
use App\Models\Doctor;

trait ResolvesDoctor
{
    /** The doctor profile of the logged-in user (never trusts an id from the request). */
    protected function doctor(): Doctor
    {
        $doctor = auth()->user()?->doctor;
        abort_if($doctor === null, 403, 'Your account has no doctor profile.');

        return $doctor;
    }

    /** A doctor may only act on their own appointments, whatever id is in the URL. */
    protected function authorizeOwn(Appointment $appointment): void
    {
        abort_unless($appointment->doctor_id === $this->doctor()->id, 403);
    }
}
