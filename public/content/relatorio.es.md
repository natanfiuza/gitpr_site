# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.16 (2026-09-23)**

## **📌 Visión General**

**GitPR** es una herramienta CLI (Command Line Interface) avanzada para la automatización de procesos Git mediante Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). Su objetivo principal es actuar como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita la deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.16):**
- **`gitpr demo` — la primera ejecución dejó de ser un acto de fe:** Un recorrido guiado que muestra un mensaje de commit, un review y una descripción de PR **sin clave de API, sin repositorio git y sin red**. La pregunta que responde ("¿qué hace esta herramienta?") tiene una sola ventana en la que puede formularse — el primer uso, antes de que el usuario haya configurado un proveedor. El recorrido pasa por el **pipeline real de generación** con la fuente de la respuesta intercambiada, así que lo que aparece es lo que la herramienta produce de verdad, mientras cada efecto externo (caché, métricas, disco, red, lectura de claves) queda neutralizado.
- **Badge de GitPR en el cuerpo del PR + comando `gitpr badge`:** Un PR escrito por IA era indistinguible de uno escrito a mano, y el resultado del linter moría en un terminal que ya se había desplazado fuera de la pantalla. El badge convierte esa señal privada en una declaración visible en el propio PR publicado — y es deliberadamente una URL estática de shields.io, que GitPR **nunca** consulta, para que publicar un PR no pase a depender de que un tercero esté en línea. Medición honesta: sin reglas de linter configuradas el badge **no** se emite, porque una lista vacía significa "nada se verificó", no "nada se encontró".
- **`gitpr split` — un árbol de trabajo con varias intenciones deja de convertirse en un commit-blob:** Lee el diff no commiteado, pide a la IA que particione los hunks por intención lógica y propone **un commit atómico por asunto**, cada uno con un mensaje generado a partir del patch de ese grupo aislado. El árbol nunca se reescribe: los archivos terminan byte a byte idénticos al inicio — solo cambia la historia. *(El índice, sí: `--apply` resetea a HEAD antes de hacer el staging.)*
- **Escaneo de secretos integrado — la regla que no puede sobrescribirse:** Siete reglas (`src/security_ruleset.py`) que corren en **cada** invocación del linter: cinco `error` bloqueantes (ID de clave AWS, token de GitHub/Slack, clave de Google, bloque de clave privada) y dos `warning`. El catálogo local era enteramente gestionado por el usuario — podía ser sobrescrito por la descarga, reescrito por el wizard (perdiendo comentarios) o extendido solo por plugins locales de la máquina. Una puerta de secretos debe comportarse igual en toda máquina, así que las reglas ahora viven **dentro del paquete**. **Cambio de comportamiento: un commit que antes pasaba ahora puede quedar bloqueado.**
- **Soporte de `extensions: ["*"]`:** Ahora significa *todo* archivo, incluidos los que no tienen sufijo — que es exactamente de donde se filtran los secretos (`id_rsa`, `.env`, `credentials`, `Dockerfile`). Una regla con `extensions: ["py"]` mantiene el filtro exactamente como antes.
- **Puentes SAST opt-in — Semgrep, Gitleaks y Bandit:** Una capa que conecta escáneres de seguridad de terceros al linter existente. Corren **solo** cuando están habilitados, **solo** sobre los archivos tocados por el diff, y los hallazgos se deduplican contra el ruleset interno: un secreto visto por ambos aparece **una vez** con un marcador de confirmación multi-fuente (`[Gitleaks + Regex]`). Gitleaks tiene sus valores enmascarados (`AKIA****`) antes de convertirse en un hallazgo.
- **`gitpr tests generate` — la suite que respeta la convención del repositorio:** Genera archivos de prueba completos a partir del diff, de un archivo específico o de un hallazgo de review. Detecta el framework en uso (Pest, PHPUnit, Jest, Vitest, Pytest) en lugar de imponer un estilo, calcula la ruta de destino convencional (el split `Feature`/`Unit` de Laravel, `tests/**/test_*.py` de Pytest) y valida la sintaxis con el toolchain local (`php -l`, `node --check`, `python -m py_compile`) — un fallo de validación se convierte en un warning, no en un error. El dry run es el comportamiento predeterminado.
- **`gitpr explain` + la flag `--explain` — la guía para quien va a revisar:** Una guía centrada en el revisor (qué cambia, por qué cambia, dónde enfocarse, cuál es el riesgo de regresión) para que nadie tenga que reconstruir la intención desde un diff crudo. Disponible como subcomando propio y como flag que anexa la sección a la descripción del PR generada.
- **Arquitectura en capas — `src/domain/` y `src/application/`:** Las dos funcionalidades más recientes (`tests` y `explain`) nacieron con una separación explícita entre reglas de dominio puras y orquestación de casos de uso, y la CLI y la TUI de chat ahora comparten **el mismo** caso de uso en lugar de duplicarlo.
- **Suite determinista y la primera CI:** `tests/conftest.py` pasó a ser hermético (fija `GITPR_LANG=en_us` y apaga el ruleset de secretos) y `.github/workflows/tests.yml` corre la suite en **Python 3.10** (el piso declarado, nunca ejercitado) y 3.13. Fue la CI la que hizo visible la deriva de locale — **22 pruebas** fallaban en una máquina pt-BR por asertar el literal en inglés.
- **Los 3 fallos heredados de tres informes seguidos quedaron cerrados:** las dos pruebas de timeout desactualizadas (`600s` vs. el default real de `180s`) y la prueba sensible al locale. La línea base de la suite dejó de ser "3 fallos conocidos" y pasó a estar **verde por construcción**.
- **Deuda nueva y concentrada:** las dos funcionalidades más recientes (`tests` y `explain`) llegaron con el registry de skills **a medias** — **40 claves `__()`** usadas en el código no existen en ninguno de los 6 diccionarios, y los registries de etiquetas de la config y del MCP no recibieron los tipos nuevos. Eso son **4 fallos** en la suite completa, todos con la misma causa raíz.
- **Estado del release 1.3.0:** `__version__` y `__lang_version__` están en el **working tree y sin commitear** (HEAD sigue en 1.2.0 / v0.0.31), el `CHANGELOG.md` **tiene** la entrada `[1.3.0] - 2026-09-21` — pero cubre **solo** el escaneo de secretos, y no `demo`, `badge`, `split`, SAST, `tests` ni `explain`. El tag `v1.2.0` **fue creado** (merge del PR #174), cerrando el punto que bloqueaba la ventana anterior.

- **Versión actual:** 1.3.0 (bump en el working tree — HEAD en 1.2.0; último tag `v1.2.0`)
- **Versión de los diccionarios de idioma:** v0.0.32 (bump en el working tree — HEAD en v0.0.31)
- **Versión de los scripts de hook:** v0.0.3
- **Publicación:** PyPI (`pip install gitpr-cli`) — canal binario eliminado
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 diccionarios)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal) — **9 subcomandos** 🆕 (`badge`, `config`, `demo`, `explain`, `fix`, `release`, `review-pr`, `split`, `tests`).
* **UI/Terminal:** Textual — TUI para chat interactivo, edición de issues, help screen, dashboard de métricas, PR Publisher, errores del linter (`LinterApp`), configuración (`ConfigApp`), el modal `NoticeScreen` y el **recorrido `gitpr demo`** (`src/ui/demo/`) 🆕.
* **Arquitectura en Capas 🆕:** `src/domain/` (reglas puras, sin I/O) + `src/application/use_cases/` (orquestación) + presentación (CLI/TUI). Introducida con `tests` y `explain`; la CLI y el `/tests` del chat llaman **al mismo** caso de uso.
* **Criptografía:** `cryptography.fernet` para protección local de claves de API, tokens de GitHub y tokens SCM de los forges.
* **Configuración:** `python-dotenv`, `pyyaml` (linter estático) + su propio schema declarativo (`src/config_schema.py`, **71 `ConfigField`** 🆕).
* **Proveedores de IA:** Integración vía SDK oficial de Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`) y OpenAI SDK (`Ollama` local).
* **APIs de Forge:** `requests` (REST) — capa de abstracción multi-forge en `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps); `src/github_api.py` mantenido como shim deprecado.
* **Linter / SAST 🆕:** YAML regex + bridge Checkstyle + **ruleset de secretos integrado** (`src/security_ruleset.py`) + **4 puentes en `src/infrastructure/linter/external/`** (base + Semgrep, Gitleaks, Bandit), con un modelo normalizado en `src/domain/linter/sast_finding_mapper.py`.
* **Internals de Git 🆕:** `src/infrastructure/git/` — `patch_applier.py` (movido desde `src/fix/`) y `selective_stager.py` (staging de un subconjunto arbitrario de hunks).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **14 herramientas anotadas, 18 recursos, 7 prompts** (conteo sin cambios en esta ventana).
* **Pruebas:** Pytest + `unittest.mock` (**96 módulos de prueba — 44 en la raíz, 9 en `tests/scm/`, 10 en `tests/fix/`, 6 en `tests/split/`, 6 en `tests/demo/`, 7 en `tests/badge/`, 4 en `tests/review/`, 4 en `tests/infrastructure/linter/external/`, 2 en `tests/domain/tests_generation/`, 2 en `tests/application/use_cases/`, 1 en `tests/domain/pr/`, 1 en `tests/domain/linter/`** 🆕 —, **1889 escenarios recolectados**) + pruebas e2e del servidor MCP vía subprocess real (JSON-RPC stdio) + fixtures de repositorio git real (`tests/fix/git_fixture.py`, `tests/split/git_fixture.py`) + un **guard de aislamiento de red** (`tests/demo/conftest.py`).
* **Empaquetado:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`.
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` + **`tests.yml`** 🆕 (matriz Python 3.10/3.13).

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación Estructurada:** Se comunica con el LLM pidiendo salida estrictamente JSON.
* **Map-Reduce (Diffs Gigantes):** Cuando el diff supera ~90k tokens, lo divide automáticamente en lotes por archivo (`split_diff_into_chunks`), procesa cada parte (Map) y unifica los resúmenes (Reduce). Soporta PRs, commits e Issues.
* **Tokenizer Local:** `tokenizer.json` para estimación precisa de tokens antes del envío a la IA.
* **Estimación de Tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()` con fallback al tokenizer local.
* **Optimización Nativa de Git:** Flags `-U1`, `-w`, `-M`, `-B` en los comandos `get_git_diff` y `get_git_full_diff` para reducir contexto inútil.
* **`get_split_diff()` 🆕:** El diff propio del `split`, con `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sin** reutilizar `get_git_diff()` — el `-w` (ignora espacios en blanco) y los smart-excludes romperían la garantía de árbol byte-idéntico, porque el patch reconstruido tiene que casar con el archivo en disco.
* **Pre-Save (`--pre-save`):** Flag oculta de debug que guarda el payload completo (instrucción del sistema + prompt) en JSON antes de cada llamada a la IA.
* **Smart Excludes con Dos Capas:** Capa global (`~/.gitpr/conf/`) + capa local del proyecto (`./.gitpr/conf/`), fusionadas en runtime. Auto-seed del archivo local en la primera ejecución. `_load_smart_excludes()` acepta `force=`.
* **Métricas con Seguimiento de Tiempo:** `log_command_metric()` en todos los flujos con `duration_ms` y lazy imports.
* **Resolución Centralizada de Salida:** `resolve_output_path()` — por defecto en `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`)**: `gitpr --init` — detecta el forge desde el remote origin, solicita extras por forge, valida el token y persiste **solo en caso de éxito**. 🆕 Anuncia el badge automático en el camino de PR e informa el switch exacto que lo apaga.
* **Plantilla de Skill de Release (`ensure_release_skill_template()`)**: Descarga `templates/gitpr.release.*.md` en el primer uso de `gitpr release`.
* **Registry Compartido de Skills:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` en `src/config.py`. 🆕 Ganó entradas para `tests` (`.gitpr.tests.md`) y `explain` (`.gitpr.explain.md`).
* **Motor de Review con Alcance de Caché (`generate_pr_content`):** `cache_scope` (anexado **solo a la clave de caché**) y `store_diff` (graba el diff revisado). Los valores predeterminados mantienen el camino local byte-idéntico.
* **Trailer de Coautoría:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceros.
* **Subprocesses Blindados:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` en todos los `subprocess.run`; verificación de conexión vía socket `8.8.8.8:53` antes de las operaciones de red.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitectura de Plugins:** Carga plugins de `~/.gitpr/plugins/`, aplicándose a **todos los proyectos**.
* **Plugins de Linter (`linter/`):** Archivos `.yml` fusionados con el `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`):** Archivos `.md` que extienden el contexto del sistema.
* **Factory Closures:** `get_linter_plugins` y `get_prompt_plugins` aíslan estado entre sesiones.
* **Comando `--plugins`:** Lista todos los plugins globales instalados con sus tipos y rutas.
* **Documentación Multilingüe:** `docs/plugins-system.md` en 5 idiomas.

