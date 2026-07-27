# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.30 (2026-07-26)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version :** Système de Métriques et Télémétrie locale hors ligne avec export CSV/JSON et tableau de bord TUI interactif, nouveaux Git Hooks pour la télémétrie comportementale (post-checkout, pre-push, post-merge), Thinking Words étendues à 263 entrées avec les phrases "Sussing" et "Cerebrating", correction du scintillement du spinner (`\033[K`), et traduction de tous les commentaires/docblocks en anglais.

- **Version actuelle :** 0.0.30
- **Publication :** PyPI (`pip install gitpr-cli`) + GitHub Releases (binaire standalone)
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licence :** LGPL-2.1
- **Langues supportées :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues)

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **Framework CLI :** Click (pour commandes, flags et formatage terminal).
* **UI/Terminal :** Textual — TUI (Text User Interface) pour chat interactif, édition d'issues, écran d'aide et tableau de bord de métriques.
* **Cryptographie :** cryptography.fernet pour la protection locale des clés API.
* **Configuration :** dotenv, pyyaml (pour le linter statique).
* **Fournisseurs IA :** Intégration via SDK officiel Google GenAI (gemini-2.5-flash), OpenAI SDK (DeepSeek), et OpenAI SDK (Ollama local).
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK officiel Anthropic pour Model Context Protocol) — **Tool Annotations, Prompts avec templates et ressources prompt:// en v0.0.30**.
* **Tests :** Pytest + unittest.mock (8 fichiers de test, 165+ scénarios).
* **Packaging :** PyInstaller (binaire standalone) + setuptools/build (PyPI).
* **CI/CD :** GitHub Actions (`pr-review.yml`) + `action.yml` pour exécution dans les pipelines.

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec la LLM en demandant un retour strictement en JSON.
* **Map-Reduce (Diffs Géants) :** Quand le diff dépasse ~90k tokens, divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce) en conservant le ton de l'architecture.
* **Estimation de Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()`.
* **Optimisation Native Git :** Flags `-U1`, `-w`, `-M`, `-B` dans les commandes `get_git_diff` et `get_git_full_diff` pour réduire le contexte inutile.
* **Pre-Save (`--pre-save`) :** Flag de debug caché qui sauvegarde le payload complet (instruction système + prompt) en JSON avant chaque appel à l'IA.
* **Smart Excludes :** Filtre de pathspec intelligent (`gitpr.smart-excludes.json`) distant — téléchargé depuis GitHub et mis à jour automatiquement avec versionnement (`SMART_EXCLUDES_VERSION`), excluant les fichiers non pertinents (lock files, artefacts de build, assets binaires) pour réduire les tokens.
* **Métriques fire-and-forget 🆕 :** Injection de `log_command_metric()` dans tous les flux (single-chunk et map-reduce) avec imports paresseux pour éviter les importations circulaires.

### **2. Interface CLI et Configuration (`src/main.py` et `src/config.py`)**

* **Configuration Initiale :** Détecte la première exécution, crée le dossier `~/.gitpr/`, et demande interactivement les clés API, préférences et langue, en sauvegardant dans un `.env`.
* **Routage des Commandes :** Gère tous les flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--lang`, `--provider`, `--pre-save`).
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique de la fonctionnalité avec un lien direct (adapté à la langue) vers GitHub.
* **--lang :** Force la langue de l'interface pour l'exécution en cours sans persister le changement.
* **--provider :** Force le fournisseur IA (`gemini`, `deepseek`, `ollama`) pour l'exécution en cours.
* **--mcp :** Démarre le serveur MCP en transport stdio pour l'intégration avec les éditeurs — **10 outils annotés + 15 ressources + 7 prompts**.
* **--metrics 🆕 :** Système de télémétrie locale avec sous-options : `--export` (CSV+JSON), `--purge` (nettoyage), `--dashboard` (TUI interactive), `--hook-event` (usage interne par les git hooks).

### **3. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le git diff sans consommer de quotas IA.
* **Règles YAML :** Lit le fichier local `.gitpr.linter.yml` (créé via `--skill`). Supporte les regex de validation, l'ignorance des commentaires et l'ignorance de répertoires spécifiques (en utilisant fnmatch).
* **Template multilingue :** Templates du linter disponibles en 5 langues.

