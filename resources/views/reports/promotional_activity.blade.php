<x-app-layout>
<style>
  /* Summary strip */
  .fk-pa-summary { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-bottom: 16px; }
  .fk-pa-card {
    position: relative; overflow: hidden; padding: 14px 16px; border-radius: 14px; cursor: pointer;
    background: var(--fk-list-row); border: 1px solid var(--fk-list-border); transition: border-color .18s ease, transform .18s ease;
  }
  .fk-pa-card:hover { transform: translateY(-1px); border-color: var(--fk-list-border-strong); }
  .fk-pa-card.is-active { border-color: var(--fk-list-accent); box-shadow: 0 0 0 1px var(--fk-list-accent) inset; }
  .fk-pa-card::after { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: var(--pa-c, var(--fk-list-accent)); }
  .fk-pa-card span { display: block; color: var(--fk-list-muted); font-size: 11px; letter-spacing: .6px; text-transform: uppercase; }
  .fk-pa-card strong { display: block; margin-top: 4px; color: var(--fk-list-heading); font-family: 'Sora', 'Inter', sans-serif; font-size: 22px; font-weight: 700; }
  .fk-pa-card.is-money { cursor: default; }
  .fk-pa-card.is-money strong { font-size: 17px; }

  /* Table cells */
  #pa-report td { vertical-align: top; }
  .fk-pa-person b { display: block; font-weight: 500; color: var(--fk-list-text); white-space: nowrap; }
  .fk-pa-person small { display: block; color: var(--fk-list-muted); font-size: 11px; }
  .fk-pa-person em { display: block; margin-top: 3px; color: var(--fk-list-soft); font-size: 12px; font-style: normal; white-space: normal; max-width: 240px; }
  .fk-pa-chips { display: flex; flex-wrap: wrap; gap: 5px; max-width: 320px; }
  .fk-pa-chip { display: inline-flex; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: 11px; white-space: nowrap; }
  .fk-pa-chip.is-pop { color: #22d3ee; background: rgba(34,211,238,.10); border: 1px solid rgba(34,211,238,.30); }
  .fk-pa-chip.is-sample { color: #a78bfa; background: rgba(167,139,250,.10); border: 1px solid rgba(167,139,250,.30); }
  .fk-pa-money { white-space: nowrap; color: var(--fk-list-text); font-size: 12px; }
  .fk-pa-money span { display: inline-block; min-width: 42px; color: var(--fk-list-muted); font-size: 11px; }
  .fk-pa-money.is-over { color: #ff5d7a; }
  .fk-pa-muted { color: var(--fk-list-muted); font-size: 12px; }
  .fk-pa-legend { display: flex; gap: 14px; margin: 0 0 10px; font-size: 12px; color: var(--fk-list-muted); }
  .fk-pa-legend i { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin-right: 5px; vertical-align: middle; }

  .fk-pa-status { display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: .4px; white-space: nowrap; }
  .fk-pa-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
  .fk-pa-status.is-pending { color: #f59e0b; background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.32); }
  .fk-pa-status.is-approved { color: #22d3ee; background: rgba(34,211,238,.12); border: 1px solid rgba(34,211,238,.32); }
  .fk-pa-status.is-revision_required { color: #a78bfa; background: rgba(167,139,250,.12); border: 1px solid rgba(167,139,250,.32); }
  .fk-pa-status.is-rejected { color: #ff5d7a; background: rgba(255,93,122,.12); border: 1px solid rgba(255,93,122,.32); }
  .fk-pa-status.is-closure_pending { color: #3b82f6; background: rgba(59,130,246,.12); border: 1px solid rgba(59,130,246,.32); }
  .fk-pa-status.is-completed { color: #22c55e; background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.32); }
</style>

<section class="fk-manual-listing">
  <div class="fk-list-page-head">
    <div class="fk-list-heading-block">
      <div class="fk-list-breadcrumb"><span>REPORTS</span><span>›</span><span>USER</span><span>›</span><span class="fk-current">PROMOTIONAL ACTIVITY REPORT</span></div>
      <div class="fk-list-title-row"><h1 class="fk-list-title">Promotional Activity Report</h1><span id="pa-count" class="fk-list-count is-visible">0 records</span></div>
    </div>
    <div class="fk-list-actions">
      <button type="button" class="btn fk-filter-trigger" data-filter-target="#pa-filter-drawer"><span class="material-icons">tune</span><span>Filters</span></button>
      <button type="submit" form="pa-export-form" class="btn fk-create-action"><span class="material-icons">cloud_download</span><span>Download Excel</span></button>
    </div>
  </div>

  <aside class="fk-filter-drawer" id="pa-filter-drawer" aria-hidden="true">
    <div class="fk-filter-drawer-head">
      <div class="fk-filter-drawer-icon"><span class="material-icons">tune</span></div>
      <div><h3>Advanced Filters</h3><p>Applied to the report and the Excel</p></div>
      <button type="button" class="fk-filter-close" aria-label="Close filters"><span class="material-icons">close</span></button>
    </div>
    <div class="fk-filter-drawer-body">
      <form id="pa-export-form" method="GET" action="{{ route('reports.promotional_activity.download') }}">
        <input type="hidden" name="approval_status" id="pa_status">
        <div class="d-flex flex-wrap flex-row">
          <div class="p-2" data-label="Start Date"><input type="date" class="form-control" id="pa_start" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}"></div>
          <div class="p-2" data-label="End Date"><input type="date" class="form-control" id="pa_end" name="end_date" value="{{ now()->format('Y-m-d') }}"></div>
          <div class="p-2" data-label="User">
            <select name="user_id" id="pa_user" class="form-control select2">
              <option value="">All Users</option>
              @foreach($users as $item)<option value="{{ $item->id }}">{{ $item->name }}{{ $item->employee_codes ? ' ('.$item->employee_codes.')' : '' }}</option>@endforeach
            </select>
          </div>
          <div class="p-2" data-label="Distributor">
            <select name="distributor_id" id="pa_distributor" class="form-control select2">
              <option value="">All Distributors</option>
              @foreach($distributors as $item)<option value="{{ $item->id }}">{{ $item->name }}{{ $item->customer_code ? ' ('.$item->customer_code.')' : '' }}</option>@endforeach
            </select>
          </div>
          <div class="p-2" data-label="Activity Type">
            <select name="activity_type_id" id="pa_type" class="form-control select2">
              <option value="">All Activity Types</option>
              @foreach($activityTypes as $item)<option value="{{ $item->id }}">{{ $item->display_name ?: $item->status_name }}</option>@endforeach
            </select>
          </div>
          <div class="p-2" data-label="POP Material">
            <select name="material" id="pa_material" class="form-control select2">
              <option value="">All Materials</option>
              @foreach($gifts as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
            </select>
          </div>
          <div class="p-2" data-label="Zone">
            <select name="division_id" id="pa_zone" class="form-control select2">
              <option value="">All Zones</option>
              @foreach($divisions as $item)<option value="{{ $item->id }}">{{ $item->division_name }}</option>@endforeach
            </select>
          </div>
          <div class="p-2" data-label="Branch">
            <select name="branch_id" id="pa_branch" class="form-control select2">
              <option value="">All Branches</option>
              @foreach($branches as $item)<option value="{{ $item->id }}">{{ $item->branch_name }}</option>@endforeach
            </select>
          </div>
        </div>
      </form>
    </div>
    <div class="fk-filter-drawer-tools">
      <button class="btn fk-tool-export" type="submit" form="pa-export-form" style="grid-column: 1 / -1;"><span class="material-icons">cloud_download</span><span>Export Excel</span></button>
    </div>
    <div class="fk-filter-drawer-foot"><button class="btn fk-filter-reset" type="button">Reset</button><button class="btn fk-filter-apply" type="button">Apply Filters</button></div>
  </aside>

  {{-- Status cards double as a status filter (click again to clear) --}}
  <div class="fk-pa-summary" id="pa-summary">
    <div class="fk-pa-card is-active" data-status="" style="--pa-c:#22d3ee"><span>All Activities</span><strong data-k="total">0</strong></div>
    @php($statusColors = ['pending' => '#f59e0b', 'approved' => '#22d3ee', 'revision_required' => '#a78bfa', 'rejected' => '#ff5d7a', 'closure_pending' => '#3b82f6', 'completed' => '#22c55e'])
    @foreach($statuses as $key => $label)
    <div class="fk-pa-card" data-status="{{ $key }}" style="--pa-c:{{ $statusColors[$key] ?? '#22d3ee' }}"><span>{{ $label }}</span><strong data-k="{{ $key }}">0</strong></div>
    @endforeach
    <div class="fk-pa-card is-money" style="--pa-c:#64748b"><span>Estimated Budget</span><strong data-k="estimated_budget">0.00</strong></div>
    <div class="fk-pa-card is-money" style="--pa-c:#64748b"><span>Company + Distributor</span><strong data-k="total_amount">0.00</strong></div>
    <div class="fk-pa-card is-money" style="--pa-c:#64748b"><span>Actual Expense</span><strong data-k="actual_expense">0.00</strong></div>
  </div>

  <div class="card fk-listing-card" data-fk-listing-ready="1"><div class="card-body">
    <div class="fk-table-meta"><div class="fk-table-meta-icon"><span class="material-icons">campaign</span></div><div class="fk-table-meta-copy"><h2>Promotional Activities</h2><p id="pa-meta" class="fk-table-meta-subline">Live report · page 1 of 1</p></div></div>
    <div class="fk-pa-legend"><span><i style="background:#22d3ee"></i>POP material</span><span><i style="background:#a78bfa"></i>Sample material</span><span><i style="background:#ff5d7a"></i>▲ Actual over budget</span></div>
    <div class="table-responsive"><table id="pa-report" class="table fk-glass-table">
      <thead><tr>
        <th>No</th><th>Request ID</th><th>Activity Date</th><th>Activity Type</th><th>Created By</th><th>Zone</th><th>Branch</th>
        <th>Distributor</th><th>Location</th><th>Materials Requested</th><th>Budget</th><th>Status</th><th>Approved / Rejected By</th>
      </tr></thead><tbody></tbody>
    </table></div>
  </div></div>
</section>

<script>
$(function () {
  var table = $('#pa-report').DataTable({
    processing: true, serverSide: true, autoWidth: false, pageLength: 25, order: [],
    language: { searchPlaceholder: 'Request ID, user, distributor, material...' },
    ajax: {
      url: "{{ route('reports.promotional_activity') }}",
      data: function (d) {
        d.start_date = $('#pa_start').val(); d.end_date = $('#pa_end').val();
        d.user_id = $('#pa_user').val(); d.distributor_id = $('#pa_distributor').val();
        d.activity_type_id = $('#pa_type').val(); d.material = $('#pa_material').val();
        d.division_id = $('#pa_zone').val(); d.branch_id = $('#pa_branch').val();
        d.approval_status = $('#pa_status').val();
      }
    },
    columns: [
      { data: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'request_no', orderable: false, searchable: false, defaultContent: '-' },
      { data: 'activity_date_fmt', orderable: false, searchable: false },
      { data: 'activity_type', orderable: false, searchable: false },
      { data: 'employee', orderable: false, searchable: false },
      { data: 'zone', orderable: false, searchable: false },
      { data: 'branch', orderable: false, searchable: false },
      { data: 'distributor', orderable: false, searchable: false },
      { data: 'location', orderable: false, searchable: false },
      { data: 'materials', orderable: false, searchable: false },
      { data: 'budget', orderable: false, searchable: false },
      { data: 'status_badge', orderable: false, searchable: false },
      { data: 'approval', orderable: false, searchable: false }
    ]
  });

  table.on('xhr', function () {
    var json = table.ajax.json() || {}, s = json.summary || {}, counts = s.counts || {};
    $('#pa-summary [data-k]').each(function () {
      var k = $(this).data('k');
      if (k === 'total') $(this).text(s.total || 0);
      else if (k in s) $(this).text(s[k]);
      else $(this).text(counts[k] || 0);
    });
  });
  table.on('draw', function () {
    var info = table.page.info();
    $('#pa-count').text((info.recordsDisplay || 0) + ' records');
    $('#pa-meta').text('Live report · page ' + ((info.page || 0) + 1) + ' of ' + (info.pages || 1));
  });

  $('#pa-summary').on('click', '.fk-pa-card[data-status]', function () {
    var status = $(this).data('status');
    if ($(this).hasClass('is-active') && status) status = '';
    $('#pa_status').val(status);
    $('#pa-summary .fk-pa-card[data-status]').removeClass('is-active')
      .filter('[data-status="' + status + '"]').addClass('is-active');
    table.ajax.reload();
  });

  $('#pa-filter-drawer .fk-filter-apply').on('click', function () {
    table.ajax.reload();
    $('#pa-filter-drawer').removeClass('is-open');
    $('body').removeClass('fk-filter-open');
  });
  $('#pa-filter-drawer .fk-filter-reset').on('click', function () {
    setTimeout(function () {
      $('#pa_user, #pa_distributor, #pa_type, #pa_material, #pa_zone, #pa_branch').val('').trigger('change.select2');
      $('#pa_start').val("{{ now()->startOfMonth()->format('Y-m-d') }}");
      $('#pa_end').val("{{ now()->format('Y-m-d') }}");
      table.ajax.reload();
    }, 0);
  });
});
</script>
</x-app-layout>
