<x-app-layout>
  <style>
    /* Status pills */
    .fk-issue-status {
      display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px;
      font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; white-space: nowrap;
    }
    .fk-issue-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .fk-issue-status.is-pending { color: #f59e0b; background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.32); }
    .fk-issue-status.is-in_progress { color: #3b82f6; background: rgba(59,130,246,.12); border: 1px solid rgba(59,130,246,.32); }
    .fk-issue-status.is-resolved { color: #22c55e; background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.32); }

    /* Row actions — the theme squeezes every table .btn into a 34px icon square,
       so the Resolve pill overrides it with a more specific selector */
    body.fk-shell table.dataTable tbody td .btn.fk-issue-resolve,
    body.fk-shell table.table tbody td .btn.fk-issue-resolve,
    .fk-issue-resolve {
      width: auto !important; min-width: 104px !important; height: 34px !important; min-height: 34px !important;
      display: inline-flex !important; align-items: center; justify-content: center; gap: 6px;
      margin: 0 !important; padding: 0 14px !important; overflow: visible !important;
      border-radius: 10px !important; border: 1px solid rgba(34,197,94,.42) !important;
      background: rgba(34,197,94,.14) !important; color: #22c55e !important; box-shadow: none !important;
      font-family: 'Sora', 'Inter', sans-serif; font-size: 12px !important; font-weight: 600 !important;
      letter-spacing: .2px; line-height: 1 !important; text-transform: none !important; white-space: nowrap;
      transition: background .18s ease, border-color .18s ease;
    }
    body.fk-shell table.dataTable tbody td .btn.fk-issue-resolve:hover,
    body.fk-shell table.table tbody td .btn.fk-issue-resolve:hover,
    .fk-issue-resolve:hover {
      background: rgba(34,197,94,.26) !important; border-color: rgba(34,197,94,.62) !important;
    }
    body.fk-shell table.dataTable tbody td .btn.fk-issue-resolve .material-icons,
    body.fk-shell table.table tbody td .btn.fk-issue-resolve .material-icons,
    .fk-issue-resolve .material-icons { color: #22c55e !important; font-size: 17px !important; }
    #issuesTable td:last-child, #issuesTable th:last-child { text-align: center; min-width: 130px; }
    .fk-issue-locked { color: var(--fk-list-dim); display: inline-flex; }
    .fk-issue-locked .material-icons { font-size: 20px; }
    #issuesTable td.fk-issue-text { white-space: normal; min-width: 220px; max-width: 320px; }

    /* Popups — CRM theme variables, same look as the calling popup */
    .fk-issue-modal .modal-dialog { max-width: 520px; margin-top: 40px; }
    .fk-issue-modal .modal-content { overflow: hidden; }
    .fk-issue-modal .fk-issue-head {
      display: flex; align-items: center; gap: 14px; padding: 18px 22px;
      border-bottom: 1px solid var(--fk-list-border);
    }
    .fk-issue-modal .fk-issue-avatar {
      width: 46px; height: 46px; flex: 0 0 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, rgba(34,197,94,.22), rgba(34,211,238,.16)); border: 1px solid rgba(34,197,94,.35); color: #22c55e;
    }
    .fk-issue-modal .fk-issue-title { flex: 1; min-width: 0; }
    .fk-issue-modal .fk-issue-title h4 { margin: 0; color: var(--fk-list-heading); font-family: 'Sora', 'Inter', sans-serif; font-size: 18px; font-weight: 700; }
    .fk-issue-modal .fk-issue-title p { margin: 3px 0 0; color: var(--fk-list-muted); font-size: 13px; }
    .fk-issue-modal .fk-issue-close {
      width: 38px; height: 38px; flex: 0 0 38px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center;
      background: var(--fk-form-bg); border: 1px solid var(--fk-form-border); color: var(--fk-list-soft); transition: all .18s ease;
    }
    .fk-issue-modal .fk-issue-close:hover { background: rgba(255,93,122,.14); border-color: rgba(255,93,122,.45); color: #ff5d7a; }
    .fk-issue-modal .modal-body { padding: 18px 22px 4px; }
    .fk-issue-modal .modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px 18px; border-top: 1px solid var(--fk-list-border); }
    .fk-issue-modal .modal-footer .btn { margin: 0; }
    .fk-issue-modal .fk-req { color: #ff5d7a; margin-left: 2px; }
    .fk-issue-modal .select2-container { width: 100% !important; }
    .fk-issue-modal .select2-dropdown { z-index: 2000; }
    .fk-issue-modal .select2-results__option { color: var(--fk-list-text); }
    .fk-issue-modal .select2-results__option--highlighted[aria-selected] { background: rgba(34,211,238,.16) !important; color: var(--fk-list-heading) !important; }
    .fk-issue-summary {
      margin-bottom: 16px; padding: 12px 14px; border-radius: 12px;
      background: var(--fk-list-row); border: 1px solid var(--fk-list-border);
    }
    .fk-issue-summary strong { display: block; color: var(--fk-list-heading); font-size: 14px; }
    .fk-issue-summary small { display: block; margin: 2px 0 8px; color: var(--fk-list-muted); font-size: 12px; }
    .fk-issue-summary p { margin: 0; color: var(--fk-list-text); font-size: 13px; white-space: pre-line; word-break: break-word; }
    .fk-issue-note {
      margin: 0 0 12px; padding: 10px 12px; border-radius: 10px; border: 1px dashed var(--fk-list-border-strong);
      color: var(--fk-list-muted); font-size: 12px; line-height: 1.5;
    }
  </style>

  <div class="row">
    <div class="col-md-12">
      <div class="fk-list-page-head">
        <div class="fk-list-heading-block">
          <div class="fk-list-breadcrumb">
            <span>User Management</span>
            <span>&rsaquo;</span>
            <span class="fk-current">User Issues</span>
          </div>
          <div class="fk-list-title-row">
            <h1 class="fk-list-title">User Issues</h1>
            <span class="fk-list-count" id="issue-record-count"></span>
          </div>
        </div>
        <div class="fk-list-actions">
          <button class="btn fk-filter-trigger" type="button" data-filter-target="#issue-filter-drawer">
            <span class="material-icons">tune</span>
            <span>Filters</span>
          </button>
          <button type="button" class="btn fk-create-action" id="openCreateIssue">
            <span class="material-icons">add_circle</span>
            <span>Create Issue</span>
          </button>
        </div>
      </div>

      <div class="card fk-listing-card" data-fk-listing-ready="1">
        <div class="card-body">
          <aside class="fk-filter-drawer" id="issue-filter-drawer">
            <div class="fk-filter-drawer-head">
              <div class="fk-filter-drawer-icon"><span class="material-icons">tune</span></div>
              <div>
                <h3>Advanced Filters</h3>
                <p>Applied live to the issue list</p>
              </div>
              <button type="button" class="fk-filter-close" aria-label="Close filters"><span class="material-icons">close</span></button>
            </div>
            <div class="fk-filter-drawer-body">
              <form action="{{ route('user-issues.download') }}" method="get" id="issue-filter-form">
                <div class="d-flex flex-wrap flex-row">
                  <div class="p-2" data-label="Status">
                    <select class="select2" name="status" id="filter_status" title="Status">
                      <option value="">All Status</option>
                      @foreach($statuses as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="p-2" data-label="User">
                    <select class="select2" name="user_id" id="filter_user" title="User">
                      <option value="">All Users</option>
                      @foreach($employees as $employee)
                      <option value="{{ $employee->id }}">{{ $employee->name }}{{ $employee->employee_codes ? ' ('.$employee->employee_codes.')' : '' }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="p-2" data-label="Start Date">
                    <input type="date" class="form-control" name="start_date" id="filter_start">
                  </div>
                  <div class="p-2" data-label="End Date">
                    <input type="date" class="form-control" name="end_date" id="filter_end">
                  </div>
                </div>
              </form>
            </div>
            <div class="fk-filter-drawer-tools">
              <button class="btn fk-tool-export" type="submit" form="issue-filter-form" style="grid-column: 1 / -1;">
                <span class="material-icons">cloud_download</span>
                <span>Export</span>
              </button>
            </div>
            <div class="fk-filter-drawer-foot">
              <button class="btn fk-filter-reset" type="button">Reset</button>
              <button class="btn fk-filter-apply" type="button">Apply Filters</button>
            </div>
          </aside>

          <div class="table-responsive">
            <table id="issuesTable" class="table table-striped- table-bordered table-hover table-checkable no-wrap">
              <thead class="text-primary">
                <th>#</th>
                <th>Zone</th>
                <th>Branch</th>
                <th>E Code</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Issue Date</th>
                <th>Issue Description</th>
                <th>Status</th>
                <th>Issue Create By</th>
                <th>Solve By</th>
                <th>Solve Remark</th>
                <th>Action</th>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Create issue --}}
  <div class="modal fade fk-issue-modal" id="createIssueModal" tabindex="-1" role="dialog" aria-labelledby="createIssueTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form id="createIssueForm">
          <div class="fk-issue-head">
            <div class="fk-issue-avatar"><i class="material-icons">add_alert</i></div>
            <div class="fk-issue-title">
              <h4 id="createIssueTitle">Create Issue</h4>
              <p>New issues start as Pending</p>
            </div>
            <button type="button" class="fk-issue-close" data-dismiss="modal" aria-label="Close"><i class="material-icons">close</i></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="createIssueUser">User<span class="fk-req">*</span></label>
              <select class="fk-user-picker" id="createIssueUser" name="user_id" style="width:100%"></select>
            </div>
            <div class="form-group">
              <label for="createIssueText">Issue<span class="fk-req">*</span></label>
              <textarea class="form-control" id="createIssueText" name="description" placeholder="Describe the issue..." required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="createIssueSubmit">Create</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Resolve / move to in progress --}}
  <div class="modal fade fk-issue-modal" id="resolveIssueModal" tabindex="-1" role="dialog" aria-labelledby="resolveIssueTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form id="resolveIssueForm">
          <input type="hidden" id="resolveIssueId">
          <div class="fk-issue-head">
            <div class="fk-issue-avatar"><i class="material-icons">task_alt</i></div>
            <div class="fk-issue-title">
              <h4 id="resolveIssueTitle">Resolve Issue</h4>
              <p>Update the progress of this issue</p>
            </div>
            <button type="button" class="fk-issue-close" data-dismiss="modal" aria-label="Close"><i class="material-icons">close</i></button>
          </div>
          <div class="modal-body">
            <div class="fk-issue-summary">
              <strong id="resolveIssueName"></strong>
              <small id="resolveIssueMeta"></small>
              <p id="resolveIssueText"></p>
            </div>
            <div class="form-group">
              <label for="resolveIssueStatus">Status</label>
              <select class="form-control" id="resolveIssueStatus" name="status">
                <option value="in_progress">In Progress</option>
                <option value="resolved">Resolved</option>
              </select>
            </div>
            <p class="fk-issue-note" id="resolveIssueNote">Solve By and Solve Remark are captured only when the issue is resolved.</p>
            <div id="resolveIssueFields">
              <div class="form-group">
                <label for="resolveIssueSolvedBy">Solve By<span class="fk-req">*</span></label>
                <select class="fk-user-picker" id="resolveIssueSolvedBy" name="solved_by" style="width:100%"></select>
              </div>
              <div class="form-group">
                <label for="resolveIssueRemark">Solve Remark<span class="fk-req">*</span></label>
                <textarea class="form-control" id="resolveIssueRemark" name="solve_remark" placeholder="Enter resolution remark..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="resolveIssueSubmit">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script type="text/javascript">
    $(function() {
      $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

      function notify(type, message) {
        if (window.Swal && Swal.fire) {
          Swal.fire({ icon: type, title: message, timer: type === 'success' ? 1800 : undefined, showConfirmButton: type !== 'success' });
        } else {
          alert(message);
        }
      }

      function errorMessage(xhr, fallback) {
        return (xhr.responseJSON && xhr.responseJSON.message) || fallback;
      }

      var table = $('#issuesTable').DataTable({
        processing: true,
        serverSide: true,
        order: [],
        language: { searchPlaceholder: 'Name, code, zone, issue...' },
        ajax: {
          url: "{{ route('user-issues.index') }}",
          data: function(d) {
            d.status = $('#filter_status').val();
            d.user_id = $('#filter_user').val();
            d.start_date = $('#filter_start').val();
            d.end_date = $('#filter_end').val();
          }
        },
        columns: [
          { data: 'DT_RowIndex', orderable: false, searchable: false },
          { data: 'zone', orderable: false, searchable: false },
          { data: 'branch', orderable: false, searchable: false },
          { data: 'employee_code', orderable: false, searchable: false },
          { data: 'employee_name', orderable: false, searchable: false },
          { data: 'designation', orderable: false, searchable: false },
          { data: 'issue_date', orderable: false, searchable: false },
          { data: 'description', orderable: false, searchable: false, className: 'fk-issue-text' },
          { data: 'status_badge', orderable: false, searchable: false },
          { data: 'created_by_name', orderable: false, searchable: false },
          { data: 'solved_by_name', orderable: false, searchable: false },
          { data: 'solve_remark', orderable: false, searchable: false, className: 'fk-issue-text' },
          { data: 'action', orderable: false, searchable: false }
        ]
      });

      table.on('draw.dt', function() {
        var total = table.page.info().recordsDisplay;
        $('#issue-record-count').text(total + ' records').addClass('is-visible');
      });

      $('.fk-filter-apply').on('click', function() {
        table.draw();
        $('#issue-filter-drawer').removeClass('is-open');
        $('body').removeClass('fk-filter-open');
      });
      $('.fk-filter-reset').on('click', function() {
        $('#filter_status, #filter_user').val('').trigger('change');
        $('#filter_start, #filter_end').val('');
        table.draw();
      });
      $('#filter_status, #filter_user, #filter_start, #filter_end').on('change', function() {
        table.draw();
      });

      // Searchable user pickers inside the popups: users come from the server
      // (first 50 on open, then by name / code / mobile as you type)
      function userPicker($select, $modal) {
        if (!$.fn.select2) return;
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.select2({
          dropdownParent: $modal.find('.modal-content'),
          placeholder: 'Select user',
          allowClear: true,
          width: '100%',
          minimumInputLength: 0,
          ajax: {
            url: "{{ route('user-issues.users') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) { return { q: params.term || '' }; },
            processResults: function(data) { return { results: data.results || [] }; },
            cache: true
          },
          language: {
            noResults: function() { return 'No user found'; },
            searching: function() { return 'Loading users...'; }
          }
        });
      }
      // The layout runs $('.select2').select2() on page ready. Select2's own container
      // also carries the "select2" class, so if our pickers exist first the layout
      // builds a second, empty select2 on top of them ("No results found").
      // Build ours after the layout and clear any stray instance it made.
      setTimeout(function() {
        $('#createIssueModal, #resolveIssueModal').find('span.select2-hidden-accessible').each(function() {
          $(this).select2('destroy');
        });
        userPicker($('#createIssueUser'), $('#createIssueModal'));
        userPicker($('#resolveIssueSolvedBy'), $('#resolveIssueModal'));
      }, 0);

      // ---------- Create ----------
      $('#openCreateIssue').on('click', function() {
        $('#createIssueForm')[0].reset();
        $('#createIssueUser').val('').trigger('change');
        $('#createIssueModal').modal('show');
      });

      $('#createIssueForm').on('submit', function(e) {
        e.preventDefault();
        if (!$('#createIssueUser').val() || !$.trim($('#createIssueText').val())) {
          notify('error', 'Please select a user and describe the issue.');
          return;
        }
        var $btn = $('#createIssueSubmit').prop('disabled', true);
        $.post("{{ route('user-issues.store') }}", {
          user_id: $('#createIssueUser').val(),
          description: $('#createIssueText').val()
        }).done(function(res) {
          $('#createIssueModal').modal('hide');
          notify('success', res.message);
          table.draw(false);
        }).fail(function(xhr) {
          notify('error', errorMessage(xhr, 'Unable to create the issue.'));
        }).always(function() {
          $btn.prop('disabled', false);
        });
      });

      // ---------- Resolve / In progress ----------
      function toggleResolveFields() {
        var resolved = $('#resolveIssueStatus').val() === 'resolved';
        $('#resolveIssueFields').toggle(resolved);
        $('#resolveIssueNote').toggle(!resolved);
      }
      $('#resolveIssueStatus').on('change', toggleResolveFields);

      $('#issuesTable').on('click', '.fk-issue-resolve', function() {
        var issue = $(this).data('issue');
        $('#resolveIssueForm')[0].reset();
        $('#resolveIssueId').val(issue.id);
        $('#resolveIssueName').text(issue.name || '-');
        $('#resolveIssueMeta').text([issue.date, issue.zone].filter(Boolean).join(' · '));
        $('#resolveIssueText').text(issue.description || '');
        $('#resolveIssueStatus').val(issue.status === 'in_progress' ? 'resolved' : 'in_progress');
        $('#resolveIssueSolvedBy').val('').trigger('change');
        toggleResolveFields();
        $('#resolveIssueModal').modal('show');
      });

      $('#resolveIssueForm').on('submit', function(e) {
        e.preventDefault();
        var status = $('#resolveIssueStatus').val();
        if (status === 'resolved' && (!$('#resolveIssueSolvedBy').val() || !$.trim($('#resolveIssueRemark').val()))) {
          notify('error', 'Please select Solve By and enter the Solve Remark.');
          return;
        }
        var $btn = $('#resolveIssueSubmit').prop('disabled', true);
        $.post("{{ url('user-issues') }}/" + $('#resolveIssueId').val() + '/status', {
          status: status,
          solved_by: $('#resolveIssueSolvedBy').val(),
          solve_remark: $('#resolveIssueRemark').val()
        }).done(function(res) {
          $('#resolveIssueModal').modal('hide');
          notify('success', res.message);
          table.draw(false);
        }).fail(function(xhr) {
          notify('error', errorMessage(xhr, 'Unable to update the issue.'));
        }).always(function() {
          $btn.prop('disabled', false);
        });
      });
    });
  </script>
</x-app-layout>
