# Plano: Sistema de Newsletter — GitPR Site

> Plano de implementação aprovado em 2026-08-16 (origem: spec em `docs/plans/20260816_prompt_newsletter_gitpr.md`).

## Contexto

O site do GitPR (Laravel 13 + Inertia 2 + Vue 3) ganhará um sistema de newsletter completo: cadastro de e-mail, confirmação por link com formulário de perfil, cancelamento (com suporte a one-click do Gmail/Outlook), conteúdo versionado derivado do relatório de status, dicas extraídas da documentação técnica, e um comando Artisan temporário para envio em lote.

**Decisões confirmadas com o usuário:**
1. Box de cadastro: coluna direita (área do TOC) em **todas** as páginas de docs + **acima do campo de busca no menu mobile**.
2. Newsletter e dicas em **5 idiomas** (en, pt_br, pt_pt, fr, es) — cada inscrito recebe no próprio `lang`, fallback en.
3. Reinscrição após cancelamento **reativa** a assinatura (zera `is_canceled`/`date_canceled`).

**Decisões técnicas:**
- Coluna `uuid` em `newsletter_subscribers` (link de cancelamento não adivinhável — necessário e não previsto na spec).
- Cancelamento one-click RFC 8058: `POST /newsletter/unsubscribe/{uuid}` executa o cancelamento (Gmail envia POST) e redireciona para a tela GET com estado "cancelado".
- Regras "só um `is_confirmed=true` por e-mail" e "reuso do uuid não expirado": guards de aplicação no controller (sem índice parcial — incompatível MySQL).
- Markdown→HTML com `league/commonmark` 2.8.3 **já instalado** (transitivo; fixar em composer.json pois passaremos a usar `Str::markdown` diretamente).
- Header das telas de confirmação/cancelamento: extrair o header existente em `SiteHeader.vue` (o header hoje é duplicado em DocsLayout e LinterUtility — seria a 3ª cópia).

## Arquitetura

- **Backend**: 1 controller + 3 Mailables + 1 Command + 2 Models + 2 migrations + 2 classes de suporte.
- **Frontend**: `SiteHeader.vue` (extração), `NewsletterBox.vue`, 2 páginas novas, ajustes em DocsLayout/LinterUtility/DocsController/HandleInertiaRequests.
- **Conteúdo**: `tip_tools.json`, `public/content/newsletter/{version}/newsletter_body.{lang}.md`, 2 skills locais.

---

## Passo 1 — Dependência

- `composer.json`: mover `league/commonmark: ^2.8` para `require` direto (já está no lock via transitiva). Rodar `composer update league/commonmark`.

## Passo 2 — Migrations (2 arquivos novos em `database/migrations/`)

Padrão anonymous class + timestamps (como a migration de linter existente).

**`2026_08_16_000001_create_newsletter_subscribers_table.php`**
```php
$table->id();
$table->uuid('uuid')->unique();
$table->string('name');
$table->string('email')->unique();          // 1 linha por e-mail; reativação atualiza
$table->string('github')->nullable();
$table->string('phone')->nullable();
$table->string('lang')->default('en');
$table->boolean('is_canceled')->default(true);   // spec: padrão true; confirm grava false
$table->timestamp('date_canceled')->nullable();
$table->timestamps();
$table->index('is_canceled');
```

**`2026_08_16_000002_create_newsletter_confirmations_table.php`**
```php
$table->id();
$table->uuid('uuid')->unique();
$table->string('email')->index();
$table->boolean('is_confirmed')->default(false);
$table->timestamp('date_confirmed')->nullable();
$table->timestamps();
```

## Passo 3 — Models (2 novos em `app/Models/`)

- **`NewsletterSubscriber.php`** — padrão `LinterRuleTemplate` (`$fillable` + `casts()`):
  - `$fillable`: `uuid, name, email, github, phone, lang, is_canceled, date_canceled`
  - `casts()`: `is_canceled => boolean`, `date_canceled => datetime`
  - `scopeActive($q)`: `where('is_canceled', false)`
- **`NewsletterConfirmation.php`**:
  - `$fillable`: `uuid, email, is_confirmed, date_confirmed`
  - `casts()`: `is_confirmed => boolean`, `date_confirmed => datetime`
  - `scopeNotExpired($q)`: `where('created_at', '>=', now()->subHours(24))`

