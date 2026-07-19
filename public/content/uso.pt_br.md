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

---

### 🏺 Arqueólogo de Código (Git Blame)

```bash
gitpr -b arquivo.py:10-20
# ou
gitpr --blame caminho/para/arquivo
```

Rastreia a **origem e evolução** de regras de negócio usando `git blame` com classificação por IA. Dois modos:

| Modo | Comando | Descrição |
| --- | --- | --- |
| **Direto** | `gitpr --blame arquivo.py:10-20` | Analisa um intervalo de linhas específico imediatamente |
| **Interativo** | `gitpr --blame arquivo.py` | Navega pelo arquivo e seleciona a região alvo em uma TUI |

A IA classifica cada commit relevante como **ORIGIN** (a regra de negócio foi introduzida) ou **REFACTORING** (transformação sem alteração de lógica).

> 💡 **Combine com `--issue`** para gerar issues estruturadas de dívida técnica a partir da análise de blame:
> ```bash
> gitpr -is --blame modulo_legado.py:45-120
> ```

---

### 🪝 Git Hooks

```bash
gitpr -ih
# ou
gitpr --installhooks
```

Instala hooks `pre-commit` e `prepare-commit-msg` no seu repositório para barreiras de qualidade automáticas.

::: note --hook `<arquivo>`
O GitPR possui uma flag oculta `--hook <arquivo>` acionada exclusivamente pelo sistema de Git Hooks em segundo plano. Ela permite que a IA injete a mensagem de commit sugerida diretamente no arquivo temporário do Git, sem poluir seu terminal.
:::

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

::: note --pre-save (Debug)
O GitPR possui uma flag oculta de debug `--pre-save` que pode ser combinada com qualquer comando de IA (ex: `gitpr -c --pre-save`). Antes de cada chamada à IA, ela salva o **payload completo** enviado ao modelo — instrução do sistema + prompt + contadores de caracteres — em um arquivo `_{action}-{datetime}.json` na pasta atual. Útil para inspecionar prompts muito grandes. Quando a resposta vem do cache local, nenhuma chamada é feita e nenhum arquivo é gerado.
:::

---

[← Instalação](/instalacao) &nbsp;|&nbsp; [Guia do Linter →](/linter)
