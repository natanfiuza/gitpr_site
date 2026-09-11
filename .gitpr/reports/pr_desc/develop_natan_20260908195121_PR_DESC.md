# 🚀 Sugestão de Pull Request

**Mensagem de Commit Recomendada:**
```text
fix: escapa termos de busca e rola até o primeiro destaque
```

---

🎯 Resumo

Corrige o destaque de termos pesquisados no MarkdownViewer quando o termo contém caracteres especiais de regex (ex.: `C++`, `C#`, parênteses) e adiciona rolagem automática até a primeira ocorrência destacada ao abrir um link com `?mark=`.

🛠️ Mudanças Técnicas

- Adiciona a função `escape_regex` para escapar metacaracteres do termo antes de montar a `RegExp`, evitando erros ou correspondências incorretas.
- Adiciona a função `scroll_to_first_mark` que identifica a primeira tag `<mark>` no conteúdo renderizado e utiliza `scrollIntoView` com rolagem suave.
- Integra a chamada de `scroll_to_first_mark` no `watch` de `parsed_content`, logo após o `highlight_marked_text`, garantindo o comportamento ao abrir resultados de busca.

⚠️ Impacto/Avisos

- Sem mudanças de banco de dados, dependências ou variáveis de ambiente.
- Impacto visual de UX: ao abrir uma URL com parâmetro `mark`, a página agora rola automaticamente até o primeiro resultado destacado.
- Melhora a robustez da pesquisa para termos com caracteres especiais.

close #58