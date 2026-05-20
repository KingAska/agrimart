<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Session;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->resetPage();
    }

    public function clearFilter()
    {
        $this->selectedCategory = null;
        $this->search = '';
        $this->resetPage();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        if (!$product || $product->stock < 1) return;

        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->images->first()?->image_path,
                'quantity' => 1,
            ];
        }

        Session::put('cart', $cart);

        $this->dispatch('cart-updated');

        $this->dispatch('notify', message: 'Berhasil dimasukkan ke keranjang!');
    }

    public function with(): array
    {
        return [
            'categories' => Category::all(),
            'products' => Product::query()
                ->where('is_active', true)
                ->with(['images' => function($query) {
                    $query->latest()->limit(1); 
                }])
                ->when($this->selectedCategory, function ($query) {
                    $query->where('category_id', $this->selectedCategory);
                })
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->orderByRaw('stock > 0 DESC')
                ->latest()
                ->paginate(12)
        ];
    }
};
?>

<div class="bg-gray-50 min-h-screen pb-12">
    <div class="bg-green-900 text-white py-16 px-4 sm:px-6 lg:px-8 text-center shadow-md">
        <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
            Selamat Datang di Agrimart BBRMP Banten
        </h1>
        <p class="mt-4 text-xl max-w-2xl mx-auto opacity-90">
            Pemesanan hari Senin - Jumat Pukul 08.00 s/d 15.00 WIB.
        <div class="mt-8 max-w-xl mx-auto relative text-gray-900">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari benih, pupuk..." 
                class="w-full text-white py-3 px-5 rounded-full shadow-lg focus:ring-4 focus:ring-green-300 outline-none transition-all">
            <svg class="w-6 h-6 absolute right-4 top-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <div class="flex flex-wrap gap-3 mb-8 justify-center">
            <button wire:click="clearFilter" 
                class="px-5 py-2 rounded-full text-sm font-semibold transition-colors shadow-sm {{ is_null($selectedCategory) ? 'bg-green-900 text-white' : 'bg-white text-gray-700 hover:bg-green-50' }}">
                Semua Produk
            </button>
            @foreach($categories as $category)
                <button wire:click="setCategory({{ $category->id }})" 
                    class="px-5 py-2 rounded-full text-sm font-semibold transition-colors shadow-sm {{ $selectedCategory == $category->id ? 'bg-green-900 text-white' : 'bg-white text-gray-700 hover:bg-green-50' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($products as $product)
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-shadow duration-300 overflow-hidden flex flex-col group"> <a href="{{ route('product.detail', $product->slug) }}" class="w-full h-48 overflow-hidden bg-gray-200 cursor-pointer block">
                        @if($product->images->count() > 0)
                            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">Tanpa Gambar</div>
                        @endif
                    </a>
                    
                    <div class="p-5 flex flex-col grow">
                        <span class="text-xs font-medium text-green-600 mb-1">{{ $product->category->name ?? 'Kategori' }}</span>
                        
                        <a href="{{ route('product.detail', $product->slug) }}" class="hover:text-green-600 transition-colors">
                            <h3 class="text-lg font-bold text-gray-900 leading-tight mb-2">{{ $product->name }}</h3>
                        </a>
                        
                        <p class="text-xl font-extrabold text-gray-900 mt-auto">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        
                        <div class="mt-4">
                            @if($product->stock > 0)
                                <button wire:click="addToCart({{ $product->id }})" class="w-full bg-green-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-700 transition-colors shadow-sm flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Masukkan Keranjang
                                </button>
                            @else
                                <button disabled class="w-full bg-gray-300 text-gray-500 font-semibold py-2 px-4 rounded-lg cursor-not-allowed">
                                    Stok Habis
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="mt-1 text-sm text-gray-500">Produk tidak ditemukan.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $products->links() }}
        </div>
    </div>
</div>