<x-app-layout>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header card-header-icon card-header-theme">
          <!-- <div class="card-icon">
             <i class="material-icons">perm_identity</i> 
          </div> -->
          <h4 class="card-title ">Leads<span class="brig ml-2"></span>
            <span class="">
            <button class="btn btn-info mb-3 float-right" type="button" data-toggle="collapse" data-target="#filterSection" aria-expanded="false" aria-controls="filterSection">
              <i class="material-icons">tune</i> Filters
              </button>
              <div class="collapse" id="filterSection">
                <div class="d-flex">
                <div>
                  <select name="status" id="status" class="form-control selectpicker" title="Status">
                    <option value="">All</option>
                    @if($status->count() > 0)
                    @foreach($status as $stat)
                    <option value="{{$stat['id']}}"> {{$stat['display_name']}} </option>
                    @endforeach
                    @endif
                  </select>
                </div>
                <div>
                  {!! Form::open(['method' => 'POST', 'class' => 'form-inline', 'id' => 'frmFilter']) !!}
                  {!! Form::text('datetime', old('datetime'), ['class' => 'form-control','placeholder'=> __('MM/DD/YYYY - MM/DD/YYYY'), 'autocomplete' => 'off']) !!}
                  {!! Form::close() !!}
                </div>
                <div>
                  <select name="user_id_search" id="user_id_search" class="form-control select2">
                    <option value="">Assign To</option>
                    @if(@isset($users ))
                    @foreach($users as $user)
                    <option value="{!! $user['id'] !!}">{!! $user['name'] !!}</option>
                    @endforeach
                    @endif
                  </select>
                </div>
                <div>
                  <select class="form-control select2 " name="state_id" id="state_id" onchange="getDistrictList()">
                      <option value="">Select {!! trans('panel.global.state') !!}</option>
                      @if($states && count($states) > 0)
                      @foreach($states as $state)
                      <option value="{!! $state->id !!}" {{ old('state_id', isset($lead) && $lead->address ? $lead->address->state_id : '') == $state->id ? 'selected' : '' }}>{!! $state->state_name !!}</option>
                      @endforeach
                      @endif
                  </select>
                </div>
                <div>
                  <select class="form-control select2 district" name="district_id" id="district_id" onchange="getCityList()">
                      @if(isset($lead) && $lead->address && $lead->address->district_id)
                      <option value="{!!  $lead->address->district_id !!}" selected>{!! $lead->address->districtname->district_name ?? '' !!}</option>
                      @else
                      <option value="">Select {!! trans('panel.global.district') !!}</option>
                      @endif
                    </select>
                </div>
                <div>
                  <select class="form-control select2 city" name="city_id" id="city_id" onchange="getPincodeList()">
                      @if(isset($lead) && $lead->address && $lead->address->city_id)
                      <option value="{!!  $lead->address->city_id !!}" selected>{!! $lead->address->cityname->city_name??'' !!}</option>
                      @else
                      <option value="">Select {!! trans('panel.global.city') !!}</option>
                      @endif
                    </select>
                </div>
                </div>
              <div class="pream_entry">

                <div class="search">
                  <div class="search_inner">
                    <button type="button"> <img src="https://expertfromindia.in/bediya/public/assets/img/search.svg"></button>
                    <input type="search" class="searchbox" id="search_lead" placeholder="Search Lead">
                  </div>
                </div>

                <div class="both_btn d-flex">
                <a href="{{route('leads.create')}}" class="btn btn-primary btn-sm btn-icon-split float-right" title="{!! trans('panel.global.add') !!} Lead">
                  <span class="icon text-white-50">
                    <i class="material-icons">add_circle</i>
                  </span>
                  <span class="text">Add Lead</span>
                </a>

                  <a href="{{route('leads-exportLeads')}}" class="btn exportbtn btn-primary btn-sm btn-icon-split float-right" id="export_button">
                    <span class="icon text-white-50">
                      <i class="material-icons">cloud_download</i>
                    </span>
                    <span class="text">Export</span>
                  </a>
                  @if(auth()->user()->can(['lead_template']))
                  <a href="{{ URL::to('lead-template') }}" class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.template') !!} Leads"><i class="material-icons">text_snippet</i></a>
                  @endif
                  @if(auth()->user()->can(['lead_upload']))
                  <form action="{{ URL::to('lead-upload') }}" class="form-horizontal" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="input-group">
                      <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                        <span class="btn btn-just-icon btn-theme btn-file">
                          <span class="fileinput-new"><i class="material-icons">attach_file</i></span>
                          <span class="fileinput-exists">Change</span>
                          <input type="hidden">
                          <input type="file" title="Select File" name="import_file" required accept=".xls,.xlsx" />
                        </span>
                      </div>
                      <div class="input-group-append">
                        <button class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.upload') !!} Leads">
                          <i class="material-icons">cloud_upload</i>
                          <div class="ripple-container"></div>
                        </button>
                      </div>
                    </div>
                  </form>
                  @endif
                </div>
              </div>
              </div>
            </span>
          </h4>

        </div>
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
          @if(session()->has('message_success'))
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <i class="material-icons">close</i>
            </button>
            <span>
              {!!session()->get('message_success') !!}
            </span>
          </div>
          @endif
          <!--  -->
          <div class="well">
            <div class="dd">
            </div>

            <div class="sort_btn d-flex">
              <div class="ass_del d-none">
                <div class="btn-group">
                  <select name="user_id" id="user_id" class="form-control">
                    <option value="">Assign Lead</option>
                    @if(@isset($users ))
                    @foreach($users as $user)
                    <option value="{!! $user['id'] !!}">{!! $user['name'] !!}</option>
                    @endforeach
                    @endif
                  </select>
                </div>
                <button type="button" class="btn mr-3 ml-3" id="del_btn"><i class="material-icons icon mr-1">delete</i>Delete</button>
                <button type="button" class="btn mr-3 ml-3" id="convert_btn"><i class="material-icons icon mr-1">transcribe</i>Convert to Customer</button>
              </div>
            </div>
          </div>
          <!--  -->
          <div class="alert fk-js-alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <i class="material-icons">close</i>
            </button>
            <span class="message"></span>
          </div>
          <div class="table-responsive">
            <table id="getLeads" class="table text-wrap">
              <thead class=" text-primary">
                <tr>
                  <th><input type="checkbox" id="checkAll"></th>
                  <th>Date</th>
                  <th>Company Name</th>
                  <th>Contact</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>City</th>
                  <th>Status</th>
                  <th>Assigned To</th>
                  <th>Note</th>
                  <th>Others</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <link href="{{ url('/').'/'.asset('vendor/bootstrap-daterange/daterangepicker.css') }}" rel="stylesheet">
  <script src="{{ url('/').'/'.asset('assets/js/jquery.custom.js') }}"></script>
  <!-- Load jQuery and moment.js -->
  <!-- <script src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script> -->

  <script src="{{ url('/').'/'.asset('vendor/bootstrap-daterange/daterangepicker.min.js') }}"></script>

  <script>
    jQuery(document).ready(function() {
      getLeads();

      jQuery('#frmFilter').submit(function() {
        getLeads();
        return false;
      });

      jQuery('#frmLeadsCreate').validate({
        rules: {
          company_name: {
            required: true
          },
          contact_name: {
            required: true
          },
        }

      });


      $(document).on('change', '#checkAll', function() {
        console.log('checkAll', this.checked);
        $('.lead-checkbox').prop('checked', this.checked);
        let selectedIds = [];

        $('.checkbox_cls:checked').each(function() {
          selectedIds.push($(this).val());
        });

        if (selectedIds.length > 0) {
          $('.ass_del').removeClass('d-none');
        } else {
          $('.ass_del').addClass('d-none');
        }
      });


      jQuery('#frmFilter [name="datetime"]').daterangepicker({
        autoUpdateInput: false,
        locale: {
          cancelLabel: 'Clear'
        }
      }).val("{{ old('datetime') }}");
      jQuery('#frmFilter [name="datetime"]').on('apply.daterangepicker', function(ev, picker) {
        jQuery(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        var this_val = jQuery(this).val();
        var assign_to = jQuery('#user_id_search').val();
        var export_button_url = "{{route('leads-exportLeads')}}?datetime=" + this_val + "&assign_to=" + assign_to;
        $('#export_button').attr('href', export_button_url);
        //console.log(jQuery(this).val());
        getLeads();
      }).on('cancel.daterangepicker', function(ev, picker) {
        jQuery(this).val('');
      });


    });



    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $('#company_name, #contact_name').on('keyup', function() {
      var company_name = $('#company_name').val();
      var contact_name = $('#contact_name').val();
      $.post("{{route('leads.searchExistsLead')}}", {
        company_name: company_name,
        contact_name: contact_name
      }, function(response) {
        $('#lead_exist_data').html(response);
      });
    });



    function getLeads() {
      jQuery('#getLeads').dataTable().fnDestroy();
      jQuery('#getLeads tbody').empty();
      var datetime = jQuery('#frmFilter [name=datetime]').val();
      var search = jQuery('#search_lead').val();
      var assign_to = jQuery('#user_id_search').val();
      var state_id = jQuery('#state_id').val();
      var district_id = jQuery('#district_id').val();
      var city_id = jQuery('#city_id').val();
      var status = jQuery('#status').val();
      var table = jQuery('#getLeads').DataTable({
        processing: false,
        serverSide: true,
        searching: false,
        ajax: {
          url: '{{ route('leads.getLeads') }}',
          method: 'POST',
          data: {
            datetime: datetime,
            search: jQuery('#search_lead').val(),
            status: status,
            assign_to: assign_to,
            state_id: state_id,
            district_id: district_id,
            city_id: city_id
          }
        },
        columns: [{
            data: 'checkbox',
            name: 'checkbox',
            orderable: false,
            searchable: false
          },
          {
            data: 'created_at',
            name: 'created_at'
          },
          {
            data: 'company_name',
            name: 'company_name'
          },
          {
            data: 'contacts',
            name: 'contacts',
            orderable: false,
            searchable: false
          },
          {
            data: 'phone',
            name: 'phone',
            orderable: false,
            searchable: false
          },
          {
            data: 'email',
            name: 'email',
            orderable: false,
            searchable: false
          },
          {
            data: 'city',
            name: 'city',
            orderable: false,
            searchable: false
          },
          {
            data: 'status',
            name: 'status',
            searchable: false
          },
          {
            data: 'assign_to',
            name: 'assign_to',
            searchable: false
          },
          {
            data: 'note',
            name: 'note',
            orderable: false,
            searchable: false
          },
          {
            data: 'others',
            name: 'others',
            orderable: false,
            searchable: false
          },
        ],
        order: [
          [1, 'desc']
        ],
        dom: 't<"bottom"lip>',

      });
      table.on('xhr', function(e, settings, json) {
          if (json && json.records_filtered_count !== undefined) {
              jQuery('.brig.ml-2').text(json.records_filtered_count + ' Leads');
          }
      });
    }

    $('#search_lead').on('keyup', function() {
      getLeads();
    });
    $('#user_id_search').on('change', function() {
      var this_val = jQuery('#frmFilter [name="datetime"]').val();
      var assign_to = jQuery(this).val();
      var export_button_url = "{{route('leads-exportLeads')}}?datetime=" + this_val + "&assign_to=" + assign_to;
      $('#export_button').attr('href', export_button_url);
      getLeads();
    });
    $('#state_id').on('change', function() {
      getLeads();
    })
    $('#district_id').on('change', function() {
      getLeads();
    })
    $('#city_id').on('change', function() {
      getLeads();
    })
    $('#status').on('change', function() {
      getLeads();
    });

    function resetFilter() {
      jQuery('#frmFilter :input:not(:button, [type="hidden"])').val('');
      getLeads();
    }

    $(document).on('change', '.checkbox_cls', function() {
      let selectedIds = [];

      $('.checkbox_cls:checked').each(function() {
        selectedIds.push($(this).val());
      });

      if (selectedIds.length > 0) {
        $('.ass_del').removeClass('d-none');
      } else {
        $('.ass_del').addClass('d-none');
      }
    });

    $('#user_id').on('change', function() {
      var user_id = $(this).val();
      let selectedIds = [];
      $('.checkbox_cls:checked').each(function() {
        selectedIds.push($(this).val()); // or $(this).data('id')
      });
      if (selectedIds.length > 0) {
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't assign this leads!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: "{{ route('leads.assignLead') }}",
              method: 'POST',
              data: {
                lead_id: selectedIds,
                user_id: user_id,
                _token: '{{ csrf_token() }}'
              },
              success: function(res) {
                if (res.status == 'success') {
                  getLeads();
                  $('.ass_del').addClass('d-none');
                  Swal.fire({
                    icon: 'success',
                    title: res.message
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: res.message
                  });
                }
              }
            })
          }
        })
      } else {
        // None selected
        $('.ass_del').addClass('d-none');
        // $('#deleteButton').prop('disabled', true);
      }
    });

    $('#del_btn').on('click', function() {
      let selectedIds = [];
      $('.checkbox_cls:checked').each(function() {
        selectedIds.push($(this).val()); // or $(this).data('id')
      });
      if (selectedIds.length > 0) {
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't delete this leads!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: "{{ route('leads.deleteLead') }}",
              method: 'POST',
              data: {
                lead_id: selectedIds,
                _token: '{{ csrf_token() }}'
              },
              success: function(res) {
                if (res.status == 'success') {
                  getLeads();
                  $('.ass_del').addClass('d-none');
                  Swal.fire({
                    icon: 'success',
                    title: res.message
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: res.message
                  });
                }
              }
            })
          }
        })
      } else {
        // None selected
        $('.ass_del').addClass('d-none');
        // $('#deleteButton').prop('disabled', true);
      }
    });
    $('#convert_btn').on('click', function() {
      let selectedIds = [];
      $('.checkbox_cls:checked').each(function() {
        selectedIds.push($(this).val()); // or $(this).data('id')
      });
      if (selectedIds.length > 0) {
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't convert leads into Customer!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, convert it!'
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: "{{ route('leads.convert') }}",
              method: 'POST',
              data: {
                lead_id: selectedIds,
                _token: '{{ csrf_token() }}'
              },
              success: function(res) {
                if (res.status == 'success') {
                  getLeads();
                  $('.ass_del').addClass('d-none');
                  Swal.fire({
                    icon: 'success',
                    title: res.message
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: res.message
                  });
                }
              }
            })
          }
        })
      } else {
        // None selected
        $('.ass_del').addClass('d-none');
        // $('#deleteButton').prop('disabled', true);
      }
    });
  </script>

</x-app-layout>
