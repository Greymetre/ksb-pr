<x-app-layout>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title ">Lead Contacts <span class="brig">20 Contacts</span>
            <span class="">
              <div class="pream_entry">

                 <div class="search">
                   <div class="search_inner">
                     <button type="button"> <img src="https://expertfromindia.in/bediya/public/assets/img/search.svg"></button>
                     <input type="search" class="searchbox" placeholder="Search Lead">
                   </div>
                </div>

                <div class="both_btn">

                <div class="well mb-3 float-right" id="checkbox_option" style="display: none;" >
                {!! Form::open(['method' => 'POST','route' => ['lead-contacts.checkboxAction'], 'class' => 'form-inline', 'id' => 'frmAction']) !!}
                <div class="form-group mr-sm-2 mb-2">
                        <input type="hidden" name="lead_ids"  id="lead_ids">                
                </div>   
                  <button type="submit" onclick="return confirm('Are you sure delete?')" class="btn btn-responsive btn-primary mr-sm-2 mb-2">Delete</button>
                {!! Form::close() !!}
              </div>
              <button type="button" data-toggle="modal" data-target="#addLeadModel" class="btn btn-primary btn-sm btn-icon-split float-right">
                    <span class="icon text-white-50">
                      <i class="material-icons">add_circle</i>
                    </span>
                    <span class="text">Add Contacts</span>
                </button>
               <!--  <a  href="{{route('contacts-exportContacts')}}" class="btn exportbtn btn-primary btn-sm btn-icon-split float-right" id="export_button">
                    <span class="icon text-white-50">
                      <i class="material-icons">cloud_download</i>
                    </span>
                    <span class="text">Export</span>
                </a> -->
                 <a  href="{{route('contacts-exportContacts')}}" class="btn exportbtn btn-primary btn-sm btn-icon-split float-right" id="export_button">
                    <span class="icon text-white-50">
                      <i class="material-icons">cloud_download</i>
                    </span>
                    <span class="text">Export</span>
                </a>
                @if(auth()->user()->can(['lead_contacts_template']))
                <a href="{{ URL::to('lead-contacts-template') }}" class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.template') !!} Lead Contacts"><i class="material-icons">text_snippet</i></a>
                @endif
                @if(auth()->user()->can(['lead_contacts_upload']))
                <form action="{{ URL::to('lead-contacts-upload') }}" class="form-horizontal" method="post" enctype="multipart/form-data">
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
                      <button class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.upload') !!} Lead Contacts">
                        <i class="material-icons">cloud_upload</i>
                        <div class="ripple-container"></div>
                      </button>
                    </div>
                  </div>
                </form>
                @endif
                
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

          <!--new div--->

            <div class="well">
            <div class="dd">
            <div class="sort_btn">
              <div class="btn-group">
                <button class="btn sort_btns  dropdown-toggle" 
                        type="button" 
                        id="dropdownMenuButton" 
                        data-toggle="dropdown" 
                        aria-haspopup="true" 
                        aria-expanded="false">
                 <img src="https://expertfromindia.in/bediya/public/assets/img/sort.png">  Sort
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a class="dropdown-item" href="#"> Range</a>
                  <a class="dropdown-item" href="#"> limit</a>
                 
                </div>
              </div>
            </div>
           
          </div>

            <div class="sort_btn">
              <div class="btn-group">
                <button class="btn sort_btns filter_btn  dropdown-toggle" 
                        type="button" 
                        id="dropdownMenuButton" 
                        data-toggle="dropdown" 
                        aria-haspopup="true" 
                        aria-expanded="false">
                 <img src="https://expertfromindia.in/bediya/public/assets/img/filter_ss.png">  Filter
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a class="dropdown-item" href="#"> id</a>
                  <a class="dropdown-item" href="#">range </a>
                  <a class="dropdown-item" href="#">class </a>
                </div>
              </div>
            </div>
            </div>

          <!--end div--->

          <div class="alert " style="display: none;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <i class="material-icons">close</i>
            </button>
            <span class="message"></span>
          </div>
          <div class="table-responsive">
            <table id="getLeadContacts" class="table ">
              <thead class=" text-primary">
                <tr>
                  <th></th>
                  <th>Name</th>
                  <th>Title</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Lead</th>
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
  <!--  -->
  <div class="modal fade" id="addLeadModel" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">New Lead</h4>
        </div>
        
          <form method="POST" 
          action="{{ route('leads.store') }}" class="form-horizontal" id="frmLeadsCreate" enctype="multipart/form-data">
            @csrf
          <div class="modal-body">
          <div class="row">
            <div class="col-md-6 pr-1 pl-1">
              <div class="col-md-12 form-group">
                  <label for="company_name">Company Name <span style="color:red">*</span></label>
                  <input type="text"  name="company_name" id="company_name" value="{{ old('company_name','') }}"  class="form-control" placeholder="Company Name">
                  @if($errors->has('company_name'))
                  <p class="help-block">
                      <strong>{{ $errors->first('company_name') }}</strong>
                  </p>
                  @endif
              </div>
            </div>
            <div class="col-md-6 pr-1 pl-1">
              <div class="col-md-12 form-group">
                  <label for="contact_name">Contact Name <span style="color:red">*</span></label>
                  <input type="text"  name="contact_name" id="contact_name" value="{{ old('contact_name','') }}"  class="form-control" placeholder="Contact Name">
                  @if($errors->has('contact_name'))
                  <p class="help-block">
                      <strong>{{ $errors->first('contact_name') }}</strong>
                  </p>
                  @endif
              </div>     
            </div>
            <div class="col-md-12" id="lead_exist_data">
            </div>
          </div>
        
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger mr-2" data-dismiss="modal">Canel</button>
          <button type="submit" class="btn btn-default">Create Lead</button>
        </div>
      </div>
      </form>
    </div>
  </div>
  <!--  -->
  <script src="{{ url('/').'/'.asset('assets/js/jquery.custom.js') }}"></script>
  <!-- Load jQuery and moment.js -->
  <script src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<script>


