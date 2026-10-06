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
              <a href="javascript:void(0)" class="btn btn-just-icon btn-theme fk-preserve-list-action" data-toggle="modal" data-target="#giftModal" data-fk-action-label="Add New Gift" id="createGift" title="Add Gift">
                <i class="material-icons">add_circle</i>
              </a>
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
                <th>Opening Stock</th>
                <th>Added</th>
                <th>Issued</th>
                <th>Current Stock</th>
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
            <div class="col-md-12" id="openingStockSection">
              <div class="input_section">
                <label class="col-form-label">Opening Stock <span class="text-danger">*</span></label>
                <div class="form-group has-default bmd-form-group">
                  <input type="number" name="quantity" id="giftQuantity" class="form-control" min="0" step="1" required>
                </div>
                <small class="text-muted" id="openingStockHint" style="display:none"></small>
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

<div class="modal fade" id="addStockModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content card">
      <div class="card-header card-header-icon card-header-theme">
        <div class="card-icon"><i class="material-icons">add_box</i></div>
        <h4 class="card-title">Add Stock - <span id="addStockGiftName"></span>
          <span class="pull-right"><button type="button" class="btn btn-just-icon btn-danger" data-dismiss="modal"><i class="material-icons">clear</i></button></span>
        </h4>
      </div>
      <form id="addStockForm">
        <input type="hidden" id="addStockGiftId">
        <div class="modal-body">
          <div class="input_section">
            <label class="col-form-label">Quantity to Add <span class="text-danger">*</span></label>
            <div class="form-group has-default bmd-form-group">
              <input type="number" name="quantity" id="addStockQuantity" class="form-control" min="1" step="1" required>
            </div>
          </div>
          <div class="input_section">
            <label class="col-form-label">Remark</label>
            <div class="form-group has-default bmd-form-group">
              <input type="text" name="remark" id="addStockRemark" class="form-control" maxlength="255">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-theme" id="submitAddStock">Add Stock</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  #movementModal .gm-dialog { max-width: 1100px; width: calc(100% - 32px); margin: 40px auto; }
  #movementModal .gm-content { background: #0b1736 !important; border: 1px solid rgba(90,130,220,.28); border-radius: 16px; color: #e6ecff; box-shadow: 0 24px 80px rgba(0,0,0,.55); overflow: hidden; }
  #movementModal .gm-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 18px 22px; border-bottom: 1px solid rgba(90,130,220,.2); }
  #movementModal .gm-title { margin: 0; font-size: 20px; font-weight: 700; color: #fff; }
  #movementModal .gm-title small { display: block; font-size: 12px; font-weight: 500; color: #8fa1d2; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 2px; }
  #movementModal .gm-close { background: transparent; border: 1px solid rgba(90,130,220,.35); color: #cfe0ff; border-radius: 10px; width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }
  #movementModal .gm-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; padding: 16px 22px; }
  #movementModal .gm-stat { background: rgba(255,255,255,.04); border: 1px solid rgba(90,130,220,.18); border-radius: 12px; padding: 10px 14px; }
  #movementModal .gm-stat span { display: block; font-size: 11px; color: #8fa1d2; text-transform: uppercase; letter-spacing: .08em; }
  #movementModal .gm-stat b { font-size: 22px; color: #fff; }
  #movementModal .gm-table-wrap { max-height: 55vh; overflow: auto; padding: 0 22px 20px; }
  #movementModal table.gm-table { width: 100%; border-collapse: collapse; background: transparent !important; }
  #movementModal .gm-table th { position: sticky; top: 0; z-index: 1; background: #0b1736; color: #8fa1d2; font-size: 11px; letter-spacing: .08em; text-transform: uppercase; font-weight: 600; padding: 10px 12px; border-bottom: 1px solid rgba(90,130,220,.25); white-space: nowrap; text-align: left; }
  #movementModal .gm-table td { padding: 12px; border-bottom: 1px solid rgba(90,130,220,.12); color: #e6ecff; font-size: 14px; vertical-align: middle; background: transparent !important; }
  #movementModal .gm-table td.gm-num, #movementModal .gm-table th.gm-num { text-align: right; white-space: nowrap; }
  #movementModal .gm-in { color: #34d399; font-weight: 700; }
  #movementModal .gm-out { color: #f87171; font-weight: 700; }
  #movementModal .gm-pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; }
  #movementModal .gm-pill.opening { background: rgba(96,165,250,.16); color: #93c5fd; }
  #movementModal .gm-pill.add { background: rgba(52,211,153,.16); color: #6ee7b7; }
  #movementModal .gm-pill.activity { background: rgba(248,113,113,.16); color: #fca5a5; }
  #movementModal .gm-muted { color: #8fa1d2; font-size: 12px; }
  #movementModal .gm-empty { text-align: center; color: #8fa1d2; padding: 28px; }
  @media (max-width: 768px) {
    #movementModal .gm-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    #movementModal .gm-hide-sm { display: none; }
  }
