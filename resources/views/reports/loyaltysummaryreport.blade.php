<x-app-layout>
  <section class="fk-manual-listing">
    <div class="fk-list-page-head">
      <div class="fk-list-heading-block">
        <div class="fk-list-breadcrumb">
          <span>REPORTS</span>
          <span>›</span>
          <span class="fk-current">LOYALTY SUMMARY BRANCH REPORT</span>
        </div>
        <div class="fk-list-title-row">
          <h1 class="fk-list-title">Loyalty Summary Branch Report</h1>
          <span id="loyaltySummaryCount" class="fk-list-count is-visible">0 records</span>
        </div>
      </div>
      <div class="fk-list-actions">
        <button type="button" class="btn fk-filter-trigger" data-filter-target="#loyaltySummaryFilters">
          <span class="material-icons">tune</span>
          <span>Filters</span>
        </button>
      </div>
    </div>

    <aside id="loyaltySummaryFilters" class="fk-filter-drawer" aria-hidden="true">
      <div class="fk-filter-drawer-head">
        <div class="fk-filter-drawer-icon">
          <span class="material-icons">tune</span>
        </div>
        <div>
          <h3>Advanced Filters</h3>
          <p>Applied live to the directory</p>
        </div>
        <button type="button" class="fk-filter-close" aria-label="Close filters">
          <span class="material-icons">close</span>
        </button>
      </div>

      <div class="fk-filter-drawer-body">
        @if(auth()->user()->can(['customer_download']))
          <form id="loyaltySummaryExportForm" method="GET" action="{{ URL::to('loyalty-summary-report-download') }}">
            <div class="d-flex flex-wrap flex-row">
              <div class="p-2" data-label="State">
                <select class="select2" name="state_id" id="state_id" data-style="select-with-transition" title="Select State">
                  <option value="">Select State</option>
                  @if(@isset($states))
                    @foreach($states as $state)
                      <option value="{!! $state['id'] !!}">{!! $state['state_name'] !!}</option>
                    @endforeach
                  @endif
                </select>
              </div>
            </div>
          </form>
        @else
          <div class="d-flex flex-wrap flex-row">
            <div class="p-2" data-label="State">
              <select class="select2" name="state_id" id="state_id" data-style="select-with-transition" title="Select State">
                <option value="">Select State</option>
                @if(@isset($states))
                  @foreach($states as $state)
                    <option value="{!! $state['id'] !!}">{!! $state['state_name'] !!}</option>
                  @endforeach
                @endif
              </select>
            </div>
          </div>
        @endif
      </div>

      <div class="fk-filter-drawer-tools">
        @if(auth()->user()->can(['customer_download']) && auth()->user()->can('loyalty_summary_report_download'))
          <button type="button" id="loyaltySummaryExportBtn" class="btn fk-tool-export">
            <span class="material-icons">cloud_download</span>
            <span>Export</span>
          </button>
        @endif
      </div>

      <div class="fk-filter-drawer-foot">
        <button type="button" class="btn fk-filter-reset">Reset</button>
        <button type="button" class="btn fk-filter-apply">Apply Filters</button>
      </div>
    </aside>

    <div class="card fk-listing-card fk-loyalty-summary-card" data-fk-listing-ready="1">
      <div class="card-body">
        @if(count($errors) > 0)
          <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <i class="material-icons">close</i>
            </button>
            <span>
              @foreach($errors->all() as $error)
                <li>{{$error}}</li>
              @endforeach
            </span>
          </div>
        @endif

        <div class="alert fk-js-alert" style="display: none;">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <i class="material-icons">close</i>
          </button>
          <span class="message"></span>
        </div>

        <div class="fk-table-meta">
          <div class="fk-table-meta-icon">
            <span class="material-icons">storefront</span>
          </div>
          <div class="fk-table-meta-copy">
            <h2>Loyalty Summary Branch Report</h2>
            <p id="loyaltySummaryMeta" class="fk-table-meta-subline">Live directory · page 1 of 1</p>
          </div>
        </div>

        <div class="table-responsive">
          <table id="getbranchsummary" class="table fk-glass-table">
            <thead>
              <tr>
                <th>{!! trans('panel.global.no') !!}</th>
                <th>State</th>
                <th>Total Retailer Registered Nos</th>
                <th>Total Retailer Under Saarthi Nos</th>
                <th>Coupon Scan Nos</th>
                <th>Mobile App Download Nos</th>
                <th>Provision Point</th>
                <th>Active Point</th>
                <th>Total Point</th>
                <th>Redeem Gift</th>
                <th>Redeem Neft</th>
                <th>Total Redeem</th>
                <th>Balance Active Point</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <script type="text/javascript">
    $(function () {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      function updateLoyaltySummaryMeta(table) {
        var info = table.page.info();
        var total = info.recordsDisplay || info.recordsTotal || 0;
        var pages = info.pages || 1;
        $('#loyaltySummaryCount')
          .text(total + ' records')
          .toggleClass('is-visible', true);
        $('#loyaltySummaryMeta').text('Live directory · page ' + ((info.page || 0) + 1) + ' of ' + pages);
      }

      var table = $('#getbranchsummary').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: true,
        columnDefs: [{
          className: 'control',
          orderable: false,
          targets: -1
        }],
        order: [[0, 'desc']],
        ajax: {
          url: "{{ route('loyaltySummaryReport') }}",
          data: function (d) {
            d.state_id = $('#state_id').val();
          }
        },
        columns: [
          { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
          { data: 'state_name', name: 'state_name', defaultContent: '' },
          { data: 'total_registered_retailers', name: 'total_registered_retailers', defaultContent: '' },
          { data: 'total_registered_retailers_under_saarthi', name: 'total_registered_retailers_under_saarthi', defaultContent: 'test3' },
          { data: 'coupon_scan_nos', name: 'coupon_scan_nos', defaultContent: '' },
          { data: 'mobile_app_downloads', name: 'mobile_app_downloads', defaultContent: '' },
          { data: 'provision_point', name: 'provision_point', defaultContent: '' },
          { data: 'active_point', name: 'active_point', defaultContent: '' },
          { data: 'total_point', name: 'total_point', defaultContent: '' },
          { data: 'redeem_gift', name: 'redeem_gift', defaultContent: '' },
          { data: 'redeem_neft', name: 'redeem_neft', defaultContent: '' },
          { data: 'total_redeem', name: 'total_redeem', defaultContent: '' },
          { data: 'balance_active_point', name: 'balance_active_point', defaultContent: '' }
        ],
        drawCallback: function () {
          updateLoyaltySummaryMeta(this.api());
        }
      });

      $('#state_id').change(function() {
        table.draw();
      });

      $('#loyaltySummaryExportBtn').on('click', function () {
        $('#loyaltySummaryExportForm').trigger('submit');
      });

      table.on('xhr.dt', function () {
        setTimeout(function () {
          updateLoyaltySummaryMeta(table);
        }, 0);
      });
    });
  </script>
</x-app-layout>
