# Technologies et Bibliothèques

GitPR CLI est construit en **Python** et utilise un ensemble de bibliothèques soigneusement sélectionnées pour offrir une expérience rapide, sécurisée et conviviale.

---

## Bibliothèques Principales

### [Click](https://click.palletsprojects.com/)
Framework CLI robuste pour construire des interfaces en ligne de commande composables et conviviales. Alimente toutes les commandes, flags et la mise en forme du terminal de GitPR.

### [Google GenAI SDK](https://pypi.org/project/google-genai/)
SDK officiel pour l'intégration directe avec l'**API Gemini**. Utilisé pour les code reviews, messages de commit et descriptions de PR alimentés par IA.

### [OpenAI SDK](https://pypi.org/project/openai/)
Utilisé pour sa compatibilité totale avec l'**API DeepSeek** et **Ollama** (modèles locaux). Permet l'architecture multi-fournisseur sans dépendance à un vendor.

### [Textual](https://textual.textualize.io/)
Framework TUI puissant pour construire des interfaces de terminal riches. Anime le **chat interactif** (`--chat`), l'éditeur d'issues et le visualiseur de diff en temps réel.

---

## Sécurité et Configuration

### [Cryptography](https://cryptography.io/)
Fournit le **chiffrement symétrique Fernet** pour stocker les clés API en toute sécurité sur le disque. Votre `GEMINI_API_KEY` n'est jamais enregistrée en clair.

### [Python-dotenv](https://pypi.org/project/python-dotenv/)
Gère les variables d'environnement dans le fichier de configuration `~/.gitpr/.env`, gardant organisés les paramètres des fournisseurs et les préférences de langue.

### [PyYAML](https://pyyaml.org/)
Parse les règles d'analyse statique définies dans `.gitpr.linter.yml`, permettant des définitions de règles YAML lisibles pour le moteur du linter.

---

## Tests et HTTP

### [Pytest](https://docs.pytest.org/)
Framework de test moderne avec une sortie console colorée et lisible. Utilisé pour les tests unitaires et d'intégration sur tous les modules.

### [Requests](https://pypi.org/project/requests/)
Bibliothèque HTTP élégante pour la communication avec l'API REST de GitHub — utilisée par l'auto-updater, la soumission d'issues et la vérification des releases.

---

## Pourquoi Python ?

Python a été choisi pour son **cycle de développement rapide**, sa **compatibilité multiplateforme** (Windows, macOS, Linux), son riche écosystème de bibliothèques IA/LLM et sa capacité à compiler en un seul binaire sans dépendances avec **PyInstaller**.
