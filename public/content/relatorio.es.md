# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.17 (2026-10-12)**

## **📌 Visión General**

**GitPR** es una herramienta CLI (Command Line Interface) avanzada para la automatización de procesos Git mediante Inteligencia Artificial (Google Gemini / DeepSeek / Ollama). Su objetivo principal es actuar como un asistente inteligente local que realiza Code Reviews, genera Pull Requests, mensajes de commit semánticos, audita la deuda técnica e inyecta buenas prácticas en el flujo de trabajo del desarrollador (Shift Left).

**Novedades de esta versión (v0.0.17):**
- **El release 1.3.0 quedó cerrado y el 1.4.0 ya está etiquetado:** el tag `v1.3.0` (merge del PR #193, 2026-09-24) cerró el ítem que llevaba dos ventanas bloqueando, y el `CHANGELOG.md` se completó con las seis funcionalidades que faltaban. Diez días después, el tag `v1.4.0` (merge del PR #207, 2026-10-12) cerró esta ventana entera — las cinco funcionalidades de abajo **más** la nota de release de 1.3.0 entraron en un solo ciclo.
- **`gitpr release` dejó de repetir la historia:** las release notes listaban, para cada versión, todos los commits de todas las versiones anteriores (la sección de 1.3.0 salió con 124 viñetas, 115 de ellas repetición de 1.2.0). La causa no era la IA — era el rango de `git log`, anclado en `git describe`, que en este repositorio resolvía a `v0.0.8`. El corte pasó a ser la **sección anterior del propio changelog**, cada viñeta ganó un enlace **real y clicable** al commit, al PR y al contribuidor, y un commit ya presente en otra sección se descarta antes de convertirse en viñeta — cuando no queda nada, el release **aborta** en lugar de publicar una sección vacía.
- **La telemetría salió del archivo JSON y pasó a ser un ledger SQLite:** los eventos se escribían en archivos por comando, desde un hilo daemon, y después se "enriquecían" de vuelta a través de la caché de IA mediante un match heurístico por (repo, rama, acción, minuto) que dependía de tres mapas de carpetas divergentes. El resultado era predecible: `tokens_actual` **siempre en cero** y pérdida de datos entre repositorios al exportar. `~/.gitpr/metrics/telemetry.db` escribe la fila **de forma síncrona**, con los tokens copiados de la metadata del proveedor en el momento de la escritura, y un proceso de vida corta (un hook, por ejemplo) ya no pierde su propia fila al salir.
- **Mentor Mode — el review que enseña, no solo señala:** un review dice *qué* está mal y casi nunca *por qué importa*. `gitpr mentor` (y la flag `--mentor` en `-r`/`-f`) enriquece los hallazgos del último review con qué está pasando, por qué importa, una analogía cotidiana y los conceptos que estudiar. Es **opt-in** y nunca inventa: con evidencia insuficiente, la explicación lo dice en lugar de rellenar el vacío.
- **Puntuación de riesgo determinista — sin IA, sin cuota:** `gitpr risk` produce una puntuación de 0–100 con un nivel `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` a partir de señales locales (rutas críticas, pruebas ausentes, migraciones, historial de bugs y reverts, hallazgos estáticos). La fórmula está declarada (**50/30/20**), igual que las reglas de escalado obligatorias, y la puntuación entra en el prompt de review como contexto **de solo lectura** y en el informe. Un número explicable vale más que una suposición cara.
- **Policy Packs — la política del repositorio pasó a ser un artefacto versionado:** los skills, las reglas de linter, las severidades, las rutas críticas, los pesos de riesgo y las convenciones de PR/commit estaban dispersos y cada repositorio los configuraba a mano. `gitpr policy` adopta un pack YAML versionado con un **checksum**, resuelve la precedencia entre capas y registra la elección en el lockfile que el equipo commitea. Cuatro packs integrados viajan en el wheel; sin un pack activo, **nada cambia**.
- **Baseline y supresiones auditables — el ruido conocido deja de bloquear:** los linters y los reviews volvían a reportar los mismos hallazgos preexistentes en cada ejecución, y distinguirlos de cabeza era tarea de quien revisaba. `.gitpr/baseline.json` registra el snapshot de los hallazgos aceptados, cada hallazgo nuevo se clasifica como `new`/`existing`/`resolved`/`ignored`/`accepted_debt`, y **solo `new` bloquea**. Cada supresión lleva un motivo; la deuda aceptada lleva un responsable y, cuando existe, una fecha límite.
- **El modelo de hallazgo pasó a ser un tipo canónico:** `NormalizedFinding` se movió de `infrastructure/linter/external/base_bridge.py` a `src/domain/finding/`. Sin eso, cada puente — Semgrep, Gitleaks, Bandit, el ruleset de regex — calcularía la huella de una forma distinta del mismo hallazgo y el baseline no tendría forma de reconocerlo.
- **La deuda de la ventana anterior quedó cerrada:** `SKILL_LABELS` y `mcp_server.SKILL_FILES` recibieron `tests`, `explain` y ahora `mentor`; las **40 claves `__()`** que no existían en ningún diccionario se tradujeron; y los **4 fallos** de la ventana anterior ya no existen. Las tres aserciones que reportaban la deuda siguen corriendo — simplemente dejaron de reportarla. La suite, medida de punta a punta en esta ventana, cierra en **2795 aprobadas de 2799** — los dos fallos restantes son un bug de aislamiento en `tests/test_mentor_cli.py`, no una regresión del producto (ver §Tests).
- **La cobertura de i18n cerró el ciclo:** **1603 claves** en cada uno de los 6 diccionarios (+445 en esta ventana) y **cero claves usadas en el código sin traducción** — el código usa 1603, los diccionarios tienen 1603.
- **Las traducciones pendientes se saldaron:** `split-command` y `otimizacao-de-tokens` completaron los 5 idiomas; `review-pr` sigue con 2. Dos familias solo-PT (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) se movieron de la raíz de `docs/` a `docs/extra/`.

- **Versión actual:** 1.4.0 (**etiquetada** — `v1.4.0`, merge del PR #207, 2026-10-12; `v1.3.0` el 2026-09-24)
- **Versión de los diccionarios de idioma:** v0.0.34 (cadena v0.0.32 → v0.0.33 → v0.0.34 en esta ventana)
- **Versión de los scripts de hook:** v0.0.4 (v0.0.3 → v0.0.4 en esta ventana)
- **Publicación:** PyPI (`pip install gitpr-cli`) — canal binario eliminado
- **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repositorio:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licencia:** LGPL-2.1
- **Idiomas soportados:** en_us, pt_br, pt_pt, es_es, fr_fr (5 idiomas, 6 diccionarios)

---

## **🏗️ Arquitectura y Bibliotecas Base**

* **Lenguaje:** Python >= 3.10
* **CLI Framework:** Click (para comandos, flags y formato de terminal) — **14 subcomandos** 🆕 (`badge`, **`baseline`** 🆕, `config`, `demo`, `explain`, `fix`, **`mentor`** 🆕, **`metrics`** 🆕, **`policy`** 🆕, `release`, `review-pr`, **`risk`** 🆕, `split`, `tests`). Cuatro de ellos son **grupos**: `baseline` (6 subcomandos), `metrics` (8), `policy` (7) y `tests` (1).
* **UI/Terminal:** Textual — TUI para chat interactivo, edición de issues, help screen, dashboard de métricas, PR Publisher, errores del linter (`LinterApp`), configuración (`ConfigApp`), el modal `NoticeScreen`, el **recorrido `gitpr demo`** (`src/ui/demo/`) y el **asistente de migración del ledger** (`src/ui/metrics_migration_app.py`) 🆕.
* **Arquitectura en Capas:** `src/domain/` (reglas puras, sin I/O) + `src/application/use_cases/` (orquestación, **14 casos de uso** 🆕) + presentación (CLI/TUI). **8 paquetes de dominio** 🆕: `baseline`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`.
* **Criptografía:** `cryptography.fernet` para protección local de claves de API, tokens de GitHub y tokens SCM de los forges.
* **Configuración:** `python-dotenv`, `pyyaml` (linter estático) + su propio schema declarativo (`src/config_schema.py`, **80 `ConfigField`** y **16 categorías** 🆕).
* **Proveedores de IA:** Integración vía SDK oficial de Google GenAI, OpenAI SDK (DeepSeek) y OpenAI SDK (Ollama local). Predeterminados configurados: `gemini-pro-latest` / `gemini-flash-lite-latest` y `deepseek-v4-pro` / `deepseek-v4-flash`.
* **APIs de Forge:** `requests` (REST) — capa de abstracción multi-forge en `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + **`web_links.py`** 🆕 (URLs de commit, PR y perfil por forge, funciones puras); `src/github_api.py` mantenido como shim deprecado.
* **Linter / SAST:** YAML regex + bridge Checkstyle + ruleset de secretos integrado (`src/security_ruleset.py`) + 4 puentes en `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), con el modelo normalizado en `src/domain/linter/sast_finding_mapper.py`. 🆕 El hallazgo normalizado ahora vive en **`src/domain/finding/`** y existe un **canal lateral `findings_out`** que expone los hallazgos estructurados junto a las cadenas de alerta.
* **Internals de Git:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, **`identity.py`** 🆕 (etiqueta de repo, rama y autor en una sola llamada a git), **`risk_history_reader.py`** 🆕 y **`test_matcher.py`** 🆕.
* **Persistencia local 🆕:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) con WAL, `PRAGMA user_version`, claves UUID y transporte por bundle.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **16 herramientas anotadas** 🆕, **19 recursos** 🆕, 7 prompts.
* **Pruebas:** Pytest + `unittest.mock` (**129 módulos de prueba** 🆕 — 49 en la raíz, 11 en `tests/application/use_cases/`, 10 en `tests/fix/`, 9 en `tests/scm/`, 7 en `tests/badge/`, 6 en `tests/demo/`, 6 en `tests/split/`, 5 en `tests/domain/baseline/`, 4 en `tests/domain/policy/`, 4 en `tests/review/`, 4 en `tests/infrastructure/linter/external/`, 2 en `tests/domain/risk/`, 2 en `tests/domain/tests_generation/`, 2 en `tests/integration/`, 2 en `tests/infrastructure/git/`, 1 en `tests/domain/finding/`, 1 en `tests/domain/mentor/`, 1 en `tests/domain/pr/`, 2 en `tests/domain/linter/`, 1 en `tests/infrastructure/baseline/` —, **2799 escenarios recolectados**) + pruebas e2e del servidor MCP vía subprocess real (JSON-RPC stdio) + fixtures de repositorio git real (`tests/fix/`, `tests/split/`) + **guard de aislamiento de red** (`tests/demo/conftest.py`, `tests/integration/conftest.py`).
* **Empaquetado:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; **packs embebidos vía `package-data`** 🆕 y la dependencia nueva **`packaging>=23.0`** 🆕.
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` + `tests.yml` (matriz Python 3.10/3.13).

---

## **🧩 Módulos Implementados y Arquitectura de Archivos**

### **1. Núcleo y Operaciones Git (`src/core.py`)**

* **Generación Estructurada:** Se comunica con el LLM pidiendo salida estrictamente JSON.
* **Map-Reduce (Diffs Gigantes):** Cuando el diff supera ~90k tokens, lo divide automáticamente en lotes por archivo (`split_diff_into_chunks`), procesa cada parte (Map) y unifica los resúmenes (Reduce). Soporta PRs, commits e Issues. 🆕 `provider`/`model` ahora se **agregan entre los chunks** y `meta`/`modules` se pasan a `log_command_metric`, que ahora copia los tokens reales de la metadata del proveedor en el momento de la escritura.
* **Tokenizer Local:** `tokenizer.json` para estimación precisa de tokens antes del envío a la IA.
* **Estimación de Tokens:** Heurística ligera `len() // 4` vía `estimate_token_count()` con fallback al tokenizer local.
* **Optimización Nativa de Git:** Flags `-U1`, `-w`, `-M`, `-B` en los comandos `get_git_diff` y `get_git_full_diff` para reducir contexto inútil.
* **`get_split_diff()`:** El diff propio del `split`, con `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberadamente sin** reutilizar `get_git_diff()` — el `-w` (ignora espacios en blanco) y los smart-excludes romperían la garantía de árbol byte-idéntico.
* **Pre-Save (`--pre-save`):** Flag oculta de debug que guarda el payload completo (instrucción del sistema + prompt) en JSON antes de cada llamada a la IA.
* **Smart Excludes con Dos Capas:** Capa global (`~/.gitpr/conf/`) + capa local del proyecto (`./.gitpr/conf/`), fusionadas en runtime. Auto-seed del archivo local en la primera ejecución. `_load_smart_excludes()` acepta `force=`.
* **Métricas con Seguimiento de Tiempo:** `log_command_metric()` en todos los flujos, ahora con `meta`, `modules` y `duration_ms`. 🆕 Dos copias divergentes del mapa comando→carpeta de caché se **eliminaron** de aquí — eran la causa de que `tokens_actual` fuera siempre `0`.
* **Resolución Centralizada de Salida:** `resolve_output_path()` — por defecto en `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`)**: `gitpr --init` — detecta el forge desde el remote origin, solicita extras por forge, valida el token y persiste **solo en caso de éxito**; anuncia el badge automático en el camino de PR.
* **Plantilla de Skill de Release (`ensure_release_skill_template()`)**: Descarga `templates/gitpr.release.*.md` en el primer uso de `gitpr release`.
* **Registry Compartido de Skills:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` en `src/config.py`. 🆕 Ganó la entrada **`mentor`** (`.gitpr.mentor.md`) — `tests` y `explain` ya estaban ahí en `tests/test_skill_context.py`.
* **Motor de Review con Alcance de Caché (`generate_pr_content`):** `cache_scope` (anexado **solo a la clave de caché**) y `store_diff` (graba el diff revisado). 🆕 `cache_scope` ahora también lleva el **policy pack activo**, para que dos repositorios bajo políticas distintas no compartan una respuesta.
* **Trailer de Coautoría:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotente, preserva trailers de terceros.
* **Subprocesses Blindados:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` en todos los `subprocess.run`; verificación de conexión vía socket `8.8.8.8:53` antes de las operaciones de red.

### **2. Sistema Global de Plugins (`src/plugins.py`)**

* **Arquitectura de Plugins:** Carga plugins de `~/.gitpr/plugins/`, aplicándose a **todos los proyectos**.
* **Plugins de Linter (`linter/`):** Archivos `.yml` fusionados con el `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`):** Archivos `.md` que extienden el contexto del sistema.
* **Factory Closures:** `get_linter_plugins` y `get_prompt_plugins` aíslan estado entre sesiones.
* **Comando `--plugins`:** Lista todos los plugins globales instalados con sus tipos y rutas.
* **Documentación Multilingüe:** `docs/plugins-system.md` en 5 idiomas.
* 🆕 **Relación con los Policy Packs:** el pack es una **tercera** capa de reglas — los plugins pertenecen a la máquina, `.gitpr.linter.yml` pertenece al proyecto y el pack está versionado y commiteado. `load_linter_rules()` fusiona las tres en el orden declarado, con las severidades del pack por encima.

### **3. Interfaz CLI y Configuración (`src/main.py` y `src/config.py`)**

* **Setup Inicial:** Detecta la primera ejecución, crea `~/.gitpr/` y solicita interactivamente las claves, preferencias e idioma. El hint de la primera ejecución apunta a `gitpr demo`.
* **Enrutamiento de Comandos:** Gestiona todas las flags y los **14 subcomandos** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, **`mentor`** 🆕, **`metrics`** 🆕, **`policy`** 🆕, **`risk`** 🆕 y **`baseline`** 🆕.
* **Comportamiento Predeterminado:** Ejecutar `gitpr` sin flags abre la TUI del PR Publisher.
* **Flags (ahora 32 opciones Click en la raíz — 5 salieron, 1 entró en esta ventana):**
  * **Eliminadas 🆕:** `--metrics`, `--export`, `--purge`, `--hook-event` y `--dashboard` — pasaron a ser el grupo `gitpr metrics` (ver §36). **Ruptura de CLI declarada.**
  * **`--mentor` 🆕:** Anexa la sección didáctica de Mentor al review de código (`-r` / `-f`).
  * `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` — sin cambios.
* **Grupo `baseline` 🆕 — 6 subcomandos:** `create` (`--refresh`, `--base`, `--yes`, `--format`), `show` (`--status`, `--rule`, `--file`, `--format`), `validate` (`--format`), `update` (`--recompute`, `--base`, `--yes`), `suppress` (`--reason`, `--scope`, `--debt`, `--owner`, `--due-date`, `--yes`) y `unsuppress` (`--yes`).
* **Grupo `policy` 🆕 — 7 subcomandos:** `list`, `show`, `validate`, `use`, `off`, `install` (`--force`, `--yes`) e `init` (`--stack`, `--yes`).
* **Grupo `metrics` 🆕 — 8 subcomandos:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` y el oculto `hook-event`; los de lectura comparten `--days`/`--since`/`--until`.
* **Subcomando `risk` 🆕 — 4 opciones:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Subcomando `mentor` 🆕 — 2 opciones:** `--finding`, `--provider`.
* **Variables de Entorno (58 claves en `DEFAULT_CONFIG`, +9 en esta ventana):** las cuatro `GITPR_BASELINE_*` (`ENABLED`, `PATH`, `REQUIRE_LOCKFILE_CHECKSUM_MATCH`, `ALLOW_LOCAL_OVERRIDES`), las dos de mentor (`GITPR_REVIEW_MENTOR_MODE`, `GITPR_MENTOR_INCLUDE_ANALOGY`), las dos de policy (`GITPR_POLICY_ENABLED`, `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`) y `GITPR_RISK_INCLUDE_IN_REVIEW`. **Fuera de `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT` y `GITPR_METRICS_CURRENCY`, leídas por `get_model_price()`.
* **Ayuda Contextual:** `-h --flag` muestra documentación específica de la flag con un enlace consciente del idioma; los subcomandos tienen su propio `epilog=` (párrafo Click `\b`). 🆕 `risk`, `policy`, `baseline` y `mentor` entraron en `HELP_MAP`/`HELP_PRIORITY`.
* **Capa de Escritura del `.env`:** `read_env_file_values()` lee **solo el archivo** vía `dotenv_values`; `save_config_values()` con `set_key`; `remove_config_value()` con `unset_key`. `validate_ai_key()` distingue una credencial rechazada (`401`/`403`) de una red inalcanzable.
* **Puerta de Baseline en el Flujo 🆕:** `main.py` resuelve la puerta de baseline antes del lint, del review, del full review, de la auditoría de archivo, del review remoto y del riesgo, y el **código de salida del lint ahora sigue el bloqueo consciente del baseline** — un hallazgo nuevo falla, uno conocido no.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` y `src/ui/pr_publish_help.py`)**

* **Interfaz Interactiva Completa:** TUI construida con Textual para revisar, editar y publicar Pull Requests directamente en el terminal.
* **7 Pantallas Modales:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` y `NoticeScreen`.
* **Badge en el Cuerpo del PR:** `attach_pr_badge()` corre en `src/main.py` justo después de que `pr_data` está completo — **punto de inyección único**. Anexado idempotente por marcador y divulgación explícita en `--no-edit`.
* **`_attach_reviewers` con resolución:** Resuelve lo que el usuario escribió **antes** de enviar, reporta lo que fue descartado y, en un `422` de lote, **repite uno a uno**.
* **Revisores Sugeridos:** Consulta al forge los revisores sugeridos; `_reviewer_suggestion_view()` monta `resolutions` vía `resolve_candidates` y prellena `handles`.
* **Bindings:** F1 (Help), F2 (Guardar .md local), F3 (Publicar vía forge), Esc (Salir).
* **Flujo de Auto-Commit:** Linter → mensaje IA → confirmación → commit → push → publica PR. 🆕 El resultado del linter ahora lleva los **hallazgos estructurados** (`findings_out`), que es lo que el baseline clasifica antes de componer el informe.
* **Manejo de PR Existente / Auto-Upstream / Flujo de Merge** — sin cambios (`GITPR_AUTO_MERGE`).

### **5. Módulo de API de GitHub (`src/github_api.py`)**

* **Shim Deprecado:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` y las demás funciones delegan en `src/infrastructure/scm/github_provider.py`; emite un `DeprecationWarning` y mantiene las tuplas legacy `(ok, data, status)` — ningún código nuevo debe importarlo.

### **6. Motor de Análisis Estático / Linter (`src/linter_engine.py`)**

* **Linter Offline:** Analiza estáticamente las líneas añadidas (`+`) en el diff sin gastar cuota de IA.
* **Reglas YAML:** Lee `.gitpr.linter.yml` (creado vía `--skill`).
* **Plugins de Linter:** Reglas adicionales desde `~/.gitpr/plugins/linter/*.yml`.
* **Comodín de Extensión:** `extensions: ["*"]` significa **todo** archivo, incluidos los que no tienen sufijo y los dotfiles.
* **Ruleset de Secretos Integrado:** `load_linter_rules()` fusiona `src/security_ruleset.py` **después** de las reglas del proyecto y de los plugins. Siete reglas, todas `extensions: ["*"]`: cinco `error` y dos `warning`. La alerta **nunca repite el valor** — el mensaje lleva solo `{file_name}` y `{line_number}`.
* 🆕 **Capa de Policy Pack:** `load_linter_rules()` ahora aplica la fusión en capas del pack activo y **sobrescribe severidades**, sin tocar `.gitpr.linter.yml` ni los plugins.
* 🆕 **Hallazgos Estructurados (`findings_out`):** además de las cadenas de alerta, el motor devuelve los hallazgos normalizados del tipo canónico `src/domain/finding/` — esa lista es la que el baseline usa como huella. Incluye el arreglo del contador de líneas del lado nuevo, para que las líneas de contexto avancen correctamente.
* **Puentes SAST Opt-In:** Semgrep, Gitleaks y Bandit corren en el camino de archivo completo **y** en el camino del diff, con deduplicación `[Gitleaks + Regex]`, enmascarado de valores (`AKIA****`) y un aviso cuando la herramienta está habilitada pero falta del `PATH`.
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=True)` en el flujo remoto, porque el puente ejecuta binarios contra archivos en disco — el árbol local, no el PR.
* **Informe Consolidado:** `generate_linter_report_content()` consolida errores regex + externos en `.gitpr/reports/linter/` — generado solo cuando hay violaciones.
* 🆕 **Consciente del baseline:** las alertas se anotan con el estado del baseline antes de convertirse en informe, y el código de salida del flujo de lint sigue el bloqueo consciente del baseline.

### **7. Seguridad y Autenticación (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cifrado:** Genera la clave maestra `secret.key` en `~/.gitpr/`.
* **Protección de Tokens:** `encrypt_data`/`decrypt_data` para claves de IA, PAT de GitHub y tokens SCM de los forges.
* **Validación Multi-Forge:** `validate_or_request_scm_token(provider, repo_display)` — 401 → bucle de reautenticación preservando el borrador.
* **Secretos en la TUI de Configuración:** Los campos `KIND_SECRET` se editan enmascarados, **nunca** muestran el valor en claro y se cifran con Fernet antes de escribirse.
* **Escaneo de Secretos en el Propio Flujo:** El linter — que corre en el hook pre-commit — es una puerta de credenciales con reglas idénticas en toda máquina.
* ⚠️ **Gaps de cobertura confirmados como aún abiertos en esta ventana:** la regla genérica sigue exigiendo comillas después del separador (`\s*[:=]\s*['\"]`), así que el formato `.env` **sin comillas** (`API_KEY=abc123`) sigue sin detectarse; los prefijos `ASIA…`, `github_pat_…` y `xoxc-`/`xoxd-` siguen fuera (el patrón es `gh[pousr]_` y `xox[baprs]-`); y no hay límite izquierdo en el nombre de la clave, así que `mytoken` casa igual que `token`.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI como Única Fuente:** `get_latest_remote_version()` consulta `https://pypi.org/pypi/gitpr-cli/json` y escribe la caché diaria **sin** el campo `download_url`.
* **Puerta Obligatoria (`enforce_update_required()`):** Devuelve `True` (tras imprimir ambas versiones y el comando pip) cuando la versión publicada es más reciente; `False` cuando está al día, cuando la versión remota es **desconocida (offline)** o cuando la verificación está apagada.
* **`check_and_update()`:** Solo consulta e **informa**, nunca instala.
* **Válvula de Escape:** `GITPR_SKIP_UPDATE_CHECK` — no se anuncia; existe para la suite y para la automatización offline.
* **Versionado Centralizado:** `__version__` (**1.4.0** 🆕), `__lang_version__` (**v0.0.34** 🆕 — cadena v0.0.31 → **v0.0.32** (release notes) → **v0.0.33** (mentor) → **v0.0.34** (bump de release)), `__scripts_version__` (**v0.0.4** 🆕), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* 🆕 **En esta ventana los tres bumps salieron del working tree y se convirtieron en commit:** `af35270` subió GitPR, idiomas y scripts juntos, y el `CHANGELOG.md` dejó de anunciar una versión que el paquete no construía.
* ⚠️ **Fix pendiente en el working tree:** los encabezados de sección de `[1.4.0]` salieron localizados (`Funcionalidades`, `Documentação`, `Tarefas`, `Contribuidores`) y se corrigieron al inglés **sin commit** — es el único cambio abierto en el repositorio hoy.

### **9. Interfaz de Chat Interactivo (`src/ui/chat_app.py`)**

* **TUI Completa:** Historial de mensajes, entrada multi-línea, barra de estado con bindings visibles. `ENABLE_COMMAND_PALETTE = False`.
* **Memoria por Rama (`src/chat_memory.py`):** Historial persistido por rama, con continuidad entre sesiones.
* **Comandos Slash:** `/explain`, `/tests`, `/optimize`, `/clear`. `/tests` (y los alias localizados) delega en el **mismo caso de uso** que usa la CLI.
* **Auto-Patching (F5), Actualización de Diff (F2), Exportación de Sesión (F6).**
* **Extractor Compartido:** F5 y `ctrl+s` llaman a `patch_extractor.extract_code_blocks()` — comportamiento visible idéntico, sin lógica duplicada.

### **10. Internacionalización — i18n (`src/i18n.py`)**

* **Sistema Inspirado en Laravel:** Función `__()` con placeholders nombrados (`{count}`, `{file}`, etc.).
* **Detección Automática:** Detecta el idioma del SO en la primera ejecución y lo guarda en `GITPR_LANG`; el directorio de perfil se crea **antes** de persistir.
* **5 Idiomas, 6 Diccionarios:** en_us (predeterminado/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Archivos Versionados:** `__lang_version__` (**v0.0.34**) controla la actualización de los paquetes (`langs/*.json`).
* **Cobertura:** **1603 claves** en cada uno de los 6 archivos — paridad total de key sets **y cobertura total del código**: el código usa **1603** claves y **ninguna** queda sin traducir (+445 en esta ventana; la deuda de 40 claves de la ventana anterior quedó cerrada). ✅
* **Snapshot del Entorno (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` capturado **inmediatamente antes** del `load_dotenv()` a nivel de módulo.
* **Caché con Indexación por Idioma:** Las respuestas de IA en caché incluyen el idioma actual en el keying MD5.
* **Fuente de Traducción en Lockstep:** una clave nueva debe existir en el código, en `langs/pt_br.json` (lista maestra), en los diccionarios FR/ES de `scripts/sync_all_langs.py` y en los valores curados de `scripts/fix_mangled_i18n_keys.py`.

### **11. Spinner Animado (`src/spinner.py`)**

* **Braille + Thinking Words:** Hilo en background durante llamadas de IA con caracteres braille y palabras de "pensamiento".
* **263 entradas por idioma:** Sincronizadas entre los 5 idiomas. `_load_thinking_words()` / `reload_thinking_words()` aceptan `force=`.

### **12. Proveedores de IA (`src/ai_providers.py`)**

* **3 Proveedores Soportados:** Google Gemini, DeepSeek y Ollama (local), con un primario/secundario por proveedor.
* **Modo JSON & Parámetros Deterministas:** `temperature=0.0` y `top_p=0.1`; fallback automático entre los proveedores configurados.
* **Timeout de IA:** predeterminado de **180s** (`GITPR_AI_TIMEOUT`).
* 🆕 **Llamadas con `quiet`:** el release y las funcionalidades nuevas propagan `quiet` hasta la capa de IA, para que el spinner nunca corrompa una salida `--format json`.
* 🆕 **Una llamada a la vez, con caché:** mentor y explain reutilizan la caché MD5 por prompt — volver a correr sobre un review sin cambios no cuesta nada.

### **13. Caché Inteligente (`src/cache.py`)**

* **MD5 + Metadata:** Keying por hash MD5 del diff y del prompt, con indexación por idioma.
* **Selección del Último Review (`resolve_last_review()`):** Elige el registro `review`/`fullreview` **más nuevo** para un par repo+rama — es la puerta de entrada del `gitpr fix` y, ahora, también del `gitpr mentor`.
* **Diff Revisado (`reviewed_diff`):** Campo en la cabecera del registro que guarda el diff efectivamente revisado, preferido por `fix/apply_fix.reviewed_diff()`.
* **Telemetría y Duración:** Persistencia de `duration_ms` y `meta_raw`.
* **Lectura para el Dashboard:** `scan_cache_files_for_dashboard()` quedó **degradado a puente de pre-migración** — el dashboard lee el ledger, y el escaneo existe solo para reconstruir la serie desde la caché cuando el usuario lo elige en el asistente de migración.

### **14. Motor de Issues y TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Motores de Contexto:** Diff actual, Historial de la rama (`-ht`) y Arqueología por Blame (`-b`).
* **Publicación Multi-Forge:** F3 crea el issue en el forge **configurado** (`GITPR_SCM_PROVIDER`) vía `provider.create_issue` — Azure DevOps lanza `ScmNotSupportedError`.
* **Map-Reduce para Issues:** El contexto por encima de ~90k tokens se divide y se unifica.
* **Manejo de 401:** Señalización de reautenticación sin cerrar la aplicación.

### **15. Arqueólogo de Código (`src/blame_engine.py`)**

* **Git Blame + IA:** Rastrea la evolución y la autoría histórica con clasificación de commits (`ORIGIN` vs `REFACTORING`).
* **Métricas de Blame:** Eventos vía `log_blame_metric()` con profundidad y número de commits analizados.
* 🆕 **Ahora también alimenta al riesgo:** el lector de historial de la puntuación (`risk_history_reader.py`) usa la misma ventana de 90 días y clasifica bugs/reverts por patrón, sin llamar a la IA.

### **16. Servidor MCP e Invocación CLI Directa (`src/mcp_server.py`)**

* **16 Herramientas MCP Anotadas 🆕:** las 14 anteriores + **`get_baseline_status`** 🆕 (el digest del baseline: conteos por estado, reglas principales, supresiones y deuda aceptada con responsable y fecha límite, deuda vencida, estado del checksum — **nunca ejecuta el linter, nunca llama a la IA, nunca escribe) y **`get_usage_metrics`** 🆕 (el ledger de uso sobre una ventana: ejecuciones, costo por modelo, módulos tocados, proveedores y tasas de calidad; sin atajo de días — quien llama nombra sus propias fechas).
* **19 Recursos + 7 Prompts Templatizados 🆕:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + **`baseline://summary`** 🆕 + `prompt://list` + 7 prompts.
* **Registry de Skills Alineado 🆕:** `mcp_server.SKILL_FILES` ahora tiene **11 entradas** — recibió `tests`, `explain` y `mentor`. La aserción `TestSkillRegistryAgreement`, que reportaba la divergencia, ahora **pasa**. ✅
* **Invocación CLI Directa:** `gitpr-mcp --tool <name> [--tool-args '<json>']` invoca cualquier tool sin iniciar el servidor stdio; `gitpr-mcp --list` imprime el registry completo como JSON.
* **Aislamiento del Stdout Real:** `_write_real_stdout()` escribe en el `sys.__stdout__` original, con fallback a `buffer` o re-codificación con `errors='replace'` en páginas de código legacy de Windows.
* **Offload del Event Loop:** Decorador `_offload` (`anyio.to_thread.run_sync`) en todas las tools — el orden de los decoradores importa (`@mcp.tool` **encima** de `@_offload`).
* **Fallos como Errores Estructurados 🆕:** las herramientas nuevas devuelven un fallo **como dato** en lugar de lanzar — un agente que pide el estado del baseline recibe el motivo del fallo, no una excepción.

### **17. Dashboard de Métricas TUI (`src/ui/metrics_app.py`)**

* **Alcance por Repositorio:** Etiqueta `📁 Repository: owner/repo` y filtrado estricto por proyecto.
* **Escaneo Asíncrono con Overlay:** Worker thread en background con una `ProgressBar`; las pruebas esperan `workers.wait_for_complete()` antes de asertar.
* 🆕 **Lectura del Ledger:** el dashboard dejó de escanear archivos JSON y ahora consulta `telemetry.db` en el worker thread, renderizando las **cinco secciones nuevas** (costo, calidad, módulos, proveedores, ciclo) con los mismos renderers que usa la CLI.
* 🆕 **Asistente de Migración (`src/ui/metrics_migration_app.py`):** ofrece importar los archivos legacy, importar **y** reconstruir desde la caché de IA, o "ahora no" — con una semántica de aborto que **nunca crea un ledger vacío** sobre archivos aún no importados.
* **Exportación Local:** CSV/JSON en `./.gitpr/metrics/export/` — 🆕 `.gitpr/metrics/` entró en el `.gitignore` con un comentario que explica por qué `.gitpr/skill/` **no** (es contenido del proyecto). Los 35 artefactos ya rastreados siguen rastreados, porque `gitignore` no quita nada del rastreo.

### **18. Sistema de Métricas y Telemetría (`src/metrics.py`)**

* 🆕 **Reescrito en esta ventana — ver §36.** `src/metrics.py` dejó de escribir archivos y ahora opera sobre **filas del ledger**; `enrich_metrics_from_cache()` y `load_cache_token_summary()` se **eliminaron** (eran el match heurístico que ponía `tokens_actual` en cero).
* **Alcance por Repositorio:** Todos los eventos indexados por `repo_name`, ahora resueltos por `src/infrastructure/git/identity.py` — que funciona en **cualquier** forge, no solo GitHub.
* **Eventos de Hook, Linter y Blame:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Exportación y Limpieza:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` con confirmación interactiva, `bundle`/`merge` para transportar y fusionar ventanas.

### **19. Sincronización de Idiomas de los Hooks Git**

* **Versionado Independiente:** `__scripts_version__` (**v0.0.4** 🆕).
* 🆕 **Plantillas reescritas:** `post-checkout`, `post-merge` y `pre-push` ahora llaman a `gitpr --quiet metrics hook-event <name>`, protegidas por `command -v gitpr` y `|| true` — se eliminó la resolución de repositorio en bash, que era **solo-GitHub** y registraba eventos bajo nombres de carpeta inutilizables. **Acción requerida: reinstalar los hooks con `gitpr --installhooks`.**
* **Mapeo de Sufijos (`HOOK_SCRIPT_SUFFIXES`):** Los códigos de interfaz (`es_es`, `fr_fr`) se traducen a los sufijos publicados (`.es`, `.fr`).
* **Elección vs. Estado (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Permite **detectar un cambio de idioma**.
* **`effective_hook_lang()`:** Resuelve el idioma efectivo; `--lang` dejó de descartarse en esa ruta.
* **Skip de Merge-Source:** `prepare-commit-msg` salta las fuentes `message|merge|squash|commit`.

### **20. Bridge de Linters Externos y Asistente Interactivo (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Asistente `--linter-setup`:** Wizard con presets numerados (PHP_CodeSniffer, ESLint, Stylelint) e inyección del bloque `external_linters`.
* **Presets Remotos:** `templates/gitpr.linter-presets.json` con la cadena de fallback local → descarga → stale → embebido.
* **TUI de Errores del Linter:** `src/ui/linter_app.py` (Textual) muestra errores críticos y warnings; en modo hook/quiet imprime y hace `sys.exit(1)`.
* **Informe Markdown:** Consolidado en `.gitpr/reports/linter/` solo cuando hay violaciones.
* **La Capa SAST Es Hermana, No un Reemplazo:** los puentes Semgrep/Gitleaks/Bandit alimentan el **mismo** pipeline de informes.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstracción Única (`ScmProvider` ABC):** `base.py` define el contrato (dataclasses `RepoRef`, `PullRequestDraft` etc. y `ScmProviderError(provider, http_status, message)` — `http_status` 0 = fallo de red).
* **Registry y Factory:** `resolve_scm_provider()` selecciona por `GITPR_SCM_PROVIDER` (predeterminado `github`); `detect_provider_from_remote()` identifica el forge desde la URL de origin.
* 🆕 **`list_pull_requests(repo, state, since, until)` — paginado y canónico:** los cuatro proveedores traducen los estados canónicos (`open`/`merged`/`closed`/`all`) al vocabulario nativo, paginan según el cursor de cada API (Link, `X-Next-Page`, token de continuación, next-URL), filtran por una ventana de fechas inclusiva y se detienen antes cuando pueden. **`list_open_pull_requests(repo)` pasó a ser un envoltorio fino** sobre la clase base — ningún call-site cambió, pero el límite de una sola página desapareció.
* 🆕 **Por qué el filtro es por fecha del estado:** tres de los cuatro forges devuelven algo más amplio para un PR fusionado (GitHub no tiene estado `merged`, Azure lo llama `completed`, Bitbucket dice `DECLINED`), así que cada uno filtra su propia respuesta contra la fecha que el estado implica — `merged_at` para los fusionados, `closed_at` para los cerrados, `created_at` para el resto.
* 🆕 **`supports_merged_dates`:** Atributo de clase, `False` en Bitbucket Cloud, que publica `merged_at` **vacío** en lugar de una fecha cercana — declarado **antes** de la llamada, como `supports_reviewable_diff`.
* 🆕 **`web_links.py` — GitPR empezó a construir enlaces HTML:** hasta esta ventana todos los enlaces venían listos en el `html_url` del payload. Las funciones puras (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) normalizan cualquier formato de remote — `https`, `ssh://`, `git://`, scp-like, con credenciales, puerto personalizado y **subgrupos anidados de GitLab** — a una base https y aplican el patrón de página de cada forge. Azure DevOps no tiene una URL de perfil simple, así que sus contribuidores conservan el nombre para mostrar.
* **`get_pull_request(repo, pr_id)` — método concreto de la ABC:** el default lanza `ScmNotSupportedError` y cada forge lo implementa — `list_open_pull_requests` pagina una sola página y no distingue cerrado de inexistente.
* **Encabezados de GitLab Sintetizados:** `changes[].diff` es un **hunk suelto**; `old_path`/`new_path` montan los encabezados `diff --git a/… / --- / +++`, honrando `/dev/null`. Y `overflow: true` **lanza** en lugar de revisar la mitad de un diff truncado.
* **`request_pull_request_reviewers` devuelve `list[str]` (ruptura de contrato):** Devuelve los logins **efectivamente anexados**, leídos del cuerpo del `201`. Las subclases de terceros deben actualizarse — **ítem heredado, aún sin documentar.**
* **Helpers Read-Only de GitHub:** `get_commit_author_login` (SHA → cuenta ligada al correo del autor) y `get_user_login` (valida/canonicaliza un handle sin gastar petición cuando no puede ser un login).
* **Fail-Fast por Forge:** Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket exige `GITPR_SCM_USERNAME`; `create_issue` en Azure lanza `ScmNotSupportedError`.
* **Publicación de Release:** `provider.create_release()` usado por `gitpr release --publish`.
* **Artefactos:** Glosario + ADR-001/ADR-005; la familia `docs/scm-multiforge.*.md` en 5 idiomas; 9 archivos de prueba, **305 escenarios** 🆕.

### **22. Subcomando `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Flujo:** `git log` entre `--since` y `HEAD` → clasificación por Conventional Commits → bump semántico sugerido (`--version <x.y.z>` lo sobrescribe) → ensamblado del changelog → resumen ejecutivo de IA opcional → *anteposición* a `CHANGELOG.md`.
* 🆕 **El rango dejó de venir de `git describe`:** `describe --tags --abbrev=0` solo ve los tags alcanzables desde HEAD — en este repositorio resolvía a `v0.0.8`, así que **cada release listaba todas las versiones anteriores**. La sección `[1.3.0]` salió con **124 viñetas, 115 de ellas repetición de 1.2.0**, y el bump sugerido podía ser **inferior** a la versión ya publicada. El origen del rango pasó a ser la **sección anterior del propio changelog**, mediante dos anclas: el hash más nuevo de esa sección (resuelto en un solo `git log --no-walk`, ordenado por epoch para sobrevivir a zonas horarias mezcladas) y, como fallback, el tag de la versión de la sección; sin ninguno de los dos, degrada al último tag **con un warning visible**.
* 🆕 **`_drop_released()` — la segunda línea de defensa:** cualquier commit cuyo hash corto aparezca en **otra** sección de versión se descarta, con un conteo. Cuando no sobrevive nada, la ejecución **aborta** con `❌ Nothing new to release` en lugar de escribir una sección vacía.
* 🆕 **Cada viñeta pasó a ser un enlace:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`. El número de PR viene del `(#123)` final del squash-merge, que permanece en el subject; sin forge o sin token, degrada a texto plano.
* 🆕 **El fallo de un PR fusionado por merge commit — arreglado por construcción:** `git log --no-merges` significa que un merge commit **nunca** llega al clasificador, así que `pr_number` era `None` **por construcción** y ningún changelog tuvo jamás un segmento `[#n]`. Una **segunda pasada sobre el mismo rango** (`git log --merges`) construye `hash → PR` desde la ascendencia (`rev-list <merge>^1..<merge>^2`): las viñetas siguen siendo una por commit, ahora con el enlace. Un merge que no es un pull request (`Merge branch 'main' into …`) no presta un número, un commit alcanzable por dos merges conserva el más cercano, y el arreglo **no es retroactivo** — solo una sección recién cortada gana enlaces.
* 🆕 **Contribuidores resueltos por la misma escalera que los revisores:** `get_commit_author_login` primero (ve direcciones privadas que la búsqueda de usuarios no ve), luego `email_to_handle`, cacheados en `~/.gitpr/cache/contributors.json` — **solo se cachean los éxitos**, así que una ejecución limitada por rate-limit vuelve a intentarlo después.
* 🆕 **El Límite de Sección Era un Bug Latente:** `upsert_changelog(force=True)` reemplaza desde la línea `## [x.y.z]` hasta el siguiente encabezado de **nivel 2**. Un `###` como límite dejaba el cuerpo viejo vivo **por encima** del nuevo; el `### Summary` y el pie de contribuidores quedaban atrás.
* **Clasificador (`src/commit_classifier.py`)** y **Builder con Secciones Traducibles (`src/changelog_builder.py`)** — 🆕 el builder ganó la dataclass `LinkContext` y el clasificador ganó `pr_number_from_merge()`, que entiende merges de GitHub, GitLab, Bitbucket y Azure DevOps.
* **Bump Semántico (`src/version_bump.py`)** y **Publicación** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 opciones en el subcomando.**
* **Rupturas declaradas:** `ReleaseNotesResult` ganó el campo obligatorio `previous_version`; `build_release_section` ganó el parámetro opcional `links` (retrocompatible).
* **Artefactos:** `docs/release-notes.*.md` — la familia ya existía en 5 idiomas y se **reescribió en esta ventana** para el motor arreglado 🆕 —, ADR y `tests/test_web_links.py`.
* **Estado del release 1.4.0:** el `CHANGELOG.md` tiene la sección completa `[1.4.0] - 2026-10-12` — las cinco funcionalidades de esta ventana **más** la nota de 1.3.0, con enlaces. Es la primera sección generada por el motor arreglado.

### **23. Subcomando `gitpr config` — TUI de Configuración**

* **Pantalla Master-Detail (`src/ui/config_app.py`):** Categorías a la izquierda, campos a la derecha, editados en línea. Cabecera con búsqueda (`/`) y un contador de cambios pendientes; pie `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schema Declarativo (`src/config_schema.py`) — la única fuente de verdad:** **16 categorías** 🆕 (+2: **Policy** y **Baseline**) y **80 `ConfigField`** 🆕 (+9), de los cuales **8 son avanzados**. Cada campo declara su categoría, el tipo de widget, `show_if`, validadores, marcadores de versión y acciones de descarga.
* **Categorías `policy` y `baseline` 🆕:** los campos del pack (habilitación y techo de caracteres del contexto inyectado) y los cuatro del baseline, incluida la ruta del archivo y el switch que exige el checksum del lockfile.
* **Sección Skills Alineada 🆕:** `SKILL_LABELS` pasó de 8 a **11 entradas** — recibió `tests`, `explain` y `mentor`. Los dos tipos que se renderizaban **en blanco** en la barra lateral ahora tienen etiqueta, y las dos aserciones de `test_config_schema.py::TestSkillsSection` **pasan**. ✅
* **Filtrado por Contexto (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` según `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` bajo `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` bajo `[Azure DevOps]`.
* **Búsqueda Global (`/`):** Coincide con la clave o la etiqueta en todas las categorías e **ignora el filtro de visibilidad**.
* **Validación en Dos Capas:** **Offline** bloquea `F2` con un error en línea; **online** (solo credenciales cambiadas en la sesión) corre en un worker con timeout de 10s y solo bloquea ante `401`/`403`.
* **Restaurar (`Ctrl+R`):** Elimina la línea del `.env` en lugar de reescribir el valor predeterminado; `Esc` con cambios pendientes pide confirmación.
* **Sección Skills — la única con alcance de proyecto:** Edita los `.gitpr/skill/*.md` del proyecto resueltos desde el directorio de invocación, escribiendo atómicamente y preservando CRLF/LF.
* **Descargas Forzadas:** Botones que fuerzan la redescarga de smart-excludes, traducciones, presets y thinking words vía `force=`.
* **Módulo Ligero de Enlaces (`src/doc_links.py`):** `doc_url()` salió de `core.py` para que la UI pueda obtener el enlace sin importar los SDKs de IA.
* **Pruebas:** 5 archivos — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9). 🆕 La suite ahora **estabiliza el entorno** antes de montar la TUI — sin eso, un `GITPR_BASELINE_*` filtrado del shell cambiaba el árbol de categorías.
* **Deuda conocida:** `gitpr -h config` abre la TUI e ignora `-h` — la puerta `if ctx.invoked_subcommand is not None: return` (main.py:678) corre antes del bloque `help_flag`.

### **24. Registro General de Uso (`src/usage_log.py`)**

* **Una Línea por Comando:** Escribe en `~/.gitpr/logs/<uuid5>.log`, **un archivo por día**, con el comando, los argumentos, el repositorio, el usuario y el timestamp.
* **Nombre Derivado de la Fecha:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` — dos procesos concurrentes no pueden discrepar sobre cuál es el archivo de hoy.
* **Escritura Síncrona (decisión explícita):** A diferencia de `log_local_metric`, que usaba un hilo daemon y perdía la escritura si el proceso terminaba antes.
* **Nunca Imprime:** El servidor MCP reserva la stdout para JSON-RPC; el módulo tampoco lanza excepciones nunca.
* **Un Solo Spawn de Git:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms en vez de ~150 ms en Windows, en *cada* ejecución.
* **Control:** `GITPR_SHOW_LOGS` (predeterminado `"true"`); desactivado en `tests/conftest.py`.
* **Artefactos:** `docs/usage-log.*.md` en 5 idiomas; `tests/test_usage_log.py` (27 escenarios).

### **25. Subcomando `gitpr fix` — Hallazgos de Review como Patches Revisables (`src/fix/`)**

* **El Pipeline Real:** último review de la caché (`resolve_last_review`) → **una** llamada de IA → extracción y validación del diff unificado → `git apply --check` → clasificación determinista → dry run o escritura → historial.
* **Paquete de 8 Archivos:** `patch_provenance.py`, `patch_extractor.py` (compartido con el chat), `patch_safety_classifier.py` (puro, sin I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Clasificación Determinista:** Rechaza un patch que atraviese más de un archivo o hunk, que toque una ruta sensible, que reviente el presupuesto de líneas, que **borre una línea con pinta de llamada**, que venga marcado de baja confianza o que falle el `git apply --check`. El `--force` **nunca** sortea la aplicabilidad, solo la clasificación.
* **Escritura Opt-In:** El dry run es el comportamiento predeterminado; cualquier mutación exige `--apply` (o una frase escrita con `--force`).
* **Pruebas:** 10 archivos en `tests/fix/` (**204 escenarios**) con fixtures de repositorio git real.

### **26. Subcomando `gitpr review-pr` — Review de PR Remoto (`src/review/`)**

* **Qué Es:** Orquestación, **no** un segundo motor de review — el motor existente, el linter y el renderizador alimentados con un diff que vino de otro sitio.
* **Paquete de 5 Archivos:** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Read-Only por Defecto:** `--post-comment` es el **único** camino que escribe en el forge. La tool MCP `review_remote_pr` ni siquiera recibe el argumento.
* 🆕 **El baseline se resuelve antes de la primera llamada de red**, y los avisos de la puerta se fusionan en el informe/comentario — un PR remoto se juzga por la misma política que el repositorio local.
* 🆕 **`render.py` ganó el parámetro de riesgo:** `compose_review_content` / `render_review_result` **anteponen** la sección `⚡ Risk Assessment` por encima de las alertas del linter, cuando `GITPR_RISK_INCLUDE_IN_REVIEW` está activo.
* **Artefactos:** `docs/review-pr.md` + `.pt_br.md` (los 3 idiomas restantes **siguen pendientes**), `docs/code-review-ia.*.md` (5), ADR-005 y `glossary-review-pr.md`.
* **Pruebas:** 4 archivos en `tests/review/` (**97 escenarios**).

### **27. Resolución de Identidad del Revisor (`src/reviewer_resolution.py`)**

* **El Bug (dos fallos silenciosos encadenados):** (1) **Prefill vacío** — `handles` solo desde `email_to_handle()`; (2) **Attach no verificado** — valores pasados *verbatim*, GitHub respondió **201 sin anexar a nadie** y `_request()` retornó sin lanzar.
* **Módulo:** Un plano, sin I/O propio, **y que nunca lanza**. `resolve_candidates()` (antes de la TUI) y `resolve_typed_reviewers()` (en el momento del attach); `match_candidate()` casa exacto y normalizado por login, nombre o correo.
* **Escalera de Resolución:** handle ya conocido (sin petición) → casamiento exacto con una persona sugerida → lookup por correo → validación del login en el forge.
* **Lo que no resuelve nunca se envía:** Sale en `ResolutionOutcome.dropped` como `(value, i18n_reason)` y se descarta con aviso visible.
* **La Misma Escalera Sirve al Release 🆕:** `release_engine` reutiliza `get_commit_author_login` + `email_to_handle` para convertir correos de commit en logins clicables.
* **Pruebas:** `tests/test_reviewer_resolution.py` (18) + ampliaciones en `test_github_provider.py` (72), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) y `test_main_suggest_reviewers.py` (8).

### **28. Subcomando `gitpr demo` — Recorrido Guiado sobre Ejemplos Grabados (`src/demo/`)**

* **Qué Es:** Un recorrido interactivo que muestra las tres salidas principales de GitPR — mensaje de commit, review y descripción de PR — **sin clave de API, sin repositorio git y sin red**.
* **Pipeline Real, Respuesta Falsa:** `FakeAIProvider` refleja `src.ai_providers.call_ai_model` **argumento por argumento**; `demo_pipeline()` parchea `src.core` y `src.metrics` para que el pipeline de producción corra **intacto** y solo se intercambie la fuente de la respuesta.
* **El Aislamiento Es un Requisito, No un Detalle:** El recorrido **no puede** tocar el `~/.gitpr` del usuario ni la red — garantizado por `tests/demo/conftest.py` (prohibición de red autouse, con una excepción de loopback para el event loop proactor de Windows).
* **Paquete:** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (dos ejemplos localizados en los 5 idiomas). Los escenarios son **módulos Python, no JSON**, para que viajen dentro del wheel.
* **TUI:** `src/ui/demo/` con un modal de ayuda; el diff va cercado como ```` ```diff ````.
* **CLI:** `gitpr demo` con `--scenario`, `--lang` y `--no-tui`.
* **Pruebas:** 6 archivos en `tests/demo/` (**155 escenarios**).
* **Artefactos:** `docs/demo.*.md` en 5 idiomas, `README.*` (5) y las métricas de ejemplo del recorrido.

### **29. Badge de GitPR y el Comando `gitpr badge` (`src/branding/`)**

* **Qué Es:** Un badge público que certifica que un pull request pasó por una verificación local de calidad, y un comando que imprime el snippet de adopción para el README del proyecto.
* **URL Estática a Propósito:** Es un Markdown de shields.io que GitPR **nunca** consulta — publicar un pull request no puede pasar a depender de que un tercero esté en línea.
* **Medición Honesta (`badge_data.py`):** `collect_linter_counts()` devuelve `None` cuando no hay reglas de linter configuradas, porque una lista vacía significa "nada se verificó", no "nada se encontró".
* **El Badge Nunca Bloquea una Publicación:** Cada camino de fallo se captura y degrada a "sin badge".
* **Punto de Inyección Único:** `attach_pr_badge()` en `src/main.py`. Anexado idempotente por marcador.
* **Comando `gitpr badge`:** `--readme` y `--style`. **Solo imprime** — el README nunca se modifica.
* **Configuración:** `GITPR_BADGE` (predeterminado `true`) — opt-out **de solo lectura**.
* **Pruebas:** 7 archivos en `tests/badge/` (**83 escenarios**).
* **Artefactos:** `docs/badge.*.md` en 5 idiomas y el survey dedicado.

### **30. Subcomando `gitpr split` — Commits Atómicos por Hunk (`src/split/`)**

* **Qué Es:** Un árbol de trabajo con varios asuntos ya no se convierte en un commit-blob. `split` lee el diff no commiteado, pide a la IA que particione los hunks por **intención lógica** y propone un commit atómico por asunto. El árbol nunca se reescribe: los archivos terminan byte a byte idénticos al inicio.
* **Paquete de 6 Archivos:** `split_plan.py`, `hunk_parser.py` (puro), `hunk_grouper.py`, `generate_split_plan.py` (read-only), `apply_split_plan.py` (**el único que muta**) y `__init__.py`.
* **La Garantía de Árbol Intacto Es Estructural:** `selective_stager.py` pre-valida **cada** grupo contra un índice temporal en HEAD y usa un `GIT_INDEX_FILE` desechable.
* **Destructivo para el Índice, No para el Árbol:** `--apply` resetea el índice a HEAD antes de hacer el staging, así que lo que el usuario ya tenía en stage queda fuera de stage — documentado explícitamente.
* **Configuración:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) y `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Los valores cero o negativos se **ignoran en favor del predeterminado**.
* **Pruebas:** 6 archivos en `tests/split/` (**101 escenarios**).
* **Artefactos:** `docs/split-command.md` — 🆕 **ahora en 5 idiomas** (los 3 que faltaban llegaron el 2026-09-24), ADR-006, `glossary-gitpr-split.md`, spec/plan y survey.

### **31. Escaneo de Secretos Integrado (`src/security_ruleset.py`)**

* **Qué Es:** Siete reglas que corren en **cada** invocación del linter, fusionadas al final de `load_linter_rules()` después de las reglas del proyecto y de los plugins.
* **Las Siete Reglas:** cinco `error` bloqueantes — ID de clave de acceso AWS, token de GitHub, token de Slack, clave de API de Google y bloque de clave privada — y dos `warning`: URL de conexión a base de datos con credenciales y asignación genérica de credencial (`password = "…"`), esta última tras un filtro de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Por Qué en el Paquete y No en la Plantilla:** Una puerta de secretos debe comportarse **igual** en toda máquina y en toda CI, así que las reglas viven en el paquete y no pueden ser reemplazadas por `--skill` ni reescritas por el wizard.
* **Gaps de Cobertura (v1) — confirmados aún abiertos 🆕:** la asignación **sin comillas** no se detecta (`API_KEY=abc123`, el formato `.env`); faltan los prefijos `ASIA…`, `github_pat_…` y `xoxc-`/`xoxd-`; y no hay límite izquierdo en el nombre de la clave.
* **Configuración:** `GITPR_LINTER_SECURITY` (predeterminado `true`; opt-out fail-open) y `GITPR_LINTER_SECURITY_DISABLED_RULES` (separadas por `;`).
* **Pruebas:** `tests/test_security_ruleset.py` (**61 escenarios**).
* **Artefactos:** ADR-007, `glossary-gitpr-secret-scanning.md`, spec/plan/survey y 3 informes de tarea.

### **32. Puentes SAST — Semgrep, Gitleaks y Bandit (`src/infrastructure/linter/external/`)**

* **Qué Es:** Una capa **opt-in** que conecta escáneres de seguridad de terceros al pipeline de linter existente.
* **Alcance Acotado:** Corren **solo** cuando están habilitados y **solo** sobre los archivos tocados por el diff.
* **Contrato Común:** `ExternalLinterBridge` (ABC) con ejecución endurecida de `subprocess` (`shell=False`, timeout estricto, `stdin=DEVNULL`, UTF-8 con `errors='replace'`). 🆕 El modelo `NormalizedFinding` **se fue de aquí** a `src/domain/finding/`, y `base_bridge` ahora **lo re-exporta** para no romper los imports existentes.
* **La Deduplicación Es el Punto:** `deduplicate_secret_findings()` fusiona los hallazgos de Gitleaks y del ruleset de regex en el mismo archivo y línea en **una sola** entrada confirmada como `[Gitleaks + Regex]`.
* **Secretos Enmascarados:** `mask_secret_value()` garantiza que el valor nunca llegue al hallazgo, al log ni a la telemetría.
* **Resolución en Tres Capas:** `load_sast_config()` lee defaults → `.gitpr.linter.yml` (bloque `sast`, con fallback a `linter.external`) → variables `GITPR_SAST_*`. **Todo es `false` por defecto**.
* **Degradación Elegante:** Una herramienta habilitada pero ausente del `PATH` emite un warning y la ejecución continúa.
* **Pruebas:** 4 archivos en `tests/infrastructure/linter/external/` (**14 escenarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + **`tests/domain/linter/test_structured_findings.py` (44)** 🆕, que cubre el paso de los hallazgos normalizados por el motor de lint.

### **33. Subcomando `gitpr tests generate` — Generación de Suites por IA (`src/domain/tests_generation/`, `src/application/`)**

* **Qué Es:** Genera archivos de prueba completos y ejecutables a partir del diff actual, de un archivo específico o de un hallazgo de review, **respetando la convención del repositorio** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Capa de Dominio:** `TestFramework` (enum) y las dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest`; `detect_test_framework()` detecta desde archivos de configuración, manifiestos y el contenido del directorio de pruebas; `build_test_scaffold()` calcula la ruta de destino convencional por framework.
* **Capa de Aplicación (`generate_test_file.py`):** Orquesta la detección, la resolución del scaffold, el prompt, la IA, el parsing JSON y la escritura opcional. `validate_test_syntax()` corre el toolchain local (`php -l`, `node --check`, `python -m py_compile`) — un fallo de validación es un **warning**, no un error.
* **Presentación:** El grupo `tests` con el subcomando `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); el dry run es el comportamiento predeterminado y sobrescribir una prueba existente pide confirmación con **No** preseleccionado. El chat delega `/tests` en el **mismo** caso de uso.
* **Deuda cerrada 🆕:** el tipo `tests` está en `SKILL_LABELS` y en `mcp_server.SKILL_FILES`, y las 17 claves `__()` que no existían se tradujeron. **Sigue sin tema en `docs/`** — solo una plantilla de skill y la spec.

### **34. Subcomando `gitpr explain` y la Flag `--explain` — Reviewer Guide (`src/domain/pr/`)**

* **Qué Es:** Una guía centrada en quien va a **revisar** — qué cambia, por qué cambia, dónde enfocarse y cuál es el riesgo de regresión.
* **Capa de Dominio (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, el renderizador `build_explain_markdown()` y `parse_explain_payload()`, que tolera salida no-JSON y **detecta placeholders** `[FILL]`/`[TODO]` para señalar evidencia insuficiente en lugar de presentar una guía hueca como completa.
* **Capa de Aplicación (`generate_pr_explanation.py`):** Resolución del proveedor, validación de la clave, carga del contexto de skill (`explain`), prompt, invocación y parsing.
* **Dos Puertas de Entrada:** `gitpr explain` (`--provider`, detección de diff ausente antes de cualquier IA) y la flag `--explain` en la CLI raíz, que anexa la guía al cuerpo de la descripción del PR **y** al payload JSON.
* **Configuración:** `GITPR_EXPLAIN_BY_DEFAULT` (predeterminado `false`).
* **Deuda cerrada 🆕:** `explain` está en `SKILL_LABELS` y en `mcp_server.SKILL_FILES`, y las claves se tradujeron. **Sigue sin tema en `docs/`.**

### **35. Suite Determinista y CI (`.github/workflows/tests.yml`)**

* **Qué Es:** El workflow que corre la suite en **Python 3.10** (el piso declarado en `pyproject.toml`) y **3.13**, con `fail-fast: false`.
* **`tests/conftest.py` Hermético:** Fija `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` y `LANG_VERSION` en la versión del código, más `GITPR_SHOW_LOGS=false` y `GITPR_SKIP_UPDATE_CHECK=true`.
* **El Orden Importa:** `src.updater` se importa **antes** de que se definan las variables de idioma, porque `i18n` snapshotea `os.environ` en `AMBIENT_ENV_KEYS` al importar.
* **Estabilidad de las Pruebas de Worker:** `test_config_app.py` y `test_metrics.py` esperan `workers.wait_for_complete()` antes de asertar.
* **Dependencia del Workflow:** El runner limpio no tiene `~/.gitpr`, y `test_demo_isolation.py` aserta que el perfil existe — el job crea explícitamente el directorio y un `.env` vacío.
* 🆕 **En esta ventana la suite creció un 48% y el escenario de fallos se dio la vuelta:** las tres aserciones que existían para reportar la deuda de registry (`TestSkillsSection`, `TestNoMissingKeys`, `TestSkillRegistryAgreement`) **pasan** — el mecanismo de detección funcionó y se saldó en el mismo ciclo en que nació.

### **36. Ledger de Telemetría SQLite (`src/ledger.py`, `src/infrastructure/git/identity.py`) 🆕**

* **El Problema No Era el Formato, Era el Join:** los eventos se escribían como archivos JSON por comando, desde un **hilo daemon** (que un proceso de vida corta — un hook — puede no sobrevivir), y luego se reconectaban a la caché de IA mediante un match heurístico por `(repo, branch, action, minute)` que dependía de **tres mapas comando→carpeta divergentes**. El síntoma era `tokens_actual` **siempre `0`** para `fullreview`/`filereview` y pérdida de datos entre repositorios al exportar.
* **La Escritura Es Síncrona, y Esa Es la Decisión:** `log_command_metric()` resuelve el contexto de git **una vez por proceso** y escribe la fila en el momento, en lugar de entregarla a un hilo que un hook puede no sobrevivir. Los tokens, el modelo y el proveedor se **copian de la metadata del proveedor en el momento de la escritura** — ya no hay enriquecimiento a posteriori.
* **Schema Versionado:** `SCHEMA_VERSION` + `PRAGMA user_version`, claves UUID (no enteros secuenciales, para que el `merge` de dos ledgers no colisione), WAL + `synchronous=FULL`, y `LedgerVersionError` para rechazar un bundle más nuevo **antes** del `ATTACH` — el ledger local nunca queda a medio importar.
* **La Invariante "Nunca Nace Vacío":** `ensure_ledger()` solo crea la base de datos **junto** con la absorción del JSON legacy (originales movidos a `~/.gitpr/metrics_legacy/`, marcador de rechazo, camino silencioso en modo no interactivo).
* **Una Ventana, Un Significado:** `--since`/`--until`/`--days` (inclusivos en ambos extremos) acotan **toda** superficie de lectura. La sección de ciclo es la excepción — pregunta a la red, así que asume los últimos 30 días y **nombra la ventana en su propio encabezado** en lugar de acotar en silencio.
* **Cada Métrica Se Calcula Una Vez y Se Renderiza Una Vez:** los pares `(style, text)` (`TITLE`/`PLAIN`/`WARN`) hacen que la CLI y el dashboard de Textual cuenten la **misma** historia; una sección sin nada que decir se omite, nunca se imprime vacía.
* **Costo con Tabla Integrada y Override:** precios de lista de los cuatro modelos que GitPR distribuye (`deepseek-v4-pro`, `deepseek-v4-flash`, `gemini-2.5-pro`, `gemini-2.5-flash-lite`), sobrescritos por `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Ambas tarifas son obligatorias** — tarifar la mitad de un modelo a un cero no configurado **subestima** la factura, así que el modelo se reporta solo en tokens. `ollama`/`local` cuestan cero; `GITPR_METRICS_CURRENCY` etiqueta el total y, cuando no es USD, **descarta** la tabla integrada en lugar de etiquetar un precio en dólares con otro nombre. Un total que deja modelos fuera dice `Total (partial)`.
* **`modules` Normalizado:** las rutas del diff pasan a dos segmentos (`src/fix`), `(root)` para un archivo en la raíz y `(no module)` para los comandos que nunca tuvieron diff (linter, blame, hooks).
* **Transporte por Bundle:** `bundle` escribe la ventana como un `.db` autocontenido (el schema y el `user_version` viajan con él) y `merge` lo adjunta e inserta por UUID, migrando un bundle viejo hacia arriba y rechazando uno más nuevo.
* **`cache_backfill` Se Queda Fuera:** las filas reconstruidas desde la caché de IA se marcan y se **excluyen de los agregados de ejecución** — la clave de caché es el prompt, así que cuentan prompts distintos, no ejecuciones.
* **Identidad Git sin Forge (`identity.py`):** etiqueta de repo, rama y autor en **una sola** llamada a subprocess, válida en cualquier forge — y deliberadamente **sin** construir un `ScmProvider`, que sería caro en cada comando de telemetría.
* **La Sección de Ciclo Es la Única Que el Ledger No Responde:** lista los PRs fusionados vía `ScmProvider.list_pull_requests` y mide `created_at → merged_at`, con códigos de estado explícitos (ok/empty/unavailable/unsupported). Cada fallo degrada a **una línea** — `Not read: <reason>`, sin fecha de merge en Bitbucket, nada fusionado en la ventana — nunca un stack trace y nunca un número adivinado.
* **Presentación Completa:** grupo `gitpr metrics` con 8 subcomandos, un dashboard que lee el ledger en un worker thread y el asistente de migración de tres opciones.
* **Configuración:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY` y `.gitpr/metrics/` en el `.gitignore`.
* **Pruebas:** `tests/test_metrics.py` saltó de 34 a **164 escenarios**; `tests/scm/*` ganó la paginación de `list_pull_requests` y `within_window` en cada forge; `test_commit_classifier.py` (30) cubre `pr_number_from_merge`.
* **Artefactos:** ADR-008, `glossary-metrics-telemetry.md`, `docs/plans/develop_natan/20260927_metrics_audit_plansfacts.md`, survey y **`docs/metricas-telemetria.*.md` reescrito en los 5 idiomas**. Vocabulario canónico en `docs/plans/glossary-metrics-telemetry.md` (según CLAUDE.md).

### **37. Subcomando `gitpr mentor` y la Flag `--mentor` — Mentor Mode (`src/domain/mentor/`) 🆕**

* **Qué Es:** Un review dice *qué* está mal y casi nunca *por qué importa*. Mentor Mode enriquece los hallazgos con **qué está pasando**, **por qué importa**, una **analogía** cotidiana y los **conceptos que estudiar** — para quien está empezando, el valor de un review está en lo que enseña después de señalar.
* **Capa de Dominio Pura (`mentor_explanation_builder.py`):** sin I/O, convierte el payload de la IA en un `MentorExplanation` validado y exige **honestidad epistémica** en tres frentes: con evidencia insuficiente, la explicación **lo dice** en lugar de rellenar el vacío; las URLs alucinadas se **eliminan** de los punteros "aprender más"; y los IDs de hallazgo desconocidos se **rechazan**, preservando el orden estricto de los hallazgos originales.
* **Capa de Aplicación (`generate_mentor_explanation.py`):** recolecta candidatos (`collect_candidates`), **lotes de como máximo 10 hallazgos** por ejecución, ordenados por severidad, reutilizando la caché MD5 por hash de prompt — volver a correr mentor sobre un review sin cambios **no cuesta nada**.
* **Dos Puertas de Entrada:** `gitpr mentor [--finding <id>] [--provider <name>]` corre sobre el **último review persistido** (por lo que debe venir después de un `-r`/`-f`), y la flag `--mentor` anexa la sección al review normal, con un warning cuando se usa en una acción que no lo soporta.
* **Opt-In Estricto:** `GITPR_REVIEW_MENTOR_MODE` (predeterminado `false`) y `GITPR_MENTOR_INCLUDE_ANALOGY` (predeterminado `true`), editables en la TUI de configuración. Encenderlo cuesta llamadas de IA adicionales — por eso el predeterminado está apagado.
* **Skill y Registry:** `.gitpr.mentor.md` registrado en `SKILL_FILES_BY_TYPE`, en `mcp_server.SKILL_FILES` y en `SKILL_LABELS`, con plantillas en **5 idiomas**.
* **Pruebas:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) y `tests/test_mentor_cli.py` (4).
* **Artefactos:** `docs/mentor-mode.*.md` en **5 idiomas**, ADR-009, `glossary-gitpr-mentor.md`, spec e informes de tarea.

### **38. Subcomando `gitpr risk` — Puntuación de Riesgo Determinista (`src/domain/risk/`) 🆕**

* **Qué Es:** Una puntuación de **0–100** con un nivel `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculada **sin IA** a partir de señales locales, para que quien revisa sepa dónde mirar primero — y para que el mismo diff reciba el mismo número dos veces.
* **Capa de Dominio Pura (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums y dataclasses (`RiskLevel`, `RiskSignal`, `RiskEvidence`, `FileRisk`, `PullRequestRisk`); pesos, umbrales, patrones de rutas críticas, glob matching y detección de pruebas/no-ejecutables cargados desde YAML; cálculo por archivo con **clamp 0–100** y agregación 50/30/20.
* **La Fórmula Está Declarada, No Aprendida:** la agregación combina **50%** el archivo de mayor riesgo, **30%** el promedio ponderado por líneas y **20%** la evidencia crítica. Por encima de eso hay **reglas de escalado obligatorias**: una puntuación ≥ 80 sube a `HIGH` como mínimo; un blocker o una puntuación ≥ 90 fuerza `CRITICAL`. Una puntuación sin regla de escalado es un número que el equipo aprende a ignorar.
* **Adaptadores de Infraestructura:** `risk_history_reader.py` lee `git log` de forma segura y extrae commits, bug fixes y reverts dentro de una ventana de **90 días**; `test_matcher.py` casa un archivo de prueba ↔ un archivo de producción por convención, a través de varios layouts de lenguaje.
* **Caso de Uso (`calculate_risk.py`):** correlaciona las secciones parseadas del diff, los hallazgos, el test matching y el historial en `FileRisk`/`PullRequestRisk`, **degradando con un warning** cuando una señal no está disponible. Aplica el policy pack por encima de la configuración base, cuando hay uno activo.
* **Tres Consumos:** el comando `gitpr risk` (`--file`, `--format text|json`, `--base`, `--no-history`), la sección `⚡ Risk Assessment` anexada a los reviews y al review remoto de PR, y el **prompt de IA** — donde la puntuación entra como contexto **de solo lectura**, en un formato compacto.
* **Opt-Out:** `GITPR_RISK_INCLUDE_IN_REVIEW` (predeterminado `true`) — como el predeterminado lo enciende, la sección empieza a aparecer en los reviews de cualquiera que actualice; apagarla es una variable. **Cambio de comportamiento declarado.**
* **Configuración por Proyecto:** `templates/gitpr.risk.yml` (o `.gitpr/skill/gitpr.risk.yml`) sobrescribe pesos, umbrales y rutas críticas. Los pesos negativos solo se aceptan para señales **mitigantes** como `test_present`.
* **Pruebas:** `tests/domain/risk/` (2 archivos, 10 escenarios), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) y `tests/test_risk_cli.py` (4).
* **Artefactos:** `docs/risk-scoring.*.md` en **5 idiomas**, ADR-010, `glossary-gitpr-risk-scoring.md` e informes de tarea (incluido uno de Gemini).

### **39. Subcomando `gitpr policy` — Policy Packs Versionados (`src/domain/policy/`, `src/infrastructure/policy/`) 🆕**

* **Qué Es:** Un **pack YAML versionado** que lleva, en un solo artefacto, las decisiones de calidad de un repositorio: skills, reglas de linter, severidades, rutas críticas, pesos de riesgo y convenciones de PR/commit. Hoy cada repositorio configura esto a mano — lo que hace que el comportamiento sea difícil de reproducir, compartir y **fijar**. El pack vuelve la configuración declarativa, versionada, componible y reproducible.
* **Capa de Dominio (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`):** parsing del manifest con un **schema cerrado** (una clave desconocida es un error, no un warning), verificación de compatibilidad y credenciales, **checksum SHA-256** y un resolver de grafo con precedencia y composición.
* **Capa de Infraestructura:** `local_policy_repository.py` resuelve el pack desde **tres orígenes** (`.gitpr/policies`, `~/.gitpr/policies` y el paquete embebido) y `policy_pack_loader.py` lo carga.
* **Casos de Uso:** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` y `validate_policy_pack`.
* **Cuatro Packs Integrados:** `laravel-quality`, `node-quality`, `php-security` y `vue-quality`, cada uno con `policy.yml` y `linter.yml`, distribuidos vía **`package-data`** — un pack que no viaja en el wheel es un pack que solo existe en la máquina de quien lo escribió.
* **Consumo Integrado en Cuatro Puntos:** `calculate_risk` (policy por encima de la configuración base), `config.load_linter_rules` (fusión en capas + sobrescritura de severidades), `core.get_skill_context` (inyecta el contexto del pack) y la **clave de caché del PR** (`cache_scope`) — dos repositorios bajo políticas distintas no pueden compartir una respuesta de IA.
* **CLI:** `gitpr policy {list, validate, show, use, off, install, init}`, con confirmación (`--yes`/`--force`), detección de stack en `init` y registro de la elección en `.gitpr/policy.lock.yml`.
* **Configuración:** `GITPR_POLICY_ENABLED` y `GITPR_POLICY_CONTEXT_MAX_CHARACTERS` (el techo del contexto inyectado en el prompt), ambas en la nueva categoría `policy` del schema.
* **Retrocompatibilidad:** sin un pack activo, el comportamiento se **preserva** — cubierto por una prueba de integración, no por inspección.
* **Dependencia Nueva:** `packaging>=23.0` (SemVer/PEP 440), declarada en el `Pipfile` y en `pyproject.toml`.
* **Pruebas:** `tests/domain/policy/` (4 archivos, 126 escenarios), `tests/application/use_cases/` (4 archivos de policy, 89), `tests/test_policy_cli.py` (26) y `tests/integration/test_policy_effect_on_review_linter_risk.py` (31 — efectos reales en un repositorio real, con un guard de red autouse).
* **Artefactos:** `docs/policy-packs.*.md` en **5 idiomas**, ADR-011, `glossary-policy-packs.md`, spec/plan/survey.

### **40. Subcomando `gitpr baseline` — Baseline y Supresiones Auditables (`src/domain/baseline/`) 🆕**

* **Qué Es:** Los linters y los reviews vuelven a reportar los mismos hallazgos preexistentes en cada ejecución, y el trabajo de distinguirlos de cabeza queda para quien revisa. El baseline registra el **snapshot de los hallazgos que el repositorio ya conoce** en `.gitpr/baseline.json`, calcula la huella de cada uno de forma estable entre ejecuciones y permite que el pipeline clasifique lo que es nuevo — **solo `new` bloquea**.
* **Capa de Dominio (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`):**
  * **Huella estable:** identidad de la regla, ruta normalizada, líneas y **sha256 de la línea con los espacios colapsados** — la línea cruda **nunca** se almacena, así que el baseline no es un archivo donde los secretos se filtren por accidente.
  * **Manifest canónico:** JSON canónico con checksum, validación que rechaza una clave desconocida, una versión de schema/huella fuera del rango soportado, una invariante de supresión/deuda rota, una huella duplicada y un rango de líneas inválido.
  * **Comparador:** clasificación, conteos por estado y resolución de entradas — `resolved` y luego `back` se lee como **nuevo**, que es la lectura conservadora.
  * **Suppression Policy:** supresiones, deuda aceptada, overrides y validadores, con casamiento en capas (entrada × overrides × policy pack) por **especificidad y precedencia**, más el seguimiento de deuda vencida.
* **Cinco Casos de Uso:** `baseline_gate` (la puerta de entrada única del pipeline — carga, clasifica, anota y compone la entrada, las capas local y de overrides del pack), `baseline_summary` (vista JSON **de solo lectura** para IDEs y agentes MCP), `compare_against_baseline`, `create_baseline` (creación/actualización con procedencia, checksums y **preservación de las decisiones humanas**) y `suppress_finding` (suprime/elimina en los ámbitos de hallazgo, línea, archivo y regla).
* **Integración con Policy Pack:** el pack puede llevar un bloque `baseline:`, validado por los **mismos** validadores, compuesto por el resolver como una capa etiquetada y reportado por `policy validate`.
* **El Repositorio Es el Único Que Escribe:** `local_baseline_repository.py` es el **único** lector/escritor de `.gitpr/baseline.json` y `.gitpr/baseline.overrides.yml`, con escritura atómica, una puerta de checksum/versión y un camino de reparación.
* **Configuración:** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` y `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`, en la nueva categoría `baseline`.
* **Opt-In y No-Op:** apagada o sin un baseline existente, la funcionalidad **no lee ningún archivo y no imprime nada** — quien no la usa no ve ningún cambio.
* **⚠️ La Excepción Deliberada:** un archivo de baseline que existe pero **no puede aplicarse** hace que el lint, el review, el full review, la auditoría de archivo, el review remoto y el riesgo **aborten con código de salida 1**. Un baseline que falla en silencio es un baseline que "hace desaparecer hallazgos" sin que nadie lo sepa — abortar es la opción segura.
* **Alcance:** CLI (6 subcomandos, todos con `--format json`), MCP (tool `get_baseline_status` + recurso `baseline://summary`), review remoto de PR (resuelto **antes de la primera llamada de red**) y el flujo de lint, cuyo código de salida ahora sigue el bloqueo consciente del baseline.
* **El Refactor Que la Funcionalidad Exigió:** `NormalizedFinding` pasó a ser un tipo canónico en `src/domain/finding/` (con `snippet_hash` y `rule_version`), importado por todos los puentes — sin eso, el mismo hallazgo tendría una forma por herramienta y la huella no lo reconocería.
* **Pruebas:** `tests/domain/baseline/` (5 archivos, **126 escenarios**), `tests/application/use_cases/` (5 archivos, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) y `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34).
* **Artefactos:** `docs/baseline-suppressions.*.md` en **5 idiomas**, ADR-012, `glossary-baseline.md`, spec/plan/survey e informe de tarea.

---

## **📊 Pruebas y Calidad**

| Archivo de Prueba | Escenarios | Enfoque |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame por rango de líneas en un archivo |
| `tests/test_blame_metrics.py` | 7 | Métricas de blame: profundidad, commits, duración |
| `tests/test_baseline_cli.py` | 54 🆕 | Grupo `baseline`: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Builder de changelog: secciones, encabezados traducibles, contribuidores, **enlaces y fechas** |
| `tests/test_chat_backend.py` | 19 | Memoria de chat, persistencia, comandos slash |
| `tests/test_commit_classifier.py` | 30 | Clasificación de Conventional Commits + **`pr_number_from_merge()` en los 4 forges** |
| `tests/test_config_app.py` | 78 | TUI de configuración: montaje, cambio de categoría, dirty tracking, F2 bloqueado, Ctrl+R, búsqueda, secretos |
| `tests/test_config_cli.py` | 9 | Registro del subcomando `config`, `-h`, lazy import, stdout limpio |
| `tests/test_config_schema.py` | 42 | Cobertura de `DEFAULT_CONFIG`, sin duplicados, categorías/kinds, `advanced` solo en Advanced, **sección de skills** ✅ |
| `tests/test_config_store.py` | 22 | Round-trip sobre el `.env` temporal, comentarios y orden preservados, `remove_config_value()` idempotente |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuración de revisores sugeridos (claves y defaults) |
| `tests/test_config_validation.py` | 40 | Tipos, enums, plantillas, `validate_ai_key()` con un SDK mockeado (401 vs. red vs. ollama) |
| `tests/test_core.py` | 49 | Flujos principales, git diff, generación de PR, timing, staging, coautoría, idioma de los hooks |
| `tests/test_diff_parser.py` | 26 | Parser de diff por líneas/hunks + `summarize_patch()` y `split_patch_sections()` |
| `tests/test_explain_command.py` | 2 | CLI de `explain`: éxito, clave ausente, diff vacío |
| `tests/test_external_linters.py` | 33 | Puente Checkstyle: parser XML, subprocess, intersección con el diff, informe |
| `tests/test_i18n.py` | 20 | Paridad entre idiomas, claves faltantes/huérfanas, identidad — **cero claves faltantes** ✅ |
| `tests/test_install_wizard.py` | 3 | Wizard de instalación interactivo |
| `tests/test_issue_engine.py` | 4 | Borrador de issue estructurado |
| `tests/test_linter_metrics.py` | 4 | Métricas del linter: errores, warnings, duración |
| `tests/test_linter_presets.py` | 5 | Presets del linter: resolución y re-descarga forzada |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` en la CLI y en la ayuda contextual |
| `tests/test_mcp_prompts.py` | 11 | Plantillas de prompts MCP y fallback de idioma |
| `tests/test_mcp_server.py` | 110 | Herramientas MCP (16), recursos (19), anotaciones, patching, CLI directa, offload — **concordancia del registry de skills** ✅ |
| `tests/test_mcp_server_e2e.py` | 6 | Servidor MCP real vía subprocess + JSON-RPC stdio |
| `tests/test_mentor_cli.py` | 4 🆕 | CLI de `mentor` y opt-in de `--mentor` |
| `tests/test_metrics.py` | 164 | **Ledger**: escritura síncrona, export idempotente, bundle/merge, versionado, migración, renderers, costo, calidad, módulos, proveedores, ciclo |
| `tests/test_net_timeouts.py` | 12 | Timeouts de red/IA — alineados con el predeterminado real de 180s |
| `tests/test_plugins.py` | 17 | Descubrimiento de plugins, fusión de reglas del linter, prompts MCP |
| `tests/test_policy_cli.py` | 26 🆕 | Grupo `policy`: list/show/validate/use/off/install/init, confirmación y lockfile |
| `tests/test_pr_publish_app.py` | 45 | TUI del PR Publisher: pantallas, flujos, revisores sugeridos, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal de error del linter: abortar, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save y payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release: opciones, ayuda con epílogo documentado |
| `tests/test_release_engine.py` | 53 | **Cutoff anclado en el changelog, `_drop_released()`, enlaces, mapa de PRs por merge commit**, publicación |
| `tests/test_reviewer_resolution.py` | 18 | Escalera de resolución, rechazo de nombre parcial, dedup, tolerancia a fallos |
| `tests/test_reviewer_suggestion.py` | 18 | Lógica de sugerencia de revisores (ranking, exclusión, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 🆕 | CLI de `risk`: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 61 | Matriz del ruleset integrado: regex, placeholders, nivel, comodín, fusión, traducciones |
| `tests/test_skill_command.py` | 10 | Descarga y validación de plantillas de skill |
| `tests/test_skill_context.py` | 18 | `get_skill_context()` con `quiet=True`, fallbacks, registry de skills |
| `tests/test_smart_excludes.py` | 15 | Filtro smart pathspec y re-descarga forzada |
| `tests/test_suggest_reviewers.py` | 17 | Revisores sugeridos en el flujo de PR (integración, hint `no_login`) |
| `tests/test_tests_command.py` | 2 | CLI del grupo `tests` y de `generate` |
| `tests/test_thinking_words.py` | 5 | Carga, parsing con separador `;` y recarga forzada |
| `tests/test_updater.py` | 23 | Puerta de PyPI: parsing de versión, caché diaria, fetch, decisiones de la puerta, wiring de la CLI |
| `tests/test_usage_log.py` | 27 | Log de uso: nombre derivado de la fecha, escritura síncrona, silencio, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump semántico: major/minor/patch, targets y validación |
| `tests/test_web_links.py` | 26 🆕 | Normalización de remotos (https/ssh/scp/credentials/port) y patrones de URL por forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 🆕 | Activación del pack, lockfile, rechazos |
| `tests/application/use_cases/test_baseline_gate.py` | 40 🆕 | Puerta: carga, clasificación, anotación, capas y aborto |
| `tests/application/use_cases/test_baseline_summary.py` | 12 🆕 | Vista de solo lectura del baseline para MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 🆕 | Correlación de diff/hallazgos/pruebas/historial, degradación |
| `tests/application/use_cases/test_create_baseline.py` | 30 🆕 | Creación/actualización con procedencia y preservación de decisiones |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 🆕 | Lotes, severidad, cache hit, clave ausente |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 2 | Caso de uso de explain |
| `tests/application/use_cases/test_generate_test_file.py` | 4 | Caso de uso de generación de pruebas |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 🆕 | Instalación del pack, orígenes y conflictos |
| `tests/application/use_cases/test_suppress_finding.py` | 29 🆕 | Supresión/eliminación en los cuatro ámbitos |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 🆕 | Validación: schema cerrado, checksum, compatibilidad |
| `tests/badge/test_badge_append.py` | 9 | Anexado idempotente del badge al cuerpo del PR |
| `tests/badge/test_badge_builder.py` | 19 | Composición Markdown de shields.io, escape, regla de color, estilo |
| `tests/badge/test_badge_cli.py` | 15 | Comando `gitpr badge`: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Conteo de alertas; `None` sin reglas configuradas |
| `tests/badge/test_badge_offline.py` | 6 | Guard offline (red prohibida, salvo loopback) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` en cada camino de publicación |
| `tests/badge/test_badge_paths.py` | 10 | Punto de inyección único en ambos publicadores (TUI y `--no-edit`) |
| `tests/demo/test_demo_app.py` | 22 | TUI del tour: navegación, pantallas, modal de ayuda |
| `tests/demo/test_demo_isolation.py` | 14 | El tour no toca ni `~/.gitpr` ni la red |
| `tests/demo/test_demo_runner.py` | 33 | Máquina de estados `DemoState`: avance, retroceso, completado, modo texto |
| `tests/demo/test_demo_scenarios.py` | 47 | Integridad de los escenarios grabados (aritmética de hunks) en los 5 idiomas |
| `tests/demo/test_demo_text_mode.py` | 18 | Salida en texto plano (`--no-tui`) para CI y grabaciones |
| `tests/demo/test_fake_ai_provider.py` | 21 | Contrato del proveedor falso: espeja `call_ai_model` argumento por argumento |
| `tests/domain/baseline/test_baseline_comparator.py` | 21 🆕 | Clasificación, conteos por estado, `resolved`→`new` |
| `tests/domain/baseline/test_baseline_fingerprint.py` | 12 🆕 | Digest golden, línea cruda nunca almacenada, estabilidad |
| `tests/domain/baseline/test_baseline_manifest.py` | 26 🆕 | JSON canónico, checksum, claves desconocidas, invariantes |
| `tests/domain/baseline/test_baseline_types.py` | 15 🆕 | Estados persistidos, ámbitos y precedencia |
| `tests/domain/baseline/test_suppression_policy.py` | 52 🆕 | Capas, especificidad, overrides, deuda vencida |
| `tests/domain/finding/test_finding_types.py` | 3 🆕 | Tipo canónico de hallazgo: `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Formateo uniforme y deduplicación `[Gitleaks + Regex]` |
| `tests/domain/linter/test_structured_findings.py` | 44 🆕 | `findings_out`: hallazgos estructurados y el contador de líneas del lado nuevo |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 🆕 | Builder puro: honestidad epistémica, URLs eliminadas, orden preservado |
| `tests/domain/policy/test_policy_checksum.py` | 15 🆕 | SHA-256 del pack y detección de edición manual |
| `tests/domain/policy/test_policy_compatibility.py` | 22 🆕 | Compatibilidad de versión y credenciales |
| `tests/domain/policy/test_policy_manifest.py` | 42 🆕 | Schema cerrado, bloque `baseline:`, errores de manifest |
| `tests/domain/policy/test_policy_resolver.py` | 47 🆕 | Precedencia, composición en capas, pack activo × base |
| `tests/domain/pr/test_explain_section_builder.py` | 4 | Renderizado Markdown, parsing tolerante, detección de `[FILL]` |
| `tests/domain/risk/test_risk_calculator.py` | 5 🆕 | Fórmula 50/30/20, clamp 0–100, escalado obligatorio |
| `tests/domain/risk/test_risk_rules.py` | 5 🆕 | Pesos, umbrales, glob de rutas críticas, override YAML |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 | Detección por config, manifest y directorio; override; fallo advertido |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 | Ruta convencional por framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Caso de uso completo: review → IA → validar → clasificar → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extractor compartido entre el chat y `fix` |
| `tests/fix/test_fix_cli.py` | 31 | Enrutamiento de subcomandos, opciones, dry-run predeterminado, `--force` |
| `tests/fix/test_fix_history.py` | 21 | Ledger `.gitpr/fix_history.json`: escritura atómica, lectura |
| `tests/fix/test_fix_settings.py` | 10 | Las cinco `GITPR_FIX_*` y los fallbacks para valores inválidos |
| `tests/fix/test_patch_applier.py` | 19 | Wrapper de `git apply`: check/apply/reverse, rama, estado |
| `tests/fix/test_patch_extractor.py` | 13 | Bloques cercados → unified diff validado |
| `tests/fix/test_patch_safety_classifier.py` | 22 | Matriz safe/review_required/experimental y códigos de motivo |
| `tests/fix/test_resolve_last_review.py` | 13 | Selección del último review, exclusión de los reviews por archivo |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback`: reversión y los tres rechazos |
| `tests/infrastructure/baseline/test_local_baseline_repository.py` | 24 🆕 | Único lector/escritor: escritura atómica, checksum, reparación |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 🆕 | `git log` seguro: commits, bugs y reverts en 90 días |
| `tests/infrastructure/git/test_test_matcher.py` | 5 🆕 | Casamiento prueba ↔ producción por convención |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Puente Bandit: disponibilidad, parsing, mapeo |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC: subprocess endurecido, timeout, binario ausente |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Puente Gitleaks: enmascarado de secretos, ámbito por archivo |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Puente Semgrep: parsing JSON, severidad, skip de no-Python |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 🆕 | Efecto real del baseline sobre review, lint y riesgo en un repositorio real |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 🆕 | Efecto real del pack sobre review, lint y riesgo (con un guard de red autouse) |
| `tests/review/test_diff_normalizer.py` | 20 | Saltos de línea, validación de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: procedencia, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orquestación: gates, PR, diff, linter, comentario opcional |
| `tests/review/test_review_pr_cli.py` | 25 | CLI de `review-pr`: opciones, rechazos antes de la IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 48 | Proveedor de Azure DevOps: org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 44 | Proveedor de Bitbucket: Basic auth, workspace, PRs |
| `tests/scm/test_contract.py` | 42 | Contrato de `ScmProvider`: firmas, dataclasses, errores |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim deprecado `github_api` → delega en el proveedor |
| `tests/scm/test_github_provider.py` | 72 | Proveedor de GitHub: REST, headers, PRs, issues, releases, read-back de revisores |
| `tests/scm/test_gitlab_provider.py` | 52 | Proveedor de GitLab: API v4, namespace, headers sintetizados y `overflow` |
| `tests/scm/test_init_wizard.py` | 12 | Wizard de `--init`: detección de forge, validación, persistencia |
| `tests/scm/test_release_publish.py` | 8 | Publicación de release por forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 | El único módulo que muta: staging selectivo, un commit por grupo |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unidades → grupos → pre-validación de conflictos → mensajes |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, presupuesto/recorte, llamada de IA, validación de respuesta |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()`: texto ↔ modelo, puro |
| `tests/split/test_selective_stager.py` | 11 | Staging de un subconjunto, verificación contra HEAD, índice limpio |
| `tests/split/test_split_cli.py` | 31 | CLI de split: opciones, dry run predeterminado, `--apply` |
| `tests/test_web_links.py` | 26 🆕 | (ver arriba) |
| `tests/sync_i18n.py` | — | Script de verificación de cobertura i18n (scaffold; nunca se ejecuta) |

**Total:** **2799 escenarios recolectados en 129 módulos de prueba** (49 en la raíz + 11 en `tests/application/use_cases/` + 10 en `tests/fix/` + 9 en `tests/scm/` + 7 en `tests/badge/` + 6 en `tests/demo/` + 6 en `tests/split/` + **5 en `tests/domain/baseline/`** 🆕 + **4 en `tests/domain/policy/`** 🆕 + 4 en `tests/review/` + 4 en `tests/infrastructure/linter/external/` + **2 en `tests/domain/risk/`** 🆕 + 2 en `tests/domain/tests_generation/` + **2 en `tests/integration/`** 🆕 + **2 en `tests/infrastructure/git/`** 🆕 + 2 en `tests/domain/linter/` + **1 en `tests/domain/finding/`** 🆕 + **1 en `tests/domain/mentor/`** 🆕 + 1 en `tests/domain/pr/` + **1 en `tests/infrastructure/baseline/`** 🆕; **+910 escenarios y +33 archivos** desde el informe anterior).

**Notas de calidad de esta versión:**
- **✅ Los 4 fallos de la ventana anterior se cerraron, con las aserciones intactas.** `SKILL_LABELS` pasó de 8 a **11** entradas y `mcp_server.SKILL_FILES` de 8 a **11**; las **40 claves `__()` sin traducir** se resolvieron — el código usa 1603 y los diccionarios tienen 1603, **cero faltantes**. Las tres aserciones que reportaban la deuda (`TestSkillsSection` ×2, `TestNoMissingKeys`, `TestSkillRegistryAgreement`) **siguen corriendo y ahora pasan** — verificado en esta ventana con `pytest tests/test_config_schema.py::TestSkillsSection tests/test_mcp_server.py::TestSkillRegistryAgreement tests/test_i18n.py::TestNoMissingKeys` → **11 passed**.
- **Crecimiento de 910 escenarios (+48%) en un solo ciclo**, el mayor salto jamás registrado en este proyecto. Solo `tests/test_metrics.py` pasó de 34 a **164** escenarios, y las tres funcionalidades nuevas con mayor densidad de pruebas son el baseline (≈380 escenarios en conjunto), policy (≈246) y el ledger.
- **La CI corre la suite en Python 3.10 y 3.13** desde la ventana anterior, y `fail-fast: false` garantiza que un fallo en una versión no esconda la otra.
- **Las pruebas de integración entraron al repertorio:** `tests/integration/` es nuevo y prueba **efecto real** — un pack activo y un baseline activo cambian el review, el lint y el riesgo en un repositorio real, con un guard autouse que bloquea la red fuera del loopback.
- **`tests/conftest.py` sigue hermético:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` y `LANG_VERSION` en la versión del código.
- **Ejecución completa en esta máquina: `2 failed, 2795 passed, 2 skipped, 138 subtests passed in 520.46s (8min40s)`.** La suite se corrió finalmente de punta a punta en esta ventana y el resultado es honesto: **2795 de los 2799 escenarios recolectados pasan**. Los **dos fallos no son una regresión del producto** — son un bug de aislamiento en `tests/test_mentor_cli.py`, y la causa raíz fue identificada y confirmada por experimento:
  - `TestMentorCli` hace `@patch("src.core.get_git_diff")`, pero `src/main.py` importa el nombre **por valor** (`from src.core import … get_git_diff`), así que el patch nunca llega al binding que el flujo realmente usa: la función **real** corre. El `get_git_diff()` real ejecuta `git diff -U1 -w -M -B HEAD -- <59 smart excludes>`, y como el único cambio rastreado en el working tree durante la ejecución era `CHANGELOG.md` — filtrado por `:(exclude)*.md` —, el diff vuelve con **0 bytes**, el flujo cae en el early return de `main.py:1444` (`⚠️ Nenhum código novo encontrado`) con **código de salida 0**, y la aserción `render_review_result.assert_called_once()` falla porque el renderer nunca fue llamado.
  - **La prueba:** repitiendo los dos escenarios con el patch apuntado a `src.main.get_git_diff` (el binding correcto), ambos se comportan exactamente como esperan las aserciones — `--mentor` llama a `_append_mentor_section` **y** al renderer; sin `--mentor` el append **no** se llama y el renderer sí. En otras palabras: **las dos pruebas dependen del entorno**, pasando mientras el working tree tenga algún cambio que no sea `.md` (lo que fue cierto durante todo el desarrollo de las funcionalidades) y fallando en un árbol solo de documentación.
  - El código de producción es correcto — el camino `-r` llega a `render_review_result` (`main.py:1593`) como siempre. **Fix recomendado:** cambiar el target del patch a `src.main.get_git_diff` en ambos escenarios (o parchear `src.core` y el binding de main juntos).
- **Ninguno de los 4 fallos de la ventana anterior permanece:** eran de otra naturaleza (registry de skills y claves `__()` sin traducir) y las aserciones que los caracterizaban siguen corriendo y pasando.
- **Guard de red en dos lugares:** `tests/demo/conftest.py` (el tour no puede envenenar una caché real) y `tests/integration/conftest.py` (nuevo).
- **Fixtures de git reales:** `tests/fix/git_fixture.py` y `tests/split/git_fixture.py` siguen siendo el estándar para lo que solo es honesto con un `git apply` real.

---

## **🌐 Internacionalización y Documentación**

* **Cobertura i18n:** **1603 claves de traducción** en los 6 diccionarios, con **paridad total de key sets** — y, por primera vez en varias ventanas, **también paridad con el código**: el código usa 1603 claves y ninguna queda sin traducir. ✅ (+445 en esta ventana.) La cadena medida por commit fue 1158 → 1161 (release notes, +3) → 1258 (ledger, +97) → 1288 (mentor, +30) → 1308 (docs de risk, +20) → 1437 (policy, +129) → **1603** (baseline, +166).
* **`__lang_version__` subió v0.0.31 → v0.0.32 → v0.0.33 → v0.0.34**, disparando la re-descarga OTA de las traducciones — y esta vez el bump está **commiteado** (`af35270`), no en el working tree.
* **Fuentes de traducción en lockstep:** una clave nueva debe existir en el código (fuente), en `langs/pt_br.json` (**lista maestra**), en los diccionarios FR/ES de `scripts/sync_all_langs.py` y en los valores curados de `scripts/fix_mangled_i18n_keys.py`; la aserción `len(CLEAN_KEYS)` sigue en 49.
* **Temas nuevos 🆕 (4, todos en 5 idiomas):**
  - `docs/mentor-mode.md` — Mentor Mode: qué enseña, las tres garantías de honestidad epistémica, los lotes de 10 y el opt-in.
  - `docs/risk-scoring.md` — la puntuación determinista: las señales locales, la fórmula 50/30/20, las reglas de escalado y el `gitpr.risk.yml`.
  - `docs/policy-packs.md` — los packs versionados: la escalera de precedencia, el lockfile que el equipo commitea y los tres fallos que abortan en lugar de degradar en silencio.
  - `docs/baseline-suppressions.md` — el baseline: los cinco estados, por qué solo `new` bloquea, qué hashea la huella y qué deja fuera deliberadamente, los cuatro ámbitos de supresión y el bloque `baseline:` de un pack.
* **Temas traducidos en esta ventana (deuda saldada) 🆕:** `docs/split-command.*.md` completó los 5 idiomas (+3: pt_pt, es_es, fr_fr) y `docs/otimizacao-de-tokens.*.md` también. **`review-pr` sigue con 2 idiomas** — es la última familia de funcionalidades parcialmente traducida.
* **Temas actualizados en esta ventana:** `docs/release-notes.*` (5 — reescrito para el cutoff anclado en el changelog y los enlaces por forge), `docs/metricas-telemetria.*` (5 — reescrito para el ledger SQLite, con el vocabulario canónico de `glossary-metrics-telemetry.md`), `docs/mcp-integration.*` (5 — las nuevas herramientas y recursos), `docs/mcp-annotations.*` (5), `docs/ARCHITECTURE.*` (5) y `README.*` (5 — el índice ganó las familias `mentor-mode`, `risk-scoring`, `policy-packs` y `baseline-suppressions`).
* **Subdirectorio `docs/extra/` 🆕:** dos familias solo en PT (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) fueron **movidas** de la parte superior de `docs/` a `docs/extra/` (`R100`, sin cambio de contenido) — el material de apoyo dejó de competir en el conteo de temas canónicos.
* **Documentación en 5 idiomas:** **46 temas canónicos** en `docs/` (+4 nuevos − 2 movidos) — **43 con cobertura completa en los 5 idiomas** 🆕 y solo **3 temas parciales** (`github-issue-prompt-com-gh`, `version-markers`, `review-pr`), contra 7 en la ventana anterior. ✅
* **Gap de documentación que persiste 🆕:** `explain` y `tests` siguen siendo las **únicas** funcionalidades sin tema en `docs/` — ganaron la entrada en el registry de skills y las claves, pero no la página. Las cuatro funcionalidades de esta ventana (mentor, risk, policy, baseline) nacieron con docs en 5 idiomas, lo que hace las dos excepciones aún más visibles.
* **Skills locales de Claude Code:** `.claude/skills/` con **29 skills** (conteo sin cambios en esta ventana).
* **Índice de memoria:** `.claude/memory/MEMORY.md` con **40 patrones** (conteo sin cambios en esta ventana).
* **Informes de tarea:** `docs/claude-code/reports/develop_natan/` (**105** en total; **+4** — `2026-09-23_release_dedup_links.md`, `2026-09-29_metrics_telemetry_v2.md`, `2026-10-09_policy_packs.md`, `2026-10-10_baseline.md`) y `docs/gemini/reports/develop_natan/` (**14**; **+7**).
* **Informes de estado:** `docs/reports/` (16 informes; este es el **17º**).
* **Planes de desarrollo:** 135 archivos en `docs/plans/` (+20 en la ventana — specs/planes de release, metrics, risk, policy y baseline; **ADR-008 a ADR-012**; los glosarios `metrics-telemetry`, `gitpr-mentor`, `gitpr-risk-scoring`, `policy-packs` y `baseline`; y el nuevo subdirectorio `docs/plans/develop_natan/`) + **14 archivos en `docs/survey/`** (+4).

---

## **🔄 Pipeline de Distribución**

1. **PyPI (canal único):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Actualización obligatoria:** la ejecución consulta PyPI al iniciar y **bloquea con código de salida 1** si hay una versión más nueva, imprimiendo `pip install --upgrade gitpr-cli`; la verificación se cachea por día y `--update` solo informa
3. **GitHub Releases:** eliminado — sin PyInstaller, sin asset `.exe`, sin hot-swap
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (instala vía pip) + `tests.yml` (matriz Python 3.10 y 3.13). **Aún falta el workflow de release** — el build y el upload a PyPI siguen siendo manuales.
5. **Servidor MCP:** entry point `gitpr-mcp` vía `pyproject.toml`
6. **Plantillas e idiomas OTA:** `templates/` y `langs/*.json` servidos desde GitHub (main) — el bump `v0.0.34` renueva las copias locales de `~/.gitpr/langs/` al publicarse; `__scripts_version__` **v0.0.4** renueva los hooks (reinstalar con `gitpr --installhooks`)
7. **Versión derivada del código:** `pyproject.toml` usa `version = {attr = "src.updater.__version__"}` — `__version__` es la **única** fuente
8. **`package-data` ahora importa 🆕:** los cuatro packs embebidos (`src/policy_packs/*`) viajan en el wheel; un paso de build que los excluya produce un `gitpr policy list` que no ve los packs de fábrica
9. **Estado de los releases 🆕:** **`v1.3.0` taggeado** (2026-09-24, merge del PR #193) y **`v1.4.0` taggeado** (2026-10-12, merge del PR #207). El `CHANGELOG.md` tiene ambas secciones completas y enlazadas — `[1.4.0]` es la primera generada por el motor corregido. El único cambio abierto en el working tree es el fix de los encabezados localizados de la sección `[1.4.0]`, que salieron en pt-BR (`Funcionalidades`, `Documentação`, `Tarefas`, `Contribuidores`) y se normalizaron al inglés.

---

## **📈 Evolución desde el Informe Anterior (v0.0.16)**

| Área | v0.0.16 (anterior) | v0.0.17 (actual) |
|------|-------------------|-----------------|
| **Versión de GitPR** | 1.3.0 (bump **sin commitear**; HEAD en 1.2.0) — tag `v1.2.0` | **1.4.0** — **tags `v1.3.0` (24/09) y `v1.4.0` (12/10) creados** ✅ |
| **Versión de idioma** | v0.0.32 (bump **sin commitear**) | **v0.0.34** (v0.0.32 → v0.0.33 → v0.0.34, **commiteado**) |
| **Versión de los scripts de hook** | v0.0.3 | **v0.0.4** (plantillas de hook reescritas para el ledger) |
| **Proveedores de IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama (sin cambios) |
| **Idiomas** | 5 idiomas, 6 diccionarios | 5 idiomas, 6 diccionarios |
| **Subcomandos** | 9 | **14** (+ `mentor`, `metrics`, `policy`, `risk`, `baseline`) — **4 de ellos son grupos** (baseline 6, metrics 8, policy 7, tests 1) |
| **Interfaz** | CLI + TUIs + wizards + 9 subcomandos | **+ `gitpr mentor`/`--mentor` + grupo `metrics` (con asistente de migración) + `gitpr policy` + `gitpr risk` + `gitpr baseline`** |
| **Capas** | `src/domain/` y `src/application/use_cases/` | **8 paquetes de dominio** (+ `baseline`, `finding`, `mentor`, `policy`, `risk`) y **14 casos de uso** |
| **Persistencia local 🆕** | — | **`src/ledger.py`** — SQLite en `~/.gitpr/metrics/telemetry.db` (WAL, `user_version`, UUID, bundle/merge) |
| **Herramientas MCP** | 14 herramientas / 18 recursos / 7 prompts (`skill://explain` y `skill://tests` **ausentes** ⚠️) | **16 herramientas / 19 recursos / 7 prompts** ✅ (+`get_usage_metrics`, +`get_baseline_status`, +`baseline://summary`; registries de skills alineados) |
| **Flags de CLI** | 36 en la raíz + 9 subcomandos | **32 en la raíz** (−5 metrics, +`--mentor`) + **14 subcomandos** con 22 subcomandos de grupo |
| **Variables de entorno** | 49 claves en `DEFAULT_CONFIG` | **58 claves** (+9: 4 de baseline, 2 de mentor, 2 de policy, 1 de risk) + `GITPR_METRICS_PRICE_*`/`CURRENCY` |
| **Esquema de configuración** | 71 `ConfigField` / 14 categorías | **80 `ConfigField` (+9) / 16 categorías** (+ Policy, + Baseline) |
| **Linter** | Regex + secretos integrados + comodín + 3 puentes SAST | **+ hallazgos estructurados (`findings_out`) + tipo canónico `src/domain/finding/` + severidad del Policy Pack + código de salida consciente del baseline** |
| **Git Hooks** | pre-commit bloquea secretos | **+ post-checkout/post-merge/pre-push llaman a `gitpr --quiet metrics hook-event`** (resolución de repositorio en bash, solo-GitHub, eliminada) |
| **Capa SCM** | 4 forges, `get_pull_request`, `supports_reviewable_diff` | **+ `list_pull_requests` paginado y canónico, `supports_merged_dates`, `within_window()`, `web_links.py` (enlaces por forge)** — 305 escenarios |
| **Motor de release** | Rango por `git describe`, sin enlaces | **Rango anclado en la sección anterior del changelog, `_drop_released()`, enlaces de commit/PR/contribuidor, mapa de PRs por merge commit** |
| **i18n (claves por archivo)** | 1158 × 6 — el código usa 1198 → **40 sin traducir** ⚠️ | **1603 × 6 — el código usa 1603 → cero sin traducir** ✅ (+445) |
| **Documentación** | 44 temas (37 completos + 7 parciales) | **46 temas (43 completos + 3 parciales)** — 4 familias nuevas en 5 idiomas + 2 deudas de traducción saldadas + `docs/extra/` |
| **CI** | `.github/workflows/tests.yml` (3.10 + 3.13) | Sin cambios (el workflow de **release** sigue pendiente) |
| **Suite de pruebas** | 1889 escenarios (96 archivos) | **2799 escenarios (129 archivos) — +910 escenarios, +33 archivos** (el mayor salto jamás registrado) |
| **Fallos conocidos** | 4 (registry de skills a medias, una causa raíz) | **2** — los 4 de la deuda **cerrados** ✅; quedan 2 en `tests/test_mentor_cli.py`, de **aislamiento de pruebas** (patch en `src.core` que no llega al binding de `src.main`), sin regresión del producto |
| **Dependencias** | — | **+ `packaging>=23.0`** (SemVer/PEP 440 para el Policy Pack) |
| **Commits desde el informe** | 8 commits | **11 commits** (`c561664`, `64a406f`, `4380ce8`, `811651f`, `57c5140`, `c46440f`, `e1cfb11`, `4d42d56`, `8450186`, `af35270`, `1abb1b5`) |
| **PRs fusionados** | 7 PRs (#177–#189) | **10 PRs (#192, #193, #194, #196, #198, #200, #201, #203, #205, #207)**; 6 issues cerrados (#191, #197, #199, #202, #204, #206) |
| **Índice de memoria** | 41 patrones | **40 patrones** (sin cambios en esta ventana) |
| **Informes de tarea** | 101 claude-code, 7 gemini | **105 claude-code (+4) y 14 gemini (+7)** |
| **Planes de desarrollo** | 115 planes, 10 surveys | **135 planes (+20), 14 surveys (+4)** — incluye ADR-008 a ADR-012 |

---

## **🚧 Próximos Pasos**

* **Corregir el aislamiento de `tests/test_mentor_cli.py` 🆕:** son los **únicos dos fallos** de la suite hoy. El patch apunta a `src.core.get_git_diff`, pero `src/main.py` importa el nombre **por valor** — la función real corre, y en un árbol cuyo único cambio es un `.md` (filtrado por `:(exclude)*.md` en los smart excludes) el diff vuelve vacío y el flujo retorna antes del renderer. Repetido con el patch en `src.main.get_git_diff`, ambos escenarios pasan. Es un fix de dos líneas que cierra la suite en **2797/2799**.
* **Commitear el fix del `CHANGELOG.md` 🆕:** los encabezados de sección de `[1.4.0]` salieron localizados (pt-BR) y se normalizaron al inglés **en el working tree**. Es el único cambio abierto del repositorio — y el paquete publicado ya lleva la versión en portugués en el archivo versionado.
* **Documentar `explain` y `tests`:** son, hoy, las **únicas dos** funcionalidades sin tema en `docs/` — las cuatro que llegaron en esta ventana nacieron documentadas en 5 idiomas. **Ítem heredado de la ventana anterior y aún abierto.**
* **Automatizar el release en GitHub Actions 🆕:** la CI de pruebas existe desde hace dos ventanas, pero el build y el upload a PyPI siguen siendo manuales — y en esta ventana el paso manual se hizo **dos veces** (v1.3.0 y v1.4.0). Un workflow de release disparado por tags cierra el último tramo manual del pipeline.
* **Ampliar la cobertura del ruleset de secretos:** los tres gaps siguen abiertos y fueron **reconfirmados en el código en esta ventana** — asignación sin comillas (`API_KEY=abc123`, el formato `.env`, que es justo donde se filtran los secretos), los prefijos `ASIA…`, `github_pat_…` y `xoxc-`/`xoxd-`, y la ausencia de límite izquierdo en el nombre de la clave (hoy `mytoken` casa igual que `token`).
* **Traducir `docs/review-pr.*.md` 🆕:** es la **última** familia con cobertura parcial (2 de 5 idiomas). `split-command` y `otimizacao-de-tokens` se saldaron en esta ventana.
* **Desrastrear los artefactos de `.gitpr/metrics/export/` 🆕:** `.gitpr/metrics/` entró en el `.gitignore` en esta ventana — pero los **35 archivos ya rastreados siguen rastreados**, porque `gitignore` no desrastrea. Falta un único `git rm --cached`. **La deuda dejó de crecer, pero no se saldó.**
* **Documentar el break de contrato de `request_pull_request_reviewers`:** pasó de `None` a `list[str]`; las subclases de terceros de `ScmProvider` necesitan actualizarse. `docs/scm-multiforge.*.md` es el lugar, en las 5 versiones. **Ítem heredado, aún abierto.**
* **Mapear el radio de impacto del baseline en el código de salida 🆕:** un baseline que no puede aplicarse ahora **aborta** lint, review, full review, auditoría de archivo, review remoto y riesgo con código de salida 1. Es la opción segura, pero es un cambio de comportamiento en **seis** flujos — merece un `docs/baseline-suppressions` que explicite el contrato de salida para pipelines de CI.
* **Reconciliar la versión del proyecto:** `CLAUDE.md` todavía dice `Current version: 0.0.37` mientras `__version__` está en **1.4.0**. Definir una única convención. **Ítem heredado, aún abierto.**
* **`gitpr -h config` ignora `-h`:** el subcomando abre la TUI en lugar de mostrar la ayuda — la puerta `if ctx.invoked_subcommand is not None: return` corre antes del bloque de `help_flag`. Corregirlo cambiaría el comportamiento de `-h` para **todos** los subcomandos, así que necesita una decisión. **Ítem heredado, aún abierto.**
* **Migrar el extractor de `sync_i18n.py` a AST:** la regex trunca literales con concatenación implícita (`__("a " "b")`). **Progreso parcial 🆕:** `tests/test_i18n.py` ya usa un extractor basado en AST (`_extract_keys_ast`) y es el que verifica la cobertura hoy; el script de sincronización sigue en regex.
* **Sección Smart Exclude en la TUI:** de los 12 ítems reportados tras usar la pantalla, el ítem 10 (*Smart Exclude*) es la única entrega aún no iniciada — y los campos `GITPR_SMART_EXCLUDES_*` siguen bajo `diff`, sin categoría propia.
* **Deudas registradas en el plan de la TUI de configuración:** `DEFAULT_CONFIG` quedó redundante con el schema; el banner de apertura no lista `--init`, `--base` ni `--plugins` (el `--dashboard` salió de la CLI, así que esa parte del ítem quedó obsoleta 🆕); `LinterApp` es la **única** app de Textual que todavía no desactiva la command palette.
* **Proveedor Anthropic Claude:** Soporte directo para la API de Claude (`claude-sonnet-5`).
* **Gráficos ASCII/Textual en el Dashboard:** Histogramas de tiempo y tendencias de tokens en la TUI de métricas — el dashboard ya lee el ledger, que era el prerrequisito faltante.
* **Seed para el `.gitpr/conf/` local:** El sembrado de plantillas de configuración local (smart-excludes, linter) sigue pendiente como subcomando propio o paso de wizard.
* **Más proveedores:** OpenAI directo, proveedores locales adicionales.

### ✅ Completados en esta ventana (2026-09-23 → 2026-10-12)

* ~~**Cerrar la deuda de las dos funcionalidades nuevas**~~ — `SKILL_LABELS` (8 → **11**) y `mcp_server.SKILL_FILES` (8 → **11**) recibieron `tests`, `explain` y `mentor`; las **40 claves `__()` sin traducir** se resolvieron y la cobertura llegó a 1603/1603. **Los 4 fallos se volvieron 0, con las aserciones intactas.**
* ~~**Cerrar el release 1.3.0**~~ — tag **`v1.3.0`** creado (merge del PR #193, 2026-09-24) y `CHANGELOG.md` completado con las seis funcionalidades que faltaban. **Era el ítem que bloqueaba dos ventanas.**
* ~~**Cerrar el release 1.4.0**~~ 🆕 — tag **`v1.4.0`** creado (merge del PR #207, 2026-10-12), con los tres bumps (GitPR, idiomas, scripts) **commiteados** y ya no en el working tree.
* ~~**`gitpr release` repite el historial y no tiene enlaces**~~ 🆕 — cutoff anclado en la sección anterior del changelog, `_drop_released()`, enlaces de commit/PR/contribuidor, mapa de PRs por merge commit y `web_links.py` (PR #192).
* ~~**Telemetría en JSON con `tokens_actual` siempre en cero**~~ 🆕 — ledger SQLite con escritura síncrona, tokens copiados en el momento de la escritura, grupo `gitpr metrics`, asistente de migración y costo por modelo (PRs #194/#196).
* ~~**Mentor Mode**~~ 🆕 — `src/domain/mentor/`, caso de uso, `gitpr mentor` + `--mentor`, skill en 5 idiomas (PR #198).
* ~~**Puntuación de riesgo determinista**~~ 🆕 — `src/domain/risk/`, `calculate_risk`, lectores de historial y de pruebas, `gitpr risk` y sección anexada al review (PR #200).
* ~~**Policy Packs**~~ 🆕 — `src/domain/policy/`, `src/infrastructure/policy/`, 4 packs embebidos, grupo `gitpr policy` y dependencia `packaging` (PR #203).
* ~~**Baseline y supresiones auditables**~~ 🆕 — `src/domain/baseline/`, `src/domain/finding/` canónico, puerta en los seis flujos, tool y recurso MCP (PR #205).
* ~~**Traducir los temas parciales**~~ — `docs/split-command.*.md` y `docs/otimizacao-de-tokens.*.md` completaron los 5 idiomas.
* ~~**`.gitpr/metrics/` fuera del control de versiones**~~ — entró en el `.gitignore` con un comentario que explica qué **no** se ignora (`.gitpr/skill/`) y por qué.
* ~~**Higiene de `docs/`**~~ — dos familias de material de apoyo (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) se movieron a `docs/extra/`, sacándolas del conteo de temas canónicos.
* ~~**Hooks que solo funcionaban en GitHub**~~ — `post-checkout`, `post-merge` y `pre-push` ahora delegan en el propio GitPR, con `__scripts_version__` en v0.0.4.

---

**Informe generado el:** 2026-10-12  
**Rama:** `develop_natan`  
**Autor:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
