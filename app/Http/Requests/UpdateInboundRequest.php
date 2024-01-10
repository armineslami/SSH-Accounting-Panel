<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateInboundRequest extends FormRequest
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
            'username' => 'required|unique:inbounds,username,'.$this->id.'|string|min:3|max:255',
            'user_password' => 'required|string|min:5|max:255',
            'is_active' => 'required|numeric|in:0,1',
            'traffic_limit' => 'nullable|numeric|min:0|max:1000',
            'remaining_traffic' => 'nullable|numeric|min:0|max:1000',
            'max_login' => 'required|numeric|min:1|max:1000',
            'active_days' => 'nullable|numeric|min:0|max:3650',
            'server_ip' => 'required|ipv4|',
            'delete_from_old_server' => 'nullable|numeric|in:0,1'
        ];
    }
}
