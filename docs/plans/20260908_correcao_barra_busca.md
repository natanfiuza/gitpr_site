# Correção da busca da barra superior (produção)

## Context

O usuário reportou que a busca da barra superior do site **não funciona** em produção (`https://gitpr.natanfiuza.dev.br`): ao digitar 3+ letras, sempre aparece "Nenhum resultado encontrado", mesmo para termos que existem na documentação.

### Fatos levantados (debug)

1. **Backend/repositório local está saudável**: a rota `GET /api/search` existe em [routes/web.php:11](routes/web.php#L11) (registrada antes da catch-all `/{page?}`), sem cache de rotas no disco, e o `DocsController::search_content` foi simulado contra o conteúdo real retornando matches nos 5 idiomas (ex.: `git` → 50/50 páginas; `menu.json` válido, nenhum arquivo faltando).
2. **Em produção, `/api/*` é interceptado no edge (CDN hcdn) com 307 self-redirect** (`Location: /api/search` — aponta para si mesmo): `/api/search`, `/api/qualquercoisa` → 307, enquanto rotas fora de `/api/` não recebem esse 307. Ou seja, o navegador/axios segue o 307 em loop → a requisição falha → busca nunca retorna resultados.
3. **A UI mascara a falha**: em [SearchBar.vue:27-30](resources/js/Components/SearchBar.vue#L27-L30), o estado vazio "Nenhum resultado encontrado" aparece sempre que `search_query.length >= 3 && !is_loading` — **inclusive quando a requisição falhou** (no `catch`, `is_loading` volta a `false`). Por isso o usuário vê "Nenhum resultado" em vez de um erro.
4. **No momento do diagnóstico, a origem PHP de produção estava fora do ar** (504 em `/`, `/instalacao`, até `/content/menu.json` estático) — requer ação do usuário no painel de hospedagem (não há acesso SSH/deploy neste repositório).
5. `/api/search` só é referenciado em: [routes/web.php:11](routes/web.php#L11), [SearchBar.vue:76](resources/js/Components/SearchBar.vue#L76) e comentário em `GEMINI.md:44`. O frontend não usa nenhuma outra rota `/api/*`.

## Correções

### 1. Mover o endpoint de `/api/search` para `/search` (código)

O prefixo `/api/` é inutilizável no edge da hospedagem (307 em loop). Trocar o caminho da rota (o nome `api.search` pode ser mantido — não é usado por `route()` em lugar nenhum):

- [routes/web.php:11](routes/web.php#L11): `Route::get('/api/search', ...)` → `Route::get('/search', ...)` (mesma posição, antes da catch-all).
- [SearchBar.vue:76](resources/js/Components/SearchBar.vue#L76): `axios.get('/api/search', ...)` → `axios.get('/search', ...)`.

### 2. Estado de erro honesto no SearchBar (código)

Em [SearchBar.vue](resources/js/Components/SearchBar.vue):

- Adicionar ref `search_error`.
- No `catch` do axios: `search_error.value = true`; no sucesso: `search_error.value = false`.
- Renderizar "Erro ao buscar. Tente novamente." quando `search_error` for `true` (em vez do "Nenhum resultado encontrado", que só deve aparecer em resposta 200 vazia).
- Limpar `search_error` ao digitar novamente (no início de `handle_input`).

Assim, qualquer falha futura (504, edge, rede) fica visível e diagnosticável em vez de parecer "sem resultados".

### 3. Teste de regressão (código)

Criar `tests/Feature/SearchTest.php` (Pest): `GET /search?q=git&lang=en` → `assertOk()` + JSON não vazio; `GET /search?q=zzzz` → JSON `[]`. Segue padrão dos testes de Feature existentes. Não usa banco.

### 4. Ajuste de doc (menor)

`GEMINI.md:44` — atualizar o comentário da estrutura de rotas (`/api/search` → `/search`).

## Ações do usuário na hospedagem (fora do repositório — não executáveis daqui)

O repositório não contém credenciais/config de deploy. Após as correções acima:

1. **Reiniciar o PHP no painel da hospedagem** (hPanel → Restart PHP / matar processos PHP) — produção estava 504 em todas as rotas.
2. **Publicar os arquivos alterados** (`routes/web.php`, `resources/js/...` compilado via `npm run build`, `tests/Feature/SearchTest.php`) e limpar cache se houver: `php artisan route:clear` (e `route:cache`/`config:cache` se o hosting usa).
3. **Verificar**: `curl "https://gitpr.natanfiuza.dev.br/search?q=git&lang=pt_br"` deve retornar JSON com resultados.
4. Se a busca retornar `[]` com o site no ar: conferir via FTP se `/content/menu.json` e os `.md` batem com o repositório (a sincronização recente de docs pode ter deixado o servidor inconsistente).
5. Sem necessidade de rodar nada localmente além do normal (`npm run build` / `composer run dev`).

## Verificação (local)

1. `php artisan route:list` — confirmar `search` registrada antes da catch-all.
2. Subir servidor temporário em porta livre (8000 está ocupada por outro projeto): `php artisan serve --port=8001` e conferir:
   - `curl "http://localhost:8001/search?q=git&lang=pt_br"` → 200 JSON com resultados.
   - `curl "http://localhost:8001/api/search?q=git"` → passa a cair na catch-all (404 HTML) — esperado, nada mais usa `/api/`.
3. `php artisan test` (roda o novo `SearchTest` + suite existente).
4. `npm run build` — sem erros de compilação (se aplicável, conferir visualmente no navegador que o estado de erro vs. vazio aparece corretamente).

## Relatório

Ao final, escrever relatório em `docs/claude-code/reports/develop_natan/2026-09-08_develop_natan_corrige_busca_barra_superior.md` (regra do CLAUDE.md).
