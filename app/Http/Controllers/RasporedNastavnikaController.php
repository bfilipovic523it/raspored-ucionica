<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            ->vidljiviZa($request->user())
            ->orderBy('vreme_pocetka')
            ->get();

        return TerminResource::collection($termini);
    }

    public function nedelja()
    {
        $nastavnici = User::where('uloga', 'nastavnik')->orderBy('name')->get();
        $ucionice = Ucionica::orderBy('naziv')->get();

        return view('raspored.nastavnik-nedelja', [
            'nastavnici' => $nastavnici,
            'ucionice' => $ucionice,
        ]);
    }

    public function terminiNedelje(Request $request)
    {
        $podaci = $request->validate([
            'datum' => ['required', 'date_format:Y-m-d'],
            'nastavnik_id' => ['required', 'exists:users,id'],
        ]);

        $ponedeljak = Carbon::parse($podaci['datum'])->startOfWeek(Carbon::MONDAY);
        $nedelja = $ponedeljak->copy()->addDays(6);

        $termini = Termin::where('user_id', $podaci['nastavnik_id'])
            ->whereBetween('datum', [$ponedeljak->toDateString(), $nedelja->toDateString()])
            ->vidljiviZa($request->user())
            ->orderBy('datum')
            ->orderBy('vreme_pocetka')
            ->get();

        return TerminResource::collection($termini);
    }
}
