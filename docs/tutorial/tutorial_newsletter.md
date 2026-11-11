# Tutorial: Como criar uma Newsletter no GitPR Site

Este documento explica como o sistema de newsletter do site funciona e o passo a passo para criar e enviar uma edição.

## 1. Como o processo funciona

### Fluxo do visitante (inscrição)

```
Visitante informa o e-mail no box (coluna direita / menu mobile)
        │
        ▼
POST /newsletter/subscribe ──────────────► e-mail de confirmação
        │                                      (link com uuid + ?lang=)
        │
        ├─ e-mail nunca confirmado e link válido (24h) → reenvia o MESMO uuid
        ├─ e-mail já confirmado → mensagem "já inscrito" + opção de
        │                        receber link de cancelamento
        └─ senão → cria registro novo em newsletter_confirmations e envia o link
        │
        ▼
GET /newsletter/confirm/{uuid}?lang=<idioma>
        │   (tela com barra superior: versão, tema, seletor de idioma)
        │
        ├─ link não encontrado / expirado (24h) → painel informativo
        ├─ já confirmado → painel de sucesso + opção de link de cancelamento
        └─ válido → formulário: nome, e-mail (readonly), GitHub, telefone, idioma
                │
                ▼
        POST /newsletter/confirm/{uuid}
                │   grava em newsletter_subscribers com is_canceled=false
                │   (reativa a inscrição se o e-mail estava cancelado)
                ▼
        Inscrito ativo ✓

Cancelamento: GET /newsletter/cancel/{uuid} (texto no idioma do inscrito) +
              POST /newsletter/unsubscribe/{uuid} (one-click, RFC 8058).
              Depois de cancelado, uma nova confirmação reativa a inscrição.
```

### Fluxo de envio (administração)

```
1. Dicas      → skill update-tip-tools (tip_tools.json)
2. Corpo      → skill generate-newsletter-body (newsletter_body.{lang}.md)
3. Envio      → php artisan newsletter:send (manual, temporário)
4. E-mail     → NewsletterMail com corpo markdown → HTML, no idioma do inscrito
                (fallback inglês), headers List-Unsubscribe + link de cancelamento
5. Anti-reenvio → marker storage/app/private/newsletter/last_sent.txt
```

## 2. Passo a passo: criando uma newsletter

### Passo 1 — Garanta o relatório atualizado

A newsletter deriva do relatório de status. Se o site ainda não tem a versão
mais recente do relatório, rode a skill `update-relatorio` (sincroniza
`public/content/relatorio.md` e traduções com o repositório do GitPR CLI).

### Passo 2 — Atualize as dicas

Rode a skill `update-tip-tools`. Ela varre `public/content/**/*.md` e atualiza
`public/content/tip_tools.json` com dicas de uso, features e curiosidades nos
5 idiomas, preservando as dicas já utilizadas (`used: true`).

### Passo 3 — Gere o corpo da newsletter

Rode a skill `generate-newsletter-body`. Ela:

1. Extrai a versão do GitPR CLI do campo **Current version** de
   `public/content/relatorio.md` (ex.: `0.0.35`).
2. Gera `public/content/newsletter/{version}/newsletter_body.md` + variantes
   `.pt_br.md`, `.pt_pt.md`, `.es.md`, `.fr.md` com apenas:
   - **Novidades da versão** (seção "What's New" do relatório);
   - **Como usar** (essencial da versão);
   - **Dicas úteis** — escolhe 1 dica com `used: false` e marca-a como
     `used: true` (nunca repetem entre edições). Uma dica por edição rende
     mais versões antes de esgotar o banco de dicas.
3. Não toca no `menu.json` — a newsletter fica fora do menu e da busca por
   design (o `DocsController` devolve 404 para qualquer path `newsletter/*`).

Se a pasta da versão já existir, a skill pergunta antes de sobrescrever.

### Passo 4 — Revise os arquivos gerados

Confira os 5 arquivos: paridade de estrutura, idioma correto, links válidos e
sem HTML embutido (é e-mail). Evite strings como `class="..."` no markdown —
o Tailwind varre `public/content/**/*.md`.

### Passo 5 — Configure o ambiente

Antes do primeiro envio real, no `.env`:

```env
MAIL_MAILER=smtp            # em dev pode ser log para conferir no storage/logs/laravel.log
MAIL_FROM_ADDRESS=noreply@seudominio.com
APP_URL=https://seudominio.com   # usado nos links de confirmação/cancelamento
```

### Passo 6 — Envie

```bash
php artisan newsletter:send              # usa a versão do CLI do relatório
php artisan newsletter:send 0.0.35      # versão explícita
php artisan newsletter:send --interval=0   # sem pausa entre envios (testes)
```

Regras do comando:

- **Anti-reenvio**: se `last_sent.txt` já tem a versão, aborta; use `--force`
  para reenviar (ex.: correção no conteúdo).
- **Alerta de volume**: se `inscritos × intervalo > 1 hora` (~720 inscritos a
  5s), avisa com a estimativa e exige `--force` — o envio é síncrono, então
  volumes grandes realmente demoram.
- **Falha individual**: um e-mail com erro não interrompe o lote (aviso no
  console e segue).
- Cada inscrito recebe no próprio `lang` (fallback inglês).

### Passo 7 — Verifique

- Dev: `MAIL_MAILER=log` → confira assunto, corpo e headers no
  `storage/logs/laravel.log`.
- Testes: `composer run test` cobre inscrição, confirmação, cancelamento e o
  comando (`tests/Feature/Newsletter*.php`).

## 3. Referência rápida

| O quê | Onde |
| --- | --- |
| Tabelas | `newsletter_subscribers`, `newsletter_confirmations` (prefixo `newsletter_`) |
| Controller | `app/Http/Controllers/NewsletterController.php` |
| Rotas | `/newsletter/subscribe`, `/newsletter/confirm/{uuid}`, `/newsletter/cancel/{uuid}`, `/newsletter/unsubscribe/{uuid}`, `/newsletter/send-cancel-link` |
| E-mails | `app/Mail/` + `resources/views/emails/` |
| Textos i18n | `app/Support/NewsletterTranslations.php` (5 idiomas) |
| Corpo da newsletter | `public/content/newsletter/{version}/newsletter_body.{lang}.md` |
| Dicas | `public/content/tip_tools.json` |
| Marker de envio | `storage/app/private/newsletter/last_sent.txt` |
| Command | `app/Console/Commands/NewsletterSendCommand.php` |
| Skills | `update-relatorio`, `update-tip-tools`, `generate-newsletter-body` |
| Testes | `tests/Feature/Newsletter*.php` |

## 4. Regras que valem ouro

- **Versão da newsletter = versão do CLI** citada no relatório (campo
  **Current version**, ex.: `0.0.35`) — não é a versão do relatório em si
  (H1, ex.: `v0.0.10`).
- Link de confirmação **expira em 24h** (base `created_at`); reenvio antes de
  confirmar reutiliza o **mesmo uuid**; depois de expirar, gera um novo.
- Só **um registro `is_confirmed=true` por e-mail** (guard de aplicação).
- `is_canceled` tem padrão `true` no banco (spec); toda confirmação grava
  `false` explicitamente. Cancelar → `true` + `date_canceled`; re-confirmar →
  **reativa**.
- Cancelamento one-click: o header `List-Unsubscribe` aponta para
  `POST /newsletter/unsubscribe/{uuid}` — o Gmail/Outlook exibem o botão
  automático de descadastro e enviam o POST direto.
