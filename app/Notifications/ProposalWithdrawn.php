<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProposalWithdrawn extends Notification
{
    use Queueable;

    public function __construct(
        public int $proposalId,
        public int $jobId,
        public string $freelancerName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Proposal Withdrawn',
            'message' => "{$this->freelancerName} withdrew a proposal.",
            'related_type' => 'proposal',
            'related_id' => $this->proposalId,
        ];
    }
}