# Geração do corpo da newsletter — 2026-10-29

- **Branch:** `develop_natan`
- **Skill:** `generate-newsletter-body`
- **Versão:** GitPR CLI **1.4.4** (extraída do campo `- **Current version:**` de `public/content/relatorio.md`; a versão do relatório é v0.0.21)
- **Entrada:** as 5 variantes de `public/content/relatorio.*.md` + `public/content/tip_tools.json`

## Arquivos gerados

Diretório novo — `public/content/newsletter/1.4.4/` não existia, nada foi sobrescrito.

| Arquivo | Idioma | Linhas |
|---|---|---|
| `newsletter_body.md` | EN | 27 |
| `newsletter_body.pt_br.md` | PT-BR | 27 |
| `newsletter_body.pt_pt.md` | PT-PT | 27 |
| `newsletter_body.es.md` | ES | 27 |
| `newsletter_body.fr.md` | FR | 27 |

Todos com a mesma estrutura: H1 da versão, três seções (`What's New in This Version` / `How to Use` / `Useful Tips` e os equivalentes `Novidades desta versão` + `Como usar` + `Dicas úteis`, `Novedades de esta versión` + `Cómo usar` + `Consejos útiles`, `Nouveautés de cette version` + `Comment l'utiliser` + `Astuces utiles`) e 6 bullets de novidades.

## Conteúdo

Novidades desta versão (fonte: as 5 bullets do "What's New in This Version (v0.0.21)" do relatório):

1. `gitpr -c` como TUI de 4 etapas (`CommitApp`, Seletor de Stage, geração em background, revisão/edição, execução animada).
2. Mensagem gerada sobre `git diff --cached` via `get_git_staged_diff()`, com fallback para a working tree só com índice vazio.
3. Auto-fechamento do modal no sucesso (`mark_finished(False)` + `set_timer(0.6, self.exit)`), **Close** preservado no erro.
4. 1.4.3 e 1.4.4 publicadas na mesma janela, com resumo do fix do gate de atualização.
5. Quebra declarada: fora de `--hook`, `gitpr -c` sempre abre a TUI.
6. Dívida de i18n 8 → 11 chaves, `__lang_version__` em v0.0.36 sem mudança de dicionário.

"Como usar" cobre atualização via `pip`, o fluxo do `gitpr -c`, os rótulos do Seletor de Stage (mantidos em inglês em PT-BR/PT-PT/ES, traduzidos com aspas francesas em FR, seguindo a convenção já usada no relatório), o caminho `--hook` para automação e as duas variáveis (`GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_AUTO_STAGE`).

## Dica consumida

| id | Fonte | Motivo da escolha |
|---|---|---|
| `tip_31` | `docs/split-command.md` | Complementa a TUI de commit: escolher arquivos resolve "o que entra"; `gitpr split` resolve "um diff com três intenções". |

Marcada `used=true` em `public/content/tip_tools.json` (diff de 1 linha; o arquivo continua LF). Free tips: 29 → 28.

## Auditoria do banco de dicas

Conferi as 29 dicas livres contra `gitpr/docs/` antes de escolher. Uma está **desatualizada e não foi consumida**:

- **`tip_9` (`docs/auto-update.md`)** — descreve o gate como "cached for 24h", diz que "there is no compatibility mode" e que `--quiet`/`--hook`/`--mcp`/`-h` são isentos. Desde 1.4.3 o cache é uma **janela móvel de 1 hora**, `gitpr -u` ignora o cache e subcomandos passam pelo gate quando há TTY. As outras 28 continuam coerentes com a documentação atual. Recomendo `update-tip-tools` para reescrever a `tip_9` (ou aposentá-la) antes da próxima edição.

`tip_31` foi validada contra `gitpr/docs/split-command.md`: `--dry-run` imprime e para, `--apply` confirma uma vez e commita grupo a grupo, `--max-groups` limita o plano e arquivos não rastreados aparecem num aviso.

## Verificação

- Os 5 arquivos têm 27 linhas, 3 seções `##` e 6 bullets; cabeçalho correto por idioma; sem HTML embutido nem links externos.
- `public/content/menu.json` **não foi alterado** (`git status` limpo para o arquivo) — a newsletter fica fora do menu por design.
- `NewsletterContent::body_markdown()` resolve `public/content/newsletter/{version}/newsletter_body.{lang}.md` com fallback para inglês — caminho e nomes conferem.
- `tip_tools.json` continua JSON válido (40 dicas, 12 usadas).
