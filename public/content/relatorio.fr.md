# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.17 (2026-10-12)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.17) :**
- **La release 1.3.0 a été close et la 1.4.0 est déjà taguée :** le tag `v1.3.0` (merge de la PR #193, 2026-09-24) a clos l'élément qui bloquait deux fenêtres, et `CHANGELOG.md` a été complété avec les six fonctionnalités qui manquaient. Dix jours plus tard, le tag `v1.4.0` (merge de la PR #207, 2026-10-12) a clos toute cette fenêtre — les cinq fonctionnalités ci-dessous **plus** la note de release 1.3.0 tiennent dans un seul cycle.
- **`gitpr release` a cessé de répéter l'historique :** les release notes listaient, pour chaque version, tous les commits de toutes les versions précédentes (la section 1.3.0 est partie avec 124 puces, dont 115 répétaient la 1.2.0). La cause n'était pas l'IA — c'était la plage de `git log`, ancrée sur `git describe`, qui dans ce dépôt résolvait vers `v0.0.8`. La coupure est devenue la **section précédente du changelog lui-même**, chaque puce a gagné un lien **réel et cliquable** vers le commit, la PR et le contributeur, et un commit déjà présent dans une autre section est écarté avant de devenir une puce — quand il ne reste rien, la release **avorte** au lieu de publier une section vide.
- **La télémétrie a quitté le fichier JSON et est devenue un ledger SQLite :** les événements étaient écrits dans des fichiers par commande, depuis un thread daemon, puis « enrichis » en retour à travers le cache d'IA via une correspondance heuristique sur (repo, branche, action, minute) qui dépendait de trois cartes de dossiers divergentes. Le résultat était prévisible : `tokens_actual` **toujours à zéro** et une perte de données entre dépôts à l'export. `~/.gitpr/metrics/telemetry.db` écrit la ligne **de façon synchrone**, avec les tokens copiés depuis les métadonnées du fournisseur au moment de l'écriture, et un processus de courte durée (un hook, par exemple) ne perd plus sa propre ligne à la sortie.
- **Mentor Mode — le review qui enseigne au lieu de seulement pointer :** un review dit *ce* qui ne va pas et presque jamais *pourquoi cela compte*. `gitpr mentor` (et le flag `--mentor` sur `-r`/`-f`) enrichit les constats du dernier review avec ce qui se passe, pourquoi cela compte, une analogie du quotidien et les concepts à étudier. C'est **opt-in** et cela n'invente jamais : avec des preuves insuffisantes, l'explication le dit au lieu de remplir le vide.
- **Score de risque déterministe — sans IA, sans quota :** `gitpr risk` produit un score de 0 à 100 avec un niveau `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` à partir de signaux locaux (chemins critiques, tests manquants, migrations, historique de bugs et de reverts, constats statiques). La formule est déclarée (**50/30/20**), tout comme les règles d'escalade obligatoires, et le score entre dans le prompt de review comme contexte **en lecture seule** et dans le rapport. Un nombre explicable vaut mieux qu'une supposition coûteuse.
- **Policy Packs — la politique du dépôt est devenue un artefact versionné :** les skills, les règles de linter, les sévérités, les chemins critiques, les poids de risque et les conventions de PR/commit étaient dispersés et chaque dépôt les configurait à la main. `gitpr policy` adopte un pack YAML versionné avec une **somme de contrôle**, résout la précédence entre les couches et consigne le choix dans le lockfile que l'équipe commite. Quatre packs intégrés voyagent dans le wheel ; sans pack actif, **rien ne change**.
- **Baseline et suppressions auditables — le bruit connu cesse de bloquer :** les linters et les reviews re-signalaient les mêmes constats préexistants à chaque exécution, et c'était au reviewer de démêler la sortie dans sa tête. `.gitpr/baseline.json` enregistre l'instantané des constats acceptés, chaque nouveau constat est classé `new`/`existing`/`resolved`/`ignored`/`accepted_debt`, et **seul `new` bloque**. Chaque suppression porte une raison ; la dette acceptée porte un propriétaire et, quand il y en a un, une échéance.
- **Le modèle de constat est devenu un type canonique :** `NormalizedFinding` a déménagé de `infrastructure/linter/external/base_bridge.py` vers `src/domain/finding/`. Sans cela, chaque pont — Semgrep, Gitleaks, Bandit, le ruleset de regex — calculerait l'empreinte d'une forme différente du même constat et la baseline n'aurait aucun moyen de le reconnaître.
- **La dette de la fenêtre précédente a été close :** `SKILL_LABELS` et `mcp_server.SKILL_FILES` ont reçu `tests`, `explain` et désormais `mentor` ; les **40 clés `__()`** qui n'existaient dans aucun dictionnaire ont été traduites ; et les **4 échecs** de la fenêtre précédente n'existent plus. Les trois assertions qui signalaient la dette tournent toujours — elles ont simplement cessé de signaler. La suite, mesurée de bout en bout dans cette fenêtre, se referme à **2795 réussis sur 2799** — les deux échecs restants sont un bug d'isolation dans `tests/test_mentor_cli.py`, pas une régression du produit (voir §Tests).
- **La couverture i18n a bouclé la boucle :** **1603 clés** dans chacun des 6 dictionnaires (+445 dans cette fenêtre) et **zéro clé utilisée dans le code sans traduction** — le code en utilise 1603, les dictionnaires en ont 1603.
- **Les traductions en attente ont été soldées :** `split-command` et `otimizacao-de-tokens` ont complété les 5 langues ; `review-pr` en a encore 2. Deux familles uniquement PT (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) ont déménagé du haut de `docs/` vers `docs/extra/`.

- **Version actuelle :** 1.4.0 (**taguée** — `v1.4.0`, merge de la PR #207, 2026-10-12 ; `v1.3.0` le 2026-09-24)
- **Version des dictionnaires de langue :** v0.0.34 (chaîne v0.0.32 → v0.0.33 → v0.0.34 dans cette fenêtre)
- **Version des scripts de hook :** v0.0.4 (v0.0.3 → v0.0.4 dans cette fenêtre)
- **Publication :** PyPI (`pip install gitpr-cli`) — canal binaire supprimé
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licence :** LGPL-2.1
- **Langues prises en charge :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues, 6 dictionnaires)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **CLI Framework :** Click (pour les commandes, flags et formatage du terminal) — **14 sous-commandes** 🆕 (`badge`, **`baseline`** 🆕, `config`, `demo`, `explain`, `fix`, **`mentor`** 🆕, **`metrics`** 🆕, **`policy`** 🆕, `release`, `review-pr`, **`risk`** 🆕, `split`, `tests`). Quatre d'entre elles sont des **groupes** : `baseline` (6 sous-commandes), `metrics` (8), `policy` (7) et `tests` (1).
* **UI/Terminal :** Textual — TUI pour le chat interactif, l'édition d'issues, l'écran d'aide, le tableau de bord de métriques, le PR Publisher, les erreurs du linter (`LinterApp`), la configuration (`ConfigApp`), le modal `NoticeScreen`, la **visite `gitpr demo`** (`src/ui/demo/`) et l'**assistant de migration du ledger** (`src/ui/metrics_migration_app.py`) 🆕.
* **Architecture en Couches :** `src/domain/` (règles pures, sans I/O) + `src/application/use_cases/` (orchestration, **14 cas d'usage** 🆕) + présentation (CLI/TUI). **8 paquets de domaine** 🆕 : `baseline`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`.
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API, des jetons GitHub et des jetons SCM des forges.
* **Configuration :** `python-dotenv`, `pyyaml` (linter statique) + son propre schéma déclaratif (`src/config_schema.py`, **80 `ConfigField`** et **16 catégories** 🆕).
* **Fournisseurs IA :** Intégration via le SDK officiel Google GenAI, le SDK OpenAI (DeepSeek) et le SDK OpenAI (Ollama local). Défauts configurés : `gemini-pro-latest` / `gemini-flash-lite-latest` et `deepseek-v4-pro` / `deepseek-v4-flash`.
* **APIs de Forge :** `requests` (REST) — abstraction multi-forge dans `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + **`web_links.py`** 🆕 (URL de commit, de PR et de profil par forge, fonctions pures) ; `src/github_api.py` conservé comme shim déprécié.
* **Linter / SAST :** Regex YAML + pont Checkstyle + ruleset de secrets intégré (`src/security_ruleset.py`) + 4 ponts dans `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), avec un modèle normalisé dans `src/domain/linter/sast_finding_mapper.py`. 🆕 Le constat normalisé vit désormais dans **`src/domain/finding/`** et il existe un **canal latéral `findings_out`** qui expose les constats structurés aux côtés des chaînes d'alerte.
* **Interne de Git :** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, **`identity.py`** 🆕 (étiquette de dépôt, de branche et d'auteur en un seul appel git), **`risk_history_reader.py`** 🆕 et **`test_matcher.py`** 🆕.
* **Persistance locale 🆕 :** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) avec WAL, `PRAGMA user_version`, clés UUID et transport par bundle.
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **16 outils annotés** 🆕, **19 ressources** 🆕, 7 prompts.
* **Tests :** Pytest + `unittest.mock` (**129 modules de test** 🆕 — 49 à la racine, 11 dans `tests/application/use_cases/`, 10 dans `tests/fix/`, 9 dans `tests/scm/`, 7 dans `tests/badge/`, 6 dans `tests/demo/`, 6 dans `tests/split/`, 5 dans `tests/domain/baseline/`, 4 dans `tests/domain/policy/`, 4 dans `tests/review/`, 4 dans `tests/infrastructure/linter/external/`, 2 dans `tests/domain/risk/`, 2 dans `tests/domain/tests_generation/`, 2 dans `tests/integration/`, 2 dans `tests/infrastructure/git/`, 1 dans `tests/domain/finding/`, 1 dans `tests/domain/mentor/`, 1 dans `tests/domain/pr/`, 2 dans `tests/domain/linter/`, 1 dans `tests/infrastructure/baseline/` —, **2799 scénarios collectés**) + tests e2e du serveur MCP via un vrai subprocess (JSON-RPC stdio) + fixtures de dépôt git réel (`tests/fix/`, `tests/split/`) + une **garde d'isolation réseau** (`tests/demo/conftest.py`, `tests/integration/conftest.py`).
* **Empaquetage :** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}` ; **packs embarqués via `package-data`** 🆕 et la nouvelle dépendance **`packaging>=23.0`** 🆕.
* **CI/CD :** GitHub Actions — `pr-review.yml` + `action.yml` + `tests.yml` (matrice Python 3.10/3.13).

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec le LLM en demandant une sortie strictement JSON.
* **Map-Reduce (Diffs Géants) :** Lorsque le diff dépasse ~90k tokens, le divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce). Prend en charge les PRs, commits et Issues. 🆕 `provider`/`model` sont désormais **agrégés à travers les lots** et `meta`/`modules` sont transmis à `log_command_metric`, qui copie désormais les vrais tokens depuis les métadonnées du fournisseur au moment de l'écriture.
* **Tokenizer Local :** `tokenizer.json` pour une estimation précise des tokens avant l'envoi à l'IA.
* **Estimation des Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()` avec fallback vers le tokenizer local.
* **Optimisation Native de Git :** Flags `-U1`, `-w`, `-M`, `-B` sur les commandes `get_git_diff` et `get_git_full_diff` pour réduire le contexte inutile.
* **`get_split_diff()` :** Le diff propre au `split`, avec `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **délibérément sans** réutiliser `get_git_diff()` — le `-w` (ignore les espaces) et les smart-excludes casseraient la garantie d'arbre identique au byte près.
* **Pre-Save (`--pre-save`) :** Flag caché de debug qui enregistre le payload complet (instruction système + prompt) en JSON avant chaque appel à l'IA.
* **Smart Excludes à Deux Couches :** Couche globale (`~/.gitpr/conf/`) + couche locale du projet (`./.gitpr/conf/`), fusionnées au runtime. Auto-seed du fichier local à la première exécution. `_load_smart_excludes()` accepte `force=`.
* **Métriques avec Suivi du Temps :** `log_command_metric()` dans tous les flux, désormais avec `meta`, `modules` et `duration_ms`. 🆕 Deux copies divergentes de la carte commande→dossier de cache ont été **supprimées** d'ici — elles étaient la cause de `tokens_actual` toujours à `0`.
* **Résolution Centralisée de la Sortie :** `resolve_output_path()` — par défaut dans `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`) :** `gitpr --init` — détecte la forge depuis le remote origin, demande les extras propres à chaque forge, valide le jeton et persiste **uniquement en cas de succès** ; annonce le badge automatique sur le chemin du PR.
* **Template de Skill de Release (`ensure_release_skill_template()`) :** Télécharge `templates/gitpr.release.*.md` à la première utilisation de `gitpr release`.
* **Registre de Skills Partagé :** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` dans `src/config.py`. 🆕 Il a gagné l'entrée **`mentor`** (`.gitpr.mentor.md`) — `tests` et `explain` y étaient déjà dans `tests/test_skill_context.py`.
* **Moteur de Review avec Périmètre de Cache (`generate_pr_content`) :** `cache_scope` (attaché **uniquement à la clé de cache**) et `store_diff` (écrit le diff révisé). 🆕 `cache_scope` porte désormais aussi le **pack de policy actif**, pour que deux dépôts sous des politiques différentes ne partagent pas une réponse.
* **Trailer de Co-paternité :** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, préserve les trailers de tiers.
* **Subprocesses Blindés :** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` sur chaque `subprocess.run` ; vérification de connexion via socket `8.8.8.8:53` avant les opérations réseau.

### **2. Système Global de Plugins (`src/plugins.py`)**

* **Architecture de Plugins :** Charge les plugins depuis `~/.gitpr/plugins/`, en s'appliquant à **tous les projets**.
* **Plugins de Linter (`linter/`) :** Fichiers `.yml` fusionnés avec le `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`) :** Fichiers `.md` qui étendent le contexte système.
* **Factory Closures :** `get_linter_plugins` et `get_prompt_plugins` isolent l'état entre les sessions.
* **Commande `--plugins` :** Liste tous les plugins globaux installés avec leurs types et chemins.
* **Documentation Multilingue :** `docs/plugins-system.md` en 5 langues.
* 🆕 **Relation avec les Policy Packs :** le pack est une **troisième** couche de règles — les plugins appartiennent à la machine, `.gitpr.linter.yml` appartient au projet et le pack est versionné et commité. `load_linter_rules()` fusionne les trois dans l'ordre déclaré, avec les sévérités du pack au-dessus.

### **3. Interface CLI et Configuration (`src/main.py` et `src/config.py`)**

* **Setup Initial :** Détecte la première exécution, crée `~/.gitpr/` et demande interactivement les clés, les préférences et la langue. Le hint de première exécution pointe vers `gitpr demo`.
* **Routage des Commandes :** Gère tous les flags et les **14 sous-commandes** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, **`mentor`** 🆕, **`metrics`** 🆕, **`policy`** 🆕, **`risk`** 🆕 et **`baseline`** 🆕.
* **Comportement par Défaut :** Exécuter `gitpr` sans flags ouvre la TUI du PR Publisher.
* **Flags (désormais 32 options Click à la racine — 5 sorties, 1 entrée dans cette fenêtre) :**
  * **Supprimés 🆕 :** `--metrics`, `--export`, `--purge`, `--hook-event` et `--dashboard` — ils sont devenus le groupe `gitpr metrics` (voir §36). **Rupture de CLI déclarée.**
  * **`--mentor` 🆕 :** Ajoute la section Mentor didactique au code review (`-r` / `-f`).
  * `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` — inchangés.
* **Groupe `baseline` 🆕 — 6 sous-commandes :** `create` (`--refresh`, `--base`, `--yes`, `--format`), `show` (`--status`, `--rule`, `--file`, `--format`), `validate` (`--format`), `update` (`--recompute`, `--base`, `--yes`), `suppress` (`--reason`, `--scope`, `--debt`, `--owner`, `--due-date`, `--yes`) et `unsuppress` (`--yes`).
* **Groupe `policy` 🆕 — 7 sous-commandes :** `list`, `show`, `validate`, `use`, `off`, `install` (`--force`, `--yes`) et `init` (`--stack`, `--yes`).
* **Groupe `metrics` 🆕 — 8 sous-commandes :** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` et le `hook-event` caché ; les commandes de lecture partagent `--days`/`--since`/`--until`.
* **Sous-commande `risk` 🆕 — 4 options :** `--file`, `--format text|json`, `--base`, `--no-history`.
* **Sous-commande `mentor` 🆕 — 2 options :** `--finding`, `--provider`.
* **Variables d'Environnement (58 clés dans `DEFAULT_CONFIG`, +9 dans cette fenêtre) :** les quatre `GITPR_BASELINE_*` (`ENABLED`, `PATH`, `REQUIRE_LOCKFILE_CHECKSUM_MATCH`, `ALLOW_LOCAL_OVERRIDES`), les deux de mentor (`GITPR_REVIEW_MENTOR_MODE`, `GITPR_MENTOR_INCLUDE_ANALOGY`), les deux de policy (`GITPR_POLICY_ENABLED`, `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`) et `GITPR_RISK_INCLUDE_IN_REVIEW`. **Hors `DEFAULT_CONFIG` :** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT` et `GITPR_METRICS_CURRENCY`, lus par `get_model_price()`.
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique du flag avec un lien tenant compte de la langue ; les sous-commandes ont leur propre `epilog=` (paragraphe `\b` de Click). 🆕 `risk`, `policy`, `baseline` et `mentor` ont rejoint `HELP_MAP`/`HELP_PRIORITY`.
* **Couche d'Écriture du `.env` :** `read_env_file_values()` lit **uniquement le fichier** via `dotenv_values` ; `save_config_values()` avec `set_key` ; `remove_config_value()` avec `unset_key`. `validate_ai_key()` distingue un identifiant refusé (`401`/`403`) d'un réseau injoignable.
* **Barrière Baseline dans le Flux 🆕 :** `main.py` résout la barrière de baseline avant le lint, le review, le full review, l'audit de fichier, le review distant et le risque, et le **code de sortie du lint suit désormais le blocage tenant compte de la baseline** — un nouveau constat échoue, un constat connu non.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)**

