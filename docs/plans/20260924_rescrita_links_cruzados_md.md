# Reescrita de links cruzados `.md` na skill `sync-docs`

## Context

Os `.md` de [public/content/docs/](public/content/docs/) são cópia fiel da documentação do repo GitPR CLI e trazem links relativos entre arquivos — `[texto](commit-message-ia.md)`. No site isso não funciona: as páginas são servidas pela rota catch-all `/{page}?lang={code}` ([routes/web.php:27](routes/web.php#L27)), então o `href` relativo resolve para `/docs/commit-message-ia.md`, o controller procura `commit-message-ia.md.md` e devolve 404 ([DocsController.php:33-41](app/Http/Controllers/DocsController.php#L33-L41)).

**Não existe nenhuma camada de reescrita hoje** — [MarkdownViewer.vue:24](resources/js/Components/MarkdownViewer.vue#L24) usa markdown-it puro. Escala: **504 links em 135 arquivos na fonte; 770 ocorrências em 212 arquivos no site**, das quais 59 apontam para `*.es_es.md`/`*.fr_fr.md` (arquivo inexistente — no site é `.es.md`/`.fr.md`) e 10 têm âncora acentuada que nunca resolve.

O objetivo é uma regra na skill `sync-docs` que converta o destino de cada link para a URL canônica do site, mais uma rede de segurança em render-time.

## Decisões fechadas (12)

| #   | Decisão             | Resposta                                                                                                                                      |
| --- | ------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | Onde reescrever     | **Ambos** — script na skill (sync-time) + regra no Vue (render-time)                                                                          |
| 2   | Formato da URL      | Absoluto `/docs/{topico}?lang={lang}`; **`en` omite o query param**                                                                           |
| 3   | Idioma do destino   | O do **arquivo que contém o link**, derivado do sufixo dele. Sem checagem de existência — o controller já cai no inglês                       |
| 4   | Também reescrever   | `../README.md` → `/docs/readme`; normalizar `.es_es`→`.es`, `.fr_fr`→`.fr`. URLs absolutas externas: **não tocar**                            |
| 5   | Âncoras             | Manter o fragmento e normalizá-lo com o **mesmo slugify do site**                                                                             |
| 6   | Backfill            | Varredura idempotente de todo o diretório a cada execução                                                                                     |
| 7   | Verificação         | `diff` passa a comparar **depois** da transformação                                                                                           |
| 8   | Execução            | Script Python em `.claude/skills/sync-docs/`                                                                                                  |
| 9   | Artefatos           | Convenção do repo: `docs/plans/` e `docs/survey/` **planos** (sem subpasta de branch); relatório em `docs/claude-code/reports/develop_natan/` |
| 10  | Vue                 | Implementar nesta task                                                                                                                        |
| 11  | Links órfãos        | Apontar para o repositório de origem                                                                                                          |
| 12  | `CONTEXT.md`        | Criar nesta task                                                                                                                              |
| 13  | Scroll até a âncora | Incluir o handler de `location.hash` (sem ele a decisão #5 entrega metade)                                                                    |
| 14  | Blocos de código    | O script pula fence (` ``` ` / `~~~`) ao reescrever                                                                                           |

## Fatos verificados que moldam a implementação

1. **URL canônica**: `/{page}?lang={code}`, `{page}` já inclui o prefixo `docs/` ([menu.json:19](public/content/menu.json#L19)). Códigos: `en`, `pt_br`, `pt_pt`, `es`, `fr`. Prova em [LanguageSelector.vue:27-30](resources/js/Components/LanguageSelector.vue#L27-L30) e no precedente já escrito à mão em [chat-interativo.pt_br.md:21](public/content/docs/chat-interativo.pt_br.md#L21).
2. **Slugify do site** — [MarkdownViewer.vue:210-218](resources/js/Components/MarkdownViewer.vue#L210-L218), aplicado só a `h2`/`h3`: minúsculas → NFD → remove U+0300–U+036F → remove `[^a-z0-9\s-]` → trim → `\s+`→`-` → `-+`→`-`. Logo `## Invocação Direta via CLI` gera id `invocacao-direta-via-cli`; as 10 âncoras acentuadas do corpus **não resolvem hoje** (e [contribuicao.es.md:144](public/content/contribuicao.es.md#L144) já está quebrado em produção pelo mesmo motivo).
3. **184 links têm o nome do arquivo como texto visível** (`* [pull-request-publication.md](pull-request-publication.md)`) — o texto **nunca** pode ser alterado.
4. **Repositório de origem confirmado**: `https://github.com/gitpr-cli/gitpr` (via `git remote -v` no repo da CLI e no próprio README dele). A forma usada por 286 links já existentes no corpus é `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/<caminho>` — manter essa forma.
5. **Python 3.13.7** disponível como `python` e `py`. **`python3` é o stub quebrado da Microsoft Store** — o script deve ser invocado sempre como `python`.

### Três armadilhas encontradas na validação

- **markdown-it percent-encoda o `href` antes da regra `link_open`** (`mdurl.encode`), então o fragmento chega como `#invoca%C3%A7%C3%A3o-...`. Aplicar o slugify nisso produziria `invocac3a3o-direta-via-cli` — que não casa com heading nenhum. Exige `decodeURIComponent` com try/catch (um `%` literal não escapado lança `URIError` e derrubaria o render). O caso inglês (`#direct-cli-invocation`, ASCII) **esconde** o bug — só as 4 variantes traduzidas o expõem.
- **Sufixos `.es_es`/`.fr_fr` NÃO devem ser normalizados nos links órfãos.** A URL aponta para o repositório de origem, onde os arquivos têm esse nome. Normalizar quebraria os 6 links de `tutorial/install-from-source.*`.
- **`'docs/'` tem 5 caracteres, não 4.** Um off-by-one aqui produz `docs//auto-update.md` — que ainda termina em `.md` e portanto **passa** no teste de idempotência estando errado.

## Implementação

### 1. `.claude/skills/sync-docs/rewrite_doc_links.py` (novo)

```
python rewrite_doc_links.py [--root DIR] [--check] [--source DIR] [--self-test] [--quiet]
```

- `--root` padrão `<repo>/public/content/docs`, resolvido via `Path(__file__).parents[3]` — **não** relativo ao cwd.
- `--check`: dry-run, exit 1 se houver pendência. `--source`: compara fonte × site após a transformação. `--self-test`: tabela de asserções em processo.
- Exit codes: `0` ok, `1` pendência/divergência, `2` erro de uso/IO.

**Regra de reescrita** (`rewrite_destination(dest, lang)`) — uma única passada de `re.sub` com callback sobre `(?<!!)\[(?P<text>[^\]]*)\]\((?P<dest>[^)\s]*)(?P<title>\s+["'][^"']*["'])?\s*\)`, devolvendo o trecho original com **apenas o destino** trocado:

| Entrada                                                      | Saída                                                                      |
| ------------------------------------------------------------ | -------------------------------------------------------------------------- |
| `foo.md`                                                     | `/docs/foo`                                                                |
| `foo.{lang}.md`                                              | `/docs/foo?lang={idioma do arquivo}`                                       |
| `../README.md`                                               | `/docs/readme[?lang=…]`                                                    |
| `docs/foo.md` (só nos `readme.*`)                            | `/docs/foo[?lang=…]`                                                       |
| `foo.md#Invocación`                                          | `/docs/foo[?lang=…]#invocacion`                                            |
| `plans/*.md`, `tutorial/*.md`                                | `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/<caminho verbatim>` |
| `https://…`, `mailto:`, `/…`, `#…`, `../src/*.py`, `foo.txt` | inalterado                                                                 |

**Idempotência é estrutural, não coincidente.** A guarda de "não mexer" precisa ser **por prefixo** (`http://`, `https://`, `//`, `mailto:`, `/`, `#`) e **não** por "termina em `.md`" — as URLs do GitHub emitidas pelo script também terminam em `.md` e seriam reescritas na segunda passada. O texto inserido não contém `[`, `]`, `(`, `)` (os delimitadores de match), então os spans do segundo passe são idênticos.

**Slugify**: espelho exato do JS, com a classe de espaço do JS reproduzida literalmente (`\s` do JS difere do Python em U+FEFF). Validado contra o JS real rodando em Node sobre 23 headings reais: 0 divergências.

**I/O em bytes, nunca texto** — 31 arquivos do diretório são CRLF e 7 não terminam em newline. `open(..., 'w')` reescreveria o arquivo inteiro só pela normalização de newline do Windows. Isso importa porque o repo tem `.gitattributes` com `eol=lf`: o git **esconde** essa diferença, então um teste de idempotência via `git diff` passaria com o conteúdo corrompido.

**Pular blocos de código (fence)**: split em `^\s{0,3}(```|~~~)` e reescrever só fora. Hoje **não há nenhum link `.md` dentro de fence** (verificado nos 212 arquivos), então é invisível agora — mas markdown-it nunca parseia links dentro de fence, então sem isso as duas implementações divergiriam no dia em que um exemplo cercado contiver `[texto](foo.md)`, e o script estaria reescrevendo código que deveria aparecer verbatim. ~10 linhas, não afeta idempotência.

### 2. `resources/js/Components/MarkdownViewer.vue`

- **Hoistar `slugify`** de dentro do `watch` (linhas 210-218) para escopo de módulo, sem alterar o corpo. Duas cópias do algoritmo é exatamente a divergência que faz o fragmento parar de casar com o `id` gerado — o bug que estamos corrigindo.
- **Adicionar `current_lang`** ao `defineProps` (linhas 14-19), com `default: 'en'`.
- **Adicionar a regra `renderer.rules.link_open`** após o bloco `fence` (linha 78): decodifica o href, aplica a mesma transformação da tabela acima com `current_lang`, delega para o `link_open` original. É **no-op** em link já reescrito (a guarda de prefixo rejeita `/docs/…` e `https://…` antes do teste de `.md`), então não há como duplicar `?lang=`.
- **Adicionar o scroll até o fragmento** no mesmo `watch`, logo após a atribuição dos `id` (linha 229): ler `window.location.hash` e chamar `scrollIntoView` no elemento correspondente. É o que faz a decisão #5 valer — sem isso o `#invocacao-direta-via-cli` chega na página certa e para no topo, porque o browser tenta rolar antes de o Vue atribuir os `id` e não repete. Mesmo padrão de `scroll_to_first_mark()` (linha 146).

### 3. `resources/js/Pages/DocsLayout.vue`

Linha 103: passar `:current_lang="current_lang"`. `DocsLayout` já declara a prop (linha 181) e o controller já a envia ([DocsController.php:107](app/Http/Controllers/DocsController.php#L107)). `MarkdownViewer` tem **um único call site** no repo — nenhum outro consumidor a atualizar.

### 4. `.claude/skills/sync-docs/SKILL.md`

- **Nova seção `## Reescrever links cruzados (CRÍTICO)`** após o mapeamento de sufixos: o comando a rodar, a tabela de formas→URL, e as duas regras que não são óbvias (idioma do destino = do arquivo que contém o link, sem checagem de existência; sufixo de origem **preservado** nos links órfãos).
- **Passo 2 (Sincronizar)**: sub-item final mandando rodar o script depois de copiar/atualizar.
- **Passo 3 (Verificar)**: substituir o bullet do `diff -rq` por dois comandos `--check` (um local, um com `--source`). O diff cru não funciona mais: acusaria diferença em todo arquivo com link cruzado e nem sequer casaria `.es_es.md` com `.es.md`.
- **`Observações`**: na linha 61, `Preservar o conteúdo markdown exatamente como está na fonte (emojis, tabelas, links, blocos de código)` → trocar `links` por `blocos de código` e acrescentar a exceção do destino dos links, mantendo explícito que o texto visível nunca muda.

### 5. Backfill

Rodar o script sobre [public/content/docs/](public/content/docs/) — primeira passada reescreve 135 arquivos / 504 destinos. Ver §Verificação para o teste de idempotência.

### 6. `CONTEXT.md` (novo, raiz do repo)

Glossário do domínio de documentação: **tópico**, **variante**, **canônico**, **monolíngue**, **legado** (`.es_es`/`.fr_fr`), **link cruzado**, **reescrita**. Sem detalhes de implementação. Motivo: o survey de 2026-09-07 registrou que "canônico" e "monolíngue" já geraram ambiguidade — os termos hoje só existem espalhados no texto da skill.

## Artefatos da sessão

| Arquivo                                                                                     | Ação                                                                           |
| ------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------ |
| `docs/plans/20260924_sync_docs_link_rewrite_plansfacts.md`                                  | Criar (plano da sessão, na raiz de `docs/plans/` conforme a convenção do repo) |
| `docs/survey/20260924_sync_docs_link_rewrite_surveyfacts.md`                                | Criar (levantamento completo: contexto, 3 rodadas de decisões, fatos)          |
| `docs/claude-code/reports/develop_natan/2026-09-24_develop_natan_sync_docs_link_rewrite.md` | Criar (regra de Reports do CLAUDE.md)                                          |
| `CONTEXT.md`                                                                                | Criar (glossário)                                                              |

> O sufixo `_plansfacts` segue a regra de auto-save do `grill-with-docs`; os arquivos já existentes em `docs/plans/` não o usam. Mantive o sufixo por ser a regra que rege este arquivo — ajusto se preferir alinhar com os existentes.

## Verificação

1. **Dry-run**: `python .claude/skills/sync-docs/rewrite_doc_links.py --check` → 135 arquivos, 504 links, exit 1.
2. **Idempotência** (o teste que de fato prova): rodar o script → `git add -A -- public/content/docs` → rodar de novo → `git diff --cached --stat -- public/content/docs` **não imprime nada**. Usar `--cached`, porque o `.gitattributes` com `eol=lf` esconde churn de CRLF do `git diff`.
3. **Hash byte a byte**: `sha256` concatenado de todos os `*.md` antes e depois da 2ª execução — tem de ser idêntico (pega o que o git normaliza).
4. **Fonte × site**: `python .claude/skills/sync-docs/rewrite_doc_links.py --check --source "C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr"` → sem linhas `DIVERGE`, exit 0. (A validação mostrou que 135 das 204 divergências atuais correspondem 1:1 aos arquivos que a reescrita toca — depois da reescrita o check fica verde e passa a reportar só drift real de conteúdo.)
5. **Varredura de links quebrados**: script inline que conta hrefs relativos terminados em `.md` no site → tem de dar **0**.
6. **Self-test**: `python .claude/skills/sync-docs/rewrite_doc_links.py --self-test` → 32 asserções (slugify, 20 formas de reescrita, idempotência do próprio output de cada caso).
7. **Navegador** — `npm run dev` e, numa cópia temporária de um arquivo com link cru de volta:
   - `/docs/chat-interativo` → `<a href="/docs/commit-message-ia">`, nunca `commit-message-ia.md`.
   - `/docs/chat-interativo?lang=pt_br` → `href="/docs/commit-message-ia?lang=pt_br"` (prova que a prop `current_lang` chega no componente) e **exatamente um** `?lang=`.
   - `/docs/mcp-annotations?lang=pt_br` → `href="/docs/mcp-integration?lang=pt_br#invocacao-direta-via-cli"` **sem acento**. Testar em `pt_br`, não na página padrão: o caso inglês é ASCII e esconde o bug do percent-encoding.
   - Clicar nesse link e confirmar que a página **rola até a seção** (exercita o handler de `location.hash`, decisão #13) e que `document.getElementById('invocacao-direta-via-cli')` retorna o heading.
   - Reverter o arquivo temporário com `git checkout --` depois.

## Fora de escopo (consequências conhecidas, para não virarem surpresa)

- Links internos viram `<a href>` comum, não `<Link>` do Inertia — navegação com reload completo. Coerente com o precedente já existente em `chat-interativo.pt_br.md:21`.
- Duas notas defasadas no `SKILL.md` **não** serão corrigidas (mudanças cirúrgicas): a lista de monolíngues cita `otimizacao-de-tokens`, que hoje tem 5 variantes na fonte (lacuna real de sync, será reportada na próxima execução); e o diagnóstico de "legados `.es_es`/`.fr_fr`" sempre retorna vazio (são 0).

---

## Resultado da execução (2026-09-24)

Todas as decisões foram implementadas. Onde a previsão do plano divergiu do que a execução mediu, vale o que está abaixo.

| Verificação | Previsto | Medido |
| --- | --- | --- |
| Dry-run | 135 arquivos, 504 links, exit 1 | **igual** (212 varridos, 135 stale, 504 destinos) |
| Idempotência (`sha256` 1ª × 2ª passada) | idêntico | **idêntico** (`11bef545…`); `git diff --cached` vazio |
| Varredura de links relativos `.md` | 0 | **0** (eram 770) |
| Self-test | 32 asserções | **36** — acrescentei 4 casos ao fechar as armadilhas do `decodeURIComponent` e do `docs/` de 5 caracteres |
| `--check` local | exit 0 | **exit 0** |
| `--check --source` | exit 0, sem `DIVERGE` | **exit 1 com 4 `DIVERGE`** — ver abaixo |
| Navegador | 4 casos | **9 formas de link** num fixture + o caso real previsto + scroll |

**O `--source` não ficou verde, e não era para ficar.** Ele reporta `otimizacao-de-tokens.{es,fr,pt_br,pt_pt}.md: ausente no site` — 4 variantes que existem na fonte e nunca foram publicadas. É a lacuna real de sync que o §Fora de escopo já antecipava ("a lista de monolíngues cita `otimizacao-de-tokens`, que hoje tem 5 variantes na fonte"), agora com nome e endereço. Não é efeito desta task: rodando o script ou não, essas 4 linhas aparecem. Fica para a próxima execução do `/sync-docs`.

**Desvio na verificação de navegador.** O plano previa reverter um arquivo real (`chat-interativo`) com `git checkout --` para testar a regra do Vue. Não serve: `chat-interativo.pt_br.md` não tem nenhum link `.md` cru, e o backfill ainda não estava commitado — um `git checkout --` teria restaurado a versão *anterior à reescrita* e perdido o trabalho. No lugar disso usei um fixture descartável (`zzz_linktest.{md,pt_br.md}`, removido ao final) cobrindo as 9 formas numa página só, mais as 3 guardas. Ganho colateral: as guardas (link externo, âncora local, `/docs/…` absoluto) passaram a ter prova em navegador, o que o plano não pedia.

**Descobertas da execução que o plano não previa:**

1. **O container que rola é o `window`, não o `<main>`.** Verificado por CDP: `window_scrollY` vai de 0 → 1369 e o heading sai de y=1369 para y=0 no viewport. Irrelevante para o usuário, mas foi o que fez a primeira leitura do teste dar falso negativo.
2. **`--screenshot` do Chrome headless devolve imagem em branco para qualquer URL com `#`** — inclusive `#ferramentas-disponiveis`, com ou sem GPU. Peculiaridade do Chrome; o `--dump-dom` na mesma URL renderiza normal. Por isso a prova de scroll acabou saindo por CDP, não por screenshot.
3. **`public/hot` está obsoleto desde 3/ago** (`[::1]:5173`), e o arquivo não é rastreado pelo git. Com ele presente, `@vite` ignora o build de produção e a página não hidrata: o `php artisan serve` sozinho serve HTML sem CSS/JS. Não é consequência desta task, mas foi o que exigiu subir o `npm run dev` para a verificação de navegador.

**Arquivo do plano.** Salvo como `docs/plans/20260924_rescrita_links_cruzados_md.md`, no padrão dos irmãos da pasta (`20260924_traducao_split_command.md`), e não com o sufixo `_plansfacts` previsto na tabela de artefatos acima. Um arquivo, não dois: `_plansfacts` é a regra do `grill-with-docs`, mas a pasta já tinha convenção própria e o conteúdo é o mesmo.
