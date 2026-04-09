<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotAccountant
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAccountant()) {
            abort(403, 'Бухгалтеру доступен только список оборудования и присвоение инвентарного номера.');
        }

        return $next($request);
    }
}
