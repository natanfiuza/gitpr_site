# **🚀 Relatório de Estado do Projeto: GitPR CLI — v0.0.16 (2026-09-23)**

## **📌 Visão Geral**

O **GitPR** é uma ferramenta de CLI (Command Line Interface) avançada para automação de processos Git utilizando Inteligência Artificial (Google Gemini / DeepSeek / Ollama). O objetivo principal é atuar como um assistente inteligente local que faz Code Reviews, gera Pull Requests, mensagens de commit semânticas, audita dívida técnica e injeta boas práticas no fluxo de trabalho do desenvolvedor (Shift Left).

**Novidades desta versão (v0.0.16):**
- **`gitpr demo` — a primeira execução deixou de ser um ato de fé:** Uma visita guiada que mostra uma mensagem de commit, um review e uma descrição de PR **sem chave de API, sem repositório git e sem rede**. A pergunta a que responde ("o que é que esta ferramenta faz?") só tem uma janela para ser feita — o primeiro uso, antes de o utilizador ter configurado um fornecedor. A visita corre pelo **pipeline real de geração** com a origem da resposta trocada, pelo que o que aparece é o que a ferramenta produz de facto, enquanto todos os efeitos externos (cache, métricas, disco, rede, leitura de chaves) são neutralizados.
- **Selo GitPR no corpo do PR + comando `gitpr badge`:** Um PR escrito por IA era indistinguível de um escrito à mão, e o resultado do linter morria num terminal que já tinha rolado fora do ecrã. O selo transforma esse sinal privado numa afirmação visível no próprio PR publicado — e é deliberadamente um URL estático do shields.io, que o GitPR **nunca** descarrega, para que publicar um PR não passe a depender de um terceiro estar no ar. Medição honesta: sem regras de linter configuradas o selo **não** é emitido, porque uma lista vazia significa "nada foi verificado", não "nada foi encontrado".
- **`gitpr split` — uma árvore de trabalho com várias intenções deixa de virar um commit-blob:** Lê o diff não commitado, pede à IA que agrupe os hunks por intenção lógica e propõe **um commit atómico por preocupação**, cada um com uma mensagem gerada a partir do patch desse grupo isolado. A árvore nunca é reescrita: os ficheiros terminam byte a byte iguais ao início — só o histórico muda. *(O índice, sim: o `--apply` faz reset para HEAD antes de preparar o staging.)*
- **Deteção de segredos embutida — a regra que não pode ser substituída:** Sete regras (`src/security_ruleset.py`) que correm em **todas** as invocações do linter: cinco `error` bloqueantes (AWS key ID, token GitHub/Slack, chave Google, bloco de chave privada) e dois `warning`. O catálogo local era inteiramente gerido pelo utilizador — podia ser sobreposto pelo descarregamento, reescrito pelo assistente (perdendo comentários) ou alargado apenas por plugins da máquina. Uma barreira de segredos tem de se comportar da mesma forma em todas as máquinas, pelo que as regras passaram a viver **dentro do pacote**. **Alteração de comportamento: um commit que antes passava pode agora ser bloqueado.**
- **Suporte a `extensions: ["*"]`:** Passa a significar *todos* os ficheiros, incluindo os que não têm sufixo — que é exatamente de onde os segredos vazam (`id_rsa`, `.env`, `credentials`, `Dockerfile`). Uma regra com `extensions: ["py"]` mantém o filtro exatamente como antes.
- **Bridges SAST opt-in — Semgrep, Gitleaks e Bandit:** Uma camada que liga scanners de segurança de terceiros ao linter existente. Correm **só** quando estão ligados, **só** sobre os ficheiros tocados pelo diff, e os apontamentos são deduplicados contra o conjunto de regras interno: um segredo visto pelos dois aparece **uma vez** com um marcador de confirmação multi-fonte (`[Gitleaks + Regex]`). O Gitleaks tem os valores mascarados (`AKIA****`) antes de se tornar apontamento.
- **`gitpr tests generate` — a suíte que respeita a convenção do repositório:** Gera ficheiros de teste completos a partir do diff, de um ficheiro específico ou de um apontamento de review. Deteta o framework em uso (Pest, PHPUnit, Jest, Vitest, Pytest) em vez de impor um estilo, calcula o caminho convencional de destino (a separação `Feature`/`Unit` do Laravel, o `tests/**/test_*.py` do Pytest) e valida a sintaxe com o toolchain local (`php -l`, `node --check`, `python -m py_compile`) — uma falha de validação torna-se um aviso, não um erro. O dry-run é a predefinição.
- **`gitpr explain` + a flag `--explain` — o guia de quem vai revisar:** Um guia centrado em quem vai **revisar** (o que muda, porque muda, onde focar, qual é o risco de regressão) para que ninguém tenha de reconstruir a intenção a partir de um diff em bruto. Disponível como subcomando próprio e como flag que anexa a secção à descrição de PR gerada.
- **Arquitetura em camadas — `src/domain/` e `src/application/`:** As duas funcionalidades mais recentes (`tests` e `explain`) nasceram com uma separação explícita entre regras de domínio puras e orquestração de casos de uso, e a CLI e a TUI de chat passaram a partilhar **o mesmo** caso de uso em vez de o duplicarem.
- **Suíte determinística e o primeiro CI:** O `tests/conftest.py` tornou-se hermético (fixa `GITPR_LANG=en_us` e desliga o conjunto de regras de segredos) e o `.github/workflows/tests.yml` corre a suíte em **Python 3.10** (o piso declarado, nunca exercitado) e 3.13. Foi o CI que tornou visível o desvio de locale — **22 testes** falhavam numa máquina pt-BR por afirmarem o literal em inglês.
- **As 3 falhas herdadas de três relatórios seguidos foram fechadas:** os dois testes de timeout desatualizados (`600s` vs. a predefinição real de `180s`) e o teste sensível ao locale. A linha de base da suíte deixou de ser "3 falhas conhecidas" e passou a ser **verde por construção**.
- **Dívida nova e concentrada:** as duas funcionalidades mais recentes (`tests` e `explain`) chegaram com o registo de skills **a meio** — **40 chaves `__()`** usadas no código não existem em nenhum dos 6 dicionários, e os registos de rótulos da config e do MCP não receberam os tipos novos. São **4 falhas** na suíte completa, todas com a mesma causa raiz.
- **Estado do release 1.3.0:** o `__version__` e o `__lang_version__` estão **na árvore de trabalho e não commitados** (o HEAD continua em 1.2.0 / v0.0.31), o `CHANGELOG.md` **tem** a entrada `[1.3.0] - 2026-09-21` — mas cobre **apenas** a deteção de segredos, e não `demo`, `badge`, `split`, SAST, `tests` nem `explain`. A tag `v1.2.0` **foi criada** (merge do PR #174), fechando o item que bloqueava a janela anterior.

- **Versão atual:** 1.3.0 (bump na árvore de trabalho — HEAD em 1.2.0; última tag `v1.2.0`)
- **Versão dos dicionários de idioma:** v0.0.32 (bump na árvore de trabalho — HEAD em v0.0.31)
- **Versão dos scripts de hook:** v0.0.3
- **Publicação:** PyPI (`pip install gitpr-cli`) — canal binário removido
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositório:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licença:** LGPL-2.1
- **Idiomas suportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 dicionários)

---

## **🏗️ Arquitetura Base e Bibliotecas**

* **Linguagem:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags e formatação de terminal) — **9 subcomandos** 🆕 (`badge`, `config`, `demo`, `explain`, `fix`, `release`, `review-pr`, `split`, `tests`).
* **UI/Terminal:** Textual — TUI para chat interativo, edição de issues, ecrã de ajuda, dashboard de métricas, PR Publisher, erros do linter (`LinterApp`), configuração (`ConfigApp`), o modal `NoticeScreen` e a **visita guiada do `gitpr demo`** (`src/ui/demo/`) 🆕.
* **Arquitetura em Camadas 🆕:** `src/domain/` (regras puras, sem I/O) + `src/application/use_cases/` (orquestração) + apresentação (CLI/TUI). Introduzida com `tests` e `explain`; a CLI e o chat `/tests` chamam **o mesmo** caso de uso.
* **Criptografia:** `cryptography.fernet` para proteção local de chaves de API, tokens GitHub e tokens SCM das forges.
* **Configuração:** `python-dotenv`, `pyyaml` (linter estático) + o seu próprio schema declarativo (`src/config_schema.py`, **71 `ConfigField`** 🆕).
* **Provedores de IA:** Integração via SDK oficial do Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`) e OpenAI SDK (`Ollama` local).
* **APIs das Forges:** `requests` (REST) — abstração multi-forge em `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps); `src/github_api.py` mantido como shim obsoleto.
* **Linter / SAST 🆕:** regex YAML + bridge Checkstyle + **conjunto de regras de segredos embutido** (`src/security_ruleset.py`) + **4 bridges em `src/infrastructure/linter/external/`** (base + Semgrep, Gitleaks, Bandit), com modelo normalizado em `src/domain/linter/sast_finding_mapper.py`.
* **Git Internals 🆕:** `src/infrastructure/git/` — `patch_applier.py` (movido de `src/fix/`) e `selective_stager.py` (staging de um subconjunto arbitrário de hunks).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **14 ferramentas anotadas, 18 recursos, 7 prompts** (contagem inalterada nesta janela).
* **Testes:** Pytest + `unittest.mock` (**96 módulos de teste — 44 na raiz, 9 em `tests/scm/`, 10 em `tests/fix/`, 6 em `tests/split/`, 6 em `tests/demo/`, 7 em `tests/badge/`, 4 em `tests/review/`, 4 em `tests/infrastructure/linter/external/`, 2 em `tests/domain/tests_generation/`, 2 em `tests/application/use_cases/`, 1 em `tests/domain/pr/`, 1 em `tests/domain/linter/`** 🆕 —, **1889 cenários recolhidos**) + testes e2e do servidor MCP via subprocess real (JSON-RPC stdio) + fixtures de repositório git real (`tests/fix/git_fixture.py`, `tests/split/git_fixture.py`) + uma **guarda de isolamento de rede** (`tests/demo/conftest.py`).
* **Empacotamento:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`.
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` + **`tests.yml`** 🆕 (matriz Python 3.10/3.13).

---

## **🧩 Módulos Implementados e Arquitetura de Ficheiros**

### **1. Operações Core e Git (`src/core.py`)**

