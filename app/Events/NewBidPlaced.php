<?php

namespace App\Events;

use App\Models\Bid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewBidPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auctionId;
    public $bidder_name;
    public $amount;
    public $created_at;

    /**
     * Create a new event instance.
     */
    public function __construct(int $auctionId, Bid $bid)
    {
        Log::info('New Bid: Auction ID: ' . $auctionId . ', Bidder: ' . $bid->bidder_name . ', Amount: ' . $bid->amount);
        $this->auctionId = $auctionId;
        $this->bidder_name = $bid->bidder_name;
        $this->amount = (float) $bid->amount;
        $this->created_at = $bid->created_at->toDateTimeString();
    }

    /**
     * Broadcast channel.
     */
    public function broadcastOn()
    {
        Log::info('New Bid action: ' . $this->auctionId);
        return new Channel('auction.' . $this->auctionId);
    }

    /**
     * Custom event name for frontend listener.
     */
    public function broadcastAs()
    {
        Log::info('NewBidPlaced');
        return 'NewBidPlaced';
    }

    /**
     * Data sent to frontend.
     */
    public function broadcastWith()
    {
        Log::info('broadcastWith');
        return [
            'auction_id' => $this->auctionId,
            'bidder_name' => $this->bidder_name,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
        ];
    }
    
}
