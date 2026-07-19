# Como Usar o GitPR CLI

O GitPR tem um comportamento padrão poderoso e opções avançadas para cada etapa do seu fluxo de trabalho Git.

---

## Comportamento Padrão: Geração de PR

Simplesmente execute:

```bash
gitpr
```

A ferramenta irá:
1. Sincronizar com o remote (`git fetch`)
2. Comparar suas alterações com `origin/main`
3. Gerar um arquivo Markdown (ex: `feature-login_20260421110134_PR_DESC.md`) com a descrição completa do Pull Request

---

## Comandos e Flags

### 🔖 Mensagem de Commit

```bash
gitpr -c
# ou
gitpr --commit
```

Executa `git diff` e exibe uma mensagem no formato **Conventional Commits**. Ótimo para commits rápidos e padronizados.

---

### 🔍 Code Review (Alterações Staged)

```bash
gitpr -r
# ou
gitpr --review
```

Revisão detalhada com IA das suas alterações locais staged. Foca em bugs, segurança, performance e qualidade de código.

---

### 🔎 Code Review Completo

```bash
gitpr -f
# ou
gitpr --fullreview
```

Revisão completa analisando **todas as alterações desde a branch remota**. Ideal para revisões abrangentes de PR.

---

### 📄 Auditoria de Arquivo Completo

```bash
gitpr -r -i src/modulo_legado.py
# ou
gitpr --review --input caminho/para/arquivo
```

Ignora o histórico git e audita o **arquivo inteiro**. Excelente para consultoria de refatoração de código legado. Deve ser usado com `-r` ou `-f`.

---

### 💬 Chat Interativo (Pair Programming)

```bash
gitpr -ch
# ou
gitpr --chat
```

Abre um **terminal TUI** onde a IA vê seu diff atual e mantém conversa contextual:

| Atalho | Ação |
| --- | --- |
| `F2` | Atualizar contexto do diff |
| `F5` | Extrair blocos de código para arquivo de patch |
| `F6` | Exportar sessão para Markdown |
| `/explain` | Explicar o diff atual |
| `/tests` | Gerar testes unitários |
| `/optimize` | Sugerir otimizações |
| `/clear` | Limpar memória da conversa |

A memória é **por branch**, então trocar de branch lhe dá um contexto limpo.

---

### 🛡️ Linter Estático

```bash
gitpr -l
# ou
gitpr --linter
```

Executa **apenas o linter estático local** — custo zero de IA. Valida as linhas alteradas contra as regras em `.gitpr.linter.yml`. Perfeito para pipelines CI/CD e hooks pre-commit.

---

### 🎫 Gerador de Issues

```bash
gitpr -is
# ou
gitpr --issue
```

Abre um **painel TUI** interativo para editar e enviar issues estruturadas. **3 motores de contexto**:

| Comando | Contexto | Caso de Uso |
| --- | --- | --- |
| `gitpr -is` | `git diff` atual | Documentar uma tarefa que você acabou de programar |
| `gitpr -is -ht` | Histórico completo da branch | Gerar documentação de release/epic |
| `gitpr -is -b arquivo:linhas` | Linha do tempo via `git blame` | Documentar evolução de código legado e dívida técnica |

---

### 🪝 Git Hooks

```bash
gitpr -ih
# ou
gitpr --installhooks
```

Instala hooks `pre-commit` e `prepare-commit-msg` no seu repositório para barreiras de qualidade automáticas.

---

### 🎨 Templates de Skills

```bash
gitpr -s
# ou
gitpr --skill
```

Gera templates personalizáveis de prompt da IA (arquivos `.gitpr.*.md`) e regras do linter (`.gitpr.linter.yml`) na raiz do seu projeto.

---

### 🌐 Override de Idioma e Provedor

```bash
# Forçar idioma para esta execução
gitpr --lang pt_br

# Alternar provedor de IA na hora
gitpr --provider deepseek
gitpr --provider ollama
```

---

### 🔄 Auto-Updater

```bash
gitpr -u
# ou
gitpr --update
```

Verifica no GitHub Releases a versão mais recente e faz hot-swap do binário.

---

### ❓ Ajuda

```bash
gitpr -h              # Ajuda geral
gitpr -h --issue      # Ajuda contextual para o comando issue
gitpr -h --linter     # Ajuda contextual para o comando linter
```

---

[← Instalação](/instalacao) &nbsp;|&nbsp; [Guia do Linter →](/linter)
