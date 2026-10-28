<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ucionica;
use App\Models\Termin;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TerminResource;


class RasporedController extends Controller
{
    public function kalendar()
    {
        $ucionice = Ucionica::orderBy('naziv')->get();

        $vremena = [];
        for ($minut = 9 * 60; $minut <= 21 * 60; $minut += 15) {
            $sat = intdiv($minut, 60);         
            $ostatak = $minut % 60;           
            $vremena[] = sprintf('%02d:%02d', $sat, $ostatak);   
        }

        return view('raspored.kalendar', [
            'ucionice' => $ucionice,
            'vremena' => $vremena,
        ]);
    }

    public function termini(Request $request)
    {
        $podaci = $request->validate([
            'datum' => ['required', 'date_format:Y-m-d'],
        ]);

        $termini = Termin::where('datum', $podaci['datum'])
            ->vidljiviZa($request->user())
            ->orderBy('vreme_pocetka')
            ->get();

        return TerminResource::collection($termini);
    }

    public function sacuvaj(Request $request)
    {
        // 1) Provera podataka
        $podaci = $request->validate([
            'ucionica_id'   => ['required', 'exists:ucionicas,id'],
            'datum'         => ['required', 'date_format:Y-m-d'],
            'vreme_pocetka' => ['required', 'date_format:H:i', 'after_or_equal:09:00'],
            'vreme_kraja'   => ['required', 'date_format:H:i', 'after:vreme_pocetka', 'before_or_equal:21:00'],
            'naziv'         => ['required', 'string', 'max:255'],
            'opis'          => ['nullable', 'string'],
        ]);

        // 2) Provera preklapanja i upis, u jednoj transakciji.
        return DB::transaction(function () use ($podaci, $request) {
            Ucionica::lockForUpdate()->find($podaci['ucionica_id']);

            $preklapaSe = Termin::where('ucionica_id', $podaci['ucionica_id'])
                ->where('datum', $podaci['datum'])
                ->whereIn('status', ['ceka', 'potvrdjen'])      
                ->where('vreme_pocetka', '<', $podaci['vreme_kraja']) 
                ->where('vreme_kraja', '>', $podaci['vreme_pocetka'])  
                ->exists();

            if ($preklapaSe) {
                return response()->json([
                    'message' => 'U ovoj učionici već postoji termin u to vreme (potvrđen ili na čekanju).',
                ], 422);
            }

            $termin = Termin::create([
                ...$podaci,                             
                'user_id' => $request->user()->id,     
                'created_by' => $request->user()->id,
                'status' => 'ceka',                      
            ]);

            return new TerminResource($termin);
        });
    }

    /**
     * Admin menja status termina
    */
    public function promeniStatus(Request $request, Termin $termin)
    {
        abort_unless($request->user()->jeAdmin(), 403);

        $podaci = $request->validate([
            'status' => ['required', 'in:potvrdjen,odbijen,ceka'],
        ]);

        return DB::transaction(function () use ($podaci, $termin, $request) {
            if ($podaci['status'] !== 'odbijen') {
                Ucionica::lockForUpdate()->find($termin->ucionica_id);

                $preklapaSe = Termin::where('ucionica_id', $termin->ucionica_id)
                    ->where('datum', $termin->datum)
                    ->where('id', '!=', $termin->id)
                    ->whereIn('status', ['ceka', 'potvrdjen'])
                    ->where('vreme_pocetka', '<', $termin->vreme_kraja)
                    ->where('vreme_kraja', '>', $termin->vreme_pocetka)
                    ->exists();

                if ($preklapaSe) {
                    return response()->json([
                        'message' => 'Termin se preklapa sa drugim terminom u istoj učionici.',
                    ], 422);
                }
            }

            $termin->update([
                'status' => $podaci['status'],
                'updated_by' => $request->user()->id,
            ]);

            return new TerminResource($termin);
        });
    }

}
