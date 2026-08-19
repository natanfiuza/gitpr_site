# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.12 (2026-08-19)**

## **📌 Visión General**

**GitPR** es una herramienta CLI (Command Line Interface) avanzada para la automatización de procesos Git mediante Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). Su objetivo principal es actuar como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita la deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.12):**
- **Bridge de Linters Externos + Asistente `--linter-setup`:** Integración con linters maduros (ESLint, PHP_CodeSniffer, Stylelint) ejecutados solo en las líneas modificadas del diff, parser de salida Checkstyle XML, nueva TUI de errores (`LinterApp`) e informe Markdown consolidado en `.gitpr/reports/linter/`. El asistente interactivo configura todo con presets remotos (`templates/gitpr.linter-presets.json`) versionados por el marcador `LINTER_PRESETS_VERSION`.
- **i18n Reparada y Completa:** El regex legacy del sync capturaba argumentos de call-site (`fg="cyan"`, `count=len(...)`) y generaba claves "mangled" que siempre caían al fallback en inglés. Reparadas 51 claves corruptas + 36 claves con `\n` literal en los 6 diccionarios; auditoría AST de 638 claves con **0 sin traducir y 0 mangled**; paridad total de **547 claves idénticas por archivo**; `__lang_version__` v0.0.13 → **v0.0.20** con pruebas de guarda.
- **Trailer de Coautoría:** Todo commit generado por IA recibe `Co-Authored-By: Gitpr-cli <gitpr@natanfiuza.dev.br>` — idempotente (no duplica, preserva trailers de terceros), oculto de la TUI (inyectado solo en la ejecución del commit) y con opt-out `GITPR_COAUTHOR=false`.
- **Fix del Hang del MCP Server:** Los 12 tool handlers eran síncronos y corrían inline en el event loop — cualquier llamada bloqueante (subprocess git, descarga OTA, SDK de IA) congelaba el servidor stdio completo. Nuevo decorador `_offload` (anyio worker threads), warm-import en el startup, `stdin=subprocess.DEVNULL` en todos los subprocesses y timeout duro de 10s en la descarga de smart-excludes. Nuevas pruebas e2e con JSON-RPC stdio real.
- **Correcciones del Modal de Error del Linter:** Botones "Commit with --no-verify" y "Abort" lado a lado (antes apilados y superpuestos); la elección no-verify ahora reanuda el flujo de commit (antes descartaba el modal y volvía al linter en bucle); push del modal diferido vía `call_next` al message pump de la app.
- **Dead Code Eliminado + Ajustes MCP:** Clase muerta `FileStageScreen` eliminada (elemento pendiente del informe anterior); `claude-code` listado en el help de `gitpr-mcp --install`; alias oculto `gitpr --mcp` documentado.
- **Documentación Multilingüe Expandida:** `docs/ARCHITECTURE.md` reescrito en EN canónico + 4 locales creados (18 temas de arquitectura, índice de 32 docs); nuevo tema `i18n_explanation` en 5 idiomas; READMEs y 4 temas actualizados.
- **Formato Consistente del Codebase:** Refactor Black-style en todo `src/` (comillas dobles, trailing commas, saltos de línea) — sin cambio funcional.
- **Skills Locales de Claude Code:** `status-report` (generación del informe de estado), `implement-fixes` (flujo de correcciones) y `caveman-commit` (mensajes de commit compactos — sustituyó el doc `docs/caveman-commit.md`).

