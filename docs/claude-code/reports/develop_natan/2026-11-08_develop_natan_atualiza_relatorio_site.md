# Atualização do Relatório de Status do Site

- **Data:** 2026-11-08
- **Branch:** `develop_natan`
- **Skill:** `.claude/skills/update-relatorio`
- **Fonte:** `gitpr/docs/reports/relatorio_estado_v0.0.23.md`

## Versão

| | Antes | Depois |
|---|---|---|
| Relatório | v0.0.22 (2026-11-04) | **v0.0.23 (2026-11-08)** |
| CLI citada | 1.5.0 (tagueada, PyPI em 1.4.4) | **1.6.0** (no changelog; PyPI serve a 1.5.0) |
| Dicionários de idioma | v0.0.38 | **v0.0.41** |

## Arquivos alterados (5)

| Arquivo | Idioma | Linhas |
|---|---|---|
| `public/content/relatorio.md` | EN | 985 |
| `public/content/relatorio.pt_br.md` | PT-BR | 985 |
| `public/content/relatorio.pt_pt.md` | PT-PT | 985 |
| `public/content/relatorio.es.md` | ES | 985 |
| `public/content/relatorio.fr.md` | FR | 985 |

Diff total: **+1620 / −1305** (261 linhas removidas e 324 adicionadas por arquivo).

## Método

O relatório fonte cresceu de 922 para 985 linhas, com **118 blocos alterados** e 663 linhas
inalteradas. Os cinco arquivos do site estavam 1:1 linha a linha com o fonte v0.0.22, então a
atualização foi feita por **delta**, não por retradução integral:

1. **PT-BR:** reprodução byte a byte do relatório fonte (`cp` + verificação de md5
   `40ca1d16baf5fd2c29856e2db5fe43bb`). Convenção já confirmada por checksum em janelas
   anteriores; o arquivo não é tradução do inglês.
2. **EN, ES, FR, PT-PT:** `difflib.SequenceMatcher` entre o fonte v0.0.22 e v0.0.23 gerou 236
   opcodes (118 alterados). Apenas os blocos novos/alterados foram traduzidos; um script
   remontou cada arquivo, copiando das linhas `equal` do arquivo existente — portanto
   **todo texto já aprovado permaneceu idêntico byte a byte**.

Terminologia mantida conforme cada arquivo: ES `proveedor`/`puerta`, FR `Provider` (SCM) vs
`fournisseur` (IA) e `barrière`, PT-PT `provedor`, `portão`, `ficheiro(s)`.

## Verificação

| Checagem | Resultado |
|---|---|
| Linhas por arquivo | 985 nos 5 |
| Posições dos `##` | `3 27 50 670 850 880 895 937` nos 5, idênticas ao fonte |
| Cabeçalhos (`#`/`###`) | 57 em todos, mesmos níveis |
| Marcadores de lista e linhas de tabela | 558 e 199 em todos, iguais ao fonte |
| Texto em inglês não traduzido (ES/FR/PT-PT) | 0 linhas de prosa idênticas ao EN |
| Vazamento cruzado de idioma | 0 em todas as direções |
| Resíduos de versão anterior | só as ocorrências legítimas da tabela comparativa da seção "Evolução" |
| `md5sum` PT-BR == fonte | `40ca1d16baf5fd2c29856e2db5fe43bb` nos dois |
| Quebras de linha duras (dois espaços finais) | restauradas no rodapé dos 4 traduzidos |
| `./vendor/bin/pest tests/Feature/NewsletterSendCommandTest.php` | 6/6 verdes |

## Conteúdo novo incorporado

- SCM por repositório: `src/scm_settings.py`, um slot de credencial por forge
  (`GITHUB_/GITLAB_/BITBUCKET_/AZURE_DEVOPS_TOKEN_ENCRYPTED` + `*_BASE_URL`) e migração
  idempotente das chaves legadas (ADR-016).
- Fecho de issue multi-forge: `get_issue`/`get_default_branch`/`closing_line` nas quatro forges,
  registo em `.gitpr/conf/issue.yml` com migração do `github.yml`, `IssueInfo.is_open`
  (ADR-010).
- `gitpr seed` / `-sd/--seed` e wizard de instalação em 5 etapas.
- TUI: categoria `smart_excludes` (fontes + editor) e categoria `check`; `DEFAULT_CONFIG`
  derivado do schema (`seeded`), 100 `ConfigField` / 18 categorias.
- Métricas: Token Trend e Latency Histogram no CLI/TUI/MCP, `duration_ms` com fallback de
  `meta`, prompt com `collapse_binary_hunks()` e 100+ padrões de smart excludes.
- Estabilidade: guarda de TTY (`_may_ask_the_user()`) desbloqueou a suíte; teste headless
  estabilizado. Agora **159 arquivos / 3422 cenários** (era 153 / 3276).
- Extensão: metadados de marketplace (icon, LICENSE, homepage) e pipeline com `dry_run`,
  guarda de tag e fail-fast de credencial.

## Pendência reportada

`NewsletterContent::version_from_relatorio()` agora devolve **1.6.0** (confirmado via
`php -r` com a mesma regex), mas `public/content/newsletter/1.6.0/` não existe — o diretório
para em `1.5.0`. Com isso, `php artisan newsletter:send` **sem `--version`** falha
explicitamente até o corpo da newsletter 1.6.0 ser gerado (comportamento desenhado; usar
`/generate-newsletter-body`). Nenhum arquivo de newsletter foi criado nesta tarefa.

## Observações

- A seção "Evolução desde o Relatório Anterior" mantém, por desenho, os valores antigos
  (`v0.0.38`, 3276, 1698, 85 `ConfigField`, 17 categorias, 58 chaves) na coluna da esquerda;
  os greps de resíduo foram avaliados linha a linha e só esses são esperados.
- `public/content/docs/*` (sync-docs em andamento), `public/content/newsletter/*` e
  `menu.json` não foram tocados.
