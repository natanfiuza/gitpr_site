# GitPR 1.6.0 — Novedades

## Novedades de esta versión

- **El proveedor SCM pasó a ser propiedad del repositorio, no de la máquina:** `src/scm_settings.py` resuelve, en una sola función, **qué proveedor, qué token y qué base URL** valen en ese repositorio — override del proyecto → remote `origin` → `github`. Cada proveedor ganó su propio slot de credencial en `~/.gitpr/.env` (`GITHUB_/GITLAB_/BITBUCKET_/AZURE_DEVOPS_TOKEN_ENCRYPTED` + `*_BASE_URL`), y las claves heredadas `GITPR_SCM_PROVIDER`/`GITPR_SCM_TOKEN_ENCRYPTED`/`GITPR_SCM_BASE_URL` se migran **una sola vez, de forma idempotente** ([ADR-016](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-016-per-provider-scm-settings.md)).
- **El cierre automático de issue dejó de ser exclusivo de GitHub:** GitLab y Bitbucket implementan la lectura de vuelta del contrato (`get_issue`, `get_default_branch`, `closing_line`) y Azure DevOps degrada a `not-supported` en vez de ignorar la funcionalidad. El registro local cambió a `.gitpr/conf/issue.yml`, con lectura transparente del `github.yml` heredado e `IssueInfo.is_open` ([ADR-010](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0010-multiforge-issue-autoclose.md)).
- **El bootstrap de la configuración local se convirtió en un comando:** `gitpr seed` (y el flag `-sd/--seed`) siembra `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` y `.gitpr/skill/.gitpr.linter.yml`, con `--force` para sobrescribir y `--all` para incluir todas las skills de IA. El asistente de instalación se reorganizó en **5 etapas**, empezando por la configuración local del proyecto.
- **La TUI de configuración ganó dos superficies nuevas:** la categoría **Smart Excludes** (lista de fuentes + editor de archivo del proyecto) y la categoría **check** (las 9 `GITPR_CHECK_*`). Y el `DEFAULT_CONFIG` dejó de ser una copia manual — ahora se **deriva del schema** vía `ConfigField.seeded`, por lo que código y schema ya no pueden divergir.
- **El dashboard ganó tendencia y latencia, y el prompt dejó de recibir basura binaria:** el `metrics` pasó a renderizar **Token Trend** (sparkline + barras por día, tope de 14 días) y **Latency Histogram** (media, mediana y P95) en el CLI, en la TUI y en la tool MCP `get_usage_metrics`; `collapse_binary_hunks()` reescribe hunks con una avalancha de `U+FFFD` como `Binary files … differ`, y el catálogo de smart excludes ganó **100+ patrones** en 8 familias binarias.
- **La suite ya no se congela, y la 1.5.0 está en PyPI:** el `-c` solo abre la `CommitApp` cuando existe TTY de verdad (`_may_ask_the_user()`), lo que **desbloqueó** el escenario que congelaba la ejecución completa en ~33%, y la prueba headless del commit se estabilizó. La suite corre ahora hasta el final — **3422 escenarios en 159 archivos**. La **1.6.0** está en el `CHANGELOG.md` y en la rama `develop_natan`, pero **aún no tiene tag ni publicación**.

## Cómo usar

La 1.6.0 está en `CHANGELOG.md` y en la rama `develop_natan`, pero aún no tiene tag; al cierre de esta edición, PyPI sirve la 1.5.0. En cuanto se publique la `v1.6.0`, actualiza con:

```
pip install --upgrade gitpr-cli
```

Para crear la configuración local del proyecto de forma explícita e idempotente, ejecuta `gitpr seed` (o `gitpr -sd`). Escribe `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` y `.gitpr/skill/.gitpr.linter.yml`, nunca sobrescribe un archivo existente y acepta `--force` para sobrescribir y `--all` para descargar también todas las skills de IA.

Para fijar el proveedor por repositorio, versiona la sección `scm` de este archivo junto con el proyecto:

```yaml
scm:
  provider: gitlab
```

GitPR solo escribe `scm.provider` cuando el proveedor elegido difiere del detectado en `origin`; el override del proyecto gana sobre la detección por remote. Los tokens viven en `~/.gitpr/.env`, un slot por proveedor, y las claves heredadas `GITPR_SCM_*` migran solas en la primera ejecución.

## Consejos útiles

GitPR publica en cuatro forges, no en uno: GitHub, GitLab, Bitbucket Cloud y Azure DevOps. El forge se detecta automáticamente por la URL de `origin`, y `gitpr --init` guía la configuración — confirma el provider, pide los extras (organización y proyecto en Azure, usuario en Bitbucket), valida el token hasta 3 veces y solo entonces guarda la credencial cifrada con Fernet en `~/.gitpr/.env`. Un GitLab o Azure self-managed solo necesita `GITPR_SCM_BASE_URL`.
