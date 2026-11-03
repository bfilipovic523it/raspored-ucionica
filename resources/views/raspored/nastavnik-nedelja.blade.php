@extends('layouts.app')

@section('title', 'Nedeljni raspored nastavnika')

@push('styles')
    @include('raspored._biblioteke')
@endpush

@section('content')
    <h2>Nedeljni raspored nastavnika</h2>

    <div class="mb-3" style="max-width: 400px;">
        <label class="form-label" for="izbor-nastavnika">Nastavnik</label>
        <select id="izbor-nastavnika" class="form-select">
            <option value="">— izaberi nastavnika —</option>
            @foreach ($nastavnici as $nastavnik)
                <option value="{{ $nastavnik->id }}">{{ $nastavnik->name }}</option>
            @endforeach
        </select>
    </div>

    <h4 id="naslov-nedelje"></h4>

    <div class="raspored">
        <div id="izbor-datuma"></div>

        <div class="raspored-desno">
            <div id="poruka" class="alert alert-info">Izaberite nastavnika.</div>
            <div class="timetable"></div>
        </div>
    </div>

    @include('raspored._popup-detalji')
@endsection

@push('scripts')
    <script>
        const naziviUcionica = @json($ucionice->pluck('naziv', 'id'));

        const izborNastavnika = document.getElementById('izbor-nastavnika');
        const poruka = document.getElementById('poruka');
        const kontejner = document.querySelector('.timetable');

        async function ucitajTermineNastavnika() {
            const dani = daniNedelje(izabraniDatum);
            prikaziNedelju(dani);

            const nastavnikId = izborNastavnika.value;

            if (!nastavnikId) {
                kontejner.innerHTML = '';
                poruka.classList.remove('d-none');
                return;
            }

            const odgovor = await fetch('{{ route('raspored.nastavnik.nedelja.termini') }}?datum=' + izabraniDatum
                + '&nastavnik_id=' + nastavnikId);
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

            const originalPoId = {};
            const terminiSaUcionicom = termini.map(function (termin) {
                originalPoId[termin.id] = termin;
                return Object.assign({}, termin, {
                    naziv: termin.naziv + ' (' + naziviUcionica[termin.ucionica_id] + ')',
                });
            });

            nacrtajRaspored('.timetable', dani.map(function (dan) {
                return dan.oznaka;
            }), terminiSaUcionicom, function (termin) {
                return oznakaZaDatum[termin.datum];
            }, function (termin) {
                prikaziDetaljeTermina(originalPoId[termin.id], naziviUcionica);
            });
        }

        let izabraniDatum = napraviIzborDatuma('izbor-datuma', function (datum) {
            izabraniDatum = datum;
            ucitajTermineNastavnika();
        });

        ucitajTermineNastavnika();

        izborNastavnika.addEventListener('change', ucitajTermineNastavnika);

        window.addEventListener('resize', ucitajTermineNastavnika);
    </script>
@endpush
