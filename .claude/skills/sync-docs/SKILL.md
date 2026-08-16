---
name: sync-docs
description: Sincroniza a documentação técnica do site (public/content/docs) com os arquivos de C:\Users\nataniel\projetos\python\gitpr\docs — verifica arquivos faltando, conteúdo divergente e se os idiomas (en, pt_br, pt_pt, es, fr) de cada tópico estão sincronizados, copiando/atualizando o que estiver dessincronizado. Use quando o usuário pedir para sincronizar, atualizar ou verificar a documentação técnica do site.
---

# Sincronizar Documentação Técnica

Sincronizar `public/content/docs/` com a documentação técnica do repositório GitPR CLI.

## Fontes e destinos

- **Fonte (autoridade):** `C:\Users\nataniel\projetos\python\gitpr\docs\*.md` — somente arquivos do nível superior. Ignorar subdiretórios (`claude-code/`, `extra/`, `gemini/`, `plans/`, `prompts/`, `reports/`) e arquivos não-doc (`gitpr_landing_page.pdf`).
- **Caso especial `readme`:** a fonte é a RAIZ do repo gitpr: `README.md` + variantes → site `docs/readme.*`.
- **Destino:** `public/content/docs/*.md`.

## Mapeamento de sufixos de idioma (CRÍTICO)

Os códigos de locale diferem entre os dois repositórios:

| Idioma | Sufixo na fonte (gitpr) | Sufixo no site (canônico) |
|---|---|---|
| Inglês | `.md` | `.md` |
| PT-BR | `.pt_br.md` | `.pt_br.md` |
| PT-PT | `.pt_pt.md` | `.pt_pt.md` |
| Espanhol | `.es_es.md` | `.es.md` |
| Francês | `.fr_fr.md` | `.fr.md` |

O site resolve `public/content/{page}.{lang}.md` com `lang` ∈ {en, pt_br, pt_pt, fr, es} ([DocsController.php](app/Http/Controllers/DocsController.php) + `LanguageSelector.vue`). Arquivos `.es_es.md`/`.fr_fr.md` no site **não são lidos** (caem no fallback inglês).

**Ao copiar da fonte para o site, sempre renomear `.es_es` → `.es` e `.fr_fr` → `.fr`.**

## Passos

1. **Diagnóstico.** Monte o conjunto de tópicos de cada lado (nome sem sufixo de idioma) e compare, por tópico e por variante, conteúdo com `diff -q`:
   - tópicos presentes só na fonte (faltando no site);
   - tópicos presentes só no site;
   - tópicos comuns com conteúdo divergente (variante por variante, aplicando o mapeamento de sufixos);
   - variantes de idioma faltando em cada tópico do site;
   - tópicos do site sem entrada em `public/content/menu.json`;
   - arquivos legados `.es_es.md`/`.fr_fr.md` no site.

2. **Sincronizar.**
   - **Tópico faltando no site:** copiar da fonte todas as variantes existentes (renomeando sufixos), sem alterar o conteúdo markdown.
   - **Conteúdo divergente:** sobrescrever o arquivo do site com o conteúdo da fonte (variante por variante). Atualizar TODAS as variantes do tópico, não só a inglesa.
   - **Variante de idioma faltando no site:** copiar da fonte se existir. Se a fonte não tiver a variante (tópicos monolíngues: `ARCHITECTURE`, `caveman-commit`, `como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `testar_sem_usar_pypi`), reportar como lacuna — não inventar traduções nem remover variantes que o site já tenha além da fonte.
   - **Arquivos legados `.es_es`/`.fr_fr` no site:** reportar como duplicados obsoletos e sugerir remoção — NÃO remover sem confirmação do usuário.
   - **Tópicos só no site** (ex.: `chat-interativo`): não tocar; reportar como exclusivos do site.
   - **menu.json:** para cada tópico NOVO copiado da fonte, adicionar entrada nas 5 línguas sob a seção "Technical Documentation", no formato `{"title": "▸ <título traduzido>", "path": "docs/<tópico>"}`, mantendo a ordem alfabética aproximada do menu e o título traduzido por idioma (use o título do arquivo traduzido como referência). Tópicos pré-existentes sem entrada no menu: apenas reportar (decisão do usuário).

3. **Verificar.**
   - Cada tópico do site tem as 5 variantes canônicas (`.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md`).
   - `diff -rq` entre fonte e site (aplicando o mapeamento de sufixos) não acusa diferenças nos tópicos comuns.
   - Nenhum sufixo `.es_es`/`.fr_fr` foi criado no site.

4. **Relatório da tarefa** (regra do CLAUDE.md): escrever `docs/claude-code/reports/{branch}/YYYY-MM-DD_{branch}_sync_docs.md` com tópicos adicionados, atualizados, lacunas de idioma e anomalias reportadas.

## Observações

- A fonte é a autoridade de conteúdo; o site nunca deve divergir dela nos tópicos comuns.
- Nunca apagar arquivos do site durante a sincronização (exceto com confirmação explícita no caso dos duplicados `.es_es`/`.fr_fr`).
- Preservar o conteúdo markdown exatamente como está na fonte (emojis, tabelas, links, blocos de código).
