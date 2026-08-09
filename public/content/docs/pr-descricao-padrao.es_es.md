# Documentación Técnica: Generación de Pull Request (Modo Predeterminado)

Cuando se ejecuta **sin flags**, GitPR genera automáticamente una descripción completa de Pull Request en Markdown, lista para pegar en GitHub, GitLab o Bitbucket.

---

## 1. Uso

```bash
gitpr
```

---

## 2. Flujo de Ejecución

```
git fetch → diff contra origin/main → IA → .md
```

1. **`git fetch`** — Sincroniza con el repositorio remoto
2. **Diff** — Compara todos los cambios de la rama actual contra `origin/main`
3. **IA** — Genera el mensaje de commit (Conventional Commits) y la descripción del PR
4. **Output** — Guarda un archivo `.md` en la raíz del proyecto

---

## 3. Output

El archivo generado (`{branch}_{datetime}_PR_DESC.md`) contiene:

```markdown
# 🚀 Sugerencia de Pull Request

**Mensaje de Commit Recomendado:**
feat: descripción corta del cambio

---

## Descripción
...
## Cambios
...
## Impacto
...
```

---

## 4. Personalización

### 4.1 Plantilla de PR

El comportamiento de la IA puede personalizarse mediante el archivo `.gitpr.pr.md`:

```bash
gitpr -s          # Descarga la plantilla
# Edita .gitpr.pr.md con las secciones obligatorias de tu equipo
gitpr             # La IA seguirá tu plantilla
```

### 4.2 Nombre del Archivo de Salida

Configura la variable de entorno `OUTPUT_FILE_NAME` en el archivo `~/.gitpr/.env`:

```ini
OUTPUT_FILE_NAME=PR_{branch}_{datetime}.md
```

Variables disponibles: `{branch}` (nombre de la rama actual) y `{datetime}` (timestamp `YYYYMMDDHHMMSS`).

---

## 5. Selección de Proveedor de IA

```bash
gitpr -p gemini       # Fuerza Google Gemini
gitpr -p deepseek     # Fuerza DeepSeek
```

Si no se especifica ningún proveedor, GitPR usa el predeterminado definido en la variable `DEFAULT_AI_PROVIDER` de `~/.gitpr/.env`.

---

## 6. Caché de Respuestas

GitPR genera un hash MD5 del diff + instrucciones de la IA. Si ejecutas `gitpr` de nuevo **sin cambiar el código**, la respuesta se devuelve desde la caché local en milisegundos, sin consumir cuotas de la API.

> **Nota:** Consulta también la [documentación principal (README.md)](../README.md) para una visión general de todas las funcionalidades.
