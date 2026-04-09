<?php

use App\Http\Controllers\CabinetController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquipmentTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EquipmentRequestController;
use App\Http\Controllers\ActivityArchiveController;
use App\Http\Controllers\UserController;
use App\Models\Equipment;
use App\Models\Department;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'equipmentCount' => Equipment::count(),
        'departmentCount' => Department::count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
    Route::get('/equipment/{equipment}', [EquipmentController::class, 'show'])
        ->middleware('not.accountant')
        ->name('equipment.show')
        ->whereNumber('equipment');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Бухгалтер: только список и присвоение инвентарного номера из списка
    Route::middleware('accountant')->group(function () {
        Route::patch('/equipment/{equipment}/inventory-number', [EquipmentController::class, 'updateInventoryNumber'])
            ->name('equipment.inventory-number.update');
    });

    Route::middleware(['utilization.manage', 'not.accountant'])->group(function () {
        Route::post('/equipment/{equipment}/utilize', [EquipmentController::class, 'markUtilized'])
            ->name('equipment.utilize')
            ->whereNumber('equipment');
        Route::post('/equipment/{equipment}/utilization-act', [EquipmentController::class, 'storeUtilizationAct'])
            ->name('equipment.utilization-act.store')
            ->whereNumber('equipment');
    });

    // Оборудование: добавление и редактирование — администратор и старшая медсестра
    Route::middleware('equipment.manage')->group(function () {
        Route::get('/equipment/create', [EquipmentController::class, 'create'])->name('equipment.create');
        Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
        Route::post('/equipment/{equipment}/documents', [EquipmentController::class, 'storeDocument'])->name('equipment.documents.store');
        Route::get('/equipment/{equipment}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
        Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
        Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');

        // Заявки от старшей медсестры
        Route::post('/equipment/{equipment}/requests/writeoff', [EquipmentRequestController::class, 'storeWriteoff'])->name('equipment.requests.writeoff');
        Route::post('/equipment/{equipment}/requests/move', [EquipmentRequestController::class, 'storeMove'])->name('equipment.requests.move');
    });

    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');

        // Заявки: список и действия
        Route::get('/equipment-requests', [EquipmentRequestController::class, 'index'])->name('equipment-requests.index');
        Route::post('/equipment/{equipment}/approve-writeoff', [EquipmentRequestController::class, 'approveWriteoff'])->name('equipment.requests.approveWriteoff');
        Route::post('/equipment-requests/{equipmentRequest}/approve-move', [EquipmentRequestController::class, 'approveMove'])->name('equipment-requests.approveMove');
        Route::post('/equipment-requests/{equipmentRequest}/reject', [EquipmentRequestController::class, 'reject'])->name('equipment-requests.reject');

        Route::get('/equipment-types', [EquipmentTypeController::class, 'index'])->name('equipment-types.index');
        Route::post('/equipment-types', [EquipmentTypeController::class, 'store'])->name('equipment-types.store');
        Route::delete('/equipment-types/{equipmentType}', [EquipmentTypeController::class, 'destroy'])->name('equipment-types.destroy');

        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        Route::get('/cabinets', [CabinetController::class, 'index'])->name('cabinets.index');
        Route::post('/cabinets', [CabinetController::class, 'store'])->name('cabinets.store');
        Route::delete('/cabinets/{cabinet}', [CabinetController::class, 'destroy'])->name('cabinets.destroy');

        Route::get('/admin/activity-archive', [ActivityArchiveController::class, 'index'])->name('admin.activity-archive');
        Route::post('/admin/activity-archive/clear', [ActivityArchiveController::class, 'clear'])->name('admin.activity-archive.clear');
        Route::post('/admin/activity-archive/clear-filtered', [ActivityArchiveController::class, 'clearFiltered'])->name('admin.activity-archive.clear-filtered');
        Route::post('/admin/activity-archive/delete-selected', [ActivityArchiveController::class, 'deleteSelected'])->name('admin.activity-archive.delete-selected');
        Route::post('/admin/activity-archive/revision/{activityLog}/restore', [ActivityArchiveController::class, 'restoreRevision'])->name('admin.activity-archive.restore-revision');
        Route::post('/admin/activity-archive/equipment-type/{id}/restore', [ActivityArchiveController::class, 'restoreEquipmentType'])->name('admin.activity-archive.restore-equipment-type')->whereNumber('id');
        Route::post('/admin/activity-archive/department/{id}/restore', [ActivityArchiveController::class, 'restoreDepartment'])->name('admin.activity-archive.restore-department')->whereNumber('id');
        Route::post('/admin/activity-archive/cabinet/{id}/restore', [ActivityArchiveController::class, 'restoreCabinet'])->name('admin.activity-archive.restore-cabinet')->whereNumber('id');
        Route::post('/admin/activity-archive/{id}/restore', [ActivityArchiveController::class, 'restore'])->name('admin.activity-archive.restore');
    });
});

require __DIR__.'/auth.php';
