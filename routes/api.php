<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('/orders', [OrderController::class, 'normalize'])->name('order.normalize');
});
