# Linter Local — Analyse Statique

Le linter de GitPR valide le code par rapport à des règles personnalisées **sans consommer de quotas IA**. Il analyse uniquement les **lignes ajoutées** dans votre `git diff`, le rendant rapide, ciblé et prêt pour le CI/CD.

---

## Démarrage Rapide

```bash
# Générer la configuration par défaut du linter
gitpr -s

# Exécuter le linter en standalone (sans IA)
gitpr -l
```

Le linter s'exécute également automatiquement dans le cadre de `--review` et `--fullreview`, avec les violations mises en évidence en haut de la sortie de revue.

---

## Configuration : `.gitpr.linter.yml`

Définissez des règles en utilisant des **Expressions Régulières** :

```yaml
rules:
  - name: "check-localhost"
    extensions: ["js", "php", "py"]
    regex: 'http(s)?://(localhost|127\.0\.0\.1)'
    message: "🚨 Utilisation de localhost détectée dans le fichier {file_name}"
    ignore_comments: true
    ignore_paths:
      - "vendor/*"
      - "node_modules/*"
      - "tests/*"

  - name: "no-console-log"
    extensions: ["js", "ts"]
    regex: 'console\.log\('
    message: "🚨 console.log() trouvé dans {file_name}:{line_number}"
    ignore_comments: false

  - name: "no-debugger"
    extensions: ["js", "ts"]
    regex: 'debugger'
    message: "🚨 instruction debugger trouvée dans {file_name}:{line_number}"
    ignore_comments: true

  - name: "no-todo-without-ticket"
    extensions: ["*"]
    regex: 'TODO(?!\s*\(\w+-\d+\))'
    message: "📝 TODO sans référence de ticket dans {file_name}:{line_number}"
    ignore_comments: false
```

---

## Champs de Règle

| Champ | Obligatoire | Description |
| --- | --- | --- |
| `name` | Oui | Identifiant unique de la règle |
| `extensions` | Oui | Extensions de fichier à vérifier (`["*"]` pour tous) |
| `regex` | Oui | Expression régulière à rechercher |
| `message` | Oui | Message de violation. Prend en charge `{file_name}` et `{line_number}` |
| `ignore_comments` | Non | Ignorer les lignes commentées (défaut : `false`) |
| `ignore_paths` | Non | Patterns glob pour les répertoires/fichiers à ignorer |

---

## Intégration CI/CD

Exécutez le linter dans votre pipeline pour **bloquer les merges** avec des violations :

### Exemple GitHub Actions

```yaml
name: GitPR Linter
on: [pull_request]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - name: Exécuter GitPR Linter
        run: |
          gitpr --linter
```

---

## Hooks Pre-Commit

Installez automatiquement avec :

```bash
gitpr --installhooks
```

Cela crée les hooks `pre-commit` et `prepare-commit-msg` qui exécutent le linter avant chaque commit, détectant les problèmes au moment le plus précoce possible (approche **Shift-Left**).

---

## Pourquoi un Linter Local ?

- **Coût IA zéro** — pas d'appels API, pas de limites de débit
- **Retour instantané** — s'exécute en millisecondes
- **Personnalisable** — des règles qui correspondent aux normes de VOTRE équipe
- **Conscient de Git** — vérifie uniquement ce que vous avez modifié, pas toute la base de code
- **Natif CI/CD** — une seule commande, aucun service externe

---

[← Guide d'Utilisation](/uso) &nbsp;|&nbsp; [Fournisseurs IA →](/providers)