* **Interface Interactive Complète :** TUI pour réviser, éditer et publier des Pull Requests directement depuis le terminal.
* **7 Écrans Modaux :** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` et `NoticeScreen`.
* **Badge dans le Corps du PR :** `attach_pr_badge()` s'exécute dans `src/main.py` juste après que `pr_data` est complet — **point d'injection unique**. Ajout idempotent par marqueur et divulgation explicite sur `--no-edit`.
* **`_attach_reviewers` avec résolution :** Résout ce que l'utilisateur a saisi **avant** l'envoi, signale ce qui a été écarté et, sur un `422` de lot, **réessaie un par un**.
* **Reviewers Suggérés :** Interroge la forge pour obtenir des reviewers suggérés ; `_reviewer_suggestion_view()` assemble les `resolutions` via `resolve_candidates` et pré-remplit `handles`.
* **Bindings :** F1 (Aide), F2 (Enregistrer .md local), F3 (Publier via la forge), Échap (Quitter).
* **Flux d'Auto-Commit :** Linter → message IA → confirmation → commit → push → publie le PR. 🆕 Le résultat du linter porte désormais les **constats structurés** (`findings_out`), et c'est ce que la baseline classe avant que le rapport soit composé.
* **Gestion de PR Existant / Auto-Upstream / Flux de Merge** — inchangés (`GITPR_AUTO_MERGE`).

### **5. Module API GitHub (`src/github_api.py`)**

* **Shim Déprécié :** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` et les autres fonctions délèguent à `src/infrastructure/scm/github_provider.py` ; il émet un `DeprecationWarning` et conserve les tuples legacy `(ok, data, status)` — aucun nouveau code ne doit l'importer.

### **6. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le diff sans dépenser de quotas d'IA.
* **Règles YAML :** Lit `.gitpr.linter.yml` (créé via `--skill`).
* **Plugins de Linter :** Règles supplémentaires depuis `~/.gitpr/plugins/linter/*.yml`.
* **Wildcard d'Extension :** `extensions: ["*"]` signifie **tous** les fichiers, y compris ceux sans suffixe et les dotfiles.
* **Ruleset de Secrets Intégré :** `load_linter_rules()` fusionne `src/security_ruleset.py` **après** les règles du projet et des plugins. Sept règles, toutes `extensions: ["*"]` : cinq `error` et deux `warning`. L'alerte **n'affiche jamais la valeur** — le message ne porte que `{file_name}` et `{line_number}`.
* 🆕 **Couche des Policy Packs :** `load_linter_rules()` applique désormais la fusion en couches du pack actif et **écrase les sévérités**, sans toucher au `.gitpr.linter.yml` ni aux plugins.
* 🆕 **Constats Structurés (`findings_out`) :** en plus des chaînes d'alerte, le moteur retourne les constats normalisés du type canonique `src/domain/finding/` — c'est cette liste que la baseline prend en empreinte. Il inclut le correctif du compteur de lignes du côté nouveau, pour que les lignes de contexte avancent correctement.
* **Ponts SAST Opt-In :** Semgrep, Gitleaks et Bandit tournent sur le chemin de fichier entier **et** sur le chemin de diff, avec la dédup `[Gitleaks + Regex]`, le masquage des valeurs (`AKIA****`) et un avertissement lorsque l'outil est activé mais absent du `PATH`.
* **`skip_external` :** `parse_diff_and_lint(..., skip_external=True)` dans le flux distant, parce que le pont exécute des binaires contre des fichiers sur le disque — l'arbre local, pas le PR.
* **Rapport Consolidé :** `generate_linter_report_content()` consolide les erreurs regex + externes dans `.gitpr/reports/linter/` — généré uniquement en cas de violations.
* 🆕 **Sensible à la baseline :** les alertes sont annotées avec le statut de baseline avant de devenir un rapport, et le code de sortie du flux de lint suit le blocage tenant compte de la baseline.

### **7. Sécurité et Authentification (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Chiffrement :** Génère la clé maîtresse `secret.key` dans `~/.gitpr/`.
* **Protection des Jetons :** `encrypt_data`/`decrypt_data` pour les clés d'IA, le PAT GitHub et les jetons SCM des forges.
* **Validation Multi-Forge :** `validate_or_request_scm_token(provider, repo_display)` — 401 → boucle de réauthentification préservant le brouillon.
* **Secrets dans la TUI de Configuration :** Les champs `KIND_SECRET` sont édités masqués, **n'affichent jamais** la valeur en clair et sont chiffrés avec Fernet avant d'être écrits.
* **Analyse de Secrets dans le Flux :** Le linter — qui tourne dans le hook pre-commit — est une barrière d'identifiants avec des règles identiques sur chaque machine.
* ⚠️ **Lacunes de couverture confirmées encore ouvertes dans cette fenêtre :** la règle générique exige toujours des guillemets après le séparateur (`\s*[:=]\s*['\"]`), donc le format `.env` **sans guillemets** (`API_KEY=abc123`) n'est toujours pas détecté ; les préfixes `ASIA…`, `github_pat_…` et `xoxc-`/`xoxd-` restent dehors (le motif est `gh[pousr]_` et `xox[baprs]-`) ; et il n'y a pas de frontière gauche sur le nom de la clé, donc `mytoken` correspond autant que `token`.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI comme Source Unique :** `get_latest_remote_version()` interroge `https://pypi.org/pypi/gitpr-cli/json` et écrit le cache quotidien **sans** le champ `download_url`.
* **Barrière Obligatoire (`enforce_update_required()`) :** Retourne `True` (après avoir affiché les deux versions et la commande pip) lorsque la version publiée est plus récente ; `False` lorsque tout est à jour, lorsque la version distante est **inconnue (hors ligne)** ou lorsque la vérification est désactivée.
* **`check_and_update()` :** Se contente d'interroger et d'**informer**, n'installe jamais.
* **Échappatoire :** `GITPR_SKIP_UPDATE_CHECK` — elle n'est pas annoncée ; elle existe pour la suite et pour l'automatisation hors ligne.
* **Versionnage Centralisé :** `__version__` (**1.4.0** 🆕), `__lang_version__` (**v0.0.34** 🆕 — chaîne v0.0.31 → **v0.0.32** (release notes) → **v0.0.33** (mentor) → **v0.0.34** (bump de release)), `__scripts_version__` (**v0.0.4** 🆕), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* 🆕 **Dans cette fenêtre, les trois bumps ont quitté l'arbre de travail et sont devenus un commit :** `af35270` a relevé GitPR, les langues et les scripts ensemble, et `CHANGELOG.md` a cessé d'annoncer une version que le paquet ne construisait pas.
* ⚠️ **Correctif en attente dans l'arbre de travail :** les titres de section de `[1.4.0]` sont sortis localisés (`Funcionalidades`, `Documentação`, `Tarefas`, `Contribuidores`) et ont été corrigés en anglais **sans commit** — c'est le seul changement ouvert dans le dépôt aujourd'hui.

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
* **Fichiers Versionnés :** `__lang_version__` (**v0.0.34**) contrôle la mise à jour des packs (`langs/*.json`).
* **Couverture :** **1603 clés** dans chacun des 6 fichiers — parité totale des key sets **et couverture totale du code** : le code utilise **1603** clés et **aucune** ne reste sans traduction (+445 dans cette fenêtre ; la dette de 40 clés de la fenêtre précédente a été close). ✅
* **Photo de l'Environnement (`AMBIENT_ENV_KEYS`) :** `frozenset(os.environ)` capturé **immédiatement avant** le `load_dotenv()` au niveau du module.
* **Cache avec Indexation par Langue :** Les réponses IA en cache incluent la langue courante dans le keying MD5.
* **Source de Traduction en Phase :** une nouvelle clé doit exister dans le code, dans `langs/pt_br.json` (liste maîtresse), dans les dicts FR/ES de `scripts/sync_all_langs.py` et dans les valeurs curatées de `scripts/fix_mangled_i18n_keys.py`.

### **11. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA avec des caractères braille et des mots de « réflexion ».
* **263 entrées par langue :** Synchronisées entre les 5 langues. `_load_thinking_words()` / `reload_thinking_words()` acceptent `force=`.

### **12. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Prise en charge :** Google Gemini, DeepSeek et Ollama (local), avec un primaire/secondaire par fournisseur.
* **Mode JSON & Paramètres Déterministes :** `temperature=0.0` et `top_p=0.1` ; fallback automatique entre les fournisseurs configurés.
* **Timeout IA :** défaut de **180s** (`GITPR_AI_TIMEOUT`).
* 🆕 **Appels avec `quiet` :** la release et les nouvelles fonctionnalités transmettent `quiet` jusqu'à la couche IA, pour que le spinner ne corrompe jamais une sortie `--format json`.
* 🆕 **Un appel à la fois, avec cache :** mentor et explain réutilisent le cache MD5 par prompt — relancer sur un review inchangé ne coûte rien.

### **13. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Keying par hash MD5 du diff et du prompt, avec indexation par langue.
* **Sélection du Dernier Review (`resolve_last_review()`) :** Choisit l'enregistrement `review`/`fullreview` **le plus récent** pour un couple repo+branche — c'est la porte d'entrée de `gitpr fix` et, désormais, de `gitpr mentor` aussi.
* **Diff Révisé (`reviewed_diff`) :** Champ en tête d'enregistrement qui conserve le diff effectivement révisé, préféré par `fix/apply_fix.reviewed_diff()`.
* **Télémétrie et Durée :** Persistance de `duration_ms` et `meta_raw`.
* **Lecture pour le Dashboard :** `scan_cache_files_for_dashboard()` a été **rétrogradé en pont de pré-migration** — le dashboard lit le ledger, et le scan n'existe que pour reconstruire les séries depuis le cache quand l'utilisateur le choisit dans l'assistant de migration.

### **14. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff actuel, Historique de la branche (`-ht`) et Archéologie par Blame (`-b`).
* **Publication Multi-Forge :** F3 crée l'issue sur la forge **configurée** (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — Azure DevOps lève `ScmNotSupportedError`.
* **Map-Reduce pour les Issues :** Le contexte au-dessus de ~90k tokens est découpé et unifié.
* **Gestion du 401 :** Signalisation de réauthentification sans fermer l'application.

### **15. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Suit l'évolution et la paternité historique avec classification des commits (`ORIGIN` vs `REFACTORING`).
* **Métriques de Blame :** Événements via `log_blame_metric()` avec la profondeur et le nombre de commits analysés.
* 🆕 **Il alimente désormais aussi le risque :** le lecteur d'historique du score (`risk_history_reader.py`) utilise la même fenêtre de 90 jours et classe bugs/reverts par motif, sans appeler l'IA.

### **16. Serveur MCP et Invocation CLI Directe (`src/mcp_server.py`)**

