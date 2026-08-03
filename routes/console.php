<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SyncAejReferentielsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule AEJ referentiels synchronization daily at midnight
Schedule::job(new SyncAejReferentielsJob('all'))->dailyAt('00:00')
    ->description('Sync all AEJ referentiels from external API');
