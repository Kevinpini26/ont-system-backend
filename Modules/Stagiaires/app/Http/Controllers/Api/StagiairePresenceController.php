<?php

namespace Modules\Stagiaires\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Modules\Stagiaires\Http\Requests\EnregistrerPresenceRequest;
use Modules\Stagiaires\Http\Resources\StagiairePresenceResource;
use Modules\Stagiaires\Models\Stagiaire;
use Modules\Stagiaires\Services\StagiaireCircuitService;

class StagiairePresenceController extends Controller
{
    public function __construct(private readonly StagiaireCircuitService $circuit) {}

    public function index(Stagiaire $stagiaire)
    {
        $this->authorize('gererPresence', Stagiaire::class);

        return StagiairePresenceResource::collection(
            $stagiaire->presences()->with('saisiPar')->orderByDesc('date')->get()
        );
    }

    public function store(EnregistrerPresenceRequest $request, Stagiaire $stagiaire)
    {
        $data = $request->validated();

        $presence = $this->circuit->enregistrerPresence(
            $stagiaire,
            $request->user(),
            Carbon::parse($data['date']),
            $data['heure_arrivee'] ?? null,
            $data['heure_depart'] ?? null,
        );

        return (new StagiairePresenceResource($presence->load('saisiPar')))->response()->setStatusCode(201);
    }

    public function destroy(Stagiaire $stagiaire, string $date)
    {
        $this->authorize('gererPresence', Stagiaire::class);

        $this->circuit->supprimerPresence($stagiaire, Carbon::parse($date));

        return response()->json(null, 204);
    }
}
