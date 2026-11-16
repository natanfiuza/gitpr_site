# Relatório de Tarefa — Análise de Pendências dos Relatórios (`v0.0.25`) e Roteiro em `docs/prompts`

- **Data:** 2026-11-14
- **Branch:** `develop_natan`
- **Repositório:** `gitpr_site` (`c:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr_site`)
- **Fluxo utilizado:** `/plan` + `/grill-with-docs`

---

## 1. Objetivo

Analisar os relatórios de estado do GitPR (`public/content/relatorio.pt_br.md` e `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs\reports\relatorio_estado_v0.0.19.md` a `v0.0.25.md`) com foco nas seções **Próximos Passos** e **Internacionalização e Documentação**, validar cada pendência diretamente no código-fonte, testes e arquivos Markdown, registrar os fatos no survey obrigatório e gerar um prompt/roteiro detalhado em etapas para execução por agente de código em `docs/prompts/`.

---

## 2. Entregas Geradas

1. **Prompt estruturado em etapas (`docs/prompts`):**
   - `docs/prompts/20261114_pendencias_relatorio_v0_0_25_prompt.md`
   - Contém:
     - **Parte I — Diagnóstico Verificado:** contraste entre itens concluídos até `v0.0.25` vs. pendências abertas reais em `Próximos Passos` e `Internacionalização e Documentação`.
     - **Parte II — Roteiro de Execução em 7 Etapas:**
       1. Alinhar SSOT `1.8.0` (`CLAUDE.md`, `GEMINI.md`) e resumo do `CHANGELOG.md` (`test_version_consistency.py`);
       2. Alinhar a tag Git `v1.7.0` (`b2688c4` → `2354a30`);
       3. Completar documentação e notas operacionais da `v0.0.25` (`config-tui`, `pull-request-publication`, `metricas-telemetria`, `scm-multiforge`);
       4. Zerar `_PARITY_EXCEPTIONS` em `tests/unit/test_docs_translation_parity.py` (8 tópicos, incluindo os 4 blocos YAML sem fechamento de `linter-regras-customizadas.pt_br.md`) + corrigir as 12 linhas faltantes dentro de cercas de `plugins-system.pt_br.md`;
       5. Internacionalizar `github-issue-prompt-com-gh` (normalizando `.md` para inglês, preservando português em `.pt_br.md`, criando `.pt_pt.md`, `.es_es.md`, `.fr_fr.md` e limpando `_EN_ONLY`);
       6. Implementar suporte a host customizado de GitHub Enterprise em `GitHubProvider.parse_repo_ref` (`src/infrastructure/scm/github_provider.py`), testes e documentação;
       7. Sincronizar `gitpr_site` via `/sync-docs` (atualizando `.claude/skills/sync-docs/SKILL.md`) e `/update-relatorio` quando houver novo relatório.
     - **Parte III — Escopo Separado:** ação operacional humana (publicação VS Code / Open VSX com `scripts/marketplace-setup-wizard.sh`, `VSCE_PAT`, `OVSX_PAT` e tag `ext-v*`) e backlog estratégico (mais provedores de IA).
2. **Survey do `/grill-with-docs`:**
   - `docs/survey/20261114_analise_pendencias_relatorios_surveyfacts.md`
3. **Relatório de tarefa (`GEMINI.md`):**
   - `docs/gemini/reports/develop_natan/2026-11-14_develop_natan_relatorio_pendencias_prompts.md`

---

## 3. Verificações Executadas

- Conferência direta dos arquivos fonte no `gitpr` e espelhos no `gitpr_site`:
  - `CLAUDE.md` / `GEMINI.md` (linha 8 ainda em `1.7.0`);
  - `CHANGELOG.md` (seção `[1.8.0]` com texto residual de `1.7.0` nas linhas 6 e 22);
  - `src/infrastructure/scm/github_provider.py` (linhas 81–94 com regex restrita a `github.com`);
  - `src/scm_settings.py` e `docs/adr/0020-per-repo-scm-provisioning-migration.md` (tratamento de `.env` read-only);
  - `tests/unit/test_version_consistency.py` e `tests/unit/test_docs_translation_parity.py` (`_EN_ONLY` e 8 `_PARITY_EXCEPTIONS`);
  - Varredura estrutural nos `.md` em `public/content/docs/` e leitura direcionada de `config-tui`, `pull-request-publication`, `metricas-telemetria`, `scm-multiforge`, `github-issue-prompt-com-gh`, `ARCHITECTURE`, `check-ci`, `git-hooks-locais`, `git-status`, `linter-regras-customizadas`, `plugins-system` e `skill-template`.
- Validação de criação, integridade e ausência de bloqueios/cercas desbalanceadas nos 3 arquivos Markdown gerados nesta tarefa.

---

## 4. Próximos Passos

Executar as Etapas 1 a 6 no repositório `gitpr` seguindo `docs/prompts/20261114_pendencias_relatorio_v0_0_25_prompt.md` e, em seguida, executar a Etapa 7 (`/sync-docs` e `/update-relatorio`) neste repositório (`gitpr_site`).
