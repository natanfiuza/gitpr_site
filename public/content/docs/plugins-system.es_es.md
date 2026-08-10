# 🧩 Sistema de Plugins de GitPR

El sistema de plugins de GitPR te permite ampliar las capacidades de la herramienta a nivel global en **todos tus proyectos** sin duplicar archivos de configuración.

## 📂 Estructura de Directorios

Los plugins se almacenan en tu carpeta global de configuración de GitPR:

```
~/.gitpr/plugins/
├── linter/          # Global linter rule packs (.yml / .yaml)
│   ├── security.yml
│   ├── no-debug.yml
│   └── php-psr.yml
└── prompts/         # Custom AI prompt templates (.md)
    ├── audit_security.md
    ├── generate_tests.md
    └── explain_to_business.md
```

> **Consejo:** Estos directorios se crean automáticamente cuando ejecutas cualquier comando de GitPR. También puedes ejecutar `gitpr --plugins` para comprobar si existen y listar todos los plugins activos.

---

## 🔍 Plugins de Linter (`plugins/linter/`)

### ¿Qué son?

Los plugins de linter son archivos YAML que contienen reglas con el mismo formato que `.gitpr.linter.yml`, pero se aplican **a nivel global** — en todos los proyectos de tu máquina.

### Diferencia entre Local y Global

| Aspecto | Local (`.gitpr.linter.yml`) | Global (`~/.gitpr/plugins/linter/*.yml`) |
|--------|---------------------------|----------------------------------------|
| **Alcance** | Específico del proyecto | Todos los proyectos de la máquina |
| **Versionado** | Se versiona junto con el proyecto | Personal — no versionado por proyecto |
| **Caso de uso** | Convenciones del equipo para un repositorio | Estándares personales, comprobaciones de seguridad |

### Cómo funciona

Cuando GitPR ejecuta el linter (mediante `-l`, `-r`, `-f` o hooks de pre-commit):

1. Carga las reglas del `.gitpr.skill/.gitpr.linter.yml` local (si existe)
2. Itera sobre todos los archivos `.yml` y `.yaml` en `~/.gitpr/plugins/linter/`
3. Combina ambos conjuntos en una única lista de reglas
4. Ejecuta las reglas combinadas sobre el diff

Si un plugin global tiene YAML inválido, GitPR muestra una **advertencia amarilla** y continúa — tu flujo de trabajo nunca se bloquea por un plugin defectuoso.

### Ejemplo: Paquete de Seguridad

Crea `~/.gitpr/plugins/linter/security.yml`:

```yaml
rules:
  - name: "AWS Access Key leak"
    regex: "AKIA[0-9A-Z]{16}"
    severity: error
    message: "AWS Access Key ID found — this should never be committed."

  - name: "Generic password assignment"
    regex: "(?i)(password|passwd|senha)\\s*=\\s*['\"][^'\"]+['\"]"
    severity: warning
    message: "Hardcoded password detected. Use environment variables."
```

### Ejemplo: Paquete No-Debug

Crea `~/.gitpr/plugins/linter/no-debug.yml`:

```yaml
rules:
  - name: "console.log left behind"
    regex: "console\\.log\\("
    severity: error
    extensions: [".js", ".ts", ".jsx", ".tsx"]
    message: "Remove console.log() before committing."

  - name: "var_dump left behind"
    regex: "var_dump\\("
    severity: error
    extensions: [".php"]
    message: "Remove var_dump() before committing."
```

---

## 💬 Plugins de Prompts (`plugins/prompts/`)

### ¿Qué son?

Los plugins de prompts son archivos Markdown (`.md`) que definen prompts de IA personalizados. Cada archivo queda disponible como:

- Un **recurso MCP** en `prompt://plugin/<filename>`
- Un **prompt MCP** llamado `Plugin: <filename>`

Esto permite que los editores con IA (VS Code, Cursor, Claude Desktop, Zed) utilicen tus flujos de trabajo personalizados.

### Cómo funciona

Al iniciar el servidor MCP (`gitpr --mcp`), GitPR:

1. Escanea `~/.gitpr/plugins/prompts/` en busca de archivos `.md`
2. Registra cada uno como recurso y prompt MCP
3. Los lista junto a los prompts integrados en `prompt://list`

### Ejemplo: Auditor de Seguridad

Crea `~/.gitpr/plugins/prompts/audit_security.md`:

```markdown
You are a Senior Security Engineer. Perform a thorough security review of the current diff.

Focus on:
1. **Injection vulnerabilities** (SQL, NoSQL, Command, XPath)
2. **XSS / Cross-Site Scripting** vectors
3. **Sensitive data exposure** (keys, tokens, PII in logs)
4. **Authentication / Authorization** flaws
5. **Insecure deserialization**
6. **Path traversal** risks

For each finding, provide:
- **Severity**: Critical / High / Medium / Low
- **File & Line**: Where the issue is
- **Description**: What the vulnerability is
- **Fix**: Concrete code suggestion

Use the format:
### [Severity] Vulnerability Name
- **File**: path/to/file:line
- **Description**: ...
- **Fix**: ...
```

### Ejemplo: Generador de Tests PHPUnit

Crea `~/.gitpr/plugins/prompts/generate_tests.md`:

```markdown
You are a Senior PHP Developer specialized in Test-Driven Development.

For the code changes in this diff, generate comprehensive PHPUnit tests following these rules:

1. **100% coverage target** — cover all new/changed methods
2. **Follow PSR-12** coding standards
3. **Use data providers** for multiple input scenarios
4. **Mock external dependencies** (APIs, databases, file systems)
5. **Test edge cases**: null, empty, boundary values, exceptions

Output a ready-to-run PHPUnit test class with:
- Class name matching the source + "Test" suffix
- setUp() for shared fixtures
- test methods prefixed with "test"
- @test, @dataProvider, and @covers annotations
```

---

## 🖥️ CLI: Listado de Plugins Activos

Ejecuta `gitpr --plugins` para ver todos los plugins instalados:

```
🧩 GitPR Plugin System

🔍 Linter Packs (2):
  - security.yml
  - no-debug.yml

💬 Custom Prompts (1):
  - audit_security.md

💡 Plugin directory: ~/.gitpr/plugins/
```

Usa `gitpr -h --plugins` para obtener ayuda contextual sobre el sistema de plugins.

---

## 🔄 Orden de Ejecución y Prioridad

| Capa | Prioridad | Comportamiento de anulación |
|-------|----------|-------------------|
| `.gitpr.linter.yml` local | Se carga primero | — |
| Global `plugins/linter/*.yml` | Se añade después de las locales | Mismo nombre de regla = ambas se ejecutan (sin deduplicación) |

Las reglas son **aditivas** — los plugins globales nunca reemplazan a las reglas locales; se añaden junto a ellas.

---

## 🛡️ Manejo de Errores

- **YAML global malformado** → Advertencia amarilla, el plugin se omite. El flujo principal continúa.
- **Directorio de plugins inexistente** → Se ignora silenciosamente. Sin advertencias.
- **Archivo de plugin vacío** → Se omite sin ningún mensaje.
- **Inicio del servidor MCP** → Los fallos de registro de plugins se capturan silenciosamente. MCP arranca con normalidad.

---

## 📚 Ver También

- [Reglas de linter personalizadas](linter-regras-customizadas) — Cómo escribir reglas en `.gitpr.linter.yml`
- [Skills y plantillas](skill-template) — Prompts y reglas de IA locales al proyecto
- [Integración MCP](mcp-integration) — Uso de GitPR con editores de IA
