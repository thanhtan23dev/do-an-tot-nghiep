<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use TCG\Voyager\Facades\Voyager;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Models\Task;
use App\Observers\TaskObserver;
use App\Models\MonthlyPayroll;
use App\Observers\MonthlyPayrollObserver;
use App\Actions\ExportPayrollAction;
use App\Actions\ExportDetailPayrollAction;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useTailwind();
        Order::observe(OrderObserver::class);
        Task::observe(TaskObserver::class);
        MonthlyPayroll::observe(MonthlyPayrollObserver::class);
        Voyager::addAction(ExportPayrollAction::class);
        Voyager::addAction(ExportDetailPayrollAction::class);
    }
}
