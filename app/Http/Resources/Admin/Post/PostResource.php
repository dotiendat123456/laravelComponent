<?php

namespace App\Http\Resources\Admin\Post;

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
        //nếu user chưa được eager loading thì không được load
        return [

            'id' => $this->id,
            'title' => $this->title,
            // 'email' => $this->user->email , //this->user
            'email' => $this->user,
            'status' => $this->status->label(),
            'created_at' => $this->created_at->format('d/m/Y'),
            'slug' => $this->slug,
        ];
    }
}
