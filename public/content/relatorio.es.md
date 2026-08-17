# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.11 (2026-08-15)**

## **📌 Visión General**

**GitPR** es una herramienta CLI (Command Line Interface) avanzada para la automatización de procesos Git mediante Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). Su objetivo principal es actuar como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita la deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.11):**
- **Corrección de selección y errores en el staging (`stage_files`):** La TUI de staging ahora lee la selección real de `SelectionList.selected` (toggles individuales respetados) y `stage_files()` devuelve `(success, error_message)` — los fallos de `git add` muestran el error real de git en lugar de un falso mensaje de éxito. El staging ahora ocurre una sola vez por flujo (antes estaba duplicado entre el modal y `check_unstaged_files`).
- **Omisión del mensaje de IA en commits generados por git:** Los hooks `prepare-commit-msg` (5 variantes de idioma) ahora omiten todas las fuentes generadas por git (`merge`, `squash`, `amend`, `commit` — antes solo `message`), con verificación belt-and-braces de `.git/MERGE_HEAD`. `git pull`/`git merge` ya no corrompen el `.git/MERGE_MSG` con mensaje de IA. Nuevo helper `is_merge_in_progress()` en el core y guard en modo hook. `__scripts_version__` → v0.0.2 con auto-sync de los hooks.
- **Traducciones de estado de archivo:** Labels de estado ("Modified", "Deleted", "New") traducidos en los paquetes es, es_es, fr, fr_fr, pt_br y pt_pt — la cobertura pt_BR subió a 507 claves.
- **Documentación multilingüe ampliada y sincronizada:** `docs/pr-descricao-padrao.md` reescrito en EN canónico + 4 locales con sección de publicación (modos de ejecución, atajos TUI, PAT); `docs/mcp-integration.md` sincronizado en los 5 idiomas (2 herramientas faltantes en la tabla + nueva subsección de recursos de prompt); `docs/git-hooks-locais.md` documenta el skip de merge-source en los 5 idiomas.
- **Nueva plantilla MCP:** `templates/gitpr.mcp-jsonrpc-calls.md` — referencia de llamadas JSON-RPC para las herramientas MCP.

- **Versión actual:** 0.0.36
- **Versión de los diccionarios de idioma:** v0.0.13
- **Versión de los scripts de hook:** v0.0.2
- **Publicación:** PyPI (`pip install gitpr-cli`) + GitHub Releases (binario standalone)
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **Framework de CLI:** Click (para comandos, flags y formato de terminal).
* **UI/Terminal:** Textual — TUI (Text User Interface) para chat interactivo, edición de issues, pantalla de ayuda, dashboard de métricas y PR Publisher.
* **Criptografía:** `cryptography.fernet` para la protección local de API keys y tokens de GitHub.
* **Configuración:** `python-dotenv`, `pyyaml` (para el linter estático).
* **Proveedores de IA:** Integración vía el SDK oficial Google GenAI (`gemini-2.5-flash`), el SDK de OpenAI (`DeepSeek`) y el SDK de OpenAI (local `Ollama`).
* **API de GitHub:** `requests` (REST API vía PAT) — módulo `src/github_api.py` con `create_pull_request()`, `update_pull_request()`, `merge_pull_request()`.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial de Anthropic para Model Context Protocol) — 12 herramientas anotadas, 15 recursos, 7 prompts.
* **Pruebas:** Pytest + `unittest.mock` (13 archivos de prueba, 214 escenarios).
* **Empaquetado:** PyInstaller (binario standalone) + setuptools/build (PyPI).
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` para ejecución en pipelines.

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación estructurada:** Se comunica con el LLM solicitando salida estrictamente JSON.
* **Map-Reduce (diffs gigantes):** Cuando el diff supera ~90k tokens, lo divide automáticamente en lotes por archivo (`split_diff_into_chunks`), procesa cada parte (Map) y unifica los resúmenes (Reduce). Soporta PRs, commits e Issues.
* **Tokenizer local:** `tokenizer.json` para una estimación precisa de tokens antes de enviar a la IA.
* **Estimación de tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()` con fallback al tokenizer local.
* **Optimización nativa de Git:** Flags `-U1`, `-w`, `-M`, `-B` en los comandos `get_git_diff` y `get_git_full_diff` para reducir contexto inútil.
* **Pre-Save (`--pre-save`):** Flag oculto de depuración que guarda el payload completo (instrucción del sistema + prompt) como JSON antes de cada llamada a la IA.
* **Smart Excludes con dos capas:** Filtro pathspec inteligente con una capa global (`~/.gitpr/conf/`) + una capa local del proyecto (`./.gitpr/conf/`). Fusión en tiempo de ejecución (unión, sin duplicados). Auto-siembra del archivo local en el primer uso. Soporte de 3 variables de entorno (`GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`).
* **Métricas con seguimiento de tiempo:** Inyección de `log_command_metric()` en todos los flujos pasando la duración en milisegundos (`duration_ms`), con lazy imports.
* **Resolución centralizada de salida:** Función `resolve_output_path()` que centraliza la lógica del directorio de salida — por defecto `.gitpr/reports/{type}/` con fallback a rutas personalizadas desde `.env`.
* **Detección de merge en curso 🆕:** Helper `is_merge_in_progress()` (verifica `git rev-parse -q --verify MERGE_HEAD`, silencioso y worktree-safe) — usado como defensa en profundidad contra hooks antiguos que llaman a la CLI durante un merge.
* **Staging con error real 🆕:** `stage_files()` ahora devuelve la tupla `(success, error_message)`, capturando el stderr/stdout de `git add` en fallos — el error real de git llega al usuario en lugar de ser engullido.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitectura de plugins:** Sistema de extensibilidad que carga plugins desde el directorio `~/.gitpr/plugins/`, aplicándolos a **todos los proyectos**.
* **Plugins de linter (`linter/`):** Archivos `.yml` con reglas regex adicionales fusionadas con el `.gitpr.linter.yml` local.
* **Plugins de prompts MCP (`prompts/`):** Archivos `.md` que extienden el contexto del sistema con instrucciones específicas.
* **Clausuras de fábrica:** Funciones `get_linter_plugins` y `get_prompt_plugins` con clausuras para aislar el estado entre sesiones.
* **Comando `--plugins`:** Lista todos los plugins globales instalados con sus tipos y rutas.
* **Documentación multilingüe:** `docs/plugins-system.md` en 5 idiomas (EN, PT-BR, PT-PT, ES, FR).

