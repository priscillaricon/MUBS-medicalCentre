@extends('layouts.app')

@section('title', 'Book Appointment')
@section('home', route('student.dashboard'))

@section('content')
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card p-4">
        <h4 class="mb-3">Book an Appointment</h4>

        <form method="POST" action="{{ route('appointments.store') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label">Medical Category</label>
            <select name="category" class="form-select">
              @foreach ($categories as $category)
                <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Doctor</label>
            <select name="doctor_id" id="doctorSelect" class="form-select" required
                    data-slots-url="{{ route('student.slots', ['doctor' => '__ID__']) }}">
              <option value="">-- Select a doctor --</option>
              @foreach ($doctors as $doctor)
                <option value="{{ $doctor->id }}" {{ (string) old('doctor_id') === (string) $doctor->id ? 'selected' : '' }}>
                  Dr. {{ $doctor->name }} ({{ $doctor->specialization }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Date</label>
              <input type="date" name="date" id="dateInput" class="form-control future-date" value="{{ old('date') }}" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Time Slot</label>
              <select name="time_slot" id="slotSelect" class="form-select" required data-selected="{{ old('time_slot') }}">
                <option value="" disabled selected>Select doctor and date first</option>
              </select>
              <div id="slotHint" class="form-text"></div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Reason for Visit</label>
            <textarea name="reason" class="form-control" rows="2" maxlength="255"
                      placeholder="Briefly describe your symptoms or reason">{{ old('reason') }}</textarea>
          </div>

          <button type="submit" class="btn btn-primary">Submit Request</button>
          <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
