# Proveedores IA — Arquitectura Multi-Modelo

GitPR es **agnóstico de IA**: no está atado a un solo modelo o proveedor. Durante la configuración, elige tu motor por defecto. Cambia en cualquier momento con una sola flag.

---

## Proveedores Soportados

### Google Gemini

El proveedor por defecto y recomendado. Rápido, económico y con soporte nativo.

```bash
# Definir por defecto en ~/.gitpr/.env
GITPR_PROVIDER=gemini
GEMINI_API_MODEL=gemini-pro-latest

# O usar una vez
gitpr --provider gemini
```

**Modelo por defecto:** `gemini-pro-latest`

**Obtén tu clave API:** [Google AI Studio](https://aistudio.google.com/apikey)

---

### DeepSeek

Alternativa potente con excelente capacidad de análisis de código. Usa el SDK compatible con OpenAI.

```bash
# Definir por defecto en ~/.gitpr/.env
GITPR_PROVIDER=deepseek
DEEPSEEK_API_MODEL=deepseek-v4-pro

# O usar una vez
gitpr --provider deepseek
```

**Modelo por defecto:** `deepseek-v4-pro`

**Obtén tu clave API:** [DeepSeek Platform](https://platform.deepseek.com/api_keys)

---

### Ollama (Local)

Ejecuta modelos **totalmente offline** — sin internet, sin claves API, sin cuotas.

```bash
# Definir por defecto en ~/.gitpr/.env
GITPR_PROVIDER=ollama

# O usar una vez
gitpr --provider ollama
```

Ollama usa el formato de API compatible con OpenAI, haciendo la integración perfecta. Los modelos se ejecutan localmente en tu máquina con total privacidad.

**Comienza aquí:** [Ollama](https://ollama.com/)

---

## Comparación de Proveedores

| Característica | Gemini | DeepSeek | Ollama |
| --- | --- | --- | --- |
| **Requiere internet** | Sí | Sí | No |
| **Requiere clave API** | Sí | Sí | No |
| **Coste** | Nivel gratuito disponible | Pago | Gratuito |
| **Privacidad** | Nube | Nube | Totalmente local |
| **Velocidad** | Rápido | Rápido | Depende del hardware |
| **Ideal para** | Uso diario | Análisis profundo | Sin conexión/air-gapped |

---

## Personalizando Modelos

Sobrescribe la versión por defecto del modelo por proveedor:

```bash
# ~/.gitpr/.env
GEMINI_API_MODEL=gemini-2.5-pro     # Para análisis más pesados
DEEPSEEK_API_MODEL=deepseek-reasoner # Para razonamiento complejo
```

---

## Cómo Funciona

GitPR usa una **capa de abstracción de proveedor** que normaliza las llamadas API independientemente del motor subyacente. La herramienta:

1. Lee tu preferencia de proveedor desde `~/.gitpr/.env` (o la flag `--provider`)
2. Enruta el prompt al SDK correcto (Google GenAI, compatible con OpenAI, u Ollama local)
3. Parsea la respuesta en un formato unificado
4. Aplica caché, map-reduce y formato de salida de forma idéntica para todos los proveedores

Esto significa que cambiar de proveedor modifica **solo** el motor IA — todas las demás funcionalidades (linter, caché, i18n, skills) funcionan exactamente igual.

---

[← Guía del Linter](/linter) &nbsp;|&nbsp; [Sistema de Skills →](/skills)
