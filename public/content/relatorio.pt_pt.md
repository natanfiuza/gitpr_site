# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.18 (2026-10-18)**

## **📌 Visão Geral**

O **GitPR** é uma ferramenta de CLI (Command Line Interface) avançada para automação de processos Git utilizando Inteligência Artificial (Google Gemini / DeepSeek / Ollama). O objetivo principal é atuar como um assistente inteligente local que faz Code Reviews, gera Pull Requests, mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo de trabalho do desenvolvedor (Shift Left).

**Novidades desta versão (v0.0.18):**
- **`gitpr check` — um único portão para o CI:** o linter, a varredura de segredos, as bridges SAST, a pontuação de risco, o baseline e o review por IA opcional passam a correr atrás de **um único comando**, com artefactos **SARIF 2.1.0**, **JSON** ou **JUnit XML** e um contrato de saída determinístico (`0` sucesso, `1` apontamento bloqueante, `2` erro de execução). O `action.yml` foi reescrito para chamar este comando, a chave de API deixou de ser obrigatória e há um caminho `--no-ia` de custo zero.
- **Extensão VS Code e Cursor — o editor tornou-se um cliente MCP:** oito comandos (`commit`, `review`, `fullReview`, `reviewMentor`, `generatePr`, `generatePrExplain`, `publishPr`, `check`) falam com o `gitpr-mcp` via stdio e **nunca duplicam lógica de negócio** — chaves, tokens e prompts ficam do lado da CLI. A extensão publica PRs em dois passos, preenche o painel **Problems** com os apontamentos do `run_check` e mascara segredos dos dois lados da fronteira.
- **Duas novas tools MCP e um caso de uso partilhado:** a `run_check` (apenas de leitura, `no_ia=true` por predefinição) e a `publish_pull_request` (nunca faz commit nem push) entraram no servidor, que passou de **16 para 18 tools**. A publicação `--no-edit` deixou de ter código próprio: a CLI e o MCP passam pelo mesmo `publish_pull_request_use_case`, com os estados `success`/`needs_push`/`already_exists`/`auth_required`/`error`.
- **A skill de PR deixou de propagar um artefacto colado por acidente:** um fragmento JSON (`".gitpr.commit.md": "gitpr.commit.md",`) acabou na regra de estrutura do template `pt_br` e a IA repetia-o em **todos** os PRs gerados. Como o `--skill` nunca sobrescreve um ficheiro existente, corrigir o template não bastava: o `get_skill_context()` passou a **remover o fragmento em memória** (sem tocar no ficheiro do utilizador), e um novo teste varre **todos os templates publicados** para evitar a reincidência.
- **O merge de PR ganhou feedback visual:** a chamada à forge corria em silêncio numa thread daemon e uma exceção inesperada **matava a thread sem aviso**, deixando a TUI bloqueada. O modal de progresso existente foi generalizado (`title` opcional, `mark_finished(show_close=False)`), passou a ser empilhado antes de o worker arrancar e cobre agora **sucesso, conflito e erro inesperado**, com dispensa automática após 1.2 s no caminho de sucesso.
- **A dívida de i18n voltou a abrir — e a suíte reporta-a:** **8 chaves `__()` usadas no código não existem em nenhum dicionário** (publicação de PR e descrições das tools MCP) e **2 ficaram órfãs**; o `es.json` e o `fr.json` ficaram em **1618** chaves contra 1620 nos outros quatro. Somadas às **7 falhas em `tests/badge/test_badge_paths.py`** — o provedor falso dos testes não tem `.token`, e o caso de uso partilhado passou a exigi-lo — e às **2 falhas conhecidas** em `tests/test_mentor_cli.py`, a suíte fecha em **2938 passados de 2952** (12 falhas, 2 skips).
- **A release 1.4.1 está preparada, mas ainda não fechada:** o `__version__` subiu para **1.4.1** (commit `46c25c1`) e o `__lang_version__` para **v0.0.35**; o `CHANGELOG.md` já tem a secção `[1.4.1]` gerada pelo motor de release — **na árvore de trabalho**, e a tag `v1.4.1` ainda **não existe**.

- **Versão atual:** 1.4.1 (bump **consolidado** em `46c25c1`; **tag `v1.4.1` ainda não criada**; `v1.4.0` continua a ser a última tag, de 2026-10-12)
- **Versão dos dicionários de idioma:** v0.0.35 (v0.0.34 → v0.0.35 nesta janela)
- **Versão dos scripts de hook:** v0.0.4 (inalterada)
- **Publicação:** PyPI (`pip install gitpr-cli`) — canal binário removido
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositório:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licença:** LGPL-2.1
- **Idiomas suportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 dicionários)

---

## **🏗️ Arquitetura Base e Bibliotecas**

* **Linguagem:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags e formatação de terminal) — **15 subcomandos** 🆕 (`badge`, `baseline`, `check` 🆕, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `split`, `tests`). Quatro deles são **grupos**: `baseline` (6 subcomandos), `metrics` (8), `policy` (7) e `tests` (1). O `check` é um comando único com **8 opções de configuração** (sem contar `-h`).
* **UI/Terminal:** Textual — TUI para chat interativo, edição de issues, ecrã de ajuda, dashboard de métricas, PR Publisher (com o **modal de progresso reutilizado no merge** 🆕), erros do linter (`LinterApp`), configuração (`ConfigApp`), o modal `NoticeScreen`, a **visita guiada do `gitpr demo`** (`src/ui/demo/`) e o **assistente de migração do ledger** (`src/ui/metrics_migration_app.py`).
* **Arquitetura em Camadas:** `src/domain/` (regras puras, sem I/O) + `src/application/use_cases/` (orquestração, **15 casos de uso** 🆕) + apresentação (CLI/TUI). **9 pacotes de domínio** 🆕: `baseline`, **`check`** 🆕, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`.
* **Extensão de Editor 🆕:** `extensions/vscode/` — cliente MCP fino em TypeScript para **VS Code e Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). Não contém regras de negócio: abre uma sessão stdio com o `gitpr-mcp`, verifica o contrato das 7 tools e delega tudo.
* **Criptografia:** `cryptography.fernet` para proteção local de chaves de API, tokens GitHub e tokens SCM das forges.
* **Configuração:** `python-dotenv`, `pyyaml` (linter estático) + o seu próprio schema declarativo (`src/config_schema.py`, **80 `ConfigField`** e **16 categorias**).
* **Provedores de IA:** Integração via SDK oficial do Google GenAI, OpenAI SDK (DeepSeek) e OpenAI SDK (Ollama local). Predefinições configuradas: `gemini-pro-latest` / `gemini-flash-lite-latest` e `deepseek-v4-pro` / `deepseek-v4-flash`.
* **APIs das Forges:** `requests` (REST) — abstração multi-forge em `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py` (URLs de commit, PR e perfil por forge, funções puras); `src/github_api.py` mantido como shim obsoleto.
* **Linter / SAST:** regex YAML + bridge Checkstyle + conjunto de regras de segredos embutido (`src/security_ruleset.py`) + 4 bridges em `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), com o modelo normalizado em `src/domain/linter/sast_finding_mapper.py` e o apontamento canónico em `src/domain/finding/`.
* **Infraestrutura de CI 🆕:** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) e `masking.py` (mascaramento de credenciais antes de serializar qualquer artefacto).
* **Git Internals:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py` (rótulo de repo, branch e autor numa única chamada ao git), `risk_history_reader.py` e `test_matcher.py`.
* **Persistência local:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) com WAL, `PRAGMA user_version`, chaves UUID e transporte por bundle.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 ferramentas anotadas** 🆕, **19 recursos**, 7 prompts.
* **Testes:** Pytest + `unittest.mock` (**138 módulos de teste** 🆕 — 51 na raiz, 12 em `tests/application/use_cases/`, 10 em `tests/fix/`, 9 em `tests/scm/`, 7 em `tests/badge/`, 6 em `tests/demo/`, 6 em `tests/split/`, 5 em `tests/domain/baseline/`, 4 em `tests/domain/policy/`, 4 em `tests/review/`, 4 em `tests/infrastructure/linter/external/`, **3 em `tests/domain/check/`** 🆕, **3 em `tests/infrastructure/ci/`** 🆕, 2 em `tests/domain/risk/`, 2 em `tests/domain/tests_generation/`, 2 em `tests/integration/`, 2 em `tests/infrastructure/git/`, 2 em `tests/domain/linter/`, 1 em `tests/domain/finding/`, 1 em `tests/domain/mentor/`, 1 em `tests/domain/pr/`, 1 em `tests/infrastructure/baseline/` —, **2952 cenários recolhidos**) + testes e2e do servidor MCP via subprocesso real (JSON-RPC stdio) + fixtures de repositório git real (`tests/fix/`, `tests/split/`) + guarda de isolamento de rede (`tests/demo/conftest.py`, `tests/integration/conftest.py`). A extensão tem a sua própria suíte Mocha (7 testes contra o servidor MCP real).
* **Empacotamento:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; packs embutidos via `package-data`; `packaging>=23.0`. A extensão é empacotada separadamente (`vsce package`, VSIX).
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` (passa a chamar o `gitpr check`) + `tests.yml` (matriz Python 3.10/3.13) + **`extension.yml`** 🆕 (build, teste e empacotamento do VSIX; publicação no Marketplace/Open VSX em tags `ext-v*`).

---

## **🧩 Módulos Implementados e Arquitetura de Ficheiros**

### **1. Operações Core e Git (`src/core.py`)**

