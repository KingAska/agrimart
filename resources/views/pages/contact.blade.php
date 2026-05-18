@php
    require_once app_path('Helpers/Phone.php');
    $phone = format_phone(env('CONTACT_WA_NUMBER'));
@endphp

@extends('layouts.app')

@section('title', 'Hubungi Kami - Agri Mart')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
        <div>
            <h1 class="text-4xl font-black text-gray-900 mb-6">Hubungi Kami</h1>
            <p class="text-gray-600 mb-8">Punya pertanyaan seputar produk atau pesanan? Tim kami siap membantu Anda 24/7.</p>
            
            <div class="space-y-6">
                <a href="https://wa.me/6281234567890" target="_blank" class="flex items-center gap-4 group">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center group-hover:bg-green-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12c0 2.17.7 4.19 1.94 5.86L2.43 22.5l4.85-1.47A9.95 9.95 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">WhatsApp Admin</p>
                        <p class="font-bold text-lg text-gray-900 group-hover:text-green-600 transition-colors">{{ $phone['formatted'] }}</p>
                    </div>
                </a>

                <a href="mailto:support@agrimart.com" class="flex items-center gap-4 group">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email Support</p>
                        <p class="font-bold text-lg text-gray-900 group-hover:text-blue-600 transition-colors">{{ env('CONTACT_SUPPORT_EMAIL') }}</p>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <h2 class="text-xl font-bold mb-6 text-gray-900">Kirim Pesan Cepat</h2>
            
            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <form action="{{ route('contact.send') }}" method="POST" class="space-y-4">
                @csrf <div>
                    <input type="text" name="name" placeholder="Nama Anda" required value="{{ old('name') }}"
                        class="w-full rounded-xl border-gray-200 focus:ring-green-500 focus:border-green-500 p-3 border outline-none">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <input type="email" name="email" placeholder="Email Anda" required value="{{ old('email') }}"
                        class="w-full rounded-xl border-gray-200 focus:ring-green-500 focus:border-green-500 p-3 border outline-none">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <textarea name="message" placeholder="Pesan Anda" rows="4" required
                        class="w-full rounded-xl border-gray-200 focus:ring-green-500 focus:border-green-500 p-3 border outline-none">{{ old('message') }}</textarea>
                    @error('message') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <button type="submit" 
                    class="w-full bg-green-600 text-white font-bold py-4 rounded-xl hover:bg-green-700 transition-all active:scale-[0.98]">
                    Kirim Sekarang
                </button>
            </form>
        </div>
    </div>
</div>
@endsection