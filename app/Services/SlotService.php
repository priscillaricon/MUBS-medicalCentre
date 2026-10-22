<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;

/**
 * Works out which time slots a doctor offers on a date:
 *   doctor_schedules (working hours)  minus  already-booked appointments  minus  times already past.
 * Used by the slots API (to draw the dropdown) AND by booking/rescheduling (to re-check on the server).
 */
class SlotService
{
    public function forDate(Doctor $doctor, Carbon $date, ?int $ignoreAppointmentId = null): array
    {
        $date = $date->copy()->startOfDay();
        $day = $date->englishDayOfWeek;

        $result = [
            'working' => false, 'day' => $day, 'window' => '',
            'slots' => [], 'free' => 0, 'total' => 0, 'message' => '',
        ];

        if ($date->lt(today())) {
            $result['message'] = 'That date has already passed.';
            return $result;
        }

        $blocks = $doctor->schedules()->where('day_of_week', $day)->orderBy('start_time')->get();
        if ($blocks->isEmpty()) {
            $result['message'] = "This doctor does not hold clinic on {$day}s.";
            return $result;
        }

        // Slots taken by live (non-cancelled) appointments. When rescheduling, the
        // appointment being moved is ignored so its own slot doesn't block itself.
        $booked = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('date', $date->toDateString())
            ->where('status', '!=', 'cancelled')
            ->when($ignoreAppointmentId, fn ($q) => $q->where('id', '!=', $ignoreAppointmentId))
            ->pluck('time_slot')
            ->map(fn ($t) => substr((string) $t, 0, 8))
            ->all();

        $now = now();
        $windows = [];

        foreach ($blocks as $block) {
            $start = Carbon::parse($date->toDateString() . ' ' . $block->start_time);
            $end   = Carbon::parse($date->toDateString() . ' ' . $block->end_time);
            $step  = max(5, (int) $block->slot_duration_minutes);
            $windows[] = $start->format('H:i') . '–' . $end->format('H:i');

            for ($t = $start->copy(); $t->copy()->addMinutes($step)->lte($end); $t->addMinutes($step)) {
                if ($t->lte($now)) {
                    continue; // this time has already gone today
                }
                $value = $t->format('H:i:s');
                $isBooked = in_array($value, $booked, true);

                $result['slots'][] = ['value' => $value, 'label' => $t->format('H:i'), 'booked' => $isBooked];
                $result['total']++;
                if (! $isBooked) {
                    $result['free']++;
                }
            }
        }

        $result['working'] = true;
        $result['window'] = implode(', ', $windows);
        if ($result['free'] === 0) {
            $result['message'] = 'Fully booked on this date. Please pick another day.';
        }

        return $result;
    }

    /** True only if $time (HH:MM:SS) is a real, still-free slot for that doctor on that date. */
    public function isBookable(Doctor $doctor, string $date, string $time, ?int $ignoreAppointmentId = null): bool
    {
        foreach ($this->forDate($doctor, Carbon::parse($date), $ignoreAppointmentId)['slots'] as $slot) {
            if ($slot['value'] === $time) {
                return ! $slot['booked'];
            }
        }
        return false;
    }
}
