<?php

namespace Modules\Kernel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint une route à un ou plusieurs postes précis du circuit courrier
 * central (ex: file d'attente Protocole, file DGA...).
 */
class EnsureUserHasPoste
{
    public function handle(Request $request, Closure $next, string ...$postes): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->poste?->value, $postes, strict: true)) {
            abort(403, "Vous n'occupez pas le poste requis pour accéder à cette ressource.");
        }

        return $next($request);
    }
}
