@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('home', route('admin.dashboard'))

@section('content')
<div class="container my-4">

  <h3 class="mb-4">Clinic Overview</h3>

  <div class="row g-3 mb-4">
    <div class="col-6 col-lg"><div class="card stat-card"><h2>{{ $stats['students'] }}</h2><small>Registered Students</small></div></div>
    <div class="col-6 col-lg"><div class="card stat-card"><h2>{{ $stats['doctors'] }}</h2><small>Doctors</small></div></div>
    <div class="col-6 col-lg"><div class="card stat-card"><h2>{{ $stats['today'] }}</h2><small>Appointments Today</small></div></div>
    <div class="col-6 col-lg"><div class="card stat-card"><h2>{{ $stats['pending'] }}</h2><small>Pending Appointments</small></div></div>
    <div class="col-6 col-lg"><div class="card stat-card"><h2>{{ $stats['completed'] }}</h2><small>Completed Consultations</small></div></div>
  </div>

  <div class="d-flex justify-content-end flex-wrap gap-2 mb-3">
    <a href="{{ route('doctor.checkin') }}" class="btn btn-outline-dark btn-sm">Patient Check-In</a>
    <a href="{{ route('admin.doctors') }}" class="btn btn-outline-primary btn-sm">Manage Doctors</a>
    <a href="{{ route('admin.schedules') }}" class="btn btn-outline-primary btn-sm">Schedules</a>
    <a href="{{ route('admin.analytics') }}" class="btn btn-outline-success btn-sm">Analytics</a>
    <a href="{{ route('admin.reports') }}" class="btn btn-outline-secondary btn-sm">Reports</a>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card p-3">
        <h5 class="mb-3">Appointments by Doctor</h5>
        <table class="table mb-0">
          <thead><tr><th>Doctor</th><th>Total</th><th>Completed</th><th>Pending</th></tr></thead>
          <tbody>
            @forelse ($doctorStats as $d)
              <tr>
                <td>Dr. {{ $d->user->name }} <small class="text-muted">({{ $d->specialization }})</small></td>
                <td>{{ $d->total_count }}</td>
                <td>{{ $d->completed_count }}</td>
                <td>{{ $d->pending_count }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted">No doctors yet. Add one under Manage Doctors.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card p-3">
        <h5 class="mb-3">Recent Registrations</h5>
        @forelse ($recentStudents as $s)
          <div class="border-bottom pb-2 mb-2">
            <strong>{{ $s->user->name }}</strong>
            <div class="small text-muted">{{ $s->reg_number }} &middot; {{ $s->course }} &middot; joined {{ $s->created_at->format('d M Y') }}</div>
          </div>
        @empty
          <p class="text-muted mb-0">No students have registered yet.</p>
        @endforelse
      </div>
    </div>
  </div>

</div>
@endsection
