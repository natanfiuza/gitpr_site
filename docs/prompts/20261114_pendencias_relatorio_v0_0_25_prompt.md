# Prompt de Execução — Pendências dos Relatórios de Estado (`v0.0.25`)

**Data de referência:** 2026-11-14  
**Baseline analisado:**
- Site (`gitpr_site`): `public/content/relatorio.pt_br.md` (espelho sincronizado da versão `v0.0.25`)
- Repositório CLI (`gitpr`): `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs\reports\relatorio_estado_v0.0.25.md`
- Histórico comparado: `relatorio_estado_v0.0.19.md` a `v0.0.24.md`

**Escopo:** consolidação verificada contra o código-fonte, testes e arquivos Markdown atuais de tudo o que **não foi feito** nas seções **`🚧 Próximos Passos`** e **`🌐 Internacionalização e Documentação`**, estruturada em **etapas sequenciais executáveis por um agente de código**, separando claramente **ações operacionais humanas** e **backlog estratégico de produto**.

---

## Parte I — Diagnóstico Verificado do Que Não Foi Feito

### 1. Seção `🚧 Próximos Passos` — O que já foi resolvido vs. o que permanece aberto

#### Já entregue/mitigado até `v0.0.25` (NÃO reabrir)
- **Guardrails do provedor local (`OllamaProvider`):** timeout configurável via `GITPR_OLLAMA_TIMEOUT`, limite mínimo ajustado para workflows locais/chunking `map_reduce`, e bloqueio de HTTP remoto fora de loopback salvo opt-in explícito `GITPR_OLLAMA_ALLOW_REMOTE=true`.
- **Cobertura E2E headless com árvore limpa:** suíte expandida para `3.469` cenários em `166` módulos, com suporte a `HEADLESS=true` e flags CLI não interativas.
- **Aviso para remoto não reconhecido:** alerta acionável quando uma URL remota não casa com GitHub/Bitbucket/GitLab.
- **ADR de migração SCM (`ADR-0020`):** `docs/adr/0020-per-repo-scm-provisioning-migration.md` formaliza slots por forge, `.gitpr/conf/gitpr.config.yml` e uso de `GITPR_SCM_TOKEN` em CI/CD.
- *(Nota histórica)* **"Provedor Anthropic Claude"** constou nos relatórios `v0.0.19`–`v0.0.24`, mas foi **removido intencionalmente** no commit `faeecb8` (`remove Anthropic Claude provider reference from v0.0.24 report`) e não aparece na `v0.0.25`. Não tratar como pendência.

#### Pendências abertas verificadas no código/repositório
| # | Item pendente | Evidência exata observada | Classificação |
|---|---|---|---|
| P1 | **Sincronizar versão `1.8.0` em `CLAUDE.md` e `GEMINI.md`** | Linha 8 de ambos ainda exibe `- **Current version:** 1.7.0 (Canonical SSOT in \`src/updater.py\`)`, causando falha em `tests/unit/test_version_consistency.py` (regex `-\s+\*\*Current version:\*\*\s+([0-9]+\.[0-9]+\.[0-9]+)`). | Agente (Código/SSOT) |
| P2 | **Corrigir resumo da seção `[1.8.0]` no `CHANGELOG.md`** | Seção `## [1.8.0] - 2026-11-14` (linha 3) traz na linha 6: `Version 1.7.0 improves usability, documentation, and setup workflows...` e lista na linha 22 `- bump version to 1.7.0 and lang to v0.0.42 (2354a30)` em vez do texto/bump consolidado da `1.8.0`. | Agente (Release/Docs) |
| P3 | **Alinhar tag Git `v1.7.0`** | Item herdado da `v0.0.24`: a tag `v1.7.0` aponta para o commit `b2688c4` (onde `__version__` ainda era `1.6.0`), em vez do commit `2354a30` onde ocorreu o bump para `1.7.0`. | Agente (Release/Git, com cautela se tag já estiver em remoto protegido) |
| P4 | **Atualizações pendentes em documentação específica da `v0.0.25`** | 4 tópicos citados no relatório ainda apresentam lacunas: `config-tui` (variável `GITPR_SKIP_PROJECT_CONFIG_SEED` ausente nas traduções + nota obsoleta `gitpr -h config` na linha 259 nas 5 variantes), `pull-request-publication` (seção `9.4`, alerta `9.1`, tabela `§14` e exemplos `§15` ausentes nas 4 traduções), `metricas-telemetria` (sem exemplo prático de limiar custo/latência por provedor para `GITPR_MAP_REDUCE_*`), `scm-multiforge` (sem detalhamento de `.env` read-only / runner efêmero conforme `ADR-0020 §4`). | Agente (Docs/i18n) |
| P5 | **Suporte a host customizado de GitHub Enterprise** | `src/infrastructure/scm/github_provider.py` linhas 81–94 (`GitHubProvider.parse_repo_ref`) usa regex hardcoded `r"github\.com[:/]([^/]+)/([^/]+?)(?:\.git)?/?$"`, levantando `ValueError("Could not parse GitHub repository from remote URL: ...")` para domínios Enterprise (documentado como limitação em `docs/scm-multiforge.md` linha 61). | Agente (Feature/Testes) |
| P6 | **Publicação no Marketplace (VS Code / Open VSX)** | Depende de execução de `scripts/marketplace-setup-wizard.sh` com PATs reais (`VSCE_PAT`, `OVSX_PAT`) e disparo de tag `ext-v*`. | **Ação Humana Operacional** |
| P7 | **Mais provedores de IA (OpenAI direta e locais adicionais)** | Citado apenas como direção estratégica de médio/longo prazo no relatório `v0.0.25`. | **Backlog Estratégico** |

