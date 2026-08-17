# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.11 (2026-08-15)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.11) :**
- **Correction de la Sélection et des Erreurs de Staging (`stage_files`) :** La TUI de staging lit désormais la sélection réelle de `SelectionList.selected` (toggles individuels respectés) et `stage_files()` renvoie `(success, error_message)` — les échecs de `git add` affichent l'erreur git réelle au lieu d'un faux message de succès. Le staging ne s'exécute plus qu'une seule fois par flux (auparavant dupliqué entre le modal et `check_unstaged_files`).
- **Skip du Message IA dans les Commits Générés par Git :** Les hooks `prepare-commit-msg` (5 variantes de langue) sautent désormais toutes les sources générées par git (`merge`, `squash`, `amend`, `commit` — avant, seul `message`), avec une vérification belt-and-braces de `.git/MERGE_HEAD`. `git pull`/`git merge` ne corrompent plus le `.git/MERGE_MSG` avec un message IA. Nouveau helper `is_merge_in_progress()` dans le core et garde en mode hook. `__scripts_version__` → v0.0.2 avec auto-sync des hooks.
- **Traductions des Statuts de Fichier :** Les labels de statut (« Modified », « Deleted », « New ») traduits dans les packs es, es_es, fr, fr_fr, pt_br et pt_pt — la couverture pt_BR est passée à 507 clés.
- **Documentation Multilingue Étendue et Synchronisée :** `docs/pr-descricao-padrao.md` réécrit en EN canonique + 4 locales avec une section de publication (modes d'exécution, raccourcis TUI, PAT) ; `docs/mcp-integration.md` synchronisé dans les 5 langues (2 outils manquants dans le tableau + nouvelle sous-section sur les ressources de prompt) ; `docs/git-hooks-locais.md` documente le skip de merge-source dans les 5 langues.
- **Nouveau Template MCP :** `templates/gitpr.mcp-jsonrpc-calls.md` — référence des appels JSON-RPC pour les outils MCP.

- **Version actuelle :** 0.0.36
- **Version des dictionnaires de langue :** v0.0.13
- **Version des scripts de hook :** v0.0.2
- **Publication :** PyPI (`pip install gitpr-cli`) + GitHub Releases (binaire standalone)
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licence :** LGPL-2.1
- **Langues supportées :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **Framework CLI :** Click (pour commandes, flags et formatage terminal).
* **UI/Terminal :** Textual — TUI (Text User Interface) pour chat interactif, édition d'issues, écran d'aide, tableau de bord de métriques et PR Publisher.
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API et tokens GitHub.
* **Configuration :** `python-dotenv`, `pyyaml` (pour le linter statique).
* **Fournisseurs IA :** Intégration via SDK officiel Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), et OpenAI SDK (`Ollama` local).
* **API GitHub :** `requests` (API REST via PAT) — module `src/github_api.py` avec `create_pull_request()`, `update_pull_request()`, `merge_pull_request()`.
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK officiel Anthropic pour Model Context Protocol) — 12 outils annotés, 15 ressources, 7 prompts.
* **Tests :** Pytest + `unittest.mock` (13 fichiers de test, 214 scénarios).
* **Packaging :** PyInstaller (binaire standalone) + setuptools/build (PyPI).
* **CI/CD :** GitHub Actions (`pr-review.yml`) + `action.yml` pour l'exécution dans les pipelines.

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec le LLM en exigeant une sortie strictement JSON.
* **Map-Reduce (Diffs Géants) :** Lorsque le diff dépasse ~90k tokens, il le divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce). Prend en charge les PR, les commits et les Issues.
* **Tokenizer Local :** `tokenizer.json` pour une estimation précise des tokens avant l'envoi à l'IA.
* **Estimation des Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()` avec repli sur le tokenizer local.
* **Optimisation Native Git :** Flags `-U1`, `-w`, `-M`, `-B` sur les commandes `get_git_diff` et `get_git_full_diff` pour réduire le contexte inutile.
* **Pre-Save (`--pre-save`) :** Flag de débogage caché qui sauvegarde le payload complet (instruction système + prompt) au format JSON avant chaque appel à l'IA.
* **Smart Excludes à Deux Couches :** Filtre pathspec intelligent avec une couche globale (`~/.gitpr/conf/`) + une couche locale au projet (`./.gitpr/conf/`). Fusion à l'exécution (union, dédupliquée). Amorçage automatique du fichier local au premier lancement. Support de 3 variables d'environnement (`GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`).
* **Métriques avec Suivi Temporel :** Injection de `log_command_metric()` dans tous les flux avec transmission de la durée en millisecondes (`duration_ms`), avec imports différés.
* **Résolution Centralisée de la Sortie :** Fonction `resolve_output_path()` qui centralise la logique des répertoires de sortie — par défaut dans `.gitpr/reports/{type}/` avec repli sur les chemins personnalisés du `.env`.
* **Détection de Merge en Cours 🆕 :** Helper `is_merge_in_progress()` (vérifie `git rev-parse -q --verify MERGE_HEAD`, silencieux et worktree-safe) — utilisé comme défense en profondeur contre les anciens hooks qui appellent la CLI pendant un merge.
* **Staging avec Erreur Réelle 🆕 :** `stage_files()` renvoie désormais le tuple `(success, error_message)`, capturant le stderr/stdout de `git add` en cas d'échec — l'erreur git réelle parvient à l'utilisateur au lieu d'être avalée.

### **2. Système Global de Plugins (`src/plugins.py`)**

* **Architecture de Plugins :** Système d'extensibilité qui charge les plugins depuis le répertoire `~/.gitpr/plugins/`, en les appliquant à **tous les projets**.
* **Plugins Linter (`linter/`) :** Fichiers `.yml` avec des règles regex supplémentaires fusionnées avec le `.gitpr.linter.yml` local.
* **Plugins de Prompts MCP (`prompts/`) :** Fichiers `.md` qui étendent le contexte système avec des instructions spécifiques.
* **Fermetures Factory :** Fonctions `get_linter_plugins` et `get_prompt_plugins` avec fermetures pour isoler l'état entre les sessions.
* **Commande `--plugins` :** Liste tous les plugins globaux installés avec leurs types et chemins.
* **Documentation Multilingue :** `docs/plugins-system.md` en 5 langues (EN, PT-BR, PT-PT, ES, FR).

### **3. Interface CLI et Configuration (`src/main.py` et `src/config.py`)**

* **Configuration Initiale :** Détecte le premier lancement, crée le dossier `~/.gitpr/` et demande interactivement les clés API, les préférences et la langue.
* **Routage des Commandes :** Gère tous les flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--publish`, `--no-publish`, `--no-edit`, `--base`, `--lang`, `--provider`, `--pre-save`, `--plugins`).
* **Comportement Par Défaut :** Exécuter `gitpr` sans flags ouvre la TUI du PR Publisher.
* **Flags :**
  * `--publish` : Ouvre la TUI interactive pour examiner, éditer et publier le PR.
  * `--no-publish` : Génère la description du PR et la sauvegarde localement sans ouvrir l'éditeur interactif.
  * `--no-edit` : Saute entièrement la TUI — auto-commit (avec validation du linter), auto-push et publication directe sur GitHub.
  * `--base <branch>` : Remplace la branche cible de la Pull Request.
  * `--plugins` : Liste les plugins globaux installés.
  * `--version` : Affiche la version actuelle de GitPR (via `@click.version_option`).
