# GitPR 1.9.0 — Novidades

## Novidades desta versão

- **O GitHub Enterprise deixou de ser um beco sem saída:** `GitHubProvider.parse_repo_ref` abandonou o regex ancorado no literal `github.com` e passou a usar um `_REMOTE_RE` genérico (`user@` opcional, qualquer `host[:port]`, separador `:` ou `/` e dois segmentos de caminho). Remotes corporativos (`github.acme.com` via HTTP/SSH/porta customizada) agora parseiam normalmente, enquanto `GITHUB_BASE_URL` continua redirecionando a API.
- **A cobertura de idiomas da extensão VS Code fechou em cinco:** `package.nls.pt-pt.json` (14 chaves) e `l10n/bundle.l10n.pt-pt.json` (26 chaves) juntaram-se a `en`, `pt-br`, `es` e `fr`, completando o suporte em português europeu na extensão.
- **100% de paridade de tradução na raiz de `docs/`:** `_EN_ONLY` foi esvaziado em `tests/unit/test_docs_translation_parity.py`, `docs/github-issue-prompt-com-gh.md` foi movido para `docs/extra/` e a raiz de `docs/` chegou a **51/51 tópicos nos cinco idiomas**.
- **Contrato executável de documentação para a ADR-0019:** `tests/unit/test_project_config_seed_docs.py` valida a seção `9.4`, o callout `[!IMPORTANT]` e `GITPR_SKIP_PROJECT_CONFIG_SEED` nas cinco variantes de `pull-request-publication` e `config-tui` ([ADR-0019](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0019-project-config-seed-opt-out-and-documentation-contract.md)).
- **Migração SCM em ambiente efêmero documentada nos cinco idiomas:** `persisted=False`, fallback em `os.environ` e tratamento de `PermissionError` em ambientes somente leitura estão documentados em `docs/scm-multiforge.*` e na [ADR-0020](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0020-per-repo-scm-provisioning-migration.md).
- **Custo e latência do Map-Reduce agora são observáveis fora do código:** `GITPR_MAP_REDUCE_MAX_TOKENS` / `MAX_PART_TOKENS` e os campos de telemetria (`map_reduce`, `chunks_count`, `duration_ms`) entraram nos cinco idiomas de `docs/metricas-telemetria.*` e na [ADR-0022](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0022-map-reduce-per-provider-token-partitioning.md).
- **`gitpr-mcp --install <editor>` passou a listar `claude-code`:** a mensagem de editor desconhecido em `_install_for_editor` lista `vscode, cursor, claude-code, claude, zed, auto`, e todos os seis dicionários de `langs/` foram sincronizados (**v0.0.44** via OTA).
- **1.9.0 publicada:** `__version__` = **1.9.0**, `CHANGELOG.md` com `## [1.9.0] - 2026-11-15`, tag `v1.9.0` e PyPI servindo **1.9.0**.

## Como usar

Atualize para a versão 1.9.0 via pip:

```bash
pip install --upgrade gitpr-cli
```

Aponte o GitPR para uma instância GitHub Enterprise em `~/.gitpr/.env` (remotes com hosts customizados agora são reconhecidos automaticamente):

```bash
GITHUB_BASE_URL=https://github.acme.com/api/v3
```

Instale o servidor MCP no Claude Code (ou outro editor suportado) e, se necessário em pipelines de CI/CD, desative o seed de configuração de projeto:

```bash
gitpr-mcp --install claude-code
GITPR_SKIP_PROJECT_CONFIG_SEED=true gitpr --pr
```

## Dicas úteis

- Flag de debug escondida: rode qualquer comando de IA com `--pre-save` (ex.: `gitpr -c --pre-save`) e o GitPR grava o payload completo — instrução de sistema, prompt e contadores de caracteres — num arquivo JSON na pasta atual. Nenhum arquivo é gerado quando a resposta vem do cache.
