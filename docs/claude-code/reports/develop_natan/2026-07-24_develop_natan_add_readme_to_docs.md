# Add README.md to Technical Documentation Section

**Date:** 2026-07-24
**Branch:** develop_natan

## Summary

Copied the GitPR CLI `README.md` from the main Python repository into `public/content/docs/` as the first item in the newly created "Technical Documentation" sidebar section, with full translations in all 5 supported languages.

## Changes

### 1. `public/content/docs/readme.md` (new)

English base — copied verbatim from `C:\Users\nataniel\projetos\python\gitpr\README.md` (328 lines, 21 KB). The official GitPR CLI README covering installation, usage, features, multi-model architecture, MCP integration, linter system, i18n, and more.

### 2. Translated versions (new)

Created by parallel translation agents preserving all Markdown formatting, HTML, code blocks, tables, URLs, technical terms, and emojis:

| File | Language | Size |
|------|----------|------|
| `readme.pt_br.md` | Brazilian Portuguese | 23 KB |
| `readme.pt_pt.md` | European Portuguese | 23 KB |
| `readme.fr.md` | French | 25 KB |
| `readme.es.md` | Spanish | 23 KB |

File naming follows the controller convention: `{name}.{lang}.md` (e.g., `readme.pt_br.md`), matching the language codes used by `LanguageSelector.vue` (`en`, `pt_br`, `pt_pt`, `fr`, `es`).

### 3. `public/content/menu.json` (modified)

Added `{ "title": "▸ README", "path": "docs/readme" }` as the first item immediately after the "Technical Documentation" section header in all 5 language arrays (`en`, `pt_br`, `pt_pt`, `fr`, `es`).
