# Autoverse visual system

<!-- impeccable:design-schema 1 -->

## Direction

A replacement visual world for Autoverse: a midnight studio and pit-lane editorial system. The page feels like a premium automotive journal at blue hour: deep graphite ground, electric blue signal lines, quiet neutrals, and large photographic crops when the product has an image. The first viewport proves the product mechanism by putting a vehicle story and the next action in the same composition.

The system borrows structural cues from automotive marques and editorial product launches observed in official Lamborghini, Porsche and Ferrari references: model-led hierarchy, sparse navigation, full-width visual moments and short confident calls to action. It does not copy their marks, words, assets or layouts.

## Materials

- **Ground:** `#070a0f` dark graphite with `#f5f7fb` light paper fallback.
- **Ink:** `#eef3fb` on dark, `#111827` on light.
- **Signal:** `#4d8dff` for action, links and the thin headlight signature.
- **Quiet:** `#9aa8bd` for supporting copy and metadata.
- **Rules:** low-contrast blue-gray hairlines, never orange.
- **Type:** `Space Grotesk` for display and labels, `Instrument Sans` for reading copy, both served by the existing project font workflow.
- **Shape:** restrained 2px–14px corners; panels feel like editorial plates, not floating glass cards.

## Composition

Use a wide asymmetric stage, one dominant subject, and a narrow supporting rail. Navigation is compact and explicit. Content sections alternate between dense information and intentional quiet. Cards are used for content relationships, not as a uniform grid around every sentence. Mobile collapses to a single reading rail with horizontal media overflow only when it helps exploration.

## Interaction

One authored signature interaction is the blue line sweep on the brand mark and primary action at hover/focus. It uses `cubic-bezier(.23,1,.32,1)` and remains under 300ms. Other motion is limited to opacity/translate transitions and respects reduced motion. Buttons compress to 0.97 on active; hover styling is enabled only for fine pointers.

## System tokens

The implementation lives in `resources/css/app.css` and keeps compatible `mc-*` classes while adding semantic `av-*` primitives for new surfaces. The current theme is controlled by Flux appearance and supports light, dark and system modes.

## Responsive contract

At 1440px the home hero keeps its text, signal mark and lead image within the first viewport. At 1024px the supporting rail moves below the lead. At 390px navigation becomes a menu, media crops become 4:3, controls stack and no copy is clipped.
