<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\ResolvesStudent;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Student;
use App\Services\SlotService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    use ResolvesStudent;

    private const CATEGORIES = [
        'General Consultation', 'Dental Checkup', 'Eye Checkup', 'Counseling', 'Emergency',
    ];

    public function create(): View
    {
        $doctors = Doctor::with('user')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get()->sortBy('name')->values();

        return view('student.book-appointment', [
            'doctors' => $doctors,
            'categories' => self::CATEGORIES,
        ]);
    }

    /** JSON for the booking page: which slots this doctor offers on a date. */
    public function slots(Request $request, Doctor $doctor, SlotService $slots): JsonResponse
    {
        abort_unless($doctor->user->is_active, 404);

        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'ignore' => ['nullable', 'integer'],
        ]);

        // "ignore" is only honoured for the student's own appointment (used when rescheduling)
        $ignore = null;
        if ($request->filled('ignore')
            && Appointment::where('id', $request->input('ignore'))
                ->where('student_id', $this->student()->id)->exists()) {
            $ignore = (int) $request->input('ignore');
        }

        return response()->json($slots->forDate($doctor, Carbon::parse($request->input('date')), $ignore));
    }

    public function store(Request $request, SlotService $slots): RedirectResponse
    {
        $student = $this->student();

        $data = $request->validate([
            'category'  => ['required', Rule::in(self::CATEGORIES)],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'date'      => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time_slot' => ['required', 'date_format:H:i:s'],
            'reason'    => ['nullable', 'string', 'max:255'],
        ]);

        $doctor = Doctor::with('user')->findOrFail($data['doctor_id']);

        if (! $doctor->user->is_active) {
            return back()->withInput()->withErrors(['doctor_id' => 'That doctor is not accepting appointments right now.']);
        }

        // Never trust the dropdown: re-check on the server that this is a real, free slot
        if (! $slots->isBookable($doctor, $data['date'], $data['time_slot'])) {
            return back()->withInput()->withErrors(['time_slot' => 'That time is not available. Please choose another slot.']);
        }
        if ($this->studentIsBusy($student, $data['date'], $data['time_slot'])) {
            return back()->withInput()->withErrors(['time_slot' => 'You already have an appointment at that time.']);
        }

        try {
            Appointment::create([
                'student_id' => $student->id,
                'doctor_id'  => $doctor->id,
                'category'   => $data['category'],
                'date'       => $data['date'],
                'time_slot'  => $data['time_slot'],
                'status'     => 'pending',
                'reason'     => $data['reason'] ?? null,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // The database's double-booking guard: someone else grabbed it a split second earlier
            return back()->withInput()->withErrors(['time_slot' => 'That slot was just taken by another student. Please pick a different one.']);
        }

        return redirect()->route('student.dashboard')
            ->with('status', 'Appointment requested. Please wait for the doctor to confirm it.');
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        $this->authorizeOwn($appointment);

        if (! $appointment->isCancellable()) {
            return back()->withErrors(['appointment' => 'Only pending or approved appointments can be cancelled.']);
        }

        $appointment->update(['status' => 'cancelled']);   // frees the slot for others

        return back()->with('status', 'Appointment cancelled.');
    }

    public function reschedule(Request $request, Appointment $appointment, SlotService $slots): RedirectResponse
    {
        $student = $this->student();
        $this->authorizeOwn($appointment);

        if (! $appointment->isCancellable()) {
            return back()->withErrors(['appointment' => 'Only pending or approved appointments can be rescheduled.']);
        }

        $data = $request->validate([
            'date'      => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time_slot' => ['required', 'date_format:H:i:s'],
        ]);

        if (! $appointment->doctor->user->is_active) {
            return back()->withErrors(['appointment' => 'That doctor is not accepting appointments right now.']);
        }

        if (! $slots->isBookable($appointment->doctor, $data['date'], $data['time_slot'], $appointment->id)) {
            return back()->withErrors(['time_slot' => 'That time is not available. Please choose another slot.']);
        }
        if ($this->studentIsBusy($student, $data['date'], $data['time_slot'], $appointment->id)) {
            return back()->withErrors(['time_slot' => 'You already have an appointment at that time.']);
        }

        try {
            // A moved appointment goes back to "pending" so the doctor re-confirms the new time
            $appointment->update([
                'date' => $data['date'], 'time_slot' => $data['time_slot'], 'status' => 'pending',
            ]);
        } catch (UniqueConstraintViolationException $e) {
            return back()->withErrors(['time_slot' => 'That slot was just taken by another student. Please pick a different one.']);
        }

        return back()->with('status', 'Appointment moved. The doctor will confirm the new time.');
    }

    /** A student may only touch their own appointments, whatever id is in the URL. */
    private function authorizeOwn(Appointment $appointment): void
    {
        abort_unless($appointment->student_id === $this->student()->id, 403);
    }

    private function studentIsBusy(Student $student, string $date, string $time, ?int $ignoreId = null): bool
    {
        return Appointment::where('student_id', $student->id)
            ->whereDate('date', $date)
            ->where('time_slot', $time)
            ->where('status', '!=', 'cancelled')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }
}
