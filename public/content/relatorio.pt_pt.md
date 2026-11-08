# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.23 (2026-11-08)**

## **📌 Visão Geral**

O **GitPR** é uma ferramenta de CLI (Command Line Interface) avançada para automação de processos Git utilizando Inteligência Artificial (Google Gemini / DeepSeek / Ollama). O objetivo principal é atuar como um assistente inteligente local que faz Code Reviews, gera Pull Requests, mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo de trabalho do desenvolvedor (Shift Left).

**Novidades desta versão (v0.0.23):**
- **A forge passou a ser propriedade do repositório, não da máquina:** `src/scm_settings.py` resolve, numa única função, **qual forge, qual token e qual base URL** valem naquele repositório — override do projeto → remote `origin` → `github`. Cada forge ganhou o seu próprio slot de credencial em `~/.gitpr/.env` (`GITHUB_/GITLAB_/BITBUCKET_/AZURE_DEVOPS_TOKEN_ENCRYPTED` + `*_BASE_URL`), e as chaves legadas `GITPR_SCM_PROVIDER`/`GITPR_SCM_TOKEN_ENCRYPTED`/`GITPR_SCM_BASE_URL` são migradas **uma única vez, de forma idempotente** ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)).
- **O fecho automático de issue deixou de ser exclusivo do GitHub:** o GitLab e o Bitbucket implementam o read-back do contrato (`get_issue`, `get_default_branch`, `closing_line`) e o Azure DevOps degrada para `not-supported` em vez de ignorar a funcionalidade. O registo local mudou para `.gitpr/conf/issue.yml`, com leitura transparente do `github.yml` legado e `IssueInfo.is_open` ([ADR-010](docs/adr/0010-multiforge-issue-autoclose.md)).
- **O bootstrap da configuração local tornou-se um comando:** `gitpr seed` (e a flag `-sd/--seed`) semeia `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` e `.gitpr/skill/.gitpr.linter.yml`, com `--force` para sobrescrever e `--all` para incluir todas as skills de IA. O wizard de instalação foi reordenado em **5 etapas**, começando pela configuração local do projeto.
- **A TUI de configuração ganhou duas superfícies novas:** a categoria **Smart Excludes** (lista de fontes + editor de ficheiro do projeto) e a categoria **check** (as 9 `GITPR_CHECK_*`). E o `DEFAULT_CONFIG` deixou de ser uma cópia manual — agora é **derivado do schema** via `ConfigField.seeded`, pelo que código e schema já não podem divergir.
- **O dashboard ganhou tendência e latência:** o `metrics` passou a renderizar **Token Trend** (sparkline + barras por dia, teto de 14 dias) e **Latency Histogram** (média, mediana e P95) no CLI, na TUI e na tool MCP `get_usage_metrics`; o ledger deixou de descartar o `duration_ms` enviado apenas no `meta` do evento.
- **O prompt deixou de receber lixo binário:** `collapse_binary_hunks()` deteta hunks com uma enxurrada de `U+FFFD` e reescreve-os como `Binary files … differ`, com um caminho rápido O(1) que preserva ficheiros Latin-1 legítimos; o catálogo de smart excludes ganhou **100+ padrões** em 8 famílias binárias.
- **A dívida de estabilidade da suite foi paga:** o `-c` só abre a `CommitApp` quando existe TTY a sério (`_may_ask_the_user()`), o que **desbloqueou** o cenário que congelava a execução completa em ~33%, e o teste headless do commit foi estabilizado (clique semântico + timeout maior). A suite corre agora até ao fim.
- **A 1.5.0 foi publicada no PyPI:** o débito de release da janela anterior está fechado. A **1.6.0** está no `CHANGELOG.md` e na branch `develop_natan`, mas **ainda não tem tag nem publicação**.
- **Versão atual:** 1.6.0 (**sem tag `v1.6.0`; o PyPI serve a 1.5.0**)
- **Versão dos dicionários de idioma:** v0.0.41 (🆕 +3 desde a v0.0.38)
- **Versão dos scripts de hook:** v0.0.4 (inalterada)
- **Publicação:** PyPI (`pip install gitpr-cli`) — canal binário removido
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositório:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licença:** LGPL-2.1
- **Idiomas suportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 dicionários)

---

## **🏗️ Arquitetura Base e Bibliotecas**

* **Linguagem:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags e formatação de terminal) — **16 subcomandos** (`badge`, `baseline`, `check`, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `seed` 🆕, `split`, `tests`). Quatro deles são **grupos**: `baseline` (6 subcomandos), `metrics` (8), `policy` (7) e `tests` (1). **34 opções Click na raiz** — 🆕 `-sd/--seed`.
* **UI/Terminal:** Textual — TUI para chat interativo, edição de issues, help screen, dashboard de métricas, PR Publisher, **CommitApp** (`src/ui/commit_app.py`, 220 linhas), erros do linter (`LinterApp`), configuração (`ConfigApp`), modal `NoticeScreen`, o tour do `gitpr demo` (`src/ui/demo/`), o assistente de migração do ledger (`src/ui/metrics_migration_app.py`) e o `PrBaseApp` (`src/ui/pr_base_app.py`). 🆕 A `ConfigApp` ganhou o painel **Smart Excludes** (lista de fontes + `TextArea` + botão de criação) e o `LinterApp` passou a desativar a command palette.
* **Arquitetura em Camadas:** `src/domain/` (regra pura, sem I/O) + `src/application/use_cases/` (orquestração, **15 casos de uso**) + apresentação (CLI/TUI). **9 pacotes de domínio**: `baseline`, `check`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`. 🆕 Fora do domínio: `src/issue_autoclose/` (fluxo de fecho de issue sobre `ScmProvider` e o registo local), `src/project_config.py` (configuração versionada) e `src/scm_settings.py` (resolução de forge e credencial por repositório).
* **Extensão de Editor:** `extensions/vscode/` — cliente TypeScript fino do MCP para **VS Code e Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). Não contém regra de negócio. 🆕 O pacote transporta `icon.png`, `LICENSE` (LGPL-2.1), `homepage` e `bugs.url` para o listing nos marketplaces.
* **Criptografia:** `cryptography.fernet` para proteção local de chaves de API, tokens GitHub e tokens SCM das forges.
* **Configuração:** `python-dotenv`, `pyyaml` (linter estático e configuração do projeto) + schema declarativo próprio (`src/config_schema.py`, **100 `ConfigField`** e **18 categorias** — 🆕 `smart_excludes` e `check`). 🆕 O `DEFAULT_CONFIG` é **derivado** dos campos com `seeded=True` (**55 chaves**); as três legadas de SCM (`GITPR_SCM_PROVIDER`, `GITPR_SCM_TOKEN_ENCRYPTED`, `GITPR_SCM_BASE_URL`) saíram. O `packaging` alimenta a comparação de versões do portão (PEP 440) e a compatibilidade dos policy packs.
* **Provedores de IA:** Integração via SDK oficial do Google GenAI, OpenAI SDK (DeepSeek) e OpenAI SDK (Ollama local). Predefinições configuradas: `gemini-pro-latest` / `gemini-flash-lite-latest` e `deepseek-v4-pro` / `deepseek-v4-flash`.
* **Forge APIs:** `requests` (REST) — abstração multi-forge em `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py`; `src/github_api.py` mantido como shim depreciado. 🆕 O contrato de issue (`get_issue`, `get_default_branch`, `closing_line`) é implementado pelas **quatro** forges quando faz sentido.
* **Linter / SAST:** regex YAML + bridge Checkstyle + ruleset de segredos embutido (`src/security_ruleset.py`) + 4 bridges em `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), com modelo normalizado em `src/domain/linter/sast_finding_mapper.py` e o achado canónico em `src/domain/finding/`.
* **Infraestrutura de CI:** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) e `masking.py` (mascaramento de credenciais antes de serializar qualquer artefacto).
* **Git Internals:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py`, `risk_history_reader.py` e `test_matcher.py`.
* **Persistência local:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) com WAL, `PRAGMA user_version`, chaves UUID e transporte por bundle. 🆕 O `duration_ms` cai para `meta["duration_ms"]` antes de assumir `0`.
* **Rede:** `src/net.bounded_urlopen` (`UPDATE_FETCH_HARD_TIMEOUT = 3.0`) limita o **wall clock incluindo DNS** no portão de atualização. `get_issue()`/`get_default_branch()` usam timeout de **5 s** (`GITPR_ISSUE_AUTOCLOSE_TIMEOUT`).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 ferramentas anotadas**, **19 recursos**, 7 prompts (contagem inalterada). 🆕 `get_usage_metrics` devolve `token_trend` e `latency`, e a resolução de forge/credencial passa por `src.scm_settings`.
* **Testes:** Pytest + `unittest.mock` (**159 módulos de teste** — 63 na raiz, 12 em `tests/application/use_cases/`, 10 em `tests/fix/`, 10 em `tests/scm/`, 10 em `tests/infrastructure/` (baseline 1, ci 3, git 2, linter 4), 21 em `tests/domain/` (baseline 5, check 3, finding 1, linter 2, mentor 1, policy 4, pr 1, risk 2, tests_generation 2), 7 em `tests/badge/`, 6 em `tests/demo/`, 6 em `tests/split/`, 5 em `tests/issue_autoclose/`, 4 em `tests/review/`, 3 em `tests/integration/`, 2 em `tests/unit/` —, **3422 cenários recolhidos**) + testes e2e do servidor MCP via subprocess real (JSON-RPC stdio) + fixtures de repositório git real (`tests/fix/`, `tests/split/`, `tests/test_full_diff_encoding.py`) + guarda de isolamento de rede (`tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py`, `tests/split/conftest.py`). A extensão tem suite Mocha própria (7 testes contra o servidor MCP real).
* **Empacotamento:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; packs embutidos via `package-data`; `packaging>=23.0`. A extensão é empacotada separadamente (`vsce package`, VSIX).
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` (chama `gitpr check`) + `tests.yml` (matriz Python 3.10/3.13) + `extension.yml` (build, teste e empacotamento do VSIX; publicação no Marketplace/Open VSX em tags `ext-v*`) + `release.yml` (testes, verificação tag/versão via SSOT, build sdist/wheel, `twine check --strict`, Trusted Publishing OIDC no PyPI e criação de GitHub Release em tags `v*`). 🆕 O `extension.yml` ganhou `workflow_dispatch` com `dry_run`, guarda de tag ↔ versão e falha explícita quando falta credencial.

---

## **🧩 Módulos Implementados e Arquitetura de Ficheiros**

### **1. Operações Core e Git (`src/core.py`)**

* **Geração Estruturada:** Comunica com o LLM a pedir um retorno estritamente em JSON.
* **Map-Reduce (Diffs Gigantes):** Quando o diff ultrapassa ~90k tokens, divide-o automaticamente em lotes por ficheiro (`split_diff_into_chunks`), processa cada parte (Map) e unifica os resumos (Reduce). Suporta PRs, commits e Issues. O `provider`/`model` são agregados entre chunks e o `meta`/`modules` é encaminhado para o `log_command_metric`.
* **Tokenizer Local:** `tokenizer.json` para estimativa precisa de tokens antes do envio à IA.
* **Estimativa de Tokens:** Heurística leve `len() // 4` via `estimate_token_count()` com fallback para o tokenizer local.
* **Otimização Git Nativa:** Flags `-U1`, `-w`, `-M`, `-B` nos comandos `get_git_diff` e `get_git_full_diff`.
* **`get_git_staged_diff()`:** corre `git diff --cached -U1 -w -M -B -- <SMART_EXCLUDES>` com `encoding="utf-8"`/`errors="replace"` e `stdin=DEVNULL`, devolvendo `None` quando o git falha (nunca uma string vazia disfarçada). É a fonte preferida da mensagem de commit: o que não está em stage não entra no prompt.
* **`get_split_diff()`:** diff próprio do `split`, com `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sem** reutilizar o `get_git_diff()`.
* **🆕 Guarda de Hunk Binário:** todos os pontos de entrada de diff (`get_git_diff`, `get_git_staged_diff`, `get_unstaged_diff`, `get_git_full_diff`, `current_diff`, `get_check_diff`, `tests_generate`, `risk`) aplicam `collapse_binary_hunks()` — hunks com corridas contíguas de ≥8 `U+FFFD` (ou ≥32 com densidade ≥10%) passam a `Binary files … differ`. O caminho rápido O(1) devolve o diff intacto quando não há `U+FFFD`, preservando Latin-1/Windows-1252 legítimos.
* **🆕 Seed da Configuração Local (`seed_project_configuration()`):** cria `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` e `.gitpr/skill/.gitpr.linter.yml`, devolvendo um resumo estruturado (`config`, `smart_excludes`, `linter`, `skills_downloaded`). Nunca sobrescreve sem `force=True`.
* **Seed da Configuração do Projeto:** o callback raiz e o `--install` chamam `ensure_project_config()` (`src/project_config.py`) em cada invocação dentro de um repositório, exceto nos caminhos de ajuda. Um ficheiro existente **nunca** é sobrescrito; a criação é anunciada apenas no caminho interativo da raiz e pode ser desligada por `GITPR_SKIP_PROJECT_CONFIG_SEED` (escape hatch da suite de testes).
* **`get_base_branch(quiet=)`:** a resolução da base delega em `resolve_pull_request_base()`; o `quiet` evita que o aviso de fallback corrompa a TUI ou o stream JSON-RPC.
* **Pré-Gravação (`--pre-save`):** Flag de debug oculta que guarda o payload completo (instrução de sistema + prompt) em JSON antes de cada chamada à IA.
* **Smart Excludes com Duas Camadas:** Camada global (`~/.gitpr/conf/`) + local do projeto (`./.gitpr/conf/`), unidas em runtime. 🆕 Os loaders globais e locais ficaram blindados contra `.env` corrompido e paths ilegíveis — um ficheiro mau nunca derruba um diff ou um lint.
* **Métricas com Rastreio Temporal:** `log_command_metric()` em todos os fluxos, com `meta`, `modules` e `duration_ms`.
* **Resolução Centralizada do Output:** `resolve_output_path()` — predefinição em `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`)**: `gitpr --init` — deteta a forge pelo remote origin, pede os extras por forge, valida o token e persiste **só em caso de sucesso**; 🆕 grava no slot da forge escolhida e só cria override no projeto quando ela difere da detetada.
* **Template de Skill de Release (`ensure_release_skill_template()`)**: Transfere `templates/gitpr.release.*.md` na primeira utilização do `gitpr release`.
* **Guarda de Skill Corrompida:** `_CORRUPTED_PR_SKILL_ARTIFACT` documenta o fragmento JSON que se colou ao template `pt_br`, e o `get_skill_context()` remove-o **em memória** quando `action_type == "pr"`.
* **Registo Partilhado de Skills:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` em `src/config.py`.
* **Motor de Review com Âmbito de Cache (`generate_pr_content`):** `cache_scope` e `store_diff`; o `cache_scope` transporta também o policy pack ativo.
* **Trailer de Coautoria:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceiros. É este trailer que a TUI de commit aplica à mensagem editada antes de `execute_git_commit()`.
* **Captura de Saída do Git Blindada:** as chamadas `subprocess.run` em `src/cache.py`, `src/core.py`, `src/issue_engine.py` e `src/main.py` que capturam saída do git usam `encoding="utf-8"` **com** `errors="replace"` — incluindo o `get_git_staged_diff()`.
* **🆕 Carregamento de `.env` Resiliente:** o `load_dotenv()` em `core.py`, `i18n.py` e `spinner.py` corre dentro de `try/except` — um `.env` ausente, ilegível ou num diretório só de leitura não impede a inicialização.
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
* **Routing de Comandos:** Gere todas as flags e os **16 subcomandos** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline`, `check` e 🆕 `seed`.
* **Comportamento Predefinido:** Executar `gitpr` sem flags abre a TUI do PR Publisher.
* **Flags (34 opções Click na raiz):** 🆕 `-sd/--seed` — semeia a configuração local do projeto e sai, gravando a métrica `seed`. `--close-issue`/`--no-close-issue` continuam mutuamente exclusivas, recusadas com exit code 1 antes de qualquer prompt ou escrita, com a regra de que o `-h` vence o conflito. As restantes (`--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` etc.) continuam inalteradas.
* **Ramo do `-c`/`--commit`:** `--hook` → comportamento silencioso e merge-aware; sem hook → `CommitApp`. 🆕 **O caminho sem hook passou a exigir TTY:** o routing consulta `_may_ask_the_user()` e, num script/CI/pipe, cai no fluxo de consola em vez de abrir a TUI.
* **Fluxo de Fecho de Issue:** `_prepare_close_issue(flags, base, may_ask)` corre **antes** do Stage Selector, reutiliza o contexto SCM resolvido e devolve a decisão (linha de fecho + issue) mais o registo. A linha entra em `insert_close_line(pr_desc_body, decision)` no ponto único de montagem do corpo. `_publish_pr_directly` devolve **bool** para que o registo só seja limpo depois de uma publicação real (ADR-007).
* **Resolução da Base do PR:** o ramo `--base` passa por `_resolve_pr_target()`, que corrige um valor obsoleto do ficheiro do projeto quando a execução é interativa e, fora dela, relata a recusa. O ficheiro `.gitpr/conf/gitpr.config.yml` é semeado em cada invocação dentro de um repositório, exceto nos caminhos de ajuda.
* **Portão de Atualização nos Subcomandos:** o callback do grupo corria antes do portão raiz, pelo que o `gitpr release`, o `fix`, o `review-pr` e os restantes **nunca** eram bloqueados por um release novo. O portão corre antes do `return`, condicionado a `may_ask and not update and not help_flag and not _subcommand_help_in_argv()`. O `_may_ask_the_user()` exige um **TTY nas duas pontas** — CI, pipes, cron, hooks e o servidor MCP continuam livres.
* **`-h` Antes do Subcomando Corrigido:** `_subcommand_help_in_argv()` existe porque o Click **limpa os argumentos do contexto** antes de o callback do grupo correr. A varredura do `argv` bruto passou a tratar o caso `gitpr -h config`, que antes abria a TUI de configuração em vez da ajuda. Coberto por `tests/test_cli_help.py`.
* **Subcomando `check` — 8 opções:** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on …`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` e `--policy <name>@<version>`.
* **🆕 Subcomando `seed` — 2 opções:** `--force/-f` (sobrescreve ficheiros existentes) e `--all/-a` (também transfere todas as skills de IA).
* **Grupo `baseline` — 6 subcomandos:** `create`, `show`, `validate`, `update`, `suppress` e `unsuppress`.
* **Grupo `policy` — 7 subcomandos:** `list`, `show`, `validate`, `use`, `off`, `install` e `init`.
* **Grupo `metrics` — 8 subcomandos:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` e o oculto `hook-event`.
* **Subcomando `risk` — 4 opções:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Subcomando `mentor` — 2 opções:** `--finding`, `--provider`.
* **🆕 `DEFAULT_CONFIG` Derivado:** `src/config.py` importa o `DEFAULT_CONFIG` de `src/config_schema.py`, montado como `{field.key: field.default for field in FIELDS if field.seeded}` — **55 chaves**. O drift entre código e schema deixou de ser possível; as três chaves legadas de SCM saíram na mesma janela.
* **Variáveis de Ambiente:** as quatro `GITPR_BASELINE_*`, as duas de mentor, as duas de policy e `GITPR_RISK_INCLUDE_IN_REVIEW`. **Fora do `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY`, as **9 `GITPR_CHECK_*`** (agora com categoria no schema), `GITPR_SKIP_UPDATE_CHECK` (booleano estrito) e `GITPR_ISSUE_AUTOCLOSE`, `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` e `GITPR_SKIP_PROJECT_CONFIG_SEED`.
* **Ajuda Contextual:** `gitpr -h --explain` renderiza; `HELP_MAP["explain"]` e `HELP_PRIORITY["explain"]` apontam para `docs/explain-pr`, e `gitpr tests`/`gitpr tests generate` têm `epilog=` próprio. 🆕 O `HELP_MAP` ganhou `seed` e os links de `close_issue`/`no_close_issue` passaram a apontar para `docs/issue-autoclose`.
* **Camada de Escrita do `.env`:** `read_env_file_values()` lê **só o ficheiro** via `dotenv_values`; `save_config_values()` com `set_key`; `remove_config_value()` com `unset_key`.
* **Portão do Baseline no Fluxo:** o `main.py` resolve o portão do baseline antes do lint, do review, do full review, da auditoria de ficheiros, do review remoto e do risco.
* **Publicação Direta Refatorada:** o `_publish_pr_directly` (`--no-edit`) delega no **`publish_pull_request_use_case`** — o mesmo caminho da ferramenta MCP `publish_pull_request` — e devolve se a forge criou o PR.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` e `src/ui/pr_publish_help.py`)**

* **Interface Interativa Completa:** TUI para rever, editar e publicar Pull Requests diretamente no terminal.
* **7 Ecrãs Modais:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` e `NoticeScreen`.
* **Três Delas Também Servem o Commit:** o `StageFilesScreen`, o `CommitMessageScreen` e o `CommitProgressScreen` são importados pelo `CommitApp` — nenhum widget foi duplicado, e o helper `_with_suppressed_stdout` também é partilhado.
* **`body_published` Distingue Criar de Abrir:** a `PrPublishApp` marca quando o corpo foi realmente criado/atualizado na forge, e não apenas quando um PR existente foi aberto. É esse sinal que permite limpar o registo da issue só depois de uma publicação a sério.
* **Selo no Corpo do PR:** o `attach_pr_badge()` corre em `src/main.py` logo depois de o `pr_data` estar completo — **ponto único de injeção**.
* **`_attach_reviewers` com resolução:** Resolve o que o utilizador escreveu **antes** de enviar, reporta o que foi descartado e, num `422` de lote, **repete um a um**. Quando um provider de terceiros segue o contrato legado e devolve `None` no read-back, o log diz `(no read-back)` em vez de `attached None`.
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
* **🆕 Credencial por Forge:** um token GitLab nunca responde por um repositório GitHub. O `GITPR_SCM_TOKEN` cru continua a ser o **único** override global de CI/CD e é tentado primeiro em todas as forges.
* **Validação Multi-Forge:** `validate_or_request_scm_token(provider, repo_display, allow_prompt=True)` — 401 → loop de reautenticação preservando o rascunho. 🆕 `allow_prompt=False` (hooks, MCP, CI) nunca pergunta, nunca apaga o token guardado e devolve a instrução "corra `gitpr --init`".
* **Segredos na TUI de Configuração:** Os campos `KIND_SECRET` são editados mascarados, **nunca** apresentam o valor em claro e são encriptados com Fernet antes de serem escritos.
* **Deteção de Segredos no Fluxo:** O linter — que corre no hook de pre-commit — é uma barreira de credenciais com regras idênticas em todas as máquinas.
* **Mascaramento no CI:** o `src/infrastructure/ci/masking.py` remove chaves privadas, AWS access key ID/secret, tokens GitHub, Google, OpenAI e Slack, `Bearer`, basic-auth em URLs e atribuições genéricas de password **antes** de o artefacto ser serializado. A extensão tem um espelho em TypeScript (`masking.ts`).

