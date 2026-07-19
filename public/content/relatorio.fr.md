# GitPR CLI — Rapport d'État du Projet

---

## Vue d'Ensemble

**GitPR** est un outil CLI pour l'automatisation des flux de travail Git alimenté par IA (Google Gemini / DeepSeek). Il agit comme un assistant intelligent local qui effectue des Code Reviews, génère des descriptions de Pull Request, crée des messages de commit sémantiques, audite la dette technique et injecte les bonnes pratiques dans le flux du développeur (approche **Shift Left**).

---

## Architecture et Bibliothèques de Base

- **Langage :** Python 3.x
- **Framework CLI :** Click (commandes, flags, formatage du terminal)
- **UI/Terminal :** TUI interactive (Textual) pour le chat et l'édition d'issues
- **Chiffrement :** Chiffrement symétrique Fernet pour la protection locale des clés API
- **Configuration :** dotenv, PyYAML (pour le linter statique)
- **Fournisseurs IA :** SDK Google GenAI (gemini-2.5-flash) + DeepSeek + Ollama

---

## Modules Implémentés

### 1. Noyau et Opérations Git (`src/core.py`)
Communication structurée avec LLM demandant des réponses strictement en JSON (`commit_message` et `pr_description`). Optimisation native Git avec les flags `-U1`, `-w`, `-M`, `-B` pour des diffs minimaux et ciblés.

### 2. Interface CLI et Configuration (`src/main.py`, `src/config.py`)
Détection de première exécution, configuration interactive des clés API, configuration `.env` dans `~/.gitpr/`. Routage des commandes pour tous les flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`).

### 3. Moteur d'Analyse Statique (`src/linter_engine.py`)
Linter hors ligne analysant uniquement les lignes ajoutées (`+`) du `git diff`. Lit `.gitpr.linter.yml` avec des règles regex, ignorant les commentaires et avec exclusion de chemins via `fnmatch`.

### 4. Coffre-fort de Sécurité (`src/security.py`)
Génération de clé Fernet (`secret.key`), fonctions `encrypt_data` et `decrypt_data`. Les clés API ne sont jamais stockées en clair.

### 5. Auto-Updater (`src/updater.py`)
Mise à jour hot-swap du binaire via l'API GitHub Releases avec vérification SHA-256 et capacité de rollback.

### 6. Chat et Auto-Patch (`src/ui/chat_app.py`)
TUI interactive avec mémoire de messages par branche. F5 extrait les blocs de code vers des fichiers patch. F6 exporte les sessions en Markdown. Slash commands pour les actions courantes.

### 7. Internationalisation (`src/i18n.py`)
Helper `__()` inspiré de Laravel avec placeholders nommés. Packs de traduction JSON dans `~/.gitpr/langs/`. Fallback anglais pour les clés manquantes. Supporte `en`, `pt_br`, `pt_pt`, `fr`, `es`.

### 8. Architecture Map-Reduce
Optimisation à deux niveaux pour les grands diffs :
- **Niveau 1 :** Flags Git natifs (`-U1`, `-w`, `-M`, `-B`) pour un contexte minimal
- **Niveau 2 :** Estimation de tokens (`len() // 4`), découpage sécurisé aux limites `diff --git`, appels IA par lots avec `time.sleep(1)` pour respecter le rate limit, et étape finale Reduce concaténant les résumés

---

## Métriques Clés

- **Fournisseurs IA :** 3 (Gemini, DeepSeek, Ollama)
- **Langues supportées :** 5 (EN, PT-BR, PT-PT, FR, ES)
- **Commandes CLI :** Plus de 12 flags
- **Linter :** Configurable par YAML, coût IA zéro
- **Cache :** Basé sur MD5, déduplication automatique
- **Sécurité :** Chiffrement symétrique Fernet (AES-128-CBC)

---

## Documentation

La documentation complète est disponible sur [github.com/natafiuza/gitpr](https://github.com/natafiuza/gitpr) et sur ce site.

---

[← Contribution](/contribuicao) &nbsp;|&nbsp; [Accueil →](/index)
