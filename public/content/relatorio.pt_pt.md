# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.15 (2026-09-17)**

## **📌 Visão Geral**

O **GitPR** é uma ferramenta de CLI (Command Line Interface) avançada para automação de processos Git utilizando Inteligência Artificial (Google Gemini / DeepSeek / Ollama). O objetivo principal é atuar como um assistente inteligente local que faz Code Reviews, gera Pull Requests, mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo de trabalho do desenvolvedor (Shift Left).

**Novidades desta versão (v0.0.15):**
- **Subcomando `gitpr fix` — o review que se torna um patch aplicável:** Fecha o ciclo entre o review automático e a correção. O último review da cache alimenta **uma** chamada de IA, que devolve apontamentos em blocos delimitados; o extrator valida cada bloco como diff unificado, o `git apply --check` prova que ele encaixa na árvore atual e um classificador **determinístico e sem I/O** rotula cada candidato como `safe`, `review_required` ou `experimental`. O dry-run é a predefinição — escrever exige `--apply`. O `--force` nunca contorna a verificação de aplicabilidade, apenas a classificação, e exige uma frase de confirmação escrita. Tudo o que foi aplicado entra em `.gitpr/fix_history.json`, que é o que o `--rollback` lê.
- **Subcomando `gitpr review-pr <n>` — revisar o PR de outra pessoa sem checkout:** O diff vem diretamente da API da forge e entra **no mesmo motor** que os fluxos locais usam — mesmo relatório, mesmas regras de linter, mesmo `.txt`. Apenas de leitura por predefinição: nada é publicado na forge sem um `--post-comment` explícito. Alarga o público-alvo de "quem vai abrir um PR" para "quem foi convidado a revisar o PR de outra pessoa".
- **Resolução de identidade do revisor — a associação que não chegava:** As sugestões nascem do `git blame`, pelo que carregam **nomes e e-mails**, não logins. Quando nada resolvia, a UI mostrava o nome em bruto — e escrever esse nome de volta fazia o GitPR enviá-lo *verbatim* como se fosse um login. O GitHub responde **201 sem associar ninguém**: sucesso aparente, revisor ausente, aviso nenhum. Agora uma camada dedicada resolve a identidade **duas vezes** (antes da TUI e no momento da associação) e fecha também a segunda falha silenciosa da API — o login aceite mas não associado passou a ser detetado lendo de volta `requested_reviewers`.
- **A camada SCM ganhou as primitivas para revisar uma revisão que não está no disco:** O `get_pull_request(repo, pr_id)` tornou-se um método **concreto** da ABC (padrão do `create_release`) e foi implementado nas quatro forges — o `list_open_pull_requests` pagina uma única página, pelo que filtrá-la por número perde PRs antigos e não distingue fechado de inexistente. O atributo de classe `supports_reviewable_diff` (`False` no Azure DevOps, cuja API devolve uma lista de ficheiros e não um diff) barra a revisão **antes de qualquer chamada de rede**.
- **Dois defeitos latentes do GitLab corrigidos:** O `changes[].diff` é um hunk solto, pelo que o caminho do ficheiro era descartado — a IA revisaria hunks órfãos e nem o chunker nem o filtro de exclusão funcionariam; os cabeçalhos `diff --git / --- / +++` passaram a ser sintetizados a partir de `old_path`/`new_path`. E o `overflow: true` era ignorado: um diff truncado era revisto a meio e publicado como se fosse inteiro — agora lança.
- **O `gitpr fix` passou a corrigir a revisão que foi revista:** O diff revisto é gravado no registo de cache (`reviewed_diff`) e o `fix` prefere-o, caindo na re-derivação apenas para registos antigos. É a única fonte correta quando o review veio de um PR remoto ou de um diff de branch inteira.
- **O MCP cresceu de 12 para 14 ferramentas e de 17 para 18 recursos:** `list_fix_candidates` (13.ª, apenas de leitura) + `skill://fix`, e `review_remote_pr` (14.ª, apenas de leitura, sem o argumento `post_comment`, sem escrever `.txt`).
- **i18n expandida para 1048 chaves:** +93 desde o relatório anterior (955 → 1022 com o `fix` → 1028 com os revisores → 1048 com o `review-pr`); o `__lang_version__` subiu de v0.0.25 para **v0.0.28** (cadeia v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28) e os 6 dicionários mantêm **paridade total de key sets**.
- **Documentação:** 2 famílias novas — `fix-command` (5 idiomas) e `review-pr` (EN + PT-BR) — e 9 tópicos atualizados, incluindo `code-review-ia` (modo remoto como §1.4), `suggested-reviewers` (resolução de login) e a §2.2 de `fix-command` reescrita nas 5 versões, porque o diff deixou de ser re-derivado.
- **Higiene de repositório:** uma árvore pip 25.2 auto-vendorizada (`pypa/`, `pip/cache/http-v2/`) tinha sido commitada por engano e foi removida; o `.gitignore` ganhou `pypa/` e `pip/`.
- **Salto de Versão para 1.2.0:** o bump está **na árvore de trabalho e ainda não foi commitado nem tagueado** (o HEAD continua em 1.1.0; a última tag é a `v1.1.0`), e o `CHANGELOG.md` ainda para em `[1.1.0] - 2026-09-13`.

- **Versão atual:** 1.2.0 (bump na árvore de trabalho — HEAD em 1.1.0)
- **Versão dos dicionários de idioma:** v0.0.28
- **Versão dos scripts de hook:** v0.0.3
- **Publicação:** PyPI (`pip install gitpr-cli`) — canal binário removido na janela anterior
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositório:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licença:** LGPL-2.1
- **Idiomas suportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 dicionários)

---

## **🏗️ Arquitetura Base e Bibliotecas**

