@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('home', route('student.dashboard'))

@section('content')
<div class="container my-4">

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h3>Welcome, {{ $student->user->name }}</h3>
      <p class="text-muted mb-0">{{ $student->reg_number }} &middot; {{ $student->course }}</p>
    </div>
    <div>
      <a href="{{ route('student.records') }}" class="btn btn-outline-primary me-2">My Medical Records</a>
      <a href="{{ route('student.idcard') }}" class="btn btn-outline-secondary me-2">My Patient ID</a>
      <a href="{{ route('student.book') }}" class="btn btn-primary">+ Book Appointment</a>
    </div>
  </div>

  <div class="card p-3">
    <h5 class="mb-3">My Appointments</h5>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr><th>Category</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          @forelse ($appointments as $appt)
            <tr>
              <td>{{ $appt->category }}</td>
              <td>Dr. {{ $appt->doctor->name }} <small class="text-muted">({{ $appt->doctor->specialization }})</small></td>
              <td>{{ $appt->date->format('Y-m-d') }}</td>
              <td>{{ substr($appt->time_slot, 0, 5) }}</td>
              <td><span class="badge badge-{{ $appt->status }}">{{ ucfirst($appt->status) }}</span></td>
              <td>
                @if ($appt->isCancellable() && $appt->date->gte(today()))
                  <button type="button" class="btn btn-sm btn-outline-primary btn-reschedule"
                          data-bs-toggle="modal" data-bs-target="#rescheduleModal"
                          data-action="{{ route('appointments.reschedule', $appt) }}"
                          data-appt="{{ $appt->id }}" data-doctor="{{ $appt->doctor_id }}"
                          data-doctor-name="Dr. {{ $appt->doctor->name }}">Reschedule</button>
                  <form method="POST" action="{{ route('appointments.cancel', $appt) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger btn-cancel-appt">Cancel</button>
                  </form>
                @elseif ($appt->sickSheet)
                  <a href="{{ route('student.sicksheet', $appt->sickSheet) }}" class="btn btn-sm btn-outline-success">View Sick Sheet</a>
                @else
                  <span class="text-muted">&mdash;</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">No appointments yet. Book one above.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

{{-- Reschedule modal: the form action is set by app.js to the clicked appointment's URL --}}
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="rescheduleForm" method="POST" action="#">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Reschedule appointment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="rsApptId">
        <input type="hidden" id="rsDoctor" data-slots-url="{{ route('student.slots', ['doctor' => '__ID__']) }}">
        <p class="mb-3">Doctor: <strong id="rsDoctorName"></strong></p>
        <div class="mb-3">
          <label class="form-label">New date</label>
          <input type="date" name="date" id="rsDate" class="form-control future-date" required>
        </div>
        <div class="mb-1">
          <label class="form-label">New time slot</label>
          <select name="time_slot" id="rsSlot" class="form-select" required>
            <option value="" disabled selected>Select a date first</option>
          </select>
          <div id="rsHint" class="form-text"></div>
        </div>
        <p class="small text-muted mt-3 mb-0">The doctor will need to confirm the new time.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Confirm new time</button>
      </div>
    </form>
  </div>
</div>
@endsection
