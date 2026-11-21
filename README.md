# Daily Quotes MRK

Önálló WordPress-plugin napi idézet megjelenítéséhez Elementorban. Az idézet és a szerző külön formázható, az elrendezés eszközönként állítható. Elementor Pro nem szükséges.

## Telepítés

A kiadási ZIP-et töltsd fel a WordPress bővítménytelepítőjében. Elementor legyen telepítve és aktív. Az **Quotes** menüben rögzíts egy idézetet, majd az Elementorban keresd a **Daily Quotes MRK** widgetet.

A korábbi shortcode-os plugint kapcsold ki. A meglévő idézetek megmaradnak, az adatbázis szerkezete és a widget belső azonosítója kompatibilis a korábbi változattal.

## Működés

- Naponta egy közös idézet, a WordPress időzónája alapján.
- Külön tipográfia, szín és szövegárnyék az idézethez és a névhez.
- Egymás melletti vagy alatti elrendezés, térköz és igazítás.
- Az admin menthet, szerkeszthet és archiválhat.
- Az archiválás nem törli a rekordot; az eltávolítás is megtartja az adatokat.
- Külső idézetszolgáltatás és nyilvános AJAX-végpont nincs.

A teljesoldalas/CDN gyorsítótárat a napi váltáshoz és módosítások után frissíteni kell.

## Fejlesztés és csomagolás

A repó gyökerében találhatók a telepíthető plugin fájljai. A tools/build.py csak az engedélyezett kiadási fájlokból készít napi-idezet-elementor gyökérmappájú ZIP-et:

~~~sh
python3 tools/build.py
~~~

Az integrációs ellenőrzés külön, eldobható WordPress-telepítésben futtatható, aktív Elementor és e plugin mellett. Tesztidézeteket és tesztfelhasználót hoz létre, ezért éles oldalon ne futtasd:

~~~sh
wp eval-file tests/integration.php
wp plugin check napi-idezet-elementor
~~~

A WordPress.org leírása a readme.txt, a licenc a LICENSE fájlban található. A Plugin Check eredménye nem helyettesíti a WordPress.org kézi felülvizsgálatát. A GitHub-feltöltés nem jelent automatikus WordPress.org-publikálást.

Licenc: GPL-2.0-or-later.

## Ellenőrzött kiadás: 1.1.0

- WordPress 7.1.5, Elementor 4.4.1, PHP 8.4.8, külön tesztadatbázisban.
- Valódi adatbázisos integrációs teszt: létrehozás, szerkesztés, jogosulatlan kérés és hibás nonce elutasítása, archiválás az eredeti adatok megőrzésével.
- Elementor-widget regisztrációja, vezérlői és szerveroldali HTML-kimenete ellenőrizve.
- Hivatalos Plugin Check, futásidejű ellenőrzésekkel: nincs jelzett hiba.
- Böngészős vizuális ellenőrzés és a deklarált legkisebb verziók külön futtatása nem történt.

Az 1.1.2-es verzió alapértelmezett kezelőfelülete angol, és fordítható marad. A feltöltött idézetek nyelve és tartalma nem változik.
