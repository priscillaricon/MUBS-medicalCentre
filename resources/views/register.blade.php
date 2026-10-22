@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card p-4">
        <h3 class="text-center mb-3">Student Registration</h3>

        {{-- Laravel: @if ($errors->any()) <div class="alert alert-danger">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div> @endif --}}

        {{-- Laravel: <form method="POST" action="{{ route('register') }}"> + @csrf --}}
        <form id="registerForm" method="POST" action="{{ route('register.store') }}">
      @csrf
          <div class="row form-section">
            <div class="col-md-6 mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Registration Number</label>
              <input type="text" name="reg_number" class="form-control" placeholder="e.g. 22/BSC/CS/001" value="{{ old('reg_number') }}" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Course</label>
              <input type="text" name="course" class="form-control" value="{{ old('course') }}" required>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Gender</label>
              <select name="gender" class="form-select" required>
                <option value="">Select</option>
                <option {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                <option {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                <option {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
              </select>
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" id="password" class="form-control" minlength="6" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="password_confirmation" id="password_confirm" class="form-control" minlength="6" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <p class="text-center mt-3 mb-0">Already have an account? <a href="{{ route('login') }}">Login</a></p>
      </div>
    </div>
  </div>
</div>
@endsection
