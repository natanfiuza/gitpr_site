# Contributing to GitPR CLI

Contributions are very welcome! Here's how to get involved.

---

## How to Contribute

1. **Fork** the project on [GitHub](https://github.com/natafiuza/gitpr)
2. **Create a branch** for your feature:
   ```bash
   git checkout -b feature/NewFeature
   ```
3. **Commit your changes:**
   ```bash
   git commit -m 'feat: add new feature'
   ```
   > 💡 **Tip:** Use GitPR itself to generate this commit message! Just run `gitpr -c`.

4. **Push** to your branch:
   ```bash
   git push origin feature/NewFeature
   ```
5. **Open a Pull Request** on the main repository

---

## Development Setup

```bash
# Clone and enter
git clone https://github.com/natafiuza/gitpr.git
cd gitpr

# Install all dependencies (including dev)
pipenv install --dev

# Run tests
pipenv run pytest -v

# Run GitPR from source
pipenv run python src/main.py
```

---

## Running Tests

```bash
# Run all tests with verbose output
pipenv run pytest -v

# Run a specific test file
pipenv run pytest tests/test_core.py -v

# Run with coverage report
pipenv run pytest --cov=src --cov-report=term-missing
```

---

## Areas to Contribute

| Area | Description |
| --- | --- |
| **New Providers** | Add support for additional AI providers (Claude, local LLMs, etc.) |
| **New Languages** | Translate GitPR to your language |
| **Linter Rules** | Share useful linter rule sets for different stacks |
| **Documentation** | Improve docs, add examples, fix typos |
| **Bug Fixes** | Check the issues tab for reported bugs |
| **TUI Enhancements** | Improve the interactive chat and issue editor UI |

---

## Project Structure

```
gitpr/
├── src/
│   ├── main.py           # CLI entry point & command routing
│   ├── core.py            # Git operations & AI integration
│   ├── config.py          # Configuration & env management
│   ├── security.py        # Encryption (Fernet)
│   ├── linter_engine.py   # Static analysis engine
│   ├── updater.py         # Auto-updater (hot-swap)
│   └── i18n.py            # Internationalization helper
├── tests/                 # Unit & integration tests
├── langs/                 # Translation files (JSON)
├── docs/                  # Extended documentation
└── run.py                 # PyInstaller entry point
```

---

## Publishing to PyPI

For maintainers:

```bash
pipenv run python -m build
pipenv run twine upload dist/*
```

---

## License

This project is licensed under the **GNU Lesser General Public License v2.1 (LGPL-2.1)**.

See the [LICENSE](https://github.com/natafiuza/gitpr/blob/main/LICENSE) file for full details.

---

## Acknowledgments

Project conceived and developed by **Natan Fiuza** — [contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br)

---

[← Cache & Updates](/cache)
