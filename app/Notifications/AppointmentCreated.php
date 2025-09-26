<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Twilio\Rest\Client; // ✅ Twilio import

class AppointmentCreated extends Notification implements ShouldQueue
{
    use Queueable; // ✅ Queueable trait zaroori hai

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    // Channels: mail + database
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    // Mail message
    public function toMail($notifiable): MailMessage
    {
        $appointment = $this->appointment;

        return (new MailMessage)
            ->subject('📅 New Appointment Assigned')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new appointment has been booked.')
            ->line('🔹 Client: ' . $appointment->client->name)
            ->line('🔹 Location: ' . optional($appointment->location)->name)
            ->line('🔹 Date & Time: ' . $appointment->appointment_time)
            ->line('Notes: ' . ($appointment->notes ?? 'No additional notes'))
            ->action('View Appointment', url('/appointments/' . $appointment->id))
            ->line('Thank you!');
    }

    // Database notification
    public function toArray($notifiable): array
    {
        $appointment = $this->appointment;

        return [
            'appointment_id' => $appointment->id,
            'client' => $appointment->client->name,
            'staff' => $appointment->staff->name ?? null,
            'location' => optional($appointment->location)->name,
            'time' => $appointment->appointment_time,
            'notes' => $appointment->notes,
        ];
    }

    // SMS notification
    public function toSms($notifiable)
    {
        $appointment = $this->appointment;

        $message = "New Appointment Assigned:\nClient: " . $appointment->client->name .
                   "\nTime: " . $appointment->appointment_time .
                   "\nLocation: " . optional($appointment->location)->name;

        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        $twilio->messages->create($notifiable->phone, [
            'from' => env('TWILIO_FROM'),
            'body' => $message,
        ]);
    }
}
