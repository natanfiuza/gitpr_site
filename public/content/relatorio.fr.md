# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.33 (2026-08-09)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.8) :**
- **PR Publisher TUI (`gitpr` par défaut) :** Interface interactive en terminal pour examiner, éditer et publier des Pull Requests directement sur GitHub via l'API REST. Inclut l'édition du titre, du corps et de la branche de base avec les bindings F1 (Help), F2 (Enregistrer localement), F3 (Publier) et Esc (Quitter). Flux complet avec 6 écrans modaux pour le commit, le staging et la progression.
- **Flux d'Auto-Commit Intelligent :** En utilisant `--no-edit` ou en publiant avec F3 avec des changements non commités, GitPR exécute le linter statique, génère un message de commit par IA (Conventional Commits), confirme avec l'utilisateur et exécute `git commit` avant de publier le PR.
- **Gestion des Fichiers Non-Stagés :** Au démarrage, GitPR vérifie les fichiers non-stagés et propose un modal TUI (`StageFilesApp`) pour sélectionner, ignorer ou annuler avant la génération du PR.
- **Traitement d'un PR Existant :** Lorsqu'un PR existe déjà pour la branche actuelle, la TUI propose de pousser vers le PR existant (en mettant à jour le corps via PATCH) ou d'en créer un nouveau.
- **Flux de Merge :** Après la création ou la mise à jour du PR, GitPR peut optionnellement effectuer le merge. Contrôlé par la variable d'environnement `GITPR_AUTO_MERGE`.
- **Auto-Upstream au Push :** Lorsque `git push` échoue par absence d'upstream, GitPR réessaie automatiquement avec `--set-upstream origin <branch>`.
- **Détection de « Nothing to commit » :** Les échecs de commit dus à l'absence de changements stagés sont traités comme un succès — le flux continue vers la publication du PR.
- **Centralisation de la Sortie :** Tous les fichiers générés utilisent désormais `.gitpr/reports/` organisés par type (`pr_desc/`, `review/`, `full_review/`, `file_review/`, `blame/`, `issue/`). Les chemins personnalisés dans le `.env` sont respectés pour la compatibilité.
- **6 Nouvelles Variables d'Environnement :** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE` — contrôle granulaire du flux de publication.
- **Module API GitHub (`src/github_api.py`) :** Fonctions partagées pour `create_pull_request()`, `update_pull_request()` et `merge_pull_request()` via l'API REST.
- **Documentation Technique Multilingue :** `docs/pull-request-publication.md` en 5 langues (EN, PT-BR, PT-PT, ES, FR) avec couverture complète du flux de PR.
- **CHANGELOG.md :** Historique complet des versions de v0.0.1 à v0.0.33 au format Keep a Changelog, alimenté à partir des rapports de statut dans `docs/reports/`.

