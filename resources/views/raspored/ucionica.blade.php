@extends('layouts.app')

@section('title', 'Raspored učionice')

@push('styles')
    @include('raspored._biblioteke')
@endpush

@section('content')
    <h2>Raspored učionice</h2>

    <div class="mb-3" style="max-width: 400px;">
        <label class="form-label" for="izbor-ucionice">Učionica</label>
        <select id="izbor-ucionice" class="form-select">
            <option value="">— izaberi učionicu —</option>
            @foreach ($ucionice as $ucionica)
                <option value="{{ $ucionica->id }}">{{ $ucionica->naziv }}</option>
            @endforeach
        </select>
    </div>

    <h4 id="naslov-nedelje"></h4>

    <div class="raspored">
        <div id="izbor-datuma"></div>

        <div class="raspored-desno">
            <div id="poruka" class="alert alert-info">Izaberite učionicu.</div>
            <div class="timetable"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const izborUcionice = document.getElementById('izbor-ucionice');
        const poruka = document.getElementById('poruka');
        const kontejner = document.querySelector('.timetable');

        function daniNedelje(datum) {
            const naziviDana = ['Pon', 'Uto', 'Sre', 'Čet', 'Pet', 'Sub', 'Ned'];
            const [godina, mesec, dan] = datum.split('-').map(Number);
            const izabrani = new Date(godina, mesec - 1, dan);
            const odPonedeljka = (izabrani.getDay() + 6) % 7;

            const dani = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(godina, mesec - 1, dan - odPonedeljka + i);
                dani.push({
                    datum: uDatum(d),
                    oznaka: naziviDana[i] + ' ' + d.getDate() + '.' + (d.getMonth() + 1) + '.',
                });
            }
            return dani;
        }

        function prikaziNedelju(dani) {
            const prvi = dani[0].datum.split('-');
            const poslednji = dani[6].datum.split('-');
            document.getElementById('naslov-nedelje').textContent =
                'Nedelja od ' + prvi[2] + '.' + prvi[1] + '.' + prvi[0] + '. do '
                + poslednji[2] + '.' + poslednji[1] + '.' + poslednji[0] + '.';
        }

        async function ucitajTermineUcionice() {
            const dani = daniNedelje(izabraniDatum);
            prikaziNedelju(dani);

            const ucionicaId = izborUcionice.value;

            if (!ucionicaId) {
                kontejner.innerHTML = '';
                poruka.classList.remove('d-none');
                return;
            }

            const odgovor = await fetch('{{ route('raspored.ucionica.termini') }}?datum=' + izabraniDatum
                + '&ucionica_id=' + ucionicaId);
            if (!odgovor.ok) {
                console.error('Greška pri učitavanju termina:', odgovor.status);
                return;
            }
            const termini = (await odgovor.json()).data;

            poruka.classList.add('d-none');

            const oznakaZaDatum = {};
            dani.forEach(function (dan) {
                oznakaZaDatum[dan.datum] = dan.oznaka;
            });

            nacrtajRaspored('.timetable', dani.map(function (dan) {
                return dan.oznaka;
            }), termini, function (termin) {
                return oznakaZaDatum[termin.datum];
            }, null);
        }

        let izabraniDatum = napraviIzborDatuma('izbor-datuma', function (datum) {
            izabraniDatum = datum;
            ucitajTermineUcionice();
        });

        ucitajTermineUcionice();

        izborUcionice.addEventListener('change', ucitajTermineUcionice);

        window.addEventListener('resize', ucitajTermineUcionice);
    </script>
@endpush
