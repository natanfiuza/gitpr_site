# GitPR 1.9.0 — What's New

## What's New in This Version

- **GitHub Enterprise stopped being a dead end:** `GitHubProvider.parse_repo_ref` dropped the regex anchored to the literal `github.com` and switched to a generic `_REMOTE_RE` (optional `user@`, any `host[:port]`, `:` or `/` separator, and two path segments). Enterprise remotes (`github.acme.com` over HTTP/SSH/custom port) now parse cleanly, while `GITHUB_BASE_URL` continues to retarget the API.
- **VS Code extension language coverage closed at five:** `package.nls.pt-pt.json` (14 keys) and `l10n/bundle.l10n.pt-pt.json` (26 keys) joined `en`, `pt-br`, `es`, and `fr`, completing European Portuguese support across the editor extension.
- **100% documentation translation parity at `docs/` root:** `_EN_ONLY` was emptied in `tests/unit/test_docs_translation_parity.py`, `docs/github-issue-prompt-com-gh.md` moved to `docs/extra/`, and the root of `docs/` reached **51/51 topics across all five languages**.
- **Executable documentation contract for ADR-0019:** `tests/unit/test_project_config_seed_docs.py` enforces section `9.4`, the `[!IMPORTANT]` callout, and `GITPR_SKIP_PROJECT_CONFIG_SEED` across all five variants of `pull-request-publication` and `config-tui` ([ADR-0019](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0019-project-config-seed-opt-out-and-documentation-contract.md)).
- **Ephemeral SCM provisioning documented across all five languages:** `persisted=False`, `os.environ` fallback, and `PermissionError` handling in read-only or ephemeral environments are documented in `docs/scm-multiforge.*` and [ADR-0020](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0020-per-repo-scm-provisioning-migration.md).
- **Map-Reduce cost and latency became observable outside the code:** `GITPR_MAP_REDUCE_MAX_TOKENS` / `MAX_PART_TOKENS` and telemetry fields (`map_reduce`, `chunks_count`, `duration_ms`) entered all five languages of `docs/metricas-telemetria.*` and [ADR-0022](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0022-map-reduce-per-provider-token-partitioning.md).
- **`gitpr-mcp --install <editor>` now lists `claude-code`:** the unknown-editor message in `_install_for_editor` lists `vscode, cursor, claude-code, claude, zed, auto`, and all six `langs/` dictionaries were synchronized (**v0.0.44** via OTA).
- **1.9.0 was published:** `__version__` = **1.9.0**, `CHANGELOG.md` with `## [1.9.0] - 2026-11-15`, tag `v1.9.0`, and PyPI serving **1.9.0**.

## How to Use

Update to version 1.9.0 via pip:

```bash
pip install --upgrade gitpr-cli
```

Point GitPR to a GitHub Enterprise instance in `~/.gitpr/.env` (remotes on custom hosts now parse automatically):

```bash
GITHUB_BASE_URL=https://github.acme.com/api/v3
```

Install the MCP server for Claude Code (or any supported editor) and optionally skip project-config seeding in CI/CD pipelines:

```bash
gitpr-mcp --install claude-code
GITPR_SKIP_PROJECT_CONFIG_SEED=true gitpr --pr
```

## Useful Tips

- Hidden debug flag: run any AI command with `--pre-save` (e.g. `gitpr -c --pre-save`) and GitPR dumps the full payload — system instruction, prompt and character counters — to a JSON file in the current folder. No file is generated when the answer comes from cache.
