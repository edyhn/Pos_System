<?php

namespace App\Notifications;

use App\Models\ReceiptReprintRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiptReprintRequested extends Notification
{
    use Queueable;

    public function __construct(public ReceiptReprintRequest $request)
    {
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
        $url = route('approvals.receipt');

        return (new MailMessage)
            ->subject('Request Cetak Ulang Struk')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ada request cetak ulang struk dari kasir.')
            ->line('Transaksi: ' . $this->request->transaction->invoice_number)
            ->line('Alasan: ' . $this->request->reason)
            ->action('Lihat Request', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'receipt_reprint',
            'request_id' => $this->request->id,
            'transaction_id' => $this->request->transaction_id,
            'message' => 'Request cetak ulang struk untuk transaksi ' . $this->request->transaction->invoice_number,
        ];
    }
}
