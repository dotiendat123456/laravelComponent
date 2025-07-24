<?php

namespace App\Http\Resources\User;

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
            'thumbnail' => $this->thumbnail,
            'title' => Str::limit($this->title, 50),
            'description' => Str::limit($this->description, 50),
            // 'publish_date' => $this->publish_date->format('d/m/Y'),
            'publish_date' => format_date($this->publish_date),
            'status' => $this->status->label(),
        ];
    }
}
