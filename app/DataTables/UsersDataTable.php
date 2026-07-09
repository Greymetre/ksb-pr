<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UsersDataTable extends DataTable
{

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('created_at', function ($data) {
                return isset($data->created_at) ? showdatetimeformat($data->created_at) : '';
            })
            ->editColumn('name', function ($data) {
                return $data->name . ($data->user_customer ? ' (' . $data->user_customer->sap_code . ')' : '');
            })
            ->editColumn('getBranchNames', function ($data) {
                $branchIds = array_filter(explode(',', $data->branch_id)); // Remove empty values
                if (!empty($branchIds)) {
                    return \App\Models\Branch::whereIn('id', $branchIds)->pluck('branch_name')->implode(', ');
                } else {
                    return "-";
                }
            })
            ->addColumn('action', function ($query) {
                $btn = '';
                $activebtn = '';
                if (auth()->user()->can(['user_edit'])) {
                    if ($query->roles()->where('id', '29')->exists()) {
                        $btn = $btn . '<a href="' . url("customer-user?id=" . encrypt($query->id)) . '" class="btn btn-info btn-just-icon btn-sm edit" id="' . encrypt($query->id) . '" title="' . trans('panel.global.edit') . ' ' . trans('panel.user.title_singular') . '">
                              <i class="material-icons">edit</i>
                            </a>';
                    } else {
                        $btn = $btn . '<a href="' . url("users/" . encrypt($query->id) . '/edit') . '" class="btn btn-info btn-just-icon btn-sm edit" id="' . encrypt($query->id) . '" title="' . trans('panel.global.edit') . ' ' . trans('panel.user.title_singular') . '">
                              <i class="material-icons">edit</i>
                            </a>';
                    }
                }
                // if(auth()->user()->can(['user_show']))
                // {
                //   $btn = $btn.'<a href="'.url("users/".encrypt($query->id)).'" class="btn btn-theme btn-just-icon btn-sm show" id="'.encrypt($query->id).'" title="'.trans('panel.global.show').' '.trans('panel.user.title_singular').'">
                //         <i class="material-icons">visibility</i>
                //       </a>';
                // }
                if (auth()->user()->can(['user_delete'])) {
                    $btn = $btn . ' <a type="button" href="#" class="btn btn-danger btn-just-icon btn-sm delete" value="' . $query->id . '" title="' . trans('panel.global.delete') . ' ' . trans('panel.user.title_singular') . '">
                                <i class="material-icons">clear</i>
                              </a>';
                }
                return '<div class="btn-group btn-group-sm" role="group" aria-label="Small button group">
                                ' . $btn . '
                            </div>';
            })
            ->addColumn('active', function ($query) {
                if (auth()->user()->can(['user_active'])) {
                    $active = ($query->active == 'Y') ? 'checked="" value="' . $query->active . '"' : 'value="' . $query->active . '"';
                    return '<div class="togglebutton">
                        <label>
                          <input type="checkbox"' . $active . ' id="' . $query->id . '" class="activeRecord">
                          <span class="toggle"></span>
                        </label>
                      </div>';
                }
            })
            ->addColumn('image', function ($query) {
                $profileimage = asset('assets/img/placeholder.jpg');
                if ($query->getMedia('profile_image')->count() > 0 && Storage::disk('s3')->exists($query->getMedia('profile_image')[0]->getPath())) {
                    return '<img src="' . $query->getMedia('profile_image')[0]->getFullUrl() . '" border="0" width="70" class="img-rounded imageDisplayModel" align="center" />';
                } else {
                    return '<img src="' . $profileimage . '" border="0" width="70" class="img-rounded imageDisplayModel" align="center" />';
                }
            })
            ->addColumn('roles', function ($query) {
                $roles = '';
                if (count($query->roles) > 0) {
                    foreach ($query->roles as $k => $role) {
                        if (count($query->roles) == ($k + 1)) {
                            $roles .= $role->name;
                        } else {
                            $roles .= $role->name . ', ';
                        }
                    }
                }
                return $roles;
            })
            ->addColumn('wahtsappmobile', function ($query) {
                $whatsappLink = "https://wa.me/" . $query->mobile;
                return '<a class="fk-whatsapp-link" href="' . $whatsappLink . '" target="_blank" rel="noopener" title="WhatsApp">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                            </svg>
                        </a>';
            })
            ->rawColumns(['action', 'image', 'active', 'roles', 'getBranchNames', 'wahtsappmobile']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model, Request $request)
    {
        $userids = getUsersReportingToAuth();
        $data = $model->with('createdbyname', 'getbranch', 'getdesignation', 'reportinginfo', 'userinfo', 'getdivision')
            ->where(function ($query) use ($userids, $request) {
                if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('Admin') && !Auth::user()->hasRole('Sub_Admin')) {
                    $query->whereIn('id', $userids);
                }
                if ($request->active && !empty($request->active)) {
                    $query->where('active', $request->active);
                }
                if ($request->division_id && !empty($request->division_id)) {
                    $query->where('division_id', $request->division_id);
                }
                if ($request->branch_id && !empty($request->branch_id)) {
                    $query->where('branch_id', $request->branch_id);
                }
                if ($request->department_id && !empty($request->department_id)) {
                    $query->where('department_id', $request->department_id);
                }
            })
            ->whereHas('roles', function ($query) use ($request) {
                if ($request->user_type == 'customer') {
                    $query->whereIn('id', config('constants.customer_roles'));
                } else {
                    $query->whereNotIn('id', config('constants.customer_roles'));
                }
            })
            ->latest()
            ->newQuery();

        return $data;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1)
            ->buttons(
                Button::make('create'),
                Button::make('export'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
            Column::make('id'),
            Column::make('add your columns'),
            Column::make('created_at'),
            Column::make('updated_at'),
        ];
    }
}
