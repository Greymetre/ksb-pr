<x-app-layout>


<style>

/* ============================= */
/*      SCHEDULE BEAT TABLE      */
/* ============================= */

#tab_beat_schedule {
    width: 100%;
}

#tab_beat_schedule th,
#tab_beat_schedule td {
    vertical-align: middle;
    padding: 8px;
}

/* Limit column width to approx col-3 (25%) */

#tab_beat_schedule th:nth-child(2),
#tab_beat_schedule td:nth-child(2),
#tab_beat_schedule th:nth-child(3),
#tab_beat_schedule td:nth-child(3) {
    width: 25%;
}

/* First and last small columns */
#tab_beat_schedule th:nth-child(1),
#tab_beat_schedule td:nth-child(1),
#tab_beat_schedule th:nth-child(4),
#tab_beat_schedule td:nth-child(4) {
    width: 5%;
    text-align: center;
}

/* Inputs & Select max width col-3 */
#tab_beat_schedule select,
#tab_beat_schedule input {
    max-width: 250px;   /* roughly col-3 */
    width: 100%;
}

/* Flatpickr fix */
.flatpickr-input {
    max-width: 250px;
    width: 100% !important;
}

/* Center align action button */
#tab_beat_schedule .btn {
    padding: 6px 10px;
}

/* Prevent layout break */
.table-responsive {
    overflow-x: auto;
}

