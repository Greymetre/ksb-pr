<x-app-layout>
<div class="row">
<div class="col-md-12">
<div class="card">

    {{-- ================= HEADER ================= --}}
    <div class="card-header card-header-icon card-header-theme">
        <div class="card-icon">
            <i class="material-icons">build_circle</i> {{-- or engineering / garage --}}
        </div>

        <h4 class="card-title">
            Secondary Customers List
            <span class="float-right">

                <button class="btn btn-info" type="button"
                        data-toggle="collapse" data-target="#filterSection">
                    <i class="material-icons">tune</i>
                </button>

                <a href="{{ route('secondary-customers.create') }}"
                   class="btn btn-theme">
                   <i class="material-icons">add_circle</i>
                </a>

            </span>
        </h4>
    </div>

    {{-- ================= FILTERS ================= --}}
    <div class="collapse" id="filterSection">
        <div class="card-body">

            <div class="row">

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Owner Name</label>
                        <input type="text" id="owner_name" class="form-control">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" id="mobile" class="form-control">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Type</label>
                        <select id="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="RETAILER">RETAILER</option>
                            <option value="WORKSHOP">WORKSHOP</option>
                            <option value="MECHANIC">MECHANIC</option>
                            <option value="GARAGE">GARAGE</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Opportunity Status</label>
                        <select id="status" class="form-control">
                            <option value="">All</option>
                            <option value="HOT">HOT</option>
                            <option value="WARM">WARM</option>
                            <option value="COLD">COLD</option>
                            <option value="LOST">LOST</option>
                        </select>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- ================= BODY ================= --}}
    <div class="card-body">

        <div class="table-responsive">
            <table id="secondaryCustomersTable"
                   class="table table-striped table-bordered table-hover w-100">

                <thead class="text-primary">
                <tr>
                    <th width="80">Action</th>
                    <th>Owner Name</th>
                    <th>Shop Name</th>
                    <th>Mobile</th>
                    <th>Type</th>
                    <th>State</th>
                    <th>District</th>
                    <th>Opportunity</th>
                    <th>Saathi Status</th>
                    <th>Created At</th>
                </tr>
                </thead>

                <tbody></tbody>
            </table>
        </div>

    </div>

</div>
</div>
</div>

{{-- ================= DATATABLE SCRIPT ================= --}}
<script>
$(document).ready(function () {

    let table = $('#secondaryCustomersTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        order: [[9, 'desc']],

        ajax: {
            url: "{{ route('secondary-customers.index') }}",
            type: "GET",
            data: function (d) {
                d.owner_name = $('#owner_name').val();
                d.mobile     = $('#mobile').val();
                d.type       = $('#type').val();
                d.status     = $('#status').val();
            }
        },

        columns: [
            { data: 'action', orderable: false, searchable: false },
            { data: 'owner_name' },
            { data: 'shop_name' },
            { data: 'mobile_number' },
            { data: 'type' },
            { data: 'state' },
            { data: 'district' },
            { data: 'opportunity_status', orderable: false },
            { data: 'saathi_awareness_status', orderable: false },
            { data: 'created_at' }
        ],

        dom: 't<"row"<"col-md-6"l><"col-md-6"p>>',
    });

    $('#owner_name, #mobile, #type, #status').on('keyup change', function () {
        table.draw();
    });

});
</script>
</x-app-layout>