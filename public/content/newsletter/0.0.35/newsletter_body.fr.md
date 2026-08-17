# GitPR 0.0.35 — Nouveautés

## Nouveautés de cette version

- **Invocation directe des outils MCP via CLI (`gitpr-mcp --tool`) :** Les 12 outils MCP de GitPR peuvent désormais être invoqués directement depuis la ligne de commande avec `gitpr-mcp --tool <name> [--tool-args '<json>']`, sans démarrer le serveur stdio JSON-RPC. Le mode `--tool` (sans nom) liste tous les outils disponibles avec leurs signatures. Idéal pour le débogage, les scripts et l'utilisation manuelle.
- **Gestion des erreurs dans le merge de PR :** Le PR Publisher (TUI Textual) affiche désormais un modal d'erreur visible lorsque le merge du PR échoue — notamment HTTP 405 indiquant des conflits. Auparavant, l'échec était silencieusement ignoré et le flux continuait comme si tout avait fonctionné.
- **Nouveaux documents MCP :** 3 nouveaux sujets de documentation MCP en 5 langues : `mcp-annotations.md` (annotations des outils), `mcp-integration.md` (guide d'intégration), `mcp-prompts.md` (guide des prompts templatisés).

## Comment l'utiliser

Installez ou mettez à jour via PyPI :

```
pip install gitpr-cli
```

Ou téléchargez le binaire standalone depuis [GitHub Releases](https://github.com/natanfiuza/gitpr/releases).

Testez la nouveauté tout de suite :

```
gitpr-mcp --tool               # liste les 12 outils disponibles avec leurs signatures
gitpr-mcp --tool analyze_diff  # invoque un outil directement, sans le serveur MCP
```

## Astuces utiles

Une seule commande configure le serveur MCP de GitPR dans votre éditeur : `gitpr-mcp --install auto` détecte VSCode, Cursor, Claude Code, Claude Desktop ou Zed et écrit le bon fichier de config. Elle est idempotente et fusionne avec les réglages existants, sans jamais écraser d'autres serveurs MCP.
