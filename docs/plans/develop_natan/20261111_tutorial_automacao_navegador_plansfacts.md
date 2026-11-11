# Plano — tutorial de automação de navegador no Claude Code

- **Data:** 2026-11-11
- **Branch:** `develop_natan`
- **Origem:** sessão `/grill-with-docs`
- **Task:** `tutorial_automacao_navegador`

## Contexto

A instalação do Playwright MCP gerou conhecimento operacional (comandos, escopos,
pegadinhas de Windows) que ficou registrado apenas num relatório datado de tarefa.
O pedido é transformar isso em material consultável em `docs/tutorial/`.

## Decisões confirmadas

| # | Decisão | Escolha |
| --- | --- | --- |
| 1 | Escopo | as três opções: Playwright MCP, Chrome DevTools MCP, Playwright devDependency |
| 2 | Plataformas | Windows e macOS/Linux |
| 3 | Idioma | português |
| 4 | Arquivo | `docs/tutorial/automacao_navegador_claude_code.md` |
| 5 | `docs/tutorial_newsletter.md` | mover para `docs/tutorial/` |
| 6 | Profundidade | guia completo |
| 7 | Ponteiro | linha em `CLAUDE.md` |

## Convenções aplicadas

- docs/ não usa front-matter: metadados em negrito após o H1
- seções `## N.` numeradas; sem TOC em markdown
- extensão típica de tutorial na pasta: 100–160 linhas

## Mudanças

1. `mkdir docs/tutorial` + `git mv docs/tutorial_newsletter.md docs/tutorial/tutorial_newsletter.md`
2. Criar `docs/tutorial/automacao_navegador_claude_code.md` com 9 seções
   (o que é → comparativo → 3 opções → exemplos → troubleshooting → referência → notas de campo)
3. `CLAUDE.md`: `docs/tutorial/           # Tutoriais internos (setup de ferramentas, fluxos)`

## Fora de escopo

- publicar no site (`public/content/`)
- reescrever relatórios históricos que citam o caminho antigo
- mexer na configuração MCP da máquina

## Verificação

1. `git status` mostra o rename e o arquivo novo
2. `npx chrome-devtools-mcp@latest --version` e `npx @playwright/mcp@latest --version` respondem
3. `@playwright/test` exporta `chromium` (testado empiricamente)
4. cercas de código em número par; pipes consistentes por tabela
5. tutorial movido sem referências internas quebradas

## Resultado

Executado em 2026-11-11. Tutorial com 279 linhas (acima da estimativa de 150–200,
abaixo das especificações do repo).

## Documentos gerados

- Relatório: `docs/claude-code/reports/develop_natan/2026-11-11_develop_natan_tutorial_automacao_navegador.md`
- Survey: `docs/survey/20261111_tutorial_automacao_navegador_surveyfacts.md`
