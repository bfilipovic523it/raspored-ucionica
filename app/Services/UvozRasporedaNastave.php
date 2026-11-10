<?php

namespace App\Services;

use App\Models\Raspored;
use App\Models\Termin;
use App\Models\Ucionica;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UvozRasporedaNastave
{
    private const DANI = ['PON', 'UTO', 'SRE', 'ČET', 'PET', 'SUB', 'NED'];

    private const KOLONE = ['Predmet', 'Profesor', 'Grupe', 'Učionica', 'Termin'];

    private array $greske = [];

    public function pripremi(string $putanjaRasporeda, string $putanjaDana): array
    {
        $this->greske = [];

        $datumiPoDanu = $this->procitajDane($putanjaDana);
        $termini = $this->procitajRaspored($putanjaRasporeda, $datumiPoDanu);

        return [
            'termini' => $this->greske ? [] : $termini,
            'greske' => $this->greske,
        ];
    }

    public function upisi(Raspored $raspored, array $termini, int $adminId): void
    {
        $sada = now();

        foreach (array_chunk($termini, 500) as $grupa) {
            Termin::insert(array_map(fn ($termin) => $termin + [
                'raspored_id' => $raspored->id,
                'created_by' => $adminId,
                'status' => 'potvrdjen',
                'created_at' => $sada,
                'updated_at' => $sada,
            ], $grupa));
        }
    }

    public function preklapanja(Raspored $raspored): array
    {
        return DB::table('termins as novi')
            ->join('termins as postojeci', function ($spoj) {
                $spoj->on('postojeci.ucionica_id', '=', 'novi.ucionica_id')
                    ->on('postojeci.datum', '=', 'novi.datum')
                    ->on('postojeci.vreme_pocetka', '<', 'novi.vreme_kraja')
                    ->on('postojeci.vreme_kraja', '>', 'novi.vreme_pocetka');
            })
            ->join('ucionicas', 'ucionicas.id', '=', 'novi.ucionica_id')
            ->where('novi.raspored_id', $raspored->id)
            ->where(function ($q) use ($raspored) {
                $q->whereNull('postojeci.raspored_id')
                    ->orWhere('postojeci.raspored_id', '!=', $raspored->id);
            })
            ->whereIn('postojeci.status', ['ceka', 'potvrdjen'])
            ->orderBy('novi.datum')
            ->orderBy('novi.vreme_pocetka')
            ->get([
                'novi.naziv as novi_naziv', 'novi.datum', 'novi.vreme_pocetka', 'novi.vreme_kraja',
                'ucionicas.naziv as ucionica', 'postojeci.naziv as postojeci_naziv',
                'postojeci.vreme_pocetka as postojeci_pocetak', 'postojeci.vreme_kraja as postojeci_kraj',
            ])
            ->map(fn ($p) => date('d.m.Y.', strtotime($p->datum)) . ' ' . $p->ucionica . ' '
                . substr($p->vreme_pocetka, 0, 5) . '–' . substr($p->vreme_kraja, 0, 5) . ': „' . $p->novi_naziv
                . '“ se preklapa sa „' . $p->postojeci_naziv . '“ ('
                . substr($p->postojeci_pocetak, 0, 5) . '–' . substr($p->postojeci_kraj, 0, 5) . ')')
            ->all();
    }

    private function procitajDane(string $putanja): array
    {
        $redovi = $this->procitajCsv($putanja, ['datum', 'dan'], 'Fajl sa danima');
        $datumiPoDanu = [];
        $vidjeniDatumi = [];

        foreach ($redovi as $brojReda => $red) {
            $datum = trim($red['datum']);
            $dan = mb_strtoupper(trim($red['dan']));

            if (! $this->ispravanDatum($datum)) {
                $this->greske[] = "Fajl sa danima, red $brojReda: neispravan datum „{$datum}“ (očekuje se npr. 2026-09-28).";
                continue;
            }

            if (! in_array($dan, self::DANI, true)) {
                $this->greske[] = "Fajl sa danima, red $brojReda: nepoznat dan „{$dan}“ (dozvoljeno: " . implode(', ', self::DANI) . ').';
                continue;
            }

            if (isset($vidjeniDatumi[$datum])) {
                $this->greske[] = "Fajl sa danima, red $brojReda: datum $datum se ponavlja (prvi put u redu {$vidjeniDatumi[$datum]}).";
                continue;
            }

            $vidjeniDatumi[$datum] = $brojReda;
            $datumiPoDanu[$dan][] = $datum;
        }

        return $datumiPoDanu;
    }

    private function procitajRaspored(string $putanja, array $datumiPoDanu): array
    {
        $redovi = $this->procitajCsv($putanja, self::KOLONE, 'Fajl sa rasporedom');

        $ucionice = Ucionica::pluck('id', 'naziv')->all();

        $profesori = [];
        foreach (User::all(['id', 'name']) as $korisnik) {
            $profesori[$this->normalizujIme($korisnik->name)] = $korisnik->id;
        }

        $termini = [];

        foreach ($redovi as $brojReda => $red) {
            $nazivUcionice = trim($red['Učionica']);
            $imeProfesora = trim($red['Profesor']);
            $termin = trim($red['Termin']);
            $ispravanRed = true;

            if (! isset($ucionice[$nazivUcionice])) {
                $this->greske[] = "Red $brojReda: nepoznata učionica „{$nazivUcionice}“.";
                $ispravanRed = false;
            }

            $profesorId = $profesori[$this->normalizujIme($imeProfesora)] ?? null;
            if ($profesorId === null) {
                $this->greske[] = "Red $brojReda: nepoznat profesor „{$imeProfesora}“.";
                $ispravanRed = false;
            }

            $vreme = $this->procitajTermin($termin);
            if ($vreme === null) {
                $this->greske[] = "Red $brojReda: neispravan termin „{$termin}“ (očekuje se npr. „PON 09:15–10“).";
                $ispravanRed = false;
            } elseif (empty($datumiPoDanu[$vreme['dan']])) {
                $this->greske[] = "Red $brojReda: za dan {$vreme['dan']} nema nijednog datuma u fajlu sa danima.";
                $ispravanRed = false;
            }

            if (! $ispravanRed) {
                continue;
            }

            foreach ($datumiPoDanu[$vreme['dan']] as $datum) {
                $termini[] = [
                    'ucionica_id' => $ucionice[$nazivUcionice],
                    'user_id' => $profesorId,
                    'naziv' => trim($red['Predmet']),
                    'opis' => trim($red['Grupe']) ?: null,
                    'datum' => $datum,
                    'vreme_pocetka' => $vreme['pocetak'],
                    'vreme_kraja' => $vreme['kraj'],
                ];
            }
        }

        return $termini;
    }

    private function procitajCsv(string $putanja, array $obavezneKolone, string $opisFajla): array
    {
        $fajl = fopen($putanja, 'r');
        if ($fajl === false) {
            $this->greske[] = "$opisFajla ne može da se otvori.";
            return [];
        }

        if (fread($fajl, 3) !== "\xEF\xBB\xBF") {
            rewind($fajl);
        }

        $zaglavlje = fgetcsv($fajl);
        if ($zaglavlje === false) {
            fclose($fajl);
            $this->greske[] = "$opisFajla je prazan.";
            return [];
        }

        $zaglavlje = array_map('trim', $zaglavlje);

        $nedostaju = array_diff($obavezneKolone, $zaglavlje);
        if ($nedostaju) {
            fclose($fajl);
            $this->greske[] = "$opisFajla nema kolone: " . implode(', ', $nedostaju) . '.';
            return [];
        }

        $redovi = [];
        $brojReda = 1;

        while (($vrednosti = fgetcsv($fajl)) !== false) {
            $brojReda++;

            if (count(array_filter($vrednosti, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $red = [];
            foreach ($zaglavlje as $i => $kolona) {
                $red[$kolona] = $vrednosti[$i] ?? '';
            }
            $redovi[$brojReda] = $red;
        }

        fclose($fajl);

        return $redovi;
    }

    private function procitajTermin(string $termin): ?array
    {
        $uzorak = '/^(' . implode('|', self::DANI) . ')\s+(\d{1,2}):(\d{2})\s*[–—-]\s*(\d{1,2})(?::(\d{2}))?$/u';

        if (! preg_match($uzorak, mb_strtoupper($termin), $delovi)) {
            return null;
        }

        $pocetak = sprintf('%02d:%02d', $delovi[2], $delovi[3]);
        $kraj = sprintf('%02d:%02d', $delovi[4], $delovi[5] ?? 0);

        if ($pocetak >= $kraj) {
            return null;
        }

        return ['dan' => $delovi[1], 'pocetak' => $pocetak, 'kraj' => $kraj];
    }

    private function normalizujIme(string $ime): string
    {
        $ime = mb_strtolower(trim($ime));
        $ime = strtr($ime, ['đ' => 'dj', 'č' => 'c', 'ć' => 'c', 'š' => 's', 'ž' => 'z']);

        return preg_replace('/\s+/', ' ', $ime);
    }

    private function ispravanDatum(string $datum): bool
    {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $datum, $delovi)) {
            return false;
        }

        return checkdate((int) $delovi[2], (int) $delovi[3], (int) $delovi[1]);
    }
}
