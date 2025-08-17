<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('fees:generate')
    ->monthlyOn(1, '00:01');


// * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1