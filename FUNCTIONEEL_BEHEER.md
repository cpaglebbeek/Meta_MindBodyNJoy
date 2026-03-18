# Functioneel Beheer — MindBodyNJoy

> Taken en procedures voor het functioneel beheer van mindbodynjoy.nl.
> Inhoud per taak wordt stapsgewijs uitgebreid.

---

## Architectuur — Content Single Point of Truth

Alle dynamische functionele content leeft in één JSON bestand: `content.json`.

```
[Beheer interface]  ──GET──▶  content.json  (leest actieve versie)
[Beheer interface]  ──POST──▶ save.php      (schrijft nieuwe versie)
[Website]           ──GET──▶  content.json  (toont actieve versie via JS)
```

- **`content.json`** — single point of truth, gehost op Hostinger onder `/beheer/`
- **`save.php`** — write endpoint, beveiligd met token, beheert versies (max 5 per veld)
- **`index.html`** — beheer interface, geen technische kennis vereist
- **`MINDBODYNJOY_CONTENT.json`** — bronkopie in GitHub repo (Meta_MindBodyNJoy)

### Versielogica
- Bij opslaan: nieuwe versie bovenaan, max 5 versies bewaard per veld
- Activeren: elke versie kan live gezet worden zonder opnieuw op te slaan
- Website toont altijd de `active_index` versie

---

## FB-001 · Aanpassen missie, visie en/of strategie tekst

### Beschrijving
Aanpassen van de missie-, visie- en/of strategietekst bij **alle uitingen** hiervan op de website, zodat de boodschap consistent blijft op elke plek waar deze teksten verschijnen.

### Trigger
- De missie, visie of strategie van MindBodyNJoy wijzigt inhoudelijk.
- Een tekstuele herformulering wordt gewenst (toon, stijl, precisie).

### Scope
Alle pagina's en secties op mindbodynjoy.nl waar missie, visie of strategie tekst voorkomt.
*(Volledige inventory nog in kaart te brengen — zie stap 1 hieronder.)*

### Procedure
1. **Inventory:** Breng alle uitingen in kaart — welke pagina's / secties / widgets bevatten de te wijzigen tekst.
2. **Nieuw tekstvoorstel:** Stel de nieuwe tekst op en leg deze ter akkoord voor.
3. **WhatIf analyse:** Benoem exact welke uitingen worden aangepast en in welke volgorde.
4. **Akkoord:** Wacht op bevestiging vóór uitvoering.
5. **Doorvoeren:** Pas de tekst aan in WordPress via Elementor voor elke geïdentificeerde uiting.
6. **Verificatie:** Controleer alle aangepaste pagina's visueel in de browser.
7. **Vastleggen:** Update relevante bestanden (bijv. `informatie.xlsx` als de tekst ook in offline materialen staat).
8. **Git sync:** `git commit` + `git push` in MindBodyNJoy repo met beschrijving van de wijziging.

### Afhankelijkheden
- Toegang tot WordPress dashboard (Hostinger)
- Toegang tot Elementor page builder
- Backup van de website vóór doorvoeren (aanbevolen)

### Status
- Inventory uitingen: **nog te doen**
- Procedure: **gedefinieerd**

---
