<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    private const CONTENTS_PER_TENANT = 50000;

    private array $contentTypes = [
        'movie',
        'series',
        'documentary',
    ];

    private array $languages = [
        'English',
        'Tamil',
        'Hindi',
        'Japanese',
        'Korean',
        'Spanish',
    ];

    private array $genres = [
        'Action',
        'Drama',
        'Comedy',
        'Sci-Fi',
        'Adventure',
        'Thriller',
        'Romance',
        'Crime',
        'Fantasy',
    ];

    public function run(): void
    {
        $faker = fake();

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {

            $batch = [];

            for ($i = 1; $i <= self::CONTENTS_PER_TENANT; $i++) {

                $title = $faker->unique()->sentence(
                    rand(2, 4)
                );

                $selectedGenres = collect($this->genres)
                    ->random(rand(1, 3))
                    ->values()
                    ->toArray();

                $description = $faker->paragraph();

                $searchText = implode(' ', [
                    $title,
                    $description,
                    implode(' ', $selectedGenres),
                ]);

                $batch[] = [
                    'tenant_id' => $tenant->id,

                    'content_uuid' => Str::uuid(),

                    'title' => $title,

                    'slug' => Str::slug($title . '-' . $i),

                    'description' => $description,

                    'content_type' => $this->contentTypes[array_rand(
                        $this->contentTypes
                    )],

                    'release_year' => rand(1990, 2026),

                    'language' => $this->languages[array_rand(
                        $this->languages
                    )],

                    'genres' => json_encode($selectedGenres),

                    'poster_url' => $faker->imageUrl(),

                    'imdb_rating' => rand(50, 95) / 10,

                    'search_text' => $searchText,

                    'status' => 'published',

                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                /*
                |--------------------------------------------------------------------------
                | Bulk Insert Every 1000 Rows
                |--------------------------------------------------------------------------
                */

                if (count($batch) === 1000) {

                    DB::table('contents')->insert($batch);

                    $batch = [];

                    $this->command->info(
                        "Inserted {$i} contents for tenant {$tenant->name}"
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Remaining Rows
            |--------------------------------------------------------------------------
            */

            if (! empty($batch)) {
                DB::table('contents')->insert($batch);
            }
        }
    }
}