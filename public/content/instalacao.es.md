# Instalación y Configuración

Elige el método que mejor se adapte a tu flujo de trabajo.

---

## ⚡ Inicio Rápido

### 1. Instalación vía PyPI

Instala GitPR CLI usando `pip`:

```bash
pip install gitpr-cli
```

### 2. Inicializando en un Repositorio

Para configurar GitPR en la carpeta de un nuevo repositorio, ejecuta:

```bash
gitpr --install
```

> **Setup Guiado:** Configuración interactiva que descarga plantillas de skill, instala Git Hooks, configura MCP para tus editores y verifica la clave API de tu proveedor IA.  
> 📖 **Documentación Completa:** [Guía del Install Wizard](https://gitpr.natanfiuza.dev.br/docs/install-wizard?lang=es_es)

---

## Windows: Usando el Ejecutable

1. Descarga `gitpr.exe` desde la página de [GitHub Releases](https://github.com/natafiuza/gitpr/releases)
2. Muévelo a un directorio en tu `PATH` (ej: tu carpeta de usuario o `C:\Windows\System32`)
3. Ejecuta el setup guiado:

```bash
gitpr --install
```

El asistente te guiará a través de:

```
🚀 Automatización Inteligente de PR con IA

🔧 Asistente de Configuración Interactiva

📥 Descargando plantillas de skill...
🪝 Instalando Git Hooks (pre-commit, prepare-commit-msg)...
🔌 Configurando MCP para editores detectados...
🔑 Verificando la clave API de tu proveedor IA...
```

Tu configuración se guarda de forma segura en `~/.gitpr/.env`.

---

## Linux / macOS: Vía PyPI (Recomendado)

Instala directamente desde [PyPI](https://pypi.org/project/gitpr-cli/):

```bash
pip install gitpr-cli
```

Luego inicializa en tu repositorio:

```bash
gitpr --install
```

El setup guiado te orientará por las plantillas de skill, Git Hooks, configuración MCP y verificación de la clave API.

---

## Desde el Código Fuente

```bash
# 1. Clona el repositorio
git clone https://github.com/natanfiuza/gitpr.git

# 2. Entra al directorio
cd gitpr

# 3. Instala las dependencias
pipenv install google-genai openai python-dotenv click cryptography

# 4. Ejecuta el setup guiado
pipenv run python src/main.py --install
```

---

## Compilando Tu Propio Ejecutable

Usa **PyInstaller** para generar un binario independiente:

```bash
# Instala las dependencias de desarrollo
pipenv install --dev

# Compila
pipenv run pyinstaller --noconfirm --onefile --icon=icon.ico --name gitpr run.py
```

El binario estará en la carpeta `dist/`:
- `gitpr` en Linux/macOS
- `gitpr.exe` en Windows

La flag `--onefile` empaqueta Python, todas las bibliotecas y dependencias en un solo ejecutable.

---

## 🔒 Seguridad

GitPR usa **cifrado simétrico Fernet** para proteger tus claves API:

- Tu `GEMINI_API_KEY` se almacena como un hash cifrado en `~/.gitpr/.env`
- Una clave maestra de descifrado se genera automáticamente en `~/.gitpr/secret.key`
- **Nunca compartas tu archivo `secret.key`**

---

## Referencia de Configuración

Todas las configuraciones se encuentran en `~/.gitpr/.env`:

| Variable | Descripción | Por defecto |
| --- | --- | --- |
| `GEMINI_API_KEY` | Tu clave API de Google Gemini (cifrada) | — |
| `GEMINI_API_MODEL` | Versión del modelo Gemini | `gemini-pro-latest` |
| `DEEPSEEK_API_KEY` | Tu clave API de DeepSeek (cifrada) | — |
| `DEEPSEEK_API_MODEL` | Versión del modelo DeepSeek | `deepseek-v4-pro` |
| `GITPR_PROVIDER` | Proveedor IA por defecto | `gemini` |
| `GITPR_LANG` | Idioma de la interfaz | detectado automáticamente |

---

[← Inicio](/index) &nbsp;|&nbsp; [Guía de Uso →](/uso)
