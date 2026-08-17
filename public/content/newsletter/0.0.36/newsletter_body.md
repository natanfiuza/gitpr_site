# GitPR 0.0.36 — What's New

## What's New in This Version

- **Staging Selection and Error Fix (`stage_files`):** The staging TUI now reads the actual selection from `SelectionList.selected` — individual toggles are respected — and `stage_files()` returns `(success, error_message)`: `git add` failures display the real git error instead of a false success message. Staging now happens only once per flow.
- **AI Message Skip on Git-Generated Commits:** The `prepare-commit-msg` hooks (5 language variants) now skip all git-generated sources (`merge`, `squash`, `amend`, `commit` — previously only `message`), with a belt-and-braces check of `.git/MERGE_HEAD`. `git pull`/`git merge` no longer corrupt `.git/MERGE_MSG` with an AI message. Hooks auto-sync to v0.0.2.
- **File Status Translations:** Status labels ("Modified", "Deleted", "New") translated in the es, fr, pt_br and pt_pt packs — pt_BR coverage rose to 507 keys.
- **Multilingual Documentation Expanded and Synced:** `docs/pr-descricao-padrao.md` rewritten in canonical EN + 4 locales with a publication section; `docs/mcp-integration.md` synced in the 5 languages; `docs/git-hooks-locais.md` documents the merge-source skip in the 5 languages.
- **New MCP Template:** `templates/gitpr.mcp-jsonrpc-calls.md` — JSON-RPC call reference for the MCP tools.

## How to Use

Update via PyPI:

```
pip install --upgrade gitpr-cli
```

Or download the standalone binary from [GitHub Releases](https://github.com/natanfiuza/gitpr/releases). The `prepare-commit-msg` hooks auto-sync to v0.0.2 — no manual step needed.

See the fixes in action:

```
gitpr              # publish flow: the staging modal respects your selection and shows real git errors
git merge <branch> # the AI message no longer touches .git/MERGE_MSG
```

## Useful Tips

With hooks installed (`gitpr -ih`), a plain `git commit` opens your editor with the AI message pre-filled. But GitPR knows when to step aside: `-m`, merges, squashes and amends are detected and the AI stays silent, so your own messages are never overwritten.
