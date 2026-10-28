# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.20 (2026-10-28)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.20) :**
- **La barrière de mise à jour a cessé de perdre les releases publiées après la première exécution du jour :** le cache PyPI était indexé par la **date locale**, donc une release publiée à 10h45 restait invisible jusqu'à minuit pour qui avait déjà lancé la CLI à 07h54. Le cache est devenu une **fenêtre glissante de 1 heure** avec un `checked_at` ISO, des écritures atomiques (`os.replace`) et une validation à la lecture — un fichier corrompu, un horodatage dans le futur ou un reste de l'ancien format expire simplement. `gitpr -u`/`--update` **ignore désormais le cache** et interroge PyPI à chaque invocation, comme la documentation le promettait déjà.
- **La comparaison de versions est passée à PEP 440 :** `packaging.version.Version` a remplacé l'arithmétique de tuples — elle corrige l'ordre des segments à plusieurs chiffres (`1.4.10 > 1.4.9`), empêche une pré-release distante de bloquer une installation stable dans un `pip install --upgrade` qui refuserait de l'installer, et traite une version illisible d'un côté ou de l'autre comme **inconnue** plutôt que comme « plus récente ».
- **La barrière atteint désormais les sous-commandes — mais seulement quand l'exécution peut demander :** `gitpr release`, `fix`, `review-pr` et les autres passent par la barrière quand il y a un TTY des deux côtés. CI, pipes, hooks, `--quiet`/`--hook`/`--mcp` et `-h`/`--help` ne sont **jamais** bloqués ; l'aide de la sous-commande est détectée dans l'**`argv` brut**, parce que Click vide les arguments du contexte avant le callback du groupe.
- **Les 7 échecs de `tests/badge/test_badge_paths.py` ont enfin été clos :** la validation du jeton du Provider est passée de l'extérieur à **l'intérieur** du bloc `if check_remote_state:` de `publish_pull_request_use_case` — le flux `--no-edit` ne retourne plus un `auth_required` en faux positif. `pr_url`/`pr_number` sont désormais lus avec un `getattr` défensif, et la fixture a gagné `token="ghp_fake"` et `number=1`. **La suite est passée de 12 à 5 échecs connus** — dette ouverte pendant trois fenêtres consécutives.
- **La sémantique de l'échappatoire a changé (rupture déclarée) :** `GITPR_SKIP_UPDATE_CHECK` est désormais lue comme un **booléen strict** — seuls `1`, `true`, `yes`, `on` et `y` (insensibles à la casse) désactivent la vérification. `false`, `0`, `no`, `off`, `n` et une valeur vide **gardent** la barrière active.
- **La dette i18n n'a pas été payée :** les dictionnaires restent à **1620 clés** (×4) et **1618** (`es.json`, `fr.json`), avec **8 clés utilisées dans le code sans traduction** et **2 orphelines** — exactement les mêmes chiffres que la fenêtre précédente.

- **Version actuelle :** 1.4.3 (**bump commité et mergé dans le PR #228, pas encore publié** — PyPI sert la **1.4.2**, il n'existe pas de tag `v1.4.3` et la section `[1.4.3]` de `CHANGELOG.md` est **dans la working tree**, non commitée)
- **Version des dictionnaires de langue :** v0.0.35 (inchangée)
- **Version des scripts de hook :** v0.0.4 (inchangée)
- **Publication :** PyPI (`pip install gitpr-cli`) — canal binaire supprimé
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licence :** LGPL-2.1
- **Langues prises en charge :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues, 6 dictionnaires)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **CLI Framework :** Click (pour les commandes, flags et formatage du terminal) — **15 sous-commandes** (`badge`, `baseline`, `check`, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `split`, `tests`). Quatre d'entre elles sont des **groupes** : `baseline` (6 sous-commandes), `metrics` (8), `policy` (7) et `tests` (1). 🆕 **Toutes passent par la barrière de mise à jour quand l'exécution est interactive** (voir §3).
* **UI/Terminal :** Textual — TUI pour le chat interactif, l'édition d'issues, l'écran d'aide, le tableau de bord de métriques, le PR Publisher (avec la modale de progression réutilisée dans le merge), les erreurs du linter (`LinterApp`), la configuration (`ConfigApp`), le modal `NoticeScreen`, la visite `gitpr demo` (`src/ui/demo/`) et l'assistant de migration du ledger (`src/ui/metrics_migration_app.py`).
* **Architecture en Couches :** `src/domain/` (règles pures, sans I/O) + `src/application/use_cases/` (orchestration, **15 cas d'usage**) + présentation (CLI/TUI). **9 paquets de domaine** : `baseline`, `check`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`.
* **Extension Éditeur :** `extensions/vscode/` — client MCP TypeScript mince pour **VS Code et Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). Il ne contient aucune règle métier.
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API, des jetons GitHub et des jetons SCM des forges.
* **Configuration :** `python-dotenv`, `pyyaml` (linter statique) + son propre schéma déclaratif (`src/config_schema.py`, **80 `ConfigField`** et **16 catégories**). 🆕 `packaging` alimente aussi la comparaison de versions de la barrière (PEP 440), en plus de la compatibilité des policy packs.
* **Fournisseurs IA :** Intégration via le SDK officiel Google GenAI, le SDK OpenAI (DeepSeek) et le SDK OpenAI (Ollama local). Défauts configurés : `gemini-pro-latest` / `gemini-flash-lite-latest` et `deepseek-v4-pro` / `deepseek-v4-flash`.
* **APIs de Forge :** `requests` (REST) — abstraction multi-forge dans `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py` ; `src/github_api.py` conservé comme shim déprécié.
* **Linter / SAST :** Regex YAML + pont Checkstyle + ruleset de secrets intégré (`src/security_ruleset.py`) + 4 ponts dans `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), avec le modèle normalisé dans `src/domain/linter/sast_finding_mapper.py` et le constat canonique dans `src/domain/finding/`.
* **Infrastructure CI :** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) et `masking.py` (masquage des identifiants avant la sérialisation de tout artefact).
* **Interne de Git :** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py`, `risk_history_reader.py` et `test_matcher.py`.
* **Persistance locale :** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) avec WAL, `PRAGMA user_version`, clés UUID et transport par bundle.
* **Réseau :** 🆕 la barrière de mise à jour a quitté l'`urllib` brut et utilise désormais `src/net.bounded_urlopen` (`UPDATE_FETCH_HARD_TIMEOUT = 3.0`), qui borne le **temps réel, DNS inclus** — le `timeout` d'`urlopen` ne couvrait pas la résolution de noms, qui sous Windows peut bloquer pendant des minutes.
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 outils annotés**, **19 ressources**, 7 prompts.
* **Tests :** Pytest + `unittest.mock` (**140 modules de test** — 53 à la racine, 12 dans `tests/application/use_cases/`, 10 dans `tests/fix/`, 9 dans `tests/scm/`, 7 dans `tests/badge/`, 6 dans `tests/demo/`, 6 dans `tests/split/`, 5 dans `tests/domain/baseline/`, 4 dans `tests/domain/policy/`, 4 dans `tests/review/`, 4 dans `tests/infrastructure/linter/external/`, 3 dans `tests/domain/check/`, 3 dans `tests/infrastructure/ci/`, 2 dans `tests/domain/risk/`, 2 dans `tests/domain/tests_generation/`, 2 dans `tests/integration/`, 2 dans `tests/infrastructure/git/`, 2 dans `tests/domain/linter/`, 1 dans `tests/domain/finding/`, 1 dans `tests/domain/mentor/`, 1 dans `tests/domain/pr/`, 1 dans `tests/infrastructure/baseline/` —, **2996 scénarios collectés** 🆕) + tests e2e du serveur MCP via un vrai subprocess (JSON-RPC stdio) + fixtures de dépôt git réel (`tests/fix/`, `tests/split/`, `tests/test_full_diff_encoding.py`) + une garde d'isolation réseau (`tests/demo/conftest.py`, `tests/integration/conftest.py`). L'extension a sa propre suite Mocha (7 tests contre le vrai serveur MCP).
* **Empaquetage :** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}` ; packs embarqués via `package-data` ; `packaging>=23.0`. L'extension est empaquetée séparément (`vsce package`, VSIX).
* **CI/CD :** GitHub Actions — `pr-review.yml` + `action.yml` (appelle `gitpr check`) + `tests.yml` (matrice Python 3.10/3.13) + `extension.yml` (build, test et empaquetage du VSIX ; publication vers Marketplace/Open VSX sur les tags `ext-v*`).

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec le LLM en demandant une sortie strictement JSON.
* **Map-Reduce (Diffs Géants) :** Lorsque le diff dépasse ~90k tokens, le divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce). Prend en charge les PRs, commits et Issues. Le `provider`/`model` sont agrégés entre les lots et `meta`/`modules` sont transmis à `log_command_metric`.
* **Tokenizer Local :** `tokenizer.json` pour une estimation précise des tokens avant l'envoi à l'IA.
* **Estimation des Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()` avec fallback vers le tokenizer local.
* **Optimisation Native de Git :** Flags `-U1`, `-w`, `-M`, `-B` sur les commandes `get_git_diff` et `get_git_full_diff`.
* **`get_split_diff()` :** Le diff propre au `split`, avec `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **délibérément sans** réutiliser `get_git_diff()`.
* **Pre-Save (`--pre-save`) :** Flag caché de debug qui enregistre le payload complet (instruction système + prompt) en JSON avant chaque appel à l'IA.
* **Smart Excludes à Deux Couches :** Couche globale (`~/.gitpr/conf/`) + couche locale du projet (`./.gitpr/conf/`), fusionnées au runtime. Auto-seed du fichier local à la première exécution. `_load_smart_excludes()` accepte `force=`.
* **Métriques avec Suivi du Temps :** `log_command_metric()` dans tous les flux, avec `meta`, `modules` et `duration_ms`.
* **Résolution Centralisée de la Sortie :** `resolve_output_path()` — par défaut dans `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`) :** `gitpr --init` — détecte la forge depuis le remote origin, demande les extras propres à chaque forge, valide le jeton et persiste **uniquement en cas de succès**.
* **Template de Skill de Release (`ensure_release_skill_template()`) :** Télécharge `templates/gitpr.release.*.md` à la première utilisation de `gitpr release`.
* **Garde contre les Skills Corrompus :** `_CORRUPTED_PR_SKILL_ARTIFACT` documente le fragment JSON qui a fui dans le template `pt_br`, et `get_skill_context()` le supprime **en mémoire** quand `action_type == "pr"`.
* **Registre de Skills Partagé :** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` dans `src/config.py`.
* **Moteur de Review avec Périmètre de Cache (`generate_pr_content`) :** `cache_scope` et `store_diff` ; le `cache_scope` porte aussi le pack de policy actif.
* **Trailer de Co-paternité :** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, préserve les trailers de tiers.
* **Capture de Sortie Git Blindée :** les **10** appels à `subprocess.run` dans `src/cache.py`, `src/core.py`, `src/issue_engine.py` et `src/main.py` qui capturent la sortie de git utilisent `encoding="utf-8"` **avec** `errors="replace"`.
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
* **Routage des Commandes :** Gère tous les flags et les **15 sous-commandes** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline` et `check`.
* **Comportement par Défaut :** Exécuter `gitpr` sans flags ouvre la TUI du PR Publisher.
* **Flags (32 options Click à la racine — inchangées dans cette fenêtre) :** `--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` etc.
* **🆕 Barrière de Mise à Jour dans les Sous-commandes :** le callback du groupe revenait avant la barrière racine, donc `gitpr release`, `fix`, `review-pr` et les autres n'étaient **jamais** bloqués par une nouvelle release. La barrière s'exécute désormais avant le `return`, conditionnée par `may_ask and not update and not help_flag and not _subcommand_help_in_argv()`. `_may_ask_the_user()` exige un **TTY des deux côtés** — CI, pipes, cron, hooks et le serveur MCP restent libres.
* **🆕 Aide de la Sous-commande Lue dans l'`argv` Brut :** `_subcommand_help_in_argv()` existe parce que Click **vide les arguments du contexte** avant que le callback du groupe ne s'exécute : à ce moment, `gitpr release -h` est indistinguable de `gitpr release`. Le balayage échoue ouvert, ne coûtant au plus qu'une barrière sautée.
* **Sous-commande `check` — 8 options :** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on …`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` et `--policy <name>@<version>`.
* **Groupe `baseline` — 6 sous-commandes :** `create`, `show`, `validate`, `update`, `suppress` et `unsuppress`.
* **Groupe `policy` — 7 sous-commandes :** `list`, `show`, `validate`, `use`, `off`, `install` et `init`.
* **Groupe `metrics` — 8 sous-commandes :** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` et le `hook-event` caché.
* **Sous-commande `risk` — 4 options :** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Sous-commande `mentor` — 2 options :** `--finding`, `--provider`.
* **Variables d'Environnement (58 clés dans `DEFAULT_CONFIG`) :** les quatre `GITPR_BASELINE_*`, les deux de mentor, les deux de policy et `GITPR_RISK_INCLUDE_IN_REVIEW`. **Hors `DEFAULT_CONFIG` :** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY`, les **9 `GITPR_CHECK_*`** et `GITPR_SKIP_UPDATE_CHECK` (🆕 désormais un booléen strict).
* **Aide Contextuelle :** `gitpr -h --explain` rend ; `HELP_MAP["explain"]` et `HELP_PRIORITY["explain"]` pointent vers `docs/explain-pr`, et `gitpr tests`/`gitpr tests generate` ont leur propre `epilog=`.
* **Couche d'Écriture du `.env` :** `read_env_file_values()` lit **uniquement le fichier** via `dotenv_values` ; `save_config_values()` avec `set_key` ; `remove_config_value()` avec `unset_key`.
* **Barrière Baseline dans le Flux :** `main.py` résout la barrière de baseline avant le lint, le review, le full review, l'audit de fichier, le review distant et le risque.
* **Publication Directe Refactorisée :** `_publish_pr_directly` (`--no-edit`) délègue à **`publish_pull_request_use_case`** — le même chemin que l'outil MCP `publish_pull_request`.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)**

