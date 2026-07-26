# Update Status Report — v0.0.28 → v0.0.29

**Date:** 2026-07-25
**Branch:** develop_natan
**Source:** `C:\Users\nataniel\projetos\python\gitpr\docs\reports\relatorio_estado_v0.0.4.md`

## Summary

Updated all 5 locale variants of `public/content/relatorio.*` from v0.0.28 to v0.0.29 based on the new GitPR CLI status report.

## Files Updated

| File | Version | Description |
|------|---------|-------------|
| `public/content/relatorio.md` | v0.0.29 (EN) | English — Project Status Report |
| `public/content/relatorio.pt_br.md` | v0.0.29 (PT-BR) | Portuguese (Brazil) — Relatório de Status |
| `public/content/relatorio.pt_pt.md` | v0.0.29 (PT-PT) | Portuguese (Portugal) — Relatório de Estado |
| `public/content/relatorio.es.md` | v0.0.29 (ES) | Spanish — Informe de Estado |
| `public/content/relatorio.fr.md` | v0.0.29 (FR) | French — Rapport d'État |

## Key Changes (v0.0.28 → v0.0.29)

| Section | Change |
|---------|--------|
| **Version** | 0.0.28 → 0.0.29 (2026-07-24 → 2026-07-25) |
| **Overview** | New highlights: MCP Prompts + Annotations + Thinking Words + Adaptive Spinner |
| **Architecture** | MCP line updated to reference Tool Annotations and Prompts refactored in v0.0.29 |
| **Module 2 (CLI)** | `--mcp` flag description expanded: 10 annotated tools + 15 resources + 7 prompts |
| **Module 7 (i18n)** | Coverage now includes MCP tools, resources, prompts, and annotations |
| **Module 8 (Spinner)** | Added Adaptive Speed feature + 201 entries per language (84 words + 117 phrases) |
| **Module 16 (MCP Server)** | Complete rewrite: Tool Annotations, 15 resources (was 7), 7 Prompts with 35 template files |
| **Testing** | Added `test_mcp_prompts.py` (11 scenarios), total scenarios 160+ → 165+ |
| **i18n & Docs** | 22 topics (was 20), 110+ pages (was 100+), 8 plans (was 7), 12+ reports (was 11+) |
| **Evolution Table** | v0.0.3 → v0.0.4 with 14 comparison rows (was 11) |
| **Next Steps** | MCP Prompts and MCP Annotations moved from "next" to completed; added "GitHub Release automation" |
