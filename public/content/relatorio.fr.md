# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.23 (2026-11-08)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.23) :**
- **Le Provider SCM est devenu une propriété du dépôt, plus de la machine :** `src/scm_settings.py` résout, dans une seule fonction, **quel Provider, quel jeton et quelle base URL** valent dans ce dépôt — override du projet → remote `origin` → `github`. Chaque forge a gagné son propre slot d'identifiant dans `~/.gitpr/.env` (`GITHUB_/GITLAB_/BITBUCKET_/AZURE_DEVOPS_TOKEN_ENCRYPTED` + `*_BASE_URL`), et les clés héritées `GITPR_SCM_PROVIDER`/`GITPR_SCM_TOKEN_ENCRYPTED`/`GITPR_SCM_BASE_URL` sont migrées **une seule fois, de façon idempotente** ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)).
- **La clôture automatique d'issue a cessé d'être réservée à GitHub :** GitLab et Bitbucket implémentent la relecture du contrat (`get_issue`, `get_default_branch`, `closing_line`) et Azure DevOps se dégrade en `not-supported` au lieu d'ignorer la fonctionnalité. Le registre local est passé à `.gitpr/conf/issue.yml`, avec lecture transparente du `github.yml` hérité et `IssueInfo.is_open` ([ADR-010](docs/adr/0010-multiforge-issue-autoclose.md)).
- **Le bootstrap de la configuration locale est devenu une commande :** `gitpr seed` (et le flag `-sd/--seed`) sème `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` et `.gitpr/skill/.gitpr.linter.yml`, avec `--force` pour écraser et `--all` pour inclure toutes les skills IA. L'assistant d'installation a été réorganisé en **5 étapes**, en commençant par la configuration locale du projet.
- **La TUI de configuration a gagné deux surfaces nouvelles :** la catégorie **Smart Excludes** (liste des sources + éditeur de fichier du projet) et la catégorie **check** (les 9 `GITPR_CHECK_*`). Et le `DEFAULT_CONFIG` a cessé d'être une copie manuelle — il est désormais **dérivé du schéma** via `ConfigField.seeded`, si bien que code et schéma ne peuvent plus diverger.
- **Le dashboard a gagné tendance et latence :** `metrics` rend désormais **Token Trend** (sparkline + barres par jour, plafond de 14 jours) et **Latency Histogram** (moyenne, médiane et P95) dans le CLI, la TUI et l'outil MCP `get_usage_metrics` ; le ledger a cessé de jeter le `duration_ms` envoyé uniquement dans le `meta` de l'événement.
- **Le prompt a cessé de recevoir des déchets binaires :** `collapse_binary_hunks()` détecte les hunks avec une avalanche de `U+FFFD` et les réécrit en `Binary files … differ`, avec un chemin rapide O(1) qui préserve les fichiers Latin-1 légitimes ; le catalogue de smart excludes a gagné **100+ motifs** dans 8 familles binaires.
- **La dette de stabilité de la suite a été soldée :** le `-c` n'ouvre la `CommitApp` que lorsqu'il y a un vrai TTY (`_may_ask_the_user()`), ce qui **a débloqué** le scénario qui figeait l'exécution complète à ~33%, et le test headless du commit a été stabilisé (clic sémantique + timeout plus long). La suite s'exécute désormais jusqu'au bout.
- **La 1.5.0 a été publiée sur PyPI :** la dette de release de la fenêtre précédente est soldée. La **1.6.0** est dans le `CHANGELOG.md` et sur la branche `develop_natan`, mais **n'a encore ni tag ni publication**.
- **Version actuelle :** 1.6.0 (**sans tag `v1.6.0` ; le PyPI sert la 1.5.0**)
- **Version des dictionnaires de langue :** v0.0.41 (🆕 +3 depuis la v0.0.38)
- **Version des scripts de hook :** v0.0.4 (inchangée)
- **Publication :** PyPI (`pip install gitpr-cli`) — canal binaire supprimé
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licence :** LGPL-2.1
- **Langues prises en charge :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues, 6 dictionnaires)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **CLI Framework :** Click (pour les commandes, les flags et le formatage terminal) — **16 sous-commandes** (`badge`, `baseline`, `check`, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `seed` 🆕, `split`, `tests`). Quatre d'entre elles sont des **groupes** : `baseline` (6 sous-commandes), `metrics` (8), `policy` (7) et `tests` (1). **34 options Click à la racine** — 🆕 `-sd/--seed`.
* **UI/Terminal :** Textual — TUI pour le chat interactif, l'édition d'issues, l'help screen, le dashboard de métriques, le PR Publisher, la **CommitApp** (`src/ui/commit_app.py`, 220 lignes), les erreurs du linter (`LinterApp`), la configuration (`ConfigApp`), la modale `NoticeScreen`, le tour de `gitpr demo` (`src/ui/demo/`), l'assistant de migration du ledger (`src/ui/metrics_migration_app.py`) et le `PrBaseApp` (`src/ui/pr_base_app.py`). 🆕 La `ConfigApp` a gagné le panneau **Smart Excludes** (liste des sources + `TextArea` + bouton de création) et le `LinterApp` désactive désormais la command palette.
* **Architecture en Couches :** `src/domain/` (règle pure, sans I/O) + `src/application/use_cases/` (orchestration, **15 cas d'usage**) + présentation (CLI/TUI). **9 paquets de domaine** : `baseline`, `check`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`. 🆕 Hors du domaine : `src/issue_autoclose/` (flux de clôture d'issue au-dessus de `ScmProvider` et du registre local), `src/project_config.py` (configuration versionnée) et `src/scm_settings.py` (résolution de Provider et d'identifiant par dépôt).
* **Extension d'Éditeur :** `extensions/vscode/` — client TypeScript fin du MCP pour **VS Code et Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). Ne contient aucune règle métier. 🆕 Le paquet transporte `icon.png`, `LICENSE` (LGPL-2.1), `homepage` et `bugs.url` pour le listing sur les marketplaces.
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API, des jetons GitHub et des jetons SCM des forges.
* **Configuration :** `python-dotenv`, `pyyaml` (linter statique et configuration du projet) + schéma déclaratif propre (`src/config_schema.py`, **100 `ConfigField`** et **18 catégories** — 🆕 `smart_excludes` et `check`). 🆕 Le `DEFAULT_CONFIG` est **dérivé** des champs avec `seeded=True` (**55 clés**) ; les trois clés héritées de SCM (`GITPR_SCM_PROVIDER`, `GITPR_SCM_TOKEN_ENCRYPTED`, `GITPR_SCM_BASE_URL`) ont disparu. `packaging` alimente la comparaison de versions de la barrière (PEP 440) et la compatibilité des policy packs.
* **Fournisseurs IA :** Intégration via le SDK officiel Google GenAI, le SDK OpenAI (DeepSeek) et le SDK OpenAI (Ollama local). Défauts configurés : `gemini-pro-latest` / `gemini-flash-lite-latest` et `deepseek-v4-pro` / `deepseek-v4-flash`.
* **APIs de Forge :** `requests` (REST) — abstraction multi-forge dans `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py` ; `src/github_api.py` conservé comme shim déprécié. 🆕 Le contrat d'issue (`get_issue`, `get_default_branch`, `closing_line`) est implémenté par les **quatre** forges quand cela a du sens.
* **Linter / SAST :** regex YAML + bridge Checkstyle + ruleset de secrets embarqué (`src/security_ruleset.py`) + 4 bridges dans `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), avec modèle normalisé dans `src/domain/linter/sast_finding_mapper.py` et constat canonique dans `src/domain/finding/`.
* **Infrastructure CI :** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) et `masking.py` (masquage des identifiants avant la sérialisation de tout artefact).
* **Interne de Git :** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py`, `risk_history_reader.py` et `test_matcher.py`.
* **Persistance locale :** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) avec WAL, `PRAGMA user_version`, clés UUID et transport par bundle. 🆕 Le `duration_ms` retombe sur `meta["duration_ms"]` avant de supposer `0`.
* **Réseau :** `src/net.bounded_urlopen` (`UPDATE_FETCH_HARD_TIMEOUT = 3.0`) borne le **wall clock y compris DNS** dans la barrière de mise à jour. `get_issue()`/`get_default_branch()` utilisent un timeout de **5 s** (`GITPR_ISSUE_AUTOCLOSE_TIMEOUT`).
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 outils annotés**, **19 ressources**, 7 prompts (comptage inchangé). 🆕 `get_usage_metrics` retourne `token_trend` et `latency`, et la résolution de forge/identifiant passe par `src.scm_settings`.
* **Tests :** Pytest + `unittest.mock` (**159 modules de test** — 63 à la racine, 12 dans `tests/application/use_cases/`, 10 dans `tests/fix/`, 10 dans `tests/scm/`, 10 dans `tests/infrastructure/` (baseline 1, ci 3, git 2, linter 4), 21 dans `tests/domain/` (baseline 5, check 3, finding 1, linter 2, mentor 1, policy 4, pr 1, risk 2, tests_generation 2), 7 dans `tests/badge/`, 6 dans `tests/demo/`, 6 dans `tests/split/`, 5 dans `tests/issue_autoclose/`, 4 dans `tests/review/`, 3 dans `tests/integration/`, 2 dans `tests/unit/` —, **3422 scénarios collectés**) + tests e2e du serveur MCP via subprocess réel (JSON-RPC stdio) + fixtures de dépôt git réel (`tests/fix/`, `tests/split/`, `tests/test_full_diff_encoding.py`) + garde d'isolation réseau (`tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py`, `tests/split/conftest.py`). L'extension a sa propre suite Mocha (7 tests contre le serveur MCP réel).
* **Empaquetage :** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}` ; packs embarqués via `package-data` ; `packaging>=23.0`. L'extension est empaquetée séparément (`vsce package`, VSIX).
* **CI/CD :** GitHub Actions — `pr-review.yml` + `action.yml` (appelle `gitpr check`) + `tests.yml` (matrice Python 3.10/3.13) + `extension.yml` (build, test et empaquetage du VSIX ; publication sur Marketplace/Open VSX sur les tags `ext-v*`) + `release.yml` (tests, vérification tag/version via SSOT, build sdist/wheel, `twine check --strict`, Trusted Publishing OIDC sur PyPI et création de GitHub Release sur les tags `v*`). 🆕 `extension.yml` a gagné `workflow_dispatch` avec `dry_run`, une garde tag ↔ version et un échec explicite quand l'identifiant manque.

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec le LLM en demandant une sortie strictement JSON.
* **Map-Reduce (Diffs Géants) :** Lorsque le diff dépasse ~90k tokens, le divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce). Prend en charge les PRs, commits et Issues. Le `provider`/`model` sont agrégés entre les lots et `meta`/`modules` sont transmis à `log_command_metric`.
* **Tokenizer Local :** `tokenizer.json` pour une estimation précise des tokens avant l'envoi à l'IA.
* **Estimation des Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()` avec fallback vers le tokenizer local.
* **Optimisation Native de Git :** Flags `-U1`, `-w`, `-M`, `-B` sur les commandes `get_git_diff` et `get_git_full_diff`.
* **`get_git_staged_diff()` :** lance `git diff --cached -U1 -w -M -B -- <SMART_EXCLUDES>` avec `encoding="utf-8"`/`errors="replace"` et `stdin=DEVNULL`, en retournant `None` quand git échoue (jamais une chaîne vide déguisée). C'est la source privilégiée du message de commit : ce qui n'est pas indexé n'entre pas dans le prompt.
* **`get_split_diff()` :** Le diff propre au `split`, avec `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **délibérément sans** réutiliser `get_git_diff()`.
* **🆕 Garde de Hunk Binaire :** tous les points d'entrée de diff (`get_git_diff`, `get_git_staged_diff`, `get_unstaged_diff`, `get_git_full_diff`, `current_diff`, `get_check_diff`, `tests_generate`, `risk`) appliquent `collapse_binary_hunks()` — les hunks avec des séries contiguës de ≥8 `U+FFFD` (ou ≥32 avec densité ≥10%) passent à `Binary files … differ`. Le chemin rapide O(1) renvoie le diff intact quand il n'y a pas de `U+FFFD`, préservant les Latin-1/Windows-1252 légitimes.
* **🆕 Seed de la Configuration Locale (`seed_project_configuration()`) :** crée `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` et `.gitpr/skill/.gitpr.linter.yml`, en renvoyant un résumé structuré (`config`, `smart_excludes`, `linter`, `skills_downloaded`). N'écrase jamais sans `force=True`.
* **Seed de la Configuration du Projet :** le callback racine et `--install` appellent `ensure_project_config()` (`src/project_config.py`) à chaque invocation dans un dépôt, sauf sur les chemins d'aide. Un fichier existant n'est **jamais** écrasé ; la création n'est annoncée que sur le chemin interactif de la racine et peut être désactivée par `GITPR_SKIP_PROJECT_CONFIG_SEED` (escape hatch de la suite de tests).
* **`get_base_branch(quiet=)` :** la résolution de la base délègue à `resolve_pull_request_base()` ; le `quiet` évite que l'avertissement de fallback ne corrompe la TUI ou le flux JSON-RPC.
* **Pre-Save (`--pre-save`) :** Flag caché de debug qui enregistre le payload complet (instruction système + prompt) en JSON avant chaque appel à l'IA.
* **Smart Excludes à Deux Couches :** Couche globale (`~/.gitpr/conf/`) + locale du projet (`./.gitpr/conf/`), unies à l'exécution. 🆕 Les loaders globaux et locaux ont été blindés contre un `.env` corrompu et des paths illisibles — un mauvais fichier ne fait jamais tomber un diff ou un lint.
* **Métriques avec Suivi du Temps :** `log_command_metric()` dans tous les flux, avec `meta`, `modules` et `duration_ms`.
* **Résolution Centralisée de la Sortie :** `resolve_output_path()` — par défaut dans `.gitpr/reports/{type}/`.
* **Wizard SCM (`run_scm_init_wizard()`) :** `gitpr --init` — détecte la forge via le remote origin, demande les extras par forge, valide le jeton et persiste **seulement en cas de succès** ; 🆕 écrit dans le slot de la forge choisie et ne crée un override dans le projet que lorsqu'elle diffère de celle détectée.
* **Template de Skill de Release (`ensure_release_skill_template()`) :** Télécharge `templates/gitpr.release.*.md` à la première utilisation de `gitpr release`.
* **Garde contre les Skills Corrompus :** `_CORRUPTED_PR_SKILL_ARTIFACT` documente le fragment JSON qui a fui dans le template `pt_br`, et `get_skill_context()` le supprime **en mémoire** quand `action_type == "pr"`.
* **Registre de Skills Partagé :** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` dans `src/config.py`.
* **Moteur de Review avec Périmètre de Cache (`generate_pr_content`) :** `cache_scope` et `store_diff` ; le `cache_scope` porte aussi le pack de policy actif.
* **Trailer de Co-paternité :** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, préserve les trailers de tiers. C'est ce trailer que la TUI de commit applique au message édité avant `execute_git_commit()`.
* **Capture de Sortie Git Blindée :** les appels à `subprocess.run` dans `src/cache.py`, `src/core.py`, `src/issue_engine.py` et `src/main.py` qui capturent la sortie de git utilisent `encoding="utf-8"` **avec** `errors="replace"` — y compris `get_git_staged_diff()`.
* **🆕 Chargement de `.env` Résilient :** `load_dotenv()` dans `core.py`, `i18n.py` et `spinner.py` s'exécute dans un `try/except` — un `.env` absent, illisible ou dans un répertoire en lecture seule n'empêche pas l'initialisation.
* **Échec de Git ≠ Diff Vide :** `diff_text is None` sort avec le code 1 ; un diff réellement vide sort avec **0** ; en `--hook`, le chemin est **fail-open**.
* **Subprocesses Blindés :** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` sur chaque `subprocess.run` ; vérification de connexion via socket avant les opérations réseau.

### **2. Système Global de Plugins (`src/plugins.py`)**

* **Architecture de Plugins :** Charge les plugins depuis `~/.gitpr/plugins/`, en s'appliquant à **tous les projets**.
* **Plugins de Linter (`linter/`) :** Fichiers `.yml` fusionnés avec le `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`) :** Fichiers `.md` qui étendent le contexte système.
* **Factory Closures :** `get_linter_plugins` et `get_prompt_plugins` isolent l'état entre les sessions.
* **Commande `--plugins` :** Liste tous les plugins globaux installés avec leurs types et chemins.
* **Documentation Multilingue :** `docs/plugins-system.md` en 5 langues.
* **Relation avec les Policy Packs :** le pack est une **troisième** couche de règles — les plugins appartiennent à la machine, `.gitpr.linter.yml` appartient au projet et le pack est versionné et commité.

### **3. Interface CLI et Configuration (`src/main.py` et `src/config.py`)**

* **Setup Initial :** Détecte la première exécution, crée `~/.gitpr/` et demande interactivement les clés, les préférences et la langue.
* **Routage des Commandes :** Gère tous les flags et les **16 sous-commandes** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline`, `check` et 🆕 `seed`.
* **Comportement par Défaut :** Exécuter `gitpr` sans flags ouvre la TUI du PR Publisher.
* **Flags (34 options Click à la racine) :** 🆕 `-sd/--seed` — sème la configuration locale du projet et sort, en enregistrant la métrique `seed`. `--close-issue`/`--no-close-issue` restent mutuellement exclusifs, refusés avec exit code 1 avant tout prompt ou écriture, avec la règle que `-h` l'emporte sur le conflit. Les autres (`--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` etc.) restent inchangés.
* **Branche du `-c`/`--commit` :** `--hook` → comportement silencieux et merge-aware ; sans hook → `CommitApp`. 🆕 **Le chemin sans hook exige désormais un TTY :** le routage consulte `_may_ask_the_user()` et, dans un script/CI/pipe, retombe sur le flux console au lieu d'ouvrir la TUI.
* **Flux de Clôture d'Issue :** `_prepare_close_issue(flags, base, may_ask)` s'exécute **avant** le Stage Selector, réutilise le contexte SCM résolu et renvoie la décision (ligne de clôture + issue) plus le registre. La ligne entre dans `insert_close_line(pr_desc_body, decision)` au point unique de montage du corps. `_publish_pr_directly` renvoie un **bool** pour que le registre ne soit nettoyé qu'après une publication réelle (ADR-007).
* **Résolution de la Base du PR :** la branche `--base` passe par `_resolve_pr_target()`, qui corrige une valeur obsolète du fichier du projet quand l'exécution est interactive et, hors de là, signale le refus. Le fichier `.gitpr/conf/gitpr.config.yml` est semé à chaque invocation dans un dépôt, sauf sur les chemins d'aide.
* **Barrière de Mise à Jour dans les Sous-commandes :** le callback du groupe revenait avant la barrière racine, donc `gitpr release`, `fix`, `review-pr` et les autres n'étaient **jamais** bloqués par une nouvelle release. La barrière s'exécute avant le `return`, conditionnée par `may_ask and not update and not help_flag and not _subcommand_help_in_argv()`. `_may_ask_the_user()` exige un **TTY des deux côtés** — CI, pipes, cron, hooks et le serveur MCP restent libres.
* **`-h` Avant la Sous-Commande Corrigé :** `_subcommand_help_in_argv()` existe parce que Click **nettoie les arguments du contexte** avant que le callback du groupe ne s'exécute. Le balayage de l'`argv` brut traite désormais le cas `gitpr -h config`, qui ouvrait auparavant la TUI de configuration au lieu de l'aide. Couvert par `tests/test_cli_help.py`.
* **Sous-commande `check` — 8 options :** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on …`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` et `--policy <name>@<version>`.
* **🆕 Sous-commande `seed` — 2 options :** `--force/-f` (écrase les fichiers existants) et `--all/-a` (transfère aussi toutes les skills IA).
* **Groupe `baseline` — 6 sous-commandes :** `create`, `show`, `validate`, `update`, `suppress` et `unsuppress`.
* **Groupe `policy` — 7 sous-commandes :** `list`, `show`, `validate`, `use`, `off`, `install` et `init`.
* **Groupe `metrics` — 8 sous-commandes :** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` et le `hook-event` caché.
* **Sous-commande `risk` — 4 options :** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Sous-commande `mentor` — 2 options :** `--finding`, `--provider`.
* **🆕 `DEFAULT_CONFIG` Dérivé :** `src/config.py` importe le `DEFAULT_CONFIG` de `src/config_schema.py`, monté comme `{field.key: field.default for field in FIELDS if field.seeded}` — **55 clés**. La dérive entre code et schéma est devenue impossible ; les trois clés héritées de SCM ont disparu dans la même fenêtre.
* **Variables d'Environnement :** les quatre `GITPR_BASELINE_*`, les deux de mentor, les deux de policy et `GITPR_RISK_INCLUDE_IN_REVIEW`. **Hors `DEFAULT_CONFIG` :** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY`, les **9 `GITPR_CHECK_*`** (désormais avec catégorie dans le schéma), `GITPR_SKIP_UPDATE_CHECK` (booléen strict) et `GITPR_ISSUE_AUTOCLOSE`, `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` et `GITPR_SKIP_PROJECT_CONFIG_SEED`.
* **Aide Contextuelle :** `gitpr -h --explain` rend ; `HELP_MAP["explain"]` et `HELP_PRIORITY["explain"]` pointent vers `docs/explain-pr`, et `gitpr tests`/`gitpr tests generate` ont leur propre `epilog=`. 🆕 `HELP_MAP` a gagné `seed` et les liens de `close_issue`/`no_close_issue` pointent désormais vers `docs/issue-autoclose`.
* **Couche d'Écriture du `.env` :** `read_env_file_values()` lit **uniquement le fichier** via `dotenv_values` ; `save_config_values()` avec `set_key` ; `remove_config_value()` avec `unset_key`.
* **Barrière Baseline dans le Flux :** `main.py` résout la barrière de baseline avant le lint, le review, le full review, l'audit de fichier, le review distant et le risque.
* **Publication Directe Refactorisée :** `_publish_pr_directly` (`--no-edit`) délègue à **`publish_pull_request_use_case`** — le même chemin que l'outil MCP `publish_pull_request` — et renvoie si la forge a créé le PR.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)**