* **Geração Estruturada:** Comunica com o LLM a pedir um retorno estritamente em JSON.
* **Map-Reduce (Diffs Gigantes):** Quando o diff ultrapassa ~90k tokens, divide-o automaticamente em lotes por ficheiro (`split_diff_into_chunks`), processa cada parte (Map) e unifica os resumos (Reduce). Suporta PRs, commits e Issues. O `provider`/`model` passam a ser agregados entre os chunks e o `meta`/`modules` são repassados ao `log_command_metric`, que passa a copiar tokens reais dos metadados do provedor no momento da escrita.
* **Tokenizer Local:** `tokenizer.json` para estimativa precisa de tokens antes do envio à IA.
* **Estimativa de Tokens:** Heurística leve `len() // 4` via `estimate_token_count()` com fallback para o tokenizer local.
* **Otimização Git Nativa:** Flags `-U1`, `-w`, `-M`, `-B` nos comandos `get_git_diff` e `get_git_full_diff` para reduzir contexto inútil.
* **`get_split_diff()`:** O diff próprio do `split`, com `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sem** reutilizar o `get_git_diff()` — o `-w` (ignora espaços em branco) e os smart-excludes quebrariam a garantia de árvore byte-idêntica.
* **Pré-Gravação (`--pre-save`):** Flag de debug oculta que guarda o payload completo (instrução do sistema + prompt) em JSON antes de cada chamada à IA.
* **Exclusões Inteligentes com Duas Camadas:** Camada global (`~/.gitpr/conf/`) + camada local do projeto (`./.gitpr/conf/`), unidas em runtime. Auto-seed do ficheiro local na primeira execução. `_load_smart_excludes()` aceita `force=`.
* **Métricas com Registo de Tempo:** `log_command_metric()` em todos os fluxos, agora com `meta`, `modules` e `duration_ms`.
* **Resolução Centralizada do Output:** `resolve_output_path()` — predefinição em `.gitpr/reports/{type}/`.
* **Assistente SCM (`run_scm_init_wizard()`)**: `gitpr --init` — deteta a forge a partir do remote de origem, solicita extras por forge, valida o token e persiste **apenas em caso de sucesso**; anuncia o selo automático no caminho do PR.
* **Template de Skill de Release (`ensure_release_skill_template()`)**: Transfere `templates/gitpr.release.*.md` na primeira utilização do `gitpr release`.
* **Guarda contra Skills Corrompidas 🆕:** O `_CORRUPTED_PR_SKILL_ARTIFACT` documenta o fragmento JSON que vazou para o template `pt_br`, e o `get_skill_context()` remove-o **em memória** quando `action_type == "pr"` — o ficheiro do utilizador permanece intacto byte a byte, e as outras ações (`commit`, `review` etc.) leem o ficheiro tal como está. Como o prompt muda, a chave MD5 da cache também muda e as respostas contaminadas deixam de ser reutilizadas.
* **Registo de Skills Partilhado:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` em `src/config.py`, com a entrada `mentor` já registada.
* **Motor de Review com Âmbito de Cache (`generate_pr_content`):** `cache_scope` (anexado **apenas à chave de cache**) e `store_diff` (guarda o diff revisto); o `cache_scope` transporta também o policy pack ativo.
* **Trailer de Coautoria:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceiros.
* **Subprocessos Blindados:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` em todos os `subprocess.run`; verificação de ligação via socket `8.8.8.53:53` antes das operações de rede.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitetura de Plugins:** Carrega plugins de `~/.gitpr/plugins/`, aplicando-se a **todos os projetos**.
* **Plugins de Linter (`linter/`):** Ficheiros `.yml` unidos ao `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`):** Ficheiros `.md` que estendem o contexto do sistema.
* **Factory Closures:** `get_linter_plugins` e `get_prompt_plugins` isolam estado entre sessões.
* **Comando `--plugins`:** Lista todos os plugins globais instalados com os seus tipos e paths.
* **Documentação Multilíngue:** `docs/plugins-system.md` em 5 idiomas.
* **Relação com os Policy Packs:** o pack é uma **terceira** camada de regras — os plugins pertencem à máquina, o `.gitpr.linter.yml` pertence ao projeto e o pack é versionado e consolidado. O `load_linter_rules()` une as três na ordem declarada, com as severidades do pack por cima.

### **3. Interface CLI e Configuração (`src/main.py` e `src/config.py`)**

* **Setup Inicial:** Deteta a primeira execução, cria `~/.gitpr/` e solicita interativamente as chaves, preferências e idioma. A dica de primeira execução aponta para o `gitpr demo`.
* **Routing de Comandos:** Gere todas as flags e os **15 subcomandos** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline` e **`check`** 🆕.
* **Comportamento Predefinido:** Executar `gitpr` sem flags abre a TUI do PR Publisher.
* **Flags (32 opções Click na raiz — inalteradas nesta janela):** `--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` etc.
* **Subcomando `check` 🆕 — 8 opções:** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on info|warning|error|minor|major|critical|blocker`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` e `--policy <name>@<version>`. O `-h` do subcomando já aponta para `docs/check-ci` no idioma corrente.
* **Grupo `baseline` — 6 subcomandos:** `create` (`--refresh`, `--base`, `--yes`, `--format`), `show` (`--status`, `--rule`, `--file`, `--format`), `validate` (`--format`), `update` (`--recompute`, `--base`, `--yes`), `suppress` (`--reason`, `--scope`, `--debt`, `--owner`, `--due-date`, `--yes`) e `unsuppress` (`--yes`).
* **Grupo `policy` — 7 subcomandos:** `list`, `show`, `validate`, `use`, `off`, `install` (`--force`, `--yes`) e `init` (`--stack`, `--yes`).
* **Grupo `metrics` — 8 subcomandos:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` e o oculto `hook-event`; os de leitura partilham `--days`/`--since`/`--until`.
* **Subcomando `risk` — 4 opções:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Subcomando `mentor` — 2 opções:** `--finding`, `--provider`.
* **Variáveis de Ambiente (58 chaves no `DEFAULT_CONFIG`):** as quatro `GITPR_BASELINE_*`, as duas do mentor, as duas da policy e a `GITPR_RISK_INCLUDE_IN_REVIEW`. **Fora do `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY` e as **9 `GITPR_CHECK_*`** 🆕 (`BASE`, `FORMAT`, `OUTPUT`, `FAIL_ON`, `NO_IA`, `IA_ONLY_HIGH_RISK`, `BASELINE_PATH`, `POLICY`, `TIMEOUT_SECONDS`).
* **Ajuda Contextual:** `-h --flag` apresenta documentação específica da flag com uma ligação ciente do idioma; os subcomandos têm o seu próprio `epilog=` (parágrafo `\b` do Click). O `risk`, o `policy`, o `baseline`, o `mentor` e o `check` (via Click) expõem a sua própria ajuda.
* **Camada de Escrita do `.env`:** `read_env_file_values()` lê **apenas o ficheiro** via `dotenv_values`; `save_config_values()` com `set_key`; `remove_config_value()` com `unset_key`. `validate_ai_key()` distingue uma credencial recusada (`401`/`403`) de uma rede inacessível.
* **Portão de Baseline no Fluxo:** O `main.py` resolve o portão do baseline antes do lint, do review, do full review, da auditoria de ficheiro, do review remoto e do risk, e o **exit code do lint segue o bloqueio ciente do baseline**.
* **Publicação Direta Refatorada 🆕:** O `_publish_pr_directly` (`--no-edit`) deixou de chamar a forge inline e passa a delegar no **`publish_pull_request_use_case`** — o mesmo caminho da tool MCP `publish_pull_request`. O selo GitPR continua a ser anunciado antes da chamada, e o corpo recomendado é composto pelo `format_recommended_commit_body()`.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` e `src/ui/pr_publish_help.py`)**

* **Interface Interativa Completa:** TUI para rever, editar e publicar Pull Requests diretamente no terminal.
* **7 Ecrãs Modais:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` e `NoticeScreen`.
* **Selo no Corpo do PR:** O `attach_pr_badge()` corre no `src/main.py` logo após o `pr_data` estar completo — **ponto único de injeção**. Anexação idempotente por marcador e divulgação explícita no `--no-edit`.
* **`_attach_reviewers` com resolução:** Resolve o que o utilizador escreveu **antes** de enviar, reporta o que foi descartado e, num `422` de lote, **repete um a um**.
* **Revisores Sugeridos:** Consulta a forge para obter revisores sugeridos; a `_reviewer_suggestion_view()` monta as `resolutions` via `resolve_candidates` e pré-preenche os `handles`.
* **Bindings:** F1 (Ajuda), F2 (Guardar .md local), F3 (Publicar via forge), Esc (Sair).
* **Fluxo de Auto-Commit:** Linter → mensagem IA → confirmação → commit → push → publica PR. O resultado do linter transporta os **apontamentos estruturados** (`findings_out`), que é o que o baseline classifica antes de o relatório ser composto.
* **Modal de Progresso no Merge 🆕:** O `CommitProgressScreen` ganhou um `title` opcional (predefinição `📦 Processing commit…`) e `mark_finished(show_close=False)`; o `_merge_progress_screen` é empilhado **antes** de o worker arrancar, e o worker arranca a partir do temporizador de montagem para eliminar a race entre a montagem e um callback rápido. O sucesso mostra `✅ PR #N merged successfully!`, preenche a barra, oculta o botão Close e dispensa automaticamente após **1.2 s** antes do pedido para abrir no navegador; o conflito e o erro inesperado (agora capturado por um `except Exception` que regista) passam pelo `_on_merge_failure`. Se o utilizador fechar o modal a meio, o merge termina e o pedido aparece sem tocar em widgets mortos. Cinco idiomas ganharam as três novas strings (en, pt_br, pt_pt, es/es_es, fr/fr_fr).
* **Tratamento de PR Existente / Auto-Upstream / Merge Flow** — inalterados (`GITPR_AUTO_MERGE`).

### **5. Módulo de API do GitHub (`src/github_api.py`)**

