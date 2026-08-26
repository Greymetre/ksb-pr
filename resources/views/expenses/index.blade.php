 <x-app-layout>
   <div class="row">
     <div class="col-md-12">
       <div class="card">
         <div class="card-header card-header-icon card-header-theme">
           <div class="card-icon">
             <i class="material-icons">perm_identity</i>
           </div>
           <h4 class="card-title ">{!! trans('panel.expenses.title_singular') !!} {!! trans('panel.global.list') !!}
             <span class="">
               <div class="btn-group header-frm-btn">


                 @if(auth()->user()->can(['expense_download']))
                 <form method="GET" action="{{ URL::to('expenses-download') }}">
                   <div class="d-flex flex-row">
                     <div class="p-2" style="width:195px;">
                       <select class="select2" name="payroll" id="payroll" data-style="select-with-transition">
                         <option value="" selected>All Grades</option>
                         @foreach($pay_rolls as $key=>$payroll)
                         <option value="{{ $key }}">{{ $payroll }}</option>
                         @endforeach
                       </select>
                     </div>

                     <div class="p-2" style="width:195px;">
                       <select class="select2" name="expenses_type" id="expenses_type" data-style="select-with-transition">
                       </select>
                     </div>

                     <div class="p-2" style="width:150px;">
                       <select class="select2" name="expense_id" id="expense_id" data-style="select-with-transition" title="Select Expense">
                         <option value="">Select Expense Id</option>

                       </select>
                     </div>


                     <div class="p-2" style="width:150px;">
                      <!-- <select class="selectpicker" name="branch_id" id="branch_id" data-style="select-with-transition" title="Select Branch"> -->

                       <select class="select2" name="branch_id" id="branch_id" data-style="select-with-transition" title="Select Branch">
                         <option value="">Select Branch</option>
                         @if(@isset($branches ))
                         @foreach($branches as $branch)
                         <option value="{!! $branch['id'] !!}">{!! $branch['name'] !!}</option>
                         @endforeach
                         @endif
                       </select>
                     </div>


                     <div class="p-2" style="width:150px;">
                       <select class="select2" name="division_id" id="division_id" data-style="select-with-transition" title="Select Zone">
                         <option value="">Select Zone</option>
                         @if(@isset($divisions ))
                         @foreach($divisions as $division)
                         <option value="{!! $division['id'] !!}">{!! $division['name'] !!}</option>
                         @endforeach
                         @endif
                       </select>
                     </div>
                     <div class="p-2" style="width:150px;">
                       <select class="select2" name="executive_id" id="executive_id" data-style="select-with-transition" title="Select Employee">
                         <option value="">Select Employee</option>
                       </select>
                     </div>

                     <div class="p-2" style="width:160px;">
                       <select class="select2" name="status" id="status" data-style="select-with-transition" title="Select Status">
                         <option value="">Select Status</option>
                         <option value="5">Hold</option>
                         <option value="4">Checked By Reporting</option>
                         <option value="3">Checked</option>
                         <option value="1">Approved</option>
                         <option value="2">Rejected</option>
                         <option value="0">Pending</option>
                       </select>
                     </div>

                     <div class="p-2" style="width:160px;">
                       <select class="select2" name="attechments" id="attechments" data-style="select-with-transition" title="Select Attechments">
                         <option value="">Select Attechments</option>
                         <option value="yes">Yes</option>
                         <option value="no">No</option>
                       </select>
                     </div>


                     <div class="p-2" style="width:140px;">
                       <input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="Start Date" autocomplete="off" readonly>
                     </div>
                     <div class="p-2" style="width:140px;">
                       <input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="End Date" autocomplete="off" readonly>
                     </div>

                     <div class="p-2"><button type="button" class="btn btn-just-icon btn-theme" title="Reset Fliter" onclick="resetFilter();"><i class="fa fa-refresh" aria-hidden="true"></i></button></div>

                     <div class="p-2"><button class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.download') !!} {!! trans('panel.customers.title') !!}"><i class="material-icons">cloud_download</i></button></div>
                   </div>
                 </form>



                 @endif

                 <div class="next-btn">
                 <div class="btn-group multi-a-r d-none fk-preserve-list-action">
                    <button class="btn btn-dark btn-sm multiChange mr-1" data-status="3"  title="Check">Check</button>
                    <button class="btn btn-success btn-sm multiChange mr-1" data-status="1"  title="Approve">Approve</button>
                    <button class="btn btn-danger btn-sm multiChange mr-2" data-status="2" title="Reject">Reject</button>
                  </div>
                   @if(auth()->user()->can(['expenses_create']))
                   <a href="{{ route('expenses.create') }}" class="btn btn-just-icon btn-theme" title="{!!  trans('panel.global.add') !!} {!! trans('panel.expenses.title_singular') !!}"><i class="material-icons">add_circle</i></a>
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
           @if(session('message_success'))
           <div class="alert alert-success">
             <button type="button" class="close" data-dismiss="alert" aria-label="Close">
               <i class="material-icons">close</i>
             </button>
             <span>
               <li>{{session('message_success')}}</li>
             </span>
           </div>
           @endif
           <div class="alert " style="display: none;">
             <button type="button" class="close" data-dismiss="alert" aria-label="Close">
               <i class="material-icons">close</i>
             </button>
             <span class="message"></span>
           </div>

           <div id="expenseInlineFilters" class="expense-inline-filters" aria-label="Expense filters"></div>

           <div class="expense-status-summary" aria-label="Expense status summary">
             <button type="button" class="expense-status-card is-pending" data-expense-status="0">
               <div class="expense-status-icon"><i class="material-icons">schedule</i></div>
               <div class="expense-status-content">
                 <span class="expense-status-label">Pending</span>
                 <strong class="expense-status-value" id="pending_count">{{$pending_count}}</strong>
               </div>
             </button>
             <button type="button" class="expense-status-card is-approved" data-expense-status="1">
               <div class="expense-status-icon"><i class="material-icons">check_circle</i></div>
               <div class="expense-status-content">
                 <span class="expense-status-label">Approved</span>
                 <strong class="expense-status-value" id="approve_count">{{$approve_count}}</strong>
               </div>
             </button>
             <button type="button" class="expense-status-card is-rejected" data-expense-status="2">
               <div class="expense-status-icon"><i class="material-icons">cancel</i></div>
               <div class="expense-status-content">
                 <span class="expense-status-label">Rejected</span>
                 <strong class="expense-status-value" id="reject_count">{{$reject_count}}</strong>
               </div>
             </button>
             <button type="button" class="expense-status-card is-checked" data-expense-status="3">
               <div class="expense-status-icon"><i class="material-icons">verified</i></div>
               <div class="expense-status-content">
                 <span class="expense-status-label">Checked</span>
                 <strong class="expense-status-value" id="checked_count">{{$checked_count}}</strong>
               </div>
             </button>
             <button type="button" class="expense-status-card is-reporting-checked" data-expense-status="4">
               <div class="expense-status-icon"><i class="material-icons">fact_check</i></div>
               <div class="expense-status-content">
                 <span class="expense-status-label">Checked By Reporting</span>
                 <strong class="expense-status-value" id="reporting_checked_count">{{$reporting_checked_count}}</strong>
               </div>
             </button>
             <button type="button" class="expense-status-card is-hold" data-expense-status="5">
               <div class="expense-status-icon"><i class="material-icons">pause_circle</i></div>
               <div class="expense-status-content">
                 <span class="expense-status-label">Hold</span>
                 <strong class="expense-status-value" id="hold_count">{{$hold_count}}</strong>
               </div>
             </button>
           </div>

           <div class="table-responsive">
             <table id="getallexpenses" class="table table-striped- table-bordered table-hover table-checkable no-wrap">
               <thead class=" text-primary">
                  <th> # </th>
                 <th>{!! trans('panel.expenses.fields.expense_id') !!}</th>
                 <th>Expense Date</th>
                 <!-- <th>{!! trans('panel.expenses.fields.user') !!}</th> -->
                 <th>Employee</th>
                 <th>{!! trans('panel.expenses.fields.designation') !!}</th>
                 <th>{!! trans('panel.expenses.fields.expense_type') !!}</th>
                 <th>{!! trans('panel.expenses.fields.rate') !!}</th>
                 <th>{!! trans('panel.expenses.fields.claim_amount') !!}</th>
                 <th>{!! trans('panel.expenses.fields.approve_amount') !!}</th>
                 <th>{!! trans('panel.expenses.fields.expense_status') !!}</th>
                 <th class="lenth_text">{!! trans('panel.expenses.fields.note') !!}</th>
                 <th>{!! trans('panel.expenses.fields.created_at') !!}</th>
                 <!-- <th>{!! trans('panel.expenses.fields.branch') !!}</th> -->
                  <th>Branch</th>
                 <th>{!! trans('panel.expenses.fields.total_km') !!}</th>
                 <th>{!! trans('panel.global.action') !!}</th>
                 <th>Attechments</th>
               </thead>
               <tbody>
               </tbody>
             </table>
           </div>
         </div>
       </div>
     </div>
   </div>

   <!-- Bootstrap Modal -->

   <div class="modal fade fk-expense-modal" id="expenseModal" tabindex="-1" role="dialog" aria-labelledby="expenseModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-fullscreen" role="document">
       <div class="modal-content">
         <div class="modal-header">
           <h5 class="modal-title" id="expenseModalLabel">Expense Details</h5>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body" id="expenseDetails">
           <!-- Expense details will be loaded here -->
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-primary expense-modal-close" data-dismiss="modal">Close</button>
         </div>
       </div>
     </div>
   </div>

   <style type="text/css">
     .flex-row .p-2 {
       width: 20% !important;
       /*   overflow: hidden;*/
     }

     .flex-row {
       flex-direction: row !important;
       flex-wrap: wrap;
     }

     span.select2.select2-container.select2-container--default.select2-container--below.select2-container--focus {}

     span#select2-executive_id-container {
       color: #000;
       line-height: 43px;
     }

     .modal-fullscreen {
       width: 90%;
       height: 100%;
       margin: auto;
       padding: 0;
       max-width: none;
     }

     .modal-fullscreen .modal-content {
       height: 100vh;
       /* Viewport height */
     }

     .modal-fullscreen .modal-body {
       overflow-y: auto;
     }

     body.fk-shell .expense-inline-filters {
       max-height: 0;
       margin: 0 18px;
       padding: 0 12px;
       overflow: hidden;
       border: 1px solid transparent;
       border-radius: 12px;
       background: rgba(7, 18, 44, .45);
       opacity: 0;
       pointer-events: none;
       transition: max-height .3s ease, margin .3s ease, padding .3s ease, border-color .3s ease, opacity .2s ease;
     }

     body.fk-shell .expense-inline-filters.is-expanded {
       max-height: 420px;
       margin: 4px 18px 8px;
       padding: 12px;
       border-color: rgba(90, 130, 220, .22);
       opacity: 1;
       pointer-events: auto;
     }

     body.fk-shell .fk-list-actions .expense-filter-toggle.is-active {
       border-color: rgba(34, 211, 238, .55) !important;
       background: rgba(34, 211, 238, .14) !important;
       color: #22d3ee !important;
     }

     body.fk-shell .expense-inline-filters:empty {
       display: none;
     }

     body.fk-shell .expense-inline-filters form {
       width: 100%;
       margin: 0;
     }

     body.fk-shell .expense-inline-filters .d-flex {
       display: grid !important;
       grid-template-columns: repeat(6, minmax(145px, 1fr));
       gap: 10px;
       align-items: center;
     }

     body.fk-shell .expense-inline-filters .p-2 {
       width: auto !important;
       min-width: 0;
       padding: 0 !important;
     }

     body.fk-shell .expense-inline-filters .select2-container,
     body.fk-shell .expense-inline-filters select,
     body.fk-shell .expense-inline-filters .form-control {
       width: 100% !important;
     }

     body.fk-shell .expense-inline-filters .btn {
       width: 100% !important;
       min-width: 0 !important;
       height: 42px !important;
       min-height: 42px !important;
       margin: 0 !important;
       border-radius: 10px !important;
     }

     @media (max-width: 1399px) {
       body.fk-shell .expense-inline-filters .d-flex {
         grid-template-columns: repeat(4, minmax(145px, 1fr));
       }
     }

     @media (max-width: 991px) {
       body.fk-shell .expense-inline-filters .d-flex {
         grid-template-columns: repeat(2, minmax(145px, 1fr));
       }
     }

     @media (max-width: 575px) {
       body.fk-shell .expense-inline-filters {
         margin-right: 12px;
         margin-left: 12px;
       }

       body.fk-shell .expense-inline-filters.is-expanded {
         max-height: 900px;
         margin-right: 12px;
         margin-left: 12px;
       }

       body.fk-shell .expense-inline-filters .d-flex {
         grid-template-columns: 1fr;
       }
     }

     #getallexpenses tbody td:nth-child(10) {
       text-align: center;
       vertical-align: middle;
     }

     body.fk-shell #getallexpenses .expense-list-status-group {
       display: flex;
       width: 100%;
       align-items: center;
       justify-content: center;
     }

     body.fk-shell #getallexpenses .expense-list-status {
       width: auto;
       min-width: 105px;
       height: 36px;
       min-height: 36px;
       margin: 0;
       padding: 0 14px !important;
       border: 0 !important;
       border-radius: 8px !important;
       color: #fff !important;
       font-size: 12px;
       font-weight: 600;
       line-height: 1.2;
       white-space: nowrap;
     }

     body.fk-shell #getallexpenses .expense-list-status.btn-success {
       background: #4caf50 !important;
     }

     body.fk-shell #getallexpenses .expense-list-status.btn-danger {
       background: #f44336 !important;
     }

     body.fk-shell #getallexpenses .expense-list-status.btn-dark {
       background: #343a40 !important;
     }

     body.fk-shell #getallexpenses .expense-list-status.btn-info {
       background: #00bcd4 !important;
     }

     body.fk-shell #getallexpenses .expense-list-status.btn-warning {
       background: #ff9800 !important;
     }
   </style>
   <script>
     var expensesIndexUrl = "{{ route('expenses.index') }}";
     var expensesTypeUrl = "{{ route('getexpenseType') }}";
     var expensesActiveUrl = "{{ url('expenses-active') }}";
     var expensesCheckedUrl = "{{ url('expenses-checked-by-reporting') }}";
     var expensesDataUrl = "{{ route('getExpensesData') }}";
     var expensesUncheckUrl = "{{ url('expenses-uncheck') }}";
     var expensesMainUrl = "{{ url('expenses') }}";
     var removeSessionUrl = "{{ route('remove.session') }}";
     var session_exec = "{{ session('executive_id') }}";
     var multiApprove = "{{ url('approveExpenses')}}";
     var multiCheck = "{{ url('checkExpenses')}}";
     var multiReject = "{{ url('rejectExpenses')}}";
     var token = $("meta[name='csrf-token']").attr("content");

     (function moveExpenseFiltersInline() {
       var target = document.getElementById('expenseInlineFilters');
       var form = document.querySelector('.card-header form[action*="expenses-download"]');
       if (target && form) {
         target.appendChild(form);
         target.setAttribute('aria-hidden', 'true');
       }
     })();

     (function installExpenseFilterToggle(attempt) {
       var actions = document.querySelector('.fk-list-page-head .fk-list-actions');
       var target = document.getElementById('expenseInlineFilters');
       if (!target) {
         return;
       }

       if (!actions) {
         if (attempt < 50) {
           setTimeout(function () {
             installExpenseFilterToggle(attempt + 1);
           }, 100);
         }
         return;
       }

       if (actions.querySelector('.expense-filter-toggle')) {
         return;
       }

       var button = document.createElement('button');
       button.type = 'button';
       button.className = 'btn fk-filter-trigger expense-filter-toggle';
       button.setAttribute('aria-controls', 'expenseInlineFilters');
       button.setAttribute('aria-expanded', 'false');
       button.innerHTML = '<span class="material-icons">tune</span><span>Filters</span>';

       button.addEventListener('click', function () {
         var expanded = target.classList.toggle('is-expanded');
         button.classList.toggle('is-active', expanded);
         button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
         target.setAttribute('aria-hidden', expanded ? 'false' : 'true');
       });

       var createButton = actions.querySelector('.fk-create-action');
       actions.insertBefore(button, createButton || null);
     })(0);

     $(document).on('click', '.expense-status-card[data-expense-status]', function () {
       var status = String($(this).data('expense-status'));
       $('.expense-status-card').removeClass('is-active');
       $(this).addClass('is-active');
       $('#status').val(status).trigger('change');
     });


     function resetFilter() {
       localStorage.setItem("is_reset", '1');
       localStorage.setItem("payroll", '');
       localStorage.setItem("executive_id", '');
       localStorage.setItem("start_date", '');
       localStorage.setItem("end_date", '');
       localStorage.setItem("status", '');
       localStorage.setItem("expense_id", '');
       localStorage.setItem("division_id", '');
       fetch(removeSessionUrl, {
         method: 'POST',
         headers: {
           'Content-Type': 'application/json',
           'X-CSRF-TOKEN': token
         }
       })
       window.location.href = expensesMainUrl;
     }

     function showExpense(eid) {
       $.ajax({
         url: '/expenses/' + eid, // The URL to the route that returns the expense details
         method: 'GET',
         success: function(response) {

           $('#expenseDetails').html(response);
           // Show the modal
           $('#expenseModal').modal('show');
         },
         error: function(xhr, status, error) {
           console.error('Error fetching expense details:', error);
         }
       });
     }
     $("#payroll").on("change", function() {
       localStorage.removeItem('executive_id');
       var payroll = $(this).val();
       $.ajax({
         url: "{{ url('getUserList') }}",
         dataType: "json",
         type: "POST",
         data: {
           _token: "{{csrf_token()}}",
           payroll: payroll
         },
         success: function(res) {
           var html = '<option value="">Select Employee</option>';
           $.each(res, function(k, v) {
             html += '<option value="' + v.id + '"> (' + v.employee_codes + ') ' + v.name + '</option>';
           });
           $("#executive_id").html(html);
         }
       });
     }).trigger("chnage");

    $(document).on('click', '.row-checkbox', function () {
        
        const selectedValues = [];
        $('.row-checkbox:checked').each(function () {
            selectedValues.push($(this).val());
        });

        if(selectedValues.length > 0){
          $(".multi-a-r").removeClass('d-none');
        }else{
          $(".multi-a-r").addClass('d-none');
        }
    });
   </script>
   
   <script src="{!! asset('assets/js/expense_filter.js?v='.time()) !!}"></script>

 </x-app-layout>
