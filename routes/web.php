<?php

use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\QR\QRCodeController;
use App\Http\Controllers\SMS\SMSController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('payment.index');
});

Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::post('/create', [PaymentController::class, 'createPaymentLink'])->name('create');
    Route::get('/callback', [PaymentController::class, 'callback'])->name('callback');
    Route::get('/success', [PaymentController::class, 'success'])->name('success');
    Route::get('/failed', [PaymentController::class, 'failed'])->name('failed');
});

Route::prefix('qr')->group(function () {
    Route::get('/create', [QRCodeController::class, 'create'])->name('qr.create');
    Route::post('/generate', [QRCodeController::class, 'store'])->name('qr.generate');
    Route::get('/{qrId}', [QRCodeController::class, 'show'])->name('qr.show');
});

Route::prefix('sms')->group(function () {
    Route::get('/', [SMSController::class, 'index'])->name('sms.index');
    Route::post('/send', [SMSController::class, 'send'])->name('sms.send');
});