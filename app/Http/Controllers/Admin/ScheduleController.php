<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function index(): View
    {
        $doctors = Doctor::with('user')->get()->sortBy('name')->values();

        $schedules = DoctorSchedule::with('doctor.user')
            ->orderBy('doctor_id')
            ->orderByRaw("FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')")
            ->orderBy('start_time')
            ->get();

        return view('admin.manage-schedules', compact('doctors', 'schedules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'doctor_id'     => ['required', 'exists:doctors,id'],
            'day_of_week'   => ['required', Rule::in(self::DAYS)],
            'start_time'    => ['required', 'date_format:H:i'],
            'end_time'      => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['required', Rule::in([15, 30, 45, 60])],
        ]);

        // Two blocks for the same doctor on the same day may not overlap
        $overlaps = DoctorSchedule::where('doctor_id', $data['doctor_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('start_time', '<', $data['end_time'] . ':00')
            ->where('end_time', '>', $data['start_time'] . ':00')
            ->exists();

        if ($overlaps) {
            return back()->withInput()->withErrors(['start_time' => 'That time overlaps an existing block for this doctor on ' . $data['day_of_week'] . '.']);
        }

        DoctorSchedule::create([
            'doctor_id'             => $data['doctor_id'],
            'day_of_week'           => $data['day_of_week'],
            'start_time'            => $data['start_time'],
            'end_time'              => $data['end_time'],
            'slot_duration_minutes' => $data['slot_duration'],
        ]);

        return back()->with('status', 'Schedule block added. Students can now book those times.');
    }

    public function destroy(DoctorSchedule $schedule): RedirectResponse
    {
        // Don't pull the rug from under students who already hold a live booking in this block
        $hasUpcoming = Appointment::where('doctor_id', $schedule->doctor_id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('date', '>=', today())
            ->whereRaw('DAYNAME(date) = ?', [$schedule->day_of_week])
            ->where('time_slot', '>=', $schedule->start_time)
            ->where('time_slot', '<', $schedule->end_time)
            ->exists();

        if ($hasUpcoming) {
            return back()->withErrors(['schedule' => 'Cannot remove this block: there are upcoming pending or approved appointments in it. Cancel or complete them first.']);
        }

        $schedule->delete();

        return back()->with('status', 'Schedule block removed.');
    }
}
