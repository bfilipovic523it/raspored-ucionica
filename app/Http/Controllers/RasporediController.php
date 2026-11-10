<?php

namespace App\Http\Controllers;

use App\Models\Raspored;
use App\Services\UvozRasporedaNastave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

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

    public function store(Request $request, UvozRasporedaNastave $uvoz)
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

        $termini = [];

        if ($podaci['tip'] === 'nastava') {
            $priprema = $uvoz->pripremi(
                $request->file('fajl_raspored')->getRealPath(),
                $request->file('fajl_dani')->getRealPath(),
            );

            if ($priprema['greske']) {
                return back()->withInput()->with('greske_uvoza', $priprema['greske']);
            }

            $termini = $priprema['termini'];
        }

        $putanje = [
            'fajl_raspored' => $request->file('fajl_raspored')->store('rasporedi'),
            'fajl_dani' => $podaci['tip'] === 'nastava' ? $request->file('fajl_dani')->store('rasporedi') : null,
        ];

        try {
            $raspored = DB::transaction(function () use ($podaci, $putanje, $termini, $uvoz, $request) {
                $raspored = Raspored::create([
                    'naziv' => $podaci['naziv'],
                    'tip' => $podaci['tip'],
                    'user_id' => $request->user()->id,
                    ...$putanje,
                ]);

                $uvoz->upisi($raspored, $termini, $request->user()->id);

                return $raspored;
            });
        } catch (Throwable $e) {
            Storage::delete(array_filter($putanje));
            throw $e;
        }

        $poruka = $podaci['tip'] === 'nastava'
            ? 'Raspored „' . $raspored->naziv . '" je uvezen: ' . count($termini) . ' termina.'
            : 'Raspored „' . $raspored->naziv . '" je sačuvan. Uvoz rasporeda testiranja još nije podržan, pa termini nisu napravljeni.';

        return redirect()->route('rasporedi.index')
            ->with('poruka', $poruka)
            ->with('upozorenja', $uvoz->preklapanja($raspored));
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
