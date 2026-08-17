# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.11 (2026-08-15)**

## **📌 Visão Geral**

O **GitPR** é uma ferramenta de CLI (Command Line Interface) avançada para automação de processos Git utilizando Inteligência Artificial (Google Gemini / DeepSeek / Ollama). O objetivo principal é atuar como um assistente inteligente local que faz Code Reviews, gera Pull Requests, mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo de trabalho do desenvolvedor (Shift Left).

**Novidades desta versão (v0.0.11):**
- **Correção de Seleção e Erros no Staging (`stage_files`):** A TUI de staging passa a ler a seleção real de `SelectionList.selected` (toggles individuais respeitados) e `stage_files()` devolve `(success, error_message)` — falhas do `git add` apresentam o erro real do git em vez de uma falsa mensagem de sucesso. O staging passou a acontecer uma única vez por fluxo (antes era duplicado entre o modal e o `check_unstaged_files`).
- **Skip de Mensagem IA em Commits Gerados pelo Git:** Os hooks `prepare-commit-msg` (5 variantes de idioma) passam a saltar todas as fontes geradas pelo git (`merge`, `squash`, `amend`, `commit` — antes só `message`), com verificação belt-and-braces de `.git/MERGE_HEAD`. `git pull`/`git merge` já não corrompem o `.git/MERGE_MSG` com mensagem de IA. Novo helper `is_merge_in_progress()` no core e guard no modo hook. `__scripts_version__` → v0.0.2 com auto-sync dos hooks.
- **Traduções de Estado de Ficheiro:** Labels de estado ("Modified", "Deleted", "New") traduzidos nos pacotes es, es_es, fr, fr_fr, pt_br e pt_pt — cobertura pt_BR subiu para 507 chaves.
- **Documentação Multilingue Expandida e Sincronizada:** `docs/pr-descricao-padrao.md` reescrito em EN canónico + 4 locales com secção de publicação (modos de execução, atalhos TUI, PAT); `docs/mcp-integration.md` sincronizado nos 5 idiomas (2 ferramentas em falta na tabela + nova subsecção de recursos de prompt); `docs/git-hooks-locais.md` documenta o skip de merge-source nos 5 idiomas.
- **Novo Template MCP:** `templates/gitpr.mcp-jsonrpc-calls.md` — referência de chamadas JSON-RPC para as ferramentas MCP.

- **Versão atual:** 0.0.36
- **Versão dos dicionários de idioma:** v0.0.13
- **Versão dos scripts de hook:** v0.0.2
- **Publicação:** PyPI (`pip install gitpr-cli`) + GitHub Releases (binário standalone)
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositório:** [github.com/natafiuza/gitpr](https://github.com/natafiuza/gitpr)
- **Licença:** LGPL-2.1
- **Idiomas suportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas)

---

## **🏗️ Arquitetura Base e Bibliotecas**

