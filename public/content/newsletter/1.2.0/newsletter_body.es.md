# GitPR 1.2.0 — Novedades

## Novedades de esta versión

- **Subcomando `gitpr fix` — el review que se convierte en patch aplicable:** El último review de la caché alimenta **una** llamada de IA, que devuelve hallazgos en bloques cercados; el extractor valida cada bloque como diff unificado y el `git apply --check` prueba que encaja en el árbol actual. Un clasificador **determinista y sin I/O** etiqueta cada candidato como `safe`, `review_required` o `experimental`. El dry run es el comportamiento predeterminado — escribir exige `--apply`, y el `--force` nunca sortea la verificación de aplicabilidad, solo la clasificación. Todo lo que se aplicó va a `.gitpr/fix_history.json`, que es lo que lee el `--rollback`.
- **Subcomando `gitpr review-pr <n>` — revisar el PR de terceros sin checkout:** El diff viene directamente de la API del forge y entra **en el mismo motor** que usan los flujos locales — mismo informe, mismas reglas de linter, mismo `.txt`. Read-only por defecto: nada se publica en el forge sin un `--post-comment` explícito. Amplía el público objetivo de "quien va a abrir un PR" a "quien fue invitado a revisar el PR de otra persona".
- **Resolución de identidad del revisor — el attach que no llegaba:** Las sugerencias nacen del `git blame`, así que cargan **nombres y correos electrónicos**, no logins. Cuando nada se resolvía, la UI mostraba el nombre puro — y escribir ese nombre de vuelta hacía que GitPR lo enviara *verbatim* como si fuera un login. GitHub responde **201 sin anexar a nadie**: éxito aparente, revisor ausente, aviso ninguno. Ahora una capa dedicada resuelve la identidad **dos veces** (antes de la TUI y en el attach) y cierra también el segundo fallo silencioso de la API — el login aceptado pero no anexado pasó a detectarse leyendo de vuelta `requested_reviewers`.
- **`gitpr fix` pasó a corregir la revisión que fue revisada:** El diff revisado se graba en el registro de caché (`reviewed_diff`) y el `fix` lo prefiere, cayendo a la re-derivación solo para registros antiguos. Es la única fuente correcta cuando el review vino de un PR remoto o de un diff de rama completa.
- **MCP creció de 12 a 14 herramientas y de 17 a 18 recursos:** `list_fix_candidates` (13ª, de solo lectura) + `skill://fix`, y `review_remote_pr` (14ª, de solo lectura, sin argumento `post_comment`, sin escribir `.txt`).
- **i18n ampliada a 1048 claves:** +93 desde el informe anterior, con `__lang_version__` en **v0.0.28** y paridad total de key sets en los 6 diccionarios.
- **Documentación:** 2 familias nuevas — `fix-command` (5 idiomas) y `review-pr` (EN + PT-BR) — y 9 temas actualizados, incluyendo `code-review-ia` (modo remoto como §1.4) y `suggested-reviewers` (resolución de login).
- **Dos defectos latentes de GitLab corregidos:** `changes[].diff` descarta la ruta del archivo, así que los encabezados `diff --git` pasaron a sintetizarse a partir de `old_path`/`new_path`; y un diff truncado (`overflow: true`) se revisaba a medias y se publicaba como si fuera entero — ahora lanza.
- **Versión 1.2.0:** el `__version__` pasó de 1.1.0 a 1.2.0 — el bump está en el working tree, todavía no commiteado ni etiquetado, y el `CHANGELOG.md` todavía se detiene en `[1.1.0]`.

## Cómo usar

Actualiza desde PyPI:

```
pip install --upgrade gitpr-cli
```

Revisa un pull request que ya está abierto — no se descarga nada a tu árbol:

```
gitpr review-pr 123                    # de solo lectura: el review sale en un .txt
gitpr review-pr 123 --post-comment     # el único camino que escribe en el forge
gitpr review-pr 123 --provider deepseek
```

Después convierte ese review en patches que lees antes de que toquen tu árbol:

```
gitpr fix                      # lista los candidatos del último review (no escribe nada)
gitpr fix FIX-001              # dry run: el diff de un hallazgo
gitpr fix FIX-001 --apply      # escribe, tras una confirmación
gitpr fix --all-safe --apply   # escribe todos los patches safe, en una rama nueva por defecto
gitpr fix --rollback FIX-001-1a2b3c4d
```

El `gitpr fix` lee el review más reciente del repositorio y de la rama actuales en la caché — ejecuta `gitpr -r` antes. El rollback no necesita commit, stash ni reset: reaplica el diff guardado con `git apply --reverse`.

## Consejos útiles

El `gitpr -r -i src/legacy/parser.py` revisa un archivo completo ignorando el historial de git — la documentación lo llama "actuar como consultor de refactorización de código legado". Personaliza el foco de la auditoría con el archivo de skill `.gitpr.filereview.md` (cohesión, acoplamiento).
