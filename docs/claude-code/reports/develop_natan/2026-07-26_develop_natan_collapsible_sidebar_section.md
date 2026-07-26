# Collapsible Sidebar Section — "Documentação Técnica"

**Date:** 2026-07-26
**Branch:** develop_natan

## Summary

Made the "Documentação Técnica" section in the sidebar collapsible. Clicking the section header now toggles visibility of its subitems, with a chevron indicator and smooth rotation animation.

## Changes

### [DocsLayout.vue](resources/js/Pages/DocsLayout.vue)

**Template changes:**
- Replaced the flat `v-for` over `menu_items` with a grouped render using `menu_groups` computed property
- Section headers (items with `type: "section"`) are now clickable `<button>` elements
- Each section header has a chevron SVG icon that rotates 90° when expanded
- Subitems are wrapped in `v-if` conditional rendering, hidden when section is collapsed
- Top-level items (before any section) are always visible

**Script changes:**
- Added `computed` to Vue imports
- Added `expanded_sections` reactive ref to track collapse/expand state per section
- Added `menu_groups` computed property that groups flat `menu_items` into sections
- Added `toggle_section(title)` method to flip expand state
- Added `is_section_expanded(title)` with auto-expand logic:
  - On first access, auto-expands if the section contains the current page
  - Otherwise defaults to collapsed
  - After user interaction, respects their explicit choice

## Behavior

| Scenario | Behavior |
|----------|----------|
| Page load, section contains current page | Auto-expanded |
| Page load, section does not contain current page | Collapsed |
| Click section header | Toggles expand/collapse |
| Navigate to page within a collapsed section | Stays collapsed (respects user choice) |
| Navigate to page within an expanded section | Stays expanded |
| Language switch | New section titles re-evaluate auto-expand |

## Build

Assets rebuilt via `npm run build`. New chunk: `DocsLayout-DHp3IFIr.js`.
