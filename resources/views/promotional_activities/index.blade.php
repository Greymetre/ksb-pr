<x-app-layout>
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

<script>
$(function () {
  function filters() { return { activity_type_id: $('#activity_type_filter').val(), date_from: $('#date_from').val(), date_to: $('#date_to').val() }; }
  var table = $('#activityTable').DataTable({ processing: true, serverSide: true, order: [[1, 'desc']], ajax: { url: "{{ route('promotional-activities-crm.index') }}", data: function (data) { $.extend(data, filters()); } }, columns: [
    {data:'DT_RowIndex', orderable:false, searchable:false}, {data:'activity_date', name:'activity_date'}, {data:'activity_type_name', name:'activityType.display_name', orderable:false}, {data:'creator_name', name:'creator.name', orderable:false}, {data:'location_name', name:'location_name'}, {data:'approval_status', name:'approval_status'}, {data:'distributor_name', orderable:false, searchable:false}, {data:'total_amount', orderable:false, searchable:false}, {data:'participants_count', orderable:false, searchable:false}, {data:'created_at', name:'created_at'}
  ], drawCallback:function(){ var info=this.api().page.info(); $('#activityRecordCount').text((info.recordsDisplay||0)+' records'); $('#activityTableMeta').text('Live directory · page '+((info.page||0)+1)+' of '+(info.pages||1)); }});
  $('#applyActivityFilter').on('click', function(){ table.draw(); });
  $('#resetActivityFilter').on('click', function(){ $('#activity_type_filter').val('').trigger('change'); $('#date_from,#date_to').val(''); table.draw(); });
  $('#exportActivities').on('click', function(e){ e.preventDefault(); window.location.href = "{{ route('promotional-activities-crm.export') }}?" + $.param(filters()); });
});
</script>
</x-app-layout>
