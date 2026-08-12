# GitPR CLI 🚀

<p align="center">
  <img src="https://raw.githubusercontent.com/natanfiuza/gitpr/main/docs/logo.png" alt="GitPR Logo" width="120">
</p>

**Automação de fluxo Git com IA** — Code Reviews, descrições de PR, commits semânticos e muito mais, direto do seu terminal.

O GitPR CLI usa **Google Gemini** e **DeepSeek** para analisar seu `git diff` e arquivos inteiros, gerando:
- Mensagens de commit no padrão **Conventional Commits**
- Descrições detalhadas de **Pull Request**
- **Code Reviews** profundos focados em reduzir dívida técnica
- Relatórios de **linting estático** sem consumir cotas de IA

---

## ⚡ Início Rápido

### 1. Instalação via PyPI

Instale o GitPR CLI usando o `pip`:

```bash
pip install gitpr-cli
```

### 2. Inicializando em um Repositório

Para configurar o GitPR em uma pasta de um novo repositório, execute:

```bash
gitpr --install
```

A configuração guiada baixa templates de skills, instala Git Hooks, configura o MCP para seus editores e verifica a chave de API do seu provedor de IA.

---

## 🎯 Principais Funcionalidades

| Funcionalidade | Comando | Descrição |
| --- | --- | --- |
| **Geração de PR** | `gitpr` | Gera automaticamente descrições de pull request a partir do seu diff |
| **Mensagens de Commit** | `gitpr -c` | Mensagens semânticas no formato Conventional Commits |
| **Code Review** | `gitpr -r` | Revisão detalhada das alterações staged |
| **Review Completo** | `gitpr -f` | Revisão completa contra a branch remota |
| **Auditoria de Arquivo** | `gitpr -r -i <arquivo>` | Análise completa de arquivo, ótimo para refatoração de código legado |
| **Chat Interativo** | `gitpr -ch` | TUI de pair-programming com memória, slash commands e auto-patch |
| **Linter Estático** | `gitpr -l` | Validação offline de regras — custo zero de IA, pronto para CI/CD |
| **Gerador de Issues** | `gitpr -is` | Gera issues estruturadas com 3 motores de contexto |
| **Git Hooks** | `gitpr -ih` | Instala hooks pre-commit e prepare-commit-msg localmente |
| **Arqueólogo de Código** | `gitpr -b` | Rastreia origem de regras de negócio via `git blame` com classificação IA |
| **Auto-Update** | `gitpr -u` | Atualização hot-swap do binário via GitHub Releases |

::: note Flags Técnicas Ocultas
- **`--hook <arquivo>`** — Usado internamente pelos Git Hooks para injetar mensagens de commit diretamente no arquivo temporário do Git.
- **`--pre-save`** — Flag de debug que salva o payload completo da IA (instrução do sistema + prompt) em um arquivo JSON antes de cada chamada. Combine com qualquer comando de IA (ex: `gitpr -c --pre-save`).
:::

---

## 🧠 Arquitetura Multi-Modelo

O GitPR é **agnóstico de IA** — escolha seu motor:

- **Google Gemini** (padrão: `gemini-2.5-flash`)
- **DeepSeek** (padrão: `deepseek-chat`)
- **Ollama** — execute modelos locais sem internet

Alterne a qualquer momento com `--provider <gemini|deepseek|ollama>`.

---

## 🌐 Internacionalização

O GitPR detecta automaticamente o idioma do seu sistema. Suporta **5 idiomas** (PT-BR, EN, PT-PT, ES, FR), com traduções baixadas automaticamente. Force um idioma com `--lang pt_br` ou defina `GITPR_LANG` na sua configuração.

---

## 📦 Map-Reduce para Diffs Grandes

Quando seu diff é grande demais para uma única chamada de IA (~90k tokens), o GitPR divide automaticamente por arquivo, resume cada parte (**Map**) e unifica tudo (**Reduce**) — sem necessidade de flags.

---

## 🔒 Segurança

Suas chaves de API são criptografadas com **Fernet (criptografia simétrica)** e armazenadas em `~/.gitpr/`. Nunca compartilhe seu arquivo `secret.key`.

---

[Guia de Instalação →](/instalacao) &nbsp;|&nbsp; [Guia de Uso →](/uso) &nbsp;|&nbsp; [Repositório GitHub →](https://github.com/natafiuza/gitpr)
