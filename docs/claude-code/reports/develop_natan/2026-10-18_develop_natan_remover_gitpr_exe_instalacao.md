# Remoção do uso de `gitpr.exe` na seção Instalação e Configuração — 2026-10-18

- **Branch:** `develop_natan`
- **Task:** `remover_gitpr_exe_instalacao`
- **Skill:** `/grill-with-docs` (grilling + domain-modeling)
- **Arquivos-alvo:** `public/content/instalacao.{md,pt_br.md,pt_pt.md,es.md,fr.md}`

## Contexto

A página `/instalacao` ainda instruía o usuário Windows a baixar o `gitpr.exe` do GitHub Releases e colocá-lo no `PATH`. O pipeline de distribuição do GitPR CLI removeu o binário — confirmado pelo relatório de estado do próprio site ([relatorio.md:702](../../../public/content/relatorio.md#L702)): *"GitHub Releases: removed — no PyInstaller, no `.exe` asset, no hot-swap"*. A mesma página também mantinha uma seção de compilação própria com PyInstaller, igualmente obsoleta.

## Decisões do grill (rodada 1, recomendados confirmados)

| Q | Decisão |
| --- | --- |
| Q1 | Remover **as duas** seções: `Windows: Using the Executable` e `Compiling Your Own Executable` |
| Q2 | Renomear `Linux / macOS: Via PyPI (Recommended)` → `Via PyPI (Recommended)` (PyPI cobre todas as plataformas) |
| Q3 | Aplicar nas **5 variantes de idioma** |
| Q4 | `index.*`, `uso.*` e `docs/testar_sem_usar_pypi.*` ficam **fora do escopo** — task separada |

## Mudanças executadas

Em cada uma das 5 variantes:

1. **Removida a seção `## Windows: ...`** — instruções de download do `gitpr.exe` do GitHub Releases, cópia para o `PATH`, bloco do wizard e linha do `~/.gitpr/.env`.
2. **Removida a seção `## Compiling/Compilando/Compiler ...`** — comandos PyInstaller (`pipenv install --dev`, `pyinstaller --onefile ... run.py`) e lista de saídas `gitpr` (Linux/macOS) / `gitpr.exe` (Windows).
3. **Título da seção PyPI generalizado:**

| Idioma | Antes | Depois |
| --- | --- | --- |
| en | `Linux / macOS: Via PyPI (Recommended)` | `Via PyPI (Recommended)` |
| pt_br | `Linux / macOS: Via PyPI (Recomendado)` | `Via PyPI (Recomendado)` |
| pt_pt | `Linux / macOS: Via PyPI (Recomendado)` | `Via PyPI (Recomendado)` |
| es | `Linux / macOS: Vía PyPI (Recomendado)` | `Vía PyPI (Recomendado)` |
| fr | `Linux / macOS : Via PyPI (Recommandé)` | `Via PyPI (Recommandé)` |

Nenhum outro trecho das páginas foi alterado.

## Verificação

| Checagem | Resultado |
|---|---|
| `gitpr.exe` / `PyInstaller` em `public/content/instalacao.*` | 0 ocorrências nas 5 variantes |
| Estrutura de seções (5/5 línguas) | Quick Start → Via PyPI (localizada) → From Source Code → Security → Configuration Reference |
| Separadores `---` e markdown | íntegros (conferido no arquivo inglês completo e nos títulos das 5 variantes) |
| `composer run test` | 58 testes — 50 passaram, 8 falharam (222 assertions, ~10,3 s) |

As 8 falhas são exatamente as pré-existentes de `Auth/*` e `ProfileTest` (`Expected 200 but received 404`), causadas pela rota catch-all [routes/web.php:27](../../../routes/web.php#L27) — as mesmas registradas no relatório de sync-docs de 2026-10-18. **Nenhuma falha nova.**

## Arquivos alterados

Modificados (5):

```
public/content/instalacao.md
public/content/instalacao.pt_br.md
public/content/instalacao.pt_pt.md
public/content/instalacao.es.md
public/content/instalacao.fr.md
```

Criados (3 — rastreabilidade da sessão):

```
docs/plans/develop_natan/20261018_remover_gitpr_exe_instalacao_plansfacts.md
docs/survey/20261018_remover_gitpr_exe_instalacao_surveyfacts.md
docs/claude-code/reports/develop_natan/2026-10-18_develop_natan_remover_gitpr_exe_instalacao.md
```

## Pendências registradas (fora do escopo)

- `public/content/index.*.md:53` — auto-update descrito como "hot-swap via GitHub Releases".
- `public/content/uso.*.md:199-201` — mesma descrição de hot-swap do binário.
- `public/content/docs/testar_sem_usar_pypi.*.md:34` — aviso sobre `gitpr.exe` em `/dist`.
- Newsletters históricas em `public/content/newsletter/*` citam o binário standalone; preservadas como registro histórico.
