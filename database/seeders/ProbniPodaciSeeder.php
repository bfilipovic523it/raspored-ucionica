<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Termin;
use App\Models\Ucionica;
use App\Models\User;


class ProbniPodaciSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@raf.rs'],
            ['name' => 'Admin Probni', 'uloga' => 'admin']
        );

        $nastavnik = User::firstOrCreate(
            ['email' => 'nastavnik@raf.rs'],
            ['name' => 'Nastavnik Probni', 'uloga' => 'nastavnik']
        );

        $raf1 = Ucionica::where('naziv', 'Raf 1 (u)')->firstOrFail();
        $raf2 = Ucionica::where('naziv', 'Raf 2 (u)')->firstOrFail();

        Termin::firstOrCreate(
            ['ucionica_id' => $raf1->id, 'datum' => '2026-10-12', 'vreme_pocetka' => '09:15'],
            ['user_id' => $nastavnik->id, 'created_by' => $nastavnik->id,
            'naziv' => 'PWA - Vežbe 1', 'vreme_kraja' => '11:00', 'status' => 'potvrdjen']
        );

        Termin::firstOrCreate(
            ['ucionica_id' => $raf2->id, 'datum' => '2026-10-12', 'vreme_pocetka' => '11:15'],
            ['user_id' => $nastavnik->id, 'created_by' => $nastavnik->id,
            'naziv' => 'Engleski 1 - Predavanja', 'vreme_kraja' => '13:00', 'status' => 'ceka']
        );

        Termin::firstOrCreate(
            ['ucionica_id' => $raf1->id, 'datum' => '2026-10-13', 'vreme_pocetka' => '14:15'],
            ['user_id' => $nastavnik->id, 'created_by' => $nastavnik->id,
            'naziv' => 'Nadoknada', 'opis' => 'Nadoknada termina vežbi od 14.10. za grupe 2s1, 2s2',
            'vreme_kraja' => '16:00', 'status' => 'odbijen']
        );
    }
}
