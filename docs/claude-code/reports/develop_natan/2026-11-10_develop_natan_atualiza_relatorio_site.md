# Atualização do Relatório do Site — 2026-11-10 (branch `develop_natan`)

Sincronização de `public/content/relatorio*.md` com o relatório de estado mais
recente do repositório GitPR CLI, conforme skill `update-relatorio`.

## Versão

| Item | Valor |
| --- | --- |
| Versão anterior publicada | **v0.0.23 (2026-11-08)** |
| Versão nova | **v0.0.24 (2026-11-10)** |
| Fonte | `gitpr/docs/reports/relatorio_estado_v0.0.24.md` (996 linhas) |
| CLI no relatório | 1.7.0 (publicada no PyPI) |

## Arquivos alterados (5 idiomas)

| Arquivo | Idioma | Método |
| --- | --- | --- |
| `public/content/relatorio.md` | EN | Tradução integral do fonte PT-BR |
| `public/content/relatorio.pt_br.md` | PT-BR | Cópia byte a byte do fonte (md5 `da6241c7…` idêntico) |
| `public/content/relatorio.pt_pt.md` | PT-PT | Traduzido do inglês atualizado |
| `public/content/relatorio.es.md` | ES | Traduzido do inglês atualizado |
| `public/content/relatorio.fr.md` | FR | Traduzido do inglês atualizado |

## Conteúdo novo na v0.0.24

- Dashboard de métricas com **gráficos ASCII** (`src/ascii_chart.py`) e abas
  **Events** / **Charts & Trends** (ADR-0018).
- Prompt mais resistente a binários: `collapse_binary_hunks()` também no início
  de `generate_pr_content()`; smart excludes ganham `*.arrow`, `*.gguf`,
  `*.tflite`, `*.keras`.
- Release da extensão desacoplada: job `github-release` (`.vsix` em `ext-v*`) e
  `publish-marketplaces` não bloqueante, por PAT.
- Custo/moeda: `KIND_FLOAT`, `GITPR_METRICS_CURRENCY`, 8 campos de preço por
  modelo; `DEFAULT_MODEL_PRICES` cobre também `gemini-pro-latest` e
  `gemini-flash-lite-latest`.
- Notas de migração formalizadas e três ADRs novas (0017, 0018, 0019).
- **1.6.0 e 1.7.0 publicadas** no PyPI; `develop_natan` mergeada em `main`
  (PR #310). Tag `v1.7.0` no origin ainda aponta para o commit pré-bump.

## Verificação

| Checagem | Resultado |
| --- | --- |
| Linhas por arquivo | 996 em todos os 5 |
| Cabeçalhos (`#`..`####`) | 57 em todos, mesmos níveis |
| Separadores `---` | 8 em todos |
| Linhas de tabela | 200 em todos |
| Links (34) e destinos | idênticos ao inglês/fonte |
| CRLF / BOM | 0 CR, sem BOM em todos |
| Métricas-chave (v0.0.24, 1.7.0, 3436, 1787, 109, v0.0.42, PR #310) | idênticas nos 5 |
| Títulos H2 traduzidos por idioma | OK |
| Probes de vazamento de idioma | sem vazamentos (só termos de glossário/código) |
| Convenções de terminologia (proveedor/puerta/riesgo; Provider/fournisseur/barrière/risque; provedor/portão/risco) | preservadas vs `HEAD` |

### Terminologia — resolução dos termos sensíveis

- **ES:** `provider` em prosa restrito a dois rótulos de seção
  (`Registro Provider-Agnóstico`, `Registro Provider-Neutro`), idênticos ao
  arquivo anterior (`HEAD`). Conceito → `proveedor`, `riesgo`; gate → `puerta`.
- **FR:** `Provider` capitalizado mantido no sentido SCM/forge, `fournisseur`
  para IA; gate → `barrière`; risco → `risque`. Hits de `gate` são o termo de
  glossário literal **Granular marketplace gate**.
- **PT-PT:** `provedor` para IA e SCM; gate → `portão`/`barreira`; risco →
  `risco`; ortografia europeia (`ficheiros`, `equipa`, `utilizador`).

## Efeito colateral — newsletter

`app/Support/NewsletterContent.php` (`version_from_relatorio()`) lê
`- **Current version:**` do `relatorio.md` em inglês; o valor passou de
**1.6.0 → 1.7.0**. Não existe `public/content/newsletter/1.7.0/`, portanto
`NewsletterSendCommand` sem `--version` passa a falhar explicitamente
(comportamento desenhado) até o corpo da newsletter existir.

Confirmado que o regex continua casando:
`./vendor/bin/pest tests/Feature/NewsletterSendCommandTest.php` → **6/6 testes,
17 asserções, passou**.

## Observações

- Nenhum outro arquivo do site foi alterado nesta tarefa.
- O método de delta linha a linha foi mantido (arquivos com 1:1 linha em relação
  ao fonte), permitindo a paridade estrutural exata verificada acima.
