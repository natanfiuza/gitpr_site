# Relatório — Sincronização da documentação técnica (sync-docs)

- **Data**: 2026-09-17
- **Branch**: `develop_natan`
- **Task**: `sync_docs`
- **Skill**: `/sync-docs`

## Contexto

Run do `/sync-docs`: sincronizar `public/content/docs/` com a documentação técnica do repo GitPR CLI.

- **Fonte (autoridade)**: `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs\*.md` (nível superior) + `gitpr\README.md` e variantes (caso especial `readme`).
- **Destino**: `public/content/docs/*.md` (44 tópicos, 197 arquivos).
- **Mapeamento de sufixos**: fonte `.es_es` → site `.es`; fonte `.fr_fr` → site `.fr`. Demais sufixos idênticos.

## Diagnóstico

Comparação tópico a tópico e variante a variante (197 arquivos de destino × 195 de origem + 5 do README raiz):

| Situação | Tópicos |
| --- | --- |
| Só na fonte (faltando no site) | `fix-command`, `review-pr` |
| Divergência real de conteúdo | `code-review-ia`, `commit-message-ia`, `issue-tui-help`, `linter-regras-customizadas`, `providers-ia`, `skill-template`, `suggested-reviewers`, `readme` |
| Divergência só de line-ending | `smart-excludes`, `github-ci-linter`, `map-reduce-diff`, `testar_sem_usar_pypi` |
| Só no site (não tocados) | `caveman-commit`, `chat-interativo` |
| Duplicados legados `.es_es`/`.fr_fr` no site | nenhum |

**Falso positivo descartado**: os 4 tópicos de "divergência só de line-ending" têm o **blob commitado idêntico** nos dois repos (`git show HEAD:…` conferido nos 3 pares en/pt_br/pt_pt). A diferença existe só no working tree — a fonte está em CRLF (checkout Windows) e o site em LF. Nenhuma alteração foi feita nesses arquivos.

## O que foi feito

### 1. Tópicos novos copiados da fonte

| Tópico | Variantes copiadas | Linhas/arquivo |
| --- | --- | --- |
| `fix-command` | `.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md` (←`.es_es`), `.fr.md` (←`.fr_fr`) | 243 |
| `review-pr` | `.md`, `.pt_br.md` | 229 |

Copiados com o conteúdo markdown intacto; apenas o sufixo de idioma foi renomeado.

### 2. Tópicos atualizados (8 tópicos × 5 variantes = 40 arquivos)

Todas as variantes foram atualizadas, não só a inglesa.

| Tópico | Mudança de conteúdo (idêntica nas 5 línguas) | Δ linhas (en) |
| --- | --- | --- |
| `code-review-ia` | 3 → **4 modos** de review; nova §1.4 "Remote Pull Request Review — `gitpr review-pr <number>`" com tabela de características; nota de que no review remoto só as regras YAML do linter rodam; exemplo `gitpr review-pr 123 --provider deepseek`; nota de que o `-p` do grupo raiz não é herdado por subcomandos; linha `OUTPUT_FILE_NAME_REVIEW` passa a listar `-r`, `review-pr` | +28 −2 |
| `commit-message-ia` | `.gitpr.commit.md`: "na raiz do projeto" → "dentro de `.gitpr/skill/`" | +1 −1 |
| `issue-tui-help` | `.gitpr.issue.md`: mesma correção de localização | +1 −1 |
| `linter-regras-customizadas` | `.gitpr.linter.yml`: mesma correção de localização | +1 −1 |
| `providers-ia` | Dois provedores → **três**: Gemini, DeepSeek e **Ollama** | +1 −1 |
| `skill-template` | Correção de URL: `gitpr-cli/gitpr.git/tree/…` → `gitpr-cli/gitpr/tree/…` | +1 −1 |
| `suggested-reviewers` | §4 reescrita: resolução de identidade dos revisores para logins, modal de revisores não anexados, `resolve_candidates()` / `resolve_typed_reviewers()`, `identity_key()` / `normalize_identity()`, novos métodos SCM `get_commit_author_login()` / `get_user_login()`, view dict ganha `resolutions` | +25 −6 |
| `readme` (← README raiz) | Contagens MCP: 12 → **14 ferramentas**, 15 → **18 recursos**; `-s`/`--skill` gera os arquivos dentro de `.gitpr/skill/`; 2 novas ferramentas MCP (`list_fix_candidates`, `review_remote_pr`); links para as docs de `review-pr` e `fix-command` | +8 −4 |

### 3. `menu.json`

Adicionadas 10 entradas (2 tópicos × 5 idiomas), sob "Technical Documentation", logo após `code-review-ia`:

| Idioma | `review-pr` | `fix-command` |
| --- | --- | --- |
| en | Remote PR Review | Fix Command |
| pt_br | Revisão de PR Remoto | Comando de Correção |
| pt_pt | Revisão de PR Remoto | Comando Fix |
| fr | Revue de PR à Distance | Commande Fix |
| es | Revisión de PR Remoto | Comando Fix |

