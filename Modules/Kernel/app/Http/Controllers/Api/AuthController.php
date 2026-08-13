<?php

namespace Modules\Kernel\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Kernel\Contracts\AuditLogger;
use Modules\Kernel\Http\Requests\LoginRequest;
use Modules\Kernel\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            // On ne journalise jamais le mot de passe fourni, uniquement
            // l'e-mail tenté, utile pour détecter un bruteforce ciblé.
            $this->audit->enregistrer('auth.echec_connexion', contexte: ['email' => $credentials['email']]);

            throw ValidationException::withMessages([
                'email' => ["Les identifiants fournis sont incorrects."],
            ]);
        }

        /** @var \Modules\Kernel\Models\User $user */
        $user = Auth::user();

        $expiration = config('sanctum.expiration');
        $token = $user->createToken(
            $credentials['device_name'] ?? 'api',
            expiresAt: $expiration ? Carbon::now()->addMinutes((int) $expiration) : null,
        )->plainTextToken;

        $this->audit->enregistrer('auth.connexion', $user, $user);

        return response()->json([
            'user' => new UserResource($user->load('direction')),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $this->audit->enregistrer('auth.deconnexion', $request->user(), $request->user());

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user()->load('direction'));
    }
}
