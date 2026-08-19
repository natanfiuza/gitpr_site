# Relatório: Sync da Documentação Técnica (2026-08-18)

**Tarefa:** Sincronizar `public/content/docs/` com a documentação técnica do GitPR CLI (`C:\Users\nataniel\projetos\python\gitpr`).

## Diagnóstico

- **Tópicos na fonte (docs/*.md):** 33 + `readme` (README da raiz do repo gitpr)
- **Tópicos no site:** todos os tópicos da fonte presentes; nenhum tópico novo a copiar
- **Divergências de conteúdo encontradas:** 6 tópicos (21 arquivos no total)

## Tópicos atualizados (sobrescritos com o conteúdo da fonte)

| Tópico | Variantes atualizadas | Natureza da divergência |
| --- | --- | --- |
| `ARCHITECTURE` | `.md` | Fonte mais atual: MCP server, TUI de PR/Issues/chat, plugins, métricas, Ollama, hooks com auto-sync |
| `commit-message-ia` | 5 (`.md`, `.pt_br`, `.pt_pt`, `.es`, `.fr`) | Fonte adicionou seção 7 sobre a assinatura `Co-Authored-By: Gitpr-cli` |
| `linter-regras-customizadas` | 5 | Fonte adicionou seções 5 (bridge Checkstyle com linters externos) e 6 (relatórios markdown) |
| `mcp-integration` | 5 | Fonte adicionou seção sobre o alias oculto `gitpr --mcp` |
| `pull-request-publication` | 5 | Fonte atualizou: nota sobre co-author signature por fluxo e renomeou `FileStageScreen` → `StageFilesScreen` |
| `readme` | 4 (`.pt_br`, `.pt_pt`, `.es`, `.fr`) | Fonte reescreveu textos do wizard de linters externos ("bridge via Checkstyle"); variante `.md` (en) já estava idêntica |

Sufixos renomeados conforme o mapeamento canônico: fonte `.es_es` → site `.es`, fonte `.fr_fr` → site `.fr`. Nenhum sufixo legado novo foi criado.

## Verificação pós-sincronização

- `cmp` variante por variante (com mapeamento de sufixos): **0 divergências** nos tópicos comuns
- **0 variantes faltando** nos tópicos comuns
- Nenhum arquivo `.es_es`/`.fr_fr` novo no site

## Anomalias reportadas (sem ação — decisão do usuário)

### 1. Arquivos legados `.es_es`/`.fr_fr` no site (56 arquivos, 28 tópicos)

Duplicados obsoletos que o site não lê (fallback para inglês). Sugestão: remover.

Tópicos: `auto-update`, `blame-arqueologo`, `chat-interativo`*, `code-review-ia`, `commit-message-ia`, `git-hooks-locais`, `github-ci-linter`, `github-pat-integration`, `gitpr-issue-option`, `guia-regex-gitpr`, `hooks-versioning`, `i18n_explanation`, `install-wizard`, `issue-tui-help`, `linter-regras-customizadas`, `map-reduce-diff`, `mcp-annotations`, `mcp-integration`, `mcp-prompts`, `metricas-telemetria`, `plugins-system`, `pr-descricao-padrao`, `providers-ia`, `pull-request-publication`, `skill-template`, `smart-excludes`, `understanding_chat_functionality`, `untracked-files`

\* **`chat-interativo` (exclusivo do site):** só possui as variantes legadas `.es_es`/`.fr_fr` — não tem as canônicas `.es`/`.fr`. O conteúdo espanhol/francês existe nos arquivos legados; se forem removidos, as variantes canônicas devem ser criadas a partir deles (decisão do usuário).

### 2. Lacunas de idioma (tópicos monolíngues na fonte)

A fonte só possui variante inglesa; o site não pode inventar traduções:

- `ARCHITECTURE` — site só tem `.md` (consistente com a fonte)
- `como_reverter_commit_git_localmente` — site só tem `.md` (consistente)
- `github-issue-prompt-com-gh` — site só tem `.md` (consistente)
- `otimizacao-de-tokens` — site só tem `.md` (consistente)
- `testar_sem_usar_pypi` — site tem 5 variantes (a mais que a fonte). Preservadas conforme regra de não remover; considerar remoção das traduções extras se o conteúdo estiver desatualizado.

### 3. Tópicos exclusivos do site (não existem na fonte)

- `caveman-commit` (só `.md`)
- `chat-interativo` (`.md`, `.pt_br`, `.pt_pt`, `.es_es`, `.fr_fr`)

Não foram tocados.

### 4. Tópicos sem entrada em `public/content/menu.json`

- `hooks-versioning`
- `plugins-system`
- `smart-excludes`

Tópicos pré-existentes (presentes na fonte e no site), apenas sem link no menu de navegação em nenhum dos 5 idiomas.

## Nada a fazer

- Nenhum tópico novo copiado da fonte → `menu.json` sem alterações.
- Nenhum arquivo removido.
