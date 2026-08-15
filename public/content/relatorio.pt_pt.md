# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.10 (2026-08-11)**

## **📌 Visão Geral**

O **GitPR** é uma ferramenta de CLI (Command Line Interface) avançada para automação de processos Git utilizando Inteligência Artificial (Google Gemini / DeepSeek / Ollama). O objetivo principal é atuar como um assistente inteligente local que faz Code Reviews, gera Pull Requests, mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo de trabalho do desenvolvedor (Shift Left).

**Novidades desta versão (v0.0.10):**

- **Invocação Direta de Ferramentas MCP via CLI (`gitpr-mcp --tool`):** As 12 ferramentas MCP do GitPR podem agora ser invocadas diretamente da linha de comandos com `gitpr-mcp --tool <name> [--tool-args '<json>']`, sem iniciar o servidor stdio JSON-RPC. O modo `--tool` (sem nome) lista todas as ferramentas disponíveis com as respetivas assinaturas. Ideal para debugging, scripts e uso manual.
- **Tratamento de Erros no Merge do PR:** O PR Publisher (TUI Textual) apresenta agora um ecrã modal de erro visível quando o merge do PR falha — especialmente HTTP 405 a indicar conflitos. Anteriormente, a falha era ignorada silenciosamente e o fluxo continuava como se tudo tivesse funcionado.
- **Novos Documentos MCP:** 3 novos tópicos de documentação MCP em 5 idiomas: `mcp-annotations.md` (anotações de ferramentas), `mcp-integration.md` (guia de integração), `mcp-prompts.md` (guia de prompts com template).

- **Versão atual:** 0.0.35
- **Versão dos dicionários de idioma:** v0.0.13
- **Versão dos scripts de hook:** v0.0.1
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
* **Testes:** Pytest + `unittest.mock` (13 ficheiros de teste, 207 cenários).
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
  * `--version` 🆕: Apresenta a versão atual do GitPR (via `@click.version_option`).
* **Variáveis de Ambiente:** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE`, `GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`.
* **Ajuda Contextual:** `-h --flag` apresenta documentação específica da funcionalidade com um link direto (ciente do idioma) para o GitHub.
* **--lang:** Força o idioma da interface na execução atual sem persistir a alteração.
* **--provider:** Força o provedor de IA (`gemini`, `deepseek`, `ollama`) na execução atual.
* **--mcp:** Inicia o servidor MCP no transporte stdio para integração com editores — **12 ferramentas anotadas + 15 recursos + 7 prompts**.
* **--install:** Assistente guiado em 4 passos que descarrega templates de skills, instala Git Hooks, configura o MCP nos editores e valida as chaves de API.
* **--metrics:** Sistema de telemetria local com âmbito por repositório: `--export`, `--purge`, `--dashboard` (TUI interativa com leitura da cache).
* **--status:** Lista os ficheiros não commitados categorizados (novos/modificados/eliminados) — rápido, sem IA, sem rede.

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
* **Tratamento de Erros no Merge 🆕:** Refatoração de `_do_merge` em 3 métodos com separação de responsabilidades: `_do_merge` (executa numa thread), `_on_merge_success` (callback de sucesso), `_on_merge_failure` (callback de falha com ecrã modal de erro). HTTP 405 (conflitos) apresenta uma mensagem clara e oferece a abertura no browser para resolução manual. Registo de `final_action` ("merged"/"merge_failed") para feedback visual pós-TUI com cores corretas.

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
* **Versões Centralizadas:** `__version__` (0.0.35), `__lang_version__` (v0.0.13), `__scripts_version__` (v0.0.1), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`.

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
* **Cobertura:** 503 chaves de tradução em pt_BR.
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

### **16. Servidor MCP e Invocação Direta via CLI (`src/mcp_server.py`)** 🆕

