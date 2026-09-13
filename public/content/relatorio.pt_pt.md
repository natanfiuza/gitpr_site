# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.14 (2026-09-13)**

## **📌 Visão Geral**

O **GitPR** é uma ferramenta de CLI (Command Line Interface) avançada para automação de processos Git utilizando Inteligência Artificial (Google Gemini / DeepSeek / Ollama). O objetivo principal é atuar como um assistente inteligente local que faz Code Reviews, gera Pull Requests, mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo de trabalho do desenvolvedor (Shift Left).

**Novidades desta versão (v0.0.14):**
- **Subcomando `gitpr config` — TUI de configuração interativa:** Um ecrã master-detail (Textual) sobre o `~/.gitpr/.env` com um menu lateral de categorias, campos editados inline, procura global (`/`), `F2` para guardar, `Ctrl+R` para restaurar e `Esc` com confirmação de descarte. Um **schema declarativo** (`src/config_schema.py` — 12 categorias, 56 `ConfigField`, 8 avançados) é a única fonte de verdade: o menu, os widgets, as predefinições e a validação derivam todos dele, pelo que acrescentar uma definição passou a ser uma alteração de **dados**, não de interface.
- **Secção Skills — a primeira superfície do ecrã com âmbito de projeto:** Um painel master-detail inline que edita os ficheiros `.gitpr/skill/*.md` do projeto (atomicamente, preservando CRLF/LF) na mesma passagem de `F2` que escreve o `.env`, com um único contador de alterações pendentes. O registo dos tipos suportados (`SKILL_FILES_BY_TYPE`) foi unificado em `src/config.py`, onde antes estava repetido em 6+ locais.
- **Registo geral de utilização (`src/usage_log.py`):** Uma linha por comando em `~/.gitpr/logs/<uuid5-of-the-date>.log` — **um ficheiro por dia** — escrita síncrona, nunca imprime e nunca lança exceções. Chamado de apenas dois locais (o callback raiz de `cli()` e o `main()` do servidor MCP), que juntos chegam a todas as flags, aos dois subcomandos e a todos os caminhos de `ctx.exit()`. Controlado por `GITPR_SHOW_LOGS` (nasce ativo em todas as instalações existentes).
- **Correção do idioma dos Git hooks:** O idioma escolhido pelo utilizador passou a ser efetivamente respeitado — `HOOK_SCRIPT_SUFFIXES` mapeia os códigos de interface (`es_es`, `fr_fr`) para os sufixos publicados (`.es`, `.fr`), `SCRIPTS_LANG` (a escolha do utilizador) foi separado de `SCRIPTS_INSTALLED_LANG` (estado em disco) para que a sincronização automática possa detetar uma mudança de idioma, e `--lang` deixou de ser ignorado.
- **Distribuição exclusiva via PyPI com bloqueio obrigatório de atualização:** O canal binário foi descontinuado — sem geração, sem upload, sem fallback. O `src/updater.py` foi reescrito em torno de uma única fonte de verdade (a API do PyPI): `enforce_update_required()` bloqueia a execução com **código de saída 1** quando existe uma versão mais recente publicada, imprimindo `pip install --upgrade gitpr-cli`. Saíram a consulta à API do GitHub Releases, a resolução de assets, o hot-swap com rollback e a dependência `pyinstaller`.
- **Registo de utilização, telemetria e dogfooding:** O próprio GitPR gerou as release notes desta janela (`gitpr release` em `.gitpr/reports/release/`), usou o registo de utilização para reconstruir a atividade e as skills locais `.gitpr.release.md` / `.gitpr.filereview.md` como instruções de sistema.
- **i18n expandida para 955 chaves:** +213 chaves desde o relatório anterior, cobrindo a TUI de configuração e as superfícies do bloqueio do PyPI; `__lang_version__` passou de v0.0.23 para **v0.0.25** (cadeia v0.0.23 → v0.0.24 → v0.0.25) e os 6 dicionários mantêm **paridade total de key sets**.
- **Documentação Multilíngue Expandida:** 2 famílias completas novas em 5 idiomas — `config-tui` e `usage-log` — e 7 tópicos atualizados (`ARCHITECTURE`, `auto-update`, `hooks-versioning`, `mcp-integration`, `skill-template`, `testar_sem_usar_pypi`, `version-markers`).
- **Remoção da chave morta `PR_AUTO_PUBLISH`:** Provada inexistente em `src/` (zero ocorrências) e removida da lista de variáveis de ambiente do `CLAUDE.md` e do `.env` do utilizador; a §5 da doc da TUI foi corrigida nas 5 versões, porque prometia "fora do ecrã" para chaves que afinal aparecem apenas de leitura em *Desconhecidas*.
- **Salto de Versão:** `__version__` passou de 1.0.0 para **1.1.0**; o `CHANGELOG.md` regista `[1.1.0] - 2026-09-13`, gerado pela própria funcionalidade de release.

- **Versão atual:** 1.1.0
- **Versão dos dicionários de idioma:** v0.0.25
- **Versão dos scripts de hook:** v0.0.3
- **Publicação:** PyPI (`pip install gitpr-cli`) — **canal binário removido nesta janela**
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositório:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licença:** LGPL-2.1
- **Idiomas suportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 dicionários)

---

## **🏗️ Arquitetura Base e Bibliotecas**

* **Linguagem:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags e formatação de terminal).
* **UI/Terminal:** Textual — TUI (Text User Interface) para chat interativo, edição de issues, help screen, dashboard de métricas, PR Publisher, erros do linter (`LinterApp`) e **configuração (`ConfigApp`)** 🆕.
* **Criptografia:** `cryptography.fernet` para proteção local de chaves de API, tokens GitHub e tokens SCM das forges — os segredos editados na TUI de configuração também são encriptados antes de serem escritos.
* **Configuração:** `python-dotenv`, `pyyaml` (para o linter estático) + **o seu próprio schema declarativo (`src/config_schema.py`)** 🆕.
* **Provedores de IA:** Integração via SDK oficial do Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), e OpenAI SDK (`Ollama` local).
* **APIs das Forges:** `requests` (REST) — camada de abstração multi-forge em `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps); módulo legacy `src/github_api.py` mantido como shim obsoleto.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — 12 ferramentas anotadas, 17 recursos, 7 prompts; handlers offloaded para threads via `anyio`.
* **Testes:** Pytest + `unittest.mock` (49 ficheiros de teste — 40 na raiz + 9 em `tests/scm/` —, 1060 cenários recolhidos) + testes e2e do servidor MCP via subprocess real (JSON-RPC stdio).
* **Empacotamento:** setuptools/build (PyPI). **O PyInstaller saiu do projeto nesta janela** — já não existe binário standalone.
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` para execução em pipelines.

---

## **🧩 Módulos Implementados e Arquitetura de Ficheiros**

### **1. Operações Core e Git (`src/core.py`)**

