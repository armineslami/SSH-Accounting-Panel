<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateInboundSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'inbound_traffic_limit' => 'nullable|numeric|min:0|max:1000',
            'inbound_active_days' => 'nullable|numeric|min:0|max:365',
            'inbound_max_login' => 'required|numeric|min:0|max:1000'
        ];
    }
}
