# Relatório — Sincronização da documentação técnica (sync-docs)

- **Data**: 2026-09-13
- **Branch**: `develop_natan`
- **Task**: `sync_docs`
- **Skill**: `/sync-docs`

## Contexto

Run do `/sync-docs`: sincronizar `public/content/docs/` com a documentação técnica do repo GitPR CLI.

- **Fonte**: `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs` (nível superior, `*.md`); `readme` vem da raiz do repo gitpr (`README*.md`).
- **Destino**: `public/content/docs/*.md`.
- **Mapeamento de locale aplicado na cópia**: `.es_es → .es`, `.fr_fr → .fr` (o site resolve `{page}.{lang}.md` com `lang ∈ {en, pt_br, pt_pt, fr, es}`).

## O que foi feito

### 1. Tópicos novos copiados (5 variantes cada, 15 arquivos)

| Tópico | Variantes | Título no menu (curado, sem prefixo "Technical Documentation:") |
| --- | --- | --- |
| `config-tui` | en, pt_br, pt_pt, es, fr | Interactive Config (TUI) |
| `release-notes` | en, pt_br, pt_pt, es, fr | Release Notes & Changelog |
| `usage-log` | en, pt_br, pt_pt, es, fr | Usage Log |

Nenhum destes tópicos tinha entrada no `menu.json`; todos são novos no site.

### 2. Conteúdo divergente sobrescrito (32 arquivos)

Sobrescritos com o conteúdo da fonte, **todas** as variantes (não só a inglesa):

| Tópico | Variantes atualizadas |
| --- | --- |
| `ARCHITECTURE` | 5 (en, pt_br, pt_pt, es, fr) |
| `auto-update` | 5 |
| `hooks-versioning` | 5 |
| `mcp-integration` | 5 |
| `readme` | 5 (fonte: raiz do repo gitpr) |
| `skill-template` | 5 |
| `testar_sem_usar_pypi` | 1 (só `en` — tópico monolíngue na fonte) |
| `version-markers` | 1 (só `en` — tópico monolíngue na fonte) |

### 3. `menu.json` — 15 entradas novas

3 tópicos × 5 idiomas, adicionadas ao fim da seção "Technical Documentation" (mesma convenção das últimas adições do menu), preservando a formatação compacta existente (`{ "title": …, "path": … }` em uma linha, indent 4, LF).

Títulos traduzidos usados:

| Tópico | en | pt_br | pt_pt | fr | es |
| --- | --- | --- | --- | --- | --- |
| `config-tui` | Interactive Config (TUI) | Configuração Interativa (TUI) | Configuração Interativa (TUI) | Configuration Interactive (TUI) | Configuración Interactiva (TUI) |
| `release-notes` | Release Notes & Changelog | Notas de Versão e Changelog | Notas de Versão e Changelog | Notes de Version et Changelog | Notas de Versión y Changelog |
| `usage-log` | Usage Log | Log de Uso | Registo de Utilização | Journal d'Utilisation | Registro de Uso |

> O `menu.json` é também o índice da busca: `DocsController::search_content()` itera sobre o menu, portanto os 3 tópicos novos passam a ser pesquisáveis automaticamente.

## Verificação (critérios da skill — todos ATENDIDOS)

| Critério | Resultado |
| --- | --- |
| Nenhum tópico só-na-fonte faltando no site | ✔ vazio |
| `diff` fonte↔site (mapeando sufixos, EOL-normalizado) sem diferenças nos tópicos comuns | ✔ 0 divergentes |
| Tópicos novos com as 5 variantes canônicas | ✔ `config-tui`, `release-notes`, `usage-log` = 5/5 |
| Nenhum sufixo `.es_es`/`.fr_fr` criado no site | ✔ 0 legados em todo `public/content` |
| `menu.json` válido e com os 3 tópicos nos 5 idiomas | ✔ parse OK, 42 entradas `docs/*` por idioma |
| Arquivos novos gravados sem CRLF | ✔ 15/15 LF |

Só-no-site intactos (não tocados, por design): `caveman-commit` (en), `chat-interativo` (5 variantes).

Contagem final: `public/content/docs/` = **190 arquivos `.md`** (175 → 190).

## Anomalias e observações

