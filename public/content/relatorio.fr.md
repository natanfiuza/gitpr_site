# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.12 (2026-08-19)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.12) :**
- **Pont de Linters Externes + Assistant `--linter-setup` :** Intégration avec des linters matures (ESLint, PHP_CodeSniffer, Stylelint) exécutés uniquement sur les lignes modifiées du diff, parser de sortie Checkstyle XML, nouvelle TUI d'erreurs (`LinterApp`) et rapport Markdown consolidé dans `.gitpr/reports/linter/`. L'assistant interactif configure tout avec des presets distants (`templates/gitpr.linter-presets.json`) versionnés par le marqueur `LINTER_PRESETS_VERSION`.
- **i18n Réparée et Complète :** Le regex legacy du sync capturait des arguments de call-site (`fg="cyan"`, `count=len(...)`) et générait des clés "mangled" qui retombaient toujours sur le fallback anglais. 51 clés corrompues réparées + 36 clés avec `\n` littéral dans les 6 dictionnaires ; audit AST de 638 clés avec **0 non traduites et 0 mangled** ; parité totale de **547 clés identiques par fichier** ; `__lang_version__` v0.0.13 → **v0.0.20** avec tests de garde.
- **Trailer de Co-paternité :** Chaque commit généré par l'IA reçoit `Co-Authored-By: Gitpr-cli <gitpr@natanfiuza.dev.br>` — idempotent (ne duplique pas, préserve les trailers de tiers), masqué de la TUI (injecté uniquement à l'exécution du commit) et avec opt-out `GITPR_COAUTHOR=false`.
- **Correction du Hang du MCP Server :** Les 12 tool handlers étaient synchrones et tournaient inline sur l'event loop — tout appel bloquant (subprocess git, téléchargement OTA, SDK IA) gelait le serveur stdio entier. Nouveau décorateur `_offload` (anyio worker threads), warm-import au démarrage, `stdin=subprocess.DEVNULL` sur tous les subprocesses et timeout dur de 10s sur le téléchargement des smart-excludes. Nouveaux tests e2e avec JSON-RPC stdio réel.
- **Corrections du Modal d'Erreur du Linter :** Boutons "Commit with --no-verify" et "Abort" côte à côte (avant empilés et superposés) ; le choix no-verify reprend désormais le flux de commit (avant il fermait le modal et revenait au linter en boucle) ; push du modal différé via `call_next` vers le message pump de l'app.
- **Dead Code Supprimé + Ajustements MCP :** Classe morte `FileStageScreen` supprimée (élément en attente du rapport précédent) ; `claude-code` listé dans l'aide de `gitpr-mcp --install` ; alias caché `gitpr --mcp` documenté.
- **Documentation Multilingue Étendue :** `docs/ARCHITECTURE.md` réécrit en EN canonique + 4 locales créés (18 sujets d'architecture, index de 32 docs) ; nouveau sujet `i18n_explanation` en 5 langues ; READMEs et 4 sujets mis à jour.
- **Formatage Cohérent du Codebase :** Refactor Black-style dans tout `src/` (guillemets doubles, trailing commas, sauts de ligne) — sans changement fonctionnel.
- **Skills Locales de Claude Code :** `status-report` (génération du rapport de statut), `implement-fixes` (flux de corrections) et `caveman-commit` (messages de commit compacts — a remplacé le doc `docs/caveman-commit.md`).

- **Version actuelle :** 0.0.37
- **Version des dictionnaires de langue :** v0.0.20
- **Version des scripts de hook :** v0.0.3
- **Publication :** PyPI (`pip install gitpr-cli`) + GitHub Releases (binaire standalone)
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **Licence :** LGPL-2.1
- **Langues prises en charge :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues, 6 dictionnaires)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **CLI Framework :** Click (pour les commandes, flags et formatage du terminal).
* **UI/Terminal :** Textual — TUI (Text User Interface) pour le chat interactif, l'édition d'issues, l'écran d'aide, le tableau de bord de métriques, le PR Publisher et les erreurs du linter (`LinterApp`).
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API et des tokens GitHub.
* **Configuration :** `python-dotenv`, `pyyaml` (pour le linter statique).
* **Fournisseurs IA :** Intégration via le SDK officiel Google GenAI (`gemini-2.5-flash`), le SDK OpenAI (`DeepSeek`) et le SDK OpenAI (`Ollama` local).
* **GitHub API :** `requests` (REST API via PAT) — module `src/github_api.py` avec `create_pull_request()`, `update_pull_request()`, `merge_pull_request()`.
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK officiel Anthropic pour Model Context Protocol) — 12 outils annotés, 15 ressources, 7 prompts ; handlers déchargés vers des threads via `anyio`.
* **Tests :** Pytest + `unittest.mock` (17 fichiers de test, 264 scénarios) + tests e2e du serveur MCP via subprocess réel (JSON-RPC stdio).
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
* **Smart Excludes à Deux Couches :** Filtre pathspec intelligent avec couche globale (`~/.gitpr/conf/`) + locale du projet (`./.gitpr/conf/`). Fusion au runtime (union, dédupliquée). Auto-seed du fichier local à la première exécution. Prend en charge 3 variables d'environnement (`GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`).
* **Métriques avec Suivi du Temps :** Injection de `log_command_metric()` dans tous les flux avec transmission de la durée en millisecondes (`duration_ms`) et imports paresseux.
* **Résolution Centralisée de la Sortie :** Fonction `resolve_output_path()` qui centralise la logique des répertoires de sortie — par défaut dans `.gitpr/reports/{type}/` avec fallback vers les chemins personnalisés du `.env`.
* **Détection de Merge en Cours :** Helper `is_merge_in_progress()` (vérifie `git rev-parse -q --verify MERGE_HEAD`, silencieux et worktree-safe) — utilisé comme défense en profondeur contre les anciens hooks qui appellent la CLI pendant un merge.
* **Staging avec Erreur Réelle :** `stage_files()` renvoie le tuple `(success, error_message)` capturant le stderr/stdout de `git add` en cas d'échec — l'erreur réelle de git atteint l'utilisateur au lieu d'être avalée.
* **Trailer de Co-paternité 🆕 :** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — helper idempotent qui ajoute `Co-Authored-By: Gitpr-cli <gitpr@natanfiuza.dev.br>` avec séparation par lignes vides ; ne duplique pas un trailer existant et préserve les `Co-Authored-By:` de tiers.
* **Téléchargement OTA avec Timeout Dur 🆕 :** `_download_smart_excludes()` exécute la requête dans un thread daemon avec timeout de 10s — le timeout d'urllib ne limite pas la résolution DNS sous Windows ; en cas de stall, bascule vers la copie hors ligne.
* **Subprocesses Blindés 🆕 :** `stdin=subprocess.DEVNULL` sur tous les `subprocess.run` — les processus enfants n'héritent plus du pipe JSON-RPC du serveur MCP (évite le hang interactif).
* **Sortie de Linter Centralisée 🆕 :** `OUTPUT_FILE_NAME_LINTER` mappé vers le dossier `linter` dans `_OUTPUT_FOLDER_MAP` — rapports enregistrés dans `.gitpr/reports/linter/`.

