<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPaidNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $type;

    public function __construct($order, $type)
    {
        $this->order = $order;
        $this->type = $type;
    }

    public function build()
    {
        $subject = $this->type === 'admin' 
            ? "Pesanan Lunas: {$this->order->invoice_number}" 
            : "Pembayaran Berhasil: {$this->order->invoice_number}";

        return $this->subject($subject)
                    ->view('emails.order_paid');
    }
}