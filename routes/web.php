<?php

use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\FuelRequisitionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderLegController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/notifications', [NotificationController::class, 'center'])->name('notifications.center');
    Route::get('/notifications/data', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::middleware('permission:customers.view|customers.create')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    });
    Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:customers.create')->name('customers.store');

    Route::middleware('permission:trips.view|trips.create')->group(function () {
        Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    });
    Route::post('/trips', [TripController::class, 'store'])->middleware('permission:trips.create')->name('trips.store');
    Route::post('/trips/{tripId}/close', [TripController::class, 'close'])->middleware('permission:trips.close')->name('trips.close');

    Route::middleware('permission:orders.view|orders.create')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    });
    Route::post('/orders', [OrderController::class, 'store'])->middleware('permission:orders.create')->name('orders.store');
    Route::post('/orders/{orderId}/status', [OrderController::class, 'updateStatus'])->middleware('permission:orders.status.update')->name('orders.status.update');
    Route::get('/orders/{orderId}/legs', [OrderLegController::class, 'index'])->middleware('permission:fleet.assign')->name('orders.legs.index');
    Route::post('/orders/{orderId}/legs', [OrderLegController::class, 'store'])->middleware('permission:fleet.assign')->name('orders.legs.store');
    Route::post('/order-legs/{legId}/complete', [OrderLegController::class, 'complete'])->middleware('permission:fleet.assign')->name('orders.legs.complete');

    Route::middleware('permission:fleet.view|fleet.create')->group(function () {
        Route::get('/fleet', [FleetController::class, 'index'])->name('fleet.index');
    });
    Route::post('/fleet', [FleetController::class, 'store'])->middleware('permission:fleet.create')->name('fleet.store');

    Route::middleware('permission:drivers.view|drivers.create')->group(function () {
        Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
    });
    Route::post('/drivers', [DriverController::class, 'store'])->middleware('permission:drivers.create')->name('drivers.store');
    Route::put('/drivers/{driverId}', [DriverController::class, 'update'])->middleware('permission:drivers.update')->name('drivers.update');

    Route::middleware('permission:fuel.view|fuel.create')->group(function () {
        Route::get('/fuel', [FuelRequisitionController::class, 'index'])->name('fuel.index');
    });
    Route::post('/fuel', [FuelRequisitionController::class, 'store'])->middleware('permission:fuel.create')->name('fuel.store');
    Route::post('/fuel/{requisitionId}/supervisor-decision', [FuelRequisitionController::class, 'supervisorDecision'])->middleware('permission:fuel.approve.supervisor')->name('fuel.supervisor.decision');
    Route::post('/fuel/{requisitionId}/accountant-decision', [FuelRequisitionController::class, 'accountantDecision'])->middleware('permission:fuel.approve.accounting')->name('fuel.accountant.decision');

    Route::prefix('admin')->name('admin.')->middleware('permission:admin.users.manage')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });

    Route::prefix('admin')->name('admin.')->middleware('permission:admin.logs.view')->group(function () {
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
