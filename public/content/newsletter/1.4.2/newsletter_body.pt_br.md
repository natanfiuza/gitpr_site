# GitPR 1.4.2 — Novidades

## Novidades desta versão

- **Dois releases fechados — 1.4.1 e 1.4.2:** o v0.0.18 deixou o 1.4.1 "preparado, mas ainda não fechado". Esta janela fechou **os dois**: a tag `v1.4.1` foi criada em 2026-10-18 (PR #218) e a `v1.4.2` em 2026-10-27 (PR #222), com o `CHANGELOG.md` commitado nas duas. O PyPI já serve **1.4.2**; `v1.4.0` deixou de ser a última tag.
- **O diff sumia em repositório com conteúdo legado:** `get_git_full_diff()` decodificava a saída do `git diff` como UTF-8 estrito. Um único byte Latin-1 (`0xFA`) matava a thread de leitura do subprocesso, `stdout` voltava `None` **sem exceção** e o CLI anunciava "No new code found" — como se a branch não tivesse mudanças. Todas as **10** chamadas de `subprocess` que capturam saída do git passaram a decodificar com `errors="replace"`, e o `None` devolvido pelo git agora **encerra com código 1** em PR, review, full review e commit. Diff vazio de verdade continua saindo **0**, e o `--hook` falha **aberto**, porque um `prepare-commit-msg` não-zero abortaria o commit do usuário.
- **`explain` e `tests generate` finalmente documentados — e consertados no caminho:** eram os **dois últimos** recursos sem tópico em `docs/`, herdados como dívida há várias janelas. Ganharam **5 idiomas cada** (`docs/explain-pr.*` e `docs/tests-generate.*`, 10 arquivos). Documentar revelou **quatro defeitos reais**: `parse_explain_payload()` quebrava com o dict já parseado do provider (`json.loads(dict)` → `TypeError`); `generate_test_file` tinha o mesmo descasamento; `gitpr tests generate --finding` lia `cand.finding.suggested_test` e `cand.diff`, atributos que não existem; e `gitpr -h --explain` estava quebrado por um dest mismatch (`explain_flag` × `explain`).
- **READMEs apontam para o site oficial da documentação:** **342** destinos de documentação nos 5 READMEs deixaram `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/*.md` e passaram a `https://gitpr.natanfiuza.dev.br/docs/*`, com o idioma no `?lang=` e o inglês **sem** parâmetro — exatamente o formato que `src/doc_links.py` já canoniza. **217 URLs únicas** verificadas com `curl`: **0 falhas**.
- **A dívida de i18n não foi paga:** os dicionários seguem em **1620 chaves** (×4) e **1618** (`es.json`, `fr.json`), com **8 chaves usadas no código sem tradução** e **2 órfãs** — os mesmos números do relatório anterior. Somadas às **7 falhas de fixture** de `tests/badge/test_badge_paths.py` e às **2 falhas conhecidas** de `tests/test_mentor_cli.py`, a suíte fecha em **2950 aprovados de 2964** (12 falhas, 2 skips).

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

As duas últimas funcionalidades sem documentação agora têm um guia cada:

```
gitpr explain                                    # guia do revisor para o diff atual
gitpr tests generate --file src/app.py           # dry run: mostra a suíte sem escrever nada
gitpr tests generate --file src/app.py --apply   # escreve o arquivo de teste após confirmação
```

O `gitpr -h --explain` voltou a funcionar e abre o guia do revisor, e tanto `gitpr tests` quanto `gitpr tests generate` agora trazem um link para o próprio guia no epílogo da ajuda. Tudo publicado nos cinco idiomas em https://gitpr.natanfiuza.dev.br/docs — e os READMEs agora apontam para lá, com o idioma em `?lang=`.

Se o seu repositório tem arquivos em codificação legada, atualize: a 1.4.2 decodifica a saída do git com `errors="replace"` e sai com código 1 quando o git falha, em vez de anunciar "Nenhum código novo encontrado" numa branch que tem mudanças.

## Dicas úteis

O Smart Excludes filtra lockfiles, arquivos minificados e gerados do diff com a sintaxe nativa de pathspec do Git, antes de a IA ver qualquer coisa — o exemplo da documentação reduz ~15.000 tokens para ~250 (98% a menos), e a lista de arquivos alterados continua sendo injetada como metadado.
