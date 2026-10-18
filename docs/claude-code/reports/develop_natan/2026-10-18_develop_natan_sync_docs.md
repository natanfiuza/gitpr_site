# Sincronização da documentação técnica — 2026-10-18

- **Branch:** `develop_natan`
- **Skill:** `sync-docs`
- **Fonte (autoridade):** `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr` (`docs/*.md` de nível superior + `README*.md` na raiz)
- **Destino:** `public/content/docs/*.md` + `public/content/menu.json`

## Diagnóstico

Comparação fonte × site aplicando o mapeamento de sufixos (`.es_es` → `.es`, `.fr_fr` → `.fr`) e a reescrita de links do `rewrite_doc_links.py --source`.

| Situação | Tópicos |
|---|---|
| Faltando no site (copiados) | `check-ci`, `editor-extension` — 5 variantes cada |
| Conteúdo divergente (atualizados) | `readme` — 5 variantes |
| Só no site (não tocados) | `caveman-commit`, `chat-interativo`, `como_reverter_commit_git_localmente`, `testar_sem_usar_pypi` |
| Variantes de idioma faltando | nenhuma nos tópicos comuns |
| Tópicos sem entrada no menu | nenhum (antes dos 2 novos) |
| Legados `.es_es`/`.fr_fr` no site | nenhum |

Observação: `como_reverter_commit_git_localmente` e `testar_sem_usar_pypi` estão em `docs/extra/` na fonte (subdiretório ignorado pela skill) e `caveman-commit`/`chat-interativo` não existem no repositório GitPR — são conteúdo exclusivo do site, preservados.

## Ações executadas

### 1. Tópicos novos copiados

- `check-ci`: `.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md → .es.md`, `.fr_fr.md → .fr.md`
- `editor-extension`: `.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md → .es.md`, `.fr_fr.md → .fr.md`

Conteúdo markdown preservado byte a byte; nenhum sufixo `.es_es`/`.fr_fr` foi criado no site.

### 2. READMEs atualizados (fonte: raiz do repo GitPR)

`README.md`, `README.pt_br.md`, `README.pt_pt.md`, `README.es_es.md`, `README.fr_fr.md` sobrescreveram `readme.*` no site. Mudanças de conteúdo:

- Novo bullet do comando `gitpr check` (check unificado de CI, artefatos SARIF/JSON/JUnit, exit codes determinísticos).
- Duas novas ferramentas MCP na tabela: `run_check` e `publish_pull_request`.
- Nota sobre a Extensão Oficial para VS Code e Cursor (thin client MCP).
- Novo item na lista "Configuration & Infrastructure": Editor Extension.
- No francês, correção de concordância vinda da fonte (`définies` → `définis`).

### 3. Links cruzados

`python .claude/skills/sync-docs/rewrite_doc_links.py` → **5 arquivos reescritos, 14 links**. Os arquivos novos de `check-ci`/`editor-extension` não tinham links relativos a reescrever.

### 4. Menu

Adicionadas 10 entradas (2 tópicos × 5 idiomas) em `public/content/menu.json`, com títulos derivados do H1 de cada variante:

| Idioma | `editor-extension` (após `demo`) | `check-ci` (após `github-ci-linter`) |
|---|---|---|
| en | ▸ Editor Extension (VS Code & Cursor) | ▸ Unified CI Check (gitpr check) |
| pt_br | ▸ Extensão de Editor (VS Code & Cursor) | ▸ Check Unificado de CI (gitpr check) |
| pt_pt | ▸ Extensão de Editor (VS Code & Cursor) | ▸ Verificação Unificada de CI (gitpr check) |
| fr | ▸ Extension d'Éditeur (VS Code & Cursor) | ▸ Vérification Unifiée pour CI (gitpr check) |
| es | ▸ Extensión de Editor (VS Code & Cursor) | ▸ Verificación Unificada de CI (gitpr check) |

Cada idioma ficou com 53 entradas `docs/*` no menu.

## Verificação

| Checagem | Resultado |
|---|---|
| `rewrite_doc_links.py --check` | exit 0 — nenhum link pendente |
| `rewrite_doc_links.py --check --source <gitpr>` | exit 0 — 234 arquivos comparados, sem divergência |
| Variantes dos tópicos comuns | 5/5 para `check-ci` e `editor-extension` |
| `menu.json` | JSON válido, 5 idiomas, novas entradas presentes |
| Legados `.es_es`/`.fr_fr` no site | nenhum |

Total atual: **53 tópicos, 246 arquivos** em `public/content/docs/`.

## Lacunas e anomalias reportadas (sem ação)

- `review-pr`: fonte só tem `.md` e `.pt_br.md` — site espelha a limitação; faltam `pt_pt`, `es`, `fr`.
- `github-issue-prompt-com-gh` e `version-markers`: monolíngues na fonte (só `.md`); site idem.
- `caveman-commit` e `como_reverter_commit_git_localmente`: só existem em inglês no site (conteúdo exclusivo do site).
- `chat-interativo` e `testar_sem_usar_pypi`: exclusivos do site, mantidos como estão.
- Nenhum duplicado obsoleto `.es_es`/`.fr_fr` a remover.

## Arquivos alterados

Novos (10):
`public/content/docs/check-ci.{md,pt_br.md,pt_pt.md,es.md,fr.md}`
`public/content/docs/editor-extension.{md,pt_br.md,pt_pt.md,es.md,fr.md}`

Modificados (6):
`public/content/docs/readme.{md,pt_br.md,pt_pt.md,es.md,fr.md}`
`public/content/menu.json`