* **16 Outils MCP Annotés 🆕 :** les 14 précédents + **`get_baseline_status`** 🆕 (le digest de la baseline : comptages par statut, top règles, suppressions et dette acceptée avec propriétaire et échéance, dette en retard, état de la somme de contrôle — **ne lance jamais le linter, n'appelle jamais l'IA, n'écrit jamais) et **`get_usage_metrics`** 🆕 (le ledger d'utilisation sur une fenêtre : exécutions, coût par modèle, modules touchés, fournisseurs et taux de qualité ; pas de raccourci de jour — l'appelant nomme ses propres dates).
* **19 Ressources + 7 Prompts Templatisés 🆕 :** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + **`baseline://summary`** 🆕 + `prompt://list` + 7 prompts.
* **Registre de Skills Aligné 🆕 :** `mcp_server.SKILL_FILES` a désormais **11 entrées** — il a reçu `tests`, `explain` et `mentor`. L'assertion `TestSkillRegistryAgreement`, qui signalait la divergence, **passe** désormais. ✅
* **Invocation CLI Directe :** `gitpr-mcp --tool <name> [--tool-args '<json>']` invoque n'importe quel outil sans démarrer le serveur stdio ; `gitpr-mcp --list` affiche le registre complet en JSON.
* **Isolation du Stdout Réel :** `_write_real_stdout()` écrit dans le `sys.__stdout__` original, avec un fallback vers `buffer` ou un ré-encodage avec `errors='replace'` sur les code pages Windows legacy.
* **Offload de l'Event Loop :** Décorateur `_offload` (`anyio.to_thread.run_sync`) sur chaque outil — l'ordre des décorateurs compte (`@mcp.tool` **au-dessus** de `@_offload`).
* **Échecs comme Erreurs Structurées 🆕 :** les nouveaux outils retournent un échec **comme donnée** au lieu de lever — un agent qui demande le statut de la baseline reçoit la raison de l'échec, pas une exception.

### **17. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`)**

* **Périmètre par Dépôt :** Étiquette `📁 Repository: owner/repo` et filtrage strict par projet.
* **Scan Asynchrone avec Overlay :** Worker thread en arrière-plan avec une `ProgressBar` ; les tests attendent `workers.wait_for_complete()` avant d'affirmer.
* 🆕 **Lecture du Ledger :** le dashboard a cessé de scanner des fichiers JSON et interroge désormais `telemetry.db` sur le worker thread, en rendant les **cinq nouvelles sections** (coût, qualité, modules, fournisseurs, cycle) à partir des mêmes renderers que la CLI utilise.
* 🆕 **Assistant de Migration (`src/ui/metrics_migration_app.py`) :** propose d'importer les fichiers legacy, d'importer **et** reconstruire depuis le cache d'IA, ou « pas maintenant » — avec une sémantique d'abandon qui **ne crée jamais un ledger vide** par-dessus des fichiers pas encore importés.
* **Export Local :** CSV/JSON dans `./.gitpr/metrics/export/` — 🆕 `.gitpr/metrics/` est entré dans `.gitignore` avec un commentaire expliquant pourquoi `.gitpr/skill/` **non** (c'est du contenu de projet). Les 35 artefacts déjà suivis restent suivis, parce que `gitignore` ne les retire pas du suivi.

### **18. Système de Métriques et Télémétrie (`src/metrics.py`)**

* 🆕 **Réécrit dans cette fenêtre — voir §36.** `src/metrics.py` a cessé d'écrire des fichiers et opère désormais sur des **lignes de ledger** ; `enrich_metrics_from_cache()` et `load_cache_token_summary()` ont été **supprimés** (c'était la correspondance heuristique qui mettait `tokens_actual` à zéro).
* **Périmètre par Dépôt :** Tous les événements indexés par `repo_name`, désormais résolus par `src/infrastructure/git/identity.py` — qui fonctionne sur **n'importe quelle** forge, pas seulement GitHub.
* **Événements de Hook, Linter et Blame :** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Export et Nettoyage :** `gitpr metrics export` (CSV/JSON), `prune`, `purge` avec confirmation interactive, `bundle`/`merge` pour transporter et fusionner des fenêtres.

### **19. Synchronisation de la Langue des Git Hooks**

* **Versionnage Indépendant :** `__scripts_version__` (**v0.0.4** 🆕).
* 🆕 **Templates réécrits :** `post-checkout`, `post-merge` et `pre-push` appellent désormais `gitpr --quiet metrics hook-event <name>`, gardés par `command -v gitpr` et `|| true` — la résolution du dépôt en bash, qui était **GitHub uniquement** et enregistrait les événements sous des noms de dossiers inutilisables, a été supprimée. **Action requise : réinstaller les hooks avec `gitpr --installhooks`.**
* **Correspondance des Suffixes (`HOOK_SCRIPT_SUFFIXES`) :** Les codes d'interface (`es_es`, `fr_fr`) traduits vers les suffixes publiés (`.es`, `.fr`).
* **Choix vs. État (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`) :** Cela permet de **détecter un changement de langue**.
* **`effective_hook_lang()` :** Résout la langue effective ; `--lang` n'est plus écarté sur ce chemin.
* **Skip de Merge-Source :** `prepare-commit-msg` saute les sources `message|merge|squash|commit`.

### **20. Pont de Linters Externes et Assistant Interactif (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistant `--linter-setup` :** Wizard avec presets numérotés (PHP_CodeSniffer, ESLint, Stylelint) et injection du bloc `external_linters`.
* **Presets Distants :** `templates/gitpr.linter-presets.json` avec la chaîne de fallback local → téléchargement → stale → embarqué.
* **TUI d'Erreurs du Linter :** `src/ui/linter_app.py` (Textual) affiche les erreurs critiques et les warnings ; en mode hook/quiet, imprime et fait `sys.exit(1)`.
* **Rapport Markdown :** Consolidé dans `.gitpr/reports/linter/` uniquement en cas de violations.
* **La Couche SAST est une Sœur, Pas un Remplacement :** les ponts Semgrep/Gitleaks/Bandit alimentent le **même** pipeline de rapport.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstraction Unique (`ScmProvider` ABC) :** `base.py` définit le contrat (dataclasses `RepoRef`, `PullRequestDraft` etc. et `ScmProviderError(provider, http_status, message)` — `http_status` 0 = échec réseau).
* **Registre et Factory :** `resolve_scm_provider()` sélectionne via `GITPR_SCM_PROVIDER` (défaut `github`) ; `detect_provider_from_remote()` identifie la forge à partir de l'URL origin.
* 🆕 **`list_pull_requests(repo, state, since, until)` — paginé et canonique :** les quatre providers traduisent les états canoniques (`open`/`merged`/`closed`/`all`) dans le vocabulaire natif, paginent sur le curseur de chaque API (Link, `X-Next-Page`, continuation token, next-URL), filtrent par une fenêtre de dates inclusive et s'arrêtent tôt quand ils le peuvent. **`list_open_pull_requests(repo)` est devenu un mince wrapper** sur la classe de base — aucun call site n'a changé, mais la limite d'une page a disparu.
* 🆕 **Pourquoi le filtre porte sur la date de l'état :** trois des quatre forges retournent quelque chose de plus large pour un PR fusionné (GitHub n'a pas d'état `merged`, Azure l'appelle `completed`, Bitbucket dit `DECLINED`), donc chacune filtre sa propre réponse sur la date qu'implique l'état — `merged_at` pour fusionné, `closed_at` pour fermé, `created_at` pour le reste.
* 🆕 **`supports_merged_dates` :** Attribut de classe, `False` sur Bitbucket Cloud, qui publie `merged_at` **vide** au lieu d'une date proche — déclaré **avant** l'appel, comme `supports_reviewable_diff`.
* 🆕 **`web_links.py` — GitPR a commencé à construire des liens HTML :** jusqu'à cette fenêtre, chaque lien arrivait prêt dans le `html_url` du payload. Les fonctions pures (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) normalisent n'importe quel format de remote — `https`, `ssh://`, `git://`, scp-like, avec identifiants, port personnalisé et **sous-groupes GitLab imbriqués** — vers une base https et appliquent le patron de page de chaque forge. Azure DevOps n'a pas d'URL de profil simple, donc ses contributeurs conservent le nom d'affichage.
* **`get_pull_request(repo, pr_id)` — méthode concrète de l'ABC :** le défaut lève `ScmNotSupportedError` et chaque forge l'implémente — `list_open_pull_requests` ne pagine qu'une seule page et ne distingue pas fermé d'inexistant.
* **En-têtes GitLab Synthétisés :** `changes[].diff` est un **hunk isolé** ; `old_path`/`new_path` assemblent les en-têtes `diff --git a/… / --- / +++`, en honorant `/dev/null`. Et `overflow: true` **lève** au lieu de réviser la moitié d'un diff tronqué.
* **`request_pull_request_reviewers` renvoie `list[str]` (rupture de contrat) :** Il retourne les logins **effectivement attachés**, lus dans le corps du `201`. Les sous-classes tierces doivent être mises à jour — **élément hérité, toujours non documenté.**
* **Helpers en Lecture Seule de GitHub :** `get_commit_author_login` (SHA → compte lié à l'e-mail de l'auteur) et `get_user_login` (valide/canonicalise un handle sans dépenser de requête quand il ne peut pas être un login).
* **Fail-Fast par Forge :** Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` ; Bitbucket exige `GITPR_SCM_USERNAME` ; `create_issue` sur Azure lève `ScmNotSupportedError`.
* **Publication de Release :** `provider.create_release()` utilisé par `gitpr release --publish`.
* **Artefacts :** Glossaire + ADR-001/ADR-005 ; la famille `docs/scm-multiforge.*.md` en 5 langues ; 9 fichiers de test, **305 scénarios** 🆕.

### **22. Sous-commande `gitpr release` — Changelog / Release Notes (`src/release_engine.py`)**

* **Flux :** `git log` entre `--since` et `HEAD` → classification par Conventional Commits → bump sémantique suggéré (`--version <x.y.z>` le remplace) → assemblage du changelog → résumé exécutif IA optionnel → *insertion en tête* de `CHANGELOG.md`.
* 🆕 **La Plage a Cessé de Venir de `git describe` :** `describe --tags --abbrev=0` ne voit que les tags atteignables depuis HEAD — dans ce dépôt, il résolvait vers `v0.0.8`, donc **chaque release listait toutes les versions précédentes**. La section `[1.3.0]` est partie avec **124 puces, dont 115 répétaient la 1.2.0**, et le bump suggéré pouvait être **inférieur** à la version déjà publiée. L'origine de la plage est devenue la **section précédente du changelog lui-même**, via deux ancres : le hash le plus récent de cette section (résolu en un seul `git log --no-walk`, trié par epoch pour survivre aux fuseaux horaires mélangés) et, en secours, le tag de la version de la section ; sans ni l'un ni l'autre, elle dégrade vers le dernier tag **avec un avertissement visible**.
* 🆕 **`_drop_released()` — la deuxième ligne de défense :** tout commit dont le hash court apparaît dans **une autre** section de version est écarté, avec un comptage. Quand rien ne survit, l'exécution **avorte** avec `❌ Nothing new to release` au lieu d'écrire une section vide.
* 🆕 **Chaque puce est devenue un lien :** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`. Le numéro de PR vient du `(#123)` final du squash-merge, qui demeure dans le sujet ; sans forge ou sans jeton, cela dégrade vers du texte brut.
* 🆕 **L'échec d'un PR fusionné par merge commit — corrigé par construction :** `git log --no-merges` veut dire qu'un merge commit n'atteint **jamais** le classifier, donc `pr_number` était `None` **par construction** et aucun changelog n'a jamais eu de segment `[#n]`. Une **seconde passe sur la même plage** (`git log --merges`) construit `hash → PR` à partir de l'ascendance (`rev-list <merge>^1..<merge>^2`) : les puces restent une par commit, désormais avec le lien. Un merge qui n'est pas une pull request (`Merge branch 'main' into …`) ne prête pas de numéro, un commit atteignable par deux merges garde le plus proche, et le correctif **n'est pas rétroactif** — seule une section fraîchement coupée gagne des liens.
* 🆕 **Contributeurs résolus par la même échelle que les reviewers :** `get_commit_author_login` d'abord (il voit les adresses privées que la recherche d'utilisateurs ne voit pas), puis `email_to_handle`, mis en cache dans `~/.gitpr/cache/contributors.json` — **seuls les succès sont mis en cache**, donc une exécution limitée par le rate limit réessaie plus tard.
* 🆕 **La Frontière de Section était un Bug Latent :** `upsert_changelog(force=True)` remplace de la ligne `## [x.y.z]` jusqu'au prochain en-tête de **niveau 2**. Un `###` comme frontière laissait l'ancien corps vivant **au-dessus** du nouveau ; le `### Summary` et le pied de page des contributeurs restaient derrière.
* **Classifier (`src/commit_classifier.py`)** et **Builder avec Sections Traduisibles (`src/changelog_builder.py`)** — 🆕 le builder a gagné la dataclass `LinkContext` et le classifier a gagné `pr_number_from_merge()`, qui comprend les merges GitHub, GitLab, Bitbucket et Azure DevOps.
* **Bump Sémantique (`src/version_bump.py`)** et **Publication** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 options dans la sous-commande.**
* **Ruptures déclarées :** `ReleaseNotesResult` a gagné le champ obligatoire `previous_version` ; `build_release_section` a gagné le paramètre optionnel `links` (compatible en arrière).
* **Artefacts :** `docs/release-notes.*.md` — la famille existait déjà en 5 langues et a été **réécrite dans cette fenêtre** pour le moteur corrigé 🆕 —, ADR et `tests/test_web_links.py`.
* **État de la release 1.4.0 :** `CHANGELOG.md` a la section complète `[1.4.0] - 2026-10-12` — les cinq fonctionnalités de cette fenêtre **plus** la note 1.3.0, avec des liens. C'est la première section générée par le moteur corrigé.

### **23. Sous-commande `gitpr config` — TUI de Configuration**

* **Écran Maître-Détail (`src/ui/config_app.py`) :** Catégories à gauche, champs à droite, édités en ligne. En-tête avec recherche (`/`) et compteur de modifications en attente ; pied de page `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Schéma Déclaratif (`src/config_schema.py`) — l'unique source de vérité :** **16 catégories** 🆕 (+2 : **Policy** et **Baseline**) et **80 `ConfigField`** 🆕 (+9), dont **8 avancés**. Chaque champ déclare sa catégorie, son type de widget, ses `show_if`, ses validateurs, ses marqueurs de version et ses actions de téléchargement.
* **Catégories `policy` et `baseline` 🆕 :** les champs du pack (activation et plafond de caractères du contexte injecté) et les quatre de baseline, dont le chemin du fichier et le commutateur qui exige la somme de contrôle du lockfile.
* **Section Skills Alignée 🆕 :** `SKILL_LABELS` est passé de 8 à **11 entrées** — il a reçu `tests`, `explain` et `mentor`. Les deux types qui s'affichaient **vides** dans la barre latérale ont désormais un libellé, et les deux assertions de `test_config_schema.py::TestSkillsSection` **passent**. ✅
* **Filtrage par Contexte (`show_if`) :** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` selon `DEFAULT_AI_PROVIDER` ; `GITPR_SCM_USERNAME` sous `[Bitbucket]` ; `GITPR_SCM_ORGANIZATION`/`PROJECT` sous `[Azure DevOps]`.
* **Recherche Globale (`/`) :** Correspond à la clé ou au libellé dans toutes les catégories et **ignore le filtre de visibilité**.
* **Validation en Deux Couches :** **Hors ligne** bloque `F2` avec une erreur en ligne ; **en ligne** (uniquement les identifiants modifiés dans la session) s'exécute dans un worker avec un timeout de 10s et ne bloque que sur `401`/`403`.
* **Restaurer (`Ctrl+R`) :** Supprime la ligne du `.env` au lieu de réécrire le défaut ; `Esc` avec des modifications en attente demande confirmation.
* **Section Skills — la seule au périmètre du projet :** Édite les `.gitpr/skill/*.md` du projet résolus depuis le répertoire appelant, en écrivant de manière atomique et en préservant CRLF/LF.
* **Téléchargements Forcés :** Boutons qui forcent le re-téléchargement des smart-excludes, des traductions, des presets et des thinking words via `force=`.
* **Module Léger de Liens (`src/doc_links.py`) :** `doc_url()` déplacé hors de `core.py` pour que l'UI puisse obtenir le lien sans importer les SDK d'IA.
* **Tests :** 5 fichiers — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9). 🆕 La suite **stabilise désormais l'environnement** avant de monter la TUI — sans cela, un `GITPR_BASELINE_*` fuité depuis le shell changeait l'arbre des catégories.
* **Dette Connue :** `gitpr -h config` ouvre la TUI et ignore `-h` — la barrière `if ctx.invoked_subcommand is not None: return` (main.py:678) s'exécute avant le bloc `help_flag`.

