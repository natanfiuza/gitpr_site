# 🚀 Sugestão de Pull Request

**Mensagem de Commit Recomendada:**
```text
fix: corrige busca com caracteres multibyte e move endpoint para /search
```

---

.gitpr.commit.md: gitpr.commit.md

🎯 Resumo

A busca da documentação retornava erro 500 ao encontrar conteúdos com acentos ou caracteres multibyte: o código usava funções de string orientadas a byte, quebrava a codificação UTF-8 no snippet e fazia o `json_encode` lançar exceção durante a resposta. Este PR corrige a manipulação (funções `mb_*`), adiciona proteção extra para JSON com bytes inválidos e remove a dependência de `/api/*`, rota que o edge de hospedagem redireciona em loop. O endpoint agora é `/search` e o frontend exibe uma mensagem amigável em caso de falha.

🛠️ Mudanças Técnicas

- Troca `stripos`/`substr` por `mb_stripos`/`mb_substr` no `DocsController`, impedindo a divisão de caracteres multibyte ao gerar o trecho de busca.
- Adiciona `continue` quando a posição da correspondência não é encontrada e aplica `JSON_INVALID_UTF8_SUBSTITUTE` na resposta JSON.
- Move a rota de `/api/search` para `/search` no `routes/web.php`, com comentário sobre o redirecionamento 307 do edge para prefixos `/api/*`.
- Atualiza `SearchBar.vue` para consumir `/search`, limpar o estado de erro a cada nova digitação e exibir aviso quando a busca falha.
- Inclui as páginas `scm-multiforge` e `suggested-reviewers` no `menu.json` em todos os idiomas suportados.
- Adiciona testes de feature cobrindo: busca bem-sucedida, conteúdo acentuado em `pt_BR`, ausência de resultados e a não-existência de `/api/search` (regressão).

⚠️ Impacto/Avisos

- A rota pública de busca muda de `/api/search` para `/search`; integrações externas que ainda chamam o prefixo `/api/*` precisam ser ajustadas.
- Não há mudanças de banco de dados, variáveis de ambiente ou dependências neste PR.
- Conteúdo com bytes UTF-8 inválidos não causará mais erro 500; o trecho correspondente exibirá `�` (U+FFFD) quando a substituição for aplicada.

close #56