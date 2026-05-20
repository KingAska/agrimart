@extends('layouts.app')

@section('title', 'Syarat & Ketentuan - Agrimart')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-16">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 md:p-12">
        <h1 class="text-3xl font-black text-gray-900 mb-10 border-b pb-6">Syarat & Ketentuan</h1>
        
        <div class="space-y-10 text-gray-600">
            <section class="relative pl-8 border-l-2 border-green-900">
                <div class="absolute -left-2.25 top-0 w-4 h-4 bg-green-900 rounded-full"></div>
                <h2 class="text-xl font-bold text-gray-800 mb-3">1. Kebijakan Pengiriman</h2>
                <p class="leading-relaxed">
                    Setiap pesanan yang masuk akan kami proses dan kirimkan <span class="font-bold text-gray-900">secepatnya</span> setelah konfirmasi pembayaran diterima oleh sistem. Tim Kami akan memastikan benih dan bibit Anda sampai dengan selamat.
                </p>
            </section>

            <section class="relative pl-8 border-l-2 border-green-900">
                <div class="absolute -left-2.25 top-0 w-4 h-4 bg-green-900 rounded-full"></div>
                <h2 class="text-xl font-bold text-gray-800 mb-3">2. Pembatalan Pesanan</h2>
                <p class="leading-relaxed">
                    Pesanan yang sudah memasuki status <span class="italic text-gray-900">"Processing"</span> atau sudah dalam tahap pengemasan tidak dapat dibatalkan secara sepihak. Mohon teliti kembali belanjaan Anda sebelum melakukan pembayaran.
                </p>
            </section>

            <section class="relative pl-8 border-l-2 border-green-900">
                <div class="absolute -left-2.25 top-0 w-4 h-4 bg-green-900 rounded-full"></div>
                <h2 class="text-xl font-bold text-gray-800 mb-3">3. Komplain</h2>
                <p class="leading-relaxed">
                    Kami menjamin kualitas produk kami. Namun, jika terdapat komplain, <span class="font-bold text-green-600">silahkan mendatangi offline store Agrimart</span> atau <a href="{{ route('contact') }}" class="text-green-600 underline font-medium hover:text-green-700">Hubungi Kami</a>.
                </p>
            </section>
        </div>
    </div>
</div>
@endsection