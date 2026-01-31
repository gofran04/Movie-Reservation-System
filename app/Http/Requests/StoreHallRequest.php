<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHallRequest extends FormRequest
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
            'name'          => ['required', 'string', 'max:255'],
            'cinema_id'     => ['required', 'exists:cinemas,id'],
            'total_rows'    => ['required', 'integer', 'min:1'],
            'total_columns' => ['required', 'integer', 'min:1'],
            'status'        => ['required', 'in:active,inactive'],
        ];
    }
}
