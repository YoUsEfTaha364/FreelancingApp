<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalSubmitted extends Notification
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


    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Proposal Updated',
            'message' => "{$this->freelancerName} updated a proposal.",
            'action_url' => "/jobs/{$this->jobId}/proposals/{$this->proposalId}",
            'related_type' => 'proposal',
            'related_id' => $this->proposalId,
        ];
    }
}