</style>
<div class="modal fade" id="movementModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog gm-dialog" role="document">
    <div class="modal-content gm-content">
      <div class="gm-head">
        <h4 class="gm-title"><small>Stock Movement</small><span id="movementGiftName"></span></h4>
        <button type="button" class="gm-close" data-dismiss="modal" title="Close"><i class="material-icons">close</i></button>
      </div>
      <div class="gm-stats">
        <div class="gm-stat"><span>Opening Stock</span><b id="gmOpening">-</b></div>
        <div class="gm-stat"><span>Added</span><b id="gmAdded" class="gm-in">-</b></div>
        <div class="gm-stat"><span>Issued</span><b id="gmIssued" class="gm-out">-</b></div>
        <div class="gm-stat"><span>Current Stock</span><b id="gmCurrent">-</b></div>
      </div>
      <div class="gm-table-wrap">
        <table class="gm-table">
          <thead>
            <tr><th>Date</th><th>Type</th><th class="gm-num">Qty</th><th class="gm-num">Balance</th><th>Used / Added By</th><th class="gm-hide-sm">Details</th></tr>
          </thead>
          <tbody id="movementRows"></tbody>
        </table>
      </div>
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
      {data: 'opening_stock', name: 'opening_stock', searchable: false},
      {data: 'added_stock', name: 'added_stock', orderable: false, searchable: false},
      {data: 'issued_stock', name: 'issued_stock', orderable: false, searchable: false},
      {data: 'quantity', name: 'quantity', searchable: false},
      {data: 'creator.name', name: 'creator.name', defaultContent: '-'},
      {data: 'created_at', name: 'created_at'}
    ]
  });

  $('#createGift').on('click', function () {
    $('#giftForm').attr('action', "{{ route('promotional-gifts.store') }}")[0].reset();
    $('#formMethod').val('POST');
    $('#giftQuantity').attr('name', 'quantity').attr('min', 0);
    $('#openingStockHint').hide();
    $('#modalTitle').text('Add Gift');
    $('#submitGift').text('Create Gift');
  });

  $(document).on('click', '.editGift', function () {
    var id = $(this).data('id');
    $.get("{{ url('promotional-gifts') }}/" + id + '/edit', function (gift) {
      $('#giftName').val(gift.name);
      // Opening stock change moves current stock by the same difference
      var issuedSinceOpening = Math.max(0, gift.opening_stock - gift.quantity);
      $('#giftQuantity').attr('name', 'opening_stock').attr('min', issuedSinceOpening).val(gift.opening_stock);
      $('#openingStockHint').text('Current stock: ' + gift.quantity + '. Changing opening stock changes current stock by the same amount.').show();
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

  $(document).on('click', '.addStock', function () {
    $('#addStockForm')[0].reset();
    $('#addStockGiftId').val($(this).data('id'));
    $('#addStockGiftName').text($(this).data('name'));
    $('#addStockModal').modal('show');
  });

  $('#addStockForm').on('submit', function (e) {
    e.preventDefault();
    var $btn = $('#submitAddStock').prop('disabled', true);
    $.post("{{ url('promotional-gifts') }}/" + $('#addStockGiftId').val() + '/stock', $(this).serialize())
      .done(function (response) { $('#addStockModal').modal('hide'); showMessage(response.message, true); table.ajax.reload(null, false); })
      .fail(function (xhr) {
        var json = xhr.responseJSON || {};
        showMessage(json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Unable to add stock.'), false);
        $('#addStockModal').modal('hide');
      })
      .always(function () { $btn.prop('disabled', false); });
  });

  $(document).on('click', '.stockMovement', function () {
    $('#movementGiftName').text($(this).data('name'));
    $('#gmOpening, #gmAdded, #gmIssued, #gmCurrent').text('-');
    $('#movementRows').html('<tr><td colspan="6" class="gm-empty">Loading...</td></tr>');
    $('#movementModal').modal('show');
    $.get("{{ url('promotional-gifts') }}/" + $(this).data('id') + '/movements', function (data) {
      $('#gmOpening').text(data.opening_stock);
      $('#gmAdded').text(data.added);
      $('#gmIssued').text(data.issued);
      $('#gmCurrent').text(data.current_stock);
      var $rows = $('#movementRows').empty();
      if (!data.movements.length) {
        $rows.append('<tr><td colspan="6" class="gm-empty">No stock movement yet</td></tr>');
        return;
      }
      data.movements.forEach(function (m) {
        var qty = m.quantity > 0 ? '+' + m.quantity : String(m.quantity);
        $rows.append($('<tr>').append(
          $('<td>').text(m.date).css('white-space', 'nowrap'),
          $('<td>').append($('<span class="gm-pill">').addClass(m.type).text(m.type_label)),
          $('<td class="gm-num">').addClass(m.quantity < 0 ? 'gm-out' : 'gm-in').text(qty),
          $('<td class="gm-num">').text(m.balance),
          $('<td>').text(m.user),
          $('<td class="gm-hide-sm gm-muted">').text(m.details)
        ));
      });
    }).fail(function () { $('#movementRows').html('<tr><td colspan="6" class="gm-empty">Unable to load stock movement.</td></tr>'); });
  });

  function showMessage(message, success) {
    $('.ajax-message').removeClass('alert-success alert-danger').addClass(success ? 'alert-success' : 'alert-danger').show();
    $('.ajax-message .message').text(message);
  }

  @if($errors->any()) $('#giftModal').modal('show'); @endif
});
</script>
</x-app-layout>
