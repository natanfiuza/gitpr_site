# Relatório: Atualização do Doc git-hooks-locais

**Data:** 2026-08-12
**Branch:** develop_natan
**Plano:** `docs/plans/20260812_atualiza_git_hooks_locais.md`

## Resumo

Sincronização do doc `git-hooks-locais` do GitPR CLI (repositório Python) com as versões traduzidas publicadas no site GitPR (Laravel). A origem (`C:\Users\nataniel\projetos\python\gitpr\docs\`) foi atualizada em 2026-08-12 com um novo parágrafo na seção 3 que ainda não existia no site (defasado desde 2026-07-05 / 2026-07-13). Foram atualizados 5 arquivos em 5 idiomas (EN, PT-BR, PT-PT, ES, FR), resultando em byte-identidade com a origem.

## Arquivos Modificados

### `public/content/docs/git-hooks-locais.md` (5 idiomas)

**Fonte:** `C:\Users\nataniel\projetos\python\gitpr\docs\git-hooks-locais.md` (e variantes traduzidas)

**Parágrafo adicionado** (seção 3 — "Preserving Manual Flow", após o exemplo de `git commit -m`):

O parágrafo documenta que mensagens de merge e geradas pelo próprio git (`git pull`/`git merge`, `git merge --squash`, `git commit --amend`/`-c`/`-C`) também são preservadas: o hook **desativa a IA silenciosamente** nesses casos, sem nunca tocar na mensagem de merge.

| Idioma | Arquivo | Trecho inicial do parágrafo |
|--------|---------|------------------------------|
| EN | `git-hooks-locais.md` | "Merge and git-generated messages are also preserved..." |
| PT-BR | `git-hooks-locais.pt_br.md` | "Mensagens de merge e geradas pelo git também são preservadas..." |
| PT-PT | `git-hooks-locais.pt_pt.md` | "Mensagens de merge e geradas pelo git também são preservadas..." |
| ES | `git-hooks-locais.es_es.md` | "Los mensajes de fusión y generados por git también se preservan..." |
| FR | `git-hooks-locais.fr_fr.md` | "Les messages de fusion et générés par git sont également préservés..." |

**Método:** inserção cirúrgica via Edit, copiando o trecho exato de cada arquivo de origem — não foi feita cópia integral dos arquivos, preservando o restante byte a byte (padrão do relatório de 2026-08-11).

## Verificação de Consistência

- ✅ `diff -q` entre cada par origem × site → **saída vazia nos 5 arquivos** (byte-idênticos)
- ✅ `git diff --stat` → 5 arquivos alterados, 25 inserções (+5 linhas por arquivo: parágrafo de 4 linhas + linha em branco)
- ✅ Parágrafo posicionado corretamente em todos os idiomas: após o fence do exemplo `git commit -m` e antes do separador `---` que precede a seção 4 (Troubleshooting)
- ✅ Traduções preservadas sem reescrita — nenhuma outra linha dos arquivos foi tocada

## Arquivos Não Modificados

- Nenhum outro doc, `menu.json` ou código do site.
- Não há frontmatter nesses docs (o controller lê o markdown cru), então nada adicional era necessário.
