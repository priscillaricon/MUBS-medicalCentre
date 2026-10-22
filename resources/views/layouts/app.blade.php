<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Welcome') - MUBS Medical Centre</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body>

@php
    // Real login wins. Until auth is wired, pages fall back to the demo name/role they declare.
    $navName = auth()->check() ? auth()->user()->name : trim($__env->yieldContent('demo_name'));
    $navRole = auth()->check() ? ucfirst(auth()->user()->role ?? 'user') : trim($__env->yieldContent('demo_role'));
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-primary no-print">
  <div class="container">
    <a class="navbar-brand" href="@yield('home', route('login'))">MUBS Medical Centre</a>
    @if ($navName !== '')
      <div class="d-flex align-items-center">
        <span class="text-white me-3">{{ $navName }} @if ($navRole !== '')({{ $navRole }})@endif</span>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
        </form>
      </div>
    @endif
  </div>
</nav>

@if (session('status') || $errors->any())
  <div class="container mt-3">
    @if (session('status'))
      <div class="alert alert-success mb-2">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger mb-0">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>
@endif

@yield('content')

<footer class="bg-light text-center text-muted py-3 mt-5 no-print">
  &copy; {{ date('Y') }} MUBS Medical Centre Management System
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
