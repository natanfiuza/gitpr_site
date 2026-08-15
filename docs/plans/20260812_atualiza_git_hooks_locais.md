# Atualizar `git-hooks-locais` no site a partir da origem

**Objetivo:** Sincronizar o doc `git-hooks-locais` do repositório da CLI (origem: `C:\Users\nataniel\projetos\python\gitpr\docs\`) com a cópia publicada no site (`public/content/docs/`), em todos os 5 idiomas suportados (EN, PT-BR, PT-PT, ES-ES, FR-FR).

## Contexto

O doc da origem foi atualizado em 2026-08-12, mas a cópia do site está defasada (2026-07-05 / 2026-07-13). Diferença verificada por `diff -u` em todos os 5 idiomas: **exatamente 1 parágrafo novo** (na seção 3, após o exemplo de `git commit -m`) existe na origem e falta no site. Todo o resto é byte-idêntico.

O parágrafo documenta que mensagens geradas pelo próprio git (`git pull`/`git merge`, `git merge --squash`, `git commit --amend`/`-c`/`-C`) desativam silenciosamente a IA do hook, preservando a mensagem de merge.

Não há frontmatter em nenhum dos lados (o controller lê o markdown cru), então nada mais precisa mudar.

## Mudanças

### 1. Inserir o parágrafo faltante nos 5 arquivos do site

Em cada arquivo abaixo, inserir — **após o fence ``` que fecha o exemplo `git commit -m "..."` e antes do separador `---` que precede a seção 4** — o texto exato da origem (UTF-8, LF):

| Arquivo | Parágrafo (da origem) |
|---|---|
| `public/content/docs/git-hooks-locais.md` | *"Merge and git-generated messages are also preserved. When the message comes from git itself — \`git pull\`/\`git merge\` (source "merge"), \`git merge --squash\` (source "squash"), or \`git commit --amend\`/\`-c\`/\`-C\` (source "commit") — the hook \*\*silently disables AI\*\*, never touching the merge message."* |
| `public/content/docs/git-hooks-locais.pt_br.md` | *"Mensagens de merge e geradas pelo git também são preservadas. Quando a mensagem vem do próprio git — \`git pull\`/\`git merge\` (origem "merge"), \`git merge --squash\` (origem "squash") ou \`git commit --amend\`/\`-c\`/\`-C\` (origem "commit") — o hook \*\*desativa a IA silenciosamente\*\*, sem nunca tocar na mensagem de merge."* |
| `public/content/docs/git-hooks-locais.pt_pt.md` | idem ao pt_br (texto idêntico na origem) |
| `public/content/docs/git-hooks-locais.es_es.md` | *"Los mensajes de fusión y generados por git también se preservan. Cuando el mensaje viene del propio git — \`git pull\`/\`git merge\` (origen "merge"), \`git merge --squash\` (origen "squash") o \`git commit --amend\`/\`-c\`/\`-C\` (origen "commit") — el hook \*\*desactiva la IA de forma silenciosa\*\*, sin tocar nunca el mensaje de fusión."* |
| `public/content/docs/git-hooks-locais.fr_fr.md` | *"Les messages de fusion et générés par git sont également préservés. Lorsque le message provient de git lui-même — \`git pull\`/\`git merge\` (origine « merge »), \`git merge --squash\` (origine « squash ») ou \`git commit --amend\`/\`-c\`/\`-C\` (origine « commit ») — le hook \*\*désactive silencieusement l'IA\*\*, sans jamais toucher au message de fusion."* |

**Método:** inserção cirúrgica via Edit (não `cp` do arquivo inteiro), preservando o restante byte a byte — alinhado com o padrão do relatório de 2026-08-11 ("adicionar apenas seções faltantes traduzidas, preservar traduções existentes"). Os textos exatos acima foram extraídos dos diffs reais; na execução, copiar o trecho diretamente dos arquivos de origem para evitar erro de transcrição.

### 2. Relatório (regra do CLAUDE.md)

Criar `docs/claude-code/reports/develop_natan/2026-08-12_develop_natan_atualiza_git_hooks_locais.md` seguindo o formato dos relatórios existentes (ex.: `2026-08-11_develop_natan_atualize_documentacao_tecnica.md`): data, branch, resumo, tabela de arquivos modificados por idioma com o parágrafo adicionado, e seção de verificação.

## Verificação

1. `diff` entre cada par origem × site → **saída vazia** para os 5 arquivos (site passa a ser byte-idêntico à origem).
2. `git diff --stat` mostra apenas os 5 arquivos + plano + relatório, com adições limitadas ao parágrafo (≈5 linhas por arquivo).
3. Conferir que o parágrafo aparece nos 5 idiomas na posição correta (antes da seção "4. Troubleshooting").

## Fora de escopo

- Sem commit (o usuário não pediu; deixar as mudanças no working tree).
- Nenhum script de sync novo — o processo manual segue o padrão documentado em `docs/prompts_sync.md`.
- Sem mudanças em outros docs, menu.json ou código do site.
