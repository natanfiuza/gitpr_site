# GitPR 1.3.0 — Novidades

## Novidades desta versão

- **`gitpr demo` — a primeira execução deixou de ser um ato de fé:** Uma visita guiada que mostra uma mensagem de commit, um review e uma descrição de PR **sem chave de API, sem repositório git e sem rede**. A pergunta a que responde ("o que é que esta ferramenta faz?") só tem uma janela para ser feita — o primeiro uso, antes de o utilizador ter configurado um fornecedor. A visita corre pelo **pipeline real de geração** com a origem da resposta trocada, pelo que o que aparece é o que a ferramenta produz de facto, enquanto todos os efeitos externos (cache, métricas, disco, rede, leitura de chaves) são neutralizados.
- **Selo GitPR no corpo do PR + comando `gitpr badge`:** Um PR escrito por IA era indistinguível de um escrito à mão, e o resultado do linter morria num terminal que já tinha rolado fora do ecrã. O selo transforma esse sinal privado numa afirmação visível no próprio PR publicado — e é deliberadamente um URL estático do shields.io, que o GitPR **nunca** descarrega, para que publicar um PR não passe a depender de um terceiro estar no ar. Medição honesta: sem regras de linter configuradas o selo **não** é emitido, porque uma lista vazia significa "nada foi verificado", não "nada foi encontrado".
- **`gitpr split` — uma árvore de trabalho com várias intenções deixa de virar um commit-blob:** Lê o diff não commitado, pede à IA que agrupe os hunks por intenção lógica e propõe **um commit atómico por preocupação**, cada um com uma mensagem gerada a partir do patch desse grupo isolado. A árvore nunca é reescrita: os ficheiros terminam byte a byte iguais ao início — só o histórico muda. *(O índice, sim: o `--apply` faz reset para HEAD antes de preparar o staging.)*
- **Deteção de segredos embutida — a regra que não pode ser substituída:** Sete regras (`src/security_ruleset.py`) que correm em **todas** as invocações do linter: cinco `error` bloqueantes (AWS key ID, token GitHub/Slack, chave Google, bloco de chave privada) e dois `warning`. O catálogo local era inteiramente gerido pelo utilizador — podia ser sobreposto pelo descarregamento, reescrito pelo assistente (perdendo comentários) ou alargado apenas por plugins da máquina. Uma barreira de segredos tem de se comportar da mesma forma em todas as máquinas, pelo que as regras passaram a viver **dentro do pacote**. **Alteração de comportamento: um commit que antes passava pode agora ser bloqueado.**
- **Suporte a `extensions: ["*"]`:** Passa a significar *todos* os ficheiros, incluindo os que não têm sufixo — que é exatamente de onde os segredos vazam (`id_rsa`, `.env`, `credentials`, `Dockerfile`). Uma regra com `extensions: ["py"]` mantém o filtro exatamente como antes.
- **Bridges SAST opt-in — Semgrep, Gitleaks e Bandit:** Uma camada que liga scanners de segurança de terceiros ao linter existente. Correm **só** quando estão ligados, **só** sobre os ficheiros tocados pelo diff, e os apontamentos são deduplicados contra o conjunto de regras interno: um segredo visto pelos dois aparece **uma vez** com um marcador de confirmação multi-fonte (`[Gitleaks + Regex]`). O Gitleaks tem os valores mascarados (`AKIA****`) antes de se tornar apontamento.
- **`gitpr tests generate` — a suíte que respeita a convenção do repositório:** Gera ficheiros de teste completos a partir do diff, de um ficheiro específico ou de um apontamento de review. Deteta o framework em uso (Pest, PHPUnit, Jest, Vitest, Pytest) em vez de impor um estilo, calcula o caminho convencional de destino (a separação `Feature`/`Unit` do Laravel, o `tests/**/test_*.py` do Pytest) e valida a sintaxe com o toolchain local (`php -l`, `node --check`, `python -m py_compile`) — uma falha de validação torna-se um aviso, não um erro. O dry-run é a predefinição.
- **`gitpr explain` + a flag `--explain` — o guia de quem vai revisar:** Um guia centrado em quem vai **revisar** (o que muda, porque muda, onde focar, qual é o risco de regressão) para que ninguém tenha de reconstruir a intenção a partir de um diff em bruto. Disponível como subcomando próprio e como flag que anexa a secção à descrição de PR gerada.
- **Arquitetura em camadas — `src/domain/` e `src/application/`:** As duas funcionalidades mais recentes (`tests` e `explain`) nasceram com uma separação explícita entre regras de domínio puras e orquestração de casos de uso, e a CLI e a TUI de chat passaram a partilhar **o mesmo** caso de uso em vez de o duplicarem.
- **Suíte determinística e o primeiro CI:** O `tests/conftest.py` tornou-se hermético (fixa `GITPR_LANG=en_us` e desliga o conjunto de regras de segredos) e o `.github/workflows/tests.yml` corre a suíte em **Python 3.10** (o piso declarado, nunca exercitado) e 3.13. Foi o CI que tornou visível o desvio de locale — **22 testes** falhavam numa máquina pt-BR por afirmarem o literal em inglês.
- **As 3 falhas herdadas de três relatórios seguidos foram fechadas:** os dois testes de timeout desatualizados (`600s` vs. a predefinição real de `180s`) e o teste sensível ao locale. A linha de base da suíte deixou de ser "3 falhas conhecidas" e passou a ser **verde por construção**.
- **Dívida nova e concentrada:** as duas funcionalidades mais recentes (`tests` e `explain`) chegaram com o registo de skills **a meio** — **40 chaves `__()`** usadas no código não existem em nenhum dos 6 dicionários, e os registos de rótulos da config e do MCP não receberam os tipos novos. São **4 falhas** na suíte completa, todas com a mesma causa raiz.
- **Estado do release 1.3.0:** o `__version__` e o `__lang_version__` estão **na árvore de trabalho e não commitados** (o HEAD continua em 1.2.0 / v0.0.31), o `CHANGELOG.md` **tem** a entrada `[1.3.0] - 2026-09-21` — mas cobre **apenas** a deteção de segredos, e não `demo`, `badge`, `split`, SAST, `tests` nem `explain`. A tag `v1.2.0` **foi criada** (merge do PR #174), fechando o item que bloqueava a janela anterior.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

Experimente antes de configurar seja o que for — a visita não precisa de chave de API, de repositório git nem de rede:

```
gitpr demo                             # visita guiada por uma mensagem de commit, um review e um PR
gitpr demo --lang=pt_pt                # a visita no seu idioma
gitpr demo --no-tui                    # texto simples, para CI e gravações
```

Os novos subcomandos — todos são apenas de leitura até passar a opção que escreve:

```
gitpr badge                            # imprime o excerto do selo para o seu README (não escreve nada)
gitpr split                            # o plano: um commit atómico por preocupação (não escreve nada)
gitpr split --apply                    # faz commit de cada grupo; a árvore termina byte a byte igual
gitpr tests generate                   # um ficheiro de teste na convenção do seu repositório (não escreve nada)
gitpr tests generate --file src/core.py --apply
gitpr explain                          # um guia para quem vai revisar o diff atual
gitpr --explain                        # anexa esse guia à descrição do PR gerada
```

Um `gitpr split` sem opções pergunta antes de fazer commit de seja o que for — e o `--apply` faz reset do índice para HEAD, pelo que o que já tinha em staging tem de ser preparado de novo (o conteúdo dos ficheiros nunca é tocado). O `gitpr tests generate` não sobrepõe nada sem `--apply` e, quando sobrepõe, a confirmação abre com **Não** pré-selecionado.

A deteção de segredos corre agora em **todas** as invocações do linter, com regras que vivem dentro do pacote e não podem ser substituídas pelo `--skill` nem reescritas pelo assistente — por isso um commit que passava pode agora ser bloqueado. O `GITPR_LINTER_SECURITY=false` é a saída de emergência. As bridges Semgrep/Gitleaks/Bandit continuam estritamente opt-in (`GITPR_SAST_*_ENABLED`), e uma ferramenta ligada mas ausente do `PATH` gera um aviso, não uma falha.

## Dicas úteis

O linter é gratuito: sem API key, sem chamadas de IA, apenas as linhas adicionadas no seu diff. Exit code 0 = passou, 1 = violações, pelo que é ideal como quality gate no GitHub Actions que bloqueia segredos e código de debug antes da revisão humana — a documentação inclui o workflow completo.
