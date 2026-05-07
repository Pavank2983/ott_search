<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CastSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake();

        $casts = [];

        for ($i = 0; $i < 1000; $i++) {
            $name = $faker->unique()->name();

            $casts[] = [
                'name' => $name,
                'slug' => Str::slug($name . '-' . $i),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('casts')->insert($casts);
    }
}