### **3. Interfaz CLI y Configuración (`src/main.py` y `src/config.py`)**

* **Setup Inicial:** Detecta la primera ejecución, crea `~/.gitpr/` y solicita interactivamente las claves, preferencias e idioma. 🆕 El hint de la primera ejecución apunta a `gitpr demo`.
* **Enrutamiento de Comandos:** Gestiona todas las flags y los **9 subcomandos** — `release`, `config`, `fix`, `review-pr`, **`demo`** 🆕, **`badge`** 🆕, **`split`** 🆕, **`tests`** 🆕 (grupo, con `generate`) y **`explain`** 🆕.
* **Comportamiento Predeterminado:** Ejecutar `gitpr` sin flags abre la TUI del PR Publisher.
* **Flags (ahora 36 opciones Click en la raíz, +1 en esta ventana):**
  * **`--explain` 🆕:** Incluye la sección *Reviewer Guide* en la descripción del PR (y en el payload JSON emitido).
  * `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--linter-setup`, `--version` — sin cambios.
* **Subcomando `demo` 🆕 — 3 opciones:** `--scenario`, `--lang` y `--no-tui` (front-end de texto plano para CI y grabaciones). El `--lang` se aplica **dentro** del subcomando, porque el callback de la raíz retorna antes que el handler.
* **Subcomando `badge` 🆕 — 2 opciones:** `--readme` (forma desnuda del snippet, amigable para pipes) y `--style` (`flat`/`flat-square`/`for-the-badge`). **Solo imprime** — el README nunca se modifica.
* **Subcomando `split` 🆕 — 5 opciones:** `--dry-run`, `--apply`, `--yes`, `--max-groups` y `--provider`. Una invocación desnuda imprime el plan y pregunta antes de commitear nada.
* **Grupo `tests` 🆕 → subcomando `generate` — 5 opciones:** `--file`, `--finding`, `--framework`, `--apply` y `--provider`. El dry run es el comportamiento predeterminado y sobrescribir una prueba existente pide confirmación con **No** preseleccionado.
* **Subcomando `explain` 🆕 — 1 opción:** `--provider`. Detecta un diff ausente con un error claro antes de cualquier llamada de IA.
* **Variables de Entorno (49 claves en `DEFAULT_CONFIG`, +5 en esta ventana):** `GITPR_LINTER_SECURITY`, `GITPR_LINTER_SECURITY_DISABLED_RULES` y las tres `GITPR_SPLIT_*` (`MAX_GROUPS`, `MAX_HUNKS`, `REQUIRE_CONFIRMATION`). **Otras 5 son de solo lectura / no sembradas:** `GITPR_BADGE` (predeterminado `true`), `GITPR_EXPLAIN_BY_DEFAULT` (predeterminado `false`) y las tres `GITPR_SAST_*_ENABLED` — GitPR **nunca** las escribe en `~/.gitpr/.env` por su cuenta.
* **Ayuda Contextual:** `-h --flag` muestra documentación específica de la funcionalidad con un enlace consciente del idioma. Los subcomandos tienen su propio `epilog=` (párrafo Click `\b` para que la URL no se re-envuelva bajo ningún locale). 🆕 `explain` se sumó a `HELP_MAP`/`HELP_PRIORITY`.
* **--lang / --provider / --mcp / --install / --metrics / --status** — sin cambios.
* **Capa de Escritura del `.env`:** `read_env_file_values()` lee **solo el archivo** vía `dotenv_values`; `save_config_values()` con `set_key`; `remove_config_value()` con `unset_key`. `validate_ai_key()` distingue una credencial rechazada (`401`/`403`) de una red inalcanzable.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)**

* **Interfaz Interactiva Completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en el terminal.
* **7 Pantallas Modales:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` y `NoticeScreen`.
* **Badge en el Cuerpo del PR 🆕:** `attach_pr_badge()` corre en `src/main.py` justo después de que `pr_data` está completo — un **punto de inyección único**, así que ambos publicadores (el área de texto de la TUI y `--no-edit`) leen el badge del mismo sitio. El usuario **ve** el badge y puede borrarlo antes de enviar; `--no-edit` imprime una línea diciendo qué entró en el cuerpo que no vio. Anexado idempotente por marcador — republicar o mover el badge no apila badges.
* **`_attach_reviewers` con resolución:** Resuelve lo que el usuario escribió **antes** de enviar, reporta lo que fue descartado y, en un `422` de lote, **repite uno a uno** — GitHub rechaza el lote entero cuando un único login es inelegible.
* **Revisores Sugeridos:** Consulta al forge los revisores sugeridos; `_reviewer_suggestion_view()` monta `resolutions` vía `resolve_candidates` y prellena `handles`.
* **Bindings:** F1 (Help), F2 (Guardar .md local), F3 (Publicar vía forge), Esc (Salir).
* **Flujo de Auto-Commit:** Linter → mensaje IA → confirmación → commit → push → publica PR.
* **Verificación de Archivos Unstaged:** `git status --porcelain` al iniciar, con un modal para seleccionar, saltar o cancelar.
* **Manejo de PR Existente / Auto-Upstream / Flujo de Merge** — sin cambios (`GITPR_AUTO_MERGE`).

### **5. Módulo de API de GitHub (`src/github_api.py`)**

* **Shim Deprecado:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` y las demás funciones delegan en `src/infrastructure/scm/github_provider.py`; emite un `DeprecationWarning` y mantiene las tuplas legacy `(ok, data, status)` — ningún código nuevo debe importarlo.

### **6. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) en el diff sin gastar cuota de IA.
* **Reglas YAML:** Lee `.gitpr.linter.yml` (creado vía `--skill`).
* **Plugins de Linter:** Reglas adicionales desde `~/.gitpr/plugins/linter/*.yml`.
* **Comodín de Extensión 🆕:** `extensions: ["*"]` ahora significa **todo** archivo, incluidos los que no tienen sufijo y los dotfiles. Es lo que da cobertura a `id_rsa`, `.env`, `credentials` y `Dockerfile`; una regla con `extensions: ["py"]` mantiene el filtro como siempre fue.
* **Ruleset de Secretos Integrado 🆕:** `load_linter_rules()` fusiona `src/security_ruleset.py` **después** de las reglas del proyecto y de los plugins, que quedan intactas — solo cambia el orden, empujando las alertas de seguridad al final del informe. Siete reglas, todas `extensions: ["*"]`: cinco `error` (ID de clave AWS, token de GitHub/Slack, clave de Google, bloque de clave privada) y dos `warning` (URL de base de datos con credenciales y asignación genérica de credencial, tras un filtro de placeholders).
* **La Alerta Nunca Repite el Valor:** El mensaje lleva solo `{file_name}` y `{line_number}` — viaja a la consola, al informe Markdown y, en el flujo de PR, al cuerpo de un pull request público; imprimir el secreto lo copiaría a los tres.
* **`--input` Ampliado 🆕:** La auditoría de archivo completo ahora escanea `.md`, `.txt` y lockfiles, ya que el ruleset casa toda extensión. Allí **informa sin bloquear** — el `sys.exit(1)` existe solo en el camino `--linter`.
* **Puentes SAST Opt-In 🆕:** Semgrep, Gitleaks y Bandit corren en el camino de archivo completo **y** en el camino del diff, filtrando hallazgos por líneas añadidas y avisando cuando la herramienta está habilitada pero falta del `PATH`. `load_sast_config()` resuelve en tres capas: defaults → `.gitpr.linter.yml` (bloque `sast`, con fallback a `linter.external`) → variables `GITPR_SAST_*`. **Todo es `false` por defecto** (opt-in estricto).
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=False)` apaga **los dos** call-sites del puente externo; el flujo remoto pasa `True`, porque el puente ejecuta binarios contra archivos en disco — el árbol local, no el PR.
* **Informe Consolidado:** `generate_linter_report_content()` consolida errores regex + externos en `.gitpr/reports/linter/` — generado solo cuando hay violaciones.
* `load_linter_presets()` acepta `force=` para redescargar desde la TUI.

### **7. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cifrado:** Genera la clave maestra `secret.key` en `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data`/`decrypt_data` para claves de IA, PAT de GitHub y tokens SCM de los forges.
* **Validación Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — 401 → bucle de reautenticación preservando el borrador; el token legacy de GitHub sigue funcional hasta que se ejecute `--init`.
* **Secretos en la TUI de Configuración:** Los campos `KIND_SECRET` se editan enmascarados, **nunca** muestran el valor en claro y se cifran con Fernet antes de escribirse. `GITPR_SCM_TOKEN` es `read_only`.
* **Escaneo de Secretos en el Propio Flujo 🆕:** El linter — que corre en el hook pre-commit — pasó a ser una puerta de credenciales con reglas idénticas en toda máquina, porque viven en el paquete y no en una plantilla descargada que `--skill` o el wizard puedan reemplazar.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI como Única Fuente:** `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` y escribe la caché diaria **sin** el campo `download_url`.
* **Puerta Obligatoria (`enforce_update_required()`):** Devuelve `True` (tras imprimir ambas versiones y el comando pip) cuando la versión publicada es más reciente; `False` cuando está al día, cuando la versión remota es **desconocida (offline)** o cuando la verificación está apagada. Devolver un `bool` en lugar de llamar a `sys.exit` internamente mantiene la función testeable.
* **`check_and_update()`:** Solo consulta e **informa**, nunca instala.
* **Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` — no se anuncia; existe para la suite y para la automatización offline.
* **Versionado Centralizado:** `__version__` (**1.3.0** — bump en el working tree), `__lang_version__` (**v0.0.32** — cadena v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32 en esta ventana 🆕), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.

