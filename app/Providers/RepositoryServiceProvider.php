<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\GroupRepository;
use App\Repositories\Interfaces\GroupAnalyticsRepositoryInterface;
use App\Repositories\GroupAnalyticsRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(GroupRepositoryInterface::class, GroupRepository::class);
        $this->app->bind(GroupAnalyticsRepositoryInterface::class, GroupAnalyticsRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
