# Rolagem automática até o primeiro termo marcado ao abrir resultado da busca

**Data:** 2026-09-08 · **Branch:** develop_natan · **Tarefa:** "Ao clicar num resultado de busca, o cursor da página deve ir para o primeiro resultado marcado"

## Contexto

Ao clicar num resultado da busca, a página abria com a palavra pesquisada destacada em amarelo (`<mark>`, via parâmetro `?mark=` da URL), mas a página **não rolava** até a ocorrência — o usuário precisava procurar o destaque manualmente. Esta tarefa adiciona a rolagem automática até a **primeira** ocorrência marcada.

## Causa

O destaque é feito em `resources/js/Components/MarkdownViewer.vue`, no `watch(parsed_content)` que roda após `nextTick()` quando o conteúdo muda (navegação Inertia ou load direto). Nenhum código rolava a página até os `<mark>` recém-criados.

**Bug latente adjacente corrigido junto:** o highlight montava `new RegExp('(' + mark_term + ')', 'gi')` com o termo **sem escapar metacaracteres de regex**. Termos como `c++`, `*`, `git [alpha]` lançavam exceção ("Nothing to repeat") e abortavam o watcher inteiro — sem highlight, sem TOC, sem colaboradores. Confirmado em teste: com o termo cru, `c++` e `*` lançam; com escape, todos os termos testados funcionam.

## Mudança aplicada

Único arquivo: `resources/js/Components/MarkdownViewer.vue`

1. **Helper `escape_regex`** (linha 119) — padrão canônico de escape (`/[.*+?^${}()|[\]\\]/g`), usado na construção do RegExp do highlight (linha 137).
2. **Função `scroll_to_first_mark`** — guard por `?mark=` presente; pega `querySelector('mark')` (primeira ocorrência em ordem de documento) e chama `scrollIntoView({ behavior: 'smooth', block: 'start' })`, mesmo padrão já usado nas âncoras do TOC em `DocsLayout.vue`. `scrollIntoView` funciona tanto se o scroll real estiver no `window` quanto no `<main overflow-auto>`.
3. **Chamada no watcher**, logo após `highlight_marked_text()` — nesse ponto os `<mark>` já estão no DOM.

### Comportamento

- Clicar num resultado de busca → página rola suavemente até o 1º destaque.
- Abrir/atualizar URL direta com `?mark=` → mesma rolagem.
- Navegação pelo menu sem `?mark=` → nada rola (inalterado).

## Verificação

- `npm run build` → ok (sintaxe validada pelo bundler).
- Lógica do escape validada em Node **com o padrão real extraído do arquivo** (evitando duplicação): termos `PyPI`, `c++`, `git [alpha]`, `a.b`, `(regex)`, `*`, `instalação` → todos produzem 2 marcações corretas; sem o escape, `c++` e `*` lançam exceção.
- Servidor temporário `php artisan serve --port=8001` (8000 ocupada): `/instalacao?lang=pt_br&mark=PyPI`, `/instalacao?lang=en&mark=PyPI`, `/docs/scm-multiforge?lang=pt_br&mark=GitLab`, `/index` → todos HTTP 200.
- A rolagem em si é client-side — **sem headless browser disponível no ambiente**, o teste visual fica para validação manual no navegador (passos abaixo).

### Passos de validação manual sugeridos (navegador)

1. `composer run dev` (ou `php artisan serve` em porta livre + `npm run dev`) e abrir `/instalacao?lang=pt_br&mark=PyPI` → página deve rolar suave e parar no 1º "PyPI" amarelo.
2. Buscar "git" na barra superior e clicar num resultado → navega e para no 1º destaque da página destino.
3. Menu lateral para outra página → não deve rolar.
4. Buscar `c++` e clicar num resultado → sem erro no console; destaque e rolagem funcionam (regressão do escape).

## Nota fora do escopo

`SearchBar.vue` (highlight do dropdown de resultados, ~linha 107) tem o mesmo padrão sem escape de regex — latente; sugerido para tarefa separada se termos com metacaracteres forem pesquisados com frequência.

## Arquivos tocados

```
M resources/js/Components/MarkdownViewer.vue
A docs/claude-code/reports/develop_natan/2026-09-08_develop_natan_rola_ate_primeiro_mark_da_busca.md
```
