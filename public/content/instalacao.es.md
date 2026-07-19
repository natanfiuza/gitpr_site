# Instalación y Configuración

Elige el método que mejor se adapte a tu flujo de trabajo.

---

## Windows: Usando el Ejecutable

1. Descarga `gitpr.exe` desde la página de [GitHub Releases](https://github.com/natafiuza/gitpr/releases)
2. Muévelo a un directorio en tu `PATH` (ej: tu carpeta de usuario o `C:\Windows\System32`)
3. Ejecuta:

```bash
gitpr
```

En la primera ejecución, el asistente de configuración te guiará:

```
🚀 Automatización Inteligente de PR con IA

🔧 ¡Primera ejecución detectada! Vamos a configurar GitPR CLI.

🔑 Ingresa tu GEMINI_API_KEY:

📄 Patrón de nombre de archivo de salida [{branch}_{datetime}_PR_DESC.md]:
```

Tu configuración se guarda de forma segura en `~/.gitpr/.env`.

---

## Linux / macOS: Vía PyPI (Recomendado)

Instala directamente desde [PyPI](https://pypi.org/project/gitpr-cli/):

```bash
pip install gitpr-cli
```

Luego ejecuta:

```bash
gitpr
```

En la primera ejecución, el asistente te guiará en la configuración de la clave API.

---

## Desde el Código Fuente

```bash
# 1. Clona el repositorio
git clone https://github.com/natanfiuza/gitpr.git

# 2. Entra al directorio
cd gitpr

# 3. Instala las dependencias
pipenv install google-genai openai python-dotenv click cryptography

# 4. Ejecuta
pipenv run python src/main.py
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