- **Versión actual:** 0.0.37
- **Versión de los diccionarios de idioma:** v0.0.20
- **Versión de los scripts de hook:** v0.0.3
- **Publicación:** PyPI (`pip install gitpr-cli`) + GitHub Releases (binario standalone)
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 diccionarios)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal).
* **UI/Terminal:** Textual — TUI (Text User Interface) para chat interactivo, edición de issues, help screen, dashboard de métricas, PR Publisher y errores del linter (`LinterApp`).
* **Criptografía:** `cryptography.fernet` para protección local de claves de API y tokens de GitHub.
* **Configuración:** `python-dotenv`, `pyyaml` (para el linter estático).
* **Proveedores de IA:** Integración vía SDK oficial de Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`) y OpenAI SDK (`Ollama` local).
* **GitHub API:** `requests` (REST API vía PAT) — módulo `src/github_api.py` con `create_pull_request()`, `update_pull_request()`, `merge_pull_request()`.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — 12 herramientas anotadas, 15 recursos, 7 prompts; handlers descargados a threads vía `anyio`.
* **Pruebas:** Pytest + `unittest.mock` (17 archivos de prueba, 264 escenarios) + pruebas e2e del servidor MCP vía subprocess real (JSON-RPC stdio).
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
* **Smart Excludes con Dos Capas:** Filtro de pathspec inteligente con capa global (`~/.gitpr/conf/`) + local del proyecto (`./.gitpr/conf/`). Fusión en runtime (unión, deduplicada). Auto-seed del archivo local en la primera ejecución. Soporta 3 variables de entorno (`GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`).
* **Métricas con Seguimiento de Tiempo:** Inyección de `log_command_metric()` en todos los flujos con la duración en milisegundos (`duration_ms`) y lazy imports.
* **Resolución Centralizada de Salida:** Función `resolve_output_path()` que centraliza la lógica de directorios de salida — por defecto en `.gitpr/reports/{type}/` con fallback a rutas personalizadas del `.env`.
* **Detección de Merge en Curso:** Helper `is_merge_in_progress()` (verifica `git rev-parse -q --verify MERGE_HEAD`, silencioso y worktree-safe) — usado como defensa en profundidad contra hooks antiguos que llaman a la CLI durante un merge.
* **Staging con Error Real:** `stage_files()` devuelve la tupla `(success, error_message)` capturando el stderr/stdout del `git add` en fallos — el error real de git llega al usuario en lugar de ser engullido.
* **Trailer de Coautoría 🆕:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — helper idempotente que anexa `Co-Authored-By: Gitpr-cli <gitpr@natanfiuza.dev.br>` con separación de líneas en blanco; no duplica trailer existente y preserva `Co-Authored-By:` de terceros.
* **Descarga OTA con Timeout Duro 🆕:** `_download_smart_excludes()` ejecuta la petición en un hilo daemon con timeout de 10s — el timeout de urllib no limita la resolución DNS en Windows; ante un stall, recurre a la copia offline.
* **Subprocesses Blindados 🆕:** `stdin=subprocess.DEVNULL` en todos los `subprocess.run` — los procesos hijos ya no heredan el pipe JSON-RPC del servidor MCP (evita hang interactivo).
* **Salida de Linter Centralizada 🆕:** `OUTPUT_FILE_NAME_LINTER` mapeado a la carpeta `linter` en `_OUTPUT_FOLDER_MAP` — informes guardados en `.gitpr/reports/linter/`.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitectura de Plugins:** Sistema de extensibilidad que carga plugins del directorio `~/.gitpr/plugins/` aplicándose a **todos los proyectos**.
* **Plugins de Linter (`linter/`):** Archivos `.yml` con reglas regex adicionales fusionadas con el `.gitpr.linter.yml` local. 🆕 `load_external_linters()` también lee la sección `external_linters` de los plugins globales.
* **Plugins de Prompt MCP (`prompts/`):** Archivos `.md` que extienden el contexto del sistema con instrucciones específicas.
* **Factory Closures:** Funciones `get_linter_plugins` y `get_prompt_plugins` con closures para aislar estado entre sesiones.
* **Comando `--plugins`:** Lista todos los plugins globales instalados con sus tipos y rutas.
* **Documentación Multilingüe:** `docs/plugins-system.md` en 5 idiomas (EN, PT-BR, PT-PT, ES, FR).

### **3. Interfaz CLI y Configuración (`src/main.py` y `src/config.py`)**

* **Setup Inicial:** Detecta la primera ejecución, crea la carpeta `~/.gitpr/` y solicita interactivamente las claves de API, preferencias e idioma.
* **Enrutamiento de Comandos:** Gestiona todas las flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--linter-setup`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--no-publish`, `--no-edit`, `--base`, `--lang`, `--provider`, `--pre-save`, `--plugins`, `--status`, `--update`).
* **Comportamiento Predeterminado:** Ejecutar `gitpr` sin flags abre la TUI del PR Publisher.
* **Flags:**
  * `--publish`: sustituido por el flujo predeterminado — la TUI del PR Publisher se abre por defecto; los modificadores `--no-publish` / `--no-edit` / `--base` controlan el flujo.
  * `--no-publish`: Genera la descripción del PR y la guarda localmente sin abrir el editor interactivo.
  * `--no-edit`: Salta la TUI por completo — auto-commit (con validación del linter), auto-push y publica directamente en GitHub.
  * `--base <branch>`: Sobrescribe la rama de destino del Pull Request.
  * `--plugins`: Lista plugins globales instalados.
  * `--linter-setup` 🆕: Abre el asistente interactivo de configuración de linters externos (presets remotos + inyección en `.gitpr.linter.yml`).
  * `--version`: Muestra la versión actual de GitPR (vía `@click.version_option`).
* **Variables de Entorno:** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE`, `GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`, `OUTPUT_FILE_NAME_LINTER` 🆕, `GITPR_COAUTHOR` 🆕 (opt-out read-only, fuera de `DEFAULT_CONFIG`).
* **Ayuda Contextual:** `-h --flag` muestra documentación específica de la funcionalidad con un enlace directo (consciente del idioma) a GitHub. 🆕 Corregido para flags con guion (`--linter-setup`, `--no-publish`, `--no-edit`, `--no-unstaged-check`) — `param_name.replace('-', '_')`.
* **--lang:** Fuerza el idioma de la interfaz para la ejecución actual sin persistir el cambio.
* **--provider:** Fuerza el proveedor de IA (`gemini`, `deepseek`, `ollama`) para la ejecución actual.
* **--mcp:** Inicia el servidor MCP en transporte stdio para integración con editores — **12 herramientas anotadas + 15 recursos + 7 prompts**.
* **--install:** Asistente guiado de 4 pasos que descarga plantillas de skill, instala Git Hooks, configura MCP en los editores y valida claves de API. 🆕 Salida 100% traducida (10 mensajes hardcoded migrados a `__()` + 34 claves nuevas).
* **--metrics:** Sistema de telemetría local con alcance por repositorio: `--export`, `--purge`, `--dashboard` (TUI interactiva con escaneo de caché).
* **--status:** Lista archivos no commiteados categorizados (new/modified/deleted) — rápido, sin IA, sin red.
* **Informe del Linter Condicional 🆕:** El informe `.gitpr/reports/linter/` solo se genera cuando hay warnings o errores — los diffs limpios ya no crean archivos vacíos.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)**

