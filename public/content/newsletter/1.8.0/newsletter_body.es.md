# GitPR 1.8.0 — Novedades

## Novedades de esta versión

- **El Map-Reduce dejó de tener un tope único:** `get_map_reduce_thresholds(provider)` resuelve el disparador y el tamaño de lote por proveedor (`_GEMINI`, `_DEEPSEEK`, `_OLLAMA` → genérico → 90.000/50.000), y el particionador se convirtió en una **cascada**: archivo → hunks (`@@`) → líneas, preservando siempre el encabezado y el marcador `[file - part N]`. Un pre-reduce intermedio consolida resúmenes parciales que aún excedan el disparador ([ADR-0022](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0022-map-reduce-per-provider-token-partitioning.md)).
- **La detección de forge fue corregida y el fallback silencioso pasó a ser aviso:** `github.com` pasó a reconocerse **antes** de devolver `default=None` — sin esto, todo repositorio GitHub se trataba como host desconocido y recibía un `scm.provider` espurio. Cuando el remote existe pero no nombra un forge conocido, `resolve_scm_settings` ahora avisa y apunta a `gitpr --init`.
- **Las notas de migración obtuvieron contrato:** [ADR-0020](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0020-per-repo-scm-provisioning-migration.md) formaliza el aprovisionamiento SCM por repositorio (scripts e imágenes no deben escribir las claves heredadas), y [ADR-0021](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0021-strict-boolean-update-check-migration.md) cierra la semántica estricta de `GITPR_SKIP_UPDATE_CHECK`.
- **La extensión de editor obtuvo una ruta de publicación reproducible:** la guía de marketplace fue traducida a los **cinco idiomas**, y `scripts/marketplace-setup-wizard.sh` conduce las cinco etapas de credenciales (PAT de Azure DevOps, namespace de Open VSX, secrets de GitHub Actions) validando cada token contra la API real antes de guardarlo.
- **La documentación de métricas y del Map-Reduce fue sincronizada:** la pestaña **Charts & Trends**, el Token Trend y el Latency Histogram entraron en los cinco idiomas, con una guarda de paridad de traducción que impide que una edición solo EN pase desapercibida.
- **La suite quedó estable en Windows:** el flujo headless de `CommitApp` se salta en `win32` (el `pilot.pause` de Textual bloquea sin TTY real) y el enrutamiento de la CLI mockea `check_internet_connection`, eliminando la dependencia de red.
- **La 1.8.0 fue publicada:** `__version__` = **1.8.0**, `CHANGELOG.md` con `## [1.8.0] - 2026-11-14`, tag `v1.8.0` y PyPI sirviendo 1.8.0.

## Cómo usar

Actualiza a la versión 1.8.0 mediante pip:

```bash
pip install --upgrade gitpr-cli
```

Ajusta los umbrales de Map-Reduce por proveedor de IA en `~/.gitpr/.env` (o de forma interactiva con `gitpr config` en la categoría `ai`):

```bash
# Ejemplo: disparador y tamaño de lote personalizados para Ollama vs. Gemini
GITPR_MAP_REDUCE_MAX_TOKENS_OLLAMA=16000
GITPR_MAP_REDUCE_MAX_PART_TOKENS_OLLAMA=8000
GITPR_MAP_REDUCE_MAX_TOKENS_GEMINI=120000
GITPR_MAP_REDUCE_MAX_PART_TOKENS_GEMINI=60000
```

Si `resolve_scm_settings` advierte que el host del remote `origin` no es reconocido, vincula explícitamente el proveedor SCM y las credenciales del repositorio:

```bash
gitpr --init
```

## Consejos útiles

- `gitpr config` abre una pantalla maestro-detalle sobre `~/.gitpr/.env`, así nadie tiene que recordar si el valor es `true`, `1` o `yes`: cada campo recibe el control que merece su tipo y un valor inválido es difícil de introducir. F2 valida y guarda, Ctrl+R restaura el valor por defecto (elimina la línea), `/` busca etiquetas y variables en todas las categorías, y Esc sale preguntando antes de descartar. Edita el mismo archivo que ya tenías: no es una segunda fuente de configuración.