* **Geração Estruturada:** Comunica com o LLM a pedir output estritamente JSON.
* **Map-Reduce (Diffs Gigantes):** Quando o diff excede ~90k tokens, divide-o automaticamente em lotes por ficheiro (`split_diff_into_chunks`), processa cada parte (Map) e unifica os resumos (Reduce). Suporta PRs, commits e Issues.
* **Tokenizer Local:** `tokenizer.json` para estimativa precisa de tokens antes do envio à IA.
* **Estimativa de Tokens:** Heurística leve `len() // 4` via `estimate_token_count()` com fallback para o tokenizer local.
* **Otimização Git Nativa:** Flags `-U1`, `-w`, `-M`, `-B` nos comandos `get_git_diff` e `get_git_full_diff` para reduzir contexto inútil.
* **Pré-Gravação (`--pre-save`):** Flag de debug oculta que guarda o payload completo (instrução do sistema + prompt) em JSON antes de cada chamada à IA.
* **Exclusões Inteligentes com Duas Camadas:** Filtro de pathspec inteligente com camada global (`~/.gitpr/conf/`) + camada local do projeto (`./.gitpr/conf/`). Junção em runtime (união, deduplicada). Auto-seed do ficheiro local na primeira execução. 🆕 `_load_smart_excludes()` aceita `force=` para novo descarregamento a pedido a partir da TUI de configuração.
* **Métricas com Registo de Tempo:** Injeção de `log_command_metric()` em todos os fluxos com repasse da duração em milissegundos (`duration_ms`) e imports lazy.
* **Resolução Centralizada do Output:** Função `resolve_output_path()` que centraliza a lógica dos diretórios de output — predefinição em `.gitpr/reports/{type}/`.
* **Assistente SCM (`run_scm_init_wizard()`)**: `gitpr --init` — deteta a forge a partir do remote de origem, solicita extras por forge (org/projeto do Azure, username do Bitbucket), valida o token com `test_connection` (3 tentativas, novo pedido em 401) e persiste `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` (Fernet) **apenas em caso de sucesso**.
* **Template de Skill de Release (`ensure_release_skill_template()`)**: Transfere `templates/gitpr.release.*.md` na primeira utilização de `gitpr release` (camada CLI, ciente do idioma, nunca sobrescreve; ignorado em `--format json`).
* **Registo de Skills Partilhado 🆕:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` saíram do `core.py` para `src/config.py` (a TUI não pode importar o `core` no topo — este arrasta os SDKs de IA); `get_skill_context()` passa a usar `skill_file_for()`.
* **Trailer de Coautoria:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceiros.
* **Subprocessos Blindados:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` em todos os `subprocess.run`; verificação de ligação via socket `8.8.8.8:53` antes das operações de rede.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitetura de Plugins:** Sistema de extensibilidade que carrega plugins do diretório `~/.gitpr/plugins/` aplicando-se a **todos os projetos**.
* **Plugins de Linter (`linter/`):** Ficheiros `.yml` com regras de regex adicionais combinadas com o `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`):** Ficheiros `.md` que estendem o contexto do sistema com instruções específicas.
* **Factory Closures:** Funções `get_linter_plugins` e `get_prompt_plugins` com closures para isolar estado entre sessões.
* **Comando `--plugins`:** Lista todos os plugins globais instalados com os seus tipos e paths.
* **Documentação Multilíngue:** `docs/plugins-system.md` em 5 idiomas (EN, PT-BR, PT-PT, ES, FR).

### **3. Interface CLI e Configuração (`src/main.py` e `src/config.py`)**

* **Setup Inicial:** Deteta a primeira execução, cria a pasta `~/.gitpr/`, e solicita interativamente as chaves de API, preferências e idioma.
* **Routing de Comandos:** Gere todas as flags e os **2 subcomandos** — `release` e `config` 🆕.
* **Comportamento Predefinido:** Executar `gitpr` sem flags abre a TUI do PR Publisher.
* **Flags (35 opções Click na raiz, inalteradas nesta janela):**
  * `--init`: Abre o assistente de configuração SCM multi-forge (deteção de forge + validação de token).
  * `--no-suggest-reviewers`: Desativa a sugestão de revisores no fluxo de publicação de PR.
  * `--no-publish`: Gera a descrição do PR e guarda-a localmente sem abrir o editor interativo.
  * `--no-edit`: Salta a TUI completamente — auto-commit, auto-push e publica diretamente na forge.
  * `--base <branch>`: Substitui a branch de destino do Pull Request.
  * `--plugins`: Lista plugins globais instalados.
  * `--linter-setup`: Abre o assistente interativo de configuração de linters externos.
  * `--version`: Apresenta a versão atual do GitPR (via `@click.version_option`).
* **Subcomando `config` 🆕:** 0 opções — abre a TUI de configuração. Deliberadamente **não** chama `setup_environment()`, para que nenhum `click.prompt` dispute o terminal com a TUI. Import lazy; epilog com `get_doc_url("config-tui.md")`.
* **Bloqueio Obrigatório de Atualização 🆕:** No início do callback de `cli()`, **depois** do handler de `--lang` (para que a mensagem saia no idioma pedido) e **antes** do despacho das flags (porque o `--linter` retorna antes de `check_internet_connection()`) — sem ele, a maioria dos comandos ficaria sem proteção. Salta `--quiet`, `--hook`, `--mcp`, `--update` e `-h/--help`; `--help`/`--version` são opções *eager* do Click e nunca chegam ao corpo.
* **Registo de Utilização 🆕:** `log_usage()` no topo do callback, antes do `if ctx.invoked_subcommand is not None: return` — as 35 flags, os 2 subcomandos e o `ctx.exit()` de `-h` passam todos por lá.
* **Variáveis de Ambiente (39 chaves em `DEFAULT_CONFIG`, inalteradas):** `GITPR_SKIP_UPDATE_CHECK` 🆕 (qualquer valor não vazio desliga o bloqueio; usado pela suíte de testes) e `GITPR_SHOW_LOGS` (declarada, semeada como `"true"` e desligada em `tests/conftest.py`) — esta última saiu das sombras e ganhou o seu próprio campo na categoria Geral da TUI.
* **Ajuda Contextual:** `-h --flag` apresenta documentação específica da funcionalidade com um link direto (ciente do idioma) para o GitHub. Os subcomandos têm o seu próprio `epilog=` (parágrafo `\b` do Click para a URL não ser re-quebrada em nenhum idioma).
* **--lang:** Força o idioma da interface na execução atual sem persistir a alteração — 🆕 e passa também a aplicar-se à resolução dos scripts de hook.
* **--provider:** Força o provedor de IA (`gemini`, `deepseek`, `ollama`) na execução atual.
* **--mcp:** Inicia o servidor MCP no transporte stdio para integração com editores — **12 ferramentas anotadas + 17 recursos + 7 prompts**.
* **--install:** Assistente guiado de 4 etapas que transfere templates de skill, instala Git Hooks, configura MCP nos editores e valida chaves de API.
* **--metrics:** Sistema de telemetria local com âmbito por repositório: `--export`, `--purge`, `--dashboard`.
* **--status:** Lista ficheiros não commitados categorizados (new/modified/deleted) — rápido, sem IA, sem rede.
* **Camada de Escrita do `.env` 🆕:** `read_env_file_values()` lê **apenas o ficheiro** via `dotenv_values` (imune a `os.environ`), `save_config_values()` escreve com `set_key`, `remove_config_value()` com `unset_key` — os 9 locais de chamada de `set_key` pré-existentes ficaram intactos. `validate_ai_key()` testa os SDKs Gemini/DeepSeek com timeouts curtos e distingue uma credencial recusada (`401`/`403`) de uma rede inacessível.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` e `src/ui/pr_publish_help.py`)**

* **Interface Interativa Completa:** TUI construída com Textual para rever, editar e publicar Pull Requests diretamente no terminal.
* **6 Ecrãs Modais:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Revisores Sugeridos:** O fluxo de publicação consulta a forge para obter revisores sugeridos e apresenta-os na TUI; seleção controlada por `--no-suggest-reviewers`, `GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N` e `GITPR_REVIEWER_SUGGESTION_EXCLUDED`.
* **Bindings:** F1 (Ajuda), F2 (Guardar .md local), F3 (Publicar via forge), Esc (Sair).
* **Fluxo de Auto-Commit:** Linter → mensagem IA → confirmação → commit → push → publica PR.
* **Verificação de Ficheiros Unstaged:** Ao iniciar, verifica `git status --porcelain` e oferece um modal para selecionar, saltar ou cancelar.
* **Tratamento de PR Existente:** Deteta PRs abertos para a branch atual via API e oferece push ou criar um novo.
* **Auto-Upstream:** Deteta falha de `git push` por falta de upstream e tenta automaticamente `--set-upstream origin <branch>`.
* **Merge Flow:** Após a criação/atualização do PR, oferece opção de merge. Controlado por `GITPR_AUTO_MERGE`.

### **5. Módulo de API do GitHub (`src/github_api.py`)**

* **Shim Obsoleto:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` e as restantes funções passam a delegar em `src/infrastructure/scm/github_provider.py`; o módulo emite um `DeprecationWarning` e mantém os tuplos legacy `(ok, data, status)` — nenhum código novo o pode importar.