* **Interface Interactive Complète :** TUI pour réviser, éditer et publier des Pull Requests directement depuis le terminal.
* **7 Écrans Modaux :** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` et `NoticeScreen`.
* **Trois d'Entre Eux Servent Aussi le Commit :** `StageFilesScreen`, `CommitMessageScreen` et `CommitProgressScreen` sont importés par `CommitApp` — aucun widget n'a été dupliqué, et l'aide `_with_suppressed_stdout` est partagée elle aussi.
* **`body_published` Distingue Créer d'Ouvrir :** la `PrPublishApp` marque quand le corps a réellement été créé/mis à jour sur la forge, et non seulement quand un PR existant a été ouvert. C'est ce signal qui permet de nettoyer le registre de l'issue seulement après une vraie publication.
* **Badge dans le Corps du PR :** `attach_pr_badge()` s'exécute dans `src/main.py` juste après que `pr_data` est complet — **point d'injection unique**.
* **`_attach_reviewers` avec résolution :** Résout ce que l'utilisateur a saisi **avant** l'envoi, signale ce qui a été écarté et, sur un `422` de lot, **réessaie un par un**. Quand un fournisseur tiers suit le contrat hérité et renvoie `None` à la relecture, le journal dit `(no read-back)` au lieu de `attached None`.
* **Reviewers Suggérés :** Interroge la forge pour obtenir des reviewers suggérés ; `_reviewer_suggestion_view()` assemble les `resolutions` via `resolve_candidates`.
* **Bindings :** F1 (Aide), F2 (Enregistrer .md local), F3 (Publier via la forge), Échap (Quitter).
* **Flux d'Auto-Commit :** Linter → message IA → confirmation → commit → push → publie le PR.
* **Modale de Progression dans le Merge :** `CommitProgressScreen` avec un `title` optionnel et `mark_finished(show_close=False)` ; le succès affiche `✅ PR #N merged successfully!`, les conflits et erreurs inattendues passent par `_on_merge_failure`.
* **Gestion de PR Existant / Auto-Upstream / Flux de Merge** — inchangés (`GITPR_AUTO_MERGE`).

### **5. Module API GitHub (`src/github_api.py`)**

