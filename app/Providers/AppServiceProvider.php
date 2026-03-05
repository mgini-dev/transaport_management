<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\FuelRequisition;
use App\Models\Order;
use App\Models\Trip;
use App\Policies\AuditLogPolicy;
use App\Policies\DriverPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\FuelRequisitionPolicy;
use App\Policies\OrderPolicy;
use App\Policies\TripPolicy;
use Illuminate\Support\Facades\Config;
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
        $this->configureSystemEmail();

        Gate::policy(Trip::class, TripPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(FuelRequisition::class, FuelRequisitionPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Driver::class, DriverPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);

        Gate::before(function ($user, string $ability) {
            if ($user->hasRole('Chief Admin')) {
                return true;
            }

            return null;
        });
    }

    private function configureSystemEmail(): void
    {
        if (! (bool) config('services.system_email.enabled', false)) {
            return;
        }

        $address = trim((string) config('services.system_email.address'));
        $password = (string) config('services.system_email.password');
        $smtpHost = trim((string) config('services.system_email.smtp.host'));
        $smtpPort = (int) config('services.system_email.smtp.port', 465);
        $smtpScheme = trim((string) config('services.system_email.smtp.scheme', 'smtps'));

        if ($address === '' || $password === '' || $smtpHost === '') {
            return;
        }

        Config::set('mail.default', 'failover');
        Config::set('mail.mailers.smtp.host', $smtpHost);
        Config::set('mail.mailers.smtp.port', $smtpPort);
        Config::set('mail.mailers.smtp.scheme', $smtpScheme);
        Config::set('mail.mailers.smtp.username', $address);
        Config::set('mail.mailers.smtp.password', $password);
        Config::set('mail.from.address', $address);
        Config::set(
            'mail.from.name',
            (string) config('services.system_email.from_name', config('app.company_name', config('app.name', 'NMIS')))
        );
    }
}
