<!-- <style>
  .table>tbody>tr>td{
    white-space: unset;
  }
</style> -->

<x-app-layout>
<div class="row">
   <div class="col-md-12">
      <div class="card">
          {!! Form::model($process,[
            'route' => $process->exists ? ['customer_process.update', $process->id] : 'customer_process.store',
            'method' => $process->exists ? 'PUT' : 'POST',
            'id' => 'createFieldsForm',
            'files'=>true
            ]) !!}
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title ">{!! trans('panel.global.add') !!} Process
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
            <div class="alert " style="display: none;">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <i class="material-icons">close</i>
              </button>
              <span class="message"></span>
            </div>
            <input type="hidden" name="id" value="{!! $process['id'] !!}">
            <div class="row">
              <div class="col-md-8">
                <div class="input_section">
                  <label class="col-form-label" for="process_name">Process Name <span class="text-danger"> *</span></label>
                 
                    <div class="form-group has-default bmd-form-group">
                      <input type="text" name="process_name" id="process_name" class="form-control" value="{!! old( 'process_name', $process['process_name']) !!}" maxlength="200" required>
                      @if ($errors->has('process_name'))
                        <div class="error"><p class="text-danger">{{ $errors->first('process_name') }}</p></div>
                      @endif
                    </div>              
                </div>
              </div>
              {{--<div class="col-md-6">
                <div class="input_section">
                  <label class="col-form-label">Lable Name <span class="text-danger"> *</span></label>
                 
                    <div class="form-group has-default bmd-form-group">
                      <input type="text" name="label_name" id="label_name" class="form-control" value="{!! old( 'label_name', $fields['label_name']) !!}" maxlength="200" required>
                      @if ($errors->has('label_name'))
                        <div class="error col-lg-12"><p class="text-danger">{{ $errors->first('label_name') }}</p></div>
                      @endif
                    </div>
                               </div>
              </div>
              <div class="col-md-6">
                <div class="input_section">
                  <label class="col-form-label">Place Holder </label>
               
                    <div class="form-group has-default bmd-form-group">
                      <input type="text" name="placeholder" id="placeholder" class="form-control" value="{!! old( 'placeholder', $fields['placeholder']) !!} " maxlength="200">
                      @if ($errors->has('placeholder'))
                        <div class="error col-lg-12"><p class="text-danger">{{ $errors->first('placeholder') }}</p></div>
                      @endif
                    </div>
          
                </div>
              </div>
              <div class="col-md-6">
                <div class="input_section">
                  <label class="col-form-label">Field Type <span class="text-danger"> *</span></label>
            
                    <div class="form-group has-default bmd-form-group">
                      <select class="form-control select2" name="field_type" id="field_type" style="width: 100%;" required>
                        <option value="" selected disabled>Select Field Type</option>
                        <option value="Input" {{ old( 'field_type' , (!empty($fields->field_type)) ? ($fields->field_type) :('') ) == 'Input' ? 'selected' : '' }}>Input</option>
                        <option value="Select" {{ old( 'field_type' , (!empty($fields->field_type)) ? ($fields->field_type) :('') ) == 'Select' ? 'selected' : '' }}>Select</option>
                        <!-- <option value="Radio" {{ old( 'field_type' , (!empty($fields->field_type)) ? ($fields->field_type) :('') ) == 'Radio' ? 'selected' : '' }}>Radio</option> -->
                        <!-- <option value="Checkbox" {{ old( 'field_type' , (!empty($fields->field_type)) ? ($fields->field_type) :('') ) == 'Checkbox' ? 'selected' : '' }}>Checkbox</option> -->
                        <!-- <option value="File" {{ old( 'field_type' , (!empty($fields->field_type)) ? ($fields->field_type) :('') ) == 'File' ? 'selected' : '' }}>File</option> -->
                        <!-- <option value="RangeSlider" {{ old( 'field_type' , (!empty($fields->field_type)) ? ($fields->field_type) :('') ) == 'RangeSlider' ? 'selected' : '' }}>Range Slider</option> -->
                     </select>
                      @if ($errors->has('field_type'))
                        <div class="error col-lg-12"><p class="text-danger">{{ $errors->first('field_type') }}</p></div>
                      @endif
                    </div>
                  </div>
             
              </div>
              <div class="col-md-6">
                <div class="input_section">
                  <label class="col-form-label">Module Type <span class="text-danger"> *</span></label>
                
                    <div class="form-group has-default bmd-form-group">
                      <select class="form-control select2" name="module" id="module" style="width: 100%;" required>
                        <option value="" selected disabled>Select Module Type</option>
        				@if(@isset($customertype ))
                        @foreach($customertype as $type)
                        <option value="{!! $type['id'] !!}" {{ old( 'customertype' , (!empty($fields->module))?($fields->module):('') ) == $type['id'] ? 'selected' : '' }}>{!! $type['customertype_name'] !!}</option>
                        @endforeach
                        @endif
                     </select>
                      @if ($errors->has('module'))
                        <div class="error col-lg-12"><p class="text-danger">{{ $errors->first('module') }}</p></div>
                      @endif
                    </div>
               
                </div>
              </div>

            <!-- nn -->

              <div class="col-md-6">
                <div class="input_section">
                  <label class=" col-form-label">Division <span class="text-danger"> *</span></label>
               
                    <div class="form-group has-default bmd-form-group">
                      <select class="form-control select2" name="division_id" id="division_id" style="width: 100%;" required>
                        <option value="" selected disabled>Select Division</option>
                      @if(@isset($divisions ))
                        @foreach($divisions as $division)
                        <option value="{!! $division['id'] !!}" {{ old( 'divisions' , (!empty($fields->division_id))?($fields->division_id):('') ) == $division['id'] ? 'selected' : '' }}>{!! $division['division_name'] !!}</option>
                        @endforeach
                        @endif
                     </select>
                      @if ($errors->has('division_id'))
                        <div class="error col-lg-12"><p class="text-danger">{{ $errors->first('division_id') }}</p></div>
                      @endif
                    </div>
                  </div>
             
              </div>

              <!-- nn -->





              <div class="col-md-6">
                <div class="input_section">
                  <label class="col-form-label">Mandatory</label>
                
                    <label class="col-form-label">
                      <div class="checkbox">
                        <input type="checkbox" name="is_required" {{ old( 'is_required' , (!empty($fields->is_required)) ? ($fields->is_required) :('') ) == true ? 'checked' : '' }}><span class="checkbox-material"><span class="check"></span></span>
                          Yes
                        </div>
                      </label>
                    </div>
                
                </div>
                    <div class="col-md-6 multipleshow">
                <div class="input_section">
                  <label class="col-form-label">Is Multiple</label>
                 
                  <label class="col-form-label">
                    <div class="checkbox">
                      <input type="checkbox" name="is_multiple" {{ old( 'is_multiple' , (!empty($fields->is_multiple)) ? ($fields->is_multiple) :('') ) == true ? 'checked' : '' }}><span class="checkbox-material"><span class="check"></span></span>
                      Yes
                      </div>
                      </label>
                  
                  </div>
              </div> --}}
            </div>
        
            <!-- Table row -->
               <div class="row fielddata" @if($process->exists && $process['steps']->count() >= 1 ) '' @else style="display: none;" @endif>
                  <div class="container-fluid mt-5 d-flex justify-content-center w-100">
                     <div class="table-responsive w-100">
                        <table class="table kvcodes-dynamic-rows-example" id="tab_logic">
                           <thead>
                              <tr class="text-white">
                                 <th class="text-center"> # </th>
                                 <th class="text-center"> Step Name </th>
                                 <th class="text-center"> Ordering </th>
                                 <th class="text-center"> </th>
                              </tr>
                           </thead>
                           <tbody >
                              @if($process->exists && isset($process['steps']))
                              @foreach($process['steps'] as $index => $rows )
                              <tr id='addr0'>
                                <td class="detail_index">{!! $index +1 !!}</td>
                                 <td>
                                  <div class="input_section">
                                    <input type="text" name="steps[{!! $index !!}][value]" class="form-control value rowchange" value="{!! $rows['value'] !!}" required />
                                    <div class='error-value'></div>
                                  </div>
                                 </td>
                                 <td>
                                  <div class="input_section">
                                    <input type="number" name="steps[{!! $index !!}][sort_order]" class="form-control value rowchange" value="{!! $rows['sort_order'] !!}" required />
                                    <div class='error-value'></div>
                                  </div>
                                 </td>
                                 <td class="td-actions text-right">
                                  <a href="javascript:void(0)" class="btn btn-danger remove-rows" value="{!! $rows['id'] !!}" data-original-title="" title="">
                                    <i class="material-icons">close</i>
                                  </a>
                                </td>
                              </tr>
                              <tr id='addr1'></tr>
                              @endforeach
                              @else
                              <tr id='addr0'>
                                 <td class="detail_index">1</td>
                                 <td>
                                    <input type="text" name="steps[1][value]" class="form-control value rowchange" required />
                                    <div class='error-value'></div>
                                 </td>
                                 <td>
                                    <input type="number" name="steps[1][sort_order]" class="form-control value rowchange" required />
                                    <div class='error-value'></div>
                                 </td>
                                 <td class="td-actions text-right">
                                  <a class="btn btn-danger remove-rows" data-original-title="" title="">
                                    <i class="material-icons">close</i>
                                  </a>
                                </td>
                              </tr>
                              <tr id='addr1'></tr>
                              @endif
                           </tbody>
                        </table>
                     </div>
                    
                  </div>
               </div>
               
                 <div class="row clearfix fielddata multipleshow">
                  <table class="table">
                    <tr>
                    <td class="td-actions text-left">
                      <a type="button" class="btn btn-info btn-xs add-rows" title="Add More" style="border-radius: 30px;color: #fff !important;font-weight: 600 !important;padding: 5px 15px !important;">
                       Add Step <i class="material-icons" style="font-size: 25px;font-weight: 900;margin: 3px;">add</i>
                      </a>
                    </td>
                  </tr>
                  </table>
              </div>
            </div>
            <div class="card-footer pull-right">
               {{ Form::submit('Submit', array('class' => 'btn btn-theme pull-right')) }}
            </div>
            {{ Form::close() }} 
         </div>
      </div>
   </div>
