<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Events\NewBidPlaced;
use App\Models\Action;
use App\Models\Admin;
use App\Models\Bid;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Events\ActionClosed;
use App\Events\ActionStarted;
use App\Notifications\NewBidNotification;


class ActionController extends Controller
{
    
    public function index()
    {
      
        $expired = Action::where('is_open', true)
            ->where('ends_at', '<=', now())
            ->get();

        foreach ($expired as $auction) {
            $auction->update(['is_open' => false]);
            broadcast(new ActionClosed($auction->id));
        }

        $auction = Action::where('is_open', true)
            ->orderBy('id', 'desc')
            ->first();

        if (!$auction) {
            $auction = $this->createNewAuction();
            broadcast(new ActionStarted($auction));
        }

        return view('action.room', compact('auction'));
    }

    // Handle bid placement
    public function placeBid(Request $request, $auctionId)
    {
        $request->validate([
            'bidder_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $auction = Action::find($auctionId);

        if (! $auction) {
            return response()->json(['message' => 'Auction not found'], 404);
        }

        // Close expired auction automatically before bidding
        if ($auction->ends_at && Carbon::parse($auction->ends_at)->isPast()) {
            $auction->update(['is_open' => false]);
            $this->checkAndCreateNewAuctions();
            return response()->json(['message' => 'Auction closed'], 422);
        }

        if (! $auction->is_open) {
            return response()->json(['message' => 'Auction closed'], 422);
        }

        $amount = (float) $request->input('amount');
        $minAllowed = (float) $auction->highest_bid;

        if ($amount <= $minAllowed) {
            return response()->json(['message' => 'Bid must be higher than current highest bid'], 422);
        }

        // Create bid
        $bid = Bid::create([
            'action_id' => $auction->id,
            'bidder_name' => $request->input('bidder_name'),
            'amount' => $amount,
        ]);

        // Update auction
        $auction->update([
            'highest_bid' => $amount,
            'highest_bidder' => $request->input('bidder_name'),
        ]);

        $admins = Admin::all();
        Log::info('Dispatching Notification');
        foreach ($admins as $admin) {
            $admin->notify(new NewBidNotification($bid));

            Log::info('NewBidNotification sent', [
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'bid_id' => $bid->id,
                'action_id' => $bid->action_id,
                'bidder_name' => $bid->bidder_name,
                'amount' => $bid->amount,
                'timestamp' => now()->toDateTimeString(),
            ]);
        }

        // Broadcast new bid event
        Log::info('Dispatching Bid event for auction ID: ' . $auction->id, ['bid' => $bid]);
        broadcast(new NewBidPlaced($auction->id, $bid));

        return response()->json([
            'success' => true,
            'bid' => $bid,
            'highest_bid' => $auction->highest_bid,
            'highest_bidder' => $auction->highest_bidder,
        ]);
    }

    // Create a single new auction
    private function createNewAuction()
    {
        return Action::create([
            'title' => 'Auto Action - ' . now()->format('H:i:s'),
            'description' => 'Automatically created auction',
            'start_price' => rand(10, 100),
            'highest_bid' => rand(10, 100),
            'ends_at' => Carbon::now()->addMinutes(1),
            'is_open' => true,
            'highest_bidder' => null
        ]);
    }

    // Create multiple new auctions if all are closed
    private function checkAndCreateNewAuctions($count = 2)
    {
        // If no open auctions left, create new ones
        if (Action::where('is_open', true)->count() === 0) {
            for ($i = 1; $i <= $count; $i++) {
                $this->createNewAuction();
            }
        }
    }

    public function closeAuction($id)
    {
        $auction = Action::find($id);
        if (!$auction || !$auction->is_open) return response()->noContent();

        $auction->update(['is_open' => false]);
        broadcast(new ActionClosed($auction->id));

        // Automatically create + start next auction
        $next = $this->createNewAuction();
        Log::info('Dispatching ActionStarted event for auction ID: ' . $auction->id);
        broadcast(new ActionStarted($next));

        return response()->json(['message' => 'Auction closed, new auction started', 'next' => $next]);
    }

    public function latestData($auctionId)
    {
        $auction = Action::with(['bids' => function($q) {
            $q->latest()->take(10);
        }])->findOrFail($auctionId);

        return response()->json([
            'highest_bid' => $auction->highest_bid,
            'highest_bidder' => $auction->highest_bidder,
            'bids' => $auction->bids,
        ]);
    }



}
