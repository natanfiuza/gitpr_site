# Corpo da Newsletter 1.5.0 (`/generate-newsletter-body`)

- **Data:** 2026-11-04
- **Branch:** `develop_natan`
- **Skill:** `.claude/skills/generate-newsletter-body`
- **Versão:** **1.5.0** (de `Current version` em `public/content/relatorio.md`)
- **Fonte de conteúdo:** `public/content/relatorio*.md` (v0.0.22) + `public/content/tip_tools.json`

## Arquivos gerados (5)

| Arquivo | Idioma | Linhas |
|---|---|---|
| `public/content/newsletter/1.5.0/newsletter_body.md` | EN | 33 |
| `public/content/newsletter/1.5.0/newsletter_body.pt_br.md` | PT-BR | 33 |
| `public/content/newsletter/1.5.0/newsletter_body.pt_pt.md` | PT-PT | 33 |
| `public/content/newsletter/1.5.0/newsletter_body.es.md` | ES | 33 |
| `public/content/newsletter/1.5.0/newsletter_body.fr.md` | FR | 33 |

O diretório `1.5.0/` não existia (a sequência parava em `1.4.4`), então não houve
sobrescrita nem confirmação a pedir.

## Estrutura

```
# GitPR 1.5.0 — <título do idioma>
## Novidades desta versão          (6 bullets, verbatim do relatório daquele idioma)
## Como usar                       (autorado à mão)
## Dicas úteis                     (1 dica do banco, verbatim)
```

Títulos por idioma mantidos como nas edições anteriores: `What's New in This Version`
/ `Novidades desta versão` (PT-BR e PT-PT) / `Novedades de esta versión` /
`Nouveautés de cette version`; `How to Use` / `Como usar` / `Cómo usar` /
`Comment l'utiliser`; `Useful Tips` / `Dicas úteis` / `Consejos útiles` / `Astuces utiles`.

## Dica consumida

| Campo | Valor |
|---|---|
| ID | **`tip_10`** |
| Fonte | `docs/pull-request-publication.md` |
| Tema | Reexecutar `gitpr` na mesma branch → "Push to existing PR" (atualiza só o corpo); retry automático de push sem upstream |

Marcada `used=true` em `public/content/tip_tools.json`. Estado do banco: **13 usadas /
27 livres** (era 12/28).

A dica foi conferida contra o doc já sincronizado nesta janela: `pull-request-publication.md`
linha 245 (`Push to existing PR → PATCH update PR body`), linha 251 (o título não muda) e
linha 427 (push sem upstream → `--set-upstream origin <branch>`). Nada no texto precisou de ajuste.

## "Como usar" (autorado)

Cobre só o que a versão mudou, sem duplicar a doc:

1. Instalação: a 1.5.0 está tagueada como `v1.5.0`, mas o PyPI ainda serve a 1.4.4 nesta data —
   o texto diz isso explicitamente e aponta `pip install --upgrade gitpr-cli` para quando a
   publicação for confirmada. Evita uma newsletter factualmente errada no momento do envio.
2. Ciclo da issue: `gitpr -is` + F3 grava o número em `.gitpr/conf/github.yml`; o próximo
   `gitpr` verifica que a issue segue aberta e oferece `close #N`; `--close-issue`,
   `--no-close-issue` e `GITPR_ISSUE_AUTOCLOSE=false`.
3. Base do PR por repositório: bloco YAML de `.gitpr/conf/gitpr.config.yml`
   (`pull_request.base_branch`), precedência do `--base` e as duas restrições (existir em
   `origin`, não ser a branch de origem do PR).

## Verificação

| Checagem | Resultado |
|---|---|
| Bullets verbatim vs relatório de cada idioma | 6/6 idênticos nos 5 idiomas |
| Dica verbatim vs `tip_tools.json` | idêntica nos 5 idiomas |
| Estrutura de seções | 1 `#` + 3 `##` por arquivo, nos 5 |
| Contagem de linhas | 33 nos 5 |
| HTML/CSS embutido (`<tag>`, `style=`) | nenhum |
| Links relativos restantes | nenhum |
| `NewsletterContent::version_from_relatorio()` | `1.5.0` |
| `NewsletterContent::body_markdown('1.5.0', <lang>)` | carrega nos 5 (3919–4428 bytes) |
| `./vendor/bin/pest tests/Feature/NewsletterSendCommandTest.php` | 6/6 verdes |
| `public/content/menu.json` | não tocado nesta tarefa (o diff de +5 é do `/sync-docs`) |

## Anomalias e observações

1. **Link relativo dentro do bullet.** O bullet 2 do relatório carrega
   `[ADR-015](docs/plans/ADR-015-project-local-pr-base.md)` nos 5 idiomas. Numa newsletter
   isso vira link quebrado, então o destino foi trocado pela URL absoluta
   `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-015-project-local-pr-base.md`
   — mesma convenção que o `rewrite_doc_links.py` usa para `plans/` e que o próprio
   `pull-request-publication.md` adota. **Só o destino mudou; o texto visível ficou intacto.**
2. **`tip_9` está defasada de novo.** A dica de auto-update afirma cache de **24h**, mas
   `public/content/docs/auto-update.md` (linha 21) diz **1 hora**; a lista de isenções
   também ficou incompleta (falta a isenção de subcomando sem terminal, linha 40).
   Rejeitada nesta edição. Fica para a próxima execução do `/update-tip-tools`.
   Observação de processo: `used=false` não significa "em dia" — a auditoria precisa ser
   feita dica a dica contra o doc, não por confiança no banco.
3. **PyPI ainda em 1.4.4.** Se a newsletter for enviada antes da publicação da 1.5.0,
   o comando de upgrade instala a versão anterior. O texto deixa essa condição explícita
   em vez de prometer a 1.5.0 no PyPI.
4. **Sem `§` a remover.** A fatia de novidades desta versão não tem ponteiros internos
   (`§`), diferente da v0.0.17.
