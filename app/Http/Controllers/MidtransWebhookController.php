<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPaidNotification;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $serverKey = env('MIDTRANS_SERVER_KEY');

        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== ($payload['signature_key'] ?? '')) {
            Log::warning('Midtrans webhook signature tidak valid', $payload);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = Order::where('invoice_number', $orderId)->first();

        if (!$order) {
            Log::warning('Order tidak ditemukan: ' . $orderId);
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        if ($transactionStatus === 'capture' && $fraudStatus === 'accept') {
            $this->handlePaid($order);
        } elseif ($transactionStatus === 'settlement') {
            $this->handlePaid($order);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $order->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);
        } elseif ($transactionStatus === 'pending') {
            $order->update(['payment_status' => 'unpaid']);
        }

        Log::info('Midtrans webhook berhasil diproses', [
            'order_id' => $orderId,
            'status' => $transactionStatus,
        ]);

        return response()->json(['message' => 'OK'], 200);
    }

    private function handlePaid($order)
    {
        if ($order->payment_status === 'paid') return;

        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        // Kirim email ke customer
        try {
            if ($order->customer_email) {
                Mail::to($order->customer_email)->send(new OrderPaidNotification($order, 'customer'));
            }
            $adminEmail = env('CONTACT_SUPPORT_EMAIL', 'azz141095@gmail.com');
            Mail::to($adminEmail)->send(new OrderPaidNotification($order, 'admin'));
        } catch (\Exception $e) {
            Log::error('Gagal kirim email: ' . $e->getMessage());
        }

        // Kirim WhatsApp
        if (env('FONNTE_TOKEN')) {
            $this->sendWhatsAppNotification($order);
        }
    }

    private function sendWhatsAppNotification($order)
    {
        $customerMsg = "Halo {$order->customer_name}! 🌾\n\n"
            . "Terima kasih, pembayaran untuk pesanan *{$order->invoice_number}* sebesar *Rp "
            . number_format($order->total_price, 0, ',', '.')
            . "* telah kami terima.\n\n"
            . "Pesanan Anda sedang kami proses untuk segera dikirim.";

        $adminMsg = "🚨 *PESANAN BARU DIBAYAR!* 🚨\n\n"
            . "Invoice: {$order->invoice_number}\n"
            . "Nama: {$order->customer_name}\n"
            . "Total: Rp " . number_format($order->total_price, 0, ',', '.')
            . "\n\nSegera cek dashboard admin untuk proses pengiriman.";

        $adminPhone = env('CONTACT_WA_NUMBER', '6281213187256');

        try {
            $token = env('FONNTE_TOKEN');

            if (!$token) {
                Log::warning('FONNTE_TOKEN belum diatur di file .env');
                return;
            }

            if ($order->customer_phone) {
                Http::withHeaders(['Authorization' => $token])
                    ->post('https://api.fonnte.com/send', [
                        'target'  => $this->formatPhone($order->customer_phone),
                        'message' => $customerMsg,
                    ]);
            }

            Http::withHeaders(['Authorization' => $token])
                ->post('https://api.fonnte.com/send', [
                    'target'  => $this->formatPhone($adminPhone),
                    'message' => $adminMsg,
                ]);

        } catch (\Exception $e) {
            Log::error('Gagal kirim WhatsApp: ' . $e->getMessage());
        }
    }

    private function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }
}