@extends('layouts.app')

@section('title', 'Health Analytics')
@section('home', route('admin.dashboard'))

@section('content')
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="mb-0">Health Analytics</h3>
    <div class="no-print d-flex align-items-center gap-2">
      <form method="GET" action="{{ route('admin.analytics') }}">
        <select name="range" class="form-select" onchange="this.form.submit()">
          @foreach ([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'] as $days => $label)
            <option value="{{ $days }}" {{ $range === $days ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </form>
      <button class="btn btn-outline-secondary btn-sm btn-print">Print</button>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
  </div>

  @if ($total === 0)
    <div class="alert alert-info">No appointments in this period yet, so the charts are empty.</div>
  @endif

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card p-3"><h6>Appointments per day <small class="text-muted">(cancelled excluded)</small></h6><div class="chart-box"><canvas id="chartDaily"></canvas></div></div>
    </div>
    <div class="col-lg-4">
      <div class="card p-3"><h6>Status breakdown</h6><div class="chart-box"><canvas id="chartStatus"></canvas></div></div>
    </div>
    <div class="col-lg-6">
      <div class="card p-3"><h6>Most common diagnoses</h6><div class="chart-box"><canvas id="chartDiagnosis"></canvas></div></div>
    </div>
    <div class="col-lg-6">
      <div class="card p-3"><h6>Busiest weekdays</h6><div class="chart-box"><canvas id="chartWeekday"></canvas></div></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  var daily = @json($daily);
  var status = @json($status);
  var diagnoses = @json($diagnoses);
  var weekday = @json($weekday);
  var palette = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997', '#6c757d'];

  new Chart(document.getElementById('chartDaily'), {
    type: 'line',
    data: { labels: daily.labels, datasets: [{ label: 'Appointments', data: daily.data, borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,.12)', fill: true, tension: 0.3 }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
  new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: { labels: status.labels, datasets: [{ data: status.data, backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#dc3545'] }] },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
  });
  new Chart(document.getElementById('chartDiagnosis'), {
    type: 'bar',
    data: { labels: diagnoses.labels, datasets: [{ label: 'Cases', data: diagnoses.data, backgroundColor: palette }] },
    options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
  new Chart(document.getElementById('chartWeekday'), {
    type: 'bar',
    data: { labels: weekday.labels, datasets: [{ label: 'Appointments', data: weekday.data, backgroundColor: '#6f42c1' }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
</script>
@endpush
