# Survey de fatos — verificar paridade da documentação técnica CLI → site

- **Data:** 2026-10-27
- **Repo:** `gitpr_site` (`c:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr_site`)
- **Branch:** `develop_natan`
- **Task:** `verificar_paridade_docs`
- **Skill acionada:** `/grill-with-docs` → grilling + domain-modeling
- **Objeto:** paridade entre `gitpr/docs` e `public/content/docs`
- **Plano da sessão:** `docs/plans/develop_natan/20261027_verificar_paridade_docs_plansfacts.md`
- **Arquivo:** gerado por regra de auto-save do `grill-with-docs`

---

## 1. Contexto e decisões do grill

**Pergunta original (verbatim):**

> Verifique em C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs se todos os arquivos de documentação tecnica existem em public\content\docs

**Rodada 1 — escopo, entrega e direção**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Q1 | Critério de presença | A variante inglesa define pass/fail; traduções ausentes são lista separada de severidade menor; monolíngue na fonte não é lacuna |
| Q2 | Entrega | Relatório + criar os faltantes seguindo as convenções do `sync-docs` |
| Q3 | Direção | Bidirecional; órfãos listados como aviso, sem deleção automática |

**Rodada 2 — navegação e estado da fonte**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Q4 | Menu | Tópicos criados entrariam no `menu.json` nas 5 línguas, título vindo do H1 de cada variante |
| Q5 | Estado da fonte | Árvore de trabalho do repositório do CLI (inclui edições não commitadas) |

**Rodada 3 — subdiretórios**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Q6 | Subdiretórios | Só nível superior (`docs/*.md`) + `README*.md` da raiz; **todos** os subdiretórios ficam fora, inclusive `extra/` — listados no relatório, sem cópia |

**Rodada 4 — entrega com lacuna zero**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Q7 | Entrega | Registrar a verificação **e auditar os 4 órfãos** (propor manter/remover; remoção só com confirmação explícita) |
| Q8 | `extra/gitpr_sintese_monetizacao.md` | Manter fora do site, citado como fora de escopo |

**Rodada 5 — órfão de chat**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Q9 | `chat-interativo` | Manter o arquivo; repontar a entrada do menu para o doc oficial |
| Q10 | Rótulo do menu | Sobrevive `▸ Interactive Chat (TUI)`; a entrada `▸ Chat Functionality Guide` é removida nas 5 línguas |

---

## 2. Relatório de fatos

### 2.1 Fonte — repositório do CLI

- Branch `develop_natan`; árvore de trabalho comparada.
- `docs/` de nível superior: **239 arquivos `.md`** = 50 tópicos (47 com 5 variantes + `github-issue-prompt-com-gh`, `version-markers` só en + `review-pr` en/pt_br).
- Raiz do repo: `README.md` + 4 variantes → 5 arquivos, caso especial `readme`.
- Total considerado: **244 arquivos / 51 tópicos**.
- Não-`.md` no topo: `gitpr_landing_page.pdf`, `logo.png`, `logo.psd` (ignorados por extensão).
- Subdiretórios (fora de escopo por Q6): **327 `.md`** — `claude-code/` 114, `plans/` 146, `survey/` 20, `reports/` 19, `gemini/` 18, `tutorial/` 5, `extra/` 3, `prompts/` 1, `testing/` 1.
- Destaques fora de escopo: `tutorial/install-from-source` tem **5 variantes** e não tem página no site; `extra/gitpr_sintese_monetizacao.md` e `testing/manual_pr_publisher_e2e.md` também não têm página.

### 2.2 Site

- `public/content/docs/`: **256 arquivos / 55 tópicos**, todos planos (sem subdiretórios).
- 50 tópicos com 5/5 variantes; 5 parciais: `caveman-commit` (en), `como_reverter_commit_git_localmente` (en), `github-issue-prompt-com-gh` (en), `review-pr` (en + pt_br), `version-markers` (en).
- Nenhum sufixo legado `.es_es`/`.fr_fr`.

### 2.3 Renderização e navegação

- `routes/web.php:27` — catch-all `/{page?}` → `DocsController@show_document`.
- `DocsController.php:31-38` — `lang` default `en`; `content/{page}.{lang}.md`, fallback para `.md`; `lang` não é validado.
- `DocsController.php:45-51` — navegação vem de `public/content/menu.json` (5 arrays, 67 entradas cada antes desta task).
- `DocsController::search_content` também itera o menu — página fora do menu sai da busca.
- `DocsLayout.vue` consome `menu_items`; `page_title` vem do menu, nunca do H1.

