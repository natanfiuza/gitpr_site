# **🚀 Relatório de Status do Projeto: GitPR CLI — v0.0.24 (2026-11-10)**

## **📌 Visão Geral**

O **GitPR** é uma ferramenta de CLI (Command Line Interface) avançada para automação de processos Git utilizando Inteligência Artificial (Google Gemini / DeepSeek / Ollama). O objetivo principal é atuar como um assistente inteligente local que faz Code Reviews, gera Pull Requests, mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo de trabalho do programador (Shift Left).

**Novidades desta versão (v0.0.24):**
- **O dashboard de métricas ganhou gráficos ASCII e navegação por separadores:** o novo `src/ascii_chart.py` renderiza um histograma vertical de latência e uma série temporal de tokens com funções puras, sem novas dependências; a `MetricsApp` ganhou os separadores **Events** e **Charts & Trends**, com os atalhos `1`/`2` ([ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
- **O prompt tornou-se mais resistente a binários:** `collapse_binary_hunks()` passa a correr **também** no início de `generate_pr_content()`, e o catálogo de smart excludes ganhou `*.arrow`, `*.gguf`, `*.tflite` e `*.keras` — artefactos de ML/dados deixam de chegar à IA mesmo quando entram por um chamador externo.
- **A release da extensão foi desacoplada do resto da release:** o `extension.yml` ganhou o job `github-release` (`.vsix` anexado aos releases `ext-v*`) e o job não-bloqueante `publish-marketplaces`, que avalia `VSCE_PAT` e `OVSX_PAT` **independentemente** e reporta para `$GITHUB_STEP_SUMMARY`. A documentação de instalação do VSIX (CLI e GUI) entrou nos 5 idiomas.
- **O custo e a moeda deixaram de ser fixos:** o schema ganhou o tipo `KIND_FLOAT`, a chave `GITPR_METRICS_CURRENCY` e **oito** campos de preço `INPUT`/`OUTPUT` por modelo; o `DEFAULT_MODEL_PRICES` passou também a atribuir preço a `gemini-pro-latest` e `gemini-flash-lite-latest`.
- **As notas de migração foram formalizadas:** a quebra do `action.yml` para `gitpr check` ([ADR-0011](docs/adr/0011-action-unified-check-migration.md)), o commit interativo sem TTY, o `GITPR_SKIP_UPDATE_CHECK` estrito e o seed/opt-out da configuração do projeto no CI passam a ter documentação explícita.
- **Três ADRs novas fecharam contratos pendentes:** [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md) (fallback de idioma do site), [ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) (gráficos/separadores) e [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) (segregação entre o auto-seed em runtime e o provisionamento explícito).
- **A 1.6.0 e a 1.7.0 foram publicadas:** o PyPI serve a **1.7.0**. O débito de release da janela anterior está fechado, e a `develop_natan` foi integrada na `main` pelo PR #310.
- **Versão atual:** 1.7.0 (**publicada no PyPI**; a tag `v1.7.0` na origin ainda aponta para o commit anterior ao bump — ver Distribuição)
- **Versão dos dicionários de idioma:** v0.0.42 (🆕 +1 desde a v0.0.41; entrega as 15 novas chaves por OTA)
- **Versão dos scripts de hook:** v0.0.4 (inalterada)
- **Publicação:** PyPI (`pip install gitpr-cli`) — canal binário removido
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositório:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licença:** LGPL-2.1
- **Idiomas suportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 dicionários)

---

## **🏗️ Arquitetura Base e Bibliotecas**

* **Linguagem:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags e formatação de terminal) — **16 subcomandos** (`badge`, `baseline`, `check`, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `seed`, `split`, `tests`). Quatro deles são **grupos**: `baseline` (6 subcomandos), `metrics` (8), `policy` (7) e `tests` (1). **34 opções Click na raiz** — inalteradas nesta janela.
* **UI/Terminal:** Textual — TUI para chat interativo, edição de issues, help screen, dashboard de métricas, PR Publisher, **CommitApp** (`src/ui/commit_app.py`, 220 linhas), erros do linter (`LinterApp`), configuração (`ConfigApp`), o modal `NoticeScreen`, o tour do `gitpr demo` (`src/ui/demo/`), o assistente de migração do ledger (`src/ui/metrics_migration_app.py`) e o `PrBaseApp` (`src/ui/pr_base_app.py`). 🆕 A `MetricsApp` passa a usar `TabbedContent` com os separadores **Events** e **Charts & Trends** e os bindings `1`/`2`.
* **Arquitetura em Camadas:** `src/domain/` (regra pura, sem I/O) + `src/application/use_cases/` (orquestração, **15 casos de uso**) + apresentação (CLI/TUI). **9 pacotes de domínio**: `baseline`, `check`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`. Fora do domínio: `src/issue_autoclose/` (fluxo de fecho de issue sobre `ScmProvider` e o registo local), `src/project_config.py` (configuração versionada), `src/scm_settings.py` (resolução de forge e credencial por repositório) e 🆕 `src/ascii_chart.py` (renderização 2D pura, sem I/O).
* **Extensão de Editor:** `extensions/vscode/` — cliente TypeScript fino do MCP para **VS Code e Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). Não contém regra de negócio. 🆕 O workflow separa o release do VSIX (job `github-release`) da barreira granular de publicação nos marketplaces.
* **Criptografia:** `cryptography.fernet` para proteção local de chaves de API, tokens GitHub e tokens SCM das forges.
* **Configuração:** `python-dotenv`, `pyyaml` (linter estático e configuração do projeto) + schema declarativo próprio (`src/config_schema.py`, 🆕 **109 `ConfigField`** e **18 categorias**). O `DEFAULT_CONFIG` é **derivado** dos campos com `seeded=True` (🆕 **56 chaves**). 🆕 O tipo `KIND_FLOAT` foi acrescentado para preços e moeda. O `packaging` alimenta a comparação de versões do portão (PEP 440) e a compatibilidade dos policy packs.
* **Provedores de IA:** Integração via SDK oficial do Google GenAI, OpenAI SDK (DeepSeek) e OpenAI SDK (Ollama local). Predefinições configuradas: `gemini-pro-latest` / `gemini-flash-lite-latest` e `deepseek-v4-pro` / `deepseek-v4-flash`.
* **Forge APIs:** `requests` (REST) — abstração multi-forge em `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py`; `src/github_api.py` mantido como shim depreciado. O contrato de issue (`get_issue`, `get_default_branch`, `closing_line`) é implementado pelas **quatro** forges quando faz sentido.
* **Linter / SAST:** regex YAML + bridge Checkstyle + ruleset de segredos embutido (`src/security_ruleset.py`) + 4 bridges em `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), com modelo normalizado em `src/domain/linter/sast_finding_mapper.py` e o achado canónico em `src/domain/finding/`.
* **Infraestrutura de CI:** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) e `masking.py` (mascaramento de credenciais antes de serializar qualquer artefacto).
* **Git Internals:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py`, `risk_history_reader.py` e `test_matcher.py`.
* **Persistência local:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) com WAL, `PRAGMA user_version`, chaves UUID e transporte por bundle. A tabela de preços vive em `src/config.py` (`DEFAULT_MODEL_PRICES`) e aceita agora overrides por modelo e de moeda.
* **Rede:** `src/net.bounded_urlopen` (`UPDATE_FETCH_HARD_TIMEOUT = 3.0`) limita o **wall clock incluindo DNS** no portão de atualização. `get_issue()`/`get_default_branch()` usam timeout de **5 s** (`GITPR_ISSUE_AUTOCLOSE_TIMEOUT`).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 ferramentas anotadas**, **19 recursos**, 7 prompts (contagem inalterada; nenhum ficheiro do servidor mudou nesta janela).
* **Testes:** Pytest + `unittest.mock` (**160 módulos de teste** — 64 na raiz, 12 em `tests/application/use_cases/`, 10 em `tests/fix/`, 10 em `tests/scm/`, 10 em `tests/infrastructure/` (baseline 1, ci 3, git 2, linter 4), 21 em `tests/domain/` (baseline 5, check 3, finding 1, linter 2, mentor 1, policy 4, pr 1, risk 2, tests_generation 2), 7 em `tests/badge/`, 6 em `tests/demo/`, 6 em `tests/split/`, 5 em `tests/issue_autoclose/`, 4 em `tests/review/`, 3 em `tests/integration/`, 2 em `tests/unit/` —, **3436 cenários recolhidos**) + testes e2e do servidor MCP via subprocesso real (JSON-RPC stdio) + fixtures de repositório git real (`tests/fix/`, `tests/split/`, `tests/test_full_diff_encoding.py`) + guarda de isolamento de rede (`tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py`, `tests/split/conftest.py`). A extensão tem suite Mocha própria (7 testes contra o servidor MCP real).
* **Empacotamento:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; packs embutidos via `package-data`; `packaging>=23.0`. A extensão é empacotada separadamente (`vsce package`, VSIX).
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` (chama `gitpr check`) + `tests.yml` (matriz Python 3.10/3.13) + `extension.yml` (build, teste e empacotamento do VSIX; 🆕 release próprio em tags `ext-v*` e uma barreira granular por marketplace) + `release.yml` (testes, verificação tag/versão via SSOT, build sdist/wheel, `twine check --strict`, Trusted Publishing OIDC no PyPI e criação de GitHub Release em tags `v*`).

---

## **🧩 Módulos Implementados e Arquitetura de Ficheiros**

### **1. Operações Core e Git (`src/core.py`)**

* **Geração Estruturada:** Comunica com o LLM a pedir um retorno estritamente em JSON.
* **Map-Reduce (Diffs Gigantes):** Quando o diff ultrapassa ~90k tokens, divide-o automaticamente em lotes por ficheiro (`split_diff_into_chunks`), processa cada parte (Map) e unifica os resumos (Reduce). Suporta PRs, commits e Issues. O `provider`/`model` são agregados entre chunks e o `meta`/`modules` é encaminhado para o `log_command_metric`.
* **Tokenizer Local:** `tokenizer.json` para estimativa precisa de tokens antes do envio à IA.
* **Estimativa de Tokens:** Heurística leve `len() // 4` via `estimate_token_count()` com fallback para o tokenizer local.
* **Otimização Git Nativa:** Flags `-U1`, `-w`, `-M`, `-B` nos comandos `get_git_diff` e `get_git_full_diff`.
* **`get_git_staged_diff()`:** corre `git diff --cached -U1 -w -M -B -- <SMART_EXCLUDES>` com `encoding="utf-8"`/`errors="replace"` e `stdin=DEVNULL`, devolvendo `None` quando o git falha (nunca uma string vazia disfarçada). É a fonte preferida da mensagem de commit.
* **`get_split_diff()`:** diff próprio do `split`, com `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sem** reutilizar o `get_git_diff()`.
* **Guarda de Hunk Binário:** todos os pontos de entrada de diff (`get_git_diff`, `get_git_staged_diff`, `get_unstaged_diff`, `get_git_full_diff`, `current_diff`, `get_check_diff`, `tests_generate`, `risk`) aplicam `collapse_binary_hunks()` — hunks com corridas contíguas de ≥8 `U+FFFD` (ou ≥32 com densidade ≥10%) passam a `Binary files … differ`. O caminho rápido O(1) devolve o diff intacto quando não há `U+FFFD`, preservando ficheiros Latin-1/Windows-1252 legítimos.
* **🆕 Colapso Defensivo em `generate_pr_content()`:** a função aplica agora `collapse_binary_hunks(diff_text)` **antes** da temporização e da chamada à IA, cobrindo também os chamadores externos que entregam o diff em bruto — não apenas o caminho vindo do git.
* **🆕 Quatro Novas Extensões Binárias:** `*.arrow`, `*.gguf`, `*.tflite` e `*.keras` entraram tanto no fallback embutido de smart excludes como em `templates/gitpr.smart-excludes.json`.
* **Seed da Configuração Local (`seed_project_configuration()`):** cria `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` e `.gitpr/skill/.gitpr.linter.yml`, devolvendo um resumo estruturado (`config`, `smart_excludes`, `linter`, `skills_downloaded`). Nunca sobrescreve sem `force=True`.
* **Seed da Configuração do Projeto:** o callback raiz e o `--install` chamam `ensure_project_config()` (`src/project_config.py`) em cada invocação dentro de um repositório, exceto nos caminhos de ajuda. Um ficheiro existente **nunca** é sobrescrito; a criação é anunciada apenas no caminho interativo da raiz e pode ser desligada por `GITPR_SKIP_PROJECT_CONFIG_SEED` (escape hatch da suite de testes).
* **`get_base_branch(quiet=)`:** a resolução da base delega em `resolve_pull_request_base()`; o `quiet` evita que o aviso de fallback corrompa a TUI ou o stream JSON-RPC.
* **Pré-Gravação (`--pre-save`):** Flag de debug oculta que guarda o payload completo em JSON antes de cada chamada à IA.
* **Smart Excludes com Duas Camadas:** Camada global (`~/.gitpr/conf/`) + local do projeto (`./.gitpr/conf/`), unidas em runtime. Os loaders globais e locais foram blindados contra `.env` corrompido e paths ilegíveis.
* **Métricas com Rastreio Temporal:** `log_command_metric()` em todos os fluxos, com `meta`, `modules` e `duration_ms`.
* **Resolução Centralizada do Output:** `resolve_output_path()` — predefinição em `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`)**: `gitpr --init` — deteta a forge pelo remote origin, pede os extras por forge, valida o token e persiste **só em caso de sucesso**; grava no slot da forge escolhida e só cria override no projeto quando ela difere da detetada.
* **Template de Skill de Release (`ensure_release_skill_template()`)**: Transfere `templates/gitpr.release.*.md` na primeira utilização do `gitpr release`.
* **Guarda de Skill Corrompida:** `_CORRUPTED_PR_SKILL_ARTIFACT` documenta o fragmento JSON que se colou ao template `pt_br`, e o `get_skill_context()` remove-o **em memória** quando `action_type == "pr"`.
* **Registo Partilhado de Skills:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` em `src/config.py`.
* **Motor de Review com Âmbito de Cache (`generate_pr_content`):** `cache_scope` e `store_diff`; o `cache_scope` transporta também o policy pack ativo.
* **Trailer de Coautoria:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceiros.
* **Captura de Saída do Git Blindada:** as chamadas `subprocess.run` em `src/cache.py`, `src/core.py`, `src/issue_engine.py` e `src/main.py` que capturam saída do git usam `encoding="utf-8"` **com** `errors="replace"` — incluindo o `get_git_staged_diff()`.
* **Carregamento de `.env` Resiliente:** o `load_dotenv()` em `core.py`, `i18n.py` e `spinner.py` corre dentro de `try/except`.
* **Falha do Git ≠ Diff Vazio:** `diff_text is None` sai com código 1; um diff genuinamente vazio sai com **0**; em `--hook` o caminho é fail-open.
* **Subprocessos Blindados:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` em todos os `subprocess.run`; verificação de conexão por socket antes das operações de rede.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitetura de Plugins:** Carrega plugins de `~/.gitpr/plugins/`, aplicando-se a **todos os projetos**.
* **Plugins de Linter (`linter/`):** Ficheiros `.yml` unidos ao `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`):** Ficheiros `.md` que estendem o contexto do sistema.
* **Factory Closures:** `get_linter_plugins` e `get_prompt_plugins` isolam estado entre sessões.
* **Comando `--plugins`:** Lista todos os plugins globais instalados com os seus tipos e paths.
* **Documentação Multilíngue:** `docs/plugins-system.md` em 5 idiomas.
* **Relação com os Policy Packs:** o pack é uma **terceira** camada de regras — os plugins pertencem à máquina, o `.gitpr.linter.yml` pertence ao projeto e o pack é versionado e commitado.

### **3. Interface CLI e Configuração (`src/main.py` e `src/config.py`)**

* **Configuração Inicial:** Deteta a primeira execução, cria `~/.gitpr/` e pede interativamente chaves, preferências e idioma.
* **Routing de Comandos:** Gere todas as flags e os **16 subcomandos** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline`, `check` e `seed`.
* **Comportamento Predefinido:** Executar `gitpr` sem flags abre a TUI do PR Publisher.
* **Flags (34 opções Click na raiz):** o `-sd/--seed` semeia a configuração local do projeto e sai; `--close-issue`/`--no-close-issue` continuam mutuamente exclusivas, recusadas com exit code 1 antes de qualquer prompt ou escrita, com a regra de que o `-h` vence o conflito. As restantes (`--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` etc.) continuam inalteradas.
* **Ramo do `-c`/`--commit`:** `--hook` → comportamento silencioso e merge-aware; sem hook → `CommitApp`. O caminho sem hook exige um TTY: o routing consulta `_may_ask_the_user()` e, num script/CI/pipe, cai no fluxo de consola.
* **Fluxo de Fecho de Issue:** `_prepare_close_issue(flags, base, may_ask)` corre **antes** do Stage Selector, reutiliza o contexto SCM resolvido e devolve a decisão (linha de fecho + issue) mais o registo. A linha entra em `insert_close_line(pr_desc_body, decision)` no ponto único de montagem do corpo. `_publish_pr_directly` devolve **bool** para que o registo só seja limpo depois de uma publicação real (ADR-007).
* **Resolução da Base do PR:** o ramo `--base` passa por `_resolve_pr_target()`, que corrige um valor obsoleto do ficheiro do projeto quando a execução é interativa e, fora dela, relata a recusa. O ficheiro `.gitpr/conf/gitpr.config.yml` é semeado em cada invocação dentro de um repositório, exceto nos caminhos de ajuda.
* **Portão de Atualização nos Subcomandos:** o portão corre antes do `return`, condicionado a `may_ask and not update and not help_flag and not _subcommand_help_in_argv()`. O `_may_ask_the_user()` exige um **TTY nas duas pontas** — CI, pipes, cron, hooks e o servidor MCP continuam livres.
* **`-h` Antes do Subcomando Corrigido:** `_subcommand_help_in_argv()` varre o `argv` bruto e trata o caso `gitpr -h config`, que antes abria a TUI de configuração em vez da ajuda. Coberto por `tests/test_cli_help.py`.
* **Subcomando `check` — 8 opções:** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on …`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` e `--policy <name>@<version>`.
* **Subcomando `seed` — 2 opções:** `--force/-f` (sobrescreve ficheiros existentes) e `--all/-a` (também transfere todas as skills de IA).
* **Grupo `baseline` — 6 subcomandos:** `create`, `show`, `validate`, `update`, `suppress` e `unsuppress`.
* **Grupo `policy` — 7 subcomandos:** `list`, `show`, `validate`, `use`, `off`, `install` e `init`.
* **Grupo `metrics` — 8 subcomandos:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` e o oculto `hook-event`.
* **Subcomando `risk` — 4 opções:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Subcomando `mentor` — 2 opções:** `--finding`, `--provider`.
* **`DEFAULT_CONFIG` Derivado:** `src/config.py` importa o `DEFAULT_CONFIG` de `src/config_schema.py`, montado como `{field.key: field.default for field in FIELDS if field.seeded}` — 🆕 **56 chaves** (eram 55). O drift entre código e schema é impossível.
* **🆕 Preços e Moeda no Schema:** `GITPR_METRICS_CURRENCY` (predefinição `USD`) e oito campos `GITPR_METRICS_PRICE_<MODEL>_{INPUT,OUTPUT}` foram acrescentados à categoria `ai`, todos com `KIND_FLOAT` e predefinição vazia.
* **Variáveis de Ambiente:** as quatro `GITPR_BASELINE_*`, as duas de mentor, as duas de policy e `GITPR_RISK_INCLUDE_IN_REVIEW`. **Fora do `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY`, as **9 `GITPR_CHECK_*`**, `GITPR_SKIP_UPDATE_CHECK` (booleano estrito) e `GITPR_ISSUE_AUTOCLOSE`, `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` e `GITPR_SKIP_PROJECT_CONFIG_SEED`.
* **Ajuda Contextual:** `gitpr -h --explain` renderiza; `HELP_MAP["explain"]` e `HELP_PRIORITY["explain"]` apontam para `docs/explain-pr`, e `gitpr tests`/`gitpr tests generate` têm `epilog=` próprio. O `HELP_MAP` inclui o `seed` e os links de `close_issue`/`no_close_issue` apontam para `docs/issue-autoclose`.
* **Camada de Escrita do `.env`:** `read_env_file_values()` lê **só o ficheiro** via `dotenv_values`; `save_config_values()` com `set_key`; `remove_config_value()` com `unset_key`.
* **Portão do Baseline no Fluxo:** o `main.py` resolve o portão do baseline antes do lint, do review, do full review, da auditoria de ficheiros, do review remoto e do risco.
* **Publicação Direta Refatorada:** o `_publish_pr_directly` (`--no-edit`) delega no **`publish_pull_request_use_case`** — o mesmo caminho da ferramenta MCP `publish_pull_request` — e devolve se a forge criou o PR.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` e `src/ui/pr_publish_help.py`)**

