<?php

use Illuminate\Support\Facades\Route;


use App\Livewire\Borrowers\BorrowerCreate;
use App\Livewire\Borrowers\BorrowerEdit;
use App\Livewire\Borrowers\BorrowerList;
use App\Livewire\Borrowers\BorrowerShow;


Route::get('/', function () {
    return view('landing');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('landing', 'landing');

Route::middleware(['auth', 'verified'])->group(function () {
    // We can't use Route::view for borrowers anymore since we want to use Livewire components as full page components or render them in a blade layout
    // Actually, the original borrowers.blade.php was just a wrapper. We can keep it or use full page components.
    // Let's stick to using the wrapper approach for list, but for Create/Edit/Show we might need specific routes.
    
    // However, the cleanest way with Livewire is usually to have routes point to the Livewire component if it's a full page, 
    // OR have a controller/view that includes the component.
    // The previous implementation of `loans` used `loans.blade.php` which included `<livewire:loans />`.
    // Let's do the same for `borrowers` list.
    
    Route::get('borrowers', function () {
        return view('borrowers');
    })->name('borrowers.index');

    // For Create, Edit, Show, we'll use Full Page Components or wrapped views.
    // Let's use wrapped views to maintain layout consistency easily if we want to pass title etc.
    // Or we can just use the Livewire component as the route handler and use the layout in the component.
    // Let's try Route::get pointing to closure returning view for consistency with current setup.
    
    Route::get('borrowers/create', function () {
        return view('borrowers.create');
    })->name('borrowers.create');

    Route::get('borrowers/{borrower}', function (App\Models\Borrower $borrower) {
        return view('borrowers.show', compact('borrower'));
    })->name('borrowers.show');

    Route::get('borrowers/{borrower}/edit', function (App\Models\Borrower $borrower) {
        return view('borrowers.edit', compact('borrower'));
    })->name('borrowers.edit');

    Route::get('borrowers/{borrower}/profit', function (App\Models\Borrower $borrower) {
        return view('borrowers.profit', compact('borrower'));
    })->name('borrowers.profit');

    Route::get('investor-profit', function () {
        return view('investor-profit');
    })->name('investor-profit');

    Route::get('investor-profit/pdf', [App\Http\Controllers\InvestorProfitController::class, 'downloadPdf'])
        ->name('investor-profit.pdf');
});

Route::view('loans', 'loans')
    ->middleware(['auth', 'verified'])
    ->name('loans');

Route::view('funds', 'funds')
    ->middleware(['auth', 'verified'])
    ->name('funds');

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
