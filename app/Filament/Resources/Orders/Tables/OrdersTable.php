<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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

                TextColumn::make('items.product.name')
                    ->label('Produk')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->searchable(),

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
                ActionGroup::make([
                    ViewAction::make()->color('gray'),
                    EditAction::make()->color('primary'),

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

                    DeleteAction::make()
                        ->label('Hapus Pesanan')
                        ->visible(fn ($record) => 
                            $record->payment_status !== 'paid' && 
                            in_array($record->status, ['pending', 'cancelled'])
                        ),
                ])
                ->color('secondary')
                ->icon('heroicon-m-ellipsis-vertical')
                ->link()
                ->label('Set')
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}