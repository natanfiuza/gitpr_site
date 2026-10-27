# GitPR 1.4.2 — Novidades

## Novidades desta versão

- **Dois releases fechados — 1.4.1 e 1.4.2:** a v0.0.18 deixou a 1.4.1 "preparada, mas ainda não fechada". Esta janela fechou **as duas**: a tag `v1.4.1` foi criada a 2026-10-18 (PR #218) e a `v1.4.2` a 2026-10-27 (PR #222), com o `CHANGELOG.md` commitado em ambas. O PyPI já serve a **1.4.2**; a `v1.4.0` deixou de ser a última tag.
- **O diff desaparecia em repositórios com conteúdo legado:** o `get_git_full_diff()` descodificava a saída do `git diff` como UTF-8 estrito. Um único byte Latin-1 (`0xFA`) matava a thread leitora do subprocesso, o `stdout` voltava a `None` **sem exceção** e a CLI anunciava "Nenhum código novo encontrado" — como se o ramo não tivesse alterações. As **10** chamadas `subprocess` que capturam saída do git passam a descodificar com `errors="replace"`, e um `None` devolvido pelo git agora **sai com código 1** em PR, review, full review e commit. Um diff genuinamente vazio continua a sair com **0**, e o `--hook` falha **aberto**, porque um `prepare-commit-msg` não-zero abortaria o commit do utilizador.
- **O `explain` e o `tests generate` finalmente documentados — e corrigidos pelo caminho:** eram as **duas últimas** funcionalidades sem tópico em `docs/`, herdadas como dívida ao longo de várias janelas. Ganharam **5 idiomas cada** (`docs/explain-pr.*` e `docs/tests-generate.*`, 10 ficheiros). Documentar revelou **quatro defeitos reais**: o `parse_explain_payload()` rebentava com o dict já interpretado do provedor (`json.loads(dict)` → `TypeError`); o `generate_test_file` tinha a mesma incompatibilidade; o `gitpr tests generate --finding` lia `cand.finding.suggested_test` e `cand.diff`, atributos que não existem; e o `gitpr -h --explain` estava quebrado por uma divergência de `dest` (`explain_flag` × `explain`).
- **Os READMEs apontam para o site oficial de documentação:** **342** destinos de documentação nos 5 READMEs deixaram `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/*.md` e apontam agora para `https://gitpr.natanfiuza.dev.br/docs/*`, com o idioma em `?lang=` e o inglês **sem** parâmetro — exatamente o formato que o `src/doc_links.py` já canoniza. **217 URLs únicas** verificadas com `curl`: **0 falhas**.
- **A dívida de i18n não foi paga:** os dicionários continuam com **1620 chaves** (×4) e **1618** (`es.json`, `fr.json`), com **8 chaves usadas no código sem tradução** e **2 órfãs** — os mesmos números do relatório anterior. Somadas às **7 falhas de fixture** em `tests/badge/test_badge_paths.py` e às **2 falhas conhecidas** em `tests/test_mentor_cli.py`, a suíte fecha em **2950 passados de 2964** (12 falhas, 2 skips).

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

As duas últimas funcionalidades sem documentação têm agora um guia cada:

```
gitpr explain                                    # guia do revisor para o diff atual
gitpr tests generate --file src/app.py           # dry run: mostra a suite sem escrever nada
gitpr tests generate --file src/app.py --apply   # escreve o ficheiro de teste após confirmação
```

O `gitpr -h --explain` voltou a funcionar e abre o guia do revisor, e tanto `gitpr tests` como `gitpr tests generate` trazem agora uma ligação para o próprio guia no epílogo da ajuda. Tudo publicado nos cinco idiomas em https://gitpr.natanfiuza.dev.br/docs — e os READMEs apontam agora para lá, com o idioma em `?lang=`.

Se o seu repositório tem ficheiros em codificação legada, atualize: a 1.4.2 descodifica a saída do git com `errors="replace"` e sai com o código 1 quando o git falha, em vez de anunciar "Nenhum código novo encontrado" numa branch que tem alterações.

## Dicas úteis

O Smart Excludes filtra lockfiles, ficheiros minificados e gerados do diff com a sintaxe nativa de pathspec do Git, antes de a IA ver o que quer que seja — o exemplo da documentação reduz ~15.000 tokens para ~250 (98% menos), e a lista de ficheiros alterados continua a ser injetada como metadado.
