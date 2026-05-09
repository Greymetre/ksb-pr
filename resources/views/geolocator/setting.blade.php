<x-app-layout>
  <style>
    .swal2-container.swal2-center.swal2-fade.swal2-shown {
      z-index: 9999;
    }

    #invoice_logo {
      cursor: pointer;
    }

    #invoice_esign {
      cursor: pointer;
    }
  </style>
  <section class="invoice_main">
    @if (count($errors) > 0)
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    @endif

    <div class="container-fluid">
      <div class="card p-4">
        <form action="{{ route('geo_locator_setting.store') }}" method="post" enctype="multipart/form-data">
          @csrf
          <h5><i class="fa fa-file-text-o"></i> Geo Locator Settings</h5>

          <div class="form-row">

            <div class="form-group col-md-6">
              <label for="customer_filter">Customer Filter</label>
              <select name="customer_filter[]" id="customer_filter" multiple class="form-control select2">
                <option value="">Select Customer Filter</option>

                @php
                $selectedCustomerFilters = $setting->customer_filter ?? [];
                @endphp
                
                <option value="Customer Type" {{ in_array("Customer Type", $selectedCustomerFilters) ? 'selected' : '' }}>Customer Type</option>
                <option value="Pincode" {{ in_array("Pincode", $selectedCustomerFilters) ? 'selected' : '' }}>Pincode</option>
                <option value="City" {{ in_array("City", $selectedCustomerFilters) ? 'selected' : '' }}>City</option>
                <option value="District" {{ in_array("District", $selectedCustomerFilters) ? 'selected' : '' }}>District</option>
                <option value="State" {{ in_array("State", $selectedCustomerFilters) ? 'selected' : '' }}>State</option>
                <option value="Grade" {{ in_array("Grade", $selectedCustomerFilters) ? 'selected' : '' }}>Grade</option>
                <option value="Branch Name" {{ in_array("Branch Name", $selectedCustomerFilters) ? 'selected' : '' }}>Branch Name</option>

              </select>
            </div>


            <div class="form-group col-md-6">
              <label for="lead_filter">Lead Filter</label>
              <select name="lead_filter[]" id="lead_filter" multiple class="form-control select2">
                <option value="">Select Lead Filter</option>

                @php
                $selectedLeadFilters = $setting->lead_filter ?? [];
                @endphp

                <option value="Lead Type" {{ in_array("Lead Type", $selectedLeadFilters) ? 'selected' : '' }}>Lead Type</option>
                <option value="Lead Source" {{ in_array("Lead Source", $selectedLeadFilters) ? 'selected' : '' }}>Lead Source</option>
                <option value="Pincode" {{ in_array("Pincode", $selectedLeadFilters) ? 'selected' : '' }}>Pincode</option>
                <option value="City" {{ in_array("City", $selectedLeadFilters) ? 'selected' : '' }}>City</option>
                <option value="District" {{ in_array("District", $selectedLeadFilters) ? 'selected' : '' }}>District</option>
                <option value="State" {{ in_array("State", $selectedLeadFilters) ? 'selected' : '' }}>State</option>
                <option value="Assignee" {{ in_array("Assignee", $selectedLeadFilters) ? 'selected' : '' }}>Assignee</option>
                <option value="Lead Status" {{ in_array("Lead Status", $selectedLeadFilters) ? 'selected' : '' }}>Lead Status</option>
                
              </select>
            </div>


          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save Settings</button>
          </div>
        </form>
      </div>
    </div>
  </section>
  <script src="{{ url('/').'/'.asset('assets/js/jquery.custom.js') }}"></script>
</x-app-layout>