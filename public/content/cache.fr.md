# Système de Cache et Auto-Updater

GitPR inclut un cache intelligent pour économiser les quotas API et un auto-updater transparent pour vous maintenir à la dernière version.

---

## ⚡ Système de Cache Local

Chaque fois que vous exécutez une commande alimentée par IA (`--review`, `--commit`, etc.), GitPR génère un **hash MD5** de votre code actuel (diff) combiné avec vos instructions.

Si vous exécutez la **même commande** sans modifier le code, GitPR intercepte la requête et renvoie le résultat **instantanément depuis le cache** — pas d'appel API, pas de quota dépensé.

### Comment Ça Marche

1. La commande s'exécute → diff + instructions sont hashés (MD5)
2. Si le hash existe dans `~/.gitpr/cache/prompts/` → renvoie le résultat en cache
3. Sinon → appelle l'IA, enregistre la réponse, renvoie le résultat

### Avantages

- **Zéro appel API en double** — ré-exécuter la même revue ne coûte rien
- **Réponses en millisecondes** — les lectures de cache sont instantanées
- **Invalidation automatique** — toute modification de code produit un hash différent
- **Transparent** — aucun flag nécessaire, toujours actif

---

## 🔄 Auto-Updater (Mise à Jour OTA)

GitPR vérifie les mises à jour silencieusement à chaque exécution et peut effectuer un hot-swap du binaire en quelques secondes.

### Vérifier et Mettre à Jour

```bash
# Forcer la vérification de mise à jour
gitpr -u
# ou
gitpr --update
```

### Comment Ça Marche

1. **Gardien de Connexion :** Vérifie la disponibilité du réseau avant de démarrer — ne bloque jamais les flux de travail hors ligne
2. **Vérification silencieuse en arrière-plan :** À chaque exécution, compare la version locale avec la dernière Release GitHub
3. **Technique Hot-Swap :** Télécharge le nouveau binaire, renomme l'ancien comme sauvegarde et le remplace de manière transparente — pendant que l'exécution actuelle se termine normalement
4. **Capacité de rollback :** Si la nouvelle version échoue, l'ancien binaire est toujours sur le disque

### Vérification de Version

GitPR utilise des **checksums SHA-256** publiés avec chaque Release GitHub pour vérifier l'intégrité du binaire avant l'installation.

---

## Flux Combiné

```bash
# 1. Travaillez normalement — le cache vous évite des appels API en double
gitpr -r
gitpr -r  # Même diff → cache hit instantané ⚡

# 2. Modifiez du code → nouveau hash → nouvel appel IA
# ... éditer des fichiers ...
gitpr -r  # Diff différent → nouvelle analyse

# 3. Restez à jour sans effort
gitpr -u  # Vérifier et installer la dernière version
```

---

## Stockage du Cache

Tous les fichiers de cache se trouvent dans `~/.gitpr/cache/prompts/`. Vous pouvez supprimer ce répertoire en toute sécurité pour libérer de l'espace disque — GitPR le recréera selon les besoins.

```bash
# Vider toutes les réponses en cache
rm -rf ~/.gitpr/cache/prompts/
```

---

[← Internationalisation](/i18n) &nbsp;|&nbsp; [Contribution →](/contribuicao)
