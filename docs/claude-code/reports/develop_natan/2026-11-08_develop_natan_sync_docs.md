# Sincronização da Documentação Técnica (`/sync-docs`)

- **Data:** 2026-11-08
- **Branch:** `develop_natan`
- **Skill:** `.claude/skills/sync-docs`
- **Fonte:** `gitpr/docs/*.md` + `gitpr/README*.md` (blobs commitados, `HEAD`)
- **Destino:** `public/content/docs/*.md`

## Resumo

Desde a última sincronização (2026-11-04), a fonte ganhou **1 tópico novo**, **4 variantes de
idioma** e **37 arquivos variante com conteúdo divergente**. Todos foram sincronizados.

- 42 arquivos alterados no site (37 modificados + 5 novos)
- 257 variantes comparadas após normalização CRLF e reescrita de links: **0 divergências**
- 40 arquivos com links cruzados reescritos (283 links)

## Tópico novo copiado

| Tópico | Variantes copiadas | Entrada no `menu.json` |
|---|---|---|
| `marketplace-publishing-guide` | `.md` (a fonte só tem inglês) | adicionada em 5 idiomas, após "Editor Extension" |

A fonte não tem traduções deste tópico. Títulos usados no menu (formato `▸ <título>`):

- en — `Marketplace Publishing & Setup Guide (VS Code & Cursor)`
- pt_br — `Guia de Publicação e Configuração no Marketplace (VS Code & Cursor)`
- pt_pt — `Guia de Publicação e Configuração no Marketplace (VS Code & Cursor)`
- fr — `Guide de Publication et de Configuration du Marketplace (VS Code & Cursor)`
- es — `Guía de Publicación y Configuración en el Marketplace (VS Code & Cursor)`

## Variantes de idioma adicionadas a tópicos existentes

`version-markers` tinha apenas `.md` no site; a fonte passou a ter as 5 variantes.

- `version-markers.es.md` (122 linhas)
- `version-markers.fr.md` (123 linhas)
- `version-markers.pt_br.md` (122 linhas)
- `version-markers.pt_pt.md` (122 linhas)

## Tópicos atualizados (37 arquivos, 9 tópicos)

Sobrescritos com o conteúdo da fonte (`git show HEAD:`), variante por variante.

| Tópico | Variantes atualizadas | Linhas (+/−) |
|---|---|---|
| `check-ci` | 5 | en +112/−0 · pt_br +152/−0 · es/fr/pt_pt +182/−0 |
| `install-wizard` | 5 | en +44/−14 · pt_br +59/−29 · pt_pt +67/−37 · fr +74/−44 · es +76/−46 |
| `scm-multiforge` | 5 | +31/−13 (todas as variantes) |
| `release-notes` | 5 | +10/−0 (todas as variantes) |
| `smart-excludes` | 5 | +10/−0 (todas as variantes) |
| `ARCHITECTURE` | 5 | +3/−3 (todas as variantes) |
| `editor-extension` | 5 | +3/−0 (todas as variantes) |
| `version-markers` | 1 (`.md`) | +8/−8 |
| `metricas-telemetria` | 1 (`.md`) | +3/−1 |

As demais variantes de `version-markers` e `metricas-telemetria` já estavam idênticas à
fonte e não geraram diff.

## Links cruzados

`PYTHONIOENCODING=utf-8 python .claude/skills/sync-docs/rewrite_doc_links.py` →
**40 arquivos reescritos, 283 links**.

Verificação posterior: `--check` retorna exit 0 (nenhum link pendente).

## Verificação

| Checagem | Resultado |
|---|---|
| Paridade fonte × site (CRLF normalizado + reescrita de links) | 257/257 variantes idênticas, 0 divergentes, 0 ausentes |
| `rewrite_doc_links.py --check` | exit 0, 0 arquivos desatualizados (269 varridos) |
| Cobertura de variantes | todos os tópicos comuns têm as 5 canônicas; lacunas esperadas abaixo |
| Sufixos legados `.es_es`/`.fr_fr` no site | nenhum |
| `menu.json` | JSON válido; entrada de `marketplace-publishing-guide` nas 5 línguas, índice 28 |

## Anomalias e lacunas reportadas

1. **`--check --source` acusa falso positivo de CRLF.** O comando oficial retorna exit 1 com
   218 `DIVERGE`, todos diferença `\r\n` (working tree da fonte) × `\n` (site) — nenhuma
   divergência real. A cópia foi feita a partir de `git show HEAD:` justamente para preservar
   LF, a mesma convenção do site (`* text=auto eol=lf`). Comparação com normalização CRLF→LF:
   **0 divergências**. O defeito segue presente em `compare_source()`.
2. **Tópicos exclusivos do site** (não tocados):
   - `caveman-commit` — só a variante inglesa; na fonte a referência é a skill, não um doc.
   - `chat-interativo` — deliberadamente fora do menu (a entrada "Interactive Chat (TUI)"
     aponta para `docs/understanding_chat_functionality`).
   - `como_reverter_commit_git_localmente` — fonte moveu para `docs/extra/`.
   - `testar_sem_usar_pypi` — fonte moveu para `docs/extra/`.
3. **Lacunas de idioma vindas da fonte** (não houve invenção de tradução):
   `github-issue-prompt-com-gh` e `marketplace-publishing-guide` são monolíngues na fonte
   (só `.md`).
4. **`SKILL.md` desatualizado, corrigido nesta tarefa.** O documento afirmava que
   `version-markers` era monolíngue e que `review-pr` tinha só `.md` e `.pt_br.md`; ambas as
   informações ficaram obsoletas (hoje os dois têm 5 variantes). A lista de monolíngues foi
   atualizada para `github-issue-prompt-com-gh` e `marketplace-publishing-guide`.
5. Nenhum duplicado obsoleto `.es_es`/`.fr_fr` foi encontrado no site.

## Arquivos alterados

- 37 `.md` modificados em `public/content/docs/`
- 5 `.md` novos em `public/content/docs/` (`marketplace-publishing-guide` ×1,
  `version-markers` ×4)
- `public/content/menu.json` — 5 entradas novas (uma por idioma)
- `.claude/skills/sync-docs/SKILL.md` — 1 linha corrigida (monolíngues na fonte)
