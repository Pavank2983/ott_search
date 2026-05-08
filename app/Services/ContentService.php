<?php

namespace App\Services;

use App\Events\ContentDeleted;
use App\Events\ContentSynced;
use App\Models\Content;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ContentService
{
    /**
     * Create new content.
     */
    public function create(array $data): Content
    {
        return DB::transaction(function () use ($data) {

            $content = Content::query()->create([
                'tenant_id' => $data['tenant_id'],
                'content_uuid' => Str::uuid(),
                'title' => $data['title'],
                'slug' => Str::slug($data['title']) . '-' . Str::random(6),
                'description' => $data['description'] ?? null,
                'poster_url' => $data['poster_url'] ?? null,
                'content_type' => $data['content_type'],
                'release_year' => $data['release_year'] ?? null,
                'imdb_rating' => $data['imdb_rating'] ?? null,
                'language' => $data['language'] ?? 'english',
                'status' => $data['status'] ?? 'published',
            ]);

            event(new ContentSynced(
                $content->id
            ));

            return $content;
        });
    }

    /**
     * Update existing content.
     */
    public function update(
        int $contentId,
        array $data
    ): Content {

        return DB::transaction(function () use (
            $contentId,
            $data
        ) {

            $content = Content::query()
                ->findOrFail($contentId);

            $content->update([
                'title' => $data['title'] ?? $content->title,
                'description' => $data['description'] ?? $content->description,
                'poster_url' => $data['poster_url'] ?? $content->poster_url,
                'content_type' => $data['content_type'] ?? $content->content_type,
                'release_year' => $data['release_year'] ?? $content->release_year,
                'imdb_rating' => $data['imdb_rating'] ?? $content->imdb_rating,
                'language' => $data['language'] ?? $content->language,
                'status' => $data['status'] ?? $content->status,
            ]);

            event(new ContentSynced(
                $content->id
            ));

            return $content->fresh();
        });
    }

    /**
     * Delete content.
     */
    public function delete(int $contentId): void
    {
        DB::transaction(function () use ($contentId) {

            $content = Content::query()
                ->findOrFail($contentId);

            $content->delete();

            event(new ContentDeleted(
                $contentId
            ));
        });
    }
}