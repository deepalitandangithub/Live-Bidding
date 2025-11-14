<?php

namespace App\Events;

use App\Models\Action;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;


class ActionStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auction;

    public function __construct(Action $auction)
    {
        Log::info('ActionStarted Event constructed for: ' . $auction->id);
        $this->auction = [
            'id' => $auction->id,
            'title' => $auction->title,
            'highest_bid' => $auction->highest_bid,
            'highest_bidder' => $auction->highest_bidder,
            'ends_at' => $auction->ends_at,
            'is_open' => $auction->is_open,
        ];
    }

    public function broadcastOn()
    {
        Log::info('auction.global');
        return new Channel('auction.global');
    }

    public function broadcastAs()
    {
        Log::info('ActionStarted');
        return 'ActionStarted';
    }
}
