<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PrijavaController extends Controller
{
    /**
     * Stranica za probnu prijavu
    */
    public function forma()
    {
        abort_unless(app()->isLocal(), 404);

        $korisnici = User::orderBy('uloga')->orderBy('name')->get();

        return view('auth.login', ['korisnici' => $korisnici]);
    }

    /**
     * Prijavljuje izabranog korisnika
    */
    public function prijavi(Request $request)
    {
        abort_unless(app()->isLocal(), 404);

        $podaci = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        Auth::loginUsingId($podaci['user_id']);

        $request->session()->regenerate();

        return redirect()->route('raspored.kalendar');
    }

    /**
     * Odjava
    */
    public function odjava(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

}
