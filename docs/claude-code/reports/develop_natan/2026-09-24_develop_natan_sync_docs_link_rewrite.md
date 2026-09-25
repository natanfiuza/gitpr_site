# Relatório — reescrita de links cruzados `.md` na skill `sync-docs`

- **Data**: 2026-09-24
- **Branch**: `develop_natan`
- **Task**: `sync_docs_link_rewrite`
- **Skills**: `/grill-with-docs` (grilling + domain-modeling)
- **Plano**: `docs/plans/20260924_rescrita_links_cruzados_md.md`
- **Survey**: `docs/survey/20260924_sync_docs_link_rewrite_surveyfacts.md`

## 1. Pedido

> Adicione uma nova regra na skill sync-docs.
> - Todo arquivo carregado deve ser verificado, nesta verificação localizar links para outros arquivos .md e substituir para a url exemplo:
> commit-message-ia.pt_br.md substituir para commit-message-ia?lang={lang}
> Onde {lang} vai ser substituido pela linguagem correspondente

## 2. O que foi entregue

Uma regra na skill `sync-docs` que converte o destino dos links cruzados `.md` para a URL canônica do site, em duas camadas: um script que reescreve o conteúdo na sincronização (a camada que resolve o problema) e uma regra no renderizador (rede de segurança para conteúdo ainda não reescrito).

| Arquivo | Ação |
| --- | --- |
| `.claude/skills/sync-docs/rewrite_doc_links.py` | **Novo.** CLI: `--root`, `--check`, `--source`, `--self-test`, `--quiet`. Exit 0 ok / 1 pendência / 2 erro de uso |
| `.claude/skills/sync-docs/SKILL.md` | Nova seção `## Reescrever links cruzados (CRÍTICO)`; passo 2 manda rodar o script por último; passo 3 troca o `diff -rq` por dois `--check`; `Observações` ganha a exceção do destino dos links |
| `resources/js/Components/MarkdownViewer.vue` | Prop `current_lang`; `slugify` hoisted para o escopo do módulo (era duplicado dentro do `watch`); regra `renderer.rules.link_open`; `scroll_to_hash()` |
| `resources/js/Pages/DocsLayout.vue` | Passa `:current_lang` ao `MarkdownViewer` |
| `public/content/docs/*.md` | **135 arquivos, 504 destinos** reescritos |
| `CONTEXT.md` | **Novo.** Glossário: tópico, variante, sufixo de idioma, canônico, monolíngue, legado, link cruzado, reescrita, idioma do destino |

### A transformação

| Na fonte | No site |
| --- | --- |
| `foo.md` | `/docs/foo` |
| `foo.pt_br.md` (num arquivo `pt_br`) | `/docs/foo?lang=pt_br` |
| `../README.md` | `/docs/readme[?lang=…]` |
| `docs/foo.md` (só nos `readme.*`) | `/docs/foo[?lang=…]` |
| `foo.md#Invocación Directa` | `/docs/foo[?lang=…]#invocacion-directa` |
| `plans/*.md`, `tutorial/*.md` | `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/<caminho verbatim>` |
| `https://…`, `mailto:`, `/…`, `#…`, `../src/*.py` | inalterado |

`en` omite o `?lang=`. O idioma do destino é o do **arquivo que contém o link**, sem checagem de existência (o controller já cai no inglês). O texto visível do link nunca muda — 184 links do corpus usam o nome do arquivo como texto.

## 3. Verificação

