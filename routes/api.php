<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\MemberController as ApiMemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Member\MemberController;
use App\Http\Controllers\Member\MemberJourneyController;
use App\Http\Controllers\Member\MemberImportExportController;
use App\Http\Controllers\Family\FamilyController;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Finance\DonationController;
use App\Http\Controllers\Finance\DonationCategoryController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\CampaignController;
use App\Http\Controllers\Finance\BudgetController;
use App\Http\Controllers\Finance\PledgeController;
use App\Http\Controllers\Finance\ProjectController;
use App\Http\Controllers\Finance\FinancialController;
use App\Http\Controllers\Finance\FinancialReportController;
use App\Http\Controllers\Finance\RecurringDonationController;
use App\Http\Controllers\Finance\TaxReceiptController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Communication\CommunicationController;
use App\Http\Controllers\Communication\PrayerRequestController;
use App\Http\Controllers\Communication\WhatsAppController;
use App\Http\Controllers\Volunteer\VolunteerController;
use App\Http\Controllers\Volunteer\VolunteerRoleController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\Group\GroupController;
use App\Http\Controllers\Group\GroupMemberController;
use App\Http\Controllers\Group\GroupAttendanceController;
use App\Http\Controllers\Group\GroupEventController;
use App\Http\Controllers\Group\EventCategoryController;
use App\Http\Controllers\Group\EventResourceController;
use App\Http\Controllers\Group\EventRegistrationController;
use App\Http\Controllers\Group\EventReminderController;
use App\Http\Controllers\Group\EventShareController;
use App\Http\Controllers\Group\GroupPermissionController;
use App\Http\Controllers\Group\GroupAnalyticsController;
use App\Http\Controllers\Group\GroupMessageController;
use App\Http\Controllers\Group\GroupPrayerRequestController;
use App\Http\Controllers\Group\GroupDocumentController;
use App\Http\Controllers\Group\GroupMemberRoleController;
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\FinancialForecastController;
use App\Http\Controllers\Finance\BudgetAllocationController;
use App\Http\Controllers\Finance\FinancialMetricController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the 'api' middleware group. Enjoy building your API!
|
*/

