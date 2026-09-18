# GitPR 1.2.0 — Nouveautés

## Nouveautés de cette version

- **Sous-commande `gitpr fix` — le review qui devient un patch applicable :** Le dernier review du cache alimente **un** appel d'IA, qui renvoie des constats dans des blocs encadrés ; l'extracteur valide chaque bloc comme un diff unifié et `git apply --check` prouve qu'il s'insère dans l'arbre actuel. Un classificateur **déterministe et sans I/O** étiquette chaque candidat comme `safe`, `review_required` ou `experimental`. Le dry run est le défaut — écrire exige `--apply`, et le `--force` ne contourne jamais la vérification d'applicabilité, seulement la classification. Tout ce qui a été appliqué entre dans `.gitpr/fix_history.json`, qui est ce que lit le `--rollback`.
- **Sous-commande `gitpr review-pr <n>` — réviser le PR d'un tiers sans checkout :** Le diff vient directement de l'API de la forge et entre **dans le même moteur** que celui utilisé par les flux locaux — même rapport, mêmes règles de linter, même `.txt`. En lecture seule par défaut : rien n'est publié sur la forge sans `--post-comment` explicite. Il élargit le public cible de « celui qui va ouvrir un PR » à « celui qui a été invité à réviser le PR de quelqu'un d'autre ».
- **Résolution de l'identité du reviewer — l'attach qui n'arrivait pas :** Les suggestions naissent du `git blame`, donc elles portent des **noms et des e-mails**, pas des logins. Quand rien ne se résolvait, l'UI affichait le nom nu — et ressaisir ce nom faisait envoyer par GitPR *verbatim* comme s'il s'agissait d'un login. GitHub répond **201 sans attacher personne** : succès apparent, reviewer absent, aucun avertissement. Désormais une couche dédiée résout l'identité **deux fois** (avant la TUI et à l'attach) et ferme aussi la seconde défaillance silencieuse de l'API — un login accepté mais non attaché est désormais détecté en relisant `requested_reviewers`.
- **`gitpr fix` corrige désormais le review qui a été révisé :** Le diff révisé est consigné dans l'enregistrement de cache (`reviewed_diff`) et le `fix` le préfère, ne retombant sur la re-dérivation que pour les anciens enregistrements. C'est la seule source correcte quand le review vient d'un PR distant ou d'un diff de branche entière.
- **MCP est passé de 12 à 14 outils et de 17 à 18 ressources :** `list_fix_candidates` (13e, en lecture seule) + `skill://fix`, et `review_remote_pr` (14e, en lecture seule, sans argument `post_comment`, sans écrire de `.txt`).
- **i18n étendue à 1048 clés :** +93 depuis le rapport précédent, avec `__lang_version__` en **v0.0.28** et une parité totale des key sets dans les 6 dictionnaires.
- **Documentation :** 2 nouvelles familles — `fix-command` (5 langues) et `review-pr` (EN + PT-BR) — et 9 sujets mis à jour, dont `code-review-ia` (mode distant comme §1.4) et `suggested-reviewers` (résolution du login).
- **Deux défauts latents de GitLab corrigés :** `changes[].diff` écarte le chemin du fichier, donc les en-têtes `diff --git` sont désormais synthétisés à partir de `old_path`/`new_path` ; et un diff tronqué (`overflow: true`) était révisé à moitié puis publié comme s'il était entier — il lève désormais.
- **Version 1.2.0 :** `__version__` est passée de 1.1.0 à 1.2.0 — le bump est dans le working tree, pas encore commité ni tagué, et le `CHANGELOG.md` s'arrête encore à `[1.1.0]`.

## Comment l'utiliser

Mettez à jour depuis PyPI :

```
pip install --upgrade gitpr-cli
```

Révisez un pull request déjà ouvert — rien n'est récupéré dans votre arbre :

```
gitpr review-pr 123                    # lecture seule : le review sort dans un .txt
gitpr review-pr 123 --post-comment     # le seul chemin qui écrit sur la forge
gitpr review-pr 123 --provider deepseek
```

Puis transformez ce review en patches que vous lisez avant qu'ils ne touchent votre arbre :

```
gitpr fix                      # liste les candidats du dernier review (n'écrit rien)
gitpr fix FIX-001              # dry run : le diff d'un constat
gitpr fix FIX-001 --apply      # écrit, après une confirmation
gitpr fix --all-safe --apply   # écrit tous les patches safe, sur une nouvelle branche par défaut
gitpr fix --rollback FIX-001-1a2b3c4d
```

Le `gitpr fix` lit le review le plus récent du dépôt et de la branche courants depuis le cache — lancez `gitpr -r` d'abord. Le rollback n'a besoin ni de commit, ni de stash, ni de reset : il rejoue le diff stocké avec `git apply --reverse`.

## Astuces utiles

`gitpr -r -i src/legacy/parser.py` passe en revue un fichier entier en ignorant l'historique git — la documentation parle d'« agir comme consultant en refactorisation de code legacy ». Personnalisez le focus de l'audit via le fichier de skill `.gitpr.filereview.md` (cohésion, couplage).
