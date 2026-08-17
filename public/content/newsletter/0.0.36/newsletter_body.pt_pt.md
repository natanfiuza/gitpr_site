# GitPR 0.0.36 — Novidades

## Novidades desta versão

- **Correção de Seleção e Erros no Staging (`stage_files`):** A TUI de staging passa a ler a seleção real de `SelectionList.selected` (toggles individuais respeitados) e `stage_files()` devolve `(success, error_message)` — falhas do `git add` apresentam o erro real do git em vez de uma falsa mensagem de sucesso. O staging passou a acontecer uma única vez por fluxo.
- **Skip de Mensagem IA em Commits Gerados pelo Git:** Os hooks `prepare-commit-msg` (5 variantes de idioma) passam a saltar todas as fontes geradas pelo git (`merge`, `squash`, `amend`, `commit` — antes só `message`), com verificação belt-and-braces de `.git/MERGE_HEAD`. `git pull`/`git merge` já não corrompem o `.git/MERGE_MSG` com mensagem de IA. Hooks com auto-sync para a v0.0.2.
- **Traduções de Estado de Ficheiro:** Labels de estado ("Modified", "Deleted", "New") traduzidos nos pacotes es, fr, pt_br e pt_pt — cobertura pt_BR subiu para 507 chaves.
- **Documentação Multilingue Expandida e Sincronizada:** `docs/pr-descricao-padrao.md` reescrito em EN canónico + 4 locales com secção de publicação; `docs/mcp-integration.md` sincronizado nos 5 idiomas; `docs/git-hooks-locais.md` documenta o skip de merge-source nos 5 idiomas.
- **Novo Template MCP:** `templates/gitpr.mcp-jsonrpc-calls.md` — referência de chamadas JSON-RPC para as ferramentas MCP.

## Como usar

Atualize via PyPI:

```
pip install --upgrade gitpr-cli
```

Ou descarregue o binário standalone em [GitHub Releases](https://github.com/natanfiuza/gitpr/releases). Os hooks `prepare-commit-msg` sincronizam automaticamente para a v0.0.2 — não é necessário nenhum passo manual.

Veja as correções em ação:

```
gitpr              # fluxo de publicação: o modal de staging respeita a sua seleção e mostra erros reais do git
git merge <branch> # a mensagem de IA já não toca no .git/MERGE_MSG
```

## Dicas úteis

Com os hooks instalados (`gitpr -ih`), um simples `git commit` abre o editor com a mensagem da IA preenchida. Mas o GitPR sabe quando sair de cena: `-m`, merges, squashes e amends são detetados e a IA fica em silêncio — as suas mensagens nunca são sobrescritas.
