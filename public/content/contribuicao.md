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

[PyPI](https://pypi.org/project/gitpr-cli/) (Python Package Index) is the official package repository for Python — like npm for JavaScript or Packagist for PHP. Publishing to PyPI allows Linux and macOS users to install GitPR with a single command:

```bash
pip install gitpr-cli
```

### How Publishing Works

Only the project maintainer (**Natan Fiuza**) holds the credentials required to publish new versions. This is intentional — it ensures that only verified, reviewed code reaches end users through the official channel.

The two-step process:

| Command | What it does |
| --- | --- |
| `pipenv run python -m build` | Packages the source code into distributable `.tar.gz` and `.whl` files in the `dist/` folder |
| `pipenv run twine upload dist/*` | Uploads those packages to PyPI using the maintainer's authenticated token |

### For Contributors

You don't need PyPI access to contribute! Fork the repo, make your changes, and submit a Pull Request. Once merged, the maintainer will include your contribution in the next PyPI release.

> 📦 **PyPI project page:** [pypi.org/project/gitpr-cli](https://pypi.org/project/gitpr-cli/)

---

## License

This project is licensed under the **GNU Lesser General Public License v2.1 (LGPL-2.1)**.

See the [LICENSE](https://github.com/natafiuza/gitpr/blob/main/LICENSE) file for full details.

---

## Acknowledgments

### Creator & Maintainer

Project conceived and developed by **Natan Fiuza** — [contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br)

### Contributors

Thanks to everyone who has contributed to GitPR CLI:

::: collaborators
https://github.com/natanfiuza
:::

> 💡 **Want to appear here?** [Contribute to the project →](#how-to-contribute)

---

[← Cache & Updates](/cache)
