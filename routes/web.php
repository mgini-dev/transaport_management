<?php

use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
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
    Route::get('/notifications/export/csv', [NotificationController::class, 'exportCsv'])->name('notifications.export.csv');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{notificationId}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/delete-many', [NotificationController::class, 'destroyMany'])->name('notifications.destroy_many');

    Route::middleware('permission:customers.view|customers.create|customers.edit|customers.delete')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    });
    Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:customers.create')->name('customers.store');
    Route::put('/customers/{customerId}', [CustomerController::class, 'update'])->middleware('permission:customers.edit')->name('customers.update');
    Route::delete('/customers/{customerId}', [CustomerController::class, 'destroy'])->middleware('permission:customers.delete')->name('customers.destroy');

    Route::middleware('permission:trips.view|trips.create')->group(function () {
        Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
        Route::get('/trips/{tripId}', [TripController::class, 'show'])->name('trips.show');
    });
    Route::post('/trips', [TripController::class, 'store'])->middleware('permission:trips.create')->name('trips.store');
    Route::post('/trips/{tripId}/close', [TripController::class, 'close'])->middleware('permission:trips.close')->name('trips.close');

    Route::middleware('permission:orders.view|orders.create')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{orderId}', [OrderController::class, 'show'])->name('orders.show');
    });
    Route::post('/orders', [OrderController::class, 'store'])->middleware('permission:orders.create')->name('orders.store');
    Route::post('/orders/{orderId}/status', [OrderController::class, 'updateStatus'])->middleware('permission:orders.status.update')->name('orders.status.update');
    Route::post('/orders/{orderId}/distance/calculate', [OrderController::class, 'calculateDistance'])->middleware('permission:orders.view_distance')->name('orders.distance.calculate');
    Route::get('/orders/{orderId}/delivery-note/pdf', [OrderController::class, 'deliveryNotePdf'])->name('orders.delivery_note.pdf');
    Route::post('/orders/{orderId}/complete-transport', [OrderController::class, 'completeTransportation'])->name('orders.complete_transport');
    Route::get('/orders/{orderId}/completion-document/preview', [OrderController::class, 'previewCompletionDocument'])->name('orders.completion_document.preview');
    Route::get('/orders/{orderId}/completion-document', [OrderController::class, 'downloadCompletionDocument'])->name('orders.completion_document.download');
    Route::get('/orders/{orderId}/legs', [OrderLegController::class, 'index'])->middleware('permission:fleet.assign')->name('orders.legs.index');
    Route::post('/orders/{orderId}/legs', [OrderLegController::class, 'store'])->middleware('permission:fleet.assign')->name('orders.legs.store');
    Route::post('/order-legs/{legId}/complete', [OrderLegController::class, 'complete'])->middleware('permission:fleet.assign')->name('orders.legs.complete');

    Route::middleware('permission:fleet.view|fleet.create')->group(function () {
        Route::get('/fleet', [FleetController::class, 'index'])->name('fleet.index');
        Route::get('/fleet/{fleetId}/edit', [FleetController::class, 'edit'])->name('fleet.edit');
    });
    Route::post('/fleet', [FleetController::class, 'store'])->middleware('permission:fleet.create')->name('fleet.store');
    Route::put('/fleet/{fleetId}', [FleetController::class, 'update'])->middleware('permission:fleet.create')->name('fleet.update');
    Route::delete('/fleet/{fleetId}', [FleetController::class, 'destroy'])->middleware('permission:fleet.delete')->name('fleet.destroy');

    Route::middleware('permission:drivers.view|drivers.create')->group(function () {
        Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
    });
    Route::post('/drivers', [DriverController::class, 'store'])->middleware('permission:drivers.create')->name('drivers.store');
    Route::put('/drivers/{driverId}', [DriverController::class, 'update'])->middleware('permission:drivers.update')->name('drivers.update');
    Route::delete('/drivers/{driverId}', [DriverController::class, 'destroy'])->middleware('permission:drivers.delete')->name('drivers.destroy');

    Route::middleware('permission:fuel.view|fuel.create')->group(function () {
        Route::get('/fuel', [FuelRequisitionController::class, 'index'])->name('fuel.index');
    });
    Route::get('/fuel/{requisitionId}', [FuelRequisitionController::class, 'show'])
        ->middleware('permission:fuel.view|fuel.approve.supervisor|fuel.approve.accounting')
        ->name('fuel.show');
    Route::post('/fuel', [FuelRequisitionController::class, 'store'])->middleware('permission:fuel.create')->name('fuel.store');
    Route::post('/fuel/distance/estimate', [FuelRequisitionController::class, 'estimateDistance'])->middleware('permission:fuel.create')->name('fuel.distance.estimate');
    Route::post('/fuel/balance', [FuelRequisitionController::class, 'storeBalance'])->middleware('permission:fuel.create')->name('fuel.balance.store');
    Route::post('/fuel/{requisitionId}/supervisor-decision', [FuelRequisitionController::class, 'supervisorDecision'])->middleware('permission:fuel.approve.supervisor')->name('fuel.supervisor.decision');
    Route::post('/fuel/{requisitionId}/accountant-decision', [FuelRequisitionController::class, 'accountantDecision'])->middleware('permission:fuel.approve.accounting')->name('fuel.accountant.decision');

    Route::prefix('admin')->name('admin.')->middleware('permission:admin.users.manage')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/export/csv', [UserController::class, 'exportCsv'])->name('users.export.csv');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });

    Route::prefix('admin')->name('admin.')->middleware('permission:admin.roles.manage')->group(function () {
        Route::get('/roles-permissions', [RolePermissionController::class, 'index'])->name('roles.index');
        Route::post('/permissions', [RolePermissionController::class, 'storePermission'])->name('permissions.store');
        Route::post('/roles', [RolePermissionController::class, 'storeRole'])->name('roles.store');
        Route::put('/roles/{role}', [RolePermissionController::class, 'updateRole'])->name('roles.update');
        Route::put('/users/{user}/permissions', [RolePermissionController::class, 'updateUserPermissions'])->name('users.permissions.update');
    });

    Route::prefix('admin')->name('admin.')->middleware('permission:admin.logs.view')->group(function () {
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        Route::get('/logs/export/csv', [LogController::class, 'exportCsv'])->name('logs.export.csv');
    });

    Route::prefix('admin')->name('admin.')->middleware('permission:admin.dashboard.view_all')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
