# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.15 (2026-09-17)**

## **📌 Visión General**

**GitPR** es una herramienta CLI (Command Line Interface) avanzada para la automatización de procesos Git mediante Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). Su objetivo principal es actuar como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita la deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.15):**
- **Subcomando `gitpr fix` — el review que se convierte en patch aplicable:** Cierra el ciclo entre el review automático y la corrección. El último review de la caché alimenta **una** llamada de IA, que devuelve hallazgos en bloques cercados; el extractor valida cada bloque como diff unificado, el `git apply --check` prueba que encaja en el árbol actual y un clasificador **determinista y sin I/O** etiqueta cada candidato como `safe`, `review_required` o `experimental`. El dry run es el comportamiento predeterminado — escribir exige `--apply`. El `--force` nunca sortea la verificación de aplicabilidad, solo la clasificación, y exige una frase de confirmación escrita. Todo lo que se aplicó va a `.gitpr/fix_history.json`, que es lo que lee el `--rollback`.
- **Subcomando `gitpr review-pr <n>` — revisar el PR de terceros sin checkout:** El diff viene directamente de la API del forge y entra **en el mismo motor** que usan los flujos locales — mismo informe, mismas reglas de linter, mismo `.txt`. Read-only por defecto: nada se publica en el forge sin un `--post-comment` explícito. Amplía el público objetivo de "quien va a abrir un PR" a "quien fue invitado a revisar el PR de otra persona".
- **Resolución de identidad del revisor — el attach que no llegaba:** Las sugerencias nacen del `git blame`, así que cargan **nombres y correos electrónicos**, no logins. Cuando nada se resolvía, la UI mostraba el nombre puro — y escribir ese nombre de vuelta hacía que GitPR lo enviara *verbatim* como si fuera un login. GitHub responde **201 sin anexar a nadie**: éxito aparente, revisor ausente, aviso ninguno. Ahora una capa dedicada resuelve la identidad **dos veces** (antes de la TUI y en el attach) y cierra también el segundo fallo silencioso de la API — el login aceptado pero no anexado pasó a detectarse leyendo de vuelta `requested_reviewers`.
- **La capa SCM ganó las primitivas para revisar una revisión que no está en disco:** `get_pull_request(repo, pr_id)` pasó a ser método **concreto** de la ABC (patrón de `create_release`) y se implementó en los cuatro forges — `list_open_pull_requests` pagina una sola página, así que filtrarla por número pierde PRs antiguos y no distingue cerrado de inexistente. El atributo de clase `supports_reviewable_diff` (`False` en Azure DevOps, cuya API devuelve una lista de archivos y no un diff) bloquea la revisión **antes de cualquier llamada de red**.
- **Dos defectos latentes de GitLab corregidos:** `changes[].diff` es un hunk suelto, así que la ruta del archivo se descartaba — la IA revisaría hunks huérfanos y ni el chunker ni el filtro de exclusión funcionarían; los encabezados `diff --git / --- / +++` pasaron a sintetizarse a partir de `old_path`/`new_path`. Y `overflow: true` se ignoraba: un diff truncado se revisaba a medias y se publicaba como si fuera entero — ahora lanza.
- **`gitpr fix` pasó a corregir la revisión que fue revisada:** El diff revisado se graba en el registro de caché (`reviewed_diff`) y el `fix` lo prefiere, cayendo a la re-derivación solo para registros antiguos. Es la única fuente correcta cuando el review vino de un PR remoto o de un diff de rama completa.
- **MCP creció de 12 a 14 herramientas y de 17 a 18 recursos:** `list_fix_candidates` (13ª, de solo lectura) + `skill://fix`, y `review_remote_pr` (14ª, de solo lectura, sin argumento `post_comment`, sin escribir `.txt`).
- **i18n ampliada a 1048 claves:** +93 desde el informe anterior (955 → 1022 con el `fix` → 1028 con los revisores → 1048 con el `review-pr`); `__lang_version__` subió de v0.0.25 a **v0.0.28** (cadena v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28) y los 6 diccionarios mantienen **paridad total de key sets**.
- **Documentación:** 2 familias nuevas — `fix-command` (5 idiomas) y `review-pr` (EN + PT-BR) — y 9 temas actualizados, incluyendo `code-review-ia` (modo remoto como §1.4), `suggested-reviewers` (resolución de login) y el §2.2 de `fix-command` reescrito en las 5 versiones, porque el diff dejó de re-derivarse.
- **Higiene de repositorio:** un árbol pip 25.2 auto-vendorizado (`pypa/`, `pip/cache/http-v2/`) había sido commiteado por error y fue eliminado; el `.gitignore` ganó `pypa/` y `pip/`.
- **Salto de versión a 1.2.0:** el bump está en el **working tree y todavía no fue commiteado ni etiquetado** (HEAD sigue en 1.1.0; el último tag es `v1.1.0`), y el `CHANGELOG.md` todavía se detiene en `[1.1.0] - 2026-09-13`.