### **2. Système Global de Plugins (`src/plugins.py`)**

* **Architecture de Plugins :** Système d'extensibilité qui charge les plugins du répertoire `~/.gitpr/plugins/` en s'appliquant à **tous les projets**.
* **Plugins de Linter (`linter/`) :** Fichiers `.yml` avec des règles regex supplémentaires fusionnées avec le `.gitpr.linter.yml` local. 🆕 `load_external_linters()` lit également la section `external_linters` des plugins globaux.
* **Plugins de Prompt MCP (`prompts/`) :** Fichiers `.md` qui étendent le contexte système avec des instructions spécifiques.
* **Factory Closures :** Fonctions `get_linter_plugins` et `get_prompt_plugins` avec closures pour isoler l'état entre les sessions.
* **Commande `--plugins` :** Liste tous les plugins globaux installés avec leurs types et chemins.
* **Documentation Multilingue :** `docs/plugins-system.md` en 5 langues (EN, PT-BR, PT-PT, ES, FR).

### **3. Interface CLI et Configuration (`src/main.py` et `src/config.py`)**

* **Setup Initial :** Détecte la première exécution, crée le dossier `~/.gitpr/` et demande interactivement les clés API, les préférences et la langue.
* **Routage des Commandes :** Gère tous les flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--linter-setup`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--no-publish`, `--no-edit`, `--base`, `--lang`, `--provider`, `--pre-save`, `--plugins`, `--status`, `--update`).
* **Comportement par Défaut :** Exécuter `gitpr` sans flags ouvre la TUI du PR Publisher.
* **Flags :**
  * `--publish` : remplacé par le flux par défaut — la TUI du PR Publisher s'ouvre par défaut ; les modificateurs `--no-publish` / `--no-edit` / `--base` contrôlent le flux.
  * `--no-publish` : Génère la description du PR et l'enregistre localement sans ouvrir l'éditeur interactif.
  * `--no-edit` : Saute entièrement la TUI — auto-commit (avec validation du linter), auto-push et publie directement sur GitHub.
  * `--base <branch>` : Remplace la branche cible du Pull Request.
  * `--plugins` : Liste les plugins globaux installés.
  * `--linter-setup` 🆕 : Ouvre l'assistant interactif de configuration des linters externes (presets distants + injection dans `.gitpr.linter.yml`).
  * `--version` : Affiche la version actuelle de GitPR (via `@click.version_option`).
