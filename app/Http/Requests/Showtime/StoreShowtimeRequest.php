<?php

namespace App\Http\Requests\Showtime;

use App\Models\Movie;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreShowtimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Basic field-level validation.
     *
     * These rules validate:
     * - required foreign keys exist
     * - start_time format is correct
     * - start_time is in the future
     *
     * NOTE:
     * Overlap validation CANNOT be done here because it depends
     * on calculated end_time (movie duration).
     */
    public function rules(): array
    {
        return [
            'movie_id'          => ['required', 'exists:movies,id'],
            'hall_id'           => ['required', 'exists:halls,id'],
            'start_time'        => ['required', 'date_format:Y-m-d H:i:s', 'after:now'],

            'prices'            => ['required', 'array'],
            'prices.regular'    => ['required', 'numeric', 'min:0','max:99999999.99'],
            'prices.vip'        => ['required', 'numeric', 'min:0','max:99999999.99'],
        ];
    }

    /**
     * Cross-field & business-rule validation.
     *
     * This method runs AFTER basic validation succeeds.
     * We use it to prevent overlapping showtimes in the same hall.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {

            // Extract validated inputs
            $movieId   = $this->input('movie_id');
            $hallId    = $this->input('hall_id');
            $startTime = Carbon::parse($this->input('start_time'));

            // Load the movie to determine its duration
            $movie = Movie::find($movieId);

            // Safety guard: if movie is missing, skip overlap check
            // (exists rule already covers this, but this avoids crashes)
            if (! $movie) {
                return;
            }

            // Calculate the end time of the new showtime
            $endTime = (clone $startTime)->addMinutes($movie->duration_minutes); // Create a copy of $startTime, then modify the copy —leave the original untouched

            /**
             * Overlap rule (core business logic):
             *
             * A showtime overlaps if:
             *   existing.start_time < new.end_time
             *   AND
             *   existing.end_time   > new.start_time
             *
             * If ANY record satisfies this → scheduling conflict
             */
            $hasOverlap = Showtime::where('hall_id', $hallId)
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime)
                ->exists();

            // If an overlap is found, reject the request
            if ($hasOverlap) {
                $validator->errors()->add(
                    'start_time',
                    'This hall already has a showtime during the selected time range.'
                );
            }
        });
    }
}
