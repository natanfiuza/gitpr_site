# Plano — Remover uso do `gitpr.exe` na seção Instalação e Configuração

- **Data:** 2026-10-18
- **Branch:** `develop_natan`
- **Task:** `remover_gitpr_exe_instalacao`
- **Skill:** `/grill-with-docs` (grilling + domain-modeling)
- **Arquivos-alvo:** `public/content/instalacao.{md,pt_br.md,pt_pt.md,es.md,fr.md}`

## Contexto

A seção "Instalação e Configuração" (`/instalacao`) ainda instrui o usuário Windows a baixar o `gitpr.exe` do GitHub Releases. O pipeline de distribuição do GitPR CLI removeu o binário: `public/content/relatorio.md:702` registra *"GitHub Releases: removed — no PyInstaller, no `.exe` asset, no hot-swap"*. A página também mantém uma seção de compilação própria com PyInstaller, igualmente obsoleta.

## Decisões confirmadas (rodada 1 do grill)

| Q | Decisão |
| --- | --- |
| Q1 | Remover **as duas** seções: `Windows: Using the Executable` e `Compiling Your Own Executable` |
| Q2 | Renomear `Linux / macOS: Via PyPI (Recommended)` → `Via PyPI (Recommended)`, pois PyPI agora cobre todas as plataformas |
| Q3 | Aplicar nas **5 variantes de idioma** (`md`, `pt_br`, `pt_pt`, `es`, `fr`) |
| Q4 | Páginas fora de `instalacao.*` (`index.*`, `uso.*`, `docs/testar_sem_usar_pypi.*`) ficam **fora do escopo** — task separada |

## Execução

1. Em cada uma das 5 variantes, remover o bloco `## Windows: ...` inteiro (download do `.exe`, cópia para o `PATH`, bloco do wizard e linha do `~/.gitpr/.env`).
2. Em cada uma das 5 variantes, remover o bloco `## Compiling/Compilando/Compiler ...` inteiro (comandos PyInstaller e saídas `gitpr` / `gitpr.exe`).
3. Ajustar o título da seção PyPI em cada idioma.
4. Verificar que `gitpr.exe` e `PyInstaller` não aparecem mais em `public/content/instalacao.*` e que os separadores `---` e a estrutura markdown continuam válidos.
5. Rodar a suíte de testes (Pest) para confirmar que não há novas falhas.
6. Escrever o relatório da task em `docs/claude-code/reports/develop_natan/`.

## Verificação

- `grep -n "gitpr.exe" public/content/instalacao.*` → sem resultados.
- `grep -ni "pyinstaller" public/content/instalacao.*` → sem resultados.
- Estrutura final das seções nas 5 línguas: Quick Start → Via PyPI (Recommended) → From Source Code → Security → Configuration Reference.
- `composer run test` sem novas falhas além das 8 pré-existentes de `Auth/*` e `ProfileTest` (404, rota catch-all), registradas no relatório de 2026-10-18.
