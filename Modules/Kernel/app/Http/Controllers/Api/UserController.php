<?php

namespace Modules\Kernel\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Kernel\Http\Requests\StoreUserRequest;
use Modules\Kernel\Http\Requests\UpdateUserRequest;
use Modules\Kernel\Http\Resources\UserResource;
use Modules\Kernel\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        return UserResource::collection(
            User::query()->with('direction')->orderBy('name')->paginate(20)
        );
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return new UserResource($user->load('direction'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $user = User::query()->create([
            ...$request->validated(),
            'password' => bcrypt($request->validated()['password']),
        ]);

        return (new UserResource($user->load('direction')))->response()->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        return new UserResource($user->load('direction'));
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * Révoque tous les jetons Sanctum d'un compte : à utiliser en cas de
     * compromission suspectée (appareil perdu, doute sur les identifiants).
     */
    public function revoquerJetons(User $user)
    {
        $this->authorize('update', $user);

        $user->tokens()->delete();

        return response()->json(['message' => 'Jetons révoqués avec succès.']);
    }
}
