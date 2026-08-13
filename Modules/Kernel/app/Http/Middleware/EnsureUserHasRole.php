<?php

namespace Modules\Kernel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role?->value, $roles, strict: true)) {
            abort(403, "Vous n'avez pas le rôle requis pour accéder à cette ressource.");
        }

        return $next($request);
    }
}
