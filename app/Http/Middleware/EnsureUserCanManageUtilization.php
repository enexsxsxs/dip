<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageUtilization
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canManageUtilization()) {
            abort(403, 'Утилизацию могут отмечать только администратор или ответственный за утилизацию.');
        }

        return $next($request);
    }
}
