# Prompt: Update `get_doc_url()` to Point to the Technical Documentation Website

## Task

Modify the `get_doc_url()` function in `C:\Users\nataniel\projetos\python\gitpr\src\core.py` so that instead of returning GitHub repository URLs, it returns URLs pointing to the GitPR official documentation website (`https://gitpr.natanfiuza.dev.br`), which now has a dedicated **Technical Documentation** section with full multi-language support.

## Current Behavior (lines 98-105 of `core.py`)

```python
def get_doc_url(filename):
    """Returns the complete URL for a docs/ file, with language suffix if needed."""
    if CURRENT_LANG.startswith("en"):
        return f"https://github.com/natanfiuza/gitpr/blob/main/docs/{filename}"
    else:
        base, ext = filename.rsplit(".", 1)
        return f"https://github.com/natanfiuza/gitpr/blob/main/docs/{base}.{CURRENT_LANG}.{ext}"
```

**Problems:**
1. Points to raw GitHub source (`github.com/.../blob/main/docs/`) instead of the rendered documentation site
2. Uses file-based language suffix (`.pt_br.md`) — the website uses `?lang=` query parameter
3. References files that may not exist on the website (e.g., `install-wizard.md` is only in the GitHub repo)

## Target Behavior

The function should transform documentation filenames into clean website URLs:

| Input `filename` | `CURRENT_LANG` | Output URL |
|---|---|---|
| `"untracked-files.md"` | `"en_us"` | `https://gitpr.natanfiuza.dev.br/docs/untracked-files` |
| `"untracked-files.md"` | `"pt_br"` | `https://gitpr.natanfiuza.dev.br/docs/untracked-files?lang=pt_br` |
| `"map-reduce-diff.md"` | `"fr"` | `https://gitpr.natanfiuza.dev.br/docs/map-reduce-diff?lang=fr` |
| `"chat-interativo.md"` | `"es"` | `https://gitpr.natanfiuza.dev.br/docs/chat-interativo?lang=es` |

### Rules

1. **Strip `.md` extension** — all filenames passed to this function end with `.md`. Remove it to get the URL slug.

2. **Base URL** — `https://gitpr.natanfiuza.dev.br/docs/`

3. **Language handling** — the website uses `?lang=` query parameter with these values: `en`, `pt_br`, `pt_pt`, `fr`, `es`. The `CURRENT_LANG` variable comes from `src/i18n.py` and may contain values like `en_us`, `pt_br`, `pt_pt`, `fr`, `es`. You must normalize:
   - Any value starting with `"en"` (e.g., `"en"`, `"en_us"`, `"en_gb"`) → no `?lang` parameter needed (English is the default on the website)
   - Other values: pass as-is via `?lang={CURRENT_LANG}` — the website handles unknown lang codes with its own English fallback, so don't over-normalize

4. **Keep the function signature and return type unchanged** — it must continue to accept a single string and return a single string. No other function in the codebase should need changes.

5. **Update the docstring** to reflect the new behavior and the website URL.

## All Call Sites (for verification)

After modifying `get_doc_url()`, verify these call sites still work correctly. **Do NOT modify these files** — just verify the URLs they produce are valid:

### In `src/core.py`:
| Line | Call | Expected URL |
|---|---|---|
| 127 | `get_doc_url('untracked-files.md')` | `https://gitpr.natanfiuza.dev.br/docs/untracked-files` |
| 319 | `get_doc_url('map-reduce-diff.md')` | `https://gitpr.natanfiuza.dev.br/docs/map-reduce-diff` |
| 584 | `get_doc_url('install-wizard.md')` | `https://gitpr.natanfiuza.dev.br/docs/install-wizard` |

