<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="utf-8">
    <title>Proba calendarjs</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@calendarjs/ce/dist/style.min.css">
    <script src="https://cdn.jsdelivr.net/npm/lemonadejs/dist/lemonade.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@calendarjs/ce/dist/index.min.js"></script>
</head>
<body>
    <h1>Proba calendarjs</h1>

    <div id="kalendar"></div>

    <p>Izabrani datum: <strong id="izabrani">nijedan</strong></p>

    <h2>Proba: Schedule (prikaz jednog dana)</h2>
    <div id="raspored" style="height: 600px;"></div>

    <script>
        
        const { Calendar } = calendarjs;

        calendarjs.setDictionary({
            'January': 'Januar',
            'February': 'Februar',
            'March': 'Mart',
            'April': 'April',
            'May': 'Maj',
            'June': 'Jun',
            'July': 'Jul',
            'August': 'Avgust',
            'September': 'Septembar',
            'October': 'Oktobar',
            'November': 'Novembar',
            'December': 'Decembar',
            'Sunday': 'Nedelja',
            'Monday': 'Ponedeljak',
            'Tuesday': 'Utorak',
            'Wednesday': 'Sreda',
            'Thursday': 'Četvrtak',
            'Friday': 'Petak',
            'Saturday': 'Subota',
            'Done': 'Gotovo',
            'Reset': 'Poništi'
        });

        Calendar(document.getElementById('kalendar'), {
            type: 'inline',      
            startingDay: 1,     
            value: new Date(),   
            onchange: function (self, vrednost) {
                document.getElementById('izabrani').textContent = vrednost;
            },
        });

        const { Schedule } = calendarjs;

        const danas = new Date().toISOString().slice(0, 10);

        Schedule(document.getElementById('raspored'), {
            type: 'day',    
            value: danas,   
            grid: 15,     
            data: [
                { guid: 't1', title: 'PWA - Vežbe 1', date: danas, start: '09:15', end: '11:00', color: '#3498db' },
                { guid: 't2', title: 'Engleski 1 - Predavanja', date: danas, start: '11:15', end: '13:00', color: '#f1c40f' },
            ],
        });

    </script>
</body>
</html>
