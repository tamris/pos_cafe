<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? Auth::user();

        // Cek apakah user login DAN role-nya admin atau owner
        if ($user && in_array($user->role, ['admin', 'owner'])) {
            return $next($request);
        }

        // Jika request berasal dari API atau mengharapkan JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Fitur ini hanya dapat diakses oleh Admin / Owner.',
            ], 403);
        }

        // Kalau bukan admin/owner via Web, tendang balik atau kasih error 403
        abort(403, 'AKSES DITOLAK. Halaman ini khusus Admin/Owner.');
    }
}