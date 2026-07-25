# 💬 Interactive Chat (TUI)

GitPR includes a full-featured interactive chat interface built with **Textual**, allowing you to have pair programming conversations with AI directly in your terminal.

## Features

- **Message history** — Your conversation is persisted per branch, so you can pick up where you left off.
- **Multi-line input** — Write long prompts with full keyboard navigation.
- **Slash commands** — Use `/explain`, `/tests`, `/optimize`, and `/clear` for quick actions.
- **Auto-patching (F5)** — Extract code blocks suggested by the AI into a patch file.
- **Diff refresh (F2)** — Reload `git diff` without restarting the session.
- **Session export (F6)** — Save the full chat history as Markdown.

## How to start

```bash
gitpr --chat          # Opens the interactive chat TUI
gitpr -c --chat       # Start with current diff loaded
```

👉 For a complete guide, see the [Chat Functionality Documentation](/docs/understanding_chat_functionality).

🔗 Repository: [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