### **3. Interfaz CLI y Configuración (`src/main.py` y `src/config.py`)**

* **Configuración inicial:** Detecta el primer uso, crea la carpeta `~/.gitpr/` y solicita interactivamente las API keys, preferencias e idioma.
* **Enrutamiento de comandos:** Gestiona todos los flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--publish`, `--no-publish`, `--no-edit`, `--base`, `--lang`, `--provider`, `--pre-save`, `--plugins`).
* **Comportamiento predeterminado:** Ejecutar `gitpr` sin flags abre la TUI del PR Publisher.
* **Flags:**
  * `--publish`: Abre la TUI interactiva para revisar, editar y publicar el PR.
  * `--no-publish`: Genera la descripción del PR y la guarda localmente sin abrir el editor interactivo.
  * `--no-edit`: Omite la TUI por completo — auto-commit (con validación del linter), auto-push y publicación directa en GitHub.
  * `--base <branch>`: Sobrescribe la rama objetivo del Pull Request.
  * `--plugins`: Lista los plugins globales instalados.
  * `--version`: Muestra la versión actual de GitPR (vía `@click.version_option`).
* **Variables de entorno:** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE`, `GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`.
* **Ayuda contextual:** `-h --flag` muestra documentación específica de la funcionalidad con un enlace directo (consciente del idioma) a GitHub.
* **--lang:** Fuerza el idioma de la interfaz para la ejecución actual sin persistir el cambio.
* **--provider:** Fuerza el proveedor de IA (`gemini`, `deepseek`, `ollama`) para la ejecución actual.
* **--mcp:** Inicia el servidor MCP en transporte stdio para la integración con editores — **12 herramientas anotadas + 15 recursos + 7 prompts**.
* **--install:** Asistente guiado de 4 pasos que descarga plantillas de skill, instala Git Hooks, configura MCP en los editores y valida las API keys.
* **--metrics:** Sistema de telemetría local con ámbito por repositorio: `--export`, `--purge`, `--dashboard` (TUI interactiva con escaneo de caché).
* **--status:** Lista los archivos sin commitear categorizados (new/modified/deleted) — rápido, sin IA, sin red.
* **Guard de merge en modo hook 🆕:** En el flujo de commit en modo hook, si `is_merge_in_progress()` devuelve True la ejecución termina silenciosamente con exit 0 antes de cualquier diff o llamada de IA.
* **Feedback real de staging 🆕:** `check_unstaged_files()` verifica el resultado de `stage_files()` en los 3 puntos de llamada (resultado de la TUI, auto-stage de pr/issue, auto-stage de commit) y muestra "❌ Failed to stage files: {error real de git}" en fallos.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)**