* **Geração Estruturada:** Comunica com o LLM a pedir um retorno estritamente em JSON.
* **Map-Reduce (Diffs Gigantes):** Quando o diff ultrapassa ~90k tokens, divide-o automaticamente em lotes por ficheiro (`split_diff_into_chunks`), processa cada parte (Map) e unifica os resumos (Reduce). Suporta PRs, commits e Issues.
* **Tokenizer Local:** `tokenizer.json` para estimativa precisa de tokens antes do envio à IA.
* **Estimativa de Tokens:** Heurística leve `len() // 4` via `estimate_token_count()` com fallback para o tokenizer local.
* **Otimização Git Nativa:** Flags `-U1`, `-w`, `-M`, `-B` nos comandos `get_git_diff` e `get_git_full_diff` para reduzir contexto inútil.
* **`get_split_diff()` 🆕:** O diff próprio do `split`, com `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sem** reutilizar o `get_git_diff()` — o `-w` (ignora espaços em branco) e os smart-excludes quebrariam a garantia de árvore byte-idêntica, porque o patch reconstruído tem de corresponder ao ficheiro em disco.
* **Pré-Gravação (`--pre-save`):** Flag de debug oculta que guarda o payload completo (instrução do sistema + prompt) em JSON antes de cada chamada à IA.
* **Exclusões Inteligentes com Duas Camadas:** Camada global (`~/.gitpr/conf/`) + camada local do projeto (`./.gitpr/conf/`), unidas em runtime. Auto-seed do ficheiro local na primeira execução. `_load_smart_excludes()` aceita `force=`.
* **Métricas com Registo de Tempo:** `log_command_metric()` em todos os fluxos com `duration_ms` e imports lazy.
* **Resolução Centralizada do Output:** `resolve_output_path()` — predefinição em `.gitpr/reports/{type}/`.
* **Assistente SCM (`run_scm_init_wizard()`)**: `gitpr --init` — deteta a forge a partir do remote de origem, solicita extras por forge, valida o token e persiste **apenas em caso de sucesso**. 🆕 Anuncia o selo automático no caminho do PR e informa o interruptor exato que o desliga.
* **Template de Skill de Release (`ensure_release_skill_template()`)**: Transfere `templates/gitpr.release.*.md` na primeira utilização de `gitpr release`.
* **Registo de Skills Partilhado:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` em `src/config.py`. 🆕 Ganhou entradas para `tests` (`.gitpr.tests.md`) e `explain` (`.gitpr.explain.md`).
* **Motor de Review com Âmbito de Cache (`generate_pr_content`):** `cache_scope` (anexado **apenas à chave de cache**) e `store_diff` (guarda o diff revisto). As predefinições mantêm o caminho local byte-idêntico.
* **Trailer de Coautoria:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceiros.
* **Subprocessos Blindados:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` em todos os `subprocess.run`; verificação de ligação via socket `8.8.8.8:53` antes das operações de rede.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitetura de Plugins:** Carrega plugins de `~/.gitpr/plugins/`, aplicando-se a **todos os projetos**.
* **Plugins de Linter (`linter/`):** Ficheiros `.yml` unidos ao `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`):** Ficheiros `.md` que estendem o contexto do sistema.
* **Factory Closures:** `get_linter_plugins` e `get_prompt_plugins` isolam estado entre sessões.
* **Comando `--plugins`:** Lista todos os plugins globais instalados com os seus tipos e paths.
* **Documentação Multilíngue:** `docs/plugins-system.md` em 5 idiomas.

### **3. Interface CLI e Configuração (`src/main.py` e `src/config.py`)**

* **Setup Inicial:** Deteta a primeira execução, cria a pasta `~/.gitpr/` e solicita interativamente as chaves de API, preferências e idioma. 🆕 A dica de primeira execução aponta para `gitpr demo`.
* **Routing de Comandos:** Gere todas as flags e os **9 subcomandos** — `release`, `config`, `fix`, `review-pr`, **`demo`** 🆕, **`badge`** 🆕, **`split`** 🆕, **`tests`** 🆕 (grupo, com `generate`) e **`explain`** 🆕.
* **Comportamento Predefinido:** Executar `gitpr` sem flags abre a TUI do PR Publisher.
* **Flags (agora 36 opções Click na raiz, +1 nesta janela):**
  * **`--explain` 🆕:** Inclui a secção *Reviewer Guide* na descrição do PR (e no payload JSON emitido).
  * `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--linter-setup`, `--version` — inalteradas.
* **Subcomando `demo` 🆕 — 3 opções:** `--scenario`, `--lang` e `--no-tui` (front-end em texto simples para CI e gravações). O `--lang` é aplicado **dentro** do subcomando, porque o callback raiz retorna antes do handler.
* **Subcomando `badge` 🆕 — 2 opções:** `--readme` (forma simples do snippet, pronta para pipe) e `--style` (`flat`/`flat-square`/`for-the-badge`). **Só imprime** — o README nunca é modificado.
* **Subcomando `split` 🆕 — 5 opções:** `--dry-run`, `--apply`, `--yes`, `--max-groups` e `--provider`. Uma invocação sem argumentos imprime o plano e pergunta antes de commitar seja o que for.
* **Grupo `tests` 🆕 → subcomando `generate` — 5 opções:** `--file`, `--finding`, `--framework`, `--apply` e `--provider`. O dry-run é a predefinição e sobrescrever um teste existente pede confirmação com **No** pré-selecionado.
* **Subcomando `explain` 🆕 — 1 opção:** `--provider`. Deteta a ausência de diff com um erro claro antes de qualquer chamada de IA.
* **Variáveis de Ambiente (49 chaves no `DEFAULT_CONFIG`, +5 nesta janela):** `GITPR_LINTER_SECURITY`, `GITPR_LINTER_SECURITY_DISABLED_RULES` e as três `GITPR_SPLIT_*` (`MAX_GROUPS`, `MAX_HUNKS`, `REQUIRE_CONFIRMATION`). **Outras 5 são apenas de leitura / não semeadas:** `GITPR_BADGE` (predefinição `true`), `GITPR_EXPLAIN_BY_DEFAULT` (predefinição `false`) e as três `GITPR_SAST_*_ENABLED` — o GitPR **nunca** as escreve sozinho no `~/.gitpr/.env`.
* **Ajuda Contextual:** `-h --flag` apresenta documentação específica da funcionalidade com uma ligação direta (ciente do idioma). Os subcomandos têm o seu próprio `epilog=` (parágrafo `\b` do Click para o URL não ser re-quebrado em nenhum idioma). 🆕 O `explain` entrou no `HELP_MAP`/`HELP_PRIORITY`.
* **--lang / --provider / --mcp / --install / --metrics / --status** — inalteradas.
* **Camada de Escrita do `.env`:** `read_env_file_values()` lê **apenas o ficheiro** via `dotenv_values`; `save_config_values()` com `set_key`; `remove_config_value()` com `unset_key`. `validate_ai_key()` distingue uma credencial recusada (`401`/`403`) de uma rede inacessível.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` e `src/ui/pr_publish_help.py`)**

* **Interface Interativa Completa:** TUI para rever, editar e publicar Pull Requests diretamente no terminal.
* **7 Ecrãs Modais:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` e `NoticeScreen`.
* **Selo no Corpo do PR 🆕:** O `attach_pr_badge()` corre no `src/main.py` logo após o `pr_data` estar completo — um **ponto único de injeção**, pelo que os dois publicadores (a área de texto da TUI e o `--no-edit`) leem o selo do mesmo sítio. O utilizador **vê** o selo e pode apagá-lo antes de enviar; o `--no-edit` imprime uma linha a dizer o que foi para o corpo e que ele não viu. Anexação idempotente por marcador — republicar ou mover o selo não empilha selos.
* **`_attach_reviewers` com resolução:** Resolve o que o utilizador escreveu **antes** de enviar, reporta o que foi descartado e, num `422` de lote, **repete um a um** — o GitHub rejeita o lote inteiro quando um único login é inelegível.
* **Revisores Sugeridos:** Consulta a forge para obter revisores sugeridos; a `_reviewer_suggestion_view()` monta as `resolutions` via `resolve_candidates` e pré-preenche os `handles`.
* **Bindings:** F1 (Ajuda), F2 (Guardar .md local), F3 (Publicar via forge), Esc (Sair).
* **Fluxo de Auto-Commit:** Linter → mensagem IA → confirmação → commit → push → publica PR.
* **Verificação de Ficheiros Unstaged:** `git status --porcelain` no arranque, com um modal para selecionar, saltar ou cancelar.
* **Tratamento de PR Existente / Auto-Upstream / Merge Flow** — inalterados (`GITPR_AUTO_MERGE`).

### **5. Módulo de API do GitHub (`src/github_api.py`)**

* **Shim Obsoleto:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` e as restantes funções delegam em `src/infrastructure/scm/github_provider.py`; emite um `DeprecationWarning` e mantém os tuplos legacy `(ok, data, status)` — nenhum código novo o pode importar.

### **6. Motor de Análise Estática / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analisa estaticamente as linhas adicionadas (`+`) no diff sem gastar quotas de IA.
* **Regras YAML:** Lê o `.gitpr.linter.yml` (criado via `--skill`).
* **Plugins de Linter:** Regras adicionais carregadas de `~/.gitpr/plugins/linter/*.yml`.
* **Wildcard de Extensão 🆕:** `extensions: ["*"]` passa a significar **todos** os ficheiros, incluindo os que não têm sufixo e os dotfiles. É o que dá cobertura a `id_rsa`, `.env`, `credentials` e `Dockerfile`; uma regra com `extensions: ["py"]` mantém o filtro como sempre foi.
* **Conjunto de Regras de Segredos Embutido 🆕:** O `load_linter_rules()` junta o `src/security_ruleset.py` **depois** das regras do projeto e dos plugins, que ficam intactas — só a ordem muda, empurrando os alertas de segurança para o fim do relatório. Sete regras, todas `extensions: ["*"]`: cinco `error` (AWS key ID, token GitHub/Slack, chave Google, bloco de chave privada) e dois `warning` (URL de base de dados com credenciais e atribuição genérica de credenciais, atrás de um filtro de placeholder).
* **O Alerta Nunca Ecoa o Valor:** A mensagem transporta apenas `{file_name}` e `{line_number}` — viaja para a consola, para o relatório Markdown e, no fluxo de PR, para o corpo de um pull request público; imprimir o segredo copiá-lo-ia para os três.
* **`--input` Alargado 🆕:** A auditoria de ficheiro inteiro passou a varrer `.md`, `.txt` e lockfiles, já que o conjunto de regras corresponde a todas as extensões. Aí **reporta sem bloquear** — o `sys.exit(1)` existe apenas no caminho `--linter`.
* **Bridges SAST Opt-In 🆕:** Semgrep, Gitleaks e Bandit correm no caminho de ficheiro inteiro **e** no caminho de diff, filtrando os apontamentos pelas linhas adicionadas e avisando quando a ferramenta está ligada mas ausente do `PATH`. O `load_sast_config()` resolve em três camadas: predefinições → `.gitpr.linter.yml` (bloco `sast`, caindo para `linter.external`) → variáveis `GITPR_SAST_*`. **Tudo tem predefinição `false`** (opt-in estrito).
* **`skip_external`:** O `parse_diff_and_lint(..., skip_external=False)` desliga os **dois** call-sites do bridge externo; o fluxo remoto passa `True`, porque o bridge executa binários contra ficheiros em disco — a árvore local, não o PR.
* **Relatório Consolidado:** O `generate_linter_report_content()` consolida erros regex + externos em `.gitpr/reports/linter/` — gerado apenas quando há violações.
* `load_linter_presets()` aceita `force=` para novo descarregamento a partir da TUI.

### **7. Segurança e Autenticação (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografia:** Gera a chave mestra `secret.key` em `~/.gitpr/`.
* **Proteção de Tokens:** `encrypt_data`/`decrypt_data` para chaves de IA, GitHub PAT e tokens SCM das forges.
* **Validação Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — 401 → ciclo de reautenticação preservando o rascunho; o token legacy do GitHub permanece funcional até o `--init` correr.
* **Segredos na TUI de Configuração:** Os campos `KIND_SECRET` são editados mascarados, **nunca** apresentam o valor em claro e são encriptados com Fernet antes de serem escritos. O `GITPR_SCM_TOKEN` é `read_only`.
* **Deteção de Segredos no Próprio Fluxo 🆕:** O linter — que corre no hook de pre-commit — passou a ser uma barreira de credenciais com regras idênticas em todas as máquinas, porque vivem no pacote e não num template descarregado que o `--skill` ou o assistente podem substituir.

### **8. Auto-Atualizador (`src/updater.py`)**

