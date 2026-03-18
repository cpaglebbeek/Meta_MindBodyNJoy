# Technisch Beheer — MindBodyNJoy

> Taken en procedures voor het technisch beheer van de MindBodyNJoy infrastructuur.
> Inhoud per taak wordt stapsgewijs uitgebreid.

---

## TB-001 · SSH key instellen voor Hostinger uploads

### Beschrijving
Eenmalige configuratie van een SSH key tussen deze Mac en de Hostinger server, zodat bestanden vanuit Claude Code direct en wachtwoordloos geüpload kunnen worden via `scp` of `rsync`.

### Trigger
- Eerste keer bestanden uploaden naar Hostinger vanuit Claude Code.
- Wachtwoord-authenticatie vervangen door veiligere key-authenticatie.

### Benodigdheden
- Toegang tot **hPanel** (hostinger.com → inloggen)
- SSH activeren in hPanel: **Geavanceerd → SSH-toegang**
- Noteer: **hostname**, **poort** (standaard `65002`), **gebruikersnaam**

### Procedure

**Stap 1 — SSH key aanmaken op de Mac** *(eenmalig)*
```bash
ssh-keygen -t ed25519 -C "mindbodynjoy-hostinger" -f ~/.ssh/mindbodynjoy_hostinger
```
Geeft twee bestanden:
- `~/.ssh/mindbodynjoy_hostinger` — privésleutel (nooit delen)
- `~/.ssh/mindbodynjoy_hostinger.pub` — publieke sleutel (naar Hostinger uploaden)

**Stap 2 — Publieke key registreren in hPanel**
1. Open `~/.ssh/mindbodynjoy_hostinger.pub` en kopieer de inhoud
2. Ga naar hPanel → **Geavanceerd → SSH-toegang → SSH-sleutels beheren**
3. Plak de publieke sleutel en sla op

**Stap 3 — SSH config instellen op de Mac** *(eenmalig)*
Voeg toe aan `~/.ssh/config`:
```
Host mindbodynjoy
    HostName     [HOSTNAME_UIT_HPANEL]
    Port         65002
    User         [GEBRUIKERSNAAM_UIT_HPANEL]
    IdentityFile ~/.ssh/mindbodynjoy_hostinger
```

**Stap 4 — Verbinding testen**
```bash
ssh mindbodynjoy
```
Verwacht resultaat: verbonden zonder wachtwoordprompt.

**Stap 5 — Bestand uploaden (na succesvolle test)**
```bash
# Enkel bestand
scp pad/naar/bestand.html mindbodynjoy:/public_html/beheer/

# Hele map syncen
rsync -avz pad/naar/map/ mindbodynjoy:/public_html/beheer/
```

### Afhankelijkheden
- hPanel SSH-toegang moet actief zijn (controleer onder Geavanceerd)
- Hostname, poort en gebruikersnaam ophalen uit hPanel vóór uitvoering

### Status
- SSH gegevens uit hPanel: ✅ `92.113.19.221:65002` / `u753337840`
- Key aangemaakt: ✅ `~/.ssh/mindbodynjoy_hostinger` (ED25519)
- Config ingesteld: ✅ `Host mindbodynjoy` in `~/.ssh/config`
- Verbinding getest: ✅ wachtwoordloos verbonden op 2026-03-18
- Beheer interface geüpload: ✅ `https://mindbodynjoy.nl/beheer/`

### Upload commando (hergebruik)
```bash
rsync -avz -e "ssh -p 65002 -i ~/.ssh/mindbodynjoy_hostinger" \
  /Users/christian/Documents/Gemini_Projects/Meta_MindBodyNJoy/beheer/ \
  u753337840@92.113.19.221:~/domains/mindbodynjoy.nl/public_html/beheer/
```

---

## TB-002 · Staging omgeving — staging.mindbodynjoy.nl

### Beschrijving
Staging subdomain ingericht zodat wijzigingen getest kunnen worden vóór live publicatie op mindbodynjoy.nl.

### Architectuur
```
staging.mindbodynjoy.nl  ─┐
                           ├──▶  ~/domains/mindbodynjoy.nl/public_html/staging/
mindbodynjoy.nl/staging   ─┘
```
Beide URLs serveren dezelfde map — staging bereikbaar via subdomain én via pad.

### Technische details
| Gegeven | Waarde |
|---------|--------|
| Subdomain | `staging.mindbodynjoy.nl` |
| Document root | `/home/u753337840/domains/mindbodynjoy.nl/public_html/staging/` |
| DNS | ALIAS `staging` → `staging.mindbodynjoy.nl.cdn.hstgr.net.` (via hPanel) |
| SSL | Automatisch via Hostinger CDN |
| Alternatief pad | `https://mindbodynjoy.nl/staging/` |

### Deploy naar staging
```bash
rsync -avz -e "ssh -p 65002 -i ~/.ssh/mindbodynjoy_hostinger" \
  /pad/naar/lokale/staging/ \
  u753337840@92.113.19.221:~/domains/mindbodynjoy.nl/public_html/staging/
```

### Status
- Subdomain aangemaakt in hPanel: ✅ 2026-03-18
- DNS ALIAS record: ✅ aanwezig
- SSL: ✅ actief via Hostinger CDN
- Document root: ✅ `public_html/staging/`
- Staging bereikbaar: ✅ HTTP 200 op beide URLs

---

## TB-003 · Deploy beheer interface — productie + staging

### Beschrijving
Wanneer de beheer interface (`index.html`, `save.php`, `server.js`) wordt bijgewerkt, wordt deze **altijd gelijktijdig naar productie én staging gedeployed**. Versienummer wordt vóór de deploy opgehoogd in `index.html`.

### Trigger
- Nieuwe content-groepen toegevoegd (G2–G9 uitrol)
- Bugfix of UX-aanpassing in de beheer interface
- Wijziging in `save.php` of `server.js`

### Procedure

**Stap 1 — Versienummer ophogen in `index.html`**
```js
const BEHEER_VERSION = 'v1.x.x';  // ← ophogen conform semantisch schema
```
| Wijziging | Increment |
|-----------|-----------|
| Nieuwe content-groep (G2–G9) | +0.1.0 |
| Minor fix / UX-tweak | +0.0.1 |
| Major herontwerp beheer interface | +1.0.0 |

**Stap 2 — Deploy naar productie**
```bash
rsync -avz -e "ssh -p 65002 -i ~/.ssh/mindbodynjoy_hostinger" \
  /Users/christian/Documents/Gemini_Projects/Meta_MindBodyNJoy/beheer/ \
  u753337840@92.113.19.221:~/domains/mindbodynjoy.nl/public_html/beheer/
```

**Stap 3 — Deploy naar staging**
```bash
rsync -avz -e "ssh -p 65002 -i ~/.ssh/mindbodynjoy_hostinger" \
  /Users/christian/Documents/Gemini_Projects/Meta_MindBodyNJoy/beheer/ \
  u753337840@92.113.19.221:~/domains/mindbodynjoy.nl/public_html/staging/beheer/
```

**Stap 4 — Verificatie**
- https://mindbodynjoy.nl/beheer/ → versienummer in footer/header controleren
- https://staging.mindbodynjoy.nl/beheer/ → idem

**Stap 5 — Git commit + push**
```bash
git add beheer/ && git commit -m "deploy: beheer interface vX.X.X — [omschrijving]"
git push
```

### Status
- TB-003 procedure: ✅ gedefinieerd
- Eerste deploy naar staging: zie TB-002 uitvoering

---