* **Linguagem:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags e formatação de terminal).
* **UI/Terminal:** Textual — TUI (Text User Interface) para chat interativo, edição de issues, help screen, dashboard de métricas, PR Publisher, erros do linter (`LinterApp`), configuração (`ConfigApp`) e o novo modal `NoticeScreen` de avisos do PR Publisher 🆕.
* **Criptografia:** `cryptography.fernet` para proteção local de chaves de API, tokens GitHub e tokens SCM das forges — os segredos editados na TUI de configuração também são encriptados antes de serem escritos.
* **Configuração:** `python-dotenv`, `pyyaml` (para o linter estático) + o seu próprio schema declarativo (`src/config_schema.py`).
* **Provedores de IA:** Integração via SDK oficial do Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), e OpenAI SDK (`Ollama` local).
* **APIs das Forges:** `requests` (REST) — camada de abstração multi-forge em `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps); módulo legacy `src/github_api.py` mantido como shim obsoleto.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — **14 ferramentas anotadas** 🆕, **18 recursos** 🆕, 7 prompts; handlers offloaded para threads via `anyio`.
* **Testes:** Pytest + `unittest.mock` (**64 ficheiros de teste — 41 na raiz, 9 em `tests/scm/`, 10 em `tests/fix/` e 4 em `tests/review/`** 🆕 —, 1437 cenários recolhidos) + testes e2e do servidor MCP via subprocess real (JSON-RPC stdio) + fixtures de repositório git real em `tests/fix/git_fixture.py` 🆕.
* **Empacotamento:** setuptools/build (PyPI).
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
* **Exclusões Inteligentes com Duas Camadas:** Filtro de pathspec inteligente com camada global (`~/.gitpr/conf/`) + camada local do projeto (`./.gitpr/conf/`). Junção em runtime (união, deduplicada). Auto-seed do ficheiro local na primeira execução. `_load_smart_excludes()` aceita `force=` para novo descarregamento a pedido a partir da TUI de configuração. 🆕 O template `templates/gitpr.smart-excludes.json` ganhou `.gitpr/fix_history.json` — um patch aplicado suja um ficheiro **rastreado**, pelo que sem isto ele apareceria nos diffs de `gitpr -c` e nas descrições de PR.
* **Métricas com Registo de Tempo:** Injeção de `log_command_metric()` em todos os fluxos com repasse da duração em milissegundos (`duration_ms`) e imports lazy.
* **Resolução Centralizada do Output:** Função `resolve_output_path()` que centraliza a lógica dos diretórios de output — predefinição em `.gitpr/reports/{type}/`.
* **Assistente SCM (`run_scm_init_wizard()`)**: `gitpr --init` — deteta a forge a partir do remote de origem, solicita extras por forge (org/projeto do Azure, username do Bitbucket), valida o token com `test_connection` (3 tentativas, novo pedido em 401) e persiste `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` (Fernet) **apenas em caso de sucesso**.
* **Template de Skill de Release (`ensure_release_skill_template()`)**: Transfere `templates/gitpr.release.*.md` na primeira utilização de `gitpr release` (camada CLI, ciente do idioma, nunca sobrescreve; ignorado em `--format json`).
* **Registo de Skills Partilhado:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` vivem em `src/config.py` (a TUI não pode importar o `core` no topo — arrasta os SDKs de IA); `get_skill_context()` usa `skill_file_for()`. 🆕 O `files_to_download` precisou de uma entrada própria para `gitpr.fix.md` — mexer apenas no `SKILL_FILES_BY_TYPE` não bastava para o `--skill` descarregar o template.
* **Motor de Review com Âmbito de Cache (`generate_pr_content`) 🆕:** Dois parâmetros **aditivos** — `cache_scope` (anexado **apenas à chave de cache**, nunca ao prompt) e `store_diff` (grava o diff revisto no registo). As predefinições `""`/`False` mantêm o caminho local **byte-idêntico**: zero invalidação de cache para os reviews locais que já existem. É o que permite que um review remoto tenha âmbito `::diff-source::pr-<n>` sem que um review local do mesmo diff o responda por engano.
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
* **Routing de Comandos:** Gere todas as flags e os **4 subcomandos** — `release`, `config`, `fix` 🆕 e `review-pr` 🆕.
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
* **Subcomando `fix` 🆕 — 8 opções:** `--list` (lista os candidatos do último review — o que o comando faz sem argumento), `--apply` (escreve na árvore; sem ele é um dry-run), `--all-safe` (seleciona todos os `safe`; escrever continua a exigir `--apply`), `--create-branch <name>`, `--no-branch`, `--yes` (salta a confirmação, **nunca** dispensa o `--force`) e `--force` (aplica um patch não seguro após uma frase escrita) e `--rollback <patch-id>`. Argumento opcional `[<finding-id>]`. Molde exato do `release`: imports lazy no corpo e `epilog` para `get_doc_url("fix-command.md")`. Nenhuma flag existente mudou de sentido — o `--force` do `release` ("regenerar uma secção existente") não colide porque os namespaces de subcomando são separados.
* **Subcomando `review-pr` 🆕 — 2 opções:** `review-pr <number>` com `--provider <name>` e `--post-comment`; apenas de leitura por predefinição, **nunca** chama `check_unstaged_files`, grava `{branch}_{datetime}_PR_REVIEW.txt` com o nome da branch de origem do PR. Rejeita o PR **antes de qualquer chamada de IA** por capacidade, existência, estado, diff vazio/não-revisável e esgotamento dos smart-excludes. Reutiliza `_resolve_scm_context`.
* **Variáveis de Ambiente (44 chaves no `DEFAULT_CONFIG`, +5 nesta janela 🆕):** as cinco `GITPR_FIX_*` — `GITPR_FIX_SAFE_MAX_LINES_CHANGED`, `GITPR_FIX_SAFE_EXCLUDED_PATHS`, `GITPR_FIX_REQUIRE_CONFIRMATION`, `GITPR_FIX_CREATE_BRANCH_ON_ALL_SAFE` e `GITPR_FIX_BRANCH_NAME_TEMPLATE`. `get_fix_settings()` devolve o bloco achatado e **recorre à predefinição embutida** quando o valor é esvaziado por acidente — limpar o campo não pode desligar a proteção em silêncio.
* **Ajuda Contextual:** `-h --flag` apresenta documentação específica da funcionalidade com um link direto (ciente do idioma) para o GitHub. Os subcomandos têm o seu próprio `epilog=` (parágrafo `\b` do Click para a URL não ser re-quebrada em nenhum idioma).
* **--lang:** Força o idioma da interface na execução atual sem persistir a alteração.
* **--provider:** Força o provedor de IA (`gemini`, `deepseek`, `ollama`) na execução atual.
* **--mcp:** Inicia o servidor MCP no transporte stdio para integração com editores — **14 ferramentas anotadas + 18 recursos + 7 prompts** 🆕.
* **--install:** Assistente guiado de 4 etapas que transfere templates de skill, instala Git Hooks, configura MCP nos editores e valida chaves de API.
* **--metrics:** Sistema de telemetria local com âmbito por repositório: `--export`, `--purge`, `--dashboard`.
* **--status:** Lista ficheiros não commitados categorizados (new/modified/deleted) — rápido, sem IA, sem rede.
* **Camada de Escrita do `.env`:** `read_env_file_values()` lê **apenas o ficheiro** via `dotenv_values` (imune a `os.environ`), `save_config_values()` escreve com `set_key`, `remove_config_value()` com `unset_key`. `validate_ai_key()` testa os SDKs Gemini/DeepSeek com timeouts curtos e distingue uma credencial recusada (`401`/`403`) de uma rede inacessível.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` e `src/ui/pr_publish_help.py`)**

* **Interface Interativa Completa:** TUI construída com Textual para rever, editar e publicar Pull Requests diretamente no terminal.
* **7 Ecrãs Modais:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` e **`NoticeScreen`** 🆕.
* **Avisos Bloqueantes (`NoticeScreen`) 🆕:** Um modal que exige reconhecimento (Esc ou Close) antes de o fluxo de merge poder continuar. É intencional: sem ele, o prompt de merge assumiria o ecrã e os avisos de revisor descartado passariam despercebidos — mas pausa os fluxos automatizados quando há avisos.
* **`_attach_reviewers` com resolução 🆕:** Resolve o que o utilizador escreveu **antes** de enviar, reporta o que foi descartado e, num `422` de lote com vários revisores, **repete um a um** — o GitHub rejeita o lote inteiro quando um único login é inelegível (o autor do PR, um não-colaborador), o que antes derrubava também os revisores válidos.
* **Revisores Sugeridos:** O fluxo de publicação consulta a forge para obter revisores sugeridos e apresenta-os na TUI; seleção controlada por `--no-suggest-reviewers`, `GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N` e `GITPR_REVIEWER_SUGGESTION_EXCLUDED`. 🆕 `_reviewer_suggestion_view()` monta as `resolutions` via `resolve_candidates`, pré-preenche os `handles` e marca quem não tem conta para as linhas de dica o sinalizarem; as `resolutions` viajam na view para a associação não resolver as mesmas pessoas duas vezes.
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
* **`skip_external` — o bridge não pode lintar a árvore errada 🆕:** O `parse_diff_and_lint(..., skip_external=False)` ganhou o parâmetro que desliga os **dois** call-sites do bridge externo. O fluxo remoto passa `True`, porque o bridge executa binários **contra ficheiros no disco** — a árvore local do utilizador, não o PR — e esses alertas seriam publicados como comentário público no PR de outra pessoa.
* **Relatório Consolidado:** `generate_linter_report_content()` consolida erros regex + externos em `.gitpr/reports/linter/` — gerado apenas quando há violações.
* `load_linter_presets()` aceita `force=` para descarregar novamente os presets a partir da TUI.

### **7. Segurança e Autenticação (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografia:** Gera uma chave mestra `secret.key` na pasta `~/.gitpr/`.
* **Proteção de Tokens:** `encrypt_data` e `decrypt_data` para proteger chaves de API de IA, PATs do GitHub e tokens SCM das forges (`GITPR_SCM_TOKEN_ENCRYPTED`).
* **Validação Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — valida o token contra a forge configurada com loop de reautenticação em 401 preservando o rascunho; o token legacy do GitHub (`GITHUB_TOKEN_ENCRYPTED`) permanece funcional até `--init` correr.
* **Segredos na TUI de Configuração:** Os campos `KIND_SECRET` são editados num campo mascarado, **nunca** apresentam o valor em claro e são encriptados com Fernet antes de serem escritos — nenhum caminho volta a ler o segredo para o ecrã. O `GITPR_SCM_TOKEN` é `read_only` e a sua descrição aponta para `gitpr --init` como o único caminho que o deve escrever.

### **8. Auto-Atualizador (`src/updater.py`)**

