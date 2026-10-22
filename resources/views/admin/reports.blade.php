@extends('layouts.app')

@section('title', 'Reports')
@section('home', route('admin.dashboard'))

@section('content')
<div class="container my-4">

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 no-print">
    <h3 class="mb-0">Appointment Report</h3>
    <div>
      <a href="{{ route('admin.reports.csv', $filters) }}" class="btn btn-outline-success btn-sm">Export CSV</a>
      <button class="btn btn-outline-secondary btn-sm btn-print">Print</button>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
  </div>

  <div class="card p-3 mb-3 no-print">
    <form method="GET" action="{{ route('admin.reports') }}" class="row g-2 align-items-end">
      <div class="col-6 col-md-2">
        <label class="form-label mb-1">From</label>
        <input type="date" name="from" class="form-control" value="{{ $filters['from'] }}">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label mb-1">To</label>
        <input type="date" name="to" class="form-control" value="{{ $filters['to'] }}">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label mb-1">Doctor</label>
        <select name="doctor_id" class="form-select">
          <option value="">All doctors</option>
          @foreach ($doctors as $d)
            <option value="{{ $d->id }}" {{ (string) $filters['doctor_id'] === (string) $d->id ? 'selected' : '' }}>Dr. {{ $d->user->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label mb-1">Status</label>
        <select name="status" class="form-select">
          <option value="">All statuses</option>
          @foreach ($statuses as $st)
            <option value="{{ $st }}" {{ $filters['status'] === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-2 d-grid">
        <button class="btn btn-primary">Apply</button>
      </div>
    </form>
  </div>

  <div class="mb-3">
    <strong>{{ $total }}</strong> appointments from {{ $filters['from'] }} to {{ $filters['to'] }}
    @foreach ($statuses as $st)
      <span class="badge badge-{{ $st }} ms-1">{{ ucfirst($st) }}: {{ $summary->get($st, 0) }}</span>
    @endforeach
  </div>

  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead>
          <tr><th>Date</th><th>Time</th><th>Student</th><th>Reg No.</th><th>Doctor</th><th>Category</th><th>Status</th></tr>
        </thead>
        <tbody>
          @forelse ($appointments as $a)
            <tr>
              <td>{{ $a->date->format('Y-m-d') }}</td>
              <td>{{ substr($a->time_slot, 0, 5) }}</td>
              <td>{{ $a->student->user->name }}</td>
              <td>{{ $a->student->reg_number }}</td>
              <td>Dr. {{ $a->doctor->name }}</td>
              <td>{{ $a->category }}</td>
              <td><span class="badge badge-{{ $a->status }}">{{ ucfirst($a->status) }}</span></td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">No appointments match these filters.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($total > $limit)
      <p class="small text-muted mt-2 mb-0">Showing the latest {{ $limit }} of {{ $total }}. Narrow the filters or export the CSV for the full list.</p>
    @endif
  </div>

</div>
@endsection
