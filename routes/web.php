<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RasporedController;
use App\Http\Controllers\PrijavaController;



Route::get('/', function () {
    return view('welcome');
});

Route::view('/proba-kalendar', 'proba-kalendar');

Route::middleware('auth')->group(function () {
    Route::get('/raspored', [RasporedController::class, 'kalendar'])->name('raspored.kalendar');
    Route::get('/raspored/termini', [RasporedController::class, 'termini'])->name('raspored.termini');
    Route::post('/raspored/termini', [RasporedController::class, 'sacuvaj'])->name('raspored.sacuvaj');
    Route::patch('/raspored/termini/{termin}/status', [RasporedController::class, 'promeniStatus'])->name('raspored.status');
});

Route::get('/login', [PrijavaController::class, 'forma'])->name('login');
Route::post('/login', [PrijavaController::class, 'prijavi']);
Route::post('/logout', [PrijavaController::class, 'odjava'])->name('logout');

