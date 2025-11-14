<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Middleware\AdminAuthenticate;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [ActionController::class, 'index'])->name('auction.room');
Route::post('/auction/{auction}/bid', [ActionController::class, 'placeBid'])->name('auction.bid');
Route::post('/auction/{id}/close', [ActionController::class, 'closeAuction']);
Route::get('/auction/{auction}/latest-data', [ActionController::class, 'latestData']);


Route::post('/session-name', function(\Illuminate\Http\Request $request){
    $request->validate([
        'name'=>'required|string|max:255'
    ]);
    session(['bidder_name'=>$request->name]);
    return response()->json(['ok'=>true]);
});


Route::prefix('admin')->group(function() {

    // Admin Auth
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Protected routes
    Route::middleware([AdminAuthenticate::class])->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/action/create', [AdminController::class, 'createAction'])->name('action.create');
        Route::post('/action/store', [AdminController::class, 'storeAction'])->name('action.store');
        Route::post('/action/{action}/close', [AdminController::class, 'closeAction'])->name('action.close');
        Route::get('/action/{action}/bids', [AdminController::class, 'viewBids'])->name('action.bids');

        Route::post('/notifications/mark-read', function () {
            auth('admin')->user()->unreadNotifications->markAsRead();
            return response()->json(['success' => true]);
        })->name('notifications.read');
    });
});

// Catch-all fallback route for 404
Route::fallback(function() {
    return response()->view('404', [], 404);
});

