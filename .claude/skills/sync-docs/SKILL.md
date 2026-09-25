---
name: sync-docs
description: Sincroniza a documentação técnica do site (public/content/docs) com os arquivos de C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs — verifica arquivos faltando, conteúdo divergente e se os idiomas (en, pt_br, pt_pt, es, fr) de cada tópico estão sincronizados, copiando/atualizando o que estiver dessincronizado. Use quando o usuário pedir para sincronizar, atualizar ou verificar a documentação técnica do site.
---

# Sincronizar Documentação Técnica

Sincronizar `public/content/docs/` com a documentação técnica do repositório GitPR CLI.

## Fontes e destinos

- **Fonte (autoridade):** `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\docs\*.md` — somente arquivos do nível superior. Ignorar subdiretórios (`claude-code/`, `extra/`, `gemini/`, `plans/`, `prompts/`, `reports/`, `survey/`, `testing/`, `tutorial/`) e arquivos não-doc (`gitpr_landing_page.pdf`, `logo.png`, `logo.psd`, `progit.pdf`).
- **Caso especial `readme`:** a fonte é a RAIZ do repo gitpr: `C:\Users\nataniel\projetos\pessoal\gitpr_projeto\gitpr\README.md` + variantes → site `docs/readme.*`.
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

## Reescrever links cruzados (CRÍTICO)

Os `.md` do site são cópia fiel de `<gitpr>/docs/` e trazem links relativos entre arquivos (ex.: `[texto](commit-message-ia.md)`). No site eles viram `href` relativo e dão 404, porque as páginas são servidas pela rota catch-all `/{page}?lang={code}`.

Sempre que houver cópia ou atualização de conteúdo, rode ao final:

```bash
python .claude/skills/sync-docs/rewrite_doc_links.py
```

O script varre `public/content/docs/` inteiro, é **idempotente** (rodar duas vezes não altera nada) e reescreve **apenas o destino** do link — o texto visível nunca muda.

| Forma na fonte | Vira |
|---|---|
| `foo.md` | `/docs/foo` |
| `foo.es_es.md`, `foo.fr_fr.md`, `foo.pt_br.md` | `/docs/foo?lang=<idioma do arquivo que contém o link>` |
| `../README.md` | `/docs/readme[?lang=…]` |
| `docs/foo.md` (só nos `readme.*`) | `/docs/foo[?lang=…]` |
| `foo.md#Invocación Directa` | `/docs/foo[?lang=…]#invocacion-directa` (mesmo slugify do site) |
| `plans/*.md`, `tutorial/*.md` | `https://github.com/gitpr-cli/gitpr.git/blob/main/docs/<caminho original>` |
| `https://…`, `mailto:`, `/docs/…`, `#âncora`, `../src/*.py` | inalterado |

**O idioma do destino é o do arquivo que contém o link**, não o do arquivo apontado. Não há checagem de existência: o `DocsController` já cai no conteúdo inglês quando a variante traduzida não existe.

Em `plans/` e `tutorial/` não há tópico no site: o link aponta para o repositório de origem com o **sufixo de origem preservado** (`.es_es`/`.fr_fr`), porque é assim que os arquivos se chamam lá. Não normalize esses sufixos antes de montar a URL do GitHub.

`MarkdownViewer.vue` tem uma regra `link_open` equivalente como rede de segurança (cobre conteúdo ainda não reescrito). Ela é um no-op no que o script já reescreveu — **não** torne o script opcional por causa dela: sem ele o HTML servido (e o `.md` cru, publicamente acessível em `/content/docs/foo.md`) continua com `href` relativo.

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
   - **Variante de idioma faltando no site:** copiar da fonte se existir. Se a fonte não tiver a variante, reportar como lacuna — não inventar traduções nem remover variantes que o site já tenha além da fonte. Monolíngues na fonte (só `.md`): `github-issue-prompt-com-gh` e `version-markers`; `review-pr` tem apenas `.md` e `.pt_br.md`. **Não** são monolíngues os tópicos que a fonte moveu para subdiretórios ignorados: `como_reverter_commit_git_localmente` e `testar_sem_usar_pypi` estão hoje em `extra/`, e por isso o site os tem como exclusivos do site.
   - **Arquivos legados `.es_es`/`.fr_fr` no site:** reportar como duplicados obsoletos e sugerir remoção — NÃO remover sem confirmação do usuário.
   - **Tópicos só no site** (ex.: `chat-interativo`): não tocar; reportar como exclusivos do site.
   - **menu.json:** para cada tópico NOVO copiado da fonte, adicionar entrada nas 5 línguas sob a seção "Technical Documentation", no formato `{"title": "▸ <título traduzido>", "path": "docs/<tópico>"}`, mantendo a ordem alfabética aproximada do menu e o título traduzido por idioma (use o título do arquivo traduzido como referência). Tópicos pré-existentes sem entrada no menu: apenas reportar (decisão do usuário).
   - **Reescrever os links cruzados (sempre, por último):** rodar `python .claude/skills/sync-docs/rewrite_doc_links.py`. Depois de copiar/atualizar as variantes, para que nenhum arquivo recém-copiado fique com link relativo `.md`.

3. **Verificar.**
   - Cada tópico do site tem as 5 variantes canônicas (`.md`, `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md`).
   - `python .claude/skills/sync-docs/rewrite_doc_links.py --check` não acusa pendência (exit 0): todo link cruzado já está na forma do site.
   - `python .claude/skills/sync-docs/rewrite_doc_links.py --check --source <gitpr>` não acusa divergência (exit 0): compara fonte e site **depois** de aplicar o mapeamento de sufixos e a reescrita de links. É este comando que substitui o `diff -rq` da versão anterior — o diff cru acusaria diferença em todo arquivo com link cruzado e nem sequer casaria `.es_es.md` com `.es.md`.
   - Nenhum sufixo `.es_es`/`.fr_fr` foi criado no site.

4. **Relatório da tarefa** (regra do CLAUDE.md): escrever `docs/claude-code/reports/{branch}/YYYY-MM-DD_{branch}_sync_docs.md` com tópicos adicionados, atualizados, lacunas de idioma e anomalias reportadas.

## Observações

- A fonte é a autoridade de conteúdo; o site nunca deve divergir dela nos tópicos comuns.
- Nunca apagar arquivos do site durante a sincronização (exceto com confirmação explícita no caso dos duplicados `.es_es`/`.fr_fr`).
- Preservar o conteúdo markdown exatamente como está na fonte (emojis, tabelas, blocos de código) — **exceto o destino dos links cruzados**, que é reescrito pelo `rewrite_doc_links.py` para a URL do site. O texto visível dos links nunca muda.
