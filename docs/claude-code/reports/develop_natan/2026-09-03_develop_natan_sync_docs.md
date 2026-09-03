# Relatório — Sync Docs (2026-09-03)

Sincronização de `public/content/docs/` com a documentação técnica do repositório GitPR CLI, executada via skill `/sync-docs`. Branch: `develop_natan`.

## 1. Nota sobre a fonte (caminho do repositório)

O caminho documentado na skill (`C:\Users\nataniel\projetos\python\gitpr\docs\`) **não existe mais**. O repositório GitPR CLI foi movido para **`C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\`** (irmão do site, origin `git@github.com:gitpr-cli/gitpr.git`). A sincronização usou esse caminho como fonte. **Sugestão:** atualizar o caminho na skill `/sync-docs`.

O repositório fonte também mudou de organização no GitHub: os documentos referenciam agora `github.com/gitpr-cli/gitpr.git` (antes `github.com/natanfiuza/gitpr`). O `CLAUDE.md` do site e a skill ainda citam a URL antiga (informativo).

## 2. Diagnóstico

Comparação tópico a tópico e variante a variante (fonte é autoridade), com mapeamento de sufixos `es_es → es`, `fr_fr → fr` e caso especial `readme` (fonte = `README*` na raiz do repo gitpr).

**Estado da fonte:** 59 arquivos **staged** no repositório gitpr (58 modificados + 1 novo: `docs/gemini/reports/develop_natan/2026-09-03_update_repo_urls.md`), 0 unstaged — a migração de URLs `natanfiuza/gitpr → gitpr-cli/gitpr.git` está preparada para commit (datada de 2026-09-03). A sincronização espelhou o conteúdo da *working tree* (estado pretendido).

**Conteúdo divergente (site desatualizado) — 6 tópicos × 5 variantes = 30 arquivos:**
Todas as divergências são a migração de URLs do repositório (`github.com/natanfiuza/gitpr` → `github.com/gitpr-cli/gitpr.git`) e, secundariamente, EOL: os arquivos da fonte estão **CRLF na working tree** (checkout Windows, autocrlf=true; blobs commitados são LF), enquanto o site é **LF puro** (`.gitattributes` com `* text=auto eol=lf`).

| Tópico | Mudança de conteúdo (variante EN) |
|---|---|
| `auto-update` | Link "GitHub Releases" → `github.com/gitpr-cli/gitpr.git/releases` |
| `github-ci-linter` | Link `git clone` → `gitpr-cli/gitpr.git.git` ⚠️ (typo, ver §5) |
| `map-reduce-diff` | Link "Repository" → `github.com/gitpr-cli/gitpr.git` |
| `skill-template` | Link templates → `github.com/gitpr-cli/gitpr.git/tree/main/templates/` |
| `smart-excludes` | Links dos JSONs de exclusão e do repositório |
| `readme` | Diversos: links de docs por flag (`--status`, `--linter-setup`, `--plugins` etc.) e `git clone` ⚠️ (typo, ver §5) |

**Tópicos só no site (não tocados, exclusivos do site):** `caveman-commit`, `chat-interativo` (`readme` é caso especial).

**Tópicos só na fonte (faltando no site):** nenhum.

**menu.json:** todos os 37 tópicos em disco têm entrada nas 5 línguas (37 itens por seção em "Technical Documentation" / "Documentação Técnica" / "Documentation Technique" / "Documentación Técnica"). Nenhum tópico novo foi copiado → **nenhuma alteração no menu** foi necessária.

## 3. Ações executadas

Cópia integral da fonte → site com mapeamento de sufixos e **normalização LF** (conteúdo preservado byte a byte; apenas CRLF → LF, alinhado à convenção do repo do site — sem efeito na renderização do markdown). **30 arquivos sobrescritos:**

- `auto-update.{md,pt_br,pt_pt,es,fr}` ← `docs/auto-update.{md,pt_br,pt_pt,es_es,fr_fr}`
- `github-ci-linter.{md,pt_br,pt_pt,es,fr}` ← `docs/github-ci-linter.{...}`
- `map-reduce-diff.{md,pt_br,pt_pt,es,fr}` ← `docs/map-reduce-diff.{...}`
- `skill-template.{md,pt_br,pt_pt,es,fr}` ← `docs/skill-template.{...}`
- `smart-excludes.{md,pt_br,pt_pt,es,fr}` ← `docs/smart-excludes.{...}`
- `readme.{md,pt_br,pt_pt,es,fr}` ← `README.{md,pt_br,pt_pt,es_es,fr_fr}` (raiz do repo gitpr)

## 4. Verificação

- ✅ **Conteúdo:** `diff` EOL-insensível fonte ↔ site acusa **zero divergências** nos tópicos comuns (155 variantes + 5 do README).
- ✅ **Cobertura de variantes canônicas:** 32 tópicos com 5/5 (`.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md`).
- ✅ **Nenhum sufixo `.es_es`/`.fr_fr`** criado no site (site permanece limpo — 0 legados).
- ✅ **EOL:** todos os arquivos de `public/content/docs/` em LF puro (0 bytes CR).
- ✅ **menu.json:** JSON válido; 37/37 tópicos com entrada nas 5 línguas.

**Lacunas de variantes (5 tópicos monolíngues — sem traduções inventadas, mantidas como na fonte/exclusivas do site):**

| Tópico | Situação |
|---|---|
| `como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `version-markers` | Fonte só tem `.md` — lacuna conhecida (já reportada em 2026-09-01) |
| `caveman-commit` | Exclusivo do site, só `.md` |
| `testar_sem_usar_pypi` | Fonte só tem `.md`; o site mantém 5/5 (traduções próprias do site além da fonte — preservadas) |

## 5. Anomalias reportadas (decisão do usuário)

1. **⚠️ Typos `.git.git` copiados da fonte** — a migração de URLs introduziu `github.com/gitpr-cli/gitpr.git.git` (duplo `.git`) em **10 arquivos da fonte** (working tree staged): `github-ci-linter.{md,pt_br,pt_pt,es,fr}` (linha `git clone`) e `README.{md,pt_br,pt_pt,es_es,fr_fr}` (linha `git clone`). O site foi sincronizado verbatim (fonte é autoridade), portanto os links quebrados existem **também no site agora**. **Sugestão:** corrigir na fonte (repo gitpr) antes do commit — o site será re-sincronizado automaticamente na próxima execução, ou a correção pode ser feita manualmente no site em paralelo.
2. **Fonte com 59 mudanças staged (não commitadas)** — o site espelha o estado pretendido da migração de URLs. Se a fonte for editada antes do commit (ex.: correção dos typos acima), re-executar o `/sync-docs`.
3. **Caminho da fonte desatualizado na skill `/sync-docs`** — aponta para `python\gitpr` (inexistente); atualizar para `pessoal\gitpr_projeto\gitpr`.

## 6. Arquivos alterados

- `public/content/docs/auto-update.{md,pt_br,pt_pt,es,fr}`
- `public/content/docs/github-ci-linter.{md,pt_br,pt_pt,es,fr}`
- `public/content/docs/map-reduce-diff.{md,pt_br,pt_pt,es,fr}`
- `public/content/docs/skill-template.{md,pt_br,pt_pt,es,fr}`
- `public/content/docs/smart-excludes.{md,pt_br,pt_pt,es,fr}`
- `public/content/docs/readme.{md,pt_br,pt_pt,es,fr}`
- Este relatório (`docs/claude-code/reports/develop_natan/2026-09-03_develop_natan_sync_docs.md`)