## Passo 4 — Suporte (2 novos em `app/Support/`)

**`NewsletterTranslations.php`** — todas as strings de UI e e-mail nos 5 idiomas (padrão dos arrays `ui_strings` existentes). API:
```php
final class NewsletterTranslations {
    public const LANGS = ['en', 'pt_br', 'pt_pt', 'fr', 'es'];
    public static function all(): array;              // ['en' => [...], ...]
    public static function for(string $lang): array;  // fallback 'en'
    public static function get(string $lang, string $key, array $replace = []): string;
}
```
Chaves (≈30): box (`newsletter_box_title/desc/email_placeholder/subscribe_btn/sent/already_confirmed/send_cancel_link_btn/cancel_link_sent/error`), confirmação (`confirm_title/intro/name_label/email_label/github_label/phone_label/lang_label/submit_btn/expired_title/message/not_found_title/already_title/success_title/message`), cancelamento (`cancel_title/intro/btn/done_title/message/already_title/not_found_title`), e-mails (`mail_confirm_subject/intro/button/note_24h`, `mail_cancel_subject/intro/button`, `mail_newsletter_subject` com `{version}`, `mail_newsletter_unsubscribe`).

**`NewsletterContent.php`** — leitura/versão:
```php
public static function versionFromRelatorio(): ?string
// File::get(public_path('content/relatorio.md')) → preg_match('/v\d+\.\d+\.\d+/', H1)
public static function bodyMarkdown(string $version, string $lang): string
// public_path("content/newsletter/{$version}/newsletter_body.{$lang}.md") → fallback .md → RuntimeException
public static function bodyHtml(string $version, string $lang): string  // Str::markdown(...)
public static function lastSentVersion(): ?string
public static function markSent(string $version): void
// Storage::disk('local')->get/put('newsletter/last_sent.txt')  (storage/app/private já existe)
```

## Passo 5 — Mailables + views (6 novos)

**`app/Mail/ConfirmationMail.php`** — props públicas `$url, $strings`; `build()`: `->locale($lang)->subject(...)->view('emails.confirmation')`. URL completa montada no controller (`route('newsletter.confirm', [$uuid, 'lang' => $lang])`) para não depender de APP_URL no envio.

**`app/Mail/CancelLinkMail.php`** — idem; locale = lang do subscriber; URL = `route('newsletter.cancel', $sub->uuid)`.

**`app/Mail/NewsletterMail.php`** — props `$htmlBody, $version, $unsubscribeUrl, $strings`. `build()`:
```php
->locale($lang)->subject(...)
->view('emails.newsletter')
->withSymfonyMessage(function ($msg) {
    $h = $msg->getHeaders();
    $h->addTextHeader('List-Unsubscribe', "<{$this->unsubscribeUrl}>");
    $h->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
});
```

**`resources/views/emails/`** (novo diretório): `confirmation.blade.php`, `cancel-link.blade.php`, `newsletter.blade.php` — HTML simples com tabela centralizada, cor `#1a80d4`, strings via `$strings` (resolvidas no controller com `NewsletterTranslations::for($lang)`). `newsletter.blade.php` = header GitPR [CLI] + `{!! $htmlBody !!}` + rodapé com link de cancelamento (`$unsubscribeUrl`).

## Passo 6 — Rotas (modificar `routes/web.php`)

Inserir **antes do catch-all** (linha 16), junto ao bloco linter-utility:
```php
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/newsletter/send-cancel-link', [NewsletterController::class, 'sendCancelLink'])->name('newsletter.send-cancel-link');
Route::get('/newsletter/confirm/{uuid}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::post('/newsletter/confirm/{uuid}', [NewsletterController::class, 'confirmSubmit'])->name('newsletter.confirm.submit');
Route::get('/newsletter/cancel/{uuid}', [NewsletterController::class, 'cancel'])->name('newsletter.cancel');
Route::post('/newsletter/unsubscribe/{uuid}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
```

## Passo 7 — Flash compartilhado (modificar `app/Http/Middleware/HandleInertiaRequests.php`)

Em `share()` adicionar: `'flash' => ['newsletter' => fn () => session('newsletter')]`. O box lê `usePage().props.flash.newsletter.status` e traduz via `ui_strings` (chaves `newsletter_sent`, `newsletter_already_confirmed`, `newsletter_cancel_link_sent`). Sem isso o `with()` some nas navegações Inertia.

