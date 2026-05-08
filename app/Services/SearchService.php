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
        string $query,
        int $tenantId,
        int $perPage = 20
    ): array {

        $page = request()->integer('page', 1);

        $cacheKey = sprintf(
            'search:tenant:%s:q:%s:page:%s:per_page:%s:type:%s:year:%s:lang:%s:rating:%s',
            $tenantId,
            md5($query),
            $page,
            $perPage,
            request('content_type', 'all'),
            request('release_year', 'all'),
            request('language', 'all'),
            request('min_rating', 'all')
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

                if (empty($contentIds)) {
                    return [
                        'data' => [],
                        'total' => 0,
                        'per_page' => $perPage,
                        'current_page' => $page,
                    ];
                }

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

                    ->get()

                    ->sortBy(function ($content) use ($contentIds) {
                        return array_search(
                            $content->id,
                            $contentIds
                        );
                    })

                    ->values()

                    ->toArray();

                return [
                    'data' => $contents,

                    'total' => $response['hits']['total']['value'] ?? 0,

                    'per_page' => $perPage,

                    'current_page' => $page,

                    'last_page' => (int) ceil(
                        ($response['hits']['total']['value'] ?? 0)
                        / $perPage
                    ),
                ];
            }
        );
    }
}