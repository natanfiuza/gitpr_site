# GitPR 1.4.0 — Novidades

## Novidades desta versão

- **A release 1.3.0 foi fechada e a 1.4.0 já tem tag:** a tag `v1.3.0` (merge do PR #193, 2026-09-24) fechou o item que bloqueava duas janelas, e o `CHANGELOG.md` foi completado com as seis funcionalidades que faltavam. Dez dias depois, a tag `v1.4.0` (merge do PR #207, 2026-10-12) fechou esta janela inteira — as cinco funcionalidades abaixo **mais** a nota de release da 1.3.0 couberam num único ciclo.
- **O `gitpr release` deixou de repetir o histórico:** as notas de release listavam, a cada versão, todos os commits de todas as versões anteriores (a secção 1.3.0 saiu com 124 bullets, 115 deles repetição da 1.2.0). A causa não era a IA — era o intervalo do `git log`, ancorado no `git describe`, que neste repositório resolvia para `v0.0.8`. O corte passou a ser a **secção anterior do próprio changelog**, cada bullet ganhou **ligação real e clicável** de commit, PR e contribuidor, e um commit que já está noutra secção é descartado antes de virar bullet — quando nada sobra, a release **aborta** em vez de publicar uma secção vazia.
- **A telemetria saiu do ficheiro JSON e tornou-se um ledger SQLite:** os eventos eram gravados em ficheiros por comando, a partir de uma thread daemon, e depois "enriquecidos" de volta pelo cache de IA através de uma correspondência heurística por (repo, branch, ação, minuto) que dependia de três mapas de pastas divergentes. O resultado era previsível: `tokens_actual` **sempre zero** e perda de dados entre repositórios na exportação. O `~/.gitpr/metrics/telemetry.db` grava a linha **de forma síncrona**, com os tokens copiados dos metadados do provedor no momento da escrita, e um processo de curta duração (um hook, por exemplo) deixou de perder a sua própria linha ao sair.
- **Mentor Mode — o review que ensina, não só aponta:** um review diz *o que* está errado e quase nunca *porque é que isso importa*. O `gitpr mentor` (e a flag `--mentor` em `-r`/`-f`) enriquece os apontamentos do último review com o que está a acontecer, porque importa, uma analogia do dia a dia e os conceitos a estudar. É **opt-in** e nunca inventa: sem evidência suficiente, a explicação di-lo em vez de preencher o vazio.
- **Pontuação de risco determinística — sem IA, sem quota:** o `gitpr risk` produz uma pontuação de 0–100 com nível `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` a partir de sinais locais (caminhos críticos, testes em falta, migrations, histórico de bugs e reverts, apontamentos estáticos). A fórmula é declarada (**50/30/20**), tal como as regras de escalonamento obrigatório, e a pontuação entra no prompt do review como contexto **read-only** e no relatório. Um número explicável vale mais do que um palpite caro.
- **Policy Packs — a política do repositório tornou-se um artefacto versionado:** skills, regras de linter, severidades, caminhos críticos, pesos de risco e convenções de PR/commit andavam espalhados e cada repositório configurava-os à mão. O `gitpr policy` adota um pack YAML versionado com **checksum**, resolve a precedência entre camadas e regista a escolha no lockfile que a equipa consolida. Quatro packs embutidos vêm no wheel; sem pack ativo, **nada muda**.
- **Baseline e supressões auditáveis — o ruído conhecido deixa de bloquear:** os linters e os reviews voltavam a reportar os mesmos apontamentos pré-existentes a cada execução, e cabia a quem revia distinguir a saída de cabeça. O `.gitpr/baseline.json` regista o instantâneo dos apontamentos aceites, cada apontamento novo é classificado como `new`/`existing`/`resolved`/`ignored`/`accepted_debt`, e **só o `new` bloqueia**. Cada supressão transporta um motivo; a dívida aceite transporta um responsável e, quando existe, um prazo.
- **O modelo de apontamento tornou-se um tipo canónico:** o `NormalizedFinding` saiu de `infrastructure/linter/external/base_bridge.py` para `src/domain/finding/`. Sem isso, cada bridge — Semgrep, Gitleaks, Bandit, o conjunto de regras regex — produziria uma impressão digital de forma diferente do mesmo apontamento e o baseline não teria como reconhecê-lo.
- **A dívida da janela anterior foi fechada:** o `SKILL_LABELS` e o `mcp_server.SKILL_FILES` receberam `tests`, `explain` e agora `mentor`; as **40 chaves `__()`** que não existiam em nenhum dicionário foram traduzidas; e as **4 falhas** da janela anterior já não existem. As três afirmações que reportavam a dívida continuam a correr — simplesmente deixaram de reportar. A suíte, medida de ponta a ponta nesta janela, fecha em **2795 passados de 2799** — as duas falhas restantes são um bug de isolamento em `tests/test_mentor_cli.py`, não uma regressão do produto.
- **A cobertura de i18n fechou o ciclo:** **1603 chaves** em cada um dos 6 dicionários (+445 nesta janela) e **zero chaves usadas no código sem tradução** — o código usa 1603, os dicionários têm 1603.
- **As traduções pendentes foram pagas:** o `split-command` e o `otimizacao-de-tokens` completaram os 5 idiomas; o `review-pr` continua com 2. Duas famílias só em PT (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) saíram do topo de `docs/` para `docs/extra/`.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

Quatro comandos novos — os de leitura não escrevem nada até passar a opção que escreve:

```
gitpr risk                                 # pontuação de 0 a 100 para o diff atual (sem IA, sem quota)
gitpr risk --file src/auth.py              # os fatores por trás da pontuação de um ficheiro
gitpr risk --format json                   # saída estruturada para CI
gitpr mentor                               # uma explicação didática dos apontamentos do último review
gitpr -r --mentor                          # anexa a secção do mentor ao code review
gitpr policy list                          # os pacotes desta máquina e o que está em vigor
gitpr policy init --stack laravel          # deteta a stack e ativa o pacote correspondente
gitpr policy use acme/team-policy@1.0.0    # fixa um pacote, gravando .gitpr/policy.lock.yml
gitpr baseline create                      # grava o diff atual como baseline aceite
gitpr baseline show                        # os apontamentos, as decisões e as contagens
gitpr baseline suppress <id> --reason "..." # uma decisão sobre um apontamento
```

Nada muda até optar por isso. Sem um `.gitpr/baseline.json` no repositório, todas as execuções comportam-se exatamente como antes — o gate só existe depois de criar o ficheiro e, a partir daí, **só os apontamentos `new` bloqueiam**: `existing`, `resolved`, `ignored` e `accepted_debt` não bloqueiam. Todas as supressões carregam um motivo; a dívida aceite ganha um responsável e, opcionalmente, um prazo. O mesmo se aplica à policy: sem um pacote ativo, a sua configuração fica intacta.

**Quebra de CLI declarada:** `--metrics`, `--export`, `--purge`, `--hook-event` e `--dashboard` saíram da raiz — tornaram-se o grupo `gitpr metrics` (`gitpr metrics dashboard`, `gitpr metrics export`, `gitpr metrics prune`). A telemetria saiu dos ficheiros JSON por comando e passou a ser um ledger SQLite em `~/.gitpr/metrics/telemetry.db`; o `gitpr metrics migrate` importa os ficheiros antigos e move-os para `~/.gitpr/metrics_legacy/`.

O `gitpr risk` nunca chama a IA — é uma fórmula declarada (50/30/20) sobre sinais locais, por isso o mesmo repositório dá sempre o mesmo número. O `gitpr mentor` é opt-in e, quando a evidência não chega, di-lo em vez de inventar uma explicação.

## Dicas úteis

O GitPR ignora ficheiros não rastreados de propósito (lê `git diff HEAD`), e isso é uma funcionalidade: um `.env.local` esquecido com palavras-passe de produção nunca é enviado à IA, e pastas de cache gigantes nunca gastam tokens. Para incluir um ficheiro novo, basta fazer `git add` antes.
