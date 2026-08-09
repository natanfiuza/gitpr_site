# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.33 (2026-08-09)**

## **📌 Visión General**

**GitPR** es una herramienta de CLI (Command Line Interface) avanzada para automatización de procesos Git utilizando Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). El objetivo principal es actuar como un asistente inteligente local que hace Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.8):**
- **PR Publisher TUI (`gitpr` predeterminado):** Interfaz interactiva en la terminal para revisar, editar y publicar Pull Requests directamente en GitHub mediante la REST API. Incluye edición de título, cuerpo y rama base con bindings F1 (Help), F2 (Guardar local), F3 (Publicar) y Esc (Salir). Flujo completo con 6 pantallas modales para commit, staging y progreso.
- **Flujo de Auto-Commit Inteligente:** Al usar `--no-edit` o publicar con F3 con cambios sin commitear, GitPR ejecuta el linter estático, genera un mensaje de commit mediante IA (Conventional Commits), confirma con el usuario y ejecuta `git commit` antes de publicar el PR.
- **Gestión de Archivos No-Stageados:** Al inicio, GitPR verifica los archivos no-stageados y ofrece un modal TUI (`StageFilesApp`) para seleccionar, omitir o cancelar antes de la generación del PR.
- **Manejo de PR Existente:** Cuando ya existe un PR para la rama actual, la TUI ofrece push al PR existente (actualizando el cuerpo mediante PATCH) o crear uno nuevo.
- **Flujo de Merge:** Después de la creación o actualización del PR, GitPR puede opcionalmente hacer el merge. Controlado por la variable de entorno `GITPR_AUTO_MERGE`.
- **Auto-Upstream en el Push:** Cuando `git push` falla por falta de upstream, GitPR automáticamente reintenta con `--set-upstream origin <branch>`.
- **Detección de "Nothing to commit":** Las fallas de commit por ausencia de cambios stageados se tratan como éxito — el flujo continúa hacia la publicación del PR.
- **Centralización de Output:** Todos los archivos generados ahora usan `.gitpr/reports/` organizados por tipo (`pr_desc/`, `review/`, `full_review/`, `file_review/`, `blame/`, `issue/`). Las rutas personalizadas en el `.env` se respetan por compatibilidad.
- **6 Nuevas Variables de Entorno:** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE` — control granular del flujo de publicación.
- **Módulo de API de GitHub (`src/github_api.py`):** Funciones compartidas para `create_pull_request()`, `update_pull_request()` y `merge_pull_request()` mediante la REST API.
- **Documentación Técnica Multilingüe:** `docs/pull-request-publication.md` en 5 idiomas (EN, PT-BR, PT-PT, ES, FR) con cobertura completa del flujo de PR.
- **CHANGELOG.md:** Historial completo de versiones de v0.0.1 hasta v0.0.33 en formato Keep a Changelog, generado a partir de los informes de estado en `docs/reports/`.

- **Versión actual:** 0.0.33
- **Versión de los diccionarios de idioma:** v0.0.11
- **Versión de los scripts de hook:** v0.0.1
- **Publicación:** PyPI (`pip install gitpr-cli`) + GitHub Releases (binario standalone)
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal).
* **UI/Terminal:** Textual — TUI (Text User Interface) para chat interactivo, edición de issues, pantalla de ayuda, panel de métricas y **PR Publisher** 🆕.
* **Criptografía:** `cryptography.fernet` para protección local de claves de API y tokens de GitHub.
* **Configuración:** `python-dotenv`, `pyyaml` (para el linter estático).
* **Proveedores IA:** Integración vía SDK oficial de Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), y OpenAI SDK (`Ollama` local).
* **GitHub API:** `requests` (REST API vía PAT) — **uso expandido con el nuevo módulo `github_api.py`** 🆕.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — Tool Annotations, Prompts con plantillas y recursos prompt://.
* **Pruebas:** Pytest + `unittest.mock` (12 archivos de prueba, 131 escenarios).
* **Empaquetado:** PyInstaller (binario standalone) + setuptools/build (PyPI).
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` para ejecución en pipelines.

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación Estructurada:** Se comunica con la LLM solicitando retorno estrictamente en JSON.
* **Map-Reduce (Diffs Gigantes):** Cuando el diff supera los ~90k tokens, se divide automáticamente en lotes por archivo (`split_diff_into_chunks`), procesa cada parte (Map) y unifica los resúmenes (Reduce) manteniendo el tono de voz de la arquitectura.
* **Estimación de Tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()`.
* **Optimización Nativa de Git:** Flags `-U1`, `-w`, `-M`, `-B` en los comandos `get_git_diff` y `get_git_full_diff` para reducir contexto innecesario.
* **Pre-Save (`--pre-save`):** Flag oculta de depuración que guarda el payload completo (system instruction + prompt) en JSON antes de cada llamada a la IA.
* **Smart Excludes:** Filtro de pathspec inteligente (`gitpr.smart-excludes.json`) remoto — descargado de GitHub y actualizado automáticamente con versionado (`SMART_EXCLUDES_VERSION`), excluyendo archivos irrelevantes (lock files, build artifacts, assets binarios y documentación) para reducir tokens.
* **Métricas con Rastreo de Tiempo:** Inyección de `log_command_metric()` en todos los flujos con envío de duración en milisegundos (`duration_ms`) e importaciones diferidas para evitar importaciones circulares.
* **Resolución Centralizada de Output 🆕:** Nueva función `resolve_output_path()` que centraliza la lógica de directorios de salida — por defecto en `.gitpr/reports/{type}/` con fallback para rutas personalizadas del `.env`.

### **2. Interfaz CLI y Configuración (`src/main.py` y `src/config.py`)**

* **Configuración Inicial:** Detecta la primera ejecución, crea la carpeta `~/.gitpr/` y solicita interactivamente las claves de API, preferencias e idioma, guardándolas en un `.env`.
* **Enrutamiento de Comandos:** Gestiona todas las flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--publish`, `--no-publish`, `--no-edit`, `--base`, `--lang`, `--provider`, `--pre-save`).
* **Comportamiento Predeterminado Cambiado 🆕:** Ejecutar `gitpr` sin flags ahora abre la TUI del PR Publisher (antes: generaba archivo y salía).
* **Nuevas Flags 🆕:**
  * `--publish`: Abre la TUI interactiva para revisar, editar y publicar el PR (comportamiento predeterminado).
  * `--no-publish`: Genera la descripción del PR y la guarda localmente sin abrir el editor interactivo.
  * `--no-edit`: Omite la TUI por completo — hace auto-commit (con validación del linter), auto-push y publica directamente en GitHub. Ideal para CI/CD.
  * `--base <branch>`: Sobrescribe la rama de destino del Pull Request.
