# Relatório — Sync Docs (2026-09-01)

Sincronização de `public/content/docs/` com a documentação técnica do repositório GitPR CLI (`C:\Users\nataniel\projetos\python\gitpr\docs\`), executada via skill `/sync-docs`. Branch: `develop_natan`.

## 1. Diagnóstico

Comparação tópico a tópico e variante a variante (fonte é autoridade), com mapeamento de sufixos `es_es → es`, `fr_fr → fr`.

**Tópicos só na fonte (faltando no site):** `version-markers` (variante única `.md`).

**Tópicos só no site (não tocados, exclusivos do site):** `caveman-commit`, `chat-interativo` (o `readme` é caso especial — fonte na raiz do repo gitpr).

**Conteúdo divergente (site desatualizado):**
- `ARCHITECTURE.md` — falta seção referenciando `version-markers.md`.
- `linter-regras-customizadas` — todas as 5 variantes; falta seção "External Linter Timeout" (GITPR_LINTER_TIMEOUT, 120s).
- `mcp-integration` — todas as 5 variantes; mudança no modo `--tool` CLI (JSON apenas em stdout, stderr vazio).
- `providers-ia` — todas as 5 variantes; falta timeout de 180s por chamada (GITPR_AI_TIMEOUT).

**README:** 5/5 variantes em sincronia — nenhuma alteração necessária.

## 2. Ações executadas

| Ação | Arquivos |
|---|---|
| Tópico adicionado | `version-markers.md` (cópia integral da fonte, sem alteração de conteúdo) |
| Conteúdo atualizado | `ARCHITECTURE.md`; `linter-regras-customizadas.{md,pt_br,pt_pt,es,fr}`; `mcp-integration.{md,pt_br,pt_pt,es,fr}`; `providers-ia.{md,pt_br,pt_pt,es,fr}` (16 arquivos) |
| `menu.json` | Entrada `docs/version-markers` adicionada nas 5 línguas ("Version Markers" / "Marcadores de Versão" / "Marcadores de Versão" / "Marqueurs de Version" / "Marcadores de Versión"), após `untracked-files` (ordem alfabética aproximada) |

## 3. Verificação

- ✅ `menu.json` é JSON válido.
- ✅ Re-diff variante a variante: **zero divergências** nos tópicos comuns (incluindo README).
- ✅ Nenhum sufixo `.es_es`/`.fr_fr` criado ou modificado na sincronização.

**Cobertura de variantes canônicas (5/5) — tópicos monolíngues por natureza (fonte não tem as variantes):**

| Tópico | Situação |
|---|---|
| `version-markers`, `otimizacao-de-tokens`, `como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh` | Fonte só tem `.md` — lacuna reportada, não inventadas traduções |
| `caveman-commit` | Exclusivo do site, só `.md` |
| `chat-interativo` | Exclusivo do site, sem `.es.md`/`.fr.md` (ver anomalias) |
| `testar_sem_usar_pypi` | Fonte só tem `.md`; o site tem 5/5 (traduções próprias do site além da fonte — mantidas) |

## 4. Anomalias reportadas (não alteradas — decisão do usuário)

> **Atualização:** o usuário confirmou a resolução de todas as anomalias abaixo (mensagem "sim"). Ações executadas na seção 5.

1. **54 duplicatas legadas `.es_es`/`.fr_fr`** com equivalente canônico `.es`/`.fr` em 27 tópicos (auto-update, blame-arqueologo, code-review-ia, commit-message-ia, git-hooks-locais, github-ci-linter, github-pat-integration, gitpr-issue-option, guia-regex-gitpr, hooks-versioning, i18n_explanation, install-wizard, issue-tui-help, linter-regras-customizadas, map-reduce-diff, mcp-annotations, mcp-integration, mcp-prompts, metricas-telemetria, plugins-system, pr-descricao-padrao, providers-ia, pull-request-publication, skill-template, smart-excludes, understanding_chat_functionality, untracked-files). Não são lidos pelo site (fallback inglês). **Sugestão: remover** (requer confirmação explícita). → **RESOLVIDO: removidas.**
2. **`chat-interativo.es_es.md` / `chat-interativo.fr_fr.md` órfãos** — sem `.es.md`/`.fr.md` canônicos; as variantes es/fr deste tópico caem no fallback inglês. → **RESOLVIDO: renomeados para sufixos canônicos** (conteúdo confirmado como espanhol/francês genuíno — H1 "💬 Chat Interactivo (TUI)" / "💬 Chat Interactif (TUI)"). Tópico agora com 5/5 variantes funcionais.
3. **Tópicos sem entrada no `menu.json`** (pré-existentes): `hooks-versioning`, `plugins-system`, `pull-request-publication`, `smart-excludes`. → **RESOLVIDO: entradas adicionadas nas 5 línguas** (títulos baseados nos H1 dos arquivos traduzidos, sem emojis para manter o estilo do menu).
4. **Bug pré-existente no `menu.json` pt_pt:** entrada de `pr-descricao-padrao` usa título em francês — `"▸ PR (Mode par Défaut)"` (deveria ser algo como "PR (Modo Predefinido)"). → **RESOLVIDO: corrigido para "▸ PR (Modo Predefinido)"** (a seção fr mantém o título francês, que é correto lá).

## 5. Ações executadas (follow-up aprovado)

| Ação | Detalhes |
|---|---|
| Remoção de duplicatas legadas | 54 arquivos `.es_es.md`/`.fr_fr.md` (27 tópicos × 2) com equivalente canônico |
| Renomeação de órfãos | `chat-interativo.es_es.md` → `chat-interativo.es.md`; `chat-interativo.fr_fr.md` → `chat-interativo.fr.md` |
| `menu.json` — novas entradas | `hooks-versioning` ("Hook Scripts Versioning" / "Versionamento de Scripts de Hooks" ×2 / "Versionnement des Scripts de Hooks" / "Versionado de Scripts de Hooks"), `plugins-system` ("Plugin System" / "Sistema de Plugins" ×2 / "Système de Plugins" / "Sistema de Plugins"), `pull-request-publication` ("PR Publication" / "Publicação de PR" ×2 / "Publication de PR" / "Publicación de PR"), `smart-excludes` ("Smart Excludes" nas 5 línguas) — posições na ordem alfabética aproximada |
| `menu.json` — correção | pt_pt: "PR (Mode par Défaut)" → "PR (Modo Predefinido)" |

## 6. Verificação final (pós-follow-up)

- ✅ `menu.json` válido; 49 entradas por seção nas 5 línguas.
- ✅ Todos os tópicos de `docs/` no disco têm entrada no menu nas 5 seções.
- ✅ Zero arquivos `.es_es`/`.fr_fr` restantes.
- ✅ `chat-interativo` com 5/5 variantes canônicas.
- ✅ Re-diff variante a variante: zero divergências (conteúdo preservado).

## 7. Arquivos alterados

- `public/content/docs/version-markers.md` (novo)
- `public/content/docs/ARCHITECTURE.md`
- `public/content/docs/linter-regras-customizadas.{md,pt_br,pt_pt,es,fr}`
- `public/content/docs/mcp-integration.{md,pt_br,pt_pt,es,fr}`
- `public/content/docs/providers-ia.{md,pt_br,pt_pt,es,fr}`
- `public/content/docs/chat-interativo.es.md`, `public/content/docs/chat-interativo.fr.md` (renomeados de `.es_es`/`.fr_fr`)
- 54 arquivos `.es_es.md`/`.fr_fr.md` removidos (duplicatas)
- `public/content/menu.json`
