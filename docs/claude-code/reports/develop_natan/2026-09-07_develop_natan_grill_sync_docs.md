# Relatório — Grill da skill `/sync-docs` (grill-with-docs)

- **Data**: 2026-09-07
- **Branch**: `develop_natan`
- **Task**: `grill_sync_docs`
- **Skills**: `/mattpocock-skills:grill-with-docs` (grilling + domain-modeling) → análise da skill de projeto `/sync-docs`

## Resumo

Grill completo da skill `/sync-docs`: levantamento de fatos (agente de exploração), rodadas de decisão com o usuário (critérios, escopo, formatos), verificação viva fonte × destino, e produção de 4 artefatos. Resultado principal: a skill **não atende hoje seus próprios critérios nem consegue operar**, por um bloqueante (caminho da fonte desatualizado) + defasagens de listas; o mecanismo de detecção de documentos novos **existe e funciona**, mas está inoperante pelo mesmo caminho errado.

## O que foi feito

1. **Exploração de fatos**: localizada a única cópia da skill em `.claude/skills/sync-docs/SKILL.md` (versionada); documentados propósito, passos, mapeamento de sufixos `.es_es→.es`/`.fr_fr→.fr`, critérios de verificação verbatim, lista de monolíngues, regras finais; mapeado o cenário de docs do site e os artefatos-spec (relatórios 08-15 e 09-03, `docs/prompts_sync.md`, planos).
2. **Rodadas de decisão (grilling)**: Q1 critérios = núcleo interno + convenções do CLAUDE.md (recomendado aceito); Q2 "novos documentos" = detectar/reportar (aceito); Q3 entregável = diagnóstico + plano de correção sem editar a skill (aceito); formato de data `YYYYMMDD`; regra de auto-save em cópia da skill em `~/.claude/skills/`.
3. **Verificação viva** (fonte real `pessoal\gitpr_projeto\gitpr\docs` × `public/content/docs`, hash MD5 + presença, mapeamento de sufixos; re-checagem EOL-insensível):
   - Só-na-fonte: `scm-multiforge`, `suggested-reviewers` (2 tópicos novos, 10 arquivos faltando no site);
   - Só-no-site: `caveman-commit`, `chat-interativo`, `readme` (esperados/por design);
   - Legados `.es_es`/`.fr_fr` no site: **0**;
   - Divergentes de conteúdo EOL-insensível: **1** (`ARCHITECTURE.md`); 25 outros arquivos diferem só por EOL.
4. **Artefatos criados**:
   - `docs/survey/20260907_grill_sync_docs_surveyfacts.md` — levantamento completo (novo diretório `docs/survey/`);
   - `C:\Users\nataniel\.claude\skills\grill-with-docs\SKILL.md` — cópia de usuário da skill oficial + regra de salvamento automático do survey (`{YYYYMMDD}_{task_name}_surveyfacts.md`), conforme decidido (imune a updates do plugin);
   - `docs/plans/20260907_grill_sync_docs.md` — plano aprovado arquivado;
   - este relatório.

## Diagnóstico da skill `/sync-docs`

### Critérios internos (checklist de verificação declarado na skill)

| Critério (verbatim) | Situação hoje | Evidência |
| --- | --- | --- |
| "Cada tópico do site tem as 5 variantes canônicas (`.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md`)" | **Atende com exceções previstas** — 32 tópicos com 5/5; 5 monolíngues tratados como lacuna documentada; `testar_sem_usar_pypi` tem 5/5 no site (traduções próprias, fonte monolíngue — não tocar) | listagem `public/content/docs` |
| "`diff -rq` entre fonte e site (aplicando o mapeamento de sufixos) não acusa diferenças nos tópicos comuns" | **NÃO atende** — 1 divergente de conteúdo (`ARCHITECTURE.md`) + **10 arquivos faltando** (`scm-multiforge` e `suggested-reviewers` ×5) | hash MD5 EOL-insensível |
| "Nenhum sufixo `.es_es`/`.fr_fr` foi criado no site" | **Atende** — 0 legados no destino | listagem `public/content/docs` |
| Passo 4 — relatório pós-task (regra CLAUDE.md Reports) | **Atende** — caminho/formato corretos na skill | conteúdo SKILL.md |

