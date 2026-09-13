# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.14 (2026-09-13)**

## **📌 Visión General**

**GitPR** es una herramienta CLI (Command Line Interface) avanzada para la automatización de procesos Git mediante Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). Su objetivo principal es actuar como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita la deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.14):**
- **Subcomando `gitpr config` — TUI de configuración interactiva:** Una pantalla master-detail (Textual) sobre `~/.gitpr/.env` con menú de categorías en la barra lateral, campos editados en línea, búsqueda global (`/`), `F2` para guardar, `Ctrl+R` para restaurar y `Esc` con confirmación de descarte. Un **schema declarativo** (`src/config_schema.py` — 12 categorías, 56 `ConfigField`, 8 avanzados) es la única fuente de verdad: el menú, los widgets, los valores predeterminados y la validación derivan de él, así que añadir un ajuste pasó a ser un cambio de **datos**, no de interfaz.
- **Sección Skills — la primera superficie con alcance de proyecto de la pantalla:** Un panel master-detail en línea que edita los archivos `.gitpr/skill/*.md` del proyecto (atómicamente, preservando CRLF/LF) en la misma pasada de `F2` que escribe el `.env`, con un único contador de cambios pendientes. El registry de tipos soportados (`SKILL_FILES_BY_TYPE`) se unificó en `src/config.py`, donde antes estaba repetido en 6+ sitios.
- **Registro general de uso (`src/usage_log.py`):** Una línea por comando en `~/.gitpr/logs/<uuid5-derivado-de-la-fecha>.log` — **un archivo por día** — escritura síncrona, nunca imprime y nunca lanza excepciones. Se invoca desde solo dos sitios (el callback raíz de `cli()` y el `main()` del servidor MCP), que juntos alcanzan todas las flags, ambos subcomandos y todas las rutas de `ctx.exit()`. Controlado por `GITPR_SHOW_LOGS` (nace activado en toda instalación existente).
- **Corrección del idioma de los Git hooks:** El idioma elegido por el usuario ahora se respeta de verdad — `HOOK_SCRIPT_SUFFIXES` mapea los códigos de interfaz (`es_es`, `fr_fr`) a los sufijos publicados (`.es`, `.fr`), `SCRIPTS_LANG` (la elección del usuario) se separó de `SCRIPTS_INSTALLED_LANG` (el estado en disco) para que la autosincronización pueda detectar un cambio de idioma, y `--lang` ya no se ignora.
- **Distribución exclusiva vía PyPI con puerta de actualización obligatoria:** El canal binario se descontinuó — ni generación, ni subida, ni fallback. `src/updater.py` se reescribió alrededor de una única fuente de verdad (la API de PyPI): `enforce_update_required()` bloquea la ejecución con **código de salida 1** cuando hay una versión más reciente publicada, imprimiendo `pip install --upgrade gitpr-cli`. Salieron la consulta a la API de GitHub Releases, la resolución de assets, el hot-swap con rollback y la dependencia de `pyinstaller`.
- **Registro de uso, telemetría y dogfooding:** El propio GitPR generó las release notes de esta ventana (`gitpr release` en `.gitpr/reports/release/`), usó el registro de uso para reconstruir la actividad y las skills locales `.gitpr.release.md` / `.gitpr.filereview.md` como instrucciones del sistema.
- **i18n ampliada a 955 claves:** +213 claves desde el informe anterior, cubriendo la TUI de configuración y las superficies de la puerta de PyPI; `__lang_version__` pasó de v0.0.23 a **v0.0.25** (cadena v0.0.23 → v0.0.24 → v0.0.25) y los 6 diccionarios mantienen **paridad total de key sets**.
- **Documentación Multilingüe Expandida:** 2 familias completas nuevas en 5 idiomas — `config-tui` y `usage-log` — y 7 temas actualizados (`ARCHITECTURE`, `auto-update`, `hooks-versioning`, `mcp-integration`, `skill-template`, `testar_sem_usar_pypi`, `version-markers`).
- **Eliminación de la clave muerta `PR_AUTO_PUBLISH`:** Demostrada inexistente en `src/` (cero ocurrencias) y eliminada de la lista de variables de entorno de `CLAUDE.md` y del `.env` del usuario; el §5 del documento de la TUI se corrigió en sus 5 versiones, porque prometía "fuera de la pantalla" para claves que en realidad aparecen de solo lectura bajo *Desconocidas*.
- **Salto de Versión:** `__version__` pasó de 1.0.0 a **1.1.0**; `CHANGELOG.md` registra `[1.1.0] - 2026-09-13`, generado por la propia funcionalidad de release.

