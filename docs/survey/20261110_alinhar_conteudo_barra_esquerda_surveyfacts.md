# Survey — alinhar o conteúdo à barra vertical da esquerda

- **Data:** 2026-11-10
- **Branch:** `develop_natan`
- **Origem:** sessão `/grill-with-docs`
- **Task:** `alinhar_conteudo_barra_esquerda`

## 1. Pedido original

> "O conteúdo do site está alinhado à barra vertical da direita e ela deve estar alinhado
> à barra da esquerda"

## 2. Contexto da tarefa

### Roteamento

Todas as páginas públicas de documentação são servidas pelo catch-all
`Route::get('/{page?}', [DocsController::class, 'show_document'])->where('page', '.*')`
(`routes/web.php:27`), que renderiza o componente Inertia `DocsLayout`
(`app/Http/Controllers/DocsController.php:99`). O conteúdo vem de `public/content/{page}.md`
e o menu de `public/content/menu.json`.

A página `Welcome.vue` existe, mas sua rota está comentada (`routes/web.php:29-36`) — não é servida.

### Páginas públicas e seus layouts

| Página | Componente | Layout |
| --- | --- | --- |
| Documentação (`/`, `/quickstart`, …) | `DocsLayout.vue` | menu lateral + conteúdo + sumário fixo |
| `/linter-utility` | `LinterUtility.vue` | 3 colunas em `max-w-7xl mx-auto` |
| `/newsletter/confirm`, `/newsletter/cancel` | `NewsletterConfirmPage.vue`, `NewsletterCancelPage.vue` | card `max-w-md` centrado |

Somente `DocsLayout.vue` tem as duas barras verticais em questão.

## 3. Levantamento de fatos (relatório)

### Barras verticais da página

Todas são utilitários `border` do Tailwind — não há `divide-x`, nem `before:`/`::before`,
nem classes customizadas `.line`/`.bar` em `resources/`.

| # | Elemento | Classe | Arquivo:linha |
| --- | --- | --- | --- |
| 1 | Borda direita do menu lateral | `border-r` no `<aside>` (`w-64`) | `DocsLayout.vue:35` |
| 2 | Borda esquerda do sumário fixo | `border-l` no `<aside>` (`right-10 w-56 pl-4`) | `DocsLayout.vue:109` |
| 3 | Barra colorida de admonitions | `.admonition { @apply border-l-4 … }` | `resources/css/app.css:7` |
| 4 | Blockquote do prose | padrão do `@tailwindcss/typography` | `MarkdownViewer.vue:2-4` |
| 5 | NavLink de auth (não público) | `border-l-4` | `ResponsiveNavLink.vue:17-18` |

Só as #1 e #2 delimitam a área de conteúdo.

### Containers e alinhamento

| Elemento | Classe | Arquivo:linha |
| --- | --- | --- |
| `<main>` (rolagem) | `flex-1 w-full min-w-0 p-4 sm:p-6 lg:p-10 overflow-auto` | `DocsLayout.vue:96` |
| Container do artigo | `max-w-4xl mx-auto xl:mr-64` | `DocsLayout.vue:97` |
| Título da página | `border-b … pb-2` (linha horizontal) | `DocsLayout.vue:98-101` |
| Sumário fixo | `hidden xl:flex … fixed top-20 right-10 w-56 max-h-[calc(100vh-6rem)] border-l … pl-4` | `DocsLayout.vue:108-109` |
| Conteúdo interno do menu | `p-6` | `DocsLayout.vue:45` |

### Geometria medida (viewport 1920px)

- Menu lateral: 0 → 256px.
- `<main>` com `lg:p-10`: caixa útil de 296px a 1880px (1584px).
- Coluna do artigo: `max-w-4xl` = 896px, `mr-64` = 256px.
  - Largura = `min(896, 1584 − 256)` = 896px.
  - `margin-left: auto` recebe o restante = 432px.
  - Faixa ocupada: **728px → 1624px**.
- Sumário: `right-10` (40px) + `w-56` (224px) → borda vertical em **1656px**.

Consequências: ~472px de vazio entre a barra esquerda (256px) e o texto (728px), e
apenas ~32px entre o fim do texto e a barra direita (1656px) — o artigo "parece alinhado
à barra da direita".

### Bundle

Chunk do layout: `public/build/assets/DocsLayout-*.js` (≈1,03 MB; aviso de tamanho de chunk
pré-existente do Vite, não relacionado a esta mudança).

## 4. Rodadas de grill — decisões tomadas

### Rodada 1

| Pergunta | Resposta |
| --- | --- |
| A "barra da esquerda" é o `border-r` do menu lateral? | **Sim** |
| Largura da coluna após alinhar | **Manter `max-w-4xl` (896px)** — descartadas: alargar para `max-w-6xl`; ocupar todo o vão |
| Respiro até a barra | **Manter o padding atual do `<main>`** — descartado: encostar na barra |

### Rodada 2

| Pergunta | Resposta |
| --- | --- |
| Comportamento abaixo de `xl` (onde o sumário está oculto) | **Alinhar à esquerda em todas as larguras** (`mx-auto xl:mx-0` foi descartado) |
| Posição do sumário "Nesta página" | **Continua fixo na borda direita** — "colar no conteúdo" (sticky ao lado da coluna) foi descartado |

### Escopo

Apenas `DocsLayout.vue`. `/linter-utility` e as páginas de newsletter ficam fora — layouts
distintos, sem as barras em questão.

## 5. Execução

- `resources/js/Pages/DocsLayout.vue:97` — removida a classe `mx-auto`.
- `npm run build` — OK.
- Bundle verificado: 0 ocorrências de `max-w-4xl mx-auto xl:mr-64`, 1 de `max-w-4xl xl:mr-64`.

## 6. Pendências

- Verificação visual manual em navegador (1920 / 1280 / 1024 / mobile; temas claro e escuro).
  O ambiente não tem automação de navegador instalada.
