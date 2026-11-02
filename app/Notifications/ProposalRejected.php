<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProposalRejected extends Notification
{
    use Queueable;

    public function __construct(
        public int $proposalId,
        public int $jobId,
        public string $clientName,
        public string $jobTitle,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

   public function toDatabase(object $notifiable): array
{
    return [
        'title' => 'Proposal rejected',
        'message' => "{$this->clientName} rejected your proposal for {$this->jobTitle}.",
        'action_url' => "/jobs/{$this->jobId}",
        'related_type' => 'proposal',
        'related_id' => $this->proposalId,
    ];
}
}