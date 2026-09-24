# Especificação Técnica e Funcional: Sistema de Newsletter

**Projeto:** GitPR Site (Documentação & Apresentação Oficial do GitPR CLI)  
**Stack:** Laravel 13.x (PHP 8.3+) | Inertia.js 2.x | Vue 3 (Composition API) | Tailwind CSS 3.x | Markdown-it | Symfony Mailer  
**Data:** 20/09/2026  
**Status:** Aprovado / Em Produção  
**Público-Alvo:** Desenvolvedores (Backend/Frontend), Engenheiros de QA (Testes & Automação) e Product Owners (PO / Negócios)

---

## 1. Visão Geral e Regras de Negócio (PO & Negócio)

### 1.1 Objetivos do Módulo
- Capturar leads e engajar a comunidade do **GitPR CLI**, notificando desenvolvedores sobre novas versões, correções críticas, novos comandos e dicas de produtividade.
- Assegurar a entregabilidade máxima dos e-mails através de **Double Opt-in** (confirmação ativa por e-mail).
- Garantir conformidade com normas globais de privacidade (LGPD/GDPR) e políticas anti-spam dos provedores de e-mail (Gmail, Outlook, Yahoo, Apple Mail).
- Suportar internacionalização nativa em **5 idiomas**: Inglês (`en`), Português Brasileiro (`pt_br`), Português Europeu (`pt_pt`), Francês (`fr`) e Espanhol (`es`).

```mermaid
sequenceDiagram
    autonumber
    actor Visitante as Visitante
    participant Box as NewsletterBox.vue
    participant Ctrl as NewsletterController
    participant DB_Conf as newsletter_confirmations
    participant Mail as Provedor de E-mail (SMTP)
    participant PageConf as NewsletterConfirmPage.vue
    participant DB_Sub as newsletter_subscribers

    Visitante->>Box: Preenche e-mail e clica em "Assinar"
    Box->>Ctrl: POST /newsletter/subscribe { email, lang }
    Ctrl->>DB_Conf: Cria/Recupera UUID (status pendente)
    Ctrl->>Mail: Dispara ConfirmationMail com link assinado
    Ctrl-->>Box: Retorna status flash 'sent'
    Box-->>Visitante: Exibe mensagem "E-mail de confirmação enviado!"
    
    Visitante->>PageConf: Clica no link do e-mail: GET /newsletter/confirm/{uuid}
    PageConf-->>Visitante: Exibe formulário com dados de perfil (Nome, GitHub, Telefone, Idioma)
    Visitante->>PageConf: Preenche perfil e clica em "Confirmar inscrição"
    PageConf->>Ctrl: POST /newsletter/confirm/{uuid} { name, email, github, phone, lang }
    Ctrl->>DB_Sub: Grava assinante ativo (is_canceled = false)
    Ctrl->>DB_Conf: Marca is_confirmed = true e date_confirmed = now()
    Ctrl-->>PageConf: Redireciona com status 'already_confirmed'
```

---

### 1.2 Regras de Negócio e Casos de Uso