### **6. Motor de Análise Estática / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analisa estaticamente as linhas adicionadas (`+`) no git diff sem gastar quotas de IA.
* **Regras YAML:** Lê o ficheiro local `.gitpr.linter.yml` (criado via `--skill`).
* **Plugins de Linter:** Regras adicionais carregadas de `~/.gitpr/plugins/linter/*.yml`.
* **Bridge de Linters Externos:** Executa ESLint/PHPCS/Stylelint nas linhas alteradas do diff, parser Checkstyle XML e cruzamento por linha.
* **Relatório Consolidado:** `generate_linter_report_content()` consolida erros regex + externos em `.gitpr/reports/linter/` — gerado apenas quando há violações.
* 🆕 `load_linter_presets()` aceita `force=` para descarregar novamente os presets a partir da TUI.

### **7. Segurança e Autenticação (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografia:** Gera uma chave mestra `secret.key` na pasta `~/.gitpr/`.
* **Proteção de Tokens:** `encrypt_data` e `decrypt_data` para proteger chaves de API de IA, PATs do GitHub e tokens SCM das forges (`GITPR_SCM_TOKEN_ENCRYPTED`).
* **Validação Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — valida o token contra a forge configurada com loop de reautenticação em 401 preservando o rascunho; o token legacy do GitHub (`GITHUB_TOKEN_ENCRYPTED`) permanece funcional até `--init` correr.
* **Segredos na TUI de Configuração 🆕:** Os campos `KIND_SECRET` são editados num campo mascarado, **nunca** apresentam o valor em claro e são encriptados com Fernet antes de serem escritos — nenhum caminho volta a ler o segredo para o ecrã. O `GITPR_SCM_TOKEN` é `read_only` e a sua descrição aponta para `gitpr --init` como o único caminho que o deve escrever.

### **8. Auto-Atualizador (`src/updater.py`) — reescrito nesta janela 🆕**

* **PyPI como Fonte Única:** `get_latest_remote_version()` consulta sempre `https://pypi.org/pypi/gitpr-cli/json`, devolve uma **string** de versão e escreve a cache diária **sem** o campo `download_url`. Perdeu o parâmetro `is_compiled` e todo o ramo da API do GitHub Releases.
* **Bloqueio Obrigatório (`enforce_update_required()`):** Devolve `True` (depois de imprimir as duas versões e o comando pip) quando a versão publicada é mais recente; devolve `False` quando está atualizado, quando a versão remota é **desconhecida (offline — o utilizador não teria forma de atualizar)** ou quando a verificação está desligada. Devolver um `bool` em vez de chamar `sys.exit` internamente mantém a função testável.
* **`check_and_update()`:** Reescrito para `--update` — apenas consulta e **reporta**, nunca instala.
* **Removido:** `GITHUB_API_URL`, `_perform_hot_swap()` (renomeava o `.exe` para `.old`, descarregava o novo e fazia rollback), `print_update_notice()` e os seus 5 locais de chamada, o bloco de limpeza do `.old` em `main.py` e a dependência `pyinstaller` do `Pipfile`. O `icon.ico` foi eliminado.
* **Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` (qualquer valor não vazio) — não publicitada ao utilizador como funcionalidade; existe para a suíte de testes e para automatização offline.
* **Cache Diário:** Evita verificações repetidas no mesmo dia.
* **Versionamento Centralizado:** `__version__` (**1.1.0**), `__lang_version__` (**v0.0.25**), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* **Defeitos que esta alteração fecha:** o antigo `urlretrieve` não tinha timeout nem checksum, e o `main.py` apagava o backup `.old` na execução seguinte sem quaisquer condições — um download truncado era irrecuperável.

### **9. Interface de Chat Interativa (`src/ui/chat_app.py`)**

* **TUI Completa:** Construída com Textual — histórico de mensagens, input multi-linha, barra de status com bindings visíveis.
* **Memória por Branch (`src/chat_memory.py`):** Histórico de conversa persistido por branch, permitindo continuidade entre sessões.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear` — atalhos para pair programming.
* **Auto-Patching (F5), Atualização de Diff (F2), Exportação de Sessão (F6).**

### **10. Internacionalização — i18n (`src/i18n.py`)**

