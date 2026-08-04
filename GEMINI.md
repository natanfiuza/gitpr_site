# GEMINI.md

## Project

**GitPR Site** — official documentation/presentation website for [GitPR CLI](https://github.com/natafiuza/gitpr), a Python CLI that automates Git workflows (AI code reviews, PR generation, semantic commits, linting, tech debt auditing, TUI chat) using Google Gemini and DeepSeek.

Built with **Laravel 13.x** + **Inertia.js 2.x** + **Vue 3** + **Tailwind CSS 3.x**.

### Stack

| Layer | Technology |
| --- | --- |
| Backend | Laravel 13.x (PHP ^8.3) |
| Frontend | Vue 3 + Inertia.js 2.x |
| Markdown | `markdown-it` + `highlight.js` + `markdown-it-container` |
| Styling | Tailwind CSS 3.x + `@tailwindcss/forms` + `@tailwindcss/typography` |
| Build | Vite 8.x + `@tailwindcss/vite` + `laravel-vite-plugin` |
| Auth | Laravel Breeze 2.x (Inertia stack) |
| Testing | Pest 4.x |

### Key Dependencies

| Package | Role |
|---------|------|
| `inertiajs/inertia-laravel` ^2.0 | SPA-like experience without an API |
| `@inertiajs/vue3` ^2.0 | Vue 3 Inertia adapter |
| `laravel/breeze` ^2.4 | Auth scaffolding (Inertia stack) |
| `laravel/sanctum` ^4.0 | API token auth |
| `tightenco/ziggy` ^2.0 | Named route helpers in JS |
| `markdown-it` ^14.3 | Markdown parser for doc pages |
| `highlight.js` ^11.11 | Code syntax highlighting |

### Project Structure

```
app/
  Http/
    Controllers/          # DocsController (renders MD, search API), Auth, Profile
    Middleware/           # HandleInertiaRequests
    Requests/             # Form requests (Login, Profile)
  Models/
    User.php
routes/
  web.php                 # Dynamic doc routes (/{page?}), /api/search, /dashboard, /profile
  auth.php                # Breeze auth routes
resources/
  js/
    Pages/                # DocsLayout.vue, Welcome.vue, Dashboard.vue
    Components/           # MarkdownViewer.vue, SearchBar.vue, ThemeToggle.vue, LanguageSelector.vue
    Layouts/              # AuthenticatedLayout, GuestLayout
    app.js                # Inertia + Vue bootstrap
  css/app.css             # Tailwind entry point
  views/app.blade.php     # Root Blade template (Inertia entry)
public/content/           # Markdown content & menu.json rendered by the site (i18n support)
docs/                     # Plans, reports, and documentation metadata
```

### Design Tokens

Defined in `tailwind.config.js`:

| Token | Value | Usage |
| --- | --- | --- |
| `gitpr_dark` | `#0a192f` | Main dark background |
| `gitpr_dark_border` | `#0f2b4e` | Dark theme borders |
| `gitpr_primary` | `#1a80d4` | Primary blue |
| `gitpr_cyan_light` | `#2dd4bf` | Accent |
| `gitpr_cyan_dark` | `#22d3ee` | Accent |
| `gitpr_text` | `#f8fafc` | Light text on dark |

Font: **Figtree** (Google Font) as default sans-serif.

### Commands

```bash
composer run setup   # Install dependencies, copy .env, generate key, migrate, build
composer run dev     # Start dev (server + queue + logs + vite in parallel via concurrently)
composer run test    # Run Pest tests
npm run build        # Production build
./vendor/bin/pint    # Lint/format PHP code
```

## Rules

### Reports

After completing significant tasks, write a detailed report at:

```
docs/gemini/reports/{branch}/{YYYY-MM-DD}_{branch}_{task_name}.md
```

`{task_name}` — lowercase, underscores, no spaces or special characters.

### Behavioral Guidelines

**Tradeoff:** these bias toward caution over speed. For trivial tasks, use judgment.

1. **Think before coding.** State assumptions explicitly. If multiple interpretations exist, present them — don't pick silently. If something is unclear, stop and ask. Push back when a simpler approach exists.

2. **Simplicity first.** Minimum code to solve the problem. No speculative features, no abstractions for single-use code, no error handling for impossible scenarios. If 200 lines could be 50, rewrite. Test: "Would a senior engineer say this is overcomplicated?" → simplify.

3. **Surgical changes.** Touch only what the task requires. Don't refactor adjacent code, don't "improve" formatting, match existing style even if you'd do it differently. Remove imports/variables that YOUR changes made unused — but don't delete pre-existing dead code unless asked.

4. **Goal-driven execution.** Transform tasks into verifiable goals ("Add validation" → "Write tests, then make them pass"). For multi-step work, state a brief plan with verify steps. Loop until verified — don't stop at "should work."

**These guidelines are working when:** diffs contain fewer unnecessary changes, fewer rewrites from overcomplication, and clarifying questions come before implementation rather than after mistakes.