---

### 2. Seção `🌐 Internacionalização e Documentação` — Inventário Completo de Lacunas

- **Arquivos de idioma do CLI (`lang/`):** `1.807` chaves × `6` idiomas (`v0.0.43`), `0` chaves faltantes. Nenhuma pendência estrutural aberta no estado atual (apenas manter sincronia se a Etapa de GitHub Enterprise adicionar novas mensagens).
- **Tópicos de documentação (`52` canônicos no CLI):** `51` possuem 5 variantes, **`1` é monolíngue (`github-issue-prompt-com-gh.md`)**.
- **Descobertas críticas na auditoria estrutural e de conteúdo:**
  1. **`github-issue-prompt-com-gh.md` está em Português apesar do nome canônico inglês (`_EN_ONLY`):**
     As linhas 1–87 de `gitpr/docs/github-issue-prompt-com-gh.md` estão redigidas em PT-BR (`# Como Usar o Template de Prompt...`). Ao expandir para 5 idiomas, é obrigatório colocar a versão em **inglês** em `.md` e criar `.pt_br.md`, `.pt_pt.md`, `.es_es.md`, `.fr_fr.md`.
  2. **`_PARITY_EXCEPTIONS` em `tests/unit/test_docs_translation_parity.py` (linhas 20–29) lista 8 tópicos com dívida estrutural conhecida:**
     - `ARCHITECTURE`: EN `277L / H=27 / R=14 / B=77 / F=4` vs `.pt_br, .pt_pt, .es_es, .fr_fr` `254L / H=26 / R=14 / B=69 / F=2` → falta a seção `### **20. Project-Local PR Target (project_config.py)**` e seu bloco fenced nas 4 traduções.
     - `check-ci`: EN `271L / F=12` vs `.pt_br` `245L / F=10` e `.pt_pt, .es_es, .fr_fr` `241L / F=10` → em `### JSON Envelope (--format json)`, o EN traz um bloco ` ```json { ... } ``` ` completo (linhas 88–114), enquanto as 4 traduções resumiram em 1 linha sem o bloco JSON.
     - `git-hooks-locais`: EN `112L / H=10 / F=10` vs `.pt_br` `114L / H=10 / F=8` (as linhas 59–61 de `.pt_br` colocaram `Bash` e `git commit --no-verify...` em texto plano sem cercas ` ``` `) e vs `.pt_pt` (`84L / H=9 / F=4`) / `.es_es, .fr_fr` (`83L / H=9 / F=4`), que estão sem a seção `### The "passwords" come from the built-in secret scanning` e os respectivos blocos de código (`GITPR_LINTER_SECURITY*` etc.).
     - `git-status`: EN e `.pt_br` `148L / H=13 / R=16 / B=3 / F=14` vs `.pt_pt, .es_es, .fr_fr` `124L / H=13 / R=16 / B=3 / F=8` → faltam 3 blocos fenced em `.pt_pt`/`.es_es`/`.fr_fr` (bloco de aviso *Commit-specific behavior*, bloco *Review/FullReview/Issue* e bloco JSON `analyze_unstaged_diff`).
     - `linter-regras-customizadas`: EN `232L / H=16 / R=9 / B=8 / F=10` vs:
       - `.pt_br` (`230L / H=14 / R=9 / B=9 / F=6`): os **4 blocos ` ```YAML `** (linhas 20, 64, 90, 102) estão **sem fechamento ` ``` `**, quebrando o parser Markdown (engolindo `## **3.` e `### **Exemplo Prático 1...`), além de ter `## ---` na linha 52 e escapes espúrios (`\#`, `\_`, `\-`, `\[`, `\\*`) dentro dos snippets YAML;
       - `.pt_pt, .es_es, .fr_fr` (`142L / H=10 / R=0 / B=6 / F=4`): faltam `### External Linter Timeout`, toda a seção `## 7. Built-in Secret Scanning` (tabela de 7 regras + bloco `GITPR_LINTER_SECURITY*`) e blocos auxiliares.
     - `mentor-mode`: EN e `.pt_br` `62L / H=6 / R=11 / B=3 / F=2` vs `.pt_pt, .es_es, .fr_fr` `34L / H=4 / R=4 / B=0 / F=2` → faltam 2 headings, 7 linhas de tabela e 3 bullets nas 3 traduções.
     - `pull-request-publication`: EN `617L / H=54 / R=99 / B=16 / F=58` vs `.pt_br, .pt_pt, .es_es, .fr_fr` `520L / H=47 / R=91 / B=13 / F=46` → faltam nas 4 traduções o alerta `> [!IMPORTANT]` em `§9.1` (linhas 321–322 EN), toda a seção `### 9.4 Migration & Workflow Note: CI/CD, Monorepos and Clean Trees` (linhas 340–394 EN), a linha `GITPR_SKIP_PROJECT_CONFIG_SEED` na tabela `§14` (linha 504 EN) e os Exemplos 9 (`gitpr --status`) e 10 (`gitpr -c --no-unstaged-check`) na `§15`.
     - `skill-template`: EN `91L / H=6 / R=20 / B=4 / F=6` vs `.pt_br, .pt_pt, .es_es, .fr_fr` `87L / H=6 / R=18 / B=4 / F=6` → faltam linha `.gitpr.release.md` na tabela `§1` (linha 25 EN), linha `gitpr release` na tabela `§2` (linha 44 EN) e parágrafo `**Release skill auto-download:**` (linha 49 EN).
  3. **Deriva oculta fora de `_PARITY_EXCEPTIONS` (não pega pelo scanner automático):**
     - `plugins-system.pt_br.md` tem `192` linhas vs `204` linhas nas outras 4 variantes: faltam `12` linhas **dentro** de dois blocos fenced de exemplo (*Security Auditor* e *PHPUnit Test Generator* — especificamente o bloco `Use the format:` e os bullets de `Output a ready-to-run PHPUnit test class with:`). O teste atual não detecta porque `_scan()` ignora conteúdo com `in_fence=True`.
     - `config-tui.*`: todas as 5 variantes têm contagem estrutural idêntica (`259L / H=17 / R=92 / B=8 / F=4`), porém apenas o `.md` (EN, linha 159) documenta `GITPR_SKIP_PROJECT_CONFIG_SEED=true`, e todas as 5 ainda citam `gitpr -h config` na linha 259.
- **Impacto no `gitpr_site`:** `.claude/skills/sync-docs/SKILL.md` (linha 73) ainda cita `github-issue-prompt-com-gh` como monolíngue; após atualizar `gitpr/docs`, será necessário sincronizar `gitpr_site/public/content/docs` via `/sync-docs` (respeitando o mapeamento `.es_es`→`.es` e `.fr_fr`→`.fr` e os 4 tópicos exclusivos do site: `chat-interativo`, `testar_sem_usar_pypi`, `como_reverter_commit_git_localmente`, `caveman-commit`).

---

## Parte II — Roteiro de Execução em Etapas para o Agente de Código

> **Instruções gerais para o agente executor:**
> - As **Etapas 1 a 6** são executadas no repositório do CLI: `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr`.
> - A **Etapa 7** é executada no repositório do site: `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr_site`.
> - **Não** edite arquivos em `gitpr_site/public/content/docs` manualmente durante as Etapas 1–6; a propagação para o site deve ocorrer apenas na Etapa 7 pelo fluxo oficial de sincronização.
> - Respeite rigorosamente as convenções de sufixo de idioma: no `gitpr/docs`, espanhol usa `.es_es.md` e francês usa `.fr_fr.md`.

---

### Etapa 1 — Alinhar SSOT de Versão (`1.8.0`) e `CHANGELOG.md`

**Objetivo:** Eliminar a inconsistência de versão entre `src/updater.py` (`1.8.0`), instruções de projeto (`CLAUDE.md`, `GEMINI.md`) e `CHANGELOG.md`, fazendo `test_version_consistency.py` voltar a passar.

**Arquivos alvo (`gitpr`):**
- `CLAUDE.md` (linha 8)
- `GEMINI.md` (linha 8)
- `CHANGELOG.md` (linhas 3–28)
- `tests/unit/test_version_consistency.py` (somente leitura/verificação)

**Ações:**
1. Ler `src/updater.py` para confirmar que `__version__` está em `1.8.0` (e conferir a versão atual de `lang`).
2. Em `CLAUDE.md` (linha 8) e `GEMINI.md` (linha 8), atualizar:
   - De: `- **Current version:** 1.7.0 (Canonical SSOT in \`src/updater.py\`)`
   - Para: `- **Current version:** 1.8.0 (Canonical SSOT in \`src/updater.py\`)`
3. Em `CHANGELOG.md`, dentro de `## [1.8.0] - 2026-11-14`:
   - Corrigir a linha 6 (`### Summary`) para referenciar `Version 1.8.0 ...` (em vez de `Version 1.7.0 ...`).
   - Revisar a seção `### 🔧 Chores` (linha 22) comparando com `git log` entre `v1.7.0`/`2354a30` e `HEAD`, garantindo que o commit/entrada da `1.8.0` esteja refletido corretamente e não deixe uma entrada deslocada de `1.7.0` como se fosse a entrega principal da `1.8.0`.

**Verificação:**
```bash
pytest tests/unit/test_version_consistency.py -v
```
Critério de aceite: todos os testes de `test_version_consistency.py` passam (`0` falhas).

---

### Etapa 2 — Alinhar a Tag Git Herdada `v1.7.0` (`b2688c4` → `2354a30`)

**Objetivo:** Resolver a pendência herdada desde `v0.0.24` de que a tag `v1.7.0` aponta para `b2688c4` (quando `__version__` ainda era `1.6.0`), devendo apontar para `2354a30` (commit do bump para `1.7.0`).

**Ações:**
1. Inspecionar antes de alterar:
   ```bash
   git show v1.7.0 --stat
   git show b2688c4:src/updater.py | grep __version__
   git show 2354a30:src/updater.py | grep __version__
   ```
2. Verificar se `v1.7.0` já existe no remoto (`git ls-remote --tags origin v1.7.0`) e se o usuário/ambiente permite reescrever tags remotas ou se uma GitHub Release publicada depende dela:
   - Se for seguro/autorizado atualizar a tag localmente (e no remoto, se aplicável):
     - Se `v1.7.0` for tag anotada, preservar ou recriar mensagem anotada apontando para `2354a30`:
       ```bash
       git tag -f -a v1.7.0 2354a30 -m "Release v1.7.0"
       ```
     - Só executar `git push origin :refs/tags/v1.7.0 && git push origin v1.7.0` (ou `--force`) se tiver permissão explícita para mover tag já publicada no remoto; caso contrário, atualizar localmente e registrar claramente a necessidade de push da tag ao mantenedor.

**Verificação:**
```bash
git rev-list -n 1 v1.7.0
git show v1.7.0:src/updater.py
```
Critério de aceite: `git rev-list -n 1 v1.7.0` resolve para o hash completo de `2354a30` e `__version__` nesse commit é `"1.7.0"`.

---

### Etapa 3 — Completar Documentação e Notas Operacionais Pendentes na `v0.0.25`

**Objetivo:** Fechar todos os itens documentais explícitos da seção `Próximos Passos` do relatório `v0.0.25` (garantindo consistência em todos os idiomas já nesta etapa ou em conjunto com a Etapa 4).

**Arquivos alvo (`gitpr/docs/`):**
1. `config-tui.md`, `config-tui.pt_br.md`, `config-tui.pt_pt.md`, `config-tui.es_es.md`, `config-tui.fr_fr.md`
2. `pull-request-publication.md` + `.pt_br.md`, `.pt_pt.md`, `.es_es.md`, `.fr_fr.md`
3. `metricas-telemetria.md` + `.pt_br.md`, `.pt_pt.md`, `.es_es.md`, `.fr_fr.md`
4. `scm-multiforge.md` + `.pt_br.md`, `.pt_pt.md`, `.es_es.md`, `.fr_fr.md`

**Ações detalhadas:**
1. **`config-tui.*`:**
   - Adicionar nas 4 traduções (`.pt_br`, `.pt_pt`, `.es_es`, `.fr_fr`) a descrição e link de `GITPR_SKIP_PROJECT_CONFIG_SEED=true` equivalente à linha 159 do `.md` em inglês.
   - Inspecionar o CLI atual (`src/` — help/subcomandos) para confirmar se `gitpr -h config` na linha 259 é obsoleto e qual é o comando correto atual (ex.: `gitpr --config` ou `gitpr --help`); atualizar essa nota nas **5 variantes** de `config-tui`.
2. **`pull-request-publication.*`:**
   - Trazer para `.pt_br`, `.pt_pt`, `.es_es` e `.fr_fr` todo o conteúdo presente no EN (`617` linhas) e ausente nas traduções (`520` linhas):
     - Alerta `> [!IMPORTANT]` em `§9.1` (linhas 321–322 EN);
     - Seção completa `### 9.4 Migration & Workflow Note: CI/CD, Monorepos and Clean Trees` (linhas 340–394 EN);
     - Linha `GITPR_SKIP_PROJECT_CONFIG_SEED` na tabela `§14` (linha 504 EN);
     - Exemplos 9 (`gitpr --status`) e 10 (`gitpr -c --no-unstaged-check`) na `§15` (ajustando a numeração final para `11` exemplos em todas as variantes).
3. **`metricas-telemetria.*`:**
   - Consultar os defaults reais em `src/` para variáveis `GITPR_MAP_REDUCE_*` e custos/modelos em `docs/map-reduce-diff.md` e `src/telemetry.py`.
   - Adicionar nas **5 variantes** um exemplo prático de avaliação de **custo vs. latência por provedor** para calibrar limiares de `GITPR_MAP_REDUCE_*` (ex.: quando o chunking `map_reduce` reduz estouro de janela/latência em modelos locais ou reduz custo vs. múltiplas chamadas).
4. **`scm-multiforge.*`:**
   - Ler `src/scm_settings.py` (`migrate_legacy_scm_config`, `MigrationResult`) e `docs/adr/0020-per-repo-scm-provisioning-migration.md §4`.
   - Expandir a seção de migração/CI nas **5 variantes** documentando explicitamente o comportamento quando `.env` é **somente leitura** ou o **runner CI/CD é efêmero**: a migração captura `(OSError, PermissionError)`, retorna `persisted=False` com aviso acionável, **não falha o pipeline** e mantém as credenciais funcionando em memória no processo atual (além de recomendar `GITPR_SCM_TOKEN` em CI/CD).

**Verificação:**
- Checar se nenhum link cruzado (`[texto](arquivo.md)`) aponta para âncora inexistente.
- Garantir que todas as variáveis/flags citadas existem exatamente no código em `src/`.

---

### Etapa 4 — Zerar `_PARITY_EXCEPTIONS` e Corrigir Derivas Ocultas em Blocos Fenced

**Objetivo:** Alinhar estruturalmente todos os 8 tópicos atualmente excepcionados em `tests/unit/test_docs_translation_parity.py`, remover todas as entradas de `_PARITY_EXCEPTIONS` e corrigir a deriva oculta em `plugins-system.pt_br.md`.

**Arquivos alvo (`gitpr`):**
- `tests/unit/test_docs_translation_parity.py`
- Tópicos de documentação em `docs/`:
  1. `ARCHITECTURE.{pt_br,pt_pt,es_es,fr_fr}.md`
  2. `check-ci.{pt_br,pt_pt,es_es,fr_fr}.md`
  3. `git-hooks-locais.{pt_br,pt_pt,es_es,fr_fr}.md`
  4. `git-status.{pt_pt,es_es,fr_fr}.md`
  5. `linter-regras-customizadas.{pt_br,pt_pt,es_es,fr_fr}.md`
  6. `mentor-mode.{pt_pt,es_es,fr_fr}.md`
  7. `pull-request-publication.{pt_br,pt_pt,es_es,fr_fr}.md` *(já alinhado na Etapa 3 — validar contagens)*
  8. `skill-template.{pt_br,pt_pt,es_es,fr_fr}.md`
  9. `plugins-system.pt_br.md` *(fora de `_PARITY_EXCEPTIONS`, correção obrigatória)*

**Checklist exato por tópico:**
1. **`ARCHITECTURE.*`:** traduzir e inserir `### **20. Project-Local PR Target (project_config.py)**` (linhas finais de `ARCHITECTURE.md`, incluindo seu bullet list e bloco fenced) nas 4 traduções.
2. **`check-ci.*`:** substituir o resumo de 1 linha sob `### JSON Envelope (--format json)` pelo bloco completo ` ```json { ... } ``` ` (linhas 88–114 de `check-ci.md`) nas 4 traduções.
3. **`git-hooks-locais.*`:**
   - Em `.pt_br`: encapsular o comando das linhas 59–61 (`Bash` / `git commit --no-verify ...`) em um bloco ` ```bash ... ``` ` válido (restaurando `fences=10`).
   - Em `.pt_pt`, `.es_es`, `.fr_fr`: adicionar a seção `### The "passwords" come from the built-in secret scanning` (linhas equivalentes do EN/PT-BR) com todos os seus blocos ` ```bash ``` `.
4. **`git-status.*`:** adicionar em `.pt_pt`, `.es_es` e `.fr_fr` os 3 blocos fenced presentes em EN/PT-BR (bloco de aviso *Commit-specific behavior*, bloco *Review/FullReview/Issue* e bloco JSON `analyze_unstaged_diff`).
5. **`linter-regras-customizadas.*`:**
   - Em `.pt_br`: fechar todos os 4 blocos ` ```YAML ` (linhas 20, 64, 90, 102 — atualmente todos abertos sem fechamento ` ``` `), remover `## ` indevido da linha 52 (`## ---` → `---`), e limpar escapes espúrios (`\#`, `\_`, `\-`, `\[`, `\\*`) nos snippets YAML comparando linha a linha com `linter-regras-customizadas.md`.
   - Em `.pt_pt`, `.es_es`, `.fr_fr`: adicionar `### External Linter Timeout`, toda a seção `## 7. Built-in Secret Scanning` (incluindo tabela de 7 regras e bloco `GITPR_LINTER_SECURITY*`), além dos blocos/subtítulos faltantes das seções 1–6 para igualar `headings=16, table_rows=9, bullets=8, fences=10`.