- **Versión actual:** 1.2.0 (bump en el working tree — HEAD en 1.1.0)
- **Versión de los diccionarios de idioma:** v0.0.28
- **Versión de los scripts de hook:** v0.0.3
- **Publicación:** PyPI (`pip install gitpr-cli`) — canal binario eliminado en la ventana anterior
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 diccionarios)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal).
* **UI/Terminal:** Textual — TUI para chat interactivo, edición de issues, help screen, dashboard de métricas, PR Publisher, errores del linter (`LinterApp`), configuración (`ConfigApp`) y el nuevo modal `NoticeScreen` de avisos del PR Publisher 🆕.
* **Criptografía:** `cryptography.fernet` para protección local de claves de API, tokens de GitHub y tokens SCM de los forges — los secretos editados en la TUI de configuración también se cifran antes de escribirse.
* **Configuración:** `python-dotenv`, `pyyaml` (para el linter estático) + su propio schema declarativo (`src/config_schema.py`).
* **Proveedores de IA:** Integración vía SDK oficial de Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`) y OpenAI SDK (`Ollama` local).
* **APIs de Forge:** `requests` (REST) — capa de abstracción multi-forge en `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps); módulo legacy `src/github_api.py` mantenido como shim deprecado.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK oficial Anthropic para Model Context Protocol) — **14 herramientas anotadas** 🆕, **18 recursos** 🆕, 7 prompts; handlers descargados a threads vía `anyio`.
* **Pruebas:** Pytest + `unittest.mock` (**64 módulos de prueba — 41 en la raíz, 9 en `tests/scm/`, 10 en `tests/fix/` y 4 en `tests/review/`** 🆕 —, 1437 escenarios recolectados) + pruebas e2e del servidor MCP vía subprocess real (JSON-RPC stdio) + fixtures de repositorio git real en `tests/fix/git_fixture.py` 🆕.
* **Empaquetado:** setuptools/build (PyPI).
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
* **Smart Excludes con Dos Capas:** Filtro de pathspec inteligente con capa global (`~/.gitpr/conf/`) + local del proyecto (`./.gitpr/conf/`). Fusión en runtime (unión, deduplicada). Auto-seed del archivo local en la primera ejecución. `_load_smart_excludes()` acepta `force=` para redescargar bajo demanda desde la TUI de configuración. 🆕 La plantilla `templates/gitpr.smart-excludes.json` ganó `.gitpr/fix_history.json` — un patch aplicado ensucia un archivo **rastreado**, así que sin eso aparecería en los diffs de `gitpr -c` y en las descripciones de PR.
* **Métricas con Seguimiento de Tiempo:** Inyección de `log_command_metric()` en todos los flujos con el paso de la duración en milisegundos (`duration_ms`) y lazy imports.
* **Resolución Centralizada de Salida:** Función `resolve_output_path()` que centraliza la lógica de directorios de salida — por defecto en `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`)**: `gitpr --init` — detecta el forge desde el remote origin, solicita extras por forge (org/project de Azure, username de Bitbucket), valida el token con `test_connection` (3 intentos, re-prompt en 401) y persiste `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` (Fernet) **solo en caso de éxito**.
* **Plantilla de Skill de Release (`ensure_release_skill_template()`)**: Descarga `templates/gitpr.release.*.md` en el primer uso de `gitpr release` (capa CLI, consciente del idioma, nunca sobrescribe; omitida con `--format json`).
* **Registry Compartido de Skills:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` viven en `src/config.py` (la TUI no puede importar `core` en el nivel superior — arrastra los SDKs de IA); `get_skill_context()` usa `skill_file_for()`. 🆕 El `files_to_download` necesitó una entrada propia para `gitpr.fix.md` — tocar solo el `SKILL_FILES_BY_TYPE` no bastaba para que `--skill` descargara la plantilla.
* **Motor de Review con Alcance de Caché (`generate_pr_content`) 🆕:** Dos parámetros **aditivos** — `cache_scope` (anexado **solo a la clave de caché**, nunca al prompt) y `store_diff` (graba el diff revisado en el registro). Los valores predeterminados `""`/`False` mantienen el camino local **byte-idéntico**: cero invalidación de caché para los reviews locales ya existentes. Es lo que permite que un review remoto quede escopado por `::diff-source::pr-<n>` sin que un review local del mismo diff lo responda por error.
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
* **Enrutamiento de Comandos:** Gestiona todas las flags y los **4 subcomandos** — `release`, `config`, `fix` 🆕 y `review-pr` 🆕.
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
* **Subcomando `fix` 🆕 — 8 opciones:** `--list` (lista los candidatos del último review — lo que hace el comando sin argumento), `--apply` (escribe en el árbol; sin él es dry run), `--all-safe` (selecciona todos los `safe`; escribir todavía exige `--apply`), `--create-branch <name>`, `--no-branch`, `--yes` (salta la confirmación, **nunca** sortea el `--force`) y `--force` (aplica un patch no seguro tras una frase escrita) y `--rollback <patch-id>`. Argumento opcional `[<finding-id>]`. Molde exacto del `release`: imports lazy en el cuerpo y `epilog` para `get_doc_url("fix-command.md")`. Ninguna flag existente cambió de sentido — el `--force` del `release` ("regenerar sección existente") no colisiona porque los namespaces de subcomando son separados.
* **Subcomando `review-pr` 🆕 — 2 opciones:** `review-pr <number>` con `--provider <name>` y `--post-comment`; read-only por defecto, **nunca** llama a `check_unstaged_files`, graba `{branch}_{datetime}_PR_REVIEW.txt` con el nombre de la rama de origen del PR. Rechaza el PR **antes de cualquier llamada de IA** por capacidad, existencia, estado, diff vacío/no revisable y agotamiento de los smart-excludes. Reutiliza `_resolve_scm_context`.
* **Variables de Entorno (44 claves en `DEFAULT_CONFIG`, +5 en esta ventana 🆕):** las cinco `GITPR_FIX_*` — `GITPR_FIX_SAFE_MAX_LINES_CHANGED`, `GITPR_FIX_SAFE_EXCLUDED_PATHS`, `GITPR_FIX_REQUIRE_CONFIRMATION`, `GITPR_FIX_CREATE_BRANCH_ON_ALL_SAFE` y `GITPR_FIX_BRANCH_NAME_TEMPLATE`. `get_fix_settings()` devuelve el bloque aplanado y **cae al valor predeterminado embebido** cuando el valor se vacía por accidente — vaciar el campo no puede tumbar la protección en silencio.
* **Ayuda Contextual:** `-h --flag` muestra documentación específica de la funcionalidad con enlace directo (consciente del idioma) a GitHub. Los subcomandos tienen su propio `epilog=` (párrafo Click `\b` para que la URL no se re-envuelva bajo ningún locale).
* **--lang:** Fuerza el idioma de la interfaz para la ejecución actual sin persistir el cambio.
* **--provider:** Fuerza el proveedor de IA (`gemini`, `deepseek`, `ollama`) para la ejecución actual.
* **--mcp:** Inicia el servidor MCP en el transporte stdio para integración con editores — **14 herramientas anotadas + 18 recursos + 7 prompts** 🆕.
* **--install:** Asistente guiado de 4 pasos que descarga plantillas de skill, instala Git Hooks, configura MCP en los editores y valida claves de API.
* **--metrics:** Sistema de telemetría local con alcance por repositorio: `--export`, `--purge`, `--dashboard`.
* **--status:** Lista archivos no commiteados categorizados (new/modified/deleted) — rápido, sin IA, sin red.
* **Capa de Escritura del `.env`:** `read_env_file_values()` lee **solo el archivo** vía `dotenv_values` (inmune a `os.environ`), `save_config_values()` escribe con `set_key`, `remove_config_value()` con `unset_key`. `validate_ai_key()` sondea los SDKs de Gemini/DeepSeek con timeouts cortos y distingue una credencial rechazada (`401`/`403`) de una red inalcanzable.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)**

* **Interfaz Interactiva Completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en el terminal.
* **7 Pantallas Modales:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` y **`NoticeScreen`** 🆕.
* **Avisos Bloqueantes (`NoticeScreen`) 🆕:** Un modal que exige reconocimiento (Esc o Close) antes de que el flujo de merge continúe. Es intencional: sin él el prompt de merge asumiría la pantalla y los avisos de revisor descartado pasarían desapercibidos — pero pausa flujos automatizados cuando hay avisos.
* **`_attach_reviewers` con resolución 🆕:** Resuelve lo que el usuario escribió **antes** de enviar, reporta lo que fue descartado y, en un `422` de lote con varios revisores, **repite uno a uno** — GitHub rechaza el lote entero cuando un único login es inelegible (el autor del PR, un no colaborador), lo que antes tumbaba también a los revisores válidos.
* **Revisores Sugeridos:** El flujo de publicación consulta al forge los revisores sugeridos y los ofrece en la TUI; selección controlada por `--no-suggest-reviewers`, `GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N` y `GITPR_REVIEWER_SUGGESTION_EXCLUDED`. 🆕 `_reviewer_suggestion_view()` monta `resolutions` vía `resolve_candidates`, prellena `handles` y marca a quienes no tienen cuenta para que las líneas de hint lo señalen; las `resolutions` viajan en la vista para que el attach no resuelva a las mismas personas dos veces.
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
* **`skip_external` — el bridge no puede lintar el árbol equivocado 🆕:** `parse_diff_and_lint(..., skip_external=False)` ganó el parámetro que apaga **los dos** call-sites del bridge externo. El flujo remoto pasa `True`, porque el bridge ejecuta binarios **contra archivos en disco** — el árbol local del usuario, no el PR — y esos avisos se publicarían como comentario público en el PR de otra persona.
* **Informe Consolidado:** `generate_linter_report_content()` consolida errores regex + externos en `.gitpr/reports/linter/` — generado solo cuando hay violaciones.
* `load_linter_presets()` acepta `force=` para redescargar los presets desde la TUI.