* **Interface Interativa Completa:** TUI para rever, editar e publicar Pull Requests diretamente no terminal.
* **7 Ecrãs Modais:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` e `NoticeScreen`.
* **Três Delas Também Servem o Commit:** o `StageFilesScreen`, o `CommitMessageScreen` e o `CommitProgressScreen` são importados pelo `CommitApp` — nenhum widget foi duplicado, e o helper `_with_suppressed_stdout` também é partilhado.
* **`body_published` Distingue Criar de Abrir:** a `PrPublishApp` marca quando o corpo foi realmente criado/atualizado na forge, e não apenas quando um PR existente foi aberto. É esse sinal que permite limpar o registo da issue só depois de uma publicação a sério.
* **Selo no Corpo do PR:** o `attach_pr_badge()` corre em `src/main.py` logo depois de o `pr_data` estar completo — **ponto único de injeção**.
* **`_attach_reviewers` com resolução:** Resolve o que o utilizador escreveu **antes** de enviar, reporta o que foi descartado e, num `422` de lote, **repete um a um**. Quando um provedor de terceiros segue o contrato legado e devolve `None` no read-back, o log diz `(no read-back)` em vez de `attached None`.
* **Revisores Sugeridos:** Consulta a forge por revisores sugeridos; o `_reviewer_suggestion_view()` constrói as `resolutions` via `resolve_candidates`.
* **Bindings:** F1 (Ajuda), F2 (Guardar .md local), F3 (Publicar via forge), Esc (Sair).
* **Fluxo de Auto-Commit:** Linter → mensagem por IA → confirmação → commit → push → publicação do PR.
* **Modal de Progresso no Merge:** `CommitProgressScreen` com `title` opcional e `mark_finished(show_close=False)`; o sucesso mostra `✅ PR #N merged successfully!`, os conflitos e erros inesperados passam pelo `_on_merge_failure`.
* **Tratamento de PR Existente / Auto-Upstream / Merge Flow** — inalterados (`GITPR_AUTO_MERGE`).

### **5. Módulo de API do GitHub (`src/github_api.py`)**

