# GitPR 1.0.0 — Novidades

## Novidades desta versão

- **SCM Multi-Forge (`gitpr --init` + camada `ScmProvider`):** Uma abstração única sobre GitHub, GitLab, Bitbucket e Azure DevOps — o `--init` detecta a forge pelo remote, valida o token (3 tentativas, novo pedido em 401) e persiste com criptografia Fernet **apenas em caso de sucesso**. O token legado do GitHub continua funcionando, sem migração.
- **Subcomando `gitpr release` (Changelog / Release Notes):** Gera o changelog da branch entre `--since` (padrão: última tag) e `HEAD`, classifica os commits por Conventional Commits, sugere o bump semântico, adiciona um resumo executivo de IA e faz *prepend* no `CHANGELOG.md`. Com `--publish`/`--draft` publica a release na forge (o GitHub cria a tag; o GitLab exige a tag) e `--format markdown|json` entrega saída estruturada.
- **Suggested Reviewers no fluxo de PR:** O GitPR agora consulta a própria forge por sugestões de revisores ao publicar um PR. Desligue com `--no-suggest-reviewers` e ajuste com `GITPR_REVIEWER_SUGGESTION_TOP_N` e `GITPR_REVIEWER_SUGGESTION_EXCLUDED`.
- **MCP Server silencioso + DNS limitado:** Fim do vazamento de saída das tools no fluxo stdio/CLI, resolução DNS com limite de tempo e o padrão de `GITPR_AI_TIMEOUT` caiu de 600s para **180s**.
- **URLs e prompts localizados:** URLs do repositório padronizadas nos templates e na documentação; os prompts de criação de issue agora recebem o idioma ativo.
- **i18n expandida para 742 chaves:** Os cabeçalhos do changelog são traduzíveis em runtime (seguem o `--lang`), 48 chaves novas nos 6 dicionários, `__lang_version__` v0.0.23 e paridade total — 0 sem tradução, 0 órfãs.
- **Documentação multilíngue expandida:** 3 famílias novas completas em 5 idiomas — `release-notes`, `scm-multiforge` e `suggested-reviewers` — com ADRs de arquitetura, além de 8 tópicos atualizados.
- **Versão 1.0.0:** O `__version__` saltou de 0.0.37 para 1.0.0, e o `CHANGELOG.md` agora é mantido pelo próprio `gitpr release`.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

Ou baixe o binário standalone em [GitHub Releases](https://github.com/gitpr-cli/gitpr/releases).

Configure sua forge uma única vez — o GitPR detecta pelo remote:

```
gitpr --init            # assistente multi-forge: detecta a forge e valida o token
```

Gere o changelog da sua branch e, se quiser, publique a release:

```
gitpr release           # changelog + resumo de IA no topo do CHANGELOG.md
gitpr release --publish # publica a release na forge configurada
```

O fluxo de PR agora sugere revisores automaticamente — desligue com `--no-suggest-reviewers`.

## Dicas úteis

O `gitpr -is` gera uma issue a partir do diff atual, mas há dois outros motores: `-ht` compila todo o histórico da branch numa issue de release/épico, e `-b src/core.py:140-195` rastreia a evolução de um arquivo via `git blame` para documentar código legado e dívida técnica.
