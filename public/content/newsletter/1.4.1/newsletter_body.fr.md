# GitPR 1.4.1 — Nouveautés

## Nouveautés de cette version

- **`gitpr check` — une barrière unique pour la CI :** linter, analyse de secrets, ponts SAST, score de risque, baseline et review IA optionnel tournent désormais derrière **une seule commande**, avec des artefacts **SARIF 2.1.0**, **JSON** ou **JUnit XML** et un contrat de sortie déterministe (`0` succès, `1` constat bloquant, `2` erreur d'exécution). `action.yml` a été réécrit pour appeler cette commande, la clé API n'est plus obligatoire et il existe un chemin `--no-ia` à coût zéro.
- **Extension VS Code et Cursor — l'éditeur est devenu un client MCP :** huit commandes (`commit`, `review`, `fullReview`, `reviewMentor`, `generatePr`, `generatePrExplain`, `publishPr`, `check`) parlent à `gitpr-mcp` sur stdio et **ne dupliquent jamais la logique métier** — clés, jetons et prompts restent du côté de la CLI. L'extension publie les PR en deux étapes, remplit le panneau **Problems** avec les constats de `run_check` et masque les secrets des deux côtés de la frontière.
- **Deux nouveaux outils MCP et un cas d'usage partagé :** `run_check` (lecture seule, `no_ia=true` par défaut) et `publish_pull_request` (ne commit jamais et ne push jamais) sont entrés dans le serveur, qui est passé de **16 à 18 outils**. La publication `--no-edit` n'a plus son propre code : CLI et MCP passent par le même `publish_pull_request_use_case`, avec les statuts `success`/`needs_push`/`already_exists`/`auth_required`/`error`.
- **Le skill de PR a cessé de propager un artefact collé par accident :** un fragment JSON (`".gitpr.commit.md": "gitpr.commit.md",`) a fini dans la règle de structure du template `pt_br` et l'IA le répétait dans **chaque** PR généré. Comme `--skill` n'écrase jamais un fichier existant, corriger le template ne suffisait pas : `get_skill_context()` **supprime désormais le fragment en mémoire** (sans toucher au fichier de l'utilisateur), et un nouveau test scanne **chaque template publié** pour éviter la récidive.
- **Le merge de PR a gagné un retour visuel :** l'appel à la forge s'exécutait en silence sur un thread daemon et une exception inattendue **tuait le thread sans avertissement**, laissant la TUI bloquée. La modale de progression existante a été généralisée (`title` optionnel, `mark_finished(show_close=False)`), poussée avant le démarrage du worker, et couvre désormais **succès, conflit et erreur inattendue**, avec fermeture automatique après 1,2 s sur le chemin nominal.
- **La dette i18n s'est rouverte — et la suite le signale :** **8 clés `__()` utilisées dans le code n'existent dans aucun dictionnaire** (publication de PR et descriptions des outils MCP) et **2 sont devenues orphelines** ; `es.json` et `fr.json` sont restés à **1618** clés contre 1620 dans les quatre autres. Ajoutés aux **7 échecs de `tests/badge/test_badge_paths.py`** — le faux fournisseur des tests n'a pas de `.token`, et le cas d'usage partagé en exige désormais un — et aux **2 échecs connus** de `tests/test_mentor_cli.py`, la suite se referme à **2938 réussis sur 2952** (12 échecs, 2 skips).
- **La release 1.4.1 est préparée, mais pas encore close :** `__version__` est monté à **1.4.1** (commit `46c25c1`) et `__lang_version__` à **v0.0.35** ; `CHANGELOG.md` a déjà la section `[1.4.1]` générée par le moteur de release — **dans l'arbre de travail**, et le tag `v1.4.1` **n'existe toujours pas**.

## Comment l'utiliser

Mettez à jour depuis PyPI :

```
pip install --upgrade gitpr-cli
```

La release s'articule autour d'une seule commande de CI :

```
gitpr check                                      # linter, secrets, SAST, risque et baseline — plus review IA optionnel
gitpr check --format sarif --output gitpr.sarif  # artefact SARIF 2.1.0 pour GitHub Code Scanning
gitpr check --no-ia                              # chemin à coût zéro : aucun appel IA et aucune clé API requise
gitpr check --fail-on warning                    # fait bloquer le build par les constats de niveau warning
```

Le code de sortie est le contrat : `0` succès, `1` constat bloquant, `2` erreur d'exécution. L'artefact peut aussi être émis en `json` ou `junit`.

Pour VS Code et Cursor, installez l'extension `natanfiuza.gitpr` depuis le Marketplace (ou Open VSX) et lancez les commandes depuis la Palette de Commandes : Commit, Review, Full Review, Review with Mentor, Generate PR, Generate PR with Reviewer Guide, Publish PR et Check. `GitPR: Check` remplit le panneau Problèmes avec des diagnostics cliquables ; les clés et les jetons restent dans `~/.gitpr/.env`.

## Astuces utiles

Les diffs de plus de ~90 000 tokens (estimation de 4 caractères par token) sont gérés automatiquement : GitPR les découpe en lots aux frontières de fichiers, résume chaque partie (Map) puis unifie le tout dans un appel final (Reduce) — aucun flag nécessaire.
