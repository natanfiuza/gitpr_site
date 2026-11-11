# Alinhar conteúdo à barra vertical da esquerda — 2026-11-10 (branch `develop_natan`)

Correção do alinhamento da coluna de conteúdo da documentação, que estava encostada
na barra vertical da direita (a do sumário "Nesta página") em vez da barra vertical
da esquerda (a do menu lateral).

## Resumo

| Item | Resultado |
| --- | --- |
| Arquivos alterados | 1 (`resources/js/Pages/DocsLayout.vue`) |
| Linhas alteradas | 1 (linha 97) |
| Classes removidas | `mx-auto` |
| Classes preservadas | `max-w-4xl`, `xl:mr-64` |
| Build de produção | `npm run build` OK (14,01s) |
| Verificação no bundle | 0 ocorrências da classe antiga, 1 da nova |

## Diagnóstico

Todas as rotas públicas de documentação são servidas pelo catch-all
`Route::get('/{page?}', DocsController::show_document)` (`routes/web.php:27`), que
renderiza `DocsLayout.vue`.

A página tem exatamente duas linhas verticais, ambas `border` do Tailwind — sem CSS
customizado, sem pseudo-elementos, sem `divide-x`:

| Barra | Elemento | Linha |
| --- | --- | --- |
| Esquerda | `border-r` do `<aside>` do menu lateral (`w-64`) | `DocsLayout.vue:35` |
| Direita | `border-l` do sumário fixo (`right-10 w-56 pl-4`) | `DocsLayout.vue:109` |

O container do artigo era:

```html
<div class="max-w-4xl mx-auto xl:mr-64">
```

`mx-auto` centralizava a coluna e, a partir de `xl`, o `xl:mr-64` (16rem) substituía
a margem direita automática por um valor fixo — o par empurrava a coluna de 896px
para a direita. Medição em 1920px:

- Início do texto: ~728px da borda esquerda da viewport → **~472px de vazio** depois
  da barra do menu lateral (que termina em 256px).
- Fim do texto: ~32px antes da barra vertical do sumário.

Ou seja, visualmente o artigo parecia "alinhado à barra da direita", com um vão grande
à esquerda — exatamente a queixa.

## Decisões

Confirmadas com o autor antes da edição:

| Decisão | Escolha |
| --- | --- |
| Barra de referência | `border-r` do menu lateral esquerdo |
| Largura da coluna | manter `max-w-4xl` (896px) |
| Respiro da barra | manter o padding do `<main>` (`p-4 sm:p-6 lg:p-10`) |
| Comportamento por breakpoint | alinhada à esquerda em todas as larguras |
| Sumário "Nesta página" | continua `fixed right-10`, colado à borda direita |
| Escopo | só `DocsLayout.vue` |

`/linter-utility` (3 colunas `max-w-7xl mx-auto`) e as páginas de newsletter (card
`max-w-md` centrado) têm layout próprio e não possuem essas barras — ficaram intactas.

## Mudança

`resources/js/Pages/DocsLayout.vue:97`:

```diff
-<div class="max-w-4xl mx-auto xl:mr-64">
+<div class="max-w-4xl xl:mr-64">
```

- **Remover `mx-auto`** desfaz a centralização: a div vira bloco normal, largura `auto`
  limitada por `max-w-4xl`, ancorada à esquerda do padding do `<main>`.
- **Manter `xl:mr-64`** não é cosmético: a partir de `xl` o sumário é
  `fixed right-10 w-56` (de 2,5rem a 16,5rem da borda direita), e a margem garante que
  a coluna não passe por baixo dele.

Conferência da conta nos breakpoints `xl`:

| Viewport | Largura útil do `<main>` | Largura da coluna | Vão até o sumário |
| --- | --- | --- | --- |
| 1280px | 944px | 688px | ~32px |
| 1400px | 1064px | 808px | ~32px |
| 1500px+ | — | 896px (limite) | ~44px ou mais |

Abaixo de `xl` o sumário está oculto (`hidden xl:flex`) e `max-w-4xl` só limita a
coluna acima de ~1232px de viewport; remover `mx-auto` não altera nada visível em
mobile.

## Verificação executada

- `npm run build` — concluído sem erros.
- `grep` no bundle gerado: a string `max-w-4xl mx-auto xl:mr-64` não aparece mais em
  `public/build/assets/DocsLayout-*.js`; a string `max-w-4xl xl:mr-64` está presente.

## Verificação visual (Playwright, 2026-11-11)

Executada contra o build de produção (`php artisan serve` + `public/build`), com o
Playwright MCP. Medições em `getBoundingClientRect()`.

| Viewport | Gap barra esq. → texto | Largura da coluna | Gap texto → sumário | Sumário | Rolagem horizontal |
| --- | --- | --- | --- | --- | --- |
| 1920px | **40px** | 896px | 449px | visível | não |
| 1280px (xl) | **40px** | 673px | 32px | visível | não |
| 1024px | **40px** | 673px (ocupa o `<main>`) | — | oculto | não |
| 390px (mobile) | 16px | 343px | — | oculto | não |

Antes da correção, em 1920px o gap barra esquerda → texto era de ~472px.

Outros pontos conferidos:

| Item | Resultado |
| --- | --- |
| Sobreposição com o sumário em `xl` | nenhuma (32px de folga) |
| Tema escuro (toggle do header) | layout idêntico ao claro |
| Sumário fixo ao rolar (`window.scrollTo(0, 1400)`) | `top` 80px antes e depois |
| Destaque do item ativo no sumário | funciona ("2. Initializing in a Repository" em negrito) |
| Página interna `/instalacao` | mesma geometria; nenhum `<pre>`/tabela escapa da coluna |
| Erros de console | 0 |

### Observação de ambiente

`public/hot` estava datado de 24/09 apontando para `http://[::1]:5173` com o Vite
parado — o Laravel servia URLs do dev server morto e a página não montava (3 erros de
console). O arquivo foi movido temporariamente durante o teste e **devolvido ao lugar**
ao final; se a home aparecer em branco localmente, basta rodar `npm run dev` para
regenerá-lo.

## Anexo — automação de navegador (Playwright MCP)

Para fechar a verificação visual pendente, foi instalado o Playwright MCP
(`claude mcp add playwright -- cmd /c npx @playwright/mcp@latest`), mantido em
escopo de projeto (nada é adicionado às dependências do `package.json`).

Verificado:

| Item | Resultado |
| --- | --- |
| Pacote | `@playwright/mcp` v0.0.85 |
| Conexão | `√ Connected` |
| Ferramentas expostas | 25 (`browser_navigate`, `browser_click`, `browser_take_screenshot`, `browser_resize`, …) |
| Lançamento do navegador | OK (navegou para uma `data:` URL e retornou snapshot) |

Caveat conhecido: a entrada é gravada sob a chave de projeto `C:/Users/...`
(C maiúsculo), mas o Git Bash reporta o cwd como `c:/Users/...` (minúsculo) — por
isso `claude mcp list` executado no Git Bash não mostra o servidor, embora o
PowerShell e a sessão do Claude Code o resolvam corretamente.

O servidor grava snapshots em `.playwright-mcp/`, adicionado ao `.gitignore`.

## Arquivos

| Arquivo | Alteração |
| --- | --- |
| `resources/js/Pages/DocsLayout.vue` | removida a classe `mx-auto` do container do artigo (linha 97) |
| `.gitignore` | adicionada a regra `/.playwright-mcp/` |
| `public/build/**` | artefatos regerados pelo `npm run build` |
