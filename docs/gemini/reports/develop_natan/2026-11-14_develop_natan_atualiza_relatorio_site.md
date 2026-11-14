# Relatório da Tarefa: Atualização do Relatório de Status do Site (v0.0.24 → v0.0.25)

**Data:** 2026-11-14  
**Branch:** `develop_natan`  
**Skill executada:** `/update-relatorio` ([`.claude/skills/update-relatorio/SKILL.md`](.claude/skills/update-relatorio/SKILL.md))  
**Fonte de referência:** `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs\reports\relatorio_estado_v0.0.25.md`

---

## 1. Resumo da Atualização

O relatório de status do GitPR CLI no site foi atualizado da versão **v0.0.24 (2026-11-10)** (CLI `1.7.0`, `__lang_version__ = v0.0.42`, `1787 × 6` chaves i18n, `3436` cenários em `160` módulos) para a versão **v0.0.25 (2026-11-14)** (CLI `1.8.0` publicada no PyPI e tag `v1.8.0` no commit `6cc0508` / merge da PR #325, `__lang_version__ = v0.0.43`, `1807 × 6` chaves i18n, `115 ConfigField` / `18` categorias / `55` chaves `seeded` no `DEFAULT_CONFIG`, `3469` cenários em `166` módulos).

### Principais novidades refletidas nos 5 idiomas

- **Map-Reduce por provedor e fatiamento em cascata ([ADR-0022](docs/adr/0022-map-reduce-per-provider-token-partitioning.md)):** `get_map_reduce_thresholds(provider)` (3 níveis de precedência com fallback `90.000`/`50.000`, `_positive_int_from_raw` e `max_part_tokens <= max_tokens`), cascata arquivo → hunks (`@@`, `_split_giant_file`) → linhas/caracteres (`_split_line_chunk`), pré-reduce intermediário e os 6 campos `GITPR_MAP_REDUCE_MAX_TOKENS_{GEMINI,DEEPSEEK,OLLAMA}` e `GITPR_MAP_REDUCE_MAX_PART_TOKENS_{GEMINI,DEEPSEEK,OLLAMA}` no schema (`src/config_schema.py`).
- **Detecção de forge, aviso de fallback e contrato SCM ([ADR-0020](docs/adr/0020-per-repo-scm-provisioning-migration.md)):** correção em `detect_provider_from_remote()` para reconhecer `github` antes de devolver `default`, emissão de `_default_provider_warning()` em `resolve_scm_settings`, atualização de `docs/scm-multiforge.*` nos 5 idiomas e ampliação de `tests/scm/` (`370 → 391` cenários).
- **Semântica estrita de `GITPR_SKIP_UPDATE_CHECK` ([ADR-0021](docs/adr/0021-strict-boolean-update-check-migration.md)):** formalização contratual e novo guarda `tests/unit/test_auto_update_docs_parity.py` (4 cenários).
- **Extensão de editor e documentação de métricas:** tradução de `docs/marketplace-publishing-guide.*` e sincronização de `docs/metricas-telemetria.*` nos 5 idiomas, adição do assistente `scripts/marketplace-setup-wizard.sh` (5 etapas, validação ao vivo de `VSCE_PAT`/`OVSX_PAT`), identificador `natanfiuza.gitpr` na seção de Distribuição e inclusão dos novos testes de paridade/sintaxe em `tests/unit/`.
- **Estabilidade da suíte no Windows e release 1.8.0:** skip de `TestCommitAppHeadlessFlow.test_headless_full_flow` em `win32`, mock de `src.main.check_internet_connection` em `TestCliCommitRouting`, publicação da versão **1.8.0** no PyPI (`## [1.8.0] - 2026-11-14`), atualização do aviso de inconsistência SSOT (`CLAUDE.md`/`GEMINI.md` ainda em `1.7.0`, resultando em 2 falhas conhecidas em `tests/unit/test_version_consistency.py`), menção ao resumo `[1.8.0]` no changelog ainda iniciando com `"Version 1.7.0 improves…"` e manutenção da pendência herdada do tag `v1.7.0` (`b2688c4`).

---

## 2. Arquivos Alterados e Idiomas

| Arquivo | Idioma | Cabeçalho H1 Verificado | Data no Rodapé |
| --- | --- | --- | --- |
| `public/content/relatorio.md` | EN | `# **🚀 Project Status Report: GitPR CLI — v0.0.25 (2026-11-14)**` | `2026-11-14` |
| `public/content/relatorio.pt_br.md` | PT-BR | `# **🚀 Relatório de Status do Projeto: GitPR CLI — v0.0.25 (2026-11-14)**` | `2026-11-14` |
| `public/content/relatorio.pt_pt.md` | PT-PT | `# **🚀 Relatório de Status do Projeto: GitPR CLI — v0.0.25 (2026-11-14)**` | `2026-11-14` |
| `public/content/relatorio.es.md` | ES | `# **🚀 Informe de Estado del Proyecto: GitPR CLI — v0.0.25 (2026-11-14)**` | `2026-11-14` |
| `public/content/relatorio.fr.md` | FR | `# **🚀 Rapport de Statut du Projet : GitPR CLI — v0.0.25 (2026-11-14)**` | `2026-11-14` |

**Artefato criado nesta tarefa:**
- `docs/gemini/reports/develop_natan/2026-11-14_develop_natan_atualiza_relatorio_site.md`

---

## 3. Verificação Realizada

Após todas as edições finais, foi realizada inspeção direta por leitura (`read_file`) nos 5 arquivos em `public/content/relatorio*.md`, confirmando:
- Consistência de versão e data no título H1 (`v0.0.25 (2026-11-14)`) e no rodapé (`2026-11-14`) em todos os 5 idiomas.
- Consistência numérica em todas as variantes: CLI `1.8.0`, `__lang_version__ = v0.0.43`, `1807 × 6` chaves i18n, schema com `115` `ConfigField` / `18` categorias / `55` chaves `seeded` (`DEFAULT_CONFIG`), `3469` cenários coletados em `166` módulos de teste e `24 commits, 12 merges` / `11` novos PRs (`#311`–`#325`).
- Pacote da extensão corrigido para `natanfiuza.gitpr` nos 5 arquivos e preservação dos links relativos (`docs/adr/...`) e identificadores técnicos sem tradução indevida.
- Isolamento idiomático por arquivo (PT-BR, EN, PT-PT, ES e FR) sem mistura de idiomas nem referências residuais a `1.6.0` no aviso de `test_version_consistency.py`.
