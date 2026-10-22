@extends('layouts.app')

@section('title', 'My Sick Sheet')
@section('home', route('student.dashboard'))

@section('content')
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h3>My Sick Sheet</h3>
    <div>
      <button class="btn btn-primary btn-sm btn-print">Print / Save as PDF</button>
      <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
  </div>

  <div class="letter mx-auto">
    <div class="text-center mb-3">
      <h5 class="mb-0">MUBS Medical Centre</h5>
      <small class="text-muted">Makerere University Business School &middot; Nakawa, Kampala</small>
    </div>
    <hr>
    <div class="d-flex justify-content-between small mb-3">
      <span>Ref: <strong>{{ $sheet->reference_no }}</strong></span>
      <span>Date: <strong>{{ $sheet->created_at->format('d M Y') }}</strong></span>
    </div>
    <h6 class="text-center text-uppercase mb-3">Medical Excuse Letter</h6>
    <p>To whom it may concern,</p>
    <p>This is to certify that <strong>{{ $sheet->student->user->name }}</strong>
       (Reg. No. <strong>{{ $sheet->student->reg_number }}</strong>, {{ $sheet->student->course }})
       was examined at the MUBS Medical Centre and is advised to rest from
       <strong>{{ $sheet->rest_from->format('d M Y') }}</strong> to
       <strong>{{ $sheet->rest_to->format('d M Y') }}</strong>
       (<strong>{{ $sheet->days }}</strong> {{ $sheet->days === 1 ? 'day' : 'days' }}).</p>
    @if ($sheet->remarks)
      <p class="fst-italic">{{ $sheet->remarks }}</p>
    @endif
    <p>Kindly excuse the student from academic activities for this period.</p>
    <div class="mt-5">
      <div style="border-top:1px solid #000;width:220px"></div>
      <small>Dr. {{ $sheet->doctor->name }}<br>{{ $sheet->doctor->specialization }}, MUBS Medical Centre</small>
    </div>
  </div>
</div>
@endsection
