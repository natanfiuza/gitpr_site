# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.28 (2026-07-24)**

---

## **📌 Visión General**

**GitPR** es una herramienta CLI avanzada para automatización de flujos Git impulsada por IA (Google Gemini / DeepSeek / Ollama). Actúa como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, crea mensajes de commit semánticos, audita deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (**Shift Left**).

**Novedad de esta versión:** Integración con **MCP (Model Context Protocol)** — GitPR ahora funciona como un servidor MCP, exponiendo todas sus capacidades de IA como herramientas directamente dentro de editores como VS Code, Cursor y Claude Desktop, sin necesidad de terminal.

- **Versión actual:** 0.0.28
- **Distribución:** PyPI (`pip install gitpr-cli`) + GitHub Releases (binario standalone)
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **Framework CLI:** Click (comandos, flags, formato de terminal)
* **UI/Terminal:** Textual — TUI para chat interactivo, edición de issues y pantalla de ayuda
* **Cifrado:** cryptography.fernet para protección local de claves API
* **Configuración:** dotenv, PyYAML (para el linter estático)
* **Proveedores IA:** SDK Google GenAI (gemini-2.5-flash), SDK OpenAI (DeepSeek), SDK OpenAI (Ollama local)
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — **NOVEDAD v0.0.28**
* **Pruebas:** Pytest + unittest.mock (8 archivos de prueba, 160+ escenarios)
* **Empaquetado:** PyInstaller (binario standalone) + setuptools/build (PyPI)
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` para ejecución en pipelines

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación Estructurada:** Se comunica con el LLM solicitando respuesta estrictamente en JSON.
* **Map-Reduce (Diffs Gigantes):** Cuando el diff supera ~90k tokens, divide automáticamente en lotes por archivo (`split_diff_into_chunks`), procesa cada parte (Map) y unifica los resúmenes (Reduce) manteniendo el tono de la arquitectura.
* **Estimación de Tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()`.
* **Optimización Nativa de Git:** Flags `-U1`, `-w`, `-M`, `-B` en `get_git_diff` y `get_git_full_diff` para reducir contexto inútil.
* **Pre-Save (`--pre-save`):** Flag oculta de debug que guarda el payload completo (system instruction + prompt) en JSON antes de cada llamada IA.
* **Smart Excludes:** Filtro pathspec inteligente (`gitpr.smart-excludes.json`) remoto — descargado desde GitHub con versionado (`SMART_EXCLUDES_VERSION`), excluyendo lock files, build artifacts y assets binarios para reducir tokens.

### **2. Interfaz CLI y Configuración (`src/main.py`, `src/config.py`)**

* **Configuración Inicial:** Detecta primera ejecución, crea la carpeta `~/.gitpr/` y guía al usuario interactivamente (claves API, preferencias, idioma) guardando en `.env`.
* **Enrutamiento de Comandos:** Gestiona todos los flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--lang`, `--provider`, `--pre-save`).
* **Ayuda Contextual:** `-h --flag` muestra documentación específica con enlace directo (language-aware) a GitHub.
* **--lang:** Fuerza el idioma de la interfaz para la ejecución actual sin persistir el cambio.
* **--provider:** Fuerza el proveedor IA (`gemini`, `deepseek`, `ollama`) para la ejecución actual.
* **--mcp:** Inicia el servidor MCP en transporte stdio para integración con editores — **NOVEDAD v0.0.28**.

### **3. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) del git diff sin gastar cuotas de IA.
* **Reglas YAML:** Lee el archivo local `.gitpr.linter.yml` (creado vía `--skill`). Soporta validación regex, ignorar comentarios y exclusión de rutas (usando fnmatch).
* **Template multilingüe:** Templates del linter disponibles en 5 idiomas.

### **4. Caja Fuerte de Seguridad (`src/security.py`)**

* **Cifrado:** Genera una clave maestra `secret.key` en la carpeta `~/.gitpr/`.
* **Funciones:** `encrypt_data` y `decrypt_data` — las claves nunca están en texto plano.
* **GitHub PAT:** Token de acceso personal de GitHub almacenado cifrado para creación de issues vía API REST.

### **5. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Verifica la API de GitHub Releases, descarga el binario compilado, renombra el ejecutable actual y lo reemplaza sin interrumpir la ejecución (con capacidad de rollback).
* **Caché diario:** Evita verificaciones repetidas el mismo día.
* **Verificación de conexión:** Socket `8.8.8.8:53` antes de cualquier operación de red.
* **Versionado de assets:** `__lang_version__`, `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` para control de actualización de templates y traducciones.

### **6. Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Construida con Textual — historial de mensajes, input multi-línea, barra de estado con bindings visibles.
* **Memoria por Rama (`src/chat_memory.py`):** Historial de conversación persistido por rama, permitiendo continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear` — atajos para acciones comunes de pair programming.
* **Auto-Patching (F5):** Extrae bloques de código sugeridos por la IA y los exporta a un archivo patch para fácil aplicación.
* **Actualización de Diff (F2):** Recarga el `git diff` actual sin reiniciar la sesión.
* **Exportación de Sesión (F6):** Guarda el historial completo del chat para documentación.
* **Comandos multilingües:** Archivos `chat_commands.{lang}.json` con traducciones de los comandos slash.

