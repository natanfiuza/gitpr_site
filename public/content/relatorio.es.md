# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.13 (2026-09-08)**

## **📌 Visión General**

**GitPR** es una herramienta CLI (Command Line Interface) avanzada para la automatización de procesos Git mediante Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). Su objetivo principal es actuar como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita la deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.13):**
- **SCM Multi-Forge (`gitpr --init` + capa `ScmProvider`):** Una única abstracción sobre GitHub, GitLab, Bitbucket y Azure DevOps en `src/infrastructure/scm/` — registry con `resolve_scm_provider()` (predeterminado `github`, fallback del token legacy intacto), `parse_repo_ref()` para el direccionamiento de repositorios, providers que lanzan `ScmProviderError` y un wizard `--init` que detecta el forge desde el remote, valida el token (`test_connection`, 3 intentos, re-prompt en 401) y persiste **solo en caso de éxito** con cifrado Fernet. `src/github_api.py` pasó a ser un shim deprecado que delega en el provider.
- **Revisores Sugeridos en el flujo de PR:** Sugerencia de revisores desde el propio forge al publicar PRs, con opt-out por flag (`--no-suggest-reviewers`) y configuración por variables de entorno (`GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N`, `GITPR_REVIEWER_SUGGESTION_EXCLUDED`).
- **Subcomando `gitpr release` (Changelog / Release Notes):** Genera el changelog de la rama entre `--since` (predeterminado: último tag) y `HEAD`, clasifica los commits por Conventional Commits (`src/commit_classifier.py`), sugiere un bump semántico (`src/version_bump.py`), ensambla las secciones traducibles (`src/changelog_builder.py`), añade un resumen ejecutivo de IA y lo *antepone* a `CHANGELOG.md`. Con `--publish`/`--draft` publica el release en el forge (GitHub crea el tag; GitLab exige un tag existente); `--format markdown|json` para salida estructurada. Plantilla de skill `gitpr.release.*.md` descargada automáticamente en el primer uso, en 5 idiomas.
- **Servidor MCP Silencioso + DNS Acotado:** Corrección de la fuga de salida de las tools en el flujo stdio/CLI y resolución DNS acotada en el tiempo (bug de la ventana anterior). Default de `GITPR_AI_TIMEOUT` reducido de 600s a **180s**.
- **URLs y Prompts Localizados:** URLs del repositorio estandarizadas en las plantillas y la documentación; los prompts de creación de issues ahora reciben el idioma activo.
- **i18n ampliada a 742 claves:** Los encabezados del changelog ahora son traducibles (helper en runtime, no constantes opacas), 48 claves nuevas traducidas en los 6 diccionarios, `__lang_version__` v0.0.23 y un bullet de la familia `release-notes` en el índice del README (5 copias).
- **Documentación Multilingüe Expandida:** 3 familias completas nuevas en 5 idiomas — `release-notes`, `scm-multiforge` y `suggested-reviewers` (+ ADRs de arquitectura para SCM y release) — y 8 temas actualizados.
- **Salto de Versión:** `__version__` pasó de 0.0.37 a **1.0.0** (vía 0.0.38 en esta ventana); CHANGELOG.md registra el encabezado `[v0.1.0] - 2026-09-07` generado por la propia funcionalidad de release durante el desarrollo.

