<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate;
use App\Models\TesterCustomer;
use App\Models\Tester;
use App\Models\Fixture;
use App\Models\MaintenanceSchedule;
use App\Models\CalibrationSchedule;
use App\Models\EventLog;
use App\Models\SparePart;
use App\Policies\TesterCustomerPolicy;
use App\Policies\TesterPolicy;
use App\Policies\FixturePolicy;
use App\Policies\MaintenanceSchedulePolicy;
use App\Policies\CalibrationSchedulePolicy;
use App\Policies\EventLogPolicy;
use App\Policies\SparePartPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        TesterCustomer::class => TesterCustomerPolicy::class,
        Tester::class => TesterPolicy::class,
        Fixture::class => FixturePolicy::class,
        MaintenanceSchedule::class => MaintenanceSchedulePolicy::class,
        CalibrationSchedule::class => CalibrationSchedulePolicy::class,
        EventLog::class => EventLogPolicy::class,
        SparePart::class => SparePartPolicy::class,
    ];

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
    public function boot(Gate $gate): void
    {
        // Register policies
        foreach ($this->policies as $model => $policy) {
            $gate->policy($model, $policy);
        }
    }
}