* **PyPI como Fonte Única:** `get_latest_remote_version()` consulta sempre `https://pypi.org/pypi/gitpr-cli/json`, devolve uma **string** de versão e escreve a cache diária **sem** o campo `download_url`.
* **Bloqueio Obrigatório (`enforce_update_required()`):** Devolve `True` (depois de imprimir as duas versões e o comando pip) quando a versão publicada é mais recente; devolve `False` quando está atualizado, quando a versão remota é **desconhecida (offline — o utilizador não teria forma de atualizar)** ou quando a verificação está desligada. Devolver um `bool` em vez de chamar `sys.exit` internamente mantém a função testável.
* **`check_and_update()`:** Apenas consulta e **reporta**, nunca instala.
* **Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` (qualquer valor não vazio) — não publicitada ao utilizador como funcionalidade; existe para a suíte de testes e para automatização offline.
* **Cache Diário:** Evita verificações repetidas no mesmo dia.
* **Versionamento Centralizado:** `__version__` (**1.2.0** — bump na árvore de trabalho), `__lang_version__` (**v0.0.28** — cadeia v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28 nesta janela 🆕), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.

### **9. Interface de Chat Interativa (`src/ui/chat_app.py`)**

* **TUI Completa:** Construída com Textual — histórico de mensagens, input multi-linha, barra de status com bindings visíveis.
* **Memória por Branch (`src/chat_memory.py`):** Histórico de conversa persistido por branch, permitindo continuidade entre sessões.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear` — atalhos para pair programming.
* **Auto-Patching (F5), Atualização de Diff (F2), Exportação de Sessão (F6).**
* **Extrator Partilhado 🆕:** O bloco da regex estava **duplicado** em F5 e `ctrl+s`; ambos passaram a chamar `patch_extractor.extract_code_blocks()`. O ficheiro perdeu 31 linhas e ganhou 3, com **comportamento visível idêntico** (mesmas teclas, mesmo `GITPR_PATCH_SUGGESTION_<key>.txt`, mesmo conteúdo) — por isso não há nota de changelog sobre o chat. É também a razão pela qual o `__init__.py` de `src/fix/` é **só docstring**: o chat importa-o em cada sessão, e um `__init__` que reexportasse arrastaria consigo as camadas de IA e de git (ver ADR-004, alternativa rejeitada).

### **10. Internacionalização — i18n (`src/i18n.py`)**

