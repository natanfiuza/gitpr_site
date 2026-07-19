# 🚀 Plano de Desenvolvimento: Busca Rápida (Search)

## 📋 Checklist de Implementação

### ⚙️ Fase 1: Backend (Controller e Rota)
- [ ] Criar o método `search_content` no `DocsController`.
- [ ] Implementar a lógica para ler os arquivos `.md` do diretório `public/content`, filtrando pelo idioma atual (`lang`).
- [ ] Retornar um JSON contendo o título da página, o caminho (`path`) e um pequeno trecho (snippet) onde o termo foi encontrado.
- [ ] Registrar a rota de API em `routes/web.php`.

### 🔎 Fase 2: Componente Frontend (Vue)
- [ ] Criar o componente `SearchBar.vue` contendo um `input` de texto.
- [ ] Implementar o *debounce* no Vue para evitar requisições excessivas enquanto o usuário digita.
- [ ] Criar o menu *dropdown* flutuante abaixo do campo para exibir os resultados encontrados, utilizando o `<Link>` do Inertia para navegação.

### 🧩 Fase 3: Injeção no Layout
- [ ] Importar o `SearchBar.vue` no arquivo `DocsLayout.vue`.
- [ ] Posicionar a barra de busca no cabeçalho (`<header>`), ao lado do seletor de idiomas.
