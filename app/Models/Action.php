<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','description','start_price','highest_bid','highest_bidder','is_open','ends_at'
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'ends_at' => 'datetime',
    ];

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function highestBid() {
        return $this->bids()->orderByDesc('amount')->first();
    }

    public function participantsCount() {
        return $this->bids()->distinct('bidder_name')->count('bidder_name');
    }
}
