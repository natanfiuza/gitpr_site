# GitPR 1.7.0 — Novedades

## Novedades de esta versión

- **El dashboard de métricas ganó gráficos ASCII y navegación por pestañas:** el nuevo `src/ascii_chart.py` renderiza un histograma vertical de latencia y una serie temporal de tokens con funciones puras, sin dependencias nuevas; la `MetricsApp` ganó las pestañas **Events** y **Charts & Trends**, con los atajos `1`/`2` ([ADR-0018](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
- **El prompt se volvió más resistente a los binarios:** `collapse_binary_hunks()` ahora corre **también** al inicio de `generate_pr_content()`, y el catálogo de smart excludes ganó `*.arrow`, `*.gguf`, `*.tflite` y `*.keras` — los artefactos de ML/datos dejan de llegar a la IA incluso cuando entran por un llamador externo.
- **El release de la extensión se desacopló del resto del release:** `extension.yml` ganó el job `github-release` (`.vsix` adjunto a los releases `ext-v*`) y el job `publish-marketplaces` no bloqueante, que evalúa `VSCE_PAT` y `OVSX_PAT` de forma **independiente** y reporta a `$GITHUB_STEP_SUMMARY`. La documentación de instalación del VSIX (CLI y GUI) entró en los 5 idiomas.
- **Costo y moneda dejaron de ser fijos:** el schema ganó el tipo `KIND_FLOAT`, la clave `GITPR_METRICS_CURRENCY` y **ocho** campos de precio `INPUT`/`OUTPUT` por modelo; `DEFAULT_MODEL_PRICES` ahora también precia `gemini-pro-latest` y `gemini-flash-lite-latest`.
- **Las notas de migración quedaron formalizadas:** la ruptura del `action.yml` hacia `gitpr check` ([ADR-0011](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0011-action-unified-check-migration.md)), el commit interactivo sin TTY, el `GITPR_SKIP_UPDATE_CHECK` estricto y el seed/opt-out de la configuración del proyecto en CI ahora tienen documentación explícita.
- **Tres ADR nuevos cerraron contratos pendientes:** [ADR-0017](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0017-documentation-site-language-fallback.md) (fallback de idioma del sitio), [ADR-0018](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) (gráficos/pestañas) y [ADR-0019](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0019-project-configuration-seed-segregation.md) (segregación entre auto-seed en runtime y aprovisionamiento explícito).
- **La 1.6.0 y la 1.7.0 fueron publicadas:** el PyPI sirve la **1.7.0**. La deuda de release de la ventana anterior está cerrada, y `develop_natan` se fusionó en `main` vía PR #310.

## Cómo usar

La 1.7.0 está publicada en PyPI. Actualiza con:

```
pip install --upgrade gitpr-cli
```

El dashboard de métricas ahora tiene dos pestañas: `gitpr metrics dashboard` abre en **Events** y `2` cambia a **Charts & Trends**, donde el histograma vertical de latencia y la serie temporal de tokens se dibujan en la terminal. `1` vuelve a la tabla de eventos, `F5` actualiza y `Esc` sale.

Para reportar el coste en tu moneda, define `GITPR_METRICS_CURRENCY` (por ejemplo `EUR`) y rellena los campos de precio `INPUT`/`OUTPUT` por modelo; el dashboard y `gitpr metrics` leen los mismos valores.

Para instalar la extensión del editor, descarga el `.vsix` desde GitHub Releases (etiqueta `ext-v*`) e instálalo en VS Code o Cursor — sin necesidad de cuenta en el marketplace.

## Consejos útiles

`gitpr metrics dashboard` abre una TUI local con coste, calidad, módulos, providers y tiempo de ciclo de los pull requests (F5 actualiza, Esc sale), y `gitpr metrics export` escribe en CSV y JSON solo las filas que nunca se exportaron. No hay servidor: empaqueta una ventana con `gitpr metrics bundle` y une los archivos del equipo con `gitpr metrics merge` para cerrar el trimestre sin salir de tu disco.
