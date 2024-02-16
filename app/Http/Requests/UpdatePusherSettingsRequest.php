<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePusherSettingsRequest extends FormRequest
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
            'pusher_id' => 'nullable|string|max:255',
            'pusher_key' => 'nullable|string|max:255',
            'pusher_secret' => 'nullable|string|max:255',
            'pusher_cluster' => 'nullable|string|max:10',
            'pusher_port' => 'nullable|numeric|min:1|max:65535',
        ];
    }
}