* **Sistema Inspirado no Laravel:** Função `__()` com suporte a placeholders nomeados (`{count}`, `{file}`, etc.).
* **Deteção Automática:** Deteta o idioma do SO na primeira execução e guarda-o em `GITPR_LANG`.
* **5 Idiomas, 6 Dicionários:** en_us (predefinição/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Ficheiros Versionados:** `__lang_version__` (**v0.0.28**) controla a atualização dos pacotes de idioma (`langs/*.json`) — cadeia de bumps v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28 nesta janela.
* **Cobertura:** **1048 chaves** de tradução em cada um dos 6 ficheiros — **paridade total de key sets** (+93 desde o relatório anterior).
* **Snapshot do Ambiente (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado no `i18n.py` **imediatamente antes** do `load_dotenv()` ao nível do módulo — corrige o badge "⚠ no ambiente" que confirmava tautologicamente que a chave está no ficheiro.
* **Chave Reescrita 🆕:** `GitHub usernames, comma separated` → `GitHub login, name or email, comma separated` — o campo deixou de prometer o que não aceitava.
* **Cache com Indexação por Idioma:** As respostas de IA em cache incluem o idioma corrente no chaveamento MD5.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Thread em background durante chamadas de IA exibindo caracteres braille com palavras de "pensamento".
* **263 entradas por idioma:** Sincronizadas entre os 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceitam `force=`.

### **12. Provedores de IA (`src/ai_providers.py`)**

* **3 Provedores Suportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Modo JSON & Parâmetros Determinísticos:** Outputs estruturados com `temperature=0.0` e `top_p=0.1`; fallback automático entre os provedores configurados.

### **13. Cache Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Chaveamento por hash MD5 do diff e do prompt, com indexação por idioma.
* **Seleção do Último Review (`resolve_last_review()`) 🆕:** Juntamente com `REVIEW_ACTION_TYPES`, escolhe o registo `review`/`fullreview` **mais recente** para um par repo+branch, **excluindo reviews com âmbito de ficheiro** (`-i`), que não descrevem a branch. É a porta de entrada do `gitpr fix`.
* **Diff Revisto (`reviewed_diff`) 🆕:** Campo no topo do registo que guarda o diff efetivamente revisto. Preferido por `fix/apply_fix.reviewed_diff()`, com fallback para re-derivação em registos antigos — a única fonte correta quando o review veio de um PR remoto ou de um diff de branch inteira.
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

* **14 Ferramentas MCP Anotadas 🆕:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, **`list_fix_candidates`** (13.ª — candidatos de correção do último review, com patch, classificação e id; apenas de leitura) e **`review_remote_pr`** (14.ª — review de um PR aberto na forge, obtido por número; apenas de leitura, **nunca** comenta e **nunca** escreve ficheiro, resolve a forge sozinho).
* **18 Recursos + 7 Prompts Templatizados 🆕:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` (**`skill://fix` é novo**) + `linter://config` + `prompt://list` + 7 prompts.
* **Invocação CLI Direta:** O comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca qualquer tool MCP diretamente sem iniciar o servidor stdio JSON-RPC. O `gitpr-mcp --list` imprime o registo completo em JSON.
* **Isolamento do Stdout Real:** `_write_real_stdout()` escreve diretamente no `sys.__stdout__` original, garantindo JSON puro no stdout — a razão pela qual o registo de utilização **nunca** imprime.
* **Offload do Event Loop:** Decorator `_offload` (`anyio.to_thread.run_sync`) aplicado às 14 tools — handlers síncronos não congelam o servidor stdio.
* **Registo de Utilização:** O `main()` do servidor chama `log_usage()` — o console script `gitpr-mcp` nunca carrega o `main.py`, pelo que este é o único ponto que lá chega.
* **Testes E2E:** `tests/test_mcp_server_e2e.py` inicia o servidor real como subprocess e fala JSON-RPC stdio.

### **17. TUI de Dashboard de Métricas (`src/ui/metrics_app.py`)**

* **Âmbito por Repositório (Repo-Scope):** Rótulo `📁 Repository: owner/repo` e filtragem estrita por projeto.
* **Varredura Assíncrona com Overlay:** Worker thread em background com widget `ProgressBar`.
* **Consolidação de Dados:** `load_cache_token_summary()` soma tokens de cache ao totalizador.
* **Exportação Local:** Guardar CSV/JSON em `./.gitpr/metrics/export/` — 🆕 os artefactos `gitpr_metrics_2026-09-17.csv`/`.json` entraram **rastreados** pelo PR #171 e são candidatos a `.gitignore`.

### **18. Sistema de Métricas e Telemetria (`src/metrics.py`)**

* **Âmbito por Repositório:** Todos os eventos indexados por `repo_name`.
* **Eventos de Hook, Linter e Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Exportação e Limpeza:** `--metrics --export` (CSV/JSON) e `--metrics --purge` com confirmação interativa.

### **19. Sincronização de Idiomas dos Git Hooks**

* **Versionamento Independente:** `__scripts_version__` (v0.0.3) controla a versão dos scripts de hook; deteção e atualização automáticas.
* **Mapeamento de Sufixos (`HOOK_SCRIPT_SUFFIXES`):** Os códigos de interface (`es_es`, `fr_fr`) passam a ser traduzidos para os sufixos realmente publicados (`.es`, `.fr`).
* **Escolha vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** O `SCRIPTS_LANG` é a escolha do utilizador; o `SCRIPTS_INSTALLED_LANG` é o que está em disco. Separados, a sincronização automática consegue **detetar uma mudança de idioma**.
* **`effective_hook_lang()`:** Resolve o idioma efetivo dos hooks; o `--lang` deixou de ser descartado nesse caminho (alteração de comportamento documentada).
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
* **`get_pull_request(repo, pr_id)` — método concreto da ABC 🆕:** Padrão do `create_release`: a predefinição lança `ScmNotSupportedError` e cada uma das quatro forges implementa-o. Existe porque o `list_open_pull_requests` pagina **uma única página** — filtrá-la por número perde PRs antigos em silêncio e **não distingue fechado de inexistente**, e o `review-pr` precisa de rejeitar o PR pelo motivo certo antes de gastar IA.
* **`supports_reviewable_diff` — gate de capacidade 🆕:** Atributo de classe, `False` no Azure DevOps, verificado **antes de qualquer chamada de rede**. A API REST do Azure devolve uma **lista de ficheiros**, não um diff unificado — revisar seria inventar conteúdo.
* **Cabeçalhos do GitLab Sintetizados 🆕:** O `changes[].diff` é um **hunk solto**; `old_path`/`new_path` eram ignorados, pelo que o caminho do ficheiro se perdia — a IA revisaria hunks órfãos e nem o chunker nem o filtro de exclusão teriam em que se apoiar. Agora o provedor monta os cabeçalhos `diff --git a/… / --- / +++`, honrando `/dev/null` para ficheiros adicionados/removidos.
* **`overflow` do GitLab Lança 🆕:** Um MR cujo diff ultrapassa o limite da API era revisto **a meio** e publicado como se fosse inteiro; agora lança `ScmProviderError` com uma mensagem clara.
* **`request_pull_request_reviewers` devolve `list[str]` 🆕 (quebra de contrato):** Devolvia `None`; agora devolve os logins **efetivamente associados**, lidos do corpo do `201`. É a única forma de detetar um login aceite e silenciosamente ignorado. O `None` continua a ser tratado como "não é possível verificar", nunca como falha — os callers antigos não se quebram, apenas perdem o read-back. As subclasses de terceiros **têm** de ser atualizadas.
* **Dois Helpers de Apenas Leitura do GitHub 🆕:** `get_commit_author_login` (mapeia um SHA à conta ligada ao e-mail do autor — o caminho fiável para endereços corporativos que a API de pesquisa de utilizadores não vê) e `get_user_login` (valida/canonicaliza um handle escrito, rejeita o que não pode ser login **sem gastar um pedido** e distingue 404 de erro transitório).
* **Fail-Fast por Forge:** O Azure DevOps exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; o Bitbucket exige `GITPR_SCM_USERNAME` (App Password = Basic auth user+token); `create_issue` no Azure lança `ScmNotSupportedError`.
* **Publicação de Release:** `provider.create_release()` usado por `gitpr release --publish` (o GitHub cria a tag na branch predefinida; o GitLab exige que a tag exista).
* **Artefactos:** Glossário + ADR-001/ADR-005 em `docs/plans/`; família `docs/scm-multiforge.*.md` em 5 idiomas; testes: 9 ficheiros, **282 cenários** (+17 nesta janela).

### **22. Subcomando `gitpr release` — Changelog / Release Notes**

* **Fluxo:** `git log` entre `--since` (predefinição: última tag alcançável, ou o primeiro commit) e `HEAD` → classificação por Conventional Commits → sugestão de bump semântico (`--version <x.y.z>` sobrepõe-se) → montagem do changelog → resumo executivo por IA opcional → *anteposição* ao `CHANGELOG.md`. A geração local é a predefinição — nada é publicado ou alterado sem pedido.
* **Classificador (`src/commit_classifier.py`):** Classifica os commits por tipo de Conventional Commits (feat/fix/refactor/docs/chore/etc.) com um parser tolerante.
* **Builder com Secções Traduzíveis (`src/changelog_builder.py`):** Secções "✨ Features", "🐛 Bug Fixes", "⚠️ Breaking Changes", "Summary", "Contributors" etc. renderizadas via `__()` em runtime (seguem `--lang`).
* **Bump Semântico (`src/version_bump.py`):** Sugere a próxima versão a partir dos tipos classificados (major para breaking, minor para feat, patch para fix) e valida alvos `x.y.z`.
* **Publicação:** `--publish` cria o release na forge configurada (com confirmação explícita); `--draft` cria-o como rascunho (GitHub; o GitLab não tem conceito de draft); `--format markdown|json` para output estruturado; `--force` para reescrever. **6 opções no subcomando.**
* **Template de Skill:** Na primeira utilização transfere `templates/gitpr.release.*.md` (5 idiomas) via `ensure_release_skill_template()` — nunca sobrescreve.
* **Pendência de Release 🆕:** O `CHANGELOG.md` **não** foi tocado nesta janela — a última entrada continua a ser `[1.1.0] - 2026-09-13`, enquanto o `__version__` já diz 1.2.0. Correr o `gitpr release` é o passo que falta.
* **Artefactos:** Família `docs/release-notes.*.md` (5 idiomas), spec em `docs/plans/`, ADR-002 e ADR-003, glossário de release notes.

### **23. Subcomando `gitpr config` — TUI de Configuração**

* **Ecrã Master-Detail (`src/ui/config_app.py`):** Categorias à esquerda, campos da categoria à direita, editados inline. Cabeçalho com procura (`/`) e um contador de pendências (`● N não guardadas`); rodapé com `F1 Ajuda · F2 Guardar · ^R Restaurar · / Procurar · Esc`. O `Geral` é sempre a primeira entrada do menu.
* **Schema Declarativo (`src/config_schema.py`) — a única fonte de verdade:** 🆕 **13 categorias** (Geral, Fornecedores de IA, Pull Request, Revisão de Código, Issue, Blame, Linter, Release, SCM / Forge, Filtros de Diff, Skills, **Correção** e Avançado) e **61 `ConfigField`**, dos quais **8 avançados** (+1 categoria e +5 campos nesta janela). Cada campo declara a sua categoria, tipo de widget (`bool`/`int`/`str`/`enum`/`template`/`path`/`secret`/`version`/`words`), `show_if`, validadores, marcadores de versão e ações de descarregamento. Os rótulos são literais `__()` para o scanner de i18n.
* **Categoria `fix` 🆕:** Os cinco `GITPR_FIX_*` ganharam uma superfície editável com descrições que explicam a consequência de cada um (orçamento de linhas, globs sensíveis, exigir confirmação, criar branch no lote all-safe e o template do nome da branch).
* **Filtragem por Contexto (`show_if`):** `GEMINI_*` / `DEEPSEEK_*` / `OLLAMA_*` aparecem em função do `DEFAULT_AI_PROVIDER` selecionado; `GITHUB_TOKEN_ENCRYPTED` aparece com provedor vazio ou `github`; `GITPR_SCM_USERNAME` sob `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` sob `[Azure DevOps]`. Alterar o `Select` re-filtra o painel **imediatamente, sem F2**.
* **Procura Global (`/`):** Encontra correspondências na chave ou no rótulo em todas as categorias e **ignora o filtro de visibilidade** — procurar `deepseek` com o Gemini selecionado encontra os campos, para permitir pré-preenchê-los.
* **Validação em Duas Camadas:** **Offline** (tipo, enum, template com placeholder conhecido e `{datetime}` obrigatório) bloqueia o `F2` com um erro inline; **online** (apenas para credenciais alteradas na sessão) corre num worker com timeout de 10s e só bloqueia em `401`/`403` — uma falha de rede ainda permite guardar.
* **Restaurar (`Ctrl+R`):** Remove a linha do `.env` em vez de reescrever a predefinição; o **`Esc`** com alterações pendentes pede confirmação; a categoria **Desconhecidas** preserva em modo de leitura as chaves fora do schema.
* **Secção Skills — a única com âmbito de projeto:** Painel master-detail inline que edita os `.gitpr/skill/*.md` do projeto, resolvidos a partir do diretório de chamada, escrevendo atomicamente e preservando CRLF/LF. O `F2` escreve o `.env` e os ficheiros de skill **na mesma passagem**.
* **Descarregamentos Forçados:** Botões que forçam o novo descarregamento dos smart-excludes, das traduções, dos presets de linter e das thinking words, via um parâmetro `force=` encadeado pelos loaders.
* **Módulo de Ligações Leve (`src/doc_links.py`):** O `doc_url()` saiu do `core.py` para que a UI possa obter a ligação à documentação sem importar o `core`/SDKs de IA.
* **Testes:** 5 ficheiros — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9). 🆕 O `test_config_schema.py` passou a cobrir os campos novos (a asserção de que `advanced` só existe em Avançado continua a valer para os 61).
* **Artefactos:** `docs/config-tui.*.md` em 5 idiomas, plano `docs/plans/20260912_config_tui.md`, glossário `glossary-config-tui.md` (8 termos) e o levantamento (grill survey).
* **Dívida conhecida:** `gitpr -h config` abre a TUI e ignora o `-h` — o gate `if ctx.invoked_subcommand is not None: return` corre antes do bloco `help_flag`; corrigi-lo mudaria o comportamento do `-h` para **todos** os subcomandos (documentado em `docs/config-tui.md`).

### **24. Registo Geral de Utilização (`src/usage_log.py`)**

* **Uma Linha por Comando:** Escreve em `~/.gitpr/logs/<uuid5>.log`, **um ficheiro por dia**, com o comando, os argumentos, o repositório, o utilizador e o timestamp. Responde a "o que é que eu executei realmente, e quando?".
* **Nome Derivado da Data:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` em vez de aleatório — um nome aleatório exigiria um contador ou um ficheiro de estado para saber qual é o ficheiro de hoje, e dois processos concorrentes poderiam discordar.
* **Escrita Síncrona (decisão explícita):** Ao contrário do `log_local_metric`, que usa uma thread daemon e por isso perde a escrita se o processo terminar cedo — inaceitável para um registo que promete registar *todos* os comandos.
* **Nunca Imprime:** O servidor MCP reserva o stdout para JSON-RPC; um `print` acidental corromperia o protocolo. O módulo também nunca lança exceções.
* **Um Único Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` em vez dos três idiomáticos — ~40 ms em vez de ~150 ms no Windows, em *todas* as execuções.
* **O Seu Próprio `_repo_label()`:** Sem reutilizar o `get_repo_name()` do `core.py` (regex hardcoded para `github.com`, devolveria `unknown/repo` no GitLab/Bitbucket/Azure) e sem o `parse_repo_ref`, que é um método de provedor e exigiria construir um provedor (token, `requests`) em cada comando.
* **Controlo:** `GITPR_SHOW_LOGS` (predefinição `"true"`); desligado em `tests/conftest.py`.
* **Dogfooding 🆕:** O registo de execução de um `pr_desc` anterior foi a evidência que isolou o bug do revisor — a linha `Reviewers requested on PR #1068: ['Eduarda Leal']` provou que o **nome** ia como login e que o `_request()` retornava sem lançar. Sem esse registo, o sintoma ("não aparece no PR") não teria como ser distinguido de uma falha de rede.
* **Artefactos:** `docs/usage-log.*.md` em 5 idiomas; `tests/test_usage_log.py` (27 cenários).

### **25. Subcomando `gitpr fix` — Apontamentos de Review como Patches Revisáveis (`src/fix/`) 🆕**

* **O que é:** Uma capacidade **nova**, não uma generalização do chat. A investigação obrigatória (passo 0 do plano) derrubou três premissas da spec: o chat **nunca** aplicou patch nenhum (F5 e `ctrl+s` gravam um `.txt` no CWD, sem `subprocess` e sem diff válido), um apontamento com `id`/`severity`/`file_path` **não existia** em lado nenhum, e `config.schema.yml` nunca existiu.
* **O Pipeline Real:** último review da cache (`resolve_last_review`) → **uma** chamada de IA (`call_ai_model`, nunca `generate_pr_content`, cujo `else` é o ramo de PR) → extração e validação de diff unificado → `git apply --check` → classificação determinística → dry-run ou escrita → histórico.
* **Pacote de 8 ficheiros (1147 linhas):**
  * `patch_provenance.py` — contrato de dados puro: `PatchSafety`, `FindingRef`, `PatchCandidate` (com `patch_id` derivado), `PatchProvenance`, `ApplyFixResult`.
  * `patch_extractor.py` — blocos delimitados + validação de diff unificado; **partilhado com o chat**.
  * `patch_safety_classifier.py` — `safe`/`review_required`/`experimental`, lógica **pura, sem I/O e sem IA**; devolve um **código de motivo**, nunca uma frase pronta.
  * `patch_applier.py` — o primeiro invólucro de `git apply` do projeto (`--check`, `apply`, `--reverse`, `checkout -b`, `status`).
  * `fix_history.py` — `.gitpr/fix_history.json` com escrita atómica (`.tmp` + `os.replace`).
  * `apply_fix.py` — o caso de uso (470 linhas).
  * `rollback_fix.py` — `git apply --reverse` sobre o diff guardado, com três recusas distintas.
  * `__init__.py` — **só docstring**, e que ordena os módulos de propósito (ver ADR-004).
* **Classificação Determinística:** Recusa um patch que atravesse mais de um ficheiro ou mais de um hunk, que toque num caminho sensível configurado, que ultrapasse o orçamento de linhas adicionadas+removidas, que **apague uma linha com aspeto de chamada**, declarado de baixa confiança pela IA, ou que falhe no `git apply --check` contra a árvore atual. O `--force` **nunca** contorna a verificação de aplicabilidade — apenas a classificação.
* **Escrita Opt-In:** O dry-run é a predefinição; qualquer mutação exige `--apply` (ou uma frase de confirmação escrita com `--force`).
* **Suporte:** O `src/diff_parser.py` ganhou `summarize_patch()` / `PatchSummary` (puros) — contagem de ficheiros, de hunks, delta de linhas e deteção de chamada removida — usados pelo classificador.
* **MCP:** 13.ª tool `list_fix_candidates` (apenas de leitura) e o recurso `skill://fix`.
* **Skill:** `templates/gitpr.fix.md` + `.pt_br.md` (persona: Senior Software Engineer), descarregados por `gitpr --skill`.
* **Configuração e Artefactos:** 5 variáveis `GITPR_FIX_*` + a categoria `fix` na TUI; `docs/fix-command.*.md` (5 idiomas, 8 secções); ADR-004 e `glossary-gitpr-fix.md`; spec/plano/levantamento em `docs/plans/` e `docs/survey/`.
* **Testes:** 10 ficheiros em `tests/fix/` (**204 cenários**) com fixtures de repositório git real (`tests/fix/git_fixture.py`) — matriz do classificador, applier, histórico, routing da CLI, rollback, settings e o extrator partilhado com o chat.

### **26. Subcomando `gitpr review-pr` — Review de PR Remoto (`src/review/`) 🆕**

* **O que é:** Orquestração, **não** um segundo motor de review. O motor existente (`generate_pr_content`), o linter (`parse_diff_and_lint`) e o renderizador são as peças do fluxo local, alimentadas com um diff que veio de outro sítio — um `.txt` de review remoto e um de review local do mesmo diff diferem **apenas no nome do ficheiro**.
* **Pacote de 5 ficheiros (560 linhas):**
  * `diff_source.py` — `DiffOrigin` + `DiffSource`: proveniência pura (`cache_scope`, `is_remote`), sem I/O.
  * `diff_normalizer.py` — normalização de newline, validação de diff (`is_reviewable_diff`) e o filtro de smart-excludes **em Python**, para um diff que o git nunca viu.
  * `render.py` — composição e escrita do artefacto, **extraída do `main.py`** e agora **partilhada** com os fluxos locais.
  * `remote_pr.py` — o caso de uso: gate → PR → diff → normalizar → excluir → motor → linter → comentário opcional.
  * `__init__.py` — marcador de pacote.
* **`split_patch_sections` (`src/diff_parser.py`) 🆕:** Devolve o caminho de cada ficheiro **ao lado** do próprio texto — base do filtro de exclusão remoto e da síntese de cabeçalhos do GitLab.
* **Apenas de Leitura por Predefinição:** O `--post-comment` é o **único** caminho que escreve na forge. O mesmo vale para a tool MCP `review_remote_pr`, que nem sequer recebe o argumento.
* **Interação com o `fix` 🆕:** Como o review remoto não corresponde a nenhuma árvore local, o diff revisto passou a ser guardado na cache (`reviewed_diff`) e o `fix` prefere-o — corrigindo um defeito que só apareceria depois desta funcionalidade.
* **Artefactos:** `docs/review-pr.md` + `.pt_br.md` (8 secções), `docs/code-review-ia.*.md` (modo remoto como §1.4 + a ressalva do linter externo, nas 5 versões), ADR-005 e `glossary-review-pr.md`; spec, plano e levantamento em `docs/plans/` e `docs/survey/`.
* **Testes:** 4 ficheiros em `tests/review/` (**97 cenários**) — `test_remote_pr.py` (40), `test_review_pr_cli.py` (25), `test_diff_normalizer.py` (20), `test_diff_source.py` (12) — mais 6 cenários novos em `tests/scm/test_gitlab_provider.py` e 11 em `tests/test_mcp_server.py`.

### **27. Resolução de Identidade do Revisor (`src/reviewer_resolution.py`) 🆕**

* **O Bug (duas falhas silenciosas encadeadas):** (1) **Prefill vazio** — `_reviewer_suggestion_view()` montava os `handles` só com `provider.email_to_handle()`, que apenas vê e-mails `users.noreply.github.com` ou com endereço **público**; com um e-mail corporativo (o caso real: `eduardaleal@grafjb.com.br`) nada resolve, o `Input` nasce vazio e a dica mostra apenas o **nome**. (2) **Associação não verificada** — `_attach_reviewers()` repassava os valores do campo **verbatim**; o GitHub responde **201 sem associar ninguém**, o `_request()` retorna sem lançar e a linha do registo é escrita: sucesso aparente, revisor ausente, aviso nenhum.
* **Módulo Novo (159 linhas):** Plano, sem I/O próprio, **que nunca lança exceções**. `resolve_candidates()` (antes da TUI) e `resolve_typed_reviewers()` (no momento da associação); `match_candidate()` faz correspondência **exata e normalizada** por login, nome ou e-mail.
* **Escada de Resolução:** handle já conhecido (sem qualquer pedido) → correspondência exata com uma pessoa sugerida → lookup por e-mail (`email_to_handle`) → validação do login na forge (`get_user_login`).
* **O que não resolve nunca é enviado:** Sai em `ResolutionOutcome.dropped` como `(valor, motivo_i18n)` e é descartado com um aviso visível, em vez de se transformar num `201` vazio.
* **Provedor Duck-Typed:** O acesso é feito por `getattr`, pelo que fakes e forges sem os métodos novos continuam a funcionar — a suíte existente não precisou de reescrita.
* **Leitura de Volta no GitHub:** O `request_pull_request_reviewers` lê `requested_reviewers` do corpo do `201` e devolve os logins **realmente** associados; um lote rejeitado com `422` (o GitHub rejeita o lote inteiro quando um único login é inelegível) é repetido **um a um**, em vez de derrubar também os revisores válidos.
* **Utilidades Adjacentes:** `_identity_key` → `identity_key` (público), novo `normalize_identity()`, e `ReviewerCandidate` passou a carregar `last_commit_hash` para a agregação guardar o commit do toque mais recente.
* **Diagnóstico:** O registo de utilização da própria ferramenta foi a evidência de origem — ver §24.
* **Artefactos:** `docs/suggested-reviewers.*.md` ressincronizado nos 5 idiomas, `ADR-002-reviewer-suggestion.md` e `glossary-reviewer-suggestion.md`, plano e levantamento (grill survey) de 4 rondas.
* **Testes:** `tests/test_reviewer_resolution.py` (18, **novo**) + ampliações em `test_github_provider.py` (68), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) e `test_main_suggest_reviewers.py` (8).