* **Variables d'Environnement :** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE`, `GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`, `OUTPUT_FILE_NAME_LINTER` 🆕, `GITPR_COAUTHOR` 🆕 (opt-out en lecture seule, hors de `DEFAULT_CONFIG`).
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique de la fonctionnalité avec un lien direct (tenant compte de la langue) vers GitHub. 🆕 Corrigé pour les flags avec trait d'union (`--linter-setup`, `--no-publish`, `--no-edit`, `--no-unstaged-check`) — `param_name.replace('-', '_')`.
* **--lang :** Force la langue de l'interface pour l'exécution en cours sans persister le changement.
* **--provider :** Force le fournisseur d'IA (`gemini`, `deepseek`, `ollama`) pour l'exécution en cours.
* **--mcp :** Démarre le serveur MCP sur le transport stdio pour l'intégration avec les éditeurs — **12 outils annotés + 15 ressources + 7 prompts**.
* **--install :** Assistant guidé en 4 étapes qui télécharge des templates de skill, installe des Git Hooks, configure MCP dans les éditeurs et valide les clés API. 🆕 Sortie 100 % traduite (10 messages codés en dur migrés vers `__()` + 34 nouvelles clés).
* **--metrics :** Système de télémétrie locale avec périmètre par dépôt : `--export`, `--purge`, `--dashboard` (TUI interactive avec scan du cache).
* **--status :** Liste les fichiers non commités catégorisés (new/modified/deleted) — rapide, sans IA, sans réseau.
* **Rapport du Linter Conditionnel 🆕 :** Le rapport `.gitpr/reports/linter/` n'est généré que lorsqu'il y a des warnings ou des erreurs — les diffs propres ne créent plus de fichiers vides.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)**

* **Interface Interactive Complète :** TUI construite avec Textual pour réviser, éditer et publier des Pull Requests directement dans le terminal.
* **6 Écrans Modaux :** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Modal de Fichiers Unstaged Amélioré :** Liste de fichiers avec hauteur fixe (`height: 6`) et défilement vertical interne.
* **Bindings :** F1 (Aide), F2 (Enregistrer .md local), F3 (Publier via GitHub API), Échap (Quitter).
* **Flux d'Auto-Commit :** Linter → message IA → confirmation → commit → push → publie le PR.
* **Vérification des Fichiers Unstaged :** Au démarrage, vérifie `git status --porcelain` et propose un modal pour sélectionner, sauter ou annuler.
* **Gestion de PR Existant :** Détecte les PRs ouverts pour la branche actuelle via l'API GitHub et propose push ou création.
* **Auto-Upstream :** Détecte l'échec de `git push` dû à l'absence d'upstream et tente automatiquement `--set-upstream origin <branch>`.
* **Détection de "Nothing to commit" :** Traite `git commit` sans changements comme un succès.
* **Flux de Merge :** Après création/mise à jour du PR, propose une option de merge. Contrôlé par `GITPR_AUTO_MERGE`.
* **Gestion d'Erreur de Merge :** Callbacks `_on_merge_success` / `_on_merge_failure` avec modal d'erreur pour HTTP 405 (conflits) et retour visuel post-TUI.
* **Sélection Réelle de Fichiers :** `StageFilesScreen.btn_stage` lit la sélection directement depuis `SelectionList.selected` — les toggles individuels de ligne (clic/Entrée) sont désormais respectés ; suppression du dictionnaire manuel `_selected` qui se désynchronisait et du `git add` dupliqué dans la TUI (staging unique dans `main.py`).
* **Dead Code Supprimé 🆕 :** La classe brouillon `FileStageScreen` (doublon mort de `StageFilesScreen`) a été supprimée avec les imports orphelins `get_unstaged_files`/`stage_files` — élément des « Prochaines Étapes » du rapport précédent terminé.
* **Trailer de Co-paternité Masqué 🆕 :** Le `Co-Authored-By:` n'apparaît plus sur l'écran d'édition du message (`CommitMessageScreen`) — il est injecté uniquement à l'exécution du commit, après confirmation de l'utilisateur. `_pending_commit_msg` reste propre pour le fallback de titre du PR.
* **Modal d'Erreur du Linter Corrigé 🆕 :** Boutons côte à côte dans un conteneur `Horizontal` avec `height: auto` (avant empilés/superposés par le `1fr`) ; push du `LinterErrorScreen` différé via `call_next` vers le message pump de l'app (avant le callback était attaché à la file morte du progress screen) ; `skip_linter` dans `_start_progress_and_commit`/`_run_linter_and_commit` garantit que le commit no-verify reprend le flux sans réexécuter le linter.

### **5. Module API GitHub (`src/github_api.py`)**

