# GitPR 1.4.2 — What's New

## What's New in This Version

- **Two releases closed — 1.4.1 and 1.4.2:** v0.0.18 left 1.4.1 "prepared but not yet closed". This window closed **both**: tag `v1.4.1` was created on 2026-10-18 (PR #218) and `v1.4.2` on 2026-10-27 (PR #222), with `CHANGELOG.md` committed in both. PyPI already serves **1.4.2**; `v1.4.0` is no longer the latest tag.
- **The diff vanished in repositories with legacy content:** `get_git_full_diff()` decoded the `git diff` output as strict UTF-8. A single Latin-1 byte (`0xFA`) killed the subprocess reader thread, `stdout` came back `None` **without an exception** and the CLI announced "No new code found" — as if the branch had no changes. All **10** `subprocess` calls that capture git output now decode with `errors="replace"`, and a `None` returned by git now **exits with code 1** in PR, review, full review and commit. A genuinely empty diff still exits **0**, and `--hook` fails **open**, because a non-zero `prepare-commit-msg` would abort the user's commit.
- **`explain` and `tests generate` finally documented — and fixed along the way:** they were the **last two** features without a topic in `docs/`, inherited as debt over several windows. They gained **5 languages each** (`docs/explain-pr.*` and `docs/tests-generate.*`, 10 files). Documenting revealed **four real defects**: `parse_explain_payload()` broke on the provider's already-parsed dict (`json.loads(dict)` → `TypeError`); `generate_test_file` had the same mismatch; `gitpr tests generate --finding` read `cand.finding.suggested_test` and `cand.diff`, attributes that do not exist; and `gitpr -h --explain` was broken by a dest mismatch (`explain_flag` × `explain`).
- **READMEs point to the official documentation site:** **342** documentation destinations across the 5 READMEs left `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/*.md` and now point to `https://gitpr.natanfiuza.dev.br/docs/*`, with the language in `?lang=` and English **without** a parameter — exactly the format `src/doc_links.py` already canonicalizes. **217 unique URLs** checked with `curl`: **0 failures**.
- **The i18n debt was not paid:** the dictionaries remain at **1620 keys** (×4) and **1618** (`es.json`, `fr.json`), with **8 keys used in code without translation** and **2 orphans** — the same numbers as the previous report. Together with the **7 fixture failures** in `tests/badge/test_badge_paths.py` and the **2 known failures** in `tests/test_mentor_cli.py`, the suite closes at **2950 passed of 2964** (12 failures, 2 skips).

## How to Use

Update from PyPI:

```
pip install --upgrade gitpr-cli
```

The last two undocumented features now have a guide each:

```
gitpr explain                                    # reviewer guide for the current diff
gitpr tests generate --file src/app.py           # dry run: shows the suite without writing
gitpr tests generate --file src/app.py --apply   # writes the test file after confirmation
```

`gitpr -h --explain` works again and opens the reviewer guide, and both `gitpr tests` and `gitpr tests generate` now carry a link to their own guide in the help epilog. Everything is published in five languages at https://gitpr.natanfiuza.dev.br/docs — and the READMEs now point there too, with the language in `?lang=`.

If your repository has files in legacy encodings, upgrade: 1.4.2 decodes git output with `errors="replace"` and exits with code 1 when git itself fails, instead of answering "No new code found" on a branch that does have changes.

## Useful Tips

Smart Excludes filters lockfiles, minified and generated files out of the diff with Git's native pathspec syntax, before the AI ever sees it — the docs' example cuts ~15,000 tokens down to ~250 (98% less), while the changed file list is still injected as metadata.
