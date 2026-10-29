# GitPR 1.4.3 — What's New

## What's New in This Version

- **The update gate stopped losing releases published after the day's first run:** the PyPI cache was keyed by the **local date**, so a release published at 10:45 stayed invisible until midnight for anyone who had already run the CLI at 07:54. The cache became a **rolling 1-hour window** with an ISO `checked_at`, atomic writes (`os.replace`) and validation on read — a corrupted file, a timestamp in the future or a leftover from the old format simply expires. `gitpr -u`/`--update` now **ignores the cache** and queries PyPI on every invocation, as the documentation already promised.
- **Version comparison became PEP 440:** `packaging.version.Version` replaced tuple arithmetic — it fixes the ordering of multi-digit segments (`1.4.10 > 1.4.9`), prevents a remote pre-release from blocking a stable installation in a `pip install --upgrade` that would refuse to install it, and treats an unreadable version on either side as **unknown** instead of "newer".
- **The gate now reaches subcommands — but only when the run can ask:** `gitpr release`, `fix`, `review-pr` and the others go through the gate when there is a TTY on both ends. CI, pipes, hooks, `--quiet`/`--hook`/`--mcp` and `-h`/`--help` are **never** blocked; subcommand help is detected in the **raw `argv`**, because Click clears the context arguments before the group callback.
- **The 7 failures in `tests/badge/test_badge_paths.py` finally closed:** the provider token validation moved from outside into **inside** the `if check_remote_state:` block of `publish_pull_request_use_case` — the `--no-edit` flow no longer returns a false-positive `auth_required`. `pr_url`/`pr_number` are now read with defensive `getattr`, and the fixture gained `token="ghp_fake"` and `number=1`. **The suite dropped from 12 to 5 known failures** — debt open for three consecutive windows.
- **The escape hatch semantics changed (declared break):** `GITPR_SKIP_UPDATE_CHECK` is now read as a **strict boolean** — only `1`, `true`, `yes`, `on` and `y` (case-insensitive) disable the check. `false`, `0`, `no`, `off`, `n` and an empty value **keep** the gate on.
- **The i18n debt was not paid:** the dictionaries remain at **1620 keys** (×4) and **1618** (`es.json`, `fr.json`), with **8 keys used in code without translation** and **2 orphans** — exactly the same numbers as the previous window.

## How to Use

Update from PyPI:

```
pip install --upgrade gitpr-cli
```

The update gate now follows you into the subcommands: in an interactive terminal, `gitpr release`, `gitpr fix` and `gitpr review-pr` also warn when a newer release exists. CI, pipes, hooks, the MCP server, `--quiet` and `-h`/`--help` are never blocked — and the help flag is detected in the raw `argv`, so `gitpr release -h` still just prints the help.

`gitpr -u`/`--update` now queries PyPI on every run instead of trusting the cache. If you disable the gate with `GITPR_SKIP_UPDATE_CHECK`, note the value is now a strict boolean: only `1`, `true`, `yes`, `on` or `y` turn the check off; `false`, `0`, `no`, `off`, `n` and an empty value keep it on.

## Useful Tips

`gitpr check` is one CI entry point: it runs the static linter, secret scanners, risk scoring, optional AI review and baseline classification, and emits SARIF 2.1.0, JSON or JUnit XML for GitHub Code Scanning, GitLab SAST and Jenkins. `--no-ia` makes it free and offline, `--ia-only-high-risk` spends AI tokens only on risky files, and the exit codes are deterministic: 0 pass, 1 findings at or above `--fail-on`, 2 configuration or execution error.
