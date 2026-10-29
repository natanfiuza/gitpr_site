# GitPR 1.4.3 — Novedades

## Novedades de esta versión

- **La puerta de actualización dejó de perder los releases publicados después de la primera ejecución del día:** la caché de PyPI se indexaba por la **fecha local**, así que un release publicado a las 10:45 permanecía invisible hasta la medianoche para quien ya había ejecutado la CLI a las 07:54. La caché pasó a ser una **ventana deslizante de 1 hora** con `checked_at` ISO, escritura atómica (`os.replace`) y validación en la lectura — un archivo corrupto, un timestamp en el futuro o un resto del formato antiguo simplemente expira. `gitpr -u`/`--update` ahora **ignora la caché** y consulta PyPI en cada invocación, como la documentación ya prometía.
- **La comparación de versiones pasó a PEP 440:** `packaging.version.Version` sustituyó a la aritmética de tuplas — corrige el orden de los segmentos con varios dígitos (`1.4.10 > 1.4.9`), impide que un pre-release remoto bloquee una instalación estable en un `pip install --upgrade` que se negaría a instalarlo, y trata una versión ilegible de cualquiera de los dos lados como **desconocida** en lugar de "más nueva".
- **La puerta ahora alcanza los subcomandos — pero solo cuando la ejecución puede preguntar:** `gitpr release`, `fix`, `review-pr` y los demás pasan por la puerta cuando hay un TTY en ambos extremos. CI, pipes, hooks, `--quiet`/`--hook`/`--mcp` y `-h`/`--help` **nunca** se bloquean; la ayuda del subcomando se detecta en el **`argv` en bruto**, porque Click limpia los argumentos del contexto antes del callback del grupo.
- **Los 7 fallos de `tests/badge/test_badge_paths.py` por fin se cerraron:** la validación del token del proveedor salió de fuera y entró **dentro** del bloque `if check_remote_state:` del `publish_pull_request_use_case` — el flujo `--no-edit` dejó de devolver un `auth_required` falso positivo. `pr_url`/`pr_number` ahora se leen con `getattr` defensivo, y la fixture ganó `token="ghp_fake"` y `number=1`. **La suite bajó de 12 a 5 fallos conocidos** — deuda abierta durante tres ventanas consecutivas.
- **La semántica de la válvula de escape cambió (ruptura declarada):** `GITPR_SKIP_UPDATE_CHECK` ahora se lee como un **booleano estricto** — solo `1`, `true`, `yes`, `on` y `y` (sin distinguir mayúsculas) desactivan la verificación. `false`, `0`, `no`, `off`, `n` y un valor vacío **mantienen** la puerta activa.
- **La deuda de i18n no se pagó:** los diccionarios siguen en **1620 claves** (×4) y **1618** (`es.json`, `fr.json`), con **8 claves usadas en el código sin traducción** y **2 huérfanas** — exactamente los mismos números que la ventana anterior.

## Cómo usar

Actualiza desde PyPI:

```
pip install --upgrade gitpr-cli
```

La puerta de actualización ahora te sigue en los subcomandos: en un terminal interactivo, `gitpr release`, `gitpr fix` y `gitpr review-pr` también avisan cuando existe una versión más reciente. CI, pipes, hooks, el servidor MCP, `--quiet` y `-h`/`--help` nunca se bloquean — y la flag de ayuda se detecta en el `argv` en bruto, así que `gitpr release -h` sigue limitándose a imprimir la ayuda.

`gitpr -u`/`--update` ahora consulta PyPI en cada ejecución en vez de confiar en la caché. Si desactivas la puerta con `GITPR_SKIP_UPDATE_CHECK`, ten en cuenta que el valor es ahora un booleano estricto: solo `1`, `true`, `yes`, `on` o `y` desactivan la comprobación; `false`, `0`, `no`, `off`, `n` y un valor vacío la mantienen activa.

## Consejos útiles

`gitpr check` es una única puerta de entrada para CI: ejecuta el linter estático, los escáneres de secretos, el risk scoring, la revisión por IA opcional y la clasificación por baseline, y emite SARIF 2.1.0, JSON o JUnit XML para GitHub Code Scanning, GitLab SAST y Jenkins. `--no-ia` lo deja gratis y offline, `--ia-only-high-risk` gasta tokens de IA solo en archivos de riesgo, y los exit codes son deterministas: 0 pasa, 1 hallazgos en el nivel de `--fail-on` o superior, 2 error de configuración o ejecución.
