# GitPR 1.5.0 — Nouveautés

## Nouveautés de cette version

- **Le cycle issue → PR → merge se referme désormais tout seul :** toute issue créée avec `gitpr -is` est désormais enregistrée dans `.gitpr/conf/github.yml` (un registre local qui s'auto-ignore dans le `.gitignore`), et le prochain `gitpr` vérifie qu'elle est toujours ouverte avant de proposer la ligne `close #N` dans le corps de la Pull Request. `--close-issue` accepte sans demander, `--no-close-issue` saute la vérification, et `GITPR_ISSUE_AUTOCLOSE=false` désactive la fonctionnalité. Le nouveau paquet `src/issue_autoclose/` compte **5 fichiers** et une série de décisions consignées dans les ADR 001–007 de `docs/specs/issue-autoclose/`.
- **La base du PR ne vit plus seulement sur la machine de celui qui l'exécute :** `.gitpr/conf/gitpr.config.yml` est versionné avec le projet et porte la branche de destination ; `src/project_config.py` résout `--base` → fichier du projet → `PR_DEFAULT_BASE` → `origin/HEAD` → `main`, et la cible doit exister dans `origin` et différer de la branche courante. Une cible égale à la branche courante est refusée et, en exécution interactive, réparée par une modale dédiée (`src/ui/pr_base_app.py`) qui écrit la nouvelle valeur dans le fichier du projet ([ADR-015](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-015-project-local-pr-base.md)).
- **La dette i18n a été payée :** les six dictionnaires comptent désormais **1698 clés** chacun, `__lang_version__` est monté à **v0.0.38**, et `tests/test_i18n.py` enchaîne **22 verts** — aucune clé manquante, aucune orpheline, aucune identité au-dessus de l'allowlist. L'extracteur de `sync_i18n.py` a remplacé la regex par de l'**AST**, ce qui a éliminé les 21 faux négatifs de concaténation implicite.
- **Le ruleset de secrets a comblé les trois lacunes encore ouvertes :** les clés STS `ASIA…`, les PAT `github_pat_…`, les cookies `xoxc-`/`xoxd-`, les affectations **sans guillemets** (`API_KEY=abc123`), la frontière à gauche sur le nom de la clé et moins de faux positifs (`bypassword`, `os.getenv(...)`). Ce sont **7 règles** aux regex plus larges, couvertes par **77 scénarios**.
- **Des contrats qui n'existaient que dans les têtes sont devenus des tests :** la version du paquet a un **SSOT unique** dans `src/updater.py` (ADR-014) et un test qui échoue si `pyproject.toml`, `CLAUDE.md` ou `GEMINI.md` divergent ; le contrat de code de sortie de la baseline a gagné 8 scénarios d'intégration ; la relecture des reviewers hérités (`None`) n'imprime plus `attached None` ; et le `-h` avant la sous-commande rend à nouveau l'aide au lieu d'ouvrir la TUI.
- **La 1.5.0 est taguée, mais pas encore publiée :** le tag `v1.5.0` pointe vers le merge du PR #260 (2026-11-04), mais PyPI sert encore la **1.4.4** — le workflow de release existe (`.github/workflows/release.yml`), mais la publication doit être confirmée.

## Comment l'utiliser

La 1.5.0 est taguée `v1.5.0` ; au bouclage de cette édition, PyPI sert encore la 1.4.4. Dès que la release sera publiée, mettez à jour avec :

```
pip install --upgrade gitpr-cli
```

Pour que le cycle de l'issue se referme tout seul, créez l'issue avec `gitpr -is` et appuyez sur F3 : le numéro est enregistré dans `.gitpr/conf/github.yml` (état local, ajouté au `.gitignore`). Au prochain `gitpr`, le corps de la Pull Request reçoit la ligne `close #N` après que GitPR a vérifié que l'issue est toujours ouverte. `--close-issue` l'accepte sans demander, `--no-close-issue` saute la vérification pour cette exécution, et `GITPR_ISSUE_AUTOCLOSE=false` désactive la fonctionnalité sur la machine.

Pour figer la base de la PR par dépôt, versionnez ce fichier avec le projet :

```yaml
pull_request:
  base_branch: develop
```

GitPR crée `.gitpr/conf/gitpr.config.yml` à la première exécution et ne l'écrase jamais ; `--base` a la priorité pour une exécution isolée. La branche choisie doit exister dans `origin` et ne peut pas être celle depuis laquelle la PR est ouverte.

## Astuces utiles

Relancez `gitpr` sur la même branche et vous n'aurez pas de PR en double : GitPR détecte la PR existante et propose « Push to existing PR », qui ne met à jour que le corps (le titre reste intact). Bonus : un `git push` échouant avec « no upstream » est automatiquement relancé avec `--set-upstream`.
