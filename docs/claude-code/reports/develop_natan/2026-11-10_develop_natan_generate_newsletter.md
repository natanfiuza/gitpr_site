# Geração do Corpo da Newsletter — 2026-11-10 (branch `develop_natan`)

Geração de `public/content/newsletter/1.7.0/newsletter_body.*.md` nos 5 idiomas,
conforme skill `generate-newsletter-body`.

## Versão

- **Versão da newsletter:** **1.7.0** (extraída de `Current version` do
  `relatorio.md`; não confundir com a versão do relatório, v0.0.24).
- O diretório `1.7.0` não existia — não houve sobrescrita.

## Arquivos gerados

| Arquivo | Idioma |
| --- | --- |
| `public/content/newsletter/1.7.0/newsletter_body.md` | EN |
| `public/content/newsletter/1.7.0/newsletter_body.pt_br.md` | PT-BR |
| `public/content/newsletter/1.7.0/newsletter_body.pt_pt.md` | PT-PT |
| `public/content/newsletter/1.7.0/newsletter_body.es.md` | ES |
| `public/content/newsletter/1.7.0/newsletter_body.fr.md` | FR |

## Estrutura

Todos os 5 arquivos seguem a mesma estrutura (4 cabeçalhos, 7 bullets,
2 blocos de código):

1. `# GitPR 1.7.0 — <Novidades/What's New/…>`
2. `## Novidades desta versão` — 7 bullets **verbatim** do relatório do próprio
   idioma (sem retradução), com links relativos `docs/…` reescritos para
   `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/…`.
3. `## Como usar` — atualização via PyPI, abas do dashboard de métricas
   (`1`/`2`, `F5`/`Esc`), `GITPR_METRICS_CURRENCY` + preços `INPUT`/`OUTPUT`
   por modelo, e instalação do `.vsix` via GitHub Releases.
4. `## Dicas úteis` — dica `tip_16` no idioma correspondente.

## Dica consumida

- **`tip_16`** — `gitpr metrics dashboard` (TUI com custo, qualidade, módulos,
  providers e ciclo de PRs; `F5`/`Esc`), `gitpr metrics export` (só linhas nunca
  exportadas, CSV+JSON), `bundle` e `merge`.
- **Auditoria antes do consumo:** todas as afirmações foram conferidas em
  `public/content/docs/metricas-telemetria.md` (§Interactive Dashboard, L246–263;
  export L173–181; bundle/merge L193–195). Tip on-theme com as novidades 1.7.0
  (abas e gráficos no dashboard).
- Marcada `"used": true` em `public/content/tip_tools.json`. Banco passou de
  **33 → 32 dicas livres** (47 no total).

## Verificação

| Checagem | Resultado |
| --- | --- |
| 5 arquivos com mesma estrutura | OK (4 cabeçalhos, 7 bullets, 2 fences) |
| Bullets verbatim do relatório de cada idioma | OK nos 5 |
| Links relativos remanescentes | 0 (5 URLs absolutas por arquivo) |
| CRLF | 0 |
| `menu.json` alterado | não (newsletter fora do menu por design) |
| `tip_16` marcada `used=true` | OK; JSON válido |
| `version_from_relatorio()` → 1.7.0 e `body_markdown()` carrega os 5 idiomas | OK |

Sem HTML embutido e sem classes CSS; conteúdo apto a e-mail.

## Observações

- Ordem respeitada: o relatório foi atualizado (`/update-relatorio`) antes desta
  geração, então `newsletter:send` sem `--version` deixa de falhar por ausência
  de corpo da 1.7.0.
- `public/content/tip_tools.json` já estava modificado no working tree por tarefa
  anterior (`update_tip_tools`); esta edição só adiciona a marcação de `tip_16`.
