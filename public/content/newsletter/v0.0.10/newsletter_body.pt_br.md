# GitPR v0.0.10 — Novidades

## Novidades desta versão

- **Invocação Direta de MCP Tools via CLI (`gitpr-mcp --tool`):** As 12 tools MCP do GitPR agora podem ser invocadas diretamente da linha de comando com `gitpr-mcp --tool <name> [--tool-args '<json>']`, sem necessidade de iniciar o servidor stdio JSON-RPC. O modo `--tool` (sem nome) lista todas as tools disponíveis com suas assinaturas. Ideal para debug, scripts e uso manual.
- **Tratamento de Erro em Merge de PR:** O PR Publisher (Textual TUI) agora exibe um modal de erro visível quando o merge da PR falha — especialmente HTTP 405 indicando conflitos. Antes, a falha era ignorada silenciosamente e o fluxo prosseguia como se tudo tivesse dado certo.
- **Novos Documentos MCP:** 3 novos tópicos de documentação MCP em 5 idiomas: `mcp-annotations.md` (anotações das tools), `mcp-integration.md` (guia de integração), `mcp-prompts.md` (guia de prompts templatizados).

## Como usar

Instale ou atualize via PyPI:

```
pip install gitpr-cli
```

Ou baixe o binário standalone em [GitHub Releases](https://github.com/natanfiuza/gitpr/releases).

Teste a novidade agora:

```
gitpr-mcp --tool               # lista as 12 tools disponíveis com assinaturas
gitpr-mcp --tool analyze_diff  # invoca uma tool diretamente, sem o servidor MCP
```

## Dicas úteis

Um único comando configura o servidor MCP do GitPR no seu editor: `gitpr-mcp --install auto` detecta VSCode, Cursor, Claude Code, Claude Desktop ou Zed e grava o arquivo de config certo. É idempotente e faz merge com as configurações existentes, sem sobrescrever outros servidores MCP.
