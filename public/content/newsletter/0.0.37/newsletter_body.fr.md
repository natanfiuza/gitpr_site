# GitPR 0.0.37 — Nouveautés

## Nouveautés de cette version

- **Pont de Linters Externes + Assistant `--linter-setup` :** Intégration avec des linters matures (ESLint, PHP_CodeSniffer, Stylelint) exécutés uniquement sur les lignes modifiées du diff, avec parser de sortie Checkstyle XML, nouvelle TUI d'erreurs (`LinterApp`) et rapport Markdown consolidé dans `.gitpr/reports/linter/`. L'assistant interactif configure tout avec des presets distants (`templates/gitpr.linter-presets.json`) versionnés par le marqueur `LINTER_PRESETS_VERSION`.
- **i18n Réparée et Complète :** 51 clés corrompues réparées + 36 clés avec `\n` littéral dans les 6 dictionnaires ; audit AST de 638 clés avec 0 non traduites et 0 mangled ; parité totale de 547 clés identiques par fichier ; `__lang_version__` v0.0.13 → v0.0.20 avec tests de garde.
- **Trailer de Co-paternité :** Chaque commit généré par l'IA reçoit `Co-Authored-By: Gitpr-cli <gitpr@natanfiuza.dev.br>` — idempotent (jamais dupliqué, préserve les trailers de tiers), masqué de la TUI et avec opt-out `GITPR_COAUTHOR=false`.
- **Correction du Hang du MCP Server :** Les 12 tool handlers étaient synchrones et tournaient inline sur l'event loop — tout appel bloquant gelait le serveur stdio. Nouveau décorateur `_offload` (anyio worker threads), warm-import au démarrage, `stdin=subprocess.DEVNULL` sur tous les subprocesses et timeout dur de 10s sur le téléchargement des smart-excludes. Nouveaux tests e2e avec JSON-RPC stdio réel.
- **Corrections du Modal d'Erreur du Linter :** Boutons « Commit with --no-verify » et « Abort » côte à côte (avant empilés et superposés) ; le choix no-verify reprend désormais le flux de commit ; push du modal différé via `call_next` vers le message pump de l'app.
- **Dead Code Supprimé + Ajustements MCP :** Classe morte `FileStageScreen` supprimée ; `claude-code` listé dans l'aide de `gitpr-mcp --install` ; alias caché `gitpr --mcp` documenté.
- **Documentation Multilingue Étendue :** `docs/ARCHITECTURE.md` réécrit en EN canonique + 4 locales créés (18 sujets d'architecture) ; nouveau sujet `i18n_explanation` en 5 langues ; READMEs et 4 sujets mis à jour.
- **Formatage Cohérent du Codebase :** Refactor Black-style dans tout `src/` (guillemets doubles, trailing commas, sauts de ligne) — sans changement fonctionnel.
- **Skills Locales de Claude Code :** Ajout des skills `status-report` (génération du rapport de statut), `implement-fixes` (flux de corrections) et `caveman-commit` (messages de commit compacts).

## Comment l'utiliser

Mettez à jour via PyPI :

```
pip install --upgrade gitpr-cli
```

Ou téléchargez le binaire standalone depuis [GitHub Releases](https://github.com/natanfiuza/gitpr/releases).

Essayez le nouveau pont de linters externes :

```
gitpr --linter-setup   # assistant interactif : ESLint, PHPCS, Stylelint
gitpr --linter         # règles regex + linters externes, rapport dans .gitpr/reports/linter/
```

Vos commits portent désormais le trailer de co-paternité automatiquement — désactivez-le avec `GITPR_COAUTHOR=false`.

## Astuces utiles

Relancez n'importe quelle commande IA sans modifier le code et GitPR répond en quelques millisecondes : les réponses sont mises en cache dans `~/.gitpr/cache/prompts/`, indexées par un hash MD5 de votre diff + instructions — répéter une commande ne consomme aucune part de votre quota API.
