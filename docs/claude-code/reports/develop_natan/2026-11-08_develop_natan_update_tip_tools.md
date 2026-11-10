# Atualização do Banco de Dicas da Newsletter

- **Data:** 2026-11-08
- **Branch:** `develop_natan`
- **Skill:** `.claude/skills/update-tip-tools`
- **Arquivo:** `public/content/tip_tools.json`

## Estado

| | Antes | Depois |
|---|---|---|
| Dicas | 40 | **47** |
| `used=true` | 14 | **14** (nenhuma regrediu) |
| `used=false` | 26 | **33** |

A varredura cobriu todos os `.md` de `public/content/**/*.md` (excluindo `newsletter/**` e
`relatorio*.md`). Cerca de 25 documentos temáticos não tinham nenhuma dica no banco; foram
escolhidos sete com material realmente novo e verificável, em vez de esgotar o catálogo.

## Dicas adicionadas (7)

| id | Fonte | Gancho |
|---|---|---|
| `tip_41` | `docs/install-wizard.md` | `gitpr --install` em 5 etapas, com `[Y/n]` por etapa; `gitpr seed`/`-sd` para só a primeira, idempotente, `--force` e `--all` |
| `tip_42` | `docs/demo.md` | `gitpr demo` sem chave, sem repositório e sem rede; `--scenario`, `--no-tui`, `--lang` |
| `tip_43` | `docs/config-tui.md` | `gitpr config` mestre-detalhe sobre o `.env`; F2 salva, Ctrl+R repõe, `/` busca, Esc pergunta |
| `tip_44` | `docs/editor-extension.md` | Extensão VS Code/Cursor como thin client MCP: segredos ficam no `.env`, 4 ações na status bar, sem telemetria externa |
| `tip_45` | `docs/mcp-prompts.md` | 7 prompts prontos no editor, templates por idioma seguindo `GITPR_LANG` |
| `tip_46` | `docs/version-markers.md` | Traduções, smart excludes, spinner, presets e hooks chegam por OTA, comparados a marcadores de versão |
| `tip_47` | `docs/linter-regras-customizadas.md` | `--linter-setup` como ponte Checkstyle para ESLint/PHPCS/Stylelint, só linhas alteradas, timeout de 120 s |

Todas entraram com `used=false` e `id` sequencial estável (`tip_41`…`tip_47`), no formato do
schema existente (`{id, used, source, content:{en,pt_br,pt_pt,fr,es}}`, indentação de 4 espaços).

## Método

O JSON foi carregado, as sete entradas foram anexadas ao final e regravado com
`json.dumps(indent=4, ensure_ascii=False)`. Antes de gravar, um teste confirmou que o
round-trip das 40 dicas existentes era **byte-idêntico** ao arquivo em disco, então o diff é
puramente aditivo: **84 linhas inseridas, nenhuma alterada**.

Terminologia conferida contra os próprios docs traduzidos: PT `assistente`/`tela` (BR) e
`ecrã`/`modelos` (PT), ES `asistente`/`plantillas`/`barra de estado`, FR
`assistant`/`modèles`/`barre d'état`, e `[Y/n]` mantido literal nos cinco idiomas como nos
docs localizados.

## Verificação

| Checagem | Resultado |
|---|---|
| JSON válido e com 47 dicas | sim |
| `used` de todas as dicas antigas preservado | sim (0 regressões `true` → `false`) |
| `id` sequencial e sem colisão | `tip_1`…`tip_47` |
| Cinco idiomas preenchidos e não vazios | 47/47 |
| Conteúdo duplicado (texto EN normalizado) | 0 |
| `source` de cada dica nova existe | 7/7 |
| Vazamento pt_br ↔ pt_pt (`arquivo`/`ficheiro`, `você`/`tu`) | 0 |
| Diff do arquivo | 84 inserções, 0 remoções |
| `public/content/menu.json` | não alterado nesta tarefa (mtime anterior, 08:00:29) |

## Observações

- Os documentos já cobertos pelo banco (smart excludes, métricas, MCP tools, check-ci, release
  notes, policy packs, etc.) não geraram dicas novas para não repetir conteúdo.
- `docs/marketplace-publishing-guide.md` e `docs/ARCHITECTURE.md` ficaram de fora: o primeiro é
  um guia de publicação da extensão (público interno) e o segundo é referência de arquitetura,
  sem ação de usuário que rendesse dica.
- O banco sobe de 26 para 33 dicas disponíveis para as próximas edições da newsletter.