---

## **📊 Testes e Qualidade**

| Ficheiro de Teste | Cenários | Foco |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por intervalo de linhas num ficheiro |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidade, commits, duração |
| `tests/test_changelog_builder.py` | 15 | Builder de changelog: secções, cabeçalhos traduzíveis, contribuidores |
| `tests/test_chat_backend.py` | 19 | Memória de chat, persistência, comandos slash |
| `tests/test_commit_classifier.py` | 23 | Classificação Conventional Commits (tipos, parser tolerante) |
| `tests/test_config_app.py` | 78 | TUI de configuração: montagem, troca de categoria, dirty tracking, F2 bloqueado, Ctrl+R, procura, segredos |
| `tests/test_config_cli.py` | 9 | Registo do subcomando `config`, `-h`, import lazy, stdout limpo |
| `tests/test_config_schema.py` | 42 | Cobertura do `DEFAULT_CONFIG`, sem duplicados, categorias/kinds, `advanced` apenas em Avançado |
| `tests/test_config_store.py` | 22 | Round-trip num `.env` temporário, comentários e ordem preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuração de revisores sugeridos (chaves e predefinições) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, templates, `validate_ai_key()` com SDK mockado (401 vs. rede vs. ollama) |
| `tests/test_core.py` | 49 | Fluxos principais, git diff, geração de PR, timing, staging, coautoria, idioma dos hooks |
| `tests/test_diff_parser.py` | 26 | Parser de diff por linhas/hunks + `summarize_patch()` e `split_patch_sections()` 🆕 |
| `tests/test_external_linters.py` | 33 | Bridge Checkstyle: parser XML, subprocess, cruzamento de diff, relatório |
| `tests/test_i18n.py` | 20 | Paridade entre idiomas (1048×6), chaves ausentes/órfãs, identidade |
| `tests/test_install_wizard.py` | 3 | Assistente interativo de instalação |
| `tests/test_issue_engine.py` | 4 | Rascunho de issue estruturado |
| `tests/test_linter_metrics.py` | 4 | Métricas de linter: erros, warnings, duração |
| `tests/test_linter_presets.py` | 5 | Presets de linter: resolução e novo descarregamento forçado |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` na CLI e na ajuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompt MCP e fallback de idioma |
| `tests/test_mcp_server.py` | 104 🆕 | Ferramentas MCP (14), recursos (18), annotations, patching, CLI direto, offload |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real via subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Recolha, exportação local, âmbito de repo, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Timeouts de rede/IA — **2 asserções desatualizadas (600s)** |
| `tests/test_plugins.py` | 17 | Descoberta de plugins, merge de regras de linter, prompts MCP |
| `tests/test_pr_publish_app.py` | 45 | TUI do PR Publisher: ecrãs, fluxos, revisores sugeridos, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de erro do linter: abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save e payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opções, help com epilog documentado |
| `tests/test_release_engine.py` | 27 | Motor de release: intervalo de commits, CHANGELOG, publicação |
| `tests/test_reviewer_resolution.py` | 18 🆕 | Escada de resolução, rejeição de nome parcial, dedup, tolerância a falha |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugestão de revisores (ranking, exclusão, top-N, `last_commit_hash`) |
| `tests/test_skill_command.py` | 10 | Download e validação de templates de skill |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` com `quiet=True`, fallbacks, registo de skills |
| `tests/test_smart_excludes.py` | 15 | Filtro pathspec inteligente e novo descarregamento forçado |
| `tests/test_suggest_reviewers.py` | 17 | Revisores sugeridos no fluxo de PR (integração, dica `no_login`) |
| `tests/test_thinking_words.py` | 5 | Carregamento, parsing com o separador `;` e recarregamento forçado |
| `tests/test_updater.py` | 23 | Bloqueio do PyPI: parsing de versão, cache diária, obtenção, decisões do bloqueio, ligação à CLI |
| `tests/test_usage_log.py` | 27 | Registo de utilização: nome derivado da data, escrita síncrona, silêncio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semântico: major/minor/patch, alvos e validação |
| `tests/fix/test_apply_fix.py` | 53 🆕 | Caso de uso completo: review → IA → validar → classificar → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 🆕 | Extrator partilhado entre o chat e o `fix` (comportamento idêntico) |
| `tests/fix/test_fix_cli.py` | 31 🆕 | Routing do subcomando, opções, dry-run por predefinição, `--force` |
| `tests/fix/test_fix_history.py` | 21 🆕 | Registo `.gitpr/fix_history.json`: escrita atómica, leitura |
| `tests/fix/test_fix_settings.py` | 10 🆕 | As cinco `GITPR_FIX_*` e os fallbacks de valor inválido |
| `tests/fix/test_patch_applier.py` | 19 🆕 | Invólucro de `git apply`: check/apply/reverse, branch, status |
| `tests/fix/test_patch_extractor.py` | 13 🆕 | Blocos delimitados → diff unificado validado |
| `tests/fix/test_patch_safety_classifier.py` | 22 🆕 | Matriz safe/review_required/experimental e códigos de motivo |
| `tests/fix/test_resolve_last_review.py` | 13 🆕 | Seleção do último review, exclusão de reviews por ficheiro |
| `tests/fix/test_rollback_fix.py` | 14 🆕 | `--rollback`: reverse e as três recusas |
| `tests/review/test_diff_normalizer.py` | 20 🆕 | Newlines, validação de diff, smart-excludes em Python |
| `tests/review/test_diff_source.py` | 12 🆕 | `DiffOrigin`/`DiffSource`: proveniência, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 🆕 | Orquestração: gates, PR, diff, linter, comentário opcional |
| `tests/review/test_review_pr_cli.py` | 25 🆕 | CLI `review-pr`: opções, rejeições antes da IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 43 | Provedor Azure DevOps: org/projeto, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 39 | Provedor Bitbucket: Basic auth, workspace |
| `tests/scm/test_contract.py` | 38 | Contrato `ScmProvider`: assinaturas, dataclasses, erros |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim obsoleto `github_api` → delega no provedor |
| `tests/scm/test_github_provider.py` | 68 🆕 | Provedor GitHub: REST, headers, PRs, issues, releases, read-back de revisores |
| `tests/scm/test_gitlab_provider.py` | 47 🆕 | Provedor GitLab: API v4, namespace, **cabeçalhos sintetizados e `overflow`** |
| `tests/scm/test_init_wizard.py` | 10 | Assistente `--init`: deteção de forge, validação, persistência |
| `tests/scm/test_release_publish.py` | 8 | Publicação de release por forge (GitHub/GitLab) |
| `tests/sync_i18n.py` | — | Script de verificação de cobertura i18n (scaffold; nunca executado) |

