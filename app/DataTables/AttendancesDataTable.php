<?php

namespace App\DataTables;

use App\Models\Attendance;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Auth;

class AttendancesDataTable extends DataTable
{
    /**
     * Build DataTable class.
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()

            ->addColumn('employee_codes', function ($row) {
                return $row->users->employee_codes ?? '-';
            })

            ->editColumn('created_at', function ($data) {
                return isset($data->created_at) ? showdatetimeformat($data->created_at) : '';
            })

            ->editColumn('punchin_date', function ($data) {
                return isset($data->punchin_date) ? stringtodate($data->punchin_date) : '';
            })

            ->editColumn('punchout_date', function ($data) {
                return isset($data->punchout_date)
                    ? stringtodate($data->punchout_date)
                    : stringtodate($data->punchin_date);
            })

            ->addColumn('punchin', function ($row) {
                $image = !empty($row->punchin_image)
                    ? env('IMAGE_UPLOADS') . $row->punchin_image
                    : asset('assets/img/placeholder.jpg');

                return '<img src="'.$image.'" width="70" class="img-rounded imageDisplayModel" />';
            })

            ->addColumn('punchout', function ($row) {
                $image = !empty($row->punchout_image)
                    ? env('IMAGE_UPLOADS') . $row->punchout_image
                    : asset('assets/img/placeholder.jpg');

                return '<img src="'.$image.'" width="70" class="img-rounded imageDisplayModel" />';
            })

            ->rawColumns(['punchin', 'punchout']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(Attendance $model)
{
    return $model->with(['users' => function ($q) {
        $q->select('id', 'name', 'employee_codes');
    }])->latest();
}


    /**
     * Optional HTML builder.
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('attendances-table')
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
     * Get columns definition.
     */
    protected function getColumns()
    {
        return [
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),

            Column::make('id')->title('ID'),

            Column::make('employee_codes')
                ->title('Employee Code')
                ->searchable(true)
                ->orderable(false),

            Column::make('created_at')->title('Created At'),
            Column::make('updated_at')->title('Updated At'),
        ];
    }
}
