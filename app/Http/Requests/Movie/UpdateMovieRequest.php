<?php

namespace App\Http\Requests\Movie;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMovieRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title'             => ['required', 'string'],
            'description'       => ['required', 'string'],
            'release_year'      => ['required', 'integer','min:1888','max:'. now()->year], // 1888 is considered the birth year of cinema.
            'rating'            => ['nullable','numeric','min:0','max:10'],
            'duration_minutes'  => ['required', 'integer', 'min:1', 'max:500'],
            'status'            => ['required', 'in:active,inactive'],
        ];
    }
}
