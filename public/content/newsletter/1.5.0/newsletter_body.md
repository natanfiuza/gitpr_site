# GitPR 1.5.0 — What's New

## What's New in This Version

- **The issue → PR → merge cycle now closes by itself:** every issue created with `gitpr -is` is now recorded in `.gitpr/conf/github.yml` (a local registry that ignores itself in `.gitignore`), and the next `gitpr` checks that it is still open before offering the `close #N` line in the Pull Request body. `--close-issue` accepts without asking, `--no-close-issue` skips the check, and `GITPR_ISSUE_AUTOCLOSE=false` turns the feature off. The new `src/issue_autoclose/` package has **5 files** and a row of decisions recorded in ADRs 001–007 in `docs/specs/issue-autoclose/`.
- **The PR base no longer lives only on the machine of whoever runs it:** `.gitpr/conf/gitpr.config.yml` is versioned with the project and carries the target branch; `src/project_config.py` resolves `--base` → project file → `PR_DEFAULT_BASE` → `origin/HEAD` → `main`, and the target must exist in `origin` and differ from the current branch. A target equal to the current branch is refused and, in interactive execution, repaired by a dedicated modal (`src/ui/pr_base_app.py`) that writes the new value into the project file ([ADR-015](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-015-project-local-pr-base.md)).
- **The i18n debt was paid:** the six dictionaries now have **1698 keys** each, `__lang_version__` went up to **v0.0.38**, and `tests/test_i18n.py` closes **22 green** — no missing keys, no orphans, no identity above the allowlist. The `sync_i18n.py` extractor swapped regex for **AST**, which eliminated the 21 false negatives from implicit concatenation.
- **The secrets ruleset covered the three gaps that were open:** STS keys `ASIA…`, PATs `github_pat_…`, cookies `xoxc-`/`xoxd-`, **unquoted** assignments (`API_KEY=abc123`), a left boundary in the key name and fewer false positives (`bypassword`, `os.getenv(...)`). That is **7 rules** with wider regexes, covered by **77 scenarios**.
- **Contracts that lived only in the head became tests:** the package version has a **single SSOT** in `src/updater.py` (ADR-014) and a test that fails if `pyproject.toml`, `CLAUDE.md` or `GEMINI.md` diverge; the baseline exit code contract gained 8 integration scenarios; the legacy reviewer read-back (`None`) no longer prints `attached None`; and `-h` before the subcommand renders help again instead of opening the TUI.
- **1.5.0 is tagged but not yet published:** the `v1.5.0` tag points to the merge of PR #260 (2026-11-04), but PyPI still serves **1.4.4** — the release workflow exists (`.github/workflows/release.yml`), but the publication needs to be confirmed.

## How to Use

1.5.0 is tagged as `v1.5.0`; at the time of this edition PyPI still serves 1.4.4. Once the release is published, update with:

```
pip install --upgrade gitpr-cli
```

To close the issue cycle on its own, create the issue with `gitpr -is` and press F3: the number is recorded in `.gitpr/conf/github.yml` (local state, added to `.gitignore`). On the next `gitpr`, the Pull Request body receives the `close #N` line after GitPR checks the issue is still open. `--close-issue` accepts it without asking, `--no-close-issue` skips the check for that run, and `GITPR_ISSUE_AUTOCLOSE=false` turns the feature off on the machine.

To pin the PR base per repository, commit this file with the project:

```yaml
pull_request:
  base_branch: develop
```

GitPR seeds `.gitpr/conf/gitpr.config.yml` on the first run and never overwrites it; `--base` overrides it for a single run. The chosen branch must exist in `origin` and cannot be the branch the PR is opened from.

## Useful Tips

Re-run `gitpr` on the same branch and you won't get duplicate PRs: GitPR detects the existing PR and offers "Push to existing PR", which updates only the body (the title stays untouched). Bonus: a failed `git push` with "no upstream" is auto-retried with `--set-upstream`.
