# Système de Skills — Ingénierie de Prompt

GitPR utilise des **templates Markdown personnalisables** comme instructions système de l'IA. Au lieu de coder les prompts en dur, vous contrôlez la "persona" de l'IA via des fichiers locaux `.gitpr.*.md` — adaptés aux conventions et règles métier de votre équipe.

---

## Génération des Templates

```bash
gitpr -s
# ou
gitpr --skill
```

Cela crée les fichiers suivants à la racine de votre projet :

---

## Fichiers de Template

### `.gitpr.commit.md`
Règles pour générer des messages de commit. Définissez votre format préféré, le ton et les conventions.

```markdown
# Exemple de personnalisation
- Utilisez le format Conventional Commits
- Maximum 72 caractères pour la ligne de résumé
- Incluez le scope quand applicable : feat(scope): description
- Utilisez le mode impératif ("ajoute" pas "ajouté")
```

---

### `.gitpr.pr.md`
Structure requise pour les descriptions de Pull Request. Définissez les sections, le niveau de détail et le formatage.

```markdown
# Exemple de personnalisation
Votre description de PR doit inclure :
1. **Résumé** — un paragraphe décrivant la modification
2. **Motivation** — pourquoi cette modification est nécessaire
3. **Tests** — comment la modification a été testée
4. **Captures d'écran** — si des modifications UI sont impliquées
5. **Breaking Changes** — listez toute modification incompatible
```

---

### `.gitpr.review.md`
Focus architectural pour l'analyse de diff. Définissez ce que l'IA doit prioriser lors des code reviews.

```markdown
# Exemple de personnalisation
Concentrez votre revue sur :
- Les violations des principes SOLID
- Les vulnérabilités de sécurité (OWASP Top 10)
- Les goulets d'étranglement de performance
- Les lacunes dans la gestion des erreurs
- L'adéquation de la couverture de tests
```

---

### `.gitpr.filereview.md`
Règles strictes de cohésion et de couplage pour l'audit complet de fichiers (utilisé avec `--input`).

```markdown
# Exemple de personnalisation
Analysez ce fichier à la recherche de :
- Violations du Principe de Responsabilité Unique
- Couplage fort avec des services externes
- Absence d'injection de dépendance
- Nombres magiques et valeurs codées en dur
- Fonctions de plus de 30 lignes
```

---

### `.gitpr.issue.md`
Structure et niveau de détail pour la génération standardisée d'issues (utilisé avec `--issue`).

```markdown
# Exemple de personnalisation
Les issues doivent contenir :
1. **Description** — énoncé clair du problème
2. **Étapes pour Reproduire** — liste numérotée
3. **Comportement Attendu**
4. **Comportement Actuel**
5. **Environnement** — OS, version, etc.
6. **Critères d'Acceptation** — format checklist
```

---

### `.gitpr.blame.md`
Focus pour l'analyse archéologique lors du traçage de l'évolution du code legacy (utilisé avec `--blame`).

```markdown
# Exemple de personnalisation
Lors du traçage de l'historique du code, identifiez :
- Le commit et l'auteur d'origine
- Pourquoi la décision a été prise (à partir des messages de commit)
- Les approches alternatives considérées
- La pertinence actuelle des contraintes d'origine
```

---

## Comment Ça Marche

1. **Templates spécifiques au projet** — chaque dépôt peut avoir ses propres conventions
2. **L'IA les lit comme instructions système** — elles sont préfixées à chaque prompt pertinent
3. **Versionnés** — committez-les dans votre dépôt pour que toute l'équipe partage les mêmes normes
4. **Personnalisation sans code** — pas besoin de modifier le code source de GitPR

---

## Exemple de Flux

```bash
# 1. Générer les templates
gitpr -s

# 2. Les personnaliser pour votre équipe
vim .gitpr.commit.md
vim .gitpr.review.md

# 3. Les committer dans le dépôt
git add .gitpr.*.md
git commit -m "feat: ajouter des templates de skill GitPR personnalisés"

# 4. Chaque membre de l'équipe reçoit maintenant le même comportement IA
```

---

[← Fournisseurs IA](/providers) &nbsp;|&nbsp; [Internationalisation →](/i18n)
