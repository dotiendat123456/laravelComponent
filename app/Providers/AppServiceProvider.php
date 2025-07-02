<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Post;
use App\Models\User;
use App\Observers\PostObserver;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Post::observe(PostObserver::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }

    // protected $policies = [
    //     Post::class => PostPolicy::class,
    // ];
}
