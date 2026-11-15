# Lilia ilusalong

Ilusalongi veebisait teenuste tutvustamiseks ja online-broneerimiseks.
PHP (MVC) + MySQL, kahes keeles (eesti / vene), kohanduv kujundus.

Projekt on tehtud PHP-ülesande raames eelmise ülesande (NewsPortal) malli põhjal.

## Funktsionaalsus

**Avalik sait**
- Avaleht: teenuste kategooriad, populaarsed teenused, meistrid, arvustused
- Teenuste kataloog filtreerimisega kategooria järgi (JavaScript)
- Teenuse leht: kirjeldus, hind, kestus, arvustused ja hinnangud (1–5)
- Online-broneering: teenus → meister → kuupäev → vaba kellaaeg (AJAX)
  - kontrollitakse tööaega (E–R 9–19, L 10–16, P suletud), möödunud aegu ja topeltbroneeringuid
- Meistrite leht, kontaktileht, otsing (mõlemas keeles), kasutaja registreerimine
- Keelevahetus ET / RU (liidese tekstid ja andmebaasi sisu)

**Halduspaneel** (`/salon_admin`)
- Töölaud statistika ja eelseisvate broneeringutega
- Teenuste lisamine, muutmine, kustutamine, pildi üleslaadimine
- Kategooriate haldus
- Broneeringute nimekiri, filtrid ja staatuse muutmine (uus / kinnitatud / tühistatud)

Testkonto: **admin@lilia.ee** / **admin123**

## Ülesande nõuded

| Nõue | Lahendus |
|---|---|
| Andmebaasi tabelid vastavad teemale | `categories`, `services`, `masters`, `bookings`, `reviews`, `users` — vt `database.sql` |
| Kohanduvus | Mobile-first CSS, murdepunktid 640 px ja 900 px, mobiilimenüü |
| Versioonihaldus | Git, iga etapp eraldi commit'ina |
| Dünaamilised elemendid | CSS-animatsioonid, kerimisel ilmumine, loendurid, filter, AJAX-ajad, ripple, „Üles“ nupp (`public/js/main.js`) |
| Teine keel | `inc/Lang.php`, `lang/et.php`, `lang/ru.php`, DB veerud `*_et` / `*_ru` |

## Käivitamine

### Docker (soovitatav)
```bash
docker compose up --build
```
- Sait: http://localhost:8080
- Halduspaneel: http://localhost:8080/salon_admin/
- phpMyAdmin: http://localhost:8081 (root / root)

Andmebaas luuakse automaatselt failist `database.sql`.

### XAMPP / OpenServer
1. Kopeeri projekt kausta `htdocs` (Apache `mod_rewrite` peab olema sees).
2. Impordi `database.sql` phpMyAdminis.
3. Vajadusel muuda ühenduse andmeid failis `inc/Database.php`
   (vaikimisi `host=db`; XAMPP puhul `localhost`) või määra keskkonnamuutujad
   `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`.

## Struktuur

```
index.php              # avaliku saidi sisendpunkt
route/routing.php      # marsruudid
controller/            # Controller
model/                 # Category, Service, Master, Review, Booking, Register
view/                  # vaated (layout, lehed, ViewService, ViewReview, Icons)
lang/                  # tõlked (et, ru)
inc/                   # Database (PDO), Lang
public/                # CSS, JS, pildid
uploads/               # halduspaneelist üleslaetud pildid
salon_admin/           # halduspaneel (oma MVC)
database.sql           # andmebaasi skeem ja testandmed
```

## Andmebaas

```
categories 1──* services 1──* reviews
     │              │
     1              1
     *              *
  masters 1──* bookings
users (administraatorid ja kliendid)
```

## Turvalisus
- Kõik SQL-päringud ettevalmistatud lausetega (PDO prepared statements)
- Kogu väljund läbi `htmlspecialchars` (XSS-kaitse)
- Paroolid `password_hash` / `password_verify`
- Halduspaneel ainult rollile `admin`
- Üleslaetud failide tüübi ja suuruse kontroll, `uploads/` kaustas PHP käivitamine keelatud

---
JPTV24 · 2026
