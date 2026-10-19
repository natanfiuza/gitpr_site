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

## Via PyPI (Recomendado)

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
| `GEMINI_API_MODEL_PRIMARY` | Versão do modelo Gemini | `gemini-pro-latest` |
| `DEEPSEEK_API_KEY` | Sua chave de API do DeepSeek (criptografada) | — |
| `DEEPSEEK_API_MODEL_PRIMARY` | Versão do modelo DeepSeek | `deepseek-v4-pro` |
| `GITPR_PROVIDER` | Provedor de IA padrão | `gemini` |
| `GITPR_LANG` | Idioma da interface | detectado automaticamente |

---

[← Início](/index) &nbsp;|&nbsp; [Guia de Uso →](/uso)
