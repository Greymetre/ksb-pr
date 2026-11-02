<x-app-layout>
  @include('odoo_sync.partials.assets')

  <div class="os-page">
    <div class="os-head">
      <div>
        <div class="os-breadcrumb">Odoo Sync › <b>Product Master Odoo</b></div>
        <h1 class="os-title"><span class="material-icons">inventory_2</span>Product Master Odoo</h1>
        <p class="os-subtitle">Products of every Odoo category, pulled twice a day (06:20 and 18:20, after categories and sub-categories). Only products under {{ implode(', ', \App\Services\Odoo\CategorySync::SYNCED_CATEGORIES) }} (and a synced sub-category) are synced into FieldKonnect's Product List; the rest are shown here only.</p>
      </div>
      <div class="os-head-actions">
        <select id="osCategoryFilter" class="os-select" title="Filter by Odoo category">
          <option value="">All categories</option>
          @foreach($categories as $category)
            <option value="{{ $category->category_odoo_id }}">{{ $category->category_name }}</option>
          @endforeach
        </select>
        <select id="osLinkFilter" class="os-select" title="Filter table">
          <option value="">All products</option>
          <option value="synced">Synced to FieldKonnect</option>
          <option value="not_synced">Not synced</option>
        </select>
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
        <div class="os-stat-label"><span class="material-icons">cloud_done</span>Products in Odoo</div>
        <div class="os-stat-value">{{ number_format($counts['total']) }}</div>
        <div class="os-stat-note">{{ number_format($counts['active']) }} active</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">link</span>Synced to FieldKonnect</div>
        <div class="os-stat-value">{{ number_format($counts['synced']) }}</div>
        <div class="os-stat-note">Shown on <a href="{{ route('products.index') }}">Product List</a></div>
      </div>
    </div>

    <p class="os-pane-note">Click an Odoo ID to copy it. Sync history and errors are on <a href="{{ route('odoo_sync.index') }}">Sync Overview</a>.</p>
    <div class="os-table-wrap">
      <div class="os-table-scroll">
        <table id="osProductsTable" class="os-table">
          <thead><tr><th>Odoo ID</th><th>Product</th><th>Category / Sub-category (Odoo ID)</th><th>Price</th><th>UOM</th><th>FieldKonnect product</th><th>Status</th><th>Odoo updated</th><th>Synced</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    $(function() {
      var table = $('#osProductsTable').DataTable($.extend({}, window.osDataTableDefaults, {
        order: [[1, 'asc']],
        ajax: {
          url: "{{ route('odoo_sync.products_data') }}",
          data: function(d) {
            d.link = $('#osLinkFilter').val();
            d.category = $('#osCategoryFilter').val();
          }
        },
        columns: window.osPreserveCase([
          { data: 'product_odoo_id', name: 'op.product_odoo_id' },
          { data: 'product_name', name: 'op.product_name' },
          { data: 'category', name: 'oc.category_name' },
          { data: 'price', name: 'op.mrp', searchable: false },
          { data: 'uom_name', name: 'op.uom_name' },
          { data: 'linked', name: 'p.product_name' },
          { data: 'status', name: 'op.active', searchable: false },
          { data: 'odoo_updated_at', name: 'op.odoo_updated_at', searchable: false },
          { data: 'updated_at', name: 'op.updated_at', searchable: false }
        ])
      }));

      $('#osLinkFilter, #osCategoryFilter').on('change', function() { table.draw(); });

      $('#osSyncNow').on('click', function() {
        var $btn = $(this).prop('disabled', true).addClass('is-busy');
        $btn.find('.os-btn-label').text('Syncing…');

        $.ajax({
          url: "{{ route('odoo_sync.products_sync') }}",
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
