# GitPR CLI 🚀

**Automatización de flujo Git impulsada por IA** — Code Reviews, descripciones de PR, commits semánticos y más, directamente desde tu terminal.

GitPR CLI utiliza **Google Gemini** y **DeepSeek** para analizar tu `git diff` y archivos completos, generando:
- Mensajes de commit en el estándar **Conventional Commits**
- Descripciones detalladas de **Pull Request**
- **Code Reviews** profundos enfocados en reducir la deuda técnica
- Informes de **linting estático** sin consumir cuotas de IA

---

## ⚡ Inicio Rápido

```bash
# Descarga desde GitHub Releases y añade a tu PATH, luego:
gitpr
```

En la primera ejecución, un asistente te guiará en la configuración — solo ingresa tu clave API y estarás listo.

---

## 🎯 Funcionalidades Clave

| Funcionalidad | Comando | Descripción |
| --- | --- | --- |
| **Generación de PR** | `gitpr` | Genera automáticamente descripciones de pull request desde tu diff |
| **Mensajes de Commit** | `gitpr -c` | Mensajes semánticos en formato Conventional Commits |
| **Code Review** | `gitpr -r` | Revisión detallada de cambios staged |
| **Revisión Completa** | `gitpr -f` | Revisión completa contra la rama remota |
| **Auditoría de Archivo** | `gitpr -r -i <archivo>` | Análisis completo de archivo, ideal para refactorizar código legacy |
| **Chat Interactivo** | `gitpr -ch` | TUI de pair-programming con memoria, slash commands y auto-patch |
| **Linter Estático** | `gitpr -l` | Validación offline de reglas — coste IA cero, listo para CI/CD |
| **Generador de Issues** | `gitpr -is` | Genera issues estructuradas con 3 motores de contexto |
| **Git Hooks** | `gitpr -ih` | Instala hooks pre-commit y prepare-commit-msg localmente |
| **Arqueólogo de Código** | `gitpr -b` | Rastrea el origen de reglas de negocio vía `git blame` con clasificación IA |
| **Auto-Update** | `gitpr -u` | Actualización hot-swap del binario vía GitHub Releases |

::: note Flags Técnicas Ocultas
- **`--hook <archivo>`** — Usado internamente por los Git Hooks para inyectar mensajes de commit directamente en el archivo temporal de Git.
- **`--pre-save`** — Flag de debug que guarda el payload completo de la IA (instrucción del sistema + prompt) en un archivo JSON antes de cada llamada. Combina con cualquier comando de IA (ej: `gitpr -c --pre-save`).
:::

---

## 🧠 Arquitectura Multi-Modelo

GitPR es **agnóstico de IA** — elige tu motor:

- **Google Gemini** (por defecto: `gemini-2.5-flash`)
- **DeepSeek** (por defecto: `deepseek-chat`)
- **Ollama** — ejecuta modelos locales sin internet

Cambia en cualquier momento con `--provider <gemini|deepseek|ollama>`.

---

## 🌐 Internacionalización

GitPR detecta automáticamente el idioma de tu sistema. Actualmente soporta **ES** y **EN**, con traducciones descargadas automáticamente. Fuerza un idioma con `--lang es` o define `GITPR_LANG` en tu configuración.

---

## 📦 Map-Reduce para Diffs Grandes

Cuando tu diff es demasiado grande para una sola llamada IA (~90k tokens), GitPR lo divide automáticamente por archivo, resume cada parte (**Map**) y unifica todo (**Reduce**) — sin necesidad de flags.

---

## 🔒 Seguridad

Tus claves API se cifran con **Fernet (cifrado simétrico)** y se almacenan en `~/.gitpr/`. Nunca compartas tu archivo `secret.key`.

---

[Guía de Instalación →](/instalacao) &nbsp;|&nbsp; [Guía de Uso →](/uso) &nbsp;|&nbsp; [Repositorio GitHub →](https://github.com/natafiuza/gitpr)