* **Nuevas Variables de Entorno 🆕:** `GITPR_AUTO_COMMIT` (omitir la confirmación de commit), `GITPR_SKIP_LINT` (omitir la validación del linter), `GITPR_AUTO_STAGE` (stage automático de archivos), `GITPR_SKIP_UNSTAGED_CHECK` (omitir la verificación de unstaged), `GITPR_SHOW_LOGS` (controlar los logs de progreso), `GITPR_AUTO_MERGE` (auto-merge después de la publicación).
* **Ayuda Contextual:** `-h --flag` muestra documentación específica de la funcionalidad con enlace directo (sensible al idioma) a GitHub.
* **--lang:** Fuerza el idioma de la interfaz para la ejecución actual sin persistir el cambio.
* **--provider:** Fuerza el proveedor de IA (`gemini`, `deepseek`, `ollama`) para la ejecución actual.
* **--mcp:** Inicia el servidor MCP en el transporte stdio para integración con editores — **10 herramientas anotadas + 15 recursos + 7 prompts**.
* **--install:** Asistente guiado de 4 pasos que descarga plantillas de skill, instala Git Hooks, configura MCP en los editores y valida claves de API.
* **--metrics:** Sistema de telemetría local con alcance por repositorio: `--export` (guarda en `./.gitpr/metrics/export/`), `--purge` (limpieza), `--dashboard` (TUI interactiva con escaneo de caché).

