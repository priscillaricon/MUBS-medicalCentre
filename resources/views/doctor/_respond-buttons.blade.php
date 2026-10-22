<form method="POST" action="{{ route('doctor.respond', $appt) }}" class="d-inline">
  @csrf
  <button name="action" value="accept" class="btn btn-sm btn-success">Accept</button>
  <button name="action" value="cancel" class="btn btn-sm btn-danger btn-cancel-appt">Cancel</button>
</form>