> Nota: rodada literalmente, a skill nem chega à verificação — o caminho da fonte declarado (`python\gitpr`) não existe, logo o diagnóstico dela falha no primeiro passo.

### Requisitos do projeto (CLAUDE.md) cobertos pela skill

- Regra **Reports** (relatório pós-task por branch): prevista no passo 4 — ✔
- Tratamento de tópicos só-no-site e proibição de apagar arquivos do site: explicitados — ✔
- Fonte como autoridade de conteúdo e preservação do markdown: explicitados — ✔

### Identifica novos documentos? (Q2 — detectar/reportar)

**Sim, por mecanismo**: a skill compara presença por nome (tópico = nome sem sufixo) entre fonte e destino e aponta "só-na-fonte (faltando no site)" por tópico/variante, além de prever entrada no `menu.json` (5 idiomas) para tópico novo. Prova viva: com o caminho correto, ela detectaria imediatamente `scm-multiforge` e `suggested-reviewers` (criados na fonte após o último run de 09-03) como documentos novos. **Mas** o caminho desatualizado torna a detecção inoperante hoje. A skill não usa heurística de "novidade" (datas/versionamento) — apenas presença — o que é suficiente para o uso (sync determinístico), conforme decidido na rodada Q2.

### Defasagens internas (desatualizações da skill em relação à realidade)

1. **[Bloqueante]** Caminho da fonte: `C:\Users\nataniel\projetos\python\gitpr\docs` → real: `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs` (README/`readme` idem, raiz do repo gitpr).
2. Lista de monolíngues defasada: ainda cita `ARCHITECTURE` (hoje 5 idiomas na fonte) e `caveman-commit` (não existe mais na fonte; é só-no-site); não cita `otimizacao-de-tokens` e `version-markers` (monolíngues reais na fonte).
3. Lista de subdirs ignorados incompleta: não cita `testing/` (existe na fonte); topo também tem `logo.png`, `logo.psd`, `progit.pdf` (ignorados hoje só por serem não-`.md`, mas não listados).
4. (Opcional) Wording do critério "5 variantes canônicas" vs. tratamento de monolíngues como lacuna — passível de gerar falso negativo; e o passo de diagnóstico pode explicitar o fluxo "tópico novo → copiar variantes + entrada no `menu.json`".

## Correções propostas — AGUARDANDO APROVAÇÃO (não aplicadas)

Nenhum arquivo da skill `/sync-docs` foi alterado. Correções propostas:

- **(a)** Atualizar caminho da fonte no SKILL.md (incl. caso `readme`) para `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs` e raiz do repo gitpr.
- **(b)** Atualizar lista de monolíngues: remover `ARCHITECTURE` e `caveman-commit`; adicionar `otimizacao-de-tokens`, `version-markers` (manter `como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `testar_sem_usar_pypi`).
- **(c)** Completar lista de subdirs/não-docs ignorados: adicionar `testing/`; listar `logo.png`, `logo.psd`, `progit.pdf`.
- **(d)** (Opcional) Explicitar no diagnóstico o fluxo de tópico novo e ajustar wording do critério de 5 variantes para conviver com monolíngues.
- **(e)** Após (a)–(c), rodar `/sync-docs` para sincronizar `scm-multiforge` e `suggested-reviewers` (10 arquivos) + `ARCHITECTURE.md` divergente + entradas no `menu.json` (5 idiomas, se aplicável a tópicos novos).

## Verificação realizada

- Survey salvo e legível em `docs/survey/`; conteúdo confere com o relatório de fatos.
- Skill de usuário criada com frontmatter + regra; plugin oficial intacto.
- Diagnóstico sustentado por dados vivos (listagens + hash MD5 EOL-insensível com mapeamento de sufixos).
- Relatório pós-task no caminho/regra do CLAUDE.md.
