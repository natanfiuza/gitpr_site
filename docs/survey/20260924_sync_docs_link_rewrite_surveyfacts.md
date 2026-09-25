# Survey de fatos — reescrita de links cruzados `.md` na skill `sync-docs`

- **Data**: 2026-09-24
- **Repo**: `gitpr_site` (c:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr_site)
- **Branch**: `develop_natan`
- **Task**: `sync_docs_link_rewrite`
- **Skill acionada**: `/grill-with-docs` → grilling + domain-modeling
- **Objeto**: skill `/sync-docs` (`.claude/skills/sync-docs/SKILL.md`)
- **Plano da sessão**: `docs/plans/20260924_rescrita_links_cruzados_md.md`
- **Arquivo**: gerado por regra de auto-save do `grill-with-docs` (`{YYYYMMDD}_{task_name}_surveyfacts.md`)

---

## 1. Contexto e decisões do grill

**Pergunta original (verbatim do usuário):**

> Adicione uma nova regra na skill sync-docs.
> - Todo arquivo carregado deve ser verificado, nesta verificação localizar links para outros arquivos .md e substituir para a url exemplo:
> commit-message-ia.pt_br.md substituir para commit-message-ia?lang={lang}
> Onde {lang} vai ser substituido pela linguagem correspondente

O pedido descreve uma reescrita de links relativos entre `.md` para a URL do site. O grill expandiu para as decisões que o pedido não fixava: onde reescrever, o que fazer com âncoras, com links órfãos, com blocos de código, e como verificar sem quebrar o `diff` que a skill já usava.

**Rodada 1 — escopo e formato da URL**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Q1 | Onde reescrever | **Ambos** — script na skill (sync-time) + regra no Vue (render-time) |
| Q2 | Formato da URL | **Absoluto** `/docs/{topico}?lang={lang}`; `en` omite o query param |
| Q3 | Idioma do destino | O do **próprio arquivo que contém o link** (sem checagem de existência) |
| Q4 | Casos especiais | `../README.md` → `/docs/readme`; normalizar `.es_es`→`.es`, `.fr_fr`→`.fr` |

**Rodada 2 — âncoras, idempotência e verificação**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Q5 | Âncoras | **Manter** o fragmento e normalizá-lo com o mesmo slugify do site |
| Q6 | Backfill | **Varredura idempotente** de todo o diretório a cada execução |
| Q7 | Verificação | O `diff` passa a comparar **depois** da transformação |
| Q8 | Execução | **Script Python** na pasta da skill |

**Rodada 3 — artefatos, escopo de implementação e links órfãos**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Q9 | Artefatos | **Seguir a convenção do repo** (`docs/plans/`, `docs/survey/`, `docs/claude-code/reports/{branch}/`) |
| Q10 | O Vue entra agora? | **Sim**, implementar nesta task |
| Q11 | Links órfãos (`plans/`, `tutorial/`) | **Apontar para o repositório de origem** |
| Q12 | `CONTEXT.md` | **Sim, criar** |

