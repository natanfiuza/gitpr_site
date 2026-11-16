# GitPR 1.9.0 — Novedades

## Novedades de esta versión

- **GitHub Enterprise dejó de ser rechazado por `parse_repo_ref`:** `GitHubProvider.parse_repo_ref` abandonó el regex rígido `github.com` y pasó a usar un `_REMOTE_RE` genérico (`user@` opcional, cualquier `host[:port]`, separador `:` o `/` y dos segmentos de ruta). Los remotes corporativos (`github.acme.com` por HTTP/SSH/puerto personalizado) ahora se parsean correctamente junto con `GITHUB_BASE_URL`.
- **La extensión de editor sumó traducción `pt-pt`:** `extensions/vscode/package.nls.pt-pt.json` (14 claves) y `l10n/bundle.l10n.pt-pt.json` (26 claves) se unieron a `en`, `pt-br`, `es` y `fr`, completando **5 idiomas**.
- **Paridad de traducción al 100% en la raíz `docs/`:** la lista `_EN_ONLY` en `tests/unit/test_docs_translation_parity.py` fue vaciada, `docs/github-issue-prompt-com-gh.md` se movió a `docs/extra/` y la raíz de `docs/` alcanzó **51/51 temas en los cinco idiomas**.
- **Pruebas de contrato para ADR-0019 en la documentación:** `tests/unit/test_project_config_seed_docs.py` valida la sección `9.4`, el aviso `[!IMPORTANT]` y `GITPR_SKIP_PROJECT_CONFIG_SEED` en las cinco variantes de `pull-request-publication` y `config-tui` ([ADR-0019](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0019-project-config-seed-opt-out-and-documentation-contract.md)).
- **Fallback para entorno de solo lectura / efímero en SCM documentado en los 5 idiomas:** `persisted=False`, el fallback en `os.environ` y `PermissionError` están documentados en `docs/scm-multiforge.*` y en [ADR-0020](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0020-per-repo-scm-provisioning-migration.md).
- **Tradeoffs de costo y latencia de Map-Reduce documentados en los 5 idiomas:** `GITPR_MAP_REDUCE_MAX_TOKENS` / `MAX_PART_TOKENS` y los campos de telemetría (`map_reduce`, `chunks_count`, `duration_ms`) entraron en `docs/metricas-telemetria.*` y en [ADR-0022](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0022-map-reduce-per-provider-token-partitioning.md).
- **`gitpr-mcp --install <editor>` ahora lista `claude-code`:** el mensaje de editor desconocido en `_install_for_editor` lista `vscode, cursor, claude-code, claude, zed, auto`, con los 6 diccionarios sincronizados (**v0.0.44** vía OTA).
- **1.9.0 publicada:** `__version__` = **1.9.0**, `CHANGELOG.md` con `## [1.9.0] - 2026-11-15`, tag `v1.9.0` y PyPI sirviendo **1.9.0**.

## Cómo usar

Actualiza a la versión 1.9.0 mediante pip:

```bash
pip install --upgrade gitpr-cli
```

Apunta GitPR a una instancia de GitHub Enterprise en `~/.gitpr/.env` (los remotes con hosts personalizados ahora se analizan automáticamente):

```bash
GITHUB_BASE_URL=https://github.acme.com/api/v3
```

Instala el servidor MCP para Claude Code (u otro editor compatible) y, si lo necesitas en pipelines de CI/CD, desactiva el seed de configuración de proyecto:

```bash
gitpr-mcp --install claude-code
GITPR_SKIP_PROJECT_CONFIG_SEED=true gitpr --pr
```

## Consejos útiles

- Flag de debug oculta: ejecuta cualquier comando de IA con `--pre-save` (p. ej. `gitpr -c --pre-save`) y GitPR guarda el payload completo — instrucción de sistema, prompt y contadores de caracteres — en un JSON de la carpeta actual. No se genera ningún archivo cuando la respuesta viene de la caché.
