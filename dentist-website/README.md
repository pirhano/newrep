# Cabinet Dentaire Dr Rhomija Nabil: website rebuild

A rebuild of the clinic's WordPress site (`lavender-chamois-170103.hostingersite.com`), focused on two jobs:
**getting patients to book**, and **getting found on Google** by people searching for a dentist in Témara.

| Desktop | Mobile |
| --- | --- |
| ![Home page](docs/apercu-accueil.jpg) | ![Mobile](docs/apercu-mobile.jpg) |

The site is in French and runs on the existing WordPress install, so the clinic keeps its hosting, its plugins and its admin.
There is **no team or staff section**: the only person named is Dr Rhomija Nabil, as part of the clinic's name, as on the wall sign.
It consists of two pieces:

| Folder | What it is |
| --- | --- |
| `wordpress/plugins/rhomija-booking/` | **Booking plugin.** Shows the clinic's free slots in real time. It records booking requests, emails the clinic and the patient, and gives the clinic a WordPress screen to confirm or cancel (by e-mail or WhatsApp). |
| `wordpress/themes/rhomija/` | **Custom theme.** A fast, mobile-first theme with no page builder. It has a page per treatment for local SEO, the clinic's real photos, and a one-click installer that creates all the pages and menus. |
| `brand/` | The new logo (SVG + PNG + favicons). |
| `dist/` | **Ready-to-upload zips.** Rebuild them with `sh tools/build.sh`. |
| `docs/` | Screenshots of the rebuilt site. |

---

## Why the logo was redone

The media library held four logos: three AI-generated variants (one spells "RHOMIJJA") and a clip-art wordmark.
None matched the clinic's real **wall sign**, the one patients see at reception: a blue/teal circle with a white tooth,
and "CABINET DENTAIRE / DR. RHOMIJA NABIL" in a geometric typeface.

The new logo is a clean vector redraw of that sign: same circle, same blue (`#1C6DB5`) and teal (`#1FA39A`), same name layout.
The website, the sign and the e-mails now share one identity.

![Logo set](docs/logo-planche.jpg)

Files in `brand/`:
- `logo-horizontal.svg`: header
- `logo-horizontal-white.svg`: dark backgrounds
- `logo-stacked.svg`: square formats such as social profiles
- `logo-mark.svg`: icon only
- `png/`: PNG exports at several sizes

The small "CABINET DENTAIRE" line uses a darker teal (`#0F766E`), so it stays readable at header size (5.5:1 on white). The emblem keeps the sign's teal.
Text is converted to outlines, so the files render the same everywhere.
To regenerate them, run `tools/build_logo.py` with the Montserrat SemiBold and Bold TTF files.

---

## Install on the Hostinger WordPress (about 10 minutes)

> Nothing below deletes content. The current Toothwise theme stays installed, so rolling back is one click
> (Apparence → Thèmes → Toothwise → Activer).

1. *(Recommended)* Take a backup: hPanel → Sites web → Sauvegardes.
2. **Extensions → Ajouter → Téléverser une extension** → `dist/rhomija-booking.zip` → *Installer* → *Activer*.
3. **Apparence → Thèmes → Ajouter → Téléverser un thème** → `dist/rhomija-theme.zip` → *Installer* → *Activer*.
4. **Apparence → Installation du site → « Installer le site ».** This creates:
   - **Pages:** Accueil, Nos soins plus 9 treatment pages, Le cabinet, Prendre rendez-vous, Contact & accès, Conseils dentaires, Mentions légales & confidentialité
   - **3 advice articles**
   - **Menus:** main menu and footer menu
   - **Settings:** static home page, title *Cabinet Dentaire Dr Rhomija Nabil* (replaces "Rhoija Smile"), Morocco time zone

   Pages that already exist are never overwritten, so the installer is safe to run again.
5. **Rendez-vous → Réglages:** check the phone number, **add the WhatsApp number**, and set opening hours and closed days (holidays, leave).
6. **Apparence → Personnaliser → « Cabinet : textes & liens »:** paste the Google Maps share link, the "leave a Google review" link, and the Facebook and Instagram links.
7. **LiteSpeed Cache → Purger tout.**
8. Make a test booking from a phone, check the e-mail arrives at `cabinetrhomija@gmail.com`, then confirm or cancel it from **Rendez-vous**.

Clinic details (phone, WhatsApp, e-mail, address, hours) are edited **in one place** (Rendez-vous → Réglages) and update everywhere:
header, footer, contact page, booking e-mails and Google structured data.

---

## How booking works

**For the patient:**
1. Pick a reason for the visit, then a day and a free time slot (only real free slots are shown).
2. Enter name, phone and, optionally, an e-mail.
3. Submit. On the success screen they can add the appointment to their calendar or ping the clinic on WhatsApp.

Treatment pages link to the form with that treatment already selected.

![Booking and e-mails](docs/apercu-reservation-emails.jpg)

**For the clinic** (WordPress → **Rendez-vous**):
- **Views:** *À venir*, *À confirmer* (also shown as a counter on the menu), *Aujourd'hui*, *Passés*, *Annulés*.
- **One-click actions:** **Confirmer** · **Honoré** · **Annuler** · **WhatsApp** (opens WhatsApp with a pre-written confirmation).
- **Confirmation e-mail:** confirming sends the patient an e-mail with a calendar invite (`.ics`), if they gave an address.
- **Phone bookings:** add them with *Ajouter un rendez-vous*. They block the slot like online ones.
- **Dashboard:** a *Prochains rendez-vous* widget is pinned at the top.
- **Search:** finds patients by name, phone or e-mail.