* **Interfaz interactiva completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en la terminal.
* **6 pantallas modales:** `CommitConfirmScreen`, `FileStageScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Modal de archivos no-stageados mejorado:** Lista de archivos con altura fija (`height: 6`) y scroll vertical interno.
* **Bindings:** F1 (Ayuda), F2 (Guardar .md local), F3 (Publicar vía API de GitHub), Esc (Salir).
* **Flujo de auto-commit:** Linter → mensaje de IA → confirmación → commit → push → publicación del PR.
* **Verificación de archivos no-stageados:** Al inicio, verifica `git status --porcelain` y ofrece un modal para seleccionar, omitir o cancelar.
* **Manejo de PR existente:** Detecta PRs abiertos para la rama actual vía la API de GitHub y ofrece hacer push o crear uno nuevo.
* **Auto-Upstream:** Detecta fallas de `git push` por falta de upstream y reintenta automáticamente con `--set-upstream origin <branch>`.
* **Detección de "Nothing to commit":** Trata `git commit` sin cambios como un éxito.
* **Flujo de merge:** Después de la creación/actualización del PR, ofrece una opción de merge. Controlado por `GITPR_AUTO_MERGE`.
* **Manejo de errores de merge:** Callbacks `_on_merge_success` / `_on_merge_failure` con modal de error para HTTP 405 (conflictos) y feedback visual posterior a la TUI.
* **Selección real de archivos 🆕:** `StageFilesScreen.btn_stage` lee la selección directamente de `SelectionList.selected` — los toggles individuales de fila (clic/Enter) ahora se respetan; eliminado el diccionario manual `_selected` que se desincronizaba y el `git add` duplicado dentro de la TUI (staging único en `main.py`).

### **5. Módulo de API de GitHub (`src/github_api.py`)**

* **Funciones compartidas:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` — encapsulando llamadas REST a la API v3 de GitHub.
* **Autenticación PAT:** Token de acceso personal validado con `GET /user` antes de las operaciones.
* **Reutilización:** Funciones usadas tanto por la TUI de PR como por la TUI de issues.

### **6. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter offline:** Analiza estáticamente las líneas añadidas (`+`) en el git diff sin gastar cuota de IA.
* **Reglas YAML:** Lee el archivo `.gitpr.linter.yml` local (creado vía `--skill`). Soporta regex de validación, ignorar comentarios e ignorar directorios específicos.
* **Plugins de linter:** Reglas adicionales cargadas desde `~/.gitpr/plugins/linter/*.yml` y fusionadas con las reglas locales.
* **Plantilla multilingüe:** Plantillas del linter disponibles en 5 idiomas.
* **Integración con auto-commit:** Se ejecuta automáticamente antes del commit en el flujo de publicación de PR.

### **7. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografía:** Genera una clave maestra `secret.key` en la carpeta `~/.gitpr/`.
* **Protección de tokens:** `encrypt_data` y `decrypt_data` para proteger las API keys de IA y el PAT de GitHub.
* **Validación del token de GitHub:** `validate_github_token()` con una llamada ligera (`GET /user`).
* **Flujo de reautenticación automática:** Si el token expira durante `gitpr -is`, captura el 401, solicita un nuevo token y relanza la TUI conservando el borrador.

