# Relatório: Atualização da Página de Instalação com Quick Start

**Data:** 2026-08-06
**Branch:** develop_natan
**Tarefa:** update_instalacao_quickstart

## Resumo

Atualizados todos os 5 arquivos de instalação (EN, PT-BR, PT-PT, FR, ES) para refletir os novos modos de instalação baseados na seção Quick Start do `public/content/docs/readme.md`.

## Arquivos Modificados

| Arquivo | Idioma |
| --- | --- |
| `public/content/instalacao.md` | English |
| `public/content/instalacao.pt_br.md` | Português (Brasil) |
| `public/content/instalacao.pt_pt.md` | Português (Portugal) |
| `public/content/instalacao.fr.md` | Français |
| `public/content/instalacao.es.md` | Español |

## Mudanças Realizadas

### Estrutura anterior (todos os idiomas)
- Wizard de primeiro execução (abordagem antiga) como fluxo principal
- Seções separadas por SO, sem menção ao `gitpr --install`

### Nova estrutura (todos os idiomas)
1. **Quick Start** — introdução com `pip install gitpr-cli` + `gitpr --install` como caminho principal, extraído do readme
2. **Windows: Executable** — atualizado para usar `gitpr --install` em vez do wizard automático de primeiro execução
3. **Linux/macOS: PyPI** — atualizado para usar `gitpr --install` ao invés de apenas rodar `gitpr`
4. **From Source Code** — atualizado para usar `--install` ao invés de apenas `python src/main.py`
5. **Compiling Your Own Executable** — mantido sem alterações
6. **Security** — mantido sem alterações
7. **Configuration Reference** — mantido sem alterações

### Adaptações por idioma
- Títulos e descrições traduzidos mantendo a terminologia existente de cada variante
- Output do wizard traduzido com os 4 passos do `--install` (skill templates, Git Hooks, MCP, API key)
- Links da documentação apontando para o locale correspondente (`?lang=pt_br`, `?lang=fr_fr`, etc.)
- Termos regionais preservados (ex: "transferir" vs "baixar" no PT-PT, "encriptação" vs "criptografia")

### Principais diferenças do readme
- O readme foca em um Quick Start minimalista (2 passos)
- O `instalacao.md` expande com todos os modos de instalação (executável, PyPI, source, compilação) para servir como página de referência completa
- O output do wizard foi adaptado para mostrar os 4 passos do `--install` (skill templates, Git Hooks, MCP, API key)
