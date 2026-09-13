# GitPR 1.1.0 — Nouveautés

## Nouveautés de cette version

- **`gitpr config` — la TUI de configuration interactive :** Un écran maître-détail au-dessus de `~/.gitpr/.env` avec un menu latéral de catégories, l'édition des champs en ligne, une recherche globale (`/`), `F2` pour enregistrer, `Ctrl+R` pour restaurer et `Esc` pour abandonner. Un schéma déclaratif (`src/config_schema.py` — 12 catégories, 56 `ConfigField`, 8 avancés) est l'unique source de vérité : menu, widgets, défauts et validation en dérivent tous, si bien qu'ajouter un réglage est devenu un changement de données, et non d'interface.
- **Une section Skills dans la TUI :** La première surface à périmètre projet de l'écran édite les fichiers `.gitpr/skill/*.md` de votre projet de manière atomique (en préservant CRLF/LF) dans la même passe `F2` qui écrit le `.env`, avec un compteur unique de modifications en attente.
- **Journal général d'utilisation :** Une ligne par commande dans `~/.gitpr/logs/<date>.log` — un fichier par jour, écriture synchrone, sans jamais afficher et sans jamais lever d'exception. Il répond à « qu'ai-je réellement exécuté, et quand ? ». Contrôlé par `GITPR_SHOW_LOGS`.
- **Correction de la langue des Git Hooks :** La langue que vous avez choisie est désormais réellement respectée — `HOOK_SCRIPT_SUFFIXES` fait correspondre les codes d'interface (`es_es`, `fr_fr`) aux suffixes publiés (`.es`, `.fr`), et `--lang` n'est plus ignoré.
- **Distribution exclusive via PyPI avec barrière de mise à jour obligatoire :** Le canal binaire a disparu — aucune génération, aucun upload, aucun fallback. `enforce_update_required()` bloque l'exécution avec le code de sortie 1 lorsqu'une version plus récente est publiée et affiche la commande exacte à exécuter.
- **i18n étendue à 955 clés :** +213 clés couvrant la TUI de configuration et la barrière PyPI, avec une parité totale des key sets dans les 6 dictionnaires (`__lang_version__` v0.0.25).
- **2 nouvelles familles de documentation en 5 langues :** `config-tui` et `usage-log`, plus 7 sujets mis à jour (`ARCHITECTURE`, `auto-update`, `hooks-versioning`, `mcp-integration`, `skill-template`, `testar_sem_usar_pypi`, `version-markers`).
- **Clé morte supprimée :** `PR_AUTO_PUBLISH` s'est révélée inutilisée et a été retirée — si une installation plus ancienne conserve encore la ligne, elle apparaît simplement en lecture seule sous *Inconnues*.
- **Version 1.1.0 :** `__version__` passe de 1.0.0 à 1.1.0, et `CHANGELOG.md` enregistre `[1.1.0] - 2026-09-13`, généré par `gitpr release` lui-même.

## Comment l'utiliser

GitPR 1.1.0 est distribué exclusivement via PyPI — le canal binaire a été retiré :

```
pip install --upgrade gitpr-cli
```

Attention : à partir de cette version, GitPR interroge PyPI à chaque exécution et **bloque l'exécution** si une version plus récente est publiée, en indiquant exactement quoi lancer. La vérification est mise en cache par jour, `--update` se contente de signaler, et `GITPR_SKIP_UPDATE_CHECK` désactive la barrière pour l'automatisation hors ligne.

Configurez tout depuis la nouvelle TUI — sans éditer `~/.gitpr/.env` à la main :

```
gitpr config            # écran interactif de configuration (F2 enregistre)
```

Modifier les instructions IA de votre projet fait partie de ce même écran : la section Skills écrit les `.gitpr/skill/*.md` dans la même passe d'enregistrement.

Vous voulez savoir ce que vous avez réellement exécuté, et quand ? Chaque commande est ajoutée à un journal quotidien :

```
~/.gitpr/logs/<date>.log    # un fichier par jour ; désactivez avec GITPR_SHOW_LOGS=false
```

Des corrections de langue sont également arrivées : `--lang` s'applique désormais aussi à vos Git hooks, donc les scripts de hook suivent la langue que vous avez choisie.

## Astuces utiles

Équipe multilingue ? `gitpr --lang <code>` remplace la langue pour une seule exécution : `gitpr -c --lang en`, `gitpr -r --lang pt_br`, `gitpr -ch --lang fr`. GitPR parle 5 langues et détecte automatiquement la locale du système au premier lancement — et depuis la 1.1.0, ce choix atteint aussi vos Git hooks.
