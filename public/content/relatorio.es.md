# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.18 (2026-10-18)**

## **📌 Visión General**

**GitPR** es una herramienta CLI (Command Line Interface) avanzada para la automatización de procesos Git mediante Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). Su objetivo principal es actuar como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita la deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.18):**
- **`gitpr check` — una única puerta para la CI:** el linter, el escaneo de secretos, los puentes SAST, la puntuación de riesgo, el baseline y el review de IA opcional ahora corren detrás de **un solo comando**, con artefactos **SARIF 2.1.0**, **JSON** o **JUnit XML** y un contrato de salida determinista (`0` éxito, `1` hallazgo bloqueante, `2` error de ejecución). `action.yml` se reescribió para llamar a este comando, la clave de API dejó de ser obligatoria y existe un camino `--no-ia` de costo cero.
- **Extensión de VS Code y Cursor — el editor se convirtió en un cliente MCP:** ocho comandos (`commit`, `review`, `fullReview`, `reviewMentor`, `generatePr`, `generatePrExplain`, `publishPr`, `check`) hablan con `gitpr-mcp` por stdio y **nunca duplican la lógica de negocio** — las claves, los tokens y los prompts se quedan del lado de la CLI. La extensión publica PRs en dos pasos, llena el panel **Problems** con los hallazgos de `run_check` y enmascara secretos en ambos lados de la frontera.
- **Dos herramientas MCP nuevas y un caso de uso compartido:** `run_check` (de solo lectura, `no_ia=true` por defecto) y `publish_pull_request` (nunca commitea y nunca hace push) entraron al servidor, que pasó de **16 a 18 herramientas**. La publicación `--no-edit` dejó de tener código propio: CLI y MCP pasan por el mismo `publish_pull_request_use_case`, con los estados `success`/`needs_push`/`already_exists`/`auth_required`/`error`.
- **El skill de PR dejó de propagar un artefacto pegado por accidente:** un fragmento JSON (`".gitpr.commit.md": "gitpr.commit.md",`) terminó en la regla de estructura de la plantilla `pt_br` y la IA lo repetía en **cada** PR generado. Como `--skill` nunca sobrescribe un archivo existente, corregir la plantilla no bastaba: `get_skill_context()` ahora **elimina el fragmento en memoria** (sin tocar el archivo del usuario), y una prueba nueva escanea **todas las plantillas publicadas** para evitar la reincidencia.
- **El merge de PR ganó retroalimentación visual:** la llamada al forge corría en silencio en un hilo daemon y una excepción inesperada **mataba el hilo sin aviso**, dejando la TUI trabada. El modal de progreso existente se generalizó (`title` opcional, `mark_finished(show_close=False)`), pasó a empujarse antes de que arranque el worker y ahora cubre **éxito, conflicto y error inesperado**, con cierre automático tras 1.2 s en el camino feliz.
- **La deuda de i18n se reabrió — y la suite lo reporta:** **8 claves `__()` usadas en el código no existen en ningún diccionario** (publicación de PR y descripciones de herramientas MCP) y **2 quedaron huérfanas**; `es.json` y `fr.json` se quedaron en **1618** claves contra 1620 en los otros cuatro. Sumadas a los **7 fallos de `tests/badge/test_badge_paths.py`** — el proveedor falso de las pruebas no tiene `.token`, y el caso de uso compartido ahora lo exige — y a los **2 fallos conocidos** de `tests/test_mentor_cli.py`, la suite cierra en **2938 aprobadas de 2952** (12 fallos, 2 skips).
- **El release 1.4.1 está preparado, pero aún no cerrado:** `__version__` subió a **1.4.1** (commit `46c25c1`) y `__lang_version__` a **v0.0.35**; `CHANGELOG.md` ya tiene la sección `[1.4.1]` generada por el motor de release — **en el working tree**, y el tag `v1.4.1` todavía **no existe**.

- **Versión actual:** 1.4.1 (bump **commiteado** en `46c25c1`; **tag `v1.4.1` aún no creado**; `v1.4.0` sigue siendo el último tag, del 2026-10-12)
- **Versión de los diccionarios de idioma:** v0.0.35 (v0.0.34 → v0.0.35 en esta ventana)
- **Versión de los scripts de hook:** v0.0.4 (sin cambios)
- **Publicación:** PyPI (`pip install gitpr-cli`) — canal binario eliminado
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 diccionarios)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal) — **15 subcomandos** 🆕 (`badge`, `baseline`, `check` 🆕, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `split`, `tests`). Cuatro de ellos son **grupos**: `baseline` (6 subcomandos), `metrics` (8), `policy` (7) y `tests` (1). `check` es un comando único con **8 opciones de configuración** (sin contar `-h`).
* **UI/Terminal:** Textual — TUI para chat interactivo, edición de issues, help screen, dashboard de métricas, PR Publisher (con el **modal de progreso reutilizado en el merge** 🆕), errores del linter (`LinterApp`), configuración (`ConfigApp`), el modal `NoticeScreen`, el **recorrido `gitpr demo`** (`src/ui/demo/`) y el **asistente de migración del ledger** (`src/ui/metrics_migration_app.py`).
* **Arquitectura en Capas:** `src/domain/` (reglas puras, sin I/O) + `src/application/use_cases/` (orquestación, **15 casos de uso** 🆕) + presentación (CLI/TUI). **9 paquetes de dominio** 🆕: `baseline`, **`check`** 🆕, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`.
* **Extensión de Editor 🆕:** `extensions/vscode/` — cliente MCP fino en TypeScript para **VS Code y Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). No contiene reglas de negocio: abre una sesión stdio con `gitpr-mcp`, verifica el contrato de las 7 herramientas y delega todo.
* **Criptografía:** `cryptography.fernet` para protección local de claves de API, tokens de GitHub y tokens SCM de los forges.
* **Configuración:** `python-dotenv`, `pyyaml` (linter estático) + su propio schema declarativo (`src/config_schema.py`, **80 `ConfigField`** y **16 categorías**).
* **Proveedores de IA:** Integración vía SDK oficial de Google GenAI, OpenAI SDK (DeepSeek) y OpenAI SDK (Ollama local). Predeterminados configurados: `gemini-pro-latest` / `gemini-flash-lite-latest` y `deepseek-v4-pro` / `deepseek-v4-flash`.
* **APIs de Forge:** `requests` (REST) — capa de abstracción multi-forge en `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py` (URLs de commit, PR y perfil por forge, funciones puras); `src/github_api.py` mantenido como shim deprecado.
* **Linter / SAST:** YAML regex + bridge Checkstyle + ruleset de secretos integrado (`src/security_ruleset.py`) + 4 puentes en `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), con el modelo normalizado en `src/domain/linter/sast_finding_mapper.py` y el hallazgo canónico en `src/domain/finding/`.
* **Infraestructura de CI 🆕:** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) y `masking.py` (enmascarado de credenciales antes de serializar cualquier artefacto).
* **Internals de Git:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py` (etiqueta de repo, rama y autor en una sola llamada a git), `risk_history_reader.py` y `test_matcher.py`.
* **Persistencia local:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) con WAL, `PRAGMA user_version`, claves UUID y transporte por bundle.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 herramientas anotadas** 🆕, **19 recursos**, 7 prompts.
* **Pruebas:** Pytest + `unittest.mock` (**138 módulos de prueba** 🆕 — 51 en la raíz, 12 en `tests/application/use_cases/`, 10 en `tests/fix/`, 9 en `tests/scm/`, 7 en `tests/badge/`, 6 en `tests/demo/`, 6 en `tests/split/`, 5 en `tests/domain/baseline/`, 4 en `tests/domain/policy/`, 4 en `tests/review/`, 4 en `tests/infrastructure/linter/external/`, **3 en `tests/domain/check/`** 🆕, **3 en `tests/infrastructure/ci/`** 🆕, 2 en `tests/domain/risk/`, 2 en `tests/domain/tests_generation/`, 2 en `tests/integration/`, 2 en `tests/infrastructure/git/`, 2 en `tests/domain/linter/`, 1 en `tests/domain/finding/`, 1 en `tests/domain/mentor/`, 1 en `tests/domain/pr/`, 1 en `tests/infrastructure/baseline/` —, **2952 escenarios recolectados**) + pruebas e2e del servidor MCP vía subprocess real (JSON-RPC stdio) + fixtures de repositorio git real (`tests/fix/`, `tests/split/`) + guard de aislamiento de red (`tests/demo/conftest.py`, `tests/integration/conftest.py`). La extensión tiene su propia suite Mocha (7 pruebas contra el servidor MCP real).
* **Empaquetado:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; packs embebidos vía `package-data`; `packaging>=23.0`. La extensión se empaqueta aparte (`vsce package`, VSIX).
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` (ahora llama a `gitpr check`) + `tests.yml` (matriz Python 3.10/3.13) + **`extension.yml`** 🆕 (build, pruebas y empaquetado del VSIX; publicación en Marketplace/Open VSX con tags `ext-v*`).

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación Estructurada:** Se comunica con el LLM pidiendo salida estrictamente JSON.
* **Map-Reduce (Diffs Gigantes):** Cuando el diff supera ~90k tokens, lo divide automáticamente en lotes por archivo (`split_diff_into_chunks`), procesa cada parte (Map) y unifica los resúmenes (Reduce). Soporta PRs, commits e Issues. `provider`/`model` se agregan entre los chunks y `meta`/`modules` se pasan a `log_command_metric`, que copia los tokens reales de la metadata del proveedor en el momento de la escritura.
* **Tokenizer Local:** `tokenizer.json` para estimación precisa de tokens antes del envío a la IA.
* **Estimación de Tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()` con fallback al tokenizer local.
* **Optimización Nativa de Git:** Flags `-U1`, `-w`, `-M`, `-B` en los comandos `get_git_diff` y `get_git_full_diff` para reducir contexto inútil.
* **`get_split_diff()`:** El diff propio del `split`, con `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sin** reutilizar `get_git_diff()` — el `-w` (ignora espacios en blanco) y los smart-excludes romperían la garantía de árbol byte-idéntico.
* **Pre-Save (`--pre-save`):** Flag oculta de debug que guarda el payload completo (instrucción del sistema + prompt) en JSON antes de cada llamada a la IA.
* **Smart Excludes con Dos Capas:** Capa global (`~/.gitpr/conf/`) + capa local del proyecto (`./.gitpr/conf/`), fusionadas en runtime. Auto-seed del archivo local en la primera ejecución. `_load_smart_excludes()` acepta `force=`.
* **Métricas con Seguimiento de Tiempo:** `log_command_metric()` en todos los flujos, ahora con `meta`, `modules` y `duration_ms`.
* **Resolución Centralizada de Salida:** `resolve_output_path()` — por defecto en `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`)**: `gitpr --init` — detecta el forge desde el remote origin, solicita extras por forge, valida el token y persiste **solo en caso de éxito**; anuncia el badge automático en el camino de PR.
* **Plantilla de Skill de Release (`ensure_release_skill_template()`)**: Descarga `templates/gitpr.release.*.md` en el primer uso de `gitpr release`.
* **Guard de Skill Corrupto 🆕:** `_CORRUPTED_PR_SKILL_ARTIFACT` documenta el fragmento JSON que se filtró en la plantilla `pt_br`, y `get_skill_context()` lo elimina **en memoria** cuando `action_type == "pr"` — el archivo del usuario queda intacto byte a byte, y las demás acciones (`commit`, `review`, etc.) leen el archivo tal cual. Como el prompt cambia, la clave MD5 de la caché también cambia y las respuestas contaminadas dejan de reutilizarse.
* **Registry Compartido de Skills:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` en `src/config.py`, con la entrada `mentor` ya registrada.
* **Motor de Review con Alcance de Caché (`generate_pr_content`):** `cache_scope` (anexado **solo a la clave de caché**) y `store_diff` (graba el diff revisado); `cache_scope` también lleva el policy pack activo.
* **Trailer de Coautoría:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceros.
* **Subprocesses Blindados:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` en todos los `subprocess.run`; verificación de conexión vía socket `8.8.8.53:53` antes de las operaciones de red.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitectura de Plugins:** Carga plugins de `~/.gitpr/plugins/`, aplicándose a **todos los proyectos**.
* **Plugins de Linter (`linter/`):** Archivos `.yml` fusionados con el `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`):** Archivos `.md` que extienden el contexto del sistema.
* **Factory Closures:** `get_linter_plugins` y `get_prompt_plugins` aíslan estado entre sesiones.
* **Comando `--plugins`:** Lista todos los plugins globales instalados con sus tipos y rutas.
* **Documentación Multilingüe:** `docs/plugins-system.md` en 5 idiomas.
* **Relación con los Policy Packs:** el pack es una **tercera** capa de reglas — los plugins pertenecen a la máquina, `.gitpr.linter.yml` pertenece al proyecto y el pack está versionado y commiteado. `load_linter_rules()` fusiona las tres en el orden declarado, con las severidades del pack por encima.

### **3. Interfaz CLI y Configuración (`src/main.py` y `src/config.py`)**

