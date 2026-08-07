# Instalação e Configuração

Escolha o método que melhor se adapta ao seu fluxo de trabalho.

---

## ⚡ Início Rápido

### 1. Instalação via PyPI

Instale o GitPR CLI usando `pip`:

```bash
pip install gitpr-cli
```

### 2. Inicializando num Repositório

Para configurar o GitPR na pasta de um novo repositório, execute:

```bash
gitpr --install
```

> **Setup Guiado:** Configuração interativa que transfere templates de skill, instala Git Hooks, configura MCP para os seus editores e verifica a chave API do seu fornecedor de IA.  
> 📖 **Documentação Completa:** [Guia do Install Wizard](https://gitpr.natanfiuza.dev.br/docs/install-wizard?lang=pt_pt)

---

## Windows: Usando o Executável

1. Transfira o `gitpr.exe` da página de [GitHub Releases](https://github.com/natafiuza/gitpr/releases)
2. Mova-o para um diretório no seu `PATH` (ex: a sua pasta de utilizador ou `C:\Windows\System32`)
3. Execute o setup guiado:

```bash
gitpr --install
```

O assistente irá guiá-lo através de:

```
🚀 Automação Inteligente de PR com IA

🔧 Assistente de Configuração Interativa

📥 A transferir templates de skill...
🪝 A instalar Git Hooks (pre-commit, prepare-commit-msg)...
🔌 A configurar MCP para editores detetados...
🔑 A verificar a chave API do seu fornecedor de IA...
```

A sua configuração é guardada com segurança em `~/.gitpr/.env`.

---

## Linux / macOS: Via PyPI (Recomendado)

Instale diretamente do [PyPI](https://pypi.org/project/gitpr-cli/):

```bash
pip install gitpr-cli
```

Depois inicialize no seu repositório:

```bash
gitpr --install
```

O setup guiado irá orientá-lo pelos templates de skill, Git Hooks, configuração MCP e verificação da chave API.

---

## A Partir do Código Fonte

```bash
# 1. Clone o repositório
git clone https://github.com/natanfiuza/gitpr.git

# 2. Entre no diretório
cd gitpr

# 3. Instale as dependências
pipenv install google-genai openai python-dotenv click cryptography

# 4. Execute o setup guiado
pipenv run python src/main.py --install
```

---

## Compilando o Seu Próprio Executável

Use o **PyInstaller** para gerar um binário independente:

```bash
# Instale as dependências de desenvolvimento
pipenv install --dev

# Compile
pipenv run pyinstaller --noconfirm --onefile --icon=icon.ico --name gitpr run.py
```

O binário estará na pasta `dist/`:
- `gitpr` no Linux/macOS
- `gitpr.exe` no Windows

A flag `--onefile` empacota o Python, todas as bibliotecas e dependências num único executável.

---

## 🔒 Segurança

O GitPR usa **encriptação simétrica Fernet** para proteger as suas chaves de API:

- A sua `GEMINI_API_KEY` é armazenada como um hash encriptado em `~/.gitpr/.env`
- Uma chave mestra de desencriptação é gerada automaticamente em `~/.gitpr/secret.key`
- **Nunca partilhe o seu ficheiro `secret.key`**

---

## Referência de Configuração

Todas as definições ficam em `~/.gitpr/.env`:

| Variável | Descrição | Padrão |
| --- | --- | --- |
| `GEMINI_API_KEY` | A sua chave de API do Google Gemini (encriptada) | — |
| `GEMINI_API_MODEL` | Versão do modelo Gemini | `gemini-pro-latest` |
| `DEEPSEEK_API_KEY` | A sua chave de API do DeepSeek (encriptada) | — |
| `DEEPSEEK_API_MODEL` | Versão do modelo DeepSeek | `deepseek-v4-pro` |
| `GITPR_PROVIDER` | Fornecedor de IA padrão | `gemini` |
| `GITPR_LANG` | Idioma da interface | detetado automaticamente |

---

[← Início](/index) &nbsp;|&nbsp; [Guia de Utilização →](/uso)
