# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.13 (2026-09-08)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.13) :**
- **SCM Multi-Forge (`gitpr --init` + couche `ScmProvider`) :** Une abstraction unique au-dessus de GitHub, GitLab, Bitbucket et Azure DevOps dans `src/infrastructure/scm/` — registre avec `resolve_scm_provider()` (défaut `github`, fallback du jeton legacy intact), `parse_repo_ref()` pour l'adressage des dépôts, des providers qui lèvent `ScmProviderError`, et un wizard `--init` qui détecte la forge depuis le remote, valide le jeton (`test_connection`, 3 tentatives, re-prompt sur 401) et persiste **uniquement en cas de succès** avec chiffrement Fernet. `src/github_api.py` est devenu un shim déprécié qui délègue au provider.
- **Reviewers Suggérés dans le flux PR :** Suggestion de reviewers depuis la forge elle-même lors de la publication des PRs, avec opt-out par flag (`--no-suggest-reviewers`) et configuration par variables d'environnement (`GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N`, `GITPR_REVIEWER_SUGGESTION_EXCLUDED`).
- **Sous-commande `gitpr release` (Changelog / Release Notes) :** Génère le changelog de la branche entre `--since` (défaut : dernier tag) et `HEAD`, classe les commits selon les Conventional Commits (`src/commit_classifier.py`), suggère un bump sémantique (`src/version_bump.py`), assemble les sections traduisibles (`src/changelog_builder.py`), ajoute un résumé exécutif IA et l'insère *en tête* de `CHANGELOG.md`. Avec `--publish`/`--draft` il publie la release sur la forge (GitHub crée le tag ; GitLab exige un tag existant) ; `--format markdown|json` pour une sortie structurée. Template de skill `gitpr.release.*.md` téléchargé automatiquement à la première utilisation, en 5 langues.
- **Serveur MCP Silencieux + DNS Borné :** Correction de la fuite de sortie des outils dans le flux stdio/CLI et résolution DNS bornée dans le temps (bug de la fenêtre précédente). Le défaut de `GITPR_AI_TIMEOUT` passe de 600s à **180s**.
- **URLs et Prompts Localisés :** URLs de dépôt standardisées dans les templates et la documentation ; les prompts de création d'issues reçoivent désormais la langue active.
- **i18n Étendue à 742 clés :** Les titres du changelog sont désormais traduisibles (helper au runtime, plus de constantes opaques), 48 nouvelles clés traduites dans les 6 dictionnaires, `__lang_version__` v0.0.23 et une puce pour la famille `release-notes` dans l'index du README (5 copies).
- **Documentation Multilingue Étendue :** 3 nouvelles familles complètes en 5 langues — `release-notes`, `scm-multiforge` et `suggested-reviewers` (+ ADRs d'architecture pour SCM et release) — et 8 sujets mis à jour.
- **Saut de Version :** `__version__` est passée de 0.0.37 à **1.0.0** (via 0.0.38 dans cette fenêtre) ; CHANGELOG.md enregistre l'en-tête `[v0.1.0] - 2026-09-07` généré par la fonctionnalité de release elle-même pendant le développement.

- **Version actuelle :** 1.0.0
- **Version des dictionnaires de langue :** v0.0.23
- **Version des scripts de hook :** v0.0.3
- **Publication :** PyPI (`pip install gitpr-cli`) + GitHub Releases (binaire standalone)
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licence :** LGPL-2.1
- **Langues prises en charge :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues, 6 dictionnaires)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **CLI Framework :** Click (pour les commandes, flags et formatage du terminal).
* **UI/Terminal :** Textual — TUI (Text User Interface) pour le chat interactif, l'édition d'issues, l'écran d'aide, le tableau de bord de métriques, le PR Publisher et les erreurs du linter (`LinterApp`).
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API, des jetons GitHub et des jetons SCM des forges.
* **Configuration :** `python-dotenv`, `pyyaml` (pour le linter statique).
* **Fournisseurs IA :** Intégration via le SDK officiel Google GenAI (`gemini-2.5-flash`), le SDK OpenAI (`DeepSeek`) et le SDK OpenAI (`Ollama` local).
* **APIs de Forge :** `requests` (REST) — couche d'abstraction multi-forge dans `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) ; module legacy `src/github_api.py` conservé comme shim déprécié.
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK officiel Anthropic pour Model Context Protocol) — 12 outils annotés, 17 ressources, 7 prompts ; handlers déchargés vers des threads via `anyio`.
* **Tests :** Pytest + `unittest.mock` (41 fichiers de test — 32 à la racine + 9 dans `tests/scm/` —, 791 scénarios collectés) + tests e2e du serveur MCP via subprocess réel (JSON-RPC stdio).
* **Empaquetage :** PyInstaller (binaire standalone) + setuptools/build (PyPI).
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
* **Smart Excludes à Deux Couches :** Filtre pathspec intelligent avec couche globale (`~/.gitpr/conf/`) + locale du projet (`./.gitpr/conf/`). Fusion au runtime (union, dédupliquée). Auto-seed du fichier local à la première exécution.
* **Métriques avec Suivi du Temps :** Injection de `log_command_metric()` dans tous les flux avec transmission de la durée en millisecondes (`duration_ms`) et imports paresseux.
* **Résolution Centralisée de la Sortie :** Fonction `resolve_output_path()` qui centralise la logique des répertoires de sortie — par défaut dans `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`) 🆕 :** `gitpr --init` — détecte la forge depuis le remote origin, demande les extras propres à chaque forge (Azure org/project, username Bitbucket), valide le jeton avec `test_connection` (3 tentatives, re-prompt sur 401) et persiste `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` (Fernet) **uniquement en cas de succès**.
* **Template de Skill de Release (`ensure_release_skill_template()`) 🆕 :** Télécharge `templates/gitpr.release.*.md` à la première utilisation de `gitpr release` (couche CLI, tenant compte de la langue, n'écrase jamais ; sauté avec `--format json`).
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
* **Routage des Commandes :** Gère tous les flags et la sous-commande `release` (voir module 22).
* **Comportement par Défaut :** Exécuter `gitpr` sans flags ouvre la TUI du PR Publisher.
* **Flags (35 options Click à la racine) :**
  * `--init` 🆕 : Ouvre le wizard de configuration du SCM multi-forge (détection de la forge + validation du jeton).
  * `--no-suggest-reviewers` 🆕 : Désactive la suggestion de reviewers dans le flux de publication du PR.
  * `--no-publish` : Génère la description du PR et l'enregistre localement sans ouvrir l'éditeur interactif.
  * `--no-edit` : Saute entièrement la TUI — auto-commit, auto-push et publie directement sur GitHub.
  * `--base <branch>` : Remplace la branche cible du Pull Request.
  * `--plugins` : Liste les plugins globaux installés.
  * `--linter-setup` : Ouvre l'assistant interactif de configuration des linters externes.
  * `--version` : Affiche la version actuelle de GitPR (via `@click.version_option`).
* **Variables d'Environnement (39 clés dans `DEFAULT_CONFIG`) :** Famille SCM 🆕 (`GITPR_SCM_PROVIDER`, `GITPR_SCM_TOKEN`, `GITPR_SCM_TOKEN_ENCRYPTED`, `GITPR_SCM_BASE_URL`, `GITPR_SCM_ORGANIZATION`, `GITPR_SCM_PROJECT`, `GITPR_SCM_USERNAME`), famille reviewers 🆕 (`GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N`, `GITPR_REVIEWER_SUGGESTION_EXCLUDED`), plus `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE`, `GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`, `GITPR_AI_TIMEOUT` (défaut 180s dans cette fenêtre), `OUTPUT_FILE_NAME_*`, `GITPR_COAUTHOR` (lecture seule) et d'autres.
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique de la fonctionnalité avec un lien direct (tenant compte de la langue) vers GitHub. 🆕 Les sous-commandes ont leur propre `epilog=` : `gitpr release -h` se termine par "Full documentation:" + `get_doc_url("release-notes.md")` (paragraphe `\b` de Click pour que l'URL ne soit ré-enroulée sous aucune locale).
* **--lang :** Force la langue de l'interface pour l'exécution en cours sans persister le changement.
* **--provider :** Force le fournisseur d'IA (`gemini`, `deepseek`, `ollama`) pour l'exécution en cours.
* **--mcp :** Démarre le serveur MCP sur le transport stdio pour l'intégration avec les éditeurs — **12 outils annotés + 17 ressources + 7 prompts**.
* **--install :** Assistant guidé en 4 étapes qui télécharge des templates de skill, installe des Git Hooks, configure MCP dans les éditeurs et valide les clés API.
* **--metrics :** Système de télémétrie locale avec périmètre par dépôt : `--export`, `--purge`, `--dashboard`.
* **--status :** Liste les fichiers non commités catégorisés (new/modified/deleted) — rapide, sans IA, sans réseau.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)**

* **Interface Interactive Complète :** TUI construite avec Textual pour réviser, éditer et publier des Pull Requests directement dans le terminal.
* **6 Écrans Modaux :** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Reviewers Suggérés 🆕 :** Le flux de publication interroge la forge pour obtenir des reviewers suggérés et les propose dans la TUI ; sélection contrôlée par `--no-suggest-reviewers`, `GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N` et `GITPR_REVIEWER_SUGGESTION_EXCLUDED`.
* **Bindings :** F1 (Aide), F2 (Enregistrer .md local), F3 (Publier via GitHub API), Échap (Quitter).
* **Flux d'Auto-Commit :** Linter → message IA → confirmation → commit → push → publie le PR.
* **Vérification des Fichiers Unstaged :** Au démarrage, vérifie `git status --porcelain` et propose un modal pour sélectionner, sauter ou annuler.
* **Gestion de PR Existant :** Détecte les PRs ouverts pour la branche actuelle via l'API et propose push ou création d'un nouveau.
* **Auto-Upstream :** Détecte l'échec de `git push` dû à l'absence d'upstream et tente automatiquement `--set-upstream origin <branch>`.
* **Flux de Merge :** Après création/mise à jour du PR, propose une option de merge. Contrôlé par `GITPR_AUTO_MERGE`.

### **5. Module API GitHub (`src/github_api.py`)**

* **Shim Déprécié 🆕 :** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` et les autres fonctions délèguent désormais à `src/infrastructure/scm/github_provider.py` ; le module émet un `DeprecationWarning` et conserve les tuples legacy `(ok, data, status)` — aucun nouveau code ne doit l'importer.

### **6. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le git diff sans dépenser de quotas d'IA.
* **Règles YAML :** Lit le fichier local `.gitpr.linter.yml` (créé via `--skill`).
* **Plugins de Linter :** Règles supplémentaires chargées depuis `~/.gitpr/plugins/linter/*.yml`.
* **Pont de Linters Externes :** Exécute ESLint/PHPCS/Stylelint sur les lignes modifiées du diff, parser Checkstyle XML et croisement par ligne.
* **Rapport Consolidé :** `generate_linter_report_content()` consolide les erreurs regex + externes dans `.gitpr/reports/linter/` — généré uniquement en cas de violations.

### **7. Sécurité et Authentification (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Chiffrement :** Génère une clé maîtresse `secret.key` dans le dossier `~/.gitpr/`.
* **Protection des Jetons :** `encrypt_data` et `decrypt_data` pour protéger les clés API d'IA, les PAT GitHub et les jetons SCM des forges (`GITPR_SCM_TOKEN_ENCRYPTED`).
* **Validation Multi-Forge 🆕 :** `validate_or_request_scm_token(provider, repo_display)` — valide le jeton auprès de la forge configurée avec une boucle 401 → réauthentification préservant le brouillon ; le jeton GitHub legacy (`GITHUB_TOKEN_ENCRYPTED`) reste fonctionnel jusqu'à l'exécution de `--init`.

### **8. Auto-Updater (`src/updater.py`)**

* **Hot-Swap :** Vérifie sur l'API GitHub Releases la version la plus récente, télécharge le binaire compilé et le remplace sans casser l'exécution en cours (avec rollback).
* **Cache quotidien :** Évite les vérifications répétées le même jour.
* **Versionnage Centralisé :** `__version__` (**1.0.0**), `__lang_version__` (**v0.0.23**), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.

### **9. Interface de Chat Interactive (`src/ui/chat_app.py`)**

* **TUI Complète :** Construite avec Textual — historique des messages, saisie multi-ligne, barre d'état avec bindings visibles.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique de conversation persisté par branche, permettant la continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear` — raccourcis pour le pair programming.
* **Auto-Patching (F5), Rafraîchissement du Diff (F2), Export de Session (F6).**

### **10. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec prise en charge des placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système à la première exécution et l'enregistre dans `GITPR_LANG`.
* **5 Langues, 6 Dictionnaires :** en_us (défaut/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Fichiers Versionnés :** `__lang_version__` (**v0.0.23**) contrôle la mise à jour des packs de langue (`langs/*.json`) — chaîne de bumps v0.0.20 → v0.0.23 dans cette fenêtre.
* **Couverture :** **742 clés** de traduction dans chacun des 6 fichiers — **parité totale des key sets** (audit AST de 742 clés dans le code : 0 non traduites, 0 orphelines).
* **Titres de Changelog Traduisibles 🆕 :** Les 8 titres de section du changelog (Features, Bug Fixes, Breaking Changes etc.) ne sont plus des constantes opaques et sont désormais des littéraux `__()` résolus au runtime via le helper `_category_heading()` — ils suivent `--lang`/`set_lang` et sont visibles par l'extracteur AST.
* **Traductions Réelles 🆕 :** +195 clés depuis le rapport précédent (dont 48 issues de la tâche « next steps » du 2026-08-09, en traduction réelle dans les 6 dictionnaires, CRLF préservé).
* **Cache avec Indexation par Langue :** Les réponses IA en cache incluent la langue courante dans le keying MD5.

### **11. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA affichant des caractères braille avec des mots de « réflexion ».
* **263 entrées par langue :** Synchronisées entre les 5 langues.

### **12. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Prise en charge :** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Mode JSON & Paramètres Déterministes :** Sorties structurées avec `temperature=0.0` et `top_p=0.1` ; fallback automatique entre les fournisseurs configurés.

### **13. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Keying par hash MD5 du diff et du prompt, avec indexation par langue.
* **Télémétrie et Durée :** Persistance des champs `duration_ms` et `meta_raw` dans les fichiers de cache.
* **Lecture pour le Dashboard :** `scan_cache_files_for_dashboard()` lit tous les fichiers de cache récursivement.

### **14. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff actuel, Historique de la branche (`-ht`), et Archéologie par Blame (`-b`).
* **Publication Multi-Forge 🆕 :** F3 crée l'issue sur la forge **configurée** (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — pas seulement GitHub ; Azure DevOps lève `ScmNotSupportedError` (les Work Items dépendent du template de processus).
* **Map-Reduce pour les Issues :** Lorsque le contexte dépasse ~90k tokens, divise automatiquement en chunks et unifie les résultats.
* **Gestion du 401 :** Signalisation de réauthentification sans fermer l'application.

### **15. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Suit l'évolution et la paternité historique des extraits de code avec classification des commits (`ORIGIN` vs `REFACTORING`).
* **Métriques de Blame :** Événements journalisés via `log_blame_metric()` avec suivi de la profondeur et du nombre de commits analysés.

### **16. Serveur MCP et Invocation CLI Directe (`src/mcp_server.py`)**

* **12 Outils MCP Annotés :** Outils pour `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **17 Ressources + 7 Prompts Templatisés :** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release}` + `linter://config` + `prompt://list` + 7 prompts — 🆕 ressources de skill de release (et la famille correspondante dans les templates).
* **Invocation CLI Directe :** La commande `gitpr-mcp --tool <name> [--tool-args '<json>']` invoque n'importe quel outil MCP directement sans démarrer le serveur stdio JSON-RPC.
* **Isolation du Stdout Réel :** `_write_real_stdout()` écrit directement dans le `sys.__stdout__` original, garantissant du JSON pur sur stdout.
* **Silence Garanti 🆕 :** Sortie des outils muselée dans le flux serveur/CLI (les fuites de `print` qui corrompaient le flux JSON-RPC ont été éliminées — fix `681a7fa`, PR #146).
* **DNS Borné dans le Temps 🆕 :** La résolution DNS des opérations réseau est bornée dans le temps — aucun appel bloquant ne reste coincé en résolution (aux côtés du timeout dur de téléchargement OTA).
* **Offload de l'Event Loop :** Décorateur `_offload` (`anyio.to_thread.run_sync`) appliqué aux 12 outils — les handlers synchrones ne gèlent pas le serveur stdio.
* **Tests E2E :** `tests/test_mcp_server_e2e.py` démarre le vrai serveur comme subprocess et parle JSON-RPC stdio.

### **17. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`)**

* **Périmètre par Dépôt (Repo-Scope) :** Étiquette `📁 Repository: owner/repo` et filtrage strict par projet.
* **Scan Asynchrone avec Overlay :** Worker thread en arrière-plan avec widget `ProgressBar`.
* **Consolidation des Données :** `load_cache_token_summary()` ajoute les tokens du cache au totalisateur.
* **Export Local :** Enregistrement CSV/JSON dans `./.gitpr/metrics/export/`.

### **18. Système de Métriques et Télémétrie (`src/metrics.py`)**

* **Périmètre par Dépôt :** Tous les événements indexés par `repo_name`.
* **Événements de Hook, Linter et Blame :** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Export et Nettoyage :** `--metrics --export` (CSV/JSON) et `--metrics --purge` avec confirmation interactive.

### **19. Synchronisation des Git Hooks**

* **Versionnage Indépendant :** `__scripts_version__` (v0.0.3) contrôle la version des scripts de hook ; détection et mise à jour automatiques.
* **Tenant Compte de la Langue :** Télécharge les templates de hook correspondant à la langue configurée.
* **Skip de Merge-Source :** Le template `prepare-commit-msg` saute les sources `message|merge|squash|commit` — les commits générés par git préservent le message original.

### **20. Pont de Linters Externes et Assistant Interactif (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **Assistant `--linter-setup` :** Wizard interactif avec presets numérotés (PHP_CodeSniffer, ESLint, Stylelint) et injection du bloc `external_linters` dans `.gitpr.linter.yml`.
* **Presets Distants :** `templates/gitpr.linter-presets.json` servi depuis GitHub avec la chaîne de résolution local → téléchargement → stale → fallback embarqué.
* **TUI d'Erreurs du Linter :** `src/ui/linter_app.py` (Textual) affiche les erreurs critiques et les warnings ; en mode hook/quiet, imprime et fait `sys.exit(1)`.
* **Rapport Markdown :** Consolidé dans `.gitpr/reports/linter/` — uniquement en cas de violations.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`) 🆕**

* **Abstraction Unique (`ScmProvider` ABC) :** `base.py` définit le contrat (dataclasses `RepoRef`, `PullRequestDraft` etc. et `ScmProviderError(provider, http_status, message)` — `http_status` 0 = échec réseau) ; un provider concret par forge dans `github_provider.py`, `gitlab_provider.py`, `bitbucket_provider.py`, `azure_devops_provider.py`.
* **Registre et Factory :** `resolve_scm_provider()` sélectionne via `GITPR_SCM_PROVIDER` (défaut `github` — zéro migration, fallback du jeton GitHub legacy intact) ; `detect_provider_from_remote()` identifie la forge à partir de l'URL origin.
* **Adressage des Dépôts :** `parse_repo_ref(remote_url) -> RepoRef(raw, workspace, name, provider)` — workspace = owner GitHub / namespace GitLab (sous-groupes) / workspace Bitbucket / affichage `{org}/{project}` Azure.
* **Fail-Fast par Forge :** Azure DevOps exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` ; Bitbucket exige `GITPR_SCM_USERNAME` (App Password = Basic auth user+token) ; `create_issue` sur Azure lève `ScmNotSupportedError`.
* **Publication de Release 🆕 :** `provider.create_release()` utilisé par `gitpr release --publish` (GitHub crée le tag sur la branche par défaut ; GitLab exige que le tag existe) — `tests/scm/test_release_publish.py`.
* **Artefacts :** Glossaire + ADR-001 dans `docs/plans/` ; famille `docs/scm-multiforge.*.md` en 5 langues ; tests : 9 fichiers, 265 scénarios.

### **22. Sous-commande `gitpr release` — Changelog / Release Notes 🆕**

* **Flux :** `git log` entre `--since` (défaut : dernier tag atteignable, ou le premier commit) et `HEAD` → classification Conventional Commits → bump sémantique suggéré (`--version <x.y.z>` le remplace) → assemblage du changelog → résumé exécutif IA optionnel → *insertion en tête* de `CHANGELOG.md`. La génération locale est le défaut — rien n'est publié ni modifié sans demande.
* **Classifier (`src/commit_classifier.py`) :** Classe les commits par type Conventional Commits (feat/fix/refactor/docs/chore/etc.) avec un parser tolérant — la base du regroupement par section.
* **Builder avec Sections Traduisibles (`src/changelog_builder.py`) :** Les sections "✨ Features", "🐛 Bug Fixes", "⚠️ Breaking Changes", "Summary", "Contributors" etc. sont rendues via `__()` au runtime (elles suivent `--lang`) ; l'arithmétique de l'audit AST est close (742 = 742).
* **Bump Sémantique (`src/version_bump.py`) :** Suggère la prochaine version à partir des types classifiés (major pour breaking, minor pour feat, patch pour fix) et valide les cibles `x.y.z`.
* **Publication :** `--publish` crée la release sur la forge configurée (avec confirmation explicite) ; `--draft` la crée en brouillon (GitHub ; GitLab n'a pas de concept de brouillon) ; `--format markdown|json` pour une sortie structurée ; `--force` pour réécrire.
* **Template de Skill :** À la première utilisation, télécharge `templates/gitpr.release.*.md` (5 langues : en/pt_br/pt_pt/es_es/fr_fr) via `ensure_release_skill_template()` — n'écrase jamais ; éditable localement comme instruction système du résumé exécutif.
* **Aide Contextuelle :** `gitpr release -h` se termine par "Full documentation:" + un lien tenant compte de la langue vers `docs/release-notes.md` (epilog `\b`, pas de ré-enroulement de l'URL).
* **Tests :** 94 nouveaux scénarios — `test_release_cli.py` (4), `test_release_engine.py` (27), `test_changelog_builder.py` (15), `test_commit_classifier.py` (23), `test_version_bump.py` (17) + `tests/scm/test_release_publish.py` (8).
* **Artefacts :** Famille `docs/release-notes.*.md` (5 langues), spec dans `docs/plans/`, ADR-002 (sous-commande) et ADR-003 (modules plats), glossaire des release notes.

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 🆕 | Blame par plage de lignes dans un fichier |
| `tests/test_blame_metrics.py` | 7 | Métriques de blame : profondeur, commits, durée |
| `tests/test_changelog_builder.py` | 15 🆕 | Builder de changelog : sections, titres traduisibles, contributeurs |
| `tests/test_chat_backend.py` | 19 | Mémoire du chat, persistance, commandes slash |
| `tests/test_commit_classifier.py` | 23 🆕 | Classification Conventional Commits (types, parser tolérant) |
| `tests/test_config_suggest_reviewers.py` | 8 🆕 | Configuration des reviewers suggérés (clés et défauts) |
| `tests/test_core.py` | 39 | Flux principaux, git diff, génération de PR, timing, staging, co-paternité |
| `tests/test_diff_parser.py` | 15 🆕 | Parser de diff par lignes/hunks |
| `tests/test_external_linters.py` | 33 | Pont Checkstyle : parser XML, subprocess, croisement de diff, rapport |
| `tests/test_i18n.py` | 20 | Parité entre langues (742×6), clés manquantes/orphelines, identité |
| `tests/test_install_wizard.py` | 3 | Assistant interactif d'installation |
| `tests/test_issue_engine.py` | 4 | Brouillon d'issue structuré |
| `tests/test_linter_metrics.py` | 4 | Métriques de linter : erreurs, warnings, durée |
| `tests/test_main_suggest_reviewers.py` | 6 🆕 | Flag `--no-suggest-reviewers` dans la CLI et l'aide contextuelle |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompt MCP et fallback de langue |
| `tests/test_mcp_server.py` | 85 | Outils MCP, ressources, annotations, patching, CLI direct, offload |
| `tests/test_mcp_server_e2e.py` | 6 | Vrai serveur MCP via subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Collecte, export local, périmètre de dépôt, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Timeouts réseau/IA — **2 assertions obsolètes (600s)** |
| `tests/test_plugins.py` | 17 | Découverte de plugins, fusion de règles de linter, prompts MCP |
| `tests/test_pr_publish_app.py` | 42 | TUI du PR Publisher : écrans, flux, reviewers suggérés |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal d'erreur du linter : abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save et payload JSON |
| `tests/test_release_cli.py` | 4 🆕 | CLI de release : options, aide avec epilog documenté |
| `tests/test_release_engine.py` | 27 🆕 | Moteur de release : plage de commits, CHANGELOG, publication |
| `tests/test_reviewer_suggestion.py` | 15 🆕 | Logique de suggestion de reviewers (classement, exclusion, top-N) |
| `tests/test_skill_command.py` | 10 | Téléchargement et validation des templates de skill |
| `tests/test_skill_context.py` | 6 🆕 | `get_skill_context()` avec `quiet=True` (fallbacks, release) |
| `tests/test_smart_excludes.py` | 13 | Filtre pathspec intelligent |
| `tests/test_suggest_reviewers.py` | 14 🆕 | Reviewers suggérés dans le flux PR (intégration) |
| `tests/test_thinking_words.py` | 3 | Chargement et parsing avec le séparateur `;` |
| `tests/test_version_bump.py` | 17 🆕 | Bump sémantique : major/minor/patch, cibles et validation |
| `tests/scm/test_contract.py` | 38 🆕 | Contrat `ScmProvider` : signatures, dataclasses, erreurs |
| `tests/scm/test_github_provider.py` | 57 🆕 | Provider GitHub : REST, en-têtes, PRs, issues, releases |
| `tests/scm/test_gitlab_provider.py` | 41 🆕 | Provider GitLab : API v4, namespace, releases |
| `tests/scm/test_bitbucket_provider.py` | 39 🆕 | Provider Bitbucket : Basic auth, workspace |
| `tests/scm/test_azure_devops_provider.py` | 43 🆕 | Provider Azure DevOps : org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_factory.py` | 11 🆕 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 🆕 | Shim `github_api` déprécié → délègue au provider (remplace `test_github_api.py`) |
| `tests/scm/test_init_wizard.py` | 10 🆕 | Wizard `--init` : détection de forge, validation, persistance |
| `tests/scm/test_release_publish.py` | 8 🆕 | Publication de release par forge (GitHub/GitLab) |
| `tests/sync_i18n.py` | — | Script de vérification de couverture i18n (scaffold ; jamais exécuté) |

**Total :** 791 scénarios collectés dans 41 fichiers de test (32 à la racine + 9 dans `tests/scm/` ; +527 depuis le rapport précédent — inclut les 265 scénarios SCM, qui n'existaient pas auparavant comme suite). Exécution complète sur cette machine avec `GITPR_LANG=en_us` : **787 passed / 2 failed / 2 skipped / 15 subtests** en ~63s.

**Notes de qualité pour cette version :**
- **2 échecs réels (tests obsolètes) :** `test_net_timeouts.py` affirme encore le défaut de 600s pour `GITPR_AI_TIMEOUT`, mais le code est passé à **180s** dans cette fenêtre (fix `681a7fa`) — voir Prochaines Étapes.
- **4 échecs environnementaux de locale (machine pt-BR) :** `test_chat_backend::test_api_exception`, `test_main_suggest_reviewers::test_flag_appears_in_contextual_help` et `test_suggest_reviewers` ×2 affichent du texte pt_br depuis la copie OTA de `~/.gitpr/langs/` — ils passent intégralement avec `GITPR_LANG=en_us`. La suite entièrement verte du rapport précédent ne se répète pas dans cette version (2 régressions de test + sensibilité à la locale).
- `test_github_api.py` a été **supprimé** (le code legacy est devenu un shim) et remplacé par `tests/scm/test_github_api_shim.py` (18 scénarios).

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n :** **742 clés** de traduction dans chacun des 6 dictionnaires (+195 depuis le rapport précédent) avec **parité totale des key sets** — audit AST de 742 clés utilisées dans le code : 0 non traduites, 0 orphelines. Les 48 dernières clés (next steps de la famille release-notes : titres de changelog, chaînes d'aide, prompts IA avec un vrai `\n` préservé) ont été traduites avec un CRLF préservé au byte près.
* **Nouveaux Sujets 🆕 (les 3 en 5 langues) :**
  - `docs/release-notes.md` — famille de la sous-commande `gitpr release` (flux, versions, publication, résumé IA, template de skill)
  - `docs/scm-multiforge.md` — couche `ScmProvider` et wizard `--init` (4 forges, jetons, limitations par forge)
  - `docs/suggested-reviewers.md` — reviewers suggérés dans le flux PR (configuration et flags)
* **Sujets mis à jour dans cette fenêtre (tous re-synchronisés dans les 5 langues) :** `docs/auto-update.md`, `docs/github-ci-linter.md`, `docs/linter-regras-customizadas.md`, `docs/map-reduce-diff.md`, `docs/mcp-integration.md`, `docs/providers-ia.md`, `docs/skill-template.md`, `docs/smart-excludes.md` + `docs/ARCHITECTURE.md` (EN ; enregistrement des nouvelles familles).
* **Documentation en 5 langues :** **37 sujets canoniques** dans `docs/` — **32 avec couverture complète dans les 5 langues** (+3 depuis le rapport précédent) et 5 sujets partiels/PT-only (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers` — ce dernier désormais compté explicitement).
* **Skills locales de Claude Code :** `.claude/skills/` avec `status-report`, `implement-fixes`, `caveman-commit`, `new-feature` et `reports-to-memory` (aucune nouvelle dans cette fenêtre).
* **Memory Index :** `.claude/memory/MEMORY.md` avec 40 patterns (25 de projet, 9 de feedback, 3 de référence + 3 standalone).
* **Rapports de tâches :** `docs/claude-code/reports/develop_natan/` (**84** au total ; **+15** dans la fenêtre — SCM multi-forge, reviewers suggérés, docs des 3 familles, release notes/spec, skill du template de release, next steps i18n etc.) et `docs/gemini/reports/` (5 fichiers aujourd'hui ; +2 dans la fenêtre : `2026-08-28_add_mcp_tools_to_gemini_md.md` et `2026-09-03_update_repo_urls.md` — le compte du rapport précédent (8) incluait des fichiers supprimés lors de la restauration du répertoire du 2026-08-26).
* **Rapports de statut :** `docs/reports/` (12 rapports ; celui-ci est le 13e).
* **Plans de développement :** 80 fichiers dans `docs/plans/` (+21 dans la fenêtre — specs et ADRs pour SCM multi-forge, release notes, reviewers suggérés, enquêtes et la skill de release).

---

## **🔄 Pipeline de Distribution**

1. **PyPI :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases :** PyInstaller → `.exe` standalone → téléversement automatisé
3. **GitHub Actions :** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server :** Point d'entrée `gitpr-mcp` via `pyproject.toml`
5. **Templates et Langues OTA :** `templates/` et `langs/*.json` servis depuis GitHub (main) — le bump `v0.0.23` renouvelle les copies locales dans `~/.gitpr/langs/` une fois publié

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.12)**

| Domaine | v0.0.12 (précédent) | v0.0.13 (actuel) |
|------|-------------------|----------------|
| **Version GitPR** | 0.0.37 | **1.0.0** (via 0.0.38 dans la fenêtre ; CHANGELOG.md avec en-tête `[v0.1.0]`) |
| **Version Langue** | v0.0.20 | **v0.0.23** |
| **Version Scripts Hook** | v0.0.3 | **v0.0.3** |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Langues** | 5 langues, 6 dictionnaires | 5 langues, 6 dictionnaires |
| **Interface** | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI + LinterApp + `--linter-setup` | **+ wizard SCM `--init` + sous-commande `gitpr release` (CLI) + reviewers suggérés dans le PR Publisher** |
| **Outils MCP** | 12 outils (offload) | **12 outils (offload ; sortie muselée + DNS borné) — 17 ressources (auparavant 15)** |
| **Flags CLI** | 27 flags | **35 options à la racine (+ `--init`, `--no-suggest-reviewers` ; compte Click complet) + sous-commande `release` avec 6 options** |
| **Variables d'Environnement** | 23 vars | **39 clés dans `DEFAULT_CONFIG` (+ 7 SCM + 3 reviewers)** |
| **Linter** | Regex + pont Checkstyle (wizard/TUI/rapport) | Inchangé |
| **Messages de Commit** | Avec trailer `Co-Authored-By` (opt-out) | Inchangé |
| **i18n (clés par fichier)** | 547 × 6 (parité totale) | **742 × 6 (parité totale) — titres de changelog traduisibles** |
| **Documentation** | 33 sujets canoniques (29 complets + 4 partiels) | **37 sujets canoniques (32 complets + 5 partiels) — 3 nouvelles familles ×5, 8 mis à jour** |
| **Suite de Tests** | 264 scénarios (17 fichiers) | **791 scénarios collectés (41 fichiers : 32 + 9 SCM) — en_us : 787 passed / 2 failed (obsolètes) / 2 skipped** |
| **Commits depuis le rapport** | 17 commits | **10 commits** |
| **PRs mergés** | 8 PRs (#119–#135) + 2 PR_DESCs sans référence | **5 PRs (#146, #151, #153, #155, #159)** |
| **Memory Index** | 32 patterns | **40 patterns (25 projet / 9 feedback / 3 référence)** |
| **Rapports de tâches** | 65 claude-code, 8 gemini | **84 claude-code (+15 dans la fenêtre) et 5 gemini (+2 ; le compte précédent incluait des fichiers d'avant la restauration du 2026-08-26)** |
| **Plans de développement** | 59 | **80 (+21 dans la fenêtre — specs, ADRs et enquêtes)** |

---

## **🚧 Prochaines Étapes**

* **Fournisseur Anthropic Claude :** Support direct de l'API Claude (`claude-sonnet-5`).
* **Graphiques ASCII/Textual dans le Dashboard :** Ajouter des histogrammes de temps et des graphiques de tendance de tokens dans la TUI de métriques.
* **Pipeline de Release sur GitHub Actions :** Automatisation complète du build PyInstaller et de l'envoi des assets vers GitHub Releases (la génération de changelog est désormais locale via `gitpr release` — l'automatisation CI/CD manque encore).
* **Seed Local de `.gitpr/conf/` :** `--init` est devenu le wizard SCM dans cette fenêtre ; le seed des templates de configuration locale (smart-excludes, linter) reste en attente comme sous-commande propre ou étape du wizard.
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires.
* **Extracteur i18n dans `sync_i18n.py` :** Le regex tronque les littéraux à concaténation implicite (`__("a " "b")`) — migrer vers AST (la garde dans `test_i18n.py` utilise déjà AST et ne dépend pas du script).
* **Corriger les Tests de Timeout Obsolètes 🆕 :** `tests/test_net_timeouts.py` (lignes ~99/117/137/149) affirme un défaut de 600s, mais le code utilise 180s depuis `681a7fa` ; aligner aussi la docstring obsolète dans `config.py` (mentionne encore "default 600").
* **Réconcilier la Version du Projet 🆕 :** `CLAUDE.md` dit encore "Current version: 0.0.37" ; `__version__` est à 1.0.0 ; CHANGELOG.md enregistre `[v0.1.0] - 2026-09-07` (généré par la fonctionnalité elle-même). Définir une convention unique et mettre à jour CLAUDE.md.
* **Dette d'Index du README 🆕 :** Les puces des familles `suggested-reviewers` et `scm-multiforge` ne sont pas encore dans l'index (décision : uniquement `release-notes` cette fois).
* **Robustesse de Locale dans les Tests 🆕 :** 4 tests sont sensibles à la locale pt_br de la machine (copies OTA de `~/.gitpr/langs/`) — fixer `GITPR_LANG=en_us` dans le setup ou mocker `TRANSLATIONS` pour que la suite soit entièrement verte sur n'importe quelle machine/CI.

### ✅ Terminés dans cette fenêtre (2026-08-28 → 2026-09-08)

* ~~**Silence des outils MCP + DNS borné**~~ — fix `681a7fa` (PR #146) ; défaut `GITPR_AI_TIMEOUT` 600s → 180s.
* ~~**URLs de dépôt standardisées + prompts d'issue localisés**~~ — `fa4bac1` (PR #151).
* ~~**SCM multi-forge complet**~~ — providers, factory, wizard `--init`, shim déprécié, docs ×5 et la suite `tests/scm/` (PR #153).
* ~~**Reviewers suggérés dans le flux PR**~~ — flag, config, tests et docs ×5 (PR #155).
* ~~**Sous-commande `gitpr release`**~~ — modules, tests, templates de skill ×5, docs ×5, ADRs et spec (PR #159, commit `b0e5d92`).
* ~~**Next steps de la famille release-notes**~~ — aide contextuelle `gitpr release -h` → docs ; 48 clés i18n traduites (742 × 6) ; titres de changelog traduisibles ; puce de la famille dans l'index du README ×5 — voir [rapport de tâche](../claude-code/reports/develop_natan/2026-09-08_release_notes_next_steps.md).

---

**Rapport généré le :** 2026-09-08  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
