---
name: mobile-first-layout
description: >-
  Design and build mobile-first layouts, UI components, and responsive screens
  for the wedding planning platform using the Terracota and Areia palette. Use
  when the user asks to create layouts, design systems, UI guidelines, or
  mobile-first screens for the wedding system.
---

# Mobile-First Layout

## Summary

Guide mobile-first layout design and UI implementation for the wedding planning platform using the Terracota and Areia visual identity. Focus on clean surfaces, clear financial and supplier hierarchy, and frictionless touch targets for couples and professionals.

## When to Use

Use this skill when:

- Designing or generating mobile-first screens, dashboards, or UI components for the wedding platform
- Applying the Terracota and Areia color tokens and typography rules to frontend code or layout specs
- Adapting wedding flows (budget, suppliers, Pinterest inspirations, gift registry, or vendor views) across mobile and desktop viewports

## Steps

1. **Start mobile-first:** Structure every layout for small viewports first, placing primary actions and key metrics within easy thumb reach, then scale cleanly to tablet and desktop grids.
2. **Apply the Terracota and Areia palette:**
   - Background canvas: `#FBF9F7` (warm sand / linen)
   - Elevated surfaces and cards: `#FFFFFF`
   - Primary brand accent (buttons, active indicators, progress bars): `#C25E38` (terracota)
   - Primary ink and headings: `#2C2523` (deep espresso)
   - Secondary muted text: `#6B625E`
   - Subtle borders and dividers: `#EAE4DF`
   - Positive financial / gift status accent: `#5A8F7B` (sage green)
3. **Set typography hierarchy:** Pair a classic serif (`Lora` or `Playfair Display`) for headings and couple titles with a clean sans-serif (`Plus Jakarta Sans` or `Inter`) for UI labels, navigation, and tabular financial figures.
4. **Keep components clean and scannable:** Use rounded cards (`rounded-2xl`), generous spacing, and clear status badges instead of dense tables on mobile. Ensure interactive buttons and links have at least `44px` of touch height.
5. **Respect role clarity without promotional clutter in the dashboard:** Keep couple budgeting, supplier tracking, Pinterest board linking, and gift registry flows clean and focused on tasks, while clearly marking paid vendor perks (such as featured placement or unlocked Pinterest inspiration boards for decorators, makeup artists, and hair stylists) and ceremony coordinator supplier overviews. Never display promotional badges or text stating that the plan is "100% Gratuito" or "Sem mensalidades" inside the authenticated dashboard (reserve pricing/free-tier messaging for the landing page and registration screen).
6. **Use Toast notifications for completed actions:** Whenever the user completes an action (saving settings, regenerating tokens, adding/updating records, or checking off checklist items), present confirmation feedback via a temporary floating **Toast** positioned clear of the mobile bottom navigation bar, rather than displaying a persistent badge or inline confirmation banner.

## Gotchas

- Avoid heavy dark backgrounds or neon accents that clash with the warm, organic wedding aesthetic.
- Do not cram wide multi-column tables into narrow mobile viewports; stack into cards with concise labels and amounts.
- Keep naming neutral and consistent with the wedding platform context rather than reusing unrelated project terms.
- Do not wrap the landing page (`/`) or guest authentication pages (`/login`, `/cadastro`) in the authenticated dashboard layout, and do not add "100% Gratuito" labels inside the dashboard.
- Do not render static badges or inline banners to confirm completed actions; use temporary floating Toast notifications instead.

