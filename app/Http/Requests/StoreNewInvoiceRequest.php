<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class StoreNewInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        abort_if(
            Gate::denies('new_invoice_create'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'secondary_customer_id' => 'required|integer|exists:secondary_customers,id',
            'invoice_number' => 'required|string|min:1|max:100|unique:new_invoices,invoice_number',
            'invoice_date' => 'required|date_format:Y-m-d',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'points' => 'nullable|numeric|min:0|max:999999999.99',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpeg,jpg,png,gif,pdf|max:10240', // 10MB max per file
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'secondary_customer_id.required' => 'Customer is required.',
            'secondary_customer_id.exists' => 'The selected customer is invalid.',
            'invoice_number.required' => 'Invoice number is required.',
            'invoice_number.unique' => 'This invoice number already exists.',
            'invoice_date.required' => 'Invoice date is required.',
            'invoice_date.date_format' => 'Invoice date must be in format YYYY-MM-DD.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be greater than 0.',
            'points.numeric' => 'Points must be a valid number.',
            'points.min' => 'Points cannot be negative.',
            'attachments.array' => 'Attachments must be an array of files.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'Attachments must be images (JPEG, PNG, GIF) or PDF files.',
            'attachments.*.max' => 'Each attachment must not exceed 10MB.',
        ];
    }
}
