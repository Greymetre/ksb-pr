<x-app-layout>
  @include('odoo_sync.partials.assets')

  <div class="os-page">
    <div class="os-head">
      <div>
        <div class="os-breadcrumb">Odoo Sync › <b>Sub Category Master Odoo</b></div>
        <h1 class="os-title"><span class="material-icons">account_tree</span>Sub Category Master Odoo</h1>
        <p class="os-subtitle">Product sub-categories pulled from Odoo twice a day (06:10 and 18:10, after categories). Only sub-categories whose parent category is linked to a FieldKonnect category are kept. The FieldKonnect sub-category master is not changed; a sub-category is linked when its name matches under that parent category.</p>
      </div>
      <button type="button" class="os-btn" id="osSyncNow"><span class="material-icons">sync</span><span class="os-btn-label">Sync now</span></button>
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
        <div class="os-stat-label"><span class="material-icons">account_tree</span>Sub-categories</div>
        <div class="os-stat-value">{{ number_format($counts['total']) }}</div>
        <div class="os-stat-note">{{ number_format($counts['active']) }} active</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">link_off</span>Not linked</div>
        <div class="os-stat-value">{{ number_format($counts['unlinked']) }}</div>
        <div class="os-stat-note {{ $counts['unlinked'] > 0 ? 'is-warning' : '' }}">No matching FieldKonnect sub-category</div>
      </div>
    </div>

    <p class="os-pane-note">Click an external ID to copy it. Sync history and errors are on <a href="{{ route('odoo_sync.index') }}">Sync Overview</a>.</p>
    <div class="os-table-wrap">
      <div class="os-table-scroll">
        <table id="osSubcategoriesTable" class="os-table">
          <thead><tr><th>External ID</th><th>Code</th><th>Sub-category</th><th>Odoo category</th><th>FieldKonnect sub-category</th><th>Ranking</th><th>Status</th><th>Odoo updated</th><th>Synced</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    $(function() {
      var table = $('#osSubcategoriesTable').DataTable($.extend({}, window.osDataTableDefaults, {
        order: [[1, 'asc']],
        ajax: "{{ route('odoo_sync.subcategories_data') }}",
        columns: window.osPreserveCase([
          { data: 'external_id', name: 'os.external_id' },
          { data: 'subcategory_code', name: 'os.subcategory_code' },
          { data: 'subcategory_name', name: 'os.subcategory_name' },
          { data: 'parent', name: 'os.category_code' },
          { data: 'linked', name: 's.subcategory_name' },
          { data: 'ranking', name: 'os.ranking', searchable: false },
          { data: 'status', name: 'os.active', searchable: false },
          { data: 'odoo_updated_at', name: 'os.odoo_updated_at', searchable: false },
          { data: 'updated_at', name: 'os.updated_at', searchable: false }
        ])
      }));

      $('#osSyncNow').on('click', function() {
        var $btn = $(this).prop('disabled', true).addClass('is-busy');
        $btn.find('.os-btn-label').text('Syncing…');

        $.ajax({
          url: "{{ route('odoo_sync.subcategories_sync') }}",
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
