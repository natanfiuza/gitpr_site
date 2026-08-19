# GitPR 0.0.37 — What's New

## What's New in This Version

- **External Linters Bridge + `--linter-setup` Assistant:** Integration with mature linters (ESLint, PHP_CodeSniffer, Stylelint) running only on the changed lines of the diff, with a Checkstyle XML parser, a new error TUI (`LinterApp`) and a consolidated Markdown report in `.gitpr/reports/linter/`. The interactive assistant configures everything with remote presets (`templates/gitpr.linter-presets.json`) versioned by the `LINTER_PRESETS_VERSION` marker.
- **i18n Repaired and Complete:** 51 corrupted keys repaired + 36 keys with literal `\n` in all 6 dictionaries; AST audit of 638 keys with 0 untranslated and 0 mangled; total parity of 547 identical keys per file; `__lang_version__` v0.0.13 → v0.0.20 with guard tests.
- **Co-Authorship Trailer:** Every AI-generated commit receives `Co-Authored-By: Gitpr-cli <gitpr@natanfiuza.dev.br>` — idempotent (never duplicated, preserves third-party trailers), hidden from the TUI and with opt-out `GITPR_COAUTHOR=false`.
- **MCP Server Hang Fix:** All 12 tool handlers were synchronous and ran inline on the event loop — any blocking call froze the stdio server. New `_offload` decorator (anyio worker threads), warm-import at startup, `stdin=subprocess.DEVNULL` on all subprocesses and a hard 10s timeout on the smart-excludes download. New e2e tests with real JSON-RPC stdio.
- **Linter Error Modal Fixes:** "Commit with --no-verify" and "Abort" buttons side by side (previously stacked and overlapping); the no-verify choice now resumes the commit flow; modal push deferred via `call_next` to the app's message pump.
- **Dead Code Removed + MCP Tweaks:** Dead `FileStageScreen` class removed; `claude-code` listed in the `gitpr-mcp --install` help; hidden `gitpr --mcp` alias documented.
- **Multilingual Documentation Expanded:** `docs/ARCHITECTURE.md` rewritten in canonical EN + 4 locales created (18 architecture topics); new `i18n_explanation` topic in 5 languages; READMEs and 4 topics updated.
- **Consistent Codebase Formatting:** Black-style refactor across all of `src/` (double quotes, trailing commas, line breaks) — no functional change.
- **Local Claude Code Skills:** `status-report` (status report generation), `implement-fixes` (fix workflow) and `caveman-commit` (compact commit messages) skills added.

## How to Use

Update via PyPI:

```
pip install --upgrade gitpr-cli
```

Or download the standalone binary from [GitHub Releases](https://github.com/natanfiuza/gitpr/releases).

Try the new external linters bridge:

```
gitpr --linter-setup   # interactive wizard: ESLint, PHPCS, Stylelint
gitpr --linter         # regex rules + external linters, report at .gitpr/reports/linter/
```

Your commits now carry the co-author trailer automatically — opt out with `GITPR_COAUTHOR=false`.

## Useful Tips

Re-run any AI command without changing the code and GitPR answers in milliseconds: responses are cached at `~/.gitpr/cache/prompts/`, keyed by an MD5 hash of your diff + instructions — repeating a command costs zero API quota.
