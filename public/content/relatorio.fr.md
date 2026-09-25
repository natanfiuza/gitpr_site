# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.16 (2026-09-23)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.16) :**
- **`gitpr demo` — la première exécution a cessé d'être un acte de foi :** Une visite guidée qui montre un message de commit, un review et une description de PR **sans clé API, sans dépôt git et sans réseau**. La question à laquelle elle répond (« que fait cet outil ? ») n'a qu'une seule fenêtre pour être posée — la première utilisation, avant que l'utilisateur n'ait configuré un fournisseur. La visite passe par le **vrai pipeline de génération** avec la source de la réponse échangée, donc ce qui apparaît est ce que l'outil produit réellement, tandis que chaque effet externe (cache, métriques, disque, réseau, lecture des clés) est neutralisé.
- **Badge GitPR dans le corps du PR + commande `gitpr badge` :** Un PR écrit par l'IA était indistinguable d'un PR écrit à la main, et le résultat du linter mourait dans un terminal qui avait défilé hors de l'écran. Le badge transforme ce signal privé en une déclaration visible dans le PR publié lui-même — et il est délibérément une URL statique shields.io, que GitPR ne récupère **jamais**, pour que publier un PR n'en vienne pas à dépendre de la disponibilité d'un tiers. Mesure honnête : sans règles de linter configurées, le badge **n'est pas émis**, parce qu'une liste vide veut dire « rien n'a été vérifié », et non « rien n'a été trouvé ».
- **`gitpr split` — un arbre de travail à plusieurs intentions cesse de devenir un blob de commit :** Il lit le diff non commité, demande à l'IA de partitionner les hunks par intention logique et propose **un commit atomique par préoccupation**, chacun avec un message généré à partir du patch de ce groupe isolé. L'arbre n'est jamais réécrit : les fichiers terminent identiques au byte près à leur état initial — seul l'historique change. *(L'index, oui : `--apply` réinitialise sur HEAD avant le staging.)*
- **Analyse de secrets intégrée — la règle qui ne peut pas être remplacée :** Sept règles (`src/security_ruleset.py`) qui s'exécutent à **chaque** invocation du linter : cinq `error` bloquantes (ID de clé AWS, jeton GitHub/Slack, clé Google, bloc de clé privée) et deux `warning`. Le catalogue local était entièrement géré par l'utilisateur — il pouvait être écrasé par le téléchargement, réécrit par le wizard (en perdant les commentaires) ou étendu seulement par des plugins locaux à la machine. Une barrière de secrets doit se comporter de la même façon sur chaque machine, donc les règles vivent désormais **à l'intérieur du paquet**. **Changement de comportement : un commit qui passait peut désormais être bloqué.**
- **Prise en charge de `extensions: ["*"]` :** Cela signifie désormais *tous* les fichiers, y compris ceux sans suffixe — ce qui est précisément d'où les secrets fuient (`id_rsa`, `.env`, `credentials`, `Dockerfile`). Une règle avec `extensions: ["py"]` conserve le filtre exactement comme avant.
- **Ponts SAST opt-in — Semgrep, Gitleaks et Bandit :** Une couche qui branche des scanners de sécurité tiers sur le linter existant. Ils s'exécutent **uniquement** lorsqu'ils sont activés, **uniquement** sur les fichiers touchés par le diff, et les constats sont dédupliqués par rapport au ruleset interne : un secret vu par les deux apparaît **une seule fois** avec un marqueur de confirmation multi-source (`[Gitleaks + Regex]`). Gitleaks a ses valeurs masquées (`AKIA****`) avant de devenir un constat.
- **`gitpr tests generate` — la suite qui respecte la convention du dépôt :** Génère des fichiers de test complets à partir du diff, d'un fichier spécifique ou d'un constat de review. Il détecte le framework utilisé (Pest, PHPUnit, Jest, Vitest, Pytest) au lieu d'imposer un style, calcule le chemin de destination conventionnel (la séparation `Feature`/`Unit` de Laravel, le `tests/**/test_*.py` de Pytest) et valide la syntaxe avec la chaîne d'outils locale (`php -l`, `node --check`, `python -m py_compile`) — un échec de validation devient un avertissement, pas une erreur. Le dry run est le défaut.
- **`gitpr explain` + le flag `--explain` — le guide de celui qui va réviser :** Un guide centré sur le reviewer (ce qui change, pourquoi cela change, où se concentrer, quel est le risque de régression) pour que personne n'ait à reconstruire l'intention à partir d'un diff brut. Disponible comme sous-commande propre et comme flag qui ajoute la section à la description de PR générée.
- **Architecture en couches — `src/domain/` et `src/application/` :** Les deux dernières fonctionnalités (`tests` et `explain`) sont nées avec une séparation explicite entre les règles de domaine pures et l'orchestration des cas d'usage, et la CLI et la TUI de chat partagent désormais **le même** cas d'usage au lieu de le dupliquer.
- **Suite déterministe et première CI :** `tests/conftest.py` est devenu hermétique (il fixe `GITPR_LANG=en_us` et désactive le ruleset de secrets) et `.github/workflows/tests.yml` exécute la suite sur **Python 3.10** (le plancher déclaré, jamais exercé) et 3.13. C'est la CI qui a rendu visible la dérive de locale — **22 tests** échouaient sur une machine pt-BR parce qu'ils affirmaient le littéral anglais.
- **Les 3 échecs hérités de trois rapports d'affilée ont été clos :** les deux tests de timeout obsolètes (`600s` contre le défaut réel de `180s`) et le test sensible à la locale. La ligne de base de la suite a cessé d'être « 3 échecs connus » et est devenue **verte par construction**.
- **Dette nouvelle et concentrée :** les deux fonctionnalités les plus récentes (`tests` et `explain`) sont arrivées avec le registre de skills **à moitié fait** — **40 clés `__()`** utilisées dans le code n'existent dans aucun des 6 dictionnaires, et les registres de libellés de la config et du MCP n'ont pas reçu les nouveaux types. Cela fait **4 échecs** dans la suite complète, tous avec la même cause racine.
- **État de la release 1.3.0 :** `__version__` et `__lang_version__` sont dans le **working tree et non commités** (HEAD est encore à 1.2.0 / v0.0.31), `CHANGELOG.md` **a** l'entrée `[1.3.0] - 2026-09-21` — mais elle ne couvre **que** l'analyse de secrets, et non `demo`, `badge`, `split`, SAST, `tests` ou `explain`. Le tag `v1.2.0` **a été créé** (merge du PR #174), clôturant l'élément qui bloquait la fenêtre précédente.

- **Version actuelle :** 1.3.0 (bump dans le working tree — HEAD à 1.2.0 ; dernier tag `v1.2.0`)
- **Version des dictionnaires de langue :** v0.0.32 (bump dans le working tree — HEAD à v0.0.31)
- **Version des scripts de hook :** v0.0.3
- **Publication :** PyPI (`pip install gitpr-cli`) — canal binaire supprimé
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licence :** LGPL-2.1
- **Langues prises en charge :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues, 6 dictionnaires)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **CLI Framework :** Click (pour les commandes, flags et formatage du terminal) — **9 sous-commandes** 🆕 (`badge`, `config`, `demo`, `explain`, `fix`, `release`, `review-pr`, `split`, `tests`).
* **UI/Terminal :** Textual — TUI pour le chat interactif, l'édition d'issues, l'écran d'aide, le tableau de bord de métriques, le PR Publisher, les erreurs du linter (`LinterApp`), la configuration (`ConfigApp`), le modal `NoticeScreen` et la **visite `gitpr demo`** (`src/ui/demo/`) 🆕.
* **Architecture en Couches 🆕 :** `src/domain/` (règles pures, sans I/O) + `src/application/use_cases/` (orchestration) + présentation (CLI/TUI). Introduite avec `tests` et `explain` ; la CLI et le `/tests` du chat appellent **le même** cas d'usage.
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API, des jetons GitHub et des jetons SCM des forges.
* **Configuration :** `python-dotenv`, `pyyaml` (linter statique) + son propre schéma déclaratif (`src/config_schema.py`, **71 `ConfigField`** 🆕).
* **Fournisseurs IA :** Intégration via le SDK officiel Google GenAI (`gemini-2.5-flash`), le SDK OpenAI (`DeepSeek`) et le SDK OpenAI (`Ollama` local).
* **APIs de Forge :** `requests` (REST) — abstraction multi-forge dans `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) ; `src/github_api.py` conservé comme shim déprécié.
* **Linter / SAST 🆕 :** Regex YAML + pont Checkstyle + **ruleset de secrets intégré** (`src/security_ruleset.py`) + **4 ponts dans `src/infrastructure/linter/external/`** (base + Semgrep, Gitleaks, Bandit), avec un modèle normalisé dans `src/domain/linter/sast_finding_mapper.py`.
* **Interne de Git 🆕 :** `src/infrastructure/git/` — `patch_applier.py` (déplacé depuis `src/fix/`) et `selective_stager.py` (staging d'un sous-ensemble arbitraire de hunks).
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **14 outils annotés, 18 ressources, 7 prompts** (compte inchangé dans cette fenêtre).
* **Tests :** Pytest + `unittest.mock` (**96 modules de test — 44 à la racine, 9 dans `tests/scm/`, 10 dans `tests/fix/`, 6 dans `tests/split/`, 6 dans `tests/demo/`, 7 dans `tests/badge/`, 4 dans `tests/review/`, 4 dans `tests/infrastructure/linter/external/`, 2 dans `tests/domain/tests_generation/`, 2 dans `tests/application/use_cases/`, 1 dans `tests/domain/pr/`, 1 dans `tests/domain/linter/`** 🆕 —, **1889 scénarios collectés**) + tests e2e du serveur MCP via un vrai subprocess (JSON-RPC stdio) + fixtures de dépôt git réel (`tests/fix/git_fixture.py`, `tests/split/git_fixture.py`) + une **garde d'isolation réseau** (`tests/demo/conftest.py`).
* **Empaquetage :** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`.
* **CI/CD :** GitHub Actions — `pr-review.yml` + `action.yml` + **`tests.yml`** 🆕 (matrice Python 3.10/3.13).

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec le LLM en demandant une sortie strictement JSON.
* **Map-Reduce (Diffs Géants) :** Lorsque le diff dépasse ~90k tokens, le divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce). Prend en charge les PRs, commits et Issues.
* **Tokenizer Local :** `tokenizer.json` pour une estimation précise des tokens avant l'envoi à l'IA.
* **Estimation des Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()` avec fallback vers le tokenizer local.
* **Optimisation Native de Git :** Flags `-U1`, `-w`, `-M`, `-B` sur les commandes `get_git_diff` et `get_git_full_diff` pour réduire le contexte inutile.
* **`get_split_diff()` 🆕 :** Le diff propre au `split`, avec `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **délibérément sans** réutiliser `get_git_diff()` — le `-w` (ignore les espaces) et les smart-excludes casseraient la garantie d'arbre identique au byte près, parce que le patch reconstruit doit correspondre au fichier sur le disque.
* **Pre-Save (`--pre-save`) :** Flag caché de debug qui enregistre le payload complet (instruction système + prompt) en JSON avant chaque appel à l'IA.
* **Smart Excludes à Deux Couches :** Couche globale (`~/.gitpr/conf/`) + couche locale du projet (`./.gitpr/conf/`), fusionnées au runtime. Auto-seed du fichier local à la première exécution. `_load_smart_excludes()` accepte `force=`.
* **Métriques avec Suivi du Temps :** `log_command_metric()` dans tous les flux avec `duration_ms` et imports paresseux.
* **Résolution Centralisée de la Sortie :** `resolve_output_path()` — par défaut dans `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`) :** `gitpr --init` — détecte la forge depuis le remote origin, demande les extras propres à chaque forge, valide le jeton et persiste **uniquement en cas de succès**. 🆕 Il annonce le badge automatique sur le chemin du PR et informe le commutateur exact qui le désactive.
* **Template de Skill de Release (`ensure_release_skill_template()`) :** Télécharge `templates/gitpr.release.*.md` à la première utilisation de `gitpr release`.
* **Registre de Skills Partagé :** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` dans `src/config.py`. 🆕 Il a gagné les entrées pour `tests` (`.gitpr.tests.md`) et `explain` (`.gitpr.explain.md`).
* **Moteur de Review avec Périmètre de Cache (`generate_pr_content`) :** `cache_scope` (attaché **uniquement à la clé de cache**) et `store_diff` (écrit le diff révisé). Les défauts gardent le chemin local identique au byte près.
* **Trailer de Co-paternité :** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, préserve les trailers de tiers.
* **Subprocesses Blindés :** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` sur chaque `subprocess.run` ; vérification de connexion via socket `8.8.8.8:53` avant les opérations réseau.

