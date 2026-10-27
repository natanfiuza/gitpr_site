# Plano de execução — Verificar paridade da documentação técnica CLI → site

- **Data:** 2026-10-27
- **Branch:** `develop_natan`
- **Task:** `verificar_paridade_docs`
- **Skill:** `/grill-with-docs` (grilling + domain-modeling)
- **Escopo:** `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs` (nível superior + `README*.md` da raiz) × `public/content/docs`
- **Survey:** `docs/survey/20261027_verificar_paridade_docs_surveyfacts.md`

## Contexto

A pergunta da sessão era se todo arquivo de documentação técnica do repositório do CLI tem página no site. A resposta medida é **sim**: 51/51 tópicos e 244/244 arquivos, sem divergência após a reescrita de links. O único achado acionável veio da auditoria dos órfãos — duas entradas de menu para o mesmo assunto de chat, uma delas apontando para uma página fina e superseída.

## Decisões confirmadas

| Q | Decisão |
| --- | --- |
| Q1 | A variante inglesa define pass/fail; traduções ausentes são lista separada de severidade menor; monolíngue não é lacuna |
| Q2 | Entrega = relatório + criar faltantes (que se revelou vazio) |
| Q3 | Bidirecional; órfãos listados, sem deleção automática |
| Q4 | Tópicos novos entrariam no `menu.json` nas 5 línguas (não houve) |
| Q5 | Comparação contra a árvore de trabalho da fonte |
| Q6 | Só nível superior + `README` da raiz; todos os subdiretórios fora, inclusive `extra/` |
| Q7 | Registrar + auditar os 4 órfãos |
| Q8 | `extra/gitpr_sintese_monetizacao.md` fica fora do site |
| Q9 | `chat-interativo` permanece no disco; a entrada do menu é repontada |
| Q10 | Sobrevive o rótulo `▸ Interactive Chat (TUI)`; `▸ Chat Functionality Guide` é removida nas 5 línguas |

## Execução

1. **`public/content/menu.json`** — em cada uma das 5 línguas:
   - na entrada de índice 18, trocar `path: "docs/chat-interativo"` por `path: "docs/understanding_chat_functionality"`, mantendo o título localizado;
   - remover a entrada `path: "docs/understanding_chat_functionality"` (índice 53), nas 5 línguas.
   - Resultado esperado: 66 entradas por idioma, nenhum `path` duplicado, JSON válido. A posição 18 é preservada de propósito (o menu é ordenado por `path`, mas o leitor vê títulos).
2. **`.claude/skills/sync-docs/SKILL.md`** — no item "Tópicos só no site", registrar que `chat-interativo` fica deliberadamente fora do menu, para o diagnóstico de `SKILL.md:67` não acusar falso positivo a cada sync.
3. **`CONTEXT.md`** — acrescentar o termo **Órfão** ao glossário.
4. **Artefatos** — survey (passo anterior), este plano e o relatório da task.

## Verificação

- `PYTHONIOENCODING=utf-8 python .claude/skills/sync-docs/rewrite_doc_links.py --check --source "C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr"` → exit 0, 244 comparados, 0 divergentes.
- Checagem do menu: 5 idiomas × 66 entradas; `docs/chat-interativo` ausente; `docs/understanding_chat_functionality` exatamente uma vez; nenhum `path` de docs sem arquivo; `chat-interativo` como único tópico fora do menu.
- `GET /docs/understanding_chat_functionality?lang=pt_br` → 200 com o título do menu; `GET /docs/chat-interativo` → 200 (arquivo preservado).
- `./vendor/bin/pint --test` para garantir que nada colateral entrou (não há PHP alterado).

## Fora deste plano

- Criar/alterar conteúdo de docs (nada falta).
- Remover `chat-interativo` ou os demais órfãos.
- Publicar `tutorial/install-from-source`, `extra/` ou `testing/`.
- Corrigir a variante inglesa em português de `como_reverter_commit_git_localmente` / `testar_sem_usar_pypi` (apenas relatado).