* **Setup Inicial:** Detecta la primera ejecución, crea `~/.gitpr/` y solicita interactivamente las claves, preferencias e idioma. El hint de la primera ejecución apunta a `gitpr demo`.
* **Enrutamiento de Comandos:** Gestiona todas las flags y los **15 subcomandos** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline` y **`check`** 🆕.
* **Comportamiento Predeterminado:** Ejecutar `gitpr` sin flags abre la TUI del PR Publisher.
* **Flags (32 opciones Click en la raíz — sin cambios en esta ventana):** `--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version`, etc.
* **Subcomando `check` 🆕 — 8 opciones:** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on info|warning|error|minor|major|critical|blocker`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` y `--policy <name>@<version>`. El `-h` del subcomando ya apunta a `docs/check-ci` en el idioma actual.
* **Grupo `baseline` — 6 subcomandos:** `create` (`--refresh`, `--base`, `--yes`, `--format`), `show` (`--status`, `--rule`, `--file`, `--format`), `validate` (`--format`), `update` (`--recompute`, `--base`, `--yes`), `suppress` (`--reason`, `--scope`, `--debt`, `--owner`, `--due-date`, `--yes`) y `unsuppress` (`--yes`).
* **Grupo `policy` — 7 subcomandos:** `list`, `show`, `validate`, `use`, `off`, `install` (`--force`, `--yes`) e `init` (`--stack`, `--yes`).
* **Grupo `metrics` — 8 subcomandos:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` y el oculto `hook-event`; los de lectura comparten `--days`/`--since`/`--until`.
* **Subcomando `risk` — 4 opciones:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Subcomando `mentor` — 2 opciones:** `--finding`, `--provider`.
* **Variables de Entorno (58 claves en `DEFAULT_CONFIG`):** las cuatro `GITPR_BASELINE_*`, las dos de mentor, las dos de policy y `GITPR_RISK_INCLUDE_IN_REVIEW`. **Fuera de `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY` y las **9 `GITPR_CHECK_*`** 🆕 (`BASE`, `FORMAT`, `OUTPUT`, `FAIL_ON`, `NO_IA`, `IA_ONLY_HIGH_RISK`, `BASELINE_PATH`, `POLICY`, `TIMEOUT_SECONDS`).
* **Ayuda Contextual:** `-h --flag` muestra documentación específica de la flag con un enlace consciente del idioma; los subcomandos tienen su propio `epilog=` (párrafo Click `\b`). `risk`, `policy`, `baseline`, `mentor` y `check` (vía Click) exponen su propia ayuda.
* **Capa de Escritura del `.env`:** `read_env_file_values()` lee **solo el archivo** vía `dotenv_values`; `save_config_values()` con `set_key`; `remove_config_value()` con `unset_key`. `validate_ai_key()` distingue una credencial rechazada (`401`/`403`) de una red inalcanzable.
* **Puerta de Baseline en el Flujo:** `main.py` resuelve la puerta de baseline antes del lint, del review, del full review, de la auditoría de archivo, del review remoto y del riesgo, y el **código de salida del lint sigue el bloqueo consciente del baseline**.
* **Publicación Directa Refactorizada 🆕:** `_publish_pr_directly` (`--no-edit`) dejó de llamar al forge en línea y ahora delega en **`publish_pull_request_use_case`** — el mismo camino que la herramienta MCP `publish_pull_request`. El badge de GitPR se sigue anunciando antes de la llamada, y el cuerpo recomendado lo compone `format_recommended_commit_body()`.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)**

* **Interfaz Interactiva Completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en el terminal.
* **7 Pantallas Modales:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` y `NoticeScreen`.
* **Badge en el Cuerpo del PR:** `attach_pr_badge()` corre en `src/main.py` justo después de que `pr_data` está completo — **punto de inyección único**. Anexado idempotente por marcador y divulgación explícita en `--no-edit`.
* **`_attach_reviewers` con resolución:** Resuelve lo que el usuario escribió **antes** de enviar, reporta lo que fue descartado y, en un `422` de lote, **repite uno a uno**.
* **Revisores Sugeridos:** Consulta al forge los revisores sugeridos; `_reviewer_suggestion_view()` monta `resolutions` vía `resolve_candidates` y prellena `handles`.
* **Bindings:** F1 (Help), F2 (Guardar .md local), F3 (Publicar vía forge), Esc (Salir).
* **Flujo de Auto-Commit:** Linter → mensaje IA → confirmación → commit → push → publica PR. El resultado del linter lleva los **hallazgos estructurados** (`findings_out`), que es lo que el baseline clasifica antes de componer el informe.
* **Modal de Progreso en el Merge 🆕:** `CommitProgressScreen` ganó un `title` opcional (predeterminado `📦 Processing commit…`) y `mark_finished(show_close=False)`; `_merge_progress_screen` se empuja **antes** de que arranque el worker, y el worker arranca desde el timer de montaje para eliminar la carrera entre el montaje y un callback rápido. El éxito muestra `✅ PR #N merged successfully!`, llena la barra, oculta Close y se cierra automáticamente tras **1.2 s** antes del prompt para abrir en el navegador; el conflicto y el error inesperado (ahora capturado por un `except Exception` que loguea) pasan por `_on_merge_failure`. Si el usuario cierra el modal a mitad de camino, el merge termina y el prompt aparece sin tocar widgets muertos. Cinco locales ganaron las tres cadenas nuevas (en, pt_br, pt_pt, es/es_es, fr/fr_fr).
* **Manejo de PR Existente / Auto-Upstream / Flujo de Merge** — sin cambios (`GITPR_AUTO_MERGE`).

### **5. Módulo de API de GitHub (`src/github_api.py`)**

