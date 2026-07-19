# Installation et Configuration

Choisissez la méthode qui convient le mieux à votre flux de travail.

---

## Windows : Utiliser l'Exécutable

1. Téléchargez `gitpr.exe` depuis la page [GitHub Releases](https://github.com/natafiuza/gitpr/releases)
2. Déplacez-le dans un répertoire de votre `PATH` (ex : votre dossier utilisateur ou `C:\Windows\System32`)
3. Exécutez :

```bash
gitpr
```

Lors de la première exécution, l'assistant de configuration vous guidera :

```
🚀 Automatisation Intelligente de PR avec IA

🔧 Première exécution détectée ! Configurons GitPR CLI.

🔑 Entrez votre GEMINI_API_KEY :

📄 Modèle de nom de fichier de sortie [{branch}_{datetime}_PR_DESC.md] :
```

Votre configuration est enregistrée en toute sécurité dans `~/.gitpr/.env`.

---

## Linux / macOS : Via PyPI (Recommandé)

Installez directement depuis [PyPI](https://pypi.org/project/gitpr-cli/) :

```bash
pip install gitpr-cli
```

Puis exécutez :

```bash
gitpr
```

Lors de la première exécution, l'assistant vous guidera dans la configuration de la clé API.

---

## À Partir du Code Source

```bash
# 1. Clonez le dépôt
git clone https://github.com/natanfiuza/gitpr.git

# 2. Entrez dans le répertoire
cd gitpr

# 3. Installez les dépendances
pipenv install google-genai openai python-dotenv click cryptography

# 4. Exécutez
pipenv run python src/main.py
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
| `GEMINI_API_MODEL` | Version du modèle Gemini | `gemini-2.5-flash` |
| `DEEPSEEK_API_KEY` | Votre clé API DeepSeek (chiffrée) | — |
| `DEEPSEEK_API_MODEL` | Version du modèle DeepSeek | `deepseek-chat` |
| `GITPR_PROVIDER` | Fournisseur IA par défaut | `gemini` |
| `GITPR_LANG` | Langue de l'interface | détectée automatiquement |

---

[← Accueil](/index) &nbsp;|&nbsp; [Guide d'Utilisation →](/uso)
