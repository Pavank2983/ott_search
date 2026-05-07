<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->uuid('content_uuid')->unique();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('content_type')->index();
            $table->year('release_year')->nullable();
            $table->string('language', 20)->nullable();
            $table->json('genres')->nullable();
            $table->string('poster_url')->nullable();
            $table->decimal('imdb_rating', 3, 1)->nullable();
            $table->text('search_text')->nullable();
            $table->string('status')->default('published')->index();
            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Performance Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('tenant_id');

            $table->index([
                'tenant_id',
                'status'
            ]);

            $table->index([
                'tenant_id',
                'content_type'
            ]);

            $table->index([
                'tenant_id',
                'release_year'
            ]);

            $table->unique([
                'tenant_id',
                'slug'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};