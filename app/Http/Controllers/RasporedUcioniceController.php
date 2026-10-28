<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Termin;
use App\Models\Ucionica;
use App\Http\Resources\TerminResource;

class RasporedUcioniceController extends Controller
{
    public function index()
    {
        $ucionice = Ucionica::orderBy('naziv')->get();

        return view('raspored.ucionica', ['ucionice' => $ucionice]);
    }

    public function termini(Request $request)
    {
        $podaci = $request->validate([
            'datum' => ['required', 'date_format:Y-m-d'],
            'ucionica_id' => ['required', 'exists:ucionicas,id'],
        ]);

        $ponedeljak = Carbon::parse($podaci['datum'])->startOfWeek(Carbon::MONDAY);
        $nedelja = $ponedeljak->copy()->addDays(6);

        $termini = Termin::where('ucionica_id', $podaci['ucionica_id'])
            ->whereBetween('datum', [$ponedeljak->toDateString(), $nedelja->toDateString()])
            ->vidljiviZa($request->user())
            ->orderBy('datum')
            ->orderBy('vreme_pocetka')
            ->get();

        return TerminResource::collection($termini);
    }
}
