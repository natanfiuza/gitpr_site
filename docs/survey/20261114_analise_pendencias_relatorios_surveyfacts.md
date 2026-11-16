# Survey — Análise de Pendências dos Relatórios de Estado (`v0.0.25`)

Data: 2026-11-14
Branch: `develop_natan`
Fontes principais inspecionadas:
- `public/content/relatorio.pt_br.md` (`gitpr_site`)
- `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs\reports\relatorio_estado_v0.0.19.md` a `v0.0.25.md`
- `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\{CLAUDE.md,GEMINI.md,CHANGELOG.md,src/infrastructure/scm/github_provider.py,src/scm_settings.py,docs/adr/0020-per-repo-scm-provisioning-migration.md}`
- `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\tests\unit\{test_version_consistency.py,test_docs_translation_parity.py}` e `public/content/docs/*.md`

---

## 1. Contexto da tarefa

O usuário solicitou (`/plan` + `/grill-with-docs`) analisar os relatórios de estado do GitPR — especificamente as seções **"Próximos passos"** e **"Internacionalização e Documentação"** —, levantar tudo o que **não foi feito** (validado diretamente no código-fonte, testes e arquivos Markdown atuais) e salvar um relatório completo e detalhado em `docs\prompts`, estruturado em etapas executáveis por um agente de código.

---

## 2. Decisoes das rodadas

### Rodada 1 (respondida pelo usuário com "recomendado" em Q1, Q2 e Q3)
- **Q1 — Destino e formato do artefato em `docs\prompts`:** salvar em `docs/prompts/20261114_pendencias_relatorio_v0_0_25_prompt.md`, unindo diagnóstico do que não foi feito e roteiro em etapas auto-contido para execução por um agente de código.
- **Q2 — Escopo da seção "Próximos passos":** incluir **todas** as pendências verificadas, dividindo claramente entre:
  - Etapas executáveis pelo agente de código (SSOT `1.8.0` em `CLAUDE.md`/`GEMINI.md`, `CHANGELOG.md`, alinhamento da tag `v1.7.0`, traduções/notas da `v0.0.25` e suporte a GitHub Enterprise em `GitHubProvider.parse_repo_ref`);
  - Seção separada de **Ação Operacional Humana** (`scripts/marketplace-setup-wizard.sh` com `VSCE_PAT`/`OVSX_PAT` e tag `ext-v*`) e **Backlog Estratégico** (mais provedores de IA).
- **Q3 — Escopo de "Internacionalização e Documentação":** cobrir integralmente os 4 blocos identificados na auditoria: (1) tópicos explícitos da `v0.0.25` (`config-tui`, `pull-request-publication`, `metricas-telemetria`, `scm-multiforge`), (2) todas as 8 entradas de `_PARITY_EXCEPTIONS` + deriva oculta em `plugins-system.pt_br.md`, (3) internacionalização de `github-issue-prompt-com-gh.md` para 5 idiomas (`52/52` tópicos) e (4) etapa final de sincronização no `gitpr_site` via `/sync-docs` e `/update-relatorio`.

---

## 3. Relatorio de fatos levantados

### 3.1 Baseline do relatório `v0.0.25` (`public/content/relatorio.pt_br.md` / `relatorio_estado_v0.0.25.md`)
- Versão reportada do CLI: `1.8.0` | `lang`: `v0.0.43` (`1.807 × 6` chaves = `0` lacunas) | `hooks`: `v0.0.4` | `115 ConfigField` (`18` categorias, `55` semeados) | `3.469` cenários de teste em `166` módulos | `52` tópicos canônicos (`51` em 5 idiomas + `1` monolíngue) | `CONTEXT.md` com `53` termos | `11` ADRs em `docs/adr/`.
- Referência histórica: o item *"Provedor Anthropic Claude"* presente em `v0.0.19`–`v0.0.23` foi removido intencionalmente no commit `faeecb8` (`remove Anthropic Claude provider reference from v0.0.24 report`).

### 3.2 Fatos verificados — Seção `🚧 Próximos Passos`
- **Já concluído na `v0.0.25`:** guardrails do `OllamaProvider` (`GITPR_OLLAMA_TIMEOUT`, `timeout >= 180s` em workflows locais, `GITPR_OLLAMA_ALLOW_REMOTE`), expansão da suíte headless, aviso explícito de remoto desconhecido e `ADR-0020`.
- **Em aberto (verificado nos arquivos):**
  1. `gitpr/CLAUDE.md` (linha 8) e `gitpr/GEMINI.md` (linha 8) ainda registram `1.7.0` como `Current version`, violando `tests/unit/test_version_consistency.py`.
  2. `gitpr/CHANGELOG.md` possui seção `## [1.8.0] - 2026-11-14` (linha 3), mas a linha 6 do `### Summary` ainda diz `Version 1.7.0 improves...` e a linha 22 mantém `- bump version to 1.7.0 and lang to v0.0.42 (2354a30)`.
  3. Tag `v1.7.0` herdada da `v0.0.24`: permanece apontando para `b2688c4` (`__version__ == 1.6.0`) em vez de `2354a30`.
  4. `GitHubProvider.parse_repo_ref()` (`src/infrastructure/scm/github_provider.py`, linhas 81–94) restringe o regex a `github\.com[:/]([^/]+)/([^/]+?)(?:\.git)?/?$` (limitação documentada em `docs/scm-multiforge.md`, linha 61).
  5. `migrate_legacy_scm_config()` (`src/scm_settings.py`) captura `(OSError, PermissionError)` retornando `MigrationResult(persisted=False, warning=...)` e mantendo chaves em memória; falta documentar esse comportamento para `.env` read-only / runners efêmeros em `docs/scm-multiforge.*` (complementando `ADR-0020 §4`).
  6. Publicação VS Code/Open VSX depende de `scripts/marketplace-setup-wizard.sh` com PATs reais (`VSCE_PAT`/`OVSX_PAT`) — operacional humano.

