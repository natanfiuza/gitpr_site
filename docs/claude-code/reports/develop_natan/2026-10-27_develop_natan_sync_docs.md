# Sincronização da documentação técnica — 2026-10-27

- **Branch:** `develop_natan`
- **Skill:** `sync-docs`
- **Fonte (autoridade):** `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr` (`docs/*.md` de nível superior + `README*.md` na raiz)
- **Destino:** `public/content/docs/*.md` + `public/content/menu.json`

## Diagnóstico

Comparação fonte × site com `rewrite_doc_links.py --check --source <gitpr>`, que aplica o mapeamento de sufixos (`.es_es` → `.es`, `.fr_fr` → `.fr`) e a reescrita de links aos dois lados antes de comparar.

| Situação | Tópicos |
|---|---|
| Faltando no site | nenhum |
| Conteúdo divergente (atualizados) | `readme` — 5 variantes |
| Variantes de idioma faltando | nenhuma nos tópicos comuns |
| Tópicos sem entrada no menu | nenhum (55/55 cobertos) |
| Menus sem tópico | nenhum |
| Legados `.es_es`/`.fr_fr` no site | nenhum |

Resultado na fonte do diagnóstico: 244 arquivos comparados, **5 divergentes** — apenas os `readme.*`. Os outros 239 estavam em sincronia.

### Natureza da divergência dos READMEs

Só ~12 linhas de prosa mudaram por arquivo; o restante era forma de link. A fonte reescreveu os links de documentação de `docs/foo.md` (relativo) para a URL pública do site (`https://gitpr.natanfiuza.dev.br/docs/foo`), como texto visível **e** como destino. O `README.md` inglês também removeu `?lang=en_us` de 2 links — código inválido que já vinha sendo copiado para o site.

### Defeito encontrado nos READMEs traduzidos (corrigido na fonte)

`README.es_es.md` e `README.fr_fr.md` geravam links para o site com os códigos de locale **internos do CLI** em vez dos do site:

| Arquivo | Links afetados | Antes | Depois |
|---|---|---|---|
| `README.es_es.md` | 73 | `?lang=es_es` | `?lang=es` |
| `README.fr_fr.md` | 72 | `?lang=fr_fr` | `?lang=fr` |

O site resolve apenas `en`, `pt_br`, `pt_pt`, `es`, `fr` ([DocsController.php:31-38](app/Http/Controllers/DocsController.php#L31-L38)). Com `es_es`/`fr_fr` o arquivo não existe, o controller cai no fallback inglês e o `current_lang` fica inválido — ou seja, ~145 links dos dois READMEs abririam a documentação em inglês. O `MarkdownViewer.vue` não corrige isso: sua regra de `link_open` só reescreve destinos **relativos** `.md`, e essas URLs são absolutas.

Correção aplicada na **fonte** (decisão do usuário), não no site — assim os dois repositórios continuam idênticos e o `--check --source` segue limpo.

`README.pt_br.md` e `README.pt_pt.md` já usavam `?lang=pt_br`/`?lang=pt_pt`, que são códigos válidos do site — não foram tocados.

## Ações executadas

1. **Fonte corrigida** — `gitpr/README.es_es.md` (71 linhas) e `gitpr/README.fr_fr.md` (70 linhas): apenas `?lang=es_es` → `?lang=es` e `?lang=fr_fr` → `?lang=fr`. Verificado por round-trip: desfazendo a substituição, o arquivo volta a ser idêntico ao `HEAD` (ignorando normalização de CRLF do git). Nenhuma outra ocorrência de `es_es`/`fr_fr` foi tocada — `GITPR_LANG=es_es`, os exemplos de `--lang <código>`, o link `docs/tutorial/install-from-source.es_es.md` e a nota sobre os 5 idiomas continuam intactos, porque descrevem o CLI, não o site.
2. **READMEs copiados** — 5 variantes sobrescreveram `readme.*` byte a byte, com renomeação de sufixo:

   ```
   README.md          -> readme.md
   README.pt_br.md    -> readme.pt_br.md
   README.pt_pt.md    -> readme.pt_pt.md
   README.es_es.md    -> readme.es.md
   README.fr_fr.md    -> readme.fr.md
   ```

   Nenhum sufixo `.es_es`/`.fr_fr` foi criado no site.
3. **Links cruzados** — `python .claude/skills/sync-docs/rewrite_doc_links.py` → **5 arquivos reescritos, 5 links**. Todos do mesmo tipo: o link relativo `docs/tutorial/install-from-source.*.md` (subdiretório ignorado pela skill) passou para a URL do GitHub com o sufixo de origem preservado, ex.: `/blob/main/docs/tutorial/install-from-source.es_es.md`.
4. **Menu** — nenhuma alteração necessária: nenhum tópico novo, e os 55 tópicos do site já têm entrada nas 5 línguas.

## Verificação

| Checagem | Resultado |
|---|---|
| `rewrite_doc_links.py --check` | exit 0 — 0 arquivos desatualizados |
| `rewrite_doc_links.py --check --source <gitpr>` | exit 0 — **244 arquivos comparados, sem divergência** |
| Cobertura de menu | 55 tópicos, 0 sem menu, 0 menus sem tópico |
| Códigos `?lang=` no site | só `es`, `fr`, `pt_br`, `pt_pt` (a única ocorrência de `fr_fr` restante está em prosa do `config-tui.fr.md`, descrevendo o CLI) |
| Legados `.es_es`/`.fr_fr` no site | nenhum |
| Fins de linha | 31 arquivos com CRLF — mesmo número de antes, nenhuma regressão |

Total: **55 tópicos, 256 arquivos** em `public/content/docs/`.

### Testes

```
Tests: 8 failed, 50 passed (58) — Assertions: 222 — Duration: ~4.7s
```

As 8 falhas são as pré-existentes de `Auth/*` e `ProfileTest` (`Expected 200 but received 404`), causadas pela rota catch-all [routes/web.php:27](routes/web.php#L27). **Nenhuma falha nova** — a mudança é só de conteúdo Markdown.

## Lacunas e anomalias reportadas (sem ação)

- `review-pr`: a fonte só tem `.md` e `.pt_br.md` — faltam `pt_pt`, `es`, `fr`. Traduções não foram inventadas.
- `github-issue-prompt-com-gh` e `version-markers`: monolíngues na fonte (só `.md`); site idem.
- `caveman-commit`, `como_reverter_commit_git_localmente`: só existem em inglês no site. Os dois estão em `docs/extra/` na fonte (subdiretório ignorado pela skill).
- `chat-interativo` e `testar_sem_usar_pypi`: exclusivos do site, mantidos intactos.
- **Anomalia fora do escopo do sync:** o mesmo defeito de locale que afetava os READMEs existe no código do CLI. [`src/doc_links.py:27`](../gitpr/src/doc_links.py#L27) monta a URL como `.../docs/<topico>?lang={CURRENT_LANG}`, e `CURRENT_LANG` vem de `src/i18n.py` com os nomes de `langs/*.json` — que incluem `es_es` e `fr_fr`. Sempre que o CLI roda com esses locales, o link aberto para o site cai no fallback inglês. A documentação em `config-tui.*.md` descreve esse comportamento fielmente, então o texto do doc está correto; o defeito é do código.

## Arquivos alterados

Site (5):

```
public/content/docs/readme.{md,pt_br.md,pt_pt.md,es.md,fr.md}
```

Fonte GitPR (2, no repo `gitpr`, branch `develop_natan`, não commitados):

```
README.es_es.md
README.fr_fr.md
```
