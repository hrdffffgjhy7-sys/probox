<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:seed-if-empty', function () {
    if (DB::table('users')->count() === 0) {
        $this->call('db:seed', ['--force' => true]);
    }
})->purpose('Seed the database when no users exist');
