# Instalação e Configuração

Escolha o método que melhor se adapta ao seu fluxo de trabalho.

---

## ⚡ Início Rápido

### 1. Instalação via PyPI

Instale o GitPR CLI usando `pip`:

```bash
pip install gitpr-cli
```

### 2. Inicializando em um Repositório

Para configurar o GitPR na pasta de um novo repositório, execute:

```bash
gitpr --install
```

> **Setup Guiado:** Configuração interativa que baixa templates de skill, instala Git Hooks, configura MCP para seus editores e verifica a chave API do seu provedor de IA.  
> 📖 **Documentação Completa:** [Guia do Install Wizard](https://gitpr.natanfiuza.dev.br/docs/install-wizard?lang=pt_br)

---

## Windows: Usando o Executável

1. Baixe o `gitpr.exe` da página de [GitHub Releases](https://github.com/natafiuza/gitpr/releases)
2. Mova-o para um diretório no seu `PATH` (ex: sua pasta de usuário ou `C:\Windows\System32`)
3. Execute o setup guiado:

```bash
gitpr --install
```

O assistente irá guiá-lo através de:

```
🚀 Automação Inteligente de PR com IA

🔧 Assistente de Configuração Interativa

📥 Baixando templates de skill...
🪝 Instalando Git Hooks (pre-commit, prepare-commit-msg)...
🔌 Configurando MCP para editores detectados...
🔑 Verificando a chave API do seu provedor de IA...
```

Sua configuração é salva com segurança em `~/.gitpr/.env`.

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

## Compilando Seu Próprio Executável

Use **PyInstaller** para gerar um binário independente:

```bash
# Instale as dependências de desenvolvimento
pipenv install --dev

# Compile
pipenv run pyinstaller --noconfirm --onefile --icon=icon.ico --name gitpr run.py
```

O binário estará na pasta `dist/`:
- `gitpr` no Linux/macOS
- `gitpr.exe` no Windows

A flag `--onefile` empacota Python, todas as bibliotecas e dependências em um único executável.

---

## 🔒 Segurança

O GitPR usa **criptografia simétrica Fernet** para proteger suas chaves de API:

- Sua `GEMINI_API_KEY` é armazenada como um hash criptografado em `~/.gitpr/.env`
- Uma chave mestra de descriptografia é gerada automaticamente em `~/.gitpr/secret.key`
- **Nunca compartilhe seu arquivo `secret.key`**

---

## Referência de Configuração

Todas as configurações ficam em `~/.gitpr/.env`:

| Variável | Descrição | Padrão |
| --- | --- | --- |
| `GEMINI_API_KEY` | Sua chave de API do Google Gemini (criptografada) | — |
| `GEMINI_API_MODEL` | Versão do modelo Gemini | `gemini-pro-latest` |
| `DEEPSEEK_API_KEY` | Sua chave de API do DeepSeek (criptografada) | — |
| `DEEPSEEK_API_MODEL` | Versão do modelo DeepSeek | `deepseek-v4-pro` |
| `GITPR_PROVIDER` | Provedor de IA padrão | `gemini` |
| `GITPR_LANG` | Idioma da interface | detectado automaticamente |

---

[← Início](/index) &nbsp;|&nbsp; [Guia de Uso →](/uso)
