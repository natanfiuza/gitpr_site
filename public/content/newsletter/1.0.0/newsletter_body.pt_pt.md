# GitPR 1.0.0 — Novidades

## Novidades desta versão

- **SCM Multi-Forge (`gitpr --init` + camada `ScmProvider`):** Uma abstração única sobre GitHub, GitLab, Bitbucket e Azure DevOps — o `--init` deteta a forge a partir do remote, valida o token (3 tentativas, novo pedido em 401) e persiste com encriptação Fernet **apenas em caso de sucesso**. O token legacy do GitHub continua a funcionar, sem migração.
- **Subcomando `gitpr release` (Changelog / Release Notes):** Gera o changelog da branch entre `--since` (predefinição: última tag) e `HEAD`, classifica os commits por Conventional Commits, sugere o bump semântico, acrescenta um resumo executivo por IA e *antepõe-no* ao `CHANGELOG.md`. Com `--publish`/`--draft` publica o release na forge (o GitHub cria a tag; o GitLab exige a tag) e `--format markdown|json` entrega output estruturado.
- **Revisores Sugeridos no fluxo de PR:** O GitPR passa a consultar a própria forge por sugestões de revisores ao publicar um PR. Desative com `--no-suggest-reviewers` e ajuste com `GITPR_REVIEWER_SUGGESTION_TOP_N` e `GITPR_REVIEWER_SUGGESTION_EXCLUDED`.
- **Servidor MCP silencioso + DNS limitado:** Fim da fuga de output das tools no fluxo stdio/CLI, resolução DNS limitada no tempo e a predefinição de `GITPR_AI_TIMEOUT` desceu de 600s para **180s**.
- **URLs e prompts localizados:** URLs do repositório uniformizadas nos templates e na documentação; os prompts de criação de issues passam a receber o idioma ativo.
- **i18n expandida para 742 chaves:** Os cabeçalhos do changelog são traduzíveis em runtime (seguem o `--lang`), 48 chaves novas nos 6 dicionários, `__lang_version__` v0.0.23 e paridade total — 0 por traduzir, 0 órfãs.
- **Documentação multilingue expandida:** 3 famílias novas completas em 5 idiomas — `release-notes`, `scm-multiforge` e `suggested-reviewers` — com ADRs de arquitetura, além de 8 tópicos atualizados.
- **Versão 1.0.0:** O `__version__` saltou de 0.0.37 para 1.0.0, e o `CHANGELOG.md` passa a ser mantido pelo próprio `gitpr release`.

## Como usar

Atualize através do PyPI:

```
pip install --upgrade gitpr-cli
```

Ou transfira o binário standalone em [GitHub Releases](https://github.com/gitpr-cli/gitpr/releases).

Configure a sua forge uma única vez — o GitPR deteta-a a partir do remote:

```
gitpr --init            # assistente multi-forge: deteta a forge e valida o token
```

Gere o changelog da sua branch e, se quiser, publique o release:

```
gitpr release           # changelog + resumo de IA no topo do CHANGELOG.md
gitpr release --publish # publica o release na forge configurada
```

O fluxo de PR passa a sugerir revisores automaticamente — desative com `--no-suggest-reviewers`.

## Dicas úteis

O `gitpr -is` gera uma issue a partir do diff atual, mas há dois outros motores: `-ht` compila todo o histórico da branch numa issue de release/épico, e `-b src/core.py:140-195` rastreia a evolução de um ficheiro via `git blame` para documentar código legado e dívida técnica.
