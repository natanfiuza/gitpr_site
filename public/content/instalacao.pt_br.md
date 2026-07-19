# Instalação e Configuração

Escolha o método que melhor se adapta ao seu fluxo de trabalho.

---

## Windows: Usando o Executável

1. Baixe o `gitpr.exe` da página de [GitHub Releases](https://github.com/natafiuza/gitpr/releases)
2. Mova-o para um diretório no seu `PATH` (ex: sua pasta de usuário ou `C:\Windows\System32`)
3. Execute:

```bash
gitpr
```

Na primeira execução, o assistente de configuração irá guiá-lo:

```
🚀 Automação Inteligente de PR com IA

🔧 Primeira execução detectada! Vamos configurar o GitPR CLI.

🔑 Digite sua GEMINI_API_KEY:

📄 Padrão de nome do arquivo de saída [{branch}_{datetime}_PR_DESC.md]:
```

Sua configuração é salva com segurança em `~/.gitpr/.env`.

---

## Linux / macOS: Via PyPI (Recomendado)

Instale diretamente do [PyPI](https://pypi.org/project/gitpr-cli/):

```bash
pip install gitpr-cli
```

Depois execute:

```bash
gitpr
```

Na primeira execução, o assistente irá guiá-lo pela configuração da chave API.

---

## A Partir do Código Fonte

```bash
# 1. Clone o repositório
git clone https://github.com/natanfiuza/gitpr.git

# 2. Entre no diretório
cd gitpr

# 3. Instale as dependências
pipenv install google-genai openai python-dotenv click cryptography

# 4. Execute
pipenv run python src/main.py
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
