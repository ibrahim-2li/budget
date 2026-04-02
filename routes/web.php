<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Auth routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/budget', [BudgetController::class, 'index'])->name('budget');
    Route::post('/budget/income', [BudgetController::class, 'addIncome'])->name('budget.income.store');
    Route::post('/budget/expense', [BudgetController::class, 'addExpense'])->name('budget.expense.store');
    Route::delete('/budget/income/{income}', [BudgetController::class, 'deleteIncome'])->name('budget.income.delete');
    Route::delete('/budget/expense/{expense}', [BudgetController::class, 'deleteExpense'])->name('budget.expense.delete');
});