* **Shim Obsoleto:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` e as restantes funções delegam em `src/infrastructure/scm/github_provider.py`; emite um `DeprecationWarning` e mantém os tuplos legacy `(ok, data, status)` — nenhum código novo o pode importar.

### **6. Motor de Análise Estática / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analisa estaticamente as linhas adicionadas (`+`) no diff sem gastar quotas de IA.
* **Regras YAML:** Lê o `.gitpr.linter.yml` (criado via `--skill`).
* **Plugins de Linter:** Regras adicionais carregadas de `~/.gitpr/plugins/linter/*.yml`.
* **Wildcard de Extensão:** `extensions: ["*"]` significa **todos** os ficheiros, incluindo os que não têm sufixo e os dotfiles.
* **Conjunto de Regras de Segredos Embutido:** O `load_linter_rules()` junta o `src/security_ruleset.py` **depois** das regras do projeto e dos plugins. Sete regras, todas `extensions: ["*"]`: cinco `error` e dois `warning`. O alerta **nunca ecoa o valor**.
* **Camada de Policy Pack:** O `load_linter_rules()` aplica a fusão em camadas do pack ativo e **sobrepõe as severidades**, sem tocar no `.gitpr.linter.yml` nem nos plugins.
* **Apontamentos Estruturados (`findings_out`):** além das strings de alerta, o motor devolve os apontamentos normalizados do tipo canónico `src/domain/finding/` — é essa lista que o baseline usa para a impressão digital e que o `gitpr check` serializa.
* **Bridges SAST Opt-In:** O Semgrep, o Gitleaks e o Bandit correm no caminho de ficheiro inteiro **e** no caminho de diff, com dedup `[Gitleaks + Regex]`, mascaramento do valor (`AKIA****`) e um aviso quando a ferramenta está ativada mas fora do `PATH`.
* **`skip_external`:** O `parse_diff_and_lint(..., skip_external=True)` no fluxo remoto, porque a bridge executa binários contra ficheiros em disco — a árvore local, não o PR.
* **Relatório Consolidado:** O `generate_linter_report_content()` consolida erros regex + externos em `.gitpr/reports/linter/` — gerado apenas quando há violações.
* **Ciente do baseline:** os alertas são anotados com o estado do baseline antes de se tornarem relatório, e o exit code do fluxo de lint segue o bloqueio ciente do baseline.

### **7. Segurança e Autenticação (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografia:** Gera a chave mestra `secret.key` em `~/.gitpr/`.
* **Proteção de Tokens:** `encrypt_data`/`decrypt_data` para chaves de IA, GitHub PAT e tokens SCM das forges.
* **Validação Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — 401 → ciclo de reautenticação preservando o rascunho.
* **Segredos na TUI de Configuração:** Os campos `KIND_SECRET` são editados mascarados, **nunca** apresentam o valor em claro e são encriptados com Fernet antes de serem escritos.
* **Deteção de Segredos no Fluxo:** O linter — que corre no hook de pre-commit — é uma barreira de credenciais com regras idênticas em todas as máquinas.
* **Mascaramento no CI 🆕:** O `src/infrastructure/ci/masking.py` remove chaves privadas, AWS access key ID/secret, tokens do GitHub (`gh[pousr]_`, `github_pat_`), Google (`AIza`), OpenAI (`sk-`), Slack (`xox[baprs]-`), `Bearer`, basic-auth em URL e atribuições genéricas de password **antes** de o artefacto ser serializado. O mesmo módulo é usado pelo `publish_pull_request_use_case` para evitar vazar um token em mensagens de erro, e a extensão tem um espelho em TypeScript (`masking.ts`).
* ⚠️ **Lacunas de cobertura confirmadas como ainda abertas:** a regra genérica continua a exigir aspas depois do separador (`\s*[:=]\s*['\"]`), pelo que o formato `.env` **sem aspas** (`API_KEY=abc123`) continua a não ser apanhado; os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-` continuam de fora do conjunto de regras do **linter** (o mascaramento do CI já cobre `github_pat_`, mas não `ASIA…` nem `xoxc-`/`xoxd-`); e não há fronteira à esquerda no nome da chave, pelo que `mytoken` corresponde da mesma forma que `token`.

### **8. Auto-Atualizador (`src/updater.py`)**

* **PyPI como Fonte Única:** O `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` e escreve a cache diária **sem** o campo `download_url`.
* **Bloqueio Obrigatório (`enforce_update_required()`):** Devolve `True` (depois de imprimir as duas versões e o comando pip) quando a versão publicada é mais recente; `False` quando está atualizado, quando a versão remota é **desconhecida (offline)** ou quando a verificação está desligada.
* **`check_and_update()`:** Apenas consulta e **reporta**, nunca instala.
* **Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` — não é publicitada; existe para a suíte e para a automatização offline.
* **Versionamento Centralizado:** `__version__` (**1.4.1** 🆕), `__lang_version__` (**v0.0.35** 🆕 — v0.0.34 → v0.0.35), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* **Estado da Release 🆕:** o bump da 1.4.1 está **consolidado** (`46c25c1`), mas a tag `v1.4.1` **não foi criada** e a secção `[1.4.1]` do `CHANGELOG.md` está **na árvore de trabalho**, não consolidada. O motor de release gerou ainda um artefacto PT-BR em `.gitpr/reports/release/develop_natan_20261018023153_RELEASE.md`, ainda por rastrear. A correção dos cabeçalhos da `[1.4.0]` (item da janela anterior) foi **consolidada** em `1abb1b5`/`fc9d400`.

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
* **Cobertura — dívida reaberta 🆕:** **1620 chaves** em `pt_br`, `pt_pt`, `es_es` e `fr_fr`, mas **1618** em `es.json` e `fr.json`. O código usa **1626** chaves: **8 não existem em nenhum dicionário** (publicação de PR e descrições das tools MCP `run_check`/`publish_pull_request`) e **2 chaves antigas ficaram órfãs**. A janela anterior tinha fechado em 1603/1603; o refactor de publicação e as descrições MCP reabriram a lacuna em 8 chaves. ❌
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
* **Uma chamada de cada vez, com cache:** o mentor e o explain reutilizam a cache MD5 por prompt — voltar a correr sobre um review inalterado não custa nada.

### **13. Cache Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Chaveamento por hash MD5 do diff e do prompt, com indexação por idioma.
* **Seleção do Último Review (`resolve_last_review()`):** Escolhe o registo `review`/`fullreview` **mais recente** para um par repo+branch — é a porta de entrada do `gitpr fix` e do `gitpr mentor`.
* **Diff Revisto (`reviewed_diff`):** Campo no topo do registo que guarda o diff efetivamente revisto, preferido por `fix/apply_fix.reviewed_diff()`.
* **Telemetria e Duração:** Persistência de `duration_ms` e `meta_raw`.
* **Leitura para o Dashboard:** O `scan_cache_files_for_dashboard()` é uma **ponte de pré-migração** — o dashboard lê o ledger, e o scan existe apenas para reconstruir a série a partir da cache quando o utilizador o escolhe no assistente de migração.
* 🆕 **Efeito da Higienização da Skill:** como o `get_skill_context()` passou a remover o fragmento corrompido do prompt de PR, a chave MD5 muda e as respostas contaminadas antigas não são reutilizadas.

### **14. Motor de Issues e TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff atual, Histórico da branch (`-ht`) e Arqueologia por Blame (`-b`).
* **Publicação Multi-Forge:** O F3 cria a issue na forge **configurada** (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — o Azure DevOps lança `ScmNotSupportedError`.
* **Map-Reduce para Issues:** Contexto acima de ~90k tokens é dividido e unificado.
* **Tratamento de 401:** Sinalização de reautenticação sem fechar a aplicação.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Acompanha a evolução e a autoria histórica com classificação de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registados via `log_blame_metric()` com profundidade e número de commits analisados.
* **Passou também a alimentar o risco:** o leitor de histórico da pontuação (`risk_history_reader.py`) usa a mesma janela de 90 dias e classifica bugs/reverts por padrão, sem chamar a IA.

### **16. Servidor MCP e Invocação Direta via CLI (`src/mcp_server.py`)**

* **18 Ferramentas MCP Anotadas 🆕:** as 16 anteriores + a **`run_check`** 🆕 (envelope JSON apenas de leitura do pipeline de CI; `no_ia` predefinido a `true`, sem review por IA a menos que pedido) e a **`publish_pull_request`** 🆕 (publica na forge configurada e **nunca faz commit nem push**; devolve o estado estruturado `success`/`needs_push`/`already_exists`/`auth_required`/`error`).
* **Parâmetros Opt-In no Contrato 🆕:** `review_code` e `full_review` aceitam `mentor: bool = False`; `generate_pr_description` aceita `explain: bool = False`. São **puramente por parâmetro** — nunca em OR com as predefinições do `.env`.
* **19 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Registo de Skills Alinhado:** O `mcp_server.SKILL_FILES` tem **11 entradas** (`tests`, `explain` e `mentor` incluídos); o `TestSkillRegistryAgreement` passa.
* **Invocação CLI Direta:** O comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca qualquer tool sem iniciar o servidor stdio; o `gitpr-mcp --list` imprime o registo completo em JSON.
* **Isolamento do Stdout Real:** O `_write_real_stdout()` escreve no `sys.__stdout__` original, com fallback para `buffer` ou nova codificação com `errors='replace'` em code pages legacy do Windows.
* **Offload do Event Loop:** o decorator `_offload` (`anyio.to_thread.run_sync`) em todas as tools — a ordem dos decorators importa (`@mcp.tool` **acima** de `@_offload`).
* **Falhas como Erros Estruturados:** as novas tools devolvem a falha **como dado** em vez de lançar — um agente que pergunta pelo estado do baseline recebe o motivo da falha, não uma exceção.
* **Métricas MCP 🆕:** o `run_check` e o `publish_pull_request` escrevem linhas locais (`mcp:check`, `mcp:publish_pr`).

### **17. TUI de Dashboard de Métricas (`src/ui/metrics_app.py`)**

* **Âmbito por Repositório:** Rótulo `📁 Repository: owner/repo` e filtragem estrita por projeto.
* **Varredura Assíncrona com Overlay:** worker thread em background com uma `ProgressBar`; os testes aguardam `workers.wait_for_complete()` antes de afirmar.
* **Leitura do Ledger:** o dashboard consulta o `telemetry.db` na worker thread, renderizando as cinco secções (custo, qualidade, módulos, provedores, ciclo) a partir dos mesmos renderizadores que a CLI usa.
* **Assistente de Migração (`src/ui/metrics_migration_app.py`):** oferece importar os ficheiros legacy, importar **e** reconstruir a partir da cache de IA, ou "agora não" — com semântica de aborto que **nunca cria um ledger vazio** sobre ficheiros ainda não importados.
* **Exportação Local:** CSV/JSON em `./.gitpr/metrics/export/` — `.gitpr/metrics/` está no `.gitignore`, mas os **34 artefactos já rastreados continuam rastreados** (o gitignore não desrastreia).

### **18. Sistema de Métricas e Telemetria (`src/metrics.py`)**

* **Reescrito na janela v0.0.17 — ver §36.** O `src/metrics.py` opera sobre **linhas do ledger**; o `enrich_metrics_from_cache()` e o `load_cache_token_summary()` foram removidos.
* **Âmbito por Repositório:** Todos os eventos indexados por `repo_name`, resolvido pelo `src/infrastructure/git/identity.py` — que funciona em **qualquer** forge.
* **Eventos de Hook, Linter e Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. 🆕 O `gitpr check` escreve `command="check"` com `findings_total`/`findings_errors` e o exit code.
* **Exportação e Limpeza:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` com confirmação interativa, `bundle`/`merge` para transportar e fundir janelas.

### **19. Sincronização de Idiomas dos Git Hooks**

* **Versionamento Independente:** `__scripts_version__` (**v0.0.4**, inalterado).
* **Templates reescritos na janela anterior:** o `post-checkout`, o `post-merge` e o `pre-push` chamam `gitpr --quiet metrics hook-event <name>`, protegidos por `command -v gitpr` e `|| true`.
* **Mapeamento de Sufixos (`HOOK_SCRIPT_SUFFIXES`):** Os códigos de interface (`es_es`, `fr_fr`) traduzidos para os sufixos publicados (`.es`, `.fr`).
* **Escolha vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detetar uma mudança de idioma**.
* **`effective_hook_lang()`:** Resolve o idioma efetivo; o `--lang` deixou de ser descartado nesse caminho.
* **Skip de Merge-Source:** O `prepare-commit-msg` salta as fontes `message|merge|squash|commit`.

### **20. Bridge de Linters Externos e Assistente Interativo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistente `--linter-setup`:** Assistente com presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e injeção do bloco `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` com uma cadeia local → descarregamento → stale → fallback embutido.
* **TUI de Erros do Linter:** O `src/ui/linter_app.py` (Textual) apresenta erros críticos e avisos; em modo hook/quiet imprime e faz `sys.exit(1)`.
* **Relatório Markdown:** Consolidado em `.gitpr/reports/linter/` apenas quando há violações.
* **A Camada SAST É Irmã, Não Substituta:** as bridges Semgrep/Gitleaks/Bandit alimentam o **mesmo** pipeline de relatório — e agora também o `gitpr check`.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstração Única (`ScmProvider` ABC):** O `base.py` define o contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. e `ScmProviderError(provider, http_status, message)` — `http_status` 0 = falha de rede).
* **Registo e Factory:** O `resolve_scm_provider()` seleciona por `GITPR_SCM_PROVIDER` (predefinição `github`); o `detect_provider_from_remote()` identifica a forge a partir do URL de origem.
* **`list_pull_requests(repo, state, since, until)` — paginado e canónico:** os quatro provedores traduzem os estados canónicos (`open`/`merged`/`closed`/`all`) para o vocabulário nativo, paginam no cursor de cada API e filtram por uma janela inclusiva de datas. O `list_open_pull_requests(repo)` é um invólucro fino na classe base.
* **Porque é que o filtro é por data do estado:** três das quatro forges devolvem algo mais amplo para um PR integrado (o GitHub não tem estado `merged`, o Azure chama-lhe `completed`, o Bitbucket diz `DECLINED`), por isso cada uma filtra a sua própria resposta contra a data que o estado implica.
* **`supports_merged_dates`:** atributo de classe, `False` no Bitbucket Cloud, que publica o `merged_at` como **vazio** — declarado antes da chamada.
* **`web_links.py`:** funções puras (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) normalizam qualquer formato de remote — `https`, `ssh://`, `git://`, scp-like, credenciais, porta personalizada e subgrupos aninhados do GitLab.
* **`get_pull_request(repo, pr_id)` — método concreto da ABC:** a predefinição lança `ScmNotSupportedError` e cada forge implementa-o.
* **Cabeçalhos do GitLab Sintetizados:** O `changes[].diff` é um **hunk solto**; o `old_path`/`new_path` montam os cabeçalhos `diff --git a/… / --- / +++`, honrando o `/dev/null`. E o `overflow: true` **lança** em vez de rever metade de um diff truncado.
* **`request_pull_request_reviewers` devolve `list[str]` (quebra de contrato):** Devolve os logins **efetivamente associados**, lidos do corpo do `201`. As subclasses de terceiros têm de ser atualizadas — **item herdado, ainda sem documentação.**
* **Helpers de Apenas Leitura do GitHub:** `get_commit_author_login` e `get_user_login`.
* **Fail-Fast por Forge:** O Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; o Bitbucket exige `GITPR_SCM_USERNAME`; o `create_issue` no Azure lança `ScmNotSupportedError`.
* **Publicação de Release:** O `provider.create_release()` usado pelo `gitpr release --publish`.
* **Publicação Partilhada de PR 🆕:** o contrato dos provedores não mudou; o que mudou foi que a publicação passa por **um único caso de uso** (`src/application/use_cases/publish_pull_request.py`), usado pelo `--no-edit` e pela tool MCP. Valida título/corpo, exige `provider.token`, verifica opcionalmente se já existe um PR e se a branch está sem push (`check_remote_state`), mascara as mensagens de erro e devolve `PublishPrOutcome`.
* **Artefactos:** Glossário + ADR-001/ADR-005; a família `docs/scm-multiforge.*.md` em 5 idiomas; 9 ficheiros de teste, **307 cenários**.

### **22. Subcomando `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Fluxo:** `git log` entre `--since` e `HEAD` → classificação por Conventional Commits → bump semântico sugerido (`--version <x.y.z>` sobrepõe-se) → montagem do changelog → resumo executivo por IA opcional → *anteposição* ao `CHANGELOG.md`.
* **O Intervalo Deixou de Vir do `git describe`:** a origem do intervalo é a **secção anterior do próprio changelog**, por meio de duas âncoras: o hash mais recente dessa secção e, como recurso, a tag da versão da secção; sem nenhuma das duas, degrada para a última tag **com um aviso visível**.
* **`_drop_released()` — a segunda linha de defesa:** qualquer commit cujo hash curto apareça noutra secção de versão é descartado, com contagem. Quando nada sobrevive, a execução **aborta** com `❌ Nothing new to release`.
* **Cada bullet tornou-se uma ligação:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **A falha dos PRs integrados por merge commit:** uma segunda passagem (`git log --merges`) constrói `hash → PR` a partir da ancestralidade; um merge que não é um pull request não empresta um número, e a correção **não é retroativa**.
* **Contribuidores resolvidos pela mesma escada dos revisores:** o `get_commit_author_login` primeiro, depois o `email_to_handle`, com cache em `~/.gitpr/cache/contributors.json`.
* **A Fronteira da Secção Era um Bug Latente:** O `upsert_changelog(force=True)` substitui da linha `## [x.y.z]` até ao próximo cabeçalho de **nível 2**; um `###` como fronteira deixava o corpo antigo vivo acima do novo.
* **Classificador (`src/commit_classifier.py`)** e **Builder com Secções Traduzíveis (`src/changelog_builder.py`)** — o builder tem a dataclass `LinkContext`, e o classificador tem o `pr_number_from_merge()`.
* **Bump Semântico (`src/version_bump.py`)** e **Publicação** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opções no subcomando.**
* **Quebras declaradas:** O `ReleaseNotesResult` ganhou `previous_version`; o `build_release_section` ganhou `links` (retrocompatível).
* **Estado da release 1.4.1 🆕:** a secção `[1.4.1] - 2026-10-18` foi gerada pelo motor corrigido e está **na árvore de trabalho**; classifica cinco commits (`8557125`, `b6382b5`, `a50f21c`, `525a1ec`, `46c25c1`) e continua a arrastar `fc9d400`/`1abb1b5` como documentação. O artefacto PT-BR correspondente está em `.gitpr/reports/release/` (por rastrear). Falta o commit e a tag.
* **Artefactos:** `docs/release-notes.*.md` em 5 idiomas, ADR e `tests/test_web_links.py`.

### **23. Subcomando `gitpr config` — TUI de Configuração**

* **Ecrã Master-Detail (`src/ui/config_app.py`):** Categorias à esquerda, campos à direita, editados inline. Cabeçalho com procura (`/`) e um contador de alterações pendentes; rodapé `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schema Declarativo (`src/config_schema.py`) — a única fonte de verdade:** **16 categorias** e **80 `ConfigField`**, dos quais **8 avançados**. Cada campo declara categoria, tipo de widget, `show_if`, validadores, marcadores de versão e ações de descarregamento. Nenhuma categoria nova nesta janela — as 9 `GITPR_CHECK_*` ainda **não** estão no schema.
* **Categorias `policy` e `baseline`:** os campos do pack (ativação e teto de caracteres) e os quatro campos do baseline, incluindo o caminho do ficheiro e o interruptor que exige o checksum do lockfile.
* **Secção Skills Alinhada:** O `SKILL_LABELS` tem **11 entradas** (`tests`, `explain`, `mentor`).
* **Filtragem por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` conforme o `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` sob `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` sob `[Azure DevOps]`.
* **Procura Global (`/`):** Encontra correspondências na chave ou no rótulo em todas as categorias e **ignora o filtro de visibilidade**.
* **Validação em Duas Camadas:** **offline** bloqueia o `F2` com um erro inline; **online** (apenas credenciais alteradas na sessão) corre num worker com timeout de 10s e só bloqueia em `401`/`403`.
* **Restaurar (`Ctrl+R`):** Remove a linha do `.env` em vez de reescrever a predefinição; o `Esc` com alterações pendentes pede confirmação.
* **Secção Skills — a única com âmbito de projeto:** Edita os `.gitpr/skill/*.md` do projeto resolvidos a partir do diretório de chamada, escrevendo atomicamente e preservando CRLF/LF.
* **Descarregamentos Forçados:** Botões que forçam o novo descarregamento dos smart-excludes, das traduções, dos presets e das thinking words via `force=`.
* **Módulo de Ligações Leve (`src/doc_links.py`):** O `doc_url()` saiu do `core.py` para que a UI possa obter a ligação sem importar os SDKs de IA.
* **Testes:** 5 ficheiros — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Dívida conhecida:** O `gitpr -h config` abre a TUI e ignora o `-h` — o portão `if ctx.invoked_subcommand is not None: return` (main.py:678) corre antes do bloco do `help_flag`.

### **24. Registo Geral de Utilização (`src/usage_log.py`)**

* **Uma Linha por Comando:** Escreve em `~/.gitpr/logs/<uuid5>.log`, **um ficheiro por dia**, com o comando, os argumentos, o repositório, o utilizador e o timestamp.
* **Nome Derivado da Data:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` — dois processos concorrentes não podem discordar sobre qual é o ficheiro de hoje.
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
* **A Mesma Escada Serve a Release:** O `release_engine` reutiliza o `get_commit_author_login` + o `email_to_handle` para transformar e-mails de commit em logins clicáveis.
* **Testes:** `tests/test_reviewer_resolution.py` (18) + ampliações em `test_github_provider.py` (72), `test_pr_publish_app.py` (55), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) e `test_main_suggest_reviewers.py` (8).

### **28. Subcomando `gitpr demo` — Visita Guiada sobre Exemplos Gravados (`src/demo/`)**

* **O que é:** Uma visita interativa que mostra as três saídas principais do GitPR — mensagem de commit, review e descrição de PR — **sem chave de API, sem repositório Git e sem rede**.
* **Pipeline Real, Resposta Falsa:** O `FakeAIProvider` espelha o `src.ai_providers.call_ai_model` **argumento a argumento**; o `demo_pipeline()` faz patch do `src.core` e do `src.metrics` para que o pipeline de produção corra **intocado** e apenas a origem da resposta seja trocada.
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
* ⚠️ **Impacto do refactor de publicação 🆕:** o `_publish_pr_directly` passa a delegar no caso de uso partilhado, que devolve `auth_required` quando `provider.token` está vazio. O provedor falso em `tests/badge/test_badge_paths.py` não declara `token`, pelo que o `create_pull_request` nunca é chamado e **7 cenários falham** com `KeyError: 'request'`. O comportamento do produto está correto (o provedor real transporta sempre o token); é a fixture que precisa de ser atualizada.
* **Artefactos:** `docs/badge.*.md` em 5 idiomas e o questionário dedicado.

### **30. Subcomando `gitpr split` — Commits Atómicos por Hunk (`src/split/`)**

* **O que é:** Uma árvore de trabalho com várias preocupações deixa de se tornar um commit-blob. O `split` lê o diff não consolidado, pede à IA que particione os hunks por **intenção lógica** e propõe um commit atómico por preocupação. A árvore nunca é reescrita: os ficheiros terminam byte a byte iguais ao início.
* **Pacote de 6 Ficheiros:** `split_plan.py`, `hunk_parser.py` (puro), `hunk_grouper.py`, `generate_split_plan.py` (apenas de leitura), `apply_split_plan.py` (**o único que muta**) e `__init__.py`.
* **A Garantia de Árvore Intacta É Estrutural:** O `selective_stager.py` pré-valida **todos** os grupos contra um índice temporário no HEAD e usa um `GIT_INDEX_FILE` descartável.
* **Destrutivo para o Índice, Não para a Árvore:** O `--apply` reinicia o índice para o HEAD antes de indexar, pelo que o que o utilizador já tinha indexado fica por indexar — documentado explicitamente.
* **Configuração:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) e `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Valores zero ou negativos são **ignorados a favor da predefinição**.
* **Testes:** 6 ficheiros em `tests/split/` (**101 cenários**).
* **Artefactos:** `docs/split-command.md` em 5 idiomas, ADR-006, `glossary-gitpr-split.md`, spec/plano e questionário.

### **31. Varredura de Segredos Embutida (`src/security_ruleset.py`)**

* **O que é:** Sete regras que correm em **todas** as invocações do linter, fundidas no fim do `load_linter_rules()` depois das regras do projeto e dos plugins.
* **As Sete Regras:** cinco `error` bloqueantes — ID de chave de acesso da AWS, token do GitHub, token do Slack, chave de API do Google e bloco de chave privada — e dois `warning`: URL de ligação a base de dados com credenciais e atribuição genérica de credencial (`password = "…"`), esta última atrás de um filtro de placeholder (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Porque no Pacote e Não no Template:** Um portão de segredos tem de se comportar **igual** em todas as máquinas e em todos os CI, por isso as regras vivem no pacote e não podem ser substituídas por `--skill` nem reescritas pelo wizard.
* **Lacunas de Cobertura (v1) — confirmadas como ainda abertas:** a atribuição **sem aspas** não é apanhada (`API_KEY=abc123`, o formato `.env`); faltam os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-`; e não há fronteira à esquerda no nome da chave. O mascaramento do CI cobre `github_pat_`, mas não `ASIA…` nem `xoxc-`/`xoxd-`; o linter não cobre nenhum dos três.
* **Configuração:** `GITPR_LINTER_SECURITY` (predefinição `true`; opt-out fail-open) e `GITPR_LINTER_SECURITY_DISABLED_RULES` (separadas por `;`).
* **Testes:** `tests/test_security_ruleset.py` (**61 cenários**).
* **Artefactos:** ADR-007, `glossary-gitpr-secret-scanning.md`, spec/plano/questionário e 3 relatórios de tarefa.

### **32. Bridges SAST — Semgrep, Gitleaks e Bandit (`src/infrastructure/linter/external/`)**

* **O que é:** Uma camada **opt-in** que liga scanners de segurança de terceiros ao pipeline de linter existente.
* **Âmbito Delimitado:** Correm **apenas** quando ativados e **apenas** sobre os ficheiros tocados pelo diff.
* **Contrato Comum:** `ExternalLinterBridge` (ABC) com execução endurecida de `subprocess` (`shell=False`, timeout estrito, `stdin=DEVNULL`, UTF-8 com `errors='replace'`). O modelo `NormalizedFinding` vive em `src/domain/finding/`, e o `base_bridge` re-exporta-o.
* **A Deduplicação É o Ponto:** O `deduplicate_secret_findings()` funde apontamentos do Gitleaks e do conjunto de regras regex no mesmo ficheiro e linha numa **única** entrada confirmada como `[Gitleaks + Regex]`.
* **Segredos Mascarados:** O `mask_secret_value()` garante que o valor nunca chega ao apontamento, ao log ou à telemetria.
* **Resolução em Três Camadas:** O `load_sast_config()` lê as predefinições → `.gitpr.linter.yml` (bloco `sast`, com fallback para `linter.external`) → variáveis `GITPR_SAST_*`. **Todas com predefinição `false`**.
* **Degradação Graciosa:** Uma ferramenta ativada mas ausente do `PATH` emite um aviso e a execução continua.
* **Testes:** 4 ficheiros em `tests/infrastructure/linter/external/` (**14 cenários**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + `tests/domain/linter/test_structured_findings.py` (44).

### **33. Subcomando `gitpr tests generate` — Geração de Suíte por IA (`src/domain/tests_generation/`, `src/application/`)**

* **O que é:** Gera ficheiros de teste completos e executáveis a partir do diff atual, de um ficheiro específico ou de um apontamento de review, **respeitando a convenção do repositório** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Camada de Domínio:** `TestFramework` (enum) e as dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest`; o `detect_test_framework()` deteta pelos ficheiros de configuração, manifests e conteúdo do diretório de testes; o `build_test_scaffold()` calcula o caminho convencional de destino por framework.
* **Camada de Aplicação (`generate_test_file.py`):** Orquestra a deteção, a resolução do scaffold, o prompt, a IA, o parsing do JSON e a escrita opcional. O `validate_test_syntax()` corre o toolchain local (`php -l`, `node --check`, `python -m py_compile`) — uma falha de validação é um **aviso**, não um erro.
* **Apresentação:** Grupo `tests` com `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); o dry-run é a predefinição e sobrescrever um teste existente pede confirmação com **No** pré-selecionado. O chat delega o `/tests` ao **mesmo** caso de uso.
* **Dívida:** o tipo `tests` está em `SKILL_LABELS` e em `mcp_server.SKILL_FILES`, e as chaves `__()` foram traduzidas. **Continua sem tópico em `docs/`** — só o template de skill e a spec.

### **34. Subcomando `gitpr explain` e a Flag `--explain` — Guia do Revisor (`src/domain/pr/`)**

* **O que é:** Um guia centrado em quem vai **rever** — o que muda, porque muda, onde focar e qual o risco de regressão.
* **Camada de Domínio (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, o renderizador `build_explain_markdown()` e o `parse_explain_payload()`, que tolera saída não-JSON e **deteta placeholders** `[FILL]`/`[TODO]` para sinalizar evidência insuficiente.
* **Camada de Aplicação (`generate_pr_explanation.py`):** Resolução do provedor, validação da chave, carregamento do skill context (`explain`), prompt, invocação e parsing.
* **Duas Portas de Entrada:** `gitpr explain` (`--provider`, deteção de diff em falta antes de qualquer IA) e a flag `--explain` na CLI raiz, que anexa o guia ao corpo da descrição do PR **e** ao payload JSON. 🆕 A extensão expõe o mesmo caminho em `GitPR: Generate PR Draft (with Reviewer Guide)`.
* **Configuração:** `GITPR_EXPLAIN_BY_DEFAULT` (predefinição `false`).
* **Dívida:** o `explain` está em `SKILL_LABELS` e em `mcp_server.SKILL_FILES`, e as chaves foram traduzidas. **Continua sem tópico em `docs/`.**

### **35. Suíte Determinística e CI (`.github/workflows/tests.yml`)**

* **O que é:** O workflow que corre a suíte em **Python 3.10** (o piso declarado no `pyproject.toml`) e **3.13**, com `fail-fast: false`.
* **`tests/conftest.py` Hermético:** Fixa `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` e o `LANG_VERSION` na versão do código, além de `GITPR_SHOW_LOGS=false` e `GITPR_SKIP_UPDATE_CHECK=true`.
* **A Ordem Importa:** O `src.updater` é importado **antes** de as variáveis de idioma serem definidas, porque o `i18n` captura o `os.environ` em `AMBIENT_ENV_KEYS` no import.
* **Estabilidade dos Testes de Worker:** O `test_config_app.py` e o `test_metrics.py` aguardam por `workers.wait_for_complete()` antes de afirmar.
* **Dependência do Workflow:** O runner limpo não tem `~/.gitpr`, e o `test_demo_isolation.py` afirma que o perfil existe — o job cria o diretório e o `.env` vazio explicitamente.
* **Nesta janela a suíte cresceu 153 cenários e a dívida de testes mudou de lado 🆕:** entraram 9 módulos novos (check, infraestrutura de CI, templates e skill), mas apareceram **10 falhas novas** — 7 de fixture no selo e 3 de i18n — mais as 2 antigas do mentor. O balanço é **2938 passados de 2952**.
* **Workflow da Extensão 🆕:** O `.github/workflows/extension.yml` corre `npm install`, build, testes e empacotamento do VSIX; em tags `ext-v*` publica no VS Code Marketplace e no Open VSX, saltando cada passo quando `VSCE_PAT`/`OVSX_PAT` está vazio.

### **36. Ledger SQLite de Telemetria (`src/ledger.py`, `src/infrastructure/git/identity.py`)**

* **O Problema Não Era o Formato, Era o Join:** os eventos eram escritos como ficheiros JSON por comando, a partir de uma **thread daemon**, e depois reconectados à cache de IA por uma correspondência heurística de `(repo, branch, action, minute)` que dependia de **três mapas comando→pasta divergentes**.
* **A Escrita É Síncrona, e Essa É a Decisão:** O `log_command_metric()` resolve o contexto git **uma vez por processo** e escreve a linha de imediato. Os tokens, o modelo e o provedor são **copiados dos metadados do provedor no momento da escrita**.
* **Schema Versionado:** `SCHEMA_VERSION` + `PRAGMA user_version`, chaves UUID, WAL + `synchronous=FULL`, e `LedgerVersionError` para recusar um bundle mais recente **antes** do `ATTACH`.
* **Invariante "Nunca Nasce Vazio":** O `ensure_ledger()` só cria a base de dados **em conjunto** com a absorção do JSON legado (originais movidos para `~/.gitpr/metrics_legacy/`).
* **Uma Janela, Um Significado:** `--since`/`--until`/`--days` estreitam **todas** as superfícies de leitura; a secção de ciclo assume os últimos 30 dias e **nomeia a janela no seu próprio cabeçalho**.
* **Cada Métrica É Calculada Uma Vez e Renderizada Uma Vez:** os pares `(style, text)` fazem a CLI e o dashboard Textual contar a **mesma** história.
* **Custo com Tabela Embutida e Override:** preços de lista dos quatro modelos, sobrepostos por `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Ambas as taxas são obrigatórias**; `ollama`/`local` custa zero; um `GITPR_METRICS_CURRENCY` não-USD **descarta** a tabela embutida; um total parcial lê `Total (partial)`.
* **`modules` Normalizado:** os caminhos tornam-se dois segmentos (`src/fix`), `(root)` e `(no module)` para comandos sem diff.
* **Transporte por Bundle:** O `bundle` escreve a janela como um `.db` autónomo; o `merge` anexa-o, insere por UUID, migra um bundle antigo e recusa um mais recente.
* **`cache_backfill` Fica de Fora:** as linhas reconstruídas a partir da cache são excluídas dos agregados de execução.
* **Identidade Git sem Forge (`identity.py`):** rótulo de repo, branch e autor numa **única** chamada de subprocesso, válido em qualquer forge.
* **A Secção de Ciclo É a Única que o Ledger Não Responde:** lista PRs integrados via `ScmProvider.list_pull_requests`; todas as falhas degradam para uma linha — `Not read: <reason>` — nunca um stack trace.
* **Apresentação Completa:** grupo `gitpr metrics` com 8 subcomandos, um dashboard que lê o ledger numa worker thread e o assistente de migração de três opções.
* **Configuração:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY` e `.gitpr/metrics/` no `.gitignore`.
* **Testes:** O `tests/test_metrics.py` (**164 cenários**); o `tests/scm/*` cobre a paginação e o `within_window`; o `test_commit_classifier.py` (30) cobre o `pr_number_from_merge`.
* **Artefactos:** ADR-008, `glossary-metrics-telemetry.md`, `docs/plans/develop_natan/20260927_metrics_audit_plansfacts.md`, questionário e `docs/metricas-telemetria.*.md` nos 5 idiomas.

### **37. Subcomando `gitpr mentor` e a Flag `--mentor` — Modo Mentor (`src/domain/mentor/`)**

* **O que é:** Um review diz *o que* está errado e quase nunca *porque é que isso importa*. O Modo Mentor enriquece os apontamentos com **o que se está a passar**, **porque importa**, uma **analogia** do dia a dia e os **conceitos a estudar**.
* **Camada de Domínio Pura (`mentor_explanation_builder.py`):** sem I/O, converte o payload da IA num `MentorExplanation` validado e impõe **honestidade epistémica**: sem evidência suficiente, a explicação **diz isso**; os URLs alucinados são **removidos**; os IDs desconhecidos são **rejeitados**, preservando a ordem dos apontamentos originais.
* **Camada de Aplicação (`generate_mentor_explanation.py`):** recolhe candidatos, **lotes de no máximo 10 apontamentos**, ordenados por severidade, reutilizando a cache MD5 por hash de prompt.
* **Duas Portas de Entrada:** `gitpr mentor [--finding <id>] [--provider <name>]` corre sobre o **último review persistido**, e a flag `--mentor` anexa a secção ao review normal. 🆕 A extensão expõe `GitPR: Review (Junior Mentor Mode)` e o MCP ganhou `mentor: bool` em `review_code`/`full_review`.
* **Opt-In Estrito:** `GITPR_REVIEW_MENTOR_MODE` (predefinição `false`) e `GITPR_MENTOR_INCLUDE_ANALOGY` (predefinição `true`), editáveis na TUI de configuração.
* **Skill e Registo:** O `.gitpr.mentor.md` registado em `SKILL_FILES_BY_TYPE`, em `mcp_server.SKILL_FILES` e em `SKILL_LABELS`, com templates em **5 idiomas**.
* **Testes:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) e `tests/test_mentor_cli.py` (4) — **2 destes falham** devido ao bug de isolamento herdado (ver §Testes).
* **Artefactos:** `docs/mentor-mode.*.md` em **5 idiomas**, ADR-009, `glossary-gitpr-mentor.md`, spec e relatórios de tarefa.

### **38. Subcomando `gitpr risk` — Pontuação de Risco Determinística (`src/domain/risk/`)**

* **O que é:** Uma pontuação de **0–100** com nível `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculada **sem IA** a partir de sinais locais, para que o revisor saiba onde olhar primeiro — e para que o mesmo diff receba o mesmo número duas vezes.
* **Camada de Domínio Pura (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums e dataclasses (`RiskLevel`, `RiskSignal`, `RiskEvidence`, `FileRisk`, `PullRequestRisk`); pesos, limiares, padrões de caminhos críticos, correspondência por glob e deteção de teste/não-executável carregados de YAML; cálculo por ficheiro com **clamp 0–100** e agregação 50/30/20.
* **A Fórmula É Declarada, Não Aprendida:** **50%** o ficheiro de maior risco, **30%** a média ponderada por linhas e **20%** evidência crítica. Regras de elevação obrigatória: pontuação ≥ 80 sobe para pelo menos `HIGH`; um bloqueador ou pontuação ≥ 90 força `CRITICAL`.
* **Adaptadores de Infraestrutura:** O `risk_history_reader.py` lê o `git log` e extrai commits, correções de bugs e reverts numa janela de **90 dias**; o `test_matcher.py` corresponde ficheiros de teste ↔ ficheiros de produção por convenção.
* **Caso de Uso (`calculate_risk.py`):** correlaciona as secções do diff, os apontamentos, a correspondência de testes e o histórico em `FileRisk`/`PullRequestRisk`, **degradando com um aviso** quando um sinal não está disponível. Aplica o policy pack por cima da configuração base.
* **Três Consumidores:** o comando `gitpr risk`, a secção `⚡ Risk Assessment` anexada aos reviews e ao review de PR remoto, e o **prompt da IA** como contexto **read-only**. 🆕 O `gitpr check` usa o mesmo `execute_calculate_risk` para a pontuação preliminar e a agregada.
* **Opt-Out:** `GITPR_RISK_INCLUDE_IN_REVIEW` (predefinição `true`).
* **Configuração por Projeto:** O `templates/gitpr.risk.yml` (ou `.gitpr/skill/gitpr.risk.yml`) sobrepõe pesos, limiares e caminhos críticos. Pesos negativos só são aceites para sinais **mitigadores**.
* **Testes:** `tests/domain/risk/` (2 ficheiros, 10), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) e `tests/test_risk_cli.py` (4).
* **Artefactos:** `docs/risk-scoring.*.md` em **5 idiomas**, ADR-010, `glossary-gitpr-risk-scoring.md` e relatórios de tarefa.

### **39. Subcomando `gitpr policy` — Policy Packs Versionados (`src/domain/policy/`, `src/infrastructure/policy/`)**

* **O que é:** Um **pack YAML versionado** que transporta, num único artefacto, as decisões de qualidade de um repositório: skills, regras de linter, severidades, caminhos críticos, pesos de risco e convenções de PR/commit.
* **Camada de Domínio (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`):** parsing do manifest com um **schema fechado** (uma chave desconhecida é um erro), verificação de compatibilidade e de credenciais, **checksum SHA-256** e um resolvedor de grafo com precedência e composição.
* **Camada de Infraestrutura:** O `local_policy_repository.py` resolve o pack a partir de **três origens** (`.gitpr/policies`, `~/.gitpr/policies` e o pacote embutido) e o `policy_pack_loader.py` carrega-o.
* **Casos de Uso:** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` e `validate_policy_pack`.
* **Quatro Packs Embutidos:** `laravel-quality`, `node-quality`, `php-security` e `vue-quality`, cada um com `policy.yml` e `linter.yml`, distribuídos via **`package-data`**.
* **Consumo Integrado em Quatro Pontos:** `calculate_risk`, `config.load_linter_rules` (fusão em camadas + sobreposição de severidade), `core.get_skill_context` e a **chave de cache do PR** (`cache_scope`). 🆕 O `gitpr check` resolve o pack **antes de tudo o resto** e sai com código 2 se ele for inválido.
* **CLI:** `gitpr policy {list, validate, show, use, off, install, init}`, com confirmação (`--yes`/`--force`), deteção de stack no `init` e a escolha registada em `.gitpr/policy.lock.yml`.
* **Configuração:** `GITPR_POLICY_ENABLED` e `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`, ambos na categoria `policy` do schema.
* **Retrocompatibilidade:** sem pack ativo, o comportamento é **preservado** — coberto por um teste de integração.
* **Dependência:** `packaging>=23.0` (SemVer/PEP 440), declarada no `Pipfile` e no `pyproject.toml`.
* **Testes:** `tests/domain/policy/` (4 ficheiros, 126), `tests/application/use_cases/` (4 ficheiros de policy, 89), `tests/test_policy_cli.py` (26) e `tests/integration/test_policy_effect_on_review_linter_risk.py` (31).
* **Artefactos:** `docs/policy-packs.*.md` em **5 idiomas**, ADR-011, `glossary-policy-packs.md`, spec/plano/questionário.

### **40. Subcomando `gitpr baseline` — Baseline e Supressões Auditáveis (`src/domain/baseline/`)**

* **O que é:** Os linters e os reviews voltam a reportar os mesmos apontamentos pré-existentes a cada execução. O baseline regista o **instantâneo dos apontamentos que o repositório já conhece** em `.gitpr/baseline.json`, cria a impressão digital de cada um de forma estável entre execuções e deixa o pipeline classificar o que é novo — **só o `new` bloqueia**.
* **Camada de Domínio (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`):**
  * **Impressão digital estável:** identidade da regra, caminho normalizado, linhas e **sha256 da linha com o whitespace colapsado** — a linha em bruto **nunca** é armazenada, por isso o baseline não é um ficheiro onde segredos vazam por acidente.
  * **Manifest canónico:** JSON canónico com checksum, validação que recusa uma chave desconhecida, uma versão de schema/impressão digital não suportada, um invariante de supressão/dívida quebrado, uma impressão digital duplicada e um intervalo de linhas inválido.
  * **Comparador:** classificação, contagens por estado e resolução de entradas — `resolved` e depois `back` lê-se como **novo**, a leitura conservadora.
  * **Política de Supressão:** supressões, dívida aceite, sobreposições e validadores, com correspondência em camadas (entrada × sobreposições × policy pack) por **especificidade e precedência**, mais o acompanhamento da dívida vencida.
* **Cinco Casos de Uso:** `baseline_gate` (o ponto de entrada único do pipeline), `baseline_summary` (vista JSON apenas de leitura para IDEs e agentes MCP), `compare_against_baseline`, `create_baseline` (criação/atualização com proveniência, checksums e **preservação das decisões humanas**) e `suppress_finding` (quatro âmbitos).
* **Integração com o Policy Pack:** o pack pode transportar um bloco `baseline:`, validado pelos **mesmos** validadores, composto pelo resolvedor como uma camada etiquetada e reportado pelo `policy validate`.
* **O Repositório É o Único que Escreve:** O `local_baseline_repository.py` é o **único** leitor/escritor de `.gitpr/baseline.json` e `.gitpr/baseline.overrides.yml`, com escrita atómica, um portão de checksum/versão e um caminho de reparação.
* **Configuração:** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` e `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`, na categoria `baseline`.
* **Opt-In e No-Op:** desligado ou sem baseline existente, a funcionalidade **não lê nenhum ficheiro e não imprime nada**.
* **⚠️ A Exceção Deliberada:** um ficheiro de baseline que existe mas **não pode ser aplicado** faz o lint, o review, o full review, a auditoria de ficheiro, o review remoto e o risk **abortarem com exit code 1**. 🆕 No `gitpr check`, um baseline corrompido sai com **código 2**, distinguindo um erro de execução de um apontamento bloqueante.
* **Alcance:** CLI (6 subcomandos, todos com `--format json`), MCP (`get_baseline_status` + `baseline://summary`), review de PR remoto (resolvido **antes da primeira chamada de rede**) e o fluxo de lint.
* **O Refactor que a Funcionalidade Exigiu:** O `NormalizedFinding` tornou-se um tipo canónico em `src/domain/finding/` (com `snippet_hash` e `rule_version`).
* **Testes:** `tests/domain/baseline/` (5 ficheiros, **126**), `tests/application/use_cases/` (5 ficheiros, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) e `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34).
* **Artefactos:** `docs/baseline-suppressions.*.md` em **5 idiomas**, ADR-012, `glossary-baseline.md`, spec/plano/questionário e relatório de tarefa.

### **41. Subcomando `gitpr check` — Portão de CI Unificado (`src/domain/check/`, `src/infrastructure/ci/`) 🆕**

* **O que é:** Um **ponto de entrada único** para pipelines de CI (GitHub Actions, GitLab CI, Azure Pipelines, Jenkins) que orquestra a análise estática, as bridges SAST, a varredura de segredos, o review por IA opcional, a pontuação de risco e a classificação do baseline, e emite **um artefacto padronizado** com um exit code determinístico.
* **A Ordem do Pipeline É Contrato:** (1) resolução da policy — inválida sai com `2`; (2) extração do diff com smart excludes; (3) linter regex + segredos + bridges externas; (4) pontuação de risco preliminar; (5) **review por IA opcional** (`--no-ia` desliga-o; sem chave configurada degrada com um aviso; uma falha em curso sai com `2`); (6) classificação do baseline (`new`/`existing`/`ignored`/`accepted_debt`/`resolved`; um baseline corrompido sai com `2`); (7) avaliação do limiar `--fail-on`; (8) geração do artefacto com **mascaramento de segredos**.
* **Camada de Domínio (`src/domain/check/`):** `check_types.py` (severidade normalizada em três níveis — `error`/`warning`/`info` — com aliases da spec: `minor → warning`, `major|critical|blocker → error`, além de `CheckFinding`, `CheckResult`, `CheckSummary`); `check_config.py` (precedência **CLI > env > predefinições**, com `GITPR_CHECK_*`); `check_output.py` (dispatch de formato e escrita atómica).
* **Camada de Infraestrutura (`src/infrastructure/ci/`):** `sarif_builder.py` (SARIF 2.1.0 compatível com o GitHub Code Scanning, com `tool.driver.rules` geradas dinamicamente); `junit_builder.py` (testsuites com `<failure>` para apontamentos bloqueantes e `<system-out>` para info/warning); `masking.py` (mascaramento antes de serializar — chaves privadas, AWS, GitHub, Google, OpenAI, Slack, Bearer, basic-auth e atribuições genéricas).
* **Caso de Uso (`run_check.py`):** `resolve_diff_base()` (explícito → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), execução do linter e das bridges, pontuação de risco, review opcional, baseline e o envelope final. O stdout emite **apenas o artefacto**; avisos e logs vão para o stderr.
* **Exit Codes:** `0` sucesso; `1` apontamento contável no limiar; `2` erro de execução (configuração inválida, policy em falta, baseline corrompido, falha de IA em curso, timeout de 300 s).
* **CLI:** `gitpr check` com 8 opções e 9 variáveis `GITPR_CHECK_*` (fora do `DEFAULT_CONFIG`). A predefinição do comando é SARIF para o stdout; o `--output` escreve num ficheiro.
* **GitHub Action Reescrita:** O `action.yml` passou a ser um único passo `gitpr check`, com os inputs `provider`, `api-key` (agora **opcional**), `format`, `output`, `fail-on`, `no-ia`, `baseline` e `policy`. **Quebra declarada:** os workflows que dependiam do passo antigo (`--linter` + `--fullreview`) e do artefacto `*_PR_FULLREVIEW.txt` têm de migrar.
* **Testes:** **37 cenários** — `tests/application/use_cases/test_run_check.py` (8), `tests/domain/check/test_check_types.py` (8), `tests/domain/check/test_check_config.py` (3), `tests/domain/check/test_check_output.py` (3), `tests/infrastructure/ci/test_masking.py` (6), `tests/infrastructure/ci/test_sarif_builder.py` (2), `tests/infrastructure/ci/test_junit_builder.py` (2) e `tests/test_check_cli.py` (5).
* **MCP:** a tool `run_check` devolve o envelope JSON do mesmo pipeline, com `no_ia` predefinido a `true` para manter a verificação no editor barata.
* **Artefactos:** `docs/check-ci.*.md` em **5 idiomas**, ADR-013, `glossary-check-ci.md`, spec, `plansfacts` e questionário.

### **42. Extensão de Editor — VS Code e Cursor (`extensions/vscode/`) 🆕**

* **O que é:** Uma extensão **cliente fino** que leva os fluxos do GitPR ao editor sem reimplementar nada: arranca o `gitpr-mcp` via stdio e chama as tools existentes. Chaves, tokens, prompts e cache ficam em `~/.gitpr/`, encriptados com Fernet — a extensão **nunca pede nem armazena um segredo**.
* **Arquitetura:** `GitPrMcpClient` sobre `@modelcontextprotocol/sdk` (JSON-RPC delimitado por linhas, `cwd` = raiz do workspace, reconexão automática) + `SessionManager` com **uma sessão por pasta** (multi-root) + verificação do contrato ao ligar: as 7 tools obrigatórias (`get_git_context`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_check`, `publish_pull_request`) têm de existir; caso contrário, `MissingMcpToolsError` instrui a correr `pip install --upgrade gitpr-cli`.
* **8 Comandos:** `gitpr.commit`, `gitpr.review`, `gitpr.fullReview`, `gitpr.reviewMentor`, `gitpr.generatePr`, `gitpr.generatePrExplain`, `gitpr.publishPr` e `gitpr.check` — os quatro principais (Commit, Review, PR, Check) estão fixados na **barra de estado**.
* **Publicação em Dois Passos:** o `generatePr` abre um documento Markdown editável (linha 1 = título, o resto = corpo); o `publishPr` pede confirmação modal e trata `success`, `needs_push` (oferece **Push & Publish**), `already_exists` (abre o PR existente), `auth_required` (abre um terminal com o `gitpr`) e `error`.
* **Diagnósticos no Painel Problems:** o `run_check` é convertido numa `DiagnosticCollection("gitpr")`, com linhas 1-based → 0-based e filtragem das entradas `ignored`/`resolved` do baseline.
* **Mascaramento em Profundidade Dupla:** o `masking.ts` espelha o `src/infrastructure/ci/masking.py` e cobre as notificações, o OutputChannel e os payloads das tools.
* **Localização da Extensão:** bundles `package.nls*.json` e `l10n/bundle.l10n*.json` em **en, es, fr e pt-br** (4 idiomas) — a UI do GitPR mantém-se nos 5.
* **CI/CD da Extensão:** O `.github/workflows/extension.yml` instala, faz build, testa (Mocha contra o servidor Python real) e empacota o VSIX; em tags `ext-v*` publica no VS Code Marketplace e no Open VSX, exigindo `VSCE_PAT`/`OVSX_PAT` (passo saltado quando o segredo está vazio).
* **O que a Extensão Não Faz:** não faz commit (apenas preenche a caixa do Source Control, com fallback para a área de transferência), não faz push e não publica sem confirmação.
* **Testes:** a sua própria suíte Mocha (7 cenários, incluindo stdio real) + `tests/test_mcp_server.py` expandido para **115 cenários** (`16 → 18` tools, `mentor` e `explain` cobertos).
* **Artefactos:** `docs/editor-extension.*.md` em **5 idiomas**, ADR-014, `glossary-editor-extension.md`, specs (2026-10-14 e 2026-10-15), `plansfacts` e o relatório do Gemini.

---

## **📊 Testes e Qualidade**

| Ficheiro de Teste | Cenários | Foco |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por intervalo de linhas num ficheiro |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidade, commits, duração |
| `tests/test_baseline_cli.py` | 54 | Grupo `baseline`: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Builder de changelog: secções, cabeçalhos traduzíveis, contribuidores, **ligações e datas** |
| `tests/test_chat_backend.py` | 19 | Memória do chat, persistência, comandos slash |
| `tests/test_check_cli.py` | 5 🆕 | CLI do `check`: opções, formatos, exit codes |
| `tests/test_commit_classifier.py` | 30 | Classificação por Conventional Commits + **`pr_number_from_merge()` nas 4 forges** |
| `tests/test_config_app.py` | 78 | TUI de configuração: montagem, troca de categorias, controlo de alterações pendentes, F2 bloqueado, Ctrl+R, procura, segredos |
| `tests/test_config_cli.py` | 9 | Registo do subcomando `config`, `-h`, import preguiçoso, stdout limpo |
| `tests/test_config_schema.py` | 42 | Cobertura do `DEFAULT_CONFIG`, ausência de duplicados, categorias/tipos, `advanced` só em Avançado, **secção skills** ✅ |
| `tests/test_config_store.py` | 22 | Ida e volta num `.env` temporário, comentários e ordem preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuração dos revisores sugeridos (chaves e predefinições) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, modelos, `validate_ai_key()` com um SDK simulado (401 vs. rede vs. ollama) |
| `tests/test_core.py` | 49 | Fluxos principais, git diff, geração de PR, temporização, staging, coautoria, idioma dos hooks |
| `tests/test_diff_parser.py` | 26 | Parser de diff por linhas/hunks + `summarize_patch()` e `split_patch_sections()` |
| `tests/test_explain_command.py` | 2 | CLI do `explain`: sucesso, chave em falta, diff vazio |
| `tests/test_external_linters.py` | 33 | Bridge do Checkstyle: parser de XML, subprocesso, referência cruzada com o diff, relatório |
| `tests/test_i18n.py` | 20 | Paridade entre idiomas, chaves em falta/órfãs, identidade — **3 falhas nesta janela** ❌ |
| `tests/test_install_wizard.py` | 3 | Assistente de instalação interativo |
| `tests/test_issue_engine.py` | 4 | Rascunho estruturado de issue |
| `tests/test_linter_metrics.py` | 4 | Métricas do linter: erros, avisos, duração |
| `tests/test_linter_presets.py` | 5 | Presets do linter: resolução e novo descarregamento forçado |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` na CLI e na ajuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Modelos de prompt do MCP e fallback de idioma |
| `tests/test_mcp_server.py` | 115 🆕 | Tools MCP (18), recursos (19), anotações, patching, CLI direta, offload, **contrato da extensão** |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real via subprocesso + stdio JSON-RPC |
| `tests/test_mentor_cli.py` | 4 | CLI do `mentor` e opt-in `--mentor` — **2 falhas conhecidas** ❌ |
| `tests/test_metrics.py` | 164 | **Ledger**: escrita síncrona, exportação idempotente, bundle/merge, versionamento, migração, renderizadores, custo, qualidade, módulos, provedores, ciclo |
| `tests/test_net_timeouts.py` | 12 | Timeouts de rede/IA — alinhados com a predefinição real de 180s |
| `tests/test_plugins.py` | 17 | Descoberta de plugins, fusão de regras do linter, prompts do MCP |
| `tests/test_policy_cli.py` | 26 | Grupo `policy`: list/show/validate/use/off/install/init, confirmação e lockfile |
| `tests/test_pr_publish_app.py` | 55 🆕 | TUI do PR Publisher: ecrãs, fluxos, revisores sugeridos, `NoticeScreen`, **modal de merge** |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de erro do linter: abortar, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save e carga JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opções, ajuda com epílogo documentado |
| `tests/test_release_engine.py` | 53 | **Corte ancorado no changelog, `_drop_released()`, ligações, mapa de PRs por merge commit**, publicação |
| `tests/test_reviewer_resolution.py` | 18 | Escada de resolução, rejeição de um nome parcial, dedup, tolerância a falhas |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugestão de revisores (ranking, exclusão, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | CLI do `risk`: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 61 | Matriz do conjunto de regras embutido: regex, marcadores, nível, wildcard, fusão, traduções |
| `tests/test_skill_command.py` | 10 | Descarregamento e validação de modelos de skill |
| `tests/test_skill_context.py` | 20 🆕 | `get_skill_context()` com `quiet=True`, fallbacks, **guarda do artefacto de PR** |
| `tests/test_skill_templates.py` | 99 🆕 | Varredura parametrizada de **todos os templates publicados** contra referências embutidas |
| `tests/test_smart_excludes.py` | 15 | Filtro smart de pathspec e novo descarregamento forçado |
| `tests/test_suggest_reviewers.py` | 17 | Revisores sugeridos no fluxo de PR (integração, dica de `no_login`) |
| `tests/test_tests_command.py` | 2 | CLI do grupo `tests` e do `generate` |
| `tests/test_thinking_words.py` | 5 | Carregamento, análise com o separador `;` e recarregamento forçado |
| `tests/test_updater.py` | 23 | Portão do PyPI: análise de versões, cache diária, fetch, decisões do portão, ligação à CLI |
| `tests/test_usage_log.py` | 27 | Registo de utilização: nome derivado da data, escrita síncrona, silêncio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semântico: major/minor/patch, alvos e validação |
| `tests/test_web_links.py` | 26 | Normalização de remote (https/ssh/scp/credenciais/porta) e padrões de URL por forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 | Ativação do pack, lockfile, recusas |
| `tests/application/use_cases/test_baseline_gate.py` | 40 | Portão: carregamento, classificação, anotação, camadas e aborto |
| `tests/application/use_cases/test_baseline_summary.py` | 12 | Vista apenas de leitura do baseline para MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 | Correlação de diff/apontamentos/testes/histórico, degradação |
| `tests/application/use_cases/test_create_baseline.py` | 30 | Criação/atualização com proveniência e preservação das decisões |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 | Lotes, severidade, acerto na cache, chave em falta |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 2 | Caso de uso do explain |
| `tests/application/use_cases/test_generate_test_file.py` | 4 | Caso de uso de geração de testes |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 | Instalação do pack, origens e conflitos |
| `tests/application/use_cases/test_run_check.py` | 8 🆕 | Orquestração do check: ordem, degradação, exit codes |
| `tests/application/use_cases/test_suppress_finding.py` | 29 | Supressão/remoção nos quatro âmbitos |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 | Validação: schema fechado, checksum, compatibilidade |
| `tests/badge/test_badge_append.py` | 9 | Anexação idempotente do selo ao corpo do PR |
| `tests/badge/test_badge_builder.py` | 19 | Composição do Markdown do shields.io, escaping, regra de cor, estilo |
| `tests/badge/test_badge_cli.py` | 15 | Comando `gitpr badge`: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Contagem de alertas; `None` sem regras configuradas |
| `tests/badge/test_badge_offline.py` | 6 | Guarda offline (rede proibida, exceto loopback) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` em todos os caminhos de publicação |
| `tests/badge/test_badge_paths.py` | 10 | Ponto de injeção único nos dois publicadores (TUI e `--no-edit`) — **7 falhas nesta janela** ❌ |
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
| `tests/domain/check/test_check_config.py` | 3 🆕 | Precedência CLI > env > predefinições |
| `tests/domain/check/test_check_output.py` | 3 🆕 | Dispatch de formato e escrita atómica |
| `tests/domain/check/test_check_types.py` | 8 🆕 | Severidade em três níveis, aliases da spec, resumo |
| `tests/domain/finding/test_finding_types.py` | 3 | Tipo canónico de apontamento: `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Formatação uniforme e deduplicação `[Gitleaks + Regex]` |
| `tests/domain/linter/test_structured_findings.py` | 44 | `findings_out`: apontamentos estruturados e o contador de linhas do lado novo |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 | Builder puro: honestidade epistémica, URLs removidos, ordem preservada |
| `tests/domain/policy/test_policy_checksum.py` | 15 | SHA-256 do pack e deteção de edição manual |
| `tests/domain/policy/test_policy_compatibility.py` | 22 | Compatibilidade de versão e credenciais |
| `tests/domain/policy/test_policy_manifest.py` | 42 | Schema fechado, bloco `baseline:`, erros de manifest |
| `tests/domain/policy/test_policy_resolver.py` | 47 | Precedência, composição em camadas, pack ativo × base |
| `tests/domain/pr/test_explain_section_builder.py` | 4 | Renderização markdown, análise tolerante, deteção de `[FILL]` |
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
| `tests/infrastructure/ci/test_junit_builder.py` | 2 🆕 | JUnit XML: failure/system-out |
| `tests/infrastructure/ci/test_masking.py` | 6 🆕 | Mascaramento de credenciais antes de serializar |
| `tests/infrastructure/ci/test_sarif_builder.py` | 2 🆕 | SARIF 2.1.0 e `tool.driver.rules` |
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

**Total:** **2952 cenários recolhidos em 138 módulos de teste** (51 na raiz + 12 em `tests/application/use_cases/` + 10 em `tests/fix/` + 9 em `tests/scm/` + 7 em `tests/badge/` + 6 em `tests/demo/` + 6 em `tests/split/` + 5 em `tests/domain/baseline/` + 4 em `tests/domain/policy/` + 4 em `tests/review/` + 4 em `tests/infrastructure/linter/external/` + **3 em `tests/domain/check/`** 🆕 + **3 em `tests/infrastructure/ci/`** 🆕 + 2 em `tests/domain/risk/` + 2 em `tests/domain/tests_generation/` + 2 em `tests/integration/` + 2 em `tests/infrastructure/git/` + 2 em `tests/domain/linter/` + 1 em `tests/domain/finding/` + 1 em `tests/domain/mentor/` + 1 em `tests/domain/pr/` + 1 em `tests/infrastructure/baseline/`; **+153 cenários e +9 ficheiros** desde o relatório anterior).

**Notas de qualidade desta versão:**
- **Execução completa nesta máquina: `12 failed, 2938 passed, 2 skipped, 138 subtests passed in 723.90s (12min04s)`.** O número de cenários cresceu 153 (+5,5%), mas o número de falhas subiu de 2 para 12. Das 12, **10 são novas** e **2 são as conhecidas** de `tests/test_mentor_cli.py`.
- **As 7 novas falhas em `tests/badge/test_badge_paths.py` são um efeito direto do refactor de publicação (PR #212).** O `_publish_pr_directly` passou a chamar o `publish_pull_request_use_case`, que exige `provider.token`; o provedor falso do teste (`SimpleNamespace(name=..., create_pull_request=...)`) não tem o atributo, pelo que o caso de uso devolve `auth_required` (401) e o `create_pull_request` nunca corre — `captured["request"]` lança `KeyError`. O produto está correto (o provedor real transporta o token); **a fixture precisa de declarar `token="ghp_fake"`**, ou o caso de uso precisa de permitir saltar a verificação do token quando `check_remote_state=False` e a publicação é delegada a um provedor real.
- **As 3 falhas de i18n são dívida de tradução real, não instabilidade:** `test_key_parity_and_count` (es/fr com 1618), `test_no_missing_keys` (8 chaves usadas no código sem dicionário) e `test_no_orphan_keys` (2 chaves antigas). As 8 em falta são exatamente as mensagens do caso de uso de publicação de PR e as descrições das novas tools MCP (`run_check`, `publish_pull_request`).
- **As 2 falhas em `tests/test_mentor_cli.py` continuam a ser o mesmo bug de isolamento da janela anterior:** o `@patch("src.core.get_git_diff")` não chega à ligação importada por valor em `src/main.py`. Numa árvore cuja única alteração é um `.md` (filtrado por `:(exclude)*.md` nos smart excludes), o diff real volta vazio, o fluxo retorna antes do renderizador e a afirmação falha. Com o patch em `src.main.get_git_diff`, os dois cenários passam. É uma correção de duas linhas, ainda pendente.
- **Do lado positivo, a suíte ganhou 153 cenários:** 99 vêm da varredura parametrizada de templates (`test_skill_templates.py`), 37 do `check`, 10 do modal de merge, 5 do contrato MCP da extensão e 2 da guarda da skill de PR. O CI corre em Python 3.10 e 3.13 com `fail-fast: false`.
- **O `tests/conftest.py` continua hermético:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` e o `LANG_VERSION` na versão do código.
- **Guarda de rede em dois sítios:** `tests/demo/conftest.py` e `tests/integration/conftest.py`.
- **Fixtures de git reais:** o `tests/fix/git_fixture.py` e o `tests/split/git_fixture.py` continuam o padrão para o que só é honesto com um `git apply` real.
- **A suíte da extensão é separada:** 7 cenários Mocha em `extensions/vscode/src/test/mcp_client.test.ts`, dos quais 1 integra realmente com o servidor MCP Python via stdio; não conta para o pytest.

---

## **🌐 Internacionalização e Documentação**

* **Cobertura de i18n — a dívida reaberta:** **1620 chaves** em `pt_br.json`, `pt_pt.json`, `es_es.json` e `fr_fr.json`, mas **1618** em `es.json` e `fr.json`. O código usa **1626** chaves: **8 por traduzir** e **2 órfãs**. Na janela anterior a pontuação era 1603/1603 com zero em falta; esta janela acrescentou **+17 chaves** (check, modal de merge e extensão) e deixou 8 para trás. ❌
* **O `__lang_version__` subiu de v0.0.34 → v0.0.35** no commit `46c25c1`, desencadeando o novo descarregamento OTA das traduções.
* **Fontes de tradução em passo sincronizado:** uma chave nova tem de existir no código (fonte), em `langs/pt_br.json` (**lista mestra**), nos dicionários FR/ES do `scripts/sync_all_langs.py` e nos valores curados do `scripts/fix_mangled_i18n_keys.py`; a afirmação `len(CLEAN_KEYS)` mantém-se em 49.
* **Tópicos novos 🆕 (2, ambos em 5 idiomas):**
  - `docs/check-ci.md` — o comando de CI unificado: a ordem do pipeline, os três formatos, os exit codes 0/1/2, a tabela flags × variáveis e o mascaramento de segredos.
  - `docs/editor-extension.md` — a extensão VS Code e Cursor: a arquitetura de cliente fino MCP, os 8 comandos, a publicação em dois passos, o painel Problems e a instalação do VSIX.
* **Tópicos atualizados nesta janela:** `README.*` (5 — a lista de comandos ganhou o `gitpr check` e a extensão), e os próprios `docs/check-ci.*`/`docs/editor-extension.*` nasceram completos.
* **Documentação em 5 idiomas:** **48 tópicos canónicos** em `docs/` (+2) — **45 com cobertura total nos 5 idiomas** e apenas **3 tópicos parciais** (`github-issue-prompt-com-gh`, `version-markers`, `review-pr`), os mesmos 3 da janela anterior.
* **Lacuna de documentação que persiste:** o `explain` e o `tests` continuam as **únicas** funcionalidades sem tópico em `docs/` — as duas funcionalidades desta janela (check e extensão de editor) nasceram documentadas em 5 idiomas.
* **Skills locais do Claude Code:** `.claude/skills/` com **29 skills** (contagem inalterada).
* **Índice de Memória:** `.claude/memory/MEMORY.md` com **40 padrões** (contagem inalterada).
* **Relatórios de tarefa:** `docs/claude-code/reports/develop_natan/` (**108** no total; **+3** — `2026-10-12_status_report_v0_0_17.md`, `2026-10-17_pr_template_skill_artifact.md`, `2026-10-18_merge_progress_modal.md`) e `docs/gemini/reports/develop_natan/` (**16**; **+2** — `2026-10-15_gitpr_check_ci.md`, `2026-10-16_editor_extension_mcp.md`).
* **Relatórios de estado:** `docs/reports/` — este é o **18.º relatório** (v0.0.18); o v0.0.17 nunca é sobrescrito.
* **Planos de desenvolvimento:** 144 ficheiros em `docs/plans/` (+9 — ADR-013/ADR-014, os glossários `check-ci` e `editor-extension`, as specs de check e extensão, e os `plansfacts` de check/template de PR/merge) + **17 ficheiros em `docs/survey/`** (+3).

---

## **🔄 Pipeline de Distribuição**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Atualização obrigatória:** a execução consulta o PyPI no arranque e **bloqueia com exit code 1** se houver uma versão mais recente, imprimindo `pip install --upgrade gitpr-cli`; a verificação é guardada em cache por dia e o `--update` apenas reporta
3. **GitHub Releases:** removidos — sem PyInstaller, sem asset `.exe`, sem hot-swap
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala via pip) + `tests.yml` (matriz Python 3.10 e 3.13) + **`extension.yml`** 🆕 (VSIX; publicação no Marketplace/Open VSX em `ext-v*`). O `action.yml` foi reescrito para um único passo `gitpr check` — **quebra declarada** para quem usa `--linter` + `--fullreview`.
5. **Servidor MCP:** entry point `gitpr-mcp` via `pyproject.toml`
6. **Extensão de Editor 🆕:** pacote VSIX independente (`natanfiuza.gitpr`), com canais VS Code Marketplace e Open VSX; exige Node.js 20+ apenas para build, não para utilização
7. **Templates e idiomas OTA:** `templates/` e `langs/*.json` servidos a partir do GitHub (main) — o bump `v0.0.35` renova as cópias locais em `~/.gitpr/langs/` quando publicado; o `__scripts_version__` **v0.0.4** renova os hooks
8. **Versão derivada do código:** o `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — o `__version__` é a **única** fonte
9. **O `package-data` importa:** os quatro packs embutidos (`src/policy_packs/*`) viajam no wheel
10. **Estado da release 🆕:** **`v1.4.0` continua a ser a última tag** (2026-10-12). O `__version__` já está em **1.4.1** (commit `46c25c1`, PR #217), mas **a tag `v1.4.1` ainda não foi criada** e a secção `[1.4.1]` do `CHANGELOG.md` está **na árvore de trabalho**, junto com dois artefactos por rastrear (`.gitpr/reports/pr_desc/develop_natan_20261018023014_PR_DESC.md` e `.gitpr/reports/release/develop_natan_20261018023153_RELEASE.md`). O pipeline de release continua manual.

---

## **📈 Evolução Desde o Relatório Anterior (v0.0.17)**

| Área | v0.0.17 (anterior) | v0.0.18 (atual) |
|------|-------------------|-----------------|
| **Versão do GitPR** | 1.4.0 (**tag `v1.4.0`**) | **1.4.1** (bump consolidado; **tag ainda não criada**) — `v1.4.0` continua a ser a última tag |
| **Versão de Idioma** | v0.0.34 | **v0.0.35** |
| **Versão dos Scripts de Hook** | v0.0.4 | v0.0.4 (inalterada) |
| **Provedores de IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama (inalterado) |
| **Idiomas** | 5 idiomas, 6 dicionários | 5 idiomas, 6 dicionários |
| **Subcomandos** | 14 | **15** (+ `check`) — 4 grupos (baseline 6, metrics 8, policy 7, tests 1) |
| **Interface** | CLI + TUIs + assistentes + 14 subcomandos | **+ `gitpr check` + extensão VS Code/Cursor** (8 comandos no editor) |
| **Camadas** | 8 pacotes de domínio, 14 casos de uso (13 ficheiros + `__init__`) | **9 pacotes de domínio** (+ `check`) e **15 casos de uso** (+ `run_check`, `publish_pull_request`) |
| **Infraestrutura de CI 🆕** | — | **`src/infrastructure/ci/`** — SARIF 2.1.0, JUnit XML e mascaramento de segredos |
| **Tools MCP** | 16 tools / 19 recursos / 7 prompts | **18 tools** (+ `run_check`, `publish_pull_request`) / 19 recursos / 7 prompts; `mentor` e `explain` como parâmetros opt-in |
| **Flags da CLI** | 32 na raiz + 14 subcomandos | **32 na raiz + 15 subcomandos**; o `check` tem 8 opções próprias |
| **Variáveis de Ambiente** | 58 no `DEFAULT_CONFIG` + preços/moeda | **58 no `DEFAULT_CONFIG`** + preços/moeda + **9 `GITPR_CHECK_*`** |
| **Schema de Configuração** | 80 `ConfigField` / 16 categorias | 80 / 16 (inalterado — o `check` ainda não tem categoria) |
| **Linter** | Regex + segredos + SAST + `findings_out` + ciente do baseline | **+ pipeline de CI unificado com SARIF/JUnit/JSON e mascaramento** |
| **Git Hooks** | post-checkout/post-merge/pre-push no ledger | Inalterado |
| **Camada SCM** | 4 forges, paginação, `web_links.py` | **+ caso de uso de publicação partilhado** (CLI e MCP); provedores inalterados |
| **Motor de release** | Corte ancorado no changelog, ligações, PR por merge | **+ secção 1.4.1 gerada** (não consolidada, sem tag); motor inalterado |
| **i18n (chaves por ficheiro)** | 1603 × 6 — **zero por traduzir** ✅ | **1620 × 4 e 1618 × 2 — 8 por traduzir e 2 órfãs** ❌ (+17 chaves) |
| **Documentação** | 46 tópicos (43 completos + 3 parciais) | **48 tópicos (45 completos + 3 parciais)** — +2 famílias em 5 idiomas |
| **CI** | `tests.yml` (3.10 + 3.13) + antigo `action.yml` | **+ `extension.yml`**; `action.yml` reescrito para o `gitpr check` (quebra declarada) |
| **Suíte de Testes** | 2799 cenários (129 ficheiros) | **2952 cenários (138 ficheiros) — +153 cenários, +9 ficheiros** |
| **Falhas conhecidas** | 2 (`test_mentor_cli`) | **12** — as 2 antigas + **7 novas falhas de fixture no selo** + **3 de i18n** |
| **Dependências** | `packaging>=23.0` | **Nenhuma nova dependência Python**; a extensão acrescenta Node.js 20+ e `@modelcontextprotocol/sdk` |
| **Commits desde o relatório** | 11 commits | **6 commits** (`fc9d400`, `a50f21c`, `b6382b5`, `525a1ec`, `8557125`, `46c25c1`) |
| **PRs integrados** | 10 PRs (#192–#207) | **6 PRs (#208, #210, #212, #214, #216, #217)**; 4 issues fechadas (#209, #211, #213, #215) |
| **Índice de Memória** | 40 padrões | 40 padrões (inalterado) |
| **Relatórios de tarefa** | 105 claude-code, 14 gemini | **108 claude-code (+3) e 16 gemini (+2)** |
| **Planos de desenvolvimento** | 135 planos, 14 questionários | **144 planos (+9), 17 questionários (+3)** — inclui ADR-013 e ADR-014 |

---

## **🚧 Próximos Passos**

* **Fechar a release 1.4.1 🆕:** consolidar a secção `[1.4.1]` do `CHANGELOG.md`, criar a tag `v1.4.1`, publicar no PyPI e decidir o destino dos dois artefactos por rastrear (`.gitpr/reports/pr_desc/...` e `.gitpr/reports/release/...`). O `__version__` já foi incrementado, por isso o pacote publica a 1.4.1 com ou sem a tag.
* **Corrigir as 7 falhas em `tests/badge/test_badge_paths.py` 🆕:** o provedor falso do teste não tem `.token`, e o caso de uso partilhado passou a exigir o atributo. Acrescentar `token="ghp_fake"` ao `SimpleNamespace` (ou permitir saltar a verificação quando `check_remote_state=False`) restaura os 7 cenários e valida o selo no novo caminho.
* **Pagar a dívida de i18n reaberta 🆕:** 8 chaves usadas sem tradução (mensagens do `publish_pull_request` e descrições MCP) + 2 órfãs + as 2 chaves em falta em `es.json`/`fr.json`. Fechar isto desbloqueia `test_key_parity_and_count`, `test_no_missing_keys` e `test_no_orphan_keys` de uma vez.
* **Corrigir o isolamento do `tests/test_mentor_cli.py`:** são as **duas falhas mais antigas** da suíte. O patch aponta a `src.core.get_git_diff`, mas o `src/main.py` importa o nome **por valor** — corre a função real, e numa árvore cuja única alteração é um `.md` o diff volta vazio e o fluxo retorna antes do renderizador. Mudar o alvo para `src.main.get_git_diff` fecha a suíte sem tocar no produto. **Item herdado, ainda aberto.**
* **Documentar o `explain` e o `tests`:** são agora as **duas únicas** funcionalidades sem tópico em `docs/` — as duas funcionalidades desta janela nasceram documentadas em 5 idiomas. **Item herdado, ainda aberto.**
* **Automatizar a release no GitHub Actions:** o CI de testes tem três workflows, mas o build + upload para o PyPI continuam manuais — e o `extension.yml` já mostra o padrão (gatilho por tag) que um `release.yml` pode seguir. **Item herdado, ainda aberto.**
* **Alargar a cobertura do conjunto de regras de segredos:** as três lacunas continuam abertas — atribuição sem aspas (`API_KEY=abc123`), os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-`, e a ausência de uma fronteira à esquerda no nome da chave. O mascaramento do CI cobre parte, mas o **linter** (que corre no pre-commit) continua a não cobrir. **Item herdado, ainda aberto.**
* **Traduzir o `docs/review-pr.*.md`:** é a **última** família de funcionalidades com cobertura parcial (2 de 5 idiomas). **Item herdado, ainda aberto.**
* **Deixar de rastrear os artefactos de `.gitpr/metrics/export/`:** **34 ficheiros continuam rastreados** (eram 35), porque o `gitignore` não desrastreia. Falta um único `git rm --cached`. **Item herdado, ainda aberto.**
* **Documentar a quebra de contrato do `request_pull_request_reviewers`:** passou de `None` para `list[str]`; as subclasses de terceiros do `ScmProvider` precisam de ser atualizadas. **Item herdado, ainda aberto.**
* **Mapear o raio de impacto do baseline no exit code:** um baseline que não pode ser aplicado aborta seis fluxos com exit code 1, e o `check` usa agora **2**. Vale a pena explicitar o contrato completo em `docs/baseline-suppressions`/`docs/check-ci` para pipelines de CI. **Item herdado, parcialmente coberto pelo check.**
* **Reconciliar a versão do projeto:** o `CLAUDE.md` ainda diz `Current version: 0.0.37` enquanto o `__version__` está em **1.4.1**. Definir uma convenção única. **Item herdado, ainda aberto.**
* **O `gitpr -h config` ignora o `-h`:** o subcomando abre a TUI em vez de mostrar a ajuda — o portão `if ctx.invoked_subcommand is not None: return` corre antes do bloco do `help_flag`. **Item herdado, ainda aberto.**
* **Migrar o extrator do `sync_i18n.py` para AST:** a regex trunca literais com concatenação implícita (`__("a " "b")`). O `tests/test_i18n.py` já usa AST; o script de sincronização continua em regex. **Item herdado, ainda aberto.**
* **Secção Smart Exclude na TUI:** o item 10 do questionário da TUI de configuração continua por começar, e os campos `GITPR_SMART_EXCLUDES_*` continuam sob `diff`, sem categoria própria. **Item herdado, ainda aberto.**
* **Dívidas registadas no plano da TUI de configuração:** o `DEFAULT_CONFIG` ficou redundante com o schema; o banner de abertura não lista `--init`, `--base` nem `--plugins`; o `LinterApp` é a **única** app Textual que ainda não desativa a paleta de comandos; e as **9 `GITPR_CHECK_*`** ainda não têm **nenhuma** categoria no schema — o `check` é a única funcionalidade configurável fora da TUI. **Item herdado, ampliado nesta janela.**
* **Criar uma nota de migração para o `action.yml`:** substituir `--linter` + `--fullreview` pelo `gitpr check` é uma quebra de contrato para os workflows existentes; o `docs/check-ci` documenta o novo comando, mas não um "antes e depois" para quem já usa a action.
* **Publicar a extensão nos marketplaces:** o workflow está pronto, mas depende de `VSCE_PAT`/`OVSX_PAT`; até lá, a instalação é via VSIX local.
* **Provedor Anthropic Claude:** Suporte direto para a API do Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual no Dashboard:** Histogramas temporais e tendências de tokens na TUI de métricas.
* **Seed para o `.gitpr/conf/` local:** O seeding de templates de configuração local (smart-excludes, linter) continua pendente como subcomando próprio ou passo de assistente.
* **Mais provedores:** OpenAI direto, provedores locais adicionais.

### ✅ Concluídos nesta janela (2026-10-12 → 2026-10-18)

* ~~**`gitpr check` — comando de CI unificado**~~ 🆕 — `src/domain/check/`, `src/infrastructure/ci/`, `run_check.py`, SARIF/JSON/JUnit, exit codes 0/1/2, 8 opções, 9 `GITPR_CHECK_*`, `action.yml` reescrito e docs em 5 idiomas (PR #210).
* ~~**Extensão VS Code e Cursor + tools MCP check/publish**~~ 🆕 — cliente fino MCP, 8 comandos, painel Problems, publicação em dois passos, `extension.yml`, `run_check`/`publish_pull_request` e parâmetros `mentor`/`explain` (PR #212).
* ~~**Caso de uso partilhado de publicação de PR**~~ 🆕 — `publish_pull_request.py` usado pelo `--no-edit` e pelo MCP, com `PublishPrOutcome` e mascaramento de erros.
* ~~**Artefacto corrompido na skill de PR**~~ 🆕 — limpeza em memória no `get_skill_context()`, template `pt_br` corrigido e varredura parametrizada de todos os templates (PR #214).
* ~~**Modal de progresso no merge de PR**~~ 🆕 — `title` opcional, `mark_finished(show_close=False)`, worker a partir da montagem, exceção genérica capturada e dispensa automática de 1.2 s (PR #216).
* ~~**Bump para 1.4.1 e v0.0.35**~~ 🆕 — `46c25c1` (PR #217); faltam apenas a tag e a publicação.
* ~~**Consolidar a correção dos cabeçalhos da `[1.4.0]`**~~ — os cabeçalhos em inglês estão no `CHANGELOG.md` versionado desde `1abb1b5`/`fc9d400`. **Item da janela anterior fechado.**
* ~~**Documentar o `check` e a extensão em 5 idiomas**~~ 🆕 — `docs/check-ci.*` e `docs/editor-extension.*`, com ADR-013/ADR-014 e os dois glossários.

---

**Relatório gerado em:** 2026-10-18  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
