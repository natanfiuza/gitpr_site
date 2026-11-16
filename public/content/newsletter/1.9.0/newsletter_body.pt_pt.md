# GitPR 1.9.0 — Novidades

## Novidades desta versão

- **O GitHub Enterprise deixou de ser rejeitado pelo `parse_repo_ref`:** `GitHubProvider.parse_repo_ref` abandonou o regex rígido `github.com` e passou a usar um `_REMOTE_RE` genérico (`user@` opcional, qualquer `host[:port]`, separador `:` ou `/` e dois segmentos de caminho). Remotes corporativos (`github.acme.com` via HTTP/SSH/porta personalizada) agora funcionam com `GITHUB_BASE_URL`.
- **A extensão de editor ganhou tradução `pt-pt`:** `extensions/vscode/package.nls.pt-pt.json` (14 chaves) e `extensions/vscode/l10n/bundle.l10n.pt-pt.json` (26 chaves) juntaram-se a `en`, `pt-br`, `es` e `fr`, fechando a cobertura em **5 idiomas**.
- **Paridade de tradução a 100% na raiz `docs/`:** a lista `_EN_ONLY` em `tests/unit/test_docs_translation_parity.py` foi esvaziada, `docs/github-issue-prompt-com-gh.md` foi movido para `docs/extra/` e a raiz de `docs/` tem agora **51/51 tópicos nos cinco idiomas**.
- **Testes de contrato para a ADR-0019 na documentação:** `tests/unit/test_project_config_seed_docs.py` valida a secção `9.4`, o aviso `[!IMPORTANT]` e `GITPR_SKIP_PROJECT_CONFIG_SEED` nas cinco variantes de `pull-request-publication` e `config-tui` ([ADR-0019](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0019-project-config-seed-opt-out-and-documentation-contract.md)).
- **Fallback para ambiente só de leitura / efémero no SCM documentado nos 5 idiomas:** `persisted=False`, fallback em `os.environ` e `PermissionError` estão documentados em `docs/scm-multiforge.*` e na [ADR-0020](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0020-per-repo-scm-provisioning-migration.md).
- **Tradeoffs de custo e latência do Map-Reduce documentados nos 5 idiomas:** `GITPR_MAP_REDUCE_MAX_TOKENS` / `MAX_PART_TOKENS` e os campos de telemetria (`map_reduce`, `chunks_count`, `duration_ms`) entraram em `docs/metricas-telemetria.*` e na [ADR-0022](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0022-map-reduce-per-provider-token-partitioning.md).
- **`gitpr-mcp --install <editor>` agora lista `claude-code`:** a mensagem de editor desconhecido em `_install_for_editor` lista `vscode, cursor, claude-code, claude, zed, auto`, com todos os 6 dicionários sincronizados (**v0.0.44** via OTA).
- **1.9.0 publicada:** `__version__` = **1.9.0**, `CHANGELOG.md` com `## [1.9.0] - 2026-11-15`, tag `v1.9.0` e PyPI a servir a **1.9.0**.

## Como usar

Atualize para a versão 1.9.0 via pip:

```bash
pip install --upgrade gitpr-cli
```

Aponte o GitPR para uma instância GitHub Enterprise em `~/.gitpr/.env` (remotes com hosts personalizados são agora analisados automaticamente):

```bash
GITHUB_BASE_URL=https://github.acme.com/api/v3
```

Instale o servidor MCP para o Claude Code (ou outro editor suportado) e, se necessário em pipelines de CI/CD, desative o seed da configuração de projeto:

```bash
gitpr-mcp --install claude-code
GITPR_SKIP_PROJECT_CONFIG_SEED=true gitpr --pr
```

## Dicas úteis

- Flag de debug escondida: execute qualquer comando de IA com `--pre-save` (ex.: `gitpr -c --pre-save`) e o GitPR grava o payload completo — instrução de sistema, prompt e contadores de caracteres — num ficheiro JSON na pasta atual. Nenhum ficheiro é gerado quando a resposta vem da cache.
