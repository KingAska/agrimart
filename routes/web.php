<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::livewire('/', 'pages::home-page')->name('home');
Route::livewire('/cart', 'pages::cart')->name('cart');
Route::livewire('/product/{slug}', 'pages::product-detail')->name('product.detail');
Route::livewire('/checkout', 'pages::checkout')->name('checkout');
Route::livewire('/invoice/{invoice_number}', 'pages::invoice')->name('invoice');
Route::livewire('/cek-pesanan', 'pages::check-order')->name('check.order');

// Route::Livewire('/about', 'pages::about')->name('about');
// Route::Livewire('/cara-pemesanan', 'pages::how-to-order')->name('how-to-order');
// Route::Livewire('/syarat-ketentuan', 'pages::terms')->name('terms');
// Route::Livewire('/kontak', 'pages::contact')->name('contact');

Route::view('/about', 'pages.about')->name('about');
Route::view('/cara-pemesanan', 'pages.how-to-order')->name('how-to-order');
Route::view('/syarat-ketentuan', 'pages.terms')->name('terms');
Route::view('/kontak', 'pages.contact')->name('contact');

Route::post('/kontak/kirim', [ContactController::class, 'send'])->name('contact.send');
Route::get('/cek-kota', function () {
    $response = Http::withHeaders(['key' => env('RAJAONGKIR_API_KEY')])
        ->get('https://rajaongkir.komerce.id/api/v1/destination/city?province_id=11');
    return $response->json();
});