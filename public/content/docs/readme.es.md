
# **GitPR CLI 🚀**

<p align="center">
  <img src="https://raw.githubusercontent.com/natanfiuza/gitpr/main/docs/logo.png" alt="GitPR Logo" width="150">
</p>

GitPR CLI es una herramienta de automatización de línea de comandos que utiliza inteligencia artificial de **Google Gemini** y **DeepSeek** para analizar los cambios en tu código (git diff) o archivos completos. La herramienta genera automáticamente mensajes de commit en el estándar *Conventional Commits*, descripciones detalladas de Pull Requests y revisiones de código profundas orientadas a reducir la deuda técnica.

🌐 **Sitio web:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/) · 📂 **Repositorio:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)

## **🛠️ Tecnologías y Librerías Utilizadas**

Este proyecto fue desarrollado en Python y utiliza las siguientes librerías principales:

* [**Click**](https://click.palletsprojects.com/): Para crear una interfaz de línea de comandos (CLI) robusta y fácil de usar.
* [**Google GenAI**](https://pypi.org/project/google-genai/): SDK oficial para la integración directa con la API de Gemini.
* [**OpenAI**](https://pypi.org/project/openai/): Librería utilizada por su completa compatibilidad con la potente API de **DeepSeek**.
* [**Python-dotenv**](https://pypi.org/project/python-dotenv/): Para la gestión segura de variables de entorno.
* [**Pytest**](https://docs.pytest.org/): Para ejecutar pruebas unitarias de forma simple, colorida y legible en la consola.
* [**Cryptography**](https://cryptography.io/): Para garantizar que tu `GEMINI_API_KEY` se almacene cifrada y de forma segura en disco.
* [**PyYAML**](https://pyyaml.org/): Se utiliza para leer y procesar las reglas personalizadas de análisis estático del archivo `.gitpr.linter.yml`.
* [**Textual**](https://textual.textualize.io/): Potente librería para crear Interfaces Gráficas de Terminal (TUI), utilizada en el panel interactivo de generación y edición de issues.
* [**Requests**](https://pypi.org/project/requests/): Librería elegante y robusta para peticiones HTTP, utilizada para comunicarse con la API REST de GitHub.
* [**MCP**](https://pypi.org/project/mcp/): SDK oficial de Python para el Model Context Protocol, que permite a GitPR integrarse directamente con editores e IDE potenciados por IA.

----

## 📦 Cómo Compilar el Ejecutable Localmente

Si deseas generar tu propio binario a partir del código fuente, utilizamos **PyInstaller**. Asegúrate de estar en el directorio raíz del proyecto con el entorno virtual configurado.

1. Instala las dependencias de desarrollo (si aún no lo has hecho):
   ```bash
   pipenv install --dev
   ```

2. Ejecuta el comando de compilación apuntando a nuestro punto de entrada (`run.py`):
   ```bash
   pipenv run pyinstaller --noconfirm --onefile --icon=icon.ico --name gitpr run.py
   ```
> **Nota técnica:** La bandera `--onefile` garantiza que todo Python, las librerías y las dependencias se compriman en un solo binario, mientras que `--paths src` ayuda al compilador a encontrar nuestros archivos `core.py` y `config.py`. 🛠️

Después de ejecutar este comando, PyInstaller creará algunas carpetas (`build` y `dist`).
Tu archivo final listo para usar estará dentro de la carpeta **`dist/`** con el nombre `gitpr` (o `gitpr.exe` en Windows).


----

## 🧪 Ejecutar Pruebas

Para asegurarte de que la lógica de captura de Git y la integración con IA funcionan correctamente, utilizamos pruebas unitarias.

1. Instala las dependencias de prueba (si aún no lo has hecho):
   ```bash
   pipenv install --dev pytest
   ```

2. Ejecuta las pruebas con el comando:
   ```bash
   pipenv run pytest -v
   ```
Pytest detectará automáticamente los archivos dentro de la carpeta `tests/` y mostrará un informe detallado de la ejecución.

----
## **⚙️ Instalación y Configuración**

### **Usando el Ejecutable (Recomendado)**

1. Descarga el archivo ejecutable gitpr desde la pestaña "Releases" en GitHub.
2. Mueve el ejecutable a una carpeta que esté en tu PATH (por ejemplo: /usr/local/bin en Linux/Mac o tu carpeta de usuario en Windows).
3. En la primera ejecución, el asistente te guiará:
   ```bash
   $ gitpr
   ```
```bash
🚀 Automatización Inteligente de PR con IA

🔧 Primera ejecución detectada. Vamos a configurar GitPR CLI.

🔑 Ingresa tu GEMINI_API_KEY:

📄 Patrón de nombre de archivo de salida predeterminado [{branch}_{datetime}_PR_DESC.md]:
```
*Nota: Tu configuración se guardará de forma segura en el archivo `~/.gitpr/.env`.*

> **🔒 Nota de Seguridad:** GitPR CLI utiliza cifrado simétrico (Fernet). Tu clave de API se almacena como un hash en el archivo `.env`, y la clave maestra para el descifrado se genera automáticamente en `~/.gitpr/secret.key`. **Nunca compartas tu archivo secret.key.**

### Desde el Código Fuente

1. Clona el repositorio: `git clone https://github.com/natanfiuza/gitpr.git`

2. Entra en la carpeta: `cd gitpr`

3. Configura el entorno:
```bash
pipenv install google-genai openai python-dotenv click cryptography
```
4. Ejecuta: pipenv run python src/main.py

## **💻 Cómo Usar**

GitPR tiene un comportamiento predeterminado potente y varias opciones avanzadas para ayudarte en tu día a día como desarrollador.

### **Comportamiento Predeterminado (Pull Request)**
Simplemente ejecuta el comando básico en tu terminal:
```bash
gitpr
```
La herramienta se sincronizará con el remoto (`git fetch`), comparará tus cambios con la rama main remota (por ejemplo: `origin/main`), y generará un archivo Markdown (por ejemplo: `feature-login_20260421110134_PR_DESC.md`) en la raíz de tu proyecto con la sugerencia completa para tu Pull Request.

### **Opciones y Comandos Avanzados**
Puedes usar las siguientes *banderas* para acciones específicas:

* `-c` o `--commit`: Ejecuta un `git diff` local y muestra **solo el mensaje de commit sugerido**.
* `-r` o `--review`: Realiza una **Code Review** detallada de los cambios locales.
* `-f` o `--fullreview`: Realiza una **Code Review Completa** analizando todos los cambios desde la rama remota.
* `-i <file>` o `--input <file>`: **Auditoría de Archivo Completo.** Debe usarse junto con `-r` o `-f`; ignora el historial de git y realiza una Code Review de todo el archivo. Excelente para actuar como consultor en la refactorización de código heredado.
* `--provider <gemini|deepseek|ollama>`: Fuerza el uso de una IA específica solo para esta ejecución, ignorando tu valor predeterminado guardado en `.env`.
* `--lang <code>`: Fuerza el idioma de la interfaz para esta ejecución (ej.: `en_us`, `pt_br`). Sobrescribe `GITPR_LANG` en `.env` sin persistir el cambio.
* `-ch` o `--chat`: Abre el **Chat Interactivo de Pair Programming** — una terminal TUI donde la IA ve tu diff actual y mantiene una conversación contextual. Cuenta con memoria por rama, comandos slash (`/explain`, `/tests`, `/optimize`, `/clear`), auto-parche (F5), actualización de diff (F2) y exportación de sesión (F6).
* `-l` o `--linter`: Ejecuta **solo el linter estático local** (sin llamadas a la IA). Ideal para usar en pipelines CI/CD para bloquear código que no cumple con las normas.
* `--mcp`: Inicia GitPR como un **servidor MCP** (Model Context Protocol) mediante transporte stdio. Permite la integración con VS Code, Cursor, Claude Desktop y otros editores compatibles con MCP, exponiendo todas las capacidades de IA de GitPR como herramientas directamente dentro de tu IDE. También disponible como el comando independiente `gitpr-mcp`.
* `-ih` o `--installhooks`: Instala automáticamente **Git Hooks locales** (`pre-commit` y `prepare-commit-msg`) en tu repositorio.
* `-s` o `--skill`: Crea los archivos de plantilla de contexto de IA (`.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md`) y el Linter (`.gitpr.linter.yml`) en la raíz del proyecto.
* `-is` o `--issue`: Genera automáticamente un borrador de un **Issue estandarizado** y abre una interfaz interactiva (TUI) para edición o envío directo mediante API REST. Esta funcionalidad tiene **3 motores de contexto** según la combinación de comandos:
  * **Issue de Código Nuevo (`gitpr -is`):** Lee el `git diff` actual. **Por qué usarlo:** Ideal para documentar rápidamente la tarea que acabas de programar, antes de hacer commit.
  * **Issue Épico/Release (`gitpr -is -ht`):** Lee el historial completo de la rama actual (Git Log + PR Cache). **Por qué usarlo:** Ideal para generar documentación consolidada de una release completa o una *feature* grande que tomó varios días/commits.
  * **Issue Arqueológico/Deuda Técnica (`gitpr -is -b file:lines`):** Lee la línea de tiempo de una regla de negocio específica. **Por qué usarlo:** Ideal para documentar deuda técnica, explicando cómo evolucionó un bloque de código heredado y por qué necesita ser refactorizado.
* `-h` o `--help`: Muestra la ayuda general con todas las opciones. Úsalo junto con otra bandera para **ayuda contextual** (ej.: `gitpr -h --issue`, `gitpr -h --linter`) con un enlace directo a la documentación detallada de cada funcionalidad.
* `-u` o `--update`: Verifica e instala la última versión de GitPR (Actualizador Automático).

> **⚙️ Nota Técnica (--hook):** GitPR tiene una bandera oculta `--hook <file>` que se activa exclusivamente mediante el sistema de Git Hooks en segundo plano. Permite que la IA inyecte el mensaje sugerido directamente en el archivo temporal de Git, sin saturar tu terminal.
>
> **⚙️ Nota Técnica (--pre-save):** GitPR tiene una bandera oculta de depuración `--pre-save` que se puede combinar con cualquier comando de IA (ej.: `gitpr -c --pre-save`). Antes de cada llamada a la IA, guarda el payload completo que se enviará al modelo (instrucción del sistema + prompt + contadores de caracteres) en un archivo `_{action}-{datetime}.json` en la carpeta actual, y luego continúa normalmente. Útil para inspeccionar prompts muy grandes. Nota: cuando la respuesta proviene de la caché local, no se realiza ninguna llamada ni se genera ningún archivo.

### 📦 Diffs Grandes (Map-Reduce)

Cuando tu diff es demasiado grande para una sola llamada a la IA (más de ~90k tokens estimados), GitPR lo divide automáticamente en lotes por archivo, solicita a la IA un resumen técnico de cada parte (Map) y unifica todo en el mensaje de commit, revisión o descripción de PR final (Reduce). No se necesitan banderas: se activa bajo demanda y muestra el progreso en la consola.

📚 Documentación completa: [docs/map-reduce-diff.md](https://github.com/natanfiuza/gitpr/blob/main/docs/map-reduce-diff.md)

## 🛡️ Linter Local (Análisis Estático)

GitPR CLI te permite definir reglas estrictas que se validarán al instante durante `--review` o `--fullreview`, sin depender de la IA. Esto es ideal para evitar que errores comunes (como `console.log` o IPs de prueba) lleguen al repositorio.

### Cómo configurar `.gitpr.linter.yml`:
Al ejecutar `gitpr --skill`, se generará una plantilla. Puedes configurar reglas usando Expresiones Regulares (Regex):

```yaml
rules:
  - name: "check-localhost"
    extensions: ["js", "php"] # Extensiones a validar
    regex: 'http(s)?://(localhost|127\.0\.0\.1)' # Qué buscar
    message: "🚨 Uso de localhost detectado en el archivo {file_name}"
    ignore_comments: true # Ignora si la línea está comentada
    ignore_paths: # Carpetas o archivos ignorados (acepta *)
      - "vendor/*"
      - "node_modules/*"
```

El Linter analiza solo las **líneas añadidas** en tu `git diff`, garantizando una ejecución enfocada y extremadamente rápida. Si hay infracciones, aparecerán resaltadas al principio de tu archivo de revisión.

## 🧠 Arquitectura Multi-Modelo (Independiente de la IA)

GitPR no está vinculado a una única Inteligencia Artificial. Durante la configuración inicial, el usuario puede elegir su motor predeterminado. Actualmente admitimos:
* **Google Gemini** (Predeterminado: `gemini-2.5-flash`)
* **DeepSeek** (Predeterminado: `deepseek-chat`)
* **Ollama** (Local) — ejecuta modelos localmente sin internet, totalmente compatible con el formato de la API de OpenAI

Puedes cambiar dinámicamente de modelo configurando las variables `GEMINI_API_MODEL` o `DEEPSEEK_API_MODEL` en tu archivo `~/.gitpr/.env`, o cambiar en tiempo real usando la bandera `--provider`.

## 🎯 Sistema Personalizable de "Skills" (Ingeniería de Prompts)

En lugar de ocultar las instrucciones de la IA en el código fuente, GitPR utiliza archivos Markdown locales que actúan como *Instrucciones del Sistema*. Al ejecutar `gitpr -s`, se generan los siguientes archivos en la raíz de tu proyecto para personalizar la "personalidad" de la IA según las reglas de negocio de tu empresa:

* `.gitpr.commit.md`: Reglas para generar mensajes de commit cortos.
* `.gitpr.pr.md`: Estructura de temas requerida para la descripción del Pull Request.
* `.gitpr.review.md`: Define el enfoque arquitectónico (ej.: SOLID, Clean Code) para el análisis del diff.
* `.gitpr.filereview.md`: Define reglas estrictas de cohesión y acoplamiento para la auditoría de archivos completos (usado con `--input`).
* `.gitpr.issue.md`: Define la estructura y el nivel de detalle requerido para generar Issues estandarizados (usado con `--issue`).
* `.gitpr.blame.md`: Define el enfoque del análisis arqueológico para el rastreo de código heredado (usado con `--blame`).

## 🌐 Internacionalización (i18n)

GitPR detecta automáticamente el idioma de tu sistema y muestra mensajes en tu idioma nativo. El sistema i18n está inspirado en el **`__()` helper de Laravel**:

* **Auto-detección:** En la primera ejecución, GitPR detecta el idioma de tu SO y lo guarda en `~/.gitpr/.env` (`GITPR_LANG`).
* **Archivos de traducción:** Los paquetes de idioma se descargan automáticamente desde el repositorio oficial a `~/.gitpr/langs/`.
* **Texto en inglés por defecto:** Si falta una traducción, se muestra directamente el texto en inglés.
* **API para desarrolladores:** Usa `from src.i18n import __` y envuelve todas las cadenas dirigidas al usuario con `__("Your text here")`.
* **Placeholders:** Admite parámetros nombrados — `__("Downloading {file}...", file="template.md")`.

Para forzar un idioma específico, configura `GITPR_LANG=pt_br` o `GITPR_LANG=en` en `~/.gitpr/.env`.

> 📖 **Guía completa para desarrolladores:** [docs/i18n_explanation.md](https://github.com/natanfiuza/gitpr/blob/main/docs/i18n_explanation.md) — arquitectura, patrones de uso, precauciones sobre importaciones circulares y cómo agregar nuevos idiomas.

## 🔌 Integración MCP (Model Context Protocol)

GitPR puede ejecutarse como un **servidor MCP**, exponiendo sus capacidades potenciadas por IA como herramientas que el asistente de IA de tu editor puede invocar directamente, sin necesidad de terminal. Esto permite un flujo de trabajo totalmente integrado donde puedes generar mensajes de commit, revisar código, ejecutar linters, rastrear orígenes de código y crear issues sin salir de tu IDE.

### Editores Compatibles

| Editor | Archivo de Configuración |
| ------ | ----------- |
| **VS Code** | `.vscode/mcp.json` |
| **Cursor** | `.cursor/mcp.json` |
| **Claude Code** | `.mcp.json` |
| **Claude Desktop** | `claude_desktop_config.json` |
| **Zed** | `settings.json` |

### Configuración Rápida

Usa el instalador integrado para configurar tu editor automáticamente:

```bash
gitpr-mcp --install vscode    # Crea .vscode/mcp.json
gitpr-mcp --install cursor      # Crea .cursor/mcp.json
gitpr-mcp --install claude-code # Crea .mcp.json
gitpr-mcp --install claude      # Actualiza la configuración de Claude Desktop
gitpr-mcp --install zed         # Actualiza la configuración de Zed
gitpr-mcp --install auto      # Detecta e instala automáticamente para todos los encontrados
```

El instalador crea el directorio de configuración si es necesario, se fusiona con cualquier configuración existente (nunca sobrescribe otros servidores) y es seguro ejecutarlo varias veces.

> La configuración manual también es compatible — consulta [docs/mcp-integration.md](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md)
> para conocer el formato JSON de configuración para cada editor.

Una vez configurado, usa lenguaje natural en el chat de IA de tu editor:

   * *"Revisa mis cambios actuales"* → llama a `review_code`
   * *"Genera un mensaje de commit"* → llama a `generate_commit_message`
   * *"Crea una descripción de PR"* → llama a `generate_pr_description`
   * *"Ejecuta el linter en mi diff"* → llama a `run_linter`

### Herramientas MCP Disponibles

| Herramienta | Descripción |
| ---- | ----------- |
| `get_git_context` | Rama actual, nombre del repositorio y URL remota |
| `analyze_diff` | Git diff de los cambios no confirmados |
| `get_full_diff` | Diff completo contra origin/main |
| `generate_commit_message` | Mensaje de commit generado por IA en formato Conventional Commits |
| `review_code` | Revisión de código por IA de los cambios locales |
| `full_review` | Revisión de código por IA de todos los cambios desde origin/main |
| `generate_pr_description` | Descripción completa del PR (título + cuerpo) |
| `run_linter` | Linter estático contra `.gitpr.linter.yml` |
| `analyze_blame` | Git blame + clasificación por IA |
| `generate_issue` | Issue estructurado a partir del diff, historial o blame |

📖 **Documentación completa:** [docs/mcp-integration.md](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md) — disponible en 5 idiomas (EN, PT-BR, PT-PT, ES, FR).

## 📚 Documentación Técnica y Guías Avanzadas

Para mantener este README conciso, detallamos las implementaciones más avanzadas enfocadas en **DevOps** e **Integración Continua** en documentos separados.

Si deseas implementar GitPR como una barrera de calidad automatizada en tu equipo, consulta las guías a continuación.

> 🌐 Cada guía está disponible en **5 idiomas** — agrega `.pt_br`, `.pt_pt`, `.fr_fr` o `.es_es` antes de la extensión `.md` para las versiones traducidas (ej.: `docs/understanding_chat_functionality.pt_br.md`). El inglés es el idioma predeterminado sin sufijo.

### Chat y Funcionalidades Interactivas

* [**🧠 Chat Interactivo (Pair Programming)**](https://github.com/natanfiuza/gitpr/blob/main/docs/understanding_chat_functionality.md) — Cómo usar el chat de IA con memoria, comandos slash, auto-parche y exportación de sesión.

### DevOps y CI/CD

* [**Git Hooks Locales (Shift-Left)**](https://github.com/natanfiuza/gitpr/blob/main/docs/git-hooks-locais.md) — Cómo usar `gitpr --installhooks` para crear barreras de seguridad en la máquina del desarrollador y usar IA para escribir mensajes de commit automáticamente.
* [**Linter Estático Personalizable**](https://github.com/natanfiuza/gitpr/blob/main/docs/linter-regras-customizadas.md) — Cómo crear reglas de validación en `.gitpr.linter.yml` para CI/CD y hooks de pre-commit.
* [**Integración CI/CD (GitHub Actions)**](https://github.com/natanfiuza/gitpr/blob/main/docs/github-ci-linter.md) — Cómo ejecutar GitPR en el pipeline para bloquear el "Merge" de PRs con infracciones.

### Funcionalidades Principales

* [**Pull Request (Modo Predeterminado)**](https://github.com/natanfiuza/gitpr/blob/main/docs/pr-descricao-padrao.md) — Flujo completo para generar descripciones de PR sin banderas.
* [**Code Review con IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/code-review-ia.md) — Guía de los modos de revisión (`--review`, `--fullreview`) y auditoría de archivos (`--input`).
* [**Mensajes de Commit con IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/commit-message-ia.md) — Cómo generar mensajes en el estándar Conventional Commits e integrarlos con Git Hooks.
* [**Generación de Issues e Interfaz TUI**](https://github.com/natanfiuza/gitpr/blob/main/docs/issue-tui-help.md) — Cómo usar la interfaz gráfica de terminal (TUI) y los 3 motores de contexto para gestionar Issues estructurados.
* [**Arqueólogo de Código (Git Blame)**](https://github.com/natanfiuza/gitpr/blob/main/docs/blame-arqueologo.md) — Cómo rastrear el origen de las reglas de negocio con `git blame` e IA.
* [**Sistema de Skills y Plantillas**](https://github.com/natanfiuza/gitpr/blob/main/docs/skill-template.md) — Cómo personalizar el comportamiento de la IA con archivos `.gitpr.*.md`.

### Configuración e Infraestructura

* [**Proveedores de IA**](https://github.com/natanfiuza/gitpr/blob/main/docs/providers-ia.md) — Configuración y selección entre Google Gemini, DeepSeek y Ollama.
* [**Actualizador Automático**](https://github.com/natanfiuza/gitpr/blob/main/docs/auto-update.md) — Cómo funciona la actualización automática (hot-swap) de GitPR.
* [**Token de GitHub (PAT) Integración y Seguridad**](https://github.com/natanfiuza/gitpr/blob/main/docs/github-pat-integration.md) — Entiende cómo GitPR crea issues directamente en el repositorio con autenticación.
* [**Internacionalización (i18n)**](https://github.com/natanfiuza/gitpr/blob/main/docs/i18n_explanation.md) — Arquitectura, patrones de uso y cómo agregar nuevos idiomas.
* [**Integración MCP**](https://github.com/natanfiuza/gitpr/blob/main/docs/mcp-integration.md) — Conecta GitPR a VS Code, Cursor y Claude Desktop mediante el Model Context Protocol.

## ⚡ Sistema de Caché Local (Ahorro de Cuota)

GitPR cuenta con un motor de caché inteligente basado en **MD5**. Cada vez que ejecutas un comando (`--review`, `--commit`, etc.), la herramienta genera un hash exacto de tu código actual (diff) y las instrucciones.
Si ejecutas el mismo comando de nuevo sin cambiar el código, GitPR intercepta la solicitud y devuelve el resultado al instante (en milisegundos) desde la carpeta `~/.gitpr/cache/prompts/`, ¡ahorrándote tiempo y cuota de la API de Gemini!

## 🔄 Actualizador Automático (Actualización Over-The-Air)

Olvídate de descargar nuevas versiones manualmente. GitPR tiene un Guardián de Conexión y un actualizador integrado:
* Verifica la disponibilidad de red antes de iniciar para no bloquear tu flujo de trabajo sin conexión.
* En cada ejecución, verifica silenciosamente si hay una nueva versión oficial en la API de GitHub.
* Puedes forzar la verificación e instalación ejecutando `gitpr --update` o `gitpr -u`.
* La herramienta utiliza la técnica *Hot-Swap*, descargando el nuevo `.exe` y reemplazando la versión anterior de forma transparente.

## Publicación en PyPI

```bash
pipenv run python -m build
pipenv run twine upload dist/*
```
## **🤝 Cómo Contribuir**

¡Las contribuciones son muy bienvenidas! Para contribuir:

1. Haz un Fork del proyecto.
2. Crea una rama para tu *feature* (git checkout -b feature/NuevaFuncionalidad).
3. Confirma tus cambios (git commit -m 'feat: add new feature'). Consejo: ¡Usa GitPR para generar este mensaje! 😄
4. Sube a la rama (git push origin feature/NuevaFuncionalidad).
5. Abre un Pull Request.

## **✨ Agradecimientos y Autoría**

Proyecto concebido y desarrollado por:

**Natan Fiuza** - [contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br)

## **📄 Licencia**

Este proyecto está licenciado bajo la **GNU Lesser General Public License v2.1 (LGPL-2.1)**. Consulta el archivo LICENSE para más detalles.