### **24. Journal Général d'Utilisation (`src/usage_log.py`)**

* **Une Ligne par Commande :** Écrit dans `~/.gitpr/logs/<uuid5>.log`, **un fichier par jour**, avec la commande, les arguments, le dépôt, l'utilisateur et l'horodatage.
* **Nom Dérivé de la Date :** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` — deux processus concurrents ne peuvent pas être en désaccord sur le fichier du jour.
* **Écriture Synchrone (décision explicite) :** Contrairement à `log_local_metric`, qui utilisait un thread daemon et perdait l'écriture si le processus se terminait avant.
* **N'Affiche Jamais :** Le serveur MCP réserve stdout au JSON-RPC ; le module ne lève jamais d'exception non plus.
* **Un Seul Spawn Git :** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms au lieu de ~150 ms sous Windows, à *chaque* exécution.
* **Contrôle :** `GITPR_SHOW_LOGS` (défaut `"true"`) ; désactivée dans `tests/conftest.py`.
* **Artefacts :** `docs/usage-log.*.md` en 5 langues ; `tests/test_usage_log.py` (27 scénarios).

### **25. Sous-commande `gitpr fix` — Constats de Review en Patches Révisables (`src/fix/`)**

* **Le Pipeline Réel :** dernier review du cache (`resolve_last_review`) → **un** appel d'IA → extraction et validation du diff unifié → `git apply --check` → classification déterministe → dry run ou écriture → historique.
* **Paquet de 8 Fichiers :** `patch_provenance.py`, `patch_extractor.py` (partagé avec le chat), `patch_safety_classifier.py` (pur, sans I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Classification Déterministe :** Refuse un patch qui traverse plus d'un fichier ou d'un hunk, qui touche un chemin sensible, qui dépasse le budget de lignes, qui **efface une ligne qui ressemble à un appel**, qui arrive marqué à faible confiance ou qui échoue au `git apply --check`. Le `--force` ne contourne **jamais** l'applicabilité, seulement la classification.
* **Écriture Opt-In :** Le dry run est le défaut ; toute mutation exige `--apply` (ou une phrase saisie avec `--force`).
* **Tests :** 10 fichiers dans `tests/fix/` (**204 scénarios**) avec des fixtures de dépôt git réel.

### **26. Sous-commande `gitpr review-pr` — Review de PR Distant (`src/review/`)**

* **Ce Que C'est :** De l'orchestration, **pas** un second moteur de review — le moteur existant, le linter et le renderer alimentés avec un diff venu d'ailleurs.
* **Paquet de 5 Fichiers :** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Lecture Seule par Défaut :** `--post-comment` est le **seul** chemin qui écrit sur la forge. L'outil MCP `review_remote_pr` ne reçoit même pas l'argument.
* 🆕 **La baseline est résolue avant le premier appel réseau**, et les avertissements de la barrière sont fusionnés dans le rapport/commentaire — un PR distant est jugé par la même politique que le dépôt local.
* 🆕 **`render.py` a gagné le paramètre de risque :** `compose_review_content` / `render_review_result` **placent en tête** la section `⚡ Risk Assessment` au-dessus des alertes du linter, quand `GITPR_RISK_INCLUDE_IN_REVIEW` est actif.
* **Artefacts :** `docs/review-pr.md` + `.pt_br.md` (les 3 langues restantes **sont toujours en attente**), `docs/code-review-ia.*.md` (5), ADR-005 et `glossary-review-pr.md`.
* **Tests :** 4 fichiers dans `tests/review/` (**97 scénarios**).

### **27. Résolution de l'Identité du Reviewer (`src/reviewer_resolution.py`)**

* **Le Bug (deux défaillances silencieuses enchaînées) :** (1) **Prefill vide** — `handles` seulement depuis `email_to_handle()` ; (2) **Attach non vérifié** — des valeurs passées *verbatim*, GitHub a répondu **201 sans attacher personne** et `_request()` est retourné sans lever.
* **Module :** Un plan pur, sans I/O propre, **et qui ne lève jamais**. `resolve_candidates()` (avant la TUI) et `resolve_typed_reviewers()` (à l'attach) ; `match_candidate()` fait correspondre de façon exacte et normalisée par login, nom ou e-mail.
* **Échelle de Résolution :** handle connu (sans requête) → correspondance exacte avec une personne suggérée → lookup par e-mail → validation du login sur la forge.
* **Ce qui ne se résout pas n'est jamais envoyé :** Cela sort dans `ResolutionOutcome.dropped` comme `(value, i18n_reason)` et est écarté avec un avertissement visible.
* **La Même Échelle Sert la Release 🆕 :** `release_engine` réutilise `get_commit_author_login` + `email_to_handle` pour transformer les e-mails de commit en logins cliquables.
* **Tests :** `tests/test_reviewer_resolution.py` (18) + des extensions dans `test_github_provider.py` (72), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) et `test_main_suggest_reviewers.py` (8).

### **28. Sous-commande `gitpr demo` — Visite Guidée sur des Exemples Enregistrés (`src/demo/`)**

* **Ce Que C'est :** Une visite interactive qui montre les trois sorties principales de GitPR — message de commit, review et description de PR — **sans clé API, sans dépôt git et sans réseau**.
* **Vrai Pipeline, Fausse Réponse :** `FakeAIProvider` reflète `src.ai_providers.call_ai_model` **argument par argument** ; `demo_pipeline()` patche `src.core` et `src.metrics` pour que le pipeline de production tourne **intact** et que seule la source de la réponse soit échangée.
* **L'Isolation est une Exigence, Pas un Détail :** La visite **ne peut pas** toucher le `~/.gitpr` de l'utilisateur ni le réseau — garanti par `tests/demo/conftest.py` (interdiction réseau autouse, avec une exception loopback pour l'event loop proactor de Windows).
* **Paquet :** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (deux exemples localisés dans les 5 langues). Les scénarios sont des **modules Python, pas du JSON**, pour qu'ils voyagent dans le wheel.
* **TUI :** `src/ui/demo/` avec un modal d'aide ; le diff est encadré comme ```` ```diff ````.
* **CLI :** `gitpr demo` avec `--scenario`, `--lang` et `--no-tui`.
* **Tests :** 6 fichiers dans `tests/demo/` (**155 scénarios**).
* **Artefacts :** `docs/demo.*.md` en 5 langues, `README.*` (5) et les métriques des exemples de la visite.

### **29. Badge GitPR et la Commande `gitpr badge` (`src/branding/`)**

* **Ce Que C'est :** Un badge public qui atteste qu'une pull request est passée par une vérification de qualité locale, et une commande qui affiche le snippet d'adoption pour le README du projet.
* **URL Statique à Dessein :** C'est un Markdown shields.io que GitPR ne récupère **jamais** — publier une pull request ne peut pas en venir à dépendre de la disponibilité d'un tiers.
* **Mesure Honnête (`badge_data.py`) :** `collect_linter_counts()` retourne `None` quand aucune règle de linter n'est configurée, parce qu'une liste vide veut dire « rien n'a été vérifié », et non « rien n'a été trouvé ».
* **Le Badge Ne Bloque Jamais une Publication :** Chaque chemin d'échec est capturé et dégrade vers « pas de badge ».
* **Point d'Injection Unique :** `attach_pr_badge()` dans `src/main.py`. Ajout idempotent par marqueur.
* **Commande `gitpr badge` :** `--readme` et `--style`. **Affiche seulement** — le README n'est jamais modifié.
* **Configuration :** `GITPR_BADGE` (défaut `true`) — un opt-out en **lecture seule**.
* **Tests :** 7 fichiers dans `tests/badge/` (**83 scénarios**).
* **Artefacts :** `docs/badge.*.md` en 5 langues et la survey dédiée.

### **30. Sous-commande `gitpr split` — Commits Atomiques par Hunk (`src/split/`)**

* **Ce Que C'est :** Un arbre de travail à plusieurs préoccupations ne devient plus un blob de commit. `split` lit le diff non commité, demande à l'IA de partitionner les hunks par **intention logique** et propose un commit atomique par préoccupation. L'arbre n'est jamais réécrit : les fichiers terminent identiques au byte près à leur état initial.
* **Paquet de 6 Fichiers :** `split_plan.py`, `hunk_parser.py` (pur), `hunk_grouper.py`, `generate_split_plan.py` (lecture seule), `apply_split_plan.py` (**le seul qui mute**) et `__init__.py`.
* **La Garantie d'Arbre Intact est Structurelle :** `selective_stager.py` pré-valide **chaque** groupe contre un index temporaire à HEAD et utilise un `GIT_INDEX_FILE` jetable.
* **Destructif pour l'Index, Pas pour l'Arbre :** `--apply` réinitialise l'index sur HEAD avant le staging, donc tout ce que l'utilisateur avait déjà stagé est déstagé — documenté explicitement.
* **Configuration :** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) et `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Les valeurs zéro ou négatives sont **ignorées au profit du défaut**.
* **Tests :** 6 fichiers dans `tests/split/` (**101 scénarios**).
* **Artefacts :** `docs/split-command.md` — 🆕 **désormais en 5 langues** (les 3 qui manquaient sont arrivées le 2026-09-24), ADR-006, `glossary-gitpr-split.md`, spec/plan et survey.

### **31. Analyse de Secrets Intégrée (`src/security_ruleset.py`)**

* **Ce Que C'est :** Sept règles qui tournent à **chaque** invocation du linter, fusionnées à la fin de `load_linter_rules()` après les règles du projet et des plugins.
* **Les Sept Règles :** cinq `error` bloquantes — ID de clé d'accès AWS, jeton GitHub, jeton Slack, clé API Google et bloc de clé privée — et deux `warning` : URL de connexion à une base de données avec identifiants et affectation d'identifiant générique (`password = "…"`), cette dernière derrière un filtre de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Pourquoi dans le Paquet et Non dans le Template :** Une barrière de secrets doit se comporter **de la même façon** sur chaque machine et dans chaque CI, donc les règles vivent dans le paquet et ne peuvent pas être remplacées par `--skill` ni réécrites par le wizard.
* **Lacunes de Couverture (v1) — confirmées toujours ouvertes 🆕 :** l'affectation **sans guillemets** n'est pas détectée (`API_KEY=abc123`, le format `.env`) ; les préfixes `ASIA…`, `github_pat_…` et `xoxc-`/`xoxd-` manquent ; et il n'y a pas de frontière gauche sur le nom de la clé.
* **Configuration :** `GITPR_LINTER_SECURITY` (défaut `true` ; opt-out fail-open) et `GITPR_LINTER_SECURITY_DISABLED_RULES` (séparées par `;`).
* **Tests :** `tests/test_security_ruleset.py` (**61 scénarios**).
* **Artefacts :** ADR-007, `glossary-gitpr-secret-scanning.md`, spec/plan/survey et 3 rapports de tâche.

### **32. Ponts SAST — Semgrep, Gitleaks et Bandit (`src/infrastructure/linter/external/`)**

* **Ce Que C'est :** Une couche **opt-in** qui branche des scanners de sécurité tiers sur le pipeline de linter existant.
* **Périmètre Borné :** Ils tournent **uniquement** lorsqu'ils sont activés et **uniquement** sur les fichiers touchés par le diff.
* **Contrat Commun :** `ExternalLinterBridge` (ABC) avec exécution `subprocess` blindée (`shell=False`, timeout strict, `stdin=DEVNULL`, UTF-8 avec `errors='replace'`). 🆕 Le modèle `NormalizedFinding` **a quitté cet endroit** pour `src/domain/finding/`, et `base_bridge` le **ré-exporte** désormais pour ne pas casser les imports existants.
* **La Déduplication est le Point :** `deduplicate_secret_findings()` fusionne les constats de Gitleaks et du ruleset de regex au même fichier et à la même ligne en **une seule** entrée confirmée `[Gitleaks + Regex]`.
* **Secrets Masqués :** `mask_secret_value()` garantit que la valeur n'atteint jamais le constat, le log ou la télémétrie.
* **Résolution en Trois Couches :** `load_sast_config()` lit défauts → `.gitpr.linter.yml` (bloc `sast`, avec fallback vers `linter.external`) → variables `GITPR_SAST_*`. **Tout est à `false` par défaut**.
* **Dégradation Gracieuse :** Un outil activé mais absent du `PATH` émet un avertissement et l'exécution continue.
* **Tests :** 4 fichiers dans `tests/infrastructure/linter/external/` (**14 scénarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + **`tests/domain/linter/test_structured_findings.py` (44)** 🆕, qui couvre le passage des constats normalisés à travers le moteur de lint.

### **33. Sous-commande `gitpr tests generate` — Génération de Suite par IA (`src/domain/tests_generation/`, `src/application/`)**

* **Ce Que C'est :** Génère des fichiers de test complets et exécutables à partir du diff courant, d'un fichier spécifique ou d'un constat de review, **en respectant la convention du dépôt** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Couche Domaine :** `TestFramework` (enum) et les dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest` ; `detect_test_framework()` détecte à partir des fichiers de configuration, des manifestes et du contenu du répertoire de tests ; `build_test_scaffold()` calcule le chemin de destination conventionnel par framework.
* **Couche Application (`generate_test_file.py`) :** Orchestre la détection, la résolution du scaffold, le prompt, l'IA, le parsing JSON et l'écriture optionnelle. `validate_test_syntax()` exécute la chaîne d'outils locale (`php -l`, `node --check`, `python -m py_compile`) — un échec de validation est un **avertissement**, pas une erreur.
* **Présentation :** Le groupe `tests` avec `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`) ; le dry run est le défaut et écraser un test existant demande confirmation avec **Non** présélectionné. Le chat délègue `/tests` au **même** cas d'usage.
* **Dette close 🆕 :** le type `tests` est dans `SKILL_LABELS` et dans `mcp_server.SKILL_FILES`, et les 17 clés `__()` qui n'existaient pas ont été traduites. **Il n'a toujours pas de sujet dans `docs/`** — seulement un template de skill et la spec.

### **34. Sous-commande `gitpr explain` et le Flag `--explain` — Guide du Reviewer (`src/domain/pr/`)**

