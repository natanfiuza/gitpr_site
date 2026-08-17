# Installation et Configuration

Choisissez la méthode qui convient le mieux à votre flux de travail.

---

## ⚡ Démarrage Rapide

### 1. Installation via PyPI

Installez GitPR CLI avec `pip` :

```bash
pip install gitpr-cli
```

### 2. Initialisation dans un Dépôt

Pour configurer GitPR dans le dossier d'un nouveau dépôt, exécutez :

```bash
gitpr --install
```

> **Setup Guidé :** Configuration interactive qui télécharge les templates de skill, installe les Git Hooks, configure MCP pour vos éditeurs et vérifie la clé API de votre fournisseur IA.  
> 📖 **Documentation Complète :** [Guide de l'Install Wizard](https://gitpr.natanfiuza.dev.br/docs/install-wizard?lang=fr_fr)

---

## Windows : Utiliser l'Exécutable

1. Téléchargez `gitpr.exe` depuis la page [GitHub Releases](https://github.com/natafiuza/gitpr/releases)
2. Déplacez-le dans un répertoire de votre `PATH` (ex : votre dossier utilisateur ou `C:\Windows\System32`)
3. Exécutez le setup guidé :

```bash
gitpr --install
```

L'assistant vous guidera à travers :

```
🚀 Automatisation Intelligente de PR avec IA

🔧 Assistant de Configuration Interactive

📥 Téléchargement des templates de skill...
🪝 Installation des Git Hooks (pre-commit, prepare-commit-msg)...
🔌 Configuration MCP pour les éditeurs détectés...
🔑 Vérification de la clé API de votre fournisseur IA...
```

Votre configuration est enregistrée en toute sécurité dans `~/.gitpr/.env`.

---

## Linux / macOS : Via PyPI (Recommandé)

Installez directement depuis [PyPI](https://pypi.org/project/gitpr-cli/) :

```bash
pip install gitpr-cli
```

Puis initialisez dans votre dépôt :

```bash
gitpr --install
```

Le setup guidé vous orientera à travers les templates de skill, les Git Hooks, la configuration MCP et la vérification de la clé API.

---

## À Partir du Code Source

```bash
# 1. Clonez le dépôt
git clone https://github.com/natanfiuza/gitpr.git

# 2. Entrez dans le répertoire
cd gitpr

# 3. Installez les dépendances
pipenv install google-genai openai python-dotenv click cryptography

# 4. Exécutez le setup guidé
pipenv run python src/main.py --install
```

---

## Compiler Votre Propre Exécutable

Utilisez **PyInstaller** pour générer un binaire autonome :

```bash
# Installez les dépendances de développement
pipenv install --dev

# Compilez
pipenv run pyinstaller --noconfirm --onefile --icon=icon.ico --name gitpr run.py
```

Le binaire se trouvera dans le dossier `dist/` :
- `gitpr` sur Linux/macOS
- `gitpr.exe` sur Windows

Le flag `--onefile` empaquète Python, toutes les bibliothèques et dépendances dans un seul exécutable.

---

## 🔒 Sécurité

GitPR utilise le **chiffrement symétrique Fernet** pour protéger vos clés API :

- Votre `GEMINI_API_KEY` est stockée sous forme de hash chiffré dans `~/.gitpr/.env`
- Une clé maîtresse de déchiffrement est générée automatiquement dans `~/.gitpr/secret.key`
- **Ne partagez jamais votre fichier `secret.key`**

---

## Référence de Configuration

Tous les paramètres se trouvent dans `~/.gitpr/.env` :

| Variable | Description | Défaut |
| --- | --- | --- |
| `GEMINI_API_KEY` | Votre clé API Google Gemini (chiffrée) | — |
| `GEMINI_API_MODEL_PRIMARY` | Version du modèle Gemini | `gemini-pro-latest` |
| `DEEPSEEK_API_KEY` | Votre clé API DeepSeek (chiffrée) | — |
| `DEEPSEEK_API_MODEL_PRIMARY` | Version du modèle DeepSeek | `deepseek-v4-pro` |
| `GITPR_PROVIDER` | Fournisseur IA par défaut | `gemini` |
| `GITPR_LANG` | Langue de l'interface | détectée automatiquement |

---

[← Accueil](/index) &nbsp;|&nbsp; [Guide d'Utilisation →](/uso)
