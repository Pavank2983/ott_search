<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    /**
     * List all tenants.
     */
    public function index(): JsonResponse
    {
        $tenants = Tenant::query()

            ->select([
                'id',
                'name',
            ])

            ->orderBy('name')

            ->get();

        return response()->json($tenants);
    }
}