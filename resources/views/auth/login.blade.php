<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>Prijava — Raspored učionica</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container" style="max-width: 420px; margin-top: 80px;">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Raspored učionica</h1>
                <p class="text-muted small">
                    Probna prijava
                </p>

                <form method="POST" action="{{ url('/login') }}">
                    @csrf
                    <select name="user_id" class="form-select mb-3" required>
                        @foreach ($korisnici as $korisnik)
                            <option value="{{ $korisnik->id }}">
                                {{ $korisnik->name }} ({{ $korisnik->uloga }})
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary w-100">Prijavi se</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