* **Variables d'Environnement :** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE`, `GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`.
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique de la fonctionnalité avec un lien direct (sensible à la langue) vers GitHub.
* **--lang :** Force la langue de l'interface pour l'exécution actuelle sans persister le changement.
* **--provider :** Force le fournisseur IA (`gemini`, `deepseek`, `ollama`) pour l'exécution actuelle.
* **--mcp :** Démarre le serveur MCP sur transport stdio pour l'intégration avec les éditeurs — **12 outils annotés + 15 ressources + 7 prompts**.
* **--install :** Assistant guidé en 4 étapes qui télécharge les templates de skills, installe les Git Hooks, configure le MCP dans les éditeurs et valide les clés API.
* **--metrics :** Système de télémétrie locale avec portée par dépôt : `--export`, `--purge`, `--dashboard` (TUI interactive avec analyse du cache).
* **--status :** Liste les fichiers non commités classés (nouveaux/modifiés/supprimés) — rapide, sans IA, sans réseau.
* **Garde de Merge en Mode Hook 🆕 :** Dans le flux de commit en mode hook, si `is_merge_in_progress()` renvoie True, l'exécution se termine silencieusement avec exit 0 avant tout diff ou appel IA.
* **Feedback Réel de Staging 🆕 :** `check_unstaged_files()` vérifie le résultat de `stage_files()` aux 3 points d'appel (résultat de la TUI, auto-stage de pr/issue, auto-stage de commit) et affiche « ❌ Failed to stage files: {erreur git réelle} » en cas d'échec.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)**

