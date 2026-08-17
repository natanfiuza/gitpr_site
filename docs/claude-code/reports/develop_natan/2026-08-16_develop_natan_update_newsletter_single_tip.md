# Relatório: Newsletter com 1 Dica por Edição (em vez de 3 a 5)

**Data:** 2026-08-16
**Branch:** `develop_natan`
**Skill:** `generate-newsletter-body` (regra interna alterada)

## Resumo

A skill `generate-newsletter-body` escolhia de **3 a 5 dicas** (`tip_tools.json`) por edição da newsletter. Com o banco atual de 26 dicas, isso renderia no máximo ~8 edições antes de esgotar as dicas. A regra agora é **1 dica por edição**, estendendo a vida útil do banco e permitindo gerar mais versões sem repetir conteúdo.

## Alterações

| Arquivo | Antes | Depois |
| --- | --- | --- |
| `.claude/skills/generate-newsletter-body/SKILL.md` (passo 3) | "escolher de 3 a 5 dicas com `used=false`" | "escolher apenas 1 dica com `used=false`" |
| `docs/tutorial_newsletter.md` (Passo 3) | "escolhe de 3 a 5 dicas com `used: false`" | "escolhe 1 dica com `used: false`" |

Ambas as alterações incluem a justificativa: **uma dica por edição rende mais versões antes de esgotar o banco de dicas**.

## Verificações

- ✅ Busca por "3 a 5" no repositório: apenas os 2 arquivos alterados continham a regra (nenhuma outra menção restante)
- ✅ Frontmatter da skill não precisou de mudança (a descrição já era genérica: "selecionando dicas não usadas")
- ✅ Nenhum outro comportamento da skill alterado (seleção por `used: false`, marcação `used: true`, 5 idiomas)
- ✅ `public/content/menu.json` não foi tocado
