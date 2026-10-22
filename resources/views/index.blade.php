@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container auth-wrapper">
  <div class="row justify-content-center w-100">
    <div class="col-md-5">
      <div class="card p-4">
        <h3 class="text-center mb-3">Login</h3>

        {{-- Laravel: @if ($errors->any()) show them here; @session('status') for flash messages --}}

        {{-- Laravel: <form method="POST" action="{{ route('login') }}"> + @csrf --}}
        <form id="loginForm" method="POST" action="{{ route('login.attempt') }}">
      @csrf
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3 mb-0">
          No account? <a href="{{ route('register') }}">Register as a student</a>
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
