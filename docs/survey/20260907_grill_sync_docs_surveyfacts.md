# Survey de fatos — grill da skill `/sync-docs`

- **Data**: 2026-09-07
- **Repo**: `gitpr_site` (c:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr_site)
- **Branch**: `develop_natan`
- **Task**: `grill_sync_docs`
- **Skill acionada**: `/mattpocock-skills:grill-with-docs` → grilling + domain-modeling
- **Objeto da análise**: skill `/sync-docs` (projeto)
- **Arquivo**: gerado por regra de auto-save do `grill-with-docs` (formato `{YYYYMMDD}_{task_name}_surveyfacts.md`)

---

## 1. Contexto e decisões do grill

Pergunta original: analisar a skill `/sync-docs` e verificar se todos os critérios estão atendidos, se atende os requisitos necessários e se identifica novos documentos.

**Rodada 1 (decisões, resposta recomendada aceita em todas):**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Q1 | Critérios de avaliação | Núcleo = o que a skill declara; requisitos externos = convenções do projeto (CLAUDE.md); boas práticas gerais apenas como observação lateral |
| Q2 | "Identifica novos documentos" | Detectar e reportar (comparação de presença fonte × destino) — não escrever nada sem aprovação |
| Q3 | Entregável | Diagnóstico + plano de correção; edição da skill somente após aprovação |

**Rodada 2 (decisões de formato, via AskUserQuestion):**

