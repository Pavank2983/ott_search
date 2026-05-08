<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ElasticSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchSuggestionController extends Controller
{
    public function __construct(
        private ElasticSearchService $elasticSearchService
    ) {}

    /**
     * Get search suggestions.
     */
    public function index(
        Request $request
    ): JsonResponse {

        $query = trim(
            $request->string('q')->toString()
        );

        $tenantId = (int) $request->integer(
            'tenant_id',
            1
        );

        /*
        |--------------------------------------------------------------------------
        | Minimum Query Length
        |--------------------------------------------------------------------------
        */

        if (strlen($query) < 2) {

            return response()->json([]);
        }

        $suggestions =
            $this->elasticSearchService
                ->getSuggestions(
                    query: $query,
                    tenantId: $tenantId
                );

        return response()->json(
            $suggestions
        );
    }
}