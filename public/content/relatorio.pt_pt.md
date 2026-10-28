# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.20 (2026-10-28)**

## **📌 Visão Geral**

O **GitPR** é uma ferramenta de CLI (Command Line Interface) avançada para automação de processos Git utilizando Inteligência Artificial (Google Gemini / DeepSeek / Ollama). O objetivo principal é atuar como um assistente inteligente local que faz Code Reviews, gera Pull Requests, mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo de trabalho do desenvolvedor (Shift Left).

**Novidades desta versão (v0.0.20):**
- **O portão de atualização deixou de perder os releases publicados depois da primeira execução do dia:** a cache do PyPI era chaveada pela **data local**, pelo que um release publicado às 10:45 ficava invisível até à meia-noite para quem já tivesse corrido a CLI às 07:54. A cache passou a ser uma **janela deslizante de 1 hora** com `checked_at` ISO, escrita atómica (`os.replace`) e validação na leitura — um ficheiro corrompido, um timestamp no futuro ou um resto do formato antigo simplesmente expira. O `gitpr -u`/`--update` passou a **ignorar a cache** e a consultar o PyPI em todas as invocações, como a documentação já prometia.
- **A comparação de versões passou a PEP 440:** o `packaging.version.Version` substituiu a aritmética de tuplos — corrige a ordenação de segmentos com vários dígitos (`1.4.10 > 1.4.9`), impede que um pré-release remoto bloqueie uma instalação estável num `pip install --upgrade` que se recusaria a instalá-lo, e trata uma versão ilegível de qualquer dos lados como **desconhecida** em vez de "mais recente".
- **O portão passou a alcançar os subcomandos — mas só quando a execução pode perguntar:** o `gitpr release`, o `fix`, o `review-pr` e os restantes passam pelo portão quando há um TTY nas duas pontas. CI, pipes, hooks, `--quiet`/`--hook`/`--mcp` e `-h`/`--help` **nunca** são bloqueados; a ajuda do subcomando é detetada no **`argv` em bruto**, porque o Click limpa os argumentos do contexto antes do callback do grupo.
- **As 7 falhas de `tests/badge/test_badge_paths.py` finalmente fecharam:** a validação do token do provedor saiu de fora e entrou **dentro** do bloco `if check_remote_state:` do `publish_pull_request_use_case` — o fluxo `--no-edit` deixou de devolver um `auth_required` falso-positivo. O `pr_url`/`pr_number` passou a ser lido com `getattr` defensivo, e a fixture ganhou `token="ghp_fake"` e `number=1`. **A suíte caiu de 12 para 5 falhas conhecidas** — dívida aberta durante três janelas consecutivas.
- **A semântica da válvula de escape mudou (quebra declarada):** o `GITPR_SKIP_UPDATE_CHECK` passou a ser lido como um **booleano estrito** — só `1`, `true`, `yes`, `on` e `y` (sem distinção de maiúsculas) desligam a verificação. `false`, `0`, `no`, `off`, `n` e um valor vazio **mantêm** o portão ligado.
- **A dívida de i18n não foi paga:** os dicionários continuam com **1620 chaves** (×4) e **1618** (`es.json`, `fr.json`), com **8 chaves usadas no código sem tradução** e **2 órfãs** — exatamente os mesmos números da janela anterior.

- **Versão atual:** 1.4.3 (**bump commitado e integrado no PR #228, ainda não publicado** — o PyPI serve a **1.4.2**, não existe tag `v1.4.3` e a secção `[1.4.3]` do `CHANGELOG.md` está **na working tree**, não commitada)
- **Versão dos dicionários de idioma:** v0.0.35 (inalterada)
- **Versão dos scripts de hook:** v0.0.4 (inalterada)
- **Publicação:** PyPI (`pip install gitpr-cli`) — canal binário removido
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositório:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licença:** LGPL-2.1
- **Idiomas suportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 dicionários)

---

## **🏗️ Arquitetura Base e Bibliotecas**

* **Linguagem:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags e formatação de terminal) — **15 subcomandos** (`badge`, `baseline`, `check`, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `split`, `tests`). Quatro deles são **grupos**: `baseline` (6 subcomandos), `metrics` (8), `policy` (7) e `tests` (1). 🆕 **Todos passam pelo portão de atualização quando a execução é interativa** (ver §3).
* **UI/Terminal:** Textual — TUI para chat interativo, edição de issues, ecrã de ajuda, dashboard de métricas, PR Publisher (com o modal de progresso reutilizado no merge), erros do linter (`LinterApp`), configuração (`ConfigApp`), o modal `NoticeScreen`, a visita `gitpr demo` (`src/ui/demo/`) e o assistente de migração do ledger (`src/ui/metrics_migration_app.py`).
* **Arquitetura em Camadas:** `src/domain/` (regra pura, sem I/O) + `src/application/use_cases/` (orquestração, **15 casos de uso**) + apresentação (CLI/TUI). **9 pacotes de domínio**: `baseline`, `check`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`.
* **Extensão de Editor:** `extensions/vscode/` — cliente MCP fino em TypeScript para **VS Code e Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). Não contém regra de negócio.
* **Criptografia:** `cryptography.fernet` para proteção local de chaves de API, tokens GitHub e tokens SCM das forges.
* **Configuração:** `python-dotenv`, `pyyaml` (linter estático) + o seu próprio schema declarativo (`src/config_schema.py`, **80 `ConfigField`** e **16 categorias**). 🆕 O `packaging` também alimenta a comparação de versões do portão (PEP 440), além da compatibilidade dos policy packs.
* **Provedores de IA:** Integração via SDK oficial do Google GenAI, OpenAI SDK (DeepSeek) e OpenAI SDK (Ollama local). Predefinições configuradas: `gemini-pro-latest` / `gemini-flash-lite-latest` e `deepseek-v4-pro` / `deepseek-v4-flash`.
* **APIs das Forges:** `requests` (REST) — abstração multi-forge em `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py`; `src/github_api.py` mantido como shim deprecado.
* **Linter / SAST:** regex YAML + bridge Checkstyle + conjunto de regras de segredos embutido (`src/security_ruleset.py`) + 4 bridges em `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), com o modelo normalizado em `src/domain/linter/sast_finding_mapper.py` e o apontamento canónico em `src/domain/finding/`.
* **Infraestrutura de CI:** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) e `masking.py` (mascaramento de credenciais antes de serializar qualquer artefacto).
* **Git Internals:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py`, `risk_history_reader.py` e `test_matcher.py`.
* **Persistência local:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) com WAL, `PRAGMA user_version`, chaves UUID e transporte por bundle.
* **Rede:** 🆕 o portão de atualização deixou o `urllib` em bruto e passou a usar o `src/net.bounded_urlopen` (`UPDATE_FETCH_HARD_TIMEOUT = 3.0`), que limita o **wall clock incluindo o DNS** — o `timeout` do `urlopen` não cobria a resolução de nomes, que no Windows pode bloquear durante minutos.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 ferramentas anotadas**, **19 recursos**, 7 prompts.
* **Testes:** Pytest + `unittest.mock` (**140 módulos de teste** — 53 na raiz, 12 em `tests/application/use_cases/`, 10 em `tests/fix/`, 9 em `tests/scm/`, 7 em `tests/badge/`, 6 em `tests/demo/`, 6 em `tests/split/`, 5 em `tests/domain/baseline/`, 4 em `tests/domain/policy/`, 4 em `tests/review/`, 4 em `tests/infrastructure/linter/external/`, 3 em `tests/domain/check/`, 3 em `tests/infrastructure/ci/`, 2 em `tests/domain/risk/`, 2 em `tests/domain/tests_generation/`, 2 em `tests/integration/`, 2 em `tests/infrastructure/git/`, 2 em `tests/domain/linter/`, 1 em `tests/domain/finding/`, 1 em `tests/domain/mentor/`, 1 em `tests/domain/pr/`, 1 em `tests/infrastructure/baseline/` —, **2996 cenários recolhidos** 🆕) + testes e2e do servidor MCP sobre um subprocesso real (JSON-RPC stdio) + fixtures de repositório git real (`tests/fix/`, `tests/split/`, `tests/test_full_diff_encoding.py`) + guarda de isolamento de rede (`tests/demo/conftest.py`, `tests/integration/conftest.py`). A extensão tem a sua própria suíte Mocha (7 testes contra o servidor MCP real).
* **Empacotamento:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; packs embutidos via `package-data`; `packaging>=23.0`. A extensão é empacotada separadamente (`vsce package`, VSIX).
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` (chama `gitpr check`) + `tests.yml` (matriz Python 3.10/3.13) + `extension.yml` (build, teste e empacotamento do VSIX; publicação no Marketplace/Open VSX em tags `ext-v*`).

---

## **🧩 Módulos Implementados e Arquitetura de Ficheiros**

### **1. Operações Core e Git (`src/core.py`)**