6. **`mentor-mode.*`:** completar `.pt_pt`, `.es_es`, `.fr_fr` com base em `mentor-mode.md`/`.pt_br.md` (`62L, H=6, R=11, B=3, F=2`).
7. **`skill-template.*`:** adicionar nas 4 traduções a linha `.gitpr.release.md` na tabela `§1`, a linha `gitpr release` na tabela `§2` e o parágrafo `**Release skill auto-download:**`.
8. **`plugins-system.pt_br.md`:** restaurar as 12 linhas faltantes dentro dos dois blocos fenced (*Security Auditor*: linhas `Use the format:` até `- **Fix**...`; *PHPUnit Test Generator*: linhas `Output a ready-to-run PHPUnit test class with:` + 4 bullets + instrução final), conferindo paridade visual linha a linha com `plugins-system.md`.
9. Em `tests/unit/test_docs_translation_parity.py`, esvaziar `_PARITY_EXCEPTIONS = {}` (ou remover o dicionário/parametrização de exceção mantendo validação rígida em todos os 52 tópicos, conforme o padrão do arquivo).

**Verificação:**
```bash
pytest tests/unit/test_docs_translation_parity.py -v
```
Além do pytest (que ignora conteúdo interno de code fences), rodar um check rápido verificando contagem de linhas e fechamento balanceado de cercas ` ``` ` em `plugins-system.*.md` e `linter-regras-customizadas.*.md`.

---

### Etapa 5 — Internacionalizar `github-issue-prompt-com-gh` (52/52 Tópicos em 5 Idiomas)

**Objetivo:** Transformar o último tópico monolíngue em documentação completa nos 5 idiomas e removê-lo de `_EN_ONLY`.

**Ponto de atenção crítico:** atualmente `gitpr/docs/github-issue-prompt-com-gh.md` (87 linhas) tem extensão canônica de inglês (`.md`), mas seu texto está redigido em **português** (`# Como Usar o Template de Prompt do GitPR para Resolver Issues do GitHub com gh CLI`).

