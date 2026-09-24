# Plano de Implementação: Tradução da Documentação Técnica do Comando Split (`gitpr split`)

Criação dos arquivos de documentação técnica para os idiomas suportados pelo GitPR Site com base no arquivo mestre `public/content/docs/split-command.md` e na versão `public/content/docs/split-command.pt_br.md`.

---

## 1. Visão Geral e Idiomas Suportados

O GitPR Site suporta 5 idiomas no seletor de linguagem (`LanguageSelector.vue` e `DocsController.php`):
- 🇺🇸 **en**: `public/content/docs/split-command.md` *(já existente)*
- 🇧🇷 **pt_br**: `public/content/docs/split-command.pt_br.md` *(já existente)*
- 🇵🇹 **pt_pt**: `public/content/docs/split-command.pt_pt.md` *(a criar)*
- 🇪🇸 **es**: `public/content/docs/split-command.es.md` *(a criar)*
- 🇫🇷 **fr**: `public/content/docs/split-command.fr.md` *(a criar)*

Além disso, propomos registrar o documento no `public/content/menu.json` em todas as seções de idioma para permitir a navegação lateral e indexação de busca completa.

---

## 2. Alterações Propostas

### 2.1 [NEW] `public/content/docs/split-command.pt_pt.md`
Tradução técnica para Português Europeu (Portugal), adaptando terminologia (ex: *ficheiros* em vez de *arquivos*, *registo* em vez de *registro*, estilo e sintaxe de PT-PT mantendo fidelidade absoluta ao conteúdo técnico, opções de CLI e formatação Markdown).

**Exemplo de adaptações:**
- "Arquivos" $\rightarrow$ "Ficheiros"
- "Árvore de trabalho" $\rightarrow$ "Árvore de trabalho"
- "Staging/estagiar" $\rightarrow$ "Staging/estagiar" / "preparar no índice"
- "Padrão" $\rightarrow$ "Predefinido"

### 2.2 [NEW] `public/content/docs/split-command.es.md`
Tradução técnica para Espanhol, seguindo o padrão de estilo estabelecido em `fix-command.es.md`, `ARCHITECTURE.es.md` e `smart-excludes.es.md`.

**Exemplo de adaptações:**
- "Technical Documentation: Split Command" $\rightarrow$ "Documentación Técnica: Comando Split (gitpr split)"
- "Working tree" $\rightarrow$ "Árbol de trabajo"
- "Staged / unstaged" $\rightarrow$ "En stage / fuera de stage (preparados / no preparados)"
- "Exit code 1" $\rightarrow$ "Código de salida 1"

### 2.3 [NEW] `public/content/docs/split-command.fr.md`
Tradução técnica para Francês, seguindo o padrão de estilo de `fix-command.fr.md`, `ARCHITECTURE.fr.md` e `smart-excludes.fr.md`.

**Exemplo de adaptações:**
- "Technical Documentation: Split Command" $\rightarrow$ "Documentation Technique : Commande Split (gitpr split)"
- "Working tree" $\rightarrow$ "Arbre de travail"
- "Hunk" $\rightarrow$ "Hunk (tronçon de diff)"
- "Exit code 1" $\rightarrow$ "Code de sortie 1"

### 2.4 [MODIFY] `public/content/menu.json`
Adicionar a entrada correspondente à documentação técnica do comando split na seção `"Technical Documentation"` de cada idioma no menu:
- `en`: `{ "title": "▸ Split Command", "path": "docs/split-command" }`
- `pt_br`: `{ "title": "▸ Comando de Divisão", "path": "docs/split-command" }`
- `pt_pt`: `{ "title": "▸ Comando de Divisão", "path": "docs/split-command" }`
- `fr`: `{ "title": "▸ Commande Split", "path": "docs/split-command" }`
- `es`: `{ "title": "▸ Comando Split", "path": "docs/split-command" }`

---

## 3. Plano de Verificação

### Testes Automatizados
- Executar testes de busca (`vendor/bin/pest tests/Feature/SearchTest.php`) para garantir indexação e compatibilidade UTF-8 em todos os idiomas.
- Validar a integridade do JSON de menu (`php -r "json_decode(file_get_contents('public/content/menu.json'), true);"`).

### Verificação Manual
- Validar se os arquivos markdown preservam blocos de código ````bash````, ````python````, tabelas e avisos sem quebra de sintaxe.
- Testar a rota de documentação `/docs/split-command?lang=pt_pt`, `/docs/split-command?lang=es` e `/docs/split-command?lang=fr`.
