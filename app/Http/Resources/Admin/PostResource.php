<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'title' => $this->title,
            'email' => $this->user_email ?? ($this->user->email ?? '-'),
            'status' => $this->status->label(),
            'created_at' => $this->created_at->format('d/m/Y'),
            'slug' => $this->slug,
        ];
    }
}
