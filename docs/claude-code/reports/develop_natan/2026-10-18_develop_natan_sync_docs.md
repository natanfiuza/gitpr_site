# Sincronização da documentação técnica — 2026-10-18

- **Branch:** `develop_natan`
- **Skill:** `sync-docs`
- **Fonte (autoridade):** `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr` (`docs/*.md` de nível superior + `README*.md` na raiz)
- **Destino:** `public/content/docs/*.md` + `public/content/menu.json`
- **Execuções:** 2 no mesmo dia — a segunda partiu do estado deixado pela primeira; este relatório consolida as duas.

## Execução 1 — `check-ci`, `editor-extension` e READMEs

### Diagnóstico

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

### Ações executadas

1. **Tópicos novos copiados** — `check-ci` e `editor-extension`, cada um com `.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md → .es.md`, `.fr_fr.md → .fr.md`. Conteúdo markdown preservado byte a byte; nenhum sufixo `.es_es`/`.fr_fr` foi criado no site.
2. **READMEs atualizados** — `README.md`, `README.pt_br.md`, `README.pt_pt.md`, `README.es_es.md`, `README.fr_fr.md` sobrescreveram `readme.*`. Mudanças: bullet do `gitpr check`, ferramentas MCP `run_check`/`publish_pull_request`, nota da Extensão Oficial VS Code/Cursor e correção de concordância no francês da fonte (`définies` → `définis`).
3. **Links cruzados** — `python .claude/skills/sync-docs/rewrite_doc_links.py` → **5 arquivos reescritos, 14 links**. Os arquivos novos de `check-ci`/`editor-extension` não tinham links relativos a reescrever.
4. **Menu** — 10 entradas (2 tópicos × 5 idiomas), com títulos derivados do H1 de cada variante:

| Idioma | `editor-extension` (após `demo`) | `check-ci` (após `github-ci-linter`) |
|---|---|---|
| en | ▸ Editor Extension (VS Code & Cursor) | ▸ Unified CI Check (gitpr check) |
| pt_br | ▸ Extensão de Editor (VS Code & Cursor) | ▸ Check Unificado de CI (gitpr check) |
| pt_pt | ▸ Extensão de Editor (VS Code & Cursor) | ▸ Verificação Unificada de CI (gitpr check) |
| fr | ▸ Extension d'Éditeur (VS Code & Cursor) | ▸ Vérification Unifiée pour CI (gitpr check) |
| es | ▸ Extensión de Editor (VS Code & Cursor) | ▸ Verificación Unificada de CI (gitpr check) |

Cada idioma ficou com **53 entradas** `docs/*` no menu.

### Verificação

| Checagem | Resultado |
|---|---|
| `rewrite_doc_links.py --check` | exit 0 — nenhum link pendente |
| `rewrite_doc_links.py --check --source <gitpr>` | exit 0 — 234 arquivos comparados, sem divergência |
| Variantes dos tópicos comuns | 5/5 para `check-ci` e `editor-extension` |
| `menu.json` | JSON válido, 5 idiomas, novas entradas presentes |
| Legados `.es_es`/`.fr_fr` no site | nenhum |

Total ao fim da execução: **53 tópicos, 246 arquivos** em `public/content/docs/`.

## Execução 2 — `explain-pr`, `tests-generate` e READMEs

### Diagnóstico

Nova rodada sobre a fonte já atualizada. O `--check --source` apontou:

| Situação | Tópicos |
|---|---|
| Faltando no site (copiados) | `explain-pr`, `tests-generate` — 5 variantes cada |
| Conteúdo divergente (atualizados) | `readme` — 5 variantes |
| Só no site (não tocados) | `caveman-commit`, `chat-interativo`, `como_reverter_commit_git_localmente`, `testar_sem_usar_pypi` |
| Tópicos sem entrada no menu | nenhum (antes dos 2 novos) |
| Legados `.es_es`/`.fr_fr` no site | nenhum |

Os `readme.*` divergiram porque a fonte passou a trazer, em cada bullet de comando, um link externo **"📖 Full docs"** (por idioma) para a documentação no GitHub — inclusive os dois tópicos novos (`explain-pr`, `tests-generate`) e `check-ci`, `editor-extension`.

### Ações executadas

