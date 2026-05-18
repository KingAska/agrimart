<?php

use Livewire\Component;
use App\Models\Order;

new class extends Component
{
    public Order $order;
    public $subtotal_items = 0;
    public $shipping_cost = 0;

    public function mount($invoice_number)
    {
        $this->order = Order::where('invoice_number', $invoice_number)
            ->with('items.product')
            ->firstOrFail();

        // 1. Hitung total harga murni dari barang belanjaan saja
        foreach ($this->order->items as $item) {
            $this->subtotal_items += $item->price * $item->quantity;
        }

        // 2. Ongkir adalah selisih mutlak dari Total Tagihan di database dikurangi Subtotal barang
        // Ini dijamin 100% konsisten mengikuti nominal asli dari halaman checkout!
        $this->shipping_cost = $this->order->total_price - $this->subtotal_items;
    }
};
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        
        <div class="bg-green-600 p-8 text-white text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h1 class="text-3xl font-extrabold">Pesanan Berhasil Dibuat!</h1>
            <p class="mt-2 text-green-100">Terima kasih, {{ $order->customer_name }}. Pesanan Anda sedang menunggu pembayaran.</p>
        </div>

        <div class="p-8">
            <div class="flex flex-col md:flex-row justify-between border-b pb-6 mb-6">
                <div>
                    <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Nomor Invoice</p>
                    <p class="text-2xl font-black text-gray-900">{{ $order->invoice_number }}</p>
                </div>
                <div class="mt-4 md:mt-0 md:text-right">
                    <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider">Tanggal Order</p>
                    <p class="text-lg font-bold text-gray-900">{{ $order->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-500 font-semibold uppercase tracking-wider mb-1">Alamat Pengiriman / Detail</p>
                <p class="text-base text-gray-850 font-medium">{{ $order->customer_address }}</p>
            </div>

            <div class="bg-gray-50 rounded-xl p-6 border border-gray-200 mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Instruksi Pembayaran</h3>
                
                @if($order->payment_method === 'manual')
                    <div class="space-y-4 text-gray-700">
                        <p>Silakan lakukan transfer tepat sebesar <strong>Rp {{ number_format($subtotal_items + $shipping_cost, 0, ',', '.') }}</strong> ke salah satu rekening berikut:</p>
                        
                        <div class="bg-white p-4 rounded-lg border flex items-center gap-4 shadow-sm">
                            <div class="bg-orange-100 text-orange-500 font-black px-4 py-2 rounded">SEABANK</div>
                            <div>
                                <p class="font-bold text-gray-900 text-lg">9015 4363 4296</p>
                                <p class="text-sm">a.n Muhammad Azam Askarulloh</p>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-lg border flex items-center gap-4 shadow-sm">
                            <div class="bg-blue-100 text-blue-800 font-black px-4 py-2 rounded">DANA</div>
                            <div>
                                <p class="font-bold text-gray-900 text-lg">0812 1318 7256</p>
                                <p class="text-sm">a.n Muhammad Azam Askarulloh</p>
                            </div>
                        </div>

                        <p class="text-sm mt-4 text-red-600 font-semibold">
                            *Setelah transfer, simpan bukti struk. Admin kami akan mengecek mutasi secara berkala dan mengubah status pesanan Anda.
                        </p>
                    </div>
                @else
                    @push('scripts')
                        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
                    @endpush

                    @if($order->payment_status === 'unpaid')
                        <button id="pay-button" class="bg-blue-600 text-white font-bold py-4 px-8 rounded-xl shadow-lg">
                            Bayar Sekarang (Midtrans)
                        </button>

                        <script>
                            const payButton = document.getElementById('pay-button');
                            payButton.addEventListener('click', function () {
                                window.snap.pay('{{ $order->snap_token }}', {
                                    onSuccess: function (result) {
                                        window.location.reload();
                                    },
                                    onPending: function (result) {
                                        window.location.reload();
                                    },
                                    onError: function (result) {
                                        alert("Pembayaran gagal!");
                                    }
                                });
                            });
                        </script>
                    @endif
                @endif
            </div>

            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Rincian Pesanan</h3>
            <div class="border rounded-lg p-4 mb-6 bg-white shadow-sm">
                <ul class="divide-y divide-gray-200">
                    @foreach($order->items as $item)
                        <li class="py-3 flex justify-between">
                            <div>
                                <span class="font-bold text-gray-900">{{ $item->product->name ?? 'Produk Dihapus' }}</span>
                                <span class="text-sm text-gray-500 block">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                            </div>
                            <span class="font-bold text-gray-900">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                        </li> 
                    @endforeach
                </ul>

                <div class="border-t border-gray-100 pt-4 mt-4 space-y-2 text-sm text-gray-650">
                    <div class="flex justify-between">
                        <span>Subtotal Produk</span>
                        <span class="font-semibold text-gray-900">Rp {{ number_format($subtotal_items, 0, ',', '.') }}</span>
                    </div>
                    
                    @if($shipping_cost > 0)
                        <div class="flex justify-between">
                            <span>Ongkos Kirim</span>
                            <span class="font-semibold text-orange-500">+ Rp {{ number_format($shipping_cost, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div class="flex justify-between text-green-600 font-medium">
                            <span>Metode Pengambilan</span>
                            <span>Ambil di Toko (Bebas Ongkir)</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-between items-center text-2xl font-black text-gray-900 bg-green-50 p-4 rounded-lg border border-green-200">
                <span>Total Tagihan</span>
                <span class="text-green-600">Rp {{ number_format($subtotal_items + $shipping_cost, 0, ',', '.') }}</span>
            </div>

        </div>
    </div>
</div>