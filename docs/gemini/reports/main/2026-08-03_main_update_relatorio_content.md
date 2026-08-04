# Report: Atualização dos Relatórios de Status (v0.0.6 / v0.0.31)

**Data:** 2026-08-03  
**Tarefa:** Atualização de `public/content/relatorio.md` e seus pares i18n (`relatorio.pt_br.md`, `relatorio.pt_pt.md`, `relatorio.es.md`, `relatorio.fr.md`).

---

## 📌 Resumo Executivo

Atualizados os ficheiros/arquivos de conteúdo do relatório de status do projeto no site (`public/content/relatorio.*.md`) com base no relatório oficial de estado v0.0.6 (`relatorio_estado_v0.0.6.md` - GitPR CLI v0.0.31).

---

## 🎯 Arquivos Modificados / Criados

- `public/content/relatorio.md` (Inglês) — Tradução e atualização completa para v0.0.31
- `public/content/relatorio.pt_br.md` (Português Brasil) — Atualização completa para v0.0.31
- `public/content/relatorio.pt_pt.md` (Português Portugal) — Adaptação e atualização completa para v0.0.31
- `public/content/relatorio.es.md` (Espanhol) — Tradução e atualização completa para v0.0.31
- `public/content/relatorio.fr.md` (Francês) — Tradução e atualização completa para v0.0.31

---

## ✨ Principais Destaques Refletidos nos Relatórios

1. **Dashboard TUI de Métricas Reformulado:** Escopo por repositório (`repo_filter`), varredura assíncrona com `ProgressBar` e correção do F5.
2. **Rastreamento de Duração IA:** Medição em tempo real com `duration_ms` via `time.perf_counter()`.
3. **Exportação Local por Projeto:** CSV e JSON gerados em `./.gitpr/metrics/export/`.
4. **Revalidação de Token GitHub:** Pré-validação `GET /user` e Auto-Reauth gracioso em HTTP 401.
5. **Spinner & Thinking Words:** Delimitador de frases atualizado para ponto e vírgula (`;`).
6. **Quick Start nos READMEs:** Instruções de `pip install gitpr-cli` e `gitpr --install` nos 5 idiomas.
7. **Guia GEMINI.md:** Padrões de desenvolvimento e relatórios de tarefas.
