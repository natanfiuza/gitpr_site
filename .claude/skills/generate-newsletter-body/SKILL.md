---
name: generate-newsletter-body
description: Gera o corpo da newsletter (public/content/newsletter/{version}/newsletter_body.md + 5 idiomas) a partir de public/content/relatorio.md, selecionando dicas não usadas de public/content/tip_tools.json. Use quando o usuário pedir para gerar, criar ou atualizar o corpo da newsletter.
---

# Gerar Corpo da Newsletter

Gerar `public/content/newsletter/{version}/newsletter_body.{lang}.md` nos 5 idiomas do site (en, pt_br, pt_pt, fr, es) a partir do relatório de status, com novidades da versão, como usar e dicas úteis.

## Passos

1. **Determinar a versão.**
   - Leia `public/content/relatorio.md` e extraia a versão do GitPR CLI do campo **Current version** com regex `\d+\.\d+\.\d+` (ex.: `0.0.35`).
   - A versão da newsletter deve ser a versão do CLI citada no relatório — não a versão do relatório em si (H1).

2. **Ler o relatório nos 5 idiomas.**
   - `relatorio.md` (en), `relatorio.pt_br.md`, `relatorio.pt_pt.md`, `relatorio.es.md`, `relatorio.fr.md`.
   - O inglês é o mestre: se uma variante não existir, use o inglês como base para traduzir.

3. **Extrair o conteúdo por idioma**, mantendo apenas:
   - **Novidades da versão** (seção "What's New" / equivalente traduzido).
   - **Como usar** (instalação/uso relevantes à versão — apenas o essencial, sem duplicar a doc completa).
   - **Dicas úteis**: escolher apenas 1 dica com `used=false` de `public/content/tip_tools.json`, no idioma correspondente, e marcar a dica escolhida com `used=true` no JSON (uma por edição rende mais versões antes de esgotar o banco de dicas).

4. **Gerar os arquivos.**
   - `public/content/newsletter/{version}/newsletter_body.md` + `.pt_br.md` + `.pt_pt.md` + `.es.md` + `.fr.md`.
   - Estrutura sugerida:
     ```markdown
     # GitPR {version} — Novidades
     ## Novidades desta versão
     ...
     ## Como usar
     ...
     ## Dicas úteis
     ...
     ```
   - Não incluir links quebrados; conteúdo deve funcionar em e-mail (evite HTML embutido e classes CSS).
   - Se `{version}` já existir em `public/content/newsletter/`, **pergunte ao usuário** antes de sobrescrever.

5. **Verificar.**
   - Os 5 arquivos têm a mesma estrutura de seções, cada um no seu idioma.
   - `public/content/menu.json` **não foi alterado** (a newsletter fica fora do menu por design).
   - As dicas usadas foram marcadas `used=true` em `tip_tools.json`.

6. **Relatório da tarefa** (regra do CLAUDE.md):
   - Escreva `docs/claude-code/reports/{branch}/YYYY-MM-DD_{branch}_generate_newsletter.md` com o resumo: versão, arquivos gerados, dicas consumidas (ids) e idiomas.

## Observações

- O corpo é enviado pelo comando `php artisan newsletter:send` (envio manual temporário), que resolve o idioma de cada inscrito com fallback para inglês.
- A versão do arquivo de relatório (H1, ex.: `v0.0.10`) difere da versão do CLI (ex.: `0.0.35`) citada no campo Current version — a newsletter usa a versão do CLI.
- Se `tip_tools.json` não tiver dicas suficientes, rode antes a skill `update-tip-tools`.
