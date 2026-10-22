@extends('layouts.app')

@section('title', 'Patient Check-In')
@section('home', route(auth()->user()->dashboardRoute()))

@section('content')
<div class="container my-4">
  <h3 class="mb-4">Patient Check-In</h3>

  <div class="card p-4 mb-4">
    <form method="GET" action="{{ route('doctor.checkin') }}" class="d-flex gap-2">
      <input type="text" name="reg_number" class="form-control" maxlength="30"
             placeholder="Scan or type registration number" value="{{ $reg }}" autofocus>
      <button class="btn btn-primary">Look Up</button>
    </form>
    <small class="text-muted mt-2">Tip: a barcode scanner just types the code and hits Enter &mdash; this field works with one directly.</small>
  </div>

  @if ($error)
    <div class="alert alert-warning">{{ $error }}</div>
  @elseif ($student)
    <div class="card p-4">
      <h5>{{ $student->user->name }} <small class="text-muted">({{ $student->reg_number }})</small></h5>
      <p class="mb-1"><strong>Course:</strong> {{ $student->course }}</p>
      <p class="mb-1"><strong>Gender:</strong> {{ $student->gender }} &middot; <strong>Phone:</strong> {{ $student->phone ?: '-' }}</p>
      <p class="mb-3"><strong>Email:</strong> {{ $student->user->email }}</p>

      <hr>
      <h6>Today's Appointments</h6>
      @forelse ($appointments as $a)
        <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2 flex-wrap gap-2">
          <div>
            <strong>{{ substr($a->time_slot, 0, 5) }}</strong> &middot; {{ $a->category }}
            <span class="text-muted">with Dr. {{ $a->doctor->name }}</span>
          </div>
          <div>
            <span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span>
            @if (auth()->user()->isDoctor() && $a->status === 'approved')
              <a href="{{ route('doctor.consultation', $a) }}" class="btn btn-sm btn-primary ms-2">Start consultation</a>
            @endif
          </div>
        </div>
      @empty
        <p class="text-muted mb-0">No appointment scheduled for today{{ auth()->user()->isDoctor() ? ' with you' : '' }}.</p>
      @endforelse
    </div>
  @endif
</div>
@endsection
