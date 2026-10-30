# Sincronização da documentação técnica — 2026-10-29

- **Branch:** `develop_natan`
- **Skill:** `sync-docs`
- **Fonte (autoridade):** `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr` (`docs/*.md` de nível superior + `README*.md` na raiz)
- **Destino:** `public/content/docs/*.md` + `public/content/menu.json`

## Diagnóstico

Comparação fonte × site com `rewrite_doc_links.py --check --source <gitpr>`, que aplica o mapeamento de sufixos (`.es_es` → `.es`, `.fr_fr` → `.fr`) e a reescrita de links aos dois lados antes de comparar.

| Situação | Tópicos |
|---|---|
| Faltando no site | nenhum |
| Conteúdo divergente (atualizados) | `auto-update` — 5 variantes; `commit-message-ia` — 5 variantes |
| Variantes de idioma faltando | nenhuma nos tópicos comuns |
| Tópicos sem entrada no menu | só `chat-interativo` (exclusão deliberada — a linha "Interactive Chat (TUI)" aponta para `docs/understanding_chat_functionality`) |
| Menus sem tópico | nenhum |
| Legados `.es_es`/`.fr_fr` no site | nenhum |

Resultado do diagnóstico: 244 arquivos comparados, **10 divergentes**, todos nos dois tópicos acima. Os outros 234 estavam em sincronia. Sem tópicos novos — o menu ficou intocado.

### Natureza da divergência

A fonte avançou depois do último sync do site (2026-09-25, versão 1.3.0). Commits que tocaram os arquivos:

| Repo | Commit | Data | Assunto |
|---|---|---|---|
| gitpr | `9bd2038` | 2026-10-29 | feat: add interactive commit TUI for gitpr commit |
| site | `ade4291` | 2026-09-25 | Nova atualização da versão 1.3.0 |

**`auto-update.*`** (+4 linhas por variante) — o texto do auto-updater ficou factualmente incorreto no site:

- cache passou de **24 horas / "verifica diariamente"** para **1 hora em janela móvel**, verificado a cada execução e antes dos subcomandos interativos;
- `gitpr -u`/`--update` sempre ignora o cache;
- comparação de versões segundo **PEP 440** (pré-release remoto ignorado em instalação estável; versão ilegível conta como desconhecida);
- tabela de exceções ganhou 2 linhas: subcomando **sem terminal** (CI/pipes/cron) e `gitpr <subcomando> -h`, além da exceção de ajuda via linha de comando bruta.

**`commit-message-ia.*`** (+18 linhas por variante) — nova seção 1 descrevendo o fluxo TUI interativo de 4 etapas (seletor de stage `StageFilesScreen` → geração em background `CommitProgressScreen` → revisão/edição `CommitMessageScreen` → execução animada), nova seção 4 de configuração de staging (`GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `--no-unstaged-check`), `ollama` adicionado aos provedores forçáveis e seções renumeradas de 4–7 para 5–8. O diff de `git diff HEAD`/console passou a `git diff --cached` — coerente com o staging seletivo.

## Ações executadas

1. **10 arquivos sobrescritos** byte a byte a partir da fonte, com renomeação de sufixo:

   ```
   auto-update.md          auto-update.pt_br.md   auto-update.pt_pt.md
   auto-update.es_es.md -> auto-update.es.md     auto-update.fr_fr.md -> auto-update.fr.md
   commit-message-ia.md    commit-message-ia.pt_br.md   commit-message-ia.pt_pt.md
   commit-message-ia.es_es.md -> .es.md          commit-message-ia.fr_fr.md -> .fr.md
   ```

   Nenhum sufixo `.es_es`/`.fr_fr` foi criado no site.
2. **Links cruzados** — `python .claude/skills/sync-docs/rewrite_doc_links.py` → **10 arquivos reescritos, 15 links**. Só dois tipos:
   - `../README.md` → `/docs/readme[?lang=…]` (10 ocorrências, uma por arquivo);
   - `tutorial/install-from-source.*.md` → URL do GitHub com sufixo de origem preservado (5 ocorrências, só em `auto-update`).
3. **Menu** — nenhuma alteração: nenhum tópico novo.

## Verificação

| Checagem | Resultado |
|---|---|
| `rewrite_doc_links.py --check` | exit 0 — 0 arquivos desatualizados |
| `rewrite_doc_links.py --check --source <gitpr>` | exit 0 — **244 arquivos comparados, sem divergência** |
| Cobertura de menu | 55 tópicos, 54 entradas; a única sem menu é `chat-interativo` (deliberado) |
| Legados `.es_es`/`.fr_fr` no site | nenhum |
| Fins de linha | 256 arquivos, 31 com CRLF — mesmo número de antes; nenhum dos 10 sincronizados tem CRLF |

Total: **55 tópicos, 256 arquivos** em `public/content/docs/`. Diff: 10 arquivos, 239 inserções, 129 remoções.

### Testes

```
Tests: 8 failed, 50 passed (58) — Assertions: 222 — Duration: ~5.3s
```

As 8 falhas são as pré-existentes de `Auth/*` e `ProfileTest` (`Expected 200 but received 404`), causadas pela rota catch-all [routes/web.php:27](routes/web.php#L27). **Nenhuma falha nova** — a mudança é só de conteúdo Markdown.

## Lacunas e anomalias reportadas (sem ação)

- `review-pr`: a fonte só tem `.md` e `.pt_br.md` — faltam `pt_pt`, `es`, `fr`. Traduções não foram inventadas.
- `github-issue-prompt-com-gh` e `version-markers`: monolíngues na fonte (só `.md`); site idem.
- `caveman-commit`, `como_reverter_commit_git_localmente`: só existem em inglês no site. Os dois estão em `docs/extra/` na fonte (subdiretório ignorado pela skill).
- `chat-interativo` e `testar_sem_usar_pypi`: exclusivos do site, mantidos intactos.

## Arquivos alterados

Site (10):

```
public/content/docs/auto-update.{md,pt_br.md,pt_pt.md,es.md,fr.md}
public/content/docs/commit-message-ia.{md,pt_br.md,pt_pt.md,es.md,fr.md}
```

Relatório (1): `docs/claude-code/reports/develop_natan/2026-10-29_develop_natan_sync_docs.md`
