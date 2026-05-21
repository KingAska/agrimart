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
    public $grand_total = 0;

    public $provinces = [];
    public $cities = [];
    public $districts = [];

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
    public $district_id = '';
    public $district_name = '';
    public $address = '';

    public $courier = '';
    public $shipping_services = [];
    public $selected_service = '';
    public $shipping_cost = 0;
    public $selected_service_label = '';

    #[Validate('required|in:manual,midtrans')]
    public $payment_method = 'manual';

    protected function rajaongkirHeaders(): array
    {
        return ['key' => env('RAJAONGKIR_API_KEY')];
    }

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

    $this->loadProvinces();

}

protected function loadProvinces()
{
    if ($this->delivery_type === 'pickup') {
        // Emsifa - gratis unlimited
        try {
            $response = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
            if ($response->successful()) {
                $this->provinces = $response->json();
            }
        } catch (\Exception $e) {}
    } else {
        // RajaOngkir - cache 24 jam
        $this->provinces = \Illuminate\Support\Facades\Cache::remember('rajaongkir_provinces', 86400, function () {
            try {
                $response = Http::withHeaders($this->rajaongkirHeaders())
                    ->get('https://rajaongkir.komerce.id/api/v1/destination/province');
                if ($response->successful()) {
                    return $response->json()['data'] ?? [];
                }
            } catch (\Exception $e) {}
            return [];
        });
    }
}

public function updatedDeliveryType($value)
{
    // Reset semua saat ganti tipe
    $this->provinces = [];
    $this->cities = [];
    $this->districts = [];
    $this->province_id = '';
    $this->province_name = '';
    $this->city_id = '';
    $this->city_name = '';
    $this->district_id = '';
    $this->district_name = '';
    $this->shipping_services = [];
    $this->shipping_cost = 0;
    $this->grand_total = $this->total;
    $this->loadProvinces();
}

public function updatedProvinceId($value)
{
    $this->cities = [];
    $this->city_id = '';
    $this->city_name = '';
    $this->districts = [];
    $this->district_id = '';
    $this->shipping_services = [];
    $this->shipping_cost = 0;
    $this->grand_total = $this->total;

    if ($value) {
        if ($this->delivery_type === 'pickup') {
            $selected = collect($this->provinces)->firstWhere('id', $value);
            $this->province_name = $selected['name'] ?? '';

            try {
                $response = Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$value}.json");
                if ($response->successful()) {
                    $this->cities = $response->json();
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Emsifa error: ' . $e->getMessage());
            }

        } else {
            $selected = collect($this->provinces)->firstWhere('id', $value);
            $this->province_name = $selected['name'] ?? '';

            $this->cities = \Illuminate\Support\Facades\Cache::remember("rajaongkir_cities_{$value}", 86400, function () use ($value) {
                try {
                    $response = Http::withHeaders($this->rajaongkirHeaders())
                        ->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$value}");
                    if ($response->successful()) {
                        return $response->json()['data'] ?? [];
                    }
                } catch (\Exception $e) {}
                return [];
            });
        }
    }
}

