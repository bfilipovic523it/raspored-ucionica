<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>Raspored učionica</title>
</head>
<body>
    <h1>Raspored učionica</h1>

    <p>Broj učionica: {{ $ucionice->count() }}</p>

    <ul>
        @foreach ($ucionice as $ucionica)
            <li>{{ $ucionica->naziv }}</li>
        @endforeach
    </ul>
</body>
</html>
