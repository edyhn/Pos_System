<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestRejected extends Notification
{
    use Queueable;

    public function __construct(
        public string $requestType,
        public string $invoiceNumber,
        public string $ownerNote,
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (config('mail.mailers.smtp.host')) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->requestType === 'refund' ? 'Refund' : 'Cetak Ulang Struk';

        return (new MailMessage)
            ->subject($label . ' Ditolak')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Request ' . $label . ' untuk transaksi ' . $this->invoiceNumber . ' telah **ditolak**.')
            ->line('Alasan: ' . $this->ownerNote);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rejected',
            'request_type' => $this->requestType,
            'message' => $this->requestType === 'refund'
                ? 'Request refund ditolak. Alasan: ' . $this->ownerNote
                : 'Request cetak ulang ditolak. Alasan: ' . $this->ownerNote,
        ];
    }
}
