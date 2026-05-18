<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin AgriMart',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('123'),
            'remember_token' => Str::random(10),
        ]);

        // $categories = [
        //     [
        //         'name' => 'Bibit Tanaman',
        //         'slug' => 'bibit-tanaman',
        //         'description' => 'Berbagai macam bibit unggul mulai dari sayuran, buah-buahan, hingga tanaman hias.',
        //     ],
        //     [
        //         'name' => 'Pupuk & Nutrisi',
        //         'slug' => 'pupuk-nutrisi',
        //         'description' => 'Pupuk organik, NPK, dan nutrisi tambahan untuk mempercepat pertumbuhan tanaman.',
        //     ],
        //     [
        //         'name' => 'Alat Pertanian',
        //         'slug' => 'alat-pertanian',
        //         'description' => 'Cangkul, sekop, sprayer, dan peralatan modern untuk mempermudah pekerjaan tani.',
        //     ],
        //     [
        //         'name' => 'Obat & Pestisida',
        //         'slug' => 'obat-pestisida',
        //         'description' => 'Solusi pembasmi hama dan pelindung tanaman dari penyakit.',
        //     ],
        //     [
        //         'name' => 'Media Tanam',
        //         'slug' => 'media-tanam',
        //         'description' => 'Tanah humus, sekam bakar, cocopeat, dan pot tanaman.',
        //     ],
        // ];

        // foreach ($categories as $category) {
        //     Category::create($category);
        // }

        $this->call([
        ProductSeeder::class,
    ]);
    }
}
