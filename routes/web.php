<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('landing', 'landing');

Route::view('borrowers', 'borrowers')
    ->middleware(['auth', 'verified'])
    ->name('borrowers');

Route::view('loans', 'loans')
    ->middleware(['auth', 'verified'])
    ->name('loans');

Route::view('payments', 'payments')
    ->middleware(['auth', 'verified'])
    ->name('payments');

Route::view('paid-loans', 'paid-loans')
    ->middleware(['auth', 'verified'])
    ->name('paid_loans');

Route::view('income-expense', 'income-expense')
    ->middleware(['auth', 'verified'])
    ->name('income_expense');

Route::view('reports', 'reports')
    ->middleware(['auth', 'verified'])
    ->name('reports');

require __DIR__.'/settings.php';
