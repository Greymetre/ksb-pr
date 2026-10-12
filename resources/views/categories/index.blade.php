<x-app-layout>
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header card-header-icon card-header-theme">
        <div class="card-icon">
          <i class="material-icons">perm_identity</i>
        </div>
        <h4 class="card-title ">{!! trans('panel.category.title_singular') !!}{!! trans('panel.global.list') !!}
              <span class="">
                <div class="btn-group header-frm-btn">
                    <div class="next-btn">
                  @if(auth()->user()->can(['category_download']))
                  <a href="{{ URL::to('categories-download') }}" class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.download') !!} {!! trans('panel.category.title') !!}"><i class="material-icons">cloud_download</i></a>
                  @endif
                  {{-- Categories come from Odoo (Odoo Sync -> Category Master Odoo): no add / upload / template --}}
                </div>
                </div>
              </span>
          </h4>
      </div>
      <div class="card-body">
        @include('odoo_sync.partials.assets')
        {{-- Odoo sync: categories come from Odoo twice a day (06:00 and 18:00), or Sync now --}}
        <div class="os-page" style="padding-bottom:0">
          <div class="os-head">
            <p class="os-subtitle">Categories come from Odoo twice a day (06:00 and 18:00). Only Column Coupler, HDPE Pipe and PVC Pipes are synced for now.</p>
            <button type="button" class="os-btn" id="odooSyncCategories"><span class="material-icons">sync</span><span class="os-btn-label">Sync now</span></button>
          </div>
          <div class="os-stats is-3">
            <div class="os-stat">
              <div class="os-stat-label"><span class="material-icons">schedule</span>Last sync</div>
              <div class="os-stat-value is-small">{{ $odooLastRun ? \Carbon\Carbon::parse($odooLastRun->created_at)->diffForHumans() : 'Not synced yet' }}</div>
              <div class="os-stat-note {{ $odooLastRun && $odooLastRun->status_code >= 300 ? 'is-danger' : '' }}">
                @if($odooLastRun)
                  {{ \Carbon\Carbon::parse($odooLastRun->created_at)->format('d M Y, h:i A') }} · {{ $odooLastRun->status_code === 502 ? 'Odoo call failed, see Odoo Sync › Sync Overview' : number_format($odooLastRun->received_count) . ' received' }}
                @else
                  Click Sync now or wait for the cron
                @endif
              </div>
            </div>
            <div class="os-stat">
              <div class="os-stat-label"><span class="material-icons">category</span>Categories</div>
              <div class="os-stat-value">{{ number_format($odooCounts['total']) }}</div>
              <div class="os-stat-note">{{ number_format($odooCounts['active']) }} active</div>
            </div>
            <div class="os-stat">
              <div class="os-stat-label"><span class="material-icons">cloud_sync</span>In Odoo</div>
              <div class="os-stat-value">{{ $odooLastRun && $odooLastRun->status_code !== 502 ? number_format($odooLastRun->received_count) : '—' }}</div>
              <div class="os-stat-note">Odoo categories; {{ number_format($odooCounts['total']) }} synced here</div>
            </div>
          </div>
        </div>
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
        <div class="table-responsive">
          <table id="getcategory" class="table table-striped- table-bordered table-hover table-checkable responsive no-wrap">
            <thead class=" text-primary">
              <th>{!! trans('panel.global.no') !!}</th>
              <th>{!! trans('panel.global.action') !!}</th>
              <th>{!! trans('panel.global.active') !!}</th>
              <th>{!! trans('panel.category.fields.category_image') !!}</th>
              <th>{!! trans('panel.category.fields.category_name') !!}</th>
              <th>Odoo Category ID</th>
              <th>Odoo Updated</th>
              <th>Last Synced</th>
              <th>{!! trans('panel.global.created_by') !!}</th>
              <th>{!! trans('panel.global.created_at') !!}</th>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="createCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content card">
      <div class="card-header card-header-icon card-header-theme">
        <div class="card-icon">
          <i class="material-icons">perm_identity</i>
        </div>
        <h4 class="card-title"><span class="modal-title">{!! trans('panel.global.add') !!}</span> {!! trans('panel.category.title_singular') !!}
          <span class="pull-right" >
            <a href="javascript:void(0)" class="btn btn-just-icon btn-danger" data-dismiss="modal"><i class="material-icons">clear</i></a>
          </span>
        </h4>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data" id="createCategoryForm">
        @csrf
        <div class="row">
            <div class="col-md-9">
                <div class="input_section">
                  <label class="col-form-label">{!! trans('panel.category.fields.category_name') !!} <span class="text-danger"> *</span></label>
                  
                  <div class="form-group has-default bmd-form-group">
                    {{-- Name comes from Odoo; the next sync would overwrite an edit --}}
                    <input type="text" name="category_name" id="category_name" class="form-control" value="{!! old( 'category_name') !!}" maxlength="200" required readonly>
                    @if ($errors->has('category_name'))
                    <div class="error"><p class="text-danger">{{ $errors->first('category_name') }}</p></div>
                    @endif
                  </div>
                  <label class="col-form-label">Odoo Category ID</label>
                    <div class="form-group has-default bmd-form-group">
                      {{-- Set by the Odoo sync; shown only, never posted --}}
                      <input type="text" id="odoo_category_id" class="form-control" readonly>
                  </div>
                </div>
              </div>
            <div class="col-md-3 col-sm-3">
              <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                 
                   <div class="fileinput-new thumbnail">
                     <img src="" id="category_image" class="imagepreview1">
                       <div class="selectThumbnail">
                     <span class="btn btn-just-icon btn-round btn-file">
                       <span class="fileinput-new"><i class="fa fa-pencil"></i></span>
                       <span class="fileinput-exists">Change</span>
                       <input type="file" name="image" class="getimage1"  accept="image/*">
                     </span>
                     <br>
                     <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                   </div>
                   </div>
                   <div class="fileinput-preview fileinput-exists thumbnail img-circle"></div>
                   <label class="bmd-label-floating">{!! trans('panel.category.fields.category_image') !!}</label>
                 </div>
            </div>
          </div>
        <div class="clearfix"></div>
        <div class="pull-right">
          <input type="hidden" name="id" id="category_id" />
          <button class="btn btn-info save"> Submit</button>
        </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="{{ url('/').'/'.asset('assets/js/jquery.custom.js') }}"></script>
