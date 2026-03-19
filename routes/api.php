<?php


use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\TransactionController;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::prefix('transactions')->group(function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::get('/pending', [TransactionController::class, 'transactionPending']);
    Route::post('/', [TransactionController::class, 'store']);
    Route::post('/{id}/processing', [TransactionController::class, 'markProcessing']);
    Route::post('/{id}/complete', [TransactionController::class, 'completeTransaction']);
});
