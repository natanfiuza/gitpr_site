# 🚀 Sugestão de Pull Request

**Mensagem de Commit Recomendada:**
```text
fix: corrige alinhamento do conteúdo no DocsLayout
```

---

## 🎯 Resumo

Ajusta o posicionamento do conteúdo principal no layout de documentação, que estava centralizado de forma indevida e desalinhado em relação à barra lateral esquerda. A mudança garante leitura mais fluida e consistência visual entre breakpoints, além de evitar que o texto fique "solto" em telas grandes.

Também foram adicionados artefatos de apoio ao desenvolvimento (ignorados pelo Git) e novos documentos de survey, plano e tutorial.

## 🛠️ Mudanças Técnicas

- Remove `mx-auto` do container de conteúdo em `DocsLayout.vue`, eliminando a centralização automática do bloco.
- Substitui `lg:p-10` por `lg:p-10 lg:pl-6`, reduzindo o padding lateral esquerdo em telas grandes para aproximar o conteúdo da sidebar.
- Mantém `xl:mr-64` para preservar o espaço reservado ao índice lateral em telas extra grandes.
- Atualiza `.gitignore` para ignorar `/.playwright-mcp/` e `.gitpr/conf/issue.yml`.
- Inclui documentação de survey, plano e tutorial (alinhamento de barra esquerda, alinhamento de texto do menu e automação de navegador com Claude Code).

## ⚠️ Impacto/Avisos

- **Banco de dados:** nenhuma migração ou alteração de schema.
- **Variáveis de ambiente:** nenhuma nova variável exigida.
- **Dependências:** nenhuma dependência adicionada ou atualizada.
- **Impacto visual:** alteração restrita ao layout de documentação; revisar em breakpoints `lg` e `xl` para confirmar o alinhamento esperado.
- **Arquivos ignorados:** confirmar que `/.playwright-mcp/` e `.gitpr/conf/issue.yml` não contêm artefatos necessários ao build em outros ambientes.

close #77

---

[![GitPR](https://img.shields.io/badge/GitPR-no_issues-brightgreen)](https://gitpr.natanfiuza.dev.br/)