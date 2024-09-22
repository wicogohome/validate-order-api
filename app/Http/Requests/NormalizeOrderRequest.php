<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NormalizeOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|string|max:50', // max:50 避免輸入內容過長導致風險，可視欄位實際情況調整長度
            'name' => 'required|string|max:50',
            'address' => 'required|array:city,district,street',
            'address.city' => 'required|string|max:50',
            'address.district' => 'required|string|max:50',
            'address.street' => 'required|string|max:50',
            'price' => 'required|numeric',
            'currency' => 'required|string|size:3',
        ];
    }
}