- **Versión actual:** 1.0.0
- **Versión de los diccionarios de idioma:** v0.0.23
- **Versión de los scripts de hook:** v0.0.3
- **Publicación:** PyPI (`pip install gitpr-cli`) + GitHub Releases (binario standalone)
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 diccionarios)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal).
* **UI/Terminal:** Textual — TUI (Text User Interface) para chat interactivo, edición de issues, help screen, dashboard de métricas, PR Publisher y errores del linter (`LinterApp`).
* **Criptografía:** `cryptography.fernet` para protección local de claves de API, tokens de GitHub y tokens SCM de los forges.
* **Configuración:** `python-dotenv`, `pyyaml` (para el linter estático).
* **Proveedores de IA:** Integración vía SDK oficial de Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`) y OpenAI SDK (`Ollama` local).
* **APIs de Forge:** `requests` (REST) — capa de abstracción multi-forge en `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps); módulo legacy `src/github_api.py` mantenido como shim deprecado.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — 12 herramientas anotadas, 17 recursos, 7 prompts; handlers descargados a threads vía `anyio`.
* **Pruebas:** Pytest + `unittest.mock` (41 archivos de prueba — 32 en la raíz + 9 en `tests/scm/` —, 791 escenarios recolectados) + pruebas e2e del servidor MCP vía subprocess real (JSON-RPC stdio).
* **Empaquetado:** PyInstaller (binario standalone) + setuptools/build (PyPI).
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` para ejecución en pipelines.

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación Estructurada:** Se comunica con el LLM pidiendo salida estrictamente JSON.
* **Map-Reduce (Diffs Gigantes):** Cuando el diff supera ~90k tokens, lo divide automáticamente en lotes por archivo (`split_diff_into_chunks`), procesa cada parte (Map) y unifica los resúmenes (Reduce). Soporta PRs, commits e Issues.
* **Tokenizer Local:** `tokenizer.json` para estimación precisa de tokens antes del envío a la IA.
* **Estimación de Tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()` con fallback al tokenizer local.
* **Optimización Nativa de Git:** Flags `-U1`, `-w`, `-M`, `-B` en los comandos `get_git_diff` y `get_git_full_diff` para reducir contexto inútil.
* **Pre-Save (`--pre-save`):** Flag oculta de debug que guarda el payload completo (instrucción del sistema + prompt) en JSON antes de cada llamada a la IA.
* **Smart Excludes con Dos Capas:** Filtro de pathspec inteligente con capa global (`~/.gitpr/conf/`) + local del proyecto (`./.gitpr/conf/`). Fusión en runtime (unión, deduplicada). Auto-seed del archivo local en la primera ejecución.
* **Métricas con Seguimiento de Tiempo:** Inyección de `log_command_metric()` en todos los flujos con la duración en milisegundos (`duration_ms`) y lazy imports.
* **Resolución Centralizada de Salida:** Función `resolve_output_path()` que centraliza la lógica de directorios de salida — por defecto en `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`) 🆕:** `gitpr --init` — detecta el forge desde el remote origin, solicita extras por forge (org/project de Azure, username de Bitbucket), valida el token con `test_connection` (3 intentos, re-prompt en 401) y persiste `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` (Fernet) **solo en caso de éxito**.
* **Plantilla de Skill de Release (`ensure_release_skill_template()`) 🆕:** Descarga `templates/gitpr.release.*.md` en el primer uso de `gitpr release` (capa CLI, consciente del idioma, nunca sobrescribe; omitida con `--format json`).
* **Trailer de Coautoría:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceros.
* **Subprocesses Blindados:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` en todos los `subprocess.run`; verificación de conexión vía socket `8.8.8.8:53` antes de las operaciones de red.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitectura de Plugins:** Sistema de extensibilidad que carga plugins del directorio `~/.gitpr/plugins/` aplicándose a **todos los proyectos**.
* **Plugins de Linter (`linter/`):** Archivos `.yml` con reglas regex adicionales fusionadas con el `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`):** Archivos `.md` que extienden el contexto del sistema con instrucciones específicas.
* **Factory Closures:** Funciones `get_linter_plugins` y `get_prompt_plugins` con closures para aislar estado entre sesiones.
* **Comando `--plugins`:** Lista todos los plugins globales instalados con sus tipos y rutas.
* **Documentación Multilingüe:** `docs/plugins-system.md` en 5 idiomas (EN, PT-BR, PT-PT, ES, FR).

### **3. Interfaz CLI y Configuración (`src/main.py` y `src/config.py`)**

* **Setup Inicial:** Detecta la primera ejecución, crea la carpeta `~/.gitpr/` y solicita interactivamente las claves de API, preferencias e idioma.
* **Enrutamiento de Comandos:** Gestiona todas las flags y el subcomando `release` (ver módulo 22).
* **Comportamiento Predeterminado:** Ejecutar `gitpr` sin flags abre la TUI del PR Publisher.
* **Flags (35 opciones Click en la raíz):**
  * `--init` 🆕: Abre el wizard de configuración multi-forge de SCM (detección de forge + validación de token).
  * `--no-suggest-reviewers` 🆕: Desactiva la sugerencia de revisores en el flujo de publicación de PR.
  * `--no-publish`: Genera la descripción del PR y la guarda localmente sin abrir el editor interactivo.
  * `--no-edit`: Salta la TUI por completo — auto-commit, auto-push y publica directamente en GitHub.
  * `--base <branch>`: Sobrescribe la rama de destino del Pull Request.
  * `--plugins`: Lista plugins globales instalados.
  * `--linter-setup`: Abre el asistente interactivo de configuración de linters externos.
  * `--version`: Muestra la versión actual de GitPR (vía `@click.version_option`).
* **Variables de Entorno (39 claves en `DEFAULT_CONFIG`):** familia SCM 🆕 (`GITPR_SCM_PROVIDER`, `GITPR_SCM_TOKEN`, `GITPR_SCM_TOKEN_ENCRYPTED`, `GITPR_SCM_BASE_URL`, `GITPR_SCM_ORGANIZATION`, `GITPR_SCM_PROJECT`, `GITPR_SCM_USERNAME`), familia reviewers 🆕 (`GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N`, `GITPR_REVIEWER_SUGGESTION_EXCLUDED`), además de `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE`, `GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`, `GITPR_AI_TIMEOUT` (default 180s en esta ventana), `OUTPUT_FILE_NAME_*`, `GITPR_COAUTHOR` (read-only) y otras.
* **Ayuda Contextual:** `-h --flag` muestra documentación específica de la funcionalidad con un enlace directo (consciente del idioma) a GitHub. 🆕 Los subcomandos obtienen su propio `epilog=`: `gitpr release -h` termina con "Full documentation:" + `get_doc_url("release-notes.md")` (párrafo Click `\b` para que la URL no se re-envuelva en ningún locale).
* **--lang:** Fuerza el idioma de la interfaz para la ejecución actual sin persistir el cambio.
* **--provider:** Fuerza el proveedor de IA (`gemini`, `deepseek`, `ollama`) para la ejecución actual.
* **--mcp:** Inicia el servidor MCP en transporte stdio para integración con editores — **12 herramientas anotadas + 17 recursos + 7 prompts**.
* **--install:** Asistente guiado de 4 pasos que descarga plantillas de skill, instala Git Hooks, configura MCP en los editores y valida claves de API.
* **--metrics:** Sistema de telemetría local con alcance por repositorio: `--export`, `--purge`, `--dashboard`.
* **--status:** Lista archivos no commiteados categorizados (new/modified/deleted) — rápido, sin IA, sin red.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)**

* **Interfaz Interactiva Completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en el terminal.
* **6 Pantallas Modales:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Revisores Sugeridos 🆕:** El flujo de publicación consulta al forge los revisores sugeridos y los ofrece en la TUI; selección controlada por `--no-suggest-reviewers`, `GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N` y `GITPR_REVIEWER_SUGGESTION_EXCLUDED`.
* **Bindings:** F1 (Help), F2 (Guardar .md local), F3 (Publicar vía GitHub API), Esc (Salir).
* **Flujo de Auto-Commit:** Linter → mensaje IA → confirmación → commit → push → publica PR.
* **Verificación de Archivos Unstaged:** Al iniciar, verifica `git status --porcelain` y ofrece un modal para seleccionar, saltar o cancelar.
* **Manejo de PR Existente:** Detecta PRs abiertos para la rama actual vía API y ofrece push o crear nuevo.
* **Auto-Upstream:** Detecta fallo de `git push` por falta de upstream e intenta automáticamente `--set-upstream origin <branch>`.
* **Flujo de Merge:** Tras la creación/actualización del PR, ofrece opción de merge. Controlado por `GITPR_AUTO_MERGE`.

### **5. Módulo de API de GitHub (`src/github_api.py`)**

* **Shim Deprecado 🆕:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` y las demás funciones ahora delegan en `src/infrastructure/scm/github_provider.py`; el módulo emite un `DeprecationWarning` y mantiene las tuplas legacy `(ok, data, status)` — ningún código nuevo debe importarlo.

