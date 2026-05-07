<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_cast', function (Blueprint $table) {
            $table->foreignId('content_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('cast_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->primary([
                'content_id',
                'cast_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_cast');
    }
};