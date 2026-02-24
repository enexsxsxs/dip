<?php

use App\Http\Controllers\CabinetController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquipmentTypeController;
use App\Http\Controllers\ProfileController;
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
    Route::get('/equipment/{equipment}', [EquipmentController::class, 'show'])->name('equipment.show')->whereNumber('equipment');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/equipment/create', [EquipmentController::class, 'create'])->name('equipment.create');
        Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
        Route::post('/equipment/{equipment}/documents', [EquipmentController::class, 'storeDocument'])->name('equipment.documents.store');
        Route::get('/equipment/{equipment}/edit', [EquipmentController::class, 'edit'])->name('equipment.edit');
        Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
        Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');

        Route::get('/equipment-types', [EquipmentTypeController::class, 'index'])->name('equipment-types.index');
        Route::post('/equipment-types', [EquipmentTypeController::class, 'store'])->name('equipment-types.store');
        Route::delete('/equipment-types/{equipmentType}', [EquipmentTypeController::class, 'destroy'])->name('equipment-types.destroy');

        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        Route::get('/cabinets', [CabinetController::class, 'index'])->name('cabinets.index');
        Route::post('/cabinets', [CabinetController::class, 'store'])->name('cabinets.store');
        Route::delete('/cabinets/{cabinet}', [CabinetController::class, 'destroy'])->name('cabinets.destroy');
    });
});

require __DIR__.'/auth.php';
