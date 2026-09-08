# Rolagem automática até o primeiro termo marcado ao abrir resultado da busca

## Context

Ao clicar num resultado da busca, a página de documentação abre com a palavra pesquisada destacada em amarelo (`<mark>`) — comportamento já existente via parâmetro `?mark=` na URL. Falta o que o usuário pede: **a página deve rolar automaticamente até a primeira ocorrência marcada**.

Como funciona hoje ([MarkdownViewer.vue](resources/js/Components/MarkdownViewer.vue)):
1. O `SearchBar.vue` monta o link `'/' + result.path + '?lang=...&mark=' + search_query`.
2. No `MarkdownViewer.vue`, o `watch(parsed_content)` (após `nextTick`) chama `highlight_marked_text()` ([linha 189](resources/js/Components/MarkdownViewer.vue#L189)), que lê `?mark=` da URL e envolve as ocorrências em `<mark class="bg-yellow-400...">`, ignorando texto dentro de `<code>`.
3. Nada rola a página até essas ocorrências.

O site já usa o padrão `el.scrollIntoView({ behavior: 'smooth' })` para âncoras do TOC ([DocsLayout.vue:278-283](resources/js/Pages/DocsLayout.vue#L278-L283)) — `scrollIntoView` funciona independentemente de o scroll real estar no `window` ou no `<main overflow-auto>` ([DocsLayout.vue:96](resources/js/Pages/DocsLayout.vue#L96)), então é a escolha robusta.

**Bug latente adjacente:** `highlight_marked_text` constrói `new RegExp('(' + mark_term + ')', 'gi')` com o termo cru ([linha 135](resources/js/Components/MarkdownViewer.vue#L135)). Termos com metacaracteres de regex (`c++`, `[`, `(`, `*` — plausíveis num site com guia de regex do linter) **lançam exceção** e abortam todo o watcher: sem highlight, sem TOC, sem colaboradores — e o novo scroll também não rodaria. Corrigir junto é necessário para o recurso ser confiável.

## Mudanças (1 arquivo: `resources/js/Components/MarkdownViewer.vue`)

### 1. Escapar metacaracteres do termo em `highlight_marked_text`

Adicionar helper pequeno no `<script setup>`:

```js
const escape_regex = (term) => term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
```

E usar em [linha 135](resources/js/Components/MarkdownViewer.vue#L135):

```js
const regex = new RegExp(`(${escape_regex(mark_term)})`, 'gi');
```

(Só aqui. `SearchBar.vue:107` tem o mesmo padrão no highlight do dropdown — latente e fora do escopo desta tarefa; apenas anotar no relatório.)

### 2. Rolar até o primeiro `<mark>`

Nova função junto de `highlight_marked_text`:

```js
const scroll_to_first_mark = () => {
    const url_params = new URLSearchParams(window.location.search);
    if (!url_params.get('mark') || !content_ref.value) return;
    const first_mark = content_ref.value.querySelector('mark');
    if (first_mark) {
        first_mark.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};
```

Chamada no watcher, logo após `highlight_marked_text();` ([linha 189](resources/js/Components/MarkdownViewer.vue#L189)) — nesse momento os `<mark>` já existem no DOM:

```js
highlight_marked_text();
scroll_to_first_mark();
```

### Comportamento resultante

- Clicar num resultado de busca (navegação Inertia com `?mark=`) → conteúdo troca → watcher dispara → marca → **rola suave até a 1ª ocorrência**.
- Abrir/atualizar URL direto com `?mark=` → mesma rolagem (consistente).
- Navegação normal pelo menu (sem `?mark=`) → guard retorna cedo, nada rola (comportamento atual preservado).
- Termos com `?`/`&`/`#` dentro do `?mark=` já são limitados pelo encoding atual da URL — fora do escopo.

## Verificação

1. `npm run build` — compila sem erros.
2. Manual (servidor em porta livre, ex.: `php artisan serve --port=8001` já que 8000 está ocupada):
   - Abrir `/instalacao?lang=pt_br&mark=PyPI` → página deve rolar suave até o 1º "PyPI" destacado em amarelo (não ficar no topo).
   - Clicar num resultado da busca na barra superior (termo com várias ocorrências, ex.: "git") → navega e para no 1º destaque da página destino.
   - Navegar pelo menu lateral para outra página → não deve rolar para lugar nenhum (sem `mark`).
   - Termo com metacaractere, ex.: `?mark=c++` ou buscar "c++" e clicar num resultado → sem exceção no console; highlight e rolagem funcionam (regressão do escape).
3. `php artisan test` — nada de backend muda; suite deve permanecer no estado atual (50 passando / 8 falhas pré-existentes de auth).

## Relatório

`docs/claude-code/reports/develop_natan/2026-09-08_develop_natan_rola_ate_primeiro_mark_da_busca.md` (regra do CLAUDE.md).