* **Ce Que C'est :** Un guide centré sur celui qui va **réviser** — ce qui change, pourquoi cela change, où se concentrer et quel est le risque de régression.
* **Couche Domaine (`explain_section_builder.py`) :** `PrExplanation` / `ReviewerFocusPoint`, le renderer `build_explain_markdown()` et `parse_explain_payload()`, qui tolère une sortie non-JSON et **détecte les placeholders** `[FILL]`/`[TODO]` pour signaler des preuves insuffisantes au lieu de présenter un guide creux comme complet.
* **Couche Application (`generate_pr_explanation.py`) :** Résolution du fournisseur, validation de la clé, chargement du contexte de skill (`explain`), prompt, invocation et parsing.
* **Deux Portes d'Entrée :** `gitpr explain` (`--provider`, détection d'un diff manquant avant toute IA) et le flag `--explain` sur la CLI racine, qui ajoute le guide au corps de la description de PR **et** au payload JSON.
* **Configuration :** `GITPR_EXPLAIN_BY_DEFAULT` (défaut `false`).
* **Dette close 🆕 :** `explain` est dans `SKILL_LABELS` et dans `mcp_server.SKILL_FILES`, et les clés ont été traduites. **Il n'a toujours pas de sujet dans `docs/`.**

### **35. Suite Déterministe et CI (`.github/workflows/tests.yml`)**

* **Ce Que C'est :** Le workflow qui exécute la suite sur **Python 3.10** (le plancher déclaré dans `pyproject.toml`) et **3.13**, avec `fail-fast: false`.
* **`tests/conftest.py` Hermétique :** Épingle `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` et `LANG_VERSION` à la version du code, plus `GITPR_SHOW_LOGS=false` et `GITPR_SKIP_UPDATE_CHECK=true`.
* **L'Ordre Compte :** `src.updater` est importé **avant** que les variables de langue ne soient définies, parce qu'`i18n` photographie `os.environ` dans `AMBIENT_ENV_KEYS` à l'import.
* **Stabilité des Tests de Worker :** `test_config_app.py` et `test_metrics.py` attendent `workers.wait_for_complete()` avant d'affirmer.
* **Dépendance du Workflow :** Le runner propre n'a pas de `~/.gitpr`, et `test_demo_isolation.py` affirme que le profil existe — le job crée explicitement le répertoire et un `.env` vide.
* 🆕 **Dans cette fenêtre, la suite a grandi de 48% et le scénario d'échec s'est inversé :** les trois assertions qui existaient pour signaler la dette de registre (`TestSkillsSection`, `TestNoMissingKeys`, `TestSkillRegistryAgreement`) **passent** — le mécanisme de détection a fonctionné et a été soldé dans le même cycle où il a été créé.

### **36. Ledger de Télémétrie SQLite (`src/ledger.py`, `src/infrastructure/git/identity.py`) 🆕**

* **Le Problème n'était Pas le Format, c'était la Jointure :** les événements étaient écrits en fichiers JSON par commande, depuis un **thread daemon** (auquel un processus de courte durée — un hook — peut ne pas survivre), puis reconnectés au cache d'IA par une correspondance heuristique sur `(repo, branche, action, minute)` qui dépendait de **trois cartes commande→dossier divergentes**. Le symptôme était `tokens_actual` **toujours à `0`** pour `fullreview`/`filereview` et une perte de données entre dépôts à l'export.
* **L'Écriture est Synchrone, et C'est la Décision :** `log_command_metric()` résout le contexte git **une fois par processus** et écrit la ligne sur-le-champ, au lieu de la confier à un thread auquel un hook peut ne pas survivre. Les tokens, le modèle et le fournisseur sont **copiés depuis les métadonnées du fournisseur au moment de l'écriture** — il n'y a plus d'enrichissement a posteriori.
* **Schéma Versionné :** `SCHEMA_VERSION` + `PRAGMA user_version`, clés UUID (pas des entiers séquentiels, pour que le `merge` de deux ledgers n'entre pas en collision), WAL + `synchronous=FULL`, et `LedgerVersionError` pour refuser un bundle plus récent **avant** l'`ATTACH` — le ledger local n'est jamais laissé à moitié importé.
* **L'Invariant « Jamais Né Vide » :** `ensure_ledger()` ne crée la base **qu'ensemble** avec l'absorption des JSON legacy (originaux déplacés vers `~/.gitpr/metrics_legacy/`, marqueur de refus, chemin silencieux en mode non interactif).
* **Une Fenêtre, Un Sens :** `--since`/`--until`/`--days` (inclusifs aux deux bouts) resserrent **chaque** surface de lecture. La section cycle est l'exception — elle interroge le réseau, donc elle suppose les 30 derniers jours et **nomme la fenêtre dans son propre en-tête** au lieu de resserrer en silence.
* **Chaque Métrique est Calculée Une Fois et Rendue Une Fois :** les paires `(style, text)` (`TITLE`/`PLAIN`/`WARN`) font que la CLI et le dashboard Textual racontent la **même** histoire ; une section qui n'a rien à dire est omise, jamais imprimée vide.
* **Coût avec une Table Intégrée et Override :** prix catalogue des quatre modèles que GitPR distribue (`deepseek-v4-pro`, `deepseek-v4-flash`, `gemini-2.5-pro`, `gemini-2.5-flash-lite`), remplacés par `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Les deux tarifs sont obligatoires** — tarifer la moitié d'un modèle à un zéro non configuré **sous-estime** la facture, donc le modèle est rapporté en tokens uniquement. `ollama`/`local` coûtent zéro ; `GITPR_METRICS_CURRENCY` étiquette le total et, quand ce n'est pas de l'USD, **écarte** la table intégrée au lieu d'étiqueter un prix en dollars avec un autre nom. Un total qui laisse des modèles de côté s'affiche comme `Total (partial)`.
* **`modules` Normalisés :** les chemins du diff deviennent deux segments (`src/fix`), `(root)` pour un fichier à la racine et `(no module)` pour les commandes qui n'ont jamais eu de diff (linter, blame, hooks).
* **Transport par Bundle :** `bundle` écrit la fenêtre comme un `.db` autonome (le schéma et `user_version` voyagent avec) et `merge` l'attache et insère par UUID, en migrant un ancien bundle vers le haut et en refusant un plus récent.
* **`cache_backfill` Reste Dehors :** les lignes reconstruites depuis le cache d'IA sont marquées et **exclues des agrégats d'exécution** — la clé de cache est le prompt, donc elles comptent des prompts distincts, pas des exécutions.
* **Identité Git sans Forge (`identity.py`) :** étiquette de dépôt, de branche et d'auteur en **un seul** appel subprocess, valide sur n'importe quelle forge — et délibérément **sans** construire un `ScmProvider`, ce qui serait coûteux à chaque commande de télémétrie.
* **La Section Cycle est la Seule à Laquelle le Ledger ne Répond Pas :** elle liste les PR fusionnés via `ScmProvider.list_pull_requests` et mesure `created_at → merged_at`, avec des codes de statut explicites (ok/empty/unavailable/unsupported). Chaque échec dégrade vers **une ligne** — `Not read: <reason>`, pas de date de fusion sur Bitbucket, rien de fusionné dans la fenêtre — jamais une stack trace et jamais un nombre deviné.
* **Présentation Complète :** le groupe `gitpr metrics` avec 8 sous-commandes, un dashboard qui lit le ledger sur un worker thread et l'assistant de migration à trois options.
* **Configuration :** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY` et `.gitpr/metrics/` dans `.gitignore`.
* **Tests :** `tests/test_metrics.py` est passé de 34 à **164 scénarios** ; `tests/scm/*` a gagné la pagination de `list_pull_requests` et `within_window` sur chaque forge ; `test_commit_classifier.py` (30) couvre `pr_number_from_merge`.
* **Artefacts :** ADR-008, `glossary-metrics-telemetry.md`, `docs/plans/develop_natan/20260927_metrics_audit_plansfacts.md`, survey et **`docs/metricas-telemetria.*.md` réécrit dans les 5 langues**. Vocabulaire canonique dans `docs/plans/glossary-metrics-telemetry.md` (conformément au CLAUDE.md).

### **37. Sous-commande `gitpr mentor` et le Flag `--mentor` — Mentor Mode (`src/domain/mentor/`) 🆕**

* **Ce Que C'est :** Un review dit *ce* qui ne va pas et presque jamais *pourquoi cela compte*. Mentor Mode enrichit les constats avec **ce qui se passe**, **pourquoi cela compte**, une **analogie** du quotidien et les **concepts à étudier** — pour quelqu'un qui débute, la valeur d'un review est dans ce qu'il enseigne après avoir pointé les choses.
* **Couche Domaine Pure (`mentor_explanation_builder.py`) :** sans I/O, convertit le payload de l'IA en un `MentorExplanation` validé et impose une **honnêteté épistémique** sur trois fronts : avec des preuves insuffisantes, l'explication **le dit** au lieu de remplir le vide ; les URL hallucinées sont **supprimées** des pointeurs « pour en savoir plus » ; et les ID de constat inconnus sont **rejetés**, en préservant l'ordre strict des constats d'origine.
* **Couche Application (`generate_mentor_explanation.py`) :** collecte les candidats (`collect_candidates`), des **lots de 10 constats au maximum** par exécution, ordonnés par sévérité, en réutilisant le cache MD5 par hash de prompt — relancer mentor sur un review inchangé **ne coûte rien**.
* **Deux Portes d'Entrée :** `gitpr mentor [--finding <id>] [--provider <name>]` s'exécute sur le **dernier review persisté** (il doit donc venir après un `-r`/`-f`), et le flag `--mentor` ajoute la section au review normal, avec un avertissement lorsqu'il est utilisé sur une action qui ne le prend pas en charge.
* **Opt-In Strict :** `GITPR_REVIEW_MENTOR_MODE` (défaut `false`) et `GITPR_MENTOR_INCLUDE_ANALOGY` (défaut `true`), éditables dans la TUI de configuration. L'activer coûte des appels d'IA supplémentaires — c'est pourquoi le défaut est désactivé.
* **Skill et Registre :** `.gitpr.mentor.md` enregistré dans `SKILL_FILES_BY_TYPE`, dans `mcp_server.SKILL_FILES` et dans `SKILL_LABELS`, avec des templates en **5 langues**.
* **Tests :** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) et `tests/test_mentor_cli.py` (4).
* **Artefacts :** `docs/mentor-mode.*.md` en **5 langues**, ADR-009, `glossary-gitpr-mentor.md`, spec et rapports de tâche.

### **38. Sous-commande `gitpr risk` — Score de Risque Déterministe (`src/domain/risk/`) 🆕**

* **Ce Que C'est :** Un score de **0 à 100** avec un niveau `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, calculé **sans IA** à partir de signaux locaux, pour que le reviewer sache où regarder en premier — et pour que le même diff obtienne deux fois le même nombre.
* **Couche Domaine Pure (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`) :** enums et dataclasses (`RiskLevel`, `RiskSignal`, `RiskEvidence`, `FileRisk`, `PullRequestRisk`) ; poids, seuils, motifs de chemins critiques, correspondance glob et détection de tests/non-exécutables chargés depuis YAML ; calcul par fichier avec **clamp 0–100** et agrégation 50/30/20.
* **La Formule est Déclarée, Pas Apprise :** l'agrégation combine **50%** le fichier le plus risqué, **30%** la moyenne pondérée par les lignes et **20%** les preuves critiques. Au-dessus, il y a des **règles d'escalade obligatoires** : un score ≥ 80 monte au moins à `HIGH` ; un bloqueur ou un score ≥ 90 force `CRITICAL`. Un score sans règle d'escalade est un nombre que l'équipe apprend à ignorer.
* **Adaptateurs d'Infrastructure :** `risk_history_reader.py` lit `git log` de façon sûre et extrait les commits, les corrections de bugs et les reverts dans une fenêtre de **90 jours** ; `test_matcher.py` fait correspondre un fichier de test ↔ un fichier de production par convention, à travers plusieurs dispositions de langage.
* **Cas d'Usage (`calculate_risk.py`) :** corrèle les sections de diff parsées, les constats, la correspondance des tests et l'historique dans `FileRisk`/`PullRequestRisk`, **en dégradant avec un avertissement** quand un signal n'est pas disponible. Applique le pack de policy au-dessus de la configuration de base, quand un pack est actif.
* **Trois Consommations :** la commande `gitpr risk` (`--file`, `--format text|json`, `--base`, `--no-history`), la section `⚡ Risk Assessment` ajoutée aux reviews et au review de PR distant, et le **prompt IA** — où le score entre comme contexte **en lecture seule**, dans un format compact.
* **Opt-Out :** `GITPR_RISK_INCLUDE_IN_REVIEW` (défaut `true`) — comme le défaut l'active, la section commence à apparaître dans les reviews de quiconque met à jour ; la désactiver est une variable. **Changement de comportement déclaré.**
* **Configuration par Projet :** `templates/gitpr.risk.yml` (ou `.gitpr/skill/gitpr.risk.yml`) remplace poids, seuils et chemins critiques. Les poids négatifs ne sont acceptés que pour les signaux **atténuants** comme `test_present`.
* **Tests :** `tests/domain/risk/` (2 fichiers, 10 scénarios), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) et `tests/test_risk_cli.py` (4).
* **Artefacts :** `docs/risk-scoring.*.md` en **5 langues**, ADR-010, `glossary-gitpr-risk-scoring.md` et des rapports de tâche (dont un de Gemini).

### **39. Sous-commande `gitpr policy` — Policy Packs Versionnés (`src/domain/policy/`, `src/infrastructure/policy/`) 🆕**

* **Ce Que C'est :** Un **pack YAML versionné** qui porte, dans un seul artefact, les décisions de qualité d'un dépôt : skills, règles de linter, sévérités, chemins critiques, poids de risque et conventions de PR/commit. Aujourd'hui, chaque dépôt configure cela à la main — ce qui rend le comportement difficile à reproduire, à partager et à **verrouiller**. Le pack rend la configuration déclarative, versionnée, composable et reproductible.
* **Couche Domaine (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`) :** parsing du manifeste avec un **schéma fermé** (une clé inconnue est une erreur, pas un avertissement), vérification de compatibilité et d'identifiants, **somme de contrôle SHA-256** et un résolveur de graphe avec précédence et composition.
* **Couche Infrastructure :** `local_policy_repository.py` résout le pack depuis **trois origines** (`.gitpr/policies`, `~/.gitpr/policies` et le paquet embarqué) et `policy_pack_loader.py` le charge.
* **Cas d'Usage :** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` et `validate_policy_pack`.
* **Quatre Packs Intégrés :** `laravel-quality`, `node-quality`, `php-security` et `vue-quality`, chacun avec `policy.yml` et `linter.yml`, distribués via **`package-data`** — un pack qui ne voyage pas dans le wheel est un pack qui n'existe que sur la machine de celui qui l'a écrit.
* **Consommation Intégrée en Quatre Points :** `calculate_risk` (policy au-dessus de la configuration de base), `config.load_linter_rules` (fusion en couches + override des sévérités), `core.get_skill_context` (injecte le contexte du pack) et la **clé de cache du PR** (`cache_scope`) — deux dépôts sous des politiques différentes ne peuvent pas partager une réponse d'IA.
* **CLI :** `gitpr policy {list, validate, show, use, off, install, init}`, avec confirmation (`--yes`/`--force`), détection de stack dans `init` et enregistrement du choix dans `.gitpr/policy.lock.yml`.
* **Configuration :** `GITPR_POLICY_ENABLED` et `GITPR_POLICY_CONTEXT_MAX_CHARACTERS` (le plafond du contexte injecté dans le prompt), tous deux dans la nouvelle catégorie `policy` du schéma.
* **Compatibilité en Arrière :** sans pack actif, le comportement est **préservé** — couvert par un test d'intégration, pas par inspection.
* **Nouvelle Dépendance :** `packaging>=23.0` (SemVer/PEP 440), déclarée dans `Pipfile` et `pyproject.toml`.
* **Tests :** `tests/domain/policy/` (4 fichiers, 126 scénarios), `tests/application/use_cases/` (4 fichiers de policy, 89), `tests/test_policy_cli.py` (26) et `tests/integration/test_policy_effect_on_review_linter_risk.py` (31 — des effets réels dans un vrai dépôt, avec une garde réseau autouse).
* **Artefacts :** `docs/policy-packs.*.md` en **5 langues**, ADR-011, `glossary-policy-packs.md`, spec/plan/survey.

