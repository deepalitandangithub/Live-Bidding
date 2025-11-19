<?php

namespace App\Notifications;

use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewBidNotification extends Notification
{
    use Queueable;

    public $bid;

    public function __construct(Bid $bid)
    {
        $this->bid = $bid;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast']; 
    }

    public function toDatabase($notifiable)
    {
        return [
            'action_id' => $this->bid->action_id,
            'bidder_name' => $this->bid->bidder_name,
            'amount' => $this->bid->amount,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'action_id' => $this->bid->action_id,
            'bidder_name' => $this->bid->bidder_name,
            'amount' => $this->bid->amount,
        ]);
    }
}
