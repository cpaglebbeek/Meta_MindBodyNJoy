# ****META_MINDBODYNJOY****

## Project Identiteit
- **Rol:** Master/Regie — centrale orchestratie voor het volledige MindBodyNJoy ecosysteem.
- **GitHub:** `https://github.com/cpaglebbeek/Meta_MindBodyNJoy.git` (branch: `main`)
- **Lokaal pad:** `/Users/christian/Documents/Gemini_Projects/Meta_MindBodyNJoy`
- **Source of Truth:** `META_MINDBODYNJOY_REPO.json`

## Ecosysteem Overzicht
| Project | Rol | Lokaal pad | GitHub |
|---------|-----|-----------|--------|
| **Meta_MindBodyNJoy** | Master / Regie | `.../Gemini_Projects/Meta_MindBodyNJoy` | `cpaglebbeek/Meta_MindBodyNJoy` |
| **MindBodyNJoy** | Website assets & content | `/Users/christian/Documents/MindBodyNJoy` | `cpaglebbeek/MindBodyNJoy` |

## Website (live)
- **URL:** https://mindbodynjoy.nl
- **Platform:** WordPress + Elementor v3.35.5 op Hostinger
- **E-commerce:** WooCommerce + WooCommerce Payments
- **Tech:** React fonts (DMSans, Roboto, Lato, Poppins), PWA-ready
- **Contact:** info@mindbodynjoy.nl | +31 (0)6 26 46 34 26

## Context-Aware Orchestration
- **Location Independence:** Leid altijd uit de context af welk deelproject actief is.
- **Routing:** Brand/strategie/cross-project assets → altijd in `Meta_MindBodyNJoy`. Platform-specifieke content → in het bijbehorende sub-project.
- **Auto Git Sync:** Na elke wijziging automatisch `git commit` + `git push` voor het gewijzigde project.
- **Dashboard:** `dashboard_info.html` MOET bijgewerkt worden na elke versie-verhoging van elk ecosysteem-project.
- **Expliciete Context:** Elke reactie begint met: `****META_MINDBODYNJOY****`

## Feature & Bugfix Protocol (Color-Coded)
**Nieuwe Feature / Update:**
- **Groen:** Minor (content, afbeelding, tekst) → versie +0.0.1
- **Oranje:** Structuurwijziging (nieuwe pagina, categorie, layout) → versie +0.1.0
- **Rood:** Major (redesign, platform-migratie, nieuwe sub-project) → versie +1.0.0

**Bugfix:**
- **Groen:** Snel herstel (tikfout, verkeerde afbeelding)
- **Geel:** Functioneel probleem (formulier, WooCommerce flow, plugin conflict)
- **Rood:** Platform/security probleem (hosting, WordPress core, breach)
- **Loop:** Compleet nieuwe invalshoek

**Root Cause Analysis (verplicht bij bugs):** Functioneel + Technisch + Architectonisch niveau.

## Build / Deploy Mandate
- **WhatIf Protocol:** Beschrijf vóór elke live wijziging exact wat er verandert. Akkoord vragen daarna.
- **Geen automatische deploys:** Wijzigingen aan live WordPress vereisen altijd expliciete bevestiging.
- **Backup Reminder:** Wijs op WordPress backup vóór elke structurele wijziging.
- **Change Detection:** Controleer `git status` in het relevante sub-project vóór sync.

## Versioning Mandate
- Versienummer bijwerken in `META_MINDBODYNJOY_REPO.json` en `versions.json` vóór elke sync.
- **Thema:** Wellness / aromatherapie begrippen (essentiële oliën, kruiden, edelstenen)
- **Huidige versie:** v1.0.0 — Lavendel

## Semantische Versioning
| Impact | Increment | Kleurcode |
|--------|-----------|-----------|
| Minor content | +0.0.1 | Groen |
| Structuurwijziging | +0.1.0 | Oranje |
| Major redesign | +1.0.0 | Rood |

## Beheerdomeinen
De volgende taakvelden zijn erkend binnen het MindBodyNJoy ecosysteem. Inhoud per domein wordt later uitgewerkt.

| Domein | Status |
|--------|--------|
| **Functioneel Beheer** | Zie `FUNCTIONEEL_BEHEER.md` |
| **Technisch Beheer** | Zie `TECHNISCH_BEHEER.md` |
| **Functioneel Ontwerp** | *(nader uit te werken)* |
| **Technisch Ontwerp** | *(nader uit te werken)* |

## Vastleggingsprotocol (Impliciet → Expliciet)
- Alles wat gevraagd wordt of impliciet overeengekomen is (door akkoord van de gebruiker) wordt ALTIJD fysiek vastgelegd — nooit alleen in het geheugen van de AI.
- **Vóór vastleggen:** expliciet benoemen wát er vastgelegd wordt en wáár, daarna akkoord vragen.
- **Meta-niveau:** afspraken, protocollen, ecosysteemregels → `CLAUDE.md`, `META_MINDBODYNJOY_REPO.json`, of `versions.json` in dit metaproject.
- **Fysiek niveau:** project-specifieke afspraken → `CLAUDE.md` of relevante config/data bestanden in het betreffende sub-project.
- Na vastleggen: `git commit` + `git push` voor het gewijzigde project.

## "Over en uit" Protocol
1. Sla sessie-context op in `META_MINDBODYNJOY_REPO.json` en dit bestand.
2. `git add` + `git commit` met beschrijvende boodschap.
3. `git push` naar GitHub voor elk gewijzigd project.
