---
name: update-tip-tools
description: Atualiza as dicas úteis da newsletter em public/content/tip_tools.json a partir da documentação técnica (public/content/**/*.md), nos 5 idiomas do site. Use quando o usuário pedir para atualizar, criar ou revisar as dicas da newsletter.
---

# Atualizar Dicas da Newsletter (tip_tools.json)

Varrer a documentação técnica do site e manter `public/content/tip_tools.json` com dicas úteis para as newsletters, nos 5 idiomas do site (en, pt_br, pt_pt, fr, es).

## Passos

1. **Ler o estado atual.**
   - Se `public/content/tip_tools.json` existir, carregue as dicas atuais e **preserve** o campo `used` das dicas existentes (uma dica usada nunca volta para `false`).
   - Se não existir, comece do zero.

2. **Varrer a documentação.**
   - Percorra todos os arquivos `.md` em `public/content/**/*.md`.
   - **Exclua**: `public/content/newsletter/**` e os arquivos `relatorio*.md`.

3. **Extrair candidatos de dicas**, usando os critérios:
   - Dicas de uso (flags, atalhos, truques de linha de comando).
   - Features existentes (ex.: chat interativo, MCP, linter, plugins, skills, commit semântico).
   - Curiosidades do GitPR.
   - Seja criativo, mas **não invente features que não estejam na documentação** — toda dica deve ter origem real em um arquivo.

4. **Redigir cada dica nos 5 idiomas.**
   - Formato de cada dica:
     ```json
     {
         "id": "tip_N",
         "used": false,
         "source": "docs/chat-interativo.md",
         "content": {
             "en": "...", "pt_br": "...", "pt_pt": "...", "fr": "...", "es": "..."
         }
     }
     ```
   - `id` sequencial (`tip_1`, `tip_2`, ...), estável entre execuções; `source` aponta para o arquivo de origem.

5. **Mesclar com o JSON existente.**
   - Manter as dicas com `used=true` intactas.
   - Adicionar dicas novas com `used=false`; deduplicar por `id` e por conteúdo.
   - Escrever o JSON ordenado e formatado (indentação de 4 espaços).

6. **Verificar.**
   - Toda dica tem os 5 idiomas preenchidos.
   - `used` nunca regrediu de `true` para `false`.
   - `public/content/menu.json` não foi alterado.

7. **Relatório da tarefa** (regra do CLAUDE.md):
   - Escreva `docs/claude-code/reports/{branch}/YYYY-MM-DD_{branch}_update_tip_tools.md` com o resumo: quantas dicas adicionadas/atualizadas, fontes usadas e idiomas.

## Observações

- O JSON é consumido pela skill `generate-newsletter-body`, que marca as dicas escolhidas com `used=true` para evitar repetição entre newsletters.
- `tip_tools.json` não entra no `menu.json` e não é indexado pela busca.
- Se a documentação não tiver material novo, não force dicas repetidas — é melhor adicionar poucas e boas.
