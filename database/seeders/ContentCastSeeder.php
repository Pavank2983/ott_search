<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentCastSeeder extends Seeder
{
    public function run(): void
    {
        $contentIds = DB::table('contents')
            ->pluck('id');

        $castIds = DB::table('casts')
            ->pluck('id')
            ->toArray();

        $batch = [];

        foreach ($contentIds as $contentId) {

            $randomCasts = collect($castIds)
                ->random(rand(2, 5));

            foreach ($randomCasts as $castId) {

                $batch[] = [
                    'content_id' => $contentId,
                    'cast_id' => $castId,
                ];
            }

            if (count($batch) >= 5000) {

                DB::table('content_cast')
                    ->insert($batch);

                $batch = [];
            }
        }

        if (! empty($batch)) {
            DB::table('content_cast')
                ->insert($batch);
        }
    }
}