**Rodada 4 — itens revelados pela validação**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Q13 | Scroll até a âncora | **Incluir agora** — sem o handler de `location.hash` a decisão #5 entrega metade |
| Q14 | Blocos de código | **Incluir o skip de fence** (` ``` ` / `~~~`) na reescrita |

---

## 2. Relatório de fatos

### 2.1 O problema

Os `.md` de `public/content/docs/` são cópia fiel da documentação do repo GitPR CLI e trazem links relativos entre arquivos — `[texto](commit-message-ia.md)`. No site isso não funciona: as páginas são servidas pela rota catch-all `/{page}?lang={code}` (`routes/web.php`), então o `href` relativo resolve para `/docs/commit-message-ia.md`, o controller procura `commit-message-ia.md.md` e devolve 404 (`DocsController.php`).

**Não existia nenhuma camada de reescrita** — `MarkdownViewer.vue` usava markdown-it puro, sem regra `link_open`.

**Escala medida:** 504 links em 135 arquivos na fonte; **770 ocorrências em 212 arquivos no site**, das quais 59 apontavam para `*.es_es.md`/`*.fr_fr.md` (arquivo que não existe no site) e 10 tinham âncora acentuada que nunca resolvia.

### 2.2 Contrato da URL canônica

`/{page}?lang={code}` onde `{page}` já inclui o prefixo `docs/` (`menu.json`). Códigos: `en`, `pt_br`, `pt_pt`, `es`, `fr`. Confirmado por três fontes independentes: `DocsController.php`, `LanguageSelector.vue` e o precedente já escrito à mão em `public/content/docs/chat-interativo.pt_br.md` (`/docs/understanding_chat_functionality?lang=pt_br`).

### 2.3 Fatos que moldaram a implementação

1. **O slugify do site remove acentos** (`MarkdownViewer.vue`): minúsculas → NFD → remove U+0300–U+036F → remove `[^a-z0-9\s-]` → trim → `\s+`→`-` → `-+`→`-`. Aplicado só a `h2`/`h3`. Logo `## Invocação Direta via CLI` gera `invocacao-direta-via-cli` — as 10 âncoras acentuadas do corpus não resolviam.
2. **184 links têm o nome do arquivo como texto visível** (`[pull-request-publication.md](pull-request-publication.md)`) — o texto **nunca** pode ser alterado.
3. **Repositório de origem confirmado**: `https://github.com/gitpr-cli/gitpr`, verificado via `git remote -v` no repo da CLI. A forma usada pelos 286 links já existentes no corpus é `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/<caminho>` — mantida por consistência.
4. **Python 3.13.7** disponível como `python` e `py`. **`python3` é o stub quebrado da Microsoft Store** — o script é invocado sempre como `python`.
5. **Idioma do destino = o do arquivo que contém o link**, não o do arquivo apontado. Não há checagem de existência: o `DocsController` já cai no conteúdo inglês quando a variante traduzida não existe.

### 2.4 Três armadilhas encontradas na validação

- **markdown-it percent-encoda o `href` antes da regra `link_open`** (`mdurl.encode`), então o fragmento chega como `#invoca%C3%A7%C3%A3o-...`. Aplicar o slugify nisso produziria `invocac3a3o-direta-via-cli`, que não casa com heading nenhum. Exige `decodeURIComponent` em try/catch (um `%` literal não escapado lança `URIError` e derrubaria o render). **O caso inglês (`#direct-cli-invocation`, ASCII) esconde o bug** — só as 4 variantes traduzidas o expõem.
- **Sufixos `.es_es`/`.fr_fr` NÃO devem ser normalizados nos links órfãos.** A URL aponta para o repositório de origem, onde os arquivos têm esse nome (`docs/tutorial/install-from-source.es_es.md` existe; `.es.md` não). Normalizar viraria 6 links funcionais em 404.
- **`'docs/'` tem 5 caracteres, não 4.** Um off-by-one produz `docs//auto-update.md` — que ainda termina em `.md` e portanto **passa** no teste de idempotência estando errado.

E uma armadilha de verificação: o `.gitattributes` do repo tem `eol=lf`, então o `git` **esconde** churn de CRLF. Um teste de idempotência via `git diff` passaria com o conteúdo corrompido. Por isso o script faz I/O em **bytes** e a verificação usa hash `sha256` concatenado além do `git diff --cached` (31 arquivos do diretório são CRLF e 7 não terminam em newline).

### 2.5 Idempotência por construção

A guarda de "não mexer" é **por prefixo** (`http://`, `https://`, `//`, `mailto:`, `/`, `#`) e **não** por "termina em `.md`" — as URLs do GitHub emitidas pelo script também terminam em `.md` e seriam reescritas na segunda passada. O texto inserido não contém `[`, `]`, `(`, `)` (os delimitadores do match), então os spans do segundo passe são idênticos.

### 2.6 Lacuna de sync encontrada (pré-existente, não é regressão)

`--check --source` reporta 4 divergências reais: `otimizacao-de-tokens.{es,fr,pt_br,pt_pt}.md` existem na fonte e **estão ausentes no site** (só a inglesa foi publicada). A nota de monolíngues do `SKILL.md` ainda lista `otimizacao-de-tokens` como monolíngue — está defasada, como o survey de 2026-09-07 já apontava. Não foi corrigida nesta task (mudança cirúrgica); fica para a próxima execução do `/sync-docs`.

---

## 3. Estado final verificado

| Verificação | Resultado |
| --- | --- |
| `--self-test` | **36/36** asserções |
| Dry-run | 212 arquivos varridos, **135 stale**, **504 destinos** |
| Backfill aplicado | 135 arquivos |
| Idempotência (`sha256` antes/depois da 2ª passada) | **idêntico** (`11bef545…`) |
| `--check` local | exit **0** |
| Varredura independente de links relativos `.md` | **0** (eram 770); 307 destinos terminando em `.md` são todos absolutos ou já reescritos |
| `--check --source` | exit **1** — só as 4 lacunas de `otimizacao-de-tokens` (§2.6) |
| Navegador (DOM renderizado) | as 9 formas do fixture corretas, guardas preservadas, `?lang=` omitido no inglês, âncora sem acento |
| Navegador (scroll) | heading sai de y=1369 para y=0 no viewport **só** com o fragmento presente |
