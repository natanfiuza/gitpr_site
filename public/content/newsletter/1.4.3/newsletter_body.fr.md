# GitPR 1.4.3 — Nouveautés

## Nouveautés de cette version

- **La barrière de mise à jour a cessé de perdre les releases publiées après la première exécution du jour :** le cache PyPI était indexé par la **date locale**, donc une release publiée à 10h45 restait invisible jusqu'à minuit pour qui avait déjà lancé la CLI à 07h54. Le cache est devenu une **fenêtre glissante de 1 heure** avec un `checked_at` ISO, des écritures atomiques (`os.replace`) et une validation à la lecture — un fichier corrompu, un horodatage dans le futur ou un reste de l'ancien format expire simplement. `gitpr -u`/`--update` **ignore désormais le cache** et interroge PyPI à chaque invocation, comme la documentation le promettait déjà.
- **La comparaison de versions est passée à PEP 440 :** `packaging.version.Version` a remplacé l'arithmétique de tuples — elle corrige l'ordre des segments à plusieurs chiffres (`1.4.10 > 1.4.9`), empêche une pré-release distante de bloquer une installation stable dans un `pip install --upgrade` qui refuserait de l'installer, et traite une version illisible d'un côté ou de l'autre comme **inconnue** plutôt que comme « plus récente ».
- **La barrière atteint désormais les sous-commandes — mais seulement quand l'exécution peut demander :** `gitpr release`, `fix`, `review-pr` et les autres passent par la barrière quand il y a un TTY des deux côtés. CI, pipes, hooks, `--quiet`/`--hook`/`--mcp` et `-h`/`--help` ne sont **jamais** bloqués ; l'aide de la sous-commande est détectée dans l'**`argv` brut**, parce que Click vide les arguments du contexte avant le callback du groupe.
- **Les 7 échecs de `tests/badge/test_badge_paths.py` ont enfin été clos :** la validation du jeton du Provider est passée de l'extérieur à **l'intérieur** du bloc `if check_remote_state:` de `publish_pull_request_use_case` — le flux `--no-edit` ne retourne plus un `auth_required` en faux positif. `pr_url`/`pr_number` sont désormais lus avec un `getattr` défensif, et la fixture a gagné `token="ghp_fake"` et `number=1`. **La suite est passée de 12 à 5 échecs connus** — dette ouverte pendant trois fenêtres consécutives.
- **La sémantique de l'échappatoire a changé (rupture déclarée) :** `GITPR_SKIP_UPDATE_CHECK` est désormais lue comme un **booléen strict** — seuls `1`, `true`, `yes`, `on` et `y` (insensibles à la casse) désactivent la vérification. `false`, `0`, `no`, `off`, `n` et une valeur vide **gardent** la barrière active.
- **La dette i18n n'a pas été payée :** les dictionnaires restent à **1620 clés** (×4) et **1618** (`es.json`, `fr.json`), avec **8 clés utilisées dans le code sans traduction** et **2 orphelines** — exactement les mêmes chiffres que la fenêtre précédente.

## Comment l'utiliser

Mettez à jour depuis PyPI :

```
pip install --upgrade gitpr-cli
```

La barrière de mise à jour vous suit désormais dans les sous-commandes : dans un terminal interactif, `gitpr release`, `gitpr fix` et `gitpr review-pr` vous avertissent aussi qu'une release plus récente existe. CI, pipes, hooks, le serveur MCP, `--quiet` et `-h`/`--help` ne sont jamais bloqués — et le flag d'aide est détecté dans l'`argv` brut, donc `gitpr release -h` se contente toujours d'afficher l'aide.

`gitpr -u`/`--update` interroge maintenant PyPI à chaque exécution au lieu de faire confiance au cache. Si vous désactivez la barrière avec `GITPR_SKIP_UPDATE_CHECK`, notez que la valeur est désormais un booléen strict : seuls `1`, `true`, `yes`, `on` ou `y` désactivent la vérification ; `false`, `0`, `no`, `off`, `n` et une valeur vide la laissent active.

## Astuces utiles

`gitpr check` est un point d'entrée CI unique : il exécute le linter statique, les scanners de secrets, le risk scoring, la revue IA optionnelle et la classification par baseline, puis émet du SARIF 2.1.0, JSON ou JUnit XML pour GitHub Code Scanning, GitLab SAST et Jenkins. `--no-ia` le rend gratuit et hors ligne, `--ia-only-high-risk` ne dépense des tokens IA que sur les fichiers à risque, et les codes de sortie sont déterministes : 0 succès, 1 constats au niveau de `--fail-on` ou au-dessus, 2 erreur de configuration ou d'exécution.
