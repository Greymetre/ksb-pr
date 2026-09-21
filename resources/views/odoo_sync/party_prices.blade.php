<x-app-layout>
  @include('odoo_sync.partials.assets')

  <div class="os-page">
    <div class="os-head">
      <div>
        <div class="os-breadcrumb">Odoo Sync › <b>Party Wise Pricing</b></div>
        <h1 class="os-title"><span class="material-icons">sell</span>Party Wise Pricing</h1>
        <p class="os-subtitle">Customer/distributor specific product prices pushed by Odoo. Test key data is kept separately and is never used by the app.</p>
      </div>
      <div class="os-endpoint"><span class="material-icons" style="font-size:16px">api</span><code>POST /api/v1/odoo/party-prices</code></div>
    </div>

    <div class="os-stats is-3">
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">schedule</span>Last received</div>
        <div class="os-stat-value is-small">{{ $counts['last_received'] ? \Carbon\Carbon::parse($counts['last_received'])->diffForHumans() : 'Nothing yet' }}</div>
        <div class="os-stat-note">{{ $counts['last_received'] ? \Carbon\Carbon::parse($counts['last_received'])->format('d M Y, h:i A') : 'Waiting for Odoo' }}</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">science</span>Test prices</div>
        <div class="os-stat-value">{{ number_format($counts['test']) }}</div>
        <div class="os-stat-note {{ $counts['test_unlinked'] > 0 ? 'is-warning' : '' }}">{{ number_format($counts['test_unlinked']) }} not linked to a party/product</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">verified</span>Live prices</div>
        <div class="os-stat-value">{{ number_format($counts['live']) }}</div>
        <div class="os-stat-note {{ $counts['live_unlinked'] > 0 ? 'is-warning' : '' }}">{{ number_format($counts['live_unlinked']) }} not linked to a party/product</div>
      </div>
    </div>

    <div class="os-tabs" role="tablist">
      <button type="button" class="os-tab is-active" data-pane="test"><span class="material-icons" style="font-size:17px">science</span>Test Prices <span class="os-tab-count">{{ number_format($counts['test']) }}</span></button>
      <button type="button" class="os-tab" data-pane="live"><span class="material-icons" style="font-size:17px">verified</span>Live Prices <span class="os-tab-count">{{ number_format($counts['live']) }}</span></button>
    </div>

    @foreach(['test' => 'Stored with the test key. Not used by FieldKonnect.', 'live' => 'Stored with the live key. These are the real party-wise prices.'] as $pane => $note)
    <div class="os-pane {{ $pane === 'test' ? 'is-active' : '' }}" id="osPane{{ ucfirst($pane) }}">
      <p class="os-pane-note">{{ $note }} <b>Not linked</b> means the code was not found in FieldKonnect. Request errors are on <a href="{{ route('odoo_sync.index') }}">Sync Overview</a>.</p>
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

  <script type="text/javascript">
    $(function() {
      var priceColumns = function() {
        return window.osPreserveCase([
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
        ]);
      };

      var sources = {
        test: "{{ route('odoo_sync.test_prices') }}",
        live: "{{ route('odoo_sync.live_prices') }}"
      };

      var build = function(pane) {
        return $('#os' + pane.charAt(0).toUpperCase() + pane.slice(1) + 'Table').DataTable($.extend({}, window.osDataTableDefaults, {
          order: [[9, 'desc']], ajax: sources[pane], columns: priceColumns()
        }));
      };

      var tables = { test: build('test') };

      // Build the live table the first time its tab is opened
      $('.os-tab').on('click', function() {
        var pane = $(this).data('pane');
        $('.os-tab').removeClass('is-active');
        $(this).addClass('is-active');
        $('.os-pane').removeClass('is-active');
        $('#osPane' + pane.charAt(0).toUpperCase() + pane.slice(1)).addClass('is-active');
        if (!tables[pane]) {
          tables[pane] = build(pane);
        } else {
          tables[pane].columns.adjust();
        }
      });
    });
  </script>
</x-app-layout>
