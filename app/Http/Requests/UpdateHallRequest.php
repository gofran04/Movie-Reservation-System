<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHallRequest extends FormRequest
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
            'name'          => ['sometimes','string','max:255'],
            'cinema_id'     => ['sometimes','exists:cinemas,id'],   
            'status'        => ['sometimes','in:active,inactive'],
            'total_rows'    => ['prohibited'],
            'total_columns' => ['prohibited'],
        ];
    }
}
