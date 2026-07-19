# Installation & Configuration

Choose the method that best fits your workflow.

---

## Windows: Using the Executable

1. Download `gitpr.exe` from the [GitHub Releases](https://github.com/natafiuza/gitpr/releases) page
2. Move it to a directory in your `PATH` (e.g., your user folder or `C:\Windows\System32`)
3. Run it:

```bash
gitpr
```

On first run, the setup wizard will guide you:

```
🚀 Intelligent PR Automation with AI

🔧 First run detected! Let's configure GitPR CLI.

🔑 Enter your GEMINI_API_KEY:

📄 Default output filename pattern [{branch}_{datetime}_PR_DESC.md]:
```

Your configuration is securely saved to `~/.gitpr/.env`.

---

## Linux / macOS: Via PyPI (Recommended)

Install directly from [PyPI](https://pypi.org/project/gitpr-cli/):

```bash
pip install gitpr-cli
```

Then run:

```bash
gitpr
```

On first run, the setup wizard will guide you through API key configuration.

---

## From Source Code

```bash
# 1. Clone the repository
git clone https://github.com/natanfiuza/gitpr.git

# 2. Enter the directory
cd gitpr

# 3. Install dependencies
pipenv install google-genai openai python-dotenv click cryptography

# 4. Run
pipenv run python src/main.py
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
| `GEMINI_API_MODEL` | Gemini model version | `gemini-2.5-flash` |
| `DEEPSEEK_API_KEY` | Your DeepSeek API key (encrypted) | — |
| `DEEPSEEK_API_MODEL` | DeepSeek model version | `deepseek-chat` |
| `GITPR_PROVIDER` | Default AI provider | `gemini` |
| `GITPR_LANG` | Interface language | auto-detected |

---

[← Home](/index) &nbsp;|&nbsp; [Usage Guide →](/uso)