* **Interface Interactive Complète :** TUI construite avec Textual pour examiner, éditer et publier des Pull Requests directement dans le terminal.
* **6 Écrans Modaux :** `CommitConfirmScreen`, `FileStageScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Modal Amélioré des Fichiers Non-Stagés :** Liste de fichiers avec hauteur fixe (`height: 6`) et défilement vertical interne.
* **Bindings :** F1 (Help), F2 (Enregistrer le .md local), F3 (Publier via l'API GitHub), Esc (Quitter).
* **Flux d'Auto-Commit :** Linter → message IA → confirmation → commit → push → publication du PR.
* **Vérification des Fichiers Non-Stagés :** Au démarrage, vérifie `git status --porcelain` et propose un modal pour sélectionner, ignorer ou annuler.
* **Gestion d'un PR Existant :** Détecte les PR ouverts pour la branche actuelle via l'API GitHub et propose de pousser ou d'en créer un nouveau.
* **Auto-Upstream :** Détecte l'échec de `git push` dû à un upstream manquant et réessaie automatiquement avec `--set-upstream origin <branch>`.
* **Détection de « Nothing to commit » :** Traite `git commit` sans changements comme un succès.
* **Flux de Merge :** Après la création/mise à jour du PR, propose une option de merge. Contrôlé par `GITPR_AUTO_MERGE`.
* **Gestion des Erreurs de Merge :** Callbacks `_on_merge_success` / `_on_merge_failure` avec un modal d'erreur pour HTTP 405 (conflits) et retour visuel post-TUI.
* **Sélection Réelle des Fichiers 🆕 :** `StageFilesScreen.btn_stage` lit la sélection directement depuis `SelectionList.selected` — les toggles individuels de ligne (clic/Entrée) sont désormais respectés ; suppression du dictionnaire manuel `_selected` qui se désynchronisait et du `git add` dupliqué dans la TUI (staging unique dans `main.py`).

### **5. Module API GitHub (`src/github_api.py`)**

* **Fonctions Partagées :** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` — encapsulant les appels REST à l'API GitHub v3.
* **Authentification PAT :** Token d'accès personnel validé avec `GET /user` avant les opérations.
* **Réutilisation :** Fonctions utilisées à la fois par la TUI des PR et par la TUI des issues.

### **6. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le diff git sans dépenser de quota IA.
* **Règles YAML :** Lit le fichier local `.gitpr.linter.yml` (créé via `--skill`). Prend en charge les regex de validation, l'ignorance des commentaires et l'ignorance de répertoires spécifiques.
* **Plugins Linter :** Règles supplémentaires chargées depuis `~/.gitpr/plugins/linter/*.yml` et fusionnées avec les règles locales.
* **Template Multilingue :** Templates de linter disponibles en 5 langues.
* **Intégration à l'Auto-Commit :** S'exécute automatiquement avant le commit dans le flux de publication des PR.

