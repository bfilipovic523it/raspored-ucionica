# Raspored učionica — kontekst projekta

Ovo je diplomski rad: Laravel aplikacija u kojoj nastavnici rezervišu termine u učionicama, a admin te zahteve potvrđuje ili odbija. Specifikaciju je napisao profesor, a detalji i odluke su razrađeni u razgovoru. Ovaj fajl sadrži sve to na jednom mestu. Ako nešto nije pokriveno ovde, pitaj pre nego što pretpostaviš.

Ako u `docs/` postoje originalna specifikacija profesora, `RAF_Raspored.csv` i slika kalendara nastave, to su izvorni materijali. Kada se razlikuju od ovog fajla, ovaj fajl ima prednost jer sadrži kasnije odluke.

## Radni dogovor

- Komuniciraj na srpskom (latinica). Komentari u kodu, tekstovi na ekranu (UI), poruke o greškama i dokumentacija pišu se na srpskom, jer se rad predaje fakultetu.
- Kod piši kompletan i spreman za upotrebu, sa detaljnim komentarima na srpskom.
- Objašnjenja neka budu detaljna i „školska", kao za potpunog početnika: zašto nešto radiš, korak po korak.
- Nazivi tabela, modela i kontrolera kao u specifikaciji (srpski nazivi sa Laravel množinom: `ucionicas`, `termins`).
- Git: manje, milestone commit-ovi (jedan po završenoj fazi), ne po svakoj sitnici.
- Okruženje: Windows, VS Code, XAMPP (MySQL) i phpMyAdmin.
- Tajne (Google client id i secret, lozinka baze) idu samo u `.env`, nikad u kod ni u ovaj fajl.

## Cilj i uloge

**Nastavnik**
- Vidi kalendar zauzeća učionica: sve potvrđene termine i svoje termine na čekanju.
- Zakazuje termin (unos u `termins` sa statusom `ceka`).
- Ne može da otkaže ni da obriše termin, to radi samo admin.

