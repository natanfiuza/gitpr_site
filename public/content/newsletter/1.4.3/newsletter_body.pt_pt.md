# GitPR 1.4.3 — Novidades

## Novidades desta versão

- **O portão de atualização deixou de perder os releases publicados depois da primeira execução do dia:** a cache do PyPI era chaveada pela **data local**, pelo que um release publicado às 10:45 ficava invisível até à meia-noite para quem já tivesse corrido a CLI às 07:54. A cache passou a ser uma **janela deslizante de 1 hora** com `checked_at` ISO, escrita atómica (`os.replace`) e validação na leitura — um ficheiro corrompido, um timestamp no futuro ou um resto do formato antigo simplesmente expira. O `gitpr -u`/`--update` passou a **ignorar a cache** e a consultar o PyPI em todas as invocações, como a documentação já prometia.
- **A comparação de versões passou a PEP 440:** o `packaging.version.Version` substituiu a aritmética de tuplos — corrige a ordenação de segmentos com vários dígitos (`1.4.10 > 1.4.9`), impede que um pré-release remoto bloqueie uma instalação estável num `pip install --upgrade` que se recusaria a instalá-lo, e trata uma versão ilegível de qualquer dos lados como **desconhecida** em vez de "mais recente".
- **O portão passou a alcançar os subcomandos — mas só quando a execução pode perguntar:** o `gitpr release`, o `fix`, o `review-pr` e os restantes passam pelo portão quando há um TTY nas duas pontas. CI, pipes, hooks, `--quiet`/`--hook`/`--mcp` e `-h`/`--help` **nunca** são bloqueados; a ajuda do subcomando é detetada no **`argv` em bruto**, porque o Click limpa os argumentos do contexto antes do callback do grupo.
- **As 7 falhas de `tests/badge/test_badge_paths.py` finalmente fecharam:** a validação do token do provedor saiu de fora e entrou **dentro** do bloco `if check_remote_state:` do `publish_pull_request_use_case` — o fluxo `--no-edit` deixou de devolver um `auth_required` falso-positivo. O `pr_url`/`pr_number` passou a ser lido com `getattr` defensivo, e a fixture ganhou `token="ghp_fake"` e `number=1`. **A suíte caiu de 12 para 5 falhas conhecidas** — dívida aberta durante três janelas consecutivas.
- **A semântica da válvula de escape mudou (quebra declarada):** o `GITPR_SKIP_UPDATE_CHECK` passou a ser lido como um **booleano estrito** — só `1`, `true`, `yes`, `on` e `y` (sem distinção de maiúsculas) desligam a verificação. `false`, `0`, `no`, `off`, `n` e um valor vazio **mantêm** o portão ligado.
- **A dívida de i18n não foi paga:** os dicionários continuam com **1620 chaves** (×4) e **1618** (`es.json`, `fr.json`), com **8 chaves usadas no código sem tradução** e **2 órfãs** — exatamente os mesmos números da janela anterior.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

O portão de atualização passou a acompanhá-lo nos subcomandos: num terminal interativo, `gitpr release`, `gitpr fix` e `gitpr review-pr` também avisam quando existe um release mais recente. CI, pipes, hooks, o servidor MCP, `--quiet` e `-h`/`--help` nunca são bloqueados — e a flag de ajuda é detetada no `argv` bruto, pelo que `gitpr release -h` continua apenas a imprimir a ajuda.

`gitpr -u`/`--update` passou a consultar o PyPI em cada execução, em vez de confiar na cache. Se desligar o portão com `GITPR_SKIP_UPDATE_CHECK`, note que o valor é agora um booleano estrito: só `1`, `true`, `yes`, `on` ou `y` desativam a verificação; `false`, `0`, `no`, `off`, `n` e valor vazio mantêm-na ligada.

## Dicas úteis

O `gitpr check` é uma única porta de entrada para CI: executa o linter estático, os scanners de segredos, o risk scoring, a revisão por IA opcional e a classificação por baseline, e emite SARIF 2.1.0, JSON ou JUnit XML para o GitHub Code Scanning, o GitLab SAST e o Jenkins. `--no-ia` torna tudo gratuito e offline, `--ia-only-high-risk` gasta tokens de IA apenas em ficheiros de risco, e os exit codes são determinísticos: 0 passou, 1 findings no nível de `--fail-on` ou acima, 2 erro de configuração ou execução.