Títulos derivados do H1 de cada arquivo traduzido. JSON validado (`json.load`) — 56 entradas por idioma, 5 blocos.

### 4. Line endings

O site declara `* text=auto eol=lf` em [.gitattributes](.gitattributes), então os arquivos copiados foram normalizados CRLF → LF (via `sed 's/\r$//'`, que toca apenas o CR de fim de linha — conteúdo preservado). Resultado: nenhum churn de line-ending no diff; cada arquivo alterado mostra só as linhas de conteúdo real.

## Verificação

| Checagem | Resultado |
| --- | --- |
| Diff de todo tópico comum contra a fonte (ignorando line-endings) | **0 divergências** — 195 variantes + 5 do README |
| Nenhum sufixo legado `.es_es`/`.fr_fr` criado no site | OK |
| `menu.json` válido e toda entrada `docs/*` com arquivo correspondente | OK |
| Todo tópico do site com entrada no menu | OK |
| Cobertura de variantes | 44 tópicos; só os casos conhecidos abaixo de 5/5 |
| `git diff --numstat` sem churn de line-ending | OK — 46 arquivos, cada um com Δ de conteúdo real |

## Lacunas de idioma e anomalias reportadas

1. **`review-pr` é monolíngue parcial na fonte**: só existem `.md` e `.pt_br.md`. As variantes `.pt_pt`, `.es` e `.fr` **não foram inventadas** — o site cai no fallback inglês ([DocsController.php:38-40](app/Http/Controllers/DocsController.php#L38-L40)). Mesma situação dos tópicos monolíngues já conhecidos (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `version-markers`), todos com 1/5 nos dois lados.
2. **`testar_sem_usar_pypi` é mais rico no site do que na fonte**: a fonte tem só `.md`, o site tem as 5 variantes. As variantes extras foram **preservadas** (a skill proíbe removê-las), e a variante inglesa foi conferida — conteúdo idêntico à fonte.
3. **Tópicos exclusivos do site, não tocados**: `caveman-commit` (1/5) e `chat-interativo` (5/5). Ambos já têm entrada no menu.
4. **Nenhum duplicado legado** `.es_es`/`.fr_fr` encontrado no site — nada a remover.
5. **4 arquivos do site com CRLF pré-existente, não tocados** (fora do escopo desta tarefa, `git status` limpo para eles): `caveman-commit.md`, `github-pat-integration.pt_br.md`, `testar_sem_usar_pypi.pt_br.md`, `untracked-files.pt_br.md`. Contrariam o `eol=lf` declarado em [.gitattributes](.gitattributes), mas git os normaliza na indexação — não há divergência commitada. Reportados para eventual normalização numa tarefa própria.

## Arquivos alterados

```
public/content/docs/fix-command{,.pt_br,.pt_pt,.es,.fr}.md            (novos, 5)
public/content/docs/review-pr{,.pt_br}.md                             (novos, 2)
public/content/docs/code-review-ia{,.pt_br,.pt_pt,.es,.fr}.md         (atualizados, 5)
public/content/docs/commit-message-ia{,.pt_br,.pt_pt,.es,.fr}.md      (atualizados, 5)
public/content/docs/issue-tui-help{,.pt_br,.pt_pt,.es,.fr}.md         (atualizados, 5)
public/content/docs/linter-regras-customizadas{,.pt_br,.pt_pt,.es,.fr}.md (atualizados, 5)
public/content/docs/providers-ia{,.pt_br,.pt_pt,.es,.fr}.md           (atualizados, 5)
public/content/docs/skill-template{,.pt_br,.pt_pt,.es,.fr}.md         (atualizados, 5)
public/content/docs/suggested-reviewers{,.pt_br,.pt_pt,.es,.fr}.md    (atualizados, 5)
public/content/docs/readme{,.pt_br,.pt_pt,.es,.fr}.md                 (atualizados, 5)
public/content/menu.json                                              (+10 linhas)
docs/claude-code/reports/develop_natan/2026-09-17_develop_natan_sync_docs.md  (este relatório)
```

Os 5 `relatorio.*.md` que aparecem como modificados em `git status` são de uma tarefa anterior (`/update-relatorio`), não desta.

## Observações

- **Conteúdo sempre literal**: os arquivos foram copiados da fonte sem edição — emojis, tabelas, links, blocos de código e separadores preservados byte a byte (exceto CRLF → LF).
- **Padrão de mudança coerente**: 4 das 8 atualizações são a mesma migração upstream de caminho (arquivos de skill/linter movidos da raiz para `.gitpr/skill/`), refletida consistentemente em `readme`, `commit-message-ia`, `issue-tui-help` e `linter-regras-customizadas`.
- **Tópicos novos têm entradas de menu em 5 idiomas** mesmo onde a página só existe em inglês — comportamento consistente com os tópicos monolíngues pré-existentes.