## Passo 8 — `app/Http/Controllers/NewsletterController.php` (novo)

Padrão controller magro + `Inertia::render`. Helper privado `lang($request, $default='en')` validando contra `LANGS`.

- **`subscribe` (POST)**: valida `email required|email|max:255` + `lang` no body.
  - e-mail já confirmado (confirmation `is_confirmed=true`) → flash `newsletter = ['status' => 'already_confirmed']`, redirect back. **Não** envia e-mail.
  - confirmação pendente não expirada → **reusa o mesmo uuid**.
  - senão cria `NewsletterConfirmation` com uuid novo (`Str::uuid()`).
  - envia `ConfirmationMail` (locale = lang do submit) e flash `sent`.
- **`sendCancelLink` (POST)**: valida email. Se subscriber ativo existe → envia `CancelLinkMail` no idioma do subscriber. Resposta **genérica** nos dois casos (não vaza existência): flash `cancel_link_sent`.
- **`confirm` (GET `{uuid}`)**: resolve estado: `not_found` | `expired` (`created_at < now-24h` e não confirmado) | `already_confirmed` | `form`. Renderiza `NewsletterConfirmPage` com `status, email (do registro), subscriber (name/github/phone/lang se existir), current_lang, ui_strings (NewsletterTranslations::for($lang)), uuid`. Lang default do form = `$sub?->lang ?? $request->query('lang', 'en')`.
- **`confirmSubmit` (POST `{uuid}`)**: revalida o registro (not_found/expired → redirect p/ GET). Valida: `name required|string|max:255`, `email required|email` **e igual ao e-mail do registro** (abort 422 senão), `github/phone nullable`, `lang required|in:LANGS`.
  - `NewsletterSubscriber::firstOrNew(['email' => $c->email])` → fill(nome/github/phone/lang), `is_canceled = false`, `date_canceled = null`, save (**reativação** — decisão do usuário).
  - Se **não** existir outra confirmação `is_confirmed=true` do mesmo e-mail (id ≠ atual): marca `is_confirmed=true`, `date_confirmed=now()` (regra "só um true por e-mail").
  - Redirect para GET (mostra estado `already_confirmed` = sucesso).
- **`cancel` (GET `{uuid}`)**: status `not_found` | `already_canceled` | `done` (via `?done=1`) | `form`. Lang: `?lang=` → lang do subscriber → en. Renderiza `NewsletterCancelPage`.
- **`unsubscribe` (POST `{uuid}`)**: atualiza `is_canceled=true`, `date_canceled=now()`; redirect para `newsletter.cancel` com `done=1` e lang do subscriber.

## Passo 9 — Frontend

**`resources/js/Components/SiteHeader.vue`** (novo) — extrai o header de DocsLayout (linhas 24-51) + variantes do LinterUtility. Props: `current_lang, subtitle, show_search, show_version, back_to_docs, show_mobile_toggle`; emite `toggle-mobile`. Contém: brand GitPR [ CLI ], badge de versão via fetch do GitHub (movido do onMounted de DocsLayout, só se `show_version`), SearchBar, ThemeToggle, LanguageSelector, botão ☰, link "back to docs" (heredado do LinterUtility).

**`resources/js/Components/NewsletterBox.vue`** (novo) — `useForm({ email: '' })`, envia `form.post(route('newsletter.subscribe'), { preserveScroll: true })` com `lang: current_lang` no body. Lê flash (`usePage().props.flash?.newsletter`): `sent` → mensagem sucesso; `already_confirmed` → mensagem + botão "enviar link de cancelamento" (`router.post(route('newsletter.send-cancel-link'), { email, lang })`); `cancel_link_sent` → mensagem genérica. Textos via `ui_strings`.

**`resources/js/Pages/DocsLayout.vue`** (modificar):
- Substituir `<header>` inline por `<SiteHeader :current_lang show_search show_version show_mobile_toggle @toggle-mobile="..." />`; remover fetch do `release_tag`.
- Adicionar `<NewsletterBox :current_lang :ui_strings />` em 2 pontos:
  1. Dentro do sidebar mobile (`<div class="mt-8 mb-6 md:hidden">`, linhas 66-68), **acima** do `<SearchBar>`;
  2. No aside TOC (linhas 113-149), após o botão indicador de scroll (linha 148), com `border-t` e `mt-4` (visível em `xl+`).

