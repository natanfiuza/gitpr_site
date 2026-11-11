# Tutorial de automação de navegador no Claude Code — 2026-11-11 (branch `develop_natan`)

Criação de um tutorial de referência sobre instalação e uso de automação de navegador
no Claude Code, a partir do conhecimento levantado na sessão que instalou o
Playwright MCP nesta máquina.

## Resumo

| Item | Resultado |
| --- | --- |
| Pasta nova | `docs/tutorial/` |
| Arquivo criado | `docs/tutorial/automacao_navegador_claude_code.md` (279 linhas) |
| Arquivo movido | `docs/tutorial_newsletter.md` → `docs/tutorial/tutorial_newsletter.md` |
| Arquivo alterado | `CLAUDE.md` (+1 linha no bloco Project Structure) |
| Opções cobertas | Playwright MCP, Chrome DevTools MCP, Playwright como devDependency |
| Plataformas | Windows (PowerShell) e macOS/Linux |
| Idioma | português |

## Contexto

A sessão anterior instalou o Playwright MCP para fechar a verificação visual do
`DocsLayout`. No caminho apareceram três pegadinhas que custaram diagnóstico:

1. o `npx` não sobe como servidor MCP no Windows sem `cmd /c`;
2. `claude mcp list` executado no Git Bash não lista o servidor, por divergência de
   maiúscula/minúscula na letra do drive (`C:` na configuração × `c:` no cwd);
3. o servidor grava snapshots em `.playwright-mcp/`, que precisa de regra no `.gitignore`.

Esse conhecimento estava preso num relatório datado — registro histórico, não material
consultável. O tutorial consolida o processo do zero.

## Decisões

| Decisão | Escolha |
| --- | --- |
| Escopo | as três opções de automação |
| Plataformas | Windows **e** macOS/Linux |
| Idioma | português |
| Nome do arquivo | `docs/tutorial/automacao_navegador_claude_code.md` |
| Tutorial existente | mover para dentro de `docs/tutorial/` (decisão do autor) |
| Profundidade | guia completo |
| Ponteiro | linha em `CLAUDE.md` |

## Mudanças

### 1. `git mv docs/tutorial_newsletter.md docs/tutorial/tutorial_newsletter.md`

A pasta não existia; foi criada antes do `git mv` (que falha se o destino não existir).
O `git status` registra o rename (`R`).

Não foram reescritos os relatórios históricos que citam o caminho antigo
(`2026-08-16_*`, `docs/survey/20260907_grill_sync_docs_surveyfacts.md`): são registros
datados e alterá-los falsearia o histórico.

### 2. `docs/tutorial/automacao_navegador_claude_code.md`

Segue as convenções de `docs/`: sem front-matter, metadados em negrito após o H1,
seções `## N.` numeradas.

Estrutura:

| Seção | Conteúdo |
| --- | --- |
| 1 | O que é MCP e o que muda na prática |
| 2 | Tabela comparativa das três opções |
| 3 | Playwright MCP: pré-requisitos, instalação Windows/macOS/Linux, escopos, validação, as 25 ferramentas por categoria |
| 4 | Chrome DevTools MCP: comando, requisito de Node, quando preferir |
| 5 | Playwright como devDependency: instalação + script de screenshot multi-viewport |
| 6 | Exemplos práticos: medir geometria, screenshot por viewport, tema escuro, sobreposição/rolagem |
| 7 | Solução de problemas: tabela sintoma × causa × correção (5 entradas) |
| 8 | Referência rápida: bloco único de comandos |
| 9 | Notas de campo: o que foi verificado nesta máquina |

Desvio do plano: a estimativa era 150–200 linhas; o resultado tem 279. A opção
"guia completo" com três ferramentas, exemplos e troubleshooting não cabia na
estimativa original. Ainda abaixo das especificações do repo (452–620 linhas).

### 3. `CLAUDE.md`

Linha adicionada ao bloco Project Structure, após `docs/reports/`:

```
docs/tutorial/           # Tutoriais internos (setup de ferramentas, fluxos)
```

## Verificação

| Checagem | Resultado |
| --- | --- |
| `npx chrome-devtools-mcp@latest --version` | `1.12.0` |
| `npx @playwright/mcp@latest --version` | `Version 0.0.85` |
| `@playwright/test` exporta `chromium` | confirmado empiricamente (`typeof chromium === 'object'`) em instalação isolada em `/tmp` |
| Cercas de código | 28 (par — todas fechadas) |
| Integridade das tabelas | contagem de pipes constante dentro de cada tabela (4, 4, 2, 2, 3, 2) |
| Referências internas no tutorial movido | nenhuma |
| `git status` | `R` no rename, `??` no arquivo novo, `M` no `CLAUDE.md` |

Os comandos de macOS/Linux são a variante sem `cmd /c` — não executados nesta máquina
(Windows). O tutorial declara isso explicitamente na seção 9.

## Arquivos

| Arquivo | Alteração |
| --- | --- |
| `docs/tutorial/automacao_navegador_claude_code.md` | criado |
| `docs/tutorial/tutorial_newsletter.md` | movido de `docs/tutorial_newsletter.md` |
| `CLAUDE.md` | +1 linha no bloco Project Structure |
