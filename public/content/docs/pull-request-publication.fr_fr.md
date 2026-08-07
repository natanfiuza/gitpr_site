# Documentation technique : publication de PR sur GitHub (`--publish`)

Cette documentation décrit le flux de publication de Pull Requests via l'interface interactive en terminal (TUI), vous permettant de consulter, modifier et publier des Pull Requests directement sur GitHub sans quitter le terminal.

---

## 1. Qu'est-ce que le Publisher de PR ?

Lorsque vous exécutez la commande `gitpr --publish`, GitPR génère la description de la PR avec l'IA (comme la commande par défaut), enregistre le fichier `.md` localement et ouvre un panneau interactif directement dans le terminal. Cela vous permet de consulter, modifier et publier la Pull Request générée par l'intelligence artificielle avant de l'envoyer au dépôt distant via l'API REST.

---

## 2. Modes de publication

Le Publisher de PR dispose de **3 modes d'exécution**, déclenchés selon les flags combinés avec `--publish`.

### 2.1 Mode interactif — `gitpr --publish`

Ouvre la TUI pour la consultation et la modification avant publication.

```bash
gitpr --publish
```

| Caractéristique | Description |
|---|---|
| **Flux** | `git fetch` → l'IA génère la PR → `.md` enregistré → la TUI s'ouvre → l'utilisateur modifie → POST vers GitHub |
| **Quand l'utiliser** | Lorsque vous souhaitez consulter et ajuster le contenu de la PR avant publication |
| **Résultat** | Pull Request créée sur GitHub avec le contenu modifié |
| **Idéal pour** | Flux de travail standard — contrôle total sur ce qui est publié |

> **Conseil :** Le fichier local `.md` est enregistré avant l'ouverture de la TUI et ré-enregistré avec toutes les modifications avant publication. Vous disposez toujours d'une sauvegarde.

---

### 2.2 Publication directe — `gitpr --publish --no-edit`

Ignore l'éditeur interactif et publie directement.

```bash
gitpr --publish --no-edit
```

| Caractéristique | Description |
|---|---|
| **Flux** | `git fetch` → l'IA génère la PR → `.md` enregistré → POST direct vers GitHub |
| **Quand l'utiliser** | Lorsque vous faites confiance au résultat de l'IA et souhaitez publier immédiatement |
| **Résultat** | Pull Request créée sur GitHub sans ouvrir la TUI |
| **Idéal pour** | Pipelines CI/CD, corrections rapides, flux de travail automatisés |

> **Attention :** À utiliser avec précaution — vous n'aurez pas la possibilité de consulter ou de modifier le contenu avant la publication.

---

### 2.3 Mode publication automatique — `PR_AUTO_PUBLISH=true`

Configure GitPR pour toujours ouvrir la TUI du publisher après la génération d'une description de PR.

```bash
# Dans ~/.gitpr/.env
PR_AUTO_PUBLISH=true
```

| Caractéristique | Description |
|---|---|
| **Activation** | Variable d'environnement dans `~/.gitpr/.env` |
| **Comportement** | Chaque exécution de `gitpr` ouvre la TUI du publisher après la génération de la PR |
| **Quand l'utiliser** | Lorsque vous souhaitez toujours publier après la génération de la description de la PR |
| **Idéal pour** | Équipes qui suivent un flux de travail « générer et publier » |

---

## 3. Configuration de la branche de base

La branche cible de la Pull Request est résolue dans l'ordre de priorité suivant :

| Priorité | Source | Comment configurer |
|---|---|---|
| **1 (la plus élevée)** | flag `--base` | `gitpr --publish --base develop` |
| **2** | variable d'env `PR_DEFAULT_BASE` | `PR_DEFAULT_BASE=develop` dans `~/.gitpr/.env` |
| **3 (par défaut)** | Détection automatique | `git symbolic-ref refs/remotes/origin/HEAD` (généralement `main` ou `master`) |

---

## 4. Raccourcis et navigation dans la TUI

L'interface a été conçue pour être rapide et ne pas nécessiter une utilisation constante de la souris. Vous pouvez naviguer entre les champs avec la touche `Tab` et utiliser les raccourcis suivants :

