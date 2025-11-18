<?php

namespace App\Notifications;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class WithdrawalRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $withdrawalRequest;
    public $type;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(WithdrawalRequest $withdrawalRequest, string $type, string $message)
    {
        $this->withdrawalRequest = $withdrawalRequest;
        $this->type = $type; // 'created', 'approved', 'rejected', 'step_completed', 'fully_approved'
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'withdrawal_request_id' => $this->withdrawalRequest->id,
            'withdrawal_request_uuid' => $this->withdrawalRequest->uuid,
            'type' => $this->type,
            'message' => $this->message,
            'amount' => $this->withdrawalRequest->amount,
            'business_name' => $this->withdrawalRequest->business->name,
            'status' => $this->withdrawalRequest->status,
        ];
    }
}

