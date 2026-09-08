# Correção da busca da barra superior (produção)

**Data:** 2026-09-08 · **Branch:** develop_natan · **Tarefa:** `/grill-with-docs` — "A busca na barra superior não está funcionando"

## Resumo

A busca da documentação em **produção** (`https://gitpr.natanfiuza.dev.br`) sempre mostrava "Nenhum resultado encontrado", mesmo para termos existentes. Investigação encontrou **4 problemas encadeados** (3 corrigidos no código + 1 operacional na hospedagem) e 1 bug latente de UTF-8 que só apareceria após a correção do caminho.

## Diagnóstico (evidências)

1. **Backend local saudável**: rota `GET /api/search` registrada antes da catch-all, sem cache de rotas; `DocsController::search_content` simulado contra o conteúdo real retornava matches nos 5 idiomas (ex.: `git` → 50/50 páginas; `menu.json` válido, nenhum arquivo faltando).
2. **Edge da hospedagem bloqueia `/api/*` com 307 em loop** (`Location: /api/search` — aponta para si mesmo):
   - `GET /api/search`, `/api/qualquercoisa` → `307`; rotas fora de `/api/` não recebem o 307.
   - A requisição nunca chega ao Laravel; axios segue o redirect em loop e falha.
3. **A UI mascara a falha como "sem resultado"**: em `SearchBar.vue`, o estado vazio aparecia quando `search_query.length >= 3 && !is_loading` — inclusive após erro no `catch` (o `finally` setava `is_loading = false`). Por isso o usuário via "Nenhum resultado encontrado" em vez de um erro.
4. **Origem PHP da produção fora do ar no momento do diagnóstico**: `504` em `/`, `/instalacao`, `/index` e até no estático `/content/menu.json` (ação necessária no painel da hospedagem — não há acesso SSH/deploy no repositório).
5. **Bug latente descoberto na verificação (500)**: `search_content` montava o snippet com `substr()`/`stripos()` **por bytes**; ao cortar conteúdo com acentos (pt_br, es, fr, pt_pt) o corte podia **dividir um caractere multibyte no meio**, gerando UTF-8 inválido → `json_encode` lançava `InvalidArgumentException: Malformed UTF-8 characters` → **HTTP 500** (reproduzido com `q=git&lang=pt_br` e `&lang=es`; `en` passava por ser majoritariamente ASCII). Teríamos batido nisso em produção logo após corrigir o caminho da rota.

## Correções aplicadas

| Arquivo | Mudança |
|---|---|
| `routes/web.php` | Rota movida de `/api/search` para `/search` (nome `api.search` mantido; nada mais usa `/api/*`). Comentário explica o motivo (307 do edge). |
| `resources/js/Components/SearchBar.vue` | URL do axios → `/search`; novo estado `search_error` — falha de rede mostra "Erro ao buscar. Tente novamente." em destaque vermelho; "Nenhum resultado encontrado" só aparece com resposta 200 vazia; erro limpo a cada digitação. |
| `app/Http/Controllers/DocsController.php` | Snippet via `mb_stripos()`/`mb_substr()` (conta caracteres, não bytes) + `continue` quando `mb_stripos` falha (conteúdo inválido) + `JSON_INVALID_UTF8_SUBSTITUTE` na resposta como rede de segurança. |
| `tests/Feature/SearchTest.php` | **Novo** — 4 testes: resultados `en`; conteúdo acentuado `pt_br` (regressão do 500); lista vazia quando nada casa; `/api/search` deve ser 404 (trava reintrodução do prefixo). |
| `GEMINI.md` | Comentário da estrutura de rotas atualizado (`/search`). |

## Verificação (local)

- `php artisan route:list` → `search … api.search › DocsController@search_content` registrada.
- Servidor temporário `php artisan serve --port=8001` (8000 ocupada por outro projeto):
  - `/search?q=git&lang={en,pt_br,pt_pt,es,fr}` → **HTTP 200, 50 resultados em todos**; snippet pt_br íntegro com acentos/emoji.
  - `/search?q=zzztermo` → `200 []`.
  - `/api/search?q=git` → `404` (esperado — cai na catch-all; nada mais usa `/api/`).
  - Antes da correção de UTF-8: `q=git&lang=pt_br` e `lang=es` → **500 estável**; após correção → 200.
- `php artisan test --filter=SearchTest` → **4/4 passando**.
- `npm run build` → ok.
- Suite completa: **50 passando / 8 falhas — todas pré-existentes** (Auth/Profile: `/login`, `/register`, `/profile`, `/verify-email`... 404). Confirmado via `git stash` + teste no HEAD limpo: as mesmas 8 falhas ocorrem sem as mudanças desta tarefa (a catch-all `/{page?}` é registrada **antes** de `/dashboard` e de `routes/auth.php` e engole essas rotas). **Fora do escopo** desta correção — ver pendências.

## Pendências do usuário (hospedagem — sem acesso deste repositório)

1. **Reiniciar o PHP no painel da hospedagem** (hPanel → Restart PHP): a origem estava devolvendo 504 em todas as rotas.
2. **Publicar** os arquivos alterados (`routes/web.php`, `app/Http/Controllers/DocsController.php`, `resources/js/Components/SearchBar.vue` + build em `public/build/` via `npm run build`, `tests/Feature/SearchTest.php`, `GEMINI.md`) e limpar cache de rotas se usado: `php artisan route:clear`.
3. **Validar**: `curl "https://gitpr.natanfiuza.dev.br/search?q=git&lang=pt_br"` deve retornar JSON com resultados.
4. Se ainda retornar `[]` com o site no ar: conferir via FTP se `public/content/menu.json` e os `.md` estão em dia com o repositório (a sincronização recente de docs pode ter deixado o servidor inconsistente).

## Sugestão fora do escopo (não aplicada)

As rotas de auth (Breeze: `/login`, `/register`, etc.) e `/dashboard` estão **quebradas por ordenação de rotas**: a catch-all `/{page?}` em `routes/web.php` é registrada antes delas e responde 404. Corrigir = mover as rotas de auth/dashboard para antes da catch-all. Se desejado, abrir tarefa separada.

## Arquivos tocados

```
M routes/web.php
M resources/js/Components/SearchBar.vue
M app/Http/Controllers/DocsController.php
M GEMINI.md
A tests/Feature/SearchTest.php
A docs/claude-code/reports/develop_natan/2026-09-08_develop_natan_corrige_busca_barra_superior.md
```