### **8. Auto-Atualizador (`src/updater.py`)**

* **PyPI como Fonte Única:** o `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` e escreve a cache. Aceita `force=True`, usado pelo `--update` para **ir sempre** à rede.
* **Janela Deslizante de 1 Hora (`UPDATE_CACHE_TTL_SECONDS = 3600`):** a cache deixou de ser "válida enquanto a data local for hoje". O documento é `{"checked_at": <ISO>, "version": ...}`; o `_cache_is_fresh()` aceita uma idade em `[0, 3600)` segundos.
* **Validação na Leitura:** um `dict` genuíno, um timestamp interpretável e uma versão em string não vazia. Um timestamp **no futuro** conta como expirado, e o formato antigo com `date` é tratado como expirado e reescrito.
* **Escrita Atómica:** `tempfile.NamedTemporaryFile` no mesmo diretório + `os.replace`, com limpeza do ficheiro temporário em caso de falha.
* **Obtenção Blindada (`bounded_urlopen`):** um corpo não-JSON e um payload sem versão são **rejeitados antes de entrarem na cache**. Um teto de 3 s de wall clock, com o DNS incluído.
* **Comparação PEP 440:** o `_coerce_version()` usa o `packaging.version.Version`, tolerando o `v` inicial e os sufixos PEP 440; o `parse_version()` mantém o contrato de tuplo para os chamadores externos. O `_is_newer()` **ignora um pré-release remoto** numa instalação estável e trata uma versão ilegível de qualquer dos lados como desconhecida.
* **Bloqueio Obrigatório (`enforce_update_required()`):** Devolve `True` (depois de imprimir as duas versões e o comando pip) quando a versão publicada é mais recente; `False` quando está atualizado, quando a versão remota é **desconhecida (offline)** ou quando a verificação está desligada.
* **Semântica Estrita da Válvula de Escape:** o `GITPR_SKIP_UPDATE_CHECK` é interpretado contra `_SKIP_TRUTHY_VALUES` (`1`, `true`, `yes`, `on`, `y`).
* **`check_and_update()`:** Apenas consulta e **reporta**, nunca instala.
* **Versionamento Centralizado:** `__version__` (**1.6.0** 🆕), `__lang_version__` (**v0.0.41** 🆕), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`. O SSOT do pacote é contrato explícito ([ADR-014](docs/adr/0014-canonical-package-version-ssot.md)) coberto por `tests/unit/test_version_consistency.py`.
* **✅ Estado do release — 1.5.0 publicada:** o débito da janela anterior fechou: o PyPI serve a **1.5.0**. A **1.6.0** já está no `CHANGELOG.md` (`## [1.6.0] - 2026-11-07`), mas **ainda não foi tagueada nem publicada** — o portão continuará sem bloquear até a tag `v1.6.0` passar pelo `release.yml`.

### **9. Interface de Chat Interativa (`src/ui/chat_app.py`)**