### 3.3 Fatos verificados — Seção `🌐 Internacionalização e Documentação`
- **Tópico monolíngue (`_EN_ONLY`):** `github-issue-prompt-com-gh.md` (87 linhas) está registrado em `_EN_ONLY`, porém seu conteúdo atual está em **português**, exigindo normalização para inglês em `.md` e criação de `.pt_br.md`, `.pt_pt.md`, `.es_es.md`, `.fr_fr.md`.
- **`_PARITY_EXCEPTIONS` em `tests/unit/test_docs_translation_parity.py` (8 tópicos):**
  - `ARCHITECTURE`: EN `277L / H=27 / R=14 / B=77 / F=4` vs traduções `254L / H=26 / R=14 / B=69 / F=2` (falta `§20 Project-Local PR Target`).
  - `check-ci`: EN `271L / F=12` vs `.pt_br` `245L / F=10` e `.pt_pt, .es, .fr` `241L / F=10` (falta bloco ` ```json ` completo em `JSON Envelope (--format json)`).
  - `git-hooks-locais`: EN `112L / H=10 / F=10` vs `.pt_br` `114L / H=10 / F=8` (linhas 59–61 sem fence ` ``` `) e vs `.pt_pt` (`84L / H=9 / F=4`) / `.es, .fr` (`83L / H=9 / F=4`) (falta seção secret scanning + blocos bash).
  - `git-status`: EN/`.pt_br` `148L / F=14` vs `.pt_pt, .es, .fr` `124L / F=8` (faltam 3 blocos fenced de saída/JSON).
  - `linter-regras-customizadas`: EN `232L / H=16 / R=9 / B=8 / F=10` vs `.pt_br` `230L / H=14 / R=9 / B=9 / F=6` (os 4 blocos ` ```YAML ` das linhas 20, 64, 90 e 102 não têm fechamento ` ``` `, há `## ---` na linha 52 e escapes WYSIWYG nos snippets) e vs `.pt_pt, .es, .fr` `142L / H=10 / R=0 / B=6 / F=4` (faltam `External Linter Timeout` e toda a seção `Built-in Secret Scanning`).
  - `mentor-mode`: EN/`.pt_br` `62L / H=6 / R=11 / B=3 / F=2` vs `.pt_pt, .es, .fr` `34L / H=4 / R=4 / B=0 / F=2`.
  - `pull-request-publication`: EN `617L / H=54 / R=99 / B=16 / F=58` vs 4 traduções `520L / H=47 / R=91 / B=13 / F=46` (faltam alerta `§9.1`, `§9.4`, linha `GITPR_SKIP_PROJECT_CONFIG_SEED` em `§14`, e Exemplos 9–10 em `§15`).
  - `skill-template`: EN `91L / R=20` vs 4 traduções `87L / R=18` (faltam `.gitpr.release.md` em `§1`, `gitpr release` em `§2`, e parágrafo `Release skill auto-download`).
- **Derivas ocultas fora de `_PARITY_EXCEPTIONS`:**
  - `plugins-system.pt_br.md`: `192` linhas vs `204` nas demais variantes (faltam 12 linhas dentro dos 2 blocos fenced de exemplo *Security Auditor* e *PHPUnit Test Generator*, ignorados por `_scan()`).
  - `config-tui.*`: `GITPR_SKIP_PROJECT_CONFIG_SEED` presente apenas no EN (linha 159); linha 259 nas 5 variantes mantém nota sobre `gitpr -h config`.
- **Estado atual no `gitpr_site`:** `public/content/docs` está sincronizado com `gitpr/docs` conforme `docs/gemini/reports/develop_natan/2026-11-14_develop_natan_sync_docs.md`, mantendo os 4 tópicos exclusivos do site (`chat-interativo`, `testar_sem_usar_pypi`, `como_reverter_commit_git_localmente`, `caveman-commit`) e `.claude/skills/sync-docs/SKILL.md` (linha 73) citando `github-issue-prompt-com-gh` como monolíngue.