### **6. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) en el git diff sin gastar cuotas de IA.
* **Reglas YAML:** Lee el archivo local `.gitpr.linter.yml` (creado vía `--skill`).
* **Plugins de Linter:** Reglas adicionales cargadas desde `~/.gitpr/plugins/linter/*.yml`.
* **Bridge de Linters Externos:** Ejecuta ESLint/PHPCS/Stylelint sobre las líneas modificadas del diff, parser de Checkstyle XML y cruce por línea.
* **Informe Consolidado:** `generate_linter_report_content()` consolida errores regex + externos en `.gitpr/reports/linter/` — generado solo cuando hay violaciones.

### **7. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cifrado:** Genera una clave maestra `secret.key` en la carpeta `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data` y `decrypt_data` para proteger claves de API de IA, PATs de GitHub y tokens SCM de los forges (`GITPR_SCM_TOKEN_ENCRYPTED`).
* **Validación Multi-Forge 🆕:** `validate_or_request_scm_token(provider, repo_display)` — valida el token contra el forge configurado con bucle de reautenticación en 401 preservando el borrador; el token legacy de GitHub (`GITHUB_TOKEN_ENCRYPTED`) sigue funcional hasta que se ejecute `--init`.

### **8. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Verifica en la API de GitHub Releases la versión más reciente, descarga el binario compilado y lo sustituye sin romper la ejecución en curso (con rollback).
* **Caché Diaria:** Evita verificaciones repetidas el mismo día.
* **Versionado Centralizado:** `__version__` (**1.0.0**), `__lang_version__` (**v0.0.23**), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.