### **7. Sécurité et Authentification (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cryptographie :** Génère une clé maîtresse `secret.key` dans le dossier `~/.gitpr/`.
* **Protection des Tokens :** `encrypt_data` et `decrypt_data` pour protéger les clés API IA et le PAT GitHub.
* **Validation du Token GitHub :** `validate_github_token()` avec un appel léger (`GET /user`).
* **Flux de Ré-authentification Automatique :** Si le token expire pendant `gitpr -is`, capture le 401, demande un nouveau token et relance la TUI en préservant le brouillon.

### **8. Auto-Updater (`src/updater.py`)**

* **Hot-Swap :** Vérifie la dernière version via l'API GitHub Releases, télécharge le binaire compilé et le remplace sans interrompre l'exécution en cours (avec rollback).
* **Cache Quotidien :** Évite les vérifications répétées le même jour.
* **Vérification de Connexion :** Socket `8.8.8.8:53` avant toute opération réseau.
* **Versionnage Centralisé :** `__version__` (0.0.36), `__lang_version__` (v0.0.13), `__scripts_version__` (v0.0.2), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`.

### **9. Interface de Chat Interactive (`src/ui/chat_app.py`)**

* **TUI Complète :** Construite avec Textual — historique de messages, saisie multiligne, barre de statut avec bindings visibles.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique de conversation persisté par branche, permettant la continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear` — raccourcis pour la programmation en binôme.
* **Auto-Patching (F5) :** Extrait les blocs de code suggérés par l'IA et les exporte vers un fichier de patch.
* **Rafraîchissement du Diff (F2) :** Recharge le `git diff` actuel sans redémarrer la session.
* **Export de Session (F6) :** Sauvegarde l'historique complet du chat pour la documentation.

### **10. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec prise en charge de placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système d'exploitation au premier lancement et la sauvegarde dans `GITPR_LANG`.
* **5 Langues :** en_us (par défaut/repli), pt_br, pt_pt, es_es, fr_fr.
* **Fichiers Versionnés :** `__lang_version__` (v0.0.13) contrôle la mise à jour des packs de langue (`langs/*.json`).
* **Couverture :** 507 clés de traduction en pt_BR.
* **Traductions des Statuts de Fichier 🆕 :** Clés « Modified », « Deleted » et « New » traduites dans les 6 packs non anglais (es, es_es, fr, fr_fr, pt_br, pt_pt).
* **Cache avec Indexation par Langue :** Les réponses IA mises en cache incluent la langue courante dans le hachage MD5.
* **Script de Synchronisation :** `tests/sync_i18n.py` pour la détection automatique des clés orphelines.

### **11. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA affichant des caractères braille avec des mots de « réflexion ».
* **Délimiteur :** Séparateur de phrases par point-virgule (`;`), compatible avec les phrases complexes contenant des virgules.
* **Vitesse Adaptative & Flickering :** Animation de découverte de caractères adaptée aux phrases longues et utilisation de l'ANSI `\033[K` pour éviter les artefacts visuels dans le terminal.
* **263 entrées par langue :** Synchronisées entre les 5 langues.

### **12. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Pris en Charge :** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Mesure de Durée :** Injection de `duration_ms` (chronométrage de haute précision via `time.perf_counter()`) dans `meta_raw` et `_telemetry_meta`.
* **Mode JSON & Paramètres Déterministes :** Sorties structurées avec `temperature=0.0` et `top_p=0.1`.

### **13. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Clé basée sur le hachage MD5 du diff et du prompt.
* **Indexation par Langue :** Le champ `lang` a été ajouté au calcul de la clé du cache.
* **Télémétrie et Durée :** Persistance des champs `duration_ms` et `meta_raw` dans les fichiers de cache.
* **Lecture pour le Tableau de Bord :** `scan_cache_files_for_dashboard()` lit tous les fichiers de cache de manière récursive.

