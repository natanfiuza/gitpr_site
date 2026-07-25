# Menu: Technical Documentation Subdivision

**Date:** 2026-07-24
**Branch:** develop_natan

## Summary

Added a visual section header in the sidebar navigation to separate the main documentation pages from the technical/advanced documentation (the `docs/` pages prefixed with ▸).

## Changes

### 1. `public/content/menu.json`

Added a `{"title": "...", "type": "section"}` item before the first ▸-prefixed doc entry in each language array:

| Language | Section Header Title |
|----------|---------------------|
| `en` | Technical Documentation |
| `pt_br` | Documentação Técnica |
| `pt_pt` | Documentação Técnica |
| `fr` | Documentation Technique |
| `es` | Documentación Técnica |

### 2. `resources/js/Pages/DocsLayout.vue`

Updated the sidebar `<nav>` rendering to check `item.type`:
- Items with `"type": "section"` render as a non-clickable, uppercase heading with `text-gitpr_cyan_dark` styling, with top margin (`mt-6`) separating the section from the main pages
- Regular items render as `<Link>` components (unchanged behavior)
- Key on the `<template>` uses `item.path ?? item.title` since section items lack `path`

### 3. `app/Http/Controllers/DocsController.php`

Both `show_document()` and `search_content()` loops skip items with `"type": "section"` to avoid:
- Incorrect title matches (section items would never match a page slug, but skipping them is cleaner)
- PHP warnings from accessing undefined `$item['path']` in the search loop

### Design Decision

Used a flat `type` field approach rather than nested arrays to keep backward compatibility and minimize changes — the JSON structure stays a flat array, only one extra field on section items. The Vue component handles the conditional rendering with a simple `v-if`/`v-else`.
