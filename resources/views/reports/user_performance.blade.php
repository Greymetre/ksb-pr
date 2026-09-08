<x-app-layout>
<div class="row"><div class="col-md-12"><div class="card">
  <div class="card-header card-header-icon card-header-theme">
    <div class="card-icon"><i class="material-icons">assessment</i></div>
    <h4 class="card-title">User Performance Report</h4>
  </div>
  <div class="card-body">
    <div class="d-flex flex-wrap mb-3" style="gap:10px">
      <select id="user_id" class="form-control select2" style="width:190px"><option value="">All Users</option>@foreach($users as $item)<option value="{{$item->id}}">{{$item->name}}</option>@endforeach</select>
      <select id="designation_id" class="form-control select2" style="width:180px"><option value="">All Designations</option>@foreach($designations as $item)<option value="{{$item->id}}">{{$item->designation_name}}</option>@endforeach</select>
      <select id="division_id" class="form-control select2" style="width:160px"><option value="">All Zones</option>@foreach($divisions as $item)<option value="{{$item->id}}">{{$item->division_name}}</option>@endforeach</select>
      <select id="branch_id" class="form-control select2" style="width:170px"><option value="">All Branches</option>@foreach($branchs as $item)<option value="{{$item->id}}">{{$item->branch_name}}</option>@endforeach</select>
      <input class="form-control datepicker" style="width:140px" id="start_date" value="{{date('Y-m-01')}}" readonly>
      <input class="form-control datepicker" style="width:140px" id="end_date" value="{{date('Y-m-d')}}" readonly>
    </div>
    <div class="table-responsive"><table id="performance-report" class="table table-striped table-bordered table-hover no-wrap" style="width:100%">
      <thead class="text-primary"><tr>
        <th>No</th><th>Employees Code</th><th>Employees Name</th><th>Daily Visit Target</th><th>Total Working Days</th><th>Total Visit Target</th><th>Total Customers Visited</th><th>Adherence %</th><th>New Counter Added</th><th>Total Cumulative Counter</th><th>Secondary Orders (value)</th><th>Primary Target</th><th>Primary Achievement</th><th>Overdue</th><th>Payment Collection</th><th>Total Payment Dues</th><th>New Dealer Appointed</th><th>Orders Collected</th><th>Zone</th><th>Branch</th><th>Designation</th><th>Reporting Manager</th>
      </tr></thead>
    </table></div>
  </div>
</div></div></div>
<script>
$(function () {
  const money = $.fn.dataTable.render.number(',', '.', 2);
  const table = $('#performance-report').DataTable({
    processing: true, serverSide: false, scrollX: true,
    ajax: { url: "{{ url('reports/user_performance') }}", data: function (d) {
      d.user_id=$('#user_id').val(); d.designation_id=$('#designation_id').val(); d.division_id=$('#division_id').val(); d.branch_id=$('#branch_id').val(); d.start_date=$('#start_date').val(); d.end_date=$('#end_date').val();
    }},
    columns: [
      {data:'DT_RowIndex',orderable:false,searchable:false},{data:'employee_code',defaultContent:''},{data:'employee_name'},{data:'daily_visit_target'},{data:'working_days'},{data:'visit_target'},{data:'customers_visited'},{data:'adherence',render:function(v){return v+' %'}},{data:'new_counters'},{data:'cumulative_counters'},
      {data:'secondary_orders_value',render:money},{data:'primary_target',render:money},{data:'primary_achievement',render:money},{data:'overdue',render:money},{data:'payment_collection',render:money},{data:'total_payment_dues',render:money},{data:'new_dealers'},{data:'orders_collected'},{data:'zone',defaultContent:''},{data:'branch',defaultContent:''},{data:'designation',defaultContent:''},{data:'reporting_manager',defaultContent:''}
    ]
  });
  $('#user_id,#designation_id,#division_id,#branch_id,#start_date,#end_date').on('change', function(){ table.ajax.reload(); });
});
</script>
</x-app-layout>