- **Version actuelle :** 0.0.33
- **Version des dictionnaires de langue :** v0.0.11
- **Version des scripts de hook :** v0.0.1
- **Publication :** PyPI (`pip install gitpr-cli`) + GitHub Releases (binaire standalone)
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licence :** LGPL-2.1
- **Langues supportées :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **Framework CLI :** Click (pour commandes, flags et formatage terminal).
* **UI/Terminal :** Textual — TUI (Text User Interface) pour chat interactif, édition d'issues, écran d'aide, tableau de bord de métriques et **PR Publisher** 🆕.
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API et tokens GitHub.
* **Configuration :** `python-dotenv`, `pyyaml` (pour le linter statique).
* **Fournisseurs IA :** Intégration via SDK officiel Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), et OpenAI SDK (`Ollama` local).
* **API GitHub :** `requests` (API REST via PAT) — **utilisation étendue avec le nouveau module `github_api.py`** 🆕.
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK officiel Anthropic pour Model Context Protocol) — Tool Annotations, Prompts avec templates et ressources prompt://.
* **Tests :** Pytest + `unittest.mock` (12 fichiers de test, 131 scénarios).
* **Empaquetage :** PyInstaller (binaire standalone) + setuptools/build (PyPI).
* **CI/CD :** GitHub Actions (`pr-review.yml`) + `action.yml` pour exécution dans les pipelines.

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec la LLM en demandant un retour strictement en JSON.
* **Map-Reduce (Diffs Géants) :** Lorsque le diff dépasse ~90k tokens, il se divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce) en conservant le ton de l'architecture.
* **Estimation de Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()`.
* **Optimisation Native de Git :** Flags `-U1`, `-w`, `-M`, `-B` dans les commandes `get_git_diff` et `get_git_full_diff` pour réduire le contexte inutile.
* **Pre-Save (`--pre-save`) :** Flag masqué de débogage qui sauvegarde le payload complet (system instruction + prompt) en JSON avant chaque appel à l'IA.
* **Smart Excludes :** Filtre de pathspec intelligent (`gitpr.smart-excludes.json`) distant — téléchargé depuis GitHub et mis à jour automatiquement avec versionnage (`SMART_EXCLUDES_VERSION`), excluant les fichiers non pertinents (fichiers de verrouillage, artefacts de build, assets binaires et documentation) pour réduire les tokens.
* **Métriques avec Suivi Temporel :** Injection de `log_command_metric()` dans tous les flux avec transmission de la durée en millisecondes (`duration_ms`) et importations différées pour éviter les dépendances circulaires.
* **Résolution Centralisée de la Sortie 🆕 :** Nouvelle fonction `resolve_output_path()` qui centralise la logique des répertoires de sortie — par défaut dans `.gitpr/reports/{type}/` avec repli sur les chemins personnalisés du `.env`.

### **2. Interface CLI et Configuration (`src/main.py` et `src/config.py`)**

* **Configuration Initiale :** Détecte la première exécution, crée le dossier `~/.gitpr/` et demande de manière interactive les clés API, préférences et langue, en les sauvegardant dans un `.env`.
* **Routage des Commandes :** Gère l'ensemble des flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--publish`, `--no-publish`, `--no-edit`, `--base`, `--lang`, `--provider`, `--pre-save`).
* **Comportement par Défaut Modifié 🆕 :** Exécuter `gitpr` sans flags ouvre désormais la TUI du PR Publisher (auparavant : générait un fichier et quittait).
* **Nouveaux Flags 🆕 :**
  * `--publish` : Ouvre la TUI interactive pour examiner, éditer et publier le PR (comportement par défaut).
  * `--no-publish` : Génère la description du PR et la sauvegarde localement sans ouvrir l'éditeur interactif.
  * `--no-edit` : Ignore complètement la TUI — effectue un auto-commit (avec validation du linter), un auto-push et publie directement sur GitHub. Idéal pour CI/CD.
  * `--base <branch>` : Remplace la branche de destination de la Pull Request.
* **Nouvelles Variables d'Environnement 🆕 :** `GITPR_AUTO_COMMIT` (ignorer la confirmation de commit), `GITPR_SKIP_LINT` (ignorer la validation du linter), `GITPR_AUTO_STAGE` (stage automatique des fichiers), `GITPR_SKIP_UNSTAGED_CHECK` (ignorer la vérification des unstaged), `GITPR_SHOW_LOGS` (contrôler les logs de progression), `GITPR_AUTO_MERGE` (auto-merge après publication).
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique de la fonctionnalité avec un lien direct (sensible à la langue) vers GitHub.
* **--lang :** Force la langue de l'interface pour l'exécution actuelle sans persister le changement.
* **--provider :** Force le fournisseur IA (`gemini`, `deepseek`, `ollama`) pour l'exécution actuelle.
* **--mcp :** Démarre le serveur MCP sur le transport stdio pour l'intégration avec les éditeurs — **10 outils annotés + 15 ressources + 7 prompts**.
* **--install :** Assistant guidé en 4 étapes qui télécharge les templates de skills, installe les Git Hooks, configure le MCP dans les éditeurs et valide les clés API.
* **--metrics :** Système de télémétrie locale avec portée par dépôt : `--export` (sauvegarde dans `./.gitpr/metrics/export/`), `--purge` (nettoyage), `--dashboard` (TUI interactive avec analyse du cache).

### **3. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)** 🆕

