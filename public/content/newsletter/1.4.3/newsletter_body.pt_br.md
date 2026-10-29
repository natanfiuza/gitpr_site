# GitPR 1.4.3 — Novidades

## Novidades desta versão

- **O portão de atualização deixou de perder o release publicado depois da primeira execução do dia:** o cache do PyPI era chaveado pela **data local**, então um release publicado às 10:45 ficava invisível até a meia-noite para quem já havia rodado o CLI às 07:54. O cache virou uma **janela corrida de 1 hora** com `checked_at` ISO, escrita atômica (`os.replace`) e validação na leitura — arquivo corrompido, timestamp no futuro ou sobra do formato antigo simplesmente expira. `gitpr -u`/`--update` passou a **ignorar o cache** e consultar o PyPI em toda invocação, como a documentação já prometia.
- **A comparação de versões virou PEP 440:** `packaging.version.Version` substituiu a aritmética de tuplas — corrige a ordenação de segmentos com vários dígitos (`1.4.10 > 1.4.9`), impede que um pré-release remoto bloqueie uma instalação estável num `pip install --upgrade` que se recusaria a instalá-lo, e trata versão ilegível de qualquer lado como **desconhecida** em vez de "mais nova".
- **O gate agora alcança subcomandos — mas só quando a execução pode perguntar:** `gitpr release`, `fix`, `review-pr` e os demais passam pelo gate quando há TTY nos dois lados. CI, pipes, hooks, `--quiet`/`--hook`/`--mcp` e `-h`/`--help` **nunca** são bloqueados; a ajuda do subcomando é detectada no **`argv` bruto**, porque o Click limpa os argumentos do contexto antes do callback do grupo.
- **As 7 falhas de `tests/badge/test_badge_paths.py` finalmente fecharam:** a validação do token do provider saiu de fora e entrou **dentro** do bloco `if check_remote_state:` do `publish_pull_request_use_case` — o fluxo `--no-edit` deixou de devolver `auth_required` falso-positivo. `pr_url`/`pr_number` passaram a ser lidos com `getattr` defensivo, e o fixture ganhou `token="ghp_fake"` e `number=1`. **A suíte caiu de 12 para 5 falhas conhecidas** — dívida aberta por três janelas consecutivas.
- **A semântica do escape hatch mudou (quebra declarada):** `GITPR_SKIP_UPDATE_CHECK` passou a ser lido como **booleano estrito** — só `1`, `true`, `yes`, `on` e `y` (case-insensitive) desligam a checagem. `false`, `0`, `no`, `off`, `n` e valor vazio **mantêm** o gate ligado.
- **A dívida de i18n não foi paga:** os dicionários seguem em **1620 chaves** (×4) e **1618** (`es.json`, `fr.json`), com **8 chaves usadas no código sem tradução** e **2 órfãs** — exatamente os mesmos números da janela anterior.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

O portão de atualização agora acompanha você nos subcomandos: num terminal interativo, `gitpr release`, `gitpr fix` e `gitpr review-pr` também avisam quando há um release mais novo. CI, pipes, hooks, o servidor MCP, `--quiet` e `-h`/`--help` nunca são bloqueados — e a flag de ajuda é detectada no `argv` bruto, então `gitpr release -h` continua apenas imprimindo a ajuda.

`gitpr -u`/`--update` passou a consultar o PyPI em toda execução, em vez de confiar no cache. Se você desliga o portão com `GITPR_SKIP_UPDATE_CHECK`, note que o valor agora é booleano estrito: só `1`, `true`, `yes`, `on` ou `y` desligam a checagem; `false`, `0`, `no`, `off`, `n` e valor vazio a mantêm ligada.

## Dicas úteis

O `gitpr check` é uma única porta de entrada para CI: roda o linter estático, os scanners de segredos, o risk scoring, a revisão por IA opcional e a classificação por baseline, e emite SARIF 2.1.0, JSON ou JUnit XML para o GitHub Code Scanning, o GitLab SAST e o Jenkins. `--no-ia` deixa tudo grátis e offline, `--ia-only-high-risk` gasta tokens de IA só em arquivos de risco, e os exit codes são determinísticos: 0 passou, 1 findings no nível de `--fail-on` ou acima, 2 erro de configuração ou execução.