### **14. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff actuel, historique de la branche (`-ht`) et archéologie par Blame (`-b`).
* **Map-Reduce pour les Issues :** Lorsque le contexte dépasse ~90k tokens, il se divise automatiquement en lots et unifie les résultats.
* **TUI Interactive :** Édition de brouillons, raccourci F2 (enregistrer localement), F3 (publier sur GitHub) et F1 (aide).
* **Traitement du 401 :** Signalisation de réauthentification sans fermeture de l'application.

### **15. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Suit l'évolution historique et la paternité des extraits de code avec classification des commits (`ORIGIN` vs `REFACTORING`).
* **Métriques Blame :** Événements enregistrés via `log_blame_metric()` avec suivi de la profondeur et du nombre de commits analysés.

### **16. Serveur MCP et Invocation CLI Directe (`src/mcp_server.py`)**

* **12 Outils MCP Annotés :** Outils pour `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **15 Ressources + 7 Prompts Templatisés :** 35 fichiers de template dans `templates/gitpr.prompt.*.md`.
* **Invocation CLI Directe :** La commande `gitpr-mcp --tool <name> [--tool-args '<json>']` invoque n'importe quel outil MCP directement sans démarrer le serveur stdio JSON-RPC.
* **Pattern Registry :** `_TOOL_FUNCS` mappe nom d'outil → callable ; `_get_tool_registry()` fusionne avec les métadonnées du catalogue.
* **Isolation Réelle du Stdout :** `_write_real_stdout()` écrit directement sur le `sys.__stdout__` original (sauvegardé avant le monkey-patching), garantissant un JSON pur sur stdout.
* **Liste des Outils :** `gitpr-mcp --tool` (sans nom) liste les 12 outils disponibles avec leurs signatures de paramètres.
* **Chargement Automatique du .env :** Les clés API sont automatiquement disponibles en mode CLI.
* **Nouveau Template JSON-RPC 🆕 :** `templates/gitpr.mcp-jsonrpc-calls.md` — référence des appels JSON-RPC pour les outils MCP.
* **Installeur Automatique :** Configuration des éditeurs pris en charge (VS Code, Cursor, Claude Code, Claude Desktop, Zed) avec fusion JSON intelligente.

### **17. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`)**

* **Portée par Dépôt (Repo-Scope) :** Étiquette `📁 Repository: owner/repo` et filtrage strict des événements et données de cache par projet.
* **Analyse Asynchrone avec Overlay :** Thread worker en arrière-plan avec widget `ProgressBar`.
* **Consolidation des Données :** `load_cache_token_summary()` additionne les tokens du cache au totalisateur.
* **Contrôle de l'État du Cache :** Fichier de registre dans `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Exportation Locale :** Sauvegarde CSV/JSON dans `./.gitpr/metrics/export/`.

### **18. Système de Métriques et Télémétrie (`src/metrics.py`)**

* **Portée par Dépôt :** Tous les événements indexés par `repo_name`.
* **Nouveaux Événements :** Événements pour le listage des fichiers non-stagés et l'export de télémétrie.
* **Événements de Hook :** `log_hook_event()` pour les hooks Git (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge).
* **Événements Linter et Blame :** `log_linter_metric()` et `log_blame_metric()`.
* **Exportation Locale :** `--metrics --export` génère CSV et JSON dans `./.gitpr/metrics/export/` avec filtre par dépôt.
* **Nettoyage :** `--metrics --purge` supprime tous les fichiers de métriques locaux avec confirmation interactive.

### **19. Synchronisation des Git Hooks**

