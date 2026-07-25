# **🚀 Rapport d'État du Projet : GitPR CLI — v0.0.28 (2026-07-24)**

---

## **📌 Vue d'Ensemble**

**GitPR** est un outil CLI avancé pour l'automatisation des flux Git alimenté par IA (Google Gemini / DeepSeek / Ollama). Il agit comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, crée des messages de commit sémantiques, audite la dette technique et injecte les bonnes pratiques dans le flux de travail du développeur (**Shift Left**).

**Nouveauté de cette version :** Intégration avec **MCP (Model Context Protocol)** — GitPR fonctionne désormais comme un serveur MCP, exposant toutes ses capacités d'IA comme outils directement dans les éditeurs comme VS Code, Cursor et Claude Desktop, sans avoir besoin du terminal.

- **Version actuelle :** 0.0.28
- **Distribution :** PyPI (`pip install gitpr-cli`) + GitHub Releases (binaire standalone)
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licence :** LGPL-2.1
- **Langues supportées :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **Framework CLI :** Click (commandes, flags, formatage du terminal)
* **UI/Terminal :** Textual — TUI pour chat interactif, édition d'issues et écran d'aide
* **Chiffrement :** cryptography.fernet pour la protection locale des clés API
* **Configuration :** dotenv, PyYAML (pour le linter statique)
* **Fournisseurs IA :** SDK Google GenAI (gemini-2.5-flash), SDK OpenAI (DeepSeek), SDK OpenAI (Ollama local)
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK officiel Anthropic pour Model Context Protocol) — **NOUVEAUTÉ v0.0.28**
* **Tests :** Pytest + unittest.mock (8 fichiers de test, 160+ scénarios)
* **Empaquetage :** PyInstaller (binaire standalone) + setuptools/build (PyPI)
* **CI/CD :** GitHub Actions (`pr-review.yml`) + `action.yml` pour exécution en pipelines

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec le LLM en demandant un retour strictement JSON.
* **Map-Reduce (Diffs Géants) :** Quand le diff dépasse ~90k tokens, divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce) en conservant le ton de l'architecture.
* **Estimation de Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()`.
* **Optimisation Native Git :** Flags `-U1`, `-w`, `-M`, `-B` dans `get_git_diff` et `get_git_full_diff` pour réduire le contexte inutile.
* **Pre-Save (`--pre-save`) :** Flag caché de debug qui sauvegarde le payload complet (system instruction + prompt) en JSON avant chaque appel IA.
* **Smart Excludes :** Filtre pathspec intelligent (`gitpr.smart-excludes.json`) distant — téléchargé depuis GitHub avec versionnement (`SMART_EXCLUDES_VERSION`), excluant les lock files, build artifacts et assets binaires pour réduire les tokens.

### **2. Interface CLI et Configuration (`src/main.py`, `src/config.py`)**

* **Configuration Initiale :** Détecte la première exécution, crée le dossier `~/.gitpr/` et guide l'utilisateur interactivement (clés API, préférences, langue) en sauvegardant dans `.env`.
* **Routage des Commandes :** Gère tous les flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--lang`, `--provider`, `--pre-save`).
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique avec lien direct (language-aware) vers GitHub.
* **--lang :** Force la langue de l'interface pour l'exécution en cours sans persister le changement.
* **--provider :** Force le fournisseur IA (`gemini`, `deepseek`, `ollama`) pour l'exécution en cours.
* **--mcp :** Démarre le serveur MCP sur le transport stdio pour l'intégration avec les éditeurs — **NOUVEAUTÉ v0.0.28**.

### **3. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) du git diff sans consommer de quotas IA.
* **Règles YAML :** Lit le fichier local `.gitpr.linter.yml` (créé via `--skill`). Supporte la validation regex, l'ignorance des commentaires et l'exclusion de chemins (utilisant fnmatch).
* **Template multilingue :** Templates du linter disponibles en 5 langues.

