# GitPR 1.1.0 — Novidades

## Novidades desta versão

- **`gitpr config` — a TUI de configuração interativa:** Um ecrã master-detail sobre o `~/.gitpr/.env` com menu lateral de categorias, edição de campos inline, procura global (`/`), `F2` para guardar, `Ctrl+R` para restaurar e `Esc` para descartar. Um schema declarativo (`src/config_schema.py` — 12 categorias, 56 `ConfigField`, 8 avançados) é a única fonte de verdade: menu, widgets, predefinições e validação derivam todos dele, pelo que acrescentar uma definição passou a ser uma alteração de dados, não de interface.
- **Secção Skills dentro da TUI:** A primeira superfície do ecrã com âmbito de projeto edita os ficheiros `.gitpr/skill/*.md` do seu projeto atomicamente (preservando CRLF/LF) na mesma passagem de `F2` que escreve o `.env`, com um único contador de alterações pendentes.
- **Registo geral de utilização:** Uma linha por comando em `~/.gitpr/logs/<data>.log` — um ficheiro por dia, escrita síncrona, sem nunca imprimir e sem nunca lançar exceções. Responde a "o que é que eu realmente executei, e quando?". Controlado por `GITPR_SHOW_LOGS`.
- **Correção do idioma dos Git hooks:** O idioma que escolheu passou a ser efetivamente respeitado — `HOOK_SCRIPT_SUFFIXES` mapeia os códigos de interface (`es_es`, `fr_fr`) para os sufixos publicados (`.es`, `.fr`), e o `--lang` deixou de ser ignorado.
- **Distribuição exclusiva via PyPI com bloqueio obrigatório de atualização:** O canal binário acabou — sem geração, sem upload, sem fallback. O `enforce_update_required()` bloqueia a execução com código de saída 1 quando existe uma versão mais recente publicada e imprime o comando exato a executar.
- **i18n expandida para 955 chaves:** +213 chaves a cobrir a TUI de configuração e o bloqueio do PyPI, com paridade total de key sets nos 6 dicionários (`__lang_version__` v0.0.25).
- **2 famílias de documentação novas em 5 idiomas:** `config-tui` e `usage-log`, além de 7 tópicos atualizados (`ARCHITECTURE`, `auto-update`, `hooks-versioning`, `mcp-integration`, `skill-template`, `testar_sem_usar_pypi`, `version-markers`).
- **Chave morta removida:** A `PR_AUTO_PUBLISH` foi provada sem uso e retirada — se uma instalação antiga ainda tiver a linha, esta aparece apenas como leitura em *Desconhecidas*.
- **Versão 1.1.0:** O `__version__` passou de 1.0.0 para 1.1.0, e o `CHANGELOG.md` regista `[1.1.0] - 2026-09-13`, gerado pelo próprio `gitpr release`.

## Como usar

O GitPR 1.1.0 é distribuído exclusivamente via PyPI — o canal binário foi descontinuado:

```
pip install --upgrade gitpr-cli
```

Atenção: a partir desta versão, o GitPR consulta o PyPI em cada execução e **bloqueia a execução** se houver uma versão mais recente publicada, indicando exatamente o que executar. A verificação é colocada em cache por dia, o `--update` apenas reporta e o `GITPR_SKIP_UPDATE_CHECK` desliga o bloqueio para automação offline.

Configure tudo pela nova TUI — sem editar o `~/.gitpr/.env` à mão:

```
gitpr config            # ecrã interativo de configuração (F2 guarda)
```

Editar as instruções de IA do seu projeto faz parte do mesmo ecrã: a secção Skills escreve os `.gitpr/skill/*.md` na mesma passagem de gravação.

Quer saber o que realmente executou, e quando? Todos os comandos são anexados a um registo diário:

```
~/.gitpr/logs/<data>.log    # um ficheiro por dia; desligue com GITPR_SHOW_LOGS=false
```

Também entraram correções de idioma: o `--lang` aplica-se agora também aos seus Git hooks, pelo que os scripts de hook acompanham o idioma que escolheu.

## Dicas úteis

Equipa com idiomas mistos? O `gitpr --lang <código>` substitui o idioma de uma única execução: `gitpr -c --lang en`, `gitpr -r --lang pt_br`, `gitpr -ch --lang fr`. O GitPR fala 5 idiomas e deteta o locale do sistema automaticamente na primeira utilização — e, desde a 1.1.0, essa escolha chega também aos seus Git hooks.
