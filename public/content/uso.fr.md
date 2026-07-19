# Comment Utiliser GitPR CLI

GitPR a un comportement par défaut puissant et des options avancées pour chaque étape de votre flux de travail Git.

---

## Comportement par Défaut : Génération de PR

Exécutez simplement :

```bash
gitpr
```

L'outil va :
1. Se synchroniser avec le remote (`git fetch`)
2. Comparer vos modifications avec `origin/main`
3. Générer un fichier Markdown (ex : `feature-login_20260421110134_PR_DESC.md`) avec la description complète du Pull Request

---

## Commandes et Flags

### 🔖 Message de Commit

```bash
gitpr -c
# ou
gitpr --commit
```

Exécute `git diff` et affiche un message au format **Conventional Commits**. Idéal pour des commits rapides et standardisés.

---

### 🔍 Code Review (Modifications Stagées)

```bash
gitpr -r
# ou
gitpr --review
```

Revue détaillée par IA de vos modifications locales stagées. Se concentre sur les bugs, la sécurité, la performance et la qualité du code.

---

### 🔎 Code Review Complète

```bash
gitpr -f
# ou
gitpr --fullreview
```

Revue complète analysant **toutes les modifications depuis la branche distante**. Idéal pour des revues de PR exhaustives.

---

### 📄 Audit de Fichier Complet

```bash
gitpr -r -i src/module_legacy.py
# ou
gitpr --review --input chemin/vers/fichier
```

Ignore l'historique git et audite le **fichier entier**. Excellent pour le conseil en refactoring de code legacy. Doit être utilisé avec `-r` ou `-f`.

---

### 💬 Chat Interactif (Pair Programming)

```bash
gitpr -ch
# ou
gitpr --chat
```

Ouvre un **terminal TUI** où l'IA voit votre diff actuel et maintient une conversation contextuelle :

| Raccourci | Action |
| --- | --- |
| `F2` | Rafraîchir le contexte du diff |
| `F5` | Extraire les blocs de code vers un fichier patch |
| `F6` | Exporter la session en Markdown |
| `/explain` | Expliquer le diff actuel |
| `/tests` | Générer des tests unitaires |
| `/optimize` | Suggérer des optimisations |
| `/clear` | Effacer la mémoire de la conversation |

La mémoire est **par branche**, donc changer de branche vous donne un contexte propre.

---

### 🛡️ Linter Statique

```bash
gitpr -l
# ou
gitpr --linter
```

Exécute **uniquement le linter statique local** — coût IA zéro. Valide les lignes modifiées contre les règles dans `.gitpr.linter.yml`. Parfait pour les pipelines CI/CD et les hooks pre-commit.

---

### 🎫 Générateur d'Issues

```bash
gitpr -is
# ou
gitpr --issue
```

Ouvre un **panneau TUI** interactif pour éditer et soumettre des issues structurées. **3 moteurs de contexte** :

| Commande | Contexte | Cas d'Usage |
| --- | --- | --- |
| `gitpr -is` | `git diff` actuel | Documenter une tâche que vous venez de coder |
| `gitpr -is -ht` | Historique complet de la branche | Générer la documentation d'une release/epic |
| `gitpr -is -b fichier:lignes` | Chronologie via `git blame` | Documenter l'évolution du code legacy et la dette technique |

---

### 🪝 Git Hooks

```bash
gitpr -ih
# ou
gitpr --installhooks
```

Installe les hooks `pre-commit` et `prepare-commit-msg` dans votre dépôt pour des barrières de qualité automatiques.

---

### 🎨 Templates de Skills

```bash
gitpr -s
# ou
gitpr --skill
```

Génère des templates de prompt IA personnalisables (fichiers `.gitpr.*.md`) et des règles de linter (`.gitpr.linter.yml`) à la racine de votre projet.

---

### 🌐 Remplacement de Langue et Fournisseur

```bash
# Forcer la langue pour cette exécution
gitpr --lang fr

# Changer de fournisseur IA à la volée
gitpr --provider deepseek
gitpr --provider ollama
```

---

### 🔄 Auto-Updater

```bash
gitpr -u
# ou
gitpr --update
```

Vérifie sur GitHub Releases la dernière version et effectue un hot-swap du binaire.

---

### ❓ Aide

```bash
gitpr -h              # Aide générale
gitpr -h --issue      # Aide contextuelle pour la commande issue
gitpr -h --linter     # Aide contextuelle pour la commande linter
```

---

[← Installation](/instalacao) &nbsp;|&nbsp; [Guide du Linter →](/linter)
