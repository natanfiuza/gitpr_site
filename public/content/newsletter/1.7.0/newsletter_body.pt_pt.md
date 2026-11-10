# GitPR 1.7.0 — Novidades

## Novidades desta versão

- **O dashboard de métricas ganhou gráficos ASCII e navegação por separadores:** o novo `src/ascii_chart.py` renderiza um histograma vertical de latência e uma série temporal de tokens com funções puras, sem novas dependências; a `MetricsApp` ganhou os separadores **Events** e **Charts & Trends**, com os atalhos `1`/`2` ([ADR-0018](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
- **O prompt tornou-se mais resistente a binários:** `collapse_binary_hunks()` passa a correr **também** no início de `generate_pr_content()`, e o catálogo de smart excludes ganhou `*.arrow`, `*.gguf`, `*.tflite` e `*.keras` — artefactos de ML/dados deixam de chegar à IA mesmo quando entram por um chamador externo.
- **A release da extensão foi desacoplada do resto da release:** o `extension.yml` ganhou o job `github-release` (`.vsix` anexado aos releases `ext-v*`) e o job não-bloqueante `publish-marketplaces`, que avalia `VSCE_PAT` e `OVSX_PAT` **independentemente** e reporta para `$GITHUB_STEP_SUMMARY`. A documentação de instalação do VSIX (CLI e GUI) entrou nos 5 idiomas.
- **O custo e a moeda deixaram de ser fixos:** o schema ganhou o tipo `KIND_FLOAT`, a chave `GITPR_METRICS_CURRENCY` e **oito** campos de preço `INPUT`/`OUTPUT` por modelo; o `DEFAULT_MODEL_PRICES` passou também a atribuir preço a `gemini-pro-latest` e `gemini-flash-lite-latest`.
- **As notas de migração foram formalizadas:** a quebra do `action.yml` para `gitpr check` ([ADR-0011](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0011-action-unified-check-migration.md)), o commit interativo sem TTY, o `GITPR_SKIP_UPDATE_CHECK` estrito e o seed/opt-out da configuração do projeto no CI passam a ter documentação explícita.
- **Três ADRs novas fecharam contratos pendentes:** [ADR-0017](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0017-documentation-site-language-fallback.md) (fallback de idioma do site), [ADR-0018](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) (gráficos/separadores) e [ADR-0019](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0019-project-configuration-seed-segregation.md) (segregação entre o auto-seed em runtime e o provisionamento explícito).
- **A 1.6.0 e a 1.7.0 foram publicadas:** o PyPI serve a **1.7.0**. O débito de release da janela anterior está fechado, e a `develop_natan` foi integrada na `main` pelo PR #310.

## Como usar

A 1.7.0 está publicada no PyPI. Atualize com:

```
pip install --upgrade gitpr-cli
```

O dashboard de métricas tem agora duas abas: `gitpr metrics dashboard` abre em **Events** e `2` alterna para **Charts & Trends**, onde o histograma vertical de latência e a série temporal de tokens são desenhados no terminal. `1` volta à tabela de eventos, `F5` atualiza e `Esc` sai.

Para reportar o custo na sua moeda, defina `GITPR_METRICS_CURRENCY` (por exemplo `EUR`) e preencha os campos de preço `INPUT`/`OUTPUT` por modelo; o dashboard e o `gitpr metrics` leem os mesmos valores.

Para instalar a extensão do editor, descarregue o `.vsix` em GitHub Releases (tag `ext-v*`) e instale no VS Code ou Cursor — sem necessidade de conta no marketplace.

## Dicas úteis

O `gitpr metrics dashboard` abre uma TUI local com custo, qualidade, módulos, providers e tempo de ciclo dos pull requests (F5 atualiza, Esc sai), e o `gitpr metrics export` grava em CSV e JSON apenas as linhas que nunca foram exportadas. Não há servidor: empacote uma janela com `gitpr metrics bundle` e junte os ficheiros da equipa com `gitpr metrics merge` para fechar o trimestre sem sair do seu disco.
