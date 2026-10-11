<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>Raspored učionica</title>
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
    </style>
</head>
<body>
    <h1>Raspored učionica</h1>
    <h2 id="naslov-datuma"></h2>

    <div class="raspored">
        <!-- Levo: mali kalendar za izbor datuma -->
        <div id="izbor-datuma"></div>

        <!-- Desno: mreža (kolone su učionice, redovi su vremena) -->
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
                        {{-- Ako se vreme završava na ":00", red dobija klasu "pun-sat". --}}
                        <tr class="{{ str_ends_with($vreme, ':00') ? 'pun-sat' : '' }}">
                            <th>{{ $vreme }}</th>
                            @foreach ($ucionice as $ucionica)
                                <td class="polje"></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const { Calendar } = calendarjs;

        // Prevod na srpski (mora PRE pravljenja kalendara).
        calendarjs.setDictionary({
            'January': 'Januar', 'February': 'Februar', 'March': 'Mart',
            'April': 'April', 'May': 'Maj', 'June': 'Jun',
            'July': 'Jul', 'August': 'Avgust', 'September': 'Septembar',
            'October': 'Oktobar', 'November': 'Novembar', 'December': 'Decembar',
            'Sunday': 'Nedelja', 'Monday': 'Ponedeljak', 'Tuesday': 'Utorak',
            'Wednesday': 'Sreda', 'Thursday': 'Četvrtak', 'Friday': 'Petak',
            'Saturday': 'Subota', 'Done': 'Gotovo', 'Reset': 'Poništi'
        });

        // Prikazuje izabrani datum iznad mreže, npr. "Nedelja, 11.10.2026."
        // datum je tekst u formatu Y-M-D, npr. "2026-10-11".
        function prikaziDatum(datum) {
            const dani = ['Nedelja', 'Ponedeljak', 'Utorak', 'Sreda', 'Četvrtak', 'Petak', 'Subota'];
            const [godina, mesec, dan] = datum.split('-');                  // "2026-10-11" -> ["2026","10","11"]
            const danUNedelji = new Date(godina, mesec - 1, dan).getDay();  // 0 = nedelja ... 6 = subota
            document.getElementById('naslov-datuma').textContent =
                dani[danUNedelji] + ', ' + dan + '.' + mesec + '.' + godina + '.';
        }

        // Današnji datum u formatu Y-M-D, po lokalnom vremenu računara.
        const sada = new Date();
        const danas = sada.getFullYear() + '-'
            + String(sada.getMonth() + 1).padStart(2, '0') + '-'
            + String(sada.getDate()).padStart(2, '0');

        Calendar(document.getElementById('izbor-datuma'), {
            type: 'inline',
            startingDay: 1,
            value: danas,
            onchange: function (self, vrednost) {
                // Izabrani datum: za sada ga samo prikazujemo.
                // U sledećem koraku ovde učitavamo termine za taj dan.
                prikaziDatum(vrednost.slice(0, 10));
            },
        });

        // Na početku je izabran današnji dan.
        prikaziDatum(danas);
    </script>

</body>
</html>
