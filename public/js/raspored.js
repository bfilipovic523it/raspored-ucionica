calendarjs.setDictionary({
    'January': 'Januar', 'February': 'Februar', 'March': 'Mart',
    'April': 'April', 'May': 'Maj', 'June': 'Jun',
    'July': 'Jul', 'August': 'Avgust', 'September': 'Septembar',
    'October': 'Oktobar', 'November': 'Novembar', 'December': 'Decembar',
    'Sunday': 'Nedelja', 'Monday': 'Ponedeljak', 'Tuesday': 'Utorak',
    'Wednesday': 'Sreda', 'Thursday': 'Četvrtak', 'Friday': 'Petak',
    'Saturday': 'Subota', 'Done': 'Gotovo', 'Reset': 'Poništi'
});

function uMinute(vreme) {
    const [sat, minut] = vreme.split(':');
    return Number(sat) * 60 + Number(minut);
}

function uVreme(minuti) {
    const sat = String(Math.floor(minuti / 60)).padStart(2, '0');
    const minut = String(minuti % 60).padStart(2, '0');
    return sat + ':' + minut;
}

function uDatum(datumObjekat) {
    return datumObjekat.getFullYear() + '-'
        + String(datumObjekat.getMonth() + 1).padStart(2, '0') + '-'
        + String(datumObjekat.getDate()).padStart(2, '0');
}

function danasnjiDatum() {
    return uDatum(new Date());
}

function prikaziDatum(datum) {
    const dani = ['Nedelja', 'Ponedeljak', 'Utorak', 'Sreda', 'Četvrtak', 'Petak', 'Subota'];
    const [godina, mesec, dan] = datum.split('-');
    const danUNedelji = new Date(godina, mesec - 1, dan).getDay();
    document.getElementById('naslov-datuma').textContent =
        dani[danUNedelji] + ', ' + dan + '.' + mesec + '.' + godina + '.';
}

function napraviIzborDatuma(elementId, naIzbor) {
    const danas = danasnjiDatum();

    calendarjs.Calendar(document.getElementById(elementId), {
        type: 'inline',
        startingDay: 1,
        value: danas,
        onchange: function (self, vrednost) {
            naIzbor(vrednost.slice(0, 10));
        },
    });

    return danas;
}

function nacrtajRaspored(selektor, redovi, termini, redTermina, naKlik) {
    const raspored = new Timetable();
    raspored.setScope(9, 21);
    raspored.addLocations(redovi);

    const terminiPoId = {};

    termini.forEach(function (termin) {
        terminiPoId[termin.id] = termin;

        const [satPocetka, minutPocetka] = termin.vreme_pocetka.split(':').map(Number);
        const [satKraja, minutKraja] = termin.vreme_kraja.split(':').map(Number);

        raspored.addEvent(
            termin.naziv,
            redTermina(termin),
            new Date(2000, 0, 1, satPocetka, minutPocetka),
            new Date(2000, 0, 1, satKraja, minutKraja),
            {
                class: termin.status,
                data: { id: termin.id },
                onClick: function () {
                    if (naKlik) {
                        naKlik(termin);
                    }
                },
            }
        );
    });

    new Timetable.Renderer(raspored).draw(selektor);
    rasporediPreklapanja(selektor, terminiPoId);
}

function rasporediPreklapanja(selektor, terminiPoId) {
    document.querySelectorAll(selektor + ' ul.room-timeline > li').forEach(function (red) {
        const stavke = Array.from(red.querySelectorAll('.time-entry')).map(function (element) {
            const termin = terminiPoId[element.dataset.id];
            return {
                element: element,
                pocetak: uMinute(termin.vreme_pocetka),
                kraj: uMinute(termin.vreme_kraja),
            };
        }).sort(function (a, b) {
            return a.pocetak - b.pocetak;
        });

        let grupa = [];
        let krajGrupe = 0;

        stavke.forEach(function (stavka) {
            if (grupa.length > 0 && stavka.pocetak >= krajGrupe) {
                podeliNaTrake(grupa);
                grupa = [];
            }
            grupa.push(stavka);
            krajGrupe = grupa.length === 1 ? stavka.kraj : Math.max(krajGrupe, stavka.kraj);
        });

        if (grupa.length > 0) {
            podeliNaTrake(grupa);
        }
    });
}

function podeliNaTrake(grupa) {
    const krajeviTraka = [];

    grupa.forEach(function (stavka) {
        let traka = krajeviTraka.findIndex(function (kraj) {
            return kraj <= stavka.pocetak;
        });
        if (traka === -1) {
            traka = krajeviTraka.length;
        }
        krajeviTraka[traka] = stavka.kraj;
        stavka.traka = traka;
    });

    const brojTraka = krajeviTraka.length;

    grupa.forEach(function (stavka) {
        stavka.element.style.top = (stavka.traka * 100 / brojTraka) + '%';
        stavka.element.style.height = (100 / brojTraka) + '%';
    });
}
