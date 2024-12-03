<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementsController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('warehouses')->group(function () {
    Route::get('/', [WarehouseController::class, 'index']);
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
});

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::post('/{id}/complete', [OrderController::class, 'complete']);
    Route::put('/{id}/cancel', [OrderController::class, 'cancel']);
    Route::put('/{id}/restore', [OrderController::class, 'restore']);
    Route::put('/{id}/update', [OrderController::class, 'updateItem']);
});

Route::get('/stock-movements', [StockMovementsController::class, 'index']);
