# GitPR 1.0.0 — Novedades

## Novedades de esta versión

- **SCM Multi-Forge (`gitpr --init` + capa `ScmProvider`):** Una única abstracción sobre GitHub, GitLab, Bitbucket y Azure DevOps — `--init` detecta el forge desde el remote, valida el token (3 intentos, re-prompt en 401) y lo guarda con cifrado Fernet **solo en caso de éxito**. El token legacy de GitHub sigue funcionando, sin migración.
- **Subcomando `gitpr release` (Changelog / Release Notes):** Genera el changelog de la rama entre `--since` (predeterminado: último tag) y `HEAD`, clasifica los commits por Conventional Commits, sugiere el bump semántico, añade un resumen ejecutivo de IA y lo *antepone* a `CHANGELOG.md`. Con `--publish`/`--draft` publica el release en el forge (GitHub crea el tag; GitLab exige el tag) y `--format markdown|json` entrega salida estructurada.
- **Revisores Sugeridos en el flujo de PR:** GitPR ahora consulta al propio forge sugerencias de revisores al publicar un PR. Desactívalo con `--no-suggest-reviewers` y ajústalo con `GITPR_REVIEWER_SUGGESTION_TOP_N` y `GITPR_REVIEWER_SUGGESTION_EXCLUDED`.
- **Servidor MCP silencioso + DNS acotado:** Se eliminó la fuga de salida de las tools en el flujo stdio/CLI, la resolución DNS está acotada en el tiempo y el default de `GITPR_AI_TIMEOUT` bajó de 600s a **180s**.
- **URLs y prompts localizados:** URLs del repositorio estandarizadas en las plantillas y la documentación; los prompts de creación de issues ahora reciben el idioma activo.
- **i18n ampliada a 742 claves:** Los encabezados del changelog son traducibles en runtime (siguen el `--lang`), 48 claves nuevas en los 6 diccionarios, `__lang_version__` v0.0.23 y paridad total — 0 sin traducir, 0 huérfanas.
- **Documentación multilingüe ampliada:** 3 familias nuevas completas en 5 idiomas — `release-notes`, `scm-multiforge` y `suggested-reviewers` — con ADRs de arquitectura, además de 8 temas actualizados.
- **Versión 1.0.0:** `__version__` saltó de 0.0.37 a 1.0.0, y `CHANGELOG.md` ahora lo mantiene el propio `gitpr release`.

## Cómo usar

Actualiza vía PyPI:

```
pip install --upgrade gitpr-cli
```

O descarga el binario standalone desde [GitHub Releases](https://github.com/gitpr-cli/gitpr/releases).

Configura tu forge una sola vez — GitPR lo detecta desde el remote:

```
gitpr --init            # asistente multi-forge: detecta el forge y valida el token
```

Genera el changelog de tu rama y, si quieres, publica el release:

```
gitpr release           # changelog + resumen de IA al inicio de CHANGELOG.md
gitpr release --publish # publica el release en el forge configurado
```

El flujo de PR ahora sugiere revisores automáticamente — desactívalo con `--no-suggest-reviewers`.

## Consejos útiles

`gitpr -is` genera una issue a partir del diff actual, pero hay otros dos motores: `-ht` compila todo el historial de la rama en una issue de release/épica, y `-b src/core.py:140-195` rastrea la evolución de un archivo vía `git blame` para documentar código legado y deuda técnica.