- **Versión actual:** 1.1.0
- **Versión de los diccionarios de idioma:** v0.0.25
- **Versión de los scripts de hook:** v0.0.3
- **Publicación:** PyPI (`pip install gitpr-cli`) — **canal binario eliminado en esta ventana**
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 diccionarios)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal).
* **UI/Terminal:** Textual — TUI para chat interactivo, edición de issues, help screen, dashboard de métricas, PR Publisher, errores del linter (`LinterApp`) y **configuración (`ConfigApp`)** 🆕.
* **Criptografía:** `cryptography.fernet` para protección local de claves de API, tokens de GitHub y tokens SCM de los forges — los secretos editados en la TUI de configuración también se cifran antes de escribirse.
* **Configuración:** `python-dotenv`, `pyyaml` (para el linter estático) + **su propio schema declarativo (`src/config_schema.py`)** 🆕.
* **Proveedores de IA:** Integración vía SDK oficial de Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`) y OpenAI SDK (`Ollama` local).
* **APIs de Forge:** `requests` (REST) — capa de abstracción multi-forge en `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps); módulo legacy `src/github_api.py` mantenido como shim deprecado.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — 12 herramientas anotadas, 17 recursos, 7 prompts; handlers descargados a threads vía `anyio`.
* **Pruebas:** Pytest + `unittest.mock` (49 archivos de prueba — 40 en la raíz + 9 en `tests/scm/` —, 1060 escenarios recolectados) + pruebas e2e del servidor MCP vía subprocess real (JSON-RPC stdio).
* **Empaquetado:** setuptools/build (PyPI). **PyInstaller salió del proyecto en esta ventana** — ya no hay binario standalone.
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
* **Smart Excludes con Dos Capas:** Filtro de pathspec inteligente con capa global (`~/.gitpr/conf/`) + local del proyecto (`./.gitpr/conf/`). Fusión en runtime (unión, deduplicada). Auto-seed del archivo local en la primera ejecución. 🆕 `_load_smart_excludes()` acepta `force=` para redescargar bajo demanda desde la TUI de configuración.
* **Métricas con Seguimiento de Tiempo:** Inyección de `log_command_metric()` en todos los flujos con la duración en milisegundos (`duration_ms`) y lazy imports.
* **Resolución Centralizada de Salida:** Función `resolve_output_path()` que centraliza la lógica de directorios de salida — por defecto en `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`)**: `gitpr --init` — detecta el forge desde el remote origin, solicita extras por forge (org/project de Azure, username de Bitbucket), valida el token con `test_connection` (3 intentos, re-prompt en 401) y persiste `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` (Fernet) **solo en caso de éxito**.
* **Plantilla de Skill de Release (`ensure_release_skill_template()`)**: Descarga `templates/gitpr.release.*.md` en el primer uso de `gitpr release` (capa CLI, consciente del idioma, nunca sobrescribe; omitida con `--format json`).
* **Registry Compartido de Skills 🆕:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` salieron de `core.py` hacia `src/config.py` (la TUI no puede importar `core` en el nivel superior — arrastra los SDKs de IA); `get_skill_context()` ahora usa `skill_file_for()`.
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
* **Enrutamiento de Comandos:** Gestiona todas las flags y los **2 subcomandos** — `release` y `config` 🆕.
* **Comportamiento Predeterminado:** Ejecutar `gitpr` sin flags abre la TUI del PR Publisher.
* **Flags (35 opciones Click en la raíz, sin cambios en esta ventana):**
  * `--init`: Abre el wizard de configuración multi-forge de SCM (detección de forge + validación de token).
  * `--no-suggest-reviewers`: Desactiva la sugerencia de revisores en el flujo de publicación de PR.
  * `--no-publish`: Genera la descripción del PR y la guarda localmente sin abrir el editor interactivo.
  * `--no-edit`: Salta la TUI por completo — auto-commit, auto-push y publica directamente en el forge.
  * `--base <branch>`: Sobrescribe la rama de destino del Pull Request.
  * `--plugins`: Lista plugins globales instalados.
  * `--linter-setup`: Abre el asistente interactivo de configuración de linters externos.
  * `--version`: Muestra la versión actual de GitPR (vía `@click.version_option`).
* **Subcomando `config` 🆕:** 0 opciones — abre la TUI de configuración. Deliberadamente **no** llama a `setup_environment()`, para que ningún `click.prompt` compita con la TUI por el terminal. Import perezoso; epilog con `get_doc_url("config-tui.md")`.
* **Puerta de Actualización Obligatoria 🆕:** Al inicio del callback de `cli()`, **después** del handler de `--lang` (para que el mensaje salga en el idioma solicitado) y **antes** del despacho de flags (porque `--linter` retorna antes de `check_internet_connection()`) — sin ella, la mayoría de los comandos quedaría sin proteger. Omite `--quiet`, `--hook`, `--mcp`, `--update` y `-h/--help`; `--help`/`--version` son opciones *eager* de Click y nunca llegan al cuerpo.
* **Registro de Uso 🆕:** `log_usage()` al inicio del callback, antes de `if ctx.invoked_subcommand is not None: return` — las 35 flags, los 2 subcomandos y el `ctx.exit()` de `-h` pasan todos por ahí.
* **Variables de Entorno (39 claves en `DEFAULT_CONFIG`, sin cambios):** `GITPR_SKIP_UPDATE_CHECK` 🆕 (cualquier valor no vacío desactiva la puerta; usado por la suite de pruebas) y `GITPR_SHOW_LOGS` (declarada, sembrada como `"true"` y desactivada en `tests/conftest.py`) — esta última salió de las sombras y ganó su propio campo en la categoría General de la TUI.
* **Ayuda Contextual:** `-h --flag` muestra documentación específica de la funcionalidad con un enlace directo (consciente del idioma) a GitHub. Los subcomandos tienen su propio `epilog=` (párrafo Click `\b` para que la URL no se re-envuelva en ningún locale).
* **--lang:** Fuerza el idioma de la interfaz para la ejecución actual sin persistir el cambio — 🆕 y ahora también se aplica a la resolución de los scripts de hook.
* **--provider:** Fuerza el proveedor de IA (`gemini`, `deepseek`, `ollama`) para la ejecución actual.
* **--mcp:** Inicia el servidor MCP en transporte stdio para integración con editores — **12 herramientas anotadas + 17 recursos + 7 prompts**.
* **--install:** Asistente guiado de 4 pasos que descarga plantillas de skill, instala Git Hooks, configura MCP en los editores y valida claves de API.
* **--metrics:** Sistema de telemetría local con alcance por repositorio: `--export`, `--purge`, `--dashboard`.
* **--status:** Lista archivos no commiteados categorizados (new/modified/deleted) — rápido, sin IA, sin red.
* **Capa de Escritura del `.env` 🆕:** `read_env_file_values()` lee **solo el archivo** vía `dotenv_values` (inmune a `os.environ`), `save_config_values()` escribe con `set_key`, `remove_config_value()` con `unset_key` — los 9 puntos de llamada preexistentes de `set_key` quedaron intactos. `validate_ai_key()` sondea los SDKs de Gemini/DeepSeek con timeouts cortos y distingue una credencial rechazada (`401`/`403`) de una red inalcanzable.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)**

* **Interfaz Interactiva Completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en el terminal.
* **6 Pantallas Modales:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Revisores Sugeridos:** El flujo de publicación consulta al forge los revisores sugeridos y los ofrece en la TUI; selección controlada por `--no-suggest-reviewers`, `GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N` y `GITPR_REVIEWER_SUGGESTION_EXCLUDED`.
* **Bindings:** F1 (Help), F2 (Guardar .md local), F3 (Publicar vía forge), Esc (Salir).
* **Flujo de Auto-Commit:** Linter → mensaje IA → confirmación → commit → push → publica PR.
* **Verificación de Archivos Unstaged:** Al iniciar, verifica `git status --porcelain` y ofrece un modal para seleccionar, saltar o cancelar.
* **Manejo de PR Existente:** Detecta PRs abiertos para la rama actual vía API y ofrece push o crear nuevo.
* **Auto-Upstream:** Detecta fallo de `git push` por falta de upstream e intenta automáticamente `--set-upstream origin <branch>`.
* **Flujo de Merge:** Tras la creación/actualización del PR, ofrece opción de merge. Controlado por `GITPR_AUTO_MERGE`.

### **5. Módulo de API de GitHub (`src/github_api.py`)**

* **Shim Deprecado:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` y las demás funciones delegan en `src/infrastructure/scm/github_provider.py`; el módulo emite un `DeprecationWarning` y mantiene las tuplas legacy `(ok, data, status)` — ningún código nuevo debe importarlo.

