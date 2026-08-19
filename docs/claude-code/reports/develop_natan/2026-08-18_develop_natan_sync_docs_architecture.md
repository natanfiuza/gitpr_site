# Relatório: Sync de `ARCHITECTURE.md` (2026-08-18)

**Tarefa:** Sincronizar `C:\Users\nataniel\projetos\python\gitpr\docs\ARCHITECTURE.md` com `public/content/docs/ARCHITECTURE.md` em todos os idiomas suportados.

**Escopo:** apenas o tópico `ARCHITECTURE` (sync pontual, não a varredura completa — essa foi feita mais cedo hoje em `2026-08-18_develop_natan_sync_docs.md`).

## Diagnóstico

O tópico `ARCHITECTURE` **deixou de ser monolíngue na fonte**. O sync anterior (11:20) o registrou como "só variante inglesa"; às 14:45 o repositório gitpr regerou o tópico em 5 idiomas.

| Lado | Estado antes deste sync |
| --- | --- |
| Fonte (gitpr/docs) | 5 variantes: `.md` (EN), `.pt_br`, `.pt_pt`, `.es_es`, `.fr_fr` |
| Site (public/content/docs) | 1 variante: `.md` — contendo **texto em português (PT-PT)**, herdado da fonte monolíngue anterior |

**Anomalia principal:** o slot canônico inglês (`ARCHITECTURE.md`) do site continha conteúdo português desatualizado. Não era uma edição local intencional — era a fonte antiga, que era escrita em PT antes de a tradução existir. Também divergia do `ARCHITECTURE.pt_pt.md` atual da fonte (conteúdo mais antigo).

## Ações executadas

| Fonte | Destino no site | Ação |
| --- | --- | --- |
| `ARCHITECTURE.md` (EN) | `ARCHITECTURE.md` | Sobrescrito — corrige idioma do slot canônico (PT → EN) |
| `ARCHITECTURE.pt_br.md` | `ARCHITECTURE.pt_br.md` | Criado |
| `ARCHITECTURE.pt_pt.md` | `ARCHITECTURE.pt_pt.md` | Criado |
| `ARCHITECTURE.es_es.md` | `ARCHITECTURE.es.md` | Criado (sufixo renomeado `.es_es` → `.es`) |
| `ARCHITECTURE.fr_fr.md` | `ARCHITECTURE.fr.md` | Criado (sufixo renomeado `.fr_fr` → `.fr`) |

Conteúdo markdown preservado exatamente como na fonte (emojis, tabelas, links, blocos de código).

## Verificação

- `cmp` variante por variante (com mapeamento de sufixos): **5/5 idênticos**, 0 divergências.
- Site agora tem as **5 variantes canônicas** do tópico: `.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md`.
- Nenhum sufixo legado `.es_es`/`.fr_fr` criado no site.
- Primeira linha de `ARCHITECTURE.md` confirmada em inglês: *"GitPR - Intelligent Code Review and Pull Request Automation"*.
- `public/content/menu.json`: entradas de `docs/ARCHITECTURE` já existiam nos 5 idiomas (Architecture / Arquitetura / Arquitetura / Architecture / Arquitectura) — **nenhuma alteração necessária**.

## Observações

- A lacuna de idioma nº 2 do relatório anterior (`ARCHITECTURE` monolíngue) está **resolvida** — aquele item pode ser considerado obsoleto.
- Nenhum arquivo foi removido.
- Conteúdo novo trazido pela fonte inclui: trailer `Co-Authored-By` no auto-commit e nota de que o relatório do linter só é gerado quando há violações.
