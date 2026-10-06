<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * --------------------------------------------------------------------------
 * Jadwal Otomatis Telegram Executive Owner Bot (Production Best Practice)
 * --------------------------------------------------------------------------
 */

// 1. Nightly Safety Net Audit (Setiap hari jam 01:00 AM WIB)
// Deteksi shift kasir yang lupa ditutup, auto-reconcile, dan kirim safety net report
Schedule::command('telegram:owner-nightly-audit')
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta')
    ->name('telegram-owner-nightly-audit')
    ->withoutOverlapping();

// 2. Executive Monthly Closing Report (Tanggal 1 setiap bulan jam 08:00 AM WIB)
// Kirim rekapitulasi laba rugi bulanan dan spreadsheet Excel 4-sheet resmi
Schedule::command('telegram:owner-monthly-report')
    ->monthlyOn(1, '08:00')
    ->timezone('Asia/Jakarta')
    ->name('telegram-owner-monthly-report')
    ->withoutOverlapping();

