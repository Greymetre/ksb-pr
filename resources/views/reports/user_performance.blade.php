<x-app-layout>
<section class="fk-manual-listing">
  <div class="fk-list-page-head">
    <div class="fk-list-heading-block">
      <div class="fk-list-breadcrumb"><span>REPORTS</span><span>›</span><span class="fk-current">USER PERFORMANCE REPORT</span></div>
      <div class="fk-list-title-row"><h1 class="fk-list-title">User Performance Report</h1><span id="performance-count" class="fk-list-count is-visible">0 records</span></div>
    </div>
    <div class="fk-list-actions">
      <button type="button" class="btn fk-filter-trigger" data-filter-target="#performance-filter-drawer"><span class="material-icons">tune</span><span>Filters</span></button>
    </div>
  </div>

  <aside class="fk-filter-drawer" id="performance-filter-drawer" aria-hidden="true">
    <div class="fk-filter-drawer-head">
      <div class="fk-filter-drawer-icon"><span class="material-icons">tune</span></div>
      <div><h3>Advanced Filters</h3><p>Apply filters or export the detailed report</p></div>
      <button type="button" class="fk-filter-close" aria-label="Close filters"><span class="material-icons">close</span></button>
    </div>
    <div class="fk-filter-drawer-body">
      <form id="performance-export-form" method="GET" action="{{ url('reports/user_performance/download') }}">
        <div class="d-flex flex-wrap flex-row">
          <div class="p-2" data-label="User"><select name="user_id" id="user_id" class="form-control select2"><option value="">All Users</option>@foreach($users as $item)<option value="{{$item->id}}">{{$item->name}}</option>@endforeach</select></div>
          <div class="p-2" data-label="Designation"><select name="designation_id" id="designation_id" class="form-control select2"><option value="">All Designations</option>@foreach($designations as $item)<option value="{{$item->id}}">{{$item->designation_name}}</option>@endforeach</select></div>
          <div class="p-2" data-label="Zone"><select name="division_id" id="division_id" class="form-control select2"><option value="">All Zones</option>@foreach($divisions as $item)<option value="{{$item->id}}">{{$item->division_name}}</option>@endforeach</select></div>
          <div class="p-2" data-label="Branch"><select name="branch_id" id="branch_id" class="form-control select2"><option value="">All Branches</option>@foreach($branchs as $item)<option value="{{$item->id}}">{{$item->branch_name}}</option>@endforeach</select></div>
          <div class="p-2" data-label="Start Date"><input type="text" class="form-control datepicker" id="start_date" name="start_date" value="{{date('Y-m-01')}}" readonly></div>
          <div class="p-2" data-label="End Date"><input type="text" class="form-control datepicker" id="end_date" name="end_date" value="{{date('Y-m-d')}}" readonly></div>
        </div>
      </form>
    </div>
    <div class="fk-filter-drawer-tools">
      <button class="btn fk-tool-export" type="submit" form="performance-export-form"><span class="material-icons">cloud_download</span><span>Export Full Report</span></button>
    </div>
    <div class="fk-filter-drawer-foot"><button class="btn fk-filter-reset" type="button">Reset</button><button class="btn fk-filter-apply" type="button">Apply Filters</button></div>
  </aside>

  <div class="card fk-listing-card" data-fk-listing-ready="1"><div class="card-body">
    <div class="fk-table-meta"><div class="fk-table-meta-icon"><span class="material-icons">people</span></div><div class="fk-table-meta-copy"><h2>User Performance Report</h2><p id="performance-meta" class="fk-table-meta-subline">Live directory · page 1 of 1</p></div></div>
    <div class="table-responsive"><table id="performance-report" class="table fk-glass-table">
      <thead><tr><th>No</th><th>Employees Code</th><th>Employees Name</th><th>Zone</th><th>Branch</th><th>Designation</th><th>Reporting Manager</th></tr></thead><tbody></tbody>
    </table></div>
  </div></div>
</section>
<script>
$(function () {
  var table = $('#performance-report').DataTable({
    processing:true, serverSide:true, autoWidth:false, pageLength:25,
    ajax:{url:"{{ url('reports/user_performance') }}",data:function(d){d.user_id=$('#user_id').val();d.designation_id=$('#designation_id').val();d.division_id=$('#division_id').val();d.branch_id=$('#branch_id').val();}},
    columns:[
      {data:'DT_RowIndex',name:'DT_RowIndex',orderable:false,searchable:false},
      {data:'employee_codes',name:'employee_codes',defaultContent:''},{data:'name',name:'name'},
      {data:'getdivision.division_name',name:'getdivision.division_name',defaultContent:'',orderable:false,searchable:false},
      {data:'getbranch.branch_name',name:'getbranch.branch_name',defaultContent:'',orderable:false,searchable:false},
      {data:'getdesignation.designation_name',name:'getdesignation.designation_name',defaultContent:'',orderable:false,searchable:false},
      {data:'reportinginfo.name',name:'reportinginfo.name',defaultContent:'',orderable:false,searchable:false}
    ]
  });
  function updateMeta(){var info=table.page.info(),total=info.recordsDisplay||0,pages=info.pages||1;$('#performance-count').text(total+' records');$('#performance-meta').text('Live directory · page '+((info.page||0)+1)+' of '+pages);}
  table.on('draw',updateMeta);
  $('#performance-filter-drawer .fk-filter-apply').on('click',function(){table.ajax.reload();});
  $('#performance-filter-drawer .fk-filter-reset').on('click',function(){setTimeout(function(){table.ajax.reload();},0);});
});
</script>
</x-app-layout>