**Total:** **1437 cenários recolhidos em 64 módulos de teste** (41 na raiz + 9 em `tests/scm/` + **10 em `tests/fix/`** 🆕 + **4 em `tests/review/`** 🆕; **+377** desde o relatório anterior, com **15 ficheiros novos**). Execução completa nesta máquina com `GITPR_LANG=en_us`: **1432 passed / 3 failed / 2 skipped / 81 subtests** em ~347s.

**Notas de qualidade desta versão:**
- **Nenhuma falha nova.** As 3 falhas são exatamente as mesmas do relatório anterior — e as duas de timeout continuam herdadas de antes:
- **2 falhas reais (testes desatualizados, herdadas):** `test_net_timeouts.py::test_ai_timeout_defaults_to_600` e `::test_invalid_ai_timeout_falls_back_to_default` afirmam a predefinição de 600s para `GITPR_AI_TIMEOUT`, mas o código usa **180s** desde o fix `681a7fa`. É o mesmo item que já estava nos Próximos Passos de **dois** relatórios anteriores e **continua em aberto**.
- **1 falha de locale (herdada, não é regressão):** `test_core.py::TestHooksLanguage::test_the_language_chosen_with_the_lang_flag_is_honoured` afirma `i18n.CURRENT_LANG == "pt_br"` — passa com o locale pt-BR da máquina e falha com `GITPR_LANG=en_us`. Continua a ser o único teste sensível ao locale da suíte.
- **Crescimento de 377 cenários com a linha de base de falhas intacta** — o sinal relevante desta janela: as três funcionalidades novas entraram sem derrubar nem mascarar nada.
- O `tests/conftest.py` mantém `GITPR_SHOW_LOGS=false` e `GITPR_SKIP_UPDATE_CHECK=true` — a suíte não escreve no registo de utilização nem é bloqueada pelo bloqueio de atualização.
- 🆕 **Fixtures de git real:** `tests/fix/git_fixture.py` monta repositórios git reais, em vez de mockar o `subprocess` — o `patch_applier` só é honesto se o `git apply` for o verdadeiro.

