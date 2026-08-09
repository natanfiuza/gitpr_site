# Documentation Technique : Génération de Pull Request (Mode par Défaut)

Lorsqu'il est exécuté **sans flags**, GitPR génère automatiquement une description complète de Pull Request en Markdown, prête à être collée sur GitHub, GitLab ou Bitbucket.

---

## 1. Utilisation

```bash
gitpr
```

---

## 2. Flux d'Exécution

```
git fetch → diff contre origin/main → IA → .md
```

1. **`git fetch`** — Synchronise avec le dépôt distant
2. **Diff** — Compare toutes les modifications de la branche actuelle par rapport à `origin/main`
3. **IA** — Génère le message de commit (Conventional Commits) et la description de la PR
4. **Output** — Enregistre un fichier `.md` à la racine du projet

---

## 3. Output

Le fichier généré (`{branch}_{datetime}_PR_DESC.md`) contient :

```markdown
# 🚀 Suggestion de Pull Request

**Message de Commit Recommandé :**
feat: description courte du changement

---

## Description
...
## Modifications
...
## Impact
...
```

---

## 4. Personnalisation

### 4.1 Template de PR

Le comportement de l'IA peut être personnalisé via le fichier `.gitpr.pr.md` :

```bash
gitpr -s          # Télécharge le template
# Éditez .gitpr.pr.md avec les sections obligatoires de votre équipe
gitpr             # L'IA suivra votre template
```

### 4.2 Nom du Fichier de Sortie

Configurez la variable d'environnement `OUTPUT_FILE_NAME` dans le fichier `~/.gitpr/.env` :

```ini
OUTPUT_FILE_NAME=PR_{branch}_{datetime}.md
```

Variables disponibles : `{branch}` (nom de la branche actuelle) et `{datetime}` (timestamp `YYYYMMDDHHMMSS`).

---

## 5. Sélection du Fournisseur d'IA

```bash
gitpr -p gemini       # Force Google Gemini
gitpr -p deepseek     # Force DeepSeek
```

Si aucun fournisseur n'est spécifié, GitPR utilise celui par défaut défini dans la variable `DEFAULT_AI_PROVIDER` de `~/.gitpr/.env`.

---

## 6. Cache des Réponses

GitPR génère un hash MD5 du diff + instructions de l'IA. Si vous exécutez à nouveau `gitpr` **sans modifier le code**, la réponse est renvoyée depuis le cache local en millisecondes, sans consommer de quotas API.

> **Note :** Consultez également la [documentation principale (README.md)](../README.md) pour un aperçu de toutes les fonctionnalités.
