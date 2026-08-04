# GitPR CLI 🚀

<p align="center">
  <img src="https://raw.githubusercontent.com/natanfiuza/gitpr/main/docs/logo.png" alt="GitPR Logo" width="120">
</p>

**Automatisation de flux Git alimentée par IA** — Code Reviews, descriptions de PR, commits sémantiques et plus, directement depuis votre terminal.

GitPR CLI utilise **Google Gemini** et **DeepSeek** pour analyser votre `git diff` et des fichiers entiers, en générant :
- Des messages de commit au format **Conventional Commits**
- Des descriptions détaillées de **Pull Request**
- Des **Code Reviews** approfondis axés sur la réduction de la dette technique
- Des rapports de **linting statique** sans consommer de quotas IA

---

## ⚡ Démarrage Rapide

### 1. Installation via PyPI

Installez GitPR CLI avec `pip` :

```bash
pip install gitpr-cli
```

### 2. Initialisation dans un Dépôt

Pour configurer GitPR dans un dossier d'un nouveau dépôt, exécutez :

```bash
gitpr --install
```

La configuration guidée télécharge les modèles de skills, installe les Git Hooks, configure le MCP pour vos éditeurs et vérifie la clé API de votre fournisseur d'IA.

---

## 🎯 Fonctionnalités Clés

| Fonctionnalité | Commande | Description |
| --- | --- | --- |
| **Génération de PR** | `gitpr` | Génère automatiquement des descriptions de pull request à partir de votre diff |
| **Messages de Commit** | `gitpr -c` | Messages sémantiques au format Conventional Commits |
| **Code Review** | `gitpr -r` | Revue détaillée des modifications stagées |
| **Revue Complète** | `gitpr -f` | Revue complète par rapport à la branche distante |
| **Audit de Fichier** | `gitpr -r -i <fichier>` | Analyse complète de fichier, idéal pour le refactoring de code legacy |
| **Chat Interactif** | `gitpr -ch` | TUI de pair-programming avec mémoire, slash commands et auto-patch |
| **Linter Statique** | `gitpr -l` | Validation hors ligne des règles — coût IA zéro, prêt pour CI/CD |
| **Générateur d'Issues** | `gitpr -is` | Génère des issues structurées avec 3 moteurs de contexte |
| **Git Hooks** | `gitpr -ih` | Installe les hooks pre-commit et prepare-commit-msg localement |
| **Archéologue de Code** | `gitpr -b` | Trace l'origine des règles métier via `git blame` avec classification IA |
| **Auto-Update** | `gitpr -u` | Mise à jour hot-swap du binaire via GitHub Releases |

::: note Flags Techniques Cachés
- **`--hook <fichier>`** — Utilisé en interne par les Git Hooks pour injecter des messages de commit directement dans le fichier temporaire de Git.
- **`--pre-save`** — Flag de debug qui enregistre le payload complet de l'IA (instruction système + prompt) dans un fichier JSON avant chaque appel. Combinez avec n'importe quelle commande IA (ex : `gitpr -c --pre-save`).
:::

---

## 🧠 Architecture Multi-Modèle

GitPR est **agnostique de l'IA** — choisissez votre moteur :

- **Google Gemini** (défaut : `gemini-pro-latest`)
- **DeepSeek** (défaut : `deepseek-v4-pro`)
- **Ollama** — exécutez des modèles locaux sans internet

Changez à tout moment avec `--provider <gemini|deepseek|ollama>`.

---

## 🌐 Internationalisation

GitPR détecte automatiquement la langue de votre système. Prend actuellement en charge **FR** et **EN**, avec des traductions téléchargées automatiquement. Forcez une langue avec `--lang fr` ou définissez `GITPR_LANG` dans votre configuration.

---

## 📦 Map-Reduce pour les Gros Diffs

Lorsque votre diff est trop volumineux pour un seul appel IA (~90k tokens), GitPR le divise automatiquement par fichier, résume chaque partie (**Map**) et unifie le tout (**Reduce**) — aucun flag nécessaire.

---

## 🔒 Sécurité

Vos clés API sont chiffrées avec **Fernet (chiffrement symétrique)** et stockées dans `~/.gitpr/`. Ne partagez jamais votre fichier `secret.key`.

---

[Guide d'Installation →](/instalacao) &nbsp;|&nbsp; [Guide d'Utilisation →](/uso) &nbsp;|&nbsp; [Dépôt GitHub →](https://github.com/natafiuza/gitpr)
