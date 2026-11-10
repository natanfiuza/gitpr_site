# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.24 (2026-11-10)**

## **📌 Visión General**

**GitPR** es una herramienta CLI (Command Line Interface) avanzada para la automatización de procesos Git mediante Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). Su objetivo principal es actuar como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita la deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.24):**
- **El dashboard de métricas ganó gráficos ASCII y navegación por pestañas:** el nuevo `src/ascii_chart.py` renderiza un histograma vertical de latencia y una serie temporal de tokens con funciones puras, sin dependencias nuevas; la `MetricsApp` ganó las pestañas **Events** y **Charts & Trends**, con los atajos `1`/`2` ([ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
- **El prompt se volvió más resistente a los binarios:** `collapse_binary_hunks()` ahora corre **también** al inicio de `generate_pr_content()`, y el catálogo de smart excludes ganó `*.arrow`, `*.gguf`, `*.tflite` y `*.keras` — los artefactos de ML/datos dejan de llegar a la IA incluso cuando entran por un llamador externo.
- **El release de la extensión se desacopló del resto del release:** `extension.yml` ganó el job `github-release` (`.vsix` adjunto a los releases `ext-v*`) y el job `publish-marketplaces` no bloqueante, que evalúa `VSCE_PAT` y `OVSX_PAT` de forma **independiente** y reporta a `$GITHUB_STEP_SUMMARY`. La documentación de instalación del VSIX (CLI y GUI) entró en los 5 idiomas.
- **Costo y moneda dejaron de ser fijos:** el schema ganó el tipo `KIND_FLOAT`, la clave `GITPR_METRICS_CURRENCY` y **ocho** campos de precio `INPUT`/`OUTPUT` por modelo; `DEFAULT_MODEL_PRICES` ahora también precia `gemini-pro-latest` y `gemini-flash-lite-latest`.
- **Las notas de migración quedaron formalizadas:** la ruptura del `action.yml` hacia `gitpr check` ([ADR-0011](docs/adr/0011-action-unified-check-migration.md)), el commit interactivo sin TTY, el `GITPR_SKIP_UPDATE_CHECK` estricto y el seed/opt-out de la configuración del proyecto en CI ahora tienen documentación explícita.
- **Tres ADR nuevos cerraron contratos pendientes:** [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md) (fallback de idioma del sitio), [ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) (gráficos/pestañas) y [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) (segregación entre auto-seed en runtime y aprovisionamiento explícito).
- **La 1.6.0 y la 1.7.0 fueron publicadas:** el PyPI sirve la **1.7.0**. La deuda de release de la ventana anterior está cerrada, y `develop_natan` se fusionó en `main` vía PR #310.
- **Versión actual:** 1.7.0 (**publicada en PyPI**; el tag `v1.7.0` en el origin todavía apunta al commit anterior al bump — ver Distribución)
- **Versión de los diccionarios de idioma:** v0.0.42 (🆕 +1 desde la v0.0.41; entrega las 15 claves nuevas por OTA)
- **Versión de los scripts de hook:** v0.0.4 (sin cambios)
- **Publicación:** PyPI (`pip install gitpr-cli`) — canal binario eliminado
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 diccionarios)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal) — **16 subcomandos** (`badge`, `baseline`, `check`, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `seed`, `split`, `tests`). Cuatro de ellos son **grupos**: `baseline` (6 subcomandos), `metrics` (8), `policy` (7) y `tests` (1). **34 opciones Click en la raíz** — sin cambios en esta ventana.
* **UI/Terminal:** Textual — TUI para chat interactivo, edición de issues, help screen, dashboard de métricas, PR Publisher, **CommitApp** (`src/ui/commit_app.py`, 220 líneas), errores del linter (`LinterApp`), configuración (`ConfigApp`), modal `NoticeScreen`, el tour del `gitpr demo` (`src/ui/demo/`), el asistente de migración del ledger (`src/ui/metrics_migration_app.py`) y el `PrBaseApp` (`src/ui/pr_base_app.py`). 🆕 La `MetricsApp` pasó a usar `TabbedContent` con las pestañas **Events** y **Charts & Trends** y los bindings `1`/`2`.
* **Arquitectura en Capas:** `src/domain/` (regla pura, sin I/O) + `src/application/use_cases/` (orquestación, **15 casos de uso**) + presentación (CLI/TUI). **9 paquetes de dominio**: `baseline`, `check`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`. Fuera del dominio: `src/issue_autoclose/` (flujo de cierre de issue sobre `ScmProvider` y el registro local), `src/project_config.py` (configuración versionada), `src/scm_settings.py` (resolución de proveedor y credencial por repositorio) y 🆕 `src/ascii_chart.py` (renderizado 2D puro, sin I/O).
* **Extensión de Editor:** `extensions/vscode/` — cliente TypeScript fino del MCP para **VS Code y Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). No contiene regla de negocio. 🆕 El workflow separa el release del VSIX (job `github-release`) de la puerta granular de publicación en los marketplaces.
* **Criptografía:** `cryptography.fernet` para protección local de claves de API, tokens de GitHub y tokens SCM de los forges.
* **Configuración:** `python-dotenv`, `pyyaml` (linter estático y configuración del proyecto) + schema declarativo propio (`src/config_schema.py`, 🆕 **109 `ConfigField`** y **18 categorías**). El `DEFAULT_CONFIG` se **deriva** de los campos con `seeded=True` (🆕 **56 claves**). 🆕 Se añadió el tipo `KIND_FLOAT` para precios y moneda. El `packaging` alimenta la comparación de versiones de la puerta (PEP 440) y la compatibilidad de los policy packs.
* **Proveedores de IA:** Integración vía SDK oficial de Google GenAI, OpenAI SDK (DeepSeek) y OpenAI SDK (Ollama local). Predeterminados configurados: `gemini-pro-latest` / `gemini-flash-lite-latest` y `deepseek-v4-pro` / `deepseek-v4-flash`.
* **Forge APIs:** `requests` (REST) — abstracción multi-forge en `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py`; `src/github_api.py` mantenido como shim deprecado. El contrato de issue (`get_issue`, `get_default_branch`, `closing_line`) lo implementan los **cuatro** proveedores cuando tiene sentido.
* **Linter / SAST:** regex YAML + bridge Checkstyle + ruleset de secretos embebido (`src/security_ruleset.py`) + 4 bridges en `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), con modelo normalizado en `src/domain/linter/sast_finding_mapper.py` y el hallazgo canónico en `src/domain/finding/`.
* **Infraestructura de CI:** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) y `masking.py` (enmascarado de credenciales antes de serializar cualquier artefacto).
* **Internals de Git:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py`, `risk_history_reader.py` y `test_matcher.py`.
* **Persistencia local:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) con WAL, `PRAGMA user_version`, claves UUID y transporte por bundle. La tabla de precios vive en `src/config.py` (`DEFAULT_MODEL_PRICES`) y ahora acepta override por modelo y de moneda.
* **Red:** `src/net.bounded_urlopen` (`UPDATE_FETCH_HARD_TIMEOUT = 3.0`) limita el **wall clock incluyendo DNS** en la puerta de actualización. `get_issue()`/`get_default_branch()` usan timeout de **5 s** (`GITPR_ISSUE_AUTOCLOSE_TIMEOUT`).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 herramientas anotadas**, **19 recursos**, 7 prompts (conteo sin cambios; ningún archivo del servidor cambió en esta ventana).
* **Pruebas:** Pytest + `unittest.mock` (**160 módulos de prueba** — 64 en la raíz, 12 en `tests/application/use_cases/`, 10 en `tests/fix/`, 10 en `tests/scm/`, 10 en `tests/infrastructure/` (baseline 1, ci 3, git 2, linter 4), 21 en `tests/domain/` (baseline 5, check 3, finding 1, linter 2, mentor 1, policy 4, pr 1, risk 2, tests_generation 2), 7 en `tests/badge/`, 6 en `tests/demo/`, 6 en `tests/split/`, 5 en `tests/issue_autoclose/`, 4 en `tests/review/`, 3 en `tests/integration/`, 2 en `tests/unit/` —, **3436 escenarios recolectados**) + pruebas e2e del servidor MCP vía subprocess real (JSON-RPC stdio) + fixtures de repositorio git real (`tests/fix/`, `tests/split/`, `tests/test_full_diff_encoding.py`) + guarda de aislamiento de red (`tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py`, `tests/split/conftest.py`). La extensión tiene suite Mocha propia (7 pruebas contra el servidor MCP real).
* **Empaquetado:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; packs embebidos vía `package-data`; `packaging>=23.0`. La extensión se empaqueta aparte (`vsce package`, VSIX).
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` (llama a `gitpr check`) + `tests.yml` (matriz Python 3.10/3.13) + `extension.yml` (build, prueba y empaquetado del VSIX; 🆕 release propio en `ext-v*` y puerta granular por marketplace) + `release.yml` (pruebas, verificación tag/versión vía SSOT, build sdist/wheel, `twine check --strict`, Trusted Publishing OIDC en PyPI y creación de GitHub Release en tags `v*`).

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación Estructurada:** Se comunica con el LLM pidiendo salida estrictamente JSON.
* **Map-Reduce (Diffs Gigantes):** Cuando el diff supera ~90k tokens, se divide automáticamente en lotes por archivo (`split_diff_into_chunks`), cada parte se procesa (Map) y los resúmenes se unifican (Reduce). Soporta PRs, commits e Issues. El `provider`/`model` se agregan entre chunks y `meta`/`modules` se reenvían a `log_command_metric`.
* **Tokenizer Local:** `tokenizer.json` para estimación precisa de tokens antes del envío a la IA.
* **Estimación de Tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()` con fallback al tokenizer local.
* **Optimización Git Nativa:** Flags `-U1`, `-w`, `-M`, `-B` en los comandos `get_git_diff` y `get_git_full_diff`.
* **`get_git_staged_diff()`:** ejecuta `git diff --cached -U1 -w -M -B -- <SMART_EXCLUDES>` con `encoding="utf-8"`/`errors="replace"` y `stdin=DEVNULL`, devolviendo `None` cuando git falla (nunca una cadena vacía disfrazada). Es la fuente preferida del mensaje de commit.
* **`get_split_diff()`:** diff propio de `split`, con `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sin** reutilizar `get_git_diff()`.
* **Guarda de Hunk Binario:** todos los puntos de entrada de diff (`get_git_diff`, `get_git_staged_diff`, `get_unstaged_diff`, `get_git_full_diff`, `current_diff`, `get_check_diff`, `tests_generate`, `risk`) aplican `collapse_binary_hunks()` — los hunks con corridas contiguas de ≥8 `U+FFFD` (o ≥32 con densidad ≥10%) pasan a `Binary files … differ`. La ruta rápida O(1) devuelve el diff intacto cuando no hay `U+FFFD`, preservando Latin-1/Windows-1252 legítimos.
* **🆕 Colapso Defensivo en `generate_pr_content()`:** la función ahora aplica `collapse_binary_hunks(diff_text)` **antes** del timing y de la llamada a la IA, cubriendo también a los llamadores externos que entregan el diff crudo — no solo la ruta que vino de git.
* **🆕 Cuatro Extensiones Binarias Nuevas:** `*.arrow`, `*.gguf`, `*.tflite` y `*.keras` entraron tanto en el fallback embebido de smart excludes como en `templates/gitpr.smart-excludes.json`.
* **Seed de la Configuración Local (`seed_project_configuration()`):** crea `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` y `.gitpr/skill/.gitpr.linter.yml`, devolviendo un resumen estructurado (`config`, `smart_excludes`, `linter`, `skills_downloaded`). Nunca sobrescribe sin `force=True`.
* **Seed de la Configuración del Proyecto:** el callback raíz y el `--install` llaman a `ensure_project_config()` (`src/project_config.py`) en cada invocación dentro de un repositorio, excepto en las rutas de ayuda. Un archivo existente **nunca** se sobrescribe; la creación se anuncia solo en la ruta interactiva de la raíz y puede desactivarse con `GITPR_SKIP_PROJECT_CONFIG_SEED` (escape hatch de la suite de pruebas).
* **`get_base_branch(quiet=)`:** la resolución de la base delega en `resolve_pull_request_base()`; el `quiet` evita que el aviso de fallback corrompa la TUI o el stream JSON-RPC.
* **Pre-Grabado (`--pre-save`):** Flag de depuración oculta que guarda el payload completo (instrucción de sistema + prompt) como JSON antes de cada llamada a la IA.
* **Smart Excludes con Dos Capas:** Capa global (`~/.gitpr/conf/`) + local del proyecto (`./.gitpr/conf/`), unidas en runtime. Los loaders globales y locales quedaron blindados contra `.env` corrupto y paths ilegibles — un archivo malo nunca tumba un diff ni un lint.
* **Métricas con Seguimiento Temporal:** `log_command_metric()` en todos los flujos, con `meta`, `modules` y `duration_ms`.
* **Resolución Centralizada de Salida:** `resolve_output_path()` — por defecto en `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`)**: `gitpr --init` — detecta el proveedor por el remote origin, pide los extras por proveedor, valida el token y persiste **solo en caso de éxito**; graba en el slot del proveedor elegido y solo crea override en el proyecto cuando este difiere del detectado.
* **Plantilla de Skill de Release (`ensure_release_skill_template()`)**: Descarga `templates/gitpr.release.*.md` en el primer uso de `gitpr release`.
* **Guarda de Skill Corrupta:** `_CORRUPTED_PR_SKILL_ARTIFACT` documenta el fragmento JSON que se pegó a la plantilla `pt_br`, y `get_skill_context()` lo elimina **en memoria** cuando `action_type == "pr"`.
* **Registro Compartido de Skills:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` en `src/config.py`.
* **Motor de Review con Ámbito de Caché (`generate_pr_content`):** `cache_scope` y `store_diff`; el `cache_scope` transporta también el policy pack activo.
* **Trailer de Coautoría:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceros.
* **Captura de Salida de Git Blindada:** las llamadas a `subprocess.run` en `src/cache.py`, `src/core.py`, `src/issue_engine.py` y `src/main.py` que capturan salida de git usan `encoding="utf-8"` **con** `errors="replace"`.
* **Carga de `.env` Resiliente:** el `load_dotenv()` en `core.py`, `i18n.py` y `spinner.py` corre dentro de `try/except` — un `.env` ausente, ilegible o en un directorio de solo lectura no impide la inicialización.
* **Fallo de Git ≠ Diff Vacío:** `diff_text is None` sale con código 1; un diff genuinamente vacío sale con **0**; en `--hook` el camino es fail-open.
* **Subprocesos Blindados:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` en todos los `subprocess.run`; verificación de conexión por socket antes de las operaciones de red.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitectura de Plugins:** Carga plugins de `~/.gitpr/plugins/`, aplicándose a **todos los proyectos**.
* **Plugins de Linter (`linter/`):** Archivos `.yml` fusionados con el `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`):** Archivos `.md` que extienden el contexto del sistema.
* **Factory Closures:** `get_linter_plugins` y `get_prompt_plugins` aíslan estado entre sesiones.
* **Comando `--plugins`:** Lista todos los plugins globales instalados con sus tipos y rutas.
* **Documentación Multilingüe:** `docs/plugins-system.md` en 5 idiomas.
* **Relación con los Policy Packs:** el pack es una **tercera** capa de reglas — los plugins pertenecen a la máquina, `.gitpr.linter.yml` pertenece al proyecto y el pack se versiona y commitea.

### **3. Interfaz CLI y Configuración (`src/main.py` y `src/config.py`)**