| Q | Decisão | Resposta |
| --- | --- | --- |
| Data no nome do survey | Ordem da data | `YYYYMMDD` (ex.: `20260907_...`) — não `YYYYDDMM` literal nem hífens |
| Local da regra de auto-save | Onde editar o `grill-with-docs` | Cópia da skill em `C:\Users\nataniel\.claude\skills\grill-with-docs\` (imune a updates do plugin) |

**Escopo adicional pedido pelo usuário:** salvar este levantamento em `docs/survey/` e adicionar a regra de auto-save à skill; salvar o plano em `docs/plans/`.

---

## 2. Relatório de fatos (agente de exploração — completo)

### 2.1 Localização da skill `/sync-docs`

- **Caminho exato (única cópia)**: `c:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr_site\.claude\skills\sync-docs\SKILL.md`
- A pasta contém **somente o SKILL.md** — nenhum script, template ou referência auxiliar.
- **Não existe** em `C:\Users\nataniel\.claude\skills\` (lá só há `find-skills` e `reports-to-memory`) nem em plugin caches.
- A skill está **versionada no git** do repo do site (1 commit: `4789950 docs: atualiza documentação técnica`).
- Repo irmão `gitpr` tem skills próprias (`update-relatorio`, `update-tip-tools`, `generate-newsletter-body`); `.claude/` do site contém só `settings.local.json` e `skills/`.

> ⚠️ **Anomalia registrada (bloqueante)**: a skill declara como fonte `C:\Users\nataniel\projetos\python\gitpr\docs\*.md` — **caminho inexistente**. O repo GitPR CLI foi movido para `c:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\`. O relatório `2026-09-03_develop_natan_sync_docs.md` (§5, anomalia 3) já apontava isso.

### 2.2 Conteúdo do SKILL.md

**Frontmatter**: `name: sync-docs`; description PT-BR — "Sincroniza a documentação técnica do site (public/content/docs) com os arquivos de C:\Users\nataniel\projetos\python\gitpr\docs — verifica arquivos faltando, conteúdo divergente e se os idiomas (en, pt_br, pt_pt, es, fr) de cada tópico estão sincronizados".

**Fontes e destinos**: fonte = topo de `gitpr/docs/*.md` **somente nível superior**; ignorar subdirs `claude-code/, extra/, gemini/, plans/, prompts/, reports/` e não-docs (`gitpr_landing_page.pdf`). Caso especial `readme`: fonte é a **raiz do repo gitpr** (`README.md` + variantes) → site `docs/readme.*`. Destino: `public/content/docs/*.md`.
- Obs.: a lista real de subdirs da fonte inclui ainda **`testing/`** (não citado na skill), e há `logo.png`, `logo.psd`, `progit.pdf` no topo (ignorados por não serem `.md`).

**Mapeamento de sufixos (crítico, tabela no arquivo)**: inglês `.md`→`.md`; PT-BR `.pt_br.md`→`.pt_br.md`; PT-PT `.pt_pt.md`→`.pt_pt.md`; espanhol `.es_es.md`→`.es.md`; francês `.fr_fr.md`→`.fr.md`. Justificativa: "O site resolve `public/content/{page}.{lang}.md` com `lang` ∈ {en, pt_br, pt_pt, fr, es} (`DocsController.php` + `LanguageSelector.vue`). Arquivos `.es_es.md`/`.fr_fr.md` no site **não são lidos** (caem no fallback inglês)." Regra imperativa: "**Ao copiar da fonte para o site, sempre renomear `.es_es` → `.es` e `.fr_fr` → `.fr`.**"

**Passos (ordem)**:
1. **Diagnóstico** — conjuntos de tópicos de cada lado (nome sem sufixo de idioma), comparação por tópico e variante com `diff -q`: só-na-fonte (faltando no site); só-no-site; comuns divergentes; variantes de idioma faltando por tópico; tópicos do site sem entrada em `menu.json`; legados `.es_es`/`.fr_fr` no site.
2. **Sincronizar** — faltando → copiar todas as variantes existentes renomeando sufixos, sem alterar markdown; divergente → sobrescrever variante por variante ("atualizar TODAS as variantes do tópico, não só a inglesa"); variante faltando → copiar se existir na fonte; se a fonte não tiver (monolíngues), tratar como lacuna; legados `.es_es`/`.fr_fr` → reportar, **não remover sem confirmação**; só-no-site (ex.: `chat-interativo`) → não tocar; `menu.json` → adicionar entrada nas 5 línguas para cada tópico NOVO, formato `{"title": "▸ <título traduzido>", "path": "docs/<tópico>"}`, seção "Technical Documentation", ordem alfabética aproximada.
3. **Verificar** (critérios explícitos, verbatim):
   - "Cada tópico do site tem as 5 variantes canônicas (`.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md`)."
   - "`diff -rq` entre fonte e site (aplicando o mapeamento de sufixos) não acusa diferenças nos tópicos comuns."
   - "Nenhum sufixo `.es_es`/`.fr_fr` foi criado no site."
4. **Relatório da tarefa** (regra do CLAUDE.md): "escrever `docs/claude-code/reports/{branch}/YYYY-MM-DD_{branch}_sync_docs.md`".

**Como decide se algo é "novo"/faltando**: não há versionamento nem heurística — comparação de **presença por nome de arquivo** (tópico = nome sem sufixo de idioma) entre o glob da fonte e a listagem do destino, e conteúdo via `diff -q` (byte a byte por variante após mapear sufixos). "Novo no site" = nome existe na fonte mas não em `public/content/docs/`.

**Monolíngues listados na skill (verbatim)**: `ARCHITECTURE`, `caveman-commit`, `como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `testar_sem_usar_pypi` — tratar como "reportar como lacuna — não inventar traduções nem remover variantes que o site já tenha além da fonte".
- ⚠️ **Lista defasada**: `ARCHITECTURE` ganhou 5 variantes na fonte (2026-08-18); o site tem `otimizacao-de-tokens.md` e `version-markers.md` monolíngues não citados; fonte não tem mais `caveman-commit` (só-no-site hoje).

**Observações finais (verbatim)**: "A fonte é a autoridade de conteúdo; o site nunca deve divergir dela nos tópicos comuns." / "Nunca apagar arquivos do site durante a sincronização (exceto com confirmação explícita no caso dos duplicados `.es_es`/`.fr_fr`)." / "Preservar o conteúdo markdown exatamente como está na fonte (emojis, tabelas, links, blocos de código)."

**CONTEXT.md/memória**: SKILL.md não menciona CONTEXT.md nem memória. Única referência externa: "regra do CLAUDE.md" no passo 4 (relatório). Não há CONTEXT.md/CONTEXT-MAP.md no repo nem memória de projeto ainda criada.

### 2.3 Cenário de documentação do repo do site

- **`docs/`** (site): `claude-code/prompts/` (1 arquivo); `claude-code/reports/develop_natan/` (46 relatórios `YYYY-MM-DD_develop_natan_*.md`, ~12 de sync_docs); `gemini/plans/` e `gemini/reports/{develop_natan, main}/`; `plans/` (13 planos); `reports/` (1: `relatorio_estado_ GitPR-CLI.md`); raiz: `prompts_sync.md`, `tutorial_newsletter.md`. **`docs/survey/` não existia — criado por esta task.**
- **`public/content/`**: `menu.json`, `tip_tools.json`, `newsletter/` (0.0.35/0.0.36/0.0.37), subpasta `docs/` (~165 .md), ~55 arquivos de páginas 5-idioma na raiz (`biblioteca`, `cache`, `contribuicao`, `i18n`, `index`, `instalacao`, `linter`, `providers`, `relatorio`, `skills`, `uso` ×5 idiomas).
- **`public/content/docs/`** (alvo da skill): 37 tópicos — 32 com 5/5 variantes + 5 monolíngues (`caveman-commit`, `como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `version-markers`).

### 2.4 Artefatos que funcionam como spec/rastro da skill

- `docs/claude-code/reports/develop_natan/2026-08-15_develop_natan_cria_skill_sync_docs.md` — narrativa da criação (fluxo diagnóstico → sincronização → verificação → relatório; descobertas de sufixos divergentes, `readme`, subdirs ignorados, regra do menu.json).
- `docs/claude-code/reports/develop_natan/2026-09-03_develop_natan_sync_docs.md` (último run) — checklist de verificação (diff EOL-insensível zero, 32 tópicos 5/5, 0 legados, LF puro, menu 37/37) + anomalias (incl. caminho da fonte desatualizado).
- `docs/prompts_sync.md` — prompts antecessores (§2 docs, §5 README → `public\content\docs\readme.*`).
- `docs/plans/20260811_atualize_documentacao_tecnica_.md` — regras pré-skill (estrutura idêntica, não criar/reescrever conteúdo, consistência entre idiomas).
- Convenção transversal: `CLAUDE.md` → `Rules → Reports` (relatório pós-task por branch).

---

## 3. Verificação viva (2026-09-07, dados reais)

Fonte real: `c:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs` (36 tópicos, 160 arquivos `.md`: 31 tópicos × 5 variantes + 5 monolíngues). Destino: `public/content/docs` (37 tópicos, 165 arquivos: 32 × 5 + 5 monolíngues). Comparação por presença + hash MD5, com mapeamento `.es_es→.es`, `.fr_fr→.fr`:

| Checagem | Resultado |
| --- | --- |
| Só-na-fonte (faltando no site) | **`scm-multiforge`** e **`suggested-reviewers`** (2 tópicos novos × 5 variantes = 10 arquivos) |
| Só-no-site | `caveman-commit` (fonte não tem mais), `chat-interativo` (por design), `readme` (caso especial README) |
| Legados `.es_es`/`.fr_fr` no site | **0** ✔ |
| Conteúdo divergente (hash bruto) | 26 arquivos (ARCHITECTURE.md; auto-update, github-ci-linter, map-reduce-diff, skill-template, smart-excludes ×5) |
| Conteúdo divergente **EOL-insensível** | **1**: `ARCHITECTURE.md` apenas (25 dos 26 eram diferença de fim de linha) |
| Tópicos do site sem entrada no `menu.json` | A verificar (não re-executada; relatório 09-03: 37/37) — `scm-multiforge`/`suggested-reviewers` certamente sem entrada se ainda não sincronizados |

**Interpretação**: se rodada com o caminho da fonte corrigido, a skill identificaria imediatamente os 2 documentos novos (`scm-multiforge`, `suggested-reviewers`) como "faltando no site" e o `ARCHITECTURE.md` como divergente — o mecanismo de detecção existe e funciona. Com o caminho desatualizado da skill (`python\gitpr`), ela não detecta nada.

---

## 4. Achados preliminares e pendências

1. **[Bloqueante]** Caminho da fonte desatualizado na skill (`python\gitpr` → `pessoal\gitpr_projeto\gitpr`); caso especial `readme` aponta para a raiz do repo gitpr (idem, mover).
2. Lista de monolíngues defasada: `ARCHITECTURE` (agora 5 idiomas) e `caveman-commit` (não está mais na fonte) devem sair; `otimizacao-de-tokens` e `version-markers` devem entrar; `testar_sem_usar_pypi` segue monolíngue na fonte (site tem 5/5 próprias — comportamento previsto: não tocar).
3. Lista de subdirs ignorados incompleta: falta `testing/`; não-docs no topo também incluem `logo.png`, `logo.psd`, `progit.pdf` (hoje ignorados só por serem não-`.md`).
4. Critérios internos de verificação: o critério "5 variantes canônicas por tópico" conflita com o tratamento de monolíngues (lacuna reportada) — wording passível de ajuste para não gerar falso negativo.
5. Sem CONTEXT.md / memória de projeto (não bloqueante).
6. Correções propostas para a skill **aguardando aprovação** (não aplicadas): (a) atualizar caminho da fonte; (b) atualizar lista de monolíngues; (c) completar lista de ignorados; (d) opcional: explicitar tratamento de tópico novo no passo de diagnóstico (copiar variantes + `menu.json`).
