# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.27 (2026-07-19)**

---

## **📌 Visión General**

**GitPR** es una herramienta CLI avanzada para automatización de flujos Git impulsada por IA (Google Gemini / DeepSeek / Ollama). Actúa como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, crea mensajes de commit semánticos, audita deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (**Shift Left**).

- **Versión actual:** 0.0.27
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
* **Pruebas:** Pytest + unittest.mock (7 archivos de prueba, 130+ escenarios)
* **Empaquetado:** PyInstaller (binario standalone) + setuptools/build (PyPI)
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` para ejecución en pipelines

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación Estructurada:** Se comunica con el LLM solicitando respuesta estrictamente en JSON.
* **Map-Reduce (Diffs Gigantes):** Divide automáticamente en lotes por archivo (`split_diff_into_chunks`), procesa cada parte (Map) y unifica los resúmenes (Reduce).
* **Estimación de Tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()`.
* **Optimización Nativa de Git:** Flags `-U1`, `-w`, `-M`, `-B` para reducir contexto inútil.
* **Pre-Save (`--pre-save`):** Flag oculta de debug que guarda el payload completo en JSON antes de cada llamada IA.
* **Smart Excludes:** Filtro pathspec inteligente remoto — descargado desde GitHub con versionado (`SMART_EXCLUDES_VERSION`).

### **2. Interfaz CLI y Configuración (`src/main.py`, `src/config.py`)**

* **Configuración Inicial:** Detecta primera ejecución y guía al usuario interactivamente.
* **Enrutamiento de Comandos:** Gestiona todos los flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--lang`, `--provider`, `--pre-save`).
* **Ayuda Contextual:** `-h --flag` muestra documentación específica con enlace a GitHub.
* **--lang:** Fuerza el idioma de la interfaz para la ejecución actual.
* **--provider:** Fuerza el proveedor IA (`gemini`, `deepseek`, `ollama`) para la ejecución actual.

### **3. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza las líneas añadidas (`+`) del git diff sin gastar cuotas de IA.
* **Reglas YAML:** Lee el archivo `.gitpr.linter.yml`. Soporta validación regex, ignorar comentarios y exclusión de rutas.
* **Template multilingüe:** Templates del linter disponibles en 5 idiomas.

### **4. Caja Fuerte de Seguridad (`src/security.py`)**

* **Cifrado:** Genera una clave maestra `secret.key` en `~/.gitpr/`.
* **Funciones:** `encrypt_data` y `decrypt_data` — las claves nunca están en texto plano.
* **GitHub PAT:** Token GitHub almacenado cifrado para creación de issues vía API REST.

### **5. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Verifica la API de GitHub Releases, descarga y reemplaza el binario sin interrumpir la ejecución (con rollback).
* **Caché diario:** Evita verificaciones repetidas el mismo día.
* **Verificación de conexión:** Socket `8.8.8.8:53` antes de cualquier operación de red.
* **Versionado de assets:** `__lang_version__`, `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`.

### **6. Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Construida con Textual — historial, input multi-línea, barra de estado.
* **Memoria por Rama (`src/chat_memory.py`):** Historial persistido por rama.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`.
* **Auto-Patching (F5):** Extrae bloques de código sugeridos a un archivo patch.
* **Actualización de Diff (F2):** Recarga el `git diff` sin reiniciar.
* **Exportación de Sesión (F6):** Guarda el historial completo en Markdown.

