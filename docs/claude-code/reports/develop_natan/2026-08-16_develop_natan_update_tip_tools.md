# Relatório: Atualização das Dicas da Newsletter (`tip_tools.json`)

**Data:** 2026-08-16
**Branch:** `develop_natan`
**Skill:** `update-tip-tools`

## Resumo

`public/content/tip_tools.json` **não existia** — esta execução criou o banco de dicas do zero.

- **Dicas adicionadas:** 26 (`tip_1` a `tip_26`, todas com `used: false`)
- **Idiomas:** 5 por dica (en, pt_br, pt_pt, fr, es) — 130 textos no total
- **Dicas atualizadas:** 0 (não havia estado anterior para preservar)

## Fontes usadas

Cada dica aponta para o arquivo de documentação de origem (`source`), todos verificados como existentes:

| Dica | Tema | Fonte |
| --- | --- | --- |
| tip_1 | Cache MD5 de respostas (repetir comando = custo zero) | `docs/commit-message-ia.md` |
| tip_2 | Map-Reduce automático para diffs > ~90k tokens | `docs/map-reduce-diff.md` |
| tip_3 | Smart Excludes (redução de ~98% de tokens) | `docs/smart-excludes.md` |
| tip_4 | Flag escondida `--pre-save` (payload completo em JSON) | `docs/readme.md` |
| tip_5 | Hook `prepare-commit-msg` que nunca sobrescreve sua mensagem | `docs/git-hooks-locais.md` |
| tip_6 | Atalhos do chat (F5/F6/F7/F8) e memória por branch | `docs/understanding_chat_functionality.md` |
| tip_7 | Gerador de issues com 3 motores de contexto (`-is`, `-ht`, `-b`) | `docs/issue-tui-help.md` |
| tip_8 | Arqueólogo de Código (ORIGIN vs REFACTORING, modelo secundário) | `docs/blame-arqueologo.md` |
| tip_9 | Auto-update com hot-swap, rollback e guardião `8.8.8.8:53` | `docs/auto-update.md` |
| tip_10 | Atualização de PR existente (só o corpo) + retry de push | `docs/pull-request-publication.md` |
| tip_11 | `gitpr-mcp --install auto` (configuração de MCP em 1 comando) | `docs/mcp-integration.md` |
| tip_12 | `gitpr-mcp --tool` (12 ferramentas direto do terminal) | `docs/mcp-integration.md` |
| tip_13 | Plugins sem código (prompts `.md` e pacotes de linter globais) | `docs/plugins-system.md` |
| tip_14 | Linter gratuito como quality gate no GitHub Actions | `docs/github-ci-linter.md` |
| tip_15 | Guia de regex contra backtracking catastrófico | `docs/guia-regex-gitpr.md` |
| tip_16 | Telemetria local com dashboard TUI (`--metrics`) | `docs/metricas-telemetria.md` |
| tip_17 | Skills como instruções de sistema versionáveis (`gitpr -s`) | `docs/skill-template.md` |
| tip_18 | Otimização de tokens (<150 tokens, "ZERO saudações", CAPS LOCK) | `docs/otimizacao-de-tokens.md` |
| tip_19 | Troca de provider por execução + Ollama offline | `providers.md` |
| tip_20 | Config determinística (temp 0.0) e fallback automático de provider | `docs/providers-ia.md` |
| tip_21 | Override de idioma por execução (`--lang`) | `i18n.md` |
| tip_22 | `gitpr --status` (listing sem IA e sem rede) | `docs/git-status.md` |
| tip_23 | Arquivos não rastreados ignorados de propósito (segurança) | `docs/untracked-files.md` |
| tip_24 | Auditoria de arquivo completo (`gitpr -r -i`) | `docs/code-review-ia.md` |
| tip_25 | Persona Caveman Commit (mensagens ultratelegráficas) | `docs/caveman-commit.md` |
| tip_26 | TUI de issues (F2/F3/Esc) + PAT com escopo pré-selecionado | `docs/gitpr-issue-option.md` |

## Metodologia

1. **Varredura:** 3 agentes leram a documentação em paralelo (`public/content/docs/**` + páginas raiz), excluindo `newsletter/**` e `relatorio*.md`, e retornaram ~58 candidatos.
2. **Seleção:** 26 candidatos com mais apelo para newsletter, sem sobreposição temática.
3. **Verificação factual:** comandos, flags, números e citações das dicas foram conferidos diretamente nos arquivos de origem antes da redação.
4. **Redação:** cada dica escrita nos 5 idiomas do site, com diferenças pt_br/pt_pt respeitadas.

## Verificações

- ✅ JSON válido (validado com `php -r` + `json_decode`)
- ✅ 26 dicas, ids únicos e sequenciais, ordenados
- ✅ Todas as dicas com os 5 idiomas preenchidos
- ✅ `used: false` em todas (não havia estado anterior; sem regressão possível)
- ✅ Todos os campos `source` apontam para arquivos existentes
- ✅ `public/content/menu.json` **não foi alterado**
- ✅ Nenhum arquivo de documentação foi modificado

## Observação extra

A varredura encontrou uma inconsistência na própria documentação (não corrigida nesta tarefa, pois não é o escopo): o default do modelo Gemini aparece como `gemini-2.5-flash` em `index.md`, mas como `gemini-pro-latest` em `instalacao.md` e `providers.md`. Vale alinhar quando a doc for revisada.
