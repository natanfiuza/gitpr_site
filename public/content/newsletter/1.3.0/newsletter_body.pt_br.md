# GitPR 1.3.0 — Novidades

## Novidades desta versão

- **`gitpr demo` — a primeira execução deixou de ser um ato de fé:** Um tour guiado que mostra mensagem de commit, review e descrição de PR **sem chave de API, sem repositório git e sem rede**. A pergunta que ele responde ("o que essa ferramenta faz?") só tem uma janela para ser feita — o primeiro uso, antes de o usuário ter configurado um provedor. O tour roda pelo **pipeline real de geração** com a fonte da resposta trocada, então o que aparece é o que a ferramenta de fato produz, enquanto todo efeito externo (cache, métricas, disco, rede, leitura de chave) é neutralizado.
- **Selo GitPR no corpo do PR + comando `gitpr badge`:** Um PR escrito por IA era indistinguível de um escrito à mão, e o resultado do linter morria num terminal que rolou para fora da tela. O selo transforma esse sinal privado numa afirmação visível no próprio PR publicado — e é deliberadamente uma URL estática do shields.io, que o GitPR **nunca** busca, para que publicar um PR não passe a depender de um terceiro estar no ar. Medição honesta: sem regras de linter configuradas o selo **não** é emitido, porque lista vazia significa "nada foi verificado", não "nada foi encontrado".
- **`gitpr split` — uma árvore de trabalho com várias intenções deixa de virar um commit-blob:** Lê o diff não commitado, pede à IA que particione os hunks por intenção lógica e propõe **um commit atômico por concern**, cada um com mensagem gerada a partir do patch daquele grupo isolado. A árvore nunca é reescrita: os arquivos terminam byte a byte iguais ao início — só o histórico muda. *(O índice, sim: o `--apply` reseta para HEAD antes de stagear.)*
- **Varredura de segredos embutida — a regra que não pode ser sobrescrita:** Sete regras (`src/security_ruleset.py`) que rodam em **toda** invocação do linter: cinco `error` bloqueantes (AWS key ID, token GitHub/Slack, chave Google, bloco de chave privada) e dois `warning`. O catálogo local era inteiramente gerenciado pelo usuário — podia ser sobrescrito pelo download, reescrito pelo wizard (perdendo comentários) ou estendido só por plugins da máquina. Um portão de segredos precisa se comportar igual em toda máquina, então as regras passaram a viver **dentro do pacote**. **Mudança de comportamento: um commit que passava pode agora ser bloqueado.**
- **Suporte a `extensions: ["*"]`:** Agora significa *todo* arquivo, incluindo os sem sufixo — que é exatamente de onde segredos vazam (`id_rsa`, `.env`, `credentials`, `Dockerfile`). Uma regra com `extensions: ["py"]` mantém o filtro exatamente como antes.
- **Bridges SAST opt-in — Semgrep, Gitleaks e Bandit:** Uma camada que pluga scanners de segurança de terceiros no linter existente. Rodam **só** quando habilitados, **só** sobre os arquivos tocados pelo diff, e os achados são deduplicados contra o ruleset interno: um segredo visto pelos dois aparece **uma vez** com marcador de confirmação multi-fonte (`[Gitleaks + Regex]`). O Gitleaks tem os valores mascarados (`AKIA****`) antes de virar achado.
- **`gitpr tests generate` — a suíte que respeita a convenção do repositório:** Gera arquivos de teste completos a partir do diff, de um arquivo específico ou de um achado de review. Detecta o framework em uso (Pest, PHPUnit, Jest, Vitest, Pytest) em vez de impor um estilo, calcula o caminho convencional de destino (o split `Feature`/`Unit` do Laravel, o `tests/**/test_*.py` do Pytest) e valida a sintaxe com o toolchain local (`php -l`, `node --check`, `python -m py_compile`) — falha de validação vira aviso, não erro. Dry run é o default.
- **`gitpr explain` + flag `--explain` — o guia de quem vai revisar:** Um guia centrado no revisor (o que muda, por que muda, onde focar, qual o risco de regressão) para que ninguém tenha que reconstruir a intenção a partir de um diff cru. Disponível como subcomando próprio e como flag que anexa a seção à descrição do PR gerada.
- **Arquitetura em camadas — `src/domain/` e `src/application/`:** As duas últimas features (`tests` e `explain`) nasceram com separação explícita entre regra de domínio pura e orquestração de caso de uso, e o CLI e a TUI de chat passaram a compartilhar **o mesmo** caso de uso em vez de duplicá-lo.
- **Suíte determinística e o primeiro CI:** `tests/conftest.py` ficou hermético (pina `GITPR_LANG=en_us` e desliga o ruleset de segredos) e o `.github/workflows/tests.yml` roda a suíte em **Python 3.10** (o piso declarado, nunca exercitado) e 3.13. Foi o CI que tornou visível o drift de locale — **22 testes** falhavam numa máquina pt-BR por asserir o literal em inglês.
- **As 3 falhas herdadas de três relatórios seguidos foram fechadas:** os dois testes de timeout desatualizados (`600s` vs. o default real de `180s`) e o teste sensível ao locale. A linha de base da suíte deixou de ser "3 falhas conhecidas" e passou a ser **verde por construção**.
- **Dívida nova e concentrada:** as duas features mais recentes (`tests` e `explain`) entraram com o registro de skills **pela metade** — **40 chaves `__()`** usadas no código não existem em nenhum dos 6 dicionários, e os registries de rótulos do config e do MCP não receberam os tipos novos. São **4 falhas** na suíte completa, todas com a mesma causa raiz.
- **Estado do release 1.3.0:** o `__version__` e o `__lang_version__` estão no **working tree e não commitados** (HEAD segue em 1.2.0 / v0.0.31), o `CHANGELOG.md` **tem** a entrada `[1.3.0] - 2026-09-21` — mas ela cobre **apenas** a varredura de segredos, e não `demo`, `badge`, `split`, SAST, `tests` nem `explain`. A tag `v1.2.0` **foi criada** (merge do PR #174), fechando o item que bloqueava a janela anterior.

## Como usar

Atualize pelo PyPI:

```
pip install --upgrade gitpr-cli
```

Experimente antes de configurar qualquer coisa — o tour não precisa de chave de API, de repositório git nem de rede:

```
gitpr demo                             # tour guiado por uma mensagem de commit, um review e um PR
gitpr demo --lang=pt_br                # o tour no seu idioma
gitpr demo --no-tui                    # texto puro, para CI e gravações
```

Os subcomandos novos — todos eles são somente leitura até você passar a opção que escreve:

```
gitpr badge                            # imprime o trecho do selo para o seu README (não escreve nada)
gitpr split                            # o plano: um commit atômico por concern (não escreve nada)
gitpr split --apply                    # commita cada grupo; a árvore termina byte a byte igual
gitpr tests generate                   # um arquivo de teste na convenção do seu repositório (não escreve nada)
gitpr tests generate --file src/core.py --apply
gitpr explain                          # um guia para quem vai revisar o diff atual
gitpr --explain                        # anexa esse guia à descrição do PR gerada
```

Um `gitpr split` sem flags pergunta antes de commitar qualquer coisa — e o `--apply` reseta o índice para HEAD, então o que você já tinha stageado precisa ser staged de novo (o conteúdo dos arquivos nunca é tocado). O `gitpr tests generate` não sobrescreve nada sem `--apply` e, quando sobrescreve, a confirmação abre com **Não** pré-selecionado.

A varredura de segredos agora roda em **toda** invocação do linter, com regras que vivem dentro do pacote e não podem ser substituídas pelo `--skill` nem reescritas pelo wizard — então um commit que passava pode ser bloqueado. O `GITPR_LINTER_SECURITY=false` é a saída de emergência. Os bridges Semgrep/Gitleaks/Bandit continuam estritamente opt-in (`GITPR_SAST_*_ENABLED`), e uma ferramenta habilitada mas ausente do `PATH` vira aviso, não falha.

## Dicas úteis

O linter é de graça: sem API key, sem chamadas de IA, só as linhas adicionadas no seu diff. Exit code 0 = passou, 1 = violações, então ele é ideal como quality gate no GitHub Actions que bloqueia segredos e código de debug antes da revisão humana — a documentação inclui o workflow completo.