| ID | Regra de Negócio | Comportamento do Sistema |
|---|---|---|
| **RN-NL01** | **Double Opt-in Obrigatório** | O preenchimento do box inicial gera apenas um registro temporário em `newsletter_confirmations` com `is_confirmed = false`. O usuário só se torna assinante após acessar o link e submeter o formulário de perfil. |
| **RN-NL02** | **Janela de Expiração do Link** | O link de confirmação expira exatamente **24 horas** após sua criação (`created_at < now() - 24h`). Após esse prazo, o status é alterado para `expired`. |
| **RN-NL03** | **Reutilização Inteligente de UUID** | Se o visitante submeter novamente um e-mail que já possui confirmação pendente válida (< 24h), o sistema reenvia o e-mail com o **mesmo UUID**, evitando links duplicados. |
| **RN-NL04** | **Submissão de E-mail Já Confirmado** | Se o e-mail submetido já estiver ativo na base de assinantes, o sistema não dispara novo e-mail de confirmação e exibe a mensagem *"Este e-mail já está inscrito"*, disponibilizando o botão *"Enviar link de cancelamento"*. |
| **RN-NL05** | **Anti-Enumeração na Solicitação de Cancelamento** | Ao solicitar o envio do link de cancelamento (`POST /newsletter/send-cancel-link`), a resposta é sempre genérica (*"Se o e-mail estiver cadastrado, um link de cancelamento foi enviado"*), impedindo que invasores descubram se um e-mail está na base. |
| **RN-NL06** | **Cancelamento em 1 Clique (RFC 8058)** | Toda newsletter disparada inclui os cabeçalhos `List-Unsubscribe` e `List-Unsubscribe-Post: List-Unsubscribe=One-Click`, permitindo que clientes de e-mail (Gmail/Outlook) cancelem a inscrição diretamente sem abrir o navegador. |
| **RN-NL07** | **Reativação Automática de Assinante** | Se um usuário que havia cancelado sua assinatura (`is_canceled = true`) solicitar uma nova inscrição e confirmá-la, seu registro em `newsletter_subscribers` é reativado (`is_canceled = false`, `date_canceled = null`) e os dados cadastrais são atualizados. |
| **RN-NL08** | **Trava de Segurança de E-mail** | No formulário de confirmação, o campo `email` é somente leitura (`readonly`). Se houver manipulação do payload POST com um e-mail divergente do token UUID, o backend rejeita com erro HTTP 422. |

---

## 2. Modelagem de Dados & Migrations (Dev & QA)

### 2.1 Tabela `newsletter_confirmations`
- **Arquivo de Migration:** `database/migrations/2026_08_16_000002_create_newsletter_confirmations_table.php`
- **Model:** `App\Models\NewsletterConfirmation`

```sql
CREATE TABLE `newsletter_confirmations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL,
  `is_confirmed` TINYINT(1) NOT NULL DEFAULT 0,
  `date_confirmed` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `newsletter_confirmations_email_index` (`email`)
);
```

#### Recursos do Model `NewsletterConfirmation`
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsletterConfirmation extends Model
{
    protected $fillable = ['uuid', 'email', 'is_confirmed', 'date_confirmed'];

    protected function casts(): array
    {
        return [
            'is_confirmed' => 'boolean',
            'date_confirmed' => 'datetime',
        ];
    }

    /**
     * Scope para filtrar registros com menos de 24 horas de criação.
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subHours(24));
    }
}
```

---

### 2.2 Tabela `newsletter_subscribers`
- **Arquivo de Migration:** `database/migrations/2026_08_16_000001_create_newsletter_subscribers_table.php`
- **Model:** `App\Models\NewsletterSubscriber`

```sql
CREATE TABLE `newsletter_subscribers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `github` VARCHAR(255) NULL DEFAULT NULL,
  `phone` VARCHAR(255) NULL DEFAULT NULL,
  `lang` VARCHAR(10) NOT NULL DEFAULT 'en',
  `is_canceled` TINYINT(1) NOT NULL DEFAULT 1,
  `date_canceled` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `newsletter_subscribers_is_canceled_index` (`is_canceled`)
);
```

#### Recursos do Model `NewsletterSubscriber`
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'uuid', 'name', 'email', 'github', 'phone', 'lang', 'is_canceled', 'date_canceled'
    ];

    protected function casts(): array
    {
        return [
            'is_canceled' => 'boolean',
            'date_canceled' => 'datetime',
        ];
    }

    /**
     * Scope para retornar apenas assinantes ativos (não cancelados).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_canceled', false);
    }
}
```

---

## 3. Arquitetura de Rotas e Endpoints

Registradas em `routes/web.php` (devem preceder a rota coringa de documentação `/{page?}`):

```php
// Newsletter Routes
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/newsletter/send-cancel-link', [NewsletterController::class, 'send_cancel_link'])->name('newsletter.send-cancel-link');
Route::get('/newsletter/confirm/{uuid}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::post('/newsletter/confirm/{uuid}', [NewsletterController::class, 'confirm_submit'])->name('newsletter.confirm.submit');
Route::get('/newsletter/cancel/{uuid}', [NewsletterController::class, 'cancel'])->name('newsletter.cancel');
Route::post('/newsletter/unsubscribe/{uuid}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
```

