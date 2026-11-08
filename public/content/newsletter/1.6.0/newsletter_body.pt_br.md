# GitPR 1.6.0 — Novidades

## Novidades desta versão

- **A forge passou a ser propriedade do repositório, não da máquina:** `src/scm_settings.py` resolve, em uma única função, **qual forge, qual token e qual base URL** valem naquele repositório — override do projeto → remote `origin` → `github`. Cada forge ganhou seu próprio slot de credencial em `~/.gitpr/.env` (`GITHUB_/GITLAB_/BITBUCKET_/AZURE_DEVOPS_TOKEN_ENCRYPTED` + `*_BASE_URL`), e as chaves legadas `GITPR_SCM_PROVIDER`/`GITPR_SCM_TOKEN_ENCRYPTED`/`GITPR_SCM_BASE_URL` são migradas **uma única vez, de forma idempotente** ([ADR-016](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-016-per-provider-scm-settings.md)).
- **O fechamento automático de issue deixou de ser exclusivo do GitHub:** GitLab e Bitbucket implementam o read-back do contrato (`get_issue`, `get_default_branch`, `closing_line`) e o Azure DevOps degrada para `not-supported` em vez de ignorar a feature. O registro local mudou para `.gitpr/conf/issue.yml`, com leitura transparente do `github.yml` legado e `IssueInfo.is_open` ([ADR-010](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0010-multiforge-issue-autoclose.md)).
- **O bootstrap da configuração local virou comando:** `gitpr seed` (e a flag `-sd/--seed`) semeia `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` e `.gitpr/skill/.gitpr.linter.yml`, com `--force` para sobrescrever e `--all` para incluir todas as skills de IA. O wizard de instalação foi reordenado em **5 etapas**, começando pela configuração local do projeto.
- **A TUI de configuração ganhou duas superfícies novas:** a categoria **Smart Excludes** (lista de fontes + editor de arquivo do projeto) e a categoria **check** (as 9 `GITPR_CHECK_*`). E `DEFAULT_CONFIG` deixou de ser uma cópia manual — agora é **derivado do schema** via `ConfigField.seeded`, então código e schema não podem mais divergir.
- **O dashboard ganhou tendência e latência, e o prompt deixou de receber lixo binário:** o `metrics` passou a renderizar **Token Trend** (sparkline + barras por dia, teto de 14 dias) e **Latency Histogram** (média, mediana e P95) no CLI, na TUI e no tool MCP `get_usage_metrics`; o `collapse_binary_hunks()` reescreve hunks com enxurrada de `U+FFFD` como `Binary files … differ`, e o catálogo de smart excludes ganhou **100+ padrões** em 8 famílias binárias.
- **A suíte não trava mais, e a 1.5.0 está no PyPI:** o `-c` só abre a `CommitApp` quando existe TTY de verdade (`_may_ask_the_user()`), o que **destravou** o cenário que congelava a execução completa em ~33%, e o teste headless do commit foi estabilizado. A suíte agora roda até o fim — **3422 cenários em 159 arquivos**. A **1.6.0** está no `CHANGELOG.md` e na branch `develop_natan`, mas **ainda não tem tag nem publicação**.

## Como usar

A 1.6.0 está no `CHANGELOG.md` e na branch `develop_natan`, mas ainda não tem tag; no fechamento desta edição o PyPI serve a 1.5.0. Assim que a `v1.6.0` for publicada, atualize com:

```
pip install --upgrade gitpr-cli
```

Para criar a configuração local do projeto de forma explícita e idempotente, rode `gitpr seed` (ou `gitpr -sd`). Ele grava `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` e `.gitpr/skill/.gitpr.linter.yml`, nunca sobrescreve um arquivo existente e aceita `--force` para sobrescrever e `--all` para baixar também todas as skills de IA.

Para fixar a forge por repositório, versione a seção `scm` deste arquivo junto com o projeto:

```yaml
scm:
  provider: gitlab
```

O GitPR só grava `scm.provider` quando a forge escolhida difere da detectada no `origin`; o override do projeto vence a detecção pelo remote. Os tokens ficam em `~/.gitpr/.env`, um slot por forge, e as chaves legadas `GITPR_SCM_*` migram sozinhas na primeira execução.

## Dicas úteis

O GitPR publica em quatro forges, não em um: GitHub, GitLab, Bitbucket Cloud e Azure DevOps. O forge é detectado automaticamente pela URL do `origin`, e o `gitpr --init` conduz a configuração — confirma o provider, pede os extras (organização e projeto no Azure, usuário no Bitbucket), valida o token até 3 vezes e só então grava a credencial criptografada com Fernet em `~/.gitpr/.env`. Um GitLab ou Azure self-managed precisa apenas de `GITPR_SCM_BASE_URL`.
