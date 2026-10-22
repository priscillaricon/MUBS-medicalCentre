@extends('layouts.app')

@section('title', 'Doctor Schedules')
@section('home', route('admin.dashboard'))

@section('content')
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Doctor Schedules</h3>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
  </div>

  <div class="row">
    <div class="col-md-4">
      <div class="card p-3 mb-4">
        <h5>Add Availability</h5>
        <form method="POST" action="{{ route('admin.schedules.store') }}">
          @csrf
          <div class="mb-2">
            <label class="form-label">Doctor</label>
            <select name="doctor_id" class="form-select" required>
              @foreach ($doctors as $d)
                <option value="{{ $d->id }}" {{ (string) old('doctor_id') === (string) $d->id ? 'selected' : '' }}>Dr. {{ $d->user->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Day</label>
            <select name="day_of_week" class="form-select" required>
              @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                <option {{ old('day_of_week') === $day ? 'selected' : '' }}>{{ $day }}</option>
              @endforeach
            </select>
          </div>
          <div class="row">
            <div class="col-6 mb-2">
              <label class="form-label">Start</label>
              <input type="time" name="start_time" class="form-control" value="{{ old('start_time', '09:00') }}" required>
            </div>
            <div class="col-6 mb-2">
              <label class="form-label">End</label>
              <input type="time" name="end_time" class="form-control" value="{{ old('end_time', '16:00') }}" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Slot length</label>
            <select name="slot_duration" class="form-select">
              @foreach ([15, 30, 45, 60] as $mins)
                <option value="{{ $mins }}" {{ (int) old('slot_duration', 30) === $mins ? 'selected' : '' }}>{{ $mins }} min</option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">Add Block</button>
        </form>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card p-3">
        <h5>Current Schedules</h5>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>Doctor</th><th>Day</th><th>Hours</th><th>Slot</th><th></th></tr></thead>
            <tbody>
              @forelse ($schedules as $s)
                <tr>
                  <td>Dr. {{ $s->doctor->user->name }}</td>
                  <td>{{ $s->day_of_week }}</td>
                  <td>{{ substr($s->start_time, 0, 5) }} &ndash; {{ substr($s->end_time, 0, 5) }}</td>
                  <td>{{ $s->slot_duration_minutes }} min</td>
                  <td>
                    <form method="POST" action="{{ route('admin.schedules.destroy', $s) }}" class="d-inline"
                          onsubmit="return confirm('Remove this schedule block?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center text-muted">No schedules yet. Students cannot book until you add some.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