* **Interfaz Interactiva Completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en el terminal.
* **6 Pantallas Modales:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Modal de Archivos Unstaged Mejorado:** Lista de archivos con altura fija (`height: 6`) y scroll vertical interno.
* **Bindings:** F1 (Help), F2 (Guardar .md local), F3 (Publicar vía GitHub API), Esc (Salir).
* **Flujo de Auto-Commit:** Linter → mensaje IA → confirmación → commit → push → publica PR.
* **Verificación de Archivos Unstaged:** Al iniciar, verifica `git status --porcelain` y ofrece un modal para seleccionar, saltar o cancelar.
* **Manejo de PR Existente:** Detecta PRs abiertos para la rama actual vía GitHub API y ofrece push o crear nuevo.
* **Auto-Upstream:** Detecta fallo de `git push` por falta de upstream e intenta automáticamente `--set-upstream origin <branch>`.
* **Detección de "Nothing to commit":** Trata `git commit` sin cambios como éxito.
* **Flujo de Merge:** Tras la creación/actualización del PR, ofrece opción de merge. Controlado por `GITPR_AUTO_MERGE`.
* **Manejo de Error de Merge:** Callbacks `_on_merge_success` / `_on_merge_failure` con modal de error para HTTP 405 (conflictos) y feedback visual post-TUI.
* **Selección Real de Archivos:** `StageFilesScreen.btn_stage` lee la selección directamente de `SelectionList.selected` — los toggles individuales de fila (clic/Enter) ahora se respetan; eliminado el diccionario manual `_selected` que quedaba desincronizado y el `git add` duplicado dentro de la TUI (staging único en `main.py`).
* **Dead Code Eliminado 🆕:** La clase borrador `FileStageScreen` (duplicado muerto de `StageFilesScreen`) fue eliminada junto con los imports huérfanos `get_unstaged_files`/`stage_files` — elemento de los "Próximos Pasos" del informe anterior completado.
* **Trailer de Coautoría Oculto 🆕:** El `Co-Authored-By:` ya no aparece en la pantalla de edición del mensaje (`CommitMessageScreen`) — se inyecta solo en la ejecución del commit, tras la confirmación del usuario. `_pending_commit_msg` permanece limpio para el fallback de título del PR.
* **Modal de Error del Linter Corregido 🆕:** Botones lado a lado en contenedor `Horizontal` con `height: auto` (antes apilados/superpuestos por el `1fr`); push del `LinterErrorScreen` diferido vía `call_next` al message pump de la app (antes el callback estaba conectado a la cola muerta del progress screen); `skip_linter` en `_start_progress_and_commit`/`_run_linter_and_commit` garantiza que el commit no-verify reanuda el flujo sin reejecutar el linter.

### **5. Módulo de API de GitHub (`src/github_api.py`)**