### **3. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)** 🆕

* **Interfaz Interactiva Completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en la terminal.
* **6 Pantallas Modales:**
  * `CommitConfirmScreen`: Confirmación antes del commit automático.
  * `FileStageScreen`: Selección interactiva de archivos para staging.
  * `CommitProgressScreen`: Barra de progreso durante el commit y push con logs en tiempo real.
  * `CommitMessageScreen`: Visualización y confirmación del mensaje generado por IA.
  * `LinterErrorScreen`: Visualización de errores del linter con opción de abortar o continuar.
  * `ErrorScreen`: Visualización de errores generales con scroll, limitado a `max-height: 80%`.
* **Bindings:** F1 (Help — modal con atajos e instrucciones), F2 (Guardar .md local), F3 (Publicar vía GitHub API), Esc (Salir).
* **Flujo de Auto-Commit:** Cuando hay cambios sin commitear y el usuario usa `--no-edit` o F3, GitPR automáticamente:
  1. Ejecuta el linter estático (a menos que `GITPR_SKIP_LINT=true`)
  2. Genera un mensaje de commit mediante IA (Conventional Commits)
  3. Confirma con el usuario (a menos que `GITPR_AUTO_COMMIT=true`)
  4. Ejecuta `git commit`
  5. Continúa con el push y la publicación del PR
* **Verificación de Archivos Unstaged:** Al iniciar, verifica `git status --porcelain` y ofrece un modal `StageFilesApp` para seleccionar, omitir o cancelar.
* **Manejo de PR Existente:** Detecta PRs abiertos para la rama actual mediante la API de GitHub y ofrece push al PR existente (actualizando mediante PATCH) o crear uno nuevo.
* **Auto-Upstream:** Detecta fallo de `git push` por falta de upstream y automáticamente reintenta con `--set-upstream origin <branch>`.
* **Detección de "Nothing to commit":** Trata `git commit` sin cambios como éxito — el flujo continúa sin error.
* **Merge Flow:** Después de la creación/actualización del PR, ofrece la opción de merge. Controlado por `GITPR_AUTO_MERGE`.
* **Corrección de Stdout:** Wrapper `_with_real_stdout()` para evitar `OSError: [Errno 9] Bad file descriptor` cuando la TUI de Textual llama a `click.secho()`.

### **4. Módulo de API de GitHub (`src/github_api.py`)** 🆕

* **Funciones Compartidas:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` — encapsulando llamadas REST a la API de GitHub v3.
* **Autenticación mediante PAT:** Token de acceso personal validado con `GET /user` antes de las operaciones.
* **Reutilización:** Funciones utilizadas tanto por la TUI de PR como por la TUI de issues, eliminando duplicación.

### **5. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) en el git diff sin gastar cuota de IA.
* **Reglas YAML:** Lee el archivo local `.gitpr.linter.yml` (creado vía `--skill`). Soporta regex de validación, ignorar comentarios e ignorar directorios específicos (usando fnmatch).
* **Plantilla Multilingüe:** Plantillas del linter disponibles en 5 idiomas.
* **Integración en el Auto-Commit 🆕:** El linter se ejecuta automáticamente antes del commit en el flujo de PR publication.

### **6. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografía:** Genera una clave maestra `secret.key` en la carpeta `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data` y `decrypt_data` para proteger claves de API de IA y el GitHub PAT.
* **Validación de Token de GitHub:** La función `validate_github_token()` realiza una llamada ligera (`GET /user`) para validar el PAT.
* **Flujo de Auto-Reauth:** Si el token expira o es inválido durante el `gitpr -is`, la aplicación captura la respuesta 401 HTTP, solicita un nuevo token al usuario y reinicia la interfaz TUI preservando el borrador.

### **7. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Verifica en la API de GitHub Releases la versión más reciente. Si hay divergencia, descarga el binario compilado, renombra el ejecutable actual y lo reemplaza sin interrumpir la ejecución en curso (con capacidad de rollback).
* **Caché diario:** Evita comprobaciones repetidas en el mismo día.
* **Verificación de conexión:** Socket `8.8.8.8:53` antes de cualquier operación de red.
* **Versionado Centralizado:** `__version__` (0.0.33), `__lang_version__` (v0.0.11), `__scripts_version__` (v0.0.1), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` — todos derivados exclusivamente del `updater.py`.

