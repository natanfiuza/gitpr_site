# Relatório: Newsletter Passa a Usar a Versão do CLI (em vez da versão do relatório)

**Data:** 2026-08-16
**Branch:** `develop_natan`
**Motivação:** pedido do usuário — a newsletter deve usar, no título (e na convenção como um todo), a versão do GitPR CLI citada no relatório (ex.: `0.0.35`), e não a versão do relatório em si (H1, ex.: `v0.0.10`).

## Escopo aprovado

O usuário escolheu a opção completa: **skill + app + tutorial**, com migração da edição recém-gerada.

## Mudanças

### 1. Skill `generate-newsletter-body` (`.claude/skills/generate-newsletter-body/SKILL.md`)

- Passo 1: extrai a versão do campo **Current version** do relatório (regex `\d+\.\d+\.\d+`, ex.: `0.0.35`) em vez do H1 (`v\d+\.\d+\.\d+`).
- Observação atualizada: a newsletter usa a versão do CLI, não a versão do relatório.

### 2. App (`app/Support/NewsletterContent.php`)

- `version_from_relatorio()` agora extrai a versão do campo **Current version** (ex.: `- **Current version:** 0.0.35` → `0.0.35`), sem o prefixo `v`.
- `NewsletterSendCommand`: help text do argumento `version` atualizado ("defaults to the GitPR version from the current report").

### 3. Tutorial (`docs/tutorial_newsletter.md`)

- Passo 3: extração da versão do CLI (campo Current version).
- Passo 6: exemplos `php artisan newsletter:send 0.0.35`.
- Regra "Versão da newsletter" invertida: agora é a versão do CLI.

### 4. Migração da edição

- Pasta `public/content/newsletter/v0.0.10/` → `public/content/newsletter/0.0.35/`.
- Títulos dos 5 arquivos corrigidos: `# GitPR v0.0.10 — …` → `# GitPR 0.0.35 — …` (en, pt_br, pt_pt, es, fr).

### 5. Testes (`tests/Feature/NewsletterSendCommandTest.php`)

O teste antigo "version defaults to the current report version and fails fast without a body" dependia da **ausência** de corpo para a versão resolvida — com a convenção nova (0.0.35) e a pasta migrada, o corpo existe e o teste quebrava. Dividido em dois:

- `the version defaults to the GitPR version from the current report` — valida que `NewsletterContent::version_from_relatorio()` retorna formato `\d+\.\d+\.\d+`.
- `fails fast when the body does not exist for the resolved version` — usa versão explícita inexistente (`no-such-version`) para cobrir o fail-fast.

## Verificações

- ✅ Extração confirmada: regex aplicada ao `relatorio.md` real retorna `0.0.35`.
- ✅ `php artisan test --filter=Newsletter` — **26/26 passando**.
- ✅ `composer run test` — 46/54 passando. **As 8 falhas são pré-existentes e não relacionadas**: telas de auth (`/login`, `/register`, `/profile`, verificação de e-mail, reset de senha) devolvem 404 porque em `routes/web.php` o catch-all `/{page?}` (linha 25) é registrado **antes** de `require auth.php` (linha 46), então o `DocsController` engole essas rotas. `routes/web.php` não foi tocado nesta tarefa.
- ✅ `./vendor/bin/pint --test` nos 3 arquivos PHP alterados — passou.
- ✅ `public/content/menu.json` não foi alterado.
- ℹ️ Os arquivos da edição v0.0.10 já haviam sido commitados entre as tarefas — o git verá a migração como rename ao adicionar a pasta nova.

## Observação

- O envio padrão (`php artisan newsletter:send` sem argumento) continua resolvendo a versão sozinho, agora para `0.0.35`, compatível com a pasta migrada.
