# GitPR 1.2.0 — Novidades

## Novidades desta versão

- **Subcomando `gitpr fix` — o review que vira patch aplicável:** O último review do cache alimenta **uma** chamada de IA, que devolve achados em blocos cercados; o extrator valida cada bloco como diff unificado e o `git apply --check` prova que ele encaixa na árvore atual. Um classificador **determinístico e sem I/O** rotula cada candidato como `safe`, `review_required` ou `experimental`. Dry run é o default — escrever exige `--apply`, e o `--force` nunca contorna a checagem de aplicabilidade, apenas a classificação. Tudo que foi aplicado entra em `.gitpr/fix_history.json`, que é o que o `--rollback` lê.
- **Subcomando `gitpr review-pr <n>` — revisar PR de terceiros sem checkout:** O diff vem direto da API da forge e entra **no mesmo motor** que os fluxos locais usam — mesmo relatório, mesmas regras de linter, mesmo `.txt`. Read-only por default: nada é publicado na forge sem `--post-comment` explícito. Amplia o público-alvo de "quem vai abrir um PR" para "quem foi convidado a revisar o PR de outra pessoa".
- **Resolução de identidade do revisor — o attach que não chegava:** As sugestões nascem do `git blame`, então carregam **nomes e e-mails**, não logins. Quando nada resolvia, a UI mostrava o nome puro — e digitar esse nome de volta fazia o GitPR enviá-lo *verbatim* como se fosse login. O GitHub responde **201 sem anexar ninguém**: sucesso aparente, revisor ausente, aviso nenhum. Agora uma camada dedicada resolve a identidade **duas vezes** (antes da TUI e no attach) e fecha também a segunda falha silenciosa da API — o login aceito mas não anexado passou a ser detectado lendo de volta `requested_reviewers`.
- **`gitpr fix` passou a corrigir a revisão que foi revisada:** O diff revisado é gravado no registro de cache (`reviewed_diff`) e o `fix` o prefere, caindo na re-derivação só para registros antigos. É a única fonte correta quando o review veio de um PR remoto ou de um diff de branch inteira.
- **MCP cresceu de 12 para 14 ferramentas e de 17 para 18 recursos:** `list_fix_candidates` (13ª, somente leitura) + `skill://fix`, e `review_remote_pr` (14ª, somente leitura, sem argumento `post_comment`, sem escrever `.txt`).
- **i18n expandida para 1048 chaves:** +93 desde o relatório anterior, com `__lang_version__` em **v0.0.28** e paridade total de key sets nos 6 dicionários.
- **Documentação:** 2 famílias novas — `fix-command` (5 idiomas) e `review-pr` (EN + PT-BR) — e 9 tópicos atualizados, incluindo `code-review-ia` (modo remoto como §1.4) e `suggested-reviewers` (resolução de login).
- **Dois defeitos latentes do GitLab consertados:** o `changes[].diff` descarta o caminho do arquivo, então os cabeçalhos `diff --git` passaram a ser sintetizados a partir de `old_path`/`new_path`; e um diff truncado (`overflow: true`) era revisado pela metade e publicado como se fosse inteiro — agora levanta.
- **Versão 1.2.0:** o `__version__` saiu de 1.1.0 para 1.2.0 — o bump está no working tree, ainda não commitado nem tagueado, e o `CHANGELOG.md` ainda para em `[1.1.0]`.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

Revise um pull request que já está aberto — nada é baixado para a sua árvore:

```
gitpr review-pr 123                    # read-only: o review sai em um .txt
gitpr review-pr 123 --post-comment     # o único caminho que escreve na forge
gitpr review-pr 123 --provider deepseek
```

Depois transforme esse review em patches que você lê antes de eles tocarem na sua árvore:

```
gitpr fix                      # lista os candidatos do último review (não escreve nada)
gitpr fix FIX-001              # dry run: o diff de um achado
gitpr fix FIX-001 --apply      # escreve, após uma confirmação
gitpr fix --all-safe --apply   # escreve todos os patches safe, em uma branch nova por padrão
gitpr fix --rollback FIX-001-1a2b3c4d
```

O `gitpr fix` lê o review mais recente do repositório e da branch atuais no cache — rode `gitpr -r` antes. O rollback não precisa de commit, stash nem reset: ele reaplica o diff guardado com `git apply --reverse`.

## Dicas úteis

O `gitpr -r -i src/legacy/parser.py` revisa um arquivo inteiro, ignorando o histórico do git — a documentação chama isso de "atuar como consultor de refatoração de código legado". Customize o foco da auditoria pelo arquivo de skill `.gitpr.filereview.md` (coesão, acoplamento).
