<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RasporedController;


Route::get('/', function () {
    return view('welcome');
});

Route::view('/proba-kalendar', 'proba-kalendar');

Route::get('/raspored', [RasporedController::class, 'kalendar'])->name('raspored.kalendar');

