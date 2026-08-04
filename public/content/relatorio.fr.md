# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.31 (2026-08-03)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.6) :**
- **Tableau de Bord TUI de Métriques Refondu :** Portée isolée par dépôt (`repo_filter`), balayage asynchrone illimité des fichiers de cache (`~/.gitpr/cache/prompts/`), superposition visuelle avec `ProgressBar`, totalisateur unifié de tokens par projet, contrôle des fichiers de cache traités (`./.gitpr/metrics/{repo}/processed_cache.json`) et correction du bug de colonnes dupliquées sur le raccourci F5 (Refresh).
- **Suivi de la Durée des Appels IA (Wall-Clock Timing) :** Injection de `duration_ms` en millisecondes via `time.perf_counter()` dans toutes les réponses LLM, transmise par le cache et affichée dans le tableau de bord des métriques.
- **Exportation Locale par Projet :** `gitpr --metrics --export` génère désormais des rapports CSV et JSON dans le dossier du projet local (`./.gitpr/metrics/export/`) en filtrant par le dépôt actif.
- **Revalidation Automatique du Token GitHub (Auto-Reauth sur 401) :** Fonction de validation de PAT (`GET /user`), pré-validation avant la TUI d'issues (`gitpr -is`) et récupération progressive en cas d'erreur HTTP 401 sans perte de brouillons.
- **Ajustements du Spinner et des Thinking Words :** Remplacement du délimiteur de phrases de la virgule par le point-virgule (`;`), permettant des phrases complexes avec des virgules dans `templates/gitpr.thinking-words.*.md` sans rompre l'analyse.
- **Démarrage Rapide dans les READMEs :** Documentation d'installation via `pip install gitpr-cli` et initialisation du dépôt via `gitpr --install` dans les READMEs des 5 langues.
- **Guide du Projet `GEMINI.md` :** Guide architectural complet, conventions de code, pipeline de commandes et standard de rapports dans `docs/gemini/reports/`.

- **Version actuelle :** 0.0.31
- **Publication :** PyPI (`pip install gitpr-cli`) + GitHub Releases (binaire standalone)
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licence :** LGPL-2.1
- **Langues supportées :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **Framework CLI :** Click (pour commandes, flags et formatage terminal).
* **UI/Terminal :** Textual — TUI (Text User Interface) pour chat interactif, édition d'issues, écran d'aide et tableau de bord de métriques.
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API et tokens GitHub.
* **Configuration :** `python-dotenv`, `pyyaml` (pour le linter statique).
* **Fournisseurs IA :** Intégration via SDK officiel Google GenAI (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), et OpenAI SDK (`Ollama` local).
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK officiel Anthropic pour Model Context Protocol) — **Tool Annotations, Prompts avec templates et ressources prompt://**.
* **Tests :** Pytest + `unittest.mock` (10 fichiers de test, 114 scénarios).
* **Empaquetage :** PyInstaller (binaire standalone) + setuptools/build (PyPI).
* **CI/CD :** GitHub Actions (`pr-review.yml`) + `action.yml` pour exécution dans les pipelines.

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec la LLM en demandant un retour strictement en JSON.
* **Map-Reduce (Diffs Géants) :** Lorsque le diff dépasse ~90k tokens, il se divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce) en conservant le ton architectural.
* **Estimation de Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()`.
* **Optimisation Native de Git :** Flags `-U1`, `-w`, `-M`, `-B` dans les commandes `get_git_diff` et `get_git_full_diff` pour réduire le contexte inutile.
* **Pre-Save (`--pre-save`) :** Flag masqué de débogage qui sauvegarde le payload complet (system instruction + prompt) en JSON avant chaque appel à l'IA.
* **Smart Excludes :** Filtre de pathspec intelligent (`gitpr.smart-excludes.json`) distant — téléchargé depuis GitHub et mis à jour automatiquement avec versionnage (`SMART_EXCLUDES_VERSION`), excluant les fichiers non pertinents (lock files, artefacts de build, assets binaires) pour réduire les tokens.
* **Métriques avec Suivi Temporel :** Injection de `log_command_metric()` dans tous les flux avec transmission de la durée en millisecondes (`duration_ms`) et importations différées pour éviter les dépendances circulaires.

### **2. Interface CLI et Configuration (`src/main.py` et `src/config.py`)**

* **Configuration Initiale :** Détecte la première exécution, crée le dossier `~/.gitpr/` et demande de manière interactive les clés API, préférences et langue, en les sauvegardant dans un `.env`.
* **Routage des Commandes :** Gère l'ensemble des flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--lang`, `--provider`, `--pre-save`).
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique de la fonctionnalité avec un lien direct (sensible à la langue) vers GitHub.
* **--lang :** Force la langue de l'interface pour l'exécution actuelle sans persister le changement.
* **--provider :** Force le fournisseur IA (`gemini`, `deepseek`, `ollama`) pour l'exécution actuelle.
* **--mcp :** Démarre le serveur MCP sur le transport stdio pour l'intégration avec les éditeurs — **10 outils annotés + 15 ressources + 7 prompts**.
* **--install :** Assistant guidé en 4 étapes qui télécharge les templates de skill, installe les Git Hooks, configure le MCP dans les éditeurs et valide les clés API.
* **--metrics :** Système de télémétrie locale avec portée par dépôt : `--export` (enregistre dans `./.gitpr/metrics/export/`), `--purge` (nettoyage), `--dashboard` (TUI interactive avec balayage du cache).

