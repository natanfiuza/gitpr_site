# GitPR 1.7.0 — Novidades

## Novidades desta versão

- **O dashboard de métricas ganhou gráficos ASCII e navegação por abas:** o novo `src/ascii_chart.py` renderiza histograma vertical de latência e série temporal de tokens com funções puras, sem dependências novas; a `MetricsApp` passou a ter as abas **Events** e **Charts & Trends**, com atalhos `1`/`2` ([ADR-0018](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
- **O prompt ficou mais resistente a binários:** `collapse_binary_hunks()` passou a rodar **também** no início de `generate_pr_content()`, e o catálogo de smart excludes ganhou `*.arrow`, `*.gguf`, `*.tflite` e `*.keras` — artefatos de ML/dados deixam de chegar à IA mesmo quando entram por um chamador externo.
- **O release da extensão foi desacoplado do resto da release:** o `extension.yml` ganhou o job `github-release` (`.vsix` anexado a releases `ext-v*`) e o job não-bloqueante `publish-marketplaces`, que avalia `VSCE_PAT` e `OVSX_PAT` **independentemente** e reporta no `$GITHUB_STEP_SUMMARY`. A documentação de instalação por VSIX (CLI e GUI) entrou nos 5 idiomas.
- **Custo e moeda deixaram de ser fixos:** o schema ganhou o tipo `KIND_FLOAT`, a chave `GITPR_METRICS_CURRENCY` e **oito** campos de preço `INPUT`/`OUTPUT` por modelo; `DEFAULT_MODEL_PRICES` passou a precificar também `gemini-pro-latest` e `gemini-flash-lite-latest`.
- **As notas de migração foram formalizadas:** a quebra do `action.yml` para `gitpr check` ([ADR-0011](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0011-action-unified-check-migration.md)), o commit interativo sem TTY, o `GITPR_SKIP_UPDATE_CHECK` estrito e o seed de configuração do projeto/opt-out em CI agora têm documentação explícita.
- **Três ADRs novas fecharam contratos pendentes:** [ADR-0017](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0017-documentation-site-language-fallback.md) (fallback de idioma no site), [ADR-0018](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) (gráficos/abas) e [ADR-0019](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0019-project-configuration-seed-segregation.md) (segregação entre auto-seed de runtime e provisionamento explícito).
- **A 1.6.0 e a 1.7.0 foram publicadas:** o PyPI serve **1.7.0**. O débito de release da janela anterior fechou, e `develop_natan` foi mergeada em `main` pelo PR #310.

## Como usar

A 1.7.0 está publicada no PyPI. Atualize com:

```
pip install --upgrade gitpr-cli
```

O dashboard de métricas agora tem duas abas: `gitpr metrics dashboard` abre em **Events** e `2` alterna para **Charts & Trends**, onde o histograma vertical de latência e a série temporal de tokens são desenhados no terminal. `1` volta para a tabela de eventos, `F5` atualiza e `Esc` sai.

Para reportar custo na sua moeda, defina `GITPR_METRICS_CURRENCY` (por exemplo `BRL`) e preencha os campos de preço `INPUT`/`OUTPUT` por modelo; o dashboard e o `gitpr metrics` leem os mesmos valores.

Para instalar a extensão do editor, baixe o `.vsix` em GitHub Releases (tag `ext-v*`) e instale no VS Code ou Cursor — sem precisar de conta no marketplace.

## Dicas úteis

O `gitpr metrics dashboard` abre uma TUI local com custo, qualidade, módulos, providers e tempo de ciclo dos pull requests (F5 atualiza, Esc sai), e o `gitpr metrics export` grava em CSV e JSON só as linhas que nunca foram exportadas. Não há servidor: empacote uma janela com `gitpr metrics bundle` e junte os arquivos do time com `gitpr metrics merge` para fechar o trimestre sem sair do seu disco.
