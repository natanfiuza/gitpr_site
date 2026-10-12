# GitPR 1.4.0 — Novedades

## Novedades de esta versión

- **El release 1.3.0 quedó cerrado y el 1.4.0 ya está etiquetado:** el tag `v1.3.0` (merge del PR #193, 2026-09-24) cerró el ítem que llevaba dos ventanas bloqueando, y el `CHANGELOG.md` se completó con las seis funcionalidades que faltaban. Diez días después, el tag `v1.4.0` (merge del PR #207, 2026-10-12) cerró esta ventana entera — las cinco funcionalidades de abajo **más** la nota de release de 1.3.0 entraron en un solo ciclo.
- **`gitpr release` dejó de repetir la historia:** las release notes listaban, para cada versión, todos los commits de todas las versiones anteriores (la sección de 1.3.0 salió con 124 viñetas, 115 de ellas repetición de 1.2.0). La causa no era la IA — era el rango de `git log`, anclado en `git describe`, que en este repositorio resolvía a `v0.0.8`. El corte pasó a ser la **sección anterior del propio changelog**, cada viñeta ganó un enlace **real y clicable** al commit, al PR y al contribuidor, y un commit ya presente en otra sección se descarta antes de convertirse en viñeta — cuando no queda nada, el release **aborta** en lugar de publicar una sección vacía.
- **La telemetría salió del archivo JSON y pasó a ser un ledger SQLite:** los eventos se escribían en archivos por comando, desde un hilo daemon, y después se "enriquecían" de vuelta a través de la caché de IA mediante un match heurístico por (repo, rama, acción, minuto) que dependía de tres mapas de carpetas divergentes. El resultado era predecible: `tokens_actual` **siempre en cero** y pérdida de datos entre repositorios al exportar. `~/.gitpr/metrics/telemetry.db` escribe la fila **de forma síncrona**, con los tokens copiados de la metadata del proveedor en el momento de la escritura, y un proceso de vida corta (un hook, por ejemplo) ya no pierde su propia fila al salir.
- **Mentor Mode — el review que enseña, no solo señala:** un review dice *qué* está mal y casi nunca *por qué importa*. `gitpr mentor` (y la flag `--mentor` en `-r`/`-f`) enriquece los hallazgos del último review con qué está pasando, por qué importa, una analogía cotidiana y los conceptos que estudiar. Es **opt-in** y nunca inventa: con evidencia insuficiente, la explicación lo dice en lugar de rellenar el vacío.
- **Puntuación de riesgo determinista — sin IA, sin cuota:** `gitpr risk` produce una puntuación de 0–100 con un nivel `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` a partir de señales locales (rutas críticas, pruebas ausentes, migraciones, historial de bugs y reverts, hallazgos estáticos). La fórmula está declarada (**50/30/20**), igual que las reglas de escalado obligatorias, y la puntuación entra en el prompt de review como contexto **de solo lectura** y en el informe. Un número explicable vale más que una suposición cara.
- **Policy Packs — la política del repositorio pasó a ser un artefacto versionado:** los skills, las reglas de linter, las severidades, las rutas críticas, los pesos de riesgo y las convenciones de PR/commit estaban dispersos y cada repositorio los configuraba a mano. `gitpr policy` adopta un pack YAML versionado con un **checksum**, resuelve la precedencia entre capas y registra la elección en el lockfile que el equipo commitea. Cuatro packs integrados viajan en el wheel; sin un pack activo, **nada cambia**.
- **Baseline y supresiones auditables — el ruido conocido deja de bloquear:** los linters y los reviews volvían a reportar los mismos hallazgos preexistentes en cada ejecución, y distinguirlos de cabeza era tarea de quien revisaba. `.gitpr/baseline.json` registra el snapshot de los hallazgos aceptados, cada hallazgo nuevo se clasifica como `new`/`existing`/`resolved`/`ignored`/`accepted_debt`, y **solo `new` bloquea**. Cada supresión lleva un motivo; la deuda aceptada lleva un responsable y, cuando existe, una fecha límite.
- **El modelo de hallazgo pasó a ser un tipo canónico:** `NormalizedFinding` se movió de `infrastructure/linter/external/base_bridge.py` a `src/domain/finding/`. Sin eso, cada puente — Semgrep, Gitleaks, Bandit, el ruleset de regex — calcularía la huella de una forma distinta del mismo hallazgo y el baseline no tendría forma de reconocerlo.
- **La deuda de la ventana anterior quedó cerrada:** `SKILL_LABELS` y `mcp_server.SKILL_FILES` recibieron `tests`, `explain` y ahora `mentor`; las **40 claves `__()`** que no existían en ningún diccionario se tradujeron; y los **4 fallos** de la ventana anterior ya no existen. Las tres aserciones que reportaban la deuda siguen corriendo — simplemente dejaron de reportarla. La suite, medida de punta a punta en esta ventana, cierra en **2795 aprobadas de 2799** — los dos fallos restantes son un bug de aislamiento en `tests/test_mentor_cli.py`, no una regresión del producto.
- **La cobertura de i18n cerró el ciclo:** **1603 claves** en cada uno de los 6 diccionarios (+445 en esta ventana) y **cero claves usadas en el código sin traducción** — el código usa 1603, los diccionarios tienen 1603.
- **Las traducciones pendientes se saldaron:** `split-command` y `otimizacao-de-tokens` completaron los 5 idiomas; `review-pr` sigue con 2. Dos familias solo-PT (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) se movieron de la raíz de `docs/` a `docs/extra/`.

## Cómo usar

Actualiza desde PyPI:

```
pip install --upgrade gitpr-cli
```

Cuatro comandos nuevos — los de solo lectura no escriben nada hasta que pases la opción que escribe:

```
gitpr risk                                 # puntuación de 0 a 100 para el diff actual (sin IA, sin cuota)
gitpr risk --file src/auth.py              # los factores detrás de la puntuación de un archivo
gitpr risk --format json                   # salida estructurada para CI
gitpr mentor                               # una explicación didáctica de los hallazgos del último review
gitpr -r --mentor                          # añade la sección del mentor al review de código
gitpr policy list                          # los packs de esta máquina y el que está en vigor
gitpr policy init --stack laravel          # detecta el stack y activa el pack correspondiente
gitpr policy use acme/team-policy@1.0.0    # fija un pack, escribiendo .gitpr/policy.lock.yml
gitpr baseline create                      # registra el diff actual como baseline aceptado
gitpr baseline show                        # los hallazgos, las decisiones y los recuentos
gitpr baseline suppress <id> --reason "..." # una decisión sobre un hallazgo
```

Nada cambia hasta que lo actives. Sin un `.gitpr/baseline.json` en el repositorio, cada ejecución se comporta exactamente como antes — la puerta solo existe una vez que creas el archivo y, a partir de ahí, **solo bloquean los hallazgos `new`**: `existing`, `resolved`, `ignored` y `accepted_debt` no. Toda supresión lleva un motivo; la deuda aceptada tiene un responsable y, opcionalmente, una fecha límite. Lo mismo vale para la policy: sin un pack activo, tu configuración queda intacta.

**Ruptura de CLI declarada:** `--metrics`, `--export`, `--purge`, `--hook-event` y `--dashboard` salieron de la raíz — pasaron a ser el grupo `gitpr metrics` (`gitpr metrics dashboard`, `gitpr metrics export`, `gitpr metrics prune`). La telemetría dejó los archivos JSON por comando y ahora es un ledger SQLite en `~/.gitpr/metrics/telemetry.db`; `gitpr metrics migrate` importa los archivos antiguos y los mueve a `~/.gitpr/metrics_legacy/`.

`gitpr risk` nunca llama a la IA — es una fórmula declarada (50/30/20) sobre señales locales, así que el mismo repositorio da siempre el mismo número. `gitpr mentor` es opt-in y, cuando la evidencia no basta, lo dice en vez de inventar una explicación.

## Consejos útiles

GitPR ignora a propósito los archivos no rastreados (lee `git diff HEAD`), y eso es una característica: un `.env.local` olvidado con contraseñas de producción nunca se envía a la IA, y las carpetas de caché gigantes nunca gastan tokens. Para incluir un archivo nuevo, basta con hacer `git add` antes.