**`resources/js/Pages/LinterUtility.vue`** (modificar): substituir header (linhas 5-22) por `<SiteHeader :current_lang subtitle="Linter Utility" back_to_docs />`; limpar imports não usados.

**`resources/js/Pages/NewsletterConfirmPage.vue`** (novo) — props: `status, email, subscriber, current_lang, ui_strings, uuid`. `SiteHeader show_version`. Corpo centralizado `max-w-md`:
- `form`: campos nome (required), email (readonly), github, telefone, select lang (5 opções, default `subscriber?.lang ?? current_lang`); submit `router.post(route('newsletter.confirm.submit', { uuid }), form)`; texto "pode cancelar a qualquer momento".
- `already_confirmed`: título de sucesso + form compacto (email readonly + botão enviar link de cancelamento → `newsletter.send-cancel-link`).
- `expired` / `not_found`: painel com título+mensagem i18n.

**`resources/js/Pages/NewsletterCancelPage.vue`** (novo) — props: `status, done, current_lang, ui_strings, uuid`. `SiteHeader show_version`. `done` → mensagem "cancelado"; senão texto i18n + botão confirmar cancelamento → `router.post(route('newsletter.unsubscribe', { uuid }))`.

**`app/Http/Controllers/DocsController.php`** (modificar): no `show_document`, fundir traduções no `ui_strings` (linhas 60-66):
```php
$ui_strings[$lang] = array_merge($ui_strings[$lang] ?? $ui_strings['en'], NewsletterTranslations::for($lang));
```
E guard de 3 linhas no topo: `str_starts_with($page, 'newsletter/')` → `abort(404)` (o catch-all tornaria o `newsletter_body.md` acessível como página de docs).

## Passo 10 — Command `app/Console/Commands/NewsletterSendCommand.php` (novo)

`signature: newsletter:send {--version=} {--force} {--interval=5}` (`--interval` para testes não dormirem).

```
1. $version = --version ?: NewsletterContent::versionFromRelatorio()  // ex: v0.0.10
   sem versão → error + FAILURE
2. lastSentVersion() === $version e sem --force → info "já enviada" + SUCCESS
3. Verificar existência do corpo (bodyMarkdown com lang 'en') UMA vez → error claro se ausente
4. $subs = NewsletterSubscriber::active()->get(); vazio → info + SUCCESS
5. Volume: count * interval > 3600 (~720 inscritos) e sem --force
   → warn com estimativa em horas + FAILURE   // spec: alerta p/ envio de múltiplas horas
6. Loop: try { bodyHtml($version, $sub->lang); Mail::send(new NewsletterMail(...)) }
   catch → warn por destinatário, NÃO interrompe o lote
   sleep(interval) entre envios; progress bar
7. markSent($version)
```

Envio síncrono (`Mail::send`, sem queue) mesmo com `QUEUE_CONNECTION=database`.

## Passo 11 — Skills locais (2 novas em `.claude/skills/`, formato das existentes)

**`.claude/skills/update-tip-tools/SKILL.md`** — frontmatter (`name: update-tip-tools`, description com gatilho "Use quando o usuário pedir para atualizar/criar as dicas da newsletter"). Passos:
1. Ler `public/content/tip_tools.json` existente, preservando `used=true`.
2. Varrer `public/content/**/*.md`, excluindo `newsletter/**` e `relatorio*`.
3. Extrair candidatos: dicas de uso, features (chat, mcp, linter, plugins...), curiosidades — sem inventar feature inexistente.
4. Redigir cada dica nos 5 idiomas → `{"id": "tip_N", "used": false, "source": "docs/<arquivo>.md", "content": {"en": "...", "pt_br": "...", ...}}`.
5. Mesclar: manter usadas intactas, adicionar novas `used=false`, deduplicar. JSON formatado.
6. Verificar: 5 idiomas por dica; `used` nunca volta de true→false.
7. Relatório da tarefa (regra CLAUDE.md).

