<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateServerRequest extends FormRequest
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
            'password' => 'bail|required|string|min:5|max:255',
            'address' => 'bail|required|unique:servers,address|ipv4|',
            'port' => 'bail|required|numeric|min:1|max:65535',
            'udp_port' => 'bail|required|numeric|min:1|max:65535',
        ];
    }
}
