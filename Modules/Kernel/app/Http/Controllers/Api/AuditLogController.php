<?php

namespace Modules\Kernel\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Kernel\Http\Resources\AuditLogResource;
use Modules\Kernel\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::query()->with('utilisateur')->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('depuis')) {
            $query->whereDate('created_at', '>=', $request->date('depuis'));
        }

        return AuditLogResource::collection($query->paginate(50));
    }
}
