# Cache System & Auto-Updater

GitPR includes intelligent caching to save API quotas and a seamless auto-updater to keep you on the latest version.

---

## ⚡ Local Cache System

Whenever you run an AI-powered command (`--review`, `--commit`, etc.), GitPR generates an **MD5 hash** of your current code (diff) combined with your instructions.

If you run the **same command** without changing the code, GitPR intercepts the request and returns the result **instantly from cache** — no API call, no quota spent.

### How It Works

1. Command executes → diff + instructions are hashed (MD5)
2. If the hash exists in `~/.gitpr/cache/prompts/` → return cached result
3. If not → call the AI, save the response, return the result

### Benefits

- **Zero duplicate API calls** — re-running the same review costs nothing
- **Millisecond responses** — cache reads are instant
- **Automatic invalidation** — any code change produces a different hash
- **Transparent** — no flags needed, always active

---

## 🔄 Auto-Updater (OTA Updates)

GitPR checks for updates silently on each execution and can hot-swap the binary in seconds.

### Check & Update

```bash
# Force update check
gitpr -u
# or
gitpr --update
```

### How It Works

1. **Connection Guardian:** Checks network availability before starting — never blocks offline workflows
2. **Silent background check:** On each execution, compares local version against the latest GitHub Release
3. **Hot-Swap technique:** Downloads the new binary, renames the old one as backup, and replaces it transparently — all while the current execution finishes normally
4. **Rollback capability:** If the new version fails, the old binary is still on disk

### Version Verification

GitPR uses **SHA-256 checksums** published with each GitHub Release to verify binary integrity before installation.

---

## Combined Workflow

```bash
# 1. Work normally — cache saves you from duplicate API calls
gitpr -r
gitpr -r  # Same diff → instant cache hit ⚡

# 2. Change some code → new hash → fresh AI call
# ... edit files ...
gitpr -r  # Different diff → new analysis

# 3. Stay updated effortlessly
gitpr -u  # Check and install latest version
```

---

## Cache Storage

All cache files live in `~/.gitpr/cache/prompts/`. You can safely delete this directory to free disk space — GitPR will recreate it as needed.

```bash
# Clear all cached responses
rm -rf ~/.gitpr/cache/prompts/
```

---

[← Internationalization](/i18n) &nbsp;|&nbsp; [Contributing →](/contribuicao)
