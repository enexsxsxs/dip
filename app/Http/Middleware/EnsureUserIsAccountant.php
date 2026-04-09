<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAccountant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAccountant()) {
            abort(403, 'Доступ разрешён только бухгалтеру.');
        }

        return $next($request);
    }
}
