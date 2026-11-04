# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.22 (2026-11-04)**

## **📌 Visión General**

**GitPR** es una herramienta CLI (Command Line Interface) avanzada para la automatización de procesos Git mediante Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). Su objetivo principal es actuar como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita la deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.22):**
- **El ciclo issue → PR → merge ahora se cierra solo:** todo issue creado con `gitpr -is` ahora se registra en `.gitpr/conf/github.yml` (un registro local que se auto-ignora en el `.gitignore`), y el siguiente `gitpr` verifica que siga abierto antes de ofrecer la línea `close #N` en el cuerpo del Pull Request. `--close-issue` acepta sin preguntar, `--no-close-issue` omite la comprobación, y `GITPR_ISSUE_AUTOCLOSE=false` desactiva la funcionalidad. El paquete nuevo `src/issue_autoclose/` tiene **5 archivos** y una serie de decisiones registradas en las ADRs 001–007 en `docs/specs/issue-autoclose/`.
- **La base del PR dejó de vivir solo en la máquina de quien ejecuta:** `.gitpr/conf/gitpr.config.yml` se versiona con el proyecto y carga la rama de destino; `src/project_config.py` resuelve `--base` → archivo del proyecto → `PR_DEFAULT_BASE` → `origin/HEAD` → `main`, y el destino debe existir en `origin` y diferir de la rama actual. Un destino igual a la rama actual se rechaza y, en la ejecución interactiva, se repara mediante un modal dedicado (`src/ui/pr_base_app.py`) que escribe el nuevo valor en el archivo del proyecto ([ADR-015](docs/plans/ADR-015-project-local-pr-base.md)).
- **La deuda de i18n se pagó:** los seis diccionarios tienen ahora **1698 claves** cada uno, `__lang_version__` subió a **v0.0.38**, y `tests/test_i18n.py` cierra **22 en verde** — sin claves faltantes, sin huérfanas, sin identidad por encima de la allowlist. El extractor de `sync_i18n.py` cambió el regex por **AST**, lo que eliminó los 21 falsos negativos de concatenación implícita.
- **El ruleset de secretos cubrió las tres brechas que estaban abiertas:** claves STS `ASIA…`, PATs `github_pat_…`, cookies `xoxc-`/`xoxd-`, asignaciones **sin comillas** (`API_KEY=abc123`), frontera a la izquierda en el nombre de la clave y menos falsos positivos (`bypassword`, `os.getenv(...)`). Son **7 reglas** con regexes más amplias, cubiertas por **77 escenarios**.
- **Contratos que solo estaban en la cabeza se volvieron pruebas:** la versión del paquete tiene **SSOT único** en `src/updater.py` (ADR-014) y una prueba que falla si `pyproject.toml`, `CLAUDE.md` o `GEMINI.md` divergen; el contrato de exit code del baseline ganó 8 escenarios de integración; el read-back de revisores legado (`None`) dejó de imprimir `attached None`; y `-h` antes del subcomando volvió a renderizar la ayuda en lugar de abrir la TUI.
- **La 1.5.0 está tagueada, pero aún no publicada:** la tag `v1.5.0` apunta al merge del PR #260 (2026-11-04), pero el PyPI todavía sirve **1.4.4** — el release workflow existe (`.github/workflows/release.yml`), pero la publicación debe confirmarse.

- **Versión actual:** 1.5.0 (**tagueada como `v1.5.0`; el PyPI todavía sirve 1.4.4**)
- **Versión de los diccionarios de idioma:** v0.0.38 (🆕 +2 desde v0.0.36, ahora con los diccionarios realmente modificados)
- **Versión de los scripts de hook:** v0.0.4 (sin cambios)
- **Publicación:** PyPI (`pip install gitpr-cli`) — canal binario eliminado
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 diccionarios)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal) — **15 subcomandos** (`badge`, `baseline`, `check`, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `split`, `tests`). Cuatro de ellos son **grupos**: `baseline` (6 subcomandos), `metrics` (8), `policy` (7) y `tests` (1). **33 opciones Click en la raíz** — 🆕 `--close-issue` y `--no-close-issue`.
* **UI/Terminal:** Textual — TUI para chat interactivo, edición de issues, pantalla de ayuda, dashboard de métricas, PR Publisher, **CommitApp** (`src/ui/commit_app.py`, 220 líneas), errores del linter (`LinterApp`), configuración (`ConfigApp`), modal `NoticeScreen`, el recorrido `gitpr demo` (`src/ui/demo/`), el asistente de migración del ledger (`src/ui/metrics_migration_app.py`) y 🆕 **`PrBaseApp`** (`src/ui/pr_base_app.py`, 98 líneas), el modal que cambia la base del PR cuando es igual a la rama actual.
* **Arquitectura en Capas:** `src/domain/` (regla pura, sin I/O) + `src/application/use_cases/` (orquestación, **15 casos de uso**) + presentación (CLI/TUI). **9 paquetes de dominio**: `baseline`, `check`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`. 🆕 **Fuera del dominio:** `src/issue_autoclose/` (flujo de cierre de issue sobre `ScmProvider` y el registro local) y `src/project_config.py` (resolución de path/YAML y validación contra el remoto) — ambos son orquestación y borde, no regla pura.
* **Extensión de Editor:** `extensions/vscode/` — cliente MCP fino en TypeScript para **VS Code y Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). No contiene lógica de negocio.
* **Criptografía:** `cryptography.fernet` para protección local de claves de API, tokens de GitHub y tokens SCM de los forges.
* **Configuración:** `python-dotenv`, `pyyaml` (linter estático y configuración del proyecto) + schema declarativo propio (`src/config_schema.py`, **85 `ConfigField`** y **17 categorías** — 🆕 la categoría `project`). `packaging` alimenta la comparación de versiones de la puerta (PEP 440) y la compatibilidad de los policy packs.
* **Proveedores de IA:** Integración vía SDK oficial de Google GenAI, OpenAI SDK (DeepSeek) y OpenAI SDK (Ollama local). Predeterminados configurados: `gemini-pro-latest` / `gemini-flash-lite-latest` y `deepseek-v4-pro` / `deepseek-v4-flash`.
* **APIs de Forge:** `requests` (REST) — abstracción multi-forge en `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py`; `src/github_api.py` se mantiene como shim obsoleto. 🆕 `IssueInfo` y las tres operaciones de issue (`get_issue`, `get_default_branch`, `closing_line`) entraron en el contrato de `ScmProvider`.
* **Linter / SAST:** YAML regex + bridge Checkstyle + ruleset de secretos integrado (`src/security_ruleset.py`) + 4 puentes en `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), con el modelo normalizado en `src/domain/linter/sast_finding_mapper.py` y el hallazgo canónico en `src/domain/finding/`. 🆕 El ruleset mantuvo las 7 reglas y **amplió la cobertura** de cada regex.
* **Infraestructura de CI:** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) y `masking.py` (enmascarado de credenciales antes de serializar cualquier artefacto).
* **Internals de Git:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py`, `risk_history_reader.py` y `test_matcher.py`.
* **Persistencia local:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) con WAL, `PRAGMA user_version`, claves UUID y transporte por bundle. 🆕 El evento `issue_autoclose` entra en el mismo ledger, con `asked`/`accepted`/`skipped_reason` en el `meta`.
* **Red:** `src/net.bounded_urlopen` (`UPDATE_FETCH_HARD_TIMEOUT = 3.0`) acota el **tiempo de reloj incluyendo el DNS** en la puerta de actualización. 🆕 `get_issue()`/`get_default_branch()` usan un timeout de **5 s** (`GITPR_ISSUE_AUTOCLOSE_TIMEOUT`), porque el flujo de PR solo necesita un sí/no.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 herramientas anotadas**, **19 recursos**, 7 prompts. 🆕 El cierre de issue **no** expuso ninguna herramienta nueva: es comportamiento del flujo de PR; `publish_pull_request` pasó a compartir el resolvedor de base y a devolver `warnings`.
* **Pruebas:** Pytest + `unittest.mock` (**153 módulos de prueba** — 59 en la raíz, 12 en `tests/application/use_cases/`, 10 en `tests/fix/`, 9 en `tests/scm/`, 7 en `tests/badge/`, 6 en `tests/split/`, 6 en `tests/demo/`, 5 en `tests/domain/baseline/`, 5 en `tests/issue_autoclose/`, 4 en `tests/domain/policy/`, 4 en `tests/review/`, 4 en `tests/infrastructure/linter/external/`, 3 en `tests/domain/check/`, 3 en `tests/infrastructure/ci/`, 3 en `tests/integration/`, 2 en `tests/domain/risk/`, 2 en `tests/domain/tests_generation/`, 2 en `tests/infrastructure/git/`, 2 en `tests/domain/linter/`, 1 en `tests/domain/finding/`, 1 en `tests/domain/mentor/`, 1 en `tests/domain/pr/`, 1 en `tests/infrastructure/baseline/`, 1 en `tests/unit/` —, **3276 escenarios recolectados**) + pruebas e2e del servidor MCP sobre un subproceso real (JSON-RPC stdio) + fixtures de repositorio git real (`tests/fix/`, `tests/split/`, `tests/test_full_diff_encoding.py`) + guardia de aislamiento de red (`tests/demo/conftest.py`, `tests/integration/conftest.py`). La extensión tiene su propia suite Mocha (7 pruebas contra el servidor MCP real).
* **Empaquetado:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; packs embebidos vía `package-data`; `packaging>=23.0`. La extensión se empaqueta aparte (`vsce package`, VSIX).
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` (llama a `gitpr check`) + `tests.yml` (matriz Python 3.10/3.13) + `extension.yml` (build, prueba y empaquetado del VSIX; publicación en el Marketplace/Open VSX en tags `ext-v*`) + 🆕 `release.yml` (pruebas, verificación tag/versión vía SSOT, build sdist/wheel, `twine check --strict`, Trusted Publishing OIDC en PyPI y creación de GitHub Release en tags `v*`).

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación Estructurada:** Se comunica con el LLM pidiendo salida estrictamente JSON.
* **Map-Reduce (Diffs Gigantes):** Cuando el diff supera ~90k tokens, se divide automáticamente en lotes por archivo (`split_diff_into_chunks`), cada parte se procesa (Map) y los resúmenes se unifican (Reduce). Soporta PRs, commits e Issues. El `provider`/`model` se agrega entre chunks y `meta`/`modules` se reenvía a `log_command_metric`.
* **Tokenizer Local:** `tokenizer.json` para estimación precisa de tokens antes del envío a la IA.
* **Estimación de Tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()` con fallback al tokenizer local.
* **Optimización Git Nativa:** Flags `-U1`, `-w`, `-M`, `-B` en los comandos `get_git_diff` y `get_git_full_diff`.
* **`get_git_staged_diff()`:** ejecuta `git diff --cached -U1 -w -M -B -- <SMART_EXCLUDES>` con `encoding="utf-8"`/`errors="replace"` y `stdin=DEVNULL`, devolviendo `None` cuando git falla (nunca una cadena vacía disfrazada). Es la fuente preferida del mensaje de commit: lo que no está preparado no entra en el prompt.
* **`get_split_diff()`:** diff propio de `split`, con `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sin** reutilizar `get_git_diff()`.
* **🆕 Seed de la Configuración del Proyecto:** el callback raíz y `--install` llaman a `ensure_project_config()` (`src/project_config.py`) en cada invocación dentro de un repositorio, excepto en los caminos de ayuda. Un archivo existente **nunca** se sobrescribe; la creación se anuncia solo en el camino interactivo de la raíz y puede desactivarse con `GITPR_SKIP_PROJECT_CONFIG_SEED` (escape hatch de la suite de pruebas).
* **🆕 `get_base_branch(quiet=)`: ** la resolución de la base dejó de ser improvisada y pasó a delegar en `resolve_pull_request_base()`; `quiet` evita que el aviso de fallback corrompa la TUI o el stream JSON-RPC.
* **Pre-Grabado (`--pre-save`):** Flag de depuración oculta que guarda el payload completo (instrucción de sistema + prompt) como JSON antes de cada llamada a la IA.
* **Smart Excludes con Dos Capas:** Capa global (`~/.gitpr/conf/`) + capa local del proyecto (`./.gitpr/conf/`), fusionadas en runtime. Auto-seed del archivo local en la primera ejecución. `_load_smart_excludes()` acepta `force=`.
* **Métricas con Seguimiento Temporal:** `log_command_metric()` en todos los flujos, con `meta`, `modules` y `duration_ms`.
* **Resolución Centralizada de Salida:** `resolve_output_path()` — por defecto en `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`)**: `gitpr --init` — detecta el forge desde el remote de origen, pide los extras por forge, valida el token y persiste **solo si tiene éxito**.
* **Plantilla de Skill de Release (`ensure_release_skill_template()`)**: Descarga `templates/gitpr.release.*.md` en el primer uso de `gitpr release`.
* **Guarda de Skill Corrupta:** `_CORRUPTED_PR_SKILL_ARTIFACT` documenta el fragmento JSON que se pegó a la plantilla `pt_br`, y `get_skill_context()` lo elimina **en memoria** cuando `action_type == "pr"`.
* **Registro Compartido de Skills:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` en `src/config.py`.
* **Motor de Review con Ámbito de Caché (`generate_pr_content`):** `cache_scope` y `store_diff`; el `cache_scope` transporta también el policy pack activo.
* **Trailer de Coautoría:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceros. Es este trailer el que la TUI de commit aplica al mensaje editado antes de `execute_git_commit()`.
* **Captura de Salida de Git Blindada:** las llamadas a `subprocess.run` en `src/cache.py`, `src/core.py`, `src/issue_engine.py` y `src/main.py` que capturan salida de git usan `encoding="utf-8"` **con** `errors="replace"` — incluida `get_git_staged_diff()`.
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
* **Enrutamiento de Comandos:** Gestiona todas las flags y los **15 subcomandos** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline` y `check`.
* **Comportamiento Predeterminado:** Ejecutar `gitpr` sin flags abre la TUI del PR Publisher.
* **Flags (33 opciones Click en la raíz):** 🆕 `--close-issue` y `--no-close-issue` — mutuamente excluyentes, rechazadas con exit code 1 antes de cualquier prompt o escritura, y con la regla de que `-h` vence al conflicto. Las demás (`--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version`, etc.) siguen sin cambios.
* **Rama de `-c`/`--commit`:** `--hook` → comportamiento silencioso y merge-aware; sin hook → `CommitApp`. **El camino sin hook sigue sin guarda de TTY** — abrir `gitpr -c` en un script todavía entra en la TUI.
* **🆕 Flujo de Cierre de Issue:** `_prepare_close_issue(flags, base, may_ask)` corre **antes** del Stage Selector, reutiliza el contexto SCM resuelto y devuelve la decisión (línea de cierre + issue) más el registro. La línea entra con `insert_close_line(pr_desc_body, decision)` en el punto único de montaje del cuerpo. `_publish_pr_directly` ahora devuelve **bool** para que el registro solo se limpie después de una publicación real (ADR-007).
* **🆕 Resolución de la Base del PR:** la rama `--base` pasa por `_resolve_pr_target()`, que corrige un valor obsoleto del archivo del proyecto cuando la ejecución es interactiva y, fuera de ella, informa el rechazo. El archivo `.gitpr/conf/gitpr.config.yml` se siembra en toda invocación dentro de un repositorio, excepto en los caminos de ayuda.
* **Puerta de Actualización en los Subcomandos:** el callback del grupo corría antes de la puerta raíz, así que `gitpr release`, `fix`, `review-pr` y los demás **nunca** se bloqueaban por un release nuevo. La puerta corre antes del `return`, condicionada a `may_ask and not update and not help_flag and not _subcommand_help_in_argv()`. `_may_ask_the_user()` exige un **TTY en ambos extremos** — CI, pipes, cron, hooks y el servidor MCP quedan libres.
* **🆕 `-h` Antes del Subcomando Corregido:** `_subcommand_help_in_argv()` existe porque Click **limpia los argumentos del contexto** antes de que corra el callback del grupo. El escaneo del `argv` en bruto pasó a tratar el caso `gitpr -h config`, que antes abría la TUI de configuración en vez de la ayuda. Cubierto por `tests/test_cli_help.py`.
* **Subcomando `check` — 8 opciones:** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on …`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` y `--policy <name>@<version>`.
* **Grupo `baseline` — 6 subcomandos:** `create`, `show`, `validate`, `update`, `suppress` y `unsuppress`.
* **Grupo `policy` — 7 subcomandos:** `list`, `show`, `validate`, `use`, `off`, `install` e `init`.
* **Grupo `metrics` — 8 subcomandos:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` y el oculto `hook-event`.
* **Subcomando `risk` — 4 opciones:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Subcomando `mentor` — 2 opciones:** `--finding`, `--provider`.
* **Variables de Entorno (58 claves en `DEFAULT_CONFIG`):** las cuatro `GITPR_BASELINE_*`, las dos del mentor, las dos de policy y `GITPR_RISK_INCLUDE_IN_REVIEW`. **Fuera de `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY`, las **9 `GITPR_CHECK_*`**, `GITPR_SKIP_UPDATE_CHECK` (booleano estricto) y 🆕 `GITPR_ISSUE_AUTOCLOSE`, `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` y `GITPR_SKIP_PROJECT_CONFIG_SEED`. Las dos primeras aparecen en el schema de la TUI (categoría `issue`); las demás son defaults read-only deliberadamente **no** sembrados en `~/.gitpr/.env`.
* **Ayuda Contextual:** `gitpr -h --explain` renderiza; `HELP_MAP["explain"]` y `HELP_PRIORITY["explain"]` apuntan a `docs/explain-pr`, y `gitpr tests`/`gitpr tests generate` tienen su propio `epilog=`. 🆕 `HELP_MAP` ganó `close_issue` y `no_close_issue`.
* **Capa de Escritura del `.env`:** `read_env_file_values()` lee **solo el archivo** vía `dotenv_values`; `save_config_values()` con `set_key`; `remove_config_value()` con `unset_key`.
* **Puerta del Baseline en el Flujo:** `main.py` resuelve la puerta del baseline antes del lint, del review, del full review, de la auditoría de archivos, del review remoto y del riesgo.
* **Publicación Directa Refactorizada:** `_publish_pr_directly` (`--no-edit`) delega en el **`publish_pull_request_use_case`** — el mismo camino que la herramienta MCP `publish_pull_request` — y devuelve si la forge creó el PR.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)**

* **Interfaz Interactiva Completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en el terminal.
* **7 Pantallas Modales:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` y `NoticeScreen`.
* **Tres de Ellas También Sirven al Commit:** `StageFilesScreen`, `CommitMessageScreen` y `CommitProgressScreen` son importadas por `CommitApp` — no se duplicó ningún widget, y el helper `_with_suppressed_stdout` también se comparte.
* **🆕 `body_published` Distingue Crear de Abrir:** `PrPublishApp` pasó a marcar cuándo el cuerpo se creó/actualizó realmente en la forge, y no solo cuando se abrió un PR existente. Es esa señal la que permite limpiar el registro del issue solo después de una publicación de verdad.
* **Insignia en el Cuerpo del PR:** `attach_pr_badge()` corre en `src/main.py` justo después de que `pr_data` esté completo — **punto único de inyección**.
* **`_attach_reviewers` con resolución:** Resuelve lo que el usuario escribió **antes** de enviar, reporta lo que fue descartado y, en un `422` de lote, **repite uno a uno**. 🆕 Cuando un proveedor de terceros sigue el contrato legado y devuelve `None` en el read-back, el log dice `(no read-back)` en vez de `attached None`.
* **Revisores Sugeridos:** Consulta al forge por revisores sugeridos; `_reviewer_suggestion_view()` construye las `resolutions` vía `resolve_candidates`.
* **Bindings:** F1 (Help), F2 (Guardar .md local), F3 (Publicar vía forge), Esc (Salir).
* **Flujo de Auto-Commit:** Linter → mensaje por IA → confirmación → commit → push → publicación del PR.
* **Modal de Progreso en el Merge:** `CommitProgressScreen` con `title` opcional y `mark_finished(show_close=False)`; el éxito muestra `✅ PR #N merged successfully!`, los conflictos y errores inesperados pasan por `_on_merge_failure`.
* **Manejo de PR Existente / Auto-Upstream / Flujo de Merge** — sin cambios (`GITPR_AUTO_MERGE`).