* **Interface Interactive Complète :** TUI pour réviser, éditer et publier des Pull Requests directement depuis le terminal.
* **7 Écrans Modaux :** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` et `NoticeScreen`.
* **Badge dans le Corps du PR :** `attach_pr_badge()` s'exécute dans `src/main.py` juste après que `pr_data` est complet — **point d'injection unique**.
* **`_attach_reviewers` avec résolution :** Résout ce que l'utilisateur a saisi **avant** l'envoi, signale ce qui a été écarté et, sur un `422` de lot, **réessaie un par un**.
* **Reviewers Suggérés :** Interroge la forge pour obtenir des reviewers suggérés ; `_reviewer_suggestion_view()` assemble les `resolutions` via `resolve_candidates`.
* **Bindings :** F1 (Aide), F2 (Enregistrer .md local), F3 (Publier via la forge), Échap (Quitter).
* **Flux d'Auto-Commit :** Linter → message IA → confirmation → commit → push → publie le PR.
* **Modale de Progression dans le Merge :** `CommitProgressScreen` avec un `title` optionnel et `mark_finished(show_close=False)` ; le succès affiche `✅ PR #N merged successfully!`, les conflits et erreurs inattendues passent par `_on_merge_failure`.
* **Gestion de PR Existant / Auto-Upstream / Flux de Merge** — inchangés (`GITPR_AUTO_MERGE`).

### **5. Module API GitHub (`src/github_api.py`)**

