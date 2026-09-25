# GitPR 1.3.0 — Novedades

## Novedades de esta versión

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

## Cómo usar

Actualiza desde PyPI:

```
pip install --upgrade gitpr-cli
```

Pruébalo antes de configurar nada — el recorrido no necesita clave de API, ni repositorio git, ni red:

```
gitpr demo                             # recorrido guiado por un mensaje de commit, un review y un PR
gitpr demo --lang=es_es                # el recorrido en tu idioma
gitpr demo --no-tui                    # texto plano, para CI y grabaciones
```

Los subcomandos nuevos — todos son de solo lectura hasta que pases la opción que escribe:

```
gitpr badge                            # imprime el fragmento del badge para tu README (no escribe nada)
gitpr split                            # el plan: un commit atómico por asunto (no escribe nada)
gitpr split --apply                    # commitea cada grupo; el árbol termina byte a byte idéntico
gitpr tests generate                   # un archivo de prueba en la convención de tu repositorio (no escribe nada)
gitpr tests generate --file src/core.py --apply
gitpr explain                          # una guía para quien va a revisar el diff actual
gitpr --explain                        # anexa esa guía a la descripción del PR generada
```

Un `gitpr split` sin flags pregunta antes de commitear nada — y `--apply` resetea el índice a HEAD, así que lo que ya tenías en staging hay que prepararlo de nuevo (el contenido de los archivos nunca se toca). `gitpr tests generate` no sobrescribe nada sin `--apply` y, cuando lo hace, la confirmación se abre con **No** preseleccionado.

El escaneo de secretos ahora corre en **cada** invocación del linter, con reglas que viven dentro del paquete y no pueden ser reemplazadas por `--skill` ni reescritas por el wizard — así que un commit que antes pasaba ahora puede quedar bloqueado. `GITPR_LINTER_SECURITY=false` es la salida de emergencia. Los puentes Semgrep/Gitleaks/Bandit siguen siendo estrictamente opt-in (`GITPR_SAST_*_ENABLED`), y una herramienta habilitada pero ausente del `PATH` produce un warning, no un fallo.

## Consejos útiles

El linter es gratis: sin API keys, sin llamadas de IA, solo las líneas añadidas de tu diff. Exit code 0 = pasa, 1 = violaciones, así que es ideal como quality gate en GitHub Actions que bloquea secretos y código de debug antes de la revisión humana — la documentación incluye el workflow completo.
