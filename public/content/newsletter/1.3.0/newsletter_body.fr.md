# GitPR 1.3.0 — Nouveautés

## Nouveautés de cette version

- **`gitpr demo` — la première exécution a cessé d'être un acte de foi :** Une visite guidée qui montre un message de commit, un review et une description de PR **sans clé API, sans dépôt git et sans réseau**. La question à laquelle elle répond (« que fait cet outil ? ») n'a qu'une seule fenêtre pour être posée — la première utilisation, avant que l'utilisateur n'ait configuré un fournisseur. La visite passe par le **vrai pipeline de génération** avec la source de la réponse échangée, donc ce qui apparaît est ce que l'outil produit réellement, tandis que chaque effet externe (cache, métriques, disque, réseau, lecture des clés) est neutralisé.
- **Badge GitPR dans le corps du PR + commande `gitpr badge` :** Un PR écrit par l'IA était indistinguable d'un PR écrit à la main, et le résultat du linter mourait dans un terminal qui avait défilé hors de l'écran. Le badge transforme ce signal privé en une déclaration visible dans le PR publié lui-même — et il est délibérément une URL statique shields.io, que GitPR ne récupère **jamais**, pour que publier un PR n'en vienne pas à dépendre de la disponibilité d'un tiers. Mesure honnête : sans règles de linter configurées, le badge **n'est pas émis**, parce qu'une liste vide veut dire « rien n'a été vérifié », et non « rien n'a été trouvé ».
- **`gitpr split` — un arbre de travail à plusieurs intentions cesse de devenir un blob de commit :** Il lit le diff non commité, demande à l'IA de partitionner les hunks par intention logique et propose **un commit atomique par préoccupation**, chacun avec un message généré à partir du patch de ce groupe isolé. L'arbre n'est jamais réécrit : les fichiers terminent identiques au byte près à leur état initial — seul l'historique change. *(L'index, oui : `--apply` réinitialise sur HEAD avant le staging.)*
- **Analyse de secrets intégrée — la règle qui ne peut pas être remplacée :** Sept règles (`src/security_ruleset.py`) qui s'exécutent à **chaque** invocation du linter : cinq `error` bloquantes (ID de clé AWS, jeton GitHub/Slack, clé Google, bloc de clé privée) et deux `warning`. Le catalogue local était entièrement géré par l'utilisateur — il pouvait être écrasé par le téléchargement, réécrit par le wizard (en perdant les commentaires) ou étendu seulement par des plugins locaux à la machine. Une barrière de secrets doit se comporter de la même façon sur chaque machine, donc les règles vivent désormais **à l'intérieur du paquet**. **Changement de comportement : un commit qui passait peut désormais être bloqué.**
- **Prise en charge de `extensions: ["*"]` :** Cela signifie désormais *tous* les fichiers, y compris ceux sans suffixe — ce qui est précisément d'où les secrets fuient (`id_rsa`, `.env`, `credentials`, `Dockerfile`). Une règle avec `extensions: ["py"]` conserve le filtre exactement comme avant.
- **Ponts SAST opt-in — Semgrep, Gitleaks et Bandit :** Une couche qui branche des scanners de sécurité tiers sur le linter existant. Ils s'exécutent **uniquement** lorsqu'ils sont activés, **uniquement** sur les fichiers touchés par le diff, et les constats sont dédupliqués par rapport au ruleset interne : un secret vu par les deux apparaît **une seule fois** avec un marqueur de confirmation multi-source (`[Gitleaks + Regex]`). Gitleaks a ses valeurs masquées (`AKIA****`) avant de devenir un constat.
- **`gitpr tests generate` — la suite qui respecte la convention du dépôt :** Génère des fichiers de test complets à partir du diff, d'un fichier spécifique ou d'un constat de review. Il détecte le framework utilisé (Pest, PHPUnit, Jest, Vitest, Pytest) au lieu d'imposer un style, calcule le chemin de destination conventionnel (la séparation `Feature`/`Unit` de Laravel, le `tests/**/test_*.py` de Pytest) et valide la syntaxe avec la chaîne d'outils locale (`php -l`, `node --check`, `python -m py_compile`) — un échec de validation devient un avertissement, pas une erreur. Le dry run est le défaut.
- **`gitpr explain` + le flag `--explain` — le guide de celui qui va réviser :** Un guide centré sur le reviewer (ce qui change, pourquoi cela change, où se concentrer, quel est le risque de régression) pour que personne n'ait à reconstruire l'intention à partir d'un diff brut. Disponible comme sous-commande propre et comme flag qui ajoute la section à la description de PR générée.
- **Architecture en couches — `src/domain/` et `src/application/` :** Les deux dernières fonctionnalités (`tests` et `explain`) sont nées avec une séparation explicite entre les règles de domaine pures et l'orchestration des cas d'usage, et la CLI et la TUI de chat partagent désormais **le même** cas d'usage au lieu de le dupliquer.
- **Suite déterministe et première CI :** `tests/conftest.py` est devenu hermétique (il fixe `GITPR_LANG=en_us` et désactive le ruleset de secrets) et `.github/workflows/tests.yml` exécute la suite sur **Python 3.10** (le plancher déclaré, jamais exercé) et 3.13. C'est la CI qui a rendu visible la dérive de locale — **22 tests** échouaient sur une machine pt-BR parce qu'ils affirmaient le littéral anglais.
- **Les 3 échecs hérités de trois rapports d'affilée ont été clos :** les deux tests de timeout obsolètes (`600s` contre le défaut réel de `180s`) et le test sensible à la locale. La ligne de base de la suite a cessé d'être « 3 échecs connus » et est devenue **verte par construction**.
- **Dette nouvelle et concentrée :** les deux fonctionnalités les plus récentes (`tests` et `explain`) sont arrivées avec le registre de skills **à moitié fait** — **40 clés `__()`** utilisées dans le code n'existent dans aucun des 6 dictionnaires, et les registres de libellés de la config et du MCP n'ont pas reçu les nouveaux types. Cela fait **4 échecs** dans la suite complète, tous avec la même cause racine.
- **État de la release 1.3.0 :** `__version__` et `__lang_version__` sont dans le **working tree et non commités** (HEAD est encore à 1.2.0 / v0.0.31), `CHANGELOG.md` **a** l'entrée `[1.3.0] - 2026-09-21` — mais elle ne couvre **que** l'analyse de secrets, et non `demo`, `badge`, `split`, SAST, `tests` ou `explain`. Le tag `v1.2.0` **a été créé** (merge du PR #174), clôturant l'élément qui bloquait la fenêtre précédente.

## Comment l'utiliser

Mettez à jour depuis PyPI :

```
pip install --upgrade gitpr-cli
```

Essayez avant de configurer quoi que ce soit — la visite n'a besoin ni de clé API, ni de dépôt git, ni de réseau :

```
gitpr demo                             # visite guidée d'un message de commit, d'un review et d'un PR
gitpr demo --lang=fr_fr                # la visite dans votre langue
gitpr demo --no-tui                    # texte brut, pour la CI et les enregistrements
```

Les nouvelles sous-commandes — toutes sont en lecture seule jusqu'à ce que vous passiez l'option qui écrit :

```
gitpr badge                            # imprime l'extrait du badge pour votre README (n'écrit rien)
gitpr split                            # le plan : un commit atomique par préoccupation (n'écrit rien)
gitpr split --apply                    # commite chaque groupe ; l'arbre termine identique au byte près
gitpr tests generate                   # un fichier de test selon la convention de votre dépôt (n'écrit rien)
gitpr tests generate --file src/core.py --apply
gitpr explain                          # un guide pour celui qui va réviser le diff courant
gitpr --explain                        # ajoute ce guide à la description de PR générée
```

Un `gitpr split` sans option demande avant de commiter quoi que ce soit — et `--apply` réinitialise l'index sur HEAD, donc ce que vous aviez déjà mis en staging doit être refait (le contenu des fichiers n'est jamais touché). `gitpr tests generate` n'écrase rien sans `--apply` et, quand il le fait, la confirmation s'ouvre avec **Non** présélectionné.

L'analyse de secrets s'exécute désormais à **chaque** invocation du linter, avec des règles qui vivent à l'intérieur du paquet et ne peuvent être remplacées par `--skill` ni réécrites par le wizard — un commit qui passait peut donc être bloqué. `GITPR_LINTER_SECURITY=false` est la porte de sortie. Les ponts Semgrep/Gitleaks/Bandit restent strictement opt-in (`GITPR_SAST_*_ENABLED`), et un outil activé mais absent du `PATH` produit un avertissement, pas un échec.

## Astuces utiles

Le linter est gratuit : pas de clé API, pas d'appel IA, uniquement les lignes ajoutées de votre diff. Code de sortie 0 = succès, 1 = violations — idéal comme quality gate GitHub Actions qui bloque secrets et code de debug avant la revue humaine ; la documentation fournit le workflow complet.
