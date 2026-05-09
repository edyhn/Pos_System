<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestApproved extends Notification
{
    use Queueable;

    public function __construct(
        public string $requestType,
        public string $invoiceNumber,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->requestType === 'refund' ? 'Refund' : 'Cetak Ulang Struk';

        return (new MailMessage)
            ->subject($label . ' Disetujui')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Request ' . $label . ' untuk transaksi ' . $this->invoiceNumber . ' telah **disetujui**.')
            ->line('Silakan hubungi owner untuk tindak lanjut.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approved',
            'request_type' => $this->requestType,
            'message' => $this->requestType === 'refund'
                ? 'Request refund disetujui.'
                : 'Request cetak ulang struk disetujui.',
        ];
    }
}
