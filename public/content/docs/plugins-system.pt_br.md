# 🧩 Sistema de Plugins do GitPR

O sistema de plugins do GitPR permite estender as funcionalidades da ferramenta globalmente em **todos os seus projetos** sem duplicar arquivos de configuração.

## 📂 Estrutura de Diretórios

Os plugins ficam armazenados na pasta de configuração global do GitPR:

```
~/.gitpr/plugins/
├── linter/          # Pacotes de regras de linter globais (.yml / .yaml)
│   ├── security.yml
│   ├── no-debug.yml
│   └── php-psr.yml
└── prompts/         # Modelos de prompts de IA personalizados (.md)
    ├── audit_security.md
    ├── generate_tests.md
    └── explain_to_business.md
```

> **Dica:** Esses diretórios são criados automaticamente quando você executa qualquer comando do GitPR. Você também pode executar `gitpr --plugins` para verificar se eles existem e listar todos os plugins ativos.

---

## 🔍 Plugins de Linter (`plugins/linter/`)

### O que são

Plugins de linter são arquivos YAML contendo regras no mesmo formato do `.gitpr.linter.yml`, mas aplicadas **globalmente** — em todos os projetos da sua máquina.

### Diferença entre Local e Global

| Aspecto | Local (`.gitpr.linter.yml`) | Global (`~/.gitpr/plugins/linter/*.yml`) |
|--------|---------------------------|----------------------------------------|
| **Escopo** | Específico do projeto | Todos os projetos da máquina |
| **Versionamento** | Commitado com o projeto | Pessoal — não versionado por projeto |
| **Caso de uso** | Convenções da equipe para um repositório | Padrões pessoais, verificações de segurança |

### Como funciona

Quando o GitPR executa o linter (via `-l`, `-r`, `-f` ou hooks de pre-commit), ele:

1. Carrega as regras do `.gitpr.skill/.gitpr.linter.yml` local (se existir)
2. Percorre todos os arquivos `.yml` e `.yaml` em `~/.gitpr/plugins/linter/`
3. Mescla os dois conjuntos em uma única lista de regras
4. Executa as regras combinadas contra o diff

Se um plugin global tiver YAML inválido, o GitPR exibe um **aviso amarelo** e continua — seu fluxo de trabalho nunca é interrompido por um plugin quebrado.

### Exemplo: Pacote de Segurança

Crie `~/.gitpr/plugins/linter/security.yml`:

```yaml
rules:
  - name: "AWS Access Key leak"
    regex: "AKIA[0-9A-Z]{16}"
    severity: error
    message: "AWS Access Key ID found — this should never be committed."

  - name: "Generic password assignment"
    regex: "(?i)(password|passwd|senha)\\s*=\\s*['\"][^'\"]+['\"]"
    severity: warning
    message: "Hardcoded password detected. Use environment variables."
```

### Exemplo: Pacote No-Debug

Crie `~/.gitpr/plugins/linter/no-debug.yml`:

```yaml
rules:
  - name: "console.log left behind"
    regex: "console\\.log\\("
    severity: error
    extensions: [".js", ".ts", ".jsx", ".tsx"]
    message: "Remove console.log() before committing."

  - name: "var_dump left behind"
    regex: "var_dump\\("
    severity: error
    extensions: [".php"]
    message: "Remove var_dump() before committing."
```

---

## 💬 Plugins de Prompt (`plugins/prompts/`)

### O que são

Plugins de prompt são arquivos Markdown (`.md`) que definem prompts de IA personalizados. Cada arquivo fica disponível como:

- Um **recurso MCP** em `prompt://plugin/<filename>`
- Um **prompt MCP** chamado `Plugin: <filename>`

Isso permite que editores com IA (VS Code, Cursor, Claude Desktop, Zed) usem seus fluxos de trabalho personalizados.

### Como funciona

Na inicialização do servidor MCP (`gitpr --mcp`), o GitPR:

1. Varre `~/.gitpr/plugins/prompts/` em busca de arquivos `.md`
2. Registra cada um deles como recurso e prompt MCP
3. Lista-os junto com os prompts integrados em `prompt://list`

### Exemplo: Auditor de Segurança

Crie `~/.gitpr/plugins/prompts/audit_security.md`:

```markdown
You are a Senior Security Engineer. Perform a thorough security review of the current diff.

Focus on:
1. **Injection vulnerabilities** (SQL, NoSQL, Command, XPath)
2. **XSS / Cross-Site Scripting** vectors
3. **Sensitive data exposure** (keys, tokens, PII in logs)
4. **Authentication / Authorization** flaws
5. **Insecure deserialization**
6. **Path traversal** risks

For each finding, provide:
- **Severity**: Critical / High / Medium / Low
- **File & Line**: Where the issue is
- **Description**: What the vulnerability is
- **Fix**: Concrete code suggestion

Use the format:
### [Severity] Vulnerability Name
- **File**: path/to/file:line
- **Description**: ...
- **Fix**: ...
```

### Exemplo: Gerador de Testes PHPUnit

Crie `~/.gitpr/plugins/prompts/generate_tests.md`:

```markdown
You are a Senior PHP Developer specialized in Test-Driven Development.

For the code changes in this diff, generate comprehensive PHPUnit tests following these rules:

1. **100% coverage target** — cover all new/changed methods
2. **Follow PSR-12** coding standards
3. **Use data providers** for multiple input scenarios
4. **Mock external dependencies** (APIs, databases, file systems)
5. **Test edge cases**: null, empty, boundary values, exceptions

Output a ready-to-run PHPUnit test class with:
- Class name matching the source + "Test" suffix
- setUp() for shared fixtures
- test methods prefixed with "test"
- @test, @dataProvider, and @covers annotations
```

---

## 🖥️ CLI: Listando Plugins Ativos

Execute `gitpr --plugins` para ver todos os plugins instalados:

```
🧩 GitPR Plugin System

🔍 Linter Packs (2):
  - security.yml
  - no-debug.yml

💬 Custom Prompts (1):
  - audit_security.md

💡 Plugin directory: ~/.gitpr/plugins/
```

Use `gitpr -h --plugins` para obter ajuda contextual sobre o sistema de plugins.

---

## 🔄 Ordem de Execução e Precedência

| Camada | Prioridade | Comportamento de sobreposição |
|-------|----------|-------------------|
| `.gitpr.linter.yml` local | Carregado primeiro | — |
| Global `plugins/linter/*.yml` | Adicionado após o local | Mesmo nome de regra = ambas executam (sem deduplicação) |

As regras são **aditivas** — os plugins globais nunca substituem as regras locais; eles são adicionados ao lado delas.

---

## 🛡️ Tratamento de Erros

- **YAML global malformado** → Aviso amarelo, plugin ignorado. O fluxo principal continua.
- **Diretório de plugins inexistente** → Ignorado silenciosamente. Sem avisos.
- **Arquivo de plugin vazio** → Ignorado sem mensagem.
- **Inicialização do servidor MCP** → Falhas no registro de plugins são capturadas silenciosamente. O MCP inicializa normalmente.

---

## 📚 Veja Também

- [Regras de Linter Personalizadas](linter-regras-customizadas) — Como escrever regras no `.gitpr.linter.yml`
- [Skills e Modelos](skill-template) — Prompts e regras de IA locais do projeto
- [Integração MCP](mcp-integration) — Usando o GitPR com editores de IA