### **9. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Historial de mensajes, entrada multi-línea, barra de estado con bindings visibles.
* **Memoria por Rama (`src/chat_memory.py`):** Historial persistido por rama, con continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`. 🆕 `/tests` (y los alias localizados `/testes`, `/pruebas`) **dejó de reenviarse al modelo genérico** y ahora delega en el **mismo caso de uso** que usa la CLI (`TestGenerationTarget`), en lugar de duplicar la lógica.
* **Auto-Patching (F5), Actualización de Diff (F2), Exportación de Sesión (F6).**
* **Extractor Compartido:** F5 y `ctrl+s` llaman a `patch_extractor.extract_code_blocks()` — comportamiento visible idéntico, sin lógica duplicada.

### **10. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`.
* **5 Idiomas, 6 Diccionarios:** en_us (predeterminado/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Archivos Versionados:** `__lang_version__` (**v0.0.32**) controla la actualización de los paquetes de idioma (`langs/*.json`) — cadena de bumps v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32 en esta ventana.
* **Cobertura:** **1158 claves** en cada uno de los 6 archivos — paridad total de key sets (+110 desde el informe anterior). **Salvedad importante 🆕:** el código usa **1198** claves, así que **40 claves `__()` no existen en ningún diccionario** (ver §33 y §34) — la paridad entre los 6 archivos es total, pero la cobertura respecto al código no lo es.
* **Corrección del Primer Arranque 🆕:** `i18n.py` ahora **crea el directorio de perfil antes** de persistir el idioma detectado, evitando un crash en la primera ejecución en una máquina limpia.
* **Snapshot del Entorno (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado **inmediatamente antes** del `load_dotenv()` a nivel de módulo — corrige el badge "⚠ en el entorno" que confirmaba tautológicamente que la clave está en el archivo.
* **Caché con Indexación por Idioma:** Las respuestas de IA en caché incluyen el idioma actual en el keying MD5.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en background durante llamadas de IA con caracteres braille y palabras de "pensamiento".
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceptan `force=`.

### **12. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Modo JSON & Parámetros Deterministas:** `temperature=0.0` y `top_p=0.1`; fallback automático entre los proveedores configurados.
* **Timeout de IA:** predeterminado de **180s** (`GITPR_AI_TIMEOUT`) — el valor de 600s se bajó deliberadamente en el fix `681a7fa` y la prueba desactualizada finalmente se alineó en esta ventana (ver §37).

### **13. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Keying por hash MD5 del diff y del prompt, con indexación por idioma.
* **Selección del Último Review (`resolve_last_review()`):** Elige el registro `review`/`fullreview` **más nuevo** para un par repo+rama, excluyendo reviews con alcance de archivo — es la puerta de entrada del `gitpr fix`.
* **Diff Revisado (`reviewed_diff`):** Campo en la cabecera del registro que guarda el diff efectivamente revisado, preferido por `fix/apply_fix.reviewed_diff()`.
* **Telemetría y Duración:** Persistencia de `duration_ms` y `meta_raw`.
* **Lectura para el Dashboard:** `scan_cache_files_for_dashboard()` lee todos los archivos de caché recursivamente.

### **14. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`) y Arqueología por Blame (`-b`).
* **Publicación Multi-Forge:** F3 crea el issue en el forge **configurado** (`GITPR_SCM_PROVIDER`) vía `provider.create_issue` — Azure DevOps lanza `ScmNotSupportedError`.
* **Map-Reduce para Issues:** El contexto por encima de ~90k tokens se divide y se unifica.
* **Manejo de 401:** Señalización de reautenticación sin cerrar la aplicación.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y la autoría histórica con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos vía `log_blame_metric()` con profundidad y número de commits analizados.

### **16. Servidor MCP e Invocación CLI Directa (`src/mcp_server.py`)**

* **14 Herramientas MCP Anotadas:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates` y `review_remote_pr` — **conteo sin cambios en esta ventana**.
* **18 Recursos + 7 Prompts Templatizados:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `prompt://list` + 7 prompts. **Salvedad 🆕:** `skill://explain` y `skill://tests` **no** existen — la prueba `TestSkillRegistryAgreement` falla precisamente porque `mcp_server.SKILL_FILES` no recibió los dos tipos nuevos que `config.SKILL_FILES_BY_TYPE` ya tiene.
* **Invocación CLI Directa:** `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca cualquier tool sin iniciar el servidor stdio; `gitpr-mcp --list` imprime el registry completo como JSON.
* **Aislamiento del Stdout Real:** `_write_real_stdout()` escribe en el `sys.__stdout__` original, garantizando JSON puro. 🆕 Endurecido contra `UnicodeEncodeError` en páginas de código legacy de Windows (fallback a `buffer` o re-codificación con `errors='replace'`).
* **Offload del Event Loop:** Decorador `_offload` (`anyio.to_thread.run_sync`) en las 14 tools — el orden de los decoradores importa (`@mcp.tool` **encima** de `@_offload`).
* **Registro de Uso:** El `main()` del servidor llama a `log_usage()` — el console script `gitpr-mcp` nunca carga `main.py`.
* **Pruebas E2E:** `tests/test_mcp_server_e2e.py` levanta el servidor real como subprocess y habla JSON-RPC stdio.

### **17. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Alcance por Repositorio:** Etiqueta `📁 Repository: owner/repo` y filtrado estricto por proyecto.
* **Escaneo Asíncrono con Overlay:** Worker thread en background con una `ProgressBar`. 🆕 Las pruebas ahora esperan `workers.wait_for_complete()` antes de asertar — sin eso, la suite era flaky por una race.
* **Consolidación de Datos:** `load_cache_token_summary()` suma tokens de caché al totalizador.
* **Exportación Local:** CSV/JSON en `./.gitpr/metrics/export/` — 🆕 más artefactos generados entraron **rastreados** (`gitpr_metrics_2026-09-18/19/21/22.*`); la deuda del `.gitignore` sigue abierta.

### **18. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Alcance por Repositorio:** Todos los eventos indexados por `repo_name`.
* **Eventos de Hook, Linter y Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Exportación y Limpieza:** `--metrics --export` (CSV/JSON) y `--metrics --purge` con confirmación interactiva.

### **19. Sincronización de Idiomas de los Hooks Git**

* **Versionado Independiente:** `__scripts_version__` (v0.0.3).
* **Mapeo de Sufijos (`HOOK_SCRIPT_SUFFIXES`):** Los códigos de interfaz (`es_es`, `fr_fr`) se traducen a los sufijos publicados (`.es`, `.fr`).
* **Elección vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detectar un cambio de idioma**.
* **`effective_hook_lang()`:** Resuelve el idioma efectivo; `--lang` dejó de descartarse en esa ruta.
* **Skip de Merge-Source:** `prepare-commit-msg` salta las fuentes `message|merge|squash|commit`.

### **20. Bridge de Linters Externos y Asistente Interactivo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Asistente `--linter-setup`:** Wizard con presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e inyección del bloque `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` con la cadena de fallback local → descarga → stale → embebido.
* **TUI de Errores del Linter:** `src/ui/linter_app.py` (Textual) muestra errores críticos y warnings; en modo hook/quiet imprime y hace `sys.exit(1)`.
* **Informe Markdown:** Consolidado en `.gitpr/reports/linter/` solo cuando hay violaciones.
* 🆕 **La Capa SAST Es Hermana, No un Reemplazo:** los puentes Semgrep/Gitleaks/Bandit viven en `src/infrastructure/linter/external/` (ver §31) y alimentan el **mismo** pipeline de informes.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstracción Única (`ScmProvider` ABC):** `base.py` define el contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. y `ScmProviderError(provider, http_status, message)` — `http_status` 0 = fallo de red); un provider concreto por forge.
* **Registry y Factory:** `resolve_scm_provider()` selecciona por `GITPR_SCM_PROVIDER` (predeterminado `github`); `detect_provider_from_remote()` identifica el forge desde la URL de origin.
* **Direccionamiento de Repositorios:** `parse_repo_ref(remote_url) -> RepoRef(raw, workspace, name, provider)`.
* **`get_pull_request(repo, pr_id)` — método concreto de la ABC:** el default lanza `ScmNotSupportedError` y cada forge lo implementa — `list_open_pull_requests` pagina **una sola página** y no distingue cerrado de inexistente.
* **`supports_reviewable_diff`:** Atributo de clase, `False` en Azure DevOps, verificado **antes de cualquier llamada de red**.
* **Encabezados de GitLab Sintetizados:** `changes[].diff` es un **hunk suelto**; `old_path`/`new_path` ahora montan los encabezados `diff --git a/… / --- / +++`, honrando `/dev/null`.
* **`overflow` de GitLab Lanza:** Un MR con diff truncado se revisaba a medias; ahora lanza `ScmProviderError`.
* **`request_pull_request_reviewers` devuelve `list[str]` (ruptura de contrato):** Devuelve los logins **efectivamente anexados**, leídos del cuerpo del `201`. Las subclases de terceros deben actualizarse.
* **Dos Helpers Read-Only de GitHub:** `get_commit_author_login` (mapea un SHA a la cuenta ligada al correo del autor) y `get_user_login` (valida/canonicaliza un handle, rechaza lo que no puede ser login sin gastar petición).
* **Fail-Fast por Forge:** Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket exige `GITPR_SCM_USERNAME`; `create_issue` en Azure lanza `ScmNotSupportedError`.
* **Publicación de Release:** `provider.create_release()` usado por `gitpr release --publish`.
* **Artefactos:** Glosario + ADR-001/ADR-005; la familia `docs/scm-multiforge.*.md` en 5 idiomas; 9 archivos de prueba, **284 escenarios**.

### **22. Subcomando `gitpr release` — Changelog / Release Notes**

* **Flujo:** `git log` entre `--since` y `HEAD` → clasificación por Conventional Commits → bump semántico sugerido (`--version <x.y.z>` lo sobrescribe) → ensamblado del changelog → resumen ejecutivo de IA opcional → *anteposición* a `CHANGELOG.md`.
* **Clasificador (`src/commit_classifier.py`)** y **Builder con Secciones Traducibles (`src/changelog_builder.py`)** — secciones renderizadas vía `__()` en runtime.
* **Bump Semántico (`src/version_bump.py`)** y **Publicación** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opciones en el subcomando.**
* **Pendiente de release 🆕:** la entrada `[1.3.0] - 2026-09-21` **existe** en `CHANGELOG.md`, pero la escribió el commit del escaneo de secretos y cubre **solo** eso — `demo`, `badge`, `split`, SAST, `tests` y `explain` **no** están en el changelog, y la entrada está fechada el 21/09 mientras `explain` es del 23/09. Ejecutar `gitpr release` es el paso que falta.

### **23. Subcomando `gitpr config` — TUI de Configuración**

* **Pantalla Master-Detail (`src/ui/config_app.py`):** Categorías a la izquierda, campos a la derecha, editados en línea. Cabecera con búsqueda (`/`) y un contador de cambios pendientes; pie `F1 Help · F2 Save · ^R Restore · / Search · Esc`.
* **Schema Declarativo (`src/config_schema.py`) — la única fuente de verdad:** 🆕 **14 categorías** (+1: **Split**) y **71 `ConfigField`** (+10), de los cuales **9 son avanzados**. Cada campo declara su categoría, el tipo de widget, `show_if`, validadores, marcadores de versión y acciones de descarga.
* **Categoría `split` 🆕:** `GITPR_SPLIT_MAX_GROUPS`, `GITPR_SPLIT_MAX_HUNKS` y `GITPR_SPLIT_REQUIRE_CONFIRMATION` con un parser de entero positivo que cae al predeterminado ante un valor no parseable — **cero o negativo no desactiva el techo en silencio**.
* **Campos de Solo Lectura 🆕:** `GITPR_BADGE`, `GITPR_EXPLAIN_BY_DEFAULT` y las tres `GITPR_SAST_*_ENABLED` aparecen en el schema (y por tanto en la pantalla) pero **no** se siembran en el `.env`.
* **Filtrado por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` según `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` bajo `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` bajo `[Azure DevOps]`. Cambiar el `Select` re-filtra el panel de inmediato.
* **Búsqueda Global (`/`):** Coincide con la clave o la etiqueta en todas las categorías e **ignora el filtro de visibilidad**.
* **Validación en Dos Capas:** **Offline** bloquea `F2` con un error en línea; **online** (solo credenciales cambiadas en la sesión) corre en un worker con timeout de 10s y solo bloquea ante `401`/`403`.
* **Restaurar (`Ctrl+R`):** Elimina la línea del `.env` en lugar de reescribir el valor predeterminado; `Esc` con cambios pendientes pide confirmación.
* **Sección Skills — la única con alcance de proyecto:** Edita los `.gitpr/skill/*.md` del proyecto resueltos desde el directorio de invocación, escribiendo atómicamente y preservando CRLF/LF. **Deuda conocida 🆕:** `SKILL_LABELS` no recibió `tests` ni `explain` — sin etiqueta, los dos tipos se renderizan **en blanco** en la barra lateral (ver §33/§34).
* **Descargas Forzadas:** Botones que fuerzan la redescarga de smart-excludes, traducciones, presets y thinking words vía `force=`.
* **Módulo Ligero de Enlaces (`src/doc_links.py`):** `doc_url()` salió de `core.py` para que la UI pueda obtener el enlace sin importar los SDKs de IA.
* **Pruebas:** 5 archivos — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Deuda conocida:** `gitpr -h config` abre la TUI e ignora `-h` — la puerta `if ctx.invoked_subcommand is not None: return` corre antes del bloque `help_flag`.

### **24. Registro General de Uso (`src/usage_log.py`)**

* **Una Línea por Comando:** Escribe en `~/.gitpr/logs/<uuid5>.log`, **un archivo por día**, con el comando, los argumentos, el repositorio, el usuario y el timestamp.
* **Nombre Derivado de la Fecha:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` en lugar de aleatorio — dos procesos concurrentes no pueden discrepar sobre cuál es el archivo de hoy.
* **Escritura Síncrona (decisión explícita):** A diferencia de `log_local_metric`, que usa un hilo daemon y pierde la escritura si el proceso termina antes.
* **Nunca Imprime:** El servidor MCP reserva la stdout para JSON-RPC; el módulo tampoco lanza excepciones nunca.
* **Un Solo Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms en vez de ~150 ms en Windows, en *cada* ejecución.
* **Control:** `GITPR_SHOW_LOGS` (predeterminado `"true"`); desactivado en `tests/conftest.py`.
* **Artefactos:** `docs/usage-log.*.md` en 5 idiomas; `tests/test_usage_log.py` (27 escenarios).

### **25. Subcomando `gitpr fix` — Hallazgos de Review como Patches Revisables (`src/fix/`)**

* **El Pipeline Real:** último review de la caché (`resolve_last_review`) → **una** llamada de IA → extracción y validación del diff unificado → `git apply --check` → clasificación determinista → dry run o escritura → historial.
* **Paquete de 8 Archivos:** `patch_provenance.py`, `patch_extractor.py` (compartido con el chat), `patch_safety_classifier.py` (puro, sin I/O), `patch_applier.py`, `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py` (solo docstring).
* **Clasificación Determinista:** Rechaza un patch que atraviese más de un archivo o hunk, que toque una ruta sensible, que reviente el presupuesto de líneas, que **borre una línea con pinta de llamada**, que venga marcado de baja confianza o que falle el `git apply --check`. El `--force` **nunca** sortea la aplicabilidad, solo la clasificación.
* **Escritura Opt-In:** El dry run es el comportamiento predeterminado; cualquier mutación exige `--apply` (o una frase escrita con `--force`).
* **`patch_applier.py` Cambió de Casa 🆕:** El envoltorio de `git apply` fue promovido a `src/infrastructure/git/patch_applier.py` (compartido con `split`) y `src/fix/patch_applier.py` pasó a ser un **shim de re-exportación** — ningún import existente se rompió.
* **Pruebas:** 10 archivos en `tests/fix/` (**204 escenarios**) con fixtures de repositorio git real.

### **26. Subcomando `gitpr review-pr` — Review de PR Remoto (`src/review/`)**

* **Qué Es:** Orquestación, **no** un segundo motor de review — el motor existente, el linter y el renderizador alimentados con un diff que vino de otro sitio. Un `.txt` de review remoto y uno local del mismo diff difieren **solo en el nombre del archivo**.
* **Paquete de 5 Archivos:** `diff_source.py` (procedencia pura), `diff_normalizer.py` (normalización, validación y smart-excludes **en Python**), `render.py` (extraído de `main.py`, ahora compartido), `remote_pr.py` (el caso de uso), `__init__.py`.
* **Read-Only por Defecto:** `--post-comment` es el **único** camino que escribe en el forge. La tool MCP `review_remote_pr` ni siquiera recibe el argumento.
* **Interacción con el `fix`:** Como un review remoto no corresponde a ningún árbol local, el diff revisado se guarda en la caché (`reviewed_diff`) y el `fix` lo prefiere.
* **Artefactos:** `docs/review-pr.md` + `.pt_br.md`, `docs/code-review-ia.*.md` (5), ADR-005 y `glossary-review-pr.md`.
* **Pruebas:** 4 archivos en `tests/review/` (**97 escenarios**).

### **27. Resolución de Identidad del Revisor (`src/reviewer_resolution.py`)**

* **El Bug (dos fallos silenciosos encadenados):** (1) **Prefill vacío** — `handles` solo desde `email_to_handle()`, que ve únicamente correos `users.noreply.github.com` o correos con dirección pública; (2) **Attach no verificado** — valores pasados *verbatim*, GitHub responde **201 sin anexar a nadie** y `_request()` retorna sin lanzar: éxito aparente, revisor ausente, aviso ninguno.
* **Módulo Nuevo:** Un plano, sin I/O propio, **que nunca lanza**. `resolve_candidates()` (antes de la TUI) y `resolve_typed_reviewers()` (en el momento del attach); `match_candidate()` casa exacto y normalizado por login, nombre o correo.
* **Escalera de Resolución:** handle ya conocido (sin petición) → casamiento exacto con una persona sugerida → lookup por correo → validación del login en el forge.
* **Lo que no resuelve nunca se envía:** Sale en `ResolutionOutcome.dropped` como `(value, i18n_reason)` y se descarta con aviso visible.
* **Provider Duck-Typed:** El acceso es por `getattr`, así que fakes y forges sin los métodos nuevos siguen funcionando.
* **Pruebas:** `tests/test_reviewer_resolution.py` (18) + ampliaciones en `test_github_provider.py` (68), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) y `test_main_suggest_reviewers.py` (8).

### **28. Subcomando `gitpr demo` — Recorrido Guiado sobre Ejemplos Grabados (`src/demo/`) 🆕**

* **Qué Es:** Un recorrido interactivo que muestra las tres salidas principales de GitPR — mensaje de commit, review y descripción de PR — **sin clave de API, sin repositorio git y sin red**. La pregunta "¿qué hace esta herramienta?" tiene una sola ventana en la que puede formularse: el primer uso, antes de que el usuario haya configurado un proveedor.
* **Pipeline Real, Respuesta Falsa:** `FakeAIProvider` refleja `src.ai_providers.call_ai_model` **argumento por argumento**; `demo_pipeline()` parchea `src.core` y `src.metrics` para que el pipeline de producción (ensamblado del prompt, chunking del diff, parsing de la respuesta) corra **intacto** y solo se intercambie la fuente de la respuesta. Es lo que garantiza que el recorrido muestre lo que la herramienta produce de verdad, y no una maqueta.
* **El Aislamiento Es un Requisito, No un Detalle:** El recorrido **no puede** tocar el `~/.gitpr` del usuario (caché, métricas, logs) ni la red — garantizado por `tests/demo/conftest.py` (prohibición de red autouse, con una excepción de loopback para el event loop proactor de Windows). Una regresión aquí envenenaría en silencio una caché real con contenido de demostración.
* **Paquete:** `demo_runner.py` (máquina de estados `DemoState` sin UI + front-end de texto plano), `fake_ai_provider.py`, `scenarios/` (dos ejemplos: un bug de actualización de perfil en Laravel y un IDOR cross-tenant en Express, cada uno localizado en los 5 idiomas con fallback al inglés). Los escenarios son **módulos Python, no JSON**, para que viajen dentro del wheel.
* **TUI:** `src/ui/demo/` (una sola pantalla, siguiendo el patrón de las apps existentes) con un modal de ayuda; el diff va cercado como ```` ```diff ```` y el mensaje de commit renderizado como texto plano.
* **CLI:** `gitpr demo` con `--scenario`, `--lang` y `--no-tui`.
* **i18n:** +32 claves y un bump a **v0.0.29**.
* **Pruebas:** 6 archivos en `tests/demo/` (**155 escenarios**) — máquina de estados, integridad de los escenarios (aritmética de hunks), navegación de la TUI, modo texto, el contrato del provider falso y los guards de aislamiento.
* **Artefactos:** `docs/demo.*.md` en 5 idiomas, `README.*` (5) y las métricas de ejemplo del recorrido.

### **29. Badge de GitPR y el Comando `gitpr badge` (`src/branding/`) 🆕**

* **Qué Es:** Un badge público que certifica que un pull request pasó por una verificación local de calidad, y un comando que imprime el snippet de adopción para el README del proyecto.
* **La Motivación Es Confianza en el Punto de Publicación:** Un cuerpo de PR escrito por IA es indistinguible de uno escrito a mano, y el resultado del linter moría en un terminal que ya se había desplazado fuera de la pantalla. El badge convierte esa señal privada en una declaración visible y verificable en el propio PR publicado.
* **URL Estática a Propósito:** Es un Markdown de shields.io que GitPR **nunca** consulta — publicar un pull request no puede pasar a depender de que un tercero esté en línea.
* **Medición Honesta (`badge_data.py`):** `collect_linter_counts()` devuelve `None` cuando no hay reglas de linter configuradas (ejecuta `gitpr --skill`), porque una lista vacía significa "nada se verificó", no "nada se encontró" — un badge verde sobre un diff sin verificar sería una afirmación que GitPR no puede sostener. El puente externo se omite: inspecciona el árbol de trabajo, no la revisión bajo revisión.
* **El Badge Nunca Bloquea una Publicación:** Cada camino de fallo en la recolección se captura y degrada a "sin badge".
* **Punto de Inyección Único:** `attach_pr_badge()` en `src/main.py`, justo después de que `pr_data` está completo — ambos publicadores leen del mismo sitio. Anexado idempotente por marcador.
* **Comando `gitpr badge`:** `--readme` (forma desnuda, amigable para pipes) y `--style`. **Solo imprime** — el README nunca se modifica.
* **Divulgación:** El wizard de init anuncia el badge automático en el camino de PR y `--no-edit` imprime una línea nombrando qué entró en el cuerpo que el usuario no vio — una funcionalidad que solo aparece después de configurar una variable es una funcionalidad que nadie descubre.
* **Configuración:** `GITPR_BADGE` (predeterminado `true`) — un opt-out de **solo lectura**, nunca escrito automáticamente.
* **i18n:** +10 claves y un bump a **v0.0.30**.
* **Pruebas:** 7 archivos en `tests/badge/` (**83 escenarios**) — builder, recolección de datos, CLI, opt-out, caminos de publicación y un guard offline (prohibición de red autouse, salvo loopback); las suites de demo y del wizard se ampliaron.
* **Demo:** El paso de PR del recorrido ahora muestra el badge que un publish real anexaría, contado desde el bloque de linter **grabado en el propio escenario** — no se lee ninguna regla ni se lintea ningún diff.
* **Artefactos:** `docs/badge.*.md` en 5 idiomas y `docs/survey/20260919_gitpr_badge_surveyfacts.md`.

### **30. Subcomando `gitpr split` — Commits Atómicos por Hunk (`src/split/`) 🆕**

* **Qué Es:** Un árbol de trabajo con varios asuntos ya no tiene que convertirse en un commit-blob. `split` lee el diff no commiteado, pide a la IA que particione los hunks por **intención lógica** y propone un commit atómico por asunto — cada uno con un mensaje generado a partir del patch reconstruido **desde ese grupo aislado**. El árbol nunca se reescribe: los archivos terminan byte a byte idénticos al inicio; solo cambia la historia.
* **Paquete de 6 Archivos, en Capas de Abajo Hacia Arriba:**
  * `split_plan.py` — contrato de datos: `SplitError`, `Hunk`, `OpaqueSection`, `ChangeUnit`, `HunkGroup`, `SplitPlan`.
  * `hunk_parser.py` — `parse_units()` / `build_patch()`: texto ↔ modelo, **puro**, nunca ejecuta git.
  * `hunk_grouper.py` — renderizado del prompt, presupuesto y recorte, **una** llamada de IA, validación de la respuesta.
  * `generate_split_plan.py` — diff → unidades → grupos → pre-validación de conflictos → mensajes. **Read-only.**
  * `apply_split_plan.py` — **el único módulo de `split` que muta**: staging selectivo y un commit por grupo.
  * `__init__.py` — marcador de paquete.
* **La Garantía de Árbol Intacto Es Estructural:** `selective_stager.py` pre-valida **cada** grupo contra un índice temporal en HEAD y usa un `GIT_INDEX_FILE` desechable — nunca se aplica un plan sin verificación, y el índice del usuario nunca se lee ni se mueve durante la planificación.
* **Destructivo para el Índice, No para el Árbol:** `--apply` resetea el índice a HEAD antes de hacer el staging, así que lo que el usuario ya tenía en stage queda fuera de stage. El contenido de los archivos se preserva, pero el staging hay que rehacerlo — documentado explícitamente.
* **Configuración:** `GITPR_SPLIT_MAX_GROUPS` (predeterminado 5), `GITPR_SPLIT_MAX_HUNKS` (predeterminado 50) y `GITPR_SPLIT_REQUIRE_CONFIRMATION` (predeterminado `true`). Los valores cero o negativos se **ignoran en favor del predeterminado**, en lugar de apagar un techo en silencio.
* **i18n:** +50 claves y un bump a **v0.0.31**.
* **Pruebas:** 6 archivos en `tests/split/` (**101 escenarios**) corriendo contra repositorios desechables reales, con la red bloqueada.
* **Artefactos:** `docs/split-command.md` + `.pt_br.md` (los 3 idiomas restantes quedan pendientes), ADR-006 (`split-apply-safety`), `glossary-gitpr-split.md`, spec/plan en `docs/plans/` y un survey.

### **31. Escaneo de Secretos Integrado (`src/security_ruleset.py`) 🆕**

* **Qué Es:** Siete reglas que corren en **cada** invocación del linter, fusionadas al final de `load_linter_rules()` después de las reglas del proyecto y de los plugins, que quedan intactas.
* **Las Siete Reglas:** cinco `error` bloqueantes — ID de clave de acceso AWS, token de GitHub, token de Slack, clave de API de Google y bloque de clave privada (`-----BEGIN … PRIVATE KEY-----`) — y dos `warning` que informan sin bloquear: una URL de conexión a base de datos con credenciales y una asignación genérica de credencial (`password = "…"`), esta última tras un filtro de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Por Qué en el Paquete y No en la Plantilla:** El catálogo local era enteramente gestionado por el usuario — podía ser sobrescrito por la descarga, reescrito por el wizard (perdiendo comentarios) o extendido solo por plugins locales de la máquina. Una puerta de secretos debe comportarse **igual** en toda máquina y en toda CI, así que las reglas viven en el paquete y no pueden ser reemplazadas por `--skill` ni reescritas por el wizard.
* **Gaps de Cobertura Conocidos (v1), declarados:** la asignación **sin comillas** no se detecta (`API_KEY=abc123` — el formato `.env`, que es exactamente donde se filtran los secretos); faltan los prefijos `ASIA…`, `github_pat_…` y `xoxc-`/`xoxd-`; y la regla genérica no tiene límite izquierdo en el nombre de la clave, así que `mytoken` casa igual que `token`.
* **Configuración:** `GITPR_LINTER_SECURITY` (predeterminado `true`; opt-out fail-open — solo `false`/`0`/`no`/`off`/`n` lo apaga) y `GITPR_LINTER_SECURITY_DISABLED_RULES` (separadas por `;`).
* **Pruebas:** `tests/test_security_ruleset.py` (**61 escenarios**) — compilación de regex, detección positiva/negativa, filtro de placeholders, enrutamiento por nivel, aplicación del comodín, fusión y completitud de traducciones.
* **Artefactos:** ADR-007 (`secret-ruleset-location-and-severity`), `glossary-gitpr-secret-scanning.md`, spec/plan/survey y 3 informes de tarea.

### **32. Puentes SAST — Semgrep, Gitleaks y Bandit (`src/infrastructure/linter/external/`) 🆕**

* **Qué Es:** Una capa **opt-in** que conecta escáneres de seguridad de terceros al pipeline de linter existente, elevando el piso de cada review sin costo para quien no lo necesita.
* **Alcance Acotado:** Corren **solo** cuando están habilitados y **solo** sobre los archivos tocados por el diff.
* **Contrato Común:** `ExternalLinterBridge` (ABC) con ejecución endurecida de `subprocess` (`shell=False`, timeout estricto, `stdin=DEVNULL`, UTF-8 con `errors='replace'`) y puentes concretos para Semgrep, Gitleaks y Bandit. El modelo `NormalizedFinding` / `ExternalLinterResult` hace que cada herramienta produzca hallazgos en la misma severidad (`error`/`warning`/`info`) y en la misma forma.
* **La Deduplicación Es el Punto:** `deduplicate_secret_findings()` fusiona hallazgos de Gitleaks y del ruleset de regex en el mismo archivo y línea en **una sola** entrada `[Gitleaks + Regex]` confirmada — un secreto visto por ambos aparece una vez, con confirmación multi-fuente, en lugar de dos.
* **Secretos Enmascarados:** `mask_secret_value()` (`AKIA****`) garantiza que el valor nunca llegue al hallazgo, al log ni a la telemetría.
* **Resolución en Tres Capas:** `load_sast_config()` lee defaults → `.gitpr.linter.yml` (bloque `sast`, con fallback a `linter.external`) → variables `GITPR_SAST_*`. **Todo es `false` por defecto** — opt-in estricto, así que nadie ve un cambio hasta que lo pide.
* **Degradación Elegante:** Una herramienta habilitada pero ausente del `PATH` emite `⚠️ SAST tool '{tool}' is enabled in config but was not found in PATH.` y la ejecución continúa.
* **Normalización de Rutas:** Todos los puentes normalizan barras invertidas→barras normales y a una forma relativa al repositorio, para que las claves de archivos modificados del diff casen en Windows **y** en Unix.
* **Configuración:** `GITPR_SAST_SEMGREP_ENABLED`, `GITPR_SAST_GITLEAKS_ENABLED`, `GITPR_SAST_BANDIT_ENABLED` + `GITPR_SAST_<TOOL>_TIMEOUT` (60s / 30s / 45s).
* **Dependencias:** `semgrep`, `gitleaks` y `bandit` **no** son paquetes Python del proyecto — son binarios externos que deben estar en el `PATH`.
* **i18n:** +7 claves (la última adición de claves de esta ventana).
* **Pruebas:** 4 archivos en `tests/infrastructure/linter/external/` (**14 escenarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) — disponibilidad, timeout de subprocess, binario ausente, parsing JSON, mapeo de severidad, enmascaramiento de secretos y skip de no-Python.

### **33. Subcomando `gitpr tests generate` — Generación de Suites por IA (`src/domain/tests_generation/`, `src/application/`) 🆕**

* **Qué Es:** Genera archivos de prueba completos y ejecutables a partir del diff actual, de un archivo específico o de un hallazgo de review, **respetando la convención del repositorio** (Pest, PHPUnit, Jest, Vitest, Pytest) en lugar de imponer un estilo.
* **Capa de Dominio:** `TestFramework` (enum) y las dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest` como contrato compartido; `detect_test_framework()` detecta desde archivos de configuración, manifiestos de dependencias y el contenido del directorio de pruebas, con un override explícito y un aviso cuando la detección falla; `build_test_scaffold()` calcula la ruta de destino convencional por framework (el split `Feature`/`Unit` de Laravel, `tests/**/test_*.py` de Pytest, las convenciones `.test`/`.spec` de JS/TS).
* **Capa de Aplicación (`generate_test_file.py`):** Orquesta la detección de framework, la resolución del scaffold, la construcción del prompt, la invocación de la IA, el parsing JSON y la escritura opcional. `validate_test_syntax()` corre el toolchain local (`php -l`, `node --check`, `python -m py_compile`) cuando está disponible — un fallo de validación es un **warning**, no un error.
* **Degradación Elegante:** Sin clave de API, o con una respuesta no-JSON del modelo, el resultado se marca de baja confianza en lugar de lanzar (quita los cercos de markdown y continúa).
* **Presentación:** El grupo `tests` con el subcomando `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); el dry run es el comportamiento predeterminado y sobrescribir una prueba existente pide confirmación con **No** preseleccionado. El chat delega `/tests` en el **mismo** caso de uso.
* **Pruebas:** `tests/domain/tests_generation/` (18), `tests/application/use_cases/test_generate_test_file.py` (4) y `tests/test_tests_command.py` (2).
* **Deuda 🆕:** No se añadió ninguna clave i18n nueva — la funcionalidad usa **17 claves `__()`** que no existen en ninguno de los 6 diccionarios (parte de las 40 ausentes), y `SKILL_LABELS`/`mcp_server.SKILL_FILES` no recibieron el tipo `tests`. No hay `docs/tests*.md`.

### **34. Subcomando `gitpr explain` y la Flag `--explain` — Reviewer Guide (`src/domain/pr/`) 🆕**

* **Qué Es:** Una guía centrada en quien va a **revisar** — qué cambia, por qué cambia, dónde enfocarse y cuál es el riesgo de regresión — para que nadie tenga que reconstruir la intención desde un diff crudo.
* **Capa de Dominio (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, el renderizador `build_explain_markdown()` y `parse_explain_payload()`, que tolera salida no-JSON o malformada y **detecta placeholders** `[FILL]`/`[TODO]` para señalar evidencia insuficiente en lugar de presentar una guía hueca como completa.
* **Capa de Aplicación (`generate_pr_explanation.py`):** Resolución del provider, validación de la clave, carga del contexto de skill (`explain`), construcción del prompt, invocación y parsing al modelo de dominio.
* **Dos Puertas de Entrada:** `gitpr explain` (subcomando, con `--provider`, detección de diff ausente antes de cualquier IA y salida coloreada) y la flag `--explain` en la CLI raíz, que anexa la guía al cuerpo de la descripción del PR **y** al payload JSON emitido, en un único `pr_desc_body` reutilizado.
* **Configuración:** `GITPR_EXPLAIN_BY_DEFAULT` (predeterminado `false`) — cuando es `true`, la sección se anexa a **toda** descripción generada, lo que añade una llamada de IA y aumenta el costo/tokens por PR.
* **Skill:** `.gitpr.explain.md` registrado en `SKILL_FILES_BY_TYPE`, con plantillas en 5 idiomas.
* **Pruebas:** `tests/domain/pr/test_explain_section_builder.py` (4), `tests/application/use_cases/test_generate_pr_explanation.py` (2) y `tests/test_explain_command.py` (2).
* **Deuda 🆕:** El mismo patrón que `tests` — claves `__()` sin traducción (parte de las 40), ausentes de `SKILL_LABELS` y de `mcp_server.SKILL_FILES`, y sin tema en `docs/`.

### **35. Suite Determinista y la Primera CI (`.github/workflows/tests.yml`) 🆕**

* **Qué Es:** El primer workflow que corre la suite, en **Python 3.10** (el piso declarado en `pyproject.toml`, nunca ejercitado) y **3.13** (la versión de desarrollo en el `Pipfile`), con `fail-fast: false`.
* **Lo que la CI Hizo Visible:** **22 pruebas** fallaban en una máquina pt-BR porque asertan el literal en inglés mientras `__()` renderiza portugués — la suite solo estaba verde con `GITPR_LANG=en_us` en la línea de comandos. Fue la motivación directa para endurecer `conftest.py`.
* **`tests/conftest.py` Hermético 🆕:** Fija `GITPR_LANG=en_us` (impide que la suite escriba en el perfil real y renderice traducciones), `GITPR_LINTER_SECURITY=false` (tres suites asertan la lista de reglas que devuelve el `load_linter_rules()` real) y `LANG_VERSION` en la versión del código (impide la redescarga de `~/.gitpr/langs/*.json` en cada bump).
* **El Orden Importa:** `src.updater` se importa **antes** de que se definan las variables de idioma, porque `i18n` snapshotea `os.environ` en `AMBIENT_ENV_KEYS` al importar — una sesión real recibe `LANG_VERSION` del archivo, no del shell.
* **Estabilidad de las Pruebas de Worker:** `test_config_app.py` y `test_metrics.py` ahora esperan `workers.wait_for_complete()` antes de asertar.
* **Dependencia del Workflow:** El runner limpio no tiene `~/.gitpr`, y `tests/demo/test_demo_isolation.py` aserta que el perfil existe (lo snapshotea para probar que el recorrido no escribe en él) — el job crea explícitamente el directorio y el `.env` vacío.

---

## **📊 Pruebas y Calidad**

| Archivo de Prueba | Escenarios | Enfoque |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por rango de líneas en un archivo |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidad, commits, duración |
| `tests/test_changelog_builder.py` | 15 | Builder de changelog: secciones, encabezados traducibles, contribuidores |
| `tests/test_chat_backend.py` | 19 | Memoria de chat, persistencia, comandos slash |
| `tests/test_commit_classifier.py` | 23 | Clasificación Conventional Commits (tipos, parser tolerante) |
| `tests/test_config_app.py` | 78 | TUI de configuración: montaje, cambio de categoría, seguimiento de cambios pendientes, F2 bloqueado, Ctrl+R, búsqueda, secretos |
| `tests/test_config_cli.py` | 9 | Registro del subcomando `config`, `-h`, import perezoso, stdout limpia |
| `tests/test_config_schema.py` | 42 | Cobertura de `DEFAULT_CONFIG`, sin duplicados, categorías/kinds, `advanced` solo en Avanzado, **sección de skills** ⚠️ |
| `tests/test_config_store.py` | 22 | Round-trip sobre un `.env` temporal, comentarios y orden preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuración de revisores sugeridos (claves y defaults) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, plantillas, `validate_ai_key()` con SDK mockeado (401 vs. red vs. ollama) |
| `tests/test_core.py` | 49 | Flujos principales, git diff, generación de PR, timing, staging, coautoría, idioma de los hooks |
| `tests/test_diff_parser.py` | 26 | Parser de diff por líneas/hunks + `summarize_patch()` y `split_patch_sections()` |
| `tests/test_explain_command.py` | 2 🆕 | CLI `explain`: éxito, clave ausente, diff vacío |
| `tests/test_external_linters.py` | 33 | Bridge Checkstyle: parser XML, subprocess, cruce de diff, informe |
| `tests/test_i18n.py` | 20 | Paridad entre idiomas, claves ausentes/huérfanas, identidad — **aserción de 40 claves ausentes** ⚠️ |
| `tests/test_install_wizard.py` | 3 | Asistente interactivo de instalación |
| `tests/test_issue_engine.py` | 4 | Borrador estructurado de issue |
| `tests/test_linter_metrics.py` | 4 | Métricas de linter: errores, warnings, duración |
| `tests/test_linter_presets.py` | 5 | Presets de linter: resolución y redescarga forzada |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` en la CLI y en la ayuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompt MCP y fallback de idioma |
| `tests/test_mcp_server.py` | 104 | Herramientas MCP (14), recursos (18), annotations, patching, CLI directo, offload — **acuerdo del registry de skills** ⚠️ |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real vía subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Recolección, exportación local, alcance de repo, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Timeouts de red/IA — **alineados al default real de 180s** ✅ |
| `tests/test_plugins.py` | 17 | Descubrimiento de plugins, merge de reglas de linter, prompts MCP |
| `tests/test_pr_publish_app.py` | 45 | TUI del PR Publisher: pantallas, flujos, revisores sugeridos, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de error del linter: abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save y payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opciones, help con epilog documentado |
| `tests/test_release_engine.py` | 27 | Motor de release: rango de commits, CHANGELOG, publicación |
| `tests/test_reviewer_resolution.py` | 18 | Escalera de resolución, rechazo de nombre parcial, dedup, tolerancia a fallos |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugerencia de revisores (ranking, exclusión, top-N, `last_commit_hash`) |
| `tests/test_security_ruleset.py` | 61 🆕 | Matriz del ruleset integrado: regex, placeholders, nivel, comodín, fusión, traducciones |
| `tests/test_skill_command.py` | 10 | Descarga y validación de plantillas de skill |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` con `quiet=True`, fallbacks, registry de skills |
| `tests/test_smart_excludes.py` | 15 | Filtro pathspec inteligente y redescarga forzada |
| `tests/test_suggest_reviewers.py` | 17 | Revisores sugeridos en el flujo de PR (integración, hint `no_login`) |
| `tests/test_tests_command.py` | 2 🆕 | CLI del grupo `tests` y de `generate` |
| `tests/test_thinking_words.py` | 5 | Carga, parsing con separador `;` y recarga forzada |
| `tests/test_updater.py` | 23 | Puerta de PyPI: parsing de versión, caché diaria, fetch, decisiones de la puerta, cableado en la CLI |
| `tests/test_usage_log.py` | 27 | Registro de uso: nombre derivado de la fecha, escritura síncrona, silencio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semántico: major/minor/patch, destinos y validación |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 2 🆕 | Caso de uso de explain: proveedor, clave, parsing |
| `tests/application/use_cases/test_generate_test_file.py` | 4 🆕 | Caso de uso de generación de pruebas: dry-run/apply, validación sintáctica |
| `tests/badge/test_badge_append.py` | 9 🆕 | Append idempotente del badge en el cuerpo del PR |
| `tests/badge/test_badge_builder.py` | 19 🆕 | Composición del Markdown de shields.io, escaping, regla de color, estilo |
| `tests/badge/test_badge_cli.py` | 15 🆕 | Comando `gitpr badge`: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 🆕 | Conteo de alertas; `None` sin reglas configuradas |
| `tests/badge/test_badge_offline.py` | 6 🆕 | Guarda offline (red prohibida, excepto loopback) |
| `tests/badge/test_badge_optout.py` | 17 🆕 | `GITPR_BADGE=false` en cada ruta de publicación |
| `tests/badge/test_badge_paths.py` | 10 🆕 | Punto único de inyección en los dos publicadores (TUI y `--no-edit`) |
| `tests/demo/test_demo_app.py` | 22 🆕 | TUI del tour: navegación, pantallas, modal de ayuda |
| `tests/demo/test_demo_isolation.py` | 14 🆕 | El tour no toca ni `~/.gitpr` ni la red |
| `tests/demo/test_demo_runner.py` | 33 🆕 | Máquina de estados de `DemoState`: avanzar, retroceder, completado, modo texto |
| `tests/demo/test_demo_scenarios.py` | 47 🆕 | Integridad de los escenarios grabados (aritmética de hunks) en los 5 idiomas |
| `tests/demo/test_demo_text_mode.py` | 18 🆕 | Salida de texto plano (`--no-tui`) para CI y grabaciones |
| `tests/demo/test_fake_ai_provider.py` | 21 🆕 | Contrato del proveedor falso: refleja `call_ai_model` argumento por argumento |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 🆕 | Formateo uniforme y deduplicación `[Gitleaks + Regex]` |
| `tests/domain/pr/test_explain_section_builder.py` | 4 🆕 | Renderizado Markdown, parsing tolerante, detección de `[FILL]` |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 🆕 | Detección por config, manifest y directorio; override; fallo advertido |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 🆕 | Ruta convencional por framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Caso de uso completo: review → IA → validar → clasificar → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extractor compartido entre el chat y `fix` (comportamiento idéntico) |
| `tests/fix/test_fix_cli.py` | 31 | Enrutamiento del subcomando, opciones, dry-run por defecto, `--force` |
| `tests/fix/test_fix_history.py` | 21 | Registro de historial `.gitpr/fix_history.json`: escritura atómica, lectura |
| `tests/fix/test_fix_settings.py` | 10 | Las cinco `GITPR_FIX_*` y los fallbacks de valor inválido |
| `tests/fix/test_patch_applier.py` | 19 | Envoltorio de `git apply`: check/apply/reverse, rama, estado |
| `tests/fix/test_patch_extractor.py` | 13 | Bloques cercados → unified diff validado |
| `tests/fix/test_patch_safety_classifier.py` | 22 | Matriz safe/review_required/experimental y códigos de motivo |
| `tests/fix/test_resolve_last_review.py` | 13 | Selección de la última review, exclusión de las reviews por archivo |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback`: reverse y las tres negativas |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 🆕 | Puente de Bandit: disponibilidad, parsing, mapeo |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 🆕 | ABC: subprocess endurecido, timeout, binario ausente |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 🆕 | Puente de Gitleaks: enmascarado de secretos, alcance por archivo |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 🆕 | Puente de Semgrep: parsing JSON, severidad, salto de no-Python |
| `tests/review/test_diff_normalizer.py` | 20 | Saltos de línea, validación del diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: procedencia, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orquestación: puertas, PR, diff, linter, comentario opcional |
| `tests/review/test_review_pr_cli.py` | 25 | CLI de `review-pr`: opciones, rechazos antes de la IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 43 | Proveedor de Azure DevOps: org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 39 | Proveedor de Bitbucket: autenticación Basic, workspace |
| `tests/scm/test_contract.py` | 38 | Contrato de `ScmProvider`: firmas, dataclasses, errores |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim obsoleto de `github_api` → delega en el proveedor |
| `tests/scm/test_github_provider.py` | 68 | Proveedor de GitHub: REST, cabeceras, PRs, issues, releases, lectura de revisores |
| `tests/scm/test_gitlab_provider.py` | 47 | Proveedor de GitLab: API v4, namespace, cabeceras sintetizadas y `overflow` |
| `tests/scm/test_init_wizard.py` | 10 | Asistente de `--init`: detección de forge, validación, persistencia |
| `tests/scm/test_release_publish.py` | 8 | Publicación de release por forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 🆕 | El único módulo que muta: staging selectivo, un commit por grupo |
| `tests/split/test_generate_split_plan.py` | 12 🆕 | Diff → unidades → grupos → prevalidación de conflictos → mensajes |
| `tests/split/test_hunk_grouper.py` | 18 🆕 | Prompt, presupuesto/recorte, llamada a la IA, validación de la respuesta |
| `tests/split/test_hunk_parser.py` | 18 🆕 | `parse_units()`/`build_patch()`: texto ↔ modelo, puro |
| `tests/split/test_selective_stager.py` | 11 🆕 | Staging de subconjuntos, verificación contra HEAD, índice limpio |
| `tests/split/test_split_cli.py` | 31 🆕 | CLI de split: opciones, dry run por defecto, `--apply` |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura de i18n (esqueleto; nunca ejecutado) |

**Total:** **1889 escenarios recolectados en 96 módulos de prueba** (44 en la raíz + 9 en `tests/scm/` + 10 en `tests/fix/` + **6 en `tests/split/`** 🆕 + **6 en `tests/demo/`** 🆕 + **7 en `tests/badge/`** 🆕 + 4 en `tests/review/` + **4 en `tests/infrastructure/linter/external/`** 🆕 + **2 en `tests/domain/tests_generation/`** 🆕 + **2 en `tests/application/use_cases/`** 🆕 + **1 en `tests/domain/pr/`** 🆕 + **1 en `tests/domain/linter/`** 🆕; **+452** desde el informe anterior, con **32 archivos nuevos**). Ejecución completa en esta máquina con `GITPR_LANG=en_us`: **1883 pasaron / 4 fallaron / 2 omitidos / 81 subtests** en ~373s.

**Notas de calidad de esta versión:**
- **✅ Se cerraron los 3 fallos heredados.** Los dos tests desactualizados de `test_net_timeouts.py` (que afirmaban 600s contra un código que entrega 180s desde el fix `681a7fa`) fueron alineados — el ítem llevaba **tres informes seguidos** abierto. Y el fallo sensible al locale se resolvió fijando `GITPR_LANG=en_us` en `conftest.py`, lo que también eliminó los **22 fallos de locale** que el CI reveló en una máquina pt-BR.
- **⚠️ 4 fallos nuevos, una única causa raíz:** las dos funcionalidades más recientes (`tests` y `explain`) llegaron con el **registry de skills a medias**. Son:
  1. `test_config_schema.py::TestSkillsSection::test_the_labels_cover_the_registry_exactly` — `SKILL_LABELS` no tiene ni `tests` ni `explain`; sin etiqueta, las dos se renderizan **en blanco** en la barra lateral de la TUI de configuración.
  2. `test_config_schema.py::TestSkillsSection::test_the_labels_follow_the_registry_order` — la misma brecha vista desde el orden: `SKILL_TYPES` tiene 10 entradas, `SKILL_LABELS` tiene 8.
  3. `test_i18n.py::TestNoMissingKeys::test_no_missing_keys` — **40 claves `__()`** usadas en el código no existen en **ninguno** de los 6 diccionarios (todas ellas de los comandos `tests` y `explain`). Como el inglés es el fallback, se renderizan como la propia clave en todos los idiomas.
  4. `test_mcp_server.py::TestSkillRegistryAgreement::test_the_two_skill_registries_agree` — `mcp_server.SKILL_FILES` y `config.SKILL_FILES_BY_TYPE` dejaron de coincidir; `skill://explain` y `skill://tests` no están expuestos por el MCP.
- **La causa es única y es barata de cerrar:** registrar los dos tipos en `SKILL_LABELS`, en `mcp_server.SKILL_FILES` y ejecutar `python tests/sync_i18n.py` para las 40 claves. Lo relevante es que la suite **lo detectó** — las tres aserciones existen exactamente para eso, y el CI las ejecuta en dos versiones de Python.
- **Crecimiento de 452 escenarios** con la línea base de fallos heredados llevada a cero — la señal de esta ventana: la suite dejó de cargar fallos conocidos y pasó a reportar la deuda nueva en el mismo commit en que nace.
- `tests/conftest.py` se volvió **hermético**: `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` y `LANG_VERSION` en la versión del código — la suite no escribe en el registro de uso, en el `.env` real ni en `~/.gitpr/langs/`.
- **Fixtures de git reales:** `tests/fix/git_fixture.py` y `tests/split/git_fixture.py` construyen repositorios reales — `patch_applier` y `selective_stager` solo son honestos si el `git apply` es el real.
- **Guarda de red:** `tests/demo/conftest.py` prohíbe la red por autouse — una regresión allí envenenaría una caché real con contenido de demostración.

---

## **🌐 Internacionalización y Documentación**

* **Cobertura de i18n:** **1158 claves de traducción** en los 6 diccionarios, con **paridad total de key sets** entre ellos (+110 desde el informe anterior). La cadena medida por commit fue 1048 → 1080 (`demo`, +32) → 1090 (`badge`, +10) → 1140 (`split`, +50) → 1151 (secrets, +11) → **1158** (SAST, +7). Los dos últimos commits (`tests`, `explain`) **no** agregaron claves — usan 40 que no existen. ⚠️ El código usa **1198** claves.
* **`__lang_version__` pasó de v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32**, disparando la redescarga OTA de las traducciones. El escaneo de secretos y el SAST agregaron claves **sin** incrementar el marcador; el bump a v0.0.32 cubre a ambos y está **en el working tree, sin commitear**.
* **Fuentes de traducción en lockstep:** una clave nueva debe existir en el código (fuente), en `langs/pt_br.json` (**lista maestra**), en los diccionarios FR/ES de `scripts/sync_all_langs.py` (segunda fuente) y en los valores curados de `scripts/fix_mangled_i18n_keys.py` (tercera fuente, leída por `tests/test_i18n.py`); la aserción `len(CLEAN_KEYS)` sigue en 49.
* **Temas nuevos 🆕 (3):**
  - `docs/demo.md` — el tour guiado: qué muestra, el pipeline real con una respuesta falsa, los escenarios y la garantía de aislamiento — **en 5 idiomas**
  - `docs/badge.md` — el badge del PR: qué afirma, dónde se adjunta, cuándo se omite y cómo imprimir el estático para el README — **en 5 idiomas**
  - `docs/split-command.md` — commits atómicos por hunk: el pipeline, la prevalidación, qué hace `--apply` con el índice — **en EN + PT-BR** (los 3 idiomas restantes están pendientes)
* **Nuevo subdirectorio 🆕:** `docs/tutorial/` con la familia `install-from-source.*` **en 5 idiomas** (instalación desde el código fuente).
* **Temas actualizados en esta ventana:** `docs/linter-regras-customizadas.*` (5 — el campo `level`, el ruleset integrado y las dos vías de escape), `docs/git-hooks-locais.*` (5 — qué bloquea ahora el hook de pre-commit y cómo sortearlo), `docs/auto-update.*` (5), `docs/ARCHITECTURE.md`, más el `README.md` y sus 4 traducciones (índice con las familias `demo`, `badge`, `split-command`, `fix-command` y `review-pr`).
* **Documentación en 5 idiomas:** **44 temas canónicos** en `docs/` (+3) — **37 con cobertura completa en los 5 idiomas** (+2) y **7 temas parciales/solo-PT** (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers`, `review-pr` con 2 idiomas y, ahora, `split-command` con 2).
* **Brecha de documentación 🆕:** `explain` y `tests` son las dos primeras funcionalidades en varias ventanas que llegan **sin un tema en `docs/`** — solo tienen las plantillas de skill.
* **Skills locales de Claude Code:** `.claude/skills/` con **29 skills** (recuento sin cambios en esta ventana).
* **Índice de memoria:** `.claude/memory/MEMORY.md` con **41 patrones** (+1 en esta ventana).
* **Informes de tarea:** `docs/claude-code/reports/develop_natan/` (**101** en total; **+8** en la ventana) y `docs/gemini/reports/develop_natan/` (**7**; **+2** — `2026-09-21_skill_gitpr_sast_bridge.md` y `2026-09-22_skill_gitpr_tests_generate.md`).
* **Informes de estado:** `docs/reports/` (15 informes; este es el 16.º).
* **Planes de desarrollo:** 115 archivos en `docs/plans/` (+16 en la ventana — specs/planes de `demo`, `badge`, `split`, secrets, SAST, `tests` y `explain`, ADR-006 y ADR-007, los glosarios `glossary-gitpr-split` y `glossary-gitpr-secret-scanning`) + **10 archivos en `docs/survey/`** (+4).

---

## **🔄 Pipeline de Distribución**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Actualización obligatoria:** la ejecución consulta PyPI al arrancar y **bloquea con código de salida 1** si hay una versión más nueva, imprimiendo `pip install --upgrade gitpr-cli`; la consulta se cachea por día y `--update` solo informa
3. **GitHub Releases:** eliminado — sin PyInstaller, sin asset `.exe`, sin hot-swap
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala vía pip) + **`tests.yml`** 🆕 (matriz Python 3.10 y 3.13, con creación explícita del perfil `~/.gitpr` que `test_demo_isolation.py` afirma que existe)
5. **Servidor MCP:** entry point `gitpr-mcp` vía `pyproject.toml`
6. **Plantillas e idiomas OTA:** `templates/` y `langs/*.json` servidos desde GitHub (main) — el bump a `v0.0.32` renueva las copias locales en `~/.gitpr/langs/` una vez publicado
7. **Versión derivada del código 🆕:** `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — `__version__` es la **única** fuente, y por eso un bump sin commitear deja el paquete construido en 1.2.0 mientras que `CHANGELOG.md` ya anuncia 1.3.0
8. **Estado del release 1.3.0 🆕:** el **tag `v1.2.0` fue creado** (merge del PR #174, 2026-09-17), cerrando el bloqueante de la ventana anterior. `__version__` (1.3.0) y `__lang_version__` (v0.0.32) están **en el working tree y sin commitear** (HEAD en 1.2.0 / v0.0.31); `CHANGELOG.md` tiene la entrada `[1.3.0]` commiteada, pero **incompleta** — cubre solo el escaneo de secretos. El camino es extender el changelog con las 6 funcionalidades restantes, ejecutar `gitpr release`, commitear el bump y etiquetar.

---

## **📈 Evolución desde el Informe Anterior (v0.0.15)**

| Área | v0.0.15 (anterior) | v0.0.16 (actual) |
|------|-------------------|-----------------|
| **Versión de GitPR** | 1.2.0 (bump **sin commitear**; HEAD en 1.1.0) | **1.3.0** (bump **sin commitear**; HEAD en 1.2.0) — **tag `v1.2.0` creado** ✅ |
| **Versión de idioma** | v0.0.28 | **v0.0.32** (vía v0.0.29, v0.0.30 y v0.0.31; bump **sin commitear** — HEAD en v0.0.31) |
| **Versión de los scripts de hook** | v0.0.3 | **v0.0.3** |
| **Proveedores de IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 idiomas, 6 diccionarios | 5 idiomas, 6 diccionarios |
| **Subcomandos** | 4 (`release`, `config`, `fix`, `review-pr`) | **9** (+ `demo`, `badge`, `split`, `tests`, `explain`) |
| **Interfaz** | CLI + TUIs + asistentes `--init`/`--install` + 4 subcomandos | **+ `gitpr demo` (TUI + modo texto) + `gitpr badge` + `gitpr split` + `gitpr tests generate` + `gitpr explain` + flag `--explain`** |
| **Capas** | `src/infrastructure/` (SCM) | **+ `src/domain/` y `src/application/use_cases/` (arquitectura por capas)** 🆕 |
| **Herramientas MCP** | 14 herramientas / 18 recursos / 7 prompts | **14 herramientas / 18 recursos / 7 prompts** (sin cambios — `skill://explain` y `skill://tests` **ausentes** ⚠️) |
| **Flags de CLI** | 35 en la raíz + `release` (6) + `config` (0) + `fix` (8) + `review-pr` (2) | **36 en la raíz** (+`--explain`) + `release` (6) + `config` (0) + `fix` (8) + `review-pr` (2) + **`demo` (3) + `badge` (2) + `split` (5) + `tests generate` (5) + `explain` (1)** |
| **Variables de entorno** | 44 claves en `DEFAULT_CONFIG` | **49 claves** (+5: 2 de seguridad + 3 de split) — **+5 read-only fuera de `DEFAULT_CONFIG`** (`GITPR_BADGE`, `GITPR_EXPLAIN_BY_DEFAULT`, 3× `GITPR_SAST_*_ENABLED`) |
| **Esquema de configuración** | 61 `ConfigField` / 13 categorías | **71 `ConfigField` (+10) / 14 categorías** (+ Split) |
| **Linter** | Regex + puente de Checkstyle | **+ ruleset de secretos integrado (7 reglas) + comodín `extensions: ["*"]` + 3 puentes SAST opt-in (Semgrep, Gitleaks, Bandit) con deduplicación multi-fuente** |
| **Git Hooks** | `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG`, `effective_hook_lang()` | Sin cambios (pero el pre-commit ahora **bloquea secretos** por defecto) |
| **Capa SCM** | 4 forges, `get_pull_request`, `supports_reviewable_diff` | Sin cambios en esta ventana |
| **i18n (claves por archivo)** | 1048 × 6 (paridad total) | **1158 × 6 (paridad total) — +110**, pero el código usa **1198** → **40 claves sin traducir** ⚠️ |
| **Documentación** | 41 temas canónicos (35 completos + 6 parciales) | **44 temas canónicos (37 completos + 7 parciales) — 3 familias nuevas + `docs/tutorial/`** |
| **CI** | Sin workflow de pruebas | **`.github/workflows/tests.yml`** (Python 3.10 + 3.13) 🆕 |
| **Suite de pruebas** | 1437 escenarios (64 archivos) | **1889 escenarios (96 archivos) — +452 escenarios, +32 archivos; en_us: 1883 pasaron / 4 fallaron / 2 omitidos** |
| **Fallos heredados** | 3 (2 de timeout + 1 de locale) | **0 — todos cerrados** ✅ (4 nuevos, una causa raíz) |
| **Commits desde el informe** | 5 commits | **8 commits** (`8920d13`, `da162d0`, `69f41ec`, `c38aed2`, `47784a7`, `a799664`, `7d84daf`, `6138cf1`) |
| **PRs fusionados** | 3 PRs (#167, #171, #173) | **7 PRs (#177, #179, #181, #183, #185, #187, #189)** |
| **Índice de memoria** | 40 patrones | **41 patrones** |
| **Informes de tarea** | 93 claude-code, 5 gemini | **101 claude-code (+8) y 7 gemini (+2)** |
| **Planes de desarrollo** | 99 planes, 6 surveys | **115 planes (+16), 10 surveys (+4)** |

---

## **🚧 Próximos Pasos**

* **Cerrar la deuda de las dos funcionalidades nuevas 🆕:** registrar `tests` y `explain` en `SKILL_LABELS` (`src/config_schema.py`) y en `mcp_server.SKILL_FILES`, y ejecutar `python tests/sync_i18n.py` para las **40 claves** faltantes. Son **4 tests en rojo** con una única causa raíz — es el ítem más barato de esta lista y el único que hoy deja la suite no verde.
* **Cerrar el release 1.3.0 🆕:** `__version__` (1.3.0) y `__lang_version__` (v0.0.32) están en el working tree sin commit. Encima de eso, la entrada `[1.3.0]` de `CHANGELOG.md` **cubre solo el escaneo de secretos** — `demo`, `badge`, `split`, SAST, `tests` y `explain` necesitan entrar en ella antes de etiquetar.
* **Documentar `explain` y `tests` 🆕:** son las dos primeras funcionalidades en varias ventanas que llegan sin un tema en `docs/` — solo existen las plantillas de skill y las specs en `docs/plans/`.
* **Traducir lo que quedó parcial:** `docs/split-command.*.md` existe solo en EN y PT-BR (faltan pt_pt, es_es, fr_fr), igual que `docs/review-pr.*.md`.
* **Ampliar la cobertura del ruleset de secretos 🆕:** las lagunas están declaradas en el propio changelog — asignación **sin comillas** (`API_KEY=abc123`, el formato `.env`, que es precisamente donde se filtran los secretos), los prefijos `ASIA…`, `github_pat_…` y `xoxc-`/`xoxd-`, y la ausencia de un límite izquierdo en el nombre de la clave (hoy `mytoken` coincide igual que `token`).
* **Documentar el cambio de contrato de `request_pull_request_reviewers`:** pasó de `None` a `list[str]`; las subclases de terceros de `ScmProvider` deben actualizarse. `docs/scm-multiforge.*.md` es el lugar, en las 5 versiones. **Ítem heredado, aún abierto.**
* **`.gitpr/metrics/export/` en `.gitignore`:** otros cuatro pares CSV/JSON entraron rastreados en esta ventana (`2026-09-18`, `19`, `21` y `22`). Son artefactos generados localmente. **La deuda creció.**
* **Proveedor de Anthropic Claude:** Soporte directo para la API de Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual en el Dashboard:** Histogramas de tiempo y tendencias de tokens en la TUI de métricas.
* **Pipeline de Release en GitHub Actions:** Automatización del build y de la subida a PyPI (el CI de pruebas ya existe; falta el de release).
* **Semilla local de `.gitpr/conf/`:** La siembra de plantillas de configuración local (smart-excludes, linter) sigue pendiente como subcomando propio o paso del asistente.
* **Más proveedores:** OpenAI directo, proveedores locales adicionales.
* **Extractor de i18n de `sync_i18n.py`:** La regex trunca literales con concatenación implícita (`__("a " "b")`) — migrar a AST.
* **Reconciliar la versión del proyecto:** `CLAUDE.md` todavía dice `Current version: 0.0.37` mientras que `__version__` está en 1.3.0. Definir una única convención. **Ítem heredado, aún abierto.**
* **Deuda del índice del README:** las familias `suggested-reviewers`, `scm-multiforge`, `config-tui` y `usage-log` siguen fuera del índice (las de esta ventana — `demo`, `badge`, `split-command` — sí entraron).
* **`gitpr -h config` ignora `-h`:** el subcomando abre la TUI en lugar de mostrar la ayuda — la puerta `if ctx.invoked_subcommand is not None: return` se ejecuta antes del bloque `help_flag`. Arreglarlo cambiaría el comportamiento de `-h` para **todos** los subcomandos, así que necesita una decisión.
* **Sección Smart Exclude en la TUI:** de los 12 ítems reportados tras usar la pantalla, el ítem 10 (*Smart Exclude*) es el único entregable aún no iniciado.
* **Deudas registradas en el plan de la TUI de config:** `DEFAULT_CONFIG` se volvió redundante con el esquema; el banner de apertura no lista `--dashboard`, `--init`, `--base` ni `--plugins`; `LinterApp` no desactiva la paleta de comandos.

### ✅ Completados en esta ventana (2026-09-17 → 2026-09-23)

* ~~**Alinear los tests de timeout desactualizados**~~ — `tests/test_net_timeouts.py` ahora afirma los 180s reales (`test_ai_timeout_defaults_to_180`) y se corrigió el docstring de `get_ai_timeout()`. **Un ítem que llevaba tres informes abierto.**
* ~~**Robustez de locale en las pruebas**~~ — `tests/conftest.py` fija `GITPR_LANG=en_us`; también desaparecieron los **22 fallos de locale** que el CI reveló en una máquina pt-BR.
* ~~**Cerrar el release 1.2.0**~~ — tag `v1.2.0` creado (merge del PR #174) y la entrada `[1.2.0] - 2026-09-17` está en `CHANGELOG.md`. **Era el ítem que bloqueaba la publicación.**
* ~~**Subcomando `gitpr demo`**~~ — paquete `src/demo/` + TUI `src/ui/demo/`, pipeline real con un proveedor falso, 2 escenarios en 5 idiomas y una guarda de aislamiento (PR #177).
* ~~**Badge de GitPR y comando `gitpr badge`**~~ — `src/branding/`, medición honesta, punto único de inyección, opt-out `GITPR_BADGE` y 7 archivos de prueba (PR #179).
* ~~**Subcomando `gitpr split`**~~ — paquete `src/split/` (6 archivos), `src/infrastructure/git/` con `selective_stager`, prevalidación contra un índice temporal (PR #181).
* ~~**Escaneo de secretos integrado**~~ — `src/security_ruleset.py` (7 reglas), el comodín `extensions: ["*"]`, dos variables de opt-out y la alerta que nunca repite el valor (PR #183).
* ~~**Puentes SAST Semgrep/Gitleaks/Bandit**~~ — `src/infrastructure/linter/external/`, deduplicación `[Gitleaks + Regex]`, enmascarado de secretos y opt-in estricto (PR #185).
* ~~**Subcomando `gitpr tests generate`**~~ — `src/domain/tests_generation/` + `src/application/use_cases/`, detección de framework, validación sintáctica y el chat delegando en el mismo caso de uso (PR #187).
* ~~**Subcomando `gitpr explain` y flag `--explain`**~~ — `src/domain/pr/explain_section_builder.py` + `generate_pr_explanation.py`, parsing tolerante con detección de `[FILL]` (PR #189).
* ~~**Primer CI para la suite**~~ — `.github/workflows/tests.yml` en Python 3.10 y 3.13, más el `conftest.py` hermético y la estabilización de los tests de workers.
* ~~**Higiene de i18n en la primera ejecución**~~ — `i18n.py` crea el directorio de perfil antes de persistir el idioma detectado.
* ~~**`patch_applier` compartido**~~ — promovido a `src/infrastructure/git/`, con `src/fix/patch_applier.py` conservado como shim de reexportación (nada se rompió).

---

**Informe generado el:** 2026-09-23  
**Rama:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
