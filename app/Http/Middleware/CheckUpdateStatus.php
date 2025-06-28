<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUpdateStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Giả sử bạn dùng cột is_admin hoặc role
        if (!$user || !$user->is_admin) {
            abort(403, 'Bạn không có quyền thay đổi trạng thái.');
        }

        return $next($request);
    }
}
