<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/index',[PaymentController::class,'index']);
Route::post('/payment',[PaymentController::class,'crateInvoice']);
Route::get('/status',[PaymentController::class,'status']);