* **Fonctions Partagées :** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` — encapsulant les appels REST à l'API GitHub v3.
* **Authentification via PAT :** Jeton d'accès personnel validé avec `GET /user` avant les opérations.
* **Réutilisation :** Fonctions utilisées à la fois par la TUI de PR et la TUI d'issues.

### **6. Moteur d'Analyse Statique / Linter (`src/linter_engine.py`)**

* **Linter Hors Ligne :** Analyse statiquement les lignes ajoutées (`+`) dans le git diff sans dépenser de quotas d'IA.
* **Règles YAML :** Lit le fichier local `.gitpr.linter.yml` (créé via `--skill`). Prend en charge la validation par regex, l'ignorance des commentaires et l'ignorance de répertoires spécifiques.
* **Plugins de Linter :** Règles supplémentaires chargées depuis `~/.gitpr/plugins/linter/*.yml` et fusionnées avec les règles locales.
* **Pont de Linters Externes 🆕 :** `_run_external_linter()` exécute des linters externes via subprocess (`encoding="utf-8"`, `errors="replace"`, `stdin=DEVNULL`, `timeout=120`) et renvoie le stdout XML **indépendamment du code de sortie** — les linters renvoient > 0 lorsqu'ils trouvent des problèmes.
* **Parser Checkstyle XML 🆕 :** `_parse_checkstyle_xml()` extrait les erreurs (line/severity/message) avec `xml.etree.ElementTree`, en tolérant les numéros de ligne non numériques et le XML invalide.
* **Croisement avec le Diff 🆕 :** Le mode diff suit les lignes ajoutées (`+`) et ne comptabilise que les erreurs du XML dont la ligne a été modifiée dans le diff actuel — les problèmes préexistants sont ignorés.
* **Setup Externe-Seul 🆕 :** Sans règles regex mais avec des linters externes configurés, le scan s'exécute quand même (avant, il était silencieusement ignoré).
* **Rapport Consolidé 🆕 :** `generate_linter_report_content()` consolide les erreurs regex + externes dans un seul Markdown.
* **Template multilingue :** Templates du linter disponibles en 5 langues.
* **Intégration à l'Auto-Commit :** Exécuté automatiquement avant le commit dans le flux de publication de PR.

### **7. Sécurité et Authentification (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Chiffrement :** Génère une clé maîtresse `secret.key` dans le dossier `~/.gitpr/`.
* **Protection des Jetons :** `encrypt_data` et `decrypt_data` pour protéger les clés API d'IA et le PAT GitHub.
* **Validation du Jeton GitHub :** `validate_github_token()` avec un appel léger (`GET /user`).
* **Flux d'Auto-Reauth :** Si le jeton expire pendant `gitpr -is`, capture le 401, demande un nouveau jeton et relance la TUI en préservant le brouillon.

### **8. Auto-Updater (`src/updater.py`)**

* **Hot-Swap :** Vérifie sur l'API GitHub Releases la version la plus récente, télécharge le binaire compilé et le remplace sans casser l'exécution en cours (avec rollback).
* **Cache quotidien :** Évite les vérifications répétées le même jour.
* **Vérification de connexion :** Socket `8.8.8.8:53` avant toute opération réseau.
* **Versionnage Centralisé :** `__version__` (0.0.37), `__lang_version__` (v0.0.20), `__scripts_version__` (v0.0.3), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION` 🆕 (presets de linter actualisables sans release).

### **9. Interface de Chat Interactive (`src/ui/chat_app.py`)**

* **TUI Complète :** Construite avec Textual — historique des messages, saisie multi-ligne, barre d'état avec bindings visibles.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique de conversation persisté par branche, permettant la continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear` — raccourcis pour le pair programming.
* **Auto-Patching (F5) :** Extrait les blocs de code suggérés par l'IA et les exporte vers un fichier de patch.
* **Rafraîchissement du Diff (F2) :** Recharge le `git diff` actuel sans redémarrer la session.
* **Export de Session (F6) :** Enregistre l'historique complet du chat pour la documentation.

### **10. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec prise en charge des placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système à la première exécution et l'enregistre dans `GITPR_LANG`.
* **5 Langues, 6 Dictionnaires :** en_us (défaut/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr (es/fr dupliqués par famille).
* **Fichiers Versionnés :** `__lang_version__` (v0.0.20) contrôle la mise à jour des packs de langue (`langs/*.json`) — chaîne de bumps v0.0.13 → v0.0.20 dans cette fenêtre.
* **Couverture :** 547 clés de traduction dans chacun des 6 fichiers — **parité totale des key sets**.
* **Réparation des Clés Corrompues 🆕 :** 51 clés "mangled" (le regex legacy du sync capturait des kwargs de call-site comme `fg="cyan"`) + 36 clés avec `\n` littéral double-échappé ont été réparées dans les 6 fichiers — **0 mangled, 0 non traduites** après audit AST de 638 clés.
* **i18n Complète de `--install` 🆕 :** Les 10 messages codés en dur de l'installeur MCP (`_run_install`, `_install_for_editor`) migrés vers `__()` avec kwargs nommés ; 34 nouvelles clés traduites.
* **Script de Synchronisation Corrigé 🆕 :** `tests/sync_i18n.py` — nouveau `PATTERN` pour le littéral de `__()` (ne capture plus le `)` du call-site), `ast.literal_eval` pour les séquences d'échappement, index `_live_key()` pour migrer les entrées legacy et garde anti-scan vide (n'écrase jamais avec zéro clé).
* **Cache avec Indexation par Langue :** Les réponses IA en cache incluent la langue courante dans le keying MD5.
* **Clés Identité par Conception :** 11 clés conservées en EN intentionnellement (prompts IA, marqueurs universels `[OK]`/`[FAIL]`, termes techniques).

### **11. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA affichant des caractères braille avec des mots de « réflexion ».
* **Délimiteur :** Séparateur de phrases par point-virgule (`;`), compatible avec les phrases complexes contenant des virgules.
* **Vitesse Adaptative & Flickering :** Animation de découverte de caractères adaptée aux phrases longues et utilisation de l'ANSI `\033[K` pour éviter les artefacts visuels dans le terminal.
* **263 entrées par langue :** Synchronisées entre les 5 langues.

### **12. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Prise en charge :** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Mesure de la Durée :** Injection de `duration_ms` (chronométrage haute précision via `time.perf_counter()`) dans `meta_raw` et `_telemetry_meta`.
* **Mode JSON & Paramètres Déterministes :** Sorties structurées avec `temperature=0.0` et `top_p=0.1`.

### **13. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Keying par hash MD5 du diff et du prompt.
* **Indexation par Langue :** Le champ `lang` a été ajouté au keying du cache.
* **Télémétrie et Durée :** Persistance des champs `duration_ms` et `meta_raw` dans les fichiers de cache.
* **Lecture pour le Dashboard :** `scan_cache_files_for_dashboard()` lit tous les fichiers de cache récursivement.

### **14. Moteur d'Issues et TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Moteurs de Contexte :** Diff actuel, Historique de la branche (`-ht`), et Archéologie par Blame (`-b`).
* **Map-Reduce pour les Issues :** Lorsque le contexte dépasse ~90k tokens, divise automatiquement en chunks et unifie les résultats.
* **TUI Interactive :** Édition de brouillons, raccourci F2 (enregistrer local), F3 (publier sur GitHub) et F1 (aide).
* **Gestion du 401 :** Signalisation de réauthentification sans fermer l'application.

### **15. Archéologue de Code (`src/blame_engine.py`)**

* **Git Blame + IA :** Suit l'évolution et la paternité historique des extraits de code avec classification des commits (`ORIGIN` vs `REFACTORING`).
* **Métriques de Blame :** Événements journalisés via `log_blame_metric()` avec suivi de la profondeur et du nombre de commits analysés.

### **16. Serveur MCP et Invocation CLI Directe (`src/mcp_server.py`)**

* **12 Outils MCP Annotés :** Outils pour `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **15 Ressources + 7 Prompts Templatisés :** 35 fichiers de template dans `templates/gitpr.prompt.*.md`.
* **Invocation CLI Directe :** La commande `gitpr-mcp --tool <name> [--tool-args '<json>']` invoque n'importe quel outil MCP directement sans démarrer le serveur stdio JSON-RPC.
* **Registry Pattern :** `_TOOL_FUNCS` mappe nom d'outil → callable ; `_get_tool_registry()` fusionne avec les métadonnées du catalogue.
* **Isolation du Stdout Réel :** `_write_real_stdout()` écrit directement dans le `sys.__stdout__` original (enregistré avant le monkey-patching), garantissant du JSON pur sur stdout.
* **Liste des Outils :** `gitpr-mcp --tool` (sans nom) liste les 12 outils disponibles avec leurs signatures de paramètres.
* **Chargement Automatique du .env :** Clés API automatiquement disponibles en mode CLI.
* **Offload de l'Event Loop 🆕 :** Décorateur `_offload` (`anyio.to_thread.run_sync`) appliqué aux 12 outils — les handlers synchrones ne gèlent plus le serveur stdio pendant les appels bloquants (cause racine du hang de `run_linter` dans Claude Code). `_TOOL_FUNCS` fait l'unwrap (`fn.__wrapped__`) en gardant le mode `--tool` CLI synchrone.
* **Warm-Import au Démarrage 🆕 :** Thread de pré-importation de `src.core` — le téléchargement OTA des smart-excludes ne retarde jamais le premier appel (import lock disputé dans un worker thread, jamais sur la boucle).
* **Aide de `--install` Corrigée 🆕 :** `claude-code` apparaît désormais dans la liste des éditeurs pris en charge de l'aide (était accepté dans `choices` mais omis du texte).
* **Tests E2E 🆕 :** `tests/test_mcp_server_e2e.py` démarre le vrai serveur comme subprocess et parle JSON-RPC stdio (initialize, `run_linter`, `get_git_context` — chaque réponse vérifiée en 60s), hermétique via `GITPR_SKIP_SMART_EXCLUDES=1`.
* **Installeur Automatique :** Configuration des éditeurs pris en charge (VS Code, Cursor, Claude Code, Claude Desktop, Zed) avec merge JSON intelligent.

### **17. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`)**

* **Périmètre par Dépôt (Repo-Scope) :** Étiquette `📁 Repository: owner/repo` et filtrage strict des événements et données de cache par projet.
* **Scan Asynchrone avec Overlay :** Worker thread en arrière-plan avec widget `ProgressBar`.
* **Consolidation des Données :** `load_cache_token_summary()` ajoute les tokens du cache au totalisateur.
* **Contrôle de l'État du Cache :** Fichier de registre dans `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Export Local :** Enregistrement CSV/JSON dans `./.gitpr/metrics/export/`.

### **18. Système de Métriques et Télémétrie (`src/metrics.py`)**

* **Périmètre par Dépôt :** Tous les événements indexés par `repo_name`.
* **Nouveaux Événements :** Événements de listage des fichiers unstaged et d'export de télémétrie.
* **Événements de Hook :** `log_hook_event()` pour les hooks Git (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge).
* **Événements de Linter et Blame :** `log_linter_metric()` et `log_blame_metric()`.
* **Export Local :** `--metrics --export` génère CSV et JSON dans `./.gitpr/metrics/export/` avec filtre par dépôt. 🆕 Exemples d'export (CSV/JSON) versionnés dans le dépôt et `.gitignore` ajusté — le dossier `.gitpr/reports/` n'est plus ignoré.
* **Nettoyage :** `--metrics --purge` supprime tous les fichiers de métriques locaux avec confirmation interactive.

### **19. Synchronisation des Git Hooks**

* **Versionnage Indépendant :** `__scripts_version__` (v0.0.3) contrôle la version des scripts de hook.
* **Détection Automatique :** Compare la version locale avec la plus récente et met à jour automatiquement.
* **Tenant Compte de la Langue :** Télécharge les templates de hook correspondant à la langue configurée.
* **Skip de Merge-Source :** Le template `prepare-commit-msg` (5 variantes de langue) utilise un case POSIX qui saute les sources `message|merge|squash|commit` et vérifie `.git/MERGE_HEAD` en belt-and-braces — les commits générés par git (`git pull`, `git merge`, `--amend`, `-c`/`-C`, `--squash`) préservent le message original de git.

### **20. Pont de Linters Externes et Assistant Interactif (`src/linter_wizard.py`, `src/ui/linter_app.py`) 🆕**

* **Assistant `--linter-setup` 🆕 :** Wizard interactif qui liste des presets numérotés (PHP_CodeSniffer, ESLint, Stylelint), affiche la commande d'installation native du linter et injecte le bloc `external_linters` dans `.gitpr.linter.yml` (avec déduplication et création du dossier `.gitpr/skill/`).
* **Presets Distants 🆕 :** `templates/gitpr.linter-presets.json` servi depuis GitHub avec une chaîne de résolution (copie locale à jour → téléchargement → copie stale → fallback `_LINTER_PRESETS` embarqué), versionné par le marqueur `LINTER_PRESETS_VERSION` — les nouveaux linters arrivent sans release.
* **TUI d'Erreurs du Linter 🆕 :** `src/ui/linter_app.py` (Textual) affiche les erreurs critiques et les warnings lorsqu'il y a des erreurs bloquantes hors hooks/quiet ; en hook/quiet, imprime et fait `sys.exit(1)` (blocage du commit préservé).
* **Rapport Markdown 🆕 :** `generate_linter_report_content()` consolide les erreurs regex + externes dans `.gitpr/reports/linter/` avec nom configurable via `OUTPUT_FILE_NAME_LINTER` — généré uniquement en cas de violations.
* **Périmètre Efficace 🆕 :** Les linters externes ne s'exécutent que lorsqu'il y a des fichiers modifiés avec une extension compatible ; YAML de configuration lu une fois par exécution.
* **Couverture de Tests 🆕 :** 13 scénarios dans `tests/test_external_linters.py` (parser XML, subprocess, croisement de diff, merge de config, générateur de rapport) + 4 tests de métriques avec mocks hermétiques.

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|------------------|----------|------|
| `tests/test_core.py` | 31 | Flux principaux, git diff, génération de PR, timing, merge en cours, staging, trailer de co-paternité |
| `tests/test_chat_backend.py` | 18 | Mémoire du chat, persistance, commandes slash |
| `tests/test_plugins.py` | 17 | Découverte de plugins, fusion de règles de linter, prompts MCP |
| `tests/test_mcp_server.py` | 82 | Outils MCP, ressources, annotations, patching, CLI direct, décorateur `_offload` |
| `tests/test_metrics.py` | 34 | Collecte, export local, périmètre de dépôt, cache token summary, duration_ms |
| `tests/test_smart_excludes.py` | 13 | Filtre pathspec intelligent |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompt MCP et fallback de langue |
| `tests/test_blame_metrics.py` | 4 | Métriques de blame : profondeur, commits, durée |
| `tests/test_linter_metrics.py` | 4 | Métriques de linter : erreurs, warnings, durée |
| `tests/test_thinking_words.py` | 3 | Chargement et parsing avec séparateur `;` |
| `tests/test_skill_command.py` | 3 | Téléchargement et validation des templates de skill |
| `tests/test_install_wizard.py` | 3 | Assistant interactif d'installation |
| `tests/test_pre_save.py` | 3 | Flag --pre-save et payload JSON |
| `tests/test_external_linters.py` | 13 🆕 | Pont Checkstyle : parser XML, subprocess, croisement de diff, rapport |
| `tests/test_i18n.py` | 15 🆕 | Parité entre langues, clés mangled, tronquées et orphelines, clés du modal de linter |
| `tests/test_mcp_server_e2e.py` | 6 🆕 | Vrai serveur MCP via subprocess + JSON-RPC stdio (initialize, run_linter, get_git_context) + mode `--tool` |
| `tests/test_pr_publish_linter_modal.py` | 4 🆕 | Modal d'erreur du linter : layout côte à côte, abort, no-verify, flux TUI complet avec commit `no_verify=True` |
| `tests/sync_i18n.py` | — | Script de vérification de couverture i18n (clés orphelines, extraction par littéral) |

**Total :** 264 scénarios de test automatisés qui passent (17 fichiers de test). Exécution complète vérifiée dans cette version : **264/264 passed en ~44s** — première exécution 100 % verte sur la machine pt-BR (les 2 échecs de locale préexistants dans `test_external_linters.py` ont été corrigés en fixant `TRANSLATIONS` à `{}` via `mock.patch`). Nouveaux tests : `TestExternalLinters` (13), `test_i18n.py` (15), `test_mcp_server_e2e.py` (6), `test_pr_publish_linter_modal.py` (4), `TestOffloadDecorator` (7) et `TestCoauthorTrailer` (5).

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n :** 547 clés de traduction dans chacun des 6 dictionnaires (+40 depuis le rapport précédent) avec **parité totale des key sets** — audit AST de 638 clés utilisées dans le code : 0 mangled, 0 non traduites.
* **Documents Mis à Jour 🆕 (tous en 5 langues) :**
  - `docs/ARCHITECTURE.md` — réécrit en EN canonique + 4 locales créés (`ARCHITECTURE.pt_br.md`, `.pt_pt.md`, `.es_es.md`, `.fr_fr.md`) : 18 sujets d'architecture, index de documentation avec 32 liens, note sur l'offload du MCP et le trailer de co-paternité
  - `docs/i18n_explanation.md` 🆕 — nouveau sujet sur le système d'internationalisation en 5 langues
  - `docs/linter-regras-customizadas.md` — nouvelles sections 5 (Pont Checkstyle) et 6 (Rapports Markdown) + bloc `external_linters` dans la structure YAML
  - `docs/commit-message-ia.md` — section « Co-Author Signature » avec exemple de console mis à jour
  - `docs/mcp-integration.md` — section « Alternative Entry Point (`gitpr --mcp`) » + `claude-code` dans la liste des éditeurs
  - `docs/pull-request-publication.md` — note d'injection du trailer par flux + tableau des composants corrigé (`FileStageScreen` → `StageFilesScreen`)
  - `docs/providers-ia.md` — synchronisé
  - `README.md` + 4 locales — sous-section « External Linters (Checkstyle Bridge) », ligne « Linter Report » dans la structure de sortie et puce du flag `--linter-setup`
  - `docs/caveman-commit.md` — supprimé : le sujet est devenu la skill locale `caveman-commit` (`.claude/skills/`)
* **Documentation en 5 langues :** 33 sujets canoniques dans `docs/` (29 avec couverture complète dans les 5 langues ; 4 sujets PT-only : `como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`).
* **Skills locales de Claude Code :** `.claude/skills/` avec `status-report` 🆕 (génération de ce rapport), `implement-fixes` 🆕 (flux de corrections) et `caveman-commit` 🆕 (messages de commit compacts) — en plus des existantes `new-feature` et `reports-to-memory`.
* **Memory Index :** `.claude/memory/MEMORY.md` avec 32 patterns dans 3 catégories (21 de projet, 3 de référence, 8 de feedback).
* **Rapports de tâches :** `docs/claude-code/reports/` (65 au total ; +15 nouveaux : linter externe, clés i18n corrompues, staging i18n + dead code + docs MCP, skills, README, co-author, rapport de linter conditionnel, ARCHITECTURE EN multilingue, co-author dans la TUI, hang du MCP, i18n de l'install wizard, i18n untranslated/mangled, modal d'erreur du linter) et `docs/gemini/reports/` (8, aucun nouveau dans cette fenêtre).
* **Rapports de statut :** `docs/reports/` (12 rapports de statut).
* **Plans de développement :** 59 fichiers documentés dans `docs/plans/` (+6 nouveaux : linter externe, clés i18n, ARCHITECTURE multilingue, hang du MCP ×2, corrections du modal de linter).

---

## **🔄 Pipeline de Distribution**

1. **PyPI :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases :** PyInstaller → `.exe` standalone → téléversement automatisé
3. **GitHub Actions :** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server :** Point d'entrée `gitpr-mcp` via `pyproject.toml`

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.11)**

| Domaine | v0.0.11 (précédent) | v0.0.12 (actuel) |
|------|-------------------|----------------|
| **Version GitPR** | 0.0.36 | **0.0.37** |
| **Version Langue** | v0.0.13 | **v0.0.20** |
| **Version Scripts Hook** | v0.0.2 | **v0.0.3** |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Langues** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 langues, 6 dictionnaires (es/fr dupliqués) |
| **Interface** | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI | **+ TUI d'erreurs du linter (`LinterApp`) + assistant `--linter-setup`** |
| **Outils MCP** | 12 outils (handlers inline sur l'event loop) | **12 outils (handlers déchargés vers des worker threads via anyio + tests e2e stdio)** |
| **Flags CLI** | 26 flags | **27 flags (+ `--linter-setup`)** |
| **Variables d'Environnement** | 16 vars | **23 vars (+ `OUTPUT_FILE_NAME_LINTER` dans DEFAULT_CONFIG (22 keys) + `GITPR_COAUTHOR` en lecture seule)** |
| **Linter** | Uniquement règles regex (local + plugins) | **Regex + pont Checkstyle (ESLint/PHPCS/Stylelint) avec croisement par lignes du diff, wizard, TUI et rapport Markdown** |
| **Messages de Commit** | Message pur de l'IA | **+ trailer `Co-Authored-By: Gitpr-cli` (idempotent, masqué de la TUI, opt-out `GITPR_COAUTHOR=false`)** |
| **i18n (clés par fichier)** | 507 en pt_BR (parité incomplète) | **547 × 6 fichiers avec parité totale — 0 mangled, 0 non traduites** |
| **Documentation** | 34 sujets | **33 sujets canoniques (29 avec 5 langues complètes) — 1 nouveau (i18n_explanation), 1 supprimé (caveman-commit → skill), 7 sujets mis à jour + ARCHITECTURE avec 4 nouveaux locales** |
| **Suite de Tests** | 214 scénarios (13 fichiers) | **264 scénarios (17 fichiers, +50) — première exécution 100 % verte sur la machine pt-BR** |
| **Commits depuis le rapport** | 4 commits | **17 commits** |
| **PRs mergés** | 2 PRs (#111, #114) | **8 PRs (#119, #122, #124, #127, #129, #131, #133, #135) + 2 PR_DESCs sans référence (i18n mangled, modal de linter)** |
| **Memory Index** | 27 patterns | **32 patterns dans 3 catégories (projet/référence/feedback)** |
| **Rapports de tâches** | 50 claude-code (+4 dans la fenêtre) | **65 claude-code (+15) et 8 gemini (0 nouveau)** |
| **Plans de développement** | 11+ | **59 (+6 dans la fenêtre)** |

---

## **🚧 Prochaines Étapes**

* **91 clés i18n encore manquantes :** Utilisées dans le code via `__()` mais absentes des dictionnaires (descriptions des outils MCP, chaînes de la TUI comme « ❌ Merge Conflict », messages d'updater/ai_providers/github_api) — elles retombent sur le fallback anglais. Les prompts IA doivent rester en EN par conception.
* **Garde `missing == 0` dans test_i18n.py :** Étendre les tests avec une assertion qui échoue lorsque de nouveaux `__()` sans entrée de dictionnaire apparaissent (aujourd'hui ne garde que la parité, les mangled et les clés identité).
* **Merge `develop_natan` → `main` :** Publier le bump `__lang_version__` v0.0.20 et les corrections de la TUI aux utilisateurs — les `langs/*.json` corrigés sont déjà sur `main` via `e2f0fa0` ; le marqueur est ce qui déclenche le refresh OTA.
* **Sanity manuel du flux TUI réel :** Un test end-to-end manuel du PR Publisher avec un diff qui casse le linter (les tests headless mockent git/AI).
* **Tests pour PR Publisher :** Couverture restante pour `pr_publish_app.py` et `github_api.py` (progrès : `test_pr_publish_linter_modal.py` couvre le flux du modal de linter).
* **Fournisseur Anthropic Claude :** Support direct de l'API Claude (`claude-sonnet-5`).
* **Graphiques ASCII/Textual dans le Dashboard :** Ajouter des histogrammes de temps et des graphiques de tendance de tokens dans la TUI de métriques.
* **Pipeline de Release sur GitHub Actions :** Automatisation complète du build PyInstaller et de l'envoi des assets vers GitHub Releases.
* **Commande `--init` locale :** Seed de `.gitpr/conf/` avec des templates de configuration locale (smart-excludes, linter, etc.).
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires.
* **Durcissement des subprocess et timeouts :** Remplacer le `shell=True` f-string de `_run_external_linter` par une liste shlex/argv ; limiter les timeouts du SDK IA dans `ai_providers.py` (~600s par défaut) ; appliquer le pattern DNS-bounding aux urllib de `i18n.py`/`ai_providers.py`.
* **Linters externes en mode full-file :** Support des `external_linters` dans `--input` et filtre par `file` dans le XML Checkstyle (aujourd'hui le croisement n'utilise que la ligne).
* **Documenter `LINTER_PRESETS_VERSION` :** Marqueur de version des presets dans `.env` (pattern Version Marker).
* **Références de docs cassées dans HELP_MAP :** `chat-interativo.md` (fichier réel : `understanding_chat_functionality.md`) et `metricas_analytics_dashboard.md` (réel : `metricas-telemetria.md`) — petit correctif.
* **CLAUDE.md obsolète :** Déclare encore la version 0.0.30 (réelle : 0.0.37) et mentionne le flag `--publish` qui n'existe plus — ARCHITECTURE.md est la référence la plus précise.
* **Scripts legacy d'i18n :** Les one-offs de `scripts/` (`fix_pt_br.py`, `fix_pt_br_pass2.py`, `final_fix.py`, `_temp_check_i18n.py`, `generate_lang_files.py`) contiennent des tables inertes de clés mangled — candidats à la suppression/l'archivage.

---

**Rapport généré le :** 2026-08-19  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
