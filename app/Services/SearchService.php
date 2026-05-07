<?php

namespace App\Services;

use App\Models\Content;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchService
{
    /**
     * Search OTT contents.
     */
    public function search(
        string $query,
        int $tenantId,
        int $perPage = 20
    ) {
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
                $perPage
            ) {

                $results = Content::query()

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
                        'actors:id,name'
                    ])

                    ->when(
                        request('content_type'),
                        fn ($q) => $q->where(
                            'content_type',
                            request('content_type')
                        )
                    )

                    ->when(
                        request('release_year'),
                        fn ($q) => $q->where(
                            'release_year',
                            request('release_year')
                        )
                    )

                    ->when(
                        request('language'),
                        fn ($q) => $q->where(
                            'language',
                            request('language')
                        )
                    )

                    ->when(
                        request('min_rating'),
                        fn ($q) => $q->where(
                            'imdb_rating',
                            '>=',
                            request('min_rating')
                        )
                    )

                    ->where('tenant_id', $tenantId)

                    ->where('status', 'published')

                    ->whereRaw(
                        'search_vector @@ plainto_tsquery(\'english\', ?)',
                        [$query]
                    )

                    ->orderByRaw(
                        'ts_rank(search_vector, plainto_tsquery(\'english\', ?)) DESC',
                        [$query]
                    )

                    ->paginate($perPage);

                return json_decode(
                    json_encode($results),
                    true
                );
            }
        );
    }
}