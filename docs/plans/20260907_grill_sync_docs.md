# Plano — Survey de fatos + regra automática no grill-with-docs + análise do /sync-docs

> Copiado do plano aprovado em `C:\Users\nataniel\.claude\plans\giggly-soaring-sparkle.md` (2026-09-07). Status: **aprovado e executado** (survey salvo, skill de usuário criada, diagnóstico produzido). Correções do `/sync-docs` seguem **aguardando aprovação**.

## Context

O usuário acionou `/mattpocock-skills:grill-with-docs` para analisar a skill `/sync-docs`: verificar se ela atende os próprios critérios e os requisitos do projeto, e se identifica novos documentos. Durante o grill, um agente de exploração levantou fatos completos sobre a skill (localização, conteúdo, critérios, estado da documentação do repo). Decisões da Rodada 1 (recomendadas, aceitas): critérios = núcleo interno da skill + convenções do CLAUDE.md; "identifica novos documentos" = detectar/reportar (não escrever sem aprovação); entregável = diagnóstico + plano de correção, edição na skill `/sync-docs` **somente após nova aprovação**.

O usuário pediu adicionalmente:
1. Salvar o levantamento completo (relatório de fatos) em `docs/survey/`.
2. Adicionar regra ao `/mattpocock-skills:grill-with-docs`: toda vez que acionada, salvar automaticamente o survey com formato `{YYYYMMDD}_{task_name}_surveyfacts.md`.