* **Sistema Inspirado no Laravel:** Função `__()` com suporte a placeholders nomeados (`{count}`, `{file}`, etc.).
* **Deteção Automática:** Deteta o idioma do SO na primeira execução e guarda-o em `GITPR_LANG`.
* **5 Idiomas, 6 Dicionários:** en_us (predefinição/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Ficheiros Versionados:** `__lang_version__` (**v0.0.25**) controla a atualização dos pacotes de idioma (`langs/*.json`) — cadeia de bumps v0.0.23 → v0.0.24 → v0.0.25 nesta janela.
* **Cobertura:** **955 chaves** de tradução em cada um dos 6 ficheiros — **paridade total de key sets** (+213 desde o relatório anterior).
* **Snapshot do Ambiente (`AMBIENT_ENV_KEYS`) 🆕:** `frozenset(os.environ)` capturado no `i18n.py` **imediatamente antes** do `load_dotenv()` ao nível do módulo. Era a raiz de um defeito silencioso da TUI: como o `config.py` importa de `i18n.py`, todo o `.env` já estava dentro de `os.environ` antes de o ecrã existir, pelo que o badge "⚠ no ambiente" confirmava tautologicamente que a chave está no ficheiro. Medido: **0 campos com o badge** num processo limpo, **40 de 49** depois de importar o ecrã. Corrigido nos três pontos de utilização.
* **Chaves Renomeadas/Removidas 🆕:** `Detected language: {lang}` → `Hooks language: {lang}`; 6 chaves obsoletas de atualização/binário removidas e 3 novas do bloqueio do PyPI acrescentadas.
* **Cache com Indexação por Idioma:** As respostas de IA em cache incluem o idioma corrente no chaveamento MD5.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Thread em background durante chamadas de IA exibindo caracteres braille com palavras de "pensamento".
* **263 entradas por idioma:** Sincronizadas entre os 5 idiomas. 🆕 `_load_thinking_words()` / `reload_thinking_words()` aceitam `force=`.

### **12. Provedores de IA (`src/ai_providers.py`)**

* **3 Provedores Suportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Modo JSON & Parâmetros Determinísticos:** Outputs estruturados com `temperature=0.0` e `top_p=0.1`; fallback automático entre os provedores configurados.

### **13. Cache Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Chaveamento por hash MD5 do diff e do prompt, com indexação por idioma.
* **Telemetria e Duração:** Persistência dos campos `duration_ms` e `meta_raw` em ficheiros de cache.
* **Leitura para o Dashboard:** `scan_cache_files_for_dashboard()` lê todos os ficheiros de cache recursivamente.

### **14. Motor de Issues e TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff atual, Histórico da branch (`-ht`), e Arqueologia por Blame (`-b`).
* **Publicação Multi-Forge:** O F3 cria a issue na forge **configurada** (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — o Azure DevOps lança `ScmNotSupportedError` (os Work Items dependem do process template).
* **Map-Reduce para Issues:** Quando o contexto excede ~90k tokens, divide automaticamente em chunks e unifica os resultados.
* **Tratamento de 401:** Sinalização de reautenticação sem fechar a aplicação.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Regista a evolução e autoria histórica de excertos de código com classificação de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registados via `log_blame_metric()` com rastreamento de profundidade e número de commits analisados.

### **16. Servidor MCP e Invocação Direta via CLI (`src/mcp_server.py`)**

* **12 Ferramentas MCP Anotadas:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **17 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release}` + `linter://config` + `prompt://list` + 7 prompts.
* **Invocação CLI Direta:** O comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca qualquer tool MCP diretamente sem iniciar o servidor stdio JSON-RPC. O `gitpr-mcp --list` imprime o registo completo em JSON.
* **Isolamento do Stdout Real:** `_write_real_stdout()` escreve diretamente no `sys.__stdout__` original, garantindo JSON puro no stdout — a razão pela qual o registo de utilização **nunca** imprime.
* **Offload do Event Loop:** Decorator `_offload` (`anyio.to_thread.run_sync`) aplicado às 12 tools — handlers síncronos não congelam o servidor stdio.
* **Registo de Utilização 🆕:** O `main()` do servidor chama `log_usage()` — o console script `gitpr-mcp` nunca carrega o `main.py`, pelo que este é o único ponto que lá chega.
* **Testes E2E:** `tests/test_mcp_server_e2e.py` inicia o servidor real como subprocess e fala JSON-RPC stdio.

### **17. TUI de Dashboard de Métricas (`src/ui/metrics_app.py`)**

* **Âmbito por Repositório (Repo-Scope):** Rótulo `📁 Repository: owner/repo` e filtragem estrita por projeto.
* **Varredura Assíncrona com Overlay:** Worker thread em background com widget `ProgressBar`.
* **Consolidação de Dados:** `load_cache_token_summary()` soma tokens de cache ao totalizador.
* **Exportação Local:** Guardar CSV/JSON em `./.gitpr/metrics/export/` (artefactos de 2026-09-12 e 2026-09-13 versionados nesta janela).

### **18. Sistema de Métricas e Telemetria (`src/metrics.py`)**

* **Âmbito por Repositório:** Todos os eventos indexados por `repo_name`.
* **Eventos de Hook, Linter e Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Exportação e Limpeza:** `--metrics --export` (CSV/JSON) e `--metrics --purge` com confirmação interativa.

### **19. Sincronização de Git Hooks — corrigido nesta janela 🆕**

* **Versionamento Independente:** `__scripts_version__` (v0.0.3) controla a versão dos scripts de hook; deteção e atualização automáticas.
* **Mapeamento de Sufixos (`HOOK_SCRIPT_SUFFIXES`) 🆕:** Os códigos de interface (`es_es`, `fr_fr`) passam a ser traduzidos para os sufixos realmente publicados (`.es`, `.fr`) — antes o idioma escolhido pelo utilizador era simplesmente ignorado.
* **Escolha vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`) 🆕:** O `SCRIPTS_LANG` é a escolha do utilizador; o `SCRIPTS_INSTALLED_LANG` é o que está em disco. Separados, a sincronização automática passa a poder **detetar uma mudança de idioma** em vez de assumir que ele já está instalado.
* **`effective_hook_lang()` 🆕:** Resolve o idioma efetivo dos hooks; o `--lang` deixou de ser descartado nesse caminho (alteração de comportamento documentada).
* **Skip de Merge-Source:** O template `prepare-commit-msg` salta as fontes `message|merge|squash|commit` — commits gerados pelo git preservam a mensagem original.

### **20. Bridge de Linters Externos e Assistente Interativo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistente `--linter-setup`:** Wizard interativo com presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e injeção do bloco `external_linters` no `.gitpr.linter.yml`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` servido do GitHub com cadeia de resolução local → download → stale → fallback embutido.
* **TUI de Erros do Linter:** `src/ui/linter_app.py` (Textual) apresenta erros críticos e warnings; em modo hook/quiet imprime e faz `sys.exit(1)`.
* **Relatório Markdown:** Consolidado em `.gitpr/reports/linter/` — apenas quando há violações.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstração Única (`ScmProvider` ABC):** O `base.py` define o contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. e `ScmProviderError(provider, http_status, message)` — `http_status` 0 = falha de rede); um provedor concreto por forge em `github_provider.py`, `gitlab_provider.py`, `bitbucket_provider.py`, `azure_devops_provider.py`.
* **Registo e Factory:** `resolve_scm_provider()` seleciona por `GITPR_SCM_PROVIDER` (predefinição `github` — zero migração, fallback legacy do token GitHub intacto); `detect_provider_from_remote()` identifica a forge a partir da URL de origem.
* **Endereçamento de Repositórios:** `parse_repo_ref(remote_url) -> RepoRef(raw, workspace, name, provider)` — workspace = owner do GitHub / namespace do GitLab (subgrupos) / workspace do Bitbucket / apresentação `{org}/{project}` do Azure.
* **Fail-Fast por Forge:** O Azure DevOps exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; o Bitbucket exige `GITPR_SCM_USERNAME` (App Password = Basic auth user+token); `create_issue` no Azure lança `ScmNotSupportedError`.
* **Publicação de Release:** `provider.create_release()` usado por `gitpr release --publish` (o GitHub cria a tag na branch predefinida; o GitLab exige que a tag exista).
* **Artefactos:** Glossário + ADR-001 em `docs/plans/`; família `docs/scm-multiforge.*.md` em 5 idiomas; testes: 9 ficheiros, 265 cenários.

