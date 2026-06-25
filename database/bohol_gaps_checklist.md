# Bohol — What's Lacking (barangay-level), per municipality

Source for official counts: PhilAtlas. "have" = distinct barangays currently in our DB
(Poblacion row-splits collapsed). Get the missing ones from **BIR RDO 84 (Tagbilaran/Bohol)**.

## ✅ COMPLETE (17) — no action needed
Alburquerque, Antequera, Baclayon, Balilihan, Calape, Candijay, Catigbian, Corella, Cortes,
Dauis, Panglao, San Miguel, Tagbilaran City, Talibon, Tubigon, Ubay  (+ Loon is *partial*, see below)

## 🔴 NEED EVERY BARANGAY (we only have the town center / Poblacion)
| Municipality | have | need | missing |
|---|---|---|---|
| Buenavista | 1 | 35 | 34 |
| Dimiao | 2 | 35 | 33 |
| Jagna | 1 | 33 | 32 |
| Carmen | 2 | 29 | 27 |
| Sagbayan | 1 | 24 | 23 |
| Sierra Bullones | 1 | 22 | 21 |
| Maribojoc | 1 | 22 | 21 |
| Pilar | 1 | 21 | 20 |
| Trinidad | 1 | 20 | 19 |
| Bilar | 1 | 19 | 18 |
| Lila | 1 | 18 | 17 |
| Danao | 1 | 17 | 16 |
| Anda | 1 | 16 | 15 |
| Alicia | 1 | 15 | 14 |
| Sevilla | 1 | 13 | 12 |
| San Isidro | 1 | 12 | 11 |
| Sikatuna | 2 | 10 | 8 |

## 🟠 PARTIAL (have some, missing many)
| Municipality | have | need | missing |
|---|---|---|---|
| Inabanga | 7 | 50 | 43 |
| Valencia | 3 | 35 | 32 |
| Loon | 37 | 67 | 30 |  ← was truncated by the sheet's 2,000-row cap
| Garcia Hernandez | 3 | 30 | 27 |
| Loboc | 5 | 28 | 23 |
| Loay | 4 | 24 | 20 |
| Clarin | 4 | 24 | 20 |
| Mabini | 3 | 22 | 19 |
| Duero | 4 | 21 | 17 |
| Getafe | 9 | 24 | 15 |
| Pres. Carlos P. Garcia | 11 | 23 | 12 |
| Guindulman | 7 | 19 | 12 |
| Batuan | 4 | 15 | 11 |
| Bien Unido | 9 | 15 | 6 |
| Dagohoy | 10 | 15 | 5 |

**TOTAL missing: ~633 barangays across 31 municipalities.**

## How to fill it
One document covers ALL of these: the official **BIR RDO 84 (Tagbilaran/Bohol)** revised
schedule of zonal values (Revenue Region 13 - Cebu City), effective 2024-05-25, from
bir.gov.ph. It lists every municipality → every barangay. Get that file → drop in
`ZONAL VALUES/_DROP_PDFS_HERE` → I parse it and fill all the gaps.
