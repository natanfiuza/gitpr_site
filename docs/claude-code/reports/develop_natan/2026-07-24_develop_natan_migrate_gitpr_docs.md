# Task Report: Migrate GitPR Documentation to Website

**Date:** 2026-07-24
**Branch:** `develop_natan`
**Task:** Transfer and structure all GitPR CLI documentation from the source repository to the website, enabling the `get_doc_url()` function to be updated to point to `https://gitpr.natanfiuza.dev.br/`.

## Summary

Copied all 15 documentation topics (5 languages each = 75 files) from `C:\Users\nataniel\projetos\python\gitpr\docs\` to `public/content/docs/`. Created a stub for `chat-interativo.md` (missing from source but referenced in code). Updated `menu.json` with the 16 new docs entries for all 5 languages.

## Changes Made

### Files Created

| Path | Description |
|------|-------------|
| `public/content/docs/` | New directory for GitPR CLI documentation |
| `public/content/docs/*.md` | 75 files copied (15 topics × 5 languages) |
| `public/content/docs/chat-interativo.md` | Stub (EN) — referenced in main.py but missing from source |
| `public/content/docs/chat-interativo.{pt_br,pt_pt,es_es,fr_fr}.md` | Stubs × 4 languages |

### Files Modified

| Path | Description |
|------|-------------|
| `public/content/menu.json` | Added 16 docs entries per language (80 total new entries) |

### Documentation Topics Migrated

1. `auto-update` — Auto-update mechanism
2. `blame-arqueologo` — Code archaeologist (git blame + AI)
3. `chat-interativo` — Interactive chat TUI (stub, was missing from source)
4. `code-review-ia` — AI code review features
5. `commit-message-ia` — AI-powered commit messages
6. `git-hooks-locais` — Local Git hooks
7. `github-pat-integration` — GitHub PAT for issue creation
8. `i18n_explanation` — Internationalization system
9. `issue-tui-help` — Issue TUI help screen
10. `linter-regras-customizadas` — Custom linter rules
11. `map-reduce-diff` — Map-Reduce for huge diffs
12. `mcp-integration` — MCP server integration
13. `providers-ia` — AI providers configuration
14. `skill-template` — Skill/template system
15. `understanding_chat_functionality` — Chat TUI guide
16. `untracked-files` — Why untracked files are ignored

### URL Structure

The site now supports the following URL pattern (matching what `get_doc_url()` should generate):

- EN: `/docs/{name}` → `public/content/docs/{name}.md`
- Other: `/docs/{name}?lang={lang}` → `public/content/docs/{name}.{lang}.md`

### Known Issue in Source

The explore agent identified that `HELP_MAP['chat']['url']` in `src/main.py:105` references `chat-interativo.md` which doesn't exist in the GitPR source repo. The other chat references correctly use `understanding_chat_functionality.md`. A stub `chat-interativo.md` was created on the site to handle this gracefully.
