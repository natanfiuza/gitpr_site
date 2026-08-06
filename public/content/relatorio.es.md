# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.32 (2026-08-06)**

## **📌 Visión General**

**GitPR** es una herramienta de CLI (Command Line Interface) avanzada para automatización de procesos Git utilizando Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). El objetivo principal es actuar como un asistente inteligente local que hace Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.7):**
- **Expansión de la Cobertura i18n (491 claves):** Sincronización completa de las llamadas `__()` en `core.py`, `main.py` y `linter_engine.py` con el archivo de traducción `pt_br.json`. Script de verificación `tests/sync_i18n.py` para detectar claves huérfanas en cualquier archivo fuente. Adición de 5 nuevas traducciones para strings de Smart Excludes, banner CLI con `--install` y telemetría local.
- **Smart Excludes para Documentación:** Filtro pathspec inteligente que detecta y excluye archivos de documentación (`.md`, `.rst`, `.txt`) del diff, con notificación visual de la cantidad de archivos excluidos (`📄 {count} documentation file(s) excluded`) y enlace a documentación.
- **Sincronización Automática de Hooks Git:** Sistema de versionado independiente para scripts de hook (`__scripts_version__` v0.0.1) con verificación y actualización automática al ejecutar `--installhooks`. Detecta el idioma del entorno y descarga la versión correcta de las plantillas.
- **Métricas para Linter, Blame y Git Hooks:** Telemetría expandida con `log_hook_event()` para eventos de hook, `log_linter_metric()` para ejecuciones del linter standalone y `log_blame_metric()` para arqueología de código.
- **Caché i18n con Indexación por Idioma:** El sistema de caché de respuestas IA ahora incluye el idioma actual en la clave, evitando colisiones entre respuestas generadas en idiomas diferentes.
- **Versionado Centralizado en el Updater:** La versión de GitPR (`__version__`) y la versión de los diccionarios de idioma (`__lang_version__`) se derivan exclusivamente de `src/updater.py`, eliminando la duplicación con `pyproject.toml`.
- **Documentación de Patrones de Arquitectura:** Memory index con 14 patrones documentados extraídos de 36 informes de tareas, cubriendo caché, spinner, MCP, métricas, UI, versionado y otros subsistemas.

- **Versión actual:** 0.0.32
- **Versión de los diccionarios de idioma:** v0.0.10
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
* **UI/Terminal:** Textual — TUI (Text User Interface) para chat interactivo, edición de issues, pantalla de ayuda y panel de métricas.
* **Criptografía:** `cryptography.fernet` para protección local de claves de API y tokens de GitHub.
* **Configuración:** `python-dotenv`, `pyyaml` (para el linter estático).
* **Proveedores IA:** Integración vía SDK oficial de Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), y OpenAI SDK (`Ollama` local).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — **Tool Annotations, Prompts con plantillas y recursos prompt://**.
* **Pruebas:** Pytest + `unittest.mock` (12 archivos de prueba, 131 escenarios).
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
* **Smart Excludes 🆕:** Filtro de pathspec inteligente (`gitpr.smart-excludes.json`) remoto — descargado de GitHub y actualizado automáticamente con versionado (`SMART_EXCLUDES_VERSION`). **Nueva funcionalidad:** exclusión de archivos de documentación con notificación visual (`📄 {count} documentation file(s) excluded`) y enlace `Learn more` a la documentación.
* **Métricas con Rastreo de Tiempo:** Inyección de `log_command_metric()` en todos los flujos con envío de duración en milisegundos (`duration_ms`) e importaciones diferidas para evitar importaciones circulares.

### **2. Interfaz CLI y Configuración (`src/main.py` y `src/config.py`)**

* **Configuración Inicial:** Detecta la primera ejecución, crea la carpeta `~/.gitpr/` y solicita interactivamente las claves de API, preferencias e idioma, guardándolas en un `.env`.
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
* **Métricas de Linter 🆕:** Eventos de ejecución del linter registrados vía `log_linter_metric()` con conteo de errores y warnings.

### **4. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Criptografía:** Genera una clave maestra `secret.key` en la carpeta `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data` y `decrypt_data` para proteger claves de API de IA y GitHub PAT.
* **Validación de Token de GitHub:** La función `validate_github_token()` realiza una llamada ligera (`GET /user`) para validar el PAT.
* **Flujo de Auto-Reauth:** Si el token expira o es inválido durante el `gitpr -is`, la aplicación captura la respuesta 401 HTTP, solicita un nuevo token al usuario y reinicia la interfaz TUI preservando el borrador.

