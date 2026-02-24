<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\FuelRequisition;
use App\Models\Order;
use App\Models\Trip;
use App\Policies\AuditLogPolicy;
use App\Policies\DriverPolicy;
use App\Policies\FuelRequisitionPolicy;
use App\Policies\OrderPolicy;
use App\Policies\TripPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Trip::class, TripPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(FuelRequisition::class, FuelRequisitionPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Driver::class, DriverPolicy::class);

        Gate::before(function ($user, string $ability) {
            if ($user->hasRole('Chief Admin')) {
                return true;
            }

            return null;
        });
    }
}
