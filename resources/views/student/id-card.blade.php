@extends('layouts.app')

@section('title', 'My Patient ID')
@section('home', route('student.dashboard'))

@section('content')
<div class="container my-4">

  <div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h3>My Patient ID</h3>
    <div>
      <button class="btn btn-primary btn-sm btn-print">Print Card</button>
      <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
  </div>

  <div class="id-card">
    <div class="text-center mb-3">
      <h5 class="mb-0">MUBS Medical Centre</h5>
      <small class="text-muted">Patient Identification Card</small>
    </div>
    <hr>
    <p class="mb-1"><strong>Name:</strong> {{ $student->user->name }}</p>
    <p class="mb-1"><strong>Reg. Number:</strong> {{ $student->reg_number }}</p>
    <p class="mb-3"><strong>Course:</strong> {{ $student->course }}</p>

    <div class="text-center">
      <svg id="barcode" data-regnumber="{{ $student->reg_number }}"></svg>
      <p class="mt-1 mb-0"><small>Present this at the front desk to check in</small></p>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
@endpush
