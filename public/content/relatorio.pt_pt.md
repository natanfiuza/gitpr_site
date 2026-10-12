# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.17 (2026-10-12)**

## **📌 Visão Geral**

O **GitPR** é uma ferramenta de CLI (Command Line Interface) avançada para automação de processos Git utilizando Inteligência Artificial (Google Gemini / DeepSeek / Ollama). O objetivo principal é atuar como um assistente inteligente local que faz Code Reviews, gera Pull Requests, mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo de trabalho do desenvolvedor (Shift Left).

**Novidades desta versão (v0.0.17):**
- **A release 1.3.0 foi fechada e a 1.4.0 já tem tag:** a tag `v1.3.0` (merge do PR #193, 2026-09-24) fechou o item que bloqueava duas janelas, e o `CHANGELOG.md` foi completado com as seis funcionalidades que faltavam. Dez dias depois, a tag `v1.4.0` (merge do PR #207, 2026-10-12) fechou esta janela inteira — as cinco funcionalidades abaixo **mais** a nota de release da 1.3.0 couberam num único ciclo.
- **O `gitpr release` deixou de repetir o histórico:** as notas de release listavam, a cada versão, todos os commits de todas as versões anteriores (a secção 1.3.0 saiu com 124 bullets, 115 deles repetição da 1.2.0). A causa não era a IA — era o intervalo do `git log`, ancorado no `git describe`, que neste repositório resolvia para `v0.0.8`. O corte passou a ser a **secção anterior do próprio changelog**, cada bullet ganhou **ligação real e clicável** de commit, PR e contribuidor, e um commit que já está noutra secção é descartado antes de virar bullet — quando nada sobra, a release **aborta** em vez de publicar uma secção vazia.
- **A telemetria saiu do ficheiro JSON e tornou-se um ledger SQLite:** os eventos eram gravados em ficheiros por comando, a partir de uma thread daemon, e depois "enriquecidos" de volta pelo cache de IA através de uma correspondência heurística por (repo, branch, ação, minuto) que dependia de três mapas de pastas divergentes. O resultado era previsível: `tokens_actual` **sempre zero** e perda de dados entre repositórios na exportação. O `~/.gitpr/metrics/telemetry.db` grava a linha **de forma síncrona**, com os tokens copiados dos metadados do provedor no momento da escrita, e um processo de curta duração (um hook, por exemplo) deixou de perder a sua própria linha ao sair.
- **Mentor Mode — o review que ensina, não só aponta:** um review diz *o que* está errado e quase nunca *porque é que isso importa*. O `gitpr mentor` (e a flag `--mentor` em `-r`/`-f`) enriquece os apontamentos do último review com o que está a acontecer, porque importa, uma analogia do dia a dia e os conceitos a estudar. É **opt-in** e nunca inventa: sem evidência suficiente, a explicação di-lo em vez de preencher o vazio.
- **Pontuação de risco determinística — sem IA, sem quota:** o `gitpr risk` produz uma pontuação de 0–100 com nível `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` a partir de sinais locais (caminhos críticos, testes em falta, migrations, histórico de bugs e reverts, apontamentos estáticos). A fórmula é declarada (**50/30/20**), tal como as regras de escalonamento obrigatório, e a pontuação entra no prompt do review como contexto **read-only** e no relatório. Um número explicável vale mais do que um palpite caro.
- **Policy Packs — a política do repositório tornou-se um artefacto versionado:** skills, regras de linter, severidades, caminhos críticos, pesos de risco e convenções de PR/commit andavam espalhados e cada repositório configurava-os à mão. O `gitpr policy` adota um pack YAML versionado com **checksum**, resolve a precedência entre camadas e regista a escolha no lockfile que a equipa consolida. Quatro packs embutidos vêm no wheel; sem pack ativo, **nada muda**.
- **Baseline e supressões auditáveis — o ruído conhecido deixa de bloquear:** os linters e os reviews voltavam a reportar os mesmos apontamentos pré-existentes a cada execução, e cabia a quem revia distinguir a saída de cabeça. O `.gitpr/baseline.json` regista o instantâneo dos apontamentos aceites, cada apontamento novo é classificado como `new`/`existing`/`resolved`/`ignored`/`accepted_debt`, e **só o `new` bloqueia**. Cada supressão transporta um motivo; a dívida aceite transporta um responsável e, quando existe, um prazo.
- **O modelo de apontamento tornou-se um tipo canónico:** o `NormalizedFinding` saiu de `infrastructure/linter/external/base_bridge.py` para `src/domain/finding/`. Sem isso, cada bridge — Semgrep, Gitleaks, Bandit, o conjunto de regras regex — produziria uma impressão digital de forma diferente do mesmo apontamento e o baseline não teria como reconhecê-lo.
- **A dívida da janela anterior foi fechada:** o `SKILL_LABELS` e o `mcp_server.SKILL_FILES` receberam `tests`, `explain` e agora `mentor`; as **40 chaves `__()`** que não existiam em nenhum dicionário foram traduzidas; e as **4 falhas** da janela anterior já não existem. As três afirmações que reportavam a dívida continuam a correr — simplesmente deixaram de reportar. A suíte, medida de ponta a ponta nesta janela, fecha em **2795 passados de 2799** — as duas falhas restantes são um bug de isolamento em `tests/test_mentor_cli.py`, não uma regressão do produto (ver §Testes).
- **A cobertura de i18n fechou o ciclo:** **1603 chaves** em cada um dos 6 dicionários (+445 nesta janela) e **zero chaves usadas no código sem tradução** — o código usa 1603, os dicionários têm 1603.
- **As traduções pendentes foram pagas:** o `split-command` e o `otimizacao-de-tokens` completaram os 5 idiomas; o `review-pr` continua com 2. Duas famílias só em PT (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) saíram do topo de `docs/` para `docs/extra/`.

- **Versão atual:** 1.4.0 (**com tag** — `v1.4.0`, merge do PR #207, 2026-10-12; `v1.3.0` em 2026-09-24)
- **Versão dos dicionários de idioma:** v0.0.34 (cadeia v0.0.32 → v0.0.33 → v0.0.34 nesta janela)
- **Versão dos scripts de hook:** v0.0.4 (v0.0.3 → v0.0.4 nesta janela)
- **Publicação:** PyPI (`pip install gitpr-cli`) — canal binário removido
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositório:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licença:** LGPL-2.1
- **Idiomas suportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 dicionários)

---

## **🏗️ Arquitetura Base e Bibliotecas**

* **Linguagem:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags e formatação de terminal) — **14 subcomandos** 🆕 (`badge`, **`baseline`** 🆕, `config`, `demo`, `explain`, `fix`, **`mentor`** 🆕, **`metrics`** 🆕, **`policy`** 🆕, `release`, `review-pr`, **`risk`** 🆕, `split`, `tests`). Quatro deles são **grupos**: `baseline` (6 subcomandos), `metrics` (8), `policy` (7) e `tests` (1).
* **UI/Terminal:** Textual — TUI para chat interativo, edição de issues, ecrã de ajuda, dashboard de métricas, PR Publisher, erros do linter (`LinterApp`), configuração (`ConfigApp`), o modal `NoticeScreen`, a **visita guiada do `gitpr demo`** (`src/ui/demo/`) e o **assistente de migração do ledger** (`src/ui/metrics_migration_app.py`) 🆕.
* **Arquitetura em Camadas:** `src/domain/` (regras puras, sem I/O) + `src/application/use_cases/` (orquestração, **14 casos de uso** 🆕) + apresentação (CLI/TUI). **8 pacotes de domínio** 🆕: `baseline`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`.
* **Criptografia:** `cryptography.fernet` para proteção local de chaves de API, tokens GitHub e tokens SCM das forges.
* **Configuração:** `python-dotenv`, `pyyaml` (linter estático) + o seu próprio schema declarativo (`src/config_schema.py`, **80 `ConfigField`** e **16 categorias** 🆕).
* **Provedores de IA:** Integração via SDK oficial do Google GenAI, OpenAI SDK (DeepSeek) e OpenAI SDK (Ollama local). Predefinições configuradas: `gemini-pro-latest` / `gemini-flash-lite-latest` e `deepseek-v4-pro` / `deepseek-v4-flash`.
* **APIs das Forges:** `requests` (REST) — abstração multi-forge em `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + **`web_links.py`** 🆕 (URLs de commit, PR e perfil por forge, funções puras); `src/github_api.py` mantido como shim obsoleto.
* **Linter / SAST:** regex YAML + bridge Checkstyle + **conjunto de regras de segredos embutido** (`src/security_ruleset.py`) + **4 bridges em `src/infrastructure/linter/external/`** (base + Semgrep, Gitleaks, Bandit), com modelo normalizado em `src/domain/linter/sast_finding_mapper.py`. 🆕 O apontamento normalizado passou a viver em **`src/domain/finding/`** e há um **canal lateral `findings_out`** que expõe os apontamentos estruturados ao lado das strings de alerta.
* **Git Internals:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, **`identity.py`** 🆕 (rótulo de repo, branch e autor numa única chamada ao git), **`risk_history_reader.py`** 🆕 e **`test_matcher.py`** 🆕.
* **Persistência local 🆕:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) com WAL, `PRAGMA user_version`, chaves UUID e transporte por bundle.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **16 ferramentas anotadas** 🆕, **19 recursos** 🆕, 7 prompts.
* **Testes:** Pytest + `unittest.mock` (**129 módulos de teste** 🆕 — 49 na raiz, 11 em `tests/application/use_cases/`, 10 em `tests/fix/`, 9 em `tests/scm/`, 7 em `tests/badge/`, 6 em `tests/demo/`, 6 em `tests/split/`, 5 em `tests/domain/baseline/`, 4 em `tests/domain/policy/`, 4 em `tests/review/`, 4 em `tests/infrastructure/linter/external/`, 2 em `tests/domain/risk/`, 2 em `tests/domain/tests_generation/`, 2 em `tests/integration/`, 2 em `tests/infrastructure/git/`, 1 em `tests/domain/finding/`, 1 em `tests/domain/mentor/`, 1 em `tests/domain/pr/`, 2 em `tests/domain/linter/`, 1 em `tests/infrastructure/baseline/` —, **2799 cenários recolhidos**) + testes e2e do servidor MCP via subprocesso real (JSON-RPC stdio) + fixtures de repositório git real (`tests/fix/`, `tests/split/`) + uma **guarda de isolamento de rede** (`tests/demo/conftest.py`, `tests/integration/conftest.py`).
* **Empacotamento:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; **packs embutidos via `package-data`** 🆕 e a nova dependência **`packaging>=23.0`** 🆕.
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` + `tests.yml` (matriz Python 3.10/3.13).

---

## **🧩 Módulos Implementados e Arquitetura de Ficheiros**

### **1. Operações Core e Git (`src/core.py`)**

* **Geração Estruturada:** Comunica com o LLM a pedir um retorno estritamente em JSON.
* **Map-Reduce (Diffs Gigantes):** Quando o diff ultrapassa ~90k tokens, divide-o automaticamente em lotes por ficheiro (`split_diff_into_chunks`), processa cada parte (Map) e unifica os resumos (Reduce). Suporta PRs, commits e Issues. 🆕 O `provider`/`model` passam a ser **agregados entre os chunks** e o `meta`/`modules` são repassados ao `log_command_metric`, que passou a copiar tokens reais dos metadados do provedor no momento da escrita.
* **Tokenizer Local:** `tokenizer.json` para estimativa precisa de tokens antes do envio à IA.
* **Estimativa de Tokens:** Heurística leve `len() // 4` via `estimate_token_count()` com fallback para o tokenizer local.
* **Otimização Git Nativa:** Flags `-U1`, `-w`, `-M`, `-B` nos comandos `get_git_diff` e `get_git_full_diff` para reduzir contexto inútil.
* **`get_split_diff()`:** O diff próprio do `split`, com `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sem** reutilizar o `get_git_diff()` — o `-w` (ignora espaços em branco) e os smart-excludes quebrariam a garantia de árvore byte-idêntica.
* **Pré-Gravação (`--pre-save`):** Flag de debug oculta que guarda o payload completo (instrução do sistema + prompt) em JSON antes de cada chamada à IA.
* **Exclusões Inteligentes com Duas Camadas:** Camada global (`~/.gitpr/conf/`) + camada local do projeto (`./.gitpr/conf/`), unidas em runtime. Auto-seed do ficheiro local na primeira execução. `_load_smart_excludes()` aceita `force=`.
* **Métricas com Registo de Tempo:** `log_command_metric()` em todos os fluxos, agora com `meta`, `modules` e `duration_ms`. 🆕 Duas cópias divergentes do mapa comando→pasta de cache foram **removidas** daqui — eram a causa de o `tokens_actual` ser sempre `0`.
* **Resolução Centralizada do Output:** `resolve_output_path()` — predefinição em `.gitpr/reports/{type}/`.
* **Assistente SCM (`run_scm_init_wizard()`)**: `gitpr --init` — deteta a forge a partir do remote de origem, solicita extras por forge, valida o token e persiste **apenas em caso de sucesso**; anuncia o selo automático no caminho do PR.
* **Template de Skill de Release (`ensure_release_skill_template()`)**: Transfere `templates/gitpr.release.*.md` na primeira utilização do `gitpr release`.
* **Registo de Skills Partilhado:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` em `src/config.py`. 🆕 Ganhou a entrada de **`mentor`** (`.gitpr.mentor.md`) — o `tests` e o `explain` já lá estavam em `tests/test_skill_context.py`.
* **Motor de Review com Âmbito de Cache (`generate_pr_content`):** `cache_scope` (anexado **apenas à chave de cache**) e `store_diff` (guarda o diff revisto). 🆕 O `cache_scope` passa a carregar também o **pack de política ativo**, para que dois repositórios sob políticas diferentes não partilhem uma resposta.
* **Trailer de Coautoria:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceiros.
* **Subprocessos Blindados:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` em todos os `subprocess.run`; verificação de ligação via socket `8.8.8.8:53` antes das operações de rede.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitetura de Plugins:** Carrega plugins de `~/.gitpr/plugins/`, aplicando-se a **todos os projetos**.
* **Plugins de Linter (`linter/`):** Ficheiros `.yml` unidos ao `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`):** Ficheiros `.md` que estendem o contexto do sistema.
* **Factory Closures:** `get_linter_plugins` e `get_prompt_plugins` isolam estado entre sessões.
* **Comando `--plugins`:** Lista todos os plugins globais instalados com os seus tipos e paths.
* **Documentação Multilíngue:** `docs/plugins-system.md` em 5 idiomas.
* 🆕 **Relação com os Policy Packs:** o pack é uma **terceira** camada de regras — os plugins pertencem à máquina, o `.gitpr.linter.yml` pertence ao projeto e o pack é versionado e consolidado. O `load_linter_rules()` une as três na ordem declarada, com as severidades do pack por cima.

### **3. Interface CLI e Configuração (`src/main.py` e `src/config.py`)**

* **Setup Inicial:** Deteta a primeira execução, cria a pasta `~/.gitpr/` e solicita interativamente as chaves de API, preferências e idioma. A dica de primeira execução aponta para o `gitpr demo`.
* **Routing de Comandos:** Gere todas as flags e os **14 subcomandos** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, **`mentor`** 🆕, **`metrics`** 🆕, **`policy`** 🆕, **`risk`** 🆕 e **`baseline`** 🆕.
* **Comportamento Predefinido:** Executar `gitpr` sem flags abre a TUI do PR Publisher.
* **Flags (agora 32 opções Click na raiz — 5 saíram, 1 entrou nesta janela):**
  * **Removidas 🆕:** `--metrics`, `--export`, `--purge`, `--hook-event` e `--dashboard` — tornaram-se o grupo `gitpr metrics` (ver §36). **Quebra de CLI declarada.**
  * **`--mentor` 🆕:** Anexa a secção didática do Mentor ao review de código (`-r` / `-f`).
  * `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` — inalteradas.
* **Grupo `baseline` 🆕 — 6 subcomandos:** `create` (`--refresh`, `--base`, `--yes`, `--format`), `show` (`--status`, `--rule`, `--file`, `--format`), `validate` (`--format`), `update` (`--recompute`, `--base`, `--yes`), `suppress` (`--reason`, `--scope`, `--debt`, `--owner`, `--due-date`, `--yes`) e `unsuppress` (`--yes`).
* **Grupo `policy` 🆕 — 7 subcomandos:** `list`, `show`, `validate`, `use`, `off`, `install` (`--force`, `--yes`) e `init` (`--stack`, `--yes`).
* **Grupo `metrics` 🆕 — 8 subcomandos:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` e o oculto `hook-event`; os de leitura partilham `--days`/`--since`/`--until`.
* **Subcomando `risk` 🆕 — 4 opções:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Subcomando `mentor` 🆕 — 2 opções:** `--finding`, `--provider`.
* **Variáveis de Ambiente (58 chaves no `DEFAULT_CONFIG`, +9 nesta janela):** as quatro `GITPR_BASELINE_*` (`ENABLED`, `PATH`, `REQUIRE_LOCKFILE_CHECKSUM_MATCH`, `ALLOW_LOCAL_OVERRIDES`), as duas do mentor (`GITPR_REVIEW_MENTOR_MODE`, `GITPR_MENTOR_INCLUDE_ANALOGY`), as duas da policy (`GITPR_POLICY_ENABLED`, `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`) e a `GITPR_RISK_INCLUDE_IN_REVIEW`. **Fora do `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT` e `GITPR_METRICS_CURRENCY`, lidas por `get_model_price()`.
* **Ajuda Contextual:** `-h --flag` apresenta documentação específica da flag com uma ligação direta (ciente do idioma); os subcomandos têm o seu próprio `epilog=` (parágrafo `\b` do Click). 🆕 O `risk`, o `policy`, o `baseline` e o `mentor` entraram no `HELP_MAP`/`HELP_PRIORITY`.
* **Camada de Escrita do `.env`:** `read_env_file_values()` lê **apenas o ficheiro** via `dotenv_values`; `save_config_values()` com `set_key`; `remove_config_value()` com `unset_key`. `validate_ai_key()` distingue uma credencial recusada (`401`/`403`) de uma rede inacessível.
* **Portão de Baseline no Fluxo 🆕:** O `main.py` resolve o portão do baseline antes do lint, do review, do full review, da auditoria de ficheiro, do review remoto e do risk, e o **código de saída do lint passa a seguir o bloqueio ciente do baseline** — um apontamento novo falha, um apontamento conhecido não.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` e `src/ui/pr_publish_help.py`)**

* **Interface Interativa Completa:** TUI para rever, editar e publicar Pull Requests diretamente no terminal.
* **7 Ecrãs Modais:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` e `NoticeScreen`.
* **Selo no Corpo do PR:** O `attach_pr_badge()` corre no `src/main.py` logo após o `pr_data` estar completo — **ponto único de injeção**. Anexação idempotente por marcador e divulgação explícita no `--no-edit`.
* **`_attach_reviewers` com resolução:** Resolve o que o utilizador escreveu **antes** de enviar, reporta o que foi descartado e, num `422` de lote, **repete um a um**.
* **Revisores Sugeridos:** Consulta a forge para obter revisores sugeridos; a `_reviewer_suggestion_view()` monta as `resolutions` via `resolve_candidates` e pré-preenche os `handles`.
* **Bindings:** F1 (Ajuda), F2 (Guardar .md local), F3 (Publicar via forge), Esc (Sair).
* **Fluxo de Auto-Commit:** Linter → mensagem IA → confirmação → commit → push → publica PR. 🆕 O resultado do linter passa a transportar os **apontamentos estruturados** (`findings_out`), que é o que o baseline classifica antes de o relatório ser composto.
* **Tratamento de PR Existente / Auto-Upstream / Merge Flow** — inalterados (`GITPR_AUTO_MERGE`).