**Ações:**
1. Preservar e revisar o texto atual em português como `docs/github-issue-prompt-com-gh.pt_br.md` (ajustando eventuais links internos para os equivalentes `.pt_br.md` e corrigindo tipografia se necessário).
2. Traduzir o conteúdo para **inglês** e gravar em `docs/github-issue-prompt-com-gh.md` (com links apontando para os documentos canônicos `.md`).
3. Criar as versões equivalentes:
   - `docs/github-issue-prompt-com-gh.pt_pt.md` (português europeu)
   - `docs/github-issue-prompt-com-gh.es_es.md` (espanhol)
   - `docs/github-issue-prompt-com-gh.fr_fr.md` (francês)
4. Em `tests/unit/test_docs_translation_parity.py`, remover `"github-issue-prompt-com-gh"` de `_EN_ONLY` (`_EN_ONLY = {}`).
5. Conferir se há referências em índices/menus/READMEs do `gitpr` que precisam mencionar as variantes.

**Verificação:**
```bash
pytest tests/unit/test_docs_translation_parity.py -v
```
Critério de aceite: todos os `52` tópicos validam sem exceções em `_EN_ONLY` nem em `_PARITY_EXCEPTIONS`.

---

### Etapa 6 — Suporte a Host Customizado de GitHub Enterprise (`GitHubProvider.parse_repo_ref`)