### **6. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) en el git diff sin gastar cuotas de IA.
* **Reglas YAML:** Lee el archivo local `.gitpr.linter.yml` (creado vía `--skill`).
* **Plugins de Linter:** Reglas adicionales cargadas desde `~/.gitpr/plugins/linter/*.yml`.
* **Bridge de Linters Externos:** Ejecuta ESLint/PHPCS/Stylelint sobre las líneas modificadas del diff, parser de Checkstyle XML y cruce por línea.
* **Informe Consolidado:** `generate_linter_report_content()` consolida errores regex + externos en `.gitpr/reports/linter/` — generado solo cuando hay violaciones.
* 🆕 `load_linter_presets()` acepta `force=` para redescargar los presets desde la TUI.

### **7. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cifrado:** Genera una clave maestra `secret.key` en la carpeta `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data` y `decrypt_data` para proteger claves de API de IA, PATs de GitHub y tokens SCM de los forges (`GITPR_SCM_TOKEN_ENCRYPTED`).
* **Validación Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — valida el token contra el forge configurado con bucle de reautenticación en 401 preservando el borrador; el token legacy de GitHub (`GITHUB_TOKEN_ENCRYPTED`) sigue funcional hasta que se ejecute `--init`.
* **Secretos en la TUI de Configuración 🆕:** Los campos `KIND_SECRET` se editan en un campo enmascarado, **nunca** muestran el valor en claro y se cifran con Fernet antes de escribirse — ninguna ruta vuelve a leer el secreto hacia la pantalla. `GITPR_SCM_TOKEN` es `read_only` y su descripción apunta a `gitpr --init` como el único camino que debería escribirlo.

### **8. Auto-Updater (`src/updater.py`) — reescrito en esta ventana 🆕**

* **PyPI como Única Fuente:** `get_latest_remote_version()` consulta siempre `https://pypi.org/pypi/gitpr-cli/json`, devuelve una **cadena** de versión y escribe la caché diaria **sin** el campo `download_url`. Perdió el parámetro `is_compiled` y toda la rama de la API de GitHub Releases.
* **Puerta Obligatoria (`enforce_update_required()`):** Devuelve `True` (tras imprimir ambas versiones y el comando pip) cuando la versión publicada es más reciente; devuelve `False` cuando está al día, cuando la versión remota es **desconocida (offline — el usuario no tendría forma de actualizar)** o cuando la verificación está desactivada. Devolver un `bool` en lugar de llamar a `sys.exit` internamente mantiene la función testeable.
* **`check_and_update()`:** Reescrita para `--update` — solo consulta e **informa**, nunca instala.
* **Eliminados:** `GITHUB_API_URL`, `_perform_hot_swap()` (renombraba el `.exe` a `.old`, descargaba el nuevo, hacía rollback), `print_update_notice()` y sus 5 puntos de llamada, el bloque de limpieza de `.old` en `main.py` y la dependencia `pyinstaller` del `Pipfile`. El `icon.ico` fue eliminado.
* **Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` (cualquier valor no vacío) — no se anuncia al usuario como funcionalidad; existe para la suite de pruebas y la automatización offline.
* **Caché Diaria:** Evita verificaciones repetidas el mismo día.
* **Versionado Centralizado:** `__version__` (**1.1.0**), `__lang_version__` (**v0.0.25**), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* **Defectos que este cambio cierra:** el antiguo `urlretrieve` no tenía timeout ni checksum, y `main.py` borraba el backup `.old` en la siguiente ejecución sin condiciones — una descarga truncada era irrecuperable.

### **9. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Construida con Textual — historial de mensajes, entrada multi-línea, barra de estado con bindings visibles.
* **Memoria por Rama (`src/chat_memory.py`):** Historial de conversación persistido por rama, permitiendo continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear` — atajos para pair programming.
* **Auto-Patching (F5), Actualización de Diff (F2), Exportación de Sesión (F6).**

### **10. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con soporte a placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`.
* **5 Idiomas, 6 Diccionarios:** en_us (predeterminado/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Archivos Versionados:** `__lang_version__` (**v0.0.25**) controla la actualización de los paquetes de idioma (`langs/*.json`) — cadena de bumps v0.0.23 → v0.0.24 → v0.0.25 en esta ventana.
* **Cobertura:** **955 claves** de traducción en cada uno de los 6 archivos — **paridad total de key sets** (+213 desde el informe anterior).
* **Snapshot del Entorno (`AMBIENT_ENV_KEYS`) 🆕:** `frozenset(os.environ)` capturado en `i18n.py` **inmediatamente antes** del `load_dotenv()` a nivel de módulo. Fue la raíz de un defecto silencioso de la TUI: como `config.py` importa de `i18n.py`, todo el `.env` ya estaba dentro de `os.environ` antes de que la pantalla existiera, así que el badge "⚠ en el entorno" confirmaba tautológicamente que la clave está en el archivo. Medido: **0 campos con el badge** en un proceso limpio, **40 de 49** tras importar la pantalla. Corregido en los tres puntos de uso.
* **Claves Renombradas/Eliminadas 🆕:** `Detected language: {lang}` → `Hooks language: {lang}`; 6 claves obsoletas de actualización/binario eliminadas y 3 nuevas de la puerta de PyPI añadidas.
* **Caché con Indexación por Idioma:** Las respuestas de IA en caché incluyen el idioma actual en el keying MD5.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en background durante llamadas de IA mostrando caracteres braille con palabras de "pensamiento".
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas. 🆕 `_load_thinking_words()` / `reload_thinking_words()` aceptan `force=`.

### **12. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Modo JSON & Parámetros Deterministas:** Salidas estructuradas con `temperature=0.0` y `top_p=0.1`; fallback automático entre los proveedores configurados.

### **13. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Keying por hash MD5 del diff y el prompt, con indexación por idioma.
* **Telemetría y Duración:** Persistencia de los campos `duration_ms` y `meta_raw` en archivos de caché.
* **Lectura para el Dashboard:** `scan_cache_files_for_dashboard()` lee todos los archivos de caché recursivamente.

### **14. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`), y Arqueología por Blame (`-b`).
* **Publicación Multi-Forge:** F3 crea el issue en el forge **configurado** (`GITPR_SCM_PROVIDER`) vía `provider.create_issue` — Azure DevOps lanza `ScmNotSupportedError` (los Work Items dependen de la plantilla de proceso).
* **Map-Reduce para Issues:** Cuando el contexto supera ~90k tokens, divide automáticamente en chunks y unifica los resultados.
* **Manejo de 401:** Señalización de reautenticación sin cerrar la aplicación.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y autoría histórica de fragmentos de código con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registrados vía `log_blame_metric()` con seguimiento de profundidad y número de commits analizados.

### **16. Servidor MCP e Invocación CLI Directa (`src/mcp_server.py`)**

* **12 Herramientas MCP Anotadas:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **17 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release}` + `linter://config` + `prompt://list` + 7 prompts.
* **Invocación CLI Directa:** El comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca cualquier tool MCP directamente sin iniciar el servidor stdio JSON-RPC. `gitpr-mcp --list` imprime el registry completo como JSON.
* **Aislamiento del Stdout Real:** `_write_real_stdout()` escribe directamente en el `sys.__stdout__` original, garantizando JSON puro en stdout — la razón por la que el registro de uso **nunca** imprime.
* **Offload del Event Loop:** Decorador `_offload` (`anyio.to_thread.run_sync`) aplicado a las 12 tools — los handlers síncronos no congelan el servidor stdio.
* **Registro de Uso 🆕:** El `main()` del servidor llama a `log_usage()` — el console script `gitpr-mcp` nunca carga `main.py`, así que este es el único punto que lo alcanza.
* **Pruebas E2E:** `tests/test_mcp_server_e2e.py` levanta el servidor real como subprocess y habla JSON-RPC stdio.

