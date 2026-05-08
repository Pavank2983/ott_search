<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        private ContentService $contentService
    ) {}

    /**
     * Create new content.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'poster_url' => ['nullable', 'string'],
            'content_type' => ['required', 'string'],
            'release_year' => ['nullable', 'integer'],
            'imdb_rating' => ['nullable', 'numeric'],
            'language' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $content = $this->contentService->create(
            $validated
        );

        return response()->json([
            'message' => 'Content created successfully.',
            'data' => $content,
        ], 201);
    }

    /**
     * Update existing content.
     */
    public function update(
        Request $request,
        int $id
    ): JsonResponse {

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'poster_url' => ['nullable', 'string'],
            'content_type' => ['sometimes', 'string'],
            'release_year' => ['nullable', 'integer'],
            'imdb_rating' => ['nullable', 'numeric'],
            'language' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $content = $this->contentService->update(
            contentId: $id,
            data: $validated
        );

        return response()->json([
            'message' => 'Content updated successfully.',
            'data' => $content,
        ]);
    }

    /**
     * Delete content.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->contentService->delete($id);

        return response()->json([
            'message' => 'Content deleted successfully.',
        ]);
    }
}