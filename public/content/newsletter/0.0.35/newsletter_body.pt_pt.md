# GitPR 0.0.35 — Novidades

## Novidades desta versão

- **Invocação Direta de Ferramentas MCP via CLI (`gitpr-mcp --tool`):** As 12 ferramentas MCP do GitPR podem agora ser invocadas diretamente da linha de comandos com `gitpr-mcp --tool <name> [--tool-args '<json>']`, sem iniciar o servidor stdio JSON-RPC. O modo `--tool` (sem nome) lista todas as ferramentas disponíveis com as respetivas assinaturas. Ideal para debugging, scripts e uso manual.
- **Tratamento de Erros no Merge do PR:** O PR Publisher (TUI Textual) apresenta agora um ecrã modal de erro visível quando o merge do PR falha — especialmente HTTP 405 a indicar conflitos. Anteriormente, a falha era ignorada silenciosamente e o fluxo continuava como se tudo tivesse funcionado.
- **Novos Documentos MCP:** 3 novos tópicos de documentação MCP em 5 idiomas: `mcp-annotations.md` (anotações de ferramentas), `mcp-integration.md` (guia de integração), `mcp-prompts.md` (guia de prompts com template).

## Como usar

Instale ou atualize via PyPI:

```
pip install gitpr-cli
```

Ou descarregue o binário standalone em [GitHub Releases](https://github.com/natanfiuza/gitpr/releases).

Teste a novidade já:

```
gitpr-mcp --tool               # lista as 12 ferramentas disponíveis com as assinaturas
gitpr-mcp --tool analyze_diff  # invoca uma ferramenta diretamente, sem o servidor MCP
```

## Dicas úteis

Um único comando configura o servidor MCP do GitPR no seu editor: `gitpr-mcp --install auto` deteta VSCode, Cursor, Claude Code, Claude Desktop ou Zed e grava o ficheiro de config certo. É idempotente e faz merge com as configurações existentes, sem sobrescrever outros servidores MCP.
