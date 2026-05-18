<?php

use Livewire\Component;
use Illuminate\Support\Facades\Session;

new class extends Component
{
    public $cart = [];

    public function mount()
    {
        $this->cart = Session::get('cart', []);
    }

    public function increaseQuantity($id)
    {
        $this->cart[$id]['quantity']++;
        $this->saveCart();
    }

    public function decreaseQuantity($id)
    {
        if ($this->cart[$id]['quantity'] > 1) {
            $this->cart[$id]['quantity']--;
        } else {
            unset($this->cart[$id]);
        }
        $this->saveCart();
    }

    public function removeItem($id)
    {
        unset($this->cart[$id]);
        $this->saveCart();
    }

    private function saveCart()
    {
        Session::put('cart', $this->cart);
        $this->dispatch('cart-updated');
    }

    public function getTotalProperty()
    {
        $total = 0;
        foreach ($this->cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Keranjang Belanja</h1>

    @if(count($cart) > 0)
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="w-full lg:w-2/3 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <ul class="divide-y divide-gray-200">
                    @foreach($cart as $id => $item)
                        <li class="py-6 flex items-center justify-between">
                            <div class="flex items-center gap-6">
                                <img src="{{ $item['image'] ? asset('storage/'.$item['image']) : 'https://via.placeholder.com/150' }}" alt="{{ $item['name'] }}" class="w-24 h-24 object-cover rounded-lg border">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $item['name'] }}</h3>
                                    <p class="text-green-600 font-semibold mt-1">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-6">
                                <div class="flex items-center border rounded-lg">
                                    <button wire:click="decreaseQuantity({{ $id }})" class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-l-lg">-</button>
                                    <span class="px-4 py-1 font-semibold text-gray-900 border-x">{{ $item['quantity'] }}</span>
                                    <button wire:click="increaseQuantity({{ $id }})" class="px-3 py-1 text-gray-600 hover:bg-gray-100 rounded-r-lg">+</button>
                                </div>
                                
                                <p class="font-bold text-gray-900 w-32 text-right">
                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                </p>

                                <button wire:click="removeItem({{ $id }})" class="text-red-500 hover:text-red-700 p-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="w-full lg:w-1/3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Ringkasan Belanja</h2>
                    
                    <div class="flex justify-between text-gray-600 mb-4">
                        <span>Total Harga</span>
                        <span class="font-semibold text-gray-900">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>
                    
                    <hr class="my-4 border-gray-200">
                    
                    <div class="flex justify-between text-lg font-bold text-gray-900 mb-8">
                        <span>Total Tagihan</span>
                        <span class="text-green-600">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>

                    <a href="/checkout" class="w-full block text-center bg-green-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-green-700 transition-colors shadow-lg">
                        Lanjut ke Pembayaran
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
            <svg class="mx-auto h-24 w-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <h2 class="text-2xl font-bold text-gray-900">Keranjang masih kosong</h2>
            <p class="text-gray-500 mt-2 mb-6">Yuk, cari bibit dan pupuk terbaik untuk kebunmu!</p>
            <a href="/" class="bg-green-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-green-700 transition">
                Mulai Belanja
            </a>
        </div>
    @endif
</div>