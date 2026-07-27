# Relatório: Sincronização de Documentação

**Data:** 2026-07-26
**Branch:** develop_natan
**Tarefa:** sync_docs

## Objetivo

Sincronizar a documentação do site GitPR (`public/content/docs/`) com a origem canônica em `C:\Users\nataniel\projetos\python\gitpr\docs` e o README.md raiz do projeto Python.

## Análise Inicial

### Origem (`python/gitpr/docs/`)
- ~85 arquivos Markdown de documentação de usuário (21 tópicos com traduções para pt_br, pt_pt, es_es, fr_fr)
- 23 relatórios de sessão do Claude Code (em `claude-code/reports/`)
- 25 planos de desenvolvimento (em `plans/`)
- 5 relatórios de estado (em `reports/`)
- 4 arquivos binários (logo.png, logo.psd, PDFs)

### Origem (`python/gitpr/` raiz)
- `README.md` + 4 traduções (es_es, fr_fr, pt_br, pt_pt)

### Destino (`public/content/docs/`)
- ~100 arquivos Markdown antes da sincronização
- 22 tópicos no menu de documentação

## Comparação

### Arquivos em comum (90 arquivos)
Todos os 90 arquivos presentes em ambos os diretórios tinham **hashes MD5 idênticos** — nenhuma atualização necessária para arquivos existentes.

### Arquivos novos copiados (15 arquivos)

| Arquivo | Descrição |
|---------|-----------|
| `ARCHITECTURE.md` | Documento mestre de arquitetura do GitPR CLI |
| `caveman-commit.md` | Template/estilo ultra-compacto de commit |
| `como_reverter_commit_git_localmente.md` | Guia de revert vs reset no Git |
| `github-issue-prompt-com-gh.md` | Formatação de issues GitHub via `gh` CLI |
| `gitpr-issue-option.md` | Guia detalhado da flag `--issue` e 3 motores |
| `guia-regex-gitpr.md` | Guia de regex performáticas para o linter |
| `metricas-telemetria.md` + 4 traduções | Sistema de métricas e telemetria offline |
| `otimizacao-de-tokens.md` | Otimização de tokens nos arquivos de skill |
| `testar_sem_usar_pypi.md` | Testes locais com `pip install -e .` |
| `install-wizard.es_es.md` | Tradução ES (consistente com naming `es_es`) |
| `install-wizard.fr_fr.md` | Tradução FR (consistente com naming `fr_fr`) |

### README sincronizados (5 arquivos atualizados)

| Origem | Destino | Tamanho |
|--------|---------|---------|
| `README.md` | `readme.md` | 22.758 bytes |
| `README.pt_br.md` | `readme.pt_br.md` | 24.093 bytes |
| `README.pt_pt.md` | `readme.pt_pt.md` | 24.278 bytes |
| `README.es_es.md` | `readme.es.md` | 24.539 bytes |
| `README.fr_fr.md` | `readme.fr.md` | 25.891 bytes |

Todos os READMEs de origem eram mais recentes (maiores) que as versões no site.

### Arquivos preservados (site-specific, não vieram da origem)
- `chat-interativo.md` + 4 traduções (página específica do site)
- `readme.md` + traduções (já existiam, foram atualizados da origem)
- `install-wizard.es.md` e `install-wizard.fr.md` (mantidos; conteúdo idêntico às novas variantes `_es`/`_fr`)

## Atualização do menu.json

Adicionadas 9 novas entradas em cada um dos 5 locales (en, pt_br, pt_pt, fr, es):

1. `docs/ARCHITECTURE` — Architecture / Arquitetura
2. `docs/caveman-commit` — Caveman Commit Style
3. `docs/gitpr-issue-option` — Issue Creation Guide
4. `docs/guia-regex-gitpr` — Regex Guide for Linter
5. `docs/metricas-telemetria` — Metrics & Telemetry
6. `docs/otimizacao-de-tokens` — Token Optimization
7. `docs/como_reverter_commit_git_localmente` — Reverting Git Commits
8. `docs/github-issue-prompt-com-gh` — GitHub Issues with GH CLI
9. `docs/testar_sem_usar_pypi` — Local Testing (without PyPI)

## Resultado Final

- **Antes:** ~100 arquivos `.md` em `public/content/docs/`
- **Depois:** 117 arquivos `.md`
- **Diferença:** +15 arquivos novos copiados + 5 READMEs atualizados
- **Menu:** 9 novas rotas de documentação em todos os 5 idiomas

## Observações

1. **Naming de locales:** O target tem uma inconsistência — a maioria dos arquivos usa sufixo `es_es`/`fr_fr`, mas `install-wizard` e `readme` usam apenas `es`/`fr`. O controller espera `?lang=es` (`.es.md`). Isso é um bug pré-existente não corrigido neste sync.

2. **Arquivos não sincronizados:** Diretórios `claude-code/`, `plans/`, `reports/` e binários (PDF, PSD, PNG) da origem não foram copiados — são artefatos de desenvolvimento interno, não documentação pública.
