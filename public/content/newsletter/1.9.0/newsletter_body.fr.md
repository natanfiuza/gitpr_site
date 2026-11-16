# GitPR 1.9.0 — Nouveautés

## Nouveautés de cette version

- **GitHub Enterprise n'est plus rejeté par `parse_repo_ref` :** `GitHubProvider.parse_repo_ref` a abandonné le regex rigide `github.com` au profit d'un `_REMOTE_RE` générique (`user@` optionnel, n'importe quel `host[:port]`, séparateur `:` ou `/` et deux segments de chemin). Les remotes d'entreprise (`github.acme.com` en HTTP/SSH/port personnalisé) sont désormais pris en charge avec `GITHUB_BASE_URL`.
- **L'extension d'éditeur a gagné la traduction `pt-pt` :** `extensions/vscode/package.nls.pt-pt.json` (14 clés) et `l10n/bundle.l10n.pt-pt.json` (26 clés) rejoignent `en`, `pt-br`, `es` et `fr`, portant la couverture à **5 langues**.
- **Parité de traduction à 100 % à la racine `docs/` :** la liste `_EN_ONLY` dans `tests/unit/test_docs_translation_parity.py` a été vidée, `docs/github-issue-prompt-com-gh.md` a été déplacé vers `docs/extra/` et la racine de `docs/` atteint **51/51 sujets dans les cinq langues**.
- **Tests de contrat pour l'ADR-0019 dans la documentation :** `tests/unit/test_project_config_seed_docs.py` valide la section `9.4`, l'avertissement `[!IMPORTANT]` et `GITPR_SKIP_PROJECT_CONFIG_SEED` dans les cinq variantes de `pull-request-publication` et `config-tui` ([ADR-0019](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0019-project-config-seed-opt-out-and-documentation-contract.md)).
- **Repli pour environnement en lecture seule / éphémère dans SCM documenté dans les 5 langues :** `persisted=False`, le repli sur `os.environ` et la gestion de `PermissionError` sont documentés dans `docs/scm-multiforge.*` et dans [ADR-0020](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0020-per-repo-scm-provisioning-migration.md).
- **Compromis coût et latence de Map-Reduce documentés dans les 5 langues :** `GITPR_MAP_REDUCE_MAX_TOKENS` / `MAX_PART_TOKENS` et les champs de télémétrie (`map_reduce`, `chunks_count`, `duration_ms`) sont désormais dans `docs/metricas-telemetria.*` et dans [ADR-0022](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0022-map-reduce-per-provider-token-partitioning.md).
- **`gitpr-mcp --install <editor>` liste désormais `claude-code` :** le message d'éditeur inconnu dans `_install_for_editor` liste `vscode, cursor, claude-code, claude, zed, auto`, avec les 6 dictionnaires synchronisés (**v0.0.44** via OTA).
- **1.9.0 publiée :** `__version__` = **1.9.0**, `CHANGELOG.md` avec `## [1.9.0] - 2026-11-15`, tag `v1.9.0` et PyPI servant **1.9.0**.

## Comment utiliser

Mettez à jour vers la version 1.9.0 via pip :

```bash
pip install --upgrade gitpr-cli
```

Pointez GitPR vers une instance GitHub Enterprise dans `~/.gitpr/.env` (les remotes sur des hôtes personnalisés sont désormais analysés automatiquement) :

```bash
GITHUB_BASE_URL=https://github.acme.com/api/v3
```

Installez le serveur MCP pour Claude Code (ou tout autre éditeur pris en charge) et, si nécessaire dans vos pipelines CI/CD, désactivez le seed de configuration de projet :

```bash
gitpr-mcp --install claude-code
GITPR_SKIP_PROJECT_CONFIG_SEED=true gitpr --pr
```

## Astuces utiles

- Flag de debug cachée : lancez n'importe quelle commande IA avec `--pre-save` (ex. `gitpr -c --pre-save`) et GitPR écrit le payload complet — instruction système, prompt et compteurs de caractères — dans un fichier JSON du dossier courant. Aucun fichier n'est généré quand la réponse vient du cache.
