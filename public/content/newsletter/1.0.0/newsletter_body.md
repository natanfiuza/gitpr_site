# GitPR 1.0.0 — What's New

## What's New in This Version

- **SCM Multi-Forge (`gitpr --init` + `ScmProvider` layer):** A single abstraction over GitHub, GitLab, Bitbucket and Azure DevOps — `--init` detects the forge from your remote, validates the token (3 attempts, re-prompt on 401) and stores it Fernet-encrypted **only on success**. The legacy GitHub token keeps working, so there is no migration.
- **`gitpr release` Subcommand (Changelog / Release Notes):** Generates the branch changelog between `--since` (default: last tag) and `HEAD`, classifies commits with Conventional Commits, suggests the semantic bump, adds an AI executive summary and prepends it to `CHANGELOG.md`. `--publish`/`--draft` push the release to the forge (GitHub creates the tag; GitLab requires it) and `--format markdown|json` gives structured output.
- **Suggested Reviewers in the PR Flow:** GitPR now asks the forge itself for reviewer suggestions when publishing a PR. Opt out with `--no-suggest-reviewers`, tune it with `GITPR_REVIEWER_SUGGESTION_TOP_N` and `GITPR_REVIEWER_SUGGESTION_EXCLUDED`.
- **Silent MCP Server + Bounded DNS:** Tool output leaking into the stdio/CLI flow was eliminated, DNS resolution is now time-bounded, and the `GITPR_AI_TIMEOUT` default dropped from 600s to **180s**.
- **Localized URLs and Prompts:** Repository URLs standardized across templates and documentation; issue creation prompts now receive the active language.
- **i18n Expanded to 742 Keys:** Changelog headings are translatable at runtime (they follow `--lang`), 48 new keys across the 6 dictionaries, `__lang_version__` v0.0.23 and full key parity — 0 untranslated, 0 orphaned.
- **Multilingual Documentation Expanded:** 3 new complete families in 5 languages — `release-notes`, `scm-multiforge` and `suggested-reviewers` — with architecture ADRs, plus 8 topics updated.
- **Version 1.0.0:** `__version__` jumped from 0.0.37 to 1.0.0, and `CHANGELOG.md` is now maintained by `gitpr release` itself.

## How to Use

Update via PyPI:

```
pip install --upgrade gitpr-cli
```

Or download the standalone binary from [GitHub Releases](https://github.com/gitpr-cli/gitpr/releases).

Configure your forge once — GitPR detects it from the remote:

```
gitpr --init            # multi-forge wizard: detects the forge and validates the token
```

Generate the changelog of your branch and, if you want, publish the release:

```
gitpr release           # changelog + AI summary prepended to CHANGELOG.md
gitpr release --publish # publishes the release on the configured forge
```

The PR flow now suggests reviewers automatically — turn it off with `--no-suggest-reviewers`.

## Useful Tips

`gitpr -is` generates an issue from your current diff, but there are two other engines: `-ht` compiles the whole branch history into a release/epic issue, and `-b src/core.py:140-195` traces a file's evolution via `git blame` to document legacy code and technical debt.
