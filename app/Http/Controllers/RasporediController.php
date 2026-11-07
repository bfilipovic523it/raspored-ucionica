<?php

namespace App\Http\Controllers;

use App\Models\Raspored;
use Illuminate\Support\Facades\Storage;

class RasporediController extends Controller
{
    public function index()
    {
        $rasporedi = Raspored::with('user')
            ->withCount('termini')
            ->latest()
            ->paginate(20);

        return view('rasporedi.index', ['rasporedi' => $rasporedi]);
    }

    public function destroy(Raspored $raspored)
    {
        $brojTermina = $raspored->termini()->count();

        Storage::delete(array_filter([$raspored->fajl_raspored, $raspored->fajl_dani]));

        $raspored->delete();

        return redirect()->route('rasporedi.index')
            ->with('poruka', 'Raspored „' . $raspored->naziv . '" je obrisan, zajedno sa ' . $brojTermina . ' termina.');
    }
}
