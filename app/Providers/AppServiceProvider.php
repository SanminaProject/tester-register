<?php

namespace App\Providers;

use App\Models\Fixture;
use App\Models\SparePart;
use App\Models\Tester;
use App\Models\TesterCalibrationSchedule as CalibrationSchedule;
use App\Models\TesterCustomer;
use App\Models\TesterEventLog as EventLog;
use App\Models\TesterMaintenanceSchedule as MaintenanceSchedule;
use App\Policies\CalibrationSchedulePolicy;
use App\Policies\EventLogPolicy;
use App\Policies\FixturePolicy;
use App\Policies\MaintenanceSchedulePolicy;
use App\Policies\SparePartPolicy;
use App\Policies\TesterCustomerPolicy;
use App\Policies\TesterPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::policy(TesterCustomer::class, TesterCustomerPolicy::class);
        Gate::policy(Tester::class, TesterPolicy::class);
        Gate::policy(Fixture::class, FixturePolicy::class);
        Gate::policy(MaintenanceSchedule::class, MaintenanceSchedulePolicy::class);
        Gate::policy(CalibrationSchedule::class, CalibrationSchedulePolicy::class);
        Gate::policy(EventLog::class, EventLogPolicy::class);
        Gate::policy(SparePart::class, SparePartPolicy::class);
    }
}
