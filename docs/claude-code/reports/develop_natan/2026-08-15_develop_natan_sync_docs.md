# Relatório — Sincronização da Documentação Técnica (`sync-docs`)

**Data:** 2026-08-15
**Branch:** `develop_natan`
**Fonte:** `C:\Users\nataniel\projetos\python\gitpr\docs\*.md` (+ `README.*` na raiz do repo gitpr)
**Destino:** `public/content/docs/`

## Resumo

Sincronização completa executada via skill `/sync-docs`. Todos os tópicos comuns à fonte e ao site estão agora idênticos variante por variante (verificado com `diff` aplicando o mapeamento de sufixos). Nenhum arquivo do site foi removido.

| Métrica | Valor |
|---|---|
| Arquivos modificados (sobrescritos pela fonte) | 54 |
| Arquivos novos (copiados da fonte) | 53 |
| Sufixos legados `.es_es`/`.fr_fr` criados | 0 |

## Tópicos adicionados

- **`git-status`** (novo no site): 5 variantes copiadas da fonte com renomeação canônica — `git-status.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md → .es.md`, `.fr_fr.md → .fr.md`.
- Entrada adicionada ao `menu.json` nas 5 línguas, sob a seção "Technical Documentation", posicionada após "Git Hooks" (ordem alfabética aproximada): `{"title": "▸ Git Status", "path": "docs/git-status"}` em `en`, `pt_br`, `pt_pt`, `fr`, `es`.

## Tópicos atualizados (conteúdo divergente sobrescrito)

Variantes divergentes corrigidas tópico por tópico (site ← fonte):

| Tópico | Variantes atualizadas |
|---|---|
| `auto-update` | .md, .pt_br, .pt_pt, .es, .fr |
| `code-review-ia` | .md, .pt_br, .pt_pt, .es, .fr |
| `commit-message-ia` | .md, .pt_br, .pt_pt, .es, .fr |
| `github-pat-integration` | .md, .pt_br, .pt_pt, .es, .fr |
| `install-wizard` | .md, .pt_br, .pt_pt, .es, .fr |
| `issue-tui-help` | .md, .pt_br, .pt_pt, .es, .fr |
| `mcp-annotations` | .md, .pt_br, .pt_pt, .es, .fr |
| `mcp-integration` | .md, .pt_br, .pt_pt, .es, .fr |
| `mcp-prompts` | .md, .pt_br, .pt_pt, .es, .fr |
| `metricas-telemetria` | .md, .pt_br, .pt_pt, .es, .fr |
| `plugins-system` | .md, .pt_br, .pt_pt, .es, .fr |
| `pr-descricao-padrao` | .md, .pt_br, .pt_pt, .es, .fr |
| `providers-ia` | .md, .pt_br, .pt_pt, .es, .fr |
| `pull-request-publication` | .md, .pt_br, .pt_pt, .es, .fr |
| `smart-excludes` | .pt_br, .pt_pt, .es, .fr |
| `testar_sem_usar_pypi` | .md |
| `untracked-files` | .pt_br, .pt_pt, .es, .fr |
| `guia-regex-gitpr` | .es |
| `readme` (fonte: raiz do repo gitpr) | .pt_br, .pt_pt, .es, .fr |

Variantes `.es`/`.fr` canônicas copiadas com renomeação (`es_es→es`, `fr_fr→fr`) para tópicos cuja base já estava em sincronia: `blame-arqueologo`, `git-hooks-locais`, `gitpr-issue-option`, `hooks-versioning`, `i18n_explanation`, `linter-regras-customizadas`, `map-reduce-diff`, `skill-template`, `understanding_chat_functionality`.

## Lacunas de idioma reportadas

- **Tópicos monolíngues na fonte** (o site não tem as 4 variantes traduzidas; nenhuma tradução inventada):
  - `ARCHITECTURE`, `caveman-commit`, `como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens` — a fonte só possui `.md`.
- **`testar_sem_usar_pypi`** — o site possui 5 variantes além da fonte (que só tem `.md`). Variantes extras do site **preservadas** conforme a regra (não remover além da fonte). A variante `.md` foi sincronizada.
- **`chat-interativo`** (exclusivo do site, não tocado): as variantes es/fr existem apenas como legados `.es_es`/`.fr_fr`, que o site **não lê** — usuários de es/fr caem no fallback inglês.

## Anomalias reportadas

- **Duplicados obsoletos `.es_es.md`/`.fr_fr.md` no site:** 28 arquivos de cada sufixo (56 no total). O site só resolve `.es.md`/`.fr.md`, então todos os 56 são ignorados e redundantes com os canônicos agora sincronizados. **Sugestão: remover** — não removidos por requererem confirmação do usuário.
- **Tópicos do site sem entrada no `menu.json`** (pré-existentes; decisão do usuário): `hooks-versioning`, `plugins-system`, `pull-request-publication`, `smart-excludes`.

## Verificação

- ✅ `diff -rq` fonte vs site (com mapeamento de sufixos) — **todos os tópicos comuns idênticos**, incluindo o caso especial `readme`.
- ✅ `git-status` presente no site com as 5 variantes canônicas.
- ✅ Nenhum sufixo `.es_es`/`.fr_fr` criado nesta sincronização (53 arquivos novos, todos com sufixos canônicos).
- ✅ `menu.json` permanece JSON válido (validado com `json.load`).