![Admin list](docs/apercu-admin-rendez-vous.jpg)

**Rules** (in Réglages):
- Slot length: 30 minutes by default.
- Patients per slot: 1.
- Minimum notice: 2 hours.
- Booking window: 60 days ahead.
- Closed dates: one per line.

**Protection:**
- Honeypot field, a minimum time to fill the form, and a rate limit per visitor.
- Slot availability is re-checked at submit time.
- One active request per phone number per day.
- The booking API sends no-cache headers, so Hostinger's LiteSpeed cache can't serve stale availability.

**Consent:** the form asks for consent under **loi 09-08** and links to the privacy page.

---

## Marketing and SEO built in

- **Treatment pages:** 9 pages with real, careful copy (consultation, scaling, cavities and root canals, extractions, implants, prostheses, aesthetics and whitening, children, emergencies). They target searches like "implant dentaire Témara".
- **Structured data:** schema.org `Dentist` data on every page, with address, phone, opening hours, treatments, a booking action and areas served.
- **Meta tags:** meta description and Open Graph sharing image (`assets/img/partage-social.jpg`). When All in One SEO is active (it is on the live site), it keeps control of these and the theme only supplies defaults.
- **Content marketing:** 3 starter articles. Add more under *Articles* with the category *Conseils dentaires*.
- **Conversion features:**
  - Next free slot shown in the hero.
  - Sticky mobile bar (Appeler / WhatsApp / Rendez-vous).
  - Click-to-call everywhere.
  - Booking form embedded on the home page.
- **Analytics events** work with Google Site Kit (already installed), GTM and the Meta Pixel. In GA4, mark `generate_lead` as a key event:
  - `generate_lead` (booking sent)
  - `click_call`
  - `click_whatsapp`
  - `click_directions`
  - `click_booking`
  - `click_review`
- **Speed:**
  - No jQuery and no page builder on the front end.
  - Self-hosted fonts.
  - WebP photos (about 330 KB for all five clinic photos at full size, about 125 KB for the mobile versions).
  - About 76 KB of HTML on the home page, with 2 scripts and 2 stylesheets.
- **Accessibility:**
  - **Text contrast meets WCAG AA (at least 4.5:1) everywhere.** This was checked with the axe-core tool on every page (desktop and mobile), every booking step, the mobile menu, the four e-mail types and the admin list.
  - Text over gradients was checked by hand at its worst point.
  - Skip link, keyboard-usable menus and booking calendar, ARIA state on slots, visible focus, and reduced-motion support.
  - `color-scheme: light`, so phones in forced dark mode don't recolour the text.

---

## To confirm with Dr Rhomija before going live

I wrote the copy from the existing site, the clinic photos and a public directory listing. A few things need the doctor's confirmation:

- [ ] **WhatsApp number.** Not found anywhere. Until it is entered, the WhatsApp buttons are hidden.
- [ ] **Opening hours.** The current site says *Lun–Samedi 9:00–17:00*. Is there a lunch break, or a shorter Saturday?
- [ ] **Treatments offered.** Confirm implants, prostheses, whitening and veneers, and children's care. Delete a page if a treatment isn't offered. Orthodontics was left out on purpose; add it if offered.
- [ ] **Claims made on the site:**
  - On-site panoramic X-ray (visible in the photos)
  - Dedicated sterilisation room (visible in the photos)
  - Written quote before major treatment
  - Reimbursement paperwork (AMO / mutuelle)
  - Emergencies seen as quickly as possible
- [ ] **Exact Google Maps pin.** Paste the share link in Personnaliser. The embedded map currently uses an address search.
- [ ] **CNDP.** Online booking processes health-related personal data. Check the clinic's CNDP declaration or authorisation under loi 09-08 and add its number to *Mentions légales & confidentialité*.
- [ ] Delete WordPress's sample post *Bonjour tout le monde !* and sample page if they exist.
- [ ] **Domain.** `dr-rhomija-nabil.ma` does not resolve yet. Once it points to Hostinger, update *Réglages → Général*.

---

## What I could and couldn't access

- **Hostinger AI Builder (first site):** the login page is behind a Cloudflare bot check, so I couldn't open that project.
  The rebuild is based on the WordPress site, the clinic's own photos (from the media library) and its public listing.
  If you export or publish the AI-builder version, its content can be merged in.
- **WordPress admin (second site):** the password provided for the new admin account was rejected (tried with both its username and its e-mail).
  **Nothing was changed on the live site.** Everything was built and tested on a local copy of WordPress 7.1.3, the same version as the live site.

---

## Development

```sh
sh tools/build.sh            # rebuild dist/*.zip
python3 tools/build_logo.py Montserrat-SemiBold.ttf Montserrat-Bold.ttf brand   # regenerate logos (needs fonttools, uharfbuzz, cairosvg)
```

- Theme text domain: `rhomija`. Plugin text domain: `rhomija-booking`. Requires PHP 7.4+ and WordPress 6.4+.
- WP-CLI: `wp rhomija install` runs the same installer as the admin button.
- **Booking REST API** (`/wp-json/rhomija-booking/v1/`):
  - `GET month?month=YYYY-MM`
  - `GET slots?date=YYYY-MM-DD`
  - `GET next`
  - `POST appointments`
- **Plugin hooks:** `rb_new_appointment` and `rb_status_changed`.
- **Tested locally:**
  - Fresh install from the zips; every page returns 200 with no PHP notices.
  - Full booking flow on mobile and desktop, plus the validation, double-booking, spam and closed-day cases.
  - Admin confirm action, and the three e-mails it triggers.
  - Theme works with the plugin deactivated; the booking page then shows the phone number instead.