### **3. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le git diff sans consommer de quota IA.
* **Règles YAML :** Lit le fichier local `.gitpr.linter.yml` (créé via `--skill`). Supporte le regex de validation, l'ignorance des commentaires et l'exclusion de répertoires spécifiques (via fnmatch).
* **Template Multilingue :** Templates du linter disponibles en 5 langues.

### **4. Sécurité et Authentification (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cryptographie :** Génère une clé maîtresse `secret.key` dans le dossier `~/.gitpr/`.
* **Protection des Tokens :** `encrypt_data` et `decrypt_data` pour protéger les clés API IA et les PAT GitHub.
* **Validation du Token GitHub 🆕 :** La fonction `validate_github_token()` effectue un appel léger (`GET /user`) pour valider le PAT.
* **Flux d'Auto-Reauth 🆕 :** Si le token expire ou est invalide pendant le `gitpr -is`, l'application capture la réponse HTTP 401, demande un nouveau token à l'utilisateur et relance l'interface TUI en préservant le brouillon.

### **5. Auto-Updater (`src/updater.py`)**

* **Hot-Swap :** Vérifie la version la plus récente sur l'API des Releases GitHub. En cas de divergence, télécharge le binaire compilé, renomme l'exécutable actuel et le remplace sans interrompre l'exécution en cours (avec possibilité de rollback).
* **Cache quotidien :** Évite les vérifications répétées le même jour.
* **Vérification de connexion :** Socket `8.8.8.8:53` avant toute opération réseau.
* **Versionnage des assets :** `__lang_version__` (v0.0.8), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` pour le contrôle de mise à jour des templates et traductions.

### **6. Interface de Chat Interactif (`src/ui/chat_app.py`)**

* **TUI Complète :** Construite avec Textual — historique des messages, saisie multi-ligne, barre de statut avec raccourcis visibles.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique de conversation persisté par branche, permettant la continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear` — raccourcis pour les actions courantes de pair programming.
* **Auto-Patching (F5) :** Extrait les blocs de code suggérés par l'IA et les exporte vers un fichier de patch pour une application facile.
* **Actualisation du Diff (F2) :** Recharge le `git diff` actuel sans redémarrer la session.
* **Exportation de Session (F6) :** Enregistre l'historique complet du chat pour documentation.