* **Configuración Inicial:** Detecta la primera ejecución, crea `~/.gitpr/` y pide interactivamente claves, preferencias e idioma.
* **Routing de Comandos:** Gestiona todos los flags y los **16 subcomandos** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline`, `check` y `seed`.
* **Comportamiento Predeterminado:** Ejecutar `gitpr` sin flags abre la TUI del PR Publisher.
* **Flags (34 opciones Click en la raíz):** `-sd/--seed` siembra la configuración local del proyecto y sale; `--close-issue`/`--no-close-issue` siguen siendo mutuamente excluyentes, rechazadas con exit code 1 antes de cualquier prompt o escritura, con la regla de que el `-h` vence el conflicto. El resto (`--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` etc.) sigue sin cambios.
* **Rama del `-c`/`--commit`:** `--hook` → comportamiento silencioso y merge-aware; sin hook → `CommitApp`. La ruta sin hook pasó a exigir TTY: el routing consulta `_may_ask_the_user()` y, en un script/CI/pipe, cae en el flujo de consola en vez de abrir la TUI.
* **Flujo de Cierre de Issue:** `_prepare_close_issue(flags, base, may_ask)` corre **antes** del Stage Selector, reutiliza el contexto SCM resuelto y devuelve la decisión (línea de cierre + issue) más el registro. La línea entra en `insert_close_line(pr_desc_body, decision)` en el punto único de montaje del cuerpo. `_publish_pr_directly` devuelve **bool** para que el registro solo se limpie tras una publicación real (ADR-007).
* **Resolución de la Base del PR:** la rama `--base` pasa por `_resolve_pr_target()`, que corrige un valor obsoleto del archivo del proyecto cuando la ejecución es interactiva y, fuera de ella, reporta el rechazo. El archivo `.gitpr/conf/gitpr.config.yml` se siembra en cada invocación dentro de un repositorio, excepto en las rutas de ayuda.
* **Puerta de Actualización en los Subcomandos:** la puerta corre antes del `return`, condicionada a `may_ask and not update and not help_flag and not _subcommand_help_in_argv()`. `_may_ask_the_user()` exige un **TTY en ambos extremos** — CI, pipes, cron, hooks y el servidor MCP quedan libres.
* **`-h` Antes del Subcomando Corregido:** `_subcommand_help_in_argv()` recorre el `argv` crudo y trata el caso `gitpr -h config`, que antes abría la TUI de configuración en vez de la ayuda. Cubierto por `tests/test_cli_help.py`.
* **Subcomando `check` — 8 opciones:** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on …`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` y `--policy <name>@<version>`.
* **Subcomando `seed` — 2 opciones:** `--force/-f` (sobrescribe archivos existentes) y `--all/-a` (también transfiere todas las skills de IA).
* **Grupo `baseline` — 6 subcomandos:** `create`, `show`, `validate`, `update`, `suppress` y `unsuppress`.
* **Grupo `policy` — 7 subcomandos:** `list`, `show`, `validate`, `use`, `off`, `install` e `init`.
* **Grupo `metrics` — 8 subcomandos:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` y el oculto `hook-event`.
* **Subcomando `risk` — 4 opciones:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Subcomando `mentor` — 2 opciones:** `--finding`, `--provider`.
* **`DEFAULT_CONFIG` Derivado:** `src/config.py` importa el `DEFAULT_CONFIG` de `src/config_schema.py`, montado como `{field.key: field.default for field in FIELDS if field.seeded}` — 🆕 **56 claves** (eran 55). El drift entre código y schema dejó de ser posible.
* **🆕 Precios y Moneda en el Schema:** `GITPR_METRICS_CURRENCY` (predeterminado `USD`) y ocho campos `GITPR_METRICS_PRICE_<MODEL>_{INPUT,OUTPUT}` se añadieron a la categoría `ai`, todos con `KIND_FLOAT` y default vacío.
* **Variables de Entorno:** las cuatro `GITPR_BASELINE_*`, las dos de mentor, las dos de policy y `GITPR_RISK_INCLUDE_IN_REVIEW`. **Fuera del `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY`, las **9 `GITPR_CHECK_*`**, `GITPR_SKIP_UPDATE_CHECK` (booleano estricto) y `GITPR_ISSUE_AUTOCLOSE`, `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` y `GITPR_SKIP_PROJECT_CONFIG_SEED`.
* **Ayuda Contextual:** `gitpr -h --explain` renderiza; `HELP_MAP["explain"]` y `HELP_PRIORITY["explain"]` apuntan a `docs/explain-pr`, y `gitpr tests`/`gitpr tests generate` tienen `epilog=` propio. El `HELP_MAP` incluye `seed` y los links de `close_issue`/`no_close_issue` apuntan a `docs/issue-autoclose`.
* **Capa de Escritura del `.env`:** `read_env_file_values()` lee **solo el archivo** vía `dotenv_values`; `save_config_values()` con `set_key`; `remove_config_value()` con `unset_key`.
* **Puerta del Baseline en el Flujo:** `main.py` resuelve la puerta del baseline antes del lint, del review, del full review, de la auditoría de archivos, del review remoto y del riesgo.
* **Publicación Directa Refactorizada:** `_publish_pr_directly` (`--no-edit`) delega en el **`publish_pull_request_use_case`** — el mismo camino que la herramienta MCP `publish_pull_request` — y devuelve si la forge creó el PR.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)**

* **Interfaz Interactiva Completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en el terminal.
* **7 Pantallas Modales:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` y `NoticeScreen`.
* **Tres de Ellas También Sirven al Commit:** `StageFilesScreen`, `CommitMessageScreen` y `CommitProgressScreen` son importadas por `CommitApp` — no se duplicó ningún widget, y el helper `_with_suppressed_stdout` también se comparte.
* **`body_published` Distingue Crear de Abrir:** la `PrPublishApp` marca cuándo el cuerpo se creó/actualizó realmente en el proveedor, y no solo cuándo se abrió un PR existente. Es esa señal la que permite limpiar el registro de la issue solo tras una publicación de verdad.
* **Insignia en el Cuerpo del PR:** `attach_pr_badge()` corre en `src/main.py` justo después de que `pr_data` esté completo — **punto único de inyección**.
* **`_attach_reviewers` con resolución:** Resuelve lo que el usuario escribió **antes** de enviar, reporta lo que fue descartado y, en un `422` por lote, **repite uno a uno**. Cuando un proveedor de terceros sigue el contrato heredado y devuelve `None` en la lectura de vuelta, el log dice `(no read-back)` en vez de `attached None`.
* **Revisores Sugeridos:** Consulta al forge por revisores sugeridos; `_reviewer_suggestion_view()` construye las `resolutions` vía `resolve_candidates`.
* **Bindings:** F1 (Help), F2 (Guardar .md local), F3 (Publicar vía forge), Esc (Salir).
* **Flujo de Auto-Commit:** Linter → mensaje por IA → confirmación → commit → push → publicación del PR.
* **Modal de Progreso en el Merge:** `CommitProgressScreen` con `title` opcional y `mark_finished(show_close=False)`; el éxito muestra `✅ PR #N merged successfully!`, los conflictos y errores inesperados pasan por `_on_merge_failure`.
* **Manejo de PR Existente / Auto-Upstream / Flujo de Merge** — sin cambios (`GITPR_AUTO_MERGE`).

### **5. Módulo de API de GitHub (`src/github_api.py`)**

