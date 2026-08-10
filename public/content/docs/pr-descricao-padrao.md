# Technical Documentation: Pull Request Generation (Default Mode)

When executed **without flags**, GitPR automatically generates a complete Pull Request description in Markdown, ready to paste into GitHub, GitLab, or Bitbucket.

---

## 1. Usage

```bash
gitpr
```

---

## 2. Execution Flow

```
git fetch → diff against origin/main → AI → .md
```

1. **`git fetch`** — Syncs with the remote repository
2. **Diff** — Compares all changes from the current branch against `origin/main`
3. **AI** — Generates the commit message (Conventional Commits) and the PR description
4. **Output** — Saves a `.md` file in the project root

---

## 3. Output

The generated file (`{branch}_{datetime}_PR_DESC.md`) contains:

```markdown
# 🚀 Pull Request Suggestion

**Recommended Commit Message:**
feat: short description of the change

---

## Description
...
## Changes
...
## Impact
...
```

---

## 4. Customization

### 4.1 PR Template

AI behavior can be customized via the `.gitpr.pr.md` file:

```bash
gitpr -s          # Downloads the template
# Edit .gitpr.pr.md with your team's required sections
gitpr             # The AI will follow your template
```

### 4.2 Output File Name

Configure the `OUTPUT_FILE_NAME` environment variable in the `~/.gitpr/.env` file:

```ini
OUTPUT_FILE_NAME=PR_{branch}_{datetime}.md
```

Available variables: `{branch}` (current branch name) and `{datetime}` (timestamp `YYYYMMDDHHMMSS`).

---

## 5. AI Provider Selection

```bash
gitpr -p gemini       # Forces Google Gemini
gitpr -p deepseek     # Forces DeepSeek
```

If no provider is specified, GitPR uses the default defined in the `DEFAULT_AI_PROVIDER` variable in `~/.gitpr/.env`.

---

## 6. Response Cache

GitPR generates an MD5 hash of the diff + AI instructions. If you run `gitpr` again **without changing the code**, the response is returned from the local cache in milliseconds, without consuming API quotas.

> **Note:** See also the [main documentation (README.md)](docs/readme) for an overview of all features.
