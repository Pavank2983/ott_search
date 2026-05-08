<?php

namespace App\Services;

use App\Models\Content;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    public function __construct(
        private ElasticSearchService $elasticSearchService
    ) {}

    /**
     * Search OTT contents using Elasticsearch.
     */
    public function search(
        ?string $query,
        int $tenantId,
        int $perPage = 20
    ): array {

        $page = request()->integer('page', 1);

        $query = trim($query ?? '');

        $contentType = request('content_type');

        $releaseYear = request('release_year');

        $language = request('language');

        $minRating = request('min_rating');

        /*
        |--------------------------------------------------------------------------
        | Cache Key
        |--------------------------------------------------------------------------
        */

        $cacheKey = sprintf(
            'search:tenant:%s:q:%s:page:%s:per_page:%s:type:%s:year:%s:lang:%s:rating:%s',
            $tenantId,
            md5($query ?: 'browse'),
            $page,
            $perPage,
            $contentType ?: 'all',
            $releaseYear ?: 'all',
            $language ?: 'all',
            $minRating ?: 'all'
        );

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(5),

            function () use (
                $query,
                $tenantId,
                $perPage,
                $page
            ) {

                /*
                |--------------------------------------------------------------------------
                | Elasticsearch Search
                |--------------------------------------------------------------------------
                */

                $response = $this->elasticSearchService->search(
                    query: $query,
                    tenantId: $tenantId,
                    perPage: $perPage
                );

                $hits = $response['hits']['hits'] ?? [];

                $contentIds = collect($hits)
                    ->pluck('_id')
                    ->map(fn ($id) => (int) $id)
                    ->toArray();

                /*
                |--------------------------------------------------------------------------
                | Empty Result
                |--------------------------------------------------------------------------
                */

                if (empty($contentIds)) {

                    return [
                        'data' => [],
                        'total' => 0,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => 0,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Fetch Database Contents
                |--------------------------------------------------------------------------
                */

                $contents = Content::query()

                    ->select([
                        'id',
                        'tenant_id',
                        'title',
                        'description',
                        'poster_url',
                        'content_type',
                        'release_year',
                        'imdb_rating',
                    ])

                    ->with([
                        'actors:id,name',
                    ])

                    ->whereIn('id', $contentIds)

                    ->where('tenant_id', $tenantId)

                    ->where('status', 'published')

                    ->get()

                    /*
                    |--------------------------------------------------------------------------
                    | Preserve Elasticsearch Relevance Order
                    |--------------------------------------------------------------------------
                    */

                    ->sortBy(function ($content) use ($contentIds) {

                        return array_search(
                            $content->id,
                            $contentIds
                        );
                    })

                    ->values()

                    ->toArray();

                /*
                |--------------------------------------------------------------------------
                | Pagination
                |--------------------------------------------------------------------------
                */

                $total = $response['hits']['total']['value'] ?? 0;

                return [
                    'data' => $contents,

                    'total' => $total,

                    'per_page' => $perPage,

                    'current_page' => $page,

                    'last_page' => (int) ceil(
                        $total / $perPage
                    ),
                ];
            }
        );
    }
}