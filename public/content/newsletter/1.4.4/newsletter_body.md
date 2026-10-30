# GitPR 1.4.4 — What's New

## What's New in This Version

- **`gitpr -c` became a 4-step TUI:** committing is no longer a silent pipeline. `CommitApp` opens the **Stage Selector** for files outside the index, generates the message in a background thread over the **staged diff**, shows the **review/edit modal** and runs the commit in an animated progress modal — you can choose files, edit the text or cancel before anything reaches the repository.
- **The message now describes what was actually staged:** `get_git_staged_diff()` runs `git diff --cached` with the same guards as the regular diff (`-U1 -w -M -B`, smart excludes, UTF-8), falling back to the working-tree diff only when the index is empty. What is not staged does not enter the prompt.
- **The commit modal closes by itself on success:** `mark_finished(False)` + `set_timer(0.6, self.exit)` hand the terminal back to you, and the CLI prints `committed`/`failed` with git's output. On error the **Close** button stays, because that is where the failure log is readable.
- **1.4.3 and 1.4.4 are both on PyPI:** the update-gate fix — rolling 1-hour cache, PEP 440 comparison, the gate reaching subcommands, strict-boolean `GITPR_SKIP_UPDATE_CHECK` — landed in 1.4.3 and was published in the same window. `pip install --upgrade gitpr-cli` gets 1.4.4.
- **Declared break:** outside `--hook`, `gitpr -c` **always** opens the TUI — there is no TTY guard in the routing. Scripts and CI jobs that relied on the silent commit must switch to `--hook`.
- **The i18n debt grew, it was not paid:** the dictionaries stay at **1620 keys** (×4) and **1618** (`es.json`, `fr.json`) while the code now uses **1629** — the 3 new commit TUI strings went in untranslated. That is **11 keys without a dictionary** and **2 orphans**, and `__lang_version__` climbed to **v0.0.36** with no language file changed.

## How to Use

Update from PyPI:

```
pip install --upgrade gitpr-cli
```

Run `gitpr -c` (or `gitpr --commit`) in any repository. If everything is already staged, the Stage Selector is skipped; otherwise it lists the files outside the index with **Select All**, **Deselect All**, **Stage Selected**, **Skip Staging** and **Cancel**. Review the generated message, edit it if you want — the edited text is what git commits — and Confirm. The progress modal closes on its own once the commit succeeds.

For automation, `--hook` keeps the old silent, merge-aware path, so use it in commit hooks and CI. `GITPR_SKIP_UNSTAGED_CHECK=true` (or `--no-unstaged-check`) skips only the Stage Selector, and `GITPR_AUTO_STAGE=true` stages everything and moves on.

## Useful Tips

A bug fix, a refactor and a config tweak in the same `git diff`? `gitpr split` groups the hunks by intent and proposes one atomic commit per concern, each with a message written for that subset alone. Reading is the default — it prints the plan and asks; `--dry-run` prints and stops, `--apply` stages and commits group by group, `--max-groups 3` caps the plan. Untracked files are named in a warning, never silently dropped.
