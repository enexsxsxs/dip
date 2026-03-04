<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageEquipment
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canManageEquipment()) {
            abort(403, 'Доступ разрешён только администраторам и старшим медсёстрам.');
        }

        return $next($request);
    }
}
