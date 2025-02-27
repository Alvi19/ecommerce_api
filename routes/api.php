<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource('products', ProductController::class);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/invoices/{orderId}', [InvoiceController::class, 'generateInvoice']);
    Route::get('/', [InvoiceController::class, 'index']);
    Route::get('/{id}', [InvoiceController::class, 'show']);
    Route::post('/payments', [PaymentController::class, 'processPayment']);
});