### **22. Subcomando `gitpr release` — Changelog / Release Notes**

* **Fluxo:** `git log` entre `--since` (predefinição: última tag alcançável, ou o primeiro commit) e `HEAD` → classificação por Conventional Commits → sugestão de bump semântico (`--version <x.y.z>` sobrepõe-se) → montagem do changelog → resumo executivo por IA opcional → *anteposição* ao `CHANGELOG.md`. A geração local é a predefinição — nada é publicado ou alterado sem pedido.
* **Classificador (`src/commit_classifier.py`):** Classifica os commits por tipo de Conventional Commits (feat/fix/refactor/docs/chore/etc.) com um parser tolerante.
* **Builder com Secções Traduzíveis (`src/changelog_builder.py`):** Secções "✨ Features", "🐛 Bug Fixes", "⚠️ Breaking Changes", "Summary", "Contributors" etc. renderizadas via `__()` em runtime (seguem `--lang`).
* **Bump Semântico (`src/version_bump.py`):** Sugere a próxima versão a partir dos tipos classificados (major para breaking, minor para feat, patch para fix) e valida alvos `x.y.z`.
* **Publicação:** `--publish` cria o release na forge configurada (com confirmação explícita); `--draft` cria-o como rascunho (GitHub; o GitLab não tem conceito de draft); `--format markdown|json` para output estruturado; `--force` para reescrever. **6 opções no subcomando.**
* **Template de Skill:** Na primeira utilização transfere `templates/gitpr.release.*.md` (5 idiomas) via `ensure_release_skill_template()` — nunca sobrescreve.
* **Dogfooding nesta janela:** O `.gitpr/reports/release/` recebeu as release notes geradas pelo próprio comando (`develop_natan_20260910144814`, `...145040`, `...145125`) e `.gitpr/skill/.gitpr.release.md` + `.gitpr.filereview.md` passaram a existir como skills locais do projeto.
* **Artefactos:** família `docs/release-notes.*.md` (5 idiomas), spec em `docs/plans/`, ADR-002 e ADR-003, glossário de release notes.

### **23. Subcomando `gitpr config` — TUI de Configuração 🆕**

* **Ecrã Master-Detail (`src/ui/config_app.py`):** Categorias à esquerda, campos da categoria à direita, editados inline. Cabeçalho com procura (`/`) e um contador de alterações pendentes (`● N não guardadas`); rodapé com `F1 Ajuda · F2 Guardar · ^R Restaurar · / Procurar · Esc`. O `Geral` é sempre a primeira entrada do menu.
* **Schema Declarativo (`src/config_schema.py`) — a única fonte de verdade:** 12 categorias (Geral, Fornecedores de IA, Pull Request, Revisão de Código, Issue, Blame, Linter, Release, SCM / Forge, Filtros de Diff, Skills, Avançado) e **56 `ConfigField`**, dos quais **8 são avançados**. Cada campo declara a sua categoria, tipo de widget (`bool`/`int`/`str`/`enum`/`template`/`path`/`secret`/`version`/`words`), `show_if`, validadores, marcadores de versão e ações de descarregamento. Os rótulos são literais `__()` para o scanner de i18n.
* **Filtragem por Contexto (`show_if`):** `GEMINI_*` / `DEEPSEEK_*` / `OLLAMA_*` aparecem em função do `DEFAULT_AI_PROVIDER` selecionado (nenhum selecionado → nenhum bloco); `GITHUB_TOKEN_ENCRYPTED` aparece com provedor vazio ou `github`; `GITPR_SCM_USERNAME` sob `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` sob `[Azure DevOps]`. Alterar o `Select` re-filtra o painel **imediatamente, sem F2** (apenas `on_select_changed` dispara `_render_view()`, e só para chaves em `VISIBILITY_CONTROLLERS`, derivadas do schema e não escritas à mão).
* **Procura Global (`/`):** Encontra correspondências na chave ou no rótulo em todas as categorias e **ignora o filtro de visibilidade** — procurar `deepseek` com o Gemini selecionado encontra os campos, para permitir pré-preenchê-los. Um campo com alterações pendentes é guardado independentemente da visibilidade (o `_build_plan` é cego à visibilidade, por conceção).
* **Validação em Duas Camadas:** **Offline** (tipo, enum, template com placeholder conhecido e `{datetime}` obrigatório) bloqueia o `F2` com um erro inline; **online** (apenas para credenciais alteradas na sessão) corre num worker com timeout de 10s e só bloqueia em `401`/`403` — uma falha de rede ainda permite guardar.
* **Restaurar (`Ctrl+R`):** Remove a linha do `.env` em vez de reescrever a predefinição; o **`Esc`** com alterações pendentes pede confirmação; a categoria **Desconhecidas** preserva em modo de leitura as chaves fora do schema (não oferece remoção, por conceção).
* **Secção Skills — a única com âmbito de projeto 🆕:** Painel master-detail inline (lista de skills à esquerda, editor de texto à direita) que edita os `.gitpr/skill/*.md` do projeto, resolvidos a partir do diretório de chamada, escrevendo atomicamente e preservando CRLF/LF. O `F2` escreve o `.env` e os ficheiros de skill **na mesma passagem**, com um único contador de alterações pendentes.
* **Descarregamentos Forçados:** Botões que forçam o novo descarregamento dos smart-excludes, das traduções, dos presets de linter e das thinking words, via um parâmetro `force=` encadeado pelos loaders.
* **Módulo de Ligações Leve (`src/doc_links.py`) 🆕:** O `doc_url()` saiu do `core.py` para que a UI possa obter a ligação à documentação sem importar o `core`/SDKs de IA. Cada categoria do schema aponta para a sua doc canónica.
* **Testes:** 5 ficheiros novos — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Artefactos:** `docs/config-tui.*.md` em 5 idiomas, plano `docs/plans/20260912_config_tui.md`, glossário `glossary-config-tui.md` (8 termos) e o levantamento (grill survey).
* **Dívida conhecida:** `gitpr -h config` abre a TUI e ignora o `-h` — o gate `if ctx.invoked_subcommand is not None: return` corre antes do bloco `help_flag`; corrigi-lo mudaria o comportamento do `-h` para **todos** os subcomandos (documentado em `docs/config-tui.md`).

### **24. Registo Geral de Utilização (`src/usage_log.py`) 🆕**

