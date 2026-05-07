<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\ContentResource;
use App\Services\SearchService;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Search OTT contents.
     */
    public function index(SearchRequest $request)
    {
        $results = $this->searchService->search(
            query: $request->validated('q'),
            tenantId: $request->validated('tenant_id'),
            perPage: (int) $request->validated('per_page', 20)
        );

        return response()->json($results);
    }
}