* **Versionnage Indépendant :** `__scripts_version__` (v0.0.2) contrôle la version des scripts de hook.
* **Détection Automatique :** Compare la version locale avec la plus récente et met à jour automatiquement.
* **Sensible à la Langue :** Télécharge les templates de hook correspondant à la langue configurée.
* **Skip de Merge-Source 🆕 :** Le template `prepare-commit-msg` (5 variantes de langue) utilise désormais un case POSIX qui saute les sources `message|merge|squash|commit` et vérifie `.git/MERGE_HEAD` en belt-and-braces — les commits générés par git (`git pull`, `git merge`, `--amend`, `-c`/`-C`, `--squash`) préservent le message git d'origine.

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|------------------|----------|------|
| `tests/test_core.py` | 32+ 🆕 | Flux principaux, git diff, génération de PR, timing, merge en cours, staging |
| `tests/test_chat_backend.py` | 30+ | Mémoire de chat, persistance, commandes slash |
| `tests/test_plugins.py` | 17 | Découverte de plugins, fusion des règles linter, prompts MCP |
| `tests/test_mcp_server.py` | 75+ | Outils MCP, ressources, annotations, patching, CLI directe |
| `tests/test_metrics.py` | 36+ | Collecte, export local, portée repo, cache token summary, duration_ms |
| `tests/test_smart_excludes.py` | 14+ | Filtre pathspec intelligent |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompts MCP et repli de langue |
| `tests/test_blame_metrics.py` | 10+ | Métriques de blame : profondeur, commits, durée |
| `tests/test_linter_metrics.py` | 8+ | Métriques de linter : erreurs, avertissements, durée |
| `tests/test_thinking_words.py` | 9+ | Chargement et parsing avec séparateur `;` |
| `tests/test_skill_command.py` | 5+ | Téléchargement et validation des templates de skills |
| `tests/test_install_wizard.py` | 5+ | Assistant d'installation interactif |
| `tests/test_pre_save.py` | 3+ | Flag --pre-save et payload JSON |
| `tests/sync_i18n.py` | — | Script de vérification de la couverture i18n (clés orphelines) |

**Total :** 214 scénarios de tests automatisés réussis (13 fichiers de test). Exécution complète vérifiée dans cette version : **214/214 passed**. Nouveaux tests : `TestIsMergeInProgress` (3 cas de merge en cours) et `TestStageFiles` (4 cas : liste vide, succès, échec avec erreur git, exception). L'échec connu signalé dans la v0.0.10 dans `test_metrics.py::test_app_skips_export_and_config_files` ne s'est pas reproduit lors de cette exécution.

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n :** 507 clés de traduction en pt_BR (+4 : labels de statut de fichier et message d'erreur de staging).
* **Documents Mis à Jour 🆕 (tous en 5 langues) :**
  - `docs/pr-descricao-padrao.md` — réécrit en EN canonique (convention docs multilingue) + 4 locales ; section de publication avec 3 modes d'exécution (`gitpr`, `--no-publish`, `--no-edit`), raccourcis TUI (F1/F2/F3/Esc), exigence de PAT et résolution de la branche de base ; chemin de sortie corrigé vers `.gitpr/reports/pr_desc/`
  - `docs/mcp-integration.md` — synchronisé avec l'implémentation : 2 outils manquants dans le tableau (`list_unstaged_files`, `analyze_unstaged_diff`), nouvelle sous-section de ressources de prompt (`prompt://*`, plugins, prompts intégrés) et section Claude Code dans les 4 traductions
  - `docs/git-hooks-locais.md` — documente le skip de merge-source du hook `prepare-commit-msg` (merge/squash/amend préservent le message git)
* **Documentation en 5 langues :** 34 sujets canoniques dans `docs/` (28 avec une couverture complète dans les 5 langues).
* **Memory Index :** `.claude/memory/MEMORY.md` avec 27 patterns en 3 catégories (20 de projet, 3 de référence, 4 de feedback).
* **Rapports de tâches :** `docs/claude-code/reports/` (+4 nouveaux : pr-descricao-padrao multilingue, fix prepare-commit-msg merge skip, unstaged modal stage fix, MCP docs sync) et `docs/gemini/reports/`.
* **Rapports de statut :** `docs/reports/` (11 rapports de statut).
* **Plans de développement :** 11+ plans documentés dans `docs/plans/`.

---

## **🔄 Pipeline de Distribution**

1. **PyPI :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases :** PyInstaller → `.exe` standalone → upload automatisé
3. **GitHub Actions :** Workflow `pr-review.yml` + `action.yml`
4. **Serveur MCP :** Point d'entrée `gitpr-mcp` via `pyproject.toml`

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.10)**

