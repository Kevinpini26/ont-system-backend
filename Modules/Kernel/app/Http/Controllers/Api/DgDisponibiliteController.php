<?php

namespace Modules\Kernel\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Kernel\Enums\Poste;
use Modules\Kernel\Enums\UserRole;
use Modules\Kernel\Support\DgDisponibilite;

/**
 * Disponibilité de la DG pour le circuit courrier — voir DgDisponibilite et
 * CourrierCircuitService::rendreAvisDg(). Endpoint lisible par tout
 * utilisateur authentifié (le poste DGA doit savoir s'il est en intérim),
 * modifiable seulement par la DG elle-même ou un administrateur.
 */
class DgDisponibiliteController extends Controller
{
    public function show()
    {
        return response()->json(['disponible' => DgDisponibilite::estDisponible()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        abort_unless($user->role === UserRole::ADMINISTRATEUR || $user->poste === Poste::DG, 403);

        $data = $request->validate(['disponible' => ['required', 'boolean']]);

        DgDisponibilite::definir($data['disponible']);

        return response()->json(['disponible' => DgDisponibilite::estDisponible()]);
    }
}