### **17. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Alcance por Repositorio (Repo-Scope):** Etiqueta `📁 Repository: owner/repo` y filtrado estricto por proyecto.
* **Escaneo Asíncrono con Overlay:** Worker thread en background con widget `ProgressBar`.
* **Consolidación de Datos:** `load_cache_token_summary()` suma tokens de caché al totalizador.
* **Exportación Local:** Guardado de CSV/JSON en `./.gitpr/metrics/export/` (artefactos del 2026-09-12 y del 2026-09-13 versionados en esta ventana).

### **18. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Alcance por Repositorio:** Todos los eventos indexados por `repo_name`.
* **Eventos de Hook, Linter y Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Exportación y Limpieza:** `--metrics --export` (CSV/JSON) y `--metrics --purge` con confirmación interactiva.

### **19. Sincronización de Idiomas de los Hooks Git — corregida en esta ventana 🆕**

* **Versionado Independiente:** `__scripts_version__` (v0.0.3) controla la versión de los scripts de hook; detección y actualización automáticas.
* **Mapeo de Sufijos (`HOOK_SCRIPT_SUFFIXES`) 🆕:** Los códigos de interfaz (`es_es`, `fr_fr`) ahora se traducen a los sufijos realmente publicados (`.es`, `.fr`) — antes el idioma elegido por el usuario simplemente se ignoraba.
* **Elección vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`) 🆕:** `SCRIPTS_LANG` es la elección del usuario; `SCRIPTS_INSTALLED_LANG` es lo que está en disco. Separados, la autosincronización puede **detectar un cambio de idioma** en vez de asumir que ya está instalado.
* **`effective_hook_lang()` 🆕:** Resuelve el idioma efectivo de los hooks; `--lang` ya no se descarta en esa ruta (cambio de comportamiento documentado).
* **Skip de Merge-Source:** La plantilla `prepare-commit-msg` salta las fuentes `message|merge|squash|commit` — los commits generados por git preservan el mensaje original.

### **20. Bridge de Linters Externos y Asistente Interactivo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Asistente `--linter-setup`:** Wizard interactivo con presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e inyección del bloque `external_linters` en `.gitpr.linter.yml`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` servido desde GitHub con la cadena de resolución local → descarga → stale → fallback embebido.
* **TUI de Errores del Linter:** `src/ui/linter_app.py` (Textual) muestra errores críticos y warnings; en modo hook/quiet imprime y hace `sys.exit(1)`.
* **Informe Markdown:** Consolidado en `.gitpr/reports/linter/` — solo cuando hay violaciones.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstracción Única (`ScmProvider` ABC):** `base.py` define el contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. y `ScmProviderError(provider, http_status, message)` — `http_status` 0 = fallo de red); un provider concreto por forge en `github_provider.py`, `gitlab_provider.py`, `bitbucket_provider.py`, `azure_devops_provider.py`.
* **Registry y Factory:** `resolve_scm_provider()` selecciona por `GITPR_SCM_PROVIDER` (predeterminado `github` — migración cero, fallback del token legacy de GitHub intacto); `detect_provider_from_remote()` identifica el forge desde la URL de origin.
* **Direccionamiento de Repositorios:** `parse_repo_ref(remote_url) -> RepoRef(raw, workspace, name, provider)` — workspace = owner de GitHub / namespace de GitLab (subgrupos) / workspace de Bitbucket / display `{org}/{project}` de Azure.
* **Fail-Fast por Forge:** Azure DevOps exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket exige `GITPR_SCM_USERNAME` (App Password = Basic auth user+token); `create_issue` en Azure lanza `ScmNotSupportedError`.
* **Publicación de Release:** `provider.create_release()` usado por `gitpr release --publish` (GitHub crea el tag en la rama predeterminada; GitLab exige que el tag exista).
* **Artefactos:** Glosario + ADR-001 en `docs/plans/`; familia `docs/scm-multiforge.*.md` en 5 idiomas; pruebas: 9 archivos, 265 escenarios.

### **22. Subcomando `gitpr release` — Changelog / Release Notes**

* **Flujo:** `git log` entre `--since` (predeterminado: último tag alcanzable, o el primer commit) y `HEAD` → clasificación Conventional Commits → bump semántico sugerido (`--version <x.y.z>` lo sobrescribe) → ensamblado del changelog → resumen ejecutivo de IA opcional → *anteposición* a `CHANGELOG.md`. La generación local es el comportamiento predeterminado — nada se publica ni se toca sin solicitarlo.
* **Clasificador (`src/commit_classifier.py`):** Clasifica los commits por tipo Conventional Commits (feat/fix/refactor/docs/chore/etc.) con un parser tolerante.
* **Builder con Secciones Traducibles (`src/changelog_builder.py`):** Secciones "✨ Features", "🐛 Bug Fixes", "⚠️ Breaking Changes", "Summary", "Contributors" etc. renderizadas vía `__()` en runtime (siguen a `--lang`).
* **Bump Semántico (`src/version_bump.py`):** Sugiere la próxima versión a partir de los tipos clasificados (major para breaking, minor para feat, patch para fix) y valida los destinos `x.y.z`.
* **Publicación:** `--publish` crea el release en el forge configurado (con confirmación explícita); `--draft` lo crea como borrador (GitHub; GitLab no tiene concepto de draft); `--format markdown|json` para salida estructurada; `--force` para reescribir. **6 opciones en el subcomando.**
* **Plantilla de Skill:** En el primer uso descarga `templates/gitpr.release.*.md` (5 idiomas) vía `ensure_release_skill_template()` — nunca sobrescribe.
* **Dogfooding en esta ventana:** `.gitpr/reports/release/` recibió las release notes generadas por el propio comando (`develop_natan_20260910144814`, `...145040`, `...145125`) y `.gitpr/skill/.gitpr.release.md` + `.gitpr.filereview.md` pasaron a existir como skills locales del proyecto.
* **Artefactos:** familia `docs/release-notes.*.md` (5 idiomas), spec en `docs/plans/`, ADR-002 y ADR-003, glosario de release notes.

