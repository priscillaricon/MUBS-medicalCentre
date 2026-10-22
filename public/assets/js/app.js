// ============================================================
// MUBS Medical Centre - Shared Frontend JS
// Slot availability is fetched from the server (SlotService).
// ============================================================

function pad(n) { return (n < 10 ? '0' : '') + n; }
function toMinutes(t) { var p = t.split(':'); return parseInt(p[0], 10) * 60 + parseInt(p[1], 10); }

var slotRequestId = 0;

// Fills a <select> with a doctor's real slots for a date. Booked ones are greyed out.
// doctorEl must carry data-slots-url (".../doctors/__ID__/slots").
async function refreshSlots(doctorEl, dateEl, slotEl, hintEl, ignoreId) {
  var doctor = doctorEl.value, date = dateEl.value;
  var token = ++slotRequestId;               // lets us discard out-of-date responses

  function placeholder(text) {
    var o = document.createElement('option');
    o.value = ''; o.textContent = text; o.disabled = true; o.selected = true;
    slotEl.appendChild(o);
  }
  function hint(text, cls) { if (hintEl) { hintEl.textContent = text; hintEl.className = 'form-text ' + (cls || ''); } }

  slotEl.innerHTML = '';
  hint('');
  if (!doctor || !date) { placeholder('Select doctor and date first'); return; }
  placeholder('Loading...');

  var url = (doctorEl.getAttribute('data-slots-url') || '').replace('__ID__', encodeURIComponent(doctor))
          + '?date=' + encodeURIComponent(date);
  if (ignoreId) url += '&ignore=' + encodeURIComponent(ignoreId);

  try {
    var res = await fetch(url, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    });
    if (token !== slotRequestId) return;
    if (!res.ok) throw new Error('HTTP ' + res.status);
    var data = await res.json();
    if (token !== slotRequestId) return;

    slotEl.innerHTML = '';
    if (!data.working || data.free === 0) {
      placeholder(data.working ? 'No slots left on this date' : 'Doctor not available');
      hint(data.message, 'text-danger');
      return;
    }

    placeholder('-- Choose a time --');
    var wanted = slotEl.getAttribute('data-selected');   // keeps the choice after a failed submit
    data.slots.forEach(function (s) {
      var o = document.createElement('option');
      o.value = s.value;
      o.textContent = s.label + (s.booked ? ' (booked)' : '');
      if (s.booked) o.disabled = true;
      else if (wanted && wanted === s.value) o.selected = true;
      slotEl.appendChild(o);
    });
    hint(data.free + ' of ' + data.total + ' slots free (' + data.day + ', ' + data.window + ')', 'text-success');
  } catch (e) {
    if (token !== slotRequestId) return;
    slotEl.innerHTML = '';
    placeholder('Could not load slots');
    hint('Could not load available times. Check your connection and try again.', 'text-danger');
  }
}

function showFieldError(input, message) {
  var existing = input.parentElement.querySelector('.invalid-feedback');
  if (!existing) {
    existing = document.createElement('div');
    existing.className = 'invalid-feedback d-block';
    input.parentElement.appendChild(existing);
  }
  existing.textContent = message;
  input.classList.add('is-invalid');
  input.addEventListener('input', function () {
    input.classList.remove('is-invalid'); existing.textContent = '';
  }, { once: true });
}

document.addEventListener('DOMContentLoaded', function () {

  // 1. No booking dates in the past
  var today = new Date().toISOString().split('T')[0];
  document.querySelectorAll('input.future-date').forEach(function (el) { el.setAttribute('min', today); });

  // 2. Register: password confirmation
  var registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
      var pw = document.getElementById('password'), pw2 = document.getElementById('password_confirm');
      if (pw && pw2 && pw.value !== pw2.value) { e.preventDefault(); showFieldError(pw2, 'Passwords do not match.'); }
    });
  }

  // 3. Confirm before cancelling an appointment
  document.querySelectorAll('.btn-cancel-appt').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      if (!confirm('Cancel this appointment? This cannot be undone.')) e.preventDefault();
    });
  });

  // 4. Booking page: live slot availability from the server
  var doctorSelect = document.getElementById('doctorSelect');
  var dateInput = document.getElementById('dateInput');
  var slotSelect = document.getElementById('slotSelect');
  var slotHint = document.getElementById('slotHint');
  if (doctorSelect && dateInput && slotSelect) {
    var refresh = function () { refreshSlots(doctorSelect, dateInput, slotSelect, slotHint); };
    doctorSelect.addEventListener('change', function () { slotSelect.removeAttribute('data-selected'); refresh(); });
    dateInput.addEventListener('change', function () { slotSelect.removeAttribute('data-selected'); refresh(); });
    refresh();
  }

  // 5. Student dashboard: reschedule modal
  var rsForm = document.getElementById('rescheduleForm');
  var rsDoctor = document.getElementById('rsDoctor');
  var rsAppt = document.getElementById('rsApptId');
  var rsDate = document.getElementById('rsDate');
  var rsSlot = document.getElementById('rsSlot');
  var rsHint = document.getElementById('rsHint');
  if (rsForm && rsDoctor && rsDate && rsSlot) {
    document.querySelectorAll('.btn-reschedule').forEach(function (btn) {
      btn.addEventListener('click', function () {
        rsForm.action = btn.getAttribute('data-action');
        rsDoctor.value = btn.getAttribute('data-doctor');
        rsAppt.value = btn.getAttribute('data-appt');
        document.getElementById('rsDoctorName').textContent = btn.getAttribute('data-doctor-name');
        rsDate.value = '';
        refreshSlots(rsDoctor, rsDate, rsSlot, rsHint, rsAppt.value);
      });
    });
    rsDate.addEventListener('change', function () { refreshSlots(rsDoctor, rsDate, rsSlot, rsHint, rsAppt.value); });
  }

  // 6. Patient ID barcode (JsBarcode via CDN)
  var barcodeEl = document.getElementById('barcode');
  if (barcodeEl && window.JsBarcode) {
    JsBarcode('#barcode', barcodeEl.getAttribute('data-regnumber') || 'UNKNOWN',
      { format: 'CODE128', width: 2, height: 60, displayValue: true });
  }

  // 7. Print buttons
  document.querySelectorAll('.btn-print').forEach(function (btn) {
    btn.addEventListener('click', function () { window.print(); });
  });
});
