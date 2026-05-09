<!-- <style>
  .table>tbody>tr>td{
    white-space: unset;
  }
</style> -->

<x-app-layout>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        {!! Form::model($active_process,[
        'route' => $active_process->exists ? ['active_customer_process.update', $active_process->id] : 'active_customer_process.store',
        'method' => $active_process->exists ? 'PUT' : 'POST',
        'id' => 'assignProcessForm',
        'files'=>true
        ]) !!}
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title ">Assign Process
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
          <input type="hidden" name="id" value="{!! $active_process['id'] !!}">
          <div class="row">
            <div class="col-md-6">
              <div class="input_section">
                <label class="col-form-label" for="customer_id">Customer <span class="text-danger"> *</span></label>

                <div class="form-group has-default bmd-form-group">
                  <select name="customer_id" class="select2 form-control" id="customer_id" required>
                    <option value="">Select Customer</option>
                    @if(@isset($customers ))
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" @if($customer->id == $active_process->customer_id) selected @endif>{{ $customer->name }}</option>
                    @endforeach
                    @endif
                  </select>
                  @if ($errors->has('customer_id'))
                  <div class="error">
                    <p class="text-danger">{{ $errors->first('customer_id') }}</p>
                  </div>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <!-- Table row -->
          <div class="row fielddata" @if($active_process->exists && $active_process['values']->count() >= 1 ) '' @else style="display: none;" @endif>
            <div class="container-fluid mt-2 d-flex w-100">
              <div class="table-responsive w-50">
                <table class="table kvcodes-dynamic-rows-example" id="tab_logic">
                  <thead class="bg-secondary">
                    <tr class="text-white">
                      <th class="text-center"> # </th>
                      <th class="text-center"> Process </th>
                      <th class="text-right" style="text-align: right !important;"> Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($active_process->exists && isset($active_process['values']))
                    @foreach($active_process['values'] as $index => $rows )
                    <tr id='addr0'>
                      <td class="detail_index">{!! $index +1 !!}</td>
                      <td>
                        <div class="input_section">
                          <input type="text" name="details[{!! $index !!}][value]" class="form-control value rowchange" value="{!! $rows['value'] !!}" />
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
                      <td class="detail_index" style="width: 6%;">1</td>
                      <td>
                        <select name="process_id[]" id="" class="form-control select2 all_process" required>
                          <option value="">Select Process</option>
                          @if(@isset($processes ))
                          @foreach($processes as $process)
                          <option value="{!! $process['id'] !!}">{!! $process['process_name'] !!}</option>
                          @endforeach
                          @endif
                        </select>
                        <div class='error-value'></div>
                      </td>
                      <td class="td-actions text-right">
                        <!-- <a class="btn btn-danger remove" data-original-title="" title="">
                          <i class="material-icons">close</i>
                        </a> -->
                      </td>
                    </tr>
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
                  <a type="button" class="btn btn-info btn-xs add-rows" style="color: #fff !important;padding: 10px 20px !important;border-radius: 12px !important;">
                    Add process<i class="material-icons">add</i>
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
    $(document).ready(function() {
      $('.fielddata').show();
      var $table = $('table.kvcodes-dynamic-rows-example'),
        counter = $("#tab_logic").find('.detail_index').last().html() || 0;
      $('a.add-rows').click(function(event) {
        event.preventDefault();
        counter++;

        // Build options
        let processOptions = `{!! collect($processes ?? [])->map(function($p) {
                      return '<option value="' . $p->id . '">' . e($p->process_name) . '</option>';
                      })->implode('') !!}`;

        // Create new row as jQuery object and hide initially
        var newRow = $(`
        <tr class="item-row" style="display: none;">
          <td class="detail_index text-dark" style="width: 6%;">${counter}</td>
          <td>
            <select name="process_id[]" class="form-control select2 all_process" required>
              <option value="">Select Process</option>
              ${processOptions}
            </select>
          </td>
          <td class="td-actions text-right">
            <a class="remove-rows btn btn-danger" title="Remove row"><i class="material-icons">close</i></a>
          </td>
        </tr>
    `);

        // Append hidden row
        $table.find('tbody').append(newRow);

        // Animate the row appearing
        newRow.fadeIn('slow'); // Adjust duration as needed

        // Initialize Select2
        newRow.find('.select2').select2();

        updateProcessOptions();
      });


      $table.on('click', '.remove-rows', function() {
        $(this).closest('tr').remove();
        updateProcessOptions();
      });
    });

    $(document).on('change', '.all_process', function() {
      updateProcessOptions();
    })

    function updateProcessOptions() {
      let selectedValues = $('select[name="process_id[]"]').map(function() {
        return $(this).val();
      }).get();
      $('select[name="process_id[]"]').each(function() {
        let $select = $(this);
        $select.find('option').each(function() {
          let $option = $(this);
          if ($option.val() !== "" && selectedValues.includes($option.val()) && $option.val() !== $select.val()) {
            $option.prop('disabled', true);
          } else {
            $option.prop('disabled', false);
          }
        });
      });
      // Refresh select2 to apply disabled options
      $('.select2').select2();
    }
  </script>
</x-app-layout>