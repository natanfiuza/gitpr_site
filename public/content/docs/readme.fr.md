# **GitPR CLI 🚀**

<p align="center">
  <img src="https://raw.githubusercontent.com/natanfiuza/gitpr/main/docs/logo.png" alt="Logo GitPR" width="150">
</p>

GitPR CLI est un outil d'automatisation en ligne de commande qui utilise l'intelligence artificielle **Google Gemini** et **DeepSeek** pour analyser vos modifications de code (git diff) ou des fichiers entiers. L'outil génère automatiquement des messages de commit au format *Conventional Commits*, des descriptions détaillées de Pull Request et des revues de code approfondies visant à réduire la dette technique.

🌐 **Site web :** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/) · 📂 **Dépôt :** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)

## **🛠️ Technologies et bibliothèques utilisées**

Ce projet a été développé en Python et utilise les bibliothèques principales suivantes :

* [**Click**](https://click.palletsprojects.com/) : Pour créer une interface en ligne de commande (CLI) robuste et conviviale.
* [**Google GenAI**](https://pypi.org/project/google-genai/) : SDK officiel pour une intégration directe avec l'API Gemini.
* [**OpenAI**](https://pypi.org/project/openai/) : Bibliothèque utilisée pour sa compatibilité totale avec la puissante API **DeepSeek**.
* [**Python-dotenv**](https://pypi.org/project/python-dotenv/) : Pour une gestion sécurisée des variables d'environnement.
* [**Pytest**](https://docs.pytest.org/) : Pour exécuter des tests unitaires de manière simple, colorée et lisible dans la console.
* [**Cryptography**](https://cryptography.io/) : Pour garantir que votre `GEMINI_API_KEY` soit stockée de manière chiffrée et sécurisée sur le disque.
* [**PyYAML**](https://pyyaml.org/) : Utilisé pour lire et traiter les règles personnalisées d'analyse statique du fichier `.gitpr.linter.yml`.
* [**Textual**](https://textual.textualize.io/) : Bibliothèque puissante pour créer des interfaces graphiques terminales (TUI), utilisée dans le panneau interactif de génération et d'édition d'issues.
* [**Requests**](https://pypi.org/project/requests/) : Bibliothèque élégante et robuste pour les requêtes HTTP, utilisée pour communiquer avec l'API REST de GitHub.
* [**MCP**](https://pypi.org/project/mcp/) : SDK Python officiel pour le Model Context Protocol, permettant à GitPR de s'intégrer directement avec les éditeurs et IDE alimentés par IA.

----

## 📦 Comment compiler l'exécutable localement

Si vous souhaitez générer votre propre binaire à partir du code source, nous utilisons **PyInstaller**. Assurez-vous d'être dans le répertoire racine du projet avec l'environnement virtuel configuré.

1. Installez les dépendances de développement (si ce n'est pas déjà fait) :
   ```bash
   pipenv install --dev
   ```

2. Exécutez la commande de construction en pointant vers notre point d'entrée (`run.py`) :
   ```bash
   pipenv run pyinstaller --noconfirm --onefile --icon=icon.ico --name gitpr run.py
   ```
> **Note technique :** Le flag `--onefile` garantit que tout le code Python, les bibliothèques et les dépendances sont compressés dans un seul binaire, tandis que `--paths src` aide le compilateur à trouver nos fichiers `core.py` et `config.py`. 🛠️

Après avoir exécuté cette commande, PyInstaller créera quelques dossiers (`build` et `dist`).
Votre fichier final prêt à l'emploi se trouvera dans le dossier **`dist/`** nommé `gitpr` (ou `gitpr.exe` sur Windows).


----

## 🧪 Exécution des tests

Pour garantir que la logique de capture Git et l'intégration IA fonctionnent correctement, nous utilisons des tests unitaires.

1. Installez les dépendances de test (si ce n'est pas déjà fait) :
   ```bash
   pipenv install --dev pytest
   ```

2. Exécutez les tests avec la commande :
   ```bash
   pipenv run pytest -v
   ```
Pytest détectera automatiquement les fichiers dans le dossier `tests/` et affichera un rapport d'exécution détaillé.

----
## **⚙️ Installation et configuration**

### **Utilisation de l'exécutable (Recommandé)**

1. Téléchargez le fichier exécutable gitpr depuis l'onglet « Releases » sur GitHub.
2. Déplacez l'exécutable vers un dossier qui se trouve dans votre PATH (ex. : /usr/local/bin sur Linux/Mac ou votre dossier utilisateur sur Windows).
3. Au premier lancement, l'assistant vous guidera :
   ```bash
   $ gitpr
   ```
```bash
🚀 Automatisation intelligente des PR avec IA

🔧 Première exécution détectée ! Configurons GitPR CLI.

🔑 Entrez votre GEMINI_API_KEY :

📄 Modèle de nom de fichier de sortie par défaut [{branch}_{datetime}_PR_DESC.md] :
```
*Remarque : Votre configuration sera sauvegardée de manière sécurisée dans le fichier `~/.gitpr/.env`.*

> **🔒 Note de sécurité :** GitPR CLI utilise le chiffrement symétrique (Fernet). Votre clé API est stockée sous forme de hachage dans le fichier `.env`, et la clé maîtresse de déchiffrement est générée automatiquement dans `~/.gitpr/secret.key`. **Ne partagez jamais votre fichier secret.key.**

### À partir du code source

1. Clonez le dépôt : `git clone https://github.com/natanfiuza/gitpr.git`

2. Entrez dans le dossier : `cd gitpr`

3. Configurez l'environnement :
```bash
pipenv install google-genai openai python-dotenv click cryptography
```
4. Exécutez : pipenv run python src/main.py

## **💻 Comment utiliser**

GitPR dispose d'un comportement par défaut puissant et de plusieurs options avancées pour vous assister dans votre quotidien de développeur.

### **Comportement par défaut (Pull Request)**
Exécutez simplement la commande nue dans votre terminal :
```bash
gitpr
```
L'outil se synchronisera avec le dépôt distant (`git fetch`), comparera vos modifications avec la branche principale distante (ex. : `origin/main`), et générera un fichier Markdown (ex. : `feature-login_20260421110134_PR_DESC.md`) à la racine de votre projet avec la suggestion complète pour votre Pull Request.

### **Options et commandes avancées**
Vous pouvez passer les *flags* suivants pour des actions spécifiques :

* `-c` ou `--commit` : Exécute un `git diff` local et affiche **uniquement le message de commit suggéré**.
* `-r` ou `--review` : Effectue une **Revue de Code** détaillée des modifications locales.
* `-f` ou `--fullreview` : Effectue une **Revue de Code complète** en analysant toutes les modifications depuis la branche distante.
* `-i <file>` ou `--input <file>` : **Audit de fichier complet.** Doit être utilisé avec `-r` ou `-f` ; ignore l'historique git et effectue une revue de code de l'intégralité du fichier. Excellent pour agir comme consultant en refactoring de code legacy.
* `--provider <gemini|deepseek|ollama>` : Force l'utilisation d'une IA spécifique uniquement pour cette exécution, ignorant votre valeur par défaut sauvegardée dans `.env`.
* `--lang <code>` : Force la langue de l'interface pour cette exécution (ex. : `en_us`, `pt_br`). Remplace `GITPR_LANG` dans `.env` sans persister le changement.
* `-ch` ou `--chat` : Ouvre le **Chat Interactif de Programmation en Paire** — un terminal TUI où l'IA voit votre diff actuel et maintient une conversation contextuelle. Dispose d'une mémoire par branche, de commandes slash (`/explain`, `/tests`, `/optimize`, `/clear`), du correctif automatique (F5), du rafraîchissement du diff (F2) et de l'export de session (F6).
* `-l` ou `--linter` : Exécute **uniquement le linter statique local** (sans appels IA). Idéal pour une utilisation dans les pipelines CI/CD afin de bloquer le code non conforme.
* `--mcp` : Démarre GitPR en tant que **serveur MCP** (Model Context Protocol) sur le transport stdio. Permet l'intégration avec VS Code, Cursor, Claude Desktop et d'autres éditeurs compatibles MCP — exposant toutes les capacités IA de GitPR sous forme d'outils directement dans votre IDE. Également disponible via la commande autonome `gitpr-mcp`.
* `-ih` ou `--installhooks` : Installe automatiquement les **Git Hooks locaux** (`pre-commit` et `prepare-commit-msg`) dans votre dépôt.
* `-s` ou `--skill` : Crée les fichiers modèles de contexte IA (`.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md`) et le Linter (`.gitpr.linter.yml`) à la racine du projet.
* `-is` ou `--issue` : Génère automatiquement un brouillon d'**Issue standardisée** et ouvre une interface interactive (TUI) pour l'édition ou la soumission directe via l'API REST. Cette fonctionnalité dispose de **3 moteurs de contexte** selon la combinaison de commandes :
  * **Issue pour nouveau code (`gitpr -is`)** : Lit le `git diff` actuel. **Pourquoi l'utiliser :** Idéal pour documenter rapidement la tâche que vous venez de terminer, avant de commiter.
  * **Issue Epic/Release (`gitpr -is -ht`)** : Lit l'historique complet de la branche actuelle (Git Log + Cache PR). **Pourquoi l'utiliser :** Idéal pour générer une documentation consolidée d'une release entière ou d'une fonctionnalité importante qui a nécessité plusieurs jours/commits.
  * **Issue Archéologique/Dette technique (`gitpr -is -b file:lines`)** : Lit la chronologie d'une règle métier spécifique. **Pourquoi l'utiliser :** Idéal pour documenter la dette technique, expliquer comment un bloc de code legacy a évolué et pourquoi il doit être refactorisé.
* `-h` ou `--help` : Affiche l'aide générale avec toutes les options. Utilisez-le avec un autre flag pour une **aide contextuelle** (ex. : `gitpr -h --issue`, `gitpr -h --linter`) avec un lien direct vers la documentation détaillée de chaque fonctionnalité.
* `-u` ou `--update` : Vérifie et installe la dernière version de GitPR (Mise à jour automatique).

> **⚙️ Note technique (--hook) :** GitPR dispose d'un flag caché `--hook <file>` qui est déclenché exclusivement par le système de Git Hooks en arrière-plan. Il permet à l'IA d'injecter le message suggéré directement dans le fichier temporaire de Git, sans encombrer votre terminal.
>
> **⚙️ Note technique (--pre-save) :** GitPR dispose d'un flag de débogage caché `--pre-save` qui peut être combiné avec n'importe quelle commande IA (ex. : `gitpr -c --pre-save`). Avant chaque appel IA, il sauvegarde la charge utile complète qui sera envoyée au modèle (instruction système + prompt + compteurs de caractères) dans un fichier `_{action}-{datetime}.json` dans le dossier courant, puis procède normalement. Utile pour inspecter des prompts très volumineux. Note : lorsque la réponse provient du cache local, aucun appel n'est effectué et aucun fichier n'est généré.

### 📦 Diffs volumineux (Map-Reduce)

Lorsque votre diff est trop volumineux pour un seul appel IA (plus d'environ 90 000 tokens estimés), GitPR le divise automatiquement en lots par fichier, demande à l'IA un résumé technique de chaque partie (Map), et unifie le tout dans le message de commit, la revue ou la description de PR final (Reduce). Aucun flag nécessaire — il s'active à la demande et affiche la progression dans la console.

📚 Documentation complète : [docs/map-reduce-diff.md](https://github.com/natanfiuza/gitpr/blob/main/docs/map-reduce-diff.md)

## 🛡️ Linter local (Analyse statique)

GitPR CLI vous permet de définir des règles strictes qui seront validées instantanément lors de `--review` ou `--fullreview`, sans dépendre de l'IA. C'est idéal pour empêcher les erreurs courantes (comme `console.log` ou des IP de test) d'atteindre le dépôt.

### Comment configurer `.gitpr.linter.yml` :
Lors de l'exécution de `gitpr --skill`, un modèle sera généré. Vous pouvez configurer des règles à l'aide d'expressions régulières (Regex) :

```yaml
rules:
  - name: "check-localhost"
    extensions: ["js", "php"] # Extensions à valider
    regex: 'http(s)?://(localhost|127\.0\.0\.1)' # Quoi rechercher
    message: "🚨 Utilisation de localhost détectée dans le fichier {file_name}"
    ignore_comments: true # Ignore si la ligne est commentée
    ignore_paths: # Dossiers ou fichiers ignorés (accepte *)
      - "vendor/*"
      - "node_modules/*"
```

Le Linter analyse uniquement les **lignes ajoutées** dans votre `git diff`, garantissant une exécution ciblée et extrêmement rapide. En cas de violations, elles apparaîtront en évidence en haut de votre fichier de revue.

## 🧠 Architecture multi-modèles (IA-Agnostique)

GitPR n'est pas lié à une seule Intelligence Artificielle. Lors de la configuration initiale, l'utilisateur peut choisir son moteur par défaut. Nous supportons actuellement :
* **Google Gemini** (Par défaut : `gemini-2.5-flash`)
* **DeepSeek** (Par défaut : `deepseek-chat`)
* **Ollama** (Local) — exécutez des modèles localement sans connexion Internet, entièrement compatible avec le format de l'API OpenAI

Vous pouvez changer dynamiquement de modèle en configurant les variables `GEMINI_API_MODEL` ou `DEEPSEEK_API_MODEL` dans votre fichier `~/.gitpr/.env`, ou basculer en temps réel à l'aide du flag `--provider`.

## 🎯 Système de « Skills » personnalisables (Ingénierie de prompt)

Au lieu de cacher les instructions IA dans le code source, GitPR utilise des fichiers Markdown locaux qui agissent comme des *Instructions Système*. Lors de l'exécution de `gitpr -s`, les fichiers suivants sont générés à la racine de votre projet pour personnaliser la « personnalité » de l'IA selon les règles métier de votre entreprise :

* `.gitpr.commit.md` : Règles pour générer des messages de commit courts.
* `.gitpr.pr.md` : Structure thématique requise pour la description de la Pull Request.
* `.gitpr.review.md` : Définit le focus architectural (ex. : SOLID, Clean Code) pour l'analyse du diff.
* `.gitpr.filereview.md` : Définit des règles strictes de cohésion et de couplage pour l'audit complet de fichier (utilisé avec `--input`).
* `.gitpr.issue.md` : Définit la structure et le niveau de détail requis pour la génération d'Issues standardisées (utilisé avec `--issue`).
* `.gitpr.blame.md` : Définit le focus de l'analyse archéologique pour le traçage de code legacy (utilisé avec `--blame`).

## 🌐 Internationalisation (i18n)

GitPR détecte automatiquement la langue de votre système et affiche les messages dans votre langue maternelle. Le système i18n est inspiré du **helper `__()` de Laravel** :

* **Détection automatique :** Au premier lancement, GitPR détecte la langue de votre OS et la sauvegarde dans `~/.gitpr/.env` (`GITPR_LANG`).
* **Fichiers de traduction :** Les packs de langue sont téléchargés automatiquement depuis le dépôt officiel vers `~/.gitpr/langs/`.
* **Repli en anglais :** Si une traduction est manquante, le texte anglais est affiché directement.
* **API développeur :** Utilisez `from src.i18n import __` et encapsulez toutes les chaînes destinées à l'utilisateur avec `__("Your text here")`.
* **Espaces réservés :** Supporte les paramètres nommés — `__("Downloading {file}...", file="template.md")`.

Pour forcer une langue spécifique, définissez `GITPR_LANG=pt_br` ou `GITPR_LANG=en` dans `~/.gitpr/.env`.

> 📖 **Guide développeur complet :** [docs/i18n_explanation.md](https://github.com/natanfiuza/gitpr/blob/main/docs/i18n_explanation.md) — architecture, motifs d'utilisation, précautions contre les importations circulaires et comment ajouter de nouvelles langues.

## 🔌 Intégration MCP (Model Context Protocol)

GitPR peut fonctionner comme un **serveur MCP**, exposant ses capacités alimentées par l'IA sous forme d'outils que l'assistant IA de votre éditeur peut invoquer directement — sans avoir besoin du terminal. Cela permet un workflow entièrement intégré où vous pouvez générer des messages de commit, revoir du code, exécuter des linters, tracer l'origine du code et créer des issues sans quitter votre IDE.

### Éditeurs supportés

| Éditeur | Fichier de configuration |
| ------- | ------------------------ |
| **VS Code** | `.vscode/mcp.json` |
| **Cursor** | `.cursor/mcp.json` |
| **Claude Code** | `.mcp.json` |
| **Claude Desktop** | `claude_desktop_config.json` |
| **Zed** | `settings.json` |

### Configuration rapide

Utilisez l'installateur intégré pour configurer votre éditeur automatiquement :

```bash
gitpr-mcp --install vscode    # Crée .vscode/mcp.json
gitpr-mcp --install cursor      # Crée .cursor/mcp.json
gitpr-mcp --install claude-code # Crée .mcp.json
gitpr-mcp --install claude      # Met à jour la configuration de Claude Desktop
gitpr-mcp --install zed         # Met à jour les paramètres de Zed
gitpr-mcp --install auto      # Détection et installation automatiques pour tout ce qui est trouvé
```

L'installateur crée le répertoire de configuration si nécessaire, fusionne avec toute configuration existante (ne remplace jamais les autres serveurs), et peut être exécuté plusieurs fois sans risque.

> La configuration manuelle est également supportée — voir [docs/mcp-integration.md](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md)
> pour le format de configuration JSON pour chaque éditeur.

Une fois configuré, utilisez le langage naturel dans le chat IA de votre éditeur :

   * *« Review my current changes »* → appelle `review_code`
   * *« Generate a commit message »* → appelle `generate_commit_message`
   * *« Create a PR description »* → appelle `generate_pr_description`
   * *« Run the linter on my diff »* → appelle `run_linter`

### Outils MCP disponibles

| Outil | Description |
| ----- | ----------- |
| `get_git_context` | Branche actuelle, nom du dépôt et URL distante |
| `analyze_diff` | Diff Git des modifications non commitées |
| `get_full_diff` | Diff complet par rapport à origin/main |
| `generate_commit_message` | Message de commit Conventional Commits généré par IA |
| `review_code` | Revue de code IA des modifications locales |
| `full_review` | Revue de code IA de toutes les modifications depuis origin/main |
| `generate_pr_description` | Description complète de PR (titre + corps) |
| `run_linter` | Linter statique basé sur `.gitpr.linter.yml` |
| `analyze_blame` | Git blame + classification IA |
| `generate_issue` | Issue structurée à partir du diff, de l'historique ou du blame |

📖 **Documentation complète :** [docs/mcp-integration.md](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md) — disponible en 5 langues (EN, PT-BR, PT-PT, ES, FR).

## 📚 Documentation technique et guides avancés

Pour garder ce README concis, nous détaillons les implémentations les plus avancées axées sur le **DevOps** et l'**Intégration Continue** dans des documents séparés.

Si vous souhaitez implémenter GitPR comme une barrière de qualité automatisée dans votre équipe, consultez les guides ci-dessous.

> 🌐 Chaque guide est disponible en **5 langues** — ajoutez `.pt_br`, `.pt_pt`, `.fr_fr` ou `.es_es` avant l'extension `.md` pour les versions traduites (ex. : `docs/understanding_chat_functionality.pt_br.md`). L'anglais est la langue par défaut, sans suffixe.

### Chat et fonctions interactives

* [**🧠 Chat interactif (Programmation en paire)**](https://github.com/natanfiuza/gitpr/blob/main/docs/understanding_chat_functionality.md) — Comment utiliser le chat IA avec mémoire, commandes slash, correctif automatique et export de session.

### DevOps & CI/CD

* [**Git Hooks locaux (Shift-Left)**](https://github.com/natanfiuza/gitpr/blob/main/docs/git-hooks-locais.md) — Comment utiliser `gitpr --installhooks` pour créer des garde-fous sur la machine du développeur et utiliser l'IA pour écrire automatiquement les messages de commit.
* [**Linter statique personnalisable**](https://github.com/natanfiuza/gitpr/blob/main/docs/linter-regras-customizadas.md) — Comment créer des règles de validation dans `.gitpr.linter.yml` pour CI/CD et les hooks pre-commit.
* [**Intégration CI/CD (GitHub Actions)**](https://github.com/natanfiuza/gitpr/blob/main/docs/github-ci-linter.md) — Comment exécuter GitPR dans le pipeline pour bloquer le « Merge » des PR avec des violations.

### Fonctionnalités principales

* [**Pull Request (Mode par défaut)**](https://github.com/natanfiuza/gitpr/blob/main/docs/pr-descricao-padrao.md) — Flux complet pour générer des descriptions de PR sans flags.
* [**Revue de code IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/code-review-ia.md) — Guide des modes de revue (`--review`, `--fullreview`) et d'audit de fichier (`--input`).
* [**Messages de commit IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/commit-message-ia.md) — Comment générer des messages au format Conventional Commits et les intégrer avec les Git Hooks.
* [**Génération d'Issues et interface TUI**](https://github.com/natanfiuza/gitpr/blob/main/docs/issue-tui-help.md) — Comment utiliser l'interface graphique terminale (TUI) et les 3 moteurs de contexte pour gérer des Issues structurées.
* [**Archéologue de code (Git Blame)**](https://github.com/natanfiuza/gitpr/blob/main/docs/blame-arqueologo.md) — Comment tracer l'origine des règles métier avec `git blame` et l'IA.
* [**Système de Skills et modèles**](https://github.com/natanfiuza/gitpr/blob/main/docs/skill-template.md) — Comment personnaliser le comportement de l'IA avec les fichiers `.gitpr.*.md`.

### Configuration et infrastructure

* [**Fournisseurs IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/providers-ia.md) — Configuration et sélection entre Google Gemini, DeepSeek et Ollama.
* [**Mise à jour automatique**](https://github.com/natanfiuza/gitpr/blob/main/docs/auto-update.md) — Comment fonctionne la mise à jour automatique (hot-swap) de GitPR.
* [**Jeton GitHub (PAT) : intégration et sécurité**](https://github.com/natanfiuza/gitpr/blob/main/docs/github-pat-integration.md) — Comprenez comment GitPR crée des issues directement dans le dépôt avec authentification.
* [**Internationalisation (i18n)**](https://github.com/natanfiuza/gitpr/blob/main/docs/i18n_explanation.md) — Architecture, motifs d'utilisation et comment ajouter de nouvelles langues.
* [**Intégration MCP**](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md) — Connectez GitPR à VS Code, Cursor et Claude Desktop via le Model Context Protocol.

## ⚡ Système de cache local (Économie de quotas)

GitPR dispose d'un moteur de cache intelligent basé sur **MD5**. Chaque fois que vous exécutez une commande (`--review`, `--commit`, etc.), l'outil génère un hachage exact de votre code actuel (diff) et des instructions.
Si vous exécutez la même commande à nouveau sans modifier le code, GitPR intercepte la requête et renvoie le résultat instantanément (en millisecondes) depuis le dossier `~/.gitpr/cache/prompts/`, vous faisant gagner du temps et économisant vos quotas d'API Gemini !

## 🔄 Mise à jour automatique (Mise à jour Over-The-Air)

Ne vous souciez plus jamais de télécharger manuellement de nouvelles versions. GitPR dispose d'un gardien de connexion et d'un système de mise à jour intégré :
* Il vérifie la disponibilité du réseau avant de démarrer afin de ne pas bloquer votre workflow hors ligne.
* À chaque exécution, il vérifie silencieusement s'il existe une nouvelle version officielle sur l'API GitHub.
* Vous pouvez forcer la vérification et l'installation en exécutant `gitpr --update` ou `gitpr -u`.
* L'outil utilise la technique *Hot-Swap*, téléchargeant le nouveau `.exe` et remplaçant l'ancienne version de manière transparente.

## Publication sur PyPI

```bash
pipenv run python -m build
pipenv run twine upload dist/*
```
## **🤝 Comment contribuer**

Les contributions sont les bienvenues ! Pour contribuer :

1. Forkez le projet.
2. Créez une branche pour votre *fonctionnalité* (git checkout -b feature/NouvelleFonctionnalite).
3. Commitez vos modifications (git commit -m 'feat: ajouter nouvelle fonctionnalité'). Astuce : Utilisez GitPR lui-même pour générer ce message ! 😄
4. Poussez vers la branche (git push origin feature/NouvelleFonctionnalite).
5. Ouvrez une Pull Request.

## **✨ Remerciements et paternité**

Projet conçu et développé par :

**Natan Fiuza** - [contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br)

## **📄 Licence**

Ce projet est sous licence **GNU Lesser General Public License v2.1 (LGPL-2.1)**. Consultez le fichier LICENSE pour plus de détails.
