# GitPR 1.4.0 — What's New

## What's New in This Version

- **Release 1.3.0 was closed and 1.4.0 is already tagged:** the `v1.3.0` tag (merge of PR #193, 2026-09-24) closed the item that had been blocking two windows, and `CHANGELOG.md` was completed with the six features that were missing. Ten days later the `v1.4.0` tag (merge of PR #207, 2026-10-12) closed this entire window — the five features below **plus** the 1.3.0 release note fit into a single cycle.
- **`gitpr release` stopped repeating the history:** release notes listed, for every version, all the commits of all previous versions (the 1.3.0 section shipped with 124 bullets, 115 of them repetition of 1.2.0). The cause was not the AI — it was the `git log` range, anchored on `git describe`, which in this repository resolved to `v0.0.8`. The cutoff became the **previous section of the changelog itself**, every bullet gained a **real, clickable** link to commit, PR and contributor, and a commit already present in another section is discarded before becoming a bullet — when nothing is left, the release **aborts** instead of publishing an empty section.
- **Telemetry left the JSON file and became a SQLite ledger:** events were written to per-command files, from a daemon thread, and then "enriched" back through the AI cache via a heuristic match on (repo, branch, action, minute) that depended on three divergent folder maps. The result was predictable: `tokens_actual` **always zero** and data loss between repositories on export. `~/.gitpr/metrics/telemetry.db` writes the row **synchronously**, with tokens copied from the provider metadata at write time, and a short-lived process (a hook, for instance) no longer loses its own row on exit.
- **Mentor Mode — the review that teaches, not just points:** a review says *what* is wrong and almost never *why it matters*. `gitpr mentor` (and the `--mentor` flag on `-r`/`-f`) enriches the findings of the last review with what is going on, why it matters, an everyday analogy and the concepts to study. It is **opt-in** and never invents: with insufficient evidence, the explanation says so instead of filling the void.
- **Deterministic risk score — no AI, no quota:** `gitpr risk` produces a 0–100 score with a `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` level from local signals (critical paths, missing tests, migrations, bug and revert history, static findings). The formula is declared (**50/30/20**), so are the mandatory escalation rules, and the score enters the review prompt as **read-only** context and the report. An explainable number is worth more than an expensive guess.
- **Policy Packs — the repository's policy became a versioned artifact:** skills, linter rules, severities, critical paths, risk weights and PR/commit conventions were scattered and every repository configured them by hand. `gitpr policy` adopts a versioned YAML pack with a **checksum**, resolves precedence between layers and records the choice in the lockfile the team commits. Four built-in packs ship in the wheel; with no active pack, **nothing changes**.
- **Baseline and auditable suppressions — known noise stops blocking:** linters and reviews re-reported the same pre-existing findings on every run, and it was up to the reviewer to tell the output apart in their head. `.gitpr/baseline.json` records the snapshot of accepted findings, every new finding is classified as `new`/`existing`/`resolved`/`ignored`/`accepted_debt`, and **only `new` blocks**. Every suppression carries a reason; accepted debt carries an owner and, when there is one, a deadline.
- **The finding model became a canonical type:** `NormalizedFinding` moved from `infrastructure/linter/external/base_bridge.py` to `src/domain/finding/`. Without that, every bridge — Semgrep, Gitleaks, Bandit, the regex ruleset — would fingerprint a different shape of the same finding and the baseline would have no way to recognize it.
- **The previous window's debt was closed:** `SKILL_LABELS` and `mcp_server.SKILL_FILES` received `tests`, `explain` and now `mentor`; the **40 `__()` keys** that did not exist in any dictionary were translated; and the **4 failures** from the previous window no longer exist. The three assertions that reported the debt are still running — they simply stopped reporting. The suite, measured end to end in this window, closes at **2795 passed out of 2799** — the two remaining failures are an isolation bug in `tests/test_mentor_cli.py`, not a product regression.
- **i18n coverage closed the loop:** **1603 keys** in each of the 6 dictionaries (+445 in this window) and **zero keys used in the code without a translation** — the code uses 1603, the dictionaries have 1603.
- **The pending translations were paid off:** `split-command` and `otimizacao-de-tokens` completed all 5 languages; `review-pr` still has 2. Two PT-only families (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) moved from the top of `docs/` to `docs/extra/`.

## How to Use

Upgrade from PyPI:

```
pip install --upgrade gitpr-cli
```

Four new commands — the read-only ones write nothing until you pass the option that does:

```
gitpr risk                                 # 0-100 score for the current diff (no AI, no quota)
gitpr risk --file src/auth.py              # the factors behind one file's score
gitpr risk --format json                   # structured output for CI
gitpr mentor                               # a didactic walkthrough of the last review's findings
gitpr -r --mentor                          # appends the mentor section to the code review
gitpr policy list                          # the packs on this machine and the one in force
gitpr policy init --stack laravel          # detects the stack and activates the matching pack
gitpr policy use acme/team-policy@1.0.0    # pins a pack, writing .gitpr/policy.lock.yml
gitpr baseline create                      # records the current diff as the accepted baseline
gitpr baseline show                        # the findings, the decisions and the counts
gitpr baseline suppress <id> --reason "..." # a decision about one finding
```

Nothing changes until you opt in. With no `.gitpr/baseline.json` in the repository every run behaves exactly as it did before — the gate only exists once you create the file, and from then on **only `new` findings block**: `existing`, `resolved`, `ignored` and `accepted_debt` do not. Every suppression carries a reason; accepted debt takes an owner and, optionally, a deadline. The same holds for the policy: with no active pack, your setup is untouched.

**Declared CLI break:** `--metrics`, `--export`, `--purge`, `--hook-event` and `--dashboard` are gone from the root — they became the `gitpr metrics` group (`gitpr metrics dashboard`, `gitpr metrics export`, `gitpr metrics prune`). Telemetry moved from per-command JSON files to a SQLite ledger at `~/.gitpr/metrics/telemetry.db`; `gitpr metrics migrate` imports the old files and moves them to `~/.gitpr/metrics_legacy/`.

`gitpr risk` never calls the AI — it is a declared 50/30/20 formula over local signals, so the same repository always yields the same number. `gitpr mentor` is opt-in and, when the evidence is not enough, says so instead of inventing an explanation.

## Useful Tips

GitPR deliberately ignores untracked files (it reads `git diff HEAD`), and that's a feature: a forgotten `.env.local` with production passwords is never sent to the AI, and giant cache folders never waste tokens. To include a new file, just `git add` it first.
