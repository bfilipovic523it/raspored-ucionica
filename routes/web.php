<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RasporedController;
use App\Http\Controllers\PrijavaController;
use App\Http\Controllers\RasporedNastavnikaController;



Route::view('/proba-kalendar', 'proba-kalendar');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('raspored.kalendar');
    })->name('home');

    Route::get('/raspored', [RasporedController::class, 'kalendar'])->name('raspored.kalendar');
    Route::get('/raspored/termini', [RasporedController::class, 'termini'])->name('raspored.termini');
    Route::post('/raspored/termini', [RasporedController::class, 'sacuvaj'])->name('raspored.sacuvaj');
    Route::patch('/raspored/termini/{termin}/status', [RasporedController::class, 'promeniStatus'])->name('raspored.status');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/raspored-nastavnika', [RasporedNastavnikaController::class, 'index'])->name('raspored.nastavnik');
    Route::get('/raspored-nastavnika/termini', [RasporedNastavnikaController::class, 'termini'])->name('raspored.nastavnik.termini');
});

Route::get('/login', [PrijavaController::class, 'forma'])->name('login');
Route::post('/login', [PrijavaController::class, 'prijavi']);
Route::post('/logout', [PrijavaController::class, 'odjava'])->name('logout');

