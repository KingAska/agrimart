<?php

namespace App\Filament\Resources\Orders\Schemas;

// use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
// use Filament\Notifications\Notification;
// use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
// use Illuminate\Support\Facades\Http;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        $calculateTotal = function (Get $get, Set $set) {
            $items = $get('../../items') ?? [];
            $total = 0;
            foreach ($items as $item) {
                $total += (intval($item['quantity'] ?? 1) * floatval($item['price'] ?? 0));
            }
            $set('../../total_price', $total);
        };

        return $schema
            ->components([
                Group::make()->schema([
Section::make('Informasi Pelanggan')->schema([
    TextInput::make('customer_name')
        ->label('Nama Lengkap (wajib)')
        ->required(),
    TextInput::make('customer_email')
        ->label('Email')
        ->email(),
    TextInput::make('customer_phone')
        ->label('No. WhatsApp / HP'),
    Radio::make('delivery_type')
        ->label('Tipe Pesanan')
        ->options([
            'pickup' => 'Ambil di Toko (Pickup)',
            'delivery' => 'Antar Pengiriman',
        ])
        ->required()
        ->inline()
        ->live()
        ->columnSpanFull(),
    TextInput::make('shipping_courier')
    ->label('Kurir / Layanan Pengiriman')
    ->disabled()  // atau required() jika ingin bisa edit
    ->columnSpanFull(),

    // Alamat SELALU ditampilkan (tanpa kondisi visible)
    Textarea::make('customer_address')
        ->label('Alamat (untuk pickup dan delivery)')
        ->required()
        ->columnSpanFull(),
])->columns(2),

                    Section::make('Produk yang Dipesan')->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $items = $get('items') ?? [];
                                $total = 0;
                                foreach ($items as $item) {
                                    $total += (intval($item['quantity'] ?? 1) * floatval($item['price'] ?? 0));
                                }
                                $set('total_price', $total);
                            })
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->label('Produk')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - Rp " . number_format($record->price, 0, ',', '.'))
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) use ($calculateTotal) {
                                        $product = \App\Models\Product::find($state);
                                        $set('price', $product?->price ?? 0);
                                        $calculateTotal($get, $set);
                                    })
                                    ->columnSpan(2),

                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated($calculateTotal)
                                    ->columnSpan(1),

                                TextInput::make('price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live(debounce: 500)
                                    ->afterStateUpdated($calculateTotal)
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->itemLabel(fn (array $state): ?string => 'Item Pesanan')
                            ->addable() 
                            ->deletable()
                            ->reorderable(),
                    ]),
                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([
                    Section::make('Status & Pembayaran')->schema([
                        TextInput::make('invoice_number')
                            ->label('No. Invoice')
                            ->default('INV-' . strtoupper(\Illuminate\Support\Str::random(8)))
                            ->readOnly(), 
                        
                        TextInput::make('total_price')
                            ->label('Total Belanja')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Otomatis dihitung berdasarkan item produk di samping.')
                            ->readOnly(), 

                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'manual' => 'Transfer Manual',
                                'midtrans' => 'Midtrans (Otomatis)',
                            ])
                            ->default('manual')
                            ->required(),

                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'unpaid' => 'Belum Dibayar',
                                'paid' => 'Lunas',
                                'failed' => 'Gagal / Batal',
                                'expired' => 'Kedaluwarsa',
                            ])
                            ->default('unpaid')
                            ->required(),

                        Select::make('status')
                            ->label('Status Pesanan')
                            ->options([
                                'pending' => 'Menunggu Diproses',
                                'processing' => 'Sedang Diproses/Dikemas',
                                'completed' => 'Selesai/Dikirim',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),
                ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}