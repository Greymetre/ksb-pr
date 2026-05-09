<x-app-layout>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title ">Process {!! trans('panel.global.list') !!}
            <span class="">
              <div class="btn-group header-frm-btn">
                <div class="next-btn">
                  @if(auth()->user()->can('process_create'))
                  <a href="{{ route('customer_process.create') }}" class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.add') !!} Process"><i class="material-icons">add_circle</i></a>
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
          <div class="alert " style="display: none;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <i class="material-icons">close</i>
            </button>
            <span class="message"></span>
          </div>
          <div class="table-responsive">
            <table id="getfields" class="table table-striped- table-bordered table-hover table-checkable responsive no-wrap">
              <thead class=" text-primary">
                <th>{!! trans('panel.global.no') !!}</th>
                <th>{!! trans('panel.global.action') !!}</th>
                <th> Process Name</th>
                <th> Created By</th>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ url('/').'/'.asset('assets/js/jquery.custom.js') }}"></script>
  <script type="text/javascript">
    $(function() {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      var table = $('#getfields').DataTable({
        processing: true,
        serverSide: true,
        "order": [
          [0, 'desc']
        ],
        ajax: "{{ route('customer_process.index') }}",
        columns: [{
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'action',
            name: 'action',
            "defaultContent": '',
            orderable: false,
            searchable: false
          },
          {
            data: 'process_name',
            name: 'process_name',
            "defaultContent": ''
          },
          {
            data: 'creatbyname.name',
            name: 'creatbyname.name',
            "defaultContent": ''
          },
        ]
      });

      $('body').on('click', '.delete', function() {
        var id = $(this).attr("value");
        var token = $("meta[name='csrf-token']").attr("content");

        Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          type: "warning",
          showCancelButton: true,
          confirmButtonText: "Yes, delete it!",
          cancelButtonText: "No, cancel!"
        }).then((result) => {
          if (result.value) {
            $.ajax({
              url: "{{ url('customer_process') }}" + '/' + id,
              type: 'DELETE',
              data: {
                _token: token,
                id: id
              },
              success: function(data) {
                if (data.status == 'success') {
                  Swal.fire("Deleted!", data.message, "success");
                } else {
                  Swal.fire("Error!", data.message, "error");
                }
                table.draw();
              },
              error: function(xhr) {
                let message = "Something went wrong!";
                // if (xhr.responseJSON && xhr.responseJSON.message) {
                //   message = xhr.responseJSON.message;
                // } else if (xhr.responseText) {
                //   message = xhr.responseText;
                // }
                Swal.fire("Error!", message, "error");
              }
            });
          }
        });
      });


    });
  </script>
</x-app-layout>