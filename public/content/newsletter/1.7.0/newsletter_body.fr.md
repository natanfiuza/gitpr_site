# GitPR 1.7.0 — Nouveautés

## Nouveautés de cette version

- **Le tableau de bord de métriques a gagné des graphiques ASCII et une navigation par onglets :** le nouveau `src/ascii_chart.py` dessine un histogramme vertical de latence et une série temporelle de tokens avec des fonctions pures, sans nouvelle dépendance ; `MetricsApp` a gagné les onglets **Events** et **Charts & Trends**, avec les raccourcis `1`/`2` ([ADR-0018](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
- **Le prompt est devenu plus résistant aux binaires :** `collapse_binary_hunks()` s'exécute désormais **aussi** au début de `generate_pr_content()`, et le catalogue d'exclusions intelligentes a gagné `*.arrow`, `*.gguf`, `*.tflite` et `*.keras` — les artefacts ML/données n'atteignent plus l'IA même lorsqu'ils arrivent par un appelant externe.
- **La publication de l'extension a été découplée du reste de la version :** `extension.yml` a gagné le job `github-release` (`.vsix` attaché aux releases `ext-v*`) et le job non bloquant `publish-marketplaces`, qui évalue `VSCE_PAT` et `OVSX_PAT` **indépendamment** et rapporte dans `$GITHUB_STEP_SUMMARY`. La documentation d'installation du VSIX (CLI et GUI) est entrée dans les 5 langues.
- **Le coût et la devise ne sont plus fixes :** le schéma a gagné le type `KIND_FLOAT`, la clé `GITPR_METRICS_CURRENCY` et **huit** champs de prix `INPUT`/`OUTPUT` par modèle ; `DEFAULT_MODEL_PRICES` tarife désormais aussi `gemini-pro-latest` et `gemini-flash-lite-latest`.
- **Les notes de migration ont été formalisées :** la rupture de `action.yml` vers `gitpr check` ([ADR-0011](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0011-action-unified-check-migration.md)), le commit interactif sans TTY, le `GITPR_SKIP_UPDATE_CHECK` strict et le seed/opt-out de la configuration de projet en CI disposent désormais d'une documentation explicite.
- **Trois nouveaux ADR ont clos des contrats en attente :** [ADR-0017](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0017-documentation-site-language-fallback.md) (fallback de langue du site), [ADR-0018](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) (graphiques/onglets) et [ADR-0019](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0019-project-configuration-seed-segregation.md) (ségrégation entre l'auto-seed d'exécution et le provisionnement explicite).
- **1.6.0 et 1.7.0 ont été publiées :** PyPI sert **1.7.0**. La dette de release de la fenêtre précédente est soldée, et `develop_natan` a été fusionnée dans `main` par la PR #310.

## Comment l'utiliser

La 1.7.0 est publiée sur PyPI. Mettez à jour avec :

```
pip install --upgrade gitpr-cli
```

Le tableau de bord des métriques a désormais deux onglets : `gitpr metrics dashboard` s'ouvre sur **Events** et `2` bascule vers **Charts & Trends**, où l'histogramme vertical de latence et la série temporelle de tokens sont dessinés dans le terminal. `1` revient au tableau des événements, `F5` rafraîchit et `Esc` quitte.

Pour exprimer le coût dans votre devise, définissez `GITPR_METRICS_CURRENCY` (par exemple `EUR`) et renseignez les champs de prix `INPUT`/`OUTPUT` par modèle ; le tableau de bord et `gitpr metrics` lisent les mêmes valeurs.

Pour installer l'extension de l'éditeur, téléchargez le `.vsix` depuis GitHub Releases (tag `ext-v*`) et installez-le dans VS Code ou Cursor — sans compte marketplace.

## Astuces utiles

`gitpr metrics dashboard` ouvre une TUI locale avec coût, qualité, modules, providers et temps de cycle des pull requests (F5 rafraîchit, Esc quitte), et `gitpr metrics export` n'écrit en CSV et JSON que les lignes jamais exportées. Pas de serveur : regroupez une fenêtre avec `gitpr metrics bundle` et fusionnez les fichiers de l'équipe avec `gitpr metrics merge` pour boucler un trimestre sans quitter votre disque.
