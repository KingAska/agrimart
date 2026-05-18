<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\OrderPaidNotification;

class PaymentCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed == $request->signature_key) {
            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                
                $order = Order::where('invoice_number', $request->order_id)->first();

                if ($order && $order->payment_status !== 'paid') {
                    
                    $order->update([
                        'payment_status' => 'paid', 
                        'status' => 'processing'
                    ]);

                    if ($order->customer_email) {
                        Mail::to($order->customer_email)->send(new OrderPaidNotification($order, 'customer'));
                    }

                    $adminEmail = env('CONTACT_SUPPORT_EMAIL', 'azz141095@gmail.com');
                    Mail::to($adminEmail)->send(new OrderPaidNotification($order, 'admin'));

                    if (env('FONNTE_TOKEN')) {
                        $this->sendWhatsAppNotification($order);
                    }
                }
            }
        }

        return response()->json(['message' => 'Callback received successfully']);
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
            \Log::warning('FONNTE_TOKEN belum diatur di file .env');
            return;
        }

        // Kirim ke customer
        if ($order->customer_phone) {

            Http::withHeaders([
                'Authorization' => $token
            ])->post('https://api.fonnte.com/send', [
                'target'  => $this->formatPhone($order->customer_phone),
                'message' => $customerMsg,
            ]);
        }

        // Kirim ke admin
        Http::withHeaders([
            'Authorization' => $token
        ])->post('https://api.fonnte.com/send', [
            'target'  => $this->formatPhone($adminPhone),
            'message' => $adminMsg,
        ]);

    } catch (\Exception $e) {

        \Log::error(
            "Gagal mengirim notifikasi WhatsApp: " . $e->getMessage()
        );
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