@extends('layouts.app')

@section('title', 'Sick Sheet')
@section('home', route('doctor.dashboard'))

@section('content')
<div class="container my-4">

  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h3>{{ $sheet ? 'Sick Sheet' : 'Issue Sick Sheet' }}</h3>
    <div>
      @if ($sheet)
        <button class="btn btn-primary btn-sm btn-print">Print</button>
      @endif
      <a href="{{ route('doctor.dashboard', ['date' => $appointment->date->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">Back to Queue</a>
    </div>
  </div>

  @if ($sheet)
    @include('partials.sick-letter', ['sheet' => $sheet])
  @else
    <div class="row g-4">
      <div class="col-lg-5 no-print">
        <div class="card p-4">
          <form method="POST" action="{{ route('sicksheets.store', $appointment) }}">
            @csrf
            <p class="text-muted small">Patient: <strong>{{ $appointment->student->user->name }}</strong> ({{ $appointment->student->reg_number }})</p>
            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label">Rest from</label>
                <input type="date" name="rest_from" id="ssFrom" class="form-control"
                       value="{{ old('rest_from', $appointment->date->toDateString()) }}" required>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label">Rest until</label>
                <input type="date" name="rest_to" id="ssTo" class="form-control"
                       value="{{ old('rest_to', $appointment->date->copy()->addDays(2)->toDateString()) }}" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Remarks (optional)</label>
              <textarea name="remarks" id="ssRemarks" class="form-control" rows="3" maxlength="500"
                        placeholder="e.g. Advised to avoid strenuous physical activity.">{{ old('remarks') }}</textarea>
            </div>
            <div class="alert alert-info py-2 small">
              The diagnosis is <strong>not</strong> printed on this letter, to protect the student's confidentiality.
            </div>
            <button type="submit" class="btn btn-success">Save &amp; Issue</button>
          </form>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="letter">
          <div class="text-center mb-3">
            <h5 class="mb-0">MUBS Medical Centre</h5>
            <small class="text-muted">Makerere University Business School &middot; Nakawa, Kampala</small>
          </div>
          <hr>
          <div class="d-flex justify-content-between small mb-3">
            <span>Ref: <strong>assigned when issued</strong></span>
            <span>Date: <strong id="lIssued"></strong></span>
          </div>
          <h6 class="text-center text-uppercase mb-3">Medical Excuse Letter</h6>
          <p>To whom it may concern,</p>
          <p>This is to certify that <strong>{{ $appointment->student->user->name }}</strong>
             (Reg. No. <strong>{{ $appointment->student->reg_number }}</strong>, {{ $appointment->student->course }})
             was examined at the MUBS Medical Centre and is advised to rest from
             <strong id="lFrom"></strong> to <strong id="lTo"></strong>
             (<strong id="lDays"></strong> day<span id="lPlural">s</span>).</p>
          <p id="lRemarks" class="fst-italic"></p>
          <p>Kindly excuse the student from academic activities for this period.</p>
          <div class="mt-5">
            <div style="border-top:1px solid #000;width:220px"></div>
            <small>Dr. {{ $appointment->doctor->name }}<br>{{ $appointment->doctor->specialization }}, MUBS Medical Centre</small>
          </div>
        </div>
      </div>
    </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
  // Live preview: the letter updates as the doctor edits the form
  (function () {
    var from = document.getElementById('ssFrom'), to = document.getElementById('ssTo'), rem = document.getElementById('ssRemarks');
    if (!from || !to || !rem) return;   // read-only mode (letter already issued)
    function fmt(v) { return new Date(v + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); }
    function update() {
      if (!from.value || !to.value) return;
      var days = Math.round((new Date(to.value) - new Date(from.value)) / 86400000) + 1;
      document.getElementById('lFrom').textContent = fmt(from.value);
      document.getElementById('lTo').textContent = fmt(to.value);
      document.getElementById('lDays').textContent = days > 0 ? days : '-';
      document.getElementById('lPlural').textContent = days === 1 ? '' : 's';
      document.getElementById('lRemarks').textContent = rem.value;
    }
    [from, to, rem].forEach(function (el) { el.addEventListener('input', update); });
    document.getElementById('lIssued').textContent = fmt(new Date().toISOString().split('T')[0]);
    update();
  })();
</script>
@endpush