**`.claude/skills/generate-newsletter-body/SKILL.md`** — frontmatter (`name: generate-newsletter-body`, gatilho "Use quando o usuário pedir para gerar o corpo da newsletter"). Passos:
1. Versão pelo H1 de `public/content/relatorio.md` (regex `v\d+\.\d+\.\d+`).
2. Ler `relatorio.{lang}.md` nos 5 idiomas (en como mestre/fallback).
3. Extrair por idioma: novidades da versão ("What's New"), como usar, e 3-5 dicas com `used=false` de `tip_tools.json` no idioma correspondente — marcá-las `used=true`.
4. Gerar `public/content/newsletter/{version}/newsletter_body.md` + `.pt_br.md` + `.pt_pt.md` + `.es.md` + `.fr.md`.
5. Verificar paridade de estrutura entre os 5 e que **nada** foi adicionado ao `menu.json`.
6. Se `{version}` já existir, confirmar antes de sobrescrever.
7. Relatório da tarefa (regra CLAUDE.md).

## Passo 12 — Testes Pest (4 novos em `tests/Feature/`)

`RefreshDatabase`/`Mail::fake()` já cobertos por Pest.php/phpunit.xml.

- **`NewsletterSubscribeTest.php`**: novo e-mail → cria confirmação + `Mail::assertSent(ConfirmationMail)`; reenvio → **mesmo uuid**, 1 registro só; expirado (created_at 25h atrás) → uuid novo; já confirmado → flash `already_confirmed` + `assertNotSent`; send-cancel-link → `assertSent(CancelLinkMail)` com locale do subscriber; e-mail inexistente → resposta genérica; email inválido → 422.
- **`NewsletterConfirmTest.php`**: GET estados (`form`/`not_found`/`expired`/`already_confirmed`); POST válido → subscriber `is_canceled=false`, confirmação `is_confirmed=true` + `date_confirmed`; email ≠ registro → 422; **reativação** de cancelado; **duplicidade** (2 confirmações do mesmo e-mail → count de `is_confirmed=true` == 1).
- **`NewsletterCancelTest.php`**: POST unsubscribe → `is_canceled=true`, `date_canceled` setado, redirect `done=1`; GET → 200.
- **`NewsletterSendCommandTest.php`**: fixture `public/content/newsletter/test-v1/newsletter_body.md` + `.pt_br.md` (limpa no `after()`); `--version=test-v1 --interval=0` → 2 envios por lang correto; marker: 2ª execução sem `--force` → 0 envios, com `--force` → envia; volume (730 inscritos) → aborta sem `--force`; falha individual não interrompe lote.

## Passo 13 — Verificação

1. `composer run test` — suíte completa + novos testes.
2. `./vendor/bin/pint` — lint PHP.
3. `npm run build` — compila os novos componentes (tailwind já varre `resources/js/**/*.vue`).
4. **Manual** (`MAIL_MAILER=log`, `php artisan serve`):
   - `/index` → box no TOC (xl+) e acima da busca no menu mobile;
   - assinar → conferir link de confirmação no `storage/logs/laravel.log` → abrir → formulário (email readonly) → salvar → tela de sucesso; re-assinar → "já confirmado" + opção de link de cancelamento;
   - gerar corpo via skill `generate-newsletter-body` → `php artisan newsletter:send --interval=0 --force` → e-mails no log com headers `List-Unsubscribe`/`List-Unsubscribe-Post` e corpo por idioma;
   - link de cancelamento → botão → "cancelado"; re-assinar → reativa.
5. Relatório da tarefa em `docs/claude-code/reports/develop_natan/2026-08-16_develop_natan_sistema_newsletter.md` (regra do CLAUDE.md).

## Riscos / pegadinhas

1. **Catch-all**: rotas `/newsletter/*` DEVEM ficar antes da linha 16 de `routes/web.php`.
2. **Flash Inertia**: sem o Passo 7, `with()` some nas navegações Inertia.
3. **sqlite (testes) vs MySQL**: usar `$table->uuid()`/`timestamp` nullable padrão; nada específico de banco.
4. **`MAIL_FROM_ADDRESS=hello@example.com`** e `APP_URL=http://localhost` no `.env` — trocar antes da 1ª newsletter real.
5. **Tailwind varre `public/content/**/*.md`**: evitar `class="..."` literal no markdown da newsletter (infla o CSS).
6. **Header duplicado**: o SiteHeader extrai marcação idêntica de DocsLayout/LinterUtility — diffs devem ser só de remoção/substituição.
7. **`is_canceled` default true** (spec literal): mantido no banco; toda criação via `confirmSubmit` grava `false` explicitamente.
