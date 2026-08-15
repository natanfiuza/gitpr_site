---
name: update-relatorio
description: Atualiza o relatório de status do GitPR no site — public/content/relatorio.md (inglês) e traduções pt_br, pt_pt, es, fr — a partir do relatório mais recente em C:\Users\nataniel\projetos\python\gitpr\docs\reports. Use quando o usuário pedir para atualizar, sincronizar ou gerar o relatório do site com a versão mais atual do GitPR.
---

# Atualizar Relatório do Site

Sincronizar `public/content/relatorio.md` e suas traduções com o relatório de estado mais recente do repositório GitPR CLI.

## Passos

1. **Descobrir o relatório fonte mais recente.**
   - Liste `C:\Users\nataniel\projetos\python\gitpr\docs\reports\` e filtre por `relatorio_estado_v*.md`.
   - Ignore arquivos sem versão (ex.: `relatorio_estado_ GitPR-CLI.md` — tem espaço no nome).
   - Escolha o arquivo com a maior versão semântica (ex.: `v0.0.10` > `v0.0.9`).
   - Se o diretório não existir ou estiver vazio, avise o usuário e pare.

2. **Comparar versões.**
   - Leia o cabeçalho de `public/content/relatorio.md` e identifique a versão publicada (ex.: "What's New in This Version (v0.0.8)").
   - Se a versão do relatório fonte for igual à publicada, pare e informe que o site já está atualizado — não reescreva nada.

3. **Atualizar o arquivo base em inglês** (`public/content/relatorio.md`).
   - Traduza integralmente o relatório fonte (PT-BR → EN), preservando:
     - a estrutura exata de seções, separadores `---` e emojis;
     - tabelas, blocos de código, nomes de módulos, arquivos, flags, funções e variáveis de ambiente (não traduzir termos técnicos);
     - links externos.
   - Mantenha a convenção atual do arquivo:
     - O cabeçalho H1 espelha o H1 do relatório fonte (versão do relatório + data, ex.: `GitPR CLI — v0.0.10 (2026-08-11)` — desde a v0.0.9 o fonte usa a versão do relatório no H1, não a do CLI).
     - A seção de novidades mantém a versão do arquivo de relatório (ex.: `What's New in This Version (v0.0.10)`).
   - Não resuma nem omita itens: o arquivo do site deve refletir todo o conteúdo do relatório fonte.

4. **Traduzir para os outros idiomas**, a partir da versão em inglês atualizada:
   - `relatorio.pt_br.md` → PT-BR
   - `relatorio.pt_pt.md` → PT-PT
   - `relatorio.es.md` → ES
   - `relatorio.fr.md` → FR
   - Preserve em cada arquivo o estilo de título já usado (ex.: "Relatório de Status do Projeto" em PT-BR, "Informe de Estado del Proyecto" em ES, "Rapport de Statut du Projet" em FR), além de estrutura, emojis e termos técnicos.
   - Atualize a data do cabeçalho com a data do relatório fonte.

5. **Verificar.**
   - Confirme que os 5 arquivos têm o cabeçalho correto no idioma certo e a mesma estrutura de seções.
   - Verifique que nenhum arquivo ficou com trechos em outro idioma.

6. **Relatório da tarefa** (regra do CLAUDE.md):
   - Escreva `docs/claude-code/reports/{branch}/YYYY-MM-DD_{branch}_atualiza_relatorio_site.md` com o resumo: versão antiga → nova, data, arquivos alterados e idiomas.

## Observações

- O relatório fonte está em PT-BR. A versão do arquivo (`v0.0.10`) difere da versão do CLI (`0.0.35`) citada dentro dele — não confunda as duas.
- A tradução PT-BR parte do inglês atualizado, que por sua vez é tradução fiel do fonte — o resultado deve ser equivalente ao relatório fonte em conteúdo.
