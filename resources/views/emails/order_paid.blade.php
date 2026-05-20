<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    @if($type === 'admin')
        <h2>Halo Admin, ada pesanan masuk yang sudah dibayar!</h2>
    @else
        <h2>Halo {{ $order->customer_name }}, Pembayaran Berhasil! 🌾</h2>
        <p>Terima kasih telah berbelanja di Agrimart. Kami telah menerima pembayaran Anda dan pesanan Anda sedang kami proses.</p>
    @endif

    <div style="background-color: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin-top: 20px;">
        <p><strong>Nomor Invoice:</strong> {{ $order->invoice_number }}</p>
        <p><strong>Total Bayar:</strong> Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
        <p><strong>Metode Bayar:</strong> Midtrans (Lunas)</p>
    </div>

    @if($type === 'customer')
        <p style="margin-top: 20px;">Anda dapat mengecek status pengiriman pesanan Anda pada halaman Lacak Pesanan di website kami.</p>
    @endif
</body>
</html>