---

## **🌐 Internacionalização e Documentação**

* **Cobertura i18n:** **1048 chaves** de tradução em cada um dos 6 dicionários (+93 desde o relatório anterior) com **paridade total de key sets**. A cadeia medida por commit foi 955 → 1022 (`fix`, +67) → 1028 (revisores, +6) → **1048** (`review-pr`, +20). O `__lang_version__` passou v0.0.25 → v0.0.26 → v0.0.27 → **v0.0.28**, desencadeando o novo download OTA das traduções.
* **Fontes de tradução em lockstep:** uma chave nova tem de existir no código (fonte), em `langs/pt_br.json` (**lista mestra**), nos dicionários FR/ES de `scripts/sync_all_langs.py` (segunda fonte) e nos valores curados de `scripts/fix_mangled_i18n_keys.py` (terceira fonte, lida por `tests/test_i18n.py`); a asserção `len(CLEAN_KEYS)` continua em 49.
* **Tópicos Novos 🆕 (2):**
  - `docs/fix-command.md` — apontamentos de review como patches: pipeline, classificação de segurança, leitura antes de escrever, histórico e rollback, skill, MCP e variáveis — **em 5 idiomas**
  - `docs/review-pr.md` — review de PR remoto: o que é, que forges podem ser revistas, o relatório, publicação, skill, MCP e variáveis — **em EN + PT-BR** (os 3 idiomas restantes ficaram pendentes)
