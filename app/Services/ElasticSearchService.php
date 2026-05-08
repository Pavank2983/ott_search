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

        if ($exists) {
            return;
        }

        $params = [
            'index' => $this->index,
            'body' => [
                'mappings' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                        ],
                        'description' => [
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

    public function search(
        string $query,
        int $tenantId,
        int $perPage = 10
    ): array {

        $page = request()->integer('page', 1);

        $filters = [
            [
                'term' => [
                    'tenant_id' => $tenantId,
                ],
            ],
        ];

        if (request('content_type')) {
            $filters[] = [
                'term' => [
                    'content_type' => request('content_type'),
                ],
            ];
        }

        if (request('release_year')) {
            $filters[] = [
                'term' => [
                    'release_year' => (int) request('release_year'),
                ],
            ];
        }

        if (request('min_rating')) {
            $filters[] = [
                'range' => [
                    'imdb_rating' => [
                        'gte' => (float) request('min_rating'),
                    ],
                ],
            ];
        }

        $params = [
            'index' => $this->index,
            'body' => [
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'multi_match' => [
                                    'query' => $query,
                                    'fields' => [
                                        'title^5',
                                        'description',
                                    ],
                                    'fuzziness' => 'AUTO',
                                ],
                            ],
                        ],
                        'filter' => $filters,
                    ],
                ],
                'sort' => [
                    [
                        '_score' => [
                            'order' => 'desc',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->client->search($params);

        return $response->asArray();
    }
}