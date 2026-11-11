# Alinhar o texto do conteúdo com o texto do menu lateral — 2026-11-11 (branch `develop_natan`)

Ajuste fino do alinhamento da coluna de conteúdo da documentação: o texto do artigo
agora entra com o mesmo respiro de 24px que o menu lateral usa depois da divisória
vertical, em vez dos 40px do `lg:p-10` do `<main>`.

## Resumo

| Item | Resultado |
| --- | --- |
| Arquivos alterados | 1 (`resources/js/Pages/DocsLayout.vue`) |
| Linhas alteradas | 1 (linha 96) |
| Classes adicionadas | `lg:pl-6` |
| Build de produção | `npm run build` OK (5,24s) |
| Verificação | Playwright MCP, 5 viewports × 2 temas |

## Contexto

O commit `554c946` (2026-11-10) tirou o conteúdo de perto da barra da direita e o
encostou na barra da esquerda — mas com um degrau em relação ao próprio menu:

| Elemento | Respiro |
| --- | --- |
| Texto dos itens do menu | 24px (`p-6` no container do menu, `DocsLayout.vue:45`) |
| Texto do artigo em `lg` | 40px (`lg:p-10` no `<main>`, `DocsLayout.vue:96`) |

Em telas ≥1024px, o artigo começava 16px mais afastado da divisória que o menu.
O pedido foi eliminar esse degrau: os dois lados da divisória com o mesmo respiro.

## Decisão

Confirmada com o autor antes da edição:

| Decisão | Escolha |
| --- | --- |
| Alvo | mesmo respiro do menu (24px) |
| Lado direito do `<main>` | **não** mexer (mantém a folga até o sumário) |
| Mobile (<768px) | inalterado — menu é gaveta, não há barra para alinhar |
| Escopo | só `DocsLayout.vue` |

## Mudança

`resources/js/Pages/DocsLayout.vue:96`:

```diff
-<main class="flex-1 w-full min-w-0 p-4 sm:p-6 lg:p-10 overflow-auto">
+<main class="flex-1 w-full min-w-0 p-4 sm:p-6 lg:p-10 lg:pl-6 overflow-auto">
```

`lg:pl-6` (24px) sobrescreve apenas o `padding-left` que `lg:p-10` havia posto em 40px.
O `padding-right` continua 40px — é ele que garante a folga até o sumário fixo
(`border-l` em `DocsLayout.vue:109`). Trocar `lg:p-10` por `lg:px-6 lg:py-10` teria
alargado o artigo em 32px e comido metade dessa folga.

## Geometria resultante

Referência: borda direita do `<aside>` do menu, em x = 256px.

| Viewport | Respiro do menu (x=24) | Respiro do artigo | Coluna | Sumário |
| --- | --- | --- | --- | --- |
| 1920px | 24px | **24px** (era 40) | 896px | 465px de folga |
| 1280px | 24px | **24px** (era 40) | 689px | 32px de folga, sem sobreposição |
| 1024px | 24px | **24px** (era 40) | 689px | oculto (`hidden xl:flex`) |
| 768px | 24px | 24px (já era, `sm:p-6`) | 449px | oculto |
| 390px | gaveta | 16px (inalterado, `p-4`) | 343px | oculto |

> Nota de leitura: o `p-6` do menu mede a partir da **borda esquerda da viewport**
> (o menu ocupa x = 0→256); o respiro do artigo mede a partir da **divisória** (x = 256).
> As duas colunas passam a ter o mesmo recuo interno de 24px — um de cada lado da linha,
> cada um contado da borda do próprio painel, não uma calha de 24px adjacente à linha.

## Verificação executada

`npm run build` + `php artisan serve` + Playwright MCP, tudo contra o build de produção.

| Checagem | Resultado |
| --- | --- |
| Respiro do artigo em 1920 / 1280 / 1024 / 768 | 24px em todos |
| `padding-left` computado do `<main>` em `lg` | `24px` (era `40px`) |
| `padding-right` computado | `40px` — inalterado |
| Largura da coluna em 1920 / 1280 / 1024 / 768 / 390 | 896 / 689 / 689 / 449 / 343 |
| Sobreposição com o sumário em 1280px | não (32px de folga) |
| Mobile 390px | 16px, sem rolagem horizontal |
| Tema claro (toggle do header) | geometria idêntica à do escuro |
| Prosa em `/instalacao` | `<pre>` dentro da coluna, sem estouro |
| Rolagem horizontal | nenhuma em nenhum viewport |
| Console | 0 erros, 0 avisos |
| Bundle | `lg:pl-6` presente em `public/build/assets/DocsLayout-*.js` (1 ocorrência) |

### Observação de ambiente

Como na sessão de 2026-11-10, `public/hot` estava datado de 24/09 apontando para
`http://[::1]:5173` com o Vite parado. O arquivo foi movido temporariamente durante o
teste e **devolvido ao lugar** ao final; o servidor `artisan serve` foi encerrado.

## Arquivos

| Arquivo | Alteração |
| --- | --- |
| `resources/js/Pages/DocsLayout.vue` | `lg:pl-6` no `<main>` (linha 96) |
| `public/build/**` | artefatos regerados pelo `npm run build` |