### **5. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Verifica en la API de GitHub Releases la versión más reciente. Si hay divergencia, descarga el binario compilado, renombra el ejecutable actual y lo reemplaza sin interrumpir la ejecución en curso (con capacidad de rollback).
* **Caché diario:** Evita comprobaciones repetidas en el mismo día.
* **Verificación de conexión:** Socket `8.8.8.8:53` antes de cualquier operación de red.
* **Versionado Centralizado 🆕:** `__version__` (0.0.32), `__lang_version__` (v0.0.10), `__scripts_version__` (v0.0.1), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` — todos derivados exclusivamente del `updater.py`, fuente única de la verdad para el versionado.

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
* **Archivos Versionados:** `__lang_version__` (v0.0.10) controla la actualización de los paquetes de idioma (`langs/*.json`).
* **Cobertura Expandida 🆕:** 491 claves de traducción en pt_BR (+44 desde v0.0.6). Sincronización completa entre llamadas `__()` en el código fuente y diccionarios de traducción. Script `tests/sync_i18n.py` para detección automática de claves huérfanas.
* **Caché con Indexación por Idioma 🆕:** Las respuestas de IA cacheadas ahora incluyen el idioma actual en la clave MD5, evitando colisiones entre idiomas diferentes.

### **8. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en segundo plano durante llamadas de IA que muestra caracteres braille con palabras de "pensamiento".
* **Delimitador Actualizado:** Cambio del separador de frases a punto y coma (`;`), evitando que frases con comas internas se dividan incorrectamente.
* **Velocidad Adaptativa y Flickering:** Animación de descubrimiento de caracteres adaptada para frases largas y uso de ANSI `\033[K` para evitar artefactos visuales en la terminal.
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas en los archivos `templates/gitpr.thinking-words.{lang}.md`.

### **9. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Medición de Duración:** Inyección de `duration_ms` (cronometraje de alta precisión vía `time.perf_counter()`) en `meta_raw` y `_telemetry_meta`.
* **Modo JSON y Parámetros Deterministas:** Salidas estructuradas con `temperature=0.0` y `top_p=0.1`.

### **10. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Clave mediante hash MD5 del diff y prompt.
* **Indexación por Idioma 🆕:** El campo `lang` se añadió a la clave de la caché, permitiendo respuestas distintas para el mismo diff en idiomas diferentes.
* **Telemetría y Duración:** Persistencia del campo `duration_ms` y `meta_raw` en archivos de caché en `~/.gitpr/cache/prompts/`.
* **Lectura para Dashboard:** `scan_cache_files_for_dashboard()` lee todos los archivos de caché recursivamente para computar métricas históricas completas.

### **11. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`), y Arqueología por Blame (`-b`).
* **TUI Interactiva:** Edición de borradores, atajo F2 (guardar local), F3 (publicar en GitHub vía API REST) y F1 (help).
* **Manejo de 401:** Solicitud de reautenticación sin cerrar la aplicación ni perder contenido.

### **12. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y autoría histórica de fragmentos de código con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame 🆕:** Eventos de arqueología registrados vía `log_blame_metric()` con seguimiento de profundidad y número de commits analizados.

### **13. Servidor MCP e Instalador (`src/mcp_server.py`)**

* **10 Herramientas MCP Anotadas:** Annotations (`readOnlyHint`, `destructiveHint`, `idempotentHint`) configuradas para IDEs como Cursor, VS Code y Claude Code.
* **15 Recursos + 7 Prompts Templatizados:** 35 archivos de plantilla en `templates/gitpr.prompt.*.md`.
* **Instalador Automático:** Configuración de editores soportados (VS Code, Cursor, Claude Code, Claude Desktop, Zed) con fusión JSON inteligente.

### **14. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Alcance por Repositorio (Repo-Scope):** Etiqueta `📁 Repository: owner/repo` y filtrado estricto de eventos y datos de caché por proyecto.
* **Escaneo Asíncrono con Overlay:** Hilo de trabajo en segundo plano que carga datos de caché mientras muestra el widget `ProgressBar` de Textual.
* **Consolidación de Datos:** `load_cache_token_summary()` suma tokens de llamadas de caché al totalizador del panel.
* **Control de Estado de Caché:** Archivo de registro en `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Fix de Columnas en F5:** Inicialización única de columnas (`_setup_columns()`), previniendo duplicación visual en actualizaciones.
* **Exportación Local:** Guardado de CSV/JSON en `./.gitpr/metrics/export/`.

### **15. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Alcance por Repositorio:** Todos los eventos de métricas se indexan por `repo_name`, permitiendo aislamiento entre proyectos.
* **Nuevos Eventos 🆕:** `log_hook_event()` para hooks Git (pre-commit, prepare-commit-msg), `log_linter_metric()` para linter standalone, `log_blame_metric()` para arqueología de código.
* **Exportación Local:** `--metrics --export` genera CSV y JSON en `./.gitpr/metrics/export/` con filtro por repositorio.
* **Limpieza:** `--metrics --purge` elimina todos los archivos de métricas locales con confirmación interactiva.

### **16. Sincronización de Hooks Git 🆕**

* **Versionado Independiente:** `__scripts_version__` (v0.0.1) en el `updater.py` controla la versión de los scripts de hook separadamente de los diccionarios de idioma.
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
| `tests/test_blame_metrics.py` 🆕 | 10+ | Métricas de blame: profundidad, commits, duración |
| `tests/test_linter_metrics.py` 🆕 | 8+ | Métricas de linter: errores, warnings, duración |
| `tests/sync_i18n.py` 🆕 | — | Script de verificación de cobertura i18n (claves huérfanas) |

**Total:** 131 escenarios de prueba automatizados pasando con 100% de éxito.

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n Expandida 🆕:** 491 claves de traducción en pt_BR (eran 447 en v0.0.6, +44 nuevas). Sincronización completa validada para `core.py`, `main.py` y `linter_engine.py`.
* **Script de Sincronización 🆕:** `tests/sync_i18n.py` — script reutilizable para detectar claves `__()` en cualquier archivo fuente que no tengan traducción en el diccionario de idioma.
* **Nuevas Pruebas de Métricas 🆕:** `test_blame_metrics.py` (140 líneas) y `test_linter_metrics.py` (116 líneas) cubriendo la telemetría de los nuevos módulos.
* **Documentación en 5 idiomas:** 23 temas en `docs/` traducidos a EN, PT-BR, PT-PT, ES, FR.
* **Memory Index:** `.claude/memory/MEMORY.md` con 14 patrones de arquitectura extraídos de 36 informes.
* **Informes de tareas:** `docs/claude-code/reports/` y `docs/reports/`.

---

## **🔄 Pipeline de Distribución**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → carga automatizada
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server:** Entry point `gitpr-mcp` vía `pyproject.toml`

---

## **📈 Evolución desde el Informe Anterior (v0.0.6)**

| Área | v0.0.6 (anterior) | v0.0.7 (actual) |
|------|-------------------|-----------------|
| **Versión GitPR** | 0.0.30 | **0.0.32** |
| **Versión Idioma** | v0.0.8 | **v0.0.10** |
| **Versión Scripts Hook** | — | **v0.0.1** |
| **Proveedores IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Traducciones pt_BR** | 447 claves | **491 claves (+44)** |
| **Dashboard TUI** | Repo-scoped, caché ilimitado + ProgressBar + F5 fix | Repo-scoped, caché ilimitado + ProgressBar + F5 fix |
| **Smart Excludes** | Filtro de pathspec remoto | **+ Exclusión de documentación con notificación visual** |
| **Métricas y Telemetría** | Wall-clock duration + Exportación local | **+ Métricas de Linter, Blame y Git Hooks** |
| **Hooks Git** | Instalación manual (`--installhooks`) | **+ Sincronización automática con versionado** |
| **Caché i18n** | Clave mediante MD5 del diff | **+ Indexación por idioma** |
| **Versionado** | `__version__` duplicado (updater + pyproject) | **Fuente única en updater.py** |
| **Suite de Pruebas** | 114 escenarios (10 archivos) | **131 escenarios (12 archivos + sync_i18n)** |
| **Documentación de Patrones** | CLAUDE.md + GEMINI.md | **+ Memory Index con 14 patrones** |

---

## **🚧 Próximos Pasos**

* **Sincronización i18n en los demás idiomas:** Expansión de las traducciones para es_es, fr_fr y pt_pt con la misma cobertura del pt_br (491 claves).
* **Pruebas de integración end-to-end para MCP:** Validación de llamadas de herramientas y prompts mediante cliente stdio simulado.
* **Proveedor Anthropic Claude:** Soporte directo a la API de Claude (`claude-sonnet-5`).
* **Gráficos en ASCII/Textual en el Dashboard:** Añadir histogramas de tiempo y gráficos de tendencia de tokens en la TUI de métricas.
* **Pipeline de Release en GitHub Actions:** Automatización completa del build de PyInstaller y envío de assets a GitHub Releases.

---

**Informe generado el:** 2026-08-06  
**Branch:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
