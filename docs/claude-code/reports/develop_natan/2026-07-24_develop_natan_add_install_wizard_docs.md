# Add Install Wizard Documentation

**Date:** 2026-07-24
**Branch:** develop_natan

## Summary

Added the `install-wizard.md` documentation (GitPR Interactive Setup Wizard `--install`) from the GitPR CLI repository to the website's Technical Documentation section, with all 5 supported languages.

## Changes

### 1. `public/content/docs/install-wizard.md` (new) + 4 translations

Copied from `C:\Users\nataniel\projetos\python\gitpr\docs\`:

| File | Language | Source |
|------|----------|--------|
| `install-wizard.md` | English | `install-wizard.md` |
| `install-wizard.pt_br.md` | Brazilian Portuguese | `install-wizard.pt_br.md` |
| `install-wizard.pt_pt.md` | European Portuguese | `install-wizard.pt_pt.md` |
| `install-wizard.fr.md` | French | `install-wizard.fr_fr.md` (renamed) |
| `install-wizard.es.md` | Spanish | `install-wizard.es_es.md` (renamed) |

The GitPR repo uses `fr_fr`/`es_es` suffixes; renamed to `fr`/`es` to match the website's language code convention used by the controller (`{file}.{lang}.md`).

### 2. `public/content/menu.json` (modified)

Added `"▸ Install Wizard"` / `"▸ Assistente de Instalação"` / `"▸ Assistant d'Installation"` / `"▸ Asistente de Instalación"` entry in all 5 language arrays, positioned between "Git Hooks" and "GitHub PAT Integration".

| Language | Menu title |
|----------|-----------|
| en | ▸ Install Wizard |
| pt_br | ▸ Assistente de Instalação |
| pt_pt | ▸ Assistente de Instalação |
| fr | ▸ Assistant d'Installation |
| es | ▸ Asistente de Instalación |

### Note

The source file contains relative links to other docs (e.g., `[Skills and Templates System](skill-template.md)`). These links work on GitHub but may need updating to website paths (`/docs/skill-template`) for the rendered site — this is a pre-existing issue across all docs in `public/content/docs/`.
