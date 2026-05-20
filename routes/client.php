<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ReservationsController;
use App\Http\Controllers\Client\SupportController;
use App\Http\Controllers\Client\DamageReportController;
use App\Http\Controllers\Client\DisputeController;

Route::middleware(['auth', 'verified', 'active', 'client'])
    ->prefix('client')
    ->as('client.')
    ->group(function () {
        // Redirect '/client' to '/client/reservations' with a named route we can reference
        Route::redirect('/', '/client/reservations')->name('home');
        Route::get('/reservations', [ReservationsController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{id}', [ReservationsController::class, 'show'])->name('reservations.show');
        Route::get('/reservations/{id}/print', [ReservationsController::class, 'print'])->name('reservations.print');

        // Support
        Route::get('/support', [SupportController::class, 'index'])->name('support.index');
        Route::get('/support/create', [SupportController::class, 'create'])->name('support.create');
        Route::post('/support', [SupportController::class, 'store'])->name('support.store');
        Route::get('/support/{id}', [SupportController::class, 'show'])->name('support.show');
        Route::post('/support/{id}/reply', [SupportController::class, 'reply'])->name('support.reply');

        // Damage Reports
        Route::get('/damage-reports', [DamageReportController::class, 'index'])->name('damageReports.index');
        Route::get('/damage-reports/{damageReport}', [DamageReportController::class, 'show'])->name('damageReports.show');

        // Disputes
        Route::get('/damage-reports/{damageReport}/dispute/create', [DisputeController::class, 'create'])->name('disputes.create');
        Route::post('/damage-reports/{damageReport}/dispute', [DisputeController::class, 'store'])->name('disputes.store');
        Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');

    });
