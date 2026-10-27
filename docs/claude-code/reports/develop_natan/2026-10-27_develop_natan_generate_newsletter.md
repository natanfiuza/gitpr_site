# Geração do corpo da newsletter 1.4.2 — 2026-10-27

- **Branch:** `develop_natan`
- **Skill:** `generate-newsletter-body`
- **Fonte:** `public/content/relatorio.md` + 4 traduções (v0.0.19)
- **Destino:** `public/content/newsletter/1.4.2/` (novo)

## Versão

| | Valor |
|---|---|
| Relatório (H1) | v0.0.19 (2026-10-27) |
| **Versão do CLI** (campo `Current version`) | **1.4.2** |
| Versão da newsletter | **1.4.2** |

A versão veio do campo `- **Current version:**` do `relatorio.md` via `\d+\.\d+\.\d+`, não do H1 do relatório. É o mesmo valor que `NewsletterContent::version_from_relatorio()` lê em produção (`/Current version:\*{0,2}\s*(\d+\.\d+\.\d+)/`), conferido com `php -r` → `1.4.2`.

Antes desta tarefa o diretório `1.4.2/` não existia (o último era `1.4.1/`), então `php artisan newsletter:send` sem argumento resolvia para 1.4.2 e falhava rápido por corpo inexistente. Como `{version}` não existia, não houve sobrescrita e não foi necessário perguntar ao usuário.

## Arquivos gerados

| Arquivo | Título (H1) | Linhas | Bytes |
|---|---|---|---|
| `newsletter_body.md` | `# GitPR 1.4.2 — What's New` | 33 | 3.829 |
| `newsletter_body.pt_br.md` | `# GitPR 1.4.2 — Novidades` | 33 | 3.958 |
| `newsletter_body.pt_pt.md` | `# GitPR 1.4.2 — Novidades` | 33 | 4.064 |
| `newsletter_body.es.md` | `# GitPR 1.4.2 — Novedades` | 33 | 4.049 |
| `newsletter_body.fr.md` | `# GitPR 1.4.2 — Nouveautés` | 33 | 4.271 |

Os nomes seguem o contrato de `NewsletterContent::body_markdown()`: inglês sem sufixo, os demais com `.lang`. Os 5 códigos coincidem exatamente com `NewsletterTranslations::LANGS` (`en`, `pt_br`, `pt_pt`, `fr`, `es`), então o fallback para inglês não é acionado por nenhum idioma.

## Estrutura

Idêntica nos 5 arquivos (3 seções `##` + 5 bullets cada):

1. `## What's New in This Version` / `## Novidades desta versão` / `## Novedades de esta versión` / `## Nouveautés de cette version` — os 5 bullets da seção de novidades do relatório **daquele idioma**, fatiados verbatim (sem o ponteiro `§` e sem reformatação).
2. `## How to Use` / `## Como usar` / `## Cómo usar` / `## Comment l'utiliser` — atualização via PyPI, os comandos de `gitpr explain` e `gitpr tests generate`, o link para a documentação em 5 idiomas e a nota sobre codificação legada / exit code 1.
3. `## Useful Tips` / `## Dicas úteis` / `## Consejos útiles` / `## Astuces utiles` — 1 dica do banco, no idioma correspondente.

Sem HTML embutido, sem CSS e sem links quebrados: o único URL é `https://gitpr.natanfiuza.dev.br/docs`, já publicado. Os blocos de código usam cerca de três crases, compatíveis com e-mail.

## Dicas consumidas

| id | Tema | Idiomas | `used` |
|---|---|---|---|
| `tip_3` | Smart Excludes (pathspec nativo do Git, ~15.000 → ~250 tokens) | en, pt_br, pt_pt, es, fr | `false` → **`true`** |

O texto da dica entrou byte a byte igual ao `content[lang]` do JSON nos 5 arquivos (conferido por comparação da última linha não vazia de cada arquivo). O banco agora tem **10 de 26** dicas consumidas.

`git diff --stat public/content/tip_tools.json`: 1 inserção, 1 deleção — só a linha do `tip_3`.

## Verificação

| Checagem | Resultado |
|---|---|
| Arquivos gerados | 5/5 |
| Linhas por arquivo | 33 nos 5 |
| Seções `##` por arquivo | 3 nos 5 |
| Bullets por arquivo | 5 nos 5 |
| Bullets idênticos ao relatório do próprio idioma | 5/5 idiomas |
| Ponteiro `§` remanescente nos bullets | 0 |
| Título/H1 no idioma certo | 5/5 |
| Dica = `tip_3` no idioma certo | 5/5 |
| `public/content/menu.json` alterado | **não** (ausente do `git status`) |
| Idioma do inscrito resolvido pelo arquivo | 5/5 (`en`, `pt_br`, `pt_pt`, `fr`, `es`) |
| Fim de linha / newline final | LF, com newline final nos 5 |

## Observações

1. **O banco de dicas está defasado em relação às features atuais.** `tip_3` é válida, mas 4 dicas conhecidas descrevem comportamento que não existe mais e **não devem ser usadas** em newsletters futuras: `tip_16` (`--metrics --dashboard/--export`, removidos na 1.4.0), `tip_9` (hot-swap de `.exe.old`, canal binário removido), `tip_12` ("12 AI tools", hoje 18) e `tip_17` (templates na raiz do projeto em vez de `.gitpr/skill/`). Recomendo rodar `/update-tip-tools` antes da próxima edição — restam 16 dicas, das quais 4 são inutilizáveis na prática.
2. **A seção "How to Use" foi escrita à mão, não fatiada.** O relatório não tem uma seção equivalente; o conteúdo foi montado a partir dos fatos da seção de novidades (comandos documentados, correção do `-h --explain`, encoding/exit code 1), sem introduzir nada que o relatório não afirme.
3. **Sem link de inscrição/cancelamento no corpo.** O rodapé e o descadastro continuam por conta do template de envio (`newsletter:send`), não do markdown.
