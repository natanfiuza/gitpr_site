# GitPR 1.1.0 — Novidades

## Novidades desta versão

- **`gitpr config` — a TUI interativa de configuração:** Uma tela master-detail sobre o `~/.gitpr/.env` com menu lateral de categorias, edição de campos inline, busca global (`/`), `F2` para salvar, `Ctrl+R` para restaurar e `Esc` para descartar. Um schema declarativo (`src/config_schema.py` — 12 categorias, 56 `ConfigField`, 8 avançados) é a fonte única de verdade: menu, widgets, defaults e validação derivam todos dele, então adicionar uma configuração virou mudança de dado, não de interface.
- **Seção Skills dentro da TUI:** A primeira superfície project-scoped da tela edita os arquivos `.gitpr/skill/*.md` do seu projeto atomicamente (preservando CRLF/LF) na mesma passada de `F2` que grava o `.env`, com um contador único de pendências.
- **Log geral de uso:** Uma linha por comando em `~/.gitpr/logs/<data>.log` — um arquivo por dia, escrita síncrona, sem nunca imprimir e sem nunca levantar exceção. Responde "o que eu realmente rodei, e quando?". Controlado por `GITPR_SHOW_LOGS`.
- **Correção do idioma dos hooks:** O idioma que você escolheu agora vale de verdade — `HOOK_SCRIPT_SUFFIXES` mapeia códigos de interface (`es_es`, `fr_fr`) para os sufixos publicados (`.es`, `.fr`), e `--lang` deixou de ser ignorado.
- **Distribuição exclusiva via PyPI com portão de atualização obrigatória:** O canal binário acabou — sem geração, sem upload, sem fallback. O `enforce_update_required()` bloqueia a execução com exit code 1 quando há versão mais nova publicada e imprime o comando exato a rodar.
- **i18n expandida para 955 chaves:** +213 chaves cobrindo a TUI de configuração e o portão do PyPI, com paridade total de key sets nos 6 dicionários (`__lang_version__` v0.0.25).
- **2 famílias de documentação novas em 5 idiomas:** `config-tui` e `usage-log`, além de 7 tópicos atualizados (`ARCHITECTURE`, `auto-update`, `hooks-versioning`, `mcp-integration`, `skill-template`, `testar_sem_usar_pypi`, `version-markers`).
- **Chave morta removida:** A `PR_AUTO_PUBLISH` foi provada sem uso e retirada — se uma instalação antiga ainda carrega a linha, ela aparece apenas como leitura em *Desconhecidas*.
- **Versão 1.1.0:** O `__version__` foi de 1.0.0 para 1.1.0, e o `CHANGELOG.md` registra `[1.1.0] - 2026-09-13`, gerado pelo próprio `gitpr release`.

## Como usar

O GitPR 1.1.0 é distribuído exclusivamente via PyPI — o canal binário foi aposentado:

```
pip install --upgrade gitpr-cli
```

Atenção: a partir desta versão, o GitPR consulta o PyPI a cada execução e **bloqueia a execução** se houver versão mais nova publicada, dizendo exatamente o que rodar. A checagem é cacheada por dia, o `--update` apenas reporta e o `GITPR_SKIP_UPDATE_CHECK` desliga o portão para automação offline.

Configure tudo pela nova TUI — sem editar o `~/.gitpr/.env` na mão:

```
gitpr config            # tela interativa de configuração (F2 salva)
```

Editar as instruções de IA do seu projeto faz parte da mesma tela: a seção Skills grava os `.gitpr/skill/*.md` na mesma passada de salvamento.

Quer saber o que você realmente rodou, e quando? Todo comando é anexado a um log diário:

```
~/.gitpr/logs/<data>.log    # um arquivo por dia; desligue com GITPR_SHOW_LOGS=false
```

Também entraram correções de idioma: o `--lang` agora vale também para os seus Git hooks, então os scripts de hook acompanham o idioma que você escolheu.

## Dicas úteis

Time com idiomas mistos? O `gitpr --lang <código>` sobrescreve o idioma de uma única execução: `gitpr -c --lang en`, `gitpr -r --lang pt_br`, `gitpr -ch --lang fr`. O GitPR fala 5 idiomas e detecta o locale do sistema automaticamente no primeiro uso — e, desde a 1.1.0, essa escolha também chega aos seus Git hooks.