---

## 4. Frontend: Componentes e Telas Vue 3

### 4.1 `resources/js/Components/NewsletterBox.vue`
Componente reutilizável inserido na barra lateral da documentação desktop (`DocsLayout.vue`) e na navegação mobile.

#### Gestão de Estados Visuais Reativos:
1. **`flash_status === 'sent'`:** Exibe aviso de e-mail de confirmação enviado.
2. **`flash_status === 'cancel_link_sent'`:** Exibe mensagem genérica de envio do link de cancelamento.
3. **`flash_status === 'already_confirmed'`:** Avisa que o e-mail já está inscrito e oferece botão para solicitar o link de cancelamento.
4. **Estado Padrão:** Formulário com campo de entrada de e-mail e botão *"Assinar"* / *"Subscribe"*.

```vue
<script setup>
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    current_lang: { type: String, default: 'en' },
    ui_strings: { type: Object, default: () => ({}) }
});

const form = useForm({
    email: '',
    lang: props.current_lang
});

const page = usePage();
const flash_status = computed(() => page.props.flash?.newsletter?.status ?? null);
const cancel_link_sending = ref(false);

const submit = () => {
    form.post(route('newsletter.subscribe'), { preserveScroll: true });
};

const send_cancel_link = () => {
    const email = page.props.flash?.newsletter?.email ?? form.email;
    cancel_link_sending.value = true;
    router.post(
        route('newsletter.send-cancel-link'),
        { email, lang: props.current_lang },
        {
            preserveScroll: true,
            onFinish: () => {
                cancel_link_sending.value = false;
                form.reset('email');
            }
        }
    );
};
</script>
```

---

### 4.2 `resources/js/Pages/NewsletterConfirmPage.vue`
Página completa do Inertia para finalização do cadastro.

#### Estados da Interface (`status`):
- **`status: 'form'`:** Exibe o formulário de cadastro com os seguintes campos:
  - `name`: Nome completo (obrigatório).
  - `email`: E-mail pré-preenchido e bloqueado com `readonly` (`bg-slate-100 dark:bg-slate-700`).
  - `github`: Usuário do GitHub (opcional, ex.: `octocat`).
  - `phone`: Telefone (opcional).
  - `lang`: Dropdown com seleção dos 5 idiomas disponíveis.
- **`status: 'already_confirmed'`:** Painel informativo com botão integrado para solicitar link de cancelamento.
- **`status: 'expired'`:** Notifica que o link expirou (> 24h) e orienta nova submissão no site.
- **`status: 'not_found'`:** Notifica que o token é inválido ou inexistente.

---

### 4.3 `resources/js/Pages/NewsletterCancelPage.vue`
Página do Inertia para confirmação e feedback de cancelamento.

#### Estados da Interface (`status`):
- **`status: 'form'`:** Exibe mensagem de confirmação (*"Deseja parar de receber as newsletters do GitPR?"*) e botão *"Cancelar inscrição"*, que dispara `POST /newsletter/unsubscribe/{uuid}`.
- **`status: 'done'`:** Confirmação visual de que a inscrição foi cancelada com sucesso (`?done=1`).
- **`status: 'already_canceled'`:** Notifica que a inscrição já estava previamente cancelada.
- **`status: 'not_found'`:** Notifica link inválido.

---

## 5. Backend: Controller, Dicionário e E-mails Blade

### 5.1 `App\Http\Controllers\NewsletterController`

