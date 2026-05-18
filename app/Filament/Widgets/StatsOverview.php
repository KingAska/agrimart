<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        // Hitung total pendapatan dari pesanan yang sudah dibayar (Lunas)
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_price');

        return [
            Stat::make('Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Dari pesanan yang sudah lunas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Grafik dummy agar tampilan lebih cantik

            Stat::make('Total Pesanan', Order::count())
                ->description('Keseluruhan pesanan masuk')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Produk Aktif', Product::where('is_active', true)->count())
                ->description('Katalog yang tampil di web')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('info'),
        ];
    }
}