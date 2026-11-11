# Survey — tutorial de automação de navegador no Claude Code

- **Data:** 2026-11-11
- **Branch:** `develop_natan`
- **Origem:** sessão `/grill-with-docs`
- **Task:** `tutorial_automacao_navegador`

## 1. Pedido original

> "Crie um tutorial de como instalar a ferramenta browser no Claude Code e salve em docs/tutorial"

## 2. Contexto da tarefa

Na sessão anterior (2026-11-10/11) o Playwright MCP foi instalado nesta máquina para
verificar o alinhamento do `DocsLayout`. Três obstáculos foram encontrados e resolvidos,
mas só ficaram registrados no relatório daquela tarefa:

| Obstáculo | Detalhe |
| --- | --- |
| `npx` como servidor MCP no Windows | precisa de `cmd /c`; sem isso o spawn falha |
| `claude mcp list` no Git Bash | não lista o servidor: chave gravada como `C:/...`, cwd resolvido como `c:/...` |
| Artefatos do servidor | grava snapshots/screenshots em `.playwright-mcp/` → regra no `.gitignore` |

## 3. Levantamento de fatos

### Estrutura de `docs/`

`docs/tutorial/` **não existia**. Havia `docs/tutorial_newsletter.md` na raiz de `docs/`
(155 linhas). Demais pastas: `claude-code/{prompts,reports}`, `gemini/{plans,reports}`,
`plans/`, `reports/`, `specs/`, `survey/`.

### Convenções

| Item | Convenção |
| --- | --- |
| Front-matter YAML | **nenhuma** nos 126 arquivos de `docs/` |
| Metadados | linhas em **negrito** logo após o H1 |
| Estrutura | H1 + `## N.` numerados + `###` |
| TOC | não existe em markdown |
| Idioma | predominantemente PT-BR |
| Tamanho | relatórios 40–120; tutoriais 155; specs 452–620 linhas |

### Referências ao arquivo movido

`grep -rn "tutorial_newsletter"` encontrou apenas ocorrências em **relatórios datados**
(`2026-08-16_*` ×3) e um survey de 2026-09-07. Nenhuma referência viva em código, JSON
ou menu. Decisão: não reescrever registros históricos.

### Fatos técnicos verificados

| Item | Valor |
| --- | --- |
| `@playwright/mcp` | v0.0.85 |
| `chrome-devtools-mcp` | v1.12.0; engines `node ^20.19.0 \|\| ^22.12.0 \|\| >=23` |
| `@playwright/test` | v1.65.0 |
| Ferramentas do Playwright MCP | 25 |
| `claude mcp add` | `-s, --scope <local\|user\|project>` (default `local`); também `-e`, `-H`, `--transport` |
| Comando validado no Windows | `claude mcp add playwright -- cmd /c npx @playwright/mcp@latest` |
| `@playwright/test` exporta `chromium`? | **sim** — testado em instalação isolada em `/tmp`: `typeof chromium === 'object'` |

> Nota: a tipagem de `@playwright/test` (`export * from 'playwright/test'`) sugere o
> contrário; a verificação empírica desempatou. Vale a execução, não a leitura do `.d.ts`.

## 4. Rodadas de grill

### Rodada 1

| Pergunta | Resposta |
| --- | --- |
| Opções cobertas | **as três** (Playwright MCP, Chrome DevTools MCP, devDependency) |
| Plataformas | **multiplataforma** (Windows + macOS/Linux) |
| Idioma | **português** |

### Rodada 2

| Pergunta | Resposta |
| --- | --- |
| Nome do arquivo | **`automacao_navegador_claude_code.md`** |
| `docs/tutorial_newsletter.md` | **mover** para `docs/tutorial/` |
| Profundidade | **guia completo** |
| Ponteiro | **linha em `CLAUDE.md`** |

## 5. Execução

1. `mkdir docs/tutorial` + `git mv docs/tutorial_newsletter.md docs/tutorial/tutorial_newsletter.md`
2. Criado `docs/tutorial/automacao_navegador_claude_code.md` — 279 linhas, 9 seções
3. `CLAUDE.md`: linha `docs/tutorial/` no bloco Project Structure

## 6. Verificação

| Checagem | Resultado |
| --- | --- |
| Versões dos pacotes publicados | 1.12.0 e 0.0.85 confirmadas |
| Export `chromium` de `@playwright/test` | confirmado |
| Cercas de código | 28 (par) |
| Pipes por tabela | constantes (4, 4, 2, 2, 3, 2) |
| `git status` | `R` rename + `??` novo arquivo + `M` CLAUDE.md |

## 7. Pendências

- Comandos de macOS/Linux não executados (máquina Windows) — declarado no tutorial, seção 9.