### 2.4 Skill `sync-docs`

- `.claude/skills/sync-docs/SKILL.md` + `rewrite_doc_links.py`.
- Fonte: `docs/*.md` de nível superior + `README*.md`; renomeação `.es_es` → `.es`, `.fr_fr` → `.fr`.
- `--check` é dry-run (só escreve quando `--check` está ausente); `--source` compara fonte × site **depois** da reescrita de links.
- `SKILL.md:67` reporta "tópicos do site sem entrada no menu"; `SKILL.md:75` já listava `chat-interativo` como só-no-site.

---

## 3. Verificação viva (2026-10-27, dados reais)

| Checagem | Resultado |
| --- | --- |
| Tópicos da fonte ausentes no site | **0** |
| Variantes da fonte ausentes no site | **0** |
| Conteúdo divergente após reescrita (`--check --source`, exit 0) | **0** de 244 |
| Tópicos sem entrada no menu | **0** (55/55 nas 5 línguas) |
| Entradas de menu sem arquivo | **0** |
| Legados `.es_es`/`.fr_fr` no site | **0** |

Saída do `rewrite_doc_links.py --check --source <gitpr>`: `arquivos varridos: 256`, `desatualizados: 0`, `nada a reescrever`, `--source: 244 arquivo(s) comparado(s)`, exit 0.

A comparação por hash bruto acusa 145 arquivos diferentes — **falso positivo esperado**: o site tem os links cruzados já reescritos para `/docs/...`, a fonte ainda tem links relativos `.md`. O `--check --source` compara depois da transformação, que é o critério correto.

### 3.1 Órfãos

| Órfão | Origem | Situação | Ação (Q9/Q10) |
| --- | --- | --- | --- |
| `chat-interativo` (5 variantes) | nenhuma; criado no site em 2026-07-24 | 23 linhas, superseído por `understanding_chat_functionality` (96 linhas, 5 idiomas); só o menu apontava para ele | entrada do menu repontada; arquivo mantido |
| `caveman-commit` (só en) | `.claude/skills/caveman-commit` do CLI (é skill, não doc) | frontmatter YAML, sem H1; menu traduzido nas 5 línguas | manter |
| `como_reverter_commit_git_localmente` (só en) | `docs/extra/` | a variante "inglesa" está em **português** | manter |
| `testar_sem_usar_pypi` (5 variantes) | `docs/extra/` (só en, em português) | site tem 5 variantes traduzidas a partir de um original PT | manter |

### 3.2 Menu antes/depois

| idioma | título que sobrevive (índice 18) | entrada removida |
| --- | --- | --- |
| en | `▸ Interactive Chat (TUI)` | `▸ Chat Functionality Guide` |
| pt_br | `▸ Chat Interativo (TUI)` | `▸ Guia do Chat` |
| pt_pt | `▸ Chat Interativo (TUI)` | `▸ Guia do Chat` |
| es | `▸ Chat Interactivo (TUI)` | `▸ Guía del Chat` |
| fr | `▸ Chat Interactif (TUI)` | `▸ Guide du Chat` |

Resultado: 67 → **66 entradas por idioma**; 54 páginas na navegação; nenhum `path` duplicado.

---

## 4. Achados e pendências

1. **Paridade completa** no escopo acordado. Nenhum arquivo de conteúdo criado ou alterado.
2. `review-pr` só tem `en` e `pt_br` na fonte — e o site espelha isso. Não é lacuna (não se inventa tradução).
3. `github-issue-prompt-com-gh` e `version-markers` são monolíngues na fonte.
4. A variante inglesa de `como_reverter_commit_git_localmente` e `testar_sem_usar_pypi` está em português — inconsistência de conteúdo, apenas relatada.
5. `chat-interativo` agora fica fora do menu e da busca por decisão; a exceção foi registrada em `SKILL.md` para não virar falso positivo recorrente.
6. Fora de escopo, mas candidatos a task futura: `tutorial/install-from-source` (5 variantes, sem página) e `extra/gitpr_sintese_monetizacao.md`.
