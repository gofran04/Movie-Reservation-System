<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\Hall;

class CreateSeatService
{
    /**
     * Generate and persist all seats for a given hall based on its layout.
     *
     * Seats are created using alphabetic row labels (A, B, ..., Z, AA, AB, ...)
     * and numeric column numbers (1, 2, 3, ...).
     *
     * Example generated seats:
     *  - A1, A2, A3
     *  - B1, B2, B3
     *
     * Bulk insertion is used for performance reasons.
     */
    public static function createSeatsForHall(Hall $hall): void
    {
        $seats = [];

        // Loop through each logical row in the hall
        for ($row = 1; $row <= $hall->total_rows; $row++) {
            // Convert numeric row index to alphabetic label (e.g. 1 → A, 27 → AA)
            $rowLabel = self::numberToRowLabel($row);

            // Loop through each column in the current row
            for ($column = 1; $column <= $hall->total_columns; $column++) {
                $seats[] = [
                    'hall_id'       => $hall->id,
                    'row_number'    => $rowLabel,
                    'column_number' => $column,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        // Insert all generated seats in a single database query for efficiency
        Seat::insert($seats);
    }

    /**
     * Convert a 1-based row number into an alphabetic row label.
     *
     * This follows the same convention as Excel column naming:
     *  1  → A
     *  26 → Z
     *  27 → AA
     *  28 → AB
     *
     * @param int $number The row index (starting from 1)
     * @return string The corresponding alphabetic row label
     */
    private static function numberToRowLabel(int $number): string
    {
        $label = '';

        // Build the label from right to left using base-26 alphabetic logic
        while ($number > 0) {
            // Convert from 1-based index to 0-based for modulo calculation
            $number--;

            // Map remainder (0–25) to letters A–Z and prepend to the label
            $label = chr(65 + ($number % 26)) . $label;

            // Move to the next "digit" in base-26
            $number = intdiv($number, 26);
        }

        return $label;
    }
}