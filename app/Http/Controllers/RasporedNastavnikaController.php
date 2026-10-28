<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Termin;
use App\Models\Ucionica;
use App\Http\Resources\TerminResource;

class RasporedNastavnikaController extends Controller
{
    public function index()
    {
        $nastavnici = User::where('uloga', 'nastavnik')->orderBy('name')->get();
        $ucionice = Ucionica::orderBy('naziv')->get();

        return view('raspored.nastavnik', [
            'nastavnici' => $nastavnici,
            'ucionice' => $ucionice,
        ]);
    }

    public function termini(Request $request)
    {
        $podaci = $request->validate([
            'datum' => ['required', 'date_format:Y-m-d'],
            'nastavnik_id' => ['required', 'exists:users,id'],
        ]);

        $termini = Termin::where('datum', $podaci['datum'])
            ->where('user_id', $podaci['nastavnik_id'])
            ->orderBy('vreme_pocetka')
            ->get();

        return TerminResource::collection($termini);
    }
}
