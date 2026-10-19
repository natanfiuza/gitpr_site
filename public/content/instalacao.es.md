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

## Vía PyPI (Recomendado)

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
| `GEMINI_API_MODEL_PRIMARY` | Versión del modelo Gemini | `gemini-pro-latest` |
| `DEEPSEEK_API_KEY` | Tu clave API de DeepSeek (cifrada) | — |
| `DEEPSEEK_API_MODEL_PRIMARY` | Versión del modelo DeepSeek | `deepseek-v4-pro` |
| `GITPR_PROVIDER` | Proveedor IA por defecto | `gemini` |
| `GITPR_LANG` | Idioma de la interfaz | detectado automáticamente |

---

[← Inicio](/index) &nbsp;|&nbsp; [Guía de Uso →](/uso)
