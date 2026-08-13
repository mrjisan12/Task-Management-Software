<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyJoinController;
use App\Http\Controllers\CompanySwitchController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('employee.dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::redirect('/register', '/login')->name('register');
    Route::post('/register', fn () => redirect()->route('login'))->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', EmployeeDashboardController::class)->name('employee.dashboard');
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::put('/notifications/settings', [NotificationController::class, 'updateSettings'])->name('notifications.settings');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/company/join', [CompanyJoinController::class, 'store'])->name('company.join');
    Route::post('/company/{company}/switch', CompanySwitchController::class)->name('company.switch');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