* **Linguagem:** Python >= 3.10
* **Framework CLI:** Click (para comandos, flags e formatação de terminal).
* **UI/Terminal:** Textual — TUI (Text User Interface) para chat interativo, edição de issues, ecrã de ajuda, dashboard de métricas e PR Publisher.
* **Criptografia:** `cryptography.fernet` para proteção local de chaves de API e tokens do GitHub.
* **Configuração:** `python-dotenv`, `pyyaml` (para o linter estático).
* **Provedores de IA:** Integração através do SDK oficial Google GenAI (`gemini-2.5-flash`), SDK OpenAI (`DeepSeek`) e SDK OpenAI (Ollama local).
* **API do GitHub:** `requests` (API REST via PAT) — módulo `src/github_api.py` com `create_pull_request()`, `update_pull_request()`, `merge_pull_request()`.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — 12 ferramentas anotadas, 15 recursos, 7 prompts.
* **Testes:** Pytest + `unittest.mock` (13 ficheiros de teste, 214 cenários).
* **Empacotamento:** PyInstaller (binário standalone) + setuptools/build (PyPI).
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
* **Exclusões Inteligentes com Duas Camadas:** Filtro de pathspec inteligente com camada global (`~/.gitpr/conf/`) + camada local ao projeto (`./.gitpr/conf/`). Junção em runtime (união, deduplicada). Auto-seed do ficheiro local na primeira execução. Suporte a 3 variáveis de ambiente (`GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`).
* **Métricas com Registo de Tempo:** Injeção de `log_command_metric()` em todos os fluxos passando a duração em milissegundos (`duration_ms`), com imports lazy.
* **Resolução Centralizada do Output:** Função `resolve_output_path()` que centraliza a lógica do diretório de output — por predefinição em `.gitpr/reports/{type}/`, com recurso a caminhos personalizados do `.env`.
* **Deteção de Merge em Curso 🆕:** Helper `is_merge_in_progress()` (verifica `git rev-parse -q --verify MERGE_HEAD`, silencioso e worktree-safe) — usado como defesa em profundidade contra hooks antigos que chamam a CLI durante um merge.
* **Staging com Erro Real 🆕:** `stage_files()` devolve agora o tuplo `(success, error_message)`, capturando o stderr/stdout do `git add` em falhas — o erro real do git chega ao utilizador em vez de ser engolido.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitetura de Plugins:** Sistema de extensibilidade que carrega plugins do diretório `~/.gitpr/plugins/`, aplicando-os a **todos os projetos**.
* **Plugins de Linter (`linter/`):** Ficheiros `.yml` com regras de regex adicionais fundidas com o `.gitpr.linter.yml` local.
* **Plugins de Prompts MCP (`prompts/`):** Ficheiros `.md` que estendem o contexto do sistema com instruções específicas.
* **Closures de Fábrica:** Funções `get_linter_plugins` e `get_prompt_plugins` com closures para isolar o estado entre sessões.
* **Comando `--plugins`:** Lista todos os plugins globais instalados com os respetivos tipos e caminhos.
* **Documentação Multilingue:** `docs/plugins-system.md` em 5 idiomas (EN, PT-BR, PT-PT, ES, FR).

### **3. Interface CLI e Configuração (`src/main.py` e `src/config.py`)**

