<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('id')) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = User::find($request->session()->get('id'));

        if (!$user) {
            $request->session()->invalidate();

            return redirect()->route('login')
                ->with('error', 'Tài khoản không tồn tại.');
        }

        if ($user->role !== 'user') {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $response = $next($request);

        // Không cho trình duyệt cache trang riêng tư
        $response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0'
        );

        $response->headers->set('Pragma', 'no-cache');

        return $response;

    }
}
