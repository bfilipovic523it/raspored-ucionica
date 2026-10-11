<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Raspored učionica</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@calendarjs/ce/dist/style.min.css">
    <script src="https://cdn.jsdelivr.net/npm/lemonadejs/dist/lemonade.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@calendarjs/ce/dist/index.min.js"></script>
    <style>
        .omotac { overflow-x: auto; }

        .mreza { border-collapse: collapse; font-size: 12px; }
        .mreza th, .mreza td { border: 1px solid #ccc; padding: 2px 6px; }
        .mreza th { background: #f0f0f0; white-space: nowrap; }

        .mreza td.polje { min-width: 70px; height: 14px; }

        .mreza tr.pun-sat td, .mreza tr.pun-sat th { border-top: 2px solid #888; }

        .raspored { display: flex; gap: 16px; align-items: flex-start; }

        .raspored .omotac { flex: 1; min-width: 0; }

        .mreza td.potvrdjen { background: #cfe2ff; }               
        .mreza td.ceka      { background: #fff3b0; }      
        .mreza td.odbijen   { background: #e0e0e0; color: #666; }
    </style>
</head>
<body>
    <h1>Raspored učionica</h1>
    <p>
        Prijavljeni ste kao <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->uloga }}).
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">Odjava</button>
        </form>
    </p>
    <h2 id="naslov-datuma"></h2>

    <div class="raspored">
        <div id="izbor-datuma"></div>

        <div class="omotac">
            <table class="mreza">
                <thead>
                    <tr>
                        <th>Vreme</th>
                        @foreach ($ucionice as $ucionica)
                            <th>{{ $ucionica->naziv }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vremena as $vreme)
                        <tr class="{{ str_ends_with($vreme, ':00') ? 'pun-sat' : '' }}">
                            <th>{{ $vreme }}</th>
                            @foreach ($ucionice as $ucionica)
                                <td class="polje" data-ucionica="{{ $ucionica->id }}" data-vreme="{{ $vreme }}"></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="popup-rezervacija" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rezervacija termina</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
                </div>

                <div class="modal-body">
                    <div id="rez-greska" class="alert alert-danger d-none"></div>

                    <form id="forma-rezervacija">
                        <input type="hidden" id="rez-ucionica-id">

                        <div class="mb-2">
                            <label class="form-label">Učionica</label>
                            <input type="text" id="rez-ucionica" class="form-control" readonly>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Datum</label>
                            <input type="date" id="rez-datum" class="form-control" required>
                        </div>

                        <div class="row mb-2">
                            <div class="col">
                                <label class="form-label">Početak</label>
                                <select id="rez-pocetak" class="form-select">
                                    @foreach ($vremena as $vreme)
                                        @if ($vreme !== '21:00')
                                            <option>{{ $vreme }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Kraj</label>
                                <select id="rez-kraj" class="form-select">
                                    @foreach ($vremena as $vreme)
                                        @if ($vreme !== '09:00')
                                            <option>{{ $vreme }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Trajanje</label>
                                <input type="text" id="rez-trajanje" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Naziv</label>
                            <input type="text" id="rez-naziv" class="form-control" placeholder="npr. PWA" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Opis (nije obavezan)</label>
                            <textarea id="rez-opis" class="form-control" rows="2"
                                      placeholder="npr. Nadoknada termina vežbi od 14.10. za grupe 2s1, 2s2"></textarea>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Otkaži</button>
                    <button type="submit" form="forma-rezervacija" class="btn btn-primary">Pošalji zahtev</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const { Calendar } = calendarjs;

        const naziviUcionica = @json($ucionice->pluck('naziv', 'id'));

        calendarjs.setDictionary({
            'January': 'Januar', 'February': 'Februar', 'March': 'Mart',
            'April': 'April', 'May': 'Maj', 'June': 'Jun',
            'July': 'Jul', 'August': 'Avgust', 'September': 'Septembar',
            'October': 'Oktobar', 'November': 'Novembar', 'December': 'Decembar',
            'Sunday': 'Nedelja', 'Monday': 'Ponedeljak', 'Tuesday': 'Utorak',
            'Wednesday': 'Sreda', 'Thursday': 'Četvrtak', 'Friday': 'Petak',
            'Saturday': 'Subota', 'Done': 'Gotovo', 'Reset': 'Poništi'
        });

        function prikaziDatum(datum) {
            const dani = ['Nedelja', 'Ponedeljak', 'Utorak', 'Sreda', 'Četvrtak', 'Petak', 'Subota'];
            const [godina, mesec, dan] = datum.split('-');                  
            const danUNedelji = new Date(godina, mesec - 1, dan).getDay();  
            document.getElementById('naslov-datuma').textContent =
                dani[danUNedelji] + ', ' + dan + '.' + mesec + '.' + godina + '.';
        }

        function uMinute(vreme) {
            const [sat, minut] = vreme.split(':');
            return Number(sat) * 60 + Number(minut);
        }

        function uVreme(minuti) {
            const sat = String(Math.floor(minuti / 60)).padStart(2, '0');
            const minut = String(minuti % 60).padStart(2, '0');
            return sat + ':' + minut;
        }

        function osveziTrajanje() {
            const pocetak = uMinute(document.getElementById('rez-pocetak').value);
            const kraj = uMinute(document.getElementById('rez-kraj').value);
            const razlika = kraj - pocetak;

            document.getElementById('rez-trajanje').value = razlika > 0
                ? Math.floor(razlika / 60) + ' h ' + (razlika % 60) + ' min'
                : 'kraj mora biti posle početka';
        }

        async function ucitajTermine(datum) {
            document.querySelectorAll('.mreza td.polje').forEach(function (polje) {
                polje.className = 'polje';
                polje.textContent = '';
                polje.title = '';
            });

            const odgovor = await fetch('{{ route('raspored.termini') }}?datum=' + datum);
            if (!odgovor.ok) {
                console.error('Greška pri učitavanju termina:', odgovor.status);
                return;
            }
            const termini = await odgovor.json();

            termini.forEach(function (termin) {
                const pocetak = uMinute(termin.vreme_pocetka);
                const kraj = uMinute(termin.vreme_kraja);

                for (let m = pocetak; m < kraj; m += 15) {
                    const polje = document.querySelector(
                        '.mreza td[data-ucionica="' + termin.ucionica_id + '"][data-vreme="' + uVreme(m) + '"]'
                    );
                    if (!polje) continue; 

                    polje.classList.add(termin.status);
                    polje.title = termin.naziv + ' (' + termin.vreme_pocetka + '–' + termin.vreme_kraja + ')';
                    if (m === pocetak) {
                        polje.textContent = termin.naziv;
                    }
                }
            });
        }

        const sada = new Date();
        const danas = sada.getFullYear() + '-'
            + String(sada.getMonth() + 1).padStart(2, '0') + '-'
            + String(sada.getDate()).padStart(2, '0');

        let izabraniDatum = danas;

        Calendar(document.getElementById('izbor-datuma'), {
            type: 'inline',
            startingDay: 1,
            value: danas,
            onchange: function (self, vrednost) {
                izabraniDatum = vrednost.slice(0, 10);
                prikaziDatum(vrednost.slice(0, 10));
                ucitajTermine(vrednost.slice(0, 10));
            },
        });

        prikaziDatum(danas);
        ucitajTermine(danas);

        document.querySelector('.mreza').addEventListener('click', function (dogadjaj) {
            const polje = dogadjaj.target.closest('td.polje');  
            if (!polje) return;                                   

            const vreme = polje.dataset.vreme;
            if (vreme === '21:00') return;                        

            if (polje.classList.contains('potvrdjen') || polje.classList.contains('ceka')) return;

            const ucionicaId = polje.dataset.ucionica;
            document.getElementById('rez-ucionica-id').value = ucionicaId;
            document.getElementById('rez-ucionica').value = naziviUcionica[ucionicaId];
            document.getElementById('rez-datum').value = izabraniDatum;
            document.getElementById('rez-pocetak').value = vreme;
            document.getElementById('rez-kraj').value = uVreme(Math.min(uMinute(vreme) + 45, 21 * 60));
            document.getElementById('rez-naziv').value = '';
            document.getElementById('rez-opis').value = '';
            osveziTrajanje();
            document.getElementById('rez-greska').classList.add('d-none');   

            bootstrap.Modal.getOrCreateInstance(document.getElementById('popup-rezervacija')).show();
        });

        document.getElementById('rez-pocetak').addEventListener('change', osveziTrajanje);
        document.getElementById('rez-kraj').addEventListener('change', osveziTrajanje);

        document.getElementById('forma-rezervacija').addEventListener('submit', async function (dogadjaj) {
            dogadjaj.preventDefault();  

            const greska = document.getElementById('rez-greska');
            greska.classList.add('d-none');

            const odgovor = await fetch('{{ route('raspored.sacuvaj') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',   
                    'Accept': 'application/json',        
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    ucionica_id: document.getElementById('rez-ucionica-id').value,
                    datum: document.getElementById('rez-datum').value,
                    vreme_pocetka: document.getElementById('rez-pocetak').value,
                    vreme_kraja: document.getElementById('rez-kraj').value,
                    naziv: document.getElementById('rez-naziv').value,
                    opis: document.getElementById('rez-opis').value,
                }),
            });

            if (odgovor.ok) {
                bootstrap.Modal.getInstance(document.getElementById('popup-rezervacija')).hide();
                ucitajTermine(izabraniDatum);
            } else {
                const podaci = await odgovor.json();
                greska.textContent = podaci.message;
                greska.classList.remove('d-none');
            }
        });
    </script>

</body>
</html>
