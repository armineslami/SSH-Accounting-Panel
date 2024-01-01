<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateServerRequest extends FormRequest
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
            'name' => 'bail|required|string|min:1|max:255',
            'username' => 'bail|required|string|min:3|max:255|in:root',
            'address' => 'bail|required|unique:servers,address,'.$this->id.'|ipv4|',
            'port' => 'bail|required|numeric|min:1|max:65535',
//            'udp_port' => 'bail|required|numeric|min:1|max:65535',
        ];
    }

    public function messages(): array
    {
        return [
            'username.in' => "Username can only be root"
        ];
    }
}