**Objetivo:** Implementar o único item de engenharia de código listado nos `Próximos Passos` do relatório `v0.0.25`.

**Arquivos alvo (`gitpr`):**
- `src/infrastructure/scm/github_provider.py` (linhas 81–94; inspecionar também `src/scm_settings.py`, `src/infrastructure/scm/` e `ProviderFactory` / detecção de provedor por remoto)
- `tests/scm/test_github_provider.py`
- `docs/scm-multiforge.{md,pt_br.md,pt_pt.md,es_es.md,fr_fr.md}` (remover/atualizar a nota de limitação atual na linha 61)

**Ações:**
1. Inspecionar como a URL base da API do GitHub e o host são configurados para slots GitHub no `gitpr` (ex.: `GITPR_GITHUB_API_URL`, `api_url`, `GITPR_SCM_BASE_URL` ou configuração por repositório em `.gitpr/conf/gitpr.config.yml` / `~/.gitpr/.env`).
2. Atualizar `GitHubProvider` e `parse_repo_ref(self, remote_url: str) -> RepoRef`:
   - Suportar `github.com` padrão (`https://github.com/owner/repo.git`, `git@github.com:owner/repo.git`, `ssh://git@github.com/owner/repo.git`) **e** hosts GitHub Enterprise configurados ou explicitamente roteados para `GitHubProvider` (HTTPS com ou sem porta, SSH `git@ghe.company.com:org/repo.git`, `ssh://git@ghe.company.com[:port]/org/repo.git`).
   - Garantir que `base_url` / API REST Enterprise (`/api/v3` quando host != `github.com`, ou `api_url` explícita) esteja coerente caso o provider use `self.api_base` / `self.base_url`.
   - Não quebrar a detecção de outros forges (`GitLabProvider`, `BitbucketProvider`): se `parse_repo_ref` aceitar qualquer host quando explicitamente configurado, validar que URLs inválidas ou sem `owner/repo` ainda levantam `ValueError` claro.