<script src="{{ url('/').'/'.asset('assets/js/validation_products.js') }}"></script>
<script type="text/javascript">
  $(function () {
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
    });
    var table = $('#getcategory').DataTable({
        processing: true,
        serverSide: true,
        "order": [ [0, 'desc'] ],
        ajax: "{{ route('categories.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            {data: 'action', name: 'action',"defaultContent": '',orderable: false, searchable: false},
             {data: 'active', name: 'active',"defaultContent": '', orderable: false, searchable: false},
            {data: 'image', name: 'image',"defaultContent": '', orderable: false, searchable: false},
            {data: 'category_name', name: 'categories.category_name',"defaultContent": ''},
            {data: 'odoo_category_id', name: 'categories.odoo_category_id',"defaultContent": ''},
            {data: 'odoo_updated_at', name: 'oc.odoo_updated_at',"defaultContent": '', searchable: false},
            {data: 'odoo_synced_at', name: 'oc.updated_at',"defaultContent": '', searchable: false},
            {data: 'createdbyname.name', name: 'createdbyname.name',"defaultContent": ''},
            {data: 'created_at', name: 'categories.created_at',"defaultContent": ''},
        ]
    });
         
    $(document).on('click', '.edit', function(){
      var base_url =$('.baseurl').data('baseurl');
      var id = $(this).attr('id');
      $.ajax({
        url: base_url + '/categories/'+id,
       dataType:"json",
       success:function(data)
       {
        $('#category_name').val(data.category_name);
        $('#odoo_category_id').val(data.odoo_category_id);
        if(data.category_image)
        {
          var image = data.category_image ;
        }
        else
        {
          var image = "{!! asset('assets/img/placeholder.jpg') !!}" ;
        }
        $("#category_image").attr({ "src": image });
        $('#category_id').val(data.id);
        var title = '{!! trans('panel.global.edit') !!}' ;
        $('.modal-title').text(title);
        $('#action_button').val('Edit');
        $('#createCategory').modal('show');
       }
      })
     });
    
    // Pull categories from Odoo now (cron also runs twice a day)
    $('#odooSyncCategories').on('click', function () {
        var $btn = $(this).prop('disabled', true).addClass('is-busy');
        $btn.find('.os-btn-label').text('Syncing…');
        $.ajax({
            url: "{{ route('categories.odoo_sync') }}",
            type: 'POST',
            data: {_token: $("meta[name='csrf-token']").attr("content")},
            success: function (r) {
              window.osToast('Synced: ' + r.created + ' created, ' + r.updated + ' updated, ' + r.skipped + ' unchanged, ' + r.failed + ' failed');
              setTimeout(function() { location.reload(); }, 1400);
            },
            error: function () {
              window.osToast('Sync failed. See Odoo Sync › Sync Overview for the error.');
              $btn.prop('disabled', false).removeClass('is-busy').find('.os-btn-label').text('Sync now');
            }
        });
    });

    $('body').on('click', '.categoryActive', function () {
        var id = $(this).attr("id");
        var active = $(this).attr("value");
        var status = '';
        if(active == 'Y')
        {
          status = 'Incative ?';
        }
        else
        {
           status = 'Ative ?';
        }
        var token = $("meta[name='csrf-token']").attr("content");
        if(!confirm("Are You sure want "+status)) {
           return false;
        }
        $.ajax({
            url: "{{ url('categories-active') }}",
            type: 'POST',
            data: {_token: token,id: id,active:active},
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
    $('.create').click(function () {
        $('#category_id').val('');
        $('#createCategoryForm').trigger("reset");
        $("#category_image").attr({ "src": '{!! asset('assets/img/placeholder.jpg') !!}' });
        $('.modal-title').text('{!! trans('panel.global.add') !!}');
    });
    
    $('body').on('click', '.delete', function () {
        var id = $(this).attr("value");
        var token = $("meta[name='csrf-token']").attr("content");
        if(!confirm("Are You sure want to delete ?")) {
           return false;
        }
        $.ajax({
            url: "{{ url('categories') }}"+'/'+id,
            type: 'DELETE',
            data: {_token: token,id: id},
            success: function (data) {
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
     
    });
</script>

</x-app-layout>
