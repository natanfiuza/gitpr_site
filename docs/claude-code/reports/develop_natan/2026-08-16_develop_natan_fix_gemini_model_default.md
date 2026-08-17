# Relatório: Correção da Inconsistência dos Modelos de IA na Documentação

**Data:** 2026-08-16
**Branch:** `develop_natan`

## Contexto

A varredura da skill `update-tip-tools` (mesmo dia) detectou inconsistência no modelo Gemini padrão: `index.md` afirmava `gemini-2.5-flash`, enquanto `instalacao.md` e `providers.md` afirmavam `gemini-pro-latest`. A pedido do usuário, a correção foi estendida ao drift do DeepSeek, aos nomes das variáveis de ambiente e propagada ao repositório fonte do CLI.

## Fonte da verdade

Verificado no código-fonte do GitPR CLI (`C:\Users\nataniel\projetos\python\gitpr\src\config.py`):

```python
DEFAULT_CONFIG = {
    "GEMINI_API_MODEL_PRIMARY": "gemini-pro-latest",
    "GEMINI_API_MODEL_SECONDARY": "gemini-flash-lite-latest",
    "DEEPSEEK_API_MODEL_PRIMARY": "deepseek-v4-pro",
    "DEEPSEEK_API_MODEL_SECONDARY": "deepseek-v4-flash",
    ...
}
```

## Correção aplicada

**Site (`gitpr_site`) — 34 arquivos, 83 linhas** (en, pt_br, pt_pt, es, fr):

| Correção | Antes | Depois | Arquivos |
| --- | --- | --- | --- |
| Default Gemini | `gemini-2.5-flash` | `gemini-pro-latest` | `index.*`, `docs/readme.*`, `docs/providers-ia.*`, `docs/i18n_explanation.*` |
| Secundário Gemini | `gemini-2.5-flash-lite` | `gemini-flash-lite-latest` | `docs/providers-ia.*` |
| Default DeepSeek | `deepseek-chat` | `deepseek-v4-pro` | `index.*`, `docs/readme.*` |
| DeepSeek primário/secundário | `deepseek-chat` (único) | `deepseek-v4-pro` / `deepseek-v4-flash` | `docs/providers-ia.*` |
| Env vars Gemini | `GEMINI_API_MODEL`, `SECONDARY_GEMINI_API_MODEL` | `GEMINI_API_MODEL_PRIMARY`, `GEMINI_API_MODEL_SECONDARY` | `docs/providers-ia.*`, `instalacao.*`, `providers.*`, `docs/readme.*` |
| Env vars DeepSeek | `DEEPSEEK_API_MODEL`, `SECONDARY_DEEPSEEK_API_MODEL` | `DEEPSEEK_API_MODEL_PRIMARY`, `DEEPSEEK_API_MODEL_SECONDARY` | idem |

**Repositório CLI (`python/gitpr`) — 17 arquivos**, mesmas substituições em:

- `README.md` + 4 variantes traduzidas (fonte do `docs/readme.*` do site)
- `docs/providers-ia.*` (5 variantes)
- `docs/i18n_explanation.*` (5 variantes)
- `CLAUDE.md` (seção Environment Variables e AI Providers)

Verificado que `docs/readme.md` do site e `README.md` do CLI ficaram em sincronia.

`tip_tools.json` e `menu.json` não foram alterados.

## Fora do escopo (não corrigido, com justificativa)

1. **`relatorio.*` no site** e **`docs/reports/*.md` no CLI**: snapshots históricos de relatórios — editar quebraria o histórico e seria sobrescrito pelo próximo `update-relatorio`.
2. **`src/gitpr_cli.egg-info/PKG-INFO`** (CLI): artefato gerado no build a partir do README — será regenerado no próximo build.
3. **Nota:** o working tree do CLI já tinha alterações pré-existentes não relacionadas (`docs/caveman-commit.md` deletado, `.claude/skills/caveman-commit/` não rastreado) — não foram tocadas por esta tarefa.
