# Survey de fatos — Remover uso do `gitpr.exe` na seção Instalação e Configuração

- **Data:** 2026-10-18
- **Repo:** `gitpr_site` (`c:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr_site`)
- **Branch:** `develop_natan`
- **Task:** `remover_gitpr_exe_instalacao`
- **Skill:** `/grill-with-docs` → grilling + domain-modeling

---

## 1. Contexto da tarefa

Pedido original: na seção "Instalação e Configuração" o site ainda mostra o uso no Windows via `gitpr.exe`; remover porque essa função não é mais gerada.

## 2. Relatório de fatos levantados

### 2.1 Onde o `gitpr.exe` aparece na seção

- `public/content/instalacao.md:30-55` — seção `## Windows: Using the Executable`: baixar `gitpr.exe` do [GitHub Releases](https://github.com/natanfiuza/gitpr/releases), mover para um diretório do `PATH` (ex.: `C:\Windows\System32`), rodar o setup guiado, bloco do wizard e `~/.gitpr/.env`.
- `public/content/instalacao.pt_br.md:30-55`, `instalacao.pt_pt.md:30-55`, `instalacao.es.md:30-55`, `instalacao.fr.md:30-55` — mesmas seções traduzidas.
- `public/content/instalacao.md:93-109` (+4 variantes) — seção `## Compiling Your Own Executable`: PyInstaller gera um binário; saídas listadas como `gitpr` (Linux/macOS) e `gitpr.exe` (Windows).

### 2.2 Confirmação da remoção no produto

- `public/content/relatorio.md:702` (e variantes pt_br/pt_pt/es/fr): *"**GitHub Releases:** removed — no PyInstaller, no `.exe` asset, no hot-swap"*.
- Consequência: o PyInstaller saiu do pipeline de distribuição; a única via de instalação é PyPI (`pip install gitpr-cli`).

### 2.3 Estrutura da página

- O Quick Start já cobre `pip install gitpr-cli` + `gitpr --install`, com link para o Guia do Install Wizard.
- A seção `## Linux / macOS: Via PyPI (Recommended)` fazia contraponto ao `.exe` no Windows; sem ele, o título sugere incorretamente que Windows não usa PyPI.
- Conteúdo resolvido por `app/Http/Controllers/DocsController.php` (`public/content/{page}.{lang}.md`), com variantes `en` (`instalacao.md`), `pt_br`, `pt_pt`, `es`, `fr`.
- `public/content/menu.json` aponta para a página `instalacao` (sem âncoras de seção), então a remoção não afeta navegação.

### 2.4 Referências fora da seção (não tocadas nesta task)

- `public/content/index.*.md:53` — tabela de comandos: auto-update "hot-swap binary updates from GitHub Releases".
- `public/content/uso.*.md:199-201` — descrição do auto-update com hot-swap do binário.
- `public/content/docs/testar_sem_usar_pypi.*.md:34` — aviso sobre `gitpr.exe` na pasta `/dist`.
- `public/content/newsletter/*` — newsletters históricas que citam o binário standalone.
- `docs/reports/relatorio_estado_ GitPR-CLI.md:39` — relatório interno antigo sobre hot-swap.

## 3. Decisões das rodadas

| Q | Pergunta | Decisão confirmada |
| --- | --- | --- |
| Q1 | Remover só a seção Windows ou também a de compilação própria? | Remover **as duas** — o PyInstaller saiu do pipeline de distribuição |
| Q2 | O que fazer com `Linux / macOS: Via PyPI (Recommended)`? | Generalizar o título para `Via PyPI (Recommended)` |
| Q3 | Aplicar nas 5 línguas? | Sim — todas as 5 variantes |
| Q4 | Corrigir `index.*` / `uso.*` / `testar_sem_usar_pypi.*` agora? | Não — task separada |

## 4. Escopo final

- Alterar somente `public/content/instalacao.{md,pt_br.md,pt_pt.md,es.md,fr.md}`.
- Resultado da execução registrado no relatório da task em `docs/claude-code/reports/develop_natan/`.
