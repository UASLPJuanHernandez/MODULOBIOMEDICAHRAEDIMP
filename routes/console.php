<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programación de respaldos automáticos
Schedule::command('backup:run --only-db')
    ->daily()
    ->at('02:00')
    ->name('backup-database-daily')
    ->onSuccess(function () {
        info('Respaldo de base de datos completado exitosamente');
    })
    ->onFailure(function () {
        error('Error al realizar respaldo de base de datos');
    });

Schedule::command('backup:run')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->name('backup-complete-weekly')
    ->onSuccess(function () {
        info('Respaldo completo semanal completado exitosamente');
    })
    ->onFailure(function () {
        error('Error al realizar respaldo completo semanal');
    });

Schedule::command('backup:clean')
    ->daily()
    ->at('04:00')
    ->name('backup-cleanup-daily')
    ->onSuccess(function () {
        info('Limpieza de respaldos completada exitosamente');
    });

Schedule::command('backup:monitor')
    ->daily()
    ->at('05:00')
    ->name('backup-monitor-daily')
    ->onSuccess(function () {
        info('Monitoreo de respaldos completado');
    });

