<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ucionica;

class UcionicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ucionice = [
            'Kolarac 1', 'Kolarac 2', 'Kolarac 3',
            'Muzicki studio', 'Nordeus',
            'Raf 1 (u)', 'Raf 2 (u)', 'Raf 3 (u)', 'Raf 4 (u)', 'Raf 5 (u)',
            'Raf 6 (a)', 'Raf 7 (u)', 'Raf 8 (a)', 'Raf 9 (a)', 'Raf 10 (a)',
            'Raf 11 (a)', 'Raf 12 (MD)', 'Raf 13 (MD)', 'Raf 14 (MD)', 'Raf 15 (MD)',
            'Raf 16 (MD)', 'Raf 20 (a)', 'Raf 21 (a)',
            'Rg 1 (u)', 'Rg 2 (u)', 'Rg 4 (u)', 'Rg 6 (u)', 'Rg7 (u)',
            'RAF Online (online)',
        ];

        foreach ($ucionice as $naziv) {
            Ucionica::firstOrCreate(['naziv' => $naziv]);
        }
    }
}