</div>
<script src="{{ asset('assets/js/jquery.custom.js') }}"></script>

<script type="text/javascript">
  $(document).ready(function(){
    $('.multipleshow').hide();
    $('.fielddata').show();
    // var field_type = $('select[name="field_type"]').val() ;
    // if(field_type == 'Select' || field_type == 'Radio'|| field_type == 'Checkbox')
    // {
    //   $('.multipleshow').show();
    // }
    // else{
    //   $('.multipleshow').hide();
    // }
  
    var $table = $('table.kvcodes-dynamic-rows-example'),
    counter = $("#tab_logic").find('.detail_index').last().html() || 0;
      $('a.add-rows').click(function(event){
        event.preventDefault();
          counter++;
        var newRow = '<tr class="item-row"> <td class="detail_index">'+counter+'</td>'+
          '<td><input type="text" name="steps[' + counter + '][value]" class="form-control" required></td>' +
          '<td><input type="number" name="steps[' + counter + '][sort_order]" class="form-control" required></td>' +
          '<td class="td-actions text-right"><a class="remove-rows btn btn-danger" title="Remove row"><i class="material-icons">close</i></a></td> </tr>';
          $table.append(newRow);
      });
   
  $table.on('click', '.remove-rows', function() {
      $(this).closest('tr').remove();
  });
});

$("#field_type").on('change', function(){
  var field_type = $('select[name="field_type"]').val() ;
  if(field_type == 'Select')
  {
    $('.multipleshow').show();
  }
  else{
    $('.multipleshow').hide();
  }
   
  if( field_type == 'Select' || field_type == 'Radio' || field_type == 'Checkbox')
   {
    $('.fielddata').show();
   }
   else
   {
    $('.fielddata').hide();
   }
 })

$('body').on('click', '.delete', function () {
        var id = $(this).attr("value");
        var token = $("meta[name='csrf-token']").attr("content");
        if(!confirm("Are You sure want to delete ?")) {
           return false;
        }
        $.ajax({
            url: "{{ url('fieldData') }}"+'/'+id,
            type: 'GET',
            data: {_token: token,id: id},
            success: function (data) {
              $('.message').empty();
              $('.alert').show();
              if(data.status == 'success')
              {
                $('.alert').addClass("alert-success");
              }
              else
              {
                $('.alert').addClass("alert-danger");
              }
              $('.message').append(data.message);
              table.draw();
            },
        });
    });
</script>
</x-app-layout>