### **7. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`.
* **5 Idiomas:** en_us (defecto/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Fallback al Inglés:** Si falta una traducción, muestra el texto en inglés directamente.
* **Archivos Versionados:** `__lang_version__` controla la actualización de los paquetes de idioma (`langs/*.json`).
* **Cobertura:** Todas las interfaces, help de Click, alertas del linter, mensajes del sistema, Git Hooks, spinner, chat **y MCP** traducidos.
* **364 claves por idioma** — **NOVEDAD v0.0.28** (+42 claves MCP).

### **8. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en segundo plano durante llamadas IA mostrando caracteres braille con palabras de "pensamiento".
* **Revelación Progresiva:** Palabras reveladas letra por letra con caracteres aleatorios, seguidas de ciclo de puntos (`. .. ...`).
* **Paleta de 10 colores aleatorios** para cada palabra.
* **Multilingüe:** Thinking Words cargadas desde templates específicos por idioma (`gitpr.thinking-words.{lang}.md`), con versionado (`THINKING_WORDS_VERSION`).

### **9. Proveedores IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:**
  * **Google Gemini:** `gemini-2.5-flash` (primario) / `gemini-2.5-flash-lite` (secundario)
  * **DeepSeek:** `deepseek-chat` (primario y secundario)
  * **Ollama:** Cualquier modelo local compatible con OpenAI API
* **Arquitectura Multi-Modelo:** Fallback automático entre proveedores en caso de fallo.
* **Modo JSON:** Todos los proveedores configurados para salida estructurada (`response_mime_type` / `response_format`).
* **Parámetros deterministas:** Temperature 0.0, top_p 0.1.

### **10. Caché Inteligente (`src/cache.py`)**

* **MD5:** Hash exacto del código (diff) + instrucciones para identificar llamadas idénticas.
* **Caché por Repositorio:** JSON incluye campo `repo` para filtrado multi-proyecto.
* **Ahorro de Cuota:** Retorno en milisegundos desde la caché local (`~/.gitpr/cache/prompts/`).

### **11. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:**
  * **Issue de Código Nuevo (`gitpr -is`):** Lee el `git diff` actual.
  * **Issue de Épico/Release (`gitpr -is -ht`):** Lee el historial completo de la rama (Git Log + Caché de PR).
  * **Issue de Deuda Técnica (`gitpr -is -b archivo:líneas`):** Línea temporal vía `git blame`.
* **TUI Interactiva:** Editor de issues con syntax highlight, bindings para guardar local (F2) o enviar vía API de GitHub (F3).
* **Help Screen (F1):** Modal con atajos e instrucciones.

### **12. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea el origen de reglas de negocio con profundidad máxima de 4 commits padre.
* **Clasificación:** Modelo secundario clasifica commits como `ORIGIN` o `REFACTORING`.
* **Resumen Ejecutivo:** Modelo avanzado genera el análisis final consolidado.
* **Salida:** Terminal color-coded (verde=origin, amarillo=refactoring) + informe Markdown.

### **13. Sistema de Skills y Templates**

* **Templates Locales:** `.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md` como *System Instructions* personalizables.
* **Templates Remotos:** Descargados desde GitHub vía `--skill` (nunca sobrescribe archivos locales existentes).
* **Multilingüe:** Templates disponibles en 5 idiomas con fallback inteligente (`get_skill_context()`).
* **Carpeta `.gitpr/skill/`:** Organización limpia de los archivos de skill en el proyecto.

### **14. Optimización Map-Reduce para Diffs Gigantes**

* **Activación Automática:** Cuando el diff supera ~90k tokens estimados.
* **División Segura:** En el delimitador regex `(^diff --git a/)` para no corromper la sintaxis.
* **Rate Limiting:** `time.sleep(1)` entre lotes Map.
* **Documentación:** Página dedicada en 5 idiomas (`docs/map-reduce-diff.{lang}.md`) enlazada en la consola durante el procesamiento.
* **Progreso en Consola:** Muestra el conteo de lotes y enlace a la documentación.

### **15. Integración CI/CD**

* **GitHub Actions:** Workflow `pr-review.yml` para revisión automática de PRs.
* **Action Definition:** `action.yml` para uso como GitHub Action en pipelines externos.
* **Git Hooks Locales:** `pre-commit` (linter) y `prepare-commit-msg` (generación de mensaje por IA) instalables vía `--installhooks`.

### **16. Servidor MCP — Integración con Editores e IDEs (`src/mcp_server.py`)** 🆕

* **10 Herramientas MCP:** `get_git_context`, `analyze_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **7 Recursos MCP:** Templates de skill (`skill://pr`, `skill://commit`, etc.) + config del linter (`linter://config`).
* **Transporte stdio:** Comunicación vía JSON-RPC 2.0 — estándar para herramientas CLI locales.
* **Aislamiento de Output:** Sistema de monkey-patching que redirige todo el output del terminal (banners, spinners, colores) a stderr, garantizando que el canal stdout se mantenga limpio para el protocolo MCP.
* **Comando `gitpr-mcp`:** Entry point dedicado registrado en `pyproject.toml`.
* **Flag `--mcp`:** Alias vía CLI principal (`gitpr --mcp`).

### **17. Instalador MCP (`gitpr-mcp --install`)** 🆕

* **6 Editores Soportados:** VS Code (`.vscode/mcp.json`), Cursor (`.cursor/mcp.json`), Claude Code (`.mcp.json`), Claude Desktop (global), Zed (global).
* **Modo Auto:** Detecta automáticamente qué editores están configurados e instala para todos.
* **Merge Inteligente:** Añade el servidor GitPR sin eliminar servidores existentes — idempotente y seguro.
* **Creación de Directorios:** Crea automáticamente `.vscode/`, `.cursor/` o el directorio global si no existen.

---

## **📊 Pruebas y Calidad**

| Archivo de Prueba | Escenarios | Foco |
|---|---|---|
| `tests/test_core.py` | 25+ | Flujos principales, git diff, PR generation |
| `tests/test_chat_backend.py` | 30+ | Memoria de chat, persistencia, comandos |
| `tests/test_skill_command.py` | 10+ | Descarga y validación de templates |
| `tests/test_pre_save.py` | 10+ | Flag --pre-save y payload JSON |
| `tests/test_smart_excludes.py` | 14+ | Filtro pathspec inteligente |
| `tests/test_thinking_words.py` | 10+ | Carga y parsing de thinking words |
| `tests/test_mcp_server.py` 🆕 | 33 | Herramientas MCP, recursos, patching de output, safe-call wrapper |

---

## **🌐 Internacionalización y Documentación**

* **364 claves de traducción** por idioma (5 idiomas = 1.820 traducciones).
* **Documentación completa en 5 idiomas:** 20 temas × 5 idiomas = 100+ páginas de documentación.
* **Nueva documentación MCP:** `docs/mcp-integration.md` + 4 traducciones (PT-BR, PT-PT, ES, FR).
* **Planes de desarrollo:** 7 planes documentados en `docs/plans/`.
* **Informes Claude Code:** 11+ informes de tareas en `docs/claude-code/reports/develop_natan/`.
* **Sitio oficial:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
* **READMEs sincronizados:** Links relativos convertidos a absolutos (compatible con PyPI).

---

## **🔄 Pipeline de Distribución**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → upload vía workflow
3. **GitHub Actions:** PR Review automatizado con `action.yml`
4. **MCP:** `gitpr-mcp` registrado como entry point en `pyproject.toml` → instalado automáticamente con `pip install` 🆕

---

## **📈 Evolución Desde el Informe Anterior (v0.0.2)**

| Área | v0.0.2 (anterior) | v0.0.3 (actual) |
|---|---|---|
| **Proveedores IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interfaz** | CLI + TUI Issues + Chat TUI | CLI + TUI Issues + Chat TUI + **MCP Server** |
| **MCP (Model Context Protocol)** | — (planeado) | **Servidor MCP completo con 10 tools + 7 resources** |
| **MCP Installer** | — | **`gitpr-mcp --install` para 6 editores** |
| **Integración con Editores** | — (solo terminal) | **VS Code, Cursor, Claude Code, Claude Desktop, Zed** |
| **Documentación MCP** | — | **5 idiomas (EN, PT-BR, PT-PT, ES, FR)** |
| **Claves i18n** | 322 claves/idioma | **364 claves/idioma (+42 MCP)** |
| **Pruebas** | 7 archivos (130+ escenarios) | **8 archivos (160+ escenarios)** |
| **Dependencias** | 8 paquetes | **9 paquetes (+mcp>=1.0.0)** |
| **README PyPI** | Links relativos (rotos) | **Links absolutos (funcionales en PyPI)** |
| **Versión** | 0.0.27 | **0.0.28** |

---

## **🚧 Próximos Pasos**

* **Pruebas de integración:** Cobertura end-to-end de los flujos principales, incluyendo pruebas del servidor MCP.
* **MCP Prompts:** Añadir prompts MCP (plantillas de mensaje) para flujos comunes como "revisar PR".
* **MCP Annotations:** Tool annotations (`readOnlyHint`, `destructiveHint`) para mejor integración con IDEs.
* **Más proveedores:** Claude API, OpenAI directo, proveedores locales adicionales.
* **Métricas y analytics:** Panel de uso para equipos.
* **Sistema de plugins:** Extensibilidad para reglas de linter y prompts personalizados.
* **Migración MCP SDK v2:** Monitorear la estabilización del SDK v2.x (modo stateless, tasks).

---

**Informe generado el:** 2026-07-24
**Rama:** `develop_natan`
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))

---

[← Contribución](/contribuicao) &nbsp;|&nbsp; [Inicio →](/index)
