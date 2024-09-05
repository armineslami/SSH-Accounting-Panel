<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAppSettingsRequest extends FormRequest
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
            "app_inbound_bandwidth_check_interval" => "required|numeric|in:30,60,360,1440",
            "app_update_check_interval" => "required|string|in:day,week,month,never",
            "app_paginate_number" => "required|numeric|min:0|max:100"
        ];
    }
}
