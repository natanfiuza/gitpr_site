# Relatório: Ajustes do Menu Mobile (≤425px) (2026-08-19)

**Tarefa:** Executar o plano `docs/plans/20260819_ajustes-do-menu-mobile.md` — reorganizar a barra superior e o menu lateral em telas com largura ≤ 425px.

**Escopo:** apenas a faixa ≤ 425px, preservando o layout atual em resoluções maiores.

## Diagnóstico

- Em ≤425px a barra superior (`SiteHeader.vue`) exibia o grupo direito completo — GitHub + badge de versão + alternância de tema + seletor de idioma (`w-40`) + sanduíche — ao lado da marca, excedendo a largura disponível e deslocando o ícone de menu.
- O seletor de idioma, o GitHub e o tema viviam **somente** na barra superior; o drawer lateral (`DocsLayout.vue`) continha apenas navegação, sem pontos fixos (topo/base).
- O sufixo `[ CLI ]` da marca estava oculto abaixo de 640px (`hidden sm:inline`), contradizendo o requisito "GitPR [CLI]" em ≤425px.

## Ações executadas

| Arquivo | Alteração |
| --- | --- |
| `resources/js/Components/SiteHeader.vue` | Sufixo `[ CLI ]` agora visível em ≤425px (`max-[425px]:inline`). Novo computed `compact_header_class` aplica `max-[425px]:hidden` a GitHub, link de voltar, `ThemeToggle` e `LanguageSelector` **apenas quando `show_mobile_toggle` é true** (página com menu mobile). Sanduíche permanece `md:hidden`. |
| `resources/js/Pages/DocsLayout.vue` | `<aside>` reestruturado para `flex flex-col`: (1) barra superior fixa com `<LanguageSelector>` (visível só ≤425px), (2) miolo rolável (`flex-1 overflow-y-auto p-6`) com o conteúdo existente, (3) barra inferior fixa agrupando ícone do GitHub + `<ThemeToggle>` (visível só ≤425px). Botão ✕ mantido como estava (`absolute top-6 right-6`, `md:hidden`). |
| `resources/js/Components/ThemeToggle.vue` | `toggle_theme` agora deriva o estado do DOM (`documentElement.classList.contains('dark')`) em vez do ref local — mantém sincronizadas as duas instâncias do componente (header + drawer). |

## Verificação

- `npm run build`: **sucesso** (1141 módulos, 0 erros).
- CSS de produção contém `@media (width<=425px)` com `.max-\[425px\]\:inline`, `:flex` e `:hidden` gerados; o bloco vem **depois** de `.hidden` no arquivo (índices 74951 vs 24137), garantindo a sobrescrita em ≤425px.
- `pr-14` presente no CSS gerado (folga do seletor para o botão ✕).
- Em >425px: barras do drawer usam `hidden` + `max-[425px]:flex` (não renderizam), header mantém todos os controles como antes; entre 426–639px o `[ CLI ]` permanece oculto, como era.
- Diff dos fontes restrito a 3 arquivos (19 + 3 + 87 linhas); reindentação do `<nav>` em `DocsLayout` decorre apenas da nova div wrapper.

## Observações

- **Suposição de escopo:** a limpeza da barra superior em ≤425px foi condicionada a `show_mobile_toggle` (hoje, somente `DocsLayout`). Páginas como `LinterUtility` e newsletter, que não têm menu lateral para receber os controles, permanecem intactas em telas pequenas — escondê-las lá removeria funcionalidade.
- A barra inferior do drawer contém apenas o ícone do GitHub (sem badge de versão), conforme o plano ("ícone do GitHub").
- Pré-existente (fora de escopo): o botão ✕ usa `text-gitpr_text` (quase branco), invisível sobre o fundo claro do drawer no tema claro.
- Artefatos de `public/build/` foram regenerados pelo build de verificação (ficam no working tree para o commit).
- Pendente: validação visual no navegador em ~375px (build estático confirma classes/CSS, não o pixel final).
