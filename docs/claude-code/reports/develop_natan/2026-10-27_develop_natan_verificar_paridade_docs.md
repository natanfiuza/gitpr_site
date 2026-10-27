# Verificação de paridade da documentação técnica — 2026-10-27

- **Branch:** `develop_natan`
- **Skill:** `/grill-with-docs` (grilling + domain-modeling)
- **Fonte (autoridade):** `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr` — `docs/*.md` de nível superior + `README*.md` da raiz, árvore de trabalho
- **Destino:** `public/content/docs/*.md` + `public/content/menu.json`
- **Artefatos da sessão:** `docs/survey/20261027_verificar_paridade_docs_surveyfacts.md`, `docs/plans/develop_natan/20261027_verificar_paridade_docs_plansfacts.md`

## Diagnóstico

Comparação por presença (tópico = nome sem sufixo de idioma, aplicando `.es_es` → `.es` e `.fr_fr` → `.fr`) e por conteúdo com `rewrite_doc_links.py --check --source <gitpr>`, que aplica a reescrita de links aos dois lados antes de comparar.

| Situação | Resultado |
| --- | --- |
| Fonte | 244 arquivos / 51 tópicos (239 `docs/*.md` + 5 `README*`) |
| Site | 256 arquivos / 55 tópicos |
| Tópicos da fonte faltando no site | **nenhum** |
| Variantes da fonte faltando no site | **nenhuma** |
| Conteúdo divergente (após reescrita de links) | **nenhum** — 244 comparados, exit 0 |
| Tópicos sem entrada no menu | nenhum (55/55 nas 5 línguas, antes desta task) |
| Entradas de menu sem arquivo | nenhuma |
| Legados `.es_es`/`.fr_fr` no site | nenhum |

**Conclusão:** dentro do escopo acordado (nível superior + README), todo o arquivo de documentação técnica do repositório do CLI existe no site. Nenhum arquivo de conteúdo foi criado ou alterado.

> Nota de método: a comparação por hash bruto acusa 145 arquivos diferentes. É falso positivo esperado — o site tem os links cruzados reescritos para `/docs/...` e a fonte ainda tem links relativos `.md`. O critério correto é o `--check --source`, que compara depois da transformação.

## Auditoria dos órfãos

Tópicos presentes no site sem origem correspondente na fonte:

| Órfão | Origem | Situação | Ação |
| --- | --- | --- | --- |
| `chat-interativo` (5 variantes) | nenhuma; criado no site em 2026-07-24 | 23 linhas, superseído por `understanding_chat_functionality` (96 linhas, 5 idiomas) | entrada do menu repontada; arquivo mantido |
| `caveman-commit` (só en) | `.claude/skills/caveman-commit` do CLI — é skill, não doc | frontmatter YAML, sem H1 | mantido |
| `como_reverter_commit_git_localmente` (só en) | `docs/extra/` (fora de escopo) | a variante "inglesa" está em português | mantido |
| `testar_sem_usar_pypi` (5 variantes) | `docs/extra/` (só en, em português) | site tem 5 variantes traduzidas | mantido |

Nenhum arquivo foi removido.

## Ações executadas

1. **`public/content/menu.json`** — fusão das duas entradas de chat, nas 5 línguas:
   - a entrada de índice 18 trocou `path: "docs/chat-interativo"` por `path: "docs/understanding_chat_functionality"`, mantendo o título localizado (`▸ Interactive Chat (TUI)`, `▸ Chat Interativo (TUI)`, `▸ Chat Interactif (TUI)`, `▸ Chat Interactivo (TUI)`);
   - a entrada `path: "docs/understanding_chat_functionality"` (`▸ Chat Functionality Guide` / `▸ Guia do Chat` / `▸ Guide du Chat` / `▸ Guía del Chat`) foi removida.
   - Diff: `5 insertions(+), 10 deletions(-)`. Menu passou de 67 para **66 entradas por idioma**, 54 páginas na navegação, nenhum `path` duplicado.
2. **`.claude/skills/sync-docs/SKILL.md`** — no item "Tópicos só no site", registrada a exceção: `chat-interativo` fica deliberadamente fora do menu, para o diagnóstico de `SKILL.md:67` não acusar falso positivo a cada sync.
3. **`CONTEXT.md`** — acrescentado o termo **Órfão** ao glossário (tópico do site sem origem na CLI; listado, nunca removido automaticamente).
4. **Artefatos** — survey, plansfacts e este relatório.

## Verificação

| Checagem | Resultado |
| --- | --- |
| `rewrite_doc_links.py --check --source <gitpr>` | exit **0**; 256 varridos, 0 desatualizados, 244 comparados, 0 divergentes |
| Consistência do menu (5 idiomas × 66 entradas) | OK — `chat-interativo` ausente, `understanding_chat_functionality` exatamente 1×, nenhum `path` sem arquivo, nenhum duplicado |
| Tópicos fora do menu | apenas `chat-interativo` (intencional) |
| `GET /docs/understanding_chat_functionality?lang=pt_br` | **200**, `page_title` = `▸ Chat Interativo (TUI)` |
| `GET /docs/chat-interativo` | **200** (arquivo preservado) |
| `./vendor/bin/pint --test` | **falha pré-existente** em 7 arquivos PHP (`DocsController.php`, `LinterUtilityController.php`, `bootstrap/app.php`, 2 seeders, `routes/web.php`, `LinterUtilityTest.php`) — nenhum arquivo PHP foi tocado nesta task |

## Observações e pendências

1. `review-pr` só tem `en` e `pt_br` na fonte; o site espelha — não é lacuna (não se inventa tradução).
2. `github-issue-prompt-com-gh` e `version-markers` são monolíngues na fonte.
3. A variante inglesa de `como_reverter_commit_git_localmente` e `testar_sem_usar_pypi` está em português — inconsistência de conteúdo, apenas relatada.
4. `/docs/chat-interativo`, agora fora do menu, perde o título amigável: `page_title` cai no fallback do caminho (`docs/chat-interativo`). Consequência aceita da decisão de tirá-la da navegação.
5. Fora de escopo (Q6), candidatos a task futura: `tutorial/install-from-source` (**5 variantes**, sem página no site), `extra/gitpr_sintese_monetizacao.md` e `testing/manual_pr_publisher_e2e.md`.