### **7. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec support des espaces réservés nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système d'exploitation à la première exécution et la sauvegarde dans `GITPR_LANG`.
* **5 Langues :** en_us (par défaut/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Fichiers Versionnés :** `__lang_version__` (v0.0.8) contrôle la mise à jour des paquets de langue (`langs/*.json`).
* **Couverture Totale :** Messages CLI, aide de Click, alertes du linter, Git Hooks, spinner, chat TUI, MCP, métriques et TUI Dashboard traduits.

### **8. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA affichant des caractères braille accompagnés de mots de "réflexion".
* **Délimiteur Mis à Jour 🆕 :** Modification du séparateur de phrases de la virgule vers le point-virgule (`;`), évitant que les phrases avec virgules internes ne soient séparées incorrectement.
* **Vitesse Adaptative & Scintillement :** Animation de découverte des caractères adaptée aux phrases longues et utilisation du code ANSI `\033[K` pour éviter les artefacts visuels dans le terminal.
* **263 entrées par langue :** Synchronisées entre les 5 langues dans les fichiers `templates/gitpr.thinking-words.{lang}.md`.

### **9. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Supportés :** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Mesure de Durée 🆕 :** Injection de `duration_ms` (chronométrage haute précision via `time.perf_counter()`) dans `meta_raw` et `_telemetry_meta`.
* **Mode JSON & Paramètres Déterministes :** Sorties structurées avec `temperature=0.0` et `top_p=0.1`.

### **10. Cache Intelligent (`src/cache.py`)**

* **MD5 + Metadata :** Clé par hachage MD5 du diff et du prompt.
* **Télémétrie et Durée 🆕 :** Persistance du champ `duration_ms` et `meta_raw` dans les fichiers de cache sous `~/.gitpr/cache/prompts/`.
* **Scanner pour Dashboard 🆕 :** `scan_cache_files_for_dashboard()` lit tous les fichiers de cache de manière récursive pour calculer des métriques historiques complètes.

### **11. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff actuel, Historique de branche (`-ht`), et Archéologie par Blame (`-b`).
* **TUI Interactive :** Édition de brouillons, raccourci F2 (sauvegarder localement), F3 (publier sur GitHub via API REST) et F1 (help).
* **Gestion du 401 🆕 :** Demande de réauthentification sans fermeture de l'application ni perte de contenu.

### **12. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Rastreint l'évolution et l'auteur historique des extraits de code avec classification des commits (`ORIGIN` vs `REFACTORING`).

### **13. Serveur MCP et Installateur (`src/mcp_server.py`)**

* **10 Outils MCP Annotés :** Annotations (`readOnlyHint`, `destructiveHint`, `idempotentHint`) configurées pour les IDEs tels que Cursor, VS Code et Claude Code.
* **15 Ressources + 7 Prompts Templatisés :** 35 fichiers de template dans `templates/gitpr.prompt.*.md`.
* **Installateur Automatique :** Configuration des éditeurs supportés (VS Code, Cursor, Claude Code, Claude Desktop, Zed) avec fusion JSON intelligente.

### **14. Tableau de Bord de Métriques TUI Refondu (`src/ui/metrics_app.py`)** 🆕

* **Portée par Dépôt (Repo-Scope) :** Étiquette `📁 Repository: owner/repo` et filtrage strict des événements et données de cache par projet.
* **Balayage Asynchrone avec Overlay :** Thread d'arrière-plan chargeant les données de cache tout en affichant le widget `ProgressBar` de Textual.
* **Consolidation des Données :** `load_cache_token_summary()` ajoute les tokens des appels de cache au totalisateur du tableau de bord.
* **Contrôle d'État du Cache :** Fichier de registre dans `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Fix de Colonnes sur F5 :** Initialisation unique des colonnes (`_setup_columns()`), évitant la duplication visuelle lors des rafraîchissements.
* **Exportation Locale :** Enregistrement CSV/JSON dans `./.gitpr/metrics/export/`.

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|-----------------|-----------|-------|
| `tests/test_core.py` | 25+ | Flux principaux, git diff, PR generation, timing |
| `tests/test_chat_backend.py` | 30+ | Mémoire de chat, persistance, commandes slash |
| `tests/test_skill_command.py` | 10+ | Téléchargement et validation des templates de skill |
| `tests/test_pre_save.py` | 10+ | Flag --pre-save et payload JSON |
| `tests/test_smart_excludes.py` | 14+ | Filtre pathspec intelligent |
| `tests/test_thinking_words.py` | 10+ | Chargement et analyse avec séparateur `;` |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompt MCP et fallback de langue |
| `tests/test_mcp_server.py` | 33 | Outils MCP, ressources, annotations et patchs |
| `tests/test_metrics.py` | 36+ | Collecte, exportation locale, portée de dépôt, cache token summary, duration_ms |
| `tests/test_install_wizard.py` | 5+ | Assistant d'installation interactif |

**Total :** 114 scénarios de test automatisés passant avec 100% de succès.

---

## **🌐 Internationalisation et Documentation**

* **Section Démarrage Rapide dans les READMEs 🆕 :** Mise à jour des fichiers `README.md`, `README.pt_br.md`, `README.pt_pt.md`, `README.es_es.md` et `README.fr_fr.md` avec les instructions `pip install gitpr-cli` et `gitpr --install`.
* **Nouveau Guide `GEMINI.md` 🆕 :** Guide de développement avec standards de code, commandes, structure du projet et rapports obligatoires.
* **447 clés de traduction** par langue (2 235 traductions au total).
* **Documentation en 5 langues :** 23 sujets dans `docs/` traduits en EN, PT-BR, PT-PT, ES, FR.
* **Rapports de tâches :** `docs/claude-code/reports/` et `docs/gemini/reports/`.

---

## **🔄 Pipeline de Distribution**

1. **PyPI :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases :** PyInstaller → `.exe` standalone → téléversement automatisé
3. **GitHub Actions :** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server :** Entry point `gitpr-mcp` via `pyproject.toml`

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.5)**

