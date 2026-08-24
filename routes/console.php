<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
|--------------------------------------------------------------------------
| CONTAS FIXAS / RECORRÊNCIAS
|--------------------------------------------------------------------------
|
| Verifica diariamente as recorrências ativas.
| O próprio comando impede a criação duplicada
| da mesma competência.
|
*/

Schedule::command('recorrencias:gerar')
    ->dailyAt('00:05')
    ->withoutOverlapping();