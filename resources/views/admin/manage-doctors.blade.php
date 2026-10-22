@extends('layouts.app')

@section('title', 'Manage Doctors')
@section('home', route('admin.dashboard'))

@section('content')
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Manage Doctors</h3>
    <a href="{{ route('admin.schedules') }}" class="btn btn-outline-primary btn-sm">Manage Schedules</a>
  </div>

  <div class="row">
    <div class="col-lg-4">
      <div class="card p-3 mb-4">
        <h5>Add New Doctor</h5>
        <form method="POST" action="{{ route('admin.doctors.store') }}">
          @csrf
          <div class="mb-2">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Temporary Password</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
            <div class="form-text">At least 8 characters. Give it to the doctor privately.</div>
          </div>
          <div class="mb-2">
            <label class="form-label">Specialization</label>
            <input type="text" name="specialization" class="form-control" value="{{ old('specialization') }}" required>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label">Room</label>
              <input type="text" name="room_number" class="form-control" value="{{ old('room_number') }}">
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100">Add Doctor</button>
        </form>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card p-3">
        <h5>Current Doctors</h5>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>Name</th><th>Email</th><th>Specialization</th><th>Room</th><th>Status</th><th></th></tr></thead>
            <tbody>
              @forelse ($doctors as $doctor)
                <tr>
                  <td>Dr. {{ $doctor->user->name }}</td>
                  <td>{{ $doctor->user->email }}</td>
                  <td>{{ $doctor->specialization }}</td>
                  <td>{{ $doctor->room_number ?: '-' }}</td>
                  <td>
                    @if ($doctor->user->is_active)
                      <span class="badge badge-completed">Active</span>
                    @else
                      <span class="badge badge-cancelled">Deactivated</span>
                    @endif
                  </td>
                  <td>
                    <form method="POST" action="{{ route('admin.doctors.toggle', $doctor) }}" class="d-inline">
                      @csrf
                      @if ($doctor->user->is_active)
                        <button class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Deactivate this doctor? They will not be able to log in or be booked.')">Deactivate</button>
                      @else
                        <button class="btn btn-sm btn-outline-success">Activate</button>
                      @endif
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center text-muted">No doctors yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
