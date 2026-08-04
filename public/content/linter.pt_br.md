# Linter Local — Análise Estática

O linter do GitPR valida código contra regras personalizadas **sem consumir cotas de IA**. Ele analisa apenas as **linhas adicionadas** no seu `git diff`, tornando-o rápido, focado e pronto para CI/CD.

::: tip ⚡ Utilitário do Linter
Crie, teste e valide regras YAML graficamente no seu navegador: **[Abrir Construtor de Regras](/linter-utility?lang=pt_br)**
:::

---

## Início Rápido

```bash
# Gerar a configuração padrão do linter
gitpr -s

# Executar o linter standalone (sem IA)
gitpr -l
```

O linter também executa automaticamente como parte do `--review` e `--fullreview`, com violações destacadas no topo da saída da revisão.

---

## Configuração: `.gitpr.linter.yml`

Defina regras usando **Expressões Regulares**:

```yaml
rules:
  - name: "check-localhost"
    extensions: ["js", "php", "py"]
    regex: 'http(s)?://(localhost|127\.0\.0\.1)'
    message: "🚨 Uso de localhost detectado no arquivo {file_name}"
    ignore_comments: true
    ignore_paths:
      - "vendor/*"
      - "node_modules/*"
      - "tests/*"

  - name: "no-console-log"
    extensions: ["js", "ts"]
    regex: 'console\.log\('
    message: "🚨 console.log() encontrado em {file_name}:{line_number}"
    ignore_comments: false

  - name: "no-debugger"
    extensions: ["js", "ts"]
    regex: 'debugger'
    message: "🚨 declaração debugger encontrada em {file_name}:{line_number}"
    ignore_comments: true

  - name: "no-todo-without-ticket"
    extensions: ["*"]
    regex: 'TODO(?!\s*\(\w+-\d+\))'
    message: "📝 TODO sem referência de ticket em {file_name}:{line_number}"
    ignore_comments: false
```

---

## Campos da Regra

| Campo | Obrigatório | Descrição |
| --- | --- | --- |
| `name` | Sim | Identificador único da regra |
| `extensions` | Sim | Extensões de arquivo a verificar (`["*"]` para todos) |
| `regex` | Sim | Expressão regular para correspondência |
| `message` | Sim | Mensagem de violação. Suporta `{file_name}` e `{line_number}` |
| `ignore_comments` | Não | Pular linhas que estão comentadas (padrão: `false`) |
| `ignore_paths` | Não | Padrões glob para diretórios/arquivos a ignorar |

---

## Integração CI/CD

Execute o linter no seu pipeline para **bloquear merges** com violações:

### Exemplo GitHub Actions

```yaml
name: GitPR Linter
on: [pull_request]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - name: Executar GitPR Linter
        run: |
          gitpr --linter
```

---

## Hooks Pre-Commit

Instale automaticamente com:

```bash
gitpr --installhooks
```

Isso cria hooks `pre-commit` e `prepare-commit-msg` que executam o linter antes de cada commit, capturando problemas no momento mais precoce possível (abordagem **Shift-Left**).

---

## Por que um Linter Local?

- **Custo zero de IA** — sem chamadas de API, sem limites de taxa
- **Feedback instantâneo** — executa em milissegundos
- **Personalizável** — regras que correspondem aos padrões do SEU time
- **Consciente do Git** — verifica apenas o que você alterou, não toda a base de código
- **Nativo para CI/CD** — comando único, sem serviços externos

---

## 🛠️ Utilitário Interativo do Linter

::: tip ⚡ Construtor de Regras & Testador de Regex
Precisa de ajuda para criar suas regras ou testar suas expressões regulares de forma visual em tempo real?

<a href="/linter-utility?lang=pt_br" class="inline-block mt-3 px-5 py-2.5 bg-gitpr_primary text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 transition-colors no-underline">
  🚀 Abrir Utilitário Interativo do Linter →
</a>
:::

---

[← Guia de Uso](/uso) &nbsp;|&nbsp; [Provedores IA →](/providers)
