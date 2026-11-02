<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CounterofferReceived extends Notification
{
    use Queueable;

    public function __construct(
        public int $proposalId,
        public int $jobId,
        public int $negotiationId,
        public string $clientName,
        public string $jobTitle,
        public float $proposedAmount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Counteroffer Received',
            'message' => "{$this->clientName} sent you a counteroffer of {$this->proposedAmount} for {$this->jobTitle}.",
            'action_url' => "/jobs/{$this->jobId}",
            'related_type' => 'negotiation',
            'related_id' => $this->negotiationId,
        ];
    }
}