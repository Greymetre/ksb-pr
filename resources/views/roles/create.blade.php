<x-app-layout>
    <style>
    /* ================= CONTAINER ================= */
    .permission-container {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: auto;
        position: relative;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        background: #fafafa;
    }

    /* ================= TABLE BASE ================= */
    .permission-table {
        width: 100%;
        min-width: 1200px;
        border-collapse: collapse;
        /* keep row lines */
        table-layout: auto;
    }

    /* ================= CELL BASE ================= */
    .permission-table th,
    .permission-table td {
        padding: 8px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #e0e0e0;
        /* horizontal lines */
        border-right: 1px solid #e0e0e0;
        /* vertical lines */
    }

    /* remove last column vertical line */
    .permission-table th:last-child,
    .permission-table td:last-child {
        border-right: none;
    }

    /* ================= PERMISSION COLUMN ================= */
    .permission-table th:first-child,
    .permission-table td:first-child {
        position: sticky;
        left: 0;
        z-index: 10;
        background: #fafafa;

        /* 🔥 INCREASED WIDTH */
        min-width: 420px;
        max-width: 420px;

        color: #000;
        font-weight: 500;
        white-space: normal;
        word-break: break-word;
    }

    /* ================= HEADER ================= */
    .permission-table thead th {
        position: sticky;
        top: 0;
        background: #fafafa;
        z-index: 5;
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
        /* 🔥 prevents wrapping */
        height: 48px;
    }

    .permission-table thead th:first-child {
        z-index: 15;
    }

    /* ================= ROLE COLUMNS ================= */
    .permission-table th:not(:first-child),
    .permission-table td:not(:first-child) {
        text-align: center;
        min-width: 140px;
    }

    /* ================= CHECKBOX ALIGNMENT (NO UI CHANGE) ================= */
    .permission-table td:not(:first-child) {
        padding: 0;
    }

    .permission-table .form-check {
        margin: 0;
        padding: 0;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .permission-table .form-check-label {
        margin: 0;
        line-height: 1;
    }

    /* keep material checkbox intact */
    .permission-table .form-check-sign {
        top: 0;
    }

    /* ================= TEXT ================= */
    .permission-table td:first-child {
        line-height: 1.5;
    }

    /* checkbox cell */
    .permission-table td.checkbox-cell {
        padding: 0;
        text-align: center;
        vertical-align: middle;
    }

    /* center the checkbox wrapper */
    .permission-table td.checkbox-cell .form-check {
        margin: 0;
        padding: 0;
        height: 100%;

        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* reset Material absolute positioning */
    .permission-table td.checkbox-cell .form-check-sign {
        position: relative;
        top: 0;
        left: 0;
    }
    </style>



    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">perm_identity</i>
                    </div>
                    <h4 class="card-title ">{{ trans('panel.global.create') }} {{ trans('panel.role.title_singular') }}
                        <span class="pull-right">
                            <div class="btn-group">
                                @if(auth()->user()->can(['role_access']))
                                <a href="{{ url('roles') }}" class="btn btn-just-icon btn-theme"
                                    title="{!! trans('panel.role.title_singular') !!}{!! trans('panel.global.list') !!}"><i
                                        class="material-icons">next_plan</i></a>
                                @endif
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

                    {{-- ================= CREATE ROLE FORM ================= --}}
                    <form method="POST" action="{{ route('roles.store') }}" class="mb-4">
                        @csrf

                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label class="col-form-label">Role Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter role name"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary mt-4">
                                    <i class="material-icons">add</i> Create Role
                                </button>
                            </div>
                        </div>
                    </form>
                    {{-- ================= END CREATE ROLE FORM ================= --}}

                    {{-- ================= SAVE PERMISSIONS FORM ================= --}}
                    <form method="POST" action="{{ route('roles.savePermissions') }}">
                        @csrf

                        <label class="col-form-label mt-3">
                            Permissions
                        </label>

                        <div class="row mb-2 align-items-center">
    <div class="col-6">
        <input 
            type="text" 
            id="permissionSearch" 
            class="form-control"
            placeholder="Search permission..." 
            onkeyup="filterPermissions()"
        >
    </div>

    <div class="col-6 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-info btn-xs" onclick="selectAllPermissions()">
            Select All
        </button>
        <button type="button" class="btn btn-info btn-xs" onclick="deselectAllPermissions()">
            Deselect All
        </button>
    </div>
</div>




                        <div class="permission-container">


                            <table class=" permission-table ">
                                <thead>
                                    <tr>
                                        <th>Permission</th>
                                        @foreach($roles as $role)
                                        <th class="text-center">
                                            {{
        Str::of($role->name)
            ->replace(['_', '-'], ' ')
            ->snake()
            ->replace('_', ' ')
            ->title()
    }}
                                        </th>

                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($permissions as $permission)
                                    <tr>
                                        <td>{{ $permission->name }}</td>

                                        @foreach($roles as $role)
                                        <td class="checkbox-cell">
                                            <div class="form-check">
                                                <label class="form-check-label m-0">
                                                    <input type="checkbox" class="form-check-input permission-checkbox"
                                                        name="permissions[{{ $role->id }}][]"
                                                        value="{{ $permission->id }}"
                                                        {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                                    <span class="form-check-sign">
                                                        <span class="check"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>



                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="material-icons">save</i> Save Permissions
                            </button>
                        </div>
                    </form>
                    {{-- ================= END SAVE PERMISSIONS FORM ================= --}}



                </div>



            </div>
        </div>

    </div>
    <!-- <div class="pull-right">
                            {{ Form::submit('Submit', array('class' => 'btn btn-theme')) }}
                        </div> -->
    <!-- <div class="text-right mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="material-icons">save</i>
            Save Permissions
        </button>
    </div> -->

    <!-- {{ Form::close() }} -->
    </div>
    </div>
    </div>
    </div>
    <script src="{{ url('/').'/'.asset('assets/js/validation_users.js') }}"></script>

    <script>
    function selectAllPermissions() {
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
    }

    function deselectAllPermissions() {
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    }

    function filterPermissions() {
        const searchValue = document
            .getElementById('permissionSearch')
            .value
            .toLowerCase();

        const rows = document.querySelectorAll('.permission-table tbody tr');

        rows.forEach(row => {
            const permissionCell = row.querySelector('td:first-child');
            const text = permissionCell.innerText.toLowerCase();

            row.style.display = text.includes(searchValue) ? '' : 'none';
        });
    }
    </script>

</x-app-layout>