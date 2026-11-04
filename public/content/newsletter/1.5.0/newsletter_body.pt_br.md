# GitPR 1.5.0 — Novidades

## Novidades desta versão

- **O ciclo issue → PR → merge passou a fechar sozinho:** toda issue criada com `gitpr -is` agora é registrada em `.gitpr/conf/github.yml` (um registro local que se auto-ignora no `.gitignore`), e o próximo `gitpr` verifica que ela continua aberta antes de oferecer a linha `close #N` no corpo do Pull Request. `--close-issue` aceita sem perguntar, `--no-close-issue` pula a checagem, e `GITPR_ISSUE_AUTOCLOSE=false` desliga a feature. O pacote novo `src/issue_autoclose/` tem **5 arquivos** e uma fileira de decisões registradas nas ADRs 001–007 em `docs/specs/issue-autoclose/`.
- **A base do PR deixou de morar só na máquina de quem executa:** `.gitpr/conf/gitpr.config.yml` é versionado com o projeto e carrega a branch de destino; `src/project_config.py` resolve `--base` → arquivo do projeto → `PR_DEFAULT_BASE` → `origin/HEAD` → `main`, e o alvo precisa existir em `origin` e diferir da branch atual. Um alvo igual à branch atual é recusado e, na execução interativa, reparado por um modal dedicado (`src/ui/pr_base_app.py`) que escreve o novo valor no arquivo do projeto ([ADR-015](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-015-project-local-pr-base.md)).
- **A dívida de i18n foi paga:** os seis dicionários têm agora **1698 chaves** cada um, `__lang_version__` subiu para **v0.0.38**, e `tests/test_i18n.py` fecha **22 verdes** — sem chaves faltando, sem órfãs, sem identidade acima da allowlist. O extrator de `sync_i18n.py` trocou o regex por **AST**, o que eliminou os 21 falsos negativos de concatenação implícita.
- **O ruleset de segredos cobriu as três lacunas que estavam abertas:** chaves STS `ASIA…`, PATs `github_pat_…`, cookies `xoxc-`/`xoxd-`, atribuições **sem aspas** (`API_KEY=abc123`), fronteira à esquerda no nome da chave e menos falsos positivos (`bypassword`, `os.getenv(...)`). São **7 regras** com regexes mais largas, cobertas por **77 cenários**.
- **Contratos que estavam só na cabeça viraram teste:** a versão do pacote tem **SSOT único** em `src/updater.py` (ADR-014) e um teste que falha se `pyproject.toml`, `CLAUDE.md` ou `GEMINI.md` divergirem; o contrato de exit code do baseline ganhou 8 cenários de integração; o read-back de revisores legado (`None`) deixou de imprimir `attached None`; e o `-h` antes do subcomando voltou a renderizar ajuda em vez de abrir a TUI.
- **A 1.5.0 está tagueada, mas ainda não publicada:** a tag `v1.5.0` aponta para o merge do PR #260 (2026-11-04), porém o PyPI ainda serve **1.4.4** — o release workflow existe (`.github/workflows/release.yml`), mas a publicação precisa ser confirmada.

## Como usar

A 1.5.0 está tagueada como `v1.5.0`; no fechamento desta edição o PyPI ainda serve a 1.4.4. Assim que o release for publicado, atualize com:

```
pip install --upgrade gitpr-cli
```

Para o ciclo da issue fechar sozinho, crie a issue com `gitpr -is` e pressione F3: o número fica registrado em `.gitpr/conf/github.yml` (estado local, adicionado ao `.gitignore`). No próximo `gitpr`, o corpo do Pull Request recebe a linha `close #N` depois de o GitPR verificar que a issue continua aberta. `--close-issue` aceita sem perguntar, `--no-close-issue` pula a checagem naquela execução, e `GITPR_ISSUE_AUTOCLOSE=false` desliga a feature na máquina.

Para fixar a base do PR por repositório, versione este arquivo junto com o projeto:

```yaml
pull_request:
  base_branch: develop
```

O GitPR cria `.gitpr/conf/gitpr.config.yml` na primeira execução e nunca o sobrescreve; o `--base` tem precedência numa execução isolada. A branch escolhida precisa existir no `origin` e não pode ser a mesma de onde o PR é aberto.

## Dicas úteis

Rode o `gitpr` de novo na mesma branch e você não terá PRs duplicados: o GitPR detecta a PR existente e oferece "Push to existing PR", que atualiza apenas o corpo (o título fica intacto). Bônus: um `git push` que falha com "no upstream" é repetido automaticamente com `--set-upstream`.
