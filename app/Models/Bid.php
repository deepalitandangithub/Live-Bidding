<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = ['action_id','bidder_name','amount'];

    public function auction()
    {
        return $this->belongsTo(Action::class);
    }
}