### **4. Coffre-fort de Sécurité (`src/security.py`)**

* **Chiffrement :** Génère une clé maîtresse `secret.key` dans le dossier `~/.gitpr/`.
* **Fonctions :** `encrypt_data` et `decrypt_data` — les clés ne sont jamais en clair.
* **GitHub PAT :** Token d'accès personnel GitHub stocké de manière chiffrée pour la création d'issues via API REST.

### **5. Auto-Updater (`src/updater.py`)**

* **Hot-Swap :** Vérifie l'API GitHub Releases, télécharge le binaire compilé, renomme l'exécutable actuel et le remplace sans interrompre l'exécution (avec capacité de rollback).
* **Cache quotidien :** Évite les vérifications répétées le même jour.
* **Vérification de connexion :** Socket `8.8.8.8:53` avant toute opération réseau.
* **Versionnement des assets :** `__lang_version__`, `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` pour le contrôle de mise à jour des templates et traductions.

### **6. Chat Interactif (`src/ui/chat_app.py`)**

* **TUI Complète :** Construite avec Textual — historique des messages, input multi-ligne, barre de statut avec bindings visibles.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique de conversation persisté par branche, permettant la continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear` — raccourcis pour les actions courantes de pair programming.
* **Auto-Patching (F5) :** Extrait les blocs de code suggérés par l'IA et les exporte vers un fichier patch pour une application facile.
* **Rafraîchissement du Diff (F2) :** Recharge le `git diff` actuel sans redémarrer la session.
* **Export de Session (F6) :** Sauvegarde l'historique complet du chat pour la documentation.
* **Commandes multilingues :** Fichiers `chat_commands.{lang}.json` avec les traductions des commandes slash.

### **7. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue de l'OS au premier lancement et l'enregistre dans `GITPR_LANG`.
* **5 Langues :** en_us (défaut/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Fallback Anglais :** Si une traduction est manquante, affiche le texte en anglais directement.
* **Fichiers Versionnés :** `__lang_version__` contrôle la mise à jour des packs de langue (`langs/*.json`).
* **Couverture :** Toutes les interfaces, help Click, alertes linter, messages système, Git Hooks, spinner, chat **et MCP** traduits.
* **364 clés par langue** — **NOUVEAUTÉ v0.0.28** (+42 clés MCP).

### **8. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA affichant des caractères braille avec des mots de « réflexion ».
* **Révélation Progressive :** Mots dévoilés lettre par lettre avec des caractères aléatoires, suivis d'un cycle de points (`. .. ...`).
* **Palette de 10 couleurs aléatoires** pour chaque mot.
* **Multilingue :** Thinking Words chargées depuis des templates spécifiques par langue (`gitpr.thinking-words.{lang}.md`), avec versionnement (`THINKING_WORDS_VERSION`).

### **9. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Supportés :**
  * **Google Gemini :** `gemini-2.5-flash` (primaire) / `gemini-2.5-flash-lite` (secondaire)
  * **DeepSeek :** `deepseek-chat` (primaire et secondaire)
  * **Ollama :** Tout modèle local compatible OpenAI API
* **Architecture Multi-Modèle :** Fallback automatique entre fournisseurs en cas d'échec.
* **Mode JSON :** Tous les fournisseurs configurés pour une sortie structurée (`response_mime_type` / `response_format`).
* **Paramètres déterministes :** Temperature 0.0, top_p 0.1.

### **10. Cache Intelligent (`src/cache.py`)**

* **MD5 :** Hash exact du code (diff) + instructions pour identifier les appels identiques.
* **Cache par Dépôt :** JSON inclut le champ `repo` pour le filtrage multi-projet.
* **Économie de Quota :** Retour en millisecondes depuis le cache local (`~/.gitpr/cache/prompts/`).

### **11. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :**
  * **Issue de Nouveau Code (`gitpr -is`) :** Lit le `git diff` actuel.
  * **Issue Épique/Release (`gitpr -is -ht`) :** Lit l'historique complet de la branche (Git Log + Cache de PR).
  * **Issue de Dette Technique (`gitpr -is -b fichier:lignes`) :** Chronologie via `git blame`.
* **TUI Interactive :** Éditeur d'issues avec syntax highlight, bindings pour sauvegarder en local (F2) ou envoyer via API GitHub (F3).
* **Help Screen (F1) :** Modal avec raccourcis et instructions.

### **12. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Trace l'origine des règles métier avec une profondeur maximale de 4 commits parents.
* **Classification :** Modèle secondaire classifie les commits comme `ORIGIN` ou `REFACTORING`.
* **Résumé Exécutif :** Modèle avancé génère l'analyse finale consolidée.
* **Sortie :** Terminal color-coded (vert=origin, jaune=refactoring) + rapport Markdown.

### **13. Système de Skills et Templates**

* **Templates Locaux :** `.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md` comme *System Instructions* personnalisables.
* **Templates Distants :** Téléchargés depuis GitHub via `--skill` (ne jamais écraser les fichiers locaux existants).
* **Multilingue :** Templates disponibles en 5 langues avec fallback intelligent (`get_skill_context()`).
* **Dossier `.gitpr/skill/` :** Organisation propre des fichiers de skill dans le projet.

### **14. Optimisation Map-Reduce pour Diffs Géants**

* **Activation Automatique :** Quand le diff dépasse ~90k tokens estimés.
* **Découpage Sécurisé :** Au délimiteur regex `(^diff --git a/)` pour ne pas corrompre la syntaxe.
* **Rate Limiting :** `time.sleep(1)` entre les lots Map.
* **Documentation :** Page dédiée en 5 langues (`docs/map-reduce-diff.{lang}.md`) linkée dans la console pendant le traitement.
* **Progression dans la Console :** Affiche le nombre de lots et le lien vers la documentation.

### **15. Intégration CI/CD**

* **GitHub Actions :** Workflow `pr-review.yml` pour la revue automatique des PRs.
* **Action Definition :** `action.yml` pour utilisation comme GitHub Action dans des pipelines externes.
* **Git Hooks Locaux :** `pre-commit` (linter) et `prepare-commit-msg` (génération de message par IA) installables via `--installhooks`.

### **16. Serveur MCP — Intégration avec Éditeurs et IDEs (`src/mcp_server.py`)** 🆕

* **10 Outils MCP :** `get_git_context`, `analyze_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **7 Ressources MCP :** Templates de skill (`skill://pr`, `skill://commit`, etc.) + config du linter (`linter://config`).
* **Transport stdio :** Communication via JSON-RPC 2.0 — standard pour les outils CLI locaux.
* **Isolation de Sortie :** Système de monkey-patching qui redirige toute la sortie du terminal (bannières, spinners, couleurs) vers stderr, garantissant que le canal stdout reste propre pour le protocole MCP.
* **Commande `gitpr-mcp` :** Point d'entrée dédié enregistré dans `pyproject.toml`.
* **Flag `--mcp` :** Alias via la CLI principale (`gitpr --mcp`).

### **17. Installateur MCP (`gitpr-mcp --install`)** 🆕

* **6 Éditeurs Supportés :** VS Code (`.vscode/mcp.json`), Cursor (`.cursor/mcp.json`), Claude Code (`.mcp.json`), Claude Desktop (global), Zed (global).
* **Mode Auto :** Détecte automatiquement quels éditeurs sont configurés et installe pour tous.
* **Merge Intelligent :** Ajoute le serveur GitPR sans supprimer les serveurs existants — idempotent et sécurisé.
* **Création de Dossiers :** Crée automatiquement `.vscode/`, `.cursor/` ou le dossier global s'ils n'existent pas.

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|---|---|---|
| `tests/test_core.py` | 25+ | Flux principaux, git diff, PR generation |
| `tests/test_chat_backend.py` | 30+ | Mémoire de chat, persistance, commandes |
| `tests/test_skill_command.py` | 10+ | Téléchargement et validation de templates |
| `tests/test_pre_save.py` | 10+ | Flag --pre-save et payload JSON |
| `tests/test_smart_excludes.py` | 14+ | Filtre pathspec intelligent |
| `tests/test_thinking_words.py` | 10+ | Chargement et parsing des thinking words |
| `tests/test_mcp_server.py` 🆕 | 33 | Outils MCP, ressources, patching de sortie, safe-call wrapper |

---

## **🌐 Internationalisation et Documentation**

* **364 clés de traduction** par langue (5 langues = 1 820 traductions).
* **Documentation complète en 5 langues :** 20 sujets × 5 langues = 100+ pages de documentation.
* **Nouvelle documentation MCP :** `docs/mcp-integration.md` + 4 traductions (PT-BR, PT-PT, ES, FR).
* **Plans de développement :** 7 plans documentés dans `docs/plans/`.
* **Rapports Claude Code :** 11+ rapports de tâches dans `docs/claude-code/reports/develop_natan/`.
* **Site officiel :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
* **READMEs synchronisés :** Liens relatifs convertis en absolus (compatible PyPI).

---

## **🔄 Pipeline de Distribution**

1. **PyPI :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases :** PyInstaller → `.exe` standalone → upload via workflow
3. **GitHub Actions :** PR Review automatisé avec `action.yml`
4. **MCP :** `gitpr-mcp` enregistré comme point d'entrée dans `pyproject.toml` → installé automatiquement avec `pip install` 🆕

---

## **📈 Évolution Depuis le Rapport Précédent (v0.0.2)**

| Domaine | v0.0.2 (précédent) | v0.0.3 (actuel) |
|---|---|---|
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Langues** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI | CLI + TUI Issues + Chat TUI + **MCP Server** |
| **MCP (Model Context Protocol)** | — (planifié) | **Serveur MCP complet avec 10 tools + 7 resources** |
| **MCP Installer** | — | **`gitpr-mcp --install` pour 6 éditeurs** |
| **Intégration avec Éditeurs** | — (terminal uniquement) | **VS Code, Cursor, Claude Code, Claude Desktop, Zed** |
| **Documentation MCP** | — | **5 langues (EN, PT-BR, PT-PT, ES, FR)** |
| **Clés i18n** | 322 clés/langue | **364 clés/langue (+42 MCP)** |
| **Tests** | 7 fichiers (130+ scénarios) | **8 fichiers (160+ scénarios)** |
| **Dépendances** | 8 paquets | **9 paquets (+mcp>=1.0.0)** |
| **README PyPI** | Liens relatifs (cassés) | **Liens absolus (fonctionnels sur PyPI)** |
| **Version** | 0.0.27 | **0.0.28** |

---

## **🚧 Prochaines Étapes**

* **Tests d'intégration :** Couverture end-to-end des flux principaux, y compris les tests du serveur MCP.
* **MCP Prompts :** Ajouter des prompts MCP (modèles de messages) pour les flux courants comme « review PR ».
* **MCP Annotations :** Tool annotations (`readOnlyHint`, `destructiveHint`) pour une meilleure intégration avec les IDEs.
* **Plus de fournisseurs :** Claude API, OpenAI direct, fournisseurs locaux additionnels.
* **Métriques et analytics :** Tableau de bord d'utilisation pour les équipes.
* **Système de plugins :** Extensibilité pour les règles de linter et les prompts personnalisés.
* **Migration MCP SDK v2 :** Surveiller la stabilisation du SDK v2.x (mode stateless, tasks).

---

**Rapport généré le :** 2026-07-24
**Branche :** `develop_natan`
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))

---

[← Contribution](/contribuicao) &nbsp;|&nbsp; [Accueil →](/index)
