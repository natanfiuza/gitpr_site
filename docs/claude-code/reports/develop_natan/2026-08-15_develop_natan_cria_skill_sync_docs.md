# Relatório: Criação da skill local `sync-docs`

**Data:** 2026-08-15
**Branch:** develop_natan

## Objetivo

Criar uma skill local que carrega a documentação técnica de `C:\Users\nataniel\projetos\python\gitpr\docs`, verifica o que está faltando ou diferente em `public\content\docs` e confere a sincronização dos 5 idiomas por tópico.

## O que foi feito

- Criada a skill `.claude/skills/sync-docs/SKILL.md` com o fluxo: diagnóstico (faltando/divergente/idiomas/menu) → sincronização (copiar, sobrescrever, renomear sufixos) → verificação → relatório da tarefa.
- Exploração prévia dos dois repositórios para documentar as convenções na skill.

## Descobertas da exploração (documentadas na skill)

1. **Sufixos de idioma divergem entre os repositórios:**
   - Fonte (gitpr): `.es_es` / `.fr_fr` / `.pt_br` / `.pt_pt`
   - Site (canônico): `.es` / `.fr` / `.pt_br` / `.pt_pt` — o `DocsController` resolve `{page}.{lang}.md` com lang ∈ {en, pt_br, pt_pt, fr, es} e o `LanguageSelector` só oferece esses códigos.
   - **Problema real identificado:** 25 tópicos do site só têm `.es_es`/`.fr_fr` — usuários de ES/FR caem no fallback inglês. A skill manda renomear ao sincronizar e reportar os duplicados legados.
2. **`readme` do site espelha o `README.md` da raiz do repo gitpr** (não `docs/`).
3. **Fonte tem subdiretórios internos** (`plans/`, `prompts/`, `reports/`, `claude-code/`, `extra/`, `gemini/`) que não são tópicos do site — ignorados.
4. **`menu.json`** registra tópicos em 5 línguas sob a seção "Technical Documentation" (formato `{"title": "▸ Título", "path": "docs/tópico"}`) — tópicos novos precisam de entrada.

## Diagnóstico atual (pendências reais no site)

- **Faltando no site:** `git-status` (5 variantes na fonte)
- **Divergentes (15 tópicos):** auto-update, code-review-ia, commit-message-ia, github-pat-integration, install-wizard, issue-tui-help, mcp-annotations, mcp-integration, mcp-prompts, metricas-telemetria, plugins-system, pr-descricao-padrao, providers-ia, pull-request-publication, testar_sem_usar_pypi
- **Exclusivos do site:** `chat-interativo`, `readme`
- **Sem entrada no menu:** hooks-versioning, plugins-system, pull-request-publication, smart-excludes
- **Duplicados legados `.es`/`.fr` vs `.es_es`/`.fr_fr`:** github-ci-linter, guia-regex-gitpr, install-wizard (+ readme/testar_sem_usar_pypi só com `.es`/`.fr`)

## Pendência

A skill ainda não foi executada. Para sincronizar o site agora, acionar `/sync-docs`.
