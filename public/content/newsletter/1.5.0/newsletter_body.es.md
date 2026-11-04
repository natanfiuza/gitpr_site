# GitPR 1.5.0 — Novedades

## Novedades de esta versión

- **El ciclo issue → PR → merge ahora se cierra solo:** todo issue creado con `gitpr -is` ahora se registra en `.gitpr/conf/github.yml` (un registro local que se auto-ignora en el `.gitignore`), y el siguiente `gitpr` verifica que siga abierto antes de ofrecer la línea `close #N` en el cuerpo del Pull Request. `--close-issue` acepta sin preguntar, `--no-close-issue` omite la comprobación, y `GITPR_ISSUE_AUTOCLOSE=false` desactiva la funcionalidad. El paquete nuevo `src/issue_autoclose/` tiene **5 archivos** y una serie de decisiones registradas en las ADRs 001–007 en `docs/specs/issue-autoclose/`.
- **La base del PR dejó de vivir solo en la máquina de quien ejecuta:** `.gitpr/conf/gitpr.config.yml` se versiona con el proyecto y carga la rama de destino; `src/project_config.py` resuelve `--base` → archivo del proyecto → `PR_DEFAULT_BASE` → `origin/HEAD` → `main`, y el destino debe existir en `origin` y diferir de la rama actual. Un destino igual a la rama actual se rechaza y, en la ejecución interactiva, se repara mediante un modal dedicado (`src/ui/pr_base_app.py`) que escribe el nuevo valor en el archivo del proyecto ([ADR-015](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-015-project-local-pr-base.md)).
- **La deuda de i18n se pagó:** los seis diccionarios tienen ahora **1698 claves** cada uno, `__lang_version__` subió a **v0.0.38**, y `tests/test_i18n.py` cierra **22 en verde** — sin claves faltantes, sin huérfanas, sin identidad por encima de la allowlist. El extractor de `sync_i18n.py` cambió el regex por **AST**, lo que eliminó los 21 falsos negativos de concatenación implícita.
- **El ruleset de secretos cubrió las tres brechas que estaban abiertas:** claves STS `ASIA…`, PATs `github_pat_…`, cookies `xoxc-`/`xoxd-`, asignaciones **sin comillas** (`API_KEY=abc123`), frontera a la izquierda en el nombre de la clave y menos falsos positivos (`bypassword`, `os.getenv(...)`). Son **7 reglas** con regexes más amplias, cubiertas por **77 escenarios**.
- **Contratos que solo estaban en la cabeza se volvieron pruebas:** la versión del paquete tiene **SSOT único** en `src/updater.py` (ADR-014) y una prueba que falla si `pyproject.toml`, `CLAUDE.md` o `GEMINI.md` divergen; el contrato de exit code del baseline ganó 8 escenarios de integración; el read-back de revisores legado (`None`) dejó de imprimir `attached None`; y `-h` antes del subcomando volvió a renderizar la ayuda en lugar de abrir la TUI.
- **La 1.5.0 está tagueada, pero aún no publicada:** la tag `v1.5.0` apunta al merge del PR #260 (2026-11-04), pero el PyPI todavía sirve **1.4.4** — el release workflow existe (`.github/workflows/release.yml`), pero la publicación debe confirmarse.

## Cómo usar

La 1.5.0 está tagueada como `v1.5.0`; al cierre de esta edición, PyPI todavía sirve la 1.4.4. En cuanto se publique el release, actualiza con:

```
pip install --upgrade gitpr-cli
```

Para que el ciclo del issue se cierre solo, crea el issue con `gitpr -is` y pulsa F3: el número queda registrado en `.gitpr/conf/github.yml` (estado local, añadido al `.gitignore`). En el siguiente `gitpr`, el cuerpo del Pull Request recibe la línea `close #N` después de que GitPR compruebe que el issue sigue abierto. `--close-issue` la acepta sin preguntar, `--no-close-issue` omite la comprobación en esa ejecución, y `GITPR_ISSUE_AUTOCLOSE=false` desactiva la funcionalidad en la máquina.

Para fijar la base del PR por repositorio, versiona este archivo junto con el proyecto:

```yaml
pull_request:
  base_branch: develop
```

GitPR crea `.gitpr/conf/gitpr.config.yml` en la primera ejecución y nunca lo sobrescribe; `--base` tiene precedencia en una ejecución aislada. La rama elegida debe existir en `origin` y no puede ser la misma desde la que se abre el PR.

## Consejos útiles

Vuelve a ejecutar `gitpr` en la misma rama y no tendrás PRs duplicados: GitPR detecta la PR existente y ofrece "Push to existing PR", que actualiza solo el cuerpo (el título queda intacto). Extra: un `git push` que falla con "no upstream" se reintenta automáticamente con `--set-upstream`.
