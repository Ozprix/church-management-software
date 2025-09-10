<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\SkillRepositoryInterface;
use App\Repositories\SkillRepository;
use App\Repositories\Interfaces\InterestRepositoryInterface;
use App\Repositories\InterestRepository;
use App\Repositories\Interfaces\SpiritualGiftRepositoryInterface;
use App\Repositories\SpiritualGiftRepository;
use App\Repositories\Interfaces\MemberAvailabilityRepositoryInterface;
use App\Repositories\MemberAvailabilityRepository;

class MemberManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register Member Management Repositories
        $this->app->bind(SkillRepositoryInterface::class, SkillRepository::class);
        $this->app->bind(InterestRepositoryInterface::class, InterestRepository::class);
        $this->app->bind(SpiritualGiftRepositoryInterface::class, SpiritualGiftRepository::class);
        $this->app->bind(MemberAvailabilityRepositoryInterface::class, MemberAvailabilityRepository::class);
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
