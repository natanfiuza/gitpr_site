# GitPR 1.4.1 — Novedades

## Novedades de esta versión

- **`gitpr check` — una única puerta para la CI:** el linter, el escaneo de secretos, los puentes SAST, la puntuación de riesgo, el baseline y el review de IA opcional ahora corren detrás de **un solo comando**, con artefactos **SARIF 2.1.0**, **JSON** o **JUnit XML** y un contrato de salida determinista (`0` éxito, `1` hallazgo bloqueante, `2` error de ejecución). `action.yml` se reescribió para llamar a este comando, la clave de API dejó de ser obligatoria y existe un camino `--no-ia` de costo cero.
- **Extensión de VS Code y Cursor — el editor se convirtió en un cliente MCP:** ocho comandos (`commit`, `review`, `fullReview`, `reviewMentor`, `generatePr`, `generatePrExplain`, `publishPr`, `check`) hablan con `gitpr-mcp` por stdio y **nunca duplican la lógica de negocio** — las claves, los tokens y los prompts se quedan del lado de la CLI. La extensión publica PRs en dos pasos, llena el panel **Problems** con los hallazgos de `run_check` y enmascara secretos en ambos lados de la frontera.
- **Dos herramientas MCP nuevas y un caso de uso compartido:** `run_check` (de solo lectura, `no_ia=true` por defecto) y `publish_pull_request` (nunca commitea y nunca hace push) entraron al servidor, que pasó de **16 a 18 herramientas**. La publicación `--no-edit` dejó de tener código propio: CLI y MCP pasan por el mismo `publish_pull_request_use_case`, con los estados `success`/`needs_push`/`already_exists`/`auth_required`/`error`.
- **El skill de PR dejó de propagar un artefacto pegado por accidente:** un fragmento JSON (`".gitpr.commit.md": "gitpr.commit.md",`) terminó en la regla de estructura de la plantilla `pt_br` y la IA lo repetía en **cada** PR generado. Como `--skill` nunca sobrescribe un archivo existente, corregir la plantilla no bastaba: `get_skill_context()` ahora **elimina el fragmento en memoria** (sin tocar el archivo del usuario), y una prueba nueva escanea **todas las plantillas publicadas** para evitar la reincidencia.
- **El merge de PR ganó retroalimentación visual:** la llamada al forge corría en silencio en un hilo daemon y una excepción inesperada **mataba el hilo sin aviso**, dejando la TUI trabada. El modal de progreso existente se generalizó (`title` opcional, `mark_finished(show_close=False)`), pasó a empujarse antes de que arranque el worker y ahora cubre **éxito, conflicto y error inesperado**, con cierre automático tras 1.2 s en el camino feliz.
- **La deuda de i18n se reabrió — y la suite lo reporta:** **8 claves `__()` usadas en el código no existen en ningún diccionario** (publicación de PR y descripciones de herramientas MCP) y **2 quedaron huérfanas**; `es.json` y `fr.json` se quedaron en **1618** claves contra 1620 en los otros cuatro. Sumadas a los **7 fallos de `tests/badge/test_badge_paths.py`** — el proveedor falso de las pruebas no tiene `.token`, y el caso de uso compartido ahora lo exige — y a los **2 fallos conocidos** de `tests/test_mentor_cli.py`, la suite cierra en **2938 aprobadas de 2952** (12 fallos, 2 skips).
- **El release 1.4.1 está preparado, pero aún no cerrado:** `__version__` subió a **1.4.1** (commit `46c25c1`) y `__lang_version__` a **v0.0.35**; `CHANGELOG.md` ya tiene la sección `[1.4.1]` generada por el motor de release — **en el working tree**, y el tag `v1.4.1` todavía **no existe**.

## Cómo usar

Actualiza desde PyPI:

```
pip install --upgrade gitpr-cli
```

La release gira en torno a un único comando de CI:

```
gitpr check                                      # linter, secretos, SAST, riesgo y baseline — más review de IA opcional
gitpr check --format sarif --output gitpr.sarif  # artefacto SARIF 2.1.0 para GitHub Code Scanning
gitpr check --no-ia                              # camino de costo cero: sin llamada a la IA y sin clave de API
gitpr check --fail-on warning                    # hace que los hallazgos de nivel warning bloqueen el build
```

El código de salida es el contrato: `0` éxito, `1` hallazgo bloqueante, `2` error de ejecución. El artefacto también puede salir en `json` o `junit`.

Para VS Code y Cursor, instala la extensión `natanfiuza.gitpr` desde el Marketplace (u Open VSX) y ejecuta los comandos desde la Paleta de Comandos: Commit, Review, Full Review, Review with Mentor, Generate PR, Generate PR with Reviewer Guide, Publish PR y Check. `GitPR: Check` llena el panel Problems con diagnósticos clicables; las claves y los tokens se quedan en `~/.gitpr/.env`.

## Consejos útiles

Los diffs de más de ~90 mil tokens (estimación de 4 caracteres por token) se gestionan automáticamente: GitPR los divide en lotes por límites de archivo, resume cada parte (Map) y unifica todo en una llamada final (Reduce) — sin necesidad de flags.
