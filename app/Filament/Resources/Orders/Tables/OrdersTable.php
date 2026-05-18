<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use PhpParser\Node\Stmt\Label;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable(),

                // --- INI ADALAH TAMBAHAN UNTUK MEMUNCULKAN PRODUK ---
                TextColumn::make('items.product.name')
                    ->label('Produk')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->searchable(),
                // ---------------------------------------------------

                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                    
                TextColumn::make('payment_method')
                    ->badge()
                    ->label('Metode')
                    ->colors([
                        'primary' => 'manual',
                        'success' => 'midtrans',
                    ]),
                    
                TextColumn::make('payment_status')
                    ->badge()
                    ->label('Pembayaran')
                    ->colors([
                        'danger' => 'unpaid',
                        'success' => 'paid',
                        'warning' => 'failed',
                    ]),
                    
                TextColumn::make('status')
                    ->badge()
                    ->label('Status Order')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                    
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Diproses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('Filter Pembayaran')
                    ->options([
                        'unpaid' => 'Belum Dibayar',
                        'paid' => 'Lunas',
                        'failed' => 'Gagal / Batal',
                    ]),
            ])
            ->recordActions([
                // MENGGUNAKAN ACTION GROUP AGAR TABEL RAPI
                ActionGroup::make([
                    ViewAction::make()->color('gray'),
                    EditAction::make()->color('primary'),

                    // 1. LIHAT ALAMAT & PETA
                    Action::make('lihat_alamat')
                        ->label('Lihat Lokasi')
                        ->icon('heroicon-o-map-pin')
                        ->color('info')
                        ->modalHeading('Lokasi Pengiriman')
                        ->modalSubmitAction(false) // Hilangkan tombol submit karena ini hanya view
                        ->modalCancelActionLabel('Tutup')
                        ->form([
                            Placeholder::make('customer_address')
                                ->label('Detail Alamat:')
                                ->content(fn ($record) => $record->customer_address),
                                
                            Placeholder::make('map')
                                ->label('Titik Peta (Otomatis):')
                                ->content(function ($record) {
                                    // Mencegah error jika user tidak pin lokasi
                                    if (!$record->latitude || !$record->longitude) {
                                        return new HtmlString('<span class="text-red-500">Koordinat peta belum diset oleh pelanggan.</span>');
                                    }

                                    return new HtmlString("
                                        <div style='border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; margin-bottom: 1rem;'>
                                            <iframe width='100%' height='300' frameborder='0' scrolling='no' marginheight='0' marginwidth='0' 
                                                src='https://maps.google.com/maps?q={$record->latitude},{$record->longitude}&hl=id&z=16&output=embed'>
                                            </iframe>
                                        </div>
                                        <a href='https://www.google.com/maps/search/?api=1&query={$record->latitude},{$record->longitude}' 
                                           target='_blank' 
                                           style='display: flex; justify-content: center; align-items: center; gap: 8px; background-color: #16a34a; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: bold; width: 100%; transition: all 0.3s;'>
                                            <svg style='width: 20px; height: 20px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 11a3 3 0 11-6 0 3 3 0 016 0z'></path></svg>
                                            Buka di Aplikasi Google Maps
                                        </a>
                                    ");
                                }),
                        ]),

                    // 2. UBAH STATUS PESANAN (PROSES / SELESAI / BATAL)
                    Action::make('ubah_status_pesanan')
                    ->visible(fn ($record) => $record->status !== 'completed')
                        ->label('Ubah Status Pesanan')
                        ->icon('heroicon-o-truck')
                        ->color('warning')
                        ->form([
                            Select::make('status')
                                ->label('Pilih Status Pesanan Baru')
                                ->options([
                                    'pending' => 'Menunggu Diproses',
                                    'processing' => 'Sedang Diproses/Dikemas',
                                    'completed' => 'Selesai/Dikirim',
                                    'cancelled' => 'Dibatalkan',
                                ])
                                ->default(fn ($record) => $record->status)
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update(['status' => $data['status']]);
                        }),

                    // 3. UBAH STATUS PEMBAYARAN
                    Action::make('ubah_status_pembayaran')
                    ->visible(fn ($record) => $record->payment_status !== 'paid')
                        ->label('Ubah Status Pembayaran')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->form([
                            Select::make('payment_status')
                                ->label('Pilih Status Pembayaran')
                                ->options([
                                    'unpaid' => 'Belum Dibayar',
                                    'paid' => 'Lunas',
                                    'failed' => 'Gagal / Batal',
                                ])
                                ->default(fn ($record) => $record->payment_status)
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update(['payment_status' => $data['payment_status']]);
                        }),

                    // 4. HAPUS PESANAN (DENGAN KONDISI KHUSUS)
                    DeleteAction::make()
                        ->label('Hapus Pesanan')
                        // Logika: Hanya muncul JIKA payment_status BUKAN 'paid' 
                        // DAN status pesanan masih 'pending' atau sudah 'cancelled'
                        ->visible(fn ($record) => 
                            $record->payment_status !== 'paid' && 
                            in_array($record->status, ['pending', 'cancelled'])
                        ),
                ])
                ->color('secondary')
                ->icon('heroicon-m-ellipsis-vertical')
                ->link()
                ->label('Aksi')
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}