# GitPR 1.4.1 — Novidades

## Novidades desta versão

- **`gitpr check` — um único portão para a CI:** linter, varredura de segredos, bridges SAST, score de risco, baseline e review de IA opcional passaram a rodar atrás de **um comando só**, com artefatos **SARIF 2.1.0**, **JSON** ou **JUnit XML** e contrato de saída determinístico (`0` sucesso, `1` achado bloqueante, `2` erro de execução). O `action.yml` foi reescrito para chamar esse comando, a API key deixou de ser obrigatória e existe um caminho `--no-ia` de custo zero.
- **Extensão para VS Code e Cursor — o editor virou cliente do MCP:** oito comandos (`commit`, `review`, `fullReview`, `reviewMentor`, `generatePr`, `generatePrExplain`, `publishPr`, `check`) falam com o `gitpr-mcp` por stdio e **nunca duplicam lógica de negócio** — chaves, tokens e prompts continuam do lado do CLI. A extensão publica PRs em duas etapas, popula o painel **Problems** com os achados do `run_check` e mascara segredos dos dois lados da fronteira.
- **Dois tools MCP novos e um caso de uso compartilhado:** `run_check` (somente leitura, `no_ia=true` por default) e `publish_pull_request` (nunca commita nem faz push) entraram no servidor, que passou de **16 para 18 tools**. A publicação do `--no-edit` deixou de ter código próprio: CLI e MCP passam pelo mesmo `publish_pull_request_use_case`, com os status `success`/`needs_push`/`already_exists`/`auth_required`/`error`.
- **O skill de PR deixou de propagar um artefato colado por engano:** um fragmento JSON (`".gitpr.commit.md": "gitpr.commit.md",`) foi parar na regra de estrutura do template `pt_br` e a IA o ecoava em **todos** os PRs gerados. Como o `--skill` nunca sobrescreve um arquivo existente, corrigir o template não bastava: `get_skill_context()` passou a **remover o fragmento em memória** (sem tocar no arquivo do usuário), e um teste novo varre **todo template publicado** para impedir a reincidência.
- **O merge de PR ganhou feedback visual:** a chamada à forge corria silenciosa numa thread daemon e uma exceção inesperada **matava a thread sem avisar**, deixando a TUI travada. O modal de progresso existente foi generalizado (`title` opcional, `mark_finished(show_close=False)`), passou a ser empilhado antes do worker começar e a cobrir **sucesso, conflito e erro inesperado**, com auto-dismiss de 1,2 s no caminho feliz.
- **A dívida de i18n reabriu — e a suíte acusa:** **8 chaves `__()` usadas no código não existem em nenhum dicionário** (publicação de PR e descrições de tools MCP) e **2 ficaram órfãs**; `es.json` e `fr.json` ficaram em **1618** chaves contra 1620 dos outros quatro. Somadas às **7 falhas de `tests/badge/test_badge_paths.py`** — o provider falso dos testes não tem `.token`, e o caso de uso compartilhado agora exige um — e às **2 falhas conhecidas** de `tests/test_mentor_cli.py`, a suíte fecha em **2938 aprovados de 2952** (12 falhas, 2 skips).
- **O release 1.4.1 está preparado, mas ainda não fechado:** o `__version__` subiu para **1.4.1** (commit `46c25c1`) e o `__lang_version__` para **v0.0.35**; o `CHANGELOG.md` já tem a seção `[1.4.1]` gerada pelo motor de release — **no working tree**, e a tag `v1.4.1` ainda **não existe**.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

O release gira em torno de um único comando de CI:

```
gitpr check                                      # linter, segredos, SAST, risco e baseline — mais review de IA opcional
gitpr check --format sarif --output gitpr.sarif  # artefato SARIF 2.1.0 para GitHub Code Scanning
gitpr check --no-ia                              # caminho de custo zero: sem chamada de IA e sem API key
gitpr check --fail-on warning                    # faz achados de nível warning bloquearem o build
```

O código de saída é o contrato: `0` sucesso, `1` achado bloqueante, `2` erro de execução. O artefato também pode sair em `json` ou `junit`.

Para VS Code e Cursor, instale a extensão `natanfiuza.gitpr` no Marketplace (ou Open VSX) e rode os comandos pela Command Palette: Commit, Review, Full Review, Review with Mentor, Generate PR, Generate PR with Reviewer Guide, Publish PR e Check. O `GitPR: Check` preenche o painel Problems com diagnósticos clicáveis; chaves e tokens continuam em `~/.gitpr/.env`.

## Dicas úteis

Diffs maiores que ~90 mil tokens (estimativa de 4 caracteres por token) são tratados automaticamente: o GitPR divide em lotes nas fronteiras de arquivo, resume cada parte (Map) e unifica tudo numa chamada final (Reduce) — sem precisar de nenhuma flag.