### **9. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Construida con Textual — historial de mensajes, entrada multi-línea, barra de estado con bindings visibles.
* **Memoria por Rama (`src/chat_memory.py`):** Historial de conversación persistido por rama, permitiendo continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear` — atajos para pair programming.
* **Auto-Patching (F5), Actualización de Diff (F2), Exportación de Sesión (F6).**

### **10. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con soporte a placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`.
* **5 Idiomas, 6 Diccionarios:** en_us (predeterminado/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Archivos Versionados:** `__lang_version__` (**v0.0.23**) controla la actualización de los paquetes de idioma (`langs/*.json`) — cadena de bumps v0.0.20 → v0.0.23 en esta ventana.
* **Cobertura:** **742 claves** de traducción en cada uno de los 6 archivos — **paridad total de key sets** (auditoría AST de 742 claves en código: 0 sin traducir, 0 huérfanas).
* **Encabezados del Changelog Traducibles 🆕:** Los 8 encabezados de sección del changelog (Features, Bug Fixes, Breaking Changes etc.) ya no son constantes opacas y ahora son literales `__()` resueltos en runtime vía el helper `_category_heading()` — siguen a `--lang`/`set_lang` y son visibles para el extractor AST.
* **Traducciones Genuinas 🆕:** +195 claves desde el informe anterior (48 de ellas de la tarea de próximos pasos del 2026-08-09, en traducción real en los 6 diccionarios, CRLF preservado).
* **Caché con Indexación por Idioma:** Las respuestas de IA en caché incluyen el idioma actual en el keying MD5.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en background durante llamadas de IA mostrando caracteres braille con palabras de "pensamiento".
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas.

### **12. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Modo JSON & Parámetros Deterministas:** Salidas estructuradas con `temperature=0.0` y `top_p=0.1`; fallback automático entre los proveedores configurados.

### **13. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Keying por hash MD5 del diff y el prompt, con indexación por idioma.
* **Telemetría y Duración:** Persistencia de los campos `duration_ms` y `meta_raw` en archivos de caché.
* **Lectura para el Dashboard:** `scan_cache_files_for_dashboard()` lee todos los archivos de caché recursivamente.

### **14. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`), y Arqueología por Blame (`-b`).
* **Publicación Multi-Forge 🆕:** F3 crea el issue en el forge **configurado** (`GITPR_SCM_PROVIDER`) vía `provider.create_issue` — no solo GitHub; Azure DevOps lanza `ScmNotSupportedError` (los Work Items dependen de la plantilla de proceso).
* **Map-Reduce para Issues:** Cuando el contexto supera ~90k tokens, divide automáticamente en chunks y unifica los resultados.
* **Manejo de 401:** Señalización de reautenticación sin cerrar la aplicación.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y autoría histórica de fragmentos de código con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registrados vía `log_blame_metric()` con seguimiento de profundidad y número de commits analizados.

### **16. Servidor MCP e Invocación CLI Directa (`src/mcp_server.py`)**

* **12 Herramientas MCP Anotadas:** Herramientas para `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **17 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release}` + `linter://config` + `prompt://list` + 7 prompts — 🆕 recursos de skill de release (y la familia correspondiente en las plantillas).
* **Invocación CLI Directa:** El comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca cualquier tool MCP directamente sin iniciar el servidor stdio JSON-RPC.
* **Aislamiento del Stdout Real:** `_write_real_stdout()` escribe directamente en el `sys.__stdout__` original, garantizando JSON puro en stdout.
* **Silencio Garantizado 🆕:** Salida de las tools silenciada en el flujo servidor/CLI (eliminadas las fugas de `print` que corrompían el stream JSON-RPC — fix `681a7fa`, PR #146).
* **DNS Acotado en el Tiempo 🆕:** La resolución DNS de las operaciones de red está acotada en el tiempo — ninguna llamada bloqueante se queda atascada en la resolución (junto con el timeout duro de la descarga OTA).
* **Offload del Event Loop:** Decorador `_offload` (`anyio.to_thread.run_sync`) aplicado a las 12 tools — los handlers síncronos no congelan el servidor stdio.
* **Pruebas E2E:** `tests/test_mcp_server_e2e.py` levanta el servidor real como subprocess y habla JSON-RPC stdio.

### **17. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Alcance por Repositorio (Repo-Scope):** Etiqueta `📁 Repository: owner/repo` y filtrado estricto por proyecto.
* **Escaneo Asíncrono con Overlay:** Worker thread en background con widget `ProgressBar`.
* **Consolidación de Datos:** `load_cache_token_summary()` suma tokens de caché al totalizador.
* **Exportación Local:** Guardado de CSV/JSON en `./.gitpr/metrics/export/`.

### **18. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Alcance por Repositorio:** Todos los eventos indexados por `repo_name`.
* **Eventos de Hook, Linter y Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Exportación y Limpieza:** `--metrics --export` (CSV/JSON) y `--metrics --purge` con confirmación interactiva.

### **19. Sincronización de Hooks Git**

* **Versionado Independiente:** `__scripts_version__` (v0.0.3) controla la versión de los scripts de hook; detección y actualización automáticas.
* **Consciente del Idioma:** Descarga plantillas de hook correspondientes al idioma configurado.
* **Skip de Merge-Source:** La plantilla `prepare-commit-msg` salta las fuentes `message|merge|squash|commit` — los commits generados por git preservan el mensaje original.

### **20. Bridge de Linters Externos y Asistente Interactivo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Asistente `--linter-setup`:** Wizard interactivo con presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e inyección del bloque `external_linters` en `.gitpr.linter.yml`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` servido desde GitHub con la cadena de resolución local → descarga → stale → fallback embebido.
* **TUI de Errores del Linter:** `src/ui/linter_app.py` (Textual) muestra errores críticos y warnings; en modo hook/quiet imprime y hace `sys.exit(1)`.
* **Informe Markdown:** Consolidado en `.gitpr/reports/linter/` — solo cuando hay violaciones.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`) 🆕**

* **Abstracción Única (`ScmProvider` ABC):** `base.py` define el contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. y `ScmProviderError(provider, http_status, message)` — `http_status` 0 = fallo de red); un provider concreto por forge en `github_provider.py`, `gitlab_provider.py`, `bitbucket_provider.py`, `azure_devops_provider.py`.
* **Registry y Factory:** `resolve_scm_provider()` selecciona por `GITPR_SCM_PROVIDER` (predeterminado `github` — migración cero, fallback del token legacy de GitHub intacto); `detect_provider_from_remote()` identifica el forge desde la URL de origin.
* **Direccionamiento de Repositorios:** `parse_repo_ref(remote_url) -> RepoRef(raw, workspace, name, provider)` — workspace = owner de GitHub / namespace de GitLab (subgrupos) / workspace de Bitbucket / display `{org}/{project}` de Azure.
* **Fail-Fast por Forge:** Azure DevOps exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket exige `GITPR_SCM_USERNAME` (App Password = Basic auth user+token); `create_issue` en Azure lanza `ScmNotSupportedError`.
* **Publicación de Release 🆕:** `provider.create_release()` usado por `gitpr release --publish` (GitHub crea el tag en la rama predeterminada; GitLab exige que el tag exista) — `tests/scm/test_release_publish.py`.
* **Artefactos:** Glosario + ADR-001 en `docs/plans/`; familia `docs/scm-multiforge.*.md` en 5 idiomas; pruebas: 9 archivos, 265 escenarios.

### **22. Subcomando `gitpr release` — Changelog / Release Notes 🆕**

* **Flujo:** `git log` entre `--since` (predeterminado: último tag alcanzable, o el primer commit) y `HEAD` → clasificación Conventional Commits → bump semántico sugerido (`--version <x.y.z>` lo sobrescribe) → ensamblado del changelog → resumen ejecutivo de IA opcional → *anteposición* a `CHANGELOG.md`. La generación local es el comportamiento predeterminado — nada se publica ni se toca sin solicitarlo.
* **Clasificador (`src/commit_classifier.py`):** Clasifica los commits por tipo Conventional Commits (feat/fix/refactor/docs/chore/etc.) con un parser tolerante — la base para el agrupamiento en secciones.
* **Builder con Secciones Traducibles (`src/changelog_builder.py`):** Secciones "✨ Features", "🐛 Bug Fixes", "⚠️ Breaking Changes", "Summary", "Contributors" etc. renderizadas vía `__()` en runtime (siguen a `--lang`); aritmética de la auditoría AST cerrada (742 = 742).
* **Bump Semántico (`src/version_bump.py`):** Sugiere la próxima versión a partir de los tipos clasificados (major para breaking, minor para feat, patch para fix) y valida los destinos `x.y.z`.
* **Publicación:** `--publish` crea el release en el forge configurado (con confirmación explícita); `--draft` lo crea como borrador (GitHub; GitLab no tiene concepto de draft); `--format markdown|json` para salida estructurada; `--force` para reescribir.
* **Plantilla de Skill:** En el primer uso descarga `templates/gitpr.release.*.md` (5 idiomas: en/pt_br/pt_pt/es_es/fr_fr) vía `ensure_release_skill_template()` — nunca sobrescribe; editable localmente como instrucción del sistema para el resumen ejecutivo.
* **Ayuda Contextual:** `gitpr release -h` termina con "Full documentation:" + un enlace consciente del idioma a `docs/release-notes.md` (epilog `\b`, sin re-envoltura de la URL).
* **Pruebas:** 94 escenarios nuevos — `test_release_cli.py` (4), `test_release_engine.py` (27), `test_changelog_builder.py` (15), `test_commit_classifier.py` (23), `test_version_bump.py` (17) + `tests/scm/test_release_publish.py` (8).
* **Artefactos:** familia `docs/release-notes.*.md` (5 idiomas), spec en `docs/plans/`, ADR-002 (subcomando) y ADR-003 (módulos planos), glosario de release notes.

---

## **📊 Pruebas y Calidad**

| Archivo de Prueba | Escenarios | Enfoque |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 🆕 | Blame por rango de líneas en un archivo |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidad, commits, duración |
| `tests/test_changelog_builder.py` | 15 🆕 | Builder de changelog: secciones, encabezados traducibles, contribuidores |
| `tests/test_chat_backend.py` | 19 | Memoria de chat, persistencia, comandos slash |
| `tests/test_commit_classifier.py` | 23 🆕 | Clasificación Conventional Commits (tipos, parser tolerante) |
| `tests/test_config_suggest_reviewers.py` | 8 🆕 | Configuración de revisores sugeridos (claves y defaults) |
| `tests/test_core.py` | 39 | Flujos principales, git diff, generación de PR, timing, staging, coautoría |
| `tests/test_diff_parser.py` | 15 🆕 | Parser de diff por líneas/hunks |
| `tests/test_external_linters.py` | 33 | Bridge Checkstyle: parser XML, subprocess, cruce de diff, informe |
| `tests/test_i18n.py` | 20 | Paridad entre idiomas (742×6), claves ausentes/huérfanas, identidad |
| `tests/test_install_wizard.py` | 3 | Asistente interactivo de instalación |
| `tests/test_issue_engine.py` | 4 | Borrador estructurado de issue |
| `tests/test_linter_metrics.py` | 4 | Métricas de linter: errores, warnings, duración |
| `tests/test_main_suggest_reviewers.py` | 6 🆕 | Flag `--no-suggest-reviewers` en la CLI y ayuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompt MCP y fallback de idioma |
| `tests/test_mcp_server.py` | 85 | Herramientas MCP, recursos, annotations, patching, CLI directo, offload |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real vía subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Recolección, exportación local, alcance de repo, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Timeouts de red/IA — **2 aserciones desactualizadas (600s)** |
| `tests/test_plugins.py` | 17 | Descubrimiento de plugins, merge de reglas de linter, prompts MCP |
| `tests/test_pr_publish_app.py` | 42 | TUI del PR Publisher: pantallas, flujos, revisores sugeridos |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de error del linter: abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save y payload JSON |
| `tests/test_release_cli.py` | 4 🆕 | CLI de release: opciones, help con epilog documentado |
| `tests/test_release_engine.py` | 27 🆕 | Motor de release: rango de commits, CHANGELOG, publicación |
| `tests/test_reviewer_suggestion.py` | 15 🆕 | Lógica de sugerencia de revisores (ranking, exclusión, top-N) |
| `tests/test_skill_command.py` | 10 | Descarga y validación de plantillas de skill |
| `tests/test_skill_context.py` | 6 🆕 | `get_skill_context()` con `quiet=True` (fallbacks, release) |
| `tests/test_smart_excludes.py` | 13 | Filtro pathspec inteligente |
| `tests/test_suggest_reviewers.py` | 14 🆕 | Revisores sugeridos en el flujo de PR (integración) |
| `tests/test_thinking_words.py` | 3 | Carga y parsing con separador `;` |
| `tests/test_version_bump.py` | 17 🆕 | Bump semántico: major/minor/patch, destinos y validación |
| `tests/scm/test_contract.py` | 38 🆕 | Contrato `ScmProvider`: firmas, dataclasses, errores |
| `tests/scm/test_github_provider.py` | 57 🆕 | Provider de GitHub: REST, headers, PRs, issues, releases |
| `tests/scm/test_gitlab_provider.py` | 41 🆕 | Provider de GitLab: API v4, namespace, releases |
| `tests/scm/test_bitbucket_provider.py` | 39 🆕 | Provider de Bitbucket: Basic auth, workspace |
| `tests/scm/test_azure_devops_provider.py` | 43 🆕 | Provider de Azure DevOps: org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_factory.py` | 11 🆕 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 🆕 | Shim deprecado `github_api` → delega en el provider (sustituye a `test_github_api.py`) |
| `tests/scm/test_init_wizard.py` | 10 🆕 | Wizard `--init`: detección de forge, validación, persistencia |
| `tests/scm/test_release_publish.py` | 8 🆕 | Publicación de release por forge (GitHub/GitLab) |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura i18n (scaffold; nunca ejecutado) |

**Total:** 791 escenarios recolectados en 41 archivos de prueba (32 en la raíz + 9 en `tests/scm/`; +527 desde el informe anterior — incluye los 265 escenarios de SCM, que antes no existían como suite). Ejecución completa en esta máquina con `GITPR_LANG=en_us`: **787 passed / 2 failed / 2 skipped / 15 subtests** en ~63s.

**Notas de calidad de esta versión:**
- **2 fallos reales (pruebas desactualizadas):** `test_net_timeouts.py` sigue asertando el default de 600s para `GITPR_AI_TIMEOUT`, pero el código cambió a **180s** en esta ventana (fix `681a7fa`) — ver Próximos Pasos.
- **4 fallos ambientales de locale (máquina pt-BR):** `test_chat_backend::test_api_exception`, `test_main_suggest_reviewers::test_flag_appears_in_contextual_help` y `test_suggest_reviewers` ×2 renderizan texto pt_br desde la copia OTA de `~/.gitpr/langs/` — pasan íntegramente con `GITPR_LANG=en_us`. La suite totalmente verde del informe anterior no se repite en esta versión (2 regresiones de prueba + sensibilidad de locale).
- `test_github_api.py` fue **eliminado** (el código legacy pasó a ser un shim) y sustituido por `tests/scm/test_github_api_shim.py` (18 escenarios).

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n:** **742 claves** de traducción en cada uno de los 6 diccionarios (+195 desde el informe anterior) con **paridad total de key sets** — auditoría AST de 742 claves usadas en código: 0 sin traducir, 0 huérfanas. Las últimas 48 claves (próximos pasos de la familia release-notes: encabezados del changelog, strings de ayuda, prompts de IA con un `\n` real preservado) fueron traducidas con CRLF preservado byte a byte.
* **Temas Nuevos 🆕 (los 3 en 5 idiomas):**
  - `docs/release-notes.md` — familia del subcomando `gitpr release` (flujo, versiones, publicación, resumen de IA, plantilla de skill)
  - `docs/scm-multiforge.md` — capa `ScmProvider` y wizard `--init` (4 forges, tokens, limitaciones por forge)
  - `docs/suggested-reviewers.md` — revisores sugeridos en el flujo de PR (configuración y flags)
* **Temas actualizados en esta ventana (todos re-sincronizados en los 5 idiomas):** `docs/auto-update.md`, `docs/github-ci-linter.md`, `docs/linter-regras-customizadas.md`, `docs/map-reduce-diff.md`, `docs/mcp-integration.md`, `docs/providers-ia.md`, `docs/skill-template.md`, `docs/smart-excludes.md` + `docs/ARCHITECTURE.md` (EN; registro de las nuevas familias).
* **Documentación en 5 idiomas:** **37 temas canónicos** en `docs/` — **32 con cobertura completa en los 5 idiomas** (+3 desde el informe anterior) y 5 temas parciales/PT-only (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers` — el último ahora contabilizado explícitamente).
* **Skills Locales de Claude Code:** `.claude/skills/` con `status-report`, `implement-fixes`, `caveman-commit`, `new-feature` y `reports-to-memory` (ninguna nueva en esta ventana).
* **Memory Index:** `.claude/memory/MEMORY.md` con 40 patrones (25 de proyecto, 9 de feedback, 3 de referencia + 3 standalone).
* **Informes de tareas:** `docs/claude-code/reports/develop_natan/` (**84** en total; **+15** en la ventana — SCM multi-forge, revisores sugeridos, docs de las 3 familias, release notes/spec, skill de plantilla de release, próximos pasos de i18n etc.) y `docs/gemini/reports/` (5 archivos hoy; +2 en la ventana: `2026-08-28_add_mcp_tools_to_gemini_md.md` y `2026-09-03_update_repo_urls.md` — el conteo del informe anterior (8) incluía archivos eliminados durante la restauración del directorio del 2026-08-26).
* **Informes de estado:** `docs/reports/` (12 informes; este es el 13º).
* **Planes de desarrollo:** 80 archivos en `docs/plans/` (+21 en la ventana — specs y ADRs de SCM multi-forge, release notes, revisores sugeridos, sondeos y la skill de release).

---

## **🔄 Pipeline de Distribución**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → subida automatizada
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server:** Entry point `gitpr-mcp` vía `pyproject.toml`
5. **Plantillas y Idiomas OTA:** `templates/` y `langs/*.json` servidos desde GitHub (main) — el bump `v0.0.23` renueva las copias locales en `~/.gitpr/langs/` una vez publicado

---

## **📈 Evolución desde el Informe Anterior (v0.0.12)**

| Área | v0.0.12 (anterior) | v0.0.13 (actual) |
|------|-------------------|----------------|
| **Versión GitPR** | 0.0.37 | **1.0.0** (vía 0.0.38 en la ventana; CHANGELOG.md con encabezado `[v0.1.0]`) |
| **Versión Idioma** | v0.0.20 | **v0.0.23** |
| **Versión Scripts Hook** | v0.0.3 | **v0.0.3** |
| **Proveedores IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 idiomas, 6 diccionarios | 5 idiomas, 6 diccionarios |
| **Interfaz** | CLI + Issues TUI + Chat TUI + MCP Server + Dashboard + PR Publisher TUI + LinterApp + `--linter-setup` | **+ wizard SCM `--init` + subcomando `gitpr release` (CLI) + revisores sugeridos en el PR Publisher** |
| **Herramientas MCP** | 12 tools (offload) | **12 tools (offload; salida silenciada + DNS acotado) — 17 recursos (antes 15)** |
| **Flags CLI** | 27 flags | **35 opciones en la raíz (+ `--init`, `--no-suggest-reviewers`; conteo completo de Click) + subcomando `release` con 6 opciones** |
| **Variables de Entorno** | 23 vars | **39 claves en `DEFAULT_CONFIG` (+ 7 SCM + 3 reviewers)** |
| **Linter** | Regex + bridge Checkstyle (wizard/TUI/informe) | Sin cambios |
| **Mensajes de Commit** | Con trailer `Co-Authored-By` (opt-out) | Sin cambios |
| **i18n (claves por archivo)** | 547 × 6 (paridad total) | **742 × 6 (paridad total) — encabezados del changelog traducibles** |
| **Documentación** | 33 temas canónicos (29 completos + 4 parciales) | **37 temas canónicos (32 completos + 5 parciales) — 3 familias nuevas ×5, 8 actualizados** |
| **Suite de Pruebas** | 264 escenarios (17 archivos) | **791 escenarios recolectados (41 archivos: 32 + 9 SCM) — en_us: 787 passed / 2 failed (desactualizadas) / 2 skipped** |
| **Commits desde el informe** | 17 commits | **10 commits** |
| **PRs mergeados** | 8 PRs (#119–#135) + 2 PR_DESCs sin referencia | **5 PRs (#146, #151, #153, #155, #159)** |
| **Memory Index** | 32 patrones | **40 patrones (25 proyecto / 9 feedback / 3 referencia)** |
| **Informes de tareas** | 65 claude-code, 8 gemini | **84 claude-code (+15 en la ventana) y 5 gemini (+2; el conteo anterior incluía archivos del pre-restore del 2026-08-26)** |
| **Planes de desarrollo** | 59 | **80 (+21 en la ventana — specs, ADRs y sondeos)** |

---

## **🚧 Próximos Pasos**

* **Proveedor Anthropic Claude:** Soporte directo a la API de Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual en el Dashboard:** Añadir histogramas de tiempo y gráficos de tendencia de tokens en la TUI de métricas.
* **Pipeline de Release en GitHub Actions:** Automatización completa del build PyInstaller y envío de assets a GitHub Releases (la generación del changelog ya es local vía `gitpr release` — todavía falta la automatización en CI/CD).
* **Seed Local de `.gitpr/conf/`:** `--init` pasó a ser el wizard de SCM en esta ventana; el seed de plantillas de configuración local (smart-excludes, linter) sigue pendiente como subcomando propio o paso del wizard.
* **Más proveedores:** OpenAI directo, proveedores locales adicionales.
* **Extractor i18n en `sync_i18n.py`:** El regex trunca literales con concatenación implícita (`__("a " "b")`) — migrar a AST (el guard de `test_i18n.py` ya usa AST y no depende del script).
* **Corregir las Pruebas de Timeout Desactualizadas 🆕:** `tests/test_net_timeouts.py` (líneas ~99/117/137/149) aserta un default de 600s, pero el código usa 180s desde `681a7fa`; alinear también el docstring obsoleto de `config.py` (todavía menciona "default 600").
* **Reconciliar la Versión del Proyecto 🆕:** `CLAUDE.md` todavía dice "Current version: 0.0.37"; `__version__` está en 1.0.0; CHANGELOG.md registra `[v0.1.0] - 2026-09-07` (generado por la propia funcionalidad). Definir una convención única y actualizar CLAUDE.md.
* **Deuda del Índice del README 🆕:** Los bullets de las familias `suggested-reviewers` y `scm-multiforge` aún no están en el índice (decisión: solo `release-notes` en esta ronda).
* **Robustez de Locale en las Pruebas 🆕:** 4 pruebas son sensibles al locale pt_br de la máquina (copias OTA de `~/.gitpr/langs/`) — fijar `GITPR_LANG=en_us` en el setup o mockear `TRANSLATIONS` para que la suite esté totalmente verde en cualquier máquina/CI.

### ✅ Completados en esta ventana (2026-08-28 → 2026-09-08)

* ~~**Silencio de las tools MCP + DNS acotado**~~ — fix `681a7fa` (PR #146); default de `GITPR_AI_TIMEOUT` 600s → 180s.
* ~~**URLs del repositorio estandarizadas + prompts de issues localizados**~~ — `fa4bac1` (PR #151).
* ~~**SCM multi-forge completo**~~ — providers, factory, wizard `--init`, shim deprecado, docs ×5 y la suite `tests/scm/` (PR #153).
* ~~**Revisores sugeridos en el flujo de PR**~~ — flag, config, pruebas y docs ×5 (PR #155).
* ~~**Subcomando `gitpr release`**~~ — módulos, pruebas, plantillas de skill ×5, docs ×5, ADRs y spec (PR #159, commit `b0e5d92`).
* ~~**Próximos pasos de la familia release-notes**~~ — ayuda contextual `gitpr release -h` → docs; 48 claves i18n traducidas (742 × 6); encabezados del changelog traducibles; bullet de familia en el índice del README ×5 — ver [informe de tarea](../claude-code/reports/develop_natan/2026-09-08_release_notes_next_steps.md).

---

**Informe generado el:** 2026-09-08  
**Rama:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
