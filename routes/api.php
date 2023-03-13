<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any('/get-new-orders', [App\Http\Controllers\Api\OrdersController::class, 'getNewOrders'])->name('get.new.orders');
Route::any('/recover-order',  [App\Http\Controllers\Api\OrdersController::class, 'recoverOrder'])->name('recover.order');
Route::any('/set-status', [App\Http\Controllers\Api\OrdersController::class, 'setStatus'])->name('set.status');
Route::any('/add-hold-images', [App\Http\Controllers\Api\OrdersController::class, 'addHoldImages'])->name('add.hold.images');
Route::any('/register-user', [App\Http\Controllers\Api\UsersController::class, 'registerUser'])->name('register.user');
Route::any('/remove-user', [App\Http\Controllers\Api\UsersController::class, 'removeUser'])->name('remove.user');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
