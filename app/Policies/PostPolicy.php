<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    // Quyền xem
    public function view(User $user, Post $post)
    {
        return $user->isAdmin() || $user->id === $post->user_id;
    }

    // Quyền tạo mới
    public function create(User $user)
    {
        return $user->isAdmin() || $user->role === UserRole::USER;
    }


    // Quyền cập nhật
    public function update(User $user, Post $post)
    {
        return $user->isAdmin() || $user->id === $post->user_id;
    }

    public function updateStatus(User $user, Post $post)
    {
        return $user->isAdmin;
    }
    // Quyền xoá
    public function delete(User $user, Post $post)
    {
        return $user->isAdmin() || $user->id === $post->user_id;
    }




    public function restore(User $user, Post $post): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return false;
    }
}
