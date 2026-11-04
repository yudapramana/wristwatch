<?php

namespace App\Http\Middleware;

use App\Support\ResultAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Melindungi endpoint Hasil Cabang dengan gerbang password yang sama dengan Klasemen.
 * Bila standing_using_password = 0, request diteruskan tanpa token.
 */
class EnsureResultsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $cfg = ResultAccess::settings();

        if ($cfg['using_password'] && !ResultAccess::hasValidToken($request, $cfg['password'])) {
            return ResultAccess::lockedResponse();
        }

        return $next($request);
    }
}