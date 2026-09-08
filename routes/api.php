<?php

use App\Http\Controllers\Api\Payment\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QR\QRController;

Route::prefix('payment')->name('api.payment.')->group(function () {
    Route::post('/create', [PaymentController::class, 'createPaymentLink'])->name('create');
    Route::get('/callback', [PaymentController::class, 'callback'])->name('callback');
    Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
});

Route::prefix('qr')->name('api.qr.')->group(function () {
    Route::post('/generate', [QRController::class, 'generate'])->name('generate');
});
