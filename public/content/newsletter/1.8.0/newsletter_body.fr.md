# GitPR 1.8.0 — Nouveautés

## Nouveautés de cette version

- **Map-Reduce n'a plus de plafond unique :** `get_map_reduce_thresholds(provider)` résout le déclencheur et la taille de lot par fournisseur (`_GEMINI`, `_DEEPSEEK`, `_OLLAMA` → générique → 90 000/50 000), et le découpage est devenu une **cascade** : fichier → hunks (`@@`) → lignes, en préservant toujours l'en-tête et le marqueur `[file - part N]`. Une pré-réduction intermédiaire consolide les résumés partiels qui dépassent encore le déclencheur ([ADR-0022](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0022-map-reduce-per-provider-token-partitioning.md)).
- **La détection de forge a été corrigée et le repli silencieux est devenu un avertissement :** `github.com` est désormais reconnu **avant** le retour de `default=None` — sans cela, chaque dépôt GitHub était traité comme un hôte inconnu et recevait un `scm.provider` erroné. Lorsque le remote existe mais ne nomme pas de forge connue, `resolve_scm_settings` avertit désormais et oriente vers `gitpr --init`.
- **Les notes de migration ont gagné des contrats :** [ADR-0020](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0020-per-repo-scm-provisioning-migration.md) formalise le provisionnement SCM par dépôt (les scripts et images ne doivent pas écrire les clés legacy), et [ADR-0021](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0021-strict-boolean-update-check-migration.md) verrouille la sémantique stricte de `GITPR_SKIP_UPDATE_CHECK`.
- **L'extension d'éditeur a gagné un parcours de publication reproductible :** le guide des marketplaces a été traduit dans les **cinq langues**, et `scripts/marketplace-setup-wizard.sh` guide les cinq étapes d'identifiants (Azure DevOps PAT, namespace Open VSX, secrets GitHub Actions), en validant chaque token contre l'API en direct avant de l'enregistrer.
- **La documentation des métriques et de Map-Reduce a été synchronisée :** l'onglet **Charts & Trends**, Token Trend et Latency Histogram sont entrés dans les cinq langues, avec un garde de parité de traduction qui empêche qu'une modification uniquement en anglais ne passe inaperçue.
- **La suite de tests est devenue stable sur Windows :** le flux headless de `CommitApp` est ignoré sur `win32` (`pilot.pause` de Textual bloque sans vrai TTY) et le routage CLI simule `check_internet_connection`, éliminant la dépendance réseau.
- **1.8.0 a été publiée :** `__version__` = **1.8.0**, `CHANGELOG.md` contient `## [1.8.0] - 2026-11-14`, le tag `v1.8.0` et PyPI servent 1.8.0.

## Comment l'utiliser

Mettez à jour vers la version 1.8.0 via pip :

```bash
pip install --upgrade gitpr-cli
```

Ajustez les seuils Map-Reduce par fournisseur d'IA dans `~/.gitpr/.env` (ou de manière interactive via `gitpr config` dans la catégorie `ai`) :

```bash
# Exemple : déclencheur et taille de lot personnalisés pour Ollama vs Gemini
GITPR_MAP_REDUCE_MAX_TOKENS_OLLAMA=16000
GITPR_MAP_REDUCE_MAX_PART_TOKENS_OLLAMA=8000
GITPR_MAP_REDUCE_MAX_TOKENS_GEMINI=120000
GITPR_MAP_REDUCE_MAX_PART_TOKENS_GEMINI=60000
```

Si `resolve_scm_settings` signale que l'hôte du remote `origin` n'est pas reconnu, associez explicitement le fournisseur SCM et les identifiants du dépôt :

```bash
gitpr --init
```

## Astuces utiles

- `gitpr config` ouvre un écran maître-détail sur `~/.gitpr/.env`, pour que plus personne n'ait à se rappeler si la valeur est `true`, `1` ou `yes` : chaque champ reçoit le contrôle que son type mérite et une valeur invalide devient difficile à saisir. F2 valide et enregistre, Ctrl+R rétablit la valeur par défaut (supprime la ligne), `/` recherche libellés et variables dans toutes les catégories, et Échap quitte en demandant avant d'abandonner. Il modifie le même fichier que celui que vous aviez déjà — pas une seconde source de configuration.