* **Tópicos atualizados nesta janela:** `docs/code-review-ia.*` (5 — modo remoto como §1.4 e a ressalva de que o linter externo não corre), `docs/suggested-reviewers.*` (5 — resolução de identidade), `docs/fix-command.*` (5 — §2.2 reescrita: o diff passou a vir do registo), `docs/commit-message-ia.*` (5), `docs/issue-tui-help.*` (5), `docs/linter-regras-customizadas.*` (5), `docs/providers-ia.*` (5) e `docs/skill-template.*` (5), além de `README.md`/`README.pt_br.md` (2×) e `CLAUDE.md` (tabela de comandos, tools MCP 13 → 14, árvore com `src/review/`).
* **Documentação em 5 idiomas:** **41 tópicos canónicos** em `docs/` — **35 com cobertura completa nos 5 idiomas** (+1 desde o relatório anterior) e **6 tópicos parciais/só em PT** (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers` e, agora, `review-pr` com 2 idiomas).
* **Skills Locais do Claude Code:** `.claude/skills/` com **29 skills** (contagem inalterada nesta janela).
* **Memory Index:** `.claude/memory/MEMORY.md` com 40 padrões (contagem inalterada nesta janela).
* **Relatórios de tarefas:** `docs/claude-code/reports/develop_natan/` (**93** no total; **+3** na janela — `gitpr fix`, resolução do login do revisor e review de PR remoto) e `docs/gemini/reports/develop_natan/` (5 ficheiros; nenhum novo).
* **Relatórios de status:** `docs/reports/` (14 relatórios; este é o 15.º).
* **Planos de desenvolvimento:** 99 ficheiros em `docs/plans/` (+10 na janela — specs/planos do `fix` e do `review-pr`, correção da sugestão de revisores, ADR-004, ADR-005 e os glossários `glossary-gitpr-fix` e `glossary-review-pr`) + **6 ficheiros em `docs/survey/`** (+3 na janela).

---

## **🔄 Pipeline de Distribuição**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Atualização obrigatória:** a execução verifica o PyPI no arranque e **bloqueia com código de saída 1** se existir uma versão mais recente, imprimindo `pip install --upgrade gitpr-cli`; a verificação é colocada em cache por dia e o `--update` apenas reporta
3. **GitHub Releases:** removido — sem PyInstaller, sem asset `.exe`, sem hot-swap
4. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml` (instala via pip)
5. **MCP Server:** Entry point `gitpr-mcp` via `pyproject.toml`
6. **Templates e Idiomas OTA:** `templates/` e `langs/*.json` servidos do GitHub (main) — o bump `v0.0.28` renova as cópias locais em `~/.gitpr/langs/` assim que for publicado
7. **Estado do release 1.2.0 🆕:** o `__version__` foi para 1.2.0 **na árvore de trabalho** e ainda não foi commitado nem tagueado (HEAD em 1.1.0, última tag `v1.1.0`); o `CHANGELOG.md` ainda para em `[1.1.0] - 2026-09-13`. O caminho é correr o `gitpr release` e fazer o commit do bump.

---

## **📈 Evolução Desde o Relatório Anterior (v0.0.14)**

| Área | v0.0.14 (anterior) | v0.0.15 (atual) |
|------|-------------------|-----------------|
| **Versão GitPR** | 1.1.0 | **1.2.0** (bump **não commitado**; HEAD em 1.1.0, última tag `v1.1.0`, CHANGELOG ainda em `[1.1.0]`) |
| **Versão Idioma** | v0.0.25 | **v0.0.28** (via v0.0.26 e v0.0.27) |
| **Versão Scripts Hook** | v0.0.3 | **v0.0.3** |
| **Provedores IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 idiomas, 6 dicionários | 5 idiomas, 6 dicionários |
| **Interface** | CLI + TUIs (Issues, Chat, Dashboard, PR Publisher, LinterApp, ConfigApp) + assistentes `--init`/`--install` + `gitpr release`/`gitpr config` | **+ `gitpr fix` (patches revisáveis) + `gitpr review-pr` (review de PR remoto) + `NoticeScreen`** |
| **Ferramentas MCP** | 12 tools / 17 recursos / 7 prompts | **14 tools / 18 recursos / 7 prompts** (+`list_fix_candidates`, +`review_remote_pr`, +`skill://fix`) |
| **Flags CLI** | 35 opções na raiz + `release` (6) + `config` (0) | **35 na raiz** + `release` (6) + `config` (0) + **`fix` (8)** + **`review-pr` (2)** |
| **Variáveis de Ambiente** | 39 chaves em `DEFAULT_CONFIG` | **44 chaves** (+5 `GITPR_FIX_*`) |
| **Linter** | Regex + bridge Checkstyle (wizard/TUI/relatório) | Inalterado (+ **`skip_external`**: o review remoto não publica alertas da árvore local) |
| **Git Hooks** | Corrigidos: `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG`, `effective_hook_lang()` | Inalterado |
| **Mensagens de Commit** | Com trailer `Co-Authored-By` (opt-out) | Inalterado |
| **Camada SCM** | 4 forges, `create_release`, `create_issue` | **+ `get_pull_request` (concreto na ABC) + `supports_reviewable_diff` + read-back de revisores (`list[str]`, quebra de contrato) + correções do GitLab** |
| **i18n (chaves por ficheiro)** | 955 × 6 (paridade total) | **1048 × 6 (paridade total) — +93 chaves** |
| **Documentação** | 39 tópicos canónicos (34 completos + 5 parciais) | **41 tópicos canónicos (35 completos + 6 parciais) — 2 famílias novas, 8 atualizadas** |
| **Distribuição** | PyPI exclusivo | Inalterado (release 1.2.0 pendente) |
| **Suíte de Testes** | 1060 cenários (49 ficheiros) | **1437 cenários (64 ficheiros: 41 + 9 SCM + 10 fix + 4 review) — en_us: 1432 passed / 3 failed (2 desatualizados + 1 de locale) / 2 skipped** |
| **Commits desde o relatório** | 2 commits | **5 commits** (`f4d5186`, `f5bed07`, `b9dd930`, `eb55400`, `a8a7770`) |
| **PRs mergeados** | 2 PRs (#162, #164) | **3 PRs (#167, #171, #173)** |
| **Memory Index** | 40 padrões | **40 padrões** |
| **Relatórios de tarefas** | 90 claude-code, 5 gemini | **93 claude-code (+3 na janela) e 5 gemini** |
| **Planos de desenvolvimento** | 89 planos, 3 surveys | **99 planos (+10), 6 surveys (+3)** |
| **Higiene de repositório** | — | **+ `pypa/`/`pip/` removidos do rastreamento, `.gitignore` atualizado** 🆕 |

---

## **🚧 Próximos Passos**

* **Fechar o release 1.2.0 🆕:** o `__version__` está em 1.2.0 na árvore de trabalho sem commit, sem tag e **sem entrada no `CHANGELOG.md`** (que para em `[1.1.0]`). Correr o `gitpr release`, commitar o bump e taguear — é o único item desta lista que bloqueia a publicação.
* **Traduzir o que ficou parcial 🆕:** `docs/review-pr.*.md` existe só em EN e PT-BR (faltam pt_pt, es_es, fr_fr) e `templates/gitpr.fix.md` só em EN e PT-BR — é a primeira vez em várias janelas que um tópico novo **não** nasce completo nos 5 idiomas.
* **Documentar a quebra de contrato do `request_pull_request_reviewers` 🆕:** passou de `None` para `list[str]`; as subclasses de terceiros de `ScmProvider` têm de ser atualizadas. `docs/scm-multiforge.*.md` é o lugar, nas 5 versões.
* **`.gitpr/metrics/export/` no `.gitignore` 🆕:** o PR #171 commitou `gitpr_metrics_2026-09-17.csv`/`.json` — são artefactos gerados localmente, como os de agosto que já estão rastreados.
* **Provedor Anthropic Claude:** Suporte direto à API do Claude (`claude-sonnet-5`).
* **Gráficos em ASCII/Textual no Dashboard:** Adicionar histogramas de tempo e gráficos de tendência de tokens na TUI de métricas.
* **Pipeline de Release no GitHub Actions:** Automação completa do build e do envio para o PyPI (a geração de changelog é agora local via `gitpr release`).
* **Seed Local de `.gitpr/conf/`:** O seed de templates de configuração local (smart-excludes, linter) continua pendente como subcomando próprio ou etapa do assistente; a TUI de configuração já oferece os **descarregamentos** desses ficheiros, mas não o seed no projeto.
* **Mais Provedores:** OpenAI direto, provedores locais adicionais.
* **Extrator i18n em `sync_i18n.py`:** O regex trunca literais com concatenação implícita (`__("a " "b")`) — migrar para AST (a guarda em `test_i18n.py` já usa AST e não depende do script).
* **Corrigir os Testes de Timeout Desatualizados:** `tests/test_net_timeouts.py` afirma uma predefinição de 600s, mas o código usa 180s desde o fix `681a7fa`; alinhar também a docstring desatualizada em `config.py`. **Item herdado, continua em aberto — já vão três relatórios.**
* **Reconciliar a Versão do Projeto:** O `CLAUDE.md` ainda diz `Current version: 0.0.37` enquanto o `__version__` está em 1.2.0 — a distância só aumentou (+0.0.37 vs. 1.1.0 no relatório anterior). Definir uma convenção única e atualizar o `CLAUDE.md`. **Item herdado, continua em aberto.**
* **Dívida do Índice do README:** Os bullets das famílias `suggested-reviewers`, `scm-multiforge`, `config-tui`, `usage-log` **e agora `fix-command` e `review-pr`** não estão no índice — a dívida cresceu nesta janela.
* **Robustez de Locale nos Testes:** 1 teste é sensível ao locale pt_br da máquina (`test_core.py::TestHooksLanguage`) — fixar `GITPR_LANG=en_us` no setup ou mockar `TRANSLATIONS` para que a suíte fique 100% verde em qualquer máquina/CI.
* **`gitpr -h config` ignora o `-h`:** o subcomando abre a TUI em vez de mostrar a ajuda — o gate `if ctx.invoked_subcommand is not None: return` corre antes do bloco `help_flag`. Corrigi-lo mudaria o comportamento do `-h` para **todos** os subcomandos, pelo que exige uma decisão.
* **Secção Smart Exclude na TUI:** dos 12 itens reportados após a utilização do ecrã, o item 10 (a secção *Smart Exclude*) é o único entregável ainda não iniciado.
* **Dívidas registadas no plano da TUI de configuração:** O `DEFAULT_CONFIG` ficou redundante com o schema; o banner de abertura não lista `--dashboard`, `--init`, `--base` nem `--plugins`; a `LinterApp` não desativa a command palette.

### ✅ Concluídos nesta janela (2026-09-13 → 2026-09-17)

* ~~**Subcomando `gitpr fix`**~~ — pacote `src/fix/` (8 ficheiros, 1147 linhas), classificador determinístico `safe`/`review_required`/`experimental`, dry-run por predefinição, `.gitpr/fix_history.json` e `--rollback` (PR #167).
* ~~**`resolve_last_review()` + `reviewed_diff` na cache**~~ — o review que alimenta o `fix` passou a ser escolhido por repo+branch, excluindo reviews com âmbito de ficheiro.
* ~~**Resolução de identidade do revisor**~~ — `src/reviewer_resolution.py`, `request_pull_request_reviewers` a devolver `list[str]`, read-back do `201`, retry individual no `422` e `NoticeScreen` (PR #171).
* ~~**Subcomando `gitpr review-pr`**~~ — pacote `src/review/` (5 ficheiros, 560 linhas), apenas de leitura por predefinição, `.txt` nomeado pela branch de origem, `--post-comment` como único caminho de escrita (PR #173).
* ~~**`ScmProvider.get_pull_request` + `supports_reviewable_diff`**~~ — método concreto na ABC implementado nas 4 forges; o Azure é barrado antes de qualquer chamada de rede.
* ~~**Correções latentes do GitLab**~~ — cabeçalhos `diff --git` sintetizados de `old_path`/`new_path` e `overflow: true` a passar a lançar.
* ~~**`skip_external` no linter**~~ — o review remoto deixou de publicar alertas do bridge externo, que corre contra a árvore local.
* ~~**MCP: 12 → 14 tools e 17 → 18 recursos**~~ — `list_fix_candidates` + `skill://fix` e `review_remote_pr`.
* ~~**i18n: +93 chaves e cadeia v0.0.25 → v0.0.28**~~ — paridade total de key sets nos 6 dicionários.
* ~~**Higiene de repositório**~~ — árvore pip 25.2 vendorizada removida do rastreamento; `.gitignore` com `pypa/` e `pip/` (commit `f5bed07`).

---

**Relatório gerado em:** 2026-09-17  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
