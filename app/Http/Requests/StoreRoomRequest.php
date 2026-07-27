<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Izinkan user yang sedang login menggunakan request ini
    }

    public function rules(): array
    {
        return [
            'topic' => 'required|string|max:255',
            'mode' => 'required|in:debate,discussion',
            'max_rounds' => 'required|integer|min:1|max:10'
        ];
    }
}