1. **Tópicos novos copiados** — `explain-pr` e `tests-generate`, cada um com `.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md → .es.md`, `.fr_fr.md → .fr.md` (10 arquivos). Markdown preservado exatamente como na fonte.
2. **READMEs resincronizados** — 5 variantes sobrescritas com a versão atual da raiz do GitPR, com os links "📖 Full docs".
3. **Links cruzados** — `rewrite_doc_links.py` → **15 arquivos reescritos, 74 links** (os 10 novos + os 5 readmes).
4. **Menu** — 10 entradas novas (2 tópicos × 5 idiomas), posicionadas conforme a ordem temática:
   - `tests-generate` imediatamente após **Split Command**;
   - `explain-pr` imediatamente após **Junior Mentor Mode**.

| Idioma | `tests-generate` | `explain-pr` |
|---|---|---|
| en | ▸ AI Test Generation (gitpr tests generate) | ▸ Reviewer Guide (gitpr explain) |
| pt_br | ▸ Geração de Testes com IA (gitpr tests generate) | ▸ Guia do Revisor (gitpr explain) |
| pt_pt | ▸ Geração de Testes com IA (gitpr tests generate) | ▸ Guia do Revisor (gitpr explain) |
| es | ▸ Generación de Pruebas con IA (gitpr tests generate) | ▸ Guía del Revisor (gitpr explain) |
| fr | ▸ Génération de Tests par IA (gitpr tests generate) | ▸ Guide du Relecteur (gitpr explain) |

Cada idioma ficou com **55 entradas** `docs/*` no menu (53 + 2).

### Verificação

| Checagem | Resultado |
|---|---|
| `rewrite_doc_links.py --check` | exit 0 — 0 arquivos desatualizados, nada a reescrever |
| `rewrite_doc_links.py --check --source <gitpr>` | exit 0 — **244 arquivos comparados**, sem divergência |
| Cobertura de menu | 55 entradas por idioma; 0 tópicos sem menu, 0 entradas sem tópico |
| Variantes dos tópicos novos | 5/5 para `explain-pr` e `tests-generate` |
| Legados `.es_es`/`.fr_fr` no site | nenhum |

Total ao fim da execução: **55 tópicos, 256 arquivos** em `public/content/docs/`.

## Estado final do dia

- **55 tópicos, 256 arquivos** no site; **244 arquivos** da fonte (239 `docs/*.md` + 5 `README*`) comparados sem divergência após o mapeamento de sufixos e a reescrita de links.
- **55 entradas `docs/*` por idioma** no `menu.json`, todas com título traduzido.
- Nenhum link cruzado pendente e nenhum sufixo legado criado.
- Nenhum tópico exclusivo do site foi tocado.

### Testes

```
Tests: 8 failed, 50 passed (58) — Assertions: 222 — Duration: ~6.9s
```

As 8 falhas são as pré-existentes de `Auth/*` e `ProfileTest` (`Expected 200 but received 404`), causadas pela rota catch-all [routes/web.php:27](routes/web.php#L27). **Nenhuma falha nova.**

## Lacunas e anomalias reportadas (sem ação)

- `review-pr`: a fonte só tem `.md` e `.pt_br.md` — o site espelha a limitação; faltam `pt_pt`, `es`, `fr`. Não foram inventadas traduções.
- `github-issue-prompt-com-gh` e `version-markers`: monolíngues na fonte (só `.md`); site idem.
- `caveman-commit`, `como_reverter_commit_git_localmente`: só existem em inglês no site.
- `chat-interativo` e `testar_sem_usar_pypi`: exclusivos do site, mantidos como estão.
- Nenhum duplicado obsoleto `.es_es`/`.fr_fr` a remover.

## Arquivos alterados

Novos (20):

```
public/content/docs/check-ci.{md,pt_br.md,pt_pt.md,es.md,fr.md}
public/content/docs/editor-extension.{md,pt_br.md,pt_pt.md,es.md,fr.md}
public/content/docs/explain-pr.{md,pt_br.md,pt_pt.md,es.md,fr.md}
public/content/docs/tests-generate.{md,pt_br.md,pt_pt.md,es.md,fr.md}
```

Modificados (6):

```
public/content/docs/readme.{md,pt_br.md,pt_pt.md,es.md,fr.md}
public/content/menu.json
```