| Touche | Action | Description |
|---|---|---|
| **`F1`** | Aide | Ouvre une fenêtre modale flottante avec des instructions rapides d'utilisation de l'interface |
| **`F2`** | Enregistrer le `.md` local | Enregistre le contenu mis à jour dans le fichier de description de la PR du projet actuel. Idéal lorsque vous souhaitez affiner le contenu plus tard |
| **`F3`** | Publier la PR | Se connecte à l'API REST de GitHub et crée la Pull Request dans le dépôt distant. Le lien direct vers la PR nouvellement créée s'affichera dans le terminal |
| **`Esc`** | Quitter | Annule l'opération et ferme l'interface sans publier |
| **`Tab`** | Naviguer | Alterne le focus entre les champs de l'interface |

---

## 5. Intégration GitHub (jeton PAT)

Pour créer des Pull Requests directement dans le dépôt distant (`F3`), GitPR a besoin d'un **jeton d'accès personnel (PAT)** GitHub avec le scope `repo`.

### 5.1 Configuration du jeton

La première fois que vous utilisez `F3`, GitPR :

1. Détecte qu'aucun jeton n'est configuré
2. Affiche l'URL de génération du jeton avec des paramètres pré-remplis (scope `repo`)
3. Vous demande de coller le jeton généré
4. Le stocke chiffré (Fernet) dans le fichier `~/.gitpr/.env`

> **Remarque :** La TUI des issues (`gitpr -is`) partage le même jeton. Si vous avez déjà configuré un jeton pour les Issues, il sera réutilisé automatiquement.

### 5.2 Sécurité

- Le jeton est stocké sous forme de hash chiffré — jamais en texte clair
- La clé maîtresse de déchiffrement se trouve dans `~/.gitpr/secret.key`
- Le jeton est validé via `GET /user` avant l'ouverture de la TUI
- Consultez le guide complet dans [github-pat-integration.md](github-pat-integration.md)

---

## 6. API GitHub — création de PR

La PR est créée via `POST https://api.github.com/repos/{owner}/{repo}/pulls` avec la charge utile suivante :

```json
{
  "title": "PR title (editable in TUI)",
  "body": "Full markdown PR description with commit message",
  "head": "Current branch (source)",
  "base": "Target branch (main, develop, etc.)"
}
```

---

## 7. Gestion des erreurs

| Erreur | Comportement |
|---|---|
| Jeton invalide/expiré (401) | Demande un nouveau jeton (jusqu'à 3 tentatives) |
| Branche introuvable (422) | Affiche le message d'erreur de GitHub avec les détails |
| Aucun commit à fusionner (422) | Affiche une erreur de validation suggérant d'apporter des modifications d'abord |
| La PR existe déjà (422) | Affiche le conflit spécifique |
| Échec réseau | Affiche un message d'erreur de connexion |
| Dépôt distant manquant | Erreur avant l'ouverture de la TUI — aucun appel API tenté |

---

## 8. Variables d'environnement

| Variable | Défaut | Description |
|---|---|---|
| `GITHUB_TOKEN_ENCRYPTED` | *(aucun)* | Jeton d'accès personnel GitHub chiffré |
| `PR_DEFAULT_BASE` | *(vide)* | Branche cible par défaut (utilise la détection automatique si vide) |
| `PR_AUTO_PUBLISH` | `false` | Définir à `true` pour toujours ouvrir le publisher après la génération de la PR |

---

## 9. Exemples pratiques

### Exemple 1 : Consulter et publier une fonctionnalité

```bash
# Vous avez fini de développer sur la branche feature/login
gitpr --publish
# → L'IA génère la description de la PR et ouvre la TUI
# → Vérifiez le titre, le corps et la branche de base
# → Appuyez sur F3 pour créer la PR sur GitHub
```

### Exemple 2 : Publication rapide sans modification

```bash
gitpr --publish --no-edit
# → La PR est générée et publiée immédiatement
# → L'URL de la PR est affichée dans le terminal
```

### Exemple 3 : Publier vers une branche de base personnalisée

```bash
gitpr --publish --base staging
# → La branche cible est définie sur "staging" au lieu de "main"
```

---

## 10. Fichiers associés

| Fichier | Fonction |
|---|---|
| `.gitpr.pr.md` | Modèle local avec règles personnalisées pour la génération de la description de PR (téléchargement avec `gitpr -s`) |
| `~/.gitpr/.env` | Configuration globale : clés API, valeurs par défaut de la PR et jeton GitHub chiffré |
| `~/.gitpr/secret.key` | Clé maîtresse Fernet pour le déchiffrement des identifiants |

> **Remarque :** Consultez également la [documentation principale (README.md)](../README.md) pour une vue d'ensemble de toutes les fonctionnalités de GitPR et le [guide de description de PR](pr-descricao-padrao.md) pour le flux de génération de PR par défaut.