### **2. Système Global de Plugins (`src/plugins.py`)**

* **Architecture de Plugins :** Charge les plugins depuis `~/.gitpr/plugins/`, en s'appliquant à **tous les projets**.
* **Plugins de Linter (`linter/`) :** Fichiers `.yml` fusionnés avec le `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`) :** Fichiers `.md` qui étendent le contexte système.
* **Factory Closures :** `get_linter_plugins` et `get_prompt_plugins` isolent l'état entre les sessions.
* **Commande `--plugins` :** Liste tous les plugins globaux installés avec leurs types et chemins.
* **Documentation Multilingue :** `docs/plugins-system.md` en 5 langues.

### **3. Interface CLI et Configuration (`src/main.py` et `src/config.py`)**

* **Setup Initial :** Détecte la première exécution, crée `~/.gitpr/` et demande interactivement les clés, les préférences et la langue. 🆕 Le hint de première exécution pointe vers `gitpr demo`.
* **Routage des Commandes :** Gère tous les flags et les **9 sous-commandes** — `release`, `config`, `fix`, `review-pr`, **`demo`** 🆕, **`badge`** 🆕, **`split`** 🆕, **`tests`** 🆕 (groupe, avec `generate`) et **`explain`** 🆕.
* **Comportement par Défaut :** Exécuter `gitpr` sans flags ouvre la TUI du PR Publisher.
* **Flags (désormais 36 options Click à la racine, +1 dans cette fenêtre) :**
  * **`--explain` 🆕 :** Inclut la section *Reviewer Guide* dans la description du PR (et dans le payload JSON émis).
  * `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--linter-setup`, `--version` — inchangés.
* **Sous-commande `demo` 🆕 — 3 options :** `--scenario`, `--lang` et `--no-tui` (front-end en texte brut pour la CI et les enregistrements). Le `--lang` est appliqué **à l'intérieur** de la sous-commande, parce que le callback racine retourne avant le handler.
* **Sous-commande `badge` 🆕 — 2 options :** `--readme` (forme nue du snippet, compatible pipe) et `--style` (`flat`/`flat-square`/`for-the-badge`). **Affiche seulement** — le README n'est jamais modifié.
* **Sous-commande `split` 🆕 — 5 options :** `--dry-run`, `--apply`, `--yes`, `--max-groups` et `--provider`. Une invocation nue affiche le plan et demande confirmation avant de commiter quoi que ce soit.
* **Groupe `tests` 🆕 → sous-commande `generate` — 5 options :** `--file`, `--finding`, `--framework`, `--apply` et `--provider`. Le dry run est le défaut et écraser un test existant demande confirmation avec **Non** présélectionné.
* **Sous-commande `explain` 🆕 — 1 option :** `--provider`. Elle détecte un diff manquant avec une erreur claire avant tout appel d'IA.
* **Variables d'Environnement (49 clés dans `DEFAULT_CONFIG`, +5 dans cette fenêtre) :** `GITPR_LINTER_SECURITY`, `GITPR_LINTER_SECURITY_DISABLED_RULES` et les trois `GITPR_SPLIT_*` (`MAX_GROUPS`, `MAX_HUNKS`, `REQUIRE_CONFIRMATION`). **5 autres sont en lecture seule / non seedées :** `GITPR_BADGE` (défaut `true`), `GITPR_EXPLAIN_BY_DEFAULT` (défaut `false`) et les trois `GITPR_SAST_*_ENABLED` — GitPR ne les écrit **jamais** tout seul dans `~/.gitpr/.env`.
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique de la fonctionnalité avec un lien tenant compte de la langue. Les sous-commandes ont leur propre `epilog=` (paragraphe `\b` de Click pour que l'URL ne soit ré-enroulée sous aucune locale). 🆕 `explain` a rejoint `HELP_MAP`/`HELP_PRIORITY`.
* **--lang / --provider / --mcp / --install / --metrics / --status** — inchangés.
* **Couche d'Écriture du `.env` :** `read_env_file_values()` lit **uniquement le fichier** via `dotenv_values` ; `save_config_values()` avec `set_key` ; `remove_config_value()` avec `unset_key`. `validate_ai_key()` distingue un identifiant refusé (`401`/`403`) d'un réseau injoignable.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)**

* **Interface Interactive Complète :** TUI pour réviser, éditer et publier des Pull Requests directement depuis le terminal.
* **7 Écrans Modaux :** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` et `NoticeScreen`.
* **Badge dans le Corps du PR 🆕 :** `attach_pr_badge()` s'exécute dans `src/main.py` juste après que `pr_data` est complet — un **point d'injection unique**, donc les deux publishers (la zone de texte de la TUI et `--no-edit`) lisent le badge au même endroit. L'utilisateur **voit** le badge et peut le supprimer avant l'envoi ; `--no-edit` imprime une ligne disant ce qui est entré dans le corps sans qu'il l'ait vu. Ajout idempotent par marqueur — republier ou déplacer le badge n'empile pas les badges.
* **`_attach_reviewers` avec résolution :** Résout ce que l'utilisateur a saisi **avant** l'envoi, signale ce qui a été écarté et, sur un `422` de lot, **réessaie un par un** — GitHub rejette le lot entier quand un seul login est inéligible.
* **Reviewers Suggérés :** Interroge la forge pour obtenir des reviewers suggérés ; `_reviewer_suggestion_view()` monte les `resolutions` via `resolve_candidates` et pré-remplit `handles`.
* **Bindings :** F1 (Aide), F2 (Enregistrer .md local), F3 (Publier via la forge), Échap (Quitter).
* **Flux d'Auto-Commit :** Linter → message IA → confirmation → commit → push → publie le PR.
* **Vérification des Fichiers Unstaged :** `git status --porcelain` à l'entrée, avec un modal pour sélectionner, sauter ou annuler.
* **Gestion de PR Existant / Auto-Upstream / Flux de Merge** — inchangés (`GITPR_AUTO_MERGE`).

### **5. Module API GitHub (`src/github_api.py`)**

* **Shim Déprécié :** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` et les fonctions restantes délèguent à `src/infrastructure/scm/github_provider.py` ; il émet un `DeprecationWarning` et conserve les tuples legacy `(ok, data, status)` — aucun nouveau code ne doit l'importer.

### **6. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le diff sans dépenser de quotas d'IA.
* **Règles YAML :** Lit `.gitpr.linter.yml` (créé via `--skill`).
* **Plugins de Linter :** Règles supplémentaires depuis `~/.gitpr/plugins/linter/*.yml`.
* **Wildcard d'Extension 🆕 :** `extensions: ["*"]` signifie désormais **tous** les fichiers, y compris ceux sans suffixe et les dotfiles. C'est ce qui donne une couverture à `id_rsa`, `.env`, `credentials` et `Dockerfile` ; une règle avec `extensions: ["py"]` conserve le filtre tel qu'il a toujours été.
* **Ruleset de Secrets Intégré 🆕 :** `load_linter_rules()` fusionne `src/security_ruleset.py` **après** les règles du projet et des plugins, qui restent intactes — seul l'ordre change, poussant les alertes de sécurité à la fin du rapport. Sept règles, toutes `extensions: ["*"]` : cinq `error` (ID de clé AWS, jeton GitHub/Slack, clé Google, bloc de clé privée) et deux `warning` (URL de base de données avec identifiants et affectation d'identifiant générique, derrière un filtre de placeholders).
* **L'Alerte N'Affiche Jamais la Valeur :** Le message ne porte que `{file_name}` et `{line_number}` — il voyage jusqu'à la console, jusqu'au rapport Markdown et, dans le flux PR, jusqu'au corps d'une pull request publique ; imprimer le secret le copierait dans les trois.
* **`--input` Élargi 🆕 :** L'audit de fichier entier scanne désormais `.md`, `.txt` et les lockfiles, puisque le ruleset correspond à toutes les extensions. Là, il **rapporte sans bloquer** — le `sys.exit(1)` n'existe que sur le chemin `--linter`.
* **Ponts SAST Opt-In 🆕 :** Semgrep, Gitleaks et Bandit tournent **et** sur le chemin de fichier entier **et** sur le chemin de diff, en filtrant les constats par lignes ajoutées et en avertissant lorsque l'outil est activé mais absent du `PATH`. `load_sast_config()` résout en trois couches : défauts → `.gitpr.linter.yml` (bloc `sast`, avec fallback vers `linter.external`) → variables `GITPR_SAST_*`. **Tout est à `false` par défaut** (opt-in strict).
* **`skip_external` :** `parse_diff_and_lint(..., skip_external=False)` désactive **les deux** call-sites du pont externe ; le flux distant passe `True`, parce que le pont exécute des binaires contre des fichiers sur le disque — l'arbre local, pas le PR.
* **Rapport Consolidé :** `generate_linter_report_content()` consolide les erreurs regex + externes dans `.gitpr/reports/linter/` — généré uniquement en cas de violations.
* `load_linter_presets()` accepte `force=` pour re-télécharger depuis la TUI.

### **7. Sécurité et Authentification (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Chiffrement :** Génère la clé maîtresse `secret.key` dans `~/.gitpr/`.
* **Protection des Jetons :** `encrypt_data`/`decrypt_data` pour les clés d'IA, le PAT GitHub et les jetons SCM des forges.
* **Validation Multi-Forge :** `validate_or_request_scm_token(provider, repo_display)` — 401 → boucle de réauthentification préservant le brouillon ; le jeton GitHub legacy reste fonctionnel jusqu'à l'exécution de `--init`.
* **Secrets dans la TUI de Configuration :** Les champs `KIND_SECRET` sont édités masqués, **n'affichent jamais** la valeur en clair et sont chiffrés avec Fernet avant d'être écrits. `GITPR_SCM_TOKEN` est `read_only`.
* **Analyse de Secrets dans le Flux Lui-Même 🆕 :** Le linter — qui tourne dans le hook pre-commit — est devenu une barrière d'identifiants avec des règles identiques sur chaque machine, parce qu'elles vivent dans le paquet et non dans un template téléchargé que `--skill` ou le wizard peuvent remplacer.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI comme Source Unique :** `get_latest_remote_version()` interroge `https://pypi.org/pypi/gitpr-cli/json` et écrit le cache quotidien **sans** le champ `download_url`.
* **Barrière Obligatoire (`enforce_update_required()`) :** Retourne `True` (après avoir affiché les deux versions et la commande pip) lorsque la version publiée est plus récente ; `False` lorsque tout est à jour, lorsque la version distante est **inconnue (hors ligne)** ou lorsque la vérification est désactivée. Retourner un `bool` au lieu d'appeler `sys.exit` en interne garde la fonction testable.
* **`check_and_update()` :** Se contente d'interroger et de **rapporter**, n'installe jamais.
* **Échappatoire :** `GITPR_SKIP_UPDATE_CHECK` — elle n'est pas annoncée ; elle existe pour la suite et pour l'automatisation hors ligne.
* **Versionnage Centralisé :** `__version__` (**1.3.0** — bump dans le working tree), `__lang_version__` (**v0.0.32** — chaîne v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32 dans cette fenêtre 🆕), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.

### **9. Interface de Chat Interactive (`src/ui/chat_app.py`)**

