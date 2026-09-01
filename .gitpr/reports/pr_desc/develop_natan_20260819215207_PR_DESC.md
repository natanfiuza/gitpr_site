# 🚀 Sugestão de Pull Request

**Mensagem de Commit Recomendada:**
```text
fix: move controles do header para menu mobile em telas pequenas
```

---

## 🎯 Resumo

Em telas muito pequenas (≤425px), o cabeçalho ficava sobrecarregado com GitHub, seletor de idioma, alternador de tema e botão de menu. Esta mudança move esses controles para dentro do menu lateral móvel, deixando o cabeçalho apenas com a marca e o ícone de hambúrguer. Além disso, corrige a sincronização do tema entre as instâncias do alternador.

## 🛠️ Mudanças Técnicas

- Adiciona classe `max-[425px]:hidden` condicional aos controles do cabeçalho quando o menu móvel está presente (via `compact_header_class`).
- Altera o `ThemeToggle` para derivar o estado do tema do DOM (`document.documentElement.classList.contains('dark')`), garantindo consistência entre múltiplas instâncias.
- Reestrutura o `DocsLayout`: adiciona `flex flex-col` ao `aside` e separa o conteúdo com `flex-1 overflow-y-auto`, permitindo que o seletor de idioma fique no topo e GitHub/alternador de tema no rodapé do menu móvel (apenas ≤425px).
- Importa `LanguageSelector` e `ThemeToggle` no `DocsLayout`.

## ⚠️ Impacto/Avisos

- Nenhuma mudança em banco de dados, variáveis de ambiente ou dependências.
- Ajustes visuais apenas para viewports ≤425px; comportamento em telas maiores permanece inalterado.
- Verificar se o `ThemeToggle` continua funcionando corretamente quando outras páginas usam a instância do cabeçalho sem o menu móvel.

close #52