### **8. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Verifica la última versión vía la API de GitHub Releases, descarga el binario compilado y lo reemplaza sin interrumpir la ejecución en curso (con rollback).
* **Caché diario:** Evita verificaciones repetidas el mismo día.
* **Verificación de conexión:** Socket `8.8.8.8:53` antes de cualquier operación de red.
* **Versionado centralizado:** `__version__` (0.0.36), `__lang_version__` (v0.0.13), `__scripts_version__` (v0.0.2), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`.

### **9. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI completa:** Construida con Textual — historial de mensajes, entrada multilínea, barra de estado con bindings visibles.
* **Memoria por rama (`src/chat_memory.py`):** Historial de conversación persistido por rama, permitiendo continuidad entre sesiones.
* **Slash commands:** `/explain`, `/tests`, `/optimize`, `/clear` — atajos para pair programming.
* **Auto-Patching (F5):** Extrae los bloques de código sugeridos por la IA y los exporta a un archivo de patch.
* **Refresco de diff (F2):** Recarga el `git diff` actual sin reiniciar la sesión.
* **Exportación de sesión (F6):** Guarda el historial completo del chat para documentación.

### **10. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema inspirado en Laravel:** Función `__()` con soporte de placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección automática:** Detecta el idioma del sistema operativo en el primer uso y lo guarda en `GITPR_LANG`.
* **5 idiomas:** en_us (predeterminado/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Archivos versionados:** `__lang_version__` (v0.0.13) controla la actualización de los paquetes de idioma (`langs/*.json`).
* **Cobertura:** 507 claves de traducción en pt_BR.
* **Traducciones de estado de archivo 🆕:** Claves "Modified", "Deleted" y "New" traducidas en los 6 paquetes no ingleses (es, es_es, fr, fr_fr, pt_br, pt_pt).
* **Caché con indexación de idioma:** Las respuestas de IA en caché incluyen el idioma actual en la clave MD5.
* **Script de sincronización:** `tests/sync_i18n.py` para la detección automática de claves huérfanas.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + thinking words:** Hilo en segundo plano durante las llamadas a IA mostrando caracteres braille con palabras de "pensamiento".
* **Delimitador:** Separador de frases usando punto y coma (`;`), compatible con frases complejas que contienen comas.
* **Velocidad adaptativa y parpadeo:** Animación de revelado de caracteres adaptada para frases largas y uso de ANSI `\033[K` para evitar artefactos visuales en la terminal.
* **263 entradas por idioma:** Sincronizadas en los 5 idiomas.

### **12. Proveedores de IA (`src/ai_providers.py`)**

* **3 proveedores soportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Medición de duración:** Inyección de `duration_ms` (temporización de alta precisión vía `time.perf_counter()`) en `meta_raw` y `_telemetry_meta`.
* **Modo JSON y parámetros deterministas:** Salidas estructuradas con `temperature=0.0` y `top_p=0.1`.

### **13. Caché Inteligente (`src/cache.py`)**

* **MD5 + metadatos:** Clave por hash MD5 del diff y el prompt.
* **Indexación de idioma:** El campo `lang` se añadió a la clave de caché.
* **Telemetría y duración:** Persistencia de los campos `duration_ms` y `meta_raw` en los archivos de caché.
* **Lectura del dashboard:** `scan_cache_files_for_dashboard()` lee todos los archivos de caché recursivamente.

### **14. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 motores de contexto:** Diff actual, historial de rama (`-ht`) y arqueología de blame (`-b`).
* **Map-Reduce para Issues:** Cuando el contexto supera ~90k tokens, se divide automáticamente en fragmentos y unifica los resultados.
* **TUI interactiva:** Edición de borradores, atajo F2 (guardar localmente), F3 (publicar en GitHub) y F1 (ayuda).
* **Manejo de 401:** Señalización de reautenticación sin cerrar la aplicación.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución histórica y la autoría de fragmentos de código con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de blame:** Eventos registrados vía `log_blame_metric()` con seguimiento de la profundidad y el número de commits analizados.

### **16. Servidor MCP e Invocación CLI Directa (`src/mcp_server.py`)**

* **12 herramientas MCP anotadas:** Herramientas para `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **15 recursos + 7 prompts plantillados:** 35 archivos de plantilla en `templates/gitpr.prompt.*.md`.
* **Invocación CLI directa:** El comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca cualquier herramienta MCP directamente sin iniciar el servidor stdio JSON-RPC.
* **Patrón de registro:** `_TOOL_FUNCS` mapea nombre de herramienta → callable; `_get_tool_registry()` se fusiona con los metadatos del catálogo.
* **Aislamiento real de stdout:** `_write_real_stdout()` escribe directamente en el `sys.__stdout__` original (guardado antes del monkey-patching), garantizando JSON puro en stdout.
* **Listado de herramientas:** `gitpr-mcp --tool` (sin nombre) lista las 12 herramientas disponibles con las firmas de parámetros.
* **Carga automática de .env:** API keys disponibles automáticamente en modo CLI.
* **Nueva plantilla JSON-RPC 🆕:** `templates/gitpr.mcp-jsonrpc-calls.md` — referencia de llamadas JSON-RPC para las herramientas MCP.
* **Instalador automático:** Configuración de los editores soportados (VS Code, Cursor, Claude Code, Claude Desktop, Zed) con fusión JSON inteligente.

### **17. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Ámbito por repositorio (Repo-Scope):** Etiqueta `📁 Repository: owner/repo` y filtrado estricto de eventos y datos de caché por proyecto.
* **Escaneo asíncrono con overlay:** Hilo de trabajo en segundo plano con el widget `ProgressBar`.
* **Consolidación de datos:** `load_cache_token_summary()` añade los tokens de caché al totalizador.
* **Control del estado de caché:** Archivo de registro en `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Exportación local:** Guardado CSV/JSON en `./.gitpr/metrics/export/`.

### **18. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Ámbito por repositorio:** Todos los eventos indexados por `repo_name`.
* **Nuevos eventos:** Eventos para el listado de archivos no-stageados y la exportación de telemetría.
* **Eventos de hook:** `log_hook_event()` para Git hooks (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge).
* **Eventos de linter y blame:** `log_linter_metric()` y `log_blame_metric()`.
* **Exportación local:** `--metrics --export` genera CSV y JSON en `./.gitpr/metrics/export/` con filtrado por repositorio.
* **Limpieza:** `--metrics --purge` elimina todos los archivos de métricas locales con confirmación interactiva.

### **19. Sincronización de Hooks Git**

* **Versionado independiente:** `__scripts_version__` (v0.0.2) controla la versión de los scripts de hook.
* **Detección automática:** Compara la versión local con la más reciente y actualiza automáticamente.
* **Consciente del idioma:** Descarga las plantillas de hook correspondientes al idioma configurado.
* **Skip de merge-source 🆕:** La plantilla `prepare-commit-msg` (5 variantes de idioma) usa ahora un case POSIX que omite las fuentes `message|merge|squash|commit` y verifica `.git/MERGE_HEAD` como belt-and-braces — los commits generados por git (`git pull`, `git merge`, `--amend`, `-c`/`-C`, `--squash`) preservan el mensaje original de git.

---

## **📊 Pruebas y Calidad**

| Archivo de Pruebas | Escenarios | Enfoque |
|------------------|----------|------|
| `tests/test_core.py` | 32+ 🆕 | Flujos principales, git diff, generación de PR, timing, merge en curso, staging |
| `tests/test_chat_backend.py` | 30+ | Memoria del chat, persistencia, slash commands |
| `tests/test_plugins.py` | 17 | Detección de plugins, fusión de reglas del linter, prompts MCP |
| `tests/test_mcp_server.py` | 75+ | Herramientas MCP, recursos, anotaciones, patching, CLI directa |
| `tests/test_metrics.py` | 36+ | Recopilación, exportación local, ámbito de repo, resumen de tokens de caché, duration_ms |
| `tests/test_smart_excludes.py` | 14+ | Filtro pathspec inteligente |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompts MCP y fallback de idioma |
| `tests/test_blame_metrics.py` | 10+ | Métricas de blame: profundidad, commits, duración |
| `tests/test_linter_metrics.py` | 8+ | Métricas del linter: errores, advertencias, duración |
| `tests/test_thinking_words.py` | 9+ | Carga y parseo con separador `;` |
| `tests/test_skill_command.py` | 5+ | Descarga y validación de plantillas de skill |
| `tests/test_install_wizard.py` | 5+ | Asistente de instalación interactivo |
| `tests/test_pre_save.py` | 3+ | Flag --pre-save y payload JSON |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura i18n (claves huérfanas) |

**Total:** 214 escenarios de prueba automatizados pasando (13 archivos de prueba). Ejecución completa verificada en esta versión: **214/214 passed**. Nuevas pruebas: `TestIsMergeInProgress` (3 casos de merge en curso) y `TestStageFiles` (4 casos: lista vacía, éxito, fallo con error de git, excepción). El fallo conocido reportado en v0.0.10 en `test_metrics.py::test_app_skips_export_and_config_files` no se reprodujo en esta ejecución.

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n:** 507 claves de traducción en pt_BR (+4: labels de estado de archivo y mensaje de error de staging).
* **Documentos actualizados 🆕 (todos en 5 idiomas):**
  - `docs/pr-descricao-padrao.md` — reescrito en EN canónico (convención de docs multilingüe) + 4 locales; sección de publicación con 3 modos de ejecución (`gitpr`, `--no-publish`, `--no-edit`), atajos TUI (F1/F2/F3/Esc), requisito de PAT y resolución de la rama base; ruta de salida corregida a `.gitpr/reports/pr_desc/`
  - `docs/mcp-integration.md` — sincronizado con la implementación: 2 herramientas faltantes en la tabla (`list_unstaged_files`, `analyze_unstaged_diff`), nueva subsección de recursos de prompt (`prompt://*`, plugins, prompts integrados) y sección de Claude Code en las 4 traducciones
  - `docs/git-hooks-locais.md` — documenta el skip de merge-source del hook `prepare-commit-msg` (merge/squash/amend preservan el mensaje de git)
* **Documentación en 5 idiomas:** 34 temas canónicos en `docs/` (28 con cobertura completa en los 5 idiomas).
* **Índice de memoria:** `.claude/memory/MEMORY.md` con 27 patrones en 3 categorías (20 de proyecto, 3 de referencia, 4 de feedback).
* **Informes de tareas:** `docs/claude-code/reports/` (+4 nuevos: pr-descricao-padrao multilingüe, fix prepare-commit-msg merge skip, unstaged modal stage fix, MCP docs sync) y `docs/gemini/reports/`.
* **Informes de estado:** `docs/reports/` (11 informes de estado).
* **Planes de desarrollo:** 11+ planes documentados en `docs/plans/`.

---

## **🔄 Pipeline de Distribución**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → subida automatizada
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **Servidor MCP:** Entry point `gitpr-mcp` vía `pyproject.toml`

---

## **📈 Evolución desde el Informe Anterior (v0.0.10)**

| Área | v0.0.10 (anterior) | v0.0.11 (actual) |
|------|-------------------|----------------|
| **Versión de GitPR** | 0.0.35 | **0.0.36** |
| **Versión de idioma** | v0.0.13 | v0.0.13 |
| **Versión de los scripts de hook** | v0.0.1 | **v0.0.2** |
| **Proveedores de IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interfaz** | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI |
| **Herramientas MCP** | 12 herramientas | 12 herramientas |
| **Flags de CLI** | 26 flags | 26 flags |
| **Variables de entorno** | 16 variables | 16 variables |
| **Staging de archivos** | Selección vía diccionario manual (desincronizada) + fallos de `git add` silenciosos + staging duplicado | **Selección real (`SelectionList.selected`) + error real de git mostrado + staging único por flujo** |
| **Hooks de commit** | La IA omitía solo la fuente `message` | **IA omite `message/merge/squash/commit` + verificación de `.git/MERGE_HEAD` + guard `is_merge_in_progress()`** |
| **i18n (claves pt_BR)** | 503 | **507 (+ labels de estado de archivo y error de staging)** |
| **Documentación** | 37 temas | **34 temas canónicos en `docs/` (28 con 5 idiomas completos) — 3 temas actualizados (pr-descricao-padrao, mcp-integration, git-hooks-locais)** |
| **Suite de pruebas** | 207 escenarios (13 archivos) | **214 escenarios (13 archivos, +7: merge en curso y staging)** |
| **Commits desde el informe** | 2 commits | **4 commits (i18n status, merge skip, docs hooks, staging fix)** |
| **PRs fusionados** | 2 PRs (#107, #110) | **2 PRs (#111, #114)** |
| **Índice de memoria** | 20 patrones | **27 patrones en 3 categorías (proyecto/referencia/feedback)** |

---

## **🚧 Próximos Pasos**

* **Pruebas para PR Publisher:** Cobertura de pruebas unitarias y de integración para el flujo de publicación de PR (`pr_publish_app.py`, `github_api.py`).
* **Pruebas de integración end-to-end para MCP:** Validación de llamadas a herramientas y prompts mediante un cliente stdio simulado.
* **Proveedor Anthropic Claude:** Soporte directo para la API de Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual en el Dashboard:** Añadir histogramas de tiempo y gráficos de tendencia de tokens a la TUI de métricas.
* **Pipeline de release en GitHub Actions:** Automatización completa del build de PyInstaller y la subida de assets a GitHub Releases.
* **Más proveedores:** OpenAI directo, proveedores locales adicionales.
* **Comando local `--init`:** Siembra de `.gitpr/conf/` con plantillas de configuración locales (smart-excludes, linter, etc.).
* **Traducciones pendientes de staging en los demás idiomas:** Las nuevas claves de error de staging existen en pt_br — propagar a pt_pt, es_es y fr_fr en el próximo cambio de versión de idioma.
* **Dead code en la TUI:** La clase borrador `FileStageScreen` duplica `StageFilesScreen` — integrar o eliminar.
* **Ajustes de documentación MCP:** La ayuda de `gitpr-mcp --install` omite `claude-code` en la lista de editores; documentar el alias oculto `gitpr --mcp` en `mcp-integration.md`.

---

**Informe generado el:** 2026-08-15  
**Rama:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