3. Adicionar testes unitários em `tests/scm/test_github_provider.py` cobrindo:
   - HTTPS Enterprise (`https://github.acme.internal/org/repo.git`)
   - SSH SCP-like Enterprise (`git@github.acme.internal:org/repo.git`)
   - SSH URI Enterprise (`ssh://git@github.acme.internal/org/repo`)
   - Preservação de casos `github.com` e falha esperada para URLs malformadas.
4. Atualizar `docs/scm-multiforge.*` (`§1.2` / `§3`) removendo a limitação de regex exclusivo de `github.com` e documentando como configurar host/API de GitHub Enterprise.

**Verificação:**
```bash
pytest tests/scm/ -v
pytest tests/unit/test_docs_translation_parity.py -v
```

---

### Etapa 7 — Sincronização no Site (`gitpr_site`)

**Objetivo:** Propagar para o `gitpr_site` toda a documentação atualizada nas Etapas 3–6 e atualizar as regras/skills do site.

**Arquivos alvo (`gitpr_site`):**
- `.claude/skills/sync-docs/SKILL.md` (linha 73: remover `github-issue-prompt-com-gh` da lista de monolíngues na fonte)
- `public/content/docs/*` (via fluxo oficial da skill `/sync-docs` + `rewrite_doc_links.py`)
- `public/content/relatorio*.md` (via `/update-relatorio` quando um novo relatório `v0.0.26`+ existir em `gitpr/docs/reports/`)

