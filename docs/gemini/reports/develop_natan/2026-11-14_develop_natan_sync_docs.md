# Relatório da Tarefa — Sincronizar Documentação Técnica (`/sync-docs`)

- **Data:** 2026-11-14
- **Branch:** `develop_natan`
- **Skill acionada:** `sync-docs`
- **Fonte:** `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr` (`docs/*.md` + `README*.md`)
- **Destino:** `public/content/docs/*.md`

---

## 1. Variantes Adicionadas (4 arquivos)

O tópico `marketplace-publishing-guide` passou a contar com as traduções na fonte, que foram copiadas com normalização de sufixo (`.es_es.md` → `.es.md`, `.fr_fr.md` → `.fr.md`):

- `public/content/docs/marketplace-publishing-guide.pt_br.md`
- `public/content/docs/marketplace-publishing-guide.pt_pt.md`
- `public/content/docs/marketplace-publishing-guide.es.md`
- `public/content/docs/marketplace-publishing-guide.fr.md`

> Nenhum tópico totalmente novo foi encontrado na fonte (`0` adições necessárias em `public/content/menu.json`).

---

## 2. Arquivos Atualizados com Alteração de Conteúdo (22 arquivos em 6 tópicos)

Confirmado via `git diff --stat --ignore-cr-at-eol public/content/docs` (`22 files changed, 247 insertions(+), 35 deletions(-)`):

- `auto-update` (`.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md`)
- `map-reduce-diff` (`.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md`)
- `marketplace-publishing-guide.md`
- `metricas-telemetria` (`.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md`)
- `readme.md` (demais variantes de `readme` também copiadas da fonte, sem diff de conteúdo)
- `scm-multiforge` (`.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md`)

> Nota: A cópia binária direta da fonte (`CRLF`) seguida da reescrita de links fez com que arquivos comuns com `LF` no índice apareçam no `git status` com aviso do `.gitattributes` (`CRLF will be replaced by LF`), sem alteração textual de conteúdo.

---

## 3. Reescrita de Links Cruzados e Verificação

- Execução de `rewrite_doc_links.py`: `167 arquivo(s) reescrito(s), 632 link(s)`.
- Verificação com `rewrite_doc_links.py --check --source C:/Users/nataniel/projetos/pessoal/gitpr_projeto/gitpr`:
  - `arquivos varridos: 273`
  - `arquivos desatualizados: 0` (`nada a reescrever.`)
  - `--source: 261 arquivo(s) comparado(s) (fonte x site, depois da transformacao)` sem divergências (`exit 0`).

---

## 4. Lacunas de Idioma, Monolíngues e Exclusivos do Site

- **Monolíngue na fonte (apenas `.md`):** `github-issue-prompt-com-gh`.
- **Exclusivos do site (órfãos — preservados sem alteração):**
  - `chat-interativo` (5 variantes; fora do `menu.json` por design)
  - `testar_sem_usar_pypi` (5 variantes; movido para `extra/` na fonte)
  - `como_reverter_commit_git_localmente` (`.md`; movido para `extra/` na fonte)
  - `caveman-commit` (`.md`; exclusivo do site)
- **Arquivos legados (`.es_es.md` / `.fr_fr.md`) no site:** nenhum encontrado (`0`).
- **`public/content/menu.json`:** não alterado.
