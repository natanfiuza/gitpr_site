# **🚀 Rapport d'État du Projet : GitPR CLI — v0.0.27 (2026-07-19)**

---

## **📌 Vue d'Ensemble**

**GitPR** est un outil CLI avancé pour l'automatisation des flux Git alimenté par IA (Google Gemini / DeepSeek / Ollama). Il agit comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, crée des messages de commit sémantiques, audite la dette technique et injecte les bonnes pratiques dans le flux de travail du développeur (**Shift Left**).

- **Version actuelle :** 0.0.27
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
* **Tests :** Pytest + unittest.mock (7 fichiers de test, 130+ scénarios)
* **Empaquetage :** PyInstaller (binaire standalone) + setuptools/build (PyPI)
* **CI/CD :** GitHub Actions (`pr-review.yml`) + `action.yml` pour exécution en pipelines

---

## **🧩 Modules Implémentés et Architecture des Fichiers**

### **1. Noyau et Opérations Git (`src/core.py`)**

* **Génération Structurée :** Communique avec le LLM en demandant un retour strictement JSON.
* **Map-Reduce (Diffs Géants) :** Divise automatiquement en lots par fichier (`split_diff_into_chunks`), traite chaque partie (Map) et unifie les résumés (Reduce).
* **Estimation de Tokens :** Heuristique légère `len() // 4` via `estimate_token_count()`.
* **Optimisation Native Git :** Flags `-U1`, `-w`, `-M`, `-B` pour réduire le contexte inutile.
* **Pre-Save (`--pre-save`) :** Flag caché de debug qui sauvegarde le payload complet en JSON avant chaque appel IA.
* **Smart Excludes :** Filtre pathspec intelligent distant — téléchargé depuis GitHub avec versionnement (`SMART_EXCLUDES_VERSION`).

### **2. Interface CLI et Configuration (`src/main.py`, `src/config.py`)**

* **Configuration Initiale :** Détecte la première exécution et guide l'utilisateur interactivement.
* **Routage des Commandes :** Gère tous les flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--lang`, `--provider`, `--pre-save`).
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique avec lien vers GitHub.
* **--lang :** Force la langue de l'interface pour l'exécution en cours.
* **--provider :** Force le fournisseur IA (`gemini`, `deepseek`, `ollama`) pour l'exécution en cours.

### **3. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse les lignes ajoutées (`+`) du git diff sans consommer de quotas IA.
* **Règles YAML :** Lit le fichier `.gitpr.linter.yml`. Supporte la validation regex, l'ignorance des commentaires et l'exclusion de chemins.
* **Template multilingue :** Templates du linter disponibles en 5 langues.

### **4. Coffre-fort de Sécurité (`src/security.py`)**

* **Chiffrement :** Génère une clé maîtresse `secret.key` dans `~/.gitpr/`.
* **Fonctions :** `encrypt_data` et `decrypt_data` — les clés ne sont jamais en clair.
* **GitHub PAT :** Token GitHub stocké de manière chiffrée pour la création d'issues via API REST.

### **5. Auto-Updater (`src/updater.py`)**

* **Hot-Swap :** Vérifie l'API GitHub Releases, télécharge et remplace le binaire sans interrompre l'exécution (avec rollback).
* **Cache quotidien :** Évite les vérifications répétées le même jour.
* **Vérification de connexion :** Socket `8.8.8.8:53` avant toute opération réseau.
* **Versionnement des assets :** `__lang_version__`, `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`.

### **6. Chat Interactif (`src/ui/chat_app.py`)**

* **TUI Complète :** Construite avec Textual — historique, input multi-ligne, barre de statut.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique persisté par branche.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear`.
* **Auto-Patching (F5) :** Extrait les blocs de code suggérés vers un fichier patch.
* **Rafraîchissement du Diff (F2) :** Recharge le `git diff` sans redémarrer.
* **Export de Session (F6) :** Sauvegarde l'historique complet en Markdown.

