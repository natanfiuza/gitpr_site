# GitPR 1.4.2 — Novedades

## Novedades de esta versión

- **Dos releases cerrados — 1.4.1 y 1.4.2:** la v0.0.18 dejó la 1.4.1 "preparada, pero aún no cerrada". Esta ventana cerró **ambos**: el tag `v1.4.1` se creó el 2026-10-18 (PR #218) y el `v1.4.2` el 2026-10-27 (PR #222), con `CHANGELOG.md` commiteado en los dos. PyPI ya sirve la **1.4.2**; `v1.4.0` dejó de ser el último tag.
- **El diff desaparecía en repositorios con contenido legado:** `get_git_full_diff()` decodificaba la salida de `git diff` como UTF-8 estricto. Un solo byte Latin-1 (`0xFA`) mataba el hilo lector del subproceso, `stdout` volvía `None` **sin excepción** y la CLI anunciaba "No se encontró código nuevo" — como si la rama no tuviera cambios. Las **10** llamadas a `subprocess` que capturan salida de git ahora decodifican con `errors="replace"`, y un `None` devuelto por git ahora **sale con código 1** en PR, review, full review y commit. Un diff genuinamente vacío sigue saliendo con **0**, y `--hook` falla **abierto**, porque un `prepare-commit-msg` distinto de cero abortaría el commit del usuario.
- **`explain` y `tests generate` finalmente documentados — y corregidos en el camino:** eran las **dos últimas** funcionalidades sin tema en `docs/`, heredadas como deuda durante varias ventanas. Ganaron **5 idiomas cada una** (`docs/explain-pr.*` y `docs/tests-generate.*`, 10 archivos). Documentar reveló **cuatro defectos reales**: `parse_explain_payload()` reventaba con el dict ya interpretado del proveedor (`json.loads(dict)` → `TypeError`); `generate_test_file` tenía la misma incompatibilidad; `gitpr tests generate --finding` leía `cand.finding.suggested_test` y `cand.diff`, atributos que no existen; y `gitpr -h --explain` estaba roto por un desajuste de `dest` (`explain_flag` × `explain`).
- **Los README apuntan al sitio oficial de documentación:** **342** destinos de documentación en los 5 README dejaron `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/*.md` y ahora apuntan a `https://gitpr.natanfiuza.dev.br/docs/*`, con el idioma en `?lang=` y el inglés **sin** parámetro — exactamente el formato que `src/doc_links.py` ya canoniza. **217 URLs únicas** verificadas con `curl`: **0 fallos**.
- **La deuda de i18n no se pagó:** los diccionarios siguen en **1620 claves** (×4) y **1618** (`es.json`, `fr.json`), con **8 claves usadas en el código sin traducción** y **2 huérfanas** — los mismos números del informe anterior. Sumadas a los **7 fallos de fixture** en `tests/badge/test_badge_paths.py` y a los **2 fallos conocidos** en `tests/test_mentor_cli.py`, la suite cierra en **2950 aprobadas de 2964** (12 fallos, 2 skips).

## Cómo usar

Actualiza desde PyPI:

```
pip install --upgrade gitpr-cli
```

Las dos últimas funcionalidades sin documentación ahora tienen una guía cada una:

```
gitpr explain                                    # guía del revisor para el diff actual
gitpr tests generate --file src/app.py           # dry run: muestra la suite sin escribir nada
gitpr tests generate --file src/app.py --apply   # escribe el archivo de test tras confirmación
```

`gitpr -h --explain` vuelve a funcionar y abre la guía del revisor, y tanto `gitpr tests` como `gitpr tests generate` incluyen ahora un enlace a su propia guía en el epílogo de la ayuda. Todo publicado en cinco idiomas en https://gitpr.natanfiuza.dev.br/docs — y los README apuntan ahora allí, con el idioma en `?lang=`.

Si tu repositorio tiene archivos en codificación heredada, actualiza: la 1.4.2 decodifica la salida de git con `errors="replace"` y sale con código 1 cuando git falla, en lugar de anunciar "No se encontró código nuevo" en una rama que sí tiene cambios.

## Consejos útiles

Smart Excludes filtra lockfiles, archivos minificados y generados del diff con la sintaxis pathspec nativa de Git, antes de que la IA vea nada — el ejemplo de la documentación reduce ~15.000 tokens a ~250 (98% menos), mientras la lista de archivos modificados se inyecta igualmente como metadato.
