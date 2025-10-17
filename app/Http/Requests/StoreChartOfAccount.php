<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChartOfAccount extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'                => 'required|string|max:255',
            'code'                => 'required|string|max:50',
            'account_sub_type_id' => 'required|exists:chart_of_account_sub_types,id', // Ensure chart_of_account_sub_type_id exists in the chart_of_account_sub_types table
            'parent_id'           => 'nullable|exists:chart_of_accounts,id|different:id', // Ensure parent_id is different from the current id (self-referencing) //|different:id,account_sub_type_id,account_sub_type_id,parent_id
            'description'         => 'nullable|string',
        ];
    }
}
