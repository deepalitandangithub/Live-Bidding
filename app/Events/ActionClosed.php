<?php

namespace App\Events;

use App\Models\Action;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;


class ActionClosed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auctionId;

    public function __construct($auctionId)
    {
        Log::info('ActionClose Event constructed for: ' . $auctionId);
        $this->auctionId = $auctionId;
    }

    public function broadcastOn()
    {
        Log::info('ActionClose');
        return new Channel("auction.{$this->auctionId}");
    }
}