* **Shim Déprécié :** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` et les autres fonctions délèguent à `src/infrastructure/scm/github_provider.py` ; il émet un `DeprecationWarning` et conserve les tuples legacy `(ok, data, status)` — aucun nouveau code ne doit l'importer.

### **6. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le diff sans dépenser de quotas d'IA.
* **Règles YAML :** Lit `.gitpr.linter.yml` (créé via `--skill`).
* **Plugins de Linter :** Règles supplémentaires depuis `~/.gitpr/plugins/linter/*.yml`.
* **Wildcard d'Extension :** `extensions: ["*"]` signifie **tous** les fichiers, y compris ceux sans suffixe et les dotfiles.
* **Ruleset de Secrets Intégré :** `load_linter_rules()` fusionne `src/security_ruleset.py` **après** les règles du projet et des plugins. Sept règles, toutes `extensions: ["*"]` : cinq `error` et deux `warning`. L'alerte **ne répète jamais la valeur**.
* **Couche des Policy Packs :** `load_linter_rules()` applique la fusion en couches du pack actif et **écrase les sévérités**.
* **Constats Structurés (`findings_out`) :** en plus des chaînes d'alerte, le moteur retourne les constats normalisés du type canonique `src/domain/finding/`.
* **Ponts SAST Opt-In :** Semgrep, Gitleaks et Bandit tournent sur le chemin de fichier entier **et** sur le chemin de diff, avec la dédup `[Gitleaks + Regex]`, le masquage des valeurs (`AKIA****`) et un avertissement lorsque l'outil est activé mais absent du `PATH`.
* **`skip_external` :** `parse_diff_and_lint(..., skip_external=True)` dans le flux distant.
* **Rapport Consolidé :** `generate_linter_report_content()` consolide les erreurs regex + externes dans `.gitpr/reports/linter/` — généré uniquement en cas de violations.
* **Sensible à la baseline :** les alertes sont annotées avec le statut de baseline avant de devenir un rapport.

### **7. Sécurité et Authentification (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Chiffrement :** Génère la clé maîtresse `secret.key` dans `~/.gitpr/`.
* **Protection des Jetons :** `encrypt_data`/`decrypt_data` pour les clés d'IA, le PAT GitHub et les jetons SCM des forges.
* **Validation Multi-Forge :** `validate_or_request_scm_token(provider, repo_display)` — 401 → boucle de réauthentification préservant le brouillon.
* **Secrets dans la TUI de Configuration :** Les champs `KIND_SECRET` sont édités masqués, **n'affichent jamais** la valeur en clair et sont chiffrés avec Fernet avant d'être écrits.
* **Analyse de Secrets dans le Flux :** Le linter — qui tourne dans le hook pre-commit — est une barrière d'identifiants avec des règles identiques sur chaque machine.
* **Masquage dans la CI :** `src/infrastructure/ci/masking.py` supprime les clés privées, l'ID/secret de clé d'accès AWS, les jetons GitHub, Google, OpenAI et Slack, `Bearer`, le basic-auth dans l'URL et les affectations génériques de mot de passe **avant** la sérialisation de l'artefact. L'extension a un miroir en TypeScript (`masking.ts`).
* ⚠️ **Lacunes de couverture confirmées encore ouvertes :** la règle générique exige toujours des guillemets après le séparateur, donc `API_KEY=abc123` passe encore entre les mailles ; les préfixes `ASIA…`, `github_pat_…` et `xoxc-`/`xoxd-` restent hors du ruleset **du linter** ; et il n'y a pas de frontière gauche sur le nom de la clé.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI comme Source Unique :** `get_latest_remote_version()` interroge `https://pypi.org/pypi/gitpr-cli/json` et écrit le cache. Elle accepte `force=True`, utilisé par `--update` pour aller **toujours** au réseau.
* **🆕 Fenêtre Glissante de 1 Heure (`UPDATE_CACHE_TTL_SECONDS = 3600`) :** le cache n'est plus « valide tant que la date locale est aujourd'hui ». Le document est désormais `{"checked_at": <ISO>, "version": ...}` ; `_cache_is_fresh()` accepte un âge dans `[0, 3600)` secondes. Une release publiée à 10h45 est désormais vue au plus une heure plus tard — avant, elle restait invisible jusqu'à minuit.
* **🆕 Validation à la Lecture :** un vrai `dict`, un horodatage analysable, une version chaîne non vide. Un horodatage **dans le futur** (une horloge revenue en arrière) compte comme expiré, et l'ancien format avec `date` est traité comme expiré et réécrit — sans migration manuelle.
* **🆕 Écriture Atomique :** `tempfile.NamedTemporaryFile` dans le même répertoire + `os.replace`, avec nettoyage du fichier temporaire en cas d'échec. Un `home` en lecture seule ne masque plus la version récupérée.
* **🆕 Fetch Blindé (`bounded_urlopen`) :** un corps non-JSON et un payload sans version sont **rejetés avant d'entrer dans le cache** — un portail captif ou un mirror malformé n'est pas persisté. Un plafond de 3 s de temps réel, DNS inclus.
* **🆕 Comparaison PEP 440 :** `_coerce_version()` utilise `packaging.version.Version`, en tolérant le `v` initial et les suffixes PEP 440 ; `parse_version()` conserve le contrat de tuple pour les appelants externes. `_is_newer()` **ignore une pré-release distante** sur une installation stable et traite une version illisible d'un côté ou de l'autre comme inconnue.
* **Barrière Obligatoire (`enforce_update_required()`) :** Retourne `True` (après avoir affiché les deux versions et la commande pip) lorsque la version publiée est plus récente ; `False` lorsque tout est à jour, lorsque la version distante est **inconnue (hors ligne)** ou lorsque la vérification est désactivée.
* **🆕 Sémantique Stricte de l'Échappatoire :** `GITPR_SKIP_UPDATE_CHECK` est analysée contre `_SKIP_TRUTHY_VALUES` (`1`, `true`, `yes`, `on`, `y`) ; avant, toute valeur non vide — y compris `false` et `0` — désactivait la vérification.
* **`check_and_update()` :** Se contente d'interroger et d'**informer**, n'installe jamais.
* **Versionnage Centralisé :** `__version__` (**1.4.3** 🆕), `__lang_version__` (**v0.0.35**), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* **⚠️ État de la Release :** `__version__` est déjà **1.4.3** et le commit a été mergé (PR #228), mais il n'existe **aucun tag `v1.4.3`**, PyPI sert la **1.4.2** et la section `[1.4.3]` de `CHANGELOG.md` est **dans la working tree**. Les installations en 1.4.1/1.4.2 ne reçoivent le correctif de la barrière que via un `pip install --upgrade gitpr-cli` manuel — leur ancienne logique continue de répondre depuis le cache quotidien.

### **9. Interface de Chat Interactive (`src/ui/chat_app.py`)**

* **TUI Complète :** Historique des messages, saisie multi-ligne, barre d'état avec bindings visibles. `ENABLE_COMMAND_PALETTE = False`.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique persisté par branche, avec continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear`. `/tests` (et les alias localisés) délègue au **même cas d'usage** que la CLI.
* **Auto-Patching (F5), Rafraîchissement du Diff (F2), Export de Session (F6).**
* **Extracteur Partagé :** F5 et `ctrl+s` appellent `patch_extractor.extract_code_blocks()` — comportement visible identique, sans logique dupliquée.

### **10. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système à la première exécution et l'enregistre dans `GITPR_LANG` ; le répertoire de profil est créé **avant** la persistance.
* **5 Langues, 6 Dictionnaires :** en_us (défaut/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Fichiers Versionnés :** `__lang_version__` (**v0.0.35**) contrôle la mise à jour des packs (`langs/*.json`).
* **Couverture — dette inchangée dans cette fenêtre :** **1620 clés** dans `pt_br`, `pt_pt`, `es_es` et `fr_fr`, mais **1618** dans `es.json` et `fr.json`. Le code utilise **1626** clés : **8 n'existent dans aucun dictionnaire** (messages de `publish_pull_request` et descriptions des outils MCP `run_check`/`publish_pull_request`) et **2 anciennes clés sont devenues orphelines**. ❌
* **Photo de l'Environnement (`AMBIENT_ENV_KEYS`) :** `frozenset(os.environ)` capturé **immédiatement avant** le `load_dotenv()` au niveau du module.
* **Cache avec Indexation par Langue :** Les réponses IA en cache incluent la langue courante dans le keying MD5.
* **Source de Traduction en Phase :** une nouvelle clé doit exister dans le code, dans `langs/pt_br.json` (liste maîtresse), dans les dicts FR/ES de `scripts/sync_all_langs.py` et dans les valeurs curatées de `scripts/fix_mangled_i18n_keys.py`.

### **11. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA avec des caractères braille et des mots de « réflexion ».
* **263 entrées par langue :** Synchronisées entre les 5 langues. `_load_thinking_words()` / `reload_thinking_words()` acceptent `force=`.

### **12. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Pris en Charge :** Google Gemini, DeepSeek et Ollama (local), avec un primaire/secondaire par fournisseur.
* **Mode JSON & Paramètres Déterministes :** `temperature=0.0` et `top_p=0.1` ; fallback automatique entre les fournisseurs configurés.
* **Timeout IA :** défaut de **180s** (`GITPR_AI_TIMEOUT`).
* **Appels avec `quiet` :** la release et les nouvelles fonctionnalités transmettent `quiet` jusqu'à la couche IA, pour que le spinner ne corrompe jamais une sortie `--format json`.
* **Un appel à la fois, avec cache :** mentor et explain réutilisent le cache MD5 par prompt.
* **Payload Déjà Parsé :** `parse_explain_payload()` et `generate_test_file` acceptent le **dict** livré par le fournisseur, avec un fallback vers la chaîne JSON brute (anciennes réponses du cache).

### **13. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Keying par hash MD5 du diff et du prompt, avec indexation par langue.
* **Sélection du Dernier Review (`resolve_last_review()`) :** Choisit l'enregistrement `review`/`fullreview` **le plus récent** pour un couple repo+branche — c'est la porte d'entrée de `gitpr fix` et de `gitpr mentor`.
* **Diff Révisé (`reviewed_diff`) :** Champ en tête d'enregistrement qui conserve le diff effectivement révisé.
* **Télémétrie et Durée :** Persistance de `duration_ms` et `meta_raw`.
* **Lecture pour le Dashboard :** `scan_cache_files_for_dashboard()` est un **pont de pré-migration**.
* **Effet de l'Assainissement du Skill :** comme `get_skill_context()` supprime le fragment corrompu du prompt de PR, la clé MD5 change et les anciennes réponses contaminées ne sont plus réutilisées.
* **`git config` Décodé en Sécurité :** les lectures de `user.name`/`user.email` utilisent `encoding="utf-8"` + `errors="replace"`.

### **14. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff actuel, Historique de la branche (`-ht`) et Archéologie par Blame (`-b`).
* **Publication Multi-Forge :** F3 crée l'issue sur la forge **configurée** (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — Azure DevOps lève `ScmNotSupportedError`.
* **Map-Reduce pour les Issues :** Le contexte au-dessus de ~90k tokens est découpé et unifié.
* **Gestion du 401 :** Signalisation de réauthentification sans fermer l'application.
* **`git remote -v` Décodé en Sécurité :** l'appel utilise `encoding="utf-8"` + `errors="replace"`.

### **15. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Suit l'évolution et la paternité historique avec classification des commits (`ORIGIN` vs `REFACTORING`).
* **Métriques de Blame :** Événements via `log_blame_metric()` avec la profondeur et le nombre de commits analysés.
* **Il alimente désormais aussi le risque :** le lecteur d'historique du score (`risk_history_reader.py`) utilise la même fenêtre de 90 jours et classe bugs/reverts par motif, sans appeler l'IA.

### **16. Serveur MCP et Invocation CLI Directe (`src/mcp_server.py`)**

* **18 Outils MCP Annotés :** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates`, `review_remote_pr`, `get_baseline_status`, `get_usage_metrics`, `run_check` et `publish_pull_request`.
* **Paramètres Opt-In dans le Contrat :** `review_code` et `full_review` acceptent `mentor: bool = False` ; `generate_pr_description` accepte `explain: bool = False`. Ils sont **purement par paramètre** — jamais OR'és avec les défauts du `.env`.
* **19 Ressources + 7 Prompts Templatisés :** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Registre de Skills Aligné :** `mcp_server.SKILL_FILES` a **11 entrées** (`tests`, `explain` et `mentor` inclus) ; `TestSkillRegistryAgreement` passe.
* **Invocation CLI Directe :** `gitpr-mcp --tool <name> [--tool-args '<json>']` invoque n'importe quel outil sans démarrer le serveur stdio ; `gitpr-mcp --list` affiche le registre complet en JSON.
* **Isolation du Stdout Réel :** `_write_real_stdout()` écrit dans le `sys.__stdout__` original, avec un fallback vers `buffer` ou un ré-encodage avec `errors='replace'` sur les code pages Windows legacy.
* **Offload de l'Event Loop :** Décorateur `_offload` (`anyio.to_thread.run_sync`) sur chaque outil — l'ordre des décorateurs compte (`@mcp.tool` **au-dessus** de `@_offload`).
* **Échecs comme Erreurs Structurées :** les nouveaux outils retournent un échec **comme donnée** au lieu de lever.
* **Métriques MCP :** `run_check` et `publish_pull_request` écrivent des lignes locales (`mcp:check`, `mcp:publish_pr`).
* **Le serveur MCP n'est jamais bloqué par la barrière :** `--mcp`/`gitpr-mcp` figure sur la liste d'exemptions depuis la première version de la barrière.

### **17. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`)**

* **Périmètre par Dépôt :** Étiquette `📁 Repository: owner/repo` et filtrage strict par projet.
* **Scan Asynchrone avec Overlay :** thread worker en arrière-plan avec `ProgressBar` ; les tests attendent `workers.wait_for_complete()` avant d'asserter.
* **Lecture du Ledger :** le tableau de bord interroge `telemetry.db` sur le thread worker et rend les cinq sections (coût, qualité, modules, fournisseurs, cycle) avec les mêmes renderers que la CLI.
* **Assistant de Migration (`src/ui/metrics_migration_app.py`) :** propose d'importer les fichiers legacy, d'importer **et** reconstruire depuis le cache IA, ou « pas maintenant ».
* **Export Local :** CSV/JSON dans `./.gitpr/metrics/export/` — `.gitpr/metrics/` est dans `.gitignore`, mais les **34 artéfacts déjà suivis restent suivis** (le gitignore ne désuit pas).

### **18. Système de Métriques et Télémétrie (`src/metrics.py`)**

* **Périmètre par Dépôt :** tous les événements indexés par `repo_name`, résolu par `src/infrastructure/git/identity.py`.
* **Événements de Hook, Linter et Blame :** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. `gitpr check` écrit `command="check"` avec `findings_total`/`findings_errors` et le code de sortie.
* **Export et Nettoyage :** `gitpr metrics export` (CSV/JSON), `prune`, `purge` avec confirmation interactive, `bundle`/`merge` pour transporter et fusionner des fenêtres.

### **19. Synchronisation de la Langue des Git Hooks**

* **Versionnage Indépendant :** `__scripts_version__` (**v0.0.4**, inchangée).
* **Templates réécrits dans la fenêtre v0.0.17 :** `post-checkout`, `post-merge` et `pre-push` appellent `gitpr --quiet metrics hook-event <name>`, protégés par `command -v gitpr` et `|| true`.
* **Mapping de Suffixes (`HOOK_SCRIPT_SUFFIXES`) :** les codes UI (`es_es`, `fr_fr`) traduits vers les suffixes publiés (`.es`, `.fr`).
* **Choix vs. État (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`) :** permet de **détecter un changement de langue**.
* **`effective_hook_lang()` :** résout la langue effective ; `--lang` n'est plus ignoré sur ce chemin.
* **Saut de la Source de Merge :** `prepare-commit-msg` saute les sources `message|merge|squash|commit`.
* **Les hooks s'exécutent avec `--quiet` et `--hook`, donc ils ne sont jamais bloqués par la barrière de mise à jour.**

### **20. Pont de Linters Externes et Assistant Interactif (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistant `--linter-setup` :** wizard avec des presets numérotés (PHP_CodeSniffer, ESLint, Stylelint) et injection du bloc `external_linters`.
* **Presets Distants :** `templates/gitpr.linter-presets.json` avec une chaîne de fallback local → téléchargement → obsolète → embarqué.
* **TUI d'Erreurs du Linter :** `src/ui/linter_app.py` (Textual) affiche les erreurs critiques et les avertissements ; en mode hook/quiet, imprime et appelle `sys.exit(1)`.
* **Rapport Markdown :** consolidé dans `.gitpr/reports/linter/` uniquement en cas de violations.
* **La Couche SAST est une Sœur, pas un Remplacement :** les ponts Semgrep/Gitleaks/Bandit alimentent le **même** pipeline de rapport — et désormais aussi `gitpr check`.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstraction Unique (`ScmProvider` ABC) :** `base.py` définit le contrat (dataclasses `RepoRef`, `PullRequestDraft` etc. et `ScmProviderError(provider, http_status, message)` — `http_status` 0 = échec réseau).
* **Registre et Factory :** `resolve_scm_provider()` sélectionne par `GITPR_SCM_PROVIDER` (défaut `github`) ; `detect_provider_from_remote()` identifie la forge depuis l'URL du remote origin.
* **`list_pull_requests(repo, state, since, until)` — paginé et canonique :** les quatre providers traduisent les états canoniques (`open`/`merged`/`closed`/`all`) dans le vocabulaire natif, paginent sur le curseur de chaque API et filtrent par une fenêtre de dates inclusive.
* **Pourquoi le filtre est par date d'état :** trois des quatre forges retournent quelque chose de plus large pour un PR mergé, donc chacune filtre sa propre réponse par rapport à la date qu'implique l'état.
* **`supports_merged_dates` :** attribut de classe, `False` sur Bitbucket Cloud, qui publie `merged_at` **vide**.
* **`web_links.py` :** fonctions pures (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) qui normalisent n'importe quel format de remote.
* **`get_pull_request(repo, pr_id)` — méthode concrète de l'ABC :** le défaut lève `ScmNotSupportedError` et chaque forge l'implémente.
* **En-têtes GitLab Synthétisés :** `changes[].diff` est un hunk lâche ; `old_path`/`new_path` construisent les en-têtes `diff --git a/… / --- / +++`, en respectant `/dev/null`. Et `overflow: true` **lève** au lieu de réviser un diff tronqué à moitié.
* **`request_pull_request_reviewers` retourne `list[str]` (rupture de contrat) :** retourne les logins **effectivement attachés**, lus dans le corps du `201`. Les sous-classes tierces doivent être mises à jour — **élément hérité, toujours non documenté.**
* **Helpers GitHub en Lecture Seule :** `get_commit_author_login` et `get_user_login`.
* **Fail-Fast par Forge :** Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` ; Bitbucket exige `GITPR_SCM_USERNAME` ; `create_issue` sur Azure lève `ScmNotSupportedError`.
* **Publication de Release :** `provider.create_release()` utilisé par `gitpr release --publish`.
* **Publication de PR Partagée :** `src/application/use_cases/publish_pull_request.py`, utilisé par `--no-edit` et par l'outil MCP. Il valide le titre/le corps, **exige `provider.token` uniquement quand `check_remote_state=True`** 🆕, vérifie optionnellement l'existence d'un PR et d'une branche non poussée, masque les messages d'erreur et retourne `PublishPrOutcome`. Les champs `url`/`number` du résultat sont lus avec un `getattr` défensif 🆕.
* **Artefacts :** Glossaire + ADR-001/ADR-005 ; la famille `docs/scm-multiforge.*.md` en 5 langues ; 9 fichiers de test, **307 scénarios**.

### **22. Sous-commande `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Flux :** `git log` entre `--since` et `HEAD` → classification par Conventional Commits → bump sémantique suggéré → assemblage du changelog → résumé exécutif IA optionnel → *prepend* dans `CHANGELOG.md`.
* **La Plage Ne Vient Plus de `git describe` :** l'origine de la plage est la **section précédente du changelog lui-même**, par deux ancres : le hash le plus récent de cette section et, en fallback, le tag de version de la section.
* **`_drop_released()` — la seconde ligne de défense :** tout commit dont le hash court apparaît dans **une autre** section de version est écarté, avec un décompte.
* **Chaque puce est devenue un lien :** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **La défaillance du PR mergé par merge-commit :** une seconde passe (`git log --merges`) construit `hash → PR` depuis l'ascendance.
* **Contributeurs résolus par la même échelle que les reviewers :** `get_commit_author_login` d'abord, puis `email_to_handle`, mis en cache dans `~/.gitpr/cache/contributors.json`.
* **La Frontière de Section était un Bug Latent :** `upsert_changelog(force=True)` remplace de la ligne `## [x.y.z]` jusqu'au prochain en-tête de **niveau 2**.
* **Classifier (`src/commit_classifier.py`)** et **Builder avec Sections Traduisibles (`src/changelog_builder.py`)**.
* **Bump Sémantique (`src/version_bump.py`)** et **Publication** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 options sur la sous-commande.**
* **🆕 État de la release :** la section `## [1.4.3] - 2026-10-27` a été générée et se trouve **dans la working tree**, pas encore commitée. Les tags `v1.4.1` et `v1.4.2` existent ; **il n'existe pas de `v1.4.3`** et PyPI sert la **1.4.2**.
* **🆕 La sous-commande passe désormais par la barrière de mise à jour** lorsqu'elle est lancée dans un terminal interactif.
* **Artefacts :** `docs/release-notes.*.md` en 5 langues, ADR et `tests/test_web_links.py`.

### **23. Sous-commande `gitpr config` — TUI de Configuration**

* **Écran Master-Detail (`src/ui/config_app.py`) :** catégories à gauche, champs à droite, édition inline. En-tête avec recherche (`/`) et compteur de modifications en attente ; pied de page `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schéma Déclaratif (`src/config_schema.py`) — la source unique de vérité :** **16 catégories** et **80 `ConfigField`**, dont **8 avancés**. Les 9 `GITPR_CHECK_*` ne sont toujours **pas** dans le schéma.
* **Catégories `policy` et `baseline` :** les champs du pack et les quatre de baseline.
* **Section Skills Alignée :** `SKILL_LABELS` a **11 entrées** (`tests`, `explain`, `mentor`).
* **Filtrage Contextuel (`show_if`) :** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` selon `DEFAULT_AI_PROVIDER` ; `GITPR_SCM_USERNAME` sous `[Bitbucket]` ; `GITPR_SCM_ORGANIZATION`/`PROJECT` sous `[Azure DevOps]`.
* **Recherche Globale (`/`) :** correspond à la clé ou au label dans toutes les catégories et **ignore le filtre de visibilité**.
* **Validation à Deux Couches :** **hors ligne** bloque `F2` avec une erreur inline ; **en ligne** (uniquement si des identifiants ont changé dans la session) tourne sur un worker avec un timeout de 10s et ne bloque que sur `401`/`403`.
* **Restauration (`Ctrl+R`) :** supprime la ligne du `.env` au lieu de réécrire le défaut ; `Esc` avec des modifications en attente demande confirmation.
* **Section Skills — la seule de portée projet :** édite les `.gitpr/skill/*.md` du projet, résolus depuis le répertoire d'invocation, avec écriture atomique et préservation de CRLF/LF.
* **Téléchargements Forcés :** boutons qui forcent le re-téléchargement des smart-excludes, des traductions, des presets et des thinking words via `force=`.
* **Module de Liens Léger (`src/doc_links.py`) :** `doc_url()` a quitté `core.py` pour que l'UI puisse obtenir le lien sans importer les SDK d'IA.
* **Tests :** 5 fichiers — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Dette connue :** `gitpr -h config` ouvre la TUI et ignore `-h` — la barrière `if ctx.invoked_subcommand is not None: return` (main.py) s'exécute avant le bloc `help_flag`. 🆕 La barrière de mise à jour vit désormais aussi à cet endroit, mais avec `not help_flag` et le balayage de l'`argv` brut comme garde.

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
* **`render.py` a gagné le paramètre de risque :** `compose_review_content` / `render_review_result` **préposent** la section `⚡ Risk Assessment` au-dessus des alertes du linter quand `GITPR_RISK_INCLUDE_IN_REVIEW` est activé.
* **Artefacts :** `docs/review-pr.md` + `.pt_br.md` (les 3 autres langues **sont encore en attente**), `docs/code-review-ia.*.md` (5), ADR-005 et `glossary-review-pr.md`.
* **Tests :** 4 fichiers dans `tests/review/` (**97 scénarios**).

### **27. Résolution de l'Identité du Reviewer (`src/reviewer_resolution.py`)**

* **Le Bug (deux défaillances silencieuses en chaîne) :** (1) **préremplissage vide** — `handles` uniquement avec `email_to_handle()` ; (2) **attache non vérifiée** — valeurs transmises *verbatim*, GitHub répondait **201 sans attacher personne** et `_request()` revenait sans lever.
* **Module :** pur, sans I/O propre, **qui ne lève jamais**. `resolve_candidates()` (avant la TUI) et `resolve_typed_reviewers()` (à l'attache) ; `match_candidate()` correspond exactement et de façon normalisée par login, nom ou email.
* **Échelle de Résolution :** handle connu (sans requête) → correspondance exacte avec une personne suggérée → recherche par email → validation du login sur la forge.
* **Ce qui ne se résout pas n'est jamais envoyé :** sort dans `ResolutionOutcome.dropped` comme `(value, reason_i18n)` et est écarté avec un avertissement visible.
* **La Même Échelle Sert à la Release :** `release_engine` réutilise `get_commit_author_login` + `email_to_handle`.
* **Tests :** `tests/test_reviewer_resolution.py` (18) + extensions dans `test_github_provider.py` (72), `test_pr_publish_app.py` (55), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) et `test_main_suggest_reviewers.py` (8).

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
* **✅ Les 7 échecs de `tests/badge/test_badge_paths.py` ont été clos dans cette fenêtre :** le cas d'usage partagé n'exige désormais `token` **que** lorsque `check_remote_state=True` — le contrat documenté — et lit `url`/`number` avec `getattr`. La fixture a gagné `token="ghp_fake"` et `number=1`, et le test asynchrone Textual a gagné `await pilot.exit(None)`. **Les 10 scénarios passent.**
* **Artefacts :** `docs/badge.*.md` en 5 langues et le survey dédié.

### **30. Sous-commande `gitpr split` — Commits Atomiques par Hunk (`src/split/`)**

* **Ce Que C'est :** un arbre de travail avec plusieurs préoccupations ne se transforme plus en commit fourre-tout. `split` lit le diff non commité, demande à l'IA de partitionner les hunks par **intention logique** et propose un commit atomique par préoccupation. L'arbre n'est jamais réécrit.
* **Paquet de 6 fichiers :** `split_plan.py`, `hunk_parser.py` (pur), `hunk_grouper.py`, `generate_split_plan.py` (lecture seule), `apply_split_plan.py` (**le seul qui mute**) et `__init__.py`.
* **La Garantie d'Arbre Intact est Structurelle :** `selective_stager.py` pré-valide **chaque** groupe contre un index temporaire à HEAD et utilise un `GIT_INDEX_FILE` jetable.
* **Destructif pour l'Index, pas pour l'Arbre :** `--apply` remet l'index à HEAD avant le staging.
* **Configuration :** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) et `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Les valeurs zéro ou négatives sont **ignorées au profit du défaut**.
* **Tests :** 6 fichiers dans `tests/split/` (**101 scénarios**).
* **Artefacts :** `docs/split-command.md` en 5 langues, ADR-006, `glossary-gitpr-split.md`, spec/plan et survey.

### **31. Analyse de Secrets Intégrée (`src/security_ruleset.py`)**

* **Ce Que C'est :** sept règles qui tournent à **chaque** invocation du linter, fusionnées à la fin de `load_linter_rules()` après les règles du projet et les plugins.
* **Les Sept Règles :** cinq `error` bloquantes — ID de clé d'accès AWS, jeton GitHub, jeton Slack, clé API Google et bloc de clé privée — et deux `warning` : URL de connexion à la base avec credentials et affectation générique de credentials, cette dernière derrière un filtre de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Pourquoi dans le Paquet et pas dans le Template :** une barrière de secrets doit se comporter **pareil** sur chaque machine et dans chaque CI.
* **Lacunes de Couverture (v1) — confirmées encore ouvertes :** l'affectation **sans guillemets** n'est pas détectée (`API_KEY=abc123`) ; les préfixes `ASIA…`, `github_pat_…` et `xoxc-`/`xoxd-` manquent ; et il n'y a pas de frontière gauche sur le nom de la clé.
* **Configuration :** `GITPR_LINTER_SECURITY` (défaut `true` ; opt-out fail-open) et `GITPR_LINTER_SECURITY_DISABLED_RULES` (séparées par `;`).
* **Tests :** `tests/test_security_ruleset.py` (**61 scénarios**).
* **Artefacts :** ADR-007, `glossary-gitpr-secret-scanning.md`, spec/plan/survey et 3 rapports de tâche.

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
* **Deux Défauts Corrigés (fenêtre v0.0.19) :** le cas d'usage accepte le **dict** déjà parsé du fournisseur, et `--finding` utilise `cand.suggested_test`/`cand.diff_unified`.
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

### **35. Suite Déterministe et CI (`.github/workflows/tests.yml`)**

* **Ce Que C'est :** le workflow qui exécute la suite sur **Python 3.10** (le plancher déclaré dans `pyproject.toml`) et **3.13**, avec `fail-fast: false`.
* **`tests/conftest.py` Hermétique :** fige `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` et `LANG_VERSION` sur la version du code, plus `GITPR_SHOW_LOGS=false` et `GITPR_SKIP_UPDATE_CHECK=true`.
* **L'Ordre Compte :** `src.updater` est importé **avant** que les variables de langue soient définies.
* **Stabilité des Tests de Worker :** `test_config_app.py` et `test_metrics.py` attendent `workers.wait_for_complete()` avant d'asserter.
* **Dépendance du Workflow :** le runner propre n'a pas de `~/.gitpr`, et `test_demo_isolation.py` affirme que le profil existe — le job crée le répertoire et le `.env` vide explicitement.
* **🆕 Dans cette fenêtre la suite a grossi de 32 scénarios et perdu 7 échecs :** `tests/test_updater.py` est passé de **23 à 55** scénarios (TTL, bords, cache hérité, JSON corrompu, `force`, échec d'écriture, timeouts, comparateur PEP 440, pré-release, interrupteur strict et barrière des sous-commandes) et les 10 scénarios de `tests/badge/test_badge_paths.py` sont passés au vert. **Aucun module nouveau** — le total reste à 140 fichiers.
* **Workflow de l'Extension :** `.github/workflows/extension.yml` exécute `npm install`, build, test et package le VSIX ; sur les tags `ext-v*`, publie vers le VS Code Marketplace et Open VSX, en sautant chaque étape quand `VSCE_PAT`/`OVSX_PAT` est vide.

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
* **Identité Git sans Forge (`identity.py`) :** dépôt, branche et auteur en **un seul** appel subprocess.
* **La Section Cycle est la Seule que le Ledger ne Peut Pas Répondre :** elle liste les PR mergés via `ScmProvider.list_pull_requests` ; chaque échec dégrade vers une ligne — `Non lu : <raison>`.
* **Présentation Complète :** groupe `gitpr metrics` avec 8 sous-commandes, un dashboard qui lit le ledger sur un thread worker et l'assistant de migration à trois options.
* **Tests :** `tests/test_metrics.py` (**164 scénarios**).

### **37. Sous-commande `gitpr mentor` et le Flag `--mentor` — Mentor Mode (`src/domain/mentor/`)**

* **Ce Que C'est :** un review dit *ce* qui ne va pas et presque jamais *pourquoi* c'est important. Le Mentor Mode enrichit les constats avec **ce qui se passe**, **pourquoi c'est important**, une **analogie** du quotidien et les **concepts à étudier**.
* **Couche de Domaine Pure (`mentor_explanation_builder.py`) :** sans I/O, convertit le payload de l'IA en un `MentorExplanation` validé et impose l'**honnêteté épistémique** : sans preuve suffisante, l'explication **le dit** ; les URL hallucinées sont **supprimées** ; les IDs inconnus sont **rejetés**.
* **Couche d'Application (`generate_mentor_explanation.py`) :** collecte les candidats, **lots d'au plus 10 constats**, ordonnés par sévérité, en réutilisant le cache MD5 par hash de prompt.
* **Deux Points d'Entrée :** `gitpr mentor [--finding <id>] [--provider <name>]` et le flag `--mentor`, qui ajoute la section au review normal.
* **Opt-In Strict :** `GITPR_REVIEW_MENTOR_MODE` (défaut `false`) et `GITPR_MENTOR_INCLUDE_ANALOGY` (défaut `true`), modifiables dans la TUI de configuration.
* **Skill et Registre :** `.gitpr.mentor.md` enregistré dans `SKILL_FILES_BY_TYPE`, dans `mcp_server.SKILL_FILES` et dans `SKILL_LABELS`, avec des templates en **5 langues**.
* **Tests :** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) et `tests/test_mentor_cli.py` (4) — **2 de ces derniers échouent** à cause du bug d'isolation hérité.
* **Artefacts :** `docs/mentor-mode.*.md` en **5 langues**, ADR-009, `glossary-gitpr-mentor.md`, spec et rapports de tâche.

### **38. Sous-commande `gitpr risk` — Score de Risque Déterministe (`src/domain/risk/`)**

* **Ce Que C'est :** un score **0–100** avec niveau `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculé **sans IA** à partir de signaux locaux.
* **Couche de Domaine Pure (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`) :** enums et dataclasses ; poids, seuils, motifs de chemins critiques, correspondance de globs et détection de tests/non-exécutables chargés depuis YAML ; calcul par fichier avec **clamp 0–100** et agrégation 50/30/20.
* **La Formule est Déclarée, pas Apprise :** **50 %** le fichier le plus risqué, **30 %** la moyenne pondérée par lignes et **20 %** les preuves critiques.
* **Adaptateurs d'Infrastructure :** `risk_history_reader.py` lit `git log` et extrait les commits, corrections de bugs et reverts sur une fenêtre de **90 jours** ; `test_matcher.py` fait correspondre test ↔ fichier de production par convention.
* **Cas d'Usage (`calculate_risk.py`) :** corrèle les sections du diff, les constats, la correspondance des tests et l'historique en `FileRisk`/`PullRequestRisk`, en **dégradant avec un avertissement** quand un signal est indisponible.
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
* **Portée :** CLI (6 sous-commandes, toutes avec `--format json`), MCP (`get_baseline_status` + `baseline://summary`), review de PR distant et le flux de lint.
* **Le Refactor de la Fonctionnalité a Exigé :** `NormalizedFinding` est devenu un type canonique dans `src/domain/finding/`.
* **Tests :** `tests/domain/baseline/` (5 fichiers, **126**), `tests/application/use_cases/` (5 fichiers, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) et `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34).
* **Artefacts :** `docs/baseline-suppressions.*.md` en **5 langues**, ADR-012, `glossary-baseline.md`, spec/plan/survey et rapport de tâche.

### **41. Sous-commande `gitpr check` — Barrière CI Unifiée (`src/domain/check/`, `src/infrastructure/ci/`)**

* **Ce Que C'est :** un **point d'entrée unique** pour les pipelines de CI qui orchestre l'analyse statique, les ponts SAST, l'analyse de secrets, le review IA optionnel, le score de risque et la classification de baseline, et émet **un artefact standardisé** avec un code de sortie déterministe.
* **L'Ordre du Pipeline est Contrat :** (1) résolution de policy — invalide sort avec `2` ; (2) extraction du diff avec smart excludes ; (3) linter regex + secrets + ponts externes ; (4) score de risque préliminaire ; (5) **review IA optionnel** ; (6) classification de baseline ; (7) évaluation du seuil `--fail-on` ; (8) génération de l'artefact avec **masquage des secrets**.
* **Couche de Domaine (`src/domain/check/`) :** `check_types.py` (sévérité normalisée en trois niveaux avec les alias de spec) ; `check_config.py` (précédence **CLI > env > défauts**) ; `check_output.py` (dispatch de format et écriture atomique).
* **Couche d'Infrastructure (`src/infrastructure/ci/`) :** `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (testsuites avec `<failure>` et `<system-out>`) et `masking.py`.
* **Cas d'Usage (`run_check.py`) :** `resolve_diff_base()` (explicite → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), exécution du linter et des ponts, score de risque, review optionnel, baseline et l'enveloppe finale. Stdout n'émet **que l'artefact**.
* **Codes de Sortie :** `0` succès ; `1` constat comptable au seuil ; `2` erreur d'exécution.
* **CLI :** `gitpr check` avec 8 options et 9 variables `GITPR_CHECK_*`.
* **GitHub Action Réécrite :** `action.yml` est devenu une seule étape `gitpr check`. **Rupture déclarée** pour les workflows qui dépendaient de `--linter` + `--fullreview`.
* **Tests :** **37 scénarios** — `test_run_check.py` (8), `test_check_types.py` (8), `test_check_config.py` (3), `test_check_output.py` (3), `test_masking.py` (6), `test_sarif_builder.py` (2), `test_junit_builder.py` (2) et `test_check_cli.py` (5).
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
* **CI/CD de l'Extension :** `.github/workflows/extension.yml` installe, build, teste et package le VSIX ; sur les tags `ext-v*`, publie vers le Marketplace et Open VSX.
* **Ce Que l'Extension ne Fait Pas :** elle ne commit pas, ne push pas et ne publie pas sans confirmation.
* **Tests :** sa propre suite Mocha (7 scénarios, dont le vrai stdio) + `tests/test_mcp_server.py` (115 scénarios).
* **Artefacts :** `docs/editor-extension.*.md` en **5 langues**, ADR-014, `glossary-editor-extension.md`, specs, `plansfacts` et le rapport Gemini.

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
| `tests/test_commit_classifier.py` | 30 | Classification Conventional Commits + `pr_number_from_merge()` sur les 4 forges |
| `tests/test_config_app.py` | 78 | TUI de configuration : montage, changement de catégorie, dirty tracking, F2 bloqué, Ctrl+R, recherche, secrets |
| `tests/test_config_cli.py` | 9 | Enregistrement de la sous-commande `config`, `-h`, import paresseux, stdout propre |
| `tests/test_config_schema.py` | 42 | Couverture de `DEFAULT_CONFIG`, absence de doublons, catégories/kinds, `advanced` seulement dans Advanced, section skills |
| `tests/test_config_store.py` | 22 | Round-trip sur le `.env` temporaire, commentaires et ordre préservés, `remove_config_value()` idempotent |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuration des reviewers suggérés (clés et défauts) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` avec SDK mocké (401 vs. réseau vs. ollama) |
| `tests/test_core.py` | 49 | Flux principaux, git diff, génération de PR, timing, staging, co-paternité, langue des hooks |
| `tests/test_diff_failure_cli.py` | 3 | Code de sortie 1 sur échec de git, 0 sur diff vide, fail-open en `--hook` |
| `tests/test_diff_parser.py` | 26 | Parser de diff par lignes/hunks + `summarize_patch()` et `split_patch_sections()` |
| `tests/test_explain_command.py` | 4 | CLI `explain` : succès, clé manquante, diff vide, aide contextuelle |
| `tests/test_external_linters.py` | 33 | Pont Checkstyle : parser XML, subprocess, intersection de diff, rapport |
| `tests/test_full_diff_encoding.py` | 1 | Dépôt réel avec un fichier Latin-1 : l'octet `0xFA` devient `U+FFFD` et le fichier apparaît dans le diff |
| `tests/test_i18n.py` | 20 | Parité entre langues, clés manquantes/orphelines, identité — **3 échecs** ❌ |
| `tests/test_install_wizard.py` | 3 | Assistant d'installation interactif |
| `tests/test_issue_engine.py` | 4 | Brouillon d'issue structuré |
| `tests/test_linter_metrics.py` | 4 | Métriques du linter : erreurs, avertissements, durée |
| `tests/test_linter_presets.py` | 5 | Presets de linter : résolution et re-téléchargement forcé |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` dans la CLI et dans l'aide contextuelle |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompts MCP et fallback de langue |
| `tests/test_mcp_server.py` | 115 | Outils MCP (18), ressources (19), annotations, patching, CLI directe, offload, contrat de l'extension |
| `tests/test_mcp_server_e2e.py` | 6 | Vrai serveur MCP via subprocess + JSON-RPC stdio |
| `tests/test_mentor_cli.py` | 4 | CLI `mentor` et opt-in `--mentor` — **2 échecs connus** ❌ |
| `tests/test_metrics.py` | 164 | **Ledger** : écriture synchrone, export idempotent, bundle/merge, versionnage, migration, renderers, coût, qualité, modules, fournisseurs, cycle |
| `tests/test_net_timeouts.py` | 12 | Timeouts réseau/IA — alignés sur le défaut réel de 180s |
| `tests/test_plugins.py` | 17 | Découverte de plugins, fusion de règles de linter, prompts MCP |
| `tests/test_policy_cli.py` | 26 | Groupe `policy` : list/show/validate/use/off/install/init, confirmation et lockfile |
| `tests/test_pr_publish_app.py` | 55 | TUI du PR Publisher : écrans, flux, reviewers suggérés, `NoticeScreen`, modale de merge |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modale d'erreur du linter : abandon, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save et payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release : options, aide avec epilog documenté |
| `tests/test_release_engine.py` | 53 | **Cutoff ancré dans le changelog, `_drop_released()`, liens, carte de PR par merge commit**, publication |
| `tests/test_reviewer_resolution.py` | 18 | Échelle de résolution, rejet de nom partiel, dédup, tolérance aux échecs |
| `tests/test_reviewer_suggestion.py` | 18 | Logique de suggestion de reviewers (classement, exclusion, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | CLI `risk` : `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 61 | Matrice du ruleset intégré : regex, placeholders, niveau, wildcard, fusion, traductions |
| `tests/test_skill_command.py` | 10 | Téléchargement et validation des templates de skill |
| `tests/test_skill_context.py` | 20 | `get_skill_context()` avec `quiet=True`, fallbacks, garde de l'artefact de PR |
| `tests/test_skill_templates.py` | 99 | Scan paramétré de **chaque template publié** contre les références embarquées |
| `tests/test_smart_excludes.py` | 15 | Filtre pathspec intelligent et re-téléchargement forcé |
| `tests/test_suggest_reviewers.py` | 17 | Reviewers suggérés dans le flux de PR (intégration, hint `no_login`) |
| `tests/test_tests_command.py` | 4 | Groupe `tests` et CLI `generate`, `--finding` et epilog avec lien |
| `tests/test_thinking_words.py` | 5 | Chargement, parsing avec séparateur `;` et rechargement forcé |
| `tests/test_updater.py` | 55 ⬆️ | **Barrière PyPI** : TTL glissant, cache hérité/corrompu, écriture atomique, `force`, `bounded_urlopen`, comparateur PEP 440, pré-release, interrupteur strict et barrière des sous-commandes |
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
| `tests/badge/test_badge_paths.py` | 10 ✅ | Point d'injection unique dans les deux publishers — **les 7 échecs ont été clos** |
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
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Effet réel du pack sur review, lint et risque (avec garde réseau autouse) |
| `tests/review/test_diff_normalizer.py` | 20 | Sauts de ligne, validation de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource` : provenance, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orchestration : barrières, PR, diff, linter, commentaire optionnel |
| `tests/review/test_review_pr_cli.py` | 25 | CLI `review-pr` : options, rejets avant l'IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 48 | Provider Azure DevOps : org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 44 | Provider Bitbucket : Basic auth, workspace, PRs |
| `tests/scm/test_contract.py` | 42 | Contrat `ScmProvider` : signatures, dataclasses, erreurs |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` déprécié → délègue au provider |
| `tests/scm/test_github_provider.py` | 72 | Provider GitHub : REST, en-têtes, PRs, issues, releases, relecture des reviewers |
| `tests/scm/test_gitlab_provider.py` | 52 | Provider GitLab : API v4, namespace, en-têtes synthétisés et `overflow` |
| `tests/scm/test_init_wizard.py` | 12 | Wizard `--init` : détection de forge, validation, persistance |
| `tests/scm/test_release_publish.py` | 8 | Publication de release par forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 | Le seul module qui mute : staging sélectif, un commit par groupe |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unités → groupes → pré-validation des conflits → messages |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, budget/troncature, appel IA, validation de réponse |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()` : texte ↔ modèle, pur |
| `tests/split/test_selective_stager.py` | 11 | Staging d'un sous-ensemble, vérification contre HEAD, index propre |
| `tests/split/test_split_cli.py` | 31 | CLI split : options, dry run par défaut, `--apply` |
| `tests/sync_i18n.py` | — | Script de vérification de couverture i18n (échafaudage ; jamais exécuté) |

**Total :** **2996 scénarios collectés dans 140 modules de test** (53 à la racine + 12 dans `tests/application/use_cases/` + 10 dans `tests/fix/` + 9 dans `tests/scm/` + 7 dans `tests/badge/` + 6 dans `tests/demo/` + 6 dans `tests/split/` + 5 dans `tests/domain/baseline/` + 4 dans `tests/domain/policy/` + 4 dans `tests/review/` + 4 dans `tests/infrastructure/linter/external/` + 3 dans `tests/domain/check/` + 3 dans `tests/infrastructure/ci/` + 2 dans `tests/domain/risk/` + 2 dans `tests/domain/tests_generation/` + 2 dans `tests/integration/` + 2 dans `tests/infrastructure/git/` + 2 dans `tests/domain/linter/` + 1 dans `tests/domain/finding/` + 1 dans `tests/domain/mentor/` + 1 dans `tests/domain/pr/` + 1 dans `tests/infrastructure/baseline/` ; **+32 scénarios, +0 fichier** depuis le rapport précédent).

**Notes de qualité pour cette version :**
- **Exécution complète sur cette machine :** **5 failed, 2989 passed, 2 skipped, 138 subtests passed in 461.76 s (7m41s)** — les 5 échecs sont exactement les dettes héritées : 3 i18n et 2 mentor. **12 échecs → 5.**
- **Les 7 échecs de `tests/badge/test_badge_paths.py` ont été résolus.** `publish_pull_request_use_case` n'exige désormais `token` **que** dans le bloc `check_remote_state` — c'était le contrat documenté, et la vérification inconditionnelle transformait une fixture sans jeton en `auth_required` (401), qui empêchait `create_pull_request` de s'exécuter. `pr_url`/`pr_number` utilisent maintenant `getattr` avec un défaut, la fixture a gagné `token="ghp_fake"` et `number=1`, et le test Textual a gagné `await pilot.exit(None)`. **Les 10 scénarios passent en ~6 s.**
- **Les 3 échecs i18n sont toujours de la vraie dette de traduction et n'ont pas été touchés dans cette fenêtre :** `test_key_parity_and_count` (es/fr à 1618), `test_no_missing_keys` (8 clés utilisées dans le code sans dictionnaire) et `test_no_orphan_keys` (2 anciennes clés). Les dictionnaires restent aux mêmes 1620/1618 que dans le rapport précédent.
- **Les 2 échecs de `tests/test_mentor_cli.py` sont toujours le même bug d'isolation :** `@patch("src.core.get_git_diff")` n'atteint pas la liaison importée par valeur dans `src/main.py`. Sur un arbre dont le seul changement est un `.md` (filtré par `:(exclude)*.md` dans les smart excludes), le vrai diff revient vide, le flux retourne avant le renderer et l'assertion échoue. **Avec le patch sur `src.main.get_git_diff`, les deux scénarios passent.** C'est toujours un correctif de deux lignes.
- **Du côté positif, les 32 nouveaux scénarios couvrent exactement ce qui a changé :** `tests/test_updater.py` est passé de 23 à **55** scénarios — TTL frais/bord/expiré, horodatage futur ou timezone-aware, cache hérité `date`, JSON corrompu, `force`, échec d'écriture, absence de `.tmp`, timeouts et user-agent, comparateur PEP 440, pré-release distante, interrupteur strict et barrière des sous-commandes (blocage sur un TTY, exemption sans TTY, exemption avec `-h`).
- **La suite dépend de l'état de l'arbre pour les tests de mentor :** avec seuls des fichiers `.md` modifiés, les deux scénarios échouent ; avec un `.py` quelconque modifié, ils passent. Cela explique la différence entre l'exécution consignée dans le rapport de la tâche (4 échecs) et celle de cette fenêtre.
- **`tests/conftest.py` reste hermétique :** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` et `LANG_VERSION` sur la version du code.
- **Garde réseau à deux endroits :** `tests/demo/conftest.py` et `tests/integration/conftest.py`.
- **Fixtures git réelles :** `tests/fix/git_fixture.py`, `tests/split/git_fixture.py` et `tests/test_full_diff_encoding.py`.
- **La suite de l'extension est séparée :** 7 scénarios Mocha dans `extensions/vscode/src/test/mcp_client.test.ts`, dont 1 s'intègre vraiment au serveur MCP Python via stdio ; elle ne compte pas dans pytest.
- **La CI tourne sur Python 3.10 et 3.13** avec `fail-fast: false`.

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n — dette inchangée :** **1620 clés** dans `pt_br.json`, `pt_pt.json`, `es_es.json` et `fr_fr.json`, mais **1618** dans `es.json` et `fr.json`. Le code utilise **1626** clés : **8 non traduites** (les messages de `publish_pull_request` et les descriptions des outils MCP `run_check`/`publish_pull_request`) et **2 orphelines** (`Cannot publish a Pull Request from a detached HEAD or empty branch.` et `Current branch has not been pushed to the remote…`). Aucune nouvelle clé n'a été ajoutée dans cette fenêtre — et aucune dette n'a été payée. ❌
* **`__lang_version__` reste à v0.0.35** (aucun bump dans cette fenêtre) — la barrière de mise à jour n'a introduit **aucune** nouvelle chaîne d'interface : les messages existants continuent d'être réutilisés.
* **Sources de traduction en phase :** une nouvelle clé doit exister dans le code (source), dans `langs/pt_br.json` (**liste maîtresse**), dans les dicts FR/ES de `scripts/sync_all_langs.py` et dans les valeurs curatées de `scripts/fix_mangled_i18n_keys.py` ; l'assertion `len(CLEAN_KEYS)` reste à 49.
* **Nouveaux topics :** **aucun** dans cette fenêtre — la lacune de documentation avait déjà été close en v0.0.19 et le reste.
* **🆕 Topics mis à jour dans cette fenêtre :**
  - `docs/auto-update.*` (**5 fichiers**) — une fenêtre de 1 heure au lieu de 24, `-u` ignorant le cache, le tableau des exemptions avec les deux nouvelles lignes (sous-commande sans terminal et sous-commande avec `-h`), la règle PEP 440 pour les pré-releases et le comportement hors ligne réécrit.
  - `docs/tutorial/install-from-source.*` (**5 fichiers**) — un cache de 1 h dans le diagramme des versions, le parser strict de `GITPR_SKIP_UPDATE_CHECK` (avec l'exemple `=false` qui désormais **ne** désactive **pas** la vérification) et une note indiquant que la surface des flags reste bloquée sans TTY.
  - `README.es_es.md` et `README.fr_fr.md` — les liens de documentation sont passés de `?lang=es_es`/`?lang=fr_fr` à `?lang=es`/`?lang=fr`, le format que le site sert réellement.
* **`CONTEXT.md` (nouveau, à la racine) :** le glossaire du domaine de la barrière de mise à jour — **update gate**, **update check**, **release cache**, **freshness window** et **interactive run**, chacun avec les synonymes à éviter. Il vient de la session de grill qui a précédé le correctif.
* **Documentation en 5 langues :** **50 topics canoniques** dans `docs/` (inchangé) — **47 avec couverture complète dans les 5 langues** et **3 topics partiels** (`github-issue-prompt-com-gh`, `version-markers`, `review-pr`), les mêmes 3 qu'avant.
* **Skills locaux de Claude Code :** `.claude/skills/` avec **29 skills** (compte inchangé).
* **Index de Mémoire :** `.claude/memory/MEMORY.md` avec **40 patterns** (compte inchangé).
* **Rapports de tâche :** `docs/claude-code/reports/develop_natan/` (**114** au total ; **+2** — `2026-10-27_update_check_stale_cache.md` et ce rapport de statut lui-même) et `docs/gemini/reports/develop_natan/` (**17** ; **+1** — `2026-10-27_fix_badge_paths_tests.md`).
* **Rapports de statut :** `docs/reports/` — c'est le **20e rapport** (v0.0.20) ; v0.0.19 n'est jamais écrasé.
* **Plans de développement :** 148 fichiers dans `docs/plans/` (**+2** — `20261027_update_check_stale_cache_plansfacts.md` et `docs/gemini/plans/.../2026-10-27_develop_natan_fix_badge_paths_tests.md`) + **21 fichiers dans `docs/survey/`** (+1 — `20261027_update_check_stale_cache_surveyfacts.md`).

---

## **🔄 Pipeline de Distribution**

1. **PyPI (canal unique) :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **🆕 Mise à jour obligatoire avec fenêtre glissante :** l'exécution vérifie PyPI au démarrage et **bloque avec le code de sortie 1** s'il existe une version plus récente, en imprimant `pip install --upgrade gitpr-cli`. La réponse est cachée pendant **1 heure** (plus jusqu'à minuit) et `--update` interroge **toujours** le réseau. La comparaison est PEP 440 : une pré-release distante ne bloque pas une installation stable, et une version illisible compte comme inconnue. **La barrière couvre désormais les sous-commandes** quand il y a un TTY ; CI, pipes, hooks, MCP et `-h` restent exemptés.
3. **GitHub Releases :** supprimé — pas de PyInstaller, pas d'asset `.exe`, pas de hot-swap
4. **GitHub Actions :** workflow `pr-review.yml` + `action.yml` (installe via pip) + `tests.yml` (matrice Python 3.10 et 3.13) + `extension.yml` (VSIX ; publication vers Marketplace/Open VSX sur `ext-v*`). `action.yml` appelle une seule étape `gitpr check` — **rupture déclarée** pour ceux qui utilisent `--linter` + `--fullreview`.
5. **Serveur MCP :** point d'entrée `gitpr-mcp` via `pyproject.toml`
6. **Extension Éditeur :** paquet VSIX indépendant (`natanfiuza.gitpr`), avec les canaux VS Code Marketplace et Open VSX ; exige Node.js 20+ uniquement pour le build, pas pour l'usage
7. **Templates et langues OTA :** `templates/` et `langs/*.json` servis depuis GitHub (main) — `__lang_version__` **v0.0.35** renouvelle les copies locales dans `~/.gitpr/langs/` ; `__scripts_version__` **v0.0.4** renouvelle les hooks
8. **Version dérivée du code :** `pyproject.toml` utilise `version = {attr = "src.updater.__version__"}` — `__version__` est la **seule** source
9. **`package-data` compte :** les quatre packs embarqués (`src/policy_packs/*`) voyagent dans le wheel
10. **🆕 État de la release :** `__version__` est à **1.4.3** et le commit est sur `main` (PR #228), mais la release **n'a pas été coupée** : il n'existe pas de tag `v1.4.3`, PyPI sert la **1.4.2** et la section `[1.4.3]` de `CHANGELOG.md` est **dans la working tree**. Le pipeline de release reste manuel.

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.19)**

| Domaine | v0.0.19 (précédent) | v0.0.20 (actuel) |
|------|-------------------|-----------------|
| **Version de GitPR** | 1.4.2 (publiée, tag `v1.4.2`) | **1.4.3** — mergée dans le PR #228, **pas encore publiée** (PyPI sert 1.4.2, pas de tag, changelog dans la working tree) |
| **Version des Langues** | v0.0.35 | v0.0.35 (inchangé) |
| **Version des Scripts de Hook** | v0.0.4 | v0.0.4 (inchangée) |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Inchangé |
| **Langues** | 5 langues, 6 dictionnaires | Inchangé |
| **Sous-commandes** | 15 | 15 (inchangé) — 4 groupes (baseline 6, metrics 8, policy 7, tests 1) |
| **Couches** | 9 paquets de domaine, 15 cas d'usage | Inchangé |
| **Outils MCP** | 18 outils / 19 ressources / 7 prompts | Inchangé |
| **Flags CLI** | 32 à la racine + 15 sous-commandes | Inchangé |
| **Variables d'Environnement** | 58 dans `DEFAULT_CONFIG` + prix/devise + 9 `GITPR_CHECK_*` | Inchangé — mais `GITPR_SKIP_UPDATE_CHECK` a changé de **sémantique** |
| **Schéma de Config** | 80 `ConfigField` / 16 catégories | Inchangé |
| **Barrière de Mise à Jour 🆕** | Cache quotidien par date locale ; sous-commandes sans barrière ; `=false` la désactivait ; comparaison par tuples | **TTL glissant de 1 h**, `-u` toujours en ligne, **PEP 440**, `bounded_urlopen` (3 s), sémantique booléenne stricte et barrière des sous-commandes seulement sur un TTY |
| **Aide des Sous-commandes 🆕** | — | `_subcommand_help_in_argv()` lit l'`argv` brut parce que Click vide `ctx.args` avant le callback |
| **Publication de PR 🆕** | Jeton exigé inconditionnellement ; `result.url`/`.number` accédés directement | Jeton exigé **uniquement** avec `check_remote_state=True` ; `getattr` défensif |
| **Linter** | Regex + secrets + SAST + `findings_out` + sensible à la baseline | Inchangé |
| **Git Hooks** | post-checkout/post-merge/pre-push dans le ledger | Inchangé |
| **Couche SCM** | 4 forges, pagination, `web_links.py` | Inchangé |
| **Moteur de release** | 1.4.1 et 1.4.2 commitées, taggées et publiées | Section `[1.4.3]` générée **dans la working tree** ; sans tag et sans publication |
| **i18n (clés par fichier)** | 1620 × 4 et 1618 × 2 — 8 non traduites et 2 orphelines | **Inchangé** ❌ |
| **Documentation** | 50 topics (47 complets + 3 partiels) | 50 topics (47 + 3) — **aucun topic nouveau** ; `auto-update` et `tutorial/install-from-source` mis à jour dans les 5 langues |
| **Glossaire du Domaine 🆕** | — | `CONTEXT.md` à la racine avec 5 termes de la barrière de mise à jour |
| **CI** | `tests.yml` (3.10 + 3.13) + `action.yml` + `extension.yml` | Inchangé |
| **Suite de Tests** | 2964 scénarios (140 fichiers) | **2996 scénarios (140 fichiers) — +32 scénarios, +0 fichier** |
| **Échecs Connus** | 12 (7 badge + 3 i18n + 2 mentor) | **5** (3 i18n + 2 mentor) — **les 7 du badge ont été clos** |
| **Dépendances** | Aucune nouvelle dépendance Python | Aucune nouvelle — `packaging` était déjà une dépendance (policy packs) |
| **Commits depuis le rapport** | 4 commits | **2 commits** (`68df9ae`, `145cbac`) |
| **PRs Mergés** | 4 PRs (#218, #220, #222, #223) ; 1 issue (#221) | **3 PRs (#224, #226, #228)** ; 2 issues fermées (#225, #227) |
| **Index de Mémoire** | 40 patterns | 40 patterns (inchangé) |
| **Rapports de tâche** | 112 claude-code, 16 gemini | **114 claude-code (+2) et 17 gemini (+1)** |
| **Plans de développement** | 146 plans, 20 surveys | **148 plans (+2), 21 surveys (+1)** |

---

## **🚧 Prochaines Étapes**

* **Publier la 1.4.3 sur PyPI :** `__version__` est déjà à 1.4.3 et le commit est sur `main` (PR #228), mais il manque encore : **commiter la section `[1.4.3]` de `CHANGELOG.md`** (elle est dans la working tree), créer le tag `v1.4.3` et téléverser le paquet. D'ici là, les installations en 1.4.1/1.4.2 **ne voient pas le correctif** — leur ancienne logique répond depuis le cache quotidien. 🆕
* **Payer la dette i18n :** 8 clés utilisées sans traduction (messages de `publish_pull_request` et descriptions MCP) + 2 orphelines + les 2 clés manquantes dans `es.json`/`fr.json`. Clore cela débloque d'un coup `test_key_parity_and_count`, `test_no_missing_keys` et `test_no_orphan_keys`. **Élément hérité, toujours ouvert.**
* **Corriger l'isolation de `tests/test_mentor_cli.py` :** ce sont les **deux plus anciens échecs** de la suite et les **seuls** restants en dehors de l'i18n. Le patch vise `src.core.get_git_diff`, mais `src/main.py` importe le nom **par valeur**. Changer la cible vers `src.main.get_git_diff` clôt la suite sans toucher au produit. **Élément hérité, toujours ouvert.**
* **Automatiser la release dans GitHub Actions :** la CI de test a quatre workflows, mais le build + l'upload vers PyPI restent manuels — et `extension.yml` montre déjà le motif (déclencheur sur tag) qu'un `release.yml` peut suivre. **Élément hérité, toujours ouvert.**
* **Étendre la couverture du ruleset de secrets :** les trois lacunes restent ouvertes — affectation sans guillemets (`API_KEY=abc123`), préfixes `ASIA…`, `github_pat_…` et `xoxc-`/`xoxd-`, et absence de frontière gauche sur le nom de la clé. **Élément hérité, toujours ouvert.**
* **Traduire `docs/review-pr.*.md` :** c'est la **dernière** famille de fonctionnalités avec une couverture partielle (2 langues sur 5). **Élément hérité, toujours ouvert.**
* **Désuivre les artéfacts de `.gitpr/metrics/export/` :** **34 fichiers restent suivis**, parce que `gitignore` ne désuit pas. Il manque un simple `git rm --cached`. **Élément hérité, toujours ouvert.**
* **Documenter la rupture de contrat de `request_pull_request_reviewers` :** elle est passée de `None` à `list[str]` ; les sous-classes tierces de `ScmProvider` doivent être mises à jour. **Élément hérité, toujours ouvert.**
* **Documenter le changement de sémantique de `GITPR_SKIP_UPDATE_CHECK` comme note de migration :** le `docs/tutorial/install-from-source` décrit déjà le parser strict, mais les automatisations qui positionnaient `=false` en attendant de faire taire la vérification la voient désormais s'exécuter. Une ligne dans `auto-update` et dans le changelog de la prochaine release vaut la peine. 🆕
* **Cartographier le rayon d'action de la baseline sur le code de sortie :** une baseline qui ne peut pas être appliquée avorte six flux avec le code de sortie 1, et `check` utilise **2**. Il vaut la peine d'expliciter le contrat complet dans `docs/baseline-suppressions`/`docs/check-ci`. **Élément hérité, partiellement couvert par check.**
* **Réconcilier la version du projet :** `CLAUDE.md` dit encore `Current version: 0.0.37` alors que `__version__` est à **1.4.3**. Définir une convention unique. **Élément hérité, toujours ouvert.**
* **`gitpr -h config` ignore `-h` :** la sous-commande ouvre la TUI au lieu d'afficher l'aide — la barrière `if ctx.invoked_subcommand is not None: return` s'exécute avant le bloc `help_flag`. **Élément hérité, toujours ouvert** (et partageant désormais le même point que la barrière de mise à jour).
* **Migrer l'extracteur de `sync_i18n.py` vers AST :** la regex tronque les littéraux avec concaténation implicite (`__("a " "b")`). `tests/test_i18n.py` utilise déjà AST ; le script de sync reste sur regex. **Élément hérité, toujours ouvert.**
* **Section Smart Exclude dans la TUI :** l'item 10 du survey de la TUI de configuration reste non commencé, et les champs `GITPR_SMART_EXCLUDES_*` restent sous `diff`, sans catégorie propre. **Élément hérité, toujours ouvert.**
* **Dettes consignées dans le plan de la TUI de config :** `DEFAULT_CONFIG` est devenu redondant avec le schéma ; la bannière d'ouverture ne liste pas `--init`, `--base` ni `--plugins` ; `LinterApp` est la **seule** app Textual qui ne désactive toujours pas la palette de commandes ; et les **9 `GITPR_CHECK_*`** n'ont toujours **aucune** catégorie dans le schéma. **Élément hérité, toujours ouvert.**
* **Créer une note de migration pour `action.yml` :** remplacer `--linter` + `--fullreview` par `gitpr check` est une rupture de contrat pour les workflows existants ; `docs/check-ci` documente la nouvelle commande, mais pas un « avant et après ». **Élément hérité, toujours ouvert.**
* **Publier l'extension sur les marketplaces :** le workflow est prêt, mais il dépend de `VSCE_PAT`/`OVSX_PAT` ; d'ici là, l'installation se fait via un VSIX local. **Élément hérité, toujours ouvert.**
* **Revoir `git diff` dans les dépôts avec de gros fichiers binaires :** le décodage avec `errors="replace"` résout le contenu hérité, mais les octets binaires du diff arrivent désormais au modèle comme une séquence de `U+FFFD`. Il vaut la peine de confirmer que les smart excludes couvrent les cas réels. **Élément hérité, toujours ouvert.**
* **🆕 Revalider la barrière réelle après la release :** avec la 1.4.3 publiée, une installation 1.4.3 ne doit **pas** bloquer, et une 1.4.2 doit bloquer dans l'heure suivant la mise en ligne du paquet — le scénario qui a donné naissance au correctif.
* **🆕 Surveiller le fallback ES/FR sur le site :** les liens des README utilisent déjà `?lang=es` et `?lang=fr` (le format correct), mais le site peut encore servir l'anglais pour ces langues. Publier les traductions honore la promesse des liens.
* **Fournisseur Anthropic Claude :** support direct de l'API Claude (`claude-sonnet-5`).
* **Graphiques ASCII/Textual dans le Dashboard :** histogrammes temporels et tendances de tokens dans la TUI de métriques.
* **Seed local de `.gitpr/conf/` :** le seed des templates de configuration locale (smart-excludes, linter) reste en attente comme sous-commande propre ou étape de wizard.
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires.

### ✅ Terminé dans cette fenêtre (2026-10-27 → 2026-10-28)

* ~~**Corriger les 7 échecs de `tests/badge/test_badge_paths.py`**~~ — PR #226 (`68df9ae`) : la validation du jeton est passée dans `check_remote_state`, `url`/`number` utilisent désormais `getattr`, la fixture a gagné `token="ghp_fake"`/`number=1` et le test Textual a gagné `pilot.exit(None)`. **Élément hérité pendant trois fenêtres consécutives — clos.**
* ~~**La barrière de mise à jour ne détectait pas une release publiée le même jour**~~ 🆕 — PR #228 (`145cbac`) : TTL glissant de 1 h, `-u` sans cache, PEP 440, `bounded_urlopen`, écriture atomique, interrupteur strict et barrière des sous-commandes uniquement en exécution interactive. **32 scénarios nouveaux dans `tests/test_updater.py`.**
* ~~**Les sous-commandes échappaient à la barrière**~~ 🆕 — `gitpr release`, `fix`, `review-pr` et les autres sont désormais bloqués dans un terminal, avec exemption pour la CI/les pipes/les hooks et pour `-h` lu dans l'`argv` brut.
* ~~**Les liens ES/FR des README pointaient vers un paramètre de langue inexistant**~~ 🆕 — `?lang=es_es`/`?lang=fr_fr` sont devenus `?lang=es`/`?lang=fr` (PR #226).
* ~~**Documenter l'auto-update avec la fenêtre de 24 h**~~ 🆕 — les 5 variantes de `docs/auto-update.*` et les 5 de `docs/tutorial/install-from-source.*` ont été réécrites pour la fenêtre de 1 h, les nouvelles exemptions et la règle PEP 440.

---

**Rapport généré le :** 2026-10-28  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
