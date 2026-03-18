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

## Logische Groeperingen — Functionele Content (G1–G9)

Alle content op mindbodynjoy.nl is ingedeeld in negen logische groepen voor functioneel beheer. Elke groep komt overeen met een functioneel onderdeel van de website.

| ID | Groep | Omschrijving | Status in beheer interface |
|----|-------|-------------|---------------------------|
| **G1** | Brand Identity | Missie, visie, strategie | ✅ Geïmplementeerd |
| **G2** | Homepage hero | Kopregel, subtitel, CTA-knop tekst | ✅ Geïmplementeerd |
| **G3** | Diensten / Aanbod | Titel, beschrijving, prijs per dienst | ✅ Geïmplementeerd |
| **G4** | Over ons | Introductietekst, bio, teamleden | ✅ Geïmplementeerd |
| **G5** | Shop / Producten | Productteksten, categoriebeschrijvingen | ✅ Geïmplementeerd |
| **G6** | Contact | Adres, telefoonnummer, openingstijden, intro-tekst | ✅ Geïmplementeerd |
| **G7** | Footer | Copyright-tekst, links, social media verwijzingen | ✅ Geïmplementeerd |
| **G8** | SEO-teksten | Meta titles, meta descriptions per pagina | ✅ Geïmplementeerd |
| **G9** | Globale elementen | Navigatielabels, vaste CTA-teksten, cookietekst | ✅ Geïmplementeerd |

> **Noot (2026-03-18):** Homepage (ID 57) bevat reeds MindBodyNJoy-eigen content. Over ons (ID 47) en Contact (ID 42) bevatten nog Elementor/Juiceito demo-content — inventarisatie G4 en G6 volgt na opschonen.

---

## Elementor Coexistentie — Architectuurstrategie

### Uitgangspunt
De website draait op WordPress + Elementor. Elementor beheert de **visuele lay-out, effecten en structuur** van pagina's. De beheer interface beheert **tekst-content** als single point of truth via `content.json`.

### Hoe coexistentie werkt

```
[Elementor]          → lay-out, animaties, widget-structuur (onaangeroerd)
[content.json]       → tekst per functioneel veld (missie, visie, etc.)
[Beheer interface]   → lezen / schrijven van content.json
[Website (JS)]       → injecteert actieve versie uit content.json in de pagina
```

- Elementor-wijzigingen **blokkeren de beheer interface niet** — ze raken andere lagen.
- Beheer interface-wijzigingen **blokkeren Elementor niet** — content.json is orthogonaal aan Elementor layout-data (`_elementor_data` in wp_postmeta).

### ACF Bridge (geplande stap)
**Advanced Custom Fields (ACF)** wordt ingezet als brug zodra directe tekstvervanging onvoldoende is:
1. ACF-velden worden aangemaakt per content-groep (G1–G9)
2. Elementor widgets lezen ACF-veld-waarden (dynamic content koppeling)
3. Beheer interface schrijft naar ACF-velden via WordPress REST API (in plaats van / naast content.json)
4. Dit maakt Elementor-bewerking én beheer interface-bewerking simultaan mogelijk zonder conflict

### Toekomstige afweging: Elementor loslaten
Op langere termijn kan Elementor worden losgelaten als betaald onderdeel, **mits**:
- Alle pagina's volledig beheerd worden via de eigen beheer interface
- Visuele lay-out gecodeerd is in static HTML/CSS (buiten Elementor)
- ACF-brug volledig operationeel is als content-laag

**Beslismoment:** Na implementatie van G1–G9 in beheer interface + ACF bridge PoC.

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
