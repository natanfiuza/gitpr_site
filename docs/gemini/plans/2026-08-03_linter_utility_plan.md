# Linter Utility - Implementation Plan

Este documento detalha o plano de implementação do utilitário de criação e edição de regras para o arquivo `.gitpr.linter.yml`.

## Open Questions Resolved

> [!NOTE]
> 1. **Teste de Regex**: Como o Python disponível no servidor é a versão 3.6.8, vamos utilizar puramente o **JavaScript (frontend)** para testar as expressões regulares. Isso garantirá respostas mais rápidas em tempo real sem sobrecarregar o backend com chamadas de sub-processos antigos. Adicionaremos uma caixa de texto interativa onde o usuário poderá colar código e testar o "match" da regex instantaneamente.
> 2. **Acesso**: A rota `/linter-utility` será **Pública**, permitindo que qualquer pessoa visualizando a documentação possa utilizar a ferramenta livremente.

## Proposed Changes

### Banco de Dados (Database)

A base de dados será utilizada para armazenar os templates de regras baseados no guia de regex do GitPR.

#### [NEW] `database/migrations/xxxx_xx_xx_create_linter_rule_templates_table.php`
- Tabela `linter_rule_templates`:
  - `id` (primária)
  - `name` (string)
  - `extensions` (json)
  - `regex` (string)
  - `message` (string)
  - `ignore_comments` (boolean)
  - `ignore_paths` (json)
  - `description` (text)

#### [NEW] `app/Models/LinterRuleTemplate.php`
- Model configurado com `$casts` para arrays/json nos campos `extensions` e `ignore_paths`.

#### [NEW] `database/seeders/LinterRuleTemplateSeeder.php`
- Popula o banco com regras performáticas e templates (ex: "check-localhost", "no-console-log", "no-debugger", "no-todo-without-ticket").

---

### Backend (Laravel)

#### [NEW] `app/Http/Controllers/LinterUtilityController.php`
- `index()`: Renderiza a página Vue Inertia enviando a lista de `LinterRuleTemplate` disponíveis.
- `generateYaml()`: Endpoint que recebe o array de regras do frontend e retorna a string YAML formatada e validada.

#### [MODIFY] `routes/web.php`
- Adição da rota pública `GET /linter-utility` apontando para `LinterUtilityController@index`.
- Adição da rota `POST /linter-utility/generate` apontando para `LinterUtilityController@generateYaml`.

---

### Frontend (Vue + Inertia + Tailwind CSS)

#### [NEW] `resources/js/Pages/LinterUtility.vue`
- Página principal do utilitário, com os seguintes blocos:
  1. **Upload / Import:** Botão para carregar um `.gitpr.linter.yml` existente e transformar em estado (state) no Vue usando a biblioteca `js-yaml` no frontend ou parse via backend.
  2. **Rule Builder:** Formulário interativo para editar a regra atual (Nome, Regex, Extensões, Mensagens).
  3. **Templates Panel:** Sidebar listando os templates do banco de dados (fornecidos via Inertia props) para o usuário "Adicionar ao projeto" com 1 clique.
  4. **Regex Editor & Tester:**
     - Uma caixa de texto onde o usuário pode **colar código real**.
     - Aplicação da Regex localmente usando o motor do JavaScript (`new RegExp()`).
     - Feedback instantâneo destacando correspondências no texto fornecido.
     - Dicas de performance focadas no guia do GitPR (evitar `.*`, uso de `\b`, etc).
  5. **Export / Download:** Botão para compilar as regras de volta para YAML e fazer o download do arquivo `.gitpr.linter.yml`.

#### [MODIFY] `public/content/linter.md` (e variantes traduzidas)
- Inserir um "Call to Action" ou link que redirecione para `/linter-utility`:
  ```markdown
  ## 🛠️ Linter Rule Builder
  Precisa de ajuda para criar suas regras ou testar suas expressões regulares de forma visual? 
  [Acesse o Utilitário do Linter](/linter-utility) para criar, testar e exportar seu arquivo `.gitpr.linter.yml`.
  ```

#### [NEW] `package.json`
- Instalar dependência de frontend para manipulação de YAML: `npm install js-yaml` (ou similar).

---

## Verification Plan

### Automated Tests
- Criar Pest tests em `tests/Feature/LinterUtilityTest.php`:
  - Garantir que a página pública `/linter-utility` responde com status 200.
  - Testar o endpoint de geração de YAML com payload simulado.

### Manual Verification
1. Navegar até a página `linter.md` e clicar no link.
2. Na página do utilitário, adicionar uma regra de template ao painel ativo.
3. No painel **Regex Tester**, colar um trecho de código e visualizar se o match da Regex acontece instantaneamente no texto via JavaScript.
4. Fazer upload do `.gitpr.linter.yml` existente para testar o parser.
5. Fazer download (exportar) as regras atualizadas e rodar localmente no terminal com `gitpr -l` para verificar o funcionamento prático da syntax YAML gerada.
