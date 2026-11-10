# GitPR 1.7.0 — What's New

## What's New in This Version

- **The metrics dashboard gained ASCII charts and tabbed navigation:** the new `src/ascii_chart.py` renders a vertical latency histogram and a token time series with pure functions, with no new dependencies; `MetricsApp` gained the **Events** and **Charts & Trends** tabs, with `1`/`2` shortcuts ([ADR-0018](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
- **The prompt became more resistant to binaries:** `collapse_binary_hunks()` now **also** runs at the start of `generate_pr_content()`, and the smart excludes catalog gained `*.arrow`, `*.gguf`, `*.tflite` and `*.keras` — ML/data artifacts no longer reach the AI even when they come in through an external caller.
- **The extension release was decoupled from the rest of the release:** `extension.yml` gained the `github-release` job (`.vsix` attached to `ext-v*` releases) and the non-blocking `publish-marketplaces` job, which evaluates `VSCE_PAT` and `OVSX_PAT` **independently** and reports to `$GITHUB_STEP_SUMMARY`. The VSIX installation documentation (CLI and GUI) entered all 5 languages.
- **Cost and currency are no longer fixed:** the schema gained the `KIND_FLOAT` type, the `GITPR_METRICS_CURRENCY` key and **eight** `INPUT`/`OUTPUT` price fields per model; `DEFAULT_MODEL_PRICES` now also prices `gemini-pro-latest` and `gemini-flash-lite-latest`.
- **The migration notes were formalized:** the `action.yml` break to `gitpr check` ([ADR-0011](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0011-action-unified-check-migration.md)), the interactive commit without a TTY, the strict `GITPR_SKIP_UPDATE_CHECK` and the project configuration seed/opt-out in CI now have explicit documentation.
- **Three new ADRs closed pending contracts:** [ADR-0017](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0017-documentation-site-language-fallback.md) (site language fallback), [ADR-0018](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) (charts/tabs) and [ADR-0019](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0019-project-configuration-seed-segregation.md) (segregation between runtime auto-seed and explicit provisioning).
- **1.6.0 and 1.7.0 were published:** PyPI serves **1.7.0**. The previous window's release debt is closed, and `develop_natan` was merged into `main` by PR #310.

## How to Use

1.7.0 is published on PyPI. Update with:

```
pip install --upgrade gitpr-cli
```

The metrics dashboard now has two tabs: `gitpr metrics dashboard` opens on **Events**, and `2` switches to **Charts & Trends**, where the vertical latency histogram and the token time series are drawn in the terminal. `1` returns to the events table, `F5` refreshes and `Esc` exits.

To report cost in your own currency, set `GITPR_METRICS_CURRENCY` (for example `EUR`) and fill the `INPUT`/`OUTPUT` price fields per model; the dashboard and `gitpr metrics` read the same values.

To install the editor extension, download the `.vsix` from GitHub Releases (tag `ext-v*`) and install it in VS Code or Cursor — no marketplace account needed.

## Useful Tips

`gitpr metrics dashboard` opens a local TUI with cost, quality, modules, providers and pull-request cycle time (F5 refresh, Esc exit), and `gitpr metrics export` writes only the rows never exported before to CSV and JSON. There is no server: bundle a window with `gitpr metrics bundle` and merge the team's files with `gitpr metrics merge` to total a quarter without leaving your own disk.
