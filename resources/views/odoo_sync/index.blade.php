<x-app-layout>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">sync_alt</i>
          </div>
          <h4 class="card-title">Odoo Sync</h4>
        </div>
        <div class="card-body">

          <p class="text-muted">
            Data pushed by Odoo to <code>/api/v1/odoo/*</code>. Data sent with a <span class="badge badge-info">TEST</span> key
            appears under <b>Test Prices</b> and is never used by FieldKonnect. Data sent with a <span class="badge badge-danger">LIVE</span>
            key appears under <b>Live Prices</b>.
          </p>

          <div class="row">
            <div class="col-md-3 col-6"><div class="alert alert-light border mb-2"><small>Test prices</small><h4 class="m-0">{{ $counts['test'] }}</h4></div></div>
            <div class="col-md-3 col-6"><div class="alert alert-light border mb-2"><small>Test prices not linked to party/product</small><h4 class="m-0">{{ $counts['test_unlinked'] }}</h4></div></div>
            <div class="col-md-3 col-6"><div class="alert alert-light border mb-2"><small>Live prices</small><h4 class="m-0">{{ $counts['live'] }}</h4></div></div>
            <div class="col-md-3 col-6"><div class="alert alert-light border mb-2"><small>Last request</small><h4 class="m-0" style="font-size:1rem">{{ $counts['last_request'] ? showdatetimeformat($counts['last_request']) : 'Never' }}</h4></div></div>
          </div>

          <h5 class="mt-3">API keys</h5>
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead class="text-primary">
                <th>#</th><th>Name</th><th>Mode</th><th>Status</th><th>Last used</th><th>Created</th>
              </thead>
              <tbody>
                @forelse($clients as $client)
                <tr>
                  <td>{{ $client->id }}</td>
                  <td>{{ $client->name }}</td>
                  <td>{!! $client->mode === 'live' ? '<span class="badge badge-danger">LIVE</span>' : '<span class="badge badge-info">TEST</span>' !!}</td>
                  <td>{!! $client->active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Revoked</span>' !!}</td>
                  <td>{{ $client->last_used_at ? showdatetimeformat($client->last_used_at) : 'Never' }}</td>
                  <td>{{ showdatetimeformat($client->created_at) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No API key created yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <ul class="nav nav-tabs mt-3" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tabLogs" role="tab" data-table="logs">Request Logs</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabTestPrices" role="tab" data-table="test">Test Prices</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabLivePrices" role="tab" data-table="live">Live Prices</a></li>
          </ul>

          <div class="tab-content pt-3">
            <div class="tab-pane active" id="tabLogs" role="tabpanel">
              <div class="table-responsive">
                <table id="odooLogsTable" class="table table-striped table-bordered table-hover w-100">
                  <thead class="text-primary">
                    <th>Time</th><th>Correlation ID</th><th>Key</th><th>Mode</th><th>Method</th>
                    <th>Received</th><th>Created</th><th>Updated</th><th>Skipped</th><th>Failed</th><th>HTTP</th><th>Errors / skipped records</th>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
            <div class="tab-pane" id="tabTestPrices" role="tabpanel">
              <div class="table-responsive">
                <table id="odooTestPricesTable" class="table table-striped table-bordered table-hover w-100">
                  @include('odoo_sync.partials.price_head')
                </table>
              </div>
            </div>
            <div class="tab-pane" id="tabLivePrices" role="tabpanel">
              <div class="table-responsive">
                <table id="odooLivePricesTable" class="table table-striped table-bordered table-hover w-100">
                  @include('odoo_sync.partials.price_head')
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script src="{{ url('/').'/'.asset('assets/js/jquery.custom.js') }}"></script>
  <script type="text/javascript">
    $(function() {
      var priceColumns = [
        { data: 'external_id', name: 'pp.external_id' },
        { data: 'price_list_code', name: 'pp.price_list_code' },
        { data: 'party_code', name: 'pp.party_code' },
        { data: 'party_name', name: 'party_name', orderable: false, searchable: false },
        { data: 'product_code', name: 'pp.product_code' },
        { data: 'product_name', name: 'pr.product_name', orderable: false, searchable: false },
        { data: 'party_price', name: 'pp.party_price', searchable: false },
        { data: 'base_price', name: 'pp.base_price', searchable: false },
        { data: 'tax_inclusive', name: 'pp.tax_inclusive', searchable: false },
        { data: 'quantity_slab', name: 'quantity_slab', orderable: false, searchable: false },
        { data: 'uom_code', name: 'pp.uom_code', searchable: false },
        { data: 'validity', name: 'validity', orderable: false, searchable: false },
        { data: 'priority', name: 'pp.priority', searchable: false },
        { data: 'active', name: 'pp.active', searchable: false },
        { data: 'updated_at', name: 'pp.updated_at', searchable: false }
      ];

      var tables = {};
      var builders = {
        logs: function() {
          return $('#odooLogsTable').DataTable({
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            ajax: "{{ route('odoo_sync.logs') }}",
            columns: [
              { data: 'created_at', name: 'l.created_at', searchable: false },
              { data: 'correlation_id', name: 'l.correlation_id' },
              { data: 'client_name', name: 'k.name' },
              { data: 'mode', name: 'l.mode' },
              { data: 'method', name: 'l.method', searchable: false },
              { data: 'received_count', name: 'l.received_count', searchable: false },
              { data: 'created_count', name: 'l.created_count', searchable: false },
              { data: 'updated_count', name: 'l.updated_count', searchable: false },
              { data: 'skipped_count', name: 'l.skipped_count', searchable: false },
              { data: 'failed_count', name: 'l.failed_count', searchable: false },
              { data: 'status_code', name: 'l.status_code', searchable: false },
              { data: 'errors', name: 'l.errors', orderable: false, searchable: false }
            ]
          });
        },
        test: function() {
          return $('#odooTestPricesTable').DataTable({
            processing: true, serverSide: true, order: [[14, 'desc']],
            ajax: "{{ route('odoo_sync.test_prices') }}", columns: priceColumns
          });
        },
        live: function() {
          return $('#odooLivePricesTable').DataTable({
            processing: true, serverSide: true, order: [[14, 'desc']],
            ajax: "{{ route('odoo_sync.live_prices') }}", columns: priceColumns
          });
        }
      };

      tables.logs = builders.logs();

      // Build each price table the first time its tab is opened
      $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var key = $(e.target).data('table');
        if (!tables[key]) {
          tables[key] = builders[key]();
        } else {
          tables[key].columns.adjust();
        }
      });
    });
  </script>
</x-app-layout>
