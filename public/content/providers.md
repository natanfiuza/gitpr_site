# AI Providers — Multi-Model Architecture

GitPR is **AI-agnostic**: it's not tied to a single model or vendor. During setup, choose your default engine. Switch anytime with a single flag.

---

## Supported Providers

### Google Gemini

The default and recommended provider. Fast, cost-effective, and natively supported.

```bash
# Set as default in ~/.gitpr/.env
GITPR_PROVIDER=gemini
GEMINI_API_MODEL=gemini-pro-latest

# Or use once
gitpr --provider gemini
```

**Default model:** `gemini-pro-latest`

**Get your API key:** [Google AI Studio](https://aistudio.google.com/apikey)

---

### DeepSeek

Powerful alternative with excellent code analysis capabilities. Uses the OpenAI-compatible SDK.

```bash
# Set as default in ~/.gitpr/.env
GITPR_PROVIDER=deepseek
DEEPSEEK_API_MODEL=deepseek-v4-pro

# Or use once
gitpr --provider deepseek
```

**Default model:** `deepseek-v4-pro`

**Get your API key:** [DeepSeek Platform](https://platform.deepseek.com/api_keys)

---

### Ollama (Local)

Run models **entirely offline** — no internet, no API keys, no quotas.

```bash
# Set as default in ~/.gitpr/.env
GITPR_PROVIDER=ollama

# Or use once
gitpr --provider ollama
```

Ollama uses the OpenAI-compatible API format, making integration seamless. Models run locally on your machine with full privacy.

**Get started:** [Ollama](https://ollama.com/)

---

## Provider Comparison

| Feature | Gemini | DeepSeek | Ollama |
| --- | --- | --- | --- |
| **Internet required** | Yes | Yes | No |
| **API key required** | Yes | Yes | No |
| **Cost** | Free tier available | Paid | Free |
| **Privacy** | Cloud | Cloud | Fully local |
| **Speed** | Fast | Fast | Depends on hardware |
| **Best for** | Daily use | Deep analysis | Offline/air-gapped |

---

## Customizing Models

Override the default model version per provider:

```bash
# ~/.gitpr/.env
GEMINI_API_MODEL=gemini-2.5-pro     # For heavier analysis
DEEPSEEK_API_MODEL=deepseek-reasoner # For complex reasoning
```

---

## How It Works

GitPR uses a **provider abstraction layer** that normalizes API calls regardless of the underlying engine. The tool:

1. Reads your provider preference from `~/.gitpr/.env` (or the `--provider` flag)
2. Routes the prompt to the correct SDK (Google GenAI, OpenAI-compatible, or Ollama local)
3. Parses the response into a unified format
4. Applies caching, map-reduce, and output formatting identically for all providers

This means switching providers changes **only** the AI engine — all other features (linter, cache, i18n, skills) work exactly the same.

---

[← Linter Guide](/linter) &nbsp;|&nbsp; [Skills System →](/skills)
