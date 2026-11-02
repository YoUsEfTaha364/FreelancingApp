<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CounterofferAccepted extends Notification
{
    use Queueable;

    public function __construct(
        public int $negotiationId,
        public int $proposalId,
        public int $jobId,
        public string $freelancerName,
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
            'title' => 'Counteroffer Accepted',
            'message' => "{$this->freelancerName} accepted your counteroffer of {$this->proposedAmount} for {$this->jobTitle}.",
            'action_url' => "/jobs/{$this->jobId}",
            'related_type' => 'negotiation',
            'related_id' => $this->negotiationId,
        ];
    }
}