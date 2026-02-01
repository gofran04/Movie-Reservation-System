<?php

namespace App\Http\Requests\Seat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeatRequest extends FormRequest
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
            'hall_id'       => ['prohibited'], // the hall_id is immutable
            'type'          => ['sometimes','in:regular,vip'],
            'status'        => ['sometimes','in:available,out_of_service'],
            'row_number'    => ['prohibited'],// the row_number is immutable
            'column_number' => ['prohibited'],// the column_number is immutable
        ];
    }
}
