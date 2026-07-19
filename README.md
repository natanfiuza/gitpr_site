# 📚 Documentação Oficial - GitPR CLI

Repositório contendo o código-fonte do site oficial de documentação do **GitPR CLI**, uma ferramenta avançada de linha de comando que utiliza Inteligência Artificial para automatizar code reviews e gerenciar fluxos do Git. 

O site foi projetado para ser leve, rápido e de fácil manutenção, extraindo e renderizando conteúdos diretamente de arquivos Markdown.

## 🛠️ Stack Tecnológica

A arquitetura do projeto foi construída utilizando um ecossistema moderno para garantir uma navegação fluida em Single Page Application (SPA):

* **Backend:** Laravel 13 (rodando em PHP 8.3)
* **Frontend:** Vue.js 3 integrado via Inertia.js
* **Estilização:** Tailwind CSS (com design system customizado e dark mode nativo)
* **Parser de Conteúdo:** `markdown-it` com highlight de sintaxe via `highlight.js`

## ⚙️ Arquitetura de Conteúdo

Todo o gerenciamento das páginas ocorre de forma dinâmica. Os arquivos `.md` armazenados no diretório `public/content/` são lidos pelo backend e entregues via Inertia ao Vue, que realiza a renderização da tipografia, injeção de botões interativos (como "Copiar Código") e geração de um Índice Automático (TOC) com suporte a *scroll spy*.