### **5. Módulo de API de GitHub (`src/github_api.py`)**

* **Shim Deprecado:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` y las demás funciones delegan en `src/infrastructure/scm/github_provider.py`; emite un `DeprecationWarning` y mantiene las tuplas legacy `(ok, data, status)` — ningún código nuevo debe importarlo.

### **6. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) en el diff sin gastar cuota de IA.
* **Reglas YAML:** Lee `.gitpr.linter.yml` (creado vía `--skill`).
* **Plugins de Linter:** Reglas adicionales desde `~/.gitpr/plugins/linter/*.yml`.
* **Comodín de Extensión:** `extensions: ["*"]` significa **todo** archivo, incluidos los que no tienen sufijo y los dotfiles.
* **Ruleset de Secretos Integrado:** `load_linter_rules()` fusiona `src/security_ruleset.py` **después** de las reglas del proyecto y de los plugins. Siete reglas, todas `extensions: ["*"]`: cinco `error` y dos `warning`. La alerta **nunca repite el valor**.
* **Capa de Policy Pack:** `load_linter_rules()` aplica la fusión en capas del pack activo y **sobreescribe severidades**.
* **Hallazgos Estructurados (`findings_out`):** además de las cadenas de alerta, el motor devuelve los hallazgos normalizados del tipo canónico `src/domain/finding/`.
* **Puentes SAST Opt-In:** Semgrep, Gitleaks y Bandit corren en el camino de archivo completo **y** en el camino del diff, con deduplicación `[Gitleaks + Regex]`, enmascarado de valores (`AKIA****`) y un aviso cuando la herramienta está habilitada pero falta del `PATH`.
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=True)` en el flujo remoto.
* **Informe Consolidado:** `generate_linter_report_content()` consolida errores regex + externos en `.gitpr/reports/linter/` — generado solo cuando hay violaciones.
* **Consciente del Baseline:** los hallazgos se anotan con el estado del baseline antes de convertirse en informe.

### **7. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cifrado:** Genera la clave maestra `secret.key` en `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data`/`decrypt_data` para claves de IA, PAT de GitHub y tokens SCM de los forges.
* **Validación Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — 401 → bucle de reautenticación preservando el borrador.
* **Secretos en la TUI de Configuración:** Los campos `KIND_SECRET` se editan enmascarados, **nunca** muestran el valor en claro y se cifran con Fernet antes de escribirse.
* **Escaneo de Secretos en el Propio Flujo:** El linter — que corre en el hook pre-commit — es una puerta de credenciales con reglas idénticas en toda máquina.
* **Enmascarado en CI:** `src/infrastructure/ci/masking.py` elimina claves privadas, AWS access key ID/secret, tokens de GitHub, Google, OpenAI y Slack, `Bearer`, basic-auth en URLs y asignaciones genéricas de contraseña **antes** de serializar el artefacto. La extensión tiene un espejo en TypeScript (`masking.ts`).
* **✅ Brechas de cobertura del ruleset cerradas en esta ventana:** la regla genérica pasó a detectar asignaciones **sin comillas**, los prefijos `ASIA…`, `github_pat_…` y `xoxc-`/`xoxd-` entraron en el catálogo, y la frontera a la izquierda del nombre de la clave impide falsos positivos como `bypassword`. El estado del arte se detalla en la sección 31.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI como Fuente Única:** `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` y escribe la caché. Acepta `force=True`, que `--update` usa para ir **siempre** a la red.
* **Ventana Deslizante de 1 Hora (`UPDATE_CACHE_TTL_SECONDS = 3600`):** la caché dejó de ser "válida mientras la fecha local sea hoy". El documento es `{"checked_at": <ISO>, "version": ...}`; `_cache_is_fresh()` acepta una edad en `[0, 3600)` segundos.
* **Validación en la Lectura:** un `dict` real, un timestamp parseable y una versión como cadena no vacía. Un timestamp **en el futuro** cuenta como expirado, y el formato legado con `date` se trata como expirado y se reescribe.
* **Escritura Atómica:** `tempfile.NamedTemporaryFile` en el mismo directorio + `os.replace`, con limpieza del archivo temporal en caso de fallo.
* **Fetch Blindado (`bounded_urlopen`):** un cuerpo no-JSON y un payload sin versión se **rechazan antes de entrar en la caché**. Techo de 3 s de tiempo de reloj, DNS incluido.
* **Comparación PEP 440:** `_coerce_version()` usa `packaging.version.Version`, tolerando la `v` inicial y los sufijos PEP 440; `parse_version()` mantiene el contrato de tupla para los llamadores externos. `_is_newer()` **ignora un pre-release remoto** en una instalación estable y trata una versión ilegible de cualquiera de los dos lados como desconocida.
* **Puerta Obligatoria (`enforce_update_required()`):** Devuelve `True` (tras imprimir ambas versiones y el comando pip) cuando la versión publicada es más reciente; `False` cuando está al día, cuando la versión remota es **desconocida (offline)** o cuando la verificación está apagada.
* **Semántica Estricta de la Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` se parsea contra `_SKIP_TRUTHY_VALUES` (`1`, `true`, `yes`, `on`, `y`).
* **`check_and_update()`:** Solo consulta e **informa**, nunca instala.
* **Versionado Centralizado:** `__version__` (**1.5.0** 🆕), `__lang_version__` (**v0.0.38** 🆕), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`. 🆕 El SSOT del paquete se volvió un contrato explícito ([ADR-014](docs/adr/0014-canonical-package-version-ssot.md)) y está cubierto por `tests/unit/test_version_consistency.py`.
* **⚠️ Estado del release — tagueado, no publicado:** la tag `v1.5.0` apunta al merge del PR #260 (`4c2bede`, 2026-11-04) y la sección `[1.5.0] - 2026-11-03` está commiteada en el `CHANGELOG.md`. PyPI, sin embargo, todavía sirve **1.4.4** — la publicación por `release.yml` debe ejecutarse/confirmarse. Mientras tanto, la puerta no bloquea a nadie (la versión remota es más antigua que la local).
* **Puerta Revalidada:** con la 1.4.4 publicada en la ventana anterior, el escenario 1.4.2 → 1.4.4 quedó cubierto por el TTL de 1 hora; la revalidación real en un cliente antiguo sigue siendo un seguimiento de campo.

### **9. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Historial de mensajes, entrada multi-línea, barra de estado con bindings visibles. `ENABLE_COMMAND_PALETTE = False`.
* **Memoria por Rama (`src/chat_memory.py`):** Historial persistido por rama, con continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`. `/tests` (y los alias localizados) delega en el **mismo caso de uso** que usa la CLI.
* **Auto-Patching (F5), Actualización de Diff (F2), Exportación de Sesión (F6).**
* **Extractor Compartido:** F5 y `ctrl+s` llaman a `patch_extractor.extract_code_blocks()` — comportamiento visible idéntico, sin lógica duplicada.
* **🆕 Ayuda Localizada de Verdad:** `ChatHelpScreen` dejó de tener literales estáticos y compone cada línea vía `__()`, con los rótulos corregidos (`Ctrl+Shift+S`, `Ctrl+Shift+E`).

### **10. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`; el directorio de perfil se crea **antes** de persistir.
* **5 Idiomas, 6 Diccionarios:** en_us (predeterminado/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Archivos Versionados:** `__lang_version__` (**v0.0.38** 🆕) controla la actualización de los paquetes (`langs/*.json`).
* **✅ Cobertura — la deuda se pagó:** los **seis** archivos tienen **1698 claves** cada uno. El código usa las mismas claves; no hay clave sin diccionario ni huérfana. `tests/test_i18n.py` cierra **22 en verde**, incluyendo la paridad de conteo, la ausencia de claves faltantes/huérfanas y el guarda de identidad.
* **🆕 El Bump de Idioma Ahora Tiene Contenido:** `__lang_version__` pasó de v0.0.36 a **v0.0.38** y, esta vez, los diccionarios cambiaron: español y francés se sincronizaron con policy packs, baseline, mentor, risk, MCP, base del PR, configuración de proyecto y atajos del chat; la renovación OTA entrega traducción nueva.
* **🆕 Guarda Contra Regresión:** `test_all_identity_keys_are_allowlisted` falla cuando una clave cuyo valor es igual al inglés no está en la allowlist de términos técnicos/acrónimos/cognados legítimos.
* **Snapshot del Entorno (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado **inmediatamente antes** del `load_dotenv()` a nivel de módulo.
* **Caché con Indexación por Idioma:** Las respuestas de IA en caché incluyen el idioma actual en el keying MD5.
* **Fuente de Traducción en Lockstep:** una clave nueva debe existir en el código, en `langs/pt_br.json` (lista maestra), en los diccionarios FR/ES de `scripts/sync_all_langs.py` y en los valores curados de `scripts/fix_mangled_i18n_keys.py`. 🆕 El extractor de `sync_i18n.py` usa **AST**, no regex.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en background durante llamadas de IA con caracteres braille y palabras de "pensamiento".
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceptan `force=`.
* **La TUI de Commit No Usa el Spinner:** la TUI muestra `CommitProgressScreen` con su propia barra animada, y la llamada a la IA corre en un `threading.Thread` daemon. Con `--hook` el spinner clásico sigue siendo el único feedback.

### **12. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini, DeepSeek y Ollama (local), con un primario/secundario por proveedor.
* **Modo JSON & Parámetros Deterministas:** `temperature=0.0` y `top_p=0.1`; fallback automático entre los proveedores configurados.
* **Timeout de IA:** predeterminado de **180s** (`GITPR_AI_TIMEOUT`).
* **Llamadas con `quiet`:** el release y las funcionalidades nuevas propagan `quiet` hasta la capa de IA, para que el spinner nunca corrompa una salida `--format json`.
* **Una Llamada a la Vez, con Caché:** mentor y explain reutilizan la caché MD5 por prompt.
* **Payload Ya Interpretado:** `parse_explain_payload()` y `generate_test_file` aceptan el **dict** que entrega el proveedor, con fallback a la cadena JSON cruda (respuestas antiguas de caché).
* **Supresión de Stdout en el Commit:** `_generate_commit_msg()` redirige `sys.stdout` a `os.devnull` durante la llamada (el mismo patrón de `_with_suppressed_stdout`), restaurándolo en `finally` — un print del proveedor no corrompe la pantalla de Textual.

### **13. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Keying por hash MD5 del diff y del prompt, con indexación por idioma.
* **Selección del Último Review (`resolve_last_review()`):** Elige el registro `review`/`fullreview` **más nuevo** para un par repo+rama — es la puerta de entrada del `gitpr fix` y de `gitpr mentor`.
* **Diff Revisado (`reviewed_diff`):** Campo en la parte superior del registro que guarda el diff efectivamente revisado.
* **Telemetría y Duración:** Persistencia de `duration_ms` y `meta_raw`.
* **Lectura para el Dashboard:** `scan_cache_files_for_dashboard()` es un **puente pre-migración**.
* **Efecto de la Sanitización de Skill:** como `get_skill_context()` elimina el fragmento corrupto del prompt de PR, la clave MD5 cambia y las respuestas antiguas contaminadas dejan de reutilizarse.
* **`git config` Decodificado con Seguridad:** las lecturas de `user.name`/`user.email` usan `encoding="utf-8"` + `errors="replace"`.
* **El Commit Reutiliza la Caché:** la TUI llama a `generate_pr_content("commit", "commit", diff_text, provider)`, así que el mismo mensaje para el mismo diff no dispara una segunda llamada a la IA.

### **14. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`) y Arqueología por Blame (`-b`).
* **Publicación Multi-Forge:** F3 crea el issue en el forge **configurado** (`GITPR_SCM_PROVIDER`) vía `provider.create_issue` — Azure DevOps lanza `ScmNotSupportedError`.
* **Map-Reduce para Issues:** El contexto por encima de ~90k tokens se divide y se unifica.
* **Manejo de 401:** Señalización de reautenticación sin cerrar la aplicación.
* **`git remote -v` Decodificado con Seguridad:** la llamada usa `encoding="utf-8"` + `errors="replace"`.
* **🆕 El Número Dejó de Perderse:** tras una creación exitosa en GitHub, `register_created_issue()` guarda el número en `.gitpr/conf/github.yml`. Los fallos de bookkeeping se absorben — un problema local nunca convierte una creación exitosa en error — y la primera escritura añade el archivo al `.gitignore`.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y la autoría histórica con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos vía `log_blame_metric()` con profundidad y número de commits analizados.
* **Ahora también alimenta al riesgo:** el lector de historial de la puntuación (`risk_history_reader.py`) usa la misma ventana de 90 días y clasifica bugs/reverts por patrón, sin llamar a la IA.

### **16. Servidor MCP e Invocación CLI Directa (`src/mcp_server.py`)**

* **18 Herramientas MCP Anotadas:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates`, `review_remote_pr`, `get_baseline_status`, `get_usage_metrics`, `run_check` y `publish_pull_request`. 🆕 El cierre de issue **no** se volvió una herramienta: es comportamiento del flujo de PR.
* **Parámetros Opt-In en el Contrato:** `review_code` y `full_review` aceptan `mentor: bool = False`; `generate_pr_description` acepta `explain: bool = False`. Son **puramente por parámetro** — nunca hacen OR con los valores por defecto del `.env`.
* **19 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Registry de Skills Alineado:** `mcp_server.SKILL_FILES` tiene **11 entradas** (`tests`, `explain` y `mentor` incluidas); `TestSkillRegistryAgreement` pasa.
* **Invocación CLI Directa:** `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca cualquier tool sin iniciar el servidor stdio; `gitpr-mcp --list` imprime el registry completo como JSON.
* **Aislamiento del Stdout Real:** `_write_real_stdout()` escribe en el `sys.__stdout__` original, con fallback a `buffer` o re-codificación con `errors='replace'` en páginas de código legacy de Windows.
* **Offload del Event Loop:** Decorador `_offload` (`anyio.to_thread.run_sync`) en todas las tools — el orden de los decoradores importa (`@mcp.tool` **encima** de `@_offload`).
* **Fallos como Errores Estructurados:** las herramientas nuevas devuelven el fallo **como dato** en lugar de lanzar.
* **🆕 `publish_pull_request` con el Resolvedor Compartido:** usa `resolve_pull_request_base()`, devuelve el envelope `{"status": "error"}` para `BaseBranchError` y expone los `warnings` del resolvedor en el payload y en el catálogo de la herramienta.
* **Métricas MCP:** `run_check` y `publish_pull_request` escriben filas locales (`mcp:check`, `mcp:publish_pr`).
* **El servidor MCP nunca queda bloqueado por la puerta:** `--mcp`/`gitpr-mcp` está en la lista de exenciones desde la primera versión de la puerta.

### **17. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Alcance por Repositorio:** Etiqueta `📁 Repository: owner/repo` y filtrado estricto por proyecto.
* **Escaneo Asíncrono con Overlay:** Worker thread en background con una `ProgressBar`; las pruebas esperan `workers.wait_for_complete()` antes de asertar.
* **Lectura del Ledger:** el dashboard consulta `telemetry.db` en el worker thread, renderizando las cinco secciones (costo, calidad, módulos, proveedores, ciclo) con los mismos renderers que usa la CLI.
* **Asistente de Migración (`src/ui/metrics_migration_app.py`):** ofrece importar los archivos legados, importar **y** reconstruir desde la caché de IA, o "ahora no".
* **🆕 Artefactos de Exportación Desrastreados:** los **34 archivos** de `.gitpr/metrics/export/` que seguían rastreados se eliminaron del índice (`git rm --cached`); el `.gitignore` es ahora la única fuente sobre ellos.

### **18. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Ámbito por Repositorio:** Todos los eventos indexados por `repo_name`, resuelto por `src/infrastructure/git/identity.py`.
* **Eventos de Hook, Linter y Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. `gitpr check` escribe `command="check"` con `findings_total`/`findings_errors` y el exit code.
* **🆕 Evento `issue_autoclose`:** una línea por decisión de cierre, con `asked`, `accepted` y `skipped_reason` en el `meta` — permite medir cuántas ofertas se aceptaron, se rechazaron y por qué motivo.
* **Exportación y Limpieza:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` con confirmación interactiva, `bundle`/`merge` para transportar y fusionar ventanas.
* **El Commit Interactivo También Mide:** el camino `commit` sigue escribiendo la línea de comando con `meta` y `modules`; la TUI no creó un nuevo tipo de métrica.

### **19. Sincronización de Idiomas de los Hooks Git**

* **Versionado Independiente:** `__scripts_version__` (**v0.0.4**, sin cambios).
* **Plantillas reescritas en la ventana v0.0.17:** `post-checkout`, `post-merge` y `pre-push` llaman a `gitpr --quiet metrics hook-event <name>`, con guardia por `command -v gitpr` y `|| true`.
* **Mapeo de Sufijos (`HOOK_SCRIPT_SUFFIXES`):** Los códigos de interfaz (`es_es`, `fr_fr`) se traducen a los sufijos publicados (`.es`, `.fr`).
* **Elección vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detectar un cambio de idioma**.
* **`effective_hook_lang()`:** Resuelve el idioma efectivo; `--lang` dejó de descartarse en esa ruta.
* **Skip de Merge-Source:** `prepare-commit-msg` salta las fuentes `message|merge|squash|commit`.
* **`prepare-commit-msg` Sigue No Interactivo:** el hook corre con `--hook`, así que **nunca** abre `CommitApp` — la TUI existe solo en el uso humano de `gitpr -c`.
* **Los hooks corren con `--quiet` y `--hook`, así que nunca quedan bloqueados por la puerta de actualización.**

### **20. Bridge de Linters Externos y Asistente Interactivo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Asistente `--linter-setup`:** Wizard con presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e inyección del bloque `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` con la cadena de fallback local → descarga → stale → embebido.
* **TUI de Errores del Linter:** `src/ui/linter_app.py` (Textual) muestra errores críticos y warnings; en modo hook/quiet imprime y hace `sys.exit(1)`.
* **Informe Markdown:** Consolidado en `.gitpr/reports/linter/` solo cuando hay violaciones.
* **La Capa SAST Es Hermana, No un Reemplazo:** los puentes Semgrep/Gitleaks/Bandit alimentan el **mismo** pipeline de informes — y también `gitpr check`.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstracción Única (`ScmProvider` ABC):** `base.py` define el contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. y `ScmProviderError(provider, http_status, message)` — `http_status` 0 = fallo de red).
* **Registry y Factory:** `resolve_scm_provider()` selecciona por `GITPR_SCM_PROVIDER` (predeterminado `github`); `detect_provider_from_remote()` identifica el forge desde la URL de origin.
* **`list_pull_requests(repo, state, since, until)` — paginado y canónico:** los cuatro proveedores traducen los estados canónicos (`open`/`merged`/`closed`/`all`) al vocabulario nativo, paginan en el cursor de cada API y filtran por una ventana de fechas inclusiva.
* **Por qué el filtro es por fecha de estado:** tres de los cuatro forges devuelven algo más amplio para un PR fusionado, así que cada uno filtra su propia respuesta contra la fecha que el estado implica.
* **`supports_merged_dates`:** atributo de clase, `False` en Bitbucket Cloud, que publica `merged_at` **vacío**.
* **`web_links.py`:** funciones puras (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) que normalizan cualquier formato de remote.
* **`get_pull_request(repo, pr_id)` — método concreto de la ABC:** el default lanza `ScmNotSupportedError` y cada forge lo implementa.
* **🆕 `IssueInfo` y las Operaciones de Issue:** `get_issue()` devuelve `IssueInfo` con `state` en el vocabulario de la forge y `is_pull_request`; `get_default_branch()` lee la rama por defecto; `closing_line()` devuelve la sintaxis de cierre (`close #N` en GitHub). Las tres son **defaults concretos** de la ABC que lanzan `ScmNotSupportedError`, así que las otras tres forges degradan a "funcionalidad inactiva" en vez de romperse.
* **GitHub:** `GET /repos/{o}/{r}/issues/{n}` (marcando `pull_request` y cayendo al número pedido cuando falta), `GET /repos/{o}/{r}` para `default_branch` (cadena vacía cuando falta) y el keyword `close #N`.
* **Encabezados de GitLab Sintetizados:** `changes[].diff` es un hunk suelto; `old_path`/`new_path` montan los encabezados `diff --git a/… / --- / +++`, honrando `/dev/null`. Y `overflow: true` **lanza** en lugar de revisar la mitad de un diff truncado.
* **`request_pull_request_reviewers` devuelve `list[str]`:** Devuelve los logins **efectivamente anexados**, leídos del cuerpo del `201`. 🆕 La ruptura de contrato está documentada en [ADR-0009](docs/adr/0009-reviewer-attach-returns-read-back.md) y en `docs/suggested-reviewers.*`/`docs/scm-multiforge.*`.
* **Helpers Read-Only de GitHub:** `get_commit_author_login` y `get_user_login`.
* **Fail-Fast por Forge:** Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket exige `GITPR_SCM_USERNAME`; `create_issue` en Azure lanza `ScmNotSupportedError`.
* **Publicación de Release:** `provider.create_release()` usado por `gitpr release --publish`.
* **Publicación Compartida de PR:** `src/application/use_cases/publish_pull_request.py`, usado por `--no-edit` y por la herramienta MCP. Valida título/cuerpo, **exige `provider.token` solo cuando `check_remote_state=True`**, verifica opcionalmente si ya existe PR y si la rama no fue empujada, enmascara los mensajes de error y devuelve `PublishPrOutcome`. Los campos `url`/`number` del resultado se leen con `getattr` defensivo.
* **Artefactos:** Glosario + ADR-001/ADR-005; la familia `docs/scm-multiforge.*.md` en 5 idiomas; 9 archivos de prueba, **317 escenarios**.

### **22. Subcomando `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Flujo:** `git log` entre `--since` y `HEAD` → clasificación Conventional Commits → bump semántico sugerido → armado del changelog → resumen ejecutivo por IA opcional → *prepend* en `CHANGELOG.md`.
* **El Rango Dejó de Venir de `git describe`:** el origen del rango es la **sección anterior del propio changelog**, mediante dos anclas: el hash más reciente de esa sección y, como fallback, el tag de versión de la sección.
* **`_drop_released()` — la segunda línea de defensa:** cualquier commit cuyo hash corto aparezca en **otra** sección de versión se descarta, con conteo.
* **Cada viñeta pasó a ser un enlace:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **El fallo del PR por merge commit:** una segunda pasada (`git log --merges`) construye `hash → PR` por ancestralidad. 🆕 La sección `[1.5.0]` cubre 7 PRs fusionados y lista los 15 merges de la ventana sin perder ninguno.
* **Contribuidores resueltos por la misma escalera que los revisores:** `get_commit_author_login` primero, luego `email_to_handle`, cacheados en `~/.gitpr/cache/contributors.json`.
* **La Frontera de la Sección Era un Bug Latente:** `upsert_changelog(force=True)` reemplaza desde la línea `## [x.y.z]` hasta el próximo encabezado de **nivel 2**.
* **Clasificador (`src/commit_classifier.py`)** y **Constructor con Secciones Traducibles (`src/changelog_builder.py`)**.
* **Bump Semántico (`src/version_bump.py`)** y **Publicación** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opciones en el subcomando.**
* **🆕 Estado del release:** la sección `## [1.5.0] - 2026-11-03` está commiteada y traducida; la tag `v1.5.0` existe. **La publicación en PyPI aún no ha ocurrido** — el paquete servido sigue siendo 1.4.4.
* **El subcomando pasa por la puerta de actualización** cuando se ejecuta en un terminal interactivo.
* **Artefactos:** `docs/release-notes.*.md` en 5 idiomas, ADR y `tests/test_web_links.py`.

### **23. Subcomando `gitpr config` — TUI de Configuración**

* **Pantalla Master-Detail (`src/ui/config_app.py`):** Categorías a la izquierda, campos a la derecha, editados en línea. Cabecera con búsqueda (`/`) y un contador de cambios pendientes; pie `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schema Declarativo (`src/config_schema.py`) — la fuente única de verdad:** **17 categorías** y **85 `ConfigField`**, de los cuales **8 son avanzados**. Las 9 `GITPR_CHECK_*` siguen **fuera** del schema.
* **🆕 Categoría `project` y el Atributo `store`:** los campos declaran dónde viven (`env` vs `project`). Los tres campos del proyecto son la ruta del archivo (read-only), la rama destino (editable) y la base efectiva con el origen (read-only). El nuevo `KIND_BRANCH` renderiza la elección de rama.
* **🆕 Categoría `issue`:** `GITPR_ISSUE_AUTOCLOSE` y `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` aparecen aquí.
* **🆕 Escritura por Store:** `ConfigApp` despacha por `store`: los valores de proyecto **nunca** pasan por `save_config_values()`, se escriben primero, soportan eliminación al vaciar, rechazan la rama actual y nunca sobrescriben YAML ilegible. La línea de la base efectiva se actualiza in-place después de guardar.
* **Categorías `policy` y `baseline`:** los campos del pack y los cuatro del baseline.
* **Sección Skills Alineada:** `SKILL_LABELS` tiene **11 entradas** (`tests`, `explain`, `mentor`).
* **Filtrado por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` según `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` bajo `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` bajo `[Azure DevOps]`.
* **Búsqueda Global (`/`):** Coincide con la clave o la etiqueta en todas las categorías e **ignora el filtro de visibilidad**.
* **Validación en Dos Capas:** **Offline** bloquea `F2` con un error en línea; **online** (solo credenciales cambiadas en la sesión) corre en un worker con timeout de 10s y solo bloquea ante `401`/`403`.
* **Restaurar (`Ctrl+R`):** Elimina la línea del `.env` en lugar de reescribir el valor predeterminado; `Esc` con cambios pendientes pide confirmación.
* **Sección Skills — la única project-scoped hasta ahora:** Edita `.gitpr/skill/*.md` del proyecto resueltos desde el directorio de invocación, escribiendo atómicamente y preservando CRLF/LF. 🆕 La categoría `project` introduce una **segunda** superficie versionada, con el mismo cuidado de escritura atómica.
* **Descargas Forzadas:** Botones que fuerzan la redescarga de smart-excludes, traducciones, presets y thinking words vía `force=`.
* **Módulo Ligero de Enlaces (`src/doc_links.py`):** `doc_url()` salió de `core.py` para que la UI pueda obtener el enlace sin importar los SDKs de IA.
* **Pruebas:** 5 archivos — `test_config_app.py` (88), `test_config_schema.py` (46), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9), más 🆕 `test_config_issue_autoclose.py` (10).
* **✅ Deuda cerrada:** `gitpr -h config` dejó de abrir la TUI y pasó a renderizar la ayuda (`tests/test_cli_help.py`).

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
* **✅ Documentación ahora completa en los 5 idiomas:** `docs/review-pr.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md` y `.fr_fr.md` — el tema dejó de ser parcial.
* **Pruebas:** 4 archivos en `tests/review/` (**97 escenarios**).

### **27. Resolución de Identidad del Revisor (`src/reviewer_resolution.py`)**

* **El Bug (dos fallos silenciosos encadenados):** (1) **Prefill vacío** — `handles` solo desde `email_to_handle()`; (2) **Attach no verificado** — valores pasados *verbatim*, GitHub respondió **201 sin anexar a nadie** y `_request()` retornó sin lanzar.
* **Módulo:** Un plano, sin I/O propio, **y que nunca lanza**. `resolve_candidates()` (antes de la TUI) y `resolve_typed_reviewers()` (en el momento del attach); `match_candidate()` casa exacto y normalizado por login, nombre o correo.
* **Escalera de Resolución:** handle ya conocido (sin petición) → casamiento exacto con una persona sugerida → lookup por correo → validación del login en el forge.
* **Lo que no resuelve nunca se envía:** Sale en `ResolutionOutcome.dropped` como `(value, reason_i18n)` y se descarta con aviso visible.
* **La Misma Escalera Sirve al Release:** `release_engine` reutiliza `get_commit_author_login` + `email_to_handle`.
* **🆕 Compañero Legado:** si un proveedor de terceros devuelve `None` (contrato antiguo), el log dice `(no read-back)` y el flujo sigue — un read-back ausente **nunca** se trata como fallo.
* **Pruebas:** `tests/test_reviewer_resolution.py` (18) + ampliaciones en `test_github_provider.py` (86), `test_pr_publish_app.py` (59), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) y `test_main_suggest_reviewers.py` (8).

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
* **Pruebas:** 7 archivos en `tests/badge/` (**83 escenarios**), todos en verde desde la ventana v0.0.20.
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

### **31. Escaneo de Secretos Integrado (`src/security_ruleset.py`) 🆕 Cobertura Ampliada**

* **Qué Es:** Siete reglas que corren en **cada** invocación del linter, fusionadas al final de `load_linter_rules()` después de las reglas del proyecto y de los plugins.
* **Las Siete Reglas:** cinco `error` bloqueantes — AWS access key ID, token de GitHub, token de Slack, clave de API de Google y clave privada — y dos `warning`: URL de conexión a base de datos con credenciales y asignación genérica de credencial, esta última tras un filtro de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **✅ Las Tres Brechas Cerradas:** la regla genérica ahora detecta **asignaciones sin comillas** (`.env`/YAML `API_KEY=abc123`), los prefijos **`ASIA…`**, **`github_pat_…`** y **`xoxc-`/`xoxd-`** entraron en el catálogo, y hay **frontera a la izquierda** en el nombre de la clave — `bypassword` ya no coincide. Las expresiones de runtime (`os.getenv(...)`, `process.env.X`) también dejaron de generar falsos positivos.
* **Por qué en el Paquete y No en la Plantilla:** una puerta de secretos debe comportarse **igual** en todas las máquinas y en todo CI.
* **Configuración:** `GITPR_LINTER_SECURITY` (predeterminado `true`; opt-out fail-open) y `GITPR_LINTER_SECURITY_DISABLED_RULES` (separadas por `;`).
* **Pruebas:** `tests/test_security_ruleset.py` (**77 escenarios**, eran 61) — matriz de regex, placeholders, nivel, comodín, fusión y traducciones; la invariante "la alerta nunca repite el valor" sigue vigente.
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
* **`tests/conftest.py` Hermético:** Fija `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` y `LANG_VERSION` en la versión del código, más `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true` y 🆕 `GITPR_SKIP_PROJECT_CONFIG_SEED=true` (la suite nunca escribe en un checkout de desarrollador).
* **El Orden Importa:** `src.updater` se importa **antes** de que se definan las variables de idioma.
* **Estabilidad de las Pruebas de Worker:** `test_config_app.py` y `test_metrics.py` esperan `workers.wait_for_complete()` antes de asertar.
* **Dependencia del Workflow:** El runner limpio no tiene `~/.gitpr`, y `test_demo_isolation.py` aserta que el perfil existe — el job crea explícitamente el directorio y un `.env` vacío.
* **🆕 En esta ventana la suite ganó 266 escenarios y 12 módulos:** `tests/issue_autoclose/` (5 archivos, **120 escenarios**), `test_project_config.py` (41), `test_pr_base_app.py` (12), `test_config_issue_autoclose.py` (10), `test_cli_help.py` (9), `test_project_config_cli.py` (7), `tests/unit/test_version_consistency.py` (5), `tests/integration/test_baseline_exit_code_contract.py` (8), además del crecimiento de `test_mcp_server.py` (117), `test_security_ruleset.py` (77), `test_github_provider.py` (86), `test_config_app.py` (88), `test_pr_publish_app.py` (59), `test_config_schema.py` (46) y `test_i18n.py` (22). **Total: 3276 escenarios en 153 archivos.**
* **Workflow de la Extensión:** `.github/workflows/extension.yml` corre `npm install`, compila, prueba y empaqueta el VSIX; en tags `ext-v*` publica en el VS Code Marketplace y Open VSX, saltando cada paso cuando `VSCE_PAT`/`OVSX_PAT` está vacío.
* **🆕 Workflow de Release:** `release.yml` corre pruebas, verifica la consistencia tag ↔ `src.updater.__version__`, construye sdist/wheel, valida con `twine check --strict`, publica por Trusted Publishing (OIDC) y crea el GitHub Release. El SSOT de versión también tiene su propia prueba (`tests/unit/test_version_consistency.py`).

### **36. Ledger de Telemetría SQLite (`src/ledger.py`, `src/infrastructure/git/identity.py`)**

* **El Problema No Era el Formato, Era la Unión:** los eventos se escribían como archivos JSON por comando, desde un **hilo daemon**, y luego se religaban a la caché de IA por un emparejamiento heurístico de `(repo, branch, action, minute)`.
* **La Escritura Es Síncrona, y Esa Es la Decisión:** `log_command_metric()` resuelve el contexto git **una vez por proceso** y escribe la fila de inmediato.
* **Schema Versionado:** `SCHEMA_VERSION` + `PRAGMA user_version`, claves UUID, WAL + `synchronous=FULL`, y `LedgerVersionError` para rechazar un bundle más nuevo **antes** del `ATTACH`.
* **Invariante "Nunca Nace Vacío":** `ensure_ledger()` solo crea la base de datos **junto** con la absorción de los JSON legados.
* **Una Ventana, Un Significado:** `--since`/`--until`/`--days` estrechan **todas** las superficies de lectura; la sección de ciclo asume los últimos 30 días y **nombra la ventana**.
* **Cada Métrica Se Calcula Una Vez y Se Renderiza Una Vez:** los pares `(style, text)` hacen que la CLI y el dashboard de Textual cuenten la **misma** historia.
* **Costo con Tabla Embebida y Override:** precios de lista de los cuatro modelos, sobreescritos por `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Ambas tasas son obligatorias**.
* **`modules` Normalizado:** las rutas pasan a dos segmentos (`src/fix`), `(root)` y `(no module)`.
* **Transporte por Bundle:** `bundle` escribe la ventana en un `.db` autónomo; `merge` lo anexa e inserta por UUID.
* **`cache_backfill` Se Queda Fuera:** las filas reconstruidas desde la caché se excluyen de los agregados de ejecución.
* **🆕 `issue_autoclose` Entra en el Ledger:** una línea por decisión, con `asked`, `accepted` y `skipped_reason`; la doc de métricas lista el comando junto a los demás.
* **Identidad Git sin Forge (`identity.py`):** etiqueta del repo, rama y autor en una **única** llamada al subproceso.
* **La Sección de Ciclo Es la Única que el Ledger No Responde:** lista PRs fusionados vía `ScmProvider.list_pull_requests`; cada fallo degrada a una sola línea — `No leído: <motivo>`.
* **Presentación Completa:** grupo `gitpr metrics` con 8 subcomandos, un dashboard que lee el ledger en un worker thread y el asistente de migración de tres opciones.
* **Pruebas:** `tests/test_metrics.py` (**164 escenarios**).

### **37. Subcomando `gitpr mentor` y la Flag `--mentor` — Mentor Mode (`src/domain/mentor/`)**

* **Qué Es:** Un review dice *qué* está mal y casi nunca *por qué importa*. Mentor Mode enriquece los hallazgos con **qué está pasando**, **por qué importa**, una **analogía** cotidiana y los **conceptos que estudiar**.
* **Capa de Dominio Pura (`mentor_explanation_builder.py`):** sin I/O, convierte el payload de la IA en un `MentorExplanation` validado e impone **honestidad epistémica**: sin evidencia suficiente, la explicación **lo dice**; las URLs alucinadas se **eliminan**; los IDs desconocidos se **rechazan**.
* **Capa de Aplicación (`generate_mentor_explanation.py`):** recolecta candidatos, **lotes de como máximo 10 hallazgos**, ordenados por severidad, reutilizando la caché MD5 por hash de prompt.
* **Dos Puntos de Entrada:** `gitpr mentor [--finding <id>] [--provider <name>]` y la flag `--mentor`, que añade la sección al review normal.
* **Opt-In Estricto:** `GITPR_REVIEW_MENTOR_MODE` (predeterminado `false`) y `GITPR_MENTOR_INCLUDE_ANALOGY` (predeterminado `true`), editables en la TUI de configuración.
* **Skill y Registry:** `.gitpr.mentor.md` registrado en `SKILL_FILES_BY_TYPE`, en `mcp_server.SKILL_FILES` y en `SKILL_LABELS`, con plantillas en **5 idiomas**.
* **✅ Aislamiento de Prueba Corregido:** `@patch("src.core.get_git_diff")` pasó a `@patch("src.main.get_git_diff")` — el nombre se importa por valor en `src/main.py`, así que el patch antiguo no alcanzaba el código bajo prueba. Los **dos fallos heredados más antiguos de la suite** se cerraron.
* **Pruebas:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) y `tests/test_mentor_cli.py` (4) — **todos en verde**.
* **Artefactos:** `docs/mentor-mode.*.md` en **5 idiomas**, ADR-009, `glossary-gitpr-mentor.md`, spec e informes de tarea.

### **38. Subcomando `gitpr risk` — Puntuación de Riesgo Determinista (`src/domain/risk/`)**

* **Qué es:** Una puntuación **0–100** con nivel `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculada **sin IA** a partir de señales locales.
* **Capa de Dominio Pura (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums y dataclasses; pesos, umbrales, patrones de ruta crítica, glob matching y detección de pruebas/no ejecutables cargados desde YAML; cálculo por archivo con **clamp 0–100** y agregación 50/30/20.
* **La Fórmula Se Declara, No Se Aprende:** **50%** el archivo de mayor riesgo, **30%** el promedio ponderado por líneas y **20%** la evidencia crítica.
* **Adaptadores de Infraestructura:** `risk_history_reader.py` lee `git log` y extrae commits, bug fixes y reverts dentro de una ventana de **90 días**; `test_matcher.py` casa archivos de prueba ↔ producción por convención.
* **Caso de Uso (`calculate_risk.py`):** correlaciona secciones del diff, hallazgos, emparejamiento de pruebas e historial en `FileRisk`/`PullRequestRisk`, **degradando con advertencia** cuando una señal no está disponible.
* **🆕 Path Tolerante:** la agregación de `check` aceptaba solo `path` y silenciaba las entradas con `file_path`, produciendo `high_risk_files` y `file_risk_map` con claves vacías. Ahora cualquiera de los dos atributos sirve — el informe de riesgo no degrada en silencio.
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
* **🆕 El Contrato Se Volvió Prueba:** `tests/integration/test_baseline_exit_code_contract.py` (8 escenarios) fija los dos códigos — el flujo que no logra aplicar el baseline nunca se disfraza de ejecución limpia.
* **Alcance:** CLI (6 subcomandos, todos con `--format json`), MCP (`get_baseline_status` + `baseline://summary`), review remoto de PR y el flujo de lint.
* **La Refactorización que la Funcionalidad Exigió:** `NormalizedFinding` pasó a tipo canónico en `src/domain/finding/`.
* **Pruebas:** `tests/domain/baseline/` (5 archivos, **126**), `tests/application/use_cases/` (5 archivos, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) y `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34) + 🆕 `tests/integration/test_baseline_exit_code_contract.py` (8).
* **Artefactos:** `docs/baseline-suppressions.*.md` en **5 idiomas**, ADR-012, `glossary-baseline.md`, spec/plan/survey e informe de tarea.

### **41. Subcomando `gitpr check` — Puerta de CI Unificada (`src/domain/check/`, `src/infrastructure/ci/`)**

* **Qué es:** Un **punto de entrada único** para pipelines de CI que orquesta análisis estático, puentes SAST, escaneo de secretos, review por IA opcional, puntuación de riesgo y clasificación por baseline, y emite **un artefacto estandarizado** con exit code determinista.
* **El Orden del Pipeline Es Contrato:** (1) resolución de la policy — inválida sale con `2`; (2) extracción del diff con smart excludes; (3) linter regex + secretos + puentes externos; (4) puntuación de riesgo preliminar; (5) **review por IA opcional**; (6) clasificación por baseline; (7) evaluación del umbral `--fail-on`; (8) generación del artefacto con **enmascarado de secretos**.
* **Capa de Dominio (`src/domain/check/`):** `check_types.py` (severidad normalizada en tres niveles con alias de la especificación); `check_config.py` (precedencia **CLI > env > defaults**); `check_output.py` (despacho por formato y escritura atómica).
* **Capa de Infraestructura (`src/infrastructure/ci/`):** `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (testsuites con `<failure>` y `<system-out>`) y `masking.py`.
* **Caso de Uso (`run_check.py`):** `resolve_diff_base()` (explícito → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), ejecución del linter y de los puentes, puntuación de riesgo, review opcional, baseline y envelope final. Stdout emite **solo el artefacto**. 🆕 La agregación de riesgo acepta `path` **o** `file_path`.
* **Exit Codes:** `0` éxito; `1` hallazgo contable en el umbral; `2` error de ejecución — ahora con el contrato del baseline cubierto por prueba.
* **CLI:** `gitpr check` con 8 opciones y 9 variables `GITPR_CHECK_*`.
* **GitHub Action Reescrita:** `action.yml` pasó a un único paso `gitpr check`. **Ruptura declarada** para workflows que dependían de `--linter` + `--fullreview`.
* **Pruebas:** 45 escenarios — `test_run_check.py` (8), `test_check_types.py` (8), `test_check_config.py` (3), `test_check_output.py` (3), `test_masking.py` (6), `test_sarif_builder.py` (2), `test_junit_builder.py` (2), `test_check_cli.py` (5) y 🆕 `test_baseline_exit_code_contract.py` (8).
* **MCP:** la herramienta `run_check` devuelve el envelope JSON del mismo pipeline, con `no_ia` por defecto en `true`.
* **Artefactos:** `docs/check-ci.*.md` en **5 idiomas**, ADR-013, `glossary-check-ci.md`, spec, `plansfacts` y survey.

### **42. Extensión de Editor — VS Code y Cursor (`extensions/vscode/`)**

* **Qué es:** Una extensión **cliente fino** que lleva los flujos de GitPR al editor sin reimplementar nada: inicia `gitpr-mcp` por stdio y llama a las herramientas existentes. Claves, tokens, prompts y caché se quedan en `~/.gitpr/`, cifrados con Fernet.
* **Arquitectura:** `GitPrMcpClient` sobre `@modelcontextprotocol/sdk` + un `SessionManager` con **una sesión por carpeta** (multi-root) + verificación de contrato al conectar: las 7 herramientas obligatorias deben existir; si no, `MissingMcpToolsError` indica `pip install --upgrade gitpr-cli`.
* **8 Comandos:** `gitpr.commit`, `gitpr.review`, `gitpr.fullReview`, `gitpr.reviewMentor`, `gitpr.generatePr`, `gitpr.generatePrExplain`, `gitpr.publishPr` y `gitpr.check`.
* **Publicación en Dos Pasos:** `generatePr` abre un documento Markdown editable; `publishPr` pide confirmación modal y maneja `success`, `needs_push`, `already_exists`, `auth_required` y `error`.
* **Diagnósticos en el Panel Problems:** `run_check` se convierte en una `DiagnosticCollection("gitpr")`.
* **Enmascarado en Doble Profundidad:** `masking.ts` refleja `src/infrastructure/ci/masking.py`.
* **Localización de la Extensión:** bundles en **en, es, fr y pt-br** (4 idiomas) — la UI de GitPR mantiene 5.
* **CI/CD de la Extensión:** `.github/workflows/extension.yml` instala, compila, prueba y empaqueta el VSIX; en tags `ext-v*` publica en el Marketplace y Open VSX.
* **Lo que la Extensión No Hace:** no commitea, no hace push y no publica sin confirmación. El comando `gitpr.commit` de la extensión llama a la herramienta MCP `generate_commit_message` — **no** abre la TUI, que es exclusiva del terminal. 🆕 El cierre automático de issue también es **exclusivo de la CLI**: la extensión no tiene camino para registrar el issue creado.
* **Pruebas:** su propia suite Mocha (7 escenarios, incluido stdio real) + `tests/test_mcp_server.py` (117 escenarios).
* **Artefactos:** `docs/editor-extension.*.md` en **5 idiomas**, ADR-014, `glossary-editor-extension.md`, specs, `plansfacts` y el informe Gemini.

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
* **Configuración:** `GITPR_SKIP_UNSTAGED_CHECK` (salta el Stage Selector) y `GITPR_AUTO_STAGE` (prepara todo y sigue), ambos ya existentes en `DEFAULT_CONFIG` y en el schema de la TUI de configuración.
* **Detalles de Textual:** `ENABLE_COMMAND_PALETTE = False`; `on_button_pressed` trata `#btn_close`; `_on_commit_progress_dismissed` lee `_commit_success` en lugar de asumir éxito cuando el modal se cierra.
* **⚠️ Ruptura de Comportamiento (aún abierta):** sin `--hook`, `gitpr -c` **siempre** abre la TUI — no hay guardia de TTY en el enrutamiento. Un script o job de CI que contaba con el commit silencioso necesita pasar a usar `--hook`; `GITPR_SKIP_UNSTAGED_CHECK`/`--no-unstaged-check` solo saltan la etapa 1.
* **Pruebas:** `tests/test_commit_app.py` (**14 escenarios**) — cada transición de estado y callback, más un flujo headless completo con el pilot de `run_test()`.
* **Dominio:** `CONTEXT.md` mantiene los términos **Stage selector** y **Commit flow**.
* **Artefactos:** `docs/commit-message-ia.*.md` en los **5 idiomas** y el informe de tarea de Gemini.

### **44. Cierre Automático de Issues (`src/issue_autoclose/`) 🆕**

* **Lo que era:** el issue creado con `gitpr -is` quedaba huérfano — el número existía solo dentro de la TUI de issue y se perdía en el instante en que esta se cerraba. Todo PR tenía que vincularse a mano, y el merge no cerraba nada.
* **Paquete de 5 archivos:** `issue_registry.py` (235 líneas), `register_created_issue.py` (80), `resolve_close_issue.py` (226), `insert_close_line.py` (46) y `__init__.py`. Cada uno es una responsabilidad: persistir, registrar en la creación, decidir en la siguiente ejecución e insertar en el cuerpo.
* **Registro Local (`IssueRegistry`):** `.gitpr/conf/github.yml` con `schema_version: 1` y un único `last_issue` (last write wins, coherente con un flujo de un PR por vez). Escritura **atómica** mediante archivo hermano + `os.replace`; `load()` devuelve `error` en vez de lanzar (un archivo corrupto no bloquea la creación), y `matches_repo()` compara owner/repo sin distinguir mayúsculas para nunca cerrar el issue del repositorio equivocado.
* **Auto-Ignorando con Precisión:** `record()` corre `git check-ignore` antes de la primera escritura y añade exactamente una línea `.gitpr/conf/github.yml` al `.gitignore` cuando el archivo no está cubierto — `.gitpr/conf/` **no** se ignora entero, porque `gitpr.config.yml` se versiona a propósito ([ADR-015](docs/plans/ADR-015-project-local-pr-base.md)).
* **Registro en la Creación:** `register_created_issue()` solo escribe en **GitHub** y absorbe los fallos de bookkeeping — un problema de escritura local nunca convierte una creación exitosa en error.
* **Decisión (`resolve_close_issue`):** filtra pronto por flags, configuración y forge no-GitHub; solo después toca disco/red. Verifica que el issue siga **abierto** y **no sea un PR**, borra registros muertos (404, cerrado, número de PR) y pregunta **una vez**, avisando cuando el PR apunta a una rama distinta de la default.
* **Línea Idempotente (`insert_close_line`):** detecta cualquier keyword existente (`close/closes/closed`, `fix/fixes/fixed`, `resolve/resolves/resolved`, con prefijo `owner/repo#N` opcional, case-insensitive) y nunca duplica la línea.
* **CLI:** `--close-issue` y `--no-close-issue` — mutuamente excluyentes, rechazadas antes de cualquier prompt o escritura; `-h` vence al conflicto. Entradas en `HELP_MAP`.
* **Conexión en el Flujo de PR:** `_prepare_close_issue` corre **antes** del Stage Selector y reutiliza el contexto SCM resuelto; `_publish_pr_directly` devuelve si la forge realmente publicó; `PrPublishApp.body_published` distingue crear/actualizar de solo abrir un PR existente. El registro solo se limpia después de una publicación real (ADR-007).
* **Contrato SCM:** `IssueInfo` (state en el vocabulario de la forge + `is_pull_request`), `get_issue()`, `get_default_branch()` y `closing_line()` son **defaults concretos** de la ABC que lanzan `ScmNotSupportedError`; solo GitHub implementa. Las otras forges degradan a "funcionalidad inactiva".
* **Configuración:** `GITPR_ISSUE_AUTOCLOSE` (default `true`, opt-out) y `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` (default `5`, int positivo), expuestas en la categoría `issue` de la TUI; se leen como defaults read-only y **no** se siembran en `~/.gitpr/.env`.
* **Telemetría:** una línea `issue_autoclose` por decisión (`asked`, `accepted`, `skipped_reason`).
* **Pruebas:** 5 archivos en `tests/issue_autoclose/` (**120 escenarios**) — round-trip del registro, schema, corrupción, fallo de escritura atómica, `.gitignore`, decisión, idempotencia de la línea, flags, conexión y semántica de `body_published`.
* **Artefactos:** `docs/specs/issue-autoclose/` (**12 archivos**: spec, entrevista, glosario, prompt y ADRs 001–007), `docs/issue-tui-help.*`, `docs/config-tui.*` y `docs/metricas-telemetria.*` en los **5 idiomas**, README en los 5 idiomas y 12 informes de tarea en `docs/claude-code/reports/feat/issue-autoclose/`.

### **45. Base del PR por Proyecto (`src/project_config.py`, `src/ui/pr_base_app.py`) 🆕**

* **Lo que era:** la rama de destino del PR vivía solo en el `~/.gitpr/.env` de la máquina (`PR_DEFAULT_BASE`) o se adivinaba por `origin/HEAD`. Un repositorio que apunta a `develop` dependía de que cada desarrollador configurara la misma variable, y el error (PR abierto contra la rama equivocada) solo aparecía en la forge.
* **Lo que es:** `.gitpr/conf/gitpr.config.yml` — archivo **versionado con el proyecto** que carga la base del PR, editado por la TUI de configuración y validado antes de cualquier commit/push. Un resolvedor único sirve a la CLI y al MCP, así que todos los puntos de entrada coinciden en la precedencia y en las reglas de fallo.
* **Resolvedor (`src/project_config.py`, 426 líneas):** `resolve_pull_request_base()` — `--base` → archivo del proyecto → `PR_DEFAULT_BASE` → detección `origin/HEAD` → `main`. Lectura/escritura YAML preserva claves ajenas; seeding idempotente; listado de ramas; detección de la rama actual; verificación contra el remoto.
* **Fallo Antes de que Nazca el PR:** `BaseBranchError`/`CurrentBranchError`; la base debe existir en `origin/<branch>` y diferir de la rama actual — sin `origin` o con ref ausente, el flujo se detiene con una pista accionable en vez de abrir un PR condenado.
* **TUI de Configuración:** nueva categoría `project` con atributo `store` (`env` vs `project`), `KIND_BRANCH` y tres campos: ruta del archivo (read-only), rama destino (editable) y base efectiva con el origen (read-only). `ConfigApp` escribe primero los valores de proyecto, soporta eliminación al vaciar, rechaza la rama actual y nunca sobrescribe YAML ilegible; la línea de la base efectiva se actualiza in-place.
* **Modal de Reparación (`src/ui/pr_base_app.py`, 98 líneas):** cuando el destino es la propia rama actual y la ejecución es interactiva, un modal enfocado pide la rama sustituta y la escribe en el archivo del proyecto. Probado en modo headless (`tests/test_pr_base_app.py`, 12 escenarios).
* **CLI:** `--base` pasa por `_resolve_pr_target()`; el archivo se siembra en toda invocación dentro de un repositorio (excepto ayuda), anunciado solo en el camino interactivo de la raíz; `GITPR_SKIP_PROJECT_CONFIG_SEED` desactiva el seed (suite de pruebas).
* **MCP:** `publish_pull_request` usa el resolvedor compartido, devuelve envelope de error para `BaseBranchError` y expone `warnings` en el payload y en el catálogo.
* **Efecto Colateral Declarado:** ejecutar `gitpr` en un repositorio pasa a crear el archivo en la primera ejecución. Un archivo existente nunca se sobrescribe; si el `.gitignore` excluye la ruta, un aviso explica que el valor no se versionará.
* **Pruebas:** `tests/test_project_config.py` (41), `tests/test_project_config_cli.py` (7), `tests/test_pr_base_app.py` (12) + ampliaciones en `test_config_app.py` (88), `test_config_schema.py` (46), `test_config_store.py` (22), `test_mcp_server.py` (117) y `test_core.py` (49).
* **Artefactos:** [ADR-015](docs/plans/ADR-015-project-local-pr-base.md), `docs/plans/develop_natan/20261030_project_pr_base_plansfacts.md`, `docs/survey/20261030_project_pr_base_surveyfacts.md`, y `docs/config-tui.*`/`docs/install-wizard.*`/`docs/pr-descricao-padrao.*`/`docs/pull-request-publication.*` en los 5 idiomas.

---

## **📊 Pruebas y Calidad**

| Archivo de Prueba | Escenarios | Enfoque |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por rango de líneas en un archivo |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidad, commits, duración |
| `tests/test_baseline_cli.py` | 54 | Grupo `baseline`: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Constructor de changelog: secciones, títulos traducibles, contribuidores, enlaces y fechas |
| `tests/test_chat_backend.py` | 19 | Memoria de chat, persistencia, comandos slash |
| `tests/test_check_cli.py` | 5 | CLI de `check`: opciones, formatos, exit codes |
| `tests/test_cli_help.py` | 9 🆕 | `-h` antes del subcomando: ayuda renderizada, TUI nunca abierta |
| `tests/test_commit_app.py` | 14 | `CommitApp`: mount, Stage Selector, auto-stage, skip, cancelar, confirmar, éxito/fallo del commit, auto-exit y flujo headless |
| `tests/test_commit_classifier.py` | 30 | Clasificación Conventional Commits + `pr_number_from_merge()` en los 4 forges |
| `tests/test_config_app.py` | 88 | TUI de configuración: montaje, cambio de categoría, dirty tracking, F2 bloqueado, Ctrl+R, búsqueda, secretos, store `project` |
| `tests/test_config_cli.py` | 9 | Registro del subcomando `config`, `-h`, lazy import, stdout limpio |
| `tests/test_config_issue_autoclose.py` | 10 🆕 | Getters de configuración y campos del schema de cierre de issue |
| `tests/test_config_schema.py` | 46 | Cobertura de `DEFAULT_CONFIG`, sin duplicados, categorías/tipos, `advanced` solo en Advanced, sección de skills |
| `tests/test_config_store.py` | 22 | Round-trip sobre el `.env` temporal, comentarios y orden preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuración de revisores sugeridos (claves y defaults) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, plantillas, `validate_ai_key()` con un SDK mockeado (401 vs. red vs. ollama) |
| `tests/test_core.py` | 49 | Flujos principales, git diff, PR generation, timing, staging, coautoría, idioma de los hooks, base del PR |
| `tests/test_diff_failure_cli.py` | 3 | Exit code 1 en fallo de git, 0 en diff vacío, fail-open en `--hook` |
| `tests/test_diff_parser.py` | 26 | Parser de diff por líneas/hunks + `summarize_patch()` y `split_patch_sections()` |
| `tests/test_explain_command.py` | 4 | CLI de `explain`: éxito, clave ausente, diff vacío, ayuda contextual |
| `tests/test_external_linters.py` | 33 | Puente Checkstyle: parser XML, subprocess, intersección con el diff, informe |
| `tests/test_full_diff_encoding.py` | 1 | Repositorio real con un archivo Latin-1: el byte `0xFA` pasa a `U+FFFD` y el archivo aparece en el diff |
| `tests/test_i18n.py` | 22 ✅ | Paridad entre idiomas, claves faltantes/huérfanas, identidad — **todos en verde** |
| `tests/test_install_wizard.py` | 3 | Wizard de instalación interactivo |
| `tests/test_issue_engine.py` | 4 | Borrador de issue estructurado |
| `tests/test_linter_metrics.py` | 4 | Métricas del linter: errores, warnings, duración |
| `tests/test_linter_presets.py` | 5 | Presets del linter: resolución y re-descarga forzada |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` en la CLI y en la ayuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompts MCP y fallback de idioma |
| `tests/test_mcp_server.py` | 117 | Herramientas MCP (18), recursos (19), anotaciones, patching, CLI directa, offload, contrato de la extensión, base del PR |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real vía subprocess + JSON-RPC stdio |
| `tests/test_mentor_cli.py` | 4 ✅ | CLI de `mentor` y opt-in de `--mentor` — **patches en `src.main`, todos en verde** |
| `tests/test_metrics.py` | 164 | **Ledger**: escritura síncrona, export idempotente, bundle/merge, versionado, migración, renderers, costo, calidad, módulos, proveedores, ciclo |
| `tests/test_net_timeouts.py` | 12 | Timeouts de red/IA — alineados con el predeterminado real de 180s |
| `tests/test_plugins.py` | 17 | Descubrimiento de plugins, fusión de reglas del linter, prompts MCP |
| `tests/test_policy_cli.py` | 26 | Grupo `policy`: list/show/validate/use/off/install/init, confirmación y lockfile |
| `tests/test_pr_base_app.py` | 12 🆕 | Modal de cambio de la base del PR: montaje headless, elección y cancelación |
| `tests/test_pr_publish_app.py` | 59 | TUI del PR Publisher: pantallas, flujos, revisores sugeridos, `NoticeScreen`, modal de merge, `body_published` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de error del linter: abortar, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save y payload JSON |
| `tests/test_project_config.py` | 41 🆕 | `project_config`: path, YAML, seed, resolvedor de base, validación contra el remoto |
| `tests/test_project_config_cli.py` | 7 🆕 | Conexión de la base del proyecto en la CLI |
| `tests/test_release_cli.py` | 4 | CLI de release: opciones, ayuda con epílogo documentado |
| `tests/test_release_engine.py` | 53 | **Cutoff anclado en el changelog, `_drop_released()`, enlaces, mapa de PRs por merge commit**, publicación |
| `tests/test_reviewer_resolution.py` | 18 | Escalera de resolución, rechazo de nombre parcial, dedup, tolerancia a fallos |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugerencia de revisores (ranking, exclusión, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | CLI de `risk`: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 77 | Matriz del ruleset integrado: regex, placeholders, nivel, comodín, fusión, traducciones |
| `tests/test_skill_command.py` | 10 | Descarga y validación de plantillas de skill |
| `tests/test_skill_context.py` | 20 | `get_skill_context()` con `quiet=True`, fallbacks, guardia del artefacto de PR |
| `tests/test_skill_templates.py` | 99 | Barrido parametrizado de **todas las plantillas publicadas** contra referencias embebidas |
| `tests/test_smart_excludes.py` | 15 | Filtro smart pathspec y re-descarga forzada |
| `tests/test_suggest_reviewers.py` | 17 | Revisores sugeridos en el flujo de PR (integración, hint `no_login`) |
| `tests/test_tests_command.py` | 4 | Grupo `tests` y CLI de `generate`, `--finding` y epilog con enlace |
| `tests/test_thinking_words.py` | 5 | Carga, parsing con separador `;` y recarga forzada |
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
| `tests/badge/test_badge_paths.py` | 10 ✅ | Punto único de inyección en ambos publishers — sigue en verde |
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
| `tests/integration/test_baseline_exit_code_contract.py` | 8 🆕 | Exit code 1 para baseline inaplicable y 2 en `check` |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Efecto real del pack sobre review, lint y riesgo (con un guard de red autouse) |
| `tests/issue_autoclose/test_close_issue_wiring.py` | 26 🆕 | Conexión en el flujo de PR: flags, prompt, publicación real, limpieza del registro |
| `tests/issue_autoclose/test_insert_close_line.py` | 19 🆕 | Inserción idempotente y reconocimiento de keywords de cierre |
| `tests/issue_autoclose/test_issue_registry.py` | 23 🆕 | Registro local: round-trip, atomicidad, corrupción, `.gitignore`, repo match |
| `tests/issue_autoclose/test_register_created_issue.py` | 12 🆕 | Registro poscreación: solo GitHub, fallos absorbidos, primera escritura |
| `tests/issue_autoclose/test_resolve_close_issue.py` | 40 🆕 | Árbol de decisión: gates, issue abierto, PR, registros muertos, confirmación |
| `tests/review/test_diff_normalizer.py` | 20 | Saltos de línea, validación de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: procedencia, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orquestación: puertas, PR, diff, linter, comentario opcional |
| `tests/review/test_review_pr_cli.py` | 25 | CLI de `review-pr`: opciones, rechazos antes de la IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 48 | Proveedor de Azure DevOps: org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 44 | Proveedor de Bitbucket: Basic auth, workspace, PRs |
| `tests/scm/test_contract.py` | 44 | Contrato de `ScmProvider`: firmas, dataclasses, errores, defaults de issue |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim deprecado `github_api` → delega en el proveedor |
| `tests/scm/test_github_provider.py` | 86 | Proveedor de GitHub: REST, headers, PRs, issues, releases, read-back de revisores, `get_issue`/`get_default_branch` |
| `tests/scm/test_gitlab_provider.py` | 52 | Proveedor de GitLab: API v4, namespace, headers sintetizados y `overflow` |
| `tests/scm/test_init_wizard.py` | 12 | Wizard de `--init`: detección de forge, validación, persistencia |
| `tests/scm/test_release_publish.py` | 8 | Publicación de release por forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 | El único módulo que muta: staging selectivo, un commit por grupo |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unidades → grupos → pre-validación de conflictos → mensajes |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, presupuesto/recorte, llamada de IA, validación de respuesta |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()`: texto ↔ modelo, puro |
| `tests/split/test_selective_stager.py` | 11 | Staging de un subconjunto, verificación contra HEAD, índice limpio |
| `tests/split/test_split_cli.py` | 31 | CLI de split: opciones, dry run predeterminado, `--apply` |
| `tests/unit/test_version_consistency.py` | 5 🆕 | SSOT de la versión: `pyproject.toml`, `CLAUDE.md` y `GEMINI.md` contra `src.updater.__version__` |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura i18n (scaffold; nunca se ejecuta) |

**Total:** **3276 escenarios recolectados en 153 módulos de prueba** (59 en la raíz + 12 en `tests/application/use_cases/` + 10 en `tests/fix/` + 9 en `tests/scm/` + 7 en `tests/badge/` + 6 en `tests/split/` + 6 en `tests/demo/` + 5 en `tests/domain/baseline/` + 5 en `tests/issue_autoclose/` + 4 en `tests/domain/policy/` + 4 en `tests/review/` + 4 en `tests/infrastructure/linter/external/` + 3 en `tests/domain/check/` + 3 en `tests/infrastructure/ci/` + 3 en `tests/integration/` + 2 en `tests/domain/risk/` + 2 en `tests/domain/tests_generation/` + 2 en `tests/infrastructure/git/` + 2 en `tests/domain/linter/` + 1 en `tests/domain/finding/` + 1 en `tests/domain/mentor/` + 1 en `tests/domain/pr/` + 1 en `tests/infrastructure/baseline/` + 1 en `tests/unit/`; **+266 escenarios, +12 archivos** desde el informe anterior).

**Notas de calidad de esta versión:**
- **⚠️ Ejecución completa en esta máquina — el escenario que se cuelga:** sin exclusión, el run se queda atascado en **~33%** (≈1085 escenarios) y no termina ni tras 16 minutos. La causa es `tests/integration/test_baseline_effect_on_review_linter_risk.py::TestAnUnusableBaseline::test_a_flow_that_never_enforced_it_is_untouched`: llama a `gitpr -c --no-unstaged-check` por el `CliRunner`, y la rama `commit` de [main.py:1625-1638](src/main.py#L1625-L1638) **abre `CommitApp` sin comprobar el TTY**, así que `app.run()` espera un terminal que no existe. Aislado, el archivo corre **24 de los 34 escenarios** y se detiene en el 25.º; el escenario solo no retorna (proceso muerto tras 45 s). **Es la primera evidencia concreta de la deuda de guardia de TTY** — no es un fallo de aserción, es la TUI bloqueando un proceso no interactivo.
- **Ejecución completa con ese escenario deseleccionado:** **3272 passed, 1 failed, 2 skipped, 1 deselected y 227 subtests passed en 695,38 s (11min35s)**. La cuenta cuadra con los 3276 recolectados (3272 + 1 + 2 + 1). El único fallo es el ya conocido `test_headless_full_flow` (timeout de 5 s esperando el Confirm en `CommitMessageScreen`) — el intermitente descrito abajo.
- **Los módulos verificados aisladamente cierran en verde:** `tests/test_i18n.py` (**22 passed en 1,49 s**), los cuatro `tests/infrastructure/linter/external/` (**14 passed en 0,09 s**) y el conteo por archivo de las pruebas nuevas verificado con `--collect-only` (120 en `tests/issue_autoclose/`, 41 en `test_project_config.py`, 12 en `test_pr_base_app.py`, 9 en `test_cli_help.py`, 10 en `test_config_issue_autoclose.py`, 5 en `test_version_consistency.py`, 8 en `test_baseline_exit_code_contract.py`).
- **✅ Los 5 fallos heredados se cerraron:** los 3 de i18n (conteo/paridad, claves faltantes, huérfanas) y los 2 de `tests/test_mentor_cli.py` (patch en el módulo equivocado) ya no existen. `tests/test_i18n.py` corre **22 passed en 1,49 s** aisladamente, y el patch del mentor apunta a `src.main.get_git_diff`.
- **✅ La deuda de traducción se pagó, no se sorteó:** los seis diccionarios tienen **1698 claves**; no hubo cambio de expectativa de prueba, hubo traducción de verdad — incluyendo las 3 cadenas de la TUI de commit que estaban pendientes, el `argparse` del MCP y `ChatHelpScreen`.
- **⚠️ La prueba intermitente del commit sigue intermitente:** `test_headless_full_flow` volvió a fallar en este run — `AssertionError: timed out waiting for condition. Current screen: CommitMessageScreen()` tras 5 s esperando el botón Confirm. Ningún commit de esta ventana abordó el problema; hoy es el **único fallo** de la suite.
- **Los escenarios nuevos cubren exactamente lo que cambió:** el paquete `issue_autoclose` tiene una suite por archivo; `project_config` cubre path/YAML/seed/resolvedor; el contrato de exit code del baseline ganó prueba de integración; y el SSOT de versión tiene su propio guarda en `tests/unit/`.
- **`tests/conftest.py` sigue hermético:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false`, `LANG_VERSION` en la versión del código y 🆕 `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
- **Guard de red en dos lugares:** `tests/demo/conftest.py` y `tests/integration/conftest.py`.
- **Fixtures de git real:** `tests/fix/git_fixture.py`, `tests/split/git_fixture.py` y `tests/test_full_diff_encoding.py`.
- **La suite de la extensión es aparte:** 7 escenarios Mocha en `extensions/vscode/src/test/mcp_client.test.ts`, de los cuales 1 se integra de verdad con el servidor MCP Python por stdio; no cuenta para pytest.
- **La CI corre en Python 3.10 y 3.13** con `fail-fast: false`, y el release tiene su propio workflow con Trusted Publishing.

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n — deuda pagada:** **1698 claves** en **los seis** diccionarios (`es.json`, `es_es.json`, `fr.json`, `fr_fr.json`, `pt_br.json`, `pt_pt.json`). El código usa las mismas claves: **0 sin traducir** y **0 huérfanas**. Eran 1620 (×4)/1618 (×2), 11 faltantes y 2 huérfanas en el informe anterior. ✅
* **🆕 `__lang_version__` subió a v0.0.38 con contenido:** español y francés se sincronizaron con policy packs, baseline, mentor, risk, MCP, base del PR, configuración de proyecto y atajos del chat; el `argparse` del MCP y `ChatHelpScreen` pasaron por `__()`; la renovación OTA ahora entrega traducción nueva de verdad.
* **Fuentes de traducción en lockstep:** una clave nueva debe existir en el código (fuente), en `langs/pt_br.json` (**lista maestra**), en los diccionarios FR/ES de `scripts/sync_all_langs.py` y en los valores curados de `scripts/fix_mangled_i18n_keys.py`; la aserción `len(CLEAN_KEYS)` sigue en 49. 🆕 El extractor de claves se volvió **AST** en ambos lados (script y prueba), eliminando la divergencia de concatenación implícita.
* **Temas nuevos:** **ninguno** en esta ventana — los 50 temas canónicos se mantienen.
* **🆕 Temas actualizados en esta ventana:**
  - `docs/issue-tui-help.*` (**5 archivos**) — documenta el registro del issue creado, el aviso de cierre y las flags `--close-issue`/`--no-close-issue`.
  - `docs/config-tui.*` (**5 archivos**) — la categoría `project` (archivo versionado, base destino, base efectiva) y la categoría `issue` (`GITPR_ISSUE_AUTOCLOSE*`).
  - `docs/metricas-telemetria.*` (**5 archivos**) — el comando `issue_autoclose` y el `meta` con `asked`/`accepted`/`skipped_reason`.
  - `docs/review-pr.*` (**5 archivos ahora completos**) — PT-PT, ES y FR fueron traducidos; el tema dejó de ser parcial.
  - `docs/install-wizard.*`, `docs/pr-descricao-padrao.*` y `docs/pull-request-publication.*` (**5 archivos cada uno**) — seed y precedencia de la base del PR, y el comportamiento del MCP.
  - `docs/baseline-suppressions.*` y `docs/check-ci.*` (**5 archivos cada uno**) — contrato de exit code del baseline y agregación de riesgo por `path`/`file_path`.
  - `docs/suggested-reviewers.*` y `docs/scm-multiforge.*` (**5 archivos cada uno**) — read-back legado (`None`) y el contrato `list[str]`.
  - `docs/commit-message-ia.*` (**5 archivos**) — sin cambio de flujo, solo sincronización de traducción.
* **`CONTEXT.md` (actualizado):** el glosario de dominio pasó de **7 a 22 términos**. Nuevos: **PR target base**, **Project configuration file**, **Self-target**, **Release workflow**, **Trusted publishing**, **Diff base**, **Embedded security ruleset**, **Read-back**, **Unusable baseline**, **Package version**, **Language asset version**, **Hook script version**, **Issue registry**, **Closing line** y **Close issue decision**.
* **Documentación en 5 idiomas:** **50 temas canónicos** en `docs/` (sin cambios) — **48 con cobertura completa en los 5 idiomas** y **2 temas parciales** (`github-issue-prompt-com-gh`, `version-markers`). `review-pr` dejó de ser parcial.
* **Nuevos artefactos de especificación:** `docs/specs/issue-autoclose/` (**12 archivos** — spec, entrevista, glosario, prompt y ADRs 001–007) y 3 nuevas ADRs en `docs/adr/` (`0008` secret ruleset, `0009` reviewer read-back, `0014` package version SSOT).
* **Skills locales de Claude Code:** `.claude/skills/` con **29 skills** (conteo sin cambios).
* **Índice de memoria:** `.claude/memory/MEMORY.md` con **40 patrones** (conteo sin cambios).
* **Informes de tarea:** `docs/claude-code/reports/develop_natan/` (**116**; **+2** desde el informe anterior) + 🆕 `docs/claude-code/reports/feat/issue-autoclose/` (**12**) y `docs/gemini/reports/develop_natan/` (**31**; **+12**).
* **Informes de estado:** `docs/reports/` — este es el **22.º informe** (v0.0.22); el v0.0.21 nunca se sobrescribe.
* **Planes de desarrollo:** **152** archivos en `docs/plans/` (+4) + **12 archivos en `docs/gemini/plans/`** (+10) + **24 archivos en `docs/survey/`** (+3).

---

## **🔄 Pipeline de Distribución**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Actualización obligatoria con ventana deslizante:** la ejecución consulta PyPI al iniciar y **bloquea con código de salida 1** si hay una versión más nueva, imprimiendo `pip install --upgrade gitpr-cli`. La respuesta se cachea durante **1 hora** y `--update` **siempre** consulta la red. La comparación es PEP 440: un pre-release remoto no bloquea una instalación estable, y una versión ilegible cuenta como desconocida. La puerta cubre los subcomandos cuando hay un TTY; CI, pipes, hooks, MCP y `-h` siguen exentos.
3. **GitHub Releases:** producción automatizada por `release.yml` en tags `v*` — build, validación, Trusted Publishing y creación del release con artefactos.
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala vía pip) + `tests.yml` (matriz Python 3.10 y 3.13) + `extension.yml` (VSIX; publicación en el Marketplace/Open VSX en `ext-v*`) + 🆕 `release.yml` (PyPI + GitHub Release en `v*`). `action.yml` llama a un único paso `gitpr check` — **ruptura declarada** para quien use `--linter` + `--fullreview`.
5. **Servidor MCP:** entry point `gitpr-mcp` vía `pyproject.toml`
6. **Extensión de Editor:** paquete VSIX autónomo (`natanfiuza.gitpr`), con canales VS Code Marketplace y Open VSX; requiere Node.js 20+ solo para compilar, no para usar
7. **Plantillas e idiomas OTA:** `templates/` y `langs/*.json` servidos desde GitHub (main) — `__lang_version__` **v0.0.38** 🆕 refresca las copias locales en `~/.gitpr/langs/`; `__scripts_version__` **v0.0.4** refresca los hooks
8. **Versión derivada del código:** `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — `__version__` es la **única** fuente, ahora garantizada por [ADR-014](docs/adr/0014-canonical-package-version-ssot.md) y por `tests/unit/test_version_consistency.py`
9. **`package-data` importa:** los cuatro packs embebidos (`src/policy_packs/*`) viajan en el wheel
10. **⚠️ Estado del release — tagueado, pendiente de publicación:** `__version__` es **1.5.0**, la tag `v1.5.0` existe y la sección `[1.5.0]` está en el `CHANGELOG.md`, pero **PyPI todavía sirve 1.4.4**. El siguiente paso es ejecutar/confirmar `release.yml` para la tag — hasta entonces, `pip install --upgrade gitpr-cli` no entrega la 1.5.0.

---

## **📈 Evolución desde el Informe Anterior (v0.0.21)**

| Área | v0.0.21 (anterior) | v0.0.22 (actual) |
|------|-------------------|-----------------|
| **Versión de GitPR** | 1.4.4 — tagueada (`v1.4.4`) y **publicada en PyPI** | **1.5.0** — tagueada (`v1.5.0`), sección en el changelog, **PyPI todavía en 1.4.4** |
| **Versión de idioma** | v0.0.36 — bump sin cambio de diccionario | **v0.0.38** — diccionarios realmente modificados |
| **Versión de los scripts de hook** | v0.0.4 | v0.0.4 (sin cambios) |
| **Proveedores de IA** | Gemini + DeepSeek + Ollama | Sin cambios |
| **Idiomas** | 5 idiomas, 6 diccionarios | Sin cambios |
| **Subcomandos** | 15 | 15 (sin cambios) — 4 grupos (baseline 6, metrics 8, policy 7, tests 1) |
| **Flags de CLI** | 32 opciones en la raíz | **33** — 🆕 `--close-issue` y `--no-close-issue` |
| **Cierre de Issue 🆕** | — | Paquete `src/issue_autoclose/` (5 archivos): registro local, decisión, línea idempotente y conexión en el PR |
| **Base del PR 🆕** | `PR_DEFAULT_BASE` de la máquina o `origin/HEAD` | **`.gitpr/conf/gitpr.config.yml` versionado** + `src/project_config.py` + modal de reparación |
| **Capas** | 9 paquetes de dominio, 15 casos de uso | Sin cambios — las dos funcionalidades nuevas son orquestación/borde |
| **Herramientas MCP** | 18 herramientas / 19 recursos / 7 prompts | Sin cambios — `publish_pull_request` ganó base compartida y `warnings` |
| **Variables de entorno** | 58 en `DEFAULT_CONFIG` + precios/moneda + 9 `GITPR_CHECK_*` | 58 + precios/moneda + 9 `GITPR_CHECK_*` + 🆕 3 read-only (`GITPR_ISSUE_AUTOCLOSE`, `GITPR_ISSUE_AUTOCLOSE_TIMEOUT`, `GITPR_SKIP_PROJECT_CONFIG_SEED`) |
| **Esquema de configuración** | 80 `ConfigField` / 16 categorías | **85 / 17** — categorías `project` y campos de issue, atributo `store` |
| **i18n (claves por archivo)** | 1620 × 4 y 1618 × 2 — 1629 en el código, 11 sin traducir, 2 huérfanas | **1698 × 6 — 0 faltantes, 0 huérfanas** ✅ |
| **Puerta de actualización** | TTL deslizante de 1 h, `-u` siempre en línea, PEP 440, `bounded_urlopen`, semántica estricta, puerta de subcomando en TTY | Sin cambios en el código |
| **Linter** | Regex + secretos + SAST + `findings_out` + consciente del baseline | Sin cambios — el **ruleset** de secretos amplió la cobertura (7 reglas, 77 escenarios) |
| **Git Hooks** | post-checkout/post-merge/pre-push en el ledger | Sin cambios — `prepare-commit-msg` sigue sin interactividad |
| **Capa SCM** | 4 forges, paginación, `web_links.py` | + `IssueInfo`, `get_issue()`, `get_default_branch()`, `closing_line()`; read-back legado documentado |
| **Motor de release** | Secciones `[1.4.3]`/`[1.4.4]` commiteadas; PyPI en 1.4.4 | Sección `[1.5.0]` commiteada y traducida; tag `v1.5.0`; publicación pendiente |
| **Documentación** | 50 temas (47 completos + 3 parciales) | 50 temas (48 completos + 2 parciales) — `review-pr` traducido en los 5 idiomas |
| **Glosario de dominio** | `CONTEXT.md` con 7 términos | **22 términos** — PR target base, project config file, self-target, issue registry, closing line, etc. |
| **CI** | `tests.yml` (3.10 + 3.13) + `action.yml` + `extension.yml` | + 🆕 `release.yml` (Trusted Publishing) |
| **Suite de pruebas** | 3010 escenarios (141 archivos) | **3276 escenarios (153 archivos) — +266 escenarios, +12 archivos** |
| **Fallos conocidos** | 5 (3 i18n + 2 mentor) + 1 intermitente | **0 de los heredados** (i18n y mentor cerradas); el intermitente del commit sigue abierto y **la suite completa ahora se cuelga en `test_a_flow_that_never_enforced_it_is_untouched`** (`-c` sin guardia de TTY) |
| **Dependencias** | Ninguna dependencia Python nueva | Ninguna nueva — `PyYAML` y `requests` ya estaban presentes |
| **Commits desde el informe** | 3 commits de código + 1 de documentación + 2 merges | **37 commits y 15 merges** (2026-10-30 → 2026-11-04) |
| **PRs fusionados** | 2 PRs (#230, #231) | **15 PRs (#235, #237, #239, #241, #243, #244, #245, #247, #249, #251, #253, #255, #256, #258, #260)** |
| **Índice de memoria** | 40 patrones | 40 patrones (sin cambios) |
| **Informes de tarea** | 114 claude-code, 19 gemini | **116 claude-code (+12 en `feat/issue-autoclose/`), 31 gemini** |
| **Planes de desarrollo** | 148 planes, 2 gemini, 21 surveys | **152 planes, 12 gemini, 24 surveys** |

---

## **🚧 Próximos Pasos**

* **🆕 Publicar la 1.5.0 en PyPI:** la tag `v1.5.0` existe y `release.yml` está listo, pero el paquete servido es 1.4.4. Ejecutar/confirmar el workflow de la tag y validar `pip install --upgrade gitpr-cli`; después comprobar que un cliente 1.4.4 pasa a bloquear. **Es la deuda de release reabierta.**
* **🆕 Estabilizar `test_headless_full_flow`:** el clic en `#btn_confirm` llega antes de que el layout de `CommitMessageScreen` se asiente y el test muere en el timeout de 5 s. Esperar a que el botón sea **clickable** (o sustituir el clic por `screen.dismiss("confirm")`) elimina el único fallo intermitente de la suite. **Ítem heredado, sin cambios en esta ventana.**
* **🆕 Proteger `-c` de la ejecución no interactiva — ahora con evidencia:** el enrutamiento sigue sin guarda de TTY — sin `--hook`, `gitpr -c` **siempre** abre `CommitApp` ([main.py:1625-1638](src/main.py#L1625-L1638)), también en scripts y CI. En esta ventana el costo dejó de ser hipotético: `test_a_flow_that_never_enforced_it_is_untouched` llama a `gitpr -c` por el `CliRunner` y **`app.run()` nunca retorna**, atascando la ejecución completa de la suite en ~33%. Reutilizar `_may_ask_the_user()` (o exigir `--hook`) corrige el comportamiento y desbloquea la prueba de una vez. **Ítem heredado, aún abierto — y es la prioridad de estabilidad de la suite.**
* **🆕 Cubrir el cierre de issue en las otras forges:** la funcionalidad es GitHub-only por diseño (`get_issue`/`get_default_branch`/`closing_line` lanzan `ScmNotSupportedError`). GitLab (`Closes #N`), Bitbucket y Azure necesitan implementación propia antes de que la funcionalidad sea multi-forge.
* **🆕 Documentar el nuevo archivo versionado como cambio de flujo:** `.gitpr/conf/gitpr.config.yml` nace en la primera ejecución dentro de un repositorio. Quien ejecuta GitPR en CI o en monorepos necesita saber que el archivo se creará y que `GITPR_SKIP_PROJECT_CONFIG_SEED` lo desactiva — la documentación de config cubre la funcionalidad, pero no la nota de migración.
* **Corregir el aislamiento de `tests/test_mentor_cli.py`:** ✅ **concluido** — el patch pasó a apuntar a `src.main.get_git_diff`.
* **Cerrar la deuda de i18n:** ✅ **concluido** — 1698 claves en los seis diccionarios, `tests/test_i18n.py` 22/22.
* **Ampliar la cobertura del ruleset de secretos:** ✅ **concluido** — `ASIA…`, `github_pat_…`, `xoxc/xoxd`, asignación sin comillas y frontera a la izquierda.
* **Traducir `docs/review-pr.*.md`:** ✅ **concluido** — 5 idiomas completos.
* **Desrastrear los artefactos de `.gitpr/metrics/export/`:** ✅ **concluido** — 0 archivos rastreados.
* **Documentar la ruptura de contrato de `request_pull_request_reviewers`:** ✅ **concluido** — ADR-0009 + docs de revisores/SCM.
* **Mapear el radio de impacto del baseline en el exit code:** ✅ **cubierto** — `tests/integration/test_baseline_exit_code_contract.py` (8 escenarios) y docs actualizadas.
* **Reconciliar la versión del proyecto:** ✅ **concluido** — `CLAUDE.md`/`GEMINI.md` en 1.5.0, ADR-014 y prueba de consistencia.
* **`gitpr -h config` ignora `-h`:** ✅ **concluido** — `-h` antes del subcomando renderiza la ayuda y no abre la TUI.
* **Migrar el extractor de `sync_i18n.py` a AST:** ✅ **concluido** — regex sustituido, 21 falsos negativos eliminados.
* **Documentar el cambio de semántica de `GITPR_SKIP_UPDATE_CHECK` como nota de migración:** `docs/tutorial/install-from-source` ya describe el parser estricto, pero las automatizaciones que ponían `=false` esperando silenciar la verificación ahora la ven correr. **Ítem heredado, aún abierto.**
* **Documentar la ruptura de comportamiento de `-c` interactivo como nota de migración:** `commit-message-ia` describe el flujo nuevo, pero no dice explícitamente "antes era silencioso; en CI usa `--hook`". **Ítem heredado, aún abierto.**
* **Sección Smart Exclude en la TUI:** los campos `GITPR_SMART_EXCLUDES_*` siguen bajo `diff`, sin categoría propia. **Ítem heredado, aún abierto.**
* **Deudas registradas en el plan de la TUI de configuración:** `DEFAULT_CONFIG` quedó redundante con el schema; el banner de apertura no lista `--init`, `--base` ni `--plugins`; `LinterApp` es la **única** app Textual que aún no desactiva la command palette; y las **9 `GITPR_CHECK_*`** siguen **sin** categoría en el schema. **Ítem heredado, aún abierto.**
* **Crear una nota de migración para `action.yml`:** reemplazar `--linter` + `--fullreview` por `gitpr check` es una ruptura de contrato para los workflows existentes. **Ítem heredado, aún abierto.**
* **Publicar la extensión en los marketplaces:** el workflow está listo, pero depende de `VSCE_PAT`/`OVSX_PAT`; hasta entonces, la instalación es vía VSIX local. **Ítem heredado, aún abierto.**
* **Revisar `git diff` en repositorios con archivos binarios grandes:** los bytes binarios llegan al modelo como una secuencia de `U+FFFD`; vale la pena confirmar que los smart excludes cubren los casos reales. **Ítem heredado, aún abierto.**
* **Monitorizar el fallback ES/FR en el sitio:** los enlaces de los README ya usan `?lang=es` y `?lang=fr`, pero el sitio todavía puede servir inglés. **Ítem heredado, aún abierto.**
* **Proveedor Anthropic Claude:** Soporte directo para la API de Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual en el Dashboard:** Histogramas de tiempo y tendencias de tokens en la TUI de métricas.
* **Seed de `.gitpr/conf/` local:** el seed del smart-excludes y del linter sigue pendiente como subcomando propio o etapa del wizard — `gitpr.config.yml` ahora cubre solo la base del PR.
* **Más proveedores:** OpenAI directo, proveedores locales adicionales.

### ✅ Completados en esta ventana (2026-10-30 → 2026-11-04)

* ~~**Todo issue creado con `gitpr -is` quedaba huérfano**~~ 🆕 — PR #260 (`4c2bede`): registro local, `get_issue`/`get_default_branch`/`closing_line`, línea `close #N` idempotente, flags `--close-issue`/`--no-close-issue` y métrica `issue_autoclose`. **120 escenarios nuevos.**
* ~~**La base del PR solo existía en el `.env` de la máquina**~~ 🆕 — PR #235 (`2eff88b`): `.gitpr/conf/gitpr.config.yml` versionado, resolvedor único, validación contra `origin` y modal `PrBaseApp` ([ADR-015](docs/plans/ADR-015-project-local-pr-base.md)).
* ~~**La deuda de i18n (11 claves faltantes, 2 huérfanas, 3 pruebas rojas)**~~ — PR #237 (`96526b9`): español y francés sincronizados, MCP y chat localizados, guarda de identidad, `__lang_version__` v0.0.37; cerrada de una vez en 1698 claves. **`test_i18n.py` 22/22.**
* ~~**Los 2 fallos antiguos de `test_mentor_cli.py`**~~ — PR #239 (`90fd76c`): patch movido a `src.main.get_git_diff`.
* ~~**El release era manual**~~ — PR #241 (`d401931`): `release.yml` con pruebas, verificación tag/versión, build, `twine check`, Trusted Publishing y GitHub Release.
* ~~**Brechas del ruleset de secretos**~~ — PR #243 (`9dd149a`): `ASIA…`, `github_pat_…`, `xoxc/xoxd`, asignación sin comillas, frontera a la izquierda y filtros de runtime.
* ~~**`gitpr -h config` abría la TUI**~~ — PR #253 (`ce52558`): `-h` antes del subcomando renderiza la ayuda.
* ~~**El informe de riesgo degradaba en silencio con `file_path`**~~ — PR #249 (`3e6f97a`): agregación tolerante y contrato de exit code del baseline cubierto.
* ~~**La versión podía divergir entre `pyproject.toml`, `CLAUDE.md` y `GEMINI.md`**~~ — PR #251 (`ed7e888`): SSOT en `src/updater.__version__` ([ADR-014](docs/adr/0014-canonical-package-version-ssot.md)) y prueba de consistencia.
* ~~**El extractor de i18n truncaba concatenación implícita**~~ — PR #255 (`694a640`): AST en el script y en la prueba.
* ~~**El contrato del read-back de revisores no estaba documentado**~~ — PR #247 (`149fb49`): `(no read-back)` + [ADR-0009](docs/adr/0009-reviewer-attach-returns-read-back.md).
* ~~**`review-pr` solo tenía 2 de los 5 idiomas**~~ — PR #244 (`8cdd1fc`): PT-PT, ES y FR traducidos.
* ~~**34 artefactos de métricas seguían rastreados**~~ — PR #245 (`e4f455b`): `git rm --cached` aplicado.
* ~~**La 1.4.4 estaba publicada pero el release seguía siendo manual**~~ — la 1.4.4 sigue en PyPI y el pipeline de release ahora es automatizado; la 1.5.0 es el primer candidato a pasar por él.

---

**Informe generado el:** 2026-11-04  
**Rama:** `main`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
