# GitPR CLI — Informe de Estado del Proyecto

---

## Visión General

**GitPR** es una herramienta CLI para automatización de flujo de trabajo Git impulsada por IA (Google Gemini / DeepSeek). Actúa como un asistente inteligente local que realiza Code Reviews, genera descripciones de Pull Request, crea mensajes de commit semánticos, audita deuda técnica e inyecta buenas prácticas en el flujo del desarrollador (enfoque **Shift Left**).

---

## Arquitectura y Bibliotecas Base

- **Lenguaje:** Python 3.x
- **Framework CLI:** Click (comandos, flags, formato de terminal)
- **UI/Terminal:** TUI interactiva (Textual) para chat y edición de issues
- **Cifrado:** Cifrado simétrico Fernet para protección local de claves API
- **Configuración:** dotenv, PyYAML (para el linter estático)
- **Proveedores IA:** SDK Google GenAI (gemini-2.5-flash) + DeepSeek + Ollama

---

## Módulos Implementados

### 1. Núcleo y Operaciones Git (`src/core.py`)
Comunicación estructurada con LLM solicitando respuestas estrictamente en JSON (`commit_message` y `pr_description`). Optimización nativa de Git con flags `-U1`, `-w`, `-M`, `-B` para diffs mínimos y enfocados.

### 2. Interfaz CLI y Configuración (`src/main.py`, `src/config.py`)
Detección de primera ejecución, configuración interactiva de claves API, configuración `.env` en `~/.gitpr/`. Enrutamiento de comandos para todos los flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`).

### 3. Motor de Análisis Estático (`src/linter_engine.py`)
Linter offline que analiza solo líneas añadidas (`+`) del `git diff`. Lee `.gitpr.linter.yml` con reglas regex, ignorando comentarios y con exclusión de rutas vía `fnmatch`.

### 4. Caja Fuerte de Seguridad (`src/security.py`)
Generación de clave Fernet (`secret.key`), funciones `encrypt_data` y `decrypt_data`. Las claves API nunca se almacenan en texto plano.

### 5. Auto-Updater (`src/updater.py`)
Actualización hot-swap del binario vía API GitHub Releases con verificación SHA-256 y capacidad de rollback.

### 6. Chat y Auto-Patch (`src/ui/chat_app.py`)
TUI interactiva con memoria de mensajes por rama. F5 extrae bloques de código a archivos de patch. F6 exporta sesiones a Markdown. Slash commands para acciones comunes.

### 7. Internacionalización (`src/i18n.py`)
Helper `__()` inspirado en Laravel con placeholders nombrados. Paquetes de traducción JSON en `~/.gitpr/langs/`. Fallback al inglés para claves faltantes. Soporta `en`, `pt_br`, `pt_pt`, `fr`, `es`.

### 8. Arquitectura Map-Reduce
Optimización en dos niveles para diffs grandes:
- **Nivel 1:** Flags nativos de Git (`-U1`, `-w`, `-M`, `-B`) para contexto mínimo
- **Nivel 2:** Estimación de tokens (`len() // 4`), división segura en límites `diff --git`, llamadas IA por lotes con `time.sleep(1)` para respetar rate limit, y etapa final Reduce concatenando resúmenes

---

## Métricas Clave

- **Proveedores IA:** 3 (Gemini, DeepSeek, Ollama)
- **Idiomas Soportados:** 5 (EN, PT-BR, PT-PT, FR, ES)
- **Comandos CLI:** Más de 12 flags
- **Linter:** Configurable por YAML, coste IA cero
- **Caché:** Basado en MD5, deduplicación automática
- **Seguridad:** Cifrado simétrico Fernet (AES-128-CBC)

---

## Documentación

La documentación completa está disponible en [github.com/natafiuza/gitpr](https://github.com/natafiuza/gitpr) y en este sitio.

---

[← Contribución](/contribuicao) &nbsp;|&nbsp; [Inicio →](/index)
