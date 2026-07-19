# Contribuer à GitPR CLI

Les contributions sont les bienvenues ! Voici comment participer.

---

## Comment Contribuer

1. **Forkez** le projet sur [GitHub](https://github.com/natafiuza/gitpr)
2. **Créez une branche** pour votre fonctionnalité :
   ```bash
   git checkout -b feature/NouvelleFonctionnalite
   ```
3. **Commitez vos modifications :**
   ```bash
   git commit -m 'feat: ajouter une nouvelle fonctionnalité'
   ```
   > 💡 **Astuce :** Utilisez GitPR lui-même pour générer ce message de commit ! Exécutez simplement `gitpr -c`.

4. **Poussez** vers votre branche :
   ```bash
   git push origin feature/NouvelleFonctionnalite
   ```
5. **Ouvrez une Pull Request** sur le dépôt principal

---

## Configuration de Développement

```bash
# Cloner et entrer
git clone https://github.com/natafiuza/gitpr.git
cd gitpr

# Installer toutes les dépendances (dev incluses)
pipenv install --dev

# Exécuter les tests
pipenv run pytest -v

# Exécuter GitPR depuis le code source
pipenv run python src/main.py
```

---

## Exécution des Tests

```bash
# Exécuter tous les tests avec sortie détaillée
pipenv run pytest -v

# Exécuter un fichier de test spécifique
pipenv run pytest tests/test_core.py -v

# Exécuter avec rapport de couverture
pipenv run pytest --cov=src --cov-report=term-missing
```

---

## Domaines de Contribution

| Domaine | Description |
| --- | --- |
| **Nouveaux Fournisseurs** | Ajouter le support pour d'autres fournisseurs IA (Claude, LLMs locaux, etc.) |
| **Nouvelles Langues** | Traduire GitPR dans votre langue |
| **Règles de Linter** | Partager des ensembles de règles utiles pour différentes stacks |
| **Documentation** | Améliorer la documentation, ajouter des exemples, corriger des fautes |
| **Correction de Bugs** | Consulter l'onglet issues pour les bugs signalés |
| **Améliorations TUI** | Améliorer l'interface du chat interactif et de l'éditeur d'issues |

---

## Structure du Projet

```
gitpr/
├── src/
│   ├── main.py           # Point d'entrée CLI et routage des commandes
│   ├── core.py            # Opérations Git et intégration IA
│   ├── config.py          # Gestion de la configuration et .env
│   ├── security.py        # Chiffrement (Fernet)
│   ├── linter_engine.py   # Moteur d'analyse statique
│   ├── updater.py         # Auto-updater (hot-swap)
│   └── i18n.py            # Helper d'internationalisation
├── tests/                 # Tests unitaires et d'intégration
├── langs/                 # Fichiers de traduction (JSON)
├── docs/                  # Documentation étendue
└── run.py                 # Point d'entrée PyInstaller
```

---

## Publication sur PyPI

Pour les mainteneurs :

```bash
pipenv run python -m build
pipenv run twine upload dist/*
```

---

## Licence

Ce projet est sous licence **GNU Lesser General Public License v2.1 (LGPL-2.1)**.

Consultez le fichier [LICENSE](https://github.com/natafiuza/gitpr/blob/main/LICENSE) pour plus de détails.

---

## Remerciements

Projet conçu et développé par **Natan Fiuza** — [contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br)

---

[← Cache et Mises à Jour](/cache)