### **5. Módulo de API do GitHub (`src/github_api.py`)**

* **Shim Obsoleto:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` e as restantes funções delegam em `src/infrastructure/scm/github_provider.py`; emite um `DeprecationWarning` e mantém os tuplos legacy `(ok, data, status)` — nenhum código novo o pode importar.

### **6. Motor de Análise Estática / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analisa estaticamente as linhas adicionadas (`+`) no diff sem gastar quotas de IA.
* **Regras YAML:** Lê o `.gitpr.linter.yml` (criado via `--skill`).
* **Plugins de Linter:** Regras adicionais carregadas de `~/.gitpr/plugins/linter/*.yml`.
* **Wildcard de Extensão:** `extensions: ["*"]` significa **todos** os ficheiros, incluindo os que não têm sufixo e os dotfiles.
* **Conjunto de Regras de Segredos Embutido:** O `load_linter_rules()` junta o `src/security_ruleset.py` **depois** das regras do projeto e dos plugins. Sete regras, todas `extensions: ["*"]`: cinco `error` e dois `warning`. O alerta **nunca ecoa o valor** — a mensagem transporta apenas `{file_name}` e `{line_number}`.
* 🆕 **Camada de Policy Pack:** O `load_linter_rules()` passa a aplicar a fusão em camadas do pack ativo e a **sobrepor as severidades**, sem tocar no `.gitpr.linter.yml` nem nos plugins.
* 🆕 **Apontamentos Estruturados (`findings_out`):** além das strings de alerta, o motor devolve os apontamentos normalizados do tipo canónico `src/domain/finding/` — é essa lista que o baseline usa para a impressão digital. Inclui a correção do contador de linhas do lado novo, para que as linhas de contexto avancem corretamente.
* **Bridges SAST Opt-In:** O Semgrep, o Gitleaks e o Bandit correm no caminho de ficheiro inteiro **e** no caminho de diff, com dedup `[Gitleaks + Regex]`, mascaramento do valor (`AKIA****`) e um aviso quando a ferramenta está ativada mas fora do `PATH`.
* **`skip_external`:** O `parse_diff_and_lint(..., skip_external=True)` no fluxo remoto, porque a bridge executa binários contra ficheiros em disco — a árvore local, não o PR.
* **Relatório Consolidado:** O `generate_linter_report_content()` consolida erros regex + externos em `.gitpr/reports/linter/` — gerado apenas quando há violações.
* 🆕 **Ciente do baseline:** os alertas são anotados com o estado do baseline antes de se tornarem relatório, e o código de saída do fluxo de lint segue o bloqueio ciente do baseline.

### **7. Segurança e Autenticação (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografia:** Gera a chave mestra `secret.key` em `~/.gitpr/`.
* **Proteção de Tokens:** `encrypt_data`/`decrypt_data` para chaves de IA, GitHub PAT e tokens SCM das forges.
* **Validação Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — 401 → ciclo de reautenticação preservando o rascunho.
* **Segredos na TUI de Configuração:** Os campos `KIND_SECRET` são editados mascarados, **nunca** apresentam o valor em claro e são encriptados com Fernet antes de serem escritos.
* **Deteção de Segredos no Fluxo:** O linter — que corre no hook de pre-commit — é uma barreira de credenciais com regras idênticas em todas as máquinas.
* ⚠️ **Lacunas de cobertura confirmadas como ainda abertas nesta janela:** a regra genérica continua a exigir aspas depois do separador (`\s*[:=]\s*['\"]`), pelo que o formato `.env` **sem aspas** (`API_KEY=abc123`) continua a não ser apanhado; os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-` continuam de fora (o padrão é `gh[pousr]_` e `xox[baprs]-`); e não há fronteira à esquerda no nome da chave, pelo que `mytoken` corresponde da mesma forma que `token`.

### **8. Auto-Atualizador (`src/updater.py`)**

* **PyPI como Fonte Única:** O `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` e escreve a cache diária **sem** o campo `download_url`.
* **Bloqueio Obrigatório (`enforce_update_required()`):** Devolve `True` (depois de imprimir as duas versões e o comando pip) quando a versão publicada é mais recente; `False` quando está atualizado, quando a versão remota é **desconhecida (offline)** ou quando a verificação está desligada.
* **`check_and_update()`:** Apenas consulta e **reporta**, nunca instala.
* **Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` — não é publicitada; existe para a suíte e para a automatização offline.
* **Versionamento Centralizado:** `__version__` (**1.4.0** 🆕), `__lang_version__` (**v0.0.34** 🆕 — cadeia v0.0.31 → **v0.0.32** (release notes) → **v0.0.33** (mentor) → **v0.0.34** (bump de release)), `__scripts_version__` (**v0.0.4** 🆕), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* 🆕 **Nesta janela os três bumps saíram da árvore de trabalho e tornaram-se um commit:** o `af35270` subiu o GitPR, os idiomas e os scripts em conjunto, e o `CHANGELOG.md` deixou de anunciar uma versão que o pacote não construía.
* ⚠️ **Correção pendente na árvore de trabalho:** os cabeçalhos de secção da `[1.4.0]` saíram localizados (`Funcionalidades`, `Documentação`, `Tarefas`, `Contribuidores`) e foram corrigidos para inglês **sem commit** — é a única alteração em aberto no repositório hoje.

### **9. Interface de Chat Interativa (`src/ui/chat_app.py`)**

* **TUI Completa:** Histórico de mensagens, input multi-linha, barra de estado com bindings visíveis. `ENABLE_COMMAND_PALETTE = False`.
* **Memória por Branch (`src/chat_memory.py`):** Histórico persistido por branch, com continuidade entre sessões.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`. O `/tests` (e os aliases localizados) delega no **mesmo caso de uso** que a CLI usa.
* **Auto-Patching (F5), Atualização de Diff (F2), Exportação de Sessão (F6).**
* **Extrator Partilhado:** O F5 e o `ctrl+s` chamam `patch_extractor.extract_code_blocks()` — comportamento visível idêntico, sem lógica duplicada.

### **10. Internacionalização — i18n (`src/i18n.py`)**

* **Sistema Inspirado no Laravel:** Função `__()` com suporte a placeholders nomeados (`{count}`, `{file}`, etc.).
* **Deteção Automática:** Deteta o idioma do SO na primeira execução e guarda-o em `GITPR_LANG`; o diretório do perfil é criado **antes** de persistir.
* **5 Idiomas, 6 Dicionários:** en_us (predefinição/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Ficheiros Versionados:** O `__lang_version__` (**v0.0.34**) controla a atualização dos pacotes de idioma (`langs/*.json`).
* **Cobertura:** **1603 chaves** em cada um dos 6 ficheiros — **paridade total de key sets** e **cobertura total do código**: o código usa **1603** chaves e **nenhuma** fica sem tradução (+445 nesta janela; a dívida das 40 chaves da janela anterior foi fechada). ✅
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
* 🆕 **Chamadas com `quiet`:** a release e as funcionalidades novas encaminham o `quiet` até à camada de IA, para que o spinner nunca corrompa uma saída `--format json`.
* 🆕 **Uma chamada de cada vez, com cache:** o mentor e o explain reutilizam a cache MD5 por prompt — voltar a correr sobre um review inalterado não custa nada.

### **13. Cache Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Chaveamento por hash MD5 do diff e do prompt, com indexação por idioma.
* **Seleção do Último Review (`resolve_last_review()`):** Escolhe o registo `review`/`fullreview` **mais recente** para um par repo+branch — é a porta de entrada do `gitpr fix` e, agora, também do `gitpr mentor`.
* **Diff Revisto (`reviewed_diff`):** Campo no topo do registo que guarda o diff efetivamente revisto, preferido por `fix/apply_fix.reviewed_diff()`.
* **Telemetria e Duração:** Persistência dos campos `duration_ms` e `meta_raw`.
* **Leitura para o Dashboard:** O `scan_cache_files_for_dashboard()` foi **rebaixado a ponte de pré-migração** — o dashboard lê o ledger, e o scan existe apenas para reconstruir a série a partir da cache quando o utilizador o escolhe no assistente de migração.

### **14. Motor de Issues e TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff atual, Histórico da branch (`-ht`) e Arqueologia por Blame (`-b`).
* **Publicação Multi-Forge:** O F3 cria a issue na forge **configurada** (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — o Azure DevOps lança `ScmNotSupportedError`.
* **Map-Reduce para Issues:** Contexto acima de ~90k tokens é dividido e unificado.
* **Tratamento de 401:** Sinalização de reautenticação sem fechar a aplicação.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Acompanha a evolução e a autoria histórica com classificação de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registados via `log_blame_metric()` com profundidade e número de commits analisados.
* 🆕 **Passou também a alimentar o risco:** o leitor de histórico da pontuação (`risk_history_reader.py`) usa a mesma janela de 90 dias e classifica bugs/reverts por padrão, sem chamar a IA.

### **16. Servidor MCP e Invocação Direta via CLI (`src/mcp_server.py`)**

* **16 Ferramentas MCP Anotadas 🆕:** as 14 anteriores + a **`get_baseline_status`** 🆕 (o digest do baseline: contagens por estado, top regras, supressões e dívida aceite com responsável e prazo, dívida vencida, estado do checksum — **nunca corre o linter, nunca chama a IA, nunca escreve**) e a **`get_usage_metrics`** 🆕 (o ledger de utilização numa janela: execuções, custo por modelo, módulos tocados, provedores e taxas de qualidade; sem atalho de dia — quem chama nomeia as suas próprias datas).
* **19 Recursos + 7 Prompts Templatizados 🆕:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + **`baseline://summary`** 🆕 + `prompt://list` + 7 prompts.
* **Registo de Skills Alinhado 🆕:** O `mcp_server.SKILL_FILES` passou a ter **11 entradas** — recebeu `tests`, `explain` e `mentor`. A afirmação `TestSkillRegistryAgreement`, que reportava a divergência, agora **passa**. ✅
* **Invocação CLI Direta:** O comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca qualquer tool sem iniciar o servidor stdio; o `gitpr-mcp --list` imprime o registo completo em JSON.
* **Isolamento do Stdout Real:** O `_write_real_stdout()` escreve no `sys.__stdout__` original, com fallback para `buffer` ou nova codificação com `errors='replace'` em code pages legacy do Windows.
* **Offload do Event Loop:** Decorator `_offload` (`anyio.to_thread.run_sync`) em todas as tools — a ordem dos decorators importa (`@mcp.tool` **acima** de `@_offload`).
* **Falhas como Erros Estruturados 🆕:** as tools novas devolvem a falha **como dado** em vez de lançar — um agente que pede o estado do baseline recebe o motivo da falha, não uma exceção.

### **17. TUI de Dashboard de Métricas (`src/ui/metrics_app.py`)**

* **Âmbito por Repositório (Repo-Scope):** Rótulo `📁 Repository: owner/repo` e filtragem estrita por projeto.
* **Varredura Assíncrona com Overlay:** Worker thread em background com uma `ProgressBar`; os testes aguardam `workers.wait_for_complete()` antes de afirmar.
* 🆕 **Leitura do Ledger:** o dashboard deixou de varrer ficheiros JSON e passou a consultar o `telemetry.db` na worker thread, renderizando as **cinco secções novas** (custo, qualidade, módulos, provedores, ciclo) a partir dos mesmos renderizadores que a CLI usa.
* 🆕 **Assistente de Migração (`src/ui/metrics_migration_app.py`):** oferece importar os ficheiros legacy, importar **e** reconstruir a partir da cache de IA, ou "agora não" — com semântica de aborto que **nunca cria um ledger vazio** sobre ficheiros ainda não importados.
* **Exportação Local:** CSV/JSON em `./.gitpr/metrics/export/` — 🆕 o `.gitpr/metrics/` entrou no `.gitignore` com um comentário a explicar porque é que o `.gitpr/skill/` **não** entra (é conteúdo do projeto). Os 35 artefactos já rastreados continuam rastreados, porque o `gitignore` não desrastreia.

### **18. Sistema de Métricas e Telemetria (`src/metrics.py`)**

* 🆕 **Reescrito nesta janela — ver §36.** O `src/metrics.py` deixou de escrever ficheiros e passa a operar sobre **linhas do ledger**; o `enrich_metrics_from_cache()` e o `load_cache_token_summary()` foram **removidos** (eram a correspondência heurística que zerava o `tokens_actual`).
* **Âmbito por Repositório:** Todos os eventos indexados por `repo_name`, agora resolvido pelo `src/infrastructure/git/identity.py` — que funciona em **qualquer** forge, não apenas no GitHub.
* **Eventos de Hook, Linter e Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Exportação e Limpeza:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` com confirmação interativa, `bundle`/`merge` para transportar e fundir janelas.

### **19. Sincronização de Idiomas dos Git Hooks**

* **Versionamento Independente:** `__scripts_version__` (**v0.0.4** 🆕).
* 🆕 **Templates reescritos:** o `post-checkout`, o `post-merge` e o `pre-push` passam a chamar `gitpr --quiet metrics hook-event <name>`, protegidos por `command -v gitpr` e `|| true` — a resolução do repositório em bash, que era **só do GitHub** e gravava eventos em nomes de pasta inutilizáveis, foi removida. **Ação necessária: reinstalar os hooks com `gitpr --installhooks`.**
* **Mapeamento de Sufixos (`HOOK_SCRIPT_SUFFIXES`):** Os códigos de interface (`es_es`, `fr_fr`) traduzidos para os sufixos publicados (`.es`, `.fr`).
* **Escolha vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detetar uma mudança de idioma**.
* **`effective_hook_lang()`:** Resolve o idioma efetivo; o `--lang` deixou de ser descartado nesse caminho.
* **Skip de Merge-Source:** O `prepare-commit-msg` salta as fontes `message|merge|squash|commit`.

### **20. Bridge de Linters Externos e Assistente Interativo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistente `--linter-setup`:** Um assistente interativo com presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e injeção do bloco `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` com uma cadeia local → download → stale → fallback embutido.
* **TUI de Erros do Linter:** O `src/ui/linter_app.py` (Textual) apresenta erros críticos e warnings; em modo hook/quiet imprime e faz `sys.exit(1)`.
* **Relatório Markdown:** Consolidado em `.gitpr/reports/linter/` apenas quando há violações.
* **A Camada SAST É Irmã, Não Substituta:** os bridges Semgrep/Gitleaks/Bandit alimentam o **mesmo** pipeline de relatório.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstração Única (`ScmProvider` ABC):** O `base.py` define o contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. e `ScmProviderError(provider, http_status, message)` — `http_status` 0 = falha de rede).
* **Registo e Factory:** O `resolve_scm_provider()` seleciona por `GITPR_SCM_PROVIDER` (predefinição `github`); o `detect_provider_from_remote()` identifica a forge a partir do URL de origem.
* 🆕 **`list_pull_requests(repo, state, since, until)` — paginado e canónico:** os quatro provedores traduzem os estados canónicos (`open`/`merged`/`closed`/`all`) para o vocabulário nativo, paginam no cursor de cada API (Link, `X-Next-Page`, continuation token, next-URL), filtram por uma janela inclusiva de datas e param cedo quando podem. O **`list_open_pull_requests(repo)` tornou-se um invólucro fino** na classe base — nenhum call-site mudou, mas o limite de uma página desapareceu.
* 🆕 **Porque é que o filtro é por data do estado:** três das quatro forges devolvem algo mais amplo para um PR integrado (o GitHub não tem estado `merged`, o Azure chama-lhe `completed`, o Bitbucket diz `DECLINED`), por isso cada uma filtra a sua própria resposta contra a data que o estado implica — `merged_at` para merged, `closed_at` para closed, `created_at` nas restantes.
* 🆕 **`supports_merged_dates`:** atributo de classe, `False` no Bitbucket Cloud, que publica o `merged_at` **vazio** em vez de uma data próxima — declarado **antes** da chamada, como o `supports_reviewable_diff`.
* 🆕 **`web_links.py` — o GitPR passou a construir ligações HTML:** até esta janela todas as ligações vinham prontas no `html_url` do payload. As funções puras (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) normalizam qualquer formato de remote — `https`, `ssh://`, `git://`, scp-like, com credenciais, porta personalizada e **subgrupos aninhados do GitLab** — para uma base https e aplicam o padrão de página de cada forge. O Azure DevOps não tem um URL de perfil simples, pelo que os seus contribuidores mantêm o nome de apresentação.
* **`get_pull_request(repo, pr_id)` — método concreto da ABC:** A predefinição lança `ScmNotSupportedError` e cada forge implementa-o — o `list_open_pull_requests` pagina **uma única página** e **não distingue fechado de inexistente**.
* **Cabeçalhos do GitLab Sintetizados:** O `changes[].diff` é um **hunk solto**; o `old_path`/`new_path` montam os cabeçalhos `diff --git a/… / --- / +++`, honrando o `/dev/null`. E o `overflow: true` **lança** em vez de rever metade de um diff truncado.
* **`request_pull_request_reviewers` devolve `list[str]` (quebra de contrato):** Devolve os logins **efetivamente associados**, lidos do corpo do `201`. As subclasses de terceiros têm de ser atualizadas — **item herdado, ainda sem documentação.**
* **Helpers de Apenas Leitura do GitHub:** `get_commit_author_login` (mapeia um SHA à conta ligada ao e-mail do autor) e `get_user_login` (valida/canonicaliza um handle sem gastar um pedido quando não pode ser login).
* **Fail-Fast por Forge:** O Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; o Bitbucket exige `GITPR_SCM_USERNAME`; o `create_issue` no Azure lança `ScmNotSupportedError`.
* **Publicação de Release:** O `provider.create_release()` usado pelo `gitpr release --publish`.
* **Artefactos:** Glossário + ADR-001/ADR-005; a família `docs/scm-multiforge.*.md` em 5 idiomas; 9 ficheiros de teste, **305 cenários** 🆕.

### **22. Subcomando `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Fluxo:** `git log` entre `--since` e `HEAD` → classificação por Conventional Commits → bump semântico sugerido (`--version <x.y.z>` sobrepõe-se) → montagem do changelog → resumo executivo por IA opcional → *anteposição* ao `CHANGELOG.md`.
* 🆕 **O Intervalo Deixou de Vir do `git describe`:** O `describe --tags --abbrev=0` só vê tags alcançáveis a partir do HEAD — neste repositório resolvia para `v0.0.8`, pelo que **todas as releases listavam todas as versões anteriores**. A secção `[1.3.0]` saiu com **124 bullets, 115 deles repetição da 1.2.0**, e o bump sugerido podia ser **inferior** à versão já publicada. A origem do intervalo passou a ser a **secção anterior do próprio changelog**, por meio de duas âncoras: o hash mais recente dessa secção (resolvido num único `git log --no-walk`, ordenado por epoch para sobreviver a fusos horários mistos) e, como recurso, a tag da versão da secção; sem nenhuma das duas, degrada para a última tag **com um aviso visível**.
* 🆕 **`_drop_released()` — a segunda linha de defesa:** qualquer commit cujo hash curto apareça noutra secção de versão é descartado, com contagem. Quando nada sobrevive, a execução **aborta** com `❌ Nothing new to release` em vez de escrever uma secção vazia.
* 🆕 **Cada bullet tornou-se uma ligação:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`. O número do PR vem do `(#123)` final do squash-merge, que permanece no subject; sem forge ou sem token, degrada para texto simples.
* 🆕 **A falha dos PRs integrados por merge commit — corrigida por construção:** o `git log --no-merges` significa que um commit de merge **nunca** chega ao classificador, pelo que o `pr_number` era `None` **por construção** e nenhum changelog chegou a ter um segmento `[#n]`. Uma **segunda passagem sobre o mesmo intervalo** (`git log --merges`) constrói `hash → PR` a partir da ancestralidade (`rev-list <merge>^1..<merge>^2`): os bullets continuam a ser um por commit, agora com a ligação. Um merge que não é um pull request (`Merge branch 'main' into …`) não empresta um número, um commit alcançável por dois merges fica com o mais próximo, e a correção **não é retroativa** — só uma secção recém-cortada ganha ligações.
* 🆕 **Contribuidores resolvidos pela mesma escada dos revisores:** o `get_commit_author_login` primeiro (vê endereços privados que a pesquisa por utilizador não vê), depois o `email_to_handle`, com cache em `~/.gitpr/cache/contributors.json` — **só os sucessos são guardados em cache**, pelo que uma execução limitada pelo rate limit volta a tentar mais tarde.
* 🆕 **A Fronteira da Secção Era um Bug Latente:** O `upsert_changelog(force=True)` substitui da linha `## [x.y.z]` até ao próximo cabeçalho de **nível 2**. Um `###` como fronteira deixava o corpo antigo vivo **acima** do novo; o `### Summary` e o rodapé de contribuidores ficavam para trás.
* **Classificador (`src/commit_classifier.py`)** e **Builder com Secções Traduzíveis (`src/changelog_builder.py`)** — 🆕 o builder ganhou a dataclass `LinkContext` e o classificador ganhou o `pr_number_from_merge()`, que entende merges do GitHub, GitLab, Bitbucket e Azure DevOps.
* **Bump Semântico (`src/version_bump.py`)** e **Publicação** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opções no subcomando.**
* **Quebras declaradas:** O `ReleaseNotesResult` ganhou o campo obrigatório `previous_version`; o `build_release_section` ganhou o parâmetro opcional `links` (retrocompatível).
* **Artefactos:** `docs/release-notes.*.md` — a família já existia em 5 idiomas e foi **reescrita nesta janela** para o motor corrigido 🆕 —, ADR e `tests/test_web_links.py`.
* **Estado da release 1.4.0:** O `CHANGELOG.md` tem a secção `[1.4.0] - 2026-10-12` completa — as cinco funcionalidades desta janela **mais** a nota da 1.3.0, com ligações. É a primeira secção gerada pelo motor corrigido.

### **23. Subcomando `gitpr config` — TUI de Configuração**

* **Ecrã Master-Detail (`src/ui/config_app.py`):** Categorias à esquerda, campos à direita, editados inline. Cabeçalho com procura (`/`) e um contador de pendências; rodapé `F1 Ajuda · F2 Guardar · ^R Restaurar · / Procurar · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schema Declarativo (`src/config_schema.py`) — a única fonte de verdade:** **16 categorias** 🆕 (+2: **Policy** e **Baseline**) e **80 `ConfigField`** 🆕 (+9), dos quais **8 avançados**. Cada campo declara a sua categoria, tipo de widget, `show_if`, validadores, marcadores de versão e ações de descarregamento.
* **Categorias `policy` e `baseline` 🆕:** os campos do pack (ativação e teto de caracteres do contexto injetado) e os quatro do baseline, incluindo o caminho do ficheiro e o interruptor que exige o checksum do lockfile.
* **Secção Skills Alinhada 🆕:** O `SKILL_LABELS` passou de 8 para **11 entradas** — recebeu `tests`, `explain` e `mentor`. Os dois tipos que renderizavam **em branco** na barra lateral passaram a ter rótulo, e as duas afirmações do `test_config_schema.py::TestSkillsSection` **passam**. ✅
* **Filtragem por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` conforme o `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` sob `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` sob `[Azure DevOps]`.
* **Procura Global (`/`):** Encontra correspondências na chave ou no rótulo em todas as categorias e **ignora o filtro de visibilidade**.
* **Validação em Duas Camadas:** **Offline** bloqueia o `F2` com um erro inline; **online** (apenas credenciais alteradas na sessão) corre num worker com timeout de 10s e só bloqueia em `401`/`403`.
* **Restaurar (`Ctrl+R`):** Remove a linha do `.env` em vez de reescrever a predefinição; o `Esc` com alterações pendentes pede confirmação.
* **Secção Skills — a única com âmbito de projeto:** Edita os `.gitpr/skill/*.md` do projeto, resolvidos a partir do diretório de chamada, escrevendo atomicamente e preservando CRLF/LF.
* **Descarregamentos Forçados:** Botões que forçam o novo descarregamento dos smart-excludes, das traduções, dos presets e das thinking words via `force=`.
* **Módulo de Ligações Leve (`src/doc_links.py`):** O `doc_url()` saiu do `core.py` para que a UI possa obter a ligação sem importar os SDKs de IA.
* **Testes:** 5 ficheiros — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9). 🆕 A suíte passou a **estabilizar o ambiente** antes de montar a TUI — sem isso, um `GITPR_BASELINE_*` vazado da shell mudava a árvore de categorias.
* **Dívida conhecida:** O `gitpr -h config` abre a TUI e ignora o `-h` — o gate `if ctx.invoked_subcommand is not None: return` (main.py:678) corre antes do bloco do `help_flag`.

### **24. Registo Geral de Utilização (`src/usage_log.py`)**

* **Uma Linha por Comando:** Escreve em `~/.gitpr/logs/<uuid5>.log`, **um ficheiro por dia**, com o comando, os argumentos, o repositório, o utilizador e o timestamp.
* **Nome Derivado da Data:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` — dois processos concorrentes não podem discordar sobre qual é o ficheiro de hoje.
* **Escrita Síncrona (decisão explícita):** Ao contrário do `log_local_metric`, que usava uma thread daemon e perdia a escrita se o processo terminasse primeiro.
* **Nunca Imprime:** O servidor MCP reserva o stdout para JSON-RPC; o módulo também nunca lança exceções.
* **Um Único Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms em vez de ~150 ms no Windows, em *todas* as execuções.
* **Controlo:** `GITPR_SHOW_LOGS` (predefinição `"true"`); desligado no `tests/conftest.py`.
* **Artefactos:** `docs/usage-log.*.md` em 5 idiomas; `tests/test_usage_log.py` (27 cenários).

### **25. Subcomando `gitpr fix` — Apontamentos de Review como Patches Revisáveis (`src/fix/`)**

* **O Pipeline Real:** último review da cache (`resolve_last_review`) → **uma** chamada de IA → extração e validação de diff unificado → `git apply --check` → classificação determinística → dry-run ou escrita → histórico.
* **Pacote de 8 Ficheiros:** `patch_provenance.py`, `patch_extractor.py` (partilhado com o chat), `patch_safety_classifier.py` (puro, sem I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Classificação Determinística:** Recusa um patch que atravesse mais de um ficheiro ou hunk, toque num caminho sensível, estoure o orçamento de linhas, **apague uma linha com aspeto de chamada**, venha marcado de baixa confiança ou falhe no `git apply --check`. O `--force` **nunca** contorna a aplicabilidade, só a classificação.
* **Escrita Opt-In:** O dry-run é a predefinição; qualquer mutação exige `--apply` (ou uma frase escrita com `--force`).
* **Testes:** 10 ficheiros em `tests/fix/` (**204 cenários**) com fixtures de repositório git real.

### **26. Subcomando `gitpr review-pr` — Review de PR Remoto (`src/review/`)**

* **O que é:** Orquestração, **não** um segundo motor de review — o motor existente, o linter e o renderizador alimentados com um diff que veio de outro sítio.
* **Pacote de 5 Ficheiros:** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Apenas de Leitura por Predefinição:** O `--post-comment` é o **único** caminho que escreve na forge. A tool MCP `review_remote_pr` nem sequer recebe o argumento.
* 🆕 **O baseline é resolvido antes da primeira chamada de rede**, e os avisos do portão são fundidos no relatório/comentário — um PR remoto é julgado pela mesma política que o repositório local.
* 🆕 **O `render.py` ganhou o parâmetro do risco:** o `compose_review_content` / o `render_review_result` **antepõem** a secção `⚡ Risk Assessment` acima dos alertas do linter, quando o `GITPR_RISK_INCLUDE_IN_REVIEW` está ligado.
* **Artefactos:** `docs/review-pr.md` + `.pt_br.md` (os 3 idiomas restantes **continuam pendentes**), `docs/code-review-ia.*.md` (5), ADR-005 e `glossary-review-pr.md`.
* **Testes:** 4 ficheiros em `tests/review/` (**97 cenários**).

### **27. Resolução de Identidade do Revisor (`src/reviewer_resolution.py`)**

* **O Bug (duas falhas silenciosas encadeadas):** (1) **Prefill vazio** — os `handles` só com o `email_to_handle()`; (2) **Associação não verificada** — valores repassados *verbatim*, o GitHub respondia **201 sem associar ninguém** e o `_request()` retornava sem lançar.
* **Módulo:** Um plano, sem I/O próprio, **que nunca lança**. O `resolve_candidates()` (antes da TUI) e o `resolve_typed_reviewers()` (no momento da associação); o `match_candidate()` faz correspondência exata e normalizada por login, nome ou e-mail.
* **Escada de Resolução:** handle já conhecido (sem qualquer pedido) → correspondência exata com uma pessoa sugerida → lookup por e-mail → validação do login na forge.
* **O que não resolve nunca é enviado:** Sai em `ResolutionOutcome.dropped` como `(valor, motivo_i18n)` e é descartado com um aviso visível.
* **A Mesma Escada Serve a Release 🆕:** O `release_engine` reutiliza o `get_commit_author_login` + o `email_to_handle` para transformar e-mails de commit em logins clicáveis.
* **Testes:** `tests/test_reviewer_resolution.py` (18) + ampliações em `test_github_provider.py` (72), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) e `test_main_suggest_reviewers.py` (8).

### **28. Subcomando `gitpr demo` — Visita Guiada sobre Exemplos Gravados (`src/demo/`)**

* **O que é:** Uma visita interativa que mostra as três saídas principais do GitPR — mensagem de commit, review e descrição de PR — **sem chave de API, sem repositório Git e sem rede**.
* **Pipeline Real, Resposta Falsa:** O `FakeAIProvider` espelha o `src.ai_providers.call_ai_model` **argumento a argumento**; o `demo_pipeline()` faz patch do `src.core` e do `src.metrics` para que o pipeline de produção corra **intocado** e apenas a origem da resposta seja trocada.
* **O Isolamento É um Requisito, Não um Detalhe:** A visita **não pode** tocar no `~/.gitpr` do utilizador nem na rede — garantido pelo `tests/demo/conftest.py` (proibição de rede autouse, com uma exceção de loopback para o event loop proactor do Windows).
* **Pacote:** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (dois exemplos localizados nos 5 idiomas). Os cenários são **módulos Python, não JSON**, para viajarem dentro do wheel.
* **TUI:** `src/ui/demo/` com um modal de ajuda; o diff é cercado como ```` ```diff ````.
* **CLI:** `gitpr demo` com `--scenario`, `--lang` e `--no-tui`.
* **Testes:** 6 ficheiros em `tests/demo/` (**155 cenários**).
* **Artefactos:** `docs/demo.*.md` em 5 idiomas, `README.*` (5) e as métricas de exemplo da visita.

### **29. Selo GitPR e o Comando `gitpr badge` (`src/branding/`)**

* **O que É:** Um selo público que atesta que um pull request passou por uma verificação de qualidade local, e um comando que imprime o snippet de adoção para o README do projeto.
* **URL Estático de Propósito:** É um Markdown do shields.io que o GitPR **nunca** vai buscar — publicar um pull request não pode passar a depender de um terceiro estar de pé.
* **Medição Honesta (`badge_data.py`):** O `collect_linter_counts()` devolve `None` quando não há regras de linter configuradas, porque uma lista vazia significa "nada foi verificado", não "nada foi encontrado".
* **O Selo Nunca Bloqueia uma Publicação:** Todos os caminhos de falha são apanhados e degradam para "sem selo".
* **Ponto de Injeção Único:** O `attach_pr_badge()` no `src/main.py`. Anexação idempotente por marcador.
* **Comando `gitpr badge`:** `--readme` e `--style`. **Só imprime** — o README nunca é modificado.
* **Configuração:** `GITPR_BADGE` (predefinição `true`) — um opt-out **apenas de leitura**.
* **Testes:** 7 ficheiros em `tests/badge/` (**83 cenários**).
* **Artefactos:** `docs/badge.*.md` em 5 idiomas e o questionário dedicado.

### **30. Subcomando `gitpr split` — Commits Atómicos por Hunk (`src/split/`)**

* **O que É:** Uma árvore de trabalho com várias preocupações deixa de se tornar um commit-blob. O `split` lê o diff não consolidado, pede à IA que particione os hunks por **intenção lógica** e propõe um commit atómico por preocupação. A árvore nunca é reescrita: os ficheiros terminam byte a byte iguais ao início.
* **Pacote de 6 Ficheiros:** `split_plan.py`, `hunk_parser.py` (puro), `hunk_grouper.py`, `generate_split_plan.py` (apenas de leitura), `apply_split_plan.py` (**o único que muta**) e `__init__.py`.
* **A Garantia de Árvore Intacta É Estrutural:** O `selective_stager.py` pré-valida **todos** os grupos contra um índice temporário no HEAD e usa um `GIT_INDEX_FILE` descartável.
* **Destrutivo para o Índice, Não para a Árvore:** O `--apply` reinicia o índice para o HEAD antes de indexar, pelo que o que o utilizador já tinha indexado fica por indexar — documentado explicitamente.
* **Configuração:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) e `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Valores zero ou negativos são **ignorados a favor da predefinição**.
* **Testes:** 6 ficheiros em `tests/split/` (**101 cenários**).
* **Artefactos:** `docs/split-command.md` — 🆕 **agora em 5 idiomas** (os 3 que faltavam chegaram em 2026-09-24), ADR-006, `glossary-gitpr-split.md`, spec/plano e questionário.

### **31. Varredura de Segredos Embutida (`src/security_ruleset.py`)**

* **O que é:** Sete regras que correm em **todas** as invocações do linter, fundidas no fim do `load_linter_rules()` depois das regras do projeto e dos plugins.
* **As Sete Regras:** cinco `error` bloqueantes — ID de chave de acesso da AWS, token do GitHub, token do Slack, chave de API do Google e bloco de chave privada — e dois `warning`: URL de ligação a base de dados com credenciais e atribuição genérica de credencial (`password = "…"`), esta última atrás de um filtro de placeholder (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Porque no Pacote e Não no Template:** Um portão de segredos tem de se comportar **igual** em todas as máquinas e em todos os CI, por isso as regras vivem no pacote e não podem ser substituídas por `--skill` nem reescritas pelo wizard.
* **Lacunas de Cobertura (v1) — confirmadas como ainda abertas 🆕:** a atribuição **sem aspas** não é apanhada (`API_KEY=abc123`, o formato `.env`); faltam os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-`; e não há fronteira à esquerda no nome da chave.
* **Configuração:** `GITPR_LINTER_SECURITY` (predefinição `true`; opt-out fail-open) e `GITPR_LINTER_SECURITY_DISABLED_RULES` (separadas por `;`).
* **Testes:** `tests/test_security_ruleset.py` (**61 cenários**).
* **Artefactos:** ADR-007, `glossary-gitpr-secret-scanning.md`, spec/plano/questionário e 3 relatórios de tarefa.

### **32. Bridges SAST — Semgrep, Gitleaks e Bandit (`src/infrastructure/linter/external/`)**

* **O que é:** Uma camada **opt-in** que liga scanners de segurança de terceiros ao pipeline de linter existente.
* **Âmbito Delimitado:** Correm **apenas** quando ativados e **apenas** sobre os ficheiros tocados pelo diff.
* **Contrato Comum:** `ExternalLinterBridge` (ABC) com execução endurecida de `subprocess` (`shell=False`, timeout estrito, `stdin=DEVNULL`, UTF-8 com `errors='replace'`). 🆕 O modelo `NormalizedFinding` **saiu daqui** para `src/domain/finding/`, e o `base_bridge` passou a **re-exportá-lo** para não quebrar os imports existentes.
* **A Deduplicação É o Ponto:** O `deduplicate_secret_findings()` funde apontamentos do Gitleaks e do conjunto de regras regex no mesmo ficheiro e linha numa **única** entrada confirmada como `[Gitleaks + Regex]`.
* **Segredos Mascarados:** O `mask_secret_value()` garante que o valor nunca chega ao apontamento, ao log ou à telemetria.
* **Resolução em Três Camadas:** O `load_sast_config()` lê as predefinições → `.gitpr.linter.yml` (bloco `sast`, com fallback para `linter.external`) → variáveis `GITPR_SAST_*`. **Todas com predefinição `false`**.
* **Degradação Graciosa:** Uma ferramenta ativada mas ausente do `PATH` emite um aviso e a execução continua.
* **Testes:** 4 ficheiros em `tests/infrastructure/linter/external/` (**14 cenários**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + **`tests/domain/linter/test_structured_findings.py` (44)** 🆕, que cobre a passagem dos apontamentos normalizados pelo motor de lint.

### **33. Subcomando `gitpr tests generate` — Geração de Suíte por IA (`src/domain/tests_generation/`, `src/application/`)**

* **O que é:** Gera ficheiros de teste completos e executáveis a partir do diff atual, de um ficheiro específico ou de um apontamento de review, **respeitando a convenção do repositório** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Camada de Domínio:** `TestFramework` (enum) e as dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest`; o `detect_test_framework()` deteta pelos ficheiros de configuração, manifests e conteúdo do diretório de testes; o `build_test_scaffold()` calcula o caminho convencional de destino por framework.
* **Camada de Aplicação (`generate_test_file.py`):** Orquestra a deteção, a resolução do scaffold, o prompt, a IA, o parsing do JSON e a escrita opcional. O `validate_test_syntax()` corre o toolchain local (`php -l`, `node --check`, `python -m py_compile`) — uma falha de validação é um **aviso**, não um erro.
* **Apresentação:** Grupo `tests` com `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); o dry-run é a predefinição e sobrescrever um teste existente pede confirmação com **No** pré-selecionado. O chat delega o `/tests` ao **mesmo** caso de uso.
* **Dívida fechada 🆕:** o tipo `tests` está em `SKILL_LABELS` e em `mcp_server.SKILL_FILES`, e as 17 chaves `__()` que não existiam foram traduzidas. **Continua sem tópico em `docs/`** — só um template de skill e spec.

### **34. Subcomando `gitpr explain` e a Flag `--explain` — Guia do Revisor (`src/domain/pr/`)**

* **O que é:** Um guia centrado em quem vai **rever** — o que muda, porque muda, onde focar e qual o risco de regressão.
* **Camada de Domínio (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, o renderizador `build_explain_markdown()` e o `parse_explain_payload()`, que tolera saída não-JSON e **deteta placeholders** `[FILL]`/`[TODO]` para sinalizar evidência insuficiente em vez de apresentar um guia oco como completo.
* **Camada de Aplicação (`generate_pr_explanation.py`):** Resolução do provedor, validação da chave, carregamento do skill context (`explain`), prompt, invocação e parsing.
* **Duas Portas de Entrada:** `gitpr explain` (`--provider`, deteção de diff em falta antes de qualquer IA) e a flag `--explain` na CLI raiz, que anexa o guia ao corpo da descrição do PR **e** ao payload JSON.
* **Configuração:** `GITPR_EXPLAIN_BY_DEFAULT` (predefinição `false`).
* **Dívida fechada 🆕:** o `explain` está em `SKILL_LABELS` e em `mcp_server.SKILL_FILES`, e as chaves foram traduzidas. **Continua sem tópico em `docs/`.**

### **35. Suíte Determinística e CI (`.github/workflows/tests.yml`)**

* **O que é:** O workflow que corre a suíte em **Python 3.10** (o piso declarado no `pyproject.toml`) e **3.13**, com `fail-fast: false`.
* **`tests/conftest.py` Hermético:** Fixa `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` e o `LANG_VERSION` na versão do código, além de `GITPR_SHOW_LOGS=false` e `GITPR_SKIP_UPDATE_CHECK=true`.
* **A Ordem Importa:** O `src.updater` é importado **antes** de as variáveis de idioma serem definidas, porque o `i18n` captura o `os.environ` em `AMBIENT_ENV_KEYS` no import.
* **Estabilidade dos Testes de Worker:** O `test_config_app.py` e o `test_metrics.py` aguardam por `workers.wait_for_complete()` antes de afirmar.
* **Dependência do Workflow:** O runner limpo não tem `~/.gitpr`, e o `test_demo_isolation.py` afirma que o perfil existe — o job cria o diretório e um `.env` vazio explicitamente.
* 🆕 **Nesta janela a suíte cresceu 48% e o cenário de falha mudou de lado:** as três afirmações que existiam para denunciar dívida de registo (`TestSkillsSection`, `TestNoMissingKeys`, `TestSkillRegistryAgreement`) **passam** — o mecanismo de deteção funcionou e foi pago no mesmo ciclo em que foi criado.

### **36. Ledger SQLite de Telemetria (`src/ledger.py`, `src/infrastructure/git/identity.py`) 🆕**

* **O Problema Não Era o Formato, Era o Join:** os eventos eram escritos como ficheiros JSON por comando, a partir de uma **thread daemon** (que um processo de vida curta — um hook — pode não sobreviver), e depois reconectados à cache de IA por uma correspondência heurística de `(repo, branch, ação, minuto)` que dependia de **três mapas comando→pasta divergentes**. O sintoma era o `tokens_actual` **sempre a `0`** para `fullreview`/`filereview` e perda de dados entre repositórios na exportação.
* **A Escrita É Síncrona, e Essa É a Decisão:** O `log_command_metric()` resolve o contexto git **uma vez por processo** e escreve a linha de imediato, em vez de a entregar a uma thread que um hook pode não sobreviver. Os tokens, o modelo e o provedor são **copiados dos metadados do provedor no momento da escrita** — já não há enriquecimento a posteriori.
* **Schema Versionado:** `SCHEMA_VERSION` + `PRAGMA user_version`, chaves UUID (não inteiros sequenciais, para que o `merge` de dois ledgers não colida), WAL + `synchronous=FULL`, e `LedgerVersionError` para recusar um bundle mais recente **antes** do `ATTACH` — o ledger local nunca fica meio-importado.
* **Invariante "Nunca Nasce Vazio":** O `ensure_ledger()` só cria a base de dados **em conjunto** com a absorção dos JSON legados (originais movidos para `~/.gitpr/metrics_legacy/`, marcador de recusa, caminho silencioso em modo não interativo).
* **Uma Janela, Um Significado:** `--since`/`--until`/`--days` (inclusivos nos dois extremos) estreitam **todas** as superfícies de leitura. A secção de ciclo é a exceção — pergunta à rede, por isso assume os últimos 30 dias e **nomeia a janela no seu próprio cabeçalho** em vez de estreitar em silêncio.
* **Cada Métrica É Calculada Uma Vez e Renderizada Uma Vez:** os pares `(style, text)` (`TITLE`/`PLAIN`/`WARN`) fazem a CLI e o dashboard Textual contar a **mesma** história; uma secção sem nada a dizer é omitida, nunca impressa vazia.
* **Custo com Tabela Embutida e Override:** preços de lista dos quatro modelos que o GitPR distribui (`deepseek-v4-pro`, `deepseek-v4-flash`, `gemini-2.5-pro`, `gemini-2.5-flash-lite`), sobrepostos por `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Ambas as taxas são obrigatórias** — tarifar metade de um modelo a um zero não configurado **subestima** a conta, por isso o modelo é reportado apenas em tokens. O `ollama`/`local` custa zero; o `GITPR_METRICS_CURRENCY` rotula o total e, quando não é USD, **descarta** a tabela embutida em vez de rotular um preço em dólares com outro nome. Um total que deixa modelos de fora lê `Total (partial)`.
* **`modules` Normalizado:** os caminhos do diff tornam-se dois segmentos (`src/fix`), `(root)` para um ficheiro na raiz e `(no module)` para os comandos que nunca tiveram diff (linter, blame, hooks).
* **Transporte por Bundle:** O `bundle` escreve a janela como um `.db` autónomo (o schema e o `user_version` viajam com ele) e o `merge` anexa-o e insere por UUID, migrando um bundle antigo para cima e recusando um mais recente.
* **`cache_backfill` Fica de Fora:** as linhas reconstruídas a partir da cache de IA são marcadas e **excluídas dos agregados de execução** — a chave da cache é o prompt, por isso contam prompts distintos, não execuções.
* **Identidade Git sem Forge (`identity.py`):** rótulo de repo, branch e autor numa **única** chamada de subprocesso, válido em qualquer forge — e deliberadamente **sem** construir um `ScmProvider`, que seria caro em todos os comandos de telemetria.
* **A Secção de Ciclo É a Única que o Ledger Não Responde:** lista PRs integrados via `ScmProvider.list_pull_requests` e mede `created_at → merged_at`, com códigos de estado explícitos (ok/empty/unavailable/unsupported). Todas as falhas degradam para **uma linha** — `Not read: <razão>`, sem data de merge no Bitbucket, nada integrado na janela — nunca um stack trace e nunca um número inventado.
* **Apresentação Completa:** grupo `gitpr metrics` com 8 subcomandos, um dashboard que lê o ledger numa thread de trabalho e o assistente de migração de três opções.
* **Configuração:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY` e `.gitpr/metrics/` no `.gitignore`.
* **Testes:** O `tests/test_metrics.py` saltou de 34 para **164 cenários**; o `tests/scm/*` ganhou a paginação do `list_pull_requests` e o `within_window` em todas as forges; o `test_commit_classifier.py` (30) cobre o `pr_number_from_merge`.
* **Artefactos:** ADR-008, `glossary-metrics-telemetry.md`, `docs/plans/develop_natan/20260927_metrics_audit_plansfacts.md`, questionário e **`docs/metricas-telemetria.*.md` reescrito nos 5 idiomas**. Vocabulário canónico em `docs/plans/glossary-metrics-telemetry.md` (conforme o CLAUDE.md).

### **37. Subcomando `gitpr mentor` e a Flag `--mentor` — Modo Mentor (`src/domain/mentor/`) 🆕**

* **O que é:** Um review diz *o que* está errado e quase nunca *porque é que aquilo importa*. O Modo Mentor enriquece os apontamentos com **o que se está a passar**, **porque importa**, uma **analogia** do dia a dia e os **conceitos a estudar** — para quem está a começar, o valor de um review está no que ensina depois de apontar.
* **Camada de Domínio Pura (`mentor_explanation_builder.py`):** sem I/O, converte o payload da IA num `MentorExplanation` validado e impõe **honestidade epistémica** em três frentes: sem evidência suficiente, a explicação **diz isso** em vez de preencher o vazio; os URLs alucinados são **removidos** dos ponteiros "learn more"; e os IDs de apontamento desconhecidos são **rejeitados**, preservando a ordem estrita dos apontamentos originais.
* **Camada de Aplicação (`generate_mentor_explanation.py`):** recolhe candidatos (`collect_candidates`), **lotes de no máximo 10 apontamentos** por execução, ordenados por severidade, reutilizando a cache MD5 por hash de prompt — voltar a correr o mentor sobre um review inalterado **não custa nada**.
* **Duas Portas de Entrada:** `gitpr mentor [--finding <id>] [--provider <nome>]` corre sobre o **último review persistido** (por isso tem de vir depois de um `-r`/`-f`), e a flag `--mentor` anexa a secção ao review normal, com um aviso quando usada numa ação que não a suporta.
* **Opt-In Estrito:** `GITPR_REVIEW_MENTOR_MODE` (predefinição `false`) e `GITPR_MENTOR_INCLUDE_ANALOGY` (predefinição `true`), editáveis na TUI de configuração. Ligar custa chamadas de IA adicionais — é por isso que a predefinição é desligada.
* **Skill e Registo:** O `.gitpr.mentor.md` registado em `SKILL_FILES_BY_TYPE`, em `mcp_server.SKILL_FILES` e em `SKILL_LABELS`, com templates em **5 idiomas**.
* **Testes:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) e `tests/test_mentor_cli.py` (4).
* **Artefactos:** `docs/mentor-mode.*.md` em **5 idiomas**, ADR-009, `glossary-gitpr-mentor.md`, spec e relatórios de tarefa.

### **38. Subcomando `gitpr risk` — Pontuação de Risco Determinística (`src/domain/risk/`) 🆕**

* **O que é:** Uma pontuação de **0–100** com nível `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculada **sem IA** a partir de sinais locais, para que o revisor saiba onde olhar primeiro — e para que o mesmo diff receba o mesmo número duas vezes.
* **Camada de Domínio Pura (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums e dataclasses (`RiskLevel`, `RiskSignal`, `RiskEvidence`, `FileRisk`, `PullRequestRisk`); pesos, limiares, padrões de caminhos críticos, correspondência por glob e deteção de teste/não-executável carregados de YAML; cálculo por ficheiro com **clamp de 0–100** e agregação 50/30/20.
* **A Fórmula É Declarada, Não Aprendida:** a agregação combina **50%** o ficheiro de maior risco, **30%** a média ponderada por linhas e **20%** evidência crítica. Acima disso há **regras de elevação obrigatória**: uma pontuação ≥ 80 sobe para pelo menos `HIGH`; um bloqueador ou uma pontuação ≥ 90 força `CRITICAL`. Uma pontuação sem regra de elevação é um número que a equipa aprende a ignorar.
* **Adaptadores de Infraestrutura:** O `risk_history_reader.py` lê o `git log` com segurança e extrai commits, correções de bugs e reverts numa janela de **90 dias**; o `test_matcher.py` corresponde ficheiro de teste ↔ ficheiro de produção por convenção, em vários layouts de linguagem.
* **Caso de Uso (`calculate_risk.py`):** correlaciona as secções do diff parseado, os apontamentos, a correspondência de testes e o histórico em `FileRisk`/`PullRequestRisk`, **degradando com um aviso** quando um sinal não está disponível. Aplica o policy pack por cima da configuração base, quando há um ativo.
* **Três Consumos:** o comando `gitpr risk` (`--file`, `--format text|json`, `--base`, `--no-history`), a secção `⚡ Risk Assessment` anexada aos reviews e ao review de PR remoto, e o **prompt da IA** — onde a pontuação entra como contexto **read-only**, em formato compacto.
* **Opt-Out:** `GITPR_RISK_INCLUDE_IN_REVIEW` (predefinição `true`) — como a predefinição o liga, a secção passa a aparecer nos reviews de quem atualizar; desligá-la é uma variável. **Mudança de comportamento declarada.**
* **Configuração por Projeto:** O `templates/gitpr.risk.yml` (ou `.gitpr/skill/gitpr.risk.yml`) sobrepõe pesos, limiares e caminhos críticos. Pesos negativos só são aceites para sinais **mitigadores** como o `test_present`.
* **Testes:** `tests/domain/risk/` (2 ficheiros, 10 cenários), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) e `tests/test_risk_cli.py` (4).
* **Artefactos:** `docs/risk-scoring.*.md` em **5 idiomas**, ADR-010, `glossary-gitpr-risk-scoring.md` e relatórios de tarefa (incluindo um do Gemini).

### **39. Subcomando `gitpr policy` — Policy Packs Versionados (`src/domain/policy/`, `src/infrastructure/policy/`) 🆕**

* **O que é:** Um **pack YAML versionado** que transporta, num único artefacto, as decisões de qualidade de um repositório: skills, regras de linter, severidades, caminhos críticos, pesos de risco e convenções de PR/commit. Hoje cada repositório configura isto à mão — o que torna o comportamento difícil de reproduzir, partilhar e **travar**. O pack torna a configuração declarativa, versionada, componível e reproduzível.
* **Camada de Domínio (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`):** parsing do manifest com um **schema fechado** (uma chave desconhecida é um erro, não um aviso), verificação de compatibilidade e de credenciais, **checksum SHA-256** e um resolvedor de grafo com precedência e composição.
* **Camada de Infraestrutura:** O `local_policy_repository.py` resolve o pack a partir de **três origens** (`.gitpr/policies`, `~/.gitpr/policies` e o pacote embutido) e o `policy_pack_loader.py` carrega-o.
* **Casos de Uso:** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` e `validate_policy_pack`.
* **Quatro Packs Embutidos:** `laravel-quality`, `node-quality`, `php-security` e `vue-quality`, cada um com `policy.yml` e `linter.yml`, distribuídos via **`package-data`** — um pack que não viaja no wheel é um pack que só existe na máquina de quem o escreveu.
* **Consumo Integrado em Quatro Pontos:** `calculate_risk` (política por cima da configuração base), `config.load_linter_rules` (fusão em camadas + sobreposição de severidade), `core.get_skill_context` (injeta o contexto do pack) e a **chave de cache do PR** (`cache_scope`) — dois repositórios sob políticas diferentes não podem partilhar uma resposta de IA.
* **CLI:** `gitpr policy {list, validate, show, use, off, install, init}`, com confirmação (`--yes`/`--force`), deteção de stack no `init` e registo da escolha em `.gitpr/policy.lock.yml`.
* **Configuração:** `GITPR_POLICY_ENABLED` e `GITPR_POLICY_CONTEXT_MAX_CHARACTERS` (o teto do contexto injetado no prompt), ambos na nova categoria `policy` do schema.
* **Retrocompatibilidade:** sem pack ativo, o comportamento é **preservado** — coberto por um teste de integração, não por inspeção.
* **Nova Dependência:** `packaging>=23.0` (SemVer/PEP 440), declarada no `Pipfile` e no `pyproject.toml`.
* **Testes:** `tests/domain/policy/` (4 ficheiros, 126 cenários), `tests/application/use_cases/` (4 ficheiros de policy, 89), `tests/test_policy_cli.py` (26) e `tests/integration/test_policy_effect_on_review_linter_risk.py` (31 — efeitos reais num repositório real, com uma guarda de rede autouse).
* **Artefactos:** `docs/policy-packs.*.md` em **5 idiomas**, ADR-011, `glossary-policy-packs.md`, spec/plano/questionário.

### **40. Subcomando `gitpr baseline` — Baseline e Supressões Auditáveis (`src/domain/baseline/`) 🆕**

* **O que é:** Os linters e os reviews voltam a reportar os mesmos apontamentos pré-existentes a cada execução, e o trabalho de distinguir a saída de cabeça sobra para o revisor. O baseline regista o **instantâneo dos apontamentos que o repositório já conhece** em `.gitpr/baseline.json`, cria a impressão digital de cada um de forma estável entre execuções e deixa o pipeline classificar o que é novo — **só o `new` bloqueia**.
* **Camada de Domínio (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`):**
  * **Impressão digital estável:** identidade da regra, caminho normalizado, linhas e **sha256 da linha com o whitespace colapsado** — a linha em bruto **nunca** é armazenada, por isso o baseline não é um ficheiro onde segredos vazam por acidente.
  * **Manifest canónico:** JSON canónico com checksum, validação que recusa uma chave desconhecida, uma versão de schema/impressão digital fora do intervalo suportado, um invariante de supressão/dívida quebrado, uma impressão digital duplicada e um intervalo de linhas inválido.
  * **Comparador:** classificação, contagens por estado e resolução de entradas — `resolved` e depois `back` lê-se como **novo**, que é a leitura conservadora.
  * **Política de Supressão:** supressões, dívida aceite, sobreposições e validadores, com correspondência em camadas (entrada × sobreposições × policy pack) por **especificidade e precedência**, mais o acompanhamento da dívida vencida.
* **Cinco Casos de Uso:** `baseline_gate` (o ponto de entrada único do pipeline — carrega, classifica, anota e compõe as camadas de entrada, local e de sobreposição do pack), `baseline_summary` (vista JSON **read-only** para IDEs e agentes MCP), `compare_against_baseline`, `create_baseline` (criação/atualização com proveniência, checksums e **preservação das decisões humanas**) e `suppress_finding` (suprime/remove nos âmbitos de apontamento, linha, ficheiro e regra).
* **Integração com o Policy Pack:** o pack pode transportar um bloco `baseline:`, validado pelos **mesmos** validadores, composto pelo resolvedor como uma camada etiquetada e reportado pelo `policy validate`.
* **O Repositório É o Único que Escreve:** O `local_baseline_repository.py` é o **único** leitor/escritor de `.gitpr/baseline.json` e `.gitpr/baseline.overrides.yml`, com escrita atómica, um portão de checksum/versão e um caminho de reparação.
* **Configuração:** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` e `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`, na nova categoria `baseline`.
* **Opt-In e No-Op:** desligado ou sem baseline existente, a funcionalidade **não lê nenhum ficheiro e não imprime nada** — quem não a usa não vê mudança.
* **⚠️ A Exceção Deliberada:** um ficheiro de baseline que existe mas **não pode ser aplicado** faz o lint, o review, o full review, a auditoria de ficheiro, o review remoto e o risk **abortarem com exit code 1**. Um baseline que falha em silêncio é um baseline que "faz desaparecer" apontamentos sem ninguém saber — abortar é a escolha segura.
* **Alcance:** CLI (6 subcomandos, todos com `--format json`), MCP (tool `get_baseline_status` + recurso `baseline://summary`), review de PR remoto (resolvido **antes da primeira chamada de rede**) e o fluxo de lint, cujo exit code passou a seguir o bloqueio ciente do baseline.
* **O Refactor que a Funcionalidade Exigiu:** O `NormalizedFinding` tornou-se um tipo canónico em `src/domain/finding/` (com `snippet_hash` e `rule_version`), importado por todas as bridges — sem isso, o mesmo apontamento teria uma forma por ferramenta e a impressão digital não o reconheceria.
* **Testes:** `tests/domain/baseline/` (5 ficheiros, **126 cenários**), `tests/application/use_cases/` (5 ficheiros, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) e `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34).
* **Artefactos:** `docs/baseline-suppressions.*.md` em **5 idiomas**, ADR-012, `glossary-baseline.md`, spec/plano/questionário e relatório de tarefa.

---

## **📊 Testes e Qualidade**

| Ficheiro de Teste | Cenários | Foco |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por intervalo de linhas num ficheiro |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidade, commits, duração |
| `tests/test_baseline_cli.py` | 54 🆕 | Grupo `baseline`: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Builder de changelog: secções, cabeçalhos traduzíveis, contribuidores, **ligações e datas** |
| `tests/test_chat_backend.py` | 19 | Memória do chat, persistência, comandos slash |
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
| `tests/test_i18n.py` | 20 | Paridade entre idiomas, chaves em falta/órfãs, identidade — **zero chaves em falta** ✅ |
| `tests/test_install_wizard.py` | 3 | Assistente de instalação interativo |
| `tests/test_issue_engine.py` | 4 | Rascunho estruturado de issue |
| `tests/test_linter_metrics.py` | 4 | Métricas do linter: erros, avisos, duração |
| `tests/test_linter_presets.py` | 5 | Presets do linter: resolução e novo descarregamento forçado |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` na CLI e na ajuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Modelos de prompt do MCP e fallback de idioma |
| `tests/test_mcp_server.py` | 110 | Tools MCP (16), recursos (19), anotações, patching, CLI direta, offload — **concordância do registo de skills** ✅ |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real via subprocesso + stdio JSON-RPC |
| `tests/test_mentor_cli.py` | 4 🆕 | CLI do `mentor` e opt-in `--mentor` |
| `tests/test_metrics.py` | 164 | **Ledger**: escrita síncrona, exportação idempotente, bundle/merge, versionamento, migração, renderizadores, custo, qualidade, módulos, provedores, ciclo |
| `tests/test_net_timeouts.py` | 12 | Timeouts de rede/IA — alinhados com a predefinição real de 180s |
| `tests/test_plugins.py` | 17 | Descoberta de plugins, fusão de regras do linter, prompts do MCP |
| `tests/test_policy_cli.py` | 26 🆕 | Grupo `policy`: list/show/validate/use/off/install/init, confirmação e lockfile |
| `tests/test_pr_publish_app.py` | 45 | TUI do PR Publisher: ecrãs, fluxos, revisores sugeridos, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de erro do linter: abortar, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save e carga JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opções, ajuda com epílogo documentado |
| `tests/test_release_engine.py` | 53 | **Corte ancorado no changelog, `_drop_released()`, ligações, mapa de PRs por merge commit**, publicação |
| `tests/test_reviewer_resolution.py` | 18 | Escada de resolução, rejeição de um nome parcial, dedup, tolerância a falhas |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugestão de revisores (ranking, exclusão, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 🆕 | CLI do `risk`: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 61 | Matriz do conjunto de regras embutido: regex, marcadores, nível, wildcard, fusão, traduções |
| `tests/test_skill_command.py` | 10 | Descarregamento e validação de modelos de skill |
| `tests/test_skill_context.py` | 18 | `get_skill_context()` com `quiet=True`, fallbacks, registo de skills |
| `tests/test_smart_excludes.py` | 15 | Filtro smart de pathspec e novo descarregamento forçado |
| `tests/test_suggest_reviewers.py` | 17 | Revisores sugeridos no fluxo de PR (integração, dica de `no_login`) |
| `tests/test_tests_command.py` | 2 | CLI do grupo `tests` e do `generate` |
| `tests/test_thinking_words.py` | 5 | Carregamento, análise com o separador `;` e recarregamento forçado |
| `tests/test_updater.py` | 23 | Portão do PyPI: análise de versões, cache diária, fetch, decisões do portão, ligação à CLI |
| `tests/test_usage_log.py` | 27 | Registo de utilização: nome derivado da data, escrita síncrona, silêncio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semântico: major/minor/patch, alvos e validação |
| `tests/test_web_links.py` | 26 🆕 | Normalização de remote (https/ssh/scp/credenciais/porta) e padrões de URL por forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 🆕 | Ativação do pack, lockfile, recusas |
| `tests/application/use_cases/test_baseline_gate.py` | 40 🆕 | Portão: carregamento, classificação, anotação, camadas e aborto |
| `tests/application/use_cases/test_baseline_summary.py` | 12 🆕 | Vista apenas de leitura do baseline para MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 🆕 | Correlação de diff/apontamentos/testes/histórico, degradação |
| `tests/application/use_cases/test_create_baseline.py` | 30 🆕 | Criação/atualização com proveniência e preservação das decisões |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 🆕 | Lotes, severidade, acerto na cache, chave em falta |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 2 | Caso de uso do explain |
| `tests/application/use_cases/test_generate_test_file.py` | 4 | Caso de uso de geração de testes |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 🆕 | Instalação do pack, origens e conflitos |
| `tests/application/use_cases/test_suppress_finding.py` | 29 🆕 | Supressão/remoção nos quatro âmbitos |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 🆕 | Validação: schema fechado, checksum, compatibilidade |
| `tests/badge/test_badge_append.py` | 9 | Anexação idempotente do selo ao corpo do PR |
| `tests/badge/test_badge_builder.py` | 19 | Composição do Markdown do shields.io, escaping, regra de cor, estilo |
| `tests/badge/test_badge_cli.py` | 15 | Comando `gitpr badge`: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Contagem de alertas; `None` sem regras configuradas |
| `tests/badge/test_badge_offline.py` | 6 | Guarda offline (rede proibida, exceto loopback) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` em todos os caminhos de publicação |
| `tests/badge/test_badge_paths.py` | 10 | Ponto de injeção único nos dois publicadores (TUI e `--no-edit`) |
| `tests/demo/test_demo_app.py` | 22 | TUI da visita: navegação, ecrãs, modal de ajuda |
| `tests/demo/test_demo_isolation.py` | 14 | A visita não toca no `~/.gitpr` nem na rede |
| `tests/demo/test_demo_runner.py` | 33 | Máquina de estados `DemoState`: avançar, recuar, concluído, modo texto |
| `tests/demo/test_demo_scenarios.py` | 47 | Integridade dos cenários gravados (aritmética de hunks) nos 5 idiomas |
| `tests/demo/test_demo_text_mode.py` | 18 | Saída em texto simples (`--no-tui`) para CI e gravações |
| `tests/demo/test_fake_ai_provider.py` | 21 | Contrato do provedor falso: espelha o `call_ai_model` argumento a argumento |
| `tests/domain/baseline/test_baseline_comparator.py` | 21 🆕 | Classificação, contagens por estado, `resolved`→`new` |
| `tests/domain/baseline/test_baseline_fingerprint.py` | 12 🆕 | Digest de referência, linha em bruto nunca armazenada, estabilidade |
| `tests/domain/baseline/test_baseline_manifest.py` | 26 🆕 | JSON canónico, checksum, chaves desconhecidas, invariantes |
| `tests/domain/baseline/test_baseline_types.py` | 15 🆕 | Estados persistidos, âmbitos e precedência |
| `tests/domain/baseline/test_suppression_policy.py` | 52 🆕 | Camadas, especificidade, sobreposições, dívida vencida |
| `tests/domain/finding/test_finding_types.py` | 3 🆕 | Tipo canónico de apontamento: `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Formatação uniforme e deduplicação `[Gitleaks + Regex]` |
| `tests/domain/linter/test_structured_findings.py` | 44 🆕 | `findings_out`: apontamentos estruturados e o contador de linhas do lado novo |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 🆕 | Builder puro: honestidade epistémica, URLs removidos, ordem preservada |
| `tests/domain/policy/test_policy_checksum.py` | 15 🆕 | SHA-256 do pack e deteção de edição manual |
| `tests/domain/policy/test_policy_compatibility.py` | 22 🆕 | Compatibilidade de versão e credenciais |
| `tests/domain/policy/test_policy_manifest.py` | 42 🆕 | Schema fechado, bloco `baseline:`, erros de manifest |
| `tests/domain/policy/test_policy_resolver.py` | 47 🆕 | Precedência, composição em camadas, pack ativo × base |
| `tests/domain/pr/test_explain_section_builder.py` | 4 | Renderização markdown, análise tolerante, deteção de `[FILL]` |
| `tests/domain/risk/test_risk_calculator.py` | 5 🆕 | Fórmula 50/30/20, clamp de 0–100, elevação obrigatória |
| `tests/domain/risk/test_risk_rules.py` | 5 🆕 | Pesos, limiares, glob de caminhos críticos, YAML de sobreposição |
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
| `tests/infrastructure/baseline/test_local_baseline_repository.py` | 24 🆕 | Único leitor/escritor: escrita atómica, checksum, reparação |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 🆕 | `git log` seguro: commits, bugs e reverts em 90 dias |
| `tests/infrastructure/git/test_test_matcher.py` | 5 🆕 | Correspondência teste ↔ produção por convenção |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Bridge do Bandit: disponibilidade, análise, mapeamento |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC: subprocesso endurecido, timeout, binário em falta |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Bridge do Gitleaks: mascaramento de segredos, âmbito por ficheiro |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Bridge do Semgrep: análise de JSON, severidade, salto de ficheiros não-Python |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 🆕 | Efeito real do baseline no review, no lint e no risk num repositório real |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 🆕 | Efeito real do pack no review, no lint e no risk (com uma guarda de rede autouse) |
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
| `tests/test_web_links.py` | 26 🆕 | (ver acima) |
| `tests/sync_i18n.py` | — | Script de verificação de cobertura de i18n (scaffold; nunca executado) |

**Total:** **2799 cenários recolhidos em 129 módulos de teste** (49 na raiz + 11 em `tests/application/use_cases/` + 10 em `tests/fix/` + 9 em `tests/scm/` + 7 em `tests/badge/` + 6 em `tests/demo/` + 6 em `tests/split/` + **5 em `tests/domain/baseline/`** 🆕 + **4 em `tests/domain/policy/`** 🆕 + 4 em `tests/review/` + 4 em `tests/infrastructure/linter/external/` + **2 em `tests/domain/risk/`** 🆕 + 2 em `tests/domain/tests_generation/` + **2 em `tests/integration/`** 🆕 + **2 em `tests/infrastructure/git/`** 🆕 + 2 em `tests/domain/linter/` + **1 em `tests/domain/finding/`** 🆕 + **1 em `tests/domain/mentor/`** 🆕 + 1 em `tests/domain/pr/` + **1 em `tests/infrastructure/baseline/`** 🆕; **+910 cenários e +33 ficheiros** desde o relatório anterior).

**Notas de qualidade desta versão:**
- **✅ As 4 falhas da janela anterior foram fechadas, com as afirmações intactas.** O `SKILL_LABELS` passou de 8 para **11** entradas e o `mcp_server.SKILL_FILES` de 8 para **11**; as **40 chaves `__()` por traduzir** foram resolvidas — o código usa 1603 e os dicionários têm 1603, **zero em falta**. As três afirmações que denunciavam a dívida (`TestSkillsSection` ×2, `TestNoMissingKeys`, `TestSkillRegistryAgreement`) **continuam a correr e agora passam** — verificado nesta janela com `pytest tests/test_config_schema.py::TestSkillsSection tests/test_mcp_server.py::TestSkillRegistryAgreement tests/test_i18n.py::TestNoMissingKeys` → **11 passados**.
- **Crescimento de 910 cenários (+48%) num único ciclo**, o maior salto alguma vez registado neste projeto. Só o `tests/test_metrics.py` passou de 34 para **164** cenários, e as três funcionalidades novas com maior densidade de testes são o baseline (≈380 cenários no total), o policy (≈246) e o ledger.
- **O CI corre a suíte em Python 3.10 e 3.13** desde a janela anterior, e o `fail-fast: false` garante que uma falha numa versão não esconde a outra.
- **Os testes de integração entraram no repertório:** o `tests/integration/` é novo e prova **efeito real** — um pack ativo e um baseline ativo mudam o review, o lint e o risk num repositório real, com uma guarda autouse que bloqueia a rede fora do loopback.
- **O `tests/conftest.py` continua hermético:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` e o `LANG_VERSION` na versão do código.
- **Execução completa nesta máquina: `2 failed, 2795 passed, 2 skipped, 138 subtests passed in 520.46s (8min40s)`.** A suíte foi finalmente corrida de ponta a ponta nesta janela e o resultado é honesto: **2795 dos 2799 cenários recolhidos passam**. As **duas falhas não são uma regressão do produto** — são um bug de isolamento no `tests/test_mentor_cli.py`, e a causa raiz foi identificada e confirmada por experiência:
  - O `TestMentorCli` faz `@patch("src.core.get_git_diff")`, mas o `src/main.py` importa o nome **por valor** (`from src.core import … get_git_diff`), pelo que o patch nunca chega à ligação que o fluxo realmente usa: corre a função **real**. O `get_git_diff()` real executa `git diff -U1 -w -M -B HEAD -- <59 smart excludes>`, e como a única alteração rastreada na árvore de trabalho durante a execução era o `CHANGELOG.md` — filtrado por `:(exclude)*.md` —, o diff volta com **0 bytes**, o fluxo cai no return antecipado em `main.py:1444` (`⚠️ Nenhum código novo encontrado`) com **exit code 0**, e a afirmação `render_review_result.assert_called_once()` falha porque o renderizador nunca foi chamado.
  - **A prova:** repetindo os dois cenários com o patch apontado a `src.main.get_git_diff` (a ligação correta), ambos comportam-se exatamente como as afirmações esperam — o `--mentor` chama o `_append_mentor_section` **e** o renderizador; sem `--mentor` a anexação **não** é chamada e o renderizador é. Por outras palavras: **os dois testes dependem do ambiente**, passando enquanto a árvore de trabalho tiver alguma alteração que não seja `.md` (o que foi verdade durante todo o desenvolvimento das funcionalidades) e falhando numa árvore só de documentação.
  - O código de produção está correto — o caminho `-r` chega ao `render_review_result` (`main.py:1593`) como sempre chegou. **Correção recomendada:** mudar o alvo do patch para `src.main.get_git_diff` nos dois cenários (ou fazer patch do `src.core` e da ligação do main em conjunto).
- **Nenhuma das 4 falhas da janela anterior permanece:** eram de outra natureza (registo de skills e chaves `__()` por traduzir) e as afirmações que as caracterizavam continuam a correr e a passar.
- **Guarda de rede em dois sítios:** `tests/demo/conftest.py` (a visita não pode envenenar uma cache real) e `tests/integration/conftest.py` (novo).
- **Fixtures de git reais:** o `tests/fix/git_fixture.py` e o `tests/split/git_fixture.py` continuam o padrão para o que só é honesto com um `git apply` real.

---

## **🌐 Internacionalização e Documentação**

* **Cobertura de i18n:** **1603 chaves de tradução** nos 6 dicionários, com **paridade total de key sets** — e, pela primeira vez em várias janelas, **paridade com o código também**: o código usa 1603 chaves e nenhuma fica sem tradução. ✅ (+445 nesta janela.) A cadeia medida por commit foi 1158 → 1161 (release notes, +3) → 1258 (ledger, +97) → 1288 (mentor, +30) → 1308 (docs do risk, +20) → 1437 (policy, +129) → **1603** (baseline, +166).
* **O `__lang_version__` subiu v0.0.31 → v0.0.32 → v0.0.33 → v0.0.34**, desencadeando o novo descarregamento OTA das traduções — e desta vez o bump está **consolidado** (`af35270`), não na árvore de trabalho.
* **Fontes de tradução em passo sincronizado:** uma chave nova tem de existir no código (fonte), em `langs/pt_br.json` (**lista mestra**), nos dicionários FR/ES do `scripts/sync_all_langs.py` e nos valores curados do `scripts/fix_mangled_i18n_keys.py`; a afirmação `len(CLEAN_KEYS)` mantém-se em 49.
* **Tópicos novos 🆕 (4, todos em 5 idiomas):**
  - `docs/mentor-mode.md` — Modo Mentor: o que ensina, as três garantias de honestidade epistémica, os lotes de 10 e o opt-in.
  - `docs/risk-scoring.md` — a pontuação determinística: os sinais locais, a fórmula 50/30/20, as regras de elevação e o `gitpr.risk.yml`.
  - `docs/policy-packs.md` — os packs versionados: a escada de precedência, o lockfile que a equipa consolida e as três falhas que abortam em vez de degradarem em silêncio.
  - `docs/baseline-suppressions.md` — o baseline: os cinco estados, porque é que só o `new` bloqueia, o que a impressão digital resume e o que deliberadamente deixa de fora, os quatro âmbitos de supressão e o bloco `baseline:` de um pack.
* **Tópicos traduzidos nesta janela (dívida paga) 🆕:** o `docs/split-command.*.md` completou os 5 idiomas (+3: pt_pt, es_es, fr_fr) e o `docs/otimizacao-de-tokens.*.md` também. O **`review-pr` continua com 2 idiomas** — é a última família de funcionalidades parcialmente traduzida.
* **Tópicos atualizados nesta janela:** `docs/release-notes.*` (5 — reescritos para o corte ancorado no changelog e as ligações por forge), `docs/metricas-telemetria.*` (5 — reescritos para o ledger SQLite, com o vocabulário canónico do `glossary-metrics-telemetry.md`), `docs/mcp-integration.*` (5 — as novas tools e recursos), `docs/mcp-annotations.*` (5), `docs/ARCHITECTURE.*` (5) e `README.*` (5 — o índice ganhou as famílias `mentor-mode`, `risk-scoring`, `policy-packs` e `baseline-suppressions`).
* **Subdiretório `docs/extra/` 🆕:** duas famílias só em PT (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) foram **movidas** do topo do `docs/` para `docs/extra/` (`R100`, sem alteração de conteúdo) — o material de apoio deixa de competir na contagem de tópicos canónicos.
* **Documentação em 5 idiomas:** **46 tópicos canónicos** em `docs/` (+4 novos − 2 movidos) — **43 com cobertura total nos 5 idiomas** 🆕 e apenas **3 tópicos parciais** (`github-issue-prompt-com-gh`, `version-markers`, `review-pr`), contra 7 na janela anterior. ✅
* **Lacuna de documentação que persiste 🆕:** o `explain` e o `tests` continuam as **únicas** funcionalidades sem tópico em `docs/` — ganharam a entrada no registo de skills e as chaves, mas não a página. As quatro funcionalidades desta janela (mentor, risk, policy, baseline) nasceram com docs em 5 idiomas, o que torna as duas exceções ainda mais visíveis.
* **Skills locais do Claude Code:** `.claude/skills/` com **29 skills** (contagem inalterada nesta janela).
* **Índice de Memória:** `.claude/memory/MEMORY.md` com **40 padrões** (contagem inalterada nesta janela).
* **Relatórios de tarefa:** `docs/claude-code/reports/develop_natan/` (**105** no total; **+4** — `2026-09-23_release_dedup_links.md`, `2026-09-29_metrics_telemetry_v2.md`, `2026-10-09_policy_packs.md`, `2026-10-10_baseline.md`) e `docs/gemini/reports/develop_natan/` (**14**; **+7**).
* **Relatórios de estado:** `docs/reports/` (16 relatórios; este é o **17.º**).
* **Planos de desenvolvimento:** 135 ficheiros em `docs/plans/` (+20 na janela — specs/planos de release, métricas, risk, policy e baseline; **ADR-008 a ADR-012**; os glossários `metrics-telemetry`, `gitpr-mentor`, `gitpr-risk-scoring`, `policy-packs` e `baseline`; e o novo subdiretório `docs/plans/develop_natan/`) + **14 ficheiros em `docs/survey/`** (+4).

---

## **🔄 Pipeline de Distribuição**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Atualização obrigatória:** a execução consulta o PyPI no arranque e **bloqueia com exit code 1** se houver uma versão mais recente, imprimindo `pip install --upgrade gitpr-cli`; a verificação é guardada em cache por dia e o `--update` apenas reporta
3. **GitHub Releases:** removidos — sem PyInstaller, sem asset `.exe`, sem hot-swap
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala via pip) + `tests.yml` (matriz Python 3.10 e 3.13). **Falta o workflow de release** — o build e o upload para o PyPI continuam manuais.
5. **Servidor MCP:** entry point `gitpr-mcp` via `pyproject.toml`
6. **Templates e idiomas OTA:** `templates/` e `langs/*.json` servidos a partir do GitHub (main) — o bump `v0.0.34` renova as cópias locais de `~/.gitpr/langs/` quando publicado; o `__scripts_version__` **v0.0.4** renova os hooks (reinstalar com `gitpr --installhooks`)
7. **Versão derivada do código:** o `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — o `__version__` é a **única** fonte
8. **O `package-data` passou a importar 🆕:** os quatro packs embutidos (`src/policy_packs/*`) viajam no wheel; um passo de build que os exclua produz um `gitpr policy list` que não vê os packs de fábrica
9. **Estado da release 🆕:** **`v1.3.0` com tag** (2026-09-24, merge do PR #193) e **`v1.4.0` com tag** (2026-10-12, merge do PR #207). O `CHANGELOG.md` tem as duas secções completas e com ligações — a `[1.4.0]` é a primeira gerada pelo motor corrigido. A única alteração em aberto na árvore de trabalho é a correção dos cabeçalhos localizados da secção `[1.4.0]`, que saíram em pt-BR (`Funcionalidades`, `Documentação`, `Tarefas`, `Contribuidores`) e foram normalizados para inglês.

---

## **📈 Evolução Desde o Relatório Anterior (v0.0.16)**

| Área | v0.0.16 (anterior) | v0.0.17 (atual) |
|------|-------------------|-----------------|
| **Versão do GitPR** | 1.3.0 (bump **não consolidado**; HEAD em 1.2.0) — tag `v1.2.0` | **1.4.0** — **tags `v1.3.0` (24/09) e `v1.4.0` (12/10) criadas** ✅ |
| **Versão de Idioma** | v0.0.32 (bump **não consolidado**) | **v0.0.34** (v0.0.32 → v0.0.33 → v0.0.34, **consolidado**) |
| **Versão dos Scripts de Hook** | v0.0.3 | **v0.0.4** (templates de hook reescritos para o ledger) |
| **Provedores de IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama (inalterado) |
| **Idiomas** | 5 idiomas, 6 dicionários | 5 idiomas, 6 dicionários |
| **Subcomandos** | 9 | **14** (+ `mentor`, `metrics`, `policy`, `risk`, `baseline`) — **4 deles são grupos** (baseline 6, metrics 8, policy 7, tests 1) |
| **Interface** | CLI + TUIs + assistentes + 9 subcomandos | **+ `gitpr mentor`/`--mentor` + grupo `metrics` (com assistente de migração) + `gitpr policy` + `gitpr risk` + `gitpr baseline`** |
| **Camadas** | `src/domain/` e `src/application/use_cases/` | **8 pacotes de domínio** (+ `baseline`, `finding`, `mentor`, `policy`, `risk`) e **14 casos de uso** |
| **Persistência local 🆕** | — | **`src/ledger.py`** — SQLite em `~/.gitpr/metrics/telemetry.db` (WAL, `user_version`, UUID, bundle/merge) |
| **Tools MCP** | 14 tools / 18 recursos / 7 prompts (faltavam `skill://explain` e `skill://tests` ⚠️) | **16 tools / 19 recursos / 7 prompts** ✅ (+`get_usage_metrics`, +`get_baseline_status`, +`baseline://summary`; registos de skills alinhados) |
| **Flags da CLI** | 36 na raiz + 9 subcomandos | **32 na raiz** (−5 metrics, +`--mentor`) + **14 subcomandos** com 22 subcomandos de grupo |
| **Variáveis de Ambiente** | 49 chaves no `DEFAULT_CONFIG` | **58 chaves** (+9: 4 baseline, 2 mentor, 2 policy, 1 risk) + `GITPR_METRICS_PRICE_*`/`CURRENCY` |
| **Schema de Configuração** | 71 `ConfigField` / 14 categorias | **80 `ConfigField` (+9) / 16 categorias** (+ Policy, + Baseline) |
| **Linter** | Regex + segredos embutidos + wildcard + 3 bridges SAST | **+ apontamentos estruturados (`findings_out`) + tipo canónico `src/domain/finding/` + severidade do Policy Pack + exit code ciente do baseline** |
| **Git Hooks** | o pre-commit bloqueia segredos | **+ o post-checkout/post-merge/pre-push chamam `gitpr --quiet metrics hook-event`** (resolução do repositório em bash, só para GitHub, removida) |
| **Camada SCM** | 4 forges, `get_pull_request`, `supports_reviewable_diff` | **+ `list_pull_requests` paginado e canónico, `supports_merged_dates`, `within_window()`, `web_links.py` (ligações por forge)** — 305 cenários |
| **Motor de release** | Intervalo por `git describe`, sem ligações | **Intervalo ancorado na secção anterior do changelog, `_drop_released()`, ligações de commit/PR/contribuidor, mapa de PRs por merge commit** |
| **i18n (chaves por ficheiro)** | 1158 × 6 — o código usa 1198 → **40 por traduzir** ⚠️ | **1603 × 6 — o código usa 1603 → zero por traduzir** ✅ (+445) |
| **Documentação** | 44 tópicos (37 completos + 7 parciais) | **46 tópicos (43 completos + 3 parciais)** — 4 famílias novas em 5 idiomas + 2 dívidas de tradução pagas + `docs/extra/` |
| **CI** | `.github/workflows/tests.yml` (3.10 + 3.13) | Inalterado (**falta** o workflow de **release**) |
| **Suíte de Testes** | 1889 cenários (96 ficheiros) | **2799 cenários (129 ficheiros) — +910 cenários, +33 ficheiros** (maior salto alguma vez registado) |
| **Falhas conhecidas** | 4 (registo de skills a meio, uma causa raiz) | **2** — as 4 da dívida **fechadas** ✅; restam 2 em `tests/test_mentor_cli.py`, de **isolamento de teste** (patch em `src.core` que não chega à ligação do `src.main`), sem regressão do produto |
| **Dependências** | — | **+ `packaging>=23.0`** (SemVer/PEP 440 para o Policy Pack) |
| **Commits desde o relatório** | 8 commits | **11 commits** (`c561664`, `64a406f`, `4380ce8`, `811651f`, `57c5140`, `c46440f`, `e1cfb11`, `4d42d56`, `8450186`, `af35270`, `1abb1b5`) |
| **PRs integrados** | 7 PRs (#177–#189) | **10 PRs (#192, #193, #194, #196, #198, #200, #201, #203, #205, #207)**; 6 issues fechadas (#191, #197, #199, #202, #204, #206) |
| **Índice de Memória** | 41 padrões | **40 padrões** (inalterado nesta janela) |
| **Relatórios de tarefa** | 101 claude-code, 7 gemini | **105 claude-code (+4) e 14 gemini (+7)** |
| **Planos de desenvolvimento** | 115 planos, 10 questionários | **135 planos (+20), 14 questionários (+4)** — inclui ADR-008 a ADR-012 |

---

## **🚧 Próximos Passos**

* **Corrigir o isolamento do `tests/test_mentor_cli.py` 🆕:** são as **duas únicas falhas** da suíte hoje. O patch aponta a `src.core.get_git_diff`, mas o `src/main.py` importa o nome **por valor** — corre a função real, e numa árvore cuja única alteração é um `.md` (filtrado por `:(exclude)*.md` nos smart excludes) o diff volta vazio e o fluxo retorna antes do renderizador. Repetido com o patch em `src.main.get_git_diff`, os dois cenários passam. É uma correção de duas linhas que fecha a suíte em **2797/2799**.
* **Consolidar a correção do `CHANGELOG.md` 🆕:** os cabeçalhos de secção da `[1.4.0]` saíram localizados (pt-BR) e foram normalizados para inglês **na árvore de trabalho**. É a única alteração em aberto no repositório — e o pacote publicado já transporta a versão portuguesa no ficheiro versionado.
* **Documentar o `explain` e o `tests`:** são, agora, as **duas únicas** funcionalidades sem tópico em `docs/` — todas as quatro que chegaram nesta janela nasceram documentadas em 5 idiomas. **Item herdado da janela anterior e ainda aberto.**
* **Automatizar a release no GitHub Actions 🆕:** o CI de testes existe há duas janelas, mas o build e o upload para o PyPI continuam manuais — e nesta janela o passo manual foi feito **duas vezes** (v1.3.0 e v1.4.0). Um workflow de release desencadeado por tags fecha o último troço manual do pipeline.
* **Alargar a cobertura do conjunto de regras de segredos:** as três lacunas continuam abertas e foram **reconfirmadas no código nesta janela** — atribuição sem aspas (`API_KEY=abc123`, o formato `.env`, que é precisamente onde os segredos vazam), os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-`, e a ausência de uma fronteira à esquerda no nome da chave (hoje `mytoken` corresponde da mesma forma que `token`).
* **Traduzir o `docs/review-pr.*.md` 🆕:** é a **última** família com cobertura parcial (2 de 5 idiomas). O `split-command` e o `otimizacao-de-tokens` foram pagos nesta janela.
* **Deixar de rastrear os artefactos de `.gitpr/metrics/export/` 🆕:** o `.gitpr/metrics/` entrou no `.gitignore` nesta janela — mas os **35 ficheiros já rastreados continuam rastreados**, porque o `gitignore` não desrastreia. Falta um único `git rm --cached`. **A dívida parou de crescer, mas não foi paga.**
* **Documentar a quebra de contrato do `request_pull_request_reviewers`:** passou de `None` para `list[str]`; as subclasses de terceiros do `ScmProvider` precisam de ser atualizadas. O `docs/scm-multiforge.*.md` é o sítio, nas 5 versões. **Item herdado, ainda aberto.**
* **Mapear o raio de impacto do baseline no exit code 🆕:** um baseline que não pode ser aplicado passa a **abortar** o lint, o review, o full review, a auditoria de ficheiro, o review remoto e o risk com exit code 1. É a escolha segura, mas é uma mudança de comportamento em **seis** fluxos — vale um `docs/baseline-suppressions` que explicite o contrato de saída para pipelines de CI.
* **Reconciliar a versão do projeto:** o `CLAUDE.md` ainda diz `Current version: 0.0.37` enquanto o `__version__` está em **1.4.0**. Definir uma convenção única. **Item herdado, ainda aberto.**
* **O `gitpr -h config` ignora o `-h`:** o subcomando abre a TUI em vez de mostrar a ajuda — o gate `if ctx.invoked_subcommand is not None: return` corre antes do bloco do `help_flag`. Corrigi-lo mudaria o comportamento do `-h` em **todos** os subcomandos, por isso precisa de uma decisão. **Item herdado, ainda aberto.**
* **Migrar o extrator do `sync_i18n.py` para AST:** a regex trunca literais com concatenação implícita (`__("a " "b")`). **Progresso parcial 🆕:** o `tests/test_i18n.py` já usa um extrator baseado em AST (`_extract_keys_ast`) e é ele que verifica a cobertura hoje; o script de sincronização continua em regex.
* **Secção Smart Exclude na TUI:** dos 12 itens reportados após usar o ecrã, o item 10 (*Smart Exclude*) é a única entrega ainda não começada — e os campos `GITPR_SMART_EXCLUDES_*` continuam sob `diff`, sem categoria própria.
* **Dívidas registadas no plano da TUI de configuração:** o `DEFAULT_CONFIG` ficou redundante com o schema; o banner de abertura não lista `--init`, `--base` nem `--plugins` (o `--dashboard` saiu da CLI, por isso essa parte do item ficou obsoleta 🆕); o `LinterApp` é a **única** app Textual que ainda não desativa a paleta de comandos.
* **Provedor Anthropic Claude:** Suporte direto para a API do Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual no Dashboard:** Histogramas temporais e tendências de tokens na TUI de métricas — o dashboard já lê o ledger, que era o pré-requisito em falta.
* **Seed para o `.gitpr/conf/` local:** O seeding de templates de configuração local (smart-excludes, linter) continua pendente como subcomando próprio ou passo de assistente.
* **Mais provedores:** OpenAI direto, provedores locais adicionais.

### ✅ Concluídos nesta janela (2026-09-23 → 2026-10-12)

* ~~**Fechar a dívida das duas funcionalidades novas**~~ — o `SKILL_LABELS` (8 → **11**) e o `mcp_server.SKILL_FILES` (8 → **11**) receberam `tests`, `explain` e `mentor`; as **40 chaves `__()` por traduzir** foram resolvidas e a cobertura chegou a 1603/1603. **As 4 falhas passaram a 0, com as afirmações intactas.**
* ~~**Fechar a release 1.3.0**~~ — tag **`v1.3.0`** criada (merge do PR #193, 2026-09-24) e `CHANGELOG.md` completado com as seis funcionalidades que faltavam. **Era o item que bloqueava duas janelas.**
* ~~**Fechar a release 1.4.0**~~ 🆕 — tag **`v1.4.0`** criada (merge do PR #207, 2026-10-12), com os três bumps (GitPR, idiomas, scripts) **consolidados** e já fora da árvore de trabalho.
* ~~**O `gitpr release` repete o histórico e não tem ligações**~~ 🆕 — corte ancorado na secção anterior do changelog, `_drop_released()`, ligações de commit/PR/contribuidor, mapa de PRs por merge commit e `web_links.py` (PR #192).
* ~~**Telemetria em JSON com `tokens_actual` sempre a zero**~~ 🆕 — ledger SQLite com escrita síncrona, tokens copiados no momento da escrita, grupo `gitpr metrics`, assistente de migração e custo por modelo (PRs #194/#196).
* ~~**Modo Mentor**~~ 🆕 — `src/domain/mentor/`, caso de uso, `gitpr mentor` + `--mentor`, skill em 5 idiomas (PR #198).
* ~~**Pontuação de risco determinística**~~ 🆕 — `src/domain/risk/`, `calculate_risk`, leitores de histórico e de testes, `gitpr risk` e secção anexada ao review (PR #200).
* ~~**Policy Packs**~~ 🆕 — `src/domain/policy/`, `src/infrastructure/policy/`, 4 packs embutidos, grupo `gitpr policy` e dependência `packaging` (PR #203).
* ~~**Baseline e supressões auditáveis**~~ 🆕 — `src/domain/baseline/`, `src/domain/finding/` canónico, portão nos seis fluxos, tool e recurso MCP (PR #205).
* ~~**Traduzir os tópicos parciais**~~ — o `docs/split-command.*.md` e o `docs/otimizacao-de-tokens.*.md` completaram os 5 idiomas.
* ~~**`.gitpr/metrics/` fora do controlo de versões**~~ — entrou no `.gitignore` com um comentário a explicar o que **não** é ignorado (`.gitpr/skill/`) e porquê.
* ~~**Higiene do `docs/`**~~ — duas famílias de material de apoio (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) foram movidas para `docs/extra/`, retirando-as da contagem de tópicos canónicos.
* ~~**Hooks que só funcionavam no GitHub**~~ — o `post-checkout`, o `post-merge` e o `pre-push` passam a delegar no próprio GitPR, com o `__scripts_version__` em v0.0.4.

---

**Relatório gerado em:** 2026-10-12  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
