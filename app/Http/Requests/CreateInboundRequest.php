<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateInboundRequest extends FormRequest
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
            'username' => 'required|regex:/^[a-z0-9]+$/|unique:inbounds,username|string|min:3|max:255',
            'user_password' => 'required|string|min:5|max:255',
            'is_active' => 'required|numeric|in:0,1',
            'traffic_limit' => 'nullable|numeric|min:0|max:1000',
            'max_login' => 'required|numeric|min:1|max:1000',
            'active_days' => 'nullable|numeric|min:0|max:3650',
            'server_ip' => 'required|ipv4|',
        ];
    }

    public function messages(): array
    {
        return [
          'username.regex' => "The :attribute filed must only contain lowercase characters and numbers."
        ];
    }
}