1. **EOL no worktree da fonte (armadilha reincidente — correção aplicada na sessão).** O worktree do repo gitpr é CRLF (`core.autocrlf=true`), mas o conteúdo canônico no git é LF e o site é LF puro. A primeira passada de cópia importou CRLF em 26 arquivos do site. Corrigido normalizando para LF todos os arquivos escritos — **nenhum CRLF foi introduzido no site**.

   Consequência: **15 dos 47 arquivos inicialmente "divergentes" eram EOL-only** (`github-ci-linter` ×5, `map-reduce-diff` ×5, `smart-excludes` ×5). Após normalização voltaram a coincidir com o site e **não aparecem no diff** — mesmo critério do run de 2026-09-08. O diff real ficou em **32 arquivos**.

2. **CRLF pré-existente no site (fora do escopo, não tocado).** 5 arquivos do site já eram CRLF antes deste run e não foram alterados: `caveman-commit.md`, `github-pat-integration.pt_br.md`, `linter-regras-customizadas.pt_br.md`, `testar_sem_usar_pypi.pt_br.md`, `untracked-files.pt_br.md`. Vale um cleanup futuro (normalização + `.gitattributes` com `* text=auto eol=lf`) para deixar o site 100% LF.

3. **Lacuna de idioma — site além da fonte.** `testar_sem_usar_pypi` é monolíngue na fonte (só `en`), mas o site tem as 5 variantes. Conforme a skill, **não foram removidas** nem houve invenção de traduções. As variantes `es`, `fr`, `pt_br`, `pt_pt` do site estão agora desatualizadas em relação à fonte (a `en` foi sincronizada). Decisão do usuário: manter, regerar a partir de outra fonte, ou remover.

4. **Mudanças de conteúdo relevantes na fonte** (site estava desatualizado, agora alinhado):
   - `readme`: removida a seção "How to Compile the Executable Locally" (PyInstaller) e trocada a distribuição por binário/GitHub Releases → **PyPI (`pip install gitpr-cli`)**; auto-updater agora "bloqueia a execução" quando há versão nova; `SCRIPTS_LANG` → `SCRIPTS_INSTALLED_LANG`; nova entrada no índice para `release-notes`.
   - `hooks-versioning`: maior diff do run (~103 linhas por variante).
   - `auto-update`: reescrito para o modelo PyPI.
   - `mcp-integration`: removida a menção a "standalone binary" nos pré-requisitos.
   - `version-markers`: "released binary" → "published package".
   - `testar_sem_usar_pypi`: aviso sobre artefatos remanescentes em `/dist`.

5. **Ordem de inserção no menu.** As entradas novas foram **anexadas ao fim** da seção "Technical Documentation" (e não intercaladas alfabeticamente), seguindo a convenção das últimas adições do menu (`metricas-telemetria`, `otimizacao-de-tokens`, `como_reverter_…`, `github-issue-prompt-…`, `testar_sem_usar_pypi`, `github-ci-linter`, `pr-descricao-padrao`). Se preferir ordem alfabética estrita, é um ajuste simples.

## Arquivos alterados (aguardando commit do usuário)

- `public/content/docs/{config-tui,release-notes,usage-log}.{md,pt_br.md,pt_pt.md,es.md,fr.md}` — **15 novos**
- `public/content/docs/ARCHITECTURE.{md,pt_br.md,pt_pt.md,es.md,fr.md}` — atualizados
- `public/content/docs/auto-update.{md,pt_br.md,pt_pt.md,es.md,fr.md}` — atualizados
- `public/content/docs/hooks-versioning.{md,pt_br.md,pt_pt.md,es.md,fr.md}` — atualizados
- `public/content/docs/mcp-integration.{md,pt_br.md,pt_pt.md,es.md,fr.md}` — atualizados
- `public/content/docs/readme.{md,pt_br.md,pt_pt.md,es.md,fr.md}` — atualizados
- `public/content/docs/skill-template.{md,pt_br.md,pt_pt.md,es.md,fr.md}` — atualizados
- `public/content/docs/testar_sem_usar_pypi.md` — atualizado
- `public/content/docs/version-markers.md` — atualizado
- `public/content/menu.json` — +15 entradas

Total: **48 arquivos** (33 modificados + 15 novos). Nenhum arquivo foi removido.
