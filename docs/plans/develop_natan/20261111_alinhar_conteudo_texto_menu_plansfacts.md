# Plano — alinhar o texto do conteúdo com o texto do menu lateral

- **Data:** 2026-11-11
- **Branch:** `develop_natan`
- **Origem:** sessão `/grill-with-docs`
- **Task:** `alinhar_conteudo_texto_menu`

## Pedido original

> "Corrige o alinhmento do conteudo para ficar alinhado a barra de navegação esquerda"

## Contexto

O commit `554c946` já havia ancorado a coluna do artigo à esquerda (removido o
`mx-auto`), deixando 40px entre a divisória do menu e o texto. O autor confirmou que
enxerga esse resultado, mas quer menos respiro: o alvo é o mesmo recuo do menu, 24px.

Ponto que precisou ser esclarecido na rodada: o `p-6` do container do menu mede a partir
da **borda esquerda da viewport** (o `<aside>` ocupa x = 0→256), enquanto o respiro do
artigo mede a partir da **divisória** (x = 256). "Alinhar com o texto do menu" foi
interpretado — e confirmado — como calha simétrica de 24px dos dois lados da linha.

## Decisões confirmadas

| # | Decisão | Escolha |
| --- | --- | --- |
| 1 | Diagnóstico | o autor vê os 40px atuais; é ajuste de CSS, não de ambiente |
| 2 | Alvo | 24px — mesmo respiro do `p-6` do menu |
| 3 | Lado direito do `<main>` | não mexer (`lg:p-10` continua a 40px) |
| 4 | Mobile (<768px) | inalterado (16px) — menu é gaveta, sem barra de referência |
| 5 | Escopo | só `DocsLayout.vue` |

## Mudança

`resources/js/Pages/DocsLayout.vue:96` — uma classe:

```diff
-<main class="flex-1 w-full min-w-0 p-4 sm:p-6 lg:p-10 overflow-auto">
+<main class="flex-1 w-full min-w-0 p-4 sm:p-6 lg:p-10 lg:pl-6 overflow-auto">
```

Descartado: trocar `lg:p-10` por `lg:px-6 lg:py-10` — mexeria também no `padding-right`
e reduziria a folga até o sumário fixo de 32px para 16px em 1280px.

## Verificação

1. `npm run build` sem erro; `lg:pl-6` presente no `DocsLayout-*.js`
2. Playwright MCP contra o build servido: respiro do artigo = 24px em 1920 / 1280 /
   1024 / 768; 16px em 390 (inalterado)
3. `padding-right` do `<main>` continua 40px; sem sobreposição com o sumário em 1280px
4. Tema claro e escuro com geometria idêntica; 0 erros de console
5. `/instalacao` mantém blocos de código dentro da coluna

## Resultado

Executado em 2026-11-11. Medições: respiro de 24px nos quatro viewports com menu
visível; mobile intocado; 0 erros de console.

## Documentos gerados

- Relatório: `docs/claude-code/reports/develop_natan/2026-11-11_develop_natan_alinhar_conteudo_texto_menu.md`
- Survey: `docs/survey/20261111_alinhar_conteudo_texto_menu_surveyfacts.md`
