# GitPR 1.4.4 — Novidades

## Novidades desta versão

- **O `gitpr -c` passou a ser uma TUI de 4 passos:** fazer commit deixou de ser um pipeline silencioso. O `CommitApp` abre o **Seletor de Stage** para os ficheiros fora do índice, gera a mensagem numa thread de background sobre o **diff em stage**, mostra o **modal de revisão/edição** e executa o commit num modal animado de progresso — dá para escolher ficheiros, editar o texto ou cancelar antes de algo chegar ao repositório.
- **A mensagem passou a descrever o que foi de facto indexado:** `get_git_staged_diff()` corre `git diff --cached` com as mesmas proteções do diff normal (`-U1 -w -M -B`, smart excludes, UTF-8), caindo no diff da working tree só quando o índice está vazio. O que não está em stage não entra no prompt.
- **O modal de commit fecha-se sozinho no sucesso:** `mark_finished(False)` + `set_timer(0.6, self.exit)` devolvem o terminal, e a CLI imprime `committed`/`failed` com a saída do git. Em erro, o botão **Close** mantém-se, porque é onde o log da falha fica legível.
- **A 1.4.3 e a 1.4.4 estão ambas no PyPI:** a correção do portão de atualização — cache deslizante de 1 hora, comparação PEP 440, portão a alcançar subcomandos e `GITPR_SKIP_UPDATE_CHECK` como booleano estrito — entrou na 1.4.3 e foi publicada na mesma janela. `pip install --upgrade gitpr-cli` instala a 1.4.4.
- **Quebra declarada:** fora do `--hook`, o `gitpr -c` **abre sempre** a TUI — não há verificação de TTY no encaminhamento. Scripts e jobs de CI que dependiam do commit silencioso têm de migrar para `--hook`.
- **A dívida de i18n aumentou, não foi paga:** os dicionários continuam com **1620 chaves** (×4) e **1618** (`es.json`, `fr.json`), mas o código já usa **1629** — as 3 strings novas da TUI de commit entraram sem tradução. São **11 chaves sem dicionário** e **2 órfãs**, e o `__lang_version__` subiu para **v0.0.36** sem nenhum ficheiro de idioma mudar.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

Corra `gitpr -c` (ou `gitpr --commit`) em qualquer repositório. Se tudo já estiver em stage, o Seletor de Stage é saltado; caso contrário, lista os ficheiros fora do índice com **Select All**, **Deselect All**, **Stage Selected**, **Skip Staging** e **Cancel**. Reveja a mensagem gerada, edite se quiser — o texto editado é o que o git faz commit — e confirme. O modal de progresso fecha-se sozinho quando o commit corre bem.

Para automação, o `--hook` mantém o caminho silencioso e merge-aware de antes, por isso use-o em hooks de commit e CI. `GITPR_SKIP_UNSTAGED_CHECK=true` (ou `--no-unstaged-check`) salta apenas o Seletor de Stage, e `GITPR_AUTO_STAGE=true` faz o stage de tudo e segue em frente.

## Dicas úteis

Uma correção de bug, um refactor e um ajuste de config no mesmo `git diff`? O `gitpr split` agrupa os hunks por intenção e propõe um commit atómico por concern, cada um com uma mensagem escrita apenas para aquele subconjunto. O padrão é só leitura — imprime o plano e pergunta; `--dry-run` imprime e para, `--apply` faz o stage e os commits grupo a grupo, e `--max-groups 3` limita o plano. Ficheiros não rastreados são nomeados num aviso, nunca descartados em silêncio.
