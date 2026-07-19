# Cómo Usar GitPR CLI

GitPR tiene un comportamiento por defecto potente y opciones avanzadas para cada etapa de tu flujo de trabajo Git.

---

## Comportamiento por Defecto: Generación de PR

Simplemente ejecuta:

```bash
gitpr
```

La herramienta:
1. Sincronizará con el remote (`git fetch`)
2. Comparará tus cambios contra `origin/main`
3. Generará un archivo Markdown (ej: `feature-login_20260421110134_PR_DESC.md`) con la descripción completa del Pull Request

---

## Comandos y Flags

### 🔖 Mensaje de Commit

```bash
gitpr -c
# o
gitpr --commit
```

Ejecuta `git diff` y muestra un mensaje en formato **Conventional Commits**. Ideal para commits rápidos y estandarizados.

---

### 🔍 Code Review (Cambios Staged)

```bash
gitpr -r
# o
gitpr --review
```

Revisión detallada con IA de tus cambios locales staged. Se enfoca en bugs, seguridad, rendimiento y calidad de código.

---

### 🔎 Code Review Completo

```bash
gitpr -f
# o
gitpr --fullreview
```

Revisión completa analizando **todos los cambios desde la rama remota**. Ideal para revisiones exhaustivas de PR.

---

### 📄 Auditoría de Archivo Completo

```bash
gitpr -r -i src/modulo_legado.py
# o
gitpr --review --input ruta/al/archivo
```

Ignora el historial git y audita el **archivo completo**. Excelente para consultoría de refactorización de código legacy. Debe usarse con `-r` o `-f`.

---

### 💬 Chat Interactivo (Pair Programming)

```bash
gitpr -ch
# o
gitpr --chat
```

Abre una **terminal TUI** donde la IA ve tu diff actual y mantiene conversación contextual:

| Atajo | Acción |
| --- | --- |
| `F2` | Actualizar contexto del diff |
| `F5` | Extraer bloques de código a archivo de patch |
| `F6` | Exportar sesión a Markdown |
| `/explain` | Explicar el diff actual |
| `/tests` | Generar pruebas unitarias |
| `/optimize` | Sugerir optimizaciones |
| `/clear` | Limpiar memoria de la conversación |

La memoria es **por rama**, por lo que cambiar de rama te da un contexto limpio.

---

### 🛡️ Linter Estático

```bash
gitpr -l
# o
gitpr --linter
```

Ejecuta **solo el linter estático local** — coste IA cero. Valida las líneas modificadas contra las reglas en `.gitpr.linter.yml`. Perfecto para pipelines CI/CD y hooks pre-commit.

---

### 🎫 Generador de Issues

```bash
gitpr -is
# o
gitpr --issue
```

Abre un **panel TUI** interactivo para editar y enviar issues estructuradas. **3 motores de contexto**:

| Comando | Contexto | Caso de Uso |
| --- | --- | --- |
| `gitpr -is` | `git diff` actual | Documentar una tarea que acabas de programar |
| `gitpr -is -ht` | Historial completo de la rama | Generar documentación de release/epic |
| `gitpr -is -b archivo:líneas` | Línea temporal vía `git blame` | Documentar evolución de código legacy y deuda técnica |

---

### 🪝 Git Hooks

```bash
gitpr -ih
# o
gitpr --installhooks
```

Instala hooks `pre-commit` y `prepare-commit-msg` en tu repositorio para barreras de calidad automáticas.

---

### 🎨 Plantillas de Skills

```bash
gitpr -s
# o
gitpr --skill
```

Genera plantillas personalizables de prompt IA (archivos `.gitpr.*.md`) y reglas del linter (`.gitpr.linter.yml`) en la raíz de tu proyecto.

---

### 🌐 Sobrescritura de Idioma y Proveedor

```bash
# Forzar idioma para esta ejecución
gitpr --lang es

# Cambiar proveedor IA sobre la marcha
gitpr --provider deepseek
gitpr --provider ollama
```

---

### 🔄 Auto-Updater

```bash
gitpr -u
# o
gitpr --update
```

Verifica en GitHub Releases la última versión y hace hot-swap del binario.

---

### ❓ Ayuda

```bash
gitpr -h              # Ayuda general
gitpr -h --issue      # Ayuda contextual para el comando issue
gitpr -h --linter     # Ayuda contextual para el comando linter
```

---

[← Instalación](/instalacao) &nbsp;|&nbsp; [Guía del Linter →](/linter)