### **23. Subcomando `gitpr config` — TUI de Configuración 🆕**

* **Pantalla Master-Detail (`src/ui/config_app.py`):** Categorías a la izquierda, campos de la categoría a la derecha, editados en línea. Cabecera con búsqueda (`/`) y un contador de cambios pendientes (`● N sin guardar`); pie con `F1 Ayuda · F2 Guardar · ^R Restaurar · / Buscar · Esc`. `General` es siempre la primera entrada del menú.
* **Schema Declarativo (`src/config_schema.py`) — la única fuente de verdad:** 12 categorías (General, Proveedores de IA, Pull Request, Revisión de Código, Issue, Blame, Linter, Release, SCM / Forge, Filtros de Diff, Skills, Avanzado) y **56 `ConfigField`**, de los cuales **8 son avanzados**. Cada campo declara su categoría, el tipo de widget (`bool`/`int`/`str`/`enum`/`template`/`path`/`secret`/`version`/`words`), `show_if`, validadores, marcadores de versión y acciones de descarga. Las etiquetas son literales `__()` para el escáner de i18n.
* **Filtrado por Contexto (`show_if`):** `GEMINI_*` / `DEEPSEEK_*` / `OLLAMA_*` aparecen según el `DEFAULT_AI_PROVIDER` seleccionado (nada seleccionado → ningún bloque); `GITHUB_TOKEN_ENCRYPTED` aparece con proveedor vacío o `github`; `GITPR_SCM_USERNAME` bajo `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` bajo `[Azure DevOps]`. Cambiar el `Select` re-filtra el panel **de inmediato, sin F2** (solo `on_select_changed` dispara `_render_view()`, y solo para claves en `VISIBILITY_CONTROLLERS`, derivadas del schema y no escritas a mano).
* **Búsqueda Global (`/`):** Coincide con la clave o la etiqueta en todas las categorías e **ignora el filtro de visibilidad** — buscar `deepseek` con Gemini seleccionado encuentra los campos, para permitir prellenarlos. Un campo sucio se guarda independientemente de la visibilidad (`_build_plan` es ciego a la visibilidad, por diseño).
* **Validación en Dos Capas:** **Offline** (tipo, enum, plantilla con un placeholder conocido y `{datetime}` obligatorio) bloquea `F2` con un error en línea; **online** (solo para credenciales cambiadas en la sesión) corre en un worker con timeout de 10s y solo bloquea ante `401`/`403` — un fallo de red permite guardar igualmente.
* **Restaurar (`Ctrl+R`):** Elimina la línea del `.env` en lugar de reescribir el valor predeterminado; **`Esc`** con cambios pendientes pide confirmación; la categoría **Desconocidas** preserva las claves fuera del schema en modo de solo lectura (no ofrece eliminación, por diseño).
* **Sección Skills — la única con alcance de proyecto 🆕:** Panel master-detail en línea (lista de skills a la izquierda, editor de texto a la derecha) que edita los `.gitpr/skill/*.md` del proyecto resueltos desde el directorio de invocación, escribiendo atómicamente y preservando CRLF/LF. `F2` escribe el `.env` y los archivos de skill **en la misma pasada**, con un único contador de cambios pendientes.
* **Descargas Forzadas:** Botones que fuerzan la redescarga de smart-excludes, traducciones, presets de linter y thinking words, vía un parámetro `force=` encadenado por los loaders.
* **Módulo Ligero de Enlaces (`src/doc_links.py`) 🆕:** `doc_url()` salió de `core.py` para que la UI pueda obtener el enlace a la documentación sin importar `core`/SDKs de IA. Cada categoría del schema apunta a su documento canónico.
* **Pruebas:** 5 archivos nuevos — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Artefactos:** `docs/config-tui.*.md` en 5 idiomas, plan `docs/plans/20260912_config_tui.md`, glosario `glossary-config-tui.md` (8 términos) y la encuesta de grill.
* **Deuda conocida:** `gitpr -h config` abre la TUI e ignora `-h` — la puerta `if ctx.invoked_subcommand is not None: return` corre antes del bloque `help_flag`; corregirlo cambiaría el comportamiento de `-h` para **todos** los subcomandos (documentado en `docs/config-tui.md`).

### **24. Registro General de Uso (`src/usage_log.py`) 🆕**

* **Una Línea por Comando:** Escribe en `~/.gitpr/logs/<uuid5>.log`, **un archivo por día**, con el comando, los argumentos, el repositorio, el usuario y el timestamp. Responde a "¿qué ejecuté realmente, y cuándo?".
* **Nombre Derivado de la Fecha:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` en lugar de aleatorio — un nombre aleatorio exigiría un contador o un archivo de estado para saber cuál es el de hoy, y dos procesos concurrentes podrían discrepar. Derivado de la fecha, el mismo día resuelve siempre al mismo nombre y los comandos concurrentes simplemente añaden al mismo archivo.
* **Escritura Síncrona (decisión explícita):** A diferencia de `log_local_metric`, que usa un hilo daemon y por tanto pierde la escritura si el proceso termina antes — inaceptable para un registro que promete registrar *todos* los comandos.
* **Nunca Imprime:** El servidor MCP reserva la stdout para JSON-RPC; un `print` accidental corrompería el protocolo. El módulo tampoco lanza excepciones nunca.
* **Un Solo Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` en lugar de los tres idiomáticos — ~40 ms en vez de ~150 ms en Windows, en *cada* ejecución.
* **Su Propio `_repo_label()`:** Sin reutilizar `get_repo_name()` de `core.py` (regex hardcodeada a `github.com`, devolvería `unknown/repo` en GitLab/Bitbucket/Azure — un defecto nuevo en un proyecto que acaba de ganar multi-forge) y sin `parse_repo_ref`, que es un método de provider y exigiría construir un provider (token, `requests`) en cada comando.
* **Control:** `GITPR_SHOW_LOGS` (predeterminado `"true"` — nace activado en toda instalación existente, sin migración); desactivado en `tests/conftest.py`.
* **Artefactos:** `docs/usage-log.*.md` en 5 idiomas; `tests/test_usage_log.py` (27 escenarios).

