# Plano — alinhar o conteúdo à barra vertical da esquerda

- **Data:** 2026-11-10
- **Branch:** `develop_natan`
- **Origem:** sessão `/grill-with-docs`
- **Task:** `alinhar_conteudo_barra_esquerda`

## Contexto

A coluna de conteúdo da documentação aparecia encostada na barra vertical da direita
(o `border-l` do sumário "Nesta página"), deixando um vão grande à esquerda. O pedido
era alinhá-la à barra vertical da esquerda (o `border-r` do menu lateral).

Causa: `max-w-4xl mx-auto xl:mr-64` em `resources/js/Pages/DocsLayout.vue:97`.
O `mx-auto` centraliza; a partir de `xl`, `xl:mr-64` troca a margem direita automática
por 16rem fixos, empurrando a coluna para a direita.

## Decisões fechadas na sessão

| # | Decisão | Escolha |
| --- | --- | --- |
| 1 | Qual é a "barra da esquerda" | `border-r` do `<aside>` do menu lateral (`DocsLayout.vue:35`) |
| 2 | Largura após alinhar | manter `max-w-4xl` (896px) |
| 3 | Respiro até a barra | manter o padding do `<main>` (`p-4 sm:p-6 lg:p-10`) |
| 4 | Comportamento abaixo de `xl` | alinhar à esquerda em **todas** as larguras |
| 5 | Posição do sumário | continua `fixed right-10` na borda direita |
| 6 | Escopo | apenas `DocsLayout.vue` |

## Mudança aprovada

Arquivo único, uma linha:

```diff
-<div class="max-w-4xl mx-auto xl:mr-64">
+<div class="max-w-4xl xl:mr-64">
```

`xl:mr-64` é mantido de propósito: sem ele, a coluna passaria por baixo do sumário fixo
em `xl` (sumário ocupa de 2,5rem a 16,5rem da borda direita).

## Verificação

1. `npm run build` sem erros.
2. Conferir no bundle que a classe antiga sumiu e a nova está presente.
3. Inspeção visual manual (`composer run dev`) em 1920 / 1280 / 1024 / mobile,
   temas claro e escuro: texto começa após a barra do menu, sem sobrepor o sumário
   e sem rolagem horizontal.
4. Rolar a página: sumário continua fixo e o destaque do item ativo continua funcionando.

## Documentos gerados

- Relatório: `docs/claude-code/reports/develop_natan/2026-11-10_develop_natan_alinhar_conteudo_barra_esquerda.md`
- Survey: `docs/survey/20261110_alinhar_conteudo_barra_esquerda_surveyfacts.md`
- Sem `GLOSSARY.md` / ADR: ajuste de uma classe utilitária, trivialmente reversível.
