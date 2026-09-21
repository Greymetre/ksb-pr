<x-app-layout>
  <style>
    /* Odoo Sync page. Scoped to .os-page; uses the shell's --fk-list-* tokens so it follows dark/light mode. */
    body.fk-shell .os-page { --os-radius: 14px; color: var(--fk-list-text, #e8f0ff); padding-bottom: 32px; }
    body.fk-shell .os-page .os-muted { color: var(--fk-list-muted, #7d8fbf); }

    .os-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin: 4px 0 20px; }
    .os-breadcrumb { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: var(--fk-list-muted, #7d8fbf); margin-bottom: 6px; }
    .os-breadcrumb b { color: var(--fk-list-accent, #22d3ee); font-weight: 600; }
    .os-title { font-size: 26px; font-weight: 700; margin: 0; color: var(--fk-list-heading, #fff); display: flex; align-items: center; gap: 10px; }
    .os-title .material-icons { font-size: 26px; color: var(--fk-list-accent, #22d3ee); }
    .os-subtitle { margin: 6px 0 0; font-size: 13px; color: var(--fk-list-soft, #a9bce6); max-width: 720px; }
    .os-endpoint { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 10px; border: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); background: var(--fk-list-panel, rgba(8,20,50,.58)); font-size: 12px; color: var(--fk-list-soft, #a9bce6); }
    .os-endpoint code { color: var(--fk-list-text, #e8f0ff); background: none; padding: 0; font-size: 12px; }

    .os-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .os-stat { padding: 16px 18px; border-radius: var(--os-radius); border: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); background: var(--fk-list-panel, rgba(8,20,50,.58)); }
    .os-stat-label { font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: var(--fk-list-muted, #7d8fbf); display: flex; align-items: center; gap: 6px; }
    .os-stat-label .material-icons { font-size: 16px; }
    .os-stat-value { font-size: 26px; font-weight: 700; margin-top: 6px; color: var(--fk-list-heading, #fff); line-height: 1.1; }
    .os-stat-value.is-small { font-size: 17px; padding-top: 5px; }
    .os-stat-note { font-size: 12px; margin-top: 4px; color: var(--fk-list-soft, #a9bce6); }
    .os-stat-note.is-warning { color: #f59e0b; }
    .os-stat-note.is-danger { color: #f87171; }

    .os-panel { border-radius: var(--os-radius); border: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); background: var(--fk-list-panel, rgba(8,20,50,.58)); padding: 16px 18px; margin-bottom: 18px; }
    .os-panel-title { font-size: 12px; letter-spacing: .08em; text-transform: uppercase; color: var(--fk-list-muted, #7d8fbf); margin: 0 0 12px; font-weight: 600; }
    .os-keys { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; }
    .os-key { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); background: var(--fk-list-row, rgba(13,28,64,.62)); }
    .os-key-icon { width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; background: rgba(34,211,238,.1); color: var(--fk-list-accent, #22d3ee); flex: none; }
    .os-key-icon .material-icons { font-size: 19px; }
    .os-key-body { min-width: 0; flex: 1; }
    .os-key-name { font-weight: 600; color: var(--fk-list-heading, #fff); font-size: 14px; display: flex; align-items: center; gap: 8px; }
    .os-key-meta { font-size: 12px; color: var(--fk-list-muted, #7d8fbf); margin-top: 2px; }
    .os-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; background: #64748b; }
    .os-dot.is-on { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.18); }

    .os-tabs { display: inline-flex; gap: 4px; padding: 4px; border-radius: 12px; border: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); background: var(--fk-list-panel, rgba(8,20,50,.58)); margin-bottom: 14px; flex-wrap: wrap; }
    .os-tab { border: 0; background: transparent; color: var(--fk-list-soft, #a9bce6); padding: 8px 14px; border-radius: 9px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: background .15s, color .15s; }
    .os-tab:hover { color: var(--fk-list-heading, #fff); }
    .os-tab.is-active { background: var(--fk-list-accent, #22d3ee); color: var(--fk-list-primary-text, #04121f); }
    .os-tab-count { font-size: 11px; padding: 1px 7px; border-radius: 20px; background: rgba(125,143,191,.18); }
    .os-tab.is-active .os-tab-count { background: rgba(4,18,31,.16); }
    .os-pane { display: none; }
    .os-pane.is-active { display: block; }
    .os-pane-note { font-size: 12px; color: var(--fk-list-muted, #7d8fbf); margin: 0 0 10px; }

    /* Tables: override the shell's global table/dataTable rules inside this page only */
    .os-table-wrap { border-radius: var(--os-radius); border: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); background: var(--fk-list-panel, rgba(8,20,50,.58)); padding: 6px 0 4px; overflow: hidden; }
    .os-table-scroll { overflow-x: auto; }
    body.fk-shell .os-page table.os-table { width: 100% !important; min-width: 980px; margin: 0 !important; border-collapse: separate !important; border-spacing: 0 !important; background: transparent !important; }
    body.fk-shell .os-page table.os-table thead th { background: transparent !important; color: var(--fk-list-head-text, #8798ca) !important; font-size: 11px !important; font-weight: 600 !important; letter-spacing: .08em; text-transform: uppercase; padding: 12px 16px !important; border: 0 !important; border-bottom: 1px solid var(--fk-list-border, rgba(120,160,255,.14)) !important; white-space: nowrap; }
    body.fk-shell .os-page table.os-table tbody td { height: auto !important; padding: 12px 16px !important; background: transparent !important; color: var(--fk-list-cell, #cbd9ff) !important; font-size: 13px !important; vertical-align: middle !important; border: 0 !important; border-bottom: 1px solid var(--fk-list-border, rgba(120,160,255,.14)) !important; text-transform: none !important; box-shadow: none !important; white-space: nowrap; }
    body.fk-shell .os-page table.os-table tbody tr:last-child td { border-bottom: 0 !important; }
    body.fk-shell .os-page table.os-table tbody tr:hover td { background: var(--fk-list-row, rgba(13,28,64,.62)) !important; }
    body.fk-shell .os-page table.os-table td.dataTables_empty { text-align: center; padding: 40px 16px !important; color: var(--fk-list-muted, #7d8fbf) !important; }
    .os-strong { color: var(--fk-list-heading, #fff); font-weight: 600; }
    .os-sub { font-size: 12px; color: var(--fk-list-muted, #7d8fbf); margin-top: 2px; }
    .os-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12px; letter-spacing: 0; }
    .os-copy { cursor: copy; border-bottom: 1px dashed transparent; }
    .os-copy:hover { border-bottom-color: currentColor; }
    .os-price { font-size: 14px; font-weight: 700; color: var(--fk-list-heading, #fff); }
    .os-tag { font-size: 10px; letter-spacing: .06em; text-transform: uppercase; padding: 1px 6px; border-radius: 5px; background: rgba(125,143,191,.16); color: var(--fk-list-soft, #a9bce6); margin-left: 4px; }

    .os-pill { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: .04em; line-height: 1.5; border: 1px solid transparent; }
    .os-pill-sm { padding: 1px 8px; font-size: 10px; margin-top: 3px; }
    .os-pill-success { background: rgba(34,197,94,.14); color: #22c55e; border-color: rgba(34,197,94,.3); }
    .os-pill-warning { background: rgba(245,158,11,.14); color: #f59e0b; border-color: rgba(245,158,11,.3); }
    .os-pill-danger { background: rgba(248,113,113,.14); color: #f87171; border-color: rgba(248,113,113,.3); }
    .os-pill-neutral { background: rgba(125,143,191,.14); color: var(--fk-list-soft, #a9bce6); border-color: rgba(125,143,191,.28); }
    .os-pill-test { background: rgba(34,211,238,.12); color: #22d3ee; border-color: rgba(34,211,238,.3); text-transform: uppercase; }
    .os-pill-live { background: rgba(244,63,94,.14); color: #fb7185; border-color: rgba(244,63,94,.32); text-transform: uppercase; }

    .os-method { display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: .06em; padding: 2px 6px; border-radius: 5px; margin-right: 8px; background: rgba(59,130,246,.16); color: #60a5fa; }
    .os-method-get { background: rgba(34,197,94,.14); color: #22c55e; }
    .os-counts { display: flex; gap: 6px; flex-wrap: nowrap; }
    .os-count { font-size: 11px; padding: 2px 8px; border-radius: 6px; background: rgba(125,143,191,.1); color: var(--fk-list-muted, #7d8fbf); }
    .os-count b { font-weight: 700; }
    .os-count-neutral { color: var(--fk-list-heading, #fff); }
    .os-count-success { background: rgba(34,197,94,.14); color: #22c55e; }
    .os-count-info { background: rgba(59,130,246,.16); color: #60a5fa; }
    .os-count-warning { background: rgba(245,158,11,.14); color: #f59e0b; }
    .os-count-danger { background: rgba(248,113,113,.16); color: #f87171; }
    .os-link-btn { border: 1px solid rgba(248,113,113,.35); background: rgba(248,113,113,.1); color: #f87171; border-radius: 8px; padding: 4px 10px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; }
    .os-link-btn .material-icons { font-size: 15px; }
    .os-link-btn:hover { background: rgba(248,113,113,.18); }

    /* DataTables controls */
    .os-dt-top { display: flex; justify-content: flex-end; padding: 6px 16px 10px; }
    .os-page .dataTables_filter { float: none !important; margin: 0 !important; }
    .os-page .dataTables_filter label { margin: 0; font-size: 0; }
    .os-page .dataTables_filter input { font-size: 13px !important; width: 280px !important; max-width: 100%; height: 36px; padding: 0 12px 0 34px !important; border-radius: 9px !important; border: 1px solid var(--fk-list-border-strong, rgba(90,130,220,.28)) !important; background: var(--fk-list-control, rgba(8,20,50,.62)) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%237d8fbf' stroke-width='2' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='m20 20-3.5-3.5'/%3E%3C/svg%3E") no-repeat 11px center !important; color: var(--fk-list-text, #e8f0ff) !important; margin: 0 !important; box-shadow: none !important; }
    .os-dt-bottom { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 10px 16px 6px; border-top: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); }
    .os-page .dataTables_info { padding: 0 !important; font-size: 12px; color: var(--fk-list-muted, #7d8fbf) !important; }
    .os-page .dataTables_paginate { padding: 0 !important; margin: 0 !important; }
    .os-page .dataTables_processing { background: var(--fk-list-panel, #081a40) !important; color: var(--fk-list-text, #e8f0ff) !important; border: 1px solid var(--fk-list-border, rgba(120,160,255,.14)) !important; border-radius: 10px; }

    .os-toast { position: fixed; right: 24px; bottom: 24px; z-index: 2000; padding: 10px 14px; border-radius: 10px; background: #0f172a; color: #fff; font-size: 13px; box-shadow: 0 10px 30px rgba(0,0,0,.35); opacity: 0; transform: translateY(8px); transition: all .2s; pointer-events: none; }
    .os-toast.is-visible { opacity: 1; transform: none; }
    #osErrorsModal .modal-content { background: var(--fk-list-panel, #081a40); border: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); border-radius: 14px; color: var(--fk-list-text, #e8f0ff); }
    #osErrorsModal .modal-header { border-bottom: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); }
    #osErrorsModal pre { margin: 0; max-height: 60vh; overflow: auto; padding: 14px; border-radius: 10px; background: rgba(0,0,0,.25); color: var(--fk-list-text, #e8f0ff); font-size: 12px; white-space: pre-wrap; word-break: break-word; }

    @media (max-width: 991px) { .os-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575px) { .os-stats { grid-template-columns: 1fr; } .os-title { font-size: 22px; } }
  </style>

  <div class="os-page">
    <div class="os-head">
      <div>
        <div class="os-breadcrumb">Integrations › <b>Odoo Sync</b></div>
        <h1 class="os-title"><span class="material-icons">sync_alt</span>Odoo Sync</h1>
        <p class="os-subtitle">Everything Odoo has pushed to FieldKonnect. Data sent with a test key is kept separately and is never used by the app.</p>
      </div>
      <div class="os-endpoint"><span class="material-icons" style="font-size:16px">api</span><code>POST /api/v1/odoo/party-prices</code></div>
    </div>

    <div class="os-stats">
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">schedule</span>Last request</div>
        <div class="os-stat-value is-small">{{ $lastRequest ? \Carbon\Carbon::parse($lastRequest->created_at)->diffForHumans() : 'No requests yet' }}</div>
        <div class="os-stat-note">{{ $lastRequest ? \Carbon\Carbon::parse($lastRequest->created_at)->format('d M Y, h:i A') . ' · HTTP ' . $lastRequest->status_code : 'Waiting for Odoo' }}</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">error_outline</span>Failed records today</div>
        <div class="os-stat-value">{{ number_format($counts['failed_today']) }}</div>
        <div class="os-stat-note {{ $counts['failed_today'] > 0 ? 'is-danger' : '' }}">{{ $counts['failed_today'] > 0 ? 'See Request Logs for details' : 'All good' }}</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">science</span>Test prices</div>
        <div class="os-stat-value">{{ number_format($counts['test']) }}</div>
        <div class="os-stat-note {{ $counts['test_unlinked'] > 0 ? 'is-warning' : '' }}">{{ number_format($counts['test_unlinked']) }} not linked to a party/product</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">verified</span>Live prices</div>
        <div class="os-stat-value">{{ number_format($counts['live']) }}</div>
        <div class="os-stat-note">Used by FieldKonnect</div>
      </div>
    </div>

    <div class="os-panel">
      <div class="os-panel-title">API keys</div>
      <div class="os-keys">
        @forelse($clients as $client)
        <div class="os-key">
          <div class="os-key-icon"><span class="material-icons">vpn_key</span></div>
          <div class="os-key-body">
            <div class="os-key-name">{{ $client->name }}
              <span class="os-pill {{ $client->mode === 'live' ? 'os-pill-live' : 'os-pill-test' }} os-pill-sm" style="margin-top:0">{{ $client->mode }}</span>
            </div>
            <div class="os-key-meta">
              <span class="os-dot {{ $client->active ? 'is-on' : '' }}"></span>{{ $client->active ? 'Active' : 'Revoked' }}
              · Last used {{ $client->last_used_at ? \Carbon\Carbon::parse($client->last_used_at)->diffForHumans() : 'never' }}
            </div>
          </div>
        </div>
        @empty
        <div class="os-muted">No API key created yet.</div>
        @endforelse
      </div>
    </div>

    <div class="os-tabs" role="tablist">
      <button type="button" class="os-tab is-active" data-pane="logs"><span class="material-icons" style="font-size:17px">receipt_long</span>Request Logs <span class="os-tab-count">{{ number_format($counts['logs']) }}</span></button>
      <button type="button" class="os-tab" data-pane="test"><span class="material-icons" style="font-size:17px">science</span>Test Prices <span class="os-tab-count">{{ number_format($counts['test']) }}</span></button>
      <button type="button" class="os-tab" data-pane="live"><span class="material-icons" style="font-size:17px">verified</span>Live Prices <span class="os-tab-count">{{ number_format($counts['live']) }}</span></button>
    </div>

    <div class="os-pane is-active" id="osPaneLogs">
      <p class="os-pane-note">One row per API call. Click a correlation ID to copy it, and quote it when reporting a problem.</p>
      <div class="os-table-wrap">
        <div class="os-table-scroll">
          <table id="osLogsTable" class="os-table">
            <thead><tr><th>Time</th><th>Request</th><th>Key</th><th>Result</th><th>HTTP</th><th>Errors</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

    @foreach(['test' => 'Stored with the test key. Not used by FieldKonnect.', 'live' => 'Stored with the live key. These are the real party-wise prices.'] as $pane => $note)
    <div class="os-pane" id="osPane{{ ucfirst($pane) }}">
      <p class="os-pane-note">{{ $note }} <b>Not linked</b> means the code was not found in FieldKonnect.</p>
      <div class="os-table-wrap">
        <div class="os-table-scroll">
          <table id="os{{ ucfirst($pane) }}Table" class="os-table">
            <thead><tr><th>External ID</th><th>Party</th><th>Product</th><th>Price list</th><th>Price</th><th>Qty slab</th><th>Validity</th><th>Priority</th><th>Status</th><th>Received</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <div class="modal fade" id="osErrorsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title mb-0">Errors / skipped records</h5>
            <small class="os-muted os-mono" id="osErrorsCorrelation"></small>
          </div>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:inherit"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p class="os-muted" style="font-size:12px">Each item shows the record's position in the request (<code>index</code>), its <code>external_id</code>, and why it failed or was skipped.</p>
          <pre id="osErrorsBody"></pre>
        </div>
      </div>
    </div>
  </div>

  <div class="os-toast" id="osToast">Copied</div>

  <script type="text/javascript">
    $(function() {
      var dtDefaults = {
        processing: true,
        serverSide: true,
        pageLength: 25,
        lengthChange: false,
        autoWidth: false,
        dom: '<"os-dt-top"f>t<"os-dt-bottom"ip>',
        language: {
          search: '',
          searchPlaceholder: 'Search…',
          emptyTable: 'Nothing received yet',
          zeroRecords: 'No matching records',
          info: 'Showing _START_–_END_ of _TOTAL_',
          infoEmpty: 'No records',
          infoFiltered: '(filtered from _MAX_)',
          processing: 'Loading…'
        }
      };

      var priceColumns = [
        { data: 'external_id', name: 'pp.external_id' },
        { data: 'party', name: 'pp.party_code' },
        { data: 'product', name: 'pp.product_code' },
        { data: 'price_list_code', name: 'pp.price_list_code' },
        { data: 'price', name: 'pp.party_price', searchable: false },
        { data: 'slab', name: 'pp.minimum_quantity', searchable: false },
        { data: 'validity', name: 'pp.valid_from', searchable: false },
        { data: 'priority', name: 'pp.priority', searchable: false },
        { data: 'status', name: 'pp.active', searchable: false },
        { data: 'updated_at', name: 'pp.updated_at', searchable: false }
      ].map(function(col) { col.className = 'fk-preserve-case'; return col; });

      var builders = {
        logs: function() {
          return $('#osLogsTable').DataTable($.extend({}, dtDefaults, {
            order: [[0, 'desc']],
            ajax: "{{ route('odoo_sync.logs') }}",
            columns: [
              { data: 'created_at', name: 'l.created_at', searchable: false },
              { data: 'request', name: 'l.correlation_id' },
              { data: 'client', name: 'k.name' },
              { data: 'result', name: 'l.failed_count', searchable: false },
              { data: 'status_code', name: 'l.status_code', searchable: false },
              { data: 'errors', name: 'l.errors', orderable: false, searchable: false }
            ].map(function(col) { col.className = 'fk-preserve-case'; return col; })
          }));
        },
        test: function() {
          return $('#osTestTable').DataTable($.extend({}, dtDefaults, {
            order: [[9, 'desc']], ajax: "{{ route('odoo_sync.test_prices') }}", columns: priceColumns
          }));
        },
        live: function() {
          return $('#osLiveTable').DataTable($.extend({}, dtDefaults, {
            order: [[9, 'desc']], ajax: "{{ route('odoo_sync.live_prices') }}", columns: priceColumns
          }));
        }
      };

      var tables = { logs: builders.logs() };

      // Tabs: build each table the first time its tab is opened
      $('.os-tab').on('click', function() {
        var pane = $(this).data('pane');
        $('.os-tab').removeClass('is-active');
        $(this).addClass('is-active');
        $('.os-pane').removeClass('is-active');
        $('#osPane' + pane.charAt(0).toUpperCase() + pane.slice(1)).addClass('is-active');
        if (!tables[pane]) {
          tables[pane] = builders[pane]();
        } else {
          tables[pane].columns.adjust();
        }
      });

      // Errors modal
      $(document).on('click', '.os-view-errors', function() {
        $('#osErrorsCorrelation').text($(this).data('correlation'));
        $('#osErrorsBody').text($(this).attr('data-errors'));
        $('#osErrorsModal').modal('show');
      });

      // Click to copy IDs
      var toastTimer;
      $(document).on('click', '.os-copy', function() {
        var value = $(this).data('copy');
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(String(value)).then(function() {
          var toast = document.getElementById('osToast');
          toast.textContent = 'Copied ' + value;
          toast.classList.add('is-visible');
          clearTimeout(toastTimer);
          toastTimer = setTimeout(function() { toast.classList.remove('is-visible'); }, 1600);
        });
      });
    });
  </script>
</x-app-layout>
