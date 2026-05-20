<?php
    require_once app_path('Helpers/Phone.php');
    $phone = format_phone(env('CONTACT_WA_NUMBER'));
    $email = env('CONTACT_SUPPORT_EMAIL');
?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <title>{{ $title ?? 'Agrimart' }}</title>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    
    <body class="bg-gray-50 flex flex-col min-h-screen text-gray-900 font-sans antialiased">
        
       <nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <a href="/" class="shrink-0 flex items-center gap-2 group">
                    <div class="bg-green-900 text-white p-2 rounded-lg group-hover:bg-green-700 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <span class="font-extrabold text-xl text-green-900 tracking-tight">Agrimart</span>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="/" class="text-sm font-medium text-gray-700 hover:text-green-600 transition">Beranda</a>
                <a href="{{ route('check.order') }}" class="text-sm font-medium text-gray-700 hover:text-green-600 transition">Lacak Pesanan</a>
                <a href="/about" class="text-sm font-medium text-gray-700 hover:text-green-600 transition">About</a>
                <livewire:cart-badge />
            </div>

            <!-- Mobile: Cart + Hamburger -->
            <div class="flex items-center gap-3 md:hidden">
                <livewire:cart-badge />
                <button
                    id="hamburger-btn"
                    onclick="toggleMobileMenu()"
                    class="p-2 rounded-lg text-gray-700 hover:bg-gray-100 transition"
                    aria-label="Toggle menu"
                >
                    <svg id="icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white shadow-md">
        <div class="px-4 py-3 space-y-1">
            <a href="/" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 transition">
                🏠 Beranda
            </a>
            <a href="{{ route('check.order') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 transition">
                📦 Lacak Pesanan
            </a>
            <a href="/about" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700 transition">
                ℹ️ About
            </a>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('icon-open');
            const iconClose = document.getElementById('icon-close');

            menu.classList.toggle('hidden');
            iconOpen.classList.toggle('hidden');
            iconClose.classList.toggle('hidden');
        }
    </script>
</nav>

        <main class="grow">
            @if(isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>

        <footer class="bg-gray-900 text-gray-300 py-10 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                        <div class="bg-green-900 text-white p-2 rounded-lg group-hover:bg-green-700 transition">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        Agrimart
                    </h3>
                    <p class="text-sm leading-relaxed text-gray-400">
                        Solusi untuk kebutuhan pertanian dan peternakan Anda. Kami menyediakan benih & bibit unggul, dan kualitas terbaik.
                    </p>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg mb-4">LAYANAN PELANGGAN</h3>
                        <ul class="text-sm space-y-3 text-gray-400">
                            <li>
                                <a href="{{ route('how-to-order') }}" class="hover:text-green-400 transition flex items-center gap-2">
                                    <span class="w-1 h-1 bg-gray-600 rounded-full"></span>
                                    Cara Pemesanan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('check.order') }}" class="hover:text-green-400 transition flex items-center gap-2">
                                    <span class="w-1 h-1 bg-gray-600 rounded-full"></span>
                                    Lacak & Konfirmasi Pesanan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('terms') }}" class="hover:text-green-400 transition flex items-center gap-2">
                                    <span class="w-1 h-1 bg-gray-600 rounded-full"></span>
                                    Syarat & Ketentuan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('contact') }}" class="hover:text-green-400 transition flex items-center gap-2">
                                    <span class="w-1 h-1 bg-gray-600 rounded-full"></span>
                                    Hubungi Kami
                                </a>
                            </li>
                        </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg mb-4">KONTAK</h3>
                    <div class="text-sm space-y-2 text-gray-400">
                        <p class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            {{ $email }}
                        </p>
                        <p class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            {{ $phone['formatted'] }}
                        </p>
                    </div>
                    <h3 class="text-white font-bold text-lg mb-4 mt-5 ">LOKASI AGRIMART</h3>
                    <div class="w-full h-45 rounded-lg overflow-hidden shadow-md border border-gray-800 mt-2">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.086952069873!2d106.23915319999999!3d-6.1189974!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e41f50025cb8c0d%3A0xabee5588b993b038!2sBALAI%20PENERAPAN%20MODERNISASI%20PERTANIAN%20(BRMP)%20BANTEN!5e0!3m2!1sid!2sid!4v1779285249139!5m2!1sid!2sid" 
                            class="w-full h-full border-0"
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
                </div>
                
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 pt-8 border-t border-gray-800 text-sm text-center text-gray-500">
                &copy; {{ date('Y') }} Agrimart Balai Besar Penerapan Modernisasi Pertanian Banten. All rights reserved.
            </div>
        </footer>

        @stack('scripts')

        <div 
            x-data="{ show: false, message: '' }"
            x-on:notify.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="fixed bottom-5 right-5 z-50 bg-green-900 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3"
            style="display: none;"
        >
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span x-text="message" class="font-bold text-sm"></span>
        </div>
        <script src="https://cdn.tailwindcss.com"></script>
    </body>
</html>