* **TUI Completa:** Histórico de mensagens, input multi-linha, barra de estado com bindings visíveis. `ENABLE_COMMAND_PALETTE = False`.
* **Memória por Branch (`src/chat_memory.py`):** Histórico persistido por branch, com continuidade entre sessões.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`. O `/tests` (e os aliases localizados) delega no **mesmo caso de uso** que a CLI usa.
* **Auto-Patching (F5), Atualização de Diff (F2), Exportação de Sessão (F6).**
* **Extrator Partilhado:** O F5 e o `ctrl+s` chamam `patch_extractor.extract_code_blocks()` — comportamento visível idêntico, sem lógica duplicada.
* **Ajuda Localizada a Sério:** o `ChatHelpScreen` compõe cada linha via `__()`. 🆕 Os links da ajuda deixaram de apontar para `blob/main` no GitHub e passam por `doc_url()`, com o contrato `?lang=` do site (`es_es`→`es`, `fr_fr`→`fr`).

### **10. Internacionalização — i18n (`src/i18n.py`)**

* **Sistema Inspirado no Laravel:** Função `__()` com placeholders nomeados (`{count}`, `{file}`, etc.).
* **Deteção Automática:** Deteta o idioma do SO na primeira execução e guarda-o em `GITPR_LANG`; o diretório do perfil é criado **antes** de persistir.
* **5 Idiomas, 6 Dicionários:** en_us (predefinição/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Ficheiros Versionados:** o `__lang_version__` (**v0.0.41** 🆕) controla a atualização dos pacotes (`langs/*.json`).
* **✅ Cobertura:** os **seis** ficheiros têm **1772 chaves** cada. O código usa as mesmas chaves; não há chave sem dicionário nem órfã. O `tests/test_i18n.py` continua **22 verdes**.
* **🆕 A v0.0.41 tem conteúdo:** as novas strings de SCM por forge, `seed`, gráficos de métricas e resumo de blame entraram nos seis dicionários; a renovação OTA entrega tradução nova.
* **Snapshot do Ambiente (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado **imediatamente antes** do `load_dotenv()` ao nível do módulo.
* **Cache com Indexação por Idioma:** As respostas de IA em cache incluem o idioma corrente no chaveamento MD5.
* **Fonte de Tradução em Lockstep:** uma chave nova tem de existir no código, em `langs/pt_br.json` (lista mestra), nos dicts FR/ES de `scripts/sync_all_langs.py` e nos valores curados de `scripts/fix_mangled_i18n_keys.py`; o extrator usa **AST**.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Thread em background durante chamadas de IA com caracteres braille e palavras de "pensamento".
* **263 entradas por idioma:** Sincronizadas entre os 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceitam `force=`.
* **A TUI de Commit Não Usa o Spinner:** a TUI mostra o `CommitProgressScreen` com a sua própria barra animada, e a chamada à IA corre numa `threading.Thread` daemon. Em `--hook` o spinner clássico continua a ser o único feedback.
* **🆕 Carregamento de `.env` Blindado:** uma falha de leitura não impede o spinner de iniciar.

### **12. Provedores de IA (`src/ai_providers.py`)**

* **3 Provedores Suportados:** Google Gemini, DeepSeek e Ollama (local), com um primário/secundário por provedor.
* **Modo JSON & Parâmetros Determinísticos:** `temperature=0.0` e `top_p=0.1`; fallback automático entre os provedores configurados.
* **Timeout de IA:** predefinição de **180s** (`GITPR_AI_TIMEOUT`).
* **Chamadas com `quiet`:** a release e as funcionalidades novas encaminham o `quiet` até à camada de IA, para que o spinner nunca corrompa uma saída `--format json`.
* **Uma Chamada de Cada Vez, com Cache:** o mentor e o explain reutilizam a cache MD5 por prompt.
* **Payload Já Interpretado:** o `parse_explain_payload()` e o `generate_test_file` aceitam o **dict** que o provedor entrega, com fallback para a string JSON em bruto (respostas antigas da cache).
* **Supressão de Stdout no Commit:** `_generate_commit_msg()` redireciona o `sys.stdout` para `os.devnull` durante a chamada, restaurando no `finally` — um print do provider não corrompe o ecrã Textual.

### **13. Cache Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Chaveamento por hash MD5 do diff e do prompt, com indexação por idioma.
* **Seleção do Último Review (`resolve_last_review()`):** Escolhe o registo `review`/`fullreview` **mais recente** para um par repo+branch — é a porta de entrada do `gitpr fix` e do `gitpr mentor`.
* **Diff Revisto (`reviewed_diff`):** Campo no topo do registo que guarda o diff efetivamente revisto.
* **Telemetria e Duração:** Persistência de `duration_ms` e `meta_raw`. 🆕 O release passa a chave de cache por idioma declarado, pelo que mudar a língua da skill gera uma secção nova em vez de reutilizar a antiga.
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
* **🆕 Registo Provider-Agnóstico:** após uma criação bem-sucedida em qualquer forge suportada, `register_created_issue()` grava o número **e o provider** em `.gitpr/conf/issue.yml`. As falhas de bookkeeping são engolidas — um problema local nunca transforma uma criação bem-sucedida em erro — e a primeira escrita acrescenta o ficheiro ao `.gitignore`.
* **🆕 Métrica por Forge:** o evento de criação passou a ser derivado dinamicamente (`issue:<provider>_create`) em vez de fixo no GitHub.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Acompanha a evolução e a autoria histórica com classificação de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registados via `log_blame_metric()` com profundidade e número de commits analisados.
* **Passou também a alimentar o risco:** o leitor de histórico da pontuação (`risk_history_reader.py`) usa a mesma janela de 90 dias e classifica bugs/reverts por padrão, sem chamar a IA.
* **🆕 Alimenta a Sugestão de Revisores:** a análise de blame de um PR classifica os ficheiros em `files_with_blame`/`files_without_blame`, consumidos pelo resumo da secção 27.

### **16. Servidor MCP e Invocação Direta via CLI (`src/mcp_server.py`)**

* **18 Ferramentas MCP Anotadas:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates`, `review_remote_pr`, `get_baseline_status`, `get_usage_metrics`, `run_check` e `publish_pull_request`.
* **Parâmetros Opt-In no Contrato:** `review_code` e `full_review` aceitam `mentor: bool = False`; `generate_pr_description` aceita `explain: bool = False`. São **puramente por parâmetro** — nunca fazem OR com os defeitos do `.env`.
* **19 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Registo de Skills Alinhado:** O `mcp_server.SKILL_FILES` tem **11 entradas** (`tests`, `explain` e `mentor` incluídos); o `TestSkillRegistryAgreement` passa.
* **Invocação CLI Direta:** O comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca qualquer tool sem iniciar o servidor stdio; o `gitpr-mcp --list` imprime o registo completo em JSON.
* **Isolamento do Stdout Real:** O `_write_real_stdout()` escreve no `sys.__stdout__` original, com fallback para `buffer` ou nova codificação com `errors='replace'` em code pages legacy do Windows.
* **Offload do Event Loop:** o decorator `_offload` (`anyio.to_thread.run_sync`) em todas as tools — a ordem dos decorators importa (`@mcp.tool` **acima** de `@_offload`).
* **Falhas como Erro Estruturado:** as tools devolvem falha **como dado** em vez de levantar.
* **🆕 `get_usage_metrics` com Gráficos:** o payload passou a incluir `token_trend` (janela de até 14 dias) e `latency` (média/mediana/P95), os mesmos números do CLI e do dashboard.
* **🆕 Credencial por Repositório:** toda a resolução SCM passa por `src.scm_settings`; sem token, a tool responde com o comando que corrige (`gitpr --init`) em vez de tentar perguntar — uma tool call não pode parar e aguardar o utilizador.
* **Métricas MCP:** `run_check` e `publish_pull_request` escrevem linhas locais (`mcp:check`, `mcp:publish_pr`).
* **O servidor MCP nunca é bloqueado pelo portão:** o `--mcp`/`gitpr-mcp` está na lista de isenções desde a primeira versão do portão.

### **17. TUI de Dashboard de Métricas (`src/ui/metrics_app.py`)**

* **Âmbito por Repositório:** Rótulo `📁 Repository: owner/repo` e filtragem estrita por projeto.
* **Varredura Assíncrona com Overlay:** worker thread em background com uma `ProgressBar`; os testes aguardam `workers.wait_for_complete()` antes de afirmar.
* **Leitura do Ledger:** o dashboard consulta o `telemetry.db` na thread de trabalho, renderizando as secções (custo, qualidade, módulos, provedores, ciclo) a partir dos mesmos renderizadores que o CLI usa.
* **🆕 Duas Secções Novas:** `section_token_trend` e `section_latency`, com renderizadores partilhados com o CLI; o `max-height` dos cards subiu de 20 para 24 para caberem os gráficos.
* **Assistente de Migração (`src/ui/metrics_migration_app.py`):** oferece importar os ficheiros legados, importar **e** reconstruir a partir da cache de IA, ou "agora não".
* **Artefactos de Exportação Destrackeados:** o `.gitignore` é a única fonte sobre `.gitpr/metrics/export/`.

### **18. Sistema de Métricas e Telemetria (`src/metrics.py`)**

* **Âmbito por Repositório:** Todos os eventos indexados por `repo_name`, resolvido por `src/infrastructure/git/identity.py`.
* **Eventos de Hook, Linter e Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. O `gitpr check` escreve `command="check"` com `findings_total`/`findings_errors` e o exit code.
* **Evento `issue_autoclose`:** uma linha por decisão de fecho, com `asked`, `accepted` e `skipped_reason` no `meta`.
* **🆕 Token Trend (`TOKEN_TREND_MAX_DAYS = 14`):** série diária contínua (dias sem uso entram como zero) com sparkline e barras horizontais por dia; escolhe o lado (input/output) com mais movimento.
* **🆕 Latency Histogram:** distribuição por faixas com **média, mediana e P95**; eventos sem duração medida ficam de fora para não puxar as estatísticas para zero nem inflar a faixa `< 1s`.
* **🆕 O `duration_ms` já não se perde:** o ledger usa `meta["duration_ms"]` como fallback antes de gravar `0`.
* **Exportação e Limpeza:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` com confirmação interativa, `bundle`/`merge` para transportar e fundir janelas.
* **O Commit Interativo Também Mede:** o caminho `commit` continua a gravar a linha de comando com `meta` e `modules`.

### **19. Sincronização de Idiomas dos Git Hooks**

* **Versionamento Independente:** `__scripts_version__` (**v0.0.4**, inalterado).
* **Templates reescritos na janela v0.0.17:** `post-checkout`, `post-merge` e `pre-push` chamam `gitpr --quiet metrics hook-event <name>`, com guarda por `command -v gitpr` e `|| true`.
* **Mapeamento de Sufixos (`HOOK_SCRIPT_SUFFIXES`):** Os códigos de interface (`es_es`, `fr_fr`) traduzidos para os sufixos publicados (`.es`, `.fr`).
* **Escolha vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detetar uma mudança de idioma**.
* **`effective_hook_lang()`:** Resolve o idioma efetivo; o `--lang` deixou de ser descartado nesse caminho.
* **Skip de Merge-Source:** O `prepare-commit-msg` salta as fontes `message|merge|squash|commit`.
* **O `prepare-commit-msg` Continua Não-Interativo:** o hook corre com `--hook`, pelo que **nunca** abre a `CommitApp` — a TUI só existe no uso humano de `gitpr -c`, e agora só quando há TTY.
* **Os hooks correm com `--quiet` e `--hook`, pelo que nunca são bloqueados pelo portão de atualização.**

### **20. Bridge de Linters Externos e Assistente Interativo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistente `--linter-setup`:** Assistente com presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e injeção do bloco `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` com uma cadeia local → descarregamento → stale → fallback embutido.
* **TUI de Erros do Linter:** `src/ui/linter_app.py` (Textual) mostra erros críticos e warnings; em hook/quiet imprime e faz `sys.exit(1)`. 🆕 `ENABLE_COMMAND_PALETTE = False` — era o **último** app Textual com a palette ativa.
* **Relatório Markdown:** Consolidado em `.gitpr/reports/linter/` apenas quando há violações.
* **A Camada SAST É Irmã, Não Substituta:** as bridges Semgrep/Gitleaks/Bandit alimentam o **mesmo** pipeline de relatório — e também o `gitpr check`.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstração Única (`ScmProvider` ABC):** O `base.py` define o contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. e `ScmProviderError(provider, http_status, message)` — `http_status` 0 = falha de rede).
* **🆕 `IssueInfo` com `is_open`:** a flag booleana remove as comparações frágeis de string de estado na camada de decisão.
* **🆕 As Operações de Issue São de Todas as Forges:** o GitLab normaliza `opened` → `open` e emite `Closes #N`; o Bitbucket normaliza `new`/`open`/`on hold` → `open` e emite `close #N`; o Azure DevOps levanta `ScmNotSupportedError` (Work Items não são issues).
* **`list_pull_requests(repo, state, since, until)` — paginado e canónico:** os quatro provedores traduzem os estados canónicos (`open`/`merged`/`closed`/`all`) para o vocabulário nativo, paginam no cursor de cada API e filtram por uma janela de datas inclusiva.
* **Porque o filtro é por data de estado:** três das quatro forges devolvem algo mais amplo para um PR integrado, pelo que cada uma filtra a sua própria resposta contra a data que o estado implica.
* **`supports_merged_dates`:** atributo de classe, `False` no Bitbucket Cloud, que publica `merged_at` **vazio**.
* **`web_links.py`:** funções puras (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) que normalizam qualquer formato de remote.
* **`get_pull_request(repo, pr_id)` — método concreto da ABC:** a predefinição lança `ScmNotSupportedError` e cada forge implementa-o.
* **🆕 Resolução por Repositório:** o registry não lê `GITPR_SCM_PROVIDER`; `resolve_scm_provider(config)` recebe o provider já resolvido por `src/scm_settings.py` (secção 46).
* **GitHub:** `GET /repos/{o}/{r}/issues/{n}` (marcando `pull_request` e recorrendo ao número pedido quando ausente), `GET /repos/{o}/{r}` para `default_branch` (string vazia quando ausente) e a keyword `close #N`.
* **Cabeçalhos do GitLab Sintetizados:** O `changes[].diff` é um **hunk solto**; o `old_path`/`new_path` montam os cabeçalhos `diff --git a/… / --- / +++`, honrando o `/dev/null`. E o `overflow: true` **lança** em vez de rever metade de um diff truncado.
* **`request_pull_request_reviewers` devolve `list[str]`:** Devolve os logins **efetivamente anexados**, lidos do corpo do `201`. A quebra de contrato está documentada em [ADR-0009](docs/adr/0009-reviewer-attach-returns-read-back.md) e em `docs/suggested-reviewers.*`/`docs/scm-multiforge.*`.
* **Helpers de Apenas Leitura do GitHub:** `get_commit_author_login` e `get_user_login`.
* **Fail-Fast por Forge:** O Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; o Bitbucket exige `GITPR_SCM_USERNAME`; o `create_issue` no Azure lança `ScmNotSupportedError`.
* **Publicação de Release:** O `provider.create_release()` usado pelo `gitpr release --publish`.
* **Publicação de PR Partilhada:** `src/application/use_cases/publish_pull_request.py`, usado pelo `--no-edit` e pela tool MCP. Valida título/corpo, **exige `provider.token` apenas quando `check_remote_state=True`**, opcionalmente verifica PR existente e branch não empurrada, mascara mensagens de erro e devolve `PublishPrOutcome`.
* **Artefactos:** Glossário + ADR-001/ADR-005/**ADR-016**; família `docs/scm-multiforge.*.md` em 5 idiomas; **10 ficheiros de teste, 370 cenários**.

### **22. Subcomando `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Fluxo:** `git log` entre `--since` e `HEAD` → classificação Conventional Commits → bump semântico sugerido → montagem do changelog → resumo executivo por IA opcional → *prepend* no `CHANGELOG.md`.
* **O Intervalo Deixou de Vir do `git describe`:** a origem do intervalo é a **secção anterior do próprio changelog**, através de duas âncoras: o hash mais recente dessa secção e, como fallback, a tag de versão da secção.
* **`_drop_released()` — a segunda linha de defesa:** qualquer commit cujo hash curto apareça noutra secção de versão é descartado, com contagem.
* **Cada bullet tornou-se uma ligação:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **A falha de PR merged por merge commit:** uma segunda passagem (`git log --merges`) monta `hash → PR` pela ancestralidade.
* **Contribuidores resolvidos pela mesma escada dos revisores:** o `get_commit_author_login` primeiro, depois o `email_to_handle`, com cache em `~/.gitpr/cache/contributors.json`.
* **A Fronteira da Secção Era um Bug Latente:** o `upsert_changelog(force=True)` substitui da linha `## [x.y.z]` até ao próximo cabeçalho de **nível 2**.
* **🆕 Front Matter Declara o Idioma da Skill:** `src/skill_frontmatter.py` (`split_front_matter()`) lê um bloco YAML inicial com `language: <code>` (allowlist `en_us`, `pt_br`, `pt_pt`, `es_es`, `fr_fr`), **remove o bloco** antes de o corpo virar instrução de sistema e troca o idioma do i18n pelo tempo da geração — headings, rótulos **e** o idioma pedido à IA, mesmo com o resumo desligado. Parse defensivo: nunca levanta, aceita BOM/CRLF e ignora declaração inválida.
* **🆕 `_declared_language()` Restaura o Estado:** o context manager devolve `CURRENT_LANG`/`TRANSLATIONS` diretamente, sem uma segunda procura de tradução na saída; `_language_for_prompt()` importa `CURRENT_LANG` de forma lazy e normaliza `-` para `_`.
* **Classificador (`src/commit_classifier.py`)** e **Construtor com Secções Traduzíveis (`src/changelog_builder.py`)**.
* **Bump Semântico (`src/version_bump.py`)** e **Publicação** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opções no subcomando.**
* **Estado do release:** a secção `## [1.6.0] - 2026-11-07` está commitada; **não há tag `v1.6.0` nem publicação**.
* **O subcomando passa pelo portão de atualização** quando é executado num terminal interativo.
* **Artefactos:** `docs/release-notes.*.md` em 5 idiomas, ADR e `tests/test_web_links.py`.

### **23. Subcomando `gitpr config` — TUI de Configuração**

* **Ecrã Master-Detail (`src/ui/config_app.py`):** Categorias à esquerda, campos à direita, editados inline. Cabeçalho com procura (`/`) e um contador de alterações pendentes; rodapé `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schema Declarativo (`src/config_schema.py`) — a fonte única de verdade:** **18 categorias** e **100 `ConfigField`**, dos quais **7 avançados** e **55 `seeded`**.
* **🆕 `DEFAULT_CONFIG` Derivado:** o dicionário deixou de ser mantido à mão — é montado a partir de `seeded=True`, pelo que uma chave nova no schema já nasce semeada ou deliberadamente não-semeada.
* **🆕 Categoria `check`:** as nove `GITPR_CHECK_*` (`BASE`, `FORMAT`, `OUTPUT`, `FAIL_ON`, `NO_IA`, `IA_ONLY_HIGH_RISK`, `BASELINE_PATH`, `POLICY`, `TIMEOUT_SECONDS`) passaram a aparecer aqui, com os mesmos defaults do pipeline.
* **🆕 Categoria `smart_excludes`:** substitui o antigo rótulo `Diff Filters`; `SMART_EXCLUDES_VERSION` mudou de grupo e a categoria tem painel próprio (ver abaixo).
* **🆕 Painel Smart Excludes:** lista de fontes (projeto, global core, global docs), `TextArea` de edição, botão de criação do ficheiro do projeto, estado/nota e rastreio de dirty. O `F2` guarda o ficheiro do projeto pelo mesmo plano de gravação.
* **🆕 Categoria `project` e o Atributo `store`:** os campos declaram onde moram (`env` vs `project`). Os três campos do projeto são o path do ficheiro (read-only), a branch alvo (editável) e a base efetiva com a origem (read-only). O `KIND_BRANCH` renderiza a escolha de branch.
* **🆕 Categoria `scm` por Forge:** ganhou a linha editável `PROJECT_SCM_PROVIDER`, a linha read-only `PROJECT_EFFECTIVE_PROVIDER` (forge em uso e origem) e um grupo `gitlab`; tokens e base URLs de **todas** as forges são listados sempre, e o teste de token usa o host do próprio slot.
* **Categoria `issue`:** `GITPR_ISSUE_AUTOCLOSE` e `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` aparecem aqui.
* **Escrita por Store:** a `ConfigApp` despacha por `store`: valores de projeto **nunca** passam por `save_config_values()`, são gravados primeiro, suportam remoção ao esvaziar, recusam a branch atual e nunca sobrescrevem YAML ilegível. A linha da base efetiva é atualizada in-place depois de guardar.
* **Categorias `policy` e `baseline`:** os campos do pack e os quatro do baseline.
* **Secção Skills Alinhada:** O `SKILL_LABELS` tem **11 entradas** (`tests`, `explain`, `mentor`).
* **Filtragem por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` conforme o `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` sob `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` sob `[Azure DevOps]`.
* **Procura Global (`/`):** Encontra correspondências na chave ou no rótulo em todas as categorias e **ignora o filtro de visibilidade**.
* **Validação em Duas Camadas:** **offline** bloqueia o `F2` com um erro inline; **online** (apenas credenciais alteradas na sessão) corre num worker com timeout de 10s e só bloqueia em `401`/`403`.
* **Restaurar (`Ctrl+R`):** Remove a linha do `.env` em vez de reescrever a predefinição; o `Esc` com alterações pendentes pede confirmação.
* **Secções Project-Scoped:** Edita `.gitpr/skill/*.md`, `.gitpr/conf/gitpr.config.yml` e os smart-excludes do projeto, gravando atomicamente e preservando CRLF/LF.
* **Descarregamentos Forçados:** Botões que forçam o novo descarregamento dos smart-excludes, das traduções, dos presets e das thinking words via `force=`.
* **Módulo de Links Leve (`src/doc_links.py`):** o `doc_url()` saiu de `core.py` para a UI obter o link sem importar SDKs de IA. 🆕 O `DOC_LANG_PARAMS` mapeia `es_es`→`es` e `fr_fr`→`fr`, e o `CURRENT_LANG` é lido **na chamada**, pelo que `set_lang()` propaga para os links.
* **Testes:** 5 ficheiros — `test_config_app.py` (94), `test_config_schema.py` (53), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9) e `test_config_issue_autoclose.py` (10).

### **24. Registo Geral de Utilização (`src/usage_log.py`)**

* **Uma Linha por Comando:** Escreve em `~/.gitpr/logs/<uuid5>.log`, **um ficheiro por dia**, com o comando, os argumentos, o repositório, o utilizador e o timestamp.
* **Nome Derivado da Data:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")`.
* **Escrita Síncrona (decisão explícita):** Ao contrário do `log_local_metric`, que usava uma thread daemon e perdia a escrita se o processo terminasse primeiro.
* **Nunca Imprime:** O servidor MCP reserva o stdout para JSON-RPC; o módulo também nunca lança uma exceção.
* **Um Único Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms em vez de ~150 ms no Windows, em *todas* as execuções.
* **Controlo:** `GITPR_SHOW_LOGS` (predefinição `"true"`); desligado no `tests/conftest.py`.
* **Artefactos:** `docs/usage-log.*.md` em 5 idiomas; `tests/test_usage_log.py` (27 cenários).

### **25. Subcomando `gitpr fix` — Apontamentos de Review como Patches Revisáveis (`src/fix/`)**

* **O Pipeline Real:** último review da cache (`resolve_last_review`) → **uma** chamada de IA → extração e validação do diff unificado → `git apply --check` → classificação determinística → dry-run ou escrita → histórico.
* **Pacote de 8 Ficheiros:** `patch_provenance.py`, `patch_extractor.py` (partilhado com o chat), `patch_safety_classifier.py` (puro, sem I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Classificação Determinística:** Recusa um patch que atravesse mais de um ficheiro ou hunk, toque num caminho sensível, estoure o orçamento de linhas, **apague uma linha com aspeto de chamada**, venha marcado de baixa confiança ou falhe no `git apply --check`. O `--force` **nunca** contorna a aplicabilidade, só a classificação.
* **Escrita Opt-In:** O dry-run é a predefinição; qualquer mutação exige `--apply` (ou uma frase escrita com `--force`).
* **Testes:** 10 ficheiros em `tests/fix/` (**204 cenários**) com fixtures de repositório git real.

### **26. Subcomando `gitpr review-pr` — Review de PR Remoto (`src/review/`)**

* **O que é:** Orquestração, **não** um segundo motor de review — o motor existente, o linter e o renderizador alimentados com um diff que veio de outro sítio.
* **Pacote de 5 Ficheiros:** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Apenas de Leitura por Predefinição:** O `--post-comment` é o **único** caminho que escreve na forge. A tool MCP `review_remote_pr` nem sequer recebe o argumento.
* **O baseline é resolvido antes da primeira chamada de rede**, e os avisos do portão são fundidos no relatório/comentário.
* **`render.py` e o risco:** o `compose_review_content` / o `render_review_result` **antepõem** a secção `⚡ Risk Assessment` acima dos alertas do linter, quando o `GITPR_RISK_INCLUDE_IN_REVIEW` está ligado.
* **Documentação completa nos 5 idiomas:** `docs/review-pr.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md` e `.fr_fr.md`.
* **🆕 Hunk Binário no Diff Remoto:** `filter_excluded_sections` aplica sempre `collapse_binary_hunks()`, inclusive nas early exits sem patterns/secções.
* **Testes:** 4 ficheiros em `tests/review/` (**97 cenários**).

### **27. Resolução de Identidade do Revisor (`src/reviewer_resolution.py`)**

* **O Bug (duas falhas silenciosas encadeadas):** (1) **Prefill vazio** — os `handles` só com o `email_to_handle()`; (2) **Associação não verificada** — valores repassados *verbatim*, o GitHub respondia **201 sem associar ninguém** e o `_request()` retornava sem lançar.
* **Módulo:** Puro, sem I/O próprio, **que nunca lança**. O `resolve_candidates()` (antes da TUI) e o `resolve_typed_reviewers()` (no momento da associação); o `match_candidate()` faz correspondência exata e normalizada por login, nome ou e-mail.
* **Escada de Resolução:** handle já conhecido (sem qualquer pedido) → correspondência exata com uma pessoa sugerida → lookup por e-mail → validação do login na forge.
* **O que não resolve nunca é enviado:** Sai em `ResolutionOutcome.dropped` como `(value, reason_i18n)` e é descartado com um aviso visível.
* **A Mesma Escada Serve a Release:** o `release_engine` reutiliza `get_commit_author_login` + `email_to_handle`.
* **Companheiro Legado:** se um provider de terceiros devolver `None` (contrato antigo), o log diz `(no read-back)` e o fluxo segue — um read-back ausente **nunca** é tratado como falha.
* **🆕 Resumo de Blame:** `ReviewerSuggestionResult` ganhou `files_with_blame`/`files_without_blame`; o motor separa os ficheiros e o CLI imprime **uma** linha consolidada (`ℹ️ Análise de blame: X ficheiro(s) com blame, Y sem blame`) — só quando há ficheiros sem blame. Dezenas de avisos por ficheiro passaram a um único resumo.
* **Testes:** `tests/test_reviewer_resolution.py` (18) + `test_github_provider.py` (86), `test_pr_publish_app.py` (59), `test_suggest_reviewers.py` (**19** 🆕), `test_reviewer_suggestion.py` (18) e `test_main_suggest_reviewers.py` (8).

### **28. Subcomando `gitpr demo` — Visita Guiada sobre Exemplos Gravados (`src/demo/`)**

* **O que é:** Uma visita interativa que mostra as três saídas principais do GitPR — mensagem de commit, review e descrição de PR — **sem chave de API, sem repositório Git e sem rede**.
* **Pipeline Real, Resposta Falsa:** o `FakeAIProvider` espelha o `src.ai_providers.call_ai_model` **argumento a argumento**; o `demo_pipeline()` faz patch de `src.core` e `src.metrics` para que o pipeline de produção corra **intocado**.
* **O Isolamento É um Requisito, Não um Detalhe:** A visita **não pode** tocar no `~/.gitpr` do utilizador nem na rede — garantido pelo `tests/demo/conftest.py`.
* **Pacote:** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (dois exemplos localizados nos 5 idiomas). Os cenários são **módulos Python, não JSON**, para viajarem dentro do wheel.
* **TUI:** `src/ui/demo/` com um modal de ajuda; o diff é cercado como ```` ```diff ````.
* **CLI:** `gitpr demo` com `--scenario`, `--lang` e `--no-tui`.
* **Testes:** 6 ficheiros em `tests/demo/` (**155 cenários**).
* **Artefactos:** `docs/demo.*.md` em 5 idiomas, `README.*` (5) e as métricas de exemplo da visita.

### **29. Selo GitPR e o Comando `gitpr badge` (`src/branding/`)**

* **O que é:** Um selo público que atesta que um pull request passou por uma verificação de qualidade local, e um comando que imprime o snippet de adoção para o README do projeto.
* **URL Estático de Propósito:** É um Markdown do shields.io que o GitPR **nunca** vai buscar — publicar um pull request não pode passar a depender de um terceiro estar de pé.
* **Medição Honesta (`badge_data.py`):** O `collect_linter_counts()` devolve `None` quando não há regras de linter configuradas, porque uma lista vazia significa "nada foi verificado", não "nada foi encontrado".
* **O Selo Nunca Bloqueia uma Publicação:** Todos os caminhos de falha são apanhados e degradam para "sem selo".
* **Ponto de Injeção Único:** O `attach_pr_badge()` no `src/main.py`. Anexação idempotente por marcador.
* **Comando `gitpr badge`:** `--readme` e `--style`. **Só imprime** — o README nunca é modificado.
* **Configuração:** `GITPR_BADGE` (predefinição `true`) — um opt-out **apenas de leitura**.
* **Testes:** 7 ficheiros em `tests/badge/` (**83 cenários**).
* **Artefactos:** `docs/badge.*.md` em 5 idiomas e o questionário dedicado.

### **30. Subcomando `gitpr split` — Commits Atómicos por Hunk (`src/split/`)**

* **O que é:** Uma árvore de trabalho com várias preocupações deixa de se tornar um commit-blob. O `split` lê o diff não commitado, pede à IA para particionar os hunks por **intenção lógica** e propõe um commit atómico por preocupação. A árvore nunca é reescrita.
* **Pacote de 6 Ficheiros:** `split_plan.py`, `hunk_parser.py` (puro), `hunk_grouper.py`, `generate_split_plan.py` (apenas de leitura), `apply_split_plan.py` (**o único que muta**) e `__init__.py`.
* **A Garantia de Árvore Intacta É Estrutural:** O `selective_stager.py` pré-valida **todos** os grupos contra um índice temporário no HEAD e usa um `GIT_INDEX_FILE` descartável.
* **Destrutivo para o Índice, Não para a Árvore:** o `--apply` faz reset do índice para `HEAD` antes de preparar.
* **Configuração:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) e `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Valores zero ou negativos são **ignorados a favor da predefinição**.
* **Relação com a TUI de Commit:** o `split` e o `-c` atacam o mesmo problema em alturas diferentes — o `split` propõe **vários** commits a partir da árvore; o `CommitApp` organiza **um** commit a partir do índice.
* **Testes:** 6 ficheiros em `tests/split/` (**101 cenários**).
* **Artefactos:** `docs/split-command.md` em 5 idiomas, ADR-006, `glossary-gitpr-split.md`, spec/plano e questionário.

### **31. Varredura de Segredos Embutida (`src/security_ruleset.py`)**

* **O que é:** Sete regras que correm em **todas** as invocações do linter, fundidas no fim do `load_linter_rules()` depois das regras do projeto e dos plugins.
* **As Sete Regras:** cinco `error` bloqueantes — AWS access key ID, token GitHub, token Slack, chave de API Google e chave privada — e dois `warning`: URL de conexão a base de dados com credenciais e atribuição genérica de credencial, esta última atrás de um filtro de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Cobertura:** atribuição **sem aspas**, prefixos **`ASIA…`**, **`github_pat_…`** e **`xoxc-`/`xoxd-`**, **fronteira à esquerda** no nome da chave (`bypassword` não casa) e supressão de falsos positivos de runtime (`os.getenv(...)`, `process.env.X`).
* **Porque no Pacote e Não no Template:** um portão de segredos tem de se comportar **da mesma forma** em todas as máquinas e em todo o CI.
* **Configuração:** `GITPR_LINTER_SECURITY` (predefinição `true`; opt-out fail-open) e `GITPR_LINTER_SECURITY_DISABLED_RULES` (separadas por `;`).
* **Testes:** `tests/test_security_ruleset.py` (**77 cenários**) — matriz de regex, placeholders, nível, wildcard, merge e traduções; a invariante "o alerta nunca ecoa o valor" continua a valer.
* **Artefactos:** ADR-007, [ADR-0008](docs/adr/0008-secret-ruleset-coverage-extension.md), `glossary-gitpr-secret-scanning.md`, spec/plano/questionário e relatórios de tarefa.

### **32. Bridges SAST — Semgrep, Gitleaks e Bandit (`src/infrastructure/linter/external/`)**

* **O que é:** Uma camada **opt-in** que liga scanners de segurança de terceiros ao pipeline de linter existente.
* **Âmbito Delimitado:** Correm **apenas** quando ativados e **apenas** sobre os ficheiros tocados pelo diff.
* **Contrato Comum:** `ExternalLinterBridge` (ABC) com execução `subprocess` blindada (`shell=False`, timeout estrito, `stdin=DEVNULL`, UTF-8 com `errors='replace'`).
* **A Deduplicação É o Ponto:** O `deduplicate_secret_findings()` funde apontamentos do Gitleaks e do conjunto de regras regex no mesmo ficheiro e linha numa **única** entrada confirmada como `[Gitleaks + Regex]`.
* **Segredos Mascarados:** O `mask_secret_value()` garante que o valor nunca chega ao apontamento, ao log ou à telemetria.
* **Resolução em Três Camadas:** O `load_sast_config()` lê as predefinições → `.gitpr.linter.yml` (bloco `sast`, com fallback para `linter.external`) → variáveis `GITPR_SAST_*`. **Todas com predefinição `false`**.
* **Degradação Graciosa:** Uma ferramenta ativada mas ausente do `PATH` emite um aviso e a execução continua.
* **Testes:** 4 ficheiros em `tests/infrastructure/linter/external/` (**14 cenários**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + `tests/domain/linter/test_structured_findings.py` (44).

### **33. Subcomando `gitpr tests generate` — Geração de Suíte por IA (`src/domain/tests_generation/`, `src/application/`)**

* **O que é:** Gera ficheiros de teste completos e executáveis a partir do diff atual, de um ficheiro específico ou de um apontamento de review, **respeitando a convenção do repositório** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Camada de Domínio:** `TestFramework` (enum) e as dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest`; o `detect_test_framework()` deteta pelos ficheiros de configuração, manifests e conteúdo do diretório de testes; o `build_test_scaffold()` calcula o caminho convencional de destino por framework.
* **Camada de Aplicação (`generate_test_file.py`):** Orquestra a deteção, a resolução do scaffold, o prompt, a IA, o parsing do JSON e a escrita opcional. O `validate_test_syntax()` corre o toolchain local (`php -l`, `node --check`, `python -m py_compile`) — uma falha de validação é um **aviso**, não um erro.
* **Apresentação:** grupo `tests` com `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); o dry run é o defeito e a sobrescrita de um teste existente pede confirmação com **Não** pré-selecionado.
* **Documentado em 5 idiomas:** `docs/tests-generate.*.md` (5 ficheiros), com ligação no `gitpr tests generate` (epilog) e no README.
* **Skill:** `.gitpr.tests.md` em `SKILL_LABELS`, `mcp_server.SKILL_FILES` e `SKILL_FILES_BY_TYPE`.

### **34. Subcomando `gitpr explain` e a Flag `--explain` — Guia do Revisor (`src/domain/pr/`)**

* **O que é:** Um guia centrado em quem vai **rever** — o que muda, porque muda, onde focar e qual o risco de regressão.
* **Camada de Domínio (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, o renderer `build_explain_markdown()` e o `parse_explain_payload()`, que tolera saída não-JSON e **deteta placeholders** `[FILL]`/`[TODO]`.
* **`parse_explain_payload()` Aceita `dict | str | None`:** o provedor entrega o JSON **já interpretado**; a função aceita as três formas e normaliza os campos `None`.
* **Ramo de Chave em Falta:** o `generate_pr_explanation` imprime o erro padrão de chave em falta e sai com **1**.
* **Camada de Aplicação (`generate_pr_explanation.py`):** Resolução do provedor, validação da chave, carregamento do skill context (`explain`), prompt, invocação e parsing.
* **Dois Pontos de Entrada:** `gitpr explain` (`--provider`, deteção de diff ausente antes de qualquer IA) e a flag `--explain` na CLI raiz. A extensão expõe o mesmo caminho em `GitPR: Generate PR Draft (with Reviewer Guide)`.
* **`-h --explain` Corrigido:** a opção usava `dest` `explain_flag`, mas o `HELP_MAP` procurava `explain` — a ajuda contextual volta a renderizar.
* **Documentado em 5 idiomas:** `docs/explain-pr.*.md` (5 ficheiros), com ligação em `HELP_MAP["explain"]`, no epilog do comando e no README.
* **Configuração:** `GITPR_EXPLAIN_BY_DEFAULT` (predefinição `false`).

### **35. Suíte Determinística e o CI (`.github/workflows/tests.yml`, `release.yml`)**

* **O que é:** O workflow que corre a suíte em **Python 3.10** (o piso declarado no `pyproject.toml`) e **3.13**, com `fail-fast: false`.
* **`tests/conftest.py` Hermético:** Pina `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` e `LANG_VERSION` na versão do código, além de `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true` e `GITPR_SKIP_PROJECT_CONFIG_SEED=true` (a suite nunca escreve num checkout de programador).
* **🆕 Fixtures Autouse Contra Fuga de Perfil:** `english_interface` e `no_hook_script_sync` impedem que o idioma, as flags de linter e o auto-sync de hooks do perfil do programador se escapem entre os testes de CLI.
* **A Ordem Importa:** o `src.updater` é importado **antes** de as variáveis de idioma serem definidas.
* **Estabilidade dos Testes de Worker:** O `test_config_app.py` e o `test_metrics.py` aguardam por `workers.wait_for_complete()` antes de afirmar.
* **Dependência do Workflow:** O runner limpo não tem `~/.gitpr`, e o `test_demo_isolation.py` afirma que o perfil existe — o job cria o diretório e o `.env` vazio explicitamente.
* **🆕 Nesta janela a suite ganhou 146 cenários e 6 módulos:** `scm/test_scm_settings.py` (24), `test_skill_frontmatter.py` (11), `test_doc_links.py` (11), `test_seed_command.py` (8), `test_tui_issue_token.py` (8) e `unit/test_check_ci_docs_parity.py` (2), além do crescimento de `test_metrics.py` (174), `tests/scm/` (370), `test_config_app.py` (94), `test_release_engine.py` (61), `test_config_schema.py` (53), `test_project_config.py` (49), `test_full_diff_encoding.py` (8), `test_commit_app.py` (17), `test_suggest_reviewers.py` (19), `test_smart_excludes.py` (17) e `tests/issue_autoclose/` (126). **Total: 3422 cenários em 159 ficheiros.**
* **Workflow da extensão:** `.github/workflows/extension.yml` corre `npm install`, compila, testa e empacota o VSIX; em tags `ext-v*` publica no VS Code Marketplace e no Open VSX, saltando cada passo quando `VSCE_PAT`/`OVSX_PAT` está vazio. 🆕 Ganhou `workflow_dispatch` com `dry_run`, guarda de tag ↔ `package.json` e falha explícita quando falta credencial.
* **Workflow de Release:** `release.yml` corre testes, verifica a consistência tag ↔ `src.updater.__version__`, builda sdist/wheel, valida com `twine check --strict`, publica por Trusted Publishing (OIDC) e cria o GitHub Release. O SSOT de versão também tem teste próprio (`tests/unit/test_version_consistency.py`).

### **36. Ledger SQLite de Telemetria (`src/ledger.py`, `src/infrastructure/git/identity.py`)**

* **O Problema Não Era o Formato, Era a Junção:** os eventos eram escritos como ficheiros JSON por comando, de uma **thread daemon**, e depois religados à cache de IA por um emparelhamento heurístico de `(repo, branch, action, minute)`.
* **A Escrita É Síncrona, e Essa É a Decisão:** o `log_command_metric()` resolve o contexto git **uma vez por processo** e escreve a linha de imediato.
* **Schema Versionado:** `SCHEMA_VERSION` + `PRAGMA user_version`, chaves UUID, WAL + `synchronous=FULL`, e `LedgerVersionError` para recusar um bundle mais recente **antes** do `ATTACH`.
* **Invariante "Nunca Nasce Vazio":** o `ensure_ledger()` só cria a base de dados **em conjunto** com a absorção dos JSON legados.
* **Uma Janela, Um Significado:** `--since`/`--until`/`--days` estreitam **todas** as superfícies de leitura; a secção de ciclo assume os últimos 30 dias e **nomeia a janela**.
* **Cada Métrica É Calculada Uma Vez e Renderizada Uma Vez:** os pares `(style, text)` fazem a CLI e o dashboard Textual contar a **mesma** história.
* **Custo com Tabela Embutida e Override:** preços de lista dos quatro modelos, sobrepostos por `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **As duas taxas são obrigatórias**.
* **`modules` Normalizado:** os caminhos passam a dois segmentos (`src/fix`), `(root)` e `(no module)`.
* **Transporte por Bundle:** o `bundle` escreve a janela num `.db` autónomo; o `merge` anexa-o e insere por UUID.
* **`cache_backfill` Fica de Fora:** as linhas reconstruídas a partir da cache são excluídas dos agregados de execução.
* **`issue_autoclose` Entra no Ledger:** uma linha por decisão, com `asked`, `accepted` e `skipped_reason`.
* **🆕 `duration_ms` com Fallback de `meta`:** eventos que só transportavam a duração em `meta["duration_ms"]` deixaram de ser gravados como `0` — sem isso, o histograma de latência subcontaria.
* **Identidade Git sem Forge (`identity.py`):** rótulo do repo, ramo e autor numa **única** chamada ao subprocesso.
* **A Secção de Ciclo É a Única que o Ledger Não Responde:** lista PRs integrados via `ScmProvider.list_pull_requests`; todas as falhas degradam para uma única linha — `Não lido: <motivo>`.
* **Apresentação Completa:** grupo `gitpr metrics` com 8 subcomandos, um dashboard que lê o ledger numa worker thread e o assistente de migração de três opções.
* **Testes:** `tests/test_metrics.py` (**174 cenários**).

### **37. Subcomando `gitpr mentor` e a Flag `--mentor` — Modo Mentor (`src/domain/mentor/`)**

* **O que é:** Um review diz *o que* está errado e quase nunca *porque é que isso importa*. O Modo Mentor enriquece os apontamentos com **o que se está a passar**, **porque importa**, uma **analogia** do dia a dia e os **conceitos a estudar**.
* **Camada de Domínio Pura (`mentor_explanation_builder.py`):** sem I/O, converte o payload da IA num `MentorExplanation` validado e impõe **honestidade epistémica**: sem evidência suficiente, a explicação **di-lo**; URLs alucinados são **removidos**; IDs desconhecidos são **rejeitados**.
* **Camada de Aplicação (`generate_mentor_explanation.py`):** recolhe candidatos, **lotes de no máximo 10 apontamentos**, ordenados por severidade, reutilizando a cache MD5 por hash de prompt.
* **Dois Pontos de Entrada:** `gitpr mentor [--finding <id>] [--provider <name>]` e a flag `--mentor`, que acrescenta a secção ao review normal.
* **Opt-In Estrito:** `GITPR_REVIEW_MENTOR_MODE` (predefinição `false`) e `GITPR_MENTOR_INCLUDE_ANALOGY` (predefinição `true`), editáveis na TUI de configuração.
* **Skill e Registo:** O `.gitpr.mentor.md` registado em `SKILL_FILES_BY_TYPE`, em `mcp_server.SKILL_FILES` e em `SKILL_LABELS`, com templates em **5 idiomas**.
* **Testes:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) e `tests/test_mentor_cli.py` (4) — **todos verdes**.
* **Artefactos:** `docs/mentor-mode.*.md` em **5 idiomas**, ADR-009, `glossary-gitpr-mentor.md`, spec e relatórios de tarefa.

### **38. Subcomando `gitpr risk` — Pontuação de Risco Determinística (`src/domain/risk/`)**

* **O que é:** Uma pontuação **0–100** com nível `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculada **sem IA** a partir de sinais locais.
* **Camada de Domínio Pura (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums e dataclasses; pesos, limiares, padrões de caminho crítico, glob matching e deteção de testes/não-executáveis carregados de YAML; cálculo por ficheiro com **clamp 0–100** e agregação 50/30/20.
* **A Fórmula É Declarada, Não Aprendida:** **50%** o ficheiro de maior risco, **30%** a média ponderada por linhas e **20%** a evidência crítica.
* **Adaptadores de Infraestrutura:** O `risk_history_reader.py` lê o `git log` e extrai commits, correções de bugs e reverts numa janela de **90 dias**; o `test_matcher.py` corresponde ficheiros de teste ↔ ficheiros de produção por convenção.
* **Caso de Uso (`calculate_risk.py`):** correlaciona secções do diff, apontamentos, emparelhamento de testes e histórico em `FileRisk`/`PullRequestRisk`, **degradando com aviso** quando um sinal não está disponível.
* **Path Tolerante:** a agregação do `check` aceita `path` **ou** `file_path` — o report de risco não degrada em silêncio.
* **Três Consumidores:** o comando `gitpr risk`, a secção `⚡ Risk Assessment` acrescentada aos reviews e ao review remoto de PR, e o **prompt de IA** como contexto **read-only**. O `gitpr check` usa o mesmo `execute_calculate_risk`.
* **Opt-Out:** `GITPR_RISK_INCLUDE_IN_REVIEW` (predefinição `true`).
* **Configuração por Projeto:** `templates/gitpr.risk.yml` (ou `.gitpr/skill/gitpr.risk.yml`) sobrepõe pesos, limiares e caminhos críticos.
* **Testes:** `tests/domain/risk/` (2 ficheiros, 10), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) e `tests/test_risk_cli.py` (4).
* **Artefactos:** `docs/risk-scoring.*.md` em **5 idiomas**, ADR-010, `glossary-gitpr-risk-scoring.md` e relatórios de tarefa.

### **39. Subcomando `gitpr policy` — Policy Packs Versionados (`src/domain/policy/`, `src/infrastructure/policy/`)**

* **O que é:** Um **pack YAML versionado** que transporta, num único artefacto, as decisões de qualidade de um repositório: skills, regras de linter, severidades, caminhos críticos, pesos de risco e convenções de PR/commit.
* **Camada de Domínio (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`):** parsing de manifesto com **schema fechado**, verificação de compatibilidade e de credenciais, **checksum SHA-256** e um resolvedor de grafo com precedência e composição.
* **Camada de Infraestrutura:** O `local_policy_repository.py` resolve o pack a partir de **três origens** (`.gitpr/policies`, `~/.gitpr/policies` e o pacote embutido) e o `policy_pack_loader.py` carrega-o.
* **Casos de Uso:** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` e `validate_policy_pack`.
* **Quatro Packs Embutidos:** `laravel-quality`, `node-quality`, `php-security` e `vue-quality`, cada um com `policy.yml` e `linter.yml`, distribuídos via **`package-data`**.
* **Consumo Integrado em Quatro Pontos:** `calculate_risk`, `config.load_linter_rules`, `core.get_skill_context` e a **chave de cache de PR** (`cache_scope`). O `gitpr check` resolve o pack **antes de tudo** e sai com código 2 se estiver inválido.
* **CLI:** `gitpr policy {list, validate, show, use, off, install, init}`, com confirmação (`--yes`/`--force`), deteção de stack no `init` e a escolha registada em `.gitpr/policy.lock.yml`.
* **Configuração:** `GITPR_POLICY_ENABLED` e `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`.
* **Retrocompatibilidade:** sem pack ativo, o comportamento é **preservado** — coberto por um teste de integração.
* **Dependência:** `packaging>=23.0` (SemVer/PEP 440).
* **Testes:** `tests/domain/policy/` (4 ficheiros, 126), `tests/application/use_cases/` (4 ficheiros de policy, 89), `tests/test_policy_cli.py` (26) e `tests/integration/test_policy_effect_on_review_linter_risk.py` (31).
* **Artefactos:** `docs/policy-packs.*.md` em **5 idiomas**, ADR-011, `glossary-policy-packs.md`, spec/plano/questionário.

### **40. Subcomando `gitpr baseline` — Baseline e Supressões Auditáveis (`src/domain/baseline/`)**

* **O que é:** Os linters e os reviews voltam a reportar os mesmos apontamentos pré-existentes a cada execução. O baseline regista o **instantâneo dos apontamentos que o repositório já conhece** em `.gitpr/baseline.json`, cria a impressão digital de cada um de forma estável entre execuções e deixa o pipeline classificar o que é novo — **só o `new` bloqueia**.
* **Camada de Domínio (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`):**
  * **Fingerprint estável:** identidade da regra, caminho normalizado, linhas e **sha256 da linha com espaços colapsados** — a linha crua **nunca** é guardada.
  * **Manifest canónico:** JSON canónico com checksum, validação que recusa uma chave desconhecida, uma versão de schema/impressão digital não suportada, um invariante de supressão/dívida quebrado, uma impressão digital duplicada e um intervalo de linhas inválido.
  * **Comparador:** classificação, contagens por estado e resolução de entradas — `resolved` e depois `back` lê-se como **novo**, a leitura conservadora.
  * **Política de Supressão:** supressões, dívida aceite, overrides e validadores, com emparelhamento em camadas por **especificidade e precedência**, mais rastreio de dívida vencida.
* **Cinco Casos de Uso:** `baseline_gate`, `baseline_summary`, `compare_against_baseline`, `create_baseline` e `suppress_finding`.
* **Integração com Policy Pack:** o pack pode transportar um bloco `baseline:`, validado pelos **mesmos** validadores.
* **O Repositório É o Único Escritor:** o `local_baseline_repository.py` é o **único** leitor/escritor de `.gitpr/baseline.json` e `.gitpr/baseline.overrides.yml`.
* **Configuração:** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` e `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`.
* **Opt-In e No-Op:** desligado ou sem baseline existente, a funcionalidade **não lê nenhum ficheiro e não imprime nada**.
* **⚠️ A Exceção Deliberada:** um ficheiro de baseline que existe mas **não pode ser aplicado** faz o lint, o review, o full review, a auditoria de ficheiros, o review remoto e o risco **abortarem com código de saída 1**. No `gitpr check`, um baseline corrompido sai com **código 2**.
* **O Contrato Tornou-se Teste:** `tests/integration/test_baseline_exit_code_contract.py` (8 cenários) trava os dois códigos.
* **Alcance:** CLI (6 subcomandos, todos com `--format json`), MCP (`get_baseline_status` + `baseline://summary`), review remoto de PR e o fluxo de lint.
* **A Refatoração que a Funcionalidade Exigiu:** o `NormalizedFinding` passou a tipo canónico em `src/domain/finding/`.
* **Testes:** `tests/domain/baseline/` (5 ficheiros, **126**), `tests/application/use_cases/` (5 ficheiros, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) e `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34) + `tests/integration/test_baseline_exit_code_contract.py` (8).
* **Artefactos:** `docs/baseline-suppressions.*.md` em **5 idiomas**, ADR-012, `glossary-baseline.md`, spec/plano/questionário e relatório de tarefa.

### **41. Subcomando `gitpr check` — Portão de CI Unificado (`src/domain/check/`, `src/infrastructure/ci/`)**

* **O que é:** Um **ponto de entrada único** para pipelines de CI que orquestra análise estática, bridges SAST, varredura de segredos, review por IA opcional, pontuação de risco e classificação por baseline, e emite **um artefacto padronizado** com exit code determinístico.
* **A Ordem do Pipeline É Contrato:** (1) resolução da policy — inválida sai com `2`; (2) extração do diff com smart excludes; (3) linter regex + segredos + bridges externas; (4) pontuação de risco preliminar; (5) **review por IA opcional**; (6) classificação por baseline; (7) avaliação do limiar `--fail-on`; (8) geração do artefacto com **mascaramento de segredos**.
* **Camada de Domínio (`src/domain/check/`):** `check_types.py` (severidade normalizada em três níveis com aliases da especificação); `check_config.py` (precedência **CLI > env > defaults**); `check_output.py` (despacho por formato e escrita atómica).
* **Camada de Infraestrutura (`src/infrastructure/ci/`):** `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (testsuites com `<failure>` e `<system-out>`) e `masking.py`.
* **Use Case (`run_check.py`):** `resolve_diff_base()` (explícito → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), execução do linter e dos bridges, score de risco, review opcional, baseline e envelope final. O stdout emite **só o artefacto**. A agregação de risco aceita `path` **ou** `file_path`.
* **Códigos de Saída:** `0` sucesso; `1` achado contável no limiar; `2` erro de execução.
* **CLI:** `gitpr check` com 8 opções e 9 variáveis `GITPR_CHECK_*` — 🆕 agora também na TUI de configuração (categoria `check`).
* **GitHub Action Reescrita:** o `action.yml` passou a um único passo `gitpr check`. **Quebra declarada** para workflows que dependiam de `--linter` + `--fullreview`.
* **🆕 A Nota de Migração Tornou-se Teste:** `tests/unit/test_check_ci_docs_parity.py` exige as 5 variantes de idioma de `docs/check-ci.*`, as 7 secções `## 1.`–`## 7.` e os termos `action.yml`, `--linter`, `--fullreview`, `gitpr check` e `SARIF` na secção de migração — mais o termo **Legacy CI action contract** no `CONTEXT.md`.
* **Testes:** `test_run_check.py` (8), `test_check_types.py` (8), `test_check_config.py` (3), `test_check_output.py` (3), `test_masking.py` (6), `test_sarif_builder.py` (2), `test_junit_builder.py` (2), `test_check_cli.py` (5), `test_baseline_exit_code_contract.py` (8) e 🆕 `test_check_ci_docs_parity.py` (2).
* **MCP:** a ferramenta `run_check` devolve o envelope JSON do mesmo pipeline, com `no_ia` por defeito a `true`.
* **Artefactos:** `docs/check-ci.*.md` em **5 idiomas**, ADR-013, `glossary-check-ci.md`, spec, `plansfacts` e questionário.

### **42. Extensão de Editor — VS Code e Cursor (`extensions/vscode/`)**

* **O que é:** Uma extensão **cliente fino** que leva os fluxos do GitPR ao editor sem reimplementar nada: inicia o `gitpr-mcp` por stdio e chama as ferramentas existentes. Chaves, tokens, prompts e cache ficam em `~/.gitpr/`, cifrados com Fernet.
* **Arquitetura:** `GitPrMcpClient` sobre `@modelcontextprotocol/sdk` + um `SessionManager` com **uma sessão por pasta** (multi-root) + verificação de contrato na conexão: as 7 ferramentas obrigatórias têm de existir; caso contrário, `MissingMcpToolsError` instrui `pip install --upgrade gitpr-cli`.
* **8 Comandos:** `gitpr.commit`, `gitpr.review`, `gitpr.fullReview`, `gitpr.reviewMentor`, `gitpr.generatePr`, `gitpr.generatePrExplain`, `gitpr.publishPr` e `gitpr.check`.
* **Publicação em Dois Passos:** o `generatePr` abre um documento Markdown editável; o `publishPr` pede confirmação modal e trata `success`, `needs_push`, `already_exists`, `auth_required` e `error`.
* **Diagnósticos no Painel Problems:** o `run_check` é convertido numa `DiagnosticCollection("gitpr")`.
* **Mascaramento em Dupla Profundidade:** o `masking.ts` espelha o `src/infrastructure/ci/masking.py`.
* **Localização da Extensão:** bundles em **en, es, fr e pt-br** (4 idiomas) — a UI do GitPR mantém 5.
* **🆕 Metadados de Marketplace:** o `package.json` ganhou `icon` (`images/icon.png`), `homepage` e `bugs.url`; o pacote transporta `LICENSE` (LGPL-2.1), condição mínima para um listing credível.
* **🆕 Pipeline Endurecido:** `.github/workflows/extension.yml` aceita `workflow_dispatch` com `dry_run`, confirma que a tag `ext-v<versão>` casa com o `package.json` antes de buildar, falha com `::error::` quando falta `VSCE_PAT`/`OVSX_PAT`, e move as credenciais para o `env` do job para que validação e publicação as leiam.
* **O Que a Extensão Não Faz:** não commita, não faz push e não publica sem confirmação. O comando `gitpr.commit` da extensão chama a tool MCP `generate_commit_message` — ele **não** abre a TUI, que é exclusiva do terminal. O fecho automático de issue também é **exclusivo do CLI**.
* **Testes:** suite Mocha própria (7 cenários, incluindo stdio real; o timeout do teste de integração subiu de 20s para 60s) + `tests/test_mcp_server.py` (117 cenários).
* **Artefactos:** `docs/editor-extension.*.md` em **5 idiomas**, `docs/marketplace-publishing-guide.md` 🆕, ADR-014, `glossary-editor-extension.md`, specs, `plansfacts` e relatório do Gemini.

### **43. TUI de Commit Interativa (`src/ui/commit_app.py`)**

* **O que é:** o `gitpr -c`/`--commit` deixou de ser um pipeline silencioso — validava a árvore, pedia a mensagem à IA e commitava de imediato, sem forma de escolher ficheiros, rever o texto ou rejeitar uma má sugestão. Agora é um `CommitApp(App)` de **4 passos** que mantém o programador no controlo até ao `git commit`.
* **Pacote:** um único módulo, `src/ui/commit_app.py` (220 linhas). Os três modais são **importados** de `src/ui/pr_publish_app.py` (`StageFilesScreen`, `CommitMessageScreen`, `CommitProgressScreen`) a par do `_with_suppressed_stdout` — **zero widgets duplicados**.
* **Os 4 Passos:**
  1. **Seletor de Stage:** se existirem ficheiros fora do índice, abre o `StageFilesScreen` com checkboxes, "Select All", "Deselect All", "Stage Selected", "Skip Staging" e "Cancel". Índice já completo → o passo é saltado.
  2. **Geração:** lê o `get_git_staged_diff()`, recorre ao `get_git_diff()` quando nada está em stage, e chama `generate_pr_content("commit", "commit", diff_text, provider)` numa `threading.Thread` daemon enquanto o `CommitProgressScreen` anima.
  3. **Revisão:** o `CommitMessageScreen` mostra a mensagem, que pode ser editada antes de confirmar — a mensagem **editada** é a que vai para o git.
  4. **Execução:** um novo `CommitProgressScreen` corre `execute_git_commit(append_coauthor_trailer(msg))` e regista o SHA no log.
* **Cada Falha Torna-se Estado, Não Exceção:** uma IA em baixo recorre ao `__("Code update")`; um commit que lança torna-se `failed` com a saída capturada. Estados terminais: `no_changes`, `cancelled`, `committed`, `failed`.
* **Auto-Exit no Sucesso:** o `log.mark_finished(False)` remove o botão, e o `_schedule_auto_exit()` regista `set_timer(0.6, self.exit)` — o utilizador volta ao prompt sem clicar em nada. **Em erro o Fechar mantém-se**, porque é aí que o log da falha continua legível.
* **`--hook` Intocado:** o caminho do hook continua sem TUI e ciente de merges (`is_merge_in_progress()` → `return` silencioso). O `prepare-commit-msg` nunca abre a aplicação.
* **✅ A Quebra Foi Fechada — guarda de TTY:** sem `--hook`, o routing consulta agora `_may_ask_the_user()`; com TTY, abre a `CommitApp`; sem TTY (CI, pipe, cron), cai no fluxo de consola silencioso. Foi esta correção que desbloqueou o cenário que congelava a suite completa em ~33%.
* **Configuração:** `GITPR_SKIP_UNSTAGED_CHECK` (salta o Stage Selector) e `GITPR_AUTO_STAGE` (faz stage de tudo e segue), ambos no `DEFAULT_CONFIG` e no schema da TUI de configuração.
* **Detalhes do Textual:** `ENABLE_COMMAND_PALETTE = False`; o `on_button_pressed` trata o `#btn_close`; o `_on_commit_progress_dismissed` lê o `_commit_success` em vez de assumir sucesso quando o modal fecha.
* **✅ Teste Headless Estabilizado:** `pilot.click("#btn_confirm")` passou a `Button.press()` + `pilot.pause()`, o predicado de espera exige `CommitMessageScreen` montada com `#btn_confirm`, e o timeout default subiu de **5 s para 10 s**. Cinco rondas de stress fecharam a 100%.
* **Testes:** `tests/test_commit_app.py` (**17 cenários** 🆕) — cada transição de estado e callback, um fluxo headless completo e o novo `TestCliCommitRouting` (routing com e sem TTY).
* **Domínio:** o `CONTEXT.md` mantém os termos **Stage selector** e **Commit flow**.
* **Artefactos:** `docs/commit-message-ia.*.md` nos **5 idiomas** e o relatório de tarefa do Gemini.

### **44. Fecho Automático de Issues (`src/issue_autoclose/`) — 🆕 Multi-Forge**

* **O que era:** a issue criada com `gitpr -is` ficava órfã — o número existia só dentro da TUI de issue e perdia-se no instante em que esta fechava. Todo o PR tinha de ser ligado à mão, e o merge não fechava nada.
* **Pacote de 5 ficheiros:** `issue_registry.py`, `register_created_issue.py`, `resolve_close_issue.py`, `insert_close_line.py` e `__init__.py`. Cada um é uma responsabilidade: persistir, registar na criação, decidir na execução seguinte e inserir no corpo.
* **🆕 Registo Provider-Neutro (`IssueRegistry`):** o ficheiro passou de `.gitpr/conf/github.yml` para **`.gitpr/conf/issue.yml`**, com um campo `provider` (default `github`). O `github.yml` legado é **lido** de forma transparente quando o novo não existe e é apagado na próxima escrita/limpeza — migração sem intervenção do utilizador.
* **Auto-Ignorando com Precisão:** `record()` corre `git check-ignore` antes da primeira escrita e acrescenta exatamente uma linha ao `.gitignore` quando o ficheiro não está coberto — `.gitpr/conf/` **não** é ignorado por inteiro, porque `gitpr.config.yml` é versionado de propósito ([ADR-015](docs/plans/ADR-015-project-local-pr-base.md)).
* **Registo na Criação:** `register_created_issue()` grava o número **e o provider** de qualquer forge suportada e engole falhas de bookkeeping — um problema de escrita local nunca transforma uma criação bem-sucedida em erro. O desfecho antigo `not-github` passou a `no-provider`.
* **Decisão (`resolve_close_issue`):** barra por flags e configuração, mas **não** mais por forge; cada forge sem suporte degrada para `skipped_reason="not-supported"` em vez de ser ignorada. Verifica que a issue continua **aberta** e **não é um PR** (preferindo `IssueInfo.is_open`), apaga registos mortos (404, fechada, número de PR) e pergunta **uma vez**, avisando quando o PR mira uma branch diferente da padrão.
* **Linha Idempotente (`insert_close_line`):** deteta qualquer keyword existente (`close/closes/closed`, `fix/fixes/fixed`, `resolve/resolves/resolved`, com prefixo `owner/repo#N` opcional, case-insensitive) e nunca duplica a linha.
* **CLI:** `--close-issue` e `--no-close-issue` — mutuamente exclusivas, recusadas antes de qualquer prompt ou escrita; o `-h` vence o conflito.
* **Fiação no Fluxo de PR:** o `_prepare_close_issue` corre **antes** do Seletor de Stage e reutiliza o contexto SCM resolvido; o `_publish_pr_directly` devolve se a forge publicou realmente; o `PrPublishApp.body_published` distingue criar/atualizar de apenas abrir um PR existente. O registo só é limpo depois de uma publicação real (ADR-007).
* **Contrato SCM:** `IssueInfo` (state no vocabulário da forge, `is_open` e `is_pull_request`), `get_issue()`, `get_default_branch()` e `closing_line()` são defaults concretos da ABC; 🆕 GitHub, GitLab e Bitbucket implementam (sintaxe `Closes #N` no GitLab, `close #N` no GitHub/Bitbucket), e o Azure DevOps levanta `ScmNotSupportedError`.
* **Configuração:** `GITPR_ISSUE_AUTOCLOSE` (default `true`, opt-out) e `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` (default `5`, int positivo), expostas na categoria `issue` da TUI.
* **Telemetria:** uma linha `issue_autoclose` por decisão (`asked`, `accepted`, `skipped_reason`).
* **Testes:** 5 ficheiros em `tests/issue_autoclose/` (**126 cenários** 🆕) — round-trip do registo, schema, corrupção, falha de escrita atómica, `.gitignore`, migração do legado, decisão, idempotência da linha, flags, fiação e semântica de `body_published`.
* **Artefactos:** [ADR-010](docs/adr/0010-multiforge-issue-autoclose.md), `docs/specs/issue-autoclose/` (12 ficheiros), `docs/issue-autoclose.*` em **5 idiomas** 🆕, `docs/issue-tui-help.*`, `docs/config-tui.*` e `docs/metricas-telemetria.*`, README nos 5 idiomas e relatórios de tarefa.

### **45. Base do PR por Projeto (`src/project_config.py`, `src/ui/pr_base_app.py`)**

* **O que era:** a branch de destino do PR vivia só no `~/.gitpr/.env` da máquina (`PR_DEFAULT_BASE`) ou era adivinhada por `origin/HEAD`. Um repositório que tem como alvo `develop` dependia de cada programador configurar a mesma variável, e o erro (PR aberto contra a branch errada) só aparecia na forge.
* **O que é:** `.gitpr/conf/gitpr.config.yml` — um ficheiro **versionado com o projeto** que carrega a base do PR, editado pela TUI de configuração e validado antes de qualquer commit/push. Um resolvedor único serve a CLI e o MCP, pelo que todos os pontos de entrada concordam na precedência e nas regras de falha.
* **Resolvedor (`src/project_config.py`):** `resolve_pull_request_base()` — `--base` → ficheiro do projeto → `PR_DEFAULT_BASE` → deteção `origin/HEAD` → `main`. Leitura/escrita YAML preserva chaves alheias; seeding idempotente; listagem de branches; deteção da branch atual; verificação contra o remote.
* **🆕 Segunda Secção (`scm:`):** o ficheiro também transporta `scm.provider` (leitura/escrita), com as duas secções independentes e recusa a sobrescrever um bloco `scm` que não seja mapping. É esse override que vence a deteção pelo remote (secção 46).
* **Falha Antes de o PR Nascer:** `BaseBranchError`/`CurrentBranchError`; a base tem de existir em `origin/<branch>` e diferir da branch atual — sem `origin` ou com a ref ausente, o fluxo para com uma dica acionável em vez de abrir um PR condenado.
* **TUI de Configuração:** categoria `project` com atributo `store` (`env` vs `project`), `KIND_BRANCH` e três campos: path do ficheiro (read-only), branch alvo (editável) e base efetiva com a origem (read-only).
* **Modal de Reparação (`src/ui/pr_base_app.py`, 98 linhas):** quando o alvo é a própria branch atual e a execução é interativa, um modal focado pede a branch substituta e grava-a no ficheiro do projeto.
* **CLI:** `--base` passa por `_resolve_pr_target()`; o ficheiro é semeado em cada invocação dentro de repositório (exceto ajuda), anunciado só no caminho interativo da raiz; `GITPR_SKIP_PROJECT_CONFIG_SEED` desliga o seed. 🆕 `gitpr seed` faz o mesmo de forma explícita e idempotente (secção 47).
* **MCP:** o `publish_pull_request` usa o resolvedor partilhado, devolve envelope de erro para `BaseBranchError` e expõe os `warnings` no payload e no catálogo.
* **Efeito Secundário Declarado:** correr o `gitpr` num repositório passa a criar o ficheiro na primeira execução. Um ficheiro existente nunca é sobrescrito; se o `.gitignore` exclui o caminho, um aviso explica que o valor não será versionado.
* **Testes:** `tests/test_project_config.py` (**49**), `tests/test_project_config_cli.py` (7), `tests/test_pr_base_app.py` (12) + ampliações em `test_config_app.py` (94), `test_config_schema.py` (53), `test_config_store.py` (22), `test_mcp_server.py` (117) e `test_core.py` (49).
* **Artefactos:** [ADR-015](docs/plans/ADR-015-project-local-pr-base.md), `docs/plans/develop_natan/20261030_project_pr_base_plansfacts.md`, `docs/survey/20261030_project_pr_base_surveyfacts.md`, e `docs/config-tui.*`/`docs/install-wizard.*`/`docs/pr-descricao-padrao.*`/`docs/pull-request-publication.*` nos 5 idiomas.

### **46. Configuração SCM por Repositório e por Forge (`src/scm_settings.py`) 🆕**

* **O que era:** a forge (`GITPR_SCM_PROVIDER`), o token (`GITPR_SCM_TOKEN_ENCRYPTED`) e a base URL eram configurações **globais da máquina** em `~/.gitpr/.env`. Uma máquina não servia mais de uma forge ao mesmo tempo, a credencial errada ia para o host errado quando o remote de um repositório mudava, e qualquer host self-hosted não reconhecido era silenciosamente mapeado para GitHub.
* **O que é:** a forge e a credencial passaram a ser **propriedade do repositório** ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)). Um único resolvedor responde "qual forge, qual token, qual base URL valem aqui?" para todas as superfícies.
* **Ordem de Resolução (`resolve_provider_choice`):** override do projeto (`scm.provider` em `.gitpr/conf/gitpr.config.yml`) → `detect_provider_from_remote(origin, default=None)` → `github`. Um valor de forge desconhecido no ficheiro do projeto é **ignorado com aviso**, nunca fatal; `default=None` permite ao wizard distinguir "reconhecido como GitHub" de "não reconhecido" e **perguntar** em vez de assumir.
* **Um Slot por Forge:** `GITHUB_TOKEN_ENCRYPTED`, `GITLAB_TOKEN_ENCRYPTED`, `BITBUCKET_TOKEN_ENCRYPTED`, `AZURE_DEVOPS_TOKEN_ENCRYPTED` (Fernet) e os `*_BASE_URL` correspondentes. Um token nunca responde por outra forge; o `GITPR_SCM_TOKEN` cru continua a ser o override global de CI/CD e é tentado primeiro em todos os provedores.
* **Migração Única e Idempotente (`migrate_legacy_scm_config`, sob lock):** move `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` + `GITPR_SCM_BASE_URL` para o slot da forge que eles nomeiam, **nunca sobrescreve** um slot já preenchido, remove as linhas legadas e continua a funcionar quando o `.env` é só de leitura (degrada para o ambiente do processo). Um token legado **sem** provider registado é deliberadamente **deixado no lugar**, não adivinhado.
* **Wizard (`gitpr --init`):** persiste **só em caso de sucesso**, grava no slot da forge escolhida, oferece a base URL guardada como default do prompt (não-GitHub) e só escreve `scm.provider` no ficheiro do projeto quando a forge escolhida difere da detetada — o ficheiro é commitado e partilhado com a equipa.
* **TUI de Configuração:** linha editável `PROJECT_SCM_PROVIDER` e linha read-only `PROJECT_EFFECTIVE_PROVIDER` (forge em uso + origem), tokens e base URLs de todas as forges sempre listados, teste de token contra o host do próprio slot e um grupo `gitlab`.
* **Superfícies Convertidas:** `src/main.py`, `src/metrics.py`, `src/release_engine.py` e `src/mcp_server.py` resolvem por `get_scm_settings()` e propagam os avisos do resolvedor.
* **Não-Interativo Nunca Pergunta:** `validate_or_request_scm_token(..., allow_prompt=False)` em hooks, MCP e CI devolve "corra `gitpr --init`", não apaga a credencial guardada e ainda usa um token validado que não pôde ser persistido.
* **Testes:** `tests/scm/test_scm_settings.py` (**24 cenários**) + ampliações em `test_init_wizard.py` (20), `test_factory.py` (14), `test_azure_devops_provider.py` (51), `test_bitbucket_provider.py` (49), `test_gitlab_provider.py` (56) e 🆕 `tests/test_tui_issue_token.py` (8).
* **Artefactos:** [ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md), `docs/plans/develop_natan/20261107_per_provider_scm_settings_plansfacts.md`, `docs/scm-multiforge.*` (5 idiomas) e o glossário `docs/plans/glossary-scm-multiforge.md`.

### **47. Subcomando `gitpr seed` — Bootstrap da Configuração Local (`src/core.py`, `src/main.py`) 🆕**

* **O que era:** a configuração local do projeto (base do PR, smart excludes, regras de linter) nascia **como efeito colateral** do callback raiz e do download de skills. Não havia forma de a semear de forma explícita, repetível e idempotente — e um CI que dependia desses ficheiros só descobria na segunda execução.
* **O que é:** `gitpr seed` (e a flag global `-sd/--seed`) promovem o bootstrap a operação de primeira classe. `seed_project_configuration()` cria:
  * `.gitpr/conf/gitpr.config.yml` (base do PR, via `ensure_project_config`);
  * `.gitpr/conf/gitpr.smart-excludes.json` (exclusões do projeto);
  * `.gitpr/skill/.gitpr.linter.yml` (regras do linter local).
* **Nunca Destrói sem Pedido:** um ficheiro existente **nunca** é sobrescrito; `--force/-f` sobrescreve de propósito, e `--all/-a` inclui também o download de todas as skills de IA. A função devolve um resumo estruturado (`config`, `smart_excludes`, `linter`, `skills_downloaded`) e o comando grava a métrica `seed`.
* **Wizard de Instalação em 5 Etapas:** **1)** Configuração Local do Projeto (novo, confirmável), **2)** Skill Templates (com o setup opcional de bridges de linter externo), **3)** Git Hooks, **4)** Configuração do MCP, **5)** Chave de API. Saltar passou a ser explícito.
* **Compatibilidade:** `generate_skill_template()` continua a auto-semear os smart-excludes, pelo que o caminho antigo não quebra; `ensure_project_config()` e `download_skill_file()` ganharam o parâmetro `force` mantendo o default no-clobber.
* **Ajuda:** `HELP_MAP["seed"]` aponta para `docs/install-wizard.md` e descreve `--force`/`--all`.
* **Testes:** `tests/test_seed_command.py` (**8 cenários**) — criação, idempotência, sobrescrita forçada, `--all`, CLI e flag; `tests/test_install_wizard.py` passou a mockar o setup de linter externo.
* **Artefactos:** `docs/install-wizard.*` (5 idiomas), `docs/plans/develop_natan/20261105_seed_local_conf_plansfacts.md`, survey e walkthrough, e o termo **Project configuration seed** no `CONTEXT.md`.

---

## **📊 Testes e Qualidade**

| Ficheiro de Teste | Cenários | Foco |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por intervalo de linhas num ficheiro |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidade, commits, duração |
| `tests/test_baseline_cli.py` | 54 | Grupo `baseline`: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Construtor de changelog: secções, títulos traduzíveis, contribuidores, links e datas |
| `tests/test_chat_backend.py` | 19 | Memória do chat, persistência, comandos slash |
| `tests/test_check_cli.py` | 5 | CLI do `check`: opções, formatos, exit codes |
| `tests/test_cli_help.py` | 9 | `-h` antes do subcomando: ajuda renderizada, TUI nunca aberta |
| `tests/test_commit_app.py` | 17 | `CommitApp`: mount, Stage Selector, auto-stage, skip, cancelar, confirmar, sucesso/falha, auto-exit, fluxo headless **e routing com/sem TTY** 🆕 |
| `tests/test_commit_classifier.py` | 30 | Classificação Conventional Commits + `pr_number_from_merge()` nas 4 forges |
| `tests/test_config_app.py` | 94 | TUI de configuração: montagem, categorias, dirty tracking, F2 bloqueado, Ctrl+R, busca, segredos, stores `project`, painel Smart Excludes 🆕 |
| `tests/test_config_cli.py` | 9 | Registo do subcomando `config`, `-h`, import preguiçoso, stdout limpo |
| `tests/test_config_issue_autoclose.py` | 10 | Getters de configuração e campos do schema de fecho de issue |
| `tests/test_config_schema.py` | 53 | Cobertura de `DEFAULT_CONFIG` derivado, sem duplicados, categorias/kinds, `seeded`, secção de skills, banner e palette 🆕 |
| `tests/test_config_store.py` | 22 | Ida e volta num `.env` temporário, comentários e ordem preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuração dos revisores sugeridos (chaves e predefinições) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, modelos, `validate_ai_key()` com um SDK simulado (401 vs. rede vs. ollama) |
| `tests/test_core.py` | 49 | Fluxos principais, git diff, geração de PR, temporização, staging, coautoria, idioma dos hooks, base do PR |
| `tests/test_diff_failure_cli.py` | 3 | Exit code 1 em falha do git, 0 em diff vazio, fail-open em `--hook` |
| `tests/test_diff_parser.py` | 26 | Parser de diff por linhas/hunks, `summarize_patch()` e `split_patch_sections()` |
| `tests/test_doc_links.py` | 11 🆕 | Contrato `?lang=` do site, `doc_url()` dinâmico, links das TUIs e paridade de 5 idiomas |
| `tests/test_explain_command.py` | 4 | CLI do `explain`: sucesso, chave ausente, diff vazio, ajuda contextual |
| `tests/test_external_linters.py` | 33 | Bridge do Checkstyle: parser de XML, subprocesso, referência cruzada com o diff, relatório |
| `tests/test_full_diff_encoding.py` | 8 | Repositório real com ficheiro Latin-1 + guarda de hunk binário (fast path, colapso, densidade) 🆕 |
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
| `tests/test_metrics.py` | 174 | **Ledger**: escrita síncrona, exportação, bundle/merge, migração, renderizadores, custo, qualidade, módulos, provedores, ciclo, **Token Trend e Latency Histogram** 🆕 |
| `tests/test_net_timeouts.py` | 12 | Timeouts de rede/IA — alinhados com a predefinição real de 180s |
| `tests/test_plugins.py` | 17 | Descoberta de plugins, fusão de regras do linter, prompts do MCP |
| `tests/test_policy_cli.py` | 26 | Grupo `policy`: list/show/validate/use/off/install/init, confirmação e lockfile |
| `tests/test_pr_base_app.py` | 12 | Modal de troca da base do PR: montagem headless, escolha e cancelamento |
| `tests/test_pr_publish_app.py` | 59 | TUI do PR Publisher: ecrãs, fluxos, revisores sugeridos, `NoticeScreen`, modal de merge, `body_published` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de erro do linter: abortar, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save e carga JSON |
| `tests/test_project_config.py` | 49 | `project_config`: path, YAML, seed, resolvedor de base, secção `scm`, validação contra o remote 🆕 |
| `tests/test_project_config_cli.py` | 7 | Fiação da base do projeto na CLI |
| `tests/test_release_cli.py` | 4 | CLI de release: opções, ajuda com epílogo documentado |
| `tests/test_release_engine.py` | 61 | **Corte ancorado no changelog, `_drop_released()`, links, mapa de PR por merge commit**, front matter de idioma 🆕 |
| `tests/test_reviewer_resolution.py` | 18 | Escada de resolução, rejeição de um nome parcial, dedup, tolerância a falhas |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugestão de revisores (ranking, exclusão, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | CLI do `risk`: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 77 | Matriz do conjunto de regras embutido: regex, marcadores, nível, wildcard, fusão, traduções |
| `tests/test_seed_command.py` | 8 🆕 | `gitpr seed`/`--seed`: criação, idempotência, force, `--all`, métricas |
| `tests/test_skill_command.py` | 10 | Descarregamento e validação de modelos de skill |
| `tests/test_skill_context.py` | 20 | `get_skill_context()` com `quiet=True`, fallbacks, guarda do artefacto de PR |
| `tests/test_skill_frontmatter.py` | 11 🆕 | `split_front_matter()`: BOM, CRLF, YAML malformado, allowlist de idiomas |
| `tests/test_skill_templates.py` | 99 | Varredura parametrizada de **todos os templates publicados** contra referências embutidas |
| `tests/test_smart_excludes.py` | 17 | Filtro pathspec inteligente, re-download forçado, **novas famílias binárias** 🆕 |
| `tests/test_suggest_reviewers.py` | 19 | Suggested reviewers no fluxo de PR + **resumo de blame** 🆕 |
| `tests/test_tests_command.py` | 4 | Grupo `tests` e CLI do `generate`, `--finding` e epilog com ligação |
| `tests/test_thinking_words.py` | 5 | Carregamento, análise com o separador `;` e recarregamento forçado |
| `tests/test_tui_issue_token.py` | 8 🆕 | `allow_prompt=False`: nunca pergunta, nunca apaga, aponta `gitpr --init` |
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
| `tests/badge/test_badge_append.py` | 9 | Anexação idempotente do selo ao corpo do PR |
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
| `tests/domain/baseline/test_baseline_fingerprint.py` | 12 | Digest de referência, linha em bruto nunca armazenada, estabilidade |
| `tests/domain/baseline/test_baseline_manifest.py` | 26 | JSON canónico, checksum, chaves desconhecidas, invariantes |
| `tests/domain/baseline/test_baseline_types.py` | 15 | Estados persistidos, âmbitos e precedência |
| `tests/domain/baseline/test_suppression_policy.py` | 52 | Camadas, especificidade, sobreposições, dívida vencida |
| `tests/domain/check/test_check_config.py` | 3 | Precedência CLI > env > defaults |
| `tests/domain/check/test_check_output.py` | 3 | Despacho por formato e escrita atómica |
| `tests/domain/check/test_check_types.py` | 8 | Severidade em três níveis, aliases da especificação, resumo |
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
| `tests/domain/risk/test_risk_rules.py` | 5 | Pesos, limiares, glob de caminhos críticos, YAML de sobreposição |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 | Deteção por configuração, manifesto e diretório; sobreposição; falha avisada |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 | Caminho convencional por framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Caso de uso completo: review → IA → validar → classificar → dry-run/aplicar |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extrator partilhado entre o chat e o `fix` |
| `tests/fix/test_fix_cli.py` | 31 | Encaminhamento do subcomando, opções, dry-run por predefinição, `--force` |
| `tests/fix/test_fix_history.py` | 21 | Registo `.gitpr/fix_history.json`: escrita atómica, leitura |
| `tests/fix/test_fix_settings.py` | 10 | Os cinco `GITPR_FIX_*` e o recurso a predefinições em valores inválidos |
| `tests/fix/test_patch_applier.py` | 19 | Invólucro do `git apply`: check/apply/reverse, ramo, estado |
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
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 | Efeito real do baseline no review, no lint e no risk num repositório real |
| `tests/integration/test_baseline_exit_code_contract.py` | 8 | Exit code 1 para baseline inaplicável e 2 no `check` |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Efeito real do pack no review, no lint e no risk (com uma guarda de rede autouse) |
| `tests/issue_autoclose/test_close_issue_wiring.py` | 26 | Fiação no fluxo de PR: flags, prompt, publicação real, limpeza do registo |
| `tests/issue_autoclose/test_insert_close_line.py` | 19 | Inserção idempotente e reconhecimento de keywords de fecho |
| `tests/issue_autoclose/test_issue_registry.py` | 26 | Registo local: round-trip, atomicidade, corrupção, `.gitignore`, repo match, **migração do `github.yml`** 🆕 |
| `tests/issue_autoclose/test_register_created_issue.py` | 13 | Registo pós-criação: multi-forge, provider persistido, falhas engolidas |
| `tests/issue_autoclose/test_resolve_close_issue.py` | 42 | Árvore de decisão: gates, `is_open`, PR, registos mortos, `not-supported`, confirmação |
| `tests/review/test_diff_normalizer.py` | 20 | Fins de linha, validação do diff, smart-excludes em Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: proveniência, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orquestração: portões, PR, diff, linter, comentário opcional |
| `tests/review/test_review_pr_cli.py` | 25 | CLI do `review-pr`: opções, rejeições antes da IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 51 | Provider Azure DevOps: org/projeto, PRs, `ScmNotSupportedError`, issue read-back |
| `tests/scm/test_bitbucket_provider.py` | 49 | Provider Bitbucket: Basic auth, workspace, PRs, `close #N` |
| `tests/scm/test_contract.py` | 44 | Contrato `ScmProvider`: assinaturas, dataclasses, erros, defaults de issue |
| `tests/scm/test_factory.py` | 14 | `resolve_scm_provider()` + `detect_provider_from_remote(default=None)` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` obsoleto → delega no provedor |
| `tests/scm/test_github_provider.py` | 86 | Provider GitHub: REST, headers, PRs, issues, releases, read-back de revisores |
| `tests/scm/test_gitlab_provider.py` | 56 | Provider GitLab: API v4, namespace, cabeçalhos sintetizados, `overflow`, `Closes #N` |
| `tests/scm/test_init_wizard.py` | 20 | Wizard `--init`: deteção da forge, slot por forge, validação, persistência |
| `tests/scm/test_release_publish.py` | 8 | Publicação de release por forge (GitHub/GitLab) |
| `tests/scm/test_scm_settings.py` | 24 🆕 | Resolvedor de forge/credencial: precedência, migração legada, avisos, read-only |
| `tests/split/test_apply_split_plan.py` | 11 | O único módulo que muta: indexação seletiva, um commit por grupo |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unidades → grupos → pré-validação de conflitos → mensagens |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, orçamento/corte, chamada de IA, validação da resposta |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()`: texto ↔ modelo, puro |
| `tests/split/test_selective_stager.py` | 11 | Indexação de subconjuntos, verificação contra o HEAD, índice limpo |
| `tests/split/test_split_cli.py` | 31 | CLI do split: opções, dry run por predefinição, `--apply` |
| `tests/unit/test_version_consistency.py` | 5 | SSOT da versão: `pyproject.toml`, `CLAUDE.md` e `GEMINI.md` contra `src.updater.__version__` |
| `tests/unit/test_check_ci_docs_parity.py` | 2 🆕 | Paridade de 5 idiomas e 7 secções do `docs/check-ci.*` + termo no `CONTEXT.md` |
| `tests/sync_i18n.py` | — | Script de verificação de cobertura de i18n (scaffold; nunca executado) |

**Total:** **3422 cenários recolhidos em 159 módulos de teste** (63 na raiz + 12 em `tests/application/use_cases/` + 10 em `tests/fix/` + 10 em `tests/scm/` + 10 em `tests/infrastructure/` + 21 em `tests/domain/` + 7 em `tests/badge/` + 6 em `tests/demo/` + 6 em `tests/split/` + 5 em `tests/issue_autoclose/` + 4 em `tests/review/` + 3 em `tests/integration/` + 2 em `tests/unit/`; **+146 cenários, +6 ficheiros** desde o relatório anterior).

**Notas de qualidade desta versão:**
- **✅ O cenário que travava a suite foi desbloqueado:** `-c` sem `--hook` já não abre a `CommitApp` quando não há TTY — o routing consulta `_may_ask_the_user()`. O `test_a_flow_that_never_enforced_it_is_untouched`, que prendia a execução completa em ~33%, termina agora.
- **✅ A única falha intermitente fechou:** `test_headless_full_flow` trocou o clique por `Button.press()` + `pilot.pause()`, endureceu o predicado de montagem e subiu o timeout de 5 s para 10 s; cinco rondas consecutivas fecharam a 100%.
- **🆕 Isolamento de perfil do programador:** as fixtures autouse `english_interface` e `no_hook_script_sync` impedem que o idioma e o auto-sync de hooks da máquina se escapem entre os testes de CLI.
- **Os cenários novos cobrem exatamente o que mudou:** um ficheiro por feature (`test_seed_command.py`, `test_doc_links.py`, `test_skill_frontmatter.py`, `test_tui_issue_token.py`, `scm/test_scm_settings.py`) e ampliações nos módulos existentes (metrics, config, release, SCM, issue_autoclose, smart excludes).
- **`tests/conftest.py` continua hermético:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false`, `LANG_VERSION` na versão do código e `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
- **Guarda de rede em vários sítios:** `tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py` e `tests/split/conftest.py`.
- **Fixtures de git real:** `tests/fix/git_fixture.py`, `tests/split/git_fixture.py` e `tests/test_full_diff_encoding.py`.
- **A suíte da extensão é separada:** 7 cenários Mocha em `extensions/vscode/src/test/mcp_client.test.ts`, dos quais 1 integra realmente com o servidor MCP Python via stdio; não conta para o pytest.
- **O CI corre em Python 3.10 e 3.13** com `fail-fast: false`, e o release tem workflow próprio com Trusted Publishing.

---

## **🌐 Internacionalização e Documentação**

* **Cobertura i18n:** **1772 chaves** em **todos os seis** dicionários (`es.json`, `es_es.json`, `fr.json`, `fr_fr.json`, `pt_br.json`, `pt_pt.json`). O código usa as mesmas chaves: **0 sem tradução** e **0 órfãs**. Eram 1698 × 6 na janela anterior.
* **🆕 O `__lang_version__` subiu para v0.0.41 com conteúdo:** entraram as strings de SCM por forge (slots, wizard, TUI), `seed`, gráficos de métricas (Token Trend, Peak, Daily avg, Latency, Timed runs) e o resumo de blame; os 6 dicionários foram sincronizados.
* **Fontes de tradução em lockstep:** uma chave nova tem de existir no código (fonte), em `langs/pt_br.json` (**lista mestra**), nos dicts FR/ES de `scripts/sync_all_langs.py` e nos valores curados de `scripts/fix_mangled_i18n_keys.py`; o extrator de chaves é **AST** nos dois lados.
* **🆕 Tópicos novos:** dois nesta janela —
  - `docs/issue-autoclose.*` (**5 ficheiros**) — o registo local, a decisão de fecho, as flags `--close-issue`/`--no-close-issue` e a cobertura multi-forge.
  - `docs/marketplace-publishing-guide.md` (**1 ficheiro, EN**) — a publicação da extensão nos marketplaces, ainda sem tradução.
* **🆕 Tópicos atualizados nesta janela:**
  - `docs/scm-multiforge.*` (**5 ficheiros**) — forge e credencial por repositório, slots por forge, migração legada ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)).
  - `docs/issue-tui-help.*` e `docs/issue-autoclose.*` — registo renomeado para `issue.yml` e comportamento nas quatro forges.
  - `docs/smart-excludes.*` (**5 ficheiros**) — a categoria da TUI, o ficheiro do projeto e as novas famílias binárias.
  - `docs/install-wizard.*` (**5 ficheiros**) — o wizard de 5 etapas e o `gitpr seed`.
  - `docs/check-ci.*` (**5 ficheiros**) — nota de migração do `action.yml`, travada por teste de paridade.
  - `docs/editor-extension.*` (**5 ficheiros**) — metadados de marketplace e o pipeline endurecido.
  - `docs/release-notes.*` (**5 ficheiros**) — o front matter `language:` da skill de release.
  - `docs/version-markers.*` (**5 ficheiros**) — ES e FR traduzidos; o tópico deixou de ser parcial.
  - `docs/ARCHITECTURE.*` (**5 ficheiros**) — as camadas novas (`scm_settings`, `skill_frontmatter`).
  - `docs/metricas-telemetria.md` (**1 ficheiro**) — Token Trend e Latency Histogram (traduções pendentes).
* **`CONTEXT.md` (atualizado):** o glossário do domínio passou de **22 para 35 termos**. Novos: **Binary hunk guard**, **Seeded configuration field**, **CI Check**, **Legacy CI action contract**, **Marketplace publication workflow**, **Extension release tag**, **Fail-fast secret check**, **Documentation link**, **Latency histogram**, **Token trend**, **Project configuration seed** e **Blame summary**.
* **Documentação em 5 idiomas:** **52 tópicos canónicos** em `docs/` (**+2**) — **50 com cobertura completa nos 5 idiomas** e **2 tópicos parciais** (`github-issue-prompt-com-gh`, `marketplace-publishing-guide`). O `version-markers` deixou de ser parcial.
* **Novos artefactos de especificação:** [ADR-010](docs/adr/0010-multiforge-issue-autoclose.md) em `docs/adr/` (agora **4 ficheiros**) e [ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md) em `docs/plans/`.
* **Skills locais do Claude Code:** `.claude/skills/` com **29 skills** (contagem inalterada).
* **Índice de Memória:** `.claude/memory/MEMORY.md` com **40 padrões** (contagem inalterada).
* **Relatórios de tarefas:** `docs/claude-code/reports/develop_natan/` (**118**; **+2**) + `docs/claude-code/reports/feat/issue-autoclose/` (**12**) + 🆕 `docs/claude-code/reports/main/` (**1**) e `docs/gemini/reports/` (**42**; **+11**), mais `docs/gemini/walkthrough/` (**19**).
* **Relatórios de estado:** `docs/reports/` — este é o **23.º relatório** (v0.0.23); o v0.0.22 nunca é sobrescrito.
* **Planos de desenvolvimento:** **161** ficheiros em `docs/plans/` (+9) + **24 ficheiros em `docs/gemini/plans/`** (+12) + **34 ficheiros em `docs/survey/`** (+10).

---

## **🔄 Pipeline de Distribuição**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Atualização obrigatória com uma janela deslizante:** a execução verifica o PyPI no arranque e **bloqueia com exit code 1** se existir uma versão mais recente, imprimindo `pip install --upgrade gitpr-cli`. A resposta fica em cache durante **1 hora** e o `--update` **consulta sempre** a rede. A comparação é PEP 440: um pré-release remoto não bloqueia uma instalação estável, e uma versão ilegível conta como desconhecida. O portão cobre os subcomandos quando existe um TTY; CI, pipes, hooks, MCP e `-h` continuam isentos.
3. **GitHub Releases:** produção automatizada pelo `release.yml` em tags `v*` — build, validação, Trusted Publishing e criação do release com os artefactos.
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala via pip) + `tests.yml` (matriz Python 3.10 e 3.13) + `extension.yml` (VSIX; `workflow_dispatch` com `dry_run`, guarda de tag, publicação no Marketplace/Open VSX em `ext-v*`) + `release.yml` (PyPI + GitHub Release em `v*`). O `action.yml` chama um único passo `gitpr check` — **quebra declarada** para quem usava `--linter` + `--fullreview`, agora coberta por teste de paridade de documentação.
5. **Servidor MCP:** entry point `gitpr-mcp` via `pyproject.toml`
6. **Extensão de Editor:** pacote VSIX independente (`natanfiuza.gitpr`), com canais VS Code Marketplace e Open VSX; requer Node.js 20+ apenas para build, não para uso. 🆕 O pacote transporta icon e LICENSE e o workflow falha cedo se tag, versão ou credencial não baterem.
7. **Templates e idiomas OTA:** `templates/` e `langs/*.json` servidos do GitHub (main) — `__lang_version__` **v0.0.41** 🆕 renova as cópias locais de `~/.gitpr/langs/`; `__scripts_version__` **v0.0.4** renova os hooks.
8. **Versão derivada do código:** `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — o `__version__` é a **única** fonte, garantido por [ADR-014](docs/adr/0014-canonical-package-version-ssot.md) e por `tests/unit/test_version_consistency.py`.
9. **`package-data` importa:** os quatro packs embutidos (`src/policy_packs/*`) viajam na wheel.
10. **✅ Estado do release — 1.5.0 publicada; 1.6.0 pendente:** o PyPI serve a **1.5.0**, fechando o débito da janela anterior. A **1.6.0** já está no `CHANGELOG.md` (`## [1.6.0] - 2026-11-07`) e na branch `develop_natan`, mas **sem tag `v1.6.0` e sem publicação** — o `pip install --upgrade gitpr-cli` ainda não entrega a 1.6.0.

---

## **📈 Evolução desde o Relatório Anterior (v0.0.22)**

| Área | v0.0.22 (anterior) | v0.0.23 (atual) |
|------|-------------------|-----------------|
| **Versão GitPR** | 1.5.0 — tagueada (`v1.5.0`), **PyPI ainda em 1.4.4** | **1.6.0** — no changelog e em `develop_natan`, **sem tag**, PyPI em **1.5.0** |
| **Versão Idioma** | v0.0.38 | **v0.0.41** — dicionários sincronizados |
| **Versão dos Scripts de Hook** | v0.0.4 | v0.0.4 (inalterada) |
| **Provedores de IA** | Gemini + DeepSeek + Ollama | Inalterado |
| **Idiomas** | 5 idiomas, 6 dicionários | Inalterado |
| **Subcomandos** | 15 | **16** — 🆕 `seed`; 4 grupos (baseline 6, metrics 8, policy 7, tests 1) |
| **Flags CLI** | 33 opções na raiz | **34** — 🆕 `-sd/--seed` |
| **Configuração SCM 🆕** | Global por máquina (`GITPR_SCM_PROVIDER`, token único) | **Por repositório e por forge** (`src/scm_settings.py`, 1 slot por forge, migração legada) |
| **Fecho de Issue** | GitHub-only | **Multi-forge** — GitLab/Bitbucket implementam, Azure degrada; registo `issue.yml` com migração |
| **Seed de Configuração 🆕** | Efeito colateral do callback raiz | **`gitpr seed`/`--seed`** + wizard de 5 etapas |
| **Camadas** | 9 pacotes de domínio, 15 casos de uso | Inalterado — `scm_settings`/`skill_frontmatter` são borda |
| **Ferramentas MCP** | 18 tools / 19 recursos / 7 prompts | Inalterado — `get_usage_metrics` ganhou `token_trend` e `latency` |
| **`DEFAULT_CONFIG`** | 58 chaves, mantidas à mão | **55 chaves, derivadas do schema** (`seeded`); 3 legadas de SCM removidas |
| **Schema de Config** | 85 `ConfigField` / 17 categorias | **100 / 18** — categorias `smart_excludes` e `check`, 55 `seeded` |
| **i18n (chaves por ficheiro)** | 1698 × 6 | **1772 × 6** — 0 em falta, 0 órfãs ✅ |
| **Portão de Atualização** | TTL corrido de 1 h, `-u` sempre online, PEP 440 | Inalterado no código |
| **Linter** | Regex + segredos + SAST + `findings_out` + baseline-aware | Inalterado |
| **Diff / Smart Excludes** | Filtro pathspec em duas camadas | + **guarda de hunk binário** e **100+ padrões** em 8 famílias |
| **Git Hooks** | post-checkout/post-merge/pre-push no ledger | Inalterado — o `prepare-commit-msg` continua sem interatividade |
| **Camada SCM** | 4 forges, paginação, `web_links.py` | + **forge/credencial por repositório**, issue read-back nas 4 forges, `IssueInfo.is_open` |
| **Release engine** | Secção `[1.5.0]`, tag `v1.5.0`, publicação pendente | Secção `[1.6.0]`; **1.5.0 publicada**; 1.6.0 sem tag; 🆕 front matter `language:` |
| **Métricas** | Ledger SQLite, custo/qualidade/módulos/provedores/ciclo | + **Token Trend** e **Latency Histogram** no CLI/TUI/MCP; `duration_ms` com fallback |
| **Commit TUI** | Sem guarda de TTY; teste intermitente | **Guarda de TTY** (`_may_ask_the_user`) e teste headless estável |
| **Extensão** | Workflow de build/publicação | + `dry_run`, guarda de tag/versão, fail-fast de credencial, icon e LICENSE |
| **Documentação** | 50 tópicos (48 completos + 2 parciais) | **52 tópicos (50 completos + 2 parciais)** — `issue-autoclose` e `marketplace-publishing-guide` novos |
| **Agenda do domínio** | `CONTEXT.md` com 22 termos | **35 termos** — Binary hunk guard, Token trend, Project configuration seed etc. |
| **CI** | `tests.yml` + `action.yml` + `extension.yml` + `release.yml` | Inalterado em ficheiros; workflows endurecidos |
| **Suite de Testes** | 3276 cenários (153 ficheiros) | **3422 cenários (159 ficheiros) — +146 cenários, +6 ficheiros** |
| **Estabilidade da Suite** | Trava em ~33% por causa do `-c` sem TTY; 1 intermitente | **✅ ambas fechadas** — nenhum bloqueio conhecido |
| **Dependências** | Sem novas dependências Python | Nenhuma nova |
| **Commits desde o relatório** | 37 commits + 15 merges | **16 commits, 0 merges** (2026-11-04 → 2026-11-07, em `develop_natan`) |
| **PRs referenciados** | 15 PRs mergeados (#235 … #260) | **14 PRs** (#261, #263, #265, #267, #269, #271, #273, #275, #277, #279, #283, #286, #288, #291) — ainda sem merge em `main` |
| **Índice de Memória** | 40 padrões | 40 padrões (inalterado) |
| **Relatórios de tarefas** | 116 claude-code, 31 gemini | **118 claude-code (+12 feat, +1 main), 42 gemini, 19 walkthrough** |
| **Planos de desenvolvimento** | 152 planos, 12 gemini, 24 surveys | **161 planos, 24 gemini, 34 surveys** |

---

## **🚧 Próximos Passos**

* **🆕 Taguear e publicar a 1.6.0:** a secção `## [1.6.0]` está no `CHANGELOG.md` e a branch `develop_natan` tem os 16 commits, mas **não existe tag `v1.6.0`**. Criar a tag, deixar o `release.yml` publicar no PyPI e confirmar que o `pip install --upgrade gitpr-cli` entrega a 1.6.0. **É a dívida de release reaberta.**
* **🆕 Fazer merge de `develop_natan` em `main`:** a janela inteira está na branch de desenvolvimento, sem nenhum merge. Os 14 PRs referenciados precisam de chegar ao `main` antes (ou junto) da tag de release.
* **🆕 Nota de migração do SCM por repositório:** as chaves `GITPR_SCM_PROVIDER`, `GITPR_SCM_TOKEN_ENCRYPTED` e `GITPR_SCM_BASE_URL` foram retiradas do `.env` pela migração automática. Quem automatizou a escrita dessas chaves (scripts de provisionamento) precisa de saber que agora há um slot por forge — a documentação de `scm-multiforge` cobre a resolução, não a nota de upgrade.
* **🆕 Traduzir o `marketplace-publishing-guide`:** o tópico novo existe só em inglês; PT-BR, PT-PT, ES e FR continuam pendentes (o tópico conta como parcial na secção de documentação).
* **🆕 Publicar a extensão nos marketplaces:** o workflow está pronto e endurecido, e o pacote já tem icon/LICENSE/homepage, mas a publicação depende de `VSCE_PAT`/`OVSX_PAT`. Até lá, a instalação é por VSIX local.
* **Estabilizar o `test_headless_full_flow`:** ✅ **concluído** — clique semântico, timeout de 10 s e predicado de montagem; 5 rondas verdes.
* **Proteger o `-c` de execução não interativa:** ✅ **concluído** — `_may_ask_the_user()` faz o gate da `CommitApp`; a suite completa deixou de travar em 33%.
* **Cobrir o fecho de issue nas outras forges:** ✅ **concluído** — GitLab, Bitbucket e Azure implementam/degradam por contrato (ADR-010).
* **Documentar o novo ficheiro versionado como mudança de fluxo:** ✅ **concluído** — `gitpr seed`, wizard de 5 etapas e `docs/install-wizard.*`.
* **Secção Smart Exclude na TUI:** ✅ **concluído** — categoria `smart_excludes` com lista de fontes e editor.
* **Dívidas registadas no plano do config TUI:** ✅ **concluído** — `DEFAULT_CONFIG` derivado, banner com `--init`/`--base`, palette desativada no `LinterApp` e as 9 `GITPR_CHECK_*` na categoria `check`.
* **Nota de migração para o `action.yml`:** ✅ **concluído** — docs em 5 idiomas e teste de paridade em `tests/unit/test_check_ci_docs_parity.py`.
* **Acompanhar o fallback ES/FR no site:** ✅ **concluído** — `DOC_LANG_PARAMS` e `?lang=` nos links de ajuda.
* **Rever o `git diff` com binários grandes:** ✅ **concluído** — `collapse_binary_hunks()` + smart excludes ampliados.
* **Gráficos em ASCII/Textual no Dashboard:** ✅ **concluído** — Token Trend e Latency Histogram.
* **Seed de `.gitpr/conf/` local:** ✅ **concluído** — `gitpr seed` cobre config, smart excludes e linter.
* **Publicar a 1.5.0 no PyPI:** ✅ **concluído** — o PyPI serve a 1.5.0.
* **Documentar a quebra de semântica do `GITPR_SKIP_UPDATE_CHECK` como nota de migração:** o `docs/tutorial/install-from-source` já descreve o parser estrito, mas automações que definiam `=false` à espera de silenciar a verificação passam a vê-la correr. **Item herdado, ainda aberto.**
* **Nota de migração do `-c` interativo:** o `commit-message-ia` descreve o fluxo novo; vale dizer explicitamente que sem TTY cai na consola e que o `--hook` continua a ser o caminho de CI. **Item herdado, ainda aberto (a guarda de TTY reduziu o impacto).**
* **Provedor Anthropic Claude:** Suporte direto à API do Claude (`claude-sonnet-5`). **Item herdado, ainda aberto.**
* **Mais provedores:** OpenAI direto, provedores locais adicionais. **Item herdado, ainda aberto.**
* **🆕 Traduzir `docs/metricas-telemetria.*`:** a janela deu gráficos novos ao tópico, mas só a versão em inglês foi atualizada — PT-BR, PT-PT, ES e FR ficaram para trás.
* **🆕 Acompanhar o host self-hosted não reconhecido:** a resolução por remote só reconhece domínios que contêm o nome da forge; um GitLab/GitHub em domínio personalizado cai para GitHub até alguém correr `gitpr --init` e gravar o override. Vale documentar o sintoma.

### ✅ Concluídos nesta janela (2026-11-04 → 2026-11-08)

* ~~**A forge e o token eram globais da máquina**~~ 🆕 — PR #288 (`ee012fa`): `src/scm_settings.py`, um slot de credencial por forge, migração legada idempotente, wizard e TUI ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)). **+24 cenários + ampliações.**
* ~~**O fecho de issue era GitHub-only**~~ 🆕 — PR #286 (`fe35c96`): read-back nas quatro forges, registo `issue.yml` com migração, `IssueInfo.is_open` ([ADR-010](docs/adr/0010-multiforge-issue-autoclose.md)).
* ~~**A configuração local nascia como efeito colateral**~~ 🆕 — PR #277 (`d934f21`): `gitpr seed`/`--seed`, `--force`/`--all` e wizard de 5 etapas.
* ~~**A TUI não tinha secção de Smart Excludes**~~ 🆕 — PR #263 (`b3e547d`): categoria `smart_excludes`, lista de fontes e editor do projeto.
* ~~**O dashboard só tinha números agregados**~~ 🆕 — PR #275 (`67a9e77`): Token Trend e Latency Histogram no CLI, TUI e MCP.
* ~~**Binários sem NUL inundavam o prompt com `U+FFFD`**~~ 🆕 — PR #271 (`988dbbc`): `collapse_binary_hunks()` + 100+ padrões de smart excludes.
* ~~**Os links de ajuda apontavam para `blob/main` e quebravam ES/FR**~~ 🆕 — PR #273 (`ffa9b9e`): `doc_url()` com `?lang=` e leitura dinâmica de `CURRENT_LANG`.
* ~~**`gitpr -c` travava em CI sem terminal**~~ 🆕 — PR #283 (`869e2a1`): guarda de TTY via `_may_ask_the_user()`.
* ~~**`test_headless_full_flow` era intermitente**~~ 🆕 — PR #279 (`f57e869`): `Button.press()`, predicado de montagem e timeout de 10 s.
* ~~**`DEFAULT_CONFIG` era uma cópia manual do schema**~~ 🆕 — PR #265 (`74f8c38`): derivado de `ConfigField.seeded`, categoria `check` e dívidas do config TUI fechadas.
* ~~**A skill de release seguia sempre o idioma da interface**~~ 🆕 — PR #291 (`b277632`): front matter `language:` lido por `src/skill_frontmatter.py`.
* ~~**A extensão não tinha metadados de marketplace**~~ 🆕 — PR #269 (`613af18`): icon, LICENSE, homepage, `workflow_dispatch`/`dry_run` e fail-fast de credencial.
* ~~**A sugestão de revisores inundava o terminal com avisos de blame**~~ 🆕 — (`405dc7c`): `files_with_blame`/`files_without_blame` e uma única linha de resumo.
* ~~**A nota de migração do `action.yml` não era verificada**~~ 🆕 — PR #267 (`d1a06f3`): teste de paridade de 5 idiomas e 7 secções.
* ~~**A 1.5.0 estava tagueada mas não publicada**~~ — o PyPI agora serve a **1.5.0**.

---

**Relatório gerado em:** 2026-11-08  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