---

## **📊 Pruebas y Calidad**

| Archivo de Prueba | Escenarios | Enfoque |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por rango de líneas en un archivo |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidad, commits, duración |
| `tests/test_changelog_builder.py` | 15 | Builder de changelog: secciones, encabezados traducibles, contribuidores |
| `tests/test_chat_backend.py` | 19 | Memoria de chat, persistencia, comandos slash |
| `tests/test_commit_classifier.py` | 23 | Clasificación Conventional Commits (tipos, parser tolerante) |
| `tests/test_config_app.py` | 78 🆕 | TUI de configuración: montaje, cambio de categoría, seguimiento de cambios pendientes, F2 bloqueado, Ctrl+R, búsqueda, secretos |
| `tests/test_config_cli.py` | 9 🆕 | Registro del subcomando `config`, `-h`, import perezoso, stdout limpia |
| `tests/test_config_schema.py` | 42 🆕 | Cobertura de `DEFAULT_CONFIG`, sin duplicados, categorías/kinds, `advanced` solo en Avanzado |
| `tests/test_config_store.py` | 22 🆕 | Round-trip sobre un `.env` temporal, comentarios y orden preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuración de revisores sugeridos (claves y defaults) |
| `tests/test_config_validation.py` | 40 🆕 | Tipos, enums, plantillas, `validate_ai_key()` con SDK mockeado (401 vs. red vs. ollama) |
| `tests/test_core.py` | 49 | Flujos principales, git diff, generación de PR, timing, staging, coautoría, idioma de los hooks |
| `tests/test_diff_parser.py` | 15 | Parser de diff por líneas/hunks |
| `tests/test_external_linters.py` | 33 | Bridge Checkstyle: parser XML, subprocess, cruce de diff, informe |
| `tests/test_i18n.py` | 20 | Paridad entre idiomas (955×6), claves ausentes/huérfanas, identidad |
| `tests/test_install_wizard.py` | 3 | Asistente interactivo de instalación |
| `tests/test_issue_engine.py` | 4 | Borrador estructurado de issue |
| `tests/test_linter_metrics.py` | 4 | Métricas de linter: errores, warnings, duración |
| `tests/test_linter_presets.py` | 5 🆕 | Presets de linter: resolución y redescarga forzada |
| `tests/test_main_suggest_reviewers.py` | 6 | Flag `--no-suggest-reviewers` en la CLI y en la ayuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompt MCP y fallback de idioma |
| `tests/test_mcp_server.py` | 86 | Herramientas MCP, recursos, annotations, patching, CLI directo, offload |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real vía subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Recolección, exportación local, alcance de repo, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Timeouts de red/IA — **2 aserciones desactualizadas (600s)** |
| `tests/test_plugins.py` | 17 | Descubrimiento de plugins, merge de reglas de linter, prompts MCP |
| `tests/test_pr_publish_app.py` | 42 | TUI del PR Publisher: pantallas, flujos, revisores sugeridos |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de error del linter: abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save y payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opciones, help con epilog documentado |
| `tests/test_release_engine.py` | 27 | Motor de release: rango de commits, CHANGELOG, publicación |
| `tests/test_reviewer_suggestion.py` | 15 | Lógica de sugerencia de revisores (ranking, exclusión, top-N) |
| `tests/test_skill_command.py` | 10 | Descarga y validación de plantillas de skill |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` con `quiet=True`, fallbacks, registry de skills |
| `tests/test_smart_excludes.py` | 15 | Filtro pathspec inteligente y redescarga forzada |
| `tests/test_suggest_reviewers.py` | 14 | Revisores sugeridos en el flujo de PR (integración) |
| `tests/test_thinking_words.py` | 5 | Carga, parsing con separador `;` y recarga forzada |
| `tests/test_updater.py` | 23 🆕 | Puerta de PyPI: parsing de versión, caché diaria, fetch, decisiones de la puerta, cableado en la CLI |
| `tests/test_usage_log.py` | 27 🆕 | Registro de uso: nombre derivado de la fecha, escritura síncrona, silencio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semántico: major/minor/patch, destinos y validación |
| `tests/scm/test_contract.py` | 38 | Contrato `ScmProvider`: firmas, dataclasses, errores |
| `tests/scm/test_github_provider.py` | 57 | Provider de GitHub: REST, headers, PRs, issues, releases |
| `tests/scm/test_gitlab_provider.py` | 41 | Provider de GitLab: API v4, namespace, releases |
| `tests/scm/test_bitbucket_provider.py` | 39 | Provider de Bitbucket: Basic auth, workspace |
| `tests/scm/test_azure_devops_provider.py` | 43 | Provider de Azure DevOps: org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim deprecado `github_api` → delega en el provider |
| `tests/scm/test_init_wizard.py` | 10 | Wizard `--init`: detección de forge, validación, persistencia |
| `tests/scm/test_release_publish.py` | 8 | Publicación de release por forge (GitHub/GitLab) |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura i18n (scaffold; nunca ejecutado) |

**Total:** 1060 escenarios recolectados en 49 archivos de prueba (40 en la raíz + 9 en `tests/scm/`; **+269** desde el informe anterior, con **8 archivos nuevos**). Ejecución completa en esta máquina con `GITPR_LANG=en_us`: **1055 passed / 3 failed / 2 skipped / 33 subtests** en ~123s.

**Notas de calidad de esta versión:**
- **2 fallos reales (pruebas desactualizadas, heredadas):** `test_net_timeouts.py` sigue asertando el default de 600s para `GITPR_AI_TIMEOUT`, pero el código usa **180s** desde el fix `681a7fa`. Es el mismo punto que ya estaba en los Próximos Pasos del informe anterior y **sigue abierto**.
- **1 fallo de locale (nuevo, no es regresión):** `test_core.py::TestHooksLanguage::test_the_language_chosen_with_the_lang_flag_is_honoured` aserta `i18n.CURRENT_LANG == "pt_br"` — pasa con el locale pt-BR de la máquina y falla con `GITPR_LANG=en_us`. Es la suite del idioma de los hooks introducida en esta ventana.
- **Sensibilidad de locale bajó de 4 a 1:** los 4 fallos ambientales de la ventana anterior (`test_chat_backend::test_api_exception`, `test_main_suggest_reviewers::test_flag_appears_in_contextual_help` y `test_suggest_reviewers` ×2) **ahora pasan** con `GITPR_LANG=en_us` — ya no son el problema, pero la causa raíz (pruebas que asumen un idioma) persiste, solo migró a otro archivo.
- `tests/conftest.py` ahora fija `GITPR_SHOW_LOGS=false` y `GITPR_SKIP_UPDATE_CHECK=true` — la suite ni escribe en el registro de uso ni queda bloqueada por la puerta de actualización.

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n:** **955 claves** de traducción en cada uno de los 6 diccionarios (+213 desde el informe anterior) con **paridad total de key sets**. Las ~200 claves nuevas cubren la TUI de configuración y las superficies de la puerta de PyPI; 6 claves obsoletas de actualización/binario fueron eliminadas, 3 nuevas añadidas y 2 de ayuda reescritas. `__lang_version__` pasó v0.0.23 → v0.0.24 → **v0.0.25**, disparando la redescarga OTA de las traducciones.
* **Fuentes de traducción en lockstep:** una clave nueva debe existir en el código (fuente), en `langs/pt_br.json` (**lista maestra**), en los dicts FR/ES de `scripts/sync_all_langs.py` (segunda fuente) y en los valores curados de `scripts/fix_mangled_i18n_keys.py` (tercera fuente, leída por `tests/test_i18n.py`); la aserción `len(CLEAN_KEYS)` pasó de 50 a **49**.
* **Temas Nuevos 🆕 (2, ambos en 5 idiomas):**
  - `docs/config-tui.md` — la pantalla `gitpr config`: disposición, lectura de los valores, edición y guardado, validación en dos capas, búsqueda, lo que queda fuera de alcance y una sección para desarrolladores
  - `docs/usage-log.md` — registro de uso: dónde viven los archivos, el nombre derivado de la fecha, el formato de la línea y lo que no hace
* **Temas actualizados en esta ventana (todos re-sincronizados en los 5 idiomas):** `docs/ARCHITECTURE.md`, `docs/auto-update.md` (reescrito para el modelo solo-PyPI), `docs/hooks-versioning.md` (idioma efectivo de los hooks), `docs/mcp-integration.md`, `docs/skill-template.md`, `docs/testar_sem_usar_pypi.md` (sin binario) y `docs/version-markers.md` (marcadores nuevos).
* **Documentación en 5 idiomas:** **39 temas canónicos** en `docs/` — **34 con cobertura completa en los 5 idiomas** (+2 desde el informe anterior) y 5 temas parciales/PT-only (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers`).
* **Skills Locales de Claude Code:** `.claude/skills/` con **29 skills** (las del proyecto — `status-report`, `implement-fixes`, `caveman-commit`, `new-feature`, `code-review`, `wizard`, `grilling` etc. — más el kit `mattpocock-skills`; el informe anterior listaba solo 5). La memoria ganó `i18n-sync-canonicos-roundtrip.md` en el commit de la TUI de configuración.
* **Memory Index:** `.claude/memory/MEMORY.md` con 40 patrones (conteo sin cambios en esta ventana).
* **Informes de tareas:** `docs/claude-code/reports/develop_natan/` (**90** en total; **+6** en la ventana — TUI de configuración, 5 fixes de UI + registro de uso, layout/secciones/descargas, sección Skills, limpieza de `PR_AUTO_PUBLISH` y eliminación del binario) y `docs/gemini/reports/develop_natan/` (5 archivos; ninguno nuevo).
* **Informes de estado:** `docs/reports/` (13 informes; este es el 14º).
* **Planes de desarrollo:** 89 archivos en `docs/plans/` (+9 en la ventana — los planes de la TUI de configuración, los fixes de pantalla, la sección Skills, la limpieza de `PR_AUTO_PUBLISH`, la eliminación del binario y el glosario `glossary-config-tui`) + 3 archivos en `docs/survey/`.

