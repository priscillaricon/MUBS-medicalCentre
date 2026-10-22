@extends('layouts.app')

@section('title', 'My Medical Records')
@section('home', route('student.dashboard'))

@section('content')
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>My Medical Records</h3>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
  </div>

  @forelse ($records as $record)
    <div class="card p-3 mb-3">
      <div class="d-flex justify-content-between flex-wrap">
        <h6 class="mb-1">Dr. {{ $record->doctor->name }} <small class="text-muted">({{ $record->doctor->specialization }})</small></h6>
        <small class="text-muted">{{ $record->created_at->format('d M Y, H:i') }}</small>
      </div>
      <p class="mb-1"><strong>Diagnosis:</strong> {!! nl2br(e($record->diagnosis)) !!}</p>
      @if ($record->prescription)
        <p class="mb-1"><strong>Prescription:</strong> {!! nl2br(e($record->prescription)) !!}</p>
      @endif
      @if ($record->notes)
        <p class="mb-0 text-muted"><strong>Notes:</strong> {!! nl2br(e($record->notes)) !!}</p>
      @endif
    </div>
  @empty
    <div class="card p-4 text-center text-muted">
      No medical records yet. Records appear here after a completed consultation.
    </div>
  @endforelse
</div>
@endsection