```php
namespace App\Http\Controllers;

use App\Mail\CancelLinkMail;
use App\Mail\ConfirmationMail;
use App\Models\NewsletterConfirmation;
use App\Models\NewsletterSubscriber;
use App\Support\NewsletterTranslations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $lang = $this->lang($request);
        $email = $validated['email'];

        if (NewsletterConfirmation::where('email', $email)->where('is_confirmed', true)->exists()) {
            return back()->with('newsletter', ['status' => 'already_confirmed', 'email' => $email]);
        }

        $pending = NewsletterConfirmation::where('email', $email)
            ->where('is_confirmed', false)
            ->notExpired()
            ->first();

        $confirmation = $pending ?: NewsletterConfirmation::create([
            'uuid' => (string) Str::uuid(),
            'email' => $email,
            'is_confirmed' => false,
        ]);

        Mail::to($email)->send(new ConfirmationMail(
            url: route('newsletter.confirm', ['uuid' => $confirmation->uuid, 'lang' => $lang]),
            strings: NewsletterTranslations::for($lang),
            lang: $lang,
        ));

        return back()->with('newsletter', ['status' => 'sent']);
    }

    public function confirm_submit(Request $request, string $uuid)
    {
        $confirmation = NewsletterConfirmation::where('uuid', $uuid)->firstOrFail();

        if ($confirmation->is_confirmed || $confirmation->created_at->lt(now()->subHours(24))) {
            return redirect()->route('newsletter.confirm', ['uuid' => $uuid]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'lang' => ['required', 'string', 'in:'.implode(',', NewsletterTranslations::LANGS)],
        ]);

        if ($validated['email'] !== $confirmation->email) {
            abort(422, 'Email does not match the confirmation link.');
        }

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $confirmation->email]);
        $subscriber->uuid = $subscriber->uuid ?? (string) Str::uuid();
        $subscriber->name = $validated['name'];
        $subscriber->github = $validated['github'] ?? null;
        $subscriber->phone = $validated['phone'] ?? null;
        $subscriber->lang = $validated['lang'];
        $subscriber->is_canceled = false;
        $subscriber->date_canceled = null;
        $subscriber->save();

        $confirmation->update([
            'is_confirmed' => true,
            'date_confirmed' => now(),
        ]);

        return redirect()->route('newsletter.confirm', ['uuid' => $uuid, 'lang' => $validated['lang']]);
    }

    public function unsubscribe(string $uuid)
    {
        $subscriber = NewsletterSubscriber::where('uuid', $uuid)->first();

        if ($subscriber) {
            $subscriber->update([
                'is_canceled' => true,
                'date_canceled' => now(),
            ]);
        }

        return redirect()->route('newsletter.cancel', [
            'uuid' => $uuid,
            'lang' => $subscriber?->lang ?? 'en',
            'done' => 1,
        ]);
    }
}
```

---

### 5.2 Centralização de Traduções: `App\Support\NewsletterTranslations`
Centraliza as chaves nos 5 idiomas (`en`, `pt_br`, `pt_pt`, `fr`, `es`). Métodos principais:
- `NewsletterTranslations::for(string $lang): array`: Retorna o conjunto completo de strings do idioma com fallback para `en`.
- `NewsletterTranslations::get(string $lang, string $key, array $replace = []): string`: Retorna a chave com substituição dinâmica de placeholders (ex.: `{version}`).

---

### 5.3 Templates Blade de E-mails

#### 1. E-mail de Confirmação (`resources/views/emails/confirmation.blade.php`)
Renderiza layout responsivo com cabeçalho escuro GitPR (`#0a192f`), botão de ação destacado em azul (`#1a80d4`), link seguro com UUID e aviso textual de expiração em 24 horas.

#### 2. E-mail com Link de Cancelamento (`resources/views/emails/cancel-link.blade.php`)
Enviado sob demanda para que o usuário confirme seu desejo de descadastro através de um clique no botão que aponta para `newsletter.cancel`.

#### 3. E-mail da Edição da Newsletter (`resources/views/emails/newsletter.blade.php`)
Renderiza o corpo HTML `{!! $htmlBody !!}` gerado a partir do Markdown correspondente, acompanhado de rodapé com link direto de cancelamento.

---

## 6. Motor Markdown e Versionamento de Conteúdo

