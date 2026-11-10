<?php

namespace App\Http\Controllers;

use App\Models\Raspored;
use Illuminate\Http\Request;
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

    public function create()
    {
        return view('rasporedi.create');
    }

    public function store(Request $request)
    {
        $podaci = $request->validate([
            'naziv' => ['required', 'string', 'max:255'],
            'tip' => ['required', 'in:nastava,testiranje'],
            'fajl_raspored' => ['required', 'file', 'extensions:csv', 'max:2048'],
            'fajl_dani' => ['required_if:tip,nastava', 'nullable', 'file', 'extensions:csv', 'max:2048'],
        ], [
            'naziv.required' => 'Upišite naziv rasporeda.',
            'fajl_raspored.required' => 'Izaberite CSV fajl sa rasporedom.',
            'fajl_raspored.extensions' => 'Fajl sa rasporedom mora biti CSV.',
            'fajl_dani.required_if' => 'Za raspored nastave izaberite i fajl sa datumima po danima.',
            'fajl_dani.extensions' => 'Fajl sa datumima mora biti CSV.',
            '*.max' => 'Fajl je prevelik (najviše 2 MB).',
        ]);

        $raspored = Raspored::create([
            'naziv' => $podaci['naziv'],
            'tip' => $podaci['tip'],
            'user_id' => $request->user()->id,
            'fajl_raspored' => $request->file('fajl_raspored')->store('rasporedi'),
            'fajl_dani' => $podaci['tip'] === 'nastava' ? $request->file('fajl_dani')->store('rasporedi') : null,
        ]);

        return redirect()->route('rasporedi.index')
            ->with('poruka', 'Raspored „' . $raspored->naziv . '" je sačuvan. Termini iz njega se još ne prave (to je sledeći korak).');
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
