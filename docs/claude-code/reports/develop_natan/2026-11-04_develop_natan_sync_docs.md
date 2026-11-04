# Sincronização da Documentação Técnica (`/sync-docs`)

- **Data:** 2026-11-04
- **Branch:** `develop_natan`
- **Skill:** `.claude/skills/sync-docs`
- **Fonte:** `gitpr/docs/*.md` + `gitpr/README*.md` (blobs commitados, `HEAD`)
- **Destino:** `public/content/docs/*.md`

## Resumo

A fonte tinha avançado bem além do site: **1 tópico novo**, **8 variantes de idioma faltando**
e **58 arquivos com conteúdo divergente**. Todos foram sincronizados. Estado final:
**252 arquivos fonte × site idênticos, 0 divergentes, 0 ausentes**.

## Tópico novo copiado

| Tópico | Variantes copiadas | Entrada no `menu.json` |
|---|---|---|
| `issue-autoclose` | `.md`, `.pt_br`, `.pt_pt`, `.es`, `.fr` | adicionada em 5 idiomas, após "Issue Creation Guide" |

Títulos usados no menu (formato `▸ <título>`):

- en — `Automatic Issue Closure`
- pt_br — `Fechamento Automático da Issue`
- pt_pt — `Fecho Automático da Issue`
- fr — `Fermeture Automatique de l'Issue`
- es — `Cierre Automático de la Issue`

## Variantes de idioma adicionadas a tópicos existentes

`review-pr` tinha apenas `.md` e `.pt_br.md` no site; a fonte passou a ter as 5 variantes.

- `review-pr.es.md`
- `review-pr.fr.md`
- `review-pr.pt_pt.md`

## Tópicos atualizados (58 arquivos, 14 tópicos)

Sobrescritos com o conteúdo da fonte, variante por variante.

| Tópico | Variantes atualizadas | Linhas (+/−) |
|---|---|---|
| `pull-request-publication` | 5 | +36 / −6 |
| `config-tui` | 5 | +27 / −5 |
| `scm-multiforge` | 5 | +24 / −3 |
| `issue-tui-help` | 5 | +23 / −2 |
| `readme` | 5 | +20 / −1 |
| `baseline-suppressions` | 5 | +18 / −4 |
| `metricas-telemetria` | 5 | +8 / −4 |
| `install-wizard` | 5 | +6 / −4 |
| `check-ci` | 5 | +3 / −0 |
| `pr-descricao-padrao` | 5 | +3 / −3 |
| `suggested-reviewers` | 5 | +3 / −3 |
| `ARCHITECTURE` | 1 (só `.md`) | +59 / −36 |
| `review-pr` | 2 (`.md`, `.pt_br`) | +3 / −3 |

As traduções de `ARCHITECTURE` já estavam em paridade com a fonte; só a variante inglesa
divergia.

## Links cruzados

`python .claude/skills/sync-docs/rewrite_doc_links.py` → **56 arquivos reescritos, 203 links**.

Verificação posterior: `--check` retorna exit 0 (nenhum link pendente).

## Verificação

| Checagem | Resultado |
|---|---|
| Paridade fonte × site (após mapeamento de sufixos + reescrita de links) | 252/252 idênticos, 0 divergentes, 0 ausentes |
| `rewrite_doc_links.py --check` | exit 0, 0 arquivos desatualizados |
| `rewrite_doc_links.py --self-test` | 36/36 ok |
| Sufixos legados `.es_es`/`.fr_fr` no site | nenhum |
| Entradas no `menu.json` | 67 por idioma, uniforme |

## Anomalias e lacunas reportadas

1. **`--check --source` acusa falso positivo de CRLF.** O passo de verificação do skill
   (`python rewrite_doc_links.py --check --source <gitpr>`) retorna exit 1 porque compara o
   texto do *working tree* da fonte sem normalizar fins de linha. Nesta máquina o repo
   `gitpr` está com `core.autocrlf=true`, então `docs/*.md` e `README*.md` aparecem com CRLF
   no disco, enquanto os blobs commitados (`git show HEAD:…`) são LF — mesma convenção do
   site (`* text=auto eol=lf`). A cópia foi feita a partir de `git show HEAD:` justamente
   para preservar LF. Comparação com normalização CRLF→LF: **0 divergências**. Sugestão:
   normalizar fins de linha em `compare_source()` para que o passo volte a ser confiável.
2. **Tópicos exclusivos do site** (não tocados):
   - `caveman-commit` — na fonte a referência é a skill `.claude/skills/caveman-commit`, não
     um doc; tem só a variante inglesa.
   - `chat-interativo` — deliberadamente fora do menu (a entrada "Interactive Chat (TUI)"
     aponta para `docs/understanding_chat_functionality`).
   - `como_reverter_commit_git_localmente` e `testar_sem_usar_pypi` — arquivos movidos para
     `gitpr/docs/extra/`, fora do escopo da sincronização.
3. **Lacunas de idioma vindas da fonte** (não houve invenção de tradução):
   `github-issue-prompt-com-gh` e `version-markers` são monolíngues na fonte (só `.md`).
4. Nenhum duplicado obsoleto `.es_es`/`.fr_fr` foi encontrado no site.

## Arquivos alterados

- 58 `.md` modificados em `public/content/docs/`
- 8 `.md` novos em `public/content/docs/` (`issue-autoclose` ×5, `review-pr` ×3)
- `public/content/menu.json` — 5 entradas novas (uma por idioma)
