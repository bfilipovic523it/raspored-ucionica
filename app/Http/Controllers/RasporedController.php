<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ucionica;

class RasporedController extends Controller
{
    public function kalendar()
    {
        $ucionice = Ucionica::orderBy('naziv')->get();

        return view('raspored.kalendar', ['ucionice' => $ucionice]);
    }
}