* **Shim Déprécié :** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` et les autres fonctions délèguent à `src/infrastructure/scm/github_provider.py` ; il émet un `DeprecationWarning` et conserve les tuples legacy `(ok, data, status)` — aucun nouveau code ne doit l'importer.

### **6. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le diff sans dépenser de quotas d'IA.
* **Règles YAML :** Lit `.gitpr.linter.yml` (créé via `--skill` ou `gitpr seed`).
* **Plugins de Linter :** Règles supplémentaires depuis `~/.gitpr/plugins/linter/*.yml`.
* **Wildcard d'Extension :** `extensions: ["*"]` signifie **tous** les fichiers, y compris ceux sans suffixe et les dotfiles.
* **Ruleset de Secrets Intégré :** `load_linter_rules()` fusionne `src/security_ruleset.py` **après** les règles du projet et des plugins. Sept règles, toutes `extensions: ["*"]` : cinq `error` et deux `warning`. L'alerte **ne répète jamais la valeur**.
* **Couche des Policy Packs :** `load_linter_rules()` applique la fusion en couches du pack actif et **écrase les sévérités**.
* **Constats Structurés (`findings_out`) :** en plus des chaînes d'alerte, le moteur retourne les constats normalisés du type canonique `src/domain/finding/`.
* **Ponts SAST Opt-In :** Semgrep, Gitleaks et Bandit tournent sur le chemin de fichier entier **et** sur le chemin de diff, avec la dédup `[Gitleaks + Regex]`, le masquage des valeurs (`AKIA****`) et un avertissement lorsque l'outil est activé mais absent du `PATH`.
* **`skip_external` :** `parse_diff_and_lint(..., skip_external=True)` dans le flux distant.
* **Rapport Consolidé :** `generate_linter_report_content()` consolide les erreurs regex + externes dans `.gitpr/reports/linter/` — généré uniquement en cas de violations.
* **Sensible à la baseline :** les alertes sont annotées avec le statut de baseline avant de devenir un rapport.

### **7. Sécurité et Authentification (`src/security.py`, `src/scm_settings.py`, `src/tui_issue.py`)**

* **Chiffrement :** Génère la clé maîtresse `secret.key` dans `~/.gitpr/`.
* **Protection des Jetons :** `encrypt_data`/`decrypt_data` pour les clés IA et pour le jeton de **chaque** forge (un slot par Provider SCM).
* **🆕 Identifiant par Forge :** un jeton GitLab ne répond jamais pour un dépôt GitHub. Le `GITPR_SCM_TOKEN` brut reste le **seul** override global de CI/CD et est tenté en premier sur toutes les forges.
* **Validation Multi-Forge :** `validate_or_request_scm_token(provider, repo_display, allow_prompt=True)` — 401 → boucle de réauthentification en préservant le brouillon. 🆕 `allow_prompt=False` (hooks, MCP, CI) ne demande jamais, n'efface jamais le jeton enregistré et renvoie l'instruction « lancez `gitpr --init` ».
* **Secrets dans la TUI de Configuration :** Les champs `KIND_SECRET` sont édités masqués, **n'affichent jamais** la valeur en clair et sont chiffrés avec Fernet avant d'être écrits.
* **Analyse de Secrets dans le Flux :** Le linter — qui tourne dans le hook pre-commit — est une barrière d'identifiants avec des règles identiques sur chaque machine.
* **Masquage dans la CI :** `src/infrastructure/ci/masking.py` supprime les clés privées, l'ID/secret de clé d'accès AWS, les jetons GitHub, Google, OpenAI et Slack, `Bearer`, le basic-auth dans l'URL et les affectations génériques de mot de passe **avant** la sérialisation de l'artefact. L'extension a un miroir en TypeScript (`masking.ts`).

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI comme Source Unique :** `get_latest_remote_version()` interroge `https://pypi.org/pypi/gitpr-cli/json` et écrit le cache. Elle accepte `force=True`, utilisé par `--update` pour aller **toujours** au réseau.
* **Fenêtre Glissante de 1 Heure (`UPDATE_CACHE_TTL_SECONDS = 3600`) :** le cache n'est plus « valide tant que la date locale est aujourd'hui ». Le document est `{"checked_at": <ISO>, "version": ...}` ; `_cache_is_fresh()` accepte un âge dans `[0, 3600)` secondes.
* **Validation à la Lecture :** un vrai `dict`, un horodatage analysable, une version chaîne non vide. Un horodatage **dans le futur** compte comme expiré, et l'ancien format avec `date` est traité comme expiré et réécrit.
* **Écriture Atomique :** `tempfile.NamedTemporaryFile` dans le même répertoire + `os.replace`, avec nettoyage du fichier temporaire en cas d'échec.
* **Fetch Blindé (`bounded_urlopen`) :** un corps non-JSON et un payload sans version sont **rejetés avant d'entrer dans le cache**. Un plafond de 3 s de temps réel, DNS inclus.
* **Comparaison PEP 440 :** `_coerce_version()` utilise `packaging.version.Version`, en tolérant le `v` initial et les suffixes PEP 440 ; `parse_version()` conserve le contrat de tuple pour les appelants externes. `_is_newer()` **ignore une pré-release distante** sur une installation stable et traite une version illisible d'un côté ou de l'autre comme inconnue.
* **Barrière Obligatoire (`enforce_update_required()`) :** Retourne `True` (après avoir affiché les deux versions et la commande pip) lorsque la version publiée est plus récente ; `False` lorsque tout est à jour, lorsque la version distante est **inconnue (hors ligne)** ou lorsque la vérification est désactivée.
* **Sémantique Stricte de l'Échappatoire :** `GITPR_SKIP_UPDATE_CHECK` est analysée contre `_SKIP_TRUTHY_VALUES` (`1`, `true`, `yes`, `on`, `y`).
* **`check_and_update()` :** Se contente d'interroger et d'**informer**, n'installe jamais.
* **Versionnement Centralisé :** `__version__` (**1.6.0** 🆕), `__lang_version__` (**v0.0.41** 🆕), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`. Le SSOT du paquet est un contrat explicite ([ADR-014](docs/adr/0014-canonical-package-version-ssot.md)) couvert par `tests/unit/test_version_consistency.py`.
* **✅ État de la release — 1.5.0 publiée :** la dette de la fenêtre précédente est soldée : le PyPI sert la **1.5.0**. La **1.6.0** est déjà dans le `CHANGELOG.md` (`## [1.6.0] - 2026-11-07`), mais **n'a encore été ni taguée ni publiée** — la barrière continuera de ne pas bloquer tant que le tag `v1.6.0` ne passera pas par `release.yml`.

### **9. Interface de Chat Interactive (`src/ui/chat_app.py`)**

* **TUI Complète :** Historique des messages, saisie multi-ligne, barre d'état avec bindings visibles. `ENABLE_COMMAND_PALETTE = False`.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique persisté par branche, avec continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear`. `/tests` (et les alias localisés) délègue au **même cas d'usage** que la CLI.
* **Auto-Patching (F5), Rafraîchissement du Diff (F2), Export de Session (F6).**
* **Extracteur Partagé :** F5 et `ctrl+s` appellent `patch_extractor.extract_code_blocks()` — comportement visible identique, sans logique dupliquée.
* **Aide Localisée pour de Vrai :** le `ChatHelpScreen` compose chaque ligne via `__()`. 🆕 Les liens d'aide ne pointent plus vers `blob/main` sur GitHub et passent par `doc_url()`, avec le contrat `?lang=` du site (`es_es`→`es`, `fr_fr`→`fr`).

### **10. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système à la première exécution et l'enregistre dans `GITPR_LANG` ; le répertoire de profil est créé **avant** la persistance.
* **5 Langues, 6 Dictionnaires :** en_us (défaut/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Fichiers Versionnés :** le `__lang_version__` (**v0.0.41** 🆕) contrôle la mise à jour des paquets (`langs/*.json`).
* **✅ Couverture :** les **six** fichiers ont **1772 clés** chacun. Le code utilise les mêmes clés ; il n'y a ni clé sans dictionnaire ni orpheline. Le `tests/test_i18n.py` reste **22 au vert**.
* **🆕 La v0.0.41 a du contenu :** les nouvelles chaînes de SCM par forge, `seed`, les graphiques de métriques et le résumé de blame sont entrés dans les six dictionnaires ; la rénovation OTA livre une traduction nouvelle.
* **Photo de l'Environnement (`AMBIENT_ENV_KEYS`) :** `frozenset(os.environ)` capturé **immédiatement avant** le `load_dotenv()` au niveau du module.
* **Cache avec Indexation par Langue :** Les réponses IA en cache incluent la langue courante dans le keying MD5.
* **Source de Traduction en Lockstep :** une nouvelle clé doit exister dans le code, dans `langs/pt_br.json` (liste maîtresse), dans les dicts FR/ES de `scripts/sync_all_langs.py` et dans les valeurs curées de `scripts/fix_mangled_i18n_keys.py` ; l'extracteur utilise **AST**.

### **11. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA avec des caractères braille et des mots de « réflexion ».
* **263 entrées par langue :** Synchronisées entre les 5 langues. `_load_thinking_words()` / `reload_thinking_words()` acceptent `force=`.
* **La TUI de Commit N'Utilise Pas le Spinner :** la TUI affiche `CommitProgressScreen` avec sa propre barre animée, et l'appel à l'IA tourne dans un `threading.Thread` daemon. En `--hook`, le spinner classique reste le seul retour utilisateur.
* **🆕 Chargement de `.env` Blindé :** un échec de lecture n'empêche pas le spinner de démarrer.

### **12. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Pris en Charge :** Google Gemini, DeepSeek et Ollama (local), avec un primaire/secondaire par fournisseur.
* **Mode JSON & Paramètres Déterministes :** `temperature=0.0` et `top_p=0.1` ; fallback automatique entre les fournisseurs configurés.
* **Timeout IA :** défaut de **180s** (`GITPR_AI_TIMEOUT`).
* **Appels avec `quiet` :** la release et les nouvelles fonctionnalités transmettent `quiet` jusqu'à la couche IA, pour que le spinner ne corrompe jamais une sortie `--format json`.
* **Un appel à la fois, avec cache :** mentor et explain réutilisent le cache MD5 par prompt.
* **Payload Déjà Parsé :** `parse_explain_payload()` et `generate_test_file` acceptent le **dict** livré par le fournisseur, avec un fallback vers la chaîne JSON brute (anciennes réponses du cache).
* **Suppression de Stdout dans le Commit :** `_generate_commit_msg()` redirige le `sys.stdout` vers `os.devnull` pendant l'appel, en le restaurant dans le `finally` — un print du fournisseur ne corrompt pas l'écran Textual.

### **13. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Keying par hash MD5 du diff et du prompt, avec indexation par langue.
* **Sélection du Dernier Review (`resolve_last_review()`) :** Choisit l'enregistrement `review`/`fullreview` **le plus récent** pour un couple repo+branche — c'est la porte d'entrée de `gitpr fix` et de `gitpr mentor`.
* **Diff Révisé (`reviewed_diff`) :** Champ en tête d'enregistrement qui conserve le diff effectivement révisé.
* **Télémétrie et Durée :** Persistance de `duration_ms` et `meta_raw`. 🆕 La release passe la clé de cache par langue déclarée, si bien que changer la langue de la skill génère une nouvelle section au lieu de réutiliser l'ancienne.
* **Lecture pour le Dashboard :** `scan_cache_files_for_dashboard()` est un **pont de pré-migration**.
* **Effet de l'Assainissement du Skill :** comme `get_skill_context()` supprime le fragment corrompu du prompt de PR, la clé MD5 change et les anciennes réponses contaminées ne sont plus réutilisées.
* **`git config` Décodé en Sécurité :** les lectures de `user.name`/`user.email` utilisent `encoding="utf-8"` + `errors="replace"`.
* **Le Commit Réutilise le Cache :** la TUI appelle `generate_pr_content("commit", "commit", diff_text, provider)`, donc le même message pour le même diff ne déclenche pas un second appel à l'IA.

### **14. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff actuel, Historique de la branche (`-ht`) et Archéologie par Blame (`-b`).
* **Publication Multi-Forge :** le F3 crée l'issue sur la forge **résolue pour le dépôt** via `provider.create_issue` — Azure DevOps lève `ScmNotSupportedError`.
* **Map-Reduce pour les Issues :** Le contexte au-dessus de ~90k tokens est découpé et unifié.
* **Gestion du 401 :** Signalisation de réauthentification sans fermer l'application.
* **`git remote -v` Décodé en Sécurité :** l'appel utilise `encoding="utf-8"` + `errors="replace"`.
* **🆕 Registre Provider-Agnostique :** après une création réussie sur n'importe quelle forge supportée, `register_created_issue()` enregistre le numéro **et le Provider** dans `.gitpr/conf/issue.yml`. Les échecs de bookkeeping sont avalés — un problème local ne transforme jamais une création réussie en erreur — et la première écriture ajoute le fichier au `.gitignore`.
* **🆕 Métrique par Forge :** l'événement de création est désormais dérivé dynamiquement (`issue:<provider>_create`) au lieu d'être figé sur GitHub.

### **15. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Suit l'évolution et la paternité historique avec classification des commits (`ORIGIN` vs `REFACTORING`).
* **Métriques de Blame :** Événements via `log_blame_metric()` avec la profondeur et le nombre de commits analysés.
* **Il alimente désormais aussi le risque :** le lecteur d'historique du score (`risk_history_reader.py`) utilise la même fenêtre de 90 jours et classe bugs/reverts par motif, sans appeler l'IA.
* **🆕 Alimente la Suggestion de Reviewers :** l'analyse de blame d'un PR classe les fichiers en `files_with_blame`/`files_without_blame`, consommés par le résumé de la section 27.

### **16. Serveur MCP et Invocation CLI Directe (`src/mcp_server.py`)**

* **18 Outils MCP Annotés :** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates`, `review_remote_pr`, `get_baseline_status`, `get_usage_metrics`, `run_check` et `publish_pull_request`.
* **Paramètres Opt-In dans le Contrat :** `review_code` et `full_review` acceptent `mentor: bool = False` ; `generate_pr_description` accepte `explain: bool = False`. Ils sont **purement par paramètre** — jamais OR'és avec les défauts du `.env`.
* **19 Ressources + 7 Prompts Templatisés :** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Registre de Skills Aligné :** `mcp_server.SKILL_FILES` a **11 entrées** (`tests`, `explain` et `mentor` inclus) ; `TestSkillRegistryAgreement` passe.
* **Invocation CLI Directe :** `gitpr-mcp --tool <name> [--tool-args '<json>']` invoque n'importe quel outil sans démarrer le serveur stdio ; `gitpr-mcp --list` affiche le registre complet en JSON.
* **Isolation du Stdout Réel :** `_write_real_stdout()` écrit dans le `sys.__stdout__` original, avec un fallback vers `buffer` ou un ré-encodage avec `errors='replace'` sur les code pages Windows legacy.
* **Offload de l'Event Loop :** Décorateur `_offload` (`anyio.to_thread.run_sync`) sur chaque outil — l'ordre des décorateurs compte (`@mcp.tool` **au-dessus** de `@_offload`).
* **Échecs comme Erreur Structurée :** les outils renvoient l'échec **comme donnée** au lieu de lever.
* **🆕 `get_usage_metrics` avec Graphiques :** le payload inclut désormais `token_trend` (fenêtre de 14 jours au plus) et `latency` (moyenne/médiane/P95), les mêmes chiffres que le CLI et le dashboard.
* **🆕 Identifiant par Dépôt :** toute la résolution SCM passe par `src.scm_settings` ; sans jeton, l'outil répond avec la commande qui corrige (`gitpr --init`) au lieu de tenter de demander — un appel d'outil ne peut pas s'arrêter et attendre l'utilisateur.
* **Métriques MCP :** `run_check` et `publish_pull_request` écrivent des lignes locales (`mcp:check`, `mcp:publish_pr`).
* **Le serveur MCP n'est jamais bloqué par la barrière :** `--mcp`/`gitpr-mcp` figure sur la liste d'exemptions depuis la première version de la barrière.

### **17. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`)**

* **Périmètre par Dépôt :** Étiquette `📁 Repository: owner/repo` et filtrage strict par projet.
* **Scan Asynchrone avec Overlay :** thread worker en arrière-plan avec `ProgressBar` ; les tests attendent `workers.wait_for_complete()` avant d'asserter.
* **Lecture du Ledger :** le dashboard interroge le `telemetry.db` dans le thread de travail, en rendant les sections (coût, qualité, modules, Providers, cycle) à partir des mêmes renderers que le CLI utilise.
* **🆕 Deux Sections Nouvelles :** `section_token_trend` et `section_latency`, avec des renderers partagés avec le CLI ; le `max-height` des cartes est passé de 20 à 24 pour faire tenir les graphiques.
* **Assistant de Migration (`src/ui/metrics_migration_app.py`) :** propose d'importer les fichiers legacy, d'importer **et** reconstruire depuis le cache IA, ou « pas maintenant ».
* **Artefacts d'Export Destrackés :** le `.gitignore` est la seule source sur `.gitpr/metrics/export/`.

### **18. Système de Métriques et Télémétrie (`src/metrics.py`)**

* **Périmètre par Dépôt :** tous les événements indexés par `repo_name`, résolu par `src/infrastructure/git/identity.py`.
* **Événements de Hook, Linter et Blame :** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. `gitpr check` écrit `command="check"` avec `findings_total`/`findings_errors` et le code de sortie.
* **Événement `issue_autoclose` :** une ligne par décision de clôture, avec `asked`, `accepted` et `skipped_reason` dans le `meta`.
* **🆕 Token Trend (`TOKEN_TREND_MAX_DAYS = 14`) :** série quotidienne continue (les jours sans usage entrent comme zéro) avec sparkline et barres horizontales par jour ; choisit le côté (input/output) qui bouge le plus.
* **🆕 Latency Histogram :** distribution par plages avec **moyenne, médiane et P95** ; les événements sans durée mesurée restent dehors pour ne pas tirer les statistiques vers zéro ni gonfler la plage `< 1s`.
* **🆕 Le `duration_ms` n'est plus perdu :** le ledger utilise `meta["duration_ms"]` comme fallback avant d'enregistrer `0`.
* **Export et Nettoyage :** `gitpr metrics export` (CSV/JSON), `prune`, `purge` avec confirmation interactive, `bundle`/`merge` pour transporter et fusionner des fenêtres.
* **Le Commit Interactif Mesure Aussi :** le chemin `commit` continue d'enregistrer la ligne de commande avec `meta` et `modules`.

### **19. Synchronisation de la Langue des Git Hooks**

* **Versionnage Indépendant :** `__scripts_version__` (**v0.0.4**, inchangée).
* **Templates réécrits dans la fenêtre v0.0.17 :** `post-checkout`, `post-merge` et `pre-push` appellent `gitpr --quiet metrics hook-event <name>`, protégés par `command -v gitpr` et `|| true`.
* **Mapping de Suffixes (`HOOK_SCRIPT_SUFFIXES`) :** les codes UI (`es_es`, `fr_fr`) traduits vers les suffixes publiés (`.es`, `.fr`).
* **Choix vs. État (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`) :** permet de **détecter un changement de langue**.
* **`effective_hook_lang()` :** résout la langue effective ; `--lang` n'est plus ignoré sur ce chemin.
* **Saut de la Source de Merge :** `prepare-commit-msg` saute les sources `message|merge|squash|commit`.
* **Le `prepare-commit-msg` Reste Non Interactif :** le hook s'exécute avec `--hook`, donc n'ouvre **jamais** la `CommitApp` — la TUI n'existe que dans l'usage humain de `gitpr -c`, et désormais seulement quand il y a un TTY.
* **Les hooks s'exécutent avec `--quiet` et `--hook`, donc ils ne sont jamais bloqués par la barrière de mise à jour.**

### **20. Pont de Linters Externes et Assistant Interactif (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistant `--linter-setup` :** wizard avec des presets numérotés (PHP_CodeSniffer, ESLint, Stylelint) et injection du bloc `external_linters`.
* **Presets Distants :** `templates/gitpr.linter-presets.json` avec une chaîne de fallback local → téléchargement → obsolète → embarqué.
* **TUI d'Erreurs du Linter :** `src/ui/linter_app.py` (Textual) affiche les erreurs critiques et les warnings ; en hook/quiet imprime et fait `sys.exit(1)`. 🆕 `ENABLE_COMMAND_PALETTE = False` — c'était la **dernière** app Textual avec la palette active.
* **Rapport Markdown :** consolidé dans `.gitpr/reports/linter/` uniquement en cas de violations.
* **La Couche SAST est une Sœur, pas un Remplacement :** les ponts Semgrep/Gitleaks/Bandit alimentent le **même** pipeline de rapport — et aussi `gitpr check`.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstraction Unique (`ScmProvider` ABC) :** `base.py` définit le contrat (dataclasses `RepoRef`, `PullRequestDraft` etc. et `ScmProviderError(provider, http_status, message)` — `http_status` 0 = échec réseau).
* **🆕 `IssueInfo` avec `is_open` :** le flag booléen supprime les comparaisons fragiles de chaîne d'état dans la couche de décision.
* **🆕 Les Opérations d'Issue Sont de Toutes les Forges :** GitLab normalise `opened` → `open` et émet `Closes #N` ; Bitbucket normalise `new`/`open`/`on hold` → `open` et émet `close #N` ; Azure DevOps lève `ScmNotSupportedError` (les Work Items ne sont pas des issues).
* **`list_pull_requests(repo, state, since, until)` — paginé et canonique :** les quatre providers traduisent les états canoniques (`open`/`merged`/`closed`/`all`) dans le vocabulaire natif, paginent sur le curseur de chaque API et filtrent par une fenêtre de dates inclusive.
* **Pourquoi le filtre est par date d'état :** trois des quatre forges retournent quelque chose de plus large pour un PR mergé, donc chacune filtre sa propre réponse par rapport à la date qu'implique l'état.
* **`supports_merged_dates` :** attribut de classe, `False` sur Bitbucket Cloud, qui publie `merged_at` **vide**.
* **`web_links.py` :** fonctions pures (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) qui normalisent n'importe quel format de remote.
* **`get_pull_request(repo, pr_id)` — méthode concrète de l'ABC :** le défaut lève `ScmNotSupportedError` et chaque forge l'implémente.
* **🆕 Résolution par Dépôt :** le registry ne lit plus `GITPR_SCM_PROVIDER` ; `resolve_scm_provider(config)` reçoit le provider déjà résolu par `src/scm_settings.py` (section 46).
* **GitHub :** `GET /repos/{o}/{r}/issues/{n}` (en marquant `pull_request` et en retombant sur le numéro demandé quand il est absent), `GET /repos/{o}/{r}` pour `default_branch` (chaîne vide quand absent) et le mot-clé `close #N`.
* **En-têtes GitLab Synthétisés :** `changes[].diff` est un hunk lâche ; `old_path`/`new_path` construisent les en-têtes `diff --git a/… / --- / +++`, en respectant `/dev/null`. Et `overflow: true` **lève** au lieu de réviser un diff tronqué à moitié.
* **`request_pull_request_reviewers` renvoie `list[str]` :** Renvoie les logins **effectivement attachés**, lus dans le corps du `201`. La rupture de contrat est documentée dans [ADR-0009](docs/adr/0009-reviewer-attach-returns-read-back.md) et dans `docs/suggested-reviewers.*`/`docs/scm-multiforge.*`.
* **Helpers GitHub en Lecture Seule :** `get_commit_author_login` et `get_user_login`.
* **Fail-Fast par Forge :** Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` ; Bitbucket exige `GITPR_SCM_USERNAME` ; `create_issue` sur Azure lève `ScmNotSupportedError`.
* **Publication de Release :** `provider.create_release()` utilisé par `gitpr release --publish`.
* **Publication de PR Partagée :** `src/application/use_cases/publish_pull_request.py`, utilisé par `--no-edit` et par l'outil MCP. Valide titre/corps, **exige `provider.token` seulement quand `check_remote_state=True`**, vérifie éventuellement un PR existant et une branche non poussée, masque les messages d'erreur et renvoie `PublishPrOutcome`.
* **Artefacts :** Glossaire + ADR-001/ADR-005/**ADR-016** ; famille `docs/scm-multiforge.*.md` en 5 langues ; **10 fichiers de test, 370 scénarios**.

### **22. Sous-commande `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Flux :** `git log` entre `--since` et `HEAD` → classification par Conventional Commits → bump sémantique suggéré → assemblage du changelog → résumé exécutif IA optionnel → *prepend* dans `CHANGELOG.md`.
* **La Plage Ne Vient Plus de `git describe` :** l'origine de la plage est la **section précédente du changelog lui-même**, par deux ancres : le hash le plus récent de cette section et, en fallback, le tag de version de la section.
* **`_drop_released()` — la seconde ligne de défense :** tout commit dont le hash court apparaît dans **une autre** section de version est écarté, avec un décompte.
* **Chaque puce est devenue un lien :** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **L'échec de PR merged par merge commit :** une deuxième passe (`git log --merges`) construit `hash → PR` par ancestralité.
* **Contributeurs résolus par la même échelle que les reviewers :** `get_commit_author_login` d'abord, puis `email_to_handle`, mis en cache dans `~/.gitpr/cache/contributors.json`.
* **La Frontière de Section était un Bug Latent :** `upsert_changelog(force=True)` remplace de la ligne `## [x.y.z]` jusqu'au prochain en-tête de **niveau 2**.
* **🆕 Le Front Matter Déclare la Langue de la Skill :** `src/skill_frontmatter.py` (`split_front_matter()`) lit un bloc YAML initial avec `language: <code>` (allowlist `en_us`, `pt_br`, `pt_pt`, `es_es`, `fr_fr`), **supprime le bloc** avant que le corps ne devienne instruction système et change la langue de l'i18n pendant la génération — headings, libellés **et** la langue demandée à l'IA, même avec le résumé désactivé. Parse défensif : ne lève jamais, accepte BOM/CRLF et ignore une déclaration invalide.
* **🆕 `_declared_language()` Restaure l'État :** le context manager renvoie `CURRENT_LANG`/`TRANSLATIONS` directement, sans une seconde recherche de traduction en sortie ; `_language_for_prompt()` importe `CURRENT_LANG` de façon lazy et normalise `-` en `_`.
* **Classifier (`src/commit_classifier.py`)** et **Builder avec Sections Traduisibles (`src/changelog_builder.py`)**.
* **Bump Sémantique (`src/version_bump.py`)** et **Publication** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 options sur la sous-commande.**
* **État de la release :** la section `## [1.6.0] - 2026-11-07` est commitée ; **il n'y a ni tag `v1.6.0` ni publication**.
* **La sous-commande passe par la barrière de mise à jour** lorsqu'elle est lancée dans un terminal interactif.
* **Artefacts :** `docs/release-notes.*.md` en 5 langues, ADR et `tests/test_web_links.py`.

### **23. Sous-commande `gitpr config` — TUI de Configuration**

* **Écran Master-Detail (`src/ui/config_app.py`) :** catégories à gauche, champs à droite, édition inline. En-tête avec recherche (`/`) et compteur de modifications en attente ; pied de page `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schéma Déclaratif (`src/config_schema.py`) — la source unique de vérité :** **18 catégories** et **100 `ConfigField`**, dont **7 avancés** et **55 `seeded`**.
* **🆕 `DEFAULT_CONFIG` Dérivé :** le dictionnaire n'est plus maintenu à la main — il est monté à partir de `seeded=True`, si bien qu'une nouvelle clé du schéma naît déjà semée ou délibérément non-semée.
* **🆕 Catégorie `check` :** les neuf `GITPR_CHECK_*` (`BASE`, `FORMAT`, `OUTPUT`, `FAIL_ON`, `NO_IA`, `IA_ONLY_HIGH_RISK`, `BASELINE_PATH`, `POLICY`, `TIMEOUT_SECONDS`) apparaissent désormais ici, avec les mêmes defaults que le pipeline.
* **🆕 Catégorie `smart_excludes` :** remplace l'ancien libellé `Diff Filters` ; `SMART_EXCLUDES_VERSION` a changé de groupe et la catégorie a son propre panneau (voir ci-dessous).
* **🆕 Panneau Smart Excludes :** liste des sources (projet, global core, global docs), `TextArea` d'édition, bouton de création du fichier du projet, état/note et suivi de dirty. Le `F2` enregistre le fichier du projet par le même plan d'écriture.
* **🆕 Catégorie `project` et l'Attribut `store` :** les champs déclarent où ils vivent (`env` vs `project`). Les trois champs du projet sont le path du fichier (read-only), la branche cible (éditable) et la base effective avec l'origine (read-only). Le `KIND_BRANCH` rend le choix de branche.
* **🆕 Catégorie `scm` par Forge :** a gagné la ligne éditable `PROJECT_SCM_PROVIDER`, la ligne read-only `PROJECT_EFFECTIVE_PROVIDER` (forge en usage et origine) et un groupe `gitlab` ; les jetons et base URLs de **toutes** les forges sont listés toujours, et le test de jeton utilise l'hôte de son propre slot.
* **Catégorie `issue` :** `GITPR_ISSUE_AUTOCLOSE` et `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` apparaissent ici.
* **Écriture par Store :** la `ConfigApp` dispatche par `store` : les valeurs de projet ne passent **jamais** par `save_config_values()`, sont écrites en premier, supportent la suppression en vidant, refusent la branche actuelle et n'écrasent jamais du YAML illisible. La ligne de la base effective est mise à jour in-place après l'enregistrement.
* **Catégories `policy` et `baseline` :** les champs du pack et les quatre de baseline.
* **Section Skills Alignée :** `SKILL_LABELS` a **11 entrées** (`tests`, `explain`, `mentor`).
* **Filtrage Contextuel (`show_if`) :** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` selon `DEFAULT_AI_PROVIDER` ; `GITPR_SCM_USERNAME` sous `[Bitbucket]` ; `GITPR_SCM_ORGANIZATION`/`PROJECT` sous `[Azure DevOps]`.
* **Recherche Globale (`/`) :** correspond à la clé ou au label dans toutes les catégories et **ignore le filtre de visibilité**.
* **Validation à Deux Couches :** **hors ligne** bloque `F2` avec une erreur inline ; **en ligne** (uniquement si des identifiants ont changé dans la session) tourne sur un worker avec un timeout de 10s et ne bloque que sur `401`/`403`.
* **Restauration (`Ctrl+R`) :** supprime la ligne du `.env` au lieu de réécrire le défaut ; `Esc` avec des modifications en attente demande confirmation.
* **Sections Project-Scoped :** Édite `.gitpr/skill/*.md`, `.gitpr/conf/gitpr.config.yml` et les smart-excludes du projet, en écrivant atomiquement et en préservant CRLF/LF.
* **Téléchargements Forcés :** boutons qui forcent le re-téléchargement des smart-excludes, des traductions, des presets et des thinking words via `force=`.
* **Module de Liens Léger (`src/doc_links.py`) :** `doc_url()` est sorti de `core.py` pour que l'UI obtienne le lien sans importer de SDK IA. 🆕 `DOC_LANG_PARAMS` mappe `es_es`→`es` et `fr_fr`→`fr`, et `CURRENT_LANG` est lu **à l'appel**, si bien que `set_lang()` se propage aux liens.
* **Tests :** 5 fichiers — `test_config_app.py` (94), `test_config_schema.py` (53), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9) et `test_config_issue_autoclose.py` (10).

### **24. Journal Général d'Utilisation (`src/usage_log.py`)**

* **Une Ligne par Commande :** écrit `~/.gitpr/logs/<uuid5>.log`, **un fichier par jour**, avec la commande, les arguments, le dépôt, l'utilisateur et l'horodatage.
* **Nom Dérivé de la Date :** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")`.
* **Écriture Synchrone (décision explicite) :** contrairement à `log_local_metric`, qui utilisait un thread daemon et perdait l'écriture si le processus se terminait avant.
* **N'Imprime Jamais :** le serveur MCP réserve stdout pour JSON-RPC ; le module ne lève jamais d'exception non plus.
* **Un Seul Spawn Git :** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms au lieu de ~150 ms sous Windows, à *chaque* exécution.
* **Contrôle :** `GITPR_SHOW_LOGS` (défaut `"true"`) ; désactivé dans `tests/conftest.py`.
* **Artefacts :** `docs/usage-log.*.md` en 5 langues ; `tests/test_usage_log.py` (27 scénarios).

### **25. Sous-commande `gitpr fix` — Constats de Review en Patches Révisables (`src/fix/`)**

* **Le Vrai Pipeline :** dernier review du cache (`resolve_last_review`) → **un** appel IA → extraction et validation du diff unifié → `git apply --check` → classification déterministe → dry run ou écriture → historique.
* **Paquet de 8 fichiers :** `patch_provenance.py`, `patch_extractor.py` (partagé avec le chat), `patch_safety_classifier.py` (pur, sans I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Classification Déterministe :** rejette un patch qui couvre plus d'un fichier ou d'un hunk, touche un chemin sensible, dépasse le budget de lignes, **supprime une ligne qui ressemble à un appel**, est marqué low-confidence ou échoue à `git apply --check`. `--force` ne contourne **jamais** l'applicabilité, seulement la classification.
* **Écriture Opt-In :** le dry run est le défaut ; toute mutation exige `--apply` (ou une phrase tapée avec `--force`).
* **Tests :** 10 fichiers dans `tests/fix/` (**204 scénarios**) avec des fixtures de dépôt git réel.

### **26. Sous-commande `gitpr review-pr` — Review de PR Distant (`src/review/`)**

* **Ce Que C'est :** de l'orchestration, **pas** un second moteur de review — le moteur, le linter et le renderer existants alimentés par un diff venu d'ailleurs.
* **Paquet de 5 fichiers :** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Lecture Seule par Défaut :** `--post-comment` est le **seul** chemin qui écrit vers la forge. L'outil MCP `review_remote_pr` ne reçoit même pas l'argument.
* **La baseline est résolue avant le premier appel réseau**, et les avertissements de la barrière sont fusionnés dans le rapport/commentaire.
* **`render.py` et le risque :** `compose_review_content` / `render_review_result` **préposent** la section `⚡ Risk Assessment` au-dessus des alertes du linter quand `GITPR_RISK_INCLUDE_IN_REVIEW` est activé.
* **Documentation complète dans les 5 langues :** `docs/review-pr.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md` et `.fr_fr.md`.
* **🆕 Hunk Binaire dans le Diff Distant :** `filter_excluded_sections` applique toujours `collapse_binary_hunks()`, y compris dans les early exits sans patterns/sections.
* **Tests :** 4 fichiers dans `tests/review/` (**97 scénarios**).

### **27. Résolution de l'Identité du Reviewer (`src/reviewer_resolution.py`)**

* **Le Bug (deux défaillances silencieuses en chaîne) :** (1) **préremplissage vide** — `handles` uniquement avec `email_to_handle()` ; (2) **attache non vérifiée** — valeurs transmises *verbatim*, GitHub répondait **201 sans attacher personne** et `_request()` revenait sans lever.
* **Module :** pur, sans I/O propre, **qui ne lève jamais**. `resolve_candidates()` (avant la TUI) et `resolve_typed_reviewers()` (à l'attache) ; `match_candidate()` correspond exactement et de façon normalisée par login, nom ou email.
* **Échelle de Résolution :** handle connu (sans requête) → correspondance exacte avec une personne suggérée → recherche par email → validation du login sur la forge.
* **Ce qui ne se résout pas n'est jamais envoyé :** sort dans `ResolutionOutcome.dropped` comme `(value, reason_i18n)` et est écarté avec un avertissement visible.
* **La Même Échelle Sert à la Release :** `release_engine` réutilise `get_commit_author_login` + `email_to_handle`.
* **Compagnon Hérité :** si un fournisseur tiers renvoie `None` (contrat ancien), le journal dit `(no read-back)` et le flux continue — une relecture absente n'est **jamais** traitée comme un échec.
* **🆕 Résumé de Blame :** `ReviewerSuggestionResult` a gagné `files_with_blame`/`files_without_blame` ; le moteur sépare les fichiers et le CLI imprime **une** ligne consolidée (`ℹ️ Analyse de blame : X fichier(s) avec blame, Y sans blame`) — seulement quand il y a des fichiers sans blame. Des dizaines d'avertissements par fichier sont devenus un seul résumé.
* **Tests :** `tests/test_reviewer_resolution.py` (18) + `test_github_provider.py` (86), `test_pr_publish_app.py` (59), `test_suggest_reviewers.py` (**19** 🆕), `test_reviewer_suggestion.py` (18) et `test_main_suggest_reviewers.py` (8).

### **28. Sous-commande `gitpr demo` — Visite Guidée sur des Exemples Enregistrés (`src/demo/`)**

* **Ce Que C'est :** une visite interactive qui montre les trois sorties principales de GitPR — message de commit, review et description de PR — **sans clé API, sans dépôt git et sans réseau**.
* **Pipeline Réel, Réponse Factice :** `FakeAIProvider` reproduit `src.ai_providers.call_ai_model` **argument par argument** ; `demo_pipeline()` patche `src.core` et `src.metrics` pour que le pipeline de production tourne **intact**.
* **L'Isolation est une Exigence, pas un Détail :** la visite **ne peut pas** toucher le `~/.gitpr` de l'utilisateur ni le réseau — garanti par `tests/demo/conftest.py`.
* **Paquet :** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (deux exemples localisés dans les 5 langues). Les scénarios sont des **modules Python, pas du JSON**, pour voyager dans le wheel.
* **TUI :** `src/ui/demo/` avec un modal d'aide ; le diff est clôturé en ```` ```diff ````.
* **CLI :** `gitpr demo` avec `--scenario`, `--lang` et `--no-tui`.
* **Tests :** 6 fichiers dans `tests/demo/` (**155 scénarios**).
* **Artefacts :** `docs/demo.*.md` en 5 langues, `README.*` (5) et les métriques d'exemple de la visite.

### **29. Badge GitPR et la Commande `gitpr badge` (`src/branding/`)**

* **Ce Que C'est :** un badge public attestant qu'une pull request a passé un contrôle de qualité local, plus une commande qui imprime l'extrait d'adoption pour le README du projet.
* **URL Statique à Dessein :** c'est un Markdown shields.io que GitPR **ne récupère jamais** — publier une pull request ne peut pas commencer à dépendre d'un tiers en ligne.
* **Mesure Honnête (`badge_data.py`) :** `collect_linter_counts()` retourne `None` quand il n'y a pas de règles de linter configurées, parce qu'une liste vide signifie « rien n'a été vérifié », pas « rien n'a été trouvé ».
* **Le Badge ne Bloque Jamais une Publication :** chaque chemin d'échec est capturé et dégrade vers « pas de badge ».
* **Point d'Injection Unique :** `attach_pr_badge()` dans `src/main.py`. Ajout idempotent par marqueur.
* **Commande `gitpr badge` :** `--readme` et `--style`. **Elle imprime seulement** — le README n'est jamais modifié.
* **Configuration :** `GITPR_BADGE` (défaut `true`) — opt-out **en lecture seule**.
* **Tests :** 7 fichiers dans `tests/badge/` (**83 scénarios**).
* **Artefacts :** `docs/badge.*.md` en 5 langues et le survey dédié.

### **30. Sous-commande `gitpr split` — Commits Atomiques par Hunk (`src/split/`)**

* **Ce Que C'est :** un arbre de travail avec plusieurs préoccupations ne se transforme plus en commit fourre-tout. `split` lit le diff non commité, demande à l'IA de partitionner les hunks par **intention logique** et propose un commit atomique par préoccupation. L'arbre n'est jamais réécrit.
* **Paquet de 6 fichiers :** `split_plan.py`, `hunk_parser.py` (pur), `hunk_grouper.py`, `generate_split_plan.py` (lecture seule), `apply_split_plan.py` (**le seul qui mute**) et `__init__.py`.
* **La Garantie d'Arbre Intact est Structurelle :** `selective_stager.py` pré-valide **chaque** groupe contre un index temporaire à HEAD et utilise un `GIT_INDEX_FILE` jetable.
* **Destructif pour l'Index, pas pour l'Arbre :** `--apply` remet l'index à HEAD avant le staging.
* **Configuration :** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) et `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Les valeurs zéro ou négatives sont **ignorées au profit du défaut**.
* **Relation avec la TUI de Commit :** `split` et `-c` attaquent le même problème à des hauteurs différentes — `split` propose **plusieurs** commits à partir de l'arbre ; `CommitApp` organise **un** commit à partir de l'index.
* **Tests :** 6 fichiers dans `tests/split/` (**101 scénarios**).
* **Artefacts :** `docs/split-command.md` en 5 langues, ADR-006, `glossary-gitpr-split.md`, spec/plan et survey.

### **31. Balayage de Secrets Embarqué (`src/security_ruleset.py`)**

* **Ce Que C'est :** sept règles qui tournent à **chaque** invocation du linter, fusionnées à la fin de `load_linter_rules()` après les règles du projet et les plugins.
* **Les Sept Règles :** cinq `error` bloquantes — ID de clé d'accès AWS, jeton GitHub, jeton Slack, clé API Google et bloc de clé privée — et deux `warning` : URL de connexion à la base avec credentials et affectation générique de credentials, cette dernière derrière un filtre de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Couverture :** affectation **sans guillemets**, préfixes **`ASIA…`**, **`github_pat_…`** et **`xoxc-`/`xoxd-`**, **frontière à gauche** dans le nom de la clé (`bypassword` ne correspond pas) et suppression des faux positifs de runtime (`os.getenv(...)`, `process.env.X`).
* **Pourquoi dans le Paquet et pas dans le Template :** une barrière de secrets doit se comporter **pareil** sur chaque machine et dans chaque CI.
* **Configuration :** `GITPR_LINTER_SECURITY` (défaut `true` ; opt-out fail-open) et `GITPR_LINTER_SECURITY_DISABLED_RULES` (séparées par `;`).
* **Tests :** `tests/test_security_ruleset.py` (**77 scénarios**) — matrice de regex, placeholders, niveau, wildcard, merge et traductions ; l'invariant « l'alerte ne répète jamais la valeur » tient toujours.
* **Artefacts :** ADR-007, [ADR-0008](docs/adr/0008-secret-ruleset-coverage-extension.md), `glossary-gitpr-secret-scanning.md`, spec/plan/survey et rapports de tâche.

### **32. Ponts SAST — Semgrep, Gitleaks et Bandit (`src/infrastructure/linter/external/`)**

* **Ce Que C'est :** une couche **opt-in** qui branche des scanners de sécurité tiers dans le pipeline de linter existant.
* **Périmètre Borné :** ils tournent **uniquement** quand activés et **uniquement** sur les fichiers touchés par le diff.
* **Contrat Commun :** `ExternalLinterBridge` (ABC) avec une exécution `subprocess` blindée (`shell=False`, timeout strict, `stdin=DEVNULL`, UTF-8 avec `errors='replace'`).
* **La Déduplication est le Point :** `deduplicate_secret_findings()` fusionne les constats de Gitleaks et du ruleset regex sur le même fichier et la même ligne en une **unique** entrée confirmée `[Gitleaks + Regex]`.
* **Secrets Masqués :** `mask_secret_value()` garantit que la valeur n'atteint jamais le constat, le log ou la télémétrie.
* **Résolution à Trois Couches :** `load_sast_config()` lit les défauts → `.gitpr.linter.yml` (bloc `sast`, avec fallback vers `linter.external`) → variables `GITPR_SAST_*`. **Toutes valent `false` par défaut**.
* **Dégradation Gracieuse :** un outil activé mais absent du `PATH` émet un avertissement et l'exécution continue.
* **Tests :** 4 fichiers dans `tests/infrastructure/linter/external/` (**14 scénarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + `tests/domain/linter/test_structured_findings.py` (44).

### **33. Sous-commande `gitpr tests generate` — Génération de Suite par IA (`src/domain/tests_generation/`, `src/application/`)**

* **Ce Que C'est :** génère des fichiers de test complets et exécutables depuis le diff courant, un fichier précis ou un constat de review, **en respectant la convention du dépôt** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Couche de Domaine :** `TestFramework` (enum) et les dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest` ; `detect_test_framework()` détecte depuis les fichiers de configuration, les manifests et le contenu des répertoires de test ; `build_test_scaffold()` calcule le chemin de destination conventionnel par framework.
* **Couche d'Application (`generate_test_file.py`) :** orchestre la détection, la résolution du scaffold, le prompt, l'IA, le parsing JSON et l'écriture optionnelle. `validate_test_syntax()` exécute la toolchain locale (`php -l`, `node --check`, `python -m py_compile`) — un échec de validation est un **avertissement**, pas une erreur.
* **Présentation :** groupe `tests` avec `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`) ; le dry run est le défaut et l'écrasement d'un test existant demande confirmation avec **No** présélectionné.
* **Documenté en 5 langues :** `docs/tests-generate.*.md` (5 fichiers), avec un lien dans `gitpr tests generate` (epilog) et dans le README.
* **Skill :** `.gitpr.tests.md` dans `SKILL_LABELS`, `mcp_server.SKILL_FILES` et `SKILL_FILES_BY_TYPE`.

### **34. Sous-commande `gitpr explain` et le Flag `--explain` — Guide du Reviewer (`src/domain/pr/`)**

* **Ce Que C'est :** un guide centré sur celui qui va **réviser** — ce qui change, pourquoi ça change, où se concentrer et quel est le risque de régression.
* **Couche de Domaine (`explain_section_builder.py`) :** `PrExplanation` / `ReviewerFocusPoint`, le renderer `build_explain_markdown()` et `parse_explain_payload()`, qui tolère une sortie non-JSON et **détecte les placeholders** `[FILL]`/`[TODO]`.
* **`parse_explain_payload()` Accepte `dict | str | None` :** le fournisseur livre le JSON **déjà parsé** ; la fonction accepte les trois formes et normalise les champs `None`.
* **Branche de Clé Manquante :** `generate_pr_explanation` imprime l'erreur standard de clé manquante et sort avec **1**.
* **Couche d'Application (`generate_pr_explanation.py`) :** résolution du fournisseur, validation de la clé, chargement du contexte de skill (`explain`), prompt, invocation et parsing.
* **Deux Points d'Entrée :** `gitpr explain` (`--provider`, détection de diff manquant avant toute IA) et le flag `--explain` de la CLI racine. L'extension expose le même chemin dans `GitPR: Generate PR Draft (with Reviewer Guide)`.
* **`-h --explain` Corrigé :** l'option utilisait le dest `explain_flag`, mais le `HELP_MAP` cherchait `explain` — l'aide contextuelle rend à nouveau.
* **Documenté en 5 langues :** `docs/explain-pr.*.md` (5 fichiers), avec un lien dans `HELP_MAP["explain"]`, dans l'epilog de la commande et dans le README.
* **Configuration :** `GITPR_EXPLAIN_BY_DEFAULT` (défaut `false`).

### **35. Suite Déterministe et CI (`.github/workflows/tests.yml`, `release.yml`)**

* **Ce Que C'est :** le workflow qui exécute la suite sur **Python 3.10** (le plancher déclaré dans `pyproject.toml`) et **3.13**, avec `fail-fast: false`.
* **`tests/conftest.py` Hermétique :** Fige `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` et `LANG_VERSION` sur la version du code, en plus de `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true` et `GITPR_SKIP_PROJECT_CONFIG_SEED=true` (la suite n'écrit jamais dans un checkout de développeur).
* **🆕 Fixtures Autouse Contre les Fuites de Profil :** `english_interface` et `no_hook_script_sync` empêchent la langue, les flags de linter et l'auto-sync de hooks du profil du développeur de s'échapper entre les tests CLI.
* **L'Ordre Compte :** `src.updater` est importé **avant** que les variables de langue soient définies.
* **Stabilité des Tests de Worker :** `test_config_app.py` et `test_metrics.py` attendent `workers.wait_for_complete()` avant d'asserter.
* **Dépendance du Workflow :** le runner propre n'a pas de `~/.gitpr`, et `test_demo_isolation.py` affirme que le profil existe — le job crée le répertoire et le `.env` vide explicitement.
* **🆕 Dans cette fenêtre la suite a gagné 146 scénarios et 6 modules :** `scm/test_scm_settings.py` (24), `test_skill_frontmatter.py` (11), `test_doc_links.py` (11), `test_seed_command.py` (8), `test_tui_issue_token.py` (8) et `unit/test_check_ci_docs_parity.py` (2), en plus de la croissance de `test_metrics.py` (174), `tests/scm/` (370), `test_config_app.py` (94), `test_release_engine.py` (61), `test_config_schema.py` (53), `test_project_config.py` (49), `test_full_diff_encoding.py` (8), `test_commit_app.py` (17), `test_suggest_reviewers.py` (19), `test_smart_excludes.py` (17) et `tests/issue_autoclose/` (126). **Total : 3422 scénarios dans 159 fichiers.**
* **Workflow de l'extension :** `.github/workflows/extension.yml` exécute `npm install`, compile, teste et empaquette le VSIX ; sur les tags `ext-v*` publie sur VS Code Marketplace et Open VSX, en sautant chaque étape quand `VSCE_PAT`/`OVSX_PAT` est vide. 🆕 A gagné `workflow_dispatch` avec `dry_run`, une garde tag ↔ `package.json` et un échec explicite quand l'identifiant manque.
* **Workflow de Release :** `release.yml` exécute les tests, vérifie la cohérence tag ↔ `src.updater.__version__`, build sdist/wheel, valide avec `twine check --strict`, publie par Trusted Publishing (OIDC) et crée la GitHub Release. Le SSOT de version a aussi son propre test (`tests/unit/test_version_consistency.py`).

### **36. Ledger de Télémétrie SQLite (`src/ledger.py`, `src/infrastructure/git/identity.py`)**

* **Le Problème N'était pas le Format, c'était la Jointure :** les événements étaient écrits en fichiers JSON par commande, depuis un **thread daemon**, puis reconnectés au cache IA par une correspondance heuristique de `(repo, branch, action, minute)`.
* **L'Écriture est Synchrone, et C'est la Décision :** `log_command_metric()` résout le contexte git **une fois par processus** et écrit la ligne sur-le-champ.
* **Schéma Versionné :** `SCHEMA_VERSION` + `PRAGMA user_version`, clés UUID, WAL + `synchronous=FULL`, et `LedgerVersionError` pour refuser un bundle plus récent **avant** `ATTACH`.
* **L'Invariant « Jamais Né Vide » :** `ensure_ledger()` ne crée la base **qu'avec** l'absorption du JSON legacy.
* **Une Fenêtre, Une Signification :** `--since`/`--until`/`--days` restreignent **toutes** les surfaces de lecture ; la section cycle suppose les 30 derniers jours et **nomme la fenêtre**.
* **Chaque Métrique est Calculée Une Fois et Rendue Une Fois :** les paires `(style, text)` font que la CLI et le tableau de bord Textual racontent la **même** histoire.
* **Coût avec Table Embarquée et Override :** prix catalogue des quatre modèles, surchargés par `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Les deux tarifs sont obligatoires**.
* **`modules` Normalisés :** les chemins deviennent deux segments (`src/fix`), `(root)` et `(no module)`.
* **Transport par Bundle :** `bundle` écrit la fenêtre en `.db` autonome ; `merge` l'attache et insère par UUID.
* **`cache_backfill` est Exclu :** les lignes reconstruites depuis le cache sont exclues des agrégats d'exécution.
* **`issue_autoclose` Entre dans le Ledger :** une ligne par décision, avec `asked`, `accepted` et `skipped_reason`.
* **🆕 `duration_ms` avec Fallback de `meta` :** les événements qui ne transportaient la durée que dans `meta["duration_ms"]` ne sont plus enregistrés comme `0` — sans cela, l'histogramme de latence sous-compterait.
* **Identité Git sans Forge (`identity.py`) :** dépôt, branche et auteur en **un seul** appel subprocess.
* **La Section Cycle est la Seule que le Ledger ne Peut Pas Répondre :** elle liste les PR mergés via `ScmProvider.list_pull_requests` ; chaque échec dégrade vers une ligne — `Non lu : <raison>`.
* **Présentation Complète :** groupe `gitpr metrics` avec 8 sous-commandes, un dashboard qui lit le ledger sur un thread worker et l'assistant de migration à trois options.
* **Tests :** `tests/test_metrics.py` (**174 scénarios**).

### **37. Sous-commande `gitpr mentor` et le Flag `--mentor` — Mentor Mode (`src/domain/mentor/`)**

* **Ce Que C'est :** un review dit *ce* qui ne va pas et presque jamais *pourquoi* c'est important. Le Mentor Mode enrichit les constats avec **ce qui se passe**, **pourquoi c'est important**, une **analogie** du quotidien et les **concepts à étudier**.
* **Couche de Domaine Pure (`mentor_explanation_builder.py`) :** sans I/O, convertit le payload de l'IA en un `MentorExplanation` validé et impose l'**honnêteté épistémique** : sans preuve suffisante, l'explication **le dit** ; les URL hallucinées sont **supprimées** ; les IDs inconnus sont **rejetés**.
* **Couche d'Application (`generate_mentor_explanation.py`) :** collecte les candidats, **lots d'au plus 10 constats**, ordonnés par sévérité, en réutilisant le cache MD5 par hash de prompt.
* **Deux Points d'Entrée :** `gitpr mentor [--finding <id>] [--provider <name>]` et le flag `--mentor`, qui ajoute la section au review normal.
* **Opt-In Strict :** `GITPR_REVIEW_MENTOR_MODE` (défaut `false`) et `GITPR_MENTOR_INCLUDE_ANALOGY` (défaut `true`), modifiables dans la TUI de configuration.
* **Skill et Registre :** `.gitpr.mentor.md` enregistré dans `SKILL_FILES_BY_TYPE`, dans `mcp_server.SKILL_FILES` et dans `SKILL_LABELS`, avec des templates en **5 langues**.
* **Tests :** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) et `tests/test_mentor_cli.py` (4) — **tous verts**.
* **Artefacts :** `docs/mentor-mode.*.md` en **5 langues**, ADR-009, `glossary-gitpr-mentor.md`, spec et rapports de tâche.

### **38. Sous-commande `gitpr risk` — Score de Risque Déterministe (`src/domain/risk/`)**

* **Ce Que C'est :** un score **0–100** avec niveau `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculé **sans IA** à partir de signaux locaux.
* **Couche de Domaine Pure (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`) :** enums et dataclasses ; poids, seuils, motifs de chemins critiques, correspondance de globs et détection de tests/non-exécutables chargés depuis YAML ; calcul par fichier avec **clamp 0–100** et agrégation 50/30/20.
* **La Formule est Déclarée, pas Apprise :** **50 %** le fichier le plus risqué, **30 %** la moyenne pondérée par lignes et **20 %** les preuves critiques.
* **Adaptateurs d'Infrastructure :** `risk_history_reader.py` lit `git log` et extrait les commits, corrections de bugs et reverts sur une fenêtre de **90 jours** ; `test_matcher.py` fait correspondre test ↔ fichier de production par convention.
* **Cas d'Usage (`calculate_risk.py`) :** corrèle les sections du diff, les constats, la correspondance des tests et l'historique en `FileRisk`/`PullRequestRisk`, en **dégradant avec un avertissement** quand un signal est indisponible.
* **Path Tolérant :** l'agrégation du `check` accepte `path` **ou** `file_path` — le rapport de risque ne se dégrade pas silencieusement.
* **Trois Consommateurs :** la commande `gitpr risk`, la section `⚡ Risk Assessment` ajoutée aux reviews et au review de PR distant, et le **prompt IA** comme contexte **en lecture seule**. `gitpr check` utilise le même `execute_calculate_risk`.
* **Opt-Out :** `GITPR_RISK_INCLUDE_IN_REVIEW` (défaut `true`).
* **Configuration par Projet :** `templates/gitpr.risk.yml` (ou `.gitpr/skill/gitpr.risk.yml`) surcharge poids, seuils et chemins critiques.
* **Tests :** `tests/domain/risk/` (2 fichiers, 10), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) et `tests/test_risk_cli.py` (4).
* **Artefacts :** `docs/risk-scoring.*.md` en **5 langues**, ADR-010, `glossary-gitpr-risk-scoring.md` et rapports de tâche.

### **39. Sous-commande `gitpr policy` — Policy Packs Versionnés (`src/domain/policy/`, `src/infrastructure/policy/`)**

* **Ce Que C'est :** un **pack YAML versionné** qui porte, dans un seul artefact, les décisions de qualité d'un dépôt : skills, règles de linter, sévérités, chemins critiques, poids de risque et conventions de PR/commit.
* **Couche de Domaine (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`) :** parsing du manifest avec un **schéma fermé**, vérification de compatibilité et de credentials, **checksum SHA-256** et résolveur de graphe avec précédence et composition.
* **Couche d'Infrastructure :** `local_policy_repository.py` résout le pack depuis **trois origines** (`.gitpr/policies`, `~/.gitpr/policies` et le paquet embarqué) et `policy_pack_loader.py` le charge.
* **Cas d'Usage :** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` et `validate_policy_pack`.
* **Quatre Packs Embarqués :** `laravel-quality`, `node-quality`, `php-security` et `vue-quality`, chacun avec `policy.yml` et `linter.yml`, distribués via **`package-data`**.
* **Consommation Intégrée à Quatre Points :** `calculate_risk`, `config.load_linter_rules`, `core.get_skill_context` et la **clé de cache de PR** (`cache_scope`). `gitpr check` résout le pack **avant tout le reste** et sort avec le code 2 s'il est invalide.
* **CLI :** `gitpr policy {list, validate, show, use, off, install, init}`, avec confirmation (`--yes`/`--force`), détection de stack dans `init` et choix enregistré dans `.gitpr/policy.lock.yml`.
* **Configuration :** `GITPR_POLICY_ENABLED` et `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`.
* **Rétrocompatibilité :** sans pack actif, le comportement est **préservé** — couvert par un test d'intégration.
* **Dépendance :** `packaging>=23.0` (SemVer/PEP 440).
* **Tests :** `tests/domain/policy/` (4 fichiers, 126), `tests/application/use_cases/` (4 fichiers policy, 89), `tests/test_policy_cli.py` (26) et `tests/integration/test_policy_effect_on_review_linter_risk.py` (31).
* **Artefacts :** `docs/policy-packs.*.md` en **5 langues**, ADR-011, `glossary-policy-packs.md`, spec/plan/survey.

### **40. Sous-commande `gitpr baseline` — Baseline et Suppressions Auditables (`src/domain/baseline/`)**

* **Ce Que C'est :** les linters et les reviews signalent à nouveau les mêmes constats préexistants à chaque exécution. La baseline enregistre le **snapshot des constats que le dépôt connaît déjà** dans `.gitpr/baseline.json`, empreinte chacun d'eux de façon stable entre les exécutions et laisse le pipeline classer ce qui est nouveau — **seul `new` bloque**.
* **Couche de Domaine (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`) :**
  * **Empreinte stable :** identité de la règle, chemin normalisé, lignes et **sha256 de la ligne avec les espaces réduits** — la ligne brute n'est **jamais** stockée.
  * **Manifest canonique :** JSON canonique avec checksum, validation qui refuse une clé inconnue, une version de schéma/empreinte non prise en charge, un invariant suppression/dette cassé, une empreinte dupliquée et une plage de lignes invalide.
  * **Comparateur :** classification, comptages par statut et résolution d'entrée — `resolved` puis `back` se lit comme **new**, la lecture conservatrice.
  * **Politique de Suppression :** suppressions, dette acceptée, overrides et validateurs, avec correspondance en couches par **spécificité et précédence**, plus le suivi de la dette échue.
* **Cinq Cas d'Usage :** `baseline_gate`, `baseline_summary`, `compare_against_baseline`, `create_baseline` et `suppress_finding`.
* **Intégration au Policy Pack :** le pack peut porter un bloc `baseline:`, validé par les **mêmes** validateurs.
* **Le Repository est le Seul Écrivain :** `local_baseline_repository.py` est le **seul** lecteur/écrivain de `.gitpr/baseline.json` et `.gitpr/baseline.overrides.yml`.
* **Configuration :** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` et `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`.
* **Opt-In et No-Op :** désactivée ou sans baseline existante, la fonctionnalité **ne lit aucun fichier et n'imprime rien**.
* **⚠️ L'Exception Délibérée :** un fichier de baseline qui existe mais **ne peut pas être appliqué** fait **avorter** lint, review, full review, audit de fichier, review distant et risque **avec le code de sortie 1**. Dans `gitpr check`, une baseline corrompue sort avec le **code 2**.
* **Le Contrat est Devenu Test :** `tests/integration/test_baseline_exit_code_contract.py` (8 scénarios) verrouille les deux codes.
* **Portée :** CLI (6 sous-commandes, toutes avec `--format json`), MCP (`get_baseline_status` + `baseline://summary`), review de PR distant et le flux de lint.
* **Le Refactor de la Fonctionnalité a Exigé :** `NormalizedFinding` est devenu un type canonique dans `src/domain/finding/`.
* **Tests :** `tests/domain/baseline/` (5 fichiers, **126**), `tests/application/use_cases/` (5 fichiers, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) et `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34) + `tests/integration/test_baseline_exit_code_contract.py` (8).
* **Artefacts :** `docs/baseline-suppressions.*.md` en **5 langues**, ADR-012, `glossary-baseline.md`, spec/plan/survey et rapport de tâche.

### **41. Sous-commande `gitpr check` — Barrière CI Unifiée (`src/domain/check/`, `src/infrastructure/ci/`)**

* **Ce Que C'est :** un **point d'entrée unique** pour les pipelines de CI qui orchestre l'analyse statique, les ponts SAST, l'analyse de secrets, le review IA optionnel, le score de risque et la classification de baseline, et émet **un artefact standardisé** avec un code de sortie déterministe.
* **L'Ordre du Pipeline est Contrat :** (1) résolution de policy — invalide sort avec `2` ; (2) extraction du diff avec smart excludes ; (3) linter regex + secrets + ponts externes ; (4) score de risque préliminaire ; (5) **review IA optionnel** ; (6) classification de baseline ; (7) évaluation du seuil `--fail-on` ; (8) génération de l'artefact avec **masquage des secrets**.
* **Couche de Domaine (`src/domain/check/`) :** `check_types.py` (sévérité normalisée en trois niveaux avec les alias de spec) ; `check_config.py` (précédence **CLI > env > défauts**) ; `check_output.py` (dispatch de format et écriture atomique).
* **Couche d'Infrastructure (`src/infrastructure/ci/`) :** `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (testsuites avec `<failure>` et `<system-out>`) et `masking.py`.
* **Use Case (`run_check.py`) :** `resolve_diff_base()` (explicite → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), exécution du linter et des bridges, score de risque, review facultative, baseline et enveloppe finale. Le stdout n'émet **que l'artefact**. L'agrégation de risque accepte `path` **ou** `file_path`.
* **Codes de Sortie :** `0` succès ; `1` constat comptable au seuil ; `2` erreur d'exécution.
* **CLI :** `gitpr check` avec 8 options et 9 variables `GITPR_CHECK_*` — 🆕 désormais aussi dans la TUI de configuration (catégorie `check`).
* **GitHub Action Réécrite :** `action.yml` est devenu une seule étape `gitpr check`. **Rupture déclarée** pour les workflows qui dépendaient de `--linter` + `--fullreview`.
* **🆕 La Note de Migration est Devenue Test :** `tests/unit/test_check_ci_docs_parity.py` exige les 5 variantes de langue de `docs/check-ci.*`, les 7 sections `## 1.`–`## 7.` et les termes `action.yml`, `--linter`, `--fullreview`, `gitpr check` et `SARIF` dans la section de migration — plus le terme **Legacy CI action contract** dans le `CONTEXT.md`.
* **Tests :** `test_run_check.py` (8), `test_check_types.py` (8), `test_check_config.py` (3), `test_check_output.py` (3), `test_masking.py` (6), `test_sarif_builder.py` (2), `test_junit_builder.py` (2), `test_check_cli.py` (5), `test_baseline_exit_code_contract.py` (8) et 🆕 `test_check_ci_docs_parity.py` (2).
* **MCP :** l'outil `run_check` retourne l'enveloppe JSON du même pipeline, avec `no_ia` à `true` par défaut.
* **Artefacts :** `docs/check-ci.*.md` en **5 langues**, ADR-013, `glossary-check-ci.md`, spec, `plansfacts` et survey.

### **42. Extension Éditeur — VS Code et Cursor (`extensions/vscode/`)**

* **Ce Que C'est :** une extension **client mince** qui apporte les flux GitPR dans l'éditeur sans rien réimplémenter : elle démarre `gitpr-mcp` sur stdio et appelle les outils existants. Clés, jetons, prompts et cache restent dans `~/.gitpr/`, chiffrés avec Fernet.
* **Architecture :** `GitPrMcpClient` sur `@modelcontextprotocol/sdk` + un `SessionManager` avec **une session par dossier** (multi-root) + vérification de contrat à la connexion : les 7 outils obligatoires doivent exister ; sinon `MissingMcpToolsError` indique `pip install --upgrade gitpr-cli`.
* **8 Commandes :** `gitpr.commit`, `gitpr.review`, `gitpr.fullReview`, `gitpr.reviewMentor`, `gitpr.generatePr`, `gitpr.generatePrExplain`, `gitpr.publishPr` et `gitpr.check`.
* **Publication en Deux Étapes :** `generatePr` ouvre un document Markdown éditable ; `publishPr` demande une confirmation modale et gère `success`, `needs_push`, `already_exists`, `auth_required` et `error`.
* **Diagnostics dans le Panneau Problems :** `run_check` est converti en `DiagnosticCollection("gitpr")`.
* **Masquage à Double Profondeur :** `masking.ts` reproduit `src/infrastructure/ci/masking.py`.
* **Localisation de l'Extension :** bundles en **en, es, fr et pt-br** (4 langues) — l'UI de GitPR reste à 5.
* **🆕 Métadonnées de Marketplace :** le `package.json` a gagné `icon` (`images/icon.png`), `homepage` et `bugs.url` ; le paquet transporte `LICENSE` (LGPL-2.1), condition minimale pour un listing crédible.
* **🆕 Pipeline Durci :** `.github/workflows/extension.yml` accepte `workflow_dispatch` avec `dry_run`, confirme que le tag `ext-v<version>` correspond au `package.json` avant de builder, échoue avec `::error::` quand `VSCE_PAT`/`OVSX_PAT` manque, et déplace les identifiants dans l'`env` du job pour que validation et publication les lisent.
* **Ce Que l'Extension Ne Fait Pas :** ne commit pas, ne push pas et ne publie pas sans confirmation. La commande `gitpr.commit` de l'extension appelle l'outil MCP `generate_commit_message` — elle **n'**ouvre **pas** la TUI, qui est exclusive au terminal. La clôture automatique d'issue est aussi **exclusive au CLI**.
* **Tests :** suite Mocha propre (7 scénarios, dont stdio réel ; le timeout du test d'intégration est passé de 20s à 60s) + `tests/test_mcp_server.py` (117 scénarios).
* **Artefacts :** `docs/editor-extension.*.md` en **5 langues**, `docs/marketplace-publishing-guide.md` 🆕, ADR-014, `glossary-editor-extension.md`, specs, `plansfacts` et rapport Gemini.

### **43. TUI de Commit Interactive (`src/ui/commit_app.py`)**

* **Ce Que C'est :** `gitpr -c`/`--commit` a cessé d'être un pipeline silencieux — il validait l'arbre, demandait le message à l'IA et commitait aussitôt, sans aucune façon de choisir les fichiers, relire le texte ou rejeter une mauvaise suggestion. C'est désormais un `CommitApp(App)` en **4 étapes** qui garde le développeur aux commandes jusqu'au `git commit`.
* **Paquet :** un module unique, `src/ui/commit_app.py` (220 lignes). Les trois modales sont **importées** de `src/ui/pr_publish_app.py` (`StageFilesScreen`, `CommitMessageScreen`, `CommitProgressScreen`) avec `_with_suppressed_stdout` — **zéro widget dupliqué**.
* **Les 4 Étapes :**
  1. **Sélecteur de Stage :** s'il y a des fichiers hors index, il ouvre `StageFilesScreen` avec des cases à cocher, « Tout sélectionner », « Tout désélectionner », « Indexer la sélection », « Ignorer l'indexation » et « Annuler ». Index déjà complet → l'étape est sautée.
  2. **Génération :** lit `get_git_staged_diff()`, retombe sur `get_git_diff()` quand rien n'est indexé, et appelle `generate_pr_content("commit", "commit", diff_text, provider)` dans un `threading.Thread` daemon pendant que `CommitProgressScreen` anime.
  3. **Relecture :** `CommitMessageScreen` affiche le message, qui peut être édité avant confirmation — c'est le message **édité** qui part vers git.
  4. **Exécution :** un nouveau `CommitProgressScreen` lance `execute_git_commit(append_coauthor_trailer(msg))` et enregistre le SHA dans le log.
* **Chaque Échec Devient un État, pas une Exception :** une IA en panne retombe sur `__("Code update")` ; un commit qui lève devient `failed` avec la sortie capturée. États terminaux : `no_changes`, `cancelled`, `committed`, `failed`.
* **Auto-Exit en Cas de Succès :** `log.mark_finished(False)` retire le bouton, et `_schedule_auto_exit()` enregistre `set_timer(0.6, self.exit)` — l'utilisateur revient au prompt sans rien cliquer. **En cas d'erreur, Fermer est conservé**, car c'est là que le journal d'échec reste lisible.
* **`--hook` Intact :** le chemin du hook reste sans TUI et conscient des merges (`is_merge_in_progress()` → `return` silencieux). `prepare-commit-msg` n'ouvre jamais l'application.
* **✅ La Rupture a Été Fermée — garde de TTY :** sans `--hook`, le routage consulte désormais `_may_ask_the_user()` ; avec TTY, ouvre la `CommitApp` ; sans TTY (CI, pipe, cron), retombe sur le flux console silencieux. C'est cette correction qui a débloqué le scénario qui figeait la suite complète à ~33%.
* **Configuration :** `GITPR_SKIP_UNSTAGED_CHECK` (saute le Stage Selector) et `GITPR_AUTO_STAGE` (met tout en stage et continue), les deux dans le `DEFAULT_CONFIG` et dans le schéma de la TUI de configuration.
* **Détails Textual :** `ENABLE_COMMAND_PALETTE = False` ; `on_button_pressed` gère `#btn_close` ; `_on_commit_progress_dismissed` lit `_commit_success` au lieu de supposer le succès quand la modale se ferme.
* **✅ Test Headless Stabilisé :** `pilot.click("#btn_confirm")` est devenu `Button.press()` + `pilot.pause()`, le prédicat d'attente exige `CommitMessageScreen` monté avec `#btn_confirm`, et le timeout par défaut est passé de **5 s à 10 s**. Cinq rondes de stress ont fermé à 100%.
* **Tests :** `tests/test_commit_app.py` (**17 scénarios** 🆕) — chaque transition d'état et callback, un flux headless complet et le nouveau `TestCliCommitRouting` (routage avec et sans TTY).
* **Domaine :** `CONTEXT.md` conserve les termes **Stage selector** et **Commit flow**.
* **Artefacts :** `docs/commit-message-ia.*.md` dans les **5 langues** et le rapport de tâche Gemini.

### **44. Clôture Automatique d'Issues (`src/issue_autoclose/`) — 🆕 Multi-Forge**

* **Ce que c'était :** l'issue créée avec `gitpr -is` était orpheline — le numéro n'existait que dans la TUI d'issue et se perdait à l'instant où elle se fermait. Chaque PR devait être lié à la main, et le merge ne fermait rien.
* **Paquet de 5 fichiers :** `issue_registry.py`, `register_created_issue.py`, `resolve_close_issue.py`, `insert_close_line.py` et `__init__.py`. Chacun a une responsabilité : persister, enregistrer à la création, décider à l'exécution suivante et insérer dans le corps.
* **🆕 Registre Provider-Neutre (`IssueRegistry`) :** le fichier est passé de `.gitpr/conf/github.yml` à **`.gitpr/conf/issue.yml`**, avec un champ `provider` (default `github`). Le `github.yml` hérité est **lu** de façon transparente quand le nouveau n'existe pas et est effacé à la prochaine écriture/nettoyage — migration sans intervention de l'utilisateur.
* **Auto-Ignoré avec Précision :** `record()` exécute `git check-ignore` avant la première écriture et ajoute exactement une ligne au `.gitignore` quand le fichier n'est pas couvert — `.gitpr/conf/` n'est **pas** ignoré en entier, parce que `gitpr.config.yml` est versionné à dessein ([ADR-015](docs/plans/ADR-015-project-local-pr-base.md)).
* **Enregistrement à la Création :** `register_created_issue()` enregistre le numéro **et le Provider** de n'importe quelle forge supportée et avale les échecs de bookkeeping — un problème d'écriture locale ne transforme jamais une création réussie en erreur. Le dénouement ancien `not-github` est devenu `no-provider`.
* **Décision (`resolve_close_issue`) :** barre par flags et configuration, mais **plus** par forge ; chaque forge sans support se dégrade en `skipped_reason="not-supported"` au lieu d'être ignorée. Vérifie que l'issue reste **ouverte** et **n'est pas un PR** (en préférant `IssueInfo.is_open`), efface les registres morts (404, fermée, numéro de PR) et demande **une fois**, en avertissant quand le PR vise une branche différente de la default.
* **Ligne Idempotente (`insert_close_line`) :** détecte toute keyword existante (`close/closes/closed`, `fix/fixes/fixed`, `resolve/resolves/resolved`, avec préfixe `owner/repo#N` optionnel, insensible à la casse) et ne duplique jamais la ligne.
* **CLI :** `--close-issue` et `--no-close-issue` — mutuellement exclusifs, refusés avant tout prompt ou écriture ; le `-h` l'emporte sur le conflit.
* **Câblage dans le Flux de PR :** `_prepare_close_issue` s'exécute **avant** le Sélecteur de Stage et réutilise le contexte SCM résolu ; `_publish_pr_directly` renvoie si la forge a réellement publié ; `PrPublishApp.body_published` distingue créer/mettre à jour de simplement ouvrir un PR existant. Le registre n'est nettoyé qu'après une publication réelle (ADR-007).
* **Contrat SCM :** `IssueInfo` (state dans le vocabulaire de la forge, `is_open` et `is_pull_request`), `get_issue()`, `get_default_branch()` et `closing_line()` sont des defaults concrets de l'ABC ; 🆕 GitHub, GitLab et Bitbucket implémentent (syntaxe `Closes #N` sur GitLab, `close #N` sur GitHub/Bitbucket), et Azure DevOps lève `ScmNotSupportedError`.
* **Configuration :** `GITPR_ISSUE_AUTOCLOSE` (default `true`, opt-out) et `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` (default `5`, int positif), exposées dans la catégorie `issue` de la TUI.
* **Télémétrie :** une ligne `issue_autoclose` par décision (`asked`, `accepted`, `skipped_reason`).
* **Tests :** 5 fichiers dans `tests/issue_autoclose/` (**126 scénarios** 🆕) — round-trip du registre, schéma, corruption, échec d'écriture atomique, `.gitignore`, migration de l'hérité, décision, idempotence de la ligne, flags, câblage et sémantique de `body_published`.
* **Artefacts :** [ADR-010](docs/adr/0010-multiforge-issue-autoclose.md), `docs/specs/issue-autoclose/` (12 fichiers), `docs/issue-autoclose.*` en **5 langues** 🆕, `docs/issue-tui-help.*`, `docs/config-tui.*` et `docs/metricas-telemetria.*`, README dans les 5 langues et rapports de tâche.

### **45. Base du PR par Projet (`src/project_config.py`, `src/ui/pr_base_app.py`)**

* **Ce que c'était :** la branche de destination du PR ne vivait que dans le `~/.gitpr/.env` de la machine (`PR_DEFAULT_BASE`) ou était devinée par `origin/HEAD`. Un dépôt qui vise `develop` dépendait de chaque développeur pour configurer la même variable, et l'erreur (PR ouvert contre la mauvaise branche) n'apparaissait que sur la forge.
* **Ce que c'est :** `.gitpr/conf/gitpr.config.yml` — un fichier **versionné avec le projet** qui porte la base du PR, édité par la TUI de configuration et validé avant tout commit/push. Un résolveur unique sert la CLI et le MCP, donc tous les points d'entrée s'accordent sur la précédence et les règles d'échec.
* **Résolveur (`src/project_config.py`) :** `resolve_pull_request_base()` — `--base` → fichier du projet → `PR_DEFAULT_BASE` → détection `origin/HEAD` → `main`. Lecture/écriture YAML préserve les clés tierces ; seeding idempotent ; listage des branches ; détection de la branche courante ; vérification contre le distant.
* **🆕 Deuxième Section (`scm:`) :** le fichier transporte aussi `scm.provider` (lecture/écriture), avec les deux sections indépendantes et refus d'écraser un bloc `scm` qui n'est pas un mapping. C'est cet override qui l'emporte sur la détection par le remote (section 46).
* **Échec Avant la Naissance du PR :** `BaseBranchError`/`CurrentBranchError` ; la base doit exister dans `origin/<branch>` et différer de la branche courante — sans `origin` ou avec une ref absente, le flux s'arrête avec une astuce actionnable au lieu d'ouvrir un PR voué à l'échec.
* **TUI de Configuration :** catégorie `project` avec attribut `store` (`env` vs `project`), `KIND_BRANCH` et trois champs : path du fichier (read-only), branche cible (éditable) et base effective avec l'origine (read-only).
* **Modale de Réparation (`src/ui/pr_base_app.py`, 98 lignes) :** quand la cible est la branche courante elle-même et que l'exécution est interactive, une modale ciblée demande la branche de remplacement et l'enregistre dans le fichier du projet.
* **CLI :** `--base` passe par `_resolve_pr_target()` ; le fichier est semé à chaque invocation dans un dépôt (sauf aide), annoncé seulement sur le chemin interactif de la racine ; `GITPR_SKIP_PROJECT_CONFIG_SEED` désactive le seed. 🆕 `gitpr seed` fait la même chose de façon explicite et idempotente (section 47).
* **MCP :** `publish_pull_request` utilise le résolveur partagé, renvoie une enveloppe d'erreur pour `BaseBranchError` et expose `warnings` dans le payload et dans le catalogue.
* **Effet de Bord Déclaré :** lancer `gitpr` dans un dépôt crée désormais le fichier à la première exécution. Un fichier existant n'est jamais écrasé ; si le `.gitignore` exclut le chemin, un avertissement explique que la valeur ne sera pas versionnée.
* **Tests :** `tests/test_project_config.py` (**49**), `tests/test_project_config_cli.py` (7), `tests/test_pr_base_app.py` (12) + extensions dans `test_config_app.py` (94), `test_config_schema.py` (53), `test_config_store.py` (22), `test_mcp_server.py` (117) et `test_core.py` (49).
* **Artefacts :** [ADR-015](docs/plans/ADR-015-project-local-pr-base.md), `docs/plans/develop_natan/20261030_project_pr_base_plansfacts.md`, `docs/survey/20261030_project_pr_base_surveyfacts.md`, et `docs/config-tui.*`/`docs/install-wizard.*`/`docs/pr-descricao-padrao.*`/`docs/pull-request-publication.*` dans les 5 langues.

### **46. Configuration SCM par Dépôt et par Forge (`src/scm_settings.py`) 🆕**

* **Ce que c'était :** la forge (`GITPR_SCM_PROVIDER`), le jeton (`GITPR_SCM_TOKEN_ENCRYPTED`) et la base URL étaient des configurations **globales de la machine** dans `~/.gitpr/.env`. Une machine ne servait pas plus d'une forge à la fois, l'identifiant erroné partait vers le mauvais hôte quand le remote d'un dépôt changeait, et tout hôte self-hosted non reconnu était silencieusement mappé sur GitHub.
* **Ce que c'est :** la forge et l'identifiant sont devenus une **propriété du dépôt** ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)). Un résolveur unique répond « quelle forge, quel jeton, quelle base URL valent ici ? » pour toutes les surfaces.
* **Ordre de Résolution (`resolve_provider_choice`) :** override du projet (`scm.provider` dans `.gitpr/conf/gitpr.config.yml`) → `detect_provider_from_remote(origin, default=None)` → `github`. Une valeur de forge inconnue dans le fichier du projet est **ignorée avec avertissement**, jamais fatale ; `default=None` permet au wizard de distinguer « reconnu comme GitHub » de « non reconnu » et de **demander** au lieu de supposer.
* **Un Slot par Forge :** `GITHUB_TOKEN_ENCRYPTED`, `GITLAB_TOKEN_ENCRYPTED`, `BITBUCKET_TOKEN_ENCRYPTED`, `AZURE_DEVOPS_TOKEN_ENCRYPTED` (Fernet) et les `*_BASE_URL` correspondants. Un jeton ne répond jamais pour une autre forge ; le `GITPR_SCM_TOKEN` brut reste l'override global de CI/CD et est tenté en premier sur tous les Providers.
* **Migration Unique et Idempotente (`migrate_legacy_scm_config`, sous lock) :** déplace `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` + `GITPR_SCM_BASE_URL` vers le slot de la forge qu'ils nomment, **n'écrase jamais** un slot déjà rempli, supprime les lignes héritées et continue de fonctionner quand le `.env` est en lecture seule (se dégrade vers l'environnement du processus). Un jeton hérité **sans** Provider enregistré est délibérément **laissé en place**, sans deviner.
* **Wizard (`gitpr --init`) :** persiste **seulement en cas de succès**, écrit dans le slot de la forge choisie, propose la base URL enregistrée comme default du prompt (hors GitHub) et n'écrit `scm.provider` dans le fichier du projet que quand la forge choisie diffère de celle détectée — le fichier est commité et partagé avec l'équipe.
* **TUI de Configuration :** ligne éditable `PROJECT_SCM_PROVIDER` et ligne read-only `PROJECT_EFFECTIVE_PROVIDER` (forge en usage + origine), jetons et base URLs de toutes les forges toujours listés, test de jeton contre l'hôte de son propre slot et un groupe `gitlab`.
* **Surfaces Converties :** `src/main.py`, `src/metrics.py`, `src/release_engine.py` et `src/mcp_server.py` résolvent par `get_scm_settings()` et propagent les avertissements du résolveur.
* **Non Interactif ne Demande Jamais :** `validate_or_request_scm_token(..., allow_prompt=False)` dans les hooks, MCP et CI renvoie « lancez `gitpr --init` », n'efface pas l'identifiant enregistré et utilise encore un jeton validé qui n'a pu être persisté.
* **Tests :** `tests/scm/test_scm_settings.py` (**24 scénarios**) + extensions dans `test_init_wizard.py` (20), `test_factory.py` (14), `test_azure_devops_provider.py` (51), `test_bitbucket_provider.py` (49), `test_gitlab_provider.py` (56) et 🆕 `tests/test_tui_issue_token.py` (8).
* **Artefacts :** [ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md), `docs/plans/develop_natan/20261107_per_provider_scm_settings_plansfacts.md`, `docs/scm-multiforge.*` (5 langues) et le glossaire `docs/plans/glossary-scm-multiforge.md`.

### **47. Sous-commande `gitpr seed` — Bootstrap de la Configuration Locale (`src/core.py`, `src/main.py`) 🆕**

* **Ce que c'était :** la configuration locale du projet (base du PR, smart excludes, règles de linter) naissait **comme effet de bord** du callback racine et du téléchargement de skills. Il n'y avait aucun moyen de la semer de façon explicite, répétable et idempotente — et un CI qui dépendait de ces fichiers ne le découvrait qu'à la deuxième exécution.
* **Ce que c'est :** `gitpr seed` (et le flag global `-sd/--seed`) promeuvent le bootstrap en opération de première classe. `seed_project_configuration()` crée :
  * `.gitpr/conf/gitpr.config.yml` (base du PR, via `ensure_project_config`) ;
  * `.gitpr/conf/gitpr.smart-excludes.json` (exclusions du projet) ;
  * `.gitpr/skill/.gitpr.linter.yml` (règles du linter local).
* **Ne Détruit Jamais sans Demande :** un fichier existant n'est **jamais** écrasé ; `--force/-f` écrase à dessein, et `--all/-a` inclut aussi le téléchargement de toutes les skills IA. La fonction renvoie un résumé structuré (`config`, `smart_excludes`, `linter`, `skills_downloaded`) et la commande enregistre la métrique `seed`.
* **Wizard d'Installation en 5 Étapes :** **1)** Configuration Locale du Projet (nouveau, confirmable), **2)** Skill Templates (avec le setup facultatif des bridges de linter externe), **3)** Git Hooks, **4)** Configuration du MCP, **5)** Clé d'API. Sauter est devenu explicite.
* **Compatibilité :** `generate_skill_template()` continue de semer automatiquement les smart-excludes, donc l'ancien chemin ne casse pas ; `ensure_project_config()` et `download_skill_file()` ont gagné le paramètre `force` en gardant le default no-clobber.
* **Aide :** `HELP_MAP["seed"]` pointe vers `docs/install-wizard.md` et décrit `--force`/`--all`.
* **Tests :** `tests/test_seed_command.py` (**8 scénarios**) — création, idempotence, écrasement forcé, `--all`, CLI et flag ; `tests/test_install_wizard.py` mocke désormais le setup de linter externe.
* **Artefacts :** `docs/install-wizard.*` (5 langues), `docs/plans/develop_natan/20261105_seed_local_conf_plansfacts.md`, survey et walkthrough, et le terme **Project configuration seed** dans le `CONTEXT.md`.

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame par plage de lignes dans un fichier |
| `tests/test_blame_metrics.py` | 7 | Métriques de blame : profondeur, commits, durée |
| `tests/test_baseline_cli.py` | 54 | Groupe `baseline` : create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Builder de changelog : sections, titres traduisibles, contributeurs, liens et dates |
| `tests/test_chat_backend.py` | 19 | Mémoire du chat, persistance, commandes slash |
| `tests/test_check_cli.py` | 5 | CLI `check` : options, formats, codes de sortie |
| `tests/test_cli_help.py` | 9 | `-h` avant la sous-commande : aide rendue, TUI jamais ouverte |
| `tests/test_commit_app.py` | 17 | `CommitApp` : mount, Stage Selector, auto-stage, skip, annuler, confirmer, succès/échec, auto-exit, flux headless **et routage avec/sans TTY** 🆕 |
| `tests/test_commit_classifier.py` | 30 | Classification Conventional Commits + `pr_number_from_merge()` sur les 4 forges |
| `tests/test_config_app.py` | 94 | TUI de configuration : montage, catégories, dirty tracking, F2 bloqué, Ctrl+R, recherche, secrets, stores `project`, panneau Smart Excludes 🆕 |
| `tests/test_config_cli.py` | 9 | Enregistrement de la sous-commande `config`, `-h`, import paresseux, stdout propre |
| `tests/test_config_issue_autoclose.py` | 10 | Getters de configuration et champs du schéma de clôture d'issue |
| `tests/test_config_schema.py` | 53 | Couverture du `DEFAULT_CONFIG` dérivé, sans doublons, catégories/kinds, `seeded`, section de skills, bannière et palette 🆕 |
| `tests/test_config_store.py` | 22 | Round-trip sur le `.env` temporaire, commentaires et ordre préservés, `remove_config_value()` idempotent |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuration des reviewers suggérés (clés et défauts) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` avec SDK mocké (401 vs. réseau vs. ollama) |
| `tests/test_core.py` | 49 | Flux principaux, git diff, génération de PR, timing, staging, co-paternité, langue des hooks, base du PR |
| `tests/test_diff_failure_cli.py` | 3 | Code de sortie 1 sur échec de git, 0 sur diff vide, fail-open en `--hook` |
| `tests/test_diff_parser.py` | 26 | Parser de diff par lignes/hunks, `summarize_patch()` et `split_patch_sections()` |
| `tests/test_doc_links.py` | 11 🆕 | Contrat `?lang=` du site, `doc_url()` dynamique, liens des TUIs et parité de 5 langues |
| `tests/test_explain_command.py` | 4 | CLI `explain` : succès, clé manquante, diff vide, aide contextuelle |
| `tests/test_external_linters.py` | 33 | Pont Checkstyle : parser XML, subprocess, intersection de diff, rapport |
| `tests/test_full_diff_encoding.py` | 8 | Dépôt réel avec fichier Latin-1 + garde de hunk binaire (fast path, collapse, densité) 🆕 |
| `tests/test_i18n.py` | 22 ✅ | Parité entre langues, clés manquantes/orphelines, identité — **tous verts** |
| `tests/test_install_wizard.py` | 3 | Assistant interactif d'installation (5 étapes) |
| `tests/test_issue_engine.py` | 4 | Brouillon d'issue structuré |
| `tests/test_linter_metrics.py` | 4 | Métriques du linter : erreurs, avertissements, durée |
| `tests/test_linter_presets.py` | 5 | Presets de linter : résolution et re-téléchargement forcé |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` dans la CLI et dans l'aide contextuelle |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompts MCP et fallback de langue |
| `tests/test_mcp_server.py` | 117 | Outils MCP (18), ressources (19), annotations, patching, CLI directe, offload, contrat de l'extension, base du PR |
| `tests/test_mcp_server_e2e.py` | 6 | Vrai serveur MCP via subprocess + JSON-RPC stdio |
| `tests/test_mentor_cli.py` | 4 ✅ | CLI du `mentor` et opt-in de `--mentor` — patches dans `src.main` |
| `tests/test_metrics.py` | 174 | **Ledger** : écriture synchrone, exportation, bundle/merge, migration, renderers, coût, qualité, modules, Providers, cycle, **Token Trend et Latency Histogram** 🆕 |
| `tests/test_net_timeouts.py` | 12 | Timeouts réseau/IA — alignés sur le défaut réel de 180s |
| `tests/test_plugins.py` | 17 | Découverte de plugins, fusion de règles de linter, prompts MCP |
| `tests/test_policy_cli.py` | 26 | Groupe `policy` : list/show/validate/use/off/install/init, confirmation et lockfile |
| `tests/test_pr_base_app.py` | 12 | Modale de changement de la base du PR : montage headless, choix et annulation |
| `tests/test_pr_publish_app.py` | 59 | TUI du PR Publisher : écrans, flux, reviewers suggérés, `NoticeScreen`, modale de merge, `body_published` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modale d'erreur du linter : abandon, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save et payload JSON |
| `tests/test_project_config.py` | 49 | `project_config` : path, YAML, seed, résolveur de base, section `scm`, validation contre le distant 🆕 |
| `tests/test_project_config_cli.py` | 7 | Câblage de la base du projet dans le CLI |
| `tests/test_release_cli.py` | 4 | CLI de release : options, aide avec epilog documenté |
| `tests/test_release_engine.py` | 61 | **Coupure ancrée sur le changelog, `_drop_released()`, liens, carte de PR par merge commit**, front matter de langue 🆕 |
| `tests/test_reviewer_resolution.py` | 18 | Échelle de résolution, rejet de nom partiel, dédup, tolérance aux échecs |
| `tests/test_reviewer_suggestion.py` | 18 | Logique de suggestion de reviewers (classement, exclusion, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | CLI `risk` : `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 77 | Matrice du ruleset intégré : regex, placeholders, niveau, wildcard, fusion, traductions |
| `tests/test_seed_command.py` | 8 🆕 | `gitpr seed`/`--seed` : création, idempotence, force, `--all`, métriques |
| `tests/test_skill_command.py` | 10 | Téléchargement et validation des templates de skill |
| `tests/test_skill_context.py` | 20 | `get_skill_context()` avec `quiet=True`, fallbacks, garde de l'artefact de PR |
| `tests/test_skill_frontmatter.py` | 11 🆕 | `split_front_matter()` : BOM, CRLF, YAML malformé, allowlist de langues |
| `tests/test_skill_templates.py` | 99 | Scan paramétré de **chaque template publié** contre les références embarquées |
| `tests/test_smart_excludes.py` | 17 | Filtre pathspec intelligent, re-download forcé, **nouvelles familles binaires** 🆕 |
| `tests/test_suggest_reviewers.py` | 19 | Suggested reviewers dans le flux de PR + **résumé de blame** 🆕 |
| `tests/test_tests_command.py` | 4 | Groupe `tests` et CLI `generate`, `--finding` et epilog avec lien |
| `tests/test_thinking_words.py` | 5 | Chargement, parsing avec séparateur `;` et rechargement forcé |
| `tests/test_tui_issue_token.py` | 8 🆕 | `allow_prompt=False` : ne demande jamais, n'efface jamais, pointe `gitpr --init` |
| `tests/test_updater.py` | 55 | **Barrière PyPI** : TTL glissant, cache hérité/corrompu, écriture atomique, `force`, `bounded_urlopen`, comparateur PEP 440, pré-release, interrupteur strict et barrière des sous-commandes |
| `tests/test_usage_log.py` | 27 | Journal d'usage : nom dérivé de la date, écriture synchrone, silence, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump sémantique : major/minor/patch, cibles et validation |
| `tests/test_web_links.py` | 26 | Normalisation de remote (https/ssh/scp/credentials/port) et motifs d'URL par forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 | Activation de pack, lockfile, refus |
| `tests/application/use_cases/test_baseline_gate.py` | 40 | Barrière : chargement, classification, annotation, couches et abandon |
| `tests/application/use_cases/test_baseline_summary.py` | 12 | Vue en lecture seule de la baseline pour MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 | Corrélation diff/constats/tests/historique, dégradation |
| `tests/application/use_cases/test_create_baseline.py` | 30 | Création/mise à jour avec provenance et préservation des décisions |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 | Lots, sévérité, cache hit, clé manquante |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 3 | Cas d'usage explain : dict du fournisseur et clé manquante |
| `tests/application/use_cases/test_generate_test_file.py` | 5 | Cas d'usage de génération de tests : dict du fournisseur et chaîne brute |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 | Installation de pack, origines et conflits |
| `tests/application/use_cases/test_run_check.py` | 8 | Orchestration du check : ordre, dégradation, codes de sortie |
| `tests/application/use_cases/test_suppress_finding.py` | 29 | Suppression/retrait dans les quatre portées |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 | Validation : schéma fermé, checksum, compatibilité |
| `tests/badge/test_badge_append.py` | 9 | Ajout idempotent du badge au corps du PR |
| `tests/badge/test_badge_builder.py` | 19 | Composition du Markdown shields.io, échappement, règle de couleur, style |
| `tests/badge/test_badge_cli.py` | 15 | Commande `gitpr badge` : extrait, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Comptage des alertes ; `None` sans règles configurées |
| `tests/badge/test_badge_offline.py` | 6 | Garde hors ligne (réseau interdit, sauf loopback) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` sur chaque chemin de publication |
| `tests/badge/test_badge_paths.py` | 10 ✅ | Point d'injection unique dans les deux publieurs |
| `tests/demo/test_demo_app.py` | 22 | TUI de la visite : navigation, écrans, modal d'aide |
| `tests/demo/test_demo_isolation.py` | 14 | La visite ne touche ni `~/.gitpr` ni le réseau |
| `tests/demo/test_demo_runner.py` | 33 | Machine d'état `DemoState` : avancer, reculer, terminé, mode texte |
| `tests/demo/test_demo_scenarios.py` | 47 | Intégrité des scénarios enregistrés (arithmétique des hunks) dans les 5 langues |
| `tests/demo/test_demo_text_mode.py` | 18 | Sortie en texte brut (`--no-tui`) pour la CI et les enregistrements |
| `tests/demo/test_fake_ai_provider.py` | 21 | Contrat du faux fournisseur : reproduit `call_ai_model` argument par argument |
| `tests/domain/baseline/test_baseline_comparator.py` | 21 | Classification, comptages par statut, `resolved`→`new` |
| `tests/domain/baseline/test_baseline_fingerprint.py` | 12 | Digest golden, ligne brute jamais stockée, stabilité |
| `tests/domain/baseline/test_baseline_manifest.py` | 26 | JSON canonique, checksum, clés inconnues, invariants |
| `tests/domain/baseline/test_baseline_types.py` | 15 | Statuts persistés, portées et précédence |
| `tests/domain/baseline/test_suppression_policy.py` | 52 | Couches, spécificité, overrides, dette échue |
| `tests/domain/check/test_check_config.py` | 3 | Précédence CLI > env > défauts |
| `tests/domain/check/test_check_output.py` | 3 | Dispatch de format et écriture atomique |
| `tests/domain/check/test_check_types.py` | 8 | Sévérité à trois niveaux, alias de spec, résumé |
| `tests/domain/finding/test_finding_types.py` | 3 | Type de constat canonique : `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Formatage uniforme et déduplication `[Gitleaks + Regex]` |
| `tests/domain/linter/test_structured_findings.py` | 44 | `findings_out` : constats structurés et compteur de lignes du côté nouveau |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 | Builder pur : honnêteté épistémique, URL supprimées, ordre préservé |
| `tests/domain/policy/test_policy_checksum.py` | 15 | SHA-256 du pack et détection d'édition manuelle |
| `tests/domain/policy/test_policy_compatibility.py` | 22 | Compatibilité de version et de credentials |
| `tests/domain/policy/test_policy_manifest.py` | 42 | Schéma fermé, bloc `baseline:`, erreurs de manifest |
| `tests/domain/policy/test_policy_resolver.py` | 47 | Précédence, composition en couches, pack actif × base |
| `tests/domain/pr/test_explain_section_builder.py` | 6 | Rendu Markdown, parsing tolérant, `[FILL]`, payload dict et `None` |
| `tests/domain/risk/test_risk_calculator.py` | 5 | Formule 50/30/20, clamp 0–100, élévation obligatoire |
| `tests/domain/risk/test_risk_rules.py` | 5 | Poids, seuils, glob de chemin critique, YAML d'override |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 | Détection par config, manifest et répertoire ; override ; échec averti |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 | Chemin conventionnel par framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Cas d'usage complet : review → IA → validation → classification → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extracteur partagé entre le chat et `fix` |
| `tests/fix/test_fix_cli.py` | 31 | Routage de sous-commande, options, dry-run par défaut, `--force` |
| `tests/fix/test_fix_history.py` | 21 | Ledger `.gitpr/fix_history.json` : écriture atomique, lecture |
| `tests/fix/test_fix_settings.py` | 10 | Les cinq `GITPR_FIX_*` et les fallbacks pour valeurs invalides |
| `tests/fix/test_patch_applier.py` | 19 | Wrapper `git apply` : check/apply/reverse, branche, statut |
| `tests/fix/test_patch_extractor.py` | 13 | Blocs clôturés → diff unifié validé |
| `tests/fix/test_patch_safety_classifier.py` | 22 | Matrice safe/review_required/experimental et codes de raison |
| `tests/fix/test_resolve_last_review.py` | 13 | Sélection du dernier review, exclusion des reviews par fichier |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback` : reverse et les trois refus |
| `tests/infrastructure/baseline/test_local_baseline_repository.py` | 24 | Seul lecteur/écrivain : écriture atomique, checksum, réparation |
| `tests/infrastructure/ci/test_junit_builder.py` | 2 | JUnit XML : failure/system-out |
| `tests/infrastructure/ci/test_masking.py` | 6 | Masquage de credentials avant sérialisation |
| `tests/infrastructure/ci/test_sarif_builder.py` | 2 | SARIF 2.1.0 et `tool.driver.rules` |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 | `git log` sûr : commits, bugs et reverts sur 90 jours |
| `tests/infrastructure/git/test_test_matcher.py` | 5 | Correspondance test ↔ production par convention |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Pont Bandit : disponibilité, parsing, mapping |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC : subprocess blindé, timeout, binaire absent |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Pont Gitleaks : masquage de secrets, périmètre par fichier |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Pont Semgrep : parsing JSON, sévérité, saut non-Python |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 | Effet réel de la baseline sur review, lint et risque dans un dépôt réel |
| `tests/integration/test_baseline_exit_code_contract.py` | 8 | Exit code 1 pour baseline inapplicable et 2 dans `check` |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Effet réel du pack sur review, lint et risque (avec garde réseau autouse) |
| `tests/issue_autoclose/test_close_issue_wiring.py` | 26 | Câblage dans le flux de PR : flags, prompt, publication réelle, nettoyage du registre |
| `tests/issue_autoclose/test_insert_close_line.py` | 19 | Insertion idempotente et reconnaissance des keywords de clôture |
| `tests/issue_autoclose/test_issue_registry.py` | 26 | Registre local : round-trip, atomicité, corruption, `.gitignore`, repo match, **migration de `github.yml`** 🆕 |
| `tests/issue_autoclose/test_register_created_issue.py` | 13 | Registre post-création : multi-forge, Provider persisté, échecs avalés |
| `tests/issue_autoclose/test_resolve_close_issue.py` | 42 | Arbre de décision : barrières, `is_open`, PR, registres morts, `not-supported`, confirmation |
| `tests/review/test_diff_normalizer.py` | 20 | Sauts de ligne, validation de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource` : provenance, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orchestration : barrières, PR, diff, linter, commentaire optionnel |
| `tests/review/test_review_pr_cli.py` | 25 | CLI `review-pr` : options, rejets avant l'IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 51 | Provider Azure DevOps : org/projet, PRs, `ScmNotSupportedError`, issue read-back |
| `tests/scm/test_bitbucket_provider.py` | 49 | Provider Bitbucket : Basic auth, workspace, PRs, `close #N` |
| `tests/scm/test_contract.py` | 44 | Contrat `ScmProvider` : signatures, dataclasses, erreurs, defaults d'issue |
| `tests/scm/test_factory.py` | 14 | `resolve_scm_provider()` + `detect_provider_from_remote(default=None)` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` déprécié → délègue au provider |
| `tests/scm/test_github_provider.py` | 86 | Provider GitHub : REST, en-têtes, PRs, issues, releases, relecture des reviewers |
| `tests/scm/test_gitlab_provider.py` | 56 | Provider GitLab : API v4, namespace, en-têtes synthétisés, `overflow`, `Closes #N` |
| `tests/scm/test_init_wizard.py` | 20 | Wizard `--init` : détection de la forge, slot par forge, validation, persistance |
| `tests/scm/test_release_publish.py` | 8 | Publication de release par forge (GitHub/GitLab) |
| `tests/scm/test_scm_settings.py` | 24 🆕 | Résolveur de forge/identifiant : précédence, migration héritée, avertissements, read-only |
| `tests/split/test_apply_split_plan.py` | 11 | Le seul module qui mute : staging sélectif, un commit par groupe |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unités → groupes → pré-validation des conflits → messages |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, budget/troncature, appel IA, validation de réponse |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()` : texte ↔ modèle, pur |
| `tests/split/test_selective_stager.py` | 11 | Staging d'un sous-ensemble, vérification contre HEAD, index propre |
| `tests/split/test_split_cli.py` | 31 | CLI split : options, dry run par défaut, `--apply` |
| `tests/unit/test_version_consistency.py` | 5 | SSOT de la version : `pyproject.toml`, `CLAUDE.md` et `GEMINI.md` contre `src.updater.__version__` |
| `tests/unit/test_check_ci_docs_parity.py` | 2 🆕 | Parité de 5 langues et 7 sections de `docs/check-ci.*` + terme dans le `CONTEXT.md` |
| `tests/sync_i18n.py` | — | Script de vérification de couverture i18n (échafaudage ; jamais exécuté) |

**Total :** **3422 scénarios collectés dans 159 modules de test** (63 à la racine + 12 dans `tests/application/use_cases/` + 10 dans `tests/fix/` + 10 dans `tests/scm/` + 10 dans `tests/infrastructure/` + 21 dans `tests/domain/` + 7 dans `tests/badge/` + 6 dans `tests/demo/` + 6 dans `tests/split/` + 5 dans `tests/issue_autoclose/` + 4 dans `tests/review/` + 3 dans `tests/integration/` + 2 dans `tests/unit/` ; **+146 scénarios, +6 fichiers** depuis le rapport précédent).

**Notes de qualité pour cette version :**
- **✅ Le scénario qui bloquait la suite a été débloqué :** `-c` sans `--hook` n'ouvre plus la `CommitApp` quand il n'y a pas de TTY — le routage consulte `_may_ask_the_user()`. Le `test_a_flow_that_never_enforced_it_is_untouched`, qui bloquait l'exécution complète à ~33%, se termine désormais.
- **✅ L'unique échec intermittent a été fermé :** `test_headless_full_flow` a remplacé le clic par `Button.press()` + `pilot.pause()`, durci le prédicat de montage et porté le timeout de 5 s à 10 s ; cinq rondes consécutives ont fermé à 100%.
- **🆕 Isolation du profil développeur :** les fixtures autouse `english_interface` et `no_hook_script_sync` empêchent la langue et l'auto-sync de hooks de la machine de s'échapper entre les tests CLI.
- **Les nouveaux scénarios couvrent exactement ce qui a changé :** un fichier par fonctionnalité (`test_seed_command.py`, `test_doc_links.py`, `test_skill_frontmatter.py`, `test_tui_issue_token.py`, `scm/test_scm_settings.py`) et des extensions dans les modules existants (metrics, config, release, SCM, issue_autoclose, smart excludes).
- **`tests/conftest.py` reste hermétique :** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false`, `LANG_VERSION` sur la version du code et `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
- **Garde réseau à plusieurs endroits :** `tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py` et `tests/split/conftest.py`.
- **Fixtures git réelles :** `tests/fix/git_fixture.py`, `tests/split/git_fixture.py` et `tests/test_full_diff_encoding.py`.
- **La suite de l'extension est séparée :** 7 scénarios Mocha dans `extensions/vscode/src/test/mcp_client.test.ts`, dont 1 s'intègre vraiment au serveur MCP Python via stdio ; elle ne compte pas dans pytest.
- **La CI tourne sur Python 3.10 et 3.13** avec `fail-fast: false`, et la release a son propre workflow avec Trusted Publishing.

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n :** **1772 clés** dans **les six** dictionnaires (`es.json`, `es_es.json`, `fr.json`, `fr_fr.json`, `pt_br.json`, `pt_pt.json`). Le code utilise les mêmes clés : **0 sans traduction** et **0 orphelines**. C'était 1698 × 6 dans la fenêtre précédente.
* **🆕 Le `__lang_version__` est passé à v0.0.41 avec du contenu :** les chaînes de SCM par forge (slots, wizard, TUI), `seed`, les graphiques de métriques (Token Trend, Peak, Daily avg, Latency, Timed runs) et le résumé de blame ; les 6 dictionnaires ont été synchronisés.
* **Sources de traduction en lockstep :** une nouvelle clé doit exister dans le code (source), dans `langs/pt_br.json` (**liste maîtresse**), dans les dicts FR/ES de `scripts/sync_all_langs.py` et dans les valeurs curées de `scripts/fix_mangled_i18n_keys.py` ; l'extracteur de clés est **AST** des deux côtés.
* **🆕 Sujets nouveaux :** deux dans cette fenêtre —
  - `docs/issue-autoclose.*` (**5 fichiers**) — le registre local, la décision de clôture, les flags `--close-issue`/`--no-close-issue` et la couverture multi-forge.
  - `docs/marketplace-publishing-guide.md` (**1 fichier, EN**) — la publication de l'extension sur les marketplaces, encore sans traduction.
* **🆕 Topics mis à jour dans cette fenêtre :**
  - `docs/scm-multiforge.*` (**5 fichiers**) — forge et identifiant par dépôt, slots par forge, migration héritée ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)).
  - `docs/issue-tui-help.*` et `docs/issue-autoclose.*` — registre renommé en `issue.yml` et comportement sur les quatre forges.
  - `docs/smart-excludes.*` (**5 fichiers**) — la catégorie de la TUI, le fichier du projet et les nouvelles familles binaires.
  - `docs/install-wizard.*` (**5 fichiers**) — le wizard de 5 étapes et `gitpr seed`.
  - `docs/check-ci.*` (**5 fichiers**) — note de migration de l'`action.yml`, verrouillée par test de parité.
  - `docs/editor-extension.*` (**5 fichiers**) — métadonnées de marketplace et pipeline durci.
  - `docs/release-notes.*` (**5 fichiers**) — le front matter `language:` de la skill de release.
  - `docs/version-markers.*` (**5 fichiers**) — ES et FR traduits ; le sujet a cessé d'être partiel.
  - `docs/ARCHITECTURE.*` (**5 fichiers**) — les couches nouvelles (`scm_settings`, `skill_frontmatter`).
  - `docs/metricas-telemetria.md` (**1 fichier**) — Token Trend et Latency Histogram (traductions en attente).
* **`CONTEXT.md` (mis à jour) :** le glossaire du domaine est passé de **22 à 35 termes**. Nouveaux : **Binary hunk guard**, **Seeded configuration field**, **CI Check**, **Legacy CI action contract**, **Marketplace publication workflow**, **Extension release tag**, **Fail-fast secret check**, **Documentation link**, **Latency histogram**, **Token trend**, **Project configuration seed** et **Blame summary**.
* **Documentation en 5 langues :** **52 sujets canoniques** dans `docs/` (**+2**) — **50 avec couverture complète dans les 5 langues** et **2 sujets partiels** (`github-issue-prompt-com-gh`, `marketplace-publishing-guide`). Le `version-markers` a cessé d'être partiel.
* **Nouveaux artefacts de spécification :** [ADR-010](docs/adr/0010-multiforge-issue-autoclose.md) dans `docs/adr/` (désormais **4 fichiers**) et [ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md) dans `docs/plans/`.
* **Skills locaux de Claude Code :** `.claude/skills/` avec **29 skills** (compte inchangé).
* **Index de Mémoire :** `.claude/memory/MEMORY.md` avec **40 patterns** (compte inchangé).
* **Rapports de tâche :** `docs/claude-code/reports/develop_natan/` (**118** ; **+2**) + `docs/claude-code/reports/feat/issue-autoclose/` (**12**) + 🆕 `docs/claude-code/reports/main/` (**1**) et `docs/gemini/reports/` (**42** ; **+11**), plus `docs/gemini/walkthrough/` (**19**).
* **Rapports d'état :** `docs/reports/` — c'est le **23ᵉ rapport** (v0.0.23) ; le v0.0.22 n'est jamais écrasé.
* **Plans de développement :** **161** fichiers dans `docs/plans/` (+9) + **24 fichiers dans `docs/gemini/plans/`** (+12) + **34 fichiers dans `docs/survey/`** (+10).

---

## **🔄 Pipeline de Distribution**

1. **PyPI (canal unique) :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mise à jour obligatoire avec fenêtre glissante :** l'exécution vérifie PyPI au démarrage et **bloque avec le code de sortie 1** s'il existe une version plus récente, en imprimant `pip install --upgrade gitpr-cli`. La réponse est cachée pendant **1 heure** et `--update` interroge **toujours** le réseau. La comparaison est PEP 440 : une pré-release distante ne bloque pas une installation stable, et une version illisible compte comme inconnue. La barrière couvre les sous-commandes quand il y a un TTY ; CI, pipes, hooks, MCP et `-h` restent exemptés.
3. **GitHub Releases :** production automatisée par `release.yml` sur les tags `v*` — build, validation, Trusted Publishing et création de la release avec les artefacts.
4. **GitHub Actions :** workflow `pr-review.yml` + `action.yml` (installe via pip) + `tests.yml` (matrice Python 3.10 et 3.13) + `extension.yml` (VSIX ; `workflow_dispatch` avec `dry_run`, garde de tag, publication sur Marketplace/Open VSX en `ext-v*`) + `release.yml` (PyPI + GitHub Release en `v*`). L'`action.yml` appelle une seule étape `gitpr check` — **rupture déclarée** pour ceux qui utilisaient `--linter` + `--fullreview`, désormais couverte par test de parité de documentation.
5. **Serveur MCP :** point d'entrée `gitpr-mcp` via `pyproject.toml`
6. **Extension d'Éditeur :** paquet VSIX indépendant (`natanfiuza.gitpr`), avec canaux VS Code Marketplace et Open VSX ; nécessite Node.js 20+ seulement pour le build, pas pour l'usage. 🆕 Le paquet transporte icon et LICENSE et le workflow échoue tôt si tag, version ou identifiant ne correspondent pas.
7. **Templates et langues OTA :** `templates/` et `langs/*.json` servis depuis GitHub (main) — `__lang_version__` **v0.0.41** 🆕 renouvelle les copies locales de `~/.gitpr/langs/` ; `__scripts_version__` **v0.0.4** renouvelle les hooks.
8. **Version dérivée du code :** `pyproject.toml` utilise `version = {attr = "src.updater.__version__"}` — le `__version__` est l'**unique** source, garanti par [ADR-014](docs/adr/0014-canonical-package-version-ssot.md) et par `tests/unit/test_version_consistency.py`.
9. **`package-data` compte :** les quatre packs embarqués (`src/policy_packs/*`) voyagent dans la wheel.
10. **✅ État de la release — 1.5.0 publiée ; 1.6.0 en attente :** le PyPI sert la **1.5.0**, soldant la dette de la fenêtre précédente. La **1.6.0** est déjà dans le `CHANGELOG.md` (`## [1.6.0] - 2026-11-07`) et sur la branche `develop_natan`, mais **sans tag `v1.6.0` et sans publication** — le `pip install --upgrade gitpr-cli` ne livre pas encore la 1.6.0.

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.22)**

| Domaine | v0.0.22 (précédent) | v0.0.23 (actuel) |
|------|-------------------|-----------------|
| **Version GitPR** | 1.5.0 — taguée (`v1.5.0`), **PyPI encore en 1.4.4** | **1.6.0** — dans le changelog et sur `develop_natan`, **sans tag**, PyPI en **1.5.0** |
| **Version Langue** | v0.0.38 | **v0.0.41** — dictionnaires synchronisés |
| **Version des Scripts de Hook** | v0.0.4 | v0.0.4 (inchangée) |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Inchangé |
| **Langues** | 5 langues, 6 dictionnaires | Inchangé |
| **Sous-commandes** | 15 | **16** — 🆕 `seed` ; 4 groupes (baseline 6, metrics 8, policy 7, tests 1) |
| **Flags CLI** | 33 options à la racine | **34** — 🆕 `-sd/--seed` |
| **Configuration SCM 🆕** | Globale par machine (`GITPR_SCM_PROVIDER`, jeton unique) | **Par dépôt et par forge** (`src/scm_settings.py`, 1 slot par forge, migration héritée) |
| **Clôture d'Issue** | GitHub-only | **Multi-forge** — GitLab/Bitbucket implémentent, Azure se dégrade ; registre `issue.yml` avec migration |
| **Seed de Configuration 🆕** | Effet de bord du callback racine | **`gitpr seed`/`--seed`** + wizard de 5 étapes |
| **Couches** | 9 paquets de domaine, 15 cas d'usage | Inchangé — `scm_settings`/`skill_frontmatter` sont bordure |
| **Outils MCP** | 18 outils / 19 ressources / 7 prompts | Inchangé — `get_usage_metrics` a gagné `token_trend` et `latency` |
| **`DEFAULT_CONFIG`** | 58 clés, maintenues à la main | **55 clés, dérivées du schéma** (`seeded`) ; 3 héritées de SCM retirées |
| **Schéma de Config** | 85 `ConfigField` / 17 catégories | **100 / 18** — catégories `smart_excludes` et `check`, 55 `seeded` |
| **i18n (clés par fichier)** | 1698 × 6 | **1772 × 6** — 0 manquantes, 0 orphelines ✅ |
| **Barrière de Mise à Jour** | TTL couru de 1 h, `-u` toujours en ligne, PEP 440 | Inchangé dans le code |
| **Linter** | Regex + secrets + SAST + `findings_out` + baseline-aware | Inchangé |
| **Diff / Smart Excludes** | Filtre pathspec en deux couches | + **garde de hunk binaire** et **100+ motifs** dans 8 familles |
| **Git Hooks** | post-checkout/post-merge/pre-push dans le ledger | Inchangé — `prepare-commit-msg` reste non interactif |
| **Couche SCM** | 4 forges, pagination, `web_links.py` | + **forge/identifiant par dépôt**, issue read-back sur les 4 forges, `IssueInfo.is_open` |
| **Release engine** | Section `[1.5.0]`, tag `v1.5.0`, publication en attente | Section `[1.6.0]` ; **1.5.0 publiée** ; 1.6.0 sans tag ; 🆕 front matter `language:` |
| **Métriques** | Ledger SQLite, coût/qualité/modules/Providers/cycle | + **Token Trend** et **Latency Histogram** dans CLI/TUI/MCP ; `duration_ms` avec fallback |
| **Commit TUI** | Sans garde de TTY ; test intermittent | **Garde de TTY** (`_may_ask_the_user`) et test headless stable |
| **Extension** | Workflow de build/publication | + `dry_run`, garde de tag/version, fail-fast d'identifiant, icon et LICENSE |
| **Documentation** | 50 sujets (48 complets + 2 partiels) | **52 sujets (50 complets + 2 partiels)** — `issue-autoclose` et `marketplace-publishing-guide` nouveaux |
| **Agenda du domaine** | `CONTEXT.md` avec 22 termes | **35 termes** — Binary hunk guard, Token trend, Project configuration seed etc. |
| **CI** | `tests.yml` + `action.yml` + `extension.yml` + `release.yml` | Inchangé en fichiers ; workflows durcis |
| **Suite de Tests** | 3276 scénarios (153 fichiers) | **3422 scénarios (159 fichiers) — +146 scénarios, +6 fichiers** |
| **Stabilité de la Suite** | Blocage à ~33% à cause du `-c` sans TTY ; 1 intermittent | **✅ les deux fermés** — aucun blocage connu |
| **Dépendances** | Aucune nouvelle dépendance Python | Aucune nouvelle |
| **Commits depuis le rapport** | 37 commits + 15 merges | **16 commits, 0 merge** (2026-11-04 → 2026-11-07, sur `develop_natan`) |
| **PRs référencés** | 15 PRs mergés (#235 … #260) | **14 PRs** (#261, #263, #265, #267, #269, #271, #273, #275, #277, #279, #283, #286, #288, #291) — pas encore mergés dans `main` |
| **Index de Mémoire** | 40 patterns | 40 patterns (inchangé) |
| **Rapports de tâche** | 116 claude-code, 31 gemini | **118 claude-code (+12 feat, +1 main), 42 gemini, 19 walkthrough** |
| **Plans de développement** | 152 plans, 12 gemini, 24 surveys | **161 plans, 24 gemini, 34 surveys** |

---

## **🚧 Prochaines Étapes**

* **🆕 Tagger et publier la 1.6.0 :** la section `## [1.6.0]` est dans le `CHANGELOG.md` et la branche `develop_natan` a les 16 commits, mais **il n'existe pas de tag `v1.6.0`**. Créer le tag, laisser `release.yml` publier sur PyPI et confirmer que `pip install --upgrade gitpr-cli` livre la 1.6.0. **C'est la dette de release rouverte.**
* **🆕 Merger `develop_natan` dans `main` :** la fenêtre entière est sur la branche de développement, sans aucun merge. Les 14 PRs référencés doivent atteindre `main` avant (ou avec) le tag de release.
* **🆕 Note de migration du SCM par dépôt :** les clés `GITPR_SCM_PROVIDER`, `GITPR_SCM_TOKEN_ENCRYPTED` et `GITPR_SCM_BASE_URL` ont été retirées du `.env` par la migration automatique. Ceux qui ont automatisé l'écriture de ces clés (scripts de provisioning) doivent savoir qu'il y a désormais un slot par forge — la documentation de `scm-multiforge` couvre la résolution, pas la note d'upgrade.
* **🆕 Traduire le `marketplace-publishing-guide` :** le nouveau sujet n'existe qu'en anglais ; PT-BR, PT-PT, ES et FR restent en attente (le sujet compte comme partiel dans la section documentation).
* **🆕 Publier l'extension sur les marketplaces :** le workflow est prêt et durci, et le paquet a déjà icon/LICENSE/homepage, mais la publication dépend de `VSCE_PAT`/`OVSX_PAT`. D'ici là, l'installation se fait par VSIX local.
* **Stabiliser le `test_headless_full_flow` :** ✅ **terminé** — clic sémantique, timeout de 10 s et prédicat de montage ; 5 rondes au vert.
* **Protéger le `-c` de l'exécution non interactive :** ✅ **terminé** — `_may_ask_the_user()` fait la barrière de la `CommitApp` ; la suite complète a cessé de bloquer à 33%.
* **Couvrir la clôture d'issue sur les autres forges :** ✅ **terminé** — GitLab, Bitbucket et Azure implémentent/se dégradent par contrat (ADR-010).
* **Documenter le nouveau fichier versionné comme changement de flux :** ✅ **terminé** — `gitpr seed`, wizard de 5 étapes et `docs/install-wizard.*`.
* **Section Smart Exclude dans la TUI :** ✅ **terminé** — catégorie `smart_excludes` avec liste des sources et éditeur.
* **Dettes enregistrées dans le plan du config TUI :** ✅ **terminé** — `DEFAULT_CONFIG` dérivé, bannière avec `--init`/`--base`, palette désactivée dans le `LinterApp` et les 9 `GITPR_CHECK_*` dans la catégorie `check`.
* **Note de migration pour l'`action.yml` :** ✅ **terminé** — docs en 5 langues et test de parité dans `tests/unit/test_check_ci_docs_parity.py`.
* **Suivre le fallback ES/FR sur le site :** ✅ **terminé** — `DOC_LANG_PARAMS` et `?lang=` dans les liens d'aide.
* **Relire le `git diff` avec de gros binaires :** ✅ **terminé** — `collapse_binary_hunks()` + smart excludes élargis.
* **Graphiques en ASCII/Textual dans le Dashboard :** ✅ **terminé** — Token Trend et Latency Histogram.
* **Seed de `.gitpr/conf/` local :** ✅ **terminé** — `gitpr seed` couvre config, smart excludes et linter.
* **Publier la 1.5.0 sur PyPI :** ✅ **terminé** — le PyPI sert la 1.5.0.
* **Documenter la rupture de sémantique du `GITPR_SKIP_UPDATE_CHECK` comme note de migration :** le `docs/tutorial/install-from-source` décrit déjà le parser strict, mais les automatisations qui définissaient `=false` en espérant silencier la vérification la voient désormais s'exécuter. **Élément hérité, encore ouvert.**
* **Note de migration du `-c` interactif :** le `commit-message-ia` décrit le nouveau flux ; il vaut la peine de dire explicitement que sans TTY il retombe sur la console et que `--hook` reste le chemin de CI. **Élément hérité, encore ouvert (la garde de TTY a réduit l'impact).**
* **Fournisseur Anthropic Claude :** Support direct de l'API Claude (`claude-sonnet-5`). **Élément hérité, encore ouvert.**
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires. **Élément hérité, encore ouvert.**
* **🆕 Traduire `docs/metricas-telemetria.*` :** la fenêtre a donné de nouveaux graphiques au sujet, mais seule la version anglaise a été mise à jour — PT-BR, PT-PT, ES et FR sont restés en arrière.
* **🆕 Suivre l'hôte self-hosted non reconnu :** la résolution par remote ne reconnaît que les domaines qui contiennent le nom de la forge ; un GitLab/GitHub sur un domaine personnalisé retombe sur GitHub jusqu'à ce que quelqu'un lance `gitpr --init` et enregistre l'override. Il vaut la peine de documenter le symptôme.

### ✅ Terminés dans cette fenêtre (2026-11-04 → 2026-11-08)

* ~~**La forge et le jeton étaient globaux de la machine**~~ 🆕 — PR #288 (`ee012fa`) : `src/scm_settings.py`, un slot d'identifiant par forge, migration héritée idempotente, wizard et TUI ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)). **+24 scénarios + extensions.**
* ~~**La clôture d'issue était GitHub-only**~~ 🆕 — PR #286 (`fe35c96`) : read-back sur les quatre forges, registre `issue.yml` avec migration, `IssueInfo.is_open` ([ADR-010](docs/adr/0010-multiforge-issue-autoclose.md)).
* ~~**La configuration locale naissait comme effet de bord**~~ 🆕 — PR #277 (`d934f21`) : `gitpr seed`/`--seed`, `--force`/`--all` et wizard de 5 étapes.
* ~~**La TUI n'avait pas de section Smart Excludes**~~ 🆕 — PR #263 (`b3e547d`) : catégorie `smart_excludes`, liste des sources et éditeur du projet.
* ~~**Le dashboard n'avait que des chiffres agrégés**~~ 🆕 — PR #275 (`67a9e77`) : Token Trend et Latency Histogram dans le CLI, la TUI et le MCP.
* ~~**Les binaires sans NUL inondaient le prompt de `U+FFFD`**~~ 🆕 — PR #271 (`988dbbc`) : `collapse_binary_hunks()` + 100+ motifs de smart excludes.
* ~~**Les liens d'aide pointaient vers `blob/main` et cassaient ES/FR**~~ 🆕 — PR #273 (`ffa9b9e`) : `doc_url()` avec `?lang=` et lecture dynamique de `CURRENT_LANG`.
* ~~**`gitpr -c` bloquait en CI sans terminal**~~ 🆕 — PR #283 (`869e2a1`) : garde de TTY via `_may_ask_the_user()`.
* ~~**`test_headless_full_flow` était intermittent**~~ 🆕 — PR #279 (`f57e869`) : `Button.press()`, prédicat de montage et timeout de 10 s.
* ~~**`DEFAULT_CONFIG` était une copie manuelle du schéma**~~ 🆕 — PR #265 (`74f8c38`) : dérivé de `ConfigField.seeded`, catégorie `check` et dettes du config TUI fermées.
* ~~**La skill de release suivait toujours la langue de l'interface**~~ 🆕 — PR #291 (`b277632`) : front matter `language:` lu par `src/skill_frontmatter.py`.
* ~~**L'extension n'avait pas de métadonnées de marketplace**~~ 🆕 — PR #269 (`613af18`) : icon, LICENSE, homepage, `workflow_dispatch`/`dry_run` et fail-fast d'identifiant.
* ~~**La suggestion de reviewers inondait le terminal d'avertissements de blame**~~ 🆕 — (`405dc7c`) : `files_with_blame`/`files_without_blame` et une seule ligne de résumé.
* ~~**La note de migration de l'`action.yml` n'était pas vérifiée**~~ 🆕 — PR #267 (`d1a06f3`) : test de parité de 5 langues et 7 sections.
* ~~**La 1.5.0 était taguée mais non publiée**~~ — le PyPI sert désormais la **1.5.0**.

---

**Rapport généré le :** 2026-11-08  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
