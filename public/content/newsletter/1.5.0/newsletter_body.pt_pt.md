# GitPR 1.5.0 — Novidades

## Novidades desta versão

- **O ciclo issue → PR → merge passou a fechar sozinho:** toda a issue criada com `gitpr -is` passa a ser registada em `.gitpr/conf/github.yml` (um registo local que se auto-ignora no `.gitignore`), e o próximo `gitpr` verifica que continua aberta antes de oferecer a linha `close #N` no corpo do Pull Request. O `--close-issue` aceita sem perguntar, o `--no-close-issue` salta a verificação, e `GITPR_ISSUE_AUTOCLOSE=false` desliga a funcionalidade. O pacote novo `src/issue_autoclose/` tem **5 ficheiros** e uma série de decisões registadas nas ADRs 001–007 em `docs/specs/issue-autoclose/`.
- **A base do PR deixou de morar só na máquina de quem executa:** `.gitpr/conf/gitpr.config.yml` é versionado com o projeto e carrega a branch de destino; `src/project_config.py` resolve `--base` → ficheiro do projeto → `PR_DEFAULT_BASE` → `origin/HEAD` → `main`, e o alvo tem de existir em `origin` e diferir da branch atual. Um alvo igual à branch atual é recusado e, na execução interativa, reparado por um modal dedicado (`src/ui/pr_base_app.py`) que escreve o novo valor no ficheiro do projeto ([ADR-015](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-015-project-local-pr-base.md)).
- **A dívida de i18n foi paga:** os seis dicionários têm agora **1698 chaves** cada um, o `__lang_version__` subiu para **v0.0.38**, e o `tests/test_i18n.py` fecha **22 verdes** — sem chaves em falta, sem órfãs, sem identidade acima da allowlist. O extrator do `sync_i18n.py` trocou a regex por **AST**, o que eliminou os 21 falsos negativos de concatenação implícita.
- **O ruleset de segredos cobriu as três lacunas que estavam abertas:** chaves STS `ASIA…`, PATs `github_pat_…`, cookies `xoxc-`/`xoxd-`, atribuições **sem aspas** (`API_KEY=abc123`), fronteira à esquerda no nome da chave e menos falsos positivos (`bypassword`, `os.getenv(...)`). São **7 regras** com regexes mais largas, cobertas por **77 cenários**.
- **Contratos que estavam só na cabeça viraram teste:** a versão do pacote tem **SSOT único** em `src/updater.py` (ADR-014) e um teste que falha se `pyproject.toml`, `CLAUDE.md` ou `GEMINI.md` divergirem; o contrato de exit code do baseline ganhou 8 cenários de integração; o read-back de revisores legado (`None`) deixou de imprimir `attached None`; e o `-h` antes do subcomando voltou a renderizar a ajuda em vez de abrir a TUI.
- **A 1.5.0 está taggeada, mas ainda não publicada:** a tag `v1.5.0` aponta para o merge do PR #260 (2026-11-04), porém o PyPI ainda serve a **1.4.4** — o workflow de release existe (`.github/workflows/release.yml`), mas a publicação precisa de ser confirmada.

## Como usar

A 1.5.0 está taggeada como `v1.5.0`; no fecho desta edição o PyPI ainda serve a 1.4.4. Assim que o release for publicado, atualize com:

```
pip install --upgrade gitpr-cli
```

Para o ciclo da issue fechar sozinho, crie a issue com `gitpr -is` e prima F3: o número fica registado em `.gitpr/conf/github.yml` (estado local, adicionado ao `.gitignore`). No próximo `gitpr`, o corpo do Pull Request recebe a linha `close #N` depois de o GitPR verificar que a issue continua aberta. O `--close-issue` aceita sem perguntar, o `--no-close-issue` salta a verificação nessa execução, e `GITPR_ISSUE_AUTOCLOSE=false` desliga a funcionalidade na máquina.

Para fixar a base do PR por repositório, versione este ficheiro com o projeto:

```yaml
pull_request:
  base_branch: develop
```

O GitPR cria `.gitpr/conf/gitpr.config.yml` na primeira execução e nunca o sobrescreve; o `--base` tem precedência numa execução isolada. A branch escolhida tem de existir no `origin` e não pode ser a mesma de onde o PR é aberto.

## Dicas úteis

Execute o `gitpr` novamente na mesma branch e não terá PRs duplicados: o GitPR deteta o PR existente e oferece "Push to existing PR", que atualiza apenas o corpo (o título fica intacto). Bónus: um `git push` que falha com "no upstream" é repetido automaticamente com `--set-upstream`.
