# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.24 (2026-11-10)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour automatiser les processus Git à l'aide d'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). Son objectif principal est d'agir comme un assistant intelligent local qui réalise des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte les meilleures pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.24) :**
- **Le tableau de bord de métriques a gagné des graphiques ASCII et une navigation par onglets :** le nouveau `src/ascii_chart.py` dessine un histogramme vertical de latence et une série temporelle de tokens avec des fonctions pures, sans nouvelle dépendance ; `MetricsApp` a gagné les onglets **Events** et **Charts & Trends**, avec les raccourcis `1`/`2` ([ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
- **Le prompt est devenu plus résistant aux binaires :** `collapse_binary_hunks()` s'exécute désormais **aussi** au début de `generate_pr_content()`, et le catalogue d'exclusions intelligentes a gagné `*.arrow`, `*.gguf`, `*.tflite` et `*.keras` — les artefacts ML/données n'atteignent plus l'IA même lorsqu'ils arrivent par un appelant externe.
- **La publication de l'extension a été découplée du reste de la version :** `extension.yml` a gagné le job `github-release` (`.vsix` attaché aux releases `ext-v*`) et le job non bloquant `publish-marketplaces`, qui évalue `VSCE_PAT` et `OVSX_PAT` **indépendamment** et rapporte dans `$GITHUB_STEP_SUMMARY`. La documentation d'installation du VSIX (CLI et GUI) est entrée dans les 5 langues.
- **Le coût et la devise ne sont plus fixes :** le schéma a gagné le type `KIND_FLOAT`, la clé `GITPR_METRICS_CURRENCY` et **huit** champs de prix `INPUT`/`OUTPUT` par modèle ; `DEFAULT_MODEL_PRICES` tarife désormais aussi `gemini-pro-latest` et `gemini-flash-lite-latest`.
- **Les notes de migration ont été formalisées :** la rupture de `action.yml` vers `gitpr check` ([ADR-0011](docs/adr/0011-action-unified-check-migration.md)), le commit interactif sans TTY, le `GITPR_SKIP_UPDATE_CHECK` strict et le seed/opt-out de la configuration de projet en CI disposent désormais d'une documentation explicite.
- **Trois nouveaux ADR ont clos des contrats en attente :** [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md) (fallback de langue du site), [ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) (graphiques/onglets) et [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) (ségrégation entre l'auto-seed d'exécution et le provisionnement explicite).
- **1.6.0 et 1.7.0 ont été publiées :** PyPI sert **1.7.0**. La dette de release de la fenêtre précédente est soldée, et `develop_natan` a été fusionnée dans `main` par la PR #310.
- **Version courante :** 1.7.0 (**publiée sur PyPI** ; le tag `v1.7.0` sur l'origine pointe encore vers le commit de pré-bump — voir Distribution)
- **Version du dictionnaire de langue :** v0.0.42 (🆕 +1 depuis v0.0.41 ; livre les 15 nouvelles clés par OTA)
- **Version des scripts de hook :** v0.0.4 (inchangée)
- **Distribution :** PyPI (`pip install gitpr-cli`) — canal binaire supprimé
- **Site :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licence :** LGPL-2.1
- **Langues prises en charge :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues, 6 dictionnaires)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **Framework CLI :** Click (pour les commandes, les flags et le formatage du terminal) — **16 sous-commandes** (`badge`, `baseline`, `check`, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `seed`, `split`, `tests`). Quatre d'entre elles sont des **groupes** : `baseline` (6 sous-commandes), `metrics` (8), `policy` (7) et `tests` (1). **34 options Click à la racine** — inchangées dans cette fenêtre.
* **UI/Terminal :** Textual — TUI pour le chat interactif, l'édition d'issues, l'écran d'aide, le tableau de bord de métriques, le PR Publisher, **CommitApp** (`src/ui/commit_app.py`, 220 lignes), les erreurs de linter (`LinterApp`), la configuration (`ConfigApp`), le modal `NoticeScreen`, la visite `gitpr demo` (`src/ui/demo/`), l'assistant de migration du ledger (`src/ui/metrics_migration_app.py`) et `PrBaseApp` (`src/ui/pr_base_app.py`). 🆕 `MetricsApp` utilise désormais `TabbedContent` avec les onglets **Events** et **Charts & Trends** et les raccourcis `1`/`2`.
* **Architecture en Couches :** `src/domain/` (règles pures, sans I/O) + `src/application/use_cases/` (orchestration, **15 cas d'usage**) + présentation (CLI/TUI). **9 paquets de domaine** : `baseline`, `check`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`. Hors du domaine : `src/issue_autoclose/` (flux de clôture d'issues via `ScmProvider` et le registre local), `src/project_config.py` (configuration versionnée), `src/scm_settings.py` (résolution de forge et de credentials par dépôt) et 🆕 `src/ascii_chart.py` (rendu 2D pur, sans I/O).
* **Extension d'Éditeur :** `extensions/vscode/` — client MCP TypeScript léger pour **VS Code et Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). Ne contient aucune règle métier. 🆕 Le workflow sépare la publication du VSIX (job `github-release`) de la barrière granulaire de publication sur les marketplaces.
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés d'API, des tokens GitHub et des tokens SCM des forges.
* **Configuration :** `python-dotenv`, `pyyaml` (linter statique et configuration de projet) + son propre schéma déclaratif (`src/config_schema.py`, 🆕 **109 `ConfigField`** et **18 catégories**). `DEFAULT_CONFIG` est **dérivé** des champs avec `seeded=True` (🆕 **56 clés**). 🆕 Le type `KIND_FLOAT` a été ajouté pour les prix et la devise. `packaging` alimente la comparaison de version de la barrière PEP 440 et la compatibilité des policy packs.
* **Fournisseurs d'IA :** Intégration via le SDK officiel Google GenAI, le SDK OpenAI (DeepSeek) et le SDK OpenAI (Ollama local). Valeurs par défaut configurées : `gemini-pro-latest` / `gemini-flash-lite-latest` et `deepseek-v4-pro` / `deepseek-v4-flash`.
* **API des Forges :** `requests` (REST) — abstraction multi-forge dans `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py` ; `src/github_api.py` conservé comme shim déprécié. Le contrat d'issue (`get_issue`, `get_default_branch`, `closing_line`) est implémenté par les **quatre** forges là où cela a du sens.
* **Linter / SAST :** YAML regex + pont Checkstyle + ruleset de secrets embarqué (`src/security_ruleset.py`) + 4 ponts dans `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), avec un modèle normalisé dans `src/domain/linter/sast_finding_mapper.py` et le constat canonique dans `src/domain/finding/`.
* **Infrastructure CI :** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) et `masking.py` (masquage de credentials avant la sérialisation de tout artefact).
* **Internes de Git :** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py`, `risk_history_reader.py` et `test_matcher.py`.
* **Persistance Locale :** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) avec WAL, `PRAGMA user_version`, clés UUID et transport par bundle. La table de prix vit dans `src/config.py` (`DEFAULT_MODEL_PRICES`) et accepte désormais des surcharges par modèle et de devise.
* **Réseau :** `src/net.bounded_urlopen` (`UPDATE_FETCH_HARD_TIMEOUT = 3.0`) borne le **temps réel, DNS inclus**, dans la barrière de mise à jour. `get_issue()`/`get_default_branch()` utilisent un timeout de **5 s** (`GITPR_ISSUE_AUTOCLOSE_TIMEOUT`).
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 outils annotés**, **19 ressources**, 7 prompts (comptage inchangé ; aucun fichier de serveur n'a changé dans cette fenêtre).
* **Tests :** Pytest + `unittest.mock` (**160 modules de test** — 64 à la racine, 12 dans `tests/application/use_cases/`, 10 dans `tests/fix/`, 10 dans `tests/scm/`, 10 dans `tests/infrastructure/` (baseline 1, ci 3, git 2, linter 4), 21 dans `tests/domain/` (baseline 5, check 3, finding 1, linter 2, mentor 1, policy 4, pr 1, risk 2, tests_generation 2), 7 dans `tests/badge/`, 6 dans `tests/demo/`, 6 dans `tests/split/`, 5 dans `tests/issue_autoclose/`, 4 dans `tests/review/`, 3 dans `tests/integration/`, 2 dans `tests/unit/` —, **3436 scénarios collectés**) + tests e2e du serveur MCP via un vrai sous-processus (JSON-RPC stdio) + fixtures de vrais dépôts git (`tests/fix/`, `tests/split/`, `tests/test_full_diff_encoding.py`) + gardes d'isolation réseau (`tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py`, `tests/split/conftest.py`). L'extension dispose de sa propre suite Mocha (7 tests contre le vrai serveur MCP).
* **Packaging :** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}` ; packs embarqués via `package-data` ; `packaging>=23.0`. L'extension est packagée séparément (`vsce package`, VSIX).
* **CI/CD :** GitHub Actions — `pr-review.yml` + `action.yml` (appelle `gitpr check`) + `tests.yml` (matrice Python 3.10/3.13) + `extension.yml` (build, test et packaging du VSIX ; 🆕 sa propre release sur `ext-v*` et une barrière granulaire par marketplace) + `release.yml` (tests, vérification tag/version via SSOT, build sdist/wheel, `twine check --strict`, Trusted Publishing OIDC vers PyPI et création de la GitHub Release sur les tags `v*`).

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec le LLM en demandant une réponse strictement JSON.
* **Map-Reduce (Diffs Géants) :** Lorsque le diff dépasse ~90k tokens, il est automatiquement découpé en lots par fichier (`split_diff_into_chunks`), chaque partie est traitée (Map) et les résumés sont unifiés (Reduce). Prend en charge les PR, les commits et les Issues. Les `provider`/`model` sont agrégés entre les lots et les `meta`/`modules` sont transmis à `log_command_metric`.
* **Tokenizer Local :** `tokenizer.json` pour une estimation précise des tokens avant l'envoi à l'IA.
* **Estimation de Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()` avec repli sur le tokenizer local.
* **Optimisation Git Native :** Flags `-U1`, `-w`, `-M`, `-B` dans les commandes `get_git_diff` et `get_git_full_diff`.
* **`get_git_staged_diff()` :** exécute `git diff --cached -U1 -w -M -B -- <SMART_EXCLUDES>` avec `encoding="utf-8"`/`errors="replace"` et `stdin=DEVNULL`, en renvoyant `None` quand git échoue (jamais une chaîne vide déguisée). C'est la source préférée du message de commit.
* **`get_split_diff()` :** le diff propre à `split`, avec `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **délibérément sans** réutiliser `get_git_diff()`.
* **Garde de Hunks Binaires :** chaque point d'entrée de diff (`get_git_diff`, `get_git_staged_diff`, `get_unstaged_diff`, `get_git_full_diff`, `current_diff`, `get_check_diff`, `tests_generate`, `risk`) applique `collapse_binary_hunks()` — les hunks avec des séries contiguës de ≥8 `U+FFFD` (ou ≥32 avec une densité ≥10 %) deviennent `Binary files … differ`. Le chemin rapide O(1) renvoie le diff intact quand il n'y a pas de `U+FFFD`, préservant les fichiers Latin-1/Windows-1252 légitimes.
* **🆕 Collapse Défensif dans `generate_pr_content()` :** la fonction applique désormais `collapse_binary_hunks(diff_text)` **avant** la mesure de temps et l'appel à l'IA, couvrant aussi les appelants externes qui livrent le diff brut — pas seulement le chemin venu de git.
* **🆕 Quatre Nouvelles Extensions Binaires :** `*.arrow`, `*.gguf`, `*.tflite` et `*.keras` sont entrées à la fois dans le repli des exclusions intelligentes embarquées et dans `templates/gitpr.smart-excludes.json`.
* **Seed de Configuration Locale (`seed_project_configuration()`) :** crée `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` et `.gitpr/skill/.gitpr.linter.yml`, en renvoyant un résumé structuré (`config`, `smart_excludes`, `linter`, `skills_downloaded`). Il n'écrase jamais sans `force=True`.
* **Seed de Configuration de Projet :** le callback racine et `--install` appellent `ensure_project_config()` (`src/project_config.py`) à chaque invocation dans un dépôt, sauf sur les chemins d'aide. Un fichier existant n'est **jamais** écrasé ; la création n'est annoncée que sur le chemin interactif de la racine et peut être désactivée par `GITPR_SKIP_PROJECT_CONFIG_SEED` (échappatoire pour la suite de tests).
* **`get_base_branch(quiet=)` :** la résolution de base délègue à `resolve_pull_request_base()` ; `quiet` empêche l'avertissement de repli de corrompre la TUI ou le flux JSON-RPC.
* **Pré-Save (`--pre-save`) :** Flag de debug caché qui sauvegarde le payload complet en JSON avant chaque appel à l'IA.
* **Exclusions Intelligentes à Deux Couches :** Couche globale (`~/.gitpr/conf/`) + locale au projet (`./.gitpr/conf/`), fusionnées à l'exécution. Les chargeurs global et local ont été durcis contre les `.env` corrompus et les chemins illisibles.
* **Métriques avec Suivi du Temps :** `log_command_metric()` dans tous les flux, avec `meta`, `modules` et `duration_ms`.
* **Résolution de Sortie Centralisée :** `resolve_output_path()` — par défaut `.gitpr/reports/{type}/`.
* **Assistant SCM (`run_scm_init_wizard()`) :** `gitpr --init` — détecte la forge à partir du remote d'origine, demande les extras par forge, valide le token et persiste **uniquement en cas de succès** ; il écrit dans le slot de la forge choisie et ne crée une surcharge de projet que lorsque cette forge diffère de celle détectée.
* **Modèle de Skill de Release (`ensure_release_skill_template()`) :** Télécharge `templates/gitpr.release.*.md` au premier usage de `gitpr release`.
* **Garde de Skill Corrompu :** `_CORRUPTED_PR_SKILL_ARTIFACT` documente le fragment JSON qui a fuité dans le modèle `pt_br`, et `get_skill_context()` le supprime **en mémoire** quand `action_type == "pr"`.
* **Registre de Skills Partagé :** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` dans `src/config.py`.
* **Moteur de Review avec Portée de Cache (`generate_pr_content`) :** `cache_scope` et `store_diff` ; le `cache_scope` porte aussi le policy pack actif.
* **Trailer de Co-autorat :** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, préserve les trailers de tiers.
* **Capture de Sortie Git Durcie :** les appels `subprocess.run` dans `src/cache.py`, `src/core.py`, `src/issue_engine.py` et `src/main.py` qui capturent la sortie git utilisent `encoding="utf-8"` **avec** `errors="replace"`.
* **Chargement Résilient de `.env` :** `load_dotenv()` dans `core.py`, `i18n.py` et `spinner.py` s'exécute dans un `try/except`.
* **Échec Git ≠ Diff Vide :** `diff_text is None` sort avec le code 1 ; un diff réellement vide sort avec **0** ; en `--hook`, le chemin est fail-open.
* **Sous-processus Blindés :** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` dans chaque `subprocess.run` ; vérification de connexion par socket avant les opérations réseau.

### **2. Système Global de Plugins (`src/plugins.py`)**

* **Architecture de Plugins :** Charge des plugins depuis `~/.gitpr/plugins/` s'appliquant à **tous les projets**.
* **Plugins de Linter (`linter/`) :** fichiers `.yml` fusionnés avec le `.gitpr.linter.yml` local.
* **Plugins de Prompts MCP (`prompts/`) :** fichiers `.md` qui étendent le contexte système.
* **Closures de Fabrique :** `get_linter_plugins` et `get_prompt_plugins` isolent l'état entre les sessions.
* **Commande `--plugins` :** Liste tous les plugins globaux installés avec leurs types et chemins.
* **Documentation Multilingue :** `docs/plugins-system.md` en 5 langues.
* **Relation avec les Policy Packs :** le pack est une **troisième** couche de règles — les plugins appartiennent à la machine, `.gitpr.linter.yml` appartient au projet et le pack est versionné et commité.

### **3. Interface CLI et Configuration (`src/main.py` et `src/config.py`)**

* **Configuration Initiale :** Détecte la première exécution, crée `~/.gitpr/` et demande interactivement les clés, les préférences et la langue.
* **Routage des Commandes :** Gère tous les flags et les **16 sous-commandes** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline`, `check` et `seed`.
* **Comportement par Défaut :** Lancer `gitpr` sans flag ouvre la TUI PR Publisher.
* **Flags (34 options Click à la racine) :** `-sd/--seed` seed la configuration locale du projet et sort ; `--close-issue`/`--no-close-issue` restent mutuellement exclusifs, refusés avec le code de sortie 1 avant tout prompt ou écriture, avec la règle que `-h` gagne le conflit. Les autres (`--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` etc.) restent inchangés.
* **Branche `-c`/`--commit` :** `--hook` → comportement silencieux et merge-aware ; sans hook → `CommitApp`. Le chemin sans hook exige un TTY : le routage vérifie `_may_ask_the_user()` et, dans un script/CI/pipe, retombe sur le flux console.
* **Flux de Clôture d'Issue :** `_prepare_close_issue(flags, base, may_ask)` s'exécute **avant** le Stage Selector, réutilise le contexte SCM résolu et renvoie la décision (ligne de clôture + issue) ainsi que l'entrée du registre. La ligne entre dans `insert_close_line(pr_desc_body, decision)` au point unique d'assemblage du corps. `_publish_pr_directly` renvoie un **bool** afin que l'entrée du registre ne soit effacée qu'après une publication réelle (ADR-007).
* **Résolution de la Base de PR :** la branche `--base` passe par `_resolve_pr_target()`, qui répare une valeur obsolète du fichier de projet quand l'exécution est interactive et, hors de ce cas, signale le refus. Le fichier `.gitpr/conf/gitpr.config.yml` est seedé à chaque invocation dans un dépôt, sauf sur les chemins d'aide.
* **Barrière de Mise à Jour dans les Sous-commandes :** la barrière s'exécute avant le `return`, conditionnée par `may_ask and not update and not help_flag and not _subcommand_help_in_argv()`. `_may_ask_the_user()` exige un **TTY aux deux extrémités** — CI, pipes, cron, hooks et le serveur MCP restent libres.
* **`-h` Avant la Sous-commande Corrigé :** `_subcommand_help_in_argv()` parcourt l'`argv` brut et gère le cas `gitpr -h config`, qui ouvrait auparavant la TUI de configuration au lieu de l'aide. Couvert par `tests/test_cli_help.py`.
* **Sous-commande `check` — 8 options :** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on …`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` et `--policy <name>@<version>`.
* **Sous-commande `seed` — 2 options :** `--force/-f` (écrase les fichiers existants) et `--all/-a` (télécharge aussi toutes les skills IA).
* **Groupe `baseline` — 6 sous-commandes :** `create`, `show`, `validate`, `update`, `suppress` et `unsuppress`.
* **Groupe `policy` — 7 sous-commandes :** `list`, `show`, `validate`, `use`, `off`, `install` et `init`.
* **Groupe `metrics` — 8 sous-commandes :** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` et la `hook-event` cachée.
* **Sous-commande `risk` — 4 options :** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Sous-commande `mentor` — 2 options :** `--finding`, `--provider`.
* **`DEFAULT_CONFIG` Dérivé :** `src/config.py` importe `DEFAULT_CONFIG` depuis `src/config_schema.py`, construit comme `{field.key: field.default for field in FIELDS if field.seeded}` — 🆕 **56 clés** (auparavant 55). Une dérive entre le code et le schéma est impossible.
* **🆕 Prix et Devise dans le Schéma :** `GITPR_METRICS_CURRENCY` (par défaut `USD`) et huit champs `GITPR_METRICS_PRICE_<MODEL>_{INPUT,OUTPUT}` ont été ajoutés à la catégorie `ai`, tous avec `KIND_FLOAT` et une valeur par défaut vide.
* **Variables d'Environnement :** les quatre `GITPR_BASELINE_*`, les deux de mentor, les deux de policy et `GITPR_RISK_INCLUDE_IN_REVIEW`. **Hors de `DEFAULT_CONFIG` :** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY`, les **9 `GITPR_CHECK_*`**, `GITPR_SKIP_UPDATE_CHECK` (booléen strict) et `GITPR_ISSUE_AUTOCLOSE`, `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` et `GITPR_SKIP_PROJECT_CONFIG_SEED`.
* **Aide Contextuelle :** `gitpr -h --explain` s'affiche ; `HELP_MAP["explain"]` et `HELP_PRIORITY["explain"]` pointent vers `docs/explain-pr`, et `gitpr tests`/`gitpr tests generate` ont leur propre `epilog=`. `HELP_MAP` inclut `seed` et les liens `close_issue`/`no_close_issue` pointent vers `docs/issue-autoclose`.
* **Couche d'Écriture `.env` :** `read_env_file_values()` lit **uniquement le fichier** via `dotenv_values` ; `save_config_values()` avec `set_key` ; `remove_config_value()` avec `unset_key`.
* **Barrière de Baseline dans le Flux :** `main.py` résout la barrière de baseline avant le lint, la review, la review complète, l'audit de fichiers, la review distante et le risque.
* **Publication Directe Refactorisée :** `_publish_pr_directly` (`--no-edit`) délègue au **`publish_pull_request_use_case`** — le même chemin que l'outil MCP `publish_pull_request` — et renvoie si la forge a créé la PR.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)**

* **Interface Interactive Complète :** TUI pour relire, éditer et publier des Pull Requests directement depuis le terminal.
* **7 Écrans Modaux :** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` et `NoticeScreen`.
* **Trois d'entre Eux Servent Aussi au Commit :** `StageFilesScreen`, `CommitMessageScreen` et `CommitProgressScreen` sont importés par `CommitApp` — aucun widget n'a été dupliqué, et l'helper `_with_suppressed_stdout` est partagé lui aussi.
* **`body_published` Distingue la Création de l'Ouverture :** `PrPublishApp` marque quand le corps a réellement été créé/mis à jour sur la forge, et non simplement quand une PR existante a été ouverte. C'est le signal qui permet d'effacer l'entrée du registre d'issues uniquement après une publication réelle.
* **Badge dans le Corps de la PR :** `attach_pr_badge()` s'exécute dans `src/main.py` juste après que `pr_data` est complet — **point d'injection unique**.
* **`_attach_reviewers` avec résolution :** Résout ce que l'utilisateur a saisi **avant** l'envoi, signale ce qui a été écarté et, sur un `422` de lot, **réessaie un par un**. Quand un Provider tiers suit le contrat legacy et renvoie `None` à la relecture, le log indique `(no read-back)` au lieu de `attached None`.
* **Reviewers Suggérés :** Interroge la forge pour les reviewers suggérés ; `_reviewer_suggestion_view()` construit les `resolutions` via `resolve_candidates`.
* **Raccourcis :** F1 (Aide), F2 (Sauvegarde locale .md), F3 (Publication via forge), Esc (Quitter).
* **Flux Auto-Commit :** Linter → message IA → confirmation → commit → push → publication de la PR.
* **Modal de Progression du Merge :** `CommitProgressScreen` avec un `title` optionnel et `mark_finished(show_close=False)` ; le succès affiche `✅ PR #N merged successfully!`, les conflits et erreurs inattendues passent par `_on_merge_failure`.
* **Gestion des PR Existantes / Auto-Upstream / Flux de Merge** — inchangé (`GITPR_AUTO_MERGE`).

### **5. Module API GitHub (`src/github_api.py`)**

* **Shim Déprécié :** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` et les autres fonctions délèguent à `src/infrastructure/scm/github_provider.py` ; il émet un `DeprecationWarning` et conserve les tuples legacy `(ok, data, status)` — aucun nouveau code ne peut l'importer.

### **6. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) du diff sans consommer de quotas IA.
* **Règles YAML :** Lit `.gitpr.linter.yml` (créé via `--skill` ou `gitpr seed`).
* **Plugins de Linter :** Règles supplémentaires depuis `~/.gitpr/plugins/linter/*.yml`.
* **Wildcard d'Extension :** `extensions: ["*"]` signifie **tous** les fichiers, y compris ceux sans suffixe et les dotfiles.
* **Ruleset de Secrets Embarqué :** `load_linter_rules()` fusionne `src/security_ruleset.py` **après** les règles du projet et les plugins. Sept règles, toutes en `extensions: ["*"]` : cinq `error` et deux `warning`. L'alerte **n'affiche jamais la valeur**.
* **Couche Policy Pack :** `load_linter_rules()` applique la fusion en couches du pack actif et **surcharge les sévérités**.
* **Constats Structurés (`findings_out`) :** au-delà des chaînes d'alerte, le moteur renvoie les constats normalisés du type canonique `src/domain/finding/`.
* **Ponts SAST Opt-In :** Semgrep, Gitleaks et Bandit s'exécutent sur le chemin fichier entier **et** sur le chemin diff, avec déduplication `[Gitleaks + Regex]`, masquage des valeurs (`AKIA****`) et un avertissement quand l'outil est activé mais absent du `PATH`.
* **`skip_external` :** `parse_diff_and_lint(..., skip_external=True)` dans le flux distant.
* **Rapport Consolidé :** `generate_linter_report_content()` consolide les erreurs regex + externes dans `.gitpr/reports/linter/` — généré uniquement en présence de violations.
* **Conscient de la Baseline :** les alertes sont annotées avec le statut de baseline avant de devenir un rapport.

### **7. Sécurité et Authentification (`src/security.py`, `src/scm_settings.py`, `src/tui_issue.py`)**

* **Cryptographie :** Génère la clé maîtresse `secret.key` dans `~/.gitpr/`.
* **Protection des Tokens :** `encrypt_data`/`decrypt_data` pour les clés IA et pour le token de **chaque** forge (un slot par Provider SCM).
* **Credential par Forge :** un token GitLab ne répond jamais pour un dépôt GitHub. Le `GITPR_SCM_TOKEN` brut reste la **seule** surcharge globale CI/CD et est essayé en premier sur chaque forge.
* **Validation Multi-Forge :** `validate_or_request_scm_token(provider, repo_display, allow_prompt=True)` — 401 → boucle de réauthentification préservant le brouillon. `allow_prompt=False` (hooks, MCP, CI) ne demande jamais, ne supprime jamais le token stocké et renvoie l'instruction « exécutez `gitpr --init` ».
* **Secrets dans la TUI de Configuration :** les champs `KIND_SECRET` sont édités masqués, n'affichent **jamais** la valeur en clair et sont chiffrés avec Fernet avant l'écriture.
* **Scan de Secrets dans le Flux :** Le linter — qui s'exécute dans le hook pre-commit — est une barrière de credentials avec des règles identiques sur chaque machine.
* **Masquage en CI :** `src/infrastructure/ci/masking.py` supprime les clés privées, l'ID/secret de clé d'accès AWS, les tokens GitHub, Google, OpenAI et Slack, `Bearer`, le basic-auth dans les URLs et les affectations génériques de mot de passe **avant** la sérialisation de l'artefact. L'extension dispose d'un miroir TypeScript (`masking.ts`).

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI comme Source Unique :** `get_latest_remote_version()` interroge `https://pypi.org/pypi/gitpr-cli/json` et écrit le cache. Il accepte `force=True`, utilisé par `--update` pour aller **toujours** sur le réseau.
* **Fenêtre Glissante d'1 Heure (`UPDATE_CACHE_TTL_SECONDS = 3600`) :** le document est `{"checked_at": <ISO>, "version": ...}` ; `_cache_is_fresh()` accepte un âge en `[0, 3600)` secondes.
* **Validation à la Lecture :** un vrai `dict`, un timestamp parsable, une version chaîne non vide. Un timestamp **dans le futur** est considéré comme expiré, et le format legacy avec `date` est traité comme expiré puis réécrit.
* **Écriture Atomique :** `tempfile.NamedTemporaryFile` dans le même répertoire + `os.replace`, avec nettoyage du fichier temporaire en cas d'échec.
* **Récupération Durcie (`bounded_urlopen`) :** un corps non-JSON et un payload sans version sont **rejetés avant d'entrer dans le cache**. Plafond de 3 s de temps réel, DNS inclus.
* **Comparaison PEP 440 :** `_coerce_version()` utilise `packaging.version.Version`, en tolérant le `v` initial et les suffixes PEP 440 ; `parse_version()` conserve le contrat de tuple pour les appelants externes. `_is_newer()` **ignore une pré-version distante** sur une installation stable et traite une version illisible d'un côté ou de l'autre comme inconnue.
* **Barrière Obligatoire (`enforce_update_required()`) :** Renvoie `True` (après avoir affiché les deux versions et la commande pip) quand la version publiée est plus récente ; `False` quand elle est à jour, quand la version distante est **inconnue (hors ligne)** ou quand la vérification est désactivée.
* **Sémantique Stricte de l'Échappatoire :** `GITPR_SKIP_UPDATE_CHECK` est interprété via `_SKIP_TRUTHY_VALUES` (`1`, `true`, `yes`, `on`, `y`). La note de migration de cette sémantique est entrée dans `docs/auto-update.*` et dans le tutoriel d'installation.
* **`check_and_update()` :** Interroge et **informe** uniquement, n'installe jamais.
* **Versionnage Centralisé :** `__version__` (**1.7.0** 🆕), `__lang_version__` (**v0.0.42** 🆕), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`. Le SSOT du paquet est un contrat explicite ([ADR-014](docs/adr/0014-canonical-package-version-ssot.md)).
* **✅ État de la Release — 1.6.0 et 1.7.0 publiées :** PyPI sert **1.7.0**. **1.6.0** a été taguée à `f5088c4` ; **1.7.0** a sa propre section dans `CHANGELOG.md` (`## [1.7.0] - 2026-11-10`) et un bump à `2354a30`. ⚠️ Le tag `v1.7.0` sur l'origine pointe encore vers `b2688c4` (pré-bump, `__version__` 1.6.0) — l'alignement du tag est l'élément de release en attente.
* **⚠️ Cohérence SSOT :** le bump `2354a30` n'a modifié que `src/updater.py` ; `CLAUDE.md` et `GEMINI.md` disent encore 1.6.0, et donc `tests/unit/test_version_consistency.py` a **2 échecs** (`test_claude_md_version_matches_ssot` et `test_gemini_md_version_matches_ssot`).

### **9. Interface de Chat Interactive (`src/ui/chat_app.py`)**

* **TUI Complète :** Historique des messages, saisie multi-ligne, barre de statut avec raccourcis visibles. `ENABLE_COMMAND_PALETTE = False`.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique persisté par branche, avec continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear`. `/tests` (et les alias localisés) délègue au **même cas d'usage** que celui utilisé par la CLI.
* **Auto-Patching (F5), Rafraîchissement du Diff (F2), Export de Session (F6).**
* **Extracteur Partagé :** F5 et `ctrl+s` appellent `patch_extractor.extract_code_blocks()` — comportement visible identique, sans logique dupliquée.
* **Aide Réellement Localisée :** `ChatHelpScreen` assemble chaque ligne via `__()`. Les liens d'aide passent par `doc_url()`, avec le contrat `?lang=` du site (`es_es`→`es`, `fr_fr`→`fr`) — formalisé dans [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md).

### **10. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec des placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système d'exploitation à la première exécution et l'enregistre dans `GITPR_LANG` ; le répertoire de profil est créé **avant** la persistance.
* **5 Langues, 6 Dictionnaires :** en_us (défaut/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Fichiers Versionnés :** `__lang_version__` (**v0.0.42** 🆕) contrôle les mises à jour du paquet (`langs/*.json`).
* **✅ Couverture :** les **six** fichiers ont **1787 clés** chacun (+15). Le code utilise les mêmes clés ; il n'y a ni clé sans dictionnaire ni orpheline. `tests/test_i18n.py` reste **22 au vert**.
* **🆕 v0.0.42 a du contenu :** les nouvelles chaînes pour les prix par token, la devise, la validation float, les graphiques/onglets ASCII et les instructions d'installation du VSIX sont entrées dans les six dictionnaires ; le rafraîchissement OTA livre la nouvelle traduction.
* **Instantané de l'Environnement (`AMBIENT_ENV_KEYS`) :** `frozenset(os.environ)` capturé **immédiatement avant** le `load_dotenv()` au niveau du module.
* **Cache avec Indexation de Langue :** Les réponses IA mises en cache incluent la langue courante dans le calcul de la clé MD5.
* **Source de Traduction en Verrouillage :** une nouvelle clé doit exister dans le code, dans `langs/pt_br.json` (liste maîtresse), dans les dicts FR/ES de `scripts/sync_all_langs.py` et dans les valeurs curatées de `scripts/fix_mangled_i18n_keys.py` ; l'extracteur utilise l'**AST**.

### **11. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA avec des caractères braille et des mots de « réflexion ».
* **263 entrées par langue :** Synchronisées entre les 5 langues. `_load_thinking_words()` / `reload_thinking_words()` acceptent `force=`.
* **La TUI de Commit N'utilise Pas le Spinner :** la TUI affiche `CommitProgressScreen` avec sa propre barre animée, et l'appel IA s'exécute dans un `threading.Thread` daemon. En `--hook`, le spinner classique reste le seul retour visuel.
* **Chargement `.env` Durci :** un échec de lecture n'empêche pas le démarrage du spinner.

### **12. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Pris en Charge :** Google Gemini, DeepSeek et Ollama (local), avec un primaire/secondaire par fournisseur.
* **Mode JSON & Paramètres Déterministes :** `temperature=0.0` et `top_p=0.1` ; repli automatique entre les fournisseurs configurés.
* **Timeout IA :** défaut de **180s** (`GITPR_AI_TIMEOUT`).
* **Appels avec `quiet` :** la release et les nouvelles fonctionnalités transmettent `quiet` jusqu'à la couche IA, afin que le spinner ne corrompe jamais une sortie `--format json`.
* **Un Appel à la Fois, avec Cache :** mentor et explain réutilisent le cache MD5 par prompt.
* **Payload Déjà Parsé :** `parse_explain_payload()` et `generate_test_file` acceptent le **dict** livré par le fournisseur, avec repli sur la chaîne JSON brute (anciennes réponses de cache).
* **Suppression de Stdout dans le Commit :** `_generate_commit_msg()` redirige `sys.stdout` vers `os.devnull` pendant l'appel, en le restaurant dans `finally`.

### **13. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Clé basée sur le hash MD5 du diff et du prompt, avec indexation de langue.
* **Sélection de la Dernière Review (`resolve_last_review()`) :** Choisit l'enregistrement `review`/`fullreview` le **plus récent** pour une paire repo+branche — c'est le point d'entrée de `gitpr fix` et de `gitpr mentor`.
* **Diff Relu (`reviewed_diff`) :** Champ en tête de l'enregistrement qui stocke le diff réellement relu.
* **Télémétrie et Durée :** Persistance de `duration_ms` et `meta_raw`. La release indexe désormais le cache par langue déclarée, de sorte que changer la langue de la skill génère une nouvelle section au lieu de réutiliser l'ancienne.
* **Lecture du Tableau de Bord :** `scan_cache_files_for_dashboard()` est un **pont de pré-migration**.
* **Effet de la Sanitization des Skills :** comme `get_skill_context()` supprime le fragment corrompu du prompt de PR, la clé MD5 change et les anciennes réponses contaminées ne sont plus réutilisées.
* **`git config` Décodé en Toute Sécurité :** les lectures de `user.name`/`user.email` utilisent `encoding="utf-8"` + `errors="replace"`.
* **Le Commit Réutilise le Cache :** la TUI appelle `generate_pr_content("commit", "commit", diff_text, provider)`, de sorte que le même message pour le même diff ne déclenche pas un second appel IA.

### **14. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff courant, historique de branche (`-ht`) et archéologie Blame (`-b`).
* **Publication Multi-Forge :** F3 crée l'issue sur la forge **résolue pour le dépôt** via `provider.create_issue` — Azure DevOps lève `ScmNotSupportedError`.
* **Map-Reduce pour les Issues :** Un contexte au-delà de ~90k tokens est découpé et unifié.
* **Gestion des 401 :** Signalement de réauthentification sans fermer l'application.
* **`git remote -v` Décodé en Toute Sécurité :** l'appel utilise `encoding="utf-8"` + `errors="replace"`.
* **Registre Indépendant du Provider :** après une création réussie sur n'importe quelle forge prise en charge, `register_created_issue()` écrit le numéro **et le Provider** dans `.gitpr/conf/issue.yml`. Les échecs de tenue de registre sont avalés — un problème local ne transforme jamais une création réussie en erreur — et la première écriture ajoute le fichier au `.gitignore`.
* **Métrique par Forge :** l'événement de création est dérivé dynamiquement (`issue:<provider>_create`) au lieu d'être figé sur GitHub.

### **15. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Suit l'évolution et la paternité historique avec classification des commits (`ORIGIN` vs `REFACTORING`).
* **Métriques de Blame :** Événements via `log_blame_metric()` avec profondeur et nombre de commits analysés.
* **Il alimente désormais aussi le risque :** le lecteur d'historique du score (`risk_history_reader.py`) utilise la même fenêtre de 90 jours et classe les bugs/reverts par motif, sans appeler l'IA.
* **Alimente les Suggestions de Reviewer :** l'analyse de blame d'une PR classe les fichiers en `files_with_blame`/`files_without_blame`, consommés par le résumé de la section 27.

### **16. Serveur MCP et Invocation CLI Directe (`src/mcp_server.py`)**

* **18 Outils MCP Annotés :** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates`, `review_remote_pr`, `get_baseline_status`, `get_usage_metrics`, `run_check` et `publish_pull_request`.
* **Paramètres Opt-In dans le Contrat :** `review_code` et `full_review` acceptent `mentor: bool = False` ; `generate_pr_description` accepte `explain: bool = False`. Ils sont **purement par paramètre** — ils ne font jamais un OR avec les défauts du `.env`.
* **19 Ressources + 7 Prompts Templatés :** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Registre de Skills Aligné :** `mcp_server.SKILL_FILES` a **11 entrées** (`tests`, `explain` et `mentor` inclus) ; `TestSkillRegistryAgreement` passe.
* **Invocation CLI Directe :** `gitpr-mcp --tool <name> [--tool-args '<json>']` invoque n'importe quel outil sans démarrer le serveur stdio ; `gitpr-mcp --list` affiche le registre complet en JSON.
* **Isolation du Vrai Stdout :** `_write_real_stdout()` écrit dans le `sys.__stdout__` d'origine, avec repli sur `buffer` ou ré-encodage avec `errors='replace'` sur les anciennes pages de code Windows.
* **Déport de la Boucle d'Événements :** le décorateur `_offload` (`anyio.to_thread.run_sync`) sur chaque outil — l'ordre des décorateurs compte (`@mcp.tool` **au-dessus** de `@_offload`).
* **Échecs en Erreur Structurée :** les outils renvoient l'échec **comme donnée** au lieu de lever une exception.
* **`get_usage_metrics` avec Graphiques :** le payload inclut `token_trend` (fenêtre de jusqu'à 14 jours) et `latency` (moyenne/médiane/P95) — les mêmes chiffres que la CLI et le tableau de bord, désormais aussi affichés en ASCII dans la TUI.
* **Credential par Dépôt :** chaque résolution SCM passe par `src.scm_settings` ; sans token, l'outil répond avec la commande de correction (`gitpr --init`) au lieu d'essayer de demander — un appel d'outil ne peut pas s'arrêter pour attendre l'utilisateur.
* **Métriques MCP :** `run_check` et `publish_pull_request` écrivent des lignes locales (`mcp:check`, `mcp:publish_pr`).
* **Le serveur MCP n'est jamais bloqué par la barrière :** `--mcp`/`gitpr-mcp` figure sur la liste d'exemption depuis la première version de la barrière.

### **17. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`) 🆕 Onglets et Graphiques**

* **Portée par Dépôt :** libellé `📁 Repository: owner/repo` et filtrage strict par projet.
* **Scan Asynchrone avec Overlay :** thread worker en arrière-plan avec `ProgressBar` ; les tests attendent `workers.wait_for_complete()` avant d'asserter.
* **🆕 Deux Onglets :** `MetricsApp` a été réorganisé en `TabbedContent` avec **Events** (les sections textuelles existantes) et **Charts & Trends** (les panneaux de latence et de tokens). Les raccourcis `1` et `2` et `action_switch_tab` sautent entre eux.
* **🆕 Moteur de Graphiques ASCII (`src/ascii_chart.py`) :** fonctions pures, sans Textual et sans nouvelle dépendance — `render_vertical_histogram` (histogramme de latence avec graduations Y, barres pleines/demi-blocs, comptage et pourcentage par tranche), `render_token_timeseries` (série quotidienne avec libellés de date, tokens compacts et une sparkline optionnelle) et `render_empty_card` (état vide encadré), plus les formateurs `_fmt_tokens` (`1.2M`/`45.0k`) et `_duration_ms_label` (`ms`/`s`/`min`).
* **🆕 Une Seule Passe de Lecture :** `_load_sections` renvoie désormais `(rendered_sections, chart_data)`, en capturant les payloads bruts `latency` et `token_trend` pendant le rendu des sections textuelles — sans seconde requête au ledger. `_finish_scan` propage `chart_data` à `_render_sections`, avec un repli qui interroge de nouveau les rapports quand les données manquent.
* **🆕 État Vide Pédagogique :** sans ledger, l'onglet des graphiques affiche une carte pointant vers `gitpr metrics migrate` au lieu d'un panneau vide.
* **Lecture du Ledger :** le tableau de bord interroge `telemetry.db` sur le thread worker, en rendant les sections (coût, qualité, modules, fournisseurs, cycle) depuis les mêmes renderers que la CLI.
* **Assistant de Migration (`src/ui/metrics_migration_app.py`) :** propose d'importer les fichiers legacy, d'importer **et** reconstruire depuis le cache IA, ou « plus tard ».
* **Artefacts d'Export Non Suivis :** `.gitignore` est l'unique source au sujet de `.gitpr/metrics/export/`.
* **Tests :** `tests/test_ascii_chart.py` (**9 scénarios** 🆕) couvre les formateurs, les états vides, l'histogramme/série peuplé, la composition des onglets et le changement d'onglet.

### **18. Système de Métriques et Télémétrie (`src/metrics.py`)**

* **Portée par Dépôt :** Tous les événements indexés par `repo_name`, résolu par `src/infrastructure/git/identity.py`.
* **Événements de Hook, Linter et Blame :** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. `gitpr check` écrit `command="check"` avec `findings_total`/`findings_errors` et le code de sortie.
* **Événement `issue_autoclose` :** une ligne par décision de clôture, avec `asked`, `accepted` et `skipped_reason` dans le `meta`.
* **Tendance de Tokens (`TOKEN_TREND_MAX_DAYS = 14`) :** une série quotidienne continue (les jours sans usage entrent à zéro) avec une sparkline et des barres horizontales par jour ; elle choisit le côté (input/output) le plus mouvementé.
* **Histogramme de Latence :** distribution par tranches avec **moyenne, médiane et P95** ; les événements sans durée mesurée sont écartés afin qu'ils ne tirent ni les statistiques vers zéro ni n'inflent la tranche `< 1s`.
* **`duration_ms` n'est plus perdu :** le ledger utilise `meta["duration_ms"]` comme repli avant d'écrire `0`.
* **🆕 Coût Configurable par Modèle :** `DEFAULT_MODEL_PRICES` (dans `src/config.py`) a gagné les alias `gemini-pro-latest` (**1.25 / 10.00** par 1M tokens) et `gemini-flash-lite-latest` (**0.10 / 0.40**), qui auparavant tombaient en « non tarifé ». Les surcharges `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT` battent toujours la table ; **les deux tarifs sont obligatoires**.
* **🆕 Devise Configurable :** `GITPR_METRICS_CURRENCY` (défaut `USD`) étiquette le total et, quand elle n'est pas USD, écarte la table embarquée au lieu d'étiqueter à tort un prix en dollars. Un total qui laisse des modèles de côté s'affiche toujours `Total (partial)`.
* **Export et Nettoyage :** `gitpr metrics export` (CSV/JSON), `prune`, `purge` avec confirmation interactive, `bundle`/`merge` pour transporter et fusionner des fenêtres.
* **Le Commit Interactif Mesure Aussi :** le chemin `commit` continue d'enregistrer la ligne de commande avec `meta` et `modules`.

### **19. Synchronisation de la Langue des Git Hooks**

* **Versionnage Indépendant :** `__scripts_version__` (**v0.0.4**, inchangée).
* **Modèles Réécrits dans la Fenêtre v0.0.17 :** `post-checkout`, `post-merge` et `pre-push` appellent `gitpr --quiet metrics hook-event <name>`, gardés par `command -v gitpr` et `|| true`.
* **Mapping de Suffixe (`HOOK_SCRIPT_SUFFIXES`) :** Codes UI (`es_es`, `fr_fr`) traduits vers les suffixes publiés (`.es`, `.fr`).
* **Choix vs. État (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`) :** Permet de **détecter un changement de langue**.
* **`effective_hook_lang()` :** Résout la langue effective ; `--lang` n'est plus écarté sur ce chemin.
* **Saut de Merge-Source :** `prepare-commit-msg` saute les sources `message|merge|squash|commit`.
* **`prepare-commit-msg` Reste Non-Interactif :** le hook s'exécute avec `--hook`, donc il n'ouvre **jamais** `CommitApp` — la TUI n'existe que dans l'usage humain de `gitpr -c`, et seulement en présence d'un TTY. 🆕 La note de migration de ce comportement est entrée dans `docs/commit-message-ia.*` dans les 5 langues.
* **Les hooks s'exécutent avec `--quiet` et `--hook`, donc ils ne sont jamais bloqués par la barrière de mise à jour.**

### **20. Pont de Linters Externes et Assistant Interactif (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistant `--linter-setup` :** Assistant avec des presets numérotés (PHP_CodeSniffer, ESLint, Stylelint) et injection du bloc `external_linters`.
* **Presets Distants :** `templates/gitpr.linter-presets.json` avec une chaîne de repli local → téléchargement → obsolète → embarqué.
* **TUI d'Erreurs de Linter :** `src/ui/linter_app.py` (Textual) affiche les erreurs critiques et les avertissements ; en mode hook/quiet, il imprime et appelle `sys.exit(1)`. `ENABLE_COMMAND_PALETTE = False`.
* **Rapport Markdown :** Consolidé dans `.gitpr/reports/linter/` uniquement en présence de violations.
* **La Couche SAST est un Frère, Pas un Remplacement :** les ponts Semgrep/Gitleaks/Bandit alimentent le **même** pipeline de rapports — et aussi `gitpr check`.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstraction Unique (`ScmProvider` ABC) :** `base.py` définit le contrat (dataclasses `RepoRef`, `PullRequestDraft` etc. et `ScmProviderError(provider, http_status, message)` — `http_status` 0 = échec réseau).
* **`IssueInfo` avec `is_open` :** le flag booléen supprime les comparaisons fragiles d'état sous forme de chaîne de la couche de décision.
* **Les Opérations d'Issue Appartiennent à Chaque Forge :** GitLab normalise `opened` → `open` et émet `Closes #N` ; Bitbucket normalise `new`/`open`/`on hold` → `open` et émet `close #N` ; Azure DevOps lève `ScmNotSupportedError` (les Work Items ne sont pas des issues).
* **`list_pull_requests(repo, state, since, until)` — paginé et canonique :** les quatre providers traduisent les états canoniques (`open`/`merged`/`closed`/`all`) vers le vocabulaire natif, paginent sur le curseur de chaque API et filtrent par une fenêtre de dates inclusive.
* **Pourquoi le filtre est par date d'état :** trois des quatre forges renvoient quelque chose de plus large pour une PR mergée, donc chacune filtre sa propre réponse contre la date impliquée par l'état.
* **`supports_merged_dates` :** attribut de classe, `False` sur Bitbucket Cloud, qui publie `merged_at` **vide**.
* **`web_links.py` :** fonctions pures (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) qui normalisent n'importe quel format de remote.
* **`get_pull_request(repo, pr_id)` — méthode concrète de l'ABC :** la valeur par défaut lève `ScmNotSupportedError` et chaque forge l'implémente.
* **Résolution par Dépôt :** le registre ne lit pas `GITPR_SCM_PROVIDER` ; `resolve_scm_provider(config)` reçoit le Provider déjà résolu par `src/scm_settings.py` (section 46).
* **GitHub :** `GET /repos/{o}/{r}/issues/{n}` (marque `pull_request` et retombe sur le numéro demandé en son absence), `GET /repos/{o}/{r}` pour `default_branch` (chaîne vide en son absence) et le mot-clé `close #N`.
* **En-têtes GitLab Synthétisés :** `changes[].diff` est un hunk lâche ; `old_path`/`new_path` construisent les en-têtes `diff --git a/… / --- / +++`, en respectant `/dev/null`. Et `overflow: true` **lève** une erreur au lieu de relire la moitié d'un diff tronqué.
* **`request_pull_request_reviewers` renvoie `list[str]` :** Renvoie les logins **réellement attachés**, lus dans le corps du `201`. La rupture de contrat est documentée dans [ADR-0009](docs/adr/0009-reviewer-attach-returns-read-back.md) et dans `docs/suggested-reviewers.*`/`docs/scm-multiforge.*`.
* **Helpers GitHub en Lecture Seule :** `get_commit_author_login` et `get_user_login`.
* **Fail-Fast par Forge :** Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` ; Bitbucket exige `GITPR_SCM_USERNAME` ; `create_issue` sur Azure lève `ScmNotSupportedError`.
* **Publication de Release :** `provider.create_release()` utilisé par `gitpr release --publish`.
* **Publication de PR Partagée :** `src/application/use_cases/publish_pull_request.py`, utilisé par `--no-edit` et par l'outil MCP. Valide titre/corps, **exige `provider.token` uniquement quand `check_remote_state=True`**, vérifie optionnellement l'existence d'une PR et d'une branche non poussée, masque les messages d'erreur et renvoie `PublishPrOutcome`.
* **Artefacts :** Glossaire + ADR-001/ADR-005/ADR-016 ; famille `docs/scm-multiforge.*.md` en 5 langues ; **10 fichiers de test, 370 scénarios**.

### **22. Sous-commande `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Flux :** `git log` entre `--since` et `HEAD` → classification par Conventional Commits → bump sémantique suggéré → assemblage du changelog → résumé exécutif IA optionnel → *prepend* à `CHANGELOG.md`.
* **La Plage Ne Vient Plus de `git describe` :** l'origine de la plage est la **section précédente du changelog lui-même**, via deux ancres : le hash le plus récent de cette section et, en repli, le tag de version de la section.
* **`_drop_released()` — la seconde ligne de défense :** tout commit dont le hash court apparaît dans **une autre** section de version est écarté, avec un décompte.
* **Chaque puce est devenue un lien :** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **L'échec de PR mergée par commit de merge :** une seconde passe (`git log --merges`) construit `hash → PR` par ascendance.
* **Contributeurs résolus par la même échelle que les reviewers :** `get_commit_author_login` d'abord, puis `email_to_handle`, mis en cache dans `~/.gitpr/cache/contributors.json`.
* **La Frontière de Section était un Bug Latent :** `upsert_changelog(force=True)` remplace de la ligne `## [x.y.z]` jusqu'au **titre de niveau 2** suivant.
* **Le Front Matter Déclare la Langue de la Skill :** `src/skill_frontmatter.py` (`split_front_matter()`) lit un bloc YAML initial avec `language: <code>` (allowlist `en_us`, `pt_br`, `pt_pt`, `es_es`, `fr_fr`), **supprime le bloc** avant que le corps ne devienne une instruction système et échange la langue i18n pendant la génération — les titres, les libellés **et** la langue demandée à l'IA, même avec le résumé désactivé.
* **Classifieur (`src/commit_classifier.py`)** et **Builder avec Sections Traduisibles (`src/changelog_builder.py`)**.
* **Bump Sémantique (`src/version_bump.py`)** et **Publication** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 options sur la sous-commande.**
* **🆕 État de la Release :** la section `## [1.7.0] - 2026-11-10` est commitée (`6d1f1dc`) et le bump `2354a30` a élevé `__version__` à **1.7.0** ; PyPI sert 1.7.0. Le tag `v1.7.0` pointe encore vers le commit de pré-bump.
* **La sous-commande passe par la barrière de mise à jour** lorsqu'elle est lancée dans un terminal interactif.
* **Artefacts :** `docs/release-notes.*.md` en 5 langues, ADR et `tests/test_web_links.py`.

### **23. Sous-commande `gitpr config` — TUI de Configuration**

* **Écran Maître-Détail (`src/ui/config_app.py`) :** Catégories à gauche, champs à droite, édités en ligne. En-tête avec recherche (`/`) et un compteur de modifications en attente ; pied de page `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schéma Déclaratif (`src/config_schema.py`) — l'unique source de vérité :** **18 catégories** et 🆕 **109 `ConfigField`** (auparavant 100), dont **7 avancés** et 🆕 **56 `seeded`** (auparavant 55).
* **`DEFAULT_CONFIG` Dérivé :** le dictionnaire est construit à partir de `seeded=True`, donc une nouvelle clé du schéma naît soit seedée, soit délibérément non seedée.
* **Catégorie `check` :** les neuf `GITPR_CHECK_*` (`BASE`, `FORMAT`, `OUTPUT`, `FAIL_ON`, `NO_IA`, `IA_ONLY_HIGH_RISK`, `BASELINE_PATH`, `POLICY`, `TIMEOUT_SECONDS`) apparaissent désormais ici, avec les mêmes défauts que le pipeline.
* **Catégorie `smart_excludes` :** remplace l'ancien libellé `Diff Filters` ; `SMART_EXCLUDES_VERSION` a changé de groupe et la catégorie dispose de son propre panneau (voir ci-dessous).
* **Panneau Smart Excludes :** liste des sources (projet, global core, global docs), `TextArea` d'édition, bouton de création du fichier de projet, statut/note et suivi des modifications. `F2` sauvegarde le fichier de projet via le même plan de sauvegarde.
* **Catégorie `project` et l'Attribut `store` :** les champs déclarent où ils vivent (`env` vs `project`). Les trois champs de projet sont le chemin du fichier (lecture seule), la branche cible (éditable) et la base effective avec origine (lecture seule). `KIND_BRANCH` rend le choix de branche.
* **Catégorie `scm` par Forge :** a gagné la ligne éditable `PROJECT_SCM_PROVIDER`, la ligne en lecture seule `PROJECT_EFFECTIVE_PROVIDER` (forge utilisée et origine) et un groupe `gitlab` ; les tokens et URLs de base de **toutes** les forges sont toujours listés, et le test de token utilise l'hôte du slot lui-même.
* **Catégorie `issue` :** `GITPR_ISSUE_AUTOCLOSE` et `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` apparaissent ici.
* **🆕 Type `KIND_FLOAT` :** le premier champ float du schéma est `GITPR_METRICS_CURRENCY`, aux côtés des huit champs de prix par modèle. La validation accepte vide et refuse une valeur négative ou non numérique avec le message « Use a number greater than or equal to zero. ».
* **🆕 Catégorie `ai` avec Prix :** les champs `*_INPUT`/`*_OUTPUT` sont visibles quand le `DEFAULT_AI_PROVIDER` correspondant est sélectionné ; une valeur vide préserve le comportement précédent.
* **Écriture par Store :** `ConfigApp` dispatche par `store` : les valeurs de projet ne passent **jamais** par `save_config_values()`, sont écrites en premier, prennent en charge la suppression à la vidange, refusent la branche courante et n'écrasent jamais un YAML illisible. La ligne de base effective est mise à jour sur place après la sauvegarde.
* **Catégories `policy` et `baseline` :** les champs du pack et les quatre de baseline.
* **Section Skills Alignée :** `SKILL_LABELS` a **11 entrées** (`tests`, `explain`, `mentor`).
* **Filtrage Contextuel (`show_if`) :** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` selon `DEFAULT_AI_PROVIDER` ; `GITPR_SCM_USERNAME` sous `[Bitbucket]` ; `GITPR_SCM_ORGANIZATION`/`PROJECT` sous `[Azure DevOps]`.
* **Recherche Globale (`/`) :** correspond sur la clé ou le libellé dans toutes les catégories et **ignore le filtre de visibilité**.
* **Validation à Deux Couches :** **Hors ligne**, elle bloque `F2` avec une erreur en ligne ; **en ligne** (uniquement des credentials modifiés dans la session), elle s'exécute dans un worker avec un timeout de 10s et ne bloque que sur `401`/`403`.
* **Restauration (`Ctrl+R`) :** Supprime la ligne du `.env` au lieu de réécrire la valeur par défaut ; `Esc` avec des modifications en attente demande confirmation.
* **Sections à Portée de Projet :** Édite `.gitpr/skill/*.md`, `.gitpr/conf/gitpr.config.yml` et les smart-excludes du projet, en écrivant de façon atomique et en préservant CRLF/LF.
* **Téléchargements Forcés :** Boutons qui forcent le re-téléchargement des smart-excludes, des traductions, des presets et des thinking words via `force=`.
* **Module de Liens Léger (`src/doc_links.py`) :** `doc_url()` a été sorti de `core.py` afin que l'UI puisse obtenir le lien sans importer les SDK IA. `DOC_LANG_PARAMS` mappe `es_es`→`es` et `fr_fr`→`fr`, et `CURRENT_LANG` est lu **au moment de l'appel**, donc `set_lang()` se propage aux liens.
* **Tests :** 5 fichiers — `test_config_app.py` (94), `test_config_schema.py` (**55** 🆕, +2), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9) et `test_config_issue_autoclose.py` (10).

### **24. Journal Général d'Utilisation (`src/usage_log.py`)**

* **Une Ligne par Commande :** Écrit `~/.gitpr/logs/<uuid5>.log`, **un fichier par jour**, avec commande, arguments, dépôt, utilisateur et timestamp.
* **Nom Dérivé de la Date :** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")`.
* **Écriture Synchrone (décision explicite) :** Contrairement à `log_local_metric`, qui utilisait un thread daemon et perdait l'enregistrement si le processus se terminait avant.
* **N'imprime Jamais :** Le serveur MCP réserve stdout au JSON-RPC ; le module ne lève non plus jamais d'exception.
* **Un Seul Spawn Git :** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms au lieu de ~150 ms sous Windows, à *chaque* exécution.
* **Contrôle :** `GITPR_SHOW_LOGS` (défaut `"true"`) ; désactivé dans `tests/conftest.py`.
* **Artefacts :** `docs/usage-log.*.md` en 5 langues ; `tests/test_usage_log.py` (27 scénarios).

### **25. Sous-commande `gitpr fix` — Constats de Review en Patches Révisables (`src/fix/`)**

* **Le Vrai Pipeline :** dernière review depuis le cache (`resolve_last_review`) → **un** appel IA → extraction et validation du diff unifié → `git apply --check` → classification déterministe → dry run ou écriture → historique.
* **Un Bundle de 8 Fichiers :** `patch_provenance.py`, `patch_extractor.py` (partagé avec le chat), `patch_safety_classifier.py` (pur, sans I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Classification Déterministe :** Refuse un patch qui traverse plus d'un fichier ou hunk, touche un chemin sensible, dépasse le budget de lignes, **supprime une ligne qui ressemble à un appel**, arrive marqué comme faible confiance ou échoue à `git apply --check`. `--force` ne contourne **jamais** l'applicabilité, seulement la classification.
* **Écriture Opt-In :** Le dry run est le défaut ; toute mutation exige `--apply` (ou une phrase tapée avec `--force`).
* **Tests :** 10 fichiers dans `tests/fix/` (**204 scénarios**) avec de vraies fixtures de dépôt git.

### **26. Sous-commande `gitpr review-pr` — Review de PR Distant (`src/review/`)**

* **Ce que c'est :** Orchestration, **pas** un second moteur de review — le moteur existant, le linter et le renderer alimentés par un diff venu d'ailleurs.
* **Un Bundle de 5 Fichiers :** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Lecture Seule par Défaut :** `--post-comment` est le **seul** chemin qui écrit sur la forge. L'outil MCP `review_remote_pr` ne reçoit même pas l'argument.
* **La baseline est résolue avant le premier appel réseau**, et les avertissements de la barrière sont fusionnés dans le rapport/commentaire.
* **`render.py` et le risque :** `compose_review_content` / `render_review_result` **placent** la section `⚡ Risk Assessment` au-dessus des alertes de linter quand `GITPR_RISK_INCLUDE_IN_REVIEW` est activé.
* **Documentation complète dans les 5 langues :** `docs/review-pr.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md` et `.fr_fr.md`.
* **Hunk Binaire dans le Diff Distant :** `filter_excluded_sections` applique toujours `collapse_binary_hunks()`, y compris sur les sorties précoces sans motifs/sections.
* **Tests :** 4 fichiers dans `tests/review/` (**97 scénarios**).

### **27. Résolution de l'Identité du Reviewer (`src/reviewer_resolution.py`)**

* **Le Bug (deux défaillances silencieuses enchaînées) :** (1) **Préremplissage vide** — `handles` uniquement avec `email_to_handle()` ; (2) **Attache non vérifiée** — valeurs passées *mot pour mot*, GitHub a répondu **201 sans attacher personne** et `_request()` est revenu sans lever d'erreur.
* **Module :** Pur, sans I/O propre, **qui ne lève jamais**. `resolve_candidates()` (avant la TUI) et `resolve_typed_reviewers()` (dans l'attache) ; `match_candidate()` correspond exactement et normalisé par login, nom ou email.
* **Échelle de Résolution :** handle connu (sans requête) → correspondance exacte avec une personne suggérée → recherche par email → validation du login sur la forge.
* **Ce qui ne se résout pas n'est jamais envoyé :** Part dans `ResolutionOutcome.dropped` sous `(value, reason_i18n)` et est écarté avec un avertissement visible.
* **La Même Échelle Sert à la Release :** `release_engine` réutilise `get_commit_author_login` + `email_to_handle`.
* **Compagnon Legacy :** si un Provider tiers renvoie `None` (ancien contrat), le log indique `(no read-back)` et le flux continue — une relecture manquante n'est **jamais** traitée comme un échec.
* **Résumé de Blame :** `ReviewerSuggestionResult` a `files_with_blame`/`files_without_blame` ; le moteur sépare les fichiers et la CLI imprime **une** ligne consolidée (`ℹ️ Blame analysis: X file(s) with blame, Y without blame`) — uniquement quand il y a des fichiers sans blame.
* **Tests :** `tests/test_reviewer_resolution.py` (18) + `test_github_provider.py` (86), `test_pr_publish_app.py` (59), `test_suggest_reviewers.py` (19), `test_reviewer_suggestion.py` (18) et `test_main_suggest_reviewers.py` (8).

### **28. Sous-commande `gitpr demo` — Visite Guidée sur des Exemples Enregistrés (`src/demo/`)**

* **Ce que c'est :** Une visite interactive qui montre les trois sorties principales de GitPR — message de commit, review et description de PR — **sans clé d'API, sans dépôt git et sans réseau**.
* **Vrai Pipeline, Fausse Réponse :** `FakeAIProvider` reflète `src.ai_providers.call_ai_model` **argument par argument** ; `demo_pipeline()` patche `src.core` et `src.metrics` afin que le pipeline de production tourne **intact**.
* **L'Isolation est une Exigence, Pas un Détail :** La visite ne **peut pas** toucher le `~/.gitpr` de l'utilisateur ni le réseau — garanti par `tests/demo/conftest.py`.
* **Bundle :** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (deux exemples localisés dans les 5 langues). Les scénarios sont des **modules Python, pas du JSON**, donc ils voyagent dans le wheel.
* **TUI :** `src/ui/demo/` avec un modal d'aide ; le diff est clôturé comme ```` ```diff ````.
* **CLI :** `gitpr demo` avec `--scenario`, `--lang` et `--no-tui`.
* **Tests :** 6 fichiers dans `tests/demo/` (**155 scénarios**).
* **Artefacts :** `docs/demo.*.md` en 5 langues, `README.*` (5) et les métriques d'exemple de la visite.

### **29. Badge GitPR et la Commande `gitpr badge` (`src/branding/`)**

* **Ce que c'est :** Un badge public qui certifie qu'une pull request est passée par un contrôle de qualité local, et une commande qui imprime l'extrait d'adoption pour le README du projet.
* **URL Statique à Dessein :** C'est un Markdown shields.io que GitPR ne récupère **jamais** — publier une pull request ne peut pas commencer à dépendre de la disponibilité d'un tiers.
* **Mesure Honnête (`badge_data.py`) :** `collect_linter_counts()` renvoie `None` quand aucune règle de linter n'est configurée, car une liste vide signifie « rien n'a été vérifié », pas « rien n'a été trouvé ».
* **Le Badge ne Bloque Jamais une Publication :** Chaque chemin d'échec est capturé et se dégrade en « pas de badge ».
* **Point d'Injection Unique :** `attach_pr_badge()` dans `src/main.py`. Ajout idempotent par marqueur.
* **Commande `gitpr badge` :** `--readme` et `--style`. **Imprime uniquement** — le README n'est jamais modifié.
* **Configuration :** `GITPR_BADGE` (défaut `true`) — opt-out en **lecture seule**.
* **Tests :** 7 fichiers dans `tests/badge/` (**83 scénarios**).
* **Artefacts :** `docs/badge.*.md` en 5 langues et l'enquête dédiée.

### **30. Sous-commande `gitpr split` — Commits Atomiques par Hunk (`src/split/`)**

* **Ce que c'est :** Un arbre de travail avec plusieurs préoccupations cesse de devenir un blob de commit. `split` lit le diff non commité, demande à l'IA de partitionner les hunks par **intention logique** et propose un commit atomique par préoccupation. L'arbre n'est jamais réécrit.
* **Un Bundle de 6 Fichiers :** `split_plan.py`, `hunk_parser.py` (pur), `hunk_grouper.py`, `generate_split_plan.py` (lecture seule), `apply_split_plan.py` (**le seul qui mute**) et `__init__.py`.
* **La Garantie d'Arbre Intact est Structurelle :** `selective_stager.py` pré-valide **chaque** groupe contre un index temporaire à HEAD et utilise un `GIT_INDEX_FILE` jetable.
* **Destructif pour l'Index, Pas pour l'Arbre :** `--apply` réinitialise l'index à HEAD avant de stager.
* **Configuration :** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) et `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Les valeurs zéro ou négatives sont **ignorées au profit du défaut**.
* **Relation avec la TUI de Commit :** `split` et `-c` attaquent le même problème à des hauteurs différentes — `split` propose **plusieurs** commits depuis l'arbre ; `CommitApp` organise **un** commit depuis l'index.
* **Tests :** 6 fichiers dans `tests/split/` (**101 scénarios**).
* **Artefacts :** `docs/split-command.md` en 5 langues, ADR-006, `glossary-gitpr-split.md`, spec/plan et enquête.

### **31. Balayage de Secrets Embarqué (`src/security_ruleset.py`)**

* **Ce que c'est :** Sept règles qui s'exécutent à **chaque** invocation du linter, fusionnées à la fin de `load_linter_rules()` après les règles du projet et les plugins.
* **Les Sept Règles :** cinq `error` bloquantes — ID de clé d'accès AWS, token GitHub, token Slack, clé d'API Google et bloc de clé privée — et deux `warning` : URL de connexion à une base avec credentials et affectation générique de credentials, cette dernière derrière un filtre de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Couverture :** affectation **sans guillemets**, préfixes **`ASIA…`**, **`github_pat_…`** et **`xoxc-`/`xoxd-`**, **frontière gauche** sur le nom de la clé (`bypassword` ne correspond pas) et suppression des faux positifs d'exécution (`os.getenv(...)`, `process.env.X`).
* **Pourquoi dans le Paquet et Pas dans le Modèle :** Une barrière de secrets doit se comporter **de la même façon** sur chaque machine et dans chaque CI.
* **Configuration :** `GITPR_LINTER_SECURITY` (défaut `true` ; opt-out fail-open) et `GITPR_LINTER_SECURITY_DISABLED_RULES` (séparés par `;`).
* **Tests :** `tests/test_security_ruleset.py` (**77 scénarios**) — matrice de regex, placeholders, niveau, wildcard, fusion et traductions ; l'invariant « l'alerte n'affiche jamais la valeur » tient toujours.
* **Artefacts :** ADR-007, [ADR-0008](docs/adr/0008-secret-ruleset-coverage-extension.md), `glossary-gitpr-secret-scanning.md`, spec/plan/enquête et rapports de tâche.

### **32. Ponts SAST — Semgrep, Gitleaks et Bandit (`src/infrastructure/linter/external/`)**

* **Ce que c'est :** Une couche **opt-in** qui branche des scanners de sécurité tiers sur le pipeline de linter existant.
* **Portée Bornée :** Ils s'exécutent **uniquement** quand ils sont activés et **uniquement** sur les fichiers touchés par le diff.
* **Contrat Commun :** `ExternalLinterBridge` (ABC) avec une exécution `subprocess` durcie (`shell=False`, timeout strict, `stdin=DEVNULL`, UTF-8 avec `errors='replace'`).
* **La Déduplication est le But :** `deduplicate_secret_findings()` fusionne les constats de Gitleaks et du ruleset regex dans le même fichier et la même ligne en **une seule** entrée confirmée `[Gitleaks + Regex]`.
* **Secrets Masqués :** `mask_secret_value()` garantit que la valeur n'atteint jamais le constat, le log ou la télémétrie.
* **Résolution à Trois Couches :** `load_sast_config()` lit les défauts → `.gitpr.linter.yml` (bloc `sast`, avec repli sur `linter.external`) → variables `GITPR_SAST_*`. **Toutes à `false` par défaut**.
* **Dégradation Gracieuse :** Un outil activé mais absent du `PATH` émet un avertissement et l'exécution continue.
* **Tests :** 4 fichiers dans `tests/infrastructure/linter/external/` (**14 scénarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + `tests/domain/linter/test_structured_findings.py` (44).

### **33. Sous-commande `gitpr tests generate` — Génération de Suite par IA (`src/domain/tests_generation/`, `src/application/`)**

* **Ce que c'est :** Génère des fichiers de test complets et exécutables à partir du diff courant, d'un fichier spécifique ou d'un constat de review, **en respectant la convention du dépôt** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Couche Domaine :** `TestFramework` (enum) et les dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest` ; `detect_test_framework()` détecte à partir des fichiers de configuration, des manifests et du contenu du répertoire de tests ; `build_test_scaffold()` calcule le chemin de destination conventionnel par framework.
* **Couche Application (`generate_test_file.py`) :** Orchestre la détection, la résolution du scaffold, le prompt, l'IA, le parsing JSON et l'écriture optionnelle. `validate_test_syntax()` exécute la chaîne d'outils locale (`php -l`, `node --check`, `python -m py_compile`) — un échec de validation est un **avertissement**, pas une erreur.
* **Présentation :** groupe `tests` avec `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`) ; le dry run est le défaut et écraser un test existant demande confirmation avec **No** présélectionné.
* **Documenté dans 5 langues :** `docs/tests-generate.*.md` (5 fichiers), avec un lien dans `gitpr tests generate` (epilog) et dans le README.
* **Skill :** `.gitpr.tests.md` dans `SKILL_LABELS`, `mcp_server.SKILL_FILES` et `SKILL_FILES_BY_TYPE`.

### **34. Sous-commande `gitpr explain` et le Flag `--explain` — Guide du Reviewer (`src/domain/pr/`)**

* **Ce que c'est :** Un guide centré sur celui qui va **relire** — ce qui change, pourquoi cela change, où se concentrer et quel est le risque de régression.
* **Couche Domaine (`explain_section_builder.py`) :** `PrExplanation` / `ReviewerFocusPoint`, le renderer `build_explain_markdown()` et `parse_explain_payload()`, qui tolère une sortie non-JSON et **détecte les placeholders** `[FILL]`/`[TODO]`.
* **`parse_explain_payload()` Accepte `dict | str | None` :** le fournisseur livre le JSON **déjà parsé** ; la fonction accepte les trois formes et normalise les champs `None`.
* **Branche Clé Manquante :** `generate_pr_explanation` imprime l'erreur standard de clé manquante et sort avec **1**.
* **Couche Application (`generate_pr_explanation.py`) :** Résolution du fournisseur, validation de la clé, chargement du contexte de skill (`explain`), prompt, invocation et parsing.
* **Deux Portes d'Entrée :** `gitpr explain` (`--provider`, détection de diff manquant avant toute IA) et le flag `--explain` sur la CLI racine. L'extension expose le même chemin dans `GitPR: Generate PR Draft (with Reviewer Guide)`.
* **`-h --explain` Corrigé :** l'option utilisait dest `explain_flag`, mais le `HELP_MAP` cherchait `explain` — l'aide contextuelle s'affiche de nouveau.
* **Documenté dans 5 langues :** `docs/explain-pr.*.md` (5 fichiers), avec un lien dans `HELP_MAP["explain"]`, dans l'epilog de la commande et dans le README.
* **Configuration :** `GITPR_EXPLAIN_BY_DEFAULT` (défaut `false`).

### **35. Suite Déterministe et CI (`.github/workflows/tests.yml`, `release.yml`)**

* **Ce que c'est :** Le workflow qui exécute la suite sur **Python 3.10** (le plancher déclaré dans `pyproject.toml`) et **3.13**, avec `fail-fast: false`.
* **`tests/conftest.py` Hermétique :** Épingle `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` et `LANG_VERSION` à la version du code, plus `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true` et `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
* **Fixtures Autouse Contre la Fuite de Profil :** `english_interface` et `no_hook_script_sync` empêchent la langue, les flags de linter et l'auto-sync des hooks du profil développeur de fuiter entre les tests CLI.
* **L'Ordre Compte :** `src.updater` est importé **avant** que les variables de langue ne soient définies.
* **Stabilité des Tests de Worker :** `test_config_app.py` et `test_metrics.py` attendent `workers.wait_for_complete()` avant d'asserter.
* **Dépendance du Workflow :** Le runner propre n'a pas de `~/.gitpr`, et `test_demo_isolation.py` asserte que le profil existe — le job crée explicitement le répertoire et le `.env` vide.
* **🆕 Dans cette fenêtre, la suite a gagné 14 scénarios et 1 module :** `test_ascii_chart.py` (9), `test_config_schema.py` (53 → **55**), `test_metrics.py` (174 → **175**), `test_full_diff_encoding.py` (8 → **9**) et `unit/test_check_ci_docs_parity.py` (2 → **3**). **Total : 3436 scénarios dans 160 fichiers.**
* **🆕 Test de Parité `action.yml` :** `tests/unit/test_check_ci_docs_parity.py` exige désormais aussi que ADR-0011 existe et référence `action.yml`, `gitpr check`, `--linter`, `--fullreview`, `SARIF` et `docs/check-ci.md`.
* **⚠️ 2 échecs connus :** `tests/unit/test_version_consistency.py::test_claude_md_version_matches_ssot` et `::test_gemini_md_version_matches_ssot` échouent parce que `CLAUDE.md`/`GEMINI.md` disent encore 1.6.0 alors que `src/updater.__version__` est déjà 1.7.0.
* **Workflow de l'Extension :** `.github/workflows/extension.yml` exécute `npm install`, compile, teste et package le VSIX. 🆕 Le job `github-release` attache le `.vsix` aux GitHub Releases des tags `ext-v*` ; le job `publish-marketplaces` évalue chaque PAT indépendamment et est non bloquant.
* **Workflow de Release :** `release.yml` exécute les tests, vérifie la cohérence tag ↔ `src.updater.__version__`, construit sdist/wheel, valide avec `twine check --strict`, publie via Trusted Publishing (OIDC) et crée la GitHub Release. Le SSOT de version a aussi son propre test (`tests/unit/test_version_consistency.py`).

### **36. Ledger de Télémétrie SQLite (`src/ledger.py`, `src/infrastructure/git/identity.py`)**

* **Le Problème N'était Pas le Format, C'était la Jointure :** les événements étaient écrits en fichiers JSON par commande, depuis un **thread daemon**, puis reconnectés plus tard au cache IA par une correspondance heuristique de `(repo, branch, action, minute)`.
* **L'Écriture est Synchrone, et C'est la Décision :** `log_command_metric()` résout le contexte git **une fois par processus** et écrit la ligne immédiatement.
* **Schéma Versionné :** `SCHEMA_VERSION` + `PRAGMA user_version`, clés UUID, WAL + `synchronous=FULL`, et `LedgerVersionError` pour refuser un bundle plus récent **avant** `ATTACH`.
* **Invariant « Jamais Né Vide » :** `ensure_ledger()` ne crée la base **qu'avec** l'absorption du JSON legacy.
* **Une Fenêtre, un Sens :** `--since`/`--until`/`--days` resserrent **toutes** les surfaces de lecture ; la section de cycle suppose les 30 derniers jours et **nomme la fenêtre**.
* **Chaque Métrique est Calculée Une Fois et Rendue Une Fois :** les paires `(style, text)` font raconter la **même** histoire à la CLI et au tableau de bord Textual.
* **Coût avec Table Embarquée et Surcharge :** prix catalogue des quatre modèles, surchargés par `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Les deux tarifs sont obligatoires**. 🆕 Les alias Gemini ont aussi un prix et `GITPR_METRICS_CURRENCY` contrôle l'étiquette.
* **`modules` Normalisés :** les chemins deviennent deux segments (`src/fix`), `(root)` et `(no module)`.
* **Transport par Bundle :** `bundle` écrit la fenêtre comme un `.db` autonome ; `merge` l'attache et insère par UUID.
* **`cache_backfill` Reste Dehors :** les lignes reconstruites depuis le cache sont exclues des agrégats d'exécution.
* **`issue_autoclose` Entre dans le Ledger :** une ligne par décision, avec `asked`, `accepted` et `skipped_reason`.
* **`duration_ms` avec Repli sur `meta` :** les événements qui ne portaient la durée que dans `meta["duration_ms"]` ne sont plus enregistrés comme `0`.
* **Identité Git Sans Forge (`identity.py`) :** libellé du dépôt, branche et auteur en **un seul** appel de sous-processus.
* **La Section Cycle est la Seule que le Ledger ne Répond Pas :** liste les PR mergées via `ScmProvider.list_pull_requests` ; chaque échec se dégrade en une ligne — `Not read: <reason>`.
* **Présentation Complète :** groupe `gitpr metrics` avec 8 sous-commandes, un tableau de bord lisant le ledger sur un thread worker et l'assistant de migration à trois options.
* **Tests :** `tests/test_metrics.py` (**175 scénarios** 🆕).

### **37. Sous-commande `gitpr mentor` et le Flag `--mentor` — Mentor Mode (`src/domain/mentor/`)**

* **Ce que c'est :** Une review dit *ce qui* est faux et presque jamais *pourquoi cela compte*. Le Mentor Mode enrichit les constats avec **ce qui se passe**, **pourquoi cela compte**, une **analogie** du quotidien et les **concepts à étudier**.
* **Couche Domaine Pure (`mentor_explanation_builder.py`) :** sans I/O, convertit le payload IA en un `MentorExplanation` validé et impose une **honnêteté épistémique** : sans preuves suffisantes, l'explication **le dit** ; les URLs hallucinées sont **supprimées** ; les IDs inconnus sont **rejetés**.
* **Couche Application (`generate_mentor_explanation.py`) :** collecte les candidats, **lots d'au plus 10 constats**, triés par sévérité, en réutilisant le cache MD5 par hash de prompt.
* **Deux Portes d'Entrée :** `gitpr mentor [--finding <id>] [--provider <name>]` et le flag `--mentor`, qui attache la section à la review normale.
* **Opt-In Strict :** `GITPR_REVIEW_MENTOR_MODE` (défaut `false`) et `GITPR_MENTOR_INCLUDE_ANALOGY` (défaut `true`), éditables dans la TUI de configuration.
* **Skill et Registre :** `.gitpr.mentor.md` enregistré dans `SKILL_FILES_BY_TYPE`, dans `mcp_server.SKILL_FILES` et dans `SKILL_LABELS`, avec des modèles en **5 langues**.
* **Tests :** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) et `tests/test_mentor_cli.py` (4) — **tous au vert**.
* **Artefacts :** `docs/mentor-mode.*.md` en **5 langues**, ADR-009, `glossary-gitpr-mentor.md`, spec et rapports de tâche.

### **38. Sous-commande `gitpr risk` — Score de Risque Déterministe (`src/domain/risk/`)**

* **Ce que c'est :** Un score **0–100** avec un niveau `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculé **sans IA** à partir de signaux locaux.
* **Couche Domaine Pure (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`) :** enums et dataclasses ; poids, seuils, motifs de chemins critiques, correspondance glob et détection de tests/non-exécutables chargés depuis YAML ; calcul par fichier avec un **clamp 0–100** et agrégation 50/30/20.
* **La Formule est Déclarée, Pas Apprise :** **50 %** le fichier le plus risqué, **30 %** la moyenne pondérée par lignes et **20 %** les preuves critiques.
* **Adaptateurs d'Infrastructure :** `risk_history_reader.py` lit `git log` et extrait les commits, corrections de bugs et reverts sur une fenêtre de **90 jours** ; `test_matcher.py` fait correspondre fichier de test ↔ production par convention.
* **Cas d'Usage (`calculate_risk.py`) :** corrèle sections du diff, constats, correspondance de tests et historique en `FileRisk`/`PullRequestRisk`, **se dégradant avec un avertissement** quand un signal est indisponible.
* **Chemin Tolérant :** l'agrégation de `check` accepte `path` **ou** `file_path` — le rapport de risque ne se dégrade pas silencieusement.
* **Trois Consommations :** la commande `gitpr risk`, la section `⚡ Risk Assessment` attachée aux reviews et à la review de PR distante, et le **prompt IA** comme contexte **en lecture seule**. `gitpr check` utilise le même `execute_calculate_risk`.
* **Opt-Out :** `GITPR_RISK_INCLUDE_IN_REVIEW` (défaut `true`).
* **Configuration par Projet :** `templates/gitpr.risk.yml` (ou `.gitpr/skill/gitpr.risk.yml`) surcharge les poids, seuils et chemins critiques.
* **Tests :** `tests/domain/risk/` (2 fichiers, 10), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) et `tests/test_risk_cli.py` (4).
* **Artefacts :** `docs/risk-scoring.*.md` en **5 langues**, ADR-010, `glossary-gitpr-risk-scoring.md` et rapports de tâche.

### **39. Sous-commande `gitpr policy` — Policy Packs Versionnés (`src/domain/policy/`, `src/infrastructure/policy/`)**

* **Ce que c'est :** Un **pack YAML versionné** qui porte, dans un seul artefact, les décisions de qualité d'un dépôt : skills, règles de linter, sévérités, chemins critiques, poids de risque et conventions de PR/commit.
* **Couche Domaine (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`) :** parsing du manifest avec un **schéma fermé**, vérification de compatibilité et de credentials, **checksum SHA-256** et un résolveur de graphe avec précédence et composition.
* **Couche Infrastructure :** `local_policy_repository.py` résout le pack depuis **trois origines** (`.gitpr/policies`, `~/.gitpr/policies` et le paquet embarqué) et `policy_pack_loader.py` le charge.
* **Cas d'Usage :** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` et `validate_policy_pack`.
* **Quatre Packs Embarqués :** `laravel-quality`, `node-quality`, `php-security` et `vue-quality`, chacun avec `policy.yml` et `linter.yml`, distribués via **`package-data`**.
* **Consommation Intégrée à Quatre Points :** `calculate_risk`, `config.load_linter_rules`, `core.get_skill_context` et la **clé de cache de PR** (`cache_scope`). `gitpr check` résout le pack **avant tout** et sort avec le code 2 s'il est invalide.
* **CLI :** `gitpr policy {list, validate, show, use, off, install, init}`, avec confirmation (`--yes`/`--force`), détection de stack à `init` et le choix écrit dans `.gitpr/policy.lock.yml`.
* **Configuration :** `GITPR_POLICY_ENABLED` et `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`.
* **Compatibilité Ascendante :** sans pack actif, le comportement est **préservé** — couvert par un test d'intégration.
* **Dépendance :** `packaging>=23.0` (SemVer/PEP 440).
* **Tests :** `tests/domain/policy/` (4 fichiers, 126), `tests/application/use_cases/` (4 fichiers de policy, 89), `tests/test_policy_cli.py` (26) et `tests/integration/test_policy_effect_on_review_linter_risk.py` (31).
* **Artefacts :** `docs/policy-packs.*.md` en **5 langues**, ADR-011, `glossary-policy-packs.md`, spec/plan/enquête.

### **40. Sous-commande `gitpr baseline` — Baseline et Suppressions Auditables (`src/domain/baseline/`)**

* **Ce que c'est :** Les linters et les reviews re-signalent les mêmes constats préexistants à chaque exécution. La baseline enregistre le **snapshot des constats que le dépôt connaît déjà** dans `.gitpr/baseline.json`, empreinte chacun de manière stable entre les exécutions et permet au pipeline de classer ce qui est nouveau — **seul `new` bloque**.
* **Couche Domaine (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`) :**
  * **Empreinte stable :** identité de la règle, chemin normalisé, lignes et **sha256 de la ligne avec les espaces réduits** — la ligne brute n'est **jamais** stockée.
  * **Manifest canonique :** JSON canonique avec checksum, validation qui refuse une clé inconnue, une version de schéma/empreinte hors de la plage supportée, un invariant suppression/dette cassé, une empreinte dupliquée et une plage de lignes invalide.
  * **Comparateur :** classification, comptages par statut et résolution d'entrée — `resolved` puis `back` se lit comme **new**, la lecture conservatrice.
  * **Politique de Suppression :** suppressions, dette acceptée, overrides et validateurs, avec correspondance en couches par **spécificité et précédence**, plus le suivi de la dette échue.
* **Cinq Cas d'Usage :** `baseline_gate`, `baseline_summary`, `compare_against_baseline`, `create_baseline` et `suppress_finding`.
* **Intégration avec le Policy Pack :** le pack peut porter un bloc `baseline:`, validé par les **mêmes** validateurs.
* **Le Dépôt est le Seul qui Écrit :** `local_baseline_repository.py` est le **seul** lecteur/écrivain de `.gitpr/baseline.json` et `.gitpr/baseline.overrides.yml`.
* **Configuration :** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` et `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`.
* **Opt-In et No-Op :** désactivée ou sans baseline existante, la fonctionnalité **ne lit aucun fichier et n'imprime rien**.
* **⚠️ L'Exception Délibérée :** un fichier de baseline qui existe mais **ne peut pas être appliqué** fait **avorter avec le code de sortie 1** le lint, la review, la full review, l'audit de fichiers, la review distante et le risque. Dans `gitpr check`, une baseline corrompue sort avec le **code 2**.
* **Le Contrat est Devenu un Test :** `tests/integration/test_baseline_exit_code_contract.py` (8 scénarios) verrouille les deux codes.
* **Portée :** CLI (6 sous-commandes, toutes avec `--format json`), MCP (`get_baseline_status` + `baseline://summary`), review de PR distante et le flux de lint.
* **Le Refactoring de la Fonctionnalité a Exigé :** `NormalizedFinding` est devenu un type canonique dans `src/domain/finding/`.
* **Tests :** `tests/domain/baseline/` (5 fichiers, **126**), `tests/application/use_cases/` (5 fichiers, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) et `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34) + `tests/integration/test_baseline_exit_code_contract.py` (8).
* **Artefacts :** `docs/baseline-suppressions.*.md` en **5 langues**, ADR-012, `glossary-baseline.md`, spec/plan/enquête et rapport de tâche.

### **41. Sous-commande `gitpr check` — Barrière CI Unifiée (`src/domain/check/`, `src/infrastructure/ci/`)**

* **Ce que c'est :** Un **point d'entrée unique** pour les pipelines CI qui orchestre l'analyse statique, les ponts SAST, le scan de secrets, la review IA optionnelle, le score de risque et la classification de baseline, et émet **un artefact standardisé** avec un code de sortie déterministe.
* **L'Ordre du Pipeline est Contrat :** (1) résolution de policy — invalide sort avec `2` ; (2) extraction du diff avec exclusions intelligentes ; (3) linter regex + secrets + ponts externes ; (4) score de risque préliminaire ; (5) **review IA optionnelle** ; (6) classification de baseline ; (7) évaluation du seuil `--fail-on` ; (8) génération de l'artefact avec **masquage des secrets**.
* **Couche Domaine (`src/domain/check/`) :** `check_types.py` (sévérité normalisée en trois niveaux avec aliases de spec) ; `check_config.py` (précédence **CLI > env > défauts**) ; `check_output.py` (dispatch de format et écriture atomique).
* **Couche Infrastructure (`src/infrastructure/ci/`) :** `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (testsuites avec `<failure>` et `<system-out>`) et `masking.py`.
* **Cas d'Usage (`run_check.py`) :** `resolve_diff_base()` (explicite → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), exécution du linter et des ponts, score de risque, review optionnelle, baseline et l'enveloppe finale. Stdout n'émet **que l'artefact**. L'agrégation de risque accepte `path` **ou** `file_path`.
* **Codes de Sortie :** `0` succès ; `1` constat comptable au seuil ; `2` erreur d'exécution.
* **CLI :** `gitpr check` avec 8 options et 9 variables `GITPR_CHECK_*`, aussi dans la TUI de configuration (catégorie `check`).
* **GitHub Action Réécrite :** `action.yml` est devenu une seule étape `gitpr check`. **Rupture déclarée** pour les workflows qui reposaient sur `--linter` + `--fullreview`. 🆕 l'en-tête de `action.yml` porte désormais la note de migration et le lien vers `docs/check-ci#7-migration-guide` ; la décision est formalisée dans [ADR-0011](docs/adr/0011-action-unified-check-migration.md).
* **La Note de Migration est Devenue un Test :** `tests/unit/test_check_ci_docs_parity.py` exige les 5 variantes de langue de `docs/check-ci.*`, les 7 sections `## 1.`–`## 7.` et les termes `action.yml`, `--linter`, `--fullreview`, `gitpr check` et `SARIF` dans la section de migration — plus le terme **Legacy CI action contract** dans `CONTEXT.md`. 🆕 il exige désormais aussi ADR-0011 et les références dans `action.yml`.
* **Tests :** `test_run_check.py` (8), `test_check_types.py` (8), `test_check_config.py` (3), `test_check_output.py` (3), `test_masking.py` (6), `test_sarif_builder.py` (2), `test_junit_builder.py` (2), `test_check_cli.py` (5), `test_baseline_exit_code_contract.py` (8) et `test_check_ci_docs_parity.py` (**3** 🆕).
* **MCP :** l'outil `run_check` renvoie l'enveloppe JSON du même pipeline, avec `no_ia` par défaut `true`.
* **Artefacts :** `docs/check-ci.*.md` en **5 langues**, ADR-013 + 🆕 ADR-0011, `glossary-check-ci.md`, spec, `plansfacts` et enquête.

### **42. Extension Éditeur — VS Code et Cursor (`extensions/vscode/`)**

* **Ce que c'est :** Une extension **client léger** qui apporte les flux de GitPR dans l'éditeur sans rien réimplémenter : elle démarre `gitpr-mcp` via stdio et appelle les outils existants. Clés, tokens, prompts et cache restent dans `~/.gitpr/`, chiffrés avec Fernet.
* **Architecture :** `GitPrMcpClient` sur `@modelcontextprotocol/sdk` + `SessionManager` avec **une session par dossier** (multi-root) + vérification du contrat de connexion : les 7 outils obligatoires doivent exister ; sinon `MissingMcpToolsError` instruit `pip install --upgrade gitpr-cli`.
* **8 Commandes :** `gitpr.commit`, `gitpr.review`, `gitpr.fullReview`, `gitpr.reviewMentor`, `gitpr.generatePr`, `gitpr.generatePrExplain`, `gitpr.publishPr` et `gitpr.check`.
* **Publication en Deux Étapes :** `generatePr` ouvre un document Markdown éditable ; `publishPr` demande une confirmation modale et gère `success`, `needs_push`, `already_exists`, `auth_required` et `error`.
* **Diagnostics dans le Panneau Problems :** `run_check` est converti en `DiagnosticCollection("gitpr")`.
* **Masquage à Double Profondeur :** `masking.ts` reflète `src/infrastructure/ci/masking.py`.
* **Localisation de l'Extension :** bundles en **en, es, fr et pt-br** (4 langues) — l'UI de GitPR elle-même reste à 5.
* **Métadonnées de Marketplace :** `package.json` a `icon` (`images/icon.png`), `homepage` et `bugs.url` ; le paquet porte `LICENSE` (LGPL-2.1), la condition minimale pour une fiche crédible.
* **🆕 Publication VSIX Découplée :** `extension.yml` a gagné un job `github-release` dédié qui attache le `.vsix` compilé aux GitHub Releases officielles des tags `ext-v*` — l'artefact de l'éditeur ne dépend plus de la release du paquet Python.
* **🆕 Barrière Marketplace Granulaire :** le job `publish-marketplaces` évalue `VSCE_PAT` et `OVSX_PAT` **indépendamment**, ne bloque pas le reste du workflow et rapporte chaque résultat dans `$GITHUB_STEP_SUMMARY`. Le terme du domaine est passé de *Fail-fast secret check* à **Granular marketplace gate**.
* **🆕 Installation en Un Clic :** `docs/editor-extension.*` (5 langues), `README.md` et `docs/marketplace-publishing-guide.md` ont gagné des instructions d'installation du VSIX via CLI et via GUI, avec des liens directs vers les releases `ext-v*`.
* **Ce que l'Extension ne Fait Pas :** elle ne commit, ne push et ne publie pas sans confirmation. La commande `gitpr.commit` de l'extension appelle l'outil MCP `generate_commit_message` — elle n'ouvre **pas** la TUI, exclusive au terminal. La clôture automatique d'issues est aussi **exclusive à la CLI**.
* **Tests :** sa propre suite Mocha (7 scénarios, dont un vrai stdio ; le timeout du test d'intégration est passé de 20s à 60s) + `tests/test_mcp_server.py` (117 scénarios).
* **Artefacts :** `docs/editor-extension.*` en **5 langues**, `docs/marketplace-publishing-guide.md`, ADR-014, `glossary-editor-extension.md`, specs, `plansfacts` et rapports.

### **43. TUI de Commit Interactive (`src/ui/commit_app.py`)**

* **Ce que c'est :** `gitpr -c`/`--commit` a cessé d'être un pipeline silencieux — il validait l'arbre, demandait le message à l'IA et commitait immédiatement, sans vous laisser choisir les fichiers, relire le texte ou refuser une mauvaise suggestion. C'est désormais un `CommitApp(App)` avec **4 étapes** qui garde le développeur aux commandes jusqu'au `git commit`.
* **Bundle :** un seul module, `src/ui/commit_app.py` (220 lignes). Les trois modaux sont **importés** depuis `src/ui/pr_publish_app.py` (`StageFilesScreen`, `CommitMessageScreen`, `CommitProgressScreen`) avec `_with_suppressed_stdout` — **zéro widget dupliqué**.
* **Les 4 Étapes :**
  1. **Stage Selector :** s'il y a des fichiers hors de l'index, il ouvre `StageFilesScreen` avec des cases à cocher, « Select All », « Deselect All », « Stage Selected », « Skip Staging » et « Cancel ». Index déjà complet → l'étape est sautée.
  2. **Génération :** lit `get_git_staged_diff()`, retombe sur `get_git_diff()` quand rien n'est stagé, et appelle `generate_pr_content("commit", "commit", diff_text, provider)` dans un `threading.Thread` daemon pendant que `CommitProgressScreen` anime.
  3. **Review :** `CommitMessageScreen` affiche le message, qui peut être édité avant confirmation — le message **édité** est celui qui part vers git.
  4. **Exécution :** un nouveau `CommitProgressScreen` exécute `execute_git_commit(append_coauthor_trailer(msg))` et enregistre le SHA dans le log.
* **Chaque Échec Devient un État, Pas une Exception :** une IA en panne retombe sur `__("Code update")` ; un commit qui lève une erreur devient `failed` avec la sortie capturée. États terminaux : `no_changes`, `cancelled`, `committed`, `failed`.
* **Auto-Sortie en Cas de Succès :** `log.mark_finished(False)` retire le bouton, et `_schedule_auto_exit()` programme `set_timer(0.6, self.exit)` — l'utilisateur revient au prompt sans cliquer. **En cas d'erreur, le Close est conservé**, car c'est là que le log d'échec reste lisible.
* **`--hook` Intact :** le chemin hook continue sans TUI et merge-aware (`is_merge_in_progress()` → `return` silencieux). `prepare-commit-msg` n'ouvre jamais l'application.
* **Garde TTY :** sans `--hook`, le routage consulte désormais `_may_ask_the_user()` ; avec un TTY, il ouvre `CommitApp` ; sans TTY (CI, pipe, cron), il retombe sur le flux console silencieux.
* **Configuration :** `GITPR_SKIP_UNSTAGED_CHECK` (saute le Stage Selector) et `GITPR_AUTO_STAGE` (stage tout et poursuit), tous deux dans `DEFAULT_CONFIG` et dans le schéma de la TUI de configuration.
* **Détails Textual :** `ENABLE_COMMAND_PALETTE = False` ; `on_button_pressed` gère `#btn_close` ; `_on_commit_progress_dismissed` lit `_commit_success` au lieu de supposer le succès à la fermeture du modal.
* **Note de Migration :** 🆕 `docs/commit-message-ia.*` (5 langues) indique désormais explicitement que, sans TTY, `-c` retombe sur le flux console et que `--hook` reste le chemin CI.
* **Tests :** `tests/test_commit_app.py` (**17 scénarios**) — chaque transition d'état et callback, un flux headless complet et `TestCliCommitRouting` (routage avec et sans TTY).
* **Domaine :** `CONTEXT.md` conserve les termes **Stage selector** et **Commit flow**.
* **Artefacts :** `docs/commit-message-ia.*` dans les **5 langues** et le rapport de tâche Gemini.

### **44. Clôture Automatique d'Issues (`src/issue_autoclose/`) — Multi-Forge**

* **Ce qu'il était :** l'issue créée avec `gitpr -is` était orpheline — le numéro n'existait qu'à l'intérieur de la TUI d'issue et était perdu à l'instant où elle se fermait. Chaque PR devait être liée à la main, et le merge ne fermait rien.
* **Un Bundle de 5 Fichiers :** `issue_registry.py`, `register_created_issue.py`, `resolve_close_issue.py`, `insert_close_line.py` et `__init__.py`. Chacun est une responsabilité : persister, enregistrer à la création, décider à l'exécution suivante et insérer dans le corps.
* **Registre Neutre vis-à-vis du Provider (`IssueRegistry`) :** le fichier a migré de `.gitpr/conf/github.yml` vers **`.gitpr/conf/issue.yml`**, avec un champ `provider` (défaut `github`). Le `github.yml` legacy est **lu** de façon transparente quand le nouveau n'existe pas et est supprimé à la prochaine écriture/nettoyage — migration sans intervention de l'utilisateur.
* **Auto-Ignorance avec Précision :** `record()` exécute `git check-ignore` avant la première écriture et ajoute exactement une ligne au `.gitignore` quand le fichier n'est pas couvert — `.gitpr/conf/` n'est **pas** ignoré dans son ensemble, car `gitpr.config.yml` est versionné à dessein ([ADR-015](docs/plans/ADR-015-project-local-pr-base.md)).
* **Enregistrement à la Création :** `register_created_issue()` enregistre le numéro **et le Provider** de n'importe quelle forge prise en charge et avale les échecs de tenue de registre — un problème d'écriture local ne transforme jamais une création réussie en erreur. L'ancien résultat `not-github` est devenu `no-provider`.
* **Décision (`resolve_close_issue`) :** filtre par flags et configuration, mais **plus** par forge ; chaque forge sans support se dégrade en `skipped_reason="not-supported"` au lieu d'être ignorée. Elle vérifie que l'issue est toujours **ouverte** et **n'est pas une PR** (en préférant `IssueInfo.is_open`), supprime les enregistrements morts (404, fermée, numéro de PR) et demande **une fois**, en avertissant quand la PR cible une branche autre que celle par défaut.
* **Ligne Idempotente (`insert_close_line`) :** détecte n'importe quel mot-clé existant (`close/closes/closed`, `fix/fixes/fixed`, `resolve/resolves/resolved`, avec un préfixe optionnel `owner/repo#N`, insensible à la casse) et ne duplique jamais la ligne.
* **CLI :** `--close-issue` et `--no-close-issue` — mutuellement exclusifs, refusés avant tout prompt ou écriture ; `-h` gagne le conflit.
* **Câblage dans le Flux de PR :** `_prepare_close_issue` s'exécute **avant** le Stage Selector et réutilise le contexte SCM résolu ; `_publish_pr_directly` renvoie si la forge a réellement publié ; `PrPublishApp.body_published` distingue la création/mise à jour de la simple ouverture d'une PR existante. Le registre n'est effacé qu'après une publication réelle (ADR-007).
* **Contrat SCM :** `IssueInfo` (état dans le vocabulaire de la forge, `is_open` et `is_pull_request`), `get_issue()`, `get_default_branch()` et `closing_line()` sont des défauts concrets de l'ABC ; GitHub, GitLab et Bitbucket les implémentent (syntaxe `Closes #N` sur GitLab, `close #N` sur GitHub/Bitbucket), et Azure DevOps lève `ScmNotSupportedError`.
* **Configuration :** `GITPR_ISSUE_AUTOCLOSE` (défaut `true`, opt-out) et `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` (défaut `5`, entier positif), exposés dans la catégorie `issue` de la TUI.
* **Télémétrie :** une ligne `issue_autoclose` par décision (`asked`, `accepted`, `skipped_reason`).
* **Tests :** 5 fichiers dans `tests/issue_autoclose/` (**126 scénarios**) — aller-retour du registre, schéma, corruption, échec d'écriture atomique, `.gitignore`, migration legacy, décision, idempotence de la ligne, flags, câblage et sémantique de `body_published`.
* **Artefacts :** [ADR-010](docs/adr/0010-multiforge-issue-autoclose.md), `docs/specs/issue-autoclose/` (12 fichiers), `docs/issue-autoclose.*` en **5 langues**, `docs/issue-tui-help.*`, `docs/config-tui.*` et `docs/metricas-telemetria.*`, README dans les 5 langues et rapports de tâche.

### **45. Base du PR par Projet (`src/project_config.py`, `src/ui/pr_base_app.py`)**

* **Ce qu'il était :** la branche cible de PR ne vivait que dans le `~/.gitpr/.env` de la machine (`PR_DEFAULT_BASE`) ou était devinée depuis `origin/HEAD`. Un dépôt ciblant `develop` dépendait de chaque développeur configurant la même variable, et l'erreur (une PR ouverte contre la mauvaise branche) n'apparaissait que sur la forge.
* **Ce que c'est :** `.gitpr/conf/gitpr.config.yml` — un fichier **versionné avec le projet** qui porte la base de PR, édité par la TUI de configuration et validé avant tout commit/push. Un unique résolveur sert la CLI et le MCP, de sorte que chaque point d'entrée s'accorde sur la précédence et les règles d'échec.
* **Résolveur (`src/project_config.py`) :** `resolve_pull_request_base()` — `--base` → fichier de projet → `PR_DEFAULT_BASE` → détection `origin/HEAD` → `main`. La lecture/écriture YAML préserve les clés étrangères ; seed idempotent ; liste des branches ; détection de la branche courante ; vérification contre le remote.
* **Seconde Section (`scm:`) :** le fichier porte aussi `scm.provider` (lecture/écriture), les deux sections étant indépendantes, et il refuse d'écraser un bloc `scm` qui n'est pas un mapping. C'est cette surcharge qui gagne sur la détection du remote (section 46).
* **Échec Avant la Naissance de la PR :** `BaseBranchError`/`CurrentBranchError` ; la base doit exister sur `origin/<branch>` et différer de la branche courante — sans `origin` ou avec une ref manquante, le flux s'arrête avec une indication actionnable au lieu d'ouvrir une PR condamnée.
* **TUI de Configuration :** catégorie `project` avec un attribut `store` (`env` vs `project`), `KIND_BRANCH` et trois champs : chemin du fichier (lecture seule), branche cible (éditable) et base effective avec origine (lecture seule).
* **Modal de Réparation (`src/ui/pr_base_app.py`, 98 lignes) :** quand la cible est la branche courante elle-même et que l'exécution est interactive, un modal ciblé demande la branche de remplacement et l'écrit dans le fichier de projet.
* **CLI :** `--base` passe par `_resolve_pr_target()` ; le fichier est seedé à chaque invocation dans un dépôt (aide exceptée), annoncé uniquement sur le chemin interactif de la racine ; `GITPR_SKIP_PROJECT_CONFIG_SEED` désactive le seed. `gitpr seed` fait la même chose explicitement et de façon idempotente (section 47).
* **🆕 Frontière Formalisée :** [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) sépare l'**auto-seed d'exécution** (uniquement `gitpr.config.yml`, au premier usage) du **provisionnement explicite** (`gitpr seed`, qui couvre config, smart excludes et linter). 🆕 `docs/config-tui.md` et `docs/pull-request-publication.md` ont gagné la note de migration et l'opt-out pour CI/monorepo.
* **MCP :** `publish_pull_request` utilise le résolveur partagé, renvoie une enveloppe d'erreur pour `BaseBranchError` et expose `warnings` dans le payload et dans le catalogue.
* **Effet de Bord Déclaré :** lancer `gitpr` dans un dépôt crée désormais le fichier à la première exécution. Un fichier existant n'est jamais écrasé ; si `.gitignore` exclut le chemin, un avertissement explique que la valeur ne sera pas versionnée.
* **Tests :** `tests/test_project_config.py` (**49**), `tests/test_project_config_cli.py` (7), `tests/test_pr_base_app.py` (12) + extensions dans `test_config_app.py` (94), `test_config_schema.py` (**55**), `test_config_store.py` (22), `test_mcp_server.py` (117) et `test_core.py` (49).
* **Artefacts :** [ADR-015](docs/plans/ADR-015-project-local-pr-base.md), 🆕 [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md), `docs/plans/develop_natan/20261030_project_pr_base_plansfacts.md`, `docs/survey/20261030_project_pr_base_surveyfacts.md`, et `docs/config-tui.*`/`docs/install-wizard.*`/`docs/pr-descricao-padrao.*`/`docs/pull-request-publication.*` dans les 5 langues.

### **46. Configuration SCM par Dépôt et par Forge (`src/scm_settings.py`)**

* **Ce qu'il était :** la forge (`GITPR_SCM_PROVIDER`), le token (`GITPR_SCM_TOKEN_ENCRYPTED`) et l'URL de base étaient des réglages **globaux à la machine** dans `~/.gitpr/.env`. Une machine ne pouvait plus servir plus d'une forge à la fois, le mauvais credential allait vers le mauvais hôte quand le remote d'un dépôt changeait, et tout hôte auto-hébergé non reconnu était silencieusement mappé sur GitHub.
* **Ce que c'est :** la forge et le credential sont devenus des **propriétés du dépôt** ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)). Un résolveur unique répond à « quelle forge, quel token, quelle URL de base s'appliquent ici ? » pour toutes les surfaces.
* **Ordre de Résolution (`resolve_provider_choice`) :** surcharge de projet (`scm.provider` dans `.gitpr/conf/gitpr.config.yml`) → `detect_provider_from_remote(origin, default=None)` → `github`. Une valeur de forge inconnue dans le fichier de projet est **ignorée avec un avertissement**, jamais fatale ; `default=None` permet à l'assistant de distinguer « reconnu comme GitHub » de « non reconnu » et de **demander** au lieu de supposer.
* **Un Slot par Forge :** `GITHUB_TOKEN_ENCRYPTED`, `GITLAB_TOKEN_ENCRYPTED`, `BITBUCKET_TOKEN_ENCRYPTED`, `AZURE_DEVOPS_TOKEN_ENCRYPTED` (Fernet) et les `*_BASE_URL` correspondantes. Un token ne répond jamais pour une autre forge ; le `GITPR_SCM_TOKEN` brut reste la surcharge globale CI/CD et est essayé en premier sur chaque Provider.
* **Migration Unique et Idempotente (`migrate_legacy_scm_config`, sous verrou) :** déplace `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` + `GITPR_SCM_BASE_URL` vers le slot de la forge qu'ils nomment, **n'écrase jamais** un slot déjà rempli, retire les lignes legacy et continue de fonctionner quand `.env` est en lecture seule (elle se dégrade vers l'environnement du processus). Un token legacy **sans** Provider enregistré est délibérément **laissé en place**, pas deviné.
* **Assistant (`gitpr --init`) :** persiste **uniquement en cas de succès**, écrit dans le slot de la forge choisie, propose l'URL de base stockée comme défaut du prompt (hors GitHub) et n'écrit `scm.provider` dans le fichier de projet que lorsque la forge choisie diffère de celle détectée — le fichier est commité et partagé avec l'équipe.
* **TUI de Configuration :** ligne éditable `PROJECT_SCM_PROVIDER` et ligne en lecture seule `PROJECT_EFFECTIVE_PROVIDER` (forge utilisée + origine), tokens et URLs de base de toutes les forges toujours listés, test de token contre l'hôte du slot lui-même et un groupe `gitlab`.
* **Surfaces Converties :** `src/main.py`, `src/metrics.py`, `src/release_engine.py` et `src/mcp_server.py` résolvent via `get_scm_settings()` et propagent les avertissements du résolveur.
* **Le Non-Interactif ne Demande Jamais :** `validate_or_request_scm_token(..., allow_prompt=False)` dans les hooks, le MCP et la CI renvoie « exécutez `gitpr --init` », ne supprime pas le credential sauvegardé et utilise encore un token validé qui n'a pas pu être persisté.
* **Tests :** `tests/scm/test_scm_settings.py` (**24 scénarios**) + extensions dans `test_init_wizard.py` (20), `test_factory.py` (14), `test_azure_devops_provider.py` (51), `test_bitbucket_provider.py` (49), `test_gitlab_provider.py` (56) et `tests/test_tui_issue_token.py` (8).
* **Artefacts :** [ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md), `docs/plans/develop_natan/20261107_per_provider_scm_settings_plansfacts.md`, `docs/scm-multiforge.*` (5 langues) et le glossaire `docs/plans/glossary-scm-multiforge.md`.

### **47. Sous-commande `gitpr seed` — Bootstrap de la Configuration Locale (`src/core.py`, `src/main.py`)**

* **Ce qu'il était :** la configuration locale du projet (base de PR, smart excludes, règles de linter) naissait **comme effet de bord** du callback racine et du téléchargement de skill. Il n'y avait aucun moyen de seeder explicitement, de façon répétable et idempotente — et une CI qui dépendait de ces fichiers ne le découvrait qu'à la seconde exécution.
* **Ce que c'est :** `gitpr seed` (et le flag global `-sd/--seed`) promeut le bootstrap en opération de premier ordre. `seed_project_configuration()` crée :
  * `.gitpr/conf/gitpr.config.yml` (base de PR, via `ensure_project_config`) ;
  * `.gitpr/conf/gitpr.smart-excludes.json` (exclusions du projet) ;
  * `.gitpr/skill/.gitpr.linter.yml` (règles de linter locales).
* **Ne Détruit Jamais Sans Demande :** un fichier existant n'est **jamais** écrasé ; `--force/-f` écrase volontairement, et `--all/-a` inclut aussi le téléchargement de toutes les skills IA. La fonction renvoie un résumé structuré (`config`, `smart_excludes`, `linter`, `skills_downloaded`) et la commande enregistre la métrique `seed`.
* **🆕 Frontière avec l'Auto-Seed :** [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) formalise la ségrégation : le callback racine/`--install` continue de seeder **uniquement** `gitpr.config.yml` au premier usage, tandis que le provisionnement complet (smart excludes + linter + skills) est toujours explicite via `gitpr seed`. L'opt-out `GITPR_SKIP_PROJECT_CONFIG_SEED` couvre la CI et les monorepos où l'auto-seed ne doit pas s'exécuter.
* **Assistant d'Installation en 5 Étapes :** **1)** Configuration Locale du Projet (nouvelle, confirmable), **2)** Modèles de Skill (avec la configuration optionnelle des ponts de linter externes), **3)** Git Hooks, **4)** Configuration MCP, **5)** Clé d'API. Le saut est devenu explicite.
* **Compatibilité :** `generate_skill_template()` continue d'auto-seeder les smart-excludes, donc l'ancien chemin ne casse pas ; `ensure_project_config()` et `download_skill_file()` ont gagné le paramètre `force` tout en conservant le défaut sans écrasement.
* **Aide :** `HELP_MAP["seed"]` pointe vers `docs/install-wizard.md` et décrit `--force`/`--all`.
* **Tests :** `tests/test_seed_command.py` (**8 scénarios**) — création, idempotence, écrasement forcé, `--all`, CLI et flag ; `tests/test_install_wizard.py` mocke désormais la configuration du linter externe.
* **Artefacts :** `docs/install-wizard.*` (5 langues), 🆕 [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md), `docs/plans/develop_natan/20261105_seed_local_conf_plansfacts.md`, enquête et walkthrough, et le terme **Project configuration seed** dans `CONTEXT.md`.

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|-----------|-----------|-------|
| `tests/test_ascii_chart.py` | 9 🆕 | Formatage compact, états vides, histogramme, série temporelle, onglets et changement d'onglet |
| `tests/test_blame_engine_ranges.py` | 7 | Blame par plage de lignes dans un fichier |
| `tests/test_blame_metrics.py` | 7 | Métriques de blame : profondeur, commits, durée |
| `tests/test_baseline_cli.py` | 54 | Groupe `baseline` : create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Builder de changelog : sections, titres traduisibles, contributeurs, liens et dates |
| `tests/test_chat_backend.py` | 19 | Mémoire du chat, persistance, commandes slash |
| `tests/test_check_cli.py` | 5 | CLI `check` : options, formats, codes de sortie |
| `tests/test_cli_help.py` | 9 | `-h` avant la sous-commande : aide rendue, TUI jamais ouverte |
| `tests/test_commit_app.py` | 17 | `CommitApp` : montage, Stage Selector, auto-stage, saut, annulation, confirmation, succès/échec, auto-sortie, flux headless et routage avec/sans TTY |
| `tests/test_commit_classifier.py` | 30 | Classification Conventional Commits + `pr_number_from_merge()` sur les 4 forges |
| `tests/test_config_app.py` | 94 | TUI de configuration : montage, catégories, suivi des modifications, F2 bloqué, Ctrl+R, recherche, secrets, stores `project`, panneau Smart Excludes |
| `tests/test_config_cli.py` | 9 | Enregistrement de la sous-commande `config`, `-h`, import paresseux, stdout propre |
| `tests/test_config_issue_autoclose.py` | 10 | Getters de configuration et champs de schéma de l'auto-clôture d'issues |
| `tests/test_config_schema.py` | 55 🆕 | Couverture de `DEFAULT_CONFIG` dérivé, pas de doublons, catégories/kind (y compris `KIND_FLOAT`), `seeded`, section skills, bannière et palette |
| `tests/test_config_store.py` | 22 | Aller-retour dans le `.env` temporaire, commentaires et ordre préservés, `remove_config_value()` idempotent |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuration des reviewers suggérés (clés et défauts) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` avec SDK mocké (401 vs. réseau vs. ollama) |
| `tests/test_core.py` | 49 | Flux principaux, diff git, génération de PR, timing, staging, co-autorat, langue des hooks, base de PR |
| `tests/test_diff_failure_cli.py` | 3 | Code de sortie 1 sur échec git, 0 sur diff vide, fail-open sur `--hook` |
| `tests/test_diff_parser.py` | 26 | Parser de diff ligne/hunk, `summarize_patch()` et `split_patch_sections()` |
| `tests/test_doc_links.py` | 11 | Contrat `?lang=` du site, `doc_url()` dynamique, liens de la TUI et parité 5 langues |
| `tests/test_explain_command.py` | 4 | CLI `explain` : succès, clé manquante, diff vide, aide contextuelle |
| `tests/test_external_linters.py` | 33 | Pont Checkstyle : parser XML, sous-processus, traversée de diff, rapport |
| `tests/test_full_diff_encoding.py` | 9 🆕 | Dépôt réel avec un fichier Latin-1 + garde de hunks binaires (chemin rapide, collapse, densité, **collapse défensif dans `generate_pr_content`**) |
| `tests/test_i18n.py` | 22 ✅ | Parité entre langues, clés manquantes/orphelines, identité — **tout au vert** |
| `tests/test_install_wizard.py` | 3 | Assistant d'installation interactif (5 étapes) |
| `tests/test_issue_engine.py` | 4 | Brouillon d'issue structuré |
| `tests/test_linter_metrics.py` | 4 | Métriques de linter : erreurs, avertissements, durée |
| `tests/test_linter_presets.py` | 5 | Presets de linter : résolution et re-téléchargement forcé |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` dans la CLI et dans l'aide contextuelle |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompts MCP et fallback de langue |
| `tests/test_mcp_server.py` | 117 | Outils MCP (18), ressources (19), annotations, patching, CLI directe, offload, contrat de l'extension, base de PR |
| `tests/test_mcp_server_e2e.py` | 6 | Vrai serveur MCP via sous-processus + JSON-RPC stdio |
| `tests/test_mentor_cli.py` | 4 ✅ | CLI `mentor` et opt-in `--mentor` — patches dans `src.main` |
| `tests/test_metrics.py` | 175 🆕 | **Ledger** : écriture synchrone, export, bundle/merge, migration, renderers, coût (y compris les alias Gemini), devise, qualité, modules, fournisseurs, cycle, Token Trend et Latency Histogram |
| `tests/test_net_timeouts.py` | 12 | Timeouts réseau/IA — alignés sur le défaut réel de 180s |
| `tests/test_plugins.py` | 17 | Découverte de plugins, fusion de règles de linter, prompts MCP |
| `tests/test_policy_cli.py` | 26 | Groupe `policy` : list/show/validate/use/off/install/init, confirmation et lockfile |
| `tests/test_pr_base_app.py` | 12 | Modal de changement de base de PR : montage headless, choix et annulation |
| `tests/test_pr_publish_app.py` | 59 | TUI PR Publisher : écrans, flux, reviewers suggérés, `NoticeScreen`, modal de merge, `body_published` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal d'erreur de linter : abandon, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save et payload JSON |
| `tests/test_project_config.py` | 49 | `project_config` : chemin, YAML, seed, résolveur de base, section `scm`, validation contre le remote |
| `tests/test_project_config_cli.py` | 7 | Câblage de la base de projet dans la CLI |
| `tests/test_release_cli.py` | 4 | CLI de release : options, aide avec epilog documenté |
| `tests/test_release_engine.py` | 61 | **Coupure ancrée sur le changelog, `_drop_released()`, liens, carte des PR par commit de merge**, front matter de langue |
| `tests/test_reviewer_resolution.py` | 18 | Échelle de résolution, rejet de nom partiel, dédup, tolérance aux échecs |
| `tests/test_reviewer_suggestion.py` | 18 | Logique de suggestion de reviewers (classement, exclusion, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | CLI `risk` : `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 77 | Matrice du ruleset embarqué : regex, placeholders, niveau, wildcard, fusion, traductions |
| `tests/test_seed_command.py` | 8 | `gitpr seed`/`--seed` : création, idempotence, force, `--all`, métriques |
| `tests/test_skill_command.py` | 10 | Téléchargement et validation de modèle de skill |
| `tests/test_skill_context.py` | 20 | `get_skill_context()` avec `quiet=True`, replis, garde d'artefact de PR |
| `tests/test_skill_frontmatter.py` | 11 | `split_front_matter()` : BOM, CRLF, YAML malformé, allowlist de langue |
| `tests/test_skill_templates.py` | 99 | Balayage paramétré de **chaque modèle publié** contre les références embarquées |
| `tests/test_smart_excludes.py` | 17 | Filtre pathspec intelligent, re-téléchargement forcé, familles binaires (y compris `*.arrow`/`*.gguf`/`*.tflite`/`*.keras`) |
| `tests/test_suggest_reviewers.py` | 19 | Reviewers suggérés dans le flux de PR + résumé de blame |
| `tests/test_tests_command.py` | 4 | Groupe `tests` et CLI `generate`, `--finding` et epilog avec lien |
| `tests/test_thinking_words.py` | 5 | Chargement, parsing avec séparateur `;` et rechargement forcé |
| `tests/test_tui_issue_token.py` | 8 | `allow_prompt=False` : ne demande jamais, ne supprime jamais, pointe vers `gitpr --init` |
| `tests/test_updater.py` | 55 | **Barrière PyPI** : TTL écoulé, cache legacy/corrompu, écriture atomique, `force`, `bounded_urlopen`, comparateur PEP 440, pré-release, commutateur de skip strict et barrière de sous-commande |
| `tests/test_usage_log.py` | 27 | Journal d'usage : nom dérivé de la date, écriture synchrone, silence, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump sémantique : major/minor/patch, cibles et validation |
| `tests/test_web_links.py` | 26 | Normalisation de remote (https/ssh/scp/credentials/port) et motifs d'URL par forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 | Activation de pack, lockfile, refus |
| `tests/application/use_cases/test_baseline_gate.py` | 40 | Barrière : chargement, classification, annotation, couches et abandon |
| `tests/application/use_cases/test_baseline_summary.py` | 12 | Vue de baseline en lecture seule pour MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 | Corrélation diff/constats/tests/historique, dégradation |
| `tests/application/use_cases/test_create_baseline.py` | 30 | Création/mise à jour avec provenance et préservation des décisions |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 | Lots, sévérité, hit de cache, clé manquante |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 3 | Cas d'usage explain : dict du fournisseur et clé manquante |
| `tests/application/use_cases/test_generate_test_file.py` | 5 | Cas d'usage de génération de tests : dict du fournisseur et chaîne brute |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 | Installation de pack, origines et conflits |
| `tests/application/use_cases/test_run_check.py` | 8 | Orchestration de check : ordre, dégradation, codes de sortie |
| `tests/application/use_cases/test_suppress_finding.py` | 29 | Suppression/retrait dans les quatre portées |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 | Validation : schéma fermé, checksum, compatibilité |
| `tests/badge/test_badge_append.py` | 9 | Ajout idempotent du badge au corps de la PR |
| `tests/badge/test_badge_builder.py` | 19 | Composition du Markdown shields.io, échappement, règle de couleur, style |
| `tests/badge/test_badge_cli.py` | 15 | Commande `gitpr badge` : extrait, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Comptage des alertes ; `None` sans règle configurée |
| `tests/badge/test_badge_offline.py` | 6 | Garde hors ligne (réseau interdit, loopback excepté) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` sur chaque chemin de publication |
| `tests/badge/test_badge_paths.py` | 10 ✅ | Point d'injection unique dans les deux publishers |
| `tests/demo/test_demo_app.py` | 22 | TUI de la visite : navigation, écrans, modal d'aide |
| `tests/demo/test_demo_isolation.py` | 14 | La visite ne touche ni `~/.gitpr` ni le réseau |
| `tests/demo/test_demo_runner.py` | 33 | Machine à états `DemoState` : avancer, reculer, terminé, mode texte |
| `tests/demo/test_demo_scenarios.py` | 47 | Intégrité des scénarios enregistrés (arithmétique des hunks) dans les 5 langues |
| `tests/demo/test_demo_text_mode.py` | 18 | Sortie en texte brut (`--no-tui`) pour CI et enregistrements |
| `tests/demo/test_fake_ai_provider.py` | 21 | Contrat du faux fournisseur : reflète `call_ai_model` argument par argument |
| `tests/domain/baseline/test_baseline_comparator.py` | 21 | Classification, comptages par statut, `resolved`→`new` |
| `tests/domain/baseline/test_baseline_fingerprint.py` | 12 | Digest de référence, ligne brute jamais stockée, stabilité |
| `tests/domain/baseline/test_baseline_manifest.py` | 26 | JSON canonique, checksum, clés inconnues, invariants |
| `tests/domain/baseline/test_baseline_types.py` | 15 | Statuts persistés, portées et précédence |
| `tests/domain/baseline/test_suppression_policy.py` | 52 | Couches, spécificité, overrides, dette échue |
| `tests/domain/check/test_check_config.py` | 3 | Précédence CLI > env > défauts |
| `tests/domain/check/test_check_output.py` | 3 | Dispatch de format et écriture atomique |
| `tests/domain/check/test_check_types.py` | 8 | Sévérité en 3 niveaux, aliases de spec, résumé |
| `tests/domain/finding/test_finding_types.py` | 3 | Type de constat canonique : `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Formatage uniforme et déduplication `[Gitleaks + Regex]` |
| `tests/domain/linter/test_structured_findings.py` | 44 | `findings_out` : constats structurés et compteur de lignes côté nouveau |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 | Builder pur : honnêteté épistémique, URLs supprimées, ordre préservé |
| `tests/domain/policy/test_policy_checksum.py` | 15 | SHA-256 du pack et détection d'édition manuelle |
| `tests/domain/policy/test_policy_compatibility.py` | 22 | Compatibilité de version et de credentials |
| `tests/domain/policy/test_policy_manifest.py` | 42 | Schéma fermé, bloc `baseline:`, erreurs de manifest |
| `tests/domain/policy/test_policy_resolver.py` | 47 | Précédence, composition en couches, pack actif × base |
| `tests/domain/pr/test_explain_section_builder.py` | 6 | Rendu Markdown, parsing tolérant, `[FILL]`, payload dict et `None` |
| `tests/domain/risk/test_risk_calculator.py` | 5 | Formule 50/30/20, clamp 0–100, élévation obligatoire |
| `tests/domain/risk/test_risk_rules.py` | 5 | Poids, seuils, glob de chemin critique, override YAML |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 | Détection par config, manifest et répertoire ; override ; échec averti |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 | Chemin conventionnel par framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Cas d'usage complet : review → IA → validation → classification → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extracteur partagé entre le chat et `fix` |
| `tests/fix/test_fix_cli.py` | 31 | Routage de sous-commande, options, dry-run par défaut, `--force` |
| `tests/fix/test_fix_history.py` | 21 | Ledger `.gitpr/fix_history.json` : écriture atomique, lecture |
| `tests/fix/test_fix_settings.py` | 10 | Les cinq `GITPR_FIX_*` et les replis sur valeur invalide |
| `tests/fix/test_patch_applier.py` | 19 | Wrapper de `git apply` : check/apply/reverse, branche, statut |
| `tests/fix/test_patch_extractor.py` | 13 | Blocs délimités → diff unifié validé |
| `tests/fix/test_patch_safety_classifier.py` | 22 | Matrice safe/review_required/experimental et codes de raison |
| `tests/fix/test_resolve_last_review.py` | 13 | Sélection de la dernière review, exclusion des reviews de fichier |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback` : reverse et les trois refus |
| `tests/infrastructure/baseline/test_local_baseline_repository.py` | 24 | Lecteur/écrivain unique : écriture atomique, checksum, réparation |
| `tests/infrastructure/ci/test_junit_builder.py` | 2 | JUnit XML : failure/system-out |
| `tests/infrastructure/ci/test_masking.py` | 6 | Masquage de credentials avant sérialisation |
| `tests/infrastructure/ci/test_sarif_builder.py` | 2 | SARIF 2.1.0 et `tool.driver.rules` |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 | `git log` sûr : commits, bugs et reverts sur 90 jours |
| `tests/infrastructure/git/test_test_matcher.py` | 5 | Correspondance test ↔ production par convention |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Pont Bandit : disponibilité, parsing, mapping |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC : sous-processus durci, timeout, binaire manquant |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Pont Gitleaks : masquage des secrets, portée par fichier |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Pont Semgrep : parsing JSON, sévérité, saut non-Python |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 | Effet réel de la baseline sur la review, le lint et le risque dans un vrai dépôt |
| `tests/integration/test_baseline_exit_code_contract.py` | 8 | Code de sortie 1 pour une baseline inapplicable et 2 dans `check` |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Effet réel du pack sur la review, le lint et le risque (avec garde réseau autouse) |
| `tests/issue_autoclose/test_close_issue_wiring.py` | 26 | Câblage du flux de PR : flags, prompt, publication réelle, nettoyage du registre |
| `tests/issue_autoclose/test_insert_close_line.py` | 19 | Insertion idempotente et reconnaissance des mots-clés de clôture |
| `tests/issue_autoclose/test_issue_registry.py` | 26 | Registre local : aller-retour, atomicité, corruption, `.gitignore`, correspondance de repo, migration `github.yml` |
| `tests/issue_autoclose/test_register_created_issue.py` | 13 | Enregistrement post-création : multi-forge, Provider persisté, échecs avalés |
| `tests/issue_autoclose/test_resolve_close_issue.py` | 42 | Arbre de décision : filtres, `is_open`, PR, enregistrements morts, `not-supported`, confirmation |
| `tests/review/test_diff_normalizer.py` | 20 | Sauts de ligne, validation de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource` : provenance, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orchestration : barrières, PR, diff, linter, commentaire optionnel |
| `tests/review/test_review_pr_cli.py` | 25 | CLI `review-pr` : options, refus avant l'IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 51 | Provider Azure DevOps : org/project, PR, `ScmNotSupportedError`, relecture d'issue |
| `tests/scm/test_bitbucket_provider.py` | 49 | Provider Bitbucket : Basic auth, workspace, PR, `close #N` |
| `tests/scm/test_contract.py` | 44 | Contrat `ScmProvider` : signatures, dataclasses, erreurs, défauts d'issue |
| `tests/scm/test_factory.py` | 14 | `resolve_scm_provider()` + `detect_provider_from_remote(default=None)` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` déprécié → délègue au Provider |
| `tests/scm/test_github_provider.py` | 86 | Provider GitHub : REST, headers, PR, issues, releases, relecture de reviewers |
| `tests/scm/test_gitlab_provider.py` | 56 | Provider GitLab : API v4, namespace, en-têtes synthétisés, `overflow`, `Closes #N` |
| `tests/scm/test_init_wizard.py` | 20 | Assistant `--init` : détection de forge, slot par forge, validation, persistance |
| `tests/scm/test_release_publish.py` | 8 | Publication de release par forge (GitHub/GitLab) |
| `tests/scm/test_scm_settings.py` | 24 | Résolveur forge/credential : précédence, migration legacy, avertissements, lecture seule |
| `tests/split/test_apply_split_plan.py` | 11 | Le seul module mutant : staging sélectif, un commit par groupe |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unités → groupes → pré-validation de conflits → messages |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, budget/troncature, appel IA, validation de réponse |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()` : texte ↔ modèle, pur |
| `tests/split/test_selective_stager.py` | 11 | Staging de sous-ensemble, vérification contre HEAD, index propre |
| `tests/split/test_split_cli.py` | 31 | CLI split : options, dry run par défaut, `--apply` |
| `tests/unit/test_version_consistency.py` | 5 ⚠️ | SSOT de version : `pyproject.toml`, `CLAUDE.md` et `GEMINI.md` contre `src.updater.__version__` — **2 échecs** parce que les deux docs disent encore 1.6.0 |
| `tests/unit/test_check_ci_docs_parity.py` | 3 🆕 | Parité 5 langues et 7 sections de `docs/check-ci.*` + ADR-0011 + références dans `action.yml` |
| `tests/sync_i18n.py` | — | Script de vérification de couverture i18n (scaffold ; jamais exécuté) |

**Total :** **3436 scénarios collectés dans 160 modules de test** (64 à la racine + 12 dans `tests/application/use_cases/` + 10 dans `tests/fix/` + 10 dans `tests/scm/` + 10 dans `tests/infrastructure/` + 21 dans `tests/domain/` + 7 dans `tests/badge/` + 6 dans `tests/demo/` + 6 dans `tests/split/` + 5 dans `tests/issue_autoclose/` + 4 dans `tests/review/` + 3 dans `tests/integration/` + 2 dans `tests/unit/` ; **+14 scénarios, +1 fichier** depuis le rapport précédent).

**Notes de qualité pour cette version :**
- **✅ Le blocage qui figeait la suite reste clos :** `-c` sans `--hook` n'ouvre pas `CommitApp` quand il n'y a pas de TTY — le routage consulte `_may_ask_the_user()`.
- **✅ Le test de commit headless reste stable :** `Button.press()` + `pilot.pause()`, prédicat de montage et timeout de 10 s.
- **🆕 Graphiques testables sans Textual :** `tests/test_ascii_chart.py` exerce le renderer pur et la composition des onglets séparément — la logique des graphiques ne dépend pas d'un vrai terminal.
- **🆕 Le collapse binaire a gagné une couverture d'appelant externe :** `test_full_diff_encoding.py` prouve désormais que `generate_pr_content` assainit un diff brut avec `U+FFFD`, au-delà du chemin venu de git.
- **🆕 La note de migration de `action.yml` est devenue un test :** la parité de documentation verrouille désormais aussi ADR-0011 et les références dans le fichier d'action.
- **⚠️ 2 échecs de cohérence de version :** `CLAUDE.md` et `GEMINI.md` déclarent encore 1.6.0 alors que `src/updater.py` est déjà à 1.7.0 ; `tests/unit/test_version_consistency.py` échoue sur ces deux scénarios. Le correctif est de mettre à jour les deux documents.
- **`tests/conftest.py` reste hermétique :** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false`, `LANG_VERSION` à la version du code et `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
- **Garde réseau à plusieurs endroits :** `tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py` et `tests/split/conftest.py`.
- **Vraies fixtures git :** `tests/fix/git_fixture.py`, `tests/split/git_fixture.py` et `tests/test_full_diff_encoding.py`.
- **La suite de l'extension est séparée :** 7 scénarios Mocha dans `extensions/vscode/src/test/mcp_client.test.ts`, dont 1 s'intègre réellement au serveur MCP Python via stdio ; elle n'entre pas dans le comptage pytest.
- **La CI s'exécute sur Python 3.10 et 3.13** avec `fail-fast: false`, et la release a son propre workflow avec Trusted Publishing.

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n :** **1787 clés** dans **les six** dictionnaires (`es.json`, `es_es.json`, `fr.json`, `fr_fr.json`, `pt_br.json`, `pt_pt.json`). Le code utilise les mêmes clés : **0 non traduite** et **0 orpheline**. C'était 1772 × 6 dans la fenêtre précédente.
* **🆕 `__lang_version__` est monté à v0.0.42 :** les nouvelles chaînes de prix par token, la devise, la validation `KIND_FLOAT`, les graphiques/onglets ASCII et l'installation du VSIX sont entrés dans les six dictionnaires ; le rafraîchissement OTA livre la nouvelle traduction sans réinstallation.
* **Sources de traduction en verrouillage :** une nouvelle clé doit exister dans le code (source), dans `langs/pt_br.json` (**liste maîtresse**), dans les dicts FR/ES de `scripts/sync_all_langs.py` et dans les valeurs curatées de `scripts/fix_mangled_i18n_keys.py` ; l'extracteur de clés est de l'**AST** des deux côtés.
* **Nouveaux sujets :** **aucun** nouveau sujet canonique dans cette fenêtre — la documentation a travaillé sur les sujets existants et sur les ADR.
* **🆕 Sujets mis à jour dans cette fenêtre :**
  - `docs/editor-extension.*` (**5 fichiers**) — release VSIX découplée, barrière de marketplace granulaire et installation en un clic.
  - `docs/auto-update.*` (**5 fichiers**) — sémantique stricte de `GITPR_SKIP_UPDATE_CHECK` et note de migration.
  - `docs/commit-message-ia.*` (**5 fichiers**) — note de migration pour le commit interactif sans TTY.
  - `docs/tutorial/install-from-source.*` (**5 fichiers**) — même note de migration pour le commutateur de mise à jour.
  - `docs/marketplace-publishing-guide.md` (**1 fichier, EN**) — liens directs vers les releases `ext-v*` ; encore partiel.
  - `docs/config-tui.md` (**EN uniquement**) — seed de configuration du projet et opt-out CI/monorepo.
  - `docs/pull-request-publication.md` (**EN uniquement**) — même note de seed/opt-out.
  - `README.md` — lien vers l'installation du VSIX.
* **`CONTEXT.md` (mis à jour) :** le glossaire du domaine est passé de **35 à 44 termes** (+9). Entre autres, sont entrés **Granular marketplace gate** (en remplacement de *Fail-fast secret check*), **Seed segregation**, **Draft extension release**, **ASCII chart**, **Chart data**, **Tabbed metrics view** et les termes de prix/devise.
* **Documentation en 5 langues :** **52 sujets canoniques** dans `docs/` (compte inchangé) — **50 avec couverture complète dans les 5 langues** et **2 sujets partiels** (`github-issue-prompt-com-gh`, `marketplace-publishing-guide`).
* **Nouveaux artefacts de spécification :** [ADR-0011](docs/adr/0011-action-unified-check-migration.md), [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md), [ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) et [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) dans `docs/adr/` (désormais **8 fichiers** ; la numérotation de `docs/adr/` n'est pas continue parce que les ADR-012/013/015/016 vivent dans `docs/plans/`).
* **Skills locaux de Claude Code :** `.claude/skills/` avec **29 skills** (compte inchangé).
* **Index de Mémoire :** `.claude/memory/MEMORY.md` avec **41 patterns** (🆕 +1).
* **Rapports de tâche :** `docs/claude-code/reports/develop_natan/` (**119** ; **+1**) + `docs/claude-code/reports/feat/` (**12**, inchangé) + `docs/claude-code/reports/main/` (**1**) et `docs/gemini/reports/` (**54** ; **+12**), plus `docs/gemini/walkthrough/` (**31** ; **+12**).
* **Rapports d'état :** `docs/reports/` — c'est le **24ᵉ rapport** (v0.0.24) ; le v0.0.23 n'est jamais écrasé.
* **Plans de développement :** **174** fichiers dans `docs/plans/` (+13) + **36 fichiers dans `docs/gemini/plans/`** (+12) + **46 fichiers dans `docs/survey/`** (+12).

---

## **🔄 Pipeline de Distribution**

1. **PyPI (canal unique) :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mise à jour obligatoire avec fenêtre glissante :** l'exécution vérifie PyPI au démarrage et **bloque avec le code de sortie 1** s'il existe une version plus récente, en imprimant `pip install --upgrade gitpr-cli`. La réponse est cachée pendant **1 heure** et `--update` interroge **toujours** le réseau. La comparaison est PEP 440 : une pré-release distante ne bloque pas une installation stable, et une version illisible compte comme inconnue. La barrière couvre les sous-commandes quand il y a un TTY ; CI, pipes, hooks, MCP et `-h` restent exemptés.
3. **GitHub Releases :** production automatisée par `release.yml` sur les tags `v*` — build, validation, Trusted Publishing et création de la release avec les artefacts.
4. **GitHub Actions :** workflow `pr-review.yml` + `action.yml` (installe via pip) + `tests.yml` (matrice Python 3.10 et 3.13) + `extension.yml` (VSIX ; 🆕 job `github-release` en `ext-v*` et job `publish-marketplaces` avec une barrière granulaire par PAT) + `release.yml` (PyPI + GitHub Release en `v*`). L'`action.yml` appelle une seule étape `gitpr check` — **rupture déclarée** pour ceux qui utilisaient `--linter` + `--fullreview`, couverte par le test de parité de documentation et par l'ADR-0011.
5. **Serveur MCP :** point d'entrée `gitpr-mcp` via `pyproject.toml`
6. **Extension d'Éditeur :** paquet VSIX indépendant (`natafiuza.gitpr`), avec canaux VS Code Marketplace et Open VSX ; nécessite Node.js 20+ seulement pour le build, pas pour l'usage. Le `.vsix` est attaché au GitHub Release de `ext-v*` même lorsque la publication sur les marketplaces est désactivée.
7. **Templates et langues OTA :** `templates/` et `langs/*.json` servis depuis GitHub (main) — `__lang_version__` **v0.0.42** 🆕 renouvelle les copies locales de `~/.gitpr/langs/` ; `__scripts_version__` **v0.0.4** renouvelle les hooks.
8. **Version dérivée du code :** `pyproject.toml` utilise `version = {attr = "src.updater.__version__"}` — le `__version__` est l'**unique** source, garanti par [ADR-014](docs/adr/0014-canonical-package-version-ssot.md) et par `tests/unit/test_version_consistency.py`.
9. **`package-data` compte :** les quatre packs embarqués (`src/policy_packs/*`) voyagent dans la wheel.
10. **✅ État de la release — 1.6.0 et 1.7.0 publiées :** le PyPI sert la **1.7.0**. La 1.6.0 a été taguée sur `f5088c4` ; la 1.7.0 a sa propre section dans le `CHANGELOG.md` (`6d1f1dc`) et son bump sur `2354a30`. `develop_natan` a été mergée dans `main` par la PR #310.
11. **⚠️ Tag 1.7.0 désaligné :** le tag `v1.7.0` sur origin pointe encore sur `b2688c4`, un commit **antérieur** au bump, où `__version__` valait 1.6.0. La garde de `release.yml` compare le tag et le `__version__` ; aligner le tag sur le commit `2354a30` (ou le recréer) est l'élément en attente pour que le GitHub Release reflète le code publié.

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.23)**

| Domaine | v0.0.23 (précédent) | v0.0.24 (actuel) |
|------|-------------------|-----------------|
| **Version GitPR** | 1.6.0 — dans le changelog et sur `develop_natan`, **sans tag**, PyPI en **1.5.0** | **1.7.0** — publiée sur PyPI ; le tag `v1.7.0` existe mais pointe sur le commit d'avant le bump |
| **Version Langue** | v0.0.41 | **v0.0.42** — dictionnaires synchronisés, +15 clés |
| **Version des Scripts de Hook** | v0.0.4 | v0.0.4 (inchangée) |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Inchangé |
| **Langues** | 5 langues, 6 dictionnaires | Inchangé |
| **Sous-commandes** | 16 | 16 — inchangé ; 4 groupes (baseline 6, metrics 8, policy 7, tests 1) |
| **Flags CLI** | 34 options à la racine | 34 — inchangé |
| **Configuration SCM** | Par dépôt et par forge | Inchangé |
| **Clôture d'Issue** | Multi-forge | Inchangé |
| **Seed de Configuration** | `gitpr seed`/`--seed` + wizard de 5 étapes | Comportement inchangé ; 🆕 frontière formalisée dans l'ADR-0019 |
| **Couches** | 9 paquets de domaine, 15 cas d'usage | Inchangé — `ascii_chart.py` est de la présentation pure |
| **Outils MCP** | 18 outils / 19 ressources / 7 prompts | Inchangé |
| **`DEFAULT_CONFIG`** | 55 clés, dérivées du schéma (`seeded`) | **56 clés** — `GITPR_METRICS_CURRENCY` |
| **Schéma de Config** | 100 `ConfigField` / 18 catégories / 55 `seeded` | **109 / 18 / 56** — `KIND_FLOAT` + 9 champs de devise/prix |
| **i18n (clés par fichier)** | 1772 × 6 | **1787 × 6** — 0 manquantes, 0 orphelines ✅ |
| **Barrière de Mise à Jour** | TTL couru de 1 h, `-u` toujours en ligne, PEP 440 | Code inchangé ; 🆕 note de migration pour le commutateur strict |
| **Linter** | Regex + secrets + SAST + `findings_out` + baseline-aware | Inchangé |
| **Diff / Smart Excludes** | + garde de hunk binaire et 100+ motifs | + **collapse défensif dans `generate_pr_content`** et 4 nouvelles familles binaires |
| **Git Hooks** | post-checkout/post-merge/pre-push dans le ledger | Inchangé — `prepare-commit-msg` reste non interactif |
| **Couche SCM** | 4 forges, pagination, `web_links.py` | Inchangé |
| **Release engine** | Section `[1.6.0]` ; 1.5.0 publiée ; 1.6.0 sans tag | Section `[1.7.0]` ; **1.6.0 et 1.7.0 publiées** ; le tag 1.7.0 désaligné |
| **Métriques** | Token Trend et Latency Histogram dans CLI/TUI/MCP | + **graphiques ASCII et onglets** dans le dashboard ; 🆕 prix par modèle et devise |
| **Commit TUI** | Garde de TTY et test headless stable | Inchangé ; 🆕 note de migration pour le flux sans TTY |
| **Extension** | Workflow durci avec `dry_run` et fail-fast | + **release VSIX découplée** et **barrière granulaire par marketplace** |
| **Documentation** | 52 sujets (50 complets + 2 partiels) | **52 sujets (50 complets + 2 partiels)** — 4 nouveaux ADR |
| **Agenda du domaine** | `CONTEXT.md` avec 35 termes | **44 termes** — Granular marketplace gate, Seed segregation, ASCII chart etc. |
| **CI** | `tests.yml` + `action.yml` + `extension.yml` + `release.yml` | Fichiers de base inchangés ; `extension.yml` restructuré |
| **Suite de Tests** | 3422 scénarios (159 fichiers) | **3436 scénarios (160 fichiers) — +14 scénarios, +1 fichier** |
| **Stabilité de la Suite** | Aucun blocage connu | **2 échecs de cohérence de version** dans `test_version_consistency.py` (docs en 1.6.0) |
| **Dépendances** | Aucune nouvelle dépendance Python | Aucune nouvelle — `ascii_chart.py` n'utilise que la stdlib |
| **Commits depuis le rapport** | 16 commits, 0 merge | **13 commits, 13 merges** (#295–#310) |
| **PRs référencés** | 14 PRs (#261 … #291) | **13 PRs mergés** (#295, #296, #297, #298, #299, #301, #302, #304, #305, #307, #308, #309, #310) ; issues fermées #294, #300, #303 et #306 |
| **Index de Mémoire** | 40 patterns | **41 patterns** |
| **Rapports de tâche** | 118 claude-code, 42 gemini, 19 walkthrough | **119 claude-code (+12 feat, +1 main), 54 gemini, 31 walkthrough** |
| **Plans de développement** | 161 plans, 24 gemini, 34 surveys | **174 plans, 36 gemini, 46 surveys** |

---

## **🚧 Prochaines Étapes**

* **🆕 Mettre à jour `CLAUDE.md` et `GEMINI.md` vers 1.7.0 :** le bump `2354a30` n'a changé que `src/updater.py`, et les deux documents disent encore 1.6.0. `tests/unit/test_version_consistency.py` a **2 échecs** à cause de cela — c'est le correctif le plus urgent de cette fenêtre.
* **🆕 Aligner le tag `v1.7.0` :** le tag sur origin pointe sur `b2688c4` (avant le bump, où `__version__` valait 1.6.0). La garde de `release.yml` compare le tag et la version ; déplacer ou recréer le tag sur le commit `2354a30` fait correspondre le GitHub Release à la 1.7.0 du PyPI.
* **🆕 Traduire le `marketplace-publishing-guide` :** le sujet reste en anglais seulement ; PT-BR, PT-PT, ES et FR restent en attente.
* **🆕 Traduire les éditions EN-only de `config-tui` et `pull-request-publication` :** les notes de migration du seed/CI et de l'opt-out ne sont entrées que dans la version anglaise.
* **🆕 Publier l'extension sur les marketplaces :** la barrière granulaire évalue déjà `VSCE_PAT` et `OVSX_PAT` indépendamment et le VSIX est déjà attaché au GitHub Release, mais la publication dépend des credentials.
* **🆕 Documenter les prix/devise dans `metricas-telemetria` :** les champs `GITPR_METRICS_PRICE_*` et `GITPR_METRICS_CURRENCY` sont dans la TUI et dans le code, mais le sujet de télémétrie ne les explique pas encore.
* **Note de migration du SCM par dépôt :** les clés `GITPR_SCM_PROVIDER`, `GITPR_SCM_TOKEN_ENCRYPTED` et `GITPR_SCM_BASE_URL` ont été retirées du `.env` par la migration automatique. Ceux qui ont automatisé l'écriture de ces clés doivent savoir qu'il y a désormais un slot par forge. **Élément hérité, encore ouvert.**
* **Traduire `docs/metricas-telemetria.*` :** PT-BR, PT-PT, ES et FR restent encore en arrière de l'original anglais. **Élément hérité, encore ouvert.**
* **Suivre l'hôte self-hosted non reconnu :** la résolution par remote ne reconnaît que les domaines qui contiennent le nom de la forge ; un GitLab/GitHub sur un domaine personnalisé retombe sur GitHub jusqu'à ce que quelqu'un lance `gitpr --init` et enregistre l'override. **Élément hérité, encore ouvert.**
* **Fournisseur Anthropic Claude :** support direct de l'API Claude (`claude-sonnet-5`). **Élément hérité, encore ouvert.**
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires. **Élément hérité, encore ouvert.**
* **Note de migration du `GITPR_SKIP_UPDATE_CHECK` :** ✅ **terminé** — `docs/auto-update.*` et le tutoriel d'installation décrivent le parser strict.
* **Note de migration du `-c` interactif :** ✅ **terminé** — `docs/commit-message-ia.*` dans les 5 langues explique le flux sans TTY et `--hook`.
* **Tag et publication 1.6.0/1.7.0 :** ✅ **terminé** — le PyPI sert la 1.7.0 (aligner le tag 1.7.0 reste comme nouvel élément ci-dessus).
* **Merge de `develop_natan` dans `main` :** ✅ **terminé** — PR #310.
* **Dettes du config TUI :** ✅ **terminé** — audit formel et clôture consignés dans les rapports du 2026-11-09.
* **Élément Smart Excludes dans la TUI :** ✅ **terminé** — audit formel et clôture consignés.
* **Note de migration de l'`action.yml` :** ✅ **terminé** — `action.yml`, ADR-0011 et le test de parité avec 3 scénarios.

### ✅ Terminés dans cette fenêtre (2026-11-08 → 2026-11-10)

* ~~**Le dashboard n'avait que des sections textuelles**~~ 🆕 — PR #307 (`07822f7`) : `src/ascii_chart.py`, onglets **Events**/**Charts & Trends**, raccourcis `1`/`2` et `tests/test_ascii_chart.py` (9 scénarios) ([ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
* ~~**Les binaires pouvaient entrer par un appelant externe**~~ 🆕 — PR #304 (`c1cddbb`) : `collapse_binary_hunks()` au début de `generate_pr_content` et `*.arrow`/`*.gguf`/`*.tflite`/`*.keras` dans les smart excludes.
* ~~**La release de l'extension dépendait de la release du paquet**~~ 🆕 — PR #302 (`3bcc6c9`) : job `github-release` en `ext-v*`, barrière granulaire par marketplace et installation du VSIX dans les 5 langues.
* ~~**Le coût et la devise étaient figés**~~ 🆕 — PR #295 (`c5485dd`) : `KIND_FLOAT`, `GITPR_METRICS_CURRENCY`, 8 champs de prix et aliases Gemini avec prix.
* ~~**La rupture de l'`action.yml` n'était pas formalisée**~~ 🆕 — PR #301 (`7bd6678`) : note de migration dans `action.yml`, [ADR-0011](docs/adr/0011-action-unified-check-migration.md) et test de parité.
* ~~**Le fallback ES/FR du site n'avait pas de contrat**~~ 🆕 — `80378b0` : [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md).
* ~~**La frontière entre auto-seed et `gitpr seed` n'était pas écrite**~~ 🆕 — `98b23cc` : [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md).
* ~~**La note de migration du commit interactif n'existait pas**~~ 🆕 — `1995213` : `docs/commit-message-ia.*` dans les 5 langues.
* ~~**La sémantique stricte de `GITPR_SKIP_UPDATE_CHECK` n'était pas documentée**~~ 🆕 — `1995213` : `docs/auto-update.*` et tutoriel.
* ~~**Le seed de configuration du projet n'avait pas de note de migration**~~ 🆕 — `7cf4ac8` : `docs/config-tui.md` et `docs/pull-request-publication.md`.
* ~~**Les dettes du config TUI et des Smart Excludes étaient ouvertes**~~ 🆕 — `8886976` et `a7c316a` : audit formel et clôture.
* ~~**Le changelog n'avait pas la 1.7.0**~~ 🆕 — `6d1f1dc` : section `## [1.7.0] - 2026-11-10`.
* ~~**La version du paquet et des dictionnaires était en 1.6.0/v0.0.41**~~ 🆕 — `2354a30` : bump vers **1.7.0** et **v0.0.42**.
* ~~**La 1.6.0 était taguée mais non publiée**~~ — le PyPI a ensuite servi la 1.6.0 puis la 1.7.0.
* ~~**Toute la fenêtre était sur `develop_natan`, sans merge**~~ — la PR #310 a emmené la branche sur `main`.

---

**Rapport généré le :** 2026-11-10  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
