# Relatório: Atualização de Documentação Técnica

**Data:** 2026-08-11
**Branch:** develop_natan
**Plano:** `docs/plans/20260811_atualize_documentacao_tecnica_.md`

## Resumo

Sincronização dos arquivos de documentação técnica do GitPR CLI (repositório Python) com as versões traduzidas publicadas no site GitPR (Laravel). Foram atualizados 20 arquivos em 5 idiomas (EN, PT-BR, PT-PT, ES, FR).

## Arquivos Modificados

### 1. `public/content/docs/mcp-annotations.md` (5 idiomas)

**Fonte:** `C:\Users\nataniel\projetos\python\gitpr\docs\mcp-annotations.md`

**Seção adicionada:** `## 🔧 Direct CLI Invocation` (e traduções equivalentes)

A seção documenta a flag `--tool` que permite invocar ferramentas MCP diretamente do terminal, ignorando o transporte MCP. Inclui exemplos de uso, observação sobre anotações não serem aplicadas no modo CLI, e link para a documentação completa de integração MCP.

| Idioma | Arquivo | Seção |
|--------|---------|-------|
| EN | `mcp-annotations.md` | `## 🔧 Direct CLI Invocation` |
| PT-BR | `mcp-annotations.pt_br.md` | `## 🔧 Invocação Direta via CLI` |
| PT-PT | `mcp-annotations.pt_pt.md` | `## 🔧 Invocação Direta via CLI` |
| ES | `mcp-annotations.es_es.md` | `## 🔧 Invocación Directa por CLI` |
| FR | `mcp-annotations.fr_fr.md` | `## 🔧 Invocation Directe par CLI` |

### 2. `public/content/docs/mcp-prompts.md` (5 idiomas)

**Fonte:** `C:\Users\nataniel\projetos\python\gitpr\docs\mcp-prompts.md`

**Seção adicionada:** `## 🔧 CLI Equivalents` (e traduções equivalentes)

A seção apresenta uma tabela de equivalência entre prompts MCP e comandos CLI `--tool`, demonstrando como obter os mesmos resultados do terminal. Inclui nota de que `--tool` invoca apenas ferramentas, não prompts.

| Idioma | Arquivo | Seção |
|--------|---------|-------|
| EN | `mcp-prompts.md` | `## 🔧 CLI Equivalents` |
| PT-BR | `mcp-prompts.pt_br.md` | `## 🔧 Equivalentes via CLI` |
| PT-PT | `mcp-prompts.pt_pt.md` | `## 🔧 Equivalentes via CLI` |
| ES | `mcp-prompts.es_es.md` | `## 🔧 Equivalentes por CLI` |
| FR | `mcp-prompts.fr_fr.md` | `## 🔧 Équivalents par CLI` |

### 3. `public/content/docs/mcp-integration.md` (5 idiomas)

**Fonte:** `C:\Users\nataniel\projetos\python\gitpr\docs\mcp-integration.md`

**Alterações:**

#### Inglês (EN)
- **Seção adicionada:** `## Direct CLI Invocation` — documenta invocação direta de ferramentas via `--tool`, exemplos com/sem parâmetros, e nota sobre Windows Command Prompt.
- Seção posicionada entre Quick Install e Available Tools, igual ao fonte.

#### Traduções (PT-BR, PT-PT, ES, FR) — 3 alterações cada:
1. **Quick Install:** Adicionada linha `gitpr-mcp --install claude-code # Cria .mcp.json`
2. **Editor Configuration:** Adicionada seção `### Claude Code` com JSON de configuração para `.mcp.json`
3. **Direct CLI Invocation:** Adicionada seção completa traduzida (com nota sobre Windows)

### 4. `public/content/index.md` (5 idiomas)

**Fonte:** `C:\Users\nataniel\projetos\python\gitpr\README.md`

**Nota:** Os arquivos `index.md` são landing pages condensadas, não traduções literais do README. Foram atualizados apenas fatos obsoletos:

| Alteração | Antes | Depois |
|-----------|-------|--------|
| Modelo Gemini padrão | `gemini-pro-latest` | `gemini-2.5-flash` |
| Modelo DeepSeek padrão | `deepseek-v4-pro` | `deepseek-chat` |
| Idiomas suportados | 2 idiomas (par EN+nativo) | **5 idiomas** (todos listados) |

## Verificação de Consistência

- ✅ Todas as 5 variantes de idioma têm as mesmas seções estruturais
- ✅ Links entre documentos usam `?lang=` corretamente por idioma
- ✅ Zero referências a modelos obsoletos (`gemini-pro-latest`, `deepseek-v4-pro`)
- ✅ Todos os links de `mcp-annotations` e `mcp-prompts` apontam para `mcp-integration#direct-cli-invocation`
- ✅ Seção `### Claude Code` presente nos 5 idiomas de `mcp-integration`

## Arquivos Não Modificados

Nenhum arquivo fora do escopo foi alterado. As traduções existentes (textos, termos, estilo) foram preservadas — apenas seções faltantes foram adicionadas e fatos obsoletos corrigidos.
