<?php

namespace App\Services;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticSearchService
{
    private Client $client;

    private string $index = 'ott_contents';

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(config('elasticsearch.hosts'))
            ->build();
    }

    public function createIndex(): void
    {
        $exists = $this->client->indices()->exists([
            'index' => $this->index,
        ]);

        if ($exists->asBool()) {
            return;
        }

        $params = [
            'index' => $this->index,

            'body' => [
                'mappings' => [
                    'properties' => [

                        /*
                        |--------------------------------------------------------------------------
                        | Title
                        |--------------------------------------------------------------------------
                        | text     -> Full text search
                        | keyword  -> Exact title matching
                        |--------------------------------------------------------------------------
                        */

                        'title' => [
                            'type' => 'text',

                            'fields' => [
                                'keyword' => [
                                    'type' => 'keyword',
                                ],
                            ],
                        ],

                        'description' => [
                            'type' => 'text',
                        ],

                        'search_text' => [
                            'type' => 'text',
                        ],

                        'tenant_id' => [
                            'type' => 'integer',
                        ],

                        'content_type' => [
                            'type' => 'keyword',
                        ],

                        'release_year' => [
                            'type' => 'integer',
                        ],

                        'language' => [
                            'type' => 'keyword',
                        ],

                        'genres' => [
                            'type' => 'keyword',
                        ],

                        'actors' => [
                            'type' => 'text',
                        ],

                        'imdb_rating' => [
                            'type' => 'float',
                        ],
                    ],
                ],
            ],
        ];

        $this->client->indices()->create($params);
    }

    public function bulkIndex(array $documents): void
    {
        if (empty($documents)) {
            return;
        }

        $this->client->bulk([
            'body' => $documents,
        ]);
    }

    public function index(array $document, int $id): void
    {
        $this->client->index([
            'index' => $this->index,
            'id' => $id,
            'body' => $document,
        ]);
    }

    public function deleteIndex(): void
    {
        $exists = $this->client->indices()->exists([
            'index' => $this->index,
        ]);

        if ($exists->asBool()) {

            $this->client->indices()->delete([
                'index' => $this->index,
            ]);
        }
    }

    public function delete(int $id): void
    {
        $exists = $this->client->exists([
            'index' => $this->index,
            'id' => $id,
        ]);

        if (!$exists->asBool()) {
            return;
        }

        $this->client->delete([
            'index' => $this->index,
            'id' => $id,
        ]);
    }

    /**
     * Search OTT contents using Elasticsearch.
     */
    public function search(
        ?string $query,
        int $tenantId,
        int $perPage = 10
    ): array {

        $page = request()->integer('page', 1);

        $query = trim($query ?? '');

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters = [
            [
                'term' => [
                    'tenant_id' => $tenantId,
                ],
            ],
        ];

        if (
            request('content_type')
            && request('content_type') !== 'all'
        ) {

            $filters[] = [
                'term' => [
                    'content_type' => request('content_type'),
                ],
            ];
        }

        if (
            request('release_year')
            && request('release_year') !== 'all'
        ) {

            $filters[] = [
                'term' => [
                    'release_year' => (int) request('release_year'),
                ],
            ];
        }

        if (
            request('language')
            && request('language') !== 'all'
        ) {

            $filters[] = [
                'term' => [
                    'language' => request('language'),
                ],
            ];
        }

        if (
            request('min_rating')
            && request('min_rating') !== 'all'
        ) {

            $filters[] = [
                'range' => [
                    'imdb_rating' => [
                        'gte' => (float) request('min_rating'),
                    ],
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Search Query
        |--------------------------------------------------------------------------
        */

        if (!empty($query)) {

            $searchQuery = [
                'bool' => [

                    'must' => [
                        [
                            'bool' => [

                                'should' => [

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Exact Full Title Match
                                    |--------------------------------------------------------------------------
                                    */

                                    [
                                        'term' => [
                                            'title.keyword' => [
                                                'value' => $query,
                                                'boost' => 1000,
                                            ],
                                        ],
                                    ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Exact Phrase Match
                                    |--------------------------------------------------------------------------
                                    */

                                    [
                                        'match_phrase' => [
                                            'title' => [
                                                'query' => $query,
                                                'boost' => 300,
                                            ],
                                        ],
                                    ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Starts With Title
                                    |--------------------------------------------------------------------------
                                    */

                                    [
                                        'prefix' => [
                                            'title.keyword' => [
                                                'value' => strtolower($query),
                                                'boost' => 150,
                                            ],
                                        ],
                                    ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Phrase Prefix Match
                                    |--------------------------------------------------------------------------
                                    */

                                    [
                                        'match_phrase_prefix' => [
                                            'title' => [
                                                'query' => $query,
                                                'boost' => 100,
                                            ],
                                        ],
                                    ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Strong AND Match
                                    |--------------------------------------------------------------------------
                                    */

                                    [
                                        'match' => [
                                            'title' => [
                                                'query' => $query,
                                                'operator' => 'and',
                                                'boost' => 50,
                                            ],
                                        ],
                                    ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Actor Match
                                    |--------------------------------------------------------------------------
                                    */

                                    [
                                        'match' => [
                                            'actors' => [
                                                'query' => $query,
                                                'operator' => 'and',
                                                'boost' => 40,
                                            ],
                                        ],
                                    ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | General Search
                                    |--------------------------------------------------------------------------
                                    */

                                    [
                                        'multi_match' => [
                                            'query' => $query,

                                            'fields' => [
                                                'title^20',
                                                'actors^15',
                                                'search_text^5',
                                                'description',
                                                'genres',
                                                'language',
                                            ],

                                            'type' => 'best_fields',

                                            'operator' => 'and',

                                            'fuzziness' => 1,
                                        ],
                                    ],

                                ],

                                'minimum_should_match' => 1,
                            ],
                        ],
                    ],

                    'filter' => $filters,
                ],
            ];

        } else {

            $searchQuery = [
                'bool' => [

                    'must' => [
                        [
                            'match_all' => (object) [],
                        ],
                    ],

                    'filter' => $filters,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Final Elasticsearch Query
        |--------------------------------------------------------------------------
        */

        $params = [
            'index' => $this->index,

            'body' => [

                /*
                |--------------------------------------------------------------------------
                | Real Total Count > 10k
                |--------------------------------------------------------------------------
                */

                'track_total_hits' => true,

                /*
                |--------------------------------------------------------------------------
                | Pagination
                |--------------------------------------------------------------------------
                */

                'from' => ($page - 1) * $perPage,

                'size' => $perPage,

                /*
                |--------------------------------------------------------------------------
                | Query
                |--------------------------------------------------------------------------
                */

                'query' => $searchQuery,

                /*
                |--------------------------------------------------------------------------
                | Sorting
                |--------------------------------------------------------------------------
                */

                'sort' => [

                    /*
                    |--------------------------------------------------------------------------
                    | Search Relevance
                    |--------------------------------------------------------------------------
                    */

                    ...(
                        !empty($query)
                        ? [
                            [
                                '_score' => [
                                    'order' => 'desc',
                                ],
                            ],
                        ]
                        : []
                    ),

                    /*
                    |--------------------------------------------------------------------------
                    | Rating
                    |--------------------------------------------------------------------------
                    */

                    [
                        'imdb_rating' => [
                            'order' => 'desc',
                        ],
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | Latest Content
                    |--------------------------------------------------------------------------
                    */

                    [
                        'release_year' => [
                            'order' => 'desc',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->client->search($params);

        return $response->asArray();
    }

    /**
     * Get search suggestions.
     */
    public function getSuggestions(
        string $query,
        int $tenantId,
        int $limit = 8
    ): array {

        $query = trim($query);

        if (empty($query)) {
            return [];
        }

        $params = [
            'index' => $this->index,

            'body' => [

                'size' => $limit,

                '_source' => [
                    'title',
                ],

                'query' => [
                    'bool' => [

                        'should' => [

                            /*
                            |--------------------------------------------------------------------------
                            | Exact Full Title Match
                            |--------------------------------------------------------------------------
                            */

                            [
                                'term' => [
                                    'title.keyword' => [
                                        'value' => $query,
                                        'boost' => 1000,
                                    ],
                                ],
                            ],

                            /*
                            |--------------------------------------------------------------------------
                            | Starts With Query
                            |--------------------------------------------------------------------------
                            */

                            [
                                'prefix' => [
                                    'title.keyword' => [
                                        'value' => strtolower($query),
                                        'boost' => 300,
                                    ],
                                ],
                            ],

                            /*
                            |--------------------------------------------------------------------------
                            | Exact Phrase Match
                            |--------------------------------------------------------------------------
                            */

                            [
                                'match_phrase' => [
                                    'title' => [
                                        'query' => $query,
                                        'boost' => 200,
                                    ],
                                ],
                            ],

                            /*
                            |--------------------------------------------------------------------------
                            | Phrase Prefix Match
                            |--------------------------------------------------------------------------
                            */

                            [
                                'match_phrase_prefix' => [
                                    'title' => [
                                        'query' => $query,
                                        'boost' => 100,
                                    ],
                                ],
                            ],

                            /*
                            |--------------------------------------------------------------------------
                            | Standard Match
                            |--------------------------------------------------------------------------
                            */

                            [
                                'match' => [
                                    'title' => [
                                        'query' => $query,
                                        'operator' => 'and',
                                        'fuzziness' => 'AUTO',
                                        'boost' => 20,
                                    ],
                                ],
                            ],

                        ],

                        'minimum_should_match' => 1,

                        'filter' => [
                            [
                                'term' => [
                                    'tenant_id' => $tenantId,
                                ],
                            ],
                        ],
                    ],
                ],

                /*
                |--------------------------------------------------------------------------
                | Final Sorting
                |--------------------------------------------------------------------------
                */

                'sort' => [

                    [
                        '_score' => [
                            'order' => 'desc',
                        ],
                    ],

                    [
                        'imdb_rating' => [
                            'order' => 'desc',
                        ],
                    ],

                ],
            ],
        ];

        $response = $this->client
            ->search($params)
            ->asArray();

        $hits = $response['hits']['hits'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Unique Suggestions
        |--------------------------------------------------------------------------
        */

        return collect($hits)

            ->pluck('_source.title')

            ->filter()

            ->unique()

            ->values()

            ->take($limit)

            ->toArray();
    }
}