<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'thumbnail' => $this->getFirstMediaUrl('thumbnails') ?? $this->thumbnail,
            'title' => $this->title,
            'description' => Str::limit($this->description, 50),
            'publish_date' => optional($this->publish_date)->format('d/m/Y'),
            'status' => $this->status->label(),
        ];
    }
}