### **8. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Construida con Textual — historial de mensajes, entrada multilínea, barra de estado con atajos visibles.
* **Memoria por Branch (`src/chat_memory.py`):** Historial de conversación persistido por rama, permitiendo continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear` — atajos para acciones comunes de pair programming.
* **Auto-Patching (F5):** Extrae bloques de código sugeridos por la IA y los exporta a un archivo de parche para su fácil aplicación.
* **Actualización de Diff (F2):** Recarga el `git diff` actual sin reiniciar la sesión.
* **Exportación de Sesión (F6):** Guarda el historial completo del chat para documentación.

### **9. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con soporte para marcadores nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`.
* **5 Idiomas:** en_us (predeterminado/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Archivos Versionados:** `__lang_version__` (v0.0.11) controla la actualización de los paquetes de idioma (`langs/*.json`).
* **Cobertura Expandida 🆕:** ~623 claves de traducción en pt_BR (+132 desde v0.0.32). Nuevas strings para la PR Publisher TUI, pantallas modales, flujo de commit y documentación de PR publication.
* **Caché con Indexación por Idioma:** Las respuestas de IA cacheadas incluyen el idioma actual en la clave MD5.
* **Script de Sincronización:** `tests/sync_i18n.py` para la detección automática de claves huérfanas.

### **10. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en segundo plano durante llamadas de IA que muestra caracteres braille con palabras de "pensamiento".
* **Delimitador:** Separador de frases por punto y coma (`;`), compatible con frases complejas que contienen comas.
* **Velocidad Adaptativa y Flickering:** Animación de descubrimiento de caracteres adaptada para frases largas y uso de ANSI `\033[K` para evitar artefactos visuales en la terminal.
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas en los archivos `templates/gitpr.thinking-words.{lang}.md`.

### **11. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Medición de Duración:** Inyección de `duration_ms` (cronometraje de alta precisión vía `time.perf_counter()`) en `meta_raw` y `_telemetry_meta`.
* **Modo JSON y Parámetros Deterministas:** Salidas estructuradas con `temperature=0.0` y `top_p=0.1`.

### **12. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Clave mediante hash MD5 del diff y prompt.
* **Indexación por Idioma:** El campo `lang` se añadió a la clave de la caché, permitiendo respuestas distintas para el mismo diff en idiomas diferentes.
* **Telemetría y Duración:** Persistencia del campo `duration_ms` y `meta_raw` en archivos de caché en `~/.gitpr/cache/prompts/`.
* **Lectura para Dashboard:** `scan_cache_files_for_dashboard()` lee todos los archivos de caché recursivamente para computar métricas históricas completas.

### **13. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`), y Arqueología por Blame (`-b`).
* **TUI Interactiva:** Edición de borradores, atajo F2 (guardar local), F3 (publicar en GitHub mediante la API REST) y F1 (help).
* **Manejo de 401:** Solicitud de reautenticación sin cerrar la aplicación ni perder contenido.

### **14. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y autoría histórica de fragmentos de código con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos de arqueología registrados vía `log_blame_metric()` con seguimiento de profundidad y número de commits analizados.

### **15. Servidor MCP e Instalador (`src/mcp_server.py`)**

* **10 Herramientas MCP Anotadas:** Annotations (`readOnlyHint`, `destructiveHint`, `idempotentHint`) configuradas para IDEs como Cursor, VS Code y Claude Code.
* **15 Recursos + 7 Prompts Templatizados:** 35 archivos de plantilla en `templates/gitpr.prompt.*.md`.
* **Instalador Automático:** Configuración de editores soportados (VS Code, Cursor, Claude Code, Claude Desktop, Zed) con fusión JSON inteligente.

### **16. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Alcance por Repositorio (Repo-Scope):** Etiqueta `📁 Repository: owner/repo` y filtrado estricto de eventos y datos de caché por proyecto.
* **Escaneo Asíncrono con Overlay:** Hilo de trabajo en segundo plano que carga datos de caché mientras muestra el widget `ProgressBar` de Textual.
* **Consolidación de Datos:** `load_cache_token_summary()` suma tokens de llamadas de caché al totalizador del panel.
* **Control de Estado de Caché:** Archivo de registro en `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Fix de Columnas en F5:** Inicialización única de columnas (`_setup_columns()`), previniendo duplicación visual en actualizaciones.
* **Exportación Local:** Guardado de CSV/JSON en `./.gitpr/metrics/export/`.

### **17. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Alcance por Repositorio:** Todos los eventos de métricas se indexan por `repo_name`, permitiendo aislamiento entre proyectos.
* **Eventos de Hook:** `log_hook_event()` para hooks Git (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge).
* **Eventos de Linter y Blame:** `log_linter_metric()` para el linter standalone, `log_blame_metric()` para arqueología de código.
* **Exportación Local:** `--metrics --export` genera CSV y JSON en `./.gitpr/metrics/export/` con filtro por repositorio.
* **Limpieza:** `--metrics --purge` elimina todos los archivos de métricas locales con confirmación interactiva.

### **18. Sincronización de Hooks Git**

* **Versionado Independiente:** `__scripts_version__` (v0.0.1) en el `updater.py` controla la versión de los scripts de hook por separado de los diccionarios de idioma.
* **Detección Automática:** Al ejecutar `--installhooks`, el sistema compara la versión local (almacenada en el `.env`) con la versión más reciente y actualiza automáticamente si es necesario.
* **Idioma-Aware:** Detecta el idioma configurado y descarga las plantillas de hook correspondientes.

---

## **📊 Pruebas y Calidad**

| Archivo de Prueba | Escenarios | Enfoque |
|-------------------|------------|---------|
| `tests/test_core.py` | 25+ | Flujos principales, git diff, PR generation, timing |
| `tests/test_chat_backend.py` | 30+ | Memoria de chat, persistencia, comandos slash |
| `tests/test_skill_command.py` | 10+ | Descarga y validación de plantillas de skill |
| `tests/test_pre_save.py` | 10+ | Flag --pre-save y payload JSON |
| `tests/test_smart_excludes.py` | 14+ | Filtro pathspec inteligente |
| `tests/test_thinking_words.py` | 10+ | Carga y análisis con separador `;` |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompt MCP y fallback de idioma |
| `tests/test_mcp_server.py` | 33 | Herramientas MCP, recursos, annotations y parcheo |
| `tests/test_metrics.py` | 36+ | Recolección, exportación local, alcance de repo, cache token summary, duration_ms |
| `tests/test_install_wizard.py` | 5+ | Asistente interactivo de instalación |
| `tests/test_blame_metrics.py` | 10+ | Métricas de blame: profundidad, commits, duración |
| `tests/test_linter_metrics.py` | 8+ | Métricas de linter: errores, warnings, duración |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura i18n (claves huérfanas) |

**Total:** 131 escenarios de prueba automatizados pasando con 100% de éxito.

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n Expandida 🆕:** ~623 claves de traducción en pt_BR (eran 491 en v0.0.32, +132 nuevas). Nuevas strings que cubren la PR Publisher TUI, pantallas modales de commit, flujo de staging y documentación.
* **Nueva Documentación Técnica 🆕:** `docs/pull-request-publication.md` en 5 idiomas (EN, PT-BR, PT-PT, ES, FR) con cobertura completa del flujo de PR publication, variables de entorno y troubleshooting.
* **CHANGELOG.md 🆕:** Historial completo de todas las versiones (v0.0.1 → v0.0.33) en formato Keep a Changelog con secciones Added, Changed y Fixed.
* **READMEs Actualizados 🆕:** Los 5 READMEs actualizados con funciones de PR Publisher, estructura de directorios `.gitpr/reports/` y enlaces a la documentación.
* **Documentación en 5 idiomas:** 24 temas en `docs/` traducidos a EN, PT-BR, PT-PT, ES, FR (+1 tema nuevo: pull-request-publication).
* **Memory Index:** `.claude/memory/MEMORY.md` con 14 patrones de arquitectura extraídos de 36 informes.
* **Informes de tareas:** `docs/claude-code/reports/` y `docs/reports/` (8 informes de estado).
* **Planes de desarrollo:** 8+ planes documentados en `docs/plans/`.

---

## **🔄 Pipeline de Distribución**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → carga automatizada
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server:** Entry point `gitpr-mcp` vía `pyproject.toml`

---

## **📈 Evolución desde el Informe Anterior (v0.0.7)**

| Área | v0.0.7 (anterior) | v0.0.8 (actual) |
|------|-------------------|-----------------|
| **Versión GitPR** | 0.0.32 | **0.0.33** |
| **Versión Idioma** | v0.0.10 | **v0.0.11** |
| **Versión Scripts Hook** | v0.0.1 | v0.0.1 |
| **Proveedores IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interfaz** | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + **PR Publisher TUI** |
| **PR Publication** | Solo generación local de .md | **TUI interactiva completa + auto-commit + push + publicación mediante API** |
| **Comportamiento Predeterminado** | `gitpr` genera archivo local | **`gitpr` abre la TUI del PR Publisher** |
| **Pantallas TUI (total)** | 3 (issues, chat, metrics) | **5 apps TUI + 6 pantallas modales de commit** |
| **GitHub API** | Issues mediante REST | **+ PRs (create, update, merge) mediante módulo dedicado** |
| **Nuevas Flags CLI** | 21 flags | **24 flags (+ `--publish`, `--no-publish`, `--no-edit`, `--base`)** |
| **Variables de Entorno** | 7 vars | **13 vars (+6: AUTO_COMMIT, SKIP_LINT, AUTO_STAGE, SKIP_UNSTAGED, SHOW_LOGS, AUTO_MERGE)** |
| **Traducciones pt_BR** | 491 claves | **~623 claves (+132 PR Publisher y commit flow)** |
| **Módulos Python** | 21 archivos en src/ | **25 archivos (+ github_api.py, pr_publish_app.py, pr_publish_help.py)** |
| **Documentación** | 23 temas | **24 temas (+ pull-request-publication.md en 5 idiomas)** |
| **CHANGELOG** | — (solo GitHub Releases) | **Historial completo de las 8 versiones (v0.0.1 → v0.0.33)** |
| **Suite de Pruebas** | 131 escenarios (12 archivos) | 131 escenarios (12 archivos) |
| **Commits desde v0.0.32** | — | **7 commits (PR Publisher + merge flow)** |

---

## **🚧 Próximos Pasos**

* **Pruebas para PR Publisher:** Cobertura de pruebas unitarias y de integración para el flujo de PR publication (`pr_publish_app.py`, `github_api.py`).
* **Pruebas de integración end-to-end para MCP:** Validación de llamadas de herramientas y prompts mediante cliente stdio simulado.
* **Proveedor Anthropic Claude:** Soporte directo a la API de Claude (`claude-sonnet-5`).
* **Gráficos en ASCII/Textual en el Dashboard:** Añadir histogramas de tiempo y gráficos de tendencia de tokens en la TUI de métricas.
* **Pipeline de Release en GitHub Actions:** Automatización completa del build de PyInstaller y envío de assets a GitHub Releases.
* **Más proveedores:** OpenAI directo, proveedores locales adicionales.
* **Plugin system:** Extensibilidad para reglas de linter y prompts personalizados.

---

**Informe generado el:** 2026-08-09  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
