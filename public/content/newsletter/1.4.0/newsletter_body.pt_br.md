# GitPR 1.4.0 — Novidades

## Novidades desta versão

- **O release 1.3.0 foi fechado e o 1.4.0 já está tagueado:** a tag `v1.3.0` (merge do PR #193, 2026-09-24) encerrou o item que bloqueava duas janelas, e o `CHANGELOG.md` foi completado com as seis features que faltavam. Dez dias depois a tag `v1.4.0` (merge do PR #207, 2026-10-12) fechou esta janela inteira — as cinco features abaixo **mais** a nota de release do 1.3.0 caberam num único ciclo.
- **`gitpr release` deixou de repetir o histórico:** as notas de release listavam, a cada versão, todos os commits de todas as versões anteriores (a seção 1.3.0 saiu com 124 bullets, 115 deles repetição da 1.2.0). A causa não era a IA — era o range do `git log`, ancorado em `git describe`, que neste repositório resolvia para `v0.0.8`. O corte passou a ser a **seção anterior do próprio changelog**, cada bullet ganhou **link real e clicável** de commit, PR e contribuidor, e um commit que já está em outra seção é descartado antes de virar bullet — quando nada sobra, o release **aborta** em vez de publicar uma seção vazia.
- **A telemetria saiu do arquivo JSON e virou um ledger SQLite:** eventos eram gravados em arquivos por comando, a partir de uma thread daemon, e depois "enriquecidos" de volta pelo cache de IA através de um casamento heurístico por (repo, branch, ação, minuto) que dependia de três mapas de pastas divergentes. O resultado era previsível: `tokens_actual` **sempre zero** e perda de dados entre repositórios na exportação. O `~/.gitpr/metrics/telemetry.db` grava a linha **de forma síncrona**, com os tokens copiados dos metadados do provedor no momento da escrita, e um processo curto (um hook, por exemplo) deixou de perder a própria linha ao sair.
- **Mentor Mode — o review que ensina, não só aponta:** um review diz *o que* está errado e quase nunca *por que aquilo importa*. O `gitpr mentor` (e a flag `--mentor` em `-r`/`-f`) enriquece os achados do último review com o que está acontecendo, por que importa, uma analogia do dia a dia e os conceitos a estudar. É **opt-in** e nunca inventa: sem evidência suficiente, a explicação diz isso em vez de preencher o vazio.
- **Score de risco determinístico — sem IA, sem cota:** `gitpr risk` produz um score 0–100 com nível `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` a partir de sinais locais (caminhos críticos, testes ausentes, migrations, histórico de bugs e reverts, achados estáticos). A fórmula é declarada (**50/30/20**), as regras de elevação obrigatória também, e o score entra no prompt do review como contexto **read-only** e no relatório. Um número explicável vale mais que um palpite caro.
- **Policy Packs — a política do repositório virou artefato versionado:** skills, regras de linter, severidades, caminhos críticos, pesos de risco e convenções de PR/commit andavam espalhados e cada repositório os configurava à mão. O `gitpr policy` adota um pack YAML versionado com **checksum**, resolve a precedência entre camadas e grava a escolha no lockfile que o time commita. Quatro packs embutidos entram no wheel; sem pack ativo, **nada muda**.
- **Baseline e supressões auditáveis — o ruído conhecido para de bloquear:** linters e reviews re-reportavam os mesmos achados pré-existentes a cada execução, e cabia ao revisor diferenciar a saída na cabeça. O `.gitpr/baseline.json` grava a fotografia dos achados aceitos, cada achado novo é classificado como `new`/`existing`/`resolved`/`ignored`/`accepted_debt`, e **só o `new` bloqueia**. Toda supressão carrega motivo; dívida aceita carrega dono e, se houver, prazo.
- **O modelo de achado virou tipo canônico:** `NormalizedFinding` saiu de `infrastructure/linter/external/base_bridge.py` para `src/domain/finding/`. Sem isso, cada bridge — Semgrep, Gitleaks, Bandit, o ruleset regex — fingerprintaria uma forma diferente do mesmo achado e o baseline não teria como reconhecê-lo.
- **A dívida da janela anterior foi fechada:** `SKILL_LABELS` e `mcp_server.SKILL_FILES` receberam `tests`, `explain` e agora `mentor`; as **40 chaves `__()`** que não existiam em nenhum dicionário foram traduzidas; e as **4 falhas** da janela anterior não existem mais. As três asserções que acusaram a dívida continuam rodando — elas só pararam de acusar. A suíte, medida de ponta a ponta nesta janela, fecha em **2795 aprovados de 2799** — as duas falhas restantes são um bug de isolamento em `tests/test_mentor_cli.py`, não regressão do produto.
- **Cobertura de i18n fechou o ciclo:** **1603 chaves** em cada um dos 6 dicionários (+445 nesta janela) e **zero chaves usadas no código sem tradução** — o código usa 1603, os dicionários têm 1603.
- **As traduções pendentes foram pagas:** `split-command` e `otimizacao-de-tokens` completaram os 5 idiomas; `review-pr` segue com 2. Duas famílias PT-only (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) saíram do topo de `docs/` para `docs/extra/`.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

Quatro comandos novos — os de leitura não escrevem nada até você passar a opção que escreve:

```
gitpr risk                                 # nota de 0 a 100 para o diff atual (sem IA, sem cota)
gitpr risk --file src/auth.py              # os fatores por trás da nota de um arquivo
gitpr risk --format json                   # saída estruturada para CI
gitpr mentor                               # uma explicação didática dos achados do último review
gitpr -r --mentor                          # anexa a seção do mentor ao code review
gitpr policy list                          # os pacotes desta máquina e o que está em vigor
gitpr policy init --stack laravel          # detecta a stack e ativa o pacote correspondente
gitpr policy use acme/team-policy@1.0.0    # fixa um pacote, gravando .gitpr/policy.lock.yml
gitpr baseline create                      # grava o diff atual como baseline aceito
gitpr baseline show                        # os achados, as decisões e as contagens
gitpr baseline suppress <id> --reason "..." # uma decisão sobre um achado
```

Nada muda até você optar. Sem um `.gitpr/baseline.json` no repositório, toda execução se comporta exatamente como antes — o gate só existe depois que você cria o arquivo e, a partir daí, **só os achados `new` bloqueiam**: `existing`, `resolved`, `ignored` e `accepted_debt` não bloqueiam. Toda supressão carrega um motivo; a dívida aceita ganha um responsável e, opcionalmente, um prazo. Vale o mesmo para a policy: sem um pacote ativo, sua configuração fica intacta.

**Quebra de CLI declarada:** `--metrics`, `--export`, `--purge`, `--hook-event` e `--dashboard` saíram da raiz — viraram o grupo `gitpr metrics` (`gitpr metrics dashboard`, `gitpr metrics export`, `gitpr metrics prune`). A telemetria saiu dos arquivos JSON por comando e virou um ledger SQLite em `~/.gitpr/metrics/telemetry.db`; o `gitpr metrics migrate` importa os arquivos antigos e os move para `~/.gitpr/metrics_legacy/`.

O `gitpr risk` nunca chama a IA — é uma fórmula declarada (50/30/20) sobre sinais locais, então o mesmo repositório sempre dá o mesmo número. O `gitpr mentor` é opt-in e, quando a evidência não basta, diz isso em vez de inventar uma explicação.

## Dicas úteis

O GitPR ignora arquivos não rastreados de propósito (ele lê `git diff HEAD`), e isso é uma feature: um `.env.local` esquecido com senhas de produção nunca é enviado à IA, e pastas de cache gigantes nunca gastam tokens. Para incluir um arquivo novo, basta dar `git add` antes.
