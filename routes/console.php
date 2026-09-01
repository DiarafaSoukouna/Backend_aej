<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SyncAejReferentielsJob;
use App\Jobs\GeneratePeriodicBulletinJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule AEJ referentiels synchronization daily at midnight
Schedule::job(new SyncAejReferentielsJob('all'))->dailyAt('00:00')
    ->description('Sync all AEJ referentiels from external API');

// ── Module IA — Bulletins périodiques ──────────────────────────────────────

// Bulletin MENSUEL : le 1er de chaque mois à 06h00 (rapport du mois précédent)
Schedule::call(function () {
    dispatch(GeneratePeriodicBulletinJob::forLastMonth());
})->monthlyOn(1, '06:00')
  ->description('Génération automatique du bulletin mensuel IA');

// Bulletin TRIMESTRIEL : le 1er janv/avril/juil/oct à 07h00
Schedule::call(function () {
    dispatch(GeneratePeriodicBulletinJob::forLastQuarter());
})->quarterly()
  ->at('07:00')
  ->description('Génération automatique du bulletin trimestriel IA');
