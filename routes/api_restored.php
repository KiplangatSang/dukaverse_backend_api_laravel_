<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\EcommerceSettingController;
use App\Http\Controllers\EcommerceVendorController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermissionTierController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskDependancyController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // Ecommerce Vendor routes
    Route::prefix('v1/ecommerce/vendors')->group(function () {
        Route::get('/', [EcommerceVendorController::class, 'index']);
        Route::post('/', [EcommerceVendorController::class, 'store']);
        Route::get('/{id}', [EcommerceVendorController::class, 'show']);
        Route::put('/{id}', [EcommerceVendorController::class, 'update']);
        Route::delete('/{id}', [EcommerceVendorController::class, 'destroy']);
    });

    // Ecommerce Setting routes
    Route::prefix('v1/ecommerce/settings')->group(function () {
        Route::post('/', [EcommerceSettingController::class, 'store']);
        Route::put('/{id}', [EcommerceSettingController::class, 'update']);
        Route::get('/{id}', [EcommerceSettingController::class, 'show']);
        Route::delete('/{id}', [EcommerceSettingController::class, 'destroy']);
    });

    // Permissions routes
    Route::get('/user/permissions-list', [PermissionController::class, 'permissionsList']);
    Route::post('/user/permissions', [PermissionController::class, 'store']);
    Route::get('/user/permissions/{id}', [PermissionController::class, 'show']);
    Route::put('/user/permissions/{id}', [PermissionController::class, 'update']);
    Route::delete('/user/permissions/{id}', [PermissionController::class, 'destroy']);

    // Tier-Permission linking routes
    Route::get('/tiers/{tier}/permissions', [PermissionTierController::class, 'index']);
    Route::post('/tiers/{tier}/permissions', [PermissionTierController::class, 'store']);
    Route::delete('/tiers/{tier}/permissions/{permission}', [PermissionTierController::class, 'destroy']);

    // Project resource routes
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/change-priority', [ProjectController::class, 'changePriority']);
    Route::post('/projects/{project}/comments', [ProjectController::class, 'addComment']);
    Route::put('/projects/{project}/comments/{comment}', [ProjectController::class, 'updateComment']);
    Route::delete('/projects/{project}/comments/{comment}', [ProjectController::class, 'deleteComment']);

    // Campaigns resource routes
    Route::resource('campaigns', CampaignController::class);
    Route::post('/campaigns/{campaign_id}/teams/members', [CampaignController::class, "addMemberToCampaignTeam"]);

    // Task resource routes
    Route::resource('tasks', TaskController::class);
    Route::post('tasks/{task_id}/assign', [TaskController::class, 'assignTask']);

    // Task dependency routes
    Route::resource('task-dependencies', TaskDependancyController::class);

    // Todos resource routes with type-based filtering
    Route::resource('todos', TodoController::class);
    Route::delete('todos/delete/{all}', [TodoController::class, 'deleteAll']);
    Route::get('todos/{type}', [TodoController::class, 'index']);
    Route::get('todos/create/{type}', [TodoController::class, 'create']);
    Route::get('todos/{todo}/{type}', [TodoController::class, 'show']);
    Route::put('todos/{todo}/edit/{type}', [TodoController::class, 'edit']);
    Route::put('todos/update/{todo}/{type}', [TodoController::class, 'update']);

    // Kanban routes for projects tasks
    Route::prefix('kanban')->group(function () {
        Route::get('projects/{project_id}/tasks', [KanbanController::class, 'index']);
        Route::put('projects/{project_id}/tasks/update-positions', [KanbanController::class, 'updateKanbanboard']);
    });

    // Calendar resource routes
    Route::resource('calendars', CalendarController::class);
    Route::post('calendars/create-from-task/{task_id}', [CalendarController::class, 'createFromTask']);
    Route::put('calendars/{calendar}/reschedule', [CalendarController::class, 'reschedule']);
    Route::post('calendars/{calendar}/attendees', [CalendarController::class, 'addAttendees']);
    Route::delete('calendars/{calendar}/attendees/{attendee}', [CalendarController::class, 'removeAttendee']);
    Route::post('calendars/{calendar}/conflicts/check', [CalendarController::class, 'checkConflicts']);

    // Lead resource routes
    Route::resource('leads', LeadController::class);
    Route::post('/campaign/{campaign_id}/leads', [LeadController::class, "addLeadsToCampaign"]);

    // Subscription and Coupon resources
    Route::resource('subscriptions', SubscriptionController::class);
    Route::resource('coupons', CouponController::class);
});