### In `src/main.py`:
| Line | Call | Expected URL |
|---|---|---|
| 46 | `get_doc_url('commit-message-ia.md')` | `https://gitpr.natanfiuza.dev.br/docs/commit-message-ia` |
| 51 | `get_doc_url('code-review-ia.md')` | `https://gitpr.natanfiuza.dev.br/docs/code-review-ia` |
| 56 | `get_doc_url('code-review-ia.md')` | `https://gitpr.natanfiuza.dev.br/docs/code-review-ia` |
| 61 | `get_doc_url('code-review-ia.md')` | `https://gitpr.natanfiuza.dev.br/docs/code-review-ia` |
| 66 | `get_doc_url('linter-regras-customizadas.md')` | `https://gitpr.natanfiuza.dev.br/docs/linter-regras-customizadas` |
| 71 | `get_doc_url('skill-template.md')` | `https://gitpr.natanfiuza.dev.br/docs/skill-template` |
| 76 | `get_doc_url('auto-update.md')` | `https://gitpr.natanfiuza.dev.br/docs/auto-update` |
| 81 | `get_doc_url('git-hooks-locais.md')` | `https://gitpr.natanfiuza.dev.br/docs/git-hooks-locais` |
| 86 | `get_doc_url('install-wizard.md')` | `https://gitpr.natanfiuza.dev.br/docs/install-wizard` |
| 91 | `get_doc_url('blame-arqueologo.md')` | `https://gitpr.natanfiuza.dev.br/docs/blame-arqueologo` |
| 96 | `get_doc_url('issue-tui-help.md')` | `https://gitpr.natanfiuza.dev.br/docs/issue-tui-help` |
| 101 | `get_doc_url('issue-tui-help.md')` | `https://gitpr.natanfiuza.dev.br/docs/issue-tui-help` |
| 106 | `get_doc_url('providers-ia.md')` | `https://gitpr.natanfiuza.dev.br/docs/providers-ia` |
| 111 | `get_doc_url('chat-interativo.md')` | `https://gitpr.natanfiuza.dev.br/docs/chat-interativo` |
| 116 | `get_doc_url('providers-ia.md')` | `https://gitpr.natanfiuza.dev.br/docs/providers-ia` |
| 325 | `get_doc_url('git-hooks-locais.md')` | `https://gitpr.natanfiuza.dev.br/docs/git-hooks-locais` |
| 329 | `get_doc_url('linter-regras-customizadas.md')` | `https://gitpr.natanfiuza.dev.br/docs/linter-regras-customizadas` |
| 491 | `get_doc_url('understanding_chat_functionality.md')` | `https://gitpr.natanfiuza.dev.br/docs/understanding_chat_functionality` |
| 529 | `get_doc_url('understanding_chat_functionality.md')` | `https://gitpr.natanfiuza.dev.br/docs/understanding_chat_functionality` |

## Edge Cases

1. **English variants** — `CURRENT_LANG` may be `"en"`, `"en_us"`, or `"en_gb"`. All should produce URLs without `?lang=` (English is the website default).

2. **Unknown language codes** — if `CURRENT_LANG` has an unexpected value, pass it through as-is. The website server-side falls back to English for unknown codes, so the URL will still work.

3. **Filename without `.md`** — the function currently uses `filename.rsplit(".", 1)` which assumes a single extension. If a filename like `README.md` is passed (the README page), it should correctly become `docs/readme` (lowercase slug matching the filesystem).

4. **`install-wizard.md`** — this file exists on GitHub but does NOT currently have a dedicated page on the website. The URL will result in a 404 on the website. This is acceptable for now — do not add special handling; just transform it like any other filename. The website team may add the page later.

## Verification

After making the change:

1. Read the modified `get_doc_url()` function to confirm it follows the rules above
2. Mentally trace through at least 3 call sites with different `CURRENT_LANG` values (e.g., `"en_us"`, `"pt_br"`, `"fr"`) to verify the URLs are correct
3. Confirm no import statements or other references broke (the function is imported in `main.py` line 17 as `from src.core import get_doc_url`)

## Files to Modify

- **Only** `C:\Users\nataniel\projetos\python\gitpr\src\core.py` — lines 98-105 (the `get_doc_url` function)

Do **NOT** modify any other files. Do not change call sites, imports, or any other function.
