# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.31 (2026-08-03)**

## **📌 Visión General**

**GitPR** es una herramienta de CLI (Command Line Interface) avanzada para automatización de procesos Git utilizando Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). El objetivo principal es actuar como un asistente inteligente local que hace Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.6):**
- **Dashboard TUI de Métricas Rediseñado:** Alcance aislado por repositorio (`repo_filter`), escaneo asíncrono ilimitado de archivos de caché (`~/.gitpr/cache/prompts/`), superposición visual con `ProgressBar`, totalizador unificado de tokens por proyecto, control de archivos de caché procesados (`./.gitpr/metrics/{repo}/processed_cache.json`) y corrección del error de columnas duplicadas en el atajo F5 (Refresh).
- **Rastreo de Duración de Llamadas IA (Wall-Clock Timing):** Inyección de `duration_ms` en milisegundos vía `time.perf_counter()` en todas las respuestas de LLM, transmitida por la caché y mostrada en el panel de métricas.
- **Exportación Local por Proyecto:** `gitpr --metrics --export` ahora genera informes CSV y JSON en la carpeta del proyecto local (`./.gitpr/metrics/export/`) filtrando por el repositorio activo.
- **Revalidación Automática de Token de GitHub (Auto-Reauth en 401):** Función de validación de PAT (`GET /user`), pre-validación antes de la TUI de issues (`gitpr -is`) y recuperación gradual ante error HTTP 401 sin pérdida de borradores.
- **Ajustes en el Spinner y Thinking Words:** Cambio del delimitador de frases de coma a punto y coma (`;`), permitiendo frases complejas con comas en `templates/gitpr.thinking-words.*.md` sin romper el análisis.
- **Inicio Rápido en los READMEs:** Documentación de instalación vía `pip install gitpr-cli` e inicialización de repositorio vía `gitpr --install` en los READMEs de los 5 idiomas.
- **Guía del Proyecto `GEMINI.md`:** Guía arquitectónica completa, convenciones de código, pipeline de comandos y estándar de informes en `docs/gemini/reports/`.

- **Versión actual:** 0.0.31
- **Publicación:** PyPI (`pip install gitpr-cli`) + GitHub Releases (binario standalone)
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal).
* **UI/Terminal:** Textual — TUI (Text User Interface) para chat interactivo, edición de issues, pantalla de ayuda y panel de métricas.
* **Criptografía:** `cryptography.fernet` para protección local de claves de API y tokens de GitHub.
* **Configuración:** `python-dotenv`, `pyyaml` (para el linter estático).
* **Proveedores IA:** Integración vía SDK oficial de Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), y OpenAI SDK (`Ollama` local).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — **Tool Annotations, Prompts con plantillas y recursos prompt://**.
* **Pruebas:** Pytest + `unittest.mock` (10 archivos de prueba, 114 escenarios).
* **Empaquetado:** PyInstaller (binario standalone) + setuptools/build (PyPI).
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` para ejecución en pipelines.

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación Estructurada:** Se comunica con la LLM solicitando retorno estrictamente en JSON.
* **Map-Reduce (Diffs Gigantes):** Cuando el diff supera los ~90k tokens, se divide automáticamente en lotes por archivo (`split_diff_into_chunks`), procesa cada parte (Map) y unifica los resúmenes (Reduce) manteniendo el tono arquitectónico.
* **Estimación de Tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()`.
* **Optimización Nativa de Git:** Flags `-U1`, `-w`, `-M`, `-B` en los comandos `get_git_diff` y `get_git_full_diff` para reducir contexto innecesario.
* **Pre-Save (`--pre-save`):** Flag oculta de depuración que guarda el payload completo (system instruction + prompt) en JSON antes de cada llamada a la IA.
* **Smart Excludes:** Filtro de pathspec inteligente (`gitpr.smart-excludes.json`) remoto — descargado de GitHub y actualizado automáticamente con versionado (`SMART_EXCLUDES_VERSION`), excluyendo archivos irrelevantes (lock files, artefactos de build, assets binarios) para reducir tokens.
* **Métricas con Rastreo de Tiempo:** Inyección de `log_command_metric()` en todos los flujos con envío de duración en milisegundos (`duration_ms`) e importaciones diferidas para evitar importaciones circulares.

### **2. Interfaz CLI y Configuración (`src/main.py` y `src/config.py`)**

