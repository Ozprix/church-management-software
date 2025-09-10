<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\FinancialForecastRepositoryInterface;
use App\Repositories\FinancialForecastRepository;
use App\Repositories\Interfaces\BudgetAllocationRepositoryInterface;
use App\Repositories\BudgetAllocationRepository;
use App\Repositories\Interfaces\FinancialMetricRepositoryInterface;
use App\Repositories\FinancialMetricRepository;
use App\Repositories\Interfaces\GroupAttendanceRepositoryInterface;
use App\Repositories\GroupAttendanceRepository;
use App\Services\Interfaces\GroupAttendanceServiceInterface;
use App\Services\GroupAttendanceService;
use App\Repositories\Interfaces\GroupEventRepositoryInterface;
use App\Repositories\GroupEventRepository;
use App\Services\Interfaces\GroupEventServiceInterface;
use App\Services\GroupEventService;
use App\Repositories\Interfaces\EventCategoryRepositoryInterface;
use App\Repositories\EventCategoryRepository;
use App\Repositories\Interfaces\EventResourceRepositoryInterface;
use App\Repositories\EventResourceRepository;
use App\Repositories\Interfaces\EventRegistrationRepositoryInterface;
use App\Repositories\EventRegistrationRepository;
use App\Repositories\Interfaces\EventReminderRepositoryInterface;
use App\Repositories\EventReminderRepository;
use App\Repositories\Interfaces\EventShareRepositoryInterface;
use App\Repositories\EventShareRepository;
use App\Services\Interfaces\EventReminderServiceInterface;
use App\Services\EventReminderService;
use App\Services\Interfaces\EventShareServiceInterface;
use App\Services\EventShareService;
use App\Repositories\Interfaces\GroupPermissionRepositoryInterface;
use App\Repositories\GroupPermissionRepository;
use App\Repositories\Interfaces\GroupRolePermissionRepositoryInterface;
use App\Repositories\GroupRolePermissionRepository;
use App\Repositories\Interfaces\GroupAnalyticsRepositoryInterface;
use App\Repositories\GroupAnalyticsRepository;
use App\Repositories\Interfaces\GroupMemberEngagementRepositoryInterface;
use App\Repositories\GroupMemberEngagementRepository;
use App\Services\Interfaces\GroupAnalyticsServiceInterface;
use App\Services\GroupAnalyticsService;
use App\Repositories\Interfaces\GroupMessageRepositoryInterface;
use App\Repositories\GroupMessageRepository;
use App\Repositories\Interfaces\GroupPrayerRequestRepositoryInterface;
use App\Repositories\GroupPrayerRequestRepository;
use App\Repositories\Interfaces\GroupDocumentRepositoryInterface;
use App\Repositories\GroupDocumentRepository;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\GroupRepository;
use App\Repositories\Interfaces\GroupMemberRepositoryInterface;
use App\Repositories\GroupMemberRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Group Attendance Repository
        $this->app->bind(GroupAttendanceRepositoryInterface::class, GroupAttendanceRepository::class);
        
        // Register Group Attendance Service
        $this->app->bind(GroupAttendanceServiceInterface::class, GroupAttendanceService::class);
        
        // Register Group Event Repository
        $this->app->bind(GroupEventRepositoryInterface::class, GroupEventRepository::class);
        
        // Register Group Event Service
        $this->app->bind(GroupEventServiceInterface::class, GroupEventService::class);
        
        // Register Event Category Repository
        $this->app->bind(EventCategoryRepositoryInterface::class, EventCategoryRepository::class);
        
        // Register Event Resource Repository
        $this->app->bind(EventResourceRepositoryInterface::class, EventResourceRepository::class);
        
        // Register Event Registration Repository
        $this->app->bind(EventRegistrationRepositoryInterface::class, EventRegistrationRepository::class);
        
        // Register Event Reminder Repository
        $this->app->bind(EventReminderRepositoryInterface::class, EventReminderRepository::class);
        
        // Register Event Share Repository
        $this->app->bind(EventShareRepositoryInterface::class, EventShareRepository::class);
        
        // Register Event Reminder Service
        $this->app->bind(EventReminderServiceInterface::class, EventReminderService::class);
        
        // Register Event Share Service
        $this->app->bind(EventShareServiceInterface::class, EventShareService::class);
        
        // Register Group Permission Repository
        $this->app->bind(GroupPermissionRepositoryInterface::class, GroupPermissionRepository::class);
        
        // Register Group Role Permission Repository
        $this->app->bind(GroupRolePermissionRepositoryInterface::class, GroupRolePermissionRepository::class);
        
        // Register Group Analytics Repository
        $this->app->bind(GroupAnalyticsRepositoryInterface::class, GroupAnalyticsRepository::class);
        
        // Register Group Member Engagement Repository
        $this->app->bind(GroupMemberEngagementRepositoryInterface::class, GroupMemberEngagementRepository::class);
        
        // Register Group Analytics Service
        $this->app->bind(GroupAnalyticsServiceInterface::class, GroupAnalyticsService::class);
        
        // Register Group Communication Repositories
        $this->app->bind(GroupMessageRepositoryInterface::class, GroupMessageRepository::class);
        $this->app->bind(GroupPrayerRequestRepositoryInterface::class, GroupPrayerRequestRepository::class);
        $this->app->bind(GroupDocumentRepositoryInterface::class, GroupDocumentRepository::class);
        
        // Register Group Repository
        $this->app->bind(GroupRepositoryInterface::class, GroupRepository::class);
        
        // Register Group Member Repository
        $this->app->bind(GroupMemberRepositoryInterface::class, GroupMemberRepository::class);
        
        // Register Financial Management Repositories
        $this->app->bind(FinancialForecastRepositoryInterface::class, FinancialForecastRepository::class);
        $this->app->bind(BudgetAllocationRepositoryInterface::class, BudgetAllocationRepository::class);
        $this->app->bind(FinancialMetricRepositoryInterface::class, FinancialMetricRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