### **7. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con placeholders nombrados.
* **Detección Automática:** Detecta el idioma del SO y lo guarda en `GITPR_LANG`.
* **5 Idiomas:** en_us (defecto/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Fallback al Inglés:** Texto en inglés si falta la traducción.
* **Cobertura completa:** Todas las interfaces, help de Click, alertas del linter, mensajes del sistema, Git Hooks, spinner y chat.

### **8. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en segundo plano mostrando caracteres braille con palabras de «pensamiento».
* **Revelación Progresiva:** Palabras reveladas letra por letra, seguidas de ciclo de puntos.
* **Paleta de 10 colores aleatorios.**
* **Multilingüe:** Thinking Words cargadas desde templates por idioma con versionado.

### **9. Proveedores IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:**
  * **Google Gemini:** `gemini-2.5-flash` (primario) / `gemini-2.5-flash-lite` (secundario)
  * **DeepSeek:** `deepseek-chat` (primario y secundario)
  * **Ollama:** Cualquier modelo local compatible con OpenAI API
* **Arquitectura Multi-Modelo:** Fallback automático entre proveedores.
* **Modo JSON:** Todos los proveedores configurados para salida estructurada.
* **Parámetros deterministas:** Temperature 0.0, top_p 0.1.

### **10. Caché Inteligente (`src/cache.py`)**

* **MD5:** Hash exacto del código (diff) + instrucciones.
* **Caché por Repositorio:** JSON con campo `repo` para filtrado multi-proyecto.
* **Ahorro de Cuota:** Retorno en milisegundos desde `~/.gitpr/cache/prompts/`.

### **11. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:**
  * **Issue de Código Nuevo (`gitpr -is`):** Lee el `git diff` actual.
  * **Issue de Épico/Release (`gitpr -is -ht`):** Lee el historial completo de la rama.
  * **Issue de Deuda Técnica (`gitpr -is -b archivo:líneas`):** Línea temporal vía `git blame`.
* **TUI Interactiva:** Editor de issues con syntax highlight, guardar local (F2) o enviar vía API GitHub (F3).

### **12. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea el origen de reglas de negocio (profundidad máx. 4 commits padre).
* **Clasificación:** Modelo secundario clasifica commits como `ORIGIN` o `REFACTORING`.
* **Resumen Ejecutivo:** Modelo avanzado genera el análisis final consolidado.
* **Salida:** Terminal color-coded (verde=origin, amarillo=refactoring) + informe Markdown.

### **13. Sistema de Skills y Templates**

* **Templates Locales:** `.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md`.
* **Templates Remotos:** Descargados desde GitHub vía `--skill`.
* **Multilingüe:** Templates disponibles en 5 idiomas con fallback inteligente.
* **Carpeta `.gitpr/skill/`:** Organización limpia de archivos de skill.

### **14. Optimización Map-Reduce para Diffs Gigantes**

* **Activación Automática:** Cuando el diff supera ~90k tokens estimados.
* **División Segura:** En el delimitador regex `(^diff --git a/)`.
* **Rate Limiting:** `time.sleep(1)` entre lotes Map.
* **Documentación dedicada en 5 idiomas** enlazada en la consola.

### **15. Integración CI/CD**

* **GitHub Actions:** Workflow `pr-review.yml` para revisión automática de PRs.
* **Action Definition:** `action.yml` para uso como GitHub Action externa.
* **Git Hooks Locales:** `pre-commit` (linter) y `prepare-commit-msg` (mensaje IA) instalables vía `--installhooks`.

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

---

## **🌐 Internacionalización y Documentación**

* **130+ archivos** traducidos/versionados.
* **Documentación completa en 5 idiomas:** 19 temas × 5 idiomas = 95+ páginas.
* **Planes de desarrollo:** 6 planes documentados en `docs/plans/`.
* **Informes Claude Code:** 10+ informes en `docs/claude-code/reports/develop_natan/`.
* **Sitio oficial:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)

---

## **🔄 Pipeline de Distribución**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → upload vía workflow
3. **GitHub Actions:** PR Review automatizado con `action.yml`

---

## **📈 Evolución Desde el Informe Anterior (v0.0.1)**

| Área | v0.0.1 (anterior) | v0.0.2 (actual) |
|---|---|---|
| **Proveedores IA** | Gemini + DeepSeek | Gemini + DeepSeek + **Ollama (local)** |
| **Idiomas** | 2 (en, pt_br) | **5 (en, pt_br, pt_pt, es_es, fr_fr)** |
| **Interfaz** | CLI + TUI Issues | CLI + TUI Issues + **Chat TUI Interactivo** |
| **Templates** | EN + PT-BR | **5 idiomas** |
| **Documentación** | Parcial | **95+ páginas en 5 idiomas** |
| **Pruebas** | 1 archivo | **7 archivos (130+ escenarios)** |
| **CI/CD** | — | **GitHub Actions + action.yml** |
| **Smart Excludes** | Local | **Remoto con versionado** |
| **Thinking Words** | Estático | **Multilingüe con versionado** |
| **Pre-Save** | — | **Flag de debug para inspección de payload** |
| **Chat Memory** | — | **Persistencia por rama** |
| **Map-Reduce Docs** | — | **Documentación en 5 idiomas** |
| **Sitio Web** | — | **gitpr.natanfiuza.dev.br** |

---

## **🚧 Próximos Pasos**

* **Pruebas de integración:** Cobertura end-to-end de los flujos principales.
* **MCP (Model Context Protocol):** Integración potencial con editores e IDEs.
* **Más proveedores:** Claude API, OpenAI directo, proveedores locales adicionales.
* **Métricas y analytics:** Panel de uso para equipos.
* **Sistema de plugins:** Extensibilidad para reglas de linter y prompts personalizados.

---

**Informe generado el:** 2026-07-19
**Rama:** `develop_natan`
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))

---

[← Contribución](/contribuicao) &nbsp;|&nbsp; [Inicio →](/index)
