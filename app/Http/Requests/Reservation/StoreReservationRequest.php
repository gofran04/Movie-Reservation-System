<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Showtime;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $showtimeId = $this->input('showtime_id');

        if ($showtimeId) {
            $showtime = Showtime::find($showtimeId);
            if($showtime->start_time < now()){
                abort(422, __('You cannot reserve a showtime that has already started'));
            }
        }
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
            'showtime_id' => ['required', 'integer', 'exists:showtimes,id'],
            'seat_ids'    => ['required', 'array', 'min:1'],
            'seat_ids.*'  => ['integer', 'exists:seats,id'],
        ];
    }
}
