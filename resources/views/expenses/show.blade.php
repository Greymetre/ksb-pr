
<style>

.activity-timeline{
position: relative;
}

.activity-item{
display: flex;
align-items: flex-start;
margin-bottom: 20px;
}

.activity-icon{
width: 36px;
height: 36px;
background: #00aadb;
color: white;
border-radius: 50%;
display: flex;
align-items: center;
justify-content: center;
font-size: 14px;
margin-right: 10px;
flex-shrink: 0;
}

.activity-content{
background: #f9fafb;
padding: 10px 12px;
border-radius: 6px;
width: 100%;
box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.activity-title{
font-weight: 600;
font-size: 13px;
color: #007bff;
}

.activity-user{
font-size: 12px;
color: #007bff;
margin-top: 2px;
}

.activity-time{
font-size: 11px;
color: #888;
margin-top: 3px;
}
</style>


@php
// one normalised list for the attachment viewer on the right of the popup
$expense_attachments = [];
foreach ($expense->getMedia('expense_file') as $expenseMedia) {
  $mediaExtension = strtolower(pathinfo($expenseMedia->getFullUrl(), PATHINFO_EXTENSION));
  $expense_attachments[] = [
    'id' => $expenseMedia->id,
    'name' => $expenseMedia->file_name ?: ('Attachment ' . $expenseMedia->id),
    'url' => $expenseMedia->getFullUrl(),
    'type' => in_array($mediaExtension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'])
      ? 'image'
      : ($mediaExtension === 'pdf' ? 'pdf' : 'file'),
    'delete_url' => route('deleteview', ['id' => $expenseMedia->id, 'expense_id' => $expense->id]),
  ];
}
@endphp

<div class="row expense-show-popup">
  <div class="{{ count($expense_attachments) ? 'col-lg-7' : 'col-lg-12' }} expense-show-main">

    @if(Session::has('success'))
    <div class="alert alert-success" id="hide_div">
      <button type="button" class="close" data-dismiss="alert">×</button>
      <strong>{!! session('success') !!}</strong>
    </div>
    @endif

    @if(Session::has('danger'))
    <div class="alert alert-danger" id="hide_danger">
      <button type="button" class="close" data-dismiss="alert">×</button>
      <strong>{!! session('danger') !!}</strong>
    </div>
    @endif


    <div class="alert" style="display: none;" id="hide_check">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <i class="material-icons">close</i>
      </button>
      <strong class="message"></strong>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="row expense-show-toolbar">
          <div class="col-4">
            <h3 class="card-title pb-3">Approve View</h3>
          </div>
          <div class="col-8 expense-show-actions">


            @if($expense->checker_status=='3')

            @if(auth()->user()->can(['expense_unchecked']))
            <button type="button" class="btn btn-dark unchecked_status">Unchecked</button>
            @endif

            @if(auth()->user()->can(['expense_approve']))
            <button type="button" class="btn btn-success approve_status">Approve Account</button>
            @endif

            @if(auth()->user()->can(['expense_reject']))
            <button type="button" class="btn btn-danger reject_status">Rejected</button>
            @endif

            @if(auth()->user()->can(['expense_hold']))
            <button type="button" class="btn btn-dark hold_status">Hold</button>
            @endif

            @elseif($expense->checker_status=='1')
            @if(auth()->user()->can(['expense_reject']))
            <button type="button" class="btn btn-danger reject_status">Rejected</button>
            @endif

            @elseif($expense->checker_status=='4')

            @if(auth()->user()->can(['expense_unchecked']))
            <button type="button" class="btn btn-dark checked_status">Checked &amp; Approved</button>
            @endif

            @if(auth()->user()->can(['expense_approve']))
            <button type="button" class="btn btn-success approve_status">Approve Account</button>
            @endif

            @if(auth()->user()->can(['expense_reject']))
            <button type="button" class="btn btn-danger reject_status">Rejected</button>
            @endif

            @if(auth()->user()->can(['expense_hold']))
            <button type="button" class="btn btn-dark hold_status">Hold</button>
            @endif

            @elseif($expense->checker_status=='5')

            @if(auth()->user()->can(['expense_unchecked']))
            <button type="button" class="btn btn-dark checked_status">Checked &amp; Approved</button>
            @endif

            @if(auth()->user()->can(['expense_approve']))
            <button type="button" class="btn btn-success approve_status">Approve Account</button>
            @endif

            @if(auth()->user()->can(['expense_reject']))
            <button type="button" class="btn btn-danger reject_status">Rejected</button>
            @endif

            @else

            @if(auth()->user()->can(['expense_checked']))
            <button type="button" class="btn btn-info checked_by_reporting_status">Checked By Reporting</button>
            @endif

            @if(auth()->user()->can(['expense_checked']))
            <button type="button" class="btn btn-dark checked_status">Checked &amp; Approved</button>
            @endif

            @if(auth()->user()->can(['expense_reject']))
            <button type="button" class="btn btn-danger reject_status">Rejected</button>
            @endif

            @if(auth()->user()->can(['expense_hold']))
            <button type="button" class="btn btn-dark hold_status">Hold</button>
            @endif

            @endif


            <?php
            if (Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Sub_Admin') || Auth::user()->hasRole('HR_Admin') || Auth::user()->hasRole('HO_Account')) { ?>

              <a class="btn btn-warning" href="{{route('expenses.edit', ['expense' => $expense->id])}}" role="button">Edit</a>

              <?php } else {

              $created_at  = $expense->created_at;
              $startDate = Carbon\Carbon::parse($created_at);
              $endDate = Carbon\Carbon::now();
              //$timeDifference = $startDate->diff($endDate)->format('%h hours and %i minutes');
              $diffInHours = $startDate->diffInHours($endDate);

              if ($diffInHours <= 24 && $expense->checker_status == 0) {

              ?>

                @if($expense->checker_status=='3' || $expense->checker_status=='1')
                @if(auth()->user()->can(['expenses_edit']))
                <a class="btn btn-warning" href="javascript:void(0)" role="button">Edit</a>
                @endif
                @else
                @if(auth()->user()->can(['expenses_edit']))
                <a class="btn btn-warning" href="{{route('expenses.edit', ['expense' => $expense->id])}}" role="button">Edit</a>
                @endif
                @endif

              <?php
              } else {
              ?>
                @if(auth()->user()->can(['expenses_edit']))
                <a class="btn btn-warning" href="javascript:void(0)" role="button">Edit</a>
                @endif
            <?php
              }
            }

            ?>


            <button type="button" class="btn btn-info expense-activity-toggle" id="expenseActivityToggle">
              <i class="material-icons">history</i> Activity
              @if(count($logdetails))<span class="expense-activity-count">{{ count($logdetails) }}</span>@endif
            </button>

            <!-- <a class="btn btn-primary" href="{{route('expenses.index')}}?executive_id={{$expense->user_id}}" role="button">Back</a> -->


          </div>
          <!-- /.col -->
        </div>
        <input type="hidden" id="expenseid" name="expense" value="{{$expense['id']}}">
        <hr>

        <div class="invoice p-3 mb-3">
          <!-- title row -->
          <div class="row">
            <div class="col-4">
              <h4>
                <small class="float-left expense-record-title">{{ trans('panel.expenses.title') }} #{!! $expense['id'] !!}</small>
              </h4>
            </div>

            <!-- /.col -->
          </div>
          <!-- info row -->
          <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
              <span class="expense-detail-label">From</span>
              <address>
                <strong>{!! isset($expense['users']['name']) ? $expense['users']['name'] :'' !!} ({{$expense->users->getdesignation->designation_name??''}}) </strong><br>
              </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <span class="expense-detail-label">Expense Type</span>
              <address>
                <strong>{!! $expense['expense_type']['name'] !!}
                  ( {!! isset($expense['expense_type']['allowance_type_id'])?config('constants.allowance_type.'.$expense['expense_type']['allowance_type_id']):''!!} )
                </strong><br>
              </address>


            </div>

            <div class="col-sm-4 invoice-col">
              <span class="expense-detail-label">Date</span>
              <address>
                <!-- <strong>{!! $expense['date'] !!}</strong><br> -->
                <strong>{!! date("d/m/Y g:i a", strtotime($expense['date'])) !!}</strong><br>
              </address>
            </div>
          </div>

          <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
              <span class="expense-detail-label">Expense Status</span>
              <address>
                <strong> @if($expense->checker_status=='1')
                  Approve
                  @elseif($expense->checker_status=='2')
                  Reject
                  @elseif($expense->checker_status=='3')
                  Checked
                  @elseif($expense->checker_status=='4')
                  Checked By Reporting
                  @elseif($expense->checker_status=='5')
                  Hold
                  @else
                  Pending
                  @endif

                </strong><br>
              </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <span class="expense-detail-label">Status Change Reason</span>
              <address>
                <strong>{{$expense->reason??""}}</strong><br>
              </address>
            </div>
            <div class="col-sm-4 invoice-col">
              <span class="expense-detail-label">Department</span>
              <address>
                <strong>{{$expense['users']['getdepartment']?$expense['users']['getdepartment']['name']:'-'}}</strong><br>
              </address>
            </div>
            <div class="col-sm-6 invoice-col expense-attendance-inline">
              <h6>
                Punch In - {{ !empty($attendance?->punchin_time) ? date('h:i A', strtotime($attendance->punchin_time)) : '-' }}
                | {{ $attendance?->punchin_address ?: '-' }}
              </h6>
              <h6>
                Punch Out - {{ !empty($attendance?->punchout_time) ? date('h:i A', strtotime($attendance->punchout_time)) : '-' }}
                | {{ $attendance?->punchout_address ?: '-' }}
              </h6>
              <h6>Total Working Hours - {{ $total_working_hours ?: '-' }}</h6>
            </div>
            <div class="col-sm-6 invoice-col">
              <div class="row">
                <div class="col-md-6">
                  @php
                  $city = \App\Models\City::find(optional($paln)->town);
                  @endphp
                  <h6>Today Plan - {{ $city->city_name ?? '' }} </h6>
                  <h6>Today Visit - {{ $city->city_name  ?? '' }}</h6>
                  <h6>Live Location - <a href="{{url('/livelocation').'?user_id='.$expense->user_id.'&date='.$expense->date}}"><i class="material-icons">location_on</i></a></h6>
                  @php
                  // the expense day itself is what gets tracked, and the controller already
                  // checked whether that day still has a live location trail to plot
                  $trackDate = $expense->date ? \Carbon\Carbon::parse($expense->date)->format('Y-m-d') : null;
                  $trackAvailable = $trackDate && ($has_track_activity ?? false);
                  @endphp
                  <h6>Geolocator -
                    @if($trackAvailable)
                    <a href="{{ url('map-all').'?submit='.urlencode('Track Activity').'&user_id='.$expense->user_id.'&track_date='.$trackDate }}"
                      target="_blank" rel="noopener" title="Open track activity for {{ \Carbon\Carbon::parse($trackDate)->format('d M Y') }}"><i class="material-icons">travel_explore</i></a>
                    @else
                    @php
                    $trackMessage = 'No track activity found for ' . ($trackDate ? \Carbon\Carbon::parse($trackDate)->format('d M Y') : 'this expense') . '. Live location data is only kept for the last 15 days.';
                    @endphp
                    <a href="javascript:void(0);" class="geolocator-unavailable" title="{{ $trackMessage }}"
                      onclick="showGeolocatorUnavailable({{ \Illuminate\Support\Js::from($trackMessage) }}); return false;"><i class="material-icons">travel_explore</i></a>
                    @endif
                  </h6>
                </div>
                <div class="col-md-6">
                  <h6>Total Visit - {{$total_visit??"0"}}</h6>
                  <h6>Total KM Run - {{$total_dis? number_format($total_dis, 2) :"0.00"}}</h6>
                </div>
              </div>
            </div>
          </div>

          @if($expense->expense_type->allowance_type_id == '1')

          <!-- Table row -->
          <div class="row">
            <div class="col-12 table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>{{ trans('panel.expenses.fields.start_km') }}</th>
                    <th>{{ trans('panel.expenses.fields.stop_km') }}</th>
                    <th>{{ trans('panel.expenses.fields.total_km') }}</th>
                    <th>{{ trans('panel.expenses.fields.rate') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>{!! $expense['start_km']??0 !!}</td>
                    <td>{!! $expense['stop_km']??0 !!}</td>
                    <td>{!! $expense['total_km']??0 !!}</td>
                    <td>{!! $expense['rate'] ?? $expense['expense_type']['rate'] ?? 0 !!}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->

          @else

          <!-- Table row -->
          <div class="row">
            <div class="col-12 table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>{{ trans('panel.expenses.fields.rate') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>{!! $expense['rate'] ?? $expense['expense_type']['rate'] ?? 0 !!}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->

          @endif



          <div class="row">
            <div class="col-6">
              <div class="table-responsive">
                <table class="table">
                  <tbody>
                    <tr>
                      <th style="width:20%">Claim Amount:</th>
                      <td>{{$expense['claim_amount']??0}}</td>
                      <input type="text" name="claims" id="claim_new_amount" value="{{$expense['claim_amount']??0}}" hidden>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="col-6">
              <div class="table-responsive">
                <table class="table">
                  <tbody>
                    <tr>
                      <th style="width:25%">Approved Amount</th>
                      <td>{{$expense['approve_amount']??0}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- accepted payments column -->
            <div class="col-6">
              <p class="lead"></p>
              <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                <strong> Note:</strong>
                {!! $expense['note'] !!}
              </p>
            </div>
          </div>


          <!-- /.row -->
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->
    </div>
    <!-- /.col -->
  </div>



<div class="col-lg-5 expense-show-attachment">
  <div class="card expense-attachment-card">
    <div class="expense-attachment-head">
      <div class="expense-attachment-title">
        <i class="material-icons">attach_file</i>
        <span>Expense Attachment</span>
      </div>
      @if(count($expense_attachments))
      <div class="expense-attachment-tools">
        <button type="button" class="expense-attachment-tool" id="expenseAttachmentZoomOut" title="Zoom out"><i class="material-icons">remove</i></button>
        <span class="expense-attachment-zoom" id="expenseAttachmentZoomLabel">100%</span>
        <button type="button" class="expense-attachment-tool" id="expenseAttachmentZoomIn" title="Zoom in"><i class="material-icons">add</i></button>
        <a class="expense-attachment-tool" id="expenseAttachmentOpen" href="{{ $expense_attachments[0]['url'] }}" target="_blank" rel="noopener" title="Open in new tab"><i class="material-icons">fullscreen</i></a>
      </div>
      @endif
    </div>

    @if(count($expense_attachments))
    <div class="expense-attachment-stage" id="expenseAttachmentStage">
      <img id="expenseAttachmentImage" src="" alt="Expense attachment" style="display:none;">
      <iframe id="expenseAttachmentFrame" src="" title="Expense attachment" style="display:none;"></iframe>
      <div class="expense-attachment-fallback" id="expenseAttachmentFallback">
        <i class="material-icons">description</i>
        <p id="expenseAttachmentFallbackName"></p>
        <a class="btn btn-info btn-sm" id="expenseAttachmentDownload" href="javascript:void(0);" target="_blank" rel="noopener">Download file</a>
      </div>
    </div>

    <div class="expense-attachment-foot">
      <div class="expense-attachment-file">
        <span id="expenseAttachmentName"></span>
        <small id="expenseAttachmentCount"></small>
      </div>
      <div class="expense-attachment-actions">
        <button type="button" class="expense-attachment-tool" id="expenseAttachmentPrev" title="Previous attachment"><i class="material-icons">chevron_left</i></button>
        <button type="button" class="expense-attachment-tool" id="expenseAttachmentNext" title="Next attachment"><i class="material-icons">chevron_right</i></button>
        <a class="expense-attachment-tool is-danger" id="expenseAttachmentDelete" href="javascript:void(0);" title="Delete attachment"
          onclick="return confirm('Are you sure you want to delete this file?');"><i class="material-icons">delete</i></a>
      </div>
    </div>
    @else
    <div class="expense-attachment-empty">
      <i class="material-icons">image_not_supported</i>
      <p>No attachment uploaded for this expense.</p>
    </div>
    @endif
  </div>
</div>
  </div>

  <!-- /.row -->


  <!-- new model for reject status -->

  <div class="modal fade bd-example-modal-lg" id="reject_expense" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title">
            <span class="modal-title">Submit </span> Reject <span class="pull-right">
              <a href="javascript:void(0)" class="btn btn-just-icon btn-danger" data-dismiss="modal">
                <i class="material-icons">clear</i>
              </a>
            </span>
          </h4>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ route('rejectExpense') }}" enctype="multipart/form-data" id="rejectExpenseForm"> @csrf
            <div class="row">
              <div class="col-md-12">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Reason </label>
                  <input type="text" name="reason" id="reason" class="form-control" value="{!! old( 'reason') !!}" required> <br><br>
                  <input type="text" name="expense_id" id="reject_expense_id" class="form-control" hidden>
                </div>
              </div>
            </div>
            <button type="buuton" class="btn btn-info save-rjc" onclick="disableButtonreject()">Reject</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- end model for status -->


  <!-- new model for approve status -->

  <div class="modal fade bd-example-modal-lg" id="approve_expense" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content card">
        <div class="card-header card-header-icon card-header-theme">
          <div class="card-icon">
            <i class="material-icons">perm_identity</i>
          </div>
          <h4 class="card-title">
            <span class="modal-title">Submit </span> Checked &amp; Approved <span class="pull-right">
              <a href="javascript:void(0)" class="btn btn-just-icon btn-danger" data-dismiss="modal">
                <i class="material-icons">clear</i>
              </a>
            </span>
          </h4>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ route('approveExpense') }}" enctype="multipart/form-data" id="approveExpenseForms"> @csrf
            <div class="row">
              <div class="col-md-12">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Approve Amount</label>
                  <input type="text" name="approve_amnt" id="approve_amnt" class="form-control" value="{!! old( 'reason') !!}" required> <br><br>
                  <input type="text" name="expense_new_id" id="expense_new_id" class="form-control" hidden>
                </div>

                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Reason</label>
                  <input type="text" name="reasons" id="reasons" class="form-control" value="{!! old( 'reasons') !!}"> <br><br>
                </div>


              </div>
            </div>
            <input type="button" class="btn btn-info save-apr" onclick="disableButton()" value="Checked &amp; Approved">
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- end model for status -->

  <!-- activity drawer, opened from the Approve View action bar -->
  <div class="expense-activity-drawer" id="expenseActivityDrawer" aria-hidden="true">
    <div class="expense-activity-backdrop" data-close-activity></div>
    <aside class="expense-activity-panel" role="dialog" aria-label="Expense activity">
      <div class="expense-activity-header">
        <h3>Activity</h3>
        <button type="button" class="expense-activity-close" data-close-activity aria-label="Close activity">
          <i class="material-icons">close</i>
        </button>
      </div>
      <div class="expense-activity-body">
        <div class="activity-timeline">
          @forelse($logdetails as $logdetail)
          <div class="activity-item">
            <div class="activity-icon">
              <i class="fas fa-user-check"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">
                {{ trans('panel.expenses.title') }}
                <strong>#{{ $expense['id'] }}</strong>
                {{$logdetail->status_type ?? ''}}
              </div>
              <div class="activity-user">
                {{$logdetail->logusers->employee_codes ?? ''}}
                {{$logdetail->logusers->name ?? ''}}
              </div>
              <div class="activity-time">
                {{ date("d M Y - g:i A", strtotime($logdetail->created_at)) }}
              </div>
            </div>
          </div>
          @empty
          <div class="expense-activity-empty">No activity recorded for this expense yet.</div>
          @endforelse
        </div>
      </div>
    </aside>
  </div>

  <script>
    // the popup body is re-injected on every open, so this runs fresh each time
    (function () {
      var attachments = @json($expense_attachments);

      // ---- activity drawer -------------------------------------------------
      var drawer = document.getElementById('expenseActivityDrawer');
      var toggle = document.getElementById('expenseActivityToggle');

      function closeDrawer() {
        if (!drawer) { return; }
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
      }

      if (drawer && toggle) {
        toggle.addEventListener('click', function () {
          drawer.classList.add('is-open');
          drawer.setAttribute('aria-hidden', 'false');
        });

        Array.prototype.forEach.call(drawer.querySelectorAll('[data-close-activity]'), function (el) {
          el.addEventListener('click', closeDrawer);
        });

        // one esc handler only, even though this script re-runs on every popup open
        if (window.expenseActivityEscHandler) {
          document.removeEventListener('keydown', window.expenseActivityEscHandler, true);
        }
        window.expenseActivityEscHandler = function (event) {
          if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
            // swallow the key so bootstrap does not close the whole expense modal too
            event.stopPropagation();
            closeDrawer();
          }
        };
        document.addEventListener('keydown', window.expenseActivityEscHandler, true);

        // the drawer lives inside the popup body, so it must go with it
        $('#expenseModal').one('hide.bs.modal', closeDrawer);
      }

      // ---- attachment viewer ----------------------------------------------
      if (!attachments.length) { return; }

      var stageImage = document.getElementById('expenseAttachmentImage');
      var stageFrame = document.getElementById('expenseAttachmentFrame');
      var fallback = document.getElementById('expenseAttachmentFallback');
      var fallbackName = document.getElementById('expenseAttachmentFallbackName');
      var downloadLink = document.getElementById('expenseAttachmentDownload');
      var openLink = document.getElementById('expenseAttachmentOpen');
      var deleteLink = document.getElementById('expenseAttachmentDelete');
      var nameLabel = document.getElementById('expenseAttachmentName');
      var countLabel = document.getElementById('expenseAttachmentCount');
      var zoomLabel = document.getElementById('expenseAttachmentZoomLabel');
      var zoomIn = document.getElementById('expenseAttachmentZoomIn');
      var zoomOut = document.getElementById('expenseAttachmentZoomOut');
      var prev = document.getElementById('expenseAttachmentPrev');
      var next = document.getElementById('expenseAttachmentNext');

      var index = 0;
      var zoom = 1;
      var MIN_ZOOM = 0.5;
      var MAX_ZOOM = 4;

      function applyZoom() {
        var current = attachments[index];
        var zoomable = current.type === 'image';
        stageImage.style.transform = 'scale(' + zoom + ')';
        zoomLabel.textContent = Math.round(zoom * 100) + '%';
        zoomIn.disabled = !zoomable || zoom >= MAX_ZOOM;
        zoomOut.disabled = !zoomable || zoom <= MIN_ZOOM;
      }

      function render() {
        var current = attachments[index];

        stageImage.style.display = current.type === 'image' ? 'block' : 'none';
        stageFrame.style.display = current.type === 'pdf' ? 'block' : 'none';
        fallback.style.display = current.type === 'file' ? 'flex' : 'none';

        stageImage.src = current.type === 'image' ? current.url : '';
        stageFrame.src = current.type === 'pdf' ? current.url : 'about:blank';
        fallbackName.textContent = current.name;
        downloadLink.href = current.url;
        openLink.href = current.url;
        deleteLink.href = current.delete_url;

        nameLabel.textContent = current.name;
        countLabel.textContent = attachments.length > 1
          ? (index + 1) + ' of ' + attachments.length
          : '';
        prev.disabled = index === 0;
        next.disabled = index === attachments.length - 1;

        zoom = 1;
        applyZoom();
      }

      zoomIn.addEventListener('click', function () {
        zoom = Math.min(MAX_ZOOM, Math.round((zoom + 0.25) * 100) / 100);
        applyZoom();
      });
      zoomOut.addEventListener('click', function () {
        zoom = Math.max(MIN_ZOOM, Math.round((zoom - 0.25) * 100) / 100);
        applyZoom();
      });
      prev.addEventListener('click', function () {
        if (index > 0) { index--; render(); }
      });
      next.addEventListener('click', function () {
        if (index < attachments.length - 1) { index++; render(); }
      });

      render();
    })();
  </script>

  <script>
    // older expenses have no live location trail left to plot, so tell the user
    // instead of sending them to a track activity page that would only error out
    function showGeolocatorUnavailable(message) {
      if (window.Swal && typeof window.Swal.fire === 'function') {
        window.Swal.fire({
          icon: 'info',
          title: 'No track activity',
          text: message,
          confirmButtonText: 'OK'
        });
      } else {
        window.alert(message);
      }
    }
  </script>

  <!-- Custom styles for this page -->

  <style>
    body.fk-shell .expense-record-title {
      display: block;
      margin-bottom: 12px;
      color: #fff !important;
      font-size: 20px !important;
      font-weight: 700 !important;
      line-height: 1.25;
    }

    /* the expense detail modal is fullscreen, so keep the alert on top of it */
    .swal2-container.swal2-shown {
      z-index: 999999 !important;
    }

    body.fk-shell .geolocator-unavailable {
      opacity: .45;
      cursor: not-allowed;
    }

    body.fk-shell .expense-detail-label {
      display: block;
      margin-bottom: 8px;
      color: #fff !important;
      font-size: 14px;
      font-weight: 600;
      line-height: 1.3;
    }

    body.fk-shell .fk-expense-modal #approve_expense .modal-dialog {
      display: flex;
      width: calc(100% - 30px) !important;
      max-width: 640px !important;
      height: auto !important;
      min-height: calc(100% - 3.5rem);
      margin: 1.75rem auto !important;
      align-items: center;
    }

    body.fk-shell .fk-expense-modal #approve_expense .modal-content {
      width: 100%;
      height: auto !important;
      max-height: calc(100vh - 60px);
      margin: 0 !important;
      overflow-y: auto;
      border-radius: 16px !important;
    }

    body.fk-shell .fk-expense-modal #approve_expense .card-header {
      display: flex;
      min-height: 76px;
      margin: 0 !important;
      padding: 16px 20px !important;
      align-items: center;
    }

    body.fk-shell .fk-expense-modal #approve_expense .card-icon {
      margin: 0 16px 0 0 !important;
      flex: 0 0 auto;
    }

    body.fk-shell .fk-expense-modal #approve_expense .card-title {
      display: flex;
      width: 100%;
      margin: 0 !important;
      align-items: center;
      justify-content: space-between;
    }

    body.fk-shell .fk-expense-modal #approve_expense .modal-body {
      padding: 24px !important;
    }

    body.fk-shell .fk-expense-modal #approve_expense .modal-body .row {
      margin-right: -8px;
      margin-left: -8px;
    }

    body.fk-shell .fk-expense-modal #approve_expense .modal-body [class*="col-"] {
      padding-right: 8px;
      padding-left: 8px;
    }

    body.fk-shell .fk-expense-modal #approve_expense .save-apr {
      min-width: 190px;
      margin: 12px 0 0 !important;
    }

    /* ---- attachment viewer ------------------------------------------- */
    body.fk-shell .expense-attachment-card {
      display: flex;
      height: calc(100vh - 210px);
      min-height: 420px;
      flex-direction: column;
      overflow: hidden;
    }

    body.fk-shell .expense-attachment-head {
      display: flex;
      padding: 12px 14px;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      border-bottom: 1px solid rgba(90, 130, 220, .22);
      background: rgba(9, 25, 58, .75);
    }

    body.fk-shell .expense-attachment-title {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #f7f9ff;
      font-size: 14px;
      font-weight: 600;
    }

    body.fk-shell .expense-attachment-title .material-icons {
      font-size: 18px;
      color: #7fb2ff;
    }

    body.fk-shell .expense-attachment-tools,
    body.fk-shell .expense-attachment-actions {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    body.fk-shell .expense-attachment-tool {
      display: inline-flex;
      width: 30px;
      height: 30px;
      padding: 0;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(90, 130, 220, .32);
      border-radius: 8px;
      background: rgba(8, 20, 50, .8);
      color: #d8e7ff;
      cursor: pointer;
      transition: background .15s ease, border-color .15s ease;
    }

    body.fk-shell .expense-attachment-tool:hover:not(:disabled) {
      border-color: rgba(120, 175, 255, .6);
      background: rgba(20, 45, 95, .9);
      color: #fff;
    }

    body.fk-shell .expense-attachment-tool:disabled {
      opacity: .35;
      cursor: not-allowed;
    }

    body.fk-shell .expense-attachment-tool.is-danger {
      border-color: rgba(240, 100, 110, .45);
      color: #ff9aa4;
    }

    body.fk-shell .expense-attachment-tool .material-icons {
      font-size: 17px;
    }

    body.fk-shell .expense-attachment-zoom {
      min-width: 44px;
      color: #b9cbf0;
      font-size: 11px;
      font-weight: 600;
      text-align: center;
    }

    body.fk-shell .expense-attachment-stage {
      display: flex;
      flex: 1 1 auto;
      align-items: flex-start;
      justify-content: center;
      overflow: auto;
      padding: 14px;
      background:
        repeating-conic-gradient(rgba(255, 255, 255, .03) 0% 25%, transparent 0% 50%) 50% / 22px 22px,
        rgba(4, 12, 32, .85);
    }

    body.fk-shell .expense-attachment-stage img {
      display: block;
      max-width: 100%;
      border-radius: 8px;
      transform-origin: top center;
      transition: transform .15s ease;
    }

    body.fk-shell .expense-attachment-stage iframe {
      width: 100%;
      height: 100%;
      min-height: 380px;
      border: 0;
      border-radius: 8px;
      background: #fff;
    }

    body.fk-shell .expense-attachment-fallback {
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin: auto;
      color: #c3cee9;
      text-align: center;
    }

    body.fk-shell .expense-attachment-fallback .material-icons {
      font-size: 44px;
      color: #6f8dc7;
    }

    body.fk-shell .expense-attachment-foot {
      display: flex;
      padding: 10px 14px;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      border-top: 1px solid rgba(90, 130, 220, .22);
      background: rgba(9, 25, 58, .75);
    }

    body.fk-shell .expense-attachment-file {
      overflow: hidden;
      color: #dce7ff;
      font-size: 12px;
    }

    body.fk-shell .expense-attachment-file span {
      display: block;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    body.fk-shell .expense-attachment-file small {
      color: #8497c6;
      font-size: 10px;
    }

    body.fk-shell .expense-attachment-empty {
      display: flex;
      flex: 1 1 auto;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 30px;
      color: #8497c6;
      text-align: center;
    }

    body.fk-shell .expense-attachment-empty .material-icons {
      font-size: 44px;
    }

    /* ---- activity drawer ---------------------------------------------- */
    body.fk-shell .expense-activity-toggle .material-icons {
      margin-right: 4px;
      font-size: 15px;
      vertical-align: -3px;
    }

    body.fk-shell .expense-activity-count {
      display: inline-block;
      min-width: 18px;
      margin-left: 6px;
      padding: 1px 5px;
      border-radius: 9px;
      background: rgba(255, 255, 255, .22);
      font-size: 10px;
      line-height: 1.5;
    }

    body.fk-shell .expense-activity-drawer {
      position: fixed;
      z-index: 1080;
      inset: 0;
      visibility: hidden;
      pointer-events: none;
    }

    body.fk-shell .expense-activity-drawer.is-open {
      visibility: visible;
      pointer-events: auto;
    }

    body.fk-shell .expense-activity-backdrop {
      position: absolute;
      inset: 0;
      background: rgba(2, 8, 24, .55);
      opacity: 0;
      transition: opacity .2s ease;
    }

    body.fk-shell .expense-activity-drawer.is-open .expense-activity-backdrop {
      opacity: 1;
    }

    body.fk-shell .expense-activity-panel {
      position: absolute;
      top: 0;
      right: 0;
      display: flex;
      width: min(390px, 92vw);
      height: 100%;
      flex-direction: column;
      border-left: 1px solid rgba(90, 130, 220, .3);
      background: #071630;
      box-shadow: -18px 0 40px rgba(0, 0, 0, .45);
      transform: translateX(100%);
      transition: transform .25s ease;
    }

    body.fk-shell .expense-activity-drawer.is-open .expense-activity-panel {
      transform: translateX(0);
    }

    body.fk-shell .expense-activity-header {
      display: flex;
      padding: 16px 18px;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(90, 130, 220, .22);
    }

    body.fk-shell .expense-activity-header h3 {
      margin: 0;
      color: #f7f9ff;
      font-size: 17px;
      font-weight: 600;
    }

    body.fk-shell .expense-activity-close {
      display: inline-flex;
      width: 30px;
      height: 30px;
      padding: 0;
      align-items: center;
      justify-content: center;
      border: 1px solid rgba(90, 130, 220, .32);
      border-radius: 8px;
      background: rgba(8, 20, 50, .8);
      color: #d8e7ff;
      cursor: pointer;
    }

    body.fk-shell .expense-activity-body {
      flex: 1 1 auto;
      padding: 16px 18px;
      overflow-y: auto;
    }

    body.fk-shell .expense-activity-empty {
      padding: 24px 0;
      color: #8497c6;
      font-size: 12px;
      text-align: center;
    }

    @media (max-width: 991px) {
      body.fk-shell .expense-attachment-card {
        height: 60vh;
      }
    }
  </style>

  <script type="text/javascript">
    $("document").ready(function() {
      setTimeout(function() {
        $("#hide_div").remove();
      }, 3000); // 3 secs

    });
  </script>
<script>
    var plan = @json($paln);
    console.log(plan);
</script> 
  <script type="text/javascript">
    $("document").ready(function() {
      setTimeout(function() {
        $("#hide_danger").remove();
      }, 3000);
    });
  </script>
