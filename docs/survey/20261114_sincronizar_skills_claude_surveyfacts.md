# Survey — Sincronizar Skills do Claude Code para o Antigravity (2026-11-14)

> Levantamento automatico da sessao de grill (`/grill-with-docs`) — fronteira esgotada e entendimento compartilhado confirmado pelo usuario.

## 1. Contexto da tarefa
- **Pedido original:** `/plan /grill-with-docs SIncronize as skills do claude code .claude\skills para que voce possa usar`
- **Tela / Modulo:** Configuração de Agentes no Workspace (`.agents/skills.json`, `.claude/skills/*/SKILL.md`)
- **Skill:** `/grill-with-docs` (grilling + domain-modeling) · branch `develop_natan`
- **Metodo:** 1 rodada, 3 decisões na fronteira, inspeção autônoma de `.claude/skills/`, `CLAUDE.md`, `GEMINI.md`, `CONTEXT.md`, `.gitignore` e documentação `agy-customizations`.
- **Arquivos-chave lidos:**
  - `.claude/skills/generate-newsletter-body/SKILL.md`
  - `.claude/skills/sync-docs/SKILL.md`
  - `.claude/skills/sync-docs/rewrite_doc_links.py`
  - `.claude/skills/update-relatorio/SKILL.md`
  - `.claude/skills/update-tip-tools/SKILL.md`
  - `CLAUDE.md`, `GEMINI.md`, `CONTEXT.md`, `.gitignore`

## 2. Decisoes das rodadas

### Rodada 1 — Descoberta, Relatórios de Agente e Versionamento
| # | Decisao | Resolucao |
|---|---|---|
| Q1 | Mecanismo de disponibilização das skills para o Antigravity | **Fonte única via `.agents/skills.json`** apontando para `.claude/skills` (evita duplicação de arquivos e do script `rewrite_doc_links.py`) |
| Q2 | Convenção de relatório de tarefa dentro das 4 skills (`CLAUDE.md` vs `GEMINI.md`) | **Instrução agnóstica ao agente** nos 4 `SKILL.md` (`docs/claude-code/reports/{branch}/...` conforme `CLAUDE.md` ou `docs/gemini/reports/{branch}/...` conforme `GEMINI.md`) |
| Q3 | Versionamento de `.agents/` no Git (`.gitignore`) | **Manter `.agents/` versionado no repositório** (fora do `.gitignore`, seguindo `.claude/` e `GEMINI.md`) |

### Defaults assumidos (nao contestados)
- Escopo de workspace (não global em `~/.gemini/config/`), pois as 4 skills operam exclusivamente sobre `public/content/` do `gitpr_site`.
- `CONTEXT.md` permanece inalterado por tratar exclusivamente do vocabulário de domínio da documentação técnica publicada no site.
- Nenhum ADR criado em `docs/adr/`, visto que `.agents/skills.json` é trivialmente reversível (não atende aos 3 critérios obrigatórios para ADR).

## 3. Relatorio de fatos levantados
### Inventário e Compatibilidade das Skills em `.claude/skills/`
As 4 skills (`generate-newsletter-body`, `sync-docs`, `update-relatorio`, `update-tip-tools`) já possuem frontmatter YAML compatível com o Antigravity (`name` idêntico ao nome da pasta e `description` preenchida).

### Descoberta via `.agents/skills.json`
O sistema de customização do Antigravity suporta `.agents/skills.json` na raiz do repositório com entradas relativas ao workspace (`{ "entries": [{ "path": ".claude/skills" }] }`), realizando varredura de 1 nível e carregando `*/SKILL.md` sem duplicar arquivos.

### Acoplamentos de Agente Identificados nos `SKILL.md`
1. **Passo de relatório**: Todas as 4 skills continham referência fixa a `docs/claude-code/reports/{branch}/...` (regra do `CLAUDE.md`), enquanto `GEMINI.md` exige `docs/gemini/reports/{branch}/...`.
2. **Script `rewrite_doc_links.py`**: Invocado em `sync-docs/SKILL.md` via `python .claude/skills/sync-docs/rewrite_doc_links.py`. Como a fonte única permanece em `.claude/skills/sync-docs/rewrite_doc_links.py`, o caminho e o cálculo `Path(__file__).resolve().parents[3]` continuam funcionando sem alterações.
