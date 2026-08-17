# GitPR 0.0.35 — Novedades

## Novedades de esta versión

- **Invocación directa de herramientas MCP vía CLI (`gitpr-mcp --tool`):** Las 12 herramientas MCP de GitPR ahora pueden invocarse directamente desde la línea de comandos con `gitpr-mcp --tool <name> [--tool-args '<json>']`, sin iniciar el servidor stdio JSON-RPC. El modo `--tool` (sin nombre) lista todas las herramientas disponibles con sus firmas. Ideal para depuración, scripts y uso manual.
- **Manejo de errores en el merge de PR:** El PR Publisher (Textual TUI) ahora muestra un modal de error visible cuando el merge del PR falla — especialmente HTTP 405 que indica conflictos. Anteriormente, la falla se ignoraba silenciosamente y el flujo continuaba como si todo hubiera funcionado.
- **Nuevos documentos MCP:** 3 nuevos temas de documentación MCP en 5 idiomas: `mcp-annotations.md` (anotaciones de herramientas), `mcp-integration.md` (guía de integración), `mcp-prompts.md` (guía de prompts plantillados).

## Cómo usar

Instala o actualiza vía PyPI:

```
pip install gitpr-cli
```

O descarga el binario standalone desde [GitHub Releases](https://github.com/natanfiuza/gitpr/releases).

Prueba la novedad ahora mismo:

```
gitpr-mcp --tool               # lista las 12 herramientas disponibles con sus firmas
gitpr-mcp --tool analyze_diff  # invoca una herramienta directamente, sin el servidor MCP
```

## Consejos útiles

Un solo comando configura el servidor MCP de GitPR en tu editor: `gitpr-mcp --install auto` detecta VSCode, Cursor, Claude Code, Claude Desktop o Zed y escribe el archivo de config correcto. Es idempotente y se fusiona con la configuración existente, sin sobrescribir otros servidores MCP.
