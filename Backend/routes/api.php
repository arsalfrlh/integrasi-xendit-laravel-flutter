<?php

use App\Http\Controllers\PaymentApiController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WithDrawlApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/callback',[PaymentController::class,'callback']);
Route::post('/callback/withdrawl',[WithDrawlApiController::class,'callback']);
Route::post('/callback/payment',[PaymentApiController::class,'callback']);

Route::post('/payment/bank',[PaymentApiController::class,'createBankPayment']);
Route::post('/payment/store',[PaymentApiController::class,'createCstorePayment']);
Route::post('/payment/qr',[PaymentApiController::class,'createQrPayment']);

Route::post('/withdrawl',[WithDrawlApiController::class,'craeteWithDrawl']);
