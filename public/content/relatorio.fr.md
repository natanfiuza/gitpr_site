# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.15 (2026-09-17)**

## **📌 Aperçu Général**

**GitPR** est un outil CLI (Command Line Interface) avancé pour l'automatisation des processus Git à l'aide de l'Intelligence Artificielle (Google Gemini / DeepSeek / Ollama). L'objectif principal est d'agir comme un assistant intelligent local qui effectue des Code Reviews, génère des Pull Requests, des messages de commit sémantiques, audite la dette technique et injecte des bonnes pratiques dans le flux de travail du développeur (Shift Left).

**Nouveautés de cette version (v0.0.15) :**
- **Sous-commande `gitpr fix` — le review qui devient un patch applicable :** Ferme la boucle entre le review automatique et la correction. Le dernier review du cache alimente **un** appel d'IA, qui renvoie des constats dans des blocs encadrés ; l'extracteur valide chaque bloc comme un diff unifié, `git apply --check` prouve qu'il s'insère dans l'arbre actuel et un classificateur **déterministe et sans I/O** étiquette chaque candidat comme `safe`, `review_required` ou `experimental`. Le dry run est le défaut — écrire exige `--apply`. Le `--force` ne contourne jamais la vérification d'applicabilité, seulement la classification, et exige une phrase de confirmation saisie. Tout ce qui a été appliqué entre dans `.gitpr/fix_history.json`, qui est ce que lit le `--rollback`.
- **Sous-commande `gitpr review-pr <n>` — réviser le PR d'un tiers sans checkout :** Le diff vient directement de l'API de la forge et entre **dans le même moteur** que celui utilisé par les flux locaux — même rapport, mêmes règles de linter, même `.txt`. En lecture seule par défaut : rien n'est publié sur la forge sans `--post-comment` explicite. Il élargit le public cible de « celui qui va ouvrir un PR » à « celui qui a été invité à réviser le PR de quelqu'un d'autre ».
- **Résolution de l'identité du reviewer — l'attach qui n'arrivait pas :** Les suggestions naissent du `git blame`, donc elles portent des **noms et des e-mails**, pas des logins. Quand rien ne se résolvait, l'UI affichait le nom nu — et ressaisir ce nom faisait envoyer par GitPR *verbatim* comme s'il s'agissait d'un login. GitHub répond **201 sans attacher personne** : succès apparent, reviewer absent, aucun avertissement. Désormais une couche dédiée résout l'identité **deux fois** (avant la TUI et à l'attach) et ferme aussi la seconde défaillance silencieuse de l'API — un login accepté mais non attaché est désormais détecté en relisant `requested_reviewers`.
- **La couche SCM a gagné les primitives pour réviser un review qui n'est pas sur le disque :** `get_pull_request(repo, pr_id)` est devenu une méthode **concrète** de l'ABC (le patron de `create_release`) et a été implémenté dans les quatre forges — `list_open_pull_requests` pagine **une seule page**, donc la filtrer par numéro perd des PRs anciens et ne distingue pas fermé d'inexistant. L'attribut de classe `supports_reviewable_diff` (`False` sur Azure DevOps, dont l'API renvoie une liste de fichiers et non un diff) bloque le review **avant tout appel réseau**.
- **Deux défauts latents de GitLab corrigés :** `changes[].diff` est un hunk isolé, donc le chemin du fichier était écarté — l'IA réviserait des hunks orphelins et ni le chunker ni le filtre d'exclusion ne fonctionneraient ; les en-têtes `diff --git / --- / +++` sont désormais synthétisés à partir de `old_path`/`new_path`. Et `overflow: true` était ignoré : un diff tronqué était révisé à moitié et publié comme s'il était entier — il lève désormais.
- **`gitpr fix` corrige désormais le review qui a été révisé :** Le diff révisé est consigné dans l'enregistrement de cache (`reviewed_diff`) et le `fix` le préfère, ne retombant sur la re-dérivation que pour les anciens enregistrements. C'est la seule source correcte quand le review vient d'un PR distant ou d'un diff de branche entière.
- **MCP est passé de 12 à 14 outils et de 17 à 18 ressources :** `list_fix_candidates` (13e, en lecture seule) + `skill://fix`, et `review_remote_pr` (14e, en lecture seule, sans argument `post_comment`, sans écrire de `.txt`).
- **i18n étendue à 1048 clés :** +93 depuis le rapport précédent (955 → 1022 avec le `fix` → 1028 avec les reviewers → 1048 avec le `review-pr`) ; `__lang_version__` est passée de v0.0.25 à **v0.0.28** (chaîne v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28) et les 6 dictionnaires conservent une **parité totale des key sets**.
- **Documentation :** 2 nouvelles familles — `fix-command` (5 langues) et `review-pr` (EN + PT-BR) — et 9 sujets mis à jour, dont `code-review-ia` (mode distant comme §1.4), `suggested-reviewers` (résolution du login) et le §2.2 de `fix-command` réécrit dans les 5 versions, parce que le diff n'est plus re-dérivé.
- **Hygiène du dépôt :** un arbre pip 25.2 auto-vendorisé (`pypa/`, `pip/cache/http-v2/`) avait été commité par erreur et a été supprimé ; le `.gitignore` a gagné `pypa/` et `pip/`.
- **Saut de version vers 1.2.0 :** le bump est dans le **working tree et n'a encore été ni commité ni tagué** (HEAD reste à 1.1.0 ; le dernier tag est `v1.1.0`), et le `CHANGELOG.md` s'arrête encore à `[1.1.0] - 2026-09-13`.

- **Version actuelle :** 1.2.0 (bump dans le working tree — HEAD à 1.1.0)
- **Version des dictionnaires de langue :** v0.0.28
- **Version des scripts de hook :** v0.0.3
- **Publication :** PyPI (`pip install gitpr-cli`) — canal binaire supprimé dans la fenêtre précédente
- **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Dépôt :** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **Licence :** LGPL-2.1
- **Langues prises en charge :** en_us, pt_br, pt_pt, es_es, fr_fr (5 langues, 6 dictionnaires)

---

## **🏗️ Architecture et Bibliothèques de Base**

* **Langage :** Python >= 3.10
* **CLI Framework :** Click (pour les commandes, flags et formatage du terminal).
* **UI/Terminal :** Textual — TUI pour le chat interactif, l'édition d'issues, l'écran d'aide, le tableau de bord de métriques, le PR Publisher, les erreurs du linter (`LinterApp`), la configuration (`ConfigApp`) et le nouveau modal `NoticeScreen` d'avis du PR Publisher 🆕.
* **Cryptographie :** `cryptography.fernet` pour la protection locale des clés API, des jetons GitHub et des jetons SCM des forges — les secrets édités dans la TUI de configuration sont également chiffrés avant d'être écrits.
* **Configuration :** `python-dotenv`, `pyyaml` (pour le linter statique) + son propre schéma déclaratif (`src/config_schema.py`).
* **Fournisseurs IA :** Intégration via le SDK officiel Google GenAI (`gemini-2.5-flash`), le SDK OpenAI (`DeepSeek`) et le SDK OpenAI (`Ollama` local).
* **APIs de Forge :** `requests` (REST) — couche d'abstraction multi-forge dans `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) ; module legacy `src/github_api.py` conservé comme shim déprécié.
* **MCP :** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (SDK officiel Anthropic pour Model Context Protocol) — **14 outils annotés** 🆕, **18 ressources** 🆕, 7 prompts ; handlers déchargés vers des threads via `anyio`.
* **Tests :** Pytest + `unittest.mock` (**64 modules de test — 41 à la racine, 9 dans `tests/scm/`, 10 dans `tests/fix/` et 4 dans `tests/review/`** 🆕 —, 1437 scénarios collectés) + tests e2e du serveur MCP via subprocess réel (JSON-RPC stdio) + fixtures de dépôt git réel dans `tests/fix/git_fixture.py` 🆕.
* **Empaquetage :** setuptools/build (PyPI).
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
* **Smart Excludes à Deux Couches :** Filtre pathspec intelligent avec couche globale (`~/.gitpr/conf/`) + locale du projet (`./.gitpr/conf/`). Fusion au runtime (union, dédupliquée). Auto-seed du fichier local à la première exécution. `_load_smart_excludes()` accepte `force=` pour un re-téléchargement à la demande depuis la TUI de configuration. 🆕 Le template `templates/gitpr.smart-excludes.json` a gagné `.gitpr/fix_history.json` — un patch appliqué salit un fichier **suivi**, donc sans cela il apparaîtrait dans les diffs de `gitpr -c` et dans les descriptions de PR.
* **Métriques avec Suivi du Temps :** Injection de `log_command_metric()` dans tous les flux avec transmission de la durée en millisecondes (`duration_ms`) et imports paresseux.
* **Résolution Centralisée de la Sortie :** Fonction `resolve_output_path()` qui centralise la logique des répertoires de sortie — par défaut dans `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`) :** `gitpr --init` — détecte la forge depuis le remote origin, demande les extras propres à chaque forge (Azure org/project, username Bitbucket), valide le jeton avec `test_connection` (3 tentatives, re-prompt sur 401) et persiste `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` (Fernet) **uniquement en cas de succès**.
* **Template de Skill de Release (`ensure_release_skill_template()`) :** Télécharge `templates/gitpr.release.*.md` à la première utilisation de `gitpr release` (couche CLI, tenant compte de la langue, n'écrase jamais ; sauté avec `--format json`).
* **Registre de Skills Partagé :** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` vivent dans `src/config.py` (la TUI ne peut pas importer `core` au niveau racine — cela tire les SDK d'IA) ; `get_skill_context()` utilise `skill_file_for()`. 🆕 `files_to_download` a eu besoin de sa propre entrée pour `gitpr.fix.md` — toucher seulement `SKILL_FILES_BY_TYPE` ne suffisait pas pour que `--skill` télécharge le template.
* **Moteur de Review avec Périmètre de Cache (`generate_pr_content`) 🆕 :** Deux paramètres **additifs** — `cache_scope` (attaché **uniquement à la clé de cache**, jamais au prompt) et `store_diff` (écrit le diff révisé dans l'enregistrement). Les défauts `""`/`False` gardent le chemin local **identique au byte près** : zéro invalidation de cache pour les reviews locaux déjà existants. C'est ce qui permet à un review distant d'être scopé par `::diff-source::pr-<n>` sans qu'un review local du même diff y réponde par erreur.
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
* **Routage des Commandes :** Gère tous les flags et les **4 sous-commandes** — `release`, `config`, `fix` 🆕 et `review-pr` 🆕.
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
* **Sous-commande `fix` 🆕 — 8 options :** `--list` (liste les candidats du dernier review — ce que la commande fait sans argument), `--apply` (écrit dans l'arbre ; sans lui, c'est un dry run), `--all-safe` (sélectionne tous les `safe` ; écrire exige encore `--apply`), `--create-branch <name>`, `--no-branch`, `--yes` (saute la confirmation, **ne contourne jamais** le `--force`) et `--force` (applique un patch non sûr après une phrase saisie) et `--rollback <patch-id>`. Argument optionnel `[<finding-id>]`. Moule exact de `release` : imports paresseux dans le corps et `epilog` pour `get_doc_url("fix-command.md")`. Aucun flag existant n'a changé de sens — le `--force` de `release` (« regénérer une section existante ») n'entre pas en collision parce que les namespaces des sous-commandes sont séparés.
* **Sous-commande `review-pr` 🆕 — 2 options :** `review-pr <number>` avec `--provider <name>` et `--post-comment` ; en lecture seule par défaut, **n'appelle jamais** `check_unstaged_files`, écrit `{branch}_{datetime}_PR_REVIEW.txt` avec le nom de la branche source du PR. Rejette le PR **avant tout appel d'IA** pour la capacité, l'existence, l'état, un diff vide/non révisable et l'épuisement des smart-excludes. Réutilise `_resolve_scm_context`.
* **Variables d'Environnement (44 clés dans `DEFAULT_CONFIG`, +5 dans cette fenêtre 🆕) :** les cinq `GITPR_FIX_*` — `GITPR_FIX_SAFE_MAX_LINES_CHANGED`, `GITPR_FIX_SAFE_EXCLUDED_PATHS`, `GITPR_FIX_REQUIRE_CONFIRMATION`, `GITPR_FIX_CREATE_BRANCH_ON_ALL_SAFE` et `GITPR_FIX_BRANCH_NAME_TEMPLATE`. `get_fix_settings()` renvoie le bloc aplati et **retombe sur le défaut intégré** quand la valeur est vidée par accident — vider le champ ne peut pas faire tomber la protection en silence.
* **Aide Contextuelle :** `-h --flag` affiche la documentation spécifique de la fonctionnalité avec un lien direct (tenant compte de la langue) vers GitHub. Les sous-commandes ont leur propre `epilog=` (paragraphe `\b` de Click pour que l'URL ne soit ré-enroulée sous aucune locale).
* **--lang :** Force la langue de l'interface pour l'exécution en cours sans persister le changement.
* **--provider :** Force le fournisseur d'IA (`gemini`, `deepseek`, `ollama`) pour l'exécution en cours.
* **--mcp :** Démarre le serveur MCP sur le transport stdio pour l'intégration avec les éditeurs — **14 outils annotés + 18 ressources + 7 prompts** 🆕.
* **--install :** Assistant guidé en 4 étapes qui télécharge des templates de skill, installe des Git Hooks, configure MCP dans les éditeurs et valide les clés API.
* **--metrics :** Système de télémétrie locale avec périmètre par dépôt : `--export`, `--purge`, `--dashboard`.
* **--status :** Liste les fichiers non commités catégorisés (new/modified/deleted) — rapide, sans IA, sans réseau.
* **Couche d'Écriture du `.env` :** `read_env_file_values()` lit **uniquement le fichier** via `dotenv_values` (immunisé contre `os.environ`), `save_config_values()` écrit avec `set_key`, `remove_config_value()` avec `unset_key`. `validate_ai_key()` sonde les SDK Gemini/DeepSeek avec des timeouts courts et distingue un identifiant refusé (`401`/`403`) d'un réseau injoignable.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` et `src/ui/pr_publish_help.py`)**

* **Interface Interactive Complète :** TUI construite avec Textual pour réviser, éditer et publier des Pull Requests directement dans le terminal.
* **7 Écrans Modaux :** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` et **`NoticeScreen`** 🆕.
* **Avis Bloquants (`NoticeScreen`) 🆕 :** Un modal qui exige une reconnaissance (Échap ou Close) avant que le flux de merge puisse continuer. C'est intentionnel : sans lui, le prompt de merge prendrait l'écran et les avis de reviewer écarté passeraient inaperçus — mais il met en pause les flux automatisés quand il y a des avis.
* **`_attach_reviewers` avec résolution 🆕 :** Résout ce que l'utilisateur a saisi **avant** l'envoi, signale ce qui a été écarté et, sur un `422` de lot avec plusieurs reviewers, **réessaie un par un** — GitHub rejette le lot entier quand un seul login est inéligible (l'auteur du PR, un non-collaborateur), ce qui auparavant faisait tomber aussi les reviewers valides.
* **Reviewers Suggérés :** Le flux de publication interroge la forge pour obtenir des reviewers suggérés et les propose dans la TUI ; sélection contrôlée par `--no-suggest-reviewers`, `GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N` et `GITPR_REVIEWER_SUGGESTION_EXCLUDED`. 🆕 `_reviewer_suggestion_view()` monte les `resolutions` via `resolve_candidates`, pré-remplit `handles` et marque ceux qui n'ont pas de compte pour que les lignes de hint le signalent ; les `resolutions` voyagent dans la vue pour que l'attach ne résolve pas les mêmes personnes deux fois.
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
* **`skip_external` — le pont ne peut pas linter le mauvais arbre 🆕 :** `parse_diff_and_lint(..., skip_external=False)` a gagné le paramètre qui désactive les **deux** call-sites du pont externe. Le flux distant passe `True`, parce que le pont exécute des binaires **contre des fichiers sur le disque** — l'arbre local de l'utilisateur, pas le PR — et ces alertes seraient publiées comme commentaire public sur le PR de quelqu'un d'autre.
* **Rapport Consolidé :** `generate_linter_report_content()` consolide les erreurs regex + externes dans `.gitpr/reports/linter/` — généré uniquement en cas de violations.
* `load_linter_presets()` accepte `force=` pour re-télécharger les presets depuis la TUI.

### **7. Sécurité et Authentification (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Chiffrement :** Génère une clé maîtresse `secret.key` dans le dossier `~/.gitpr/`.
* **Protection des Jetons :** `encrypt_data` et `decrypt_data` pour protéger les clés API d'IA, les PAT GitHub et les jetons SCM des forges (`GITPR_SCM_TOKEN_ENCRYPTED`).
* **Validation Multi-Forge :** `validate_or_request_scm_token(provider, repo_display)` — valide le jeton auprès de la forge configurée avec une boucle 401 → réauthentification préservant le brouillon ; le jeton GitHub legacy (`GITHUB_TOKEN_ENCRYPTED`) reste fonctionnel jusqu'à l'exécution de `--init`.
* **Secrets dans la TUI de Configuration :** Les champs `KIND_SECRET` sont édités dans un champ masqué, **n'affichent jamais** la valeur en clair et sont chiffrés avec Fernet avant d'être écrits — aucun chemin ne relit le secret vers l'écran. `GITPR_SCM_TOKEN` est `read_only` et sa description pointe vers `gitpr --init` comme seul chemin qui devrait l'écrire.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI comme Source Unique :** `get_latest_remote_version()` interroge toujours `https://pypi.org/pypi/gitpr-cli/json`, retourne une **chaîne** de version et écrit le cache quotidien **sans** le champ `download_url`.
* **Barrière Obligatoire (`enforce_update_required()`) :** Retourne `True` (après avoir affiché les deux versions et la commande pip) lorsque la version publiée est plus récente ; retourne `False` lorsque tout est à jour, lorsque la version distante est **inconnue (hors ligne — l'utilisateur n'aurait aucun moyen de mettre à jour)** ou lorsque la vérification est désactivée. Retourner un `bool` au lieu d'appeler `sys.exit` en interne garde la fonction testable.
* **`check_and_update()` :** Se contente d'interroger et de **rapporter**, n'installe jamais.
* **Échappatoire :** `GITPR_SKIP_UPDATE_CHECK` (toute valeur non vide) — non annoncée à l'utilisateur comme fonctionnalité ; elle existe pour la suite de tests et l'automatisation hors ligne.
* **Cache Quotidien :** Évite les vérifications répétées le même jour.
* **Versionnage Centralisé :** `__version__` (**1.2.0** — bump dans le working tree), `__lang_version__` (**v0.0.28** — chaîne v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28 dans cette fenêtre 🆕), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.

### **9. Interface de Chat Interactive (`src/ui/chat_app.py`)**

* **TUI Complète :** Construite avec Textual — historique des messages, saisie multi-ligne, barre d'état avec bindings visibles.
* **Mémoire par Branche (`src/chat_memory.py`) :** Historique de conversation persisté par branche, permettant la continuité entre les sessions.
* **Commandes Slash :** `/explain`, `/tests`, `/optimize`, `/clear` — raccourcis pour le pair programming.
* **Auto-Patching (F5), Rafraîchissement du Diff (F2), Export de Session (F6).**
* **Extracteur Partagé 🆕 :** Le bloc de la regex était **dupliqué** dans F5 et `ctrl+s` ; les deux appellent désormais `patch_extractor.extract_code_blocks()`. Le fichier a perdu 31 lignes et en a gagné 3, avec un **comportement visible identique** (mêmes touches, même `GITPR_PATCH_SUGGESTION_<key>.txt`, même contenu) — c'est pourquoi il n'y a pas de note de changelog sur le chat. C'est aussi la raison pour laquelle le `__init__.py` de `src/fix/` est **docstring seulement** : le chat l'importe à chaque session, et un `__init__` qui réexporterait tirerait avec lui les couches d'IA et de git (voir ADR-004, alternative rejetée).

### **10. Internationalisation — i18n (`src/i18n.py`)**

* **Système Inspiré de Laravel :** Fonction `__()` avec prise en charge des placeholders nommés (`{count}`, `{file}`, etc.).
* **Détection Automatique :** Détecte la langue du système à la première exécution et l'enregistre dans `GITPR_LANG`.
* **5 Langues, 6 Dictionnaires :** en_us (défaut/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Fichiers Versionnés :** `__lang_version__` (**v0.0.28**) contrôle la mise à jour des packs de langue (`langs/*.json`) — chaîne de bumps v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28 dans cette fenêtre.
* **Couverture :** **1048 clés** de traduction dans chacun des 6 fichiers — **parité totale des key sets** (+93 depuis le rapport précédent).
* **Photo de l'Environnement (`AMBIENT_ENV_KEYS`) :** `frozenset(os.environ)` capturé dans `i18n.py` **immédiatement avant** le `load_dotenv()` au niveau du module — corrige le badge « ⚠ dans l'environnement » qui confirmait tautologiquement que la clé est dans le fichier.
* **Clé Réécrite 🆕 :** `GitHub usernames, comma separated` → `GitHub login, name or email, comma separated` — le champ a cessé de promettre ce qu'il n'acceptait pas.
* **Cache avec Indexation par Langue :** Les réponses IA en cache incluent la langue courante dans le keying MD5.

### **11. Spinner Animé (`src/spinner.py`)**

* **Braille + Thinking Words :** Thread en arrière-plan pendant les appels IA affichant des caractères braille avec des mots de « réflexion ».
* **263 entrées par langue :** Synchronisées entre les 5 langues. `_load_thinking_words()` / `reload_thinking_words()` acceptent `force=`.

### **12. Fournisseurs IA (`src/ai_providers.py`)**

* **3 Fournisseurs Prise en charge :** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Mode JSON & Paramètres Déterministes :** Sorties structurées avec `temperature=0.0` et `top_p=0.1` ; fallback automatique entre les fournisseurs configurés.

### **13. Cache Intelligent (`src/cache.py`)**

* **MD5 + Métadonnées :** Keying par hash MD5 du diff et du prompt, avec indexation par langue.
* **Sélection du Dernier Review (`resolve_last_review()`) 🆕 :** Avec `REVIEW_ACTION_TYPES`, choisit l'enregistrement `review`/`fullreview` **le plus récent** pour un couple repo+branche, **en excluant les reviews à périmètre de fichier** (`-i`), qui ne décrivent pas la branche. C'est la porte d'entrée de `gitpr fix`.
* **Diff Révisé (`reviewed_diff`) 🆕 :** Champ en tête d'enregistrement qui conserve le diff effectivement révisé. Préféré par `fix/apply_fix.reviewed_diff()`, avec fallback vers la re-dérivation pour les anciens enregistrements — la seule source correcte quand le review vient d'un PR distant ou d'un diff de branche entière.
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

* **14 Outils MCP Annotés 🆕 :** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, **`list_fix_candidates`** (13e — candidats du dernier review avec patch, classification et id ; en lecture seule) et **`review_remote_pr`** (14e — review d'un PR ouvert sur la forge, récupéré par numéro ; en lecture seule, **ne commente jamais** et **n'écrit jamais** de fichier, résout la forge tout seul).
* **18 Ressources + 7 Prompts Templatisés 🆕 :** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` (**`skill://fix` est nouveau**) + `linter://config` + `prompt://list` + 7 prompts.
* **Invocation CLI Directe :** La commande `gitpr-mcp --tool <name> [--tool-args '<json>']` invoque n'importe quel outil MCP directement sans démarrer le serveur stdio JSON-RPC. `gitpr-mcp --list` affiche le registre complet en JSON.
* **Isolation du Stdout Réel :** `_write_real_stdout()` écrit directement dans le `sys.__stdout__` original, garantissant du JSON pur sur stdout — la raison pour laquelle le journal d'utilisation **n'affiche jamais**.
* **Offload de l'Event Loop :** Décorateur `_offload` (`anyio.to_thread.run_sync`) appliqué aux 14 outils — les handlers synchrones ne gèlent pas le serveur stdio.
* **Journal d'Utilisation :** Le `main()` du serveur appelle `log_usage()` — le script console `gitpr-mcp` ne charge jamais `main.py`, donc c'est le seul point qui l'atteint.
* **Tests E2E :** `tests/test_mcp_server_e2e.py` démarre le vrai serveur comme subprocess et parle JSON-RPC stdio.

### **17. Tableau de Bord de Métriques TUI (`src/ui/metrics_app.py`)**

* **Périmètre par Dépôt (Repo-Scope) :** Étiquette `📁 Repository: owner/repo` et filtrage strict par projet.
* **Scan Asynchrone avec Overlay :** Worker thread en arrière-plan avec widget `ProgressBar`.
* **Consolidation des Données :** `load_cache_token_summary()` ajoute les tokens du cache au totalisateur.
* **Export Local :** Enregistrement CSV/JSON dans `./.gitpr/metrics/export/` — 🆕 les artefacts `gitpr_metrics_2026-09-17.csv`/`.json` sont entrés dans le suivi git via le PR #171 et sont candidats au `.gitignore`.

### **18. Système de Métriques et Télémétrie (`src/metrics.py`)**

* **Périmètre par Dépôt :** Tous les événements indexés par `repo_name`.
* **Événements de Hook, Linter et Blame :** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Export et Nettoyage :** `--metrics --export` (CSV/JSON) et `--metrics --purge` avec confirmation interactive.

### **19. Synchronisation de la Langue des Git Hooks**

* **Versionnage Indépendant :** `__scripts_version__` (v0.0.3) contrôle la version des scripts de hook ; détection et mise à jour automatiques.
* **Correspondance des Suffixes (`HOOK_SCRIPT_SUFFIXES`) :** Les codes d'interface (`es_es`, `fr_fr`) sont traduits vers les suffixes réellement publiés (`.es`, `.fr`).
* **Choix vs. État (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`) :** `SCRIPTS_LANG` est le choix de l'utilisateur ; `SCRIPTS_INSTALLED_LANG` est ce qui se trouve sur le disque. Séparés, l'auto-synchronisation peut **détecter un changement de langue**.
* **`effective_hook_lang()` :** Résout la langue effective des hooks ; `--lang` n'est plus écarté sur ce chemin (changement de comportement documenté).
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
* **`get_pull_request(repo, pr_id)` — méthode concrète de l'ABC 🆕 :** Le patron de `create_release` : le défaut lève `ScmNotSupportedError` et chacune des quatre forges l'implémente. Elle existe parce que `list_open_pull_requests` pagine **une seule page** — la filtrer par numéro perd des PRs anciens en silence et **ne distingue pas fermé d'inexistant**, et le `review-pr` doit rejeter le PR pour la bonne raison avant de dépenser de l'IA.
* **`supports_reviewable_diff` — barrière de capacité 🆕 :** Attribut de classe, `False` sur Azure DevOps, vérifié **avant tout appel réseau**. L'API REST d'Azure renvoie une **liste de fichiers**, pas un diff unifié — réviser reviendrait à inventer du contenu.
* **En-têtes GitLab Synthétisés 🆕 :** `changes[].diff` est un **hunk isolé** ; `old_path`/`new_path` étaient ignorés, donc le chemin du fichier se perdait — l'IA réviserait des hunks orphelins et ni le chunker ni le filtre d'exclusion n'auraient sur quoi s'appuyer. Le provider assemble désormais les en-têtes `diff --git a/… / --- / +++`, en honorant `/dev/null` pour les fichiers ajoutés/supprimés.
* **L'`overflow` de GitLab Lève 🆕 :** Un MR dont le diff dépasse la limite de l'API était révisé **à moitié** et publié comme s'il était entier ; il lève désormais `ScmProviderError` avec un message clair.
* **`request_pull_request_reviewers` renvoie `list[str]` 🆕 (rupture de contrat) :** Il retournait `None` ; il retourne désormais les logins **effectivement attachés**, lus dans le corps du `201`. C'est la seule façon de détecter un login accepté et silencieusement ignoré. `None` reste traité comme « impossible de vérifier », jamais comme un échec — les anciens callers ne cassent pas, ils perdent seulement le read-back. Les sous-classes tierces **doivent** être mises à jour.
* **Deux Helpers en Lecture Seule de GitHub 🆕 :** `get_commit_author_login` (associe un SHA au compte lié à l'e-mail de l'auteur — le chemin fiable pour les adresses d'entreprise que l'API de recherche d'utilisateurs ne voit pas) et `get_user_login` (valide/canonicalise un handle saisi, rejette ce qui ne peut pas être un login **sans dépenser de requête** et distingue un 404 d'une erreur transitoire).
* **Fail-Fast par Forge :** Azure DevOps exige `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` ; Bitbucket exige `GITPR_SCM_USERNAME` (App Password = Basic auth user+token) ; `create_issue` sur Azure lève `ScmNotSupportedError`.
* **Publication de Release :** `provider.create_release()` utilisé par `gitpr release --publish` (GitHub crée le tag sur la branche par défaut ; GitLab exige que le tag existe).
* **Artefacts :** Glossaire + ADR-001/ADR-005 dans `docs/plans/` ; famille `docs/scm-multiforge.*.md` en 5 langues ; tests : 9 fichiers, **282 scénarios** (+17 dans cette fenêtre).

### **22. Sous-commande `gitpr release` — Changelog / Release Notes**

* **Flux :** `git log` entre `--since` (défaut : dernier tag atteignable, ou le premier commit) et `HEAD` → classification par Conventional Commits → bump sémantique suggéré (`--version <x.y.z>` le remplace) → assemblage du changelog → résumé exécutif IA optionnel → *insertion en tête* de `CHANGELOG.md`. La génération locale est le défaut — rien n'est publié ni modifié sans demande.
* **Classifier (`src/commit_classifier.py`) :** Classe les commits par type Conventional Commits (feat/fix/refactor/docs/chore/etc.) avec un parser tolérant.
* **Builder avec Sections Traduisibles (`src/changelog_builder.py`) :** Les sections "✨ Features", "🐛 Bug Fixes", "⚠️ Breaking Changes", "Summary", "Contributors" etc. sont rendues via `__()` au runtime (elles suivent `--lang`).
* **Bump Sémantique (`src/version_bump.py`) :** Suggère la prochaine version à partir des types classifiés (major pour breaking, minor pour feat, patch pour fix) et valide les cibles `x.y.z`.
* **Publication :** `--publish` crée la release sur la forge configurée (avec confirmation explicite) ; `--draft` la crée en brouillon (GitHub ; GitLab n'a pas de concept de brouillon) ; `--format markdown|json` pour une sortie structurée ; `--force` pour réécrire. **6 options dans la sous-commande.**
* **Template de Skill :** À la première utilisation, télécharge `templates/gitpr.release.*.md` (5 langues) via `ensure_release_skill_template()` — n'écrase jamais.
* **Point de release en attente 🆕 :** Le `CHANGELOG.md` **n'a pas** été touché dans cette fenêtre — la dernière entrée reste `[1.1.0] - 2026-09-13`, alors que `__version__` dit déjà 1.2.0. Lancer `gitpr release` est l'étape manquante.
* **Artefacts :** Famille `docs/release-notes.*.md` (5 langues), spec dans `docs/plans/`, ADR-002 et ADR-003, glossaire des release notes.

### **23. Sous-commande `gitpr config` — TUI de Configuration**

* **Écran Maître-Détail (`src/ui/config_app.py`) :** Catégories à gauche, champs de la catégorie à droite, édités en ligne. En-tête avec recherche (`/`) et compteur de modifications en attente (`● N non enregistrées`) ; pied de page avec `F1 Aide · F2 Enregistrer · ^R Restaurer · / Rechercher · Échap`. `Général` est toujours la première entrée du menu.
* **Schéma Déclaratif (`src/config_schema.py`) — l'unique source de vérité :** 🆕 **13 catégories** (Général, Fournisseurs d'IA, Pull Request, Révision de Code, Issue, Blame, Linter, Release, SCM / Forge, Filtres de Diff, Skills, **Correction** et Avancé) et **61 `ConfigField`**, dont **8 avancés** (+1 catégorie et +5 champs dans cette fenêtre). Chaque champ déclare sa catégorie, son type de widget (`bool`/`int`/`str`/`enum`/`template`/`path`/`secret`/`version`/`words`), ses `show_if`, ses validateurs, ses marqueurs de version et ses actions de téléchargement. Les libellés sont des littéraux `__()` pour le scanner i18n.
* **Catégorie `fix` 🆕 :** Les cinq `GITPR_FIX_*` ont gagné une surface éditable avec des descriptions qui expliquent la conséquence de chacun (budget de lignes, globs sensibles, exiger une confirmation, créer une branche sur le lot all-safe et le template du nom de branche).
* **Filtrage par Contexte (`show_if`) :** `GEMINI_*` / `DEEPSEEK_*` / `OLLAMA_*` apparaissent selon le `DEFAULT_AI_PROVIDER` sélectionné ; `GITHUB_TOKEN_ENCRYPTED` apparaît avec un fournisseur vide ou `github` ; `GITPR_SCM_USERNAME` sous `[Bitbucket]` ; `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` sous `[Azure DevOps]`. Changer le `Select` refiltre le panneau **immédiatement, sans F2**.
* **Recherche Globale (`/`) :** Correspond à la clé ou au libellé dans toutes les catégories et **ignore le filtre de visibilité** — chercher `deepseek` avec Gemini sélectionné trouve les champs, pour permettre de les pré-remplir.
* **Validation en Deux Couches :** **Hors ligne** (type, enum, template avec un placeholder connu et `{datetime}` obligatoire) bloque `F2` avec une erreur en ligne ; **en ligne** (uniquement pour les identifiants modifiés dans la session) s'exécute dans un worker avec un timeout de 10s et ne bloque que sur `401`/`403` — un échec réseau permet quand même d'enregistrer.
* **Restaurer (`Ctrl+R`) :** Supprime la ligne du `.env` au lieu de réécrire le défaut ; **`Esc`** avec des modifications en attente demande confirmation ; la catégorie **Inconnues** préserve les clés hors schéma en lecture seule.
* **Section Skills — la seule au périmètre du projet :** Panneau maître-détail en ligne qui édite les `.gitpr/skill/*.md` du projet résolus depuis le répertoire appelant, en écrivant de manière atomique et en préservant CRLF/LF. `F2` écrit le `.env` et les fichiers de skill **dans la même passe**.
* **Téléchargements Forcés :** Boutons qui forcent le re-téléchargement des smart-excludes, des traductions, des presets de linter et des thinking words, via un paramètre `force=` chaîné à travers les loaders.
* **Module Léger de Liens (`src/doc_links.py`) :** `doc_url()` déplacé hors de `core.py` pour que l'UI puisse obtenir le lien de documentation sans importer `core`/les SDK d'IA.
* **Tests :** 5 fichiers — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9). 🆕 Le `test_config_schema.py` couvre désormais les nouveaux champs (l'assertion selon laquelle `advanced` n'existe qu'en Avancé reste valable pour les 61).
* **Artefacts :** `docs/config-tui.*.md` en 5 langues, plan `docs/plans/20260912_config_tui.md`, glossaire `glossary-config-tui.md` (8 termes) et l'enquête grill.
* **Dette Connue :** `gitpr -h config` ouvre la TUI et ignore `-h` — la barrière `if ctx.invoked_subcommand is not None: return` s'exécute avant le bloc `help_flag` ; la corriger changerait le comportement de `-h` pour **toutes** les sous-commandes (documenté dans `docs/config-tui.md`).

### **24. Journal Général d'Utilisation (`src/usage_log.py`)**

* **Une Ligne par Commande :** Écrit dans `~/.gitpr/logs/<uuid5>.log`, **un fichier par jour**, avec la commande, les arguments, le dépôt, l'utilisateur et l'horodatage. Il répond à « qu'ai-je réellement exécuté, et quand ? ».
* **Nom Dérivé de la Date :** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` plutôt qu'aléatoire — un nom aléatoire exigerait un compteur ou un fichier d'état pour savoir quel fichier est celui d'aujourd'hui, et deux processus concurrents pourraient être en désaccord.
* **Écriture Synchrone (décision explicite) :** Contrairement à `log_local_metric`, qui utilise un thread daemon et perd donc l'écriture si le processus se termine tôt — inacceptable pour un journal qui promet d'enregistrer *chaque* commande.
* **N'Affiche Jamais :** Le serveur MCP réserve stdout au JSON-RPC ; un `print` accidentel corromprait le protocole. Le module ne lève jamais non plus.
* **Un Seul Spawn Git :** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` au lieu des trois idiomatiques — ~40 ms au lieu de ~150 ms sous Windows, à *chaque* exécution.
* **Son Propre `_repo_label()` :** Sans réutiliser `get_repo_name()` de `core.py` (regex codée en dur sur `github.com`, retournerait `unknown/repo` sur GitLab/Bitbucket/Azure) et sans `parse_repo_ref`, qui est une méthode de provider et exigerait de construire un provider (jeton, `requests`) à chaque commande.
* **Contrôle :** `GITPR_SHOW_LOGS` (défaut `"true"`) ; désactivée dans `tests/conftest.py`.
* **Dogfooding 🆕 :** Le journal d'exécution d'un `pr_desc` antérieur a été la preuve qui a isolé le bug du reviewer — la ligne `Reviewers requested on PR #1068: ['Eduarda Leal']` a prouvé que le **nom** partait comme login et que le `_request()` retournait sans lever. Sans cet enregistrement, le symptôme (« il n'apparaît pas sur le PR ») n'aurait pas pu être distingué d'une panne réseau.
* **Artefacts :** `docs/usage-log.*.md` en 5 langues ; `tests/test_usage_log.py` (27 scénarios).

### **25. Sous-commande `gitpr fix` — Constats de Review en Patches Révisables (`src/fix/`) 🆕**

* **Ce Que C'est :** Une capacité **nouvelle**, pas une généralisation du chat. L'investigation obligatoire (étape 0 du plan) a fait tomber trois prémisses de la spec : le chat **n'a jamais** appliqué de patch (F5 et `ctrl+s` écrivent un `.txt` dans le CWD, sans `subprocess` et sans diff valide), un finding avec `id`/`severity`/`file_path` **n'existait** nulle part, et `config.schema.yml` n'a jamais existé.
* **Le Pipeline Réel :** dernier review du cache (`resolve_last_review`) → **un** appel d'IA (`call_ai_model`, jamais `generate_pr_content`, dont le `else` est la branche de PR) → extraction et validation du diff unifié → `git apply --check` → classification déterministe → dry run ou écriture → historique.
* **Paquet de 8 Fichiers (1147 lignes) :**
  * `patch_provenance.py` — contrat de données pur : `PatchSafety`, `FindingRef`, `PatchCandidate` (avec un `patch_id` dérivé), `PatchProvenance`, `ApplyFixResult`.
  * `patch_extractor.py` — blocs encadrés + validation du diff unifié ; **partagé avec le chat**.
  * `patch_safety_classifier.py` — `safe`/`review_required`/`experimental`, logique **pure, sans I/O et sans IA** ; retourne un **code de motif**, jamais une phrase prête.
  * `patch_applier.py` — premier enveloppe de `git apply` du projet (`--check`, `apply`, `--reverse`, `checkout -b`, `status`).
  * `fix_history.py` — `.gitpr/fix_history.json` avec écriture atomique (`.tmp` + `os.replace`).
  * `apply_fix.py` — le cas d'usage (470 lignes).
  * `rollback_fix.py` — `git apply --reverse` sur le diff conservé, avec trois refus distincts.
  * `__init__.py` — **docstring seulement**, et qui ordonne les modules à dessein (voir ADR-004).
* **Classification Déterministe :** Refuse un patch qui traverse plus d'un fichier ou plus d'un hunk, qui touche un chemin sensible configuré, qui dépasse le budget de lignes ajoutées+supprimées, qui **efface une ligne qui ressemble à un appel**, que l'IA a déclaré à faible confiance, ou qui échoue au `git apply --check` contre l'arbre actuel. Le `--force` **ne contourne jamais** la vérification d'applicabilité — seulement la classification.
* **Écriture Opt-In :** Le dry run est le défaut ; toute mutation exige `--apply` (ou une phrase de confirmation saisie avec `--force`).
* **Support :** `src/diff_parser.py` a gagné `summarize_patch()` / `PatchSummary` (purs) — comptage de fichiers, de hunks, delta de lignes et détection d'appel supprimé — utilisés par le classificateur.
* **MCP :** 13e outil `list_fix_candidates` (en lecture seule) et la ressource `skill://fix`.
* **Skill :** `templates/gitpr.fix.md` + `.pt_br.md` (persona : Senior Software Engineer), téléchargés par `gitpr --skill`.
* **Configuration et Artefacts :** 5 variables `GITPR_FIX_*` + catégorie `fix` dans la TUI ; `docs/fix-command.*.md` (5 langues, 8 sections) ; ADR-004 et `glossary-gitpr-fix.md` ; spec/plan/survey dans `docs/plans/` et `docs/survey/`.
* **Tests :** 10 fichiers dans `tests/fix/` (**204 scénarios**) avec des fixtures de dépôt git réel (`tests/fix/git_fixture.py`) — matrice du classificateur, applier, historique, routage CLI, rollback, settings et l'extracteur partagé avec le chat.

### **26. Sous-commande `gitpr review-pr` — Review de PR Distant (`src/review/`) 🆕**

* **Ce Que C'est :** De l'orchestration, **pas** un second moteur de review. Le moteur existant (`generate_pr_content`), le linter (`parse_diff_and_lint`) et le renderer sont les pièces du flux local, alimentées avec un diff venu d'ailleurs — un `.txt` de review distant et un de review local du même diff ne diffèrent **que par le nom du fichier**.
* **Paquet de 5 Fichiers (560 lignes) :**
  * `diff_source.py` — `DiffOrigin` + `DiffSource` : provenance pure (`cache_scope`, `is_remote`), sans I/O.
  * `diff_normalizer.py` — normalisation des newlines, validation du diff (`is_reviewable_diff`) et le filtre de smart-excludes **en Python**, pour un diff que git n'a jamais vu.
  * `render.py` — composition et écriture de l'artefact, **extraite de `main.py`** et désormais **partagée** avec les flux locaux.
  * `remote_pr.py` — le cas d'usage : barrière → PR → diff → normaliser → exclure → moteur → linter → commentaire optionnel.
  * `__init__.py` — marqueur de paquet.
* **`split_patch_sections` (`src/diff_parser.py`) 🆕 :** Retourne le chemin de chaque fichier **à côté** de son propre texte — base du filtre d'exclusion distant et de la synthèse des en-têtes GitLab.
* **Lecture Seule par Défaut :** `--post-comment` est le **seul** chemin qui écrit sur la forge. Il en va de même pour l'outil MCP `review_remote_pr`, qui ne reçoit même pas l'argument.
* **Interaction avec le `fix` 🆕 :** Comme le review distant ne correspond à aucun arbre local, le diff révisé a commencé à être conservé dans le cache (`reviewed_diff`) et le `fix` le préfère — corrigeant un défaut qui n'apparaîtrait qu'après cette fonctionnalité.
* **Artefacts :** `docs/review-pr.md` + `.pt_br.md` (8 sections), `docs/code-review-ia.*.md` (mode distant comme §1.4 + la réserve du linter externe, dans les 5 versions), ADR-005 et `glossary-review-pr.md` ; spec, plan et survey dans `docs/plans/` et `docs/survey/`.
* **Tests :** 4 fichiers dans `tests/review/` (**97 scénarios**) — `test_remote_pr.py` (40), `test_review_pr_cli.py` (25), `test_diff_normalizer.py` (20), `test_diff_source.py` (12) — plus 6 scénarios nouveaux dans `tests/scm/test_gitlab_provider.py` et 11 dans `tests/test_mcp_server.py`.

### **27. Résolution de l'Identité du Reviewer (`src/reviewer_resolution.py`) 🆕**

* **Le Bug (deux défaillances silencieuses enchaînées) :** (1) **Prefill vide** — `_reviewer_suggestion_view()` montait `handles` seulement avec `provider.email_to_handle()`, qui ne voit que les e-mails `users.noreply.github.com` ou ceux avec une adresse **publique** ; avec un e-mail d'entreprise (le cas réel : `eduardaleal@grafjb.com.br`) rien ne se résout, l'`Input` naît vide et le hint n'affiche que le **nom**. (2) **Attach non vérifié** — `_attach_reviewers()` repassait les valeurs du champ **verbatim** ; GitHub répond **201 sans attacher personne**, le `_request()` retourne sans lever et la ligne de log est écrite : succès apparent, reviewer absent, aucun avertissement.
* **Module Nouveau (159 lignes) :** Un plan, sans I/O propre, **qui ne lève jamais**. `resolve_candidates()` (avant la TUI) et `resolve_typed_reviewers()` (à l'attach) ; `match_candidate()` fait correspondre de façon **exacte et normalisée** par login, nom ou e-mail.
* **Échelle de Résolution :** handle déjà connu (sans requête) → correspondance exacte avec une personne suggérée → lookup par e-mail (`email_to_handle`) → validation du login sur la forge (`get_user_login`).
* **Ce qui ne se résout pas n'est jamais envoyé :** Cela sort dans `ResolutionOutcome.dropped` comme `(valeur, motif_i18n)` et est écarté avec un avertissement visible, au lieu de devenir un `201` vide.
* **Provider Duck-Typed :** L'accès se fait par `getattr`, donc les fakes et les forges sans les nouvelles méthodes continuent de fonctionner — la suite existante n'a pas eu besoin d'être réécrite.
* **Lecture de Retour sur GitHub :** `request_pull_request_reviewers` lit `requested_reviewers` dans le corps du `201` et retourne les logins **réellement** attachés ; un lot rejeté avec un `422` (GitHub rejette le lot entier quand un seul login est inéligible) est répété **un par un**, au lieu de faire tomber aussi les reviewers valides.
* **Utilitaires Adjacents :** `_identity_key` → `identity_key` (public), un nouveau `normalize_identity()`, et `ReviewerCandidate` porte désormais `last_commit_hash` pour que l'agrégation conserve le commit du toucher le plus récent.
* **Diagnostic :** Le journal d'utilisation de l'outil lui-même a été la preuve d'origine — voir §24.
* **Artefacts :** `docs/suggested-reviewers.*.md` re-synchronisé dans les 5 langues, `ADR-002-reviewer-suggestion.md` et `glossary-reviewer-suggestion.md`, plan et survey du grill de 4 tours.
* **Tests :** `tests/test_reviewer_resolution.py` (18, **nouveau**) + des extensions dans `test_github_provider.py` (68), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) et `test_main_suggest_reviewers.py` (8).

---

## **📊 Tests et Qualité**

| Fichier de Test | Scénarios | Focus |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame par plage de lignes dans un fichier |
| `tests/test_blame_metrics.py` | 7 | Métriques de blame : profondeur, commits, durée |
| `tests/test_changelog_builder.py` | 15 | Builder de changelog : sections, titres traduisibles, contributeurs |
| `tests/test_chat_backend.py` | 19 | Mémoire du chat, persistance, commandes slash |
| `tests/test_commit_classifier.py` | 23 | Classification Conventional Commits (types, parser tolérant) |
| `tests/test_config_app.py` | 78 | TUI de configuration : montage, changement de catégorie, suivi des modifications, F2 bloqué, Ctrl+R, recherche, secrets |
| `tests/test_config_cli.py` | 9 | Enregistrement de la sous-commande `config`, `-h`, import paresseux, stdout propre |
| `tests/test_config_schema.py` | 42 | Couverture de `DEFAULT_CONFIG`, absence de doublons, catégories/kinds, `advanced` uniquement dans Avancé |
| `tests/test_config_store.py` | 22 | Round-trip sur un `.env` temporaire, commentaires et ordre préservés, `remove_config_value()` idempotent |
| `tests/test_config_suggest_reviewers.py` | 8 | Configuration des reviewers suggérés (clés et défauts) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` avec un SDK mocké (401 vs. réseau vs. ollama) |
| `tests/test_core.py` | 49 | Flux principaux, git diff, génération de PR, timing, staging, co-paternité, langue des hooks |
| `tests/test_diff_parser.py` | 26 | Parser de diff par lignes/hunks + `summarize_patch()` et `split_patch_sections()` 🆕 |
| `tests/test_external_linters.py` | 33 | Pont Checkstyle : parser XML, subprocess, croisement de diff, rapport |
| `tests/test_i18n.py` | 20 | Parité entre langues (1048×6), clés manquantes/orphelines, identité |
| `tests/test_install_wizard.py` | 3 | Assistant interactif d'installation |
| `tests/test_issue_engine.py` | 4 | Brouillon d'issue structuré |
| `tests/test_linter_metrics.py` | 4 | Métriques de linter : erreurs, warnings, durée |
| `tests/test_linter_presets.py` | 5 | Presets de linter : résolution et re-téléchargement forcé |
| `tests/test_main_suggest_reviewers.py` | 8 | Flag `--no-suggest-reviewers` dans la CLI et l'aide contextuelle |
| `tests/test_mcp_prompts.py` | 11 | Templates de prompt MCP et fallback de langue |
| `tests/test_mcp_server.py` | 104 🆕 | Outils MCP (14), ressources (18), annotations, patching, CLI direct, offload |
| `tests/test_mcp_server_e2e.py` | 6 | Vrai serveur MCP via subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Collecte, export local, périmètre de dépôt, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Timeouts réseau/IA — **2 assertions obsolètes (600s)** |
| `tests/test_plugins.py` | 17 | Découverte de plugins, fusion de règles de linter, prompts MCP |
| `tests/test_pr_publish_app.py` | 45 | TUI du PR Publisher : écrans, flux, reviewers suggérés, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Modal d'erreur du linter : abort, no-verify |
| `tests/test_pre_save.py` | 3 | Flag --pre-save et payload JSON |
| `tests/test_release_cli.py` | 4 | CLI de release : options, aide avec epilog documenté |
| `tests/test_release_engine.py` | 27 | Moteur de release : plage de commits, CHANGELOG, publication |
| `tests/test_reviewer_resolution.py` | 18 🆕 | Échelle de résolution, rejet d'un nom partiel, dédup, tolérance à l'échec |
| `tests/test_reviewer_suggestion.py` | 18 | Logique de suggestion de reviewers (classement, exclusion, top-N, `last_commit_hash`) |
| `tests/test_skill_command.py` | 10 | Téléchargement et validation des templates de skill |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` avec `quiet=True`, fallbacks, registre de skills |
| `tests/test_smart_excludes.py` | 15 | Filtre pathspec intelligent et re-téléchargement forcé |
| `tests/test_suggest_reviewers.py` | 17 | Reviewers suggérés dans le flux PR (intégration, hint `no_login`) |
| `tests/test_thinking_words.py` | 5 | Chargement, parsing avec le séparateur `;` et rechargement forcé |
| `tests/test_updater.py` | 23 | Barrière PyPI : parsing de version, cache quotidien, fetch, décisions de la barrière, câblage CLI |
| `tests/test_usage_log.py` | 27 | Journal d'utilisation : nom dérivé de la date, écriture synchrone, silence, `_repo_label()` multi-forge |
| `tests/test_version_bump.py` | 17 | Bump sémantique : major/minor/patch, cibles et validation |
| `tests/fix/test_apply_fix.py` | 53 🆕 | Cas d'usage complet : review → IA → valider → classifier → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 🆕 | Extracteur partagé entre le chat et `fix` (comportement identique) |
| `tests/fix/test_fix_cli.py` | 31 🆕 | Routage de la sous-commande, options, dry-run par défaut, `--force` |
| `tests/fix/test_fix_history.py` | 21 🆕 | Registre `.gitpr/fix_history.json` : écriture atomique, lecture |
| `tests/fix/test_fix_settings.py` | 10 🆕 | Les cinq `GITPR_FIX_*` et les fallbacks de valeur invalide |
| `tests/fix/test_patch_applier.py` | 19 🆕 | Enveloppe de `git apply` : check/apply/reverse, branche, status |
| `tests/fix/test_patch_extractor.py` | 13 🆕 | Blocs encadrés → diff unifié validé |
| `tests/fix/test_patch_safety_classifier.py` | 22 🆕 | Matrice safe/review_required/experimental et codes de motif |
| `tests/fix/test_resolve_last_review.py` | 13 🆕 | Sélection du dernier review, exclusion des reviews par fichier |
| `tests/fix/test_rollback_fix.py` | 14 🆕 | `--rollback` : reverse et les trois refus |
| `tests/review/test_diff_normalizer.py` | 20 🆕 | Newlines, validation de diff, smart-excludes en Python |
| `tests/review/test_diff_source.py` | 12 🆕 | `DiffOrigin`/`DiffSource` : provenance, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 🆕 | Orchestration : barrières, PR, diff, linter, commentaire optionnel |
| `tests/review/test_review_pr_cli.py` | 25 🆕 | CLI `review-pr` : options, rejets avant l'IA, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 43 | Provider Azure DevOps : org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 39 | Provider Bitbucket : Basic auth, workspace |
| `tests/scm/test_contract.py` | 38 | Contrat `ScmProvider` : signatures, dataclasses, erreurs |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Shim `github_api` déprécié → délègue au provider |
| `tests/scm/test_github_provider.py` | 68 🆕 | Provider GitHub : REST, en-têtes, PRs, issues, releases, read-back des reviewers |
| `tests/scm/test_gitlab_provider.py` | 47 🆕 | Provider GitLab : API v4, namespace, **en-têtes synthétisés et `overflow`** |
| `tests/scm/test_init_wizard.py` | 10 | Wizard `--init` : détection de forge, validation, persistance |
| `tests/scm/test_release_publish.py` | 8 | Publication de release par forge (GitHub/GitLab) |
| `tests/sync_i18n.py` | — | Script de vérification de couverture i18n (scaffold ; jamais exécuté) |

**Total :** **1437 scénarios collectés dans 64 modules de test** (41 à la racine + 9 dans `tests/scm/` + **10 dans `tests/fix/`** 🆕 + **4 dans `tests/review/`** 🆕 ; **+377** depuis le rapport précédent, avec **15 nouveaux fichiers**). Exécution complète sur cette machine avec `GITPR_LANG=en_us` : **1432 passed / 3 failed / 2 skipped / 81 subtests** en ~347s.

**Notes de qualité pour cette version :**
- **Aucun nouvel échec.** Les 3 échecs sont exactement les mêmes que dans le rapport précédent — et les deux de timeout restent hérités d'avant :
- **2 échecs réels (tests obsolètes, hérités) :** `test_net_timeouts.py::test_ai_timeout_defaults_to_600` et `::test_invalid_ai_timeout_falls_back_to_default` affirment le défaut de 600s pour `GITPR_AI_TIMEOUT`, mais le code utilise **180s** depuis le fix `681a7fa`. C'est le même élément qui figurait déjà dans les Prochaines Étapes de **deux** rapports précédents et il **reste ouvert**.
- **1 échec de locale (hérité, pas une régression) :** `test_core.py::TestHooksLanguage::test_the_language_chosen_with_the_lang_flag_is_honoured` affirme `i18n.CURRENT_LANG == "pt_br"` — il passe avec la locale pt-BR de la machine et échoue avec `GITPR_LANG=en_us`. C'est encore le seul test sensible à la locale de la suite.
- **Croissance de 377 scénarios avec la ligne de base d'échecs intacte** — le signal pertinent de cette fenêtre : les trois nouvelles fonctionnalités sont entrées sans rien faire tomber ni masquer.
- `tests/conftest.py` conserve `GITPR_SHOW_LOGS=false` et `GITPR_SKIP_UPDATE_CHECK=true` — la suite n'écrit pas dans le journal d'utilisation et n'est pas bloquée par la barrière de mise à jour.
- 🆕 **Fixtures de git réel :** `tests/fix/git_fixture.py` monte de vrais dépôts git, au lieu de mocker `subprocess` — le `patch_applier` n'est honnête que si le `git apply` est le vrai.

---

## **🌐 Internationalisation et Documentation**

* **Couverture i18n :** **1048 clés** de traduction dans chacun des 6 dictionnaires (+93 depuis le rapport précédent) avec **parité totale des key sets**. La chaîne mesurée par commit a été 955 → 1022 (`fix`, +67) → 1028 (reviewers, +6) → **1048** (`review-pr`, +20). `__lang_version__` est passée v0.0.25 → v0.0.26 → v0.0.27 → **v0.0.28**, déclenchant le re-téléchargement OTA des traductions.
* **Sources de traduction en lockstep :** une nouvelle clé doit exister dans le code (source), dans `langs/pt_br.json` (**liste maîtresse**), dans les dicts FR/ES de `scripts/sync_all_langs.py` (deuxième source) et dans les valeurs curées de `scripts/fix_mangled_i18n_keys.py` (troisième source, lue par `tests/test_i18n.py`) ; l'assertion `len(CLEAN_KEYS)` reste à 49.
* **Nouveaux Sujets 🆕 (2) :**
  - `docs/fix-command.md` — constats de review comme patches : pipeline, classification de sécurité, lecture avant écriture, historique et rollback, skill, MCP et variables — **en 5 langues**
  - `docs/review-pr.md` — review de PR distant : ce que c'est, quelles forges peuvent être révisées, le rapport, la publication, skill, MCP et variables — **en EN + PT-BR** (les 3 langues restantes sont en attente)
* **Sujets mis à jour dans cette fenêtre :** `docs/code-review-ia.*` (5 — mode distant comme §1.4 et la réserve que le linter externe ne tourne pas), `docs/suggested-reviewers.*` (5 — résolution de l'identité), `docs/fix-command.*` (5 — §2.2 réécrit : le diff vient désormais de l'enregistrement), `docs/commit-message-ia.*` (5), `docs/issue-tui-help.*` (5), `docs/linter-regras-customizadas.*` (5), `docs/providers-ia.*` (5) et `docs/skill-template.*` (5), plus `README.md`/`README.pt_br.md` (2×) et `CLAUDE.md` (tableau des commandes, outils MCP 13 → 14, arborescence avec `src/review/`).
* **Documentation en 5 langues :** **41 sujets canoniques** dans `docs/` — **35 avec couverture complète dans les 5 langues** (+1 depuis le rapport précédent) et **6 sujets partiels/PT-only** (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers` et, désormais, `review-pr` avec 2 langues).
* **Skills locales de Claude Code :** `.claude/skills/` avec **29 skills** (compte inchangé dans cette fenêtre).
* **Memory Index :** `.claude/memory/MEMORY.md` avec 40 patterns (compte inchangé dans cette fenêtre).
* **Rapports de tâches :** `docs/claude-code/reports/develop_natan/` (**93** au total ; **+3** dans la fenêtre — `gitpr fix`, résolution du login du reviewer et review de PR distant) et `docs/gemini/reports/develop_natan/` (5 fichiers ; aucun nouveau).
* **Rapports de statut :** `docs/reports/` (14 rapports ; celui-ci est le 15e).
* **Plans de développement :** 99 fichiers dans `docs/plans/` (+10 dans la fenêtre — les specs/plans du `fix` et du `review-pr`, la correction de la suggestion de reviewers, ADR-004, ADR-005 et les glossaires `glossary-gitpr-fix` et `glossary-review-pr`) + **6 fichiers dans `docs/survey/`** (+3 dans la fenêtre).

---

## **🔄 Pipeline de Distribution**

1. **PyPI (canal unique) :** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mise à jour obligatoire :** l'exécution vérifie PyPI au démarrage et **bloque avec le code de sortie 1** s'il existe une version plus récente, en affichant `pip install --upgrade gitpr-cli` ; la vérification est mise en cache par jour et `--update` se contente de rapporter
3. **GitHub Releases :** supprimé — pas de PyInstaller, pas d'asset `.exe`, pas de hot-swap
4. **GitHub Actions :** Workflow `pr-review.yml` + `action.yml` (installe via pip)
5. **MCP Server :** Point d'entrée `gitpr-mcp` via `pyproject.toml`
6. **Templates et Langues OTA :** `templates/` et `langs/*.json` servis depuis GitHub (main) — le bump `v0.0.28` renouvelle les copies locales dans `~/.gitpr/langs/` une fois publié
7. **État de la release 1.2.0 🆕 :** le `__version__` est passé à 1.2.0 **dans le working tree** et n'a encore été ni commité ni tagué (HEAD à 1.1.0, dernier tag `v1.1.0`) ; le `CHANGELOG.md` s'arrête encore à `[1.1.0] - 2026-09-13`. Le chemin est de lancer `gitpr release` et de commiter le bump.

---

## **📈 Évolution depuis le Rapport Précédent (v0.0.14)**

| Domaine | v0.0.14 (précédent) | v0.0.15 (actuel) |
|------|-------------------|-----------------|
| **Version GitPR** | 1.1.0 | **1.2.0** (bump **non commité** ; HEAD à 1.1.0, dernier tag `v1.1.0`, CHANGELOG encore à `[1.1.0]`) |
| **Version Langue** | v0.0.25 | **v0.0.28** (via v0.0.26 et v0.0.27) |
| **Version Scripts Hook** | v0.0.3 | **v0.0.3** |
| **Fournisseurs IA** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Langues** | 5 langues, 6 dictionnaires | 5 langues, 6 dictionnaires |
| **Interface** | CLI + TUIs (Issues, Chat, Dashboard, PR Publisher, LinterApp, ConfigApp) + wizards `--init`/`--install` + `gitpr release`/`gitpr config` | **+ `gitpr fix` (patches révisables) + `gitpr review-pr` (review de PR distant) + `NoticeScreen`** |
| **Outils MCP** | 12 outils / 17 ressources / 7 prompts | **14 outils / 18 ressources / 7 prompts** (+`list_fix_candidates`, +`review_remote_pr`, +`skill://fix`) |
| **Flags CLI** | 35 options à la racine + `release` (6) + `config` (0) | **35 à la racine** + `release` (6) + `config` (0) + **`fix` (8)** + **`review-pr` (2)** |
| **Variables d'Environnement** | 39 clés dans `DEFAULT_CONFIG` | **44 clés** (+5 `GITPR_FIX_*`) |
| **Linter** | Regex + pont Checkstyle (wizard/TUI/rapport) | Inchangé (+ **`skip_external`** : le review distant ne publie pas les alertes de l'arbre local) |
| **Git Hooks** | Corrigés : `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG`, `effective_hook_lang()` | Inchangé |
| **Messages de Commit** | Avec trailer `Co-Authored-By` (opt-out) | Inchangé |
| **Couche SCM** | 4 forges, `create_release`, `create_issue` | **+ `get_pull_request` (concret sur l'ABC) + `supports_reviewable_diff` + read-back des reviewers (`list[str]`, rupture de contrat) + corrections GitLab** |
| **i18n (clés par fichier)** | 955 × 6 (parité totale) | **1048 × 6 (parité totale) — +93 clés** |
| **Documentation** | 39 sujets canoniques (34 complets + 5 partiels) | **41 sujets canoniques (35 complets + 6 partiels) — 2 nouvelles familles, 8 mis à jour** |
| **Distribution** | PyPI exclusif | Inchangé (release 1.2.0 en attente) |
| **Suite de Tests** | 1060 scénarios (49 fichiers) | **1437 scénarios (64 fichiers : 41 + 9 SCM + 10 fix + 4 review) — en_us : 1432 passed / 3 failed (2 obsolètes + 1 locale) / 2 skipped** |
| **Commits depuis le rapport** | 2 commits | **5 commits** (`f4d5186`, `f5bed07`, `b9dd930`, `eb55400`, `a8a7770`) |
| **PRs mergés** | 2 PRs (#162, #164) | **3 PRs (#167, #171, #173)** |
| **Memory Index** | 40 patterns | **40 patterns** |
| **Rapports de tâches** | 90 claude-code, 5 gemini | **93 claude-code (+3 dans la fenêtre) et 5 gemini** |
| **Plans de développement** | 89 plans, 3 surveys | **99 plans (+10), 6 surveys (+3)** |
| **Hygiène du dépôt** | — | **+ `pypa/`/`pip/` retirés du suivi, `.gitignore` mis à jour** 🆕 |

---

## **🚧 Prochaines Étapes**

* **Clôturer la release 1.2.0 🆕 :** le `__version__` est à 1.2.0 dans le working tree sans commit, sans tag et **sans entrée dans le `CHANGELOG.md`** (qui s'arrête à `[1.1.0]`). Lancer `gitpr release`, commiter le bump et taguer — c'est le seul élément de cette liste qui bloque la publication.
* **Traduire ce qui est resté partiel 🆕 :** `docs/review-pr.*.md` n'existe qu'en EN et PT-BR (il manque pt_pt, es_es, fr_fr) et `templates/gitpr.fix.md` seulement en EN et PT-BR — c'est la première fois en plusieurs fenêtres qu'un sujet nouveau **ne** naît **pas** complet dans les 5 langues.
* **Documenter la rupture de contrat de `request_pull_request_reviewers` 🆕 :** il est passé de `None` à `list[str]` ; les sous-classes tierces de `ScmProvider` doivent être mises à jour. `docs/scm-multiforge.*.md` est l'endroit, dans les 5 versions.
* **`.gitpr/metrics/export/` dans le `.gitignore` 🆕 :** le PR #171 a commité `gitpr_metrics_2026-09-17.csv`/`.json` — ce sont des artefacts générés localement, comme ceux d'août qui sont déjà suivis.
* **Fournisseur Anthropic Claude :** Support direct de l'API Claude (`claude-sonnet-5`).
* **Graphiques ASCII/Textual dans le Dashboard :** Ajouter des histogrammes de temps et des graphiques de tendance de tokens dans la TUI de métriques.
* **Pipeline de Release sur GitHub Actions :** Automatisation complète du build et de l'envoi vers PyPI (la génération de changelog est désormais locale via `gitpr release`).
* **Seed Local de `.gitpr/conf/` :** Le seed des templates de configuration locale (smart-excludes, linter) reste en attente comme sous-commande propre ou étape du wizard ; la TUI de configuration propose les **téléchargements** de ces fichiers, mais pas le seed du projet.
* **Plus de fournisseurs :** OpenAI direct, fournisseurs locaux supplémentaires.
* **Extracteur i18n dans `sync_i18n.py` :** Le regex tronque les littéraux à concaténation implicite (`__("a " "b")`) — migrer vers AST (la garde dans `test_i18n.py` utilise déjà AST et ne dépend pas du script).
* **Corriger les Tests de Timeout Obsolètes :** `tests/test_net_timeouts.py` affirme un défaut de 600s, mais le code utilise 180s depuis le fix `681a7fa` ; aligner aussi la docstring obsolète dans `config.py`. **Élément hérité, toujours ouvert — cela fait déjà trois rapports.**
* **Réconcilier la Version du Projet :** `CLAUDE.md` dit encore `Current version: 0.0.37` alors que le `__version__` est à 1.2.0 — l'écart n'a fait que se creuser (+0.0.37 vs. 1.1.0 dans le rapport précédent). Définir une convention unique et mettre à jour le `CLAUDE.md`. **Élément hérité, toujours ouvert.**
* **Dette d'Index du README :** Les puces des familles `suggested-reviewers`, `scm-multiforge`, `config-tui`, `usage-log` **et désormais `fix-command` et `review-pr`** ne sont pas dans l'index — la dette a augmenté dans cette fenêtre.
* **Robustesse de Locale dans les Tests :** 1 test est sensible à la locale pt_br de la machine (`test_core.py::TestHooksLanguage`) — fixer `GITPR_LANG=en_us` dans le setup ou mocker `TRANSLATIONS` pour que la suite soit 100 % verte sur n'importe quelle machine/CI.
* **`gitpr -h config` ignore `-h` :** la sous-commande ouvre la TUI au lieu d'afficher l'aide — la barrière `if ctx.invoked_subcommand is not None: return` s'exécute avant le bloc `help_flag`. La corriger changerait le comportement de `-h` pour **toutes** les sous-commandes, cela demande donc une décision.
* **Section Smart Exclude dans la TUI :** des 12 éléments remontés après l'utilisation de l'écran, l'élément 10 (la section *Smart Exclude*) est le seul livrable non encore entamé.
* **Dettes consignées dans le plan de la TUI de configuration :** `DEFAULT_CONFIG` est devenu redondant avec le schéma ; la bannière d'ouverture ne liste pas `--dashboard`, `--init`, `--base` ni `--plugins` ; `LinterApp` ne désactive pas la palette de commandes.

### ✅ Terminés dans cette fenêtre (2026-09-13 → 2026-09-17)

* ~~**Sous-commande `gitpr fix`**~~ — paquet `src/fix/` (8 fichiers, 1147 lignes), classificateur déterministe `safe`/`review_required`/`experimental`, dry run par défaut, `.gitpr/fix_history.json` et `--rollback` (PR #167).
* ~~**`resolve_last_review()` + `reviewed_diff` dans le cache**~~ — le review qui alimente le `fix` est désormais choisi par repo+branche, en excluant les reviews à périmètre de fichier.
* ~~**Résolution de l'identité du reviewer**~~ — `src/reviewer_resolution.py`, `request_pull_request_reviewers` retournant `list[str]`, read-back du `201`, retry individuel sur `422` et `NoticeScreen` (PR #171).
* ~~**Sous-commande `gitpr review-pr`**~~ — paquet `src/review/` (5 fichiers, 560 lignes), lecture seule par défaut, `.txt` nommé d'après la branche source, `--post-comment` comme seul chemin d'écriture (PR #173).
* ~~**`ScmProvider.get_pull_request` + `supports_reviewable_diff`**~~ — méthode concrète sur l'ABC implémentée dans les 4 forges ; Azure barré avant tout appel réseau.
* ~~**Corrections latentes de GitLab**~~ — en-têtes `diff --git` synthétisés depuis `old_path`/`new_path` et `overflow: true` qui lève désormais.
* ~~**`skip_external` dans le linter**~~ — le review distant ne publie plus les alertes du pont externe, qui tourne contre l'arbre local.
* ~~**MCP : 12 → 14 outils et 17 → 18 ressources**~~ — `list_fix_candidates` + `skill://fix` et `review_remote_pr`.
* ~~**i18n : +93 clés et chaîne v0.0.25 → v0.0.28**~~ — parité totale des key sets dans les 6 dictionnaires.
* ~~**Hygiène du dépôt**~~ — arbre pip 25.2 vendorisé retiré du suivi ; `.gitignore` avec `pypa/` et `pip/` (commit `f5bed07`).

---

**Rapport généré le :** 2026-09-17  
**Branche :** `develop_natan`  
**Auteur :** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
