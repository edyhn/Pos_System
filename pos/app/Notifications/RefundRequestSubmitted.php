<?php

namespace App\Notifications;

use App\Models\RefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(public RefundRequest $refundRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('approvals.refund');

        return (new MailMessage)
            ->subject('Request Refund Baru')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ada request refund baru dari kasir yang perlu disetujui.')
            ->line('Transaksi: ' . $this->refundRequest->transaction->invoice_number)
            ->line('Kondisi: ' . $this->refundRequest->condition_info)
            ->action('Lihat Request', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'refund_request',
            'request_id' => $this->refundRequest->id,
            'transaction_id' => $this->refundRequest->transaction_id,
            'message' => 'Request refund untuk transaksi ' . $this->refundRequest->transaction->invoice_number,
        ];
    }
}
