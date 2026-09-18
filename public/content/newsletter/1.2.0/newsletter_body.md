# GitPR 1.2.0 — What's New

## What's New in This Version

- **`gitpr fix` — the review that becomes an applicable patch:** The last review in the cache feeds **one** AI call, which returns findings in fenced blocks; each block is validated as a unified diff and `git apply --check` proves it fits your current tree. A deterministic, I/O-free classifier labels every candidate `safe`, `review_required` or `experimental`. Dry run is the default — writing requires `--apply`, and `--force` never bypasses the applicability check, only the classification. Everything applied goes into `.gitpr/fix_history.json`, which is what `--rollback` reads.
- **`gitpr review-pr <n>` — reviewing someone else's PR without a checkout:** The diff comes straight from the forge API and enters the **same engine** the local flows use — same report, same linter rules, same `.txt`. Read-only by default: nothing is published to the forge without an explicit `--post-comment`. It widens the audience from "whoever is about to open a PR" to "whoever was invited to review someone else's PR".
- **Reviewer identity resolution — the attach that never landed:** Suggestions are born from `git blame`, so they carry **names and e-mails**, not logins. When nothing resolved, the UI showed the bare name — and typing that name back made GitPR send it *verbatim* as if it were a login. GitHub answers **201 without attaching anyone**: apparent success, reviewer missing, no warning at all. A dedicated layer now resolves the identity **twice** (before the TUI and at attach time) and also closes the API's second silent failure — a login accepted but not attached is detected by reading `requested_reviewers` back.
- **`gitpr fix` now fixes the review that was reviewed:** The reviewed diff is written to the cache record (`reviewed_diff`) and `fix` prefers it, falling back to re-derivation only for old records. It is the only correct source when the review came from a remote PR or from a whole-branch diff.
- **MCP grew from 12 to 14 tools and from 17 to 18 resources:** `list_fix_candidates` (13th, read-only) + `skill://fix`, and `review_remote_pr` (14th, read-only, no `post_comment` argument, writes no `.txt`).
- **i18n expanded to 1048 keys:** +93 since the previous report, with `__lang_version__` at **v0.0.28** and full key-set parity across the 6 dictionaries.
- **Documentation:** 2 new families — `fix-command` (5 languages) and `review-pr` (EN + PT-BR) — and 9 updated topics, including `code-review-ia` (remote mode as §1.4) and `suggested-reviewers` (login resolution).
- **Two latent GitLab defects fixed:** `changes[].diff` discards the file path, so the `diff --git` headers are now synthesized from `old_path`/`new_path`; and a truncated diff (`overflow: true`) used to be reviewed halfway and published as if it were whole — now it raises.
- **Version 1.2.0:** `__version__` moved from 1.1.0 to 1.2.0 — the bump is in the working tree, not yet committed or tagged, and `CHANGELOG.md` still stops at `[1.1.0]`.

## How to Use

Upgrade from PyPI:

```
pip install --upgrade gitpr-cli
```

Review a pull request that is already open — nothing is checked out, nothing is fetched into your tree:

```
gitpr review-pr 123                    # read-only: the review lands in a .txt
gitpr review-pr 123 --post-comment     # the only path that writes to the forge
gitpr review-pr 123 --provider deepseek
```

Then turn that review into patches you read before they touch your tree:

```
gitpr fix                      # lists the candidates of the last review (writes nothing)
gitpr fix FIX-001              # dry run: the diff of one finding
gitpr fix FIX-001 --apply      # writes it, after a confirmation
gitpr fix --all-safe --apply   # writes every safe patch, on a new branch by default
gitpr fix --rollback FIX-001-1a2b3c4d
```

`gitpr fix` reads the most recent review of the current repository and branch from the cache — run `gitpr -r` first. The rollback needs no commit, no stash and no reset: it replays the stored diff with `git apply --reverse`.

## Useful Tips

`gitpr -r -i src/legacy/parser.py` reviews a whole file, ignoring git history — the docs call it "acting as a consultant on legacy code refactoring". Customize the audit's focus via the `.gitpr.filereview.md` skill file (cohesion, coupling).