**Admin**
- Vidi sve termine. Boje: odbijeni sivo, na čekanju žuto.
- Klikom menja status termina (potvrdi / odbij).
- CRUD za učionice, korisnike, termine i kalendar školske godine.
- Bulk uvoz rasporeda iz CSV-a (vidi odeljak „Uvoz CSV-a").
- Briše termine.

## Tehnologije

- Laravel 12 (XAMPP ima PHP 8.2.12, a Laravel 13 traži PHP 8.3+, pa se Laravel 13 ne koristi), Blade, Bootstrap 5, MySQL (XAMPP). Bez starter kita (Breeze, Jetstream): nema registracije ni lozinki.
- Kalendar u pregledu: calendarjs (https://calendarjs.com/docs/calendar/international), koji se instancira u Blade-u, a podaci se učitavaju i osvežavaju preko `fetch()` AJAX poziva.
- Laravel Blueprint (`draft.yaml`) za generisanje modela, migracija, resource kontrolera i ruta.
- Prijava: Google (OAuth), predlog je Laravel Socialite. Passport se NE koristi, iako ga je profesor pomenuo. Bitno mu je samo da se ne može prijaviti bilo ko.

## Baza

- `users`: standardna Laravelova tabela plus kolona za vrstu korisnika (`admin`, `nastavnik`).
- `ucionicas`: `id`, `naziv`, `opis`, plus oznaka da je učionica izuzeta iz provere preklapanja (predlog: boolean kolona; potrebna za „RAF Online").
- `termins`: `id`, `ucionica_id`, `user_id`, `created_at` / `updated_at` (standardni Laravelovi), `created_by`, `updated_by`, `naziv`, `opis`, `datum`, `vreme_pocetka`, `vreme_kraja`, `bulk_uid`, `status` (enum: `potvrdjen`, `odbijen`, `ceka`).
- Modeli: `User`, `Ucionica`, `Termin`. Migracije: `create_ucionicas`, `create_termins`.
- Trajanje se NE čuva u bazi, računa se iz početka i kraja.

## Pravila

### Prijava
- Samo Google prijava, bez registracije i bez klasičnog unosa mejla i lozinke.
- Dozvoljen je samo RAF domen. Domen se drži u `.env` (na primer `ALLOWED_EMAIL_DOMAIN=raf.rs`), a provera se radi na serveru (parametar `hd` kod Googlea nije dovoljan jer se može zaobići).
- Korisnik mora već da postoji u `users` (povezivanje po mejlu). Nove korisnike unosi admin ili seeder.

### Preklapanje termina
- Server odbija novi termin ako u istoj učionici i istog datuma postoji termin sa statusom `ceka` ili `potvrdjen` čije se vreme seče sa njegovim. Uslov: `novi.pocetak < postojeci.kraj` i `novi.kraj > postojeci.pocetak` (termin koji počinje tačno kad drugi završava ne smeta).
- Odbijeni (`odbijen`) termini ne blokiraju ništa.
- Ako je preklopljeni termin na čekanju, nastavnik mora da sačeka da ga admin potvrdi ili odbije pa tek onda šalje zahtev.
- Provera se radi u transakciji, da dva nastavnika ne prođu istovremeno.
- Učionica „RAF Online" je izuzeta iz provere (dozvoljeni su istovremeni termini).

## Ekrani

### Layout (zajednički)
- Bootstrap 5, `container-fluid` (od ivice do ivice, jer kalendar treba da bude prostran).
- Navbar: levo naziv „Raspored učionica" kao link ka home ruti, desno logout. Admin u navbaru ima i linkove: Učionice, Korisnici, Termini.

### Admin (`resources/views/admin/`)
- `ucionica/`, `user/`: klasični CRUD blade-ovi.
- `termin/`: klasični CRUD, spisak svih termina sa paginacijom, da admin može direktno da menja redove (low level, bez JavaScript kalendara). Admin u njemu može da menja sva polja, uključujući status.

### Kalendar (`resources/views/raspored/kalendar.blade.php`)
- Kolone su sve učionice, redovi su sati od 9 do 21.
- Prikaz je za izabrani dan (pon–ned). Konkretan datum se bira u kalendaru pored.
- Korak mreže je 15 minuta, jer termini u rasporedu počinju u :15 (npr. 09:15) i završavaju na pun sat.
- Klik na polje otvara popup za rezervaciju: datum, vreme početka, vreme kraja (trajanje se računa i prikazuje), naziv, opis (opcioni). Primer: naziv „PWA", opis „Nadoknada termina vežbi od 14.10. za grupe 2s1, 2s2".
- `user_id` se uzima iz sesije (ulogovani korisnik), a status je podrazumevano `ceka`.
- Nastavnik vidi potvrđene termine i svoje na čekanju. Admin vidi sve (odbijeni sivo, na čekanju žuto) i klikom menja status.

### Rute
- `auth` + admin middleware: resource rute `admin.ucionica.*`, `admin.user.*`, `admin.termin.*` (generiše Blueprint iz resource kontrolera `Ucionica`, `User`, `Termin`).
- `auth` (nastavnik i admin): `raspored.kalendar` i home ruta `/`.
- Login preko Googlea, bez registracije.

## Uvoz CSV-a (bulk insert, samo admin)

### Ekran
- Polje za upload CSV fajla.
- Izbor režima: **one-time** (kolokvijumi i ispiti, tačan datum) ili **recurring** (raspored časova kroz ceo semestar).
- Izbor tipa fajla: CSV **sa datumima** ili **sa danima u nedelji**.
- Za recurring: izbor semestra, tj. kalendara nastave (vidi dole).

### Pravila
- Svi termini jednog uvoza dobijaju isti `bulk_uid` u formatu `timestamp.user_id`, gde je `user_id` admin koji je izvršio uvoz. Admin se beleži i u `created_by`.
- `user_id` svakog uvezenog termina je **profesor** (kao korisnik iz `users`), a ne admin. Povezivanje je po imenu iz kolone `Profesor`, uz normalizaciju (velika/mala slova, dijakritici, `Dj`/`Đ`).
- Uvezeni termini odmah dobijaju status `potvrdjen`.
- „RAF Online" je obična učionica u `ucionicas` (ali izuzeta iz provere preklapanja).
- Predlog toka: uvoz u dva koraka. Prvo pregled sa izveštajem o neusklađenim redovima (nepoznata učionica, nepoznat profesor, sudar sa već postojećim terminom), pa upis u bazu tek posle admin potvrde. (Podrazumevano važi, osim ako student kaže drugačije.)
- Brisanje recurring termina: admin može da obriše sve pojave istog paketa (ista učionica, isto vreme, isti naziv, isti `bulk_uid`).

### Format fajla `docs/RAF_Raspored.csv`
- Kolone: `Predmet`, `Profesor`, `Grupe`, `Učionica`, `Termin`, `eLearning`, `Zoom`, `Konsultacije`.
- 383 reda, 99 različitih profesora, 29 učionica. Ovo je „CSV sa danima": nema datuma, samo dani, pa se uvozi isključivo kao recurring.
- `Termin` izgleda ovako: `PON 09:15–10`. Dani: PON, UTO, SRE, ČET, PET. Kraj je samo sat, pa `10` znači 10:00 (`21` znači 21:00). Počeci su u :15, a termini traju između 09:15 i 21:00.
- U fajlu nema preklapanja termina u istoj učionici.
- `Predmet` ima oblik `Naziv - Vežbe 1` ili `Naziv - Predavanja 2` (tipovi: Vežbe, Predavanja, Demonstracije, MCF; nekoliko redova nema ` - `). Cela vrednost ide u `termins.naziv`. Predlog: u `termins.opis` ide `Grupe` iz fajla.
- Nazivi učionica su neujednačeni (`Raf 1 (u)`, `Rg7 (u)`, `Muzicki studio`, `Nordeus`, `Kolarac 1`, `RAF Online (online)`). Povezivanje sa `ucionicas.naziv` je po tačnom nazivu. Spisak učionica za seeder se dobija iz jedinstvenih vrednosti kolone `Učionica`.
- Kolone `eLearning`, `Zoom` i `Konsultacije` se pri uvozu ignorišu. Mejl profesora je u `Konsultacije` za 81 od 99 profesora i može da posluži samo za sastavljanje spiska korisnika za seeder.

## Kalendar nastave 2026/2027

Za recurring uvoz se datumi NE računaju kao „13 nedelja". Prolazi se datum po datum: za svaki datum se odredi efektivni dan u nedelji (specijalni radni dani se mapiraju na drugi dan, subota i nedelja se preskaču ako nisu specijalni), a preskaču se neradni dani i kolokvijumske nedelje.

**Zimski semestar**
- Početak: 28.09.2026.
- Neradni dani: 11.11, 31.12–03.01, 06.01–08.01.
- Specijalni radni dani: subota 31.10 radi kao sreda, ponedeljak 04.01 kao četvrtak, utorak 05.01 kao petak.
- Kolokvijumske nedelje: 14.11–22.11 i 09.01–17.01.
- Ispitni rokovi: januarski 18.01–03.02, februarski 04.02–21.02. Neradni dani 15.02–16.02.

**Letnji semestar**
- Početak: 22.02.2027.
- Neradni dani: 30.04–03.05.
- Specijalni radni dan: sreda 02.06 radi kao petak.
- Kolokvijumske nedelje: 10.04–18.04 i 03.06–13.06.
- Raspust: 19.07–15.08.
- Ispitni rokovi: junski 14.06–30.06, julski 01.07–18.07, avgustovski 16.08–01.09, septembarski 02.09–19.09.
- Upis na fakultet 20.09–24.09, početak nove školske godine 27.09.

**Prozor nastave za generisanje:** zimski od 28.09.2026 do 08.01.2027, letnji od 22.02.2027 do 02.06.2027 (pre poslednje kolokvijumske nedelje).

**Očekivani broj termina po predmetu** (po čitanju kalendara, treba proveriti): zimski semestar tačno 13 za svaki dan u nedelji. Letnji 13, osim utorka koji ima 14. Ne hardkodovati 13, brojanje treba da proističe iz kalendara.

**Predlog strukture u bazi:** `semestars` (naziv, skolska_godina, pocetak, kraj_nastave) i `kalendar_stavkas` (semestar_id, datum_od, datum_do, tip: `neradni` / `kolokvijumska` / `specijalni_radni`, radi_kao nullable: PON–PET). Admin ih unosi ručnim CRUD-om, ima ih desetak po semestru.

## Seederi

- `users`: za razvoj se koriste probni podaci. Predlog: 99 korisnika sa imenima iz CSV-a (da uvoz može da se isproba na pravom fajlu) i izmišljenim mejlovima, plus admin nalog čiji mejl dolazi iz `.env`. Profesor će kasnije poslati pravi spisak nastavnika sa mejlovima, pa seeder mora da radi „upsert" po mejlu (ne briše i ne pravi korisnike iznova), da uvezeni termini zadrže svog profesora.
- `ucionicas`: iz jedinstvenih vrednosti kolone `Učionica` u CSV-u.
- Termini: seeder samo za probu tokom razvoja, posle se uklanja.

## Predlog redosleda izrade

1. Baza: migracije, modeli, seederi (korisnici, učionice, kalendar nastave).
2. Prijava: Google prijava sa ograničenjem domena, uloge i middleware.
3. Admin CRUD-ovi (učionice, korisnici, termini, kalendar nastave).
4. Kalendar prikaz i popup za rezervaciju, uz pravilo preklapanja.
5. Uvoz CSV-a (pregled, upis, brisanje paketa).

## Otvorena pitanja

- Utorak sa 14 termina u letnjem semestru: namerno ili greška u čitanju kalendara? (pitanje za profesora)
- Spisak svih nastavnika sa mejlovima: iz CSV-a se dobija 81 od 99, a za 18 treba spisak od profesora.
- Potvrditi uvoz u dva koraka (pregled, pa upis).
- Da li se provera preklapanja primenjuje i kada admin u CRUD-u menja status (na primer iz `odbijen` u `potvrdjen`)?
