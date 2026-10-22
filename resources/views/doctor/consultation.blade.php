@extends('layouts.app')

@section('title', 'Consultation')
@section('home', route('doctor.dashboard'))

@section('content')
<div class="container my-4">
  <div class="row g-4">

    <div class="col-lg-7">
      <div class="card p-4">
        <h4>Consultation &mdash; {{ $appointment->student->user->name }}
          <small class="text-muted">({{ $appointment->student->reg_number }})</small></h4>
        <p class="text-muted mb-2">{{ $appointment->category }} &middot; {{ $appointment->date->format('Y-m-d') }} at {{ substr($appointment->time_slot, 0, 5) }}</p>
        @if ($appointment->reason)
          <p><strong>Reason for visit:</strong> {{ $appointment->reason }}</p>
        @endif

        <form method="POST" action="{{ route('consultations.store', $appointment) }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Diagnosis</label>
            <textarea name="diagnosis" class="form-control" rows="3" maxlength="2000" required>{{ old('diagnosis') }}</textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Prescription</label>
            <textarea name="prescription" class="form-control" rows="3" maxlength="2000">{{ old('prescription') }}</textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Additional Notes</label>
            <textarea name="notes" class="form-control" rows="2" maxlength="2000">{{ old('notes') }}</textarea>
          </div>
          <button type="submit" class="btn btn-success">Save &amp; Mark Completed</button>
          <a href="{{ route('doctor.dashboard', ['date' => $appointment->date->toDateString()]) }}" class="btn btn-outline-secondary">Back</a>
        </form>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card p-3">
        <h6 class="mb-3">Previous visits</h6>
        @forelse ($history as $h)
          <div class="border-bottom pb-2 mb-2">
            <div class="d-flex justify-content-between small text-muted">
              <span>Dr. {{ $h->doctor->name }}</span>
              <span>{{ $h->created_at->format('d M Y') }}</span>
            </div>
            <div><strong>{{ $h->diagnosis }}</strong></div>
            @if ($h->prescription)
              <div class="small text-muted">{{ $h->prescription }}</div>
            @endif
          </div>
        @empty
          <p class="text-muted mb-0">No earlier visits recorded for this student.</p>
        @endforelse
      </div>
    </div>

  </div>
</div>
@endsection
