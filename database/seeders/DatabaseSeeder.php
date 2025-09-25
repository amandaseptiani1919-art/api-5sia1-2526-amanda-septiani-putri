<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // factory
        //User::factory(15)->create();

        Product::factory(200)->create();

        //seeder
        //User::factory()->create([
            //'name' => 'Test User',
            //'email' => 'test@example.com',
       // ]);
    }
}