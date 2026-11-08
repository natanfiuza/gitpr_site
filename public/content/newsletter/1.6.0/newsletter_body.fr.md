# GitPR 1.6.0 — Nouveautés

## Nouveautés de cette version

- **Le Provider SCM est devenu une propriété du dépôt, plus de la machine :** `src/scm_settings.py` résout, dans une seule fonction, **quel Provider, quel jeton et quelle base URL** valent dans ce dépôt — override du projet → remote `origin` → `github`. Chaque forge a gagné son propre slot d'identifiant dans `~/.gitpr/.env` (`GITHUB_/GITLAB_/BITBUCKET_/AZURE_DEVOPS_TOKEN_ENCRYPTED` + `*_BASE_URL`), et les clés héritées `GITPR_SCM_PROVIDER`/`GITPR_SCM_TOKEN_ENCRYPTED`/`GITPR_SCM_BASE_URL` sont migrées **une seule fois, de façon idempotente** ([ADR-016](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-016-per-provider-scm-settings.md)).
- **La clôture automatique d'issue a cessé d'être réservée à GitHub :** GitLab et Bitbucket implémentent la relecture du contrat (`get_issue`, `get_default_branch`, `closing_line`) et Azure DevOps se dégrade en `not-supported` au lieu d'ignorer la fonctionnalité. Le registre local est passé à `.gitpr/conf/issue.yml`, avec lecture transparente du `github.yml` hérité et `IssueInfo.is_open` ([ADR-010](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0010-multiforge-issue-autoclose.md)).
- **Le bootstrap de la configuration locale est devenu une commande :** `gitpr seed` (et le flag `-sd/--seed`) sème `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` et `.gitpr/skill/.gitpr.linter.yml`, avec `--force` pour écraser et `--all` pour inclure toutes les skills IA. L'assistant d'installation a été réorganisé en **5 étapes**, en commençant par la configuration locale du projet.
- **La TUI de configuration a gagné deux surfaces nouvelles :** la catégorie **Smart Excludes** (liste des sources + éditeur de fichier du projet) et la catégorie **check** (les 9 `GITPR_CHECK_*`). Et le `DEFAULT_CONFIG` a cessé d'être une copie manuelle — il est désormais **dérivé du schéma** via `ConfigField.seeded`, si bien que code et schéma ne peuvent plus diverger.
- **Le dashboard a gagné tendance et latence, et le prompt a cessé de recevoir des déchets binaires :** `metrics` rend désormais **Token Trend** (sparkline + barres par jour, plafond de 14 jours) et **Latency Histogram** (moyenne, médiane et P95) dans le CLI, la TUI et l'outil MCP `get_usage_metrics` ; `collapse_binary_hunks()` réécrit les hunks noyés de `U+FFFD` en `Binary files … differ`, et le catalogue de smart excludes a gagné **100+ motifs** dans 8 familles binaires.
- **La suite ne se fige plus, et la 1.5.0 est sur PyPI :** le `-c` n'ouvre la `CommitApp` que lorsqu'il y a un vrai TTY (`_may_ask_the_user()`), ce qui **a débloqué** le scénario qui figeait l'exécution complète à ~33%, et le test headless du commit a été stabilisé. La suite s'exécute désormais jusqu'au bout — **3422 scénarios dans 159 fichiers**. La **1.6.0** est dans le `CHANGELOG.md` et sur la branche `develop_natan`, mais **n'a encore ni tag ni publication**.

## Comment l'utiliser

La 1.6.0 est dans `CHANGELOG.md` et sur la branche `develop_natan`, mais n'a pas encore de tag ; au bouclage de cette édition, PyPI sert la 1.5.0. Dès que la `v1.6.0` sera publiée, mettez à jour avec :

```
pip install --upgrade gitpr-cli
```

Pour créer la configuration locale du projet de façon explicite et idempotente, lancez `gitpr seed` (ou `gitpr -sd`). La commande écrit `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` et `.gitpr/skill/.gitpr.linter.yml`, n'écrase jamais un fichier existant et accepte `--force` pour écraser et `--all` pour télécharger aussi toutes les skills IA.

Pour figer la forge par dépôt, versionnez la section `scm` de ce fichier avec le projet :

```yaml
scm:
  provider: gitlab
```

GitPR n'écrit `scm.provider` que lorsque la forge choisie diffère de celle détectée dans `origin` ; l'override du projet l'emporte sur la détection par le remote. Les jetons vivent dans `~/.gitpr/.env`, un slot par forge, et les clés héritées `GITPR_SCM_*` migrent d'elles-mêmes à la première exécution.

## Astuces utiles

GitPR publie vers quatre forges, pas une : GitHub, GitLab, Bitbucket Cloud et Azure DevOps. Le forge est détecté automatiquement depuis l'URL d'`origin`, et `gitpr --init` guide la configuration — confirme le provider, demande les extras (organisation et projet Azure, nom d'utilisateur Bitbucket), valide le token jusqu'à trois fois et n'écrit ensuite la credential chiffrée Fernet dans `~/.gitpr/.env`. Un GitLab ou Azure auto-hébergé n'a besoin que de `GITPR_SCM_BASE_URL`.
