# GitPR 1.4.4 — Nouveautés

## Nouveautés de cette version

- **`gitpr -c` est devenu une TUI en 4 étapes :** commiter n'est plus un pipeline silencieux. `CommitApp` ouvre le **Sélecteur de Stage** pour les fichiers hors index, génère le message dans un thread d'arrière-plan sur le **diff indexé**, affiche la **modale de révision/édition** et exécute le commit dans une modale animée de progression — vous pouvez choisir les fichiers, éditer le texte ou annuler avant que quoi que ce soit n'atteigne le dépôt.
- **Le message décrit désormais ce qui a réellement été indexé :** `get_git_staged_diff()` exécute `git diff --cached` avec les mêmes protections que le diff normal (`-U1 -w -M -B`, smart excludes, UTF-8), et ne retombe sur le diff de l'arbre de travail que lorsque l'index est vide. Ce qui n'est pas indexé n'entre pas dans le prompt.
- **La modale de commit se ferme seule en cas de succès :** `mark_finished(False)` + `set_timer(0.6, self.exit)` rendent le terminal, et la CLI affiche `committed`/`failed` avec la sortie de git. En cas d'erreur, le bouton **Close** reste, car c'est là que le journal de l'échec demeure lisible.
- **1.4.3 et 1.4.4 sont toutes deux sur PyPI :** le correctif de la barrière de mise à jour — cache glissant de 1 heure, comparaison PEP 440, barrière atteignant les sous-commandes et `GITPR_SKIP_UPDATE_CHECK` en booléen strict — est arrivé dans la 1.4.3 et a été publié dans la même fenêtre. `pip install --upgrade gitpr-cli` installe la 1.4.4.
- **Rupture déclarée :** hors `--hook`, `gitpr -c` **ouvre toujours** la TUI — il n'y a aucune vérification de TTY dans le routage. Les scripts et les jobs CI qui comptaient sur le commit silencieux doivent passer à `--hook`.
- **La dette i18n a grossi, elle n'a pas été payée :** les dictionnaires restent à **1620 clés** (×4) et **1618** (`es.json`, `fr.json`), alors que le code en utilise désormais **1629** — les 3 nouvelles chaînes de la TUI de commit sont entrées non traduites. Cela fait **11 clés sans dictionnaire** et **2 orphelines**, et `__lang_version__` est monté à **v0.0.36** sans qu'aucun fichier de langue ne change.

## Comment l'utiliser

Mettez à jour depuis PyPI :

```
pip install --upgrade gitpr-cli
```

Lancez `gitpr -c` (ou `gitpr --commit`) dans n'importe quel dépôt. Si tout est déjà indexé, le Sélecteur de Stage est sauté ; sinon, il liste les fichiers hors index avec « Tout sélectionner », « Tout désélectionner », « Indexer la sélection », « Ignorer l'indexation » et « Annuler ». Relisez le message généré, éditez-le si vous voulez — c'est le texte édité que git commite — puis confirmez. La modale de progression se ferme d'elle-même une fois le commit réussi.

Pour l'automatisation, `--hook` conserve le chemin silencieux et merge-aware d'avant : utilisez-le dans les hooks de commit et en CI. `GITPR_SKIP_UNSTAGED_CHECK=true` (ou `--no-unstaged-check`) ne saute que le Sélecteur de Stage, et `GITPR_AUTO_STAGE=true` indexe tout et poursuit.

## Astuces utiles

Un correctif, une refactorisation et un réglage de config dans le même `git diff` ? `gitpr split` regroupe les hunks par intention et propose un commit atomique par préoccupation, chacun avec un message écrit pour ce seul sous-ensemble. La lecture est le défaut — il imprime le plan et demande ; `--dry-run` imprime et s'arrête, `--apply` indexe et commite groupe par groupe, et `--max-groups 3` plafonne le plan. Les fichiers non suivis sont nommés dans un avertissement, jamais écartés en silence.
