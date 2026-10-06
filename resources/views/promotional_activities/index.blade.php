<x-app-layout>
<style>
  body.fk-shell .fk-activity-status-tabs { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 14px 18px; border-bottom: 1px solid rgba(90, 130, 220, .22); }
  body.fk-shell .fk-activity-status-tab { --tab-color: #22d3ee; --tab-rgb: 34, 211, 238; height: 34px; display: inline-flex; align-items: center; gap: 8px; padding: 0 12px 0 14px; border-radius: 999px; border: 1px solid rgba(90, 130, 220, .28); background: rgba(8, 20, 50, .45); color: #a9bce6; font-size: 12px; font-weight: 700; letter-spacing: .3px; cursor: pointer; outline: none !important; box-shadow: none !important; transition: border-color .15s, background .15s, color .15s; }
  body.fk-shell .fk-activity-status-tab::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: var(--tab-color); }
  body.fk-shell .fk-activity-status-tab[data-status="pending"] { --tab-color: #ffb547; --tab-rgb: 255, 181, 71; }
  body.fk-shell .fk-activity-status-tab[data-status="approved"] { --tab-color: #4a9dff; --tab-rgb: 74, 157, 255; }
  body.fk-shell .fk-activity-status-tab[data-status="rejected"] { --tab-color: #ff5d7a; --tab-rgb: 255, 93, 122; }
  body.fk-shell .fk-activity-status-tab[data-status="completed"] { --tab-color: #12d18e; --tab-rgb: 18, 209, 142; }
  body.fk-shell .fk-activity-status-tab span { min-width: 24px; height: 20px; line-height: 20px; padding: 0 7px; border-radius: 999px; background: rgba(90, 130, 220, .18); color: #cbd9ff; font-size: 11px; text-align: center; }
  body.fk-shell .fk-activity-status-tab:hover { border-color: rgba(var(--tab-rgb), .5); color: #e3ecff; }
  body.fk-shell .fk-activity-status-tab.active { border-color: rgba(var(--tab-rgb), .6); background: rgba(var(--tab-rgb), .12); color: var(--tab-color); }
  body.fk-shell .fk-activity-status-tab.active span { background: rgba(var(--tab-rgb), .22); color: var(--tab-color); }
  body.fk-shell table.fk-glass-table tbody td .badge-info { border: 1px solid rgba(74, 157, 255, .38); background: rgba(74, 157, 255, .10); color: #4a9dff; }
</style>
<section class="fk-manual-listing">
  <div class="fk-list-page-head">
    <div class="fk-list-heading-block">
      <div class="fk-list-breadcrumb"><span>Marketing</span><span>&rsaquo;</span><span class="fk-current">Promotional Activity</span></div>
      <div class="fk-list-title-row"><h1 class="fk-list-title">Promotional Activity List</h1><span class="fk-list-count is-visible" id="activityRecordCount">0 records</span></div>
    </div>
    <div class="fk-list-actions"><button class="btn fk-filter-trigger" type="button" data-filter-target="#activityFilterDrawer"><span class="material-icons">tune</span><span>Filters</span></button></div>
  </div>
  <div class="card fk-listing-card" data-fk-listing-ready="1"><div class="card-body">
    <div class="fk-table-meta"><div class="fk-table-meta-icon"><span class="material-icons">campaign</span></div><div class="fk-table-meta-copy"><h2>Activity Directory</h2><p class="fk-table-meta-subline" id="activityTableMeta">Live directory · page 1 of 1</p></div></div>
    <div class="fk-activity-status-tabs" id="activityStatusTabs">
      <button type="button" class="fk-activity-status-tab active" data-status="">All <span data-count="all">0</span></button>
      <button type="button" class="fk-activity-status-tab" data-status="pending">Pending <span data-count="pending">0</span></button>
      <button type="button" class="fk-activity-status-tab" data-status="approved">Approved <span data-count="approved">0</span></button>
      <button type="button" class="fk-activity-status-tab" data-status="rejected">Rejected <span data-count="rejected">0</span></button>
      <button type="button" class="fk-activity-status-tab" data-status="completed">Completed <span data-count="completed">0</span></button>
    </div>
    <div class="table-responsive"><table class="table fk-glass-table" id="activityTable"><thead><tr>
      <th>#</th><th>Activity Date</th><th>Activity Type</th><th>Created By</th><th>Location</th><th>Status</th><th>Distributor</th><th>Total Amount</th><th>Participants</th><th>Created At</th>
    </tr></thead></table></div>
  </div></div>
</section>

<aside class="fk-filter-drawer" id="activityFilterDrawer">
  <div class="fk-filter-drawer-head"><div class="fk-filter-drawer-icon"><span class="material-icons">tune</span></div><div><h3>Advanced Filters</h3><p>Filter promotional activities</p></div><button type="button" class="fk-filter-close" aria-label="Close filters"><span class="material-icons">close</span></button></div>
  <div class="fk-filter-drawer-body">
    <div class="fk-filter-field"><label for="activity_type_filter">Activity Type</label><select id="activity_type_filter" class="form-control select2 fk-filter-control"><option value="">All activity types</option>@foreach($activityTypes as $type)<option value="{{ $type->id }}">{{ $type->display_name ?: $type->status_name }}</option>@endforeach</select></div>
    <div class="fk-filter-field"><label for="date_from">From Date</label><input type="date" id="date_from" class="form-control fk-filter-control"></div>
    <div class="fk-filter-field"><label for="date_to">To Date</label><input type="date" id="date_to" class="form-control fk-filter-control"></div>
  </div>
  @if(auth()->user()->hasRole('superadmin') || auth()->user()->can('promotional_activity_export'))
  <div class="fk-filter-drawer-tools"><a href="{{ route('promotional-activities-crm.export') }}" id="exportActivities" class="btn fk-tool-export"><span class="material-icons">cloud_download</span><span>Export</span></a></div>
  @endif
  <div class="fk-filter-drawer-foot"><button class="btn fk-filter-reset" id="resetActivityFilter" type="button">Reset</button><button class="btn fk-filter-apply" id="applyActivityFilter" type="button">Apply Filters</button></div>
</aside>

<div class="modal fade" id="activityDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content card">
      <div class="card-header card-header-icon card-header-theme">
        <div class="card-icon"><i class="material-icons">campaign</i></div>
        <h4 class="card-title">Promotional Activity Detail
          <span class="pull-right"><a href="javascript:void(0)" class="btn btn-just-icon btn-danger" data-dismiss="modal"><i class="material-icons">clear</i></a></span>
        </h4>
      </div>
      <div class="modal-body">
        <div id="activityDetailBody"><p class="text-center">Loading...</p></div>
        <div id="activityApprovalBox" style="display:none;">
          <hr>
          <div class="d-flex align-items-center justify-content-between mb-2">
            <small class="text-muted">Gifts (edit before approving)</small>
            <button type="button" class="btn btn-sm btn-info" id="addActivityGift">+ Add Gift</button>
          </div>
          <div id="activityGiftRows"></div>
          <div class="form-group">
            <label for="activityApprovalRemark" class="col-form-label">Remark</label>
            <textarea id="activityApprovalRemark" class="form-control" rows="3" maxlength="1000" placeholder="Add remark (required to reject)"></textarea>
          </div>
          <div class="text-right">
            <button type="button" class="btn btn-danger activity-approval-btn" data-status="rejected">Reject</button>
            <button type="button" class="btn btn-success activity-approval-btn" data-status="approved">Approve</button>
          </div>
        </div>
        <form id="activityCompleteBox" style="display:none;" enctype="multipart/form-data">
          <hr>
          <h5 class="mb-3">Complete Activity</h5>
          <div class="form-group">
            <label class="col-form-label">Distributor <span class="text-danger">*</span></label>
            <select id="activityDistributor" class="form-control" style="width:100%;"></select>
          </div>
          <div class="form-group">
            <label class="col-form-label">Activity Photos <span class="text-danger">*</span> <small class="text-muted">(1 to 3, max 5 MB each)</small></label>
            <input type="file" id="activityPhotos" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
          </div>
          <div class="d-flex align-items-center justify-content-between mb-2">
            <label class="col-form-label mb-0">Participants <span class="text-danger">*</span></label>
            <button type="button" class="btn btn-sm btn-info" id="addActivityParticipant">+ Add Participant</button>
          </div>
          <div id="activityParticipantRows"></div>
          <div class="form-group">
            <label for="activityExecutionRemark" class="col-form-label">Execution Remark</label>
            <textarea id="activityExecutionRemark" class="form-control" rows="3" maxlength="1000" placeholder="Add remark (optional)"></textarea>
          </div>
          <div class="text-right"><button type="submit" class="btn btn-success" id="completeActivityBtn">Complete Activity</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(function () {
  var activeStatus = '';
  function filters() { return { activity_type_id: $('#activity_type_filter').val(), date_from: $('#date_from').val(), date_to: $('#date_to').val(), approval_status: activeStatus }; }
  var table = $('#activityTable').DataTable({ processing: true, serverSide: true, order: [[1, 'desc']], ajax: { url: "{{ route('promotional-activities-crm.index') }}", data: function (data) { $.extend(data, filters()); } }, columns: [
    {data:'DT_RowIndex', orderable:false, searchable:false}, {data:'activity_date', name:'activity_date'}, {data:'activity_type_name', name:'activityType.display_name', orderable:false}, {data:'creator_name', name:'creator.name', orderable:false}, {data:'location_name', name:'location_name'}, {data:'approval_status', name:'approval_status'}, {data:'distributor_name', orderable:false, searchable:false}, {data:'total_amount', orderable:false, searchable:false}, {data:'participants_count', orderable:false, searchable:false}, {data:'created_at', name:'created_at'}
  ], drawCallback:function(){ var info=this.api().page.info(); $('#activityRecordCount').text((info.recordsDisplay||0)+' records'); $('#activityTableMeta').text('Live directory · page '+((info.page||0)+1)+' of '+(info.pages||1)); var counts=(this.api().ajax.json()||{}).status_counts||{}, total=0; $.each(['pending','approved','rejected','completed'], function(i,key){ var value=parseInt(counts[key]||0,10); total+=value; $('#activityStatusTabs [data-count="'+key+'"]').text(value); }); $('#activityStatusTabs [data-count="all"]').text(total); }});
  $('#activityStatusTabs').on('click', '.fk-activity-status-tab', function(){ activeStatus=$(this).data('status'); $('#activityStatusTabs .fk-activity-status-tab').removeClass('active'); $(this).addClass('active'); table.draw(); });
  var activeActivityId = null;
  function esc(value) { return $('<div>').text(value === null || value === undefined || value === '' ? '-' : value).html(); }
  function detailRow(label, value) { return '<div class="col-md-4 mb-3"><small class="text-muted d-block">' + label + '</small><strong>' + esc(value) + '</strong></div>'; }
  $('#activityTable tbody').css('cursor', 'pointer').on('click', 'tr', function () {
    var row = table.row(this).data();
    if (!row) return;
    activeActivityId = row.id;
    $('#activityDetailBody').html('<p class="text-center">Loading...</p>');
    $('#activityApprovalBox, #activityCompleteBox').hide();
    $('#activityApprovalRemark, #activityExecutionRemark, #activityPhotos').val('');
    $('#activityGiftRows, #activityParticipantRows').empty();
    $('#activityDistributor').val(null).trigger('change');
    $('#activityDetailModal').modal('show');
    $.get("{{ url('promotional-activities-crm') }}/" + row.id).done(function (response) {
      var d = response.data;
      var html = '<div class="row">' + detailRow('Activity Type', d.activity_type) + detailRow('Activity Date', d.activity_date) + detailRow('Location', d.location_name)
        + detailRow('Created By', d.creator) + detailRow('Reporting Manager', d.reporting_manager) + detailRow('Status', (d.approval_status || '').replace(/_/g, ' ').toUpperCase())
        + detailRow('Company Share', d.company_share) + detailRow('Distributor Share', d.distributor_share) + detailRow('Total Amount', d.total_amount)
        + detailRow('Created At', d.created_at) + (d.distributor ? detailRow('Distributor', d.distributor) : '')
        + (d.approved_rejected_by ? detailRow(d.approval_status === 'rejected' ? 'Rejected By' : 'Approved By', d.approved_rejected_by) + detailRow(d.approval_status === 'rejected' ? 'Rejected At' : 'Approved At', d.approved_rejected_at) : '') + '</div>'
        + '<div class="mb-3"><small class="text-muted d-block">Remark</small>' + esc(d.remark) + '</div>';
      if (d.approval_remark) html += '<div class="mb-3"><small class="text-muted d-block">Approval Remark</small>' + esc(d.approval_remark) + '</div>';
      if (d.gifts.length && !d.can_approve) html += '<div class="mb-3"><small class="text-muted d-block">Gifts</small>' + d.gifts.map(function (g) { return esc(g.name) + ' × ' + esc(g.quantity); }).join('<br>') + '</div>';
      if (d.participants.length) html += '<div class="mb-3"><small class="text-muted d-block">Participants</small>' + d.participants.map(function (p) { return esc(p.name) + ' (' + esc(p.mobile) + ') - ' + esc(p.address); }).join('<br>') + '</div>';
      if (d.execution_remark) html += '<div class="mb-3"><small class="text-muted d-block">Execution Remark</small>' + esc(d.execution_remark) + '</div>';
      if (d.photos.length) html += '<div class="mb-3"><small class="text-muted d-block">Photos</small>' + d.photos.map(function (url) { return '<a href="' + esc(url) + '" target="_blank"><img src="' + esc(url) + '" style="width:90px;height:90px;object-fit:cover;border-radius:8px;margin:4px 6px 0 0;"></a>'; }).join('') + '</div>';
      $('#activityDetailBody').html(html);
      availableGifts = d.available_gifts || [];
      if (d.can_approve) {
        d.gifts.forEach(function (g) { addGiftRow(g.id, g.quantity); });
        $('#activityApprovalBox').show();
      }
      if (d.can_complete) {
        addParticipantRow();
        $('#activityCompleteBox').show();
      }
    }).fail(function () {
      $('#activityDetailBody').html('<p class="text-center text-danger">Unable to load activity details.</p>');
    });
  });
  var availableGifts = [];
  function addGiftRow(giftId, quantity) {
    var options = '<option value="">Select gift</option>' + availableGifts.map(function (g) {
      return '<option value="' + g.id + '"' + (String(g.id) === String(giftId) ? ' selected' : '') + '>' + esc(g.name) + ' (stock ' + esc(g.quantity) + ')</option>';
    }).join('');
    $('#activityGiftRows').append('<div class="form-row align-items-center mb-2 activity-gift-row"><div class="col-7"><select class="form-control gift-id">' + options + '</select></div>'
      + '<div class="col-3"><input type="number" min="1" class="form-control gift-qty" placeholder="Qty" value="' + (quantity || 1) + '"></div>'
      + '<div class="col-2 text-right"><button type="button" class="btn btn-sm btn-danger remove-row">×</button></div></div>');
  }
  function addParticipantRow() {
    if ($('#activityParticipantRows .activity-participant-row').length >= 50) return;
    $('#activityParticipantRows').append('<div class="form-row align-items-center mb-2 activity-participant-row"><div class="col-md-3"><input type="text" maxlength="150" class="form-control participant-name" placeholder="Name"></div>'
      + '<div class="col-md-3"><input type="text" maxlength="10" class="form-control participant-mobile" placeholder="Mobile (10 digits)"></div>'
      + '<div class="col-md-5"><input type="text" maxlength="255" class="form-control participant-address" placeholder="Address"></div>'
      + '<div class="col-md-1 text-right"><button type="button" class="btn btn-sm btn-danger remove-row">×</button></div></div>');
  }
  $('#addActivityGift').on('click', function () { addGiftRow('', 1); });
  $('#addActivityParticipant').on('click', addParticipantRow);
  $('#activityDetailModal').on('click', '.remove-row', function () { $(this).closest('.form-row').remove(); });
  $('#activityDistributor').select2({
    dropdownParent: $('#activityDetailModal'), placeholder: 'Search distributor', allowClear: true,
    ajax: { url: function () { return "{{ url('promotional-activities-crm') }}/" + activeActivityId + '/distributors'; }, dataType: 'json', delay: 300, data: function (params) { return { search: params.term }; } }
  });
  function errorMessage(xhr, fallback) {
    var json = xhr.responseJSON || {};
    if (json.errors) return Object.values(json.errors)[0][0];
    return json.message || fallback;
  }
  $('.activity-approval-btn').on('click', function () {
    var status = $(this).data('status');
    if (status === 'rejected' && !$.trim($('#activityApprovalRemark').val())) { Swal.fire('Please add a remark to reject', '', 'warning'); return; }
    var gifts = [], giftError = false;
    $('#activityGiftRows .activity-gift-row').each(function () {
      var id = $(this).find('.gift-id').val(), qty = parseInt($(this).find('.gift-qty').val(), 10);
      if (!id || !(qty > 0)) giftError = true; else gifts.push({ gift_id: id, quantity: qty });
    });
    if (status === 'approved' && giftError) { Swal.fire('Select a gift and quantity in every gift row', '', 'warning'); return; }
    var buttons = $('.activity-approval-btn').prop('disabled', true);
    $.ajax({
      url: "{{ url('promotional-activities-crm') }}/" + activeActivityId + '/approval', method: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      data: { status: status, remark: $('#activityApprovalRemark').val(), gifts: gifts.length ? gifts : '' }
    }).done(function (response) {
      $('#activityDetailModal').modal('hide');
      Swal.fire(response.message, '', 'success');
      table.draw(false);
    }).fail(function (xhr) {
      Swal.fire(errorMessage(xhr, 'Unable to update activity'), '', 'error');
    }).always(function () { buttons.prop('disabled', false); });
  });
  $('#activityCompleteBox').on('submit', function (e) {
    e.preventDefault();
    var photos = $('#activityPhotos')[0].files, participants = [], participantError = false;
    $('#activityParticipantRows .activity-participant-row').each(function () {
      var p = { name: $.trim($(this).find('.participant-name').val()), mobile: $.trim($(this).find('.participant-mobile').val()), address: $.trim($(this).find('.participant-address').val()) };
      if (!p.name || !/^[0-9]{10}$/.test(p.mobile) || !p.address) participantError = true;
      participants.push(p);
    });
    if (!$('#activityDistributor').val()) { Swal.fire('Please select a distributor', '', 'warning'); return; }
    if (!photos.length || photos.length > 3) { Swal.fire('Please upload 1 to 3 photos', '', 'warning'); return; }
    if (!participants.length || participantError) { Swal.fire('Fill name, 10 digit mobile and address for every participant', '', 'warning'); return; }
    var formData = new FormData();
    formData.append('distributor_id', $('#activityDistributor').val());
    formData.append('participants', JSON.stringify(participants));
    formData.append('execution_remark', $.trim($('#activityExecutionRemark').val()));
    $.each(photos, function (i, file) { formData.append('photos[]', file); });
    var button = $('#completeActivityBtn').prop('disabled', true);
    $.ajax({
      url: "{{ url('promotional-activities-crm') }}/" + activeActivityId + '/complete', method: 'POST', data: formData, processData: false, contentType: false,
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    }).done(function (response) {
      $('#activityDetailModal').modal('hide');
      Swal.fire(response.message, '', 'success');
      table.draw(false);
    }).fail(function (xhr) {
      Swal.fire(errorMessage(xhr, 'Unable to complete activity'), '', 'error');
    }).always(function () { button.prop('disabled', false); });
  });
  $('#applyActivityFilter').on('click', function(){ table.draw(); });
  $('#resetActivityFilter').on('click', function(){ $('#activity_type_filter').val('').trigger('change'); $('#date_from,#date_to').val(''); table.draw(); });
  $('#exportActivities').on('click', function(e){ e.preventDefault(); window.location.href = "{{ route('promotional-activities-crm.export') }}?" + $.param(filters()); });
});
</script>
</x-app-layout>
