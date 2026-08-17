# GitPR 0.0.35 — What's New

## What's New in This Version

- **Direct Invocation of MCP Tools via CLI (`gitpr-mcp --tool`):** The 12 GitPR MCP tools can now be invoked directly from the command line with `gitpr-mcp --tool <name> [--tool-args '<json>']`, without starting the stdio JSON-RPC server. The `--tool` mode (without a name) lists all available tools with their signatures. Ideal for debugging, scripts, and manual use.
- **Error Handling in PR Merge:** The PR Publisher (Textual TUI) now displays a visible error modal when the PR merge fails — especially HTTP 405 indicating conflicts. Previously, the failure was silently ignored and the flow continued as if everything had worked.
- **New MCP Documents:** 3 new MCP documentation topics in 5 languages: `mcp-annotations.md` (tool annotations), `mcp-integration.md` (integration guide), `mcp-prompts.md` (templated prompts guide).

## How to Use

Install or update via PyPI:

```
pip install gitpr-cli
```

Or download the standalone binary from [GitHub Releases](https://github.com/natanfiuza/gitpr/releases).

Try the new feature right away:

```
gitpr-mcp --tool               # lists the 12 available tools with signatures
gitpr-mcp --tool analyze_diff  # invokes a tool directly, without the MCP server
```

## Useful Tips

One command configures GitPR's MCP server in your editor: `gitpr-mcp --install auto` detects VSCode, Cursor, Claude Code, Claude Desktop or Zed and writes the right config file. It's idempotent and merges with existing settings, never overwriting other MCP servers.
