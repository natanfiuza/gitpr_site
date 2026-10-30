# GitPR 1.4.4 — Novedades

## Novedades de esta versión

- **`gitpr -c` se convirtió en una TUI de 4 etapas:** hacer el commit dejó de ser un pipeline silencioso. `CommitApp` abre el **Selector de Stage** para los archivos fuera del índice, genera el mensaje en un hilo de fondo sobre el **diff preparado**, muestra el **modal de revisión/edición** y ejecuta el commit en un modal animado de progreso — puedes elegir archivos, editar el texto o cancelar antes de que algo llegue al repositorio.
- **El mensaje ahora describe lo que realmente quedó en stage:** `get_git_staged_diff()` ejecuta `git diff --cached` con las mismas protecciones del diff normal (`-U1 -w -M -B`, smart excludes, UTF-8), y solo cae al diff del árbol de trabajo cuando el índice está vacío. Lo que no está en stage no entra en el prompt.
- **El modal de commit se cierra solo con éxito:** `mark_finished(False)` + `set_timer(0.6, self.exit)` devuelven la terminal, y la CLI imprime `committed`/`failed` con la salida de git. En caso de error, el botón **Close** sigue ahí, porque es donde el log del fallo queda legible.
- **1.4.3 y 1.4.4 están ambas en PyPI:** el arreglo de la puerta de actualización — caché deslizante de 1 hora, comparación PEP 440, la puerta alcanzando subcomandos y `GITPR_SKIP_UPDATE_CHECK` como booleano estricto — entró en la 1.4.3 y se publicó en la misma ventana. `pip install --upgrade gitpr-cli` instala la 1.4.4.
- **Ruptura declarada:** fuera de `--hook`, `gitpr -c` **siempre** abre la TUI — no hay verificación de TTY en el enrutamiento. Los scripts y jobs de CI que dependían del commit silencioso tienen que migrar a `--hook`.
- **La deuda de i18n creció, no se pagó:** los diccionarios siguen en **1620 claves** (×4) y **1618** (`es.json`, `fr.json`), pero el código ya usa **1629** — las 3 cadenas nuevas de la TUI de commit entraron sin traducción. Son **11 claves sin diccionario** y **2 huérfanas**, y `__lang_version__` subió a **v0.0.36** sin que cambiara ningún archivo de idioma.

## Cómo usar

Actualiza desde PyPI:

```
pip install --upgrade gitpr-cli
```

Ejecuta `gitpr -c` (o `gitpr --commit`) en cualquier repositorio. Si todo ya está en stage, el Selector de Stage se salta; si no, lista los archivos fuera del índice con **Select All**, **Deselect All**, **Stage Selected**, **Skip Staging** y **Cancel**. Revisa el mensaje generado, edítalo si quieres — el texto editado es el que commitea git — y confirma. El modal de progreso se cierra solo cuando el commit sale bien.

Para automatización, `--hook` conserva el camino silencioso y merge-aware de antes, así que úsalo en hooks de commit y CI. `GITPR_SKIP_UNSTAGED_CHECK=true` (o `--no-unstaged-check`) salta solo el Selector de Stage, y `GITPR_AUTO_STAGE=true` prepara todo y sigue adelante.

## Consejos útiles

¿Una corrección de bug, un refactor y un ajuste de config en el mismo `git diff`? `gitpr split` agrupa los hunks por intención y propone un commit atómico por concern, cada uno con un mensaje escrito solo para ese subconjunto. Leer es lo predeterminado — imprime el plan y pregunta; `--dry-run` imprime y para, `--apply` hace stage y commits grupo a grupo, y `--max-groups 3` limita el plan. Los archivos no rastreados se nombran en un aviso, nunca se descartan en silencio.
