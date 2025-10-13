<?php

namespace App\Http\Requests\voucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVoucherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'company_id'   => 'required|integer|exists:companies,id',
            'voucher_type' => 'required|in:JV,CPV,BPV,CRV,BRV',
            'date'         => 'required|date',
            'number'       => 'required|string|max:50',
            'memo'         => 'nullable|string|max:255',
            "payment_method" => 'nullable|in:cash,cheque,bank,card',
            "check_number" => 'nullable|string|max:50',
            "bank_reference" => 'nullable|string|max:50',
            "deposit_slip" => 'nullable|string|max:50',
            "cashier_info" => 'nullable|string|max:50',
            'lines'                 => 'required|array|min:1',
            'lines.*.account_id'    => 'required|integer|exists:chart_of_accounts,id',
            'lines.*.debit'         => 'nullable|numeric|min:0|required_without:lines.*.credit',
            'lines.*.credit'        => 'nullable|numeric|min:0|required_without:lines.*.debit',
            'lines.*.memo'          => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('lines', []) as $index => $line) {
                $debit  = $line['debit'] ?? 0;
                $credit = $line['credit'] ?? 0;
                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add("lines.$index.credit", "Only debit or credit can be greater than 0, not both.");
                }
            }
        });
    }
}
