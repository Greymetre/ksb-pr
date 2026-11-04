<x-app-layout>
<style>
  body.fk-shell .fk-activity-status-tabs { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 14px 18px; border-bottom: 1px solid rgba(90, 130, 220, .22); }
  body.fk-shell .fk-activity-status-tab { --tab-color: #22d3ee; --tab-rgb: 34, 211, 238; height: 34px; display: inline-flex; align-items: center; gap: 8px; padding: 0 12px 0 14px; border-radius: 999px; border: 1px solid rgba(90, 130, 220, .28); background: rgba(8, 20, 50, .45); color: #a9bce6; font-size: 12px; font-weight: 700; letter-spacing: .3px; cursor: pointer; outline: none !important; box-shadow: none !important; transition: border-color .15s, background .15s, color .15s; }
  body.fk-shell .fk-activity-status-tab::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: var(--tab-color); }
  body.fk-shell .fk-activity-status-tab[data-status="pending"] { --tab-color: #ffb547; --tab-rgb: 255, 181, 71; }
  body.fk-shell .fk-activity-status-tab[data-status="approved"] { --tab-color: #4a9dff; --tab-rgb: 74, 157, 255; }
  body.fk-shell .fk-activity-status-tab[data-status="closure_pending"] { --tab-color: #2dd4bf; --tab-rgb: 45, 212, 191; }
  body.fk-shell .fk-activity-status-tab[data-status="revision_required"] { --tab-color: #c084fc; --tab-rgb: 192, 132, 252; }
  body.fk-shell .fk-activity-status-tab[data-status="rejected"] { --tab-color: #ff5d7a; --tab-rgb: 255, 93, 122; }
  body.fk-shell .fk-activity-status-tab[data-status="completed"] { --tab-color: #12d18e; --tab-rgb: 18, 209, 142; }
  body.fk-shell .fk-activity-status-tab span { min-width: 24px; height: 20px; line-height: 20px; padding: 0 7px; border-radius: 999px; background: rgba(90, 130, 220, .18); color: #cbd9ff; font-size: 11px; text-align: center; }
  body.fk-shell .fk-activity-status-tab:hover { border-color: rgba(var(--tab-rgb), .5); color: #e3ecff; }
  body.fk-shell .fk-activity-status-tab.active { border-color: rgba(var(--tab-rgb), .6); background: rgba(var(--tab-rgb), .12); color: var(--tab-color); }
  body.fk-shell .fk-activity-status-tab.active span { background: rgba(var(--tab-rgb), .22); color: var(--tab-color); }
  /* Alerts and confirms opened from the detail popup must show above it. */
  .swal2-container { z-index: 99999 !important; }
  body.fk-shell table.fk-glass-table tbody td .badge-info { border: 1px solid rgba(74, 157, 255, .38); background: rgba(74, 157, 255, .10); color: #4a9dff; }
  body.fk-shell table.fk-glass-table tbody td .badge-primary { border: 1px solid rgba(45, 212, 191, .38); background: rgba(45, 212, 191, .10); color: #2dd4bf; }
  body.fk-shell table.fk-glass-table tbody td .badge-secondary { border: 1px solid rgba(192, 132, 252, .38); background: rgba(192, 132, 252, .10); color: #c084fc; }
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
      <button type="button" class="fk-activity-status-tab" data-status="closure_pending">Closure Pending <span data-count="closure_pending">0</span></button>
      <button type="button" class="fk-activity-status-tab" data-status="revision_required">Revision Required <span data-count="revision_required">0</span></button>
      <button type="button" class="fk-activity-status-tab" data-status="rejected">Rejected <span data-count="rejected">0</span></button>
      <button type="button" class="fk-activity-status-tab" data-status="completed">Completed <span data-count="completed">0</span></button>
    </div>
    <div class="table-responsive"><table class="table fk-glass-table" id="activityTable"><thead><tr>
      <th>#</th><th>Request ID</th><th>Proposed Date</th><th>Activity Type</th><th>Created By</th><th>Location</th><th>Status</th><th>Distributor</th><th>Total Amount</th><th>Participants</th><th>Created At</th>
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
        <div id="activityDeleteBox" class="text-right" style="display:none;"><button type="button" class="btn btn-danger" id="deleteActivityBtn"><i class="material-icons" style="font-size:18px;vertical-align:middle;">delete</i> Delete Activity</button></div>
        <div id="activityApprovalBox" style="display:none;">
          <hr>
          <div class="d-flex align-items-center justify-content-between mb-2">
            <small class="text-muted">Gifts (edit before approving)</small>
            <button type="button" class="btn btn-sm btn-info" id="addActivityGift">+ Add Gift</button>
          </div>
          <div id="activityGiftRows"></div>
          <div class="form-group">
            <label for="activityApprovalRemark" class="col-form-label">Remark</label>
            <textarea id="activityApprovalRemark" class="form-control" rows="3" maxlength="1000" placeholder="Add remark (required to reject or send back)"></textarea>
          </div>
          <div class="text-right">
            <button type="button" class="btn btn-danger activity-approval-btn" data-status="rejected">Reject</button>
            <button type="button" class="btn btn-warning activity-approval-btn" data-status="revision_required">Send Back for Correction</button>
            <button type="button" class="btn btn-success activity-approval-btn" data-status="approved">Approve</button>
          </div>
        </div>
        <div id="activityReviewBox" style="display:none;">
          <hr>
          <h5 class="mb-3" style="color:#e3ecff;font-weight:700;">Review Activity</h5>
          <div class="form-group">
            <label for="activityReviewRemark" class="col-form-label">Review Remark</label>
            <textarea id="activityReviewRemark" class="form-control" rows="3" maxlength="1000" placeholder="Add remark (required to return for correction)"></textarea>
          </div>
          <div class="text-right">
            <button type="button" class="btn btn-warning activity-review-btn" data-status="returned">Return for Correction</button>
            <button type="button" class="btn btn-success activity-review-btn" data-status="closed">Close Activity</button>
          </div>
        </div>
        <form id="activityCompleteBox" style="display:none;" enctype="multipart/form-data">
          <hr>
          <h5 class="mb-3" style="color:#e3ecff;font-weight:700;">Activity Details &amp; Expenses</h5>
          <div class="form-group">
            <label class="col-form-label">Distributor <span class="text-danger">*</span></label>
            <select id="activityDistributor" class="form-control" style="width:100%;"><option value=""></option></select>
            <small class="text-muted d-block mt-1" id="activityDistributorInfo"></small>
          </div>
          <div class="form-group">
            <label class="col-form-label">Activity Photos <span class="text-danger">*</span> <small class="text-muted">(1 to 3, max 5 MB each)</small></label>
            <input type="file" id="activityPhotos" accept="image/jpeg,image/png,image/webp" multiple style="display:none;">
            <div><button type="button" class="btn btn-sm btn-info" id="chooseActivityPhotos"><i class="material-icons" style="font-size:18px;vertical-align:middle;">add_photo_alternate</i> Choose Photos</button>
            <small class="text-muted ml-2" id="activityPhotoCount">No photo selected</small></div>
            <div id="activityPhotoPreview" class="d-flex flex-wrap mt-2"></div>
          </div>
          <div class="form-group">
            <label for="activityActualExpense" class="col-form-label">Actual Expense (₹) <span class="text-danger">*</span></label>
            <input type="number" min="0" step="0.01" id="activityActualExpense" class="form-control" placeholder="0">
          </div>
          <div class="form-group">
            <label class="col-form-label">Bills / Invoices <small class="text-muted">(needed when expense is above 0; up to 5 images or PDFs, max 5 MB each)</small></label>
            <input type="file" id="activityBills" accept="image/jpeg,image/png,image/webp,application/pdf" multiple style="display:none;">
            <div><button type="button" class="btn btn-sm btn-info" id="chooseActivityBills"><i class="material-icons" style="font-size:18px;vertical-align:middle;">receipt_long</i> Choose Bills</button>
            <small class="text-muted ml-2" id="activityBillCount">No bill selected</small></div>
            <div id="activityBillList" class="mt-2"></div>
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
          <div class="text-right"><button type="submit" class="btn btn-success" id="completeActivityBtn">Submit for Review</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(function () {
  var activeStatus = '';
  function filters() { return { activity_type_id: $('#activity_type_filter').val(), date_from: $('#date_from').val(), date_to: $('#date_to').val(), approval_status: activeStatus }; }
  var table = $('#activityTable').DataTable({ processing: true, serverSide: true, order: [[2, 'desc']], ajax: { url: "{{ route('promotional-activities-crm.index') }}", data: function (data) { $.extend(data, filters()); } }, columns: [
    {data:'DT_RowIndex', orderable:false, searchable:false}, {data:'request_no', name:'request_no'}, {data:'activity_date', name:'activity_date'}, {data:'activity_type_name', name:'activityType.display_name', orderable:false}, {data:'creator_name', name:'creator.name', orderable:false}, {data:'location_name', name:'location_name'}, {data:'approval_status', name:'approval_status'}, {data:'distributor_name', orderable:false, searchable:false}, {data:'total_amount', orderable:false, searchable:false}, {data:'participants_count', orderable:false, searchable:false}, {data:'created_at', name:'created_at'}
  ], drawCallback:function(){ var info=this.api().page.info(); $('#activityRecordCount').text((info.recordsDisplay||0)+' records'); $('#activityTableMeta').text('Live directory · page '+((info.page||0)+1)+' of '+(info.pages||1)); var counts=(this.api().ajax.json()||{}).status_counts||{}, total=0; $.each(['pending','approved','closure_pending','revision_required','rejected','completed'], function(i,key){ var value=parseInt(counts[key]||0,10); total+=value; $('#activityStatusTabs [data-count="'+key+'"]').text(value); }); $('#activityStatusTabs [data-count="all"]').text(total); }});
  $('#activityStatusTabs').on('click', '.fk-activity-status-tab', function(){ activeStatus=$(this).data('status'); $('#activityStatusTabs .fk-activity-status-tab').removeClass('active'); $(this).addClass('active'); table.draw(); });
  var activeActivityId = null;
  function esc(value) { return $('<div>').text(value === null || value === undefined || value === '' ? '-' : value).html(); }
  // Images show as thumbnails like the photos; other files (PDF) as a link with the file name.
  function fileLinks(files) {
    return files.map(function (f) {
      if (/\.(jpe?g|png|webp)$/i.test(f.name)) return '<a href="' + esc(f.url) + '" target="_blank"><img src="' + esc(f.url) + '" style="width:90px;height:90px;object-fit:cover;border-radius:8px;margin:4px 6px 0 0;"></a>';
      return '<a href="' + esc(f.url) + '" target="_blank" class="d-inline-block mr-2 mt-1"><i class="material-icons" style="font-size:18px;vertical-align:middle;">picture_as_pdf</i> ' + esc(f.name) + '</a>';
    }).join('');
  }
  function detailRow(label, value) { return '<div class="col-md-4 mb-3"><small class="text-muted d-block">' + label + '</small><strong>' + esc(value) + '</strong></div>'; }
  $('#activityTable tbody').css('cursor', 'pointer').on('click', 'tr', function () {
    var row = table.row(this).data();
    if (!row) return;
    activeActivityId = row.id;
    $('#activityDetailBody').html('<p class="text-center">Loading...</p>');
    $('#activityApprovalBox, #activityCompleteBox, #activityDeleteBox, #activityReviewBox').hide();
    $('#activityApprovalRemark, #activityExecutionRemark, #activityReviewRemark, #activityActualExpense').val('');
    selectedPhotos = []; keptPhotos = []; renderPhotos();
    selectedBills = []; keptBills = []; renderBills();
    $('#activityGiftRows, #activityParticipantRows').empty();
    $('#activityDistributor').val('');
    $('#activityDetailModal').modal('show');
    $.get("{{ url('promotional-activities-crm') }}/" + row.id).done(function (response) {
      var d = response.data;
      var decisionLabel = { rejected: 'Rejected', revision_required: 'Sent Back' }[d.approval_status] || 'Approved';
      var html = '<div class="row">' + detailRow('Request ID', d.request_no) + detailRow('Activity Type', d.activity_type) + detailRow('Proposed Date', d.activity_date) + detailRow('Location', d.location_name)
        + detailRow('Created By', d.creator) + detailRow('Reporting Manager', d.reporting_manager) + detailRow('Status', (d.approval_status || '').replace(/_/g, ' ').toUpperCase())
        + detailRow('Estimated Budget', d.estimated_budget) + detailRow('Company Share', d.company_share) + detailRow('Distributor Share', d.distributor_share) + detailRow('Total Amount', d.total_amount)
        + detailRow('Created At', d.created_at) + (d.distributor ? detailRow('Distributor', d.distributor) : '')
        + (d.approved_rejected_by ? detailRow(decisionLabel + ' By', d.approved_rejected_by) + detailRow(decisionLabel + ' At', d.approved_rejected_at) : '') + '</div>'
        + '<div class="mb-3"><small class="text-muted d-block">Purpose</small>' + esc(d.purpose) + '</div>'
        + '<div class="mb-3"><small class="text-muted d-block">Remark</small>' + esc(d.remark) + '</div>';
      if (d.attachments.length) html += '<div class="mb-3"><small class="text-muted d-block">Attachments</small>' + fileLinks(d.attachments) + '</div>';
      if (d.approval_remark) html += '<div class="mb-3"><small class="text-muted d-block">Approval Remark</small>' + esc(d.approval_remark) + '</div>';
      if (d.gifts.length && !d.can_approve) html += '<div class="mb-3"><small class="text-muted d-block">Gifts</small>' + d.gifts.map(function (g) { return esc(g.name) + ' × ' + esc(g.quantity); }).join('<br>') + '</div>';
      if (d.participants.length) html += '<div class="mb-3"><small class="text-muted d-block">Participants</small>' + d.participants.map(function (p) { return esc(p.name) + ' (' + esc(p.mobile) + ') - ' + esc(p.address); }).join('<br>') + '</div>';
      if (d.execution_remark) html += '<div class="mb-3"><small class="text-muted d-block">Execution Remark</small>' + esc(d.execution_remark) + '</div>';
      if (d.actual_expense !== null) html += '<div class="row">' + detailRow('Actual Expense', d.actual_expense) + detailRow('Estimated Budget', d.estimated_budget) + detailRow('Submitted For Review', d.execution_submitted_at) + '</div>'
        + (d.over_budget ? '<div class="alert alert-warning py-2">Actual expense is over the estimated budget.</div>' : '');
      if (d.bills.length) html += '<div class="mb-3"><small class="text-muted d-block">Bills</small>' + fileLinks(d.bills) + '</div>';
      if (d.reviewed_by) html += '<div class="row">' + detailRow(d.approval_status === 'completed' ? 'Closed By' : 'Returned By', d.reviewed_by) + detailRow(d.approval_status === 'completed' ? 'Closed At' : 'Returned At', d.reviewed_at) + '</div>';
      if (d.review_remark) html += '<div class="mb-3"><small class="text-muted d-block">' + (d.approval_status === 'approved' ? 'Returned For Correction' : 'Review Remark') + '</small>' + esc(d.review_remark) + '</div>';
      if (d.photos.length) html += '<div class="mb-3"><small class="text-muted d-block">Photos</small>' + d.photos.map(function (url) { return '<a href="' + esc(url) + '" target="_blank"><img src="' + esc(url) + '" style="width:90px;height:90px;object-fit:cover;border-radius:8px;margin:4px 6px 0 0;"></a>'; }).join('') + '</div>';
      $('#activityDetailBody').html(html);
      availableGifts = d.available_gifts || [];
      if (d.can_approve) {
        d.gifts.forEach(function (g) { addGiftRow(g.id, g.quantity); });
        $('#activityApprovalBox').show();
      }
      if (d.can_delete) $('#activityDeleteBox').show();
      if (d.can_review) $('#activityReviewBox').show();
      if (d.can_complete) {
        // A submission the manager returned is prefilled for correction.
        if (d.participants.length) d.participants.forEach(function (p) { addParticipantRow(p); }); else addParticipantRow();
        $('#activityExecutionRemark').val(d.execution_remark || '');
        $('#activityActualExpense').val(d.actual_expense !== null ? String(d.actual_expense).replace(/,/g, '') : '');
        keptPhotos = d.photo_files || []; renderPhotos();
        keptBills = d.bills || []; renderBills();
        loadDistributors(d.id, d.distributor_id, d.distributor);
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
  function addParticipantRow(participant) {
    if ($('#activityParticipantRows .activity-participant-row').length >= 50) return;
    var row = $('<div class="form-row align-items-center mb-2 activity-participant-row"><div class="col-md-3"><input type="text" maxlength="150" class="form-control participant-name" placeholder="Name"></div>'
      + '<div class="col-md-3"><input type="text" maxlength="10" class="form-control participant-mobile" placeholder="Mobile (10 digits)"></div>'
      + '<div class="col-md-5"><input type="text" maxlength="255" class="form-control participant-address" placeholder="Address"></div>'
      + '<div class="col-md-1 text-right"><button type="button" class="btn btn-sm btn-danger remove-row">×</button></div></div>');
    if (participant && participant.name) {
      row.find('.participant-name').val(participant.name); row.find('.participant-mobile').val(participant.mobile); row.find('.participant-address').val(participant.address);
    }
    $('#activityParticipantRows').append(row);
  }
  $('#addActivityGift').on('click', function () { addGiftRow('', 1); });
  $('#addActivityParticipant').on('click', function () { addParticipantRow(); });
  $('#activityDetailModal').on('click', '.remove-row', function () { $(this).closest('.form-row').remove(); });
  // Distributors are loaded when the popup opens so the full list shows by default; select2 searches it locally.
  // select2 is set up only after the list loads: initialising it on page load lets the layout's global
  // $('.select2').select2() wrap our select2 container in a second, empty dropdown.
  function initDistributorSelect() {
    var select = $('#activityDistributor');
    if (select.hasClass('select2-hidden-accessible')) select.select2('destroy');
    select.select2({ dropdownParent: $('#activityDetailModal'), placeholder: 'Select distributor', allowClear: true, width: '100%' });
  }
  // The dealer chosen when the request was created is preselected.
  function loadDistributors(activityId, selectedId, selectedName) {
    var select = $('#activityDistributor');
    select.empty().append('<option value=""></option>');
    initDistributorSelect();
    $('#activityDistributorInfo').text('Loading distributors...');
    $.get("{{ url('promotional-activities-crm') }}/" + activityId + '/distributors').done(function (response) {
      if (String(activityId) !== String(activeActivityId)) return;
      (response.results || []).forEach(function (item) { select.append(new Option(item.text, item.id, false, false)); });
      if (selectedId && !select.find('option[value="' + selectedId + '"]').length) select.append(new Option(selectedName || ('Customer ' + selectedId), selectedId, false, false));
      if (selectedId) select.val(String(selectedId));
      initDistributorSelect();
      $('#activityDistributorInfo').text((response.results || []).length + ' distributors loaded');
      if (!(response.results || []).length) Swal.fire('No distributor found for this user', '', 'warning');
    }).fail(function (xhr) {
      $('#activityDistributorInfo').text('Unable to load distributors');
      Swal.fire(errorMessage(xhr, 'Unable to load distributors'), '', 'error');
    });
  }
  // keptPhotos / keptBills: files of a returned submission, kept unless removed.
  var selectedPhotos = [], keptPhotos = [];
  function renderPhotos() {
    var preview = $('#activityPhotoPreview').empty();
    keptPhotos.forEach(function (photo, index) {
      var item = $('<div style="position:relative;margin:0 8px 8px 0;"></div>');
      item.append($('<img style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid rgba(90,130,220,.35);">').attr('src', photo.url));
      item.append('<button type="button" class="btn btn-danger btn-sm remove-kept-photo" data-index="' + index + '" style="position:absolute;top:-8px;right:-8px;padding:0 7px;border-radius:50%;min-width:0;">×</button>');
      preview.append(item);
    });
    selectedPhotos.forEach(function (file, index) {
      var item = $('<div style="position:relative;margin:0 8px 8px 0;"></div>');
      item.append($('<img style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid rgba(90,130,220,.35);">').attr('src', URL.createObjectURL(file)));
      item.append('<button type="button" class="btn btn-danger btn-sm remove-photo" data-index="' + index + '" style="position:absolute;top:-8px;right:-8px;padding:0 7px;border-radius:50%;min-width:0;">×</button>');
      preview.append(item);
    });
    var count = keptPhotos.length + selectedPhotos.length;
    $('#activityPhotoCount').text(count ? count + ' of 3 photos selected' : 'No photo selected');
    $('#chooseActivityPhotos').prop('disabled', count >= 3);
  }
  $('#chooseActivityPhotos').on('click', function () { $('#activityPhotos').trigger('click'); });
  $('#activityPhotos').on('change', function () {
    var files = Array.prototype.slice.call(this.files || []);
    var room = 3 - keptPhotos.length - selectedPhotos.length, skipped = files.length > room;
    selectedPhotos = selectedPhotos.concat(files.slice(0, Math.max(room, 0)));
    $(this).val('');
    renderPhotos();
    if (skipped) Swal.fire('Only 3 photos can be uploaded', '', 'warning');
  });
  $('#activityPhotoPreview').on('click', '.remove-photo', function () { selectedPhotos.splice($(this).data('index'), 1); renderPhotos(); });
  $('#activityPhotoPreview').on('click', '.remove-kept-photo', function () { keptPhotos.splice($(this).data('index'), 1); renderPhotos(); });
  var selectedBills = [], keptBills = [];
  function renderBills() {
    var list = $('#activityBillList').empty();
    function billRow(name, cls, index) { return $('<div class="mb-1"></div>').text(name).append(' <button type="button" class="btn btn-danger btn-sm ' + cls + '" data-index="' + index + '" style="padding:0 7px;min-width:0;">×</button>'); }
    keptBills.forEach(function (bill, index) { list.append(billRow(bill.name, 'remove-kept-bill', index)); });
    selectedBills.forEach(function (file, index) { list.append(billRow(file.name, 'remove-bill', index)); });
    var count = keptBills.length + selectedBills.length;
    $('#activityBillCount').text(count ? count + ' of 5 bills selected' : 'No bill selected');
    $('#chooseActivityBills').prop('disabled', count >= 5);
  }
  $('#chooseActivityBills').on('click', function () { $('#activityBills').trigger('click'); });
  $('#activityBills').on('change', function () {
    var files = Array.prototype.slice.call(this.files || []);
    var room = 5 - keptBills.length - selectedBills.length, skipped = files.length > room;
    selectedBills = selectedBills.concat(files.slice(0, Math.max(room, 0)));
    $(this).val('');
    renderBills();
    if (skipped) Swal.fire('Only 5 bills can be uploaded', '', 'warning');
  });
  $('#activityBillList').on('click', '.remove-bill', function () { selectedBills.splice($(this).data('index'), 1); renderBills(); });
  $('#activityBillList').on('click', '.remove-kept-bill', function () { keptBills.splice($(this).data('index'), 1); renderBills(); });
  $('.activity-review-btn').on('click', function () {
    var status = $(this).data('status');
    if (status === 'returned' && !$.trim($('#activityReviewRemark').val())) { Swal.fire('Please add a remark saying what to correct', '', 'warning'); return; }
    var buttons = $('.activity-review-btn').prop('disabled', true);
    $.ajax({
      url: "{{ url('promotional-activities-crm') }}/" + activeActivityId + '/review', method: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      data: { status: status, remark: $('#activityReviewRemark').val() }
    }).done(function (response) {
      $('#activityDetailModal').modal('hide');
      Swal.fire(response.message, '', 'success');
      table.draw(false);
    }).fail(function (xhr) {
      Swal.fire(errorMessage(xhr, 'Unable to review activity'), '', 'error');
    }).always(function () { buttons.prop('disabled', false); });
  });
  function errorMessage(xhr, fallback) {
    var json = xhr.responseJSON || {};
    if (json.errors) return Object.values(json.errors)[0][0];
    return json.message || fallback;
  }
  $('.activity-approval-btn').on('click', function () {
    var status = $(this).data('status');
    if (status !== 'approved' && !$.trim($('#activityApprovalRemark').val())) { Swal.fire(status === 'rejected' ? 'Please add a remark to reject' : 'Please add a remark saying what to correct', '', 'warning'); return; }
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
    var participants = [], participantError = false, photoCount = keptPhotos.length + selectedPhotos.length;
    var expense = $.trim($('#activityActualExpense').val());
    $('#activityParticipantRows .activity-participant-row').each(function () {
      var p = { name: $.trim($(this).find('.participant-name').val()), mobile: $.trim($(this).find('.participant-mobile').val()), address: $.trim($(this).find('.participant-address').val()) };
      if (!p.name || !/^[0-9]{10}$/.test(p.mobile) || !p.address) participantError = true;
      participants.push(p);
    });
    if (!$('#activityDistributor').val()) { Swal.fire('Please select a distributor', '', 'warning'); return; }
    if (!photoCount || photoCount > 3) { Swal.fire('Please upload 1 to 3 photos', '', 'warning'); return; }
    if (expense === '' || !(parseFloat(expense) >= 0)) { Swal.fire('Please enter the actual expense', '', 'warning'); return; }
    if (parseFloat(expense) > 0 && !(keptBills.length + selectedBills.length)) { Swal.fire('Please upload at least one bill for the expense', '', 'warning'); return; }
    if (!participants.length || participantError) { Swal.fire('Fill name, 10 digit mobile and address for every participant', '', 'warning'); return; }
    var formData = new FormData();
    formData.append('distributor_id', $('#activityDistributor').val());
    formData.append('participants', JSON.stringify(participants));
    formData.append('execution_remark', $.trim($('#activityExecutionRemark').val()));
    formData.append('actual_expense', expense);
    $.each(selectedPhotos, function (i, file) { formData.append('photos[]', file); });
    $.each(keptPhotos, function (i, photo) { formData.append('keep_photos[]', photo.path); });
    $.each(selectedBills, function (i, file) { formData.append('bills[]', file); });
    $.each(keptBills, function (i, bill) { formData.append('keep_bills[]', bill.path); });
    var button = $('#completeActivityBtn').prop('disabled', true);
    $.ajax({
      url: "{{ url('promotional-activities-crm') }}/" + activeActivityId + '/complete', method: 'POST', data: formData, processData: false, contentType: false,
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    }).done(function (response) {
      $('#activityDetailModal').modal('hide');
      Swal.fire(response.message, '', 'success');
      table.draw(false);
    }).fail(function (xhr) {
      Swal.fire(errorMessage(xhr, 'Unable to submit activity details'), '', 'error');
    }).always(function () { button.prop('disabled', false); });
  });
  $('#deleteActivityBtn').on('click', function () {
    Swal.fire({
      title: 'Delete this activity?', text: "You won't be able to revert this! Gifts of a completed activity go back into stock.",
      type: 'warning', showCancelButton: true, confirmButtonText: 'Yes, delete it!', cancelButtonText: 'No, cancel!'
    }).then(function (result) {
      if (!result.value) return;
      var button = $('#deleteActivityBtn').prop('disabled', true);
      $.ajax({
        url: "{{ url('promotional-activities-crm') }}/" + activeActivityId, method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
      }).done(function (response) {
        $('#activityDetailModal').modal('hide');
        Swal.fire(response.message, '', 'success');
        table.draw(false);
      }).fail(function (xhr) {
        Swal.fire(errorMessage(xhr, 'Unable to delete activity'), '', 'error');
      }).always(function () { button.prop('disabled', false); });
    });
  });
  $('#applyActivityFilter').on('click', function(){ table.draw(); });
  $('#resetActivityFilter').on('click', function(){ $('#activity_type_filter').val('').trigger('change'); $('#date_from,#date_to').val(''); table.draw(); });
  $('#exportActivities').on('click', function(e){ e.preventDefault(); window.location.href = "{{ route('promotional-activities-crm.export') }}?" + $.param(filters()); });
});
</script>
</x-app-layout>
