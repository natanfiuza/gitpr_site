# Execução do `/sync-docs` — fechar a lacuna de `otimizacao-de-tokens`, entradas de menu e notas defasadas

## Context

Esta é a **primeira execução real** do `/sync-docs` depois de a skill ganhar a regra de reescrita de links cruzados (task anterior, já mergeada: `.claude/skills/sync-docs/rewrite_doc_links.py`). O diagnóstico da skill rodou sobre as duas árvores e encontrou **uma única lacuna de conteúdo** — o tópico `otimizacao-de-tokens`, que a fonte tem em 5 variantes e o site publicou só em inglês — mais três notas defasadas dentro do próprio `SKILL.md`, que a execução anterior deixou registradas como pendência ("Não foi corrigido nesta task (mudança cirúrgica). É trabalho para a próxima execução do `/sync-docs`", relatório de 2026-09-24, §4.1).

Durante o diagnóstico o usuário corrigiu a raiz do problema na fonte: as 4 traduções que estavam **0 bytes** foram preenchidas, e o `otimizacao-de-tokens.md` inglês — que continha texto em português — foi reescrito em inglês. O resultado tem uma consequência que muda o escopo desta execução:

> O `.md` do site ficou **desatualizado** (ainda tem o texto português de quando a fonte era português). São **5 arquivos** a sincronizar, não 4.

### Estado verificado agora (2026-09-24)

```
--check                        → 212 varridos, 0 desatualizados, exit 0
--check --source               → 200 comparados, 5 DIVERGE, exit 1
    DIVERGE  otimizacao-de-tokens.es.md     : ausente no site
    DIVERGE  otimizacao-de-tokens.fr.md     : ausente no site
    DIVERGE  otimizacao-de-tokens.pt_br.md  : ausente no site
    DIVERGE  otimizacao-de-tokens.pt_pt.md  : ausente no site
    DIVERGE  otimizacao-de-tokens.md        : site='# Documentação Técnica: Otimizaç' fonte='# Technical Documentation: Token'
```

Fora dessa lacuna, **o site e a fonte estão idênticos** nos 200 arquivos comparáveis (depois do mapeamento de sufixos e da reescrita de links). Não há drift de conteúdo a corrigir.

## Decisões do usuário nesta execução

| #   | Pergunta                                           | Resposta                                                          |
| --- | -------------------------------------------------- | ----------------------------------------------------------------- |
| 1   | Arquivos de `otimizacao-de-tokens` vazios na fonte | **"recarregar os arquivos, o conteúdo foi preenchido"**           |
| 2   | Notas defasadas no `SKILL.md`                      | **Corrigir as três agora**                                        |
| 3   | `badge` e `demo` sem entrada no `menu.json`        | **Adicionar as entradas nas 5 línguas**                           |
| 4   | Os 4 arquivos de `otimizacao-de-tokens`            | "Também traduzir os 4 arquivos" — **prejudicada pela resposta 1** |

**A resposta 4 não será executada, e o motivo é a própria resposta 1.** Traduzir os 4 arquivos sobrescreveria as traduções que o usuário acabou de escrever na fonte com traduções minhas, de qualidade inferior e fora da autoridade de conteúdo (o `SKILL.md` é explícito: *"A fonte é a autoridade de conteúdo; o site nunca deve divergir dela nos tópicos comuns"*). A resposta 1 descreve exatamente o trabalho que a resposta 4 pedia — ele já foi feito, na fonte, por quem tem autoridade sobre o texto. O que resta é **propagar** o conteúdo para o site, que é a etapa 1 abaixo. Verificação de que as traduções estão completas e não são stubs: paridade estrutural nas 5 variantes (3 `##`, 0 `###`, 4 blocos de código, 0 tabelas), com H1 correto em cada idioma — es 4429 B, fr 4821 B, pt_br 4309 B, pt_pt 4328 B, en 4091 B.

## 1. Sincronizar as 5 variantes de `otimizacao-de-tokens`

