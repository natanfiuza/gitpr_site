# GitPR 1.2.0 — Novidades

## Novidades desta versão

- **Subcomando `gitpr fix` — o review que se torna um patch aplicável:** O último review da cache alimenta **uma** chamada de IA, que devolve apontamentos em blocos delimitados; o extrator valida cada bloco como diff unificado e o `git apply --check` prova que ele encaixa na árvore atual. Um classificador **determinístico e sem I/O** rotula cada candidato como `safe`, `review_required` ou `experimental`. O dry-run é a predefinição — escrever exige `--apply`, e o `--force` nunca contorna a verificação de aplicabilidade, apenas a classificação. Tudo o que foi aplicado entra em `.gitpr/fix_history.json`, que é o que o `--rollback` lê.
- **Subcomando `gitpr review-pr <n>` — revisar o PR de outra pessoa sem checkout:** O diff vem diretamente da API da forge e entra **no mesmo motor** que os fluxos locais usam — mesmo relatório, mesmas regras de linter, mesmo `.txt`. Apenas de leitura por predefinição: nada é publicado na forge sem um `--post-comment` explícito. Alarga o público-alvo de "quem vai abrir um PR" para "quem foi convidado a revisar o PR de outra pessoa".
- **Resolução de identidade do revisor — a associação que não chegava:** As sugestões nascem do `git blame`, pelo que carregam **nomes e e-mails**, não logins. Quando nada resolvia, a UI mostrava o nome em bruto — e escrever esse nome de volta fazia o GitPR enviá-lo *verbatim* como se fosse um login. O GitHub responde **201 sem associar ninguém**: sucesso aparente, revisor ausente, aviso nenhum. Agora uma camada dedicada resolve a identidade **duas vezes** (antes da TUI e no momento da associação) e fecha também a segunda falha silenciosa da API — o login aceite mas não associado passou a ser detetado lendo de volta `requested_reviewers`.
- **O `gitpr fix` passou a corrigir a revisão que foi revista:** O diff revisto é gravado no registo de cache (`reviewed_diff`) e o `fix` prefere-o, caindo na re-derivação apenas para registos antigos. É a única fonte correta quando o review veio de um PR remoto ou de um diff de branch inteira.
- **O MCP cresceu de 12 para 14 ferramentas e de 17 para 18 recursos:** `list_fix_candidates` (13.ª, apenas de leitura) + `skill://fix`, e `review_remote_pr` (14.ª, apenas de leitura, sem o argumento `post_comment`, sem escrever `.txt`).
- **i18n expandida para 1048 chaves:** +93 desde o relatório anterior, com o `__lang_version__` em **v0.0.28** e paridade total de key sets nos 6 dicionários.
- **Documentação:** 2 famílias novas — `fix-command` (5 idiomas) e `review-pr` (EN + PT-BR) — e 9 tópicos atualizados, incluindo `code-review-ia` (modo remoto como §1.4) e `suggested-reviewers` (resolução de login).
- **Dois defeitos latentes do GitLab corrigidos:** o `changes[].diff` descarta o caminho do ficheiro, pelo que os cabeçalhos `diff --git` passaram a ser sintetizados a partir de `old_path`/`new_path`; e um diff truncado (`overflow: true`) era revisto a meio e publicado como se fosse inteiro — agora lança.
- **Versão 1.2.0:** o `__version__` passou de 1.1.0 para 1.2.0 — o bump está na árvore de trabalho, ainda não commitado nem tagueado, e o `CHANGELOG.md` ainda para em `[1.1.0]`.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

Reveja um pull request que já está aberto — nada é obtido para a sua árvore:

```
gitpr review-pr 123                    # apenas de leitura: o review sai num .txt
gitpr review-pr 123 --post-comment     # o único caminho que escreve na forge
gitpr review-pr 123 --provider deepseek
```

Depois transforme esse review em patches que lê antes de tocarem na sua árvore:

```
gitpr fix                      # lista os candidatos do último review (não escreve nada)
gitpr fix FIX-001              # dry-run: o diff de um apontamento
gitpr fix FIX-001 --apply      # escreve, após uma confirmação
gitpr fix --all-safe --apply   # escreve todos os patches safe, numa branch nova por predefinição
gitpr fix --rollback FIX-001-1a2b3c4d
```

O `gitpr fix` lê o review mais recente do repositório e da branch atuais na cache — execute `gitpr -r` primeiro. O rollback não precisa de commit, stash nem reset: reaplica o diff guardado com `git apply --reverse`.

## Dicas úteis

O `gitpr -r -i src/legacy/parser.py` revê um ficheiro inteiro, ignorando o histórico do git — a documentação chama-lhe "atuar como consultor de refatoração de código legado". Personalize o foco da auditoria através do ficheiro de skill `.gitpr.filereview.md` (coesão, acoplamento).