### **7. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cifrado:** Genera una clave maestra `secret.key` en la carpeta `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data` y `decrypt_data` para proteger claves de API de IA, PATs de GitHub y tokens SCM de los forges (`GITPR_SCM_TOKEN_ENCRYPTED`).
* **Validación Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — valida el token contra el forge configurado con bucle de reautenticación en 401 preservando el borrador; el token legacy de GitHub (`GITHUB_TOKEN_ENCRYPTED`) sigue funcional hasta que se ejecute `--init`.
* **Secretos en la TUI de Configuración:** Los campos `KIND_SECRET` se editan en un campo enmascarado, **nunca** muestran el valor en claro y se cifran con Fernet antes de escribirse — ninguna ruta vuelve a leer el secreto hacia la pantalla. `GITPR_SCM_TOKEN` es `read_only` y su descripción apunta a `gitpr --init` como el único camino que debería escribirlo.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI como Única Fuente:** `get_latest_remote_version()` consulta siempre `https://pypi.org/pypi/gitpr-cli/json`, devuelve una **cadena** de versión y escribe la caché diaria **sin** el campo `download_url`.
* **Puerta Obligatoria (`enforce_update_required()`):** Devuelve `True` (tras imprimir ambas versiones y el comando pip) cuando la versión publicada es más reciente; devuelve `False` cuando está al día, cuando la versión remota es **desconocida (offline — el usuario no tendría forma de actualizar)** o cuando la verificación está desactivada. Devolver un `bool` en lugar de llamar a `sys.exit` internamente mantiene la función testeable.
* **`check_and_update()`:** Solo consulta e **informa**, nunca instala.
* **Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` (cualquier valor no vacío) — no se anuncia al usuario como funcionalidad; existe para la suite de pruebas y la automatización offline.
* **Caché Diaria:** Evita verificaciones repetidas el mismo día.
* **Versionado Centralizado:** `__version__` (**1.2.0** — bump en el working tree), `__lang_version__` (**v0.0.28** — cadena v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28 en esta ventana 🆕), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.

### **9. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Construida con Textual — historial de mensajes, entrada multi-línea, barra de estado con bindings visibles.
* **Memoria por Rama (`src/chat_memory.py`):** Historial de conversación persistido por rama, permitiendo continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear` — atajos para pair programming.
* **Auto-Patching (F5), Actualización de Diff (F2), Exportación de Sesión (F6).**
* **Extractor Compartido 🆕:** El bloque de la regex estaba **duplicado** en F5 y `ctrl+s`; ambos pasaron a llamar a `patch_extractor.extract_code_blocks()`. El archivo perdió 31 líneas y ganó 3, con **comportamiento visible idéntico** (mismas teclas, mismo `GITPR_PATCH_SUGGESTION_<key>.txt`, mismo contenido) — por eso no hay nota de changelog sobre el chat. Es también la razón por la que el `__init__.py` de `src/fix/` es **solo docstring**: el chat lo importa en cada sesión, y un `__init__` que reexportara arrastraría consigo las capas de IA y de git (ver ADR-004, alternativa rechazada).

