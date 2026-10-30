# GitPR 1.4.4 — Novidades

## Novidades desta versão

- **O `gitpr -c` virou uma TUI de 4 etapas:** commitar deixou de ser um pipeline silencioso. O `CommitApp` abre o **Seletor de Stage** para os arquivos fora do index, gera a mensagem em uma thread de background sobre o **diff staged**, mostra o **modal de revisão/edição** e executa o commit em um modal animado de progresso — dá para escolher arquivos, editar o texto ou cancelar antes de qualquer coisa chegar ao repositório.
- **A mensagem agora descreve o que foi de fato staged:** `get_git_staged_diff()` roda `git diff --cached` com as mesmas proteções do diff normal (`-U1 -w -M -B`, smart excludes, UTF-8), caindo no diff da working tree só quando o index está vazio. O que não está staged não entra no prompt.
- **O modal de commit se fecha sozinho no sucesso:** `mark_finished(False)` + `set_timer(0.6, self.exit)` devolvem o terminal para você, e a CLI imprime `committed`/`failed` com a saída do git. Em erro, o botão **Close** continua ali, porque é onde o log da falha fica legível.
- **1.4.3 e 1.4.4 estão as duas no PyPI:** a correção do gate de atualização — cache móvel de 1 hora, comparação PEP 440, gate alcançando subcomandos e `GITPR_SKIP_UPDATE_CHECK` como booleano estrito — entrou na 1.4.3 e foi publicada na mesma janela. `pip install --upgrade gitpr-cli` instala a 1.4.4.
- **Quebra declarada:** fora do `--hook`, o `gitpr -c` **sempre** abre a TUI — não há verificação de TTY no roteamento. Scripts e jobs de CI que dependiam do commit silencioso precisam migrar para `--hook`.
- **A dívida de i18n aumentou, não foi paga:** os dicionários continuam com **1620 chaves** (×4) e **1618** (`es.json`, `fr.json`), mas o código agora usa **1629** — as 3 strings novas da TUI de commit entraram sem tradução. São **11 chaves sem dicionário** e **2 órfãs**, e o `__lang_version__` subiu para **v0.0.36** sem nenhum arquivo de idioma mudar.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

Rode `gitpr -c` (ou `gitpr --commit`) em qualquer repositório. Se tudo já estiver staged, o Seletor de Stage é pulado; caso contrário, ele lista os arquivos fora do index com **Select All**, **Deselect All**, **Stage Selected**, **Skip Staging** e **Cancel**. Revise a mensagem gerada, edite se quiser — o texto editado é o que o git commita — e confirme. O modal de progresso se fecha sozinho quando o commit dá certo.

Para automação, o `--hook` mantém o caminho silencioso e merge-aware de antes, então use em hooks de commit e CI. `GITPR_SKIP_UNSTAGED_CHECK=true` (ou `--no-unstaged-check`) pula só o Seletor de Stage, e `GITPR_AUTO_STAGE=true` faz o stage de tudo e segue em frente.

## Dicas úteis

Uma correção de bug, um refactor e um ajuste de config no mesmo `git diff`? O `gitpr split` agrupa os hunks por intenção e propõe um commit atômico por concern, cada um com uma mensagem escrita só para aquele subconjunto. O padrão é só leitura — ele imprime o plano e pergunta; `--dry-run` imprime e para, `--apply` faz o stage e os commits grupo a grupo, e `--max-groups 3` limita o plano. Arquivos não rastreados são nomeados num aviso, nunca descartados em silêncio.
