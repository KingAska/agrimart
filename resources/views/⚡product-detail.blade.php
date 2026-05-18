<?php

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

new class extends Component
{
    public Product $product;
    public int $quantity = 1;

    public function mount($slug)
    {
        $this->product = Product::where('slug', $slug)
            ->with(['images', 'category'])
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function increaseQuantity()
    {
        if ($this->quantity < $this->product->stock) {
            $this->quantity++;
        }
    }

    public function decreaseQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart()
    {
        if ($this->product->stock < $this->quantity) return;

        $cart = Session::get('cart', []);

        if (isset($cart[$this->product->id])) {
            $cart[$this->product->id]['quantity'] += $this->quantity;
        } else {
            $cart[$this->product->id] = [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->price,
                'image' => $this->product->images->first()?->image_path,
                'quantity' => $this->quantity,
            ];
        }

        Session::put('cart', $cart);
        $this->dispatch('cart-updated');
        
        // Munculkan notifikasi sukses sesaat
        session()->flash('success', 'Produk berhasil ditambahkan ke keranjang!');
    }
};
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <nav class="mb-8 flex text-sm text-gray-500 gap-2">
            <a href="/" class="hover:text-green-600 transition">Beranda</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">{{ $product->name }}</span>
        </nav>

        @if (session()->has('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
                <p class="font-bold">Berhasil!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
                
                @php
                    $firstImage = $product->images->count() > 0 ? asset('storage/' . $product->images->first()->image_path) : 'https://via.placeholder.com/600';
                @endphp
                
                <div x-data="{ mainImage: '{{ $firstImage }}' }" class="flex flex-col gap-4">
                    <div class="w-full aspect-w-1 aspect-h-1 bg-gray-200 rounded-xl overflow-hidden border">
                        <img :src="mainImage" alt="{{ $product->name }}" class="w-full h-96 object-cover object-center">
                    </div>
                    
                    @if($product->images->count() > 1)
                        <div class="flex gap-4 overflow-x-auto pb-2">
                            @foreach($product->images as $img)
                                <button @click="mainImage = '{{ asset('storage/' . $img->image_path) }}'" class="shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 focus:outline-none focus:border-green-500 hover:opacity-75 transition">
                                    <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex flex-col">
                    <div class="mb-6">
                        <span class="text-sm font-semibold text-green-600 tracking-wider uppercase">{{ $product->category->name ?? 'Kategori' }}</span>
                        <h1 class="text-3xl font-extrabold text-gray-900 mt-2">{{ $product->name }}</h1>
                        <p class="text-4xl font-black text-gray-900 mt-4">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Deskripsi Produk</h3>
                        <div class="prose prose-green text-gray-600 max-w-none">
                            {!! $product->description !!}
                        </div>
                    </div>

                    <div class="mt-auto border-t pt-6">
                        <div class="flex items-center gap-4 mb-6">
                            <span class="text-gray-700 font-medium">Sisa Stok:</span>
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full font-bold shadow-inner">{{ $product->stock }}</span>
                        </div>

                        @if($product->stock > 0)
                            <div class="flex flex-col sm:flex-row gap-4 items-end">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-semibold text-gray-600">Jumlah Beli</label>
                                    <div class="flex items-center border border-gray-300 rounded-xl bg-white overflow-hidden shadow-sm h-12">
                                        <button wire:click="decreaseQuantity" class="px-4 text-gray-500 hover:bg-gray-100 hover:text-green-600 transition font-bold text-xl">-</button>
                                        <span class="px-4 font-bold text-gray-900 border-x border-gray-300 w-16 text-center">{{ $quantity }}</span>
                                        <button wire:click="increaseQuantity" class="px-4 text-gray-500 hover:bg-gray-100 hover:text-green-600 transition font-bold text-xl">+</button>
                                    </div>
                                </div>
                                
                                <button wire:click="addToCart" class="grow bg-green-600 text-white font-bold h-12 rounded-xl hover:bg-green-700 transition shadow-md flex items-center justify-center gap-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Masukkan Keranjang
                                </button>
                            </div>
                        @else
                            <div class="w-full bg-red-100 text-red-600 text-center font-bold py-4 rounded-xl border border-red-200">
                                Maaf, stok produk sedang kosong.
                            </div>
                        @endif
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>