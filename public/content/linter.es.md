# Linter Local — Análisis Estático

El linter de GitPR valida código contra reglas personalizadas **sin consumir cuotas de IA**. Analiza solo las **líneas añadidas** en tu `git diff`, haciéndolo rápido, enfocado y listo para CI/CD.

---

## Inicio Rápido

```bash
# Generar la configuración por defecto del linter
gitpr -s

# Ejecutar el linter standalone (sin IA)
gitpr -l
```

El linter también se ejecuta automáticamente como parte de `--review` y `--fullreview`, con las violaciones destacadas al inicio de la salida de revisión.

---

## Configuración: `.gitpr.linter.yml`

Define reglas usando **Expresiones Regulares**:

```yaml
rules:
  - name: "check-localhost"
    extensions: ["js", "php", "py"]
    regex: 'http(s)?://(localhost|127\.0\.0\.1)'
    message: "🚨 Uso de localhost detectado en el archivo {file_name}"
    ignore_comments: true
    ignore_paths:
      - "vendor/*"
      - "node_modules/*"
      - "tests/*"

  - name: "no-console-log"
    extensions: ["js", "ts"]
    regex: 'console\.log\('
    message: "🚨 console.log() encontrado en {file_name}:{line_number}"
    ignore_comments: false

  - name: "no-debugger"
    extensions: ["js", "ts"]
    regex: 'debugger'
    message: "🚨 sentencia debugger encontrada en {file_name}:{line_number}"
    ignore_comments: true

  - name: "no-todo-without-ticket"
    extensions: ["*"]
    regex: 'TODO(?!\s*\(\w+-\d+\))'
    message: "📝 TODO sin referencia de ticket en {file_name}:{line_number}"
    ignore_comments: false
```

---

## Campos de Regla

| Campo | Obligatorio | Descripción |
| --- | --- | --- |
| `name` | Sí | Identificador único de la regla |
| `extensions` | Sí | Extensiones de archivo a verificar (`["*"]` para todos) |
| `regex` | Sí | Expresión regular a buscar |
| `message` | Sí | Mensaje de violación. Soporta `{file_name}` y `{line_number}` |
| `ignore_comments` | No | Saltar líneas que están comentadas (por defecto: `false`) |
| `ignore_paths` | No | Patrones glob para directorios/archivos a ignorar |

---

## Integración CI/CD

Ejecuta el linter en tu pipeline para **bloquear merges** con violaciones:

### Ejemplo GitHub Actions

```yaml
name: GitPR Linter
on: [pull_request]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - name: Ejecutar GitPR Linter
        run: |
          gitpr --linter
```

---

## Hooks Pre-Commit

Instala automáticamente con:

```bash
gitpr --installhooks
```

Esto crea hooks `pre-commit` y `prepare-commit-msg` que ejecutan el linter antes de cada commit, detectando problemas en el momento más temprano posible (enfoque **Shift-Left**).

---

## ¿Por qué un Linter Local?

- **Coste IA cero** — sin llamadas API, sin límites de tasa
- **Retroalimentación instantánea** — se ejecuta en milisegundos
- **Personalizable** — reglas que coinciden con los estándares de TU equipo
- **Consciente de Git** — verifica solo lo que has cambiado, no toda la base de código
- **Nativo para CI/CD** — comando único, sin servicios externos

---

[← Guía de Uso](/uso) &nbsp;|&nbsp; [Proveedores IA →](/providers)
