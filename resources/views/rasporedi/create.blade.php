@extends('layouts.app')

@section('title', 'Novi uvoz rasporeda')

@section('content')
    <h2>Novi uvoz rasporeda</h2>

    <div class="card" style="max-width: 640px;">
        <div class="card-body">
            @if (session('greske_uvoza'))
                <div class="alert alert-danger">
                    <strong>Raspored nije uvezen.</strong> U fajlovima je pronađeno grešaka: {{ count(session('greske_uvoza')) }}.
                    Ispravite ih i ponovo izaberite fajlove.
                    <ul class="mb-0 mt-2" style="max-height: 300px; overflow-y: auto;">
                        @foreach (session('greske_uvoza') as $greska)
                            <li>{{ $greska }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $greska)
                            <li>{{ $greska }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('rasporedi.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="naziv">Naziv</label>
                    <input type="text" id="naziv" name="naziv" class="form-control"
                           value="{{ old('naziv') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Vrsta rasporeda</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tip" id="tip-nastava" value="nastava"
                               @checked(old('tip', 'nastava') === 'nastava')>
                        <label class="form-check-label" for="tip-nastava">Raspored nastave (semestralni)</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tip" id="tip-testiranje" value="testiranje"
                               @checked(old('tip') === 'testiranje')>
                        <label class="form-check-label" for="tip-testiranje">Raspored testiranja (ispiti, kolokvijumi)</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="fajl_raspored">Raspored (CSV)</label>
                    <input type="file" id="fajl_raspored" name="fajl_raspored" class="form-control" accept=".csv" required>
                </div>

                <div class="mb-3" id="polje-dani">
                    <label class="form-label" for="fajl_dani">Datumi po danima (CSV)</label>
                    <input type="file" id="fajl_dani" name="fajl_dani" class="form-control" accept=".csv">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Sačuvaj</button>
                    <a href="{{ route('rasporedi.index') }}" class="btn btn-secondary">Odustani</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function osveziPoljeDani() {
            const nastava = document.getElementById('tip-nastava').checked;
            document.getElementById('polje-dani').classList.toggle('d-none', !nastava);
            document.getElementById('fajl_dani').required = nastava;
        }

        document.querySelectorAll('input[name="tip"]').forEach(function (radio) {
            radio.addEventListener('change', osveziPoljeDani);
        });

        osveziPoljeDani();
    </script>
@endpush