// Authentication Routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Members API
    Route::apiResource('members', ApiMemberController::class);
    
    // Additional member endpoints
    Route::prefix('members')->group(function () {
        // Search members
        Route::get('/search', [ApiMemberController::class, 'search']);
        
        // Member statistics
        Route::get('/stats', [ApiMemberController::class, 'stats']);
        
        // Export members
        Route::get('/export', [ApiMemberController::class, 'export']);
        
        // Import members
        Route::post('/import', [ApiMemberController::class, 'import']);
        
        // Bulk actions
        Route::post('/bulk-actions', [ApiMemberController::class, 'bulkActions']);
        
        // Import/Export routes
        Route::prefix('import')->group(function () {
            // Download import template
            Route::get('/template', 'App\Http\Controllers\Api\MemberImportController@template')
                ->name('members.import.template');
                
            // Process import
            Route::post('/process', 'App\Http\Controllers\Api\MemberImportController@import')
                ->name('members.import.process');
        });
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Members
    Route::get('/members/{id}/donations', [MemberController::class, 'getDonations']);
    Route::get('/members/{memberId}/donations/year-summary', [MemberController::class, 'getDonationYearSummary']);
    Route::get('/members/{id}/groups', [MemberController::class, 'getGroups']);
    Route::get('/members/{member}/journeys', [MemberJourneyController::class, 'index']);
    Route::post('/members/{member}/journeys', [MemberJourneyController::class, 'store']);
    Route::get('/members/{member}/journeys/{journey}', [MemberJourneyController::class, 'show']);
    Route::put('/members/{member}/journeys/{journey}', [MemberJourneyController::class, 'update']);
    Route::delete('/members/{member}/journeys/{journey}', [MemberJourneyController::class, 'destroy']);
    
    // Member Skills routes
    Route::get('/members/{id}/skills', [\App\Http\Controllers\Member\SkillController::class, 'getSkillsForMember']);
    Route::post('/members/{id}/skills', [\App\Http\Controllers\Member\SkillController::class, 'assignSkillToMember']);
    Route::delete('/members/{id}/skills/{skillId}', [\App\Http\Controllers\Member\SkillController::class, 'removeSkillFromMember']);
    
    // Member Interests routes
    Route::get('/members/{id}/interests', [\App\Http\Controllers\Member\InterestController::class, 'getInterestsForMember']);
    Route::post('/members/{id}/interests', [\App\Http\Controllers\Member\InterestController::class, 'assignInterestToMember']);
    Route::delete('/members/{id}/interests/{interestId}', [\App\Http\Controllers\Member\InterestController::class, 'removeInterestFromMember']);
    
    // Member Spiritual Gifts routes
    Route::get('/members/{id}/spiritual-gifts', [\App\Http\Controllers\Member\SpiritualGiftController::class, 'getSpiritualGiftsForMember']);
    Route::post('/members/{id}/spiritual-gifts', [\App\Http\Controllers\Member\SpiritualGiftController::class, 'assignSpiritualGiftToMember']);
    Route::delete('/members/{id}/spiritual-gifts/{giftId}', [\App\Http\Controllers\Member\SpiritualGiftController::class, 'removeSpiritualGiftFromMember']);
    
    // Member Availability routes
    Route::get('/members/{id}/availability', [\App\Http\Controllers\Member\MemberAvailabilityController::class, 'getMemberAvailability']);
    Route::post('/members/{id}/availability', [\App\Http\Controllers\Member\MemberAvailabilityController::class, 'updateMemberAvailability']);
    Route::delete('/members/{id}/availability/{availabilityId}', [\App\Http\Controllers\Member\MemberAvailabilityController::class, 'deleteAvailabilityTimeSlot']);
    Route::delete('/members/{id}/availability', [\App\Http\Controllers\Member\MemberAvailabilityController::class, 'clearMemberAvailability']);
    Route::post('/members/{id}/availability/copy-day', [\App\Http\Controllers\Member\MemberAvailabilityController::class, 'copyDayAvailability']);
    Route::post('/members/{id}/availability/import', [\App\Http\Controllers\Member\MemberAvailabilityController::class, 'importAvailabilityFromCalendar']);

    // Member Import/Export
    Route::post('/members/import', [MemberImportExportController::class, 'import']);
    Route::get('/members/export', [MemberImportExportController::class, 'export']);
    Route::get('/members/template', [MemberImportExportController::class, 'template']);

    // Families
    Route::apiResource('families', FamilyController::class);
    Route::get('/families/{id}/members', [FamilyController::class, 'members']);

    // Attendance
    Route::apiResource('attendance', AttendanceController::class);
    Route::get('/attendance/events/{event_id}', [AttendanceController::class, 'byEvent']);
    Route::get('/attendance/members/{member_id}', [AttendanceController::class, 'byMember']);

    // Finance
    // Donations
    Route::apiResource('donations', DonationController::class);
    Route::get('/donations/statistics', [DonationController::class, 'statistics']);
    Route::post('/donations/{id}/send-receipt', [DonationController::class, 'sendReceipt']);
    Route::get('/members/{id}/donations', [DonationController::class, 'memberDonations']);
    Route::get('/members/{id}/gifts-received', [DonationController::class, 'memberGiftsReceived']);

    // Donation Categories
    Route::apiResource('donation-categories', DonationCategoryController::class);
    Route::post('/donation-categories/{id}/set-default', [DonationCategoryController::class, 'setDefault']);

    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::get('/projects/{id}/donations', [ProjectController::class, 'donations']);
    Route::patch('/projects/{id}/status', [ProjectController::class, 'updateStatus']);

    // Payments
    Route::post('/donations/{donationId}/payment', [PaymentController::class, 'processPayment']);
    Route::post('/donations/{donationId}/refund', [PaymentController::class, 'processRefund']);
    Route::get('/donations/{donationId}/transactions', [PaymentController::class, 'getTransactions']);
    Route::get('/payments/gateways', [PaymentController::class, 'getAvailableGateways']);

    // Recurring Donations
    Route::apiResource('recurring-donations', RecurringDonationController::class);
    Route::post('/recurring-donations/{id}/cancel', [RecurringDonationController::class, 'cancel']);
    Route::get('/recurring-donations/{id}/donations', [RecurringDonationController::class, 'getDonations']);
    Route::get('/members/{memberId}/recurring-donations', [RecurringDonationController::class, 'getMemberRecurringDonations']);
    Route::post('/recurring-donations/process', [RecurringDonationController::class, 'processDueRecurringDonations']);

    // Tax Receipts
    Route::get('/tax-receipts', [TaxReceiptController::class, 'index']);
    Route::get('/tax-receipts/stats', [TaxReceiptController::class, 'getStats']);
    Route::post('/donations/{donationId}/tax-receipt', [TaxReceiptController::class, 'generateForDonation']);
    Route::post('/members/{memberId}/annual-tax-receipt', [TaxReceiptController::class, 'generateAnnualForMember']);
    Route::post('/tax-receipts/generate-all-annual', [TaxReceiptController::class, 'generateAllAnnualReceipts']);
    Route::post('/tax-receipts/{id}/send', [TaxReceiptController::class, 'sendByEmail']);
    Route::post('/tax-receipts/{id}/void', [TaxReceiptController::class, 'voidReceipt']);
    Route::get('/tax-receipts/{id}/download', [TaxReceiptController::class, 'download']);
    Route::get('/members/{memberId}/tax-receipts', [TaxReceiptController::class, 'getMemberReceipts']);
    Route::get('/tax-receipts/yearly-summary', [TaxReceiptController::class, 'getYearlySummary']);
    Route::post('/payments/client-token', [PaymentController::class, 'getClientToken']);
    Route::post('/webhooks/{gateway}', [PaymentController::class, 'handleWebhook']);
    Route::apiResource('expenses', ExpenseController::class);
    Route::get('/expenses/statistics', [ExpenseController::class, 'statistics']);
    Route::apiResource('campaigns', CampaignController::class);
    Route::apiResource('budgets', BudgetController::class);
    Route::get('/budgets/categories', [BudgetController::class, 'categories']);
    Route::get('/budgets/overview', [BudgetController::class, 'overview']);
    Route::apiResource('pledges', PledgeController::class);
    Route::get('/pledges/statistics', [PledgeController::class, 'statistics']);
    Route::get('/pledges/upcoming', [PledgeController::class, 'upcoming']);

    // Financial Dashboard
    Route::get('/finance/summary', [FinancialController::class, 'summary']);
    Route::get('/finance/budget-utilization', [FinancialController::class, 'budgetUtilization']);
    Route::get('/finance/campaign-progress', [FinancialController::class, 'campaignProgress']);
    
    // Financial Forecasts
    Route::get('/finance/forecasts', [FinancialForecastController::class, 'index']);
    Route::post('/finance/forecasts', [FinancialForecastController::class, 'store']);
    Route::get('/finance/forecasts/{id}', [FinancialForecastController::class, 'show']);
    Route::put('/finance/forecasts/{id}', [FinancialForecastController::class, 'update']);
    Route::delete('/finance/forecasts/{id}', [FinancialForecastController::class, 'destroy']);
    Route::post('/finance/forecasts/{id}/items', [FinancialForecastController::class, 'addItem']);
    Route::put('/finance/forecasts/{forecastId}/items/{itemId}', [FinancialForecastController::class, 'updateItem']);
    Route::delete('/finance/forecasts/{forecastId}/items/{itemId}', [FinancialForecastController::class, 'deleteItem']);
    Route::get('/finance/forecasts/{id}/variance', [FinancialForecastController::class, 'getVariance']);
    
    // Budget Allocations
    Route::get('/finance/budget-allocations', [BudgetAllocationController::class, 'index']);
    Route::post('/finance/budget-allocations', [BudgetAllocationController::class, 'store']);
    Route::get('/finance/budget-allocations/{id}', [BudgetAllocationController::class, 'show']);
    Route::put('/finance/budget-allocations/{id}', [BudgetAllocationController::class, 'update']);
    Route::delete('/finance/budget-allocations/{id}', [BudgetAllocationController::class, 'destroy']);
    Route::get('/finance/budget-allocations/budget/{budgetId}', [BudgetAllocationController::class, 'getAllocationsForBudget']);
    Route::get('/finance/budget-allocations/department/{department}', [BudgetAllocationController::class, 'getAllocationsForDepartment']);
    Route::get('/finance/budget-allocations/ministry/{ministry}', [BudgetAllocationController::class, 'getAllocationsForMinistry']);
    Route::get('/finance/budget-allocations/project/{project}', [BudgetAllocationController::class, 'getAllocationsForProject']);
    Route::get('/finance/budget-allocations/category/{category}', [BudgetAllocationController::class, 'getAllocationsForCategory']);
    Route::get('/finance/budget-allocations/over-budget', [BudgetAllocationController::class, 'getOverBudgetAllocations']);
    Route::get('/finance/budget-allocations/near-limit', [BudgetAllocationController::class, 'getNearLimitAllocations']);
    Route::post('/finance/budget-allocations/{id}/update-used-amount', [BudgetAllocationController::class, 'updateUsedAmount']);
    Route::post('/finance/budget-allocations/update-all-used-amounts', [BudgetAllocationController::class, 'updateAllUsedAmounts']);
    
    // Financial Metric routes
    Route::prefix('financial-metrics')->group(function () {
        Route::get('/', [FinancialMetricController::class, 'index']);
        Route::post('/', [FinancialMetricController::class, 'store']);
        Route::get('/{id}', [FinancialMetricController::class, 'show']);
        Route::put('/{id}', [FinancialMetricController::class, 'update']);
        Route::delete('/{id}', [FinancialMetricController::class, 'destroy']);
    });
    
    // Skills routes
    Route::prefix('skills')->group(function () {
        Route::get('/', [\App\Http\Controllers\Member\SkillController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Member\SkillController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Member\SkillController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Member\SkillController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Member\SkillController::class, 'destroy']);
        Route::get('/categories', [\App\Http\Controllers\Member\SkillController::class, 'getCategories']);
        Route::get('/category/{category}', [\App\Http\Controllers\Member\SkillController::class, 'getSkillsByCategory']);
        Route::get('/{id}/members', [\App\Http\Controllers\Member\SkillController::class, 'getMembersWithSkill']);
    });
    
    // Interests routes
    Route::prefix('interests')->group(function () {
        Route::get('/', [\App\Http\Controllers\Member\InterestController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Member\InterestController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Member\InterestController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Member\InterestController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Member\InterestController::class, 'destroy']);
        Route::get('/categories', [\App\Http\Controllers\Member\InterestController::class, 'getCategories']);
        Route::get('/category/{category}', [\App\Http\Controllers\Member\InterestController::class, 'getInterestsByCategory']);
        Route::get('/{id}/members', [\App\Http\Controllers\Member\InterestController::class, 'getMembersWithInterest']);
    });
    
    // Spiritual Gifts routes
    Route::prefix('spiritual-gifts')->group(function () {
        Route::get('/', [\App\Http\Controllers\Member\SpiritualGiftController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Member\SpiritualGiftController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Member\SpiritualGiftController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Member\SpiritualGiftController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Member\SpiritualGiftController::class, 'destroy']);
        Route::get('/distribution', [\App\Http\Controllers\Member\SpiritualGiftController::class, 'getSpiritualGiftDistribution']);
        Route::get('/{id}/members', [\App\Http\Controllers\Member\SpiritualGiftController::class, 'getMembersWithSpiritualGift']);
    });
    
    // Member Availability routes
    Route::prefix('availability')->group(function () {
        Route::get('/statistics', [\App\Http\Controllers\Member\MemberAvailabilityController::class, 'getAvailabilityStatistics']);
        Route::post('/find-available-members', [\App\Http\Controllers\Member\MemberAvailabilityController::class, 'findAvailableMembers']);
    });

    // Financial Metrics
    Route::get('/finance/metrics', [FinancialMetricController::class, 'getMetricsByDateRange']);
    Route::post('/finance/metrics', [FinancialMetricController::class, 'store']);
    Route::put('/finance/metrics/{id}', [FinancialMetricController::class, 'update']);
    Route::delete('/finance/metrics/{id}', [FinancialMetricController::class, 'destroy']);
    Route::get('/finance/metrics/name/{metricName}', [FinancialMetricController::class, 'getMetricsByName']);
    Route::get('/finance/metrics/category/{category}', [FinancialMetricController::class, 'getMetricsByCategory']);
    Route::get('/finance/metrics/name/{metricName}/date-range', [FinancialMetricController::class, 'getMetricsByNameAndDateRange']);
    Route::get('/finance/metrics/name/{metricName}/category/{category}/date-range', [FinancialMetricController::class, 'getMetricsByNameCategoryAndDateRange']);
    Route::get('/finance/metrics/name/{metricName}/trend', [FinancialMetricController::class, 'getMetricTrend']);
    Route::post('/finance/metrics/calculate', [FinancialMetricController::class, 'calculateAndStoreMetrics']);
    Route::get('/finance/metrics/name/{metricName}/latest', [FinancialMetricController::class, 'getLatestMetricValue']);
    Route::get('/finance/metrics/name/{metricName}/category/{category}/latest', [FinancialMetricController::class, 'getLatestMetricValue']);
    Route::get('/finance/metrics/summary', [FinancialMetricController::class, 'getFinancialSummary']);
    Route::get('/finance/dashboard', [FinancialMetricController::class, 'getDashboardData']);

    // Financial Reports
    Route::get('/finance/reports/summary', [FinancialReportController::class, 'getSummary']);
    Route::get('/finance/reports/chart-data', [FinancialReportController::class, 'getChartData']);
    Route::post('/finance/reports/generate-pdf', [FinancialReportController::class, 'generatePdfReport']);
    Route::post('/finance/reports/export-excel', [FinancialReportController::class, 'exportToExcel']);

    // Events
    Route::apiResource('events', EventController::class);

    // Communications
    Route::apiResource('communications', CommunicationController::class);
    Route::post('/communications/send', [CommunicationController::class, 'send']);
    Route::apiResource('prayer-requests', PrayerRequestController::class);

    // WhatsApp Messaging
    Route::post('/whatsapp/send', [WhatsAppController::class, 'sendMessage']);
    Route::post('/whatsapp/send-bulk', [WhatsAppController::class, 'sendBulkMessage']);
    Route::post('/whatsapp/send-template', [WhatsAppController::class, 'sendTemplateMessage']);

    // Volunteers
    Route::apiResource('volunteers', VolunteerController::class);
    Route::apiResource('volunteer-roles', VolunteerRoleController::class);
    
    // Groups
    Route::apiResource('groups', GroupController::class);
    Route::get('/groups/statistics', [GroupController::class, 'statistics']);
    Route::get('/groups/{groupId}/members', [GroupMemberController::class, 'index']);
    Route::post('/groups/{groupId}/members', [GroupMemberController::class, 'store']);
    Route::post('/groups/{groupId}/members/batch', [GroupMemberController::class, 'batchStore']);
    Route::put('/groups/{groupId}/members/{memberId}', [GroupMemberController::class, 'update']);
    Route::delete('/groups/{groupId}/members/{memberId}', [GroupMemberController::class, 'destroy']);
    Route::get('groups/{groupId}/member-roles', [GroupMemberRoleController::class, 'getGroupMemberRoles']);
    Route::put('groups/{groupId}/member-roles/{memberId}', [GroupMemberRoleController::class, 'updateMemberRole']);
    Route::get('group-roles/permissions', [GroupMemberRoleController::class, 'getAvailablePermissions']);
    Route::get('group-roles/{role}/default-permissions', [GroupMemberRoleController::class, 'getDefaultPermissionsForRole']);
    
    // Group Attendance Routes
    Route::get('groups/{id}/attendances', [GroupAttendanceController::class, 'index']);
    Route::post('groups/{id}/attendances', [GroupAttendanceController::class, 'store']);
    Route::get('groups/{id}/attendances/{attendanceId}', [GroupAttendanceController::class, 'show']);
    Route::put('groups/{id}/attendances/{attendanceId}', [GroupAttendanceController::class, 'update']);
    Route::delete('groups/{id}/attendances/{attendanceId}', [GroupAttendanceController::class, 'destroy']);
    Route::get('groups/{id}/attendances/{attendanceId}/details', [GroupAttendanceController::class, 'getAttendanceDetails']);
    Route::post('groups/{id}/attendances/{attendanceId}/details', [GroupAttendanceController::class, 'addAttendanceDetail']);
    Route::put('groups/{id}/attendances/{attendanceId}/details/{detailId}', [GroupAttendanceController::class, 'updateAttendanceDetail']);
    Route::delete('groups/{id}/attendances/{attendanceId}/details/{detailId}', [GroupAttendanceController::class, 'removeAttendanceDetail']);

    // Group Event Routes
    Route::get('groups/{id}/events', [GroupEventController::class, 'index']);
    Route::post('groups/{id}/events', [GroupEventController::class, 'store']);
    Route::get('groups/{id}/events/{eventId}', [GroupEventController::class, 'show']);
    Route::put('groups/{id}/events/{eventId}', [GroupEventController::class, 'update']);
    Route::delete('groups/{id}/events/{eventId}', [GroupEventController::class, 'destroy']);
    
    // Event Category Routes
    Route::get('event-categories', [EventCategoryController::class, 'index']);
    Route::get('event-categories/active', [EventCategoryController::class, 'getActiveCategories']);
    Route::post('event-categories', [EventCategoryController::class, 'store']);
    Route::get('event-categories/{id}', [EventCategoryController::class, 'show']);
    Route::put('event-categories/{id}', [EventCategoryController::class, 'update']);
    Route::delete('event-categories/{id}', [EventCategoryController::class, 'destroy']);
    
    // Event Resource Routes
    Route::get('groups/{groupId}/events/{eventId}/resources', [EventResourceController::class, 'index']);
    Route::post('groups/{groupId}/events/{eventId}/resources', [EventResourceController::class, 'store']);
    Route::get('groups/{groupId}/events/{eventId}/resources/{resourceId}', [EventResourceController::class, 'show']);
    Route::post('groups/{groupId}/events/{eventId}/resources/{resourceId}', [EventResourceController::class, 'update']);
    Route::delete('groups/{groupId}/events/{eventId}/resources/{resourceId}', [EventResourceController::class, 'destroy']);
    Route::get('events/{eventId}/public-resources', [EventResourceController::class, 'getPublicResources']);
    Route::get('event-resources/{resourceId}/download', [EventResourceController::class, 'download']);
    
    // Event Registration Routes
    Route::get('groups/{groupId}/events/{eventId}/registrations', [EventRegistrationController::class, 'index']);
    Route::post('groups/{groupId}/events/{eventId}/registrations', [EventRegistrationController::class, 'store']);
    Route::get('groups/{groupId}/events/{eventId}/registrations/{registrationId}', [EventRegistrationController::class, 'show']);
    Route::put('groups/{groupId}/events/{eventId}/registrations/{registrationId}', [EventRegistrationController::class, 'update']);
    Route::delete('groups/{groupId}/events/{eventId}/registrations/{registrationId}', [EventRegistrationController::class, 'destroy']);
    Route::get('groups/{groupId}/events/{eventId}/registration-stats', [EventRegistrationController::class, 'getRegistrationStats']);
    Route::get('members/{memberId}/event-registrations', [EventRegistrationController::class, 'getMemberRegistrations']);
    Route::get('events/{eventId}/members/{memberId}/check-registration', [EventRegistrationController::class, 'checkMemberRegistration']);
    
    // Event Reminder Routes
    Route::get('groups/{groupId}/events/{eventId}/reminders', [EventReminderController::class, 'index']);
    Route::post('groups/{groupId}/events/{eventId}/reminders', [EventReminderController::class, 'store']);
    Route::get('groups/{groupId}/events/{eventId}/reminders/{reminderId}', [EventReminderController::class, 'show']);
    Route::put('groups/{groupId}/events/{eventId}/reminders/{reminderId}', [EventReminderController::class, 'update']);
    Route::delete('groups/{groupId}/events/{eventId}/reminders/{reminderId}', [EventReminderController::class, 'destroy']);
    Route::get('event-reminders/pending', [EventReminderController::class, 'getPendingReminders']);
    Route::post('event-reminders/{reminderId}/mark-sent', [EventReminderController::class, 'markAsSent']);
    
    // Event Share Routes
    Route::get('groups/{groupId}/events/{eventId}/shares', [EventShareController::class, 'index']);
    Route::post('groups/{groupId}/events/{eventId}/shares', [EventShareController::class, 'store']);
    Route::get('groups/{groupId}/events/{eventId}/shares/{shareId}', [EventShareController::class, 'show']);
    Route::put('groups/{groupId}/events/{eventId}/shares/{shareId}', [EventShareController::class, 'update']);
    Route::delete('groups/{groupId}/events/{eventId}/shares/{shareId}', [EventShareController::class, 'destroy']);
    Route::get('groups/{groupId}/events/{eventId}/shares/status/{status}', [EventShareController::class, 'getSharesByStatus']);
    Route::get('groups/{groupId}/shared-events', [EventShareController::class, 'getSharesForGroup']);
    Route::get('members/{memberId}/shared-events', [EventShareController::class, 'getSharesForMember']);
    Route::post('event-shares/{token}/accept', [EventShareController::class, 'acceptShare']);
    Route::post('event-shares/{token}/decline', [EventShareController::class, 'declineShare']);
    Route::get('shared-events/{token}', [EventShareController::class, 'viewSharedEvent']);
    
    // Group Permission Routes
    Route::get('group-permissions', [GroupPermissionController::class, 'getAllPermissions']);
    Route::get('group-permissions/categories', [GroupPermissionController::class, 'getAllCategories']);
    Route::get('group-permissions/category/{category}', [GroupPermissionController::class, 'getPermissionsByCategory']);
    Route::get('groups/{groupId}/permissions', [GroupPermissionController::class, 'getGroupRolePermissions']);
    Route::get('groups/{groupId}/roles/{role}/permissions', [GroupPermissionController::class, 'getRolePermissions']);
    Route::post('groups/{groupId}/roles/{role}/permissions', [GroupPermissionController::class, 'assignPermissionsToRole']);
    Route::delete('groups/{groupId}/roles/{role}/permissions/{permissionId}', [GroupPermissionController::class, 'removePermissionFromRole']);
    Route::get('groups/{groupId}/roles/{role}/permissions/{permissionSlug}/check', [GroupPermissionController::class, 'checkRolePermission']);
    Route::post('groups/{groupId}/permissions/initialize', [GroupPermissionController::class, 'initializeDefaultRolePermissions']);
    
    // Group Analytics Routes
    Route::get('groups/{groupId}/analytics/dashboard', [GroupAnalyticsController::class, 'getDashboard']);
    Route::post('groups/{groupId}/analytics/generate', [GroupAnalyticsController::class, 'generateAnalytics']);
    Route::get('groups/{groupId}/analytics/attendance', [GroupAnalyticsController::class, 'getAttendanceAnalytics']);
    Route::get('groups/{groupId}/analytics/growth', [GroupAnalyticsController::class, 'getGrowthAnalytics']);
    Route::get('groups/{groupId}/analytics/engagement', [GroupAnalyticsController::class, 'getEngagementAnalytics']);
    Route::get('groups/{groupId}/analytics/members', [GroupAnalyticsController::class, 'getMemberEngagementAnalytics']);
    Route::get('groups/{groupId}/analytics/members/{memberId}/trend', [GroupAnalyticsController::class, 'getMemberEngagementTrend']);
    Route::get('groups/analytics/comparison', [GroupAnalyticsController::class, 'getGroupsComparison']);
    Route::post('groups/analytics/schedule', [GroupAnalyticsController::class, 'scheduleAnalyticsGeneration']);
    Route::get('groups/{groupId}/analytics/comprehensive', [GroupAnalyticsController::class, 'getComprehensiveAnalytics']);
    
    // Group Messages Routes
    Route::get('groups/{groupId}/messages', [GroupMessageController::class, 'index']);
    Route::post('groups/{groupId}/messages', [GroupMessageController::class, 'store']);
    Route::get('groups/{groupId}/messages/{messageId}', [GroupMessageController::class, 'show']);
    Route::put('groups/{groupId}/messages/{messageId}', [GroupMessageController::class, 'update']);
    Route::delete('groups/{groupId}/messages/{messageId}', [GroupMessageController::class, 'destroy']);
    Route::get('groups/{groupId}/announcements', [GroupMessageController::class, 'getAnnouncements']);
    Route::get('groups/{groupId}/pinned-messages', [GroupMessageController::class, 'getPinnedMessages']);
    Route::get('groups/{groupId}/unread-count', [GroupMessageController::class, 'getUnreadCount']);
    Route::post('groups/{groupId}/mark-all-read', [GroupMessageController::class, 'markAllAsRead']);
    
    // Group Prayer Requests Routes
    Route::get('groups/{groupId}/prayer-requests', [GroupPrayerRequestController::class, 'index']);
    Route::post('groups/{groupId}/prayer-requests', [GroupPrayerRequestController::class, 'store']);
    Route::get('groups/{groupId}/prayer-requests/{requestId}', [GroupPrayerRequestController::class, 'show']);
    Route::put('groups/{groupId}/prayer-requests/{requestId}', [GroupPrayerRequestController::class, 'update']);
    Route::delete('groups/{groupId}/prayer-requests/{requestId}', [GroupPrayerRequestController::class, 'destroy']);
    Route::post('groups/{groupId}/prayer-requests/{requestId}/answer', [GroupPrayerRequestController::class, 'markAsAnswered']);
    Route::post('groups/{groupId}/prayer-requests/{requestId}/archive', [GroupPrayerRequestController::class, 'archive']);
    Route::post('groups/{groupId}/prayer-requests/{requestId}/reactivate', [GroupPrayerRequestController::class, 'reactivate']);
    Route::post('groups/{groupId}/prayer-requests/{requestId}/responses', [GroupPrayerRequestController::class, 'addResponse']);
    Route::post('groups/{groupId}/prayer-requests/{requestId}/toggle-praying', [GroupPrayerRequestController::class, 'togglePraying']);
    Route::get('groups/{groupId}/prayer-requests-stats', [GroupPrayerRequestController::class, 'getStats']);
    
    // Group Documents Routes
    Route::get('groups/{groupId}/documents', [GroupDocumentController::class, 'index']);
    Route::post('groups/{groupId}/documents', [GroupDocumentController::class, 'store']);
    Route::get('groups/{groupId}/documents/{documentId}', [GroupDocumentController::class, 'show']);
    Route::put('groups/{groupId}/documents/{documentId}', [GroupDocumentController::class, 'update']);
    Route::delete('groups/{groupId}/documents/{documentId}', [GroupDocumentController::class, 'destroy']);
    Route::get('groups/{groupId}/documents/{documentId}/download', [GroupDocumentController::class, 'download']);
    Route::get('groups/{groupId}/document-categories', [GroupDocumentController::class, 'getCategories']);
    Route::get('groups/{groupId}/recent-documents', [GroupDocumentController::class, 'getRecentDocuments']);
    Route::get('groups/{groupId}/most-accessed-documents', [GroupDocumentController::class, 'getMostAccessedDocuments']);

    // Reports
    Route::apiResource('reports', ReportController::class);

    // Custom Fields
    Route::get('/custom-fields', [CustomFieldController::class, 'index']);
    Route::post('/custom-fields', [CustomFieldController::class, 'store']);
    Route::get('/custom-fields/{id}', [CustomFieldController::class, 'show']);
    Route::put('/custom-fields/{id}', [CustomFieldController::class, 'update']);
    Route::delete('/custom-fields/{id}', [CustomFieldController::class, 'destroy']);
    Route::post('/custom-fields/reorder', [CustomFieldController::class, 'reorder']);
    Route::get('/reports/types', [ReportController::class, 'types']);
    Route::get('/reports/metrics', [ReportController::class, 'metrics']);
    Route::get('/reports/donations-chart', [ReportController::class, 'donationsChart']);
    Route::get('/reports/attendance-chart', [ReportController::class, 'attendanceChart']);
    Route::get('/reports/{id}/generate', [ReportController::class, 'generate']);
    Route::post('/reports/{id}/toggle-favorite', [ReportController::class, 'toggleFavorite']);
    Route::post('/reports/{id}/schedule', [ReportController::class, 'scheduleReport']);
    Route::post('/reports/{id}/apply-template/{template_id}', [ReportController::class, 'applyTemplate']);

    // Report Templates
    Route::apiResource('report-templates', ReportController::class);
    Route::get('/report-templates/types', [ReportController::class, 'templateTypes']);
});