* **Configuración Inicial:** Detecta la primera ejecución, crea la carpeta `~/.gitpr/` y solicita interactivamente claves de API, preferencias e idioma, guardándolo en un `.env`.
* **Enrutamiento de Comandos:** Gestiona todas las flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--lang`, `--provider`, `--pre-save`).
* **Ayuda Contextual:** `-h --flag` muestra documentación específica de la funcionalidad con enlace directo (sensible al idioma) a GitHub.
* **--lang:** Fuerza el idioma de la interfaz para la ejecución actual sin persistir el cambio.
* **--provider:** Fuerza el proveedor de IA (`gemini`, `deepseek`, `ollama`) para la ejecución actual.
* **--mcp:** Inicia el servidor MCP en el transporte stdio para integración con editores — **10 herramientas anotadas + 15 recursos + 7 prompts**.
* **--install:** Asistente guiado de 4 pasos que descarga plantillas de skill, instala Git Hooks, configura MCP en los editores y valida claves de API.
* **--metrics:** Sistema de telemetría local con alcance por repositorio: `--export` (guarda en `./.gitpr/metrics/export/`), `--purge` (limpieza), `--dashboard` (TUI interactiva con escaneo de caché).

### **3. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) en el git diff sin gastar cuota de IA.
* **Reglas YAML:** Lee el archivo local `.gitpr.linter.yml` (creado vía `--skill`). Soporta regex de validación, ignorar comentarios e ignorar directorios específicos (usando fnmatch).
* **Plantilla Multilingüe:** Plantillas del linter disponibles en 5 idiomas.

### **4. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografía:** Genera una clave maestra `secret.key` en la carpeta `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data` y `decrypt_data` para proteger claves de API de IA y GitHub PAT.
* **Validación de Token de GitHub 🆕:** La función `validate_github_token()` realiza una llamada ligera (`GET /user`) para validar el PAT.
* **Flujo de Auto-Reauth 🆕:** Si el token expira o es inválido durante el `gitpr -is`, la aplicación captura la respuesta 401 HTTP, solicita un nuevo token al usuario y reinicia la interfaz TUI preservando el borrador.

### **5. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Verifica en la API de GitHub Releases la versión más reciente. Si hay divergencia, descarga el binario compilado, renombra el ejecutable actual y lo reemplaza sin interrumpir la ejecución en curso (con capacidad de rollback).
* **Caché diario:** Evita comprobaciones repetidas en el mismo día.
* **Verificación de conexión:** Socket `8.8.8.8:53` antes de cualquier operación de red.
* **Versionado de assets:** `__lang_version__` (v0.0.8), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` para control de actualización de plantillas y traducciones.

