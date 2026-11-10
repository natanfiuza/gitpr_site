# Sync Docs — 2026-11-10 (branch `develop_natan`)

Sincronização de `public/content/docs/` com a documentação técnica do repositório
GitPR CLI (`gitpr/docs/*.md` + `README*.md` da raiz), conforme skill `sync-docs`.

## Resumo

| Item | Resultado |
| --- | --- |
| Tópicos faltando no site | 0 |
| Tópicos novos adicionados | 0 |
| Tópicos atualizados | 7 (19 arquivos) |
| Entradas novas no `menu.json` | 0 |
| Lacunas de idioma (fonte ausente) | 2 tópicos monolíngues (esperado) |
| Duplicados legados `.es_es`/`.fr_fr` no site | 0 |
| Links cruzados reescritos | 18 arquivos, 31 links |
| Paridade fonte × site (normalizada) | 257/257 arquivos, 0 divergências |

## Tópicos atualizados

Todos copiados a partir do blob commitado da fonte (`git show HEAD:<path>`, LF),
preservando o conteúdo markdown e aplicando o mapeamento de sufixos
`.es_es → .es`, `.fr_fr → .fr`.

### `auto-update` — 5 variantes
- Nova seção **“Escape Switch for Local Development (`GITPR_SKIP_UPDATE_CHECK`)”**.
- Aviso `[!WARNING]` de migração: desde a v1.4.3 a variável é avaliada por parser
  booleano estrito; valores legados como `false`/`0` deixam de silenciar a checagem.

### `commit-message-ia` — 5 variantes
- Aviso `[!WARNING]` de migração: até a v1.4.4 `gitpr -c` era silencioso/não
  interativo; a partir da v1.4.4 abre o TUI de 4 passos e executa o commit.
- Dica `[!TIP]` de boas práticas para CI/headless (`gitpr --commit --hook <file>`).

### `editor-extension` — 5 variantes
- Instalação via **GitHub Releases** (download do `.vsix`) passa a ser a opção
  primária; VS Code Marketplace/Cursor aparecem como alternativas.

### `config-tui` — 1 variante (inglês)
- Documenta a variável `GITPR_SKIP_PROJECT_CONFIG_SEED=true` para desligar a
  criação automática de `.gitpr/conf/gitpr.config.yml`, com link para
  `/docs/pull-request-publication#94-migration-workflow-note-cicd-monorepos-and-clean-trees`.

### `pull-request-publication` — 1 variante (inglês)
- Nota `[!IMPORTANT]` de mudança de fluxo (seed em runtime) e nova seção
  **§9.4 Migration & Workflow Note: CI/CD, Monorepos and Clean Trees**
  (exemplos de `git status --porcelain` e GitHub Actions).

### `marketplace-publishing-guide` — 1 variante (inglês, monolíngue)
- Passo 4 do checklist agora descreve a criação de **GitHub Release oficial**
  com ícone e licença.

### `readme` — 1 variante (inglês)
- Bloco da extensão VS Code/Cursor: passa a apontar para download do `.vsix`
  em GitHub Releases e para o Guia da Extensão.

## Lacunas de idioma

Nenhuma lacuna inesperada. Os únicos tópicos monolíngues na fonte continuam sendo
os esperados pela skill:

- `github-issue-prompt-com-gh` — só `.md`.
- `marketplace-publishing-guide` — só `.md`.

`version-markers` e `review-pr` têm as 5 variantes na fonte e já estavam completos
no site.

## Exclusivos do site (não tocados)

- `chat-interativo` (5 variantes) — deliberadamente fora do `menu.json`
  (a entrada “Interactive Chat (TUI)” aponta para `docs/understanding_chat_functionality`).
- `caveman-commit` (só `.md`) — sem correspondente no nível superior da fonte.
- `como_reverter_commit_git_localmente` (só `.md`) — a fonte moveu o arquivo para
  `extra/`, diretório ignorado pela sincronização.
- `testar_sem_usar_pypi` (5 variantes) — também em `extra/` na fonte.

## Anomalias

- **CRLF falso positivo:** `rewrite_doc_links.py --check --source` acusa divergência
  em todos os arquivos porque o working tree de `gitpr` está materializado com CRLF
  (`core.autocrlf=true`) enquanto o site usa LF. A comparação foi feita com
  normalização de fins de linha sobre os blobs `HEAD` (LF), como registrado na
  memória do projeto; resultado: **0 divergências reais** após a sincronização.
- Nenhum arquivo foi apagado. Nenhum sufixo `.es_es`/`.fr_fr` foi criado no site.
- Nenhum tópico pré-existente ficou sem entrada no `menu.json` (exceto o caso
  deliberado de `chat-interativo`).

## Verificação

| Checagem | Resultado |
| --- | --- |
| 5 variantes canônicas por tópico | OK (exceto os monolíngues esperados/site-exclusivos) |
| `rewrite_doc_links.py --check` | exit 0 — “nada a reescrever” (269 arquivos varridos) |
| Paridade fonte × site pós-reescrita, CRLF normalizado | 257 arquivos, 0 divergências |
| Sufixos `.es_es`/`.fr_fr` no site | nenhum |

## Arquivos alterados

```
public/content/docs/auto-update.{md,pt_br,pt_pt,es,fr}.md
public/content/docs/commit-message-ia.{md,pt_br,pt_pt,es,fr}.md
public/content/docs/config-tui.md
public/content/docs/editor-extension.{md,pt_br,pt_pt,es,fr}.md
public/content/docs/marketplace-publishing-guide.md
public/content/docs/pull-request-publication.md
public/content/docs/readme.md
```

Total: 19 arquivos, +254/−21 linhas.