jQuery(document).ready(function(){
    getLeadContacts();

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
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$('#company_name, #contact_name').on('keyup', function () {
    var company_name = $('#company_name').val();
    var contact_name = $('#contact_name').val();
  $.post("{{route('leads.searchExistsLead')}}", {company_name:company_name,contact_name:contact_name}, function(response){
      $('#lead_exist_data').html(response);
  });
});
 


function getLeadContacts(){
    jQuery('#getLeadContacts').dataTable().fnDestroy();
    jQuery('#getLeadContacts tbody').empty();
    jQuery('#getLeadContacts').DataTable({
        processing: false,
        serverSide: true,
        searching: false,
        ajax: {
            url: "{{ route('lead-contacts.getLeadContacts') }}",
            method: 'POST'
        },
        columns: [
            {data: 'checkbox', name: 'checkbox'},
            {data: 'name', name: 'name'},
            {data: 'title', name: 'title'},
            {data: 'phone_number', name: 'phone_number'},
            {data: 'email', name: 'email'},
            {data: 'lead.company_name', name: 'lead.company_name'}
        ],
        order: [[0, 'desc']],
        dom: 't<"bottom"lip>',
    });
}

function checkboxDelete(lead_id){
   var checkboxes = document.querySelectorAll('.checkbox_cls');
   var checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);
   if(checkedOne == true){
    jQuery("#checkbox_option").css("display", "block");
   }else{
    jQuery("#checkbox_option").css("display", "none");
   }
    
   
    var lead_ids = jQuery('#lead_ids').val();
    if( lead_ids.split(',').indexOf(lead_id)> -1) {
    var lead_idssArray = lead_ids.split(',');
    for (var i = 0; i < lead_idssArray.length; i++) {
                if (lead_idssArray[i] === lead_id) {
                    lead_idssArray.splice(i, 1);
                }
    }
    jQuery('#lead_ids').val(lead_idssArray);
   
    }else{

      if(lead_ids ==""){
           var res = lead_ids.concat(lead_id);
      }else{
           var res = lead_ids.concat(","+lead_id);
      }
       
      jQuery('#lead_ids').val(res);
    }

 
}

</script>

</x-app-layout>