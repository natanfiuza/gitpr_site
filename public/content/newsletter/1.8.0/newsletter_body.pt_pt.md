# GitPR 1.8.0 — Novidades

## Novidades desta versão

- **O Map-Reduce deixou de ter um teto único:** o `get_map_reduce_thresholds(provider)` resolve o gatilho e o tamanho de lote por provedor (`_GEMINI`, `_DEEPSEEK`, `_OLLAMA` → genérico → 90.000/50.000), e o fatiador tornou-se uma **cascata**: ficheiro → hunks (`@@`) → linhas, preservando sempre o cabeçalho e o marcador `[file - part N]`. Um pré-reduce intermédio consolida resumos parciais que ainda excedam o gatilho ([ADR-0022](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0022-map-reduce-per-provider-token-partitioning.md)).
- **A deteção de forge foi corrigida e o fallback silencioso passou a aviso:** o `github.com` passou a ser reconhecido **antes** do retorno de `default=None` — sem isso, todo o repositório GitHub era tratado como host desconhecido e recebia um `scm.provider` espúrio. Quando o remote existe mas não nomeia uma forge conhecida, o `resolve_scm_settings` agora avisa e aponta para `gitpr --init`.
- **As notas de migração ganharam contrato:** a [ADR-0020](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0020-per-repo-scm-provisioning-migration.md) formaliza o provisionamento SCM por repositório (scripts e imagens não devem gravar as chaves legadas), e a [ADR-0021](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0021-strict-boolean-update-check-migration.md) fecha a semântica estrita de `GITPR_SKIP_UPDATE_CHECK`.
- **A extensão de editor ganhou um caminho de publicação reproduzível:** o guia de marketplace foi traduzido para os **cinco idiomas**, e o `scripts/marketplace-setup-wizard.sh` conduz as cinco etapas de credenciais (PAT do Azure DevOps, namespace do Open VSX, secrets do GitHub Actions) validando cada token contra a API real antes de o gravar.
- **A documentação de métricas e do Map-Reduce foi sincronizada:** o separador **Charts & Trends**, o Token Trend e o Latency Histogram entraram nos cinco idiomas, com uma guarda de paridade de tradução que impede que uma edição apenas-EN passe despercebida.
- **A suite ficou estável no Windows:** o fluxo headless da `CommitApp` é saltado em `win32` (o `pilot.pause` do Textual bloqueia sem TTY real) e o routing da CLI simula o `check_internet_connection`, eliminando a dependência de rede.
- **A 1.8.0 foi publicada:** `__version__` = **1.8.0**, `CHANGELOG.md` com `## [1.8.0] - 2026-11-14`, tag `v1.8.0` e PyPI a servir a 1.8.0.

## Como usar

Atualize para a versão 1.8.0 via pip:

```bash
pip install --upgrade gitpr-cli
```

Ajuste os limites do Map-Reduce por fornecedor de IA em `~/.gitpr/.env` (ou interativamente via `gitpr config` na categoria `ai`):

```bash
# Exemplo: gatilho e tamanho de lote personalizados para Ollama vs. Gemini
GITPR_MAP_REDUCE_MAX_TOKENS_OLLAMA=16000
GITPR_MAP_REDUCE_MAX_PART_TOKENS_OLLAMA=8000
GITPR_MAP_REDUCE_MAX_TOKENS_GEMINI=120000
GITPR_MAP_REDUCE_MAX_PART_TOKENS_GEMINI=60000
```

Se o `resolve_scm_settings` avisar que o host do remote `origin` não é reconhecido, associe explicitamente o fornecedor SCM e as credenciais do repositório:

```bash
gitpr --init
```

## Dicas úteis

- O `gitpr config` abre um ecrã mestre-detalhe sobre o `~/.gitpr/.env`, por isso ninguém precisa de memorizar se o valor é `true`, `1` ou `yes`: cada campo recebe o controlo que o seu tipo merece e um valor inválido torna-se difícil de escrever. F2 valida e guarda, Ctrl+R repõe a predefinição (remove a linha), `/` procura rótulos e variáveis em todas as categorias, e Esc sai perguntando antes de descartar. Edita o mesmo ficheiro que já tinhas — não é uma segunda fonte de configuração.