| Domaine | v0.0.10 (précédent) | v0.0.11 (actuel) |
|------|-------------------|----------------|
| **Version GitPR** | 0.0.35 | **0.0.36** |
| **Version des Langues** | v0.0.13 | v0.0.13 |
| **Version des Scripts de Hook** | v0.0.1 | **v0.0.2** |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Langues** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI |
| **Outils MCP** | 12 outils | 12 outils |
| **Flags CLI** | 26 flags | 26 flags |
| **Variables d'Environnement** | 16 vars | 16 vars |
| **Staging de Fichiers** | Sélection via dictionnaire manuel (désynchronisé) + échecs de `git add` silencieux + staging dupliqué | **Sélection réelle (`SelectionList.selected`) + erreur git réelle affichée + staging unique par flux** |
| **Hooks de Commit** | L'IA sautait uniquement la source `message` | **L'IA saute `message/merge/squash/commit` + vérification de `.git/MERGE_HEAD` + garde `is_merge_in_progress()`** |
| **i18n (clés pt_BR)** | 503 | **507 (+ labels de statut de fichier et erreur de staging)** |
| **Documentation** | 37 sujets | **34 sujets canoniques dans `docs/` (28 avec 5 langues complètes) — 3 sujets mis à jour (pr-descricao-padrao, mcp-integration, git-hooks-locais)** |
| **Suite de Tests** | 207 scénarios (13 fichiers) | **214 scénarios (13 fichiers, +7 : merge en cours et staging)** |
| **Commits depuis le rapport** | 2 commits | **4 commits (i18n status, merge skip, docs hooks, staging fix)** |
| **PR Fusionnés** | 2 PR (#107, #110) | **2 PR (#111, #114)** |
| **Memory Index** | 20 patterns | **27 patterns en 3 catégories (projet/référence/feedback)** |

---

## **🚧 Prochaines Étapes**

* **Tests pour le PR Publisher :** Couverture de tests unitaires et d'intégration pour le flux de publication des PR (`pr_publish_app.py`, `github_api.py`).
* **Tests d'intégration de bout en bout pour MCP :** Validation des appels d'outils et des prompts via un client stdio simulé.
* **Fournisseur Anthropic Claude :** Support direct de l'API Claude (`claude-sonnet-5`).
* **Graphiques ASCII/Textual dans le Dashboard :** Ajouter des histogrammes de temps et des graphiques de tendance de tokens dans la TUI de métriques.
* **Pipeline de Release dans GitHub Actions :** Automatisation complète du build PyInstaller et envoi des assets vers GitHub Releases.
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires.
* **Commande locale `--init` :** Amorçage de `.gitpr/conf/` avec des templates de configuration locale (smart-excludes, linter, etc.).
* **Traductions de staging en attente dans les autres langues :** Les nouvelles clés d'erreur de staging existent en pt_br — à propager vers pt_pt, es_es et fr_fr lors du prochain changement de version de langue.
* **Dead code dans la TUI :** La classe brouillon `FileStageScreen` duplique `StageFilesScreen` — à intégrer ou à supprimer.
* **Ajustements de la documentation MCP :** L'aide de `gitpr-mcp --install` omet `claude-code` dans la liste des éditeurs ; documenter l'alias caché `gitpr --mcp` dans `mcp-integration.md`.

---

**Rapport généré le :** 2026-08-15  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
