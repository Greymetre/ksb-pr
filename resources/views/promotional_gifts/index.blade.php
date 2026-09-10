<x-app-layout>
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header card-header-icon card-header-theme">
        <div class="card-icon"><i class="material-icons">redeem</i></div>
        <h4 class="card-title">Gift List
          <span class="btn-group header-frm-btn">
            @if(auth()->user()->hasRole('superadmin') || auth()->user()->can('promotional_gift_create'))
            <span class="next-btn">
              <button type="button" class="btn btn-just-icon btn-theme" data-toggle="modal" data-target="#giftModal" id="createGift" title="Add Gift">
                <i class="material-icons">add_circle</i>
              </button>
            </span>
            @endif
          </span>
        </h4>
      </div>
      <div class="card-body">
        @if($errors->any())
          <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert"><i class="material-icons">close</i></button>
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </div>
        @endif
        @if(session('message_success'))
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert"><i class="material-icons">close</i></button>
            <li>{{ session('message_success') }}</li>
          </div>
        @endif
        <div class="alert ajax-message" style="display:none">
          <button type="button" class="close" data-dismiss="alert"><i class="material-icons">close</i></button>
          <span class="message"></span>
        </div>
        <div class="table-responsive">
          <table id="giftTable" class="table table-striped- table-bordered table-hover table-checkable responsive no-wrap">
            <thead class="text-primary">
              <tr>
                <th>No.</th>
                <th>Status</th>
                <th>Action</th>
                <th>Gift Name</th>
                <th>Quantity</th>
                <th>Created By</th>
                <th>Created At</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="giftModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content card">
      <div class="card-header card-header-icon card-header-theme">
        <div class="card-icon"><i class="material-icons">redeem</i></div>
        <h4 class="card-title"><span id="modalTitle">Add Gift</span>
          <span class="pull-right"><button type="button" class="btn btn-just-icon btn-danger" data-dismiss="modal"><i class="material-icons">clear</i></button></span>
        </h4>
      </div>
      <form method="POST" id="giftForm" action="{{ route('promotional-gifts.store') }}">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="input_section">
                <label class="col-form-label">Gift Name <span class="text-danger">*</span></label>
                <div class="form-group has-default bmd-form-group">
                  <input type="text" name="name" id="giftName" class="form-control" maxlength="150" required>
                </div>
              </div>
            </div>
            <div class="col-md-12">
              <div class="input_section">
                <label class="col-form-label">Quantity <span class="text-danger">*</span></label>
                <div class="form-group has-default bmd-form-group">
                  <input type="number" name="quantity" id="giftQuantity" class="form-control" min="0" step="1" required>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-theme" id="submitGift">Create Gift</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript">
$(function () {
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  var table = $('#giftTable').DataTable({
    processing: true,
    serverSide: true,
    order: [[0, 'desc']],
    ajax: "{{ route('promotional-gifts.index') }}",
    columns: [
      {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
      {data: 'status_toggle', name: 'active', orderable: false, searchable: false},
      {data: 'action', name: 'action', orderable: false, searchable: false},
      {data: 'name', name: 'name'},
      {data: 'quantity', name: 'quantity'},
      {data: 'creator.name', name: 'creator.name', defaultContent: '-'},
      {data: 'created_at', name: 'created_at'}
    ]
  });

  $('#createGift').on('click', function () {
    $('#giftForm').attr('action', "{{ route('promotional-gifts.store') }}")[0].reset();
    $('#formMethod').val('POST');
    $('#modalTitle').text('Add Gift');
    $('#submitGift').text('Create Gift');
  });

  $(document).on('click', '.editGift', function () {
    var id = $(this).data('id');
    $.get("{{ url('promotional-gifts') }}/" + id + '/edit', function (gift) {
      $('#giftName').val(gift.name);
      $('#giftQuantity').val(gift.quantity);
      $('#giftForm').attr('action', "{{ url('promotional-gifts') }}/" + id);
      $('#formMethod').val('PUT');
      $('#modalTitle').text('Edit Gift');
      $('#submitGift').text('Update Gift');
      $('#giftModal').modal('show');
    });
  });

  $(document).on('click', '.deleteGift', function () {
    if (!confirm('Are you sure you want to delete this gift?')) return;
    $.ajax({
      url: "{{ url('promotional-gifts') }}/" + $(this).data('id'),
      type: 'DELETE',
      success: function (response) { showMessage(response.message, true); table.ajax.reload(null, false); },
      error: function () { showMessage('Unable to delete gift.', false); }
    });
  });

  $(document).on('change', '.activeRecord', function () {
    var checkbox = $(this);
    $.post("{{ url('promotional-gifts') }}/" + checkbox.data('id') + '/active')
      .done(function (response) { showMessage(response.message, true); table.ajax.reload(null, false); })
      .fail(function () { checkbox.prop('checked', !checkbox.prop('checked')); showMessage('Unable to update gift status.', false); });
  });

  function showMessage(message, success) {
    $('.ajax-message').removeClass('alert-success alert-danger').addClass(success ? 'alert-success' : 'alert-danger').show();
    $('.ajax-message .message').text(message);
  }

  @if($errors->any()) $('#giftModal').modal('show'); @endif
});
</script>
</x-app-layout>
