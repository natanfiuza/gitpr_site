# 🚀 Plano de Desenvolvimento: Site Documentação GitPR CLI

## 📌 Visão Geral
Construção do site de documentação do **GitPR CLI**, inspirado no layout do [Textualize](https://textual.textualize.io/). O sistema utilizará Laravel para o backend, Vue.js + Inertia.js para o frontend, e Tailwind CSS para estilização. O conteúdo será gerado dinamicamente a partir de arquivos `.md` armazenados em `public/content`.

## 🎨 Paleta de Cores (Baseada na Logo)
*   **Azul Escuro (Fundo/Bordas):** `#0a192f` / `#0f2b4e`
*   **Azul Médio (Primária):** `#1a80d4`
*   **Ciano (Destaques/Gradientes):** `#2dd4bf` / `#22d3ee`
*   **Branco (Texto principal):** `#f8fafc`

---

## 📋 Checklist de Implementação Passo a Passo

### 🛠️ Fase 1: Setup do Ambiente e Estrutura Base
- [ ] Iniciar projeto Laravel com Inertia e Vue (`laravel new gitpr-site --inertia`).
- [ ] Configurar Tailwind CSS com a paleta de cores personalizada no `tailwind.config.js`.
- [ ] Criar o diretório `public/content` e adicionar um arquivo de teste `index.md`.
- [ ] Limpar as views padrão e preparar o `app.blade.php` base.

### 📦 Fase 2: Roteamento e Controller (Backend)
- [ ] Criar rotas catch-all no `web.php` para capturar as URLs da documentação.
- [ ] Criar `DocsController` com um método (usando `snake_case` para os nomes) para lidar com a requisição.
- [ ] Implementar lógica no controller para verificar se o arquivo `.md` existe na pasta `public/content`. Se não existir, retornar erro 404.
- [ ] Passar o caminho do arquivo Markdown ou o conteúdo bruto como `prop` do Inertia para o Vue.

### 💻 Fase 3: Renderização e Gerenciamento no Vue (Frontend)
- [ ] Instalar um parser de Markdown no frontend (ex: `markdown-it` ou `marked`) e um plugin de highlight de sintaxe (ex: `highlight.js` ou `shiki`).
- [ ] Criar o componente Vue principal (`DocsLayout.vue`) com:
    - [ ] Sidebar esquerda de navegação (Menu de arquivos).
    - [ ] Header superior (Logo GitPR e links rápidos).
    - [ ] Área principal de conteúdo.
- [ ] Criar o componente `MarkdownViewer.vue` que recebe o texto `.md`, converte para HTML estruturado e aplica as classes do Tailwind (via `@tailwindcss/typography`).

### 🎨 Fase 4: Estilização e UX (Estilo Textualize)
- [ ] Implementar o design system com base na logo (Dark mode nativo com gradientes Ciano/Azul).
- [ ] Estilizar os blocos de código (`<pre><code>`) do Markdown com bordas arredondadas e botão de "Copiar código".
- [ ] Adicionar navegação SPA suave entre as páginas usando `<Link>` do Inertia para que os `.md` carreguem sem recarregar a página.
- [ ] Implementar menu responsivo para mobile (Sidebar colapsável).

### 🔍 Fase 5: Funcionalidades Extras
- [ ] Criar um arquivo `menu.json` em `public/content` para ditar a ordem e os títulos da Sidebar.
- [ ] Implementar âncoras automáticas (TOC - Table of Contents) do lado direito lendo os headers `H2` e `H3` do Markdown renderizado.
- [ ] Otimização de SEO (Meta tags dinâmicas baseadas no primeiro parágrafo do arquivo Markdown).
