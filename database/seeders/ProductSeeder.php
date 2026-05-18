<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $this->command->info('Mempersiapkan data produk Agri Mart yang realistis...');

        // 50 Data Asli Pertanian Indonesia
        $realProducts = [
            // Kategori: Benih & Bibit Tanaman
            ['name' => 'Benih Cabai Rawit Ori 212', 'category' => 'Benih & Bibit', 'price' => 85000],
            ['name' => 'Benih Tomat Servo F1', 'category' => 'Benih & Bibit', 'price' => 125000],
            ['name' => 'Benih Semangka Inul', 'category' => 'Benih & Bibit', 'price' => 65000],
            ['name' => 'Benih Jagung Manis Bonanza', 'category' => 'Benih & Bibit', 'price' => 95000],
            ['name' => 'Benih Kangkung Bika', 'category' => 'Benih & Bibit', 'price' => 35000],
            ['name' => 'Benih Terong Yuvita F1', 'category' => 'Benih & Bibit', 'price' => 45000],
            ['name' => 'Benih Padi Inpari 32 (5kg)', 'category' => 'Benih & Bibit', 'price' => 110000],
            ['name' => 'Bibit Durian Musang King Kaki 3', 'category' => 'Benih & Bibit', 'price' => 150000],
            ['name' => 'Bibit Alpukat Miki Berlabel', 'category' => 'Benih & Bibit', 'price' => 75000],
            ['name' => 'Bibit Mangga Mahatir', 'category' => 'Benih & Bibit', 'price' => 85000],

            // Kategori: Pupuk & Nutrisi
            ['name' => 'Pupuk NPK Mutiara 16-16-16 (1kg)', 'category' => 'Pupuk & Nutrisi', 'price' => 22000],
            ['name' => 'Pupuk Urea Nitrea Non-Subsidi (5kg)', 'category' => 'Pupuk & Nutrisi', 'price' => 65000],
            ['name' => 'Pupuk KCL Mahkota (1kg)', 'category' => 'Pupuk & Nutrisi', 'price' => 18000],
            ['name' => 'Pupuk TSP 46% (1kg)', 'category' => 'Pupuk & Nutrisi', 'price' => 15000],
            ['name' => 'Pupuk Organik Cair (POC) NASA 500cc', 'category' => 'Pupuk & Nutrisi', 'price' => 45000],
            ['name' => 'Pupuk Daun Gandasil D 100gr', 'category' => 'Pupuk & Nutrisi', 'price' => 14000],
            ['name' => 'Pupuk Buah Gandasil B 100gr', 'category' => 'Pupuk & Nutrisi', 'price' => 14000],
            ['name' => 'Pupuk Kandang Ayam Fermentasi (10kg)', 'category' => 'Pupuk & Nutrisi', 'price' => 25000],
            ['name' => 'Nutrisi Hidroponik AB Mix Sayur 250gr', 'category' => 'Pupuk & Nutrisi', 'price' => 28000],
            ['name' => 'Dolomit / Kapur Pertanian (5kg)', 'category' => 'Pupuk & Nutrisi', 'price' => 20000],

            // Kategori: Pestisida & ZPT
            ['name' => 'Insektisida Curacron 500EC 250ml', 'category' => 'Pestisida & ZPT', 'price' => 65000],
            ['name' => 'Insektisida Decis 25EC 50ml', 'category' => 'Pestisida & ZPT', 'price' => 25000],
            ['name' => 'Fungisida Antracol 70WP 250gr', 'category' => 'Pestisida & ZPT', 'price' => 45000],
            ['name' => 'Fungisida Dithane M-45 200gr', 'category' => 'Pestisida & ZPT', 'price' => 38000],
            ['name' => 'Herbisida Roundup 486 SL 1 Liter', 'category' => 'Pestisida & ZPT', 'price' => 95000],
            ['name' => 'Herbisida Gramoxone 276SL 1 Liter', 'category' => 'Pestisida & ZPT', 'price' => 85000],
            ['name' => 'Bakterisida Agrept 20WP 50gr', 'category' => 'Pestisida & ZPT', 'price' => 22000],
            ['name' => 'Rodentisida Petrokum (Racun Tikus)', 'category' => 'Pestisida & ZPT', 'price' => 15000],
            ['name' => 'ZPT Atonik 6.0 L 100ml', 'category' => 'Pestisida & ZPT', 'price' => 30000],
            ['name' => 'Lem Perangkap Lalat Buah Glumon', 'category' => 'Pestisida & ZPT', 'price' => 18000],

            // Kategori: Alat & Peralatan Pertanian
            ['name' => 'Sprayer Elektrik Yokohama 16 Liter', 'category' => 'Alat & Peralatan', 'price' => 650000],
            ['name' => 'Gunting Dahan Pruning Kenmaster', 'category' => 'Alat & Peralatan', 'price' => 45000],
            ['name' => 'Cangkul Baja Asli Anti Lengket', 'category' => 'Alat & Peralatan', 'price' => 120000],
            ['name' => 'Sabit / Arit Baja Per Tajam', 'category' => 'Alat & Peralatan', 'price' => 55000],
            ['name' => 'Selang Drip Irigasi 3/4 Inch (100m)', 'category' => 'Alat & Peralatan', 'price' => 185000],
            ['name' => 'Gembor Air Plastik Maspion 10 Liter', 'category' => 'Alat & Peralatan', 'price' => 75000],
            ['name' => 'Plastik Mulsa Hitam Perak (Roll 500m)', 'category' => 'Alat & Peralatan', 'price' => 450000],
            ['name' => 'Paranet 65% Peneduh Tanaman (Lebar 3m)', 'category' => 'Alat & Peralatan', 'price' => 25000], // per meter
            ['name' => 'Polybag Hitam Ukuran 25x25 (1kg)', 'category' => 'Alat & Peralatan', 'price' => 32000],
            ['name' => 'Traktor Mini Quick Capung Metal', 'category' => 'Alat & Peralatan', 'price' => 12500000],

            // Kategori: Media Tanam & Aksesoris
            ['name' => 'Cocopeat Sabut Kelapa Blok 5kg', 'category' => 'Media Tanam', 'price' => 45000],
            ['name' => 'Sekam Bakar Murni Organik 1kg', 'category' => 'Media Tanam', 'price' => 8000],
            ['name' => 'Media Tanam Subur Siap Pakai 10kg', 'category' => 'Media Tanam', 'price' => 25000],
            ['name' => 'Rockwool Cultilene Hidroponik (1 Slab)', 'category' => 'Media Tanam', 'price' => 65000],
            ['name' => 'Root Up Perangsang Akar 100gr', 'category' => 'Media Tanam', 'price' => 35000],
            ['name' => 'Hydroton Jerman Media Hidroponik 1L', 'category' => 'Media Tanam', 'price' => 15000],
            ['name' => 'Perlite Pertanian Ukuran 3-6mm 10L', 'category' => 'Media Tanam', 'price' => 40000],
            ['name' => 'Vermiculite Import 5L', 'category' => 'Media Tanam', 'price' => 35000],
            ['name' => 'Tray Semai Benih 105 Lubang', 'category' => 'Media Tanam', 'price' => 12000],
            ['name' => 'Pot Plastik Hitam Ukuran 20cm', 'category' => 'Media Tanam', 'price' => 4500],
        ];

        // Looping untuk memasukkan data asli
        foreach ($realProducts as $item) {
            // 1. Buat atau Cari Kategori
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($item['category'])],
                ['name' => $item['category']]
            );

            // 2. Buat Deskripsi Realistis
            $description = "Produk " . $item['name'] . " 100% original. Sangat cocok untuk kebutuhan pertanian, perkebunan, maupun hobi tanaman hias di rumah Anda. Telah lulus uji kualitas mutu (Quality Control) dan siap dikirim dengan aman.";

            // 3. Simpan ke database
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $item['name'],
                'slug' => Str::slug($item['name']) . '-' . Str::random(4), 
                'description' => $description,
                'price' => $item['price'], 
                'stock' => $faker->numberBetween(10, 500),
                'is_active' => true,
            ]);

            // 4. Buat 1 atau 2 Gambar Dummy untuk setiap produk
            $numImages = $faker->numberBetween(1, 2);
            for ($j = 0; $j < $numImages; $j++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'products/sample-agrimart.jpg', // Path dummy
                ]);
            }
        }

        $this->command->info('50 Produk Asli Pertanian berhasil ditambahkan ke database!');
    }
}