@extends('layouts.app')

@section('title', 'Doctor Dashboard')
@section('home', route('doctor.dashboard'))

@section('content')
<div class="container my-4">

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h3>Dr. {{ $doctor->user->name }}</h3>
      <p class="text-muted mb-0">{{ $doctor->specialization }}@if ($doctor->room_number) &middot; {{ $doctor->room_number }}@endif</p>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2">
      <a href="{{ route('doctor.checkin') }}" class="btn btn-outline-secondary">Patient Check-In</a>
      <form method="GET" action="{{ route('doctor.dashboard') }}" class="d-flex">
        <input type="date" name="date" class="form-control me-2" value="{{ $date }}">
        <button class="btn btn-outline-primary">View</button>
      </form>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card stat-card"><h2>{{ $queue->count() }}</h2><small>Appointments on this day</small></div></div>
    <div class="col-6 col-md-3"><div class="card stat-card"><h2>{{ $counts->get('pending', 0) }}</h2><small>Pending</small></div></div>
    <div class="col-6 col-md-3"><div class="card stat-card"><h2>{{ $counts->get('approved', 0) }}</h2><small>Approved</small></div></div>
    <div class="col-6 col-md-3"><div class="card stat-card"><h2>{{ $counts->get('completed', 0) }}</h2><small>Completed</small></div></div>
  </div>

  @if ($pending->isNotEmpty())
    <div class="card p-3 mb-4 border-warning">
      <h5 class="mb-3">Requests waiting for your decision</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Date</th><th>Time</th><th>Student</th><th>Reg No.</th><th>Category</th><th>Reason</th><th>Actions</th></tr></thead>
          <tbody>
            @foreach ($pending as $p)
              <tr>
                <td>{{ $p->date->format('D, d M') }}</td>
                <td>{{ substr($p->time_slot, 0, 5) }}</td>
                <td>{{ $p->student->user->name }}</td>
                <td>{{ $p->student->reg_number }}</td>
                <td>{{ $p->category }}</td>
                <td>{{ $p->reason }}</td>
                <td>@include('doctor._respond-buttons', ['appt' => $p])</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  <div class="card p-3">
    <h5 class="mb-3">Queue for {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</h5>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr><th>Time</th><th>Student</th><th>Reg No.</th><th>Category</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          @forelse ($queue as $a)
            <tr>
              <td>{{ substr($a->time_slot, 0, 5) }}</td>
              <td>{{ $a->student->user->name }}</td>
              <td>{{ $a->student->reg_number }}</td>
              <td>{{ $a->category }}</td>
              <td>{{ $a->reason }}</td>
              <td><span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
              <td>
                @if ($a->status === 'pending')
                  @include('doctor._respond-buttons', ['appt' => $a])
                @elseif ($a->status === 'approved')
                  @if (! $blockFuture || $a->date->lte(today()))
                    <a href="{{ route('doctor.consultation', $a) }}" class="btn btn-sm btn-primary">Consult</a>
                  @else
                    <span class="text-muted small">Scheduled</span>
                  @endif
                @elseif ($a->status === 'completed')
                  <a href="{{ route('doctor.sicksheet', $a) }}" class="btn btn-sm btn-outline-success">{{ $a->sickSheet ? 'View Sick Sheet' : 'Issue Sick Sheet' }}</a>
                @else
                  <span class="text-muted">&mdash;</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">No appointments on this date.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
