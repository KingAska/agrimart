<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget; // ✅ BENAR
use Filament\Actions\Action;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column; // ✅ TAMBAHKAN INI
use Filament\Tables;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LatestOrders extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Pesanan Terbaru';

    protected static ?int $sort = 2;

    // ✅ INI YANG BENAR (bukan getHeaderActions)
    protected function getTableHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Export')   
                ->exports([
                    ExcelExport::make()
                        ->modifyQueryUsing(function ($query) {
                            // ✅ Tambahkan ->with('items.product') agar loading data lebih cepat (mencegah N+1 query)
                            return $query->with('items.product')->where('created_at', '>=', now()->subMonth());
                        })
                        ->withColumns([
                            // ✅ PILIH KOLOM YANG MAU DI-EXPORT DI SINI
                            Column::make('invoice_number')->heading('Invoice'),
                            Column::make('customer_name')->heading('Nama Pelanggan'),
                            Column::make('produk_dibeli')
                                ->heading('Produk yang Dibeli')
                                ->getStateUsing(function ($record) {
                                    return $record->items->map(function ($item) {
                                        $namaProduk = $item->product ? $item->product->name : 'Produk Tidak Ditemukan';
                                        return $namaProduk . ' (' . $item->quantity . ' qty)';
                                    })->implode(', ');
                                }),

                            Column::make('subtotal_items')
                            ->heading('Total Belanja (Produk)')
                            ->getStateUsing(function ($record) {
                            $subtotal = $record->items->sum(fn($item) => $item->price * $item->quantity);
                              return 'Rp ' . number_format($subtotal, 0, ',', '.');
                            }),
                            Column::make('customer_phone')->heading('No. WhatsApp/HP'),
                            Column::make('customer_address')->heading('Alamat Pengiriman'),
                            Column::make('status')->heading('Status Pesanan'),
                            Column::make('created_at')
                                ->heading('Tanggal Pesanan')
                                ->getStateUsing(fn ($record) => $record->created_at->format('d M Y H:i')),
                        ]),
                ]),
        ];
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('invoice_number')->label('Invoice')->searchable(),
                TextColumn::make('customer_name')->label('Pelanggan'),

                // --- TAMBAHAN KOLOM PRODUK ---
                TextColumn::make('items.product.name')
                    ->label('Produk')
                    ->listWithLineBreaks()
                    ->limitList(2) // Dibatasi 2 agar tampilan widget tidak terlalu panjang
                    ->expandableLimitedList(),
                // -----------------------------

                TextColumn::make('total_price')->label('Total Belanja')->money('IDR'),
                TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        'failed' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('Lihat')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record))
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated(false);
    }
}