| Domaine | v0.0.5 (précédent) | v0.0.6 (actuel) |
|---------|--------------------|-----------------|
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Langues** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Tableau de Bord TUI** | Global, limité à 100 événements | **Repo-scoped, balayage du cache illimité + ProgressBar + F5 fix** |
| **Métriques & Durée** | Tokens et compteurs simples | **Wall-clock duration (`duration_ms`) + Export local (`./.gitpr/metrics/export/`)** |
| **GitHub PAT Auth** | Stockage sécurisé sans pré-validation | **Validation préalable via `GET /user` + Auto-Reauth gracieux sur 401** |
| **Thinking Words** | Séparateur par virgule `,` | **Séparateur `;` (supporte les phrases complexes) synchro en 5 langues** |
| **README Documentation** | Focus téléchargement de binaires | **Quick Start avec `pip install gitpr-cli` et `gitpr --install` en 5 langues** |
| **Manuels de Développement**| CLAUDE.md | **CLAUDE.md + GEMINI.md** |
| **Suite de Tests** | 100+ scénarios | **114 scénarios de test (100% passant)** |
| **Version PyPI** | 0.0.30 | **0.0.31** |

---

## **🚧 Prochaines Étapes**

* **Tests d'intégration end-to-end pour MCP :** Validation des appels d'outils et prompts via un client stdio simulé.
* **Fournisseur Anthropic Claude :** Support direct de l'API Claude (`claude-3-5-sonnet`).
* **Graphiques ASCII/Textual dans le Tableau de Bord :** Ajouter des histogrammes de temps et des graphiques de tendance de tokens dans la TUI de métriques.
* **Pipeline de Release dans GitHub Actions :** Automatisation complète du build PyInstaller et envoi des assets vers GitHub Releases.

---

**Rapport généré le :** 2026-08-03  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))  