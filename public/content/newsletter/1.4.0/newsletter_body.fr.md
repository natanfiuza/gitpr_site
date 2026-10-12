# GitPR 1.4.0 — Nouveautés

## Nouveautés de cette version

- **La release 1.3.0 a été close et la 1.4.0 est déjà taguée :** le tag `v1.3.0` (merge de la PR #193, 2026-09-24) a clos l'élément qui bloquait deux fenêtres, et `CHANGELOG.md` a été complété avec les six fonctionnalités qui manquaient. Dix jours plus tard, le tag `v1.4.0` (merge de la PR #207, 2026-10-12) a clos toute cette fenêtre — les cinq fonctionnalités ci-dessous **plus** la note de release 1.3.0 tiennent dans un seul cycle.
- **`gitpr release` a cessé de répéter l'historique :** les release notes listaient, pour chaque version, tous les commits de toutes les versions précédentes (la section 1.3.0 est partie avec 124 puces, dont 115 répétaient la 1.2.0). La cause n'était pas l'IA — c'était la plage de `git log`, ancrée sur `git describe`, qui dans ce dépôt résolvait vers `v0.0.8`. La coupure est devenue la **section précédente du changelog lui-même**, chaque puce a gagné un lien **réel et cliquable** vers le commit, la PR et le contributeur, et un commit déjà présent dans une autre section est écarté avant de devenir une puce — quand il ne reste rien, la release **avorte** au lieu de publier une section vide.
- **La télémétrie a quitté le fichier JSON et est devenue un ledger SQLite :** les événements étaient écrits dans des fichiers par commande, depuis un thread daemon, puis « enrichis » en retour à travers le cache d'IA via une correspondance heuristique sur (repo, branche, action, minute) qui dépendait de trois cartes de dossiers divergentes. Le résultat était prévisible : `tokens_actual` **toujours à zéro** et une perte de données entre dépôts à l'export. `~/.gitpr/metrics/telemetry.db` écrit la ligne **de façon synchrone**, avec les tokens copiés depuis les métadonnées du fournisseur au moment de l'écriture, et un processus de courte durée (un hook, par exemple) ne perd plus sa propre ligne à la sortie.
- **Mentor Mode — le review qui enseigne au lieu de seulement pointer :** un review dit *ce* qui ne va pas et presque jamais *pourquoi cela compte*. `gitpr mentor` (et le flag `--mentor` sur `-r`/`-f`) enrichit les constats du dernier review avec ce qui se passe, pourquoi cela compte, une analogie du quotidien et les concepts à étudier. C'est **opt-in** et cela n'invente jamais : avec des preuves insuffisantes, l'explication le dit au lieu de remplir le vide.
- **Score de risque déterministe — sans IA, sans quota :** `gitpr risk` produit un score de 0 à 100 avec un niveau `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` à partir de signaux locaux (chemins critiques, tests manquants, migrations, historique de bugs et de reverts, constats statiques). La formule est déclarée (**50/30/20**), tout comme les règles d'escalade obligatoires, et le score entre dans le prompt de review comme contexte **en lecture seule** et dans le rapport. Un nombre explicable vaut mieux qu'une supposition coûteuse.
- **Policy Packs — la politique du dépôt est devenue un artefact versionné :** les skills, les règles de linter, les sévérités, les chemins critiques, les poids de risque et les conventions de PR/commit étaient dispersés et chaque dépôt les configurait à la main. `gitpr policy` adopte un pack YAML versionné avec une **somme de contrôle**, résout la précédence entre les couches et consigne le choix dans le lockfile que l'équipe commite. Quatre packs intégrés voyagent dans le wheel ; sans pack actif, **rien ne change**.
- **Baseline et suppressions auditables — le bruit connu cesse de bloquer :** les linters et les reviews re-signalaient les mêmes constats préexistants à chaque exécution, et c'était au reviewer de démêler la sortie dans sa tête. `.gitpr/baseline.json` enregistre l'instantané des constats acceptés, chaque nouveau constat est classé `new`/`existing`/`resolved`/`ignored`/`accepted_debt`, et **seul `new` bloque**. Chaque suppression porte une raison ; la dette acceptée porte un propriétaire et, quand il y en a un, une échéance.
- **Le modèle de constat est devenu un type canonique :** `NormalizedFinding` a déménagé de `infrastructure/linter/external/base_bridge.py` vers `src/domain/finding/`. Sans cela, chaque pont — Semgrep, Gitleaks, Bandit, le ruleset de regex — calculerait l'empreinte d'une forme différente du même constat et la baseline n'aurait aucun moyen de le reconnaître.
- **La dette de la fenêtre précédente a été close :** `SKILL_LABELS` et `mcp_server.SKILL_FILES` ont reçu `tests`, `explain` et désormais `mentor` ; les **40 clés `__()`** qui n'existaient dans aucun dictionnaire ont été traduites ; et les **4 échecs** de la fenêtre précédente n'existent plus. Les trois assertions qui signalaient la dette tournent toujours — elles ont simplement cessé de signaler. La suite, mesurée de bout en bout dans cette fenêtre, se referme à **2795 réussis sur 2799** — les deux échecs restants sont un bug d'isolation dans `tests/test_mentor_cli.py`, pas une régression du produit.
- **La couverture i18n a bouclé la boucle :** **1603 clés** dans chacun des 6 dictionnaires (+445 dans cette fenêtre) et **zéro clé utilisée dans le code sans traduction** — le code en utilise 1603, les dictionnaires en ont 1603.
- **Les traductions en attente ont été soldées :** `split-command` et `otimizacao-de-tokens` ont complété les 5 langues ; `review-pr` en a encore 2. Deux familles uniquement PT (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) ont déménagé du haut de `docs/` vers `docs/extra/`.

## Comment l'utiliser

Mettez à jour depuis PyPI :

```
pip install --upgrade gitpr-cli
```

Quatre nouvelles commandes — celles en lecture seule n'écrivent rien tant que vous ne passez pas l'option qui écrit :

```
gitpr risk                                 # score de 0 à 100 pour le diff courant (sans IA, sans quota)
gitpr risk --file src/auth.py              # les facteurs derrière le score d'un fichier
gitpr risk --format json                   # sortie structurée pour la CI
gitpr mentor                               # une explication didactique des constats du dernier code review
gitpr -r --mentor                          # ajoute la section mentor au code review
gitpr policy list                          # les packs de cette machine et celui en vigueur
gitpr policy init --stack laravel          # détecte la stack et active le pack correspondant
gitpr policy use acme/team-policy@1.0.0    # épingle un pack, en écrivant .gitpr/policy.lock.yml
gitpr baseline create                      # enregistre le diff courant comme baseline acceptée
gitpr baseline show                        # les constats, les décisions et les comptages
gitpr baseline suppress <id> --reason "..." # une décision sur un constat
```

Rien ne change tant que vous ne l'activez pas. Sans `.gitpr/baseline.json` dans le dépôt, chaque exécution se comporte exactement comme avant — la barrière n'existe qu'une fois le fichier créé et, à partir de là, **seuls les constats `new` bloquent** : `existing`, `resolved`, `ignored` et `accepted_debt` non. Chaque suppression porte un motif ; la dette acceptée a un responsable et, éventuellement, une échéance. Idem pour la policy : sans pack actif, votre configuration reste intacte.

**Rupture de CLI annoncée :** `--metrics`, `--export`, `--purge`, `--hook-event` et `--dashboard` ont disparu de la racine — ils sont devenus le groupe `gitpr metrics` (`gitpr metrics dashboard`, `gitpr metrics export`, `gitpr metrics prune`). La télémétrie quitte les fichiers JSON par commande pour un ledger SQLite dans `~/.gitpr/metrics/telemetry.db` ; `gitpr metrics migrate` importe les anciens fichiers et les déplace vers `~/.gitpr/metrics_legacy/`.

`gitpr risk` n'appelle jamais l'IA — c'est une formule déclarée (50/30/20) sur des signaux locaux, donc le même dépôt donne toujours le même nombre. `gitpr mentor` est opt-in et, quand les preuves manquent, le dit au lieu d'inventer une explication.

## Astuces utiles

GitPR ignore volontairement les fichiers non suivis (il lit `git diff HEAD`), et c'est une fonctionnalité : un `.env.local` oublié avec des mots de passe de production n'est jamais envoyé à l'IA, et les énormes dossiers de cache ne gaspillent jamais de tokens. Pour inclure un nouveau fichier, faites simplement `git add` avant.
