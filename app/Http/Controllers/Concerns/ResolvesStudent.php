<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Student;

trait ResolvesStudent
{
    /** The student profile of the logged-in user (never trusts an id from the request). */
    protected function student(): Student
    {
        $student = auth()->user()?->student;
        abort_if($student === null, 403, 'Your account has no student profile.');

        return $student;
    }
}
