# GitPR 1.4.1 — Novidades

## Novidades desta versão

- **`gitpr check` — um único portão para o CI:** o linter, a varredura de segredos, as bridges SAST, a pontuação de risco, o baseline e o review por IA opcional passam a correr atrás de **um único comando**, com artefactos **SARIF 2.1.0**, **JSON** ou **JUnit XML** e um contrato de saída determinístico (`0` sucesso, `1` apontamento bloqueante, `2` erro de execução). O `action.yml` foi reescrito para chamar este comando, a chave de API deixou de ser obrigatória e há um caminho `--no-ia` de custo zero.
- **Extensão VS Code e Cursor — o editor tornou-se um cliente MCP:** oito comandos (`commit`, `review`, `fullReview`, `reviewMentor`, `generatePr`, `generatePrExplain`, `publishPr`, `check`) falam com o `gitpr-mcp` via stdio e **nunca duplicam lógica de negócio** — chaves, tokens e prompts ficam do lado da CLI. A extensão publica PRs em dois passos, preenche o painel **Problems** com os apontamentos do `run_check` e mascara segredos dos dois lados da fronteira.
- **Duas novas tools MCP e um caso de uso partilhado:** a `run_check` (apenas de leitura, `no_ia=true` por predefinição) e a `publish_pull_request` (nunca faz commit nem push) entraram no servidor, que passou de **16 para 18 tools**. A publicação `--no-edit` deixou de ter código próprio: a CLI e o MCP passam pelo mesmo `publish_pull_request_use_case`, com os estados `success`/`needs_push`/`already_exists`/`auth_required`/`error`.
- **A skill de PR deixou de propagar um artefacto colado por acidente:** um fragmento JSON (`".gitpr.commit.md": "gitpr.commit.md",`) acabou na regra de estrutura do template `pt_br` e a IA repetia-o em **todos** os PRs gerados. Como o `--skill` nunca sobrescreve um ficheiro existente, corrigir o template não bastava: o `get_skill_context()` passou a **remover o fragmento em memória** (sem tocar no ficheiro do utilizador), e um novo teste varre **todos os templates publicados** para evitar a reincidência.
- **O merge de PR ganhou feedback visual:** a chamada à forge corria em silêncio numa thread daemon e uma exceção inesperada **matava a thread sem aviso**, deixando a TUI bloqueada. O modal de progresso existente foi generalizado (`title` opcional, `mark_finished(show_close=False)`), passou a ser empilhado antes de o worker arrancar e cobre agora **sucesso, conflito e erro inesperado**, com dispensa automática após 1.2 s no caminho de sucesso.
- **A dívida de i18n voltou a abrir — e a suíte reporta-a:** **8 chaves `__()` usadas no código não existem em nenhum dicionário** (publicação de PR e descrições das tools MCP) e **2 ficaram órfãs**; o `es.json` e o `fr.json` ficaram em **1618** chaves contra 1620 nos outros quatro. Somadas às **7 falhas em `tests/badge/test_badge_paths.py`** — o provedor falso dos testes não tem `.token`, e o caso de uso partilhado passou a exigi-lo — e às **2 falhas conhecidas** em `tests/test_mentor_cli.py`, a suíte fecha em **2938 passados de 2952** (12 falhas, 2 skips).
- **A release 1.4.1 está preparada, mas ainda não fechada:** o `__version__` subiu para **1.4.1** (commit `46c25c1`) e o `__lang_version__` para **v0.0.35**; o `CHANGELOG.md` já tem a secção `[1.4.1]` gerada pelo motor de release — **na árvore de trabalho**, e a tag `v1.4.1` ainda **não existe**.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

A release gira em torno de um único comando de CI:

```
gitpr check                                      # linter, segredos, SAST, risco e baseline — mais review de IA opcional
gitpr check --format sarif --output gitpr.sarif  # artefacto SARIF 2.1.0 para GitHub Code Scanning
gitpr check --no-ia                              # caminho de custo zero: sem chamada de IA e sem chave de API
gitpr check --fail-on warning                    # faz apontamentos de nível warning bloquearem o build
```

O código de saída é o contrato: `0` sucesso, `1` apontamento bloqueante, `2` erro de execução. O artefacto também pode sair em `json` ou `junit`.

Para VS Code e Cursor, instale a extensão `natanfiuza.gitpr` no Marketplace (ou Open VSX) e execute os comandos pela Command Palette: Commit, Review, Full Review, Review with Mentor, Generate PR, Generate PR with Reviewer Guide, Publish PR e Check. O `GitPR: Check` preenche o painel Problems com diagnósticos clicáveis; chaves e tokens continuam em `~/.gitpr/.env`.

## Dicas úteis

Diffs maiores do que ~90 mil tokens (estimativa de 4 caracteres por token) são tratados automaticamente: o GitPR divide-os em lotes nas fronteiras de ficheiro, resume cada parte (Map) e unifica tudo numa chamada final (Reduce) — sem precisar de qualquer flag.
