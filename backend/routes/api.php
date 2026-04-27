<?php

use App\Http\Controllers\CompraController;
use App\Http\Controllers\VendaController;
use Illuminate\Support\Facades\Route;

Route::get('/compras', [CompraController::class, 'index']);
Route::post('/compras', [CompraController::class, 'store']);

Route::post('/vendas', [VendaController::class, 'store']);
Route::get('/vendas', [VendaController::class, 'index']);

Route::get('/produtos', [\App\Http\Controllers\ProdutoController::class, 'index']);
Route::post('/produtos', [\App\Http\Controllers\ProdutoController::class, 'store']);
Route::put('/produtos/{id}', [\App\Http\Controllers\ProdutoController:: class, 'update']);
Route::delete('/produtos/{id}', [\App\Http\Controllers\ProdutoController::class, 'destroy']);


