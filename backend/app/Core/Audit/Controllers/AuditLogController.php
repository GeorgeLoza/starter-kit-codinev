<?php

declare(strict_types=1);

namespace App\Core\Audit\Controllers;

use App\Core\Audit\Models\AuditLog;
use App\Core\Audit\Resources\AuditLogResource;
use App\Core\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuditLogController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 20);

        $logs = AuditLog::query()
            ->with('user')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->string('action')))
            ->when($request->filled('model_type'), fn ($q) => $q->where('model_type', $request->string('model_type')))
            ->when($request->filled('model_id'), fn ($q) => $q->where('model_id', $request->integer('model_id')))
            ->when($request->filled('from'), fn ($q) => $q->where('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('created_at', '<=', $request->date('to')))
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return ApiResponse::paginated(
            $logs,
            'Audit logs obtenidos',
            fn ($log) => (new AuditLogResource($log))->resolve()
        );
    }

    public function show(AuditLog $auditLog): JsonResponse
    {
        return ApiResponse::success(
            new AuditLogResource($auditLog->load('user')),
            'Audit log obtenido'
        );
    }
}