Decisões fechadas com o usuário: formato de data = **YYYYMMDD** (ex.: `20260907_...`); edição = **cópia da skill em `C:\Users\nataniel\.claude\skills\grill-with-docs\`** (imune a updates do plugin, duplica a oficial).

Fatos-chave do levantamento: a skill `/sync-docs` vive em `.claude/skills/sync-docs/SKILL.md` (versionado); declara critérios de verificação explícitos (5 variantes por tópico, `diff` zero nos tópicos comuns, nenhum `.es_es`/`.fr_fr` criado); **o caminho da fonte que ela declara (`C:\...\python\gitpr\docs`) não existe mais** — o repo GitPR CLI agora é `pessoal\gitpr_projeto\gitpr` (bloqueante); lista de monolíngues desatualizada; lista de subdirs ignorados incompleta (`testing/` ausente).

## Mudanças

### 1. Survey de fatos — `docs/survey/20260907_grill_sync_docs_surveyfacts.md`

Criar `docs/survey/` no repo (não existe) e escrever o arquivo com o levantamento completo, em PT-BR:
- Cabeçalho: data (2026-09-07), repo, branch `develop_natan`, task `grill_sync_docs`, skill analisada `/sync-docs`.
- Decisões das rodadas do grill (Q1–Q3 com as respostas recomendadas aceitas; formato de data e local da regra decididos).
- Relatório de fatos completo (do agente de exploração): localização da skill; conteúdo do SKILL.md (propósito, fontes/destinos, mapeamento de sufixos `.es_es→.es`/`.fr_fr→.fr`, passos diagnóstico→sync→verificação→relatório, critérios verbatim, monolíngues, regras finais); cenário de documentação do repo (`docs/`, `docs/claude-code/reports/develop_natan/`, `public/content/docs/` ~37 tópicos, `menu.json`); artefatos que funcionam como spec/rastro (relatórios de 08-15 e 09-03, `docs/prompts_sync.md`, planos).
- Achados preliminares e pendências (caminho da fonte desatualizado, lista de monolíngues defasada, `testing/` ausente da lista de ignorados, ausência de CONTEXT.md).

### 2. Skill de usuário — `C:\Users\nataniel\.claude\skills\grill-with-docs\SKILL.md`

Criar a pasta/arquivo copiando o comportamento da skill oficial (uma linha: *"Call the Skill tool twice, for 'grilling' and 'domain-modeling'"* — mantida verbatim no corpo) e adicionando a regra de salvamento automático:

```markdown
---
name: grill-with-docs
description: A relentless interview to sharpen a plan or design, which also creates docs (ADR's and glossary) as we go. Salva automaticamente o survey de fatos em docs/survey.
disable-model-invocation: true
---

Call the Skill tool twice, for "grilling" and "domain-modeling".

## Salvamento automático do survey

Ao final da sessão de grill (fronteira esgotada e entendimento compartilhado confirmado), salve automaticamente o levantamento completo — contexto da tarefa, decisões das rodadas e relatório de fatos levantados — em:

`docs/survey/{YYYYMMDD}_{task_name}_surveyfacts.md`

- `{YYYYMMDD}`: data local sem separadores (ex.: 20260907).
- `{task_name}`: identificador curto da task em lowercase/underscores.
- Crie `docs/survey/` na raiz do repo onde a skill foi acionada, se não existir.
- Sobrescreva se já houver arquivo do mesmo dia/task (task rodou novamente).
```

Fonte do comportamento original (para referência): `C:\Users\nataniel\.claude\plugins\cache\claude-plugins-official\mattpocock-skills\1.2.3\skills\engineering\grill-with-docs\SKILL.md`. A cópia não edita o plugin.

### 3. Análise do `/sync-docs` (diagnóstico — sem editar a skill)

Apresentar o diagnóstico completo no chat e no relatório (item 4), avaliando contra os critérios definidos (Q1):
- **Critérios internos (checklist de verificação da skill)**: 5 variantes por tópico; `diff -rq` zero com mapeamento de sufixos; zero `.es_es`/`.fr_fr` no site — avaliados com dados vivos (listar `gitpr_projeto\gitpr\docs` no novo caminho e comparar com `public/content/docs/`).
- **Convenções do projeto (CLAUDE.md)**: passo 4 (relatório pós-task em `docs/claude-code/reports/{branch}/`) — cumprido na skill.
- **Identifica novos documentos?** (Q2): sim, por mecanismo — comparação de presença por nome (tópico = arquivo sem sufixo) entre fonte e destino + entrada no `menu.json` em 5 idiomas; **mas** o caminho da fonte aponta para diretório inexistente → hoje a skill, rodada literalmente, não detecta nada.
- Correções propostas para `/sync-docs` (listadas, **não aplicadas neste plano** — aguardam aprovação em passo seguinte): (a) atualizar caminho da fonte para `pessoal\gitpr_projeto\gitpr\docs` (+ README na raiz do repo gitpr); (b) atualizar lista de monolíngues (remover `ARCHITECTURE`, adicionar `otimizacao-de-tokens`, `version-markers`); (c) completar lista de subdirs/arquivos não-doc ignorados (`testing/`, `logo.*`, `progit.pdf`); (d) opcional: explicitar no passo de diagnóstico o tratamento de tópico novo (copiar variantes + `menu.json`).

### 4. Relatório pós-tarefa (regra Reports do CLAUDE.md)

Escrever `docs/claude-code/reports/develop_natan/2026-09-07_develop_natan_grill_sync_docs.md` com: o que foi feito (survey salvo, skill de usuário criada), o diagnóstico do `/sync-docs` e as correções propostas pendentes de aprovação.

## Arquivos tocados

| Arquivo | Ação |
| --- | --- |
| `docs/survey/20260907_grill_sync_docs_surveyfacts.md` | Criar (novo, item 1) |
| `C:\Users\nataniel\.claude\skills\grill-with-docs\SKILL.md` | Criar (novo, item 2) |
| `docs/claude-code/reports/develop_natan/2026-09-07_develop_natan_grill_sync_docs.md` | Criar (novo, item 4) |
| `.claude/skills/sync-docs/SKILL.md` | **Não alterar** (correções aguardam aprovação) |

## Verificação

1. `docs/survey/20260907_grill_sync_docs_surveyfacts.md` existe, contém o relatório de fatos completo e abre sem erros.
2. Skill de usuário invocável: `/grill-with-docs` (sem prefixo) lista a cópia; o frontmatter/regra estão no arquivo correto.
3. Diagnóstico baseado em dados reais: listagem de `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs` confere com a fonte declarada; comparação com `public/content/docs/` sustenta cada critério avaliado como atendido/não atendido.
4. Relatório pós-task salvo no caminho/regra do CLAUDE.md.