* **Configuração Inicial:** Deteta a primeira execução, cria a pasta `~/.gitpr/` e pede interativamente as chaves de API, preferências e idioma.
* **Roteamento de Comandos:** Gere todas as flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--publish`, `--no-publish`, `--no-edit`, `--base`, `--lang`, `--provider`, `--pre-save`, `--plugins`).
* **Comportamento Predefinido:** Executar `gitpr` sem flags abre a TUI do PR Publisher.
* **Flags:**
  * `--publish`: Abre a TUI interativa para rever, editar e publicar o PR.
  * `--no-publish`: Gera a descrição do PR e guarda-a localmente sem abrir o editor interativo.
  * `--no-edit`: Ignora a TUI por completo — auto-commit (com validação do linter), auto-push e publica diretamente no GitHub.
  * `--base <branch>`: Substitui a branch alvo do Pull Request.
  * `--plugins`: Lista os plugins globais instalados.
  * `--version`: Apresenta a versão atual do GitPR (via `@click.version_option`).
* **Variáveis de Ambiente:** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE`, `GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`.
* **Ajuda Contextual:** `-h --flag` apresenta documentação específica da funcionalidade com um link direto (ciente do idioma) para o GitHub.
* **--lang:** Força o idioma da interface na execução atual sem persistir a alteração.
* **--provider:** Força o provedor de IA (`gemini`, `deepseek`, `ollama`) na execução atual.
* **--mcp:** Inicia o servidor MCP no transporte stdio para integração com editores — **12 ferramentas anotadas + 15 recursos + 7 prompts**.
* **--install:** Assistente guiado em 4 passos que descarrega templates de skills, instala Git Hooks, configura o MCP nos editores e valida as chaves de API.
* **--metrics:** Sistema de telemetria local com âmbito por repositório: `--export`, `--purge`, `--dashboard` (TUI interativa com leitura da cache).
* **--status:** Lista os ficheiros não commitados categorizados (novos/modificados/eliminados) — rápido, sem IA, sem rede.
* **Guard de Merge no Modo Hook 🆕:** No fluxo de commit em modo hook, se `is_merge_in_progress()` devolver True a execução termina silenciosamente com exit 0 antes de qualquer diff ou chamada de IA.
* **Feedback Real de Staging 🆕:** `check_unstaged_files()` verifica o resultado de `stage_files()` nos 3 pontos de chamada (resultado da TUI, auto-stage de pr/issue, auto-stage de commit) e apresenta "❌ Failed to stage files: {erro real do git}" em falhas.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` e `src/ui/pr_publish_help.py`)**

* **Interface Interativa Completa:** TUI construída com Textual para rever, editar e publicar Pull Requests diretamente no terminal.
* **6 Ecrãs Modais:** `CommitConfirmScreen`, `FileStageScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Ecrã Modal de Ficheiros Não-Stageados Melhorado:** Lista de ficheiros com altura fixa (`height: 6`) e scroll vertical interno.
* **Bindings:** F1 (Ajuda), F2 (Guardar .md local), F3 (Publicar via API do GitHub), Esc (Sair).
* **Fluxo de Auto-Commit:** Linter → mensagem da IA → confirmação → commit → push → publicar PR.
* **Verificação de Ficheiros Não-Stageados:** No arranque, verifica `git status --porcelain` e oferece um ecrã modal para selecionar, saltar ou cancelar.
* **Tratamento de PR Existente:** Deteta PRs abertos para a branch atual via API do GitHub e oferece push ou criação de novo.
* **Auto-Upstream:** Deteta falhas de `git push` por ausência de upstream e tenta novamente automaticamente com `--set-upstream origin <branch>`.
* **Deteção de "Nothing to commit":** Trata `git commit` sem mudanças como sucesso.
* **Fluxo de Merge:** Após a criação/atualização do PR, oferece uma opção de merge. Controlado por `GITPR_AUTO_MERGE`.
* **Tratamento de Erros no Merge:** Callbacks `_on_merge_success` / `_on_merge_failure` com ecrã modal de erro para HTTP 405 (conflitos) e feedback visual pós-TUI.
* **Seleção Real de Ficheiros 🆕:** `StageFilesScreen.btn_stage` lê a seleção diretamente de `SelectionList.selected` — toggles individuais de linha (clique/Enter) passam a ser respeitados; removido o dicionário manual `_selected` que ficava dessincronizado e o `git add` duplicado dentro da TUI (staging único no `main.py`).

### **5. Módulo de API do GitHub (`src/github_api.py`)**

