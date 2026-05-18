<?php

use Livewire\Component;
use App\Models\Order;

new class extends Component
{
    public $invoice_number = '';
    public $order = null;

    public function checkOrder()
    {
        $this->validate([
            'invoice_number' => 'required',
            // 'phone' => 'required',
        ]);

        $this->order = Order::where('invoice_number', $this->invoice_number)
            ->with('items.product')
            ->first();

        if (!$this->order) {
            session()->flash('error', 'Pesanan tidak ditemukan. Pastikan data yang dimasukkan benar.');
        }
    }
};
?>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-extrabold text-gray-900">Lacak Pesanan Anda</h1>
        <p class="text-gray-500 mt-2">Masukkan detail pesanan untuk melihat status pengiriman.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border p-6 mb-8">
        <form wire:submit.prevent="checkOrder" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Invoice</label>
                <input wire:model="invoice_number" type="text" placeholder="INV-XXXXX" class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 border px-4 py-2">
            </div>
            {{-- <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP / WhatsApp</label>
                <input wire:model="phone" type="text" placeholder="0812..." class="w-full rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500 border px-4 py-2">
            </div> --}}
            <button type="submit" class="bg-green-900 text-white font-bold py-2 px-6 rounded-lg hover:bg-green-700 transition">
                Cek Pesanan
            </button>
        </form>

        @if (session()->has('error'))
            <div class="mt-4 text-red-600 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif
    </div>

    @if($order)
        <div class="bg-white rounded-2xl shadow-lg border overflow-hidden">
            <div class="bg-gray-50 p-8 border-b">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-green-100 rounded-full text-green-900">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status Saat Ini:</p>
                            <span class="text-xl font-black uppercase {{ $order->status === 'pending' ? 'text-black' : ($order->status === 'processing' ? 'text-black':($order->status === 'completed' ? 'text-green-700' : 'text-red-700')) }}">
                                {{ $order->status === 'pending' ? 'Menunggu' : ($order->status === 'processing' ? 'Diproses' : ($order->status === 'completed' ? 'Selesai' : 'Dibatalkan')) }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Metode Bayar: <span class="font-bold text-gray-900">{{ strtoupper($order->payment_method) }}</span></p>
                        <p class="text-sm text-gray-500">Status Bayar: 
                            <span class="px-2 py-1 rounded text-xs font-bold {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <h3 class="font-bold text-gray-900 mb-4 text-lg">Ringkasan Barang</h3>
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex justify-between items-center border-b pb-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gray-100 rounded overflow-hidden">
                                    <img src="{{ $item->product->images->first() ? asset('storage/' . $item->product->images->first()->image_path) : 'https://via.placeholder.com/50' }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $item->product->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <p class="font-bold text-gray-900">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 pt-6 border-t flex justify-between items-center">
                    <span class="text-xl font-bold text-gray-900">Total Pembayaran</span>
                    <span class="text-2xl font-black text-green-600">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>
                
                @if($order->payment_status === 'unpaid')
                    <div class="mt-8">
                        <a href="{{ route('invoice', $order->invoice_number) }}" class="block w-full text-center bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition">
                            Klik di Sini untuk Menyelesaikan Pembayaran
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>