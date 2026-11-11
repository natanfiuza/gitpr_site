# Survey — alinhar o texto do conteúdo com o texto do menu lateral

- **Data:** 2026-11-11
- **Branch:** `develop_natan`
- **Origem:** sessão `/grill-with-docs`
- **Task:** `alinhar_conteudo_texto_menu`

## 1. Pedido original

> "Corrige o alinhmento do conteudo para ficar alinhado a barra de navegação esquerda"

## 2. Contexto da tarefa

O pedido anterior nesta mesma frente (2026-11-10, commit `554c946`) já havia removido o
`mx-auto` do wrapper do artigo. O resultado medido foi 40px entre a divisória do menu e
o texto. O autor confirma que vê esse resultado — logo, não é problema de
`public/hot`/Vite — mas considera o respiro ainda grande.

## 3. Levantamento de fatos

Mapa completo das classes que posicionam horizontalmente o artigo em
`resources/js/Pages/DocsLayout.vue`:

| Elemento | Classe | Efeito horizontal |
| --- | --- | --- |
| `<aside>` menu | `w-64 ... border-r` (linha 35) | ocupa x = 0→256 em ≥768px |
| container do menu | `p-6` (linha 45) | itens começam em x = 24 |
| `<main>` | `p-4 sm:p-6 lg:p-10` (linha 96) | padding 16 / 24 / 40 |
| wrapper do artigo | `max-w-4xl xl:mr-64` (linha 97) | 896px máx; margem direita 256px em `xl` |
| `<h1>` | `border-b ... pb-2 mb-6` (linha 98) | sem efeito horizontal |
| `MarkdownViewer` | `prose max-w-none` | sem padding/margem própria |
| `app.css` | `.prose` sem override | nenhum efeito |
| sumário (TOC) | `fixed right-10 w-56 border-l pl-4` | à direita, não desloca o artigo |

Conclusões do levantamento:

- O único responsável pelo degrau era o `padding-left` do `<main>` em `lg`.
- `xl:mr-64` só mexe na margem direita; `md:relative` no `<aside>` é o que muda a posição
  do artigo entre mobile e desktop.
- O `p-6` do menu mede da borda da **viewport**; o do artigo mede da **divisória** —
  "alinhar com o texto do menu" foi interpretado como igualar os dois recuos internos
  (24px cada, contados da borda do respectivo painel).

## 4. Rodada de grill

| Pergunta | Resposta |
| --- | --- |
| O que se vê hoje | "Vejo ~40px, quero menos" — o ajuste anterior está aplicado |
| Alvo | "Alinhar com o texto do menu (24px)" |

## 5. Execução

1. `DocsLayout.vue:96`: adicionado `lg:pl-6` ao `<main>`
2. `npm run build`
3. Verificação com Playwright MCP em 1920 / 1280 / 1024 / 768 / 390, temas escuro e claro

## 6. Verificação

| Viewport | Respiro do menu | Respiro do artigo | Coluna | Observação |
| --- | --- | --- | --- | --- |
| 1920px | 24 | **24** (era 40) | 896 | TOC a 465px de folga |
| 1280px | 24 | **24** (era 40) | 689 | TOC a 32px, sem sobreposição |
| 1024px | 24 | **24** (era 40) | 689 | TOC oculto |
| 768px | 24 | 24 | 449 | `<aside>` `relative` |
| 390px | gaveta | 16 | 343 | inalterado |

| Item | Resultado |
| --- | --- |
| `padding-right` do `<main>` | 40px (inalterado) |
| Tema claro | geometria idêntica ao escuro |
| `/instalacao` | `<pre>` dentro da coluna |
| Rolagem horizontal | nenhuma |
| Console | 0 erros, 0 avisos |
| Bundle | `lg:pl-6` presente 1× em `DocsLayout-*.js` |

## 7. Pendências

- Nenhuma. O commit fica a critério do autor (a árvore já tinha as mudanças do tutorial
  de automação não commitadas).
