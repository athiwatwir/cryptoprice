<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Facades\Log;

class IndicatorNotification extends Notification
{
    use Queueable;
    //https://laravel-notification-channels.com/telegram/#contents
    /**
     * Create a new notification instance.
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = (object) $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toTelegram($notifiable)
    {
        if ($this->data->isLong) {
            return TelegramMessage::create()
                ->content("🟢 LONG #" . $this->data->coin.' up '.$this->data->amount.'%');
        } else {
            return TelegramMessage::create()
            ->content("🔴 LONG #" . $this->data->coin.' down '.$this->data->amount.'%');
        }

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
