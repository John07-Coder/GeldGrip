<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FixedExpenseController;
use App\Http\Controllers\IncomeSourceController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::view('/inloggen', 'auth.login')->name('login');
    Route::post('/inloggen', [AuthController::class, 'login'])->middleware('throttle:20,1');
    Route::view('/registreren', 'auth.register')->name('register');
    Route::post('/registreren', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::view('/wachtwoord-vergeten', 'auth.forgot-password')->name('password.request');
    Route::post('/wachtwoord-vergeten', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/wachtwoord-herstellen/{token}', fn (string $token) => view('auth.reset-password', compact('token')))->name('password.reset');
    Route::post('/wachtwoord-herstellen', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('inkomsten', IncomeSourceController::class)->except('show')->names('incomes')->parameters(['inkomsten' => 'income']);
    Route::resource('vaste-lasten', FixedExpenseController::class)->except('show')->names('expenses')->parameters(['vaste-lasten' => 'expense']);
    Route::get('/instellingen', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/instellingen', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/instellingen/wachtwoord', [SettingsController::class, 'password'])->name('settings.password');
    Route::delete('/instellingen', [SettingsController::class, 'destroy'])->name('settings.destroy');
    Route::post('/uitloggen', [AuthController::class, 'logout'])->name('logout');
});
