# CLAUDE.md

## Project: GitPR Site — Documentation Website for GitPR CLI

This is the **official documentation/presentation website** for [GitPR CLI](https://github.com/natafiuza/gitpr), an AI-powered CLI tool that automates Git workflows using Google Gemini and DeepSeek. The site is built with **Laravel**, **Inertia.js**, **Vue 3**, and **Tailwind CSS**.

### What GitPR CLI Does (the tool this site documents)

GitPR is a Python CLI that acts as an intelligent local assistant for developers:
- **AI Code Reviews** — automated review of staged/committed changes
- **PR Generation** — auto-generates pull request descriptions using LLMs
- **Semantic Commit Messages** — generates conventional commit messages from diffs
- **Static Linter** — offline YAML-configurable linting of changed lines (no AI quota cost)
- **Tech Debt Auditing** — identifies code smells and anti-patterns
- **Interactive Chat/TUI** — chat interface with AI about code, with patch extraction (F5)
- **Auto-Update** — hot-swap binary updates from GitHub Releases with rollback
- **Map-Reduce Architecture** — splits large diffs into chunks for LLM token limits
- **i18n** — full PT-BR and EN translations

### Tech Stack (this website)

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13.x (PHP ^8.3) |
| Frontend | Vue 3 + Inertia.js 2.x |
| Styling | Tailwind CSS 3.x + @tailwindcss/forms + @tailwindcss/typography |
| Build | Vite 8.x + laravel-vite-plugin |
| Auth | Laravel Breeze 2.x (Inertia stack) |
| Testing | Pest 4.x |

### Project Structure

```
app/                          # Laravel backend
  Http/
    Controllers/              # Auth, Profile, and page controllers
    Middleware/
      HandleInertiaRequests.php
    Requests/                 # Form requests (Login, Profile)
  Models/
    User.php
  Providers/
    AppServiceProvider.php

resources/
  js/
    app.js                    # Inertia + Vue app bootstrap
    Pages/                    # Vue page components per route
      Welcome.vue             # Public landing page
      Dashboard.vue           # Authenticated dashboard
      Auth/                   # Login, Register, Forgot/Reset Password, etc.
      Profile/                # Profile edit, password update, account delete
    Components/               # Reusable Vue components (Buttons, Inputs, Modal, etc.)
    Layouts/                  # AuthenticatedLayout, GuestLayout
  css/
    app.css                   # Tailwind entry point
  views/
    app.blade.php             # Root Blade template (Inertia entry)

routes/
  web.php                     # Public routes (/, /dashboard, /profile)
  auth.php                    # Breeze auth routes

docs/
  reports/                    # GitPR CLI state reports (PT-BR)

public/content/               # Markdown content rendered by the site (per tailwind config)
```

### Brand Colors (defined in tailwind.config.js)

- `gitpr_dark`: `#0a192f` — main dark background
- `gitpr_dark_border`: `#0f2b4e` — dark theme borders
- `gitpr_primary`: `#1a80d4` — primary blue
- `gitpr_cyan_light`: `#2dd4bf`
- `gitpr_cyan_dark`: `#22d3ee`
- `gitpr_text`: `#f8fafc` — light text on dark

Font: **Figtree** (Google Font) as the default sans-serif family.

### Development Commands

```bash
# Install everything
composer run setup

# Start dev environment (server + queue + logs + vite in parallel)
composer run dev

# Build for production
npm run build

# Run tests
composer run test

# Lint PHP
./vendor/bin/pint
```

### Key Dependencies

- `inertiajs/inertia-laravel` ^2.0 — SPA-like experience without an API
- `laravel/breeze` ^2.4 — Authentication scaffolding (Inertia stack)
- `laravel/sanctum` ^4.0 — API token auth
- `tightenco/ziggy` ^2.0 — Named route helpers in JavaScript

### Current State

The project is a fresh Laravel install with Breeze already scaffolded. The Welcome page still has the default Laravel content — it needs to be customized with GitPR branding, features showcase, and documentation navigation. The tailwind config already includes GitPR custom colors and watches `/public/content/**/*.md` for documentation content.

### Git History Context

- `4401723` — Set up a fresh Laravel app
- `e859404` — Install Breeze


### Rules

- After completing any task, generate a detailed report at `docs/claude-code/reports/{branch}/{currentdate}_{branch}_{taskname}.md`, where {currentdate} should be replaced with the current date, {branch} with the current branch name, and {taskname} with a short description of the task — no spaces or special characters, only lowercase letters separated by underscores.

---

# Diretrizes de Comportamento (Karpathy Skills)

Behavioral guidelines to reduce common LLM coding mistakes. Merge with project-specific instructions as needed.

**Tradeoff:** These guidelines bias toward caution over speed. For trivial tasks, use judgment.

## 1. Think Before Coding

**Don't assume. Don't hide confusion. Surface tradeoffs.**

Before implementing:
- State your assumptions explicitly. If uncertain, ask.
- If multiple interpretations exist, present them - don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear, stop. Name what's confusing. Ask.

## 2. Simplicity First

**Minimum code that solves the problem. Nothing speculative.**

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that wasn't requested.
- No error handling for impossible scenarios.
- If you write 200 lines and it could be 50, rewrite it.

Ask yourself: "Would a senior engineer say this is overcomplicated?" If yes, simplify.

## 3. Surgical Changes

**Touch only what you must. Clean up only your own mess.**

When editing existing code:
- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style, even if you'd do it differently.
- If you notice unrelated dead code, mention it - don't delete it.

When your changes create orphans:
- Remove imports/variables/functions that YOUR changes made unused.
- Don't remove pre-existing dead code unless asked.

The test: Every changed line should trace directly to the user's request.

## 4. Goal-Driven Execution

**Define success criteria. Loop until verified.**

Transform tasks into verifiable goals:
- "Add validation" → "Write tests for invalid inputs, then make them pass"
- "Fix the bug" → "Write a test that reproduces it, then make it pass"
- "Refactor X" → "Ensure tests pass before and after"

For multi-step tasks, state a brief plan:
```
1. [Step] → verify: [check]
2. [Step] → verify: [check]
3. [Step] → verify: [check]
```

Strong success criteria let you loop independently. Weak criteria ("make it work") require constant clarification.

---

**These guidelines are working if:** fewer unnecessary changes in diffs, fewer rewrites due to overcomplication, and clarifying questions come before implementation rather than after mistakes.