* **Shim Deprecado:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` y las demás funciones delegan en `src/infrastructure/scm/github_provider.py`; emite un `DeprecationWarning` y mantiene las tuplas legacy `(ok, data, status)` — ningún código nuevo debe importarlo.

### **6. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) en el diff sin gastar cuota de IA.
* **Reglas YAML:** Lee `.gitpr.linter.yml` (creado vía `--skill` o `gitpr seed`).
* **Plugins de Linter:** Reglas adicionales desde `~/.gitpr/plugins/linter/*.yml`.
* **Comodín de Extensión:** `extensions: ["*"]` significa **todo** archivo, incluidos los que no tienen sufijo y los dotfiles.
* **Ruleset de Secretos Integrado:** `load_linter_rules()` fusiona `src/security_ruleset.py` **después** de las reglas del proyecto y de los plugins. Siete reglas, todas `extensions: ["*"]`: cinco `error` y dos `warning`. La alerta **nunca repite el valor**.
* **Capa de Policy Pack:** `load_linter_rules()` aplica la fusión en capas del pack activo y **sobreescribe severidades**.
* **Hallazgos Estructurados (`findings_out`):** además de las cadenas de alerta, el motor devuelve los hallazgos normalizados del tipo canónico `src/domain/finding/`.
* **Puentes SAST Opt-In:** Semgrep, Gitleaks y Bandit corren en el camino de archivo completo **y** en el camino del diff, con deduplicación `[Gitleaks + Regex]`, enmascarado de valores (`AKIA****`) y un aviso cuando la herramienta está habilitada pero falta del `PATH`.
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=True)` en el flujo remoto.
* **Informe Consolidado:** `generate_linter_report_content()` consolida errores regex + externos en `.gitpr/reports/linter/` — generado solo cuando hay violaciones.
* **Consciente del Baseline:** los hallazgos se anotan con el estado del baseline antes de convertirse en informe.

### **7. Seguridad y Autenticación (`src/security.py`, `src/scm_settings.py`, `src/tui_issue.py`)**

* **Cifrado:** Genera la clave maestra `secret.key` en `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data`/`decrypt_data` para claves de IA y para el token de **cada** proveedor (un slot por proveedor SCM).
* **Credencial por Proveedor:** un token GitLab nunca responde por un repositorio GitHub. El `GITPR_SCM_TOKEN` crudo sigue siendo el **único** override global de CI/CD y se intenta primero en todos los proveedores.
* **Validación Multi-Forge:** `validate_or_request_scm_token(provider, repo_display, allow_prompt=True)` — 401 → loop de reautenticación preservando el borrador. `allow_prompt=False` (hooks, MCP, CI) nunca pregunta, nunca borra el token guardado y devuelve la instrucción "ejecute `gitpr --init`".
* **Secretos en la TUI de Configuración:** Los campos `KIND_SECRET` se editan enmascarados, **nunca** muestran el valor en claro y se cifran con Fernet antes de escribirse.
* **Escaneo de Secretos en el Propio Flujo:** El linter — que corre en el hook pre-commit — es una puerta de credenciales con reglas idénticas en toda máquina.
* **Enmascarado en CI:** `src/infrastructure/ci/masking.py` elimina claves privadas, AWS access key ID/secret, tokens de GitHub, Google, OpenAI y Slack, `Bearer`, basic-auth en URLs y asignaciones genéricas de contraseña **antes** de serializar el artefacto. La extensión tiene un espejo en TypeScript (`masking.ts`).

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI como Fuente Única:** `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` y escribe la caché. Acepta `force=True`, que `--update` usa para ir **siempre** a la red.
* **Ventana Deslizante de 1 Hora (`UPDATE_CACHE_TTL_SECONDS = 3600`):** el documento es `{"checked_at": <ISO>, "version": ...}`; `_cache_is_fresh()` acepta una edad en `[0, 3600)` segundos.
* **Validación en la Lectura:** un `dict` real, un timestamp parseable y una versión como cadena no vacía. Un timestamp **en el futuro** cuenta como expirado, y el formato legado con `date` se trata como expirado y se reescribe.
* **Escritura Atómica:** `tempfile.NamedTemporaryFile` en el mismo directorio + `os.replace`, con limpieza del archivo temporal en caso de fallo.
* **Fetch Blindado (`bounded_urlopen`):** un cuerpo no-JSON y un payload sin versión se **rechazan antes de entrar en la caché**. Techo de 3 s de tiempo de reloj, DNS incluido.
* **Comparación PEP 440:** `_coerce_version()` usa `packaging.version.Version`, tolerando la `v` inicial y los sufijos PEP 440; `parse_version()` mantiene el contrato de tupla para los llamadores externos. `_is_newer()` **ignora un pre-release remoto** en una instalación estable y trata una versión ilegible de cualquiera de los dos lados como desconocida.
* **Puerta Obligatoria (`enforce_update_required()`):** Devuelve `True` (tras imprimir ambas versiones y el comando pip) cuando la versión publicada es más reciente; `False` cuando está al día, cuando la versión remota es **desconocida (offline)** o cuando la verificación está apagada.
* **Semántica Estricta de la Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` se parsea contra `_SKIP_TRUTHY_VALUES` (`1`, `true`, `yes`, `on`, `y`). La nota de migración de esta semántica entró en `docs/auto-update.*` y en el tutorial de instalación.
* **`check_and_update()`:** Solo consulta e **informa**, nunca instala.
* **Versionado Centralizado:** `__version__` (**1.7.0** 🆕), `__lang_version__` (**v0.0.42** 🆕), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`. El SSOT del paquete es contrato explícito ([ADR-014](docs/adr/0014-canonical-package-version-ssot.md)).
* **✅ Estado del release — 1.6.0 y 1.7.0 publicadas:** el PyPI sirve la **1.7.0**. La **1.6.0** fue tagueada en `f5088c4`; la **1.7.0** tiene su propia sección en el `CHANGELOG.md` (`## [1.7.0] - 2026-11-10`) y un bump en `2354a30`. ⚠️ El tag `v1.7.0` en el origin todavía apunta a `b2688c4` (anterior al bump, `__version__` 1.6.0) — alinear el tag es el pendiente abierto de release.
* **⚠️ Consistencia del SSOT:** el bump `2354a30` cambió solo `src/updater.py`; `CLAUDE.md` y `GEMINI.md` siguen diciendo 1.6.0, y por eso `tests/unit/test_version_consistency.py` tiene **2 fallos** (`test_claude_md_version_matches_ssot` y `test_gemini_md_version_matches_ssot`).

### **9. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Historial de mensajes, entrada multi-línea, barra de estado con bindings visibles. `ENABLE_COMMAND_PALETTE = False`.
* **Memoria por Rama (`src/chat_memory.py`):** Historial persistido por rama, con continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`. `/tests` (y los alias localizados) delega en el **mismo caso de uso** que usa la CLI.
* **Auto-Patching (F5), Actualización de Diff (F2), Exportación de Sesión (F6).**
* **Extractor Compartido:** F5 y `ctrl+s` llaman a `patch_extractor.extract_code_blocks()` — comportamiento visible idéntico, sin lógica duplicada.
* **Ayuda Localizada en Serio:** el `ChatHelpScreen` compone cada línea vía `__()`. Los links de la ayuda pasan por `doc_url()`, con el contrato `?lang=` del sitio (`es_es`→`es`, `fr_fr`→`fr`) — formalizado en [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md).

### **10. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`; el directorio de perfil se crea **antes** de persistir.
* **5 Idiomas, 6 Diccionarios:** en_us (predeterminado/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Archivos Versionados:** el `__lang_version__` (**v0.0.42** 🆕) controla la actualización de los paquetes (`langs/*.json`).
* **✅ Cobertura:** los **seis** archivos tienen **1787 claves** cada uno (+15). El código usa las mismas claves; no hay clave sin diccionario ni huérfana. El `tests/test_i18n.py` sigue **22 en verde**.
* **🆕 La v0.0.42 tiene contenido:** las nuevas strings de precios por token, moneda, validación de floats, gráficos/pestañas ASCII e instalación del VSIX entraron en los seis diccionarios; la renovación OTA entrega traducción nueva.
* **Snapshot del Entorno (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado **inmediatamente antes** del `load_dotenv()` a nivel de módulo.
* **Caché con Indexación por Idioma:** Las respuestas de IA en caché incluyen el idioma actual en el keying MD5.
* **Fuente de Traducción en Lockstep:** una clave nueva tiene que existir en el código, en `langs/pt_br.json` (lista maestra), en los dicts FR/ES de `scripts/sync_all_langs.py` y en los valores curados de `scripts/fix_mangled_i18n_keys.py`; el extractor usa **AST**.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en background durante llamadas de IA con caracteres braille y palabras de "pensamiento".
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceptan `force=`.
* **La TUI de Commit No Usa el Spinner:** la TUI muestra `CommitProgressScreen` con su propia barra animada, y la llamada a la IA corre en un `threading.Thread` daemon. Con `--hook` el spinner clásico sigue siendo el único feedback.
* **Carga de `.env` Blindada:** un fallo de lectura no impide que arranque el spinner.

### **12. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini, DeepSeek y Ollama (local), con un primario/secundario por proveedor.
* **Modo JSON & Parámetros Deterministas:** `temperature=0.0` y `top_p=0.1`; fallback automático entre los proveedores configurados.
* **Timeout de IA:** predeterminado de **180s** (`GITPR_AI_TIMEOUT`).
* **Llamadas con `quiet`:** el release y las funcionalidades nuevas propagan `quiet` hasta la capa de IA, para que el spinner nunca corrompa una salida `--format json`.
* **Una Llamada a la Vez, con Caché:** mentor y explain reutilizan la caché MD5 por prompt.
* **Payload Ya Interpretado:** `parse_explain_payload()` y `generate_test_file` aceptan el **dict** que entrega el proveedor, con fallback a la cadena JSON cruda (respuestas antiguas de caché).
* **Supresión de Stdout en el Commit:** `_generate_commit_msg()` redirige el `sys.stdout` a `os.devnull` durante la llamada, restaurándolo en el `finally`.

### **13. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Keying por hash MD5 del diff y del prompt, con indexación por idioma.
* **Selección del Último Review (`resolve_last_review()`):** Elige el registro `review`/`fullreview` **más nuevo** para un par repo+rama — es la puerta de entrada del `gitpr fix` y de `gitpr mentor`.
* **Diff Revisado (`reviewed_diff`):** Campo en la parte superior del registro que guarda el diff efectivamente revisado.
* **Telemetría y Duración:** Persistencia de `duration_ms` y `meta_raw`. El release pasa la clave de caché por idioma declarado, por lo que cambiar el idioma de la skill genera una sección nueva en vez de reutilizar la anterior.
* **Lectura para el Dashboard:** `scan_cache_files_for_dashboard()` es un **puente pre-migración**.
* **Efecto de la Sanitización de Skill:** como `get_skill_context()` elimina el fragmento corrupto del prompt de PR, la clave MD5 cambia y las respuestas antiguas contaminadas dejan de reutilizarse.
* **`git config` Decodificado con Seguridad:** las lecturas de `user.name`/`user.email` usan `encoding="utf-8"` + `errors="replace"`.
* **El Commit Reutiliza la Caché:** la TUI llama a `generate_pr_content("commit", "commit", diff_text, provider)`, así que el mismo mensaje para el mismo diff no dispara una segunda llamada a la IA.

### **14. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`) y Arqueología por Blame (`-b`).
* **Publicación Multi-Forge:** el F3 crea la issue en el proveedor **resuelto para el repositorio** vía `provider.create_issue` — Azure DevOps lanza `ScmNotSupportedError`.
* **Map-Reduce para Issues:** El contexto por encima de ~90k tokens se divide y se unifica.
* **Manejo de 401:** Señalización de reautenticación sin cerrar la aplicación.
* **`git remote -v` Decodificado con Seguridad:** la llamada usa `encoding="utf-8"` + `errors="replace"`.
* **Registro Provider-Agnóstico:** tras una creación exitosa en cualquier proveedor soportado, `register_created_issue()` guarda el número **y el proveedor** en `.gitpr/conf/issue.yml`. Los fallos de bookkeeping se engullen — un problema local nunca convierte una creación exitosa en error — y la primera escritura agrega el archivo al `.gitignore`.
* **Métrica por Proveedor:** el evento de creación pasó a derivarse dinámicamente (`issue:<provider>_create`) en vez de estar fijo en GitHub.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y la autoría histórica con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos vía `log_blame_metric()` con profundidad y número de commits analizados.
* **Ahora también alimenta al riesgo:** el lector de historial de la puntuación (`risk_history_reader.py`) usa la misma ventana de 90 días y clasifica bugs/reverts por patrón, sin llamar a la IA.
* **Alimenta la Sugerencia de Revisores:** el análisis de blame de un PR clasifica los archivos en `files_with_blame`/`files_without_blame`, consumidos por el resumen de la sección 27.

### **16. Servidor MCP e Invocación CLI Directa (`src/mcp_server.py`)**

* **18 Herramientas MCP Anotadas:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates`, `review_remote_pr`, `get_baseline_status`, `get_usage_metrics`, `run_check` y `publish_pull_request`.
* **Parámetros Opt-In en el Contrato:** `review_code` y `full_review` aceptan `mentor: bool = False`; `generate_pr_description` acepta `explain: bool = False`. Son **puramente por parámetro** — nunca hacen OR con los valores por defecto del `.env`.
* **19 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Registry de Skills Alineado:** `mcp_server.SKILL_FILES` tiene **11 entradas** (`tests`, `explain` y `mentor` incluidas); `TestSkillRegistryAgreement` pasa.
* **Invocación CLI Directa:** `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca cualquier tool sin iniciar el servidor stdio; `gitpr-mcp --list` imprime el registry completo como JSON.
* **Aislamiento del Stdout Real:** `_write_real_stdout()` escribe en el `sys.__stdout__` original, con fallback a `buffer` o re-codificación con `errors='replace'` en páginas de código legacy de Windows.
* **Offload del Event Loop:** Decorador `_offload` (`anyio.to_thread.run_sync`) en todas las tools — el orden de los decoradores importa (`@mcp.tool` **encima** de `@_offload`).
* **Fallos como Error Estructurado:** las tools devuelven el fallo **como dato** en vez de lanzar.
* **`get_usage_metrics` con Gráficos:** el payload incluye `token_trend` (ventana de hasta 14 días) y `latency` (media/mediana/P95) — los mismos números del CLI y del dashboard, ahora también mostrados en ASCII en la TUI.
* **Credencial por Repositorio:** toda la resolución SCM pasa por `src.scm_settings`; sin token, la tool responde con el comando que lo corrige (`gitpr --init`) en vez de intentar preguntar — una tool call no puede detenerse y esperar al usuario.
* **Métricas MCP:** `run_check` y `publish_pull_request` escriben filas locales (`mcp:check`, `mcp:publish_pr`).
* **El servidor MCP nunca queda bloqueado por la puerta:** `--mcp`/`gitpr-mcp` está en la lista de exenciones desde la primera versión de la puerta.

### **17. Dashboard de Métricas TUI (`src/ui/metrics_app.py`) 🆕 Pestañas y Gráficos**

* **Alcance por Repositorio:** Etiqueta `📁 Repository: owner/repo` y filtrado estricto por proyecto.
* **Escaneo Asíncrono con Overlay:** Worker thread en background con una `ProgressBar`; las pruebas esperan `workers.wait_for_complete()` antes de asertar.
* **🆕 Dos Pestañas:** la `MetricsApp` se reorganizó en `TabbedContent` con **Events** (las secciones textuales existentes) y **Charts & Trends** (los paneles de latencia y de tokens). Los bindings `1` y `2` y `action_switch_tab` saltan entre ellas.
* **🆕 Motor de Gráficos ASCII (`src/ascii_chart.py`):** funciones puras, sin Textual y sin dependencias nuevas — `render_vertical_histogram` (histograma de latencia con ticks en Y, barras de bloque lleno/medio, conteo y porcentaje por bucket), `render_token_timeseries` (serie diaria con etiquetas de fecha, tokens compactos y un sparkline opcional) y `render_empty_card` (estado vacío enmarcado), además de los formateadores `_fmt_tokens` (`1.2M`/`45.0k`) y `_duration_ms_label` (`ms`/`s`/`min`).
* **🆕 Una Sola Pasada de Lectura:** `_load_sections` ahora devuelve `(rendered_sections, chart_data)`, capturando los payloads crudos de `latency` y `token_trend` mientras renderiza las secciones textuales — sin una segunda consulta al ledger. `_finish_scan` propaga `chart_data` a `_render_sections`, con un fallback que reconsulta los informes cuando faltan los datos.
* **🆕 Estado Vacío Instructivo:** sin ledger, la pestaña de gráficos muestra una card que apunta a `gitpr metrics migrate` en vez de un panel vacío.
* **Lectura del Ledger:** el dashboard consulta el `telemetry.db` en el hilo de trabajo, renderizando las secciones (costo, calidad, módulos, proveedores, ciclo) desde los mismos renderizadores que usa el CLI.
* **Asistente de Migración (`src/ui/metrics_migration_app.py`):** ofrece importar los archivos legados, importar **y** reconstruir desde la caché de IA, o "ahora no".
* **Artefactos de Exportación Destrackeados:** el `.gitignore` es la única fuente sobre `.gitpr/metrics/export/`.
* **Pruebas:** `tests/test_ascii_chart.py` (**9 escenarios** 🆕) cubre formateadores, estados vacíos, histograma/serie poblados, composición de pestañas y cambio de pestaña.

### **18. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Ámbito por Repositorio:** Todos los eventos indexados por `repo_name`, resuelto por `src/infrastructure/git/identity.py`.
* **Eventos de Hook, Linter y Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. `gitpr check` escribe `command="check"` con `findings_total`/`findings_errors` y el exit code.
* **Evento `issue_autoclose`:** una línea por decisión de cierre, con `asked`, `accepted` y `skipped_reason` en el `meta`.
* **Token Trend (`TOKEN_TREND_MAX_DAYS = 14`):** serie diaria continua (los días sin uso entran como cero) con sparkline y barras horizontales por día; elige el lado (input/output) con más movimiento.
* **Latency Histogram:** distribución por rangos con **media, mediana y P95**; los eventos sin duración medida quedan fuera para no arrastrar las estadísticas a cero ni inflar el rango `< 1s`.
* **El `duration_ms` ya no se pierde:** el ledger usa `meta["duration_ms"]` como fallback antes de guardar `0`.
* **🆕 Costo Configurable por Modelo:** `DEFAULT_MODEL_PRICES` (en `src/config.py`) ganó los alias `gemini-pro-latest` (**1.25 / 10.00** por 1M de tokens) y `gemini-flash-lite-latest` (**0.10 / 0.40**), que antes caían como "sin precio". Los overrides `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT` siguen venciendo la tabla; **ambas tasas son obligatorias**.
* **🆕 Moneda Configurable:** `GITPR_METRICS_CURRENCY` (predeterminado `USD`) etiqueta el total y, cuando no es USD, descarta la tabla embebida en vez de etiquetar mal un precio en dólares. Un total que deja modelos fuera sigue leyéndose `Total (partial)`.
* **Exportación y Limpieza:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` con confirmación interactiva, `bundle`/`merge` para transportar y fusionar ventanas.
* **El Commit Interactivo También Mide:** la ruta `commit` sigue guardando la línea de comando con `meta` y `modules`.

### **19. Sincronización de Idiomas de los Hooks Git**

* **Versionado Independiente:** `__scripts_version__` (**v0.0.4**, sin cambios).
* **Plantillas reescritas en la ventana v0.0.17:** `post-checkout`, `post-merge` y `pre-push` llaman a `gitpr --quiet metrics hook-event <name>`, con guardia por `command -v gitpr` y `|| true`.
* **Mapeo de Sufijos (`HOOK_SCRIPT_SUFFIXES`):** Los códigos de interfaz (`es_es`, `fr_fr`) se traducen a los sufijos publicados (`.es`, `.fr`).
* **Elección vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detectar un cambio de idioma**.
* **`effective_hook_lang()`:** Resuelve el idioma efectivo; `--lang` dejó de descartarse en esa ruta.
* **Skip de Merge-Source:** `prepare-commit-msg` salta las fuentes `message|merge|squash|commit`.
* **El `prepare-commit-msg` Sigue No Interactivo:** el hook corre con `--hook`, por lo que **nunca** abre la `CommitApp` — la TUI solo existe en el uso humano de `gitpr -c`, y solo cuando hay TTY. 🆕 La nota de migración de este comportamiento entró en `docs/commit-message-ia.*` en los 5 idiomas.
* **Los hooks corren con `--quiet` y `--hook`, así que nunca quedan bloqueados por la puerta de actualización.**

### **20. Bridge de Linters Externos y Asistente Interactivo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Asistente `--linter-setup`:** Wizard con presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e inyección del bloque `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` con la cadena de fallback local → descarga → stale → embebido.
* **TUI de Errores del Linter:** `src/ui/linter_app.py` (Textual) muestra errores críticos y warnings; en hook/quiet imprime y hace `sys.exit(1)`. `ENABLE_COMMAND_PALETTE = False`.
* **Informe Markdown:** Consolidado en `.gitpr/reports/linter/` solo cuando hay violaciones.
* **La Capa SAST Es Hermana, No un Reemplazo:** los puentes Semgrep/Gitleaks/Bandit alimentan el **mismo** pipeline de informes — y también `gitpr check`.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstracción Única (`ScmProvider` ABC):** `base.py` define el contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. y `ScmProviderError(provider, http_status, message)` — `http_status` 0 = fallo de red).
* **`IssueInfo` con `is_open`:** el flag booleano elimina las comparaciones frágiles de string de estado en la capa de decisión.
* **Las Operaciones de Issue Son de Todos los Proveedores:** GitLab normaliza `opened` → `open` y emite `Closes #N`; Bitbucket normaliza `new`/`open`/`on hold` → `open` y emite `close #N`; Azure DevOps lanza `ScmNotSupportedError` (Work Items no son issues).
* **`list_pull_requests(repo, state, since, until)` — paginado y canónico:** los cuatro proveedores traducen los estados canónicos (`open`/`merged`/`closed`/`all`) al vocabulario nativo, paginan en el cursor de cada API y filtran por una ventana de fechas inclusiva.
* **Por qué el filtro es por fecha de estado:** tres de los cuatro forges devuelven algo más amplio para un PR fusionado, así que cada uno filtra su propia respuesta contra la fecha que el estado implica.
* **`supports_merged_dates`:** atributo de clase, `False` en Bitbucket Cloud, que publica `merged_at` **vacío**.
* **`web_links.py`:** funciones puras (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) que normalizan cualquier formato de remote.
* **`get_pull_request(repo, pr_id)` — método concreto de la ABC:** el default lanza `ScmNotSupportedError` y cada forge lo implementa.
* **Resolución por Repositorio:** el registry ya no lee `GITPR_SCM_PROVIDER`; `resolve_scm_provider(config)` recibe el proveedor ya resuelto por `src/scm_settings.py` (sección 46).
* **GitHub:** `GET /repos/{o}/{r}/issues/{n}` (marcando `pull_request` y cayendo al número pedido cuando falta), `GET /repos/{o}/{r}` para `default_branch` (cadena vacía cuando falta) y el keyword `close #N`.
* **Encabezados de GitLab Sintetizados:** `changes[].diff` es un hunk suelto; `old_path`/`new_path` montan los encabezados `diff --git a/… / --- / +++`, honrando `/dev/null`. Y `overflow: true` **lanza** en lugar de revisar la mitad de un diff truncado.
* **`request_pull_request_reviewers` devuelve `list[str]`:** Devuelve los logins **efectivamente adjuntados**, leídos del cuerpo del `201`. La ruptura de contrato está documentada en [ADR-0009](docs/adr/0009-reviewer-attach-returns-read-back.md) y en `docs/suggested-reviewers.*`/`docs/scm-multiforge.*`.
* **Helpers Read-Only de GitHub:** `get_commit_author_login` y `get_user_login`.
* **Fail-Fast por Forge:** Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket exige `GITPR_SCM_USERNAME`; `create_issue` en Azure lanza `ScmNotSupportedError`.
* **Publicación de Release:** `provider.create_release()` usado por `gitpr release --publish`.
* **Publicación de PR Compartida:** `src/application/use_cases/publish_pull_request.py`, usado por el `--no-edit` y por la tool MCP. Valida título/cuerpo, **exige `provider.token` solo cuando `check_remote_state=True`**, opcionalmente verifica PR existente y rama no pusheada, enmascara mensajes de error y devuelve `PublishPrOutcome`.
* **Artefactos:** Glosario + ADR-001/ADR-005/**ADR-016**; familia `docs/scm-multiforge.*.md` en 5 idiomas; **10 archivos de prueba, 370 escenarios**.

### **22. Subcomando `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Flujo:** `git log` entre `--since` y `HEAD` → clasificación Conventional Commits → bump semántico sugerido → armado del changelog → resumen ejecutivo por IA opcional → *prepend* en `CHANGELOG.md`.
* **El Rango Dejó de Venir de `git describe`:** el origen del rango es la **sección anterior del propio changelog**, mediante dos anclas: el hash más reciente de esa sección y, como fallback, el tag de versión de la sección.
* **`_drop_released()` — la segunda línea de defensa:** cualquier commit cuyo hash corto aparezca en **otra** sección de versión se descarta, con conteo.
* **Cada viñeta pasó a ser un enlace:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **El fallo del PR merged por merge commit:** una segunda pasada (`git log --merges`) monta `hash → PR` por ancestralidad.
* **Contribuidores resueltos por la misma escalera que los revisores:** `get_commit_author_login` primero, luego `email_to_handle`, cacheados en `~/.gitpr/cache/contributors.json`.
* **La Frontera de la Sección Era un Bug Latente:** `upsert_changelog(force=True)` reemplaza desde la línea `## [x.y.z]` hasta el próximo encabezado de **nivel 2**.
* **El Front Matter Declara el Idioma de la Skill:** `src/skill_frontmatter.py` (`split_front_matter()`) lee un bloque YAML inicial con `language: <code>` (allowlist `en_us`, `pt_br`, `pt_pt`, `es_es`, `fr_fr`), **elimina el bloque** antes de que el cuerpo pase a ser instrucción de sistema y cambia el idioma del i18n durante el tiempo de la generación — headings, rótulos **y** el idioma pedido a la IA, incluso con el resumen desactivado.
* **Clasificador (`src/commit_classifier.py`)** y **Constructor con Secciones Traducibles (`src/changelog_builder.py`)**.
* **Bump Semántico (`src/version_bump.py`)** y **Publicación** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opciones en el subcomando.**
* **🆕 Estado del release:** la sección `## [1.7.0] - 2026-11-10` está commiteada (`6d1f1dc`) y el bump en `2354a30` elevó `__version__` a **1.7.0**; el PyPI sirve la 1.7.0. El tag `v1.7.0` sigue apuntando al commit anterior al bump.
* **El subcomando pasa por la puerta de actualización** cuando se ejecuta en un terminal interactivo.
* **Artefactos:** `docs/release-notes.*.md` en 5 idiomas, ADR y `tests/test_web_links.py`.

### **23. Subcomando `gitpr config` — TUI de Configuración**

* **Pantalla Master-Detail (`src/ui/config_app.py`):** Categorías a la izquierda, campos a la derecha, editados en línea. Cabecera con búsqueda (`/`) y un contador de cambios pendientes; pie `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schema Declarativo (`src/config_schema.py`) — la fuente única de verdad:** **18 categorías** y 🆕 **109 `ConfigField`** (eran 100), de los cuales **7 avanzados** y 🆕 **56 `seeded`** (eran 55).
* **`DEFAULT_CONFIG` Derivado:** el diccionario se monta a partir de `seeded=True`, así que una clave nueva en el schema ya nace sembrada o deliberadamente no-sembrada.
* **Categoría `check`:** las nueve `GITPR_CHECK_*` (`BASE`, `FORMAT`, `OUTPUT`, `FAIL_ON`, `NO_IA`, `IA_ONLY_HIGH_RISK`, `BASELINE_PATH`, `POLICY`, `TIMEOUT_SECONDS`) pasaron a aparecer aquí, con los mismos defaults del pipeline.
* **Categoría `smart_excludes`:** sustituye al antiguo rótulo `Diff Filters`; `SMART_EXCLUDES_VERSION` cambió de grupo y la categoría tiene panel propio (ver abajo).
* **Panel Smart Excludes:** lista de fuentes (proyecto, global core, global docs), `TextArea` de edición, botón de creación del archivo del proyecto, estado/nota y rastreo de dirty. El `F2` guarda el archivo del proyecto por el mismo plan de escritura.
* **Categoría `project` y el Atributo `store`:** los campos declaran dónde viven (`env` vs `project`). Los tres campos del proyecto son el path del archivo (read-only), la rama objetivo (editable) y la base efectiva con el origen (read-only). El `KIND_BRANCH` renderiza la elección de rama.
* **Categoría `scm` por Proveedor:** ganó la línea editable `PROJECT_SCM_PROVIDER`, la línea read-only `PROJECT_EFFECTIVE_PROVIDER` (proveedor en uso y origen) y un grupo `gitlab`; tokens y base URLs de **todos** los proveedores se listan siempre, y la prueba de token usa el host del propio slot.
* **Categoría `issue`:** `GITPR_ISSUE_AUTOCLOSE` y `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` aparecen aquí.
* **🆕 Tipo `KIND_FLOAT`:** el primer campo float del schema es `GITPR_METRICS_CURRENCY`, junto a los ocho campos de precio por modelo. La validación acepta vacío y rechaza un valor negativo o no numérico con el mensaje "Use a number greater than or equal to zero.".
* **🆕 Categoría `ai` con Precios:** los campos `*_INPUT`/`*_OUTPUT` son visibles cuando el `DEFAULT_AI_PROVIDER` correspondiente está seleccionado; un valor vacío preserva el comportamiento anterior.
* **Escritura por Store:** la `ConfigApp` despacha por `store`: los valores de proyecto **nunca** pasan por `save_config_values()`, se graban primero, soportan eliminación al vaciar, rechazan la rama actual y nunca sobrescriben YAML ilegible. La línea de la base efectiva se actualiza in-place tras guardar.
* **Categorías `policy` y `baseline`:** los campos del pack y los cuatro del baseline.
* **Sección Skills Alineada:** `SKILL_LABELS` tiene **11 entradas** (`tests`, `explain`, `mentor`).
* **Filtrado por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` según `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` bajo `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` bajo `[Azure DevOps]`.
* **Búsqueda Global (`/`):** Coincide con la clave o la etiqueta en todas las categorías e **ignora el filtro de visibilidad**.
* **Validación en Dos Capas:** **Offline** bloquea `F2` con un error en línea; **online** (solo credenciales cambiadas en la sesión) corre en un worker con timeout de 10s y solo bloquea ante `401`/`403`.
* **Restaurar (`Ctrl+R`):** Elimina la línea del `.env` en lugar de reescribir el valor predeterminado; `Esc` con cambios pendientes pide confirmación.
* **Secciones Project-Scoped:** Edita `.gitpr/skill/*.md`, `.gitpr/conf/gitpr.config.yml` y los smart-excludes del proyecto, grabando atómicamente y preservando CRLF/LF.
* **Descargas Forzadas:** Botones que fuerzan la redescarga de smart-excludes, traducciones, presets y thinking words vía `force=`.
* **Módulo de Links Ligero (`src/doc_links.py`):** el `doc_url()` salió de `core.py` para que la UI obtenga el link sin importar SDKs de IA. El `DOC_LANG_PARAMS` mapea `es_es`→`es` y `fr_fr`→`fr`, y el `CURRENT_LANG` se lee **en la llamada**, por lo que `set_lang()` se propaga a los links.
* **Pruebas:** 5 archivos — `test_config_app.py` (94), `test_config_schema.py` (**55** 🆕, +2), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9) y `test_config_issue_autoclose.py` (10).

### **24. Registro General de Uso (`src/usage_log.py`)**

* **Una Línea por Comando:** Escribe en `~/.gitpr/logs/<uuid5>.log`, **un archivo por día**, con el comando, los argumentos, el repositorio, el usuario y el timestamp.
* **Nombre Derivado de la Fecha:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")`.
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
* **`render.py` y el riesgo:** `compose_review_content` / `render_review_result` **anteponen** la sección `⚡ Risk Assessment` por encima de las alertas del linter, cuando `GITPR_RISK_INCLUDE_IN_REVIEW` está activo.
* **Documentación completa en los 5 idiomas:** `docs/review-pr.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md` y `.fr_fr.md`.
* **Hunk Binario en el Diff Remoto:** `filter_excluded_sections` aplica siempre `collapse_binary_hunks()`, incluso en las early exits sin patterns/secciones.
* **Pruebas:** 4 archivos en `tests/review/` (**97 escenarios**).

### **27. Resolución de Identidad del Revisor (`src/reviewer_resolution.py`)**

* **El Bug (dos fallos silenciosos encadenados):** (1) **Prefill vacío** — `handles` solo desde `email_to_handle()`; (2) **Attach no verificado** — valores pasados *verbatim*, GitHub respondió **201 sin anexar a nadie** y `_request()` retornó sin lanzar.
* **Módulo:** Un plano, sin I/O propio, **y que nunca lanza**. `resolve_candidates()` (antes de la TUI) y `resolve_typed_reviewers()` (en el momento del attach); `match_candidate()` casa exacto y normalizado por login, nombre o correo.
* **Escalera de Resolución:** handle ya conocido (sin petición) → casamiento exacto con una persona sugerida → lookup por correo → validación del login en el forge.
* **Lo que no resuelve nunca se envía:** Sale en `ResolutionOutcome.dropped` como `(value, reason_i18n)` y se descarta con aviso visible.
* **La Misma Escalera Sirve al Release:** `release_engine` reutiliza `get_commit_author_login` + `email_to_handle`.
* **Compañero Heredado:** si un proveedor de terceros devuelve `None` (contrato antiguo), el log dice `(no read-back)` y el flujo continúa — una lectura de vuelta ausente **nunca** se trata como fallo.
* **Resumen de Blame:** `ReviewerSuggestionResult` tiene `files_with_blame`/`files_without_blame`; el motor separa los archivos y el CLI imprime **una** línea consolidada (`ℹ️ Análisis de blame: X archivo(s) con blame, Y sin blame`) — solo cuando hay archivos sin blame.
* **Pruebas:** `tests/test_reviewer_resolution.py` (18) + `test_github_provider.py` (86), `test_pr_publish_app.py` (59), `test_suggest_reviewers.py` (19), `test_reviewer_suggestion.py` (18) y `test_main_suggest_reviewers.py` (8).

### **28. Subcomando `gitpr demo` — Recorrido Guiado sobre Ejemplos Grabados (`src/demo/`)**

* **Qué Es:** Un recorrido interactivo que muestra las tres salidas principales de GitPR — mensaje de commit, review y descripción de PR — **sin clave de API, sin repositorio git y sin red**.
* **Pipeline Real, Respuesta Falsa:** `FakeAIProvider` refleja `src.ai_providers.call_ai_model` **argumento por argumento**; `demo_pipeline()` parchea `src.core` y `src.metrics` para que el pipeline de producción corra **intacto**.
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
* **Artefactos:** `docs/badge.*.md` en 5 idiomas y el survey dedicado.

### **30. Subcomando `gitpr split` — Commits Atómicos por Hunk (`src/split/`)**

* **Qué es:** Un árbol de trabajo con varias preocupaciones deja de convertirse en un commit-blob. `split` lee el diff no commiteado, pide a la IA que particione los hunks por **intención lógica** y propone un commit atómico por preocupación. El árbol nunca se reescribe.
* **Paquete de 6 Archivos:** `split_plan.py`, `hunk_parser.py` (puro), `hunk_grouper.py`, `generate_split_plan.py` (read-only), `apply_split_plan.py` (**el único que muta**) y `__init__.py`.
* **La Garantía de Árbol Intacto Es Estructural:** `selective_stager.py` pre-valida **cada** grupo contra un índice temporal en HEAD y usa un `GIT_INDEX_FILE` desechable.
* **Destructivo para el Índice, No para el Árbol:** `--apply` resetea el índice a `HEAD` antes de preparar.
* **Configuración:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) y `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Los valores cero o negativos se **ignoran en favor del predeterminado**.
* **Relación con la TUI de Commit:** `split` y `-c` atacan el mismo problema en alturas distintas — `split` propone **varios** commits desde el árbol; `CommitApp` organiza **un** commit desde el índice.
* **Pruebas:** 6 archivos en `tests/split/` (**101 escenarios**).
* **Artefactos:** `docs/split-command.md` en 5 idiomas, ADR-006, `glossary-gitpr-split.md`, spec/plan y survey.

### **31. Barrido de Secretos Embebido (`src/security_ruleset.py`)**

* **Qué Es:** Siete reglas que corren en **cada** invocación del linter, fusionadas al final de `load_linter_rules()` después de las reglas del proyecto y de los plugins.
* **Las Siete Reglas:** cinco `error` bloqueantes — AWS access key ID, token de GitHub, token de Slack, clave de API de Google y clave privada — y dos `warning`: URL de conexión a base de datos con credenciales y asignación genérica de credencial, esta última tras un filtro de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Cobertura:** asignación **sin comillas**, prefijos **`ASIA…`**, **`github_pat_…`** y **`xoxc-`/`xoxd-`**, **frontera a la izquierda** en el nombre de la clave (`bypassword` no coincide) y supresión de falsos positivos de runtime (`os.getenv(...)`, `process.env.X`).
* **Por qué en el Paquete y No en la Plantilla:** una puerta de secretos debe comportarse **igual** en todas las máquinas y en todo CI.
* **Configuración:** `GITPR_LINTER_SECURITY` (predeterminado `true`; opt-out fail-open) y `GITPR_LINTER_SECURITY_DISABLED_RULES` (separadas por `;`).
* **Pruebas:** `tests/test_security_ruleset.py` (**77 escenarios**) — matriz de regex, placeholders, nivel, wildcard, merge y traducciones; la invariante "la alerta nunca repite el valor" sigue vigente.
* **Artefactos:** ADR-007, [ADR-0008](docs/adr/0008-secret-ruleset-coverage-extension.md), `glossary-gitpr-secret-scanning.md`, spec/plan/survey e informes de tarea.

### **32. Puentes SAST — Semgrep, Gitleaks y Bandit (`src/infrastructure/linter/external/`)**

* **Qué Es:** Una capa **opt-in** que conecta escáneres de seguridad de terceros al pipeline de linter existente.
* **Alcance Acotado:** Corren **solo** cuando están habilitados y **solo** sobre los archivos tocados por el diff.
* **Contrato Común:** `ExternalLinterBridge` (ABC) con ejecución `subprocess` blindada (`shell=False`, timeout estricto, `stdin=DEVNULL`, UTF-8 con `errors='replace'`).
* **La Deduplicación Es el Punto:** `deduplicate_secret_findings()` fusiona los hallazgos de Gitleaks y del ruleset de regex en el mismo archivo y línea en **una sola** entrada confirmada como `[Gitleaks + Regex]`.
* **Secretos Enmascarados:** `mask_secret_value()` garantiza que el valor nunca llegue al hallazgo, al log ni a la telemetría.
* **Resolución en Tres Capas:** `load_sast_config()` lee defaults → `.gitpr.linter.yml` (bloque `sast`, con fallback a `linter.external`) → variables `GITPR_SAST_*`. **Todo es `false` por defecto**.
* **Degradación Elegante:** Una herramienta habilitada pero ausente del `PATH` emite un warning y la ejecución continúa.
* **Pruebas:** 4 archivos en `tests/infrastructure/linter/external/` (**14 escenarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + `tests/domain/linter/test_structured_findings.py` (44).

### **33. Subcomando `gitpr tests generate` — Generación de Suites por IA (`src/domain/tests_generation/`, `src/application/`)**

* **Qué Es:** Genera archivos de prueba completos y ejecutables a partir del diff actual, de un archivo específico o de un hallazgo de review, **respetando la convención del repositorio** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Capa de Dominio:** `TestFramework` (enum) y las dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest`; `detect_test_framework()` detecta desde archivos de configuración, manifiestos y el contenido del directorio de pruebas; `build_test_scaffold()` calcula la ruta de destino convencional por framework.
* **Capa de Aplicación (`generate_test_file.py`):** Orquesta la detección, la resolución del scaffold, el prompt, la IA, el parsing JSON y la escritura opcional. `validate_test_syntax()` corre el toolchain local (`php -l`, `node --check`, `python -m py_compile`) — un fallo de validación es un **warning**, no un error.
* **Presentación:** grupo `tests` con `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); el dry run es el comportamiento por defecto y sobrescribir una prueba existente pide confirmación con **No** preseleccionado.
* **Documentado en 5 idiomas:** `docs/tests-generate.*.md` (5 archivos), con enlace en `gitpr tests generate` (epilog) y en el README.
* **Skill:** `.gitpr.tests.md` en `SKILL_LABELS`, `mcp_server.SKILL_FILES` y `SKILL_FILES_BY_TYPE`.

### **34. Subcomando `gitpr explain` y la Flag `--explain` — Reviewer Guide (`src/domain/pr/`)**

* **Qué Es:** Una guía centrada en quien va a **revisar** — qué cambia, por qué cambia, dónde enfocarse y cuál es el riesgo de regresión.
* **Capa de Dominio (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, el renderer `build_explain_markdown()` y `parse_explain_payload()`, que tolera salida no JSON y **detecta placeholders** `[FILL]`/`[TODO]`.
* **`parse_explain_payload()` Acepta `dict | str | None`:** el proveedor entrega el JSON **ya interpretado**; la función acepta las tres formas y normaliza los campos `None`.
* **Rama de Clave Faltante:** `generate_pr_explanation` imprime el error estándar de clave faltante y sale con **1**.
* **Capa de Aplicación (`generate_pr_explanation.py`):** Resolución del proveedor, validación de la clave, carga del contexto de skill (`explain`), prompt, invocación y parsing.
* **Dos Puntos de Entrada:** `gitpr explain` (`--provider`, detección de diff ausente antes de cualquier IA) y la flag `--explain` en la CLI raíz. La extensión expone el mismo camino en `GitPR: Generate PR Draft (with Reviewer Guide)`.
* **`-h --explain` Corregido:** la opción usaba `dest` `explain_flag`, pero el `HELP_MAP` buscaba `explain` — la ayuda contextual vuelve a renderizar.
* **Documentado en 5 idiomas:** `docs/explain-pr.*.md` (5 archivos), con enlace en `HELP_MAP["explain"]`, en el epilog del comando y en el README.
* **Configuración:** `GITPR_EXPLAIN_BY_DEFAULT` (predeterminado `false`).

### **35. Suite Determinista y CI (`.github/workflows/tests.yml`, `release.yml`)**

* **Qué Es:** El workflow que corre la suite en **Python 3.10** (el piso declarado en `pyproject.toml`) y **3.13**, con `fail-fast: false`.
* **`tests/conftest.py` Hermético:** Fija `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` y `LANG_VERSION` en la versión del código, además de `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true` y `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
* **Fixtures Autouse Contra Fuga de Perfil:** `english_interface` y `no_hook_script_sync` impiden que el idioma, los flags de linter y el auto-sync de hooks del perfil del desarrollador se escapen entre las pruebas de CLI.
* **El Orden Importa:** `src.updater` se importa **antes** de que se definan las variables de idioma.
* **Estabilidad de las Pruebas de Worker:** `test_config_app.py` y `test_metrics.py` esperan `workers.wait_for_complete()` antes de asertar.
* **Dependencia del Workflow:** El runner limpio no tiene `~/.gitpr`, y `test_demo_isolation.py` aserta que el perfil existe — el job crea explícitamente el directorio y el `.env` vacío.
* **🆕 En esta ventana la suite ganó 14 escenarios y 1 módulo:** `test_ascii_chart.py` (9), `test_config_schema.py` (53 → **55**), `test_metrics.py` (174 → **175**), `test_full_diff_encoding.py` (8 → **9**) y `unit/test_check_ci_docs_parity.py` (2 → **3**). **Total: 3436 escenarios en 160 archivos.**
* **🆕 Prueba de Paridad del `action.yml`:** `tests/unit/test_check_ci_docs_parity.py` ahora también exige que ADR-0011 exista y referencie `action.yml`, `gitpr check`, `--linter`, `--fullreview`, `SARIF` y `docs/check-ci.md`.
* **⚠️ 2 fallos conocidos:** `tests/unit/test_version_consistency.py::test_claude_md_version_matches_ssot` y `::test_gemini_md_version_matches_ssot` fallan porque `CLAUDE.md`/`GEMINI.md` siguen diciendo 1.6.0 mientras `src/updater.__version__` ya es 1.7.0.
* **Workflow de la extensión:** `.github/workflows/extension.yml` corre `npm install`, compila, prueba y empaqueta el VSIX. 🆕 El job `github-release` adjunta el `.vsix` a los GitHub Releases de los tags `ext-v*`; el job `publish-marketplaces` evalúa cada PAT de forma independiente y es no bloqueante.
* **Workflow de Release:** `release.yml` corre pruebas, verifica la consistencia tag ↔ `src.updater.__version__`, builda sdist/wheel, valida con `twine check --strict`, publica por Trusted Publishing (OIDC) y crea el GitHub Release. El SSOT de versión también tiene prueba propia (`tests/unit/test_version_consistency.py`).

### **36. Ledger de Telemetría SQLite (`src/ledger.py`, `src/infrastructure/git/identity.py`)**

* **El Problema No Era el Formato, Era la Unión:** los eventos se escribían como archivos JSON por comando, desde un **hilo daemon**, y luego se religaban a la caché de IA por un emparejamiento heurístico de `(repo, branch, action, minute)`.
* **La Escritura Es Síncrona, y Esa Es la Decisión:** `log_command_metric()` resuelve el contexto git **una vez por proceso** y escribe la fila de inmediato.
* **Schema Versionado:** `SCHEMA_VERSION` + `PRAGMA user_version`, claves UUID, WAL + `synchronous=FULL`, y `LedgerVersionError` para rechazar un bundle más nuevo **antes** del `ATTACH`.
* **Invariante "Nunca Nace Vacío":** `ensure_ledger()` solo crea la base de datos **junto** con la absorción de los JSON legados.
* **Una Ventana, Un Significado:** `--since`/`--until`/`--days` estrechan **todas** las superficies de lectura; la sección de ciclo asume los últimos 30 días y **nombra la ventana**.
* **Cada Métrica Se Calcula Una Vez y Se Renderiza Una Vez:** los pares `(style, text)` hacen que la CLI y el dashboard de Textual cuenten la **misma** historia.
* **Costo con Tabla Embebida y Override:** precios de lista de los cuatro modelos, sobreescritos por `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Ambas tasas son obligatorias**. 🆕 Los alias de Gemini también tienen precio y `GITPR_METRICS_CURRENCY` controla la etiqueta.
* **`modules` Normalizado:** las rutas pasan a dos segmentos (`src/fix`), `(root)` y `(no module)`.
* **Transporte por Bundle:** `bundle` escribe la ventana en un `.db` autónomo; `merge` lo anexa e inserta por UUID.
* **`cache_backfill` Se Queda Fuera:** las filas reconstruidas desde la caché se excluyen de los agregados de ejecución.
* **`issue_autoclose` Entra al Ledger:** una línea por decisión, con `asked`, `accepted` y `skipped_reason`.
* **`duration_ms` con Fallback de `meta`:** los eventos que solo transportaban la duración en `meta["duration_ms"]` dejaron de guardarse como `0`.
* **Identidad Git sin Forge (`identity.py`):** etiqueta del repo, rama y autor en una **única** llamada al subproceso.
* **La Sección de Ciclo Es la Única que el Ledger No Responde:** lista PRs fusionados vía `ScmProvider.list_pull_requests`; cada fallo degrada a una sola línea — `No leído: <motivo>`.
* **Presentación Completa:** grupo `gitpr metrics` con 8 subcomandos, un dashboard que lee el ledger en un worker thread y el asistente de migración de tres opciones.
* **Pruebas:** `tests/test_metrics.py` (**175 escenarios** 🆕).

### **37. Subcomando `gitpr mentor` y la Flag `--mentor` — Mentor Mode (`src/domain/mentor/`)**

* **Qué Es:** Un review dice *qué* está mal y casi nunca *por qué importa*. Mentor Mode enriquece los hallazgos con **qué está pasando**, **por qué importa**, una **analogía** cotidiana y los **conceptos que estudiar**.
* **Capa de Dominio Pura (`mentor_explanation_builder.py`):** sin I/O, convierte el payload de la IA en un `MentorExplanation` validado e impone **honestidad epistémica**: sin evidencia suficiente, la explicación **lo dice**; las URLs alucinadas se **eliminan**; los IDs desconocidos se **rechazan**.
* **Capa de Aplicación (`generate_mentor_explanation.py`):** recolecta candidatos, **lotes de como máximo 10 hallazgos**, ordenados por severidad, reutilizando la caché MD5 por hash de prompt.
* **Dos Puntos de Entrada:** `gitpr mentor [--finding <id>] [--provider <name>]` y la flag `--mentor`, que añade la sección al review normal.
* **Opt-In Estricto:** `GITPR_REVIEW_MENTOR_MODE` (predeterminado `false`) y `GITPR_MENTOR_INCLUDE_ANALOGY` (predeterminado `true`), editables en la TUI de configuración.
* **Skill y Registry:** `.gitpr.mentor.md` registrado en `SKILL_FILES_BY_TYPE`, en `mcp_server.SKILL_FILES` y en `SKILL_LABELS`, con plantillas en **5 idiomas**.
* **Pruebas:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) y `tests/test_mentor_cli.py` (4) — **todos en verde**.
* **Artefactos:** `docs/mentor-mode.*.md` en **5 idiomas**, ADR-009, `glossary-gitpr-mentor.md`, spec e informes de tarea.

### **38. Subcomando `gitpr risk` — Puntuación de Riesgo Determinista (`src/domain/risk/`)**

* **Qué es:** Una puntuación **0–100** con nivel `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculada **sin IA** a partir de señales locales.
* **Capa de Dominio Pura (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums y dataclasses; pesos, umbrales, patrones de ruta crítica, glob matching y detección de pruebas/no ejecutables cargados desde YAML; cálculo por archivo con **clamp 0–100** y agregación 50/30/20.
* **La Fórmula Se Declara, No Se Aprende:** **50%** el archivo de mayor riesgo, **30%** el promedio ponderado por líneas y **20%** la evidencia crítica.
* **Adaptadores de Infraestructura:** `risk_history_reader.py` lee `git log` y extrae commits, bug fixes y reverts dentro de una ventana de **90 días**; `test_matcher.py` casa archivos de prueba ↔ producción por convención.
* **Caso de Uso (`calculate_risk.py`):** correlaciona secciones del diff, hallazgos, emparejamiento de pruebas e historial en `FileRisk`/`PullRequestRisk`, **degradando con advertencia** cuando una señal no está disponible.
* **Path Tolerante:** la agregación del `check` acepta `path` **o** `file_path` — el reporte de riesgo no degrada en silencio.
* **Tres Consumidores:** el comando `gitpr risk`, la sección `⚡ Risk Assessment` añadida a los reviews y al review remoto de PR, y el **prompt de IA** como contexto **read-only**. `gitpr check` usa el mismo `execute_calculate_risk`.
* **Opt-Out:** `GITPR_RISK_INCLUDE_IN_REVIEW` (predeterminado `true`).
* **Configuración por Proyecto:** `templates/gitpr.risk.yml` (o `.gitpr/skill/gitpr.risk.yml`) sobrescribe pesos, umbrales y rutas críticas.
* **Pruebas:** `tests/domain/risk/` (2 archivos, 10), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) y `tests/test_risk_cli.py` (4).
* **Artefactos:** `docs/risk-scoring.*.md` en **5 idiomas**, ADR-010, `glossary-gitpr-risk-scoring.md` e informes de tarea.

### **39. Subcomando `gitpr policy` — Policy Packs Versionados (`src/domain/policy/`, `src/infrastructure/policy/`)**

* **Qué Es:** Un **pack YAML versionado** que lleva, en un solo artefacto, las decisiones de calidad de un repositorio: skills, reglas de linter, severidades, rutas críticas, pesos de riesgo y convenciones de PR/commit.
* **Capa de Dominio (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`):** parsing de manifiesto con **schema cerrado**, verificación de compatibilidad y credenciales, **checksum SHA-256** y un resolvedor de grafo con precedencia y composición.
* **Capa de Infraestructura:** `local_policy_repository.py` resuelve el pack desde **tres orígenes** (`.gitpr/policies`, `~/.gitpr/policies` y el paquete embebido) y `policy_pack_loader.py` lo carga.
* **Casos de Uso:** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` y `validate_policy_pack`.
* **Cuatro Packs Integrados:** `laravel-quality`, `node-quality`, `php-security` y `vue-quality`, cada uno con `policy.yml` y `linter.yml`, distribuidos vía **`package-data`**.
* **Consumo Integrado en Cuatro Puntos:** `calculate_risk`, `config.load_linter_rules`, `core.get_skill_context` y la **clave de caché de PR** (`cache_scope`). `gitpr check` resuelve el pack **antes que todo** y sale con código 2 si es inválido.
* **CLI:** `gitpr policy {list, validate, show, use, off, install, init}`, con confirmación (`--yes`/`--force`), detección de stack en `init` y registro de la elección en `.gitpr/policy.lock.yml`.
* **Configuración:** `GITPR_POLICY_ENABLED` y `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`.
* **Retrocompatibilidad:** sin un pack activo, el comportamiento se **preserva** — cubierto por una prueba de integración.
* **Dependencia:** `packaging>=23.0` (SemVer/PEP 440).
* **Pruebas:** `tests/domain/policy/` (4 archivos, 126), `tests/application/use_cases/` (4 archivos de policy, 89), `tests/test_policy_cli.py` (26) y `tests/integration/test_policy_effect_on_review_linter_risk.py` (31).
* **Artefactos:** `docs/policy-packs.*.md` en **5 idiomas**, ADR-011, `glossary-policy-packs.md`, spec/plan/survey.

### **40. Subcomando `gitpr baseline` — Baseline y Supresiones Auditables (`src/domain/baseline/`)**

* **Qué Es:** Los linters y los reviews vuelven a reportar los mismos hallazgos preexistentes en cada ejecución. El baseline registra el **snapshot de los hallazgos que el repositorio ya conoce** en `.gitpr/baseline.json`, calcula la huella de cada uno de forma estable entre ejecuciones y permite que el pipeline clasifique lo que es nuevo — **solo `new` bloquea**.
* **Capa de Dominio (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`):**
  * **Fingerprint estable:** identidad de la regla, ruta normalizada, líneas y **sha256 de la línea con espacios colapsados** — la línea cruda **nunca** se guarda.
  * **Manifest canónico:** JSON canónico con checksum, validación que rechaza una clave desconocida, una versión de schema/huella fuera del rango soportado, una invariante de supresión/deuda rota, una huella duplicada y un rango de líneas inválido.
  * **Comparador:** clasificación, conteos por estado y resolución de entradas — `resolved` y luego `back` se lee como **nuevo**, que es la lectura conservadora.
  * **Política de Supresión:** supresiones, deuda aceptada, overrides y validadores, con emparejamiento en capas por **especificidad y precedencia**, más seguimiento de deuda vencida.
* **Cinco Casos de Uso:** `baseline_gate`, `baseline_summary`, `compare_against_baseline`, `create_baseline` y `suppress_finding`.
* **Integración con Policy Pack:** el pack puede llevar un bloque `baseline:`, validado por los **mismos** validadores.
* **El Repositorio Es el Único Escritor:** `local_baseline_repository.py` es el **único** lector/escritor de `.gitpr/baseline.json` y `.gitpr/baseline.overrides.yml`.
* **Configuración:** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` y `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`.
* **Opt-In y No-Op:** apagada o sin un baseline existente, la funcionalidad **no lee ningún archivo y no imprime nada**.
* **⚠️ La Excepción Deliberada:** un archivo de baseline que existe pero **no puede aplicarse** hace que lint, review, full review, auditoría de archivos, review remoto y riesgo **aborten con código de salida 1**. En `gitpr check`, un baseline corrupto sale con **código 2**.
* **El Contrato se Volvió Prueba:** `tests/integration/test_baseline_exit_code_contract.py` (8 escenarios) fija los dos códigos.
* **Alcance:** CLI (6 subcomandos, todos con `--format json`), MCP (`get_baseline_status` + `baseline://summary`), review remoto de PR y el flujo de lint.
* **La Refactorización que la Funcionalidad Exigió:** `NormalizedFinding` pasó a tipo canónico en `src/domain/finding/`.
* **Pruebas:** `tests/domain/baseline/` (5 archivos, **126**), `tests/application/use_cases/` (5 archivos, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) y `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34) + `tests/integration/test_baseline_exit_code_contract.py` (8).
* **Artefactos:** `docs/baseline-suppressions.*.md` en **5 idiomas**, ADR-012, `glossary-baseline.md`, spec/plan/survey e informe de tarea.

### **41. Subcomando `gitpr check` — Puerta de CI Unificada (`src/domain/check/`, `src/infrastructure/ci/`)**

* **Qué es:** Un **punto de entrada único** para pipelines de CI que orquesta análisis estático, puentes SAST, escaneo de secretos, review por IA opcional, puntuación de riesgo y clasificación por baseline, y emite **un artefacto estandarizado** con exit code determinista.
* **El Orden del Pipeline Es Contrato:** (1) resolución de la policy — inválida sale con `2`; (2) extracción del diff con smart excludes; (3) linter regex + secretos + puentes externos; (4) puntuación de riesgo preliminar; (5) **review por IA opcional**; (6) clasificación por baseline; (7) evaluación del umbral `--fail-on`; (8) generación del artefacto con **enmascarado de secretos**.
* **Capa de Dominio (`src/domain/check/`):** `check_types.py` (severidad normalizada en tres niveles con alias de la especificación); `check_config.py` (precedencia **CLI > env > defaults**); `check_output.py` (despacho por formato y escritura atómica).
* **Capa de Infraestructura (`src/infrastructure/ci/`):** `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (testsuites con `<failure>` y `<system-out>`) y `masking.py`.
* **Use Case (`run_check.py`):** `resolve_diff_base()` (explícito → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), ejecución del linter y de los bridges, score de riesgo, review opcional, baseline y sobre final. El stdout emite **solo el artefacto**. La agregación de riesgo acepta `path` **o** `file_path`.
* **Códigos de Salida:** `0` éxito; `1` hallazgo contable en el umbral; `2` error de ejecución.
* **CLI:** `gitpr check` con 8 opciones y 9 variables `GITPR_CHECK_*`, también en la TUI de configuración (categoría `check`).
* **GitHub Action Reescrita:** `action.yml` pasó a un único paso `gitpr check`. **Ruptura declarada** para workflows que dependían de `--linter` + `--fullreview`. 🆕 El encabezado del `action.yml` ahora lleva la nota de migración y el enlace a `docs/check-ci#7-migration-guide`; la decisión está formalizada en [ADR-0011](docs/adr/0011-action-unified-check-migration.md).
* **La Nota de Migración se Volvió Prueba:** `tests/unit/test_check_ci_docs_parity.py` exige las 5 variantes de idioma de `docs/check-ci.*`, las 7 secciones `## 1.`–`## 7.` y los términos `action.yml`, `--linter`, `--fullreview`, `gitpr check` y `SARIF` en la sección de migración — más el término **Legacy CI action contract** en el `CONTEXT.md`. 🆕 Ahora también exige ADR-0011 y las referencias en `action.yml`.
* **Pruebas:** `test_run_check.py` (8), `test_check_types.py` (8), `test_check_config.py` (3), `test_check_output.py` (3), `test_masking.py` (6), `test_sarif_builder.py` (2), `test_junit_builder.py` (2), `test_check_cli.py` (5), `test_baseline_exit_code_contract.py` (8) y `test_check_ci_docs_parity.py` (**3** 🆕).
* **MCP:** la herramienta `run_check` devuelve el envelope JSON del mismo pipeline, con `no_ia` por defecto en `true`.
* **Artefactos:** `docs/check-ci.*.md` en **5 idiomas**, ADR-013 + 🆕 ADR-0011, `glossary-check-ci.md`, spec, `plansfacts` y survey.

### **42. Extensión de Editor — VS Code y Cursor (`extensions/vscode/`)**

* **Qué es:** Una extensión **cliente fino** que lleva los flujos de GitPR al editor sin reimplementar nada: inicia `gitpr-mcp` por stdio y llama a las herramientas existentes. Claves, tokens, prompts y caché se quedan en `~/.gitpr/`, cifrados con Fernet.
* **Arquitectura:** `GitPrMcpClient` sobre `@modelcontextprotocol/sdk` + un `SessionManager` con **una sesión por carpeta** (multi-root) + verificación de contrato al conectar: las 7 herramientas obligatorias deben existir; si no, `MissingMcpToolsError` indica `pip install --upgrade gitpr-cli`.
* **8 Comandos:** `gitpr.commit`, `gitpr.review`, `gitpr.fullReview`, `gitpr.reviewMentor`, `gitpr.generatePr`, `gitpr.generatePrExplain`, `gitpr.publishPr` y `gitpr.check`.
* **Publicación en Dos Pasos:** `generatePr` abre un documento Markdown editable; `publishPr` pide confirmación modal y maneja `success`, `needs_push`, `already_exists`, `auth_required` y `error`.
* **Diagnósticos en el Panel Problems:** `run_check` se convierte en una `DiagnosticCollection("gitpr")`.
* **Enmascarado en Doble Profundidad:** `masking.ts` refleja `src/infrastructure/ci/masking.py`.
* **Localización de la Extensión:** bundles en **en, es, fr y pt-br** (4 idiomas) — la UI de GitPR mantiene 5.
* **Metadatos de Marketplace:** el `package.json` tiene `icon` (`images/icon.png`), `homepage` y `bugs.url`; el paquete transporta `LICENSE` (LGPL-2.1), condición mínima para un listing creíble.
* **🆕 Release del VSIX Desacoplado:** `extension.yml` ganó un job `github-release` dedicado que adjunta el `.vsix` compilado a los GitHub Releases oficiales de los tags `ext-v*` — el artefacto del editor dejó de depender del release del paquete Python.
* **🆕 Puerta de Marketplace Granular:** el job `publish-marketplaces` evalúa `VSCE_PAT` y `OVSX_PAT` de forma **independiente**, no bloquea el resto del workflow y reporta cada resultado en `$GITHUB_STEP_SUMMARY`. El término de dominio pasó de *Fail-fast secret check* a **Granular marketplace gate**.
* **🆕 Instalación en Un Clic:** `docs/editor-extension.*` (5 idiomas), `README.md` y `docs/marketplace-publishing-guide.md` ganaron instrucciones de instalación del VSIX por CLI y por GUI, con enlaces directos a los releases `ext-v*`.
* **Lo Que la Extensión No Hace:** no commitea, no hace push y no publica sin confirmación. El comando `gitpr.commit` de la extensión llama a la tool MCP `generate_commit_message` — **no** abre la TUI, que es exclusiva del terminal. El cierre automático de issue también es **exclusivo del CLI**.
* **Pruebas:** suite Mocha propia (7 escenarios, incluyendo stdio real; el timeout de la prueba de integración subió de 20s a 60s) + `tests/test_mcp_server.py` (117 escenarios).
* **Artefactos:** `docs/editor-extension.*` en **5 idiomas**, `docs/marketplace-publishing-guide.md`, ADR-014, `glossary-editor-extension.md`, specs, `plansfacts` e informes.

### **43. TUI de Commit Interactivo (`src/ui/commit_app.py`)**

* **Qué es:** `gitpr -c`/`--commit` dejó de ser un pipeline silencioso — validaba el árbol, pedía el mensaje a la IA y hacía el commit de inmediato, sin dejar elegir archivos, revisar el texto o rechazar una mala sugerencia. Ahora es una `CommitApp(App)` de **4 etapas** que mantiene al desarrollador en el control hasta el `git commit`.
* **Paquete:** un solo módulo, `src/ui/commit_app.py` (220 líneas). Los tres modales se **importan** de `src/ui/pr_publish_app.py` (`StageFilesScreen`, `CommitMessageScreen`, `CommitProgressScreen`) junto con `_with_suppressed_stdout` — **cero widget duplicado**.
* **Las 4 Etapas:**
  1. **Stage Selector:** si hay archivos fuera del índice, abre `StageFilesScreen` con checkboxes, "Select All", "Deselect All", "Stage Selected", "Skip Staging" y "Cancel". Índice ya completo → la etapa se salta.
  2. **Generación:** lee `get_git_staged_diff()`, cae a `get_git_diff()` cuando nada está preparado, y llama a `generate_pr_content("commit", "commit", diff_text, provider)` en un `threading.Thread` daemon mientras `CommitProgressScreen` anima.
  3. **Revisión:** `CommitMessageScreen` muestra el mensaje, que puede editarse antes de confirmar — el mensaje **editado** es el que va a git.
  4. **Ejecución:** un nuevo `CommitProgressScreen` corre `execute_git_commit(append_coauthor_trailer(msg))` y registra el SHA en el log.
* **Todo Fallo Se Vuelve Estado, No Excepción:** la IA rota cae a `__("Code update")`; el commit que lanza excepción se vuelve `failed` con la salida capturada. Estados terminales: `no_changes`, `cancelled`, `committed`, `failed`.
* **Auto-Exit al Tener Éxito:** `log.mark_finished(False)` quita el botón, y `_schedule_auto_exit()` registra `set_timer(0.6, self.exit)` — el usuario vuelve al prompt sin pulsar nada. **En error el Close se mantiene**, porque es ahí donde el log del fallo queda legible.
* **`--hook` Intacto:** el camino de hook sigue sin TUI y merge-aware (`is_merge_in_progress()` → `return` silencioso). El `prepare-commit-msg` nunca abre la aplicación.
* **Guarda de TTY:** sin `--hook`, el routing consulta ahora `_may_ask_the_user()`; con TTY, abre la `CommitApp`; sin TTY (CI, pipe, cron), cae en el flujo de consola silencioso.
* **Configuración:** `GITPR_SKIP_UNSTAGED_CHECK` (salta el Stage Selector) y `GITPR_AUTO_STAGE` (hace stage de todo y sigue), ambos en el `DEFAULT_CONFIG` y en el schema de la TUI de configuración.
* **Detalles de Textual:** `ENABLE_COMMAND_PALETTE = False`; `on_button_pressed` trata `#btn_close`; `_on_commit_progress_dismissed` lee `_commit_success` en lugar de asumir éxito cuando el modal se cierra.
* **Nota de Migración:** 🆕 `docs/commit-message-ia.*` (5 idiomas) ahora dice explícitamente que, sin TTY, `-c` cae en el flujo de consola y que `--hook` sigue siendo el camino de CI.
* **Pruebas:** `tests/test_commit_app.py` (**17 escenarios**) — cada transición de estado y callback, un flujo headless completo y `TestCliCommitRouting` (routing con y sin TTY).
* **Dominio:** `CONTEXT.md` mantiene los términos **Stage selector** y **Commit flow**.
* **Artefactos:** `docs/commit-message-ia.*.md` en los **5 idiomas** y el informe de tarea de Gemini.

### **44. Cierre Automático de Issues (`src/issue_autoclose/`) — Multi-Forge**

* **Lo que era:** el issue creado con `gitpr -is` quedaba huérfano — el número existía solo dentro de la TUI de issue y se perdía en el instante en que esta se cerraba. Todo PR tenía que vincularse a mano, y el merge no cerraba nada.
* **Paquete de 5 archivos:** `issue_registry.py`, `register_created_issue.py`, `resolve_close_issue.py`, `insert_close_line.py` y `__init__.py`. Cada uno es una responsabilidad: persistir, registrar en la creación, decidir en la ejecución siguiente e insertar en el cuerpo.
* **Registro Provider-Neutro (`IssueRegistry`):** el archivo pasó de `.gitpr/conf/github.yml` a **`.gitpr/conf/issue.yml`**, con un campo `provider` (default `github`). El `github.yml` heredado se **lee** de forma transparente cuando el nuevo no existe y se borra en la siguiente escritura/limpieza — migración sin intervención del usuario.
* **Auto-Ignorándose con Precisión:** `record()` corre `git check-ignore` antes de la primera escritura y agrega exactamente una línea al `.gitignore` cuando el archivo no está cubierto — `.gitpr/conf/` **no** se ignora por entero, porque `gitpr.config.yml` se versiona a propósito ([ADR-015](docs/plans/ADR-015-project-local-pr-base.md)).
* **Registro en la Creación:** `register_created_issue()` guarda el número **y el proveedor** de cualquier proveedor soportado y engulle fallos de bookkeeping — un problema de escritura local nunca convierte una creación exitosa en error. El desenlace antiguo `not-github` pasó a `no-provider`.
* **Decisión (`resolve_close_issue`):** barre por flags y configuración, pero **no** ya por proveedor; cada proveedor sin soporte degrada a `skipped_reason="not-supported"` en vez de ser ignorado. Verifica que la issue sigue **abierta** y **no es un PR** (prefiriendo `IssueInfo.is_open`), borra registros muertos (404, cerrada, número de PR) y pregunta **una vez**, avisando cuando el PR apunta a una rama distinta de la default.
* **Línea Idempotente (`insert_close_line`):** detecta cualquier keyword existente (`close/closes/closed`, `fix/fixes/fixed`, `resolve/resolves/resolved`, con prefijo `owner/repo#N` opcional, case-insensitive) y nunca duplica la línea.
* **CLI:** `--close-issue` y `--no-close-issue` — mutuamente excluyentes, rechazadas antes de cualquier prompt o escritura; el `-h` vence el conflicto.
* **Conexión en el Flujo de PR:** `_prepare_close_issue` corre **antes** del Stage Selector y reutiliza el contexto SCM resuelto; `_publish_pr_directly` devuelve si la forge realmente publicó; `PrPublishApp.body_published` distingue crear/actualizar de solo abrir un PR existente. El registro solo se limpia después de una publicación real (ADR-007).
* **Contrato SCM:** `IssueInfo` (state en el vocabulario del proveedor, `is_open` y `is_pull_request`), `get_issue()`, `get_default_branch()` y `closing_line()` son defaults concretos de la ABC; GitHub, GitLab y Bitbucket implementan (sintaxis `Closes #N` en GitLab, `close #N` en GitHub/Bitbucket), y Azure DevOps lanza `ScmNotSupportedError`.
* **Configuración:** `GITPR_ISSUE_AUTOCLOSE` (default `true`, opt-out) y `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` (default `5`, int positivo), expuestas en la categoría `issue` de la TUI.
* **Telemetría:** una línea `issue_autoclose` por decisión (`asked`, `accepted`, `skipped_reason`).
* **Pruebas:** 5 archivos en `tests/issue_autoclose/` (**126 escenarios**) — round-trip del registro, schema, corrupción, fallo de escritura atómica, `.gitignore`, migración del heredado, decisión, idempotencia de la línea, flags, cableado y semántica de `body_published`.
* **Artefactos:** [ADR-010](docs/adr/0010-multiforge-issue-autoclose.md), `docs/specs/issue-autoclose/` (12 archivos), `docs/issue-autoclose.*` en **5 idiomas**, `docs/issue-tui-help.*`, `docs/config-tui.*` y `docs/metricas-telemetria.*`, README en los 5 idiomas e informes de tarea.

### **45. Base del PR por Proyecto (`src/project_config.py`, `src/ui/pr_base_app.py`)**

* **Lo que era:** la rama de destino del PR vivía solo en el `~/.gitpr/.env` de la máquina (`PR_DEFAULT_BASE`) o se adivinaba por `origin/HEAD`. Un repositorio que apunta a `develop` dependía de que cada desarrollador configurara la misma variable, y el error (PR abierto contra la rama equivocada) solo aparecía en la forge.
* **Lo que es:** `.gitpr/conf/gitpr.config.yml` — archivo **versionado con el proyecto** que carga la base del PR, editado por la TUI de configuración y validado antes de cualquier commit/push. Un resolvedor único sirve a la CLI y al MCP, así que todos los puntos de entrada coinciden en la precedencia y en las reglas de fallo.
* **Resolvedor (`src/project_config.py`):** `resolve_pull_request_base()` — `--base` → archivo del proyecto → `PR_DEFAULT_BASE` → detección `origin/HEAD` → `main`. Lectura/escritura YAML preserva claves ajenas; seeding idempotente; listado de ramas; detección de la rama actual; verificación contra el remote.
* **Segunda Sección (`scm:`):** el archivo también transporta `scm.provider` (lectura/escritura), con las dos secciones independientes y rechazo a sobrescribir un bloque `scm` que no sea mapping. Es ese override el que vence la detección por el remote (sección 46).
* **Fallo Antes de que Nazca el PR:** `BaseBranchError`/`CurrentBranchError`; la base debe existir en `origin/<branch>` y diferir de la rama actual — sin `origin` o con ref ausente, el flujo se detiene con una pista accionable en vez de abrir un PR condenado.
* **TUI de Configuración:** categoría `project` con atributo `store` (`env` vs `project`), `KIND_BRANCH` y tres campos: path del archivo (read-only), rama objetivo (editable) y base efectiva con el origen (read-only).
* **Modal de Reparación (`src/ui/pr_base_app.py`, 98 líneas):** cuando el objetivo es la propia rama actual y la ejecución es interactiva, un modal enfocado pide la rama sustituta y la graba en el archivo del proyecto.
* **CLI:** `--base` pasa por `_resolve_pr_target()`; el archivo se siembra en cada invocación dentro de repositorio (excepto ayuda), anunciado solo en la ruta interactiva de la raíz; `GITPR_SKIP_PROJECT_CONFIG_SEED` desactiva el seed. `gitpr seed` hace lo mismo de forma explícita e idempotente (sección 47).
* **🆕 Frontera Formalizada:** [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) separa el **auto-seed de runtime** (solo `gitpr.config.yml`, en el primer uso) del **provisioning explícito** (`gitpr seed`, que cubre config, smart excludes y linter). 🆕 `docs/config-tui.md` y `docs/pull-request-publication.md` ganaron la nota de migración y el opt-out para CI/monorepo.
* **MCP:** `publish_pull_request` usa el resolvedor compartido, devuelve envelope de error para `BaseBranchError` y expone `warnings` en el payload y en el catálogo.
* **Efecto Colateral Declarado:** ejecutar `gitpr` en un repositorio pasa a crear el archivo en la primera ejecución. Un archivo existente nunca se sobrescribe; si el `.gitignore` excluye la ruta, un aviso explica que el valor no se versionará.
* **Pruebas:** `tests/test_project_config.py` (**49**), `tests/test_project_config_cli.py` (7), `tests/test_pr_base_app.py` (12) + ampliaciones en `test_config_app.py` (94), `test_config_schema.py` (**55**), `test_config_store.py` (22), `test_mcp_server.py` (117) y `test_core.py` (49).
* **Artefactos:** [ADR-015](docs/plans/ADR-015-project-local-pr-base.md), 🆕 [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md), `docs/plans/develop_natan/20261030_project_pr_base_plansfacts.md`, `docs/survey/20261030_project_pr_base_surveyfacts.md`, y `docs/config-tui.*`/`docs/install-wizard.*`/`docs/pr-descricao-padrao.*`/`docs/pull-request-publication.*` en los 5 idiomas.

### **46. Configuración SCM por Repositorio y por Proveedor (`src/scm_settings.py`)**

* **Lo que era:** el proveedor (`GITPR_SCM_PROVIDER`), el token (`GITPR_SCM_TOKEN_ENCRYPTED`) y la base URL eran configuraciones **globales de la máquina** en `~/.gitpr/.env`. Una máquina no servía más de un proveedor a la vez, la credencial equivocada iba al host equivocado cuando el remote de un repositorio cambiaba, y cualquier host self-hosted no reconocido se mapeaba silenciosamente a GitHub.
* **Lo que es:** el proveedor y la credencial pasaron a ser **propiedad del repositorio** ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)). Un único resolvedor responde "¿qué proveedor, qué token, qué base URL valen aquí?" para todas las superficies.
* **Orden de Resolución (`resolve_provider_choice`):** override del proyecto (`scm.provider` en `.gitpr/conf/gitpr.config.yml`) → `detect_provider_from_remote(origin, default=None)` → `github`. Un valor de proveedor desconocido en el archivo del proyecto se **ignora con aviso**, nunca es fatal; `default=None` permite al wizard distinguir "reconocido como GitHub" de "no reconocido" y **preguntar** en vez de asumir.
* **Un Slot por Proveedor:** `GITHUB_TOKEN_ENCRYPTED`, `GITLAB_TOKEN_ENCRYPTED`, `BITBUCKET_TOKEN_ENCRYPTED`, `AZURE_DEVOPS_TOKEN_ENCRYPTED` (Fernet) y los `*_BASE_URL` correspondientes. Un token nunca responde por otro proveedor; el `GITPR_SCM_TOKEN` crudo sigue siendo el override global de CI/CD y se intenta primero en todos los proveedores.
* **Migración Única e Idempotente (`migrate_legacy_scm_config`, bajo lock):** mueve `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` + `GITPR_SCM_BASE_URL` al slot del proveedor que ellos nombran, **nunca sobrescribe** un slot ya relleno, elimina las líneas heredadas y sigue funcionando cuando el `.env` es de solo lectura (degrada al entorno del proceso). Un token heredado **sin** proveedor registrado se **deja en su lugar** deliberadamente, sin adivinar.
* **Wizard (`gitpr --init`):** persiste **solo en caso de éxito**, graba en el slot del proveedor elegido, ofrece la base URL guardada como default del prompt (no-GitHub) y solo escribe `scm.provider` en el archivo del proyecto cuando el proveedor elegido difiere del detectado — el archivo se commitea y se comparte con el equipo.
* **TUI de Configuración:** línea editable `PROJECT_SCM_PROVIDER` y línea read-only `PROJECT_EFFECTIVE_PROVIDER` (proveedor en uso + origen), tokens y base URLs de todos los proveedores siempre listados, prueba de token contra el host del propio slot y un grupo `gitlab`.
* **Superficies Convertidas:** `src/main.py`, `src/metrics.py`, `src/release_engine.py` y `src/mcp_server.py` resuelven por `get_scm_settings()` y propagan los avisos del resolvedor.
* **No Interactivo Nunca Pregunta:** `validate_or_request_scm_token(..., allow_prompt=False)` en hooks, MCP y CI devuelve "ejecute `gitpr --init`", no borra la credencial guardada y aún usa un token validado que no pudo persistirse.
* **Pruebas:** `tests/scm/test_scm_settings.py` (**24 escenarios**) + ampliaciones en `test_init_wizard.py` (20), `test_factory.py` (14), `test_azure_devops_provider.py` (51), `test_bitbucket_provider.py` (49), `test_gitlab_provider.py` (56) y `tests/test_tui_issue_token.py` (8).
* **Artefactos:** [ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md), `docs/plans/develop_natan/20261107_per_provider_scm_settings_plansfacts.md`, `docs/scm-multiforge.*` (5 idiomas) y el glosario `docs/plans/glossary-scm-multiforge.md`.

### **47. Subcomando `gitpr seed` — Bootstrap de la Configuración Local (`src/core.py`, `src/main.py`)**

* **Lo que era:** la configuración local del proyecto (base del PR, smart excludes, reglas de linter) nacía **como efecto colateral** del callback raíz y de la descarga de skills. No había forma de sembrarla de forma explícita, repetible e idempotente — y un CI que dependía de esos archivos solo lo descubría en la segunda ejecución.
* **Lo que es:** `gitpr seed` (y el flag global `-sd/--seed`) promueven el bootstrap a operación de primera clase. `seed_project_configuration()` crea:
  * `.gitpr/conf/gitpr.config.yml` (base del PR, vía `ensure_project_config`);
  * `.gitpr/conf/gitpr.smart-excludes.json` (exclusiones del proyecto);
  * `.gitpr/skill/.gitpr.linter.yml` (reglas del linter local).
* **Nunca Destruye sin Pedido:** un archivo existente **nunca** se sobrescribe; `--force/-f` sobrescribe a propósito, y `--all/-a` incluye también la descarga de todas las skills de IA. La función devuelve un resumen estructurado (`config`, `smart_excludes`, `linter`, `skills_downloaded`) y el comando guarda la métrica `seed`.
* **🆕 Frontera con el Auto-Seed:** [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) formaliza la segregación: el callback raíz/`--install` sigue sembrando **solo** `gitpr.config.yml` en el primer uso, mientras que el provisioning completo (smart excludes + linter + skills) siempre es explícito vía `gitpr seed`. El opt-out `GITPR_SKIP_PROJECT_CONFIG_SEED` cubre CI y monorepos donde el auto-seed no debe correr.
* **Wizard de Instalación en 5 Etapas:** **1)** Configuración Local del Proyecto (nuevo, confirmable), **2)** Skill Templates (con el setup opcional de bridges de linter externo), **3)** Git Hooks, **4)** Configuración del MCP, **5)** Clave de API. Saltar pasó a ser explícito.
* **Compatibilidad:** `generate_skill_template()` sigue auto-sembrando los smart-excludes, así que la ruta antigua no se rompe; `ensure_project_config()` y `download_skill_file()` ganaron el parámetro `force` manteniendo el default no-clobber.
* **Ayuda:** `HELP_MAP["seed"]` apunta a `docs/install-wizard.md` y describe `--force`/`--all`.
* **Pruebas:** `tests/test_seed_command.py` (**8 escenarios**) — creación, idempotencia, sobrescritura forzada, `--all`, CLI y flag; `tests/test_install_wizard.py` pasó a mockear el setup de linter externo.
* **Artefactos:** `docs/install-wizard.*` (5 idiomas), 🆕 [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md), `docs/plans/develop_natan/20261105_seed_local_conf_plansfacts.md`, survey y walkthrough, y el término **Project configuration seed** en el `CONTEXT.md`.

---

## **📊 Pruebas y Calidad**

| Archivo de Prueba | Escenarios | Enfoque |
|------------------|----------|------|
| `tests/test_ascii_chart.py` | 9 🆕 | Formateo compacto, estados vacíos, histograma, serie temporal, pestañas y cambio de pestaña |
| `tests/test_blame_engine_ranges.py` | 7 | Blame por rango de líneas en un archivo |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidad, commits, duración |
| `tests/test_baseline_cli.py` | 54 | Grupo `baseline`: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Constructor de changelog: secciones, títulos traducibles, contribuidores, enlaces y fechas |
| `tests/test_chat_backend.py` | 19 | Memoria de chat, persistencia, comandos slash |
| `tests/test_check_cli.py` | 5 | CLI de `check`: opciones, formatos, exit codes |
| `tests/test_cli_help.py` | 9 | `-h` antes del subcomando: ayuda renderizada, TUI nunca abierta |
| `tests/test_commit_app.py` | 17 | `CommitApp`: mount, Stage Selector, auto-stage, skip, cancelar, confirmar, éxito/fallo, auto-exit, flujo headless y routing con/sin TTY |
| `tests/test_commit_classifier.py` | 30 | Clasificación Conventional Commits + `pr_number_from_merge()` en los 4 forges |
| `tests/test_config_app.py` | 94 | TUI de configuración: montaje, categorías, dirty tracking, F2 bloqueado, Ctrl+R, búsqueda, secretos, stores `project`, panel Smart Excludes |
| `tests/test_config_cli.py` | 9 | Registro del subcomando `config`, `-h`, lazy import, stdout limpio |
| `tests/test_config_issue_autoclose.py` | 10 | Getters de configuración y campos del schema de cierre de issue |
| `tests/test_config_schema.py` | 55 🆕 | Cobertura de `DEFAULT_CONFIG` derivado, sin duplicados, categorías/kinds (incluido `KIND_FLOAT`), `seeded`, sección de skills, banner y palette |
| `tests/test_config_store.py` | 22 | Round-trip sobre el `.env` temporal, comentarios y orden preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuración de revisores sugeridos (claves y defaults) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, plantillas, `validate_ai_key()` con un SDK mockeado (401 vs. red vs. ollama) |
| `tests/test_core.py` | 49 | Flujos principales, git diff, PR generation, timing, staging, coautoría, idioma de los hooks, base del PR |
| `tests/test_diff_failure_cli.py` | 3 | Exit code 1 en fallo de git, 0 en diff vacío, fail-open en `--hook` |
| `tests/test_diff_parser.py` | 26 | Parser de diff por líneas/hunks, `summarize_patch()` y `split_patch_sections()` |
| `tests/test_doc_links.py` | 11 | Contrato `?lang=` del sitio, `doc_url()` dinámico, links de las TUIs y paridad de 5 idiomas |
| `tests/test_explain_command.py` | 4 | CLI de `explain`: éxito, clave ausente, diff vacío, ayuda contextual |
| `tests/test_external_linters.py` | 33 | Puente Checkstyle: parser XML, subprocess, intersección con el diff, informe |
| `tests/test_full_diff_encoding.py` | 9 🆕 | Repositorio real con archivo Latin-1 + guarda de hunk binario (fast path, colapso, densidad, **colapso defensivo en `generate_pr_content`**) |
| `tests/test_i18n.py` | 22 ✅ | Paridad entre idiomas, claves faltantes/huérfanas, identidad — **todos en verde** |
| `tests/test_install_wizard.py` | 3 | Asistente interactivo de instalación (5 etapas) |
| `tests/test_issue_engine.py` | 4 | Borrador de issue estructurado |
| `tests/test_linter_metrics.py` | 4 | Métricas del linter: errores, warnings, duración |
| `tests/test_linter_presets.py` | 5 | Presets del linter: resolución y re-descarga forzada |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` en la CLI y en la ayuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompts MCP y fallback de idioma |
| `tests/test_mcp_server.py` | 117 | Herramientas MCP (18), recursos (19), anotaciones, patching, CLI directa, offload, contrato de la extensión, base del PR |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real vía subprocess + JSON-RPC stdio |
| `tests/test_mentor_cli.py` | 4 ✅ | CLI del `mentor` y opt-in del `--mentor` — patches en `src.main` |
| `tests/test_metrics.py` | 175 🆕 | **Ledger**: escritura síncrona, exportación, bundle/merge, migración, renderizadores, costo (incluidos los alias de Gemini), moneda, calidad, módulos, proveedores, ciclo, Token Trend y Latency Histogram |
| `tests/test_net_timeouts.py` | 12 | Timeouts de red/IA — alineados con el predeterminado real de 180s |
| `tests/test_plugins.py` | 17 | Descubrimiento de plugins, fusión de reglas del linter, prompts MCP |
| `tests/test_policy_cli.py` | 26 | Grupo `policy`: list/show/validate/use/off/install/init, confirmación y lockfile |
| `tests/test_pr_base_app.py` | 12 | Modal de cambio de la base del PR: montaje headless, elección y cancelación |
| `tests/test_pr_publish_app.py` | 59 | TUI del PR Publisher: pantallas, flujos, revisores sugeridos, `NoticeScreen`, modal de merge, `body_published` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de error del linter: abortar, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save y payload JSON |
| `tests/test_project_config.py` | 49 | `project_config`: path, YAML, seed, resolvedor de base, sección `scm`, validación contra el remote |
| `tests/test_project_config_cli.py` | 7 | Cableado de la base del proyecto en la CLI |
| `tests/test_release_cli.py` | 4 | CLI de release: opciones, ayuda con epílogo documentado |
| `tests/test_release_engine.py` | 61 | **Corte anclado en el changelog, `_drop_released()`, links, mapa de PR por merge commit**, front matter de idioma |
| `tests/test_reviewer_resolution.py` | 18 | Escalera de resolución, rechazo de nombre parcial, dedup, tolerancia a fallos |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugerencia de revisores (ranking, exclusión, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | CLI de `risk`: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 77 | Matriz del ruleset integrado: regex, placeholders, nivel, comodín, fusión, traducciones |
| `tests/test_seed_command.py` | 8 | `gitpr seed`/`--seed`: creación, idempotencia, force, `--all`, métricas |
| `tests/test_skill_command.py` | 10 | Descarga y validación de plantillas de skill |
| `tests/test_skill_context.py` | 20 | `get_skill_context()` con `quiet=True`, fallbacks, guardia del artefacto de PR |
| `tests/test_skill_frontmatter.py` | 11 | `split_front_matter()`: BOM, CRLF, YAML malformado, allowlist de idiomas |
| `tests/test_skill_templates.py` | 99 | Barrido parametrizado de **todas las plantillas publicadas** contra referencias embebidas |
| `tests/test_smart_excludes.py` | 17 | Filtro pathspec inteligente, re-descarga forzada, familias binarias (incluidas `*.arrow`/`*.gguf`/`*.tflite`/`*.keras`) |
| `tests/test_suggest_reviewers.py` | 19 | Suggested reviewers en el flujo de PR + resumen de blame |
| `tests/test_tests_command.py` | 4 | Grupo `tests` y CLI de `generate`, `--finding` y epilog con enlace |
| `tests/test_thinking_words.py` | 5 | Carga, parsing con separador `;` y recarga forzada |
| `tests/test_tui_issue_token.py` | 8 | `allow_prompt=False`: nunca pregunta, nunca borra, apunta `gitpr --init` |
| `tests/test_updater.py` | 55 | **Puerta de PyPI**: TTL deslizante, caché legado/corrupto, escritura atómica, `force`, `bounded_urlopen`, comparador PEP 440, pre-release, switch de skip estricto y puerta del subcomando |
| `tests/test_usage_log.py` | 27 | Log de uso: nombre derivado de la fecha, escritura síncrona, silencio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semántico: major/minor/patch, targets y validación |
| `tests/test_web_links.py` | 26 | Normalización de remotos (https/ssh/scp/credentials/port) y patrones de URL por forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 | Activación del pack, lockfile, rechazos |
| `tests/application/use_cases/test_baseline_gate.py` | 40 | Puerta: carga, clasificación, anotación, capas y aborto |
| `tests/application/use_cases/test_baseline_summary.py` | 12 | Vista de solo lectura del baseline para MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 | Correlación de diff/hallazgos/pruebas/historial, degradación |
| `tests/application/use_cases/test_create_baseline.py` | 30 | Creación/actualización con procedencia y preservación de decisiones |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 | Lotes, severidad, cache hit, clave ausente |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 3 | Caso de uso de explain: dict del proveedor y clave faltante |
| `tests/application/use_cases/test_generate_test_file.py` | 5 | Caso de uso de generación de pruebas: dict del proveedor y cadena cruda |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 | Instalación del pack, orígenes y conflictos |
| `tests/application/use_cases/test_run_check.py` | 8 | Orquestación del check: orden, degradación, exit codes |
| `tests/application/use_cases/test_suppress_finding.py` | 29 | Supresión/eliminación en los cuatro ámbitos |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 | Validación: schema cerrado, checksum, compatibilidad |
| `tests/badge/test_badge_append.py` | 9 | Anexado idempotente del badge al cuerpo del PR |
| `tests/badge/test_badge_builder.py` | 19 | Composición Markdown de shields.io, escape, regla de color, estilo |
| `tests/badge/test_badge_cli.py` | 15 | Comando `gitpr badge`: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Conteo de alertas; `None` sin reglas configuradas |
| `tests/badge/test_badge_offline.py` | 6 | Guard offline (red prohibida, salvo loopback) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` en cada camino de publicación |
| `tests/badge/test_badge_paths.py` | 10 ✅ | Punto único de inyección en los dos publicadores |
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
| `tests/domain/check/test_check_config.py` | 3 | Precedencia CLI > env > defaults |
| `tests/domain/check/test_check_output.py` | 3 | Despacho por formato y escritura atómica |
| `tests/domain/check/test_check_types.py` | 8 | Severidad en tres niveles, alias de la especificación, resumen |
| `tests/domain/finding/test_finding_types.py` | 3 | Tipo canónico de hallazgo: `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Formateo uniforme y deduplicación `[Gitleaks + Regex]` |
| `tests/domain/linter/test_structured_findings.py` | 44 | `findings_out`: hallazgos estructurados y el contador de líneas del lado nuevo |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 | Builder puro: honestidad epistémica, URLs eliminadas, orden preservado |
| `tests/domain/policy/test_policy_checksum.py` | 15 | SHA-256 del pack y detección de edición manual |
| `tests/domain/policy/test_policy_compatibility.py` | 22 | Compatibilidad de versión y credenciales |
| `tests/domain/policy/test_policy_manifest.py` | 42 | Schema cerrado, bloque `baseline:`, errores de manifest |
| `tests/domain/policy/test_policy_resolver.py` | 47 | Precedencia, composición en capas, pack activo × base |
| `tests/domain/pr/test_explain_section_builder.py` | 6 | Renderizado Markdown, parsing tolerante, `[FILL]`, payload dict y `None` |
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
| `tests/infrastructure/ci/test_junit_builder.py` | 2 | JUnit XML: failure/system-out |
| `tests/infrastructure/ci/test_masking.py` | 6 | Enmascarado de credenciales antes de serializar |
| `tests/infrastructure/ci/test_sarif_builder.py` | 2 | SARIF 2.1.0 y `tool.driver.rules` |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 | `git log` seguro: commits, bugs y reverts en 90 días |
| `tests/infrastructure/git/test_test_matcher.py` | 5 | Casamiento prueba ↔ producción por convención |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Puente Bandit: disponibilidad, parsing, mapeo |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC: subprocess endurecido, timeout, binario ausente |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Puente Gitleaks: enmascarado de secretos, ámbito por archivo |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Puente Semgrep: parsing JSON, severidad, skip de no-Python |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 | Efecto real del baseline sobre review, lint y riesgo en un repositorio real |
| `tests/integration/test_baseline_exit_code_contract.py` | 8 | Exit code 1 para baseline inaplicable y 2 en el `check` |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Efecto real del pack sobre review, lint y riesgo (con un guard de red autouse) |
| `tests/issue_autoclose/test_close_issue_wiring.py` | 26 | Cableado en el flujo de PR: flags, prompt, publicación real, limpieza del registro |
| `tests/issue_autoclose/test_insert_close_line.py` | 19 | Inserción idempotente y reconocimiento de keywords de cierre |
| `tests/issue_autoclose/test_issue_registry.py` | 26 | Registro local: round-trip, atomicidad, corrupción, `.gitignore`, repo match, migración de `github.yml` |
| `tests/issue_autoclose/test_register_created_issue.py` | 13 | Registro post-creación: multi-forge, proveedor persistido, fallos engullidos |
| `tests/issue_autoclose/test_resolve_close_issue.py` | 42 | Árbol de decisión: gates, `is_open`, PR, registros muertos, `not-supported`, confirmación |
| `tests/review/test_diff_normalizer.py` | 20 | Saltos de línea, validación de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: procedencia, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orquestación: puertas, PR, diff, linter, comentario opcional |
| `tests/review/test_review_pr_cli.py` | 25 | CLI de `review-pr`: opciones, rechazos antes de la IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 51 | Proveedor Azure DevOps: org/proyecto, PRs, `ScmNotSupportedError`, issue read-back |
| `tests/scm/test_bitbucket_provider.py` | 49 | Proveedor Bitbucket: Basic auth, workspace, PRs, `close #N` |
| `tests/scm/test_contract.py` | 44 | Contrato de `ScmProvider`: firmas, dataclasses, errores, defaults de issue |
| `tests/scm/test_factory.py` | 14 | `resolve_scm_provider()` + `detect_provider_from_remote(default=None)` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim deprecado `github_api` → delega en el proveedor |
| `tests/scm/test_github_provider.py` | 86 | Proveedor GitHub: REST, headers, PRs, issues, releases, read-back de revisores |
| `tests/scm/test_gitlab_provider.py` | 56 | Proveedor GitLab: API v4, namespace, cabeceras sintetizadas, `overflow`, `Closes #N` |
| `tests/scm/test_init_wizard.py` | 20 | Wizard `--init`: detección del proveedor, slot por proveedor, validación, persistencia |
| `tests/scm/test_release_publish.py` | 8 | Publicación de release por forge (GitHub/GitLab) |
| `tests/scm/test_scm_settings.py` | 24 | Resolvedor de proveedor/credencial: precedencia, migración heredada, avisos, read-only |
| `tests/split/test_apply_split_plan.py` | 11 | El único módulo que muta: staging selectivo, un commit por grupo |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unidades → grupos → pre-validación de conflictos → mensajes |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, presupuesto/recorte, llamada de IA, validación de respuesta |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()`: texto ↔ modelo, puro |
| `tests/split/test_selective_stager.py` | 11 | Staging de un subconjunto, verificación contra HEAD, índice limpio |
| `tests/split/test_split_cli.py` | 31 | CLI de split: opciones, dry run predeterminado, `--apply` |
| `tests/unit/test_version_consistency.py` | 5 ⚠️ | SSOT de la versión: `pyproject.toml`, `CLAUDE.md` y `GEMINI.md` contra `src.updater.__version__` — **2 fallos** porque ambos docs todavía dicen 1.6.0 |
| `tests/unit/test_check_ci_docs_parity.py` | 3 🆕 | Paridad de 5 idiomas y 7 secciones del `docs/check-ci.*` + ADR-0011 + referencias en `action.yml` |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura i18n (scaffold; nunca se ejecuta) |

**Total:** **3436 escenarios recolectados en 160 módulos de prueba** (64 en la raíz + 12 en `tests/application/use_cases/` + 10 en `tests/fix/` + 10 en `tests/scm/` + 10 en `tests/infrastructure/` + 21 en `tests/domain/` + 7 en `tests/badge/` + 6 en `tests/demo/` + 6 en `tests/split/` + 5 en `tests/issue_autoclose/` + 4 en `tests/review/` + 3 en `tests/integration/` + 2 en `tests/unit/`; **+14 escenarios, +1 archivo** desde el informe anterior).

**Notas de calidad de esta versión:**
- **✅ El bloqueo que trababa la suite sigue cerrado:** `-c` sin `--hook` no abre la `CommitApp` cuando no hay TTY — el routing consulta `_may_ask_the_user()`.
- **✅ La prueba headless de commit sigue estable:** `Button.press()` + `pilot.pause()`, predicado de montaje y timeout de 10 s.
- **🆕 Gráficos testables sin Textual:** `tests/test_ascii_chart.py` ejercita el renderizador puro y la composición de pestañas por separado — la lógica de gráficos no depende de un terminal real.
- **🆕 El colapso de binarios ganó cobertura de llamador externo:** `test_full_diff_encoding.py` ahora prueba que `generate_pr_content` sanea un diff crudo con `U+FFFD`, más allá del camino que venía de git.
- **🆕 La nota de migración del `action.yml` se volvió prueba:** la paridad de documentación ahora también fija ADR-0011 y las referencias en el archivo de acción.
- **⚠️ 2 fallos de consistencia de versión:** `CLAUDE.md` y `GEMINI.md` todavía declaran 1.6.0 mientras `src/updater.py` ya está en 1.7.0; `tests/unit/test_version_consistency.py` falla esos dos escenarios. La corrección es actualizar ambos documentos.
- **`tests/conftest.py` sigue hermético:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false`, `LANG_VERSION` en la versión del código y `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
- **Guarda de red en varios sitios:** `tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py` y `tests/split/conftest.py`.
- **Fixtures de git real:** `tests/fix/git_fixture.py`, `tests/split/git_fixture.py` y `tests/test_full_diff_encoding.py`.
- **La suite de la extensión es aparte:** 7 escenarios Mocha en `extensions/vscode/src/test/mcp_client.test.ts`, de los cuales 1 se integra de verdad con el servidor MCP Python por stdio; no entra en el conteo de pytest.
- **La CI corre en Python 3.10 y 3.13** con `fail-fast: false`, y el release tiene su propio workflow con Trusted Publishing.

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n:** **1787 claves** en **todos los seis** diccionarios (`es.json`, `es_es.json`, `fr.json`, `fr_fr.json`, `pt_br.json`, `pt_pt.json`). El código usa las mismas claves: **0 sin traducción** y **0 huérfanas**. Eran 1772 × 6 en la ventana anterior.
* **🆕 El `__lang_version__` subió a v0.0.42:** las nuevas strings de precio por token, la moneda, la validación `KIND_FLOAT`, los gráficos/pestañas ASCII y la instalación del VSIX entraron en los seis diccionarios; el refresh OTA entrega la traducción nueva sin reinstalar.
* **Fuentes de traducción en lockstep:** una clave nueva tiene que existir en el código (fuente), en `langs/pt_br.json` (**lista maestra**), en los dicts FR/ES de `scripts/sync_all_langs.py` y en los valores curados de `scripts/fix_mangled_i18n_keys.py`; el extractor de claves es **AST** en ambos lados.
* **Temas nuevos:** **ningún** tema canónico nuevo en esta ventana — la documentación trabajó sobre los temas existentes y sobre los ADRs.
* **🆕 Temas actualizados en esta ventana:**
  - `docs/editor-extension.*` (**5 archivos**) — release del VSIX desacoplado, puerta de marketplace granular e instalación en un clic.
  - `docs/auto-update.*` (**5 archivos**) — semántica estricta de `GITPR_SKIP_UPDATE_CHECK` y nota de migración.
  - `docs/commit-message-ia.*` (**5 archivos**) — nota de migración del commit interactivo sin TTY.
  - `docs/tutorial/install-from-source.*` (**5 archivos**) — misma nota de migración para el switch de actualización.
  - `docs/marketplace-publishing-guide.md` (**1 archivo, EN**) — enlaces directos a los releases `ext-v*`; aún parcial.
  - `docs/config-tui.md` (**solo EN**) — seed de configuración del proyecto y opt-out para CI/monorepo.
  - `docs/pull-request-publication.md` (**solo EN**) — misma nota de seed/opt-out.
  - `README.md` — enlace a la instalación del VSIX.
* **`CONTEXT.md` (actualizado):** el glosario del dominio pasó de **35 a 44 términos** (+9). Entre otros, entraron **Granular marketplace gate** (en lugar de *Fail-fast secret check*), **Seed segregation**, **Draft extension release**, **ASCII chart**, **Chart data**, **Tabbed metrics view** y los términos de precio/moneda.
* **Documentación en 5 idiomas:** **52 temas canónicos** en `docs/` (conteo sin cambios) — **50 con cobertura completa en los 5 idiomas** y **2 temas parciales** (`github-issue-prompt-com-gh`, `marketplace-publishing-guide`).
* **Nuevos artefactos de especificación:** [ADR-0011](docs/adr/0011-action-unified-check-migration.md), [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md), [ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) y [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) en `docs/adr/` (ahora **8 archivos**; la numeración de `docs/adr/` no es continua porque ADR-012/013/015/016 viven en `docs/plans/`).
* **Skills locales de Claude Code:** `.claude/skills/` con **29 skills** (conteo sin cambios).
* **Índice de memoria:** `.claude/memory/MEMORY.md` con **41 patrones** (🆕 +1).
* **Informes de tareas:** `docs/claude-code/reports/develop_natan/` (**119**; **+1**) + `docs/claude-code/reports/feat/` (**12**, sin cambios) + `docs/claude-code/reports/main/` (**1**) y `docs/gemini/reports/` (**54**; **+12**), más `docs/gemini/walkthrough/` (**31**; **+12**).
* **Informes de estado:** `docs/reports/` — este es el **24.º informe** (v0.0.24); el v0.0.23 nunca se sobrescribe.
* **Planes de desarrollo:** **174** archivos en `docs/plans/` (+13) + **36 archivos en `docs/gemini/plans/`** (+12) + **46 archivos en `docs/survey/`** (+12).

---

## **🔄 Pipeline de Distribución**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Actualización obligatoria con ventana deslizante:** la ejecución consulta PyPI al iniciar y **bloquea con código de salida 1** si hay una versión más nueva, imprimiendo `pip install --upgrade gitpr-cli`. La respuesta se cachea durante **1 hora** y `--update` **siempre** consulta la red. La comparación es PEP 440: un pre-release remoto no bloquea una instalación estable, y una versión ilegible cuenta como desconocida. La puerta cubre los subcomandos cuando hay un TTY; CI, pipes, hooks, MCP y `-h` siguen exentos.
3. **GitHub Releases:** producción automatizada por `release.yml` en tags `v*` — build, validación, Trusted Publishing y creación del release con artefactos.
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala vía pip) + `tests.yml` (matriz Python 3.10 y 3.13) + `extension.yml` (VSIX; 🆕 job `github-release` en `ext-v*` y job `publish-marketplaces` con puerta granular por PAT) + `release.yml` (PyPI + GitHub Release en `v*`). El `action.yml` llama a un único paso `gitpr check` — **ruptura declarada** para quienes usaban `--linter` + `--fullreview`, cubierta por la prueba de paridad de documentación y por ADR-0011.
5. **Servidor MCP:** entry point `gitpr-mcp` vía `pyproject.toml`
6. **Extensión de Editor:** paquete VSIX independiente (`natafiuza.gitpr`), con canales VS Code Marketplace y Open VSX; requiere Node.js 20+ solo para build, no para uso. El `.vsix` se adjunta al GitHub Release de `ext-v*` incluso cuando la publicación en marketplace está desactivada.
7. **Templates e idiomas OTA:** `templates/` y `langs/*.json` servidos desde GitHub (main) — `__lang_version__` **v0.0.42** 🆕 renueva las copias locales de `~/.gitpr/langs/`; `__scripts_version__` **v0.0.4** renueva los hooks.
8. **Versión derivada del código:** `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — el `__version__` es la **única** fuente, garantizado por [ADR-014](docs/adr/0014-canonical-package-version-ssot.md) y por `tests/unit/test_version_consistency.py`.
9. **`package-data` importa:** los cuatro packs embebidos (`src/policy_packs/*`) viajan en la wheel.
10. **✅ Estado del release — 1.6.0 y 1.7.0 publicadas:** el PyPI sirve la **1.7.0**. La 1.6.0 fue tagueada en `f5088c4`; la 1.7.0 tiene su sección propia en el `CHANGELOG.md` (`6d1f1dc`) y bump en `2354a30`. `develop_natan` fue mergeada en `main` por el PR #310.
11. **⚠️ Tag de la 1.7.0 desalineado:** el tag `v1.7.0` en origin sigue apuntando a `b2688c4`, un commit **anterior** al bump, donde el `__version__` era 1.6.0. La guarda del `release.yml` compara tag y `__version__`; alinear el tag al commit `2354a30` (o recrearlo) es el ítem pendiente para que el GitHub Release refleje el código publicado.

---

## **📈 Evolución desde el Informe Anterior (v0.0.23)**

| Área | v0.0.23 (anterior) | v0.0.24 (actual) |
|------|-------------------|-----------------|
| **Versión GitPR** | 1.6.0 — en el changelog y en `develop_natan`, **sin tag**, PyPI en **1.5.0** | **1.7.0** — publicada en PyPI; el tag `v1.7.0` existe pero apunta al commit anterior al bump |
| **Versión Idioma** | v0.0.41 | **v0.0.42** — diccionarios sincronizados, +15 claves |
| **Versión de los scripts de hook** | v0.0.4 | v0.0.4 (sin cambios) |
| **Proveedores de IA** | Gemini + DeepSeek + Ollama | Sin cambios |
| **Idiomas** | 5 idiomas, 6 diccionarios | Sin cambios |
| **Subcomandos** | 16 | 16 — sin cambios; 4 grupos (baseline 6, metrics 8, policy 7, tests 1) |
| **Flags CLI** | 34 opciones en la raíz | 34 — sin cambios |
| **Configuración SCM** | Por repositorio y por proveedor | Sin cambios |
| **Cierre de Issue** | Multi-forge | Sin cambios |
| **Seed de Configuración** | `gitpr seed`/`--seed` + wizard de 5 etapas | Comportamiento sin cambios; 🆕 frontera formalizada en ADR-0019 |
| **Capas** | 9 paquetes de dominio, 15 casos de uso | Sin cambios — `ascii_chart.py` es presentación pura |
| **Herramientas MCP** | 18 tools / 19 recursos / 7 prompts | Sin cambios |
| **`DEFAULT_CONFIG`** | 55 claves, derivadas del schema (`seeded`) | **56 claves** — `GITPR_METRICS_CURRENCY` |
| **Schema de Config** | 100 `ConfigField` / 18 categorías / 55 `seeded` | **109 / 18 / 56** — `KIND_FLOAT` + 9 campos de moneda/precio |
| **i18n (claves por archivo)** | 1772 × 6 | **1787 × 6** — 0 faltantes, 0 huérfanas ✅ |
| **Puerta de Actualización** | TTL corrido de 1 h, `-u` siempre online, PEP 440 | Código sin cambios; 🆕 nota de migración del switch estricto |
| **Linter** | Regex + secretos + SAST + `findings_out` + baseline-aware | Sin cambios |
| **Diff / Smart Excludes** | + guarda de hunk binario y 100+ patrones | + **colapso defensivo en `generate_pr_content`** y 4 familias binarias nuevas |
| **Git Hooks** | post-checkout/post-merge/pre-push en el ledger | Sin cambios — `prepare-commit-msg` sigue sin interactividad |
| **Capa SCM** | 4 proveedores, paginación, `web_links.py` | Sin cambios |
| **Release engine** | sección `[1.6.0]`; 1.5.0 publicada; 1.6.0 sin tag | sección `[1.7.0]`; **1.6.0 y 1.7.0 publicadas**; el tag de la 1.7.0 desalineado |
| **Métricas** | Token Trend y Latency Histogram en CLI/TUI/MCP | + **gráficos ASCII y pestañas** en el dashboard; 🆕 precios por modelo y moneda |
| **Commit TUI** | guarda de TTY y prueba headless estable | Sin cambios; 🆕 nota de migración del flujo sin TTY |
| **Extensión** | workflow endurecido con `dry_run` y fail-fast | + **release del VSIX desacoplado** y **puerta granular por marketplace** |
| **Documentación** | 52 temas (50 completos + 2 parciales) | **52 temas (50 completos + 2 parciales)** — 4 ADRs nuevos |
| **Agenda del dominio** | `CONTEXT.md` con 35 términos | **44 términos** — Granular marketplace gate, Seed segregation, ASCII chart etc. |
| **CI** | `tests.yml` + `action.yml` + `extension.yml` + `release.yml` | Archivos base sin cambios; `extension.yml` reestructurado |
| **Suite de Pruebas** | 3422 escenarios (159 archivos) | **3436 escenarios (160 archivos) — +14 escenarios, +1 archivo** |
| **Estabilidad de la Suite** | Sin bloqueo conocido | **2 fallos de consistencia de versión** en `test_version_consistency.py` (docs en 1.6.0) |
| **Dependencias** | Sin nuevas dependencias Python | Ninguna nueva — `ascii_chart.py` usa solo la stdlib |
| **Commits desde el informe** | 16 commits, 0 merges | **13 commits, 13 merges** (#295–#310) |
| **PRs referenciados** | 14 PRs (#261 … #291) | **13 PRs mergeados** (#295, #296, #297, #298, #299, #301, #302, #304, #305, #307, #308, #309, #310); issues cerradas #294, #300, #303 y #306 |
| **Índice de memoria** | 40 patrones | **41 patrones** |
| **Informes de tareas** | 118 claude-code, 42 gemini, 19 walkthrough | **119 claude-code (+12 feat, +1 main), 54 gemini, 31 walkthrough** |
| **Planes de desarrollo** | 161 planes, 24 gemini, 34 surveys | **174 planes, 36 gemini, 46 surveys** |

---

## **🚧 Próximos Pasos**

* **🆕 Actualizar `CLAUDE.md` y `GEMINI.md` a 1.7.0:** el bump `2354a30` cambió solo `src/updater.py`, y ambos documentos todavía dicen 1.6.0. `tests/unit/test_version_consistency.py` tiene **2 fallos** por eso — es la corrección más urgente de esta ventana.
* **🆕 Alinear el tag `v1.7.0`:** el tag en origin apunta a `b2688c4` (anterior al bump, donde el `__version__` era 1.6.0). La guarda del `release.yml` compara tag y versión; mover/recrear el tag sobre el commit `2354a30` hace que el GitHub Release coincida con el PyPI 1.7.0.
* **🆕 Traducir el `marketplace-publishing-guide`:** el tema sigue solo en inglés; PT-BR, PT-PT, ES y FR siguen pendientes.
* **🆕 Traducir las ediciones solo EN de `config-tui` y `pull-request-publication`:** las notas de migración de seed/CI y de opt-out entraron solo en la versión en inglés.
* **🆕 Publicar la extensión en los marketplaces:** la puerta granular ya evalúa `VSCE_PAT` y `OVSX_PAT` de forma independiente y el VSIX ya se adjunta al GitHub Release, pero la publicación depende de las credenciales.
* **🆕 Documentar precios/moneda en `metricas-telemetria`:** los campos `GITPR_METRICS_PRICE_*` y `GITPR_METRICS_CURRENCY` están en la TUI y en el código, pero el tema de telemetría todavía no los explica.
* **Nota de migración del SCM por repositorio:** las claves `GITPR_SCM_PROVIDER`, `GITPR_SCM_TOKEN_ENCRYPTED` y `GITPR_SCM_BASE_URL` fueron retiradas del `.env` por la migración automática. Quien automatizó la escritura de esas claves necesita saber que ahora hay un slot por proveedor. **Ítem heredado, aún abierto.**
* **Traducir `docs/metricas-telemetria.*`:** PT-BR, PT-PT, ES y FR siguen atrás del original en inglés. **Ítem heredado, aún abierto.**
* **Acompañar el host self-hosted no reconocido:** la resolución por remote solo reconoce dominios que contienen el nombre del proveedor; un GitLab/GitHub en dominio personalizado cae a GitHub hasta que alguien corra `gitpr --init` y grabe el override. **Ítem heredado, aún abierto.**
* **Proveedor Anthropic Claude:** Soporte directo a la API de Claude (`claude-sonnet-5`). **Ítem heredado, aún abierto.**
* **Más proveedores:** OpenAI directo, proveedores locales adicionales. **Ítem heredado, aún abierto.**
* **Nota de migración de `GITPR_SKIP_UPDATE_CHECK`:** ✅ **concluido** — `docs/auto-update.*` y el tutorial de instalación describen el parser estricto.
* **Nota de migración del `-c` interactivo:** ✅ **concluido** — `docs/commit-message-ia.*` en los 5 idiomas explica el flujo sin TTY y el `--hook`.
* **Tag y publicación de 1.6.0/1.7.0:** ✅ **concluido** — el PyPI sirve la 1.7.0 (alinear el tag de la 1.7.0 sigue como ítem nuevo arriba).
* **Merge de `develop_natan` en `main`:** ✅ **concluido** — PR #310.
* **Deudas del config TUI:** ✅ **concluido** — auditoría formal y cierre registrados en los informes del 2026-11-09.
* **Ítem Smart Excludes en la TUI:** ✅ **concluido** — auditoría formal y cierre registrados.
* **Nota de migración del `action.yml`:** ✅ **concluido** — `action.yml`, ADR-0011 y la prueba de paridad con 3 escenarios.

### ✅ Concluidos en esta ventana (2026-11-08 → 2026-11-10)

* ~~**El dashboard solo tenía secciones textuales**~~ 🆕 — PR #307 (`07822f7`): `src/ascii_chart.py`, pestañas **Events**/**Charts & Trends**, bindings `1`/`2` y `tests/test_ascii_chart.py` (9 escenarios) ([ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
* ~~**Los binarios podían entrar por un llamador externo**~~ 🆕 — PR #304 (`c1cddbb`): `collapse_binary_hunks()` al inicio de `generate_pr_content` y `*.arrow`/`*.gguf`/`*.tflite`/`*.keras` en los smart excludes.
* ~~**El release de la extensión dependía del release del paquete**~~ 🆕 — PR #302 (`3bcc6c9`): job `github-release` en `ext-v*`, puerta de marketplace granular e instalación del VSIX en los 5 idiomas.
* ~~**El costo y la moneda eran fijos**~~ 🆕 — PR #295 (`c5485dd`): `KIND_FLOAT`, `GITPR_METRICS_CURRENCY`, 8 campos de precio y alias de Gemini con precio.
* ~~**La ruptura del `action.yml` no estaba formalizada**~~ 🆕 — PR #301 (`7bd6678`): nota de migración en el `action.yml`, [ADR-0011](docs/adr/0011-action-unified-check-migration.md) y prueba de paridad.
* ~~**El fallback ES/FR del sitio no tenía contrato**~~ 🆕 — `80378b0`: [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md).
* ~~**La frontera entre el auto-seed y `gitpr seed` no estaba escrita**~~ 🆕 — `98b23cc`: [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md).
* ~~**La nota de migración del commit interactivo no existía**~~ 🆕 — `1995213`: `docs/commit-message-ia.*` en los 5 idiomas.
* ~~**La semántica estricta de `GITPR_SKIP_UPDATE_CHECK` no estaba documentada**~~ 🆕 — `1995213`: `docs/auto-update.*` y tutorial.
* ~~**El seed de configuración del proyecto no tenía nota de migración**~~ 🆕 — `7cf4ac8`: `docs/config-tui.md` y `docs/pull-request-publication.md`.
* ~~**Las deudas del config TUI y de Smart Excludes estaban abiertas**~~ 🆕 — `8886976` y `a7c316a`: auditoría formal y cierre.
* ~~**El changelog no tenía la 1.7.0**~~ 🆕 — `6d1f1dc`: sección `## [1.7.0] - 2026-11-10`.
* ~~**La versión del paquete y de los diccionarios estaba en 1.6.0/v0.0.41**~~ 🆕 — `2354a30`: bump a **1.7.0** y **v0.0.42**.
* ~~**La 1.6.0 estaba tagueada pero no publicada**~~ — el PyPI pasó a servir la 1.6.0 y luego la 1.7.0.
* ~~**La ventana entera estaba en `develop_natan`, sin merge**~~ — el PR #310 llevó la rama al `main`.

---

**Informe generado el:** 2026-11-10  
**Rama:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
