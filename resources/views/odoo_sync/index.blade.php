<x-app-layout>
  @include('odoo_sync.partials.assets')

  <div class="os-page">
    <div class="os-head">
      <div>
        <div class="os-breadcrumb">Odoo Sync › <b>Sync Overview</b></div>
        <h1 class="os-title"><span class="material-icons">monitor_heart</span>Sync Overview</h1>
        <p class="os-subtitle">API keys and every request Odoo has sent to FieldKonnect, across all modules.</p>
      </div>
      <div class="os-endpoint"><span class="material-icons" style="font-size:16px">api</span><code>/api/v1/odoo/*</code></div>
    </div>

    <div class="os-stats is-3">
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">schedule</span>Last request</div>
        <div class="os-stat-value is-small">{{ $lastRequest ? \Carbon\Carbon::parse($lastRequest->created_at)->diffForHumans() : 'No requests yet' }}</div>
        <div class="os-stat-note">{{ $lastRequest ? \Carbon\Carbon::parse($lastRequest->created_at)->format('d M Y, h:i A') . ' · HTTP ' . $lastRequest->status_code : 'Waiting for Odoo' }}</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">swap_vert</span>Requests today</div>
        <div class="os-stat-value">{{ number_format($counts['requests_today']) }}</div>
        <div class="os-stat-note">{{ number_format($counts['logs']) }} in total</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">error_outline</span>Failed records today</div>
        <div class="os-stat-value">{{ number_format($counts['failed_today']) }}</div>
        <div class="os-stat-note {{ $counts['failed_today'] > 0 ? 'is-danger' : '' }}">{{ $counts['failed_today'] > 0 ? 'Open the errors in the log below' : 'All good' }}</div>
      </div>
    </div>

    <div class="os-panel">
      <div class="os-panel-title">Modules</div>
      <div class="os-module-grid">
        <a class="os-module" href="{{ route('odoo_sync.party_prices') }}">
          <div class="os-key-icon"><span class="material-icons">sell</span></div>
          <div class="os-key-body">
            <div class="os-key-name">Party Wise Pricing <span class="os-pill os-pill-success os-pill-sm" style="margin-top:0">Ready</span></div>
            <div class="os-key-meta">POST /api/v1/odoo/party-prices</div>
          </div>
        </a>
        @foreach(['Product Category', 'Product Sub-category', 'Product Master'] as $upcoming)
        <div class="os-module is-soon">
          <div class="os-key-icon"><span class="material-icons">inventory_2</span></div>
          <div class="os-key-body">
            <div class="os-key-name">{{ $upcoming }} <span class="os-pill os-pill-neutral os-pill-sm" style="margin-top:0">Soon</span></div>
            <div class="os-key-meta">Not available yet</div>
          </div>
        </div>
        @endforeach
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

    <div class="os-panel-title" style="margin-top:4px">Request logs</div>
    <p class="os-pane-note">One row per API call. Click a correlation ID to copy it, and quote it when reporting a problem.</p>
    <div class="os-table-wrap">
      <div class="os-table-scroll">
        <table id="osLogsTable" class="os-table">
          <thead><tr><th>Time</th><th>Request</th><th>Module</th><th>Key</th><th>Result</th><th>HTTP</th><th>Errors</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
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

  <script type="text/javascript">
    $(function() {
      $('#osLogsTable').DataTable($.extend({}, window.osDataTableDefaults, {
        order: [[0, 'desc']],
        ajax: "{{ route('odoo_sync.logs') }}",
        columns: window.osPreserveCase([
          { data: 'created_at', name: 'l.created_at', searchable: false },
          { data: 'request', name: 'l.correlation_id' },
          { data: 'module', name: 'l.entity' },
          { data: 'client', name: 'k.name' },
          { data: 'result', name: 'l.failed_count', searchable: false },
          { data: 'status_code', name: 'l.status_code', searchable: false },
          { data: 'errors', name: 'l.errors', orderable: false, searchable: false }
        ])
      }));

      $(document).on('click', '.os-view-errors', function() {
        $('#osErrorsCorrelation').text($(this).data('correlation'));
        $('#osErrorsBody').text($(this).attr('data-errors'));
        $('#osErrorsModal').modal('show');
      });
    });
  </script>
</x-app-layout>
