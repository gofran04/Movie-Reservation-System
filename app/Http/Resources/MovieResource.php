<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->description,
            'release_year'     => $this->release_year,
            'rating'           => $this->rating,
            'duration_minutes' => $this->duration_minutes,            
            'poster'           => [
                                    'original' => $this->getFirstMediaUrl('poster'),
                                    'preview'  => $this->getFirstMediaUrl('poster', 'preview'),
                                ],
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
