# GitPR 0.0.37 — Novidades

## Novidades desta versão

- **Bridge de Linters Externos + Assistente `--linter-setup`:** Integração com linters maduros (ESLint, PHP_CodeSniffer, Stylelint) executados apenas nas linhas alteradas do diff, com parser de saída Checkstyle XML, nova TUI de erros (`LinterApp`) e relatório Markdown consolidado em `.gitpr/reports/linter/`. O assistente interativo configura tudo com presets remotos (`templates/gitpr.linter-presets.json`) versionados pelo marcador `LINTER_PRESETS_VERSION`.
- **i18n Reparada e Completa:** Reparadas 51 chaves corrompidas + 36 chaves com `\n` literal em todos os 6 dicionários; auditoria AST de 638 chaves com 0 não traduzidas e 0 mangled; paridade total de 547 chaves idênticas por arquivo; `__lang_version__` v0.0.13 → v0.0.20 com testes de guarda.
- **Trailer de Coautoria:** Todo commit gerado por IA recebe `Co-Authored-By: Gitpr-cli <gitpr@natanfiuza.dev.br>` — idempotente (nunca duplica, preserva trailers de terceiros), oculto da TUI e com opt-out `GITPR_COAUTHOR=false`.
- **Fix do Hang do MCP Server:** Os 12 tool handlers eram síncronos e rodavam inline no event loop — qualquer chamada bloqueante travava o servidor stdio. Novo decorator `_offload` (anyio worker threads), warm-import no startup, `stdin=subprocess.DEVNULL` em todos os subprocessos e timeout duro de 10s no download de smart-excludes. Testes e2e novos com JSON-RPC stdio real.
- **Correções do Modal de Erro do Linter:** Botões "Commit with --no-verify" e "Abort" lado a lado (antes empilhados e sobrepostos); a escolha no-verify agora retoma o fluxo de commit; push do modal adiado via `call_next` para o message pump do app.
- **Dead Code Removido + Ajustes MCP:** Classe morta `FileStageScreen` removida; `claude-code` listado no help do `gitpr-mcp --install`; alias oculto `gitpr --mcp` documentado.
- **Documentação Multilíngue Expandida:** `docs/ARCHITECTURE.md` reescrito em EN canônico + 4 locales criados (18 tópicos de arquitetura); novo tópico `i18n_explanation` em 5 idiomas; READMEs e 4 tópicos atualizados.
- **Formatação Consistente do Codebase:** Refactor Black-style em todo o `src/` (aspas duplas, trailing commas, quebras de linha) — sem mudança funcional.
- **Skills Locais do Claude Code:** Adicionadas as skills `status-report` (geração do relatório de status), `implement-fixes` (workflow de correções) e `caveman-commit` (mensagens de commit compactas).

## Como usar

Atualize via PyPI:

```
pip install --upgrade gitpr-cli
```

Ou baixe o binário standalone em [GitHub Releases](https://github.com/natanfiuza/gitpr/releases).

Experimente a nova bridge de linters externos:

```
gitpr --linter-setup   # assistente interativo: ESLint, PHPCS, Stylelint
gitpr --linter         # regras regex + linters externos, relatório em .gitpr/reports/linter/
```

Seus commits agora ganham o trailer de coautoria automaticamente — desative com `GITPR_COAUTHOR=false`.

## Dicas úteis

Execute de novo qualquer comando de IA sem alterar o código e o GitPR responde em milissegundos: as respostas ficam em cache em `~/.gitpr/cache/prompts/`, indexadas por um hash MD5 do seu diff + instruções — repetir um comando não gasta nada da sua cota de API.