| Item | Resultado |
| --- | --- |
| `--self-test` | **36/36** |
| Dry-run | 212 arquivos varridos, 135 stale, **504 destinos** |
| Idempotência (`sha256` concatenado 1ª × 2ª passada) | **idêntico** — `11bef545132c1a51847ce515eb8a5dd85ea24dbc1c906a051d8e7ea76cbb6d12`; `git diff --cached` vazio |
| `--check` local | **exit 0** |
| Varredura independente de links relativos `.md` | **0** (eram 770) — 307 destinos terminam em `.md` e todos são absolutos ou já reescritos |
| `--check --source` | **exit 1** — 4 divergências reais, ver §4.1 |
| Navegador, 9 formas num fixture descartável | todas corretas; guardas (externo, âncora local, `/docs/…`) preservadas |
| Navegador, `?lang=` | presente em `pt_br`, **ausente** em `en` |
| Navegador, âncora | `/docs/mcp-integration?lang=pt_br#invocacao-direta-via-cli` — **sem acento**, como previsto |
| Navegador, scroll | via CDP: `window_scrollY` 0 → 1369 e heading de y=1369 → **y=0** no viewport, só com o fragmento |
| `npm run build` | ok (4.85s) |

### Como a idempotência é garantida

Por construção, não por coincidência: a guarda de "não mexer" é **por prefixo** (`http://`, `https://`, `//`, `mailto:`, `/`, `#`) e não por "termina em `.md`" — as URLs do GitHub emitidas pelo script também terminam em `.md` e seriam reescritas na segunda passada.

O I/O é feito em **bytes**: 31 arquivos do diretório são CRLF e 7 não terminam em newline, e o `.gitattributes` do repo tem `eol=lf`, o que faz o git **esconder** churn de CRLF. Um teste de idempotência baseado só no `git diff` passaria com o conteúdo corrompido — por isso o hash `sha256` é a prova principal.

## 4. Achados

### 4.1 Lacuna de sync pré-existente (não é regressão)

`--check --source` reporta 4 divergências reais:

```
DIVERGE  otimizacao-de-tokens.{es,fr,pt_br,pt_pt}.md: ausente no site
```

A fonte tem 5 variantes desse tópico; o site publicou só a inglesa. A lista de monolíngues do `SKILL.md` ainda o classifica como monolíngue — está defasada, como o survey de 2026-09-07 já apontava. **Não foi corrigido nesta task** (mudança cirúrgica). É trabalho para a próxima execução do `/sync-docs`: copiar as 4 variantes e atualizar a nota.

### 4.2 `public/hot` obsoleto faz o site não hidratar

`public/hot` existe desde 3/ago apontando para `[::1]:5173` e não é rastreado pelo git. Com ele presente, o `@vite` ignora o build de produção: `php artisan serve` sozinho serve HTML sem CSS/JS, e a página não renderiza o markdown. Foi preciso subir o `npm run dev` para a verificação de navegador. Não é consequência desta task, mas é uma armadilha para quem for testar o resultado.

### 4.3 `public/build` está versionado

São 30 arquivos rastreados. O rebuild exigido para aplicar as mudanças do Vue trocou os nomes com hash: **26 deleções + 26 adições + 1 modificação**. Esperado, mas polui o diff — vale decidir se o build deveria estar no `.gitignore`.

### 4.4 `--screenshot` do Chrome headless não serve para testar âncoras

Qualquer URL com `#` devolve imagem em branco (4829 bytes), inclusive `#ferramentas-disponiveis`, com ou sem GPU — com `--dump-dom` na mesma URL renderizando normal. A prova de scroll saiu por CDP (`Runtime.evaluate` medindo `scrollY` e `getBoundingClientRect`). Registrado para não se perder tempo com isso de novo.

## 5. Notas

- **Links cruzados viram `<a href>` comum**, não `<Link>` do Inertia: navegação com reload completo. Coerente com o precedente já escrito à mão em `chat-interativo.pt_br.md:21`.
- **Duas implementações da mesma regra** (`rewrite_destination()` no Python e `rewrite_md_target()` no Vue) precisam andar juntas. Ambas estão comentadas apontando uma para a outra. O `--self-test` cobre 36 casos do lado Python; a paridade do lado Vue foi verificada em navegador.
- **O plano foi salvo sem o sufixo `_plansfacts`** previsto na tabela de artefatos: `docs/plans/` já tinha convenção própria (`20260924_traducao_split_command.md`) e o conteúdo é o mesmo. Um arquivo, não dois.
- **Fixtures de teste removidos** (`zzz_linktest.*`) e servidores de teste encerrados por PID específico.
