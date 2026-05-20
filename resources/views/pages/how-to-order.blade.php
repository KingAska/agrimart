@extends('layouts.app')

@section('title', 'Cara Pemesanan - Agrimart')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-16">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-12 text-center uppercase tracking-tight">Cara Pemesanan</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
        <div class="space-y-8">
            <div class="flex gap-5 group">
                <div class="shrink-0 w-12 h-12 bg-green-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-green-100 group-hover:scale-110 transition-transform">1</div>
                <div>
                    <h3 class="font-bold text-lg text-gray-900">Pilih Produk</h3>
                    <p class="text-gray-500 leading-relaxed">Jelajahi katalog kami, pilih benih atau bibit ayam yang Anda butuhkan, lalu klik <span class="font-semibold text-green-600">"Masukkan ke Keranjang"</span>.</p>
                </div>
            </div>

            <div class="flex gap-5 group">
                <div class="shrink-0 w-12 h-12 bg-green-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-green-100 group-hover:scale-110 transition-transform">2</div>
                <div>
                    <h3 class="font-bold text-lg text-gray-900">Checkout & Isi Alamat</h3>
                    <p class="text-gray-500 leading-relaxed">Periksa kembali belanjaan Anda. Isi data pengiriman dan gunakan <span class="font-semibold text-green-600">Fitur Peta bagi opsi antar pengiriman</span> untuk menandai titik lokasi rumah Anda agar pengantaran lebih akurat.</p>
                </div>
            </div>

            <div class="flex gap-5 group">
                <div class="shrink-0 w-12 h-12 bg-green-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-green-100 group-hover:scale-110 transition-transform">3</div>
                <div>
                    <h3 class="font-bold text-lg text-gray-900">Lakukan Pembayaran</h3>
                    <p class="text-gray-500 leading-relaxed">Pilih metode Transfer Manual atau <span class="font-semibold text-blue-600">Midtrans (Otomatis)</span> untuk bayar via QRIS atau Virtual Account. Selesaikan pembayaran sebelum batas waktu habis.</p>
                </div>
            </div>

            <div class="flex gap-5 group">
                <div class="shrink-0 w-12 h-12 bg-green-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-green-100 group-hover:scale-110 transition-transform">4</div>
                <div>
                    <h3 class="font-bold text-lg text-gray-900">Pantau Pesanan</h3>
                    <p class="text-gray-500 leading-relaxed">Setelah bayar, Anda bisa memantau status barang melalui halaman <a href="{{ route('check.order') }}" class="text-green-600 underline font-medium hover:text-green-700">Lacak Pesanan</a> hingga sampai di depan pintu rumah.</p>
                </div>
            </div>
        </div>

        <div class="bg-linear-to-br from-gray-50 to-gray-100 rounded-3xl p-12 border border-dashed border-gray-300 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 bg-white rounded-2xl shadow-sm flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <p class="text-gray-400 font-medium">Infografis Alur Belanja<br><span class="text-xs">Segera Hadir</span></p>
        </div>
    </div>
</div>
@endsection