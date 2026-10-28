@extends('layouts.app')

@section('title', 'Raspored učionica')

@push('styles')
    @include('raspored._biblioteke')
@endpush

@section('content')
    <h2 id="naslov-datuma"></h2>
    <button type="button" id="dugme-nova" class="btn btn-primary mb-3">Nova rezervacija</button>

    <div class="raspored">
        <div id="izbor-datuma"></div>

        <div class="timetable"></div>
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
                        <div class="mb-2">
                            <label class="form-label">Učionica</label>
                            <select id="rez-ucionica-id" class="form-select" required>
                                <option value="">— izaberi učionicu —</option>
                                @foreach ($ucionice as $ucionica)
                                    <option value="{{ $ucionica->id }}">{{ $ucionica->naziv }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Datum</label>
                            <input type="text" id="rez-datum" class="form-control" readonly>
                            <div class="form-text">Datum se menja u kalendaru levo.</div>
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

    {{-- Popup za promenu statusa (samo za admina). --}}
    <div class="modal fade" id="popup-status" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Termin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
                </div>
                <div class="modal-body">
                    <div id="status-greska" class="alert alert-danger d-none"></div>
                    <select id="status-izbor" class="form-select form-select-sm mb-2 d-none"></select>
                    <p class="mb-1"><strong id="status-naziv"></strong></p>
                    <p class="mb-1" id="status-vreme"></p>
                    <p class="mb-1 text-muted" id="status-opis"></p>
                    <p class="mb-0">Trenutni status: <strong id="status-trenutni"></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" data-status="potvrdjen">Potvrdi</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-status="odbijen">Odbij</button>
                    <button type="button" class="btn btn-warning btn-sm" data-status="ceka">Na čekanju</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const naziviUcionica = @json($ucionice->pluck('naziv', 'id'));

        const redosledUcionica = @json($ucionice->pluck('naziv'));

        const jeAdmin = {{ auth()->user()->jeAdmin() ? 'true' : 'false' }};

        let ucitaniTermini = {};

        let izabraniTerminId = null;

        function osveziTrajanje() {
            const pocetak = uMinute(document.getElementById('rez-pocetak').value);
            const kraj = uMinute(document.getElementById('rez-kraj').value);
            const razlika = kraj - pocetak;

            document.getElementById('rez-trajanje').value = razlika > 0
                ? Math.floor(razlika / 60) + ' h ' + (razlika % 60) + ' min'
                : 'kraj mora biti posle početka';
        }

        async function ucitajTermine(datum) {
            const odgovor = await fetch('{{ route('raspored.termini') }}?datum=' + datum);
            if (!odgovor.ok) {
                console.error('Greška pri učitavanju termina:', odgovor.status);
                return;
            }
            const termini = (await odgovor.json()).data;

            ucitaniTermini = {};
            termini.forEach(function (termin) {
                ucitaniTermini[termin.id] = termin;
            });

            nacrtajRaspored('.timetable', redosledUcionica, termini, function (termin) {
                return naziviUcionica[termin.ucionica_id];
            }, function (termin) {
                if (jeAdmin) {
                    otvoriPopupStatus([String(termin.id)]);
                }
            });
        }

        let izabraniDatum = napraviIzborDatuma('izbor-datuma', function (datum) {
            izabraniDatum = datum;
            prikaziDatum(datum);
            ucitajTermine(datum);
        });

        prikaziDatum(izabraniDatum);
        ucitajTermine(izabraniDatum);

        document.getElementById('dugme-nova').addEventListener('click', function () {
            document.getElementById('rez-ucionica-id').value = '';
            document.getElementById('rez-datum').value = document.getElementById('naslov-datuma').textContent;
            document.getElementById('rez-pocetak').value = '09:15';
            document.getElementById('rez-kraj').value = '10:00';
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
                    datum: izabraniDatum,
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

        function otvoriPopupStatus(terminIds) {
            const izbor = document.getElementById('status-izbor');
            izbor.innerHTML = '';

            if (terminIds.length > 1) {
                terminIds.forEach(function (id) {
                    const termin = ucitaniTermini[id];
                    const opcija = document.createElement('option');
                    opcija.value = id;
                    opcija.textContent = termin.naziv + ' (' + termin.status + ')';
                    izbor.appendChild(opcija);
                });
                izbor.classList.remove('d-none');
            } else {
                izbor.classList.add('d-none');
            }

            izbor.value = terminIds[terminIds.length - 1];
            prikaziTerminUPopupu(terminIds[terminIds.length - 1]);

            document.getElementById('status-greska').classList.add('d-none');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('popup-status')).show();
        }

        function prikaziTerminUPopupu(terminId) {
            const termin = ucitaniTermini[terminId];
            izabraniTerminId = terminId;

            document.getElementById('status-naziv').textContent = termin.naziv;
            document.getElementById('status-vreme').textContent =
                naziviUcionica[termin.ucionica_id] + ', ' + termin.vreme_pocetka + '–' + termin.vreme_kraja;
            document.getElementById('status-opis').textContent = termin.opis || '';
            document.getElementById('status-trenutni').textContent = termin.status;
        }

        document.getElementById('status-izbor').addEventListener('change', function () {
            prikaziTerminUPopupu(this.value);
            document.getElementById('status-greska').classList.add('d-none');
        });

        document.querySelectorAll('#popup-status [data-status]').forEach(function (dugme) {
            dugme.addEventListener('click', async function () {
                const greska = document.getElementById('status-greska');
                greska.classList.add('d-none');

                const odgovor = await fetch('{{ url('/raspored/termini') }}/' + izabraniTerminId + '/status', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ status: dugme.dataset.status }),
                });

                if (odgovor.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('popup-status')).hide();
                    ucitajTermine(izabraniDatum);  
                } else {
                    const podaci = await odgovor.json();
                    greska.textContent = podaci.message;
                    greska.classList.remove('d-none');
                }
            });
        });

        window.addEventListener('resize', function () {
            ucitajTermine(izabraniDatum);
        });
    </script>
@endpush
