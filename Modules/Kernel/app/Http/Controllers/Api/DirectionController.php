<?php

namespace Modules\Kernel\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Kernel\Http\Requests\StoreDirectionRequest;
use Modules\Kernel\Http\Requests\UpdateDirectionRequest;
use Modules\Kernel\Http\Resources\DirectionResource;
use Modules\Kernel\Models\Direction;

class DirectionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Direction::class);

        return DirectionResource::collection(Direction::query()->orderBy('code')->get());
    }

    public function show(Direction $direction)
    {
        $this->authorize('view', $direction);

        return new DirectionResource($direction);
    }

    public function store(StoreDirectionRequest $request)
    {
        $direction = Direction::query()->create([
            'actif' => true,
            ...$request->validated(),
        ]);

        return (new DirectionResource($direction))->response()->setStatusCode(201);
    }

    public function update(UpdateDirectionRequest $request, Direction $direction)
    {
        $direction->update($request->validated());

        return new DirectionResource($direction);
    }

    public function destroy(Direction $direction)
    {
        $this->authorize('delete', $direction);

        $direction->delete();

        return response()->json(null, 204);
    }
}
