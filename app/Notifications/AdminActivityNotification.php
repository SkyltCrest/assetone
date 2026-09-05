<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AdminActivityNotification extends Notification
{
    /**
     * @param  string       $action    Past-tense verb, e.g. "created", "updated", "deleted", "accepted".
     * @param  string       $subject   Noun phrase, e.g. 'asset "AST-001 — Dell Latitude"'.
     * @param  string       $actorName Who performed the action.
     * @param  string|null  $url       Where to send the admin on click.
     * @param  string|null  $detail    Optional extra context, e.g. "changed: role, status".
     */
    public function __construct(
        public string $action,
        public string $subject,
        public string $actorName,
        public ?string $url = null,
        public ?string $detail = null,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = sprintf('%s %s %s.', $this->actorName, $this->action, $this->subject);

        if ($this->detail) {
            $message .= ' ('.$this->detail.')';
        }

        $titleSubject = preg_replace('/^the /', '', $this->subject);

        return [
            'type' => 'admin_activity',
            'action' => $this->action,
            'title' => ucfirst($this->action).' — '.ucfirst($titleSubject),
            'message' => $message,
            'url' => $this->url,
        ];
    }
}