* **Uma Linha por Comando:** Escreve em `~/.gitpr/logs/<uuid5>.log`, **um ficheiro por dia**, com o comando, os argumentos, o repositório, o utilizador e o timestamp. Responde a "o que é que eu executei realmente, e quando?".
* **Nome Derivado da Data:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` em vez de aleatório — um nome aleatório exigiria um contador ou um ficheiro de estado para saber qual é o ficheiro de hoje, e dois processos concorrentes poderiam discordar. Derivado da data, o mesmo dia resolve sempre para o mesmo nome e comandos concorrentes limitam-se a acrescentar ao mesmo ficheiro.
* **Escrita Síncrona (decisão explícita):** Ao contrário do `log_local_metric`, que usa uma thread daemon e por isso perde a escrita se o processo terminar cedo — inaceitável para um registo que promete registar *todos* os comandos.
* **Nunca Imprime:** O servidor MCP reserva o stdout para JSON-RPC; um `print` acidental corromperia o protocolo. O módulo também nunca lança exceções.
* **Um Único Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` em vez dos três idiomáticos — ~40 ms em vez de ~150 ms no Windows, em *todas* as execuções.
* **O Seu Próprio `_repo_label()`:** Sem reutilizar o `get_repo_name()` do `core.py` (regex hardcoded para `github.com`, devolveria `unknown/repo` no GitLab/Bitbucket/Azure — um defeito novo num projeto que acabara de ganhar multi-forge) e sem o `parse_repo_ref`, que é um método de provedor e exigiria construir um provedor (token, `requests`) em cada comando.
* **Controlo:** `GITPR_SHOW_LOGS` (predefinição `"true"` — nasce ativo em todas as instalações existentes, sem migração); desligado em `tests/conftest.py`.
* **Artefactos:** `docs/usage-log.*.md` em 5 idiomas; `tests/test_usage_log.py` (27 cenários).

---

## **📊 Testes e Qualidade**

| Ficheiro de Teste | Cenários | Foco |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por intervalo de linhas num ficheiro |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidade, commits, duração |
| `tests/test_changelog_builder.py` | 15 | Builder de changelog: secções, cabeçalhos traduzíveis, contribuidores |
| `tests/test_chat_backend.py` | 19 | Memória de chat, persistência, comandos slash |
| `tests/test_commit_classifier.py` | 23 | Classificação Conventional Commits (tipos, parser tolerante) |
| `tests/test_config_app.py` | 78 🆕 | TUI de configuração: montagem, troca de categoria, dirty tracking, F2 bloqueado, Ctrl+R, procura, segredos |
| `tests/test_config_cli.py` | 9 🆕 | Registo do subcomando `config`, `-h`, import lazy, stdout limpo |
| `tests/test_config_schema.py` | 42 🆕 | Cobertura do `DEFAULT_CONFIG`, sem duplicados, categorias/kinds, `advanced` apenas em Avançado |
| `tests/test_config_store.py` | 22 🆕 | Round-trip num `.env` temporário, comentários e ordem preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuração de revisores sugeridos (chaves e predefinições) |
| `tests/test_config_validation.py` | 40 🆕 | Tipos, enums, templates, `validate_ai_key()` com SDK mockado (401 vs. rede vs. ollama) |
| `tests/test_core.py` | 49 | Fluxos principais, git diff, geração de PR, timing, staging, coautoria, idioma dos hooks |
| `tests/test_diff_parser.py` | 15 | Parser de diff por linhas/hunks |
| `tests/test_external_linters.py` | 33 | Bridge Checkstyle: parser XML, subprocess, cruzamento de diff, relatório |
| `tests/test_i18n.py` | 20 | Paridade entre idiomas (955×6), chaves ausentes/órfãs, identidade |
| `tests/test_install_wizard.py` | 3 | Assistente interativo de instalação |
| `tests/test_issue_engine.py` | 4 | Rascunho de issue estruturado |
| `tests/test_linter_metrics.py` | 4 | Métricas de linter: erros, warnings, duração |
| `tests/test_linter_presets.py` | 5 🆕 | Presets de linter: resolução e novo descarregamento forçado |
| `tests/test_main_suggest_reviewers.py` | 6 | Flag `--no-suggest-reviewers` na CLI e na ajuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompt MCP e fallback de idioma |
| `tests/test_mcp_server.py` | 86 | Ferramentas MCP, recursos, annotations, patching, CLI direto, offload |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real via subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Recolha, exportação local, âmbito de repo, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Timeouts de rede/IA — **2 asserções desatualizadas (600s)** |
| `tests/test_plugins.py` | 17 | Descoberta de plugins, merge de regras de linter, prompts MCP |
| `tests/test_pr_publish_app.py` | 42 | TUI do PR Publisher: ecrãs, fluxos, revisores sugeridos |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de erro do linter: abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save e payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opções, help com epilog documentado |
| `tests/test_release_engine.py` | 27 | Motor de release: intervalo de commits, CHANGELOG, publicação |
| `tests/test_reviewer_suggestion.py` | 15 | Lógica de sugestão de revisores (ranking, exclusão, top-N) |
| `tests/test_skill_command.py` | 10 | Download e validação de templates de skill |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` com `quiet=True`, fallbacks, registo de skills |
| `tests/test_smart_excludes.py` | 15 | Filtro pathspec inteligente e novo descarregamento forçado |
| `tests/test_suggest_reviewers.py` | 14 | Revisores sugeridos no fluxo de PR (integração) |
| `tests/test_thinking_words.py` | 5 | Carregamento, parsing com o separador `;` e recarregamento forçado |
| `tests/test_updater.py` | 23 🆕 | Bloqueio do PyPI: parsing de versão, cache diária, obtenção, decisões do bloqueio, ligação à CLI |
| `tests/test_usage_log.py` | 27 🆕 | Registo de utilização: nome derivado da data, escrita síncrona, silêncio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semântico: major/minor/patch, alvos e validação |
| `tests/scm/test_contract.py` | 38 | Contrato `ScmProvider`: assinaturas, dataclasses, erros |
| `tests/scm/test_github_provider.py` | 57 | Provedor GitHub: REST, headers, PRs, issues, releases |
| `tests/scm/test_gitlab_provider.py` | 41 | Provedor GitLab: API v4, namespace, releases |
| `tests/scm/test_bitbucket_provider.py` | 39 | Provedor Bitbucket: Basic auth, workspace |
| `tests/scm/test_azure_devops_provider.py` | 43 | Provedor Azure DevOps: org/projeto, PRs, `ScmNotSupportedError` |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim obsoleto `github_api` → delega no provedor |
| `tests/scm/test_init_wizard.py` | 10 | Assistente `--init`: deteção de forge, validação, persistência |
| `tests/scm/test_release_publish.py` | 8 | Publicação de release por forge (GitHub/GitLab) |
| `tests/sync_i18n.py` | — | Script de verificação de cobertura i18n (scaffold; nunca executado) |

**Total:** 1060 cenários recolhidos em 49 ficheiros de teste (40 na raiz + 9 em `tests/scm/`; **+269** desde o relatório anterior, com **8 ficheiros novos**). Execução completa nesta máquina com `GITPR_LANG=en_us`: **1055 passed / 3 failed / 2 skipped / 33 subtests** em ~123s.

**Notas de qualidade desta versão:**
- **2 falhas reais (testes desatualizados, herdadas):** `test_net_timeouts.py` ainda afirma a predefinição de 600s para `GITPR_AI_TIMEOUT`, mas o código usa **180s** desde o fix `681a7fa`. É o mesmo item que já estava nos Próximos Passos do relatório anterior e **continua em aberto**.
- **1 falha de locale (nova, não é regressão):** `test_core.py::TestHooksLanguage::test_the_language_chosen_with_the_lang_flag_is_honoured` afirma `i18n.CURRENT_LANG == "pt_br"` — passa com o locale pt-BR da máquina e falha com `GITPR_LANG=en_us`. É a suíte do idioma dos hooks introduzida nesta janela.
- **Sensibilidade ao locale baixou de 4 para 1:** as 4 falhas ambientais da janela anterior (`test_chat_backend::test_api_exception`, `test_main_suggest_reviewers::test_flag_appears_in_contextual_help` e `test_suggest_reviewers` ×2) **passam agora** com `GITPR_LANG=en_us` — deixaram de ser o problema, mas a causa raiz (testes que assumem um idioma) persiste, apenas migrou para outro ficheiro.
- O `tests/conftest.py` passa a fixar `GITPR_SHOW_LOGS=false` e `GITPR_SKIP_UPDATE_CHECK=true` — a suíte não escreve no registo de utilização nem é bloqueada pelo bloqueio de atualização.

---

## **🌐 Internacionalização e Documentação**

* **Cobertura i18n:** **955 chaves** de tradução em cada um dos 6 dicionários (+213 desde o relatório anterior) com **paridade total de key sets**. As ~200 chaves novas cobrem a TUI de configuração e as superfícies do bloqueio do PyPI; 6 chaves obsoletas de atualização/binário foram removidas, 3 novas acrescentadas e 2 de ajuda reescritas. O `__lang_version__` passou v0.0.23 → v0.0.24 → **v0.0.25**, desencadeando o novo download OTA das traduções.
* **Fontes de tradução em lockstep:** uma chave nova tem de existir no código (fonte), em `langs/pt_br.json` (**lista mestra**), nos dicionários FR/ES de `scripts/sync_all_langs.py` (segunda fonte) e nos valores curados de `scripts/fix_mangled_i18n_keys.py` (terceira fonte, lida por `tests/test_i18n.py`); a asserção `len(CLEAN_KEYS)` passou de 50 para **49**.
* **Tópicos Novos 🆕 (2, ambos em 5 idiomas):**
  - `docs/config-tui.md` — o ecrã `gitpr config`: layout, leitura de valores, edição/gravação, validação em duas camadas, procura, âmbito excluído e uma secção para programadores
  - `docs/usage-log.md` — registo de utilização: onde vivem os ficheiros, o nome derivado da data, o formato da linha e o que não faz
* **Tópicos atualizados nesta janela (todos ressincronizados nos 5 idiomas):** `docs/ARCHITECTURE.md`, `docs/auto-update.md` (reescrito para o modelo exclusivo PyPI), `docs/hooks-versioning.md` (idioma efetivo dos hooks), `docs/mcp-integration.md`, `docs/skill-template.md`, `docs/testar_sem_usar_pypi.md` (sem binário) e `docs/version-markers.md` (novos marcadores).
* **Documentação em 5 idiomas:** **39 tópicos canónicos** em `docs/` — **34 com cobertura completa nos 5 idiomas** (+2 desde o relatório anterior) e 5 tópicos parciais/só em PT (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers`).
* **Skills Locais do Claude Code:** `.claude/skills/` com **29 skills** (as do projeto — `status-report`, `implement-fixes`, `caveman-commit`, `new-feature`, `code-review`, `wizard`, `grilling` etc. — mais o kit `mattpocock-skills`; o relatório anterior listava apenas 5). A memória ganhou `i18n-sync-canonicos-roundtrip.md` no commit da TUI de configuração.
* **Memory Index:** `.claude/memory/MEMORY.md` com 40 padrões (contagem inalterada nesta janela).
* **Relatórios de tarefas:** `docs/claude-code/reports/develop_natan/` (**90** no total; **+6** na janela — TUI de configuração, 5 correções de UI + registo de utilização, layout/secções/descarregamentos, secção Skills, limpeza de `PR_AUTO_PUBLISH` e remoção do binário) e `docs/gemini/reports/develop_natan/` (5 ficheiros; nenhum novo).
* **Relatórios de status:** `docs/reports/` (13 relatórios; este é o 14.º).
* **Planos de desenvolvimento:** 89 ficheiros em `docs/plans/` (+9 na janela — os planos da TUI de configuração, as correções do ecrã, a secção Skills, a limpeza de `PR_AUTO_PUBLISH`, a remoção do binário e o glossário `glossary-config-tui`) + 3 ficheiros em `docs/survey/`.