* **TUI Complète :** Historique des messages, saisie multi-ligne, barre d'état avec bindings visibles.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique persisté par branche, avec continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear`. 🆕 `/tests` (et les alias localisés `/testes`, `/pruebas`) **a cessé d'être transmis au modèle générique** et délègue désormais au **même cas d'usage** que la CLI (`TestGenerationTarget`), au lieu de dupliquer la logique.
* **Auto-Patching (F5), Rafraîchissement du Diff (F2), Export de Session (F6).**
* **Extracteur Partagé :** F5 et `ctrl+s` appellent `patch_extractor.extract_code_blocks()` — comportement visible identique, sans logique dupliquée.

### **10. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système à la première exécution et l'enregistre dans `GITPR_LANG`.
* **5 Langues, 6 Dictionnaires :** en_us (défaut/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Fichiers Versionnés :** `__lang_version__` (**v0.0.32**) contrôle la mise à jour des packs de langue (`langs/*.json`) — chaîne de bumps v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32 dans cette fenêtre.
* **Couverture :** **1158 clés** dans chacun des 6 fichiers — parité totale des key sets (+110 depuis le rapport précédent). **Réserve importante 🆕 :** le code utilise **1198** clés, donc **40 clés `__()` n'existent dans aucun dictionnaire** (voir §33 et §34) — la parité entre les 6 fichiers est totale, mais la couverture par rapport au code ne l'est pas.
* **Correction de la Première Exécution 🆕 :** `i18n.py` **crée désormais le répertoire de profil avant** de persister la langue détectée, évitant un crash à la première exécution sur une machine propre.
* **Photo de l'Environnement (`AMBIENT_ENV_KEYS`) :** `frozenset(os.environ)` capturé **immédiatement avant** le `load_dotenv()` au niveau du module — corrige le badge « ⚠ dans l'environnement » qui confirmait tautologiquement que la clé est dans le fichier.
* **Cache avec Indexation par Langue :** Les réponses IA en cache incluent la langue courante dans le keying MD5.

### **11. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA avec des caractères braille et des mots de « réflexion ».
* **263 entrées par langue :** Synchronisées entre les 5 langues. `_load_thinking_words()` / `reload_thinking_words()` acceptent `force=`.

### **12. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Prise en charge :** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Mode JSON & Paramètres Déterministes :** `temperature=0.0` et `top_p=0.1` ; fallback automatique entre les fournisseurs configurés.
* **Timeout IA :** défaut de **180s** (`GITPR_AI_TIMEOUT`) — la valeur de 600s a été délibérément abaissée dans le fix `681a7fa` et le test obsolète a finalement été aligné dans cette fenêtre (voir §37).

### **13. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Keying par hash MD5 du diff et du prompt, avec indexation par langue.
* **Sélection du Dernier Review (`resolve_last_review()`) :** Choisit l'enregistrement `review`/`fullreview` **le plus récent** pour un couple repo+branche, en excluant les reviews à périmètre de fichier — c'est la porte d'entrée de `gitpr fix`.
* **Diff Révisé (`reviewed_diff`) :** Champ en tête d'enregistrement qui conserve le diff effectivement révisé, préféré par `fix/apply_fix.reviewed_diff()`.
* **Télémétrie et Durée :** Persistance de `duration_ms` et `meta_raw`.
* **Lecture pour le Dashboard :** `scan_cache_files_for_dashboard()` lit tous les fichiers de cache récursivement.

### **14. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff actuel, Historique de la branche (`-ht`) et Archéologie par Blame (`-b`).
* **Publication Multi-Forge :** F3 crée l'issue sur la forge **configurée** (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — Azure DevOps lève `ScmNotSupportedError`.
* **Map-Reduce pour les Issues :** Le contexte au-dessus de ~90k tokens est découpé et unifié.
* **Gestion du 401 :** Signalisation de réauthentification sans fermer l'application.

### **15. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Suit l'évolution et la paternité historique avec classification des commits (`ORIGIN` vs `REFACTORING`).
* **Métriques de Blame :** Événements via `log_blame_metric()` avec la profondeur et le nombre de commits analysés.

### **16. Serveur MCP et Invocation CLI Directe (`src/mcp_server.py`)**

* **14 Outils MCP Annotés :** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates` et `review_remote_pr` — **compte inchangé dans cette fenêtre**.
* **18 Ressources + 7 Prompts Templatisés :** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `prompt://list` + 7 prompts. **Réserve 🆕 :** `skill://explain` et `skill://tests` n'existent **pas** — le test `TestSkillRegistryAgreement` échoue précisément parce que `mcp_server.SKILL_FILES` n'a pas reçu les deux nouveaux types que `config.SKILL_FILES_BY_TYPE` a déjà.
* **Invocation CLI Directe :** `gitpr-mcp --tool <name> [--tool-args '<json>']` invoque n'importe quel outil sans démarrer le serveur stdio ; `gitpr-mcp --list` affiche le registre complet en JSON.
* **Isolation du Stdout Réel :** `_write_real_stdout()` écrit dans le `sys.__stdout__` original, garantissant du JSON pur. 🆕 Durci contre `UnicodeEncodeError` sur les code pages Windows legacy (fallback vers `buffer` ou ré-encodage avec `errors='replace'`).
* **Offload de l'Event Loop :** Décorateur `_offload` (`anyio.to_thread.run_sync`) sur les 14 outils — l'ordre des décorateurs compte (`@mcp.tool` **au-dessus** de `@_offload`).
* **Journal d'Utilisation :** Le `main()` du serveur appelle `log_usage()` — le script console `gitpr-mcp` ne charge jamais `main.py`.
* **Tests E2E :** `tests/test_mcp_server_e2e.py` démarre le vrai serveur comme subprocess et parle JSON-RPC stdio.

### **17. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`)**

* **Périmètre par Dépôt :** Étiquette `📁 Repository: owner/repo` et filtrage strict par projet.
* **Scan Asynchrone avec Overlay :** Worker thread en arrière-plan avec une `ProgressBar`. 🆕 Les tests attendent désormais `workers.wait_for_complete()` avant d'affirmer — sans cela, la suite était instable à cause d'une race.
* **Consolidation des Données :** `load_cache_token_summary()` ajoute les tokens du cache au totalisateur.
* **Export Local :** CSV/JSON dans `./.gitpr/metrics/export/` — 🆕 davantage d'artefacts générés sont entrés **suivis** (`gitpr_metrics_2026-09-18/19/21/22.*`) ; la dette du `.gitignore` reste ouverte.

### **18. Système de Métriques et Télémétrie (`src/metrics.py`)**

* **Périmètre par Dépôt :** Tous les événements indexés par `repo_name`.
* **Événements de Hook, Linter et Blame :** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Export et Nettoyage :** `--metrics --export` (CSV/JSON) et `--metrics --purge` avec confirmation interactive.

### **19. Synchronisation de la Langue des Git Hooks**

* **Versionnage Indépendant :** `__scripts_version__` (v0.0.3).
* **Correspondance des Suffixes (`HOOK_SCRIPT_SUFFIXES`) :** Les codes d'interface (`es_es`, `fr_fr`) traduits vers les suffixes publiés (`.es`, `.fr`).
* **Choix vs. État (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`) :** Cela permet de **détecter un changement de langue**.
* **`effective_hook_lang()` :** Résout la langue effective ; `--lang` n'est plus écarté sur ce chemin.
* **Skip de Merge-Source :** `prepare-commit-msg` saute les sources `message|merge|squash|commit`.

### **20. Pont de Linters Externes et Assistant Interactif (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistant `--linter-setup` :** Wizard avec presets numérotés (PHP_CodeSniffer, ESLint, Stylelint) et injection du bloc `external_linters`.
* **Presets Distants :** `templates/gitpr.linter-presets.json` avec la chaîne de fallback local → téléchargement → stale → embarqué.
* **TUI d'Erreurs du Linter :** `src/ui/linter_app.py` (Textual) affiche les erreurs critiques et les warnings ; en mode hook/quiet, imprime et fait `sys.exit(1)`.
* **Rapport Markdown :** Consolidé dans `.gitpr/reports/linter/` uniquement en cas de violations.
* 🆕 **La Couche SAST est une Sœur, Pas un Remplacement :** les ponts Semgrep/Gitleaks/Bandit vivent dans `src/infrastructure/linter/external/` (voir §31) et alimentent le **même** pipeline de rapport.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstraction Unique (`ScmProvider` ABC) :** `base.py` définit le contrat (dataclasses `RepoRef`, `PullRequestDraft` etc. et `ScmProviderError(provider, http_status, message)` — `http_status` 0 = échec réseau) ; un provider concret par forge.
* **Registre et Factory :** `resolve_scm_provider()` sélectionne via `GITPR_SCM_PROVIDER` (défaut `github`) ; `detect_provider_from_remote()` identifie la forge à partir de l'URL origin.
* **Adressage des Dépôts :** `parse_repo_ref(remote_url) -> RepoRef(raw, workspace, name, provider)`.
* **`get_pull_request(repo, pr_id)` — méthode concrète de l'ABC :** le défaut lève `ScmNotSupportedError` et chaque forge l'implémente — `list_open_pull_requests` pagine **une seule page** et ne distingue pas fermé d'inexistant.
* **`supports_reviewable_diff` :** Attribut de classe, `False` sur Azure DevOps, vérifié **avant tout appel réseau**.
* **En-têtes GitLab Synthétisés :** `changes[].diff` est un **hunk isolé** ; `old_path`/`new_path` assemblent désormais les en-têtes `diff --git a/… / --- / +++`, en honorant `/dev/null`.
* **L'`overflow` de GitLab Lève :** Un MR avec un diff tronqué était révisé à moitié ; il lève désormais `ScmProviderError`.
* **`request_pull_request_reviewers` renvoie `list[str]` (rupture de contrat) :** Il retourne les logins **effectivement attachés**, lus dans le corps du `201`. Les sous-classes tierces doivent être mises à jour.
* **Deux Helpers en Lecture Seule de GitHub :** `get_commit_author_login` (associe un SHA au compte lié à l'e-mail de l'auteur) et `get_user_login` (valide/canonicalise un handle, rejette ce qui ne peut pas être un login sans dépenser de requête).
* **Fail-Fast par Forge :** Azure exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` ; Bitbucket exige `GITPR_SCM_USERNAME` ; `create_issue` sur Azure lève `ScmNotSupportedError`.
* **Publication de Release :** `provider.create_release()` utilisé par `gitpr release --publish`.
* **Artefacts :** Glossaire + ADR-001/ADR-005 ; la famille `docs/scm-multiforge.*.md` en 5 langues ; 9 fichiers de test, **284 scénarios**.

### **22. Sous-commande `gitpr release` — Changelog / Release Notes**

* **Flux :** `git log` entre `--since` et `HEAD` → classification par Conventional Commits → bump sémantique suggéré (`--version <x.y.z>` le remplace) → assemblage du changelog → résumé exécutif IA optionnel → *insertion en tête* de `CHANGELOG.md`.
* **Classifier (`src/commit_classifier.py`)** et **Builder avec Sections Traduisibles (`src/changelog_builder.py`)** — sections rendues via `__()` au runtime.
* **Bump Sémantique (`src/version_bump.py`)** et **Publication** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 options dans la sous-commande.**
* **Point de release en attente 🆕 :** l'entrée `[1.3.0] - 2026-09-21` **existe** dans `CHANGELOG.md`, mais elle a été écrite par le commit de l'analyse de secrets et ne couvre **que** celle-ci — `demo`, `badge`, `split`, SAST, `tests` et `explain` ne sont **pas** dans le changelog, et l'entrée est datée du 21/09 alors que `explain` est du 23/09. Lancer `gitpr release` est l'étape manquante.

### **23. Sous-commande `gitpr config` — TUI de Configuration**

* **Écran Maître-Détail (`src/ui/config_app.py`) :** Catégories à gauche, champs à droite, édités en ligne. En-tête avec recherche (`/`) et compteur de modifications en attente ; pied de page `F1 Help · F2 Save · ^R Restore · / Search · Esc`.
* **Schéma Déclaratif (`src/config_schema.py`) — l'unique source de vérité :** 🆕 **14 catégories** (+1 : **Split**) et **71 `ConfigField`** (+10), dont **9 avancés**. Chaque champ déclare sa catégorie, son type de widget, ses `show_if`, ses validateurs, ses marqueurs de version et ses actions de téléchargement.
* **Catégorie `split` 🆕 :** `GITPR_SPLIT_MAX_GROUPS`, `GITPR_SPLIT_MAX_HUNKS` et `GITPR_SPLIT_REQUIRE_CONFIRMATION` avec un parser d'entier positif qui retombe sur le défaut en cas de valeur non analysable — **zéro ou négatif ne désactive pas le plafond en silence**.
* **Champs en Lecture Seule 🆕 :** `GITPR_BADGE`, `GITPR_EXPLAIN_BY_DEFAULT` et les trois `GITPR_SAST_*_ENABLED` apparaissent dans le schéma (et donc à l'écran) mais ne sont **pas** seedés dans le `.env`.
* **Filtrage par Contexte (`show_if`) :** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` selon `DEFAULT_AI_PROVIDER` ; `GITPR_SCM_USERNAME` sous `[Bitbucket]` ; `GITPR_SCM_ORGANIZATION`/`PROJECT` sous `[Azure DevOps]`. Changer le `Select` refiltre le panneau immédiatement.
* **Recherche Globale (`/`) :** Correspond à la clé ou au libellé dans toutes les catégories et **ignore le filtre de visibilité**.
* **Validation en Deux Couches :** **Hors ligne** bloque `F2` avec une erreur en ligne ; **en ligne** (uniquement les identifiants modifiés dans la session) s'exécute dans un worker avec un timeout de 10s et ne bloque que sur `401`/`403`.
* **Restaurer (`Ctrl+R`) :** Supprime la ligne du `.env` au lieu de réécrire le défaut ; `Esc` avec des modifications en attente demande confirmation.
* **Section Skills — la seule au périmètre du projet :** Édite les `.gitpr/skill/*.md` du projet résolus depuis le répertoire appelant, en écrivant de manière atomique et en préservant CRLF/LF. **Dette connue 🆕 :** `SKILL_LABELS` n'a reçu ni `tests` ni `explain` — sans libellé, les deux types s'affichent **vides** dans la barre latérale (voir §33/§34).
* **Téléchargements Forcés :** Boutons qui forcent le re-téléchargement des smart-excludes, des traductions, des presets et des thinking words via `force=`.
* **Module Léger de Liens (`src/doc_links.py`) :** `doc_url()` déplacé hors de `core.py` pour que l'UI puisse obtenir le lien sans importer les SDK d'IA.
* **Tests :** 5 fichiers — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Dette Connue :** `gitpr -h config` ouvre la TUI et ignore `-h` — la barrière `if ctx.invoked_subcommand is not None: return` s'exécute avant le bloc `help_flag`.

### **24. Journal Général d'Utilisation (`src/usage_log.py`)**

* **Une Ligne par Commande :** Écrit dans `~/.gitpr/logs/<uuid5>.log`, **un fichier par jour**, avec la commande, les arguments, le dépôt, l'utilisateur et l'horodatage.
* **Nom Dérivé de la Date :** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` plutôt qu'aléatoire — deux processus concurrents ne peuvent pas être en désaccord sur le fichier du jour.
* **Écriture Synchrone (décision explicite) :** Contrairement à `log_local_metric`, qui utilise un thread daemon et perd l'écriture si le processus se termine avant.
* **N'Affiche Jamais :** Le serveur MCP réserve stdout au JSON-RPC ; le module ne lève jamais d'exception non plus.
* **Un Seul Spawn Git :** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms au lieu de ~150 ms sous Windows, à *chaque* exécution.
* **Contrôle :** `GITPR_SHOW_LOGS` (défaut `"true"`) ; désactivée dans `tests/conftest.py`.
* **Artefacts :** `docs/usage-log.*.md` en 5 langues ; `tests/test_usage_log.py` (27 scénarios).

### **25. Sous-commande `gitpr fix` — Constats de Review en Patches Révisables (`src/fix/`)**

* **Le Pipeline Réel :** dernier review du cache (`resolve_last_review`) → **un** appel d'IA → extraction et validation du diff unifié → `git apply --check` → classification déterministe → dry run ou écriture → historique.
* **Paquet de 8 Fichiers :** `patch_provenance.py`, `patch_extractor.py` (partagé avec le chat), `patch_safety_classifier.py` (pur, sans I/O), `patch_applier.py`, `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py` (docstring seulement).
* **Classification Déterministe :** Refuse un patch qui traverse plus d'un fichier ou d'un hunk, qui touche un chemin sensible, qui dépasse le budget de lignes, qui **efface une ligne qui ressemble à un appel**, qui arrive marqué à faible confiance ou qui échoue au `git apply --check`. Le `--force` ne contourne **jamais** l'applicabilité, seulement la classification.
* **Écriture Opt-In :** Le dry run est le défaut ; toute mutation exige `--apply` (ou une phrase saisie avec `--force`).
* **`patch_applier.py` Déménage 🆕 :** L'enveloppe de `git apply` a été promue dans `src/infrastructure/git/patch_applier.py` (partagée avec `split`) et `src/fix/patch_applier.py` est devenu un **shim de ré-export** — aucun import existant n'a cassé.
* **Tests :** 10 fichiers dans `tests/fix/` (**204 scénarios**) avec des fixtures de dépôt git réel.

### **26. Sous-commande `gitpr review-pr` — Review de PR Distant (`src/review/`)**

* **Ce Que C'est :** De l'orchestration, **pas** un second moteur de review — le moteur existant, le linter et le renderer alimentés avec un diff venu d'ailleurs. Un `.txt` de review distant et un de review local du même diff ne diffèrent **que par le nom du fichier**.
* **Paquet de 5 Fichiers :** `diff_source.py` (provenance pure), `diff_normalizer.py` (normalisation, validation et smart-excludes **en Python**), `render.py` (extrait de `main.py`, désormais partagé), `remote_pr.py` (le cas d'usage), `__init__.py`.
* **Lecture Seule par Défaut :** `--post-comment` est le **seul** chemin qui écrit sur la forge. L'outil MCP `review_remote_pr` ne reçoit même pas l'argument.
* **Interaction avec le `fix` :** Comme un review distant ne correspond à aucun arbre local, le diff révisé est conservé dans le cache (`reviewed_diff`) et le `fix` le préfère.
* **Artefacts :** `docs/review-pr.md` + `.pt_br.md`, `docs/code-review-ia.*.md` (5), ADR-005 et `glossary-review-pr.md`.
* **Tests :** 4 fichiers dans `tests/review/` (**97 scénarios**).

### **27. Résolution de l'Identité du Reviewer (`src/reviewer_resolution.py`)**

* **Le Bug (deux défaillances silencieuses enchaînées) :** (1) **Prefill vide** — `handles` seulement depuis `email_to_handle()`, qui ne voit que les e-mails `users.noreply.github.com` ou ceux avec une adresse publique ; (2) **Attach non vérifié** — des valeurs passées *verbatim*, GitHub répond **201 sans attacher personne** et `_request()` retourne sans lever : succès apparent, reviewer absent, aucun avertissement du tout.
* **Module Nouveau :** Un plan, sans I/O propre, **qui ne lève jamais**. `resolve_candidates()` (avant la TUI) et `resolve_typed_reviewers()` (à l'attach) ; `match_candidate()` fait correspondre de façon exacte et normalisée par login, nom ou e-mail.
* **Échelle de Résolution :** handle connu (sans requête) → correspondance exacte avec une personne suggérée → lookup par e-mail → validation du login sur la forge.
* **Ce qui ne se résout pas n'est jamais envoyé :** Cela sort dans `ResolutionOutcome.dropped` comme `(valeur, motif_i18n)` et est écarté avec un avertissement visible.
* **Provider Duck-Typed :** L'accès se fait par `getattr`, donc les fakes et les forges sans les nouvelles méthodes continuent de fonctionner.
* **Tests :** `tests/test_reviewer_resolution.py` (18) + des extensions dans `test_github_provider.py` (68), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) et `test_main_suggest_reviewers.py` (8).

### **28. Sous-commande `gitpr demo` — Visite Guidée sur des Exemples Enregistrés (`src/demo/`) 🆕**

* **Ce Que C'est :** Une visite interactive qui montre les trois sorties principales de GitPR — message de commit, review et description de PR — **sans clé API, sans dépôt git et sans réseau**. La question « que fait cet outil ? » n'a qu'une seule fenêtre pour être posée : la première utilisation, avant que l'utilisateur n'ait configuré un fournisseur.
* **Vrai Pipeline, Fausse Réponse :** `FakeAIProvider` reflète `src.ai_providers.call_ai_model` **argument par argument** ; `demo_pipeline()` patche `src.core` et `src.metrics` pour que le pipeline de production (assemblage du prompt, découpage du diff, parsing de la réponse) tourne **intact** et que seule la source de la réponse soit échangée. C'est ce qui garantit que la visite montre ce que l'outil produit réellement, et non une maquette.
* **L'Isolation est une Exigence, Pas un Détail :** La visite **ne peut pas** toucher le `~/.gitpr` de l'utilisateur (cache, métriques, logs) ni le réseau — garanti par `tests/demo/conftest.py` (interdiction réseau autouse, avec une exception loopback pour l'event loop proactor de Windows). Une régression ici empoisonnerait silencieusement un vrai cache avec du contenu de démonstration.
* **Paquet :** `demo_runner.py` (machine à états `DemoState` sans UI + front-end en texte brut), `fake_ai_provider.py`, `scenarios/` (deux exemples : un bug de mise à jour de profil en Laravel et un IDOR cross-tenant en Express, chacun localisé dans les 5 langues avec un fallback vers l'anglais). Les scénarios sont des **modules Python, pas du JSON**, pour qu'ils voyagent dans le wheel.
* **TUI :** `src/ui/demo/` (écran unique, suivant le patron des apps existantes) avec un modal d'aide ; le diff est encadré comme ```` ```diff ```` et le message de commit rendu en texte brut.
* **CLI :** `gitpr demo` avec `--scenario`, `--lang` et `--no-tui`.
* **i18n :** +32 clés et un bump vers **v0.0.29**.
* **Tests :** 6 fichiers dans `tests/demo/` (**155 scénarios**) — machine à états, intégrité des scénarios (arithmétique des hunks), navigation dans la TUI, mode texte, contrat du fake provider et les gardes d'isolation.
* **Artefacts :** `docs/demo.*.md` en 5 langues, `README.*` (5) et les métriques des exemples de la visite.

### **29. Badge GitPR et la Commande `gitpr badge` (`src/branding/`) 🆕**

* **Ce Que C'est :** Un badge public qui atteste qu'une pull request est passée par une vérification de qualité locale, et une commande qui affiche le snippet d'adoption pour le README du projet.
* **La Motivation est la Confiance au Point de Publication :** Un corps de PR écrit par l'IA est indistinguable d'un corps écrit à la main, et le résultat du linter mourait dans un terminal qui avait défilé hors de l'écran. Le badge transforme ce signal privé en une déclaration visible et vérifiable dans le PR publié lui-même.
* **URL Statique à Dessein :** C'est un Markdown shields.io que GitPR ne récupère **jamais** — publier une pull request ne peut pas en venir à dépendre de la disponibilité d'un tiers.
* **Mesure Honnête (`badge_data.py`) :** `collect_linter_counts()` retourne `None` quand aucune règle de linter n'est configurée (lancer `gitpr --skill`), parce qu'une liste vide veut dire « rien n'a été vérifié », et non « rien n'a été trouvé » — un badge vert sur un diff non vérifié serait une affirmation que GitPR ne peut pas soutenir. Le pont externe est ignoré : il inspecte l'arbre de travail, pas la révision en cours de review.
* **Le Badge Ne Bloque Jamais une Publication :** Chaque chemin d'échec de la collecte est capturé et dégrade vers « pas de badge ».
* **Point d'Injection Unique :** `attach_pr_badge()` dans `src/main.py`, juste après que `pr_data` est complet — les deux publishers lisent au même endroit. Ajout idempotent par marqueur.
* **Commande `gitpr badge` :** `--readme` (forme nue, compatible pipe) et `--style`. **Affiche seulement** — le README n'est jamais modifié.
* **Divulgation :** Le wizard d'init annonce le badge automatique sur le chemin du PR et `--no-edit` imprime une ligne nommant ce qui est entré dans le corps sans que l'utilisateur l'ait vu — une fonctionnalité qui n'apparaît qu'après avoir configuré une variable est une fonctionnalité que personne ne découvre.
* **Configuration :** `GITPR_BADGE` (défaut `true`) — un opt-out en **lecture seule**, jamais écrit automatiquement.
* **i18n :** +10 clés et un bump vers **v0.0.30**.
* **Tests :** 7 fichiers dans `tests/badge/` (**83 scénarios**) — builder, collecte de données, CLI, opt-out, chemins de publication et une garde hors ligne (interdiction réseau autouse, sauf loopback) ; les suites du demo et du wizard ont été étendues.
* **Demo :** L'étape PR de la visite montre désormais le badge qu'une publication réelle attacherait, compté à partir du bloc linter **enregistré dans le scénario lui-même** — aucune règle n'est lue et aucun diff n'est linté.
* **Artefacts :** `docs/badge.*.md` en 5 langues et `docs/survey/20260919_gitpr_badge_surveyfacts.md`.

### **30. Sous-commande `gitpr split` — Commits Atomiques par Hunk (`src/split/`) 🆕**

* **Ce Que C'est :** Un arbre de travail à plusieurs préoccupations n'a plus à devenir un blob de commit. `split` lit le diff non commité, demande à l'IA de partitionner les hunks par **intention logique** et propose un commit atomique par préoccupation — chacun avec un message généré à partir du patch reconstruit **depuis ce groupe isolé**. L'arbre n'est jamais réécrit : les fichiers terminent identiques au byte près à leur état initial ; seul l'historique change.
* **Paquet de 6 Fichiers, en Couches du Bas vers le Haut :**
  * `split_plan.py` — contrat de données : `SplitError`, `Hunk`, `OpaqueSection`, `ChangeUnit`, `HunkGroup`, `SplitPlan`.
  * `hunk_parser.py` — `parse_units()` / `build_patch()` : texte ↔ modèle, **pur**, n'exécute jamais git.
  * `hunk_grouper.py` — rendu du prompt, budget et tronquage, **un** appel d'IA, validation de la réponse.
  * `generate_split_plan.py` — diff → unités → groupes → pré-validation des conflits → messages. **Lecture seule.**
  * `apply_split_plan.py` — **le seul module de `split` qui mute** : staging sélectif et un commit par groupe.
  * `__init__.py` — marqueur de paquet.
* **La Garantie d'Arbre Intact est Structurelle :** `selective_stager.py` pré-valide **chaque** groupe contre un index temporaire à HEAD et utilise un `GIT_INDEX_FILE` jetable — un plan n'est jamais appliqué sans vérification, et l'index de l'utilisateur n'est jamais lu ni déplacé pendant la planification.
* **Destructif pour l'Index, Pas pour l'Arbre :** `--apply` réinitialise l'index sur HEAD avant le staging, donc tout ce que l'utilisateur avait déjà stagé est déstagé. Le contenu des fichiers est préservé, mais le staging doit être refait — documenté explicitement.
* **Configuration :** `GITPR_SPLIT_MAX_GROUPS` (défaut 5), `GITPR_SPLIT_MAX_HUNKS` (défaut 50) et `GITPR_SPLIT_REQUIRE_CONFIRMATION` (défaut `true`). Les valeurs zéro ou négatives sont **ignorées au profit du défaut**, au lieu de désactiver silencieusement un plafond.
* **i18n :** +50 clés et un bump vers **v0.0.31**.
* **Tests :** 6 fichiers dans `tests/split/` (**101 scénarios**) tournant contre de vrais dépôts jetables, avec le réseau bloqué.
* **Artefacts :** `docs/split-command.md` + `.pt_br.md` (les 3 langues restantes sont en attente), ADR-006 (`split-apply-safety`), `glossary-gitpr-split.md`, spec/plan dans `docs/plans/` et une survey.

### **31. Analyse de Secrets Intégrée (`src/security_ruleset.py`) 🆕**

* **Ce Que C'est :** Sept règles qui tournent à **chaque** invocation du linter, fusionnées à la fin de `load_linter_rules()` après les règles du projet et des plugins, qui restent intactes.
* **Les Sept Règles :** cinq `error` bloquantes — ID de clé d'accès AWS, jeton GitHub, jeton Slack, clé API Google et bloc de clé privée (`-----BEGIN … PRIVATE KEY-----`) — et deux `warning` qui rapportent sans bloquer : une URL de connexion à une base de données avec identifiants et une affectation d'identifiant générique (`password = "…"`), cette dernière derrière un filtre de placeholders (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Pourquoi dans le Paquet et Non dans le Template :** Le catalogue local était entièrement géré par l'utilisateur — il pouvait être écrasé par le téléchargement, réécrit par le wizard (en perdant les commentaires) ou étendu seulement par des plugins locaux à la machine. Une barrière de secrets doit se comporter **de la même façon** sur chaque machine et dans chaque CI, donc les règles vivent dans le paquet et ne peuvent pas être remplacées par `--skill` ni réécrites par le wizard.
* **Lacunes de Couverture Connues (v1), déclarées :** l'affectation **sans guillemets** n'est pas détectée (`API_KEY=abc123` — le format `.env`, qui est exactement d'où les secrets fuient) ; les préfixes `ASIA…`, `github_pat_…` et `xoxc-`/`xoxd-` manquent ; et la règle générique n'a pas de frontière gauche sur le nom de la clé, donc `mytoken` correspond autant que `token`.
* **Configuration :** `GITPR_LINTER_SECURITY` (défaut `true` ; opt-out fail-open — seuls `false`/`0`/`no`/`off`/`n` la désactivent) et `GITPR_LINTER_SECURITY_DISABLED_RULES` (séparées par `;`).
* **Tests :** `tests/test_security_ruleset.py` (**61 scénarios**) — compilation des regex, détection positive/négative, filtre de placeholders, routage par niveau, application du wildcard, fusion et complétude des traductions.
* **Artefacts :** ADR-007 (`secret-ruleset-location-and-severity`), `glossary-gitpr-secret-scanning.md`, spec/plan/survey et 3 rapports de tâche.

### **32. Ponts SAST — Semgrep, Gitleaks et Bandit (`src/infrastructure/linter/external/`) 🆕**

* **Ce Que C'est :** Une couche **opt-in** qui branche des scanners de sécurité tiers sur le pipeline de linter existant, élevant le plancher de chaque review sans coût pour qui n'en a pas besoin.
* **Périmètre Borné :** Ils tournent **uniquement** lorsqu'ils sont activés et **uniquement** sur les fichiers touchés par le diff.
* **Contrat Commun :** `ExternalLinterBridge` (ABC) avec exécution `subprocess` blindée (`shell=False`, timeout strict, `stdin=DEVNULL`, UTF-8 avec `errors='replace'`) et des ponts concrets pour Semgrep, Gitleaks et Bandit. Le modèle `NormalizedFinding` / `ExternalLinterResult` fait produire à chaque outil des constats à la même sévérité (`error`/`warning`/`info`) et de la même forme.
* **La Déduplication est le Point :** `deduplicate_secret_findings()` fusionne les constats de Gitleaks et du ruleset de regex au même fichier et à la même ligne en **une seule** entrée confirmée `[Gitleaks + Regex]` — un secret vu par les deux apparaît une fois, avec confirmation multi-source, au lieu de deux.
* **Secrets Masqués :** `mask_secret_value()` (`AKIA****`) garantit que la valeur n'atteint jamais le constat, le log ou la télémétrie.
* **Résolution en Trois Couches :** `load_sast_config()` lit défauts → `.gitpr.linter.yml` (bloc `sast`, avec fallback vers `linter.external`) → variables `GITPR_SAST_*`. **Tout est à `false` par défaut** — opt-in strict, donc personne ne voit de changement tant qu'il ne le demande pas.
* **Dégradation Gracieuse :** Un outil activé mais absent du `PATH` émet `⚠️ SAST tool '{tool}' is enabled in config but was not found in PATH.` et l'exécution continue.
* **Normalisation des Chemins :** Tous les ponts normalisent les antislashs en slashs et vers une forme relative au dépôt, pour que les clés de fichiers modifiés du diff correspondent sous Windows **et** sous Unix.
* **Configuration :** `GITPR_SAST_SEMGREP_ENABLED`, `GITPR_SAST_GITLEAKS_ENABLED`, `GITPR_SAST_BANDIT_ENABLED` + `GITPR_SAST_<TOOL>_TIMEOUT` (60s / 30s / 45s).
* **Dépendances :** `semgrep`, `gitleaks` et `bandit` ne sont **pas** des paquets Python du projet — ce sont des binaires externes qui doivent être dans le `PATH`.
* **i18n :** +7 clés (le dernier ajout de clés de cette fenêtre).
* **Tests :** 4 fichiers dans `tests/infrastructure/linter/external/` (**14 scénarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) — disponibilité, timeout de subprocess, binaire manquant, parsing JSON, mapping de sévérité, masquage de secrets et saut du non-Python.

### **33. Sous-commande `gitpr tests generate` — Génération de Suite par IA (`src/domain/tests_generation/`, `src/application/`) 🆕**

* **Ce Que C'est :** Génère des fichiers de test complets et exécutables à partir du diff courant, d'un fichier spécifique ou d'un constat de review, **en respectant la convention du dépôt** (Pest, PHPUnit, Jest, Vitest, Pytest) au lieu d'imposer un style.
* **Couche Domaine :** `TestFramework` (enum) et les dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest` comme contrat partagé ; `detect_test_framework()` détecte à partir des fichiers de configuration, des manifestes de dépendances et du contenu du répertoire de tests, avec un override explicite et un avertissement quand la détection échoue ; `build_test_scaffold()` calcule le chemin de destination conventionnel par framework (la séparation `Feature`/`Unit` de Laravel, le `tests/**/test_*.py` de Pytest, les conventions `.test`/`.spec` de JS/TS).
* **Couche Application (`generate_test_file.py`) :** Orchestre la détection du framework, la résolution du scaffold, la construction du prompt, l'invocation de l'IA, le parsing JSON et l'écriture optionnelle. `validate_test_syntax()` exécute la chaîne d'outils locale (`php -l`, `node --check`, `python -m py_compile`) lorsqu'elle est disponible — un échec de validation est un **avertissement**, pas une erreur.
* **Dégradation Gracieuse :** Sans clé API, ou avec une réponse non-JSON du modèle, le résultat est marqué à faible confiance au lieu de lever (il dépouille les fences markdown et continue).
* **Présentation :** Le groupe `tests` avec la sous-commande `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`) ; le dry run est le défaut et écraser un test existant demande confirmation avec **Non** présélectionné. Le chat délègue `/tests` au **même** cas d'usage.
* **Tests :** `tests/domain/tests_generation/` (18), `tests/application/use_cases/test_generate_test_file.py` (4) et `tests/test_tests_command.py` (2).
* **Dette 🆕 :** Aucune nouvelle clé i18n n'a été ajoutée — la fonctionnalité utilise **17 clés `__()`** qui n'existent dans aucun des 6 dictionnaires (une partie des 40 manquantes), et `SKILL_LABELS`/`mcp_server.SKILL_FILES` n'ont pas reçu le type `tests`. Il n'y a pas de `docs/tests*.md`.

### **34. Sous-commande `gitpr explain` et le Flag `--explain` — Guide du Reviewer (`src/domain/pr/`) 🆕**

* **Ce Que C'est :** Un guide centré sur celui qui va **réviser** — ce qui change, pourquoi cela change, où se concentrer et quel est le risque de régression — pour que personne n'ait à reconstruire l'intention à partir d'un diff brut.
* **Couche Domaine (`explain_section_builder.py`) :** `PrExplanation` / `ReviewerFocusPoint`, le renderer `build_explain_markdown()` et `parse_explain_payload()`, qui tolère une sortie non-JSON ou malformée et **détecte les placeholders** `[FILL]`/`[TODO]` pour signaler des preuves insuffisantes au lieu de présenter un guide creux comme complet.
* **Couche Application (`generate_pr_explanation.py`) :** Résolution du fournisseur, validation de la clé, chargement du contexte de skill (`explain`), construction du prompt, invocation et parsing vers le modèle de domaine.
* **Deux Portes d'Entrée :** `gitpr explain` (sous-commande, avec `--provider`, détection d'un diff manquant avant toute IA et sortie colorisée) et le flag `--explain` sur la CLI racine, qui ajoute le guide au corps de la description de PR **et** au payload JSON émis, dans un unique `pr_desc_body` réutilisé.
* **Configuration :** `GITPR_EXPLAIN_BY_DEFAULT` (défaut `false`) — quand il est à `true`, la section est ajoutée à **chaque** description générée, ce qui ajoute un appel d'IA et augmente le coût/les tokens par PR.
* **Skill :** `.gitpr.explain.md` enregistré dans `SKILL_FILES_BY_TYPE`, avec des templates en 5 langues.
* **Tests :** `tests/domain/pr/test_explain_section_builder.py` (4), `tests/application/use_cases/test_generate_pr_explanation.py` (2) et `tests/test_explain_command.py` (2).
* **Dette 🆕 :** Le même patron que `tests` — des clés `__()` sans traduction (une partie des 40), absentes de `SKILL_LABELS` et de `mcp_server.SKILL_FILES`, et sans sujet dans `docs/`.

### **35. Suite Déterministe et Première CI (`.github/workflows/tests.yml`) 🆕**

* **Ce Que C'est :** Le premier workflow qui exécute la suite, sur **Python 3.10** (le plancher déclaré dans `pyproject.toml`, jamais exercé) et **3.13** (la version de développement du `Pipfile`), avec `fail-fast: false`.
* **Ce Que la CI a Rend Visible :** **22 tests** échouaient sur une machine pt-BR parce qu'ils affirment le littéral anglais alors que `__()` rend du portugais — la suite n'était verte qu'avec `GITPR_LANG=en_us` sur la ligne de commande. Ce fut la motivation directe du durcissement de `conftest.py`.
* **`tests/conftest.py` Hermétique 🆕 :** Il fixe `GITPR_LANG=en_us` (empêche la suite d'écrire dans le vrai profil et de rendre des traductions), `GITPR_LINTER_SECURITY=false` (trois suites affirment la liste de règles que le vrai `load_linter_rules()` retourne) et `LANG_VERSION` à la version du code (empêche le re-téléchargement de `~/.gitpr/langs/*.json` à chaque bump).
* **L'Ordre Compte :** `src.updater` est importé **avant** que les variables de langue ne soient définies, parce qu'`i18n` photographie `os.environ` dans `AMBIENT_ENV_KEYS` à l'import — une vraie session reçoit `LANG_VERSION` depuis le fichier, pas depuis le shell.
* **Stabilité des Tests de Worker :** `test_config_app.py` et `test_metrics.py` attendent désormais `workers.wait_for_complete()` avant d'affirmer.
* **Dépendance du Workflow :** Le runner propre n'a pas de `~/.gitpr`, et `tests/demo/test_demo_isolation.py` affirme que le profil existe (il le photographie pour prouver que la visite n'y écrit pas) — le job crée explicitement le répertoire et le `.env` vide.

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame par plage de lignes dans un fichier |
| `tests/test_blame_metrics.py` | 7 | Métriques de blame : profondeur, commits, durée |
| `tests/test_changelog_builder.py` | 15 | Builder de changelog : sections, titres traduisibles, contributeurs |
| `tests/test_chat_backend.py` | 19 | Mémoire du chat, persistance, commandes slash |
| `tests/test_commit_classifier.py` | 23 | Classification Conventional Commits (types, parser tolérant) |
| `tests/test_config_app.py` | 78 | TUI de configuration : montage, changement de catégorie, suivi des modifications, F2 bloqué, Ctrl+R, recherche, secrets |
| `tests/test_config_cli.py` | 9 | Enregistrement de la sous-commande `config`, `-h`, import paresseux, stdout propre |
| `tests/test_config_schema.py` | 42 | Couverture de `DEFAULT_CONFIG`, absence de doublons, catégories/kinds, `advanced` uniquement dans Avancé, **section skills** ⚠️ |
| `tests/test_config_store.py` | 22 | Round-trip sur un `.env` temporaire, commentaires et ordre préservés, `remove_config_value()` idempotent |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuration des reviewers suggérés (clés et défauts) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` avec un SDK mocké (401 vs. réseau vs. ollama) |
| `tests/test_core.py` | 49 | Flux principaux, git diff, génération de PR, timing, staging, co-paternité, langue des hooks |
| `tests/test_diff_parser.py` | 26 | Parser de diff par lignes/hunks + `summarize_patch()` et `split_patch_sections()` |
| `tests/test_explain_command.py` | 2 🆕 | CLI `explain` : succès, clé manquante, diff vide |
| `tests/test_external_linters.py` | 33 | Pont Checkstyle : parser XML, subprocess, croisement de diff, rapport |
| `tests/test_i18n.py` | 20 | Parité entre langues, clés manquantes/orphelines, identité — **assertion des 40 clés manquantes** ⚠️ |
| `tests/test_install_wizard.py` | 3 | Assistant interactif d'installation |
| `tests/test_issue_engine.py` | 4 | Brouillon d'issue structuré |
| `tests/test_linter_metrics.py` | 4 | Métriques de linter : erreurs, warnings, durée |
| `tests/test_linter_presets.py` | 5 | Presets de linter : résolution et re-téléchargement forcé |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` dans la CLI et l'aide contextuelle |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompt MCP et fallback de langue |
| `tests/test_mcp_server.py` | 104 | Outils MCP (14), ressources (18), annotations, patching, CLI direct, offload — **accord du registre de skills** ⚠️ |
| `tests/test_mcp_server_e2e.py` | 6 | Vrai serveur MCP via subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Collecte, export local, périmètre de dépôt, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Timeouts réseau/IA — **alignés sur le vrai défaut de 180s** ✅ |
| `tests/test_plugins.py` | 17 | Découverte de plugins, fusion de règles de linter, prompts MCP |
| `tests/test_pr_publish_app.py` | 45 | TUI du PR Publisher : écrans, flux, reviewers suggérés, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal d'erreur du linter : abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save et payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release : options, aide avec epilog documenté |
| `tests/test_release_engine.py` | 27 | Moteur de release : plage de commits, CHANGELOG, publication |
| `tests/test_reviewer_resolution.py` | 18 | Échelle de résolution, rejet d'un nom partiel, dédup, tolérance à l'échec |
| `tests/test_reviewer_suggestion.py` | 18 | Logique de suggestion de reviewers (classement, exclusion, top-N, `last_commit_hash`) |
| `tests/test_security_ruleset.py` | 61 🆕 | Matrice du ruleset intégré : regex, placeholders, niveau, wildcard, fusion, traductions |
| `tests/test_skill_command.py` | 10 | Téléchargement et validation des templates de skill |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` avec `quiet=True`, fallbacks, registre de skills |
| `tests/test_smart_excludes.py` | 15 | Filtre pathspec intelligent et re-téléchargement forcé |
| `tests/test_suggest_reviewers.py` | 17 | Reviewers suggérés dans le flux PR (intégration, hint `no_login`) |
| `tests/test_tests_command.py` | 2 🆕 | CLI du groupe `tests` et de `generate` |
| `tests/test_thinking_words.py` | 5 | Chargement, parsing avec le séparateur `;` et rechargement forcé |
| `tests/test_updater.py` | 23 | Barrière PyPI : parsing de version, cache quotidien, fetch, décisions de la barrière, câblage CLI |
| `tests/test_usage_log.py` | 27 | Journal d'utilisation : nom dérivé de la date, écriture synchrone, silence, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump sémantique : major/minor/patch, cibles et validation |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 2 🆕 | Cas d'usage explain : provider, clé, parsing |
| `tests/application/use_cases/test_generate_test_file.py` | 4 🆕 | Cas d'usage de génération de tests : dry-run/apply, validation de syntaxe |
| `tests/badge/test_badge_append.py` | 9 🆕 | Ajout idempotent du badge au corps du PR |
| `tests/badge/test_badge_builder.py` | 19 🆕 | Composition du Markdown shields.io, échappement, règle de couleur, style |
| `tests/badge/test_badge_cli.py` | 15 🆕 | Commande `gitpr badge` : snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 🆕 | Comptage des alertes ; `None` sans règles configurées |
| `tests/badge/test_badge_offline.py` | 6 🆕 | Garde hors ligne (réseau interdit, sauf loopback) |
| `tests/badge/test_badge_optout.py` | 17 🆕 | `GITPR_BADGE=false` sur chaque chemin de publication |
| `tests/badge/test_badge_paths.py` | 10 🆕 | Point d'injection unique dans les deux publishers (TUI et `--no-edit`) |
| `tests/demo/test_demo_app.py` | 22 🆕 | TUI de la visite : navigation, écrans, modal d'aide |
| `tests/demo/test_demo_isolation.py` | 14 🆕 | La visite ne touche ni `~/.gitpr` ni le réseau |
| `tests/demo/test_demo_runner.py` | 33 🆕 | Machine à états `DemoState` : avant, arrière, terminé, mode texte |
| `tests/demo/test_demo_scenarios.py` | 47 🆕 | Intégrité des scénarios enregistrés (arithmétique des hunks) dans les 5 langues |
| `tests/demo/test_demo_text_mode.py` | 18 🆕 | Sortie en texte brut (`--no-tui`) pour la CI et les enregistrements |
| `tests/demo/test_fake_ai_provider.py` | 21 🆕 | Contrat du fake provider : reflète `call_ai_model` argument par argument |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 🆕 | Formatage uniforme et déduplication `[Gitleaks + Regex]` |
| `tests/domain/pr/test_explain_section_builder.py` | 4 🆕 | Rendu Markdown, parsing tolérant, détection de `[FILL]` |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 🆕 | Détection par config, manifeste et répertoire ; override ; échec averti |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 🆕 | Chemin conventionnel par framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Cas d'usage complet : review → IA → valider → classifier → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extracteur partagé entre le chat et `fix` (comportement identique) |
| `tests/fix/test_fix_cli.py` | 31 | Routage de la sous-commande, options, dry-run par défaut, `--force` |
| `tests/fix/test_fix_history.py` | 21 | Registre `.gitpr/fix_history.json` : écriture atomique, lecture |
| `tests/fix/test_fix_settings.py` | 10 | Les cinq `GITPR_FIX_*` et les fallbacks de valeur invalide |
| `tests/fix/test_patch_applier.py` | 19 | Enveloppe de `git apply` : check/apply/reverse, branche, status |
| `tests/fix/test_patch_extractor.py` | 13 | Blocs encadrés → diff unifié validé |
| `tests/fix/test_patch_safety_classifier.py` | 22 | Matrice safe/review_required/experimental et codes de motif |
| `tests/fix/test_resolve_last_review.py` | 13 | Sélection du dernier review, exclusion des reviews par fichier |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback` : reverse et les trois refus |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 🆕 | Pont Bandit : disponibilité, parsing, mapping |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 🆕 | ABC : subprocess blindé, timeout, binaire manquant |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 🆕 | Pont Gitleaks : masquage des secrets, périmètre par fichier |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 🆕 | Pont Semgrep : parsing JSON, sévérité, saut du non-Python |
| `tests/review/test_diff_normalizer.py` | 20 | Newlines, validation de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource` : provenance, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orchestration : barrières, PR, diff, linter, commentaire optionnel |
| `tests/review/test_review_pr_cli.py` | 25 | CLI `review-pr` : options, rejets avant l'IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 43 | Provider Azure DevOps : org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 39 | Provider Bitbucket : Basic auth, workspace |
| `tests/scm/test_contract.py` | 38 | Contrat `ScmProvider` : signatures, dataclasses, erreurs |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` déprécié → délègue au provider |
| `tests/scm/test_github_provider.py` | 68 | Provider GitHub : REST, en-têtes, PRs, issues, releases, read-back des reviewers |
| `tests/scm/test_gitlab_provider.py` | 47 | Provider GitLab : API v4, namespace, en-têtes synthétisés et `overflow` |
| `tests/scm/test_init_wizard.py` | 10 | Wizard `--init` : détection de forge, validation, persistance |
| `tests/scm/test_release_publish.py` | 8 | Publication de release par forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 🆕 | Le seul module qui mute : staging sélectif, un commit par groupe |
| `tests/split/test_generate_split_plan.py` | 12 🆕 | Diff → unités → groupes → pré-validation des conflits → messages |
| `tests/split/test_hunk_grouper.py` | 18 🆕 | Prompt, budget/tronquage, appel d'IA, validation de la réponse |
| `tests/split/test_hunk_parser.py` | 18 🆕 | `parse_units()`/`build_patch()` : texte ↔ modèle, pur |
| `tests/split/test_selective_stager.py` | 11 🆕 | Staging d'un sous-ensemble, vérification contre HEAD, index propre |
| `tests/split/test_split_cli.py` | 31 🆕 | CLI split : options, dry run par défaut, `--apply` |
| `tests/sync_i18n.py` | — | Script de vérification de couverture i18n (scaffold ; jamais exécuté) |

**Total :** **1889 scénarios collectés dans 96 modules de test** (44 à la racine + 9 dans `tests/scm/` + 10 dans `tests/fix/` + **6 dans `tests/split/`** 🆕 + **6 dans `tests/demo/`** 🆕 + **7 dans `tests/badge/`** 🆕 + 4 dans `tests/review/` + **4 dans `tests/infrastructure/linter/external/`** 🆕 + **2 dans `tests/domain/tests_generation/`** 🆕 + **2 dans `tests/application/use_cases/`** 🆕 + **1 dans `tests/domain/pr/`** 🆕 + **1 dans `tests/domain/linter/`** 🆕 ; **+452** depuis le rapport précédent, avec **32 nouveaux fichiers**). Exécution complète sur cette machine avec `GITPR_LANG=en_us` : **1883 réussis / 4 échoués / 2 ignorés / 81 sous-tests** en ~373s.

**Notes de qualité pour cette version :**
- **✅ Les 3 échecs hérités ont été clos.** Les deux tests obsolètes de `test_net_timeouts.py` (qui affirmaient 600s contre un code qui livre 180s depuis le correctif `681a7fa`) ont été alignés — le point était ouvert depuis **trois rapports consécutifs**. Et l'échec sensible à la locale a été résolu en épinglant `GITPR_LANG=en_us` dans `conftest.py`, ce qui a aussi éliminé les **22 échecs de locale** que la CI a révélés sur une machine pt-BR.
- **⚠️ 4 nouveaux échecs, une seule cause racine :** les deux fonctionnalités les plus récentes (`tests` et `explain`) sont arrivées avec le **registre de skills à moitié fait**. Les voici :
  1. `test_config_schema.py::TestSkillsSection::test_the_labels_cover_the_registry_exactly` — `SKILL_LABELS` n'a ni `tests` ni `explain` ; sans libellé, les deux s'affichent **vides** dans la barre latérale de la TUI de configuration.
  2. `test_config_schema.py::TestSkillsSection::test_the_labels_follow_the_registry_order` — le même manque vu à travers l'ordre : `SKILL_TYPES` a 10 entrées, `SKILL_LABELS` en a 8.
  3. `test_i18n.py::TestNoMissingKeys::test_no_missing_keys` — **40 clés `__()`** utilisées dans le code n'existent dans **aucun** des 6 dictionnaires (toutes issues des commandes `tests` et `explain`). Comme l'anglais est le fallback, elles s'affichent comme la clé elle-même dans toutes les langues.
  4. `test_mcp_server.py::TestSkillRegistryAgreement::test_the_two_skill_registries_agree` — `mcp_server.SKILL_FILES` et `config.SKILL_FILES_BY_TYPE` ont cessé de concorder ; `skill://explain` et `skill://tests` ne sont pas exposées par le MCP.
- **La cause est unique et peu coûteuse à clore :** enregistrer les deux types dans `SKILL_LABELS`, dans `mcp_server.SKILL_FILES` et lancer `python tests/sync_i18n.py` pour les 40 clés. Le point pertinent est que la suite l'a **détecté** — les trois assertions existent exactement pour cela, et la CI les exécute sur deux versions de Python.
- **Croissance de 452 scénarios** avec la base d'échecs hérités ramenée à zéro — le signal de cette fenêtre : la suite a cessé de porter des échecs connus et a commencé à signaler la dette nouvelle dans le même commit où elle naît.
- `tests/conftest.py` est devenu **hermétique** : `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` et `LANG_VERSION` à la version du code — la suite n'écrit ni dans le journal d'utilisation, ni dans le vrai `.env`, ni dans `~/.gitpr/langs/`.
- **Fixtures git réelles :** `tests/fix/git_fixture.py` et `tests/split/git_fixture.py` construisent de vrais dépôts — `patch_applier` et `selective_stager` ne sont honnêtes que si le `git apply` est le vrai.
- **Garde réseau :** `tests/demo/conftest.py` interdit le réseau par autouse — une régression à cet endroit empoisonnerait un vrai cache avec du contenu de démonstration.

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n :** **1158 clés de traduction** dans les 6 dictionnaires, avec une **parité complète de l'ensemble des clés** entre eux (+110 depuis le rapport précédent). La chaîne mesurée commit par commit était 1048 → 1080 (`demo`, +32) → 1090 (`badge`, +10) → 1140 (`split`, +50) → 1151 (secrets, +11) → **1158** (SAST, +7). Les deux derniers commits (`tests`, `explain`) n'ont **pas** ajouté de clés — ils en utilisent 40 qui n'existent pas. ⚠️ Le code utilise **1198** clés.
* **`__lang_version__` est passé de v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32**, déclenchant le re-téléchargement OTA des traductions. L'analyse de secrets et le SAST ont ajouté des clés **sans** incrémenter le marqueur ; le bump vers v0.0.32 couvre les deux et se trouve **dans l'arbre de travail, non commité**.
* **Sources de traduction en phase :** une nouvelle clé doit exister dans le code (source), dans `langs/pt_br.json` (**liste maîtresse**), dans les dictionnaires FR/ES de `scripts/sync_all_langs.py` (deuxième source) et dans les valeurs curatées de `scripts/fix_mangled_i18n_keys.py` (troisième source, lue par `tests/test_i18n.py`) ; l'assertion `len(CLEAN_KEYS)` tient toujours à 49.
* **Nouveaux sujets 🆕 (3) :**
  - `docs/demo.md` — la visite guidée : ce qu'elle montre, le vrai pipeline avec une fausse réponse, les scénarios et la garantie d'isolation — **en 5 langues**
  - `docs/badge.md` — le badge de PR : ce qu'il indique, où il est attaché, quand il est omis et comment imprimer le statique pour le README — **en 5 langues**
  - `docs/split-command.md` — commits atomiques par hunk : le pipeline, la pré-validation, ce que `--apply` fait à l'index — **en EN + PT-BR** (les 3 langues restantes sont en attente)
* **Nouveau sous-répertoire 🆕 :** `docs/tutorial/` avec la famille `install-from-source.*` **en 5 langues** (installation depuis le code source).
* **Sujets mis à jour dans cette fenêtre :** `docs/linter-regras-customizadas.*` (5 — le champ `level`, le ruleset intégré et les deux échappatoires), `docs/git-hooks-locais.*` (5 — ce que le hook pre-commit bloque désormais et comment le contourner), `docs/auto-update.*` (5), `docs/ARCHITECTURE.md`, plus `README.md` et ses 4 traductions (index avec les familles `demo`, `badge`, `split-command`, `fix-command` et `review-pr`).
* **Documentation en 5 langues :** **44 sujets canoniques** dans `docs/` (+3) — **37 avec une couverture complète dans les 5 langues** (+2) et **7 sujets partiels/PT uniquement** (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers`, `review-pr` avec 2 langues et, désormais, `split-command` avec 2).
* **Lacune de documentation 🆕 :** `explain` et `tests` sont les deux premières fonctionnalités en plusieurs fenêtres à arriver **sans sujet dans `docs/`** — elles n'ont que les templates de skill.
* **Skills locales de Claude Code :** `.claude/skills/` avec **29 skills** (compte inchangé dans cette fenêtre).
* **Index de mémoire :** `.claude/memory/MEMORY.md` avec **41 motifs** (+1 dans cette fenêtre).
* **Rapports de tâches :** `docs/claude-code/reports/develop_natan/` (**101** au total ; **+8** dans la fenêtre) et `docs/gemini/reports/develop_natan/` (**7** ; **+2** — `2026-09-21_skill_gitpr_sast_bridge.md` et `2026-09-22_skill_gitpr_tests_generate.md`).
* **Rapports de statut :** `docs/reports/` (15 rapports ; celui-ci est le 16e).
* **Plans de développement :** 115 fichiers dans `docs/plans/` (+16 dans la fenêtre — specs/plans pour `demo`, `badge`, `split`, secrets, SAST, `tests` et `explain`, ADR-006 et ADR-007, les glossaires `glossary-gitpr-split` et `glossary-gitpr-secret-scanning`) + **10 fichiers dans `docs/survey/`** (+4).

---

## **🔄 Pipeline de Distribution**

1. **PyPI (canal unique) :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mise à jour obligatoire :** l'exécution vérifie PyPI au démarrage et **bloque avec le code de sortie 1** s'il existe une version plus récente, en imprimant `pip install --upgrade gitpr-cli` ; la vérification est mise en cache par jour et `--update` ne fait que rapporter
3. **GitHub Releases :** supprimées — plus de PyInstaller, plus d'asset `.exe`, plus de hot-swap
4. **GitHub Actions :** workflow `pr-review.yml` + `action.yml` (installe via pip) + **`tests.yml`** 🆕 (matrice Python 3.10 et 3.13, avec création explicite du profil `~/.gitpr` dont `test_demo_isolation.py` affirme l'existence)
5. **Serveur MCP :** point d'entrée `gitpr-mcp` via `pyproject.toml`
6. **Templates et langues OTA :** `templates/` et `langs/*.json` servis depuis GitHub (main) — le bump `v0.0.32` renouvelle les copies locales dans `~/.gitpr/langs/` une fois publié
7. **Version dérivée du code 🆕 :** `pyproject.toml` utilise `version = {attr = "src.updater.__version__"}` — `__version__` est la **seule** source, et c'est pourquoi un bump non commité laisse le paquet construit en 1.2.0 alors que `CHANGELOG.md` annonce déjà 1.3.0
8. **État de la release 1.3.0 🆕 :** le **tag `v1.2.0` a été créé** (merge de la PR #174, 2026-09-17), clôturant le bloqueur de la fenêtre précédente. `__version__` (1.3.0) et `__lang_version__` (v0.0.32) sont **dans l'arbre de travail et non commités** (HEAD à 1.2.0 / v0.0.31) ; `CHANGELOG.md` a l'entrée `[1.3.0]` commitée, mais **incomplète** — elle ne couvre que l'analyse de secrets. La voie est d'étendre le changelog avec les 6 fonctionnalités restantes, de lancer `gitpr release`, de commiter le bump et de tagger.

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.15)**

| Domaine | v0.0.15 (précédent) | v0.0.16 (actuel) |
|------|-------------------|-----------------|
| **Version de GitPR** | 1.2.0 (bump **non commité** ; HEAD à 1.1.0) | **1.3.0** (bump **non commité** ; HEAD à 1.2.0) — **tag `v1.2.0` créé** ✅ |
| **Version des langues** | v0.0.28 | **v0.0.32** (via v0.0.29, v0.0.30 et v0.0.31 ; bump **non commité** — HEAD à v0.0.31) |
| **Version des scripts de hook** | v0.0.3 | **v0.0.3** |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Langues** | 5 langues, 6 dictionnaires | 5 langues, 6 dictionnaires |
| **Sous-commandes** | 4 (`release`, `config`, `fix`, `review-pr`) | **9** (+ `demo`, `badge`, `split`, `tests`, `explain`) |
| **Interface** | CLI + TUIs + wizards `--init`/`--install` + 4 sous-commandes | **+ `gitpr demo` (TUI + mode texte) + `gitpr badge` + `gitpr split` + `gitpr tests generate` + `gitpr explain` + flag `--explain`** |
| **Couches** | `src/infrastructure/` (SCM) | **+ `src/domain/` et `src/application/use_cases/` (architecture en couches)** 🆕 |
| **Outils MCP** | 14 outils / 18 ressources / 7 prompts | **14 outils / 18 ressources / 7 prompts** (inchangé — `skill://explain` et `skill://tests` **manquantes** ⚠️) |
| **Flags CLI** | 35 à la racine + `release` (6) + `config` (0) + `fix` (8) + `review-pr` (2) | **36 à la racine** (+`--explain`) + `release` (6) + `config` (0) + `fix` (8) + `review-pr` (2) + **`demo` (3) + `badge` (2) + `split` (5) + `tests generate` (5) + `explain` (1)** |
| **Variables d'environnement** | 44 clés dans `DEFAULT_CONFIG` | **49 clés** (+5 : 2 sécurité + 3 split) — **+5 en lecture seule hors `DEFAULT_CONFIG`** (`GITPR_BADGE`, `GITPR_EXPLAIN_BY_DEFAULT`, 3× `GITPR_SAST_*_ENABLED`) |
| **Schéma de configuration** | 61 `ConfigField` / 13 catégories | **71 `ConfigField` (+10) / 14 catégories** (+ Split) |
| **Linter** | Regex + pont Checkstyle | **+ ruleset de secrets intégré (7 règles) + wildcard `extensions: ["*"]` + 3 ponts SAST opt-in (Semgrep, Gitleaks, Bandit) avec dédup multi-source** |
| **Git Hooks** | `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG`, `effective_hook_lang()` | Inchangé (mais le pre-commit **bloque désormais les secrets** par défaut) |
| **Couche SCM** | 4 forges, `get_pull_request`, `supports_reviewable_diff` | Inchangée dans cette fenêtre |
| **i18n (clés par fichier)** | 1048 × 6 (parité complète) | **1158 × 6 (parité complète) — +110**, mais le code utilise **1198** → **40 clés non traduites** ⚠️ |
| **Documentation** | 41 sujets canoniques (35 complets + 6 partiels) | **44 sujets canoniques (37 complets + 7 partiels) — 3 nouvelles familles + `docs/tutorial/`** |
| **CI** | Aucun workflow de test | **`.github/workflows/tests.yml`** (Python 3.10 + 3.13) 🆕 |
| **Suite de tests** | 1437 scénarios (64 fichiers) | **1889 scénarios (96 fichiers) — +452 scénarios, +32 fichiers ; en_us : 1883 réussis / 4 échoués / 2 ignorés** |
| **Échecs hérités** | 3 (2 timeout + 1 locale) | **0 — tous clos** ✅ (4 nouveaux, une seule cause racine) |
| **Commits depuis le rapport** | 5 commits | **8 commits** (`8920d13`, `da162d0`, `69f41ec`, `c38aed2`, `47784a7`, `a799664`, `7d84daf`, `6138cf1`) |
| **PRs fusionnées** | 3 PRs (#167, #171, #173) | **7 PRs (#177, #179, #181, #183, #185, #187, #189)** |
| **Index de mémoire** | 40 motifs | **41 motifs** |
| **Rapports de tâches** | 93 claude-code, 5 gemini | **101 claude-code (+8) et 7 gemini (+2)** |
| **Plans de développement** | 99 plans, 6 surveys | **115 plans (+16), 10 surveys (+4)** |

---

## **🚧 Prochaines Étapes**

* **Clore la dette des deux nouvelles fonctionnalités 🆕 :** enregistrer `tests` et `explain` dans `SKILL_LABELS` (`src/config_schema.py`) et dans `mcp_server.SKILL_FILES`, et lancer `python tests/sync_i18n.py` pour les **40 clés** manquantes. Cela fait **4 tests rouges** avec une seule cause racine — c'est l'élément le moins coûteux de cette liste et le seul qui laisse aujourd'hui la suite non verte.
* **Clore la release 1.3.0 🆕 :** `__version__` (1.3.0) et `__lang_version__` (v0.0.32) sont dans l'arbre de travail sans commit. En plus de cela, l'entrée `[1.3.0]` de `CHANGELOG.md` **ne couvre que l'analyse de secrets** — `demo`, `badge`, `split`, le SAST, `tests` et `explain` doivent y entrer avant de tagger.
* **Documenter `explain` et `tests` 🆕 :** ce sont les deux premières fonctionnalités en plusieurs fenêtres à arriver sans sujet dans `docs/` — seuls les templates de skill et les specs dans `docs/plans/` existent.
* **Traduire ce qui est resté partiel :** `docs/split-command.*.md` n'existe qu'en EN et PT-BR (pt_pt, es_es, fr_fr manquent), tout comme `docs/review-pr.*.md`.
* **Étendre la couverture du ruleset de secrets 🆕 :** les lacunes sont déclarées dans le changelog lui-même — assignation **sans guillemets** (`API_KEY=abc123`, le format `.env`, qui est précisément là où les secrets fuient), les préfixes `ASIA…`, `github_pat_…` et `xoxc-`/`xoxd-`, et l'absence de frontière gauche sur le nom de clé (aujourd'hui `mytoken` correspond autant que `token`).
* **Documenter la rupture de contrat de `request_pull_request_reviewers` :** il est passé de `None` à `list[str]` ; les sous-classes tierces de `ScmProvider` doivent être mises à jour. `docs/scm-multiforge.*.md` est l'endroit, dans les 5 versions. **Élément hérité, toujours ouvert.**
* **`.gitpr/metrics/export/` dans `.gitignore` :** quatre paires CSV/JSON supplémentaires sont entrées suivies dans cette fenêtre (`2026-09-18`, `19`, `21` et `22`). Ce sont des artefacts générés localement. **La dette a grandi.**
* **Fournisseur Anthropic Claude :** support direct de l'API Claude (`claude-sonnet-5`).
* **Graphiques ASCII/Textual dans le Dashboard :** histogrammes temporels et tendances de tokens dans la TUI de métriques.
* **Pipeline de release dans GitHub Actions :** automatisation du build et de l'upload vers PyPI (la CI de tests existe désormais ; celle de release manque).
* **Seed local `.gitpr/conf/` :** l'amorçage des templates de configuration locale (smart-excludes, linter) reste en attente en tant que sous-commande à part entière ou étape de wizard.
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires.
* **Extracteur i18n `sync_i18n.py` :** la regex tronque les littéraux avec concaténation implicite (`__("a " "b")`) — migrer vers l'AST.
* **Réconcilier la version du projet :** `CLAUDE.md` dit encore `Current version: 0.0.37` alors que `__version__` est à 1.3.0. Définir une convention unique. **Élément hérité, toujours ouvert.**
* **Dette d'index du README :** les familles `suggested-reviewers`, `scm-multiforge`, `config-tui` et `usage-log` restent hors de l'index (celles de cette fenêtre — `demo`, `badge`, `split-command` — y sont entrées).
* **`gitpr -h config` ignore `-h` :** la sous-commande ouvre la TUI au lieu d'afficher l'aide — la barrière `if ctx.invoked_subcommand is not None: return` s'exécute avant le bloc `help_flag`. La corriger changerait le comportement de `-h` pour **toutes** les sous-commandes, donc cela demande une décision.
* **Section Smart Exclude dans la TUI :** des 12 éléments signalés après usage de l'écran, l'élément 10 (*Smart Exclude*) est le seul livrable pas encore commencé.
* **Dettes consignées dans le plan de la TUI de configuration :** `DEFAULT_CONFIG` est devenu redondant avec le schéma ; la bannière d'ouverture ne liste pas `--dashboard`, `--init`, `--base` ni `--plugins` ; `LinterApp` ne désactive pas la palette de commandes.

### ✅ Terminés dans cette fenêtre (2026-09-17 → 2026-09-23)

* ~~**Aligner les tests de timeout obsolètes**~~ — `tests/test_net_timeouts.py` affirme désormais les vrais 180s (`test_ai_timeout_defaults_to_180`) et la docstring de `get_ai_timeout()` a été corrigée. **Un élément ouvert depuis trois rapports.**
* ~~**Robustesse des locales dans les tests**~~ — `tests/conftest.py` épingle `GITPR_LANG=en_us` ; les **22 échecs de locale** que la CI a révélés sur une machine pt-BR ont aussi disparu.
* ~~**Clore la release 1.2.0**~~ — tag `v1.2.0` créé (merge de la PR #174) et l'entrée `[1.2.0] - 2026-09-17` est dans `CHANGELOG.md`. **C'était l'élément qui bloquait la publication.**
* ~~**Sous-commande `gitpr demo`**~~ — paquet `src/demo/` + TUI `src/ui/demo/`, vrai pipeline avec un faux provider, 2 scénarios en 5 langues et une garde d'isolation (PR #177).
* ~~**Badge GitPR et commande `gitpr badge`**~~ — `src/branding/`, mesure honnête, point d'injection unique, opt-out `GITPR_BADGE` et 7 fichiers de test (PR #179).
* ~~**Sous-commande `gitpr split`**~~ — paquet `src/split/` (6 fichiers), `src/infrastructure/git/` avec `selective_stager`, pré-validation contre un index temporaire (PR #181).
* ~~**Analyse de secrets intégrée**~~ — `src/security_ruleset.py` (7 règles), le wildcard `extensions: ["*"]`, deux variables d'opt-out et l'alerte qui n'affiche jamais la valeur (PR #183).
* ~~**Ponts SAST Semgrep/Gitleaks/Bandit**~~ — `src/infrastructure/linter/external/`, dédup `[Gitleaks + Regex]`, masquage des secrets et opt-in strict (PR #185).
* ~~**Sous-commande `gitpr tests generate`**~~ — `src/domain/tests_generation/` + `src/application/use_cases/`, détection de framework, validation de syntaxe et le chat déléguant au même cas d'usage (PR #187).
* ~~**Sous-commande `gitpr explain` et flag `--explain`**~~ — `src/domain/pr/explain_section_builder.py` + `generate_pr_explanation.py`, parsing tolérant avec détection de `[FILL]` (PR #189).
* ~~**Première CI pour la suite**~~ — `.github/workflows/tests.yml` sur Python 3.10 et 3.13, plus le `conftest.py` hermétique et la stabilisation des tests de workers.
* ~~**Hygiène i18n au premier lancement**~~ — `i18n.py` crée le répertoire de profil avant de persister la langue détectée.
* ~~**`patch_applier` partagé**~~ — promu dans `src/infrastructure/git/`, avec `src/fix/patch_applier.py` conservé comme shim de ré-export (rien ne s'est cassé).

---

**Rapport généré le :** 2026-09-23  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