* **Shim Deprecado:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` y las demás funciones delegan en `src/infrastructure/scm/github_provider.py`; emite un `DeprecationWarning` y mantiene las tuplas legacy `(ok, data, status)` — ningún código nuevo debe importarlo.

### **6. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) en el diff sin gastar cuota de IA.
* **Reglas YAML:** Lee `.gitpr.linter.yml` (creado vía `--skill`).
* **Plugins de Linter:** Reglas adicionales desde `~/.gitpr/plugins/linter/*.yml`.
* **Comodín de Extensión:** `extensions: ["*"]` significa **todo** archivo, incluidos los que no tienen sufijo y los dotfiles.
* **Ruleset de Secretos Integrado:** `load_linter_rules()` fusiona `src/security_ruleset.py` **después** de las reglas del proyecto y de los plugins. Siete reglas, todas `extensions: ["*"]`: cinco `error` y dos `warning`. La alerta **nunca repite el valor**.
* **Capa de Policy Pack:** `load_linter_rules()` aplica la fusión en capas del pack activo y **sobrescribe severidades**, sin tocar `.gitpr.linter.yml` ni los plugins.
* **Hallazgos Estructurados (`findings_out`):** además de las cadenas de alerta, el motor devuelve los hallazgos normalizados del tipo canónico `src/domain/finding/` — esa lista es la que el baseline usa como huella y la que `gitpr check` serializa.
* **Puentes SAST Opt-In:** Semgrep, Gitleaks y Bandit corren en el camino de archivo completo **y** en el camino del diff, con deduplicación `[Gitleaks + Regex]`, enmascarado de valores (`AKIA****`) y un aviso cuando la herramienta está habilitada pero falta del `PATH`.
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=True)` en el flujo remoto, porque el puente ejecuta binarios contra archivos en disco — el árbol local, no el PR.
* **Informe Consolidado:** `generate_linter_report_content()` consolida errores regex + externos en `.gitpr/reports/linter/` — generado solo cuando hay violaciones.
* **Consciente del baseline:** las alertas se anotan con el estado del baseline antes de convertirse en informe, y el código de salida del flujo de lint sigue el bloqueo consciente del baseline.

### **7. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cifrado:** Genera la clave maestra `secret.key` en `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data`/`decrypt_data` para claves de IA, PAT de GitHub y tokens SCM de los forges.
* **Validación Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — 401 → bucle de reautenticación preservando el borrador.
* **Secretos en la TUI de Configuración:** Los campos `KIND_SECRET` se editan enmascarados, **nunca** muestran el valor en claro y se cifran con Fernet antes de escribirse.
* **Escaneo de Secretos en el Propio Flujo:** El linter — que corre en el hook pre-commit — es una puerta de credenciales con reglas idénticas en toda máquina.
* **Enmascarado en CI 🆕:** `src/infrastructure/ci/masking.py` elimina claves privadas, AWS access key ID/secret, tokens de GitHub (`gh[pousr]_`, `github_pat_`), Google (`AIza`), OpenAI (`sk-`), Slack (`xox[baprs]-`), `Bearer`, basic-auth en URL y asignaciones genéricas de contraseña **antes** de que el artefacto se serialice. El mismo módulo lo usa `publish_pull_request_use_case` para no filtrar un token en mensajes de error, y la extensión tiene un espejo en TypeScript (`masking.ts`).
* ⚠️ **Gaps de cobertura confirmados como aún abiertos:** la regla genérica sigue exigiendo comillas después del separador (`\s*[:=]\s*['\"]`), así que el formato `.env` **sin comillas** (`API_KEY=abc123`) sigue sin detectarse; los prefijos `ASIA…`, `github_pat_…` y `xoxc-`/`xoxd-` siguen fuera del ruleset del **linter** (el enmascarado de CI ya cubre `github_pat_`, pero no `ASIA…` ni `xoxc-`/`xoxd-`); y no hay límite izquierdo en el nombre de la clave, así que `mytoken` casa igual que `token`.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI como Única Fuente:** `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` y escribe la caché diaria **sin** el campo `download_url`.
* **Puerta Obligatoria (`enforce_update_required()`):** Devuelve `True` (tras imprimir ambas versiones y el comando pip) cuando la versión publicada es más reciente; `False` cuando está al día, cuando la versión remota es **desconocida (offline)** o cuando la verificación está apagada.
* **`check_and_update()`:** Solo consulta e **informa**, nunca instala.
* **Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` — no se anuncia; existe para la suite y para la automatización offline.
* **Versionado Centralizado:** `__version__` (**1.4.1** 🆕), `__lang_version__` (**v0.0.35** 🆕 — v0.0.34 → v0.0.35), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* **Estado del Release 🆕:** el bump de 1.4.1 está **commiteado** (`46c25c1`), pero el tag `v1.4.1` **no se creó** y la sección `[1.4.1]` de `CHANGELOG.md` está **en el working tree**, sin commitear. El motor de release también generó un artefacto PT-BR en `.gitpr/reports/release/develop_natan_20261018023153_RELEASE.md`, aún sin rastrear. El fix de los encabezados `[1.4.0]` (ítem de la ventana anterior) quedó **commiteado** en `1abb1b5`/`fc9d400`.

### **9. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Historial de mensajes, entrada multi-línea, barra de estado con bindings visibles. `ENABLE_COMMAND_PALETTE = False`.
* **Memoria por Rama (`src/chat_memory.py`):** Historial persistido por rama, con continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`. `/tests` (y los alias localizados) delega en el **mismo caso de uso** que usa la CLI.
* **Auto-Patching (F5), Actualización de Diff (F2), Exportación de Sesión (F6).**
* **Extractor Compartido:** F5 y `ctrl+s` llaman a `patch_extractor.extract_code_blocks()` — comportamiento visible idéntico, sin lógica duplicada.

### **10. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`; el directorio de perfil se crea **antes** de persistir.
* **5 Idiomas, 6 Diccionarios:** en_us (predeterminado/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Archivos Versionados:** `__lang_version__` (**v0.0.35**) controla la actualización de los paquetes (`langs/*.json`).
* **Cobertura — deuda reabierta 🆕:** **1620 claves** en `pt_br`, `pt_pt`, `es_es` y `fr_fr`, pero **1618** en `es.json` y `fr.json`. El código usa **1626** claves: **8 no existen en ningún diccionario** (publicación de PR y descripciones de las herramientas MCP `run_check`/`publish_pull_request`) y **2 claves viejas quedaron huérfanas**. La ventana anterior había cerrado en 1603/1603; el refactor de publicación y las descripciones MCP reabrieron el gap en 8 claves. ❌
* **Snapshot del Entorno (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado **inmediatamente antes** del `load_dotenv()` a nivel de módulo.
* **Caché con Indexación por Idioma:** Las respuestas de IA en caché incluyen el idioma actual en el keying MD5.
* **Fuente de Traducción en Lockstep:** una clave nueva debe existir en el código, en `langs/pt_br.json` (lista maestra), en los diccionarios FR/ES de `scripts/sync_all_langs.py` y en los valores curados de `scripts/fix_mangled_i18n_keys.py`.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en background durante llamadas de IA con caracteres braille y palabras de "pensamiento".
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceptan `force=`.

### **12. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini, DeepSeek y Ollama (local), con un primario/secundario por proveedor.
* **Modo JSON & Parámetros Deterministas:** `temperature=0.0` y `top_p=0.1`; fallback automático entre los proveedores configurados.
* **Timeout de IA:** predeterminado de **180s** (`GITPR_AI_TIMEOUT`).
* **Llamadas con `quiet`:** el release y las funcionalidades nuevas propagan `quiet` hasta la capa de IA, para que el spinner nunca corrompa una salida `--format json`.
* **Una llamada a la vez, con caché:** mentor y explain reutilizan la caché MD5 por prompt — volver a correr sobre un review sin cambios no cuesta nada.

### **13. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Keying por hash MD5 del diff y del prompt, con indexación por idioma.
* **Selección del Último Review (`resolve_last_review()`):** Elige el registro `review`/`fullreview` **más nuevo** para un par repo+rama — es la puerta de entrada del `gitpr fix` y de `gitpr mentor`.
* **Diff Revisado (`reviewed_diff`):** Campo en la cabecera del registro que guarda el diff efectivamente revisado, preferido por `fix/apply_fix.reviewed_diff()`.
* **Telemetría y Duración:** Persistencia de `duration_ms` y `meta_raw`.
* **Lectura para el Dashboard:** `scan_cache_files_for_dashboard()` es un **puente de pre-migración** — el dashboard lee el ledger, y el escaneo existe solo para reconstruir la serie desde la caché cuando el usuario lo elige en el asistente de migración.
* 🆕 **Efecto de la Sanitización del Skill:** como `get_skill_context()` ahora elimina el fragmento corrupto del prompt de PR, la clave MD5 cambia y las respuestas viejas contaminadas no se reutilizan.

### **14. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`) y Arqueología por Blame (`-b`).
* **Publicación Multi-Forge:** F3 crea el issue en el forge **configurado** (`GITPR_SCM_PROVIDER`) vía `provider.create_issue` — Azure DevOps lanza `ScmNotSupportedError`.
* **Map-Reduce para Issues:** El contexto por encima de ~90k tokens se divide y se unifica.
* **Manejo de 401:** Señalización de reautenticación sin cerrar la aplicación.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y la autoría histórica con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos vía `log_blame_metric()` con profundidad y número de commits analizados.
* **Ahora también alimenta al riesgo:** el lector de historial de la puntuación (`risk_history_reader.py`) usa la misma ventana de 90 días y clasifica bugs/reverts por patrón, sin llamar a la IA.

### **16. Servidor MCP e Invocación CLI Directa (`src/mcp_server.py`)**

* **18 Herramientas MCP Anotadas 🆕:** las 16 anteriores + **`run_check`** 🆕 (envelope JSON de solo lectura del pipeline de CI; `no_ia` vale `true` por defecto, sin review de IA salvo que se pida) y **`publish_pull_request`** 🆕 (publica en el forge configurado y **nunca commitea ni hace push**; devuelve el estado estructurado `success`/`needs_push`/`already_exists`/`auth_required`/`error`).
* **Parámetros Opt-In en el Contrato 🆕:** `review_code` y `full_review` aceptan `mentor: bool = False`; `generate_pr_description` acepta `explain: bool = False`. Son **puramente por parámetro** — nunca se combinan con un OR con los predeterminados del `.env`.
* **19 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Registry de Skills Alineado:** `mcp_server.SKILL_FILES` tiene **11 entradas** (`tests`, `explain` y `mentor` incluidas); `TestSkillRegistryAgreement` pasa.
* **Invocación CLI Directa:** `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca cualquier tool sin iniciar el servidor stdio; `gitpr-mcp --list` imprime el registry completo como JSON.
* **Aislamiento del Stdout Real:** `_write_real_stdout()` escribe en el `sys.__stdout__` original, con fallback a `buffer` o re-codificación con `errors='replace'` en páginas de código legacy de Windows.
* **Offload del Event Loop:** Decorador `_offload` (`anyio.to_thread.run_sync`) en todas las tools — el orden de los decoradores importa (`@mcp.tool` **encima** de `@_offload`).
* **Fallos como Errores Estructurados:** las herramientas nuevas devuelven un fallo **como dato** en lugar de lanzar — un agente que pregunta por el estado del baseline recibe el motivo del fallo, no una excepción.
* **Métricas MCP 🆕:** `run_check` y `publish_pull_request` escriben filas locales (`mcp:check`, `mcp:publish_pr`).

### **17. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Alcance por Repositorio:** Etiqueta `📁 Repository: owner/repo` y filtrado estricto por proyecto.
* **Escaneo Asíncrono con Overlay:** Worker thread en background con una `ProgressBar`; las pruebas esperan `workers.wait_for_complete()` antes de asertar.
* **Lectura del Ledger:** el dashboard consulta `telemetry.db` en el worker thread, renderizando las cinco secciones (costo, calidad, módulos, proveedores, ciclo) con los mismos renderers que usa la CLI.
* **Asistente de Migración (`src/ui/metrics_migration_app.py`):** ofrece importar los archivos legacy, importar **y** reconstruir desde la caché de IA, o "ahora no" — con una semántica de aborto que **nunca crea un ledger vacío** sobre archivos aún no importados.
* **Exportación Local:** CSV/JSON en `./.gitpr/metrics/export/` — `.gitpr/metrics/` está en el `.gitignore`, pero los **34 artefactos ya rastreados siguen rastreados** (`gitignore` no desrastrea).

### **18. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Reescrito en la ventana v0.0.17 — ver §36.** `src/metrics.py` opera sobre **filas del ledger**; `enrich_metrics_from_cache()` y `load_cache_token_summary()` se eliminaron.
* **Alcance por Repositorio:** Todos los eventos indexados por `repo_name`, resueltos por `src/infrastructure/git/identity.py` — que funciona en **cualquier** forge.
* **Eventos de Hook, Linter y Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. 🆕 `gitpr check` escribe `command="check"` con `findings_total`/`findings_errors` y el código de salida.
* **Exportación y Limpieza:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` con confirmación interactiva, `bundle`/`merge` para transportar y fusionar ventanas.

### **19. Sincronización de Idiomas de los Hooks Git**

* **Versionado Independiente:** `__scripts_version__` (**v0.0.4**, sin cambios).
* **Plantillas reescritas en la ventana anterior:** `post-checkout`, `post-merge` y `pre-push` llaman a `gitpr --quiet metrics hook-event <name>`, protegidas por `command -v gitpr` y `|| true`.
* **Mapeo de Sufijos (`HOOK_SCRIPT_SUFFIXES`):** Los códigos de interfaz (`es_es`, `fr_fr`) se traducen a los sufijos publicados (`.es`, `.fr`).
* **Elección vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detectar un cambio de idioma**.
* **`effective_hook_lang()`:** Resuelve el idioma efectivo; `--lang` dejó de descartarse en esa ruta.
* **Skip de Merge-Source:** `prepare-commit-msg` salta las fuentes `message|merge|squash|commit`.

### **20. Bridge de Linters Externos y Asistente Interactivo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Asistente `--linter-setup`:** Wizard con presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e inyección del bloque `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` con la cadena de fallback local → descarga → stale → embebido.
* **TUI de Errores del Linter:** `src/ui/linter_app.py` (Textual) muestra errores críticos y warnings; en modo hook/quiet imprime y hace `sys.exit(1)`.
* **Informe Markdown:** Consolidado en `.gitpr/reports/linter/` solo cuando hay violaciones.
* **La Capa SAST Es Hermana, No un Reemplazo:** los puentes Semgrep/Gitleaks/Bandit alimentan el **mismo** pipeline de informes — y ahora también `gitpr check`.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstracción Única (`ScmProvider` ABC):** `base.py` define el contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. y `ScmProviderError(provider, http_status, message)` — `http_status` 0 = fallo de red).
* **Registry y Factory:** `resolve_scm_provider()` selecciona por `GITPR_SCM_PROVIDER` (predeterminado `github`); `detect_provider_from_remote()` identifica el forge desde la URL de origin.
* **`list_pull_requests(repo, state, since, until)` — paginado y canónico:** los cuatro proveedores traducen los estados canónicos (`open`/`merged`/`closed`/`all`) al vocabulario nativo, paginan según el cursor de cada API y filtran por una ventana de fechas inclusiva. `list_open_pull_requests(repo)` es un envoltorio fino sobre la clase base.
* **Por qué el filtro es por fecha del estado:** tres de los cuatro forges devuelven algo más amplio para un PR fusionado (GitHub no tiene estado `merged`, Azure lo llama `completed`, Bitbucket dice `DECLINED`), así que cada uno filtra su propia respuesta contra la fecha que el estado implica.
* **`supports_merged_dates`:** atributo de clase, `False` en Bitbucket Cloud, que publica `merged_at` como **vacío** — declarado antes de la llamada.
* **`web_links.py`:** funciones puras (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) que normalizan cualquier formato de remote — `https`, `ssh://`, `git://`, scp-like, credenciales, puerto personalizado y subgrupos anidados de GitLab.
* **`get_pull_request(repo, pr_id)` — método concreto de la ABC:** el default lanza `ScmNotSupportedError` y cada forge lo implementa.
* **Encabezados de GitLab Sintetizados:** `changes[].diff` es un hunk suelto; `old_path`/`new_path` montan los encabezados `diff --git a/… / --- / +++`, honrando `/dev/null`. Y `overflow: true` **lanza** en lugar de revisar la mitad de un diff truncado.
* **`request_pull_request_reviewers` devuelve `list[str]` (ruptura de contrato):** Devuelve los logins **efectivamente anexados**, leídos del cuerpo del `201`. Las subclases de terceros deben actualizarse — **ítem heredado, aún sin documentar.**
* **Helpers Read-Only de GitHub:** `get_commit_author_login` y `get_user_login`.
* **Fail-Fast por Forge:** Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket exige `GITPR_SCM_USERNAME`; `create_issue` en Azure lanza `ScmNotSupportedError`.
* **Publicación de Release:** `provider.create_release()` usado por `gitpr release --publish`.
* **Publicación Compartida de PR 🆕:** el contrato de los proveedores no cambió; lo que cambió es que la publicación pasa por **un único caso de uso** (`src/application/use_cases/publish_pull_request.py`), usado por `--no-edit` y por la herramienta MCP. Valida título/cuerpo, exige `provider.token`, opcionalmente comprueba si existe un PR y si la rama no está pusheada (`check_remote_state`), enmascara mensajes de error y devuelve `PublishPrOutcome`.
* **Artefactos:** Glosario + ADR-001/ADR-005; la familia `docs/scm-multiforge.*.md` en 5 idiomas; 9 archivos de prueba, **307 escenarios**.

### **22. Subcomando `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Flujo:** `git log` entre `--since` y `HEAD` → clasificación por Conventional Commits → bump semántico sugerido (`--version <x.y.z>` lo sobrescribe) → ensamblado del changelog → resumen ejecutivo de IA opcional → *anteposición* a `CHANGELOG.md`.
* **El Rango Dejó de Venir de `git describe`:** el origen del rango es la **sección anterior del propio changelog**, mediante dos anclas: el hash más nuevo de esa sección y, como fallback, el tag de la versión de la sección; sin ninguno de los dos, degrada al último tag **con un warning visible**.
* **`_drop_released()` — la segunda línea de defensa:** cualquier commit cuyo hash corto aparezca en **otra** sección de versión se descarta, con un conteo. Cuando no sobrevive nada, la ejecución **aborta** con `❌ Nothing new to release`.
* **Cada viñeta pasó a ser un enlace:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **El fallo de un PR fusionado por merge commit:** una segunda pasada (`git log --merges`) construye `hash → PR` desde la ascendencia; un merge que no es un pull request no presta un número, y la corrección **no es retroactiva**.
* **Contribuidores resueltos por la misma escalera que los revisores:** `get_commit_author_login` primero, luego `email_to_handle`, cacheados en `~/.gitpr/cache/contributors.json`.
* **El Límite de Sección Era un Bug Latente:** `upsert_changelog(force=True)` reemplaza desde la línea `## [x.y.z]` hasta el siguiente encabezado de **nivel 2**; un `###` como límite dejaba el cuerpo viejo vivo por encima del nuevo.
* **Clasificador (`src/commit_classifier.py`)** y **Builder con Secciones Traducibles (`src/changelog_builder.py`)** — el builder tiene la dataclass `LinkContext`, y el clasificador tiene `pr_number_from_merge()`.
* **Bump Semántico (`src/version_bump.py`)** y **Publicación** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opciones en el subcomando.**
* **Rupturas declaradas:** `ReleaseNotesResult` ganó `previous_version`; `build_release_section` ganó `links` (retrocompatible).
* **Estado del release 1.4.1 🆕:** la sección `[1.4.1] - 2026-10-18` fue generada por el motor corregido y está **en el working tree**; clasifica cinco commits (`8557125`, `b6382b5`, `a50f21c`, `525a1ec`, `46c25c1`) y todavía arrastra `fc9d400`/`1abb1b5` como documentación. El artefacto PT-BR correspondiente está en `.gitpr/reports/release/` (sin rastrear). Falta el commit y el tag.
* **Artefactos:** `docs/release-notes.*.md` en 5 idiomas, ADR y `tests/test_web_links.py`.

### **23. Subcomando `gitpr config` — TUI de Configuración**

* **Pantalla Master-Detail (`src/ui/config_app.py`):** Categorías a la izquierda, campos a la derecha, editados en línea. Cabecera con búsqueda (`/`) y un contador de cambios pendientes; pie `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schema Declarativo (`src/config_schema.py`) — la única fuente de verdad:** **16 categorías** y **80 `ConfigField`**, de los cuales **8 son avanzados**. Cada campo declara su categoría, el tipo de widget, `show_if`, validadores, marcadores de versión y acciones de descarga. Sin categoría nueva en esta ventana — las 9 `GITPR_CHECK_*` **aún no** están en el schema.
* **Categorías `policy` y `baseline`:** los campos del pack (habilitación y techo de caracteres) y los cuatro del baseline, incluida la ruta del archivo y el switch que exige el checksum del lockfile.
* **Sección Skills Alineada:** `SKILL_LABELS` tiene **11 entradas** (`tests`, `explain`, `mentor`).
* **Filtrado por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` según `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` bajo `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` bajo `[Azure DevOps]`.
* **Búsqueda Global (`/`):** Coincide con la clave o la etiqueta en todas las categorías e **ignora el filtro de visibilidad**.
* **Validación en Dos Capas:** **Offline** bloquea `F2` con un error en línea; **online** (solo credenciales cambiadas en la sesión) corre en un worker con timeout de 10s y solo bloquea ante `401`/`403`.
* **Restaurar (`Ctrl+R`):** Elimina la línea del `.env` en lugar de reescribir el valor predeterminado; `Esc` con cambios pendientes pide confirmación.
* **Sección Skills — la única con alcance de proyecto:** Edita los `.gitpr/skill/*.md` del proyecto resueltos desde el directorio de invocación, escribiendo atómicamente y preservando CRLF/LF.
* **Descargas Forzadas:** Botones que fuerzan la redescarga de smart-excludes, traducciones, presets y thinking words vía `force=`.
* **Módulo Ligero de Enlaces (`src/doc_links.py`):** `doc_url()` salió de `core.py` para que la UI pueda obtener el enlace sin importar los SDKs de IA.
* **Pruebas:** 5 archivos — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Deuda conocida:** `gitpr -h config` abre la TUI e ignora `-h` — la puerta `if ctx.invoked_subcommand is not None: return` (main.py:678) corre antes del bloque `help_flag`.

### **24. Registro General de Uso (`src/usage_log.py`)**

* **Una Línea por Comando:** Escribe en `~/.gitpr/logs/<uuid5>.log`, **un archivo por día**, con el comando, los argumentos, el repositorio, el usuario y el timestamp.
* **Nombre Derivado de la Fecha:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` — dos procesos concurrentes no pueden discrepar sobre cuál es el archivo de hoy.
* **Escritura Síncrona (decisión explícita):** A diferencia de `log_local_metric`, que usaba un hilo daemon y perdía la escritura si el proceso terminaba antes.
* **Nunca Imprime:** El servidor MCP reserva la stdout para JSON-RPC; el módulo tampoco lanza excepciones nunca.
* **Un Solo Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms en vez de ~150 ms en Windows, en *cada* ejecución.
* **Control:** `GITPR_SHOW_LOGS` (predeterminado `"true"`); desactivado en `tests/conftest.py`.
* **Artefactos:** `docs/usage-log.*.md` en 5 idiomas; `tests/test_usage_log.py` (27 escenarios).

### **25. Subcomando `gitpr fix` — Hallazgos de Review como Patches Revisables (`src/fix/`)**

* **El Pipeline Real:** último review de la caché (`resolve_last_review`) → **una** llamada de IA → extracción y validación del diff unificado → `git apply --check` → clasificación determinista → dry run o escritura → historial.
* **Paquete de 8 Archivos:** `patch_provenance.py`, `patch_extractor.py` (compartido con el chat), `patch_safety_classifier.py` (puro, sin I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Clasificación Determinista:** Rechaza un patch que atraviese más de un archivo o hunk, que toque una ruta sensible, que reviente el presupuesto de líneas, que **borre una línea con pinta de llamada**, que venga marcado de baja confianza o que falle el `git apply --check`. El `--force` **nunca** sortea la aplicabilidad, solo la clasificación.
* **Escritura Opt-In:** El dry run es el comportamiento predeterminado; cualquier mutación exige `--apply` (o una frase escrita con `--force`).
* **Pruebas:** 10 archivos en `tests/fix/` (**204 escenarios**) con fixtures de repositorio git real.

### **26. Subcomando `gitpr review-pr` — Review de PR Remoto (`src/review/`)**

* **Qué Es:** Orquestación, **no** un segundo motor de review — el motor existente, el linter y el renderizador alimentados con un diff que vino de otro sitio.
* **Paquete de 5 Archivos:** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Read-Only por Defecto:** `--post-comment` es el **único** camino que escribe en el forge. La tool MCP `review_remote_pr` ni siquiera recibe el argumento.
* **El baseline se resuelve antes de la primera llamada de red**, y los avisos de la puerta se fusionan en el informe/comentario.
* **`render.py` ganó el parámetro de riesgo:** `compose_review_content` / `render_review_result` **anteponen** la sección `⚡ Risk Assessment` por encima de las alertas del linter, cuando `GITPR_RISK_INCLUDE_IN_REVIEW` está activo.
* **Artefactos:** `docs/review-pr.md` + `.pt_br.md` (los 3 idiomas restantes **siguen pendientes**), `docs/code-review-ia.*.md` (5), ADR-005 y `glossary-review-pr.md`.
* **Pruebas:** 4 archivos en `tests/review/` (**97 escenarios**).

### **27. Resolución de Identidad del Revisor (`src/reviewer_resolution.py`)**

* **El Bug (dos fallos silenciosos encadenados):** (1) **Prefill vacío** — `handles` solo desde `email_to_handle()`; (2) **Attach no verificado** — valores pasados *verbatim*, GitHub respondió **201 sin anexar a nadie** y `_request()` retornó sin lanzar.
* **Módulo:** Un plano, sin I/O propio, **y que nunca lanza**. `resolve_candidates()` (antes de la TUI) y `resolve_typed_reviewers()` (en el momento del attach); `match_candidate()` casa exacto y normalizado por login, nombre o correo.
* **Escalera de Resolución:** handle ya conocido (sin petición) → casamiento exacto con una persona sugerida → lookup por correo → validación del login en el forge.
* **Lo que no resuelve nunca se envía:** Sale en `ResolutionOutcome.dropped` como `(value, reason_i18n)` y se descarta con aviso visible.
* **La Misma Escalera Sirve al Release:** `release_engine` reutiliza `get_commit_author_login` + `email_to_handle` para convertir correos de commit en logins clicables.
* **Pruebas:** `tests/test_reviewer_resolution.py` (18) + ampliaciones en `test_github_provider.py` (72), `test_pr_publish_app.py` (55), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) y `test_main_suggest_reviewers.py` (8).

### **28. Subcomando `gitpr demo` — Recorrido Guiado sobre Ejemplos Grabados (`src/demo/`)**

* **Qué Es:** Un recorrido interactivo que muestra las tres salidas principales de GitPR — mensaje de commit, review y descripción de PR — **sin clave de API, sin repositorio git y sin red**.
* **Pipeline Real, Respuesta Falsa:** `FakeAIProvider` refleja `src.ai_providers.call_ai_model` **argumento por argumento**; `demo_pipeline()` parchea `src.core` y `src.metrics` para que el pipeline de producción corra **intacto** y solo se intercambie la fuente de la respuesta.
* **El Aislamiento Es un Requisito, No un Detalle:** El recorrido **no puede** tocar el `~/.gitpr` del usuario ni la red — garantizado por `tests/demo/conftest.py`.
* **Paquete:** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (dos ejemplos localizados en los 5 idiomas). Los escenarios son **módulos Python, no JSON**, para que viajen dentro del wheel.
* **TUI:** `src/ui/demo/` con un modal de ayuda; el diff va cercado como ```` ```diff ````.
* **CLI:** `gitpr demo` con `--scenario`, `--lang` y `--no-tui`.
* **Pruebas:** 6 archivos en `tests/demo/` (**155 escenarios**).
* **Artefactos:** `docs/demo.*.md` en 5 idiomas, `README.*` (5) y las métricas de ejemplo del recorrido.

### **29. Badge de GitPR y el Comando `gitpr badge` (`src/branding/`)**

* **Qué Es:** Un badge público que certifica que un pull request pasó por una verificación local de calidad, y un comando que imprime el snippet de adopción para el README del proyecto.
* **URL Estática a Propósito:** Es un Markdown de shields.io que GitPR **nunca** consulta — publicar un pull request no puede pasar a depender de que un tercero esté en línea.
* **Medición Honesta (`badge_data.py`):** `collect_linter_counts()` devuelve `None` cuando no hay reglas de linter configuradas, porque una lista vacía significa "nada se verificó", no "nada se encontró".
* **El Badge Nunca Bloquea una Publicación:** Cada camino de fallo se captura y degrada a "sin badge".
* **Punto de Inyección Único:** `attach_pr_badge()` en `src/main.py`. Anexado idempotente por marcador.
* **Comando `gitpr badge`:** `--readme` y `--style`. **Solo imprime** — el README nunca se modifica.
* **Configuración:** `GITPR_BADGE` (predeterminado `true`) — opt-out **de solo lectura**.
* **Pruebas:** 7 archivos en `tests/badge/` (**83 escenarios**).
* ⚠️ **Impacto del refactor de publicación 🆕:** `_publish_pr_directly` ahora delega en el caso de uso compartido, que devuelve `auth_required` cuando `provider.token` está vacío. El proveedor falso de `tests/badge/test_badge_paths.py` no declara `token`, así que `create_pull_request` nunca se llama y **7 escenarios fallan** con `KeyError: 'request'`. El comportamiento del producto es correcto (el proveedor real siempre lleva el token); lo que hay que actualizar es la fixture.
* **Artefactos:** `docs/badge.*.md` en 5 idiomas y el survey dedicado.

### **30. Subcomando `gitpr split` — Commits Atómicos por Hunk (`src/split/`)**

* **Qué Es:** Un árbol de trabajo con varios asuntos ya no se convierte en un commit-blob. `split` lee el diff no commiteado, pide a la IA que particione los hunks por **intención lógica** y propone un commit atómico por asunto. El árbol nunca se reescribe: los archivos terminan byte a byte idénticos al inicio.
* **Paquete de 6 Archivos:** `split_plan.py`, `hunk_parser.py` (puro), `hunk_grouper.py`, `generate_split_plan.py` (read-only), `apply_split_plan.py` (**el único que muta**) y `__init__.py`.
* **La Garantía de Árbol Intacto Es Estructural:** `selective_stager.py` pre-valida **cada** grupo contra un índice temporal en HEAD y usa un `GIT_INDEX_FILE` desechable.
* **Destructivo para el Índice, No para el Árbol:** `--apply` resetea el índice a HEAD antes de hacer el staging, así que lo que el usuario ya tenía en stage queda fuera de stage — documentado explícitamente.
* **Configuración:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) y `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Los valores cero o negativos se **ignoran en favor del predeterminado**.
* **Pruebas:** 6 archivos en `tests/split/` (**101 escenarios**).
* **Artefactos:** `docs/split-command.md` en 5 idiomas, ADR-006, `glossary-gitpr-split.md`, spec/plan y survey.

### **31. Escaneo de Secretos Integrado (`src/security_ruleset.py`)**

* **Qué Es:** Siete reglas que corren en **cada** invocación del linter, fusionadas al final de `load_linter_rules()` después de las reglas del proyecto y de los plugins.
* **Las Siete Reglas:** cinco `error` bloqueantes — ID de clave de acceso AWS, token de GitHub, token de Slack, clave de API de Google y bloque de clave privada — y dos `warning`: URL de conexión a base de datos con credenciales y asignación genérica de credencial (`password = "…"`), esta última tras un filtro de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Por Qué en el Paquete y No en la Plantilla:** Una puerta de secretos debe comportarse **igual** en toda máquina y en toda CI, así que las reglas viven en el paquete y no pueden ser reemplazadas por `--skill` ni reescritas por el wizard.
* **Gaps de Cobertura (v1) — confirmados aún abiertos:** la asignación **sin comillas** no se detecta (`API_KEY=abc123`, el formato `.env`); faltan los prefijos `ASIA…`, `github_pat_…` y `xoxc-`/`xoxd-`; y no hay límite izquierdo en el nombre de la clave. El enmascarado de CI cubre `github_pat_`, pero no `ASIA…` ni `xoxc-`/`xoxd-`; el linter no cubre ninguno de los tres.
* **Configuración:** `GITPR_LINTER_SECURITY` (predeterminado `true`; opt-out fail-open) y `GITPR_LINTER_SECURITY_DISABLED_RULES` (separadas por `;`).
* **Pruebas:** `tests/test_security_ruleset.py` (**61 escenarios**).
* **Artefactos:** ADR-007, `glossary-gitpr-secret-scanning.md`, spec/plan/survey y 3 informes de tarea.

### **32. Puentes SAST — Semgrep, Gitleaks y Bandit (`src/infrastructure/linter/external/`)**

* **Qué Es:** Una capa **opt-in** que conecta escáneres de seguridad de terceros al pipeline de linter existente.
* **Alcance Acotado:** Corren **solo** cuando están habilitados y **solo** sobre los archivos tocados por el diff.
* **Contrato Común:** `ExternalLinterBridge` (ABC) con ejecución endurecida de `subprocess` (`shell=False`, timeout estricto, `stdin=DEVNULL`, UTF-8 con `errors='replace'`). El modelo `NormalizedFinding` vive en `src/domain/finding/`, y `base_bridge` lo re-exporta.
* **La Deduplicación Es el Punto:** `deduplicate_secret_findings()` fusiona los hallazgos de Gitleaks y del ruleset de regex en el mismo archivo y línea en **una sola** entrada confirmada como `[Gitleaks + Regex]`.
* **Secretos Enmascarados:** `mask_secret_value()` garantiza que el valor nunca llegue al hallazgo, al log ni a la telemetría.
* **Resolución en Tres Capas:** `load_sast_config()` lee defaults → `.gitpr.linter.yml` (bloque `sast`, con fallback a `linter.external`) → variables `GITPR_SAST_*`. **Todo es `false` por defecto**.
* **Degradación Elegante:** Una herramienta habilitada pero ausente del `PATH` emite un warning y la ejecución continúa.
* **Pruebas:** 4 archivos en `tests/infrastructure/linter/external/` (**14 escenarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + `tests/domain/linter/test_structured_findings.py` (44).

### **33. Subcomando `gitpr tests generate` — Generación de Suites por IA (`src/domain/tests_generation/`, `src/application/`)**

* **Qué Es:** Genera archivos de prueba completos y ejecutables a partir del diff actual, de un archivo específico o de un hallazgo de review, **respetando la convención del repositorio** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Capa de Dominio:** `TestFramework` (enum) y las dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest`; `detect_test_framework()` detecta desde archivos de configuración, manifiestos y el contenido del directorio de pruebas; `build_test_scaffold()` calcula la ruta de destino convencional por framework.
* **Capa de Aplicación (`generate_test_file.py`):** Orquesta la detección, la resolución del scaffold, el prompt, la IA, el parsing JSON y la escritura opcional. `validate_test_syntax()` corre el toolchain local (`php -l`, `node --check`, `python -m py_compile`) — un fallo de validación es un **warning**, no un error.
* **Presentación:** El grupo `tests` con `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); el dry run es el comportamiento predeterminado y sobrescribir una prueba existente pide confirmación con **No** preseleccionado. El chat delega `/tests` en el **mismo** caso de uso.
* **Deuda:** el tipo `tests` está en `SKILL_LABELS` y en `mcp_server.SKILL_FILES`, y las claves `__()` se tradujeron. **Sigue sin tema en `docs/`** — solo la plantilla de skill y la spec.

### **34. Subcomando `gitpr explain` y la Flag `--explain` — Reviewer Guide (`src/domain/pr/`)**

* **Qué Es:** Una guía centrada en quien va a **revisar** — qué cambia, por qué cambia, dónde enfocarse y cuál es el riesgo de regresión.
* **Capa de Dominio (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, el renderizador `build_explain_markdown()` y `parse_explain_payload()`, que tolera salida no-JSON y **detecta placeholders** `[FILL]`/`[TODO]` para señalar evidencia insuficiente.
* **Capa de Aplicación (`generate_pr_explanation.py`):** Resolución del proveedor, validación de la clave, carga del contexto de skill (`explain`), prompt, invocación y parsing.
* **Dos Puertas de Entrada:** `gitpr explain` (`--provider`, detección de diff ausente antes de cualquier IA) y la flag `--explain` en la CLI raíz, que anexa la guía al cuerpo de la descripción del PR **y** al payload JSON. 🆕 La extensión expone el mismo camino en `GitPR: Generate PR Draft (with Reviewer Guide)`.
* **Configuración:** `GITPR_EXPLAIN_BY_DEFAULT` (predeterminado `false`).
* **Deuda:** `explain` está en `SKILL_LABELS` y en `mcp_server.SKILL_FILES`, y las claves se tradujeron. **Sigue sin tema en `docs/`.**

### **35. Suite Determinista y CI (`.github/workflows/tests.yml`)**

* **Qué Es:** El workflow que corre la suite en **Python 3.10** (el piso declarado en `pyproject.toml`) y **3.13**, con `fail-fast: false`.
* **`tests/conftest.py` Hermético:** Fija `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` y `LANG_VERSION` en la versión del código, más `GITPR_SHOW_LOGS=false` y `GITPR_SKIP_UPDATE_CHECK=true`.
* **El Orden Importa:** `src.updater` se importa **antes** de que se definan las variables de idioma, porque `i18n` snapshotea `os.environ` en `AMBIENT_ENV_KEYS` al importar.
* **Estabilidad de las Pruebas de Worker:** `test_config_app.py` y `test_metrics.py` esperan `workers.wait_for_complete()` antes de asertar.
* **Dependencia del Workflow:** El runner limpio no tiene `~/.gitpr`, y `test_demo_isolation.py` aserta que el perfil existe — el job crea explícitamente el directorio y un `.env` vacío.
* **En esta ventana la suite creció 153 escenarios y la deuda de pruebas cambió de lado 🆕:** entraron 9 módulos nuevos (check, infraestructura de CI, plantillas y skill), pero aparecieron **10 fallos nuevos** — 7 de fixture en el badge y 3 de i18n — más los 2 viejos de mentor. El balance es **2938 aprobadas de 2952**.
* **Workflow de la Extensión 🆕:** `.github/workflows/extension.yml` corre `npm install`, construye, prueba y empaqueta el VSIX; con tags `ext-v*` publica en el VS Code Marketplace y Open VSX, saltando cada paso cuando `VSCE_PAT`/`OVSX_PAT` está vacío.

### **36. Ledger de Telemetría SQLite (`src/ledger.py`, `src/infrastructure/git/identity.py`)**

* **El Problema No Era el Formato, Era el Join:** los eventos se escribían como archivos JSON por comando, desde un **hilo daemon**, y luego se reconectaban a la caché de IA mediante un match heurístico por `(repo, branch, action, minute)` que dependía de **tres mapas comando→carpeta divergentes**.
* **La Escritura Es Síncrona, y Esa Es la Decisión:** `log_command_metric()` resuelve el contexto de git **una vez por proceso** y escribe la fila en el momento. Los tokens, el modelo y el proveedor se **copian de la metadata del proveedor en el momento de la escritura**.
* **Schema Versionado:** `SCHEMA_VERSION` + `PRAGMA user_version`, claves UUID, WAL + `synchronous=FULL`, y `LedgerVersionError` para rechazar un bundle más nuevo **antes** del `ATTACH`.
* **La Invariante "Nunca Nace Vacío":** `ensure_ledger()` solo crea la base de datos **junto** con la absorción del JSON legacy (originales movidos a `~/.gitpr/metrics_legacy/`).
* **Una Ventana, Un Significado:** `--since`/`--until`/`--days` acotan **toda** superficie de lectura; la sección de ciclo asume los últimos 30 días y **nombra la ventana en su propio encabezado**.
* **Cada Métrica Se Calcula Una Vez y Se Renderiza Una Vez:** los pares `(style, text)` hacen que la CLI y el dashboard de Textual cuenten la **misma** historia.
* **Costo con Tabla Integrada y Override:** precios de lista de los cuatro modelos, sobrescritos por `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Ambas tarifas son obligatorias**; `ollama`/`local` cuestan cero; un `GITPR_METRICS_CURRENCY` que no sea USD **descarta** la tabla integrada; un total parcial dice `Total (partial)`.
* **`modules` Normalizado:** las rutas pasan a dos segmentos (`src/fix`), `(root)` y `(no module)` para los comandos sin diff.
* **Transporte por Bundle:** `bundle` escribe la ventana como un `.db` autocontenido; `merge` lo adjunta, inserta por UUID, migra un bundle viejo y rechaza uno más nuevo.
* **`cache_backfill` Se Queda Fuera:** las filas reconstruidas desde la caché se excluyen de los agregados de ejecución.
* **Identidad Git sin Forge (`identity.py`):** etiqueta de repo, rama y autor en **una sola** llamada a subprocess, válida en cualquier forge.
* **La Sección de Ciclo Es la Única Que el Ledger No Responde:** lista los PRs fusionados vía `ScmProvider.list_pull_requests`; cada fallo degrada a una línea — `Not read: <reason>` — nunca a un stack trace.
* **Presentación Completa:** grupo `gitpr metrics` con 8 subcomandos, un dashboard que lee el ledger en un worker thread y el asistente de migración de tres opciones.
* **Configuración:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY` y `.gitpr/metrics/` en el `.gitignore`.
* **Pruebas:** `tests/test_metrics.py` (**164 escenarios**); `tests/scm/*` cubre la paginación y `within_window`; `test_commit_classifier.py` (30) cubre `pr_number_from_merge`.
* **Artefactos:** ADR-008, `glossary-metrics-telemetry.md`, `docs/plans/develop_natan/20260927_metrics_audit_plansfacts.md`, survey y `docs/metricas-telemetria.*.md` en los 5 idiomas.

### **37. Subcomando `gitpr mentor` y la Flag `--mentor` — Mentor Mode (`src/domain/mentor/`)**

* **Qué Es:** Un review dice *qué* está mal y casi nunca *por qué importa*. Mentor Mode enriquece los hallazgos con **qué está pasando**, **por qué importa**, una **analogía** cotidiana y los **conceptos que estudiar**.
* **Capa de Dominio Pura (`mentor_explanation_builder.py`):** sin I/O, convierte el payload de la IA en un `MentorExplanation` validado y exige **honestidad epistémica**: con evidencia insuficiente, la explicación **lo dice**; las URLs alucinadas se **eliminan**; los IDs desconocidos se **rechazan**, preservando el orden de los hallazgos originales.
* **Capa de Aplicación (`generate_mentor_explanation.py`):** recolecta candidatos, **lotes de como máximo 10 hallazgos**, ordenados por severidad, reutilizando la caché MD5 por hash de prompt.
* **Dos Puertas de Entrada:** `gitpr mentor [--finding <id>] [--provider <name>]` corre sobre el **último review persistido**, y la flag `--mentor` anexa la sección al review normal. 🆕 La extensión expone `GitPR: Review (Junior Mentor Mode)` y MCP ganó `mentor: bool` en `review_code`/`full_review`.
* **Opt-In Estricto:** `GITPR_REVIEW_MENTOR_MODE` (predeterminado `false`) y `GITPR_MENTOR_INCLUDE_ANALOGY` (predeterminado `true`), editables en la TUI de configuración.
* **Skill y Registry:** `.gitpr.mentor.md` registrado en `SKILL_FILES_BY_TYPE`, en `mcp_server.SKILL_FILES` y en `SKILL_LABELS`, con plantillas en **5 idiomas**.
* **Pruebas:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) y `tests/test_mentor_cli.py` (4) — **2 de estos fallan** por el bug de aislamiento heredado (ver §Pruebas).
* **Artefactos:** `docs/mentor-mode.*.md` en **5 idiomas**, ADR-009, `glossary-gitpr-mentor.md`, spec e informes de tarea.

### **38. Subcomando `gitpr risk` — Puntuación de Riesgo Determinista (`src/domain/risk/`)**

* **Qué Es:** Una puntuación de **0–100** con un nivel `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculada **sin IA** a partir de señales locales, para que quien revisa sepa dónde mirar primero — y para que el mismo diff reciba el mismo número dos veces.
* **Capa de Dominio Pura (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums y dataclasses (`RiskLevel`, `RiskSignal`, `RiskEvidence`, `FileRisk`, `PullRequestRisk`); pesos, umbrales, patrones de rutas críticas, glob matching y detección de pruebas/no-ejecutables cargados desde YAML; cálculo por archivo con **clamp 0–100** y agregación 50/30/20.
* **La Fórmula Está Declarada, No Aprendida:** **50%** el archivo de mayor riesgo, **30%** el promedio ponderado por líneas y **20%** la evidencia crítica. Reglas de escalado obligatorias: una puntuación ≥ 80 sube a `HIGH` como mínimo; un blocker o una puntuación ≥ 90 fuerza `CRITICAL`.
* **Adaptadores de Infraestructura:** `risk_history_reader.py` lee `git log` y extrae commits, bug fixes y reverts dentro de una ventana de **90 días**; `test_matcher.py` casa archivos de prueba ↔ producción por convención.
* **Caso de Uso (`calculate_risk.py`):** correlaciona las secciones del diff, los hallazgos, el test matching y el historial en `FileRisk`/`PullRequestRisk`, **degradando con un warning** cuando una señal no está disponible. Aplica el policy pack por encima de la configuración base.
* **Tres Consumidores:** el comando `gitpr risk`, la sección `⚡ Risk Assessment` anexada a los reviews y al review remoto de PR, y el **prompt de IA** como contexto **de solo lectura**. 🆕 `gitpr check` usa el mismo `execute_calculate_risk` para la puntuación preliminar y la agregada.
* **Opt-Out:** `GITPR_RISK_INCLUDE_IN_REVIEW` (predeterminado `true`).
* **Configuración por Proyecto:** `templates/gitpr.risk.yml` (o `.gitpr/skill/gitpr.risk.yml`) sobrescribe pesos, umbrales y rutas críticas. Los pesos negativos solo se aceptan para señales **mitigantes**.
* **Pruebas:** `tests/domain/risk/` (2 archivos, 10), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) y `tests/test_risk_cli.py` (4).
* **Artefactos:** `docs/risk-scoring.*.md` en **5 idiomas**, ADR-010, `glossary-gitpr-risk-scoring.md` e informes de tarea.

### **39. Subcomando `gitpr policy` — Policy Packs Versionados (`src/domain/policy/`, `src/infrastructure/policy/`)**

* **Qué Es:** Un **pack YAML versionado** que lleva, en un solo artefacto, las decisiones de calidad de un repositorio: skills, reglas de linter, severidades, rutas críticas, pesos de riesgo y convenciones de PR/commit.
* **Capa de Dominio (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`):** parsing del manifest con un **schema cerrado** (una clave desconocida es un error), verificación de compatibilidad y credenciales, **checksum SHA-256** y un resolver de grafo con precedencia y composición.
* **Capa de Infraestructura:** `local_policy_repository.py` resuelve el pack desde **tres orígenes** (`.gitpr/policies`, `~/.gitpr/policies` y el paquete embebido) y `policy_pack_loader.py` lo carga.
* **Casos de Uso:** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` y `validate_policy_pack`.
* **Cuatro Packs Integrados:** `laravel-quality`, `node-quality`, `php-security` y `vue-quality`, cada uno con `policy.yml` y `linter.yml`, distribuidos vía **`package-data`**.
* **Consumo Integrado en Cuatro Puntos:** `calculate_risk`, `config.load_linter_rules` (fusión en capas + sobrescritura de severidades), `core.get_skill_context` y la **clave de caché del PR** (`cache_scope`). 🆕 `gitpr check` resuelve el pack **antes que todo lo demás** y sale con código 2 si es inválido.
* **CLI:** `gitpr policy {list, validate, show, use, off, install, init}`, con confirmación (`--yes`/`--force`), detección de stack en `init` y registro de la elección en `.gitpr/policy.lock.yml`.
* **Configuración:** `GITPR_POLICY_ENABLED` y `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`, ambas en la categoría `policy` del schema.
* **Retrocompatibilidad:** sin un pack activo, el comportamiento se **preserva** — cubierto por una prueba de integración.
* **Dependencia:** `packaging>=23.0` (SemVer/PEP 440), declarada en el `Pipfile` y en `pyproject.toml`.
* **Pruebas:** `tests/domain/policy/` (4 archivos, 126), `tests/application/use_cases/` (4 archivos de policy, 89), `tests/test_policy_cli.py` (26) y `tests/integration/test_policy_effect_on_review_linter_risk.py` (31).
* **Artefactos:** `docs/policy-packs.*.md` en **5 idiomas**, ADR-011, `glossary-policy-packs.md`, spec/plan/survey.

### **40. Subcomando `gitpr baseline` — Baseline y Supresiones Auditables (`src/domain/baseline/`)**

* **Qué Es:** Los linters y los reviews vuelven a reportar los mismos hallazgos preexistentes en cada ejecución. El baseline registra el **snapshot de los hallazgos que el repositorio ya conoce** en `.gitpr/baseline.json`, calcula la huella de cada uno de forma estable entre ejecuciones y permite que el pipeline clasifique lo que es nuevo — **solo `new` bloquea**.
* **Capa de Dominio (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`):**
  * **Huella estable:** identidad de la regla, ruta normalizada, líneas y **sha256 de la línea con los espacios colapsados** — la línea cruda **nunca** se almacena, así que el baseline no es un archivo donde los secretos se filtren por accidente.
  * **Manifest canónico:** JSON canónico con checksum, validación que rechaza una clave desconocida, una versión de schema/huella fuera del rango soportado, una invariante de supresión/deuda rota, una huella duplicada y un rango de líneas inválido.
  * **Comparador:** clasificación, conteos por estado y resolución de entradas — `resolved` y luego `back` se lee como **nuevo**, que es la lectura conservadora.
  * **Suppression Policy:** supresiones, deuda aceptada, overrides y validadores, con casamiento en capas (entrada × overrides × policy pack) por **especificidad y precedencia**, más el seguimiento de deuda vencida.
* **Cinco Casos de Uso:** `baseline_gate` (la puerta de entrada única del pipeline), `baseline_summary` (vista JSON **de solo lectura** para IDEs y agentes MCP), `compare_against_baseline`, `create_baseline` (creación/actualización con procedencia, checksums y **preservación de las decisiones humanas**) y `suppress_finding` (cuatro ámbitos).
* **Integración con Policy Pack:** el pack puede llevar un bloque `baseline:`, validado por los **mismos** validadores, compuesto por el resolver como una capa etiquetada y reportado por `policy validate`.
* **El Repositorio Es el Único Que Escribe:** `local_baseline_repository.py` es el **único** lector/escritor de `.gitpr/baseline.json` y `.gitpr/baseline.overrides.yml`, con escritura atómica, una puerta de checksum/versión y un camino de reparación.
* **Configuración:** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` y `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`, en la categoría `baseline`.
* **Opt-In y No-Op:** apagada o sin un baseline existente, la funcionalidad **no lee ningún archivo y no imprime nada**.
* **⚠️ La Excepción Deliberada:** un archivo de baseline que existe pero **no puede aplicarse** hace que el lint, el review, el full review, la auditoría de archivo, el review remoto y el riesgo **aborten con código de salida 1**. 🆕 En `gitpr check`, un baseline corrupto sale con **código 2**, distinguiendo un error de ejecución de un hallazgo bloqueante.
* **Alcance:** CLI (6 subcomandos, todos con `--format json`), MCP (`get_baseline_status` + `baseline://summary`), review remoto de PR (resuelto **antes de la primera llamada de red**) y el flujo de lint.
* **El Refactor Que la Funcionalidad Exigió:** `NormalizedFinding` pasó a ser un tipo canónico en `src/domain/finding/` (con `snippet_hash` y `rule_version`).
* **Pruebas:** `tests/domain/baseline/` (5 archivos, **126**), `tests/application/use_cases/` (5 archivos, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) y `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34).
* **Artefactos:** `docs/baseline-suppressions.*.md` en **5 idiomas**, ADR-012, `glossary-baseline.md`, spec/plan/survey e informe de tarea.

### **41. Subcomando `gitpr check` — Puerta de CI Unificada (`src/domain/check/`, `src/infrastructure/ci/`) 🆕**

* **Qué Es:** Un **punto de entrada único** para pipelines de CI (GitHub Actions, GitLab CI, Azure Pipelines, Jenkins) que orquesta el análisis estático, los puentes SAST, el escaneo de secretos, el review de IA opcional, la puntuación de riesgo y la clasificación del baseline, y emite **un artefacto estandarizado** con un código de salida determinista.
* **El Orden del Pipeline Es Contrato:** (1) resolución del policy — inválido sale con `2`; (2) extracción del diff con smart excludes; (3) linter regex + secretos + puentes externos; (4) puntuación de riesgo preliminar; (5) **review de IA opcional** (`--no-ia` lo desactiva; sin clave configurada degrada con un warning; un fallo en vuelo sale con `2`); (6) clasificación del baseline (`new`/`existing`/`ignored`/`accepted_debt`/`resolved`; un baseline corrupto sale con `2`); (7) evaluación del umbral `--fail-on`; (8) generación del artefacto con **enmascarado de secretos**.
* **Capa de Dominio (`src/domain/check/`):** `check_types.py` (severidad normalizada en tres niveles — `error`/`warning`/`info` — con alias de spec: `minor → warning`, `major|critical|blocker → error`, más `CheckFinding`, `CheckResult`, `CheckSummary`); `check_config.py` (precedencia **CLI > env > predeterminados**, con `GITPR_CHECK_*`); `check_output.py` (despacho de formato y escritura atómica).
* **Capa de Infraestructura (`src/infrastructure/ci/`):** `sarif_builder.py` (SARIF 2.1.0 compatible con GitHub Code Scanning, con `tool.driver.rules` generado dinámicamente); `junit_builder.py` (testsuites con `<failure>` para hallazgos bloqueantes y `<system-out>` para info/warning); `masking.py` (enmascarado antes de serializar — claves privadas, AWS, GitHub, Google, OpenAI, Slack, Bearer, basic-auth y asignaciones genéricas).
* **Caso de Uso (`run_check.py`):** `resolve_diff_base()` (explícito → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), ejecución del linter y de los puentes, puntuación de riesgo, review opcional, baseline y el envelope final. La stdout emite **solo el artefacto**; los warnings y los logs van a stderr.
* **Códigos de Salida:** `0` éxito; `1` hallazgo contabilizable en el umbral; `2` error de ejecución (configuración inválida, policy ausente, baseline corrupto, fallo de IA en vuelo, timeout de 300 s).
* **CLI:** `gitpr check` con 8 opciones y 9 variables `GITPR_CHECK_*` (fuera de `DEFAULT_CONFIG`). El formato predeterminado del comando es SARIF a stdout; `--output` escribe un archivo.
* **GitHub Action Reescrita:** `action.yml` pasó a ser un único paso de `gitpr check`, con inputs `provider`, `api-key` (ahora **opcional**), `format`, `output`, `fail-on`, `no-ia`, `baseline` y `policy`. **Ruptura declarada:** los workflows que dependían del paso viejo (`--linter` + `--fullreview`) y del artefacto `*_PR_FULLREVIEW.txt` necesitan migrar.
* **Pruebas:** **37 escenarios** — `tests/application/use_cases/test_run_check.py` (8), `tests/domain/check/test_check_types.py` (8), `tests/domain/check/test_check_config.py` (3), `tests/domain/check/test_check_output.py` (3), `tests/infrastructure/ci/test_masking.py` (6), `tests/infrastructure/ci/test_sarif_builder.py` (2), `tests/infrastructure/ci/test_junit_builder.py` (2) y `tests/test_check_cli.py` (5).
* **MCP:** la herramienta `run_check` devuelve el envelope JSON del mismo pipeline, con `no_ia` predeterminado en `true` para mantener barato el check del editor.
* **Artefactos:** `docs/check-ci.*.md` en **5 idiomas**, ADR-013, `glossary-check-ci.md`, spec, `plansfacts` y survey.

### **42. Extensión de Editor — VS Code y Cursor (`extensions/vscode/`) 🆕**

* **Qué Es:** Una extensión **cliente fino** que lleva los flujos de GitPR al editor sin reimplementar nada: inicia `gitpr-mcp` por stdio y llama a las herramientas existentes. Las claves, los tokens, los prompts y la caché se quedan en `~/.gitpr/`, cifrados con Fernet — la extensión **nunca pide ni almacena un secreto**.
* **Arquitectura:** `GitPrMcpClient` sobre `@modelcontextprotocol/sdk` (JSON-RPC delimitado por líneas, `cwd` = raíz del workspace, reconexión automática) + `SessionManager` con **una sesión por carpeta** (multi-root) + verificación de contrato al conectar: las 7 herramientas requeridas (`get_git_context`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_check`, `publish_pull_request`) deben existir; si no, `MissingMcpToolsError` indica `pip install --upgrade gitpr-cli`.
* **8 Comandos:** `gitpr.commit`, `gitpr.review`, `gitpr.fullReview`, `gitpr.reviewMentor`, `gitpr.generatePr`, `gitpr.generatePrExplain`, `gitpr.publishPr` y `gitpr.check` — los cuatro principales (Commit, Review, PR, Check) están fijados a la **barra de estado**.
* **Publicación en Dos Pasos:** `generatePr` abre un documento Markdown editable (línea 1 = título, el resto = cuerpo); `publishPr` pide confirmación modal y maneja `success`, `needs_push` (ofrece **Push & Publish**), `already_exists` (abre el PR existente), `auth_required` (abre un terminal con `gitpr`) y `error`.
* **Diagnósticos en el Panel Problems:** `run_check` se convierte en una `DiagnosticCollection("gitpr")`, con líneas 1-based → 0-based y filtrado de entradas `ignored`/`resolved` del baseline.
* **Enmascarado de Doble Profundidad:** `masking.ts` refleja `src/infrastructure/ci/masking.py` y cubre las notificaciones, el OutputChannel y los payloads de las herramientas.
* **Localización de la Extensión:** bundles `package.nls*.json` y `l10n/bundle.l10n*.json` en **en, es, fr y pt-br** (4 idiomas) — la UI de GitPR se mantiene en 5.
* **CI/CD de la Extensión:** `.github/workflows/extension.yml` instala, construye, prueba (Mocha contra el servidor Python real) y empaqueta el VSIX; con tags `ext-v*` publica en el VS Code Marketplace y Open VSX, requiriendo `VSCE_PAT`/`OVSX_PAT` (paso omitido cuando el secreto está vacío).
* **Lo Que la Extensión No Hace:** no commitea (solo llena la caja de Source Control, con fallback al portapapeles), no hace push y no publica sin confirmación.
* **Pruebas:** su propia suite Mocha (7 escenarios, incluido stdio real) + `tests/test_mcp_server.py` ampliado a **115 escenarios** (`16 → 18` herramientas, `mentor` y `explain` cubiertos).
* **Artefactos:** `docs/editor-extension.*.md` en **5 idiomas**, ADR-014, `glossary-editor-extension.md`, specs (2026-10-14 y 2026-10-15), `plansfacts` y el informe de Gemini.

---

## **📊 Pruebas y Calidad**

| Archivo de Prueba | Escenarios | Enfoque |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por rango de líneas en un archivo |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidad, commits, duración |
| `tests/test_baseline_cli.py` | 54 | Grupo `baseline`: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Builder de changelog: secciones, encabezados traducibles, contribuidores, **enlaces y fechas** |
| `tests/test_chat_backend.py` | 19 | Memoria de chat, persistencia, comandos slash |
| `tests/test_check_cli.py` | 5 🆕 | CLI de `check`: opciones, formatos, códigos de salida |
| `tests/test_commit_classifier.py` | 30 | Clasificación de Conventional Commits + **`pr_number_from_merge()` en los 4 forges** |
| `tests/test_config_app.py` | 78 | TUI de configuración: montaje, cambio de categoría, dirty tracking, F2 bloqueado, Ctrl+R, búsqueda, secretos |
| `tests/test_config_cli.py` | 9 | Registro del subcomando `config`, `-h`, lazy import, stdout limpio |
| `tests/test_config_schema.py` | 42 | Cobertura de `DEFAULT_CONFIG`, sin duplicados, categorías/kinds, `advanced` solo en Advanced, **sección de skills** ✅ |
| `tests/test_config_store.py` | 22 | Round-trip sobre el `.env` temporal, comentarios y orden preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuración de revisores sugeridos (claves y defaults) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, plantillas, `validate_ai_key()` con un SDK mockeado (401 vs. red vs. ollama) |
| `tests/test_core.py` | 49 | Flujos principales, git diff, generación de PR, timing, staging, coautoría, idioma de los hooks |
| `tests/test_diff_parser.py` | 26 | Parser de diff por líneas/hunks + `summarize_patch()` y `split_patch_sections()` |
| `tests/test_explain_command.py` | 2 | CLI de `explain`: éxito, clave ausente, diff vacío |
| `tests/test_external_linters.py` | 33 | Puente Checkstyle: parser XML, subprocess, intersección con el diff, informe |
| `tests/test_i18n.py` | 20 | Paridad entre idiomas, claves faltantes/huérfanas, identidad — **3 fallos en esta ventana** ❌ |
| `tests/test_install_wizard.py` | 3 | Wizard de instalación interactivo |
| `tests/test_issue_engine.py` | 4 | Borrador de issue estructurado |
| `tests/test_linter_metrics.py` | 4 | Métricas del linter: errores, warnings, duración |
| `tests/test_linter_presets.py` | 5 | Presets del linter: resolución y re-descarga forzada |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` en la CLI y en la ayuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompts MCP y fallback de idioma |
| `tests/test_mcp_server.py` | 115 🆕 | Herramientas MCP (18), recursos (19), anotaciones, patching, CLI directa, offload, **contrato de la extensión** |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real vía subprocess + JSON-RPC stdio |
| `tests/test_mentor_cli.py` | 4 | CLI de `mentor` y opt-in de `--mentor` — **2 fallos conocidos** ❌ |
| `tests/test_metrics.py` | 164 | **Ledger**: escritura síncrona, export idempotente, bundle/merge, versionado, migración, renderers, costo, calidad, módulos, proveedores, ciclo |
| `tests/test_net_timeouts.py` | 12 | Timeouts de red/IA — alineados con el predeterminado real de 180s |
| `tests/test_plugins.py` | 17 | Descubrimiento de plugins, fusión de reglas del linter, prompts MCP |
| `tests/test_policy_cli.py` | 26 | Grupo `policy`: list/show/validate/use/off/install/init, confirmación y lockfile |
| `tests/test_pr_publish_app.py` | 55 🆕 | TUI del PR Publisher: pantallas, flujos, revisores sugeridos, `NoticeScreen`, **modal de merge** |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de error del linter: abortar, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save y payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opciones, ayuda con epílogo documentado |
| `tests/test_release_engine.py` | 53 | **Cutoff anclado en el changelog, `_drop_released()`, enlaces, mapa de PRs por merge commit**, publicación |
| `tests/test_reviewer_resolution.py` | 18 | Escalera de resolución, rechazo de nombre parcial, dedup, tolerancia a fallos |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugerencia de revisores (ranking, exclusión, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | CLI de `risk`: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 61 | Matriz del ruleset integrado: regex, placeholders, nivel, comodín, fusión, traducciones |
| `tests/test_skill_command.py` | 10 | Descarga y validación de plantillas de skill |
| `tests/test_skill_context.py` | 20 🆕 | `get_skill_context()` con `quiet=True`, fallbacks, **guard del artefacto de PR** |
| `tests/test_skill_templates.py` | 99 🆕 | Escaneo parametrizado de **todas las plantillas publicadas** contra referencias embebidas |
| `tests/test_smart_excludes.py` | 15 | Filtro smart pathspec y re-descarga forzada |
| `tests/test_suggest_reviewers.py` | 17 | Revisores sugeridos en el flujo de PR (integración, hint `no_login`) |
| `tests/test_tests_command.py` | 2 | CLI del grupo `tests` y de `generate` |
| `tests/test_thinking_words.py` | 5 | Carga, parsing con separador `;` y recarga forzada |
| `tests/test_updater.py` | 23 | Puerta de PyPI: parsing de versión, caché diaria, fetch, decisiones de la puerta, wiring de la CLI |
| `tests/test_usage_log.py` | 27 | Log de uso: nombre derivado de la fecha, escritura síncrona, silencio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semántico: major/minor/patch, targets y validación |
| `tests/test_web_links.py` | 26 | Normalización de remotos (https/ssh/scp/credentials/port) y patrones de URL por forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 | Activación del pack, lockfile, rechazos |
| `tests/application/use_cases/test_baseline_gate.py` | 40 | Puerta: carga, clasificación, anotación, capas y aborto |
| `tests/application/use_cases/test_baseline_summary.py` | 12 | Vista de solo lectura del baseline para MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 | Correlación de diff/hallazgos/pruebas/historial, degradación |
| `tests/application/use_cases/test_create_baseline.py` | 30 | Creación/actualización con procedencia y preservación de decisiones |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 | Lotes, severidad, cache hit, clave ausente |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 2 | Caso de uso de explain |
| `tests/application/use_cases/test_generate_test_file.py` | 4 | Caso de uso de generación de pruebas |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 | Instalación del pack, orígenes y conflictos |
| `tests/application/use_cases/test_run_check.py` | 8 🆕 | Orquestación del check: orden, degradación, códigos de salida |
| `tests/application/use_cases/test_suppress_finding.py` | 29 | Supresión/eliminación en los cuatro ámbitos |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 | Validación: schema cerrado, checksum, compatibilidad |
| `tests/badge/test_badge_append.py` | 9 | Anexado idempotente del badge al cuerpo del PR |
| `tests/badge/test_badge_builder.py` | 19 | Composición Markdown de shields.io, escape, regla de color, estilo |
| `tests/badge/test_badge_cli.py` | 15 | Comando `gitpr badge`: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Conteo de alertas; `None` sin reglas configuradas |
| `tests/badge/test_badge_offline.py` | 6 | Guard offline (red prohibida, salvo loopback) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` en cada camino de publicación |
| `tests/badge/test_badge_paths.py` | 10 | Punto de inyección único en ambos publicadores (TUI y `--no-edit`) — **7 fallos en esta ventana** ❌ |
| `tests/demo/test_demo_app.py` | 22 | TUI del tour: navegación, pantallas, modal de ayuda |
| `tests/demo/test_demo_isolation.py` | 14 | El tour no toca ni `~/.gitpr` ni la red |
| `tests/demo/test_demo_runner.py` | 33 | Máquina de estados `DemoState`: avance, retroceso, completado, modo texto |
| `tests/demo/test_demo_scenarios.py` | 47 | Integridad de los escenarios grabados (aritmética de hunks) en los 5 idiomas |
| `tests/demo/test_demo_text_mode.py` | 18 | Salida en texto plano (`--no-tui`) para CI y grabaciones |
| `tests/demo/test_fake_ai_provider.py` | 21 | Contrato del proveedor falso: espeja `call_ai_model` argumento por argumento |
| `tests/domain/baseline/test_baseline_comparator.py` | 21 | Clasificación, conteos por estado, `resolved`→`new` |
| `tests/domain/baseline/test_baseline_fingerprint.py` | 12 | Digest golden, línea cruda nunca almacenada, estabilidad |
| `tests/domain/baseline/test_baseline_manifest.py` | 26 | JSON canónico, checksum, claves desconocidas, invariantes |
| `tests/domain/baseline/test_baseline_types.py` | 15 | Estados persistidos, ámbitos y precedencia |
| `tests/domain/baseline/test_suppression_policy.py` | 52 | Capas, especificidad, overrides, deuda vencida |
| `tests/domain/check/test_check_config.py` | 3 🆕 | Precedencia CLI > env > predeterminados |
| `tests/domain/check/test_check_output.py` | 3 🆕 | Despacho de formato y escritura atómica |
| `tests/domain/check/test_check_types.py` | 8 🆕 | Severidad en tres niveles, alias de spec, resumen |
| `tests/domain/finding/test_finding_types.py` | 3 | Tipo canónico de hallazgo: `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Formateo uniforme y deduplicación `[Gitleaks + Regex]` |
| `tests/domain/linter/test_structured_findings.py` | 44 | `findings_out`: hallazgos estructurados y el contador de líneas del lado nuevo |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 | Builder puro: honestidad epistémica, URLs eliminadas, orden preservado |
| `tests/domain/policy/test_policy_checksum.py` | 15 | SHA-256 del pack y detección de edición manual |
| `tests/domain/policy/test_policy_compatibility.py` | 22 | Compatibilidad de versión y credenciales |
| `tests/domain/policy/test_policy_manifest.py` | 42 | Schema cerrado, bloque `baseline:`, errores de manifest |
| `tests/domain/policy/test_policy_resolver.py` | 47 | Precedencia, composición en capas, pack activo × base |
| `tests/domain/pr/test_explain_section_builder.py` | 4 | Renderizado Markdown, parsing tolerante, detección de `[FILL]` |
| `tests/domain/risk/test_risk_calculator.py` | 5 | Fórmula 50/30/20, clamp 0–100, escalado obligatorio |
| `tests/domain/risk/test_risk_rules.py` | 5 | Pesos, umbrales, glob de rutas críticas, override YAML |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 | Detección por config, manifest y directorio; override; fallo advertido |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 | Ruta convencional por framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Caso de uso completo: review → IA → validar → clasificar → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extractor compartido entre el chat y `fix` |
| `tests/fix/test_fix_cli.py` | 31 | Enrutamiento de subcomandos, opciones, dry-run predeterminado, `--force` |
| `tests/fix/test_fix_history.py` | 21 | Ledger `.gitpr/fix_history.json`: escritura atómica, lectura |
| `tests/fix/test_fix_settings.py` | 10 | Las cinco `GITPR_FIX_*` y los fallbacks para valores inválidos |
| `tests/fix/test_patch_applier.py` | 19 | Wrapper de `git apply`: check/apply/reverse, rama, estado |
| `tests/fix/test_patch_extractor.py` | 13 | Bloques cercados → unified diff validado |
| `tests/fix/test_patch_safety_classifier.py` | 22 | Matriz safe/review_required/experimental y códigos de motivo |
| `tests/fix/test_resolve_last_review.py` | 13 | Selección del último review, exclusión de los reviews por archivo |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback`: reversión y los tres rechazos |
| `tests/infrastructure/baseline/test_local_baseline_repository.py` | 24 | Único lector/escritor: escritura atómica, checksum, reparación |
| `tests/infrastructure/ci/test_junit_builder.py` | 2 🆕 | JUnit XML: failure/system-out |
| `tests/infrastructure/ci/test_masking.py` | 6 🆕 | Enmascarado de credenciales antes de serializar |
| `tests/infrastructure/ci/test_sarif_builder.py` | 2 🆕 | SARIF 2.1.0 y `tool.driver.rules` |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 | `git log` seguro: commits, bugs y reverts en 90 días |
| `tests/infrastructure/git/test_test_matcher.py` | 5 | Casamiento prueba ↔ producción por convención |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Puente Bandit: disponibilidad, parsing, mapeo |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC: subprocess endurecido, timeout, binario ausente |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Puente Gitleaks: enmascarado de secretos, ámbito por archivo |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Puente Semgrep: parsing JSON, severidad, skip de no-Python |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 | Efecto real del baseline sobre review, lint y riesgo en un repositorio real |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Efecto real del pack sobre review, lint y riesgo (con un guard de red autouse) |
| `tests/review/test_diff_normalizer.py` | 20 | Saltos de línea, validación de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: procedencia, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orquestación: puertas, PR, diff, linter, comentario opcional |
| `tests/review/test_review_pr_cli.py` | 25 | CLI de `review-pr`: opciones, rechazos antes de la IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 48 | Proveedor de Azure DevOps: org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 44 | Proveedor de Bitbucket: Basic auth, workspace, PRs |
| `tests/scm/test_contract.py` | 42 | Contrato de `ScmProvider`: firmas, dataclasses, errores |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim deprecado `github_api` → delega en el proveedor |
| `tests/scm/test_github_provider.py` | 72 | Proveedor de GitHub: REST, headers, PRs, issues, releases, read-back de revisores |
| `tests/scm/test_gitlab_provider.py` | 52 | Proveedor de GitLab: API v4, namespace, headers sintetizados y `overflow` |
| `tests/scm/test_init_wizard.py` | 12 | Wizard de `--init`: detección de forge, validación, persistencia |
| `tests/scm/test_release_publish.py` | 8 | Publicación de release por forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 | El único módulo que muta: staging selectivo, un commit por grupo |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unidades → grupos → pre-validación de conflictos → mensajes |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, presupuesto/recorte, llamada de IA, validación de respuesta |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()`: texto ↔ modelo, puro |
| `tests/split/test_selective_stager.py` | 11 | Staging de un subconjunto, verificación contra HEAD, índice limpio |
| `tests/split/test_split_cli.py` | 31 | CLI de split: opciones, dry run predeterminado, `--apply` |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura i18n (scaffold; nunca se ejecuta) |

**Total:** **2952 escenarios recolectados en 138 módulos de prueba** (51 en la raíz + 12 en `tests/application/use_cases/` + 10 en `tests/fix/` + 9 en `tests/scm/` + 7 en `tests/badge/` + 6 en `tests/demo/` + 6 en `tests/split/` + 5 en `tests/domain/baseline/` + 4 en `tests/domain/policy/` + 4 en `tests/review/` + 4 en `tests/infrastructure/linter/external/` + **3 en `tests/domain/check/`** 🆕 + **3 en `tests/infrastructure/ci/`** 🆕 + 2 en `tests/domain/risk/` + 2 en `tests/domain/tests_generation/` + 2 en `tests/integration/` + 2 en `tests/infrastructure/git/` + 2 en `tests/domain/linter/` + 1 en `tests/domain/finding/` + 1 en `tests/domain/mentor/` + 1 en `tests/domain/pr/` + 1 en `tests/infrastructure/baseline/`; **+153 escenarios y +9 archivos** desde el informe anterior).

**Notas de calidad de esta versión:**
- **Ejecución completa en esta máquina: `12 failed, 2938 passed, 2 skipped, 138 subtests passed in 723.90s (12min04s)`.** El conteo de escenarios creció 153 (+5.5%), pero el número de fallos subió de 2 a 12. De los 12, **10 son nuevos** y **2 son los conocidos** de `tests/test_mentor_cli.py`.
- **Los 7 fallos nuevos de `tests/badge/test_badge_paths.py` son efecto directo del refactor de publicación (PR #212).** `_publish_pr_directly` empezó a llamar a `publish_pull_request_use_case`, que exige `provider.token`; el proveedor falso de la prueba (`SimpleNamespace(name=..., create_pull_request=...)`) no tiene el atributo, así que el caso de uso devuelve `auth_required` (401) y `create_pull_request` nunca corre — `captured["request"]` lanza `KeyError`. El producto es correcto (el proveedor real lleva el token); **la fixture necesita declarar `token="ghp_fake"`**, o el caso de uso necesita permitir saltar la verificación del token cuando `check_remote_state=False` y la publicación se delega a un proveedor real.
- **Los 3 fallos de i18n son deuda de traducción real, no flakiness:** `test_key_parity_and_count` (es/fr con 1618), `test_no_missing_keys` (8 claves usadas en el código sin diccionario) y `test_no_orphan_keys` (2 claves viejas). Las 8 faltantes son exactamente los mensajes del caso de uso de publicación de PR y las descripciones de las herramientas MCP nuevas (`run_check`, `publish_pull_request`).
- **Los 2 fallos de `tests/test_mentor_cli.py` siguen siendo el mismo bug de aislamiento de la ventana anterior:** `@patch("src.core.get_git_diff")` no llega al binding importado por valor en `src/main.py`. En un árbol cuyo único cambio es un `.md` (filtrado por `:(exclude)*.md` en los smart excludes) el diff real vuelve vacío, el flujo retorna antes del renderer y la aserción falla. Con el patch en `src.main.get_git_diff`, ambos escenarios pasan. Es un fix de dos líneas, aún pendiente.
- **En el lado positivo, la suite ganó 153 escenarios:** 99 vienen del escaneo parametrizado de plantillas (`test_skill_templates.py`), 37 de `check`, 10 del modal de merge, 5 del contrato MCP de la extensión y 2 del guard del skill de PR. La CI corre en Python 3.10 y 3.13 con `fail-fast: false`.
- **`tests/conftest.py` sigue hermético:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` y `LANG_VERSION` en la versión del código.
- **Guard de red en dos lugares:** `tests/demo/conftest.py` y `tests/integration/conftest.py`.
- **Fixtures de git reales:** `tests/fix/git_fixture.py` y `tests/split/git_fixture.py` siguen siendo el estándar para lo que solo es honesto con un `git apply` real.
- **La suite de la extensión es aparte:** 7 escenarios Mocha en `extensions/vscode/src/test/mcp_client.test.ts`, de los cuales 1 se integra de verdad con el servidor MCP Python por stdio; no cuenta para pytest.

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n — la deuda se reabrió:** **1620 claves** en `pt_br.json`, `pt_pt.json`, `es_es.json` y `fr_fr.json`, pero **1618** en `es.json` y `fr.json`. El código usa **1626** claves: **8 sin traducir** y **2 huérfanas**. En la ventana anterior el marcador era 1603/1603 con cero faltantes; esta ventana agregó **+17 claves** (check, modal de merge y extensión) y dejó 8 atrás. ❌
* **`__lang_version__` subió de v0.0.34 → v0.0.35** en el commit `46c25c1`, disparando la re-descarga OTA de las traducciones.
* **Fuentes de traducción en lockstep:** una clave nueva debe existir en el código (fuente), en `langs/pt_br.json` (**lista maestra**), en los diccionarios FR/ES de `scripts/sync_all_langs.py` y en los valores curados de `scripts/fix_mangled_i18n_keys.py`; la aserción `len(CLEAN_KEYS)` sigue en 49.
* **Temas nuevos 🆕 (2, ambos en 5 idiomas):**
  - `docs/check-ci.md` — el comando de CI unificado: el orden del pipeline, los tres formatos, los códigos de salida 0/1/2, la tabla de flags × variables y el enmascarado de secretos.
  - `docs/editor-extension.md` — la extensión de VS Code y Cursor: la arquitectura de cliente fino MCP, los 8 comandos, la publicación en dos pasos, el panel Problems y la instalación del VSIX.
* **Temas actualizados en esta ventana:** `README.*` (5 — la lista de comandos ganó `gitpr check` y la extensión), y los propios `docs/check-ci.*`/`docs/editor-extension.*` nacieron completos.
* **Documentación en 5 idiomas:** **48 temas canónicos** en `docs/` (+2) — **45 con cobertura completa en los 5 idiomas** y solo **3 temas parciales** (`github-issue-prompt-com-gh`, `version-markers`, `review-pr`), los mismos 3 de la ventana anterior.
* **Gap de documentación que persiste:** `explain` y `tests` siguen siendo las **únicas** funcionalidades sin tema en `docs/` — las dos funcionalidades de esta ventana (check y extensión de editor) nacieron documentadas en 5 idiomas.
* **Skills locales de Claude Code:** `.claude/skills/` con **29 skills** (conteo sin cambios).
* **Índice de memoria:** `.claude/memory/MEMORY.md` con **40 patrones** (conteo sin cambios).
* **Informes de tarea:** `docs/claude-code/reports/develop_natan/` (**108** en total; **+3** — `2026-10-12_status_report_v0_0_17.md`, `2026-10-17_pr_template_skill_artifact.md`, `2026-10-18_merge_progress_modal.md`) y `docs/gemini/reports/develop_natan/` (**16**; **+2** — `2026-10-15_gitpr_check_ci.md`, `2026-10-16_editor_extension_mcp.md`).
* **Informes de estado:** `docs/reports/` — este es el **18º informe** (v0.0.18); el v0.0.17 nunca se sobrescribe.
* **Planes de desarrollo:** 144 archivos en `docs/plans/` (+9 — ADR-013/ADR-014, los glosarios `check-ci` y `editor-extension`, las specs de check y de la extensión, y los `plansfacts` de check/plantilla de PR/merge) + **17 archivos en `docs/survey/`** (+3).

---

## **🔄 Pipeline de Distribución**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Actualización obligatoria:** la ejecución consulta PyPI al iniciar y **bloquea con código de salida 1** si hay una versión más nueva, imprimiendo `pip install --upgrade gitpr-cli`; la verificación se cachea por día y `--update` solo informa
3. **GitHub Releases:** eliminado — sin PyInstaller, sin asset `.exe`, sin hot-swap
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala vía pip) + `tests.yml` (matriz Python 3.10 y 3.13) + **`extension.yml`** 🆕 (VSIX; publicación en Marketplace/Open VSX con `ext-v*`). `action.yml` se reescribió para un único paso de `gitpr check` — **ruptura declarada** para quienes usan `--linter` + `--fullreview`.
5. **Servidor MCP:** entry point `gitpr-mcp` vía `pyproject.toml`
6. **Extensión de Editor 🆕:** paquete VSIX independiente (`natanfiuza.gitpr`), con canales VS Code Marketplace y Open VSX; requiere Node.js 20+ solo para construir, no para usar
7. **Plantillas e idiomas OTA:** `templates/` y `langs/*.json` servidos desde GitHub (main) — el bump `v0.0.35` renueva las copias locales de `~/.gitpr/langs/` al publicarse; `__scripts_version__` **v0.0.4** renueva los hooks
8. **Versión derivada del código:** `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — `__version__` es la **única** fuente
9. **`package-data` importa:** los cuatro packs embebidos (`src/policy_packs/*`) viajan en el wheel
10. **Estado del release 🆕:** **`v1.4.0` sigue siendo el último tag** (2026-10-12). `__version__` ya está en **1.4.1** (commit `46c25c1`, PR #217), pero **el tag `v1.4.1` todavía no se creó** y la sección `[1.4.1]` de `CHANGELOG.md` está **en el working tree**, junto con dos artefactos sin rastrear (`.gitpr/reports/pr_desc/develop_natan_20261018023014_PR_DESC.md` y `.gitpr/reports/release/develop_natan_20261018023153_RELEASE.md`). El pipeline de release sigue siendo manual.

---

## **📈 Evolución desde el Informe Anterior (v0.0.17)**

| Área | v0.0.17 (anterior) | v0.0.18 (actual) |
|------|-------------------|-----------------|
| **Versión de GitPR** | 1.4.0 (**tag `v1.4.0`**) | **1.4.1** (bump commiteado; **tag aún no creado**) — `v1.4.0` sigue siendo el último tag |
| **Versión de idioma** | v0.0.34 | **v0.0.35** |
| **Versión de los scripts de hook** | v0.0.4 | v0.0.4 (sin cambios) |
| **Proveedores de IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama (sin cambios) |
| **Idiomas** | 5 idiomas, 6 diccionarios | 5 idiomas, 6 diccionarios |
| **Subcomandos** | 14 | **15** (+ `check`) — 4 grupos (baseline 6, metrics 8, policy 7, tests 1) |
| **Interfaz** | CLI + TUIs + wizards + 14 subcomandos | **+ `gitpr check` + extensión de VS Code/Cursor** (8 comandos en el editor) |
| **Capas** | 8 paquetes de dominio, 14 casos de uso (13 archivos + `__init__`) | **9 paquetes de dominio** (+ `check`) y **15 casos de uso** (+ `run_check`, `publish_pull_request`) |
| **Infraestructura de CI 🆕** | — | **`src/infrastructure/ci/`** — SARIF 2.1.0, JUnit XML y enmascarado de secretos |
| **Herramientas MCP** | 16 herramientas / 19 recursos / 7 prompts | **18 herramientas** (+ `run_check`, `publish_pull_request`) / 19 recursos / 7 prompts; `mentor` y `explain` como parámetros opt-in |
| **Flags de CLI** | 32 en la raíz + 14 subcomandos | **32 en la raíz + 15 subcomandos**; `check` tiene 8 opciones propias |
| **Variables de entorno** | 58 en `DEFAULT_CONFIG` + precios/moneda | **58 en `DEFAULT_CONFIG`** + precios/moneda + **9 `GITPR_CHECK_*`** |
| **Esquema de configuración** | 80 `ConfigField` / 16 categorías | 80 / 16 (sin cambios — `check` aún no tiene categoría) |
| **Linter** | Regex + secretos + SAST + `findings_out` + consciente del baseline | **+ pipeline de CI unificado con SARIF/JUnit/JSON y enmascarado** |
| **Git Hooks** | post-checkout/post-merge/pre-push en el ledger | Sin cambios |
| **Capa SCM** | 4 forges, paginación, `web_links.py` | **+ caso de uso de publicación compartido** (CLI y MCP); proveedores sin cambios |
| **Motor de release** | Cutoff anclado en el changelog, enlaces, PR por merge | **+ sección 1.4.1 generada** (sin commitear, sin tag); motor sin cambios |
| **i18n (claves por archivo)** | 1603 × 6 — **cero sin traducir** ✅ | **1620 × 4 y 1618 × 2 — 8 sin traducir y 2 huérfanas** ❌ (+17 claves) |
| **Documentación** | 46 temas (43 completos + 3 parciales) | **48 temas (45 completos + 3 parciales)** — +2 familias en 5 idiomas |
| **CI** | `tests.yml` (3.10 + 3.13) + `action.yml` viejo | **+ `extension.yml`**; `action.yml` reescrito para `gitpr check` (ruptura declarada) |
| **Suite de pruebas** | 2799 escenarios (129 archivos) | **2952 escenarios (138 archivos) — +153 escenarios, +9 archivos** |
| **Fallos conocidos** | 2 (`test_mentor_cli`) | **12** — los 2 viejos + **7 fallos nuevos de fixture en el badge** + **3 de i18n** |
| **Dependencias** | `packaging>=23.0` | **Sin dependencia Python nueva**; la extensión suma Node.js 20+ y `@modelcontextprotocol/sdk` |
| **Commits desde el informe** | 11 commits | **6 commits** (`fc9d400`, `a50f21c`, `b6382b5`, `525a1ec`, `8557125`, `46c25c1`) |
| **PRs fusionados** | 10 PRs (#192–#207) | **6 PRs (#208, #210, #212, #214, #216, #217)**; 4 issues cerrados (#209, #211, #213, #215) |
| **Índice de memoria** | 40 patrones | 40 patrones (sin cambios) |
| **Informes de tarea** | 105 claude-code, 14 gemini | **108 claude-code (+3) y 16 gemini (+2)** |
| **Planes de desarrollo** | 135 planes, 14 surveys | **144 planes (+9), 17 surveys (+3)** — incluye ADR-013 y ADR-014 |

---

## **🚧 Próximos Pasos**

* **Cerrar el release 1.4.1 🆕:** commitear la sección `[1.4.1]` de `CHANGELOG.md`, crear el tag `v1.4.1`, publicar en PyPI y decidir el destino de los dos artefactos sin rastrear (`.gitpr/reports/pr_desc/...` y `.gitpr/reports/release/...`). `__version__` ya está bumpeado, así que el paquete publica 1.4.1 con o sin el tag.
* **Corregir los 7 fallos de `tests/badge/test_badge_paths.py` 🆕:** el proveedor falso de la prueba no tiene `.token`, y el caso de uso compartido ahora exige el atributo. Agregar `token="ghp_fake"` al `SimpleNamespace` (o permitir saltar la verificación cuando `check_remote_state=False`) restaura los 7 escenarios y valida el badge en el camino nuevo.
* **Pagar la deuda de i18n reabierta 🆕:** 8 claves usadas sin traducción (mensajes de `publish_pull_request` y descripciones MCP) + 2 huérfanas + las 2 claves faltantes en `es.json`/`fr.json`. Cerrar esto desbloquea a la vez `test_key_parity_and_count`, `test_no_missing_keys` y `test_no_orphan_keys`.
* **Corregir el aislamiento de `tests/test_mentor_cli.py`:** son los **dos fallos más antiguos** de la suite. El patch apunta a `src.core.get_git_diff`, pero `src/main.py` importa el nombre **por valor** — la función real corre, y en un árbol cuyo único cambio es un `.md` el diff vuelve vacío y el flujo retorna antes del renderer. Cambiar el target a `src.main.get_git_diff` cierra la suite sin tocar el producto. **Ítem heredado, aún abierto.**
* **Documentar `explain` y `tests`:** son ahora las **únicas dos** funcionalidades sin tema en `docs/` — las dos funcionalidades de esta ventana nacieron documentadas en 5 idiomas. **Ítem heredado, aún abierto.**
* **Automatizar el release en GitHub Actions:** la CI de pruebas tiene tres workflows, pero el build + upload a PyPI siguen siendo manuales — y `extension.yml` ya muestra el patrón (disparo por tag) que un `release.yml` puede seguir. **Ítem heredado, aún abierto.**
* **Ampliar la cobertura del ruleset de secretos:** los tres gaps siguen abiertos — asignación sin comillas (`API_KEY=abc123`), los prefijos `ASIA…`, `github_pat_…` y `xoxc-`/`xoxd-`, y la ausencia de límite izquierdo en el nombre de la clave. El enmascarado de CI cubre parte, pero el **linter** (que corre en pre-commit) todavía no. **Ítem heredado, aún abierto.**
* **Traducir `docs/review-pr.*.md`:** es la **última** familia de funcionalidades con cobertura parcial (2 de 5 idiomas). **Ítem heredado, aún abierto.**
* **Desrastrear los artefactos de `.gitpr/metrics/export/`:** **34 archivos siguen rastreados** (antes eran 35), porque `gitignore` no desrastrea. Falta un único `git rm --cached`. **Ítem heredado, aún abierto.**
* **Documentar la ruptura de contrato de `request_pull_request_reviewers`:** pasó de `None` a `list[str]`; las subclases de terceros de `ScmProvider` necesitan actualizarse. **Ítem heredado, aún abierto.**
* **Mapear el radio de impacto del baseline en el código de salida:** un baseline que no puede aplicarse aborta seis flujos con código de salida 1, y `check` ahora usa **2**. Vale la pena explicitar el contrato completo en `docs/baseline-suppressions`/`docs/check-ci` para pipelines de CI. **Ítem heredado, parcialmente cubierto por check.**
* **Reconciliar la versión del proyecto:** `CLAUDE.md` todavía dice `Current version: 0.0.37` mientras `__version__` está en **1.4.1**. Definir una única convención. **Ítem heredado, aún abierto.**
* **`gitpr -h config` ignora `-h`:** el subcomando abre la TUI en lugar de mostrar la ayuda — la puerta `if ctx.invoked_subcommand is not None: return` corre antes del bloque `help_flag`. **Ítem heredado, aún abierto.**
* **Migrar el extractor de `sync_i18n.py` a AST:** la regex trunca literales con concatenación implícita (`__("a " "b")`). `tests/test_i18n.py` ya usa AST; el script de sincronización sigue en regex. **Ítem heredado, aún abierto.**
* **Sección Smart Exclude en la TUI:** el ítem 10 del survey de la TUI de configuración sigue sin iniciar, y los campos `GITPR_SMART_EXCLUDES_*` siguen bajo `diff`, sin categoría propia. **Ítem heredado, aún abierto.**
* **Deudas registradas en el plan de la TUI de configuración:** `DEFAULT_CONFIG` quedó redundante con el schema; el banner de apertura no lista `--init`, `--base` ni `--plugins`; `LinterApp` es la **única** app de Textual que todavía no desactiva la command palette; y las **9 `GITPR_CHECK_*`** todavía **no** tienen categoría en el schema — `check` es la única funcionalidad configurable fuera de la TUI. **Ítem heredado, ampliado en esta ventana.**
* **Crear una nota de migración para `action.yml`:** reemplazar `--linter` + `--fullreview` por `gitpr check` es una ruptura de contrato para los workflows existentes; `docs/check-ci` documenta el comando nuevo, pero no un "antes y después" para quienes ya usan la action.
* **Publicar la extensión en los marketplaces:** el workflow está listo, pero depende de `VSCE_PAT`/`OVSX_PAT`; hasta entonces, la instalación es vía VSIX local.
* **Proveedor Anthropic Claude:** Soporte directo para la API de Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual en el Dashboard:** Histogramas de tiempo y tendencias de tokens en la TUI de métricas.
* **Seed para el `.gitpr/conf/` local:** El sembrado de plantillas de configuración local (smart-excludes, linter) sigue pendiente como subcomando propio o paso de wizard.
* **Más proveedores:** OpenAI directo, proveedores locales adicionales.

### ✅ Completados en esta ventana (2026-10-12 → 2026-10-18)

* ~~**`gitpr check` — comando de CI unificado**~~ 🆕 — `src/domain/check/`, `src/infrastructure/ci/`, `run_check.py`, SARIF/JSON/JUnit, códigos de salida 0/1/2, 8 opciones, 9 `GITPR_CHECK_*`, `action.yml` reescrito y docs en 5 idiomas (PR #210).
* ~~**Extensión de VS Code y Cursor + herramientas MCP de check/publish**~~ 🆕 — cliente fino MCP, 8 comandos, panel Problems, publicación en dos pasos, `extension.yml`, `run_check`/`publish_pull_request` y parámetros `mentor`/`explain` (PR #212).
* ~~**Caso de uso compartido de publicación de PR**~~ 🆕 — `publish_pull_request.py` usado por `--no-edit` y por MCP, con `PublishPrOutcome` y enmascarado de errores.
* ~~**Artefacto corrupto en el skill de PR**~~ 🆕 — limpieza en memoria en `get_skill_context()`, plantilla `pt_br` corregida y escaneo parametrizado de todas las plantillas (PR #214).
* ~~**Modal de progreso en el merge de PR**~~ 🆕 — `title` opcional, `mark_finished(show_close=False)`, worker desde el montaje, excepción genérica capturada y cierre automático a los 1.2 s (PR #216).
* ~~**Bump a 1.4.1 y v0.0.35**~~ 🆕 — `46c25c1` (PR #217); solo faltan el tag y la publicación.
* ~~**Commitear el fix de los encabezados `[1.4.0]`**~~ — los encabezados en inglés están en el `CHANGELOG.md` versionado desde `1abb1b5`/`fc9d400`. **Ítem de la ventana anterior cerrado.**
* ~~**Documentar `check` y la extensión en 5 idiomas**~~ 🆕 — `docs/check-ci.*` y `docs/editor-extension.*`, con ADR-013/ADR-014 y los dos glosarios.

---

**Informe generado el:** 2026-10-18  
**Rama:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