* **Interface Interactive Complète :** TUI construite avec Textual pour examiner, éditer et publier des Pull Requests directement dans le terminal.
* **6 Écrans Modaux :**
  * `CommitConfirmScreen` : Confirmation avant le commit automatique.
  * `FileStageScreen` : Sélection interactive des fichiers pour le staging.
  * `CommitProgressScreen` : Barre de progression pendant le commit et le push avec logs en temps réel.
  * `CommitMessageScreen` : Affichage et confirmation du message généré par IA.
  * `LinterErrorScreen` : Affichage des erreurs du linter avec option d'abandonner ou de continuer.
  * `ErrorScreen` : Affichage des erreurs générales avec défilement, plafonné à `max-height: 80%`.
* **Bindings :** F1 (Help — modal avec raccourcis et instructions), F2 (Enregistrer le .md localement), F3 (Publier via l'API GitHub), Esc (Quitter).
* **Flux d'Auto-Commit :** Lorsqu'il y a des changements non commités et que l'utilisateur utilise `--no-edit` ou F3, GitPR automatiquement :
  1. Exécute le linter statique (sauf si `GITPR_SKIP_LINT=true`)
  2. Génère un message de commit via IA (Conventional Commits)
  3. Confirme avec l'utilisateur (sauf si `GITPR_AUTO_COMMIT=true`)
  4. Exécute `git commit`
  5. Poursuit vers le push et la publication du PR
* **Vérification des Fichiers Unstaged :** Au démarrage, vérifie `git status --porcelain` et propose le modal `StageFilesApp` pour sélectionner, ignorer ou annuler.
* **Traitement d'un PR Existant :** Détecte les PR ouverts pour la branche actuelle via l'API GitHub et propose de pousser vers le PR existant (mise à jour via PATCH) ou d'en créer un nouveau.
* **Auto-Upstream :** Détecte l'échec de `git push` par absence d'upstream et réessaie automatiquement avec `--set-upstream origin <branch>`.
* **Détection de « Nothing to commit » :** Traite `git commit` sans changements comme un succès — le flux continue sans erreur.
* **Flux de Merge :** Après la création/mise à jour du PR, propose une option de merge. Contrôlé par `GITPR_AUTO_MERGE`.
* **Correction de Stdout :** Wrapper `_with_real_stdout()` pour éviter `OSError: [Errno 9] Bad file descriptor` lorsque la TUI de Textual appelle `click.secho()`.

### **4. Module API GitHub (`src/github_api.py`)** 🆕

* **Fonctions Partagées :** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` — encapsulant les appels REST à l'API GitHub v3.
* **Authentification via PAT :** Token d'accès personnel validé avec `GET /user` avant les opérations.
* **Réutilisation :** Fonctions utilisées à la fois par la TUI de PR et par la TUI d'issues, éliminant la duplication.

### **5. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le git diff sans consommer de quotas IA.
* **Règles YAML :** Lit le fichier local `.gitpr.linter.yml` (créé via `--skill`). Prend en charge les regex de validation, l'ignorance des commentaires et l'ignorance de répertoires spécifiques (à l'aide de fnmatch).
* **Template multilingue :** Les templates du linter sont disponibles en 5 langues.
* **Intégration dans l'Auto-Commit 🆕 :** Le linter est exécuté automatiquement avant le commit dans le flux de publication de PR.

### **6. Sécurité et Authentification (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cryptographie :** Génère une clé maîtresse `secret.key` dans le dossier `~/.gitpr/`.
* **Protection des Tokens :** `encrypt_data` et `decrypt_data` pour protéger les clés API IA et le PAT GitHub.
* **Validation du Token GitHub :** La fonction `validate_github_token()` effectue un appel léger (`GET /user`) pour valider le PAT.
* **Flux d'Auto-Reauthentification :** Si le token expire ou devient invalide pendant `gitpr -is`, l'application capture la réponse HTTP 401, demande un nouveau token à l'utilisateur et relance l'interface TUI en préservant le brouillon.

### **7. Auto-Updater (`src/updater.py`)**

* **Hot-Swap :** Vérifie la version la plus récente sur l'API GitHub Releases. En cas de divergence, télécharge le binaire compilé, renomme l'exécutable actuel et le remplace sans interrompre l'exécution en cours (avec capacité de rollback).
* **Cache quotidien :** Évite les vérifications répétées le même jour.
* **Vérification de connexion :** Socket `8.8.8.8:53` avant toute opération réseau.
* **Versionnage Centralisé :** `__version__` (0.0.33), `__lang_version__` (v0.0.11), `__scripts_version__` (v0.0.1), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` — tous dérivés exclusivement de `updater.py`.

### **8. Interface de Chat Interactif (`src/ui/chat_app.py`)**

* **TUI Complète :** Construite avec Textual — historique de messages, entrée multi-lignes, barre de statut avec bindings visibles.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique de conversation persisté par branche, permettant la continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear` — raccourcis pour les actions courantes de pair programming.
* **Auto-Patching (F5) :** Extrait les blocs de code suggérés par l'IA et les exporte vers un fichier de patch pour une application facile.
* **Mise à jour du Diff (F2) :** Recharge le `git diff` actuel sans redémarrer la session.
* **Exportation de Session (F6) :** Sauvegarde l'historique complet du chat pour la documentation.

### **9. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec prise en charge de placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système d'exploitation à la première exécution et la sauvegarde dans `GITPR_LANG`.
* **5 Langues :** en_us (par défaut/repli), pt_br, pt_pt, es_es, fr_fr.
* **Fichiers Versionnés :** `__lang_version__` (v0.0.11) contrôle la mise à jour des packs de langue (`langs/*.json`).
* **Couverture Étendue 🆕 :** ~623 clés de traduction en pt_BR (+132 depuis v0.0.32). Nouvelles chaînes pour le PR Publisher TUI, les écrans modaux, le flux de commit et la documentation de publication de PR.
* **Cache avec Indexation par Langue :** Les réponses IA mises en cache incluent la langue courante dans le hachage MD5.
* **Script de Synchronisation :** `tests/sync_i18n.py` pour la détection automatique des clés orphelines.

### **10. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA affichant des caractères braille avec des mots de « réflexion ».
* **Délimiteur :** Séparateur de phrases par point-virgule (`;`), compatible avec les phrases complexes contenant des virgules.
* **Vitesse Adaptative & Flickering :** Animation de découverte de caractères adaptée aux phrases longues et utilisation de l'ANSI `\033[K` pour éviter les artefacts visuels dans le terminal.
* **263 entrées par langue :** Synchronisées entre les 5 langues dans les fichiers `templates/gitpr.thinking-words.{lang}.md`.

### **11. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Pris en Charge :** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Mesure de Durée :** Injection de `duration_ms` (chronométrage de haute précision via `time.perf_counter()`) dans `meta_raw` et `_telemetry_meta`.
* **Mode JSON & Paramètres Déterministes :** Sorties structurées avec `temperature=0.0` et `top_p=0.1`.

### **12. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Clé basée sur le hachage MD5 du diff et du prompt.
* **Indexation par Langue :** Le champ `lang` a été ajouté au calcul de la clé du cache, permettant des réponses distinctes pour le même diff dans des langues différentes.
* **Télémétrie et Durée :** Persistance des champs `duration_ms` et `meta_raw` dans les fichiers de cache dans `~/.gitpr/cache/prompts/`.
* **Lecture pour le Tableau de Bord :** `scan_cache_files_for_dashboard()` lit tous les fichiers de cache de manière récursive pour calculer des métriques historiques complètes.

### **13. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff actuel, Historique de la branche (`-ht`) et Archéologie par Blame (`-b`).
* **TUI Interactive :** Édition de brouillons, raccourci F2 (enregistrer localement), F3 (publier sur GitHub via l'API REST) et F1 (aide).
* **Traitement du 401 :** Signalisation de réauthentification sans fermeture de l'application avec perte de contenu.

### **14. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Suit l'évolution et la paternité historique des extraits de code avec classification des commits (`ORIGIN` vs `REFACTORING`).
* **Métriques de Blame :** Événements d'archéologie enregistrés via `log_blame_metric()` avec suivi de la profondeur et du nombre de commits analysés.

### **15. Serveur MCP et Installeur (`src/mcp_server.py`)**

* **10 Outils MCP Annotés :** Annotations (`readOnlyHint`, `destructiveHint`, `idempotentHint`) configurées pour les IDE comme Cursor, VS Code et Claude Code.
* **15 Ressources + 7 Prompts Templatisés :** 35 fichiers de template dans `templates/gitpr.prompt.*.md`.
* **Installeur Automatique :** Configuration des éditeurs pris en charge (VS Code, Cursor, Claude Code, Claude Desktop, Zed) avec fusion JSON intelligente.

### **16. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`)**

* **Portée par Dépôt (Repo-Scope) :** Étiquette `📁 Repository: owner/repo` et filtrage strict des événements et données de cache par projet.
* **Analyse Asynchrone avec Overlay :** Thread worker en arrière-plan qui charge les données de cache tout en affichant le widget `ProgressBar` de Textual.
* **Consolidation des Données :** `load_cache_token_summary()` additionne les tokens des appels mis en cache au totalisateur du tableau de bord.
* **Contrôle de l'État du Cache :** Fichier de registre dans `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Correction des Colonnes au F5 :** Initialisation unique des colonnes (`_setup_columns()`), empêchant la duplication visuelle lors des mises à jour.
* **Exportation Locale :** Sauvegarde CSV/JSON dans `./.gitpr/metrics/export/`.

### **17. Système de Métriques et Télémétrie (`src/metrics.py`)**

* **Portée par Dépôt :** Tous les événements de métriques sont indexés par `repo_name`, permettant l'isolation entre projets.
* **Événements de Hook :** `log_hook_event()` pour les hooks Git (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge).
* **Événements de Linter et Blame :** `log_linter_metric()` pour le linter standalone, `log_blame_metric()` pour l'archéologie de code.
* **Exportation Locale :** `--metrics --export` génère CSV et JSON dans `./.gitpr/metrics/export/` avec filtre par dépôt.
* **Nettoyage :** `--metrics --purge` supprime tous les fichiers de métriques locaux avec confirmation interactive.

### **18. Synchronisation des Hooks Git**

* **Versionnage Indépendant :** `__scripts_version__` (v0.0.1) dans `updater.py` contrôle la version des scripts de hook séparément des dictionnaires de langue.
* **Détection Automatique :** Lors de l'exécution de `--installhooks`, le système compare la version locale (stockée dans le `.env`) avec la version la plus récente et la met à jour automatiquement si nécessaire.
* **Sensible à la Langue :** Détecte la langue configurée et télécharge les templates de hook correspondants.

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|------------------|----------|------|
| `tests/test_core.py` | 25+ | Flux principaux, git diff, génération de PR, timing |
| `tests/test_chat_backend.py` | 30+ | Mémoire de chat, persistance, commandes slash |
| `tests/test_skill_command.py` | 10+ | Téléchargement et validation des templates de skills |
| `tests/test_pre_save.py` | 10+ | Flag --pre-save et payload JSON |
| `tests/test_smart_excludes.py` | 14+ | Filtre pathspec intelligent |
| `tests/test_thinking_words.py` | 10+ | Chargement et parsing avec séparateur `;` |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompts MCP et repli de langue |
| `tests/test_mcp_server.py` | 33 | Outils MCP, ressources, annotations et patching |
| `tests/test_metrics.py` | 36+ | Collecte, exportation locale, portée repo, cache token summary, duration_ms |
| `tests/test_install_wizard.py` | 5+ | Assistant d'installation interactif |
| `tests/test_blame_metrics.py` | 10+ | Métriques de blame : profondeur, commits, durée |
| `tests/test_linter_metrics.py` | 8+ | Métriques de linter : erreurs, avertissements, durée |
| `tests/sync_i18n.py` | — | Script de vérification de la couverture i18n (clés orphelines) |

**Total :** 131 scénarios de test automatisés réussis avec 100% de succès.

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n Étendue 🆕 :** ~623 clés de traduction en pt_BR (étaient 491 dans v0.0.32, +132 nouvelles). Nouvelles chaînes couvrant le PR Publisher TUI, les écrans modaux de commit, le flux de staging et la documentation.
* **Nouvelle Documentation Technique 🆕 :** `docs/pull-request-publication.md` en 5 langues (EN, PT-BR, PT-PT, ES, FR) avec couverture complète du flux de publication de PR, des variables d'environnement et du dépannage.
* **CHANGELOG.md 🆕 :** Historique complet de toutes les versions (v0.0.1 → v0.0.33) au format Keep a Changelog avec les sections Added, Changed et Fixed.
* **READMEs Mis à Jour 🆕 :** Les 5 READMEs mis à jour avec les fonctionnalités du PR Publisher, la structure de répertoires `.gitpr/reports/` et les liens vers la documentation.
* **Documentation en 5 langues :** 24 sujets dans `docs/` traduits en EN, PT-BR, PT-PT, ES, FR (+1 nouveau sujet : pull-request-publication).
* **Memory Index :** `.claude/memory/MEMORY.md` avec 14 patrons d'architecture extraits de 36 rapports.
* **Rapports de tâches :** `docs/claude-code/reports/` et `docs/reports/` (8 rapports de statut).
* **Plans de développement :** 8+ plans documentés dans `docs/plans/`.

---

## **🔄 Pipeline de Distribution**

1. **PyPI :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases :** PyInstaller → `.exe` standalone → upload automatisé
3. **GitHub Actions :** Workflow `pr-review.yml` + `action.yml`
4. **Serveur MCP :** Point d'entrée `gitpr-mcp` via `pyproject.toml`

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.7)**

| Domaine | v0.0.7 (précédent) | v0.0.8 (actuel) |
|------|-------------------|----------------|
| **Version GitPR** | 0.0.32 | **0.0.33** |
| **Version Langue** | v0.0.10 | **v0.0.11** |
| **Version Scripts Hook** | v0.0.1 | v0.0.1 |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Langues** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI + Serveur MCP + Dashboard | CLI + TUI Issues + Chat TUI + Serveur MCP + Dashboard + **PR Publisher TUI** |
| **Publication de PR** | Génération locale de .md uniquement | **TUI interactive complète + auto-commit + push + publication via API** |
| **Comportement par Défaut** | `gitpr` génère un fichier local | **`gitpr` ouvre la TUI du PR Publisher** |
| **Écrans TUI (total)** | 3 (issues, chat, metrics) | **5 apps TUI + 6 écrans modaux de commit** |
| **API GitHub** | Issues via REST | **+ PR (create, update, merge) via module dédié** |
| **Nouveaux Flags CLI** | 21 flags | **24 flags (+ `--publish`, `--no-publish`, `--no-edit`, `--base`)** |
| **Variables d'Environnement** | 7 vars | **13 vars (+6 : AUTO_COMMIT, SKIP_LINT, AUTO_STAGE, SKIP_UNSTAGED, SHOW_LOGS, AUTO_MERGE)** |
| **Traductions pt_BR** | 491 clés | **~623 clés (+132 PR Publisher et flux de commit)** |
| **Modules Python** | 21 fichiers dans src/ | **25 fichiers (+ github_api.py, pr_publish_app.py, pr_publish_help.py)** |
| **Documentation** | 23 sujets | **24 sujets (+ pull-request-publication.md en 5 langues)** |
| **CHANGELOG** | — (uniquement GitHub Releases) | **Historique complet des 8 versions (v0.0.1 → v0.0.33)** |
| **Suite de Tests** | 131 scénarios (12 fichiers) | 131 scénarios (12 fichiers) |
| **Commits depuis v0.0.32** | — | **7 commits (PR Publisher + flux de merge)** |

---

## **🚧 Prochaines Étapes**

* **Tests pour le PR Publisher :** Couverture des tests unitaires et d'intégration pour le flux de publication de PR (`pr_publish_app.py`, `github_api.py`).
* **Tests d'intégration de bout en bout pour MCP :** Validation des appels d'outils et des prompts via un client stdio simulé.
* **Fournisseur Anthropic Claude :** Support direct de l'API Claude (`claude-sonnet-5`).
* **Graphiques ASCII/Textual dans le Dashboard :** Ajouter des histogrammes de temps et des graphiques de tendance de tokens dans la TUI de métriques.
* **Pipeline de Release dans GitHub Actions :** Automatisation complète du build PyInstaller et envoi des assets vers GitHub Releases.
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires.
* **Système de plugins :** Extensibilité pour les règles de linter et les prompts personnalisés.

---

**Rapport généré le :** 2026-08-09  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
