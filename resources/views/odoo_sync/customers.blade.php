<x-app-layout>
  @include('odoo_sync.partials.assets')

  <div class="os-page">
    <div class="os-head">
      <div>
        <div class="os-breadcrumb">Odoo Sync › <b>Primary Customer Odoo</b></div>
        <h1 class="os-title"><span class="material-icons">storefront</span>Primary Customer Odoo</h1>
        <p class="os-subtitle">Primary customers / dealers pulled from Odoo twice a day (06:30 and 18:30). The FieldKonnect customer master is not changed; a customer is linked when its Odoo Code matches the Odoo customer ID, else its GSTIN, else its 10-digit mobile.</p>
      </div>
      <div class="os-head-actions">
        <a href="{{ route('odoo_sync.customers_export') }}" class="os-btn os-btn-ghost"><span class="material-icons">download</span>Export</a>
        <button type="button" class="os-btn" id="osSyncNow"><span class="material-icons">sync</span><span class="os-btn-label">Sync now</span></button>
      </div>
    </div>

    <div class="os-stats is-3">
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">schedule</span>Last sync</div>
        <div class="os-stat-value is-small">{{ $lastRun ? \Carbon\Carbon::parse($lastRun->created_at)->diffForHumans() : 'Not synced yet' }}</div>
        <div class="os-stat-note {{ $lastRun && $lastRun->status_code >= 300 ? 'is-danger' : '' }}">
          @if($lastRun)
            {{ \Carbon\Carbon::parse($lastRun->created_at)->format('d M Y, h:i A') }} · {{ $lastRun->status_code === 502 ? 'Odoo call failed, see Sync Overview' : number_format($lastRun->received_count) . ' received' }}
          @else
            Click Sync now or wait for the cron
          @endif
        </div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">storefront</span>Customers</div>
        <div class="os-stat-value">{{ number_format($counts['total']) }}</div>
        <div class="os-stat-note">{{ number_format($counts['active']) }} active</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">link_off</span>Not linked</div>
        <div class="os-stat-value">{{ number_format($counts['unlinked']) }}</div>
        <div class="os-stat-note {{ $counts['unlinked'] > 0 ? 'is-warning' : '' }}">No FieldKonnect customer with the same Odoo Code, GSTIN or mobile</div>
      </div>
    </div>

    <p class="os-pane-note">Click an Odoo ID to copy it. Sync history and errors are on <a href="{{ route('odoo_sync.index') }}">Sync Overview</a>.</p>
    <div class="os-table-wrap">
      <div class="os-table-scroll">
        <table id="osCustomersTable" class="os-table">
          <thead><tr><th>Odoo Customer ID</th><th>Firm</th><th>Contact</th><th>Location</th><th>GSTIN</th><th>Type</th><th>FieldKonnect customer</th><th>Status</th><th>Odoo updated</th><th>Synced</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    $(function() {
      var table = $('#osCustomersTable').DataTable($.extend({}, window.osDataTableDefaults, {
        order: [[1, 'asc']],
        ajax: "{{ route('odoo_sync.customers_data') }}",
        columns: window.osPreserveCase([
          { data: 'customer_odoo_id', name: 'ocu.customer_odoo_id' },
          { data: 'firm_name', name: 'ocu.firm_name' },
          { data: 'contact', name: 'ocu.mobile' },
          { data: 'location', name: 'ocu.state' },
          { data: 'gstin', name: 'ocu.gstin' },
          { data: 'customer_type', name: 'ocu.customer_type' },
          { data: 'linked', name: 'c.name' },
          { data: 'status', name: 'ocu.active', searchable: false },
          { data: 'odoo_updated_at', name: 'ocu.odoo_updated_at', searchable: false },
          { data: 'updated_at', name: 'ocu.updated_at', searchable: false }
        ])
      }));

      $('#osSyncNow').on('click', function() {
        var $btn = $(this).prop('disabled', true).addClass('is-busy');
        $btn.find('.os-btn-label').text('Syncing…');

        $.ajax({
          url: "{{ route('odoo_sync.customers_sync') }}",
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).done(function(r) {
          window.osToast('Synced: ' + r.created + ' created, ' + r.updated + ' updated, ' + r.skipped + ' unchanged, ' + r.failed + ' failed');
          setTimeout(function() { location.reload(); }, 1400);
        }).fail(function() {
          window.osToast('Sync failed. See Sync Overview for the error.');
          $btn.prop('disabled', false).removeClass('is-busy').find('.os-btn-label').text('Sync now');
        });
      });
    });
  </script>
</x-app-layout>
