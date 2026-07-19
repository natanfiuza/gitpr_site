# Technologies & Libraries

GitPR CLI is built with **Python** and leverages a carefully selected set of libraries to deliver a fast, secure, and user-friendly experience.

---

## Core Libraries

### [Click](https://click.palletsprojects.com/)
Robust CLI framework for building composable, user-friendly command-line interfaces. Powers all GitPR commands, flags, and terminal formatting.

### [Google GenAI SDK](https://pypi.org/project/google-genai/)
Official SDK for direct integration with the **Gemini API**. Used for AI-powered code reviews, commit messages, and PR descriptions.

### [OpenAI SDK](https://pypi.org/project/openai/)
Used for its full compatibility with the **DeepSeek API** and **Ollama** (local models). Enables the multi-provider architecture without vendor lock-in.

### [Textual](https://textual.textualize.io/)
Powerful TUI framework for building rich terminal interfaces. Drives the **interactive chat** (`--chat`), issue editor, and real-time diff viewer.

---

## Security & Config

### [Cryptography](https://cryptography.io/)
Provides **Fernet symmetric encryption** to securely store API keys on disk. Your `GEMINI_API_KEY` is never saved in plain text.

### [Python-dotenv](https://pypi.org/project/python-dotenv/)
Manages environment variables across the `~/.gitpr/.env` configuration file, keeping provider settings and language preferences organized.

### [PyYAML](https://pyyaml.org/)
Parses the custom static analysis rules defined in `.gitpr.linter.yml`, enabling human-readable YAML rule definitions for the linter engine.

---

## Testing & HTTP

### [Pytest](https://docs.pytest.org/)
Modern testing framework with colorful, readable console output. Used for unit and integration tests across all modules.

### [Requests](https://pypi.org/project/requests/)
Elegant HTTP library for GitHub REST API communication — used by the auto-updater, issue submission, and release checks.

---

## Why Python?

Python was chosen for its **rapid development cycle**, **cross-platform compatibility** (Windows, macOS, Linux), rich ecosystem of AI/LLM libraries, and the ability to compile into a single zero-dependency binary with **PyInstaller**.
