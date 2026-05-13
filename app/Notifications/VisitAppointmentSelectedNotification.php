<?php

namespace App\Notifications;

use App\Models\VisitReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitAppointmentSelectedNotification extends Notification
{
    use Queueable;

    public function __construct(public VisitReport $visitReport) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'title' => 'تم اختيار موعد الزيارة',
            'body' => $this->body(),
            'url' => url('/consultant/visit-reports/'.$this->visitReport->id),
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تم اختيار موعد الزيارة')
            ->greeting('مرحباً')
            ->line($this->body())
            ->action('عرض تقرير الزيارة', url('/consultant/visit-reports/'.$this->visitReport->id));
    }

    protected function body(): string
    {
        $initiative = $this->visitReport->initiative?->name_ar ?? '-';
        $date = $this->visitReport->scheduled_at?->format('Y-m-d h:i A') ?? '-';

        return 'اختارت الجهة موعد الزيارة للمبادرة: '.$initiative.'، الموعد: '.$date;
    }
}
