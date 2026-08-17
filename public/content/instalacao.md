# Installation & Configuration

Choose the method that best fits your workflow.

---

## ⚡ Quick Start

### 1. Installation via PyPI

Install GitPR CLI using `pip`:

```bash
pip install gitpr-cli
```

### 2. Initializing in a Repository

To set up GitPR in a folder of a new repository, run:

```bash
gitpr --install
```

> **Guided Setup:** Interactive configuration that downloads skill templates, installs Git Hooks, configures MCP for your editors, and verifies the API key for your AI provider.  
> 📖 **Full Documentation:** [Install Wizard Guide](https://gitpr.natanfiuza.dev.br/docs/install-wizard?lang=en_us)

---

## Windows: Using the Executable

1. Download `gitpr.exe` from the [GitHub Releases](https://github.com/natanfiuza/gitpr/releases) page
2. Move it to a directory in your `PATH` (e.g., your user folder or `C:\Windows\System32`)
3. Run the guided setup:

```bash
gitpr --install
```

The wizard will guide you through:

```
🚀 Intelligent PR Automation with AI

🔧 Interactive Setup Wizard

📥 Downloading skill templates...
🪝 Installing Git Hooks (pre-commit, prepare-commit-msg)...
🔌 Configuring MCP for detected editors...
🔑 Verifying your AI provider API key...
```

Your configuration is securely saved to `~/.gitpr/.env`.

---

## Linux / macOS: Via PyPI (Recommended)

Install directly from [PyPI](https://pypi.org/project/gitpr-cli/):

```bash
pip install gitpr-cli
```

Then initialize in your repository:

```bash
gitpr --install
```

The guided setup will walk you through skill templates, Git Hooks, MCP configuration, and API key verification.

---

## From Source Code

```bash
# 1. Clone the repository
git clone https://github.com/natanfiuza/gitpr.git

# 2. Enter the directory
cd gitpr

# 3. Install dependencies
pipenv install google-genai openai python-dotenv click cryptography

# 4. Run the guided setup
pipenv run python src/main.py --install
```

---

## Compiling Your Own Executable

Use **PyInstaller** to generate a standalone binary:

```bash
# Install dev dependencies
pipenv install --dev

# Build
pipenv run pyinstaller --noconfirm --onefile --icon=icon.ico --name gitpr run.py
```

The binary will be in the `dist/` folder:
- `gitpr` on Linux/macOS
- `gitpr.exe` on Windows

The `--onefile` flag bundles Python, all libraries, and dependencies into a single executable.

---

## 🔒 Security

GitPR uses **Fernet symmetric encryption** to protect your API keys:

- Your `GEMINI_API_KEY` is stored as an encrypted hash in `~/.gitpr/.env`
- A master decryption key is auto-generated at `~/.gitpr/secret.key`
- **Never share your `secret.key` file**

---

## Configuration Reference

All settings live in `~/.gitpr/.env`:

| Variable | Description | Default |
| --- | --- | --- |
| `GEMINI_API_KEY` | Your Google Gemini API key (encrypted) | — |
| `GEMINI_API_MODEL_PRIMARY` | Gemini model version | `gemini-pro-latest` |
| `DEEPSEEK_API_KEY` | Your DeepSeek API key (encrypted) | — |
| `DEEPSEEK_API_MODEL_PRIMARY` | DeepSeek model version | `deepseek-v4-pro` |
| `GITPR_PROVIDER` | Default AI provider | `gemini` |
| `GITPR_LANG` | Interface language | auto-detected |

---

[← Home](/index) &nbsp;|&nbsp; [Usage Guide →](/uso)
