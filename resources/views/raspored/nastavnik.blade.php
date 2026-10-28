@extends('layouts.app')

@section('title', 'Raspored nastavnika')

@push('styles')
    @include('raspored._biblioteke')
@endpush

@section('content')
    <h2>Raspored nastavnika</h2>

    <div class="mb-3" style="max-width: 400px;">
        <label class="form-label" for="izbor-nastavnika">Nastavnik</label>
        <select id="izbor-nastavnika" class="form-select">
            <option value="">— izaberi nastavnika —</option>
            @foreach ($nastavnici as $nastavnik)
                <option value="{{ $nastavnik->id }}">{{ $nastavnik->name }}</option>
            @endforeach
        </select>
    </div>

    <h4 id="naslov-datuma"></h4>

    <div class="raspored">
        <div id="izbor-datuma"></div>

        <div class="raspored-desno">
            <div id="poruka" class="alert alert-info">Izaberite nastavnika.</div>
            <div class="timetable"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const naziviUcionica = @json($ucionice->pluck('naziv', 'id'));

        const redosledUcionica = @json($ucionice->pluck('naziv'));

        const izborNastavnika = document.getElementById('izbor-nastavnika');
        const poruka = document.getElementById('poruka');
        const kontejner = document.querySelector('.timetable');

        function prikaziPoruku(tekst) {
            kontejner.innerHTML = '';
            poruka.textContent = tekst;
            poruka.classList.remove('d-none');
        }

        async function ucitajTermineNastavnika() {
            const nastavnikId = izborNastavnika.value;

            if (!nastavnikId) {
                prikaziPoruku('Izaberite nastavnika.');
                return;
            }

            const odgovor = await fetch('{{ route('raspored.nastavnik.termini') }}?datum=' + izabraniDatum
                + '&nastavnik_id=' + nastavnikId);
            if (!odgovor.ok) {
                console.error('Greška pri učitavanju termina:', odgovor.status);
                return;
            }
            const termini = (await odgovor.json()).data;

            if (termini.length === 0) {
                prikaziPoruku('Nastavnik nema termine za izabrani dan.');
                return;
            }

            poruka.classList.add('d-none');

            const ucioniceSaTerminima = redosledUcionica.filter(function (naziv) {
                return termini.some(function (termin) {
                    return naziviUcionica[termin.ucionica_id] === naziv;
                });
            });

            nacrtajRaspored('.timetable', ucioniceSaTerminima, termini, naziviUcionica, null);
        }

        let izabraniDatum = napraviIzborDatuma('izbor-datuma', function (datum) {
            izabraniDatum = datum;
            prikaziDatum(datum);
            ucitajTermineNastavnika();
        });

        prikaziDatum(izabraniDatum);
        ucitajTermineNastavnika();

        izborNastavnika.addEventListener('change', ucitajTermineNastavnika);

        window.addEventListener('resize', ucitajTermineNastavnika);
    </script>
@endpush
