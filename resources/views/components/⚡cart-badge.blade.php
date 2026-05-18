<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;

new class extends Component
{
    public int $cartCount = 0;

    public function mount()
    {
        $this->updateCartCount();
    }

    #[On('cart-updated')]
    public function updateCartCount()
    {
        $cart = Session::get('cart', []);
        $this->cartCount = array_sum(array_column($cart, 'quantity'));
    }
};
?>

<a href="{{ route('cart') }}" class="relative p-2 text-gray-600 hover:text-green-600 transition-colors">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
    
    @if($cartCount > 0)
        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full shadow-sm">
            {{ $cartCount }}
        </span>
    @endif
</a>