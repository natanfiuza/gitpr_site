# GitPR 1.0.0 — Nouveautés

## Nouveautés de cette version

- **SCM Multi-Forge (`gitpr --init` + couche `ScmProvider`) :** Une abstraction unique au-dessus de GitHub, GitLab, Bitbucket et Azure DevOps — `--init` détecte la forge depuis le remote, valide le jeton (3 tentatives, re-prompt sur 401) et l'enregistre chiffré avec Fernet **uniquement en cas de succès**. Le jeton legacy GitHub continue de fonctionner, sans migration.
- **Sous-commande `gitpr release` (Changelog / Release Notes) :** Génère le changelog de la branche entre `--since` (défaut : dernier tag) et `HEAD`, classe les commits selon les Conventional Commits, suggère le bump sémantique, ajoute un résumé exécutif IA et l'insère *en tête* de `CHANGELOG.md`. Avec `--publish`/`--draft` il publie la release sur la forge (GitHub crée le tag ; GitLab exige le tag) et `--format markdown|json` fournit une sortie structurée.
- **Reviewers Suggérés dans le flux PR :** GitPR interroge désormais la forge elle-même pour suggérer des reviewers lors de la publication d'une PR. Désactivez avec `--no-suggest-reviewers`, ajustez avec `GITPR_REVIEWER_SUGGESTION_TOP_N` et `GITPR_REVIEWER_SUGGESTION_EXCLUDED`.
- **Serveur MCP silencieux + DNS borné :** La fuite de sortie des outils dans le flux stdio/CLI est corrigée, la résolution DNS est bornée dans le temps et le défaut de `GITPR_AI_TIMEOUT` passe de 600s à **180s**.
- **URLs et prompts localisés :** URLs de dépôt standardisées dans les templates et la documentation ; les prompts de création d'issues reçoivent désormais la langue active.
- **i18n étendue à 742 clés :** Les titres du changelog sont traduisibles au runtime (ils suivent `--lang`), 48 nouvelles clés dans les 6 dictionnaires, `__lang_version__` v0.0.23 et parité totale — 0 non traduite, 0 orpheline.
- **Documentation multilingue étendue :** 3 nouvelles familles complètes en 5 langues — `release-notes`, `scm-multiforge` et `suggested-reviewers` — avec les ADRs d'architecture, plus 8 sujets mis à jour.
- **Version 1.0.0 :** `__version__` est passée de 0.0.37 à 1.0.0, et `CHANGELOG.md` est désormais tenu par `gitpr release` lui-même.

## Comment l'utiliser

Mettez à jour via PyPI :

```
pip install --upgrade gitpr-cli
```

Ou téléchargez le binaire standalone depuis [GitHub Releases](https://github.com/gitpr-cli/gitpr/releases).

Configurez votre forge une seule fois — GitPR la détecte depuis le remote :

```
gitpr --init            # assistant multi-forge : détecte la forge et valide le jeton
```

Générez le changelog de votre branche et, si vous le souhaitez, publiez la release :

```
gitpr release           # changelog + résumé IA en tête de CHANGELOG.md
gitpr release --publish # publie la release sur la forge configurée
```

Le flux PR suggère désormais les reviewers automatiquement — désactivez avec `--no-suggest-reviewers`.

## Astuces utiles

`gitpr -is` génère une issue à partir du diff actuel, mais il existe deux autres moteurs : `-ht` compile tout l'historique de la branche en une issue de release/épique, et `-b src/core.py:140-195` retrace l'évolution d'un fichier via `git blame` pour documenter le code legacy et la dette technique.