### **4. Sécurité et Coffre-fort (`src/security.py`)**

* **Cryptographie :** Génère une clé maîtresse `secret.key` dans le dossier `~/.gitpr/`.
* **Fonctions :** `encrypt_data` et `decrypt_data` pour garantir que les jetons et clés ne soient pas en texte clair.
* **GitHub PAT :** Jeton d'accès personnel GitHub stocké de manière cryptée pour la création d'issues via l'API REST.

### **5. Mise à Jour Automatique (`src/updater.py`)**

* **Hot-Swap :** Vérifie sur l'API GitHub Releases la version la plus récente. En cas de divergence, télécharge le binaire compilé, renomme l'exécutable actuel et le remplace sans interrompre l'exécution en cours (avec capacité de rollback).
* **Cache quotidien :** Évite les vérifications répétées le même jour.
* **Vérification de connexion :** Socket `8.8.8.8:53` avant toute opération réseau.
* **Versionnement des assets :** `__lang_version__` (v0.0.8), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` pour le contrôle des mises à jour des templates et traductions.

### **6. Interface de Chat Interactif (`src/ui/chat_app.py`)**

* **TUI Complète :** Construite avec Textual — historique des messages, champ de saisie multi-ligne, barre de statut avec bindings visibles.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique de conversation persisté par branche, permettant la continuité entre sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear` — raccourcis pour des actions courantes de pair programming.
* **Auto-Patching (F5) :** Extrait les blocs de code suggérés par l'IA et les exporte vers un fichier de patch pour application facile.
* **Mise à Jour du Diff (F2) :** Recharge le `git diff` actuel sans redémarrer la session.
* **Export de Session (F6) :** Sauvegarde l'historique complet du chat pour documentation.
* **Commandes multilingues :** Fichiers `chat_commands.{lang}.json` avec traductions des commandes slash.