* **Funciones Compartidas:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` — encapsulando llamadas REST a la API de GitHub v3.
* **Autenticación vía PAT:** Token de acceso personal validado con `GET /user` antes de las operaciones.
* **Reutilización:** Funciones usadas tanto por la TUI de PR como por la TUI de issues.

### **6. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) en el git diff sin gastar cuotas de IA.
* **Reglas YAML:** Lee el archivo local `.gitpr.linter.yml` (creado vía `--skill`). Soporta regex de validación, ignorar comentarios e ignorar directorios específicos.
* **Plugins de Linter:** Reglas adicionales cargadas desde `~/.gitpr/plugins/linter/*.yml` y fusionadas con las reglas locales.
* **Bridge de Linters Externos 🆕:** `_run_external_linter()` ejecuta linters externos vía subprocess (`encoding="utf-8"`, `errors="replace"`, `stdin=DEVNULL`, `timeout=120`) y devuelve el stdout XML **independientemente del exit code** — los linters devuelven > 0 cuando encuentran problemas.
* **Parser Checkstyle XML 🆕:** `_parse_checkstyle_xml()` extrae errores (line/severity/message) con `xml.etree.ElementTree`, tolerando línea no numérica y XML inválido.
* **Cruce con el Diff 🆕:** El modo diff rastrea las líneas añadidas (`+`) y contabiliza solo errores del XML cuya línea fue modificada en el diff actual — los problemas preexistentes se ignoran.
* **Setup Solo-Externo 🆕:** Sin reglas regex pero con linters externos configurados, el escaneo sigue ejecutándose (antes se ignoraba silenciosamente).
* **Informe Consolidado 🆕:** `generate_linter_report_content()` consolida errores regex + externos en un único Markdown.
* **Plantilla multilingüe:** Plantillas del linter disponibles en 5 idiomas.
* **Integración en el Auto-Commit:** Se ejecuta automáticamente antes del commit en el flujo de publicación de PR.

### **7. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cifrado:** Genera una clave maestra `secret.key` en la carpeta `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data` y `decrypt_data` para proteger claves de API de IA y el PAT de GitHub.
* **Validación de Token de GitHub:** `validate_github_token()` con llamada ligera (`GET /user`).
* **Flujo de Auto-Reauth:** Si el token expira durante `gitpr -is`, captura 401, solicita un nuevo token y relanza la TUI preservando el borrador.

### **8. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Verifica en la API de GitHub Releases la versión más reciente, descarga el binario compilado y lo sustituye sin romper la ejecución en curso (con rollback).
* **Caché diaria:** Evita verificaciones repetidas el mismo día.
* **Verificación de conexión:** Socket `8.8.8.8:53` antes de cualquier operación de red.
* **Versionado Centralizado:** `__version__` (0.0.37), `__lang_version__` (v0.0.20), `__scripts_version__` (v0.0.3), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION` 🆕 (presets de linter actualizables sin release).

### **9. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Construida con Textual — historial de mensajes, entrada multi-línea, barra de estado con bindings visibles.
* **Memoria por Rama (`src/chat_memory.py`):** Historial de conversación persistido por rama, permitiendo continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear` — atajos para pair programming.
* **Auto-Patching (F5):** Extrae bloques de código sugeridos por la IA y los exporta a un archivo de patch.
* **Actualización de Diff (F2):** Recarga el `git diff` actual sin reiniciar la sesión.
* **Exportación de Sesión (F6):** Guarda el historial completo del chat para documentación.

### **10. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con soporte a placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`.
* **5 Idiomas, 6 Diccionarios:** en_us (predeterminado/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr (es/fr duplicados por familia).
* **Archivos Versionados:** `__lang_version__` (v0.0.20) controla la actualización de los paquetes de idioma (`langs/*.json`) — cadena de bumps v0.0.13 → v0.0.20 en esta ventana.
* **Cobertura:** 547 claves de traducción en cada uno de los 6 archivos — **paridad total de key sets**.
* **Reparación de Claves Corruptas 🆕:** 51 claves "mangled" (el regex legacy del sync capturaba kwargs de call-site como `fg="cyan"`) + 36 claves con `\n` literal doble-escapeado fueron reparadas en los 6 archivos — **0 mangled, 0 sin traducir** tras auditoría AST de 638 claves.
* **i18n Completa de `--install` 🆕:** Los 10 mensajes hardcoded del instalador MCP (`_run_install`, `_install_for_editor`) migrados a `__()` con kwargs nombrados; 34 claves nuevas traducidas.
* **Script de Sincronización Corregido 🆕:** `tests/sync_i18n.py` — nuevo `PATTERN` para el literal de `__()` (ya no captura el `)` del call-site), `ast.literal_eval` para secuencias de escape, índice `_live_key()` para migrar entradas legacy y guard de escaneo vacío (nunca sobrescribe con cero claves).
* **Caché con Indexación por Idioma:** Las respuestas de IA en caché incluyen el idioma actual en el keying MD5.
* **Claves Identidad por Diseño:** 11 claves mantenidas en EN intencionalmente (prompts de IA, marcadores universales `[OK]`/`[FAIL]`, términos técnicos).

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en background durante llamadas de IA mostrando caracteres braille con palabras de "pensamiento".
* **Delimitador:** Separador de frases por punto y coma (`;`), compatible con frases complejas que contienen comas.
* **Velocidad Adaptativa & Flickering:** Animación de descubrimiento de caracteres adaptada para frases largas y uso del ANSI `\033[K` para evitar artefactos visuales en el terminal.
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas.

### **12. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Medición de Duración:** Inyección de `duration_ms` (cronometraje de alta precisión vía `time.perf_counter()`) en `meta_raw` y `_telemetry_meta`.
* **Modo JSON & Parámetros Deterministas:** Salidas estructuradas con `temperature=0.0` y `top_p=0.1`.

### **13. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Keying por hash MD5 del diff y el prompt.
* **Indexación por Idioma:** El campo `lang` fue añadido al keying de caché.
* **Telemetría y Duración:** Persistencia de los campos `duration_ms` y `meta_raw` en archivos de caché.
* **Lectura para el Dashboard:** `scan_cache_files_for_dashboard()` lee todos los archivos de caché recursivamente.

### **14. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`), y Arqueología por Blame (`-b`).
* **Map-Reduce para Issues:** Cuando el contexto supera ~90k tokens, divide automáticamente en chunks y unifica los resultados.
* **TUI Interactiva:** Edición de borradores, atajo F2 (guardar local), F3 (publicar en GitHub) y F1 (help).
* **Manejo de 401:** Señalización de reautenticación sin cerrar la aplicación.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y autoría histórica de fragmentos de código con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos registrados vía `log_blame_metric()` con seguimiento de profundidad y número de commits analizados.

### **16. Servidor MCP e Invocación CLI Directa (`src/mcp_server.py`)**

* **12 Herramientas MCP Anotadas:** Herramientas para `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **15 Recursos + 7 Prompts Templatizados:** 35 archivos de plantilla en `templates/gitpr.prompt.*.md`.
* **Invocación CLI Directa:** El comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca cualquier tool MCP directamente sin iniciar el servidor stdio JSON-RPC.
* **Registry Pattern:** `_TOOL_FUNCS` mapea nombre de tool → callable; `_get_tool_registry()` fusiona con metadatos del catálogo.
* **Aislamiento del Stdout Real:** `_write_real_stdout()` escribe directamente en el `sys.__stdout__` original (guardado antes del monkey-patching), garantizando JSON puro en stdout.
* **Listado de Tools:** `gitpr-mcp --tool` (sin nombre) lista las 12 tools disponibles con firmas de parámetros.
* **Carga Automática del .env:** Claves de API disponibles automáticamente en modo CLI.
* **Offload del Event Loop 🆕:** Decorador `_offload` (`anyio.to_thread.run_sync`) aplicado a las 12 tools — los handlers síncronos ya no congelan el servidor stdio durante llamadas bloqueantes (causa raíz del hang de `run_linter` en Claude Code). `_TOOL_FUNCS` hace unwrap (`fn.__wrapped__`) manteniendo el modo `--tool` CLI síncrono.
* **Warm-Import en el Startup 🆕:** Hilo de pre-importación de `src.core` — la descarga OTA de smart-excludes nunca retrasa la primera llamada (import lock disputado en worker thread, nunca en el loop).
* **Help del `--install` Corregido 🆕:** `claude-code` ahora aparece en la lista de editores soportados del help (era aceptado en `choices` pero omitido en el texto).
* **Pruebas E2E 🆕:** `tests/test_mcp_server_e2e.py` levanta el servidor real como subprocess y habla JSON-RPC stdio (initialize, `run_linter`, `get_git_context` — cada respuesta asertada en 60s), hermético vía `GITPR_SKIP_SMART_EXCLUDES=1`.
* **Instalador Automático:** Configuración de editores soportados (VS Code, Cursor, Claude Code, Claude Desktop, Zed) con merge JSON inteligente.

### **17. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Alcance por Repositorio (Repo-Scope):** Etiqueta `📁 Repository: owner/repo` y filtrado estricto de eventos y datos de caché por proyecto.
* **Escaneo Asíncrono con Overlay:** Worker thread en background con widget `ProgressBar`.
* **Consolidación de Datos:** `load_cache_token_summary()` suma tokens de caché al totalizador.
* **Control de Estado de Caché:** Archivo de registro en `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Exportación Local:** Guardado de CSV/JSON en `./.gitpr/metrics/export/`.

### **18. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Alcance por Repositorio:** Todos los eventos indexados por `repo_name`.
* **Nuevos Eventos:** Eventos de listado de archivos unstaged y exportación de telemetría.
* **Eventos de Hook:** `log_hook_event()` para hooks de Git (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge).
* **Eventos de Linter y Blame:** `log_linter_metric()` y `log_blame_metric()`.
* **Exportación Local:** `--metrics --export` genera CSV y JSON en `./.gitpr/metrics/export/` con filtro por repositorio. 🆕 Ejemplos de exportación (CSV/JSON) versionados en el repositorio y `.gitignore` ajustado — la carpeta `.gitpr/reports/` ya no se ignora.
* **Limpieza:** `--metrics --purge` elimina todos los archivos de métricas locales con confirmación interactiva.

### **19. Sincronización de Hooks Git**

* **Versionado Independiente:** `__scripts_version__` (v0.0.3) controla la versión de los scripts de hook.
* **Detección Automática:** Compara la versión local con la más reciente y actualiza automáticamente.
* **Consciente del Idioma:** Descarga plantillas de hook correspondientes al idioma configurado.
* **Skip de Merge-Source:** La plantilla `prepare-commit-msg` (5 variantes de idioma) usa un case POSIX que salta las fuentes `message|merge|squash|commit` y verifica `.git/MERGE_HEAD` como belt-and-braces — los commits generados por git (`git pull`, `git merge`, `--amend`, `-c`/`-C`, `--squash`) preservan el mensaje original de git.

### **20. Bridge de Linters Externos y Asistente Interactivo (`src/linter_wizard.py`, `src/ui/linter_app.py`) 🆕**

* **Asistente `--linter-setup` 🆕:** Wizard interactivo que lista presets numerados (PHP_CodeSniffer, ESLint, Stylelint), muestra el comando de instalación nativa del linter e inyecta el bloque `external_linters` en `.gitpr.linter.yml` (con dedup y creación de la carpeta `.gitpr/skill/`).
* **Presets Remotos 🆕:** `templates/gitpr.linter-presets.json` servido desde GitHub con cadena de resolución (copia local actualizada → descarga → copia stale → fallback `_LINTER_PRESETS` embebido), versionado por el marcador `LINTER_PRESETS_VERSION` — nuevos linters entran sin release.
* **TUI de Errores del Linter 🆕:** `src/ui/linter_app.py` (Textual) muestra errores críticos y warnings cuando hay errores bloqueantes fuera de hooks/quiet; en hook/quiet imprime y hace `sys.exit(1)` (bloqueo de commit preservado).
* **Informe Markdown 🆕:** `generate_linter_report_content()` consolida errores regex + externos en `.gitpr/reports/linter/` con nombre configurable vía `OUTPUT_FILE_NAME_LINTER` — generado solo cuando hay violaciones.
* **Alcance Eficiente 🆕:** Los linters externos solo se ejecutan cuando hay archivos modificados con extensión compatible; YAML de configuración leído una vez por ejecución.
* **Cobertura de Pruebas 🆕:** 13 escenarios en `tests/test_external_linters.py` (parser XML, subprocess, cruce de diff, merge de config, generador de informe) + 4 pruebas de métricas con mock herméticos.

---

## **📊 Pruebas y Calidad**

| Archivo de Prueba | Escenarios | Enfoque |
|------------------|----------|------|
| `tests/test_core.py` | 31 | Flujos principales, git diff, generación de PR, timing, merge en curso, staging, trailer de coautoría |
| `tests/test_chat_backend.py` | 18 | Memoria de chat, persistencia, comandos slash |
| `tests/test_plugins.py` | 17 | Descubrimiento de plugins, merge de reglas de linter, prompts MCP |
| `tests/test_mcp_server.py` | 82 | Herramientas MCP, recursos, annotations, patching, CLI directo, decorador `_offload` |
| `tests/test_metrics.py` | 34 | Recolección, exportación local, alcance de repo, cache token summary, duration_ms |
| `tests/test_smart_excludes.py` | 13 | Filtro pathspec inteligente |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompt MCP y fallback de idioma |
| `tests/test_blame_metrics.py` | 4 | Métricas de blame: profundidad, commits, duración |
| `tests/test_linter_metrics.py` | 4 | Métricas de linter: errores, warnings, duración |
| `tests/test_thinking_words.py` | 3 | Carga y parsing con separador `;` |
| `tests/test_skill_command.py` | 3 | Descarga y validación de plantillas de skill |
| `tests/test_install_wizard.py` | 3 | Asistente interactivo de instalación |
| `tests/test_pre_save.py` | 3 | Flag --pre-save y payload JSON |
| `tests/test_external_linters.py` | 13 🆕 | Bridge Checkstyle: parser XML, subprocess, cruce de diff, informe |
| `tests/test_i18n.py` | 15 🆕 | Paridad entre idiomas, claves mangled, truncadas y huérfanas, claves del modal de linter |
| `tests/test_mcp_server_e2e.py` | 6 🆕 | Servidor MCP real vía subprocess + JSON-RPC stdio (initialize, run_linter, get_git_context) + modo `--tool` |
| `tests/test_pr_publish_linter_modal.py` | 4 🆕 | Modal de error del linter: layout lado a lado, abort, no-verify, flujo TUI completo con commit `no_verify=True` |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura i18n (claves huérfanas, extracción por literal) |

**Total:** 264 escenarios de prueba automatizados pasando (17 archivos de prueba). Ejecución completa verificada en esta versión: **264/264 passed en ~44s** — primera ejecución 100% verde en la máquina pt-BR (los 2 fallos preexistentes de locale en `test_external_linters.py` fueron corregidos fijando `TRANSLATIONS` a `{}` vía `mock.patch`). Nuevas pruebas: `TestExternalLinters` (13), `test_i18n.py` (15), `test_mcp_server_e2e.py` (6), `test_pr_publish_linter_modal.py` (4), `TestOffloadDecorator` (7) y `TestCoauthorTrailer` (5).

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n:** 547 claves de traducción en cada uno de los 6 diccionarios (+40 desde el informe anterior) con **paridad total de key sets** — auditoría AST de 638 claves usadas en código: 0 mangled, 0 sin traducir.
* **Documentos Actualizados 🆕 (todos en 5 idiomas):**
  - `docs/ARCHITECTURE.md` — reescrito en EN canónico + 4 locales creados (`ARCHITECTURE.pt_br.md`, `.pt_pt.md`, `.es_es.md`, `.fr_fr.md`): 18 temas de arquitectura, índice de documentación con 32 enlaces, nota del offload del MCP y del trailer de coautoría
  - `docs/i18n_explanation.md` 🆕 — nuevo tema sobre el sistema de internacionalización en 5 idiomas
  - `docs/linter-regras-customizadas.md` — nuevas secciones 5 (Bridge Checkstyle) y 6 (Informes Markdown) + bloque `external_linters` en la estructura YAML
  - `docs/commit-message-ia.md` — sección "Co-Author Signature" con ejemplo de consola actualizado
  - `docs/mcp-integration.md` — sección "Alternative Entry Point (`gitpr --mcp`)" + `claude-code` en la lista de editores
  - `docs/pull-request-publication.md` — nota de inyección del trailer por flujo + tabla de componentes corregida (`FileStageScreen` → `StageFilesScreen`)
  - `docs/providers-ia.md` — sincronizado
  - `README.md` + 4 locales — subsección "External Linters (Checkstyle Bridge)", línea "Linter Report" en la estructura de salida y bullet de la flag `--linter-setup`
  - `docs/caveman-commit.md` — eliminado: el tema se convirtió en la skill local `caveman-commit` (`.claude/skills/`)
* **Documentación en 5 idiomas:** 33 temas canónicos en `docs/` (29 con cobertura completa en los 5 idiomas; 4 temas PT-only: `como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`).
* **Skills locales de Claude Code:** `.claude/skills/` con `status-report` 🆕 (generación de este informe), `implement-fixes` 🆕 (flujo de correcciones) y `caveman-commit` 🆕 (mensajes de commit compactos) — además de las existentes `new-feature` y `reports-to-memory`.
* **Memory Index:** `.claude/memory/MEMORY.md` con 32 patrones en 3 categorías (21 de proyecto, 3 de referencia, 8 de feedback).
* **Informes de tareas:** `docs/claude-code/reports/` (65 en total; +15 nuevos: linter externo, claves i18n corruptas, staging i18n + dead code + docs MCP, skills, README, co-author, informe de linter condicional, ARCHITECTURE EN multilingüe, co-author en la TUI, hang del MCP, i18n del install wizard, i18n untranslated/mangled, modal de error del linter) y `docs/gemini/reports/` (8, sin nuevos en esta ventana).
* **Informes de estado:** `docs/reports/` (12 informes de estado).
* **Planes de desarrollo:** 59 archivos documentados en `docs/plans/` (+6 nuevos: linter externo, claves i18n, ARCHITECTURE multilingüe, hang del MCP ×2, correcciones del modal de linter).

---

## **🔄 Pipeline de Distribución**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → subida automatizada
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server:** Entry point `gitpr-mcp` vía `pyproject.toml`

---

## **📈 Evolución desde el Informe Anterior (v0.0.11)**

| Área | v0.0.11 (anterior) | v0.0.12 (actual) |
|------|-------------------|----------------|
| **Versión GitPR** | 0.0.36 | **0.0.37** |
| **Versión Idioma** | v0.0.13 | **v0.0.20** |
| **Versión Scripts Hook** | v0.0.2 | **v0.0.3** |
| **Proveedores IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 idiomas, 6 diccionarios (es/fr duplicados) |
| **Interfaz** | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI | **+ TUI de errores del linter (`LinterApp`) + asistente `--linter-setup`** |
| **Herramientas MCP** | 12 tools (handlers inline en el event loop) | **12 tools (handlers descargados a worker threads vía anyio + pruebas e2e stdio)** |
| **Flags CLI** | 26 flags | **27 flags (+ `--linter-setup`)** |
| **Variables de Entorno** | 16 vars | **23 vars (+ `OUTPUT_FILE_NAME_LINTER` en DEFAULT_CONFIG (22 keys) + `GITPR_COAUTHOR` read-only)** |
| **Linter** | Solo reglas regex (local + plugins) | **Regex + bridge Checkstyle (ESLint/PHPCS/Stylelint) con cruce por líneas del diff, wizard, TUI e informe Markdown** |
| **Mensajes de Commit** | Mensaje puro de la IA | **+ trailer `Co-Authored-By: Gitpr-cli` (idempotente, oculto de la TUI, opt-out `GITPR_COAUTHOR=false`)** |
| **i18n (claves por archivo)** | 507 en pt_BR (paridad incompleta) | **547 × 6 archivos con paridad total — 0 mangled, 0 sin traducir** |
| **Documentación** | 34 temas | **33 temas canónicos (29 con 5 idiomas completos) — 1 nuevo (i18n_explanation), 1 eliminado (caveman-commit → skill), 7 temas actualizados + ARCHITECTURE con 4 locales nuevos** |
| **Suite de Pruebas** | 214 escenarios (13 archivos) | **264 escenarios (17 archivos, +50) — primera ejecución 100% verde en la máquina pt-BR** |
| **Commits desde el informe** | 4 commits | **17 commits** |
| **PRs mergeados** | 2 PRs (#111, #114) | **8 PRs (#119, #122, #124, #127, #129, #131, #133, #135) + 2 PR_DESCs sin referencia (i18n mangled, modal de linter)** |
| **Memory Index** | 27 patrones | **32 patrones en 3 categorías (proyecto/referencia/feedback)** |
| **Informes de tareas** | 50 claude-code (+4 en la ventana) | **65 claude-code (+15) y 8 gemini (0 nuevos)** |
| **Planes de desarrollo** | 11+ | **59 (+6 en la ventana)** |

---

## **🚧 Próximos Pasos**

* **91 claves i18n aún ausentes:** Usadas en código vía `__()` pero ausentes de los diccionarios (descripciones de las tools MCP, strings de la TUI como "❌ Merge Conflict", mensajes de updater/ai_providers/github_api) — caen al fallback en inglés. Los prompts de IA deben permanecer en EN por diseño.
* **Guard `missing == 0` en test_i18n.py:** Extender las pruebas con una aserción que falle cuando entren nuevos `__()` sin entrada en el diccionario (hoy solo guarda paridad, mangled y claves identidad).
* **Merge `develop_natan` → `main`:** Publicar el bump `__lang_version__` v0.0.20 y las correcciones de la TUI a los usuarios — los `langs/*.json` corregidos ya están en `main` vía `e2f0fa0`; el marcador es lo que dispara el refresh OTA.
* **Sanity manual del flujo TUI real:** Una prueba end-to-end manual del PR Publisher con diff que rompe el linter (las pruebas headless mockean git/AI).
* **Pruebas para PR Publisher:** Cobertura restante para `pr_publish_app.py` y `github_api.py` (progreso: `test_pr_publish_linter_modal.py` cubre el flujo del modal de linter).
* **Proveedor Anthropic Claude:** Soporte directo a la API de Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual en el Dashboard:** Añadir histogramas de tiempo y gráficos de tendencia de tokens en la TUI de métricas.
* **Pipeline de Release en GitHub Actions:** Automatización completa del build PyInstaller y envío de assets a GitHub Releases.
* **Comando `--init` local:** Seed de `.gitpr/conf/` con plantillas de configuración local (smart-excludes, linter, etc.).
* **Más proveedores:** OpenAI directo, proveedores locales adicionales.
* **Hardening de subprocess y timeouts:** Cambiar el `shell=True` f-string de `_run_external_linter` por lista shlex/argv; limitar los timeouts del SDK de IA en `ai_providers.py` (~600s default); aplicar el patrón DNS-bounding a los urllib de `i18n.py`/`ai_providers.py`.
* **Linters externos en modo full-file:** Soporte a `external_linters` en `--input` y filtro por `file` en el XML de Checkstyle (hoy el cruce usa solo línea).
* **Documentar `LINTER_PRESETS_VERSION`:** Marcador de versión de los presets en `.env` (patrón Version Marker).
* **Referencias de docs rotas en HELP_MAP:** `chat-interativo.md` (archivo real: `understanding_chat_functionality.md`) y `metricas_analytics_dashboard.md` (real: `metricas-telemetria.md`) — pequeño fix.
* **CLAUDE.md desactualizado:** Aún declara versión 0.0.30 (real: 0.0.37) y menciona la flag `--publish` que ya no existe — ARCHITECTURE.md es la referencia más precisa.
* **Scripts legacy de i18n:** `scripts/` one-offs (`fix_pt_br.py`, `fix_pt_br_pass2.py`, `final_fix.py`, `_temp_check_i18n.py`, `generate_lang_files.py`) contienen tablas inertes de claves mangled — candidatos a eliminación/archivado.

---

**Informe generado el:** 2026-08-19  
**Rama:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
