<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Gate;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        $ability = $this->isMethod('post') ? 'order_create' : 'order_edit';

        abort_if(Gate::denies($ability), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }


    public function rules()
    {
        $rules = [];
        switch($this) {
            case !empty($this->id) :
                $rules = [
                    'customer_type_id' => 'required|integer|exists:customer_types,id',
                    'buyer_id'      => 'required|integer|exists:customers,id',
                    'seller_id'     => 'nullable|integer|exists:customers,id',
                    'total_qty'     => 'nullable|numeric',
                    'shipped_qty'   => 'nullable|numeric',
                    'orderno'       => 'nullable|min:2|max:100|string|regex:/[a-zA-Z0-9\s]+/',
                    'order_date'    => 'nullable|date_format:Y-m-d',
                    'completed_date'=> 'nullable|date_format:Y-m-d',
                    'total_gst'     => 'nullable|numeric',
                    'total_discount'=> 'nullable|numeric',
                    'extra_discount'=> 'nullable|numeric',
                    'extra_discount_amount' => 'nullable|numeric',
                    'sub_total'     => 'nullable|numeric',
                    'grand_total'   => 'nullable|numeric',
                    'created_by'    => 'nullable|numeric|exists:users,id',
                    'status_id'     => 'nullable|numeric|exists:statuses,id',
                ];
                break;
            default :
                $rules = [
                    'customer_type_id' => 'required|integer|exists:customer_types,id',
                    'buyer_id'      => 'required|integer|exists:customers,id',
                    'seller_id'     => 'nullable|integer|exists:customers,id',
                    'total_qty'     => 'nullable|numeric',
                    'shipped_qty'   => 'nullable|numeric',
                    'orderno'       => 'nullable|min:2|max:100|string|regex:/[a-zA-Z0-9\s]+/',
                    'order_date'    => 'nullable|date_format:Y-m-d',
                    'completed_date'=> 'nullable|date_format:Y-m-d',
                    'total_gst'     => 'nullable|numeric',
                    'total_discount'=> 'nullable|numeric',
                    'extra_discount'=> 'nullable|numeric',
                    'extra_discount_amount' => 'nullable|numeric',
                    'sub_total'     => 'nullable|numeric',
                    'grand_total'   => 'nullable|numeric',
                    'created_by'    => 'nullable|numeric|exists:users,id',
                    'status_id'     => 'nullable|numeric|exists:statuses,id',
                ];
                break;
        }

        $rules['orderdetail'] = 'required|array|min:1';
        $rules['orderdetail.*.product_id'] = 'required|integer|exists:products,id';
        $rules['orderdetail.*.quantity'] = 'required|numeric|min:0.01';
        $rules['orderdetail.*.price'] = 'required|numeric|min:0';

        return $rules;
    }
}
