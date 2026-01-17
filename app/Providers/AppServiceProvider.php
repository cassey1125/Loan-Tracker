<?php

namespace App\Providers;

use App\Models\Loan;
use App\Models\Payment;
use App\Observers\LoanObserver;
use App\Observers\PaymentObserver;
use App\Services\Loan\Interest\FlatRateInterestStrategy;
use App\Services\Loan\Interest\InterestCalculationStrategy;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(InterestCalculationStrategy::class, FlatRateInterestStrategy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Loan::observe(LoanObserver::class);
        Payment::observe(PaymentObserver::class);
    }
}