* **12 Ferramentas MCP Anotadas:** Ferramentas para `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **15 Recursos + 7 Prompts com Template:** 35 ficheiros de template em `templates/gitpr.prompt.*.md`.
* **Invocação Direta via CLI 🆕:** O comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca qualquer ferramenta MCP diretamente sem iniciar o servidor stdio JSON-RPC.
* **Padrão de Registo 🆕:** `_TOOL_FUNCS` mapeia nome da ferramenta → callable; `_get_tool_registry()` funde com os metadados do catálogo.
* **Isolamento Real do Stdout 🆕:** `_write_real_stdout()` escreve diretamente no `sys.__stdout__` original (guardado antes do monkey-patching), garantindo JSON puro no stdout.
* **Listagem de Ferramentas 🆕:** `gitpr-mcp --tool` (sem nome) lista as 12 ferramentas disponíveis com as assinaturas dos parâmetros.
* **Carregamento Automático do .env 🆕:** Chaves de API automaticamente disponíveis em modo CLI.
* **Novos Documentos MCP 🆕:** `docs/mcp-annotations.md`, `docs/mcp-integration.md`, `docs/mcp-prompts.md` em 5 idiomas cada (15 novos ficheiros).
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

* **Versões Independentes:** `__scripts_version__` (v0.0.1) controla a versão dos scripts de hook.
* **Deteção Automática:** Compara a versão local com a mais recente e atualiza automaticamente.
* **Ciente do Idioma:** Descarrega os templates de hook correspondentes ao idioma configurado.

---

## **📊 Testes e Qualidade**

| Ficheiro de Teste | Cenários | Foco |
|------------------|----------|------|
| `tests/test_core.py` | 25+ | Fluxos principais, git diff, geração de PR, tempos |
| `tests/test_chat_backend.py` | 30+ | Memória do chat, persistência, comandos slash |
| `tests/test_plugins.py` | 17 | Descoberta de plugins, fusão de regras do linter, prompts MCP |
| `tests/test_mcp_server.py` | 75+ 🆕 | Ferramentas MCP, recursos, anotações, patching, CLI direta |
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

**Total:** 207 cenários de teste automatizados a passar (13 ficheiros de teste). 1 falha conhecida em `test_metrics.py::test_app_skips_export_and_config_files` (pré-existente, não relacionada com as alterações recentes).

---

## **🌐 Internacionalização e Documentação**

* **Cobertura i18n:** 503 chaves de tradução em pt_BR.
* **Novos Documentos Técnicos 🆕:** 3 novos tópicos MCP em 5 idiomas cada (15 ficheiros):
  - `docs/mcp-annotations.md` — catálogo de anotações para as 12 ferramentas MCP
  - `docs/mcp-integration.md` — guia de integração do MCP para editores (VS Code, Cursor, Claude Code, Claude Desktop, Zed)
  - `docs/mcp-prompts.md` — referência para os 7 prompts MCP com template
* **Documentação Existente:** `docs/plugins-system.md`, `docs/smart-excludes.md`, `docs/untracked-files.md` e mais — tudo em 5 idiomas.
* **Documentação em 5 idiomas:** 37 tópicos únicos em `docs/` (+3 novos tópicos MCP).
* **Índice de Memória:** `.claude/memory/MEMORY.md` com 20 padrões de arquitetura (+2 novos: mcp-tool-cli-invocacao-direta, merge-conflict-error-handling).
* **Relatórios de tarefas:** `docs/claude-code/reports/` e `docs/reports/` (10 relatórios de estado).
* **Planos de desenvolvimento:** 10+ planos documentados em `docs/plans/`.

---

## **🔄 Pipeline de Distribuição**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → upload automatizado
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **Servidor MCP:** Ponto de entrada `gitpr-mcp` via `pyproject.toml`

---

## **📈 Evolução Desde o Relatório Anterior (v0.0.9)**

| Área | v0.0.9 (anterior) | v0.0.10 (atual) |
|------|-------------------|----------------|
| **Versão do GitPR** | 0.0.34 | **0.0.35** |
| **Versão do Idioma** | v0.0.12 | **v0.0.13** |
| **Versão dos Scripts de Hook** | v0.0.1 | v0.0.1 |
| **Provedores de IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI + **CLI MCP Direta** |
| **Ferramentas MCP** | 10 ferramentas | **12 ferramentas (+ list_unstaged_files, analyze_unstaged_diff)** |
| **CLI MCP Direta** | — | **`gitpr-mcp --tool <name>` — invocação direta sem servidor** |
| **Tratamento de Merge do PR** | O fluxo ignorava erros de merge | **Ecrã modal de erro para HTTP 405 (conflitos) + feedback visual** |
| **Flags CLI** | 25 flags | **26 flags (+ `--version`)** |
| **Variáveis de Ambiente** | 16 variáveis | 16 variáveis |
| **Documentação** | 34 tópicos | **37 tópicos (+3: mcp-annotations, mcp-integration, mcp-prompts em 5 idiomas)** |
| **Suite de Testes** | 171 cenários (13 ficheiros) | **207 cenários (13 ficheiros, +36 testes MCP)** |
| **Commits desde v0.0.34** | — | **2 commits (mcp-tool-cli + merge-error-handling)** |
| **PRs Fundidos** | — | **2 PRs (#107, #110)** |

---

## **🚧 Próximos Passos**

* **Testes para o PR Publisher:** Cobertura de testes unitários e de integração para o fluxo de publicação de PR (`pr_publish_app.py`, `github_api.py`).
* **Testes de integração end-to-end para o MCP:** Validação de chamadas a ferramentas e prompts através de um cliente stdio simulado.
* **Provedor Anthropic Claude:** Suporte direto à API do Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual no Dashboard:** Adicionar histogramas de tempo e gráficos de tendência de tokens à TUI de métricas.
* **Pipeline de release no GitHub Actions:** Automação completa do build PyInstaller e upload de artefactos para GitHub Releases.
* **Mais provedores:** OpenAI direto, provedores locais adicionais.
* **Comando local `--init`:** Seed de `.gitpr/conf/` com templates de configuração locais (smart-excludes, linter, etc.).

---

**Relatório gerado em:** 2026-08-11  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
