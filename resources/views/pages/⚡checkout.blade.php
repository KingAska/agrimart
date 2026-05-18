<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCreated;
use Illuminate\Support\Facades\Log;

new class extends Component
{
    public $cart = [];
    public $total = 0;
    public $grand_total = 0; // total belanja (tanpa ongkir)

    public $provinces = [];
    public $cities = [];

    #[Validate('required|min:3', message: 'Nama lengkap wajib diisi (min. 3 huruf)')]
    public $name = '';

    #[Validate('required|email', message: 'Email tidak valid')]
    public $email = '';

    #[Validate('required|numeric', message: 'Nomor HP harus berupa angka')]
    public $phone = '';

    #[Validate('required|in:pickup,delivery')]
    public $delivery_type = 'pickup';

    public $province_id = '';
    public $province_name = '';
    public $city_id = '';
    public $city_name = '';
    public $address = '';

    #[Validate('required|in:manual,midtrans')]
    public $payment_method = 'manual';

    public function mount()
    {
        $this->cart = Session::get('cart', []);
        
        if (empty($this->cart)) {
            return redirect()->route('home');
        }

        foreach ($this->cart as $item) {
            $this->total += $item['price'] * $item['quantity'];
        }
        $this->grand_total = $this->total;

        // Ambil data provinsi untuk keperluan form alamat
        try {
            $response = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
            if ($response->successful()) {
                $this->provinces = $response->json();
            }
        } catch (\Exception $e) {}
    }
    
    public function updatedProvinceId($value)
    {
        $this->cities = [];
        $this->city_id = '';
        $this->city_name = '';

        if ($value) {
            $selectedProv = collect($this->provinces)->firstWhere('id', $value);
            $this->province_name = $selectedProv['name'] ?? '';

            try {
                $response = Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$value}.json");
                if ($response->successful()) {
                    $this->cities = $response->json();
                }
            } catch (\Exception $e) {}
        }
    }

    public function updatedCityId($value)
    {
        if ($value) {
            $selectedCity = collect($this->cities)->firstWhere('id', $value);
            $this->city_name = $selectedCity['name'] ?? '';
        }
    }

    public function processCheckout()
    {
        $this->validate();

        if ($this->delivery_type === 'delivery') {
            $this->validate([
                'province_id' => 'required',
                'city_id' => 'required',
                'address' => 'required|min:10',
            ], [
                'province_id.required' => 'Provinsi wajib dipilih',
                'city_id.required' => 'Kota/Kabupaten wajib dipilih',
                'address.required' => 'Alamat jalan harus lengkap dan jelas',
                'address.min' => 'Alamat jalan harus lengkap dan jelas (min. 10 karakter)',
            ]);

            $fullAddress = $this->address . ', ' . $this->city_name . ', Provinsi ' . $this->province_name;
            $orderLatitude = null;   // Tidak pakai peta
            $orderLongitude = null;
        } else {
            $fullAddress = 'Ambil di Toko (Pickup)';
            $orderLatitude = null;
            $orderLongitude = null;
        }

        DB::beginTransaction();
        try {
            $orderData = [
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'total_price' => $this->grand_total,
                'customer_name' => $this->name,
                'customer_email' => $this->email,
                'customer_phone' => $this->phone,
                'customer_address' => $fullAddress,
                'payment_method' => $this->payment_method,
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'latitude' => $orderLatitude,
                'longitude' => $orderLongitude,
            ];

            // Jika tabel orders punya kolom delivery_type, aktifkan baris di bawah
            // $orderData['delivery_type'] = $this->delivery_type;

            $order = Order::create($orderData);

            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            if ($this->payment_method === 'midtrans') {
                Config::$serverKey = env('MIDTRANS_SERVER_KEY');
                Config::$isProduction = env('MIDTRANS_IS_PRODUCTION') ?? false;
                Config::$isSanitized = true;
                Config::$is3ds = true;

                $params = [
                    'transaction_details' => [
                        'order_id' => $order->invoice_number,
                        'gross_amount' => (int) $order->total_price,
                    ],
                    'customer_details' => [
                        'first_name' => $order->customer_name,
                        'email' => $order->customer_email,
                        'phone' => $order->customer_phone,
                    ],
                ];

                $snapToken = Snap::getSnapToken($params);
                $order->update(['snap_token' => $snapToken]);
            }

            DB::commit();

            try {
                Mail::to($order->customer_email)->send(new OrderCreated($order));
            } catch (\Exception $e) {
                Log::error('Gagal mengirim email pesanan: ' . $e->getMessage());
            }

            Session::forget('cart');
            $this->dispatch('cart-updated');

            return redirect()->route('invoice', ['invoice_number' => $order->invoice_number]);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900">Checkout Pesanan</h1>
        <p class="text-gray-500 mt-2">Lengkapi data pengiriman dan pilih metode pembayaran.</p>
    </div>

    @if (session()->has('error'))
        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="w-full lg:w-2/3 bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 border-b pb-4">Informasi Pengiriman</h2>
            
            <form wire:submit="processCheckout" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                        <input wire:model="name" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border">
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. WhatsApp / HP</label>
                        <input wire:model="phone" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border">
                        @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input wire:model="email" type="email" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border">
                    @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Pesanan</label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="delivery_type" value="pickup" class="w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300">
                            <span class="text-sm font-medium text-gray-900">Ambil di Toko (Pickup)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="delivery_type" value="delivery" class="w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300">
                            <span class="text-sm font-medium text-gray-900">Antar Pengiriman</span>
                        </label>
                    </div>
                    @error('delivery_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                @if($delivery_type === 'delivery')
                    <div class="space-y-6 pt-4 border-t border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Provinsi</label>
                                <select wire:model.live="province_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border bg-white">
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinces as $prov)
                                        <option value="{{ $prov['id'] }}">{{ $prov['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('province_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kota / Kabupaten</label>
                                <select wire:model.live="city_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border bg-white" {{ empty($cities) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Kota/Kabupaten --</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('city_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Detail Alamat Jalan</label>
                            <textarea wire:model="address" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border" placeholder="Nama Jalan, Gedung, RT/RW, Kelurahan, Kecamatan, Kode Pos..."></textarea>
                            @error('address') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endif

                <h2 class="text-xl font-bold text-gray-900 mb-6 border-b pb-4 mt-10">Metode Pembayaran</h2>
                
                <div class="space-y-4">
                    <label class="flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition {{ $payment_method === 'manual' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                        <input wire:model.live="payment_method" type="radio" value="manual" class="w-5 h-5 text-green-600 focus:ring-green-500">
                        <div class="ml-4 grow">
                            <span class="block font-bold text-gray-900">Transfer Bank Manual</span>
                            <span class="block text-sm text-gray-500">Verifikasi dilakukan oleh admin (BCA, Mandiri, BRI).</span>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition {{ $payment_method === 'midtrans' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                        <input wire:model.live="payment_method" type="radio" value="midtrans" class="w-5 h-5 text-green-600 focus:ring-green-500">
                        <div class="ml-4 grow">
                            <span class="block font-bold text-gray-900">Bayar Otomatis (Midtrans)</span>
                            <span class="block text-sm text-gray-500">QRIS, Virtual Account, e-Wallet (OVO, GoPay, dll). Konfirmasi otomatis.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-green-600 text-white font-extrabold text-lg py-4 px-4 rounded-xl hover:bg-green-700 transition-colors shadow-lg">
                        Buat Pesanan Sekarang
                    </button>
                </div>
            </form>
        </div>

        <div class="w-full lg:w-1/3">
            <div class="bg-gray-100 rounded-xl shadow-inner border border-gray-200 p-6 sticky top-24">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Barang Belanjaan</h2>
                <ul class="divide-y divide-gray-300 mb-6">
                    @foreach($cart as $item)
                        <li class="py-4 flex justify-between">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900">{{ $item['name'] }}</span>
                                <span class="text-sm text-gray-600">{{ $item['quantity'] }} x Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                            </div>
                            <span class="font-bold text-gray-900">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="flex justify-between text-xl font-black text-gray-900 border-t border-gray-300 pt-4">
                    <span>Total Tagihan</span>
                    <span class="text-green-600">Rp {{ number_format($grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>