### **7. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec support de placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système à la première exécution et sauvegarde dans `GITPR_LANG`.
* **5 Langues :** en_us (par défaut/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Fallback en Anglais :** Si une traduction manque, affiche le texte en anglais directement.
* **Fichiers Versionnés :** `__lang_version__` (v0.0.8) contrôle la mise à jour des packs de langue (`langs/*.json`).
* **Couverture :** Tous les messages d'interface, aide de Click, alertes du linter, messages système, Git Hooks, spinner, chat, outils MCP, ressources MCP, prompts MCP, annotations MCP, métriques et tableau de bord TUI traduits.
* **447 clés par langue 🆕** (+83 clés dans cette version : 16 métriques CLI + 20 tableau de bord TUI + 47 incrémentales).

### **8. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA affichant des caractères braille avec des mots de "pensée".
* **Découverte Progressive :** Mots révélés lettre par lettre avec des caractères aléatoires, suivis d'un cycle de points (`. .. ...`).
* **Couleurs Aléatoires :** Palette de 10 couleurs pour chaque mot.
* **Vitesse Adaptative :** Phrases longues (36+ caractères) révélées plus rapidement (1 frame/lettre, 0.04s) pour afficher le texte complet avant de changer de mot. Les mots courts conservent la vitesse originale.
* **Correction du Scintillement 🆕 :** Remplacement de `ljust(70)` par ANSI `\033[K` (clear to end of line) pour éliminer les résidus de phrases longues lors du passage à des mots courts.
* **Multilingue :** Thinking Words chargées depuis des templates spécifiques par langue (`gitpr.thinking-words.{lang}.md`), avec versionnement (`THINKING_WORDS_VERSION`).
* **263 entrées par langue 🆕 :** Élargi avec les phrases "Sussing" (31) et "Cerebrating" (31) — total de 263 mots/phrases par langue.

### **9. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Supportés :**
  * **Google Gemini :** `gemini-2.5-flash` (primaire) / `gemini-2.5-flash-lite` (secondaire)
  * **DeepSeek :** `deepseek-chat` (primaire et secondaire)
  * **Ollama :** Tout modèle local compatible avec l'API OpenAI
* **Architecture Multi-Modèle :** Basculement automatique entre fournisseurs en cas d'échec.
* **Mode JSON :** Tous les fournisseurs configurés pour une sortie structurée (`response_mime_type` / `response_format`).
* **Paramètres déterministes :** Temperature 0.0, top_p 0.1.
* **Injection de télémétrie :** Métadonnées d'utilisation (`_telemetry_meta`) injectées silencieusement dans les réponses pour alimenter le système de métriques.

### **10. Cache Intelligent (`src/cache.py`)**

* **MD5 :** Hash exact du code (diff) + instructions pour identifier les appels identiques.
* **Cache par Dépôt :** JSON inclut le champ `repo` pour le filtrage multi-projet.
* **Économie de Quota :** Retourne les résultats en millisecondes depuis le cache local (`~/.gitpr/cache/prompts/`).
* **Métadonnées de télémétrie :** Champ `meta_raw` avec le comptage de tokens sauvegardé avec le cache.

### **11. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :**
  * **Issue de Code Nouveau (`gitpr -is`) :** Lit le `git diff` actuel.
  * **Issue d'Épopée/Release (`gitpr -is -ht`) :** Lit l'historique complet de la branche (Git Log + Cache de PR).
  * **Issue de Dette Technique (`gitpr -is -b fichier:lignes`) :** Ligne de temps via `git blame`.
* **TUI Interactive :** Éditeur d'issues avec syntax highlighting, bindings pour sauvegarder localement (F2) ou envoyer via l'API GitHub (F3).
* **Écran d'Aide (F1) :** Fenêtre modale avec raccourcis et instructions.

### **12. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Retrace l'origine des règles métier avec une profondeur maximale de 4 commits parents.
* **Classification :** Modèle secondaire classifie les commits comme `ORIGIN` ou `REFACTORING`.
* **Résumé Exécutif :** Modèle avancé génère une analyse finale consolidée.
* **Sortie :** Terminal colorisé (vert=origin, jaune=refactoring) + rapport Markdown.

### **13. Système de Skills et Templates**

* **Templates Locaux :** `.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md` comme *System Instructions* personnalisables.
* **Templates Distants :** Téléchargés depuis GitHub via `--skill` (ne remplace jamais les fichiers locaux existants).
* **Multilingue :** Templates disponibles en 5 langues avec fallback intelligent (`get_skill_context()`).
* **Templates MCP Prompt 🆕 :** 35 fichiers (`gitpr.prompt.*.md`) en 5 langues dans le répertoire `templates/`.

### **14. Optimisation Map-Reduce pour Diffs Géants**

* **Activation Automatique :** Quand le diff dépasse ~90k tokens estimés.
* **Split Sécurisé :** Coupe au délimiteur regex `(^diff --git a/)` pour ne pas corrompre la syntaxe.
* **Rate Limiting :** `time.sleep(1)` entre les lots Map.
* **Documentation :** Page dédiée en 5 langues (`docs/map-reduce-diff.{lang}.md`) liée dans la console pendant le traitement.
* **Progression dans la Console :** Affiche le comptage des lots et un lien vers la documentation.

### **15. Intégration CI/CD**

* **GitHub Actions :** Workflow `pr-review.yml` pour la révision automatique des PRs.
* **Action Definition :** `action.yml` pour utilisation comme GitHub Action dans les pipelines externes.
* **Git Hooks Locaux :** `pre-commit` (linter), `prepare-commit-msg` (génération de message par IA), `post-checkout`, `pre-push`, `post-merge` (télémétrie comportementale 🆕) installables via `--installhooks`.

### **16. Serveur MCP — Intégration avec Éditeurs et IDEs (`src/mcp_server.py`)**

* **10 Outils MCP avec Annotations :** `get_git_context`, `analyze_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue` — tous avec `ToolAnnotations` (`readOnlyHint`, `destructiveHint`, `idempotentHint`).
  * **3 outils en lecture seule** (`readOnlyHint=True`, `idempotentHint=True`) : `get_git_context`, `analyze_diff`, `run_linter`.
  * **7 outils avec effets de bord** (`readOnlyHint=False`, `destructiveHint=False`) : appels réseau (APIs IA, git fetch).
* **15 Ressources MCP :** 7 templates de skill (`skill://pr`, `skill://commit`, etc.) + config du linter (`linter://config`) + 7 templates de prompt (`prompt://review`, `prompt://commit`, etc.) + `prompt://list`.
* **7 Prompts MCP avec Templates :** Contenu externalisé dans 35 fichiers de template (7 prompts × 5 langues) dans le répertoire `templates/gitpr.prompt.*.md` avec basculement de langue automatique.
* **Transport stdio :** Communication via JSON-RPC 2.0 — standard pour les outils CLI locaux.
* **Isolation de Sortie :** Système de monkey-patching qui redirige toute la sortie du terminal (bannières, spinners, couleurs) vers stderr, garantissant que le canal stdout reste propre pour le protocole MCP.
* **Commande `gitpr-mcp` :** Point d'entrée dédié enregistré dans `pyproject.toml`.
* **Flag `--mcp` :** Alias via la CLI principale (`gitpr --mcp`).

### **17. Installeur MCP (`gitpr-mcp --install`)**

* **6 Éditeurs Supportés :** VS Code (`.vscode/mcp.json`), Cursor (`.cursor/mcp.json`), Claude Code (`.mcp.json`), Claude Desktop (global), Zed (global).
* **Mode Auto :** Détecte automatiquement quels éditeurs sont configurés et installe pour tous.
* **Fusion Intelligente :** Ajoute le serveur GitPR sans supprimer les serveurs existants — idempotent et sûr.
* **Création de Répertoires :** Crée automatiquement `.vscode/`, `.cursor/` ou le répertoire global s'ils n'existent pas.

### **18. Métriques et Télémétrie Locale (`src/metrics.py`, `src/ui/metrics_app.py`)** 🆕

* **Collecte fire-and-forget :** Chaque commande CLI génère un événement JSON asynchrone dans `~/.gitpr/metrics/{owner}/{branch}/` avec timestamp, commande, statut, fournisseur, tokens, durée, dépôt et branche.
* **Payload enrichi :** Champs additionnels comme `cache_hit`, `map_reduce_triggered`, `linter_errors`, `linter_warnings` pour le contexte spécifique de la commande.
* **Export CSV/JSON :** `gitpr --metrics --export` consolide tous les événements non exportés avec `click.progressbar()`, générant `gitpr_metrics_YYYY-MM-DD.csv` et `.json`.
* **Nettoyage sécurisé :** `gitpr --metrics --purge` supprime les fichiers après confirmation de l'utilisateur.
* **Tableau de Bord TUI :** `gitpr --metrics --dashboard` ouvre une interface Textual interactive avec DataTable (100 derniers événements), barre de résumé agrégé (total, erreurs, tokens, top commandes/fournisseurs) et bindings F5 (rafraîchir) et Esc (quitter).
* **Git Hooks de télémétrie :** `post-checkout` (changement de branche), `pre-push` (livraisons), `post-merge` (intégration) — installés automatiquement via `--installhooks`.
* **100% local et anonyme :** Aucune donnée ne quitte la machine. Les événements contiennent des métadonnées d'utilisation, jamais le contenu des fichiers ou des diffs.

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|-----------------|-----------|-------|
| `tests/test_core.py` | 25+ | Flux principaux, git diff, génération PR |
| `tests/test_chat_backend.py` | 30+ | Mémoire de chat, persistance, commandes |
| `tests/test_skill_command.py` | 10+ | Téléchargement et validation de templates |
| `tests/test_pre_save.py` | 10+ | Flag --pre-save et payload JSON |
| `tests/test_smart_excludes.py` | 14+ | Filtre pathspec intelligent |
| `tests/test_thinking_words.py` | 10+ | Chargement et parsing des thinking words |
| `tests/test_mcp_prompts.py` | 11 | Fonctions de prompt, PROMPT_FILES, _read_prompt_file(), basculement de langue |
| `tests/test_mcp_server.py` | 33 | Outils MCP, ressources, patching de sortie, wrapper safe-call |

## **🌐 Internationalisation et Documentation**

* **447 clés de traduction** par langue (5 langues = 2 235 traductions) 🆕.
* **Documentation complète en 5 langues :** 23 sujets × 5 langues = 115+ pages de documentation.
* **Nouvelle documentation 🆕 :** `docs/metricas-telemetria.md` en 5 langues (EN, PT-BR, PT-PT, ES, FR).
* **Documentation existante élargie :** `docs/mcp-prompts.md` et `docs/mcp-annotations.md` mises à jour avec le système de templates et les ressources `prompt://`.
* **Templates MCP :** 35 fichiers de prompt (`gitpr.prompt.*.md`) en 5 langues dans le répertoire `templates/`.
* **Thinking Words :** 263 entrées par langue dans `templates/gitpr.thinking-words.{lang}.md`.
* **Code nettoyé 🆕 :** Tous les commentaires et docstrings en portugais traduits en anglais dans les fichiers `src/metrics.py`, `src/main.py` et `src/ai_providers.py`.
* **Plans de développement :** 8 plans documentés dans `docs/plans/`.
* **Rapports Claude Code :** 13+ rapports de tâches dans `docs/claude-code/reports/develop_natan/`.
* **Site officiel :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
* **READMEs synchronisés :** Mis à jour avec MCP Prompts, MCP Tool Annotations et Métriques & Télémétrie dans toutes les 5 langues 🆕.

## **🔄 Pipeline de Distribution**

1. **PyPI :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases :** PyInstaller → `.exe` standalone → téléchargement via workflow
3. **GitHub Actions :** Révision de PR automatisée avec `action.yml`
4. **MCP :** `gitpr-mcp` enregistré comme point d'entrée dans `pyproject.toml` → installé automatiquement avec `pip install`

## **📈 Évolution depuis le Rapport Précédent (v0.0.4)**

| Domaine | v0.0.4 (précédent) | v0.0.5 (actuel) |
|---------|--------------------|-----------------|
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Langues** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI + Serveur MCP | CLI + TUI Issues + Chat TUI + Serveur MCP + **Tableau de Bord Métriques TUI** |
| **Outils MCP** | 10 outils avec ToolAnnotations | 10 outils avec ToolAnnotations |
| **Ressources MCP** | 15 (skills + linter + prompts) | 15 (skills + linter + prompts) |
| **Prompts MCP** | 7 prompts avec templates (35 fichiers) | 7 prompts avec templates (35 fichiers) |
| **Télémétrie** | — (uniquement événement map-reduce) | **Système complet : collecte, export, purge, tableau de bord TUI** |
| **Git Hooks** | 2 (pre-commit, prepare-commit-msg) | **5 (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge)** |
| **Flags CLI** | 16 flags | **21 flags (+ --metrics, --export, --purge, --dashboard, --hook-event)** |
| **Thinking Words** | 201 entrées/langue | **263 entrées/langue (+62 phrases Sussing + Cerebrating)** |
| **Spinner** | Vitesse adaptative | Vitesse adaptative + **correction du scintillement (\033[K)** |
| **Clés i18n** | 364 clés/langue | **447 clés/langue (+83)** |
| **Documentation** | 110+ pages (22 sujets) | **115+ pages (23 sujets)** |
| **Code** | Commentaires mélangés PT/EN | **Tous les commentaires en anglais** |
| **Version** | 0.0.29 | **0.0.30** |
| **Version Lang** | v0.0.7 | **v0.0.8** |

## **🚧 Prochaines Étapes**

* **Tests d'intégration MCP :** Couverture de bout en bout du serveur MCP avec client de test.
* **Plus de fournisseurs :** API Claude, OpenAI direct, fournisseurs locaux supplémentaires.
* **Système de plugins :** Extensibilité pour les règles de linter et les prompts personnalisés.
* **Migration MCP SDK v2 :** Surveiller la stabilisation du SDK v2.x (mode stateless, tasks).
* **GitHub Release automatisé :** Pipeline CI/CD complet pour build + release.
* **Tableau de bord avec métriques d'équipe :** Serveur HTTP optionnel pour tableaux de bord dans le navigateur à partir des CSV exportés.

---

**Rapport généré le :** 2026-07-26
**Branche :** `develop_natan`
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))