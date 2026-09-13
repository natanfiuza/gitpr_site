# GitPR 1.1.0 — Novedades

## Novedades de esta versión

- **`gitpr config` — la TUI de configuración interactiva:** Una pantalla master-detail sobre `~/.gitpr/.env` con menú lateral de categorías, edición de campos en línea, búsqueda global (`/`), `F2` para guardar, `Ctrl+R` para restaurar y `Esc` para descartar. Un schema declarativo (`src/config_schema.py` — 12 categorías, 56 `ConfigField`, 8 avanzados) es la única fuente de verdad: menú, widgets, valores predeterminados y validación derivan todos de él, así que añadir un ajuste pasó a ser un cambio de datos, no de interfaz.
- **Sección Skills dentro de la TUI:** La primera superficie con alcance de proyecto de la pantalla edita los archivos `.gitpr/skill/*.md` de tu proyecto atómicamente (preservando CRLF/LF) en la misma pasada de `F2` que escribe el `.env`, con un único contador de cambios pendientes.
- **Registro general de uso:** Una línea por comando en `~/.gitpr/logs/<fecha>.log` — un archivo por día, escritura síncrona, sin imprimir nunca y sin lanzar nunca excepciones. Responde a "¿qué ejecuté realmente, y cuándo?". Controlado por `GITPR_SHOW_LOGS`.
- **Corrección del idioma de los Git hooks:** El idioma que elegiste ahora se respeta de verdad — `HOOK_SCRIPT_SUFFIXES` mapea los códigos de interfaz (`es_es`, `fr_fr`) a los sufijos publicados (`.es`, `.fr`), y `--lang` ya no se ignora.
- **Distribución exclusiva vía PyPI con puerta de actualización obligatoria:** El canal binario desapareció — sin generación, sin subida, sin fallback. `enforce_update_required()` bloquea la ejecución con código de salida 1 cuando hay una versión más reciente publicada e imprime el comando exacto a ejecutar.
- **i18n ampliada a 955 claves:** +213 claves que cubren la TUI de configuración y la puerta de PyPI, con paridad total de key sets en los 6 diccionarios (`__lang_version__` v0.0.25).
- **2 familias de documentación nuevas en 5 idiomas:** `config-tui` y `usage-log`, además de 7 temas actualizados (`ARCHITECTURE`, `auto-update`, `hooks-versioning`, `mcp-integration`, `skill-template`, `testar_sem_usar_pypi`, `version-markers`).
- **Clave muerta eliminada:** `PR_AUTO_PUBLISH` se demostró sin uso y se retiró — si una instalación antigua aún conserva la línea, ahora aparece solo como lectura en *Desconocidas*.
- **Versión 1.1.0:** `__version__` pasó de 1.0.0 a 1.1.0, y `CHANGELOG.md` registra `[1.1.0] - 2026-09-13`, generado por el propio `gitpr release`.

## Cómo usar

GitPR 1.1.0 se distribuye exclusivamente a través de PyPI — el canal binario se retiró:

```
pip install --upgrade gitpr-cli
```

Atención: a partir de esta versión, GitPR consulta PyPI en cada ejecución y **bloquea la ejecución** si hay una versión más reciente publicada, indicando exactamente qué ejecutar. La comprobación se cachea por día, `--update` solo informa y `GITPR_SKIP_UPDATE_CHECK` desactiva la puerta para automatización offline.

Configura todo desde la nueva TUI — sin editar `~/.gitpr/.env` a mano:

```
gitpr config            # pantalla interactiva de configuración (F2 guarda)
```

Editar las instrucciones de IA de tu proyecto forma parte de esa misma pantalla: la sección Skills escribe los `.gitpr/skill/*.md` en la misma pasada de guardado.

¿Quieres saber qué ejecutaste realmente, y cuándo? Cada comando se añade a un registro diario:

```
~/.gitpr/logs/<fecha>.log    # un archivo por día; desactívalo con GITPR_SHOW_LOGS=false
```

También llegaron correcciones de idioma: `--lang` ahora se aplica también a tus Git hooks, así que los scripts de hook siguen el idioma que elegiste.

## Consejos útiles

¿Equipo con idiomas mixtos? `gitpr --lang <código>` sustituye el idioma de una sola ejecución: `gitpr -c --lang en`, `gitpr -r --lang pt_br`, `gitpr -ch --lang fr`. GitPR habla 5 idiomas y detecta automáticamente el locale del sistema en el primer uso — y, desde la 1.1.0, esa elección llega también a tus Git hooks.