### **7. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec placeholders nommés.
* **Détection Automatique :** Détecte la langue de l'OS et l'enregistre dans `GITPR_LANG`.
* **5 Langues :** en_us (défaut/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Fallback Anglais :** Texte en anglais si la traduction est manquante.
* **Couverture complète :** Toutes les interfaces, help Click, alertes linter, messages système, Git Hooks, spinner et chat.

### **8. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan affichant des caractères braille avec des mots de « réflexion ».
* **Révélation Progressive :** Mots dévoilés lettre par lettre, suivis d'un cycle de points.
* **Palette de 10 couleurs aléatoires.**
* **Multilingue :** Thinking Words chargées depuis des templates par langue avec versionnement.

### **9. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Supportés :**
  * **Google Gemini :** `gemini-2.5-flash` (primaire) / `gemini-2.5-flash-lite` (secondaire)
  * **DeepSeek :** `deepseek-chat` (primaire et secondaire)
  * **Ollama :** Tout modèle local compatible OpenAI API
* **Architecture Multi-Modèle :** Fallback automatique entre fournisseurs.
* **Mode JSON :** Tous les fournisseurs configurés pour une sortie structurée.
* **Paramètres déterministes :** Temperature 0.0, top_p 0.1.

### **10. Cache Intelligent (`src/cache.py`)**

* **MD5 :** Hash exact du code (diff) + instructions.
* **Cache par Dépôt :** JSON avec champ `repo` pour filtrage multi-projet.
* **Économie de Quota :** Retour en millisecondes depuis `~/.gitpr/cache/prompts/`.

### **11. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :**
  * **Issue de Nouveau Code (`gitpr -is`) :** Lit le `git diff` actuel.
  * **Issue Épique/Release (`gitpr -is -ht`) :** Lit l'historique complet de la branche.
  * **Issue de Dette Technique (`gitpr -is -b fichier:lignes`) :** Chronologie via `git blame`.
* **TUI Interactive :** Éditeur d'issues avec syntax highlight, sauvegarde locale (F2) ou envoi via API GitHub (F3).

### **12. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Trace l'origine des règles métier (profondeur max 4 commits parents).
* **Classification :** Modèle secondaire classifie les commits comme `ORIGIN` ou `REFACTORING`.
* **Résumé Exécutif :** Modèle avancé génère l'analyse finale consolidée.
* **Sortie :** Terminal color-coded (vert=origin, jaune=refactoring) + rapport Markdown.

### **13. Système de Skills et Templates**

* **Templates Locaux :** `.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md`.
* **Templates Distants :** Téléchargés depuis GitHub via `--skill`.
* **Multilingue :** Templates disponibles en 5 langues avec fallback intelligent.
* **Dossier `.gitpr/skill/` :** Organisation propre des fichiers de skill.

### **14. Optimisation Map-Reduce pour Diffs Géants**

* **Activation Automatique :** Quand le diff dépasse ~90k tokens estimés.
* **Découpage Sécurisé :** Au délimiteur regex `(^diff --git a/)`.
* **Rate Limiting :** `time.sleep(1)` entre les lots Map.
* **Documentation dédiée en 5 langues** linkée dans la console.

### **15. Intégration CI/CD**

* **GitHub Actions :** Workflow `pr-review.yml` pour revue automatique des PRs.
* **Action Definition :** `action.yml` pour utilisation comme GitHub Action externe.
* **Git Hooks Locaux :** `pre-commit` (linter) et `prepare-commit-msg` (message IA) installables via `--installhooks`.

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

---

## **🌐 Internationalisation et Documentation**

* **130+ fichiers** traduits/versionnés.
* **Documentation complète en 5 langues :** 19 sujets × 5 langues = 95+ pages.
* **Plans de développement :** 6 plans documentés dans `docs/plans/`.
* **Rapports Claude Code :** 10+ rapports dans `docs/claude-code/reports/develop_natan/`.
* **Site officiel :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)

---

## **🔄 Pipeline de Distribution**

1. **PyPI :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases :** PyInstaller → `.exe` standalone → upload via workflow
3. **GitHub Actions :** PR Review automatisé avec `action.yml`

---

## **📈 Évolution Depuis le Rapport Précédent (v0.0.1)**

| Domaine | v0.0.1 (précédent) | v0.0.2 (actuel) |
|---|---|---|
| **Fournisseurs IA** | Gemini + DeepSeek | Gemini + DeepSeek + **Ollama (local)** |
| **Langues** | 2 (en, pt_br) | **5 (en, pt_br, pt_pt, es_es, fr_fr)** |
| **Interface** | CLI + TUI Issues | CLI + TUI Issues + **Chat TUI Interactif** |
| **Templates** | EN + PT-BR | **5 langues** |
| **Documentation** | Partielle | **95+ pages en 5 langues** |
| **Tests** | 1 fichier | **7 fichiers (130+ scénarios)** |
| **CI/CD** | — | **GitHub Actions + action.yml** |
| **Smart Excludes** | Local | **Distant avec versionnement** |
| **Thinking Words** | Statique | **Multilingue avec versionnement** |
| **Pre-Save** | — | **Flag de debug pour inspection payload** |
| **Chat Memory** | — | **Persistance par branche** |
| **Map-Reduce Docs** | — | **Documentation en 5 langues** |
| **Site Web** | — | **gitpr.natanfiuza.dev.br** |

---

## **🚧 Prochaines Étapes**

* **Tests d'intégration :** Couverture end-to-end des flux principaux.
* **MCP (Model Context Protocol) :** Intégration potentielle avec éditeurs et IDEs.
* **Plus de fournisseurs :** Claude API, OpenAI direct, fournisseurs locaux additionnels.
* **Métriques et analytics :** Tableau de bord d'utilisation pour les équipes.
* **Système de plugins :** Extensibilité pour règles de linter et prompts personnalisés.

---

**Rapport généré le :** 2026-07-19
**Branche :** `develop_natan`
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))

---

[← Contribution](/contribuicao) &nbsp;|&nbsp; [Accueil →](/index)