* **PyPI como Fonte Única:** O `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` e escreve a cache diária **sem** o campo `download_url`.
* **Bloqueio Obrigatório (`enforce_update_required()`):** Devolve `True` (depois de imprimir as duas versões e o comando pip) quando a versão publicada é mais recente; `False` quando está atualizado, quando a versão remota é **desconhecida (offline)** ou quando a verificação está desligada. Devolver um `bool` em vez de chamar `sys.exit` internamente mantém a função testável.
* **`check_and_update()`:** Apenas consulta e **reporta**, nunca instala.
* **Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` — não é publicitada; existe para a suíte e para automatização offline.
* **Versionamento Centralizado:** `__version__` (**1.3.0** — bump na árvore de trabalho), `__lang_version__` (**v0.0.32** — cadeia v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32 nesta janela 🆕), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.

### **9. Interface de Chat Interativa (`src/ui/chat_app.py`)**

* **TUI Completa:** Histórico de mensagens, input multi-linha, barra de estado com bindings visíveis.
* **Memória por Branch (`src/chat_memory.py`):** Histórico persistido por branch, com continuidade entre sessões.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`. 🆕 O `/tests` (e os aliases localizados `/testes`, `/pruebas`) **deixou de ser encaminhado para o modelo genérico** e passou a delegar no **mesmo caso de uso** que a CLI usa (`TestGenerationTarget`), em vez de duplicar a lógica.
* **Auto-Patching (F5), Atualização de Diff (F2), Exportação de Sessão (F6).**
* **Extrator Partilhado:** O F5 e o `ctrl+s` chamam `patch_extractor.extract_code_blocks()` — comportamento visível idêntico, sem lógica duplicada.

### **10. Internacionalização — i18n (`src/i18n.py`)**