* **Shim Obsoleto:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` e as restantes funções delegam em `src/infrastructure/scm/github_provider.py`; emite um `DeprecationWarning` e mantém os tuplos legacy `(ok, data, status)` — nenhum código novo o pode importar.

### **6. Motor de Análise Estática / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analisa estaticamente as linhas adicionadas (`+`) no diff sem gastar quotas de IA.
* **Regras YAML:** Lê `.gitpr.linter.yml` (criado via `--skill` ou `gitpr seed`).
* **Plugins de Linter:** Regras adicionais carregadas de `~/.gitpr/plugins/linter/*.yml`.
* **Wildcard de Extensão:** `extensions: ["*"]` significa **todos** os ficheiros, incluindo os que não têm sufixo e os dotfiles.
* **Conjunto de Regras de Segredos Embutido:** O `load_linter_rules()` junta o `src/security_ruleset.py` **depois** das regras do projeto e dos plugins. Sete regras, todas `extensions: ["*"]`: cinco `error` e dois `warning`. O alerta **nunca ecoa o valor**.
* **Camada de Policy Pack:** o `load_linter_rules()` aplica a fusão em camadas do pack ativo e **sobrepõe severidades**.
* **Apontamentos Estruturados (`findings_out`):** além das strings de alerta, o motor devolve os apontamentos normalizados do tipo canónico `src/domain/finding/`.
* **Bridges SAST Opt-In:** O Semgrep, o Gitleaks e o Bandit correm no caminho de ficheiro inteiro **e** no caminho de diff, com dedup `[Gitleaks + Regex]`, mascaramento do valor (`AKIA****`) e um aviso quando a ferramenta está ativada mas fora do `PATH`.
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=True)` no fluxo remoto.
* **Relatório Consolidado:** O `generate_linter_report_content()` consolida erros regex + externos em `.gitpr/reports/linter/` — gerado apenas quando há violações.
* **Consciente do Baseline:** os apontamentos são anotados com o estado do baseline antes de se tornarem relatório.

### **7. Segurança e Autenticação (`src/security.py`, `src/scm_settings.py`, `src/tui_issue.py`)**

* **Criptografia:** Gera a chave mestra `secret.key` em `~/.gitpr/`.
* **Proteção de Tokens:** `encrypt_data`/`decrypt_data` para chaves de IA e para o token de **cada** forge (um slot por provedor SCM).
* **Credencial por Forge:** um token GitLab nunca responde por um repositório GitHub. O `GITPR_SCM_TOKEN` cru continua a ser o **único** override global de CI/CD e é tentado primeiro em todas as forges.
* **Validação Multi-Forge:** `validate_or_request_scm_token(provider, repo_display, allow_prompt=True)` — 401 → loop de reautenticação preservando o rascunho. `allow_prompt=False` (hooks, MCP, CI) nunca pergunta, nunca apaga o token guardado e devolve a instrução "corra `gitpr --init`".
* **Segredos na TUI de Configuração:** Os campos `KIND_SECRET` são editados mascarados, **nunca** apresentam o valor em claro e são encriptados com Fernet antes de serem escritos.
* **Deteção de Segredos no Fluxo:** O linter — que corre no hook de pre-commit — é uma barreira de credenciais com regras idênticas em todas as máquinas.
* **Mascaramento no CI:** o `src/infrastructure/ci/masking.py` remove chaves privadas, AWS access key ID/secret, tokens GitHub, Google, OpenAI e Slack, `Bearer`, basic-auth em URLs e atribuições genéricas de password **antes** de o artefacto ser serializado. A extensão tem um espelho em TypeScript (`masking.ts`).

### **8. Auto-Atualizador (`src/updater.py`)**

* **PyPI como Fonte Única:** o `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` e escreve a cache. Aceita `force=True`, usado pelo `--update` para **ir sempre** à rede.
* **Janela Deslizante de 1 Hora (`UPDATE_CACHE_TTL_SECONDS = 3600`):** o documento é `{"checked_at": <ISO>, "version": ...}`; o `_cache_is_fresh()` aceita uma idade em `[0, 3600)` segundos.
* **Validação na Leitura:** um `dict` genuíno, um timestamp interpretável e uma versão em string não vazia. Um timestamp **no futuro** conta como expirado, e o formato antigo com `date` é tratado como expirado e reescrito.
* **Escrita Atómica:** `tempfile.NamedTemporaryFile` no mesmo diretório + `os.replace`, com limpeza do ficheiro temporário em caso de falha.
* **Obtenção Blindada (`bounded_urlopen`):** um corpo não-JSON e um payload sem versão são **rejeitados antes de entrarem na cache**. Um teto de 3 s de wall clock, com o DNS incluído.
* **Comparação PEP 440:** o `_coerce_version()` usa o `packaging.version.Version`, tolerando o `v` inicial e os sufixos PEP 440; o `parse_version()` mantém o contrato de tuplo para os chamadores externos. O `_is_newer()` **ignora um pré-release remoto** numa instalação estável e trata uma versão ilegível de qualquer dos lados como desconhecida.
* **Bloqueio Obrigatório (`enforce_update_required()`):** Devolve `True` (depois de imprimir as duas versões e o comando pip) quando a versão publicada é mais recente; `False` quando está atualizado, quando a versão remota é **desconhecida (offline)** ou quando a verificação está desligada.
* **Semântica Estrita da Válvula de Escape:** o `GITPR_SKIP_UPDATE_CHECK` é interpretado contra `_SKIP_TRUTHY_VALUES` (`1`, `true`, `yes`, `on`, `y`). A nota de migração desta semântica entrou em `docs/auto-update.*` e no tutorial de instalação.
* **`check_and_update()`:** Apenas consulta e **informa**, nunca instala.
* **Versionamento Centralizado:** `__version__` (**1.7.0** 🆕), `__lang_version__` (**v0.0.42** 🆕), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`. O SSOT do pacote é contrato explícito ([ADR-014](docs/adr/0014-canonical-package-version-ssot.md)).
* **✅ Estado do release — 1.6.0 e 1.7.0 publicadas:** o PyPI serve a **1.7.0**. A **1.6.0** foi tagueada em `f5088c4`; a **1.7.0** tem secção própria no `CHANGELOG.md` (`## [1.7.0] - 2026-11-10`) e um bump em `2354a30`. ⚠️ A tag `v1.7.0` na origin ainda aponta para `b2688c4` (anterior ao bump, `__version__` 1.6.0) — alinhar a tag é o item de release pendente.
* **⚠️ Consistência do SSOT:** o bump `2354a30` alterou apenas `src/updater.py`; o `CLAUDE.md` e o `GEMINI.md` continuam a dizer 1.6.0, pelo que o `tests/unit/test_version_consistency.py` tem **2 falhas** (`test_claude_md_version_matches_ssot` e `test_gemini_md_version_matches_ssot`).

### **9. Interface de Chat Interativa (`src/ui/chat_app.py`)**

* **TUI Completa:** Histórico de mensagens, input multi-linha, barra de estado com bindings visíveis. `ENABLE_COMMAND_PALETTE = False`.
* **Memória por Branch (`src/chat_memory.py`):** Histórico persistido por branch, com continuidade entre sessões.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`. O `/tests` (e os aliases localizados) delega no **mesmo caso de uso** que a CLI usa.
* **Auto-Patching (F5), Atualização de Diff (F2), Exportação de Sessão (F6).**
* **Extrator Partilhado:** O F5 e o `ctrl+s` chamam `patch_extractor.extract_code_blocks()` — comportamento visível idêntico, sem lógica duplicada.
* **Ajuda Localizada a Sério:** o `ChatHelpScreen` compõe cada linha via `__()`. Os links da ajuda passam por `doc_url()`, com o contrato `?lang=` do site (`es_es`→`es`, `fr_fr`→`fr`) — formalizado na [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md).

### **10. Internacionalização — i18n (`src/i18n.py`)**

* **Sistema Inspirado no Laravel:** Função `__()` com placeholders nomeados (`{count}`, `{file}`, etc.).
* **Deteção Automática:** Deteta o idioma do SO na primeira execução e guarda-o em `GITPR_LANG`; o diretório do perfil é criado **antes** de persistir.
* **5 Idiomas, 6 Dicionários:** en_us (predefinição/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Ficheiros Versionados:** o `__lang_version__` (**v0.0.42** 🆕) controla a atualização dos pacotes (`langs/*.json`).
* **✅ Cobertura:** os **seis** ficheiros têm **1787 chaves** cada (+15). O código usa as mesmas chaves; não há chave sem dicionário nem órfã. O `tests/test_i18n.py` continua **22 verdes**.
* **🆕 A v0.0.42 tem conteúdo:** as novas strings de preços por token, moeda, validação `KIND_FLOAT`, gráficos/separadores ASCII e instruções de instalação do VSIX entraram nos seis dicionários; a renovação OTA entrega tradução nova.
* **Snapshot do Ambiente (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado **imediatamente antes** do `load_dotenv()` ao nível do módulo.
* **Cache com Indexação por Idioma:** As respostas de IA em cache incluem o idioma corrente no chaveamento MD5.
* **Fonte de Tradução em Lockstep:** uma chave nova tem de existir no código, em `langs/pt_br.json` (lista mestra), nos dicts FR/ES de `scripts/sync_all_langs.py` e nos valores curados de `scripts/fix_mangled_i18n_keys.py`; o extrator usa **AST**.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Thread em background durante chamadas de IA com caracteres braille e palavras de "pensamento".
* **263 entradas por idioma:** Sincronizadas entre os 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceitam `force=`.
* **A TUI de Commit Não Usa o Spinner:** a TUI mostra o `CommitProgressScreen` com a sua própria barra animada, e a chamada à IA corre numa `threading.Thread` daemon. Em `--hook` o spinner clássico continua a ser o único feedback.
* **Carregamento de `.env` Blindado:** uma falha de leitura não impede o spinner de iniciar.

### **12. Provedores de IA (`src/ai_providers.py`)**

* **3 Provedores Suportados:** Google Gemini, DeepSeek e Ollama (local), com um primário/secundário por provedor.
* **Modo JSON & Parâmetros Determinísticos:** `temperature=0.0` e `top_p=0.1`; fallback automático entre os provedores configurados.
* **Timeout de IA:** predefinição de **180s** (`GITPR_AI_TIMEOUT`).
* **Chamadas com `quiet`:** a release e as funcionalidades novas encaminham o `quiet` até à camada de IA, para que o spinner nunca corrompa uma saída `--format json`.
* **Uma Chamada de Cada Vez, com Cache:** o mentor e o explain reutilizam a cache MD5 por prompt.
* **Payload Já Interpretado:** o `parse_explain_payload()` e o `generate_test_file` aceitam o **dict** que o provedor entrega, com fallback para a string JSON em bruto (respostas antigas da cache).
* **Supressão de Stdout no Commit:** `_generate_commit_msg()` redireciona o `sys.stdout` para `os.devnull` durante a chamada, restaurando no `finally`.

### **13. Cache Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Chaveamento por hash MD5 do diff e do prompt, com indexação por idioma.
* **Seleção do Último Review (`resolve_last_review()`):** Escolhe o registo `review`/`fullreview` **mais recente** para um par repo+branch — é a porta de entrada do `gitpr fix` e do `gitpr mentor`.
* **Diff Revisto (`reviewed_diff`):** Campo no topo do registo que guarda o diff efetivamente revisto.
* **Telemetria e Duração:** Persistência de `duration_ms` e `meta_raw`. A release passa agora a chave de cache por idioma declarado, pelo que mudar a língua da skill gera uma secção nova em vez de reutilizar a antiga.
* **Leitura para o Dashboard:** o `scan_cache_files_for_dashboard()` é uma **ponte pré-migração**.
* **Efeito da Sanitização de Skill:** como o `get_skill_context()` remove o fragmento corrompido do prompt de PR, a chave MD5 muda e as respostas antigas contaminadas deixam de ser reutilizadas.
* **`git config` Descodificado com Segurança:** as leituras de `user.name`/`user.email` usam `encoding="utf-8"` + `errors="replace"`.
* **O Commit Reutiliza a Cache:** a TUI chama `generate_pr_content("commit", "commit", diff_text, provider)`, pelo que a mesma mensagem para o mesmo diff não dispara uma segunda chamada à IA.

### **14. Motor de Issues e TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff atual, Histórico da branch (`-ht`) e Arqueologia por Blame (`-b`).
* **Publicação Multi-Forge:** o F3 cria a issue na forge **resolvida para o repositório** via `provider.create_issue` — o Azure DevOps levanta `ScmNotSupportedError`.
* **Map-Reduce para Issues:** Contexto acima de ~90k tokens é dividido e unificado.
* **Tratamento de 401:** Sinalização de reautenticação sem fechar a aplicação.
* **`git remote -v` Descodificado com Segurança:** a chamada usa `encoding="utf-8"` + `errors="replace"`.
* **Registo Agnóstico ao Provedor:** após uma criação bem-sucedida em qualquer forge suportada, `register_created_issue()` grava o número **e o provedor** em `.gitpr/conf/issue.yml`. As falhas de bookkeeping são engolidas — um problema local nunca transforma uma criação bem-sucedida em erro — e a primeira escrita acrescenta o ficheiro ao `.gitignore`.
* **Métrica por Forge:** o evento de criação é derivado dinamicamente (`issue:<provider>_create`) em vez de ser fixo no GitHub.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Acompanha a evolução e a autoria histórica com classificação de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registados via `log_blame_metric()` com profundidade e número de commits analisados.
* **Passou também a alimentar o risco:** o leitor de histórico da pontuação (`risk_history_reader.py`) usa a mesma janela de 90 dias e classifica bugs/reverts por padrão, sem chamar a IA.
* **Alimenta a Sugestão de Revisores:** a análise de blame de um PR classifica os ficheiros em `files_with_blame`/`files_without_blame`, consumidos pelo resumo da secção 27.

### **16. Servidor MCP e Invocação Direta via CLI (`src/mcp_server.py`)**

* **18 Ferramentas MCP Anotadas:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates`, `review_remote_pr`, `get_baseline_status`, `get_usage_metrics`, `run_check` e `publish_pull_request`.
* **Parâmetros Opt-In no Contrato:** `review_code` e `full_review` aceitam `mentor: bool = False`; `generate_pr_description` aceita `explain: bool = False`. São **puramente por parâmetro** — nunca fazem OR com os defeitos do `.env`.
* **19 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Registo de Skills Alinhado:** O `mcp_server.SKILL_FILES` tem **11 entradas** (`tests`, `explain` e `mentor` incluídos); o `TestSkillRegistryAgreement` passa.
* **Invocação CLI Direta:** O comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca qualquer ferramenta sem iniciar o servidor stdio; o `gitpr-mcp --list` imprime o registo completo em JSON.
* **Isolamento do Stdout Real:** O `_write_real_stdout()` escreve no `sys.__stdout__` original, com fallback para `buffer` ou nova codificação com `errors='replace'` em code pages legacy do Windows.
* **Offload do Event Loop:** o decorator `_offload` (`anyio.to_thread.run_sync`) em todas as ferramentas — a ordem dos decorators importa (`@mcp.tool` **acima** de `@_offload`).
* **Falhas como Erro Estruturado:** as ferramentas devolvem falha **como dado** em vez de levantar.
* **`get_usage_metrics` com Gráficos:** o payload inclui `token_trend` (janela de até 14 dias) e `latency` (média/mediana/P95) — os mesmos números do CLI e do dashboard, agora também apresentados em ASCII na TUI.
* **Credencial por Repositório:** toda a resolução SCM passa por `src.scm_settings`; sem token, a ferramenta responde com o comando que corrige (`gitpr --init`) em vez de tentar perguntar — uma tool call não pode parar e aguardar o utilizador.
* **Métricas MCP:** `run_check` e `publish_pull_request` escrevem linhas locais (`mcp:check`, `mcp:publish_pr`).
* **O servidor MCP nunca é bloqueado pelo portão:** o `--mcp`/`gitpr-mcp` está na lista de isenções desde a primeira versão do portão.

### **17. TUI de Dashboard de Métricas (`src/ui/metrics_app.py`) 🆕 Separadores e Gráficos**

* **Âmbito por Repositório:** Rótulo `📁 Repository: owner/repo` e filtragem estrita por projeto.
* **Varredura Assíncrona com Overlay:** worker thread em background com uma `ProgressBar`; os testes aguardam `workers.wait_for_complete()` antes de afirmar.
* **🆕 Dois Separadores:** a `MetricsApp` foi reorganizada em `TabbedContent` com **Events** (as secções textuais existentes) e **Charts & Trends** (os painéis de latência e tokens). Os bindings `1` e `2` e o `action_switch_tab` saltam entre eles.
* **🆕 Motor de Gráficos ASCII (`src/ascii_chart.py`):** funções puras, sem Textual e sem novas dependências — `render_vertical_histogram` (histograma de latência com ticks Y, barras de bloco cheio/meio, contagem e percentagem por faixa), `render_token_timeseries` (série diária com etiquetas de data, tokens compactos e uma sparkline opcional) e `render_empty_card` (estado vazio emoldurado), mais os formatadores `_fmt_tokens` (`1.2M`/`45.0k`) e `_duration_ms_label` (`ms`/`s`/`min`).
* **🆕 Uma Única Passagem de Leitura:** o `_load_sections` devolve agora `(rendered_sections, chart_data)`, capturando os payloads em bruto `latency` e `token_trend` enquanto renderiza as secções textuais — sem uma segunda consulta ao ledger. O `_finish_scan` propaga o `chart_data` ao `_render_sections`, com um fallback que volta a consultar os relatórios quando os dados faltam.
* **🆕 Estado Vazio Instrucional:** sem ledger, o separador de gráficos mostra um cartão que aponta para o `gitpr metrics migrate` em vez de um painel vazio.
* **Leitura do Ledger:** o dashboard consulta o `telemetry.db` na thread de trabalho, renderizando as secções (custo, qualidade, módulos, provedores, ciclo) a partir dos mesmos renderizadores que o CLI usa.
* **Assistente de Migração (`src/ui/metrics_migration_app.py`):** oferece importar os ficheiros legados, importar **e** reconstruir a partir da cache de IA, ou "agora não".
* **Artefactos de Exportação Destrackeados:** o `.gitignore` é a única fonte sobre `.gitpr/metrics/export/`.
* **Testes:** `tests/test_ascii_chart.py` (**9 cenários** 🆕) cobre os formatadores, os estados vazios, o histograma/série preenchidos, a composição dos separadores e a mudança de separador.

### **18. Sistema de Métricas e Telemetria (`src/metrics.py`)**

* **Âmbito por Repositório:** Todos os eventos indexados por `repo_name`, resolvido por `src/infrastructure/git/identity.py`.
* **Eventos de Hook, Linter e Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. O `gitpr check` escreve `command="check"` com `findings_total`/`findings_errors` e o exit code.
* **Evento `issue_autoclose`:** uma linha por decisão de fecho, com `asked`, `accepted` e `skipped_reason` no `meta`.
* **Token Trend (`TOKEN_TREND_MAX_DAYS = 14`):** uma série diária contínua (dias sem uso entram como zero) com sparkline e barras horizontais por dia; escolhe o lado (input/output) com mais movimento.
* **Latency Histogram:** distribuição por faixas com **média, mediana e P95**; eventos sem duração medida ficam de fora para não puxar as estatísticas para zero nem inflar a faixa `< 1s`.
* **O `duration_ms` já não se perde:** o ledger usa `meta["duration_ms"]` como fallback antes de gravar `0`.
* **🆕 Custo Configurável por Modelo:** o `DEFAULT_MODEL_PRICES` (em `src/config.py`) ganhou os aliases `gemini-pro-latest` (**1.25 / 10.00** por 1M tokens) e `gemini-flash-lite-latest` (**0.10 / 0.40**), que antes caíam como "sem preço". Os overrides `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT` continuam a bater a tabela; **ambas as taxas são obrigatórias**.
* **🆕 Moeda Configurável:** o `GITPR_METRICS_CURRENCY` (predefinição `USD`) etiqueta o total e, quando não é USD, descarta a tabela embutida em vez de rotular mal um preço em dólares. Um total que deixa modelos de fora continua a ler `Total (partial)`.
* **Exportação e Limpeza:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` com confirmação interativa, `bundle`/`merge` para transportar e fundir janelas.
* **O Commit Interativo Também Mede:** o caminho `commit` continua a gravar a linha de comando com `meta` e `modules`.

### **19. Sincronização de Idiomas dos Git Hooks**

* **Versionamento Independente:** `__scripts_version__` (**v0.0.4**, inalterado).
* **Templates reescritos na janela v0.0.17:** `post-checkout`, `post-merge` e `pre-push` chamam `gitpr --quiet metrics hook-event <name>`, com guarda por `command -v gitpr` e `|| true`.
* **Mapeamento de Sufixos (`HOOK_SCRIPT_SUFFIXES`):** Os códigos de interface (`es_es`, `fr_fr`) traduzidos para os sufixos publicados (`.es`, `.fr`).
* **Escolha vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detetar uma mudança de idioma**.
* **`effective_hook_lang()`:** Resolve o idioma efetivo; o `--lang` deixou de ser descartado nesse caminho.
* **Skip de Merge-Source:** O `prepare-commit-msg` salta as fontes `message|merge|squash|commit`.
* **O `prepare-commit-msg` Continua Não-Interativo:** o hook corre com `--hook`, pelo que **nunca** abre a `CommitApp` — a TUI só existe no uso humano de `gitpr -c`, e apenas quando há TTY. 🆕 A nota de migração deste comportamento entrou em `docs/commit-message-ia.*` nos 5 idiomas.
* **Os hooks correm com `--quiet` e `--hook`, pelo que nunca são bloqueados pelo portão de atualização.**

### **20. Bridge de Linters Externos e Assistente Interativo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistente `--linter-setup`:** Assistente com presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e injeção do bloco `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` com uma cadeia local → descarregamento → stale → fallback embutido.
* **TUI de Erros do Linter:** `src/ui/linter_app.py` (Textual) mostra erros críticos e warnings; em hook/quiet imprime e faz `sys.exit(1)`. `ENABLE_COMMAND_PALETTE = False`.
* **Relatório Markdown:** Consolidado em `.gitpr/reports/linter/` apenas quando há violações.
* **A Camada SAST É Irmã, Não Substituta:** as bridges Semgrep/Gitleaks/Bandit alimentam o **mesmo** pipeline de relatório — e também o `gitpr check`.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstração Única (`ScmProvider` ABC):** O `base.py` define o contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. e `ScmProviderError(provider, http_status, message)` — `http_status` 0 = falha de rede).
* **`IssueInfo` com `is_open`:** a flag booleana remove as comparações frágeis de string de estado na camada de decisão.
* **As Operações de Issue São de Todas as Forges:** o GitLab normaliza `opened` → `open` e emite `Closes #N`; o Bitbucket normaliza `new`/`open`/`on hold` → `open` e emite `close #N`; o Azure DevOps levanta `ScmNotSupportedError` (Work Items não são issues).
* **`list_pull_requests(repo, state, since, until)` — paginado e canónico:** os quatro provedores traduzem os estados canónicos (`open`/`merged`/`closed`/`all`) para o vocabulário nativo, paginam no cursor de cada API e filtram por uma janela de datas inclusiva.
* **Porque o filtro é por data de estado:** três das quatro forges devolvem algo mais amplo para um PR integrado, pelo que cada uma filtra a sua própria resposta contra a data que o estado implica.
* **`supports_merged_dates`:** atributo de classe, `False` no Bitbucket Cloud, que publica `merged_at` **vazio**.
* **`web_links.py`:** funções puras (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) que normalizam qualquer formato de remote.
* **`get_pull_request(repo, pr_id)` — método concreto da ABC:** a predefinição lança `ScmNotSupportedError` e cada forge implementa-o.
* **Resolução por Repositório:** o registry não lê `GITPR_SCM_PROVIDER`; `resolve_scm_provider(config)` recebe o provedor já resolvido por `src/scm_settings.py` (secção 46).
* **GitHub:** `GET /repos/{o}/{r}/issues/{n}` (marcando `pull_request` e recorrendo ao número pedido quando ausente), `GET /repos/{o}/{r}` para `default_branch` (string vazia quando ausente) e a keyword `close #N`.
* **Cabeçalhos do GitLab Sintetizados:** O `changes[].diff` é um hunk solto; o `old_path`/`new_path` montam os cabeçalhos `diff --git a/… / --- / +++`, honrando o `/dev/null`. E o `overflow: true` **lança** em vez de rever metade de um diff truncado.
* **`request_pull_request_reviewers` devolve `list[str]`:** Devolve os logins **efetivamente anexados**, lidos do corpo do `201`. A quebra de contrato está documentada em [ADR-0009](docs/adr/0009-reviewer-attach-returns-read-back.md) e em `docs/suggested-reviewers.*`/`docs/scm-multiforge.*`.
* **Helpers de Apenas Leitura do GitHub:** `get_commit_author_login` e `get_user_login`.
* **Fail-Fast por Forge:** O Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; o Bitbucket exige `GITPR_SCM_USERNAME`; o `create_issue` no Azure lança `ScmNotSupportedError`.
* **Publicação de Release:** O `provider.create_release()` usado pelo `gitpr release --publish`.
* **Publicação de PR Partilhada:** `src/application/use_cases/publish_pull_request.py`, usado pelo `--no-edit` e pela ferramenta MCP. Valida título/corpo, **exige `provider.token` apenas quando `check_remote_state=True`**, opcionalmente verifica PR existente e branch não empurrada, mascara mensagens de erro e devolve `PublishPrOutcome`.
* **Artefactos:** Glossário + ADR-001/ADR-005/ADR-016; família `docs/scm-multiforge.*.md` em 5 idiomas; **10 ficheiros de teste, 370 cenários**.

### **22. Subcomando `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Fluxo:** `git log` entre `--since` e `HEAD` → classificação Conventional Commits → bump semântico sugerido → montagem do changelog → resumo executivo por IA opcional → *prepend* no `CHANGELOG.md`.
* **O Intervalo Deixou de Vir do `git describe`:** a origem do intervalo é a **secção anterior do próprio changelog**, através de duas âncoras: o hash mais recente dessa secção e, como fallback, a tag de versão da secção.
* **`_drop_released()` — a segunda linha de defesa:** qualquer commit cujo hash curto apareça noutra secção de versão é descartado, com contagem.
* **Cada bullet tornou-se uma ligação:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **A falha de PR merged por merge commit:** uma segunda passagem (`git log --merges`) monta `hash → PR` pela ancestralidade.
* **Contribuidores resolvidos pela mesma escada dos revisores:** o `get_commit_author_login` primeiro, depois o `email_to_handle`, com cache em `~/.gitpr/cache/contributors.json`.
* **A Fronteira da Secção Era um Bug Latente:** o `upsert_changelog(force=True)` substitui da linha `## [x.y.z]` até ao próximo cabeçalho de **nível 2**.
* **Front Matter Declara o Idioma da Skill:** `src/skill_frontmatter.py` (`split_front_matter()`) lê um bloco YAML inicial com `language: <code>` (allowlist `en_us`, `pt_br`, `pt_pt`, `es_es`, `fr_fr`), **remove o bloco** antes de o corpo virar instrução de sistema e troca o idioma do i18n pelo tempo da geração — headings, rótulos **e** o idioma pedido à IA, mesmo com o resumo desligado.
* **Classificador (`src/commit_classifier.py`)** e **Construtor com Secções Traduzíveis (`src/changelog_builder.py`)**.
* **Bump Semântico (`src/version_bump.py`)** e **Publicação** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opções no subcomando.**
* **🆕 Estado do release:** a secção `## [1.7.0] - 2026-11-10` está commitada (`6d1f1dc`) e o bump em `2354a30` subiu o `__version__` para **1.7.0**; o PyPI serve a 1.7.0. A tag `v1.7.0` ainda aponta para o commit anterior ao bump.
* **O subcomando passa pelo portão de atualização** quando é executado num terminal interativo.
* **Artefactos:** `docs/release-notes.*.md` em 5 idiomas, ADR e `tests/test_web_links.py`.

### **23. Subcomando `gitpr config` — TUI de Configuração**

* **Ecrã Master-Detail (`src/ui/config_app.py`):** Categorias à esquerda, campos à direita, editados inline. Cabeçalho com procura (`/`) e um contador de alterações pendentes; rodapé `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schema Declarativo (`src/config_schema.py`) — a fonte única de verdade:** **18 categorias** e 🆕 **109 `ConfigField`** (eram 100), dos quais **7 avançados** e 🆕 **56 `seeded`** (eram 55).
* **`DEFAULT_CONFIG` Derivado:** o dicionário é montado a partir de `seeded=True`, pelo que uma chave nova no schema já nasce semeada ou deliberadamente não-semeada.
* **Categoria `check`:** as nove `GITPR_CHECK_*` (`BASE`, `FORMAT`, `OUTPUT`, `FAIL_ON`, `NO_IA`, `IA_ONLY_HIGH_RISK`, `BASELINE_PATH`, `POLICY`, `TIMEOUT_SECONDS`) passam a aparecer aqui, com os mesmos defaults do pipeline.
* **Categoria `smart_excludes`:** substitui o antigo rótulo `Diff Filters`; `SMART_EXCLUDES_VERSION` mudou de grupo e a categoria tem painel próprio (ver abaixo).
* **Painel Smart Excludes:** lista de fontes (projeto, global core, global docs), `TextArea` de edição, botão de criação do ficheiro do projeto, estado/nota e rastreio de dirty. O `F2` guarda o ficheiro do projeto pelo mesmo plano de gravação.
* **Categoria `project` e o Atributo `store`:** os campos declaram onde moram (`env` vs `project`). Os três campos do projeto são o path do ficheiro (read-only), a branch alvo (editável) e a base efetiva com a origem (read-only). O `KIND_BRANCH` renderiza a escolha de branch.
* **Categoria `scm` por Forge:** ganhou a linha editável `PROJECT_SCM_PROVIDER`, a linha read-only `PROJECT_EFFECTIVE_PROVIDER` (forge em uso e origem) e um grupo `gitlab`; tokens e base URLs de **todas** as forges são listados sempre, e o teste de token usa o host do próprio slot.
* **Categoria `issue`:** `GITPR_ISSUE_AUTOCLOSE` e `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` aparecem aqui.
* **🆕 Tipo `KIND_FLOAT`:** o primeiro campo float do schema é `GITPR_METRICS_CURRENCY`, a par dos oito campos de preço por modelo. A validação aceita vazio e recusa um valor negativo ou não numérico com a mensagem "Use a number greater than or equal to zero.".
* **🆕 Categoria `ai` com Preços:** os campos `*_INPUT`/`*_OUTPUT` ficam visíveis quando o `DEFAULT_AI_PROVIDER` correspondente está selecionado; um valor vazio preserva o comportamento anterior.
* **Escrita por Store:** a `ConfigApp` despacha por `store`: valores de projeto **nunca** passam por `save_config_values()`, são gravados primeiro, suportam remoção ao esvaziar, recusam a branch atual e nunca sobrescrevem YAML ilegível. A linha da base efetiva é atualizada in-place depois de guardar.
* **Categorias `policy` e `baseline`:** os campos do pack e os quatro do baseline.
* **Secção Skills Alinhada:** O `SKILL_LABELS` tem **11 entradas** (`tests`, `explain`, `mentor`).
* **Filtragem por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` conforme o `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` sob `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` sob `[Azure DevOps]`.
* **Procura Global (`/`):** Encontra correspondências na chave ou no rótulo em todas as categorias e **ignora o filtro de visibilidade**.
* **Validação em Duas Camadas:** **offline** bloqueia o `F2` com um erro inline; **online** (apenas credenciais alteradas na sessão) corre num worker com timeout de 10s e só bloqueia em `401`/`403`.
* **Restaurar (`Ctrl+R`):** Remove a linha do `.env` em vez de reescrever a predefinição; o `Esc` com alterações pendentes pede confirmação.
* **Secções Project-Scoped:** Edita `.gitpr/skill/*.md`, `.gitpr/conf/gitpr.config.yml` e os smart-excludes do projeto, gravando atomicamente e preservando CRLF/LF.
* **Descarregamentos Forçados:** Botões que forçam o novo descarregamento dos smart-excludes, das traduções, dos presets e das thinking words via `force=`.
* **Módulo de Links Leve (`src/doc_links.py`):** o `doc_url()` saiu de `core.py` para a UI obter o link sem importar SDKs de IA. O `DOC_LANG_PARAMS` mapeia `es_es`→`es` e `fr_fr`→`fr`, e o `CURRENT_LANG` é lido **na chamada**, pelo que `set_lang()` propaga para os links.
* **Testes:** 5 ficheiros — `test_config_app.py` (94), `test_config_schema.py` (**55** 🆕, +2), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9) e `test_config_issue_autoclose.py` (10).

### **24. Registo Geral de Utilização (`src/usage_log.py`)**

* **Uma Linha por Comando:** escreve `~/.gitpr/logs/<uuid5>.log`, **um ficheiro por dia**, com o comando, os argumentos, o repositório, o utilizador e o timestamp.
* **Nome Derivado da Data:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")`.
* **Escrita Síncrona (decisão explícita):** ao contrário do `log_local_metric`, que usava uma thread daemon e perdia o registo se o processo terminasse primeiro.
* **Nunca Imprime:** o servidor MCP reserva o stdout para o JSON-RPC; o módulo também nunca levanta uma exceção.
* **Um Único Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms em vez de ~150 ms no Windows, em *todas* as execuções.
* **Controlo:** `GITPR_SHOW_LOGS` (predefinição `"true"`); desativado no `tests/conftest.py`.
* **Artefactos:** `docs/usage-log.*.md` em 5 idiomas; `tests/test_usage_log.py` (27 cenários).

### **25. Subcomando `gitpr fix` — Apontamentos de Review como Patches Revisíveis (`src/fix/`)**

* **O Pipeline Real:** último review da cache (`resolve_last_review`) → **uma** chamada de IA → extração e validação do diff unificado → `git apply --check` → classificação determinística → dry run ou escrita → histórico.
* **Um pacote de 8 ficheiros:** `patch_provenance.py`, `patch_extractor.py` (partilhado com o chat), `patch_safety_classifier.py` (puro, sem I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Classificação Determinística:** recusa um patch que atravesse mais de um ficheiro ou hunk, toque num caminho sensível, rebente o orçamento de linhas, **apague uma linha que pareça uma chamada**, venha marcado como baixa confiança ou falhe o `git apply --check`. O `--force` **nunca** contorna a aplicabilidade, apenas a classificação.
* **Escrita Opt-In:** o dry run é a predefinição; qualquer mutação exige `--apply` (ou uma frase escrita com `--force`).
* **Testes:** 10 ficheiros em `tests/fix/` (**204 cenários**) com fixtures de repositório git reais.

### **26. Subcomando `gitpr review-pr` — Review Remoto de PR (`src/review/`)**

* **O que é:** orquestração, **não** um segundo motor de review — o motor existente, o linter e o renderer alimentados com um diff que veio de fora.
* **Um pacote de 5 ficheiros:** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Só de Leitura por Predefinição:** o `--post-comment` é o **único** caminho que escreve na forge. A ferramenta MCP `review_remote_pr` nem recebe o argumento.
* **A baseline é resolvida antes da primeira chamada de rede**, e os avisos do portão são fundidos no relatório/comentário.
* **`render.py` e o risco:** o `compose_review_content` / `render_review_result` **colocam** a secção `⚡ Risk Assessment` acima dos alertas do linter quando o `GITPR_RISK_INCLUDE_IN_REVIEW` está ligado.
* **Documentação completa nos 5 idiomas:** `docs/review-pr.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md` e `.fr_fr.md`.
* **Hunk Binário no Diff Remoto:** o `filter_excluded_sections` aplica sempre o `collapse_binary_hunks()`, incluindo nas saídas precoces sem padrões/secções.
* **Testes:** 4 ficheiros em `tests/review/` (**97 cenários**).

### **27. Resolução da Identidade dos Revisores (`src/reviewer_resolution.py`)**

* **O Bug (duas falhas silenciosas em cadeia):** (1) **Prefill vazio** — `handles` apenas com o `email_to_handle()`; (2) **Anexação não verificada** — valores passados *literalmente*, o GitHub respondeu **201 sem anexar ninguém** e o `_request()` regressou sem levantar.
* **Módulo:** puro, sem I/O próprio, **que nunca levanta**. `resolve_candidates()` (antes da TUI) e `resolve_typed_reviewers()` (na anexação); o `match_candidate()` corresponde exatamente e de forma normalizada por login, nome ou email.
* **Escada de Resolução:** handle conhecido (sem pedido) → correspondência exata com uma pessoa sugerida → procura por email → validação do login na forge.
* **O que não resolve nunca é enviado:** sai em `ResolutionOutcome.dropped` como `(value, reason_i18n)` e é descartado com um aviso visível.
* **A Mesma Escada Serve a Release:** o `release_engine` reutiliza o `get_commit_author_login` + `email_to_handle`.
* **Companheiro Legado:** se um provedor terceiro devolve `None` (contrato antigo), o log diz `(no read-back)` e o fluxo continua — uma read-back em falta **nunca** é tratada como falha.
* **Resumo de Blame:** o `ReviewerSuggestionResult` tem `files_with_blame`/`files_without_blame`; o motor separa os ficheiros e o CLI imprime **uma** linha consolidada (`ℹ️ Blame analysis: X file(s) with blame, Y without blame`) — só quando há ficheiros sem blame.
* **Testes:** `tests/test_reviewer_resolution.py` (18) + `test_github_provider.py` (86), `test_pr_publish_app.py` (59), `test_suggest_reviewers.py` (19), `test_reviewer_suggestion.py` (18) e `test_main_suggest_reviewers.py` (8).

### **28. Subcomando `gitpr demo` — Visita Guiada sobre Exemplos Gravados (`src/demo/`)**

* **O que é:** uma visita interativa que mostra as três saídas principais do GitPR — mensagem de commit, review e descrição de PR — **sem uma API key, sem um repositório git e sem rede**.
* **Pipeline Real, Resposta Falsa:** o `FakeAIProvider` espelha o `src.ai_providers.call_ai_model` **argumento a argumento**; o `demo_pipeline()` substitui o `src.core` e o `src.metrics` para que o pipeline de produção corra **intocado**.
* **O Isolamento É um Requisito, Não um Detalhe:** a visita **não pode** tocar no `~/.gitpr` do utilizador nem na rede — garantido pelo `tests/demo/conftest.py`.
* **Pacote:** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (dois exemplos localizados nos 5 idiomas). Os cenários são **módulos Python, não JSON**, para viajarem dentro do wheel.
* **TUI:** `src/ui/demo/` com um modal de ajuda; o diff vai num bloco ```` ```diff ````.
* **CLI:** `gitpr demo` com `--scenario`, `--lang` e `--no-tui`.
* **Testes:** 6 ficheiros em `tests/demo/` (**155 cenários**).
* **Artefactos:** `docs/demo.*.md` em 5 idiomas, `README.*` (5) e as métricas de exemplo da visita.

### **29. Badge do GitPR e o Comando `gitpr badge` (`src/branding/`)**

* **O que é:** um badge público que certifica que um pull request passou por uma verificação de qualidade local, e um comando que imprime o excerto de adoção para o README do projeto.
* **URL Estático de Propósito:** é um Markdown shields.io que o GitPR **nunca** descarrega — publicar um pull request não pode passar a depender de um terceiro estar no ar.
* **Medição Honesta (`badge_data.py`):** o `collect_linter_counts()` devolve `None` quando não há regras de linter configuradas, porque uma lista vazia significa "nada foi verificado", não "nada foi encontrado".
* **O Badge Nunca Bloqueia uma Publicação:** todos os caminhos de falha são capturados e degradam para "sem badge".
* **Ponto de Injeção Único:** `attach_pr_badge()` no `src/main.py`. Anexação idempotente por marcador.
* **Comando `gitpr badge`:** `--readme` e `--style`. **Só imprime** — o README nunca é modificado.
* **Configuração:** `GITPR_BADGE` (predefinição `true`) — opt-out **só de leitura**.
* **Testes:** 7 ficheiros em `tests/badge/` (**83 cenários**).
* **Artefactos:** `docs/badge.*.md` em 5 idiomas e o inquérito dedicado.

### **30. Subcomando `gitpr split` — Commits Atómicos por Hunk (`src/split/`)**

* **O que é:** uma árvore de trabalho com várias preocupações deixa de se transformar num blob de commit. O `split` lê o diff não commitado, pede à IA para particionar os hunks por **intenção lógica** e propõe um commit atómico por preocupação. A árvore nunca é reescrita.
* **Um pacote de 6 ficheiros:** `split_plan.py`, `hunk_parser.py` (puro), `hunk_grouper.py`, `generate_split_plan.py` (só de leitura), `apply_split_plan.py` (**o único que muta**) e `__init__.py`.
* **A Garantia de Árvore Intacta É Estrutural:** o `selective_stager.py` pré-valida **cada** grupo contra um índice temporário em HEAD e usa um `GIT_INDEX_FILE` descartável.
* **Destrutivo para o Índice, Não para a Árvore:** o `--apply` repõe o índice em HEAD antes de fazer stage.
* **Configuração:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) e `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Valores zero ou negativos são **ignorados a favor da predefinição**.
* **Relação com a TUI de Commit:** o `split` e o `-c` atacam o mesmo problema a alturas diferentes — o `split` propõe **vários** commits a partir da árvore; a `CommitApp` organiza **um** commit a partir do índice.
* **Testes:** 6 ficheiros em `tests/split/` (**101 cenários**).
* **Artefactos:** `docs/split-command.md` em 5 idiomas, ADR-006, `glossary-gitpr-split.md`, spec/plano e inquérito.

### **31. Verificação de Segredos Embutidos (`src/security_ruleset.py`)**

* **O que é:** sete regras que correm em **cada** invocação do linter, fundidas no fim do `load_linter_rules()` depois das regras do projeto e dos plugins.
* **As Sete Regras:** cinco `error` bloqueantes — chave de acesso AWS, token do GitHub, token do Slack, chave da API Google e bloco de chave privada — e duas `warning`: URL de ligação a base de dados com credenciais e atribuição de credencial genérica, esta última atrás de um filtro de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Cobertura:** atribuição **sem aspas**, prefixos **`ASIA…`**, **`github_pat_…`** e **`xoxc-`/`xoxd-`**, **fronteira esquerda** no nome da chave (`bypassword` não corresponde) e supressão de falsos positivos em runtime (`os.getenv(...)`, `process.env.X`).
* **Porque no Pacote e Não no Template:** um portão de segredos tem de se comportar **da mesma forma** em todas as máquinas e em todos os CI.
* **Configuração:** `GITPR_LINTER_SECURITY` (predefinição `true`; opt-out fail-open) e `GITPR_LINTER_SECURITY_DISABLED_RULES` (separados por `;`).
* **Testes:** `tests/test_security_ruleset.py` (**77 cenários**) — matriz de regex, placeholders, nível, wildcard, fusão e traduções; o invariante "o alerta nunca ecoa o valor" mantém-se.
* **Artefactos:** ADR-007, [ADR-0008](docs/adr/0008-secret-ruleset-coverage-extension.md), `glossary-gitpr-secret-scanning.md`, spec/plano/inquérito e relatórios de tarefa.

### **32. Bridges SAST — Semgrep, Gitleaks e Bandit (`src/infrastructure/linter/external/`)**

* **O que é:** uma camada **opt-in** que liga scanners de segurança de terceiros ao pipeline de linter existente.
* **Âmbito Limitado:** correm **apenas** quando ativados e **apenas** sobre os ficheiros tocados pelo diff.
* **Contrato Comum:** `ExternalLinterBridge` (ABC) com execução `subprocess` endurecida (`shell=False`, timeout rigoroso, `stdin=DEVNULL`, UTF-8 com `errors='replace'`).
* **A Deduplicação É o Ponto:** o `deduplicate_secret_findings()` funde os apontamentos do Gitleaks e do ruleset de regex no mesmo ficheiro e linha numa **única** entrada confirmada `[Gitleaks + Regex]`.
* **Segredos Mascarados:** o `mask_secret_value()` garante que o valor nunca chega ao apontamento, ao log ou à telemetria.
* **Resolução em Três Camadas:** o `load_sast_config()` lê as predefinições → `.gitpr.linter.yml` (bloco `sast`, com fallback para `linter.external`) → variáveis `GITPR_SAST_*`. **Todas com predefinição `false`**.
* **Degradação Graciosa:** uma ferramenta ativada mas ausente do `PATH` emite um aviso e a execução continua.
* **Testes:** 4 ficheiros em `tests/infrastructure/linter/external/` (**14 cenários**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + `tests/domain/linter/test_structured_findings.py` (44).

### **33. Subcomando `gitpr tests generate` — Geração de Suites por IA (`src/domain/tests_generation/`, `src/application/`)**

* **O que é:** gera ficheiros de teste completos e executáveis a partir do diff atual, de um ficheiro específico ou de um apontamento de review, **respeitando a convenção do repositório** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Camada de Domínio:** `TestFramework` (enum) e as dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest`; o `detect_test_framework()` deteta a partir dos ficheiros de configuração, dos manifests e do conteúdo do diretório de testes; o `build_test_scaffold()` calcula o caminho de destino convencional por framework.
* **Camada de Aplicação (`generate_test_file.py`):** orquestra a deteção, a resolução do scaffold, o prompt, a IA, o parsing do JSON e a escrita opcional. O `validate_test_syntax()` corre a toolchain local (`php -l`, `node --check`, `python -m py_compile`) — uma falha de validação é um **aviso**, não um erro.
* **Apresentação:** grupo `tests` com `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); o dry run é a predefinição e sobrescrever um teste existente pede confirmação com **Não** pré-selecionado.
* **Documentado em 5 idiomas:** `docs/tests-generate.*.md` (5 ficheiros), com uma ligação no `gitpr tests generate` (epilog) e no README.
* **Skill:** `.gitpr.tests.md` no `SKILL_LABELS`, no `mcp_server.SKILL_FILES` e no `SKILL_FILES_BY_TYPE`.

### **34. Subcomando `gitpr explain` e a Flag `--explain` — Guia do Revisor (`src/domain/pr/`)**

* **O que é:** um guia centrado em quem vai **rever** — o que muda, porque muda, onde focar e qual é o risco de regressão.
* **Camada de Domínio (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, o renderer `build_explain_markdown()` e o `parse_explain_payload()`, que tolera saída não-JSON e **deteta placeholders** `[FILL]`/`[TODO]`.
* **`parse_explain_payload()` aceita `dict | str | None`:** o provedor entrega o JSON **já interpretado**; a função aceita as três formas e normaliza os campos `None`.
* **Ramo de Chave em Falta:** o `generate_pr_explanation` imprime o erro padrão de chave em falta e sai com **1**.
* **Camada de Aplicação (`generate_pr_explanation.py`):** resolução do provedor, validação da chave, carregamento do contexto da skill (`explain`), prompt, invocação e parsing.
* **Duas Portas de Entrada:** `gitpr explain` (`--provider`, deteção de diff em falta antes de qualquer IA) e a flag `--explain` no CLI raiz. A extensão expõe o mesmo caminho em `GitPR: Generate PR Draft (with Reviewer Guide)`.
* **`-h --explain` Corrigido:** a opção usava o dest `explain_flag`, mas o `HELP_MAP` procurava `explain` — a ajuda contextual volta a renderizar.
* **Documentado em 5 idiomas:** `docs/explain-pr.*.md` (5 ficheiros), com uma ligação no `HELP_MAP["explain"]`, no epilog do comando e no README.
* **Configuração:** `GITPR_EXPLAIN_BY_DEFAULT` (predefinição `false`).

### **35. Suite Determinística e CI (`.github/workflows/tests.yml`, `release.yml`)**

* **O que é:** o workflow que corre a suite em **Python 3.10** (o mínimo declarado no `pyproject.toml`) e **3.13**, com `fail-fast: false`.
* **`tests/conftest.py` Hermético:** fixa `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` e o `LANG_VERSION` na versão do código, mais `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true` e `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
* **Fixtures Autouse Contra Fugas do Perfil:** `english_interface` e `no_hook_script_sync` impedem que o idioma, as flags de linter e a auto-sincronização dos hooks do perfil de desenvolvimento passem de um teste de CLI para outro.
* **A Ordem Importa:** o `src.updater` é importado **antes** de as variáveis de idioma serem definidas.
* **Estabilidade dos Testes com Workers:** o `test_config_app.py` e o `test_metrics.py` aguardam `workers.wait_for_complete()` antes de afirmar.
* **Dependência do Workflow:** o runner limpo não tem `~/.gitpr`, e o `test_demo_isolation.py` afirma que o perfil existe — a job cria explicitamente o diretório e o `.env` vazio.
* **🆕 Nesta janela a suite ganhou 14 cenários e 1 módulo:** `test_ascii_chart.py` (9), `test_config_schema.py` (53 → **55**), `test_metrics.py` (174 → **175**), `test_full_diff_encoding.py` (8 → **9**) e `unit/test_check_ci_docs_parity.py` (2 → **3**). **Total: 3436 cenários em 160 ficheiros.**
* **🆕 Teste de Paridade do `action.yml`:** o `tests/unit/test_check_ci_docs_parity.py` passa também a exigir que o ADR-0011 exista e referencie `action.yml`, `gitpr check`, `--linter`, `--fullreview`, `SARIF` e `docs/check-ci.md`.
* **⚠️ 2 falhas conhecidas:** `tests/unit/test_version_consistency.py::test_claude_md_version_matches_ssot` e `::test_gemini_md_version_matches_ssot` falham porque o `CLAUDE.md`/`GEMINI.md` ainda dizem 1.6.0 enquanto o `src/updater.__version__` já é 1.7.0.
* **Workflow da Extensão:** o `.github/workflows/extension.yml` corre `npm install`, compila, testa e empacota o VSIX. 🆕 A job `github-release` anexa o `.vsix` às GitHub Releases das tags `ext-v*`; a job `publish-marketplaces` avalia cada PAT de forma independente e é não-bloqueante.
* **Workflow de Release:** o `release.yml` corre os testes, verifica a consistência tag ↔ `src.updater.__version__`, constrói sdist/wheel, valida com `twine check --strict`, publica via Trusted Publishing (OIDC) e cria a GitHub Release. A SSOT da versão tem também o seu próprio teste (`tests/unit/test_version_consistency.py`).

### **36. Ledger de Telemetria SQLite (`src/ledger.py`, `src/infrastructure/git/identity.py`)**

* **O Problema Não Era o Formato, Era a Junção:** os eventos eram escritos como ficheiros JSON por comando, a partir de uma **thread daemon**, e depois re-ligados à cache de IA por uma correspondência heurística de `(repo, branch, action, minute)`.
* **A Escrita É Síncrona, e Isso É a Decisão:** o `log_command_metric()` resolve o contexto git **uma vez por processo** e escreve a linha de imediato.
* **Schema Versionado:** `SCHEMA_VERSION` + `PRAGMA user_version`, chaves UUID, WAL + `synchronous=FULL`, e `LedgerVersionError` para recusar um bundle mais recente **antes** do `ATTACH`.
* **Invariante "Nunca Nasce Vazio":** o `ensure_ledger()` só cria a base de dados **em conjunto** com a absorção do JSON legado.
* **Uma Janela, Um Significado:** `--since`/`--until`/`--days` estreitam **todas** as superfícies de leitura; a secção de ciclo assume os últimos 30 dias e **nomeia a janela**.
* **Cada Métrica É Calculada uma Vez e Rendida uma Vez:** os pares `(style, text)` fazem o CLI e o dashboard Textual contar a **mesma** história.
* **Custo com Tabela Embutida e Override:** preços de lista dos quatro modelos, substituídos por `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Ambas as taxas são obrigatórias**. 🆕 Os aliases do Gemini também têm preço e o `GITPR_METRICS_CURRENCY` controla a etiqueta.
* **`modules` Normalizado:** caminhos passam a dois segmentos (`src/fix`), `(root)` e `(no module)`.
* **Transporte do Bundle:** o `bundle` escreve a janela como um `.db` autónomo; o `merge` anexa-o e insere por UUID.
* **O `cache_backfill` Fica de Fora:** as linhas reconstruídas a partir da cache são excluídas dos agregados de execução.
* **O `issue_autoclose` Entra no Ledger:** uma linha por decisão, com `asked`, `accepted` e `skipped_reason`.
* **`duration_ms` com Fallback em `meta`:** os eventos que só traziam a duração em `meta["duration_ms"]` deixam de ser gravados como `0`.
* **Identidade Git Sem Forge (`identity.py`):** Rótulo do repo, branch e autor numa **única** chamada de subprocesso.
* **A Secção de Ciclo É a Única que o Ledger Não Responde:** lista PRs integrados via `ScmProvider.list_pull_requests`; qualquer falha degrada para uma linha — `Not read: <reason>`.
* **Apresentação Completa:** grupo `gitpr metrics` com 8 subcomandos, um dashboard que lê o ledger numa thread de trabalho e o assistente de migração com três opções.
* **Testes:** `tests/test_metrics.py` (**175 cenários** 🆕).

### **37. Subcomando `gitpr mentor` e a Flag `--mentor` — Modo Mentor (`src/domain/mentor/`)**

* **O que é:** um review diz *o que* está errado e quase nunca *porque é que isso importa*. O Modo Mentor enriquece os apontamentos com **o que está a acontecer**, **porque é que importa**, uma **analogia** do dia a dia e os **conceitos a estudar**.
* **Camada de Domínio Pura (`mentor_explanation_builder.py`):** sem I/O, converte o payload da IA num `MentorExplanation` validado e impõe **honestidade epistémica**: sem evidência suficiente, a explicação **di-lo**; URLs alucinados são **removidos**; IDs desconhecidos são **rejeitados**.
* **Camada de Aplicação (`generate_mentor_explanation.py`):** recolhe candidatos, **lotes de no máximo 10 apontamentos**, ordenados por severidade, reutilizando a cache MD5 pelo hash do prompt.
* **Duas Portas de Entrada:** `gitpr mentor [--finding <id>] [--provider <name>]` e a flag `--mentor`, que anexa a secção ao review normal.
* **Opt-In Estrito:** `GITPR_REVIEW_MENTOR_MODE` (predefinição `false`) e `GITPR_MENTOR_INCLUDE_ANALOGY` (predefinição `true`), editáveis na TUI de configuração.
* **Skill e Registo:** `.gitpr.mentor.md` registado no `SKILL_FILES_BY_TYPE`, no `mcp_server.SKILL_FILES` e no `SKILL_LABELS`, com templates em **5 idiomas**.
* **Testes:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) e `tests/test_mentor_cli.py` (4) — **todos verdes**.
* **Artefactos:** `docs/mentor-mode.*.md` em **5 idiomas**, ADR-009, `glossary-gitpr-mentor.md`, spec e relatórios de tarefa.

### **38. Subcomando `gitpr risk` — Pontuação de Risco Determinística (`src/domain/risk/`)**

* **O que é:** uma pontuação **0–100** com um nível `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculada **sem IA** a partir de sinais locais.
* **Camada de Domínio Pura (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums e dataclasses; pesos, limiares, padrões de caminhos críticos, correspondência glob e deteção de testes/não-executáveis carregados do YAML; cálculo por ficheiro com **clamp 0–100** e agregação 50/30/20.
* **A Fórmula É Declarada, Não Aprendida:** **50%** o ficheiro de maior risco, **30%** a média ponderada por linhas e **20%** evidência crítica.
* **Adaptadores de Infraestrutura:** o `risk_history_reader.py` lê o `git log` e extrai commits, correções de bugs e reverts numa janela de **90 dias**; o `test_matcher.py` faz corresponder ficheiro de teste ↔ produção por convenção.
* **Caso de Uso (`calculate_risk.py`):** correlaciona as secções do diff, os apontamentos, a correspondência de testes e o histórico em `FileRisk`/`PullRequestRisk`, **degradando com um aviso** quando um sinal não está disponível.
* **Caminho Tolerante:** a agregação do `check` aceita `path` **ou** `file_path` — o relatório de risco não degrada em silêncio.
* **Três Consumos:** o comando `gitpr risk`, a secção `⚡ Risk Assessment` anexada aos reviews e ao review remoto de PR, e o **prompt de IA** como contexto **só de leitura**. O `gitpr check` usa o mesmo `execute_calculate_risk`.
* **Opt-Out:** `GITPR_RISK_INCLUDE_IN_REVIEW` (predefinição `true`).
* **Configuração por Projeto:** `templates/gitpr.risk.yml` (ou `.gitpr/skill/gitpr.risk.yml`) substitui pesos, limiares e caminhos críticos.
* **Testes:** `tests/domain/risk/` (2 ficheiros, 10), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) e `tests/test_risk_cli.py` (4).
* **Artefactos:** `docs/risk-scoring.*.md` em **5 idiomas**, ADR-010, `glossary-gitpr-risk-scoring.md` e relatórios de tarefa.

### **39. Subcomando `gitpr policy` — Packs de Política Versionados (`src/domain/policy/`, `src/infrastructure/policy/`)**

* **O que é:** um **pack YAML versionado** que transporta, num único artefacto, as decisões de qualidade de um repositório: skills, regras de linter, severidades, caminhos críticos, pesos de risco e convenções de PR/commit.
* **Camada de Domínio (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`):** parsing do manifest com um **schema fechado**, verificação de compatibilidade e credenciais, **checksum SHA-256** e um resolvedor de grafo com precedência e composição.
* **Camada de Infraestrutura:** o `local_policy_repository.py` resolve o pack a partir de **três origens** (`.gitpr/policies`, `~/.gitpr/policies` e o pacote embutido) e o `policy_pack_loader.py` carrega-o.
* **Casos de Uso:** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` e `validate_policy_pack`.
* **Quatro Packs Embutidos:** `laravel-quality`, `node-quality`, `php-security` e `vue-quality`, cada um com `policy.yml` e `linter.yml`, distribuídos via **`package-data`**.
* **Consumo Integrado em Quatro Pontos:** `calculate_risk`, `config.load_linter_rules`, `core.get_skill_context` e a **chave de cache do PR** (`cache_scope`). O `gitpr check` resolve o pack **antes de tudo** e sai com o código 2 se ele for inválido.
* **CLI:** `gitpr policy {list, validate, show, use, off, install, init}`, com confirmação (`--yes`/`--force`), deteção de stack no `init` e a escolha gravada em `.gitpr/policy.lock.yml`.
* **Configuração:** `GITPR_POLICY_ENABLED` e `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`.
* **Compatibilidade Retroativa:** sem pack ativo, o comportamento é **preservado** — coberto por um teste de integração.
* **Dependência:** `packaging>=23.0` (SemVer/PEP 440).
* **Testes:** `tests/domain/policy/` (4 ficheiros, 126), `tests/application/use_cases/` (4 ficheiros de policy, 89), `tests/test_policy_cli.py` (26) e `tests/integration/test_policy_effect_on_review_linter_risk.py` (31).
* **Artefactos:** `docs/policy-packs.*.md` em **5 idiomas**, ADR-011, `glossary-policy-packs.md`, spec/plano/inquérito.

### **40. Subcomando `gitpr baseline` — Baseline e Supressões Auditáveis (`src/domain/baseline/`)**

* **O que é:** os linters e os reviews voltam a reportar os mesmos apontamentos pré-existentes em cada execução. O baseline regista o **instantâneo dos apontamentos que o repositório já conhece** em `.gitpr/baseline.json`, cria uma impressão digital estável de cada um entre execuções e deixa o pipeline classificar o que é novo — **só o `new` bloqueia**.
* **Camada de Domínio (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`):**
  * **Fingerprint estável:** identidade da regra, caminho normalizado, linhas e **sha256 da linha com os espaços colapsados** — a linha crua **nunca** é guardada.
  * **Manifest canónico:** JSON canónico com checksum, validação que recusa uma chave desconhecida, uma versão de schema/fingerprint fora do intervalo suportado, um invariante de supressão/dívida quebrado, um fingerprint duplicado e um intervalo de linhas inválido.
  * **Comparador:** classificação, contagens por estado e resolução de entradas — `resolved` e depois `back` lê-se como **novo**, a leitura conservadora.
  * **Política de Supressão:** supressões, dívida aceite, overrides e validadores, com correspondência em camadas por **especificidade e precedência**, mais o acompanhamento de dívida em atraso.
* **Cinco Casos de Uso:** `baseline_gate`, `baseline_summary`, `compare_against_baseline`, `create_baseline` e `suppress_finding`.
* **Integração com o Policy Pack:** o pack pode transportar um bloco `baseline:`, validado pelos **mesmos** validadores.
* **O Repositório É o Único que Escreve:** o `local_baseline_repository.py` é o **único** leitor/escritor de `.gitpr/baseline.json` e `.gitpr/baseline.overrides.yml`.
* **Configuração:** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` e `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`.
* **Opt-In e No-Op:** desligado ou sem baseline existente, a funcionalidade **não lê nenhum ficheiro e não imprime nada**.
* **⚠️ A Exceção Deliberada:** um ficheiro de baseline que existe mas **não pode ser aplicado** faz o lint, o review, o full review, a auditoria de ficheiros, o review remoto e o risco **abortar com exit code 1**. No `gitpr check`, uma baseline corrompida sai com o **código 2**.
* **O Contrato Tornou-se um Teste:** o `tests/integration/test_baseline_exit_code_contract.py` (8 cenários) fixa ambos os códigos.
* **Alcance:** CLI (6 subcomandos, todos com `--format json`), MCP (`get_baseline_status` + `baseline://summary`), review remoto de PR e o fluxo de lint.
* **O Refactoring da Funcionalidade Exigiu:** o `NormalizedFinding` passou a ser um tipo canónico em `src/domain/finding/`.
* **Testes:** `tests/domain/baseline/` (5 ficheiros, **126**), `tests/application/use_cases/` (5 ficheiros, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) e `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34) + `tests/integration/test_baseline_exit_code_contract.py` (8).
* **Artefactos:** `docs/baseline-suppressions.*.md` em **5 idiomas**, ADR-012, `glossary-baseline.md`, spec/plano/inquérito e relatório de tarefa.

### **41. Subcomando `gitpr check` — Portão de CI Unificado (`src/domain/check/`, `src/infrastructure/ci/`)**

* **O que é:** um **ponto de entrada único** para pipelines de CI que orquestra análise estática, bridges SAST, verificação de segredos, review de IA opcional, pontuação de risco e classificação de baseline, e emite **um artefacto normalizado** com um exit code determinístico.
* **A Ordem do Pipeline É Contrato:** (1) resolução da política — inválida sai com `2`; (2) extração do diff com smart excludes; (3) linter de regex + segredos + bridges externas; (4) pontuação preliminar de risco; (5) **review de IA opcional**; (6) classificação de baseline; (7) avaliação do limiar `--fail-on`; (8) geração do artefacto com **mascaramento de segredos**.
* **Camada de Domínio (`src/domain/check/`):** `check_types.py` (severidade normalizada em três níveis com aliases da spec); `check_config.py` (precedência **CLI > env > predefinições**); `check_output.py` (despacho de formato e escrita atómica).
* **Camada de Infraestrutura (`src/infrastructure/ci/`):** `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (testsuites com `<failure>` e `<system-out>`) e `masking.py`.
* **Caso de Uso (`run_check.py`):** `resolve_diff_base()` (explícito → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), a execução do linter e das bridges, a pontuação de risco, o review opcional, a baseline e o envelope final. O stdout emite **apenas o artefacto**. A agregação de risco aceita `path` **ou** `file_path`.
* **Exit Codes:** `0` sucesso; `1` apontamento contável no limiar; `2` erro de execução.
* **CLI:** `gitpr check` com 8 opções e 9 variáveis `GITPR_CHECK_*`, também na TUI de configuração (categoria `check`).
* **GitHub Action Reescrita:** o `action.yml` passou a um único passo `gitpr check`. **Quebra declarada** para workflows que dependiam de `--linter` + `--fullreview`. 🆕 o cabeçalho do `action.yml` passa a trazer a nota de migração e a ligação para `docs/check-ci#7-migration-guide`; a decisão está formalizada na [ADR-0011](docs/adr/0011-action-unified-check-migration.md).
* **A Nota de Migração Tornou-se um Teste:** o `tests/unit/test_check_ci_docs_parity.py` exige as 5 variantes de idioma de `docs/check-ci.*`, as 7 secções `## 1.`–`## 7.` e os termos `action.yml`, `--linter`, `--fullreview`, `gitpr check` e `SARIF` na secção de migração — mais o termo **Legacy CI action contract** no `CONTEXT.md`. 🆕 passa também a exigir a ADR-0011 e as referências no `action.yml`.
* **Testes:** `test_run_check.py` (8), `test_check_types.py` (8), `test_check_config.py` (3), `test_check_output.py` (3), `test_masking.py` (6), `test_sarif_builder.py` (2), `test_junit_builder.py` (2), `test_check_cli.py` (5), `test_baseline_exit_code_contract.py` (8) e `test_check_ci_docs_parity.py` (**3** 🆕).
* **MCP:** a ferramenta `run_check` devolve o envelope JSON do mesmo pipeline, com `no_ia` predefinido a `true`.
* **Artefactos:** `docs/check-ci.*.md` em **5 idiomas**, ADR-013 + 🆕 ADR-0011, `glossary-check-ci.md`, spec, `plansfacts` e inquérito.

### **42. Extensão de Editor — VS Code e Cursor (`extensions/vscode/`)**

* **O que é:** uma extensão **cliente fino** que traz os fluxos do GitPR para o editor sem reimplementar nada: inicia o `gitpr-mcp` sobre stdio e chama as ferramentas existentes. Chaves, tokens, prompts e cache ficam em `~/.gitpr/`, cifrados com Fernet.
* **Arquitetura:** `GitPrMcpClient` sobre `@modelcontextprotocol/sdk` + `SessionManager` com **uma sessão por pasta** (multi-root) + verificação do contrato de ligação: as 7 ferramentas obrigatórias têm de existir; caso contrário, o `MissingMcpToolsError` instrui `pip install --upgrade gitpr-cli`.
* **8 Comandos:** `gitpr.commit`, `gitpr.review`, `gitpr.fullReview`, `gitpr.reviewMentor`, `gitpr.generatePr`, `gitpr.generatePrExplain`, `gitpr.publishPr` e `gitpr.check`.
* **Publicação em Dois Passos:** o `generatePr` abre um documento Markdown editável; o `publishPr` pede confirmação modal e trata `success`, `needs_push`, `already_exists`, `auth_required` e `error`.
* **Diagnósticos no Painel de Problemas:** o `run_check` é convertido numa `DiagnosticCollection("gitpr")`.
* **Mascaramento em Profundidade Dupla:** o `masking.ts` espelha o `src/infrastructure/ci/masking.py`.
* **Localização da Extensão:** bundles em **en, es, fr e pt-br** (4 idiomas) — a própria UI do GitPR continua em 5.
* **Metadados do Marketplace:** o `package.json` tem `icon` (`images/icon.png`), `homepage` e `bugs.url`; o pacote traz o `LICENSE` (LGPL-2.1), a condição mínima para uma listagem credível.
* **🆕 Release do VSIX Desacoplada:** o `extension.yml` ganhou uma job `github-release` dedicada que anexa o `.vsix` compilado às GitHub Releases oficiais das tags `ext-v*` — o artefacto do editor deixa de depender da release do pacote Python.
* **🆕 Portão de Marketplace Granular:** a job `publish-marketplaces` avalia o `VSCE_PAT` e o `OVSX_PAT` **de forma independente**, não bloqueia o resto do workflow e reporta cada resultado no `$GITHUB_STEP_SUMMARY`. O termo de domínio mudou de *Fail-fast secret check* para **Granular marketplace gate**.
* **🆕 Instalação com Um Clique:** `docs/editor-extension.*` (5 idiomas), o `README.md` e o `docs/marketplace-publishing-guide.md` ganharam instruções de instalação do VSIX via CLI e via GUI, com ligações diretas às releases `ext-v*`.
* **O que a Extensão Não Faz:** não faz commit, push nem publica sem confirmação. O comando `gitpr.commit` da extensão chama a ferramenta MCP `generate_commit_message` — **não** abre a TUI, que é exclusiva do terminal. O fecho automático de issues é também **exclusivo da CLI**.
* **Testes:** a sua própria suite Mocha (7 cenários, incluindo stdio real; o timeout do teste de integração passou de 20s para 60s) + `tests/test_mcp_server.py` (117 cenários).
* **Artefactos:** `docs/editor-extension.*` em **5 idiomas**, `docs/marketplace-publishing-guide.md`, ADR-014, `glossary-editor-extension.md`, specs, `plansfacts` e relatórios.

### **43. TUI de Commit Interativa (`src/ui/commit_app.py`)**

* **O que é:** o `gitpr -c`/`--commit` deixou de ser um pipeline silencioso — validava a árvore, pedia a mensagem à IA e fazia o commit de imediato, sem deixar o utilizador escolher ficheiros, rever o texto ou recusar uma má sugestão. Agora é uma `CommitApp(App)` com **4 passos** que mantém o programador no controlo até ao `git commit`.
* **Pacote:** um único módulo, `src/ui/commit_app.py` (220 linhas). Os três modais são **importados** de `src/ui/pr_publish_app.py` (`StageFilesScreen`, `CommitMessageScreen`, `CommitProgressScreen`) juntamente com o `_with_suppressed_stdout` — **zero widget duplicado**.
* **Os 4 Passos:**
  1. **Seletor de Stage:** se houver ficheiros fora do índice, abre a `StageFilesScreen` com checkboxes, "Select All", "Deselect All", "Stage Selected", "Skip Staging" e "Cancel". Índice já completo → o passo é saltado.
  2. **Geração:** lê o `get_git_staged_diff()`, recorre ao `get_git_diff()` quando nada está em stage, e chama o `generate_pr_content("commit", "commit", diff_text, provider)` numa `threading.Thread` daemon enquanto a `CommitProgressScreen` anima.
  3. **Revisão:** a `CommitMessageScreen` mostra a mensagem, que pode ser editada antes de confirmar — a mensagem **editada** é a que vai para o git.
  4. **Execução:** uma nova `CommitProgressScreen` corre o `execute_git_commit(append_coauthor_trailer(msg))` e registra o SHA no log.
* **Cada Falha Torna-se Estado, Não Exceção:** uma IA em baixo recorre ao `__("Code update")`; um commit que levanta passa a `failed` com a saída capturada. Estados terminais: `no_changes`, `cancelled`, `committed`, `failed`.
* **Auto-Saída em Sucesso:** o `log.mark_finished(False)` remove o botão, e o `_schedule_auto_exit()` agenda `set_timer(0.6, self.exit)` — o utilizador volta ao prompt sem clicar em nada. **Em erro o Fechar é mantido**, porque é aí que o log da falha fica legível.
* **`--hook` Intocado:** o caminho do hook continua sem TUI e ciente de merge (`is_merge_in_progress()` → `return` silencioso). O `prepare-commit-msg` nunca abre a aplicação.
* **Guarda de TTY:** sem `--hook`, o routing passa a consultar o `_may_ask_the_user()`; com TTY, abre a `CommitApp`; sem TTY (CI, pipe, cron), recorre ao fluxo de consola silencioso.
* **Configuração:** `GITPR_SKIP_UNSTAGED_CHECK` (salta o Seletor de Stage) e `GITPR_AUTO_STAGE` (faz stage de tudo e prossegue), ambos no `DEFAULT_CONFIG` e no schema da TUI de configuração.
* **Detalhes Textual:** `ENABLE_COMMAND_PALETTE = False`; o `on_button_pressed` trata o `#btn_close`; o `_on_commit_progress_dismissed` lê o `_commit_success` em vez de assumir sucesso quando o modal fecha.
* **Nota de Migração:** 🆕 o `docs/commit-message-ia.*` (5 idiomas) passa a dizer explicitamente que, sem TTY, o `-c` recorre ao fluxo de consola e que o `--hook` continua a ser o caminho de CI.
* **Testes:** `tests/test_commit_app.py` (**17 cenários**) — todas as transições de estado e callbacks, um fluxo headless completo e o `TestCliCommitRouting` (routing com e sem TTY).
* **Domínio:** o `CONTEXT.md` mantém os termos **Stage selector** e **Commit flow**.
* **Artefactos:** `docs/commit-message-ia.*` nos **5 idiomas** e o relatório de tarefa do Gemini.

### **44. Fecho Automático de Issues (`src/issue_autoclose/`) — Multi-Forge**

* **O que era:** a issue criada com o `gitpr -is` ficava órfã — o número só existia dentro da TUI de issues e perdia-se no instante em que esta fechava. Cada PR tinha de ser ligado à mão, e o merge não fechava nada.
* **Um pacote de 5 ficheiros:** `issue_registry.py`, `register_created_issue.py`, `resolve_close_issue.py`, `insert_close_line.py` e `__init__.py`. Cada um é uma responsabilidade: persistir, registar na criação, decidir na execução seguinte e inserir no corpo.
* **Registo Neutro em Relação ao Provedor (`IssueRegistry`):** o ficheiro passou de `.gitpr/conf/github.yml` para **`.gitpr/conf/issue.yml`**, com um campo `provider` (predefinição `github`). O `github.yml` legado é **lido** de forma transparente quando o novo não existe e é apagado na escrita/limpeza seguinte — migração sem intervenção do utilizador.
* **Auto-Ignorado com Precisão:** o `record()` corre o `git check-ignore` antes da primeira escrita e acrescenta exatamente uma linha ao `.gitignore` quando o ficheiro não está coberto — o `.gitpr/conf/` **não** é ignorado como um todo, porque o `gitpr.config.yml` é versionado de propósito ([ADR-015](docs/plans/ADR-015-project-local-pr-base.md)).
* **Registo na Criação:** o `register_created_issue()` registra o número **e o provedor** de qualquer forge suportada e engole falhas de bookkeeping — um problema de escrita local nunca transforma uma criação bem-sucedida em erro. O antigo resultado `not-github` passou a `no-provider`.
* **Decisão (`resolve_close_issue`):** filtra por flags e configuração, mas **já não** por forge; cada forge sem suporte degrada para `skipped_reason="not-supported"` em vez de ser ignorada. Verifica que a issue continua **aberta** e **não é um PR** (preferindo `IssueInfo.is_open`), apaga registos mortos (404, fechada, número de PR) e pergunta **uma vez**, avisando quando o PR tem como alvo uma branch diferente da predefinida.
* **Linha Idempotente (`insert_close_line`):** deteta qualquer palavra-chave existente (`close/closes/closed`, `fix/fixes/fixed`, `resolve/resolves/resolved`, com um prefixo opcional `owner/repo#N`, sem distinguir maiúsculas de minúsculas) e nunca duplica a linha.
* **CLI:** `--close-issue` e `--no-close-issue` — mutuamente exclusivos, recusados antes de qualquer prompt ou escrita; o `-h` ganha o conflito.
* **Ligação no Fluxo do PR:** o `_prepare_close_issue` corre **antes** do Seletor de Stage e reutiliza o contexto SCM resolvido; o `_publish_pr_directly` devolve se a forge publicou realmente; o `PrPublishApp.body_published` distingue criar/atualizar de apenas abrir um PR existente. O registo só é limpo após uma publicação real (ADR-007).
* **Contrato SCM:** `IssueInfo` (estado no vocabulário da forge, `is_open` e `is_pull_request`), `get_issue()`, `get_default_branch()` e `closing_line()` são predefinições concretas da ABC; o GitHub, o GitLab e o Bitbucket implementam-nas (sintaxe `Closes #N` no GitLab, `close #N` no GitHub/Bitbucket), e o Azure DevOps levanta `ScmNotSupportedError`.
* **Configuração:** `GITPR_ISSUE_AUTOCLOSE` (predefinição `true`, opt-out) e `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` (predefinição `5`, inteiro positivo), expostos na categoria `issue` da TUI.
* **Telemetria:** uma linha `issue_autoclose` por decisão (`asked`, `accepted`, `skipped_reason`).
* **Testes:** 5 ficheiros em `tests/issue_autoclose/` (**126 cenários**) — ida e volta do registo, schema, corrupção, falha de escrita atómica, `.gitignore`, migração legada, decisão, idempotência da linha, flags, ligação e semântica do `body_published`.
* **Artefactos:** [ADR-010](docs/adr/0010-multiforge-issue-autoclose.md), `docs/specs/issue-autoclose/` (12 ficheiros), `docs/issue-autoclose.*` em **5 idiomas**, `docs/issue-tui-help.*`, `docs/config-tui.*` e `docs/metricas-telemetria.*`, README nos 5 idiomas e relatórios de tarefa.

### **45. Base do PR por Projeto (`src/project_config.py`, `src/ui/pr_base_app.py`)**

* **O que era:** a branch alvo do PR vivia apenas no `~/.gitpr/.env` da máquina (`PR_DEFAULT_BASE`) ou era adivinhada a partir do `origin/HEAD`. Um repositório que tem como alvo a `develop` dependia de cada programador configurar a mesma variável, e o erro (um PR aberto contra a branch errada) só aparecia na forge.
* **O que é:** `.gitpr/conf/gitpr.config.yml` — um ficheiro **versionado com o projeto** que transporta a base do PR, editado pela TUI de configuração e validado antes de qualquer commit/push. Um único resolvedor serve o CLI e o MCP, para que todos os pontos de entrada concordem na precedência e nas regras de falha.
* **Resolvedor (`src/project_config.py`):** `resolve_pull_request_base()` — `--base` → ficheiro do projeto → `PR_DEFAULT_BASE` → deteção de `origin/HEAD` → `main`. A leitura/escrita de YAML preserva chaves alheias; seeding idempotente; listagem de branches; deteção da branch atual; verificação contra o remote.
* **Segunda Secção (`scm:`):** o ficheiro transporta também `scm.provider` (leitura/escrita), com as duas secções independentes, e recusa sobrescrever um bloco `scm` que não seja um mapping. É esse override que ganha à deteção remota (secção 46).
* **Falha Antes de o PR Nascer:** `BaseBranchError`/`CurrentBranchError`; a base tem de existir em `origin/<branch>` e ser diferente da branch atual — sem `origin` ou com uma ref em falta, o fluxo pára com uma dica acionável em vez de abrir um PR condenado.
* **TUI de Configuração:** categoria `project` com um atributo `store` (`env` vs `project`), `KIND_BRANCH` e três campos: caminho do ficheiro (só de leitura), branch alvo (editável) e base efetiva com origem (só de leitura).
* **Modal de Reparação (`src/ui/pr_base_app.py`, 98 linhas):** quando o alvo é a própria branch atual e a execução é interativa, um modal focado pede a branch de substituição e grava-a no ficheiro do projeto.
* **CLI:** o `--base` passa pelo `_resolve_pr_target()`; o ficheiro é semeado em todas as invocações dentro de um repositório (exceto na ajuda), anunciado apenas no caminho interativo da raiz; o `GITPR_SKIP_PROJECT_CONFIG_SEED` desativa o seed. O `gitpr seed` faz o mesmo de forma explícita e idempotente (secção 47).
* **🆕 Fronteira Formalizada:** a [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) separa o **auto-seed de runtime** (apenas `gitpr.config.yml`, no primeiro uso) do **provisionamento explícito** (`gitpr seed`, que cobre configuração, smart excludes e linter). 🆕 o `docs/config-tui.md` e o `docs/pull-request-publication.md` ganharam a nota de migração e o opt-out para CI/monorepo.
* **MCP:** o `publish_pull_request` usa o resolvedor partilhado, devolve um envelope de erro para `BaseBranchError` e expõe `warnings` no payload e no catálogo.
* **Efeito Colateral Declarado:** correr o `gitpr` num repositório passa a criar o ficheiro na primeira execução. Um ficheiro existente nunca é sobrescrito; se o `.gitignore` excluir o caminho, um aviso explica que o valor não será versionado.
* **Testes:** `tests/test_project_config.py` (**49**), `tests/test_project_config_cli.py` (7), `tests/test_pr_base_app.py` (12) + extensões em `test_config_app.py` (94), `test_config_schema.py` (**55**), `test_config_store.py` (22), `test_mcp_server.py` (117) e `test_core.py` (49).
* **Artefactos:** [ADR-015](docs/plans/ADR-015-project-local-pr-base.md), 🆕 [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md), `docs/plans/develop_natan/20261030_project_pr_base_plansfacts.md`, `docs/survey/20261030_project_pr_base_surveyfacts.md`, e `docs/config-tui.*`/`docs/install-wizard.*`/`docs/pr-descricao-padrao.*`/`docs/pull-request-publication.*` nos 5 idiomas.

### **46. Configuração SCM por Repositório e por Forge (`src/scm_settings.py`)**

* **O que era:** a forge (`GITPR_SCM_PROVIDER`), o token (`GITPR_SCM_TOKEN_ENCRYPTED`) e a base URL eram definições **globais à máquina** no `~/.gitpr/.env`. A máquina deixou de poder servir mais do que uma forge ao mesmo tempo, a credencial errada ia para o host errado quando o remote de um repositório mudava, e qualquer host self-hosted não reconhecido era mapeado em silêncio para o GitHub.
* **O que é:** a forge e a credencial passaram a **propriedades do repositório** ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)). Um único resolvedor responde "que forge, que token, que base URL se aplicam aqui?" para todas as superfícies.
* **Ordem de Resolução (`resolve_provider_choice`):** override do projeto (`scm.provider` no `.gitpr/conf/gitpr.config.yml`) → `detect_provider_from_remote(origin, default=None)` → `github`. Um valor de forge desconhecido no ficheiro do projeto é **ignorado com um aviso**, nunca fatal; `default=None` deixa o assistente distinguir "reconhecido como GitHub" de "não reconhecido" e **perguntar** em vez de assumir.
* **Um Slot por Forge:** `GITHUB_TOKEN_ENCRYPTED`, `GITLAB_TOKEN_ENCRYPTED`, `BITBUCKET_TOKEN_ENCRYPTED`, `AZURE_DEVOPS_TOKEN_ENCRYPTED` (Fernet) e as `*_BASE_URL` correspondentes. Um token nunca responde por outra forge; o `GITPR_SCM_TOKEN` em bruto continua a ser o override global de CI/CD e é tentado em primeiro lugar em todos os provedores.
* **Migração Idempotente Única (`migrate_legacy_scm_config`, sob lock):** move `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` + `GITPR_SCM_BASE_URL` para o slot da forge que nomeiam, **nunca sobrescreve** um slot já preenchido, remove as linhas legadas e continua a funcionar quando o `.env` é só de leitura (degrada para o ambiente do processo). Um token legado **sem** provedor registado é deliberadamente **deixado no lugar**, não adivinhado.
* **Assistente (`gitpr --init`):** persiste **apenas em sucesso**, escreve no slot da forge escolhida, oferece a base URL guardada como predefinição do prompt (fora do GitHub) e só escreve `scm.provider` no ficheiro do projeto quando a forge escolhida difere da detetada — o ficheiro é commitado e partilhado com a equipa.
* **TUI de Configuração:** linha editável `PROJECT_SCM_PROVIDER` e linha só de leitura `PROJECT_EFFECTIVE_PROVIDER` (forge em uso + origem), tokens e base URLs de **todas** as forges sempre listados, teste de token contra o host do próprio slot e um grupo `gitlab`.
* **Superfícies Convertidas:** `src/main.py`, `src/metrics.py`, `src/release_engine.py` e `src/mcp_server.py` resolvem através do `get_scm_settings()` e propagam os avisos do resolvedor.
* **Não-Interativo Nunca Pergunta:** o `validate_or_request_scm_token(..., allow_prompt=False)` em hooks, MCP e CI devolve "corre `gitpr --init`", não apaga a credencial guardada e usa ainda um token validado que não pôde ser persistido.
* **Testes:** `tests/scm/test_scm_settings.py` (**24 cenários**) + extensões em `test_init_wizard.py` (20), `test_factory.py` (14), `test_azure_devops_provider.py` (51), `test_bitbucket_provider.py` (49), `test_gitlab_provider.py` (56) e `tests/test_tui_issue_token.py` (8).
* **Artefactos:** [ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md), `docs/plans/develop_natan/20261107_per_provider_scm_settings_plansfacts.md`, `docs/scm-multiforge.*` (5 idiomas) e o glossário `docs/plans/glossary-scm-multiforge.md`.

### **47. Subcomando `gitpr seed` — Bootstrap da Configuração Local (`src/core.py`, `src/main.py`)**

* **O que era:** a configuração local do projeto (base do PR, smart excludes, regras de linter) nascia **como efeito colateral** do callback da raiz e do descarregamento da skill. Não havia forma de a semear de modo explícito, repetível e idempotente — e um CI que dependia desses ficheiros só o descobria na segunda execução.
* **O que é:** o `gitpr seed` (e a flag global `-sd/--seed`) promovem o bootstrap a operação de primeira classe. O `seed_project_configuration()` cria:
  * `.gitpr/conf/gitpr.config.yml` (base do PR, via `ensure_project_config`);
  * `.gitpr/conf/gitpr.smart-excludes.json` (exclusões do projeto);
  * `.gitpr/skill/.gitpr.linter.yml` (regras de linter locais).
* **Nunca Destrói Sem Pedido:** um ficheiro existente **nunca** é sobrescrito; o `--force/-f` sobrescreve de propósito, e o `--all/-a` inclui também o descarregamento de todas as skills de IA. A função devolve um resumo estruturado (`config`, `smart_excludes`, `linter`, `skills_downloaded`) e o comando registra a métrica `seed`.
* **🆕 Fronteira com o Auto-Seed:** a [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) formaliza a segregação: o callback da raiz/`--install` continua a semear **apenas** o `gitpr.config.yml` no primeiro uso, enquanto o provisionamento completo (smart excludes + linter + skills) é sempre explícito através do `gitpr seed`. O opt-out `GITPR_SKIP_PROJECT_CONFIG_SEED` cobre CI e monorepos onde o auto-seed não deve correr.
* **Assistente de Instalação em 5 Etapas:** **1)** Configuração Local do Projeto (nova, confirmável), **2)** Templates de Skill (com a configuração opcional das bridges de linter externas), **3)** Git Hooks, **4)** Configuração MCP, **5)** API Key. O skip passou a ser explícito.
* **Compatibilidade:** o `generate_skill_template()` continua a semear automaticamente os smart-excludes, para que o caminho antigo não quebre; o `ensure_project_config()` e o `download_skill_file()` ganharam o parâmetro `force` mantendo a predefinição de não-sobrescrita.
* **Ajuda:** o `HELP_MAP["seed"]` aponta para `docs/install-wizard.md` e descreve `--force`/`--all`.
* **Testes:** `tests/test_seed_command.py` (**8 cenários**) — criação, idempotência, sobrescrita forçada, `--all`, CLI e flag; o `tests/test_install_wizard.py` passou a simular a configuração do linter externo.
* **Artefactos:** `docs/install-wizard.*` (5 idiomas), 🆕 [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md), `docs/plans/develop_natan/20261105_seed_local_conf_plansfacts.md`, survey e walkthrough, e o termo **Project configuration seed** no `CONTEXT.md`.

---

## **📊 Testes e Qualidade**

| Ficheiro de Teste | Cenários | Foco |
|-----------|-----------|-------|
| `tests/test_ascii_chart.py` | 9 🆕 | Formatação compacta, estados vazios, histograma, série temporal, separadores e mudança de separador |
| `tests/test_blame_engine_ranges.py` | 7 | Blame por intervalo de linhas num ficheiro |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidade, commits, duração |
| `tests/test_baseline_cli.py` | 54 | Grupo `baseline`: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Construtor de changelog: secções, títulos traduzíveis, contribuidores, ligações e datas |
| `tests/test_chat_backend.py` | 19 | Memória do chat, persistência, comandos slash |
| `tests/test_check_cli.py` | 5 | CLI do `check`: opções, formatos, exit codes |
| `tests/test_cli_help.py` | 9 | `-h` antes do subcomando: ajuda renderizada, TUI nunca aberta |
| `tests/test_commit_app.py` | 17 | `CommitApp`: mount, Seletor de Stage, auto-stage, skip, cancelar, confirmar, sucesso/falha, auto-exit, fluxo headless e routing com/sem TTY |
| `tests/test_commit_classifier.py` | 30 | Classificação Conventional Commits + `pr_number_from_merge()` nas 4 forges |
| `tests/test_config_app.py` | 94 | TUI de configuração: montagem, categorias, dirty tracking, F2 bloqueado, Ctrl+R, procura, segredos, stores `project`, painel Smart Excludes |
| `tests/test_config_cli.py` | 9 | Registo do subcomando `config`, `-h`, import preguiçoso, stdout limpo |
| `tests/test_config_issue_autoclose.py` | 10 | Getters de configuração e campos do schema de fecho de issue |
| `tests/test_config_schema.py` | 55 🆕 | Cobertura do `DEFAULT_CONFIG` derivado, sem duplicados, categorias/kinds (incluindo `KIND_FLOAT`), `seeded`, secção de skills, banner e palette |
| `tests/test_config_store.py` | 22 | Ida e volta num `.env` temporário, comentários e ordem preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuração dos revisores sugeridos (chaves e predefinições) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, modelos, `validate_ai_key()` com um SDK simulado (401 vs. rede vs. ollama) |
| `tests/test_core.py` | 49 | Fluxos principais, git diff, geração de PR, temporização, staging, coautoria, idioma dos hooks, base do PR |
| `tests/test_diff_failure_cli.py` | 3 | Exit code 1 em falha do git, 0 em diff vazio, fail-open em `--hook` |
| `tests/test_diff_parser.py` | 26 | Parser de diff por linhas/hunks, `summarize_patch()` e `split_patch_sections()` |
| `tests/test_doc_links.py` | 11 | Contrato `?lang=` do site, `doc_url()` dinâmico, ligações das TUIs e paridade de 5 idiomas |
| `tests/test_explain_command.py` | 4 | CLI do `explain`: sucesso, chave ausente, diff vazio, ajuda contextual |
| `tests/test_external_linters.py` | 33 | Bridge do Checkstyle: parser de XML, subprocesso, referência cruzada com o diff, relatório |
| `tests/test_full_diff_encoding.py` | 9 🆕 | Repositório real com ficheiro Latin-1 + guarda de hunk binário (fast path, colapso, densidade, **colapso defensivo em `generate_pr_content`**) |
| `tests/test_i18n.py` | 22 ✅ | Paridade de idiomas, chaves em falta/órfãs, identidade — **todos verdes** |
| `tests/test_install_wizard.py` | 3 | Assistente interativo de instalação (5 etapas) |
| `tests/test_issue_engine.py` | 4 | Rascunho estruturado de issue |
| `tests/test_linter_metrics.py` | 4 | Métricas do linter: erros, avisos, duração |
| `tests/test_linter_presets.py` | 5 | Presets do linter: resolução e novo descarregamento forçado |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` na CLI e na ajuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Modelos de prompt do MCP e fallback de idioma |
| `tests/test_mcp_server.py` | 117 | Ferramentas MCP (18), recursos (19), anotações, patching, CLI direta, offload, contrato da extensão, base do PR |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real via subprocesso + stdio JSON-RPC |
| `tests/test_mentor_cli.py` | 4 ✅ | CLI do `mentor` e opt-in do `--mentor` — patches em `src.main` |
| `tests/test_metrics.py` | 175 🆕 | **Ledger**: escrita síncrona, exportação, bundle/merge, migração, renderizadores, custo (incluindo os aliases do Gemini), moeda, qualidade, módulos, provedores, ciclo, Token Trend e Latency Histogram |
| `tests/test_net_timeouts.py` | 12 | Timeouts de rede/IA — alinhados com a predefinição real de 180s |
| `tests/test_plugins.py` | 17 | Descoberta de plugins, fusão de regras do linter, prompts do MCP |
| `tests/test_policy_cli.py` | 26 | Grupo `policy`: list/show/validate/use/off/install/init, confirmação e lockfile |
| `tests/test_pr_base_app.py` | 12 | Modal de troca da base do PR: montagem headless, escolha e cancelamento |
| `tests/test_pr_publish_app.py` | 59 | TUI do PR Publisher: ecrãs, fluxos, revisores sugeridos, `NoticeScreen`, modal de merge, `body_published` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de erro do linter: abortar, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save e carga JSON |
| `tests/test_project_config.py` | 49 | `project_config`: path, YAML, seed, resolvedor de base, secção `scm`, validação contra o remote |
| `tests/test_project_config_cli.py` | 7 | Fiação da base do projeto na CLI |
| `tests/test_release_cli.py` | 4 | CLI de release: opções, ajuda com epílogo documentado |
| `tests/test_release_engine.py` | 61 | **Corte ancorado no changelog, `_drop_released()`, ligações, mapa de PR por merge commit**, front matter de idioma |
| `tests/test_reviewer_resolution.py` | 18 | Escada de resolução, rejeição de um nome parcial, dedup, tolerância a falhas |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugestão de revisores (ranking, exclusão, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | CLI do `risk`: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 77 | Matriz do conjunto de regras embutido: regex, marcadores, nível, wildcard, fusão, traduções |
| `tests/test_seed_command.py` | 8 | `gitpr seed`/`--seed`: criação, idempotência, force, `--all`, métricas |
| `tests/test_skill_command.py` | 10 | Descarregamento e validação de modelos de skill |
| `tests/test_skill_context.py` | 20 | `get_skill_context()` com `quiet=True`, fallbacks, guarda do artefacto de PR |
| `tests/test_skill_frontmatter.py` | 11 | `split_front_matter()`: BOM, CRLF, YAML malformado, allowlist de idiomas |
| `tests/test_skill_templates.py` | 99 | Varredura parametrizada de **todos os templates publicados** contra referências embutidas |
| `tests/test_smart_excludes.py` | 17 | Filtro pathspec inteligente, re-download forçado, famílias binárias (incluindo `*.arrow`/`*.gguf`/`*.tflite`/`*.keras`) |
| `tests/test_suggest_reviewers.py` | 19 | Revisores sugeridos no fluxo de PR + resumo de blame |
| `tests/test_tests_command.py` | 4 | Grupo `tests` e CLI do `generate`, `--finding` e epilog com ligação |
| `tests/test_thinking_words.py` | 5 | Carregamento, análise com o separador `;` e recarregamento forçado |
| `tests/test_tui_issue_token.py` | 8 | `allow_prompt=False`: nunca pergunta, nunca apaga, aponta `gitpr --init` |
| `tests/test_updater.py` | 55 | **Portão do PyPI**: TTL deslizante, cache antiga/corrompida, escrita atómica, `force`, `bounded_urlopen`, comparador PEP 440, pré-release, skip estrito e portão de subcomando |
| `tests/test_usage_log.py` | 27 | Registo de utilização: nome derivado da data, escrita síncrona, silêncio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semântico: major/minor/patch, alvos e validação |
| `tests/test_web_links.py` | 26 | Normalização de remote (https/ssh/scp/credenciais/porta) e padrões de URL por forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 | Ativação do pack, lockfile, recusas |
| `tests/application/use_cases/test_baseline_gate.py` | 40 | Portão: carregamento, classificação, anotação, camadas e aborto |
| `tests/application/use_cases/test_baseline_summary.py` | 12 | Vista apenas de leitura do baseline para MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 | Correlação de diff/apontamentos/testes/histórico, degradação |
| `tests/application/use_cases/test_create_baseline.py` | 30 | Criação/atualização com proveniência e preservação das decisões |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 | Lotes, severidade, acerto na cache, chave em falta |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 3 | Caso de uso de explain: dict do provedor e chave ausente |
| `tests/application/use_cases/test_generate_test_file.py` | 5 | Caso de uso de geração de testes: dict do provedor e string crua |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 | Instalação do pack, origens e conflitos |
| `tests/application/use_cases/test_run_check.py` | 8 | Orquestração do check: ordem, degradação, exit codes |
| `tests/application/use_cases/test_suppress_finding.py` | 29 | Supressão/remoção nos quatro âmbitos |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 | Validação: schema fechado, checksum, compatibilidade |
| `tests/badge/test_badge_append.py` | 9 | Anexação idempotente do badge ao corpo do PR |
| `tests/badge/test_badge_builder.py` | 19 | Composição do Markdown do shields.io, escaping, regra de cor, estilo |
| `tests/badge/test_badge_cli.py` | 15 | Comando `gitpr badge`: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Contagem de alertas; `None` sem regras configuradas |
| `tests/badge/test_badge_offline.py` | 6 | Guarda offline (rede proibida, exceto loopback) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` em todos os caminhos de publicação |
| `tests/badge/test_badge_paths.py` | 10 ✅ | Ponto único de injeção nos dois publicadores |
| `tests/demo/test_demo_app.py` | 22 | TUI da visita: navegação, ecrãs, modal de ajuda |
| `tests/demo/test_demo_isolation.py` | 14 | A visita não toca no `~/.gitpr` nem na rede |
| `tests/demo/test_demo_runner.py` | 33 | Máquina de estados `DemoState`: avançar, recuar, concluído, modo texto |
| `tests/demo/test_demo_scenarios.py` | 47 | Integridade dos cenários gravados (aritmética de hunks) nos 5 idiomas |
| `tests/demo/test_demo_text_mode.py` | 18 | Saída em texto simples (`--no-tui`) para CI e gravações |
| `tests/demo/test_fake_ai_provider.py` | 21 | Contrato do provedor falso: espelha o `call_ai_model` argumento a argumento |
| `tests/domain/baseline/test_baseline_comparator.py` | 21 | Classificação, contagens por estado, `resolved`→`new` |
| `tests/domain/baseline/test_baseline_fingerprint.py` | 12 | Digest de referência, linha em bruto nunca guardada, estabilidade |
| `tests/domain/baseline/test_baseline_manifest.py` | 26 | JSON canónico, checksum, chaves desconhecidas, invariantes |
| `tests/domain/baseline/test_baseline_types.py` | 15 | Estados persistidos, âmbitos e precedência |
| `tests/domain/baseline/test_suppression_policy.py` | 52 | Camadas, especificidade, overrides, dívida em atraso |
| `tests/domain/check/test_check_config.py` | 3 | Precedência CLI > env > predefinições |
| `tests/domain/check/test_check_output.py` | 3 | Despacho por formato e escrita atómica |
| `tests/domain/check/test_check_types.py` | 8 | Severidade em três níveis, aliases da spec, resumo |
| `tests/domain/finding/test_finding_types.py` | 3 | Tipo canónico de apontamento: `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Formatação uniforme e deduplicação `[Gitleaks + Regex]` |
| `tests/domain/linter/test_structured_findings.py` | 44 | `findings_out`: apontamentos estruturados e o contador de linhas do lado novo |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 | Builder puro: honestidade epistémica, URLs removidos, ordem preservada |
| `tests/domain/policy/test_policy_checksum.py` | 15 | SHA-256 do pack e deteção de edição manual |
| `tests/domain/policy/test_policy_compatibility.py` | 22 | Compatibilidade de versão e credenciais |
| `tests/domain/policy/test_policy_manifest.py` | 42 | Schema fechado, bloco `baseline:`, erros de manifest |
| `tests/domain/policy/test_policy_resolver.py` | 47 | Precedência, composição em camadas, pack ativo × base |
| `tests/domain/pr/test_explain_section_builder.py` | 6 | Renderização Markdown, parsing tolerante, `[FILL]`, payload dict e `None` |
| `tests/domain/risk/test_risk_calculator.py` | 5 | Fórmula 50/30/20, clamp de 0–100, elevação obrigatória |
| `tests/domain/risk/test_risk_rules.py` | 5 | Pesos, limiares, glob de caminhos críticos, YAML de override |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 | Deteção por configuração, manifesto e diretório; override; falha avisada |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 | Caminho convencional por framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Caso de uso completo: review → IA → validar → classificar → dry-run/aplicar |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extrator partilhado entre o chat e o `fix` |
| `tests/fix/test_fix_cli.py` | 31 | Encaminhamento do subcomando, opções, dry-run por predefinição, `--force` |
| `tests/fix/test_fix_history.py` | 21 | Registo `.gitpr/fix_history.json`: escrita atómica, leitura |
| `tests/fix/test_fix_settings.py` | 10 | Os cinco `GITPR_FIX_*` e o recurso a predefinições em valores inválidos |
| `tests/fix/test_patch_applier.py` | 19 | Invólucro do `git apply`: check/apply/reverse, branch, estado |
| `tests/fix/test_patch_extractor.py` | 13 | Blocos cercados → diff unificado validado |
| `tests/fix/test_patch_safety_classifier.py` | 22 | Matriz safe/review_required/experimental e códigos de motivo |
| `tests/fix/test_resolve_last_review.py` | 13 | Seleção do último review, exclusão dos reviews por ficheiro |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback`: reversão e as três recusas |
| `tests/infrastructure/baseline/test_local_baseline_repository.py` | 24 | Único leitor/escritor: escrita atómica, checksum, reparação |
| `tests/infrastructure/ci/test_junit_builder.py` | 2 | JUnit XML: failure/system-out |
| `tests/infrastructure/ci/test_masking.py` | 6 | Mascaramento de credenciais antes de serializar |
| `tests/infrastructure/ci/test_sarif_builder.py` | 2 | SARIF 2.1.0 e `tool.driver.rules` |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 | `git log` seguro: commits, bugs e reverts em 90 dias |
| `tests/infrastructure/git/test_test_matcher.py` | 5 | Correspondência teste ↔ produção por convenção |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Bridge do Bandit: disponibilidade, análise, mapeamento |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC: subprocesso endurecido, timeout, binário em falta |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Bridge do Gitleaks: mascaramento de segredos, âmbito por ficheiro |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Bridge do Semgrep: análise de JSON, severidade, salto de ficheiros não-Python |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 | Efeito real do baseline no review, no lint e no risco num repositório real |
| `tests/integration/test_baseline_exit_code_contract.py` | 8 | Exit code 1 para baseline inaplicável e 2 no `check` |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Efeito real do pack no review, no lint e no risco (com uma guarda de rede autouse) |
| `tests/issue_autoclose/test_close_issue_wiring.py` | 26 | Fiação no fluxo de PR: flags, prompt, publicação real, limpeza do registo |
| `tests/issue_autoclose/test_insert_close_line.py` | 19 | Inserção idempotente e reconhecimento de palavras-chave de fecho |
| `tests/issue_autoclose/test_issue_registry.py` | 26 | Registo local: round-trip, atomicidade, corrupção, `.gitignore`, repo match, migração do `github.yml` |
| `tests/issue_autoclose/test_register_created_issue.py` | 13 | Registo pós-criação: multi-forge, provedor persistido, falhas engolidas |
| `tests/issue_autoclose/test_resolve_close_issue.py` | 42 | Árvore de decisão: portões, `is_open`, PR, registos mortos, `not-supported`, confirmação |
| `tests/review/test_diff_normalizer.py` | 20 | Fins de linha, validação do diff, smart-excludes em Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: proveniência, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orquestração: portões, PR, diff, linter, comentário opcional |
| `tests/review/test_review_pr_cli.py` | 25 | CLI do `review-pr`: opções, rejeições antes da IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 51 | Provedor Azure DevOps: org/projeto, PRs, `ScmNotSupportedError`, issue read-back |
| `tests/scm/test_bitbucket_provider.py` | 49 | Provedor Bitbucket: Basic auth, workspace, PRs, `close #N` |
| `tests/scm/test_contract.py` | 44 | Contrato `ScmProvider`: assinaturas, dataclasses, erros, predefinições de issue |
| `tests/scm/test_factory.py` | 14 | `resolve_scm_provider()` + `detect_provider_from_remote(default=None)` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` obsoleto → delega no provedor |
| `tests/scm/test_github_provider.py` | 86 | Provedor GitHub: REST, headers, PRs, issues, releases, read-back de revisores |
| `tests/scm/test_gitlab_provider.py` | 56 | Provedor GitLab: API v4, namespace, cabeçalhos sintetizados, `overflow`, `Closes #N` |
| `tests/scm/test_init_wizard.py` | 20 | Assistente `--init`: deteção da forge, slot por forge, validação, persistência |
| `tests/scm/test_release_publish.py` | 8 | Publicação de release por forge (GitHub/GitLab) |
| `tests/scm/test_scm_settings.py` | 24 | Resolvedor de forge/credencial: precedência, migração legada, avisos, só de leitura |
| `tests/split/test_apply_split_plan.py` | 11 | O único módulo que muta: indexação seletiva, um commit por grupo |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unidades → grupos → pré-validação de conflitos → mensagens |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, orçamento/corte, chamada de IA, validação da resposta |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()`: texto ↔ modelo, puro |
| `tests/split/test_selective_stager.py` | 11 | Indexação de subconjuntos, verificação contra o HEAD, índice limpo |
| `tests/split/test_split_cli.py` | 31 | CLI do split: opções, dry run por predefinição, `--apply` |
| `tests/unit/test_version_consistency.py` | 5 ⚠️ | SSOT da versão: `pyproject.toml`, `CLAUDE.md` e `GEMINI.md` contra `src.updater.__version__` — **2 falhas** porque ambos os docs ainda dizem 1.6.0 |
| `tests/unit/test_check_ci_docs_parity.py` | 3 🆕 | Paridade de 5 idiomas e 7 secções do `docs/check-ci.*` + ADR-0011 + referências no `action.yml` |
| `tests/sync_i18n.py` | — | Script de verificação de cobertura de i18n (scaffold; nunca executado) |

**Total:** **3436 cenários recolhidos em 160 módulos de teste** (64 na raiz + 12 em `tests/application/use_cases/` + 10 em `tests/fix/` + 10 em `tests/scm/` + 10 em `tests/infrastructure/` + 21 em `tests/domain/` + 7 em `tests/badge/` + 6 em `tests/demo/` + 6 em `tests/split/` + 5 em `tests/issue_autoclose/` + 4 em `tests/review/` + 3 em `tests/integration/` + 2 em `tests/unit/`; **+14 cenários, +1 ficheiro** desde o relatório anterior).

**Notas de qualidade desta versão:**
- **✅ O bloqueio que congelava a suite continua fechado:** o `-c` sem `--hook` não abre a `CommitApp` quando não há TTY — o routing consulta o `_may_ask_the_user()`.
- **✅ O teste do commit headless continua estável:** `Button.press()` + `pilot.pause()`, predicado de mount e timeout de 10 s.
- **🆕 Gráficos testáveis sem Textual:** o `tests/test_ascii_chart.py` exercita o renderer puro e a composição dos separadores em separado — a lógica dos gráficos não depende de um terminal real.
- **🆕 O colapso binário ganhou cobertura de chamadores externos:** o `test_full_diff_encoding.py` prova agora que o `generate_pr_content` sanitiza um diff cru com `U+FFFD`, para além do caminho que veio do git.
- **🆕 A nota de migração do `action.yml` tornou-se um teste:** a paridade documental passa também a fixar a ADR-0011 e as referências no ficheiro da action.
- **⚠️ 2 falhas de consistência de versão:** o `CLAUDE.md` e o `GEMINI.md` continuam a declarar 1.6.0 enquanto o `src/updater.py` já está em 1.7.0; o `tests/unit/test_version_consistency.py` falha esses dois cenários. A correção é atualizar os dois documentos.
- **O `tests/conftest.py` continua hermético:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false`, `LANG_VERSION` na versão do código e `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
- **Guarda de rede em vários sítios:** `tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py` e `tests/split/conftest.py`.
- **Fixtures de git real:** `tests/fix/git_fixture.py`, `tests/split/git_fixture.py` e `tests/test_full_diff_encoding.py`.
- **A suite da extensão é separada:** 7 cenários Mocha em `extensions/vscode/src/test/mcp_client.test.ts`, dos quais 1 integra realmente com o servidor MCP Python via stdio; não entra na contagem do pytest.
- **O CI corre em Python 3.10 e 3.13** com `fail-fast: false`, e a release tem workflow próprio com Trusted Publishing.

---

## **🌐 Internacionalização e Documentação**

* **Cobertura i18n:** **1787 chaves** em **todos os seis** dicionários (`es.json`, `es_es.json`, `fr.json`, `fr_fr.json`, `pt_br.json`, `pt_pt.json`). O código usa as mesmas chaves: **0 sem tradução** e **0 órfãs**. Eram 1772 × 6 na janela anterior.
* **🆕 O `__lang_version__` subiu para v0.0.42:** as novas strings de preços por token, moeda, validação `KIND_FLOAT`, gráficos/separadores ASCII e instalação do VSIX entraram nos seis dicionários; a renovação OTA entrega a tradução nova sem reinstalar.
* **Fontes de tradução em lockstep:** uma chave nova tem de existir no código (fonte), em `langs/pt_br.json` (**lista mestra**), nos dicts FR/ES de `scripts/sync_all_langs.py` e nos valores curados de `scripts/fix_mangled_i18n_keys.py`; o extrator de chaves é **AST** nos dois lados.
* **Tópicos novos:** **nenhum** tópico canónico novo nesta janela — a documentação trabalhou sobre os tópicos existentes e sobre as ADRs.
* **🆕 Tópicos atualizados nesta janela:**
  - `docs/editor-extension.*` (**5 ficheiros**) — release do VSIX desacoplada, portão de marketplace granular e instalação com um clique.
  - `docs/auto-update.*` (**5 ficheiros**) — semântica estrita do `GITPR_SKIP_UPDATE_CHECK` e nota de migração.
  - `docs/commit-message-ia.*` (**5 ficheiros**) — nota de migração do commit interativo sem TTY.
  - `docs/tutorial/install-from-source.*` (**5 ficheiros**) — a mesma nota de migração para o interruptor de atualização.
  - `docs/marketplace-publishing-guide.md` (**1 ficheiro, EN**) — ligações diretas às releases `ext-v*`; continua parcial.
  - `docs/config-tui.md` (**só EN**) — seed da configuração do projeto e opt-out para CI/monorepo.
  - `docs/pull-request-publication.md` (**só EN**) — a mesma nota de seed/opt-out.
  - `README.md` — ligação para a instalação do VSIX.
* **`CONTEXT.md` (atualizado):** o glossário de domínio passou de **35 para 44 termos** (+9). Entre outros, entraram **Granular marketplace gate** (substituindo *Fail-fast secret check*), **Seed segregation**, **Draft extension release**, **ASCII chart**, **Chart data**, **Tabbed metrics view** e os termos de preço/moeda.
* **Documentação em 5 idiomas:** **52 tópicos canónicos** em `docs/` (contagem inalterada) — **50 com cobertura completa nos 5 idiomas** e **2 tópicos parciais** (`github-issue-prompt-com-gh`, `marketplace-publishing-guide`).
* **Novos artefactos de especificação:** [ADR-0011](docs/adr/0011-action-unified-check-migration.md), [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md), [ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) e [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) em `docs/adr/` (agora **8 ficheiros**; a numeração de `docs/adr/` não é contínua porque a ADR-012/013/015/016 vivem em `docs/plans/`).
* **Skills locais do Claude Code:** `.claude/skills/` com **29 skills** (contagem inalterada).
* **Índice de Memória:** `.claude/memory/MEMORY.md` com **41 padrões** (🆕 +1).
* **Relatórios de tarefa:** `docs/claude-code/reports/develop_natan/` (**119**; **+1**) + `docs/claude-code/reports/feat/` (**12**, inalterado) + `docs/claude-code/reports/main/` (**1**) e `docs/gemini/reports/` (**54**; **+12**), mais `docs/gemini/walkthrough/` (**31**; **+12**).
* **Relatórios de estado:** `docs/reports/` — este é o **24.º relatório** (v0.0.24); o v0.0.23 nunca é sobrescrito.
* **Planos de desenvolvimento:** **174** ficheiros em `docs/plans/` (+13) + **36 ficheiros em `docs/gemini/plans/`** (+12) + **46 ficheiros em `docs/survey/`** (+12).

---

## **🔄 Pipeline de Distribuição**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Atualização obrigatória com janela decorrida:** a execução verifica o PyPI no arranque e **bloqueia com exit code 1** se houver uma versão mais recente, imprimindo `pip install --upgrade gitpr-cli`. A resposta fica em cache durante **1 hora** e o `--update` consulta **sempre** a rede. A comparação é PEP 440: uma pré-release remota não bloqueia uma instalação estável, uma versão ilegível conta como desconhecida. O portão cobre os subcomandos quando há TTY; CI, pipes, hooks, MCP e `-h` ficam isentos.
3. **GitHub Releases:** a produção é automatizada pelo `release.yml` nas tags `v*` — build, validação, Trusted Publishing e criação da release com artefactos.
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala via pip) + `tests.yml` (matriz Python 3.10 e 3.13) + `extension.yml` (VSIX; 🆕 job `github-release` nas tags `ext-v*` e job `publish-marketplaces` com um portão granular por PAT) + `release.yml` (PyPI + GitHub Release nas tags `v*`). O `action.yml` chama um único passo `gitpr check` — uma **quebra declarada** para quem usava `--linter` + `--fullreview`, coberta pelo teste de paridade documental e pela ADR-0011.
5. **Servidor MCP:** ponto de entrada `gitpr-mcp` via `pyproject.toml`
6. **Extensão de Editor:** pacote VSIX independente (`natafiuza.gitpr`), com os canais VS Code Marketplace e Open VSX; exige Node.js 20+ apenas para o build, não para o uso. O `.vsix` é anexado à GitHub Release das tags `ext-v*` mesmo quando a publicação no marketplace está desligada.
7. **Templates e idiomas OTA:** `templates/` e `langs/*.json` servidos a partir do GitHub (main) — `__lang_version__` **v0.0.42** 🆕 renova as cópias locais em `~/.gitpr/langs/`; `__scripts_version__` **v0.0.4** renova os hooks.
8. **Versão derivada do código:** o `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — o `__version__` é a fonte **única**, garantida pela [ADR-014](docs/adr/0014-canonical-package-version-ssot.md) e pelo `tests/unit/test_version_consistency.py`.
9. **O `package-data` importa:** os quatro packs embutidos (`src/policy_packs/*`) viajam no wheel.
10. **✅ Estado da release — 1.6.0 e 1.7.0 publicadas:** o PyPI serve a **1.7.0**. A 1.6.0 foi tagueada em `f5088c4`; a 1.7.0 tem secção própria no `CHANGELOG.md` (`6d1f1dc`) e bump em `2354a30`. A `develop_natan` foi integrada na `main` pelo PR #310.
11. **⚠️ Tag da 1.7.0 desalinhada:** a tag `v1.7.0` no origin continua a apontar para `b2688c4`, um commit **anterior** ao bump, onde o `__version__` era 1.6.0. A guarda do `release.yml` compara tag e `__version__`; alinhar a tag ao commit `2354a30` (ou recriá-la) é o item pendente para que a GitHub Release reflita o código publicado.

---

## **📈 Evolução desde o Relatório Anterior (v0.0.23)**

| Área | v0.0.23 (anterior) | v0.0.24 (atual) |
|------|-------------------|-----------------|
| **Versão do GitPR** | 1.6.0 — no changelog e na `develop_natan`, **sem tag**, PyPI na **1.5.0** | **1.7.0** — publicada no PyPI; a tag `v1.7.0` existe mas aponta para o commit anterior ao bump |
| **Versão de Idioma** | v0.0.41 | **v0.0.42** — dicionários sincronizados, +15 chaves |
| **Versão dos Scripts de Hook** | v0.0.4 | v0.0.4 (inalterada) |
| **Provedores de IA** | Gemini + DeepSeek + Ollama | Inalterado |
| **Idiomas** | 5 idiomas, 6 dicionários | Inalterado |
| **Subcomandos** | 16 | 16 — inalterado; 4 grupos (baseline 6, metrics 8, policy 7, tests 1) |
| **Flags da CLI** | 34 opções na raiz | 34 — inalteradas |
| **Configuração SCM** | Por repositório e por forge | Inalterada |
| **Fecho de Issues** | Multi-forge | Inalterado |
| **Seed de Configuração** | `gitpr seed`/`--seed` + assistente de 5 etapas | Comportamento inalterado; 🆕 fronteira formalizada na ADR-0019 |
| **Camadas** | 9 pacotes de domínio, 15 casos de uso | Inalterado — o `ascii_chart.py` é apresentação pura |
| **Ferramentas MCP** | 18 ferramentas / 19 recursos / 7 prompts | Inalterado |
| **`DEFAULT_CONFIG`** | 55 chaves, derivadas do schema (`seeded`) | **56 chaves** — `GITPR_METRICS_CURRENCY` |
| **Schema de Configuração** | 100 `ConfigField` / 18 categorias / 55 `seeded` | **109 / 18 / 56** — `KIND_FLOAT` + 9 campos de moeda/preço |
| **i18n (chaves por ficheiro)** | 1772 × 6 | **1787 × 6** — 0 em falta, 0 órfãs ✅ |
| **Portão de Atualização** | TTL deslizante de 1 h, `-u` sempre online, PEP 440 | Código inalterado; 🆕 nota de migração do interruptor estrito |
| **Linter** | Regex + segredos + SAST + `findings_out` + ciente do baseline | Inalterado |
| **Diff / Smart Excludes** | + guarda de hunk binário e 100+ padrões | + **colapso defensivo no `generate_pr_content`** e 4 famílias binárias novas |
| **Git Hooks** | post-checkout/post-merge/pre-push no ledger | Inalterado — o `prepare-commit-msg` continua não-interativo |
| **Camada SCM** | 4 forges, paginação, `web_links.py` | Inalterada |
| **Motor de release** | secção `[1.6.0]`; 1.5.0 publicada; 1.6.0 sem tag | secção `[1.7.0]`; **1.6.0 e 1.7.0 publicadas**; tag da 1.7.0 desalinhada |
| **Métricas** | Token Trend e Latency Histogram na CLI/TUI/MCP | + **gráficos ASCII e separadores** no dashboard; 🆕 preços por modelo e moeda |
| **TUI de Commit** | guarda de TTY e teste headless estável | Inalterada; 🆕 nota de migração do fluxo sem TTY |
| **Extensão** | workflow endurecido com `dry_run` e fail-fast | + **release do VSIX desacoplada** e **portão granular por marketplace** |
| **Documentação** | 52 tópicos (50 completos + 2 parciais) | **52 tópicos (50 completos + 2 parciais)** — 4 ADRs novas |
| **Agenda de domínio** | `CONTEXT.md` com 35 termos | **44 termos** — Granular marketplace gate, Seed segregation, ASCII chart etc. |
| **CI** | `tests.yml` + `action.yml` + `extension.yml` + `release.yml` | Ficheiros base inalterados; `extension.yml` reestruturado |
| **Suite de Testes** | 3422 cenários (159 ficheiros) | **3436 cenários (160 ficheiros) — +14 cenários, +1 ficheiro** |
| **Estabilidade da Suite** | Sem bloqueio conhecido | **2 falhas de consistência de versão** no `test_version_consistency.py` (docs em 1.6.0) |
| **Dependências** | Sem novas dependências Python | Nenhuma nova — o `ascii_chart.py` usa apenas a stdlib |
| **Commits desde o relatório** | 16 commits, 0 merges | **13 commits, 13 merges** (#295–#310) |
| **PRs referidos** | 14 PRs (#261 … #291) | **13 PRs integrados** (#295, #296, #297, #298, #299, #301, #302, #304, #305, #307, #308, #309, #310); issues fechadas #294, #300, #303 e #306 |
| **Índice de Memória** | 40 padrões | **41 padrões** |
| **Relatórios de tarefa** | 118 claude-code, 42 gemini, 19 walkthrough | **119 claude-code (+12 feat, +1 main), 54 gemini, 31 walkthrough** |
| **Planos de desenvolvimento** | 161 planos, 24 gemini, 34 surveys | **174 planos, 36 gemini, 46 surveys** |

---

## **🚧 Próximos Passos**

* **🆕 Atualizar o `CLAUDE.md` e o `GEMINI.md` para 1.7.0:** o bump `2354a30` só mexeu no `src/updater.py`, e ambos os documentos continuam a dizer 1.6.0. O `tests/unit/test_version_consistency.py` tem **2 falhas** por causa disto — é a correção mais urgente desta janela.
* **🆕 Alinhar a tag `v1.7.0`:** a tag no origin aponta para `b2688c4` (anterior ao bump, onde o `__version__` era 1.6.0). A guarda do `release.yml` compara tag e versão; mover/recriar a tag no commit `2354a30` faz a GitHub Release corresponder ao PyPI 1.7.0.
* **🆕 Traduzir o `marketplace-publishing-guide`:** o tópico continua só em inglês; PT-BR, PT-PT, ES e FR continuam pendentes.
* **🆕 Traduzir as edições apenas-EN do `config-tui` e do `pull-request-publication`:** as notas de migração de seed/CI e de opt-out entraram apenas na versão inglesa.
* **🆕 Publicar a extensão nos marketplaces:** o portão granular já avalia o `VSCE_PAT` e o `OVSX_PAT` de forma independente e o VSIX já está anexado à GitHub Release, mas a publicação depende das credenciais.
* **🆕 Documentar preços/moeda no `metricas-telemetria`:** os campos `GITPR_METRICS_PRICE_*` e `GITPR_METRICS_CURRENCY` estão na TUI e no código, mas o tópico de telemetria ainda não os explica.
* **Nota de migração do SCM por repositório:** as chaves `GITPR_SCM_PROVIDER`, `GITPR_SCM_TOKEN_ENCRYPTED` e `GITPR_SCM_BASE_URL` foram removidas do `.env` pela migração automática. Quem automatizou a escrita dessas chaves precisa de saber que agora há um slot por forge. **Item herdado, ainda em aberto.**
* **Traduzir `docs/metricas-telemetria.*`:** PT-BR, PT-PT, ES e FR continuam atrás do original inglês. **Item herdado, ainda em aberto.**
* **Rastrear o host self-hosted não reconhecido:** a resolução de remote só reconhece domínios que contenham o nome da forge; um GitLab/GitHub num domínio personalizado recorre ao GitHub até alguém correr o `gitpr --init` e escrever o override. **Item herdado, ainda em aberto.**
* **Provedor Anthropic Claude:** suporte direto à API do Claude (`claude-sonnet-5`). **Item herdado, ainda em aberto.**
* **Mais provedores:** OpenAI direto, provedores locais adicionais. **Item herdado, ainda em aberto.**
* **Nota de migração do `GITPR_SKIP_UPDATE_CHECK`:** ✅ **concluído** — o `docs/auto-update.*` e o tutorial de instalação descrevem o parser estrito.
* **Nota de migração do `-c` interativo:** ✅ **concluído** — o `docs/commit-message-ia.*` nos 5 idiomas explica o fluxo sem TTY e o `--hook`.
* **Tag e publicação da 1.6.0/1.7.0:** ✅ **concluído** — o PyPI serve a 1.7.0 (o alinhamento da tag 1.7.0 fica como item novo acima).
* **Merge da `develop_natan` na `main`:** ✅ **concluído** — PR #310.
* **Dívidas da TUI de Configuração:** ✅ **concluído** — auditoria formal e fecho registados nos relatórios de 2026-11-09.
* **Item Smart Excludes na TUI:** ✅ **concluído** — auditoria formal e fecho registados.
* **Nota de migração do `action.yml`:** ✅ **concluído** — `action.yml`, ADR-0011 e o teste de paridade com 3 cenários.

### ✅ Concluído nesta janela (2026-11-08 → 2026-11-10)

* ~~**O dashboard só tinha secções textuais**~~ 🆕 — PR #307 (`07822f7`): `src/ascii_chart.py`, separadores **Events**/**Charts & Trends**, bindings `1`/`2` e `tests/test_ascii_chart.py` (9 cenários) ([ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
* ~~**Os binários podiam entrar por um chamador externo**~~ 🆕 — PR #304 (`c1cddbb`): `collapse_binary_hunks()` no início do `generate_pr_content` e `*.arrow`/`*.gguf`/`*.tflite`/`*.keras` nos smart excludes.
* ~~**A release da extensão dependia da release do pacote**~~ 🆕 — PR #302 (`3bcc6c9`): job `github-release` nas tags `ext-v*`, portão de marketplace granular e instalação do VSIX nos 5 idiomas.
* ~~**O custo e a moeda eram fixos**~~ 🆕 — PR #295 (`c5485dd`): `KIND_FLOAT`, `GITPR_METRICS_CURRENCY`, 8 campos de preço e aliases do Gemini com preço.
* ~~**A quebra do `action.yml` não estava formalizada**~~ 🆕 — PR #301 (`7bd6678`): nota de migração no `action.yml`, [ADR-0011](docs/adr/0011-action-unified-check-migration.md) e teste de paridade.
* ~~**O fallback ES/FR do site não tinha contrato**~~ 🆕 — `80378b0`: [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md).
* ~~**A fronteira entre auto-seed e `gitpr seed` não estava escrita**~~ 🆕 — `98b23cc`: [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md).
* ~~**A nota de migração do commit interativo não existia**~~ 🆕 — `1995213`: `docs/commit-message-ia.*` nos 5 idiomas.
* ~~**A semântica estrita do `GITPR_SKIP_UPDATE_CHECK` não estava documentada**~~ 🆕 — `1995213`: `docs/auto-update.*` e tutorial.
* ~~**O seed da configuração do projeto não tinha nota de migração**~~ 🆕 — `7cf4ac8`: `docs/config-tui.md` e `docs/pull-request-publication.md`.
* ~~**As dívidas da TUI de configuração e dos Smart Excludes estavam abertas**~~ 🆕 — `8886976` e `a7c316a`: auditoria formal e fecho.
* ~~**O changelog não tinha a 1.7.0**~~ 🆕 — `6d1f1dc`: secção `## [1.7.0] - 2026-11-10`.
* ~~**A versão do pacote e dos dicionários estava em 1.6.0/v0.0.41**~~ 🆕 — `2354a30`: bump para **1.7.0** e **v0.0.42**.
* ~~**A 1.6.0 estava tagueada mas não publicada**~~ — o PyPI passou a servir a 1.6.0 e depois a 1.7.0.
* ~~**A janela inteira estava na `develop_natan`, sem merge**~~ — o PR #310 levou a branch para a `main`.

---

**Relatório gerado em:** 2026-11-10  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
