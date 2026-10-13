<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Gate;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('product_create') || Gate::denies('product_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return true;
    }

    /**
     * The fields the Odoo product sync fills, plus the image (kept in FieldKonnect only).
     */
    public function rules()
    {
        return [
            'product_name'   => 'required|string|min:2|max:250',
            'category_id'    => 'required|numeric|exists:categories,id',
            'subcategory_id' => 'required|numeric|exists:subcategories,id',
            'unit_id'        => 'nullable|numeric|exists:unit_measures,id',
            'mrp'            => 'required|numeric|min:0',
            'gst'            => 'nullable|numeric|min:0|max:90',
            'active'         => 'required|in:Y,N',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}