### 6.1 Estrutura de Diretórios
```
public/content/
├── relatorio.md                 # Extração da versão atual ("Current version: 1.2.0")
└── newsletter/
    ├── 0.0.35/
    │   ├── newsletter_body.md         # Edição padrão em Inglês (obrigatório)
    │   ├── newsletter_body.pt_br.md   # Tradução Português (Brasil)
    │   ├── newsletter_body.pt_pt.md   # Tradução Português (Portugal)
    │   ├── newsletter_body.fr.md      # Tradução Francês
    │   └── newsletter_body.es.md      # Tradução Espanhol
    └── 1.2.0/
        └── ...
```

### 6.2 Classe `App\Support\NewsletterContent`
- `version_from_relatorio()`: Executa regex em `public/content/relatorio.md` procurando por `Current version:\*{0,2}\s*(\d+\.\d+\.\d+)`.
- `body_markdown(string $version, string $lang)`: Carrega o arquivo do idioma e aplica fallback para `newsletter_body.md` caso o arquivo específico não exista. Lança `RuntimeException` se a edição não existir.
- `body_html(string $version, string $lang)`: Converte o Markdown em HTML GFM via `Str::markdown()`.
- `last_sent_version()` e `mark_sent(string $version)`: Registra a última versão disparada em `storage/app/private/newsletter/last_sent.txt`.

---

## 7. Comando Artisan: `php artisan newsletter:send`

Implementado em `app/Console/Commands/NewsletterSendCommand.php`:

```bash
php artisan newsletter:send {version?} {--force} {--interval=5}
```

### Fluxo de Execução e Mecanismos de Proteção:
1. **Resolução de Versão:** Se não informada, extrai do relatório de status.
2. **Anti-Reenvio:** Verifica se a versão é idêntica à de `last_sent.txt`. Se sim, exige `--force`.
3. **Fail-Fast:** Valida se o arquivo Markdown em inglês existe antes de iterar sobre os assinantes.
4. **Alerta de Duração:** Se `quantidade_de_inscritos * intervalo > 3600 segundos`, o comando alerta sobre o tempo estimado e exige `--force`.
5. **Loop de Disparo com Isolamento:** Itera sobre `NewsletterSubscriber::active()->get()`. Cada envio é encapsulado em `try/catch (Throwable $e)`. Se o envio de um assinante falhar, exibe aviso e avança para o próximo.
6. **Marcador de Conclusão:** Ao finalizar com sucesso, chama `NewsletterContent::mark_sent($version)`.

---

## 8. Matriz de Testes Automatizados (QA)

Todos os testes estão implementados com Pest Framework em `tests/Feature/Newsletter*.php` (26 testes, 100% aprovados):

| Arquivo de Teste | Cenário Coberto |
|---|---|
| `NewsletterSubscribeTest.php` | - Criação de confirmação pendente e envio de e-mail.<br>- Reutilização de UUID em requisições antes de 24h.<br>- Geração de novo UUID após expiração de 24h.<br>- Bloqueio de envio para e-mail já confirmado.<br>- Resposta genérica no envio de link de cancelamento.<br>- Validação de e-mail inválido. |
| `NewsletterConfirmTest.php` | - Exibição correta dos 4 estados (`form`, `not_found`, `expired`, `already_confirmed`).<br>- Gravação de assinante e ativação da confirmação.<br>- Bloqueio e erro 422 na tentativa de adulterar o e-mail.<br>- Reativação automática de assinante cancelado.<br>- Garantia de apenas um registro confirmado por e-mail. |
| `NewsletterCancelTest.php` | - Exibição da tela de cancelamento para assinante ativo.<br>- Execução do cancelamento via POST com redirecionamento para `?done=1`.<br>- Tratamento de UUID inexistente. |
| `NewsletterSendCommandTest.php` | - Envio do corpo traduzido conforme o idioma de cada assinante.<br>- Fallback para o corpo em inglês quando a tradução não existir.<br>- Bloqueio de reenvio sem `--force`.<br>- Abortamento em lotes demorados sem `--force`.<br>- Fail-fast quando o corpo markdown não existir. |

