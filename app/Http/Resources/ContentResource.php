<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'poster_url' => $this->poster_url,
            'content_type' => $this->content_type,
            'release_year' => $this->release_year,
            'imdb_rating' => $this->imdb_rating,

            'actors' => $this->actors->map(function ($actor) {
                return [
                    'id' => $actor->id,
                    'name' => $actor->name,
                ];
            }),
        ];
    }
}