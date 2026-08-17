# GitPR 0.0.36 — Novedades

## Novedades de esta versión

- **Corrección de selección y errores en el staging (`stage_files`):** La TUI de staging ahora lee la selección real de `SelectionList.selected` (toggles individuales respetados) y `stage_files()` devuelve `(success, error_message)` — los fallos de `git add` muestran el error real de git en lugar de un falso mensaje de éxito. El staging ahora ocurre una sola vez por flujo.
- **Omisión del mensaje de IA en commits generados por git:** Los hooks `prepare-commit-msg` (5 variantes de idioma) ahora omiten todas las fuentes generadas por git (`merge`, `squash`, `amend`, `commit` — antes solo `message`), con verificación belt-and-braces de `.git/MERGE_HEAD`. `git pull`/`git merge` ya no corrompen el `.git/MERGE_MSG` con mensaje de IA. Hooks con auto-sync a la v0.0.2.
- **Traducciones de estado de archivo:** Labels de estado ("Modified", "Deleted", "New") traducidos en los paquetes es, fr, pt_br y pt_pt — la cobertura pt_BR subió a 507 claves.
- **Documentación multilingüe ampliada y sincronizada:** `docs/pr-descricao-padrao.md` reescrito en EN canónico + 4 locales con sección de publicación; `docs/mcp-integration.md` sincronizado en los 5 idiomas; `docs/git-hooks-locais.md` documenta el skip de merge-source en los 5 idiomas.
- **Nueva plantilla MCP:** `templates/gitpr.mcp-jsonrpc-calls.md` — referencia de llamadas JSON-RPC para las herramientas MCP.

## Cómo usar

Actualiza vía PyPI:

```
pip install --upgrade gitpr-cli
```

O descarga el binario standalone desde [GitHub Releases](https://github.com/natanfiuza/gitpr/releases). Los hooks `prepare-commit-msg` se sincronizan automáticamente a la v0.0.2 — no hace falta ningún paso manual.

Comprueba las correcciones:

```
gitpr            # flujo de publicación: el modal de staging respeta tu selección y muestra errores reales de git
git merge <rama> # el mensaje de IA ya no toca el .git/MERGE_MSG
```

## Consejos útiles

Con los hooks instalados (`gitpr -ih`), un simple `git commit` abre tu editor con el mensaje de la IA ya rellenado. Pero GitPR sabe cuándo apartarse: detecta `-m`, merges, squashes y amends y la IA permanece en silencio — tus propios mensajes nunca se sobrescriben.