* **Geração Estruturada:** Comunica com o LLM a pedir um retorno estritamente em JSON.
* **Map-Reduce (Diffs Gigantes):** Quando o diff ultrapassa ~90k tokens, divide-o automaticamente em lotes por ficheiro (`split_diff_into_chunks`), processa cada parte (Map) e unifica os resumos (Reduce). Suporta PRs, commits e Issues. O `provider`/`model` são agregados entre chunks e o `meta`/`modules` é encaminhado para o `log_command_metric`.
* **Tokenizer Local:** `tokenizer.json` para estimativa precisa de tokens antes do envio à IA.
* **Estimativa de Tokens:** Heurística leve `len() // 4` via `estimate_token_count()` com fallback para o tokenizer local.
* **Otimização Git Nativa:** Flags `-U1`, `-w`, `-M`, `-B` nos comandos `get_git_diff` e `get_git_full_diff`.
* **`get_split_diff()`:** diff próprio do `split`, com `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sem** reutilizar o `get_git_diff()`.
* **Pré-Gravação (`--pre-save`):** Flag de debug oculta que guarda o payload completo (instrução de sistema + prompt) em JSON antes de cada chamada à IA.
* **Exclusões Inteligentes com Duas Camadas:** Camada global (`~/.gitpr/conf/`) + camada local do projeto (`./.gitpr/conf/`), unidas em runtime. Auto-seed do ficheiro local na primeira execução. `_load_smart_excludes()` aceita `force=`.
* **Métricas com Rastreio Temporal:** `log_command_metric()` em todos os fluxos, com `meta`, `modules` e `duration_ms`.
* **Resolução Centralizada do Output:** `resolve_output_path()` — predefinição em `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`)**: `gitpr --init` — deteta a forge a partir do remote de origem, pede os extras por forge, valida o token e persiste **só em caso de sucesso**.
* **Template de Skill de Release (`ensure_release_skill_template()`)**: Transfere `templates/gitpr.release.*.md` na primeira utilização do `gitpr release`.
* **Guarda de Skill Corrompida:** `_CORRUPTED_PR_SKILL_ARTIFACT` documenta o fragmento JSON que se colou ao template `pt_br`, e o `get_skill_context()` remove-o **em memória** quando `action_type == "pr"`.
* **Registo Partilhado de Skills:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` em `src/config.py`.
* **Motor de Review com Âmbito de Cache (`generate_pr_content`):** `cache_scope` e `store_diff`; o `cache_scope` transporta também o policy pack ativo.
* **Trailer de Coautoria:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceiros.
* **Captura de Saída do Git Blindada:** as **10** chamadas `subprocess.run` em `src/cache.py`, `src/core.py`, `src/issue_engine.py` e `src/main.py` que capturam saída do git usam `encoding="utf-8"` **com** `errors="replace"`.
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
* **Roteamento de Comandos:** Gere todas as flags e os **15 subcomandos** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline` e `check`.
* **Comportamento Predefinido:** Executar `gitpr` sem flags abre a TUI do PR Publisher.
* **Flags (32 opções Click na raiz — inalteradas nesta janela):** `--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` etc.
* **🆕 Portão de Atualização nos Subcomandos:** o callback do grupo retornava antes do portão raiz, pelo que o `gitpr release`, o `fix`, o `review-pr` e os restantes **nunca** eram bloqueados por um release novo. O portão passa agora a correr antes do `return`, condicionado a `may_ask and not update and not help_flag and not _subcommand_help_in_argv()`. O `_may_ask_the_user()` exige um **TTY nas duas pontas** — CI, pipes, cron, hooks e o servidor MCP continuam livres.
* **🆕 Ajuda do Subcomando Lida do `argv` em Bruto:** o `_subcommand_help_in_argv()` existe porque o Click **limpa os argumentos do contexto** antes de o callback do grupo correr: nesse ponto, `gitpr release -h` é indistinguível de `gitpr release`. A varredura falha aberta, custando no máximo um portão saltado.
* **Subcomando `check` — 8 opções:** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on …`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` e `--policy <name>@<version>`.
* **Grupo `baseline` — 6 subcomandos:** `create`, `show`, `validate`, `update`, `suppress` e `unsuppress`.
* **Grupo `policy` — 7 subcomandos:** `list`, `show`, `validate`, `use`, `off`, `install` e `init`.
* **Grupo `metrics` — 8 subcomandos:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` e o oculto `hook-event`.
* **Subcomando `risk` — 4 opções:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Subcomando `mentor` — 2 opções:** `--finding`, `--provider`.
* **Variáveis de Ambiente (58 chaves em `DEFAULT_CONFIG`):** as quatro `GITPR_BASELINE_*`, as duas do mentor, as duas da policy e `GITPR_RISK_INCLUDE_IN_REVIEW`. **Fora de `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY`, as **9 `GITPR_CHECK_*`** e o `GITPR_SKIP_UPDATE_CHECK` (🆕 agora booleano estrito).
* **Ajuda Contextual:** o `gitpr -h --explain` volta a renderizar; o `HELP_MAP["explain"]` e o `HELP_PRIORITY["explain"]` apontam para `docs/explain-pr`, e o `gitpr tests`/`gitpr tests generate` têm `epilog=` próprio.
* **Camada de Escrita do `.env`:** `read_env_file_values()` lê **só o ficheiro** via `dotenv_values`; `save_config_values()` com `set_key`; `remove_config_value()` com `unset_key`.
* **Portão do Baseline no Fluxo:** o `main.py` resolve o portão do baseline antes do lint, do review, do full review, da auditoria de ficheiros, do review remoto e do risco.
* **Publicação Direta Refatorada:** o `_publish_pr_directly` (`--no-edit`) delega no **`publish_pull_request_use_case`** — o mesmo caminho da ferramenta MCP `publish_pull_request`.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` e `src/ui/pr_publish_help.py`)**

* **Interface Interativa Completa:** TUI para rever, editar e publicar Pull Requests diretamente no terminal.
* **7 Ecrãs Modais:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` e `NoticeScreen`.
* **Selo no Corpo do PR:** o `attach_pr_badge()` corre em `src/main.py` logo depois de o `pr_data` estar completo — **ponto único de injeção**.
* **`_attach_reviewers` com resolução:** Resolve o que o utilizador escreveu **antes** de enviar, reporta o que foi descartado e, num `422` de lote, **repete um a um**.
* **Revisores Sugeridos:** Consulta a forge por revisores sugeridos; o `_reviewer_suggestion_view()` constrói as `resolutions` via `resolve_candidates`.
* **Bindings:** F1 (Ajuda), F2 (Guardar .md local), F3 (Publicar via forge), Esc (Sair).
* **Fluxo de Auto-Commit:** Linter → mensagem por IA → confirmação → commit → push → publicação do PR.
* **Modal de Progresso no Merge:** `CommitProgressScreen` com `title` opcional e `mark_finished(show_close=False)`; o sucesso mostra `✅ PR #N merged successfully!`, os conflitos e erros inesperados passam pelo `_on_merge_failure`.
* **Tratamento de PR Existente / Auto-Upstream / Merge Flow** — inalterados (`GITPR_AUTO_MERGE`).

### **5. Módulo de API do GitHub (`src/github_api.py`)**