* **Sistema Inspirado no Laravel:** Função `__()` com suporte a placeholders nomeados (`{count}`, `{file}`, etc.).
* **Deteção Automática:** Deteta o idioma do SO na primeira execução e guarda-o em `GITPR_LANG`.
* **5 Idiomas, 6 Dicionários:** en_us (predefinição/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Ficheiros Versionados:** O `__lang_version__` (**v0.0.32**) controla a atualização dos pacotes de idioma (`langs/*.json`) — cadeia de bumps v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32 nesta janela.
* **Cobertura:** **1158 chaves** em cada um dos 6 ficheiros — **paridade total de key sets** (+110 desde o relatório anterior). **Ressalva importante 🆕:** o código usa **1198** chaves, pelo que **40 chaves `__()` não existem em nenhum dicionário** (ver §33 e §34) — a paridade entre os 6 ficheiros é total, mas a cobertura em relação ao código não é.
* **Correção de Primeira Execução 🆕:** O `i18n.py` passou a **criar o diretório do perfil antes** de persistir o idioma detetado, evitando um crash na primeira execução numa máquina limpa.
* **Snapshot do Ambiente (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado **imediatamente antes** do `load_dotenv()` ao nível do módulo — corrige o badge "⚠ no ambiente" que confirmava tautologicamente que a chave está no ficheiro.
* **Cache com Indexação por Idioma:** As respostas de IA em cache incluem o idioma corrente no chaveamento MD5.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Thread em background durante chamadas de IA com caracteres braille e palavras de "pensamento".
* **263 entradas por idioma:** Sincronizadas entre os 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceitam `force=`.

### **12. Provedores de IA (`src/ai_providers.py`)**

* **3 Provedores Suportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Modo JSON & Parâmetros Determinísticos:** `temperature=0.0` e `top_p=0.1`; fallback automático entre os provedores configurados.
* **Timeout de IA:** predefinição de **180s** (`GITPR_AI_TIMEOUT`) — o valor de 600s foi baixado deliberadamente no fix `681a7fa` e o teste desatualizado foi finalmente alinhado nesta janela (ver §37).

### **13. Cache Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Chaveamento por hash MD5 do diff e do prompt, com indexação por idioma.
* **Seleção do Último Review (`resolve_last_review()`):** Escolhe o registo `review`/`fullreview` **mais recente** para um par repo+branch, excluindo reviews com âmbito de ficheiro — é a porta de entrada do `gitpr fix`.
* **Diff Revisto (`reviewed_diff`):** Campo no topo do registo que guarda o diff efetivamente revisto, preferido por `fix/apply_fix.reviewed_diff()`.
* **Telemetria e Duração:** Persistência dos campos `duration_ms` e `meta_raw`.
* **Leitura para o Dashboard:** O `scan_cache_files_for_dashboard()` lê todos os ficheiros de cache recursivamente.

### **14. Motor de Issues e TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff atual, Histórico da branch (`-ht`) e Arqueologia por Blame (`-b`).
* **Publicação Multi-Forge:** O F3 cria a issue na forge **configurada** (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — o Azure DevOps lança `ScmNotSupportedError`.
* **Map-Reduce para Issues:** Contexto acima de ~90k tokens é dividido e unificado.
* **Tratamento de 401:** Sinalização de reautenticação sem fechar a aplicação.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Acompanha a evolução e a autoria histórica de excertos de código com classificação de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registados via `log_blame_metric()` com profundidade e número de commits analisados.

### **16. Servidor MCP e Invocação Direta via CLI (`src/mcp_server.py`)**

* **14 Ferramentas MCP Anotadas:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates` e `review_remote_pr` — **contagem inalterada nesta janela**.
* **18 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `prompt://list` + 7 prompts. **Ressalva 🆕:** `skill://explain` e `skill://tests` **não** existem — o teste `TestSkillRegistryAgreement` falha justamente porque o `mcp_server.SKILL_FILES` não recebeu os dois tipos novos que o `config.SKILL_FILES_BY_TYPE` já tem.
* **Invocação CLI Direta:** O comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca qualquer tool sem iniciar o servidor stdio; o `gitpr-mcp --list` imprime o registo completo em JSON.
* **Isolamento do Stdout Real:** O `_write_real_stdout()` escreve no `sys.__stdout__` original, garantindo JSON puro. 🆕 Endurecido contra `UnicodeEncodeError` em code pages legacy do Windows (fallback para `buffer` ou nova codificação com `errors='replace'`).
* **Offload do Event Loop:** Decorator `_offload` (`anyio.to_thread.run_sync`) nas 14 tools — a ordem dos decorators importa (`@mcp.tool` **acima** de `@_offload`).
* **Registo de Utilização:** O `main()` do servidor chama `log_usage()` — o console script `gitpr-mcp` nunca carrega o `main.py`.
* **Testes E2E:** O `tests/test_mcp_server_e2e.py` inicia o servidor real como subprocess e fala JSON-RPC stdio.

### **17. TUI de Dashboard de Métricas (`src/ui/metrics_app.py`)**

* **Âmbito por Repositório (Repo-Scope):** Rótulo `📁 Repository: owner/repo` e filtragem estrita por projeto.
* **Varredura Assíncrona com Overlay:** Worker thread em background com `ProgressBar`. 🆕 Os testes passaram a aguardar `workers.wait_for_complete()` antes de afirmar — sem isso, a suíte era instável por causa de uma corrida.
* **Consolidação de Dados:** O `load_cache_token_summary()` soma tokens de cache ao totalizador.
* **Exportação Local:** CSV/JSON em `./.gitpr/metrics/export/` — 🆕 mais artefactos gerados entraram **rastreados** (`gitpr_metrics_2026-09-18/19/21/22.*`); a dívida do `.gitignore` continua em aberto.

### **18. Sistema de Métricas e Telemetria (`src/metrics.py`)**

* **Âmbito por Repositório:** Todos os eventos indexados por `repo_name`.
* **Eventos de Hook, Linter e Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Exportação e Limpeza:** `--metrics --export` (CSV/JSON) e `--metrics --purge` com confirmação interativa.

### **19. Sincronização de Idiomas dos Git Hooks**

* **Versionamento Independente:** `__scripts_version__` (v0.0.3).
* **Mapeamento de Sufixos (`HOOK_SCRIPT_SUFFIXES`):** Os códigos de interface (`es_es`, `fr_fr`) traduzidos para os sufixos publicados (`.es`, `.fr`).
* **Escolha vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detetar uma mudança de idioma**.
* **`effective_hook_lang()`:** Resolve o idioma efetivo; o `--lang` deixou de ser descartado nesse caminho.
* **Skip de Merge-Source:** O `prepare-commit-msg` salta as fontes `message|merge|squash|commit`.

### **20. Bridge de Linters Externos e Assistente Interativo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistente `--linter-setup`:** Um assistente interativo com presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e injeção do bloco `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` com uma cadeia local → download → stale → fallback embutido.
* **TUI de Erros do Linter:** O `src/ui/linter_app.py` (Textual) apresenta erros críticos e warnings; em modo hook/quiet imprime e faz `sys.exit(1)`.
* **Relatório Markdown:** Consolidado em `.gitpr/reports/linter/` apenas quando há violações.
* 🆕 **A Camada SAST É Irmã, Não Substituta:** os bridges Semgrep/Gitleaks/Bandit vivem em `src/infrastructure/linter/external/` (ver §31) e alimentam o **mesmo** pipeline de relatório.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstração Única (`ScmProvider` ABC):** O `base.py` define o contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. e `ScmProviderError(provider, http_status, message)` — `http_status` 0 = falha de rede); um provedor concreto por forge.
* **Registo e Factory:** O `resolve_scm_provider()` seleciona por `GITPR_SCM_PROVIDER` (predefinição `github`); o `detect_provider_from_remote()` identifica a forge a partir do URL de origem.
* **Endereçamento de Repositórios:** `parse_repo_ref(remote_url) -> RepoRef(raw, workspace, name, provider)`.
* **`get_pull_request(repo, pr_id)` — método concreto da ABC:** A predefinição lança `ScmNotSupportedError` e cada forge implementa-o — o `list_open_pull_requests` pagina **uma única página** e **não distingue fechado de inexistente**.
* **`supports_reviewable_diff`:** Atributo de classe, `False` no Azure DevOps, verificado **antes de qualquer chamada de rede**.
* **Cabeçalhos do GitLab Sintetizados:** O `changes[].diff` é um **hunk solto**; o `old_path`/`new_path` passaram a montar os cabeçalhos `diff --git a/… / --- / +++`, honrando o `/dev/null`.
* **`overflow` do GitLab Lança:** Um MR com um diff truncado era revisto a meio; agora lança `ScmProviderError`.
* **`request_pull_request_reviewers` devolve `list[str]` (quebra de contrato):** Devolve os logins **efetivamente associados**, lidos do corpo do `201`. As subclasses de terceiros têm de ser atualizadas.
* **Dois Helpers de Apenas Leitura do GitHub:** `get_commit_author_login` (mapeia um SHA à conta ligada ao e-mail do autor) e `get_user_login` (valida/canonicaliza um handle, rejeita o que não pode ser login **sem gastar um pedido**).
* **Fail-Fast por Forge:** O Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; o Bitbucket exige `GITPR_SCM_USERNAME`; o `create_issue` no Azure lança `ScmNotSupportedError`.
* **Publicação de Release:** `provider.create_release()` usado pelo `gitpr release --publish`.
* **Artefactos:** Glossário + ADR-001/ADR-005; a família `docs/scm-multiforge.*.md` em 5 idiomas; 9 ficheiros de teste, **284 cenários**.

### **22. Subcomando `gitpr release` — Changelog / Release Notes**

* **Fluxo:** `git log` entre `--since` e `HEAD` → classificação por Conventional Commits → bump semântico sugerido (`--version <x.y.z>` sobrepõe-se) → montagem do changelog → resumo executivo por IA opcional → *anteposição* ao `CHANGELOG.md`.
* **Classificador (`src/commit_classifier.py`)** e **Builder com Secções Traduzíveis (`src/changelog_builder.py`)** — secções renderizadas via `__()` em runtime.
* **Bump Semântico (`src/version_bump.py`)** e **Publicação** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opções no subcomando.**
* **Pendência de Release 🆕:** A entrada `[1.3.0] - 2026-09-21` **existe** no `CHANGELOG.md`, mas foi escrita pelo commit da deteção de segredos e cobre **apenas** essa — `demo`, `badge`, `split`, SAST, `tests` e `explain` **não estão** no changelog, e a entrada está datada de 21/09 enquanto o `explain` é de 23/09. Correr o `gitpr release` é o passo que falta.

### **23. Subcomando `gitpr config` — TUI de Configuração**

* **Ecrã Master-Detail (`src/ui/config_app.py`):** Categorias à esquerda, campos à direita, editados inline. Cabeçalho com procura (`/`) e um contador de pendências; rodapé `F1 Ajuda · F2 Guardar · ^R Restaurar · / Procurar · Esc`.
* **Schema Declarativo (`src/config_schema.py`) — a única fonte de verdade:** 🆕 **14 categorias** (+1: **Split**) e **71 `ConfigField`** (+10), dos quais **9 avançados**. Cada campo declara a sua categoria, tipo de widget, `show_if`, validadores, marcadores de versão e ações de descarregamento.
* **Categoria `split` 🆕:** `GITPR_SPLIT_MAX_GROUPS`, `GITPR_SPLIT_MAX_HUNKS` e `GITPR_SPLIT_REQUIRE_CONFIRMATION` com um parser de inteiro positivo que recorre à predefinição num valor não-analisável — **zero ou negativo não desliga o teto em silêncio**.
* **Campos Apenas de Leitura 🆕:** `GITPR_BADGE`, `GITPR_EXPLAIN_BY_DEFAULT` e as três `GITPR_SAST_*_ENABLED` aparecem no schema (e portanto no ecrã) mas **não** são semeadas no `.env`.
* **Filtragem por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` conforme o `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` sob `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` sob `[Azure DevOps]`. Alterar o `Select` re-filtra o painel imediatamente.
* **Procura Global (`/`):** Encontra correspondências na chave ou no rótulo em todas as categorias e **ignora o filtro de visibilidade**.
* **Validação em Duas Camadas:** **Offline** bloqueia o `F2` com um erro inline; **online** (apenas credenciais alteradas na sessão) corre num worker com timeout de 10s e só bloqueia em `401`/`403`.
* **Restaurar (`Ctrl+R`):** Remove a linha do `.env` em vez de reescrever a predefinição; o `Esc` com alterações pendentes pede confirmação.
* **Secção Skills — a única com âmbito de projeto:** Edita os `.gitpr/skill/*.md` do projeto, resolvidos a partir do diretório de chamada, escrevendo atomicamente e preservando CRLF/LF. **Dívida conhecida 🆕:** O `SKILL_LABELS` não recebeu `tests` nem `explain` — sem rótulo, os dois tipos renderizam **em branco** na barra lateral (ver §33/§34).
* **Descarregamentos Forçados:** Botões que forçam o novo descarregamento dos smart-excludes, das traduções, dos presets e das thinking words via `force=`.
* **Módulo de Ligações Leve (`src/doc_links.py`):** O `doc_url()` saiu do `core.py` para que a UI possa obter a ligação sem importar os SDKs de IA.
* **Testes:** 5 ficheiros — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Dívida conhecida:** O `gitpr -h config` abre a TUI e ignora o `-h` — o gate `if ctx.invoked_subcommand is not None: return` corre antes do bloco do `help_flag`.

### **24. Registo Geral de Utilização (`src/usage_log.py`)**

* **Uma Linha por Comando:** Escreve em `~/.gitpr/logs/<uuid5>.log`, **um ficheiro por dia**, com o comando, os argumentos, o repositório, o utilizador e o timestamp.
* **Nome Derivado da Data:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` em vez de aleatório — dois processos concorrentes não podem discordar sobre qual é o ficheiro de hoje.
* **Escrita Síncrona (decisão explícita):** Ao contrário do `log_local_metric`, que usa uma thread daemon e perde a escrita se o processo terminar primeiro.
* **Nunca Imprime:** O servidor MCP reserva o stdout para JSON-RPC; o módulo também nunca lança exceções.
* **Um Único Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms em vez de ~150 ms no Windows, em *todas* as execuções.
* **Controlo:** `GITPR_SHOW_LOGS` (predefinição `"true"`); desligado no `tests/conftest.py`.
* **Artefactos:** `docs/usage-log.*.md` em 5 idiomas; `tests/test_usage_log.py` (27 cenários).

### **25. Subcomando `gitpr fix` — Apontamentos de Review como Patches Revisáveis (`src/fix/`)**

* **O Pipeline Real:** último review da cache (`resolve_last_review`) → **uma** chamada de IA → extração e validação de diff unificado → `git apply --check` → classificação determinística → dry-run ou escrita → histórico.
* **Pacote de 8 ficheiros:** `patch_provenance.py`, `patch_extractor.py` (partilhado com o chat), `patch_safety_classifier.py` (puro, sem I/O), `patch_applier.py`, `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py` (só docstring).
* **Classificação Determinística:** Recusa um patch que atravesse mais de um ficheiro ou hunk, toque num caminho sensível, estoure o orçamento de linhas, **apague uma linha com aspeto de chamada**, venha marcado de baixa confiança ou falhe no `git apply --check`. O `--force` **nunca** contorna a aplicabilidade, só a classificação.
* **Escrita Opt-In:** O dry-run é a predefinição; qualquer mutação exige `--apply` (ou uma frase escrita com `--force`).
* **`patch_applier.py` Mudou de Casa 🆕:** O invólucro do `git apply` foi promovido a `src/infrastructure/git/patch_applier.py` (partilhado com o `split`) e o `src/fix/patch_applier.py` passou a ser um **shim de re-exportação** — nenhum import existente quebrou.
* **Testes:** 10 ficheiros em `tests/fix/` (**204 cenários**) com fixtures de repositório git real.

### **26. Subcomando `gitpr review-pr` — Review de PR Remoto (`src/review/`)**

* **O que é:** Orquestração, **não** um segundo motor de review — o motor existente, o linter e o renderizador alimentados com um diff que veio de outro sítio. Um `.txt` de review remoto e um de review local do mesmo diff diferem **apenas no nome do ficheiro**.
* **Pacote de 5 ficheiros:** `diff_source.py` (proveniência pura), `diff_normalizer.py` (normalização, validação e smart-excludes **em Python**), `render.py` (extraído do `main.py`, agora partilhado), `remote_pr.py` (o caso de uso), `__init__.py`.
* **Apenas de Leitura por Predefinição:** O `--post-comment` é o **único** caminho que escreve na forge. A tool MCP `review_remote_pr` nem sequer recebe o argumento.
* **Interação com o `fix`:** Como o review remoto não corresponde a nenhuma árvore local, o diff revisto passou a ser guardado na cache (`reviewed_diff`) e o `fix` prefere-o.
* **Artefactos:** `docs/review-pr.md` + `.pt_br.md`, `docs/code-review-ia.*.md` (5), ADR-005 e `glossary-review-pr.md`.
* **Testes:** 4 ficheiros em `tests/review/` (**97 cenários**).

### **27. Resolução de Identidade do Revisor (`src/reviewer_resolution.py`)**

* **O Bug (duas falhas silenciosas encadeadas):** (1) **Prefill vazio** — os `handles` só com o `email_to_handle()`, que apenas vê e-mails `users.noreply.github.com` ou com endereço **público**; (2) **Associação não verificada** — valores repassados *verbatim*, o GitHub responde **201 sem associar ninguém** e o `_request()` retorna sem lançar: sucesso aparente, revisor ausente, aviso nenhum.
* **Módulo Novo:** Um plano, sem I/O próprio, **que nunca lança**. O `resolve_candidates()` (antes da TUI) e o `resolve_typed_reviewers()` (no momento da associação); o `match_candidate()` faz correspondência exata e normalizada por login, nome ou e-mail.
* **Escada de Resolução:** handle já conhecido (sem qualquer pedido) → correspondência exata com uma pessoa sugerida → lookup por e-mail → validação do login na forge.
* **O que não resolve nunca é enviado:** Sai em `ResolutionOutcome.dropped` como `(valor, motivo_i18n)` e é descartado com um aviso visível.
* **Provedor Duck-Typed:** O acesso é feito por `getattr`, pelo que fakes e forges sem os métodos novos continuam a funcionar.
* **Testes:** `tests/test_reviewer_resolution.py` (18) + ampliações em `test_github_provider.py` (68), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) e `test_main_suggest_reviewers.py` (8).

### **28. Subcomando `gitpr demo` — Visita Guiada sobre Exemplos Gravados (`src/demo/`) 🆕**

* **O que É:** Uma visita interativa que mostra as três saídas principais do GitPR — mensagem de commit, review e descrição de PR — **sem chave de API, sem repositório Git e sem ligação**. A pergunta "o que é que esta ferramenta faz?" só tem uma janela para ser feita: a primeira utilização, antes de o utilizador ter configurado um provedor.
* **Pipeline Real, Resposta Falsa:** O `FakeAIProvider` espelha o `src.ai_providers.call_ai_model` **argumento a argumento**; o `demo_pipeline()` faz patch do `src.core` e do `src.metrics` para que o pipeline de produção (montagem do prompt, fragmentação do diff, análise da resposta) corra **intocado** e apenas a origem da resposta seja trocada. É o que garante que a visita mostra o que a ferramenta produz de facto, e não uma maquete.
* **O Isolamento É um Requisito, Não um Detalhe:** A visita **não pode** tocar no `~/.gitpr` do utilizador (cache, métricas, registos) nem na rede — garantido pelo `tests/demo/conftest.py` (proibição de rede autouse, com uma exceção de loopback para o event loop proactor do Windows). Uma regressão aqui envenenaria em silêncio uma cache real com conteúdo de demonstração.
* **Pacote:** `demo_runner.py` (`DemoState`, máquina de estados sem UI + front-end em texto simples), `fake_ai_provider.py`, `scenarios/` (dois exemplos: um bug de atualização de perfil em Laravel e um IDOR entre inquilinos em Express, cada um localizado nos 5 idiomas com recurso ao inglês). Os cenários são **módulos Python, não JSON**, para viajarem dentro do wheel.
* **TUI:** `src/ui/demo/` (ecrã único, seguindo o padrão das apps existentes) com um modal de ajuda; o diff é cercado como ```` ```diff ```` e a mensagem de commit é renderizada como texto simples.
* **CLI:** `gitpr demo` com `--scenario`, `--lang` e `--no-tui`.
* **i18n:** +32 chaves e um bump para **v0.0.29**.
* **Testes:** 6 ficheiros em `tests/demo/` (**155 cenários**) — máquina de estados, integridade dos cenários (aritmética de hunks), navegação da TUI, modo texto, o contrato do provedor falso e as guardas de isolamento.
* **Artefactos:** `docs/demo.*.md` em 5 idiomas, `README.*` (5) e as métricas de exemplo da visita.

### **29. Selo GitPR e o Comando `gitpr badge` (`src/branding/`) 🆕**

* **O que É:** Um selo público que atesta que um pull request passou por uma verificação de qualidade local, e um comando que imprime o snippet de adoção para o README do projeto.
* **A Motivação É a Confiança no Ponto de Publicação:** Um corpo de PR escrito por IA é indistinguível de um escrito à mão, e o resultado do linter morria num terminal que já tinha saído do ecrã. O selo transforma esse sinal privado numa afirmação visível e verificável no próprio PR publicado.
* **URL Estático de Propósito:** É um Markdown do shields.io que o GitPR **nunca** vai buscar — publicar um pull request não pode passar a depender de um terceiro estar de pé.
* **Medição Honesta (`badge_data.py`):** O `collect_linter_counts()` devolve `None` quando não há regras de linter configuradas (corra `gitpr --skill`), porque uma lista vazia significa "nada foi verificado", não "nada foi encontrado" — um selo verde sobre um diff por verificar seria uma afirmação que o GitPR não pode sustentar. A bridge externa é ignorada: inspeciona a árvore de trabalho, não a revisão em análise.
* **O Selo Nunca Bloqueia uma Publicação:** Todos os caminhos de falha na recolha são apanhados e degradam para "sem selo".
* **Ponto de Injeção Único:** O `attach_pr_badge()` no `src/main.py`, logo depois de o `pr_data` estar completo — ambos os publicadores leem do mesmo sítio. Acrescento idempotente por marcador.
* **Comando `gitpr badge`:** `--readme` (forma nua, pronto para pipe) e `--style`. **Apenas imprime** — o README nunca é modificado.
* **Divulgação:** O assistente de inicialização anuncia o selo automático no caminho do PR e o `--no-edit` imprime uma linha a nomear o que entrou no corpo sem o utilizador o ter visto — uma funcionalidade que só aparece depois de configurar uma variável é uma funcionalidade que ninguém descobre.
* **Configuração:** `GITPR_BADGE` (predefinição `true`) — um opt-out **apenas de leitura**, nunca escrito automaticamente.
* **i18n:** +10 chaves e um bump para **v0.0.30**.
* **Testes:** 7 ficheiros em `tests/badge/` (**83 cenários**) — builder, recolha de dados, CLI, opt-out, caminhos de publicação e uma guarda offline (proibição de rede autouse, exceto loopback); as suítes do demo e do assistente foram ampliadas.
* **Demo:** O passo do PR na visita mostra agora o selo que uma publicação real anexaria, contado a partir do bloco de linter **gravado no próprio cenário** — nenhuma regra é lida e nenhum diff é verificado.
* **Artefactos:** `docs/badge.*.md` em 5 idiomas e `docs/survey/20260919_gitpr_badge_surveyfacts.md`.

### **30. Subcomando `gitpr split` — Commits Atómicos por Hunk (`src/split/`) 🆕**

* **O que É:** Uma árvore de trabalho com várias preocupações já não tem de se tornar um commit-blob. O `split` lê o diff não consolidado, pede à IA para particionar os hunks por **intenção lógica** e propõe um commit atómico por preocupação — cada um com uma mensagem gerada a partir do patch reconstruído **a partir desse grupo isolado**. A árvore nunca é reescrita: os ficheiros ficam byte a byte idênticos ao início; só o histórico muda.
* **Pacote de 6 Ficheiros, em Camadas de Baixo para Cima:**
  * `split_plan.py` — contrato de dados: `SplitError`, `Hunk`, `OpaqueSection`, `ChangeUnit`, `HunkGroup`, `SplitPlan`.
  * `hunk_parser.py` — `parse_units()` / `build_patch()`: texto ↔ modelo, **puro**, nunca corre o git.
  * `hunk_grouper.py` — renderização do prompt, orçamento e corte, **uma** chamada de IA, validação da resposta.
  * `generate_split_plan.py` — diff → unidades → grupos → pré-validação de conflitos → mensagens. **Apenas de leitura.**
  * `apply_split_plan.py` — **o único módulo do `split` que muta**: indexação seletiva e um commit por grupo.
  * `__init__.py` — marcador do pacote.
* **A Garantia de Árvore Intacta É Estrutural:** O `selective_stager.py` pré-valida **todos** os grupos contra um índice temporário no HEAD e usa um `GIT_INDEX_FILE` descartável — um plano nunca é aplicado sem verificação, e o índice do utilizador nunca é lido nem mexido durante o planeamento.
* **Destrutivo para o Índice, Não para a Árvore:** O `--apply` reinicia o índice para o HEAD antes de indexar, pelo que o que o utilizador já tinha no índice (*staging*) fica por indexar. O conteúdo dos ficheiros é preservado, mas a indexação tem de ser refeita — documentado explicitamente.
* **Configuração:** `GITPR_SPLIT_MAX_GROUPS` (predefinição 5), `GITPR_SPLIT_MAX_HUNKS` (predefinição 50) e `GITPR_SPLIT_REQUIRE_CONFIRMATION` (predefinição `true`). Valores zero ou negativos são **ignorados a favor da predefinição**, em vez de desligarem um teto em silêncio.
* **i18n:** +50 chaves e um bump para **v0.0.31**.
* **Testes:** 6 ficheiros em `tests/split/` (**101 cenários**) a correr contra repositórios descartáveis reais, com a rede bloqueada.
* **Artefactos:** `docs/split-command.md` + `.pt_br.md` (os restantes 3 idiomas estão pendentes), ADR-006 (`split-apply-safety`), `glossary-gitpr-split.md`, spec/plano em `docs/plans/` e um questionário.

### **31. Deteção de Segredos Embutida (`src/security_ruleset.py`) 🆕**

* **O que É:** Sete regras que correm em **todas** as invocações do linter, fundidas no fim do `load_linter_rules()` depois das regras do projeto e dos plugins, que ficam intactas.
* **As Sete Regras:** cinco `error` bloqueantes — ID de chave de acesso da AWS, token do GitHub, token do Slack, chave de API da Google e bloco de chave privada (`-----BEGIN … PRIVATE KEY-----`) — e dois `warning` que reportam sem bloquear: um URL de ligação a base de dados com credenciais e uma atribuição genérica de credencial (`password = "…"`), esta última atrás de um filtro de marcadores (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Porque no Pacote e Não no Modelo:** O catálogo local era inteiramente gerido pelo utilizador — podia ser substituído pelo descarregamento, reescrito pelo assistente (perdendo comentários) ou ampliado apenas por plugins locais à máquina. Uma barreira de segredos tem de se comportar **da mesma maneira** em todas as máquinas e em todas as CI, por isso as regras vivem no pacote e não podem ser substituídas pelo `--skill` nem reescritas pelo assistente.
* **Lacunas de Cobertura Conhecidas (v1), declaradas:** uma atribuição **sem aspas** não é apanhada (`API_KEY=abc123` — o formato `.env`, que é exatamente onde os segredos escapam); faltam os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-`; e a regra genérica não tem fronteira à esquerda no nome da chave, pelo que `mytoken` corresponde como `token`.
* **Configuração:** `GITPR_LINTER_SECURITY` (predefinição `true`; opt-out fail-open — só `false`/`0`/`no`/`off`/`n` o desliga) e `GITPR_LINTER_SECURITY_DISABLED_RULES` (separadas por `;`).
* **Testes:** `tests/test_security_ruleset.py` (**61 cenários**) — compilação das regex, deteção positiva/negativa, filtro de marcadores, encaminhamento por nível, aplicação de wildcards, fusão e completude das traduções.
* **Artefactos:** ADR-007 (`secret-ruleset-location-and-severity`), `glossary-gitpr-secret-scanning.md`, spec/plano/questionário e 3 relatórios de tarefa.

### **32. Bridges SAST — Semgrep, Gitleaks e Bandit (`src/infrastructure/linter/external/`) 🆕**

* **O que É:** Uma camada **opt-in** que liga scanners de segurança de terceiros ao pipeline de linter existente, elevando o piso de todos os reviews sem custo para quem não precisa dela.
* **Âmbito Delimitado:** Correm **apenas** quando estão ativadas e **apenas** sobre os ficheiros tocados pelo diff.
* **Contrato Comum:** `ExternalLinterBridge` (ABC) com execução endurecida de `subprocess` (`shell=False`, timeout estrito, `stdin=DEVNULL`, UTF-8 com `errors='replace'`) e bridges concretas para o Semgrep, o Gitleaks e o Bandit. O modelo `NormalizedFinding` / `ExternalLinterResult` faz com que todas as ferramentas produzam apontamentos na mesma severidade (`error`/`warning`/`info`) e com a mesma forma.
* **A Deduplicação É o Ponto:** O `deduplicate_secret_findings()` funde apontamentos do Gitleaks e do conjunto de regras de segredos no mesmo ficheiro e linha numa **única** entrada confirmada `[Gitleaks + Regex]` — um segredo visto por ambos aparece uma vez, com confirmação de várias fontes, em vez de duas.
* **Segredos Mascarados:** O `mask_secret_value()` (`AKIA****`) garante que o valor nunca chega ao apontamento, ao registo ou à telemetria.
* **Resolução em Três Camadas:** O `load_sast_config()` lê as predefinições → `.gitpr.linter.yml` (bloco `sast`, com recurso a `linter.external`) → variáveis `GITPR_SAST_*`. **Todas com predefinição `false`** — opt-in estrito, para que ninguém veja uma mudança até a pedir.
* **Degradação Graciosa:** Uma ferramenta ativada mas ausente do `PATH` emite `⚠️ SAST tool '{tool}' is enabled in config but was not found in PATH.` e a execução continua.
* **Normalização de Caminhos:** Todas as bridges normalizam as barras invertidas em barras normais e para uma forma relativa ao repositório, para que as chaves de ficheiros modificados do diff coincidam no Windows **e** em Unix.
* **Configuração:** `GITPR_SAST_SEMGREP_ENABLED`, `GITPR_SAST_GITLEAKS_ENABLED`, `GITPR_SAST_BANDIT_ENABLED` + `GITPR_SAST_<TOOL>_TIMEOUT` (60s / 30s / 45s).
* **Dependências:** `semgrep`, `gitleaks` e `bandit` **não** são pacotes Python do projeto — são binários externos que têm de estar no `PATH`.
* **i18n:** +7 chaves (a última adição de chaves desta janela).
* **Testes:** 4 ficheiros em `tests/infrastructure/linter/external/` (**14 cenários**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) — disponibilidade, timeout de subprocesso, binário em falta, análise de JSON, mapeamento de severidade, mascaramento de segredos e salto de ficheiros não-Python.

### **33. Subcomando `gitpr tests generate` — Geração de Suítes por IA (`src/domain/tests_generation/`, `src/application/`) 🆕**

* **O que É:** Gera ficheiros de teste completos e executáveis a partir do diff atual, de um ficheiro específico ou de um apontamento de review, **respeitando a convenção do repositório** (Pest, PHPUnit, Jest, Vitest, Pytest) em vez de impor um estilo.
* **Camada de Domínio:** `TestFramework` (enum) e as dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest` como contrato partilhado; o `detect_test_framework()` deteta a partir de ficheiros de configuração, manifestos de dependências e do conteúdo do diretório de testes, com uma sobreposição explícita e um aviso quando a deteção falha; o `build_test_scaffold()` calcula o caminho de destino convencional por framework (a divisão `Feature`/`Unit` do Laravel, o `tests/**/test_*.py` do Pytest, as convenções `.test`/`.spec` de JS/TS).
* **Camada de Aplicação (`generate_test_file.py`):** Orquestra a deteção da framework, a resolução do scaffold, a construção do prompt, a invocação da IA, a análise do JSON e a escrita opcional. O `validate_test_syntax()` corre a cadeia de ferramentas local (`php -l`, `node --check`, `python -m py_compile`) quando disponível — uma falha de validação é um **aviso**, não um erro.
* **Degradação Graciosa:** Sem chave de API, ou com uma resposta não-JSON do modelo, o resultado é marcado como de baixa confiança em vez de lançar (retira as cercas markdown e continua).
* **Apresentação:** O grupo `tests` com o subcomando `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); o dry run é a predefinição e sobrescrever um teste existente pede confirmação com **Não** pré-selecionado. O chat delega o `/tests` no **mesmo** caso de uso.
* **Testes:** `tests/domain/tests_generation/` (18), `tests/application/use_cases/test_generate_test_file.py` (4) e `tests/test_tests_command.py` (2).
* **Dívida 🆕:** Nenhuma chave de i18n nova foi adicionada — a funcionalidade usa **17 chaves `__()`** que não existem em nenhum dos 6 dicionários (parte das 40 em falta), e o `SKILL_LABELS`/`mcp_server.SKILL_FILES` não recebeu o tipo `tests`. Não existe `docs/tests*.md`.

### **34. Subcomando `gitpr explain` e a Flag `--explain` — Guia do Revisor (`src/domain/pr/`) 🆕**

* **O que É:** Um guia centrado em quem vai **rever** — o que muda, porque muda, onde focar e qual é o risco de regressão — para que ninguém tenha de reconstruir a intenção a partir de um diff em bruto.
* **Camada de Domínio (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, o renderizador `build_explain_markdown()` e o `parse_explain_payload()`, que tolera saída não-JSON ou malformada e **deteta marcadores** `[FILL]`/`[TODO]` para assinalar evidência insuficiente em vez de apresentar um guia oco como completo.
* **Camada de Aplicação (`generate_pr_explanation.py`):** Resolução do provedor, validação da chave, carregamento do contexto de skill (`explain`), construção do prompt, invocação e análise para o modelo de domínio.
* **Duas Portas de Entrada:** O `gitpr explain` (subcomando, com `--provider`, deteção de um diff em falta antes de qualquer IA e saída colorida) e a flag `--explain` na CLI raiz, que anexa o guia ao corpo da descrição do PR **e** à carga JSON emitida, num único `pr_desc_body` reutilizado.
* **Configuração:** `GITPR_EXPLAIN_BY_DEFAULT` (predefinição `false`) — quando `true`, a secção é anexada a **todas** as descrições geradas, o que acrescenta uma chamada de IA e aumenta o custo/tokens por PR.
* **Skill:** `.gitpr.explain.md` registado no `SKILL_FILES_BY_TYPE`, com modelos em 5 idiomas.
* **Testes:** `tests/domain/pr/test_explain_section_builder.py` (4), `tests/application/use_cases/test_generate_pr_explanation.py` (2) e `tests/test_explain_command.py` (2).
* **Dívida 🆕:** O mesmo padrão do `tests` — chaves `__()` sem tradução (parte das 40), em falta no `SKILL_LABELS` e no `mcp_server.SKILL_FILES`, e sem tópico em `docs/`.

### **35. Suíte Determinística e a Primeira CI (`.github/workflows/tests.yml`) 🆕**

* **O que É:** O primeiro workflow que corre a suíte, em **Python 3.10** (o piso declarado no `pyproject.toml`, nunca exercitado) e **3.13** (a versão de desenvolvimento no `Pipfile`), com `fail-fast: false`.
* **O que a CI Tornou Visível:** **22 testes** falhavam numa máquina pt-BR porque afirmam o literal em inglês enquanto o `__()` renderiza português — a suíte só ficava verde com `GITPR_LANG=en_us` na linha de comandos. Foi a motivação direta para endurecer o `conftest.py`.
* **`tests/conftest.py` Hermético 🆕:** Fixa `GITPR_LANG=en_us` (impede a suíte de escrever no perfil real e de renderizar traduções), `GITPR_LINTER_SECURITY=false` (três suítes afirmam a lista de regras que o `load_linter_rules()` real devolve) e o `LANG_VERSION` na versão do código (impede o novo descarregamento de `~/.gitpr/langs/*.json` a cada bump).
* **A Ordem Importa:** O `src.updater` é importado **antes** de as variáveis de idioma serem definidas, porque o `i18n` captura o `os.environ` para `AMBIENT_ENV_KEYS` no import — uma sessão real recebe o `LANG_VERSION` do ficheiro, não da shell.
* **Estabilidade dos Testes com Workers:** O `test_config_app.py` e o `test_metrics.py` passam a esperar por `workers.wait_for_complete()` antes de afirmar.
* **Dependência do Workflow:** O runner limpo não tem `~/.gitpr`, e o `tests/demo/test_demo_isolation.py` afirma que o perfil existe (faz-lhe um snapshot para provar que a visita não escreve nele) — o job cria explicitamente o diretório e o `.env` vazio.

---

## **📊 Testes e Qualidade**

| Ficheiro de Teste | Cenários | Foco |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por intervalo de linhas num ficheiro |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidade, commits, duração |
| `tests/test_changelog_builder.py` | 15 | Builder de changelog: secções, cabeçalhos traduzíveis, contribuidores |
| `tests/test_chat_backend.py` | 19 | Memória do chat, persistência, comandos slash |
| `tests/test_commit_classifier.py` | 23 | Classificação por Conventional Commits (tipos, parser tolerante) |
| `tests/test_config_app.py` | 78 | TUI de configuração: montagem, troca de categorias, controlo de alterações pendentes, F2 bloqueado, Ctrl+R, procura, segredos |
| `tests/test_config_cli.py` | 9 | Registo do subcomando `config`, `-h`, import preguiçoso, stdout limpo |
| `tests/test_config_schema.py` | 42 | Cobertura do `DEFAULT_CONFIG`, ausência de duplicados, categorias/tipos, `advanced` só em Avançado, **secção skills** ⚠️ |
| `tests/test_config_store.py` | 22 | Ida e volta num `.env` temporário, comentários e ordem preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuração dos revisores sugeridos (chaves e predefinições) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, modelos, `validate_ai_key()` com um SDK simulado (401 vs. rede vs. ollama) |
| `tests/test_core.py` | 49 | Fluxos principais, git diff, geração de PR, temporização, staging, coautoria, idioma dos hooks |
| `tests/test_diff_parser.py` | 26 | Parser de diff por linhas/hunks + `summarize_patch()` e `split_patch_sections()` |
| `tests/test_explain_command.py` | 2 🆕 | CLI do `explain`: sucesso, chave em falta, diff vazio |
| `tests/test_external_linters.py` | 33 | Bridge do Checkstyle: parser de XML, subprocesso, referência cruzada com o diff, relatório |
| `tests/test_i18n.py` | 20 | Paridade de idiomas, chaves em falta/órfãs, identidade — **afirmação das 40 chaves em falta** ⚠️ |
| `tests/test_install_wizard.py` | 3 | Assistente de instalação interativo |
| `tests/test_issue_engine.py` | 4 | Rascunho estruturado de issue |
| `tests/test_linter_metrics.py` | 4 | Métricas do linter: erros, avisos, duração |
| `tests/test_linter_presets.py` | 5 | Presets do linter: resolução e novo descarregamento forçado |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` na CLI e na ajuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Modelos de prompt do MCP e recurso de idioma |
| `tests/test_mcp_server.py` | 104 | Tools MCP (14), recursos (18), anotações, patching, CLI direta, offload — **concordância do registo de skills** ⚠️ |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real via subprocesso + stdio JSON-RPC |
| `tests/test_metrics.py` | 34 | Recolha, exportação local, âmbito por repositório, resumo de tokens da cache |
| `tests/test_net_timeouts.py` | 12 | Timeouts de rede/IA — **alinhados com a predefinição real de 180s** ✅ |
| `tests/test_plugins.py` | 17 | Descoberta de plugins, fusão de regras do linter, prompts do MCP |
| `tests/test_pr_publish_app.py` | 45 | TUI do PR Publisher: ecrãs, fluxos, revisores sugeridos, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de erro do linter: abortar, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save e carga JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opções, ajuda com epílogo documentado |
| `tests/test_release_engine.py` | 27 | Motor de release: intervalo de commits, CHANGELOG, publicação |
| `tests/test_reviewer_resolution.py` | 18 | Escada de resolução, rejeição de um nome parcial, dedup, tolerância a falhas |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugestão de revisores (ranking, exclusão, top-N, `last_commit_hash`) |
| `tests/test_security_ruleset.py` | 61 🆕 | Matriz do conjunto de regras embutido: regex, marcadores, nível, wildcard, fusão, traduções |
| `tests/test_skill_command.py` | 10 | Descarregamento e validação de modelos de skill |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` com `quiet=True`, recurso, registo de skills |
| `tests/test_smart_excludes.py` | 15 | Filtro smart de pathspec e novo descarregamento forçado |
| `tests/test_suggest_reviewers.py` | 17 | Revisores sugeridos no fluxo de PR (integração, dica de `no_login`) |
| `tests/test_tests_command.py` | 2 🆕 | CLI do grupo `tests` e do `generate` |
| `tests/test_thinking_words.py` | 5 | Carregamento, análise com o separador `;` e recarregamento forçado |
| `tests/test_updater.py` | 23 | Portão do PyPI: análise de versões, cache diária, fetch, decisões do portão, ligação à CLI |
| `tests/test_usage_log.py` | 27 | Registo de utilização: nome derivado da data, escrita síncrona, silêncio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semântico: major/minor/patch, alvos e validação |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 2 🆕 | Caso de uso do explain: provedor, chave, análise |
| `tests/application/use_cases/test_generate_test_file.py` | 4 🆕 | Caso de uso de geração de testes: dry-run/apply, validação de sintaxe |
| `tests/badge/test_badge_append.py` | 9 🆕 | Anexação idempotente do selo ao corpo do PR |
| `tests/badge/test_badge_builder.py` | 19 🆕 | Composição do Markdown do shields.io, escaping, regra de cor, estilo |
| `tests/badge/test_badge_cli.py` | 15 🆕 | Comando `gitpr badge`: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 🆕 | Contagem de alertas; `None` sem regras configuradas |
| `tests/badge/test_badge_offline.py` | 6 🆕 | Guarda offline (rede proibida, exceto loopback) |
| `tests/badge/test_badge_optout.py` | 17 🆕 | `GITPR_BADGE=false` em todos os caminhos de publicação |
| `tests/badge/test_badge_paths.py` | 10 🆕 | Ponto de injeção único nos dois publicadores (TUI e `--no-edit`) |
| `tests/demo/test_demo_app.py` | 22 🆕 | TUI da visita: navegação, ecrãs, modal de ajuda |
| `tests/demo/test_demo_isolation.py` | 14 🆕 | A visita não toca no `~/.gitpr` nem na rede |
| `tests/demo/test_demo_runner.py` | 33 🆕 | Máquina de estados `DemoState`: avançar, recuar, concluído, modo texto |
| `tests/demo/test_demo_scenarios.py` | 47 🆕 | Integridade dos cenários gravados (aritmética de hunks) nos 5 idiomas |
| `tests/demo/test_demo_text_mode.py` | 18 🆕 | Saída em texto simples (`--no-tui`) para CI e gravações |
| `tests/demo/test_fake_ai_provider.py` | 21 🆕 | Contrato do provedor falso: espelha o `call_ai_model` argumento a argumento |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 🆕 | Formatação uniforme e deduplicação `[Gitleaks + Regex]` |
| `tests/domain/pr/test_explain_section_builder.py` | 4 🆕 | Renderização markdown, análise tolerante, deteção de `[FILL]` |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 🆕 | Deteção por configuração, manifesto e diretório; sobreposição; falha avisada |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 🆕 | Caminho convencional por framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Caso de uso completo: review → IA → validar → classificar → dry-run/aplicar |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extrator partilhado entre o chat e o `fix` (comportamento idêntico) |
| `tests/fix/test_fix_cli.py` | 31 | Encaminhamento do subcomando, opções, dry-run por predefinição, `--force` |
| `tests/fix/test_fix_history.py` | 21 | Registo `.gitpr/fix_history.json`: escrita atómica, leitura |
| `tests/fix/test_fix_settings.py` | 10 | Os cinco `GITPR_FIX_*` e o recurso a predefinições em valores inválidos |
| `tests/fix/test_patch_applier.py` | 19 | Invólucro do `git apply`: check/apply/reverse, ramo, estado |
| `tests/fix/test_patch_extractor.py` | 13 | Blocos cercados → diff unificado validado |
| `tests/fix/test_patch_safety_classifier.py` | 22 | Matriz safe/review_required/experimental e códigos de motivo |
| `tests/fix/test_resolve_last_review.py` | 13 | Seleção do último review, exclusão dos reviews por ficheiro |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback`: reversão e as três recusas |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 🆕 | Bridge do Bandit: disponibilidade, análise, mapeamento |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 🆕 | ABC: subprocesso endurecido, timeout, binário em falta |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 🆕 | Bridge do Gitleaks: mascaramento de segredos, âmbito por ficheiro |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 🆕 | Bridge do Semgrep: análise de JSON, severidade, salto de ficheiros não-Python |
| `tests/review/test_diff_normalizer.py` | 20 | Fins de linha, validação do diff, smart-excludes em Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: proveniência, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orquestração: portões, PR, diff, linter, comentário opcional |
| `tests/review/test_review_pr_cli.py` | 25 | CLI do `review-pr`: opções, rejeições antes da IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 43 | Provedor Azure DevOps: org/projeto, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 39 | Provedor Bitbucket: autenticação Basic, workspace |
| `tests/scm/test_contract.py` | 38 | Contrato `ScmProvider`: assinaturas, dataclasses, erros |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` obsoleto → delega no provedor |
| `tests/scm/test_github_provider.py` | 68 | Provedor GitHub: REST, cabeçalhos, PRs, issues, releases, leitura de revisores |
| `tests/scm/test_gitlab_provider.py` | 47 | Provedor GitLab: API v4, namespace, cabeçalhos sintetizados e `overflow` |
| `tests/scm/test_init_wizard.py` | 10 | Assistente `--init`: deteção da forge, validação, persistência |
| `tests/scm/test_release_publish.py` | 8 | Publicação de release por forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 🆕 | O único módulo que muta: indexação seletiva, um commit por grupo |
| `tests/split/test_generate_split_plan.py` | 12 🆕 | Diff → unidades → grupos → pré-validação de conflitos → mensagens |
| `tests/split/test_hunk_grouper.py` | 18 🆕 | Prompt, orçamento/corte, chamada de IA, validação da resposta |
| `tests/split/test_hunk_parser.py` | 18 🆕 | `parse_units()`/`build_patch()`: texto ↔ modelo, puro |
| `tests/split/test_selective_stager.py` | 11 🆕 | Indexação de subconjuntos, verificação contra o HEAD, índice limpo |
| `tests/split/test_split_cli.py` | 31 🆕 | CLI do split: opções, dry run por predefinição, `--apply` |
| `tests/sync_i18n.py` | — | Script de verificação de cobertura de i18n (scaffold; nunca executado) |

**Total:** **1889 cenários recolhidos em 96 módulos de teste** (44 na raiz + 9 em `tests/scm/` + 10 em `tests/fix/` + **6 em `tests/split/`** 🆕 + **6 em `tests/demo/`** 🆕 + **7 em `tests/badge/`** 🆕 + 4 em `tests/review/` + **4 em `tests/infrastructure/linter/external/`** 🆕 + **2 em `tests/domain/tests_generation/`** 🆕 + **2 em `tests/application/use_cases/`** 🆕 + **1 em `tests/domain/pr/`** 🆕 + **1 em `tests/domain/linter/`** 🆕; **+452** desde o relatório anterior, com **32 ficheiros novos**). Execução completa nesta máquina com `GITPR_LANG=en_us`: **1883 passados / 4 falhados / 2 saltados / 81 subtestes** em ~373s.

**Notas de qualidade desta versão:**
- **✅ As 3 falhas herdadas foram fechadas.** Os dois testes desatualizados em `test_net_timeouts.py` (que afirmavam 600s contra um código que entrega 180s desde a correção `681a7fa`) foram alinhados — o item estava aberto há **três relatórios seguidos**. E a falha sensível ao locale foi resolvida fixando `GITPR_LANG=en_us` no `conftest.py`, o que também eliminou as **22 falhas de locale** que a CI revelou numa máquina pt-BR.
- **⚠️ 4 falhas novas, uma única causa raiz:** as duas funcionalidades mais recentes (`tests` e `explain`) chegaram com o **registo de skills a meio**. São elas:
  1. `test_config_schema.py::TestSkillsSection::test_the_labels_cover_the_registry_exactly` — o `SKILL_LABELS` não tem `tests` nem `explain`; sem rótulo, os dois renderizam **em branco** na barra lateral da TUI de configuração.
  2. `test_config_schema.py::TestSkillsSection::test_the_labels_follow_the_registry_order` — a mesma lacuna vista pela ordenação: o `SKILL_TYPES` tem 10 entradas, o `SKILL_LABELS` tem 8.
  3. `test_i18n.py::TestNoMissingKeys::test_no_missing_keys` — **40 chaves `__()`** usadas no código não existem em **nenhum** dos 6 dicionários (todas dos comandos `tests` e `explain`). Como o inglês é o recurso, renderizam como a própria chave em todos os idiomas.
  4. `test_mcp_server.py::TestSkillRegistryAgreement::test_the_two_skill_registries_agree` — o `mcp_server.SKILL_FILES` e o `config.SKILL_FILES_BY_TYPE` deixaram de concordar; `skill://explain` e `skill://tests` não são expostos pelo MCP.
- **A causa é uma só e é barata de fechar:** registar os dois tipos no `SKILL_LABELS`, no `mcp_server.SKILL_FILES` e correr `python tests/sync_i18n.py` para as 40 chaves. O ponto relevante é que a suíte **detetou-o** — as três afirmações existem exatamente para isso, e a CI corre-as em duas versões de Python.
- **Crescimento de 452 cenários** com a linha de base das falhas herdadas levada a zero — o sinal desta janela: a suíte deixou de carregar falhas conhecidas e passou a reportar dívida nova no mesmo commit em que ela nasce.
- O `tests/conftest.py` tornou-se **hermético**: `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` e `LANG_VERSION` na versão do código — a suíte não escreve no registo de utilização, no `.env` real nem em `~/.gitpr/langs/`.
- **Fixtures de git reais:** o `tests/fix/git_fixture.py` e o `tests/split/git_fixture.py` constroem repositórios reais — o `patch_applier` e o `selective_stager` só são honestos se o `git apply` for o real.
- **Guarda de rede:** o `tests/demo/conftest.py` proíbe a rede por autouse — uma regressão ali envenenaria uma cache real com conteúdo de demonstração.

---

## **🌐 Internacionalização e Documentação**

* **Cobertura de i18n:** **1158 chaves de tradução** nos 6 dicionários, com **paridade total do conjunto de chaves** entre eles (+110 desde o relatório anterior). A cadeia medida por commit foi 1048 → 1080 (`demo`, +32) → 1090 (`badge`, +10) → 1140 (`split`, +50) → 1151 (segredos, +11) → **1158** (SAST, +7). Os dois últimos commits (`tests`, `explain`) **não** adicionaram chaves — usam 40 que não existem. ⚠️ O código usa **1198** chaves.
* **`__lang_version__` foi de v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32**, desencadeando o novo descarregamento OTA das traduções. A deteção de segredos e o SAST adicionaram chaves **sem** mexer no marcador; o bump para v0.0.32 cobre ambos e está **na árvore de trabalho, por consolidar**.
* **Fontes de tradução em passo sincronizado:** uma chave nova tem de existir no código (fonte), em `langs/pt_br.json` (**lista mestra**), nos dicionários FR/ES do `scripts/sync_all_langs.py` (segunda fonte) e nos valores curados do `scripts/fix_mangled_i18n_keys.py` (terceira fonte, lida pelo `tests/test_i18n.py`); a afirmação `len(CLEAN_KEYS)` mantém-se nas 49.
* **Tópicos novos 🆕 (3):**
  - `docs/demo.md` — a visita guiada: o que mostra, o pipeline real com resposta falsa, os cenários e a garantia de isolamento — **em 5 idiomas**
  - `docs/badge.md` — o selo de PR: o que afirma, onde é anexado, quando é omitido e como imprimir o estático para o README — **em 5 idiomas**
  - `docs/split-command.md` — commits atómicos por hunk: o pipeline, a pré-validação, o que o `--apply` faz ao índice — **em EN + PT-BR** (os restantes 3 idiomas estão pendentes)
* **Subdiretório novo 🆕:** `docs/tutorial/` com a família `install-from-source.*` **em 5 idiomas** (instalação a partir do código-fonte).
* **Tópicos atualizados nesta janela:** `docs/linter-regras-customizadas.*` (5 — o campo `level`, o conjunto de regras embutido e as duas válvulas de escape), `docs/git-hooks-locais.*` (5 — o que o hook pre-commit passa a bloquear e como contornar), `docs/auto-update.*` (5), `docs/ARCHITECTURE.md`, mais o `README.md` e as suas 4 traduções (índice com as famílias `demo`, `badge`, `split-command`, `fix-command` e `review-pr`).
* **Documentação em 5 idiomas:** **44 tópicos canónicos** em `docs/` (+3) — **37 com cobertura total nos 5 idiomas** (+2) e **7 tópicos parciais/só em PT** (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers`, `review-pr` com 2 idiomas e, agora, `split-command` com 2).
* **Lacuna de documentação 🆕:** O `explain` e o `tests` são as duas primeiras funcionalidades em várias janelas a chegar **sem tópico em `docs/`** — só têm os modelos de skill.
* **Skills locais do Claude Code:** `.claude/skills/` com **29 skills** (contagem inalterada nesta janela).
* **Índice de Memória:** `.claude/memory/MEMORY.md` com **41 padrões** (+1 nesta janela).
* **Relatórios de tarefa:** `docs/claude-code/reports/develop_natan/` (**101** no total; **+8** na janela) e `docs/gemini/reports/develop_natan/` (**7**; **+2** — `2026-09-21_skill_gitpr_sast_bridge.md` e `2026-09-22_skill_gitpr_tests_generate.md`).
* **Relatórios de estado:** `docs/reports/` (15 relatórios; este é o 16.º).
* **Planos de desenvolvimento:** 115 ficheiros em `docs/plans/` (+16 na janela — specs/planos de `demo`, `badge`, `split`, segredos, SAST, `tests` e `explain`, ADR-006 e ADR-007, os glossários `glossary-gitpr-split` e `glossary-gitpr-secret-scanning`) + **10 ficheiros em `docs/survey/`** (+4).

---

## **🔄 Pipeline de Distribuição**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Atualização obrigatória:** a execução verifica o PyPI no arranque e **bloqueia com código de saída 1** se houver uma versão mais recente, imprimindo `pip install --upgrade gitpr-cli`; a verificação é guardada em cache por dia e o `--update` apenas reporta
3. **GitHub Releases:** removido — sem PyInstaller, sem asset `.exe`, sem hot-swap
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala via pip) + **`tests.yml`** 🆕 (matriz Python 3.10 e 3.13, com criação explícita do perfil `~/.gitpr` que o `test_demo_isolation.py` afirma existir)
5. **Servidor MCP:** ponto de entrada `gitpr-mcp` via `pyproject.toml`
6. **Modelos e idiomas OTA:** `templates/` e `langs/*.json` servidos a partir do GitHub (main) — o bump para `v0.0.32` renova as cópias locais em `~/.gitpr/langs/` depois de publicado
7. **Versão derivada do código 🆕:** o `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — o `__version__` é a **única** fonte, e é por isso que um bump por consolidar deixa o pacote construído em 1.2.0 enquanto o `CHANGELOG.md` já anuncia 1.3.0
8. **Estado da release 1.3.0 🆕:** a **tag `v1.2.0` foi criada** (merge do PR #174, 2026-09-17), fechando o bloqueio da janela anterior. O `__version__` (1.3.0) e o `__lang_version__` (v0.0.32) estão **na árvore de trabalho e por consolidar** (HEAD em 1.2.0 / v0.0.31); o `CHANGELOG.md` tem a entrada `[1.3.0]` consolidada, mas **incompleta** — cobre apenas a deteção de segredos. O caminho é estender o changelog com as 6 funcionalidades restantes, correr o `gitpr release`, consolidar o bump e criar a tag.

---

## **📈 Evolução Desde o Relatório Anterior (v0.0.15)**

| Área | v0.0.15 (anterior) | v0.0.16 (atual) |
|------|-------------------|-----------------|
| **Versão do GitPR** | 1.2.0 (bump **não consolidado**; HEAD em 1.1.0) | **1.3.0** (bump **não consolidado**; HEAD em 1.2.0) — **tag `v1.2.0` criada** ✅ |
| **Versão de Idioma** | v0.0.28 | **v0.0.32** (via v0.0.29, v0.0.30 e v0.0.31; bump **não consolidado** — HEAD em v0.0.31) |
| **Versão dos Scripts de Hook** | v0.0.3 | **v0.0.3** |
| **Provedores de IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 idiomas, 6 dicionários | 5 idiomas, 6 dicionários |
| **Subcomandos** | 4 (`release`, `config`, `fix`, `review-pr`) | **9** (+ `demo`, `badge`, `split`, `tests`, `explain`) |
| **Interface** | CLI + TUIs + assistentes `--init`/`--install` + 4 subcomandos | **+ `gitpr demo` (TUI + modo texto) + `gitpr badge` + `gitpr split` + `gitpr tests generate` + `gitpr explain` + flag `--explain`** |
| **Camadas** | `src/infrastructure/` (SCM) | **+ `src/domain/` e `src/application/use_cases/` (arquitetura em camadas)** 🆕 |
| **Tools MCP** | 14 tools / 18 recursos / 7 prompts | **14 tools / 18 recursos / 7 prompts** (inalterado — `skill://explain` e `skill://tests` **em falta** ⚠️) |
| **Flags da CLI** | 35 na raiz + `release` (6) + `config` (0) + `fix` (8) + `review-pr` (2) | **36 na raiz** (+`--explain`) + `release` (6) + `config` (0) + `fix` (8) + `review-pr` (2) + **`demo` (3) + `badge` (2) + `split` (5) + `tests generate` (5) + `explain` (1)** |
| **Variáveis de Ambiente** | 44 chaves em `DEFAULT_CONFIG` | **49 chaves** (+5: 2 de segurança + 3 do split) — **+5 apenas de leitura fora do `DEFAULT_CONFIG`** (`GITPR_BADGE`, `GITPR_EXPLAIN_BY_DEFAULT`, 3× `GITPR_SAST_*_ENABLED`) |
| **Schema de Configuração** | 61 `ConfigField` / 13 categorias | **71 `ConfigField` (+10) / 14 categorias** (+ Split) |
| **Linter** | Regex + bridge Checkstyle | **+ conjunto de regras de segredos embutido (7 regras) + wildcard `extensions: ["*"]` + 3 bridges SAST opt-in (Semgrep, Gitleaks, Bandit) com dedup multi-fonte** |
| **Git Hooks** | `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG`, `effective_hook_lang()` | Inalterado (mas o pre-commit passa a **bloquear segredos** por predefinição) |
| **Camada SCM** | 4 forges, `get_pull_request`, `supports_reviewable_diff` | Inalterada nesta janela |
| **i18n (chaves por ficheiro)** | 1048 × 6 (paridade total) | **1158 × 6 (paridade total) — +110**, mas o código usa **1198** → **40 chaves sem tradução** ⚠️ |
| **Documentação** | 41 tópicos canónicos (35 completos + 6 parciais) | **44 tópicos canónicos (37 completos + 7 parciais) — 3 famílias novas + `docs/tutorial/`** |
| **CI** | Sem workflow de testes | **`.github/workflows/tests.yml`** (Python 3.10 + 3.13) 🆕 |
| **Suíte de Testes** | 1437 cenários (64 ficheiros) | **1889 cenários (96 ficheiros) — +452 cenários, +32 ficheiros; en_us: 1883 passados / 4 falhados / 2 saltados** |
| **Falhas herdadas** | 3 (2 de timeout + 1 de locale) | **0 — todas fechadas** ✅ (4 novas, uma causa raiz) |
| **Commits desde o relatório** | 5 commits | **8 commits** (`8920d13`, `da162d0`, `69f41ec`, `c38aed2`, `47784a7`, `a799664`, `7d84daf`, `6138cf1`) |
| **PRs integrados** | 3 PRs (#167, #171, #173) | **7 PRs (#177, #179, #181, #183, #185, #187, #189)** |
| **Índice de Memória** | 40 padrões | **41 padrões** |
| **Relatórios de tarefa** | 93 claude-code, 5 gemini | **101 claude-code (+8) e 7 gemini (+2)** |
| **Planos de desenvolvimento** | 99 planos, 6 questionários | **115 planos (+16), 10 questionários (+4)** |

---

## **🚧 Próximos Passos**

* **Fechar a dívida das duas funcionalidades novas 🆕:** registar `tests` e `explain` no `SKILL_LABELS` (`src/config_schema.py`) e no `mcp_server.SKILL_FILES`, e correr `python tests/sync_i18n.py` para as **40 chaves** em falta. São **4 testes vermelhos** com uma única causa raiz — é o item mais barato desta lista e o único que hoje deixa a suíte não-verde.
* **Fechar a release 1.3.0 🆕:** o `__version__` (1.3.0) e o `__lang_version__` (v0.0.32) estão na árvore de trabalho sem commit. Além disso, a entrada `[1.3.0]` no `CHANGELOG.md` **cobre apenas a deteção de segredos** — `demo`, `badge`, `split`, SAST, `tests` e `explain` têm de entrar nela antes de criar a tag.
* **Documentar o `explain` e o `tests` 🆕:** são as duas primeiras funcionalidades em várias janelas a chegar sem tópico em `docs/` — só existem os modelos de skill e as specs em `docs/plans/`.
* **Traduzir o que ficou parcial:** o `docs/split-command.*.md` existe apenas em EN e PT-BR (faltam pt_pt, es_es, fr_fr), tal como o `docs/review-pr.*.md`.
* **Alargar a cobertura do conjunto de regras de segredos 🆕:** as lacunas estão declaradas no próprio changelog — atribuição **sem aspas** (`API_KEY=abc123`, o formato `.env`, que é precisamente onde os segredos escapam), os prefixos `ASIA…`, `github_pat_…` e `xoxc-`/`xoxd-`, e a ausência de fronteira à esquerda no nome da chave (hoje `mytoken` corresponde como `token`).
* **Documentar a quebra de contrato do `request_pull_request_reviewers`:** passou de `None` para `list[str]`; subclasses de `ScmProvider` de terceiros têm de ser atualizadas. O `docs/scm-multiforge.*.md` é o sítio, nas 5 versões. **Item herdado, ainda aberto.**
* **`.gitpr/metrics/export/` no `.gitignore`:** mais quatro pares CSV/JSON entraram rastreados nesta janela (`2026-09-18`, `19`, `21` e `22`). São artefactos gerados localmente. **A dívida cresceu.**
* **Provedor Anthropic Claude:** Suporte direto à API do Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual no Dashboard:** Histogramas temporais e tendências de tokens na TUI de métricas.
* **Pipeline de Release em GitHub Actions:** Automatização da construção e do envio para o PyPI (a CI de testes já existe; falta a de release).
* **Semente local `.gitpr/conf/`:** A sementeira de modelos de configuração local (smart-excludes, linter) continua pendente como subcomando próprio ou passo do assistente.
* **Mais provedores:** OpenAI direto, provedores locais adicionais.
* **Extrator de i18n do `sync_i18n.py`:** A regex trunca literais com concatenação implícita (`__("a " "b")`) — migrar para AST.
* **Reconciliar a versão do projeto:** O `CLAUDE.md` ainda diz `Current version: 0.0.37` enquanto o `__version__` está em 1.3.0. Definir uma convenção única. **Item herdado, ainda aberto.**
* **Dívida do índice do README:** as famílias `suggested-reviewers`, `scm-multiforge`, `config-tui` e `usage-log` continuam fora do índice (as desta janela — `demo`, `badge`, `split-command` — entraram).
* **`gitpr -h config` ignora o `-h`:** o subcomando abre a TUI em vez de mostrar a ajuda — o gate `if ctx.invoked_subcommand is not None: return` corre antes do bloco do `help_flag`. Corrigi-lo mudaria o comportamento do `-h` em **todos** os subcomandos, por isso precisa de uma decisão.
* **Secção Smart Exclude na TUI:** dos 12 itens reportados depois de usar o ecrã, o item 10 (*Smart Exclude*) é o único entregável ainda não iniciado.
* **Dívidas registadas no plano da TUI de configuração:** o `DEFAULT_CONFIG` ficou redundante com o schema; o banner de abertura não lista `--dashboard`, `--init`, `--base` nem `--plugins`; a `LinterApp` não desativa a paleta de comandos.

### ✅ Concluídos nesta janela (2026-09-17 → 2026-09-23)

* ~~**Alinhar os testes de timeout desatualizados**~~ — o `tests/test_net_timeouts.py` afirma agora os 180s reais (`test_ai_timeout_defaults_to_180`) e a docstring do `get_ai_timeout()` foi corrigida. **Um item que estava aberto há três relatórios.**
* ~~**Robustez de locale nos testes**~~ — o `tests/conftest.py` fixa `GITPR_LANG=en_us`; as **22 falhas de locale** que a CI revelou numa máquina pt-BR também desapareceram.
* ~~**Fechar a release 1.2.0**~~ — tag `v1.2.0` criada (merge do PR #174) e a entrada `[1.2.0] - 2026-09-17` está no `CHANGELOG.md`. **Era o item que bloqueava a publicação.**
* ~~**Subcomando `gitpr demo`**~~ — pacote `src/demo/` + TUI `src/ui/demo/`, pipeline real com provedor falso, 2 cenários em 5 idiomas e uma guarda de isolamento (PR #177).
* ~~**Selo GitPR e comando `gitpr badge`**~~ — `src/branding/`, medição honesta, ponto de injeção único, opt-out `GITPR_BADGE` e 7 ficheiros de teste (PR #179).
* ~~**Subcomando `gitpr split`**~~ — pacote `src/split/` (6 ficheiros), `src/infrastructure/git/` com o `selective_stager`, pré-validação contra um índice temporário (PR #181).
* ~~**Deteção de segredos embutida**~~ — `src/security_ruleset.py` (7 regras), o wildcard `extensions: ["*"]`, duas variáveis de opt-out e o alerta que nunca ecoa o valor (PR #183).
* ~~**Bridges SAST Semgrep/Gitleaks/Bandit**~~ — `src/infrastructure/linter/external/`, dedup `[Gitleaks + Regex]`, mascaramento de segredos e opt-in estrito (PR #185).
* ~~**Subcomando `gitpr tests generate`**~~ — `src/domain/tests_generation/` + `src/application/use_cases/`, deteção de framework, validação de sintaxe e o chat a delegar no mesmo caso de uso (PR #187).
* ~~**Subcomando `gitpr explain` e flag `--explain`**~~ — `src/domain/pr/explain_section_builder.py` + `generate_pr_explanation.py`, análise tolerante com deteção de `[FILL]` (PR #189).
* ~~**Primeira CI para a suíte**~~ — `.github/workflows/tests.yml` em Python 3.10 e 3.13, mais o `conftest.py` hermético e a estabilização dos testes com workers.
* ~~**Higiene de i18n na primeira execução**~~ — o `i18n.py` cria o diretório do perfil antes de persistir o idioma detetado.
* ~~**`patch_applier` partilhado**~~ — promovido para `src/infrastructure/git/`, com o `src/fix/patch_applier.py` mantido como shim de re-exportação (nada quebrou).

---

**Relatório gerado em:** 2026-09-23  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
