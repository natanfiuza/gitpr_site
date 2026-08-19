# GitPR 0.0.37 — Novedades

## Novedades de esta versión

- **Bridge de Linters Externos + Asistente `--linter-setup`:** Integración con linters maduros (ESLint, PHP_CodeSniffer, Stylelint) ejecutados solo en las líneas modificadas del diff, con parser de salida Checkstyle XML, nueva TUI de errores (`LinterApp`) e informe Markdown consolidado en `.gitpr/reports/linter/`. El asistente interactivo configura todo con presets remotos (`templates/gitpr.linter-presets.json`) versionados por el marcador `LINTER_PRESETS_VERSION`.
- **i18n Reparada y Completa:** Reparadas 51 claves corruptas + 36 claves con `\n` literal en los 6 diccionarios; auditoría AST de 638 claves con 0 sin traducir y 0 mangled; paridad total de 547 claves idénticas por archivo; `__lang_version__` v0.0.13 → v0.0.20 con pruebas de guarda.
- **Trailer de Coautoría:** Todo commit generado por IA recibe `Co-Authored-By: Gitpr-cli <gitpr@natanfiuza.dev.br>` — idempotente (nunca se duplica, preserva trailers de terceros), oculto de la TUI y con opt-out `GITPR_COAUTHOR=false`.
- **Fix del Hang del MCP Server:** Los 12 tool handlers eran síncronos y corrían inline en el event loop — cualquier llamada bloqueante congelaba el servidor stdio. Nuevo decorador `_offload` (anyio worker threads), warm-import en el startup, `stdin=subprocess.DEVNULL` en todos los subprocesses y timeout duro de 10s en la descarga de smart-excludes. Nuevas pruebas e2e con JSON-RPC stdio real.
- **Correcciones del Modal de Error del Linter:** Botones "Commit with --no-verify" y "Abort" lado a lado (antes apilados y superpuestos); la elección no-verify ahora reanuda el flujo de commit; push del modal diferido vía `call_next` al message pump de la app.
- **Dead Code Eliminado + Ajustes MCP:** Clase muerta `FileStageScreen` eliminada; `claude-code` listado en el help de `gitpr-mcp --install`; alias oculto `gitpr --mcp` documentado.
- **Documentación Multilingüe Expandida:** `docs/ARCHITECTURE.md` reescrito en EN canónico + 4 locales creados (18 temas de arquitectura); nuevo tema `i18n_explanation` en 5 idiomas; READMEs y 4 temas actualizados.
- **Formato Consistente del Codebase:** Refactor Black-style en todo `src/` (comillas dobles, trailing commas, saltos de línea) — sin cambio funcional.
- **Skills Locales de Claude Code:** Añadidas las skills `status-report` (generación del informe de estado), `implement-fixes` (flujo de correcciones) y `caveman-commit` (mensajes de commit compactos).

## Cómo usar

Actualiza vía PyPI:

```
pip install --upgrade gitpr-cli
```

O descarga el binario standalone desde [GitHub Releases](https://github.com/natanfiuza/gitpr/releases).

Prueba el nuevo bridge de linters externos:

```
gitpr --linter-setup   # asistente interactivo: ESLint, PHPCS, Stylelint
gitpr --linter         # reglas regex + linters externos, informe en .gitpr/reports/linter/
```

Tus commits ahora llevan el trailer de coautoría automáticamente — desactívalo con `GITPR_COAUTHOR=false`.

## Consejos útiles

Vuelve a ejecutar cualquier comando de IA sin cambiar el código y GitPR responde en milisegundos: las respuestas se guardan en caché en `~/.gitpr/cache/prompts/`, indexadas por un hash MD5 de tu diff + instrucciones — repetir un comando no gasta nada de tu cuota de API.