### **40. Sous-commande `gitpr baseline` — Baseline et Suppressions Auditables (`src/domain/baseline/`) 🆕**

* **Ce Que C'est :** Les linters et les reviews re-signalent les mêmes constats préexistants à chaque exécution, et le travail de démêler la sortie dans sa tête est laissé au reviewer. La baseline enregistre l'**instantané des constats que le dépôt connaît déjà** dans `.gitpr/baseline.json`, prend en empreinte chacun d'une manière stable à travers les exécutions et laisse le pipeline classer ce qui est nouveau — **seul `new` bloque**.
* **Couche Domaine (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`) :**
  * **Empreinte stable :** identité de la règle, chemin normalisé, lignes et **sha256 de la ligne avec les espaces réduits** — la ligne brute n'est **jamais** stockée, donc la baseline n'est pas un fichier où les secrets fuient par accident.
  * **Manifeste canonique :** JSON canonique avec somme de contrôle, validation qui refuse une clé inconnue, une version de schéma/empreinte hors de la plage supportée, un invariant suppression/dette cassé, une empreinte dupliquée et une plage de lignes invalide.
  * **Comparateur :** classification, comptages par statut et résolution d'entrée — `resolved` puis `back` se lit comme **new**, ce qui est la lecture conservatrice.
  * **Politique de Suppression :** suppressions, dette acceptée, overrides et validateurs, avec correspondance en couches (entrée × overrides × policy pack) par **spécificité et précédence**, plus le suivi de la dette en retard.
* **Cinq Cas d'Usage :** `baseline_gate` (le point d'entrée unique du pipeline — charge, classe, annote et compose l'entrée, couches locale et d'overrides du pack), `baseline_summary` (vue JSON **en lecture seule** pour les IDE et les agents MCP), `compare_against_baseline`, `create_baseline` (création/mise à jour avec provenance, sommes de contrôle et **préservation des décisions humaines**) et `suppress_finding` (supprime/retire dans les périmètres constat, ligne, fichier et règle).
* **Intégration au Policy Pack :** le pack peut porter un bloc `baseline:`, validé par les **mêmes** validateurs, composé par le résolveur comme une couche étiquetée et rapporté par `policy validate`.
* **Le Dépôt est le Seul à Écrire :** `local_baseline_repository.py` est le **seul** lecteur/écrivain de `.gitpr/baseline.json` et `.gitpr/baseline.overrides.yml`, avec écriture atomique, une barrière de somme de contrôle/version et un chemin de réparation.
* **Configuration :** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` et `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`, dans la nouvelle catégorie `baseline`.
* **Opt-In et No-Op :** désactivée ou sans baseline existante, la fonctionnalité **ne lit aucun fichier et n'imprime rien** — qui ne l'utilise pas ne voit aucun changement.
* **⚠️ L'Exception Délibérée :** un fichier de baseline qui existe mais **ne peut pas être appliqué** fait **avorter avec le code de sortie 1** le lint, le review, le full review, l'audit de fichier, le review distant et le risque. Une baseline qui échoue en silence est une baseline qui « fait disparaître des constats » sans que personne ne le sache — avorter est le choix sûr.
* **Portée :** CLI (6 sous-commandes, toutes avec `--format json`), MCP (outil `get_baseline_status` + ressource `baseline://summary`), review de PR distant (résolue **avant le premier appel réseau**) et le flux de lint, dont le code de sortie suit désormais le blocage tenant compte de la baseline.
* **Le Refactor que la Fonctionnalité a Exigé :** `NormalizedFinding` est devenu un type canonique dans `src/domain/finding/` (avec `snippet_hash` et `rule_version`), importé par chaque pont — sans cela, le même constat aurait une forme par outil et l'empreinte ne le reconnaîtrait pas.
* **Tests :** `tests/domain/baseline/` (5 fichiers, **126 scénarios**), `tests/application/use_cases/` (5 fichiers, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) et `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34).
* **Artefacts :** `docs/baseline-suppressions.*.md` en **5 langues**, ADR-012, `glossary-baseline.md`, spec/plan/survey et rapport de tâche.

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame par plage de lignes dans un fichier |
| `tests/test_blame_metrics.py` | 7 | Métriques de blame : profondeur, commits, durée |
| `tests/test_baseline_cli.py` | 54 🆕 | groupe `baseline` : create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Constructeur de changelog : sections, titres traduisibles, contributeurs, **liens et dates** |
| `tests/test_chat_backend.py` | 19 | Mémoire du chat, persistance, commandes slash |
| `tests/test_commit_classifier.py` | 30 | Classification Conventional Commits + **`pr_number_from_merge()` sur les 4 forges** |
| `tests/test_config_app.py` | 78 | TUI de configuration : montage, changement de catégorie, suivi dirty, F2 bloqué, Ctrl+R, recherche, secrets |
| `tests/test_config_cli.py` | 9 | Enregistrement de la sous-commande `config`, `-h`, import paresseux, stdout propre |
| `tests/test_config_schema.py` | 42 | Couverture de `DEFAULT_CONFIG`, aucun doublon, catégories/kinds, `advanced` uniquement dans Advanced, **section des skills** ✅ |
| `tests/test_config_store.py` | 22 | Aller-retour sur le `.env` temporaire, commentaires et ordre préservés, `remove_config_value()` idempotent |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuration des reviewers suggérés (clés et défauts) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` avec un SDK mocké (401 vs. réseau vs. ollama) |
| `tests/test_core.py` | 49 | Flux principaux, git diff, génération de PR, timing, staging, co-autorat, langue des hooks |
| `tests/test_diff_parser.py` | 26 | Parseur de diff par lignes/hunks + `summarize_patch()` et `split_patch_sections()` |
| `tests/test_explain_command.py` | 2 | CLI `explain` : succès, clé manquante, diff vide |
| `tests/test_external_linters.py` | 33 | Pont Checkstyle : parseur XML, subprocess, intersection de diff, rapport |
| `tests/test_i18n.py` | 20 | Parité entre les langues, clés manquantes/orphelines, identité — **zéro clé manquante** ✅ |
| `tests/test_install_wizard.py` | 3 | Wizard d'installation interactif |
| `tests/test_issue_engine.py` | 4 | Brouillon d'issue structuré |
| `tests/test_linter_metrics.py` | 4 | Métriques de linter : erreurs, warnings, durée |
| `tests/test_linter_presets.py` | 5 | Presets de linter : résolution et re-téléchargement forcé |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` dans la CLI et dans l'aide contextuelle |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompts MCP et fallback de langue |
| `tests/test_mcp_server.py` | 110 | Outils MCP (16), ressources (19), annotations, patching, CLI directe, offload — **concordance du registre de skills** ✅ |
| `tests/test_mcp_server_e2e.py` | 6 | Vrai serveur MCP via subprocess + stdio JSON-RPC |
| `tests/test_mentor_cli.py` | 4 🆕 | CLI `mentor` et opt-in `--mentor` |
| `tests/test_metrics.py` | 164 | **Ledger** : écriture synchrone, export idempotent, bundle/merge, versionnage, migration, renderers, coût, qualité, modules, fournisseurs, cycle |
| `tests/test_net_timeouts.py` | 12 | Timeouts réseau/IA — alignés sur le vrai défaut de 180s |
| `tests/test_plugins.py` | 17 | Découverte des plugins, fusion des règles de linter, prompts MCP |
| `tests/test_policy_cli.py` | 26 🆕 | groupe `policy` : list/show/validate/use/off/install/init, confirmation et fichier de verrouillage |
| `tests/test_pr_publish_app.py` | 45 | TUI du PR Publisher : écrans, flux, reviewers suggérés, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modale d'erreur du linter : abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save et payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release : options, aide avec epilog documenté |
| `tests/test_release_engine.py` | 53 | **Cutoff ancré dans le changelog, `_drop_released()`, liens, carte des PR par commit de merge**, publication |
| `tests/test_reviewer_resolution.py` | 18 | Échelle de résolution, rejet de nom partiel, dédup, tolérance aux échecs |
| `tests/test_reviewer_suggestion.py` | 18 | Logique de suggestion de reviewers (classement, exclusion, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 🆕 | CLI `risk` : `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 61 | Matrice du ruleset intégré : regex, placeholders, niveau, wildcard, fusion, traductions |
| `tests/test_skill_command.py` | 10 | Téléchargement et validation des templates de skill |
| `tests/test_skill_context.py` | 18 | `get_skill_context()` avec `quiet=True`, fallbacks, registre de skills |
| `tests/test_smart_excludes.py` | 15 | Filtre pathspec intelligent et re-téléchargement forcé |
| `tests/test_suggest_reviewers.py` | 17 | Reviewers suggérés dans le flux de PR (intégration, indice `no_login`) |
| `tests/test_tests_command.py` | 2 | CLI du groupe `tests` et de `generate` |
| `tests/test_thinking_words.py` | 5 | Chargement, parsing avec un séparateur `;` et rechargement forcé |
| `tests/test_updater.py` | 23 | Barrière PyPI : parsing de version, cache quotidien, fetch, décisions de la barrière, câblage CLI |
| `tests/test_usage_log.py` | 27 | Journal d'utilisation : nom dérivé de la date, écriture synchrone, silence, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump sémantique : major/minor/patch, cibles et validation |
| `tests/test_web_links.py` | 26 🆕 | Normalisation de remote (https/ssh/scp/identifiants/port) et motifs d'URL par forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 🆕 | Activation de pack, fichier de verrouillage, refus |
| `tests/application/use_cases/test_baseline_gate.py` | 40 🆕 | Barrière : chargement, classification, annotation, couches et avortement |
| `tests/application/use_cases/test_baseline_summary.py` | 12 🆕 | Vue en lecture seule de la baseline pour MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 🆕 | Corrélation diff/constats/tests/historique, dégradation |
| `tests/application/use_cases/test_create_baseline.py` | 30 🆕 | Création/mise à jour avec provenance et préservation des décisions |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 🆕 | Lots, sévérité, cache hit, clé manquante |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 2 | Cas d'usage explain |
| `tests/application/use_cases/test_generate_test_file.py` | 4 | Cas d'usage de génération de tests |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 🆕 | Installation de pack, origines et conflits |
| `tests/application/use_cases/test_suppress_finding.py` | 29 🆕 | Suppression/retrait dans les quatre périmètres |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 🆕 | Validation : schéma fermé, somme de contrôle, compatibilité |
| `tests/badge/test_badge_append.py` | 9 | Ajout idempotent du badge au corps de la PR |
| `tests/badge/test_badge_builder.py` | 19 | Composition Markdown shields.io, escape, règle de couleur, style |
| `tests/badge/test_badge_cli.py` | 15 | Commande `gitpr badge` : snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Comptage des alertes ; `None` sans règles configurées |
| `tests/badge/test_badge_offline.py` | 6 | Garde hors ligne (réseau interdit, sauf loopback) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` sur chaque chemin de publication |
| `tests/badge/test_badge_paths.py` | 10 | Point d'injection unique dans les deux publishers (TUI et `--no-edit`) |
| `tests/demo/test_demo_app.py` | 22 | TUI du tour : navigation, écrans, modale d'aide |
| `tests/demo/test_demo_isolation.py` | 14 | Le tour ne touche ni `~/.gitpr` ni le réseau |
| `tests/demo/test_demo_runner.py` | 33 | Machine à états `DemoState` : avant, arrière, terminé, mode texte |
| `tests/demo/test_demo_scenarios.py` | 47 | Intégrité des scénarios enregistrés (arithmétique des hunks) dans les 5 langues |
| `tests/demo/test_demo_text_mode.py` | 18 | Sortie en texte brut (`--no-tui`) pour la CI et les enregistrements |
| `tests/demo/test_fake_ai_provider.py` | 21 | Contrat du faux fournisseur : reflète `call_ai_model` argument par argument |
| `tests/domain/baseline/test_baseline_comparator.py` | 21 🆕 | Classification, comptages par statut, `resolved`→`new` |
| `tests/domain/baseline/test_baseline_fingerprint.py` | 12 🆕 | Digest golden, ligne brute jamais stockée, stabilité |
| `tests/domain/baseline/test_baseline_manifest.py` | 26 🆕 | JSON canonique, somme de contrôle, clés inconnues, invariants |
| `tests/domain/baseline/test_baseline_types.py` | 15 🆕 | Statuts persistés, périmètres et précédence |
| `tests/domain/baseline/test_suppression_policy.py` | 52 🆕 | Couches, spécificité, overrides, dette en retard |
| `tests/domain/finding/test_finding_types.py` | 3 🆕 | Type canonique de constat : `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Formatage uniforme et déduplication `[Gitleaks + Regex]` |
| `tests/domain/linter/test_structured_findings.py` | 44 🆕 | `findings_out` : constats structurés et le compteur de lignes du côté nouveau |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 🆕 | Builder pur : honnêteté épistémique, URL supprimées, ordre préservé |
| `tests/domain/policy/test_policy_checksum.py` | 15 🆕 | SHA-256 du pack et détection d'édition manuelle |
| `tests/domain/policy/test_policy_compatibility.py` | 22 🆕 | Compatibilité de version et d'identifiants |
| `tests/domain/policy/test_policy_manifest.py` | 42 🆕 | Schéma fermé, bloc `baseline:`, erreurs de manifeste |
| `tests/domain/policy/test_policy_resolver.py` | 47 🆕 | Précédence, composition en couches, pack actif × base |
| `tests/domain/pr/test_explain_section_builder.py` | 4 | Rendu Markdown, parsing tolérant, détection de `[FILL]` |
| `tests/domain/risk/test_risk_calculator.py` | 5 🆕 | Formule 50/30/20, clamp 0–100, escalade obligatoire |
| `tests/domain/risk/test_risk_rules.py` | 5 🆕 | Poids, seuils, glob de chemins critiques, YAML d'override |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 | Détection par config, manifeste et répertoire ; override ; échec averti |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 | Chemin conventionnel par framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Cas d'usage complet : review → IA → validation → classification → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extracteur partagé entre le chat et `fix` |
| `tests/fix/test_fix_cli.py` | 31 | Routage des sous-commandes, options, dry-run par défaut, `--force` |
| `tests/fix/test_fix_history.py` | 21 | Ledger `.gitpr/fix_history.json` : écriture atomique, lecture |
| `tests/fix/test_fix_settings.py` | 10 | Les cinq `GITPR_FIX_*` et les fallbacks pour valeurs invalides |
| `tests/fix/test_patch_applier.py` | 19 | Wrapper `git apply` : check/apply/reverse, branche, statut |
| `tests/fix/test_patch_extractor.py` | 13 | Blocs fencés → diff unifié validé |
| `tests/fix/test_patch_safety_classifier.py` | 22 | Matrice safe/review_required/experimental et codes de raison |
| `tests/fix/test_resolve_last_review.py` | 13 | Sélection du dernier review, exclusion des reviews par fichier |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback` : reverse et les trois refus |
| `tests/infrastructure/baseline/test_local_baseline_repository.py` | 24 🆕 | Seul lecteur/écrivain : écriture atomique, somme de contrôle, réparation |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 🆕 | `git log` sûr : commits, bugs et reverts en 90 jours |
| `tests/infrastructure/git/test_test_matcher.py` | 5 🆕 | Correspondance test ↔ production par convention |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Pont Bandit : disponibilité, parsing, mapping |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC : subprocess blindé, timeout, binaire manquant |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Pont Gitleaks : masquage des secrets, périmètre par fichier |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Pont Semgrep : parsing JSON, sévérité, skip non-Python |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 🆕 | Effet réel de la baseline sur le review, le lint et le risque dans un vrai dépôt |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 🆕 | Effet réel du pack sur le review, le lint et le risque (avec une garde réseau autouse) |
| `tests/review/test_diff_normalizer.py` | 20 | Retours à la ligne, validation de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource` : provenance, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orchestration : barrières, PR, diff, linter, commentaire optionnel |
| `tests/review/test_review_pr_cli.py` | 25 | CLI `review-pr` : options, rejets avant l'IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 48 | Provider Azure DevOps : org/projet, PR, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 44 | Provider Bitbucket : Basic auth, workspace, PR |
| `tests/scm/test_contract.py` | 42 | Contrat `ScmProvider` : signatures, dataclasses, erreurs |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` déprécié → délègue au provider |
| `tests/scm/test_github_provider.py` | 72 | Provider GitHub : REST, headers, PR, issues, releases, relecture des reviewers |
| `tests/scm/test_gitlab_provider.py` | 52 | Provider GitLab : API v4, namespace, headers synthétisés et `overflow` |
| `tests/scm/test_init_wizard.py` | 12 | Wizard `--init` : détection de forge, validation, persistance |
| `tests/scm/test_release_publish.py` | 8 | Publication de release par forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 | Le seul module qui mute : staging sélectif, un commit par groupe |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → unités → groupes → pré-validation des conflits → messages |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, budget/trimming, appel IA, validation de la réponse |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()` : texte ↔ modèle, pur |
| `tests/split/test_selective_stager.py` | 11 | Staging d'un sous-ensemble, vérification contre HEAD, index propre |
| `tests/split/test_split_cli.py` | 31 | CLI split : options, dry run par défaut, `--apply` |
| `tests/test_web_links.py` | 26 🆕 | (voir ci-dessus) |
| `tests/sync_i18n.py` | — | Script de vérification de la couverture i18n (scaffold ; jamais exécuté) |

**Total :** **2799 scénarios collectés à travers 129 modules de test** (49 à la racine + 11 dans `tests/application/use_cases/` + 10 dans `tests/fix/` + 9 dans `tests/scm/` + 7 dans `tests/badge/` + 6 dans `tests/demo/` + 6 dans `tests/split/` + **5 dans `tests/domain/baseline/`** 🆕 + **4 dans `tests/domain/policy/`** 🆕 + 4 dans `tests/review/` + 4 dans `tests/infrastructure/linter/external/` + **2 dans `tests/domain/risk/`** 🆕 + 2 dans `tests/domain/tests_generation/` + **2 dans `tests/integration/`** 🆕 + **2 dans `tests/infrastructure/git/`** 🆕 + 2 dans `tests/domain/linter/` + **1 dans `tests/domain/finding/`** 🆕 + **1 dans `tests/domain/mentor/`** 🆕 + 1 dans `tests/domain/pr/` + **1 dans `tests/infrastructure/baseline/`** 🆕 ; **+910 scénarios et +33 fichiers** depuis le rapport précédent).

**Notes de qualité pour cette version :**
- **✅ Les 4 échecs de la fenêtre précédente ont été clos, avec les assertions intactes.** `SKILL_LABELS` est passé de 8 à **11** entrées et `mcp_server.SKILL_FILES` de 8 à **11** ; les **40 clés `__()` non traduites** ont été résolues — le code utilise 1603 et les dictionnaires en ont 1603, **zéro manquante**. Les trois assertions qui signalaient la dette (`TestSkillsSection` ×2, `TestNoMissingKeys`, `TestSkillRegistryAgreement`) **tournent toujours et passent désormais** — vérifié dans cette fenêtre avec `pytest tests/test_config_schema.py::TestSkillsSection tests/test_mcp_server.py::TestSkillRegistryAgreement tests/test_i18n.py::TestNoMissingKeys` → **11 passed**.
- **Croissance de 910 scénarios (+48%) en un seul cycle**, le plus grand bond jamais enregistré dans ce projet. `tests/test_metrics.py` à lui seul est passé de 34 à **164** scénarios, et les trois nouvelles fonctionnalités à la plus forte densité de tests sont la baseline (≈380 scénarios combinés), la policy (≈246) et le ledger.
- **La CI exécute la suite sur Python 3.10 et 3.13** depuis la fenêtre précédente, et `fail-fast: false` garantit qu'un échec sur une version ne masque pas l'autre.
- **Les tests d'intégration sont entrés au répertoire :** `tests/integration/` est nouveau et prouve un **effet réel** — un pack actif et une baseline active changent le review, le lint et le risque dans un vrai dépôt, avec une garde autouse qui bloque le réseau en dehors du loopback.
- **`tests/conftest.py` reste hermétique :** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` et `LANG_VERSION` à la version du code.
- **Exécution complète sur cette machine : `2 failed, 2795 passed, 2 skipped, 138 subtests passed in 520.46s (8min40s)`.** La suite a finalement été exécutée de bout en bout dans cette fenêtre et le résultat est honnête : **2795 des 2799 scénarios collectés passent**. Les **deux échecs ne sont pas une régression du produit** — c'est un bug d'isolation dans `tests/test_mentor_cli.py`, et la cause racine a été identifiée et confirmée par expérience :
  - `TestMentorCli` fait `@patch("src.core.get_git_diff")`, mais `src/main.py` importe le nom **par valeur** (`from src.core import … get_git_diff`), donc le patch n'atteint jamais la liaison que le flux utilise réellement : la fonction **réelle** s'exécute. Le vrai `get_git_diff()` exécute `git diff -U1 -w -M -B HEAD -- <59 smart excludes>`, et comme le seul changement suivi dans l'arbre de travail pendant l'exécution était `CHANGELOG.md` — filtré par `:(exclude)*.md` —, le diff revient avec **0 octet**, le flux tombe dans le retour anticipé à `main.py:1444` (`⚠️ Nenhum código novo encontrado`) avec le **code de sortie 0**, et l'assertion `render_review_result.assert_called_once()` échoue parce que le renderer n'a jamais été appelé.
  - **La preuve :** en répétant les deux scénarios avec le patch pointé sur `src.main.get_git_diff` (la bonne liaison), les deux se comportent exactement comme les assertions l'attendent — `--mentor` appelle `_append_mentor_section` **et** le renderer ; sans `--mentor`, l'append n'est **pas** appelé et le renderer l'est. Autrement dit : **les deux tests dépendent de l'environnement**, passant tant que l'arbre de travail a un changement qui n'est pas du `.md` (ce qui était vrai tout au long du développement des fonctionnalités) et échouant sur un arbre de documentation seule.
  - Le code de production est correct — le chemin `-r` atteint `render_review_result` (`main.py:1593`) comme toujours. **Correctif recommandé :** changer la cible du patch pour `src.main.get_git_diff` dans les deux scénarios (ou patcher `src.core` et la liaison de main ensemble).
- **Aucun des 4 échecs de la fenêtre précédente ne subsiste :** ils étaient d'une autre nature (registre de skills et clés `__()` non traduites) et les assertions qui les caractérisaient tournent toujours et passent.
- **Garde réseau à deux endroits :** `tests/demo/conftest.py` (le tour ne peut pas empoisonner un vrai cache) et `tests/integration/conftest.py` (nouveau).
- **Fixtures git réelles :** `tests/fix/git_fixture.py` et `tests/split/git_fixture.py` restent la norme pour ce qui n'est honnête qu'avec un vrai `git apply`.

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n :** **1603 clés de traduction** dans les 6 dictionnaires, avec une **parité totale des key sets** — et, pour la première fois en plusieurs fenêtres, **la parité avec le code aussi** : le code utilise 1603 clés et aucune n'est non traduite. ✅ (+445 dans cette fenêtre.) La chaîne mesurée par commit était 1158 → 1161 (release notes, +3) → 1258 (ledger, +97) → 1288 (mentor, +30) → 1308 (docs de risque, +20) → 1437 (policy, +129) → **1603** (baseline, +166).
* **`__lang_version__` est monté v0.0.31 → v0.0.32 → v0.0.33 → v0.0.34**, déclenchant le re-téléchargement OTA des traductions — et cette fois le bump est **commité** (`af35270`), pas dans l'arbre de travail.
* **Sources de traduction en phase :** une nouvelle clé doit exister dans le code (source), dans `langs/pt_br.json` (**liste maîtresse**), dans les dicts FR/ES de `scripts/sync_all_langs.py` et dans les valeurs curatées de `scripts/fix_mangled_i18n_keys.py` ; l'assertion `len(CLEAN_KEYS)` reste à 49.
* **Nouveaux sujets 🆕 (4, tous en 5 langues) :**
  - `docs/mentor-mode.md` — Mentor Mode : ce qu'il enseigne, les trois garanties d'honnêteté épistémique, les lots de 10 et l'opt-in.
  - `docs/risk-scoring.md` — le score déterministe : les signaux locaux, la formule 50/30/20, les règles d'escalade et le `gitpr.risk.yml`.
  - `docs/policy-packs.md` — les packs versionnés : l'échelle de précédence, le fichier de verrouillage que l'équipe commite et les trois échecs qui avortent au lieu de dégrader en silence.
  - `docs/baseline-suppressions.md` — la baseline : les cinq statuts, pourquoi seul `new` bloque, ce que l'empreinte hashe et ce qu'elle laisse délibérément de côté, les quatre périmètres de suppression et le bloc `baseline:` d'un pack.
* **Sujets traduits dans cette fenêtre (dette soldée) 🆕 :** `docs/split-command.*.md` a complété les 5 langues (+3 : pt_pt, es_es, fr_fr) et `docs/otimizacao-de-tokens.*.md` aussi. **`review-pr` n'a toujours que 2 langues** — c'est la dernière famille de fonctionnalités partiellement traduite.
* **Sujets mis à jour dans cette fenêtre :** `docs/release-notes.*` (5 — réécrits pour le cutoff ancré dans le changelog et les liens par forge), `docs/metricas-telemetria.*` (5 — réécrits pour le ledger SQLite, avec le vocabulaire canonique de `glossary-metrics-telemetry.md`), `docs/mcp-integration.*` (5 — les nouveaux outils et ressources), `docs/mcp-annotations.*` (5), `docs/ARCHITECTURE.*` (5) et `README.*` (5 — l'index a gagné les familles `mentor-mode`, `risk-scoring`, `policy-packs` et `baseline-suppressions`).
* **Sous-répertoire `docs/extra/` 🆕 :** deux familles PT uniquement (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) ont été **déplacées** du haut de `docs/` vers `docs/extra/` (`R100`, aucun changement de contenu) — le matériel de support cesse de concourir dans le comptage des sujets canoniques.
* **Documentation en 5 langues :** **46 sujets canoniques** dans `docs/` (+4 nouveaux − 2 déplacés) — **43 avec couverture complète dans les 5 langues** 🆕 et seulement **3 sujets partiels** (`github-issue-prompt-com-gh`, `version-markers`, `review-pr`), contre 7 dans la fenêtre précédente. ✅
* **Lacune de documentation qui persiste 🆕 :** `explain` et `tests` restent les **seules** fonctionnalités sans sujet dans `docs/` — elles ont gagné l'entrée au registre de skills et les clés, mais pas la page. Les quatre fonctionnalités de cette fenêtre (mentor, risk, policy, baseline) sont nées avec des docs en 5 langues, ce qui rend les deux exceptions encore plus visibles.
* **Skills locaux du Claude Code :** `.claude/skills/` avec **29 skills** (comptage inchangé dans cette fenêtre).
* **Index Mémoire :** `.claude/memory/MEMORY.md` avec **40 patterns** (comptage inchangé dans cette fenêtre).
* **Rapports de tâche :** `docs/claude-code/reports/develop_natan/` (**105** au total ; **+4** — `2026-09-23_release_dedup_links.md`, `2026-09-29_metrics_telemetry_v2.md`, `2026-10-09_policy_packs.md`, `2026-10-10_baseline.md`) et `docs/gemini/reports/develop_natan/` (**14** ; **+7**).
* **Rapports de statut :** `docs/reports/` (16 rapports ; celui-ci est le **17e**).
* **Plans de développement :** 135 fichiers dans `docs/plans/` (+20 dans la fenêtre — specs/plans de release, metrics, risk, policy et baseline ; **ADR-008 à ADR-012** ; les glossaires `metrics-telemetry`, `gitpr-mentor`, `gitpr-risk-scoring`, `policy-packs` et `baseline` ; et le nouveau sous-répertoire `docs/plans/develop_natan/`) + **14 fichiers dans `docs/survey/`** (+4).

---

## **🔄 Pipeline de Distribution**

1. **PyPI (canal unique) :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mise à jour obligatoire :** l'exécution vérifie PyPI au démarrage et **bloque avec le code de sortie 1** s'il existe une version plus récente, en imprimant `pip install --upgrade gitpr-cli` ; la vérification est mise en cache par jour et `--update` ne fait que rapporter
3. **GitHub Releases :** supprimées — pas de PyInstaller, pas d'asset `.exe`, pas de hot-swap
4. **GitHub Actions :** workflow `pr-review.yml` + `action.yml` (installe via pip) + `tests.yml` (matrice Python 3.10 et 3.13). **Le workflow de release manque toujours** — le build et l'upload vers PyPI sont encore manuels.
5. **Serveur MCP :** point d'entrée `gitpr-mcp` via `pyproject.toml`
6. **Templates et langues OTA :** `templates/` et `langs/*.json` servis depuis GitHub (main) — le bump `v0.0.34` renouvelle les copies locales de `~/.gitpr/langs/` une fois publié ; `__scripts_version__` **v0.0.4** renouvelle les hooks (réinstaller avec `gitpr --installhooks`)
7. **Version dérivée du code :** `pyproject.toml` utilise `version = {attr = "src.updater.__version__"}` — `__version__` est la **seule** source
8. **`package-data` compte désormais 🆕 :** les quatre packs embarqués (`src/policy_packs/*`) voyagent dans le wheel ; une étape de build qui les exclut produit un `gitpr policy list` qui ne voit pas les packs d'usine
9. **État des releases 🆕 :** **`v1.3.0` taguée** (2026-09-24, merge de la PR #193) et **`v1.4.0` taguée** (2026-10-12, merge de la PR #207). `CHANGELOG.md` a les deux sections complètes et liées — `[1.4.0]` est la première générée par le moteur corrigé. Le seul changement ouvert dans l'arbre de travail est le correctif des titres localisés de la section `[1.4.0]`, sortis en pt-BR (`Funcionalidades`, `Documentação`, `Tarefas`, `Contribuidores`) et normalisés en anglais.

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.16)**

| Domaine | v0.0.16 (précédent) | v0.0.17 (actuel) |
|------|-------------------|-----------------|
| **Version GitPR** | 1.3.0 (bump **non commité** ; HEAD à 1.2.0) — tag `v1.2.0` | **1.4.0** — **tags `v1.3.0` (24/09) et `v1.4.0` (12/10) créés** ✅ |
| **Version des Langues** | v0.0.32 (bump **non commité**) | **v0.0.34** (v0.0.32 → v0.0.33 → v0.0.34, **commité**) |
| **Version des Scripts de Hook** | v0.0.3 | **v0.0.4** (templates de hook réécrits pour le ledger) |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama (inchangé) |
| **Langues** | 5 langues, 6 dictionnaires | 5 langues, 6 dictionnaires |
| **Sous-commandes** | 9 | **14** (+ `mentor`, `metrics`, `policy`, `risk`, `baseline`) — **4 d'entre elles sont des groupes** (baseline 6, metrics 8, policy 7, tests 1) |
| **Interface** | CLI + TUIs + wizards + 9 sous-commandes | **+ `gitpr mentor`/`--mentor` + groupe `metrics` (avec assistant de migration) + `gitpr policy` + `gitpr risk` + `gitpr baseline`** |
| **Couches** | `src/domain/` et `src/application/use_cases/` | **8 paquets de domaine** (+ `baseline`, `finding`, `mentor`, `policy`, `risk`) et **14 cas d'usage** |
| **Persistance locale 🆕** | — | **`src/ledger.py`** — SQLite à `~/.gitpr/metrics/telemetry.db` (WAL, `user_version`, UUID, bundle/merge) |
| **Outils MCP** | 14 outils / 18 ressources / 7 prompts (`skill://explain` et `skill://tests` **manquants** ⚠️) | **16 outils / 19 ressources / 7 prompts** ✅ (+ `get_usage_metrics`, + `get_baseline_status`, + `baseline://summary` ; registres de skills alignés) |
| **Flags CLI** | 36 à la racine + 9 sous-commandes | **32 à la racine** (−5 metrics, +`--mentor`) + **14 sous-commandes** avec 22 sous-commandes de groupe |
| **Variables d'Environnement** | 49 clés dans `DEFAULT_CONFIG` | **58 clés** (+9 : 4 baseline, 2 mentor, 2 policy, 1 risk) + `GITPR_METRICS_PRICE_*`/`CURRENCY` |
| **Schéma de Config** | 71 `ConfigField` / 14 catégories | **80 `ConfigField` (+9) / 16 catégories** (+ Policy, + Baseline) |
| **Linter** | Regex + secrets intégrés + wildcard + 3 ponts SAST | **+ constats structurés (`findings_out`) + type canonique `src/domain/finding/` + sévérité du Policy Pack + code de sortie tenant compte de la baseline** |
| **Git Hooks** | pre-commit bloque les secrets | **+ post-checkout/post-merge/pre-push appellent `gitpr --quiet metrics hook-event`** (résolution de dépôt en bash, GitHub uniquement, supprimée) |
| **Couche SCM** | 4 forges, `get_pull_request`, `supports_reviewable_diff` | **+ `list_pull_requests` paginé et canonique, `supports_merged_dates`, `within_window()`, `web_links.py` (liens par forge)** — 305 scénarios |
| **Moteur de release** | Plage par `git describe`, sans liens | **Plage ancrée dans la section précédente du changelog, `_drop_released()`, liens commit/PR/contributeur, carte des PR par commit de merge** |
| **i18n (clés par fichier)** | 1158 × 6 — le code utilise 1198 → **40 non traduites** ⚠️ | **1603 × 6 — le code utilise 1603 → zéro non traduite** ✅ (+445) |
| **Documentation** | 44 sujets (37 complets + 7 partiels) | **46 sujets (43 complets + 3 partiels)** — 4 nouvelles familles en 5 langues + 2 dettes de traduction soldées + `docs/extra/` |
| **CI** | `.github/workflows/tests.yml` (3.10 + 3.13) | Inchangée (le workflow de **release** est toujours en attente) |
| **Suite de Tests** | 1889 scénarios (96 fichiers) | **2799 scénarios (129 fichiers) — +910 scénarios, +33 fichiers** (plus grand bond jamais enregistré) |
| **Échecs connus** | 4 (registre de skills à moitié fait, une cause racine) | **2** — les 4 de la dette **clos** ✅ ; 2 subsistent dans `tests/test_mentor_cli.py`, d'**isolation de test** (patch sur `src.core` qui n'atteint pas la liaison `src.main`), sans régression du produit |
| **Dépendances** | — | **+ `packaging>=23.0`** (SemVer/PEP 440 pour le Policy Pack) |
| **Commits depuis le rapport** | 8 commits | **11 commits** (`c561664`, `64a406f`, `4380ce8`, `811651f`, `57c5140`, `c46440f`, `e1cfb11`, `4d42d56`, `8450186`, `af35270`, `1abb1b5`) |
| **PR fusionnés** | 7 PR (#177–#189) | **10 PR (#192, #193, #194, #196, #198, #200, #201, #203, #205, #207)** ; 6 issues fermées (#191, #197, #199, #202, #204, #206) |
| **Index Mémoire** | 41 patterns | **40 patterns** (inchangé dans cette fenêtre) |
| **Rapports de tâche** | 101 claude-code, 7 gemini | **105 claude-code (+4) et 14 gemini (+7)** |
| **Plans de développement** | 115 plans, 10 surveys | **135 plans (+20), 14 surveys (+4)** — inclut ADR-008 à ADR-012 |

---

## **🚧 Prochaines Étapes**

* **Corriger l'isolation de `tests/test_mentor_cli.py` 🆕 :** ce sont les **deux seuls échecs** de la suite aujourd'hui. Le patch pointe sur `src.core.get_git_diff`, mais `src/main.py` importe le nom **par valeur** — la vraie fonction s'exécute, et sur un arbre dont le seul changement est un `.md` (filtré par `:(exclude)*.md` dans les smart excludes), le diff revient vide et le flux retourne avant le renderer. Répété avec le patch sur `src.main.get_git_diff`, les deux scénarios passent. C'est un correctif de deux lignes qui ferme la suite à **2797/2799**.
* **Commiter le correctif du `CHANGELOG.md` 🆕 :** les titres de section de `[1.4.0]` sont sortis localisés (pt-BR) et ont été normalisés en anglais **dans l'arbre de travail**. C'est le seul changement ouvert dans le dépôt — et le paquet publié porte déjà la version portugaise dans le fichier versionné.
* **Documenter `explain` et `tests` :** ce sont, désormais, les **deux seules** fonctionnalités sans sujet dans `docs/` — les quatre arrivées dans cette fenêtre sont nées documentées en 5 langues. **Élément hérité de la fenêtre précédente et toujours ouvert.**
* **Automatiser la release dans GitHub Actions 🆕 :** la CI de tests existe depuis deux fenêtres, mais le build et l'upload vers PyPI sont encore manuels — et dans cette fenêtre l'étape manuelle a été faite **deux fois** (v1.3.0 et v1.4.0). Un workflow de release déclenché sur les tags ferme le dernier tronçon manuel du pipeline.
* **Étendre la couverture du ruleset de secrets :** les trois lacunes restent ouvertes et ont été **reconfirmées dans le code dans cette fenêtre** — affectation sans guillemets (`API_KEY=abc123`, le format `.env`, qui est précisément là où les secrets fuient), les préfixes `ASIA…`, `github_pat_…` et `xoxc-`/`xoxd-`, et l'absence de frontière gauche sur le nom de la clé (aujourd'hui `mytoken` correspond autant que `token`).
* **Traduire `docs/review-pr.*.md` 🆕 :** c'est la **dernière** famille à couverture partielle (2 des 5 langues). `split-command` et `otimizacao-de-tokens` ont été soldés dans cette fenêtre.
* **Retirer du suivi les artefacts de `.gitpr/metrics/export/` 🆕 :** `.gitpr/metrics/` est entré dans `.gitignore` dans cette fenêtre — mais les **35 fichiers déjà suivis restent suivis**, parce que `gitignore` ne retire pas du suivi. Il manque un simple `git rm --cached`. **La dette a cessé de croître, mais elle n'a pas été soldée.**
* **Documenter la rupture de contrat de `request_pull_request_reviewers` :** elle est passée de `None` à `list[str]` ; les sous-classes tierces de `ScmProvider` doivent être mises à jour. `docs/scm-multiforge.*.md` est l'endroit, dans les 5 versions. **Élément hérité, toujours ouvert.**
* **Cartographier le rayon d'impact de la baseline sur le code de sortie 🆕 :** une baseline qui ne peut pas être appliquée **fait désormais avorter** le lint, le review, le full review, l'audit de fichier, le review distant et le risque avec le code de sortie 1. C'est le choix sûr, mais c'est un changement de comportement sur **six** flux — qui mérite un `docs/baseline-suppressions` explicitant le contrat de sortie pour les pipelines de CI.
* **Réconcilier la version du projet :** `CLAUDE.md` dit encore `Current version: 0.0.37` alors que `__version__` est à **1.4.0**. Définir une convention unique. **Élément hérité, toujours ouvert.**
* **`gitpr -h config` ignore `-h` :** la sous-commande ouvre la TUI au lieu d'afficher l'aide — la barrière `if ctx.invoked_subcommand is not None: return` s'exécute avant le bloc `help_flag`. Le corriger changerait le comportement de `-h` pour **toutes** les sous-commandes, donc cela demande une décision. **Élément hérité, toujours ouvert.**
* **Migrer l'extracteur de `sync_i18n.py` vers l'AST :** la regex tronque les littéraux avec concaténation implicite (`__("a " "b")`). **Progrès partiel 🆕 :** `tests/test_i18n.py` utilise déjà un extracteur basé sur l'AST (`_extract_keys_ast`) et c'est lui qui vérifie la couverture aujourd'hui ; le script de synchronisation est encore sur regex.
* **Section Smart Exclude dans la TUI :** des 12 éléments signalés après l'utilisation de l'écran, l'élément 10 (*Smart Exclude*) est la seule livraison pas encore commencée — et les champs `GITPR_SMART_EXCLUDES_*` restent sous `diff`, sans catégorie propre.
* **Dettes consignées dans le plan de la TUI de config :** `DEFAULT_CONFIG` est devenu redondant avec le schéma ; la bannière d'ouverture ne liste pas `--init`, `--base` ou `--plugins` (le `--dashboard` a quitté la CLI, donc cette partie de l'élément est devenue obsolète 🆕) ; `LinterApp` est la **seule** app Textual qui ne désactive toujours pas la palette de commandes.
* **Fournisseur Anthropic Claude :** Support direct de l'API Claude (`claude-sonnet-5`).
* **Graphiques ASCII/Textual dans le Dashboard :** Histogrammes temporels et tendances de tokens dans la TUI de métriques — le dashboard lit déjà le ledger, ce qui était le prérequis manquant.
* **Seed pour le `.gitpr/conf/` local :** L'amorçage des templates de configuration locale (smart-excludes, linter) reste en attente, comme sous-commande propre ou étape de wizard.
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires.

### ✅ Terminé dans cette fenêtre (2026-09-23 → 2026-10-12)

* ~~**Clore la dette des deux nouvelles fonctionnalités**~~ — `SKILL_LABELS` (8 → **11**) et `mcp_server.SKILL_FILES` (8 → **11**) ont reçu `tests`, `explain` et `mentor` ; les **40 clés `__()` non traduites** ont été résolues et la couverture a atteint 1603/1603. **Les 4 échecs sont devenus 0, avec les assertions intactes.**
* ~~**Clore la release 1.3.0**~~ — tag **`v1.3.0`** créé (merge de la PR #193, 2026-09-24) et `CHANGELOG.md` complété avec les six fonctionnalités qui manquaient. **C'était l'élément qui bloquait deux fenêtres.**
* ~~**Clore la release 1.4.0**~~ 🆕 — tag **`v1.4.0`** créé (merge de la PR #207, 2026-10-12), avec les trois bumps (GitPR, langues, scripts) **commités** et plus dans l'arbre de travail.
* ~~**`gitpr release` répète l'historique et n'a pas de liens**~~ 🆕 — cutoff ancré dans la section précédente du changelog, `_drop_released()`, liens commit/PR/contributeur, carte des PR par commit de merge et `web_links.py` (PR #192).
* ~~**Télémétrie en JSON avec `tokens_actual` toujours à zéro**~~ 🆕 — ledger SQLite avec écriture synchrone, tokens copiés au moment de l'écriture, groupe `gitpr metrics`, assistant de migration et coût par modèle (PRs #194/#196).
* ~~**Mentor Mode**~~ 🆕 — `src/domain/mentor/`, cas d'usage, `gitpr mentor` + `--mentor`, skill en 5 langues (PR #198).
* ~~**Score de risque déterministe**~~ 🆕 — `src/domain/risk/`, `calculate_risk`, lecteurs d'historique et de tests, `gitpr risk` et section ajoutée au review (PR #200).
* ~~**Policy Packs**~~ 🆕 — `src/domain/policy/`, `src/infrastructure/policy/`, 4 packs embarqués, groupe `gitpr policy` et dépendance `packaging` (PR #203).
* ~~**Baseline et suppressions auditables**~~ 🆕 — `src/domain/baseline/`, `src/domain/finding/` canonique, barrière dans les six flux, outil et ressource MCP (PR #205).
* ~~**Traduire les sujets partiels**~~ — `docs/split-command.*.md` et `docs/otimizacao-de-tokens.*.md` ont complété les 5 langues.
* ~~**`.gitpr/metrics/` hors du contrôle de version**~~ — entré dans `.gitignore` avec un commentaire expliquant ce qui **n'est pas** ignoré (`.gitpr/skill/`) et pourquoi.
* ~~**Hygiène de `docs/`**~~ — deux familles de matériel de support (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) ont été déplacées vers `docs/extra/`, les sortant du comptage des sujets canoniques.
* ~~**Hooks qui ne fonctionnaient que sur GitHub**~~ — `post-checkout`, `post-merge` et `pre-push` délèguent désormais à GitPR lui-même, avec `__scripts_version__` à v0.0.4.

---

**Rapport généré le :** 2026-10-12  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