/* Floating Submit Button */
.floating-submit-btn {
    position: fixed;
    bottom: 25px;
    right: 30px;
    z-index: 9999;
    padding: 12px 25px;
    font-size: 15px;
    border-radius: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Hover effect */
.floating-submit-btn:hover {
    transform: translateY(-2px);
    transition: 0.2s ease;
}

</style> 

{!! Form::model($beats,[
      'route' => $beats->exists ? ['beats.update', encrypt($beats->id) ] : 'beats.store',
      'method' => $beats->exists ? 'PUT' : 'POST',
      'id' => 'storeBeatData',
      'files'=>true
      ]) !!}
<div class="row mt-4">
	<div class="col-lg-6">
		<div class="card p-0" data-animation="true">
			<div class="card-body">
				<h5 class="newdata">Beat Management</h5>
            <div class="row">
               <div class="col-md-12">
                  <div class="input_section">
                     <label class="col-form-label">{!! trans('panel.beat.beat_name') !!} <span class="text-danger"> *</span></label>
                     <input type="text" name="beat_name" id="beat_name" class="form-control" value="{!! old( 'beat_name', $beats['beat_name']) !!}"  maxlength="200" required>
                     @if ($errors->has('beat_name'))
                        <div class="error col-lg-12">
                           <p class="text-danger">{{ $errors->first('beat_name') }}</p>
                        </div>
                     @endif
                  </div>
               </div>
               <div class="col-md-12">
                  <div class="input_section">
                     <label class="col-form-label">{!! trans('panel.global.state') !!} </label>
                     <select class="form-control select2 state" name="state_id" style="width: 100%;" onchange="getDistrictList()">
                        <option value="">Select {!! trans('panel.global.state') !!}</option>
                        @if(@isset($states))
                        @foreach($states as $state)
                        <option value="{!! $state['id'] !!}" {!! ($beats['state_id'] ==  $state['id']) ? 'selected' : ''!!}>{!! $state['state_name'] !!}</option>
                        @endforeach
                        @endif
                     </select>
                     @if ($errors->has('state_id'))
                        <div class="error col-lg-12">
                           <p class="text-danger">{{ $errors->first('state_id') }}</p>
                        </div>
                     @endif
                  </div>
               </div>
               <div class="col-md-12">
                  <div class="input_section">
                     <label class="col-form-label">{!! trans('panel.global.district') !!} </label>
                     <select class="form-control select2 district" name="district_id[]" multiple style="width: 100%;" onchange="getCityListMultiDis()">
                        <option value="">Select {!! trans('panel.global.district') !!}</option>
                        @if(@isset($districts))
                        @foreach($districts as $district)
                        <option value="{!! $district['id'] !!}" @if(in_array($district['id'],explode(',',$beats['district_id']))) selected @endif>{!! $district['district_name'] !!}</option>
                        @endforeach
                        @endif
                     </select>
                     @if ($errors->has('district_id'))
                        <div class="error">
                           <p class="text-danger">{{ $errors->first('district_id') }}</p>
                        </div>
                     @endif
                  </div>
                   </div>
                  <div class="col-md-12">
                  <div class="input_section">
                     <label class="col-form-label">{!! trans('panel.global.city') !!} </label>
                     <select class="form-control select2 city" id="city_id" name="city_id[]" multiple style="width: 100%;">
                        <option value="">Select {!! trans('panel.global.city') !!}</option>
                        @if(@isset($cities))
                        @foreach($cities as $city)
                        <option value="{!! $city['id'] !!}" @if(in_array($city['id'],explode(',',$beats['city_id']))) selected @endif>{!! $city['city_name'] !!}</option>
                        @endforeach
                        @endif
                     </select>
                     @if ($errors->has('city_id'))
                        <div class="error">
                           <p class="text-danger">{{ $errors->first('city_id') }}</p>
                        </div>
                     @endif
                  </div>
               </div>

                 <div class="col-md-12">
                  <div class="input_section">
                     <label class="col-form-label">{!! trans('panel.beat.description') !!} <span class="text-danger"> *</span></label>
                     <textarea name="description" class="form-control" rows="5">{!! old( 'description', $beats['description']) !!}</textarea>
                     @if ($errors->has('description'))
                     <div class="error">
                        <p class="text-danger">{{ $errors->first('description') }}</p>
                     </div>
                     @endif
                  </div>
               </div>





            </div>
          
         
            @if($beats->exists)
               {{ Form::submit('Submit', array('class' => 'btn btn-theme pull-right')) }} 
            @endif
			</div>
		</div>
	</div>
   @if($beats->exists)
      {{ Form::close() }}
   @endif
   
   <div class="col-lg-6">
		<div class="card p-0" data-animation="true">
			<div class="card-body">
				<h5 class="newdata"> Beat User</h5>
            <div class="row p-2">
               <div class="table-responsive">
               <a href="javascript:;" title="Add a row" class="btn btn-just-icon btn-success pull-right add-users-rows" onclick="getUserlist()">+</a>
               @if($beats->exists)
                  <form action="{{ URL::to('add-beatusers') }}" class="form-horizontal" method="post">
                  {{ csrf_field() }}
                  <input type="hidden" name="beat_id" id="beat_id" value="{{ $beats->id }}">
                  <!-- <input type="hidden" name="bxeat_id" id="beat_id" value="{!! old( 'beat_id', $beats->id) !!}" class="form-control select2"> -->
               @endif
                     <table class="table beat-users-rows" id="tab_beat_users">
                        <thead>
                           <tr class="item-row">
                              <th>No</th>
                              <th>User</th>
                              <th></th>
                           </tr>
                        </thead>
                        <tbody>
                           
                        </tbody>
                     </table>
                     @if($beats->exists)
                     <button class="btn btn-theme pull-right"> Add</button>
                     </form>
                     @endif
                  </div>
                  <div class="table-responsive">
                     <table class="table">
                        <tbody>
                           @if($beats->exists && isset($beats['beatusers']))
                              @foreach($beats['beatusers'] as $key => $index )
                                 <tr>
                                    <td>{!! $key+1 !!}</td>
                                    <td>
                                    <div class="input_section">
                                    <select  class="form-control select2 user" disabled><option value="{!! $index['user_id'] !!}" selected>{!! $index['users']['name'] !!}</option></select>
                                  </div></td>
                                    <td class="td-actions text-right">
                                     <a class="btn btn-danger" title="Remove row" onclick="deleteUserFromBeat({!! $index['id'] !!})"><i class="material-icons">close</i>
                                     </a>
                                   </td>
                                 </tr>
                              @endforeach
                           @endif
                        </tbody>
                     </table>
                  </div>
            </div>
            
			</div>
		</div>

      <!-- Beat Customer -->
      <div class="card p-0" data-animation="true">
			<div class="card-body">
				<h5 class="newdata"> Beat Customer</h5>

            <div class="row p-3">
               <div class="table-responsive">
               <a href="javascript:;" title="Add a row" class="btn pull-right btn-just-icon btn-info add-customer-rows" onclick="getRetailerlist()">+</a>
               @if($beats->exists)
               <form action="{{ URL::to('add-beatcustomers') }}" class="form-horizontal" method="post">
                  {{ csrf_field() }}
                  <input type="hidden" name="beat_id" value="{!! old( 'beat_id', $beats->id) !!}">
               @endif
                     <table class="table beat-customer-rows" id="tab_beat_customer">
                        <thead>
                           <tr class="item-row">
                              <th>No</th>
                              <th>Customer</th>
                           </tr>
                        </thead>
                        <tbody>
                        </tbody>
                     </table>
                     @if($beats->exists)
                     <button class="btn btn-theme pull-right"> Add</button>
                     </form>
                     @endif
                     <table class="table">
                        <tbody>
                           @if($beats->exists && isset($beats['beatcustomers']))
@foreach($beats['beatcustomers'] as $key => $rows)
<tr>
<td>{{ $key+1 }}</td>

<td>
    @php
        $customerName = null;
        $mobile = 'N/A';

        if ($rows->customer_type === 'secondary') {
            $customerName = optional($rows->retailer)->name ?? 'N/A';
            $mobile = optional($rows->retailer)->mobile ?? 'N/A';
        } elseif ($rows->customer_type === 'master') {
            $customerName = optional($rows->distributor)->name ?? 'N/A';
            $mobile = optional($rows->distributor)->mobile ?? 'N/A';
        }
    @endphp

    {{ $customerName }} ({{ $mobile }})
</td>

<td class="td-actions text-right">
<a class="btn btn-danger" title="Remove row" onclick="deletecustomers({{ $rows['id'] }})">
<i class="material-icons">close</i>
</a>
</td>
</tr>
@endforeach
                           @endif
                        </tbody>
                     </table>
                  </div>
                  @if($beats->exists && isset($beats['beatcustomers']))
                  <script>
                     window.existingBeatCustomers = [
                        @foreach($beats['beatcustomers'] as $rows)
                              "{{ $rows['distributor_id'] }}",
                        @endforeach
                     ];
                  </script>
                  @endif
            </div>
            
			</div>
		</div>
	</div>

   <!-- <div class="col-lg-6">
		
	</div> -->

   <div class="col-lg-12">
		<div class="card mt-4 p-0" data-animation="true">
			<div class="card-body">
				<h5 class="newdata"> Schedule Beat</h5>
            <div class="row p-3">
            <div class="table-responsive">
            <a href="javascript:;" title="Add a row" class="btn btn-just-icon btn-success pull-right add-schedule-rows" onclick="getScheduleUserlist()">+</a>
               @if($beats->exists)
                  <form action="{{ route('beats.saveIndividualSchedule')  }}" class="form-horizontal" method="post" id="scheduleForm">
                     {{ csrf_field() }}
                     <input type="hidden" name="beat_id" value="{!! old( 'beat_id', $beats->id) !!}">
               @endif
                     <table class="table beat-schedule-rows" id="tab_beat_schedule">
<thead>
    <tr class="item-row">
        <th style="width: 5%; text-align: center;">#</th>
        <th style="width: 22%;">User Name</th>
        <th style="width: 22%;">Start Date</th>
                <th style="width: 22%;">Recurrence</th>
        <th style="width: 22%;">End Date</th>

        <th style="width: 5%; text-align: center;">Action</th>
    </tr>
</thead>
                        <tbody>
              
                        </tbody>
                     </table>
                     @if($beats->exists)
                     <button class="btn btn-theme pull-right"> Add</button>
                     </form>
                     @endif
                     <table class="table">
                        <tbody>
                        @if($beats->exists && isset($beats['beatschedules']))
                              @foreach($beats['beatschedules'] as $key => $rows )
                                 <tr>
                                    <td>{!! $rows['id'] !!}</td>
                                    <td>{!! $rows['users']['name'] !!}</td>
                                    <td>{!! $rows['beat_date'] !!}</td>
                                    <td class="td-actions text-right">
                                      @if(auth()->user()->can(['beat_delete']))
                                        <a class="btn btn-danger" title="Remove row" onclick="deleteschedules({!! $rows['id'] !!})"><i class="material-icons">close</i>
                                        </a>
                                     @endif
                                   </td>
                                 </tr>
                              @endforeach
                           @endif
                        </tbody>
                     </table>
                  </div>
               </div>
			</div>
		</div>
      @if(!$beats->exists)
      {{ Form::submit('Submit', array('class' => 'btn btn-theme pull-right floating-submit-btn')) }} 
      @endif
	</div>
</div>
@if(!$beats->exists)
{{ Form::close() }}
@endif
<!-- <div class="row">
   <div class="col-md-12">
      <div class="card">
         <div class="card-header card-header-tabs card-header-warning">
            <div class="nav-tabs-navigation">
               <div class="nav-tabs-wrapper">
                  <h4 class="card-title ">
                     {!!  trans('panel.global.add') !!} {!! trans('panel.beat.title_singular') !!}
                     @if(auth()->user()->can(['district_access']))
                     <ul class="nav nav-tabs pull-right" data-tabs="tabs">
                        <li class="nav-item">
                           <a class="nav-link" href="{{ url('beats') }}">
                              <i class="material-icons">next_plan</i> {!! trans('panel.beat.title') !!}
                              <div class="ripple-container"></div>
                           </a>
                        </li>
                     </ul>
                     @endif
                  </h4>
               </div>
            </div>
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
            
            <div class="row">
               <div class="col-md-6">
                  <div class="form-group">
                     <label class="bmd-label-floating">{!! trans('panel.beat.beat_name') !!} <span class="text-danger"> *</span></label>
                     <input type="text" name="beat_name" class="form-control" id="customerInputName" value="{!! old( 'beat_name', $beats['beat_name']) !!}" >
                     @if ($errors->has('beat_name'))
                     <div class="error col-lg-12">
                        <p class="text-danger">{{ $errors->first('beat_name') }}</p>
                     </div>
                     @endif
                  </div>
               </div>
               <div class="col-md-6">
                  <div class="form-group">
                     <select class="form-control select2" name="district_id" style="width: 100%;">
                        <option value="">Select {!! trans('panel.global.district') !!}</option>
                        @if(@isset($districts))
                        @foreach($districts as $district)
                        <option value="{!! $district['id'] !!}" {!! (isset($beats['district_id']) ? $beats['district_id'] :'' ==  $district['id']) ? 'selected' : ''!!}>{!! $district['district_name'] !!}</option>
                        @endforeach
                        @endif
                     </select>
                     @if ($errors->has('district_id'))
                     <div class="error col-lg-12">
                        <p class="text-danger">{{ $errors->first('district_id') }}</p>
                     </div>
                     @endif
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12">
                  <div class="form-group">
                     <label class="bmd-label-floating">{!! trans('panel.beat.description') !!} <span class="text-danger"> *</span></label>
                     <textarea name="description" class="form-control" rows="5">{!! old( 'description', $beats['description']) !!}</textarea>
                     @if ($errors->has('description'))
                     <div class="error col-lg-12">
                        <p class="text-danger">{{ $errors->first('description') }}</p>
                     </div>
                     @endif
                  </div>
               </div>
            </div>
            <hr>
            <div class="row">
               <div class="col-md-6">
                  <div class="table-responsive">
                     <table class="table beat-customer-rows" id="tab_beat_customer">
                        <thead>
                           <tr class="item-row">
                              <th>No</th>
                              <th>Customer</th>
                           </tr>
                        </thead>
                        <tbody>
                           @if($beats->exists && isset($beats['beatcustomers']))
                              @foreach($beats['beatcustomers'] as $key => $rows )
                                
                              @endforeach
                           @endif
                        </tbody>
                     </table>
                  </div>
                  <a href="javascript:;" title="Add a row" class="btn btn-just-icon btn-success pull-left add-customer-rows" onclick="getRetailerlist()">+</a>
               </div>
               <div class="col-md-6">
                  <div class="table-responsive">
                     <table class="table beat-users-rows" id="tab_beat_users">
                        <thead>
                           <tr class="item-row">
                              <th>No</th>
                              <th>User</th>
                           </tr>
                        </thead>
                        <tbody>
                           @if($beats->exists && isset($beats['beatusers']))
                              @foreach($beats['beatusers'] as $key => $index )
                                 <tr>
                                    <td>{!! $key+1 !!}</td>
                                    <td>{!! $index['users']['name'] !!}</td>
                                    <td></td>
                                 </tr>
                              @endforeach
                           @endif
                        </tbody>
                     </table>
                  </div>
                  <a href="javascript:;" title="Add a row" class="btn btn-just-icon btn-success pull-right add-users-rows" onclick="getUserlist()">+</a>
               </div>
            </div>
            <hr class="my-1">
            <h4 class="section-heading mb-3  h4 mt-0 text-center text-info">Schedule Beat</h4> 
            <div class="row">
               <div class="col-md-8">
                  <div class="table-responsive">
                     <table class="table beat-schedule-rows" id="tab_beat_schedule">
                        <thead>
                           <tr class="item-row">
                              <th></th>
                              <th>User Name</th>
                              <th>Date</th>
                              <th></th>
                           </tr>
                        </thead>
                        <tbody>

                        </tbody>
                     </table>
                  </div>
                  <a href="javascript:;" title="Add a row" class="btn btn-just-icon btn-success pull-right add-schedule-rows" onclick="getScheduleUserlist()">+</a>
               </div>
            </div>
            
         </div>
      </div>
   </div>
   <div class="col-md-12">
      <div class="card">
         <div class="card-header card-header-tabs card-header-warning">
            <div class="nav-tabs-navigation">
               <div class="nav-tabs-wrapper">
                  <h4 class="card-title ">User List</h4>
               </div>
            </div>
         </div>
         <div class="card-body">
         
            <table class="table">
              <thead>
                <tr class="item-row">
                  <th> # </th>
                  <th>User</th>
                  <th>Date</th>
                  <th></th>
                </tr>
              </thead>
              @if($beats->exists && !empty($beats->beatschedules))
                      @foreach($beats->beatschedules as $key => $rows )
                  <tr>
                    <td class="text-primary"> {!! $key+1 !!}</td>
                    <td>{!! $rows['users']['name'] !!}</td>
                    <td>{!! $rows['beat_date'] !!}</td>
                    <td class="td-actions text-right">
                      <a class="btn btn-danger" title="Remove row" onclick="deleteschedules({!! $rows['id'] !!})"><i class="material-icons">close</i>
                      </a>
                    </td>
                  </tr>
                @endforeach
              @endif
            </table>
         </div>
      </div>
    </div>
</div> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://silver.fieldkonnect.io//public/assets/js/core/jquery.validate.js"></script>
<script src="{{ asset('assets/js/jquery.custom.js') }}"></script>

<!-- <script src="{{ asset('public/assets/js/jquery.beat.js') }}"></script> -->
<!-- <script src="{{ url('/').'/'.asset('assets/js/jquery.custom.js') }}"></script>
<script src="{{ url('/').'/'.asset('assets/js/jquery.beat.js') }}"></script> -->



<script>
//    $(document).ready(function(){
//     $('.select2').select2({
//         placeholder: "Select User",
//         allowClear: true,
//         width: '100%'
//     });
// });
</script>
<script>
    // PHP se Laravel data ko JSON me convert karke JS variable me daal do
    let beats = @json($beats);

    console.log(beats); // pura object dekh sakte ho

    // Beatcustomers ke customers dekhne ke liye
    beats.beatcustomers.forEach(bc => {
        console.log(bc.customer); // sirf unified customer object
    });
</script>

<script>



function getUserlist()
{
  var token = $("meta[name='csrf-token']").attr("content");
  var base_url =$('.baseurl').data('baseurl'); 

    $.ajax({
        url: base_url + '/getUserList',
        dataType: "json",
        type: "POST",
        data:{ "_token": token},
        success: function(res){
            if(res){
var $select = $('#tab_beat_users tbody tr:last').find(".user");
              $select.empty();
              $select.append('<option value="">Select User</option>');
              
              $.each(res,function(key,value){ 
                $select.append(
                  '<option value="'+value.id+'">'+value.name+' '+value.mobile+'</option>'
                );
              });

              // 🔥 SELECT2 APPLY HERE
              $select.select2({
                  placeholder: "Select User",
                  allowClear: true,
                  width: '100%'
              });
            }
        }
    });
}


let selectedCustomers = new Set();

// Helper: Rebuild all customer dropdowns excluding taken IDs
function updateAllCustomerDropdowns() {
    const allSelects = document.querySelectorAll('.beat-customer-rows .customer');

    allSelects.forEach(select => {
        const currentValue = select.value || '';

        // Clear & rebuild
        select.innerHTML = '<option value="">Select Customer</option>';

        if (window.allAvailableCustomers && window.allAvailableCustomers.length > 0) {
            window.allAvailableCustomers.forEach(cust => {
                const idStr = String(cust.id);

                // Show option if:
                // - not taken by anyone else, OR
                // - it is the currently selected value in THIS dropdown
                if (!selectedCustomers.has(idStr) || idStr === currentValue) {
                    const option = new Option(
                        `${cust.name} ${cust.mobile || ''}`.trim(),
                        cust.id
                    );
                    select.add(option);
                }
            });
        }

        // Restore current selection (if still allowed)
        if (currentValue) {
            select.value = currentValue;
        }
    });

    // Re-init select2 (only if you're using it)
    if (typeof $.fn.select2 !== 'undefined') {
        $('.customer').select2();
    }
}



// When you get the list from server (first time or refresh)
function getRetailerlist() {
    var base_url = $('.baseurl').data('baseurl');
    var token    = $("meta[name='csrf-token']").attr("content");
    var state_id = $("select[name=state_id]").val();
    var district_id = $(".district").val() || [];
    var city_id     = $(".city").val() || [];
    var users = [];
    $(".user:selected").each(function(){ users.push($(this).val()); });

    $.ajax({
        url: base_url + '/getRetailerlist',
        dataType: "json",
        type: "POST",
        data: {
            "_token": token,
            state_id: state_id,
            district_id: district_id,
            city_id: city_id,
            user_id: users
        },
        success: function(res) {
            // Store full list globally
            window.allAvailableCustomers = res || [];
            updateAllCustomerDropdowns();

            // Populate only the last (new) row
            const lastSelect = $('#tab_beat_customer tr:last .customer')[0];
            if (lastSelect) {
                lastSelect.innerHTML = '<option value="">Select Customer</option>';

                res.forEach(cust => {
                    if (!selectedCustomers.has(String(cust.id))) {
const opt = new Option(`${cust.name} (${cust.mobile})`, cust.id);
opt.setAttribute("data-type", cust.type);
lastSelect.add(opt);
                        lastSelect.add(opt);
                    }
                });

                $(lastSelect).select2();
            }
        },
        error: function() {
            console.error("Failed to load customers");
        }
    });
}

// ────────────────────────────────────────────────
// Customers dynamic rows + duplicate prevention
// ────────────────────────────────────────────────
$(document).ready(function () {
    const $customerTable = $('table.beat-customer-rows');
    let customerCounter = $customerTable.find('tbody tr').length + 1;
    if (window.existingBeatCustomers && window.existingBeatCustomers.length > 0) {
    window.existingBeatCustomers.forEach(id => {
        selectedCustomers.add(String(id));
    });
}

    // Add new row
    $('a.add-customer-rows').on('click', function (e) {
        e.preventDefault();

        const newRow = `
            <tr class="item-row">
                <td>${customerCounter}</td>
                <td>
<select name="customers[]" 
        class="form-control customer rowchange select2">
    <option value="">Select Customer</option>
</select>

<input type="hidden" name="customer_type[]" class="customer_type">
                </td>
                <td class="td-actions text-right">
                    <a class="remove-customer-rows btn btn-danger" title="Remove row">
                        <i class="material-icons">close</i>
                    </a>
                </td>
            </tr>`;

        $customerTable.find('tbody').append(newRow);
        getRetailerlist(); // populate new row
        customerCounter++;
    });

    // Remove dynamic row
    $customerTable.on('click', '.remove-customer-rows', function () {
        const removedId = $(this).closest('tr').find('.customer').val();
        if (removedId) selectedCustomers.delete(String(removedId));
        $(this).closest('tr').remove();
        updateAllCustomerDropdowns();
    });

    // Track changes in dynamic selects
    $(document).on('change', '.beat-customer-rows .customer', function () {
        const customerId = this.value;
            const customerText = $(this).find(":selected").text();

    console.log("Selected Customer ID:", customerId);
    console.log("Selected Customer Name:", customerText);
        const $row = $(this).closest('tr');
        const prevId = $row.data('prev-customer-id');

        if (prevId) selectedCustomers.delete(String(prevId));

        if (customerId) {
            selectedCustomers.add(String(customerId));
            $row.data('prev-customer-id', customerId);
        } else {
            $row.removeData('prev-customer-id');
        }


            const type = $(this).find(':selected').data('type');
    $row.find('.customer_type').val(type);
    
        updateAllCustomerDropdowns();
    });

    // ────────────────────────────────────────────────
    // Load existing (saved) customers in edit view
    // ────────────────────────────────────────────────

    // Run once on page load

    // Also catch any dynamic rows that might already exist
    $('.beat-customer-rows .customer').each(function () {
        const val = $(this).val();
        if (val && val !== '') {
            selectedCustomers.add(String(val));
        }
    });

    // Initial refresh
    // (will be more accurate after first getRetailerlist call)

    updateAllCustomerDropdowns();
});

// ────────────────────────────────────────────────
// Beat Users - Add / Remove dynamic rows
// ────────────────────────────────────────────────
$(document).ready(function(){


$(document).on('submit', 'form[action*="add-beatcustomers"]', function(e){

    e.preventDefault();

    let payload = {
        beat_id: $(this).find('input[name="beat_id"]').val(),
        customers: [],
        customer_type: []
    };

    $('#tab_beat_customer tbody tr').each(function(){

        let customerId = $(this).find('.customer').val();
        let type = $(this).find('.customer_type').val();

        if(customerId){
            payload.customers.push(customerId);
            payload.customer_type.push(type);
        }

    });

    console.log("Beat Customer Payload:", payload);

});
    var $usersTable = $('table.beat-users-rows');
    var counter = $usersTable.find('tbody tr').length + 1;  // better for edit mode

    $('a.add-users-rows').on('click', function(event){
        event.preventDefault();
        
        var newRow = 
            '<tr class="item-row">' +
                '<td>' + counter + '</td>' +
                '<td>' +
                    '<select name="users[]" class="form-control user rowchange select2">' +
                        '<option value="">Select User</option>' +
                    '</select>' +
                '</td>' +
                '<td class="td-actions text-right">' +
                    '<a class="remove-user-rows btn btn-danger" title="Remove row">' +
                        '<i class="material-icons">close</i>' +
                    '</a>' +
                '</td>' +
            '</tr>';

        $usersTable.find('tbody').append(newRow);

        // Load users into the newest row
        getUserlist();   // your existing function targets #tab_beat_users tr:last .user

        counter++;
    });

    $usersTable.on('click', '.remove-user-rows', function() {
        $(this).closest('tr').remove();
        // Optional: renumber if you care about the numbers
    });
});

function getScheduleUserlist()
{
  var token = $("meta[name='csrf-token']").attr("content");
  var base_url =$('.baseurl').data('baseurl'); 
  var beat_id = $('#beat_id').val();

    $.ajax({
        url: base_url + '/getUserList',
        dataType: "json",
        type: "POST",
        data:{ "_token": token, beat_id : beat_id},
        success: function(res){

            if(res){
                var $select = $('#tab_beat_schedule tr:last').find(".user");

                $select.empty();
                $select.append('<option value="">Select User</option>');
                
                $.each(res,function(key,value){ 
                    $select.append(
                      '<option value="'+value.id+'">'+value.name+' '+value.mobile+'</option>'
                    );
                });

                // 🔥 APPLY SELECT2 HERE
                $select.select2({
                    placeholder: "Select User",
                    allowClear: true,
                    width: '100%'
                });
            }
        }
    });
}


$(document).ready(function(){
    var $table = $('table.beat-schedule-rows'),
    counter = 1;
   $('a.add-schedule-rows').click(function(e){
    e.preventDefault();
    counter++;

    var newRow = `
    <tr>
        <td>${counter}</td>

        <td>
            <select name="beatdetail[${counter}][user_id]" 
                    class="form-control user select2"></select>
        </td>

      

        <td>
            <input type="date" 
                   name="beatdetail[${counter}][start_date]" 
                   class="form-control startPicker"
                   placeholder="Start Date">
        </td>

        <input type="hidden" name="beat_id" value="{{ $beats->id }}">

          <td>
            <select name="beatdetail[${counter}][schedule_type]" 
                    class="form-control scheduleType">
                <option value="">Select Type</option>
              <option value="single">Does Not Repeat</option>
            </select>
        </td>

<td>  <!-- Always visible now -->
        <input type="date" name="beatdetail[${counter}][end_date]" class="form-control endPicker" disabled placeholder="End Date">
    </td>

      <td style="display:none;" class="multi-date">
    <input type="text" 
           name="beatdetail[${counter}][multiple_dates]" 
           class="form-control multiPicker" hidden>
</td>

        <td>
            <a class="remove-rows btn btn-danger">
                <i class="material-icons">close</i>
            </a>
        </td>
    </tr>`;

$('#tab_beat_schedule tbody').append(newRow);

let lastRow = $('#tab_beat_schedule tbody tr:last');

// Initialize datepickers
// lastRow.find('.startPicker').flatpickr({
//     dateFormat: "Y-m-d"
// });

// lastRow.find('.endPicker').flatpickr({
//     dateFormat: "Y-m-d"
// });

initFlatpickr(lastRow.find('.startPicker')[0]);
flatpickr(lastRow.find('.endPicker')[0], {
    dateFormat: "Y-m-d",
    mode: "single"
});

// Trigger schedule type change
lastRow.find('.scheduleType')
    .val('single')
    .trigger('change');

getScheduleUserlist();
});
// Remove schedule row
$(document).on('click', '.remove-rows', function(e){
    e.preventDefault();
    $(this).closest('tr').remove();
});


if ($('#tab_beat_schedule').length && $('#tab_beat_schedule tbody tr').length === 0) {
        $('.add-schedule-rows').trigger('click');
    }
});









function deleteschedules(e)
{
    if (confirm('Are you sure you want to delete this?')) {
        var id = e; 
        var token = $("meta[name='csrf-token']").attr("content");
        var base_url =$('.baseurl').data('baseurl');
        $.ajax({
            url: base_url + '/schedule-delete/'+id,
            dataType: "json",
            type: "DELETE",
            cache: false,
            data:{"id": id,"_token": token},
            success: function(res){
                if(res)
                {
                    swal({
                      title: "Deleted!",
                      text: "Schedule has been deleted successfully",
                      type: "success",
                      confirmButtonText: "OK",
                    });
                    location.reload();
                }
            }
        });
    }
    else
    {
        location.reload();
    }
}
function deleteUserFromBeat(e)
{
    if (confirm('Are you sure you want to delete this?')) {
        var id = e; 
        var token = $("meta[name='csrf-token']").attr("content");
        var base_url =$('.baseurl').data('baseurl');
        $.ajax({
            url: base_url + '/beat-user-delete/'+id,
            dataType: "json",
            type: "DELETE",
            cache: false,
            data:{"id": id,"_token": token},
            success: function(res){
                if(res)
                {
                    swal({
                      title: "Deleted!",
                      text: "Schedule has been deleted successfully",
                      type: "success",
                      confirmButtonText: "OK",
                    });
                    location.reload();
                }
            }
        });
    }
    else
    {
        location.reload();
    }
}

function deletecustomers(e)
{
    if (confirm('Are you sure you want to delete this?')) {
        var id = e; 
        var token = $("meta[name='csrf-token']").attr("content");
        var base_url =$('.baseurl').data('baseurl');
        $.ajax({
            url: base_url + '/beatcustomer-delete/'+id,
            dataType: "json",
            type: "DELETE",
            cache: false,
            data:{"id": id,"_token": token},
            success: function(res){
                if(res)
                {
                    swal({
                      title: "Deleted!",
                      text: "Schedule has been deleted successfully",
                      type: "success",
                      confirmButtonText: "OK",
                    });
                    location.reload();
                }
            }
        });
    }
    else
    {
        location.reload();
    }
}

/*=============== Beat Validation =====================*/
  $('#storeBeatData').validate({
    rules:{
      beat_name:
      {
        required:true,
        minlength:3,
        maxlength: 250,
      },
      description:
      {
        required:true,
        minlength:3,
        maxlength: 450,
      },
    },
    highlight: function(element) {
      $(element).closest('.error').css("display", "none");
    },
    unhighlight: function(element) {
      $(element).closest('.error').css("display", "block");
    },
    messages:{
      name:{
        minlength: "Please enter a valid Award Name.",
        required: "Please enter Award Name",
      },
      description:{
        required: "Please enter Description",
      },
    }
  });
    $('#scheduleForm').on('submit', function(e){

    e.preventDefault();   // 🔥 VERY IMPORTANT

    var form = $(this);
    var base_url = $('.baseurl').data('baseurl');

    $.ajax({
        url: form.attr('action'),
        type: "POST",
        data: form.serialize(),
        success: function(response){

            if(response.status){

                swal({
                    title: "Success!",
                    text: response.message,
                    type: "success",
                    confirmButtonText: "OK"
                });

                // reload page after save
                setTimeout(function(){
                    location.reload();
                }, 1000);
            }
        },
        error: function(xhr){
            console.log(xhr.responseText);
        }
    });

});

let flatpickrInstance =null;

// function initFlatpickr(type, inputElement) {

//     // destroy previous instance
//     if (flatpickrInstance) {
//         flatpickrInstance.destroy();
//     }

//     if (type === 'single') {

//         flatpickrInstance = flatpickr(inputElement, {
//             dateFormat: "Y-m-d",
//             mode: "single"
//         });

//     } 
//     else if (type === 'multiple') {

//         flatpickrInstance = flatpickr(inputElement, {
//             dateFormat: "Y-m-d",
//             mode: "multiple"
//         });

//     } 
//     else if (type === 'weekly') {

//         flatpickrInstance = flatpickr(inputElement, {
//             dateFormat: "Y-m-d",
//             mode: "range",
//             onClose: function(selectedDates, dateStr, instance) {

//                 if (selectedDates.length === 2) {

//                     let start = selectedDates[0];
//                     let end = selectedDates[1];
//                     let dates = [];
//                     let current = new Date(start);

//                     while (current <= end) {
//                         dates.push(instance.formatDate(current, "Y-m-d"));
//                         current.setDate(current.getDate() + 7);
//                     }

//                     instance.setDate(dates, true);
//                 }
//             }
//         });

//     } 
//     else if (type === 'monthly') {

//         flatpickrInstance = flatpickr(inputElement, {
//             dateFormat: "Y-m-d",
//             mode: "range",
//             onClose: function(selectedDates, dateStr, instance) {

//                 if (selectedDates.length === 2) {

//                     let start = selectedDates[0];
//                     let end = selectedDates[1];
//                     let dates = [];

//                     let weekday = start.getDay(); // same weekday
//                     let current = new Date(start);

//                     while (current <= end) {

//                         if (current.getDay() === weekday) {
//                             dates.push(instance.formatDate(current, "Y-m-d"));
//                         }

//                         current.setDate(current.getDate() + 7);
//                     }

//                     instance.setDate(dates, true);
//                 }
//             }
//         });
//     }
// }

function initFlatpickr(element) {

    if (!element) return;
    if (!$(element).hasClass('startPicker')) return;

    if (element._flatpickr) {
        element._flatpickr.destroy();
    }

    flatpickr(element, {
        mode: "multiple",
        dateFormat: "Y-m-d",

        altInput: true,
        altFormat: "Y-m-d",
        altInputClass: "form-control",

        onChange: function(selectedDates, dateStr, instance) {

            let row = $(instance.element).closest('tr');
            let recurrenceDropdown = row.find('.scheduleType');
            let multiInput = row.find('.multiPicker');
            let endInput = row.find('.endPicker');

            if (selectedDates.length === 1) {
   //  multiDateTd.hide(); 
                let selectedDate = selectedDates[0];

                let dayName = selectedDate.toLocaleDateString('en-US', { weekday: 'long' });
                let dayOfMonth = selectedDate.getDate();

                // Week number
                let weekNumber = Math.ceil(dayOfMonth / 7);

                // Check next same weekday
                let nextSameWeekday = new Date(selectedDate);
                nextSameWeekday.setDate(nextSameWeekday.getDate() + 7);

                // Only if next week goes to next month AND weekNumber is 5
                let isLastWeekday = (weekNumber === 5 && nextSameWeekday.getMonth() !== selectedDate.getMonth());

                function getOrdinal(n) {
                    if (n === 1) return "st";
                    if (n === 2) return "nd";
                    if (n === 3) return "rd";
                    return "th";
                }

                let optionsHtml = `
                    <option value="">Select Type</option>
                    <option value="single">Does Not Repeat</option>
                    <option value="weekly">Weekly on ${dayName}</option>
                `;

                if (isLastWeekday) {
                    optionsHtml += `
                        <option value="monthly">Last ${dayName} of the month</option>
                    `;
                } else {
                    optionsHtml += `
                        <option value="monthly">Monthly ${weekNumber}${getOrdinal(weekNumber)} ${dayName}</option>
                    `;
                }

                recurrenceDropdown.html(optionsHtml);
                recurrenceDropdown.val('single').trigger('change');

                multiInput.val('');
                endInput.val('');
            }

            else if (selectedDates.length > 1) {


                recurrenceDropdown.html(`
                    <option value="multiple">Does Not Repeat</option>
                `).val('multiple').trigger('change');

                multiInput.val(dateStr);

                instance.element.value = '';
                instance.altInput.value = "Multiple";
            }
        }
    });
}

$(document).on('change', '.scheduleType', function() {

    let type = $(this).val();
    let row = $(this).closest('tr');

    const $startInput = row.find('.startPicker');
    const $endInput   = row.find('.endPicker');
    const $multiInput = row.find('.multiPicker');
    const $multiTd    = $multiInput.closest('td');

    // Reset everything first
    $startInput.prop('disabled', false);
    $endInput.prop('disabled', false);
    $multiTd.hide();

    if (type === 'single' || type === '') {

        // Does Not Repeat
        $endInput.prop('disabled', true);
        $endInput.val('');

    } 
    else if (type === 'multiple') {

        // Multiple Dates
        $multiTd.show();
        $endInput.prop('disabled', true);
        $endInput.val('');

    } 
    else {

        // Weekly / Monthly / Monthly Last
        $endInput.prop('disabled', false);

        if ($startInput.val()) {
            $endInput.attr('min', $startInput.val());
        }
    }

});

// $(document).on('change', '.scheduleType', function() {

//     let type = $(this).val();
//     let row = $(this).closest('tr');

//     if(type === 'multiple'){

//         row.find('.multi-date').show();
//         row.find('.startPicker').closest('td').hide();
//         row.find('.endPicker').closest('td').hide();

//         let input = row.find('.multiPicker')[0];
//         initFlatpickr('multiple', input);
//     }
//     else if(type === 'weekly' || type === 'monthly'){

//         row.find('.multi-date').hide();
//         row.find('.startPicker').closest('td').show();
//         row.find('.endPicker').closest('td').show();
//     }
//     else {

//         row.find('.multi-date').hide();
//         row.find('.startPicker').closest('td').show();
//         row.find('.endPicker').closest('td').hide();
//     }
// });


</script>

</x-app-layout>