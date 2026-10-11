<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ucionica;
use App\Models\Termin;


class RasporedController extends Controller
{
    public function kalendar()
    {
        $ucionice = Ucionica::orderBy('naziv')->get();

        $vremena = [];
        for ($minut = 9 * 60; $minut <= 21 * 60; $minut += 15) {
            $sat = intdiv($minut, 60);         // celobrojno deljenje: 555 / 60 = 9
            $ostatak = $minut % 60;            // ostatak deljenja:   555 % 60 = 15
            $vremena[] = sprintf('%02d:%02d', $sat, $ostatak);   // "09:15"
        }

        return view('raspored.kalendar', [
            'ucionice' => $ucionice,
            'vremena' => $vremena,
        ]);
    }

    public function termini(Request $request)
    {
        // Provera ulaza: datum mora postojati i biti u formatu Y-m-d.
        // Ako nije, Laravel sam vraća grešku i dalje se ne ide.
        $podaci = $request->validate([
            'datum' => ['required', 'date_format:Y-m-d'],
        ]);

        // Termini za taj datum, poređani po vremenu početka.
        // Za sada vraćamo SVE termine. Pravilo "nastavnik vidi samo potvrđene
        // i svoje na čekanju" dodajemo kad bude prijave (dok ne znamo ko je ulogovan).
        $termini = Termin::where('datum', $podaci['datum'])
            ->orderBy('vreme_pocetka')
            ->get();

        // Šaljemo samo polja potrebna kalendaru. Vreme iz baze stiže kao
        // "09:15:00", pa uzimamo prvih 5 znakova: "09:15".
        $rezultat = $termini->map(function ($termin) {
            return [
                'id' => $termin->id,
                'ucionica_id' => $termin->ucionica_id,
                'naziv' => $termin->naziv,
                'opis' => $termin->opis,
                'vreme_pocetka' => substr($termin->vreme_pocetka, 0, 5),
                'vreme_kraja' => substr($termin->vreme_kraja, 0, 5),
                'status' => $termin->status,
            ];
        });

        return response()->json($rezultat);
    }
}
