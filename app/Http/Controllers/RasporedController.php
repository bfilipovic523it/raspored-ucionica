<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ucionica;

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
}
