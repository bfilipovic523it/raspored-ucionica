@extends('layouts.app')

@section('title', 'Rasporedi')

@section('content')
    <div class="d-flex align-items-center justify-content-between">
        <h2>Rasporedi</h2>
        <a href="{{ route('rasporedi.create') }}" class="btn btn-primary">Novi uvoz</a>
    </div>
    @if (session('poruka'))
        <div class="alert alert-success">{{ session('poruka') }}</div>
    @endif

    @if (session('upozorenja'))
        <div class="alert alert-warning">
            <strong>Upozorenje:</strong> broj uvezenih termina koji se preklapaju sa već postojećim terminima: {{ count(session('upozorenja')) }}.
            <ul class="mb-0 mt-2" style="max-height: 300px; overflow-y: auto;">
                @foreach (session('upozorenja') as $upozorenje)
                    <li>{{ $upozorenje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($rasporedi->isEmpty())
        <div class="alert alert-info">Još nema uvezenih rasporeda.</div>
    @else
        <table class="table table-striped table-hover align-middle bg-white">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Naziv</th>
                    <th>Tip</th>
                    <th>Uvezao</th>
                    <th>Datum uvoza</th>
                    <th class="text-end">Termina</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rasporedi as $raspored)
                    <tr>
                        <td>{{ $raspored->id }}</td>
                        <td>{{ $raspored->naziv }}</td>
                        <td>{{ $raspored->tip === 'nastava' ? 'Raspored nastave' : 'Raspored testiranja' }}</td>
                        <td>{{ $raspored->user->name }}</td>
                        <td>{{ $raspored->created_at->format('d.m.Y. H:i') }}</td>
                        <td class="text-end">{{ $raspored->termini_count }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('rasporedi.destroy', $raspored) }}" class="m-0"
                                  onsubmit="return confirm('Obrisati raspored „{{ $raspored->naziv }}“ i svih {{ $raspored->termini_count }} njegovih termina?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Obriši</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $rasporedi->links() }}
    @endif
@endsection