**Ações:**
1. Atualizar `.claude/skills/sync-docs/SKILL.md` linha 73 para refletir que todos os `52` tópicos da fonte têm 5 idiomas (após a Etapa 5).
2. Executar o procedimento da skill `/sync-docs`:
   - Copiar arquivos novos/modificados de `gitpr/docs` convertendo `.es_es.md` → `.es.md` e `.fr_fr.md` → `.fr.md`.
   - Executar `rewrite_doc_links.py` e validar com `--check`.
   - **Preservar intocados** os 4 tópicos exclusivos do site (órfãos conhecidos sem correspondência em `gitpr/docs`): `chat-interativo`, `testar_sem_usar_pypi`, `como_reverter_commit_git_localmente`, `caveman-commit`.
3. Verificar se `resources/js/` ou `public/content/menu.json` precisam de entrada para `github-issue-prompt-com-gh` ou se já está listado.
4. Quando houver novo relatório de estado no CLI (`relatorio_estado_v0.0.26.md`), rodar `/update-relatorio` no `gitpr_site`.

**Verificação (`gitpr_site`):**
```bash
php artisan test
npm run build
```
E confirmar zero saídas de erro em `rewrite_doc_links.py --check`.

---

## Parte III — Escopo Separado: Ação Operacional Humana e Backlog Estratégico

> **Não executar autonomamente no agente de código sem autorização/credenciais explícitas:**

### A. Ação Operacional Humana (Release de Extensão VS Code / Open VSX)
- **O quê:** publicar a extensão VS Code no Visual Studio Marketplace e Open VSX Registry.
- **Motivo de bloqueio para agente:** exige tokens secretos pessoais (`VSCE_PAT` e `OVSX_PAT`), criação de conta/publisher e disparo controlado de tag `ext-v*`.
- **Script existente no `gitpr`:** `scripts/marketplace-setup-wizard.sh`.

### B. Backlog Estratégico de Produto (Fora do escopo imediato deste roteiro)
- **Mais provedores de IA:** suporte a API direta da OpenAI e provedores locais adicionais (evolução arquitetural de adapters LLM; requer ADR/especificação própria antes de implementação).
- *(Observação)* Provedor Anthropic Claude foi removido do relatório de status no commit `faeecb8` e não integra o backlog atual documentado na `v0.0.25`.
