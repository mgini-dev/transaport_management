<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkflowStageNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly string $type,
        private readonly array $meta = [],
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if ($this->shouldSendEmail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'meta' => $this->meta,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toMail(object $notifiable): MailMessage
    {
        $companyName = (string) config('app.company_name', config('app.name', 'NMIS'));
        $companyAddress = trim((string) config('app.company_address', ''));
        $requiredAction = data_get($this->meta, 'required_action');
        $requiredPermission = data_get($this->meta, 'required_permission');
        $requiredRoles = collect(data_get($this->meta, 'required_roles', []))
            ->filter(fn ($role) => filled($role))
            ->implode(', ');
        $recipientRoles = collect(data_get($this->meta, 'recipient_roles', []))
            ->filter(fn ($role) => filled($role))
            ->implode(', ');
        $actionUrl = (string) data_get($this->meta, 'action_url', route('notifications.center'));
        $greetingName = trim((string) data_get($notifiable, 'name', 'Team'));

        $mail = (new MailMessage())
            ->subject("{$this->title} | {$companyName}")
            ->greeting("Hello {$greetingName},")
            ->line($this->message)
            ->line('Notification type: '.str_replace('.', ' > ', $this->type));

        if (filled($requiredAction)) {
            $mail->line('Required action: '.$requiredAction);
        }

        if (filled($requiredPermission)) {
            $mail->line('Permission required: '.$requiredPermission);
        }

        if ($requiredRoles !== '') {
            $mail->line('Role(s) expected for this action: '.$requiredRoles);
        }

        if ($recipientRoles !== '') {
            $mail->line('Your role(s): '.$recipientRoles);
        }

        $mail->action('Open Workflow', $actionUrl)
            ->line('This is an automated workflow email from the transport system.');

        if ($companyAddress !== '') {
            $mail->line('Company address: '.$companyAddress);
        }

        return $mail->salutation($companyName);
    }

    private function shouldSendEmail(object $notifiable): bool
    {
        if (! (bool) config('services.system_email.enabled', false)) {
            return false;
        }

        return filled(data_get($notifiable, 'email'));
    }
}
