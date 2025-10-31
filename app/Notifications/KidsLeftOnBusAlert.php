<?php

namespace App\Notifications;

use App\Models\DailyChecklist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KidsLeftOnBusAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $checklist;

    /**
     * Create a new notification instance.
     */
    public function __construct(DailyChecklist $checklist)
    {
        $this->checklist = $checklist;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $vehicle = $this->checklist->vehicle;
        $driver = $this->checklist->user;
        $driverName = trim($driver->first_name . ' ' . $driver->last_name);

        return (new MailMessage)
                    ->error()
                    ->subject('🚨 CRITICAL ALERT: Kids Left on Bus')
                    ->greeting('CRITICAL SAFETY ALERT')
                    ->line('A driver has reported that kids may have been left on a bus.')
                    ->line('**Vehicle:** ' . $vehicle->display_name)
                    ->line('**Driver:** ' . $driverName)
                    ->line('**Time:** ' . $this->checklist->completed_at->format('d/m/Y H:i'))
                    ->action('View Checklist', route('managerial.checklist.show', $this->checklist->checklist_uuid))
                    ->line('**IMMEDIATE ACTION REQUIRED**')
                    ->line('Please verify the bus immediately and ensure all children are accounted for.')
                    ->salutation('Buses Safety System');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'checklist_id' => $this->checklist->checklist_id,
            'checklist_uuid' => $this->checklist->checklist_uuid,
            'vehicle_id' => $this->checklist->vehicle_id,
            'vehicle_name' => $this->checklist->vehicle->display_name,
            'driver_id' => $this->checklist->user_id,
            'driver_name' => trim($this->checklist->user->first_name . ' ' . $this->checklist->user->last_name),
            'time' => $this->checklist->completed_at,
            'alert_type' => 'kids_left_on_bus',
            'severity' => 'critical',
        ];
    }
}



