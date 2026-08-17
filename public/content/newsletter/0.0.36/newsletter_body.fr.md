# GitPR 0.0.36 — Nouveautés

## Nouveautés de cette version

- **Correction de la sélection et des erreurs de staging (`stage_files`) :** La TUI de staging lit désormais la sélection réelle de `SelectionList.selected` (toggles individuels respectés) et `stage_files()` renvoie `(success, error_message)` — les échecs de `git add` affichent l'erreur git réelle au lieu d'un faux message de succès. Le staging ne s'exécute plus qu'une seule fois par flux.
- **Skip du message IA dans les commits générés par git :** Les hooks `prepare-commit-msg` (5 variantes de langue) sautent désormais toutes les sources générées par git (`merge`, `squash`, `amend`, `commit` — avant, seul `message`), avec une vérification belt-and-braces de `.git/MERGE_HEAD`. `git pull`/`git merge` ne corrompent plus le `.git/MERGE_MSG` avec un message IA. Hooks avec auto-sync vers la v0.0.2.
- **Traductions des statuts de fichier :** Les labels de statut (« Modified », « Deleted », « New ») traduits dans les packs es, fr, pt_br et pt_pt — la couverture pt_BR est passée à 507 clés.
- **Documentation multilingue étendue et synchronisée :** `docs/pr-descricao-padrao.md` réécrit en EN canonique + 4 locales avec une section de publication ; `docs/mcp-integration.md` synchronisé dans les 5 langues ; `docs/git-hooks-locais.md` documente le skip de merge-source dans les 5 langues.
- **Nouveau template MCP :** `templates/gitpr.mcp-jsonrpc-calls.md` — référence des appels JSON-RPC pour les outils MCP.

## Comment l'utiliser

Mettez à jour via PyPI :

```
pip install --upgrade gitpr-cli
```

Ou téléchargez le binaire standalone depuis [GitHub Releases](https://github.com/natanfiuza/gitpr/releases). Les hooks `prepare-commit-msg` se synchronisent automatiquement vers la v0.0.2 — aucune étape manuelle requise.

Voyez les corrections en action :

```
gitpr              # flux de publication : le modal de staging respecte votre sélection et affiche les vraies erreurs git
git merge <branche> # le message IA ne touche plus au .git/MERGE_MSG
```

## Astuces utiles

Avec les hooks installés (`gitpr -ih`), un simple `git commit` ouvre votre éditeur avec le message de l'IA prérempli. Mais GitPR sait s'effacer : les `-m`, merges, squashes et amends sont détectés et l'IA reste silencieuse — vos propres messages ne sont jamais écrasés.
