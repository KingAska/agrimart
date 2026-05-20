@extends('layouts.app')

@section('title', 'Tentang Kami - Agrimart')

@section('content')
    <div class="bg-linear-to-b from-green-50 to-white py-20 border-b border-green-100">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-6 tracking-tight">
                Solusi Pertanian & Peternakan Unggul Bersama <span class="text-green-900">Agrimart</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-600 leading-relaxed max-w-2xl mx-auto">
                Penyedia benih sumber dan bibit berkualitas dari BBRMP Banten. Kami menghadirkan solusi modern terintegrasi, mulai dari benih tanaman unggul bersertifikat hingga bibit ayam pilihan, demi mendukung produktivitas dan kesejahteraan Anda.
            </p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-6">Mendigitalisasi Agribisnis, Mendukung Petani & Peternak</h2>
                <div class="space-y-6 text-gray-600 leading-relaxed">
                    <p>
                        <strong>Agrimart</strong> hadir sebagai platform modern untuk memudahkan Anda mengakses produk unggulan dari UPBS Benih dan UPBS Ayam BBRMP Banten.
                    </p>
                    <p>
                        Setiap benih tanaman dan bibit ayam yang kami sediakan telah melalui proses seleksi, kurasi, dan kontrol kualitas ketat standar nasional. Karena kami percaya, hasil panen dan ternak yang melimpah selalu dimulai dari bibit terbaik.
                    </p>
                </div>
                
                <div class="grid grid-cols-2 gap-6 mt-10 border-t pt-8">
                    <div>
                        <p class="text-4xl font-black text-green-900 mb-1">100%</p>
                        <p class="text-sm text-gray-500 font-medium">Kualitas Terbaik</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-green-900 mb-1">24/7</p>
                        <p class="text-sm text-gray-500 font-medium">Realtime Update Stock</p>
                    </div>
                </div>
            </div>

            <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-gray-100 aspect-4/3 group">
                <img src="{{ asset('storage/product-images/About.png') }}" 
                     alt="Pertanian Modern Agrimart" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                <div class="absolute inset-0 bg-linear-to-t from-black/50 to-transparent"></div>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 py-20 border-t border-gray-100">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-4">Mengapa Memilih Agrimart?</h2>
                <p class="text-gray-500">Kami berkomitmen memberikan pengalaman belanja agrikultur terbaik.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-green-100 text-green-900 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">TERSERTIFIKASI</h3>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-green-100 text-green-900 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">TERSTANDAR</h3>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-green-100 text-green-900 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                       <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">BERKUALITAS</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-20 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-8">Siap Memulai Usaha Tani dan Ternak yang Lebih Menguntungkan?</h2>
        <a href="/" class="inline-block bg-green-900 text-white font-extrabold text-lg py-4 px-10 rounded-xl hover:bg-green-700 hover:shadow-xl hover:-translate-y-1 transition-all">
            Mulai Belanja Sekarang
        </a>
    </div>
@endsection