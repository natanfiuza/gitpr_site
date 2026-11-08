# GitPR 1.6.0 — Novidades

## Novidades desta versão

- **A forge passou a ser propriedade do repositório, não da máquina:** `src/scm_settings.py` resolve, numa única função, **qual forge, qual token e qual base URL** valem naquele repositório — override do projeto → remote `origin` → `github`. Cada forge ganhou o seu próprio slot de credencial em `~/.gitpr/.env` (`GITHUB_/GITLAB_/BITBUCKET_/AZURE_DEVOPS_TOKEN_ENCRYPTED` + `*_BASE_URL`), e as chaves legadas `GITPR_SCM_PROVIDER`/`GITPR_SCM_TOKEN_ENCRYPTED`/`GITPR_SCM_BASE_URL` são migradas **uma única vez, de forma idempotente** ([ADR-016](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-016-per-provider-scm-settings.md)).
- **O fecho automático de issue deixou de ser exclusivo do GitHub:** o GitLab e o Bitbucket implementam o read-back do contrato (`get_issue`, `get_default_branch`, `closing_line`) e o Azure DevOps degrada para `not-supported` em vez de ignorar a funcionalidade. O registo local mudou para `.gitpr/conf/issue.yml`, com leitura transparente do `github.yml` legado e `IssueInfo.is_open` ([ADR-010](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0010-multiforge-issue-autoclose.md)).
- **O bootstrap da configuração local tornou-se um comando:** `gitpr seed` (e a flag `-sd/--seed`) semeia `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` e `.gitpr/skill/.gitpr.linter.yml`, com `--force` para sobrescrever e `--all` para incluir todas as skills de IA. O wizard de instalação foi reordenado em **5 etapas**, começando pela configuração local do projeto.
- **A TUI de configuração ganhou duas superfícies novas:** a categoria **Smart Excludes** (lista de fontes + editor de ficheiro do projeto) e a categoria **check** (as 9 `GITPR_CHECK_*`). E o `DEFAULT_CONFIG` deixou de ser uma cópia manual — agora é **derivado do schema** via `ConfigField.seeded`, pelo que código e schema já não podem divergir.
- **O dashboard ganhou tendência e latência, e o prompt deixou de receber lixo binário:** o `metrics` passou a renderizar **Token Trend** (sparkline + barras por dia, teto de 14 dias) e **Latency Histogram** (média, mediana e P95) no CLI, na TUI e na tool MCP `get_usage_metrics`; o `collapse_binary_hunks()` deteta hunks com uma enxurrada de `U+FFFD` e reescreve-os como `Binary files … differ`, e o catálogo de smart excludes ganhou **100+ padrões** em 8 famílias binárias.
- **A suite já não bloqueia, e a 1.5.0 está no PyPI:** o `-c` só abre a `CommitApp` quando existe TTY a sério (`_may_ask_the_user()`), o que **desbloqueou** o cenário que congelava a execução completa em ~33%, e o teste headless do commit foi estabilizado. A suite corre agora até ao fim — **3422 cenários em 159 ficheiros**. A **1.6.0** está no `CHANGELOG.md` e na branch `develop_natan`, mas **ainda não tem tag nem publicação**.

## Como usar

A 1.6.0 está no `CHANGELOG.md` e na branch `develop_natan`, mas ainda não tem tag; no fecho desta edição o PyPI serve a 1.5.0. Assim que a `v1.6.0` for publicada, atualize com:

```
pip install --upgrade gitpr-cli
```

Para criar a configuração local do projeto de forma explícita e idempotente, execute `gitpr seed` (ou `gitpr -sd`). Cria `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` e `.gitpr/skill/.gitpr.linter.yml`, nunca sobrescreve um ficheiro existente e aceita `--force` para sobrescrever e `--all` para transferir também todas as skills de IA.

Para fixar a forge por repositório, versione a secção `scm` deste ficheiro junto com o projeto:

```yaml
scm:
  provider: gitlab
```

O GitPR só escreve `scm.provider` quando a forge escolhida difere da detetada no `origin`; o override do projeto vence a deteção pelo remote. Os tokens ficam em `~/.gitpr/.env`, um slot por forge, e as chaves legadas `GITPR_SCM_*` migram sozinhas na primeira execução.

## Dicas úteis

O GitPR publica em quatro forges, não num só: GitHub, GitLab, Bitbucket Cloud e Azure DevOps. O forge é detetado automaticamente pelo URL do `origin`, e o `gitpr --init` conduz a configuração — confirma o provider, pede os extras (organização e projeto no Azure, utilizador no Bitbucket), valida o token até 3 vezes e só então grava a credencial encriptada com Fernet em `~/.gitpr/.env`. Um GitLab ou Azure self-managed precisa apenas de `GITPR_SCM_BASE_URL`.