---

## **🔄 Pipeline de Distribución**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Actualización obligatoria:** la ejecución consulta PyPI al arrancar y **bloquea con código de salida 1** si hay una versión más reciente, imprimiendo `pip install --upgrade gitpr-cli`; la verificación se cachea por día y `--update` solo informa
3. **GitHub Releases:** **eliminado** — ni PyInstaller, ni asset `.exe`, ni hot-swap; `pyinstaller` salió del `Pipfile` y `icon.ico` fue eliminado
4. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml` (siempre instalaba vía pip, así que no se vio afectado)
5. **MCP Server:** Entry point `gitpr-mcp` vía `pyproject.toml`
6. **Plantillas e Idiomas OTA:** `templates/` y `langs/*.json` servidos desde GitHub (main) — el bump `v0.0.25` renueva las copias locales en `~/.gitpr/langs/` una vez publicado

---

## **📈 Evolución desde el Informe Anterior (v0.0.13)**

| Área | v0.0.13 (anterior) | v0.0.14 (actual) |
|------|-------------------|-----------------|
| **Versión GitPR** | 1.0.0 | **1.1.0** (CHANGELOG.md con encabezado `[1.1.0] - 2026-09-13`) |
| **Versión Idioma** | v0.0.23 | **v0.0.25** (vía v0.0.24) |
| **Versión Scripts Hook** | v0.0.3 | **v0.0.3** |
| **Proveedores IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 idiomas, 6 diccionarios | 5 idiomas, 6 diccionarios |
| **Interfaz** | CLI + TUIs (Issues, Chat, Dashboard, PR Publisher, LinterApp) + wizard `--init` + `gitpr release` | **+ TUI de configuración `gitpr config` (12 categorías, 56 campos, sección Skills) + registro general de uso** |
| **Herramientas MCP** | 12 tools / 17 recursos / 7 prompts | 12 tools / 17 recursos / 7 prompts (+ `log_usage()` en el entry point) |
| **Flags CLI** | 35 opciones en la raíz + subcomando `release` (6) | 35 opciones en la raíz + `release` (6) + **`config` (0 opciones)** |
| **Variables de Entorno** | 39 claves en `DEFAULT_CONFIG` | **39 claves** (+ `GITPR_SKIP_UPDATE_CHECK`; `GITPR_SHOW_LOGS` salió de las sombras y pasó a ser un campo de la TUI) |
| **Linter** | Regex + bridge Checkstyle (wizard/TUI/informe) | Sin cambios (+ redescarga forzada de presets desde la TUI) |
| **Git Hooks** | El idioma de los scripts ignoraba `--lang` | **Corregido: `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG` vs. `SCRIPTS_INSTALLED_LANG`, `effective_hook_lang()`** |
| **Mensajes de Commit** | Con trailer `Co-Authored-By` (opt-out) | Sin cambios |
| **i18n (claves por archivo)** | 742 × 6 (paridad total) | **955 × 6 (paridad total) — +213 claves** |
| **Documentación** | 37 temas canónicos (32 completos + 5 parciales) | **39 temas canónicos (34 completos + 5 parciales) — 2 familias nuevas ×5, 7 actualizados** |
| **Distribución** | PyPI + GitHub Releases (binario PyInstaller) | **PyPI exclusivo — binario, hot-swap y `pyinstaller` eliminados** |
| **Suite de Pruebas** | 791 escenarios (41 archivos) | **1060 escenarios (49 archivos: 40 + 9 SCM) — en_us: 1055 passed / 3 failed (2 desactualizadas + 1 de locale) / 2 skipped** |
| **Commits desde el informe** | 10 commits | **2 commits** (`bf9f1b9`, `f108c4c`) |
| **PRs mergeados** | 5 PRs (#146, #151, #153, #155, #159) | **2 PRs (#162, #164)** |
| **Memory Index** | 40 patrones | **40 patrones** (+ `i18n-sync-canonicos-roundtrip.md`) |
| **Informes de tareas** | 84 claude-code, 5 gemini | **90 claude-code (+6 en la ventana) y 5 gemini** |
| **Planes de desarrollo** | 80 | **89 (+9 en la ventana — TUI de configuración, fixes de pantalla, Skills, limpieza y eliminación del binario)** |

---

## **🚧 Próximos Pasos**

* **Proveedor Anthropic Claude:** Soporte directo a la API de Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual en el Dashboard:** Añadir histogramas de tiempo y gráficos de tendencia de tokens en la TUI de métricas.
* **Pipeline de Release en GitHub Actions:** Automatización completa del build y de la subida a PyPI (la generación del changelog ya es local vía `gitpr release`, y el canal binario ya no existe — solo falta la automatización en CI/CD).
* **Seed Local de `.gitpr/conf/`:** El sembrado de plantillas de configuración local (smart-excludes, linter) sigue pendiente como subcomando propio o paso del wizard; la TUI de configuración ya ofrece las **descargas** de esos archivos, pero no el seed del proyecto.
* **Más proveedores:** OpenAI directo, proveedores locales adicionales.
* **Extractor i18n en `sync_i18n.py`:** El regex trunca literales con concatenación implícita (`__("a " "b")`) — migrar a AST (el guard de `test_i18n.py` ya usa AST y no depende del script).
* **Corregir las Pruebas de Timeout Desactualizadas:** `tests/test_net_timeouts.py` (líneas ~99/117/137/149) aserta un default de 600s, pero el código usa 180s desde el fix `681a7fa`; alinear también el docstring obsoleto de `config.py` (todavía menciona "default 600"). **Punto heredado, sigue abierto.**
* **Reconciliar la Versión del Proyecto:** `CLAUDE.md` todavía dice "Current version: 0.0.37" mientras `__version__` está en 1.1.0 y el CHANGELOG registra `[1.1.0] - 2026-09-13`. Definir una convención única y actualizar el CLAUDE.md. **Punto heredado, sigue abierto.**
* **Deuda del Índice del README:** los bullets de las familias `suggested-reviewers`, `scm-multiforge` **y ahora `config-tui` y `usage-log`** no están en el índice — la deuda creció en esta ventana.
* **Robustez de Locale en las Pruebas:** 1 prueba es sensible al locale pt_br de la máquina (`test_core.py::TestHooksLanguage`) — fijar `GITPR_LANG=en_us` en el setup o mockear `TRANSLATIONS` para que la suite esté 100% verde en cualquier máquina/CI.
* **`gitpr -h config` ignora `-h` 🆕:** el subcomando abre la TUI en lugar de mostrar la ayuda — la puerta `if ctx.invoked_subcommand is not None: return` corre antes del bloque `help_flag`. Corregirlo cambiaría el comportamiento de `-h` para **todos** los subcomandos, así que requiere una decisión.
* **Sección Smart Exclude en la TUI 🆕:** de los 12 puntos reportados tras usar la pantalla, el punto 10 (la sección *Smart Exclude*) es el único entregable aún no iniciado — el esquema está en los próximos pasos del informe de tarea.
* **Deudas registradas en el plan de la TUI de configuración 🆕:** `DEFAULT_CONFIG` quedó redundante con el schema; el banner de apertura no lista `--dashboard`, `--init`, `--base` ni `--plugins`; `LinterApp` no desactiva la command palette.

### ✅ Completados en esta ventana (2026-09-08 → 2026-09-13)

* ~~**Subcomando `gitpr config` con TUI master-detail**~~ — schema declarativo, capa de escritura del `.env`, validación en dos capas, búsqueda, docs ×5 (PR #162).
* ~~**Sección Skills en la TUI**~~ — edición de los `.gitpr/skill/*.md` del proyecto con escrituras atómicas, registry `SKILL_FILES_BY_TYPE` unificado en `config.py` (PR #162).
* ~~**Registro general de uso (`GITPR_SHOW_LOGS`)**~~ — `src/usage_log.py`, un archivo por día, escritura síncrona, silencioso (PR #162).
* ~~**Corrección del idioma de los Git hooks**~~ — `--lang` ya no se ignora; `SCRIPTS_LANG` separado de `SCRIPTS_INSTALLED_LANG` (PR #162).
* ~~**Corrección del badge de entorno en la TUI**~~ — `AMBIENT_ENV_KEYS` capturado antes del `load_dotenv` a nivel de módulo; el badge ahora significa lo que promete (PR #162).
* ~~**Limpieza de la clave muerta `PR_AUTO_PUBLISH`**~~ — eliminada de `CLAUDE.md` y del `.env` del usuario; el §5 del documento de la TUI corregido en sus 5 versiones.
* ~~**Distribución exclusiva vía PyPI + puerta de actualización obligatoria**~~ — `enforce_update_required()`, `GITPR_SKIP_UPDATE_CHECK`, eliminación del hot-swap, del binario y de `pyinstaller`; `docs/auto-update.md` reescrito ×5 (PR #164).
* ~~**i18n: +213 claves y cadena v0.0.23 → v0.0.25**~~ — paridad total de key sets en los 6 diccionarios, con las tres fuentes de traducción en lockstep.
* ~~**Documentación de las 2 familias nuevas**~~ — `config-tui` y `usage-log` en 5 idiomas, más 7 temas actualizados.

---

**Informe generado el:** 2026-09-13  
**Rama:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
