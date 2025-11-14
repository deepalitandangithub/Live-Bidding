<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Sample Auction');
            $table->text('description')->nullable();
            $table->decimal('start_price', 10, 2)->default(0);
            $table->decimal('highest_bid', 10, 2)->default(0);
            $table->string('highest_bidder')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('actions');
    }
};
