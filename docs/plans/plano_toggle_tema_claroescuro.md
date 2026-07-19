# 🚀 Plano de Desenvolvimento: Toggle Tema Claro/Escuro

## 📋 Checklist de Implementação

### 🎨 Fase 1: Configuração do Tailwind
- [ ] Atualizar `tailwind.config.js` para habilitar `darkMode: 'class'`.
- [ ] Definir o esquema de cores reversas no layout principal (`DocsLayout.vue`). As classes atuais (ex: `bg-gitpr_dark`) receberão o prefixo `dark:` e definiremos cores suaves para o padrão claro (ex: `bg-slate-50`).

### 🌓 Fase 2: Componente de Toggle (Frontend)
- [ ] Criar o componente isolado `ThemeToggle.vue` exibindo um botão interativo (Ícone de Sol ☀️ e Lua 🌙).
- [ ] Implementar a lógica reativa no Vue para alternar a classe `dark` na tag `<html>` do documento e salvar a preferência em `localStorage`.

### 🧩 Fase 3: Injeção e Ajustes Finos
- [ ] Injetar o componente `ThemeToggle.vue` no cabeçalho superior, ao lado do botão de idioma.
- [ ] Criar um script inicial no `app.blade.php` para bloquear o *flicker* (aquele piscar branco rápido na tela antes do Vue carregar o modo escuro armazenado).
