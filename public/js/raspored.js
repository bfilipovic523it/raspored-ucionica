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

const naziviStatusa = {
    potvrdjen: 'potvrđen',
    ceka: 'na čekanju',
    odbijen: 'odbijen',
};

let prikazaniTerminId = null;
let posleIzmeneStatusa = null;

function prikaziDetaljeTermina(termin, naziviUcionica, posleIzmene) {
    const [godina, mesec, dan] = termin.datum.split('-');

    prikazaniTerminId = termin.id;
    posleIzmeneStatusa = posleIzmene;
    document.getElementById('detalji-greska').classList.add('d-none');

    document.getElementById('detalji-naziv').textContent = termin.naziv;
    document.getElementById('detalji-ucionica').textContent = naziviUcionica[termin.ucionica_id];
    document.getElementById('detalji-datum').textContent = dan + '.' + mesec + '.' + godina + '.';
    document.getElementById('detalji-vreme').textContent = termin.vreme_pocetka + '–' + termin.vreme_kraja;
    document.getElementById('detalji-nastavnik').textContent = termin.nastavnik || '—';
    document.getElementById('detalji-status').textContent = naziviStatusa[termin.status];
    document.getElementById('detalji-opis').textContent = termin.opis || '—';

    bootstrap.Modal.getOrCreateInstance(document.getElementById('popup-detalji')).show();
}

document.addEventListener('click', async function (dogadjaj) {
    const dugme = dogadjaj.target.closest('#popup-detalji [data-status]');
    if (!dugme) {
        return;
    }

    const popup = document.getElementById('popup-detalji');
    const greska = document.getElementById('detalji-greska');
    greska.classList.add('d-none');

    const odgovor = await fetch(popup.dataset.urlTermini + '/' + prikazaniTerminId + '/status', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ status: dugme.dataset.status }),
    });

    if (odgovor.ok) {
        bootstrap.Modal.getInstance(popup).hide();
        if (posleIzmeneStatusa) {
            posleIzmeneStatusa();
        }
    } else {
        const podaci = await odgovor.json();
        greska.textContent = podaci.message;
        greska.classList.remove('d-none');
    }
});

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