Copiar da fonte para o site, **sem alterar o conteúdo** (a reescrita de links é a etapa 2), aplicando o mapeamento de sufixos da skill:

| Fonte                                        | Destino no site                                      | Ação                                           |
| -------------------------------------------- | ---------------------------------------------------- | ---------------------------------------------- |
| `<gitpr>/docs/otimizacao-de-tokens.md`       | `public/content/docs/otimizacao-de-tokens.md`        | **sobrescrever** (4349 B em pt → 4091 B em en) |
| `<gitpr>/docs/otimizacao-de-tokens.pt_br.md` | `public/content/docs/otimizacao-de-tokens.pt_br.md`  | criar                                          |
| `<gitpr>/docs/otimizacao-de-tokens.pt_pt.md` | `public/content/docs/otimizacao-de-tokens.pt_pt.md`  | criar                                          |
| `<gitpr>/docs/otimizacao-de-tokens.es_es.md` | `public/content/docs/otimizacao-de-tokens.**es**.md` | criar — **renomear sufixo**                    |
| `<gitpr>/docs/otimizacao-de-tokens.fr_fr.md` | `public/content/docs/otimizacao-de-tokens.**fr**.md` | criar — **renomear sufixo**                    |

A sobrescrita do `.md` não perde nada: o conteúdo português que está lá hoje volta como `otimizacao-de-tokens.pt_br.md` na mesma operação — o que existe hoje no site é a variante pt_br **sob o nome inglês**, um erro que esta etapa corrige.

## 2. Reescrever os links cruzados

```bash
python .claude/skills/sync-docs/rewrite_doc_links.py
```

Rodar **depois** do passo 1, como manda a skill. Os 5 arquivos de origem têm **0 links `.md` relativos** (verificado), então é esperado que a passada não altere nada — mas o script varre o diretório inteiro e é idempotente, então isso é uma afirmação a confirmar, não a assumir: se os arquivos recém-copiados trouxerem qualquer link cru, é aqui que ele é pego.

## 3. `public/content/menu.json` — entradas de `badge` e `demo`

`badge` e `demo` existem no site com **5/5 variantes cada** mas não têm entrada no menu: 45 entradas `docs/*` para 47 tópicos, e o `grep` por `docs/badge`/`docs/demo` retorna 0. Sem entrada, as páginas existem e são servidas, mas são inalcançáveis pela navegação.

A ordem de `path` é **idêntica nas 5 línguas** (verificado), então a inserção é no mesmo índice em cada array. Título = H1 da variante daquele idioma sem o prefixo do título da seção, como no resto do menu (`# Technical Documentation: Code Archaeologist with Git Blame (--blame)` → `▸ Code Archaeologist (Blame)`):

**`docs/badge`** — inserir depois de `docs/auto-update`, antes de `docs/blame-arqueologo`:

| Língua  | `title`                      |
| ------- | ---------------------------- |
| `en`    | `▸ Pull Request Badge`       |
| `pt_br` | `▸ Selo de Pull Request`     |
| `pt_pt` | `▸ Selo de Pull Request`     |
| `es`    | `▸ Insignia de Pull Request` |
| `fr`    | `▸ Badge de Pull Request`    |

**`docs/demo`** — inserir depois de `docs/caveman-commit`, antes de `docs/git-hooks-locais`:

| Língua  | `title`                        |
| ------- | ------------------------------ |
| `en`    | `▸ Guided Tour (gitpr demo)`   |
| `pt_br` | `▸ Tour Guiado (gitpr demo)`   |
| `pt_pt` | `▸ Visita Guiada (gitpr demo)` |
| `es`    | `▸ Tour Guiado (gitpr demo)`   |
| `fr`    | `▸ Visite Guidée (gitpr demo)` |

Formato exato, igual ao das entradas vizinhas: `{"title": "▸ <título>", "path": "docs/<tópico>"}`. Sob a seção existente ("Technical Documentation" / "Documentação Técnica" / "Documentation Technique" / "Documentación Técnica") — não criar seção nova.

