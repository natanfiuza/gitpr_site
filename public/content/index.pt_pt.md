# GitPR CLI 🚀

**Automação de fluxo Git com IA** — Code Reviews, descrições de PR, commits semânticos e muito mais, diretamente do seu terminal.

O GitPR CLI utiliza o **Google Gemini** e o **DeepSeek** para analisar o seu `git diff` e ficheiros inteiros, gerando:
- Mensagens de commit no padrão **Conventional Commits**
- Descrições detalhadas de **Pull Request**
- **Code Reviews** aprofundados com foco na redução de dívida técnica
- Relatórios de **linting estático** sem consumir quotas de IA

---

## ⚡ Início Rápido

```bash
# Transfira do GitHub Releases e adicione ao seu PATH, depois:
gitpr
```

Na primeira execução, um assistente irá guiá-lo pela configuração — basta introduzir a sua chave de API e estará pronto.

---

## 🎯 Principais Funcionalidades

| Funcionalidade | Comando | Descrição |
| --- | --- | --- |
| **Geração de PR** | `gitpr` | Gera automaticamente descrições de pull request a partir do seu diff |
| **Mensagens de Commit** | `gitpr -c` | Mensagens semânticas no formato Conventional Commits |
| **Code Review** | `gitpr -r` | Revisão detalhada das alterações em stage |
| **Review Completo** | `gitpr -f` | Revisão completa contra o ramo remoto |
| **Auditoria de Ficheiro** | `gitpr -r -i <ficheiro>` | Análise completa de ficheiro, ideal para refatoração de código legado |
| **Chat Interativo** | `gitpr -ch` | TUI de pair-programming com memória, slash commands e auto-patch |
| **Linter Estático** | `gitpr -l` | Validação offline de regras — custo zero de IA, pronto para CI/CD |
| **Gerador de Issues** | `gitpr -is` | Gera issues estruturadas com 3 motores de contexto |
| **Git Hooks** | `gitpr -ih` | Instala hooks pre-commit e prepare-commit-msg localmente |
| **Auto-Update** | `gitpr -u` | Atualização hot-swap do binário via GitHub Releases |

---

## 🧠 Arquitetura Multi-Modelo

O GitPR é **agnóstico de IA** — escolha o seu motor:

- **Google Gemini** (padrão: `gemini-2.5-flash`)
- **DeepSeek** (padrão: `deepseek-chat`)
- **Ollama** — execute modelos locais sem internet

Alterne a qualquer momento com `--provider <gemini|deepseek|ollama>`.

---

## 🌐 Internacionalização

O GitPR deteta automaticamente o idioma do seu sistema. Atualmente suporta **PT-PT** e **EN**, com traduções transferidas automaticamente. Force um idioma com `--lang pt_pt` ou defina `GITPR_LANG` na sua configuração.

---

## 📦 Map-Reduce para Diffs Grandes

Quando o seu diff é demasiado grande para uma única chamada de IA (~90k tokens), o GitPR divide automaticamente por ficheiro, resume cada parte (**Map**) e unifica tudo (**Reduce**) — sem necessidade de flags.

---

## 🔒 Segurança

As suas chaves de API são encriptadas com **Fernet (encriptação simétrica)** e armazenadas em `~/.gitpr/`. Nunca partilhe o seu ficheiro `secret.key`.

---

[Guia de Instalação →](/instalacao) &nbsp;|&nbsp; [Guia de Utilização →](/uso) &nbsp;|&nbsp; [Repositório GitHub →](https://github.com/natafiuza/gitpr)