* **Funções Partilhadas:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` — encapsulam chamadas REST à API v3 do GitHub.
* **Autenticação PAT:** Token de acesso pessoal validado com `GET /user` antes das operações.
* **Reutilização:** Funções usadas tanto pela TUI de PR como pela TUI de issues.

### **6. Motor de Análise Estática / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analisa estaticamente as linhas adicionadas (`+`) do git diff sem gastar quota de IA.
* **Regras YAML:** Lê o ficheiro `.gitpr.linter.yml` local (criado via `--skill`). Suporta regex de validação, ignorar comentários e ignorar diretórios específicos.
* **Plugins de Linter:** Regras adicionais carregadas de `~/.gitpr/plugins/linter/*.yml` e fundidas com as regras locais.
* **Template Multilingue:** Templates de linter disponíveis em 5 idiomas.
* **Integração com Auto-Commit:** Executa automaticamente antes do commit no fluxo de publicação do PR.

### **7. Segurança e Autenticação (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografia:** Gera uma chave mestra `secret.key` na pasta `~/.gitpr/`.
* **Proteção de Tokens:** `encrypt_data` e `decrypt_data` para proteger chaves de API da IA e o PAT do GitHub.
* **Validação do Token do GitHub:** `validate_github_token()` com uma chamada leve (`GET /user`).
* **Fluxo de Reautenticação Automática:** Se o token expirar durante `gitpr -is`, captura o 401, pede um novo token e relança a TUI preservando o rascunho.

### **8. Auto-Atualizador (`src/updater.py`)**

* **Hot-Swap:** Verifica a versão mais recente via API de GitHub Releases, descarrega o binário compilado e substitui-o sem interromper a execução em curso (com rollback).
* **Cache Diária:** Evita verificações repetidas no mesmo dia.
* **Verificação de Ligação:** Socket `8.8.8.8:53` antes de qualquer operação de rede.
* **Versões Centralizadas:** `__version__` (0.0.36), `__lang_version__` (v0.0.13), `__scripts_version__` (v0.0.2), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`.

### **9. Interface de Chat Interativa (`src/ui/chat_app.py`)**

* **TUI Completa:** Construída com Textual — histórico de mensagens, input de várias linhas, barra de estado com bindings visíveis.
* **Memória por Branch (`src/chat_memory.py`):** Histórico de conversa persistido por branch, permitindo continuidade entre sessões.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear` — atalhos para programação em par.
* **Auto-Patching (F5):** Extrai blocos de código sugeridos pela IA e exporta-os para um ficheiro de patch.
* **Atualização do Diff (F2):** Recarrega o `git diff` atual sem reiniciar a sessão.
* **Exportação da Sessão (F6):** Guarda o histórico completo do chat para documentação.

### **10. Internacionalização — i18n (`src/i18n.py`)**

* **Sistema Inspirado no Laravel:** Função `__()` com suporte para placeholders nomeados (`{count}`, `{file}`, etc.).
* **Deteção Automática:** Deteta o idioma do SO na primeira execução e guarda-o em `GITPR_LANG`.
* **5 Idiomas:** en_us (predefinido/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Ficheiros Versionados:** `__lang_version__` (v0.0.13) controla a atualização dos pacotes de idioma (`langs/*.json`).
* **Cobertura:** 507 chaves de tradução em pt_BR.
* **Traduções de Estado de Ficheiro 🆕:** Chaves "Modified", "Deleted" e "New" traduzidas nos 6 pacotes não-ingleses (es, es_es, fr, fr_fr, pt_br, pt_pt).
* **Cache com Indexação por Idioma:** As respostas de IA em cache incluem o idioma atual na chave MD5.
* **Script de Sincronização:** `tests/sync_i18n.py` para deteção automática de chaves órfãs.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Thread em segundo plano durante chamadas à IA que apresenta caracteres braille com palavras de "pensamento".
* **Delimitador:** Separador de frases usando ponto e vírgula (`;`), compatível com frases complexas que contêm vírgulas.
* **Velocidade Adaptativa e Flickering:** Animação de revelação de caracteres adaptada para frases longas e uso de ANSI `\033[K` para evitar artefactos visuais no terminal.
* **263 entradas por idioma:** Sincronizadas nos 5 idiomas.

### **12. Provedores de IA (`src/ai_providers.py`)**

* **3 Provedores Suportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Medição de Duração:** Injeção de `duration_ms` (cronometragem de alta precisão via `time.perf_counter()`) em `meta_raw` e `_telemetry_meta`.
* **Modo JSON e Parâmetros Determinísticos:** Outputs estruturados com `temperature=0.0` e `top_p=0.1`.

### **13. Cache Inteligente (`src/cache.py`)**

* **MD5 + Metadados:** Chave baseada no hash MD5 do diff e do prompt.
* **Indexação por Idioma:** O campo `lang` foi adicionado à chave da cache.
* **Telemetria e Duração:** Persistência dos campos `duration_ms` e `meta_raw` nos ficheiros de cache.
* **Leitura do Dashboard:** `scan_cache_files_for_dashboard()` lê todos os ficheiros de cache recursivamente.

### **14. Motor de Issues e TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff atual, histórico da Branch (`-ht`) e arqueologia de Blame (`-b`).
* **Map-Reduce para Issues:** Quando o contexto excede ~90k tokens, divide automaticamente em blocos e unifica os resultados.
* **TUI Interativa:** Edição de rascunho, atalho F2 (guardar localmente), F3 (publicar no GitHub) e F1 (ajuda).
* **Tratamento de 401:** Sinalização de reautenticação sem fechar a aplicação.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Acompanha a evolução histórica e a autoria de trechos de código com classificação de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registados via `log_blame_metric()` com acompanhamento da profundidade e do número de commits analisados.

### **16. Servidor MCP e Invocação Direta via CLI (`src/mcp_server.py`)**

* **12 Ferramentas MCP Anotadas:** Ferramentas para `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **15 Recursos + 7 Prompts com Template:** 35 ficheiros de template em `templates/gitpr.prompt.*.md`.
* **Invocação Direta via CLI:** O comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca qualquer ferramenta MCP diretamente sem iniciar o servidor stdio JSON-RPC.
* **Padrão de Registo:** `_TOOL_FUNCS` mapeia nome da ferramenta → callable; `_get_tool_registry()` funde com os metadados do catálogo.
* **Isolamento Real do Stdout:** `_write_real_stdout()` escreve diretamente no `sys.__stdout__` original (guardado antes do monkey-patching), garantindo JSON puro no stdout.
* **Listagem de Ferramentas:** `gitpr-mcp --tool` (sem nome) lista as 12 ferramentas disponíveis com as assinaturas dos parâmetros.
* **Carregamento Automático do .env:** Chaves de API automaticamente disponíveis em modo CLI.
* **Novo Template JSON-RPC 🆕:** `templates/gitpr.mcp-jsonrpc-calls.md` — referência de chamadas JSON-RPC para as ferramentas MCP.
* **Instalador Automático:** Configuração dos editores suportados (VS Code, Cursor, Claude Code, Claude Desktop, Zed) com merge inteligente de JSON.

### **17. TUI de Dashboard de Métricas (`src/ui/metrics_app.py`)**

* **Âmbito por Repositório (Repo-Scope):** Etiqueta `📁 Repository: owner/repo` e filtragem estrita de eventos e dados de cache por projeto.
* **Leitura Assíncrona com Overlay:** Thread de trabalho em segundo plano com o widget `ProgressBar`.
* **Consolidação de Dados:** `load_cache_token_summary()` adiciona os tokens de cache ao totalizador.
* **Controlo do Estado da Cache:** Ficheiro de registo em `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Exportação Local:** Gravação CSV/JSON em `./.gitpr/metrics/export/`.

### **18. Sistema de Métricas e Telemetria (`src/metrics.py`)**

* **Âmbito por Repositório:** Todos os eventos indexados por `repo_name`.
* **Novos Eventos:** Eventos para listagem de ficheiros não-stageados e exportação de telemetria.
* **Eventos de Hook:** `log_hook_event()` para hooks de Git (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge).
* **Eventos de Linter e Blame:** `log_linter_metric()` e `log_blame_metric()`.
* **Exportação Local:** `--metrics --export` gera CSV e JSON em `./.gitpr/metrics/export/` com filtragem por repositório.
* **Limpeza:** `--metrics --purge` remove todos os ficheiros de métricas locais com confirmação interativa.

### **19. Sincronização de Git Hooks**

* **Versões Independentes:** `__scripts_version__` (v0.0.2) controla a versão dos scripts de hook.
* **Deteção Automática:** Compara a versão local com a mais recente e atualiza automaticamente.
* **Ciente do Idioma:** Descarrega os templates de hook correspondentes ao idioma configurado.
* **Skip de Merge-Source 🆕:** O template `prepare-commit-msg` (5 variantes de idioma) usa agora um case POSIX que salta as fontes `message|merge|squash|commit` e verifica `.git/MERGE_HEAD` como belt-and-braces — commits gerados pelo git (`git pull`, `git merge`, `--amend`, `-c`/`-C`, `--squash`) preservam a mensagem original do git.

---

## **📊 Testes e Qualidade**

| Ficheiro de Teste | Cenários | Foco |
|------------------|----------|------|
| `tests/test_core.py` | 32+ 🆕 | Fluxos principais, git diff, geração de PR, tempos, merge em curso, staging |
| `tests/test_chat_backend.py` | 30+ | Memória do chat, persistência, comandos slash |
| `tests/test_plugins.py` | 17 | Descoberta de plugins, fusão de regras do linter, prompts MCP |
| `tests/test_mcp_server.py` | 75+ | Ferramentas MCP, recursos, anotações, patching, CLI direta |
| `tests/test_metrics.py` | 36+ | Recolha, exportação local, âmbito do repo, resumo de tokens da cache, duration_ms |
| `tests/test_smart_excludes.py` | 14+ | Filtro de pathspec inteligente |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompts MCP e fallback de idioma |
| `tests/test_blame_metrics.py` | 10+ | Métricas de blame: profundidade, commits, duração |
| `tests/test_linter_metrics.py` | 8+ | Métricas de linter: erros, avisos, duração |
| `tests/test_thinking_words.py` | 9+ | Carregamento e parsing com separador `;` |
| `tests/test_skill_command.py` | 5+ | Descarregamento e validação de templates de skill |
| `tests/test_install_wizard.py` | 5+ | Assistente de instalação interativo |
| `tests/test_pre_save.py` | 3+ | Flag `--pre-save` e payload JSON |
| `tests/sync_i18n.py` | — | Script de verificação da cobertura i18n (chaves órfãs) |

**Total:** 214 cenários de teste automatizados a passar (13 ficheiros de teste). Execução completa verificada nesta versão: **214/214 passed**. Novos testes: `TestIsMergeInProgress` (3 casos de merge em curso) e `TestStageFiles` (4 casos: lista vazia, sucesso, falha com erro do git, exceção). A falha conhecida relatada no v0.0.10 em `test_metrics.py::test_app_skips_export_and_config_files` não se reproduziu nesta execução.

---

## **🌐 Internacionalização e Documentação**

* **Cobertura i18n:** 507 chaves de tradução em pt_BR (+4: labels de estado de ficheiro e mensagem de erro de staging).
* **Documentos Atualizados 🆕 (todos em 5 idiomas):**
  - `docs/pr-descricao-padrao.md` — reescrito em EN canónico (convenção de docs multilíngue) + 4 locales; secção de publicação com 3 modos de execução (`gitpr`, `--no-publish`, `--no-edit`), atalhos TUI (F1/F2/F3/Esc), requisito de PAT e resolução da branch base; caminho de saída corrigido para `.gitpr/reports/pr_desc/`
  - `docs/mcp-integration.md` — sincronizado com a implementação: 2 ferramentas em falta na tabela (`list_unstaged_files`, `analyze_unstaged_diff`), nova subsecção de recursos de prompt (`prompt://*`, plugins, prompts built-in) e secção do Claude Code nas 4 traduções
  - `docs/git-hooks-locais.md` — documenta o skip de merge-source do hook `prepare-commit-msg` (merge/squash/amend preservam a mensagem do git)
* **Documentação em 5 idiomas:** 34 tópicos canónicos em `docs/` (28 com cobertura completa nos 5 idiomas).
* **Índice de Memória:** `.claude/memory/MEMORY.md` com 27 padrões em 3 categorias (20 de projeto, 3 de referência, 4 de feedback).
* **Relatórios de tarefas:** `docs/claude-code/reports/` (+4 novos: pr-descricao-padrao multilíngue, fix prepare-commit-msg merge skip, unstaged modal stage fix, MCP docs sync) e `docs/gemini/reports/`.
* **Relatórios de estado:** `docs/reports/` (11 relatórios de estado).
* **Planos de desenvolvimento:** 11+ planos documentados em `docs/plans/`.

---

## **🔄 Pipeline de Distribuição**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → upload automatizado
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **Servidor MCP:** Ponto de entrada `gitpr-mcp` via `pyproject.toml`

---

## **📈 Evolução Desde o Relatório Anterior (v0.0.10)**

| Área | v0.0.10 (anterior) | v0.0.11 (atual) |
|------|-------------------|----------------|
| **Versão do GitPR** | 0.0.35 | **0.0.36** |
| **Versão do Idioma** | v0.0.13 | v0.0.13 |
| **Versão dos Scripts de Hook** | v0.0.1 | **v0.0.2** |
| **Provedores de IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI |
| **Ferramentas MCP** | 12 ferramentas | 12 ferramentas |
| **Flags CLI** | 26 flags | 26 flags |
| **Variáveis de Ambiente** | 16 variáveis | 16 variáveis |
| **Staging de Ficheiros** | Seleção via dicionário manual (dessincronizada) + falhas de `git add` silenciosas + staging duplicado | **Seleção real (`SelectionList.selected`) + erro real do git apresentado + staging único por fluxo** |
| **Hooks de Commit** | A IA saltava apenas a fonte `message` | **IA salta `message/merge/squash/commit` + verificação de `.git/MERGE_HEAD` + guard `is_merge_in_progress()`** |
| **i18n (chaves pt_BR)** | 503 | **507 (+ labels de estado de ficheiro e erro de staging)** |
| **Documentação** | 37 tópicos | **34 tópicos canónicos em `docs/` (28 com 5 idiomas completos) — 3 tópicos atualizados (pr-descricao-padrao, mcp-integration, git-hooks-locais)** |
| **Suite de Testes** | 207 cenários (13 ficheiros) | **214 cenários (13 ficheiros, +7: merge em curso e staging)** |
| **Commits desde o relatório** | 2 commits | **4 commits (i18n status, merge skip, docs hooks, staging fix)** |
| **PRs Fundidos** | 2 PRs (#107, #110) | **2 PRs (#111, #114)** |
| **Índice de Memória** | 20 padrões | **27 padrões em 3 categorias (projeto/referência/feedback)** |

---

## **🚧 Próximos Passos**

* **Testes para o PR Publisher:** Cobertura de testes unitários e de integração para o fluxo de publicação de PR (`pr_publish_app.py`, `github_api.py`).
* **Testes de integração end-to-end para o MCP:** Validação de chamadas a ferramentas e prompts através de um cliente stdio simulado.
* **Provedor Anthropic Claude:** Suporte direto à API do Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual no Dashboard:** Adicionar histogramas de tempo e gráficos de tendência de tokens à TUI de métricas.
* **Pipeline de release no GitHub Actions:** Automação completa do build PyInstaller e upload de artefactos para GitHub Releases.
* **Mais provedores:** OpenAI direto, provedores locais adicionais.
* **Comando local `--init`:** Seed de `.gitpr/conf/` com templates de configuração locais (smart-excludes, linter, etc.).
* **Traduções pendentes de staging nos restantes idiomas:** As novas chaves de erro de staging existem em pt_br — propagar para pt_pt, es_es e fr_fr na próxima mudança de versão de idioma.
* **Dead code na TUI:** A classe rascunho `FileStageScreen` duplica `StageFilesScreen` — integrar ou remover.
* **Ajustes de documentação MCP:** A ajuda do `gitpr-mcp --install` omite `claude-code` na lista de editores; documentar o alias oculto `gitpr --mcp` em `mcp-integration.md`.

---

**Relatório gerado em:** 2026-08-15  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