* **Shim Obsoleto:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` e as restantes funções delegam em `src/infrastructure/scm/github_provider.py`; emite um `DeprecationWarning` e mantém os tuplos legacy `(ok, data, status)` — nenhum código novo o pode importar.

### **6. Motor de Análise Estática / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analisa estaticamente as linhas adicionadas (`+`) no diff sem gastar quotas de IA.
* **Regras YAML:** Lê o `.gitpr.linter.yml` (criado via `--skill`).
* **Plugins de Linter:** Regras adicionais carregadas de `~/.gitpr/plugins/linter/*.yml`.
* **Wildcard de Extensão:** `extensions: ["*"]` significa **todos** os ficheiros, incluindo os que não têm sufixo e os dotfiles.
* **Conjunto de Regras de Segredos Embutido:** O `load_linter_rules()` junta o `src/security_ruleset.py` **depois** das regras do projeto e dos plugins. Sete regras, todas `extensions: ["*"]`: cinco `error` e dois `warning`. O alerta **nunca ecoa o valor**.
* **Camada de Policy Pack:** o `load_linter_rules()` aplica a fusão em camadas do pack ativo e **sobrepõe severidades**.
* **Apontamentos Estruturados (`findings_out`):** além das strings de alerta, o motor devolve os apontamentos normalizados do tipo canónico `src/domain/finding/`.
* **Bridges SAST Opt-In:** O Semgrep, o Gitleaks e o Bandit correm no caminho de ficheiro inteiro **e** no caminho de diff, com dedup `[Gitleaks + Regex]`, mascaramento do valor (`AKIA****`) e um aviso quando a ferramenta está ativada mas fora do `PATH`.
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=True)` no fluxo remoto.
* **Relatório Consolidado:** O `generate_linter_report_content()` consolida erros regex + externos em `.gitpr/reports/linter/` — gerado apenas quando há violações.
* **Consciente do Baseline:** os apontamentos são anotados com o estado do baseline antes de se tornarem relatório.

### **7. Segurança e Autenticação (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografia:** Gera a chave mestra `secret.key` em `~/.gitpr/`.
* **Proteção de Tokens:** `encrypt_data`/`decrypt_data` para chaves de IA, GitHub PAT e tokens SCM das forges.
* **Validação Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — 401 → ciclo de reautenticação preservando o rascunho.
* **Segredos na TUI de Configuração:** Os campos `KIND_SECRET` são editados mascarados, **nunca** apresentam o valor em claro e são encriptados com Fernet antes de serem escritos.
* **Deteção de Segredos no Fluxo:** O linter — que corre no hook de pre-commit — é uma barreira de credenciais com regras idênticas em todas as máquinas.
* **Mascaramento no CI:** o `src/infrastructure/ci/masking.py` remove chaves privadas, AWS access key ID/secret, tokens GitHub, Google, OpenAI e Slack, `Bearer`, basic-auth em URLs e atribuições genéricas de password **antes** de o artefacto ser serializado. A extensão tem um espelho em TypeScript (`masking.ts`).
* ⚠️ **Lacunas de cobertura confirmadas em aberto:** a regra genérica continua a exigir aspas depois do separador, pelo que `API_KEY=abc123` ainda escapa; os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-` continuam fora do ruleset **do linter**; e não existe fronteira à esquerda no nome da chave.

### **8. Auto-Atualizador (`src/updater.py`)**

* **PyPI como Fonte Única:** o `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` e escreve a cache. Aceita `force=True`, usado pelo `--update` para **ir sempre** à rede.
* **🆕 Janela Deslizante de 1 Hora (`UPDATE_CACHE_TTL_SECONDS = 3600`):** a cache deixou de ser "válida enquanto a data local for hoje". O documento passou a ser `{"checked_at": <ISO>, "version": ...}`; o `_cache_is_fresh()` aceita uma idade em `[0, 3600)` segundos. Um release publicado às 10:45 passa a ser visto no máximo uma hora depois — antes, ficava invisível até à meia-noite.
* **🆕 Validação na Leitura:** um `dict` genuíno, um timestamp interpretável e uma versão em string não vazia. Um timestamp **no futuro** (um relógio que recuou) conta como expirado, e o formato antigo com `date` é tratado como expirado e reescrito — sem migração manual.
* **🆕 Escrita Atómica:** `tempfile.NamedTemporaryFile` no mesmo diretório + `os.replace`, com limpeza do ficheiro temporário em caso de falha. Um `home` só de leitura deixou de esconder a versão obtida.
* **🆕 Obtenção Blindada (`bounded_urlopen`):** um corpo não-JSON e um payload sem versão são **rejeitados antes de entrarem na cache** — um portal cativo ou um mirror malformado não são persistidos. Um teto de 3 s de wall clock, com o DNS incluído.
* **🆕 Comparação PEP 440:** o `_coerce_version()` usa o `packaging.version.Version`, tolerando o `v` inicial e os sufixos PEP 440; o `parse_version()` mantém o contrato de tuplo para os chamadores externos. O `_is_newer()` **ignora um pré-release remoto** numa instalação estável e trata uma versão ilegível de qualquer dos lados como desconhecida.
* **Bloqueio Obrigatório (`enforce_update_required()`):** Devolve `True` (depois de imprimir as duas versões e o comando pip) quando a versão publicada é mais recente; `False` quando está atualizado, quando a versão remota é **desconhecida (offline)** ou quando a verificação está desligada.
* **🆕 Semântica Estrita da Válvula de Escape:** o `GITPR_SKIP_UPDATE_CHECK` é interpretado contra `_SKIP_TRUTHY_VALUES` (`1`, `true`, `yes`, `on`, `y`); antes, qualquer valor não vazio — incluindo `false` e `0` — desligava a verificação.
* **`check_and_update()`:** Apenas consulta e **reporta**, nunca instala.
* **Versionamento Centralizado:** `__version__` (**1.4.3** 🆕), `__lang_version__` (**v0.0.35**), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* **⚠️ Estado do release:** o `__version__` já é **1.4.3** e o commit foi integrado (PR #228), mas **não existe tag `v1.4.3`**, o PyPI serve a **1.4.2** e a secção `[1.4.3]` do `CHANGELOG.md` está **na working tree**. As instalações em 1.4.1/1.4.2 só recebem a correção do portão através de um `pip install --upgrade gitpr-cli` manual — a lógica antiga delas continua a responder a partir da cache diária.

### **9. Interface de Chat Interativa (`src/ui/chat_app.py`)**

* **TUI Completa:** Histórico de mensagens, input multi-linha, barra de estado com bindings visíveis. `ENABLE_COMMAND_PALETTE = False`.
* **Memória por Branch (`src/chat_memory.py`):** Histórico persistido por branch, com continuidade entre sessões.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`. O `/tests` (e os aliases localizados) delega no **mesmo caso de uso** que a CLI usa.
* **Auto-Patching (F5), Atualização de Diff (F2), Exportação de Sessão (F6).**
* **Extrator Partilhado:** O F5 e o `ctrl+s` chamam `patch_extractor.extract_code_blocks()` — comportamento visível idêntico, sem lógica duplicada.

### **10. Internacionalização — i18n (`src/i18n.py`)**

* **Sistema Inspirado no Laravel:** Função `__()` com placeholders nomeados (`{count}`, `{file}`, etc.).
* **Deteção Automática:** Deteta o idioma do SO na primeira execução e guarda-o em `GITPR_LANG`; o diretório do perfil é criado **antes** de persistir.
* **5 Idiomas, 6 Dicionários:** en_us (predefinição/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Ficheiros Versionados:** O `__lang_version__` (**v0.0.35**) controla a atualização dos pacotes (`langs/*.json`).
* **Cobertura — dívida inalterada nesta janela:** **1620 chaves** em `pt_br`, `pt_pt`, `es_es` e `fr_fr`, mas **1618** em `es.json` e `fr.json`. O código usa **1626** chaves: **8 não existem em nenhum dicionário** (mensagens de `publish_pull_request` e descrições das ferramentas MCP `run_check`/`publish_pull_request`) e **2 chaves antigas ficaram órfãs**. ❌
* **Snapshot do Ambiente (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado **imediatamente antes** do `load_dotenv()` ao nível do módulo.
* **Cache com Indexação por Idioma:** As respostas de IA em cache incluem o idioma corrente no chaveamento MD5.
* **Fontes de Tradução em Passo Sincronizado:** uma chave nova tem de existir no código, em `langs/pt_br.json` (**lista mestra**), nos dicionários FR/ES do `scripts/sync_all_langs.py` e nos valores curados do `scripts/fix_mangled_i18n_keys.py`.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Thread em background durante chamadas de IA com caracteres braille e palavras de "pensamento".
* **263 entradas por idioma:** Sincronizadas entre os 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceitam `force=`.

### **12. Provedores de IA (`src/ai_providers.py`)**

* **3 Provedores Suportados:** Google Gemini, DeepSeek e Ollama (local), com um primário/secundário por provedor.
* **Modo JSON & Parâmetros Determinísticos:** `temperature=0.0` e `top_p=0.1`; fallback automático entre os provedores configurados.
* **Timeout de IA:** predefinição de **180s** (`GITPR_AI_TIMEOUT`).
* **Chamadas com `quiet`:** a release e as funcionalidades novas encaminham o `quiet` até à camada de IA, para que o spinner nunca corrompa uma saída `--format json`.
* **Uma Chamada de Cada Vez, com Cache:** o mentor e o explain reutilizam a cache MD5 por prompt.
* **Payload Já Interpretado:** o `parse_explain_payload()` e o `generate_test_file` aceitam o **dict** que o provedor entrega, com fallback para a string JSON em bruto (respostas antigas da cache).

### **13. Cache Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Chaveamento por hash MD5 do diff e do prompt, com indexação por idioma.
* **Seleção do Último Review (`resolve_last_review()`):** Escolhe o registo `review`/`fullreview` **mais recente** para um par repo+branch — é a porta de entrada do `gitpr fix` e do `gitpr mentor`.
* **Diff Revisto (`reviewed_diff`):** Campo no topo do registo que guarda o diff efetivamente revisto.
* **Telemetria e Duração:** Persistência de `duration_ms` e `meta_raw`.
* **Leitura para o Dashboard:** o `scan_cache_files_for_dashboard()` é uma **ponte pré-migração**.
* **Efeito da Sanitização de Skill:** como o `get_skill_context()` remove o fragmento corrompido do prompt de PR, a chave MD5 muda e as respostas antigas contaminadas deixam de ser reutilizadas.
* **`git config` Descodificado com Segurança:** as leituras de `user.name`/`user.email` usam `encoding="utf-8"` + `errors="replace"`.

### **14. Motor de Issues e TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff atual, Histórico da branch (`-ht`) e Arqueologia por Blame (`-b`).
* **Publicação Multi-Forge:** O F3 cria a issue na forge **configurada** (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — o Azure DevOps lança `ScmNotSupportedError`.
* **Map-Reduce para Issues:** Contexto acima de ~90k tokens é dividido e unificado.
* **Tratamento de 401:** Sinalização de reautenticação sem fechar a aplicação.
* **`git remote -v` Descodificado com Segurança:** a chamada usa `encoding="utf-8"` + `errors="replace"`.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Acompanha a evolução e a autoria histórica com classificação de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registados via `log_blame_metric()` com profundidade e número de commits analisados.
* **Passou também a alimentar o risco:** o leitor de histórico da pontuação (`risk_history_reader.py`) usa a mesma janela de 90 dias e classifica bugs/reverts por padrão, sem chamar a IA.

### **16. Servidor MCP e Invocação Direta via CLI (`src/mcp_server.py`)**

* **18 Ferramentas MCP Anotadas:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates`, `review_remote_pr`, `get_baseline_status`, `get_usage_metrics`, `run_check` e `publish_pull_request`.
* **Parâmetros Opt-In no Contrato:** `review_code` e `full_review` aceitam `mentor: bool = False`; `generate_pr_description` aceita `explain: bool = False`. São **puramente por parâmetro** — nunca fazem OR com os defeitos do `.env`.
* **19 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Registo de Skills Alinhado:** O `mcp_server.SKILL_FILES` tem **11 entradas** (`tests`, `explain` e `mentor` incluídos); o `TestSkillRegistryAgreement` passa.
* **Invocação CLI Direta:** O comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca qualquer tool sem iniciar o servidor stdio; o `gitpr-mcp --list` imprime o registo completo em JSON.
* **Isolamento do Stdout Real:** O `_write_real_stdout()` escreve no `sys.__stdout__` original, com fallback para `buffer` ou nova codificação com `errors='replace'` em code pages legacy do Windows.
* **Offload do Event Loop:** o decorator `_offload` (`anyio.to_thread.run_sync`) em todas as tools — a ordem dos decorators importa (`@mcp.tool` **acima** de `@_offload`).
* **Falhas como Erros Estruturados:** as ferramentas novas devolvem a falha **como dado** em vez de levantar.
* **Métricas MCP:** `run_check` e `publish_pull_request` escrevem linhas locais (`mcp:check`, `mcp:publish_pr`).
* **O servidor MCP nunca é bloqueado pelo portão:** o `--mcp`/`gitpr-mcp` está na lista de isenções desde a primeira versão do portão.

### **17. TUI de Dashboard de Métricas (`src/ui/metrics_app.py`)**

* **Âmbito por Repositório:** Rótulo `📁 Repository: owner/repo` e filtragem estrita por projeto.
* **Varredura Assíncrona com Overlay:** worker thread em background com uma `ProgressBar`; os testes aguardam `workers.wait_for_complete()` antes de afirmar.
* **Leitura do Ledger:** o dashboard consulta o `telemetry.db` na worker thread, renderizando as cinco secções (custo, qualidade, módulos, provedores, ciclo) a partir dos mesmos renderizadores que a CLI usa.
* **Assistente de Migração (`src/ui/metrics_migration_app.py`):** oferece importar os ficheiros legados, importar **e** reconstruir a partir da cache de IA, ou "agora não".
* **Exportação Local:** CSV/JSON em `./.gitpr/metrics/export/` — `.gitpr/metrics/` está no `.gitignore`, mas os **34 artefactos já rastreados continuam rastreados** (o gitignore não desrastreia).

### **18. Sistema de Métricas e Telemetria (`src/metrics.py`)**

* **Âmbito por Repositório:** Todos os eventos indexados por `repo_name`, resolvido por `src/infrastructure/git/identity.py`.
* **Eventos de Hook, Linter e Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. O `gitpr check` escreve `command="check"` com `findings_total`/`findings_errors` e o exit code.
* **Exportação e Limpeza:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` com confirmação interativa, `bundle`/`merge` para transportar e fundir janelas.

### **19. Sincronização de Idiomas dos Git Hooks**

* **Versionamento Independente:** `__scripts_version__` (**v0.0.4**, inalterado).
* **Templates reescritos na janela v0.0.17:** `post-checkout`, `post-merge` e `pre-push` chamam `gitpr --quiet metrics hook-event <name>`, com guarda por `command -v gitpr` e `|| true`.
* **Mapeamento de Sufixos (`HOOK_SCRIPT_SUFFIXES`):** Os códigos de interface (`es_es`, `fr_fr`) traduzidos para os sufixos publicados (`.es`, `.fr`).
* **Escolha vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detetar uma mudança de idioma**.
* **`effective_hook_lang()`:** Resolve o idioma efetivo; o `--lang` deixou de ser descartado nesse caminho.
* **Skip de Merge-Source:** O `prepare-commit-msg` salta as fontes `message|merge|squash|commit`.
* **Os hooks correm com `--quiet` e `--hook`, pelo que nunca são bloqueados pelo portão de atualização.**

### **20. Bridge de Linters Externos e Assistente Interativo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistente `--linter-setup`:** Assistente com presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e injeção do bloco `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` com uma cadeia local → descarregamento → stale → fallback embutido.
* **TUI de Erros do Linter:** O `src/ui/linter_app.py` (Textual) apresenta erros críticos e avisos; em modo hook/quiet imprime e faz `sys.exit(1)`.
* **Relatório Markdown:** Consolidado em `.gitpr/reports/linter/` apenas quando há violações.
* **A Camada SAST É Irmã, Não Substituta:** as bridges Semgrep/Gitleaks/Bandit alimentam o **mesmo** pipeline de relatório — e agora também o `gitpr check`.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstração Única (`ScmProvider` ABC):** O `base.py` define o contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. e `ScmProviderError(provider, http_status, message)` — `http_status` 0 = falha de rede).
* **Registo e Factory:** O `resolve_scm_provider()` seleciona por `GITPR_SCM_PROVIDER` (predefinição `github`); o `detect_provider_from_remote()` identifica a forge a partir do URL de origem.
* **`list_pull_requests(repo, state, since, until)` — paginado e canónico:** os quatro provedores traduzem os estados canónicos (`open`/`merged`/`closed`/`all`) para o vocabulário nativo, paginam no cursor de cada API e filtram por uma janela de datas inclusiva.
* **Porque o filtro é por data de estado:** três das quatro forges devolvem algo mais amplo para um PR integrado, pelo que cada uma filtra a sua própria resposta contra a data que o estado implica.
* **`supports_merged_dates`:** atributo de classe, `False` no Bitbucket Cloud, que publica `merged_at` **vazio**.
* **`web_links.py`:** funções puras (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) que normalizam qualquer formato de remote.
* **`get_pull_request(repo, pr_id)` — método concreto da ABC:** a predefinição lança `ScmNotSupportedError` e cada forge implementa-o.
* **Cabeçalhos do GitLab Sintetizados:** O `changes[].diff` é um **hunk solto**; o `old_path`/`new_path` montam os cabeçalhos `diff --git a/… / --- / +++`, honrando o `/dev/null`. E o `overflow: true` **lança** em vez de rever metade de um diff truncado.
* **`request_pull_request_reviewers` devolve `list[str]` (quebra de contrato):** Devolve os logins **efetivamente associados**, lidos do corpo do `201`. As subclasses de terceiros têm de ser atualizadas — **item herdado, ainda sem documentação.**
* **Helpers de Apenas Leitura do GitHub:** `get_commit_author_login` e `get_user_login`.
* **Fail-Fast por Forge:** O Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; o Bitbucket exige `GITPR_SCM_USERNAME`; o `create_issue` no Azure lança `ScmNotSupportedError`.
* **Publicação de Release:** O `provider.create_release()` usado pelo `gitpr release --publish`.
* **Publicação Partilhada de PR:** `src/application/use_cases/publish_pull_request.py`, usado pelo `--no-edit` e pela ferramenta MCP. Valida título/corpo, **exige `provider.token` apenas quando `check_remote_state=True`** 🆕, verifica opcionalmente se já existe PR e se o ramo não foi empurrado, mascara as mensagens de erro e devolve `PublishPrOutcome`. Os campos `url`/`number` do resultado são lidos com `getattr` defensivo 🆕.
* **Artefactos:** Glossário + ADR-001/ADR-005; a família `docs/scm-multiforge.*.md` em 5 idiomas; 9 ficheiros de teste, **307 cenários**.

### **22. Subcomando `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Fluxo:** `git log` entre `--since` e `HEAD` → classificação Conventional Commits → bump semântico sugerido → montagem do changelog → resumo executivo por IA opcional → *prepend* no `CHANGELOG.md`.
* **O Intervalo Deixou de Vir do `git describe`:** a origem do intervalo é a **secção anterior do próprio changelog**, através de duas âncoras: o hash mais recente dessa secção e, como fallback, a tag de versão da secção.
* **`_drop_released()` — a segunda linha de defesa:** qualquer commit cujo hash curto apareça noutra secção de versão é descartado, com contagem.
* **Cada bullet tornou-se uma ligação:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **A falha do PR por merge commit:** uma segunda passagem (`git log --merges`) constrói `hash → PR` por ancestralidade.
* **Contribuidores resolvidos pela mesma escada dos revisores:** o `get_commit_author_login` primeiro, depois o `email_to_handle`, com cache em `~/.gitpr/cache/contributors.json`.
* **A Fronteira da Secção Era um Bug Latente:** o `upsert_changelog(force=True)` substitui da linha `## [x.y.z]` até ao próximo cabeçalho de **nível 2**.
* **Classificador (`src/commit_classifier.py`)** e **Construtor com Secções Traduzíveis (`src/changelog_builder.py`)**.
* **Bump Semântico (`src/version_bump.py`)** e **Publicação** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opções no subcomando.**
* **🆕 Estado do release:** a secção `## [1.4.3] - 2026-10-27` foi gerada e está **na working tree**, ainda não commitada. As tags `v1.4.1` e `v1.4.2` existem; **não existe `v1.4.3`** e o PyPI serve a **1.4.2**.
* **🆕 O subcomando passa agora pelo portão de atualização** quando é executado num terminal interativo.
* **Artefactos:** `docs/release-notes.*.md` em 5 idiomas, ADR e `tests/test_web_links.py`.

### **23. Subcomando `gitpr config` — TUI de Configuração**

* **Ecrã Master-Detail (`src/ui/config_app.py`):** Categorias à esquerda, campos à direita, editados inline. Cabeçalho com procura (`/`) e um contador de alterações pendentes; rodapé `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schema Declarativo (`src/config_schema.py`) — a única fonte de verdade:** **16 categorias** e **80 `ConfigField`**, dos quais **8 são avançados**. As 9 `GITPR_CHECK_*` continuam **fora** do schema.
* **Categorias `policy` e `baseline`:** os campos do pack e os quatro do baseline.
* **Secção Skills Alinhada:** O `SKILL_LABELS` tem **11 entradas** (`tests`, `explain`, `mentor`).
* **Filtragem por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` conforme o `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` sob `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` sob `[Azure DevOps]`.
* **Procura Global (`/`):** Encontra correspondências na chave ou no rótulo em todas as categorias e **ignora o filtro de visibilidade**.
* **Validação em Duas Camadas:** **offline** bloqueia o `F2` com um erro inline; **online** (apenas credenciais alteradas na sessão) corre num worker com timeout de 10s e só bloqueia em `401`/`403`.
* **Restaurar (`Ctrl+R`):** Remove a linha do `.env` em vez de reescrever a predefinição; o `Esc` com alterações pendentes pede confirmação.
* **Secção Skills — a única com âmbito de projeto:** Edita os `.gitpr/skill/*.md` do projeto resolvidos a partir do diretório de chamada, escrevendo atomicamente e preservando CRLF/LF.
* **Descarregamentos Forçados:** Botões que forçam o novo descarregamento dos smart-excludes, das traduções, dos presets e das thinking words via `force=`.
* **Módulo de Ligações Leve (`src/doc_links.py`):** O `doc_url()` saiu do `core.py` para que a UI possa obter a ligação sem importar os SDKs de IA.
* **Testes:** 5 ficheiros — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Dívida conhecida:** O `gitpr -h config` abre a TUI e ignora o `-h` — o portão `if ctx.invoked_subcommand is not None: return` (main.py) corre antes do bloco do `help_flag`. 🆕 O portão de atualização vive agora também nesse ponto, mas com `not help_flag` e a varredura do `argv` em bruto como guarda.

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
* **O `render.py` ganhou o parâmetro do risco:** o `compose_review_content` / o `render_review_result` **antepõem** a secção `⚡ Risk Assessment` acima dos alertas do linter quando o `GITPR_RISK_INCLUDE_IN_REVIEW` está ligado.
* **Artefactos:** `docs/review-pr.md` + `.pt_br.md` (os 3 idiomas restantes **continuam pendentes**), `docs/code-review-ia.*.md` (5), ADR-005 e `glossary-review-pr.md`.
* **Testes:** 4 ficheiros em `tests/review/` (**97 cenários**).

### **27. Resolução de Identidade do Revisor (`src/reviewer_resolution.py`)**

* **O Bug (duas falhas silenciosas encadeadas):** (1) **Prefill vazio** — os `handles` só com o `email_to_handle()`; (2) **Associação não verificada** — valores repassados *verbatim*, o GitHub respondia **201 sem associar ninguém** e o `_request()` retornava sem lançar.
* **Módulo:** Puro, sem I/O próprio, **que nunca lança**. O `resolve_candidates()` (antes da TUI) e o `resolve_typed_reviewers()` (no momento da associação); o `match_candidate()` faz correspondência exata e normalizada por login, nome ou e-mail.
* **Escada de Resolução:** handle já conhecido (sem qualquer pedido) → correspondência exata com uma pessoa sugerida → lookup por e-mail → validação do login na forge.
* **O que não resolve nunca é enviado:** Sai em `ResolutionOutcome.dropped` como `(value, reason_i18n)` e é descartado com um aviso visível.
* **A Mesma Escada Serve a Release:** o `release_engine` reutiliza `get_commit_author_login` + `email_to_handle`.
* **Testes:** `tests/test_reviewer_resolution.py` (18) + ampliações em `test_github_provider.py` (72), `test_pr_publish_app.py` (55), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) e `test_main_suggest_reviewers.py` (8).

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
* **✅ As 7 falhas de `tests/badge/test_badge_paths.py` foram fechadas nesta janela:** o caso de uso partilhado passou a exigir `token` **apenas** quando `check_remote_state=True` — o contrato documentado — e a ler `url`/`number` com `getattr`. A fixture ganhou `token="ghp_fake"` e `number=1`, e o teste assíncrono do Textual ganhou `await pilot.exit(None)`. **Os 10 cenários passam.**
* **Artefactos:** `docs/badge.*.md` em 5 idiomas e o questionário dedicado.

### **30. Subcomando `gitpr split` — Commits Atómicos por Hunk (`src/split/`)**

* **O que é:** Uma árvore de trabalho com várias preocupações deixa de se tornar um commit-blob. O `split` lê o diff não commitado, pede à IA para particionar os hunks por **intenção lógica** e propõe um commit atómico por preocupação. A árvore nunca é reescrita.
* **Pacote de 6 Ficheiros:** `split_plan.py`, `hunk_parser.py` (puro), `hunk_grouper.py`, `generate_split_plan.py` (apenas de leitura), `apply_split_plan.py` (**o único que muta**) e `__init__.py`.
* **A Garantia de Árvore Intacta É Estrutural:** O `selective_stager.py` pré-valida **todos** os grupos contra um índice temporário no HEAD e usa um `GIT_INDEX_FILE` descartável.
* **Destrutivo para o Índice, Não para a Árvore:** o `--apply` faz reset do índice para `HEAD` antes de preparar.
* **Configuração:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) e `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Valores zero ou negativos são **ignorados a favor da predefinição**.
* **Testes:** 6 ficheiros em `tests/split/` (**101 cenários**).
* **Artefactos:** `docs/split-command.md` em 5 idiomas, ADR-006, `glossary-gitpr-split.md`, spec/plano e questionário.

### **31. Varredura de Segredos Embutida (`src/security_ruleset.py`)**

* **O que é:** Sete regras que correm em **todas** as invocações do linter, fundidas no fim do `load_linter_rules()` depois das regras do projeto e dos plugins.
* **As Sete Regras:** cinco `error` bloqueantes — AWS access key ID, token GitHub, token Slack, chave de API Google e chave privada — e dois `warning`: URL de conexão a base de dados com credenciais e atribuição genérica de credencial, esta última atrás de um filtro de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Porque no Pacote e Não no Template:** um portão de segredos tem de se comportar **da mesma forma** em todas as máquinas e em todo o CI.
* **Lacunas de Cobertura (v1) — confirmadas em aberto:** a atribuição **sem aspas** não é apanhada (`API_KEY=abc123`); faltam os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-`; e não existe fronteira à esquerda no nome da chave.
* **Configuração:** `GITPR_LINTER_SECURITY` (predefinição `true`; opt-out fail-open) e `GITPR_LINTER_SECURITY_DISABLED_RULES` (separadas por `;`).
* **Testes:** `tests/test_security_ruleset.py` (**61 cenários**).
* **Artefactos:** ADR-007, `glossary-gitpr-secret-scanning.md`, spec/plano/questionário e 3 relatórios de tarefa.

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
* **Dois Defeitos Corrigidos (janela v0.0.19):** o caso de uso aceita o **dict** já interpretado do provedor, e o `--finding` usa `cand.suggested_test`/`cand.diff_unified`.
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

### **35. Suíte Determinística e CI (`.github/workflows/tests.yml`)**

* **O que é:** O workflow que corre a suíte em **Python 3.10** (o piso declarado no `pyproject.toml`) e **3.13**, com `fail-fast: false`.
* **`tests/conftest.py` Hermético:** Fixa `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` e o `LANG_VERSION` na versão do código, além de `GITPR_SHOW_LOGS=false` e `GITPR_SKIP_UPDATE_CHECK=true`.
* **A Ordem Importa:** o `src.updater` é importado **antes** de as variáveis de idioma serem definidas.
* **Estabilidade dos Testes de Worker:** O `test_config_app.py` e o `test_metrics.py` aguardam por `workers.wait_for_complete()` antes de afirmar.
* **Dependência do Workflow:** O runner limpo não tem `~/.gitpr`, e o `test_demo_isolation.py` afirma que o perfil existe — o job cria o diretório e o `.env` vazio explicitamente.
* **🆕 Nesta janela a suíte cresceu 32 cenários e perdeu 7 falhas:** o `tests/test_updater.py` saltou de **23 para 55** cenários (TTL, casos-limite, cache antiga, JSON corrompido, `force`, falha de escrita, timeouts, comparador PEP 440, pré-release, skip estrito e portão de subcomando) e os 10 cenários de `tests/badge/test_badge_paths.py` passaram a verde. **Nenhum módulo novo** — o total mantém-se em 140 ficheiros.
* **Workflow da Extensão:** o `.github/workflows/extension.yml` corre `npm install`, compila, testa e empacota o VSIX; em tags `ext-v*` publica no VS Code Marketplace e no Open VSX, saltando cada passo quando `VSCE_PAT`/`OVSX_PAT` está vazio.

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
* **Identidade Git sem Forge (`identity.py`):** rótulo do repo, ramo e autor numa **única** chamada ao subprocesso.
* **A Secção de Ciclo É a Única que o Ledger Não Responde:** lista PRs integrados via `ScmProvider.list_pull_requests`; todas as falhas degradam para uma única linha — `Não lido: <motivo>`.
* **Apresentação Completa:** grupo `gitpr metrics` com 8 subcomandos, um dashboard que lê o ledger numa worker thread e o assistente de migração de três opções.
* **Testes:** `tests/test_metrics.py` (**164 cenários**).

### **37. Subcomando `gitpr mentor` e a Flag `--mentor` — Modo Mentor (`src/domain/mentor/`)**

* **O que é:** Um review diz *o que* está errado e quase nunca *porque é que isso importa*. O Modo Mentor enriquece os apontamentos com **o que se está a passar**, **porque importa**, uma **analogia** do dia a dia e os **conceitos a estudar**.
* **Camada de Domínio Pura (`mentor_explanation_builder.py`):** sem I/O, converte o payload da IA num `MentorExplanation` validado e impõe **honestidade epistémica**: sem evidência suficiente, a explicação **di-lo**; URLs alucinados são **removidos**; IDs desconhecidos são **rejeitados**.
* **Camada de Aplicação (`generate_mentor_explanation.py`):** recolhe candidatos, **lotes de no máximo 10 apontamentos**, ordenados por severidade, reutilizando a cache MD5 por hash de prompt.
* **Dois Pontos de Entrada:** `gitpr mentor [--finding <id>] [--provider <name>]` e a flag `--mentor`, que acrescenta a secção ao review normal.
* **Opt-In Estrito:** `GITPR_REVIEW_MENTOR_MODE` (predefinição `false`) e `GITPR_MENTOR_INCLUDE_ANALOGY` (predefinição `true`), editáveis na TUI de configuração.
* **Skill e Registo:** O `.gitpr.mentor.md` registado em `SKILL_FILES_BY_TYPE`, em `mcp_server.SKILL_FILES` e em `SKILL_LABELS`, com templates em **5 idiomas**.
* **Testes:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) e `tests/test_mentor_cli.py` (4) — **2 destes falham** pelo bug de isolamento herdado.
* **Artefactos:** `docs/mentor-mode.*.md` em **5 idiomas**, ADR-009, `glossary-gitpr-mentor.md`, spec e relatórios de tarefa.

### **38. Subcomando `gitpr risk` — Pontuação de Risco Determinística (`src/domain/risk/`)**

* **O que é:** Uma pontuação **0–100** com nível `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculada **sem IA** a partir de sinais locais.
* **Camada de Domínio Pura (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums e dataclasses; pesos, limiares, padrões de caminho crítico, glob matching e deteção de testes/não-executáveis carregados de YAML; cálculo por ficheiro com **clamp 0–100** e agregação 50/30/20.
* **A Fórmula É Declarada, Não Aprendida:** **50%** o ficheiro de maior risco, **30%** a média ponderada por linhas e **20%** a evidência crítica.
* **Adaptadores de Infraestrutura:** O `risk_history_reader.py` lê o `git log` e extrai commits, correções de bugs e reverts numa janela de **90 dias**; o `test_matcher.py` corresponde ficheiros de teste ↔ ficheiros de produção por convenção.
* **Caso de Uso (`calculate_risk.py`):** correlaciona secções do diff, apontamentos, emparelhamento de testes e histórico em `FileRisk`/`PullRequestRisk`, **degradando com aviso** quando um sinal não está disponível.
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
* **Alcance:** CLI (6 subcomandos, todos com `--format json`), MCP (`get_baseline_status` + `baseline://summary`), review remoto de PR e o fluxo de lint.
* **A Refatoração que a Funcionalidade Exigiu:** o `NormalizedFinding` passou a tipo canónico em `src/domain/finding/`.
* **Testes:** `tests/domain/baseline/` (5 ficheiros, **126**), `tests/application/use_cases/` (5 ficheiros, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) e `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34).
* **Artefactos:** `docs/baseline-suppressions.*.md` em **5 idiomas**, ADR-012, `glossary-baseline.md`, spec/plano/questionário e relatório de tarefa.

### **41. Subcomando `gitpr check` — Portão de CI Unificado (`src/domain/check/`, `src/infrastructure/ci/`)**

* **O que é:** Um **ponto de entrada único** para pipelines de CI que orquestra análise estática, bridges SAST, varredura de segredos, review por IA opcional, pontuação de risco e classificação por baseline, e emite **um artefacto padronizado** com exit code determinístico.
* **A Ordem do Pipeline É Contrato:** (1) resolução da policy — inválida sai com `2`; (2) extração do diff com smart excludes; (3) linter regex + segredos + bridges externas; (4) pontuação de risco preliminar; (5) **review por IA opcional**; (6) classificação por baseline; (7) avaliação do limiar `--fail-on`; (8) geração do artefacto com **mascaramento de segredos**.
* **Camada de Domínio (`src/domain/check/`):** `check_types.py` (severidade normalizada em três níveis com aliases da especificação); `check_config.py` (precedência **CLI > env > defaults**); `check_output.py` (despacho por formato e escrita atómica).
* **Camada de Infraestrutura (`src/infrastructure/ci/`):** `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (testsuites com `<failure>` e `<system-out>`) e `masking.py`.
* **Caso de Uso (`run_check.py`):** `resolve_diff_base()` (explícito → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), execução do linter e das bridges, pontuação de risco, review opcional, baseline e envelope final. O stdout emite **apenas o artefacto**.
* **Exit Codes:** `0` sucesso; `1` apontamento contável no limiar; `2` erro de execução.
* **CLI:** `gitpr check` com 8 opções e 9 variáveis `GITPR_CHECK_*`.
* **GitHub Action Reescrita:** o `action.yml` passou a um único passo `gitpr check`. **Quebra declarada** para workflows que dependiam de `--linter` + `--fullreview`.
* **Testes:** **37 cenários** — `test_run_check.py` (8), `test_check_types.py` (8), `test_check_config.py` (3), `test_check_output.py` (3), `test_masking.py` (6), `test_sarif_builder.py` (2), `test_junit_builder.py` (2) e `test_check_cli.py` (5).
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
* **CI/CD da Extensão:** o `.github/workflows/extension.yml` instala, compila, testa e empacota o VSIX; em tags `ext-v*` publica no Marketplace e no Open VSX.
* **O que a Extensão Não Faz:** não faz commit, não faz push e não publica sem confirmação.
* **Testes:** a sua própria suíte Mocha (7 cenários, incluindo stdio real) + `tests/test_mcp_server.py` (115 cenários).
* **Artefactos:** `docs/editor-extension.*.md` em **5 idiomas**, ADR-014, `glossary-editor-extension.md`, specs, `plansfacts` e o relatório Gemini.

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
| `tests/test_commit_classifier.py` | 30 | Classificação Conventional Commits + `pr_number_from_merge()` nas 4 forges |
| `tests/test_config_app.py` | 78 | TUI de configuração: montagem, troca de categorias, controlo de alterações pendentes, F2 bloqueado, Ctrl+R, procura, segredos |
| `tests/test_config_cli.py` | 9 | Registo do subcomando `config`, `-h`, import preguiçoso, stdout limpo |
| `tests/test_config_schema.py` | 42 | Cobertura de `DEFAULT_CONFIG`, sem duplicados, categorias/tipos, `advanced` só em Advanced, secção de skills |
| `tests/test_config_store.py` | 22 | Ida e volta num `.env` temporário, comentários e ordem preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuração dos revisores sugeridos (chaves e predefinições) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, modelos, `validate_ai_key()` com um SDK simulado (401 vs. rede vs. ollama) |
| `tests/test_core.py` | 49 | Fluxos principais, git diff, geração de PR, temporização, staging, coautoria, idioma dos hooks |
| `tests/test_diff_failure_cli.py` | 3 | Exit code 1 em falha do git, 0 em diff vazio, fail-open em `--hook` |
| `tests/test_diff_parser.py` | 26 | Parser de diff por linhas/hunks + `summarize_patch()` e `split_patch_sections()` |
| `tests/test_explain_command.py` | 4 | CLI do `explain`: sucesso, chave ausente, diff vazio, ajuda contextual |
| `tests/test_external_linters.py` | 33 | Bridge do Checkstyle: parser de XML, subprocesso, referência cruzada com o diff, relatório |
| `tests/test_full_diff_encoding.py` | 1 | Repositório real com um ficheiro Latin-1: o byte `0xFA` torna-se `U+FFFD` e o ficheiro aparece no diff |
| `tests/test_i18n.py` | 20 | Paridade de idiomas, chaves em falta/órfãs, identidade — **3 falhas** ❌ |
| `tests/test_install_wizard.py` | 3 | Assistente de instalação interativo |
| `tests/test_issue_engine.py` | 4 | Rascunho estruturado de issue |
| `tests/test_linter_metrics.py` | 4 | Métricas do linter: erros, avisos, duração |
| `tests/test_linter_presets.py` | 5 | Presets do linter: resolução e novo descarregamento forçado |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` na CLI e na ajuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Modelos de prompt do MCP e fallback de idioma |
| `tests/test_mcp_server.py` | 115 | Ferramentas MCP (18), recursos (19), anotações, patching, CLI direta, offload, contrato da extensão |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real via subprocesso + stdio JSON-RPC |
| `tests/test_mentor_cli.py` | 4 | CLI do `mentor` e opt-in `--mentor` — **2 falhas conhecidas** ❌ |
| `tests/test_metrics.py` | 164 | **Ledger**: escrita síncrona, exportação idempotente, bundle/merge, versionamento, migração, renderizadores, custo, qualidade, módulos, provedores, ciclo |
| `tests/test_net_timeouts.py` | 12 | Timeouts de rede/IA — alinhados com a predefinição real de 180s |
| `tests/test_plugins.py` | 17 | Descoberta de plugins, fusão de regras do linter, prompts do MCP |
| `tests/test_policy_cli.py` | 26 | Grupo `policy`: list/show/validate/use/off/install/init, confirmação e lockfile |
| `tests/test_pr_publish_app.py` | 55 | TUI do PR Publisher: ecrãs, fluxos, revisores sugeridos, `NoticeScreen`, modal de merge |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de erro do linter: abortar, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save e carga JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opções, ajuda com epílogo documentado |
| `tests/test_release_engine.py` | 53 | **Corte ancorado no changelog, `_drop_released()`, ligações, mapa de PRs por merge commit**, publicação |
| `tests/test_reviewer_resolution.py` | 18 | Escada de resolução, rejeição de um nome parcial, dedup, tolerância a falhas |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugestão de revisores (ranking, exclusão, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | CLI do `risk`: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 61 | Matriz do conjunto de regras embutido: regex, marcadores, nível, wildcard, fusão, traduções |
| `tests/test_skill_command.py` | 10 | Descarregamento e validação de modelos de skill |
| `tests/test_skill_context.py` | 20 | `get_skill_context()` com `quiet=True`, fallbacks, guarda do artefacto de PR |
| `tests/test_skill_templates.py` | 99 | Varredura parametrizada de **todos os templates publicados** contra referências embutidas |
| `tests/test_smart_excludes.py` | 15 | Filtro smart de pathspec e novo descarregamento forçado |
| `tests/test_suggest_reviewers.py` | 17 | Revisores sugeridos no fluxo de PR (integração, dica de `no_login`) |
| `tests/test_tests_command.py` | 4 | Grupo `tests` e CLI do `generate`, `--finding` e epilog com ligação |
| `tests/test_thinking_words.py` | 5 | Carregamento, análise com o separador `;` e recarregamento forçado |
| `tests/test_updater.py` | 55 ⬆️ | **Portão do PyPI**: TTL deslizante, cache antiga/corrompida, escrita atómica, `force`, `bounded_urlopen`, comparador PEP 440, pré-release, skip estrito e portão de subcomando |
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
| `tests/badge/test_badge_paths.py` | 10 ✅ | Ponto único de injeção nos dois publishers — **as 7 falhas foram fechadas** |
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
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Efeito real do pack no review, no lint e no risk (com uma guarda de rede autouse) |
| `tests/review/test_diff_normalizer.py` | 20 | Fins de linha, validação do diff, smart-excludes em Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: proveniência, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orquestração: portões, PR, diff, linter, comentário opcional |
| `tests/review/test_review_pr_cli.py` | 25 | CLI do `review-pr`: opções, rejeições antes da IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 48 | Provedor Azure DevOps: org/projeto, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 44 | Provedor Bitbucket: autenticação Basic, workspace, PRs |
| `tests/scm/test_contract.py` | 42 | Contrato `ScmProvider`: assinaturas, dataclasses, erros |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` obsoleto → delega no provedor |
| `tests/scm/test_github_provider.py` | 72 | Provedor GitHub: REST, cabeçalhos, PRs, issues, releases, leitura de revisores |
| `tests/scm/test_gitlab_provider.py` | 52 | Provedor GitLab: API v4, namespace, cabeçalhos sintetizados e `overflow` |
| `tests/scm/test_init_wizard.py` | 12 | Assistente `--init`: deteção da forge, validação, persistência |
| `tests/scm/test_release_publish.py` | 8 | Publicação de release por forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 | O único módulo que muta: indexação seletiva, um commit por grupo |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unidades → grupos → pré-validação de conflitos → mensagens |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, orçamento/corte, chamada de IA, validação da resposta |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()`: texto ↔ modelo, puro |
| `tests/split/test_selective_stager.py` | 11 | Indexação de subconjuntos, verificação contra o HEAD, índice limpo |
| `tests/split/test_split_cli.py` | 31 | CLI do split: opções, dry run por predefinição, `--apply` |
| `tests/sync_i18n.py` | — | Script de verificação de cobertura de i18n (scaffold; nunca executado) |

**Total:** **2996 cenários recolhidos em 140 módulos de teste** (53 na raiz + 12 em `tests/application/use_cases/` + 10 em `tests/fix/` + 9 em `tests/scm/` + 7 em `tests/badge/` + 6 em `tests/demo/` + 6 em `tests/split/` + 5 em `tests/domain/baseline/` + 4 em `tests/domain/policy/` + 4 em `tests/review/` + 4 em `tests/infrastructure/linter/external/` + 3 em `tests/domain/check/` + 3 em `tests/infrastructure/ci/` + 2 em `tests/domain/risk/` + 2 em `tests/domain/tests_generation/` + 2 em `tests/integration/` + 2 em `tests/infrastructure/git/` + 2 em `tests/domain/linter/` + 1 em `tests/domain/finding/` + 1 em `tests/domain/mentor/` + 1 em `tests/domain/pr/` + 1 em `tests/infrastructure/baseline/`; **+32 cenários, +0 ficheiros** desde o relatório anterior).

**Notas de qualidade desta versão:**
- **Execução completa nesta máquina:** **5 failed, 2989 passed, 2 skipped, 138 subtests passed em 461,76 s (7m41s)** — as 5 falhas são exatamente as dívidas herdadas: 3 de i18n e 2 de mentor. **12 falhas → 5.**
- **As 7 falhas de `tests/badge/test_badge_paths.py` foram resolvidas.** O `publish_pull_request_use_case` passou a exigir `token` **apenas** dentro do bloco `check_remote_state` — era esse o contrato documentado, e a verificação incondicional transformava uma fixture sem token num `auth_required` (401) que impedia o `create_pull_request` de correr. O `pr_url`/`pr_number` passou a usar `getattr` com predefinição, a fixture ganhou `token="ghp_fake"` e `number=1`, e o teste do Textual ganhou `await pilot.exit(None)`. **Os 10 cenários passam em ~6 s.**
- **As 3 falhas de i18n continuam a ser dívida real de tradução e não foram tocadas nesta janela:** `test_key_parity_and_count` (es/fr em 1618), `test_no_missing_keys` (8 chaves usadas no código sem dicionário) e `test_no_orphan_keys` (2 chaves antigas). Os dicionários continuam nos mesmos 1620/1618 do relatório anterior.
- **As 2 falhas de `tests/test_mentor_cli.py` são ainda o mesmo bug de isolamento:** o `@patch("src.core.get_git_diff")` não chega à ligação importada por valor em `src/main.py`. Numa árvore cuja única alteração é um `.md` (filtrado pelos smart excludes com `:(exclude)*.md`), o diff real volta vazio, o fluxo retorna antes do renderer e a asserção falha. **Com o patch em `src.main.get_git_diff`, os dois cenários passam.** Continua a ser uma correção de duas linhas.
- **Do lado positivo, os 32 cenários novos cobrem exatamente o que mudou:** o `tests/test_updater.py` passou de 23 para **55** cenários — TTL fresco/limite/expirado, timestamp no futuro ou timezone-aware, cache antiga com `date`, JSON corrompido, `force`, falha de escrita, ausência de `.tmp`, timeouts e user-agent, comparador PEP 440, pré-release remoto, skip estrito e o portão de subcomando (bloqueio num TTY, isenção sem TTY, isenção com `-h`).
- **A suíte depende do estado da árvore para os testes do mentor:** com apenas ficheiros `.md` alterados, os dois cenários falham; com qualquer `.py` alterado, passam. Isso explica a diferença entre a execução registada no relatório da tarefa (4 falhas) e a desta janela.
- **O `tests/conftest.py` continua hermético:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` e o `LANG_VERSION` na versão do código.
- **Guarda de rede em dois sítios:** `tests/demo/conftest.py` e `tests/integration/conftest.py`.
- **Fixtures de git real:** `tests/fix/git_fixture.py`, `tests/split/git_fixture.py` e `tests/test_full_diff_encoding.py`.
- **A suíte da extensão é separada:** 7 cenários Mocha em `extensions/vscode/src/test/mcp_client.test.ts`, dos quais 1 integra realmente com o servidor MCP Python via stdio; não conta para o pytest.
- **O CI corre em Python 3.10 e 3.13** com `fail-fast: false`.

---

## **🌐 Internacionalização e Documentação**

* **Cobertura i18n — dívida inalterada:** **1620 chaves** em `pt_br.json`, `pt_pt.json`, `es_es.json` e `fr_fr.json`, mas **1618** em `es.json` e `fr.json`. O código usa **1626** chaves: **8 sem tradução** (as mensagens de `publish_pull_request` e as descrições das ferramentas MCP `run_check`/`publish_pull_request`) e **2 órfãs** (`Cannot publish a Pull Request from a detached HEAD or empty branch.` e `Current branch has not been pushed to the remote…`). Nenhuma chave nova foi acrescentada nesta janela — e nenhuma dívida foi paga. ❌
* **O `__lang_version__` mantém-se em v0.0.35** (sem bump nesta janela) — o portão de atualização não introduziu **nenhuma** nova string de interface: as mensagens existentes continuam a ser reutilizadas.
* **Fontes de tradução em passo sincronizado:** uma chave nova tem de existir no código (fonte), em `langs/pt_br.json` (**lista mestra**), nos dicionários FR/ES do `scripts/sync_all_langs.py` e nos valores curados do `scripts/fix_mangled_i18n_keys.py`; a afirmação `len(CLEAN_KEYS)` mantém-se em 49.
* **Tópicos novos:** **nenhum** nesta janela — a lacuna de documentação já tinha sido fechada na v0.0.19 e assim permanece.
* **🆕 Tópicos atualizados nesta janela:**
  - `docs/auto-update.*` (**5 ficheiros**) — uma janela de 1 hora em vez de 24, o `-u` a ignorar a cache, a tabela de isenções com as duas novas linhas (subcomando sem terminal e subcomando com `-h`), a regra PEP 440 para pré-releases e o comportamento offline reescrito.
  - `docs/tutorial/install-from-source.*` (**5 ficheiros**) — uma cache de 1 h no diagrama de versões, o parser estrito do `GITPR_SKIP_UPDATE_CHECK` (com o exemplo `=false` a **não** desligar agora a verificação) e uma nota de que a superfície de flags continua bloqueada sem TTY.
  - `README.es_es.md` e `README.fr_fr.md` — as ligações de documentação passaram de `?lang=es_es`/`?lang=fr_fr` para `?lang=es`/`?lang=fr`, o formato que o site serve realmente.
* **`CONTEXT.md` (novo, na raiz):** o glossário de domínio do portão de atualização — **update gate**, **update check**, **release cache**, **freshness window** e **interactive run**, cada um com os sinónimos a evitar. Veio da sessão de grill que precedeu a correção.
* **Documentação em 5 idiomas:** **50 tópicos canónicos** em `docs/` (inalterado) — **47 com cobertura completa nos 5 idiomas** e **3 tópicos parciais** (`github-issue-prompt-com-gh`, `version-markers`, `review-pr`), os mesmos 3 de antes.
* **Skills locais do Claude Code:** `.claude/skills/` com **29 skills** (contagem inalterada).
* **Índice de Memória:** `.claude/memory/MEMORY.md` com **40 padrões** (contagem inalterada).
* **Relatórios de tarefa:** `docs/claude-code/reports/develop_natan/` (**114** no total; **+2** — `2026-10-27_update_check_stale_cache.md` e este próprio relatório de estado) e `docs/gemini/reports/develop_natan/` (**17**; **+1** — `2026-10-27_fix_badge_paths_tests.md`).
* **Relatórios de estado:** `docs/reports/` — este é o **20.º relatório** (v0.0.20); o v0.0.19 nunca é sobrescrito.
* **Planos de desenvolvimento:** 148 ficheiros em `docs/plans/` (**+2** — `20261027_update_check_stale_cache_plansfacts.md` e `docs/gemini/plans/.../2026-10-27_develop_natan_fix_badge_paths_tests.md`) + **21 ficheiros em `docs/survey/`** (+1 — `20261027_update_check_stale_cache_surveyfacts.md`).

---

## **🔄 Pipeline de Distribuição**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **🆕 Atualização obrigatória com uma janela deslizante:** a execução verifica o PyPI no arranque e **bloqueia com exit code 1** se existir uma versão mais recente, imprimindo `pip install --upgrade gitpr-cli`. A resposta fica em cache durante **1 hora** (já não até à meia-noite) e o `--update` **consulta sempre** a rede. A comparação é PEP 440: um pré-release remoto não bloqueia uma instalação estável, e uma versão ilegível conta como desconhecida. **O portão passou a cobrir os subcomandos** quando existe um TTY; CI, pipes, hooks, MCP e `-h` continuam isentos.
3. **GitHub Releases:** removidos — sem PyInstaller, sem asset `.exe`, sem hot-swap
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala via pip) + `tests.yml` (matriz Python 3.10 e 3.13) + `extension.yml` (VSIX; publicação no Marketplace/Open VSX em `ext-v*`). O `action.yml` chama um único passo `gitpr check` — **quebra declarada** para quem use `--linter` + `--fullreview`.
5. **Servidor MCP:** entry point `gitpr-mcp` via `pyproject.toml`
6. **Extensão de Editor:** pacote VSIX autónomo (`natanfiuza.gitpr`), com canais VS Code Marketplace e Open VSX; exige Node.js 20+ só para compilar, não para usar
7. **Templates e idiomas OTA:** `templates/` e `langs/*.json` servidos do GitHub (main) — o `__lang_version__` **v0.0.35** refresca as cópias locais em `~/.gitpr/langs/`; o `__scripts_version__` **v0.0.4** refresca os hooks
8. **Versão derivada do código:** o `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — o `__version__` é a **única** fonte
9. **O `package-data` importa:** os quatro packs embutidos (`src/policy_packs/*`) viajam no wheel
10. **🆕 Estado do release:** o `__version__` está em **1.4.3** e o commit está em `main` (PR #228), mas o release **não foi cortado**: não existe tag `v1.4.3`, o PyPI serve a **1.4.2** e a secção `[1.4.3]` do `CHANGELOG.md` está **na working tree**. O pipeline de release continua manual.

---

## **📈 Evolução Desde o Relatório Anterior (v0.0.19)**

| Área | v0.0.19 (anterior) | v0.0.20 (atual) |
|------|-------------------|-----------------|
| **Versão GitPR** | 1.4.2 (publicada, tag `v1.4.2`) | **1.4.3** — integrada no PR #228, **ainda não publicada** (o PyPI serve 1.4.2, sem tag, changelog na working tree) |
| **Versão dos Idiomas** | v0.0.35 | v0.0.35 (inalterada) |
| **Versão dos Scripts de Hook** | v0.0.4 | v0.0.4 (inalterada) |
| **Provedores de IA** | Gemini + DeepSeek + Ollama | Inalterado |
| **Idiomas** | 5 idiomas, 6 dicionários | Inalterado |
| **Subcomandos** | 15 | 15 (inalterado) — 4 grupos (baseline 6, metrics 8, policy 7, tests 1) |
| **Camadas** | 9 pacotes de domínio, 15 casos de uso | Inalterado |
| **Ferramentas MCP** | 18 ferramentas / 19 recursos / 7 prompts | Inalterado |
| **Flags de CLI** | 32 na raiz + 15 subcomandos | Inalterado |
| **Variáveis de Ambiente** | 58 em `DEFAULT_CONFIG` + preços/moeda + 9 `GITPR_CHECK_*` | Inalteradas — mas o `GITPR_SKIP_UPDATE_CHECK` mudou de **semântica** |
| **Schema de Config** | 80 `ConfigField` / 16 categorias | Inalterado |
| **Portão de Atualização 🆕** | Cache diária por data local; subcomandos sem portão; `=false` desligava-o; comparação por tuplos | **TTL deslizante de 1 h**, `-u` sempre online, **PEP 440**, `bounded_urlopen` (3 s), semântica booleana estrita e portão de subcomando apenas num TTY |
| **Editor de Ajuda do Subcomando 🆕** | — | O `_subcommand_help_in_argv()` lê o `argv` em bruto porque o Click limpa o `ctx.args` antes do callback |
| **Publicação de PR 🆕** | Token exigido incondicionalmente; `result.url`/`.number` acedidos diretamente | Token exigido **apenas** com `check_remote_state=True`; `getattr` defensivo |
| **Linter** | Regex + segredos + SAST + `findings_out` + consciente do baseline | Inalterado |
| **Git Hooks** | post-checkout/post-merge/pre-push no ledger | Inalterado |
| **Camada SCM** | 4 forges, paginação, `web_links.py` | Inalterada |
| **Motor de release** | 1.4.1 e 1.4.2 commitadas, taggadas e publicadas | Secção `[1.4.3]` gerada **na working tree**; sem tag e sem publicação |
| **i18n (chaves por ficheiro)** | 1620 × 4 e 1618 × 2 — 8 sem tradução e 2 órfãs | **Inalterado** ❌ |
| **Documentação** | 50 tópicos (47 completos + 3 parciais) | 50 tópicos (47 + 3) — **nenhum tópico novo**; `auto-update` e `tutorial/install-from-source` atualizados nos 5 idiomas |
| **Glossário de domínio 🆕** | — | `CONTEXT.md` na raiz com 5 termos do portão de atualização |
| **CI** | `tests.yml` (3.10 + 3.13) + `action.yml` + `extension.yml` | Inalterada |
| **Suíte de Testes** | 2964 cenários (140 ficheiros) | **2996 cenários (140 ficheiros) — +32 cenários, +0 ficheiros** |
| **Falhas Conhecidas** | 12 (7 badge + 3 i18n + 2 mentor) | **5** (3 i18n + 2 mentor) — **as 7 do badge fecharam** |
| **Dependências** | Nenhuma dependência Python nova | Nenhuma nova — o `packaging` já era uma dependência (policy packs) |
| **Commits desde o relatório** | 4 commits | **2 commits** (`68df9ae`, `145cbac`) |
| **PRs integrados** | 4 PRs (#218, #220, #222, #223); 1 issue (#221) | **3 PRs (#224, #226, #228)**; 2 issues fechadas (#225, #227) |
| **Índice de Memória** | 40 padrões | 40 padrões (inalterado) |
| **Relatórios de tarefa** | 112 claude-code, 16 gemini | **114 claude-code (+2) e 17 gemini (+1)** |
| **Planos de desenvolvimento** | 146 planos, 20 surveys | **148 planos (+2), 21 surveys (+1)** |

---

## **🚧 Próximos Passos**

* **Publicar a 1.4.3 no PyPI:** o `__version__` já está em 1.4.3 e o commit está em `main` (PR #228), mas continua a faltar: **commitar a secção `[1.4.3]` do `CHANGELOG.md`** (está na working tree), criar a tag `v1.4.3` e carregar o pacote. Até lá, as instalações em 1.4.1/1.4.2 **não veem a correção** — a lógica antiga responde a partir da cache diária. 🆕
* **Pagar a dívida de i18n:** 8 chaves usadas sem tradução (mensagens de `publish_pull_request` e descrições MCP) + 2 órfãs + as 2 chaves em falta em `es.json`/`fr.json`. Fechar isto desbloqueia de uma vez `test_key_parity_and_count`, `test_no_missing_keys` e `test_no_orphan_keys`. **Item herdado, ainda aberto.**
* **Corrigir o isolamento de `tests/test_mentor_cli.py`:** são as **duas falhas mais antigas** da suíte e as **únicas** restantes além das de i18n. O patch aponta a `src.core.get_git_diff`, mas o `src/main.py` importa o nome **por valor**. Mudar o alvo para `src.main.get_git_diff` fecha a suíte sem tocar no produto. **Item herdado, ainda aberto.**
* **Automatizar a release no GitHub Actions:** o CI de testes tem quatro workflows, mas o build + upload para o PyPI continuam manuais — e o `extension.yml` já mostra o padrão (gatilho por tag) que um `release.yml` pode seguir. **Item herdado, ainda aberto.**
* **Alargar a cobertura do conjunto de regras de segredos:** as três lacunas continuam abertas — atribuição sem aspas (`API_KEY=abc123`), os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-`, e a ausência de fronteira à esquerda no nome da chave. **Item herdado, ainda aberto.**
* **Traduzir o `docs/review-pr.*.md`:** é a **última** família de funcionalidades com cobertura parcial (2 de 5 idiomas). **Item herdado, ainda aberto.**
* **Deixar de rastrear os artefactos de `.gitpr/metrics/export/`:** restam **34 ficheiros rastreados**, porque o `gitignore` não desrastreia. Falta um único `git rm --cached`. **Item herdado, ainda aberto.**
* **Documentar a quebra de contrato do `request_pull_request_reviewers`:** passou de `None` para `list[str]`; as subclasses de terceiros do `ScmProvider` precisam de ser atualizadas. **Item herdado, ainda aberto.**
* **Documentar a mudança de semântica do `GITPR_SKIP_UPDATE_CHECK` como nota de migração:** o `docs/tutorial/install-from-source` já descreve o parser estrito, mas as automatizações que definem `=false` a contar silenciar a verificação passam agora a vê-la correr. Vale a pena acrescentar uma linha ao `auto-update` e ao changelog do próximo release. 🆕
* **Mapear o raio de impacto do baseline no exit code:** um baseline que não pode ser aplicado aborta seis fluxos com exit code 1, e o `check` usa **2**. Vale a pena explicitar o contrato completo em `docs/baseline-suppressions`/`docs/check-ci`. **Item herdado, parcialmente coberto pelo check.**
* **Reconciliar a versão do projeto:** o `CLAUDE.md` ainda diz `Current version: 0.0.37` enquanto o `__version__` está em **1.4.3**. Definir uma convenção única. **Item herdado, ainda aberto.**
* **O `gitpr -h config` ignora o `-h`:** o subcomando abre a TUI em vez de mostrar a ajuda — o portão `if ctx.invoked_subcommand is not None: return` corre antes do bloco do `help_flag`. **Item herdado, ainda aberto** (e agora partilhando o mesmo ponto do portão de atualização).
* **Migrar o extrator do `sync_i18n.py` para AST:** a regex trunca literais com concatenação implícita (`__("a " "b")`). O `tests/test_i18n.py` já usa AST; o script de sincronização continua em regex. **Item herdado, ainda aberto.**
* **Secção Smart Exclude na TUI:** o item 10 do questionário da TUI de configuração continua por começar, e os campos `GITPR_SMART_EXCLUDES_*` continuam sob `diff`, sem categoria própria. **Item herdado, ainda aberto.**
* **Dívidas registadas no plano da TUI de configuração:** o `DEFAULT_CONFIG` ficou redundante com o schema; o banner de abertura não lista `--init`, `--base` nem `--plugins`; o `LinterApp` é a **única** app Textual que ainda não desativa a command palette; e as **9 `GITPR_CHECK_*`** continuam **sem** categoria no schema. **Item herdado, ainda aberto.**
* **Criar uma nota de migração para o `action.yml`:** substituir `--linter` + `--fullreview` por `gitpr check` é uma quebra de contrato para workflows existentes; o `docs/check-ci` documenta o comando novo, mas não um "antes e depois". **Item herdado, ainda aberto.**
* **Publicar a extensão nos marketplaces:** o workflow está pronto, mas depende de `VSCE_PAT`/`OVSX_PAT`; até lá, a instalação é por VSIX local. **Item herdado, ainda aberto.**
* **Rever o `git diff` em repositórios com ficheiros binários grandes:** a descodificação com `errors="replace"` resolve o conteúdo legado, mas os bytes binários do diff passam agora ao modelo como uma sequência de `U+FFFD`. Vale a pena confirmar que os smart excludes cobrem os casos reais. **Item herdado, ainda aberto.**
* **🆕 Revalidar o portão real depois do release:** com a 1.4.3 publicada, uma instalação em 1.4.3 **não** deve bloquear, e uma em 1.4.2 deve bloquear no prazo de uma hora após o pacote subir — o cenário que originou a correção.
* **🆕 Acompanhar o fallback ES/FR no site:** as ligações dos READMEs já levam `?lang=es` e `?lang=fr` (o formato correto), mas o site pode continuar a servir inglês para esses idiomas. Publicar as traduções cumpre a promessa das ligações.
* **Provedor Anthropic Claude:** Suporte direto para a API do Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual no Dashboard:** Histogramas temporais e tendências de tokens na TUI de métricas.
* **Seed para o `.gitpr/conf/` local:** O seeding de templates de configuração local (smart-excludes, linter) continua pendente como subcomando próprio ou passo de assistente.
* **Mais provedores:** OpenAI direto, provedores locais adicionais.

### ✅ Concluídos nesta janela (2026-10-27 → 2026-10-28)

* ~~**Corrigir as 7 falhas de `tests/badge/test_badge_paths.py`**~~ — PR #226 (`68df9ae`): a validação do token passou para dentro do `check_remote_state`, o `url`/`number` passaram a usar `getattr`, a fixture ganhou `token="ghp_fake"`/`number=1` e o teste Textual ganhou `pilot.exit(None)`. **Item herdado durante três janelas consecutivas — fechado.**
* ~~**O portão de atualização não detetava um release publicado no próprio dia**~~ 🆕 — PR #228 (`145cbac`): TTL deslizante de 1 h, `-u` sem cache, PEP 440, `bounded_urlopen`, escrita atómica, skip estrito e portão de subcomando apenas em execuções interativas. **32 cenários novos em `tests/test_updater.py`.**
* ~~**Os subcomandos escapavam ao portão**~~ 🆕 — o `gitpr release`, `fix`, `review-pr` e os restantes são agora bloqueados num terminal, com isenção para CI/pipe/hook e o `-h` lido do `argv` em bruto.
* ~~**As ligações dos READMEs ES/FR apontavam para um parâmetro de idioma inexistente**~~ 🆕 — `?lang=es_es`/`?lang=fr_fr` passaram a `?lang=es`/`?lang=fr` (PR #226).
* ~~**Documentar o auto-update com a janela de 24 h**~~ 🆕 — as 5 variantes de `docs/auto-update.*` e as 5 de `docs/tutorial/install-from-source.*` foram reescritas para a janela de 1 h, as novas isenções e a regra PEP 440.

---

**Relatório gerado em:** 2026-10-28  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
