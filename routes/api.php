<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

    Route::post('/orders', [OrderController::class, 'normalize'])->name('order.normalize');
