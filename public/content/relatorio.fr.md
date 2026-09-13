# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.14 (2026-09-13)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.14) :**
- **Sous-commande `gitpr config` — TUI de configuration interactive :** Un écran maître-détail (Textual) au-dessus de `~/.gitpr/.env` avec un menu latéral de catégories, des champs édités en ligne, une recherche globale (`/`), `F2` pour enregistrer, `Ctrl+R` pour restaurer et `Esc` avec confirmation d'abandon. Un **schéma déclaratif** (`src/config_schema.py` — 12 catégories, 56 `ConfigField`, 8 avancés) est l'unique source de vérité : le menu, les widgets, les défauts et la validation en dérivent tous, si bien qu'ajouter un réglage est devenu un changement de **données**, et non d'interface.
- **Section Skills — la première surface à périmètre projet de l'écran :** Un panneau maître-détail en ligne qui édite les fichiers `.gitpr/skill/*.md` du projet (de manière atomique, en préservant CRLF/LF) dans la même passe `F2` qui écrit le `.env`, avec un compteur unique de modifications en attente. Le registre des types pris en charge (`SKILL_FILES_BY_TYPE`) a été unifié dans `src/config.py`, alors qu'il était auparavant répété à plus de 6 endroits.
- **Journal Général d'Utilisation (`src/usage_log.py`) :** Une ligne par commande dans `~/.gitpr/logs/<uuid5-de-la-date>.log` — **un fichier par jour** — écriture synchrone, n'affiche jamais et ne lève jamais. Appelé depuis seulement deux endroits (le callback racine de `cli()` et le `main()` du serveur MCP), qui couvrent ensemble chaque flag, les deux sous-commandes et chaque chemin `ctx.exit()`. Contrôlé par `GITPR_SHOW_LOGS` (né activé sur toute installation existante).
- **Correction de la langue des Git Hooks :** La langue choisie par l'utilisateur est désormais réellement respectée — `HOOK_SCRIPT_SUFFIXES` fait correspondre les codes d'interface (`es_es`, `fr_fr`) aux suffixes publiés (`.es`, `.fr`), `SCRIPTS_LANG` (le choix de l'utilisateur) a été séparé de `SCRIPTS_INSTALLED_LANG` (l'état sur disque) afin que l'auto-synchronisation puisse détecter un changement de langue, et `--lang` n'est plus ignoré.
- **Distribution exclusive via PyPI avec barrière de mise à jour obligatoire :** Le canal binaire a été abandonné — aucune génération, aucun upload, aucun fallback. `src/updater.py` a été réécrit autour d'une source de vérité unique (l'API PyPI) : `enforce_update_required()` bloque l'exécution avec le **code de sortie 1** lorsqu'une version plus récente est publiée, en affichant `pip install --upgrade gitpr-cli`. La requête à l'API GitHub Releases, la résolution des assets, le hot-swap avec rollback et la dépendance `pyinstaller` ont disparu.
- **Journal d'utilisation, télémétrie et dogfooding :** GitPR a lui-même généré les release notes de cette fenêtre (`gitpr release` dans `.gitpr/reports/release/`), a utilisé le journal d'utilisation pour reconstituer l'activité et les skills locales `.gitpr.release.md` / `.gitpr.filereview.md` comme instructions système.
- **i18n étendue à 955 clés :** +213 clés depuis le rapport précédent, couvrant la TUI de configuration et les surfaces de la barrière PyPI ; `__lang_version__` est passée de v0.0.23 à **v0.0.25** (chaîne v0.0.23 → v0.0.24 → v0.0.25) et les 6 dictionnaires conservent une **parité totale des key sets**.
- **Documentation Multilingue Étendue :** 2 nouvelles familles complètes en 5 langues — `config-tui` et `usage-log` — et 7 sujets mis à jour (`ARCHITECTURE`, `auto-update`, `hooks-versioning`, `mcp-integration`, `skill-template`, `testar_sem_usar_pypi`, `version-markers`).
- **Suppression de la clé morte `PR_AUTO_PUBLISH` :** Prouvée inexistante dans `src/` (zéro occurrence) et retirée de la liste de variables d'environnement de `CLAUDE.md` ainsi que du `.env` de l'utilisateur ; le §5 de la doc de la TUI a été corrigé dans les 5 versions, car il promettait « en dehors de l'écran » des clés qui apparaissent en réalité en lecture seule sous *Inconnues*.
- **Saut de Version :** `__version__` est passée de 1.0.0 à **1.1.0** ; `CHANGELOG.md` enregistre `[1.1.0] - 2026-09-13`, généré par la fonctionnalité de release elle-même.