---

## **🔄 Pipeline de Distribuição**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Atualização obrigatória:** a execução verifica o PyPI no arranque e **bloqueia com código de saída 1** se existir uma versão mais recente, imprimindo `pip install --upgrade gitpr-cli`; a verificação é colocada em cache por dia e o `--update` apenas reporta
3. **GitHub Releases:** **removido** — sem PyInstaller, sem asset `.exe`, sem hot-swap; o `pyinstaller` saiu do `Pipfile` e o `icon.ico` foi eliminado
4. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml` (sempre instalou via pip, pelo que não foi afetado)
5. **MCP Server:** Entry point `gitpr-mcp` via `pyproject.toml`
6. **Templates e Idiomas OTA:** `templates/` e `langs/*.json` servidos do GitHub (main) — o bump `v0.0.25` renova as cópias locais em `~/.gitpr/langs/` assim que for publicado

---

## **📈 Evolução Desde o Relatório Anterior (v0.0.13)**

| Área | v0.0.13 (anterior) | v0.0.14 (atual) |
|------|-------------------|-----------------|
| **Versão GitPR** | 1.0.0 | **1.1.0** (CHANGELOG.md com cabeçalho `[1.1.0] - 2026-09-13`) |
| **Versão Idioma** | v0.0.23 | **v0.0.25** (via v0.0.24) |
| **Versão Scripts Hook** | v0.0.3 | **v0.0.3** |
| **Provedores IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 idiomas, 6 dicionários | 5 idiomas, 6 dicionários |
| **Interface** | CLI + TUIs (Issues, Chat, Dashboard, PR Publisher, LinterApp) + assistente `--init` + `gitpr release` | **+ TUI de configuração `gitpr config` (12 categorias, 56 campos, secção Skills) + registo geral de utilização** |
| **Ferramentas MCP** | 12 tools / 17 recursos / 7 prompts | 12 tools / 17 recursos / 7 prompts (+ `log_usage()` no ponto de entrada) |
| **Flags CLI** | 35 opções na raiz + subcomando `release` (6) | 35 opções na raiz + `release` (6) + **`config` (0 opções)** |
| **Variáveis de Ambiente** | 39 chaves em `DEFAULT_CONFIG` | **39 chaves** (+ `GITPR_SKIP_UPDATE_CHECK`; `GITPR_SHOW_LOGS` saiu das sombras e passou a campo na TUI) |
| **Linter** | Regex + bridge Checkstyle (wizard/TUI/relatório) | Inalterado (+ novo descarregamento forçado de presets a partir da TUI) |
| **Git Hooks** | O idioma dos scripts ignorava o `--lang` | **Corrigido: `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG` vs. `SCRIPTS_INSTALLED_LANG`, `effective_hook_lang()`** |
| **Mensagens de Commit** | Com trailer `Co-Authored-By` (opt-out) | Inalterado |
| **i18n (chaves por ficheiro)** | 742 × 6 (paridade total) | **955 × 6 (paridade total) — +213 chaves** |
| **Documentação** | 37 tópicos canónicos (32 completos + 5 parciais) | **39 tópicos canónicos (34 completos + 5 parciais) — 2 famílias novas ×5, 7 atualizados** |
| **Distribuição** | PyPI + GitHub Releases (binário PyInstaller) | **PyPI exclusivo — binário, hot-swap e `pyinstaller` removidos** |
| **Suíte de Testes** | 791 cenários (41 ficheiros) | **1060 cenários (49 ficheiros: 40 + 9 SCM) — en_us: 1055 passed / 3 failed (2 desatualizados + 1 de locale) / 2 skipped** |
| **Commits desde o relatório** | 10 commits | **2 commits** (`bf9f1b9`, `f108c4c`) |
| **PRs mergeados** | 5 PRs (#146, #151, #153, #155, #159) | **2 PRs (#162, #164)** |
| **Memory Index** | 40 padrões | **40 padrões** (+ `i18n-sync-canonicos-roundtrip.md`) |
| **Relatórios de tarefas** | 84 claude-code, 5 gemini | **90 claude-code (+6 na janela) e 5 gemini** |
| **Planos de desenvolvimento** | 80 | **89 (+9 na janela — TUI de configuração, correções do ecrã, Skills, limpeza e remoção do binário)** |

---

## **🚧 Próximos Passos**

* **Provedor Anthropic Claude:** Suporte direto à API do Claude (`claude-sonnet-5`).
* **Gráficos em ASCII/Textual no Dashboard:** Adicionar histogramas de tempo e gráficos de tendência de tokens na TUI de métricas.
* **Pipeline de Release no GitHub Actions:** Automação completa do build e do envio para o PyPI (a geração de changelog é agora local via `gitpr release` e o canal binário deixou de existir — falta apenas a automatização de CI/CD).
* **Seed Local de `.gitpr/conf/`:** O seed de templates de configuração local (smart-excludes, linter) continua pendente como subcomando próprio ou etapa do assistente; a TUI de configuração já oferece os **descarregamentos** desses ficheiros, mas não o seed no projeto.
* **Mais Provedores:** OpenAI direto, provedores locais adicionais.
* **Extrator i18n em `sync_i18n.py`:** O regex trunca literais com concatenação implícita (`__("a " "b")`) — migrar para AST (a guarda em `test_i18n.py` já usa AST e não depende do script).
* **Corrigir os Testes de Timeout Desatualizados:** `tests/test_net_timeouts.py` (linhas ~99/117/137/149) afirma uma predefinição de 600s, mas o código usa 180s desde o fix `681a7fa`; alinhar também a docstring desatualizada em `config.py` (ainda menciona "default 600"). **Item herdado, continua em aberto.**
* **Reconciliar a Versão do Projeto:** O `CLAUDE.md` ainda diz "Current version: 0.0.37" enquanto o `__version__` está em 1.1.0 e o CHANGELOG.md regista `[1.1.0] - 2026-09-13`. Definir uma convenção única e atualizar o CLAUDE.md. **Item herdado, continua em aberto.**
* **Dívida do Índice do README:** Os bullets das famílias `suggested-reviewers`, `scm-multiforge` **e agora `config-tui` e `usage-log`** não estão no índice — a dívida cresceu nesta janela.
* **Robustez de Locale nos Testes:** 1 teste é sensível ao locale pt_br da máquina (`test_core.py::TestHooksLanguage`) — fixar `GITPR_LANG=en_us` no setup ou mockar `TRANSLATIONS` para que a suíte fique 100% verde em qualquer máquina/CI.
* **`gitpr -h config` ignora o `-h` 🆕:** o subcomando abre a TUI em vez de mostrar a ajuda — o gate `if ctx.invoked_subcommand is not None: return` corre antes do bloco `help_flag`. Corrigi-lo mudaria o comportamento do `-h` para **todos** os subcomandos, pelo que exige uma decisão.
* **Secção Smart Exclude na TUI 🆕:** dos 12 itens reportados após a utilização do ecrã, o item 10 (a secção *Smart Exclude*) é o único entregável ainda não iniciado — o esboço está nos próximos passos do relatório de tarefa.
* **Dívidas registadas no plano da TUI de configuração 🆕:** O `DEFAULT_CONFIG` ficou redundante com o schema; o banner de abertura não lista `--dashboard`, `--init`, `--base` ou `--plugins`; a `LinterApp` não desativa a command palette.

### ✅ Concluídos nesta janela (2026-09-08 → 2026-09-13)

* ~~**Subcomando `gitpr config` com TUI master-detail**~~ — schema declarativo, camada de escrita do `.env`, validação em duas camadas, procura, docs ×5 (PR #162).
* ~~**Secção Skills na TUI**~~ — edição dos `.gitpr/skill/*.md` do projeto com escritas atómicas, registo `SKILL_FILES_BY_TYPE` unificado em `config.py` (PR #162).
* ~~**Registo geral de utilização (`GITPR_SHOW_LOGS`)**~~ — `src/usage_log.py`, um ficheiro por dia, escrita síncrona, silencioso (PR #162).
* ~~**Correção do idioma dos Git hooks**~~ — o `--lang` deixou de ser ignorado; `SCRIPTS_LANG` separado de `SCRIPTS_INSTALLED_LANG` (PR #162).
* ~~**Correção do badge de ambiente na TUI**~~ — `AMBIENT_ENV_KEYS` capturado antes do `load_dotenv` ao nível do módulo; o badge passou a significar o que promete (PR #162).
* ~~**Limpeza da chave morta `PR_AUTO_PUBLISH`**~~ — removida do `CLAUDE.md` e do `.env` do utilizador; §5 da doc da TUI corrigida nas 5 versões.
* ~~**Distribuição exclusiva via PyPI + bloqueio obrigatório de atualização**~~ — `enforce_update_required()`, `GITPR_SKIP_UPDATE_CHECK`, remoção do hot-swap, do binário e do `pyinstaller`; `docs/auto-update.md` reescrito ×5 (PR #164).
* ~~**i18n: +213 chaves e cadeia v0.0.23 → v0.0.25**~~ — paridade total de key sets nos 6 dicionários, com as três fontes de tradução em lockstep.
* ~~**Documentação das 2 famílias novas**~~ — `config-tui` e `usage-log` em 5 idiomas, mais 7 tópicos atualizados.

---

**Relatório gerado em:** 2026-09-13  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
