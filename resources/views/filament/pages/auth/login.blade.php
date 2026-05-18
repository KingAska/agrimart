<div class="flex min-h-screen bg-gray-50">
        
    <div class="hidden lg:flex w-1/2 bg-gray-900 items-center justify-center relative overflow-hidden">
        
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1500937386664-56d1dfef3854?q=80&w=2000&auto=format&fit=crop" 
                 class="w-full h-full object-cover opacity-40">
        </div>

        <div class="absolute inset-0 bg-linear-to-br from-gray-900 to-green-900 opacity-90"></div>
        
        <div class="relative z-10 text-white text-center px-12">
            <h1 class="text-5xl font-bold mb-6">Agrimart</h1>
            <p class="text-xl text-gray-300">Solusi digital terpercaya untuk kebutuhan pertanian dan peternakan modern Anda.</p>
        </div>
        
        <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-green-600/20 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 relative">
        
        <a href="{{ url('/') }}" class="absolute top-6 left-6 flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-green-600 transition-colors duration-200 group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Website
        </a>

        <div class="w-full max-w-md mt-10 lg:mt-0"> 
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-900">Selamat Datang</h2>
                <p class="text-gray-500 mt-2">Masuk ke Panel Admin Agrimart</p>
            </div>

            <div class="mb-6">
                @livewire('notifications')
            </div>

            <form wire:submit="authenticate" class="space-y-6">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" wire:model="data.email" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-600 focus:border-green-600 transition outline-none">
                    @error('data.email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" wire:model="data.password" 
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-600 focus:border-green-600 transition outline-none">
                    @error('data.password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="data.remember" class="rounded text-green-600 focus:ring-green-500 border-gray-300">
                        <span class="text-sm text-gray-600">Ingat Saya</span>
                    </label>
                    
                    @if (filament()->hasPasswordReset())
                        <a href="{{ filament()->getRequestPasswordResetUrl() }}" class="text-sm font-medium text-green-600 hover:text-green-500">Lupa Password?</a>
                    @endif
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition shadow-lg flex justify-center items-center">
                    <svg wire:loading wire:target="authenticate" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="authenticate">Masuk Sekarang</span>
                    <span wire:loading wire:target="authenticate">Memproses...</span>
                </button>
            </form>

            <p class="text-center mt-8 text-sm text-gray-400">
                &copy; {{ date('Y') }} Agrimart.
            </p>
        </div>
    </div>
</div>