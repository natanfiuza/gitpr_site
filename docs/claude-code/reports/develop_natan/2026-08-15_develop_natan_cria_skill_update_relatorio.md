# Relatório: Criação da skill local `update-relatorio`

**Data:** 2026-08-15
**Branch:** develop_natan

## Objetivo

Criar uma skill local que, sempre que acionada, atualiza o relatório de status do GitPR no site a partir do relatório mais recente do repositório GitPR CLI, e traduz nos idiomas suportados.

## O que foi feito

- Criada a skill `.claude/skills/update-relatorio/SKILL.md` com o fluxo completo:
  1. Descobrir o relatório fonte mais recente em `C:\Users\nataniel\projetos\python\gitpr\docs\reports\` (maior versão semântica de `relatorio_estado_v*.md`; hoje: `v0.0.10`).
  2. Comparar com a versão publicada em `public/content/relatorio.md` e parar se já estiver atualizado.
  3. Atualizar o arquivo base em inglês (`relatorio.md`) — tradução integral do fonte PT-BR, preservando estrutura, emojis, termos técnicos e links.
  4. Traduzir para `relatorio.pt_br.md`, `relatorio.pt_pt.md`, `relatorio.es.md` e `relatorio.fr.md`.
  5. Verificar cabeçalhos, idiomas e estrutura.
  6. Gerar relatório da tarefa conforme regra do CLAUDE.md.

## Descobertas relevantes documentadas na skill

- Convenção de versões: o H1 do site espelha o H1 do relatório fonte (versão do CLI + data, ex.: `GitPR CLI — v0.0.35 (2026-08-11)`), enquanto a seção de novidades usa a versão do arquivo de relatório (ex.: `v0.0.10`).
- O arquivo `relatorio_estado_ GitPR-CLI.md` (com espaço no nome) não tem versão e deve ser ignorado na descoberta.
- O site publica a versão baseada em `v0.0.8` (CLI v0.0.33); o fonte mais recente é `v0.0.10` (CLI v0.0.35) — há atualização pendente.

## Pendência

A skill ainda não foi executada. Para atualizar o site agora, acionar `/update-relatorio`.