> Isto é uma **exceção deliberada** a uma regra da própria skill ("Tópicos pré-existentes sem entrada no menu: apenas reportar"). Aprovada na resposta 3. A regra não muda; a exceção fica registrada no relatório.

## 4. `.claude/skills/sync-docs/SKILL.md` — as três notas defasadas

1. **Lista de monolíngues** — `otimizacao-de-tokens` sai (a fonte agora tem 5 variantes; era a lacuna que esta execução fecha). Recomputado da fonte: os monolíngues reais são `github-issue-prompt-com-gh` e `version-markers` (só `.md`). `review-pr` tem 2 variantes (`.md`, `.pt_br.md`) — a fonte não tem as outras 3, é lacuna da fonte, não do sync.
2. **Mesma lista** — `como_reverter_commit_git_localmente` e `testar_sem_usar_pypi` saem: os dois **migraram para `docs/extra/`** na fonte (verificado; só a versão `.md` existe lá). Como `extra/` é subdiretório ignorado, os dois deixaram de ser tópicos da fonte e o site passa a tê-los como **exclusivos do site**, ao lado de `chat-interativo` e `caveman-commit`.
3. **Lista de subdiretórios ignorados** — está incompleta. Os subdiretórios reais da fonte são 9: `claude-code/`, `extra/`, `gemini/`, `plans/`, `prompts/`, `reports/`, **`survey/`**, `testing/`, **`tutorial/`**. Faltam `survey/` e `tutorial/`. (`tutorial/` é justamente o subdiretório cujos links o script manda para o GitHub — a omissão na lista contradiz a seção de links.)

## 5. Relatório da tarefa

`docs/claude-code/reports/develop_natan/2026-09-24_develop_natan_sync_docs.md` (regra do `CLAUDE.md`), com: tópicos sincronizados, entradas de menu adicionadas, correções na skill, a decisão 4 prejudicada e o motivo, e os itens reportados sem alteração (abaixo).

## Verificação

1. `python .claude/skills/sync-docs/rewrite_doc_links.py --check` → **exit 0**.
2. `python .claude/skills/sync-docs/rewrite_doc_links.py --check --source "C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr"` → **exit 0, zero linhas `DIVERGE`** (hoje: 5). É o critério de aceite da execução inteira.
3. **Nenhum `.es_es`/`.fr_fr` criado no site**: `ls public/content/docs/*.es_es.md public/content/docs/*.fr_fr.md` → vazio.
4. **`menu.json` parseia e as entradas novas resolvem**: `python -c "import json;json.load(open(...,encoding='utf-8'))"` nas 5 línguas (um JSON inválido derruba o menu inteiro do site) + conferir que `docs/badge` e `docs/demo` apontam para arquivos que existem em `public/content/docs/` e que o nº de entradas subiu de 45 → 47 em cada língua.
5. **As 5 variantes casam byte a byte com a fonte** depois da transformação — é o próprio item 2; conferir explicitamente que `otimizacao-de-tokens.es.md`/`.fr.md` são os arquivos renomeados e não cópias vazias (`stat -c%s` > 0).
6. Sem mudança em Vue/JS: **não** é preciso `npm run build` — nenhuma alteração em `resources/`.

## Reportado, sem alteração

Ficam como estão, porque apagar é irreversível e a skill proíbe remoção sem confirmação explícita; vão descritos no relatório:

- **5 tópicos exclusivos do site**: `caveman-commit` (removido da fonte), `chat-interativo` (exclusivo por design), `readme` (≡ `README` da fonte, difere só em maiúsculas), `como_reverter_commit_git_localmente` e `testar_sem_usar_pypi` (migraram para `docs/extra/`).
- **Tópicos sem as 5 variantes**: `review-pr` (`.md` + `.pt_br.md`), `github-issue-prompt-com-gh` e `version-markers` (só `.md`). Lacuna da fonte — não se inventa tradução.
- **0 arquivos legados `.es_es`/`.fr_fr` no site** — nada a remover.
