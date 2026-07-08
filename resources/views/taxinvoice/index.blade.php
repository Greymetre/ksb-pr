<x-app-layout>
  <section class="fk-manual-listing">
    <div class="fk-list-page-head">
      <div class="fk-list-heading-block">
        <div class="fk-list-breadcrumb">
          <span>Account Management</span><span>&rsaquo;</span><span class="fk-current">Invoice</span>
        </div>
        <div class="fk-list-title-row">
          <h1 class="fk-list-title">All Invoices</h1>
          <span class="fk-list-count is-visible" id="invoiceRecordCount">0 records</span>
        </div>
      </div>
      <div class="fk-list-actions">
        <button class="btn fk-filter-trigger" type="button" data-filter-target="#invoiceFilterDrawer">
          <span class="material-icons">tune</span><span>Filters</span>
        </button>
        @if(Auth::user()->can('invoice_create'))
          <a href="{{ route('tax_invoice.create') }}" class="btn fk-create-action" title="Add Invoice">
            <span class="material-icons">add_circle</span><span>Add New Invoice</span>
          </a>
        @endif
      </div>
    </div>

    <div class="card fk-listing-card" data-fk-listing-ready="1">
      <div class="card-body">
        <div class="fk-table-meta">
          <div class="fk-table-meta-icon"><span class="material-icons">receipt_long</span></div>
          <div class="fk-table-meta-copy">
            <h2>Invoice Directory</h2>
            <p class="fk-table-meta-subline" id="invoiceTableMeta">Live directory · page 1 of 1</p>
          </div>
        </div>
        <div class="table-responsive">
          <table id="invoiceTable" class="table fk-glass-table" style="width:100%">
            <thead>
              <tr>
                <th>Date</th>
                <th>Invoice #</th>
                <th>Order Number</th>
                <th>Customer Name</th>
                <th>Invoice Status</th>
                <th>Due Date</th>
                <th>Invoice Amount</th>
                <th>Balance</th>
                <th>Sub Total</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </section>

  <aside class="fk-filter-drawer" id="invoiceFilterDrawer">
    <div class="fk-filter-drawer-head">
      <div class="fk-filter-drawer-icon"><span class="material-icons">tune</span></div>
      <div>
        <h3>Advanced Filters</h3>
        <p>Applied live to the directory</p>
      </div>
      <button type="button" class="fk-filter-close" aria-label="Close filters"><span class="material-icons">close</span></button>
    </div>
    <div class="fk-filter-drawer-body">
      <div class="fk-filter-field">
        <label for="searchInput">Search Contact</label>
        <input type="text" id="searchInput" class="form-control fk-filter-control" placeholder="Search Contact">
      </div>
      <div class="fk-filter-field">
        <label for="start_date">Start Date</label>
        <input type="text" class="form-control datepicker fk-filter-control" id="start_date" placeholder="Start Date" autocomplete="off">
      </div>
      <div class="fk-filter-field">
        <label for="end_date">End Date</label>
        <input type="text" class="form-control datepicker fk-filter-control" id="end_date" placeholder="End Date" autocomplete="off">
      </div>
    </div>
    @if(Auth::user()->can('invoice_export'))
      <div class="fk-filter-drawer-tools">
        <a id="downloadInvoice" href="javascript:void(0);" class="btn fk-tool-export" title="Download Invoice">
          <span class="material-icons">cloud_download</span><span>Export</span>
        </a>
      </div>
    @endif
    <div class="fk-filter-drawer-foot">
      <button class="btn fk-filter-reset" type="button">Reset</button>
      <button class="btn fk-filter-apply" type="button">Apply Filters</button>
    </div>
  </aside>

  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>

  <script>
    $(function() {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      var table = $('#invoiceTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        columnDefs: [{
          className: 'control',
          orderable: false,
          targets: -1
        }],
        order: [
          [0, 'desc']
        ],
        retrieve: true,
        ajax: {
          url: "{{ route('tax_invoice.index', ['dev' => true]) }}",
          data: function(d) {
            d.searchInput = $('#searchInput').val();
            d.start_date = $('#start_date').val();
            d.end_date = $('#end_date').val();
          }
        },
        columns: [{
            data: 'invoice_date',
            name: 'invoice_date',
            defaultContent: '',
            orderable: false,
            searchable: false
          },
          {
            data: 'invoice_no',
            name: 'invoice_no',
            defaultContent: '',
            orderable: false
          },
          {
            data: 'order_no',
            name: 'order_no',
            defaultContent: '',
            orderable: false
          },
          {
            data: 'customer.name',
            name: 'customer.name',
            defaultContent: '',
            orderable: false
          },
          {
            data: 'status',
            name: 'status',
            defaultContent: 'Paid',
            orderable: false
          },
          {
            data: 'due_date',
            name: 'due_date',
            defaultContent: '',
            orderable: false
          },
          {
            data: 'grand_total',
            name: 'grand_total',
            defaultContent: '',
            orderable: false
          },
          {
            data: 'balance',
            name: 'balance',
            defaultContent: '',
            orderable: false
          },
          {
            data: 'sub_total',
            name: 'sub_total',
            defaultContent: '',
            orderable: false
          },
        ],
        dom: 't<"bottom"ip>',
        drawCallback: function() {
          var info = this.api().page.info();
          $('#invoiceRecordCount').text((info.recordsDisplay || 0) + ' records');
          $('#invoiceTableMeta').text('Live directory · page ' + ((info.page || 0) + 1) + ' of ' + (info.pages || 1));
        }
      });

      $('#searchInput, #end_date').on('input change', function() {
        table.draw();
      });

      $('#start_date').on('change', function() {
        var selectedStartDate = $('#start_date').datepicker('getDate');
        $('#end_date').datepicker('option', 'minDate', selectedStartDate);
        table.draw();
      });
    });

    $('#downloadInvoice').click(function() {
      var startDate = $('#start_date').val();
      var endDate = $('#end_date').val();
      var searchInput = $('#searchInput').val();
      var url = "{{ route('invoice.export') }}?start_date=" + startDate + "&end_date=" + endDate + "&searchInput=" + searchInput;
      window.location.href = url;
    });
  </script>
</x-app-layout>
