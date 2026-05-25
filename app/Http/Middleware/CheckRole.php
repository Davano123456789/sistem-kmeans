<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect('login');
        }

        $userRole = strtolower($request->user()->role);

        // Jika role user mengandung kata 'dosen' (misalnya 'dosen' atau 'dosen pembimbing')
        if (str_contains($userRole, 'dosen')) {
            $allowedRoutes = [
                'kmeans.riwayat.index',
                'kmeans.riwayat.show',
                'kmeans.riwayat.export',
                'logout'
            ];

            $currentRouteName = $request->route() ? $request->route()->getName() : null;

            if (!in_array($currentRouteName, $allowedRoutes)) {
                // Jika dosen mencoba mengakses root `/`, redirect saja ke halaman hasil-cluster
                if ($request->is('/')) {
                    return redirect()->route('kmeans.riwayat.index');
                }
                
                // Jika mengakses halaman lain, tolak dan kembalikan ke halaman hasil cluster
                return redirect()->route('kmeans.riwayat.index')->with('error', 'Akses ditolak! Akun Dosen hanya diizinkan untuk melihat Hasil Cluster (Read-Only).');
            }
        }

        return $next($request);
    }
}
