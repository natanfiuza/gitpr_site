# 🚀 Plano de Desenvolvimento: Multi-idiomas (i18n) GitPR Site

## 📌 Visão Geral
Implementar suporte a múltiplos idiomas no site de documentação utilizando arquivos `.md` sufixados com o código do idioma (ex: `index.pt_br.md`). O frontend terá uma barra superior com um seletor reativo de idiomas (com bandeiras), que atualizará o conteúdo e o menu instantaneamente via Inertia, sem recarregar a página.

---

## 📋 Checklist de Implementação

### 📂 Fase 1: Arquitetura de Arquivos e Backend
- [ ] Criar os arquivos de teste: `index.md` (Inglês), `index.pt_br.md`, `index.pt_pt.md`, `index.fr.md` e `index.es.md`.
- [ ] Atualizar `DocsController` para aceitar um parâmetro de requisição `lang` (ex: `?lang=pt_br`).
- [ ] Ajustar a lógica de `show_document` no backend para buscar o arquivo com o sufixo apropriado. Se o idioma for inglês (padrão) ou o arquivo traduzido não existir, fazer *fallback* (voltar) para o `.md` original.
- [ ] Reestruturar o `menu.json` para suportar chaves de idiomas (ex: `{"en": [...], "pt_br": [...]}`) e enviar o array correto para o frontend com base no idioma atual.

### 🌐 Fase 2: Gerenciamento de Estado (Vue + Inertia)
- [ ] Definir o idioma padrão no frontend (Inglês) e verificar se há um idioma salvo no `localStorage` do navegador para manter a preferência do usuário.
- [ ] Passar o `current_lang` como propriedade do Inertia a partir do backend para garantir que o servidor e o cliente estejam sincronizados.

### 🇧🇷 Fase 3: Componente Header e Seletor de Idiomas
- [ ] Criar um novo componente Vue `LanguageSelector.vue` contendo um dropdown customizado para exibir a bandeira (SVG ou Emoji) e o nome do idioma.
- [ ] Adicionar uma barra superior (`<header>`) no `DocsLayout.vue` e injetar o `LanguageSelector.vue` alinhado à direita.

### ⚡ Fase 4: Reatividade e Atualização
- [ ] Implementar a função `change_language` no Vue que atualizará o `localStorage` e disparará um `router.get()` (Inertia) passando o novo idioma como query string (`?lang=novo_idioma`).
- [ ] Garantir que o Inertia preserve o scroll da página (`preserveScroll: true`) ao trocar de idioma para uma experiência de usuário impecável.
