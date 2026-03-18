# TODO — Meta MindBodyNJoy

> Meta todo-lijst voor toekomstige uitwerking. Items worden hier geparkeerd en later opgepakt als taak in het juiste domeinbestand.

---

## Openstaande items

### [ ] Logische groeperingen G1–G9 uitrollen in beheer interface
**Prioriteit:** Hoog
**Context:** De groeperingen G1–G9 zijn gedefinieerd en vastgelegd in `FUNCTIONEEL_BEHEER.md`. G1 (Brand Identity: missie, visie, strategie) is geïmplementeerd. G2–G9 moeten nog worden uitgerold.

**Groeperingen:**
| ID | Groep | Status |
|----|-------|--------|
| G1 | Brand Identity (missie, visie, strategie) | ✅ Live |
| G2 | Homepage hero (kopregel, subtitel, CTA) | 🔲 Todo |
| G3 | Diensten / Aanbod | 🔲 Todo |
| G4 | Over ons | 🔲 Todo — pagina bevat nog demo-content (Juiceito) |
| G5 | Shop / Producten | 🔲 Todo |
| G6 | Contact | 🔲 Todo — pagina bevat nog demo-content (Juiceito) |
| G7 | Footer | 🔲 Todo |
| G8 | SEO-teksten (meta title/description per pagina) | 🔲 Todo |
| G9 | Globale elementen (navigatie, CTA, cookie) | 🔲 Todo |

**Vervolgstap per groep:**
1. Voeg velden toe aan `MINDBODYNJOY_CONTENT.json` (schema uitbreiding)
2. Upload bijgewerkte `content.json` naar productie én staging
3. Breid beheer interface (`index.html`) uit met nieuwe secties per groep
4. Upload bijgewerkte `index.html` naar productie én staging

**Domein:** Functioneel Beheer / Functioneel Ontwerp

---

### [ ] Over ons en Contact pagina's opschonen (Juiceito demo-content)
**Prioriteit:** Middel
**Context:** Pagina's "Over ons" (WP ID 47) en "Contact" (WP ID 42) bevatten nog Elementor/Juiceito demo-content. MindBodyNJoy-eigen content ontbreekt hier nog.
**Vervolgstap:** Nieuwe tekst aanleveren voor G4 en G6, vervolgens via Elementor of beheer interface doorvoeren.
**Domein:** Functioneel Beheer / Functioneel Ontwerp

---

### [ ] ACF Bridge PoC — Elementor ↔ Beheer Interface
**Prioriteit:** Laag (toekomstig)
**Context:** Geplande architectuurstap waarbij Advanced Custom Fields (ACF) als brug fungeert tussen de beheer interface en Elementor dynamic content. Zie `FUNCTIONEEL_BEHEER.md` → Elementor Coexistentie.
**Vervolgstap:** ACF plugin installeren, velden aanmaken per G1–G9, Elementor widgets koppelen via dynamic content.
**Domein:** Technisch Ontwerp / Technisch Beheer

---