### **10. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con soporte a placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`.
* **5 Idiomas, 6 Diccionarios:** en_us (predeterminado/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Archivos Versionados:** `__lang_version__` (**v0.0.28**) controla la actualización de los paquetes de idioma (`langs/*.json`) — cadena de bumps v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28 en esta ventana.
* **Cobertura:** **1048 claves** de traducción en cada uno de los 6 archivos — **paridad total de key sets** (+93 desde el informe anterior).
* **Snapshot del Entorno (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado en `i18n.py` **inmediatamente antes** del `load_dotenv()` a nivel de módulo — corrige el badge "⚠ en el entorno" que confirmaba tautológicamente que la clave está en el archivo.
* **Clave Reescrita 🆕:** `GitHub usernames, comma separated` → `GitHub login, name or email, comma separated` — el campo dejó de prometer lo que no aceptaba.
* **Caché con Indexación por Idioma:** Las respuestas de IA en caché incluyen el idioma actual en el keying MD5.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en background durante llamadas de IA mostrando caracteres braille con palabras de "pensamiento".
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceptan `force=`.

### **12. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Modo JSON & Parámetros Deterministas:** Salidas estructuradas con `temperature=0.0` y `top_p=0.1`; fallback automático entre los proveedores configurados.

### **13. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Keying por hash MD5 del diff y el prompt, con indexación por idioma.
* **Selección del Último Review (`resolve_last_review()`) 🆕:** Junto con `REVIEW_ACTION_TYPES`, elige el registro `review`/`fullreview` **más nuevo** para un par repo+rama, **excluyendo reviews con alcance de archivo** (`-i`), que no describen la rama. Es la puerta de entrada del `gitpr fix`.
* **Diff Revisado (`reviewed_diff`) 🆕:** Campo en la cabecera del registro que guarda el diff efectivamente revisado. Preferido por `fix/apply_fix.reviewed_diff()`, con fallback a la re-derivación en registros antiguos — la única fuente correcta cuando el review vino de un PR remoto o de un diff de rama completa.
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

* **14 Herramientas MCP Anotadas 🆕:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, **`list_fix_candidates`** (13ª — candidatos del último review con patch, clasificación e id; de solo lectura) y **`review_remote_pr`** (14ª — review de PR abierto en el forge, obtenido por número; de solo lectura, **nunca** comenta y **nunca** escribe archivo, resuelve el forge por sí solo).
* **18 Recursos + 7 Prompts Templatizados 🆕:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` (**`skill://fix` es nuevo**) + `linter://config` + `prompt://list` + 7 prompts.
* **Invocación CLI Directa:** El comando `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca cualquier tool MCP directamente sin iniciar el servidor stdio JSON-RPC. `gitpr-mcp --list` imprime el registry completo como JSON.
* **Aislamiento del Stdout Real:** `_write_real_stdout()` escribe directamente en el `sys.__stdout__` original, garantizando JSON puro en stdout — la razón por la que el registro de uso **nunca** imprime.
* **Offload del Event Loop:** Decorador `_offload` (`anyio.to_thread.run_sync`) aplicado a las 14 tools — los handlers síncronos no congelan el servidor stdio.
* **Registro de Uso:** El `main()` del servidor llama a `log_usage()` — el console script `gitpr-mcp` nunca carga `main.py`, así que este es el único punto que lo alcanza.
* **Pruebas E2E:** `tests/test_mcp_server_e2e.py` levanta el servidor real como subprocess y habla JSON-RPC stdio.

### **17. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Alcance por Repositorio (Repo-Scope):** Etiqueta `📁 Repository: owner/repo` y filtrado estricto por proyecto.
* **Escaneo Asíncrono con Overlay:** Worker thread en background con widget `ProgressBar`.
* **Consolidación de Datos:** `load_cache_token_summary()` suma tokens de caché al totalizador.
* **Exportación Local:** Guardado de CSV/JSON en `./.gitpr/metrics/export/` — 🆕 los artefactos `gitpr_metrics_2026-09-17.csv`/`.json` entraron **rastreados** por el PR #171 y son candidatos a `.gitignore`.

### **18. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* **Alcance por Repositorio:** Todos los eventos indexados por `repo_name`.
* **Eventos de Hook, Linter y Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Exportación y Limpieza:** `--metrics --export` (CSV/JSON) y `--metrics --purge` con confirmación interactiva.

### **19. Sincronización de Idiomas de los Hooks Git**

* **Versionado Independiente:** `__scripts_version__` (v0.0.3) controla la versión de los scripts de hook; detección y actualización automáticas.
* **Mapeo de Sufijos (`HOOK_SCRIPT_SUFFIXES`):** Los códigos de interfaz (`es_es`, `fr_fr`) se traducen a los sufijos realmente publicados (`.es`, `.fr`).
* **Elección vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** `SCRIPTS_LANG` es la elección del usuario; `SCRIPTS_INSTALLED_LANG` es lo que está en disco. Separados, la autosincronización puede **detectar un cambio de idioma**.
* **`effective_hook_lang()`:** Resuelve el idioma efectivo de los hooks; `--lang` dejó de descartarse en esa ruta (cambio de comportamiento documentado).
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
* **`get_pull_request(repo, pr_id)` — método concreto de la ABC 🆕:** Patrón de `create_release`: el default lanza `ScmNotSupportedError` y cada uno de los cuatro forges lo implementa. Existe porque `list_open_pull_requests` pagina **una sola página** — filtrarla por número pierde PRs antiguos en silencio y **no distingue cerrado de inexistente**, y el `review-pr` necesita rechazar el PR por el motivo correcto antes de gastar IA.
* **`supports_reviewable_diff` — puerta de capacidad 🆕:** Atributo de clase, `False` en Azure DevOps, verificado **antes de cualquier llamada de red**. La API REST de Azure devuelve una **lista de archivos**, no un diff unificado — revisar sería inventar contenido.
* **Encabezados de GitLab Sintetizados 🆕:** `changes[].diff` es un **hunk suelto**; `old_path`/`new_path` se ignoraban, así que la ruta del archivo se perdía — la IA revisaría hunks huérfanos y ni el chunker ni el filtro de exclusión tendrían en qué apoyarse. Ahora el provider monta los encabezados `diff --git a/… / --- / +++`, honrando `/dev/null` para archivos añadidos/eliminados.
* **`overflow` de GitLab Lanza 🆕:** Un MR cuyo diff revienta el límite de la API se revisaba **a medias** y se publicaba como si fuera entero; ahora lanza `ScmProviderError` con mensaje claro.
* **`request_pull_request_reviewers` devuelve `list[str]` 🆕 (ruptura de contrato):** Devolvía `None`; ahora devuelve los logins **efectivamente anexados**, leídos del cuerpo del `201`. Es la única forma de detectar un login aceptado y silenciosamente ignorado. `None` sigue tratándose como "no es posible verificar", nunca como fallo — los callers antiguos no se rompen, solo pierden el read-back. Las subclases de terceros **deben** actualizarse.
* **Dos Helpers Read-Only de GitHub 🆕:** `get_commit_author_login` (mapea un SHA a la cuenta ligada al correo del autor — el camino confiable para direcciones corporativas que la API de búsqueda de usuarios no ve) y `get_user_login` (valida/canonicaliza un handle escrito, rechaza lo que no puede ser login **sin gastar petición** y distingue 404 de error transitorio).
* **Fail-Fast por Forge:** Azure DevOps exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket exige `GITPR_SCM_USERNAME` (App Password = Basic auth user+token); `create_issue` en Azure lanza `ScmNotSupportedError`.
* **Publicación de Release:** `provider.create_release()` usado por `gitpr release --publish` (GitHub crea el tag en la rama predeterminada; GitLab exige que el tag exista).
* **Artefactos:** Glosario + ADR-001/ADR-005 en `docs/plans/`; familia `docs/scm-multiforge.*.md` en 5 idiomas; pruebas: 9 archivos, **282 escenarios** (+17 en esta ventana).

### **22. Subcomando `gitpr release` — Changelog / Release Notes**

* **Flujo:** `git log` entre `--since` (predeterminado: último tag alcanzable, o el primer commit) y `HEAD` → clasificación por Conventional Commits → bump semántico sugerido (`--version <x.y.z>` lo sobrescribe) → ensamblado del changelog → resumen ejecutivo de IA opcional → *anteposición* a `CHANGELOG.md`. La generación local es el comportamiento predeterminado — nada se publica ni se toca sin solicitarlo.
* **Clasificador (`src/commit_classifier.py`):** Clasifica los commits por tipo Conventional Commits (feat/fix/refactor/docs/chore/etc.) con un parser tolerante.
* **Builder con Secciones Traducibles (`src/changelog_builder.py`):** Secciones "✨ Features", "🐛 Bug Fixes", "⚠️ Breaking Changes", "Summary", "Contributors" etc. renderizadas vía `__()` en runtime (siguen a `--lang`).
* **Bump Semántico (`src/version_bump.py`):** Sugiere la próxima versión a partir de los tipos clasificados (major para breaking, minor para feat, patch para fix) y valida los destinos `x.y.z`.
* **Publicación:** `--publish` crea el release en el forge configurado (con confirmación explícita); `--draft` lo crea como borrador (GitHub; GitLab no tiene concepto de draft); `--format markdown|json` para salida estructurada; `--force` para reescribir. **6 opciones en el subcomando.**
* **Plantilla de Skill:** En el primer uso descarga `templates/gitpr.release.*.md` (5 idiomas) vía `ensure_release_skill_template()` — nunca sobrescribe.
* **Pendiente de release 🆕:** El `CHANGELOG.md` **no** fue tocado en esta ventana — la última entrada sigue siendo `[1.1.0] - 2026-09-13`, mientras el `__version__` ya dice 1.2.0. Ejecutar `gitpr release` es el paso que falta.
* **Artefactos:** familia `docs/release-notes.*.md` (5 idiomas), spec en `docs/plans/`, ADR-002 y ADR-003, glosario de release notes.

### **23. Subcomando `gitpr config` — TUI de Configuración**

* **Pantalla Master-Detail (`src/ui/config_app.py`):** Categorías a la izquierda, campos de la categoría a la derecha, editados en línea. Cabecera con búsqueda (`/`) y un contador de cambios pendientes (`● N sin guardar`); pie con `F1 Ayuda · F2 Guardar · ^R Restaurar · / Buscar · Esc`. `General` es siempre la primera entrada del menú.
* **Schema Declarativo (`src/config_schema.py`) — la única fuente de verdad:** 🆕 **13 categorías** (General, Proveedores de IA, Pull Request, Revisión de Código, Issue, Blame, Linter, Release, SCM / Forge, Filtros de Diff, Skills, **Corrección** y Avanzado) y **61 `ConfigField`**, de los cuales **8 son avanzados** (+1 categoría y +5 campos en esta ventana). Cada campo declara su categoría, el tipo de widget (`bool`/`int`/`str`/`enum`/`template`/`path`/`secret`/`version`/`words`), `show_if`, validadores, marcadores de versión y acciones de descarga. Las etiquetas son literales `__()` para el escáner de i18n.
* **Categoría `fix` 🆕:** Las cinco `GITPR_FIX_*` ganaron superficie editable con descripciones que explican la consecuencia de cada una (presupuesto de líneas, globs sensibles, exigir confirmación, crear rama en el lote all-safe y la plantilla del nombre de la rama).
* **Filtrado por Contexto (`show_if`):** `GEMINI_*` / `DEEPSEEK_*` / `OLLAMA_*` aparecen según el `DEFAULT_AI_PROVIDER` seleccionado; `GITHUB_TOKEN_ENCRYPTED` aparece con proveedor vacío o `github`; `GITPR_SCM_USERNAME` bajo `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` bajo `[Azure DevOps]`. Cambiar el `Select` re-filtra el panel **de inmediato, sin F2**.
* **Búsqueda Global (`/`):** Coincide con la clave o la etiqueta en todas las categorías e **ignora el filtro de visibilidad** — buscar `deepseek` con Gemini seleccionado encuentra los campos, para permitir prellenarlos.
* **Validación en Dos Capas:** **Offline** (tipo, enum, plantilla con un placeholder conocido y `{datetime}` obligatorio) bloquea `F2` con un error en línea; **online** (solo para credenciales cambiadas en la sesión) corre en un worker con timeout de 10s y solo bloquea ante `401`/`403` — un fallo de red permite guardar.
* **Restaurar (`Ctrl+R`):** Elimina la línea del `.env` en lugar de reescribir el valor predeterminado; **`Esc`** con cambios pendientes pide confirmación; la categoría **Desconocidas** preserva las claves fuera del schema en modo de solo lectura.
* **Sección Skills — la única con alcance de proyecto:** Panel master-detail en línea que edita los `.gitpr/skill/*.md` del proyecto resueltos desde el directorio de invocación, escribiendo atómicamente y preservando CRLF/LF. `F2` escribe el `.env` y los archivos de skill **en la misma pasada**.
* **Descargas Forzadas:** Botones que fuerzan la redescarga de smart-excludes, traducciones, presets de linter y thinking words, vía un parámetro `force=` encadenado por los loaders.
* **Módulo Ligero de Enlaces (`src/doc_links.py`):** `doc_url()` salió de `core.py` para que la UI pueda obtener el enlace a la documentación sin importar `core`/SDKs de IA.
* **Pruebas:** 5 archivos — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9). 🆕 El `test_config_schema.py` pasó a cubrir los campos nuevos (la aserción de que `advanced` solo existe en Avanzado sigue valiendo para los 61).
* **Artefactos:** `docs/config-tui.*.md` en 5 idiomas, plan `docs/plans/20260912_config_tui.md`, glosario `glossary-config-tui.md` (8 términos) y la encuesta del grill.
* **Deuda conocida:** `gitpr -h config` abre la TUI e ignora `-h` — la puerta `if ctx.invoked_subcommand is not None: return` corre antes del bloque `help_flag`; corregirlo cambiaría el comportamiento de `-h` para **todos** los subcomandos (documentado en `docs/config-tui.md`).

### **24. Registro General de Uso (`src/usage_log.py`)**

* **Una Línea por Comando:** Escribe en `~/.gitpr/logs/<uuid5>.log`, **un archivo por día**, con el comando, los argumentos, el repositorio, el usuario y el timestamp. Responde a "¿qué ejecuté realmente, y cuándo?".
* **Nombre Derivado de la Fecha:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` en lugar de aleatorio — un nombre aleatorio exigiría un contador o un archivo de estado para saber cuál es el de hoy, y dos procesos concurrentes podrían discrepar.
* **Escritura Síncrona (decisión explícita):** A diferencia de `log_local_metric`, que usa un hilo daemon y por tanto pierde la escritura si el proceso termina antes — inaceptable para un registro que promete registrar *todos* los comandos.
* **Nunca Imprime:** El servidor MCP reserva la stdout para JSON-RPC; un `print` accidental corrompería el protocolo. El módulo tampoco lanza excepciones nunca.
* **Un Solo Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` en lugar de los tres idiomáticos — ~40 ms en vez de ~150 ms en Windows, en *cada* ejecución.
* **Su Propio `_repo_label()`:** Sin reutilizar `get_repo_name()` de `core.py` (regex hardcodeada a `github.com`, devolvería `unknown/repo` en GitLab/Bitbucket/Azure) y sin `parse_repo_ref`, que es un método de provider y exigiría construir un provider (token, `requests`) en cada comando.
* **Control:** `GITPR_SHOW_LOGS` (predeterminado `"true"`); desactivado en `tests/conftest.py`.
* **Dogfooding 🆕:** El registro de ejecución de un `pr_desc` anterior fue la evidencia que aisló el bug del revisor — la línea `Reviewers requested on PR #1068: ['Eduarda Leal']` probó que el **nombre** iba como login y que el `_request()` retornaba sin lanzar. Sin ese registro, el síntoma ("no aparece en el PR") no tendría cómo distinguirse de un fallo de red.
* **Artefactos:** `docs/usage-log.*.md` en 5 idiomas; `tests/test_usage_log.py` (27 escenarios).

### **25. Subcomando `gitpr fix` — Hallazgos de Review como Patches Revisables (`src/fix/`) 🆕**

* **Qué es:** Capacidad **nueva**, no una generalización del chat. La investigación obligatoria (paso 0 del plan) tumbó tres premisas de la spec: el chat **nunca** aplicó patch alguno (F5 y `ctrl+s` graban un `.txt` en el CWD, sin `subprocess` y sin diff válido), un finding con `id`/`severity`/`file_path` **no existía** en ningún sitio, y `config.schema.yml` nunca existió.
* **El Pipeline Real:** último review de la caché (`resolve_last_review`) → **una** llamada de IA (`call_ai_model`, nunca `generate_pr_content`, cuyo `else` es la rama de PR) → extracción y validación de diff unificado → `git apply --check` → clasificación determinista → dry run o escritura → historial.
* **Paquete de 8 archivos (1147 líneas):**
  * `patch_provenance.py` — contrato de datos puro: `PatchSafety`, `FindingRef`, `PatchCandidate` (con `patch_id` derivado), `PatchProvenance`, `ApplyFixResult`.
  * `patch_extractor.py` — bloques cercados + validación de diff unificado; **compartido con el chat**.
  * `patch_safety_classifier.py` — `safe`/`review_required`/`experimental`, lógica **pura, sin I/O y sin IA**; devuelve **código de motivo**, nunca frase hecha.
  * `patch_applier.py` — primer envoltorio de `git apply` del proyecto (`--check`, `apply`, `--reverse`, `checkout -b`, `status`).
  * `fix_history.py` — `.gitpr/fix_history.json` con escritura atómica (`.tmp` + `os.replace`).
  * `apply_fix.py` — el caso de uso (470 líneas).
  * `rollback_fix.py` — `git apply --reverse` sobre el diff guardado, con tres negativas distintas.
  * `__init__.py` — **solo docstring**, y que ordena los módulos a propósito (ver ADR-004).
* **Clasificación Determinista:** Rechaza patch que atraviese más de un archivo o más de un hunk, que toque una ruta sensible configurada, que reviente el presupuesto de líneas añadidas+eliminadas, que **borre una línea con pinta de llamada**, declarado de baja confianza por la IA, o que falle el `git apply --check` contra el árbol actual. El `--force` **nunca** sortea la verificación de aplicabilidad — solo la clasificación.
* **Escritura Opt-In:** El dry run es el comportamiento predeterminado; cualquier mutación exige `--apply` (o frase de confirmación escrita con `--force`).
* **Soporte:** `src/diff_parser.py` ganó `summarize_patch()` / `PatchSummary` (puros) — conteo de archivos, de hunks, delta de líneas y detección de llamada eliminada — usados por el clasificador.
* **MCP:** 13ª tool `list_fix_candidates` (de solo lectura) y el recurso `skill://fix`.
* **Skill:** `templates/gitpr.fix.md` + `.pt_br.md` (persona: Senior Software Engineer), descargados por `gitpr --skill`.
* **Configuración y Artefactos:** 5 variables `GITPR_FIX_*` + la categoría `fix` en la TUI; `docs/fix-command.*.md` (5 idiomas, 8 secciones); ADR-004 y `glossary-gitpr-fix.md`; spec/plan/survey en `docs/plans/` y `docs/survey/`.
* **Pruebas:** 10 archivos en `tests/fix/` (**204 escenarios**) con fixtures de repositorio git real (`tests/fix/git_fixture.py`) — matriz del clasificador, applier, historial, enrutamiento CLI, rollback, settings y el extractor compartido con el chat.

### **26. Subcomando `gitpr review-pr` — Review de PR Remoto (`src/review/`) 🆕**

* **Qué es:** Orquestación, **no** un segundo motor de review. El motor existente (`generate_pr_content`), el linter (`parse_diff_and_lint`) y el renderizador son las piezas del flujo local, alimentadas con un diff que vino de otro sitio — un `.txt` de review remoto y uno de review local del mismo diff difieren **solo en el nombre del archivo**.
* **Paquete de 5 archivos (560 líneas):**
  * `diff_source.py` — `DiffOrigin` + `DiffSource`: procedencia pura (`cache_scope`, `is_remote`), sin I/O.
  * `diff_normalizer.py` — normalización de newline, validación de diff (`is_reviewable_diff`) y el filtro de smart-excludes **en Python**, para un diff que git nunca vio.
  * `render.py` — composición y escritura del artefacto, **extraída de `main.py`** y ahora **compartida** con los flujos locales.
  * `remote_pr.py` — el caso de uso: puerta → PR → diff → normalizar → excluir → motor → linter → comentario opcional.
  * `__init__.py` — marcador de paquete.
* **`split_patch_sections` (`src/diff_parser.py`) 🆕:** Devuelve la ruta de cada archivo **al lado** del propio texto — base del filtro de exclusión remoto y de la síntesis de encabezados de GitLab.
* **Read-Only por Defecto:** `--post-comment` es el **único** camino que escribe en el forge. Lo mismo vale para la tool MCP `review_remote_pr`, que ni siquiera recibe el argumento.
* **Interacción con el `fix` 🆕:** Como el review remoto no corresponde a ningún árbol local, el diff revisado pasó a guardarse en la caché (`reviewed_diff`) y el `fix` lo prefiere — corrigiendo un defecto que solo aparecería después de esta feature.
* **Artefactos:** `docs/review-pr.md` + `.pt_br.md` (8 secciones), `docs/code-review-ia.*.md` (modo remoto como §1.4 + la salvedad del linter externo, en las 5 versiones), ADR-005 y `glossary-review-pr.md`; spec, plan y survey en `docs/plans/` y `docs/survey/`.
* **Pruebas:** 4 archivos en `tests/review/` (**97 escenarios**) — `test_remote_pr.py` (40), `test_review_pr_cli.py` (25), `test_diff_normalizer.py` (20), `test_diff_source.py` (12) — más 6 escenarios nuevos en `tests/scm/test_gitlab_provider.py` y 11 en `tests/test_mcp_server.py`.

### **27. Resolución de Identidad del Revisor (`src/reviewer_resolution.py`) 🆕**

* **El Bug (dos fallos silenciosos encadenados):** (1) **Prefill vacío** — `_reviewer_suggestion_view()` montaba `handles` solo con `provider.email_to_handle()`, que ve únicamente correos `users.noreply.github.com` o con dirección **pública**; con correo corporativo (el caso real: `eduardaleal@grafjb.com.br`) nada se resuelve, el `Input` nace vacío y el hint muestra solo el **nombre**. (2) **Attach no verificado** — `_attach_reviewers()` repasaba los valores del campo **verbatim**; GitHub responde **201 sin anexar a nadie**, el `_request()` retorna sin lanzar y la línea del log se escribe: éxito aparente, revisor ausente, aviso ninguno.
* **Módulo Nuevo (159 líneas):** Plano, sin I/O propio, **que nunca lanza**. `resolve_candidates()` (antes de la TUI) y `resolve_typed_reviewers()` (en el attach); `match_candidate()` casa **exacto y normalizado** por login, nombre o correo.
* **Escalera de Resolución:** handle ya conocido (sin petición) → casamiento exacto con una persona sugerida → lookup por correo (`email_to_handle`) → validación del login en el forge (`get_user_login`).
* **Lo que no resuelve nunca se envía:** Sale en `ResolutionOutcome.dropped` como `(valor, motivo_i18n)` y se descarta con aviso visible, en lugar de convertirse en un `201` vacío.
* **Provider Duck-Typed:** El acceso es por `getattr`, así que fakes y forges sin los métodos nuevos siguen funcionando — la suite existente no necesitó reescritura.
* **Lectura de Vuelta en GitHub:** `request_pull_request_reviewers` lee `requested_reviewers` del cuerpo del `201` y devuelve los logins **realmente** anexados; un lote rechazado con `422` (GitHub rechaza el lote entero cuando un único login es inelegible) se repite **uno a uno**, en lugar de tumbar también a los revisores válidos.
* **Utilidades Adyacentes:** `_identity_key` → `identity_key` (público), nuevo `normalize_identity()`, y `ReviewerCandidate` pasó a cargar `last_commit_hash` para que la agregación guarde el commit del toque más reciente.
* **Diagnóstico:** El registro de uso de la propia herramienta fue la evidencia de origen — ver §24.
* **Artefactos:** `docs/suggested-reviewers.*.md` re-sincronizado en los 5 idiomas, `ADR-002-reviewer-suggestion.md` y `glossary-reviewer-suggestion.md`, plan y survey del grill de 4 rondas.
* **Pruebas:** `tests/test_reviewer_resolution.py` (18, **nuevo**) + ampliaciones en `test_github_provider.py` (68), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) y `test_main_suggest_reviewers.py` (8).

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
| `tests/test_config_schema.py` | 42 | Cobertura de `DEFAULT_CONFIG`, sin duplicados, categorías/kinds, `advanced` solo en Avanzado |
| `tests/test_config_store.py` | 22 | Round-trip sobre un `.env` temporal, comentarios y orden preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuración de revisores sugeridos (claves y defaults) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, plantillas, `validate_ai_key()` con SDK mockeado (401 vs. red vs. ollama) |
| `tests/test_core.py` | 49 | Flujos principales, git diff, generación de PR, timing, staging, coautoría, idioma de los hooks |
| `tests/test_diff_parser.py` | 26 | Parser de diff por líneas/hunks + `summarize_patch()` y `split_patch_sections()` 🆕 |
| `tests/test_external_linters.py` | 33 | Bridge Checkstyle: parser XML, subprocess, cruce de diff, informe |
| `tests/test_i18n.py` | 20 | Paridad entre idiomas (1048×6), claves ausentes/huérfanas, identidad |
| `tests/test_install_wizard.py` | 3 | Asistente interactivo de instalación |
| `tests/test_issue_engine.py` | 4 | Borrador estructurado de issue |
| `tests/test_linter_metrics.py` | 4 | Métricas de linter: errores, warnings, duración |
| `tests/test_linter_presets.py` | 5 | Presets de linter: resolución y redescarga forzada |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` en la CLI y en la ayuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompt MCP y fallback de idioma |
| `tests/test_mcp_server.py` | 104 🆕 | Herramientas MCP (14), recursos (18), annotations, patching, CLI directo, offload |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real vía subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Recolección, exportación local, alcance de repo, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Timeouts de red/IA — **2 aserciones desactualizadas (600s)** |
| `tests/test_plugins.py` | 17 | Descubrimiento de plugins, merge de reglas de linter, prompts MCP |
| `tests/test_pr_publish_app.py` | 45 | TUI del PR Publisher: pantallas, flujos, revisores sugeridos, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de error del linter: abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save y payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opciones, help con epilog documentado |
| `tests/test_release_engine.py` | 27 | Motor de release: rango de commits, CHANGELOG, publicación |
| `tests/test_reviewer_resolution.py` | 18 🆕 | Escalera de resolución, rechazo de nombre parcial, dedup, tolerancia a fallos |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugerencia de revisores (ranking, exclusión, top-N, `last_commit_hash`) |
| `tests/test_skill_command.py` | 10 | Descarga y validación de plantillas de skill |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` con `quiet=True`, fallbacks, registry de skills |
| `tests/test_smart_excludes.py` | 15 | Filtro pathspec inteligente y redescarga forzada |
| `tests/test_suggest_reviewers.py` | 17 | Revisores sugeridos en el flujo de PR (integración, hint `no_login`) |
| `tests/test_thinking_words.py` | 5 | Carga, parsing con separador `;` y recarga forzada |
| `tests/test_updater.py` | 23 | Puerta de PyPI: parsing de versión, caché diaria, fetch, decisiones de la puerta, cableado en la CLI |
| `tests/test_usage_log.py` | 27 | Registro de uso: nombre derivado de la fecha, escritura síncrona, silencio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semántico: major/minor/patch, destinos y validación |
| `tests/fix/test_apply_fix.py` | 53 🆕 | Caso de uso completo: review → IA → validar → clasificar → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 🆕 | Extractor compartido entre el chat y `fix` (comportamiento idéntico) |
| `tests/fix/test_fix_cli.py` | 31 🆕 | Enrutamiento del subcomando, opciones, dry-run predeterminado, `--force` |
| `tests/fix/test_fix_history.py` | 21 🆕 | Ledger `.gitpr/fix_history.json`: escritura atómica, lectura |
| `tests/fix/test_fix_settings.py` | 10 🆕 | Las cinco `GITPR_FIX_*` y los fallbacks de valor inválido |
| `tests/fix/test_patch_applier.py` | 19 🆕 | Envoltorio de `git apply`: check/apply/reverse, branch, status |
| `tests/fix/test_patch_extractor.py` | 13 🆕 | Bloques cercados → diff unificado validado |
| `tests/fix/test_patch_safety_classifier.py` | 22 🆕 | Matriz safe/review_required/experimental y códigos de motivo |
| `tests/fix/test_resolve_last_review.py` | 13 🆕 | Selección del último review, exclusión de reviews por archivo |
| `tests/fix/test_rollback_fix.py` | 14 🆕 | `--rollback`: reverse y las tres negativas |
| `tests/review/test_diff_normalizer.py` | 20 🆕 | Newlines, validación de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 🆕 | `DiffOrigin`/`DiffSource`: procedencia, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 🆕 | Orquestación: puertas, PR, diff, linter, comentario opcional |
| `tests/review/test_review_pr_cli.py` | 25 🆕 | CLI `review-pr`: opciones, rechazos antes de la IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 43 | Provider de Azure DevOps: org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 39 | Provider de Bitbucket: Basic auth, workspace |
| `tests/scm/test_contract.py` | 38 | Contrato `ScmProvider`: firmas, dataclasses, errores |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim deprecado `github_api` → delega en el provider |
| `tests/scm/test_github_provider.py` | 68 🆕 | Provider de GitHub: REST, headers, PRs, issues, releases, read-back de revisores |
| `tests/scm/test_gitlab_provider.py` | 47 🆕 | Provider de GitLab: API v4, namespace, **encabezados sintetizados y `overflow`** |
| `tests/scm/test_init_wizard.py` | 10 | Wizard `--init`: detección de forge, validación, persistencia |
| `tests/scm/test_release_publish.py` | 8 | Publicación de release por forge (GitHub/GitLab) |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura i18n (scaffold; nunca ejecutado) |

**Total:** **1437 escenarios recolectados en 64 módulos de prueba** (41 en la raíz + 9 en `tests/scm/` + **10 en `tests/fix/`** 🆕 + **4 en `tests/review/`** 🆕; **+377** desde el informe anterior, con **15 archivos nuevos**). Ejecución completa en esta máquina con `GITPR_LANG=en_us`: **1432 passed / 3 failed / 2 skipped / 81 subtests** en ~347s.

**Notas de calidad de esta versión:**
- **Ningún fallo nuevo.** Los 3 fallos son exactamente los mismos del informe anterior — y los dos de timeout siguen heredados de antes:
- **2 fallos reales (pruebas desactualizadas, heredadas):** `test_net_timeouts.py::test_ai_timeout_defaults_to_600` y `::test_invalid_ai_timeout_falls_back_to_default` asertan el default de 600s para `GITPR_AI_TIMEOUT`, pero el código usa **180s** desde el fix `681a7fa`. Es el mismo punto que ya estaba en los Próximos Pasos de **dos** informes anteriores y **sigue abierto**.
- **1 fallo de locale (heredado, no es regresión):** `test_core.py::TestHooksLanguage::test_the_language_chosen_with_the_lang_flag_is_honoured` aserta `i18n.CURRENT_LANG == "pt_br"` — pasa con el locale pt-BR de la máquina y falla con `GITPR_LANG=en_us`. Sigue siendo la única prueba sensible al locale de la suite.
- **Crecimiento de 377 escenarios con la línea base de fallos intacta** — la señal relevante de esta ventana: las tres funcionalidades nuevas entraron sin tumbar ni enmascarar nada.
- `tests/conftest.py` mantiene `GITPR_SHOW_LOGS=false` y `GITPR_SKIP_UPDATE_CHECK=true` — la suite no escribe en el registro de uso ni queda bloqueada por la puerta de actualización.
- 🆕 **Fixtures de git real:** `tests/fix/git_fixture.py` monta repositorios git de verdad, en lugar de mockear `subprocess` — el `patch_applier` solo es honesto si el `git apply` es el de verdad.

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n:** **1048 claves** de traducción en cada uno de los 6 diccionarios (+93 desde el informe anterior) con **paridad total de key sets**. La cadena medida por commit fue 955 → 1022 (`fix`, +67) → 1028 (revisores, +6) → **1048** (`review-pr`, +20). `__lang_version__` subió v0.0.25 → v0.0.26 → v0.0.27 → **v0.0.28**, disparando la redescarga OTA de las traducciones.
* **Fuentes de traducción en lockstep:** una clave nueva debe existir en el código (fuente), en `langs/pt_br.json` (**lista maestra**), en los dicts FR/ES de `scripts/sync_all_langs.py` (segunda fuente) y en los valores curados de `scripts/fix_mangled_i18n_keys.py` (tercera fuente, leída por `tests/test_i18n.py`); la aserción `len(CLEAN_KEYS)` sigue en 49.
* **Temas Nuevos 🆕 (2):**
  - `docs/fix-command.md` — hallazgos de review como patches: pipeline, clasificación de seguridad, lectura antes de escribir, historial y rollback, skill, MCP y variables — **en 5 idiomas**
  - `docs/review-pr.md` — review de PR remoto: qué es, qué forges pueden revisarse, el informe, publicación, skill, MCP y variables — **en EN + PT-BR** (los 3 idiomas restantes quedaron pendientes)
* **Temas actualizados en esta ventana:** `docs/code-review-ia.*` (5 — modo remoto como §1.4 y la salvedad de que el linter externo no corre), `docs/suggested-reviewers.*` (5 — resolución de identidad), `docs/fix-command.*` (5 — §2.2 reescrita: el diff pasó a venir del registro), `docs/commit-message-ia.*` (5), `docs/issue-tui-help.*` (5), `docs/linter-regras-customizadas.*` (5), `docs/providers-ia.*` (5) y `docs/skill-template.*` (5), además de `README.md`/`README.pt_br.md` (2×) y `CLAUDE.md` (tabla de comandos, tools MCP 13 → 14, árbol con `src/review/`).
* **Documentación en 5 idiomas:** **41 temas canónicos** en `docs/` — **35 con cobertura completa en los 5 idiomas** (+1 desde el informe anterior) y **6 temas parciales/PT-only** (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers` y, ahora, `review-pr` con 2 idiomas).
* **Skills Locales de Claude Code:** `.claude/skills/` con **29 skills** (conteo sin cambios en esta ventana).
* **Memory Index:** `.claude/memory/MEMORY.md` con 40 patrones (conteo sin cambios en esta ventana).
* **Informes de tareas:** `docs/claude-code/reports/develop_natan/` (**93** en total; **+3** en la ventana — `gitpr fix`, resolución del login del revisor y review de PR remoto) y `docs/gemini/reports/develop_natan/` (5 archivos; ninguno nuevo).
* **Informes de estado:** `docs/reports/` (14 informes; este es el 15º).
* **Planes de desarrollo:** 99 archivos en `docs/plans/` (+10 en la ventana — las specs/planes del `fix` y del `review-pr`, la corrección de la sugerencia de revisores, ADR-004, ADR-005 y los glosarios `glossary-gitpr-fix` y `glossary-review-pr`) + **6 archivos en `docs/survey/`** (+3 en la ventana).

---

## **🔄 Pipeline de Distribución**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Actualización obligatoria:** la ejecución consulta PyPI al arrancar y **bloquea con código de salida 1** si hay una versión más reciente, imprimiendo `pip install --upgrade gitpr-cli`; la verificación se cachea por día y `--update` solo informa
3. **GitHub Releases:** eliminado — sin PyInstaller, sin asset `.exe`, sin hot-swap
4. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml` (instala vía pip)
5. **MCP Server:** Entry point `gitpr-mcp` vía `pyproject.toml`
6. **Plantillas e Idiomas OTA:** `templates/` y `langs/*.json` servidos desde GitHub (main) — el bump `v0.0.28` renueva las copias locales de `~/.gitpr/langs/` una vez publicado
7. **Estado del release 1.2.0 🆕:** el `__version__` pasó a 1.2.0 **en el working tree** y todavía no fue commiteado ni etiquetado (HEAD en 1.1.0, último tag `v1.1.0`); el `CHANGELOG.md` todavía se detiene en `[1.1.0] - 2026-09-13`. El camino es ejecutar `gitpr release` y hacer el commit del bump.

---

## **📈 Evolución desde el Informe Anterior (v0.0.14)**

| Área | v0.0.14 (anterior) | v0.0.15 (actual) |
|------|-------------------|-----------------|
| **Versión GitPR** | 1.1.0 | **1.2.0** (bump **no commiteado**; HEAD en 1.1.0, último tag `v1.1.0`, CHANGELOG todavía en `[1.1.0]`) |
| **Versión Idioma** | v0.0.25 | **v0.0.28** (vía v0.0.26 y v0.0.27) |
| **Versión Scripts Hook** | v0.0.3 | **v0.0.3** |
| **Proveedores IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Idiomas** | 5 idiomas, 6 diccionarios | 5 idiomas, 6 diccionarios |
| **Interfaz** | CLI + TUIs (Issues, Chat, Dashboard, PR Publisher, LinterApp, ConfigApp) + wizards `--init`/`--install` + `gitpr release`/`gitpr config` | **+ `gitpr fix` (patches revisables) + `gitpr review-pr` (review de PR remoto) + `NoticeScreen`** |
| **Herramientas MCP** | 12 tools / 17 recursos / 7 prompts | **14 tools / 18 recursos / 7 prompts** (+`list_fix_candidates`, +`review_remote_pr`, +`skill://fix`) |
| **Flags CLI** | 35 opciones en la raíz + `release` (6) + `config` (0) | **35 en la raíz** + `release` (6) + `config` (0) + **`fix` (8)** + **`review-pr` (2)** |
| **Variables de Entorno** | 39 claves en `DEFAULT_CONFIG` | **44 claves** (+5 `GITPR_FIX_*`) |
| **Linter** | Regex + bridge Checkstyle (wizard/TUI/informe) | Sin cambios (+ **`skip_external`**: el review remoto no publica avisos del árbol local) |
| **Git Hooks** | Corregidos: `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG`, `effective_hook_lang()` | Sin cambios |
| **Mensajes de Commit** | Con trailer `Co-Authored-By` (opt-out) | Sin cambios |
| **Capa SCM** | 4 forges, `create_release`, `create_issue` | **+ `get_pull_request` (concreto en la ABC) + `supports_reviewable_diff` + read-back de revisores (`list[str]`, ruptura de contrato) + correcciones de GitLab** |
| **i18n (claves por archivo)** | 955 × 6 (paridad total) | **1048 × 6 (paridad total) — +93 claves** |
| **Documentación** | 39 temas canónicos (34 completos + 5 parciales) | **41 temas canónicos (35 completos + 6 parciales) — 2 familias nuevas, 8 actualizadas** |
| **Distribución** | PyPI exclusivo | Sin cambios (release 1.2.0 pendiente) |
| **Suite de Pruebas** | 1060 escenarios (49 archivos) | **1437 escenarios (64 archivos: 41 + 9 SCM + 10 fix + 4 review) — en_us: 1432 passed / 3 failed (2 desactualizadas + 1 de locale) / 2 skipped** |
| **Commits desde el informe** | 2 commits | **5 commits** (`f4d5186`, `f5bed07`, `b9dd930`, `eb55400`, `a8a7770`) |
| **PRs mergeados** | 2 PRs (#162, #164) | **3 PRs (#167, #171, #173)** |
| **Memory Index** | 40 patrones | **40 patrones** |
| **Informes de tareas** | 90 claude-code, 5 gemini | **93 claude-code (+3 en la ventana) y 5 gemini** |
| **Planes de desarrollo** | 89 planes, 3 surveys | **99 planes (+10), 6 surveys (+3)** |
| **Higiene de repositorio** | — | **+ `pypa/`/`pip/` eliminados del rastreo, `.gitignore` actualizado** 🆕 |

---

## **🚧 Próximos Pasos**

* **Cerrar el release 1.2.0 🆕:** el `__version__` está en 1.2.0 en el working tree sin commit, sin tag y **sin entrada en el `CHANGELOG.md`** (que se detiene en `[1.1.0]`). Ejecutar `gitpr release`, commitear el bump y etiquetar — es el único punto de esta lista que bloquea la publicación.
* **Traducir lo que quedó parcial 🆕:** `docs/review-pr.*.md` existe solo en EN y PT-BR (faltan pt_pt, es_es, fr_fr) y `templates/gitpr.fix.md` solo en EN y PT-BR — es la primera vez en varias ventanas que un tema nuevo **no** nace completo en los 5 idiomas.
* **Documentar la ruptura de contrato de `request_pull_request_reviewers` 🆕:** pasó de `None` a `list[str]`; las subclases de terceros de `ScmProvider` deben actualizarse. `docs/scm-multiforge.*.md` es el lugar, en las 5 versiones.
* **`.gitpr/metrics/export/` en el `.gitignore` 🆕:** el PR #171 commiteó `gitpr_metrics_2026-09-17.csv`/`.json` — son artefactos generados localmente, como los de agosto que ya están rastreados.
* **Proveedor Anthropic Claude:** Soporte directo a la API de Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual en el Dashboard:** Añadir histogramas de tiempo y gráficos de tendencia de tokens en la TUI de métricas.
* **Pipeline de Release en GitHub Actions:** Automatización completa del build y de la subida a PyPI (la generación del changelog ahora es local vía `gitpr release`).
* **Seed Local de `.gitpr/conf/`:** El sembrado de plantillas de configuración local (smart-excludes, linter) sigue pendiente como subcomando propio o paso del wizard; la TUI de configuración ofrece las **descargas** de esos archivos, pero no el seed del proyecto.
* **Más proveedores:** OpenAI directo, proveedores locales adicionales.
* **Extractor i18n en `sync_i18n.py`:** El regex trunca literales con concatenación implícita (`__("a " "b")`) — migrar a AST (el guard de `test_i18n.py` ya usa AST y no depende del script).
* **Corregir las Pruebas de Timeout Desactualizadas:** `tests/test_net_timeouts.py` aserta un default de 600s, pero el código usa 180s desde el fix `681a7fa`; alinear también el docstring obsoleto de `config.py`. **Punto heredado, sigue abierto — ya son tres informes.**
* **Reconciliar la Versión del Proyecto:** `CLAUDE.md` todavía dice `Current version: 0.0.37` mientras el `__version__` está en 1.2.0 — la distancia solo aumentó (+0.0.37 vs. 1.1.0 en el informe anterior). Definir una convención única y actualizar el `CLAUDE.md`. **Punto heredado, sigue abierto.**
* **Deuda del Índice del README:** los bullets de las familias `suggested-reviewers`, `scm-multiforge`, `config-tui`, `usage-log` **y ahora `fix-command` y `review-pr`** no están en el índice — la deuda creció en esta ventana.
* **Robustez de Locale en las Pruebas:** 1 prueba es sensible al locale pt_br de la máquina (`test_core.py::TestHooksLanguage`) — fijar `GITPR_LANG=en_us` en el setup o mockear `TRANSLATIONS` para que la suite esté 100% verde en cualquier máquina/CI.
* **`gitpr -h config` ignora `-h`:** el subcomando abre la TUI en lugar de mostrar la ayuda — la puerta `if ctx.invoked_subcommand is not None: return` corre antes del bloque `help_flag`. Corregirlo cambiaría el comportamiento de `-h` para **todos** los subcomandos, así que requiere una decisión.
* **Sección Smart Exclude en la TUI:** de los 12 puntos reportados tras usar la pantalla, el punto 10 (la sección *Smart Exclude*) es el único entregable aún no iniciado.
* **Deudas registradas en el plan de la TUI de configuración:** `DEFAULT_CONFIG` quedó redundante con el schema; el banner de apertura no lista `--dashboard`, `--init`, `--base` ni `--plugins`; `LinterApp` no desactiva la command palette.

### ✅ Completados en esta ventana (2026-09-13 → 2026-09-17)

* ~~**Subcomando `gitpr fix`**~~ — paquete `src/fix/` (8 archivos, 1147 líneas), clasificador determinista `safe`/`review_required`/`experimental`, dry run por defecto, `.gitpr/fix_history.json` y `--rollback` (PR #167).
* ~~**`resolve_last_review()` + `reviewed_diff` en la caché**~~ — el review que alimenta el `fix` pasó a elegirse por repo+rama, excluyendo reviews con alcance de archivo.
* ~~**Resolución de identidad del revisor**~~ — `src/reviewer_resolution.py`, `request_pull_request_reviewers` devolviendo `list[str]`, read-back del `201`, retry individual en el `422` y `NoticeScreen` (PR #171).
* ~~**Subcomando `gitpr review-pr`**~~ — paquete `src/review/` (5 archivos, 560 líneas), read-only por defecto, `.txt` nombrado por la rama de origen, `--post-comment` como único camino de escritura (PR #173).
* ~~**`ScmProvider.get_pull_request` + `supports_reviewable_diff`**~~ — método concreto en la ABC implementado en los 4 forges; Azure barrado antes de cualquier llamada de red.
* ~~**Correcciones latentes de GitLab**~~ — encabezados `diff --git` sintetizados de `old_path`/`new_path` y `overflow: true` pasando a lanzar.
* ~~**`skip_external` en el linter**~~ — el review remoto dejó de publicar avisos del bridge externo, que corre contra el árbol local.
* ~~**MCP: 12 → 14 tools y 17 → 18 recursos**~~ — `list_fix_candidates` + `skill://fix` y `review_remote_pr`.
* ~~**i18n: +93 claves y cadena v0.0.25 → v0.0.28**~~ — paridad total de key sets en los 6 diccionarios.
* ~~**Higiene de repositorio**~~ — árbol pip 25.2 vendorizado eliminado del rastreo; `.gitignore` con `pypa/` y `pip/` (commit `f5bed07`).

---

**Informe generado el:** 2026-09-17  
**Rama:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
