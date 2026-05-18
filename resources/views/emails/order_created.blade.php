<!DOCTYPE html>
<html>
<head>
    <title>Pesanan Dibuat</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
    <h2 style="color: #16a34a;">Halo {{ $order->customer_name }}, Terima Kasih! 🌾</h2>
    <p>Pesanan Anda telah berhasil dibuat. Berikut adalah rincian pesanan Anda:</p>

    <div style="background-color: #f9f9f9; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 10px 0;"><strong>Nomor Invoice:</strong> <span style="color: #16a34a; font-weight: bold;">{{ $order->invoice_number }}</span></p>
        <p style="margin: 0 0 10px 0;"><strong>Metode Pembayaran:</strong> {{ $order->payment_method === 'midtrans' ? 'Bayar Otomatis (Midtrans)' : 'Transfer Manual' }}</p>
        <p style="margin: 0 0 10px 0;"><strong>Status Pembayaran:</strong> <span style="color: #ef4444; font-weight: bold;">BELUM DIBAYAR</span></p>
        <hr style="border: 0; border-top: 1px solid #ddd; margin: 15px 0;">
        <p style="margin: 0 0 5px 0;"><strong>Alamat Pengiriman:</strong></p>
        <p style="margin: 0;">{{ $order->customer_address }}</p>
    </div>

    <h3>Rincian Barang Belanjaan:</h3>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f3f4f6; border-bottom: 2px solid #ddd;">
                <th style="padding: 10px; text-align: left;">Produk</th>
                <th style="padding: 10px; text-align: center;">Qty</th>
                <th style="padding: 10px; text-align: right;">Harga</th>
            </tr>
        </thead>
        <tbody>
            @php
                $subtotal_items = 0; 
            @endphp
            @foreach($order->items as $item)
            @php 
                $subtotal_items += $item->price * $item->quantity; 
            @endphp
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px;">{{ $item->product->name }}</td>
                <td style="padding: 10px; text-align: center;">{{ $item->quantity }}</td>
                <td style="padding: 10px; text-align: right;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="padding: 8px 10px; text-align: right; color: #555; font-size: 14px;">Subtotal Produk:</td>
                <td style="padding: 8px 10px; text-align: right; color: #333; font-size: 14px; font-weight: bold;">Rp {{ number_format($subtotal_items, 0, ',', '.') }}</td>
            </tr>
            <tr style="border-top: 2px solid #ddd;">
                <td colspan="2" style="padding: 15px 10px; text-align: right; font-weight: bold; font-size: 18px;">Total Tagihan:</td>
                <td style="padding: 15px 10px; text-align: right; font-weight: bold; font-size: 18px; color: #16a34a;">Rp {{ number_format($subtotal_items, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('invoice', ['invoice_number' => $order->invoice_number]) }}" style="background-color: #16a34a; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
            Selesaikan Pembayaran Sekarang
        </a>
    </div>
    
    <p style="margin-top: 30px; font-size: 12px; color: #888;">Jika Anda sudah melakukan pembayaran, abaikan pesan ini. Terima kasih telah berbelanja di Agrimart.</p>
</body>
</html>