### **6. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Construida con Textual — historial de mensajes, entrada multilínea, barra de estado con atajos visibles.
* **Memoria por Branch (`src/chat_memory.py`):** Historial de conversación persistido por rama, permitiendo continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear` — atajos para acciones comunes de pair programming.
* **Auto-Patching (F5):** Extrae bloques de código sugeridos por la IA y los exporta a un archivo de parche para su fácil aplicación.
* **Actualización de Diff (F2):** Recarga el `git diff` actual sin reiniciar la sesión.
* **Exportación de Sesión (F6):** Guarda el historial completo del chat para documentación.

### **7. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con soporte para marcadores nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`.
* **5 Idiomas:** en_us (predeterminado/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Archivos Versionados:** `__lang_version__` (v0.0.8) controla la actualización de los paquetes de idioma (`langs/*.json`).
* **Cobertura Total:** Mensajes CLI, ayuda de Click, alertas del linter, Git Hooks, spinner, chat TUI, MCP, métricas y TUI Dashboard traducidos.

### **8. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en segundo plano durante llamadas de IA que muestra caracteres braille con palabras de "pensamiento".
* **Delimitador Actualizado 🆕:** Cambio del separador de frases a punto y coma (`;`), evitando que frases con comas internas se dividan incorrectamente.
* **Velocidad Adaptativa y Flickering:** Animación de descubrimiento de caracteres adaptada para frases largas y uso de ANSI `\033[K` para evitar artefactos visuales en la terminal.
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas en los archivos `templates/gitpr.thinking-words.{lang}.md`.

### **9. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Medición de Duración 🆕:** Inyección de `duration_ms` (cronometraje de alta precisión vía `time.perf_counter()`) en `meta_raw` y `_telemetry_meta`.
* **Modo JSON y Parámetros Deterministas:** Salidas estructuradas con `temperature=0.0` y `top_p=0.1`.

### **10. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Clave mediante hash MD5 del diff y prompt.
* **Telemetría y Duración 🆕:** Persistencia del campo `duration_ms` y `meta_raw` en archivos de caché en `~/.gitpr/cache/prompts/`.
* **Escáner para Dashboard 🆕:** `scan_cache_files_for_dashboard()` lee todos los archivos de caché recursivamente para computar métricas históricas completas.

### **11. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`), y Arqueología por Blame (`-b`).
* **TUI Interactiva:** Edición de borradores, atajo F2 (guardar local), F3 (publicar en GitHub vía API REST) y F1 (help).
* **Manejo de 401 🆕:** Solicitud de reautenticación sin cerrar la aplicación ni perder contenido.

### **12. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y autoría histórica de fragmentos de código con clasificación de commits (`ORIGIN` vs `REFACTORING`).

### **13. Servidor MCP e Instalador (`src/mcp_server.py`)**

* **10 Herramientas MCP Anotadas:** Annotations (`readOnlyHint`, `destructiveHint`, `idempotentHint`) configuradas para IDEs como Cursor, VS Code y Claude Code.
* **15 Recursos + 7 Prompts Templatizados:** 35 archivos de plantilla en `templates/gitpr.prompt.*.md`.
* **Instalador Automático:** Configuración de editores soportados (VS Code, Cursor, Claude Code, Claude Desktop, Zed) con fusión JSON inteligente.

### **14. Dashboard de Métricas TUI Rediseñado (`src/ui/metrics_app.py`)** 🆕

* **Alcance por Repositorio (Repo-Scope):** Etiqueta `📁 Repository: owner/repo` y filtrado estricto de eventos y datos de caché por proyecto.
* **Escaneo Asíncrono con Overlay:** Hilo de trabajo en segundo plano que carga datos de caché mientras muestra el widget `ProgressBar` de Textual.
* **Consolidación de Datos:** `load_cache_token_summary()` suma tokens de llamadas de caché al totalizador del panel.
* **Control de Estado de Caché:** Archivo de registro en `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Fix de Columnas en F5:** Inicialización única de columnas (`_setup_columns()`), previniendo duplicación visual en actualizaciones.
* **Exportación Local:** Guardado de CSV/JSON en `./.gitpr/metrics/export/`.

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

**Total:** 114 escenarios de prueba automatizados pasando con 100% de éxito.

---

## **🌐 Internacionalización y Documentación**

* **Sección Quick Start en los READMEs 🆕:** Actualización de los archivos `README.md`, `README.pt_br.md`, `README.pt_pt.md`, `README.es_es.md` y `README.fr_fr.md` con instrucciones de `pip install gitpr-cli` y `gitpr --install`.
* **Nueva Guía `GEMINI.md` 🆕:** Guía de desarrollo con estándares de código, comandos, estructura del proyecto e informes obligatorios.
* **447 claves de traducción** por idioma (2.235 traducciones en total).
* **Documentación en 5 idiomas:** 23 temas en `docs/` traducidos a EN, PT-BR, PT-PT, ES, FR.
* **Informes de tareas:** `docs/claude-code/reports/` y `docs/gemini/reports/`.

---

## **🔄 Pipeline de Distribución**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → carga automatizada
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server:** Entry point `gitpr-mcp` vía `pyproject.toml`

---

## **📈 Evolución desde el Informe Anterior (v0.0.5)**

| Área | v0.0.5 (anterior) | v0.0.6 (actual) |
|------|-------------------|-----------------|
| **Proveedores IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Dashboard TUI** | Global, limitado a 100 eventos | **Repo-scoped, escaneo de caché ilimitado + ProgressBar + F5 fix** |
| **Métricas y Duración** | Tokens y contadores simples | **Wall-clock duration (`duration_ms`) + Exportación local (`./.gitpr/metrics/export/`)** |
| **GitHub PAT Auth** | Almacenamiento seguro sin pre-validación | **Validación previa vía `GET /user` + Auto-Reauth en HTTP 401** |
| **Thinking Words** | Separador por coma `,` | **Separador `;` (soporta frases complejas) sincronizado en 5 idiomas** |
| **README Documentación** | Enfoque en descarga de binarios | **Quick Start con `pip install gitpr-cli` y `gitpr --install` en 5 idiomas** |
| **Manuales de Desarrollo**| CLAUDE.md | **CLAUDE.md + GEMINI.md** |
| **Suite de Pruebas** | 100+ escenarios | **114 escenarios de prueba (100% pasando)** |
| **Versión PyPI** | 0.0.30 | **0.0.31** |

---

## **🚧 Próximos Pasos**

* **Pruebas de integración end-to-end para MCP:** Validación de llamadas de herramientas y prompts mediante cliente stdio simulado.
* **Proveedor Anthropic Claude:** Soporte directo a la API de Claude (`claude-3-5-sonnet`).
* **Gráficos en ASCII/Textual en el Dashboard:** Añadir histogramas de tiempo y gráficos de tendencia de tokens en la TUI de métricas.
* **Pipeline de Release en GitHub Actions:** Automatización completa del build de PyInstaller y envío de assets a GitHub Releases.

---

**Informe generado el:** 2026-08-03  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))  
