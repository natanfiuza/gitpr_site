# Tecnologías y Bibliotecas

GitPR CLI está construido en **Python** y utiliza un conjunto cuidadosamente seleccionado de bibliotecas para ofrecer una experiencia rápida, segura y amigable.

---

## Bibliotecas Principales

### [Click](https://click.palletsprojects.com/)
Framework CLI robusto para construir interfaces de línea de comandos componibles y amigables. Impulsa todos los comandos, flags y formato de terminal de GitPR.

### [Google GenAI SDK](https://pypi.org/project/google-genai/)
SDK oficial para integración directa con la **API Gemini**. Usado para code reviews, mensajes de commit y descripciones de PR impulsados por IA.

### [OpenAI SDK](https://pypi.org/project/openai/)
Usado por su total compatibilidad con la **API DeepSeek** y **Ollama** (modelos locales). Permite la arquitectura multi-proveedor sin dependencia de vendor.

### [Textual](https://textual.textualize.io/)
Framework TUI potente para construir interfaces de terminal enriquecidas. Impulsa el **chat interactivo** (`--chat`), editor de issues y visualizador de diff en tiempo real.

---

## Seguridad y Configuración

### [Cryptography](https://cryptography.io/)
Proporciona **cifrado simétrico Fernet** para almacenar claves API de forma segura en disco. Tu `GEMINI_API_KEY` nunca se guarda en texto plano.

### [Python-dotenv](https://pypi.org/project/python-dotenv/)
Gestiona variables de entorno en el archivo de configuración `~/.gitpr/.env`, manteniendo organizados los ajustes de proveedores y preferencias de idioma.

### [PyYAML](https://pyyaml.org/)
Parsea las reglas de análisis estático definidas en `.gitpr.linter.yml`, permitiendo definiciones de reglas YAML legibles para el motor del linter.

---

## Pruebas y HTTP

### [Pytest](https://docs.pytest.org/)
Framework de pruebas moderno con salida colorida y legible en consola. Usado para pruebas unitarias y de integración en todos los módulos.

### [Requests](https://pypi.org/project/requests/)
Biblioteca HTTP elegante para comunicación con la API REST de GitHub — usada por el auto-updater, envío de issues y verificación de releases.

---

## ¿Por qué Python?

Python fue elegido por su **ciclo de desarrollo rápido**, **compatibilidad multiplataforma** (Windows, macOS, Linux), rico ecosistema de bibliotecas IA/LLM y la capacidad de compilar en un solo binario sin dependencias con **PyInstaller**.