public function updatedCityId($value)
{
    $this->districts = [];
    $this->district_id = '';
    $this->shipping_services = [];
    $this->shipping_cost = 0;
    $this->grand_total = $this->total;

    if ($value) {
        if ($this->delivery_type === 'pickup') {
            // Emsifa - tidak perlu kecamatan untuk pickup
            $selected = collect($this->cities)->firstWhere('id', $value);
            $this->city_name = $selected['name'] ?? '';
        } else {
            // RajaOngkir
            $selected = collect($this->cities)->firstWhere('id', $value);
            $this->city_name = $selected['name'] ?? '';

            $this->districts = \Illuminate\Support\Facades\Cache::remember("rajaongkir_districts_{$value}", 86400, function () use ($value) {
                try {
                    $response = Http::withHeaders($this->rajaongkirHeaders())
                        ->get("https://rajaongkir.komerce.id/api/v1/destination/district/{$value}");
                    if ($response->successful()) {
                        return $response->json()['data'] ?? [];
                    }
                } catch (\Exception $e) {}
                return [];
            });
        }
    }
}

    public function updatedDistrictId($value)
    {
        $this->shipping_services = [];
        $this->shipping_cost = 0;
        $this->grand_total = $this->total;

        if ($value) {
            $selected = collect($this->districts)->firstWhere('id', $value);
            $this->district_name = $selected['name'] ?? '';
        }
    }

  public function checkOngkir()
{
    if (!$this->district_id || !$this->courier) {
        session()->flash('ongkir_error', 'Pilih kecamatan dan kurir terlebih dahulu.');
        return;
    }

    try {
        $response = Http::withHeaders($this->rajaongkirHeaders())
            ->asForm()
            ->post('https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost', [
                'origin'      => (int) env('RAJAONGKIR_ORIGIN'),
                'destination' => (int) $this->district_id,
                'weight'      => 1000,
                'courier'     => $this->courier,
                'price'       => 'lowest',
            ]);

        if ($response->successful()) {
    $this->shipping_services = $response->json()['data'] ?? [];
    $this->selected_service = '';
    $this->shipping_cost = 0;
    $this->grand_total = $this->total;
        } else {
    session()->flash('ongkir_error', 'Response: ' . json_encode($response->json()));
}
    } catch (\Exception $e) {
        session()->flash('ongkir_error', 'Error: ' . $e->getMessage());
    }
}

    public function selectService($service, $cost, $label)
    {
        $this->selected_service = $service;
        $this->selected_service_label = $label;
        $this->shipping_cost = $cost;
        $this->grand_total = $this->total + $cost;
    }

    public function processCheckout()
    {
        $rules = [
            'name'    => 'required|min:3',
            'email'   => 'required|email',
            'phone'   => 'required|numeric',
            'address' => 'required|min:10',
        ];

        $messages = [
            'address.required' => 'Alamat wajib diisi',
            'address.min'      => 'Alamat minimal 10 karakter',
        ];

        if ($this->delivery_type === 'delivery') {
            $rules['province_id']      = 'required';
            $rules['city_id']          = 'required';
            $rules['district_id']      = 'required';
            $rules['courier']          = 'required';
            $rules['selected_service'] = 'required';

            $messages['province_id.required']      = 'Provinsi wajib dipilih';
            $messages['city_id.required']          = 'Kota wajib dipilih';
            $messages['district_id.required']      = 'Kecamatan wajib dipilih';
            $messages['courier.required']          = 'Kurir wajib dipilih';
            $messages['selected_service.required'] = 'Layanan pengiriman wajib dipilih, klik Cek Ongkir dulu';
        }

        $this->validate($rules, $messages);

        $fullAddress = $this->delivery_type === 'pickup'
    ? $this->address . ', ' . $this->city_name . ', Provinsi ' . $this->province_name
    : $this->address . ', Kec. ' . $this->district_name . ', ' . $this->city_name . ', Provinsi ' . $this->province_name;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'invoice_number'   => 'INV-' . strtoupper(Str::random(8)),
                'total_price'      => $this->grand_total,
                'customer_name'    => $this->name,
                'customer_email'   => $this->email,
                'customer_phone'   => $this->phone,
                'customer_address' => $fullAddress,
                'payment_method'   => $this->payment_method,
                'payment_status'   => 'unpaid',
                'status'           => 'pending',
                'shipping_courier' => $this->delivery_type === 'delivery'
                    ? strtoupper($this->courier) . ' - ' . $this->selected_service_label
                    : 'Pickup',
                'shipping_cost'    => $this->shipping_cost,
                'latitude'         => null,
                'longitude'        => null,
            ]);

            foreach ($this->cart as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                ]);
            }

            if ($this->payment_method === 'midtrans') {
                Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
                Config::$isProduction = env('MIDTRANS_IS_PRODUCTION') ?? false;
                Config::$isSanitized  = true;
                Config::$is3ds        = true;

                $snapToken = Snap::getSnapToken([
                    'transaction_details' => [
                        'order_id'     => $order->invoice_number,
                        'gross_amount' => (int) $order->total_price,
                    ],
                    'customer_details' => [
                        'first_name' => $order->customer_name,
                        'email'      => $order->customer_email,
                        'phone'      => $order->customer_phone,
                    ],
                ]);
                $order->update(['snap_token' => $snapToken]);
            }

            DB::commit();

            try {
                Mail::to($order->customer_email)->send(new OrderCreated($order));
            } catch (\Exception $e) {
                Log::error('Gagal mengirim email: ' . $e->getMessage());
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

<div> {{-- ROOT ELEMENT --}}
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

        {{-- KIRI: Form --}}
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
                            <input type="radio" wire:model.live="delivery_type" value="pickup" class="w-5 h-5 text-green-600">
                            <span class="text-sm font-medium">Ambil di Toko (Pickup)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="delivery_type" value="delivery" class="w-5 h-5 text-green-600">
                            <span class="text-sm font-medium">Antar Pengiriman</span>
                        </label>
                    </div>
                </div>

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
                            {{-- Tambah ini sementara untuk debug --}}
                            <p class="text-xs text-gray-400">Jumlah provinsi: {{ count($provinces) }}</p>
                            @error('province_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kota / Kabupaten</label>
                            <select wire:model.live="city_id" 
    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border bg-white {{ empty($cities) ? 'opacity-50 cursor-not-allowed' : '' }}">
    <option value="">-- Pilih Kota/Kabupaten --</option>
    @foreach($cities as $city)
        <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
    @endforeach
</select>
                            @error('city_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Kecamatan - hanya untuk delivery --}}
                    @if($delivery_type === 'delivery')
                    <div>
                         <label class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                            <select wire:model.live="district_id"
                     class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border bg-white {{ empty($districts) ? 'opacity-50 cursor-not-allowed' : '' }}">
                 <option value="">-- Pilih Kecamatan --</option>
                     @foreach($districts as $district)
                 <option value="{{ $district['id'] }}">{{ $district['name'] }}</option>
                      @endforeach
                </select>
                     @error('district_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Detail Alamat Jalan</label>
                        <textarea wire:model="address" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border" placeholder="Nama Jalan, RT/RW, Kelurahan, Kode Pos..."></textarea>
                        @error('address') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if($delivery_type === 'delivery')
                <div class="pt-4 border-t border-gray-100 space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Cek Ongkos Kirim</h3>

                    @if(session()->has('ongkir_error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded text-sm">
                            {{ session('ongkir_error') }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kurir</label>
                        <select wire:model="courier" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 px-4 py-2 border bg-white">
                            <option value="">-- Pilih Kurir --</option>
                            <option value="jne">JNE</option>
                            <option value="tiki">TIKI</option>
                            <option value="pos">POS Indonesia</option>
                            <option value="jnt">J&T Express</option>
                            <option value="sicepat">SiCepat</option>
                            <option value="anteraja">AnterAja</option>
                        </select>
                        @error('courier') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <button type="button" wire:click="checkOngkir" wire:loading.attr="disabled"
                        class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-blue-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="checkOngkir">Cek Ongkir</span>
                        <span wire:loading wire:target="checkOngkir">Mengecek...</span>
                    </button>

                    @if(!empty($shipping_services))
<div class="space-y-2">
    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Layanan Pengiriman</label>
    @foreach($shipping_services as $service)
        <label class="flex items-center justify-between p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition {{ $selected_service === $service['code'].'_'.$service['service'] ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
            <div class="flex items-center gap-3">
                <input type="radio"
                    wire:click="selectService('{{ $service['code'].'_'.$service['service'] }}', {{ $service['cost'] }}, '{{ $service['service'] }} - {{ $service['description'] }}')"
                    class="w-4 h-4 text-green-600"
                    {{ $selected_service === $service['code'].'_'.$service['service'] ? 'checked' : '' }}>
                <div>
                    <span class="font-bold text-gray-900">{{ $service['name'] }} {{ $service['service'] }}</span>
                    <span class="block text-xs text-gray-500">{{ $service['description'] }}{{ $service['etd'] ? ' • Estimasi '.$service['etd'].' hari' : '' }}</span>
                </div>
            </div>
            <span class="font-bold text-green-700">Rp {{ number_format($service['cost'], 0, ',', '.') }}</span>
        </label>
    @endforeach
    @error('selected_service') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
</div>
@endif
                </div>
                @endif

                <h2 class="text-xl font-bold text-gray-900 mb-6 border-b pb-4 mt-10">Metode Pembayaran</h2>
                <div class="space-y-4">
                    <label class="flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition {{ $payment_method === 'manual' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                        <input wire:model.live="payment_method" type="radio" value="manual" class="w-5 h-5 text-green-600">
                        <div class="ml-4 grow">
                            <span class="block font-bold text-gray-900">Transfer Bank Manual</span>
                            <span class="block text-sm text-gray-500">Verifikasi dilakukan oleh admin (BCA, Mandiri, BRI).</span>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition {{ $payment_method === 'midtrans' ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                        <input wire:model.live="payment_method" type="radio" value="midtrans" class="w-5 h-5 text-green-600">
                        <div class="ml-4 grow">
                            <span class="block font-bold text-gray-900">Bayar Otomatis (Midtrans)</span>
                            <span class="block text-sm text-gray-500">QRIS, Virtual Account, e-Wallet. Konfirmasi otomatis.</span>
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

        {{-- KANAN: Ringkasan --}}
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
                <div class="flex justify-between text-sm text-gray-700 border-t border-gray-300 pt-4">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                @if($shipping_cost > 0)
                <div class="flex justify-between text-sm text-gray-700 mt-2">
                    <span>Ongkos Kirim</span>
                    <span>Rp {{ number_format($shipping_cost, 0, ',', '.') }}</span>
                </div>
                @elseif($delivery_type === 'pickup')
                <div class="flex justify-between text-sm text-gray-700 mt-2">
                    <span>Ongkos Kirim</span>
                    <span class="text-green-600 font-bold">Gratis (Pickup)</span>
                </div>
                @endif
                <div class="flex justify-between text-xl font-black text-gray-900 border-t border-gray-300 pt-4 mt-2">
                    <span>Total Tagihan</span>
                    <span class="text-green-600">Rp {{ number_format($grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
</div> {{-- TUTUP ROOT ELEMENT --}}