- **Version actuelle :** 1.1.0
- **Version des dictionnaires de langue :** v0.0.25
- **Version des scripts de hook :** v0.0.3
- **Publication :** PyPI (`pip install gitpr-cli`) — **canal binaire supprimé dans cette fenêtre**
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licence :** LGPL-2.1
- **Langues prises en charge :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues, 6 dictionnaires)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **CLI Framework :** Click (pour les commandes, flags et formatage du terminal).
* **UI/Terminal :** Textual — TUI (Text User Interface) pour le chat interactif, l'édition d'issues, l'écran d'aide, le tableau de bord de métriques, le PR Publisher, les erreurs du linter (`LinterApp`) et **la configuration (`ConfigApp`)** 🆕.
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API, des jetons GitHub et des jetons SCM des forges — les secrets édités dans la TUI de configuration sont également chiffrés avant d'être écrits.
* **Configuration :** `python-dotenv`, `pyyaml` (pour le linter statique) + **son propre schéma déclaratif (`src/config_schema.py`)** 🆕.
* **Fournisseurs IA :** Intégration via le SDK officiel Google GenAI (`gemini-2.5-flash`), le SDK OpenAI (`DeepSeek`) et le SDK OpenAI (`Ollama` local).
* **APIs de Forge :** `requests` (REST) — couche d'abstraction multi-forge dans `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) ; module legacy `src/github_api.py` conservé comme shim déprécié.
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK officiel Anthropic pour Model Context Protocol) — 12 outils annotés, 17 ressources, 7 prompts ; handlers déchargés vers des threads via `anyio`.
* **Tests :** Pytest + `unittest.mock` (49 fichiers de test — 40 à la racine + 9 dans `tests/scm/` —, 1060 scénarios collectés) + tests e2e du serveur MCP via subprocess réel (JSON-RPC stdio).
* **Empaquetage :** setuptools/build (PyPI). **PyInstaller a quitté le projet dans cette fenêtre** — il n'y a plus de binaire standalone.
* **CI/CD :** GitHub Actions (`pr-review.yml`) + `action.yml` pour l'exécution dans les pipelines.

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec le LLM en demandant une sortie strictement JSON.
* **Map-Reduce (Diffs Géants) :** Lorsque le diff dépasse ~90k tokens, le divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce). Prend en charge les PRs, commits et Issues.
* **Tokenizer Local :** `tokenizer.json` pour une estimation précise des tokens avant l'envoi à l'IA.
* **Estimation des Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()` avec fallback vers le tokenizer local.
* **Optimisation Native de Git :** Flags `-U1`, `-w`, `-M`, `-B` sur les commandes `get_git_diff` et `get_git_full_diff` pour réduire le contexte inutile.
* **Pre-Save (`--pre-save`) :** Flag caché de debug qui enregistre le payload complet (instruction système + prompt) en JSON avant chaque appel à l'IA.
* **Smart Excludes à Deux Couches :** Filtre pathspec intelligent avec couche globale (`~/.gitpr/conf/`) + locale du projet (`./.gitpr/conf/`). Fusion au runtime (union, dédupliquée). Auto-seed du fichier local à la première exécution. 🆕 `_load_smart_excludes()` accepte `force=` pour un re-téléchargement à la demande depuis la TUI de configuration.
* **Métriques avec Suivi du Temps :** Injection de `log_command_metric()` dans tous les flux avec transmission de la durée en millisecondes (`duration_ms`) et imports paresseux.
* **Résolution Centralisée de la Sortie :** Fonction `resolve_output_path()` qui centralise la logique des répertoires de sortie — par défaut dans `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`) :** `gitpr --init` — détecte la forge depuis le remote origin, demande les extras propres à chaque forge (Azure org/project, username Bitbucket), valide le jeton avec `test_connection` (3 tentatives, re-prompt sur 401) et persiste `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` (Fernet) **uniquement en cas de succès**.
* **Template de Skill de Release (`ensure_release_skill_template()`) :** Télécharge `templates/gitpr.release.*.md` à la première utilisation de `gitpr release` (couche CLI, tenant compte de la langue, n'écrase jamais ; sauté avec `--format json`).
* **Registre de Skills Partagé 🆕 :** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` déplacés de `core.py` vers `src/config.py` (la TUI ne peut pas importer `core` au niveau racine — cela tire les SDK d'IA) ; `get_skill_context()` utilise désormais `skill_file_for()`.
* **Trailer de Co-paternité :** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, préserve les trailers de tiers.
* **Subprocesses Blindés :** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` sur chaque `subprocess.run` ; vérification de connexion via socket `8.8.8.8:53` avant les opérations réseau.

### **2. Système Global de Plugins (`src/plugins.py`)**

* **Architecture de Plugins :** Système d'extensibilité qui charge les plugins du répertoire `~/.gitpr/plugins/` en s'appliquant à **tous les projets**.
* **Plugins de Linter (`linter/`) :** Fichiers `.yml` avec des règles regex supplémentaires fusionnées avec le `.gitpr.linter.yml` local.
* **Plugins de Prompt MCP (`prompts/`) :** Fichiers `.md` qui étendent le contexte système avec des instructions spécifiques.
* **Factory Closures :** Fonctions `get_linter_plugins` et `get_prompt_plugins` avec closures pour isoler l'état entre les sessions.
* **Commande `--plugins` :** Liste tous les plugins globaux installés avec leurs types et chemins.
* **Documentation Multilingue :** `docs/plugins-system.md` en 5 langues (EN, PT-BR, PT-PT, ES, FR).

### **3. Interface CLI et Configuration (`src/main.py` et `src/config.py`)**

* **Setup Initial :** Détecte la première exécution, crée le dossier `~/.gitpr/` et demande interactivement les clés API, les préférences et la langue.
* **Routage des Commandes :** Gère tous les flags et les **2 sous-commandes** — `release` et `config` 🆕.
* **Comportement par Défaut :** Exécuter `gitpr` sans flags ouvre la TUI du PR Publisher.
* **Flags (35 options Click à la racine, inchangées dans cette fenêtre) :**
  * `--init` : Ouvre le wizard de configuration du SCM multi-forge (détection de la forge + validation du jeton).
  * `--no-suggest-reviewers` : Désactive la suggestion de reviewers dans le flux de publication du PR.
  * `--no-publish` : Génère la description du PR et l'enregistre localement sans ouvrir l'éditeur interactif.
  * `--no-edit` : Saute entièrement la TUI — auto-commit, auto-push et publie directement sur la forge.
  * `--base <branch>` : Remplace la branche cible du Pull Request.
  * `--plugins` : Liste les plugins globaux installés.
  * `--linter-setup` : Ouvre l'assistant interactif de configuration des linters externes.
  * `--version` : Affiche la version actuelle de GitPR (via `@click.version_option`).
* **Sous-commande `config` 🆕 :** 0 option — ouvre la TUI de configuration. N'appelle délibérément **pas** `setup_environment()`, afin qu'aucun `click.prompt` ne dispute le terminal à la TUI. Import paresseux ; epilog avec `get_doc_url("config-tui.md")`.
* **Barrière de Mise à Jour Obligatoire 🆕 :** Au début du callback `cli()`, **après** le handler `--lang` (pour que le message sorte dans la langue demandée) et **avant** la distribution des flags (car `--linter` retourne avant `check_internet_connection()`) — sans elle, la plupart des commandes resteraient non protégées. Elle saute `--quiet`, `--hook`, `--mcp`, `--update` et `-h/--help` ; `--help`/`--version` sont des options Click *eager* et n'atteignent jamais le corps.
* **Journal d'Utilisation 🆕 :** `log_usage()` en tête du callback, avant `if ctx.invoked_subcommand is not None: return` — les 35 flags, les 2 sous-commandes et le `ctx.exit()` de `-h` passent tous par là.
* **Variables d'Environnement (39 clés dans `DEFAULT_CONFIG`, inchangées) :** `GITPR_SKIP_UPDATE_CHECK` 🆕 (toute valeur non vide désactive la barrière ; utilisée par la suite de tests) et `GITPR_SHOW_LOGS` (déclarée, initialisée à `"true"` et désactivée dans `tests/conftest.py`) — cette dernière est sortie de l'ombre et a gagné son propre champ dans la catégorie Général de la TUI.
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique de la fonctionnalité avec un lien direct (tenant compte de la langue) vers GitHub. Les sous-commandes ont leur propre `epilog=` (paragraphe `\b` de Click pour que l'URL ne soit ré-enroulée sous aucune locale).
* **--lang :** Force la langue de l'interface pour l'exécution en cours sans persister le changement — 🆕 et elle s'applique désormais aussi à la résolution des scripts de hook.
* **--provider :** Force le fournisseur d'IA (`gemini`, `deepseek`, `ollama`) pour l'exécution en cours.
* **--mcp :** Démarre le serveur MCP sur le transport stdio pour l'intégration avec les éditeurs — **12 outils annotés + 17 ressources + 7 prompts**.
* **--install :** Assistant guidé en 4 étapes qui télécharge des templates de skill, installe des Git Hooks, configure MCP dans les éditeurs et valide les clés API.
* **--metrics :** Système de télémétrie locale avec périmètre par dépôt : `--export`, `--purge`, `--dashboard`.
* **--status :** Liste les fichiers non commités catégorisés (new/modified/deleted) — rapide, sans IA, sans réseau.
* **Couche d'Écriture du `.env` 🆕 :** `read_env_file_values()` lit **uniquement le fichier** via `dotenv_values` (immunisé contre `os.environ`), `save_config_values()` écrit avec `set_key`, `remove_config_value()` avec `unset_key` — les 9 sites d'appel `set_key` préexistants ont été laissés intacts. `validate_ai_key()` sonde les SDK Gemini/DeepSeek avec des timeouts courts et distingue un identifiant refusé (`401`/`403`) d'un réseau injoignable.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)**

* **Interface Interactive Complète :** TUI construite avec Textual pour réviser, éditer et publier des Pull Requests directement dans le terminal.
* **6 Écrans Modaux :** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Reviewers Suggérés :** Le flux de publication interroge la forge pour obtenir des reviewers suggérés et les propose dans la TUI ; sélection contrôlée par `--no-suggest-reviewers`, `GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N` et `GITPR_REVIEWER_SUGGESTION_EXCLUDED`.
* **Bindings :** F1 (Aide), F2 (Enregistrer .md local), F3 (Publier via la forge), Échap (Quitter).
* **Flux d'Auto-Commit :** Linter → message IA → confirmation → commit → push → publie le PR.
* **Vérification des Fichiers Unstaged :** Au démarrage, vérifie `git status --porcelain` et propose un modal pour sélectionner, sauter ou annuler.
* **Gestion de PR Existant :** Détecte les PRs ouverts pour la branche actuelle via l'API et propose push ou création d'un nouveau.
* **Auto-Upstream :** Détecte l'échec de `git push` dû à l'absence d'upstream et tente automatiquement `--set-upstream origin <branch>`.
* **Flux de Merge :** Après création/mise à jour du PR, propose une option de merge. Contrôlé par `GITPR_AUTO_MERGE`.

### **5. Module API GitHub (`src/github_api.py`)**

* **Shim Déprécié :** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` et les fonctions restantes délèguent à `src/infrastructure/scm/github_provider.py` ; le module émet un `DeprecationWarning` et conserve les tuples legacy `(ok, data, status)` — aucun nouveau code ne doit l'importer.

### **6. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le git diff sans dépenser de quotas d'IA.
* **Règles YAML :** Lit le fichier local `.gitpr.linter.yml` (créé via `--skill`).
* **Plugins de Linter :** Règles supplémentaires chargées depuis `~/.gitpr/plugins/linter/*.yml`.
* **Pont de Linters Externes :** Exécute ESLint/PHPCS/Stylelint sur les lignes modifiées du diff, parser Checkstyle XML et croisement par ligne.
* **Rapport Consolidé :** `generate_linter_report_content()` consolide les erreurs regex + externes dans `.gitpr/reports/linter/` — généré uniquement en cas de violations.
* 🆕 `load_linter_presets()` accepte `force=` pour re-télécharger les presets depuis la TUI.

### **7. Sécurité et Authentification (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Chiffrement :** Génère une clé maîtresse `secret.key` dans le dossier `~/.gitpr/`.
* **Protection des Jetons :** `encrypt_data` et `decrypt_data` pour protéger les clés API d'IA, les PAT GitHub et les jetons SCM des forges (`GITPR_SCM_TOKEN_ENCRYPTED`).
* **Validation Multi-Forge :** `validate_or_request_scm_token(provider, repo_display)` — valide le jeton auprès de la forge configurée avec une boucle 401 → réauthentification préservant le brouillon ; le jeton GitHub legacy (`GITHUB_TOKEN_ENCRYPTED`) reste fonctionnel jusqu'à l'exécution de `--init`.
* **Secrets dans la TUI de Configuration 🆕 :** Les champs `KIND_SECRET` sont édités dans un champ masqué, **n'affichent jamais** la valeur en clair et sont chiffrés avec Fernet avant d'être écrits — aucun chemin ne relit le secret vers l'écran. `GITPR_SCM_TOKEN` est `read_only` et sa description pointe vers `gitpr --init` comme seul chemin qui devrait l'écrire.

### **8. Auto-Updater (`src/updater.py`) — réécrit dans cette fenêtre 🆕**

* **PyPI comme Source Unique :** `get_latest_remote_version()` interroge toujours `https://pypi.org/pypi/gitpr-cli/json`, retourne une **chaîne** de version et écrit le cache quotidien **sans** le champ `download_url`. Il a perdu le paramètre `is_compiled` et toute la branche de l'API GitHub Releases.
* **Barrière Obligatoire (`enforce_update_required()`) :** Retourne `True` (après avoir affiché les deux versions et la commande pip) lorsque la version publiée est plus récente ; retourne `False` lorsque tout est à jour, lorsque la version distante est **inconnue (hors ligne — l'utilisateur n'aurait aucun moyen de mettre à jour)** ou lorsque la vérification est désactivée. Retourner un `bool` au lieu d'appeler `sys.exit` en interne garde la fonction testable.
* **`check_and_update()` :** Réécrit pour `--update` — il se contente d'interroger et de **rapporter**, n'installe jamais.
* **Supprimés :** `GITHUB_API_URL`, `_perform_hot_swap()` (renommait le `.exe` en `.old`, téléchargeait le nouveau, effectuait un rollback), `print_update_notice()` et ses 5 sites d'appel, le bloc de nettoyage des `.old` dans `main.py` et la dépendance `pyinstaller` du `Pipfile`. L'`icon.ico` a été supprimé.
* **Échappatoire :** `GITPR_SKIP_UPDATE_CHECK` (toute valeur non vide) — non annoncée à l'utilisateur comme fonctionnalité ; elle existe pour la suite de tests et l'automatisation hors ligne.
* **Cache Quotidien :** Évite les vérifications répétées le même jour.
* **Versionnage Centralisé :** `__version__` (**1.1.0**), `__lang_version__` (**v0.0.25**), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* **Défauts que ce changement élimine :** l'ancien `urlretrieve` n'avait ni timeout ni checksum, et `main.py` supprimait la sauvegarde `.old` à l'exécution suivante sans condition — un téléchargement tronqué était irrécupérable.

### **9. Interface de Chat Interactive (`src/ui/chat_app.py`)**

* **TUI Complète :** Construite avec Textual — historique des messages, saisie multi-ligne, barre d'état avec bindings visibles.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique de conversation persisté par branche, permettant la continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear` — raccourcis pour le pair programming.
* **Auto-Patching (F5), Rafraîchissement du Diff (F2), Export de Session (F6).**

### **10. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec prise en charge des placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système à la première exécution et l'enregistre dans `GITPR_LANG`.
* **5 Langues, 6 Dictionnaires :** en_us (défaut/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Fichiers Versionnés :** `__lang_version__` (**v0.0.25**) contrôle la mise à jour des packs de langue (`langs/*.json`) — chaîne de bumps v0.0.23 → v0.0.24 → v0.0.25 dans cette fenêtre.
* **Couverture :** **955 clés** de traduction dans chacun des 6 fichiers — **parité totale des key sets** (+213 depuis le rapport précédent).
* **Photo de l'Environnement (`AMBIENT_ENV_KEYS`) 🆕 :** `frozenset(os.environ)` capturé dans `i18n.py` **immédiatement avant** le `load_dotenv()` au niveau du module. C'était la racine d'un défaut silencieux de la TUI : comme `config.py` importe depuis `i18n.py`, tout le `.env` était déjà dans `os.environ` avant que l'écran n'existe, si bien que le badge « ⚠ dans l'environnement » confirmait tautologiquement que la clé est dans le fichier. Mesuré : **0 champ avec le badge** dans un processus propre, **40 sur 49** après l'import de l'écran. Corrigé aux trois points d'utilisation.
* **Clés Renommées/Supprimées 🆕 :** `Detected language: {lang}` → `Hooks language: {lang}` ; 6 clés obsolètes de mise à jour/binaire supprimées et 3 nouvelles clés de la barrière PyPI ajoutées.
* **Cache avec Indexation par Langue :** Les réponses IA en cache incluent la langue courante dans le keying MD5.

### **11. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA affichant des caractères braille avec des mots de « réflexion ».
* **263 entrées par langue :** Synchronisées entre les 5 langues. 🆕 `_load_thinking_words()` / `reload_thinking_words()` acceptent `force=`.

### **12. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Prise en charge :** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Mode JSON & Paramètres Déterministes :** Sorties structurées avec `temperature=0.0` et `top_p=0.1` ; fallback automatique entre les fournisseurs configurés.

### **13. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Keying par hash MD5 du diff et du prompt, avec indexation par langue.
* **Télémétrie et Durée :** Persistance des champs `duration_ms` et `meta_raw` dans les fichiers de cache.
* **Lecture pour le Dashboard :** `scan_cache_files_for_dashboard()` lit tous les fichiers de cache récursivement.

### **14. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff actuel, Historique de la branche (`-ht`), et Archéologie par Blame (`-b`).
* **Publication Multi-Forge :** F3 crée l'issue sur la forge **configurée** (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — Azure DevOps lève `ScmNotSupportedError` (les Work Items dépendent du template de processus).
* **Map-Reduce pour les Issues :** Lorsque le contexte dépasse ~90k tokens, divise automatiquement en chunks et unifie les résultats.
* **Gestion du 401 :** Signalisation de réauthentification sans fermer l'application.

### **15. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Suit l'évolution et la paternité historique des extraits de code avec classification des commits (`ORIGIN` vs `REFACTORING`).
* **Métriques de Blame :** Événements journalisés via `log_blame_metric()` avec suivi de la profondeur et du nombre de commits analysés.

### **16. Serveur MCP et Invocation CLI Directe (`src/mcp_server.py`)**

* **12 Outils MCP Annotés :** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **17 Ressources + 7 Prompts Templatisés :** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release}` + `linter://config` + `prompt://list` + 7 prompts.
* **Invocation CLI Directe :** La commande `gitpr-mcp --tool <name> [--tool-args '<json>']` invoque n'importe quel outil MCP directement sans démarrer le serveur stdio JSON-RPC. `gitpr-mcp --list` affiche le registre complet en JSON.
* **Isolation du Stdout Réel :** `_write_real_stdout()` écrit directement dans le `sys.__stdout__` original, garantissant du JSON pur sur stdout — la raison pour laquelle le journal d'utilisation **n'affiche jamais**.
* **Offload de l'Event Loop :** Décorateur `_offload` (`anyio.to_thread.run_sync`) appliqué aux 12 outils — les handlers synchrones ne gèlent pas le serveur stdio.
* **Journal d'Utilisation 🆕 :** Le `main()` du serveur appelle `log_usage()` — le script console `gitpr-mcp` ne charge jamais `main.py`, donc c'est le seul point qui l'atteint.
* **Tests E2E :** `tests/test_mcp_server_e2e.py` démarre le vrai serveur comme subprocess et parle JSON-RPC stdio.

### **17. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`)**

* **Périmètre par Dépôt (Repo-Scope) :** Étiquette `📁 Repository: owner/repo` et filtrage strict par projet.
* **Scan Asynchrone avec Overlay :** Worker thread en arrière-plan avec widget `ProgressBar`.
* **Consolidation des Données :** `load_cache_token_summary()` ajoute les tokens du cache au totalisateur.
* **Export Local :** Enregistrement CSV/JSON dans `./.gitpr/metrics/export/` (artefacts des 2026-09-12 et 2026-09-13 versionnés dans cette fenêtre).

### **18. Système de Métriques et Télémétrie (`src/metrics.py`)**

* **Périmètre par Dépôt :** Tous les événements indexés par `repo_name`.
* **Événements de Hook, Linter et Blame :** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Export et Nettoyage :** `--metrics --export` (CSV/JSON) et `--metrics --purge` avec confirmation interactive.

### **19. Synchronisation de la Langue des Git Hooks — corrigée dans cette fenêtre 🆕**

* **Versionnage Indépendant :** `__scripts_version__` (v0.0.3) contrôle la version des scripts de hook ; détection et mise à jour automatiques.
* **Correspondance des Suffixes (`HOOK_SCRIPT_SUFFIXES`) 🆕 :** Les codes d'interface (`es_es`, `fr_fr`) sont désormais traduits vers les suffixes réellement publiés (`.es`, `.fr`) — auparavant la langue choisie par l'utilisateur était simplement ignorée.
* **Choix vs. État (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`) 🆕 :** `SCRIPTS_LANG` est le choix de l'utilisateur ; `SCRIPTS_INSTALLED_LANG` est ce qui se trouve sur le disque. Séparés, l'auto-synchronisation peut **détecter un changement de langue** au lieu de supposer qu'elle est déjà installée.
* **`effective_hook_lang()` 🆕 :** Résout la langue effective des hooks ; `--lang` n'est plus écarté sur ce chemin (changement de comportement documenté).
* **Skip de Merge-Source :** Le template `prepare-commit-msg` saute les sources `message|merge|squash|commit` — les commits générés par git préservent le message original.

### **20. Pont de Linters Externes et Assistant Interactif (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistant `--linter-setup` :** Wizard interactif avec presets numérotés (PHP_CodeSniffer, ESLint, Stylelint) et injection du bloc `external_linters` dans `.gitpr.linter.yml`.
* **Presets Distants :** `templates/gitpr.linter-presets.json` servi depuis GitHub avec la chaîne de résolution local → téléchargement → stale → fallback embarqué.
* **TUI d'Erreurs du Linter :** `src/ui/linter_app.py` (Textual) affiche les erreurs critiques et les warnings ; en mode hook/quiet, imprime et fait `sys.exit(1)`.
* **Rapport Markdown :** Consolidé dans `.gitpr/reports/linter/` — uniquement en cas de violations.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Abstraction Unique (`ScmProvider` ABC) :** `base.py` définit le contrat (dataclasses `RepoRef`, `PullRequestDraft` etc. et `ScmProviderError(provider, http_status, message)` — `http_status` 0 = échec réseau) ; un provider concret par forge dans `github_provider.py`, `gitlab_provider.py`, `bitbucket_provider.py`, `azure_devops_provider.py`.
* **Registre et Factory :** `resolve_scm_provider()` sélectionne via `GITPR_SCM_PROVIDER` (défaut `github` — zéro migration, fallback du jeton GitHub legacy intact) ; `detect_provider_from_remote()` identifie la forge à partir de l'URL origin.
* **Adressage des Dépôts :** `parse_repo_ref(remote_url) -> RepoRef(raw, workspace, name, provider)` — workspace = owner GitHub / namespace GitLab (sous-groupes) / workspace Bitbucket / affichage `{org}/{project}` Azure.
* **Fail-Fast par Forge :** Azure DevOps exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` ; Bitbucket exige `GITPR_SCM_USERNAME` (App Password = Basic auth user+token) ; `create_issue` sur Azure lève `ScmNotSupportedError`.
* **Publication de Release :** `provider.create_release()` utilisé par `gitpr release --publish` (GitHub crée le tag sur la branche par défaut ; GitLab exige que le tag existe).
* **Artefacts :** Glossaire + ADR-001 dans `docs/plans/` ; famille `docs/scm-multiforge.*.md` en 5 langues ; tests : 9 fichiers, 265 scénarios.

### **22. Sous-commande `gitpr release` — Changelog / Release Notes**

* **Flux :** `git log` entre `--since` (défaut : dernier tag atteignable, ou le premier commit) et `HEAD` → classification Conventional Commits → bump sémantique suggéré (`--version <x.y.z>` le remplace) → assemblage du changelog → résumé exécutif IA optionnel → *insertion en tête* de `CHANGELOG.md`. La génération locale est le défaut — rien n'est publié ni modifié sans demande.
* **Classifier (`src/commit_classifier.py`) :** Classe les commits par type Conventional Commits (feat/fix/refactor/docs/chore/etc.) avec un parser tolérant.
* **Builder avec Sections Traduisibles (`src/changelog_builder.py`) :** Les sections "✨ Features", "🐛 Bug Fixes", "⚠️ Breaking Changes", "Summary", "Contributors" etc. sont rendues via `__()` au runtime (elles suivent `--lang`).
* **Bump Sémantique (`src/version_bump.py`) :** Suggère la prochaine version à partir des types classifiés (major pour breaking, minor pour feat, patch pour fix) et valide les cibles `x.y.z`.
* **Publication :** `--publish` crée la release sur la forge configurée (avec confirmation explicite) ; `--draft` la crée en brouillon (GitHub ; GitLab n'a pas de concept de brouillon) ; `--format markdown|json` pour une sortie structurée ; `--force` pour réécrire. **6 options dans la sous-commande.**
* **Template de Skill :** À la première utilisation, télécharge `templates/gitpr.release.*.md` (5 langues) via `ensure_release_skill_template()` — n'écrase jamais.
* **Dogfooding dans cette fenêtre :** `.gitpr/reports/release/` a reçu les release notes générées par la commande elle-même (`develop_natan_20260910144814`, `...145040`, `...145125`) et `.gitpr/skill/.gitpr.release.md` + `.gitpr.filereview.md` ont vu le jour comme skills locales du projet.
* **Artefacts :** Famille `docs/release-notes.*.md` (5 langues), spec dans `docs/plans/`, ADR-002 et ADR-003, glossaire des release notes.

### **23. Sous-commande `gitpr config` — TUI de Configuration 🆕**

* **Écran Maître-Détail (`src/ui/config_app.py`) :** Catégories à gauche, champs de la catégorie à droite, édités en ligne. En-tête avec recherche (`/`) et compteur de modifications en attente (`● N unsaved`) ; pied de page avec `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `Général` est toujours la première entrée du menu.
* **Schéma Déclaratif (`src/config_schema.py`) — l'unique source de vérité :** 12 catégories (Général, Fournisseurs d'IA, Pull Request, Révision de Code, Issue, Blame, Linter, Release, SCM / Forge, Filtres de Diff, Skills, Avancé) et **56 `ConfigField`**, dont **8 avancés**. Chaque champ déclare sa catégorie, son type de widget (`bool`/`int`/`str`/`enum`/`template`/`path`/`secret`/`version`/`words`), ses `show_if`, ses validateurs, ses marqueurs de version et ses actions de téléchargement. Les libellés sont des littéraux `__()` pour le scanner i18n.
* **Filtrage Contextuel (`show_if`) :** `GEMINI_*` / `DEEPSEEK_*` / `OLLAMA_*` apparaissent selon le `DEFAULT_AI_PROVIDER` sélectionné (aucun sélectionné → aucun bloc) ; `GITHUB_TOKEN_ENCRYPTED` apparaît avec un fournisseur vide ou `github` ; `GITPR_SCM_USERNAME` sous `[Bitbucket]` ; `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` sous `[Azure DevOps]`. Changer le `Select` refiltre le panneau **immédiatement, sans F2** (seul `on_select_changed` déclenche `_render_view()`, et uniquement pour les clés de `VISIBILITY_CONTROLLERS`, dérivées du schéma et non écrites à la main).
* **Recherche Globale (`/`) :** Correspond à la clé ou au libellé dans toutes les catégories et **ignore le filtre de visibilité** — chercher `deepseek` avec Gemini sélectionné trouve les champs, pour permettre de les pré-remplir. Un champ modifié est enregistré indépendamment de sa visibilité (`_build_plan` est aveugle à la visibilité, par conception).
* **Validation en Deux Couches :** **Hors ligne** (type, enum, template avec un placeholder connu et `{datetime}` obligatoire) bloque `F2` avec une erreur en ligne ; **en ligne** (uniquement pour les identifiants modifiés dans la session) s'exécute dans un worker avec un timeout de 10s et ne bloque que sur `401`/`403` — un échec réseau permet quand même d'enregistrer.
* **Restaurer (`Ctrl+R`) :** Supprime la ligne du `.env` au lieu de réécrire le défaut ; **`Esc`** avec des modifications en attente demande confirmation ; la catégorie **Inconnues** préserve les clés hors schéma en lecture seule (elle ne propose pas la suppression, par conception).
* **Section Skills — la seule au périmètre du projet 🆕 :** Panneau maître-détail en ligne (liste des skills à gauche, éditeur de texte à droite) qui édite les `.gitpr/skill/*.md` du projet résolus depuis le répertoire appelant, en écrivant de manière atomique et en préservant CRLF/LF. `F2` écrit le `.env` et les fichiers de skill **dans la même passe**, avec un compteur unique de modifications en attente.
* **Téléchargements Forcés :** Boutons qui forcent le re-téléchargement des smart-excludes, des traductions, des presets de linter et des thinking words, via un paramètre `force=` chaîné à travers les loaders.
* **Module Léger de Liens (`src/doc_links.py`) 🆕 :** `doc_url()` déplacé hors de `core.py` pour que l'UI puisse obtenir le lien de documentation sans importer `core`/les SDK d'IA. Chaque catégorie du schéma pointe vers sa doc canonique.
* **Tests :** 5 nouveaux fichiers — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Artefacts :** `docs/config-tui.*.md` en 5 langues, plan `docs/plans/20260912_config_tui.md`, glossaire `glossary-config-tui.md` (8 termes) et l'enquête grill.
* **Dette Connue :** `gitpr -h config` ouvre la TUI et ignore `-h` — la barrière `if ctx.invoked_subcommand is not None: return` s'exécute avant le bloc `help_flag` ; la corriger changerait le comportement de `-h` pour **toutes** les sous-commandes (documenté dans `docs/config-tui.md`).

### **24. Journal Général d'Utilisation (`src/usage_log.py`) 🆕**

* **Une Ligne par Commande :** Écrit dans `~/.gitpr/logs/<uuid5>.log`, **un fichier par jour**, avec la commande, les arguments, le dépôt, l'utilisateur et l'horodatage. Il répond à « qu'ai-je réellement exécuté, et quand ? ».
* **Nom Dérivé de la Date :** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` plutôt qu'aléatoire — un nom aléatoire exigerait un compteur ou un fichier d'état pour savoir quel fichier est celui d'aujourd'hui, et deux processus concurrents pourraient être en désaccord. Dérivé de la date, le même jour résout toujours vers le même nom et les commandes concurrentes ajoutent simplement au même fichier.
* **Écriture Synchrone (décision explicite) :** Contrairement à `log_local_metric`, qui utilise un thread daemon et perd donc l'écriture si le processus se termine tôt — inacceptable pour un journal qui promet d'enregistrer *chaque* commande.
* **N'Affiche Jamais :** Le serveur MCP réserve stdout au JSON-RPC ; un `print` accidentel corromprait le protocole. Le module ne lève jamais non plus.
* **Un Seul Spawn Git :** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` au lieu des trois idiomatiques — ~40 ms au lieu de ~150 ms sous Windows, à *chaque* exécution.
* **Son Propre `_repo_label()` :** Sans réutiliser `get_repo_name()` de `core.py` (regex codée en dur sur `github.com`, retournerait `unknown/repo` sur GitLab/Bitbucket/Azure — un nouveau défaut dans un projet qui vient de gagner le multi-forge) et sans `parse_repo_ref`, qui est une méthode de provider et exigerait de construire un provider (jeton, `requests`) à chaque commande.
* **Contrôle :** `GITPR_SHOW_LOGS` (défaut `"true"` — né activé sur toute installation existante, aucune migration) ; désactivée dans `tests/conftest.py`.
* **Artefacts :** `docs/usage-log.*.md` en 5 langues ; `tests/test_usage_log.py` (27 scénarios).

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame par plage de lignes dans un fichier |
| `tests/test_blame_metrics.py` | 7 | Métriques de blame : profondeur, commits, durée |
| `tests/test_changelog_builder.py` | 15 | Builder de changelog : sections, titres traduisibles, contributeurs |
| `tests/test_chat_backend.py` | 19 | Mémoire du chat, persistance, commandes slash |
| `tests/test_commit_classifier.py` | 23 | Classification Conventional Commits (types, parser tolérant) |
| `tests/test_config_app.py` | 78 🆕 | TUI de configuration : montage, changement de catégorie, suivi des modifications, F2 bloqué, Ctrl+R, recherche, secrets |
| `tests/test_config_cli.py` | 9 🆕 | Enregistrement de la sous-commande `config`, `-h`, import paresseux, stdout propre |
| `tests/test_config_schema.py` | 42 🆕 | Couverture de `DEFAULT_CONFIG`, absence de doublons, catégories/kinds, `advanced` uniquement dans Avancé |
| `tests/test_config_store.py` | 22 🆕 | Round-trip sur un `.env` temporaire, commentaires et ordre préservés, `remove_config_value()` idempotent |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuration des reviewers suggérés (clés et défauts) |
| `tests/test_config_validation.py` | 40 🆕 | Types, enums, templates, `validate_ai_key()` avec un SDK mocké (401 vs. réseau vs. ollama) |
| `tests/test_core.py` | 49 | Flux principaux, git diff, génération de PR, timing, staging, co-paternité, langue des hooks |
| `tests/test_diff_parser.py` | 15 | Parser de diff par lignes/hunks |
| `tests/test_external_linters.py` | 33 | Pont Checkstyle : parser XML, subprocess, croisement de diff, rapport |
| `tests/test_i18n.py` | 20 | Parité entre langues (955×6), clés manquantes/orphelines, identité |
| `tests/test_install_wizard.py` | 3 | Assistant interactif d'installation |
| `tests/test_issue_engine.py` | 4 | Brouillon d'issue structuré |
| `tests/test_linter_metrics.py` | 4 | Métriques de linter : erreurs, warnings, durée |
| `tests/test_linter_presets.py` | 5 🆕 | Presets de linter : résolution et re-téléchargement forcé |
| `tests/test_main_suggest_reviewers.py` | 6 | Flag `--no-suggest-reviewers` dans la CLI et l'aide contextuelle |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompt MCP et fallback de langue |
| `tests/test_mcp_server.py` | 86 | Outils MCP, ressources, annotations, patching, CLI direct, offload |
| `tests/test_mcp_server_e2e.py` | 6 | Vrai serveur MCP via subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Collecte, export local, périmètre de dépôt, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Timeouts réseau/IA — **2 assertions obsolètes (600s)** |
| `tests/test_plugins.py` | 17 | Découverte de plugins, fusion de règles de linter, prompts MCP |
| `tests/test_pr_publish_app.py` | 42 | TUI du PR Publisher : écrans, flux, reviewers suggérés |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal d'erreur du linter : abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save et payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release : options, aide avec epilog documenté |
| `tests/test_release_engine.py` | 27 | Moteur de release : plage de commits, CHANGELOG, publication |
| `tests/test_reviewer_suggestion.py` | 15 | Logique de suggestion de reviewers (classement, exclusion, top-N) |
| `tests/test_skill_command.py` | 10 | Téléchargement et validation des templates de skill |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` avec `quiet=True`, fallbacks, registre de skills |
| `tests/test_smart_excludes.py` | 15 | Filtre pathspec intelligent et re-téléchargement forcé |
| `tests/test_suggest_reviewers.py` | 14 | Reviewers suggérés dans le flux PR (intégration) |
| `tests/test_thinking_words.py` | 5 | Chargement, parsing avec le séparateur `;` et rechargement forcé |
| `tests/test_updater.py` | 23 🆕 | Barrière PyPI : parsing de version, cache quotidien, fetch, décisions de la barrière, câblage CLI |
| `tests/test_usage_log.py` | 27 🆕 | Journal d'utilisation : nom dérivé de la date, écriture synchrone, silence, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump sémantique : major/minor/patch, cibles et validation |
| `tests/scm/test_contract.py` | 38 | Contrat `ScmProvider` : signatures, dataclasses, erreurs |
| `tests/scm/test_github_provider.py` | 57 | Provider GitHub : REST, en-têtes, PRs, issues, releases |
| `tests/scm/test_gitlab_provider.py` | 41 | Provider GitLab : API v4, namespace, releases |
| `tests/scm/test_bitbucket_provider.py` | 39 | Provider Bitbucket : Basic auth, workspace |
| `tests/scm/test_azure_devops_provider.py` | 43 | Provider Azure DevOps : org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` déprécié → délègue au provider |
| `tests/scm/test_init_wizard.py` | 10 | Wizard `--init` : détection de forge, validation, persistance |
| `tests/scm/test_release_publish.py` | 8 | Publication de release par forge (GitHub/GitLab) |
| `tests/sync_i18n.py` | — | Script de vérification de couverture i18n (scaffold ; jamais exécuté) |

**Total :** 1060 scénarios collectés dans 49 fichiers de test (40 à la racine + 9 dans `tests/scm/` ; **+269** depuis le rapport précédent, avec **8 nouveaux fichiers**). Exécution complète sur cette machine avec `GITPR_LANG=en_us` : **1055 passed / 3 failed / 2 skipped / 33 subtests** en ~123s.

**Notes de qualité pour cette version :**
- **2 échecs réels (tests obsolètes, hérités) :** `test_net_timeouts.py` affirme encore le défaut de 600s pour `GITPR_AI_TIMEOUT`, mais le code utilise **180s** depuis le fix `681a7fa`. C'est le même élément qui figurait déjà dans les Prochaines Étapes du rapport précédent et qui **reste ouvert**.
- **1 échec de locale (nouveau, pas une régression) :** `test_core.py::TestHooksLanguage::test_the_language_chosen_with_the_lang_flag_is_honoured` affirme `i18n.CURRENT_LANG == "pt_br"` — il passe avec la locale pt-BR de la machine et échoue avec `GITPR_LANG=en_us`. C'est la suite de la langue des hooks introduite dans cette fenêtre.
- **La sensibilité à la locale passe de 4 à 1 :** les 4 échecs environnementaux de la fenêtre précédente (`test_chat_backend::test_api_exception`, `test_main_suggest_reviewers::test_flag_appears_in_contextual_help` et `test_suggest_reviewers` ×2) **passent désormais** avec `GITPR_LANG=en_us` — ils ne sont plus le problème, mais la cause racine (des tests qui présupposent une langue) persiste, elle a simplement migré vers un autre fichier.
- `tests/conftest.py` fixe désormais `GITPR_SHOW_LOGS=false` et `GITPR_SKIP_UPDATE_CHECK=true` — la suite n'écrit ni dans le journal d'utilisation ni n'est bloquée par la barrière de mise à jour.

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n :** **955 clés** de traduction dans chacun des 6 dictionnaires (+213 depuis le rapport précédent) avec **parité totale des key sets**. Les ~200 nouvelles clés couvrent la TUI de configuration et les surfaces de la barrière PyPI ; 6 clés obsolètes de mise à jour/binaire ont été supprimées, 3 nouvelles ajoutées et 2 clés d'aide réécrites. `__lang_version__` est passée v0.0.23 → v0.0.24 → **v0.0.25**, déclenchant le re-téléchargement OTA des traductions.
* **Sources de traduction en lockstep :** une nouvelle clé doit exister dans le code (source), dans `langs/pt_br.json` (**liste maîtresse**), dans les dicts FR/ES de `scripts/sync_all_langs.py` (deuxième source) et dans les valeurs curées de `scripts/fix_mangled_i18n_keys.py` (troisième source, lue par `tests/test_i18n.py`) ; l'assertion `len(CLEAN_KEYS)` est passée de 50 à **49**.
* **Nouveaux Sujets 🆕 (2, tous deux en 5 langues) :**
  - `docs/config-tui.md` — l'écran `gitpr config` : disposition, lecture des valeurs, édition/enregistrement, validation en deux couches, recherche, hors périmètre et une section pour les développeurs
  - `docs/usage-log.md` — journal d'utilisation : où vivent les fichiers, le nom dérivé de la date, le format de ligne et ce qu'il ne fait pas
* **Sujets mis à jour dans cette fenêtre (tous re-synchronisés dans les 5 langues) :** `docs/ARCHITECTURE.md`, `docs/auto-update.md` (réécrit pour le modèle PyPI uniquement), `docs/hooks-versioning.md` (langue effective des hooks), `docs/mcp-integration.md`, `docs/skill-template.md`, `docs/testar_sem_usar_pypi.md` (sans binaire) et `docs/version-markers.md` (nouveaux marqueurs).
* **Documentation en 5 langues :** **39 sujets canoniques** dans `docs/` — **34 avec couverture complète dans les 5 langues** (+2 depuis le rapport précédent) et 5 sujets partiels/PT-only (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers`).
* **Skills locales de Claude Code :** `.claude/skills/` avec **29 skills** (celles du projet — `status-report`, `implement-fixes`, `caveman-commit`, `new-feature`, `code-review`, `wizard`, `grilling` etc. — plus le kit `mattpocock-skills` ; le rapport précédent n'en listait que 5). La mémoire a gagné `i18n-sync-canonicos-roundtrip.md` dans le commit de la TUI de configuration.
* **Memory Index :** `.claude/memory/MEMORY.md` avec 40 patterns (compte inchangé dans cette fenêtre).
* **Rapports de tâches :** `docs/claude-code/reports/develop_natan/` (**90** au total ; **+6** dans la fenêtre — TUI de configuration, 5 correctifs d'UI + journal d'utilisation, layout/sections/téléchargements, section Skills, nettoyage de `PR_AUTO_PUBLISH` et suppression du binaire) et `docs/gemini/reports/develop_natan/` (5 fichiers ; aucun nouveau).
* **Rapports de statut :** `docs/reports/` (13 rapports ; celui-ci est le 14e).
* **Plans de développement :** 89 fichiers dans `docs/plans/` (+9 dans la fenêtre — les plans de la TUI de configuration, les correctifs de l'écran, la section Skills, le nettoyage de `PR_AUTO_PUBLISH`, la suppression du binaire et le glossaire `glossary-config-tui`) + 3 fichiers dans `docs/survey/`.

---

## **🔄 Pipeline de Distribution**

1. **PyPI (canal unique) :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mise à jour obligatoire :** l'exécution vérifie PyPI au démarrage et **bloque avec le code de sortie 1** s'il existe une version plus récente, en affichant `pip install --upgrade gitpr-cli` ; la vérification est mise en cache par jour et `--update` se contente de rapporter
3. **GitHub Releases :** **supprimé** — pas de PyInstaller, pas d'asset `.exe`, pas de hot-swap ; `pyinstaller` a quitté le `Pipfile` et `icon.ico` a été supprimé
4. **GitHub Actions :** Workflow `pr-review.yml` + `action.yml` (il installait toujours via pip, il n'a donc pas été affecté)
5. **MCP Server :** Point d'entrée `gitpr-mcp` via `pyproject.toml`
6. **Templates et Langues OTA :** `templates/` et `langs/*.json` servis depuis GitHub (main) — le bump `v0.0.25` renouvelle les copies locales dans `~/.gitpr/langs/` une fois publié

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.13)**

| Domaine | v0.0.13 (précédent) | v0.0.14 (actuel) |
|------|-------------------|-----------------|
| **Version GitPR** | 1.0.0 | **1.1.0** (CHANGELOG.md avec en-tête `[1.1.0] - 2026-09-13`) |
| **Version Langue** | v0.0.23 | **v0.0.25** (via v0.0.24) |
| **Version Scripts Hook** | v0.0.3 | **v0.0.3** |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Langues** | 5 langues, 6 dictionnaires | 5 langues, 6 dictionnaires |
| **Interface** | CLI + TUIs (Issues, Chat, Dashboard, PR Publisher, LinterApp) + wizard `--init` + `gitpr release` | **+ TUI de configuration `gitpr config` (12 catégories, 56 champs, section Skills) + journal général d'utilisation** |
| **Outils MCP** | 12 outils / 17 ressources / 7 prompts | 12 outils / 17 ressources / 7 prompts (+ `log_usage()` au point d'entrée) |
| **Flags CLI** | 35 options à la racine + sous-commande `release` (6) | 35 options à la racine + `release` (6) + **`config` (0 option)** |
| **Variables d'Environnement** | 39 clés dans `DEFAULT_CONFIG` | **39 clés** (+ `GITPR_SKIP_UPDATE_CHECK` ; `GITPR_SHOW_LOGS` est sortie de l'ombre et est devenue un champ de la TUI) |
| **Linter** | Regex + pont Checkstyle (wizard/TUI/rapport) | Inchangé (+ re-téléchargement forcé des presets depuis la TUI) |
| **Git Hooks** | La langue des scripts ignorait `--lang` | **Corrigé : `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG` vs. `SCRIPTS_INSTALLED_LANG`, `effective_hook_lang()`** |
| **Messages de Commit** | Avec trailer `Co-Authored-By` (opt-out) | Inchangé |
| **i18n (clés par fichier)** | 742 × 6 (parité totale) | **955 × 6 (parité totale) — +213 clés** |
| **Documentation** | 37 sujets canoniques (32 complets + 5 partiels) | **39 sujets canoniques (34 complets + 5 partiels) — 2 nouvelles familles ×5, 7 mis à jour** |
| **Distribution** | PyPI + GitHub Releases (binaire PyInstaller) | **PyPI exclusif — binaire, hot-swap et `pyinstaller` supprimés** |
| **Suite de Tests** | 791 scénarios (41 fichiers) | **1060 scénarios (49 fichiers : 40 + 9 SCM) — en_us : 1055 passed / 3 failed (2 obsolètes + 1 locale) / 2 skipped** |
| **Commits depuis le rapport** | 10 commits | **2 commits** (`bf9f1b9`, `f108c4c`) |
| **PRs mergés** | 5 PRs (#146, #151, #153, #155, #159) | **2 PRs (#162, #164)** |
| **Memory Index** | 40 patterns | **40 patterns** (+ `i18n-sync-canonicos-roundtrip.md`) |
| **Rapports de tâches** | 84 claude-code, 5 gemini | **90 claude-code (+6 dans la fenêtre) et 5 gemini** |
| **Plans de développement** | 80 | **89 (+9 dans la fenêtre — TUI de configuration, correctifs de l'écran, Skills, nettoyage et suppression du binaire)** |

---

## **🚧 Prochaines Étapes**

* **Fournisseur Anthropic Claude :** Support direct de l'API Claude (`claude-sonnet-5`).
* **Graphiques ASCII/Textual dans le Dashboard :** Ajouter des histogrammes de temps et des graphiques de tendance de tokens dans la TUI de métriques.
* **Pipeline de Release sur GitHub Actions :** Automatisation complète du build et de l'envoi vers PyPI (la génération de changelog est désormais locale via `gitpr release`, et le canal binaire n'existe plus — seule manque l'automatisation CI/CD).
* **Seed Local de `.gitpr/conf/` :** Le seed des templates de configuration locale (smart-excludes, linter) reste en attente comme sous-commande propre ou étape du wizard ; la TUI de configuration propose désormais le **téléchargement** de ces fichiers, mais pas le seed du projet.
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires.
* **Extracteur i18n dans `sync_i18n.py` :** Le regex tronque les littéraux à concaténation implicite (`__("a " "b")`) — migrer vers AST (la garde dans `test_i18n.py` utilise déjà AST et ne dépend pas du script).
* **Corriger les Tests de Timeout Obsolètes :** `tests/test_net_timeouts.py` (lignes ~99/117/137/149) affirme un défaut de 600s, mais le code utilise 180s depuis le fix `681a7fa` ; aligner aussi la docstring obsolète dans `config.py` (elle mentionne encore "default 600"). **Élément hérité, toujours ouvert.**
* **Réconcilier la Version du Projet :** `CLAUDE.md` dit encore "Current version: 0.0.37" alors que `__version__` est à 1.1.0 et que le CHANGELOG enregistre `[1.1.0] - 2026-09-13`. Définir une convention unique et mettre à jour le CLAUDE.md. **Élément hérité, toujours ouvert.**
* **Dette d'Index du README :** Les puces des familles `suggested-reviewers`, `scm-multiforge` **et désormais `config-tui` et `usage-log`** ne sont pas dans l'index — la dette a augmenté dans cette fenêtre.
* **Robustesse de Locale dans les Tests :** 1 test est sensible à la locale pt_br de la machine (`test_core.py::TestHooksLanguage`) — fixer `GITPR_LANG=en_us` dans le setup ou mocker `TRANSLATIONS` pour que la suite soit 100 % verte sur n'importe quelle machine/CI.
* **`gitpr -h config` ignore `-h` 🆕 :** La sous-commande ouvre la TUI au lieu d'afficher l'aide — la barrière `if ctx.invoked_subcommand is not None: return` s'exécute avant le bloc `help_flag`. La corriger changerait le comportement de `-h` pour **toutes** les sous-commandes, cela demande donc une décision.
* **Section Smart Exclude dans la TUI 🆕 :** Des 12 éléments remontés après l'utilisation de l'écran, l'élément 10 (la section *Smart Exclude*) est le seul livrable non encore entamé — l'esquisse se trouve dans les prochaines étapes du rapport de tâche.
* **Dettes consignées dans le plan de la TUI de configuration 🆕 :** `DEFAULT_CONFIG` est devenu redondant avec le schéma ; la bannière d'ouverture ne liste pas `--dashboard`, `--init`, `--base` ni `--plugins` ; `LinterApp` ne désactive pas la palette de commandes.

### ✅ Terminés dans cette fenêtre (2026-09-08 → 2026-09-13)

* ~~**Sous-commande `gitpr config` avec une TUI maître-détail**~~ — schéma déclaratif, couche d'écriture du `.env`, validation en deux couches, recherche, docs ×5 (PR #162).
* ~~**Section Skills dans la TUI**~~ — édition des `.gitpr/skill/*.md` du projet avec écritures atomiques, registre `SKILL_FILES_BY_TYPE` unifié dans `config.py` (PR #162).
* ~~**Journal Général d'Utilisation (`GITPR_SHOW_LOGS`)**~~ — `src/usage_log.py`, un fichier par jour, écriture synchrone, silencieux (PR #162).
* ~~**Correction de la langue des Git Hooks**~~ — `--lang` n'est plus ignoré ; `SCRIPTS_LANG` séparé de `SCRIPTS_INSTALLED_LANG` (PR #162).
* ~~**Correction du badge d'environnement dans la TUI**~~ — `AMBIENT_ENV_KEYS` capturé avant le `load_dotenv` au niveau du module ; le badge signifie désormais ce qu'il promet (PR #162).
* ~~**Nettoyage de la clé morte `PR_AUTO_PUBLISH`**~~ — retirée de `CLAUDE.md` et du `.env` de l'utilisateur ; le §5 de la doc de la TUI corrigé dans les 5 versions.
* ~~**Distribution exclusive via PyPI + barrière de mise à jour obligatoire**~~ — `enforce_update_required()`, `GITPR_SKIP_UPDATE_CHECK`, suppression du hot-swap, du binaire et de `pyinstaller` ; `docs/auto-update.md` réécrit ×5 (PR #164).
* ~~**i18n : +213 clés et chaîne v0.0.23 → v0.0.25**~~ — parité totale des key sets dans les 6 dictionnaires, avec les trois sources de traduction en lockstep.
* ~~**Documentation des 2 nouvelles familles**~~ — `config-tui` et `usage-log` en 5 langues, plus 7 sujets mis à jour.

---

**Rapport généré le :** 2026-09-13  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
